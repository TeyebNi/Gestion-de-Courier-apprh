<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
 <title>MD Bootstrap Example</title>
 <!-- Bootstrap core CSS -->
 <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
 <!-- Material Design Bootstrap CSS -->
 <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.20.0/css/mdb.min.css" rel="stylesheet">
</head>
<body>
 <div class="container text-left mt-5">
   <h1 class="text-primary">Gestion Ressouce Humaine</h1>
   <button class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Nouveau Employeee</button>
<br>
   <table id="example2" class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>N°</th>
                <th>Id</th>
                <th>PersonID</th>
 <th>NNI</th>
 <th>Nom</th>
 <th>Fonction</th>
<th>TypeContrat</th>
 <th>Tel</th>
 <th>DateNaissance</th>
 <th>LieuNaissance</th>
 <th>DateDebut</th>
 <th>DateFin</th>
 <th>User</th>
 <th>Observ </th>
 <th>Statut</th>
 <th>Action</th>
                </tr>
                </thead>
                <tbody>            
 @foreach($remarque as $key=>$remarque)
 <tr>
 <td>{{++$key}}</td>
    <td>{{$remarque->id}}</td>
 <td>{{$remarque->PersonID}}</td>
 <td>{{$remarque->nni}}</td>
 <td>{{$remarque->nom}}</td>
 <td>{{$remarque->fonctio}}</td>
 <td>{{$remarque->typecontrat}}</td>
 <td>{{$remarque->tel}}</td>
 <td>{{$remarque->datenaiss}}</td>
 <td>{{$remarque->dateness}}</td>
 <td>{{$remarque->debutcontrat}}</td>
 <td>{{$remarque->fincontrat}}</td>
 <td>{{$remarque->usermodif}}</td>
 <td>{{$remarque->obsv}}</td>
<td>{{$remarque->statut}}</td>
<td><a href="" type="button" class="btn btn-success"> Show</a> 
 <a  type="button" class="btn btn-info"> Edit</a> 
 <a  type="button" class="btn btn-danger"> Delete</a></td>
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
        <h5 class="modal-title" id="exampleModalLabel">Depot de Demande</h5>
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
</html>