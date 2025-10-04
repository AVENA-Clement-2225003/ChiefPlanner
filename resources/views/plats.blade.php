@php use Illuminate\Support\Facades\Session; @endphp
@extends('layout')

@section('title', 'ChiefPlanner - Plats')

@section('content')
    <div class="dishes-container">
        @if(Session::get('isCreator'))
            <div class="dishes-widget">
                <h2 class="widgetTitle">Ajouter un plat</h2>
                <form method="post" action="/add/dish" class="dishes-form" id="addDishForm">
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

                    <div class="form-group" style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
                        <label style="display: flex; align-items: center; gap: 10px; font-weight: 600; color: var(--dark); cursor: pointer; font-size: 1rem;">
                            <input type="checkbox" id="addToPlaylistCheck" onchange="togglePlaylistSelect()" style="width: 18px; height: 18px; cursor: pointer;">
                            <span>Ajouter à un MealFlow</span>
                        </label>
                        <div id="playlistSelectContainer" style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px solid #dee2e6;">
                            <label for="playlist_id" style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--dark);">Sélectionner un MealFlow:</label>
                            <select name="playlist_id" id="playlist_id" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 1rem; background-color: white;">
                                <option value="">-- Aucun --</option>
                            </select>
                        </div>
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
                        @if(Session::get('isAdmin') || Session::get('isCreator'))
                            <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($plats as $plat)
                        <tr>
                            <td>{{ $plat->id_plat }}</td>
                            <td>{{ $plat->nom }}</td>
                            @if(Session::get('isAdmin') || Session::get('isCreator'))
                                <td>
                                    <button class="btn btn-sm" onclick="editDish({{ $plat->id_plat }}, '{{ $plat->nom }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                            <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                                        </svg>
                                        Modifier
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteDish({{ $plat->id_plat }}, '{{ $plat->nom }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1z"/>
                                        </svg>
                                        Supprimer
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Dish Modal -->
    @if(Session::get('isAdmin') || Session::get('isCreator'))
    <div id="editDishModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Modifier le plat</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editDishForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="editDishName">Nom du plat:</label>
                    <input type="text" id="editDishName" name="dish_name" required>
                </div>
                <div class="form-group">
                    <label>Ingrédients:</label>
                    <div id="editIngredientsList"></div>
                    <button type="button" onclick="addEditIngredientRow()" class="btn-sm">Ajouter un ingrédient</button>
                </div>
                <div class="form-actions">
                    <button type="button" onclick="closeEditModal()" class="btn btn-outline">Annuler</button>
                    <button type="submit" class="btn btn-success">Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteDishModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Supprimer le plat</h3>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <div class="form-group">
                <p>Êtes-vous sûr de vouloir supprimer le plat "<span id="deleteDishName"></span>" ?</p>
                <p><strong>Cette action est irréversible.</strong></p>
            </div>
            <div class="form-actions">
                <button type="button" onclick="closeDeleteModal()" class="btn btn-outline">Annuler</button>
                <form id="deleteDishForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script>
        let ingredientCount = 1;
        let userPlaylists = [];

        // Load playlists on page load
        @if(Session::get('isCreator'))
        document.addEventListener('DOMContentLoaded', function() {
            loadPlaylists();
        });

        function loadPlaylists() {
            fetch('/api/playlists')
                .then(response => response.json())
                .then(playlists => {
                    userPlaylists = playlists;
                    const select = document.getElementById('playlist_id');
                    select.innerHTML = '<option value="">-- Aucune --</option>';
                    playlists.forEach(playlist => {
                        const option = document.createElement('option');
                        option.value = playlist.id_playlist;
                        option.textContent = playlist.name;
                        select.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading playlists:', error));
        }

        function togglePlaylistSelect() {
            const checkbox = document.getElementById('addToPlaylistCheck');
            const container = document.getElementById('playlistSelectContainer');
            container.style.display = checkbox.checked ? 'block' : 'none';
        }

        // Handle form submission to add dish to playlist
        document.getElementById('addDishForm').addEventListener('submit', function(e) {
            const playlistId = document.getElementById('playlist_id').value;
            if (playlistId && document.getElementById('addToPlaylistCheck').checked) {
                // Store playlist ID to add after dish creation
                sessionStorage.setItem('pendingPlaylistAdd', playlistId);
            }
        });
        @endif

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

        @if(Session::get('isAdmin') || Session::get('isCreator'))
        // Edit dish functionality
        let currentEditDishId = null;
        let availableIngredients = @json($ingredients);

        function editDish(dishId, dishName) {
            currentEditDishId = dishId;
            document.getElementById('editDishName').value = dishName;
            document.getElementById('editDishForm').action = `/plats/update/${dishId}`;
            
            // Load current ingredients
            loadDishIngredients(dishId);
            
            document.getElementById('editDishModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editDishModal').style.display = 'none';
            document.getElementById('editIngredientsList').innerHTML = '';
        }

        function loadDishIngredients(dishId) {
            fetch(`/plats/ingredients/${dishId}`)
                .then(response => response.json())
                .then(ingredients => {
                    const container = document.getElementById('editIngredientsList');
                    container.innerHTML = '';
                    
                    ingredients.forEach(ingredient => {
                        addEditIngredientRow(ingredient);
                    });
                    
                    if (ingredients.length === 0) {
                        addEditIngredientRow();
                    }
                })
                .catch(error => {
                    console.error('Error loading ingredients:', error);
                    addEditIngredientRow();
                });
        }

        function addEditIngredientRow(ingredient = null) {
            const container = document.getElementById('editIngredientsList');
            const row = document.createElement('div');
            row.className = 'ingredient-row';
            
            // Ingredient select
            const ingredientDiv = document.createElement('div');
            const ingredientLabel = document.createElement('label');
            ingredientLabel.textContent = 'Ingrédient';
            const ingredientSelect = document.createElement('select');
            ingredientSelect.name = 'ingredients[][id_ingredient]';
            ingredientSelect.required = true;
            
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Sélectionner un ingrédient';
            ingredientSelect.appendChild(defaultOption);
            
            availableIngredients.forEach(ing => {
                const option = document.createElement('option');
                option.value = ing.id_ingredient;
                option.textContent = ing.nom;
                if (ingredient && ingredient.id_ingredient == ing.id_ingredient) {
                    option.selected = true;
                }
                ingredientSelect.appendChild(option);
            });
            
            ingredientDiv.appendChild(ingredientLabel);
            ingredientDiv.appendChild(ingredientSelect);
            
            // Quantity input
            const quantityDiv = document.createElement('div');
            const quantityLabel = document.createElement('label');
            quantityLabel.textContent = 'Quantité';
            const quantityInput = document.createElement('input');
            quantityInput.type = 'number';
            quantityInput.step = '0.1';
            quantityInput.name = 'ingredients[][quantity]';
            quantityInput.required = true;
            
            // Parse existing quantity to extract number and unit
            let quantityValue = '';
            let unitValue = 'g';
            if (ingredient && ingredient.quantity) {
                const match = ingredient.quantity.match(/^(\d+(?:\.\d+)?)\s*(.*)$/);
                if (match) {
                    quantityValue = match[1];
                    unitValue = match[2] || 'g';
                }
            }
            quantityInput.value = quantityValue;
            
            quantityDiv.appendChild(quantityLabel);
            quantityDiv.appendChild(quantityInput);
            
            // Unit select
            const unitDiv = document.createElement('div');
            const unitLabel = document.createElement('label');
            unitLabel.textContent = 'Unité';
            const unitSelect = document.createElement('select');
            unitSelect.name = 'ingredients[][type]';
            
            const units = ['tranche', 'personne', 'x', 'g', 'ml'];
            units.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit;
                option.textContent = unit;
                if (unit === unitValue) {
                    option.selected = true;
                }
                unitSelect.appendChild(option);
            });
            
            unitDiv.appendChild(unitLabel);
            unitDiv.appendChild(unitSelect);
            
            // Remove button
            const actionDiv = document.createElement('div');
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn-danger btn-sm';
            removeBtn.textContent = 'Supprimer';
            removeBtn.onclick = () => row.remove();
            actionDiv.appendChild(removeBtn);
            
            row.appendChild(ingredientDiv);
            row.appendChild(quantityDiv);
            row.appendChild(unitDiv);
            row.appendChild(actionDiv);
            container.appendChild(row);
        }

        // Delete dish functionality
        function deleteDish(dishId, dishName) {
            document.getElementById('deleteDishName').textContent = dishName;
            document.getElementById('deleteDishForm').action = `/plats/delete/${dishId}`;
            document.getElementById('deleteDishModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteDishModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editDishModal');
            const deleteModal = document.getElementById('deleteDishModal');
            if (event.target === editModal) {
                closeEditModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        }
        @endif
    </script>
@endsection
