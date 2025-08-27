@extends('layouts.app')

@section('content')
<div class="container ">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>All Clients</h2>
        <a href="{{ route('clients.create') }}" class="btn btn-success btn-sm">+ Add New Client</a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
 @if($clients->isEmpty())
        <div class="alert alert-info">No Clients available.</div>
    @else
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Company</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
            <tr>
                <td>{{ $client->name }}</td>
                <td>{{ $client->email }}</td>
                <td>{{ $client->company_name }}</td>
                <td>{{ $client->status }}</td>
                <td>{{ $client->assignedUser->name ?? 'N/A' }}</td>
                <td>
                    <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('clients.destroy', $client) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"
                            onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
   @endif
</div>
 

@endsection
