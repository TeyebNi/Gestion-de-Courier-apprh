
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

         <form action="{{route('orientation.store')}}" method="post">
        @csrf
        
        
        
   <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Orientation </span>
      </div>
   <input type="text" class="form-control" name="name" placeholder="Entrer Nom">
     
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
