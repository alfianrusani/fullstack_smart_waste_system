<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\WebAuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\UserController;

// ==========================================
// RUTE DEFAULT (Root URL)
// ==========================================
// Jika mengakses http://127.0.0.1:8000/ akan otomatis diarahkan ke halaman login
Route::get('/', function () {
    return redirect('/login');
});

// ==========================================
// RUTE AUTHENTICATION (WEB)
// ==========================================
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
Route::post('/register', [WebAuthController::class, 'register'])->name('register');


// ==========================================
// RUTE KHUSUS ADMIN (Menggunakan DashboardController)
// ==========================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    
    // Dashboard Utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admins.dashboard');

    // Manajemen Laporan
    Route::get('/reports', [DashboardController::class, 'reports'])->name('admins.reports');
    Route::post('/reports/update-status/{id}', [DashboardController::class, 'updateStatus'])->name('admins.reports.update');
    Route::delete('/reports/delete/{id}', [DashboardController::class, 'deleteReport'])->name('admins.reports.delete');

    // Manajemen Pengguna
    Route::get('/users', [DashboardController::class, 'users'])->name('admins.users');
    Route::get('/users/create', [DashboardController::class, 'createUser'])->name('admins.create');
    Route::post('/users/store', [DashboardController::class, 'storeUser'])->name('admins.users.store');
    Route::get('/users/edit/{id}', [DashboardController::class, 'editUser'])->name('admins.users.edit');
    Route::put('/users/update/{id}', [DashboardController::class, 'updateUser'])->name('admins.users.update');
    Route::delete('/users/delete/{id}', [DashboardController::class, 'deleteUser'])->name('admins.users.delete');
});


// ==========================================
// RUTE KHUSUS USER BIASA
// ==========================================
Route::middleware(['auth', 'role:user'])->prefix('user')->group(function () {
    
    // Dashboard Users
    Route::get('/dashboard', [\App\Http\Controllers\Web\UserController::class, 'index'])->name('user.dashboard');

    // Laporan Pengguna (User)
    Route::get('/reports', [\App\Http\Controllers\Web\UserController::class, 'reports'])->name('user.reports');

    // Pengaturan Akun / Profil (Menampilkan Form Edit Akun)
    Route::get('/profile', [\App\Http\Controllers\Web\UserController::class, 'profile'])->name('user.profile');
    
    // Proses Update Akun
    Route::put('/profile/update', [\App\Http\Controllers\Web\UserController::class, 'updateProfile'])->name('user.profile.update');
});

