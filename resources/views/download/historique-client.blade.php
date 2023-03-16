<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Historique {{ $client->user->name }} </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        table,
        th,
        td {
            border: 1px solid #9b9494cc;

        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            border-bottom: 1px solid #9b9494cc;
        }

        th {
            height: 5%;
            background-color: #0dcaf0;
            color: white;
        }

        td {
            text-align: left
        }

        tr {}

        .title-sector,
        .app-name {
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        img{
            height: 50px;
            width: 50px;
            border-radius: 50%;
        }
        .header_histo, td{
            border: 0;
        }
    </style>
</head>

<body>
    <h1 class="app-name">{{ $title }}</h1>
    <table class="header_histo">
        <tr>
            <td>
               <strong>{{ $client->user->name }}</strong> <br>
               {{$client->sexe == 2 ? 'Masculin': 'Féminin'}} <br>
               N° CNI/passeport : {{$client->cni}}
            </td>
            <td style="text-align: right; ">
                {{-- <img src="{{asset('uploadProfil/'.$client->user->avatar)}}" alt="Profil"> --}}
                <img src="{{'storage/uploadProfil/'.$client->user->avatar}}" alt="Profil" >
            </td>
            <td style="text-align: right;">
                N° comptoir: {{$client->numero_comptoir}} <br>
                N° Registre de commerce : {{$client->numero_registre_de_commerce}}
            </td>
        </tr>
    </table>
    <p>{{ $date }}</p>

    <h3 class="title-sector">Historique <strong>{{ $client->user->name }}</strong> du {{-- $info->startDate ." Au ".$info->endDate --}} </h3>
    <table>
        <tr>
            <th>N°</th>
            <th>Date</th>
            <th>Operation</th>
            <th>Montant opération</th>
            <th>Solde</th>
        </tr>
        @php $id = 1; @endphp
        @foreach ($operations as $operation)
            <tr>
                <td>{{ $id++ }} </td>
                <td>{{ $operation->created_at }} </td>
                <td>
                    {{ $operation->type == 1
                                ? 'Versement'
                                : ($operation->type == -1
                            ? 'Retrait'
                            : ($operation->type == 0
                        ? 'Reconduction'
                        : '')) }}
                        
                </td>
                
                </td>
                <td>{{ $operation->amount }} XAF</td>
                <td>
                    {{ $operation->type == 1
                        ? $operation->amount + $operation->remaining_balance
                        : ($operation->type == -1
                            ? $operation->remaining_balance - $operation->amount
                            : ($operation->type == 0
                                ? $operation->remaining_balance
                                : '')) }}
                    XAF
                </td>
            </tr>
        @endforeach
    </table>
</body>

</html>
