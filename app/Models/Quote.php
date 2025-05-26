<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'amount',
        'status',
        'user_id',
        'valid_until',
        'rejection_reason',
        'rejection_details',
        'reference',
        'has_rfq',
        'rfq_files_count',
        'contact_person',
        'total_rfq_items'
    ];

    protected $casts = [
        'valid_until' => 'date',
        'amount' => 'decimal:2',
        'has_rfq' => 'boolean',
        'total_rfq_items' => 'integer',
        'rfq_files_count' => 'integer'
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
        return $this->belongsTo(User::class);
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

    // Scopes
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
