@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Task Reports - {{ $chartContext }}</h2>

    {{-- Filters --}}
    <form method="GET" class="mb-4 row g-2 align-items-end">
        <div class="col-md-3">
            <label for="status" class="form-label">Filter by Status:</label>
           <select name="status" class="form-select">
                <option value="">All</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
       @if($agents->count())
                <div class="col-md-3">
                    <label for="assigned_to" class="form-label">Filter by User:</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">All</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ request('assigned_to') == $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }} ({{ $agent->getRoleNames()->first() }})
                            </option>
                        @endforeach
                    </select>
                </div>
       @endif

        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
        </div>
        <div class="col-md-3">
            <a href="{{ route('reports.index') }}" class="btn btn-secondary w-100">Clear</a>
        </div>
    </form>

    {{-- Charts --}}
    <div class="row mb-5">
        {{-- Status Pie Chart --}}
       <div class="col-md-6">
    <div class="chart-container">
        <canvas id="statusChart"></canvas>
    </div>
</div>

        {{-- Monthly Line Chart --}}
       <div class="col-md-6">
    <div class="chart-container">
        <canvas id="monthlyChart"></canvas>
    </div>
</div>

    {{-- Agent-wise Breakdown --}}
    @if($agentChart)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">Agent-wise Task Summary</div>
        <div class="card-body">
            <ul class="list-group">
                @foreach($agentChart as $agent)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $agent['name'] }}
                        <span class="badge bg-primary rounded-pill">{{ $agent['count'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif
</div>
@endsection
<style>
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
</style>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const statusData = {
        labels: {!! json_encode($statusChart->keys()) !!},
        datasets: [{
            label: 'Task Status',
            data: {!! json_encode($statusChart->values()) !!},
            backgroundColor: ['#2ecc71','#f39c12'],
        }]
    };

   new Chart(document.getElementById('statusChart'), {
    type: 'pie',
    data: statusData,
    options: {
        maintainAspectRatio: false
    }
});


    const monthlyData = {
        labels: {!! json_encode($monthlyChart->keys()) !!},
        datasets: [{
            label: 'Tasks Created',
            data: {!! json_encode($monthlyChart->values()) !!},
            fill: true,
            tension: 0.4,
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.2)',
        }]
    };

   new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: monthlyData,
    options: {
        maintainAspectRatio: false
    }
});

</script>
@endsection
