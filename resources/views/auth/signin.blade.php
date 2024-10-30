@extends('auth.layout')

@section('auth.title', 'Inscription')

@section('auth.content')
    <h2>Inscription</h2>
    <form method="post" action="{{ route('auth.process.inscription') }}">
        @csrf
        <label>Mail :
            <input type="email" name="email" placeholder="email@gmail.com" required>
        </label>
        <label>Prénom :
            <input type="text" name="name" placeholder="James" required>
        </label>
        <label>Mot de passe :
            <input type="password" name="pwd" placeholder="Mot de passe" required>
        </label>
        <label>Confirmation :
            <input type="password" name="pwdConf" placeholder="Mot de passe" required>
        </label>
        <input type="submit" value="S'inscrire">
    </form>
    <a href="{{ route('auth.google') }}" class="btn btn-primary">
        Inscription avec Google
    </a>
    <p>Déjà un compte ? <a href="{{ route('auth.connection') }}">Connectez-vous !</a></p>
@endsection
