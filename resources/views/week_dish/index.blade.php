@extends('week_dish.layout')

@section('week_dish.day', $day->day_name . ' ' . $day->day_time)

@section('week_dish.title', $dish->nom)

@section('week_dish.content')
    <div style="display: flex; flex-direction: column">
        <h2>{{ $day->day_name . ' - ' . $day->day_time }}</h2>
        <div style="display: flex; align-items: center;">
            <h3>{{ $dish->nom }}</h3>
            <button onclick="showEdit()" class="editDishOption">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                    <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                    <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                </svg>
            </button>
            <div id="editOptionHolder" class="editOption" style="display: none">
                <button style="background: none; padding: 0; border: none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                    </svg>
                </button>
                <input id="searchInput" type="text" name="dish_name"/>
                <div id="searchResults">
                    <!-- Results will be loaded here -->
                </div>
            </div>
        </div>

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
    <script>
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
    </script>
    <script>
        let timeout;
        document.getElementById('searchInput').addEventListener('input', function () {
            clearTimeout(timeout);

            timeout = setTimeout(() => {
                const query = this.value;

                fetch(`/search?query=${query}`)
                    .then(response => response.json())
                    .then(data => {
                        const resultsDiv = document.getElementById('searchResults');
                        resultsDiv.innerHTML = ''; // Clear previous results

                        if (data.length > 0) {
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.textContent = item.name; // Adjust based on your data
                                resultsDiv.appendChild(div);
                            });
                        } else {
                            resultsDiv.textContent = 'No results found.';
                        }
                    })
                    .catch(error => console.error('Error fetching data:', error));
            }, 500);
        });
    </script>
@endsection
