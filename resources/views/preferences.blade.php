@extends('layout')

@section('title', 'ChiefPlanner - Préférences')

@section('content')
    <div class="widgetHolder">
        <div class="widget preferences-widget">
            <h2 class="widgetTitle">Jours sélectionnés</h2>
            <form action="/preferences/update" method="post" class="preferences-form">
                @csrf
                <div class="weekSelection">
                @foreach($daylist as $dayName => $day)
                    <div class="daySelection">
                        <p>{{ $dayName }}</p>
                        <label class="day-label">
                            <input type="checkbox"
                                   name="schedule[{{ $dayName }}][morning]"
                                   @if($day['morning'] == 1) checked @endif>
                            <span class="day custom-checkbox"></span>
                        </label>
                        <label class="day-label">
                            <input type="checkbox"
                                   name="schedule[{{ $dayName }}][afternoon]"
                                   @if($day['afternoon'] == 1) checked @endif>
                            <span class="day custom-checkbox"></span>
                        </label>
                    </div>
                @endforeach
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
@endsection
