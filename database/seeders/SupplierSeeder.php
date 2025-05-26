<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\ProductItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a user for the updated_by field
        $user = User::first();
        
        // Create sample suppliers
        $suppliers = [
            [
                'name' => 'ABC Electronics',
                'contact_person' => 'John Smith',
                'email' => 'john@abcelectronics.com',
                'phone' => '+254 712 345 678',
                'address' => 'Nairobi, Kenya',
                'notes' => 'Reliable supplier for electronic components',
                'updated_by' => $user->id,
            ],
            [
                'name' => 'XYZ Hardware',
                'contact_person' => 'Jane Doe',
                'email' => 'jane@xyzhardware.com',
                'phone' => '+254 723 456 789',
                'address' => 'Mombasa, Kenya',
                'notes' => 'Specializes in hardware tools and equipment',
                'updated_by' => $user->id,
            ],
            [
                'name' => 'Global Office Supplies',
                'contact_person' => 'Michael Johnson',
                'email' => 'michael@globalsupplies.com',
                'phone' => '+254 734 567 890',
                'address' => 'Kisumu, Kenya',
                'notes' => 'Office supplies and furniture provider',
                'updated_by' => $user->id,
            ],
        ];
        
        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }
        
        // Create sample products if none exist
        $productCount = ProductItem::count();
        if ($productCount === 0) {
            $products = [
                [
                    'name' => 'Laptop',
                    'description' => 'High-performance business laptop',
                    'price' => 85000,
                ],
                [
                    'name' => 'Desktop Computer',
                    'description' => 'Office desktop workstation',
                    'price' => 65000,
                ],
                [
                    'name' => 'Monitor',
                    'description' => '24-inch LED monitor',
                    'price' => 15000,
                ],
                [
                    'name' => 'Keyboard',
                    'description' => 'Wireless ergonomic keyboard',
                    'price' => 3500,
                ],
                [
                    'name' => 'Mouse',
                    'description' => 'Wireless optical mouse',
                    'price' => 1500,
                ],
                [
                    'name' => 'Printer',
                    'description' => 'Color laser printer',
                    'price' => 25000,
                ],
                [
                    'name' => 'Office Chair',
                    'description' => 'Ergonomic office chair',
                    'price' => 12000,
                ],
                [
                    'name' => 'Office Desk',
                    'description' => 'Executive office desk',
                    'price' => 18000,
                ],
                [
                    'name' => 'Filing Cabinet',
                    'description' => 'Metal filing cabinet',
                    'price' => 8000,
                ],
                [
                    'name' => 'Whiteboard',
                    'description' => 'Large magnetic whiteboard',
                    'price' => 5000,
                ],
            ];
            
            foreach ($products as $productData) {
                ProductItem::create($productData);
            }
        }
        
        // Get products to associate with suppliers
        $products = ProductItem::take(10)->get();
        
        if ($products->count() > 0) {
            // Associate products with suppliers
            $suppliers = Supplier::all();
            
            foreach ($suppliers as $supplier) {
                // Assign 3-5 random products to each supplier
                $randomProducts = $products->random(rand(3, min(5, $products->count())));
                
                foreach ($randomProducts as $product) {
                    $supplier->products()->attach($product->id, [
                        'price' => rand(100, 5000),
                        'notes' => 'Sample product from ' . $supplier->name,
                        'updated_by' => $user->id,
                    ]);
                }
            }
        }
    }
}
