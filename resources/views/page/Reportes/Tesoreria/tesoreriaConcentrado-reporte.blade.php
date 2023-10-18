<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Tesoreria Concentrado</title>

    <link rel="stylesheet" href="{{ asset('css/reportes/reportes.css') }}">


</head>

<body>
    <table class="cabecera ancho">
        <tr>
            <td class="logo">
                <img src="{{ $logo }}" alt="Logo de la empresa">
            </td>


            <td class="info-empresa">
                <h3>{{ $tesoreria->companies_name }}</h3>
                <p>R.F.C. {{ $tesoreria->companies_rfc }}</p>
            </td>

            <td class="info-compra">
                <p><strong>Fecha de Emisión </strong> {{ \Carbon\Carbon::now()->isoFormat('LL') }}</p>
            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>REPORTE DE TESORERIA CONCENTRADO</strong></h3>
            </td>
        </tr>

    </table>


    <table class="articulos-table">
        <tr>
            <th>
                <p>CUENTA</p>
            </th>
            <th>
                <p>DESCRIPCIÓN</p>
            </th>
            <th>
                <p>NÚMERO CUENTA</p>
            </th>
            <th>
                <p>SALDO INICIAL</p>
            </th>
            <th>
                <p>CARGOS</p>
            </th>
            <th>
                <p>ABONOS</p>
            </th>
            <th>
                <p>SALDO FINAL</p>
            </th>
        </tr>
        @foreach ($cuentas as $cuenta)
            <tr>
                <td>{{ $cuenta->moneyAccountsBalance_moneyAccount }}</td>
                <td>{{ $cuenta->moneyAccounts_referenceBank }}</td>
                <td>{{ $cuenta->moneyAccounts_numberAccount }}</td>
                <td style="text-align: right">${{ number_format($cuenta->moneyAccountsBalance_initialBalance, 2) }}</td>
                <td style="text-align: right">${{ number_format($cuenta->assistant_charge, 2) }}</td>
                <td style="text-align: right">${{ number_format($cuenta->assistant_payment, 2) }}</td>
                <td style="text-align: right">${{ number_format($cuenta->moneyAccountsBalance_balance, 2) }}</td>
            </tr>
        @endforeach
    </table>
    {{-- <table class="articulos-table2">
    
        <tr>
            <th>
                <p>TOTALES</p>
            </th>
            <th>
                <p>{{number_format($total, 2)}}</p>
            </th>
            <th>
                <p>KG</p>
            </th>
            <th>
                <p>TOTAL IMPORTE</p>
            </th>
            <th>
                <p>${{number_format($totalImporte, 2)}}</p>
            </th>
        </tr>
    </table> --}}


</body>

</html>
