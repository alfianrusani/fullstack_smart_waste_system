<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Cek apakah role user sesuai dengan yang diizinkan di route
        if ($user->role !== $role) {
            // Jika bukan admin tapi mencoba akses halaman admin, lempar ke dashboard user (atau error 403)
            if ($user->role === 'user') {
                return redirect('/user/dashboard')->with('error', 'Akses ditolak. Anda bukan admin.');
            }
            return redirect('/dashboard')->with('error', 'Akses ditolak.');
        }

        return $next($request);
    }
}