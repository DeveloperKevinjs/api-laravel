<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        // Auth::attempt autentica con la sesión; las credenciales provienen del FormRequest.
        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'credenciales incorrectas',
            ], 401);
        }

        // Evita reutilizar el identificador de sesión previo a la autenticación.
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Inicio de sesion correcto',
            'user' => $request->user(),
        ]);
    }

    public function logout(Request $request)
    {
        // Cierra la autenticación del guard de sesión utilizado por el login.
        Auth::guard('web')->logout();

        // Descarta los datos de la sesión anterior.
        $request->session()->invalidate();

        // Renueva el token CSRF para las siguientes peticiones web.
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    public function register(StoreUserRequest $request)
    {
        // El cast hashed de User aplica hash a la contraseña; Hidden la excluye del JSON.
        $user = User::create($request->validated());

        return response()->json($user, 201);
    }
}
