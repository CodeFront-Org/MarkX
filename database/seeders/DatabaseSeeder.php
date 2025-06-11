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
        // Create admin user if it doesn't exist
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role' => 'rfq_approver',
            ]);
        }

        // Create LPO Admin user if it doesn't exist
        if (!User::where('email', 'lpo_admin@example.com')->exists()) {
            User::create([
                'name' => 'LPO Admin',
                'email' => 'lpo_admin@example.com',
                'password' => bcrypt('password'),
                'role' => 'lpo_admin',
            ]);
        }

        // Create RFQ Processors
        $rfqProcessors = [
            [
                'name' => 'RFQ Processor One',
                'email' => 'marketer1@example.com',
                'password' => bcrypt('password'),
                'role' => 'rfq_processor',
            ],
            [
                'name' => 'RFQ Processor Two',
                'email' => 'marketer2@example.com',
                'password' => bcrypt('password'),
                'role' => 'rfq_processor',
            ],
            [
                'name' => 'RFQ Processor Three',
                'email' => 'marketer3@example.com',
                'password' => bcrypt('password'),
                'role' => 'rfq_processor',
            ],
        ];

        foreach ($rfqProcessors as $processor) {
            if (!User::where('email', $processor['email'])->exists()) {
                User::create($processor);
            }
        }

        // Create clients
        $clientsData = [
            [
                'name' => 'Client One',
                'email' => 'client1@example.com',
                'password' => bcrypt('password'),
                'role' => 'client',
            ],
            [
                'name' => 'Client Two',
                'email' => 'client2@example.com',
                'password' => bcrypt('password'),
                'role' => 'client',
            ],
            [
                'name' => 'Client Three',
                'email' => 'client3@example.com',
                'password' => bcrypt('password'),
                'role' => 'client',
            ],
        ];

        $clients = [];
        foreach ($clientsData as $clientData) {
            if (!User::where('email', $clientData['email'])->exists()) {
                $clients[] = User::create($clientData);
            } else {
                $clients[] = User::where('email', $clientData['email'])->first();
            }
        }

        // Skip quote creation if we already have quotes
        if (Quote::count() > 0) {
            return;
        }

        // Get RFQ Processor IDs
        $processorIds = User::where('role', 'rfq_processor')->pluck('id')->toArray();
        
        if (empty($processorIds)) {
            // If no processors found, use the first user as fallback
            $processorIds = [User::first()->id];
        }
        
        if (empty($clients)) {
            // If no clients found, skip quote creation
            return;
        }
        
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

        // Sample company names for quote titles
        $companies = [
            'Acme Corp', 'Globex', 'Initech', 'Umbrella Corp', 'Stark Industries',
            'Wayne Enterprises', 'Cyberdyne Systems', 'Soylent Corp', 'Massive Dynamic',
            'Oscorp Industries', 'LexCorp', 'Weyland-Yutani', 'Tyrell Corporation'
        ];

        // Create quotes for each month in the last 6 months
        $refCounter = 1;
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
                
                // Generate a random company and project type for the title
                $company = $companies[array_rand($companies)];
                $itemNames = array_keys($items);
                $projectType = $itemNames[array_rand($itemNames)];
                
                // Create a unique reference using counter and microtime
                $uniqueRef = 'REF-' . $quoteDate->format('Ym') . '-' . $refCounter . '-' . substr(md5(microtime()), 0, 5);
                $refCounter++;
                
                // Create the quote
                $quote = Quote::create([
                    'user_id' => $clients[array_rand($clients)]->id,
                    'marketer_id' => $processorIds[array_rand($processorIds)],
                    'amount' => 0, // Will be calculated from items
                    'status' => $randomStatus,
                    'title' => $company . ' ' . $projectType,
                    'description' => 'Quote for ' . $projectType . ' services for ' . $company,
                    'contact_person' => 'Contact Person ' . ($i + 1),
                    'reference' => $uniqueRef,
                    'valid_until' => $quoteDate->copy()->addMonths(1),
                    'created_at' => $quoteDate,
                    'updated_at' => $randomStatus !== 'pending' 
                        ? $quoteDate->copy()->addDays(rand(1, 14)) 
                        : $quoteDate,
                ]);
                
                // Add 1-5 items to the quote
                $itemsCount = rand(1, 5);
                $quoteTotal = 0;
                
                // Get random items without using array_rand
                $itemNames = array_keys($items);
                shuffle($itemNames);
                $selectedItems = array_slice($itemNames, 0, min($itemsCount, count($itemNames)));
                
                foreach ($selectedItems as $itemName) {
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
