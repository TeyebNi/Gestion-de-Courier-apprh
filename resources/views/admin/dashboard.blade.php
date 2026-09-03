@extends('layouts.master')

@section('title')
Tableau de bord
@endsection

@section('content')

<style>
    /* ---- Bannière d'accueil ---- */
    .dash-hero {
        background: linear-gradient(120deg, #0c2646 0%, #16233d 55%, #1d3457 100%);
        border-radius: 12px;
        padding: 28px 32px;
        margin-bottom: 24px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        box-shadow: 0 8px 24px rgba(12, 38, 70, 0.18);
    }
    .dash-hero h3 {
        font-weight: 700;
        margin: 0 0 4px;
        font-size: 1.5rem;
    }
    .dash-hero p {
        margin: 0;
        color: rgba(255, 255, 255, 0.75);
        font-size: 0.9rem;
    }
    .dash-hero .dash-hero-date {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 30px;
        padding: 8px 18px;
        font-size: 0.85rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .dash-hero .dash-hero-date i { color: #e8862c; margin-right: 6px; }

    /* ---- Cartes statistiques : icônes en pastille + accent couleur ---- */
    .dashboard-page .card-stats,
    .dashboard-page .card-chart,
    .dashboard-page .card-tasks {
        border: none;
        border-top: 3px solid #e2e6ea;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .dashboard-page .card-stats:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
    }
    .dashboard-page .card-stats .icon-big {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(44, 168, 255, 0.12);
    }
    .dashboard-page .card-stats .icon-big i { font-size: 22px; }
    .dashboard-page .card-stats:has(.text-info) { border-top-color: #2CA8FF; }
    .dashboard-page .card-stats:has(.text-info) .icon-big { background: rgba(44, 168, 255, 0.12); }
    .dashboard-page .card-stats:has(.text-primary) { border-top-color: #e8862c; }
    .dashboard-page .card-stats:has(.text-primary) .icon-big { background: rgba(232, 134, 44, 0.14); }
    .dashboard-page .card-stats:has(.text-success) { border-top-color: #18ce0f; }
    .dashboard-page .card-stats:has(.text-success) .icon-big { background: rgba(24, 206, 15, 0.12); }
    .dashboard-page .card-stats:has(.text-warning) { border-top-color: #FFA534; }
    .dashboard-page .card-stats:has(.text-warning) .icon-big { background: rgba(255, 165, 52, 0.14); }
    .dashboard-page .card-stats:has(.text-danger) { border-top-color: #FB404B; }
    .dashboard-page .card-stats:has(.text-danger) .icon-big { background: rgba(251, 64, 75, 0.12); }

    .dashboard-page .card-chart,
    .dashboard-page .card-tasks { border-top-color: #1d3457; }
    .dashboard-page .card-header .card-title { font-weight: 700; color: #2c3e50; }
    .dashboard-page .table thead th { color: #1d3457 !important; }
</style>

<div class="dashboard-page">

<div class="dash-hero">
    <div>
        <h3>Bonjour, {{ auth()->user()->name }} 👋</h3>
        <p>Bienvenue sur le tableau de bord — Gestion de Courrier</p>
    </div>
    <div class="dash-hero-date">
        <i class="now-ui-icons ui-1_calendar-60"></i>{{ now()->format('d/m/Y') }} · {{ now()->format('H:i') }}
    </div>
</div>

@if($isPlainUser)

<!-- KPI cards -->
<div class="row">
    <div class="col-lg-3 col-md-6">
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

    <div class="col-lg-3 col-md-6">
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

    <div class="col-lg-3 col-md-6">
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

    <div class="col-lg-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="now-ui-icons ui-1_send text-danger"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <p class="card-category">En attente d'envoi</p>
                            <h4 class="card-title">{{ $totalEnAttenteEnvoi }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <hr>
                <div class="stats">
                    <a href="{{ route('depot.index') }}">
                        <i class="now-ui-icons arrows-1_share-66"></i> Pas encore envoyées au Cabinet
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dernières demandes -->
<div class="row">
    <div class="col-md-12">
        <div class="card card-tasks">
            <div class="card-header">
                <h5 class="card-category">Guichet</h5>
                <h4 class="card-title">Dernières Demandes Déposées</h4>
            </div>
            <div class="card-body">
                <div class="table-full-width table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>Nom</th>
                            <th>Objet</th>
                            <th>Téléphone</th>
                            <th>Type</th>
                            <th>Date</th>
                        </thead>
                        <tbody>
                            @forelse($recentDemandesUser as $d)
                            <tr>
                                <td>
                                    {{ $d->nom ?: ($d->origine_detail ?: '—') }}
                                    @if($d->nni)
                                        <br><small class="text-muted">NNI: {{ $d->nni }}</small>
                                    @endif
                                </td>
                                <td class="text-truncate" style="max-width:220px;" title="{{ $d->objet }}">{{ $d->objet ?: '—' }}</td>
                                <td>{{ $d->tel ?: '—' }}</td>
                                <td>{{ $d->typdm ?: '—' }}</td>
                                <td>{{ $d->daterecpFormatted() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aucune demande pour le moment.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <hr>
                <div class="stats">
                    <a href="{{ route('depot.index') }}">
                        <i class="now-ui-icons files_box"></i> Voir toutes les demandes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@elseif($isCabinet || $isMaireUser)

<!-- Dashboard minimal Cabinet / Maire -->
<div class="row justify-content-center">
    <div class="col-lg-5 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="now-ui-icons ui-1_bell-53 text-danger"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <p class="card-category">{{ $queueLabel }}</p>
                            <h4 class="card-title">{{ $pendingCount }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <hr>
                <div class="stats">
                    <a href="{{ $queueRoute }}">
                        <i class="now-ui-icons {{ $isCabinet ? 'arrows-1_share-66' : 'ui-1_check' }}"></i>
                        {{ $isCabinet ? 'Aller au Cabinet' : 'Aller aux décisions' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    @if($isMaireUser)
    <div class="col-lg-5 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="now-ui-icons ui-1_check text-success"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <p class="card-category">Mes décisions</p>
                            <h4 class="card-title">{{ $totalAcceptees + $totalRefusees }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <hr>
                <div class="stats">
                    <i class="now-ui-icons ui-1_check text-success"></i> {{ $totalAcceptees }} acceptées
                    &nbsp;·&nbsp;
                    <i class="now-ui-icons ui-1_simple-remove text-danger"></i> {{ $totalRefusees }} refusées
                </div>
            </div>
        </div>
    </div>
    @endif
    @if($isCabinet)
    <div class="col-lg-5 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="now-ui-icons arrows-1_share-66 text-success"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <p class="card-category">Envoyées au Maire</p>
                            <h4 class="card-title">{{ $totalEnvoyeesMaire }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <hr>
                <div class="stats">
                    <i class="now-ui-icons ui-1_send"></i> Total des transmissions
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-tasks">
            <div class="card-header">
                <h5 class="card-category">{{ $isCabinet ? 'Cabinet' : 'Maire' }}</h5>
                <h4 class="card-title">Demandes en attente</h4>
            </div>
            <div class="card-body">
                <div class="table-full-width table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Objet</th>
                            <th>Date</th>
                        </thead>
                        <tbody>
                            @forelse($recentQueue as $d)
                            <tr>
                                <td>{{ $d->nom ?: ($d->origine_detail ?: '—') }}</td>
                                <td>{{ $d->typdm ?: '—' }}</td>
                                <td class="text-truncate" style="max-width:220px;" title="{{ $d->objet }}">{{ $d->objet ?: '—' }}</td>
                                <td>{{ $d->daterecpFormatted() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Aucune demande en attente.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@else
<div class="row">
    @if($isAdmin)
    <div class="col-lg-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="now-ui-icons files_paper text-info"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <p class="card-category">Nombre de Demandes</p>
                            <h4 class="card-title">{{ $totalDemandes }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <hr>
                <div class="stats">
                    <i class="now-ui-icons files_box"></i> {{ $isAdmin ? 'Total déposé au guichet' : 'Concernant votre service' }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="col-lg-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="now-ui-icons shopping_delivery-fast text-primary"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <p class="card-category">Demandes Assignées</p>
                            <h4 class="card-title">{{ $totalDemandesAssignees }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <hr>
                <div class="stats">
                    <i class="now-ui-icons location_compass-05"></i>
                    {{ $isAdmin ? 'Tous services confondus' : 'Pour votre service' }}
                </div>
            </div>
        </div>
    </div>

    @if($isAdmin)
    <div class="col-lg-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="now-ui-icons design_bullet-list-67 text-warning"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <p class="card-category">Types de Demande</p>
                            <h4 class="card-title">{{ $totalTypes }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <hr>
                <div class="stats">
                    <i class="now-ui-icons ui-1_settings-gear-63"></i> {{ $isAdmin ? 'Catégories actives' : 'Types reçus par votre service' }}
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="col-lg-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="now-ui-icons business_briefcase-24 text-danger"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <p class="card-category">Demandes en Cours</p>
                            <h4 class="card-title">{{ $totalEnCours }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <hr>
                <div class="stats">
                    <i class="now-ui-icons ui-1_email-85"></i> En attente de clôture par votre service
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="col-lg-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="now-ui-icons ui-1_check text-success"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <p class="card-category">Taux d'Acceptation</p>
                            <h4 class="card-title">{{ $tauxAcceptation }}%</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <hr>
                <div class="stats">
                    <i class="now-ui-icons ui-1_check text-success"></i> {{ $totalAcceptees }} acceptées
                    &nbsp;·&nbsp;
                    <i class="now-ui-icons ui-1_simple-remove text-danger"></i> {{ $totalRefusees }} refusées
                </div>
            </div>
        </div>
    </div>
</div>

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
                    <i class="now-ui-icons design_bullet-list-67"></i> {{ $totalTypes }} types au total
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Acceptées/Refusées + Dernières demandes -->
<div class="row">
    <div class="col-lg-5">
        <div class="card card-chart">
            <div class="card-header">
                <h5 class="card-category">Traitement des demandes</h5>
                <h4 class="card-title">Acceptées / Refusées</h4>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="reponseChart"></canvas>
                </div>
            </div>
            <div class="card-footer">
                <div class="stats">
                    <i class="now-ui-icons ui-1_check"></i> {{ $totalAcceptees }} acceptées
                    &nbsp;·&nbsp;
                    <i class="now-ui-icons ui-1_simple-remove"></i> {{ $totalRefusees }} refusées
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card card-tasks">
            <div class="card-header">
                <h5 class="card-category">Activité récente</h5>
                <h4 class="card-title">{{ $isAdmin ? 'Dernières Demandes Déposées' : 'Dernières Demandes de votre Service' }}</h4>
            </div>
            <div class="card-body">
                <div class="table-full-width table-responsive">
                    @if($isAdmin)
                    <table class="table">
                        <thead class="text-primary">
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Date</th>
                        </thead>
                        <tbody>
                            @forelse($recentDemandes as $d)
                            <tr>
                                <td>{{ $d->nom ?: ($d->origine_detail ?: '—') }}</td>
                                <td>{{ $d->typdm ?: '—' }}</td>
                                <td>{{ $d->daterecpFormatted() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Aucune demande pour le moment.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @else
                    <table class="table">
                        <thead class="text-primary">
                            <th>Nom</th>
                            <th>Objet</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </thead>
                        <tbody>
                            @forelse($recentServiceDemandes as $d)
                            <tr>
                                <td>{{ $d->nom ?: ($d->origine_detail ?: '—') }}</td>
                                <td class="text-truncate" style="max-width:180px;" title="{{ $d->objet }}">{{ $d->objet ?: '—' }}</td>
                                <td><span class="badge {{ \App\Models\Tabdepot::circuitStepBadgeClass($d->statut_circuit) }}">{{ $d->statutLabel() }}</span></td>
                                <td>{{ $d->daterecpFormatted() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Aucune demande pour le moment.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
            <div class="card-footer">
                <hr>
                <div class="stats">
                    <i class="now-ui-icons loader_refresh"></i> Mis à jour à l'instant
                </div>
            </div>
        </div>
    </div>
</div>

@if($isAdmin && $serviceLabels->isNotEmpty())
<div class="row">
    <div class="col-md-12">
        <div class="card card-chart">
            <div class="card-header">
                <h5 class="card-category">Charge de travail</h5>
                <h4 class="card-title">Demandes par Service</h4>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="serviceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endif

</div>

@endsection

@section('scripts')
@if(!$isPlainUser && !$isCabinet && !$isMaireUser)
<script src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var months = @json($months);
    var monthCounts = @json($monthCounts);
    var typeLabels = @json($typeLabels);
    var typeCounts = @json($typeCounts);
    var serviceLabels = @json($serviceLabels);
    var serviceCounts = @json($serviceCounts);
    var totalAcceptees = {{ $totalAcceptees }};
    var totalRefusees = {{ $totalRefusees }};

    var palette = ['#2CA8FF', '#FB404B', '#18ce0f', '#FFA534', '#9C27B0', '#00BCD4', '#FF5722', '#607D8B'];

    new Chart(document.getElementById('evolutionChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Demandes',
                data: monthCounts,
                borderColor: '#e8862c',
                backgroundColor: 'rgba(232,134,44,0.15)',
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#e8862c',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            plugins: { legend: { display: false } }
        }
    });

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

    new Chart(document.getElementById('reponseChart'), {
        type: 'bar',
        data: {
            labels: ['Acceptées', 'Refusées'],
            datasets: [{
                label: 'Demandes',
                data: [totalAcceptees, totalRefusees],
                backgroundColor: ['#18ce0f', '#FB404B'],
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            plugins: { legend: { display: false } }
        }
    });

    @if($isAdmin)
    var serviceChartEl = document.getElementById('serviceChart');
    if (serviceChartEl) {
        new Chart(serviceChartEl, {
            type: 'bar',
            data: {
                labels: serviceLabels,
                datasets: [{
                    label: 'Demandes',
                    data: serviceCounts,
                    backgroundColor: '#2CA8FF',
                    borderRadius: 6,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { display: false } }
            }
        });
    }
    @endif
});
</script>
@endif
@endsection