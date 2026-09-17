@extends('layouts.app')

@section('title')
    Repuestos de Moto
@endsection

@section('app-contents')
    @if (session('success'))
        <x-alert :message="session('success')" />
    @endif

@endsection