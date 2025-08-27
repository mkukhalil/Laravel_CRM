<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Lead;
use App\Models\Task;
use App\Models\Client;
use Carbon\Carbon;

class ManagerController extends Controller
{
    public function index()
    {
        $managerId = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | TASKS QUERY
        |--------------------------------------------------------------------------
        */
        $taskQuery = Task::where(function ($query) use ($managerId) {
            $query->where('created_by', $managerId)
                  ->orWhere('user_id', $managerId);
        });

        $assignedTasks   = (clone $taskQuery)->count();
        $pendingTasks    = (clone $taskQuery)->where('status', 'pending')->count();
        $completedTasks  = (clone $taskQuery)->where('status', 'completed')->count();

        /*
        |--------------------------------------------------------------------------
        | LEADS QUERY
        |--------------------------------------------------------------------------
        */
        $leadQuery = Lead::where(function ($query) use ($managerId) {
            $query->where('assigned_by', $managerId)
                  ->orWhere('assigned_to', $managerId);
        });

        $leadCount = (clone $leadQuery)->count();

        /*
        |--------------------------------------------------------------------------
        | CLIENTS QUERY
        |--------------------------------------------------------------------------
        */
        $clientsCount = Client::where(function ($query) use ($managerId) {
            $query->where('created_by', $managerId)
                  ->orWhere('assigned_to', $managerId);
        })->count();

        /*
        |--------------------------------------------------------------------------
        | TRENDS (Month-over-month change %)
        |--------------------------------------------------------------------------
        */
        $leadTrend = $this->calculateTrend($leadQuery, 'created_at');
        $taskTrend = $this->calculateTrend($taskQuery, 'created_at');

        /*
        |--------------------------------------------------------------------------
        | CHART DATA: Leads & Tasks over last 12 months
        |--------------------------------------------------------------------------
        */
        $leadSeries = $this->buildMonthlySeries($leadQuery);
        $taskSeries = $this->buildMonthlySeries($taskQuery);

        /*
        |--------------------------------------------------------------------------
        | TASK STATUS COUNTS (For Donut Chart)
        |--------------------------------------------------------------------------
        */
        $taskStatusRaw = (clone $taskQuery)
            ->selectRaw('LOWER(TRIM(status)) as status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Ensure consistent order
        $statusOrder = ['pending', 'completed'];
        $taskStatusCounts = collect($statusOrder)
            ->mapWithKeys(fn($status) => [$status => $taskStatusRaw[$status] ?? 0])
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | RECENT LEADS & TASKS
        |--------------------------------------------------------------------------
        */
        $recentLeads = (clone $leadQuery)->latest()->take(5)->get();
        $recentTasks = (clone $taskQuery)->latest()->take(5)->get();

        /*
        |--------------------------------------------------------------------------
        | COLOR PALETTE
        |--------------------------------------------------------------------------
        */
        $palette = [
        'primary'   => '#4e73df',
        'success'   => '#1cc88a',
        'danger'    => '#e74a3b',
        'warning'   => '#f6c23e',
        'info'      => '#36b9cc',
        'muted'     => '#858796',
        'lightFill' => 'rgba(78, 115, 223, 0.12)',
    ];

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */
        return view('manager.dashboard', compact(
            'leadCount',
            'assignedTasks',
            'pendingTasks',
            'completedTasks',
            'clientsCount',
            'leadTrend',
            'taskTrend',
            'leadSeries',
            'taskSeries',
            'taskStatusCounts',
            'recentLeads',
            'recentTasks',
            'palette'
        ));
    }

    private function calculateTrend($query, $dateField)
    {
        $currentMonth = (clone $query)
            ->whereBetween($dateField, [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->count();

        $previousMonth = (clone $query)
            ->whereBetween($dateField, [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
            ->count();

        if ($previousMonth == 0) {
            return $currentMonth > 0 ? 100 : 0;
        }

        return round((($currentMonth - $previousMonth) / $previousMonth) * 100, 1);
    }

    private function buildMonthlySeries($query)
    {
        $labels = [];
        $series = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');
            $series[] = (clone $query)
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
        }

        return ['labels' => $labels, 'series' => $series];
    }
}
