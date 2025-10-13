<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    LookupController,
    SaleController,
    InstallmentController,
    ProductController,
    CustomerController,
};

// ------------------------- PÚBLICO -------------------------
Route::post('/auth/login', [AuthController::class, 'login']);

// ------------------------- PRIVADO -------------------------
Route::middleware(['auth:sanctum'])->group(function () {

    // Sessão
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Lookup (buscas rápidas)
    Route::get('/lookup', LookupController::class)
        ->middleware('ensure.usertype:admin,vendedor,cobrador,vendedor_cobrador');

    // ----------------- VENDAS -----------------
    // Detalhe da venda (liberado também para cobrador)
    Route::get('/sales/{sale}', [SaleController::class, 'show'])
        ->middleware('ensure.usertype:admin,vendedor,cobrador,vendedor_cobrador');

    // Criar venda (sem cobrador)
    Route::post('/sales', [SaleController::class, 'store'])
        ->middleware('ensure.usertype:admin,vendedor,vendedor_cobrador');

    // ----------------- COBRANÇA -----------------
    Route::middleware(['ensure.usertype:cobrador,vendedor_cobrador'])->group(function () {
        Route::post('/installments/{installment}/pay', [InstallmentController::class, 'pay']);
    });

    // ----------------- CLIENTES -----------------
    // Mostrar cliente (usado na "Ficha do cliente")
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])
        ->middleware('ensure.usertype:admin,vendedor,cobrador,vendedor_cobrador');

    // Criar cliente
    Route::post('/customers', [CustomerController::class, 'store'])
        ->middleware('ensure.usertype:admin,vendedor,vendedor_cobrador');

    // ----------------- PRODUTOS (ESTOQUE) -----------------
    Route::middleware(['ensure.usertype:admin,vendedor,vendedor_cobrador'])->group(function () {
        Route::get('/products', [ProductController::class, 'index']);
        Route::post('/products/{product}/photos', [ProductController::class, 'storePhotos']);
        Route::delete('/products/{product}/photos', [ProductController::class, 'destroyPhotos']);
    });
});
