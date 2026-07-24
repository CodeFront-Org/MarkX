<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'marketer_id',
        'amount',
        'status',
        'created_at',
        'updated_at',
        'title',
        'description',
        'footertext',
        'valid_until',
        'rejection_reason',
        'rejection_details',
        'reference',
        'has_rfq',
        'rfq_files_count',
        'contact_person',
        'total_rfq_items',
        'approved_at',
        'approved_by',
        'closed_at',
        'closed_by',
        'submitted_to_customer_at'
    ];

    protected $casts = [
        'valid_until' => 'date',
        'amount' => 'decimal:2',
        'has_rfq' => 'boolean',
        'total_rfq_items' => 'integer',
        'rfq_files_count' => 'integer',
        'approved_at' => 'datetime',
        'closed_at' => 'datetime',
        'submitted_to_customer_at' => 'datetime'
    ];

    protected $appends = [
        'quoted_items_count',
        'unquoted_items_count',
        'total_items_count',
        'remaining_items_count'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function marketer()
    {
        return $this->belongsTo(User::class, 'marketer_id');
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function files()
    {
        return $this->hasMany(QuoteFile::class);
    }

    public function productItems()
    {
        return $this->belongsToMany(ProductItem::class, 'quote_items');
    }

    public function unquotedItems()
    {
        return $this->hasMany(UnquotedItem::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function approvals()
    {
        return $this->hasMany(QuoteApproval::class);
    }

    // ---------------------------------------------------------------------
    // Approver chain
    // ---------------------------------------------------------------------

    /**
     * The next approver in the chain who still needs to approve this quote,
     * or null when the chain is empty or every step has approved.
     *
     * Derived (not stored) so it stays correct even if the chain is reordered
     * or an approver is removed while the quote is in flight.
     */
    public function nextApprover(): ?User
    {
        $approvedUserIds = $this->approvals()
            ->where('action', 'approved')
            ->pluck('user_id')
            ->all();

        $step = ApprovalChainStep::ordered()
            ->whereNotIn('user_id', $approvedUserIds)
            ->with('approver')
            ->first();

        return $step ? $step->approver : null;
    }

    /**
     * Whether it is the given user's turn to approve this quote.
     */
    public function isAwaitingApprovalBy(User $user): bool
    {
        $next = $this->nextApprover();

        return $next !== null && $next->id === $user->id;
    }

    /**
     * Whether an approver chain has been configured at all.
     */
    public function hasApprovalChain(): bool
    {
        return ApprovalChainStep::exists();
    }

    /**
     * Whether every configured chain step has approved this quote.
     * True for an empty chain (nothing left to wait on).
     */
    public function chainApprovalComplete(): bool
    {
        return $this->nextApprover() === null;
    }

    /**
     * Position (1-based) of the current step and total number of steps, for
     * display, e.g. "Approval 2 of 3". Returns [0, 0] when no chain exists.
     */
    public function approvalProgress(): array
    {
        $total = ApprovalChainStep::count();

        if ($total === 0) {
            return [0, 0];
        }

        $approvedCount = $this->approvals()->where('action', 'approved')->distinct('user_id')->count('user_id');

        return [min($approvedCount + 1, $total), $total];
    }

    // Scopes
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Computed Properties
    public function getQuotedItemsCountAttribute()
    {
        return $this->items()->count();
    }

    public function getUnquotedItemsCountAttribute()
    {
        return $this->unquotedItems()->count();
    }

    public function getTotalItemsCountAttribute()
    {
        return $this->quoted_items_count + $this->unquoted_items_count;
    }

    public function getRemainingItemsCountAttribute()
    {
        return $this->total_rfq_items - $this->total_items_count;
    }

    // Methods
    public function getDaysInStatus()
    {
        if ($this->status === 'pending_customer' && $this->submitted_to_customer_at) {
            return $this->submitted_to_customer_at->diffInDays();
        }
        if ($this->status === 'completed' && $this->closed_at) {
            return $this->closed_at->diffInDays();
        }
        if (($this->status === 'pending_finance' || $this->status === 'rejected') && $this->updated_at) {
            return $this->updated_at->diffInDays();
        }
        return $this->created_at->diffInDays();
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function markAsConverted()
    {
        return $this->update(['status' => 'converted']);
    }

    public function updateRfqFileCount()
    {
        $count = $this->files()->count();
        $this->update([
            'rfq_files_count' => $count,
            'has_rfq' => $count > 0
        ]);
    }

    public function canDeleteFile()
    {
        return $this->rfq_files_count > 1;
    }
}
