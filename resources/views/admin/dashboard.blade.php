@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">Welcome, {{ Auth::user()->name }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('users.create') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-person-plus"></i> Add User
        </a>
        <a href="{{ route('leads.create') }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-plus-circle"></i> Add Lead
        </a>
        <a href="{{ route('tasks.create') }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-plus-square"></i> Add Task
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-graph-up"></i> Reports
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <x-metric-card title="Total Users" :count="$userCount"  icon="bi-people"  href="{{ route('users.index') }}"  :trend="$userTrend" />
    </div>
    <div class="col-md-3">
        <x-metric-card
            title="Total Leads" :count="$leadCount" icon="bi-bar-chart-line-fill"  href="{{ route('leads.index') }}" :trend="$leadTrend"
        />
    </div>
    <div class="col-md-3">
        <x-metric-card
            title="Total Tasks" :count="$taskCount" icon="bi-check-circle-fill" href="{{ route('tasks.index') }}" :trend="$taskTrend"
        />
    </div>
    <div class="col-md-3">
        <x-metric-card
            title="Total Clients"
            :count="$clientCount"
            icon="bi-person-check-fill"
            href="{{ route('clients.index') }}"
            :trend="$clientTrend"
        />
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-0">
                <strong>Leads (last 12 months)</strong>
            </div>
            <div class="card-body">
                <canvas id="leadsTrendChart" height="120"></canvas>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0">
                <strong>Clients (last 12 months)</strong>
            </div>
            <div class="card-body">
                <canvas id="clientsTrendChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-0">
                <strong>Task Status</strong>
            </div>
            <div class="card-body">
                <canvas id="taskStatusChart" height="220"></canvas>
                <div class="mt-3 small text-muted">
                    Pending: {{ $tasksPending }} &nbsp;|&nbsp; Completed: {{ $tasksCompleted }}
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 d-flex justify-content-between">
                <strong>Recent Activity</strong>
                @if($activities->isEmpty())
                    <small class="text-muted">Enable activity logs to see timeline</small>
                @endif
            </div>
            <div class="card-body" style="max-height: 360px; overflow:auto;">
                @forelse ($activities as $act)
                    <div class="mb-3">
                        <div class="fw-semibold">{{ ucfirst($act->event ?? 'activity') }}</div>
                        <div class="small text-muted">
                            {{ optional($act->causer)->name ?? 'System' }} •
                            {{ $act->created_at->diffForHumans() }}
                        </div>
                        @if(!empty($act->description))
                            <div class="small">{{ $act->description }}</div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">No activity yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const labels = @json($labels);
    const leadsSeries = @json($leadsSeries);
    const clientsSeries = @json($clientsSeries);
    const tasksPending = @json($tasksPending);
    const tasksCompleted = @json($tasksCompleted);

    const mkChart = (el, type, data, options={}) =>
        new Chart(document.getElementById(el), { type, data, options });

    mkChart('leadsTrendChart', 'line', {
        labels,
        datasets: [{ label: 'Leads', data: leadsSeries, tension: 0.3 }]
    });

    mkChart('clientsTrendChart', 'bar', {
        labels,
        datasets: [{ label: 'Clients', data: clientsSeries }]
    });

    mkChart('taskStatusChart', 'doughnut', {
        labels: ['Pending', 'Completed'],
        datasets: [{ data: [tasksPending, tasksCompleted] }]
    }, {
        plugins: { legend: { position: 'bottom' } }
    });
})();
</script>
@endpush
