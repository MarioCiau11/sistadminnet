 style="text-align: right"<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPORTE DE VENTAS POR CLIENTES Y ARTÍCULO</title>

    <link rel="stylesheet" href="{{ asset('css/reportes/reportes.css') }}">
    <style>
        .page-break {
            page-break-after: always;
        }
    </style>


</head>

<body>
    <table class="cabecera ancho">
        <tr>
            <td class="logo">
                <img src="{{ $logo }}" alt="Logo de la empresa">
            </td>

            <td class="info-empresa">
                <h3>{{ session('company')->companies_name }}</h3>
                <p>R.F.C. {{ session('company')->companies_rfc }}</p>
            </td>

            <td class="info-venta">
                <p><strong>Fecha de Emisión </strong> {{ \Carbon\Carbon::now()->isoFormat('LL') }}</p>

                <p><strong>Estatus: </strong> {{ $status }}</p>
            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>REPORTE DE VENTAS POR CLIENTES Y ARTÍCULO</strong></h3>
                <h3><strong>{{$fecha}}</strong></h3>
            </td>
        </tr>

    </table>
<table class="informacion-prov2" style="width: 1080px; text-align: center; margin: 0 auto;">
    
    @foreach ($clientesVentas as $clientesPorVenta)
    <tr>
        <td colspan="10">
            <p>{{ $clientesPorVenta['customers_key']}} - {{ $clientesPorVenta['customers_businessName']}}</p>

        </td>

    </tr>


    {{-- <table class="articulos-table" style="margin-top: 0px;"> --}}
        <tr>
            <th>
                <p>#</p>
            </th>
            <th  style="width: 50px">
                <p>MOVIMIENTO</p>
            </th>
            <th >
                <p>NOMBRE DEL ARTÍCULO</p>
            </th>
            <th>
                <p>TOTAL UNIDAD</p>
            </th>
            <th>
                <p>UNIDAD</p>
            </th>
            <th>
                <p>COSTO</p>
            </th>
                <th>
                <p>IMPORTE</p>
            </th>
            <th>
                <p>SUBTOTAL</p>
            </th>
            <th>
                <p>IMPUESTOS</p>
            </th>
            <th>
                <p>TOTAL</p>
            </th>
        </tr>
        <?php 
        // dd($clientesPorVenta ['ventas']);
        
        ?>

        @foreach ($clientesPorVenta ['ventas'] as $key => $venta)
        <tr>

            <?php

            if($key % 20 == 0 && $key !=0){
                echo '</table>';
                echo '<div class="page-break"></div>';
                echo '<table class="articulos-table cabecera ancho">
        <tr>
            <th>
                <p>#</p>
            </th>
            <th  style="width: 50px">
                <p>MOVIMIENTO</p>
            </th>
            <th >
                <p>NOMBRE DEL ARTÍCULO</p>
            </th>
            <th>
                <p>TOTAL UNIDAD</p>
            </th>
            <th>
                <p>UNIDAD</p>
            </th>
            <th>
                <p>COSTO</p>
            </th>
                <th>
                <p>IMPORTE</p>
            </th>
            <th>
                <p>SUBTOTAL</p>
            </th>
            <th>
                <p>IMPUESTOS</p>
            </th>
            <th>
                <p>TOTAL</p>
            </th>
        </tr>';
            }


            ?>



            <td>{{ $key + 1}}</td>
            <td>{{ $venta['sales_movement']}} {{ $venta['sales_movementID']}}</td>
            <td style="text-align: left">{{ $venta['salesDetails_article']}} - {{ $venta['salesDetails_descript']}}</td>
            <td>{{ $venta['salesDetails_quantity']}}</td>
            <td>{{ $venta['salesDetails_unit']}}</td>
            <?php
            if ($moneda == 'Todos') {
                if ($venta['sales_money'] != $parametro[0]->generalParameters_defaultMoney) {
                    $costo = $venta['salesDetails_unitCost'] * $venta['sales_typeChange'];
                    $importe = $venta['salesDetails_amount'] * $venta['sales_typeChange'];
                    $subtotal = $venta['salesDetails_amount'] * $venta['sales_typeChange'];
                    $total = $venta['salesDetails_total'] * $venta['sales_typeChange'];
                    $impuestos = $venta['salesDetails_amount'] * ($venta['salesDetails_ivaPorcent'] / 100) * $venta['sales_typeChange'];
                } else {
                    $costo = $venta['salesDetails_unitCost'];
                    $importe = $venta['salesDetails_amount'];
                    $subtotal = $venta['salesDetails_amount'];
                    $total = $venta['salesDetails_total'];
                    $impuestos = $venta['salesDetails_amount'] * ($venta['salesDetails_ivaPorcent'] / 100);
                }
            } else {
                $costo = $venta['salesDetails_unitCost'];
                $importe = $venta['salesDetails_amount'];
                $subtotal = $venta['salesDetails_amount'];
                $total = $venta['salesDetails_total'];
                $impuestos = $venta['salesDetails_amount'] * ($venta['salesDetails_ivaPorcent'] / 100);
            }
            ?>
            <td style="text-align: right">${{ number_format($costo, 2)}}</td>
            <td style="text-align: right">${{ number_format($importe, 2)}}</td>
            <td style="text-align: right">${{ number_format($subtotal, 2)}}</td>
            <td style="text-align: right">${{number_format($impuestos, 2)}}</td>
            <td style="text-align: right">${{ number_format($total, 2)}}</td>
        </tr>


            <?php
            $totalUnidad = 0;
            $totalImporte = 0;
            $totalSubtotal = 0;
            $totalImpuestos = 0;
            $totalTotal = 0;

            foreach ($clientesPorVenta ['ventas'] as $venta) {
                $totalUnidad += $venta['salesDetails_unitCost'];
                $totalImporte += $venta['salesDetails_amount'];
                $totalSubtotal += $venta['salesDetails_amount'];
                $totalImpuestos += $venta['salesDetails_amount'] * ($venta['salesDetails_ivaPorcent'] / 100);
                $totalTotal += $venta['salesDetails_total'];
            }
            ?>

