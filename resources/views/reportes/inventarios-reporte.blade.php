<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inventario</title>

    <link rel="stylesheet" href="{{ asset('css/reportes/reportes.css') }}">


</head>

<body>
    <table class="cabecera ancho">
        <tr>
            <td class="logo">
                <img src="{{ $logo }}" alt="Logo de la empresa">
            </td>


            <td class="info-empresa">
                <h3>{{ $inventario->companies_name }}</h3>
                <p>R.F.C. {{ $inventario->companies_rfc }}</p>
            </td>

            <td class="info-inventario">
                <p><strong>Fecha Emision: </strong>
                    {{ \Carbon\Carbon::parse($inventario->inventories_issuedate)->format('d/m/Y') }}</p>
                <h3><strong>Folio :</strong> <span class="folio-bold">{{ $inventario->inventories_movementID }}</span>
                </h3>
                <p><strong>Estatus: </strong> {{ $inventario->inventories_status }}</p>
                <p><strong>Sucursal: </strong> {{ $inventario->branchOffices_name }}</p>
                <p><strong>Almacen origen: </strong> {{ $inventario->depots_name }}</p>

                @if ($inventario->inventories_movement == 'Salida por Traspaso')
                    <p><strong>Almacen Destino: </strong> {{ $inventario->depots_nameDestiny }}</p>
                @endif
            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>{{ $inventario->inventories_movement }} -
                        {{ $inventario->inventories_movementID }}</strong></h3>
                @if ($inventario->inventories_movement === 'Transferencia entre Alm.')
                    <p><strong>DE {{ $inventario->depots_name }}</strong> <strong> A
                            {{ $inventario->depots_nameDestiny }}</strong></p>
                @endif
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

        @foreach ($articulos as $key => $articulo)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $articulo->inventoryDetails_article }}</td>
                <td>{{ $articulo->inventoryDetails_descript }}</td>
                <td>{{ number_format($articulo->inventoryDetails_quantity, $articulo->units_decimalVal) }}</td>
                <td>{{ $articulo->inventoryDetails_unit }}</td>
                <td style="text-align: right">{{ '$' . number_format($articulo->inventoryDetails_unitCost, 2) }}</td>
                <td style="text-align: right">{{ '$' . number_format($articulo->inventoryDetails_amount, 2) }}</td>
            </tr>
        @endforeach
    </table>

    {{-- <table class="costos-desglosados">
        <tr>
            <td class="anchoCompleto">

            </td>
            <td>
                <p>Subtotal</p>
            </td>
            <td>
                <p class="numeros-reportes">$ {{ number_format($inventario->purchase_amount, 2) }}</p>
            </td>
        </tr>
        <tr>
            <td class="anchoCompleto"></td>
            <td>
                <p>Impuesto</p>
            </td>
            <td>
                <p class="numeros-reportes">$ {{ number_format($inventario->purchase_taxes, 2) }}</p>
            </td>
        </tr>

        <tr>
            <td class="anchoCompleto">
                <p><?php
                $totalReporte = $inventario->purchase_amount + $inventario->purchase_taxes;
                $formato = new NumeroALetras();
                echo $formato->toInvoice($totalReporte, 2, $inventario->money_key);
                ?></p>
            </td>
            <td>
                <p>Total</p>
            </td>
            <td>
                <p class="numeros-reportes">$ {{ number_format($totalReporte, 2) }}</p>
            </td>
        </tr>

    </table> --}}
</body>

</html>
