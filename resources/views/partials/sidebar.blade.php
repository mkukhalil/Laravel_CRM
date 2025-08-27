@php
    $user = Auth::user();
    $role = $user ? $user->getRoleNames()->first() : null;
@endphp

@if($user)
    <ul class="nav flex-column">
        {{-- Admin --}}
        @if($role === 'Admin')
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people me-2"></i> Users
                </a>
            </li>
        @endif

        {{-- Manager --}}
        @if($role === 'Manager')
            <li class="nav-item">
                <a href="{{ route('manager.dashboard') }}" class="nav-link {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
        @endif

        {{-- Agent --}}
        @if($role === 'Agent')
            <li class="nav-item">
                <a href="{{ route('agent.dashboard') }}" class="nav-link {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
        @endif

        {{-- Common for All --}}
        @if(in_array($role, ['Admin', 'Manager', 'Agent']))
            
            <li class="nav-item">
                <a href="{{ route('leads.index') }}" class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}">
                    <i class="bi bi-person-lines-fill me-2"></i> Leads
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('tasks.index') }}" class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                    <i class="bi bi-check2-square me-2"></i> Tasks
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    <i class="bi bi-person-check-fill me-2"></i> Clients
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line-fill me-2"></i> Reports
                </a>
            </li>
        @endif
    </ul>
@endif
