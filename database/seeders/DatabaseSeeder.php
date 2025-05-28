<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Quote;
use App\Models\QuoteItem;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        // Create marketers
        $marketers = [
            [
                'name' => 'Marketer One',
                'email' => 'marketer1@example.com',
                'password' => bcrypt('password'),
                'role' => 'marketer',
            ],
            [
                'name' => 'Marketer Two',
                'email' => 'marketer2@example.com',
                'password' => bcrypt('password'),
                'role' => 'marketer',
            ],
            [
                'name' => 'Marketer Three',
                'email' => 'marketer3@example.com',
                'password' => bcrypt('password'),
                'role' => 'marketer',
            ],
        ];

        foreach ($marketers as $marketer) {
            User::create($marketer);
        }

        // Get marketer IDs
        $marketerIds = User::where('role', 'marketer')->pluck('id')->toArray();
        
        // Create quotes over the last 6 months
        $statuses = ['pending', 'approved', 'rejected', 'completed'];
        $items = [
            'Website Development' => [15000, 30000],
            'Mobile App Development' => [20000, 40000],
            'SEO Services' => [5000, 10000],
            'Content Marketing' => [3000, 8000],
            'Social Media Management' => [2500, 7500],
            'Logo Design' => [500, 2000],
            'Brand Identity Package' => [3000, 8000],
            'E-commerce Setup' => [10000, 25000],
            'Email Marketing Campaign' => [1500, 5000],
            'Video Production' => [5000, 15000],
        ];

        // Create quotes for each month in the last 6 months
        for ($month = 5; $month >= 0; $month--) {
            $date = Carbon::now()->subMonths($month);
            $monthStart = Carbon::now()->subMonths($month)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($month)->endOfMonth();
            
            // More quotes in recent months
            $quotesCount = 15 + ($month * 3);
            
            for ($i = 0; $i < $quotesCount; $i++) {
                // Random date within the month
                $quoteDate = Carbon::createFromTimestamp(
                    rand($monthStart->timestamp, $monthEnd->timestamp)
                );
                
                // Determine status (more approved in earlier months)
                $statusWeights = [
                    'pending' => $month <= 1 ? 40 : 20,
                    'approved' => $month >= 3 ? 70 : 50,
                    'rejected' => 30
                ];
                
                $randomStatus = $this->getRandomWeighted($statusWeights);
                
                // Create the quote
                $quote = Quote::create([
                    'user_id' => $clients[array_rand($clients)]->id,
                    'marketer_id' => $marketerIds[array_rand($marketerIds)],
                    'amount' => 0, // Will be calculated from items
                    'status' => $randomStatus,
                    'created_at' => $quoteDate,
                    'updated_at' => $randomStatus !== 'pending' 
                        ? $quoteDate->copy()->addDays(rand(1, 14)) 
                        : $quoteDate,
                ]);
                
                // Add 1-5 items to the quote
                $itemsCount = rand(1, 5);
                $quoteTotal = 0;
                
                $selectedItems = array_rand($items, min($itemsCount, count($items)));
                if (!is_array($selectedItems)) {
                    $selectedItems = [$selectedItems];
                }
                
                foreach ($selectedItems as $itemKey) {
                    $itemName = array_keys($items)[$itemKey];
                    $range = $items[$itemName];
                    
                    $price = rand($range[0], $range[1]);
                    $quantity = rand(1, 3);
                    $itemTotal = $price * $quantity;
                    $quoteTotal += $itemTotal;
                    
                    QuoteItem::create([
                        'quote_id' => $quote->id,
                        'item' => $itemName,
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);
                }
                
                // Update the quote total
                $quote->update(['amount' => $quoteTotal]);
            }
        }
    }
    
    /**
     * Get a random value based on weights
     */
    private function getRandomWeighted(array $weights) 
    {
        $total = array_sum($weights);
        $rand = mt_rand(1, $total);
        
        foreach ($weights as $key => $weight) {
            $rand -= $weight;
            if ($rand <= 0) {
                return $key;
            }
        }
        
        return array_key_first($weights);
    }
}
