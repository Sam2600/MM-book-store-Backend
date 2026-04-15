<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Constants\Auth\AuthConstant;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows only users with role_id = ROLE_ADMIN (1).
 * Apply to all /admin/* routes.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role_id !== AuthConstant::ROLE_ADMIN) {
            return response()->json([
                'status'  => 'NG',
                'message' => 'Forbidden. Admin access required.',
            ], 403);
        }

        return $next($request);
    }
}
