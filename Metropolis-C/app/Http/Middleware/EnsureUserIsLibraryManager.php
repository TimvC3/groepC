<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsLibraryManager
{
    public function handle(Request $request, Closure $next): Response
{
    if (!in_array($request->user()?->role, ['library_manager'])) {
        abort(403);
    }

    return $next($request);
}
}
