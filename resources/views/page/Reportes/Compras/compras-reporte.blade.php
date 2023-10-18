<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de compras por Unidad</title>

    <link rel="stylesheet" href="{{ asset('css/reportes/reportes.css') }}">


</head>

<body>
    <table class="cabecera ancho">
        <tr>
            <td class="logo">
                <img src="{{ $logo }}" alt="Logo de la empresa">
            </td>


            <td class="info-empresa">
                <h3>{{ $compra->companies_name }}</h3>
                <p>R.F.C. {{ $compra->companies_rfc }}</p>
            </td>

            <td class="info-compra">
                <p><strong>Fecha de Emisión: </strong> {{ \Carbon\Carbon::now()->isoFormat('LL') }}</p>

                {{-- <p><strong>Almacén:</strong> <span class="folio-bold">
                    @if ($nameAlmacen == 'Todos')
                        {{ $nameAlmacen }}
                    @else
                        {{ $compra->depots_key.' - '.$compra->depots_name }}
                    @endif
                </span></p>
                <p><strong>Sucursal:</strong> <span class="folio-bold">
                    @if ($nameSucursal == 'Todos')
                        {{ $nameSucursal }}
                    @else
                        {{ $compra->branchOffices_key.' - '.$compra->branchOffices_name }}
                    @endif
                </span></p> --}}
                <p><strong>Estatus: </strong> {{ $status  }}</p>
            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>REPORTE DE COMPRAS POR UNIDAD</strong></h3>
                <h3><strong>{{$nameFecha}}</strong></h3>
            </td>
        </tr>

    </table>


    <table class="articulos-table">
        <tr>
            <th>
                <p>#</p>
            </th>
            <th>
                <p>FECHA MOV.</p>
            </th>
            <th>
                <p>TOTAL UNIDAD</p>
            </th>
            <th>
                <p>UNIDAD</p>
            </th>

            <th>
                <p>IMPORTE</p>
            </th>
        </tr>

        @foreach ($compras as $key => $compra)
            <tr>
                <td style="text-align: center">{{ $key + 1 }}</td>
                <td style="text-align: center">{{ \Carbon\Carbon::parse( $compra->purchase_issueDate)->format('d/m/Y') }}</td>
                <td style="text-align: right">{{ number_format($compra->purchaseDetails_inventoryAmount, $compra->units_decimalVal) }}</td>
                <td style="text-align: center">{{ $compra->purchaseDetails_unit }}</td>
              
                <td style="text-align: right">${{ number_format($compra->purchaseDetails_total, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <table class="articulos-table2">
       
        <?php

        $total = 0;
        $totalImporte = 0;
        foreach ($compras as $compra) {
            $total += $compra->purchaseDetails_inventoryAmount;
            $totalImporte += $compra->purchaseDetails_total;
        }
        ?>
        <tr>
            <th style="width: 100px; border:0ch">
                
            </th>
            <th style="width: 100px; border:0ch">
                
            </th>
            <th style="width: 100px; border:0ch">
                
            </th>
            <th style="width: 100px; border:0ch">
                
            </th>
            <th style="width: 100px">
                <p>TOTALES</p>
            </th>
            <th style="width: 50px">
                <p style="text-align: right; width: 100px">{{number_format($total, $compra->units_decimalVal)}}</p>
            </th>
            <th>
                <p>KG</p>
            </th>
            <th style="width: 100px">
                <p>TOTAL IMPORTE</p>
            </th>
            <th style="width: 100px">
                <p style="text-align: right">${{number_format($totalImporte, 2)}}</p>
            </th>
        </tr>
    </table>


</body>

</html>
