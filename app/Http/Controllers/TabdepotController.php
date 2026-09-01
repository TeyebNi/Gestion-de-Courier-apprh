<?php

namespace App\Http\Controllers;

use App\Models\Tabdepot;
use App\Models\Typedem;
use App\Models\Orientation;
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
        if (! auth()->user()->canAccessDepot()) {
            abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
        }

        $search = $request->search;
        $typedem = Typedem::all();
        $orientations = Orientation::orderBy('name')->get();

        $tabdepot = Tabdepot::query()
            ->when($search, function ($query) use ($search) {
                $query->where('nom', 'like', "%{$search}%")
                      ->orWhere('nni', 'like', "%{$search}%")
                      ->orWhere('tel', 'like', "%{$search}%")
                      ->orWhere('adresse', 'like', "%{$search}%")
                      ->orWhere('typdm', 'like', "%{$search}%")
                      ->orWhere('objet', 'like', "%{$search}%")
                      ->orWhere('reference', 'like', "%{$search}%");
            })
            ->orderby('id', 'desc')
            ->paginate(5)
            ->appends(['search' => $search]);

        return view('depot.index', compact('tabdepot', 'typedem', 'orientations', 'search'));
    }

    public function exportExcel(Request $request)
    {
        if (! auth()->user()->canAccessDepot()) {
            abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
        }

        $tabdepots = Tabdepot::orderby('id', 'asc')->get();

        return $this->streamCsv(
            $tabdepots,
            ['N°', 'Nom', 'NNI', 'Téléphone', 'Adresse', 'Type demande', 'Objet', 'Référence', 'Origine', 'Détails Origine', 'Date réception', 'Statut'],
            fn ($t, $i) => [$i + 1, $t->nom, $t->nni, $t->tel, $t->adresse, $t->typdm, $t->objet, $t->reference, $t->origine, $t->origine_detail, $t->daterecp, $t->statutLabel()],
            'depot_demandes'
        );
    }

    public function print_facture($idt)
    {
        $detailf = Tabdepot::where('id', $idt)->firstOrFail();

        $html = view('depot.print_reçu', compact('detailf'))->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('recu_depot_' . $detailf->id . '.pdf', \Mpdf\Output\Destination::DOWNLOAD), 200)
            ->header('Content-Type', 'application/pdf');
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
        if (! auth()->user()->canAccessDepot()) {
            abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
        }

        $isInterne = $request->origine === 'interne';
        $isInstitution = $request->type_expediteur === 'institution';

        $request->validate([
            'typdm' => [$isInterne ? 'required' : 'nullable', 'string', 'max:255'],
            'objet' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:100'],
            'origine_detail' => [$isInstitution ? 'required' : 'nullable', 'string', 'max:255'],
            'nom' => [$isInstitution ? 'nullable' : 'required', 'string', 'max:255'],
            'nni' => ['nullable', 'digits:10'],
            'tel' => [$isInstitution ? 'nullable' : 'required', 'digits:8'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'daterecp' => ['nullable', 'date', 'before_or_equal:today'],
            'piece_jointe' => [$isInstitution ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ], [
            'typdm.required' => 'Le type de demande est obligatoire pour une demande interne.',
            'origine_detail.required' => "Le nom de l'institution est obligatoire.",
            'nom.required' => 'Le nom est obligatoire.',
            'nni.digits' => 'Le NNI doit contenir exactement 10 chiffres.',
            'tel.required' => 'Le téléphone est obligatoire.',
            'tel.digits' => 'Le téléphone doit contenir exactement 8 chiffres.',
            'daterecp.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'piece_jointe.required' => 'La pièce jointe (scan du document) est obligatoire pour une institution.',
            'piece_jointe.mimes' => 'La pièce jointe doit être un PDF, JPG ou PNG.',
            'piece_jointe.max' => 'La pièce jointe ne doit pas dépasser 10 Mo.',
        ]);

        $pieceJointePath = null;
        if ($request->hasFile('piece_jointe')) {
            $pieceJointePath = $request->file('piece_jointe')->store('pieces_jointes', 'public');
        }

        $demande = Tabdepot::create([
            'typdm' => $request->typdm,
            'objet' => $request->objet,
            'reference' => $request->reference,
            'origine' => $request->origine,
            'origine_detail' => $request->origine_detail,
            'type_expediteur' => $request->type_expediteur,
            'piece_jointe' => $pieceJointePath,
            'nom' => $request->nom,
            // NNI et adresse n'ont pas de sens pour une demande interne (agent municipal).
            'nni' => $isInterne ? null : $request->nni,
            'tel' => $request->tel,
            'adresse' => $isInterne ? null : $request->adresse,
            'daterecp' => $request->daterecp ?: now()->format('Y-m-d'),
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
    if (! auth()->user()->canAccessDepot()) {
        abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
    }

    $isInterne = $request->origine === 'interne';
    $isInstitution = $request->type_expediteur === 'institution';

    $request->validate([
        'typdm' => [$isInterne ? 'required' : 'nullable', 'string', 'max:255'],
        'objet' => ['nullable', 'string', 'max:255'],
        'reference' => ['nullable', 'string', 'max:100'],
        'origine' => ['nullable', 'in:interne,externe'],
        'origine_detail' => [$isInstitution ? 'required' : 'nullable', 'string', 'max:255'],
        'type_expediteur' => ['nullable', 'in:personne,institution'],
        'piece_jointe' => [
            ($isInstitution && !$tabdepot->piece_jointe && !$request->hasFile('piece_jointe')) ? 'required' : 'nullable',
            'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240',
        ],
        'nom' => [$isInstitution ? 'nullable' : 'required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
        'nni' => ['nullable', 'digits:10'],
        'tel' => [$isInstitution ? 'nullable' : 'required', 'digits:8'],
        'adresse' => ['nullable', 'string', 'max:255'],
        'daterecp' => ['nullable', 'date', 'before_or_equal:today'],
    ], [
        'nom.regex' => 'Le nom ne doit contenir que des lettres.',
        'nom.required' => 'Le nom est obligatoire.',
        'origine_detail.required' => "Le nom de l'institution est obligatoire.",
        'nni.digits' => 'Le NNI doit contenir exactement 10 chiffres.',
        'tel.required' => 'Le téléphone est obligatoire.',
        'tel.digits' => 'Le téléphone doit contenir exactement 8 chiffres.',
        'daterecp.before_or_equal' => 'La date ne peut pas être dans le futur.',
        'piece_jointe.required' => 'La pièce jointe (scan du document) est obligatoire pour une institution.',
        'piece_jointe.mimes' => 'La pièce jointe doit être un PDF, JPG ou PNG.',
        'piece_jointe.max' => 'La pièce jointe ne doit pas dépasser 10 Mo.',
    ]);

    $data = $request->only(['typdm', 'objet', 'reference', 'origine', 'origine_detail', 'type_expediteur', 'nom', 'nni', 'tel', 'adresse', 'daterecp']);

    if ($isInterne) {
        // NNI et adresse n'ont pas de sens pour une demande interne (agent municipal).
        $data['nni'] = null;
        $data['adresse'] = null;
    }

    if ($request->hasFile('piece_jointe')) {
        if ($tabdepot->piece_jointe) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($tabdepot->piece_jointe);
        }
        $data['piece_jointe'] = $request->file('piece_jointe')->store('pieces_jointes', 'public');
    }

    $tabdepot->update($data);

    return redirect()->route('depot.index')->with('success', "Demande de {$tabdepot->nom} modifiée avec succès.");
}

    public function destroy(Tabdepot $tabdepot)
{
    if (! auth()->user()->canAccessDepot()) {
        abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
    }

    $nom = $tabdepot->nom;
    $tabdepot->delete();

    return redirect()->route('depot.index')->with('success', "Demande de {$nom} supprimée avec succès.");
}
    
}