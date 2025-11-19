<?php

namespace App\Console\Commands;

use App\Models\Quote;
use Illuminate\Console\Command;

class BackfillSubmittedToCustomerDate extends Command
{
    protected $signature = 'quotes:backfill-submitted-date';
    protected $description = 'Backfill submitted_to_customer_at for existing quotes';

    public function handle()
    {
        $quotes = Quote::whereIn('status', ['pending_customer', 'pending_finance', 'completed'])
            ->whereNull('submitted_to_customer_at')
            ->get();

        $this->info("Found {$quotes->count()} quotes to update");

        foreach ($quotes as $quote) {
            $daysAgo = $quote->created_at->diffInDays(now());
            $submittedDate = now()->subDays($daysAgo);
            
            $quote->update(['submitted_to_customer_at' => $submittedDate]);
            
            $this->line("Updated quote #{$quote->id}: {$quote->title}");
        }

        $this->info('Backfill completed!');
    }
}
