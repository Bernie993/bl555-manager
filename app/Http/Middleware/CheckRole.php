<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }
        
        // Check if role starts with ! (negation)
        if (str_starts_with($role, '!')) {
            $excludedRole = substr($role, 1);
            if ($user->role && $user->role->name === $excludedRole) {
                abort(403, 'Forbidden - This role is not allowed to access this resource');
            }
        } else {
            // Check if user has the required role
            if (!$user->role || $user->role->name !== $role) {
                abort(403, 'Forbidden - You do not have the required role to access this resource');
            }
        }
        
        return $next($request);
    }
}