<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'CRM Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .sidebar {
            height: 100vh;
            background: #343a40;
            color: white;
            padding-top: 1rem;
        }
        .sidebar a {
            color: #adb5bd;
            padding: 0.75rem 1rem;
            display: block;
            text-decoration: none;
        }
        .sidebar a:hover {
            background: #495057;
            color: #fff;
        }
        .logout-btn {
            border: none;
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
        }
        .logout-btn:hover {
            background: #c82333;
        }
        .hover-bg:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar p-4 bg-dark text-white" style="min-height: 100vh;">
    <h4 class="mb-4">CRM</h4>

    {{-- Admin Links --}}
    @role('Admin')
        <x-sidebar-link icon="bi-speedometer2" :route="route('admin.dashboard')" label="Dashboard" />
        <x-sidebar-link icon="bi-people" :route="route('users.index')" label="Manage Users" />
        <x-sidebar-link icon="bi-journal-text" :route="route('leads.index')" label="Leads" />
        <x-sidebar-link icon="bi-graph-up" route="#" label="Reports" />
    @endrole

    {{-- Manager Links --}}
    @role('Manager')
        <x-sidebar-link icon="bi-speedometer2" :route="route('manager.dashboard')" label="Dashboard" />
        <x-sidebar-link icon="bi-journal-text" :route="route('leads.index')" label="Leads" />
        <x-sidebar-link icon="bi-bar-chart" route="#" label="Reports" />
    @endrole

    {{-- Agent Links --}}
    @role('Agent')
        <x-sidebar-link icon="bi-speedometer2" :route="route('agent.dashboard')" label="Dashboard" />
        <x-sidebar-link icon="bi-journal-text" :route="route('leads.index')" label="My Leads" />
    @endrole

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="btn btn-outline-light w-100 text-start">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
        </button>
    </form>
</div>


    <!-- Main Content -->
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5>Welcome, {{ auth()->user()->name }}</h5>
                <small class="text-muted">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</small>
            </div>
            <button class="btn btn-outline-secondary btn-sm" id="toggleTheme">
                <i class="bi bi-brightness-high"></i>
            </button>
        </div>

        @include('partials.flash')
        @yield('content')
    </div>
</div>

<script>
    // Dark mode toggle
    document.getElementById('toggleTheme').addEventListener('click', function () {
        const html = document.documentElement;
        html.dataset.bsTheme = html.dataset.bsTheme === 'dark' ? 'light' : 'dark';
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>
</html>
