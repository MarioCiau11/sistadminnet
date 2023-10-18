<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPORTE DE VENTAS ACUMULADAS POR ARTÍCULO Y CLIENTE</title>


    <link rel="stylesheet" href="{{ asset('css/reportes/reportes.css') }}">


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
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>REPORTE DE VENTAS ACUMULADAS POR ARTÍCULO Y CLIENTE</strong></h3>
                {{-- <h3><strong>{{$fecha}}</strong></h3> --}}
            </td>
        </tr>

    </table>
    <table class="articulos-table">
    <?php
    $articulos_array = array();
    $clientes_array = array();

    foreach ($ventas as $venta) {
        $articulos_array[] = $venta['assistantUnit_account'] . '-' . $venta['articles_descript'];
    }

    $articulos_array = array_unique($articulos_array);

    foreach ($ventas as $venta) {
        $clientes_array[$venta['customers_key']] = $venta['customers_key'] . '-' . $venta['customers_businessName'];
    }

    $clientes_array = array_unique($clientes_array);
    ?>


    @foreach ($articulos_array as $articulo)
    <tr>
        <td colspan="3" style="text-align: left; padding-top: 10px;">
            <strong><p>{{ $articulo }}</p></strong>
        </td>
    </tr>

    @foreach ($clientes_array as $cliente)
    <?php
    $articuloPorCompra = [];
    $articuloPorCompra = [
        'customers_key' => '',
        'customers_businessName' => '',
        'ventas' => [],
    ]

    ?>

    @foreach ($ventas as $venta)
    @if ($articulo == $venta['assistantUnit_account'] . '-' . $venta['articles_descript'] && $cliente == $venta['customers_key'] . '-' . $venta['customers_businessName'])
    <?php
    $articuloPorCompra['customers_key'] = $venta['customers_key'];
    $articuloPorCompra['customers_businessName'] = $venta['customers_businessName'];
    $articuloPorCompra['ventas'][] = $venta;
    ?>
    @endif
    @endforeach

    @if (count($articuloPorCompra['ventas']) > 0)
    <tr>
        <td colspan="3" style="font-size: 11px; text-align: center">
            <p>{{ $articuloPorCompra['customers_key'] . '-' . $articuloPorCompra['customers_businessName'] }}</p>
        </td>
    </tr>                    
    <tr class="articulos-table" >
        <th style="border: 1px solid black;">
            <p>PERÍODO</p>
        </th>
        <th style="border: 1px solid black;">
            <p>VENTA TOTAL</p>
        </th>
        <th style="border: 1px solid black;">
            <p>CANTIDAD NETA</p>
        </th>
    </tr>

        <?php
        $mes = [];
        $comprasNetas = [];
        $cantidadNetas = [];

        //el mes no se tiene que repetir

        foreach ($articuloPorCompra['ventas'] as $key => $compra) {
            $meses = [
                '1' => 'ENERO',
                '2' => 'FEBRERO',
                '3' => 'MARZO',
                '4' => 'ABRIL',
                '5' => 'MAYO',
                '6' => 'JUNIO',
                '7' => 'JULIO',
                '8' => 'AGOSTO',
                '9' => 'SEPTIEMBRE',
                '10' => 'OCTUBRE',
                '11' => 'NOVIEMBRE',
                '12' => 'DICIEMBRE',
            ];

            $mes[$compra['assistantUnit_period']] = $meses[$compra['assistantUnit_period']];

        }

        foreach ($mes as $key => $meses) {
            $comprasNetas[$key] = 0;
            $cantidadNetas[$key] = 0;
        }

        foreach ($articuloPorCompra['ventas'] as $key => $compra) {
            $comprasNetas[$compra['assistantUnit_period']] += $compra['assistantUnit_payment'];
            $cantidadNetas[$compra['assistantUnit_period']] += $compra['assistantUnit_paymentUnit'];
        }

        ?>

@foreach ($mes as $key => $meses)
<tr>
    <td style="text-align: center">
        <p>{{ $meses }}</p>
    </td>
    <td style="text-align: center">
        <p>${{ number_format($comprasNetas[$key], 2) }}</p>
    </td>
    <td style="text-align: center">
        <p>{{ number_format($cantidadNetas[$key], 0) }}</p>
    </td>
</tr>

@endforeach

<tr>
    <th>
        <p>TOTAL:</p>
    </th>
    <th>
        <p>${{ number_format(array_sum($comprasNetas), 2) }}</p>
    </th>
    <th>
        <p>{{ number_format(array_sum($cantidadNetas), 0) }}</p>
    </th>
</tr>


@endif

@endforeach 
@endforeach


</table>

<table class="articulos-table">

    <?php
    $totalComprasNetas = 0;
    $totalCantidadNetas = 0;

    foreach ($ventas as $key => $venta) {
        $totalComprasNetas += $venta['assistantUnit_payment'];
        $totalCantidadNetas += $venta['assistantUnit_paymentUnit'];
    }

    ?>

    <tr>
        <th style="width: 230px">
            <p>TOTAL:</p>
        </th>
        <th style="width: 235px">
            <p>${{ number_format($totalComprasNetas, 2) }}</p>
        </th>
        <th>
            <p>{{ number_format($totalCantidadNetas, 0) }}</p>
        </th>
    </tr>
</table>

</body>

</html>
