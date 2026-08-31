@extends('layouts.master')

@section('title')
Cabinet - Circuit des Demandes
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
                    À transmettre au Maire
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Origine</th>
                            <th>Date</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            @forelse($aEnvoyer as $d)
                            <tr>
                                <td>{{ $d->nom }} @if($d->piece_jointe)<a href="{{ asset('storage/' . $d->piece_jointe) }}" target="_blank" title="Voir la pièce jointe"><i class="fas fa-paperclip text-info"></i></a>@endif</td>
                                <td>{{ $d->typdm }}</td>
                                <td>{{ $d->origine ?? '—' }}</td>
                                <td>{{ $d->daterecp }}</td>
                                <td>
                                    <form action="{{ route('circuit.envoyer-maire', $d) }}" method="post" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">Envoyer au Maire</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aucune demande en attente.</td>
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
