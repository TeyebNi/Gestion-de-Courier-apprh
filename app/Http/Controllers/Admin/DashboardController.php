<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemandeHistorique;
use App\Models\Tabdepot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Répartition des demandes assignées, pour le graphique "Demandes par
     * Service" : service_assigne contient soit le nom d'un service, soit le
     * nom propre d'une personne (Adjoint au Maire/Division/Chef de Service/
     * Conseiller). Les regrouper telles quelles donnerait une barre par
     * personne, noyée parmi les services. On regroupe donc chaque Division
     * et chaque Chef de Service dans le service dont ils dépendent, et
     * l'Adjoint au Maire/le Conseiller dans une catégorie unique par rôle —
     * avec, en complément, un détail personne par personne pour ces deux
     * derniers rôles.
     */
    private function workloadChartData($assignedQuery): array
    {
        // Clé sur "service" (l'identifiant de file réellement stocké dans
        // Tabdepot.service_assigne) et non "name", car pour une Division ce
        // sont deux choses différentes (nom de la division vs. son titulaire).
        $nestedParents = User::whereIn('role_kind', User::serviceNestedRoleKinds())->pluck('division_of', 'service');

        $serviceCountsMap = [];
        $maireAdjointCountsMap = [];
        $conseillerCountsMap = [];
        foreach ($assignedQuery->get(['service_assigne', 'destination_type']) as $d) {
            $label = match ($d->destination_type) {
                'maire_adjoint' => User::MAIRE_ADJOINT_LABEL,
                'conseiller' => User::CONSEILLER_LABEL,
                'division', 'chef_service' => $nestedParents[$d->service_assigne] ?? $d->service_assigne,
                default => $d->service_assigne,
            };
            $serviceCountsMap[$label] = ($serviceCountsMap[$label] ?? 0) + 1;

            if ($d->destination_type === 'maire_adjoint') {
                $maireAdjointCountsMap[$d->service_assigne] = ($maireAdjointCountsMap[$d->service_assigne] ?? 0) + 1;
            } elseif ($d->destination_type === 'conseiller') {
                $conseillerCountsMap[$d->service_assigne] = ($conseillerCountsMap[$d->service_assigne] ?? 0) + 1;
            }
        }
        arsort($serviceCountsMap);
        $serviceCountsMap = array_slice($serviceCountsMap, 0, 8, true);

        arsort($maireAdjointCountsMap);
        arsort($conseillerCountsMap);

        return [
            'serviceLabels' => collect(array_keys($serviceCountsMap)),
            'serviceCounts' => collect(array_values($serviceCountsMap)),
            'maireAdjointLabels' => collect(array_keys($maireAdjointCountsMap)),
            'maireAdjointCounts' => collect(array_values($maireAdjointCountsMap)),
            'conseillerLabels' => collect(array_keys($conseillerCountsMap)),
            'conseillerCounts' => collect(array_values($conseillerCountsMap)),
        ];
    }

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

            // Le Cabinet décide de la destination de chaque demande : lui
            // montrer la répartition globale l'aide à voir où son travail
            // atterrit, comme pour l'admin.
            $workload = $this->workloadChartData(Tabdepot::whereNotNull('service_assigne'));

            return view('admin.dashboard', compact(
                'isAdmin',
                'isPlainUser',
                'isCabinet',
                'pendingCount',
                'recentQueue',
                'queueRoute',
                'queueLabel',
                'totalAnnotees'
            ) + $workload);
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

            // L'accueil n'est rattaché à aucun service : vue globale, comme l'admin.
            $workload = $this->workloadChartData(Tabdepot::whereNotNull('service_assigne'));

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
            ) + $workload);
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

        $workload = $this->workloadChartData(clone $assignedQuery);

        return view('admin.dashboard', compact(
            'isAdmin',
            'isPlainUser',
            'isCabinet',
            'totalDemandes',
            'totalDemandesAssignees',
            'totalEnCours',
            'months',
            'monthCounts',
            'recentDemandes',
            'recentServiceDemandes'
        ) + $workload);
    }
}