@extends('layouts.master')

@section('title')
Dashboard Courier
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    Gestion des Demandes
                    <button class="btn btn-primary btn-sm ml-2" data-toggle="modal" data-target="#exampleModal">Nouvelle Demande</button>
                </h4>
                <div class="d-flex align-items-center flex-wrap">
                    @include('partials.search-box', ['route' => 'depot.index', 'placeholder' => 'Rechercher par code, objet...', 'minWidth' => 300])
                    <a href="{{ route('depot.export') }}" class="btn btn-success btn-sm" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
                </div>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table">
                        <thead class=" text-primary">
                            <th>Code</th>
                            <th>Objet</th>
                            <th>Origine</th>
                            <th>Statut</th>
                            <th class="text-right">Date</th>
                            <th class="text-right">Action</th>
                        </thead>
                        <tbody>
                          @forelse($tabdepot as $item)
                            @php
                                $depotBadgeClass = \App\Models\Tabdepot::circuitStepBadgeClass($item->statut_circuit ?? 'accueil');
                            @endphp
                            <tr>
                                <td>{{ $item->reference ?: '—' }}</td>
                                <td class="text-truncate" style="max-width:280px;" title="{{ $item->objet }}">{{ $item->objet ?: '—' }}</td>
                                <td>@include('partials.origine-badge', ['demande' => $item])</td>
                                  <td><span class="badge {{ $depotBadgeClass }}">{{ $item->statutLabel() }}</span></td>
                                  <td class="text-right">{{ $item->daterecpFormatted() }}</td>
                                <td class="text-right">
                                    <a href="{{ route('depot.print_reçu', $item->id) }}" target="_blank" class="btn btn-success btn-sm" title="Imprimer"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg></a>
                                    @if(($item->statut_circuit ?? 'accueil') === 'accueil')
                                    <form action="{{ route('circuit.envoyer-fatou', $item) }}" method="post" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-info btn-sm" title="Envoyer au Cabinet">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                    @else
                                    <a href="{{ route('circuit.historique', $item) }}" class="btn btn-secondary btn-sm" title="{{ $item->statutLabel() }}">
                                        <i class="fas fa-route"></i>
                                    </a>
                                    @endif
                                    <a data-id="{{$item->id}}" data-objet="{{$item->objet}}" data-reference="{{$item->reference}}" data-origine="{{$item->origine}}" data-piece-jointe-url="{{ $item->piece_jointe ? asset('storage/' . $item->piece_jointe) : '' }}" data-toggle="modal" data-target="#exampleModal-edit" type="button" class="btn btn-info btn-sm" title="Modifier"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></a>
                                    @if(($item->statut_circuit ?? 'accueil') === 'accueil')
                                    <a data-id="{{$item->id}}" data-reference="{{ $item->reference ?: 'cette demande' }}" data-toggle="modal" data-target="#exampleModal-delete" type="button" class="btn btn-danger btn-sm" title="Supprimer"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg></a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    @if($search)
                                        Aucune demande ne correspond à « {{ $search }} ».
                                    @else
                                        Aucune demande enregistrée pour le moment.
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $tabdepot->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

@endsection


@section('scripts')

<!-- Modal Ajout -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog  modal-notify modal-lg modal-right modal-success" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Ajouter une Demande</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
      </div>
      <div class="modal-body">
         <form action="{{route('depot.store')}}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="form_source" value="create">
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Origine *</span>
      </div>
     <select id="create_origine" class="form-control" name="origine" required>
      <option value="">Sélectionner l'origine</option>
      <option value="interne" @selected(old('origine') === 'interne')>Interne (note entre services de la commune)</option>
      <option value="externe" @selected(old('origine') === 'externe')>Externe (citoyen, institution, organisme...)</option>
    </select>
      </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Code *</span>
      </div>
      <input type="text" class="form-control @error('reference') is-invalid @enderror" name="reference" value="{{ old('reference') }}" placeholder="Code / référence du courrier" maxlength="100" required>
    </div>
    @error('reference')
      <div class="text-danger small mt-1 mb-2">{{ $message }}</div>
    @enderror
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Objet</span>
      </div>
      <input type="text" class="form-control" name="objet" value="{{ old('objet') }}" placeholder="Résumé de la demande (optionnel)" maxlength="255">
    </div>
      <br>
      <div class="form-group">
        <label for="create_piece_jointe">Pièce jointe (scanner/joindre le document original, optionnel)</label>
        <input type="file" class="form-control-file @error('piece_jointe') is-invalid @enderror" id="create_piece_jointe" name="piece_jointe" accept=".jpg,.jpeg,.png,.pdf">
        @error('piece_jointe')
          <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
      </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-warning" data-dismiss="modal" title="Fermer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
        <button type="submit"  class="btn btn-success" title="Ajouter"><i class="now-ui-icons ui-1_check"></i></button>
      </div>
