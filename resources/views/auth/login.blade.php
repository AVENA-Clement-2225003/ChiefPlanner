@extends('auth.layout')

@section('auth.title', 'Connexion')

@section('auth.content')
    <h2>Connexion</h2>
    <form method="post" action="{{ route('auth.process.connection') }}">
        @csrf
        <label>Mail :
            <input type="email" name="email" placeholder="email@gmail.com" required>
        </label>
        <label>Mot de passe :
            <input type="password" name="pwd" placeholder="Mot de passe" required>
        </label>
        <input type="submit" value="Se connecter">
    </form>
    <a href="{{ route('auth.google') }}" class="btn btn-primary">
        Connexion avec Google
    </a>
    <p>Pas de compte ? <a href="{{ route('auth.inscription') }}">Inscrivez-vous !</a></p>
@endsection
