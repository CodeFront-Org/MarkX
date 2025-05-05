<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    protected $fillable = [
        'quote_id',
        'item',
        'quantity',
        'price',
        'approved'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'approved' => 'boolean'
    ];

    protected $appends = ['total'];

    public function getTotalAttribute()
    {
        return $this->quantity * $this->price;
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}
