<?php

namespace App\Listeners;

use App\Events\InvoiceEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InvoiceEventListener
{
    public function handle(InvoiceEvent $event)
    {
        // Only log if we have an invoice ID (it will be null during creation event firing)
        if ($event->invoice->id) {
            DB::table('invoice_logs')->insert([
                'invoice_id' => $event->invoice->id,
                'user_id' => $event->user->id,
                'action' => $event->action,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
                'created_at' => now()
            ]);
        }

        Log::info('Invoice action performed', [
            'invoice_number' => $event->invoice->invoice_number ?? 'Not yet assigned',
            'user' => $event->user->name,
            'action' => $event->action,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus
        ]);
    }
}