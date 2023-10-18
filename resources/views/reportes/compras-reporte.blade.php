<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de compra</title>

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
                <p><strong>Fecha Emision:
                    </strong>{{ \Carbon\Carbon::parse($compra->purchase_issueDate)->format('d/m/Y') }}</p>
                <h3><strong>Folio :</strong> <span class="folio-bold">{{ $compra->purchase_movementID }}</span>
                </h3>
                <p><strong>Sucursal: </strong>{{ session('sucursal')->branchOffices_key }} -
                    {{ session('sucursal')->branchOffices_name }}</p>
                <p><strong>Estatus: </strong> {{ $compra->purchase_status }}</p>
            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>{{ $compra->purchase_movement }}</strong></h3>
            </td>
        </tr>

    </table>

    <table class="informacion-proveedor">
        <tr>
            <td>
                <p>PROVEEDOR:</p>
            </td>
            <td>
                <p>{{ $compra->providers_name }}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>DOMICILIO:</p>
            </td>
            <td>
                <p>{{ $compra->providers_address . ' ' . $compra->providers_roads . ' ' . $compra->providers_outdoorNumber . ' ' . $compra->providers_interiorNumber . ' ' . $compra->providers_colonyFractionation . ' ' . $compra->providers_state . ' ' . $compra->providers_country }}

            </td>
        </tr>
        <tr>
            <td>
                <p>CONDICIÓN:</p>
            </td>
            <td>
                <p>{{ $compra->creditConditions_name }}</p>
            </td>
        </tr>

    </table>

    <table class="articulos-table">
        <tr>
            <th>
                <p>#</p>
            </th>
            <th>
                <p>CLAVE ARTICULO</p>
            </th>
            <th>
                <p>NOMBRE DEL ARTICULO</p>
            </th>
            <th>
                <p>CANTIDAD</p>
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
        </tr>

        @foreach ($articulos_compra as $key => $articulo)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $articulo->purchaseDetails_article }}</td>
                <td>{{ $articulo->purchaseDetails_descript }}</td>
                <td>{{ number_format($articulo->purchaseDetails_quantity, $articulo->units_decimalVal) }}</td>
                <td>{{ $articulo->purchaseDetails_unit }}</td>
                <td style="text-align: right">${{ number_format($articulo->purchaseDetails_unitCost, 2) }}</td>
                <td style="text-align: right">${{ number_format($articulo->purchaseDetails_amount, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <table class="costos-desglosados">
        <tr>
            <td class="anchoCompleto">

            </td>
            <td>
                <p style="text-align: right">Subtotal</p>
            </td>
            <td>
                <p class="numeros-reportes" style="text-align: right">$
                    {{ number_format($compra->purchase_amount, 2) }}</p>
            </td>
        </tr>
        <tr>
            <td class="anchoCompleto"></td>
            <td>
                <p style="text-align: right">Impuesto</p>
            </td>
            <td>
                <p class="numeros-reportes" style="text-align: right">$ {{ number_format($compra->purchase_taxes, 2) }}
                </p>
            </td>
        </tr>

        <tr>
            <td class="anchoCompleto">
                <p style="text-align: right"><?php
                $totalReporte = $compra->purchase_amount + $compra->purchase_taxes;
                $formato = new NumeroALetras();
                
                echo $formato->toMoney($totalReporte, 2, $compra->money_key, 'CENTAVOS '), $compra->money_keySat;
                
                ?></p>
            </td>
            <td>
                <p style="text-align: right">Total</p>
            </td>
            <td>
                <p class="numeros-reportes" style="text-align: right">$ {{ number_format($totalReporte, 2) }}</p>
            </td>
        </tr>

    </table>
</body>

</html>
