<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Tabdepot;
use App\Models\Orientation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AffectationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
       public function index(Request $request)
    {
     $orientation =Orientation::all(); 
      $tabdepot =Tabdepot::all();   
      $data=$request->all();
      $client['affectation']=Affectation::orderby('id','asc')->paginate(50);
      return view('affectation.index', $client)->with('tabdepot',$tabdepot)->with('orientation',$orientation );
    
    }



    /**
     * Show the form for creating a new resource.
     * 
     */
     public function exportPDF4()
    { 
      $data =Tabdepot::all();
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
        Affectation::create([ 
        'sevice'=>$request->sevice,
         'dateaff'=>$request->dateaff,
         'iddmd'=>$request->iddmd,
         
      ]);
      session()->flash('success', 'les donnees successfully enregistre.');
             
     return redirect()->route('affectation.index')->with('succes',' Affectation saved');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tabdepot $tabdepot)
    {
        //
    }
    /**
     * Display the specified resource.
     */
   
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tabdepot $tabdepot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
   
   

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
   
    /**
     * Display the specified resource.
     */
   

    /**
     * Show the form for editing the specified resource.
     */
   
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Affectation $affectation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Affectation $affectation)
    {
        //
    }
}
