@extends('week_dish.layout')

@section('week_dish.day', $day->day_name . ' ' . $day->day_time)

@section('week_dish.title', $dish?->nom ?? 'Aucun plat')

@section('week_dish.content')
    <div class="widgetHolder">
        <div class="widget">
            <nav class="dish-navigation">
                @if($navigation['previous'])
                    <a href="{{ route('week-dish.inspect', ['day_id' => $navigation['previous']]) }}" class="nav-button nav-previous" title="Plat précédent">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                        <span>Précédent</span>
                    </a>
                @else
                    <span class="nav-button nav-previous disabled">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                        </svg>
                        <span>Précédent</span>
                    </span>
                @endif

                @if($navigation['next'])
                    <a href="{{ route('week-dish.inspect', ['day_id' => $navigation['next']]) }}" class="nav-button nav-next" title="Plat suivant">
                        <span>Suivant</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </a>
                @else
                    <span class="nav-button nav-next disabled">
                        <span>Suivant</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </span>
                @endif
            </nav>

            <div class="dish-header">
                <h2 class="widgetTitle">{{ $day->day_name . ' - ' . $day->day_time }}</h2>

                @if($dish)
                    <div class="dish-title">
                        <h3>{{ $dish->nom }}</h3>
                        <button onclick="showEdit()" class="buttonImg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                            </svg>
                        </button>
                    </div>
                    <div id="editOptionHolder" class="edit-options" style="display: none">
                        <button onclick="regenDish()" class="buttonImg" title="Régénérer un plat aléatoire">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                                <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                            </svg>
                        </button>
                        <div class="search-container">
                            <input id="searchInput" type="text" name="dish_name" placeholder="Rechercher un plat..."/>
                            <div id="searchResults" class="search-results">
                                <!-- Results will be loaded here -->
                            </div>
                        </div>
                        <form id="updateDishForm" method="POST" action="{{ route('week-dish.modify', ['day_id' => $day->id_jour]) }}">
                            @csrf
                            <input type="hidden" name="dish_id" id="selectedDishId" value="">
                            <button type="submit" class="submit-button" disabled>Valider</button>
                        </form>
                    </div>
                @else
                    <div class="no-dish-message">
                        <p>Aucun plat n'est défini pour ce jour.</p>
                        <a href="{{ route('home') }}" class="nav-button">Retour à l'accueil</a>
                    </div>
                @endif
            </div>

            @if($dish)
                <div class="ingredients-table">
                    <table>
                        <thead>
                        <tr>
                            <th>Ingrédient</th>
                            <th>Quantité</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($ingredients as $ingredient)
                            <tr>
                                <td>{{ $ingredient->nom }}</td>
                                <td>{{ $ingredient->quantity }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <style>
        .dish-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5em;
        }

        .nav-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5em;
            padding: 0.5em 1em;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s, color 0.2s;
            color: #333;
            background-color: #F2F2F2;
        }

        .nav-button:hover:not(.disabled) {
            background-color: #e0e0e0;
            color: #000;
        }

        .nav-button.disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .no-dish-message {
            text-align: center;
            padding: 2em;
            background-color: #F2F2F2;
            border-radius: 15px;
            margin-top: 1em;
        }

        .no-dish-message p {
            margin: 0 0 1em 0;
            color: #666;
            font-size: 1.1em;
        }

        .dish-header {
            margin-bottom: 2em;
        }
        
        .dish-title {
            display: flex;
            align-items: center;
            gap: 1em;
            margin: 1em 0;
        }
        
        .dish-title h3 {
            margin: 0;
            font-size: 1.5em;
        }
        
        .edit-options {
            display: flex;
            align-items: center;
            gap: 1em;
            margin-top: 1em;
            padding: 1em;
            background-color: #F2F2F2;
            border-radius: 15px;
        }
        
        .search-container {
            position: relative;
            flex-grow: 1;
        }
        
        #searchInput {
            width: 100%;
            padding: 0.5em;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            font-family: Inter, sans-serif;
        }
        
        .search-results div {
            padding: 0.5em;
            cursor: pointer;
            font-family: Inter, sans-serif;
        }

        .search-results div.error-message {
            cursor: default;
            padding: 1em;
        }
        
        .ingredients-table {
            background-color: #F2F2F2;
            border-radius: 15px;
            padding: 1em;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 0.5em;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            font-weight: bold;
            color: #333;
        }
        
        tr:last-child td {
            border-bottom: none;
        }

        .submit-button {
            padding: 0.5em 1em;
            border: none;
            border-radius: 5px;
            background-color: #7AE47D;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .submit-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .submit-button:not(:disabled):hover {
            background-color: #68c96b;
        }

        .selected-dish {
            background-color: #e8f5e9 !important;
        }
    </style>

    <script>
        let selectedDishElement = null;

        function showEdit() {
            const element = document.getElementById('editOptionHolder');
            if (element) {
                if (element.style.display === 'none') {
                    element.style.display = 'flex';
                } else {
                    element.style.display = 'none';
                }
            }
        }

        function selectDish(element, dishId, dishName) {
            // Remove previous selection
            if (selectedDishElement) {
                selectedDishElement.classList.remove('selected-dish');
            }

            // Update selection
            selectedDishElement = element;
            element.classList.add('selected-dish');

            // Update form
            document.getElementById('selectedDishId').value = dishId;
            document.querySelector('.submit-button').disabled = false;

            // Update search input
            document.getElementById('searchInput').value = dishName;

            // Hide results
            document.getElementById('searchResults').style.display = 'none';
        }

        function regenDish() {
            if (confirm('Voulez-vous vraiment régénérer un nouveau plat aléatoire ?')) {
                window.location.href = "{{ route('week-dish.regenerate', ['day_id' => $day->id_jour]) }}";
            }
        }
        
        let timeout;
        const searchInput = document.getElementById('searchInput');
        
        function performSearch() {
            const query = searchInput.value;
            document.getElementById('searchResults').style.display = 'block';

            // Add debug logging
            console.log('Searching for:', query);
            console.log('Search URL:', `{{ route('search') }}?dish_name=${encodeURIComponent(query)}`);

            fetch(`{{ route('search') }}?dish_name=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Search results:', data);
                    const resultsDiv = document.getElementById('searchResults');
                    resultsDiv.innerHTML = ''; // Clear previous results

                    if (data.length > 0) {
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.textContent = item.nom;
                            div.addEventListener('click', function() {
                                selectDish(this, item.id_plat, item.nom);
                            });
                            resultsDiv.appendChild(div);
                        });
                    } else {
                        const div = document.createElement('div');
                        div.textContent = 'Aucun résultat trouvé.';
                        div.classList.add('error-message');
                        div.style.color = '#666';
                        div.style.fontStyle = 'italic';
                        resultsDiv.appendChild(div);
                    }
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    const resultsDiv = document.getElementById('searchResults');
                    resultsDiv.innerHTML = '';
                    const div = document.createElement('div');
                    div.textContent = 'Erreur lors de la recherche.';
                    div.classList.add('error-message');
                    div.style.color = '#dc3545';
                    div.style.fontStyle = 'italic';
                    resultsDiv.appendChild(div);
                });
        }

        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);
            timeout = setTimeout(performSearch, 500);
        });

        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Prevent form submission
                clearTimeout(timeout);
                performSearch();
            }
        });

        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            const searchContainer = document.querySelector('.search-container');
            const searchResults = document.getElementById('searchResults');
            
            if (!searchContainer.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    </script>
@endsection
