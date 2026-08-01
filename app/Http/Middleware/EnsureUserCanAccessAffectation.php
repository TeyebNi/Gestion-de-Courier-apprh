<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessAffectation
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->canAccessAffectation()) {
            abort(403, "Vous n'avez pas accès au module Affectation.");
        }

        return $next($request);
    }
}
