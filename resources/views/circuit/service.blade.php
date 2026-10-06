@extends('layouts.master')

@section('title')
{{ auth()->user()->specialServiceLabel() ? 'Mes Courriers' : 'Courriers du Service' }}
@endsection

@section('content')

@if(session('success'))
<div id="successOverlay" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; display:flex; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:32px; width:90%; max-width:380px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
        <div style="margin:0 auto 16px; width:64px; height:64px; border-radius:50%; border:3px solid #28a745; display:flex; align-items:center; justify-content:center;">
            <span style="color:#28a745; font-size:32px;">&#10003;</span>
        </div>
        <p style="color:#333; margin-bottom:20px;">{{ session('success') }}</p>
        <button id="closeSuccessService" class="btn btn-info" style="width:100%;">OK</button>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var closeBtn = document.getElementById('closeSuccessService');
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
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    {{ auth()->user()->specialServiceLabel() ? 'Mes Courriers' : 'Courriers du Service' }}
                </h4>
                <form method="GET" action="{{ route('circuit.service.index') }}" class="form-inline">
                    <select name="statut" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="traiter" @selected($statut === 'traiter')>Traitées</option>
                        <option value="classer" @selected($statut === 'classer')>Classées</option>
                        <option value="convoquer" @selected($statut === 'convoquer')>Convoquées</option>
                    </select>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Rechercher par code, objet, téléphone..." value="{{ $search }}" style="min-width:220px;">
                    <button type="submit" class="btn btn-primary btn-sm ml-2">Rechercher</button>
                    @if($search || $statut)
                    <a href="{{ route('circuit.service.index') }}" class="btn btn-outline-secondary btn-sm ml-2" title="Réinitialiser">&times;</a>
                    @endif
                </form>
                <a href="{{ route('circuit.service.export', ['search' => $search, 'statut' => $statut]) }}" class="btn btn-success btn-sm ml-2" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
            </div>
        </div>
    </div>
</div>

{{-- Une résolution (Traitée/Classée/Convoquée) ne concerne que les courriers
     déjà traités ; les courriers en cours n'en ont pas encore et n'y
     répondent jamais. On ne montre donc ce tableau que quand aucune
     résolution n'est choisie (la recherche texte, elle, s'y applique et
     reste visible). --}}
