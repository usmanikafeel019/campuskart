<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $r)
    {
        return view('profile.edit', ['user' => $r->user()]);
    }

    public function update(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:100', 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($r->user()->id)], 'phone' => 'nullable|string|regex:/^[+0-9 ()-]{7,25}$/', 'current_password' => 'required|current_password']);
        $r->user()->update(collect($d)->except('current_password')->all());

        return back()->with('success', 'Profile updated. Phone numbers remain private.');
    }
}
