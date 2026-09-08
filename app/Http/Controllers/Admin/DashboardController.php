<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemandeHistorique;
use App\Models\Tabdepot;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $isCabinet = !$isAdmin && $user->isFatou();
        // Un admin restreint (ex: Accueil, qui n'a ni Cabinet, ni l'accès à toutes
        // les services) reçoit le dashboard minimal plutôt que la vue admin
        // complète, cohérent avec ce qu'il peut réellement voir ailleurs. Manquer
        // uniquement "can_manage_users" ne doit pas dégrader le dashboard : ça ne
        // change rien à ce qu'il peut voir en matière de demandes/circuit.
        $isPlainUser = (!$isAdmin && !$isCabinet && empty($user->service))
            || ($isAdmin && ! $user->canAccessCabinet() && ! $user->canAccessAllServices());

        // ----- Cabinet de Maire : dashboard minimal centré sur sa file d'attente -----
        if ($isCabinet) {
            $pendingCount = Tabdepot::where('statut_circuit', 'fatou')->count();
            $recentQueue = Tabdepot::where('statut_circuit', 'fatou')
                ->orderByDesc('id')
                ->limit(8)
                ->get();
            $queueRoute = route('circuit.fatou.index');
            $queueLabel = "En attente d'annotations";

            // Chaque annotation saisie (avec ou sans service concerné) est
            // journalisée depuis le statut "fatou" : un compte simple du travail
            // accompli, sans rien exposer de plus que ce qui concerne le Cabinet.
            $totalAnnotees = DemandeHistorique::where('de_statut', 'fatou')
                ->whereIn('vers_statut', ['service', 'cloture'])
                ->count();

            return view('admin.dashboard', compact(
                'isAdmin',
                'isPlainUser',
                'isCabinet',
                'pendingCount',
                'recentQueue',
                'queueRoute',
                'queueLabel',
                'totalAnnotees'
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
            $totalCorbeille = Tabdepot::onlyTrashed()->count();
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

            return view('admin.dashboard', compact(
                'isAdmin',
                'isPlainUser',
                'isCabinet',
                'totalDemandesToday',
                'totalDemandesWeek',
                'totalDemandesAll',
                'totalEnAttenteEnvoi',
                'totalCorbeille',
                'recentDemandesUser',
                'months',
                'monthCounts'
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

        $totalDemandes = $isAdmin ? Tabdepot::count() : $totalDemandesAssignees;

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

            $recentDemandes = collect();
            $recentServiceDemandes = (clone $assignedQuery)
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get();
        }

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
            'totalDemandes',
            'totalDemandesAssignees',
            'totalEnCours',
            'months',
            'monthCounts',
            'serviceLabels',
            'serviceCounts',
            'recentDemandes',
            'recentServiceDemandes'
        ));
    }
}