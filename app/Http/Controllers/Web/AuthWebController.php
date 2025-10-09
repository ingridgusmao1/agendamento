<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthWebController extends Controller
{
    public function showLogin()
    {
        return view('auth.login-code');
    }

    public function doLogin(Request $r)
    {
        $data = $r->validate([
            'code' => 'required|string',
            'password' => 'required|string',
        ]);

        // Guard web, autenticando por "code"
        if (Auth::attempt(['code' => $data['code'], 'password' => $data['password']], $r->boolean('remember'))) {
            $r->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['code' => 'Código ou senha inválidos.'])->onlyInput('code');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('login');
    }
}
