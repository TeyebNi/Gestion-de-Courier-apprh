<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche Maire #{{ $tabdepot->id }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            padding: 20px;
            color: #000;
            font-weight: 700;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .header-table .fr {
            text-align: left;
            font-size: 10px;
            line-height: 1.5;
            color: #000;
            font-weight: 700;
            width: 38%;
            padding-right: 10px;
        }

        .header-table .ar {
            text-align: right;
            font-size: 11px;
            line-height: 1.7;
            direction: rtl;
            color: #000;
            font-weight: 700;
            width: 38%;
            padding-left: 10px;
        }

        .header-table .logo {
            text-align: center;
            width: 24%;
        }

        .header-table img {
            width: 35px;
            height: 35px;
        }

        hr {
            border: none;
            border-top: 1px solid #000;
            margin: 8px 0 0 0;
        }

        .datetime {
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: #000;
            margin: 6px 0 20px 0;
        }

        h1.title {
            text-align: center;
            margin: 15px 0 25px 0;
            font-size: 20px;
            color: #000;
        }

        h1.title .num {
            font-weight: 700;
            font-size: 16px;
        }

        .details {
            width: 100%;
            margin-top: 10px;
            margin-left: 10px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table td {
            border: none;
            padding: 0 0 12px 0;
            vertical-align: top;
            font-size: 14px;
            color: #000;
            font-weight: 700;
        }

        .details-table .detail-label {
            width: 160px;
            font-weight: 700;
            white-space: nowrap;
        }

        .details-table .detail-sep {
            width: 20px;
            text-align: center;
            font-weight: 700;
        }

        .details-table .detail-value {
            text-align: left;
            font-weight: 700;
            color: #000;
        }

        .annotation-block {
            margin-top: 30px;
            margin-left: 10px;
        }

        .annotation-title {
            font-size: 13px;
            font-weight: 700;
            border-top: 1px solid #000;
            padding-top: 12px;
            color: #444;
        }
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
                <img src="{{ public_path('images/logo-tvz.png') }}" alt="Logo" width="55" height="55" style="width:55px; height:55px;">
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

    <h1 class="title">
        Fiche pour le Maire <span class="num">N° {{ $tabdepot->id }}</span>
    </h1>

    <div class="details">
        <table class="details-table">
            <tr>
                <td class="detail-label">Nom / Institution</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">{{ $tabdepot->nom ?: ($tabdepot->origine_detail ?: '—') }}</td>
            </tr>
            <tr>
                <td class="detail-label">Type de demande</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">{{ $tabdepot->typdm ?: '—' }}</td>
            </tr>
            <tr>
                <td class="detail-label">Objet</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">{{ $tabdepot->objet ?: '—' }}</td>
            </tr>
            <tr>
                <td class="detail-label">N° référence</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">{{ $tabdepot->reference ?: '—' }}</td>
            </tr>
            <tr>
                <td class="detail-label">Origine</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">
                    @if($tabdepot->origine === 'interne')
                        Interne @if($tabdepot->origine_detail) — {{ $tabdepot->origine_detail }} @endif
                    @elseif($tabdepot->origine === 'externe')
                        Externe @if($tabdepot->type_expediteur === 'institution') — {{ $tabdepot->origine_detail ?: 'Institution' }} @else — Citoyen @endif
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr>
                <td class="detail-label">NNI</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">{{ $tabdepot->nni ?: '—' }}</td>
            </tr>
            <tr>
                <td class="detail-label">Téléphone</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">{{ $tabdepot->tel ?: '—' }}</td>
            </tr>
            <tr>
                <td class="detail-label">Adresse</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">{{ $tabdepot->adresse ?: '—' }}</td>
            </tr>
            <tr>
                <td class="detail-label">Date de réception</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">{{ $tabdepot->daterecpFormatted() }}</td>
            </tr>
            @if($tabdepot->piece_jointe)
            <tr>
                <td class="detail-label">Pièce jointe</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">Document scanné joint au dossier papier</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="annotation-block">
        <div class="annotation-title">À porter au Maire pour ses annotations (données oralement, saisies ensuite par le Cabinet dans l'application)</div>
    </div>

</body>
</html>
