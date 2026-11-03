<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Sanctum reconoce la sesión autenticada; no se emiten tokens Bearer en el login.
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Registro e inicio de sesión públicos; los recursos del ERP exigen autenticación.
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// apiResource conecta las operaciones CRUD con los métodos del controlador.
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('suppliers', SupplierController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class);
});

// Lectura y creación de categorías; actualización y eliminación siguen pendientes.
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show', 'store']);
});

// Se conserva la mayúscula de la URL. Solo store está implementado actualmente.
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('Requisitions', RequisitionController::class);
});
