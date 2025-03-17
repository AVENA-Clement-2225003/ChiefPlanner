@php use Illuminate\Support\Facades\Session; @endphp
@extends('layout')

@section('title', 'ChiefPlanner - Ingrédients')

@section('content')
    <div class="ingredients-container">
        @if(Session::get('isCreator'))
            <div class="ingredients-widget">
                <h2 class="widgetTitle">Ajouter un ingrédient</h2>
                <form method="post" action="/add/ingredient" class="ingredients-form">
                    @csrf
                    <label for="ingredient_name">Nouvel ingrédient</label>
                    <input type="text" id="ingredient_name" name="ingredient_name" placeholder="Nom de l'ingrédient" required/>
                    <input type="submit" value="Ajouter"/>
                </form>
            </div>
        @endif

        <div class="ingredients-widget">
            <h2 class="widgetTitle">Liste des ingrédients</h2>
            <table class="ingredients-table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Ingrédient</th>
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
        </div>
    </div>
@endsection
