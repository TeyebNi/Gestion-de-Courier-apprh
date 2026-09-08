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
                        <option value="service" @selected($statut === 'service')>Chez un service</option>
                        <option value="cloture" @selected($statut === 'cloture')>Clôturée</option>
                    </select>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                        </div>
                        <input type="text" name="search" id="scan_search" class="form-control" placeholder="Scannez le code-barres, ou tapez le code, l'objet..." value="{{ $search }}" autofocus autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm ml-2">Rechercher</button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>Code</th>
                            <th>Origine</th>
                            <th>Objet</th>
                            <th>Où se trouve la demande ?</th>
                            <th>Annotations du Maire</th>
                            <th>Dernière mise à jour</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            @forelse($demandes as $d)
                            <tr>
                                <td>{{ $d->reference ?: '—' }}</td>
                                <td>@include('partials.origine-badge', ['demande' => $d])</td>
                                <td class="text-truncate" style="max-width:220px;" title="{{ $d->objet }}">{{ $d->objet ?: '—' }}</td>
                                <td>
                                    <span class="badge {{ \App\Models\Tabdepot::circuitStepBadgeClass($d->statut_circuit) }}">{{ $d->statutLabel() }}</span>
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
                                <td colspan="7" class="text-center text-muted">Aucune demande engagée dans le circuit pour le moment.</td>
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
