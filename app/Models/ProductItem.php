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
    
    /**
     * Get the suppliers that provide this product.
     */
    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_product')
            ->withPivot('price', 'supplier_product_code', 'notes', 'updated_by')
            ->withTimestamps();
    }
}
