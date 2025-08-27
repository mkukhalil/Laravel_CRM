@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow rounded">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Lead Details</h4>
            <a href="{{ route('leads.index') }}" class="btn btn-light btn-sm">← Back to All Leads</a>
        </div>

        <div class="card-body">
            <h5 class="card-title">{{ $lead->name }}</h5>

            <div class="row">
                <div class="col-md-6 mb-2">
                    <strong>Email:</strong><br>
                    {{ $lead->email ?? 'N/A' }}
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Phone:</strong><br>
                    {{ $lead->phone ?? 'N/A' }}
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Source:</strong><br>
                    {{ $lead->source ?? 'N/A' }}
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Status:</strong><br>
                    <span class="badge {{ $lead->status === 'Qualified' ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ ucfirst($lead->status) }}
                    </span>
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Assigned To:</strong><br>
                    {{ $lead->assignee->name ?? 'Unassigned' }}
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Created By:</strong><br>
                    {{ $lead->creator->name ?? 'N/A' }}
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Created At:</strong><br>
                    {{ $lead->created_at->format('d M Y, h:i A') }}
                </div>
            </div>

            @if($lead->status === 'Qualified')
                <form action="{{ route('leads.convert', $lead->id) }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">Convert to Client</button>
                </form>
            @endif

            <div class="mt-4">
                <a href="{{ route('leads.edit', $lead) }}" class="btn btn-primary me-2">Edit</a>
              
            </div>
        </div>
    </div>
</div>
@endsection
