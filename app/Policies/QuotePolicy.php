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
        return $user->role === 'manager' || $user->id === $quote->user_id;
    }

    public function create(User $user): bool
    {
        return $user->role !== 'manager'; // Managers cannot create quotes
    }

    public function update(User $user, Quote $quote): bool
    {
        return $user->id === $quote->user_id && $quote->status === 'pending';
    }

    public function delete(User $user, Quote $quote): bool
    {
        return $user->id === $quote->user_id && $quote->status === 'pending';
    }

    public function approve(User $user, Quote $quote): bool
    {
        return $user->id === $quote->user_id && $quote->status === 'pending';
    }

    public function reject(User $user, Quote $quote): bool
    {
        return $user->id === $quote->user_id && $quote->status === 'pending';
    }

    public function convert(User $user, Quote $quote): bool
    {
        return $user->id === $quote->user_id && $quote->status === 'approved';
    }

    public function restore(User $user, Quote $quote): bool
    {
        return $user->id === $quote->user_id;
    }

    public function forceDelete(User $user, Quote $quote): bool
    {
        return false; // Disable force delete for all users
    }
}
