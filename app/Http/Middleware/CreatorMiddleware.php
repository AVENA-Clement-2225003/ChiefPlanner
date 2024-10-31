<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CreatorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!(Session::get('isCreator') || Session::get('isAdmin')))
        {
            return redirect('/')->with('error', 'Vous n\'êtes pas authorisé à créer des plats');
        }
        return $next($request);
    }
}
