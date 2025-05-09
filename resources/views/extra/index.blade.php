@extends('extra.layout')

@section('extra.title', 'Edit')

@section('extra.content')
    <div class="extra-widgetHolder">
        <div class="extra-widget">
            <h2 class="widgetTitle">Liste des extras</h2>
            <div class="extra-table-container">
                <table class="extra-table">
                    <thead>
                    <tr>
                        <th>Intitulé</th>
                        <th>Quantité</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($extraList as $extra)
                        <tr data-id="{{ $extra->intitule }}">
                            <td>
                                <span class="view-mode">{{ $extra->intitule }}</span>
                                <input type="text" class="extra-edit-mode" value="{{ $extra->intitule }}" style="display: none;">
                            </td>
                            <td>
                                <span class="view-mode">{{ $extra->quantite }}</span>
                                <input type="text" class="extra-edit-mode" value="{{ $extra->quantite }}" style="display: none;">
                            </td>
                            <td class="extra-actions">
                                <button class="extra-action-button extra-edit-button" title="Modifier">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                        <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                                    </svg>
                                </button>
                                <form action="{{ route('extra.delete') }}" method="post" class="delete-form" style="display: inline;">
                                    @csrf
                                    <input type="hidden" value="{{ $extra->intitule }}" name="intitule">
                                    <button type="submit" class="extra-action-button extra-delete-button" title="Supprimer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                        </svg>
                                    </button>
                                </form>
                                <div class="edit-actions" style="display: none;">
                                    <button class="extra-action-button extra-save-button" title="Enregistrer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
                                            <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                                        </svg>
                                    </button>
                                    <button class="extra-action-button extra-cancel-button" title="Annuler">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                                            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <form action="{{ route('extra.add') }}" method="post" class="extra-add-form">
                @csrf
                <div class="extra-input-group">
                    <input type="text" name="intitule" placeholder="Intitulé" required>
                    <input type="text" name="quantite" placeholder="Quantité" required>
                    <button type="submit" class="extra-action-button extra-add-button" title="Ajouter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .extra-widgetHolder {
            max-width: 800px;
            margin: 0 auto;
            padding: 1em;
        }

        .extra-widget {
            /* background-color: #E3E3E3; */
            border-radius: 15px;
            padding: 1.5em;
        }

        .extra-table-container {
            background-color:rgb(241, 241, 241);
            border-radius: 10px;
            padding: 0.5em;
            margin-bottom: 1em;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .extra-table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 0.6em;
            text-align: left;
            border-bottom: 1px solid #fff;
            font-family: Inter, sans-serif;
            font-size: 0.95em;
        }

        th {
            font-weight: 600;
            color: #333;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .extra-actions {
            white-space: nowrap;
            width: 1%;
        }

        .extra-action-button {
            background: none;
            border: none;
            padding: 0.4em;
            margin: 0 0.2em;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .extra-edit-button {
            color: #2196F3;
        }

        .extra-edit-button:hover {
            background-color: rgba(33, 150, 243, 0.1);
        }

        .extra-delete-button {
            color: #dc3545;
        }

        .extra-delete-button:hover {
            background-color: rgba(220, 53, 69, 0.1);
        }

        .extra-save-button {
            color: #28a745;
        }

        .extra-save-button:hover {
            background-color: rgba(40, 167, 69, 0.1);
        }

        .extra-cancel-button {
            color: #6c757d;
        }

        .extra-cancel-button:hover {
            background-color: rgba(108, 117, 125, 0.1);
        }

        .extra-add-form {
            margin-top: 1em;
        }

        .extra-input-group {
            display: flex;
            gap: 0.5em;
        }

        .extra-input-group input {
            flex: 1;
            padding: 0.5em;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: Inter, sans-serif;
            font-size: 0.95em;
        }

        .extra-input-group input:focus {
            outline: none;
            border-color: #2196F3;
        }

        .extra-add-button {
            color: #28a745;
            padding: 0 0.8em;
        }

        .extra-add-button:hover {
            background-color: rgba(40, 167, 69, 0.1);
        }

        .extra-edit-mode {
            width: 100%;
            padding: 0.4em;
            border: 1px solid #2196F3;
            border-radius: 4px;
            font-family: Inter, sans-serif;
            font-size: 0.95em;
        }

        @media (max-width: 600px) {
            .extra-input-group {
                flex-direction: column;
            }
            
            .extra-action-button {
                padding: 0.6em;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete confirmation
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer cet extra ?')) {
                        e.preventDefault();
                    }
                });
            });

            // Edit functionality
            document.querySelectorAll('.extra-edit-button').forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const viewModes = row.querySelectorAll('.view-mode');
                    const editModes = row.querySelectorAll('.extra-edit-mode');
                    const editActions = row.querySelector('.edit-actions');
                    const normalActions = row.querySelectorAll('.extra-edit-button, .delete-form');

                    viewModes.forEach(el => el.style.display = 'none');
                    editModes.forEach(el => el.style.display = 'block');
                    editActions.style.display = 'inline-block';
                    normalActions.forEach(el => el.style.display = 'none');
                });
            });

            // Save functionality
            document.querySelectorAll('.extra-save-button').forEach(button => {
                button.addEventListener('click', async function() {
                    const row = this.closest('tr');
                    const oldIntitule = row.dataset.id;
                    const [newIntitule, newQuantite] = row.querySelectorAll('.extra-edit-mode');

                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('old_intitule', oldIntitule);
                    formData.append('intitule', newIntitule.value);
                    formData.append('quantite', newQuantite.value);

                    try {
                        const response = await fetch('{{ route('extra.update') }}', {
                            method: 'POST',
                            body: formData
                        });

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            alert('Erreur lors de la mise à jour');
                        }
                    } catch (error) {
                        alert('Erreur lors de la mise à jour');
                    }
                });
            });

            // Cancel functionality
            document.querySelectorAll('.extra-cancel-button').forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const viewModes = row.querySelectorAll('.view-mode');
                    const editModes = row.querySelectorAll('.extra-edit-mode');
                    const editActions = row.querySelector('.edit-actions');
                    const normalActions = row.querySelectorAll('.extra-edit-button, .delete-form');

                    viewModes.forEach(el => el.style.display = 'block');
                    editModes.forEach(el => el.style.display = 'none');
                    editActions.style.display = 'none';
                    normalActions.forEach(el => el.style.display = 'inline-block');
                });
            });
        });
    </script>
@endsection
