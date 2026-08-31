@extends('layouts.master')

@section('title')
Historique de la Demande
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    Demande de {{ $tabdepot->nom }}
                </h4>
                <p class="text-muted">Type : {{ $tabdepot->typdm }} — NNI : {{ $tabdepot->nni }}</p>
            </div>
            <div class="card-body">
                <p><strong>Statut actuel :</strong> {{ $tabdepot->statutLabel() }}</p>
                @if($tabdepot->piece_jointe)
                <p>
                    <a href="{{ asset('storage/' . $tabdepot->piece_jointe) }}" target="_blank" class="btn btn-info btn-sm">
                        <i class="fas fa-paperclip"></i> Voir le document original (scan)
                    </a>
                </p>
                @endif

                <h5 class="mt-4">Historique des transferts</h5>
                <ul class="list-group">
                    @forelse($tabdepot->historiques as $h)
                    <li class="list-group-item">
                        <strong>{{ $h->created_at->format('d/m/Y H:i') }}</strong>
                        — {{ $h->de_statut ? \App\Models\Tabdepot::circuitStepLabel($h->de_statut) . ' → ' : '' }}{{ \App\Models\Tabdepot::circuitStepLabel($h->vers_statut) }}
                        @if($h->user)
                            <span class="text-muted">(par {{ $h->user->name }})</span>
                        @endif
                        @if($h->commentaire)
                            <br><small class="text-muted">{{ $h->commentaire }}</small>
                        @endif
                    </li>
                    @empty
                    <li class="list-group-item text-muted">Aucun historique pour le moment.</li>
                    @endforelse
                </ul>

                <a href="{{ route('circuit.suivi') }}" class="btn btn-secondary mt-4">Retour au suivi</a>
            </div>
        </div>
    </div>
</div>

@endsection
