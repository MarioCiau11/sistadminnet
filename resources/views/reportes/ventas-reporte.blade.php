<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas</title>

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
                <p><strong>Fecha Emision: </strong>
                    {{ \Carbon\Carbon::parse($venta->sales_issuedate)->format('d/m/Y') }}</p>
                <h3><strong>Folio :</strong> <span class="folio-bold">{{ $venta->sales_movementID }}</span>
                </h3>
                <p><strong>Estatus: </strong> {{ $venta->sales_status }}</p>
                <p><strong>Almacén:</strong> <span class="folio-bold">
                        {{ $venta->depots_key . ' - ' . $venta->depots_name }}

                    </span></p>
                <p><strong>Sucursal:</strong> <span class="folio-bold">

                        {{ $venta->branchOffices_key . ' - ' . $venta->branchOffices_name }}

                    </span></p>
            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>{{ $venta->sales_movement . ' ' . $venta->sales_movementID }}</strong></h3>
            </td>
        </tr>

    </table>

    <table class="informacion-proveedor">
        <tr>
            <td>
                <p>CLIENTE:</p>
            </td>
            <td>
                <p>{{ $venta->customers_businessName }}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>DOMICILIO:</p>
            </td>
            <td>
                <p>{{ $venta->customers_addres . ' ' . $venta->customers_roads . ' ' . $venta->customers_outdoorNumber . ' ' . $venta->customers_interiorNumber . ' ' . $venta->customers_colonyFractionation . ' ' . $venta->customers_state . ' ' . $venta->customers_country }}
                </p>
            </td>
        </tr>
        <tr>
            <td>
                <p>CONDICIÓN:</p>
            </td>
            <td>
                <p>{{ $venta->creditConditions_name }}</p>
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
                <p>CLAVE SAT</p>
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


        @foreach ($articulos_venta as $key => $articulo)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $articulo->salesDetails_article }}</td>
                <td style="text-align: center">{{ preg_replace('/[^0-9]/', '', $articulo->articles_productService) }}
                </td>
                <td>{{ $articulo->salesDetails_descript }}
                <br>
                <strong  style="font-size: 11px">{{ $articulo->salesDetails_observations }}</strong></td>
                <td>{{ number_format($articulo->salesDetails_quantity, $articulo->units_decimalVal) }}</td>
                <td>{{ $articulo->salesDetails_unit }}</td>
                <td style="text-align: right">${{ number_format($articulo->salesDetails_unitCost, 2) }}</td>
                <td style="text-align: right">${{ number_format($articulo->salesDetails_amount, 2) }}</td>
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
                <p class="numeros-reportes" style="text-align: right">$ {{ number_format($venta->sales_amount, 2) }}
                </p>
            </td>
        </tr>
        <tr>
            <td class="anchoCompleto"></td>
            <td>
                <p style="text-align: right">Impuesto</p>
            </td>
            <td>
                <p class="numeros-reportes" style="text-align: right">$ {{ number_format($venta->sales_taxes, 2) }}</p>
            </td>
        </tr>

        <tr>
            <td class="anchoCompleto">
                <p style="text-align: right"><?php
                $totalReporte = $venta->sales_amount + $venta->sales_taxes;
                $formato = new NumeroALetras();
                echo $formato->toMoney($totalReporte, 2, $venta->money_key, 'CENTAVOS '), $venta->money_keySat;
                
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
