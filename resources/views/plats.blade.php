@extends('layout')

@section('title', 'ChiefPlanner - Plats')

@section('content')
    <section>
        <h2>Plats</h2>
        <form>
            <label>Nouvel ingrédient :
                <input type="text"/>
            </label>
            <input type="submit"/>
        </form>
        <table>
            <thead>
            <tr>
                <td>Id</td>
                <td>Plats</td>
            </tr>
            </thead>
            <tbody>
        @foreach($plats as $plat)
            <tr>
                <td>{{ $plat->id }}</td>
                <td>{{ $plat->nom }}</td>
            </tr>
        @endforeach
            </tbody>
        </table>
    </section>
@endsection
