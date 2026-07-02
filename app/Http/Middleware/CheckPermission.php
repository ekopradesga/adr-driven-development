<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckPermission middleware.
 *
 * Provides route-level permission gating via the 'permission' alias.
 * Usage: ->middleware('permission:user.view')
 *        ->middleware('permission:user.create,user.update')  (any one grants access)
 *
 * Authorization at object level is handled by Policies inside controllers.
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        foreach ($permissions as $permission) {
            if (auth()->user()->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this resource.');
    }
}
