@extends('layouts.master')

@section('title')
Demandes du Service
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
            <div class="card-header">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    {{ auth()->user()->specialServiceLabel() ? 'Demandes qui vous sont orientées' : 'Demandes orientées vers votre service' }}
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>Code</th>
                            <th>Origine</th>
                            <th>Objet</th>
                            <th>Annotations du Maire</th>
                            <th>Date</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            @forelse($demandes as $d)
                            <tr>
                                <td>{{ $d->reference ?: '—' }}</td>
                                <td>@include('partials.origine-badge', ['demande' => $d])</td>
                                <td class="text-truncate" style="max-width:220px;" title="{{ $d->objet }}">{{ $d->objet ?: '—' }}</td>
                                <td class="text-truncate" style="max-width:220px;" title="{{ $d->remarque_maire }}">{{ $d->remarque_maire ?: '—' }}</td>
                                <td>{{ $d->daterecpFormatted() }}</td>
                                <td>
                                    <form action="{{ route('circuit.cloturer', $d) }}" method="post" style="display:inline;" onsubmit="return confirm('Marquer cette demande comme traitée ?');">
                                        @csrf
                                        <input type="hidden" name="resolution" value="traiter">
                                        <button type="submit" class="btn btn-success btn-sm" title="Traiter"><i class="fas fa-check"></i> Traiter</button>
                                    </form>
                                    <form action="{{ route('circuit.cloturer', $d) }}" method="post" style="display:inline;" onsubmit="return confirm('Classer cette demande sans traitement particulier ?');">
                                        @csrf
                                        <input type="hidden" name="resolution" value="classer">
                                        <button type="submit" class="btn btn-secondary btn-sm" title="Classer"><i class="fas fa-folder"></i> Classer</button>
                                    </form>
                                    <form action="{{ route('circuit.cloturer', $d) }}" method="post" style="display:inline;" onsubmit="return confirm('Convoquer le demandeur ?');">
                                        @csrf
                                        <input type="hidden" name="resolution" value="convoquer">
                                        <button type="submit" class="btn btn-warning btn-sm" title="Convoquer"><i class="fas fa-phone"></i> Convoquer</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Aucune demande pour votre service.</td>
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

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    Demandes traitées
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>Code</th>
                            <th>Origine</th>
                            <th>Objet</th>
                            <th>Résolution</th>
                            <th>Date</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            @forelse($demandesTraitees as $d)
                            <tr>
                                <td>{{ $d->reference ?: '—' }}</td>
                                <td>@include('partials.origine-badge', ['demande' => $d])</td>
                                <td class="text-truncate" style="max-width:220px;" title="{{ $d->objet }}">{{ $d->objet ?: '—' }}</td>
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
                                <td>{{ $d->daterecpFormatted() }}</td>
                                <td>
                                    <a href="{{ route('circuit.historique', $d) }}" class="btn btn-info btn-sm" title="Voir l'historique complet">
                                        <i class="fas fa-history"></i> Historique
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Aucune demande traitée pour le moment.</td>
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

@endsection
