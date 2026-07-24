<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            \Log::error('CheckRole: No authenticated user');
            abort(403, 'Unauthorized action.');
        }

        // Laravel passes each comma-separated value as a separate argument,
        // so "role:lpo_admin,rfq_approver" gives $roles = ['lpo_admin', 'rfq_approver'].
        // We also handle pipe separators for any routes using "role:lpo_admin|rfq_approver".
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (preg_split('/[,|]/', $role) as $r) {
                $r = trim($r);
                if ($r !== '') {
                    $allowedRoles[] = $r;
                }
            }
        }

        $user = $request->user();

        // Super admins have full access to the system.
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $userRoles = $user->getRolesArray();

        \Log::info('CheckRole: User roles=' . implode(',', $userRoles) . ', Allowed roles=' . implode(',', $allowedRoles));

        $hasAccess = !empty(array_intersect($userRoles, $allowedRoles));

        if (!$hasAccess) {
            \Log::error('CheckRole: Role mismatch - User roles: ' . implode(',', $userRoles) . ' not in allowed roles: ' . implode(',', $allowedRoles));
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
