@extends('layout')

@section('title')
    @yield('week_dish.day') - @yield('week_dish.title')
@endsection

@section('content')
    @yield('week_dish.content')
@endsection
