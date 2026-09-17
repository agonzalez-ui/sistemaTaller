@extends('layouts.app')

@section('title')
    Facturas
@endsection

@section('app-contents')
    @if (session('success'))
        <x-alert :message="session('success')" />
    @endif

@endsection