@extends('layouts.master')

@section('title')
Dashboard Courier
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    Gestion des Demandes
                    <button class="btn btn-primary btn-sm ml-2" data-toggle="modal" data-target="#exampleModal">Nouvelle Demande</button>
                </h4>
                <a href="{{ route('depot.export') }}" class="btn btn-success btn-sm" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table">
                        <thead class=" text-primary">
                           <th>N°</th>
                            <th>Code</th>
                            <th>Type de Demande</th>
                            <th>Objet</th>
                            <th>Origine</th>
                            <th>Nom</th>
                            <th class="text-right">NNI</th>
                            <th class="text-right">Tel</th>
                             <th>Statut</th>
                            <th class="text-right">Date</th>
                            <th class="text-right">Action</th>
                        </thead>
                        <tbody>
                          @foreach($tabdepot as $key=>$item)
                            @php
                                $depotBadgeClass = \App\Models\Tabdepot::circuitStepBadgeClass($item->statut_circuit ?? 'accueil');
                            @endphp
                            <tr>
                                <td>{{++$key}}</td>
                                <td>{{$item->id}}</td>
                                <td>{{$item->typdm}}</td>
                                <td>{{ $item->objet ?: '—' }}</td>
                                <td>
                                    @if($item->origine === 'interne')
                                        <span class="badge badge-info">Interne</span>
                                        @if($item->origine_detail)
                                            <br><small class="text-muted">{{ $item->origine_detail }}</small>
                                        @endif
                                    @elseif($item->origine === 'externe')
                                        <span class="badge badge-secondary">Externe</span>
                                        @if($item->origine_detail)
                                            <br><small class="text-muted">{{ $item->origine_detail }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-right">{{ $item->nom ?: ($item->origine_detail ?: '—') }}</td>
                                  <td class="text-right">{{ $item->nni ?: '—' }}</td>
                                <td class="text-right">{{ $item->tel ?: '—' }}</td>
                                  <td><span class="badge {{ $depotBadgeClass }}">{{ $item->statutLabel() }}</span></td>
                                  <td class="text-right">{{$item->daterecp}}</td>
                                <td class="text-right">
                                    <a href="{{ route('depot.print_reçu', $item->id) }}" target="_blank" class="btn btn-success btn-sm" title="Imprimer"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg></a>
                                    @if($item->piece_jointe)
                                    <a href="{{ asset('storage/' . $item->piece_jointe) }}" target="_blank" class="btn btn-outline-info btn-sm" title="Voir la pièce jointe"><i class="fas fa-paperclip"></i></a>
                                    @endif
                                    @if(($item->statut_circuit ?? 'accueil') === 'accueil')
                                    <form action="{{ route('circuit.envoyer-fatou', $item) }}" method="post" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-info btn-sm" title="Envoyer au Cabinet">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                    @else
                                    <a href="{{ route('circuit.historique', $item) }}" class="btn btn-secondary btn-sm" title="{{ $item->statutLabel() }}">
                                        <i class="fas fa-route"></i>
                                    </a>
                                    @endif
                                    <a data-id="{{$item->id}}" data-typdm="{{$item->typdm}}" data-objet="{{$item->objet}}" data-reference="{{$item->reference}}" data-origine="{{$item->origine}}" data-origine-detail="{{$item->origine_detail}}" data-type-expediteur="{{$item->type_expediteur}}" data-piece-jointe="{{ $item->piece_jointe ? asset('storage/' . $item->piece_jointe) : '' }}" data-nom="{{$item->nom}}" data-nni="{{$item->nni}}" data-adresse="{{$item->adresse}}" data-tel="{{$item->tel}}" data-daterecp="{{$item->daterecp}}" data-toggle="modal" data-target="#exampleModal-edit" type="button" class="btn btn-info btn-sm" title="Modifier"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></a>
                                    @if(($item->statut_circuit ?? 'accueil') === 'accueil')
                                    <a data-id="{{$item->id}}" data-nom="{{$item->nom}}" data-toggle="modal" data-target="#exampleModal-delete" type="button" class="btn btn-danger btn-sm" title="Supprimer"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg></a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $tabdepot->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

@endsection


@section('scripts')

<!-- Modal Ajout -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog  modal-notify modal-lg modal-right modal-success" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Ajouter une Demande</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
      </div>
      <div class="modal-body">
         <form action="{{route('depot.store')}}" method="post" enctype="multipart/form-data">
        @csrf
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Origine *</span>
      </div>
     <select id="create_origine" class="form-control" name="origine" onchange="toggleOrigineDetail(this, 'create')" required>
      <option value="">Sélectionner l'origine</option>
      <option value="interne">Interne (note entre services de la commune)</option>
      <option value="externe">Externe (citoyen, institution, organisme...)</option>
    </select>
      </div>
      <small class="form-text text-muted" style="margin: -8px 0 10px 5px;">
          "Interne" = uniquement une note d'un service municipal vers un autre. Toute demande ou réclamation
          venant d'un citoyen (même liée à un service interne comme les impôts) est "Externe → Citoyen".
      </small>
      <div class="input-group" id="create_origine_interne_wrap" style="display:none;">
        <div class="input-group-prepend">
        <span class="input-group-text">Service</span>
      </div>
     <select class="form-control" name="origine_detail" id="create_origine_detail_select" disabled>
      <option value="">Sélectionner le service</option>
      @foreach($orientations as $o)
        <option value="{{ $o->name }}">{{ $o->name }}</option>
      @endforeach
    </select>
      </div>
      <div class="input-group" id="create_type_expediteur_wrap" style="display:none;">
        <div class="input-group-prepend">
        <span class="input-group-text">Type d'expéditeur *</span>
      </div>
     <select class="form-control" name="type_expediteur" id="create_type_expediteur" disabled onchange="toggleNniRequirement('create')">
      <option value="">Sélectionner le type d'expéditeur</option>
      <option value="personne">Citoyen (personne physique)</option>
      <option value="institution">Institution / Organisme (administration, entreprise, association, école...)</option>
    </select>
      </div>
      <br>
      <div class="input-group" id="create_origine_externe_wrap" style="display:none;">
        <div class="input-group-prepend">
        <span class="input-group-text">Nom de l'institution *</span>
      </div>
      <input type="text" class="form-control" name="origine_detail" id="create_origine_detail_text" placeholder="Ex: Ministère de l'Intérieur" disabled>
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text" id="create_typdm_label">Type de Demande</span>
      </div>
     <select   class="form-control" name="typdm" id="create_typdm">
      <option value="">Sélectionner le type de demande</option>
      @foreach($typedem as $c)
        <option value="{{$c->name}}">{{$c->name}}</option>
    @endforeach
    </select>
    </div>
     <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Objet</span>
      </div>
      <input type="text" class="form-control" name="objet" placeholder="Résumé de la demande (ex: Demande de raccordement eau)" maxlength="255">
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">N° référence</span>
      </div>
      <input type="text" class="form-control" name="reference" placeholder="Référence du courrier de l'expéditeur (optionnel)" maxlength="100">
    </div>
      <br>
      <div class="input-group" id="create_nni_wrap">
        <div class="input-group-prepend">
        <span class="input-group-text" id="create_nni_label">NNI</span>
      </div>
      <input type="text" class="form-control" name="nni" id="create_nni" placeholder="Entrer NNI" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)" pattern="[0-9]{10}" minlength="10" maxlength="10" inputmode="numeric">
    </div>
      <br>
      <div class="input-group" id="create_nom_wrap">
        <div class="input-group-prepend">
        <span class="input-group-text" id="create_nom_label">Nom *</span>
      </div>
      <input type="text" class="form-control" name="nom" id="create_nom" placeholder="Entrer Nom" oninput="this.value=this.value.replace(/[0-9]/g,'')" required>
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text" id="create_tel_label">Tel *</span>
      </div>
      <input type="text" class="form-control" name="tel" id="create_tel" placeholder="Entrer Tel" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,8)" required pattern="[0-9]{8}" minlength="8" maxlength="8" inputmode="numeric">
    </div>
     <br>
     <div class="input-group" id="create_adresse_wrap">
        <div class="input-group-prepend">
        <span class="input-group-text">Adresse (optionnel)</span>
      </div>
      <input type="text" class="form-control" name="adresse" id="create_adresse" placeholder="Entrer Adresse">
    </div>
     <br>
   <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Date</span>
      </div>
      <input type="date" class="form-control" name="daterecp" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
    </div>
     <br>
   <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Pièce jointe</span>
      </div>
      <div class="custom-file">
        <input type="file" class="custom-file-input" name="piece_jointe" id="create_piece_jointe" accept=".pdf,.jpg,.jpeg,.png">
        <label class="custom-file-label" for="create_piece_jointe">Scan / photo du document (PDF, JPG, PNG — max 10 Mo)</label>
      </div>
    </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-warning" data-dismiss="modal" title="Fermer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
        <button type="submit"  class="btn btn-success" title="Ajouter"><i class="now-ui-icons ui-1_check"></i></button>
      </div>
      

