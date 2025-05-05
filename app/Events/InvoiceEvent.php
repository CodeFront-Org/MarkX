<?php

namespace App\Events;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceEvent
{
    use Dispatchable, SerializesModels;

    public $invoice;
    public $user;
    public $action;
    public $oldStatus;
    public $newStatus;

    public function __construct(Invoice $invoice, User $user, string $action, ?string $oldStatus = null, ?string $newStatus = null)
    {
        $this->invoice = $invoice;
        $this->user = $user;
        $this->action = $action;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}