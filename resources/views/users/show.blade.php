@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow rounded">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">User Details</h4>
            <a href="{{ route('users.index') }}" class="btn btn-light btn-sm">← Back to All Users</a>
        </div>

        <div class="card-body">
            <h5 class="card-title">{{ $user->name }}</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Email:</strong><br>
                    {{ $user->email }}
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Role:</strong><br>
                    <span class="badge bg-info">{{ $user->getRoleNames()->first() ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary me-2">Edit</a>
            
            </div>
        </div>
    </div>
</div>
@endsection
