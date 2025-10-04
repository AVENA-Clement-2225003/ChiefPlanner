@php use Illuminate\Support\Facades\Session; @endphp
@extends('layout')

@section('title', 'ChiefPlanner - ' . $playlist->name)

@section('content')
    <div class="dishes-container">
        <div class="dishes-widget">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 class="widgetTitle">{{ $playlist->name }}</h2>
                <a href="{{ route('playlists.index') }}" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                    </svg>
                    Retour aux MealFlows
                </a>
            </div>

            @if($playlist->plats->isEmpty())
                <p style="text-align: center; padding: 20px; color: #666;">
                    Ce MealFlow est vide. Ajoutez des plats depuis la page 
                    <a href="{{ route('all-dishes') }}" style="color: #007bff; text-decoration: underline;">Tous les plats</a>.
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
                        @foreach($playlist->plats as $plat)
                            <tr>
                                <td>{{ $plat->id_plat }}</td>
                                <td>{{ $plat->nom }}</td>
                                <td>
                                    <button class="btn btn-danger btn-sm" onclick="removeDish({{ $plat->id_plat }}, '{{ $plat->nom }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1z"/>
                                        </svg>
                                        Retirer
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- Remove Dish Confirmation Modal -->
    <div id="removeDishModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Retirer le plat</h3>
                <span class="close" onclick="closeRemoveModal()">&times;</span>
            </div>
            <div class="form-group">
                <p>Êtes-vous sûr de vouloir retirer "<span id="removeDishName"></span>" de cette playlist ?</p>
            </div>
            <div class="form-actions">
                <button type="button" onclick="closeRemoveModal()" class="btn btn-outline">Annuler</button>
                <form id="removeDishForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Retirer</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function removeDish(dishId, dishName) {
            document.getElementById('removeDishName').textContent = dishName;
            document.getElementById('removeDishForm').action = `/playlists/{{ $playlist->id_playlist }}/remove-dish/${dishId}`;
            document.getElementById('removeDishModal').style.display = 'block';
        }

        function closeRemoveModal() {
            document.getElementById('removeDishModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const removeModal = document.getElementById('removeDishModal');
            if (event.target === removeModal) {
                closeRemoveModal();
            }
        }
    </script>
@endsection
