<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ScrambleAuthMiddleware
{
    /**
     * Handle an incoming request for Scramble docs
     */
    public function handle(Request $request, Closure $next)
    {
        // En développement, on autorise l'accès libre à la documentation
        if (app()->environment('local')) {
            return $next($request);
        }

        // En production, vous pouvez ajouter une authentification
        // Par exemple, vérifier si l'utilisateur est admin
        
        return $next($request);
    }
}