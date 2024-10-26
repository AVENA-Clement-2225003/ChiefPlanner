<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    <title>@yield('title')</title>
</head>
<body>
<nav>
    <div>
        <h1><a href="/">Chief Planner</a></h1>
    </div>
    <ul>
        <li><a href="/plats">Plats</a></li>
        <li><a href="/ingredients">Ingrédients</a></li>
        <li><a href="/preferences">Préférences</a></li>
        @if(true) <!--#290404 Faire que ca s'affiche que si on est un compte administrateur-->
            <li><a href="/admin">Administration</a></li>
        @endif
    </ul>
</nav>
    @yield('content')
</body>
