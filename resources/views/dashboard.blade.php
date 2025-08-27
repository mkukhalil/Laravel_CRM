@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
    <div class="container">
        <h2 class="mb-4">Dashboard</h2>
        <p class="lead">You're logged in as <strong>{{ auth()->user()->getRoleNames()->first() }}</strong>.</p>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Users</h5>
                        <p class="card-text">Manage users and roles.</p>
                        <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm">View</a>
                    </div>
                </div>
            </div>
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
