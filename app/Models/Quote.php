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
        'rejection_details'
    ];

    protected $casts = [
        'valid_until' => 'date',
        'amount' => 'decimal:2'
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

    // Methods
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function markAsConverted()
    {
        return $this->update(['status' => 'converted']);
    }
}
