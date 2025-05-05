<?php

namespace App\Models;

use App\Events\InvoiceEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'quote_id',
        'user_id',
        'amount',
        'status',
        'due_date',
        'paid_at'
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    private const ALLOWED_TRANSITIONS = [
        'draft' => ['final', 'cancelled'],
        'final' => ['paid', 'overdue', 'cancelled'],
        'overdue' => ['paid', 'cancelled'],
        'paid' => [],
        'cancelled' => []
    ];

    // Relationships
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeOverdue($query)
    {
        return $query->where('status', 'final')
                    ->whereDate('due_date', '<', now())
                    ->whereNull('paid_at');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid')->whereNotNull('paid_at');
    }

    // Methods
    public function markAsPaid()
    {
        return $this->transitionTo('paid', [
            'paid_at' => now()
        ]);
    }

    public function markAsOverdue()
    {
        if ($this->due_date < now() && $this->status === 'final') {
            return $this->transitionTo('overdue');
        }
        return false;
    }

    public function markAsFinal()
    {
        return $this->transitionTo('final', [
            'sent_at' => now()
        ]);
    }

    public function markAsCancelled()
    {
        return $this->transitionTo('cancelled');
    }

    protected function transitionTo(string $newStatus, array $additionalFields = [])
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new Exception("Invalid status transition from {$this->status} to {$newStatus}");
        }

        $oldStatus = $this->status;

        $result = DB::transaction(function () use ($newStatus, $additionalFields) {
            return $this->update(array_merge(['status' => $newStatus], $additionalFields));
        });

        // Fire status change event
        event(new InvoiceEvent(
            $this,
            Auth::user(),
            'status_change',
            $oldStatus,
            $newStatus
        ));

        return $result;
    }

    protected function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::ALLOWED_TRANSITIONS[$this->status] ?? []);
    }

    protected static function generateUniqueInvoiceNumber(): string
    {
        return DB::transaction(function () {
            $sequence = DB::table('sequences')
                ->where('name', 'invoice_number')
                ->lockForUpdate()
                ->first();

            $nextValue = $sequence->current_value + 1;

            DB::table('sequences')
                ->where('name', 'invoice_number')
                ->update(['current_value' => $nextValue]);

            return 'INV-' . date('Y') . '-' . str_pad($nextValue, 5, '0', STR_PAD_LEFT);
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateUniqueInvoiceNumber();
            }

            // Fire creation event
            event(new InvoiceEvent(
                $invoice,
                Auth::user(),
                'created'
            ));
        });

        static::deleting(function ($invoice) {
            if ($invoice->status !== 'draft') {
                throw new Exception('Only draft invoices can be deleted');
            }

            // Fire deletion event
            event(new InvoiceEvent(
                $invoice,
                Auth::user(),
                'deleted'
            ));
        });
    }
}
