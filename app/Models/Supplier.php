<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'notes',
        'updated_by',
    ];

    /**
     * Get the products associated with this supplier.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(ProductItem::class, 'supplier_product')
            ->withPivot('price', 'supplier_product_code', 'notes', 'updated_by')
            ->withTimestamps();
    }

    /**
     * Get the user who last updated this supplier.
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    /**
     * Get the last update date for this supplier.
     */
    public function getLastUpdatedAttribute()
    {
        return $this->updated_at->format('M d, Y H:i');
    }
    
    /**
     * Get the count of products associated with this supplier.
     */
    public function getProductCountAttribute()
    {
        return $this->products()->count();
    }
}
