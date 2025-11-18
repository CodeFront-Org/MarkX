<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->role === 'rfq_approver' || $user->role === 'lpo_admin';
    }
}