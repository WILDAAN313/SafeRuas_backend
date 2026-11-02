<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            if ($request->expectsJson()) {
                throw new AuthenticationException;
            }

            return redirect()->guest(route('login'));
        }

        if ($user->role !== 'super_admin') {
            if ($user->role === 'admin_instansi' && ! $request->expectsJson()) {
                return redirect()->route('admin.dashboard');
            }

            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
