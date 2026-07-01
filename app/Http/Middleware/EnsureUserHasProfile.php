<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasProfile
{
    public function handle(Request $request, Closure $next, string ...$profiles): Response
    {
        $usuario = $request->user();

        abort_unless($usuario && in_array($usuario->perfil, $profiles, true), 403);

        return $next($request);
    }
}
