<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserRegistrationController extends Controller
{
    public function create($role)
    {
        if (!in_array($role, ['rfq_processor', 'rfq_approver', 'lpo_admin'])) {
            abort(404);
        }
        
        return view('users.create', ['role' => $role]);
    }

    public function store(Request $request, $role)
    {
        if (!in_array($role, ['rfq_processor', 'rfq_approver', 'lpo_admin'])) {
            abort(404);
        }

        $attributes = $request->validate([
            'name' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('users', 'email')],
            'password' => ['required', 'min:5', 'max:20'],
            'phone' => ['max:50'],
            'location' => ['max:70'],
        ]);

        $attributes['password'] = Hash::make($attributes['password']);
        $attributes['role'] = $role;

        $user = User::create($attributes);

        $roleLabel = ucfirst($role);
        return redirect()->route('user-management')
            ->with('success', "$roleLabel registered successfully");
    }
}