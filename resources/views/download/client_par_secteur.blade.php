<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Liste des clients secteur de {{ $sector->name }} </title>
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
    </style>
</head>

<body>
    <h1 class="app-name">{{ $title }}</h1>
    <p>{{ $date }}</p>

    <h3 class="title-sector">SECTEUR {{ $sector->name }}</h3>
    <table>
        <tr>
            <th>N°</th>
            <th>NOM client</th>
            <th>Téléphone</th>
            <th>N° de compte</th>
            <th>Solde</th>
            <th>N° de comptoir </th>
        </tr>
        @php $id = 1; @endphp
        @foreach ($clients as $client)
            <tr>
                <td>{{ $id++ }} </td>
                <td>{{ $client->name }} </td>
                <td>{{ $client->phone }} </td>
                <td>{{ $client->accounts[0]->account_number }} </td>
                <td>{{ $client->accounts[0]->account_balance }} </td>
                <td>{{ $client->numero_comptoir }} </td>
            </tr>
        @endforeach
    </table>
</body>

</html>
