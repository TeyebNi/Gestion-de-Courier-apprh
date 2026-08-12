@extends('layouts.master')

@section('title')
Maire - Circuit des Demandes
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
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    Demandes en attente de décision
                </h4>
                <a href="{{ route('circuit.maire.historique') }}" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-history"></i> Historique de mes décisions
                </a>
            </div>
            <div class="card-body">
                @forelse($demandes as $d)
                <div class="card" style="border: 1px solid #eee; margin-bottom: 15px;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p><strong>Nom :</strong> {{ $d->nom }}</p>
                                <p><strong>Type de demande :</strong> {{ $d->typdm }}</p>
                                <p><strong>Origine :</strong> {{ $d->origine ?? '—' }} @if($d->origine_detail) ({{ $d->origine_detail }}) @endif</p>
                                <p><strong>NNI :</strong> {{ $d->nni }} — <strong>Tel :</strong> {{ $d->tel }}</p>
                                <p><strong>Adresse :</strong> {{ $d->adresse }}</p>
                                <p><strong>Date de réception :</strong> {{ $d->daterecp }}</p>
                                @if($d->piece_jointe)
                                <p>
                                    <a href="{{ asset('storage/' . $d->piece_jointe) }}" target="_blank" class="btn btn-info btn-sm">
                                        <i class="fas fa-paperclip"></i> Voir le document original (scan)
                                    </a>
                                </p>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <form action="{{ route('circuit.decider', $d) }}" method="post">
                                    @csrf
                                    <div class="form-group">
                                        <label>Décision <span class="text-danger">*</span></label>
                                        <select name="decision_maire" class="form-control" required>
                                            <option value="">Sélectionner</option>
                                            <option value="accepte">Accepter</option>
                                            <option value="refuse">Refuser</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Remarque (optionnel)</label>
                                        <textarea name="remarque_maire" class="form-control" rows="3" placeholder="Note ou instructions..."></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Service concerné <span class="text-danger">*</span></label>
                                        <select name="service_destination" class="form-control" required>
                                            <option value="">Sélectionner le service</option>
                                            @foreach($orientations as $o)
                                                <option value="{{ $o->name }}">{{ $o->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">Envoyer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-center text-muted">Aucune demande en attente.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