<!-- BEGIN DEPOT VALIDATION FR -->

<style>
    .depot-error-message {
        display: none;
        color: #dc3545;
        font-size: 13px;
        font-weight: 600;
        margin: 5px 15px 8px 15px;
    }

    .depot-error-message.visible {
        display: block;
    }

    .depot-invalid {
        border: 1px solid #dc3545 !important;
    }

    .depot-valid {
        border: 1px solid #28a745 !important;
    }
</style>

<script>
function toggleOrigineDetail(selectEl, prefix) {
    var value = selectEl.value;
    var interneWrap = document.getElementById(prefix + '_origine_interne_wrap');
    var interneSelect = document.getElementById(prefix + '_origine_detail_select');
    var typeExpediteurWrap = document.getElementById(prefix + '_type_expediteur_wrap');
    var typeExpediteurSelect = document.getElementById(prefix + '_type_expediteur');
    var nniWrap = document.getElementById(prefix + '_nni_wrap');
    var nniInput = document.getElementById(prefix + '_nni');
    var adresseWrap = document.getElementById(prefix + '_adresse_wrap');
    var adresseInput = document.getElementById(prefix + '_adresse');
    var isInterne = value === 'interne';
    var isExterne = value === 'externe';

    // NNI et adresse ne concernent qu'un expéditeur externe (citoyen/institution) :
    // une demande interne entre agents municipaux n'en a pas besoin.
    if (nniWrap) {
        nniWrap.style.display = isInterne ? 'none' : '';
        if (nniInput) {
            nniInput.disabled = isInterne;
            if (isInterne) { nniInput.value = ''; }
        }
    }
    if (adresseWrap) {
        adresseWrap.style.display = isInterne ? 'none' : '';
        if (adresseInput) {
            adresseInput.disabled = isInterne;
            if (isInterne) { adresseInput.value = ''; }
        }
    }

    interneWrap.style.display = isInterne ? '' : 'none';
    interneSelect.disabled = !isInterne;
    if (!isInterne) { interneSelect.value = ''; }

    if (typeExpediteurWrap) {
        typeExpediteurWrap.style.display = isExterne ? '' : 'none';
        typeExpediteurSelect.disabled = !isExterne;
        // On force un choix explicite à chaque changement d'origine : pas de
        // valeur par défaut silencieuse qui ferait croire que "Citoyen" est
        // déjà sélectionné.
        typeExpediteurSelect.value = '';
    }

    toggleNniRequirement(prefix);

    var typdmLabel = document.getElementById(prefix + '_typdm_label');
    if (typdmLabel) {
        typdmLabel.textContent = isInterne ? 'Type de Demande *' : 'Type de Demande';
    }
}

