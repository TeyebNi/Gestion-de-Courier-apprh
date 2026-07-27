<?php

namespace App\Http\Controllers;

use App\Models\Tabdepot;
use App\Models\Typedem;
use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Traits\ExportsCsv;

class TabdepotController extends Controller
{
    use ExportsCsv;

    protected SmsService $sms;

    public function __construct(SmsService $sms)
    {
        $this->sms = $sms;
    }

    public function index(Request $request)
    {
        $search = $request->search;
        $typedem = Typedem::all();

        $tabdepot = Tabdepot::query()
            ->when($search, function ($query) use ($search) {
                $query->where('nom', 'like', "%{$search}%")
                      ->orWhere('nni', 'like', "%{$search}%")
                      ->orWhere('tel', 'like', "%{$search}%")
                      ->orWhere('adresse', 'like', "%{$search}%")
                      ->orWhere('typdm', 'like', "%{$search}%");
            })
            ->orderby('id', 'asc')
            ->paginate(5)
            ->appends(['search' => $search]);

        return view('depot.index', compact('tabdepot', 'typedem', 'search'));
    }

    public function exportExcel(Request $request)
    {
        $tabdepots = Tabdepot::orderby('id', 'asc')->get();

        return $this->streamCsv(
            $tabdepots,
            ['N°', 'Nom', 'NNI', 'Téléphone', 'Adresse', 'Type demande', 'Date réception'],
            fn ($t, $i) => [$i + 1, $t->nom, $t->nni, $t->tel, $t->adresse, $t->typdm, $t->daterecp],
            'depot_demandes'
        );
    }

    public function print_facture($idt)
    {
        $detailf = Tabdepot::where('id', $idt)->firstOrFail();
        return view('depot.print_reçu', compact('detailf'));
    }

    public function exportPDF4()
    {
        $data = Tabdepot::all();
        view()->share('data', $data);
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
            'typdm' => $request->typdm,
            'nom' => $request->nom,
            'nni' => $request->nni,
            'tel' => $request->tel,
            'adresse' => $request->adresse,
            'daterecp' => $request->daterecp,
        ]);

        if ($demande->tel) {
            $this->sms->send(
                $demande->tel,
                "Bonjour {$demande->nom}, votre demande ({$demande->typdm}) a bien été enregistrée. Code: {$demande->id}. Commune de Tevragh Zeina."
            );
        }

        session()->flash('success', 'les donnees successfully enregistre.');

        return redirect()->route('depot.index')->with('succes', ' Demande saved');
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
    $request->validate([
        'typdm' => ['required', 'string', 'max:255'],
        'nom' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
        'nni' => ['required', 'digits:10'],
        'tel' => ['required', 'digits:8'],
        'adresse' => ['nullable', 'string', 'max:255'],
        'daterecp' => ['nullable', 'date', 'before_or_equal:today'],
    ], [
        'nom.regex' => 'Le nom ne doit contenir que des lettres.',
        'nni.digits' => 'Le NNI doit contenir exactement 10 chiffres.',
        'tel.digits' => 'Le téléphone doit contenir exactement 8 chiffres.',
        'daterecp.before_or_equal' => 'La date ne peut pas être dans le futur.',
    ]);

    $tabdepot->update($request->only(['typdm', 'nom', 'nni', 'tel', 'adresse', 'daterecp']));

    return redirect()->route('depot.index')->with('success', "Demande de {$tabdepot->nom} modifiée avec succès.");
}

    public function destroy(Tabdepot $tabdepot)
{
    $nom = $tabdepot->nom;
    $tabdepot->delete();

    return redirect()->route('depot.index')->with('success', "Demande de {$nom} supprimée avec succès.");
}
    
}