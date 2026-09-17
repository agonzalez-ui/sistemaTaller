@extends('layouts.auth')

@section('title')
    Repuestos de Moto
@endsection

@section('auth-contents')
    @if (session('success'))
        <x-alert :message="session('success')" />
    @endif

@endsection