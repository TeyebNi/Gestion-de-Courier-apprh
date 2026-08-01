<?php
namespace App\Http\Controllers;
use App\Models\ServiceNotification;
use App\Models\Tabdepot;
use Illuminate\Http\Request;
class ServiceNotificationController extends Controller
{
    public function index(Request $request)
    {
        $userService = auth()->user()->service;
        $notifications = ServiceNotification::where('service', $userService)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('notifications.index', compact('notifications', 'userService'));
    }
    public function markRead(ServiceNotification $notification)
    {
        if ($notification->service !== auth()->user()->service) {
            abort(403);
        }
        $notification->update(['is_read' => true]);
        return redirect()->route('notifications.index')->with('success', 'Notification marquée comme lue.');
    }
    public function respond(Request $request, ServiceNotification $notification)
    {
        if ($notification->service !== auth()->user()->service) {
            abort(403);
        }
        $request->validate([
            'response' => ['required', 'in:accepted,rejected'],
        ]);
        $demande = Tabdepot::find($notification->iddmd);
        $statusLabel = $request->response === 'accepted' ? 'ACCEPTÉE' : 'REFUSÉE';
        $notification->update([
            'response' => $request->response,
            'responded_at' => now(),
        ]);
        $nom = $demande->nom ?? 'le demandeur';
        return redirect()->route('notifications.index')->with('success', "Réponse enregistrée : demande {$statusLabel} pour {$nom}.");
    }
}
