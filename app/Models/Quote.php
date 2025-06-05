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
        'closed_by'
    ];

    protected $casts = [
        'valid_until' => 'date',
        'amount' => 'decimal:2',
        'has_rfq' => 'boolean',
        'total_rfq_items' => 'integer',
        'rfq_files_count' => 'integer',
        'approved_at' => 'datetime',
        'closed_at' => 'datetime'
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
