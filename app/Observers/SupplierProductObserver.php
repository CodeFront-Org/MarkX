<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

class SupplierProductObserver
{
    /**
     * Handle the Pivot "creating" event.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Pivot  $pivot
     * @return void
     */
    public function creating(Pivot $pivot)
    {
        // Only handle supplier_product pivot models
        if ($pivot->getTable() !== 'supplier_product') {
            return;
        }

        // Generate a unique supplier product code if not already set
        if (empty($pivot->supplier_product_code)) {
            // Get the supplier name
            $supplier = \App\Models\Supplier::find($pivot->supplier_id);
            
            if ($supplier) {
                // Format: SUP-{SUPPLIER_PREFIX}-{RANDOM_NUMBER}
                $prefix = strtoupper(Str::substr($supplier->name, 0, 3));
                $random = rand(10000, 99999);
                
                $pivot->supplier_product_code = "SUP-{$prefix}-{$random}";
            }
        }
    }
} 