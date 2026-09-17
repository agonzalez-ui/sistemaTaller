@extends('layouts.auth')

@section('title')
    Motos
@endsection

@section('auth-contents')
    @if (session('success'))
        <x-alert :message="session('success')" />
    @endif

@endsection