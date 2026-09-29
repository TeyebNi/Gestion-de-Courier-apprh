@include('partials.dashboard-stat-grid', [
    'title' => 'Courriers par Service',
    'subtitle' => 'Charge de travail',
    'labels' => $serviceLabels,
    'counts' => $serviceCounts,
    'icon' => 'business_briefcase-24',
])

@include('partials.dashboard-stat-grid', [
    'title' => 'Courriers par Adjoint au Maire',
    'subtitle' => 'Charge de travail',
    'labels' => $maireAdjointLabels,
    'counts' => $maireAdjointCounts,
    'icon' => 'users_single-02',
])

@include('partials.dashboard-stat-grid', [
    'title' => 'Courriers par Conseiller',
    'subtitle' => 'Charge de travail',
    'labels' => $conseillerLabels,
    'counts' => $conseillerCounts,
    'icon' => 'users_single-02',
])

@include('partials.dashboard-stat-grid', [
    'title' => 'Courriers par Étape du Circuit',
    'subtitle' => "Vue d'ensemble",
    'labels' => $stageLabels,
    'counts' => $stageCounts,
    'colWidth' => 'col-lg-2 col-md-4 col-6',
    'itemIcons' => [
        "À l'accueil" => ['ui-1_calendar-60', 'info'],
        'Chez le Cabinet de Maire' => ['ui-1_send', 'primary'],
        'Chez un service' => ['business_briefcase-24', 'success'],
        "Chez l'Adjoint au Maire" => ['users_single-02', 'warning'],
        'Chez le Conseiller' => ['users_single-02', 'warning'],
        'Clôturée' => ['ui-1_check', 'danger'],
    ],
])

@include('partials.dashboard-stat-grid', [
    'title' => 'Clôtures par Résolution',
    'subtitle' => 'Détail',
    'labels' => $clotureLabels,
    'counts' => $clotureCounts,
    'itemIcons' => [
        'Traitées' => ['ui-1_check', 'success'],
        'Classées' => ['files_box', 'info'],
        'Convoquées' => ['ui-1_bell-53', 'warning'],
        'Non précisée' => ['ui-1_simple-remove', 'danger'],
    ],
])
