@extends('layouts.master')

@section('title')
Demandes du Service
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    Demandes orientées vers votre service
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Décision du Maire</th>
                            <th>Remarque</th>
                            <th>Date</th>
                        </thead>
                        <tbody>
                            @forelse($demandes as $d)
                            <tr>
                                <td>{{ $d->nom }} @if($d->piece_jointe)<a href="{{ asset('storage/' . $d->piece_jointe) }}" target="_blank" title="Voir la pièce jointe"><i class="fas fa-paperclip text-info"></i></a>@endif</td>
                                <td>{{ $d->typdm }}</td>
                                <td>
                                    @if($d->decision_maire === 'accepte')
                                        <span class="badge badge-success">Acceptée</span>
                                    @elseif($d->decision_maire === 'refuse')
                                        <span class="badge badge-danger">Refusée</span>
                                    @else
                                        <span class="badge badge-secondary">Envoyée directement (sans décision)</span>
                                    @endif
                                </td>
                                <td>{{ $d->remarque_maire ?: '—' }}</td>
                                <td>{{ $d->daterecp }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aucune demande pour votre service.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
