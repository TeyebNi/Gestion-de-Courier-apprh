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
                        <input type="text" name="search" id="scan_search" class="form-control" placeholder="Scannez le code-barres, ou tapez N°, nom, NNI, objet, référence..." value="{{ $search }}" autofocus autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm ml-2">Rechercher</button>
                    <button type="button" class="btn btn-secondary btn-sm ml-2" data-toggle="modal" data-target="#qrScannerModal">
                        <i class="fas fa-camera"></i> Scanner avec la caméra
                    </button>
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
                            <th>Annotations du Maire</th>
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
                                <td colspan="8" class="text-center text-muted">Aucune demande engagée dans le circuit pour le moment.</td>
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

<div class="modal fade" id="qrScannerModal" tabindex="-1" role="dialog" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrScannerModalLabel">Scanner le QR code du reçu</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="qr-reader" style="width: 100%;"></div>
                <div id="qr-reader-error" class="alert alert-danger mt-2" style="display:none;"></div>
                <p class="text-muted small mt-2 mb-0">Placez le reçu imprimé face à la caméra, bien éclairé.</p>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('scan_search');
    if (input) {
        input.focus();
        input.select();
    }

    var modalEl = document.getElementById('qrScannerModal');
    var errorBox = document.getElementById('qr-reader-error');
    var html5QrCode = null;

    function showError(message) {
        errorBox.textContent = message;
        errorBox.style.display = 'block';
    }

    function stopScanner() {
        if (!html5QrCode) {
            return;
        }
        var instance = html5QrCode;
        html5QrCode = null;
        instance.stop().then(function () {
            instance.clear();
        }).catch(function () {
            // La caméra était déjà arrêtée (ex: modal fermée avant la fin du démarrage) : rien à faire.
        });
    }

    if (modalEl && window.Html5Qrcode) {
        $(modalEl).on('shown.bs.modal', function () {
            errorBox.style.display = 'none';
            html5QrCode = new Html5Qrcode('qr-reader');
            html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: 250 },
                function (decodedText) {
                    // Le QR encode l'id de la demande sur 6 chiffres (ex: 000051) :
                    // on retire les zéros de tête, comme le fait déjà la recherche manuelle.
                    input.value = decodedText.replace(/^0+/, '') || '0';
                    $(modalEl).modal('hide');
                    input.form.submit();
                },
                function () {
                    // Aucun QR détecté sur cette image : normal tant que le reçu n'est pas bien cadré.
                }
            ).catch(function (err) {
                showError("Impossible d'accéder à la caméra : " + err);
            });
        });

        $(modalEl).on('hidden.bs.modal', stopScanner);
    }
});
</script>

@endsection
