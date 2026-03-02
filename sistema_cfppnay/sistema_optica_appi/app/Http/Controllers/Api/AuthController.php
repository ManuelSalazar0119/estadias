<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        Log::info('Login request:', $request->all()); // Ver qué datos llegan

        $request->validate([
            'username' => 'required|string',
            'pass' => 'required|string',
        ]);

        $user = Usuario::where('username', $request->username)->first();
        Log::info('User found:', ['user' => $user ? $user->toArray() : null]);

        if (!$user) {
            Log::warning('Usuario no encontrado');
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        if (!Hash::check($request->pass, $user->pass)) {
            Log::warning('Contraseña incorrecta');
            return response()->json([
                'status' => 'error',
                'message' => 'Contraseña incorrecta'
            ], 401);
        }

        Log::info('Login exitoso para usuario:', ['username' => $user->username]);

        return response()->json([
            'status' => 'success',
            'message' => 'Inicio de sesión exitoso',
            'user' => [
                'id_usuario' => $user->id_usuario,
                'nombre' => $user->nombre_usuario,
                'tipo' => $user->tipo_usuario,
            ]
        ]);
    }
}
