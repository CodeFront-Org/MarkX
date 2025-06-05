<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuotePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view quotes list
    }

    public function view(User $user, Quote $quote): bool
    {
        return $user->role === 'rfq_approver' || $user->role === 'lpo_admin' || $user->id === $quote->user_id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'rfq_processor'; // Only RFQ processors can create quotes
    }

    public function update(User $user, Quote $quote): bool
    {
        // Only lpo_admin can update quotes, and only if they're not already completed
        return $user->role === 'lpo_admin' && $quote->status !== 'completed';
    }

    public function delete(User $user, Quote $quote): bool
    {
        return $user->id === $quote->user_id && $quote->status === 'pending_manager';
    }

    public function approve(User $user, Quote $quote): bool
    {
        // RFQ Approvers can approve quotes in pending_manager status (whole quote approval)
        if ($user->role === 'rfq_approver') {
            return $quote->status === 'pending_manager';
        }

        // LPO Admin can approve quotes in pending_finance status (finalize after reviewing items)
        if ($user->role === 'lpo_admin') {
            return $quote->status === 'pending_finance';
        }

        return false;
    }

    public function reject(User $user, Quote $quote): bool
    {
        // RFQ Approvers can reject quotes in pending_manager status
        if ($user->role === 'rfq_approver') {
            return $quote->status === 'pending_manager';
        }

        // LPO Admin can reject quotes in pending_finance status
        if ($user->role === 'lpo_admin') {
            return $quote->status === 'pending_finance';
        }

        return false;
    }

    public function submitToFinance(User $user, Quote $quote): bool
    {
        // Only the RFQ processor who created the quote can submit it to finance
        return $user->id === $quote->user_id && $quote->status === 'pending_customer';
    }

    public function restore(User $user, Quote $quote): bool
    {
        return $user->role === 'rfq_approver';
    }

    public function forceDelete(User $user, Quote $quote): bool
    {
        return false; // Disable force delete for all users
    }
}
