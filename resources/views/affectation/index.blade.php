@extends('layouts.master')

@section('title')
Affectation
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <p class="category">
                    Gestion des Affectations
                    @if(auth()->user()->isAdmin())
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal">Nouvelle Affectation</button>
                    @endif
                    <a href="{{ route('affectation.export') }}" class="btn btn-success btn-sm" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
                </p>
            </div>
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>N°</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Demande</th>
                            <th class="text-right">Action</th>
                        </thead>
                        <tbody>
                            @foreach($affectation as $key => $c)
                            <tr>
                                <td>{{ $affectation->firstItem() + $key }}</td>
                                <td>{{ $c->sevice }}</td>
                                <td>{{ $c->dateaff }}</td>
                                <td>
                                    @if($c->demande)
                                        {{ $c->demande->typdm }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a data-id="{{ $c->id }}" data-sevice="{{ $c->sevice }}" data-dateaff="{{ $c->dateaff }}" data-iddmd="{{ $c->iddmd }}" data-toggle="modal" data-target="#exampleModal-edit" class="btn btn-success btn-sm" title="Modifier"><i class="now-ui-icons ui-2_settings-90"></i></a>
                                    <a data-id="{{ $c->id }}" data-toggle="modal" data-target="#exampleModal-delete" class="btn btn-danger btn-sm" title="Supprimer"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $affectation->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@endsection
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog  modal-notify modal-lg modal-right modal-success" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Ajouter Affectation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

         <div id="affectation-ajax-errors"></div>
         <form id="affectationForm" action="{{route('affectation.store')}}" method="post">
        @csrf
        
        <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Code</span>
      </div>
      <input type="text" class="form-control" name="id" placeholder="Entrer Code">
     
                    <span  class="text-danger"></span>
                   
    </div>
    <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Orientation</span>
      </div>
     <select   class="form-control" id="aff_sevice" name="sevice">
      <option value="">Select Orientation</option>
      @foreach($orientation as $c)      
        <option value="{{$c->name}}">{{$c->name}}</option>
    @endforeach
    </select>
      </div>
      <span class="text-danger" data-error-for="sevice"></span>
       <br>
   <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Date </span>
      </div>
      <input type="date" class="form-control" id="aff_dateaff" name="dateaff" max="{{ date('Y-m-d') }}" placeholder="Entrer Date">
     
                    <span  class="text-danger" data-error-for="dateaff"></span>
                   
    </div>
     <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Demande</span>
      </div>
     <select   class="form-control" id="aff_iddmd" name="iddmd">
      <option value="">Select Code Demande</option>
      @foreach($tabdepot as $c)      
        <option value="{{$c->id}}">#{{$c->id}} - {{ $c->typdm }}</option>
    @endforeach
    </select>
      </div>
      <span class="text-danger" data-error-for="iddmd"></span>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-warning" data-dismiss="modal" title="Fermer"><i class="now-ui-icons ui-1_simple-remove"></i></button>
        <button type="submit" id="affectationBtn" class="btn btn-success" title="Ajouter"><i class="now-ui-icons ui-1_check"></i></button>
      </div>
      </form>
    </div>
  </div>
</div>
 </div>
 <!-- Modal edit-->
<div class="modal fade" id="exampleModal-edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog  modal-notify modal-lg modal-right modal-success" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modifier l'Affectation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editAffectationForm" action="" method="post">
       @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">Service</span>
            </div>
            @if(auth()->user()->isAdmin())
            <select class="form-control" id="edit_aff_sevice" name="sevice">
              <option value="">Sélectionner le service</option>
              @foreach($orientation as $c)
                <option value="{{ $c->name }}">{{ $c->name }}</option>
              @endforeach
            </select>
            @else
            <input type="text" class="form-control" id="edit_aff_sevice_display" disabled>
            <input type="hidden" id="edit_aff_sevice" name="sevice">
            @endif
          </div>
          <br>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">Demande</span>
            </div>
            <select class="form-control" id="edit_aff_iddmd" name="iddmd">
              <option value="">Sélectionner le code demande</option>
              @foreach($tabdepot as $c)
                <option value="{{ $c->id }}">#{{ $c->id }} - {{ $c->typdm }}</option>
              @endforeach
            </select>
          </div>
          <br>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">Date</span>
            </div>
            <input type="date" class="form-control" id="edit_aff_dateaff" name="dateaff" max="{{ date('Y-m-d') }}">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-warning" data-dismiss="modal" title="Fermer"><i class="now-ui-icons ui-1_simple-remove"></i></button>
          <button type="submit" class="btn btn-success" title="Modifier"><i class="now-ui-icons ui-1_check"></i></button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal delete-->
<div class="modal fade left " id="exampleModal-delete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog notifi modal-lg modal-right modal-danger" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Supprimer l'Affectation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="deleteAffectationForm" action="" method="post">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Voulez-vous vraiment supprimer cette affectation ?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-warning" data-dismiss="modal" title="Annuler"><i class="now-ui-icons ui-1_simple-remove"></i></button>
          <button type="submit" class="btn btn-success" title="Oui, Supprimer"><i class="now-ui-icons ui-1_check"></i></button>
        </div>
      </form>
    </div>
  </div>
</div>

@if (session('success'))
<div id="affectationEditSuccessOverlay" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; display:flex; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:32px; width:90%; max-width:380px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
        <div style="margin:0 auto 16px; width:64px; height:64px; border-radius:50%; border:3px solid #28a745; display:flex; align-items:center; justify-content:center;">
            <span style="color:#28a745; font-size:32px;">&#10003;</span>
        </div>
        <p style="color:#333; margin-bottom:20px;">{{ session('success') }}</p>
        <button id="closeAffectationEditSuccess" class="btn btn-info" style="width:100%;">OK</button>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#exampleModal-edit').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        $('#editAffectationForm').attr('action', '/affectation/' + id);
        $('#edit_aff_sevice').val(button.data('sevice'));
        $('#edit_aff_sevice_display').val(button.data('sevice'));
        $('#edit_aff_iddmd').val(button.data('iddmd'));
        $('#edit_aff_dateaff').val(button.data('dateaff'));
    });

    $('#exampleModal-delete').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        $('#deleteAffectationForm').attr('action', '/affectation/' + id);
    });

    var closeBtnAffEdit = document.getElementById('closeAffectationEditSuccess');
    if (closeBtnAffEdit) {
        closeBtnAffEdit.addEventListener('click', function () {
            document.getElementById('affectationEditSuccessOverlay').style.display = 'none';
        });
    }
});
</script>
 <!-- Modal Show-->
