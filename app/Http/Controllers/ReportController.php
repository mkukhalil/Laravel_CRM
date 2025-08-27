<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;

class ReportController extends Controller
{
    // Reuse a consistent chart palette (same as your dashboard)
    private array $palette = [
        'primary'   => '#4e73df', // blue
        'success'   => '#1cc88a', // green
        'danger'    => '#e74a3b', // red
        'warning'   => '#f6c23e', // yellow
        'info'      => '#36b9cc', // teal
        'muted'     => '#858796', // gray
        'lightFill' => 'rgba(78, 115, 223, 0.15)',   // primary light
        'infoFill'  => 'rgba(54, 185, 204, 0.2)',    // info light
        'succFill'  => 'rgba(28, 200, 138, 0.15)',   // success light
    ];

    public function index(Request $request)
    {
        $user = Auth::user();

        // Filters (shared)
        $tab          = $request->get('tab', 'leads'); // active tab
        $from         = $request->get('from');
        $to           = $request->get('to');
        $status       = $request->get('status');       // used per-tab
        $assignedToId = $request->get('assigned_to');  // used per-tab

        // Date range bounds
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate   = $to   ? Carbon::parse($to)->endOfDay()     : null;

        // --- LEADS ---
        $leadQuery = $this->leadBaseQueryFor($user);
        if ($status)        $leadQuery->where('status', $status);
        if ($assignedToId)  $leadQuery->where('assigned_to', $assignedToId);
        if ($fromDate)      $leadQuery->where('created_at', '>=', $fromDate);
        if ($toDate)        $leadQuery->where('created_at', '<=', $toDate);

        $leadsPaginated = (clone $leadQuery)->latest()->paginate(10)->appends($request->query());
        $leadsAll       = (clone $leadQuery)->get();


        
        $leadStatusCounts = $leadsAll->groupBy('status')->map->count();
        $leadOrder = ['New', 'Contacted', 'Qualified', 'Converted']; 
        $leadStatusCounts = collect($leadOrder)
        ->mapWithKeys(fn($status) => [$status => $leadStatusCounts[$status] ?? 0]);


        $leadMonthly      = $this->monthlyCount($leadsAll);

        // For Admin/Manager: show agent breakdown used in chart and agent filter
        $leadAgents = collect();
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            $leadAgents = $this->agentsForLeadsFilter($user);
        }
        $leadAgentChart = null;
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            $leadAgentChart = $leadsAll->groupBy('assigned_to')->map(function ($group, $userId) {
                $u = User::find($userId);
                return [
                    'name'  => optional($u)->name ?? 'Unknown',
                    'count' => $group->count()
                ];
            })->values();
        }

        // --- TASKS ---
        $taskQuery = Task::visibleTo($user);
        if ($status)        $taskQuery->where('status', $status);
        if ($assignedToId && $user->hasAnyRole(['Admin', 'Manager'])) {
            $taskQuery->where('user_id', $assignedToId);
        }
        if ($fromDate)      $taskQuery->where('created_at', '>=', $fromDate);
        if ($toDate)        $taskQuery->where('created_at', '<=', $toDate);

        $tasksPaginated = (clone $taskQuery)->latest()->paginate(10)->appends($request->query());
        $tasksAll       = (clone $taskQuery)->get();

        $taskStatusCounts = $tasksAll->groupBy('status')->map->count();
        $taskOrder = ['completed', 'pending']; 
        $taskStatusCounts = collect($taskOrder)
        ->mapWithKeys(fn($status) => [$status => $taskStatusCounts[$status] ?? 0]);
        $taskMonthly      = $this->monthlyCount($tasksAll);
        $taskAgents = collect();
        if ($user->hasRole('Admin')) {
            $taskAgents = User::whereHas('roles', fn($q) => $q->whereIn('name', ['Agent','Manager']))->get();
        } elseif ($user->hasRole('Manager')) {
            $taskAgents = User::whereIn('id', Task::where('created_by', $user->id)->pluck('user_id')->unique())->get();
        }

        $taskAgentChart = null;
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            $taskAgentChart = $tasksAll->groupBy('user_id')->map(function ($group, $id) {
                $u = User::find($id);
                return [
                    'name'  => optional($u)->name ?? 'Unknown',
                    'count' => $group->count()
                ];
            })->values();
        }

        // --- CLIENTS ---
        $clientQuery = $this->clientBaseQueryFor($user);
        if ($status)        $clientQuery->where('status', $status);
        if ($assignedToId)  $clientQuery->where('assigned_to', $assignedToId);
        if ($fromDate)      $clientQuery->where('created_at', '>=', $fromDate);
        if ($toDate)        $clientQuery->where('created_at', '<=', $toDate);

        $clientsPaginated = (clone $clientQuery)->latest()->paginate(10)->appends($request->query());
        $clientsAll       = (clone $clientQuery)->get();

        $clientStatusCounts = $clientsAll->groupBy('status')->map->count();
        $clientMonthly      = $this->monthlyCount($clientsAll);

        $clientAgents = collect();
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            $clientAgents = $this->agentsForClientsFilter($user);
        }

        $data = [
            'tab' => $tab,

            // shared filters/role lists
            'from' => $from, 'to' => $to, 'status' => $status, 'assigned_to' => $assignedToId,
            'leadAgents'   => $leadAgents,
            'taskAgents'   => $taskAgents,
            'clientAgents' => $clientAgents,

            // leads
            'leads'            => $leadsPaginated,
            'leadStatusCounts' => $leadStatusCounts,
            'leadMonthly'      => $leadMonthly,
            'leadAgentChart'   => $leadAgentChart,

            // tasks
            'tasks'            => $tasksPaginated,
            'taskStatusCounts' => $taskStatusCounts,
            'taskMonthly'      => $taskMonthly,
            'taskAgentChart'   => $taskAgentChart,

            // clients
            'clients'            => $clientsPaginated,
            'clientStatusCounts' => $clientStatusCounts,
            'clientMonthly'      => $clientMonthly,

            // palette (for your views)
            'palette' => $this->palette,

            // context labels
            'leadContext'   => $this->contextFor($user, 'Leads'),
            'taskContext'   => $this->contextFor($user, 'Tasks'),
            'clientContext' => $this->contextFor($user, 'Clients'),
        ];

        return view('reports.index', $data);
    }

    // ---------- CSV EXPORTS (Admin/Manager only) ----------

    public function exportLeads(Request $request)
    {
        $this->authorizeManager();
        $q = $this->leadBaseQueryFor(Auth::user());
        $this->applyCommonFilters($q, $request, 'leads');
        $rows = $q->orderBy('created_at', 'desc')->get(['id','name','email','phone','status','source','assigned_to','created_at']);
        return $this->toCsvDownload($rows, 'leads_report.csv', ['ID','Name','Email','Phone','Status','Source','Assigned To','Created At']);
    }

    public function exportTasks(Request $request)
    {
        $this->authorizeManager();
        $q = Task::visibleTo(Auth::user());
        $this->applyCommonFilters($q, $request, 'tasks');
        $rows = $q->orderBy('created_at', 'desc')->get(['id','title','status','user_id','created_by','created_at']);
        return $this->toCsvDownload($rows, 'tasks_report.csv', ['ID','Title','Status','Assigned User','Created By','Created At']);
    }

    public function exportClients(Request $request)
    {
        $this->authorizeManager();
        $q = $this->clientBaseQueryFor(Auth::user());
        $this->applyCommonFilters($q, $request, 'clients');
        $rows = $q->orderBy('created_at', 'desc')->get(['id','name','email','phone','company_name','status','assigned_to','created_at']);
        return $this->toCsvDownload($rows, 'clients_report.csv', ['ID','Name','Email','Phone','Company','Status','Assigned To','Created At']);
    }

    // ---------- Helpers ----------

    private function leadBaseQueryFor($user)
    {
        if ($user->hasRole('Admin'))  return Lead::query();
        if ($user->hasRole('Manager')) return Lead::where('assigned_by', $user->id);
        return Lead::where('assigned_to', $user->id); // Agent
    }

    private function clientBaseQueryFor($user)
    {
        if ($user->hasRole('Admin'))   return Client::query();
        if ($user->hasRole('Manager')) return Client::where('created_by', $user->id);
        return Client::where('assigned_to', $user->id); // Agent
    }

    private function applyCommonFilters($query, Request $request, string $type)
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // leads/clients assigned_to; tasks uses user_id
        if ($request->filled('assigned_to')) {
            if ($type === 'tasks') {
                $query->where('user_id', $request->assigned_to);
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', Carbon::parse($request->from)->startOfDay());
        }

        if ($request->filled('to')) {
            $query->where('created_at', '<=', Carbon::parse($request->to)->endOfDay());
        }
    }

    private function monthlyCount(Collection $items): array
    {
        // Build last 12 months from oldest to newest
        $now = Carbon::now();
        $months = collect(range(0, 11))->map(fn($i) => $now->copy()->subMonths(11 - $i)->startOfMonth());
        $labels = $months->map(fn($d) => $d->format('M Y'))->values();

        $grouped = $items->groupBy(function ($model) {
            return Carbon::parse($model->created_at)->format('Y-m');
        })->map->count();

        $series = [];
        foreach ($months as $d) {
            $key = $d->format('Y-m');
            $series[] = $grouped[$key] ?? 0;
        }

        return ['labels' => $labels, 'series' => $series];
    }

    private function agentsForLeadsFilter($user)
    {
        if ($user->hasRole('Admin')) {
            return User::role('Agent')->get();
        }
        // Manager -> agents he assigned leads to
        $agentIds = Lead::where('assigned_by', $user->id)->pluck('assigned_to')->unique();
        return User::whereIn('id', $agentIds)->get();
    }

    private function agentsForClientsFilter($user)
    {
        if ($user->hasRole('Admin')) {
            return User::role('Agent')->get();
        }
        // Manager -> agents under his created clients
        $agentIds = Client::where('created_by', $user->id)->pluck('assigned_to')->unique();
        return User::whereIn('id', $agentIds)->get();
    }

    private function contextFor($user, string $base): string
    {
        return $user->hasRole('Admin')
            ? "All $base"
            : ($user->hasRole('Manager') ? "Assigned $base" : "Your $base");
    }

    private function authorizeManager(): void
    {
        abort_unless(Auth::user()->hasAnyRole(['Admin','Manager']), 403);
    }

    private function toCsvDownload(Collection $rows, string $filename, array $headers = [])
    {
        $callback = function () use ($rows, $headers) {
            $FH = fopen('php://output', 'w');
            if (!empty($headers)) fputcsv($FH, $headers);
            foreach ($rows as $r) fputcsv($FH, $r->toArray());
            fclose($FH);
        };

        return Response::streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
