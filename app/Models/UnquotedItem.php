<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnquotedItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quote_id',
        'item',
        'quantity',
        'reason',
        'reason_details'
    ];

    /**
     * Get the quote that owns this unquoted item.
     */
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}
