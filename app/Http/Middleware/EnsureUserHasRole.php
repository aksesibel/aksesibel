<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk membatasi akses berdasarkan nama role.
 *
 * Penggunaan di route: ->middleware(EnsureUserHasRole::class . ':Admin,Petugas Tindak Lanjut')
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role) {
            abort(403, 'Akses ditolak.');
        }

        if (! in_array($user->role->role_name, $roles, true)) {
            abort(403, 'Anda tidak memiliki kewenangan untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}

