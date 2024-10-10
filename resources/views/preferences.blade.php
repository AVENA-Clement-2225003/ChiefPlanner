@extends('layout')

@section('title', 'ChiefPlanner - Préférences')

@section('content')
    <div class="weekHolder">
        <p class="widgetTitle">Jours sélectionnés</p>
        <form class="weekSelection">
            @foreach($daylist as $dayName => $day)
                <div class="daySelection">
                    <p>{{ $dayName }}</p>
                    <label> <!--#290404 Il faut faire la pose de la calsse par le JS car il faut que le check box soit selectionné de maniere concrete par le navigateur comme ca quand je clique ca fait apparaitre disparaitre le vers ou pas donc faut aussi revoir mon css lié au day selected-->
                        <input type="checkbox">
                        <span class="day {{ $day['afternoon'] === 0 ? 'notSelected':'selected' }}"></span>
                    </label>
                    <label>
                        <input type="checkbox">
                        <span class="day {{ $day['afternoon'] === 0 ? 'notSelected':'selected' }}"></span>
                    </label>
                </div>
            @endforeach
            <input type="Submit" value="Mettre à jour">
        </form>
    </div>
@endsection
