@extends('layouts.master')

@section('title')
Dashboard Courier
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
               <p class="category">
                <button class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Nouvelle Demande</button>
                 <a href="{{ route('depot.export') }}" class="btn btn-success btn-sm" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
                </p>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table">
                        <thead class=" text-primary">
                           <th>N°</th>
                            <th>Code</th>
                            <th>Type de Demande</th>
                            <th>Nom</th>
                            <th class="text-right">NNI</th>
                            <th class="text-right">Tel</th>
                             <th class="text-right">Adresse</th>
                            <th class="text-right">Date</th>
                            <th class="text-right">Action</th>
                        </thead>
                        <tbody>
                          @foreach($tabdepot as $key=>$item)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>{{$item->id}}</td>
                                <td>{{$item->typdm}}</td>
                                <td class="text-right">{{$item->nom}}</td>
                                  <td class="text-right">{{$item->nni}}</td>
                                <td class="text-right">{{$item->tel}}</td>
                                  <td class="text-right">{{$item->adresse}}</td>
                                  <td class="text-right">{{$item->daterecp}}</td>
                                <td class="text-right">
                                    <a href="{{ route('depot.print_reçu', $item->id) }}" target="_blank" class="btn btn-success btn-sm" title="Imprimer"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg></a>
                                    <a data-id="{{$item->id}}" data-typdm="{{$item->typdm}}" data-nom="{{$item->nom}}" data-nni="{{$item->nni}}" data-adresse="{{$item->adresse}}" data-tel="{{$item->tel}}" data-daterecp="{{$item->daterecp}}" data-toggle="modal" data-target="#exampleModal-edit" type="button" class="btn btn-info btn-sm" title="Modifier"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></a>
                                    <a data-id="{{$item->id}}" data-nom="{{$item->nom}}" data-toggle="modal" data-target="#exampleModal-delete" type="button" class="btn btn-danger btn-sm" title="Supprimer"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg></a>
                                </td>
                            </tr>
                            @endforeach
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
         <form action="{{route('depot.store')}}" method="post">
        @csrf
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Type de Demande</span>
      </div>
     <select   class="form-control" name="typdm">
      <option value="">Sélectionner le type de demande</option>
      @foreach($typedem as $c)
        <option value="{{$c->name}}">{{$c->name}}</option>
    @endforeach
    </select>
      </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">NNI</span>
      </div>
      <input type="text" class="form-control" name="nni" placeholder="Entrer NNI" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)" required>
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Nom</span>
      </div>
      <input type="text" class="form-control" name="nom" placeholder="Entrer Nom" oninput="this.value=this.value.replace(/[0-9]/g,'')" required>
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Tel</span>
      </div>
      <input type="text" class="form-control" name="tel" placeholder="Entrer Tel" maxlength="8" inputmode="numeric" pattern="[0-9]{8}" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,8)" required>
    </div>
     <br>
     <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Adresse</span>
      </div>
      <input type="text" class="form-control" name="adresse" placeholder="Entrer Adresse">
    </div>
     <br>
   <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Date</span>
      </div>
      <input type="date" class="form-control" name="daterecp" max="{{ date('Y-m-d') }}">
    </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-warning" data-dismiss="modal" title="Fermer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
        <button type="submit" class="btn btn-primary">Ajouter</button>
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
      <form id="editDepotForm" action="" method="post">
       @csrf
        @method('PUT')
        <div class="modal-body">
        <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Type de Demande</span>
      </div>
     <select id="edit_typdm" class="form-control" name="typdm">
      <option value="">Sélectionner le type de demande</option>
      @foreach($typedem as $c)
        <option value="{{$c->name}}">{{$c->name}}</option>
    @endforeach
    </select>
      </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">NNI</span>
      </div>
      <input id="edit_nni" type="text" class="form-control" name="nni" placeholder="Entrer NNI" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)" required>
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Nom</span>
      </div>
      <input id="edit_nom" type="text" class="form-control" name="nom" placeholder="Entrer Nom" oninput="this.value=this.value.replace(/[0-9]/g,'')" required>
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Tel</span>
      </div>
      <input id="edit_tel" type="text" class="form-control" name="tel" placeholder="Entrer Tel" maxlength="8" inputmode="numeric" pattern="[0-9]{8}" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,8)" required>
    </div>
     <br>
     <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Adresse</span>
      </div>
      <input id="edit_adresse" type="text" class="form-control" name="adresse" placeholder="Entrer Adresse">
    </div>
     <br>
   <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Date</span>
      </div>
      <input id="edit_daterecp" type="date" class="form-control" name="daterecp" max="{{ date('Y-m-d') }}">
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
          <p>Voulez-vous vraiment supprimer la demande de <strong id="delete_depot_nom"></strong> ?</p>
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
    $('#editDepotForm').attr('action', '/depot/' + id);
    $('#edit_typdm').val(button.data('typdm'));
    $('#edit_nni').val(button.data('nni'));
    $('#edit_nom').val(button.data('nom'));
    $('#edit_tel').val(button.data('tel'));
    $('#edit_adresse').val(button.data('adresse'));
    $('#edit_daterecp').val(button.data('daterecp'));
});

$('#exampleModal-delete').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#deleteDepotForm').attr('action', '/depot/' + id);
    $('#delete_depot_nom').text(button.data('nom'));
});

var closeBtnDepot = document.getElementById('closeSuccessDepot');
if (closeBtnDepot) {
    closeBtnDepot.addEventListener('click', function () {
        document.getElementById('successOverlay').style.display = 'none';
    });
}
</script>
@endsection