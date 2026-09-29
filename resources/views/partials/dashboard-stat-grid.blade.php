{{--
    Grille de petites cartes statistiques (même habillage que les cartes KPI
    en haut du dashboard) : une carte par valeur réelle (service, personne,
    étape...), plutôt qu'un graphique qui tronque ou agrège. Attend :
    - $title, $subtitle : titres de la section
    - $labels, $counts   : collections alignées (mêmes indices)
    - $icon (optionnel)  : icône now-ui par défaut pour chaque carte
    - $itemIcons (optionnel) : [libellé => [icône, couleur]] pour un rendu
      au cas par cas (utilisé pour les étapes du circuit et les résolutions)
    - $colWidth (optionnel) : classes de colonne Bootstrap
--}}
@php
    $defaultColors = ['info', 'primary', 'success', 'warning', 'danger'];
    $gridCol = $colWidth ?? 'col-lg-3 col-md-4 col-6';
    $itemIcons = $itemIcons ?? [];
@endphp
@if(($labels ?? collect())->isNotEmpty())
<div class="row">
    <div class="col-md-12">
        <div class="card card-chart">
            <div class="card-header">
                <h5 class="card-category">{{ $subtitle ?? 'Charge de travail' }}</h5>
                <h4 class="card-title">{{ $title }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($labels as $i => $label)
                    @php
                        $itemIcon = $itemIcons[$label][0] ?? ($icon ?? 'business_briefcase-24');
                        $itemColor = $itemIcons[$label][1] ?? $defaultColors[$i % count($defaultColors)];
                    @endphp
                    <div class="{{ $gridCol }} mb-3">
                        <div class="card card-stats card-stats-mini mb-0">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-4">
                                        <div class="icon-big text-center icon-warning">
                                            <i class="now-ui-icons {{ $itemIcon }} text-{{ $itemColor }}"></i>
                                        </div>
                                    </div>
                                    <div class="col-8">
                                        <div class="numbers">
                                            <p class="card-category" title="{{ $label }}">{{ $label }}</p>
                                            <h4 class="card-title">{{ $counts[$i] }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
