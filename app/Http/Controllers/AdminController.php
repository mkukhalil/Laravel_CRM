<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Lead;
use App\Models\Task;
use App\Models\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // --- Base counts
        $userCount   = User::count();
        $leadCount   = Lead::count();
        $taskCount   = Task::count();
        $clientCount = Client::count();

        // --- Periods
        $now             = Carbon::now();
        $startThisMonth  = $now->copy()->startOfMonth();
        $startPrevMonth  = $now->copy()->subMonth()->startOfMonth();
        $endPrevMonth    = $startThisMonth->copy()->subSecond();

        // Helper to compute % change month-over-month
        $moM = function (string $modelClass) use ($startThisMonth, $startPrevMonth, $endPrevMonth) {
            $thisMonth = $modelClass::where('created_at', '>=', $startThisMonth)->count();
            $prevMonth = $modelClass::whereBetween('created_at', [$startPrevMonth, $endPrevMonth])->count();
            $percent   = $prevMonth > 0 ? round((($thisMonth - $prevMonth) / $prevMonth) * 100) : null;

            return ['current' => $thisMonth, 'previous' => $prevMonth, 'percent' => $percent];
        };

        $userTrend   = $moM(User::class);
        $leadTrend   = $moM(Lead::class);
        $taskTrend   = $moM(Task::class);
        $clientTrend = $moM(Client::class);

        // --- Build last 12 month labels
        $months = collect(range(0, 11))->map(fn ($i) => $now->copy()->subMonths(11 - $i)->startOfMonth());
        $labels = $months->map(fn ($d) => $d->format('M Y'))->values();

        // Map helper to get series for a model
        $seriesFor = function (string $table) use ($months) {
            $from = $months->first()->copy()->startOfMonth();
            $raw = DB::table($table)
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COUNT(*) as c')
                ->where('created_at', '>=', $from)
                ->groupBy('ym')
                ->orderBy('ym')
                ->pluck('c', 'ym')
                ->toArray();

            $series = [];
            foreach ($months as $d) {
                $ym = $d->format('Y-m');
                $series[] = $raw[$ym] ?? 0;
            }
            return $series;
        };

        $leadsSeries   = $seriesFor('leads');
        $clientsSeries = $seriesFor('clients');

        // Task status breakdown (pending/completed)
        $taskStatus = Task::select('status', DB::raw('COUNT(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();

        $tasksPending   = $taskStatus['pending']   ?? 0;
        $tasksCompleted = $taskStatus['completed'] ?? 0;

        // Optional activity feed (Spatie)
        $activities = class_exists(\Spatie\Activitylog\Models\Activity::class)
            ? \Spatie\Activitylog\Models\Activity::latest()->limit(10)->get()
            : collect();

        return view('admin.dashboard', [
            'userCount'   => $userCount,
            'leadCount'   => $leadCount,
            'taskCount'   => $taskCount,
            'clientCount' => $clientCount,

            // trends (% vs last month)
            'userTrend'   => $userTrend['percent'],
            'leadTrend'   => $leadTrend['percent'],
            'taskTrend'   => $taskTrend['percent'],
            'clientTrend' => $clientTrend['percent'],

            // charts
            'labels'        => $labels,
            'leadsSeries'   => $leadsSeries,
            'clientsSeries' => $clientsSeries,
            'tasksPending'  => $tasksPending,
            'tasksCompleted'=> $tasksCompleted,

            // activity
            'activities'    => $activities,
        ]);
    }
}
