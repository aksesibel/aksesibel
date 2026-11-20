<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKnownRole
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(in_array($request->user()?->role?->role_name, Role::PROPOSAL_ROLES, true), 403, 'Peran akun belum memiliki akses internal. Hubungi Pengelola Fasilitas.');

        return $next($request);
    }
}
