<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Consulta pública: el binding busca por documento y show devuelve JSON, no una vista.
Route::get('/supplier/{supplier:document_number}', [SupplierController::class, 'show']);

// Las rutas web usan sesión y la protección CSRF del middleware web.
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
