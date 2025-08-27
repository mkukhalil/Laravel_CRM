<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
});

// Logout
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Landing route with role-based redirection
Route::get('/', function () {
    if (!Auth::check()) {
        return view('welcome');
    }

    $role = Auth::user()->getRoleNames()->first();

    return match ($role) {
        'Admin' => redirect()->route('admin.dashboard'),
        'Manager' => redirect()->route('manager.dashboard'),
        'Agent' => redirect()->route('agent.dashboard'),
        default => redirect('/login')->withErrors(['role' => 'No valid role assigned']),
    };
});

// Admin-only routes
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::resource('users', UserController::class);
    Route::post('/users/{id}/update-role', [UserController::class, 'updateRole'])->name('users.updateRole');
});

// Manager-only routes
Route::middleware(['auth', 'role:Manager'])->get('/manager/dashboard', [ManagerController::class, 'index'])->name('manager.dashboard');

// Agent-only routes
Route::middleware(['auth', 'role:Agent'])->get('/agent/dashboard', [AgentController::class, 'index'])->name('agent.dashboard');

// Shared Leads module for all roles
Route::middleware(['auth', 'role:Admin|Manager|Agent'])->group(function () {
    Route::resource('leads', LeadController::class);
});

// Fallback for unknown routes
Route::fallback(fn () => redirect('/login'));

Route::middleware(['auth', 'role:Admin|Manager|Agent'])->group(function () {
    Route::resource('tasks', TaskController::class);
});

Route::post('/leads/{lead}/convert', [App\Http\Controllers\LeadController::class, 'convertToClient'])
    ->name('leads.convert')
    ->middleware('auth');

Route::resource('clients', ClientController::class)->middleware('auth');

 
Route::post('/notifications/read', function () {
    Auth::user()->unreadNotifications->markAsRead();
    return back();
})->name('notifications.read')->middleware('auth');

// routes/web.php
Route::delete('/notifications/clear', function () {
    Auth::user()->notifications()->delete();
    return back();
})->name('notifications.clear')->middleware('auth');

// Notification Routes (cleaned up)
Route::middleware('auth')->group(function () {
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::delete('/notifications/clear', [NotificationController::class, 'clearAll'])->name('notifications.clear');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markOneAsRead'])->name('notifications.markOneAsRead');
    Route::delete('/notifications/{id}/delete', [NotificationController::class, 'delete'])->name('notifications.delete');
});
Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');

// Reports (single page with tabs + CSV exports)
Route::middleware(['auth'])->group(function () {
    Route::get('/reports', [ ReportController::class, 'index'])->name('reports.index');

    // CSV exports (Admin/Manager only)
    Route::middleware('role:Admin|Manager')->group(function () {
        Route::get('/reports/export/leads',   [ ReportController::class, 'exportLeads'])->name('reports.export.leads');
        Route::get('/reports/export/tasks',   [ ReportController::class, 'exportTasks'])->name('reports.export.tasks');
        Route::get('/reports/export/clients', [ ReportController::class, 'exportClients'])->name('reports.export.clients');
    });
});

Route::patch('/leads/{id}/update-status', [LeadController::class, 'updateStatus'])
    ->name('leads.updateStatus');
Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');