function toggleNniRequirement(prefix) {
    var typeExpediteurSelect = document.getElementById(prefix + '_type_expediteur');
    var nniInput = document.getElementById(prefix + '_nni');
    var nniLabel = document.getElementById(prefix + '_nni_label');
    var nomWrap = document.getElementById(prefix + '_nom_wrap');
    var nomInput = document.getElementById(prefix + '_nom');
    var telInput = document.getElementById(prefix + '_tel');
    var telLabel = document.getElementById(prefix + '_tel_label');
    var institutionWrap = document.getElementById(prefix + '_origine_externe_wrap');
    var institutionInput = document.getElementById(prefix + '_origine_detail_text');
    if (!nniInput) { return; }

    var isInstitution = typeExpediteurSelect && !typeExpediteurSelect.disabled && typeExpediteurSelect.value === 'institution';

    nniInput.removeAttribute('required');

    if (institutionWrap) {
        institutionWrap.style.display = isInstitution ? '' : 'none';
        if (institutionInput) {
            institutionInput.disabled = !isInstitution;
            if (!isInstitution) { institutionInput.value = ''; }
        }
    }

    if (isInstitution) {
        nniInput.value = '';
        if (nniLabel) { nniLabel.textContent = 'NNI (non applicable)'; }
        if (nomWrap) { nomWrap.style.display = 'none'; }
        if (nomInput) { nomInput.disabled = true; nomInput.value = ''; }
        if (telInput) { telInput.removeAttribute('required'); }
        if (telLabel) { telLabel.textContent = 'Tel (optionnel)'; }
    } else {
        if (nniLabel) { nniLabel.textContent = 'NNI (optionnel)'; }
        if (nomWrap) { nomWrap.style.display = ''; }
        if (nomInput) { nomInput.disabled = false; }
        if (telInput) { telInput.setAttribute('required', 'required'); }
        if (telLabel) { telLabel.textContent = 'Tel *'; }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.custom-file-input').forEach(function (input) {
        input.addEventListener('change', function () {
            var label = input.nextElementSibling;
            if (label && input.files && input.files.length > 0) {
                label.textContent = input.files[0].name;
            }
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const allForms = Array.from(document.querySelectorAll('form'));

    const form = allForms.find(function (currentForm) {
        return currentForm.querySelector('[name="typdm"]') &&
               currentForm.querySelector('[name="nni"]') &&
               currentForm.querySelector('[name="nom"]') &&
               currentForm.querySelector('[name="tel"]') &&
               currentForm.querySelector('[name="adresse"]') &&
               currentForm.querySelector('[name="daterecp"]');
    });

    if (!form) {
        console.error('Formulaire Ajouter une Demande introuvable.');
        return;
    }

    /*
     * Désactiver les messages natifs anglais du navigateur.
     */
    form.setAttribute('novalidate', 'novalidate');

    const fields = [
        {
            element: form.querySelector('[name="typdm"]'),
            emptyMessage: 'Veuillez sélectionner un type de demande.',
            isValid: function (value) {
                var origineEl = document.getElementById('create_origine');
                var isInterne = origineEl && origineEl.value === 'interne';
                if (isInterne) {
                    return value !== '';
                }
                return true;
            }
        },
        {
            element: document.getElementById('create_origine_detail_text'),
            emptyMessage: "Veuillez saisir le nom de l'institution.",
            isValid: function (value) {
                var typeExpediteurEl = document.getElementById('create_type_expediteur');
                var isInstitution = typeExpediteurEl && !typeExpediteurEl.disabled && typeExpediteurEl.value === 'institution';
                if (!isInstitution) {
                    return true;
                }
                return value.trim() !== '';
            }
        },
        {
            element: form.querySelector('[name="nni"]'),
            emptyMessage: 'Veuillez saisir le NNI.',
            invalidMessage: 'Le NNI doit contenir exactement 10 chiffres.',
            numeric: true,
            maxLength: 10,
            isValid: function (value) {
                if (value === '') {
                    return true;
                }
                return /^\d{10}$/.test(value);
            }
        },
        {
            element: form.querySelector('[name="nom"]'),
            emptyMessage: 'Veuillez saisir le nom et le prénom.',
            invalidMessage: 'Le nom et le prénom sont obligatoires.',
            isValid: function (value) {
                var typeExpediteurEl = document.getElementById('create_type_expediteur');
                var isInstitution = typeExpediteurEl && !typeExpediteurEl.disabled && typeExpediteurEl.value === 'institution';
                if (isInstitution) {
                    return true;
                }
                return value.length >= 2;
            }
        },
        {
            element: form.querySelector('[name="piece_jointe"]'),
            emptyMessage: 'Veuillez joindre le document (obligatoire pour une institution).',
            isValid: function () {
                var typeExpediteurEl = document.getElementById('create_type_expediteur');
                var isInstitution = typeExpediteurEl && !typeExpediteurEl.disabled && typeExpediteurEl.value === 'institution';
                if (!isInstitution) {
                    return true;
                }
                var fileInput = document.getElementById('create_piece_jointe');
                return fileInput && fileInput.files && fileInput.files.length > 0;
            }
        },
        {
            element: form.querySelector('[name="tel"]'),
            emptyMessage: 'Veuillez saisir le numéro de téléphone.',
            invalidMessage:
                'Le numéro de téléphone doit contenir exactement 8 chiffres.',
            numeric: true,
            maxLength: 8,
            isValid: function (value) {
                var typeExpediteurEl = document.getElementById('create_type_expediteur');
                var isInstitution = typeExpediteurEl && !typeExpediteurEl.disabled && typeExpediteurEl.value === 'institution';
                if (isInstitution) {
                    return value === '' || /^\d{8}$/.test(value);
                }
                return /^\d{8}$/.test(value);
            }
        },
        {
            element: form.querySelector('[name="daterecp"]'),
            emptyMessage: 'Veuillez sélectionner une date.',
            invalidMessage: 'La date est obligatoire.',
            isValid: function (value) {
                return value !== '';
            }
        }
    ];

    fields.forEach(function (field) {
        /*
         * Supprimer les contraintes HTML qui produisent
         * les messages anglais du navigateur.
         */
        field.element.removeAttribute('required');
        field.element.removeAttribute('pattern');
        field.element.removeAttribute('minlength');

        if (field.numeric) {
            field.element.setAttribute('inputmode', 'numeric');
            field.element.setAttribute(
                'maxlength',
                String(field.maxLength)
            );
        }

        const message = document.createElement('div');
        message.className = 'depot-error-message';

        field.element.insertAdjacentElement('afterend', message);
        field.messageElement = message;
    });

    function valueOf(field) {
        return String(field.element.value || '').trim();
    }

    function errorMessage(field) {
        if (valueOf(field) === '') {
            return field.emptyMessage;
        }

        return field.invalidMessage ||
            'Veuillez renseigner correctement ce champ.';
    }

    function clearError(field) {
        field.element.classList.remove('depot-invalid');
        field.messageElement.classList.remove('visible');
        field.messageElement.textContent = '';
    }

    function showError(field) {
        field.element.classList.remove('depot-valid');
        field.element.classList.add('depot-invalid');

        field.messageElement.textContent = errorMessage(field);
        field.messageElement.classList.add('visible');
    }

    function validateField(field, displayMessage) {
        const valid = field.isValid(valueOf(field));

        if (valid) {
            clearError(field);
            field.element.classList.add('depot-valid');
        } else {
            field.element.classList.remove('depot-valid');

            if (displayMessage) {
                showError(field);
            }
        }

        return valid;
    }

    function firstInvalidBefore(index) {
        for (let i = 0; i < index; i++) {
            if (!validateField(fields[i], true)) {
                return fields[i];
            }
        }

        return null;
    }

    /*
     * Empêcher le passage à un champ suivant avant
     * la validation des champs précédents.
     */
    fields.forEach(function (field, index) {
        const element = field.element;

        element.addEventListener('mousedown', function (event) {
            const previousInvalid = firstInvalidBefore(index);

            if (previousInvalid) {
                event.preventDefault();
                previousInvalid.element.focus();
            }
        });

        element.addEventListener('focus', function () {
            const previousInvalid = firstInvalidBefore(index);

            if (previousInvalid) {
                previousInvalid.element.focus();
            }
        });

        element.addEventListener('input', function () {
            if (field.numeric) {
                this.value = this.value
                    .replace(/\D/g, '')
                    .slice(0, field.maxLength);
            }

            clearError(field);

            if (valueOf(field) !== '') {
                validateField(field, false);
            }
        });

        element.addEventListener('change', function () {
            validateField(field, true);
        });

        element.addEventListener('blur', function () {
            validateField(field, true);
        });

        element.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();

            if (!validateField(field, true)) {
                element.focus();
                return;
            }

            if (fields[index + 1]) {
                fields[index + 1].element.focus();
            }
        });
    });

    /*
     * Bloquer l’enregistrement si un champ est vide
     * ou incorrect.
     */
    form.addEventListener('submit', function (event) {
        let firstInvalid = null;

        fields.forEach(function (field) {
            if (!validateField(field, true) && !firstInvalid) {
                firstInvalid = field;
            }
        });

        if (firstInvalid) {
            event.preventDefault();
            event.stopImmediatePropagation();

            firstInvalid.element.focus();
            firstInvalid.element.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            return false;
        }
    }, true);
});
</script>

