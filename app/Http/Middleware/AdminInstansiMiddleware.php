<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminInstansiMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->role === 'developer') {
            return $next($request);
        }

        abort_unless($user !== null && $user->role === 'admin_instansi', 403, 'Akses ditolak.');
        abort_if($user->instansi_id === null, 403, 'Akun admin belum dihubungkan ke instansi.');

        return $next($request);
    }
}
