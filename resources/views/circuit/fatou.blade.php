@extends('layouts.master')

@section('title')
Cabinet de Maire - Circuit des Demandes
@endsection

@section('content')

@if(session('success'))
<div id="successOverlay" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; display:flex; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:32px; width:90%; max-width:380px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
        <div style="margin:0 auto 16px; width:64px; height:64px; border-radius:50%; border:3px solid #28a745; display:flex; align-items:center; justify-content:center;">
            <span style="color:#28a745; font-size:32px;">&#10003;</span>
        </div>
        <p style="color:#333; margin-bottom:20px;">{{ session('success') }}</p>
        <button id="closeSuccessCircuit" class="btn btn-info" style="width:100%;">OK</button>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var closeBtn = document.getElementById('closeSuccessCircuit');
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            document.getElementById('successOverlay').style.display = 'none';
        });
    }
});
</script>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    Cabinet de Maire — Demandes en attente d'annotations
                </h4>
                <p class="text-muted mb-0" style="font-size: 0.9em;">
                    Portez le dossier au Maire, recueillez ses annotations, puis saisissez-les ici.
                </p>
            </div>
            <div class="card-body">
                @forelse($aEnvoyer as $d)
                <div class="card" style="border: 1px solid #eee; margin-bottom: 15px;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p><strong>Code :</strong> {{ $d->reference ?: '—' }}</p>
                                <p><strong>Objet :</strong> {{ $d->objet ?: '—' }}</p>
                                <p><strong>Origine :</strong> @include('partials.origine-badge', ['demande' => $d])</p>
                                <p><strong>Date de réception :</strong> {{ $d->daterecpFormatted() }}</p>
                            </div>
                            <div class="col-md-4">
                                <form action="{{ route('circuit.decider', $d) }}" method="post">
                                    @csrf
                                    <div class="form-group">
                                        <label>Annotations du Maire <span class="text-danger">*</span></label>
                                        <textarea name="remarque_maire" class="form-control" rows="4" placeholder="Ce que le Maire a annoté sur le dossier..." required></textarea>
                                    </div>
                                    @php
                                        $preselected = $d->origine === 'interne' ? $orientations->firstWhere('name', $d->origine_detail) : null;
                                    @endphp
                                    <div class="form-group">
                                        <label>Destination (optionnel)</label>
                                        <select class="form-control top-category" data-card="{{ $d->id }}" data-preselected="{{ $preselected?->name }}">
                                            <option value="">Aucun (classer directement)</option>
                                            <option value="service" {{ $preselected ? 'selected' : '' }}>Service</option>
                                            <option value="maire_adjoint">Adjoint au Maire</option>
                                            <option value="conseiller">Conseiller</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="service_pick_wrap_{{ $d->id }}" style="display:none;">
                                        <label>Quel service ?</label>
                                        <select class="form-control service-pick" data-card="{{ $d->id }}"></select>
                                    </div>
                                    <div class="form-group" id="division_pick_wrap_{{ $d->id }}" style="display:none;">
                                        <label>Précisez</label>
                                        <select class="form-control division-pick" data-card="{{ $d->id }}"></select>
                                    </div>
                                    <div class="form-group" id="person_pick_wrap_{{ $d->id }}" style="display:none;">
                                        <label id="person_pick_label_{{ $d->id }}"></label>
                                        <select class="form-control person-pick" data-card="{{ $d->id }}"></select>
                                    </div>
                                    <input type="hidden" name="destination_category" id="final_category_{{ $d->id }}" value="">
                                    <input type="hidden" name="destination_value" id="final_value_{{ $d->id }}" value="">
                                    <button type="submit" class="btn btn-primary btn-block">Enregistrer les annotations</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-center text-muted">Aucune demande en attente.</p>
                @endforelse
                <div class="d-flex justify-content-center mt-3">
                    {{ $aEnvoyer->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    Demandes déjà annotées
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>Code</th>
                            <th>Objet</th>
                            <th>Annotations du Maire</th>
                            <th>Où se trouve la demande ?</th>
                            <th>Dernière mise à jour</th>
                        </thead>
                        <tbody>
                            @forelse($dejaAnnotees as $d)
                            <tr>
                                <td>{{ $d->reference ?: '—' }}</td>
                                <td class="text-truncate" style="max-width:220px;" title="{{ $d->objet }}">{{ $d->objet ?: '—' }}</td>
                                <td class="text-truncate" style="max-width:260px;" title="{{ $d->remarque_maire }}">{{ $d->remarque_maire }}</td>
                                <td>
                                    <span class="badge {{ \App\Models\Tabdepot::circuitStepBadgeClass($d->statut_circuit) }}">{{ $d->statutLabel() }}</span>
                                </td>
                                <td>{{ $d->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aucune demande annotée pour le moment.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $dejaAnnotees->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var ORIENTATIONS = @json($orientations->pluck('name'));
    var DIVISIONS_BY_SERVICE = @json($divisionsByService);
    var CHEF_SERVICE_BY_SERVICE = @json($chefServiceByService);
    var PEOPLE_BY_KIND = {
        maire_adjoint: @json($peopleByKind['maire_adjoint']),
        conseiller: @json($peopleByKind['conseiller']),
    };
    var PERSON_LABELS = {
        maire_adjoint: 'Quel Adjoint au Maire ?',
        conseiller: 'Quel Conseiller ?',
    };
    var PERSON_CATEGORY_LABELS = {
        maire_adjoint: 'Adjoint au Maire',
        conseiller: 'Conseiller',
    };

    function populateSelect(select, options, placeholder, labelPrefix) {
        select.innerHTML = '';
        var placeholderOpt = document.createElement('option');
        placeholderOpt.value = '';
        placeholderOpt.textContent = placeholder;
        select.appendChild(placeholderOpt);
        options.forEach(function (name) {
            var opt = document.createElement('option');
            opt.value = name;
            opt.textContent = labelPrefix ? (labelPrefix + ' — ' + name) : name;
            select.appendChild(opt);
        });
    }

    // Comme populateSelect, mais pour des rôles à titre de poste propre
    // (Division, Conseiller) : chaque option est {title, name}, la valeur
    // soumise est le titre (routage), le texte affiché ajoute le nom du
    // titulaire actuel pour la clarté.
    function populateTitledSelect(select, items, placeholder, labelPrefix) {
        select.innerHTML = '';
        var placeholderOpt = document.createElement('option');
        placeholderOpt.value = '';
        placeholderOpt.textContent = placeholder;
        select.appendChild(placeholderOpt);
        items.forEach(function (item) {
            var opt = document.createElement('option');
            opt.value = item.title;
            opt.textContent = labelPrefix + ' ' + item.title + ' — ' + item.name;
            select.appendChild(opt);
        });
    }

    function setFinal(cardId, category, value) {
        document.getElementById('final_category_' + cardId).value = category || '';
        document.getElementById('final_value_' + cardId).value = value || '';
    }

    function onTopCategoryChange(select, preselectedService) {
        var cardId = select.dataset.card;
        var category = select.value;
        var serviceWrap = document.getElementById('service_pick_wrap_' + cardId);
        var divisionWrap = document.getElementById('division_pick_wrap_' + cardId);
        var personWrap = document.getElementById('person_pick_wrap_' + cardId);

        serviceWrap.style.display = 'none';
        divisionWrap.style.display = 'none';
        personWrap.style.display = 'none';
        setFinal(cardId, null, null);

        if (category === 'service') {
            var servicePick = serviceWrap.querySelector('select');
            populateSelect(servicePick, ORIENTATIONS, 'Sélectionner un service');
            if (preselectedService) { servicePick.value = preselectedService; }
            serviceWrap.style.display = '';
            onServicePickChange(servicePick);
        } else if (category === 'maire_adjoint') {
            var personPick = personWrap.querySelector('select');
            document.getElementById('person_pick_label_' + cardId).textContent = PERSON_LABELS[category];
            populateSelect(personPick, PEOPLE_BY_KIND[category] || [], 'Sélectionner...', PERSON_CATEGORY_LABELS[category]);
            personWrap.style.display = '';
            setFinal(cardId, category, personPick.value);
        } else if (category === 'conseiller') {
            var conseillerPick = personWrap.querySelector('select');
            document.getElementById('person_pick_label_' + cardId).textContent = PERSON_LABELS[category];
            populateTitledSelect(conseillerPick, PEOPLE_BY_KIND[category] || [], 'Sélectionner...', PERSON_CATEGORY_LABELS[category]);
            personWrap.style.display = '';
            setFinal(cardId, category, conseillerPick.value);
        }
    }

    function onServicePickChange(select) {
        var cardId = select.dataset.card;
        var divisionWrap = document.getElementById('division_pick_wrap_' + cardId);

        if (!select.value) {
            divisionWrap.style.display = 'none';
            setFinal(cardId, 'service', '');
            return;
        }

        // Une seule liste combinée : le service lui-même en premier (choix par
        // défaut), suivi de ses divisions et de son Chef de Service — pas un
        // champ séparé et optionnel à côté du service. La catégorie de chaque
        // option est portée par data-category (pas la valeur, pour distinguer
        // une Division et un Chef de Service qui porteraient le même nom).
        var divisionPick = divisionWrap.querySelector('select');
        divisionPick.innerHTML = '';
        var serviceOpt = document.createElement('option');
        serviceOpt.value = '__service__';
        serviceOpt.dataset.category = 'service';
        serviceOpt.textContent = 'Service ' + select.value;
        divisionPick.appendChild(serviceOpt);
        (DIVISIONS_BY_SERVICE[select.value] || []).forEach(function (division) {
            var opt = document.createElement('option');
            opt.value = division.title;
            opt.dataset.category = 'division';
            opt.textContent = 'Division ' + division.title + ' — ' + division.name;
            divisionPick.appendChild(opt);
        });
        (CHEF_SERVICE_BY_SERVICE[select.value] || []).forEach(function (name) {
            var opt = document.createElement('option');
            opt.value = name;
            opt.dataset.category = 'chef_service';
            opt.textContent = 'Chef de Service — ' + name;
            divisionPick.appendChild(opt);
        });
        divisionWrap.style.display = '';
        onDivisionPickChange(divisionPick);
    }

    function onDivisionPickChange(select) {
        var cardId = select.dataset.card;
        var servicePick = document.getElementById('service_pick_wrap_' + cardId).querySelector('select');
        var category = select.options[select.selectedIndex] ? select.options[select.selectedIndex].dataset.category : 'service';

        if (!category || category === 'service') {
            setFinal(cardId, 'service', servicePick.value);
        } else {
            setFinal(cardId, category, select.value);
        }
    }

    document.querySelectorAll('.top-category').forEach(function (select) {
        select.addEventListener('change', function () { onTopCategoryChange(this, null); });

        // Une demande interne dont le service correspond déjà à une Orientation
        // pré-sélectionne "Service" : révéler tout de suite le bon service choisi.
        if (select.value === 'service' && select.dataset.preselected) {
            onTopCategoryChange(select, select.dataset.preselected);
        }
    });

    document.querySelectorAll('.service-pick').forEach(function (select) {
        select.addEventListener('change', function () { onServicePickChange(this); });
    });

    document.querySelectorAll('.division-pick').forEach(function (select) {
        select.addEventListener('change', function () { onDivisionPickChange(this); });
    });

    document.querySelectorAll('.person-pick').forEach(function (select) {
        select.addEventListener('change', function () {
            var cardId = this.dataset.card;
            var category = document.querySelector('.top-category[data-card="' + cardId + '"]').value;
            setFinal(cardId, category, this.value);
        });
    });
});
</script>
@endsection
