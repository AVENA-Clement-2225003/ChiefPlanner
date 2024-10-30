<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Session::has('user_id'))
        {
            return redirect(route('auth.connection'))->with('error', 'Vous n\'êtes pas connecté');
        }
        return $next($request);
    }
}
