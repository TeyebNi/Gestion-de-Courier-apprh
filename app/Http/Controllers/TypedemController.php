<?php

namespace App\Http\Controllers;

use App\Models\Typedem;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Traits\ExportsCsv;

class TypedemController extends Controller
{
    use ExportsCsv;

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
        $client['typedem'] = $query->paginate(5)->appends(['search' => $search]);
        return view('typedem.index', $client)->with('search', $search);
    }



    /**
     * Show the form for creating a new resource.
     */
     public function exportExcel()
    {
        $typedems = Typedem::orderby('id', 'asc')->get();

        return $this->streamCsv(
            $typedems,
            ['N°', 'Type de demande'],
            fn ($t, $i) => [$i + 1, $t->name],
            'types_demande'
        );
    }

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
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => "Veuillez entrer le type de demande.",
        ]);

        $typedem = Typedem::create([
            'name' => $request->name,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Le type de demande « {$typedem->name} » a été ajouté avec succès.",
                'name' => $typedem->name,
            ]);
        }

        session()->flash('success', 'les donnees successfully enregistre.');

        return redirect()->route('typedem.index')->with('succes', ' Type Demande saved');
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
    public function update(Request $request, Typedem $typedem)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => "Veuillez entrer le type de demande.",
        ]);

        $typedem->update([
            'name' => $request->name,
        ]);

        return redirect()->route('typedem.index')->with('success', "Type de demande #{$typedem->id} modifié avec succès.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Typedem $typedem)
    {
        $id = $typedem->id;
        $typedem->delete();

        return redirect()->route('typedem.index')->with('success', "Type de demande #{$id} supprimé avec succès.");
    }
}