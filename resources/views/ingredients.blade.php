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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ingredients as $ingredient)
                        <tr data-ingredient-id="{{ $ingredient->id_ingredient }}">
                            <td>{{ $ingredient->id_ingredient }}</td>
                            <td class="ingredient-name">{{ $ingredient->nom }}</td>
                            <td>
                                @if(Session::get('isCreator'))
                                    <button type="button" class="admin-button edit" title="Modifier l'ingrédient">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                            <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                            <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                                        </svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Ingredient Modal -->
    <div id="editIngredientModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Modifier l'ingrédient</h2>
                <span class="close">&times;</span>
            </div>
            <form id="editIngredientForm" method="post">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_ingredient_id" name="ingredient_id">
                <div class="form-group">
                    <label for="edit_ingredient_name">Nom de l'ingrédient</label>
                    <input type="text" id="edit_ingredient_name" name="ingredient_name" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn submit-button">Enregistrer</button>
                    <button type="button" class="btn" style="background-color: #aaa; color: white;">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the modal
            const modal = document.getElementById('editIngredientModal');
            const form = document.getElementById('editIngredientForm');
            const closeBtn = modal.querySelector('.close');
            const cancelBtn = modal.querySelector('button[type="button"].btn');
            
            // Get all edit buttons
            const editButtons = document.querySelectorAll('.admin-button.edit');
            
            // Add click event to all edit buttons
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const ingredientId = row.dataset.ingredientId;
                    const ingredientName = row.querySelector('.ingredient-name').textContent;
                    
                    // Set form values
                    document.getElementById('edit_ingredient_id').value = ingredientId;
                    document.getElementById('edit_ingredient_name').value = ingredientName;
                    
                    // Set form action
                    form.action = `/ingredients/update/${ingredientId}`;
                    
                    // Show modal
                    modal.style.display = 'flex';
                });
            });
            
            // Close modal when clicking on X or Cancel
            closeBtn.addEventListener('click', () => modal.style.display = 'none');
            cancelBtn.addEventListener('click', () => modal.style.display = 'none');
            
            // Close modal when clicking outside of it
            window.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
            
            // Prevent form submission from closing modal if validation fails
            form.addEventListener('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
