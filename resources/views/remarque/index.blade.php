@extends('layouts.app')


@section('content')
    <div class="card-footer">
<a href="" class="btn btn-primary">Export PDF</a>

 <div class="container text-left mt-5">
   <h1 class="text-primary">Gestion Ressouce Humaine</h1>
   <button class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Nouveau Employeee</button>
<br>
   <table id="example2" class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>N°</th>
                    <th>Matricul</th>
 <th>NNI</th>
 <th>Nom</th>
 <th>Fonction</th>
<th>TypeContrat</th>
 
 <th>Action</th>
                </tr>
                </thead>
                <tbody>            
 @foreach($remarque as $key=>$remarque)
 <tr>
 <td>{{++$key}}</td>
 <td>{{$remarque->id}}</td>
 <td>{{$remarque->nni}}</td>
 <td>{{$remarque->nom}}</td>
 <td>{{$remarque->fonction}}</td>
 <td>{{$remarque->typecontrat}}</td>
<td><a  data-id="{{$remarque->id}}" data-nni="{{$remarque->nni}}"  data-nom="{{$remarque->nom}}" data-fonction="{{$remarque->fonction}}" data-typecontrat="{{$remarque->typecontrat}}" data-tel="{{$remarque->tel}}" data-toggle="modal" data-target="#exampleModal-edit" type="button" class="btn btn-success"> Show</a>
    <a  data-id="{{$remarque->id}}" data-nni="{{$remarque->nni}}"  data-nom="{{$remarque->nom}}" data-fonction="{{$remarque->fonction}}" data-typecontrat="{{$remarque->typecontrat}}" data-tel="{{$remarque->tel}}" data-toggle="modal" data-target="#exampleModal-edit" type="button" class="btn btn-info"> Edit</a> 
 <a   data-id="{{$remarque->id}}" data-toggle="modal" data-target="#exampleModal-delete"  type="button" class="btn btn-danger"> Delite</a></td>
 </tr>
 @endforeach
 </tbody>
 

 </thead>
 
</table>
<!-- New Empoyee -->


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

        <form action="{{route('remarque.store')}}" method="post">
        @csrf
       
        <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Code</span>
      </div>
      <input type="text" class="form-control" name="PersonID" placeholder="Entrer Code">
     
                  @error('PersonID')
                    <span  class="text-danger">{{$message}}</span>
                    @enderror
                   
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">NNI</span>
      </div>
      <input type="text" class="form-control" name="nni" placeholder="Entrer NNI">
      
                     @error('nni')
                    <span  class="text-danger">{{$message}}</span>
                    @enderror
                   
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Nom</span>
      </div>
      <input type="text" class="form-control" name="nom" placeholder="Entrer Nom">
     
                    @error('nom')
                    <span  class="text-danger">{{$message}}</span>
                    @enderror
    </div>
      <br>
      
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Fonction</span>
      </div>
      <input type="text" class="form-control" name="fonction" placeholder="Entrer Fonction">
      
                    @error('fonction')
                    <span  class="text-danger">{{$message}}</span>
                    @enderror
                   
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Type de Contrat</span>
      </div>
      <input type="text" class="form-control" name="typecontrat" placeholder="Entrer Type de Contrat">
      
                    @error('typecontrat')
                    <span  class="text-danger">{{$message}}</span>
                    @enderror
                    
    </div>
     <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Tel</span>
      </div>
      <input type="text" class="form-control" name="tel" placeholder="Entrer Tel">
      
                     @error('tel')
                    <span  class="text-danger">{{$message}}</span>
                    @enderror
                    
    </div>
     <br>
   <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Date Naissance</span>
      </div>
      <input type="date" class="form-control" name="datenaiss" placeholder="Entrer Date de Naissance">
     
                    @error('datenaiss')
                    <span  class="text-danger">{{$message}}</span>
                    @enderror
                   
    </div>
    <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">lieu de Naissance</span>
      </div>
      <input type="text" class="form-control" name="lieuness" placeholder="lieu de Naissance">
      
                     @error('lieuness')
                    <span  class="text-danger">{{$message}}</span>
                    @enderror
                   
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Date Recrutment</span>
      </div>
      <input type="date" class="form-control" name="debutcontrat" placeholder="Entrer DateRecrutment">
     
                     @error('debutcontrat')
                    <span  class="text-danger">{{$message}}</span>
                    @enderror
                   
    </div>
      <br>
      
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Date Fin de Contrat</span>
      </div>
      <input type="date" class="form-control" name="fincontrat" placeholder="Entrer Date Fin de Contrat">
     
                     @error('fincontrat')
                    <span  class="text-danger">{{$message}}</span>
                    @enderror
                    
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">User</span>
      </div>
      <input type="text" class="form-control" name="usermodif" placeholder="Entrer user">
      
                     @error('usermodif')
                    <span  class="text-danger">{{$message}}</span>
                    @enderror
                    
    </div>
     <br>
    <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Obervation/span>
      </div>
      <input type="text" class="form-control" name="obsv" placeholder="Entrer OBS">
      
                     @error('obsv')
                    <span  class="text-danger">{{$message}}</span>
                    @enderror
                    
    </div>
    <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Statut</span>
      </div>
      <input type="text" class="form-control" name="statut" placeholder="Entrer statut">
      
                    @error('statut')
                    <span  class="text-danger">{{$message}}</span>
                    @enderror
                    
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
<!-- edit Empoyee -->


<!-- Modal -->
<div class="modal fade left" id="exampleModal-edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog  modal-notify modal-lg modal-right modal-success" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Ajouter Employee</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <form action="{{route('remarque.store')}}" method="post">
        @csrf
       
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
        <span class="input-group-text">Fonction</span>
      </div>
      <input type="text" class="form-control" name="fonction" placeholder="Entrer Fonction">
      
                    <span  class="text-danger"></span>
                   
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Type de Contrat</span>
      </div>
      <input type="text" class="form-control" name="typecontrat" placeholder="Entrer Type de Contrat">
      
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
        <span class="input-group-text">Date Naissance</span>
      </div>
      <input type="date" class="form-control" name="lieunaiss" placeholder="Entrer Date de Naissance">
     
                    <span  class="text-danger"></span>
                   
    </div>
    <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">lieu de Naissance</span>
      </div>
      <input type="text" class="form-control" name="lieuness" placeholder="lieu de Naissance">
      
                    <span  class="text-danger"></span>
                   
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Date Recrutment</span>
      </div>
      <input type="date" class="form-control" name="debutcontrat" placeholder="Entrer DateRecrutment">
     
                    <span  class="text-danger"></span>
                   
    </div>
      <br>
      
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Date Fin de Contrat</span>
      </div>
      <input type="date" class="form-control" name="	fincontrat" placeholder="Entrer Date Fin de Contrat">
     
                    <span  class="text-danger"></span>
                    
    </div>
      <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">User</span>
      </div>
      <input type="text" class="form-control" name="usermodif" placeholder="Entrer user">
      
                    <span  class="text-danger"></span>
                    
    </div>
     <br>
    <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Obervation/span>
      </div>
      <input type="text" class="form-control" name="obsv" placeholder="Entrer OBS">
      
                    <span  class="text-danger"></span>
                    
    </div>
    <br>
      <div class="input-group">
        <div class="input-group-prepend">
        <span class="input-group-text">Statut</span>
      </div>
      <input type="text" class="form-control" name="statut" placeholder="Entrer statut">
      
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
 <!-- jQuery -->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
 <!-- Bootstrap JS -->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
 <!-- Material Design Bootstrap JS -->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.20.0/js/mdb.min.js"></script>
</body>
@endsection
</html>
