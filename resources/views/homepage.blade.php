@php use Illuminate\Support\Facades\Session; @endphp
@extends('layout')

@section('title', 'ChiefPlanner - Home')

@section('content')
    <div class="widgetHolder">
        <div class="main widget">
            <div class="weekHolder">
                <div class="weekTitleHolder">
                    <p class="widgetTitle">Cette semaine...</p>
                    <div style="display: flex;flex-direction: row">
                        @if(Session::has('previous_weeks'))
                            <button id="previousButton" class="buttonImg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                     class="bi bi-arrow-left" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                          d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                                </svg>
                            </button>
                        @endif
                        <button id="regenButton" class="buttonImg">
                            <svg id="regenImage" xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                 fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                      d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                                <path
                                    d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="weekSelection">
                    @foreach($daylist as $dayName => $day)
                        <div class="daySelection">
                            <p>{{ $dayName }}</p>
                            <div class="day {{ $day['morning'] === null ? 'notSelected':'selectedRandomColor' }}">
                                @if($day['morning'] !== null)
                                    <span class="info">
                                    Plat: {{$day['morning'][0]}}<br>
                                    Ingrédients: <ul class="ingredientList">
                                                    @foreach($day['morning'][1] as $ingredient)
                                                <li>{{ $ingredient }}</li>
                                            @endforeach
                                                </ul>
                                </span>
                                @endif
                            </div>
                            <div class="day {{ $day['afternoon'] === null ? 'notSelected':'selectedRandomColor' }}">
                                @if($day['afternoon'] !== null)
                                    <span class="info">
                                    Plat: {{$day['afternoon'][0]}}<br>
                                    Ingrédients: <ul class="ingredientList">
                                                    @foreach($day['afternoon'][1] as $ingredient)
                                                <li>{{ $ingredient }}</li>
                                            @endforeach
                                                </ul>
                                </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="graphHolder">
                <canvas id="myChart" width="400" height="200"></canvas>
            </div>
        </div>
        <div class="secondaryWidget">
            <div class="BList widget">
                <div>
                    <p class="widgetTitle">A acheter</p>
                    <form id="GForm" method="post" action="/add/groceries_purchase">
                        @csrf
                        <input type="hidden" required id="price" name="price"/>
                        <button class="buttonImg" id="purchaseValidate">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                 class="bi bi-bag-check" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                      d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                                <path
                                    d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                            </svg>
                        </button>
                    </form>
                </div>
                <table class="buyingList">
                    <colgroup>
                        <col class="bordered">
                        <col>
                    </colgroup>
                    <thead>
                    <tr>
                        <td>Ingrédients</td>
                        <td>Quantité</td>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($ingredientList as $ingredient => $types)
                        @foreach($types as $quantity)
                            <tr>
                                <td>{{ $ingredient }}</td>
                                <td>{{ $quantity }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="third widget">
                <div>
                    <p class="widgetTitle">Extra</p>
                    <button class="buttonImg" onclick="window.location = '/extra'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                             class="bi bi-pencil" viewBox="0 0 16 16">
                            <path
                                d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                        </svg>
                    </button>
                </div>
                @if($extraBuyingList === null)
                    <p>Aucun</p>
                @else
                    <ul>
                        @foreach($extraBuyingList as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        //***********Random color***********//
        const colorList = ['#FFABAB', '#AFF8DB', '#B28DFF', '#6EB5FF', '#FFF5BA', '#FCC2FF'];

        function getRandomColor() {
            const randomIndex = Math.floor(Math.random() * colorList.length);
            return colorList[randomIndex];
        }

        const elements = document.querySelectorAll('.selectedRandomColor');
        elements.forEach(element => {
            element.style.backgroundColor = getRandomColor();
        });

        //***********Regen***********//

        const regen_button = document.getElementById('regenButton');
        const prev_button = document.getElementById('previousButton');
        const regen_image = document.getElementById('regenImage');

        regen_button.addEventListener('click', () => {
            regen_image.classList.add('rotating');
            setTimeout(() => {
                regen_image.classList.remove('rotating');
            }, 500); //Match the ms with the duration of the animation
            window.location.href = '/refresh';
        });

        prev_button.addEventListener('click', () => {
            alert("Not programmed yet");
            //Faire que ça remet la semaine d'avant #290404
        });

        //***********Groceries***********//
        const groceries_form = document.getElementById('GForm');
        const input = document.getElementById('price');

        groceries_form.addEventListener('submit', (event) => {
            event.preventDefault(); // Prevent form submission initially
            const userInput = prompt("Combien on couté les courses ?");

            if (userInput === null || userInput.trim() === '') {
                return;
            }

            input.value = userInput;
            groceries_form.submit();
        });

        // ***********Info-bulle***********//

        const days = Array.from(document.querySelectorAll('.day')).filter(day => day.querySelector('span'));

        days.forEach(day => {
            day.addEventListener('mouseover', () => {
                const infoBox = day.querySelector('.info');
                infoBox.style.display = 'block';
            });

            day.addEventListener('mouseout', () => {
                const infoBox = day.querySelector('.info');
                infoBox.style.display = 'none';
            });
        });

        //***********Chart***********//
        const labels = @json($priceList[0]);  // Dates (x-axis)
        const data = @json($priceList[1]);  // Prices (y-axis)

        const ctx = document.getElementById('myChart').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'line',  // You can use 'bar', 'line', 'pie', etc.
            data: {
                labels: labels,  // x-axis labels
                datasets: [{
                    label: 'Prix',
                    data: data, // Dynamic data from PHP
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
    </script>
@endsection
