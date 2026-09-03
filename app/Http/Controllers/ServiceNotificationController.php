<?php

namespace App\Http\Controllers;

use App\Models\ServiceNotification;

class ServiceNotificationController extends Controller
{
    public function index()
    {
        $userService = auth()->user()->service;

        $notifications = ServiceNotification::query()
            ->when(! auth()->user()->canAccessAllServices(), fn ($q) => $q->where('service', $userService))
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('notifications.index', compact('notifications', 'userService'));
    }

    public function markRead(ServiceNotification $notification)
    {
        if (! auth()->user()->canAccessAllServices() && $notification->service !== auth()->user()->service) {
            abort(403);
        }

        $notification->update(['is_read' => true]);

        return redirect()->route('notifications.index')->with('success', 'Notification marquée comme lue.');
    }
}
