@extends('layouts.auth')

@section('title')
    Órdenes
@endsection

@section('auth-contents')
    @if (session('success'))
        <x-alert :message="session('success')" />
    @endif

@endsection