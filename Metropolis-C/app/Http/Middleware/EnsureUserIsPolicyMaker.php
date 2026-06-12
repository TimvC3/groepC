<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPolicyMaker
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== 'policy_maker') {
            abort(403);
        }

        return $next($request);
    }
}
