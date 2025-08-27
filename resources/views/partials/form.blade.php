<div class="mb-3">
    <label>Name</label>
    <input type="text" name="name" value="{{ old('name', $client->name ?? '') }}" class="form-control" required>
</div>

<div class="mb-3">
    <label>Email</label>
    <input type="email" name="email" value="{{ old('email', $client->email ?? '') }}" class="form-control" required>
</div>

<div class="mb-3">
    <label>Phone</label>
    <input type="text" name="phone" value="{{ old('phone', $client->phone ?? '') }}" class="form-control">
</div>

<div class="mb-3">
    <label>Company Name</label>
    <input type="text" name="company_name" value="{{ old('company_name', $client->company_name ?? '') }}" class="form-control">
</div>

<div class="mb-3">
    <label>Address</label>
    <textarea name="address" class="form-control">{{ old('address', $client->address ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label>Status</label>
    <select name="status" class="form-control">
        <option value="Active" {{ old('status', $client->status ?? '') === 'Active' ? 'selected' : '' }}>Active</option>
        <option value="Inactive" {{ old('status', $client->status ?? '') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
        <option value="Prospect" {{ old('status', $client->status ?? '') === 'Prospect' ? 'selected' : '' }}>Prospect</option>
    </select>
</div>

<div class="mb-3">
    <label>Assign To</label>
    <select name="assigned_to" class="form-control" required>
        @foreach(\App\Models\User::all() as $user)
            <option value="{{ $user->id }}" {{ old('assigned_to', $client->assigned_to ?? '') == $user->id ? 'selected' : '' }}>
                {{ $user->name }} ({{ $user->getRoleNames()->first() }})
            </option>
        @endforeach
    </select>
</div>
