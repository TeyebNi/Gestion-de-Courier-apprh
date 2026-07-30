
@extends('layouts.admin')
  
@section('content')
<div class="card-footer">
<a href="{{('exportpdf')}}" class="btn btn-primary">Export PDF</a>
      </div>
<div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">DataTable </h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <table id="example2" class="table table-bordered table-hover">
                <thead>
                <tr>
                <td>id</td>
                <th>Name</th>
 <th>Email</th>
 <th>Username</th>
 <th>Code</th>
 <th>Image</th>
 <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($admin as $d)
 <tr>
 <td>{{$d->id}}</td>
 <td>{{$d->name}}</td>
 <td>{{$d->email}}</td>
 <td>{{$d->username}}</td>
 <td>{{$d->com_code}}</td>
 <td>
    <img src="{{asset('uploads/'.$d->photo)}}" width="70px" height="70px" alt="img">
</td>
<td><a href="" type="button" class="btn btn-success"> Show</a> 
  
  </td>
 
 </tr>
 @endforeach
 </tbody>
 

 </thead>
 
</table>


@endsection
