@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Add New Client</h3>

    <form action="{{ route('clients.store') }}" method="POST">
        @csrf

        @include('partials.form', ['client' => null])
        
        <button type="submit" class="btn btn-success mt-2">Save Client</button>
    </form>
</div>
@endsection
