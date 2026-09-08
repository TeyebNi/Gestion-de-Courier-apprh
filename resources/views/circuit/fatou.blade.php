@extends('layouts.master')

@section('title')
Cabinet de Maire - Circuit des Demandes
@endsection

@section('content')

@if(session('success'))
<div id="successOverlay" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; display:flex; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:32px; width:90%; max-width:380px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
        <div style="margin:0 auto 16px; width:64px; height:64px; border-radius:50%; border:3px solid #28a745; display:flex; align-items:center; justify-content:center;">
            <span style="color:#28a745; font-size:32px;">&#10003;</span>
        </div>
        <p style="color:#333; margin-bottom:20px;">{{ session('success') }}</p>
        <button id="closeSuccessCircuit" class="btn btn-info" style="width:100%;">OK</button>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var closeBtn = document.getElementById('closeSuccessCircuit');
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
                    Cabinet de Maire — Demandes en attente d'annotations
                </h4>
                <p class="text-muted mb-0" style="font-size: 0.9em;">
                    Portez le dossier au Maire, recueillez ses annotations, puis saisissez-les ici.
                </p>
            </div>
            <div class="card-body">
                @forelse($aEnvoyer as $d)
                <div class="card" style="border: 1px solid #eee; margin-bottom: 15px;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p><strong>Code :</strong> {{ $d->reference ?: '—' }}</p>
                                <p><strong>Objet :</strong> {{ $d->objet ?: '—' }}</p>
                                <p><strong>Origine :</strong> @include('partials.origine-badge', ['demande' => $d])</p>
                                <p><strong>Date de réception :</strong> {{ $d->daterecpFormatted() }}</p>
                            </div>
                            <div class="col-md-4">
                                <form action="{{ route('circuit.decider', $d) }}" method="post">
                                    @csrf
                                    <div class="form-group">
                                        <label>Annotations du Maire <span class="text-danger">*</span></label>
                                        <textarea name="remarque_maire" class="form-control" rows="4" placeholder="Ce que le Maire a annoté sur le dossier..." required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Service concerné (optionnel)</label>
                                        <select name="service_destination" class="form-control">
                                            <option value="">Aucun (classer directement)</option>
                                            @foreach($orientations as $o)
                                                <option value="{{ $o->name }}" {{ $d->origine === 'interne' && $d->origine_detail === $o->name ? 'selected' : '' }}>{{ $o->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">Enregistrer les annotations</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-center text-muted">Aucune demande en attente.</p>
                @endforelse
                <div class="d-flex justify-content-center mt-3">
                    {{ $aEnvoyer->links() }}
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
                    Demandes déjà annotées
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>Code</th>
                            <th>Objet</th>
                            <th>Annotations du Maire</th>
                            <th>Où se trouve la demande ?</th>
                            <th>Dernière mise à jour</th>
                        </thead>
                        <tbody>
                            @forelse($dejaAnnotees as $d)
                            <tr>
                                <td>{{ $d->reference ?: '—' }}</td>
                                <td class="text-truncate" style="max-width:220px;" title="{{ $d->objet }}">{{ $d->objet ?: '—' }}</td>
                                <td class="text-truncate" style="max-width:260px;" title="{{ $d->remarque_maire }}">{{ $d->remarque_maire }}</td>
                                <td>
                                    <span class="badge {{ \App\Models\Tabdepot::circuitStepBadgeClass($d->statut_circuit) }}">{{ $d->statutLabel() }}</span>
                                </td>
                                <td>{{ $d->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aucune demande annotée pour le moment.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $dejaAnnotees->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
