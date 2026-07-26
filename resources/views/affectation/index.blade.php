
@extends('layouts.master')




@section('title')

Dashboard Courier
@endsection

@section('content')

<div class="content">
                <div class="row">
                    <div class="col-md-8 ml-auto mr-auto">
                        <div class="card card-upgrade">
                            <div class="card-header text-center">
                                <h4 class="card-title">Affectation</h3>
                                    <p class="card-category"></p>
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

                                <div class="table-responsive table-upgrade">
                                    <table class="table">
                                        <thead>
                                            <th></th>
                                            <th class="text-center">Date</th>
                                            <th class="text-center">Demande</th>
                                            
                                        </thead>
                                        <tbody>
                                            @foreach($affectation as $c)
                                            <tr>
                                               
                                              <td>
                                                  {{$c->sevice}}
                                                </td>
                                                <td>
                                                   {{$c->dateaff}}
                                                </td>
                                                 <td>
                                                   {{$c->iddmd}}
                                                </td> 
                                                <td>
                                                 {{$c->id}}
                                                </td>
                                            </tr>
                                            @endforeach
                                            
                                            
                                            <tr>
        <td class="text-center">
                                                    <a href="#" class="btn btn-round btn-default disabled">Current Version</a>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Nouvelle Affectation</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
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
        <option value="{{$c->id}}">{{$c->id}}</option>
    @endforeach
    </select>
      </div>
      <span class="text-danger" data-error-for="iddmd"></span>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" id="affectationBtn" class="btn btn-primary">Ajouter</button>
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
        <h5 class="modal-title" id="exampleModalLabel">Modifier Sffectation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <form action="" method="post">
       @csrf
        @method('PUT')
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
        <span class="input-group-text">Service</span>
      </div>
      <input type="text" class="form-control" name="sevice" placeholder="Entrer Servicee">
      
                    <span  class="text-danger"></span>
                    
    </div>
      <br>
       <br>
   <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Date </span>
      </div>
      <input type="date" class="form-control" name="dateaff" max="{{ date('Y-m-d') }}" placeholder="Entrer Date">
     
                    <span  class="text-danger"></span>
                   
    </div>
    
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Modifier</button>
      </div>
      </form>
    </div>
  </div>
</div>
 </div>
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

<!-- Modal Delete-->
<div class="modal fade left " id="exampleModal-delete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog notifi modal-lg modal-right modal-danger" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Delete Demande</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <form action="{{}}" method="post">
        @csrf
        @method('DELETE')
      <input type="hidden"  id="id" name="id" >
      <p class ="text-centre" width="50px"> are you sure want to delete this Demande</p>
      </div>
   <div class="modal-footer">
        <button type="button" class="btn btn-warning" data-dismiss="modal">No/delete</button>
        <button type="submit" class="btn btn-success">Yes/DeleteDemande</button>
      </div>
      </form>
       </div>
  </div>
</div>
 </div>

 <script>

$('#exampleModal-show').on('show.bs.modal',function(event){
  var button=$(event.relatedTarget)
   var typdm=button.data('typdm')
  var nom=button.data('nom')
  var nni=button.data('nni')
  var tel=button.data('tel')
  var adresse=button.data('adresse')
  var daterecp=button.data('daterecp')
  var id= button.data('id')
            
  var modal=$(this)
  modal.find('.modal-title').text('edit demande Information');
  modal.find('.modal-body #typdm').val(typdm);
  modal.find('.modal-body #nom').val(nom);
  modal.find('.modal-body #adresse').val(nni);
  modal.find('.modal-body #tel').val(tel);
  modal.find('.modal-body #adresse').val(adresse);
  modal.find('.modal-body #daterecp').val(daterecp);
  modal.find('.modal-body #id').val(id); 
              
  });
  $('#exampleModal-edit').on('show.bs.modal',function(event){
  var button=$(event.relatedTarget)
  var typdm=button.data('typdm')
  var nom=button.data('nom')
  var nni=button.data('nni')
  var tel=button.data('tel')
  var adresse=button.data('adresse')
  var daterecp=button.data('daterecp')
  var id= button.data('id')
            
  var modal=$(this)
  modal.find('.modal-title').text('edit demande Information');
  modal.find('.modal-body #typdm').val(typdm);
  modal.find('.modal-body #nom').val(nom);
  modal.find('.modal-body #adresse').val(nni);
  modal.find('.modal-body #tel').val(tel);
  modal.find('.modal-body #adresse').val(adresse);
  modal.find('.modal-body #daterecp').val(daterecp);
  modal.find('.modal-body #id').val(id); 
              
  });
  $('#exampleModal-delete').on('show.bs.modal',function(event){
  var button=$(event.relatedTarget)
  var fournisseur_id= button.data('id')
            
            var modal=$(this)
            modal.find('.modal-title').text('delete demande Information');
            modal.find('.modal-body #id').val(id);
          }); 
</script>


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
