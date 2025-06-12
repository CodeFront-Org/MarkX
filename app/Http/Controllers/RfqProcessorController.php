<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RfqProcessorController extends Controller
{
    public function create()
    {
        return view('rfq-processors.create');
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
        $attributes['role'] = 'rfq_processor';

        $user = User::create($attributes);

        return redirect()->route('user-management')->with('success', 'RFQ Processor registered successfully');
    }
}
