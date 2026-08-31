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
                            <th>Action</th>
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
                                <td>
                                    <form action="{{ route('circuit.cloturer', $d) }}" method="post" onsubmit="return confirm('Marquer cette demande comme traitée ?');">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Clôturer
                                        </button>
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
            </div>
        </div>
    </div>
</div>

@endsection
