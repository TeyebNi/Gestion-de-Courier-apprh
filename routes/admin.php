<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::group(['namespace'=>'Admin','prefix'=>'admin','middleware'=>'auth:admin'], function (){
 Route::get('dashboard',[DashboardController::class,'index'])->name('admin.dashboard');
 

    });



Route::group(['namespace'=>'Admin','prefix'=>'admin','middleware'=>'guest:admin'], function (){
Route::get('login',[LoginController::class,'show_login_view'])->name('admin.showlogin');
Route::post('login',[LoginController::class,'login'])->name('admin.login');
Route::get('admin',[AdminController::class,'index'])->name('admin.index');
 Route::post('admin',[AdmintController::class,'store'])->name('admin.store');
});

