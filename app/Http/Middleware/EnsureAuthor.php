<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Constants\Auth\AuthConstant;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows only users with role_id = ROLE_AUTHOR (3) or ROLE_ADMIN (1).
 * Admins can always perform author actions.
 * Apply to novel/chapter write routes.
 */
class EnsureAuthor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $authorizedRoles = [AuthConstant::ROLE_ADMIN, AuthConstant::ROLE_AUTHOR];

        if (!$user || !in_array($user->role_id, $authorizedRoles, true)) {
            return response()->json([
                'status'  => 'NG',
                'message' => 'Forbidden. Author access required.',
            ], 403);
        }

        return $next($request);
    }
}
