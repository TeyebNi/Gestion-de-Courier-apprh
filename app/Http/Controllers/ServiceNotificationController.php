<?php

namespace App\Http\Controllers;

use App\Models\ServiceNotification;
use Illuminate\Http\Request;

class ServiceNotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceNotification::orderBy('created_at', 'desc');

        if ($request->filled('service')) {
            $query->where('service', $request->service);
        }

        $notifications = $query->paginate(10);
        $services = ServiceNotification::select('service')->distinct()->pluck('service');

        return view('notifications.index', compact('notifications', 'services'));
    }

    public function markRead(ServiceNotification $notification)
    {
        $notification->update(['is_read' => true]);
        return redirect()->route('notifications.index')->with('success', 'Notification marquée comme lue.');
    }
}
