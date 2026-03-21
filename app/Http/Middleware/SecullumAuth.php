<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecullumAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->get('secullum_autenticado')) {
            return redirect()
                ->route('login')
                ->with('error', 'Faça login para acessar o sistema.');
        }

        return $next($request);
    }
}