<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view invoices list
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->role === 'manager' || $user->id === $invoice->quote->user_id;
    }

    public function create(User $user): bool
    {
        return $user->role !== 'manager'; // Managers cannot create invoices
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->id === $invoice->quote->user_id && $invoice->status === 'draft';
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->id === $invoice->quote->user_id && $invoice->status === 'draft';
    }

    public function send(User $user, Invoice $invoice): bool
    {
        return $user->id === $invoice->quote->user_id && $invoice->status === 'draft';
    }

    public function markAsPaid(User $user, Invoice $invoice): bool
    {
        return $user->id === $invoice->quote->user_id && in_array($invoice->status, ['final', 'overdue']);
    }
}
