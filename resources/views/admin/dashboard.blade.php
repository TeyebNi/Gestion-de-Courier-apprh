@extends('layouts.master')

@section('title')
Tableau de bord
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <h3 style="font-weight: 700; color: #2c3e50; margin: 0 0 20px;">Gestion de Dashboard</h3>
    </div>
</div>

@if($isPlainUser)

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
                            <th>NNI</th>
                            <th>Téléphone</th>
                            <th>Type</th>
                            <th>Date</th>
                        </thead>
                        <tbody>
                            @forelse($recentDemandesUser as $d)
                            <tr>
                                <td>{{ $d->nom }}</td>
                                <td>{{ $d->nni }}</td>
                                <td>{{ $d->tel }}</td>
                                <td>{{ $d->typdm }}</td>
                                <td>{{ $d->daterecp }}</td>
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
<div class="row">
    <div class="col-lg-4 col-md-6">
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
                        <i class="now-ui-icons arrows-1_share-66"></i>
                        {{ $isCabinet ? 'Aller au Cabinet' : 'Aller aux décisions' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
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
                            <th>Date</th>
                        </thead>
                        <tbody>
                            @forelse($recentQueue as $d)
                            <tr>
                                <td>{{ $d->nom }}</td>
                                <td>{{ $d->typdm }}</td>
                                <td>{{ $d->daterecp }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Aucune demande en attente.</td>
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
                            <p class="card-category">Affectations</p>
                            <h4 class="card-title">{{ $totalAffectations }}</h4>
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
                            <i class="now-ui-icons ui-1_bell-53 text-danger"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <p class="card-category">Notifications Non Lues</p>
                            <h4 class="card-title">{{ $totalUnread }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <hr>
                <div class="stats">
                    <i class="now-ui-icons ui-1_email-85"></i> À traiter pour votre service
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
                    <i class="now-ui-icons ui-1_bell-53"></i> {{ $totalEnAttente }} en attente de réponse
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
                <h4 class="card-title">{{ $isAdmin ? 'Dernières Demandes Déposées' : 'Dernières Notifications de votre Service' }}</h4>
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
                                <td>{{ $d->nom }}</td>
                                <td>{{ $d->typdm }}</td>
                                <td>{{ $d->daterecp }}</td>
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
                            <th>Message</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </thead>
                        <tbody>
                            @forelse($recentNotifications as $n)
                            <tr>
                                <td>{{ $n->message }}</td>
                                <td>
                                    @if($n->response === 'accepted')
                                        <span class="badge badge-success">Acceptée</span>
                                    @elseif($n->response === 'rejected')
                                        <span class="badge badge-danger">Refusée</span>
                                    @else
                                        <span class="badge badge-warning">En attente</span>
                                    @endif
                                </td>
                                <td>{{ $n->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Aucune notification pour le moment.</td>
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
                <h4 class="card-title">Affectations par Service</h4>
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
    var totalEnAttente = {{ $totalEnAttente }};

    var palette = ['#2CA8FF', '#FB404B', '#18ce0f', '#FFA534', '#9C27B0', '#00BCD4', '#FF5722', '#607D8B'];

    new Chart(document.getElementById('evolutionChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Demandes',
                data: monthCounts,
                borderColor: '#2CA8FF',
                backgroundColor: 'rgba(44,168,255,0.15)',
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#2CA8FF',
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
            labels: ['Acceptées', 'Refusées', 'En attente'],
            datasets: [{
                data: [totalAcceptees, totalRefusees, totalEnAttente],
                backgroundColor: ['#18ce0f', '#FB404B', '#FFA534'],
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
                    label: 'Affectations',
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