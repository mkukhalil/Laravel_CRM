<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\TaskAssigned;

class TaskController extends Controller
{
   public function index(Request $request)
{
    $user = Auth::user();

    $query = Task::with('user');

    if ($user->hasRole('Admin')) {
        // Admin can see all tasks
    } elseif ($user->hasRole('Manager')) {
        // Manager can see tasks assigned to or created by them
        $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('created_by', $user->id);
        });
    } else {
        // Agent can only see tasks assigned to them
        $query->where('user_id', $user->id);
    }

    // Apply filters
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('assigned_to') && $user->hasRole(['Admin', 'Manager'])) {
        $query->where('user_id', $request->assigned_to);
    }

    $tasks = $query->latest()->get();

    // For role filter dropdown
    $agents = $user->hasRole(['Admin', 'Manager'])
        ? User::role('Agent')->get()
        : collect(); // Empty if not Admin/Manager

    return view('tasks.index', compact('tasks', 'agents'));
}


    public function create()
    {
        $users = User::role(['Agent', 'Manager'])->get();
        return view('tasks.create', compact('users'));
    }

    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'user_id' => 'required|exists:users,id',
    ]);

    $task = Task::create([
        'title' => $request->title,
        'description' => $request->description,
        'user_id' => $request->user_id,
        'created_by' => Auth::id(),
        'status' => 'pending',
    ]);

    // Notify assigned user
    $assignedUser = User::find($request->user_id);
    $assignedUser->notify(new TaskAssigned($task));
// TaskController@store - after creating $task
if (function_exists('activity')) {
    activity()
        ->causedBy(Auth::user())
        ->performedOn($task)
        ->event('task.created')
        ->withProperties(['assigned_to' => $task->user_id])
        ->log('Task created');
}

    return redirect()->route('tasks.index')->with('success', 'Task created and notification sent.');
}
public function show(Task $task)
{
    $this->authorizeTask($task); // reuses your access control logic
    return view('tasks.show', compact('task'));
}

    public function edit(Task $task)
    {
        $this->authorizeTask($task);
        $users = User::role(['Agent', 'Manager'])->get();

        return view('tasks.edit', compact('task', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTask($task);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:pending,completed',
        ]);

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => $request->user_id,
            'status' => $request->status,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        $this->authorizeTask($task);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }

    private function authorizeTask(Task $task)
    {
        $user = Auth::user();
        if ($user->hasRole('Admin')) return;

        abort_unless(
            $task->created_by === $user->id || $task->user_id === $user->id,
            403,
            'Unauthorized'
        );
    }
}
