<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>
@yield('title')
    </title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    <!--     Fonts and icons     -->
    <link href="{{ asset('assets/fonts/montserrat/200.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/fonts/montserrat/400.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/fonts/montserrat/700.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <!-- CSS Files -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/now-ui-dashboard.css?v=1.0.1') }}" rel="stylesheet" />
    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="{{ asset('assets/demo/demo.css') }}" rel="stylesheet" />
    <style>
        /* Sidebar personnalisé : dégradé bleu marine élégant, cohérent avec le reste de l'application */
        .sidebar[data-color="blue"]:after {
            background: #16233d;
            background: linear-gradient(180deg, #0c2646 0%, #16233d 55%, #1d3457 100%);
        }
        .sidebar .logo {
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }
        .sidebar[data-color="blue"] .nav li.active > a,
        .sidebar[data-color="blue"] .nav li.active > a i {
            color: #e8862c;
        }
        .sidebar .nav li > a {
            transition: all 0.2s ease-in-out;
        }
        .sidebar .nav li:not(.active) > a:hover {
            color: #ffffff !important;
            opacity: 0.85;
        }

        /* Bordures verticales entre les colonnes de tous les tableaux de l'application */
        .table th,
        .table td {
            border-right: 2px solid #adb5bd;
            border-top: 2px solid #adb5bd;
        }
        .table th:last-child,
        .table td:last-child {
            border-right: none;
        }
        .table thead th {
            border-top: 2px solid #adb5bd !important;
            border-bottom: 2px solid #adb5bd !important;
            border-right: 2px solid #adb5bd !important;
        }
        .table thead th:last-child {
            border-right: none !important;
        }
    </style>
</head>

<body class="">
    <div class="wrapper ">
        <div class="sidebar" data-color="blue"><!-- Tip 1: You can change the color sidebar using: data-color="blue | green | orange | red | yellow"-->
            <div class="logo">
                <a href="{{ route('dashboard') }}" class="simple-text logo-mini">
                    Gt
                </a>
                <a href="{{ route('dashboard') }}" class="simple-text logo-normal">
                    Courier
                </a>
            </div>
            <div class="sidebar-wrapper">
                <ul class="nav">
                    <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}">
                            <i class="now-ui-icons design_app"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    @if(auth()->check() && auth()->user()->isAdmin())
                    <li class="{{ request()->is('orientation*') ? 'active' : '' }}">
                        <a href="{{ route('orientation.index') }}">
                            <i class="now-ui-icons location_compass-05"></i>
                            <p>Orientation</p>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->canAccessDepot())
                    @php
                        $pendingSendCount = \App\Models\Tabdepot::where('statut_circuit', 'accueil')->count();
                    @endphp
                    <li class="{{ request()->is('depot') || request()->is('depot/*') ? 'active' : '' }}">
                        <a href="{{ route('depot.index') }}">
                            <i class="now-ui-icons files_box"></i>
                            <p>
                                Dépôt des Demandes
                                @if($pendingSendCount > 0)
                                    <span class="badge badge-warning" title="En attente d'envoi au Cabinet">{{ $pendingSendCount }}</span>
                                @endif
                            </p>
                        </a>
                    </li>
                    <li class="{{ request()->is('depot-corbeille*') ? 'active' : '' }}">
                        <a href="{{ route('depot.trashed') }}">
                            <i class="fas fa-trash-restore"></i>
                            <p>Corbeille</p>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->canAccessCabinet())
                    <li class="{{ request()->is('circuit/fatou*') ? 'active' : '' }}">
                        <a href="{{ route('circuit.fatou.index') }}">
                            <i class="now-ui-icons arrows-1_share-66"></i>
                            <p>Circuit - Cabinet de Maire</p>
                        </a>
                    </li>
                    @endif
                    @if(!empty(auth()->user()->service) || auth()->user()->canAccessAllServices())
                    <li class="{{ request()->is('circuit/service*') ? 'active' : '' }}">
                        <a href="{{ route('circuit.service.index') }}">
                            <i class="now-ui-icons business_briefcase-24"></i>
                            <p>Demandes du Circuit</p>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->canAccessSuivi())
                    <li class="{{ request()->is('circuit/suivi*') ? 'active' : '' }}">
                        <a href="{{ route('circuit.suivi') }}">
                            <i class="now-ui-icons ui-1_zoom-bold"></i>
                            <p>Suivi des Demandes</p>
                        </a>
                    </li>
                    @endif
                    @if(auth()->check() && auth()->user()->canManageUsers())
                    <li class="{{ request()->is('utilisateurs*') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}">
                            <i class="now-ui-icons users_single-02"></i>
                            <p>Les Utilisateurs</p>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="main-panel">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-absolute bg-white fixed-top">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <div class="navbar-toggle">
                            <button type="button" class="navbar-toggler">
                                <span class="navbar-toggler-bar bar1"></span>
                                <span class="navbar-toggler-bar bar2"></span>
                                <span class="navbar-toggler-bar bar3"></span>
                            </button>
                        </div>
                        <a class="navbar-brand" style="font-weight: 700; color: #212529; font-size: 1.15em;" href="{{ route('dashboard') }}">{{ request()->routeIs('dashboard') ? 'Gestion de Courier' : '' }}</a>
                    </div>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navigation">
                        <form method="GET" action="{{ url()->current() }}">
                            <div class="input-group no-border">
                                <input type="text" class="form-control"   name="search" value="{{ request('search') }}"  placeholder="Rechercher...">
                                <button type="submit" class="input-group-addon" style="border:none; background:transparent;">
                                    <i class="now-ui-icons ui-1_zoom-bold"></i>
                                </button>
                            </div>
                        </form>
                        <ul class="navbar-nav">
                            @if(auth()->user()->canAccessDepot() || auth()->user()->canAccessCabinet())
                            @php
                                $accueilPendingCount = auth()->user()->canAccessDepot()
                                    ? \App\Models\Tabdepot::where('statut_circuit', 'accueil')->count()
                                    : 0;
                                $cabinetPendingCount = auth()->user()->canAccessCabinet()
                                    ? \App\Models\Tabdepot::where('statut_circuit', 'fatou')->count()
                                    : 0;
                                $nouvellesAnnotationsCount = auth()->user()->canAccessDepot()
                                    ? \App\Models\Tabdepot::where('vue_accueil', false)->count()
                                    : 0;
                                $circuitPendingTotal = $accueilPendingCount + $cabinetPendingCount + $nouvellesAnnotationsCount;
                            @endphp
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="circuitBellDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="position:relative;">
                                    <i class="now-ui-icons ui-1_bell-53"></i>
                                    @if($circuitPendingTotal > 0)
                                        <span class="badge badge-danger" style="position:absolute; top:2px; right:2px; font-size:10px; padding:3px 5px;">{{ $circuitPendingTotal }}</span>
                                    @endif
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="circuitBellDropdown" style="min-width:320px;">
                                    <span class="dropdown-item-text"><strong>En attente de traitement</strong></span>
                                    <div class="dropdown-divider"></div>
                                    @if(auth()->user()->canAccessDepot())
                                        <a class="dropdown-item" href="{{ route('depot.index') }}" style="white-space:normal;">
                                            <span class="badge badge-secondary">Accueil</span>
                                            <div>{{ $accueilPendingCount }} demande(s) à transmettre au Cabinet</div>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        @if($nouvellesAnnotationsCount > 0)
                                        <a class="dropdown-item" href="{{ route('circuit.suivi') }}" style="white-space:normal;">
                                            <span class="badge badge-warning">Annotations</span>
                                            <div>{{ $nouvellesAnnotationsCount }} nouvelle(s) annotation(s) du Maire à consulter</div>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        @endif
                                    @endif
                                    @if(auth()->user()->canAccessCabinet())
                                        <a class="dropdown-item" href="{{ route('circuit.fatou.index') }}" style="white-space:normal;">
                                            <span class="badge badge-info">Cabinet de Maire</span>
                                            <div>{{ $cabinetPendingCount }} demande(s) en attente d'annotations</div>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                    @endif
                                </div>
                            </li>
                            @endif
                            @if(!empty(auth()->user()->service) || auth()->user()->canAccessAllServices())
                            @php
                                $notifBaseQuery = auth()->user()->canAccessAllServices()
                                    ? \App\Models\ServiceNotification::query()
                                    : \App\Models\ServiceNotification::where('service', auth()->user()->service);
                                $unreadNotifCount = (clone $notifBaseQuery)->where('is_read', false)->count();
                                $recentNotifs = (clone $notifBaseQuery)->where('is_read', false)->orderByDesc('created_at')->limit(5)->get();
                            @endphp
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="notifBellDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="position:relative;">
                                    <i class="now-ui-icons ui-1_bell-53"></i>
                                    @if($unreadNotifCount > 0)
                                        <span class="badge badge-danger" style="position:absolute; top:2px; right:2px; font-size:10px; padding:3px 5px;">{{ $unreadNotifCount }}</span>
                                    @endif
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notifBellDropdown" style="min-width:320px;">
                                    <span class="dropdown-item-text"><strong>Notifications</strong></span>
                                    <div class="dropdown-divider"></div>
                                    @forelse($recentNotifs as $n)
                                        <a class="dropdown-item" href="{{ route('notifications.index') }}" style="white-space:normal;">
                                            <span class="badge badge-primary">{{ $n->service }}</span>
                                            <div>{{ $n->message }}</div>
                                            <small class="text-muted">{{ $n->created_at->format('d/m/Y H:i') }}</small>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                    @empty
                                        <span class="dropdown-item-text text-muted">Aucune nouvelle notification.</span>
                                        <div class="dropdown-divider"></div>
                                    @endforelse
                                    <a class="dropdown-item text-center" href="{{ route('notifications.index') }}"><strong>Voir toutes les notifications</strong></a>
                                </div>
                            </li>
                            @endif
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userAccountDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="now-ui-icons users_single-02"></i>
                                    <p>
                                        <span class="d-lg-none d-md-block">
                                            {{ auth()->check() ? auth()->user()->name : 'Compte' }}
                                        </span>
                                    </p>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userAccountDropdown">
                                    @auth
                                        <span class="dropdown-item-text">
                                            <strong>{{ auth()->user()->name }}</strong><br>
                                            <small class="text-muted">{{ auth()->user()->email }}</small><br>
                                            <span class="badge badge-{{ auth()->user()->isAdmin() ? 'danger' : 'info' }}">
                                                {{ auth()->user()->isAdmin() ? 'Admin' : 'User' }}
                                            </span>
                                        </span>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="{{ route('auth.login.logout') }}"><i class="now-ui-icons media-1_button-power"></i> Logout</a>
                                    @endauth
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- End Navbar -->
            <div class="panel-header panel-header-sm" style="background: #FFFFFF;">
            </div>
            <div class="content">
                @yield('content')
                
            </div>
            <footer class="footer">
                <div class="container-fluid">
                    <nav>
                        <ul>
                            <li>
                                <a href="https://www.creative-tim.com">
                                    Courier
                                </a>
                            </li>
                            <li>
                                <a href="http://presentation.creative-tim.com">
                                    
                                </a>
                            </li>
                            <li>
                                <a href="http://blog.creative-tim.com">
                                    
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <div class="copyright">
                        &copy;
                        <script>
                            document.write(new Date().getFullYear())
                        </script>, Designed by
                        <a href="https://www.invisionapp.com" target="_blank">Invision</a>. Coded by
                        <a href="https://www.creative-tim.com" target="_blank">Creative Tim</a>.
                    </div>
                </div>
            </footer>
        </div>
    </div>
