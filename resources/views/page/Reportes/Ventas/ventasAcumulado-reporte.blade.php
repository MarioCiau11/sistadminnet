<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPORTE DE VENTAS ACUMULADO POR ARTÍCULO</title>

    <link rel="stylesheet" href="{{ asset('css/reportes/reportes.css') }}">


</head>

<body>
    <table class="cabecera ancho">
        <tr>
            <td class="logo">
                <img src="{{ $logo }}" alt="Logo de la empresa">
            </td>


            <td class="info-empresa">
                <h3>{{ $venta->companies_name }}</h3>
                <p>R.F.C. {{ $venta->companies_rfc }}</p>
            </td>

            <td class="info-compra">
                <p><strong>Fecha de Emisión </strong> {{ \Carbon\Carbon::now()->isoFormat('LL') }}</p>
                <p><strong>Estatus: </strong> {{ $status  }}</p>

            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>REPORTE DE VENTAS ACUMULADO POR ARTÍCULO</strong></h3>
                <h3><strong>{{$fecha}}</strong></h3>

            </td>
        </tr>

    </table>


    <table class="articulos-table">
        <tr>
            <th>
                <p>CLAVE</p>
            </th>
            <th>
                <p>DESCRIPCIÓN</p>
            </th>
            <th>
                <p>PRECIO</p>
            </th>
            <th>
                <p>TOTAL</p>
            </th>
        </tr>


        @foreach ($articulos as $venta)
            <tr>
                <td>{{ $venta->salesDetails_article }}</td>
                <td>{{ $venta->salesDetails_descript }}</td>
                <td style="text-align: right">${{ number_format($venta->salesDetails_unitCost, 2) }}</td>
                <td style="text-align: right">${{ number_format($venta->salesDetails_total, 2) }}</td>
    
            </tr>
        @endforeach
    </table>

    <table class="ventas-table">
        <?php
        $total = 0;
        foreach ($articulos as $venta) {
            $total += $venta->salesDetails_total;
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
                <p> </p>
            </th>
            <th>
                <p>${{number_format($total, 2)}}</p>

            </th>
        </tr>

        
    </table>


</body>

</html>
