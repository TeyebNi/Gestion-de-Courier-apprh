<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu #{{ $detailf->id }}</title>

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
            margin: 15px 0 30px 0;
            font-size: 22px;
            color: #000;
        }

        h1.title .num {
            font-weight: 700;
            font-size: 18px;
        }
        .details {
            width: 100%;
            margin-top: 20px;
            margin-left: 10px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table td {
            border: none;
            padding: 0 0 17px 0;
            vertical-align: top;
            font-size: 16px;
            color: #000;
            font-weight: 700;
        }

        .details-table .detail-label {
            width: 180px;
            font-weight: 700;
            white-space: nowrap;
        }

        .details-table .detail-sep {
            width: 25px;
            text-align: center;
            font-weight: 700;
        }

        .details-table .detail-value {
            text-align: left;
            font-weight: 700;
            color: #000;
        }


        .signature-block {
            margin-top: 70px;
            font-size: 15px;
            color: #000;
            margin-left: 10px;
        }

        .signature-title {
            font-weight: 700;
            margin-bottom: 8px;
        }

        .signature-user {
            font-size: 15px;
            font-weight: 700;
            color: #000;
        }

        .qr-block {
            text-align: center;
            margin-top: 15px;
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
        Reçu de Dépôt <span class="num">N° {{ $detailf->id }}</span>
    </h1>
    <div class="details">
        <table class="details-table">
            <tr>
                <td class="detail-label">Code</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">{{ $detailf->reference ?: '—' }}</td>
            </tr>

            <tr>
                <td class="detail-label">Objet</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">{{ $detailf->objet ?: '—' }}</td>
            </tr>

            @if($detailf->nom)
            <tr>
                <td class="detail-label">Nom et Prénom</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">{{ $detailf->nom }}</td>
            </tr>
            @endif

            @if($detailf->tel)
            <tr>
                <td class="detail-label">Téléphone</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">{{ $detailf->tel }}</td>
            </tr>
            @endif

            @if($detailf->nni)
            <tr>
                <td class="detail-label">NNI/NIF</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">{{ $detailf->nni }}</td>
            </tr>
            @endif

            <tr>
                <td class="detail-label">Origine</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">
                    @if($detailf->origine === 'interne')
                        Interne @if($detailf->origine_detail) — {{ $detailf->origine_detail }} @endif
                    @elseif($detailf->origine === 'externe')
                        Externe
                    @else
                        —
                    @endif
                </td>
            </tr>

            <tr>
                <td class="detail-label">Date</td>
                <td class="detail-sep">:</td>
                <td class="detail-value">{{ $detailf->daterecpFormatted() }}</td>
            </tr>
        </table>
    </div>

    <div class="signature-block">

        <div class="signature-title">Signature :</div>
        <div class="signature-user">
            {{ auth()->check() ? auth()->user()->name : '' }}
        </div>
    </div>

    <div class="qr-block">
        <barcode code="{{ str_pad($detailf->id, 6, '0', STR_PAD_LEFT) }}" type="QR" size="1" error="M" />
    </div>

</body>
</html>
