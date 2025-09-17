<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\UserAdminController;

Route::get('/', fn() => redirect()->route('admin.dashboard'));

// Login web por CÓDIGO + senha
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
    Route::get('/products/create', [ProductAdminController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductAdminController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductAdminController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductAdminController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductAdminController::class, 'destroy'])->name('products.destroy');

    // Usuários
    Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
    Route::post('/users', [UserAdminController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserAdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserAdminController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/reset-password', [UserAdminController::class, 'resetPassword'])->name('users.resetPassword');
});