@if(! $statut)
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    {{ auth()->user()->specialServiceLabel() ? 'Courriers qui vous sont orientés' : 'Courriers orientés vers votre service' }}
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>Code</th>
                            <th>Origine</th>
                            <th>Objet</th>
                            <th>Nom</th>
                            <th>Téléphone</th>
                            <th>Annotations du Maire</th>
                            <th>Reçue le</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            @forelse($demandes as $d)
                            <tr>
                                <td>{{ $d->reference ?: '—' }}</td>
                                <td>@include('partials.origine-badge', ['demande' => $d])</td>
                                <td class="text-truncate" style="max-width:220px;" title="{{ $d->objet }}">{{ $d->objet ?: '—' }}</td>
                                <td class="text-truncate" style="max-width:160px;" title="{{ $d->nom }}">{{ $d->nom ?: '—' }}</td>
                                <td>{{ $d->tel ?: '—' }}</td>
                                <td class="text-truncate" style="max-width:220px;" title="{{ $d->remarque_maire }}">{{ $d->remarque_maire ?: '—' }}</td>
                                <td>{{ $d->updated_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($d->piece_jointe)
                                    <a href="{{ asset('storage/' . $d->piece_jointe) }}" target="_blank" class="btn btn-secondary btn-sm" title="Voir la pièce jointe"><i class="fas fa-paperclip"></i></a>
                                    @endif
                                    <button type="button" class="btn btn-success btn-sm" title="Traiter" data-toggle="modal" data-target="#resolutionModal"
                                        data-id="{{ $d->id }}" data-resolution="traiter" data-btn-class="btn-success"
                                        data-title="Traiter le courrier" data-body="Voulez-vous vraiment marquer le courrier {{ $d->reference ?: '#' . $d->id }} comme traité ?" data-confirm-label="Oui, traiter">
                                        <i class="fas fa-check"></i> Traiter
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" title="Classer" data-toggle="modal" data-target="#resolutionModal"
                                        data-id="{{ $d->id }}" data-resolution="classer" data-btn-class="btn-secondary"
                                        data-title="Classer le courrier" data-body="Voulez-vous vraiment classer le courrier {{ $d->reference ?: '#' . $d->id }} sans traitement particulier ?" data-confirm-label="Oui, classer">
                                        <i class="fas fa-folder"></i> Classer
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" title="Convoquer" data-toggle="modal" data-target="#resolutionModal"
                                        data-id="{{ $d->id }}" data-resolution="convoquer" data-btn-class="btn-warning"
                                        data-title="Convoquer le demandeur" data-body="Convoquer le demandeur pour le courrier {{ $d->reference ?: '#' . $d->id }} ?" data-confirm-label="Oui, convoquer">
                                        <i class="fas fa-phone"></i> Convoquer
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Aucun courrier pour votre service.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $demandes->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    Courriers traités
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>Code</th>
                            <th>Origine</th>
                            <th>Objet</th>
                            <th>Nom</th>
                            <th>Téléphone</th>
                            <th>Résolution</th>
                            <th>Traitée le</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            @forelse($demandesTraitees as $d)
                            <tr>
                                <td>{{ $d->reference ?: '—' }}</td>
                                <td>@include('partials.origine-badge', ['demande' => $d])</td>
                                <td class="text-truncate" style="max-width:220px;" title="{{ $d->objet }}">{{ $d->objet ?: '—' }}</td>
                                <td class="text-truncate" style="max-width:160px;" title="{{ $d->nom }}">{{ $d->nom ?: '—' }}</td>
                                <td>{{ $d->tel ?: '—' }}</td>
                                <td>
                                    @if($d->resolution_service === 'traiter')
                                        <span class="badge badge-success">Traitée</span>
                                    @elseif($d->resolution_service === 'classer')
                                        <span class="badge badge-secondary">Classée</span>
                                    @elseif($d->resolution_service === 'convoquer')
                                        <span class="badge badge-warning">Convoquée</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $d->updated_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($d->piece_jointe)
                                    <a href="{{ asset('storage/' . $d->piece_jointe) }}" target="_blank" class="btn btn-secondary btn-sm" title="Voir la pièce jointe"><i class="fas fa-paperclip"></i></a>
                                    @endif
                                    <a href="{{ route('circuit.historique', $d) }}" class="btn btn-info btn-sm" title="Voir l'historique complet">
                                        <i class="fas fa-history"></i> Historique
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Aucun courrier traité pour le moment.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $demandesTraitees->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Traiter/Classer/Convoquer -->
<div class="modal fade" id="resolutionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-notify modal-lg modal-right modal-success" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resolutionModalTitle">Confirmer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="resolutionForm" method="POST" action="">
                @csrf
                <input type="hidden" name="resolution" id="resolutionValue" value="">
                <div class="modal-body">
                    <p id="resolutionModalBody"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal" title="Annuler"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
                    <button type="submit" class="btn btn-success" id="resolutionModalConfirm" title="Confirmer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$('#resolutionModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#resolutionForm').attr('action', '{{ url('/circuit') }}/' + id + '/cloturer');
    $('#resolutionValue').val(button.data('resolution'));
    $('#resolutionModalTitle').text(button.data('title'));
    $('#resolutionModalBody').text(button.data('body'));
    var confirmBtn = document.getElementById('resolutionModalConfirm');
    confirmBtn.textContent = button.data('confirm-label');
    confirmBtn.className = 'btn ' + button.data('btn-class');
});
</script>
@endsection
