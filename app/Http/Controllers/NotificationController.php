<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->unreadNotifications()
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    public function all()
    {
        $notifications = Auth::user()->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['status' => 'ok']);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['status' => 'ok']);
    }
}
