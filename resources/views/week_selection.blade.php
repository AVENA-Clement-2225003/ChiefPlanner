@extends('layout')

@section('title', 'ChiefPlanner - Selection')

@section('content')
    <div class="weekHolder">
        <p class="widgetTitle">Cette semaine...</p>
        <div class="weekSelection">
            @foreach($daylist as $dayName => $day)
                <div class="daySelection">
                    <p>{{ $dayName }}</p>
                    <div class="day {{ $day['morning'] === 0 ? 'notSelected':'selected' }}"></div>
                    <div class="day {{ $day['afternoon'] === 0 ? 'notSelected':'selected' }}"></div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
