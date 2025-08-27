@extends('layouts.app')

@section('title', 'Leads')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">Leads Management</h4>
    <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle"></i> Add Lead
    </a>
</div>

<style>
    .kanban-board {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 20px;
    }
    .kanban-column {
        flex: 1;
        min-width: 250px;
        background-color: #f8f9fa;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    .kanban-column h4 {
        font-size: 1.1rem;
        font-weight: bold;
        margin-bottom: 15px;
        padding: 10px;
        border-radius: 8px;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .status-new { background-color: #f6c23e; }
    .status-contacted { background-color: #4e73df; color: #000; }
    .status-qualified { background-color: #1cc88a; }
    .status-converted { background-color: #36b9cc; }
    .kanban-item {
        background-color: #ffffff;
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 10px;
        box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        cursor: grab;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kanban-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .kanban-item p {
        margin: 0;
        font-size: 0.9rem;
        color: #555;
    }
    .kanban-item strong {
        font-size: 1rem;
        color: #333;
    }
</style>

<div class="kanban-board">
    @foreach(['New', 'Contacted', 'Qualified', 'Converted'] as $status)
    <div class="kanban-column" data-status="{{ $status }}">
        <h4 class="status-{{ strtolower($status) }}">
            {{ $status }} 
            <span class="badge bg-light text-dark">{{ $leads->where('status', $status)->count() }}</span>
        </h4>
        @foreach($leads->where('status', $status) as $lead)
            <div class="kanban-item" draggable="true" data-id="{{ $lead->id }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div onclick="window.location.href='{{ route('leads.edit', $lead->id) }}'" style="cursor:pointer;">
                        <strong>{{ $lead->name }}</strong>
                        <p>{{ $lead->email }}</p>
                    </div>
                    @if(Auth::user()->hasRole('Admin') || ($lead->status !== 'Converted' && 
                        ((Auth::user()->hasRole('Manager') && $lead->assigned_by == Auth::id()) || 
                         (Auth::user()->hasRole('Agent') && $lead->assigned_to == Auth::id()))))
                        <button class="btn btn-sm btn-link text-danger p-0 delete-lead" data-id="{{ $lead->id }}" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @endforeach
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let draggedItem = null;
    let currentUserRole = "{{ Auth::user()->getRoleNames()->first() }}";

    // Drag and drop events
    document.querySelectorAll('.kanban-item').forEach(item => {
        item.addEventListener('dragstart', function (e) {
            let parentColumn = this.closest('.kanban-column').getAttribute('data-status');
            if (parentColumn === 'Converted' && currentUserRole !== 'Admin') {
                e.preventDefault();
                return;
            }
            draggedItem = this;
            setTimeout(() => this.style.display = 'none', 0);
        });

        item.addEventListener('dragend', function () {
            if (draggedItem) {
                setTimeout(() => {
                    draggedItem.style.display = 'block';
                    draggedItem = null;
                }, 0);
            }
        });
    });

    document.querySelectorAll('.kanban-column').forEach(column => {
        column.addEventListener('dragover', e => e.preventDefault());

        column.addEventListener('drop', function () {
            if (draggedItem) {
                let leadId = draggedItem.getAttribute('data-id');
                let newStatus = this.getAttribute('data-status');
                let oldStatus = draggedItem.closest('.kanban-column').getAttribute('data-status');

                if (oldStatus === 'Converted' && newStatus !== 'Converted' && currentUserRole !== 'Admin') {
                    alert('Only Admin can move leads out of Converted.');
                    return;
                }

                this.appendChild(draggedItem);

                fetch(`/leads/${leadId}/update-status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ status: newStatus })
                }).then(res => res.json())
                  .then(data => console.log('Status updated:', data));
            }
        });
    });

    // Delete button AJAX
    document.querySelectorAll('.delete-lead').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!confirm('Are you sure you want to delete this lead?')) return;

            let leadId = this.getAttribute('data-id');

            fetch(`/leads/${leadId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.closest('.kanban-item').remove();
                } else {
                    alert(data.error || 'Error deleting lead.');
                }
            })
            .catch(() => alert('Error deleting lead.'));
        });
    });
});
</script>
@endsection
