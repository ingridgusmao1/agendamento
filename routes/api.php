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

// Público
Route::post('/auth/login', [AuthController::class, 'login']);

// Privado (tudo aqui exige token Sanctum)
Route::prefix('')->middleware(['auth:sanctum'])->group(function () {
    // sessão
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // lookup pode ser usado por todos os perfis que operam o app
    Route::get('/lookup', LookupController::class)->middleware('ensure.usertype:admin,vendedor,cobrador,vendedor_cobrador');

    // Produtos: usado no fluxo de VENDAS
    Route::middleware(['ensure.usertype:admin,vendedor,vendedor_cobrador'])->group(function () {
        Route::get('/products', [ProductController::class, 'index']);
        // Vendas (criar nova, ver detalhes)
        Route::apiResource('sales', SaleController::class)->only(['show','store']);
    });

    // Cobrança: pagar parcela
    Route::middleware(['ensure.usertype:cobrador,vendedor_cobrador'])->group(function () {
        Route::post('/installments/{installment}/pay', [InstallmentController::class, 'pay']);
    });

    Route::post('/customers', [CustomerController::class, 'store'])->middleware('ensure.usertype:admin,vendedor,vendedor_cobrador');
});
