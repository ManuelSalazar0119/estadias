<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClienteContrato;
use Illuminate\Support\Facades\Auth;

class ContratoController extends Controller
{
    // Mostrar los contratos no cobrados del cobrador autenticado
    public function contratosNoCobrados()
    {
        $usuario = Auth::user();

        if ($usuario->tipo_usuario !== 'Cobrador') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Obtener contratos asignados al cobrador con estado pendiente y sus folios
        $contratos = ClienteContrato::where('id_cobrador', $usuario->id_usuario)
            ->where('estado_cobranza', 'Pendiente')
            ->with('folio')
            ->get();

        return response()->json([
            'success' => true,
            'contratos' => $contratos
        ]);
    }
}
