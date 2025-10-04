@php use Illuminate\Support\Facades\Session; @endphp
@extends('layout')

@section('title', 'ChiefPlanner - Tous les plats')

@section('content')
    <div class="dishes-container">
        <div class="dishes-widget">
            <h2 class="widgetTitle">Tous les plats</h2>
            @if($dishes->isEmpty())
                <p style="text-align: center; padding: 20px; color: #666;">
                    Aucun plat disponible. Créez des plats depuis la page 
                    <a href="{{ route('plats') }}" style="color: #007bff; text-decoration: underline;">Plats</a>.
                </p>
            @else
                <table class="dishes-table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Plat</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dishes as $dish)
                            <tr>
                                <td>{{ $dish->id_plat }}</td>
                                <td>{{ $dish->nom }}</td>
                                <td>
                                    <button class="btn btn-sm" onclick="addToPlaylist({{ $dish->id_plat }}, '{{ $dish->nom }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                                        </svg>
                                        Ajouter à un MealFlow
                                    </button>
                                    @if(Session::get('isAdmin') || Session::get('isCreator'))
                                        <button class="btn btn-sm" onclick="viewDetails({{ $dish->id_plat }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16">
                                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                                <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                                            </svg>
                                            Détails
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- Add to Playlist Modal -->
    <div id="addToPlaylistModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ajouter "<span id="dishName"></span>" à un MealFlow</h3>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            @if($playlists->isEmpty())
                <div class="form-group">
                    <p>Vous n'avez pas encore de MealFlow. Créez-en un depuis la page 
                        <a href="{{ route('playlists.index') }}" style="color: #007bff; text-decoration: underline;">MealFlows</a>.
                    </p>
                </div>
                <div class="form-actions">
                    <button type="button" onclick="closeAddModal()" class="btn btn-outline">Fermer</button>
                </div>
            @else
                <form id="addToPlaylistForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="dish_id" id="dishIdInput">
                    <div class="form-group">
                        <label for="playlistSelect">Sélectionner un MealFlow:</label>
                        <select name="playlist_id" id="playlistSelect" required style="width: 100%; padding: 8px; margin-top: 5px;">
                            <option value="">-- Choisir un MealFlow --</option>
                            @foreach($playlists as $playlist)
                                <option value="{{ $playlist->id_playlist }}">{{ $playlist->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" onclick="closeAddModal()" class="btn btn-outline">Annuler</button>
                        <button type="submit" class="btn btn-success">Ajouter</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Dish Details Modal -->
    @if(Session::get('isAdmin') || Session::get('isCreator'))
    <div id="dishDetailsModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Détails du plat</h3>
                <span class="close" onclick="closeDetailsModal()">&times;</span>
            </div>
            <div id="dishDetailsContent">
                <p style="text-align: center;">Chargement...</p>
            </div>
            <div class="form-actions">
                <button type="button" onclick="closeDetailsModal()" class="btn btn-outline">Fermer</button>
            </div>
        </div>
    </div>
    @endif

    <script>
        let currentDishId = null;

        function addToPlaylist(dishId, dishName) {
            currentDishId = dishId;
            document.getElementById('dishName').textContent = dishName;
            document.getElementById('dishIdInput').value = dishId;
            document.getElementById('addToPlaylistModal').style.display = 'block';
        }

        function closeAddModal() {
            document.getElementById('addToPlaylistModal').style.display = 'none';
            document.getElementById('playlistSelect').value = '';
        }

        // Handle form submission
        document.getElementById('addToPlaylistForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const playlistId = document.getElementById('playlistSelect').value;
            if (!playlistId) {
                alert('Veuillez sélectionner un MealFlow');
                return;
            }
            
            this.action = `/playlists/${playlistId}/add-dish`;
            this.submit();
        });

        @if(Session::get('isAdmin') || Session::get('isCreator'))
        function viewDetails(dishId) {
            document.getElementById('dishDetailsModal').style.display = 'block';
            document.getElementById('dishDetailsContent').innerHTML = '<p style="text-align: center;">Chargement...</p>';
            
            fetch(`/plats/ingredients/${dishId}`)
                .then(response => response.json())
                .then(ingredients => {
                    let html = '<div class="form-group">';
                    html += '<h4>Ingrédients:</h4>';
                    if (ingredients.length === 0) {
                        html += '<p>Aucun ingrédient défini pour ce plat.</p>';
                    } else {
                        html += '<ul style="list-style: none; padding: 0;">';
                        ingredients.forEach(ingredient => {
                            html += `<li style="padding: 5px 0; border-bottom: 1px solid #eee;">
                                <strong>${ingredient.name}</strong>: ${ingredient.quantity}
                            </li>`;
                        });
                        html += '</ul>';
                    }
                    html += '</div>';
                    document.getElementById('dishDetailsContent').innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading dish details:', error);
                    document.getElementById('dishDetailsContent').innerHTML = 
                        '<p style="color: red;">Erreur lors du chargement des détails.</p>';
                });
        }

        function closeDetailsModal() {
            document.getElementById('dishDetailsModal').style.display = 'none';
        }
        @endif

        // Close modals when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addToPlaylistModal');
            @if(Session::get('isAdmin') || Session::get('isCreator'))
            const detailsModal = document.getElementById('dishDetailsModal');
            @endif
            
            if (event.target === addModal) {
                closeAddModal();
            }
            @if(Session::get('isAdmin') || Session::get('isCreator'))
            if (event.target === detailsModal) {
                closeDetailsModal();
            }
            @endif
        }
    </script>
@endsection
