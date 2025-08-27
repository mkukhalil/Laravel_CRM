<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function clearAll()
    {
        Auth::user()->notifications()->delete();
        return back()->with('success', 'All notifications cleared.');
    }

    public function markOneAsRead($id)
    {
        $notification = Auth::user()->unreadNotifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
        return back()->with('success', 'Notification marked as read.');
    }

    public function delete($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        if ($notification) {
            $notification->delete();
        }
        return back()->with('success', 'Notification deleted.');
    }
}
