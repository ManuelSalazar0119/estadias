<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/cobrador/contratos-no-cobrados/{idCobrador}', [ContratoController::class, 'contratosNoCobrados']);
