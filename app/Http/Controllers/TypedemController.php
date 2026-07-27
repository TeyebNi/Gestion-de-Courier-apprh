<?php

namespace App\Http\Controllers;

use App\Models\Typedem;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TypedemController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Typedem::orderby('id', 'asc');
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        $client['typedem'] = $query->paginate(50)->appends(['search' => $search]);
        return view('typedem.index', $client)->with('search', $search);
    }



    /**
     * Show the form for creating a new resource.
     */
     public function exportPDF4()
    { 
      $data =Typedem::all();
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
        Typedem::create([ 

         'name'=>$request->name,
      ]);
      session()->flash('success', 'les donnees successfully enregistre.');
             
     return redirect()->route('typedem.index')->with('succes',' Type Demande saved');
    }
   
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
    public function show(typedem $typedem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(typedem $typedem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, typedem $typedem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(typedem $typedem)
    {
        //
    }
}
