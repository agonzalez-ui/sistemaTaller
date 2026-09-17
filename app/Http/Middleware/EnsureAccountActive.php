<?php

namespace App\Http\Middleware;

use App\Support\SecurityAudit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $user->refresh();
            $user->load('role');

            if (! $user->active || ($user->role_id && (! $user->role || ! $user->role->active))) {
                SecurityAudit::closeAccess($request, 'DISABLED');
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Su cuenta o rol está deshabilitado. Contacte al administrador.');
            }
        }

        return $next($request);
    }
}
