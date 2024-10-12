@extends('layout')

@section('title', 'ChiefPlanner - Préférences')

@section('content')
    <div class="weekHolder">
        <p class="widgetTitle">Jours sélectionnés</p>
        <form action="/preferences/update" method="post">
            @csrf
            <div class="weekSelection">
            @foreach($daylist as $dayName => $day)
                <div class="daySelection">
                    <p>{{ $dayName }}</p>
                    <label> <!--#290404 Il faut faire la pose de la classe par le JS car il faut que le check box soit selectionné de manière concrete par le navigateur comme ça quand je clique ça fait apparaitre, disparaitre le vert ou pas donc il faut aussi revoir mon css lié au day selected-->
                        <input type="checkbox"
                               name="schedule[{{ $dayName }}][morning]"
                               @if($day['morning'] == 1) checked @endif>
                        <span class="day custom-checkbox"></span>
                    </label>
                    <label>
                        <input type="checkbox"
                               name="schedule[{{ $dayName }}][afternoon]"
                               @if($day['afternoon'] == 1) checked @endif>
                        <span class="day custom-checkbox"></span>
                    </label>
                </div>
            @endforeach
            </div>
            <input type="Submit" value="Mettre à jour">
        </form>
    </div>
@endsection
