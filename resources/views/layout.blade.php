@php use Illuminate\Support\Facades\Session; @endphp
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    <title>@yield('title')</title>
    <script src="{{ asset('js/theme-toggle.js') }}"></script>
</head>
<body>
<nav>
    <div>
        <h1><a href="/">Chief Planner</a></h1>
    </div>
    <div class="theme-toggle-wrapper">
        <button id="theme-toggle" aria-label="Toggle dark mode">
            <span class="theme-toggle-icon light-icon">☀</span>
            <span class="theme-toggle-icon dark-icon">☾</span>
        </button>
    </div>
    <ul>
        @if(!Session::has('user_id'))
            <li><a href="{{ route('auth.connection') }}">Se connecter</a></li>
        @else
            <li><a href="/plats">Plats</a></li>
            <li><a href="/ingredients">Ingrédients</a></li>
            <li><a href="/playlists">MealFlows</a></li>
            <li><a href="/all-dishes">Tous les plats</a></li>
            <li><a href="/preferences">Préférences</a></li>
            @if(Session::has('isAdmin') && Session::get('isAdmin'))
                <li><a href="/admin">Administration</a></li>
            @endif
            <li><a href="{{ route('auth.logout') }}">Se déconnecter</a></li>
        @endif
    </ul>
</nav>
@if(Session::has('success'))
    <div class="alertDiv alertSuccess"><p>{{ Session::get('success') }}</p></div>
@elseif(Session::has('error'))
    <div class="alertDiv alertError"><p>{{ Session::get('error') }}</p></div>
@endif
@yield('content')
</body>
