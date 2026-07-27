<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu #{{ $detailf->id }}</title>
    <style>
        body { font-family: sans-serif; padding: 30px; color: #000; }
        .header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
        .header .fr { text-align: left; font-size: 12px; line-height: 1.5; white-space: nowrap; color: #000; font-weight: 600; }
        .header .ar { text-align: right; font-size: 12px; line-height: 1.5; white-space: nowrap; direction: rtl; color: #000; font-weight: 600; }
        .header img { width: 80px; height: 80px; object-fit: contain; flex-shrink: 0; }
        hr { border: none; border-top: 1px solid #000; margin: 10px 0 25px 0; }
        h1 { text-align: center; margin-bottom: 5px; font-size: 20px; color: #000; }
        .subtitle { text-align: center; color: #000; margin-bottom: 25px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; color: #000; }
        table, th, td { border: 1px solid #000; padding: 10px; color: #000; }
        th { background: #f2f2f2; text-align: left; font-weight: 700; }
        .print-btn { text-align: center; margin-bottom: 20px; }
        @media print { .print-btn { display: none; } }
        .footer { margin-top: 30px; text-align: right; color: #000; font-size: 13px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="print-btn">
        <button onclick="window.print()">Imprimer</button>
    </div>

    <div class="header">
        <div class="fr">
            <strong>Honneur - Fraternité - Justice</strong><br>
            République Islamique de Mauritanie<br>
            Wilaya de Nouakchott Ouest<br>
            Mougataa de Tevragh Zeina<br>
            Commune de Tevragh Zeina
        </div>

        <img src="{{ asset('images/logo-tvz.png') }}" alt="Logo">

        <div class="ar">
            <strong>شرف - إخاء - عدل</strong><br>
            الجمهورية الإسلامية الموريتانية<br>
            ولاية انواكشوط الغربية<br>
            مقاطعة تفرغ زينه<br>
            بلدية تفرغ زينه
        </div>
    </div>
    <hr>

    <h1>Reçu de Dépôt</h1>
    <div class="subtitle">N° {{ $detailf->id }}</div>

    <table>
        <tr>
            <th>Type de Demande</th>
            <th>Nom</th>
            <th>NNI</th>
            <th>Tel</th>
            <th>Adresse</th>
            <th>Date</th>
        </tr>
        <tr>
            <td>{{ $detailf->typdm }}</td>
            <td>{{ $detailf->nom }}</td>
            <td>{{ $detailf->nni }}</td>
            <td>{{ $detailf->tel }}</td>
            <td>{{ $detailf->adresse }}</td>
            <td>{{ $detailf->daterecp }}</td>
        </tr>
    </table>

    <div class="footer">
        {{ now()->format('d/m/Y H.i.s') }}
    </div>
    <script>
        window.onload = function () {
            window.print();
        };
    </script>
</body>
</html>
