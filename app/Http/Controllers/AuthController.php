<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    Public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if(!Auth::attempt($credentials)){
            return response()->json([
                'message' => 'credenciales incorrectas'
            ],401);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Inicio de sesion correcto',
            'user'    => $request->user()
        ]);

    }

    public function logout(Request $request)
    {
        //Cierra  la autenticacion actual
        Auth::guard('web')->logout();

        //Invalida la sesion actual
        $request->session()->invalidate();

        //Genera un nuevo token CSRF
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.'
        ]);

    }

    public function register(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        return response()->json($user,201);
    }
}
