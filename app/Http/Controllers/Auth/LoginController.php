<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $login = $request->input('email'); // field input tetap 'email' di form
        $password = $request->input('password');

        // Cek apakah input berupa email atau username
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$fieldType => $login, 'password' => $password])) {
            return redirect()->intended('/');
        }
        return back()->with('error', 'Email/Username atau password salah');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/login');
    }
}