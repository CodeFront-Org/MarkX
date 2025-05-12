<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductItem extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
    ];

    public function quotes(): BelongsToMany
    {
        return $this->belongsToMany(Quote::class, 'quote_items');
    }
}
