@extends('layout')

@section('title', 'ChiefPlanner - Plats')

@section('content')
    <section>
        <h2>Plats</h2>
        <form method="post" action="/nowhere">
            <label>Nouveau plat :
                <input type="text"/>
            </label>
            <div id="ingredients-container">
                <label for="ingredient_1">Ingredient:</label>
                <select name="ingredients[0][id]" id="ingredient_1">
                    @foreach($ingredients as $ingredient)
                        <option value="{{ $ingredient->id }}">{{ $ingredient->nom }}</option>
                    @endforeach
                </select>
                <label for="quantity_1">Quantity:</label>
                <input type="text" name="ingredients[0][quantity]" id="quantity_1" required>

                <button type="button" class="buttonImg" onclick="addIngredient()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-plus-square" viewBox="0 0 16 16">
                        <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                    </svg>
                </button>
                <button type="button" class="buttonImg" onclick="removeIngredient()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-dash-square" viewBox="0 0 16 16">
                        <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                        <path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8"/>
                    </svg>
                </button>
            </div>
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
    <script>
        let ingredientCount = 1;
        function addIngredient() {
            const container = document.getElementById('ingredients-container');
            ingredientCount++;
            const html = `
            <div class="ingredient-row">
                <label for="ingredient_${ingredientCount}">Ingredient:</label>
                <select name="ingredients[${ingredientCount}][id]" id="ingredient_${ingredientCount}">
                    @foreach($ingredients as $ingredient)
            <option value="{{ $ingredient->id }}">{{ $ingredient->nom }}</option>
                    @endforeach
            </select>
            <label for="quantity_${ingredientCount}">Quantity:</label>
                <input type="text" name="ingredients[${ingredientCount}][quantity]" id="quantity_${ingredientCount}" required>
            </div>
        `;
            container.insertAdjacentHTML('beforeend', html);
        }

        // Function to remove the last added ingredient div
        function removeIngredient() {
            const container = document.getElementById('ingredients-container');
            const rows = container.getElementsByClassName('ingredient-row');

            if (rows.length >= 1) {  // Ensure there's at least one row
                container.removeChild(rows[rows.length - 1]);  // Remove the last added row
                ingredientCount--;  // Decrement the ingredient count
            }
        }
    </script>
@endsection
