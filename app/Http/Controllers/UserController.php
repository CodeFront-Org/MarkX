<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
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
        ]);

        $user->update($attributes);

        return redirect()->route('user-management')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        if ($user->isManager() && User::where('role', 'manager')->count() <= 1) {
            return redirect()->route('user-management')
                ->with('error', 'Cannot delete the last manager');
        }

        $user->delete();
        return redirect()->route('user-management')
            ->with('success', 'User deleted successfully');
    }
}