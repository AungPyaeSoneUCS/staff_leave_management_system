<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user()) {
            abort(403, __('flash.unauthorized'));
        }

        $allowedRoles = [];
        foreach ($roles as $role) {
            $allowedRoles = array_merge($allowedRoles, array_map('trim', explode(',', $role)));
        }

        if (! in_array($request->user()->role, $allowedRoles, true)) {
            abort(403, __('flash.unauthorized'));
        }

        return $next($request);
    }
}
