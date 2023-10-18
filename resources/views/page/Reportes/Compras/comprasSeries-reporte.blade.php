<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPORTE - COMPRAS CON SERIES</title>

    <link rel="stylesheet" href="{{ asset('css/reportes/reportes.css') }}">
    <style>
        .page-break {
            page-break-after: always;
        }

        body {
            font-size: 10px;
        }
    </style>

</head>

<body>
    <table class="cabecera ancho">
        <tr>
            <td class="logo">
                <img src="{{ $logo }}" alt="Logo de la empresa">
            </td>

            <td>
                <h3><strong>REPORTE - COMPRAS CON SERIES </strong></h3>
            </td>

            <td class="info-compra">
                <p><strong>Fecha de Emisión </strong> {{ \Carbon\Carbon::now()->isoFormat('LL') }}</p>
            </td>
        </tr>

    </table>

    <?php 

    $articulos_array = array();
    $clientesCompras = array();
    $comprasSeries = array();
    

    foreach ($compras as $compra) {
        $articulos_array[] = $compra['purchaseDetails_article'] . " - " . $compra['purchaseDetails_descript'];
    }

    $articulos_array = array_unique($articulos_array);

    foreach ($compras as $compra) {
        $clientesCompras[$compra['providers_key']] = $compra['providers_key']." - ".$compra['providers_name'];
    }

    foreach ($compras as $compra) {
        $comprasSeries[$compra['purchase_movementID']] = $compra['purchase_movementID'];
    }


    $clientesCompras = array_unique($clientesCompras);



    // dd($articulos_array, $clientescompras, $ventas, $ventasSeries);
    
    
    
    ?>

    <table class="informacion-prov2">
      

        @foreach ($articulos_array as $articulo)
        <tr>   
                <th><p>{{ $articulo }}</p></th>

        </tr>
        <tr>
            <td>
                <p></p>

            </td>

        </tr>

        
    <table class="articulos-table2">
        <tr>
            <th>
                <p>Fecha</p>
            </th>
            <th  style="width: 100px;">
                <p>Operación</p>
            </th>
            <th style="text-align:left">
                <p>Referencia</p>
            </th>
            <th style="text-align:left">
                <p>Proveedor</p>
            </th>
            <th>
                <p>Cantidad</p>
            </th>
            <th  style="width: 120px; text-align:left">
                <p>Series</p>
            </th>
        </tr>

        @foreach ($compras as $key => $compra)
        @if ($compra['purchaseDetails_article']." - ".$compra['purchaseDetails_descript'] == $articulo)
        <tr>
            <td>
                <p>{{ \Carbon\Carbon::parse($compra['purchase_issueDate'])->format('d/m/Y') }}</p>
            </td>
            <td>
                <p>{{ $compra['purchase_movement'].'-'.$compra['purchase_movementID'] }}</p>
            </td>
            <td style=" text-align:left">
                <p>{{ $compra['purchase_reference'] }}</p>
            </td>
            <td style=" text-align:left">
                <p>{{ $compra['providers_key'].' - '.$compra['providers_name'] }}</p>
            </td>
            <td>
                {{-- <p>{{ $compra['purchaseDetails_quantity'] }}</p>  --}}
                <p>1</p>
            </td>
            <td style=" text-align:left">
                <p>{{ $compra['lotSeriesMov_lotSerie'] }}</p>
            </td>
        </tr>
        @endif
        @if ($key == 20 && $nameArticulo == 'Todos')
        {{-- @if ($loop->last) --}}
        </table>
        <div class="page-break"></div>
        <table class="articulos-table2">
            <tr>
                <th>
                    <p>Fecha</p>
                </th>
                <th  style="width: 100px;">
                    <p>Operación</p>
                </th>
                <th style="text-align:left">
                    <p>Referencia</p>
                </th>
                <th style="text-align:left">
                    <p>Proveedor</p>
                </th>
                <th>
                    <p>Cantidad</p>
                </th>
                <th  style="width: 100px; text-align:left">
                    <p>Series</p>
                </th>
            </tr>
        @endif

        @endforeach

    </table>
        @endforeach


    </table>

</body>

</html>
