@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Lead</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Please fix the following issues:
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('leads.update', $lead) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Lead Name *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $lead->name) }}" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $lead->email) }}">
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $lead->phone) }}">
        </div>

        <div class="mb-3">
            <label for="source" class="form-label">Source</label>
            <select name="source" class="form-select">
                <option value="">Select</option>
                @foreach (['Website', 'Referral', 'Social Media'] as $source)
                    <option value="{{ $source }}" {{ old('source', $lead->source) == $source ? 'selected' : '' }}>
                        {{ $source }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" class="form-select">
                @foreach (['New', 'Contacted', 'Qualified'] as $status)
                    <option value="{{ $status }}" {{ old('status', $lead->status) == $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>

       @php
    $agents = \App\Models\User::role('Agent')->get();
@endphp


        @if(auth()->user()->hasRole(['Admin', 'Manager']))

            <div class="mb-3">
                <label for="assigned_to" class="form-label">Assign To Agent</label>
                <select name="assigned_to" class="form-select">
                <option value="">-- Select Agent --</option>
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}" {{ old('assigned_to', $lead->assigned_to ?? '') == $agent->id ? 'selected' : '' }}>
                        {{ $agent->name }}
                    </option>
                @endforeach
            </select>

            </div>
        @endif


        <button type="submit" class="btn btn-success">Update Lead</button>
        <a href="{{ route('leads.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
