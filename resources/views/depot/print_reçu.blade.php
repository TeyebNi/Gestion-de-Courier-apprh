<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu #{{ $detailf->id }}</title>
    <style>
        body { font-family: sans-serif; padding: 20px; color: #000; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .header-table td { border: none; padding: 0; vertical-align: middle; }
        .header-table .fr { text-align: left; font-size: 13px; line-height: 1.6; color: #000; font-weight: 600; }
        .header-table .ar { text-align: right; font-size: 14px; line-height: 1.8; direction: rtl; color: #000; font-weight: 600; }
        .header-table .logo { text-align: center; width: 110px; }
        .header-table img { width: 90px; height: 90px; }
        hr { border: none; border-top: 1px solid #000; margin: 8px 0 0 0; }
        .datetime { text-align: left; font-size: 12px; font-weight: 600; color: #000; margin: 6px 0 20px 0; }
        h1.title { text-align: center; margin: 15px 0 30px 0; font-size: 22px; color: #000; }
        h1.title .num { font-weight: normal; font-size: 18px; }
        table.data { width: 100%; border-collapse: collapse; color: #000; table-layout: fixed; }
        table.data, table.data th, table.data td {
            border: 1px solid #000;
            padding: 12px;
            color: #000;
            white-space: nowrap;
            overflow: hidden;
        }
        table.data th { background: #f2f2f2; text-align: left; font-weight: 700; font-size: 15px; }
        table.data td { font-size: 15px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="fr">
                <strong>Honneur - Fraternité - Justice</strong><br>
                République Islamique de Mauritanie<br>
                Wilaya de Nouakchott Ouest<br>
                Mougataa de Tevragh Zeina<br>
                Commune de Tevragh Zeina
            </td>
            <td class="logo">
                <img src="{{ public_path('images/logo-tvz.png') }}" alt="Logo">
            </td>
            <td class="ar">
                <strong>شرف - إخاء - عدل</strong><br>
                الجمهورية الإسلامية الموريتانية<br>
                ولاية انواكشوط الغربية<br>
                مقاطعة تفرغ زينه<br>
                بلدية تفرغ زينه
            </td>
        </tr>
    </table>
    <hr>
    <div class="datetime">{{ now()->format('d/m/Y') }}, {{ now()->format('H:i') }}</div>

    <h1 class="title">Reçu de Dépôt <span class="num">N° {{ $detailf->id }}</span></h1>

    <table class="data">
        <colgroup>
            <col style="width: 14%;">
            <col style="width: 20%;">
            <col style="width: 18%;">
            <col style="width: 14%;">
            <col style="width: 16%;">
            <col style="width: 18%;">
        </colgroup>
        <tr>
            <th>Type</th>
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

</body>
</html>