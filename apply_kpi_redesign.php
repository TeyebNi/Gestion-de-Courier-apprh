<?php
/**
 * Redesign des 3 cartes KPI (Aujourd'hui, Cette Semaine, Total General)
 * sur le dashboard des utilisateurs sans service (depot uniquement).
 * A executer depuis la racine du projet Laravel :
 *   php apply_kpi_redesign.php
 */

$file = 'resources/views/admin/dashboard.blade.php';

$old = <<<'OLDBLOCK'
<!-- KPI cards -->
<div class="row">
    <div class="col-lg-4 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="now-ui-icons ui-1_calendar-60 text-info"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <p class="card-category">Aujourd'hui</p>
                            <h4 class="card-title">{{ $totalDemandesToday }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <hr>
                <div class="stats">
                    <i class="now-ui-icons files_box"></i> Demandes déposées aujourd'hui
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="now-ui-icons files_single-copy-04 text-primary"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <p class="card-category">Cette semaine</p>
                            <h4 class="card-title">{{ $totalDemandesWeek }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <hr>
                <div class="stats">
                    <i class="now-ui-icons ui-1_calendar-60"></i> Du lundi à aujourd'hui
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="now-ui-icons files_paper text-success"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <p class="card-category">Total Général</p>
                            <h4 class="card-title">{{ $totalDemandesAll }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <hr>
                <div class="stats">
                    <i class="now-ui-icons arrows-1_refresh-69"></i> Depuis le début
                </div>
            </div>
        </div>
    </div>
</div>
OLDBLOCK;

$new = <<<'NEWBLOCK'
<style>
    .kpi-card {
        border-radius: 18px;
        padding: 26px 24px;
        display: flex;
        align-items: center;
        gap: 18px;
        color: #fff;
        box-shadow: 0 10px 25px rgba(0,0,0,0.14);
        transition: transform .2s ease, box-shadow .2s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 16px 32px rgba(0,0,0,0.2);
    }
    .kpi-card::after {
        content: "";
        position: absolute;
        right: -30px;
        bottom: -30px;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,0.08);
    }
    .kpi-icon {
        width: 58px;
        height: 58px;
        min-width: 58px;
        border-radius: 50%;
        background: rgba(255,255,255,0.22);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        z-index: 1;
    }
    .kpi-body { z-index: 1; }
    .kpi-label {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .6px;
        opacity: .9;
        margin: 0 0 4px;
        font-weight: 600;
    }
    .kpi-value {
        font-size: 2.3rem;
        font-weight: 700;
        line-height: 1;
        margin: 0 0 6px;
    }
    .kpi-sub {
        font-size: .78rem;
        opacity: .85;
        margin: 0;
    }
    .kpi-today { background: linear-gradient(135deg, #2CA8FF 0%, #1B6FBF 100%); }
    .kpi-week  { background: linear-gradient(135deg, #FFA534 0%, #E67E00 100%); }
    .kpi-total { background: linear-gradient(135deg, #18ce0f 0%, #0C9E44 100%); }
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
NEWBLOCK;

if (!file_exists($file)) {
    echo "[SKIP] Fichier introuvable : $file\n";
    exit(1);
}

$content = file_get_contents($file);

if (strpos($content, $old) === false) {
    echo "[ATTENTION] Bloc KPI non trouve (fichier deja modifie ?) dans $file\n";
    exit(1);
}

file_put_contents($file, str_replace($old, $new, $content));
echo "[OK] $file (cartes KPI redessinees)\n";
