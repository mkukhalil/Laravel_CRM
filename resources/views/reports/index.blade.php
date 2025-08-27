@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Reports</h4>
        <div class="d-flex gap-2">
            @role('Admin|Manager')
                <a href="{{ route('reports.export.leads', request()->all()) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-download"></i> Export Leads CSV
                </a>
                <a href="{{ route('reports.export.tasks', request()->all()) }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download"></i> Export Tasks CSV
                </a>
                <a href="{{ route('reports.export.clients', request()->all()) }}" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-download"></i> Export Clients CSV
                </a>
            @endrole
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports.index') }}" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="col-md-2">
            <label class="form-label">From</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label">To</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <input type="text" name="status" value="{{ $status }}" class="form-control" placeholder="e.g. pending, completed, Converted">
            <!-- <small class="text-muted">Leave empty for all</small> -->
        </div>

        {{-- Assigned user filter is role-aware and depends on active tab context --}}
        <div class="col-md-3">
            <label class="form-label">Assigned User</label>
            <select name="assigned_to" class="form-select">
                <option value="">All</option>
                @if($tab === 'leads')
                    @foreach($leadAgents as $u)
                        <option value="{{ $u->id }}" @selected($assigned_to == $u->id)>{{ $u->name }}</option>
                    @endforeach
                @elseif($tab === 'tasks')
                    @foreach($taskAgents as $u)
                        <option value="{{ $u->id }}" @selected($assigned_to == $u->id)>{{ $u->name }}</option>
                    @endforeach
                @elseif($tab === 'clients')
                    @foreach($clientAgents as $u)
                        <option value="{{ $u->id }}" @selected($assigned_to == $u->id)>{{ $u->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-primary">Apply</button>
        </div>
    </form>

    {{-- Tabs --}}
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'leads' ? 'active' : '' }}"
               href="{{ route('reports.index', array_merge(request()->except('page'), ['tab' => 'leads'])) }}"
               role="tab">Leads</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'tasks' ? 'active' : '' }}"
               href="{{ route('reports.index', array_merge(request()->except('page'), ['tab' => 'tasks'])) }}"
               role="tab">Tasks</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'clients' ? 'active' : '' }}"
               href="{{ route('reports.index', array_merge(request()->except('page'), ['tab' => 'clients'])) }}"
               role="tab">Clients</a>
        </li>
    </ul>

    <div class="tab-content pt-3">

        {{-- ===================== LEADS TAB ===================== --}}
        @if($tab === 'leads')
        <div class="tab-pane fade show active">
            {{-- Metric cards --}}
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">Total Leads</h6>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="fs-4 fw-bold">{{ $leads->total() }}</div>
                                <i class="bi bi-person-lines-fill fs-3 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @foreach($leadStatusCounts as $s => $c)
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">{{ ucfirst($s) }}</h6>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="fs-4 fw-bold">{{ $c }}</div>
                                <i class="bi bi-graph-up fs-3" style="color: {{ $palette['success'] }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Charts --}}
            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0"><strong>{{ $leadContext }} – Status</strong></div>
                        <div class="card-body"><canvas id="leadStatusChart" height="220"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0"><strong>{{ $leadContext }} – Monthly</strong></div>
                        <div class="card-body"><canvas id="leadMonthlyChart" height="220"></canvas></div>
                    </div>
                </div>

                @if($leadAgentChart)
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0"><strong>Agent Breakdown</strong></div>
                        <div class="card-body"><canvas id="leadAgentChart" height="220"></canvas></div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Table --}}
            <div class="card shadow-sm border-0 mt-3">
                <div class="card-header bg-white border-0"><strong>Lead Details</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Source</th>
                                    <th>Assigned To</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leads as $lead)
                                    <tr>
                                        <td>{{ $lead->name }}</td>
                                        <td>{{ $lead->status }}</td>
                                        <td>{{ $lead->source }}</td>
                                        <td>{{ optional($lead->assignedToUser)->name }}</td>
                                        <td>{{ $lead->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No leads found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    {{ $leads->links() }}
                </div>
            </div>
        </div>
        @endif

        {{-- ===================== TASKS TAB ===================== --}}
        @if($tab === 'tasks')
        <div class="tab-pane fade show active">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">Total Tasks</h6>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="fs-4 fw-bold">{{ $tasks->total() }}</div>
                                <i class="bi bi-list-check fs-3 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @foreach($taskStatusCounts as $s => $c)
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">{{ ucfirst($s) }}</h6>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="fs-4 fw-bold">{{ $c }}</div>
                                <i class="bi bi-circle-half fs-3" style="color: {{ $palette['info'] }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0"><strong>{{ $taskContext }} – Status</strong></div>
                        <div class="card-body"><canvas id="taskStatusChart" height="220"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0"><strong>{{ $taskContext }} – Monthly</strong></div>
                        <div class="card-body"><canvas id="taskMonthlyChart" height="220"></canvas></div>
                    </div>
                </div>

                @if($taskAgentChart)
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0"><strong>User Breakdown</strong></div>
                        <div class="card-body"><canvas id="taskAgentChart" height="220"></canvas></div>
                    </div>
                </div>
                @endif
            </div>

            <div class="card shadow-sm border-0 mt-3">
                <div class="card-header bg-white border-0"><strong>Task Details</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Assigned User</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tasks as $task)
                                    <tr>
                                        <td>{{ $task->title }}</td>
                                        <td class="text-capitalize">{{ $task->status }}</td>
                                        <td>{{ optional($task->user)->name }}</td>
                                        <td>{{ $task->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No tasks found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    {{ $tasks->links() }}
                </div>
            </div>
        </div>
        @endif

        {{-- ===================== CLIENTS TAB ===================== --}}
        @if($tab === 'clients')
        <div class="tab-pane fade show active">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">Total Clients</h6>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="fs-4 fw-bold">{{ $clients->total() }}</div>
                                <i class="bi bi-people fs-3" style="color: {{ $palette['primary'] }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @foreach($clientStatusCounts as $s => $c)
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">{{ ucfirst($s) }}</h6>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="fs-4 fw-bold">{{ $c }}</div>
                                <i class="bi bi-person-check fs-3" style="color: {{ $palette['success'] }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0"><strong>{{ $clientContext }} – Status</strong></div>
                        <div class="card-body"><canvas id="clientStatusChart" height="220"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0"><strong>{{ $clientContext }} – Monthly</strong></div>
                        <div class="card-body"><canvas id="clientMonthlyChart" height="220"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mt-3">
                <div class="card-header bg-white border-0"><strong>Client Details</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clients as $c)
                                    <tr>
                                        <td>{{ $c->name }}</td>
                                        <td>{{ $c->email }}</td>
                                        <td>{{ $c->status }}</td>
                                        <td>{{ optional($c->assignedUser)->name }}</td>
                                        <td>{{ $c->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No clients found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    {{ $clients->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Shared palette from PHP
const PALETTE = @json($palette);

// ----------- LEADS CHARTS -----------
@if($tab === 'leads')
new Chart(document.getElementById('leadStatusChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($leadStatusCounts->keys()->values()) !!},
        datasets: [{
            data: {!! json_encode($leadStatusCounts->values()->values()) !!},
            backgroundColor: [PALETTE.warning, PALETTE.primary, PALETTE.success, PALETTE.info, PALETTE.danger],
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } }, maintainAspectRatio: false }
});

new Chart(document.getElementById('leadMonthlyChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($leadMonthly['labels']) !!},
        datasets: [{
            label: 'Leads',
            data: {!! json_encode($leadMonthly['series']) !!},
            borderColor: PALETTE.info,
            backgroundColor: PALETTE.infoFill,
            tension: 0.35,
            fill: true
        }]
    },
    options: { plugins: { legend: { display: false } }, maintainAspectRatio: false }
});

@if($leadAgentChart)
new Chart(document.getElementById('leadAgentChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(collect($leadAgentChart)->pluck('name')) !!},
        datasets: [{
            label: 'Leads',
            data: {!! json_encode(collect($leadAgentChart)->pluck('count')) !!},
            backgroundColor: PALETTE.success,
        }]
    },
    options: { plugins: { legend: { display: false } }, maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
@endif
@endif

// ----------- TASKS CHARTS -----------
@if($tab === 'tasks')
new Chart(document.getElementById('taskStatusChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($taskStatusCounts->keys()->values()) !!},
        datasets: [{
            data: {!! json_encode($taskStatusCounts->values()->values()) !!},
            backgroundColor: [PALETTE.success, PALETTE.warning, PALETTE.danger, PALETTE.info, PALETTE.primary],
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } }, maintainAspectRatio: false }
});

new Chart(document.getElementById('taskMonthlyChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($taskMonthly['labels']) !!},
        datasets: [{
            label: 'Tasks',
            data: {!! json_encode($taskMonthly['series']) !!},
            backgroundColor: PALETTE.primary,
        }]
    },
    options: { plugins: { legend: { display: false } }, maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});

@if($taskAgentChart)
new Chart(document.getElementById('taskAgentChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(collect($taskAgentChart)->pluck('name')) !!},
        datasets: [{
            label: 'Tasks',
            data: {!! json_encode(collect($taskAgentChart)->pluck('count')) !!},
            backgroundColor: PALETTE.success,
        }]
    },
    options: { plugins: { legend: { display: false } }, maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
@endif
@endif

// ----------- CLIENTS CHARTS -----------
@if($tab === 'clients')
new Chart(document.getElementById('clientStatusChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($clientStatusCounts->keys()->values()) !!},
        datasets: [{
            data: {!! json_encode($clientStatusCounts->values()->values()) !!},
            backgroundColor: [PALETTE.success, PALETTE.primary, PALETTE.warning, PALETTE.info, PALETTE.danger],
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } }, maintainAspectRatio: false }
});

new Chart(document.getElementById('clientMonthlyChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($clientMonthly['labels']) !!},
        datasets: [{
            label: 'Clients',
            data: {!! json_encode($clientMonthly['series']) !!},
            borderColor: PALETTE.primary,
            backgroundColor: PALETTE.lightFill,
            tension: 0.35,
            fill: true
        }]
    },
    options: { plugins: { legend: { display: false } }, maintainAspectRatio: false }
});
@endif
</script>
@endpush

@push('styles')
<style>
.chart-container { position: relative; height: 300px; width: 100%; }
.table > :not(caption) > * > * { vertical-align: middle; }
</style>
@endpush