<!-- END DEPOT VALIDATION FR -->

</form>
    </div>
  </div>
</div>

<!-- Modal Modifier -->
<div class="modal fade" id="exampleModal-edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog  modal-notify modal-lg modal-right modal-success" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modifier la Demande</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editDepotForm" action="" method="post" enctype="multipart/form-data">
       @csrf
        @method('PUT')
        <div class="modal-body">
        <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Origine</span>
      </div>
     <select id="edit_origine" class="form-control" name="origine" onchange="toggleOrigineDetail(this, 'edit')">
      <option value="">Sélectionner l'origine</option>
      <option value="interne">Interne (note entre services de la commune)</option>
      <option value="externe">Externe (citoyen, institution, organisme...)</option>
    </select>
      </div>
      <small class="form-text text-muted" style="margin: -8px 0 10px 5px;">
          "Interne" = uniquement une note d'un service municipal vers un autre. Toute demande ou réclamation
          venant d'un citoyen (même liée à un service interne comme les impôts) est "Externe → Citoyen".
      </small>
      <div class="input-group" id="edit_origine_interne_wrap" style="display:none;">
        <div class="input-group-prepend">
        <span class="input-group-text">Service</span>
      </div>
     <select class="form-control" name="origine_detail" id="edit_origine_detail_select" disabled>
      <option value="">Sélectionner le service</option>
      @foreach($orientations as $o)
        <option value="{{ $o->name }}">{{ $o->name }}</option>
      @endforeach
    </select>
      </div>
      <div class="input-group" id="edit_type_expediteur_wrap" style="display:none;">
        <div class="input-group-prepend">
        <span class="input-group-text">Type d'expéditeur *</span>
      </div>
     <select class="form-control" name="type_expediteur" id="edit_type_expediteur" disabled onchange="toggleNniRequirement('edit')">
      <option value="">Sélectionner le type d'expéditeur</option>
      <option value="personne">Citoyen (personne physique)</option>
      <option value="institution">Institution / Organisme (administration, entreprise, association, école...)</option>
    </select>
      </div>
      <br>
      <div class="input-group" id="edit_origine_externe_wrap" style="display:none;">
        <div class="input-group-prepend">
        <span class="input-group-text">Nom de l'institution *</span>
      </div>
      <input type="text" class="form-control" name="origine_detail" id="edit_origine_detail_text" placeholder="Ex: Ministère de l'Intérieur" disabled>
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text" id="edit_typdm_label">Type de Demande</span>
      </div>
     <select id="edit_typdm" class="form-control" name="typdm">
      <option value="">Sélectionner le type de demande</option>
      @foreach($typedem as $c)
        <option value="{{$c->name}}">{{$c->name}}</option>
    @endforeach
    </select>
      </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Objet</span>
      </div>
      <input id="edit_objet" type="text" class="form-control" name="objet" placeholder="Résumé de la demande" maxlength="255">
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">N° référence</span>
      </div>
      <input id="edit_reference" type="text" class="form-control" name="reference" placeholder="Référence du courrier de l'expéditeur (optionnel)" maxlength="100">
    </div>
      <br>
      <div class="input-group" id="edit_nni_wrap">
        <div class="input-group-prepend">
        <span class="input-group-text" id="edit_nni_label">NNI</span>
      </div>
      <input id="edit_nni" type="text" class="form-control" name="nni" placeholder="Entrer NNI" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)">
    </div>
      <br>
      <div class="input-group" id="edit_nom_wrap">
        <div class="input-group-prepend">
        <span class="input-group-text" id="edit_nom_label">Nom *</span>
      </div>
      <input id="edit_nom" type="text" class="form-control" name="nom" placeholder="Entrer Nom" oninput="this.value=this.value.replace(/[0-9]/g,'')">
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text" id="edit_tel_label">Tel *</span>
      </div>
      <input id="edit_tel" type="text" class="form-control" name="tel" placeholder="Entrer Tel" maxlength="8" inputmode="numeric" pattern="[0-9]{8}" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,8)" required>
    </div>
     <br>
     <div class="input-group" id="edit_adresse_wrap">
        <div class="input-group-prepend">
        <span class="input-group-text">Adresse (optionnel)</span>
      </div>
      <input id="edit_adresse" type="text" class="form-control" name="adresse" placeholder="Entrer Adresse">
    </div>
     <br>
   <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Date</span>
      </div>
      <input id="edit_daterecp" type="date" class="form-control" name="daterecp" max="{{ date('Y-m-d') }}">
    </div>
     <br>
   <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Pièce jointe</span>
      </div>
      <div class="custom-file">
        <input type="file" class="custom-file-input" name="piece_jointe" id="edit_piece_jointe" accept=".pdf,.jpg,.jpeg,.png">
        <label class="custom-file-label" for="edit_piece_jointe">Remplacer le scan / photo (optionnel)</label>
      </div>
    </div>
    <div id="edit_piece_jointe_current" class="mt-2"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-warning" data-dismiss="modal" title="Fermer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
          <button type="submit" class="btn btn-success" title="Modifier"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Supprimer -->
