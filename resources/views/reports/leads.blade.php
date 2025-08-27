@extends('layouts.app')

@section('title', 'Leads Report')

@section('content')
<div class="container">
    <h2 class="mb-4">Leads Report</h2>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('reports.leads') }}" class="row g-3 mb-4">
        <div class="col-md-2">
            <input type="date" name="from" value="{{ request('from') }}" class="form-control" placeholder="From Date">
        </div>
        <div class="col-md-2">
            <input type="date" name="to" value="{{ request('to') }}" class="form-control" placeholder="To Date">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                @foreach($statusCounts as $status => $count)
                    <option value="{{ $status }}" @selected(request('status') == $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        @if($assignedUsers)
        <div class="col-md-3">
            <select name="assigned_to" class="form-select">
                <option value="">All Agents</option>
                @foreach($assignedUsers as $user)
                    <option value="{{ $user->id }}" @selected(request('assigned_to') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-white bg-primary p-3">
                <h6>Total Leads</h6>
                <h3>{{ $leads->count() }}</h3>
            </div>
        </div>
        @foreach($statusCounts as $status => $count)
            <div class="col-md-3">
                <div class="card shadow-sm border-0 bg-light p-3">
                    <h6 class="text-muted">{{ ucfirst($status) }}</h6>
                    <h3 class="text-dark">{{ $count }}</h3>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Status Breakdown Chart -->
    <div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title">{{ $chartContext }} - Status Breakdown</h5>
        <div class="chart-container">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>


    <!-- Monthly Created Leads Chart -->
   <div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title">{{ $chartContext }} - Monthly Created</h5>
        <div class="chart-container">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>
</div>


    <!-- Agent-wise Chart for Manager -->
    @if($agentChart)
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title">Leads Assigned to Agents</h5>
        <div class="chart-container">
            <canvas id="agentChart"></canvas>
        </div>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Bar Chart - Status Breakdown
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($statusCounts->keys()) !!},
            datasets: [{
                label: 'Leads by Status',
                data: {!! json_encode($statusCounts->values()) !!},
                backgroundColor: ['#4e73df', '#1cc88a', '#e74a3b', '#f6c23e', '#36b9cc'],
                borderColor: '#ddd',
                borderWidth: 1
            }]
        },
        options: {
    responsive: true,
    maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: context => ` ${context.parsed.y} leads`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => Number.isInteger(value) ? value : '',
                        stepSize: 1
                    },
                    title: {
                        display: true,
                        text: 'Number of Leads'
                    }
                }
            }
        }
    });

    // Line Chart - Monthly Created
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyData->keys()) !!},
            datasets: [{
                label: 'Leads Created',
                data: {!! json_encode($monthlyData->values()) !!},
                borderColor: '#36b9cc',
                backgroundColor: 'rgba(54, 185, 204, 0.2)',
                pointBackgroundColor: '#36b9cc',
                pointBorderColor: '#fff',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
    responsive: true,
    maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: context => ` ${context.parsed.y} leads`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => Number.isInteger(value) ? value : '',
                        stepSize: 1
                    },
                    title: {
                        display: true,
                        text: 'Number of Leads'
                    }
                }
            }
        }
    });

    // Manager-only: Agent-wise Bar Chart
    @if($agentChart)
    const agentCtx = document.getElementById('agentChart').getContext('2d');
    new Chart(agentCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($agentChart->pluck('name')) !!},
            datasets: [{
                label: 'Leads Assigned',
                data: {!! json_encode($agentChart->pluck('count')) !!},
                backgroundColor: '#1cc88a',
                borderColor: '#17a673',
                borderWidth: 1
            }]
        },
       options: {
    responsive: true,
    maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: context => `${context.parsed.y} leads`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => Number.isInteger(value) ? value : '',
                        stepSize: 1
                    },
                    title: {
                        display: true,
                        text: 'Number of Leads'
                    }
                }
            }
        }
    });
    @endif
</script>
@endpush
