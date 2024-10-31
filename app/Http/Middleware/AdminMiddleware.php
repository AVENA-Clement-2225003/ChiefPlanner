<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Session::get('isAdmin'))
        {
            return redirect('/')->with('error', 'Vous n\'êtes pas authorisé à accéder aux fonctionnalités administrateur');
        }
        return $next($request);
    }
}