</form>
    </div>
  </div>
</div>

<!-- Modal Modifier -->
<div class="modal fade" id="exampleModal-edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog  modal-notify modal-lg modal-right modal-success" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modifier la Demande</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editDepotForm" action="" method="post" enctype="multipart/form-data">
       @csrf
        @method('PUT')
        <input type="hidden" name="form_source" value="edit">
        <input type="hidden" name="depot_id" id="edit_depot_id" value="{{ old('depot_id') }}">
        <div class="modal-body">
        <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Origine *</span>
      </div>
     <select id="edit_origine" class="form-control" name="origine" required>
      <option value="">Sélectionner l'origine</option>
      <option value="interne" @selected(old('origine') === 'interne')>Interne (note entre services de la commune)</option>
      <option value="externe" @selected(old('origine') === 'externe')>Externe (citoyen, institution, organisme...)</option>
    </select>
      </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Code *</span>
      </div>
      <input id="edit_reference" type="text" class="form-control @error('reference') is-invalid @enderror" name="reference" value="{{ old('reference') }}" placeholder="Code / référence du courrier" maxlength="100" required>
    </div>
    @error('reference')
      <div class="text-danger small mt-1 mb-2">{{ $message }}</div>
    @enderror
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Objet</span>
      </div>
      <input id="edit_objet" type="text" class="form-control" name="objet" value="{{ old('objet') }}" placeholder="Résumé de la demande (optionnel)" maxlength="255">
    </div>
      <br>
      <div class="form-group">
        <label for="edit_piece_jointe">Pièce jointe (scanner/joindre le document original, optionnel)</label>
        <input type="file" class="form-control-file @error('piece_jointe') is-invalid @enderror" id="edit_piece_jointe" name="piece_jointe" accept=".jpg,.jpeg,.png,.pdf">
        <small id="edit_piece_jointe_current" class="form-text text-muted"></small>
        @error('piece_jointe')
          <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
      </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-warning" data-dismiss="modal" title="Fermer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
          <button type="submit" class="btn btn-success" title="Modifier"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Supprimer -->
<div class="modal fade left " id="exampleModal-delete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog notifi modal-lg modal-right modal-danger" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Supprimer la Demande</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="deleteDepotForm" action="" method="post">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Voulez-vous vraiment supprimer la demande <strong id="delete_depot_nom"></strong> ?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-warning" data-dismiss="modal" title="Annuler"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
          <button type="submit" class="btn btn-success" title="Oui, Supprimer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></button>
        </div>
      </form>
    </div>
  </div>
</div>

@if (session('success'))
<div id="successOverlay" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; display:flex; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:32px; width:90%; max-width:380px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
        <div style="margin:0 auto 16px; width:64px; height:64px; border-radius:50%; border:3px solid #28a745; display:flex; align-items:center; justify-content:center;">
            <span style="color:#28a745; font-size:32px;">&#10003;</span>
        </div>
        <p style="color:#333; margin-bottom:20px;">{{ session('success') }}</p>
        <button id="closeSuccessDepot" class="btn btn-info" style="width:100%;">OK</button>
    </div>
</div>
@endif

<script>
$('#exampleModal-edit').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#editDepotForm').attr('action', '{{ url('/depot') }}/' + id);
    $('#edit_depot_id').val(id);
    $('#edit_objet').val(button.data('objet'));
    $('#edit_reference').val(button.data('reference'));
    $('#edit_origine').val(button.data('origine'));
    var pieceJointeUrl = button.data('piece-jointe-url');
    var currentPieceJointe = $('#edit_piece_jointe_current');
    if (pieceJointeUrl) {
        currentPieceJointe.html('Pièce jointe actuelle : <a href="' + pieceJointeUrl + '" target="_blank">voir le document</a> (choisir un fichier ci-dessus la remplacera).');
    } else {
        currentPieceJointe.text('Aucune pièce jointe pour le moment.');
    }
});

$('#exampleModal-delete').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#deleteDepotForm').attr('action', '{{ url('/depot') }}/' + id);
    $('#delete_depot_nom').text(button.data('reference'));
});

var closeBtnDepot = document.getElementById('closeSuccessDepot');
if (closeBtnDepot) {
    closeBtnDepot.addEventListener('click', function () {
        document.getElementById('successOverlay').style.display = 'none';
    });
}

$('#exampleModal').on('hidden.bs.modal', function () {
    var form = this.querySelector('form');
    if (!form) { return; }
    form.reset();
});

@if ($errors->any())
    @if (old('form_source') === 'edit')
        $('#editDepotForm').attr('action', '{{ url('/depot') }}/' + @json(old('depot_id')));
        $('#exampleModal-edit').modal('show');
    @else
        $('#exampleModal').modal('show');
    @endif
@endif
</script>
@endsection
