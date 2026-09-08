<?php

namespace App\Http\Controllers;

use App\Models\ServiceNotification;
use App\Models\Tabdepot;

class ServiceNotificationController extends Controller
{
    public function index()
    {
        $userService = auth()->user()->service;

        $notifications = ServiceNotification::query()
            ->when(! auth()->user()->canAccessAllServices(), fn ($q) => $q->where('service', $userService))
            ->orderByDesc('created_at')
            ->paginate(5);

        // D'anciennes notifications (avant le circuit actuel) ont un iddmd
        // invalide (nom de type, vide, ou demande depuis supprimée) : ne
        // proposer le lien que vers une demande qui existe réellement.
        $validDemandeIds = Tabdepot::whereIn('id', $notifications->pluck('iddmd')->filter(fn ($id) => is_numeric($id)))
            ->pluck('id')
            ->all();

        return view('notifications.index', compact('notifications', 'userService', 'validDemandeIds'));
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
