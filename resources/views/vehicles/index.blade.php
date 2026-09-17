@extends('layouts.app')

@section('title')
    Motos
@endsection

@section('app-contents')
    @if (session('success'))
        <x-alert :message="session('success')" />
    @endif

@endsection