<div class="modal fade left " id="exampleModal-delete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog notifi modal-lg modal-right modal-danger" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Supprimer la Demande</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="deleteDepotForm" action="" method="post">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Voulez-vous vraiment supprimer la demande de <strong id="delete_depot_nom"></strong> ?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-warning" data-dismiss="modal" title="Annuler"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
          <button type="submit" class="btn btn-success" title="Oui, Supprimer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></button>
        </div>
      </form>
    </div>
  </div>
</div>

@if (session('success'))
<div id="successOverlay" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; display:flex; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:32px; width:90%; max-width:380px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
        <div style="margin:0 auto 16px; width:64px; height:64px; border-radius:50%; border:3px solid #28a745; display:flex; align-items:center; justify-content:center;">
            <span style="color:#28a745; font-size:32px;">&#10003;</span>
        </div>
        <p style="color:#333; margin-bottom:20px;">{{ session('success') }}</p>
        <button id="closeSuccessDepot" class="btn btn-info" style="width:100%;">OK</button>
    </div>
</div>
@endif

<script>
$('#exampleModal-edit').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#editDepotForm').attr('action', '{{ url('/depot') }}/' + id);
    $('#edit_typdm').val(button.data('typdm'));
    $('#edit_objet').val(button.data('objet'));
    $('#edit_reference').val(button.data('reference'));
    $('#edit_origine').val(button.data('origine'));
    toggleOrigineDetail(document.getElementById('edit_origine'), 'edit');
    var origineDetail = button.data('origine-detail');
    if (button.data('origine') === 'interne') {
        $('#edit_origine_detail_select').val(origineDetail);
    } else if (button.data('origine') === 'externe') {
        $('#edit_origine_detail_text').val(origineDetail);
    }
    if (button.data('origine') === 'externe') {
        $('#edit_type_expediteur').val(button.data('type-expediteur') || 'personne');
    }
    toggleNniRequirement('edit');
    $('#edit_nni').val(button.data('nni'));
    $('#edit_nom').val(button.data('nom'));
    $('#edit_tel').val(button.data('tel'));
    $('#edit_adresse').val(button.data('adresse'));
    $('#edit_daterecp').val(button.data('daterecp'));
    var pieceJointe = button.data('piece-jointe');
    if (pieceJointe) {
        $('#edit_piece_jointe_current').html('<a href="' + pieceJointe + '" target="_blank" class="btn btn-outline-info btn-sm"><i class="fas fa-paperclip"></i> Voir le document actuel</a>');
    } else {
        $('#edit_piece_jointe_current').html('<small class="text-muted">Aucune pièce jointe actuellement.</small>');
    }
});

