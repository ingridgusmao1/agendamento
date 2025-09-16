<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $r)
    {
        $cred = $r->validate([
            'code'     => 'required',
            'password' => 'required',
        ]);

        if (!Auth::attempt($cred)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        $user  = $r->user();
        $token = $user->createToken('app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user,
        ]);
    }

    public function me(Request $r)
    {
        return $r->user();
    }

    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()?->delete();
        return response()->noContent();
    }
}
