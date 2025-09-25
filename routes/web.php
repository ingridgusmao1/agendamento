<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\CustomerAdminController;
use App\Http\Controllers\Admin\SaleAdminController;

Route::get('/', fn() => redirect()->route('admin.dashboard'));

// Login web por código + senha
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthWebController::class, 'doLogin'])->name('login.post');
});

// Fallback
Route::fallback(function () {
    return redirect()->route('admin.dashboard');
});

Route::post('/logout', [AuthWebController::class, 'logout'])->middleware('auth')->name('logout');

// Painel ADMIN
Route::prefix('admin')->middleware(['auth','ensure.usertype:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');

    // Produtos
    Route::get('/products', [ProductAdminController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductAdminController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductAdminController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductAdminController::class, 'destroy'])->name('products.destroy');

    // Paginação de produtos
    Route::get('/products/fetch', [ProductAdminController::class, 'fetch'])->name('products.fetch');

    //------------------------------------------------------------------------------------------------------------------------

    // Usuários
    Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
    Route::post('/users', [UserAdminController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserAdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserAdminController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/reset-password', [UserAdminController::class, 'resetPassword'])->name('users.resetPassword');

    // Paginação de usuários
    Route::get('/users/fetch', [UserAdminController::class, 'fetch'])->name('users.fetch');

    //------------------------------------------------------------------------------------------------------------------------

    // Galeria
    Route::get('products/{product}/gallery', [ProductAdminController::class, 'gallery'])->name('products.gallery');
    Route::get('products/{product}/images', [ProductAdminController::class, 'images'])->name('products.images');
    Route::post('products/{product}/images', [ProductAdminController::class, 'uploadImages'])->name('products.images.upload');
    Route::delete('products/{product}/images/{index}', [ProductAdminController::class, 'deleteImage'])->name('products.images.delete');

    // Upload (usa 'photos[]' no request, conforme o validator)
    Route::post('products/{product}/images', [ProductAdminController::class, 'uploadImages'])->name('products.images.upload');

    // Remoção em lote (sem {index})
    Route::delete('products/{product}/images', [ProductAdminController::class, 'deleteImagesBatch'])->name('products.images.batchDelete');

    // (Opcional) manter a remoção por índice individual, se ainda usar em algum lugar:
    Route::delete('products/{product}/images/{index}', [ProductAdminController::class, 'deleteImage'])->name('products.images.delete');

    //------------------------------------------------------------------------------------------------------------------------

    // Clientes
    Route::get('/customers',        [CustomerAdminController::class, 'index'])->name('customers.index');
    Route::get('/customers/fetch',  [CustomerAdminController::class, 'fetch'])->name('customers.fetch');
    Route::post('/customers',       [CustomerAdminController::class, 'store'])->name('customers.store');
    Route::put('/customers/{customer}', [CustomerAdminController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerAdminController::class, 'destroy'])->name('customers.destroy');

    //------------------------------------------------------------------------------------------------------------------------

    // Notas de venda
    Route::get('/sales',               [SaleAdminController::class, 'index'])->name('sales.index');
    Route::get('/sales/fetch',         [SaleAdminController::class, 'fetch'])->name('sales.fetch');
    Route::get('/sales/{sale}',        [SaleAdminController::class, 'show'])->name('sales.show');
    Route::delete('/sales/{sale}',     [SaleAdminController::class, 'destroy'])->name('sales.destroy');

    //------------------------------------------------------------------------------------------------------------------------

    // Relatórios financeiros
    Route::get('/financial-reports', [SaleAdminController::class, 'financialReports'])->name('financial-reports.index');

});
