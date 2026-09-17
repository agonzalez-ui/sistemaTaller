@extends('layouts.auth')

@section('title')
    Facturas
@endsection

@section('auth-contents')
    @if (session('success'))
        <x-alert :message="session('success')" />
    @endif

@endsection