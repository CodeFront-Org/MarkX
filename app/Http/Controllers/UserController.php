<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(15);
        return view('laravel-examples.user-management', compact('users'));
    }

    public function edit(User $user)
    {
        return view('laravel-examples.user-edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $attributes = $request->validate([
            'name' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'max:50'],
            'location' => ['nullable', 'max:70'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in(['rfq_approver', 'rfq_processor', 'lpo_admin', 'superadmin', 'client'])],
        ]);

        $roles = array_values(array_unique($attributes['roles']));
        $attributes['roles'] = $roles;
        $attributes['role'] = $roles[0] ?? 'rfq_processor';

        $user->update($attributes);

        return redirect()->route('user-management')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        // Super admin accounts may only be removed by another super admin.
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'You are not allowed to delete a Super Admin account.');
        }

        if ($user->isRfqApprover() && User::withRole('rfq_approver')->count() <= 1) {
            return back()->with('error', 'Cannot delete the last RFQ Approver account');
        }

        $user->delete();
        return redirect()->route('user-management')
            ->with('success', 'User deleted successfully');
    }
}