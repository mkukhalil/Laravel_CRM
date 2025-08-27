<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Lead;
use App\Models\Task;
use App\Models\Client;

class AgentController extends Controller
{
    private array $palette = [
        'primary'   => '#4e73df',
        'success'   => '#1cc88a',
        'danger'    => '#e74a3b',
        'warning'   => '#f6c23e',
        'info'      => '#36b9cc',
        'muted'     => '#858796',
        'lightFill' => 'rgba(78, 115, 223, 0.12)',
    ];

    public function index()
    {
        $agentId = Auth::id();

        // Basic counts
        $leadCount      = Lead::where('assigned_to', $agentId)->count();
        $assignedTasks  = Task::where('user_id', $agentId)->count();
        $pendingTasks   = Task::where('user_id', $agentId)->where('status', 'pending')->count();
        $completedTasks = Task::where('user_id', $agentId)->where('status', 'completed')->count();

        // Clients converted/assigned to this agent (aligns with your ClientController)
        $clientsCount   = Client::where('assigned_to', $agentId)->count();

        // Series for charts (last 12 months)
        $leadSeries = $this->seriesForTable('leads', ['assigned_to' => $agentId]);
        $taskSeries = $this->seriesForTable('tasks', ['user_id' => $agentId]);

        // Status counts (for donut chart)
       // Get counts
$taskStatusCounts = Task::where('user_id', $agentId)
    ->select('status', DB::raw('COUNT(*) as c'))
    ->groupBy('status')
    ->pluck('c', 'status')
    ->toArray();
$desiredOrder = ['pending', 'completed'];

// Reorder
$taskStatusCounts = collect($desiredOrder)
    ->mapWithKeys(function ($status) use ($taskStatusCounts) {
        return [$status => $taskStatusCounts[$status] ?? 0];
    })
    ->toArray();


        // Recent
        $recentTasks = Task::where('user_id', $agentId)->latest()->take(6)->get();

        // Trends (percentage change from previous month)
        $leadTrend = $this->percentFromSeries($leadSeries['series']);
        $taskTrend = $this->percentFromSeries($taskSeries['series']);

        return view('agent.dashboard', compact(
            'leadCount',
            'assignedTasks',
            'pendingTasks',
            'completedTasks',
            'clientsCount',
            'leadSeries',
            'taskSeries',
            'taskStatusCounts',
            'recentTasks',
            'leadTrend',
            'taskTrend'
        ))->with('palette', $this->palette);
    }

    /**
     * Build a monthly series for a table with given where conditions.
     */
    private function seriesForTable(string $table, array $where = []): array
    {
        $now = Carbon::now();
        $months = collect(range(0, 11))
            ->map(fn($i) => $now->copy()->subMonths(11 - $i)->startOfMonth());
        $labels = $months->map(fn($d) => $d->format('M Y'))->toArray();

        $from = $months->first()->copy()->startOfMonth();

        $query = DB::table($table)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COUNT(*) as c')
            ->where('created_at', '>=', $from);

        foreach ($where as $column => $value) {
            $query->where($column, $value);
        }

        $raw = $query->groupBy('ym')
            ->orderBy('ym')
            ->pluck('c', 'ym')
            ->toArray();

        $series = [];
        foreach ($months as $d) {
            $ym = $d->format('Y-m');
            $series[] = $raw[$ym] ?? 0;
        }

        return ['labels' => $labels, 'series' => $series];
    }

    /**
     * Calculate percentage change between last two months.
     */
    private function percentFromSeries(array $series): ?int
    {
        $n = count($series);
        if ($n < 2) return null;

        $current = (int) $series[$n - 1];
        $previous = (int) $series[$n - 2];

        if ($previous === 0) return null;

        return (int) round((($current - $previous) / $previous) * 100);
    }
}
