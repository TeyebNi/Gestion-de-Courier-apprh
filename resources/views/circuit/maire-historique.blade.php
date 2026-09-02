@extends('layouts.master')

@section('title')
Historique du Maire
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    Historique de mes décisions
                </h4>
                @include('partials.search-box', ['route' => 'circuit.maire.historique', 'placeholder' => 'Rechercher par N°, nom, NNI, objet, référence...', 'minWidth' => 300])
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>N°</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Objet</th>
                            <th>Décision</th>
                            <th>Remarque</th>
                            <th>Statut actuel</th>
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
                                    @if($d->decision_maire === 'accepte')
                                        <span class="badge badge-success">Acceptée</span>
                                    @else
                                        <span class="badge badge-danger">Refusée</span>
                                    @endif
                                </td>
                                <td>{{ $d->remarque_maire ?: '—' }}</td>
                                <td><span class="text-muted">{{ $d->statutLabel() }}</span></td>
                                <td>
                                    <a href="{{ route('circuit.historique', $d) }}" class="btn btn-info btn-sm" title="Voir l'historique complet">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    @if($search)
                                        Aucune décision ne correspond à « {{ $search }} ».
                                    @else
                                        Aucune décision prise pour le moment.
                                    @endif
                                </td>
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

@endsection
