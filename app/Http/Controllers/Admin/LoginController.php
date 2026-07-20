<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;

class LoginController extends Controller
{ 
  public function show_login_view(){
        return view('admin.auth.login');

    }

   public function login(Request $request) {
     if(auth()->guard('admin')->attempt(['username'=>$request->input('username'),'password'=>$request->input('password')]))
    { return redirect()->route('admin.dashboard');

    }

    }
   /*
    function make_new_admin(){
    $admin=new App\Models\Admin();
    $admin->name='admine';
    $admin->email='testt@gmail.com';
    $admin->username='admin';
    $admin->password=bcrypt("admine");
    $admin->com_code=2;
    $admin->save();

    }
    */
}
