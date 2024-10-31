@php use Illuminate\Support\Facades\Session; @endphp
@extends('layout')

@section('title', 'ChiefPlanner - Ingrédients')

@section('content')
    <section>
        <h2>Ingrédients</h2>
        @if(Session::get('isCreator'))
            <form method="post" action="/add/ingredient" class="widget">
                @csrf
                <label>Nouvel ingrédient :
                    <input type="text" name="ingredient_name"/>
                </label>
                <input type="submit"/>
            </form>
        @endif
        <table class="widget">
            <thead>
            <tr>
                <td>Id</td>
                <td>Ingrédient</td>
            </tr>
            </thead>
            <tbody>
            @foreach($ingredients as $ingredient)
                <tr>
                    <td>{{ $ingredient->id_ingredient }}</td>
                    <td>{{ $ingredient->nom }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>
@endsection
