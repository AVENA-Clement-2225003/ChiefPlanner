@extends('layout')

@section('title', 'ChiefPlanner - Ingrédients')

@section('content')
    <section>
        <h2>Ingrédients</h2>
        <form method="post" action="/add/ingredient">
            @csrf
            <label>Nouvel ingrédient :
                <input type="text" name="ingredient_name"/>
            </label>
            <input type="submit"/>
        </form>
        <table>
            <thead>
            <tr>
                <td>Id</td>
                <td>Ingrédient</td>
            </tr>
            </thead>
            <tbody>
            @foreach($ingredients as $ingredient)
                <tr>
                    <td>{{ $ingredient->id }}</td>
                    <td>{{ $ingredient->nom }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>
@endsection
