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

        // ----- KPI cards -----
        $totalDemandes = Tabdepot::count();

        $affectationQuery = Affectation::query();
        if (!$isAdmin) {
            $affectationQuery->where('sevice', $user->service);
        }
        $totalAffectations = (clone $affectationQuery)->count();

        $totalTypes = Typedem::count();

        $notifQuery = ServiceNotification::query();
        if (!$isAdmin) {
            $notifQuery->where('service', $user->service);
        }
        $totalAcceptees = (clone $notifQuery)->where('response', 'accepted')->count();
        $totalRefusees = (clone $notifQuery)->where('response', 'rejected')->count();
        $totalEnAttente = (clone $notifQuery)->whereNull('response')->count();
        $totalReponses = $totalAcceptees + $totalRefusees;
        $tauxAcceptation = $totalReponses > 0 ? round(($totalAcceptees / $totalReponses) * 100) : 0;

        // ----- Évolution des demandes (6 derniers mois) -----
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

        // ----- Répartition par type de demande -----
        $typeStats = Tabdepot::select('typdm')
            ->selectRaw('count(*) as total')
            ->whereNotNull('typdm')
            ->groupBy('typdm')
            ->orderByDesc('total')
            ->limit(8)
            ->get();
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

        // ----- Dernières demandes déposées -----
        $recentDemandes = Tabdepot::orderByDesc('id')->limit(5)->get();

        return view('admin.dashboard', compact(
            'isAdmin',
            'totalDemandes',
            'totalAffectations',
            'totalTypes',
            'tauxAcceptation',
            'totalAcceptees',
            'totalRefusees',
            'totalEnAttente',
            'months',
            'monthCounts',
            'typeLabels',
            'typeCounts',
            'serviceLabels',
            'serviceCounts',
            'recentDemandes'
        ));
    }
}