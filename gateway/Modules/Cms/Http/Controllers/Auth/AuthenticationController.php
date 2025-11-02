<?php

namespace Modules\Cms\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{


    public function authenticate(
        Request $request
    )
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (!$credentials) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        } else {
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();

                return redirect()->intended('dashboard');
            }
        }


    }
}
