<?php

namespace App\Http\Controllers;

use App\Models\Tabdepot;
use App\Models\Typedem;
use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class TabdepotController extends Controller
{
    protected SmsService $sms;

    public function __construct(SmsService $sms)
    {
        $this->sms = $sms;
    }

    public function index(Request $request)
    {
      $typedem=Typedem::all();   
      $data=$request->all();
      $client['tabdepot']=Tabdepot::orderby('id','asc')->paginate(5);
      return view('depot.index', $client)->with('typedem',$typedem);
    }

     public function exportPDF4()
    { 
      $data =Tabdepot::all();
      view()->share('data',$data);
      $pdf = PDF::loadView('admin.show-pdf');
      return $pdf->download('data.pdf');
    }
   public function exportPDF()
{
    $data = Tabdepot::all();
    $pdf = Pdf::loadView('invoice', ['data' => $data]);
    return $pdf->download('invoice.pdf');
}

    public function exportPDF1()
    {
        $pdf = Pdf::loadView('report', [
            'title' => 'Rapport de Test',
            'author' => 'Mohamed'
        ]);
        return $pdf->download('report.pdf');
    }
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $demande = Tabdepot::create([
        'typdm'=>$request->typdm,
         'nom'=>$request->nom,
         'nni'=>$request->nni,
         'tel'=>$request->tel,
         'adresse'=>$request->adresse,
          'daterecp'=>$request->daterecp,
      ]);

      if ($demande->tel) {
          $this->sms->send(
              $demande->tel,
              "Bonjour {$demande->nom}, votre demande ({$demande->typdm}) a bien été enregistrée. Code: {$demande->id}. Commune de Tevragh Zeina."
          );
      }

      session()->flash('success', 'les donnees successfully enregistre.');
             
     return redirect()->route('depot.index')->with('succes',' Demande saved');
    }

    public function show(Tabdepot $tabdepot)
    {
        //
    }

    public function edit(Tabdepot $tabdepot)
    {
        //
    }

    public function update(Request $request, Tabdepot $tabdepot)
    {
        //
    }

    public function destroy(Tabdepot $tabdepot)
    {
        //
    }
}
