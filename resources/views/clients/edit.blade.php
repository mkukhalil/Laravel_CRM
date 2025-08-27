@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Edit Client</h3>

    <form action="{{ route('clients.update', $client) }}" method="POST">
        @csrf
        @method('PUT')

        @include('partials.form', ['client' => $client])

        <button type="submit" class="btn btn-primary mt-2">Update Client</button>
    </form>
</div>
@endsection
