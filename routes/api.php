<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController,LookupController,SaleController,InstallmentController};

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/lookup', LookupController::class);

    Route::middleware(['ensure.usertype:vendedor,vendedor_cobrador'])->group(function(){
        Route::apiResource('sales', SaleController::class)->only(['show','store']);
    });
    Route::middleware(['auth:sanctum','ensure.usertype:cobrador,vendedor_cobrador'])->group(function(){
        Route::post('/installments/{installment}/pay',[InstallmentController::class,'pay']);
    });
});
