<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
            padding: 8px;
        }
        th {
            background: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Depot des Demandes</h1>
    <table>
        <thead>
            <tr>
                <th>
                                                Code
                                            </th>
                                            <th>
                                                Type de Demande
                                            </th>
                                            <th>
                                                Nom
                                            </th>
                                            <th class="text-right">
                                                NNI
                                            </th>
 
            </tr>
        </thead>
        <tbody>
           
 @foreach($data as $key=>$data)
                
 <tr>
                                              <td>
                                              {{++$key}}
                                                </td>
                                                <td>
                                                  {{$data->id}}
                                                </td>
                                                <td>
                                                   {{$data->typdm}}
                                                </td>
                                                <td class="text-right">
                                                    {{$data->nom}}
                                                </td>
                                                 
                </tr>
             @endforeach
        </tbody>
    </table>

    <h3></h3>
</body>
</html>