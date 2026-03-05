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

        $userRole = $request->user()->role;

        \Log::info('CheckRole: User role=' . $userRole . ', Allowed roles=' . implode(',', $allowedRoles));

        if (!in_array($userRole, $allowedRoles)) {
            \Log::error('CheckRole: Role mismatch - User role: ' . $userRole . ' not in allowed roles: ' . implode(',', $allowedRoles));
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
