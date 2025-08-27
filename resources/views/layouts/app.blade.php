<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'CRM Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
 
@stack('styles')
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        /* Sidebar Background */
.sidebar {
    background-color: #111827; /* Deep Navy */
    color: #f3f4f6; /* Light Text */
    min-height: 100vh;
}

/* Sidebar Links */
.sidebar .nav-link {
    color: #9ca3af; /* Muted Gray */
    border-radius: 8px;
    padding: 10px 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s ease;
}

/* Active Link */
.sidebar .nav-link.active {
    background-color: #2563eb; /* Blue Highlight */
    color: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

/* Hover Effect */
.sidebar .nav-link:hover {
    background-color: #1e3a8a; /* Darker Blue */
    color: #fff;
    transform: translateX(3px);
}

/* Section Heading */
.sidebar .section-title {
    padding: 6px 14px;
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
    font-weight: bold;
    margin-top: 20px;
}


        .topbar {
            background-color: white;
            padding: 10px 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
        }
    </style>

</head>
<body>
<div class="d-flex">
    @auth
    <div class="sidebar p-3">
        <h5 class="mb-4 text-white">CRM</h5>
        @include('partials.sidebar')
    </div>
    @endauth

    <div class="flex-grow-1">
        <div class="topbar d-flex justify-content-between align-items-center">
            <h5 class="mb-0">@yield('title')</h5>
            <div class="d-flex align-items-center gap-3">
                @auth
                @php
                    $notifications = Auth::user()->unreadNotifications;
                @endphp
                <div class="dropdown">
                    <a class="btn btn-light position-relative dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false" id="notifDropdown">
                        <i class="bi bi-bell-fill"></i>
                        @if($notifications->count() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $notifications->count() }}
                                <span class="visually-hidden">unread notifications</span>
                            </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm p-2" style="width: 300px;">
                        <li class="dropdown-header fw-semibold d-flex justify-content-between align-items-center">
                            Notifications
                        </li>
                        @if($notifications->count())
                            <li class="dropdown-item text-center">
                                <form method="POST" action="{{ route('notifications.clear') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger w-100">Clear All</button>
                                </form>
                            </li>
                            @foreach ($notifications as $note)
                                <li class="dropdown-item border-bottom small py-2 d-flex justify-content-between align-items-start">
                                    <div style="flex: 1;">
                                        <a href="{{ url('tasks/' . ($note->data['task_id'] ?? '#')) }}" class="text-decoration-none text-dark">
                                            <div class="fw-semibold">{{ $note->data['title'] ?? 'New Lead' }}</div>
                                            <div class="text-muted">Assigned by {{ $note->data['assigned_by'] }}</div>
                                            <div class="text-muted small">{{ $note->created_at->diffForHumans() }}</div>
                                        </a>
                                    </div>
                                    <div class="d-flex flex-column align-items-end ms-2">
                                        <form method="POST" action="{{ route('notifications.markOneAsRead', $note->id) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success mb-1 btn-sm px-2 py-0">✔</button>
                                        </form>
                                        <form method="POST" action="{{ route('notifications.delete', $note->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger btn-sm px-2 py-0">✖</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        @else
                            <li class="dropdown-item text-muted text-center py-3">No new notifications</li>
                        @endif
                    </ul>
                </div>
                @endauth

                <span class="text-muted">
                    Hello, {{ Auth::check() ? Auth::user()->name : 'Guest' }}
                </span>

                @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
                @endauth
            </div>
        </div>

        <div class="p-4">
            @include('partials.flash')
            @yield('content')
        </div>
    </div>
</div>

<!-- Bootstrap & Notification Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.toast').forEach(toastEl => {
            new bootstrap.Toast(toastEl).show();
        });

        const dropdown = document.getElementById('notifDropdown');
        dropdown?.addEventListener('click', function () {
            fetch("{{ route('notifications.markAsRead') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                }
            });
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stack('scripts')
@yield('scripts')

</body>
</html>
