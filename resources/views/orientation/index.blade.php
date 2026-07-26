
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
                                <h4 class="card-title">Orientation</h3>
                                    <p class="card-category"></p>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive table-upgrade">
                                    <table class="table">
                                        <thead>
                                            <th></th>
                                            <th class="text-center">Code</th>
                                            <th class="text-center">N°</th>
                                        </thead>
                                        <tbody>
                                           @foreach($orientation as $key=>$c)
                                            <tr>
                                               
                                                <td>
                                                   {{$c->name}}
                                                </td>
                                                 <td>
                                                   {{$c->id}}
                                                </td> 
                                                 <td>
                                                  {{++$key}}
                                                </td>
                                            </tr>
                                            @endforeach
                                            
                                            
                                            <tr>
        <td class="text-center">
                                                   <a  data-id="  data-toggle="modal" data-target="#exampleModal-edit" type="button" class="btn btn-info"> Edit</a> 
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Nouvelle Orientation
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
        <h5 class="modal-title" id="exampleModalLabel">Ajouter Orientation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

         <div id="orientation-ajax-errors"></div>
         <form id="orientationForm" action="{{route('orientation.store')}}" method="post">
        @csrf
        
        
        
   <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Orientation </span>
      </div>
   <input type="text" class="form-control" id="orientation_name" name="name" placeholder="Entrer Nom">
     
                    <span class="text-danger" data-error-for="name"></span>
                   
    </div>
    
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" id="orientationBtn" class="btn btn-primary">Ajouter</button>
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
        <h5 class="modal-title" id="exampleModalLabel">Modifier Orientation</h5>
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
        <span class="input-group-text">Orientation </span>
      </div>
      <input type="date" class="form-control" name="name" placeholder="Entrer ">
     
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


<!-- Modale de succès -->
<div id="orientationSuccessOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:32px; width:90%; max-width:380px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
        <div style="margin:0 auto 16px; width:64px; height:64px; border-radius:50%; border:3px solid #28a745; display:flex; align-items:center; justify-content:center;">
            <span style="color:#28a745; font-size:32px;">&#10003;</span>
        </div>
        <h5 style="margin-bottom:8px;">Succès !</h5>
        <p id="orientationSuccessDetails" style="color:#666; margin-bottom:20px;"></p>
        <button id="closeOrientationSuccess" class="btn btn-primary" style="width:100%;">OK</button>
    </div>
</div>

<script>
document.getElementById('orientationForm').addEventListener('submit', function (e) {
    e.preventDefault();

    var form = e.target;
    var btn = document.getElementById('orientationBtn');
    var errorsBox = document.getElementById('orientation-ajax-errors');

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
            document.getElementById('orientationSuccessDetails').textContent = result.data.name;
            document.getElementById('orientationSuccessOverlay').style.display = 'flex';
            form.reset();
        } else if (result.status === 422) {
            var errors = result.data.errors || {};
            Object.keys(errors).forEach(function (field) {
                var span = document.querySelector('[data-error-for="' + field + '"]');
                var input = document.getElementById('orientation_' + field) || document.getElementById(field);
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

document.getElementById('closeOrientationSuccess').addEventListener('click', function () {
    document.getElementById('orientationSuccessOverlay').style.display = 'none';
    window.location.reload();
});
</script>
