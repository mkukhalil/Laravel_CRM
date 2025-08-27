@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Add New Lead</h2>

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

    <form action="{{ route('leads.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Lead Name *</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email (optional)</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone (optional)</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
        </div>

        <div class="mb-3">
            <label for="source" class="form-label">Source</label>
            <select name="source" class="form-select">
                <option value="">Select</option>
                <option value="Website" {{ old('source') == 'Website' ? 'selected' : '' }}>Website</option>
                <option value="Referral" {{ old('source') == 'Referral' ? 'selected' : '' }}>Referral</option>
                <option value="Social Media" {{ old('source') == 'Social Media' ? 'selected' : '' }}>Social Media</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="New" {{ old('status') == 'New' ? 'selected' : '' }}>New</option>
                <option value="Contacted" {{ old('status') == 'Contacted' ? 'selected' : '' }}>Contacted</option>
                <option value="Qualified" {{ old('status') == 'Qualified' ? 'selected' : '' }}>Qualified</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="assigned_to" class="form-label">Assign To (Sales Agent)</label>
            <select name="assigned_to" class="form-select">
                <option value="">-- Select Agent --</option>
                @foreach ($agents as $user)
                    <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success">Save Lead</button>
        <a href="{{ route('leads.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
