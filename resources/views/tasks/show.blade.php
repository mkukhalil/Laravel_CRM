@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow rounded">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Task Details</h4>
            <a href="{{ route('tasks.index') }}" class="btn btn-light btn-sm">← Back to All Tasks</a>
        </div>
        <div class="card-body">
            <h5 class="card-title">{{ $task->title }}</h5>

            <p class="mb-3">
                <strong>Description:</strong><br>
                {{ $task->description ?? 'No description provided.' }}
            </p>

            <div class="row">
                <div class="col-md-6 mb-2">
                    <strong>Status:</strong><br>
                    <span class="badge {{ $task->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ ucfirst($task->status) }}
                    </span>
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Assigned To:</strong><br>
                    {{ $task->user->name ?? 'N/A' }}
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Assigned By:</strong><br>
                    {{ $task->creator->name ?? 'N/A' }}
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Created On:</strong><br>
                    {{ $task->created_at->format('d M Y, h:i A') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
