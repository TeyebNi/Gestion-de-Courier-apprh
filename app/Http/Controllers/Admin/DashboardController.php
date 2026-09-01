<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\ServiceNotification;
use App\Models\Tabdepot;
use App\Models\Typedem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $isCabinet = !$isAdmin && $user->isFatou();
        $isMaireUser = !$isAdmin && $user->isMaire();
        // Un admin restreint (ex: Accueil, qui n'a pas Cabinet/Maire/Utilisateurs/
        // toutes les services) reçoit le dashboard minimal plutôt que la vue admin
        // complète, cohérent avec ce qu'il peut réellement voir ailleurs.
        $isPlainUser = (!$isAdmin && !$isCabinet && !$isMaireUser && empty($user->service))
            || ($isAdmin && ! $user->isUnrestrictedAdmin());

        // ----- Cabinet / Maire : dashboard minimal centré sur leur file d'attente -----
        if ($isCabinet || $isMaireUser) {
            if ($isCabinet) {
                $pendingCount = Tabdepot::where('statut_circuit', 'fatou')->whereNull('decision_maire')->count();
                $recentQueue = Tabdepot::where('statut_circuit', 'fatou')
                    ->whereNull('decision_maire')
                    ->orderByDesc('id')
                    ->limit(8)
                    ->get();
                $queueRoute = route('circuit.fatou.index');
                $queueLabel = 'En attente au Cabinet';
            } else {
                $pendingCount = Tabdepot::where('statut_circuit', 'maire')->count();
                $recentQueue = Tabdepot::where('statut_circuit', 'maire')
                    ->orderByDesc('id')
                    ->limit(8)
                    ->get();
                $queueRoute = route('circuit.maire.index');
                $queueLabel = 'En attente de décision';
            }

            return view('admin.dashboard', compact(
                'isAdmin',
                'isPlainUser',
                'isCabinet',
                'isMaireUser',
                'pendingCount',
                'recentQueue',
                'queueRoute',
                'queueLabel'
            ));
        }

        // ----- Utilisateur simple (pas de service) : dashboard minimal -----
        if ($isPlainUser) {
            // Cas particulier : pas de service, mais acces Affectation accorde manuellement
            // (case "Acces au module Affectation" cochee sur sa fiche utilisateur).
            $hasAffectationAccess = (bool) $user->can_affectation;
            $totalAffectationsGlobal = $hasAffectationAccess ? Affectation::count() : 0;

            $totalDemandesToday = Tabdepot::whereDate('daterecp', Carbon::today())->count();
            $totalDemandesWeek = Tabdepot::whereBetween('daterecp', [
                Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek(),
            ])->count();
            $totalDemandesAll = Tabdepot::count();
            $totalEnAttenteEnvoi = Tabdepot::where('statut_circuit', 'accueil')->count();
            $recentDemandesUser = Tabdepot::orderByDesc('id')->limit(8)->get();

            $moisFr = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
            $months = [];
            $monthCounts = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $months[] = $moisFr[$date->month - 1] . ' ' . $date->format('Y');
                $monthCounts[] = Tabdepot::whereYear('daterecp', $date->year)
                    ->whereMonth('daterecp', $date->month)
                    ->count();
            }

            $typeStats = Tabdepot::select('typdm')
                ->selectRaw('count(*) as total')
                ->whereNotNull('typdm')
                ->groupBy('typdm')
                ->orderByDesc('total')
                ->limit(8)
                ->get();
            $typeLabels = $typeStats->pluck('typdm');
            $typeCounts = $typeStats->pluck('total');

            return view('admin.dashboard', compact(
                'isAdmin',
                'isPlainUser',
                'isCabinet',
                'isMaireUser',
                'hasAffectationAccess',
                'totalAffectationsGlobal',
                'totalDemandesToday',
                'totalDemandesWeek',
                'totalDemandesAll',
                'totalEnAttenteEnvoi',
                'recentDemandesUser',
                'months',
                'monthCounts',
                'typeLabels',
                'typeCounts'
            ));
        }

        // ----- KPI cards -----
        $affectationQuery = Affectation::query();
        if (!$isAdmin) {
            $affectationQuery->where('sevice', $user->service);
        }
        $totalAffectations = (clone $affectationQuery)->count();

        if ($isAdmin) {
            $totalDemandes = Tabdepot::count();
            $totalTypes = Typedem::count();
        } else {
            $scopedDemandeIds = (clone $affectationQuery)->pluck('iddmd')->unique();
            $totalDemandes = $scopedDemandeIds->count();
            $totalTypes = Tabdepot::whereIn('id', $scopedDemandeIds)
                ->whereNotNull('typdm')
                ->distinct('typdm')
                ->count('typdm');
        }

        $notifQuery = ServiceNotification::query();
        if (!$isAdmin) {
            $notifQuery->where('service', $user->service);
        }
        $totalAcceptees = (clone $notifQuery)->where('response', 'accepted')->count();
        $totalRefusees = (clone $notifQuery)->where('response', 'rejected')->count();
        $totalEnAttente = (clone $notifQuery)->whereNull('response')->count();
        $totalReponses = $totalAcceptees + $totalRefusees;
        $tauxAcceptation = $totalReponses > 0 ? round(($totalAcceptees / $totalReponses) * 100) : 0;

        $totalUnread = (clone $notifQuery)->where('is_read', false)->count();

        $moisFr = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

        if ($isAdmin) {
            // ----- Évolution des demandes déposées (global, 6 derniers mois) -----
            $months = [];
            $monthCounts = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $months[] = $moisFr[$date->month - 1] . ' ' . $date->format('Y');
                $monthCounts[] = Tabdepot::whereYear('daterecp', $date->year)
                    ->whereMonth('daterecp', $date->month)
                    ->count();
            }

            // ----- Répartition par type de demande (global) -----
            $typeStats = Tabdepot::select('typdm')
                ->selectRaw('count(*) as total')
                ->whereNotNull('typdm')
                ->groupBy('typdm')
                ->orderByDesc('total')
                ->limit(8)
                ->get();

            $recentDemandes = Tabdepot::orderByDesc('id')->limit(5)->get();
            $recentNotifications = collect();
        } else {
            // ----- Évolution des affectations reçues par ce service (6 derniers mois) -----
            $months = [];
            $monthCounts = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $months[] = $moisFr[$date->month - 1] . ' ' . $date->format('Y');
                $monthCounts[] = Affectation::where('sevice', $user->service)
                    ->whereYear('dateaff', $date->year)
                    ->whereMonth('dateaff', $date->month)
                    ->count();
            }

            // ----- Répartition par type des demandes affectées à ce service -----
            $iddmdIds = Affectation::where('sevice', $user->service)->pluck('iddmd');
            $typeStats = Tabdepot::select('typdm')
                ->selectRaw('count(*) as total')
                ->whereIn('id', $iddmdIds)
                ->whereNotNull('typdm')
                ->groupBy('typdm')
                ->orderByDesc('total')
                ->limit(8)
                ->get();

            $recentDemandes = collect();
            $recentNotifications = ServiceNotification::where('service', $user->service)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }

        $typeLabels = $typeStats->pluck('typdm');
        $typeCounts = $typeStats->pluck('total');

        // ----- Répartition des affectations par service -----
        $serviceStats = (clone $affectationQuery)
            ->select('sevice')
            ->selectRaw('count(*) as total')
            ->groupBy('sevice')
            ->orderByDesc('total')
            ->limit(8)
            ->get();
        $serviceLabels = $serviceStats->pluck('sevice');
        $serviceCounts = $serviceStats->pluck('total');

        return view('admin.dashboard', compact(
            'isAdmin',
            'isPlainUser',
            'isCabinet',
            'isMaireUser',
            'totalDemandes',
            'totalAffectations',
            'totalTypes',
            'tauxAcceptation',
            'totalAcceptees',
            'totalRefusees',
            'totalEnAttente',
            'totalUnread',
            'months',
            'monthCounts',
            'typeLabels',
            'typeCounts',
            'serviceLabels',
            'serviceCounts',
            'recentDemandes',
            'recentNotifications'
        ));
    }
}