@endforeach
        
        <?php
        $totalUnidad = 0;
        $totalImporte = 0;
        $totalSubtotal = 0;
        $totalImpuestos = 0;
        $totalTotal = 0;

        foreach ($clientesPorVenta ['ventas'] as $venta) {
            $totalUnidad += $venta['salesDetails_quantity'];
            if ($moneda == 'Todos') {
                if ($venta['sales_money'] != $parametro[0]->generalParameters_defaultMoney) {
                    $totalImporte += $venta['salesDetails_amount'] * $venta['sales_typeChange'];
                    $totalSubtotal += $venta['salesDetails_amount'] * $venta['sales_typeChange'];
                    $totalImpuestos += $venta['salesDetails_amount'] * ($venta['salesDetails_ivaPorcent'] / 100) * $venta['sales_typeChange'];
                    $totalTotal += $venta['salesDetails_total'] * $venta['sales_typeChange'];
                } else {
                    $totalImporte += $venta['salesDetails_amount'];
                    $totalSubtotal += $venta['salesDetails_amount'];
                    $totalImpuestos += $venta['salesDetails_amount'] * ($venta['salesDetails_ivaPorcent'] / 100);
                    $totalTotal += $venta['salesDetails_total'];
                }
            } else {
                $totalImporte += $venta['salesDetails_amount'];
                $totalSubtotal += $venta['salesDetails_amount'];
                $totalImpuestos += $venta['salesDetails_amount'] * ($venta['salesDetails_ivaPorcent'] / 100);
                $totalTotal += $venta['salesDetails_total'];
            }
        }
        ?>

        <tr>
            <th>
                <p> </p>
            </th>
            <th>
                <p> </p>
            </th>
            <th>
                <p>TOTAL UNIDAD</p>
            </th>
            <th>
                <p>{{ ($totalUnidad) }}</p>
            </th>
            <th>
                <p> </p>
            </th>
            <th>
                <p>TOTALES</p>
            </th>
            <th>
                <p style="text-align: right">${{ number_format($totalImporte, 2)}}</p>
            </th>
            <th>
                <p style="text-align: right">${{ number_format( $totalSubtotal, 2) }}</p>
            </th>
            <th>
                <p style="text-align: right">${{ number_format($totalImpuestos,2)}}</p>
            </th>
            <th>
                <p style="text-align: right">${{ number_format($totalTotal, 2) }}</p>
            </th>
        </tr>
        {{-- </table> --}}
        <br>
        @endforeach
</table>

</body>

</html>
