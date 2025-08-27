@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>All Users</h2>
        <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">+ Add New User</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Filter by Role --}}
    <form method="GET" action="{{ route('users.index') }}" class="mb-4 row g-2 align-items-end">
        <div class="col-md-3">
            <label for="role" class="form-label">Filter by Role:</label>
            <select name="role" id="role" class="form-select">
                <option value="">All</option>
                <option value="Admin" {{ request('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                <option value="Manager" {{ request('role') == 'Manager' ? 'selected' : '' }}>Manager</option>
                <option value="Agent" {{ request('role') == 'Agent' ? 'selected' : '' }}>Agent</option>
            </select>
        </div>

        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
        </div>
        <div class="col-md-3">
            <a href="{{ route('users.index') }}" class="btn btn-secondary w-100">Clear</a>
        </div>
    </form>

    {{-- Users Table --}}
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @php
                            $role = $user->getRoleNames()->first();
                            $badgeClass = match($role) {
                                'Admin' => 'danger',
                                'Manager' => 'primary',
                                'Agent' => 'info',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($role) }}</span>
                    </td>
                    <td>{{ $user->created_at->format('d M, Y') }}</td>
                    <td>
                        <a href="{{ route('users.show', $user) }}" class="btn btn-outline-info btn-sm">View</a>
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm">Edit</a>

                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
