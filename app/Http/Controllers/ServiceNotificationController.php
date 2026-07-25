<?php

namespace App\Http\Controllers;

use App\Models\ServiceNotification;
use App\Models\Tabdepot;
use App\Services\SmsService;
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

    public function respond(Request $request, ServiceNotification $notification, SmsService $sms)
    {
        if ($notification->service !== auth()->user()->service) {
            abort(403);
        }

        $request->validate([
            'response' => ['required', 'in:accepted,rejected'],
        ]);

        $demande = Tabdepot::find($notification->iddmd);

        if (! $demande || ! $demande->tel) {
            return redirect()->route('notifications.index')->with('error', "Impossible d'envoyer le SMS : numéro de téléphone introuvable pour cette demande.");
        }

        $statusLabel = $request->response === 'accepted' ? 'ACCEPTÉE' : 'REFUSÉE';

        $message = "Bonjour {$demande->nom}, votre demande ({$demande->typdm}, code {$demande->id}) auprès du service {$notification->service} a été {$statusLabel}. Commune de Tevragh Zeina.";

        $sent = $sms->send($demande->tel, $message);

        $notification->update([
            'response' => $request->response,
            'responded_at' => now(),
        ]);

        if ($sent) {
            return redirect()->route('notifications.index')->with('success', "Réponse envoyée par SMS au demandeur ({$demande->nom}).");
        }

        return redirect()->route('notifications.index')->with('error', "La réponse a été enregistrée, mais l'envoi du SMS a échoué (vérifiez la config Twilio ou que le numéro est vérifié en compte d'essai).");
    }
}
