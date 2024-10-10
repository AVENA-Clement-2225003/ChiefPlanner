@extends('layout')

@section('title', 'ChiefPlanner - Home')

@section('content')
    <div class="widgetHolder">
        <div class="main widget">
            <div class="weekHolder">
                <div class="weekTitleHolder">
                    <p class="widgetTitle">Cette semaine...</p>
                    <div style="display: flex;flex-direction: row">
                        <button id="previousButton" class="previousWeekSelection">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                            </svg>
                        </button>
                        <button id="regenButton" class="regenWeekSelection">
                            <svg id="regenImage" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                                <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="weekSelection">
            @foreach($daylist as $dayName => $day)
                    <div class="daySelection">
                        <p>{{ $dayName }}</p>
                        <div class="day {{ $day['morning'] === 0 ? 'notSelected':'selectedRandomColor' }}"></div>
                        <div class="day {{ $day['afternoon'] === 0 ? 'notSelected':'selectedRandomColor' }}"></div>
                    </div>
            @endforeach
                </div>
            </div>
            <div class="graphHolder">
                <p>Je suis un graphe</p>
            </div>
        </div>
        <div class="secondaryWidget">
            <div class="BList widget">
                <p class="widgetTitle">A acheter</p>
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
                    @foreach(array(array("Patate", "400g"), array("Steak", "1"), array("Jus de pomme", "1.5l"), array("Pain de mie", "Medium"), array("Jambon", "6 tranches"), array("Dentifrice", "1")) as $ingredient)
                        <tr>
                            <td>{{$ingredient[0]}}</td>
                            <td>{{$ingredient[1]}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="third widget">
                <p>None</p>
            </div>
        </div>
    </div>
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
            prev_button.style.display = 'block';
            setTimeout(() => {
                regen_image.classList.remove('rotating');
            }, 500); //Match the ms with the duration of the animation
        });

        prev_button.addEventListener('click', () => {
            alert("Not programmed yet");
            //Faire que ça remet la semaine d'avant #290404
        });
    </script>
@endsection
