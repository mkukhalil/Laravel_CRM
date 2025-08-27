@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
    <div class="container">
        <h2 class="mb-4">Welcome, {{ auth()->user()->name }}</h2>
        <p class="lead">You are logged in as <strong>{{ auth()->user()->getRoleNames()->first() }}</strong>.</p>

        <div class="row">
            @if(auth()->user()->hasRole('Admin'))
                <div class="col-md-4">
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Users</h5>
                            <p class="card-text">Manage users and roles.</p>
                            <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm">View</a>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-md-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Leads</h5>
                        <p class="card-text">View and assign leads.</p>
                        <a href="{{ route('leads.index') }}" class="btn btn-primary btn-sm">View</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
