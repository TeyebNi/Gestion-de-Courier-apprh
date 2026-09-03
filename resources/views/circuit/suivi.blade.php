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
                    <select name="statut" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="fatou" @selected($statut === 'fatou')>{{ \App\Models\Tabdepot::circuitStepLabel('fatou') }}</option>
                        <option value="maire" @selected($statut === 'maire')>{{ \App\Models\Tabdepot::circuitStepLabel('maire') }}</option>
                        <option value="service" @selected($statut === 'service')>Chez un service</option>
                        <option value="cloture" @selected($statut === 'cloture')>Clôturée</option>
                    </select>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                        </div>
                        <input type="text" name="search" id="scan_search" class="form-control" placeholder="Scannez le code-barres, ou tapez N°, nom, NNI, objet, référence..." value="{{ $search }}" autofocus autocomplete="off">
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
                            <th>Objet</th>
                            <th>Où se trouve la demande ?</th>
                            <th>Décision Maire</th>
                            <th>Remarque Maire</th>
                            <th>Dernière mise à jour</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            @forelse($demandes as $d)
                            <tr>
                                <td>{{ $d->id }}</td>
                                <td>{{ $d->nom ?: ($d->origine_detail ?: '—') }} @if($d->piece_jointe)<a href="{{ asset('storage/' . $d->piece_jointe) }}" target="_blank" title="Voir la pièce jointe"><i class="fas fa-paperclip text-info"></i></a>@endif</td>
                                <td>{{ $d->typdm ?: '—' }}</td>
                                <td class="text-truncate" style="max-width:220px;" title="{{ $d->objet }}">{{ $d->objet ?: '—' }}</td>
                                <td>
                                    <span class="badge {{ \App\Models\Tabdepot::circuitStepBadgeClass($d->statut_circuit) }}">{{ $d->statutLabel() }}</span>
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
                                <td class="text-truncate" style="max-width:220px;" title="{{ $d->remarque_maire }}">{{ $d->remarque_maire ?: '—' }}</td>
                                <td>{{ $d->updated_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('circuit.historique', $d) }}" class="btn btn-info btn-sm" title="Voir l'historique complet">
                                        <i class="fas fa-history"></i> Historique
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Aucune demande engagée dans le circuit pour le moment.</td>
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
