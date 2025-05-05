<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MarketerController extends Controller
{
    public function create()
    {
        return view('marketers.create');
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'name' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('users', 'email')],
            'password' => ['required', 'min:5', 'max:20'],
            'phone' => ['max:50'],
            'location' => ['max:70'],
        ]);

        $attributes['password'] = Hash::make($attributes['password']);
        $attributes['role'] = 'marketer';

        $user = User::create($attributes);

        return redirect()->route('user-management')->with('success', 'Marketer registered successfully');
    }
}