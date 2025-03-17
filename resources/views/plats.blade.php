@php use Illuminate\Support\Facades\Session; @endphp
@extends('layout')

@section('title', 'ChiefPlanner - Plats')

@section('content')
    <div class="dishes-container">
        @if(Session::get('isCreator'))
            <div class="dishes-widget">
                <h2 class="widgetTitle">Ajouter un plat</h2>
                <form method="post" action="/add/dish" class="dishes-form">
                    @csrf
                    <label for="plat_name">Nouveau plat</label>
                    <input type="text" id="plat_name" name="plat_name" placeholder="Nom du plat" required/>
                    
                    <div id="ingredients-container">
                        <div class="ingredient-row">
                            <div>
                                <label for="ingredient_1">Ingrédient</label>
                                <select name="ingredients[0][id_ingredient]" id="ingredient_1" required>
                                    @foreach($ingredients as $ingredient)
                                        <option value="{{ $ingredient->id_ingredient }}">{{ $ingredient->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="quantity_1">Quantité</label>
                                <input type="number" step="0.1" name="ingredients[0][quantity]" id="quantity_1" required>
                            </div>
                            
                            <div>
                                <label for="type_1">Unité</label>
                                <select name="ingredients[0][type]" id="type_1">
                                    <option value="tranche">tranche</option>
                                    <option value="personne">personne</option>
                                    <option value="x">x</option>
                                    <option value="g">g</option>
                                    <option value="ml">ml</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="ingredient-actions">
                        <button type="button" class="ingredient-button add-ingredient" onclick="addIngredient()" title="Ajouter un ingrédient">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                 class="bi bi-plus-square" viewBox="0 0 16 16">
                                <path
                                    d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                                <path
                                    d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                            </svg>
                        </button>
                        <button type="button" class="ingredient-button remove-ingredient" onclick="removeIngredient()" title="Retirer un ingrédient">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                 class="bi bi-dash-square" viewBox="0 0 16 16">
                                <path
                                    d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                                <path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8"/>
                            </svg>
                        </button>
                    </div>

                    <input type="submit" value="Ajouter le plat"/>
                </form>
            </div>
        @endif

        <div class="dishes-widget">
            <h2 class="widgetTitle">Liste des plats</h2>
            <table class="dishes-table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Plat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plats as $plat)
                        <tr>
                            <td>{{ $plat->id_plat }}</td>
                            <td>{{ $plat->nom }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        let ingredientCount = 1;

        function addIngredient() {
            const container = document.getElementById('ingredients-container');
            ingredientCount++;
            const html = `
                <div class="ingredient-row">
                    <div>
                        <label for="ingredient_${ingredientCount}">Ingrédient</label>
                        <select name="ingredients[${ingredientCount}][id_ingredient]" id="ingredient_${ingredientCount}" required>
                            @foreach($ingredients as $ingredient)
                                <option value="{{ $ingredient->id_ingredient }}">{{ $ingredient->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="quantity_${ingredientCount}">Quantité</label>
                        <input type="number" step="0.1" name="ingredients[${ingredientCount}][quantity]" id="quantity_${ingredientCount}" required>
                    </div>
                    
                    <div>
                        <label for="type_${ingredientCount}">Unité</label>
                        <select name="ingredients[${ingredientCount}][type]" id="type_${ingredientCount}">
                            <option value="tranche">tranche</option>
                            <option value="personne">personne</option>
                            <option value="x">x</option>
                            <option value="g">g</option>
                            <option value="ml">ml</option>
                        </select>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeIngredient() {
            const container = document.getElementById('ingredients-container');
            const rows = container.getElementsByClassName('ingredient-row');
            if (rows.length > 1) {
                container.removeChild(rows[rows.length - 1]);
                ingredientCount--;
            }
        }
    </script>
@endsection
