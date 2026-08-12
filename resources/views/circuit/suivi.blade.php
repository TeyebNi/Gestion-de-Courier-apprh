@extends('layouts.master')

@section('title')
Suivi des Demandes
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    Suivi des Demandes
                </h4>
                <form method="GET" action="{{ route('circuit.suivi') }}" class="form-inline">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                        </div>
                        <input type="text" name="search" id="scan_search" class="form-control" placeholder="Scannez le code-barres, ou tapez N°, nom, NNI..." value="{{ $search }}" autofocus autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm ml-2">Rechercher</button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>N°</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Où se trouve la demande ?</th>
                            <th>Décision Maire</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            @forelse($demandes as $d)
                            <tr>
                                <td>{{ $d->id }}</td>
                                <td>{{ $d->nom }} @if($d->piece_jointe)<a href="{{ asset('storage/' . $d->piece_jointe) }}" target="_blank" title="Voir la pièce jointe"><i class="fas fa-paperclip text-info"></i></a>@endif</td>
                                <td>{{ $d->typdm }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($d->statut_circuit) {
                                            'fatou' => 'badge-info',
                                            'maire' => 'badge-warning',
                                            'service' => 'badge-primary',
                                            'cloture' => 'badge-success',
                                            default => 'badge-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $d->statutLabel() }}</span>
                                </td>
                                <td>
                                    @if($d->decision_maire === 'accepte')
                                        <span class="text-success">Acceptée</span>
                                    @elseif($d->decision_maire === 'refuse')
                                        <span class="text-danger">Refusée</span>
                                    @else
                                        <span class="text-muted">En attente</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('circuit.historique', $d) }}" class="btn btn-info btn-sm" title="Voir l'historique complet">
                                        <i class="fas fa-history"></i> Historique
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aucune demande engagée dans le circuit pour le moment.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $demandes->links() }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('scan_search');
    if (input) {
        input.focus();
        input.select();
    }
});
</script>

@endsection
