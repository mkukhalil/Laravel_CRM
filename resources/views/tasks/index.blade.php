@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>All Tasks</h2>
        @if(auth()->user()->hasRole(['Admin', 'Manager']))
            <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">+ Add Task</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('tasks.index') }}" class="mb-4 row g-2 align-items-end">
        <div class="col-md-3">
            <label for="status" class="form-label">Filter by Status:</label>
            <select name="status" id="status" class="form-select">
                <option value="">All</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>

        @if(auth()->user()->hasRole(['Admin', 'Manager']))
        <div class="col-md-3">
            <label for="assigned_to" class="form-label">Filter by Agent:</label>
            <select name="assigned_to" id="assigned_to" class="form-select">
                <option value="">All</option>
                @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ request('assigned_to') == $agent->id ? 'selected' : '' }}>
                        {{ $agent->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
        </div>
        <div class="col-md-3">
            <a href="{{ route('tasks.index') }}" class="btn btn-secondary w-100">Clear</a>
        </div>
    </form>

    {{-- Tasks Table --}}
    @if($tasks->isEmpty())
        <div class="alert alert-info">No tasks available.</div>
    @else
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Assigned User</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $task)
                <tr>
                    <td>{{ $task->title }}</td>
                    <td>{{ $task->user?->name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge {{ $task->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ ucfirst($task->status) }}
                        </span>
                    </td>
                    <td>{{ $task->created_at->format('d M, Y') }}</td>
                    <td>
                        <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-outline-info btn-sm">View</a>
                        <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this task?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
