<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReportController; 
use App\Http\Controllers\Api\AuthController;   

// Rute Publik (Login & Register)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rute Terlindungi (Wajib menyertakan Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rute Laporan Sampah (Aman & Terikat ke User yang Login)
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports', [ReportController::class, 'store']);
    Route::put('/reports/{id}/status', [ReportController::class, 'updateStatus']);

    // Rute Profil Pengguna
    Route::put('/profile/update', [\App\Http\Controllers\Web\UserController::class, 'updateProfile']);
});