</body>
<!--   Core JS Files   -->
<script src="{{ asset('assets/js/core/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.jquery.min.js') }}"></script>
<!--  Google Maps Plugin    -->
<!-- Chart JS -->
<script src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script>
<!--  Notifications Plugin    -->
<script src="{{ asset('assets/js/plugins/bootstrap-notify.js') }}"></script>
<!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
<script src="{{ asset('assets/js/now-ui-dashboard.js?v=1.0.1') }}"></script>
<!-- Now Ui Dashboard DEMO methods, don't include it in your project! -->
<script src="{{ asset('assets/demo/demo.js') }}"></script>

<style>
/* Corrections responsive - mobile et tablette */
@media (max-width: 768px) {
    .modal-right {
        margin: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
    }
    .modal-right .modal-content {
        border-radius: 0;
        min-height: 100vh;
    }
    .table-responsive {
        font-size: 13px;
    }
    .btn-sm, .btn {
        white-space: nowrap;
    }
    .card-header .category {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
}
@media (max-width: 576px) {
    .navbar .form-group {
        display: none;
    }
    h4.card-title {
        font-size: 1.1rem;
    }
}
</style>

@yield('scripts')

<!-- Déconnexion automatique après 60 minutes d'inactivité -->
<script>
(function () {
    var logoutUrl = "{{ route('auth.login.logout') }}";
    var keepAliveUrl = "{{ route('keep-alive') }}";
    var timeoutMinutes = 60;
    var warningBeforeMinutes = 2;
    var inactivityTimer, warningTimer;
    var lastPing = 0;
    var pingIntervalMs = 60 * 1000; // 1 ping serveur max par minute

    function resetTimers() {
        clearTimeout(inactivityTimer);
        clearTimeout(warningTimer);
        warningTimer = setTimeout(showWarning, (timeoutMinutes - warningBeforeMinutes) * 60 * 1000);
        inactivityTimer = setTimeout(doLogout, timeoutMinutes * 60 * 1000);

        // Maintient la session serveur en vie tant qu'il y a une vraie activité
        var now = Date.now();
        if (now - lastPing > pingIntervalMs) {
            lastPing = now;
            fetch(keepAliveUrl, { credentials: 'same-origin' }).catch(function () {});
        }
    }

    function showWarning() {
        if (confirm("Vous allez être déconnecté dans " + warningBeforeMinutes + " minute(s) pour cause d'inactivité. Cliquez sur OK pour rester connecté.")) {
            // Vérifie que la session est encore vraiment valide côté serveur
            // avant de relancer les minuteurs (le temps de répondre au message
            // peut avoir suffi à faire expirer la session pendant ce temps).
            fetch(keepAliveUrl, { credentials: 'same-origin', redirect: 'manual' })
                .then(function (response) {
                    if (response.type === 'opaqueredirect' || response.status === 0 || response.status === 401 || response.status === 419) {
                        window.location.href = logoutUrl;
                    } else {
                        resetTimers();
                    }
                })
                .catch(function () {
                    window.location.href = logoutUrl;
                });
        }
    }

    function doLogout() {
        window.location.href = logoutUrl;
    }

    ['mousemove', 'mousedown', 'keypress', 'scroll', 'touchstart', 'click'].forEach(function (evt) {
        document.addEventListener(evt, resetTimers, false);
    });

    resetTimers();
})();
</script>
</html>