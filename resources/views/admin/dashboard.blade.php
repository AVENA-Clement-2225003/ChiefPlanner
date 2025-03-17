@extends('admin.layout')

@section('admin.title', 'Dashboard')

@section('admin.content')
    <div class="admin-container">
        <div class="admin-widget">
            <h2 class="widgetTitle">Gestion des comptes</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Email</th>
                        <th>Nom</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accounts as $account)
                        <tr>
                            <td>{{ $account->id_utilisateur }}</td>
                            <td>{{ $account->email }}</td>
                            <td>
                                <a href="{{ route('admin.user.inspect', ['user_id' => $account->id_utilisateur]) }}" class="admin-user-link">
                                    {{ $account->nom }}
                                </a>
                            </td>
                            @if($account->id_role !== 0)
                                <form method="post" action="{{ route('admin.user.changeRole', ['user_id' => $account->id_utilisateur]) }}">
                                    @csrf
                                    <td>
                                        <select name="role" required>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->nom_role }}" @if($role->nom_role === $account->nom_role) selected @endif>
                                                    {{ $role->nom_role }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="actions">
                                        <button type="submit" class="admin-action-button save" title="Enregistrer">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                                            </svg>
                                        </button>
                                        <button type="button" class="admin-action-button delete" onclick="deleteUser({{ $account->id_utilisateur }}, '{{ $account->nom }}')" title="Supprimer">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                                <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/>
                                            </svg>
                                        </button>
                                    </td>
                                </form>
                            @else
                                <td>{{ $account->nom_role }}</td>
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function deleteUser(userId, userName) {
            if (confirm('Vous êtes sûr de vouloir supprimer le compte de ' + userName + ' ?')) {
                window.location.href = '/admin/user/' + userId + '/delete';
            }
        }
    </script>
@endsection
