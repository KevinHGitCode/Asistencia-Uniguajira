<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->role, $this->expandRoles($roles), true)) {
            abort(403, 'No tienes permisos para acceder.');
        }

        return $next($request);
    }

    /**
     * @param  array<int, string>  $roles
     * @return array<int, string>
     */
    private function expandRoles(array $roles): array
    {
        if (in_array(User::ROLE_ADMIN, $roles, true) && ! in_array(User::ROLE_SUPERADMIN, $roles, true)) {
            $roles[] = User::ROLE_SUPERADMIN;
        }

        return $roles;
    }
}
