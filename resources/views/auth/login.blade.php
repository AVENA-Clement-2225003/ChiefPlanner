@extends('auth.layout')

@section('auth.title', 'Connexion')

@section('auth.content')
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <div id="g_id_onload"
         data-client_id="829444804686-f203dmng55mtkvm85hhlrsrrrhn371r1.apps.googleusercontent.com"
         data-callback="handleCredentialResponse">
    </div>
    <div class="g_id_signin" data-type="standard"></div>
    <a href="{{ route('auth.google') }}" class="btn btn-primary">
        Connexion avec Google
    </a>
@endsection
