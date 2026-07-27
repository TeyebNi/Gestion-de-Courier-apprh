<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>
@yield('title')
    </title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
    <!-- CSS Files -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../assets/css/now-ui-dashboard.css?v=1.0.1" rel="stylesheet" />
    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="../assets/demo/demo.css" rel="stylesheet" />
</head>

<body class="">
    <div class="wrapper ">
        <div class="sidebar" data-color="blue"><!-- Tip 1: You can change the color sidebar using: data-color="blue | green | orange | red | yellow"-->
            <div class="logo">
                <a href="http://www.creative-tim.com" class="simple-text logo-mini">
                    Gt
                </a>
                <a href="http://www.creative-tim.com" class="simple-text logo-normal">
                    Courier
                </a>
            </div>
            <div class="sidebar-wrapper">
                <ul class="nav">
                    <li>
                        <a href="">
                            <i class="now-ui-icons design_app"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    @if(auth()->user()->isAdmin() || !empty(auth()->user()->service))
                    <li>
                        <a href="{{('affectation')}}">
                            <i class="now-ui-icons location_map-big"></i>
                            <p>Affectation</p>
                        </a>
                    </li>
                    @endif
                    @if(auth()->check() && auth()->user()->isAdmin())
                    <li>
                        <a href="{{('orientation')}}">
                            <i class="now-ui-icons education_atom"></i>
                            <p>Orientation</p>
                        </a>
                    </li>
                    @endif
                    
<li class="active">
                        <a href="{{('depot')}}">
                            <i class="now-ui-icons design_bullet-list-67"></i>
                            <p>Dépôt des Demandes</p>
                        </a>
                    </li>
                    @if(auth()->check() && auth()->user()->isAdmin())
                    <li>
                        <a href="{{('typedem')}}">
                            <i class="now-ui-icons text_caps-small"></i>
                            <p>Type Demande</p>
                        </a>
                    </li>
                    <li>
                        <a href="{{('utilisateurs')}}">
                            <i class="now-ui-icons users_single-02"></i>
                            <p>Les Utilisateurs</p>
                        </a>
                    </li>
                    @endif
                    <li class="active-pro">
                        <a href="{{('typedem')}}">
                            <i class="now-ui-icons arrows-1_cloud-download-93"></i>
                            <p></p>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="main-panel">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-transparent  navbar-absolute bg-primary fixed-top">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <div class="navbar-toggle">
                            <button type="button" class="navbar-toggler">
                                <span class="navbar-toggler-bar bar1"></span>
                                <span class="navbar-toggler-bar bar2"></span>
                                <span class="navbar-toggler-bar bar3"></span>
                            </button>
                        </div>
                        <a class="navbar-brand" href="#pablo">Gestion de Courier</a>
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
                            <li class="nav-item" style="position:relative;">
                                <a class="nav-link" href="{{('notifications')}}" style="position:relative; display:inline-block;">
                                    <i class="now-ui-icons ui-1_bell-53"></i>
                                    @php $unread = \App\Models\ServiceNotification::where('is_read', false)->where('service', auth()->user()->service)->count(); @endphp
                                    @if($unread > 0)
                                        <span class="badge badge-danger" style="position:absolute; top:-2px; right:-2px; font-size:10px; padding:2px 5px;">{{ $unread }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#pablo">
                                    <i class="now-ui-icons media-2_sound-wave"></i>
                                    <p>
                                        <span class="d-lg-none d-md-block">Stats</span>
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="http://example.com" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="now-ui-icons location_world"></i>
                                    <p>
                                        <span class="d-lg-none d-md-block">Some Actions</span>
                                    </p>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                </div>
                            </li>
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
            <div class="panel-header panel-header-sm">
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
<script src="../assets/js/core/jquery.min.js"></script>
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
<!--  Google Maps Plugin    -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script>
<!-- Chart JS -->
<script src="../assets/js/plugins/chartjs.min.js"></script>
<!--  Notifications Plugin    -->
<script src="../assets/js/plugins/bootstrap-notify.js"></script>
<!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
<script src="../assets/js/now-ui-dashboard.js?v=1.0.1"></script>
<!-- Now Ui Dashboard DEMO methods, don't include it in your project! -->
<script src="../assets/demo/demo.js"></script>

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

<!-- Déconnexion automatique après 15 minutes d'inactivité -->
<script>
(function () {
    var logoutUrl = "{{ route('auth.login.logout') }}";
    var keepAliveUrl = "{{ route('keep-alive') }}";
    var timeoutMinutes = 2;
    var warningBeforeMinutes = 1;
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