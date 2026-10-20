<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Redirect halaman utama ke kasir
Route::get('/', function () {
    return redirect()->route('pos.create');
});

// Route khusus Tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    // Tantangan Mandiri: Halaman informasi khusus tamu
    Route::get('/info', function () {
        return '<h1>Selamat Datang di Simple POS</h1><p>Halaman ini hanya bisa diakses oleh tamu yang belum login. Silakan <a href="' . route('login') . '">Login di sini</a>.</p>';
    })->name('info');
});

// Route yang membutuhkan Autentikasi (sudah login)
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Fitur Kasir (Dapat diakses oleh Kasir, Admin, & Manager)
    Route::get('/pos', [TransactionController::class, 'create'])->name('pos.create');
    Route::post('/pos', [TransactionController::class, 'store'])->name('transactions.store');

    // Tantangan Mandiri: Fitur Transaksi (Dapat diakses Admin & Manager)
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    });

    // Fitur Produk & Kategori (Hanya dapat diakses Admin)
    Route::middleware('role:admin')->group(function () {
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('products', ProductController::class)->except(['show', 'destroy']);
    });
});