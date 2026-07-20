<?php

namespace App\Http\Controllers;

use App\Models\Remarque;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RemarqueRequest;
use Barryvdh\DomPDF\Facade\Pdf;


class RemarqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         
      $data=$request->all();
      $client['remarque']=Remarque::orderby('id','asc')->paginate(50);
        return view('remarque.index', $client);
    
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }


    public function exportPDF()
{
    // جلب البيانات من قاعدة البيانات
    $data = Remarque::all();

    // تمرير البيانات مباشرة إلى الـ view
    $pdf = Pdf::loadView('invoice', ['data' => $data]);

    // تنزيل الملف
    return $pdf->download('invoice.pdf');
}
    public function exportPDF4()
    { 
      $data =Remarque::all();
      view()->share('data',$data);
      $pdf = PDF::loadView('admin.show-pdf');
      return $pdf->download('data.pdf');
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
    public function store(RemarqueRequest $request)
    {
        
      Remarque::create([ 
          'PersonID'=>$request->PersonID,
          'nni'=>$request->nni, 
          'nom'=>$request->nom,
          'fonction'=>$request->fonction,
          'typecontrat'=>$request->typecontrat,
          'tel'=>$request->tel,
           'datenaiss'=>$request->datenaiss,
           'lieuness'=>$request->lieuness,
           'debutcontrat'=>$request->debutcontrat,
           'fincontrat'=>$request->fincontrat,
           'usermodif'=>$request->usermodif,
            'obsv'=>$request->obsv,
            'statut'=>$request->statut,
      ]);
      session()->flash('success', 'les donnees successfully enregistre.');
             
     return redirect()->route('remarque.index')->with('succes',' Employee saved');
    }

    /**
     * Display the specified resource.
     */
    public function show(Remarque $remarque)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Remarque $remarque)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Remarque $remarque)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Remarque $remarque)
    {
        //
    }
}
