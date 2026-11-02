<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RequisitionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// Route::post('/supplier', [SupplierController::class, 'store']);
// Route::put('/supplier/{supplier}', [SupplierController::class, 'update']);
// Route::patch('/supplier/{supplier}', [SupplierController::class, 'update']);
// Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('suppliers', SupplierController::class);
});
#Esto genera conceptualmente:
// GET     /api/suppliers              → index()
// POST    /api/suppliers              → store()
// GET     /api/suppliers/{supplier}   → show()
// PUT     /api/suppliers/{supplier}   → update()
// PATCH   /api/suppliers/{supplier}   → update()
// DELETE  /api/suppliers/{supplier}   → destroy()


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class);
});

Route::middleware('auth:sanctum')->group(function(){
    Route::apiResource('Requisitions', RequisitionController::class);
});

// Route::get('/products', [ProductController::class, 'index']);






