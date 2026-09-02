@extends('layouts.master')

@section('title')
Historique de la Demande
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    Demande de {{ $tabdepot->nom ?: ($tabdepot->origine_detail ?: '—') }}
                    <span class="badge {{ \App\Models\Tabdepot::circuitStepBadgeClass($tabdepot->statut_circuit) }} ml-2">{{ $tabdepot->statutLabel() }}</span>
                </h4>
                <a href="{{ route('circuit.suivi') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Retour au suivi
                </a>
            </div>
            <div class="card-body">

                <h5 class="mb-3">Détails de la demande</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">N° / Code</small>
                        <strong>{{ $tabdepot->id }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Type de demande</small>
                        <strong>{{ $tabdepot->typdm ?: '—' }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Objet</small>
                        <strong>{{ $tabdepot->objet ?: '—' }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Origine</small>
                        <strong>
                            @if($tabdepot->origine === 'interne')
                                Interne @if($tabdepot->origine_detail) — {{ $tabdepot->origine_detail }} @endif
                            @elseif($tabdepot->origine === 'externe')
                                Externe
                                @if($tabdepot->type_expediteur === 'institution')
                                    — {{ $tabdepot->origine_detail ?: 'Institution' }}
                                @else
                                    — Citoyen
                                @endif
                            @else
                                —
                            @endif
                        </strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">N° référence</small>
                        <strong>{{ $tabdepot->reference ?: '—' }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Date de réception</small>
                        <strong>{{ $tabdepot->daterecpFormatted() }}</strong>
                    </div>
                    @if($tabdepot->nni)
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">NNI</small>
                        <strong>{{ $tabdepot->nni }}</strong>
                    </div>
                    @endif
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Téléphone</small>
                        <strong>{{ $tabdepot->tel ?: '—' }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Adresse</small>
                        <strong>{{ $tabdepot->adresse ?: '—' }}</strong>
                    </div>
                    @if($tabdepot->decision_maire)
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Décision du Maire</small>
                        <strong>
                            @if($tabdepot->decision_maire === 'accepte')
                                <span class="text-success">Acceptée</span>
                            @else
                                <span class="text-danger">Refusée</span>
                            @endif
                        </strong>
                    </div>
                    @endif
                    @if($tabdepot->remarque_maire)
                    <div class="col-md-8 mb-3">
                        <small class="text-muted d-block">Remarque du Maire</small>
                        <strong>{{ $tabdepot->remarque_maire }}</strong>
                    </div>
                    @endif
                    @if($tabdepot->service_assigne)
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Service assigné</small>
                        <strong>{{ $tabdepot->service_assigne }}</strong>
                    </div>
                    @endif
                </div>

                @if($tabdepot->piece_jointe)
                <p>
                    <a href="{{ asset('storage/' . $tabdepot->piece_jointe) }}" target="_blank" class="btn btn-info btn-sm">
                        <i class="fas fa-paperclip"></i> Voir le document original (scan)
                    </a>
                </p>
                @endif

                <hr>

                <h5 class="mt-4 mb-3">Historique des transferts</h5>
                <ul class="list-group timeline-list">
                    @forelse($historiques as $h)
                    <li class="list-group-item d-flex align-items-start">
                        <span class="badge {{ \App\Models\Tabdepot::circuitStepBadgeClass($h->vers_statut) }} timeline-dot" title="{{ \App\Models\Tabdepot::circuitStepLabel($h->vers_statut) }}">
                            <i class="fas fa-circle"></i>
                        </span>
                        <span>
                            <strong>{{ $h->created_at->format('d/m/Y H:i') }}</strong>
                            — {{ $h->de_statut ? \App\Models\Tabdepot::circuitStepLabel($h->de_statut) . ' → ' : '' }}{{ \App\Models\Tabdepot::circuitStepLabel($h->vers_statut) }}
                            @if($h->user)
                                <span class="text-muted">(par {{ $h->user->name }})</span>
                            @endif
                            @if($h->commentaire)
                                <br><small class="text-muted">{{ $h->commentaire }}</small>
                            @endif
                        </span>
                    </li>
                    @empty
                    <li class="list-group-item text-muted">Aucun historique pour le moment.</li>
                    @endforelse
                </ul>
                <div class="d-flex justify-content-center mt-3">
                    {{ $historiques->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline-dot {
        border-radius: 50%;
        width: 12px;
        height: 12px;
        min-width: 12px;
        padding: 0;
        margin-top: 5px;
        margin-right: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .timeline-dot i { font-size: 8px; }
</style>

@endsection
