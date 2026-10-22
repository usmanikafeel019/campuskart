<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:100', 'email' => 'required|email|max:255|unique:users', 'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()]]);
        $u = User::create($d);
        Auth::login($u);
        $r->session()->regenerate();

        return redirect('/my-listings');
    }

    public function login(Request $r)
    {
        $d = $r->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt([...$d, 'is_active' => true], $r->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials or suspended account.'])->onlyInput('email');
        }$r->session()->regenerate();

        return redirect()->intended(Auth::user()->role === 'admin' ? '/admin' : '/materials');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect('/');
    }
}
