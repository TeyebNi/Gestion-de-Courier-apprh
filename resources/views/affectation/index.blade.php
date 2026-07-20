
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

         <form action="{{route('affectation.store')}}" method="post">
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
     <select   class="form-control" name="sevice">
      <option value="">Select Orientation</option>
      @foreach($orientation as $c)      
        <option value="{{$c->name}}">{{$c->name}}</option>
    @endforeach
    </select>
      </div>
       <br>
   <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Date </span>
      </div>
      <input type="date" class="form-control" name="dateaff" placeholder="Entrer Date">
     
                    <span  class="text-danger"></span>
                   
    </div>
     <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Demande</span>
      </div>
     <select   class="form-control" name="iddmd">
      <option value="">Select Code Demande</option>
      @foreach($tabdepot as $c)      
        <option value="{{$c->typdm}}">{{$c->id}}</option>
    @endforeach
    </select>
      </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Ajouter</button>
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
      <input type="date" class="form-control" name="dateaff" placeholder="Entrer Date">
     
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