<div class="modal fade" id="exampleModal-show" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog  modal-notify modal-lg modal-right modal-success" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Affichage</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <form action="" method="post">
        
        <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Code</span>
      </div>
      <input type="text" class="form-control" name="PersonID" placeholder="Entrer Code">
     
                    <span  class="text-danger"></span>
                   
    </div>
    <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Type de Demande</span>
      </div>
      <input type="text" class="form-control" name="typdm" placeholder="Entrer Type de Demande">
      
                    <span  class="text-danger"></span>
                    
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">NNI</span>
      </div>
      <input type="text" class="form-control" name="nni" placeholder="Entrer NNI">
      
                    <span  class="text-danger"></span>
                   
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Nom</span>
      </div>
      <input type="text" class="form-control" name="nom" placeholder="Entrer Nom">
     
                    <span  class="text-danger"></span>
                   
    </div>
      <br>
      
      
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Tel</span>
      </div>
      <input type="text" class="form-control" name="tel" placeholder="Entrer Tel">
      
                    <span  class="text-danger"></span>
                    
    </div>
     <br>
     <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Adresse</span>
      </div>
      <input type="text" class="form-control" name="adresse" placeholder="Entrer Adresse">
      
                    <span  class="text-danger"></span>
                   
    </div>
      
     <br>
   <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Date </span>
      </div>
      <input type="date" class="form-control" name="daterecp" placeholder="Entrer Date">
     
                    <span  class="text-danger"></span>
                   
    </div>
    
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
       
      </div>
      </form>
    </div>
  </div>
</div>
 </div>

<!-- Modale de succès Affectation -->
<div id="affectationSuccessOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:32px; width:90%; max-width:380px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
        <div style="margin:0 auto 16px; width:64px; height:64px; border-radius:50%; border:3px solid #28a745; display:flex; align-items:center; justify-content:center;">
            <span style="color:#28a745; font-size:32px;">&#10003;</span>
        </div>
        <h5 style="margin-bottom:8px;">Affectation réussie !</h5>
        <p id="affectationSuccessDetails" style="color:#666; margin-bottom:20px;"></p>
        <button id="closeAffectationSuccess" class="btn btn-primary" style="width:100%;">OK</button>
    </div>
</div>

<script>
document.getElementById('affectationForm').addEventListener('submit', function (e) {
    e.preventDefault();

    var form = e.target;
    var btn = document.getElementById('affectationBtn');
    var errorsBox = document.getElementById('affectation-ajax-errors');

    errorsBox.innerHTML = '';
    document.querySelectorAll('[data-error-for]').forEach(function (el) { el.textContent = ''; });
    document.querySelectorAll('.form-control').forEach(function (el) { el.classList.remove('is-invalid'); });

    btn.disabled = true;

    fetch(form.action, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: new FormData(form),
    })
    .then(function (response) {
        return response.json().then(function (data) {
            return { status: response.status, data: data };
        });
    })
    .then(function (result) {
        btn.disabled = false;

        if (result.status === 200 && result.data.success) {
            document.getElementById('affectationSuccessDetails').textContent = result.data.message;
            document.getElementById('affectationSuccessOverlay').style.display = 'flex';
            form.reset();
        } else if (result.status === 422) {
            var errors = result.data.errors || {};
            Object.keys(errors).forEach(function (field) {
                var span = document.querySelector('[data-error-for="' + field + '"]');
                var input = document.getElementById('aff_' + field);
                if (span) span.textContent = errors[field][0];
                if (input) input.classList.add('is-invalid');
            });
        } else {
            errorsBox.innerHTML = '<div class="alert alert-danger">Une erreur est survenue. Réessayez.</div>';
        }
    })
    .catch(function () {
        btn.disabled = false;
        errorsBox.innerHTML = '<div class="alert alert-danger">Impossible de contacter le serveur.</div>';
    });
});

document.getElementById('closeAffectationSuccess').addEventListener('click', function () {
    document.getElementById('affectationSuccessOverlay').style.display = 'none';
    window.location.reload();
});
</script>