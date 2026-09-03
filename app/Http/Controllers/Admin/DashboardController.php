<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            $totalAcceptees = null;
            $totalRefusees = null;

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

                // Aucune page n'affiche ce total ailleurs : même "Historique de mes
                // décisions" ne montre qu'une liste, jamais un compte global.
                $totalAcceptees = Tabdepot::where('decision_maire', 'accepte')->count();
                $totalRefusees = Tabdepot::where('decision_maire', 'refuse')->count();
            }

            return view('admin.dashboard', compact(
                'isAdmin',
                'isPlainUser',
                'isCabinet',
                'isMaireUser',
                'pendingCount',
                'recentQueue',
                'queueRoute',
                'queueLabel',
                'totalAcceptees',
                'totalRefusees'
            ));
        }

        // ----- Utilisateur simple (pas de service) : dashboard minimal -----
        if ($isPlainUser) {
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
        // "Demandes assignées" = demandes du circuit envoyées à un service
        // (Tabdepot.service_assigne, renseigné par le Maire via decide()).
        $assignedQuery = Tabdepot::whereNotNull('service_assigne');
        if (!$isAdmin) {
            $assignedQuery->where('service_assigne', $user->service);
        }
        $totalDemandesAssignees = (clone $assignedQuery)->count();

        if ($isAdmin) {
            $totalDemandes = Tabdepot::count();
            $totalTypes = Typedem::count();
        } else {
            $totalDemandes = $totalDemandesAssignees;
            $totalTypes = (clone $assignedQuery)
                ->whereNotNull('typdm')
                ->distinct('typdm')
                ->count('typdm');
        }

        // "Acceptées/Refusées" reflète désormais la décision du Maire elle-même
        // (Tabdepot.decision_maire), la seule source de vérité depuis que le
        // circuit envoie le SMS directement au citoyen dans decide().
        $decisionQuery = Tabdepot::whereNotNull('decision_maire');
        if (!$isAdmin) {
            $decisionQuery->where('service_assigne', $user->service);
        }
        $totalAcceptees = (clone $decisionQuery)->where('decision_maire', 'accepte')->count();
        $totalRefusees = (clone $decisionQuery)->where('decision_maire', 'refuse')->count();
        $totalReponses = $totalAcceptees + $totalRefusees;
        $tauxAcceptation = $totalReponses > 0 ? round(($totalAcceptees / $totalReponses) * 100) : 0;

        // Demandes assignées à ce service mais pas encore clôturées par lui.
        $totalEnCours = (clone $assignedQuery)->where('statut_circuit', 'service')->count();

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
            $recentServiceDemandes = collect();
        } else {
            // ----- Évolution des demandes reçues par ce service (6 derniers mois) -----
            $months = [];
            $monthCounts = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $months[] = $moisFr[$date->month - 1] . ' ' . $date->format('Y');
                $monthCounts[] = Tabdepot::where('service_assigne', $user->service)
                    ->whereYear('daterecp', $date->year)
                    ->whereMonth('daterecp', $date->month)
                    ->count();
            }

            // ----- Répartition par type des demandes assignées à ce service -----
            $typeStats = (clone $assignedQuery)
                ->select('typdm')
                ->selectRaw('count(*) as total')
                ->whereNotNull('typdm')
                ->groupBy('typdm')
                ->orderByDesc('total')
                ->limit(8)
                ->get();

            $recentDemandes = collect();
            $recentServiceDemandes = (clone $assignedQuery)
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get();
        }

        $typeLabels = $typeStats->pluck('typdm');
        $typeCounts = $typeStats->pluck('total');

        // ----- Répartition des demandes assignées par service -----
        $serviceStats = (clone $assignedQuery)
            ->select('service_assigne')
            ->selectRaw('count(*) as total')
            ->groupBy('service_assigne')
            ->orderByDesc('total')
            ->limit(8)
            ->get();
        $serviceLabels = $serviceStats->pluck('service_assigne');
        $serviceCounts = $serviceStats->pluck('total');

        return view('admin.dashboard', compact(
            'isAdmin',
            'isPlainUser',
            'isCabinet',
            'isMaireUser',
            'totalDemandes',
            'totalDemandesAssignees',
            'totalTypes',
            'tauxAcceptation',
            'totalAcceptees',
            'totalRefusees',
            'totalEnCours',
            'months',
            'monthCounts',
            'typeLabels',
            'typeCounts',
            'serviceLabels',
            'serviceCounts',
            'recentDemandes',
            'recentServiceDemandes'
        ));
    }
}