<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireModulePermission
{
    public function handle(Request $request, Closure $next, string $module, ?string $permission = null): Response
    {
        $action = $permission ?? match ($request->route()->getActionMethod()) {
            'create', 'store' => 'create',
            'edit', 'update' => 'edit',
            'destroy' => 'delete',
            default => 'view',
        };
        abort_unless($request->user()?->hasModulePermission($module, $action), 403, 'No tiene permiso para realizar esta acción.');

        return $next($request);
    }
}
