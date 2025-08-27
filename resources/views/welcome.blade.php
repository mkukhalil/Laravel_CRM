@extends('layouts.app')

@section('title', 'Welcome to CRM')

@section('content')
<div class="text-center py-5">
    <h1 class="display-4 fw-bold">Welcome to Your CRM</h1>
    <p class="lead text-muted">Manage your leads, users, and team efficiently — all in one place.</p>

    @auth
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg mt-4">Go to Dashboard</a>
    @else
        <a href="{{ route('login') }}" class="btn btn-primary btn-lg mt-4 me-2">Login</a>
        <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg mt-4">Register</a>
    @endauth

     
</div>
@endsection
