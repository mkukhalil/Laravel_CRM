@extends('layouts.app')

@section('title', 'Agent Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">Welcome, {{ Auth::user()->name }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('leads.index') }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-person-lines-fill"></i> My Leads
        </a>
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-list-check"></i> My Tasks
        </a>
        <a href="{{ route('clients.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-people"></i> My Clients
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <x-metric-card title="My Leads" :count="$leadCount" icon="bi-person-check-fill" :trend="$leadTrend" />
    </div>
    <div class="col-md-3">
        <x-metric-card title="My Tasks" :count="$assignedTasks" icon="bi-list-check" :trend="$taskTrend" />
    </div>
    <div class="col-md-3">
        <x-metric-card title="Pending" :count="$pendingTasks" icon="bi-hourglass-split" />
    </div>
    {{-- Replaced "Completed" card with "Clients" card --}}
    <div class="col-md-3">
        <x-metric-card title="Clients" :count="$clientsCount" icon="bi-people" />
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-0"><strong>My Leads (last 12 months)</strong></div>
            <div class="card-body">
                <canvas id="agentLeadsChart" height="120"></canvas>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0"><strong>My Tasks (last 12 months)</strong></div>
            <div class="card-body">
                <canvas id="agentTasksChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-0"><strong>Task Status</strong></div>
            {{-- FIX: lock the body height so the chart can't stretch --}}
            <div class="card-body" style="height: 260px;">
                <canvas id="agentTaskStatusChart"></canvas>
                <div class="mt-3 small text-muted">
                    Pending: {{ $pendingTasks }} &nbsp;|&nbsp; Completed: {{ $completedTasks }}
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0"><strong>Quick Actions</strong></div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('tasks.index') }}">View My Tasks</a>
                    <a class="btn btn-outline-success btn-sm" href="{{ route('leads.index') }}">View My Leads</a>
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('reports.index', ['tab' => 'leads']) }}">My Reports</a>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mt-3">
            <div class="card-header bg-white border-0"><strong>Recent Tasks</strong></div>
            <div class="card-body">
                @forelse($recentTasks as $t)
                    <div class="mb-2">
                        <div class="fw-semibold">{{ $t->title }}</div>
                        <div class="small text-muted">{{ optional($t->user)->name ?? 'Unassigned' }} • {{ $t->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No recent tasks.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const PALETTE = @json($palette);

// Leads chart
new Chart(document.getElementById('agentLeadsChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($leadSeries['labels']) !!},
        datasets: [{
            label: 'Leads',
            data: {!! json_encode($leadSeries['series']) !!},
            borderColor: PALETTE.info,
            backgroundColor: PALETTE.lightFill,
            tension: 0.35,
            fill: true
        }]
    },
    options: { maintainAspectRatio: false }
});

// Tasks chart
new Chart(document.getElementById('agentTasksChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($taskSeries['labels']) !!},
        datasets: [{
            label: 'Tasks',
            data: {!! json_encode($taskSeries['series']) !!},
            borderColor: PALETTE.primary,
            backgroundColor: PALETTE.lightFill,
            tension: 0.35,
            fill: true
        }]
    },
    options: { maintainAspectRatio: false }
});

// Task status donut (fixed height via parent container)
new Chart(document.getElementById('agentTaskStatusChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_keys($taskStatusCounts)) !!},
        datasets: [{
            data: {!! json_encode(array_values($taskStatusCounts)) !!},
            backgroundColor: [PALETTE.warning, PALETTE.success, PALETTE.danger, PALETTE.info, PALETTE.primary]
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>
@endpush
