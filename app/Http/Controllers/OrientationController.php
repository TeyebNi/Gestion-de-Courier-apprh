<?php

namespace App\Http\Controllers;

use App\Models\Orientation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrientationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
      public function index(Request $request)
    {
         
      $data=$request->all();
      $client['orientation']=Orientation::orderby('id','asc')->paginate(50);
      return view('orientation.index', $client);
    
    }



    /**
     * Show the form for creating a new resource.
     */
     public function exportPDF4()
    { 
      $data =Orientation::all();
      view()->share('data',$data);
      $pdf = PDF::loadView('admin.show-pdf');
      return $pdf->download('data.pdf');
    }
   public function exportPDF()
{
    // جلب البيانات من قاعدة البيانات
    $data = Tabdepot::all();

    // تمرير البيانات مباشرة إلى الـ view
    $pdf = Pdf::loadView('invoice', ['data' => $data]);

    // تنزيل الملف
    return $pdf->download('invoice.pdf');
}


    public function exportPDF1()
    {
        // تحميل الـ view مع البيانات
        $pdf = Pdf::loadView('report', [
            'title' => 'Rapport de Test',
            'author' => 'Mohamed'
        ]);

        // تنزيل الملف باسم report.pdf
        return $pdf->download('report.pdf');
    }
   

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Orientation::create([ 

         'name'=>$request->name,
      ]);
      session()->flash('success', 'les donnees successfully enregistre.');
             
     return redirect()->route('orientation.index')->with('succes',' Orientation saved');
    }
    /**
     * Display the specified resource.
     */
    public function show(orientation $orientation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(orientation $orientation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, orientation $orientation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(orientation $orientation)
    {
        //
    }
}
