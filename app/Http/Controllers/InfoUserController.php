<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class InfoUserController extends Controller
{
    public function create()
    {
        return view('laravel-examples/user-profile');
    }

    public function store(Request $request)
    {
        $attributes = request()->validate([
            'name' => ['required', 'max:50'],
            'phone' => ['max:50'],
            'about_me' => ['max:150'],
            'location' => ['max:70'],
        ]);

        if ($request->get('email') != auth()->user()->email) {
            $attribute = request()->validate([
                'email' => ['required', 'email', 'max:50', Rule::unique('users')->ignore(Auth::user()->id)],
            ]);
        }

        // Handle password change if provided
        if ($request->filled('current_password')) {
            $request->validate([
                'current_password' => ['required', 'string'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if (!Hash::check($request->current_password, auth()->user()->password)) {
                return back()->withErrors(['current_password' => 'The current password is incorrect.']);
            }

            $attributes['password'] = Hash::make($request->password);
        }
              $user = Auth::user();
                if ($request->hasFile('signature')) {
            $signature = $request->file('signature')->store('signatures', 'public');
            $user->signature = basename($signature);
            
            $user->save();
        }


        User::where('id', Auth::user()->id)
        ->update(array_merge([
            'name' => $attributes['name'],
            'email' => $request->get('email') ?? auth()->user()->email,
            'phone' => $attributes['phone'],
            'location' => $attributes['location'],
            'about_me' => $attributes['about_me'],
        ], isset($attributes['password']) ? ['password' => $attributes['password']] : []));

        return redirect('/user-profile')->with('success','Profile updated successfully');
    }
}
