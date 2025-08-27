@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow rounded">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Client Details</h4>
            <a href="{{ route('clients.index') }}" class="btn btn-light btn-sm">← Back to All Clients</a>
        </div>

        <div class="card-body">
            <h5 class="card-title">{{ $client->name }}</h5>

            <div class="row">
                <div class="col-md-6 mb-2">
                    <strong>Email:</strong><br>
                    {{ $client->email ?? 'N/A' }}
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Phone:</strong><br>
                    {{ $client->phone ?? 'N/A' }}
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Company:</strong><br>
                    {{ $client->company_name ?? 'N/A' }}
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Address:</strong><br>
                    {{ $client->address ?? 'N/A' }}
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Status:</strong><br>
                    <span class="badge {{ $client->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                        {{ ucfirst($client->status) }}
                    </span>
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Assigned To:</strong><br>
                    {{ $client->assignedUser->name ?? 'Unassigned' }}
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Created By:</strong><br>
                    {{ $client->creator->name ?? 'N/A' }}
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary me-2">Edit</a>
                 
            </div>
        </div>
    </div>
</div>
@endsection
