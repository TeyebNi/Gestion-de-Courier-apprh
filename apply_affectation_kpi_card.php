<?php
/**
 * Ajoute une carte KPI 'Affectations' sur le dashboard des utilisateurs
 * sans service qui ont l'acces Affectation (can_affectation=true).
 * A executer depuis la racine du projet Laravel :
 *   php apply_affectation_kpi_card.php
 */

$ctrlFile = 'app/Http/Controllers/Admin/DashboardController.php';
$viewFile = 'resources/views/admin/dashboard.blade.php';

$ctrlOld1 = <<<'CTRLOLD1'
        // ----- Utilisateur simple (pas de service) : dashboard minimal -----
        if ($isPlainUser) {
            $totalDemandesToday = Tabdepot::whereDate('daterecp', Carbon::today())->count();
CTRLOLD1;

$ctrlNew1 = <<<'CTRLNEW1'
        // ----- Utilisateur simple (pas de service) : dashboard minimal -----
        if ($isPlainUser) {
            // Cas particulier : pas de service, mais acces Affectation accorde manuellement
            // (case "Acces au module Affectation" cochee sur sa fiche utilisateur).
            $hasAffectationAccess = (bool) $user->can_affectation;
            $totalAffectationsGlobal = $hasAffectationAccess ? Affectation::count() : 0;

            $totalDemandesToday = Tabdepot::whereDate('daterecp', Carbon::today())->count();
CTRLNEW1;

$ctrlOld2 = <<<'CTRLOLD2'
            return view('admin.dashboard', compact(
                'isAdmin',
                'isPlainUser',
                'totalDemandesToday',
                'totalDemandesWeek',
                'totalDemandesAll',
                'recentDemandesUser',
                'months',
                'monthCounts',
                'typeLabels',
                'typeCounts'
            ));
CTRLOLD2;

$ctrlNew2 = <<<'CTRLNEW2'
            return view('admin.dashboard', compact(
                'isAdmin',
                'isPlainUser',
                'hasAffectationAccess',
                'totalAffectationsGlobal',
                'totalDemandesToday',
                'totalDemandesWeek',
                'totalDemandesAll',
                'recentDemandesUser',
                'months',
                'monthCounts',
                'typeLabels',
                'typeCounts'
            ));
CTRLNEW2;

$viewOld = <<<'VIEWOLD'
<style>
    .kpi-card {
        border-radius: 18px;
        padding: 26px 24px;
        display: flex;
        align-items: center;
        gap: 18px;
        background: #fff;
        border: 1px solid #eaeaea;
        box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        transition: transform .2s ease, box-shadow .2s ease;
        height: 100%;
    }
    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    }
    .kpi-icon {
        width: 58px;
        height: 58px;
        min-width: 58px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: #fff;
    }
    .kpi-label {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #9A9A9A;
        margin: 0 0 4px;
        font-weight: 600;
    }
    .kpi-value {
        font-size: 2.3rem;
        font-weight: 700;
        line-height: 1;
        margin: 0 0 6px;
        color: #2c3e50;
    }
    .kpi-sub {
        font-size: .78rem;
        color: #9A9A9A;
        margin: 0;
    }
    .kpi-today .kpi-icon { background: #2CA8FF; }
    .kpi-week  .kpi-icon { background: #FFA534; }
    .kpi-total .kpi-icon { background: #18ce0f; }
</style>

<!-- KPI cards -->
<div class="row">
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="kpi-card kpi-today">
            <div class="kpi-icon"><i class="now-ui-icons ui-1_calendar-60"></i></div>
            <div class="kpi-body">
                <p class="kpi-label">Aujourd'hui</p>
                <h2 class="kpi-value">{{ $totalDemandesToday }}</h2>
                <p class="kpi-sub"><i class="now-ui-icons files_box"></i> Demandes déposées aujourd'hui</p>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="kpi-card kpi-week">
            <div class="kpi-icon"><i class="now-ui-icons files_single-copy-04"></i></div>
            <div class="kpi-body">
                <p class="kpi-label">Cette Semaine</p>
                <h2 class="kpi-value">{{ $totalDemandesWeek }}</h2>
                <p class="kpi-sub"><i class="now-ui-icons ui-1_calendar-60"></i> Du lundi à aujourd'hui</p>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="kpi-card kpi-total">
            <div class="kpi-icon"><i class="now-ui-icons files_paper"></i></div>
            <div class="kpi-body">
                <p class="kpi-label">Total Général</p>
                <h2 class="kpi-value">{{ $totalDemandesAll }}</h2>
                <p class="kpi-sub"><i class="now-ui-icons arrows-1_refresh-69"></i> Depuis le début</p>
            </div>
        </div>
    </div>
</div>
VIEWOLD;

$viewNew = <<<'VIEWNEW'
<style>
    .kpi-card {
        border-radius: 18px;
        padding: 26px 24px;
        display: flex;
        align-items: center;
        gap: 18px;
        background: #fff;
        border: 1px solid #eaeaea;
        box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        transition: transform .2s ease, box-shadow .2s ease;
        height: 100%;
    }
    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    }
    .kpi-icon {
        width: 58px;
        height: 58px;
        min-width: 58px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: #fff;
    }
    .kpi-label {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #9A9A9A;
        margin: 0 0 4px;
        font-weight: 600;
    }
    .kpi-value {
        font-size: 2.3rem;
        font-weight: 700;
        line-height: 1;
        margin: 0 0 6px;
        color: #2c3e50;
    }
    .kpi-sub {
        font-size: .78rem;
        color: #9A9A9A;
        margin: 0;
    }
    .kpi-today .kpi-icon { background: #2CA8FF; }
    .kpi-week  .kpi-icon { background: #FFA534; }
    .kpi-total .kpi-icon { background: #18ce0f; }
    .kpi-affectation .kpi-icon { background: #9C27B0; }
</style>

<!-- KPI cards -->
<div class="row">
    @if($hasAffectationAccess)
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="kpi-card kpi-affectation">
            <div class="kpi-icon"><i class="now-ui-icons shopping_delivery-fast"></i></div>
            <div class="kpi-body">
                <p class="kpi-label">Affectations</p>
                <h2 class="kpi-value">{{ $totalAffectationsGlobal }}</h2>
                <p class="kpi-sub"><i class="now-ui-icons location_compass-05"></i> Tous services confondus</p>
            </div>
        </div>
    </div>
    @endif

    <div class="{{ $hasAffectationAccess ? 'col-lg-3' : 'col-lg-4' }} col-md-6 mb-4">
        <div class="kpi-card kpi-today">
            <div class="kpi-icon"><i class="now-ui-icons ui-1_calendar-60"></i></div>
            <div class="kpi-body">
                <p class="kpi-label">Aujourd'hui</p>
                <h2 class="kpi-value">{{ $totalDemandesToday }}</h2>
                <p class="kpi-sub"><i class="now-ui-icons files_box"></i> Demandes déposées aujourd'hui</p>
            </div>
        </div>
    </div>

    <div class="{{ $hasAffectationAccess ? 'col-lg-3' : 'col-lg-4' }} col-md-6 mb-4">
        <div class="kpi-card kpi-week">
            <div class="kpi-icon"><i class="now-ui-icons files_single-copy-04"></i></div>
            <div class="kpi-body">
                <p class="kpi-label">Cette Semaine</p>
                <h2 class="kpi-value">{{ $totalDemandesWeek }}</h2>
                <p class="kpi-sub"><i class="now-ui-icons ui-1_calendar-60"></i> Du lundi à aujourd'hui</p>
            </div>
        </div>
    </div>

    <div class="{{ $hasAffectationAccess ? 'col-lg-3' : 'col-lg-4' }} col-md-6 mb-4">
        <div class="kpi-card kpi-total">
            <div class="kpi-icon"><i class="now-ui-icons files_paper"></i></div>
            <div class="kpi-body">
                <p class="kpi-label">Total Général</p>
                <h2 class="kpi-value">{{ $totalDemandesAll }}</h2>
                <p class="kpi-sub"><i class="now-ui-icons arrows-1_refresh-69"></i> Depuis le début</p>
            </div>
        </div>
    </div>
</div>
VIEWNEW;

$ok = 0; $fail = 0;

if (!file_exists($ctrlFile)) {
    echo "[SKIP] Fichier introuvable : $ctrlFile\n";
    $fail += 2;
} else {
    $c = file_get_contents($ctrlFile);
    if (strpos($c, $ctrlOld1) === false) {
        echo "[ATTENTION] Extrait 1 non trouve (deja modifie ?) dans $ctrlFile\n";
        $fail++;
    } else {
        $c = str_replace($ctrlOld1, $ctrlNew1, $c);
        echo "[OK] $ctrlFile (detection hasAffectationAccess ajoutee)\n";
        $ok++;
    }
    if (strpos($c, $ctrlOld2) === false) {
        echo "[ATTENTION] Extrait 2 non trouve (deja modifie ?) dans $ctrlFile\n";
        $fail++;
    } else {
        $c = str_replace($ctrlOld2, $ctrlNew2, $c);
        echo "[OK] $ctrlFile (variables passees a la vue)\n";
        $ok++;
    }
    file_put_contents($ctrlFile, $c);
}

if (!file_exists($viewFile)) {
    echo "[SKIP] Fichier introuvable : $viewFile\n";
    $fail++;
} else {
    $v = file_get_contents($viewFile);
    if (strpos($v, $viewOld) === false) {
        echo "[ATTENTION] Bloc KPI non trouve (deja modifie ?) dans $viewFile\n";
        $fail++;
    } else {
        $v = str_replace($viewOld, $viewNew, $v);
        file_put_contents($viewFile, $v);
        echo "[OK] $viewFile (carte Affectations ajoutee)\n";
        $ok++;
    }
}

echo "\nTermine : $ok extraits appliques, $fail non appliques.\n";