$('#exampleModal-delete').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#deleteDepotForm').attr('action', '{{ url('/depot') }}/' + id);
    $('#delete_depot_nom').text(button.data('nom'));
});

var closeBtnDepot = document.getElementById('closeSuccessDepot');
if (closeBtnDepot) {
    closeBtnDepot.addEventListener('click', function () {
        document.getElementById('successOverlay').style.display = 'none';
    });
}
</script>
@endsection


<!-- BEGIN EXCEL BUTTON POSITION -->
<style>
    /*
     * نفس السطر لزر الإضافة وأيقونة Excel
     */
    .page-actions-aligned {
        width: 100%;
        display: flex !important;
        align-items: center !important;
        gap: 10px;
        min-height: 42px;
    }

    /*
     * دفع أيقونة Excel إلى أقصى اليمين
     */
    .page-actions-aligned .excel-export-toolbar {
        margin: 0 0 0 auto !important;
        padding: 0 !important;
        width: auto !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
    }

    .page-actions-aligned .excel-export-button {
        margin: 0 !important;
        float: none !important;
        position: static !important;
    }

    /*
     * منع وجود مساحة كبيرة بين الأزرار والجدول
     */
    .excel-export-toolbar {
        margin-top: 0 !important;
        margin-bottom: 12px !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toolbar = document.querySelector('.excel-export-toolbar');

    if (!toolbar) {
        return;
    }

    const excelButton = toolbar.querySelector(
        '.excel-export-button, a, button'
    );

    if (!excelButton) {
        return;
    }

    /*
     * البحث عن زر الإضافة الموجود أعلى كل صفحة:
     * Nouvelle Demande
     * Nouvelle Affectation
     * Nouvelle Orientation
     * Nouveau Type de Demande
     */
    const buttons = Array.from(
        document.querySelectorAll('a, button')
    );

    const actionButton = buttons.find(function (element) {
        if (element === excelButton) {
            return false;
        }

        const text = String(
            element.textContent || ''
        ).trim().toLowerCase();

        return (
            text.includes('nouveau') ||
            text.includes('nouvelle') ||
            text.includes('ajouter')
        );
    });

    if (!actionButton) {
        /*
         * في الصفحات التي لا تحتوي على زر Ajouter،
         * وضع Excel في أعلى اليمين داخل البطاقة.
         */
        const card =
            toolbar.closest('.card-body') ||
            toolbar.closest('.card') ||
            document.querySelector('.card-body') ||
            document.querySelector('.card');

        if (card) {
            card.style.position = 'relative';
            toolbar.style.display = 'flex';
            toolbar.style.justifyContent = 'flex-end';
            toolbar.style.marginTop = '0';
        }

        return;
    }

    /*
     * استعمال الحاوية الأصلية التي يوجد فيها زر Nouveau/Nouvelle،
     * حتى يبقى النص الموجود بجانبه في نفس السطر.
     */
    const actionContainer = actionButton.parentElement;

    if (!actionContainer) {
        return;
    }

    actionContainer.classList.add('page-actions-aligned');

    /*
     * نقل شريط Excel إلى نفس حاوية زر الإضافة.
     */
    actionContainer.appendChild(toolbar);

    toolbar.style.display = 'flex';
    toolbar.style.marginLeft = 'auto';
});
</script>
<!-- END EXCEL BUTTON POSITION -->

