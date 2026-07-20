<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Http\Requests\AdminRequest;
use PDF;

class AdminController extends Controller
{
    public function index(Request $request)
    {
      $data=$request->all();
        
        return view('admin.index', compact($data));
    }
    public function getadmin()
    {
        return Admin::get();
    }
    public function create()
    {$search =$request->search;

      $admin =Admin::where(function($query) use ($search){
        $query->where('name','like',"%search%");
    })
    ->get();
      return view('admin.show', compact('admin','search'));
        return view('admin.create');
    }
    public function show()
    {
        $admin =Admin::all();
        return view('admin.show', compact('admin'));
    }

    public function exportpdf()
    { 
      $data =Admin::all();
      view()->share('data',$data);
      $pdf = PDF::loadView('admin.show-pdf');
      return $pdf->download('data.pdf');
    }

    public function store(AdminRequest $request) 
    {  
      $file_extension=$request->photo->getClientOriginalExtension();
      $file_name =time().'.'.$file_extension;
      $path='uploads';
      $request->photo->move($path,$file_name);
      
      Admin::create([ 
        'name'=>$request->name,
        'email'=>$request->email,
        'username'=>$request->username,
        'password'=>$request->password,
        'com_code'=>$request->com_code,
        'photo'=>$file_name,
      ]);
             
     return redirect()->route('admin.index')->with('succes',' admin saved');
        
    }

    public function search(Request $request)
    {
      $request->validate([
       'q'=>'required'
      ]);

        $q=$request->q;
        $filteredAdmin=Admin::where('name','like','%'.$q.'%')->orwhere('name','like','%'.$q.'%')->get();
       if($filteredAdmin){
        return view('admin.show')->with(['admin'=>$filteredAdmin]);
       }
        else{
          return redirect()->with(['status'=>'search failed ,,try again ']);
        }
    }  

}
