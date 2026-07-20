
@extends('layouts.admin')

@section('content')


<section class="content">
      <div class="container-fluid">
        <div class="row">
          <!-- left column -->
          <div class="col-md-6">
          <div class="form-group">
          <form  method="get" action="/search" >
        <div class="input-group-prepend">
        <input type="search" class="form-control"  id="search" name="search" placeholder=" Rechercher">
        <span class="input-group-text">
        <button type="submit" class="btn btn-success">Recherche</button>
        </span>
        </div>
</form>
            </div>
            </div>
<table >  
 <thead>
 <tr>
 <th>Name</th>
 <th>Email</th>
 <th>Username</th>
 <th>Code</th>
 <th>Image</th>
 </tr>
 
 

 </thead>
 
</table>

@endsection