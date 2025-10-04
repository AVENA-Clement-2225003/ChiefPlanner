@php use Illuminate\Support\Facades\Session; @endphp
@extends('layout')

@section('title', 'ChiefPlanner - MealFlows')

@section('content')
    <div class="dishes-container">
        <!-- Create Playlist Widget -->
        <div class="dishes-widget">
            <h2 class="widgetTitle">Créer un MealFlow</h2>
            <form method="post" action="{{ route('playlists.store') }}" class="dishes-form">
                @csrf
                <label for="playlist_name">Nom du MealFlow</label>
                <input type="text" id="playlist_name" name="name" placeholder="Nom du MealFlow" maxlength="50" required/>
                <input type="submit" value="Créer le MealFlow"/>
            </form>
        </div>

        <!-- Playlists List Widget -->
        <div class="dishes-widget">
            <h2 class="widgetTitle">Mes MealFlows</h2>
            @if($playlists->isEmpty())
                <p style="text-align: center; padding: 20px; color: #666;">
                    Aucun MealFlow créé. Créez votre premier MealFlow pour organiser vos plats !
                </p>
            @else
                <table class="dishes-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Nombre de plats</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($playlists as $playlist)
                            <tr>
                                <td>{{ $playlist->name }}</td>
                                <td>{{ $playlist->plats()->count() }}</td>
                                <td>
                                    <a href="{{ route('playlists.show', $playlist->id_playlist) }}" class="btn btn-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                        </svg>
                                        Voir
                                    </a>
                                    <button class="btn btn-sm" onclick="editPlaylist({{ $playlist->id_playlist }}, '{{ $playlist->name }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                            <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                                        </svg>
                                        Modifier
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deletePlaylist({{ $playlist->id_playlist }}, '{{ $playlist->name }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1z"/>
                                        </svg>
                                        Supprimer
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- Edit Playlist Modal -->
    <div id="editPlaylistModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Modifier le MealFlow</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editPlaylistForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="editPlaylistName">Nom du MealFlow:</label>
                    <input type="text" id="editPlaylistName" name="name" maxlength="50" required>
                </div>
                <div class="form-actions">
                    <button type="button" onclick="closeEditModal()" class="btn btn-outline">Annuler</button>
                    <button type="submit" class="btn btn-success">Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deletePlaylistModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Supprimer le MealFlow</h3>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <div class="form-group">
                <p>Êtes-vous sûr de vouloir supprimer le MealFlow "<span id="deletePlaylistName"></span>" ?</p>
                <p><strong>Cette action est irréversible.</strong></p>
            </div>
            <div class="form-actions">
                <button type="button" onclick="closeDeleteModal()" class="btn btn-outline">Annuler</button>
                <form id="deletePlaylistForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editPlaylist(playlistId, playlistName) {
            document.getElementById('editPlaylistName').value = playlistName;
            document.getElementById('editPlaylistForm').action = `/playlists/update/${playlistId}`;
            document.getElementById('editPlaylistModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editPlaylistModal').style.display = 'none';
        }

        function deletePlaylist(playlistId, playlistName) {
            document.getElementById('deletePlaylistName').textContent = playlistName;
            document.getElementById('deletePlaylistForm').action = `/playlists/delete/${playlistId}`;
            document.getElementById('deletePlaylistModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deletePlaylistModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editPlaylistModal');
            const deleteModal = document.getElementById('deletePlaylistModal');
            if (event.target === editModal) {
                closeEditModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        }
    </script>
@endsection
