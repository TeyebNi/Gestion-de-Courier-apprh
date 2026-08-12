<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsFatou
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || (! $request->user()->isFatou() && ! $request->user()->isAdmin())) {
            abort(403, "Accès réservé à Fatou.");
        }

        return $next($request);
    }
}
