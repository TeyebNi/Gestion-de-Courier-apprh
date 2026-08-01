<?php
/**
 * Retire le graphique 'Type de Demande' du dashboard des utilisateurs
 * sans service (depot uniquement) - hors de leur perimetre d'acces.
 * A executer depuis la racine du projet Laravel :
 *   php apply_remove_typedem_chart.php
 */

$file = 'resources/views/admin/dashboard.blade.php';

$oldEvo = <<<'OLDEVO'
<!-- Évolution + Type de demande -->
<div class="row">
    <div class="col-lg-8">
        <div class="card card-chart">
            <div class="card-header">
                <h5 class="card-category">Suivi dans le temps</h5>
                <h4 class="card-title">Évolution des Demandes (6 derniers mois)</h4>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="evolutionChart"></canvas>
                </div>
            </div>
            <div class="card-footer">
                <div class="stats">
                    <i class="now-ui-icons arrows-1_refresh-69"></i> Basé sur la date de réception des demandes
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-chart">
            <div class="card-header">
                <h5 class="card-category">Répartition</h5>
                <h4 class="card-title">Type de Demande</h4>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="typeChart"></canvas>
                </div>
            </div>
            <div class="card-footer">
                <div class="stats">
                    <i class="now-ui-icons design_bullet-list-67"></i> {{ $typeLabels->count() }} types au total
                </div>
            </div>
        </div>
    </div>
</div>
OLDEVO;

$newEvo = <<<'NEWEVO'
<!-- Évolution -->
<div class="row">
    <div class="col-md-12">
        <div class="card card-chart">
            <div class="card-header">
                <h5 class="card-category">Suivi dans le temps</h5>
                <h4 class="card-title">Évolution des Demandes (6 derniers mois)</h4>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="evolutionChart"></canvas>
                </div>
            </div>
            <div class="card-footer">
                <div class="stats">
                    <i class="now-ui-icons arrows-1_refresh-69"></i> Basé sur la date de réception des demandes
                </div>
            </div>
        </div>
    </div>
</div>
NEWEVO;

$oldJs = <<<'OLDJS'
    new Chart(document.getElementById('typeChart'), {
        type: 'doughnut',
        data: {
            labels: typeLabels,
            datasets: [{
                data: typeCounts,
                backgroundColor: palette,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
        }
    });

    @if(!$isPlainUser)
OLDJS;

$newJs = <<<'NEWJS'
    var typeChartEl = document.getElementById('typeChart');
    if (typeChartEl) {
        new Chart(typeChartEl, {
            type: 'doughnut',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeCounts,
                    backgroundColor: palette,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
            }
        });
    }

    @if(!$isPlainUser)
NEWJS;

if (!file_exists($file)) {
    echo "[SKIP] Fichier introuvable : $file\n";
    exit(1);
}

$content = file_get_contents($file);
$ok = 0; $fail = 0;

if (strpos($content, $oldEvo) === false) {
    echo "[ATTENTION] Bloc Evolution/Type non trouve (deja modifie ?)\n";
    $fail++;
} else {
    $content = str_replace($oldEvo, $newEvo, $content);
    echo "[OK] Bloc Evolution/Type remplace (graphique retire, pleine largeur)\n";
    $ok++;
}

if (strpos($content, $oldJs) === false) {
    echo "[ATTENTION] Code JS typeChart non trouve (deja modifie ?)\n";
    $fail++;
} else {
    $content = str_replace($oldJs, $newJs, $content);
    echo "[OK] Code JS typeChart securise\n";
    $ok++;
}

file_put_contents($file, $content);
echo "\nTermine : $ok extraits appliques, $fail non appliques.\n";
