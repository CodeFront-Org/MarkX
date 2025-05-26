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
        return $user->role === 'manager' || $user->role === 'finance' || $user->id === $quote->user_id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'marketer'; // Only marketers can create quotes
    }

    public function update(User $user, Quote $quote): bool
    {
        // Only finance can update quotes, and only if they're not already completed
        return $user->role === 'finance' && $quote->status !== 'completed';
    }

    public function delete(User $user, Quote $quote): bool
    {
        return $user->id === $quote->user_id && $quote->status === 'pending_manager';
    }

    public function approve(User $user, Quote $quote): bool
    {
        // Managers can approve quotes in pending_manager status (whole quote approval)
        if ($user->role === 'manager') {
            return $quote->status === 'pending_manager';
        }

        // Finance can approve quotes in pending_finance status (finalize after reviewing items)
        if ($user->role === 'finance') {
            return $quote->status === 'pending_finance';
        }

        return false;
    }

    public function reject(User $user, Quote $quote): bool
    {
        // Managers can reject quotes in pending_manager status
        if ($user->role === 'manager') {
            return $quote->status === 'pending_manager';
        }

        // Finance can reject quotes in pending_finance status
        if ($user->role === 'finance') {
            return $quote->status === 'pending_finance';
        }

        return false;
    }

    public function submitToFinance(User $user, Quote $quote): bool
    {
        // Only the marketer who created the quote can submit it to finance
        return $user->id === $quote->user_id && $quote->status === 'pending_customer';
    }

    public function restore(User $user, Quote $quote): bool
    {
        return $user->role === 'manager';
    }

    public function forceDelete(User $user, Quote $quote): bool
    {
        return false; // Disable force delete for all users
    }
}
