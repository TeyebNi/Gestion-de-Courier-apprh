
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
                                 <a href="{{('invoice')}}">Export pdfp</a>   
        
                                </p>
                              
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class=" text-primary">
                                           <th>
                                                N°
                                            </th>
                                            <th>
                                                Code
                                            </th>
                                            <th>
                                                Type de Demande
                                            </th>
                                            <th>
                                                Nom
                                            </th>
                                            <th class="text-right">
                                                NNI
                                            </th>
                                            <th class="text-right">
                                                Tel
                                            </th>
                                             <th class="text-right">
                                               Adresse
                                            </th>
                                            <th class="text-right">
                                                Date
                                            </th>
                                            <th class="text-right">
                                                Action
                                            </th>
                                        </thead>
                                        <tbody>
<<<<<<< HEAD
                                          @foreach($tabdepots as $key=>$tabdepot)
=======
                                          @foreach($tabdepot as $key=>$item)
>>>>>>> 1817cc7f428a827ca79743958efb01e013ccb3d4
                                            <tr>
                                                <td>
                                                  {{++$key}}
                                                </td>
                                                <td>
                                                  {{$item->id}}
                                                </td>
                                                <td>
                                                   {{$item->typdm}}
                                                </td>
                                                <td class="text-right">
                                                    {{$item->nom}}
                                                </td>
                                                  <td class="text-right">
                                                   {{$item->nni}}
                                                </td>
                                                <td class="text-right">
                                                    {{$item->tel}}
                                                </td>
                                                  <td class="text-right">
                                                    {{$item->adresse}}
                                                </td>
                                                  <td class="text-right">
                                                    {{$item->daterecp}}
                                                </td>
<<<<<<< HEAD
                                                <td> <a href ="{{route('depot.print_reçu',$tabdepot->id)}}"  type="button"  class="btn btn-success"> Print</a>
    <a  data-id="{{$tabdepot->id}}" data-typdm="{{$tabdepot->typdm}}" data-nom="{{$tabdepot->nom}}" data-nni="{{$tabdepot->nni}}" data-adresse="{{$tabdepot->adresse}}"  data-tel="{{$tabdepot->tel}}" data-daterecp="{{$tabdepot->daterecp}}" data-toggle="modal" data-target="#exampleModal-edit" type="button" class="btn btn-info"> Edit</a> 
 <a   data-id="{{$tabdepot->id}}" data-toggle="modal" data-target="#exampleModal-delete"  type="button" class="btn btn-danger"> Delete</a></td>
=======
                                                <td><a  data-id="{{$item->id}}" data-typdm="{{$item->typdm}}" data-nom="{{$item->nom}}" data-nni="{{$item->nni}}" data-adresse="{{$item->adresse}}"  data-tel="{{$item->tel}}" data-daterecp="{{$item->daterecp}}"   data-toggle="modal" data-target="#exampleModal-show" type="button" class="btn btn-success"> Show</a>
    <a  data-id="{{$item->id}}" data-typdm="{{$item->typdm}}" data-nom="{{$item->nom}}" data-nni="{{$item->nni}}" data-adresse="{{$item->adresse}}"  data-tel="{{$item->tel}}" data-daterecp="{{$item->daterecp}}" data-toggle="modal" data-target="#exampleModal-edit" type="button" class="btn btn-info"> Edit</a> 
 <a   data-id="{{$item->id}}" data-toggle="modal" data-target="#exampleModal-delete"  type="button" class="btn btn-danger"> Delete</a></td>
>>>>>>> 1817cc7f428a827ca79743958efb01e013ccb3d4
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                    {{ $tabdepots->appends(request()->query())->links() }}
                </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card card-plain">
                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    {{ $tabdepot->links() }}
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
        <h5 class="modal-title" id="exampleModalLabel">Ajouter Employee</h5>
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
      <option value="">Select Type de Demande</option>
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
        <h5 class="modal-title" id="exampleModalLabel">Modifier Demande</h5>
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
      <input type="text" class="form-control" name="PersonID" placeholder="Entrer Code">
     
                    <span  class="text-danger"></span>
                   
    </div>
    <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Type de Demande</span>
      </div>
     <select   class="form-control" name="typdm">
      <option value="">Select Type de Demande</option>
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
     <select   class="form-control" name="typdm">
      <option value="">Select Type de Demande</option>
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
