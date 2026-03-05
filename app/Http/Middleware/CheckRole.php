<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            \Log::error('CheckRole: No authenticated user');
            abort(403, 'Unauthorized action.');
        }

        $allowedRoles = explode('|', $role);
        $userRole = $request->user()->role;
        
        \Log::info('CheckRole: User role=' . $userRole . ', Allowed roles=' . implode(',', $allowedRoles));
        
        if (!in_array($userRole, $allowedRoles)) {
            \Log::error('CheckRole: Role mismatch - User role: ' . $userRole . ' not in allowed roles: ' . implode(',', $allowedRoles));
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
