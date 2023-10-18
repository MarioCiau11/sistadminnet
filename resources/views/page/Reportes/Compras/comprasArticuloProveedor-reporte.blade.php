<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPORTE DE COMPRAS POR ARTÍCULO O PROVEEDOR</title>

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

            <td class="info-compra">
                <p><strong>Fecha de Emisión </strong> {{ \Carbon\Carbon::now()->isoFormat('LL') }}</p>
                <p><strong>Estatus: </strong> {{ $status }}</p>
            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>REPORTE DE COMPRAS POR PROVEEDOR Y ARTÍCULO</strong></h3>
                <h3><strong>{{$fecha }}</strong></h3>
            </td>
        </tr>

    </table>
<table class="informacion-prov2">
    
    @foreach ($proveedorCompras as $proveedorPorCompra)
    <tr>
        <td>
            <p>{{ $proveedorPorCompra['providers_name']}}</p>
        </td>
    </tr>
    <table class="articulos-table" style="margin-top: 0px;">
        <tr>
            <th>
                <p>#</p>
            </th>
            <th  style="width: 150px">
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
        {{-- hacemos que cada 30 registros haga un salto de pagina --}}
        

        @foreach ($proveedorPorCompra ['compras'] as $key => $compra)
        <tr>
            <td>{{ $key + 1}}</td>
            <td>{{ $compra['purchase_movement']}} {{ $compra['purchase_movementID']}}</td>
            <td style="text-align: left">{{ $compra['purchaseDetails_descript']}}</td>
            <td style="text-align: center">{{ number_format($compra['purchaseDetails_quantity'], $compra['units_decimalVal'])}}</td>
            <td>{{ $compra['purchaseDetails_unit']}}</td>
            <?php
            if ($moneda == 'Todos') {
                if ($compra['purchase_money'] != $parametro[0]->generalParameters_defaultMoney) {
                    $costo = $compra['purchaseDetails_unitCost'] * $compra['purchase_typeChange'];
                    $importe = $compra['purchaseDetails_amount'] * $compra['purchase_typeChange'];
                    $total = $compra['purchaseDetails_total'] * $compra['purchase_typeChange'];
                    $impuestos = $compra['purchaseDetails_amount'] * ($compra['purchaseDetails_ivaPorcent'] / 100) * $compra['purchase_typeChange'];
                } else {
                    $costo = $compra['purchaseDetails_unitCost'];
                    $importe = $compra['purchaseDetails_amount'];
                    $total = $compra['purchaseDetails_total'];
                    $impuestos = $compra['purchaseDetails_amount'] * ($compra['purchaseDetails_ivaPorcent'] / 100);
                }
            } else {
                $costo = $compra['purchaseDetails_unitCost'];
                $importe = $compra['purchaseDetails_amount'];
                $total = $compra['purchaseDetails_total'];
                $impuestos = $compra['purchaseDetails_amount'] * ($compra['purchaseDetails_ivaPorcent'] / 100);

            }

            //hacemos lo mismo con el importe
            ?>

            <td style="text-align: right">${{ number_format($costo, 2)}}</td>
            <td style="text-align: right">${{ number_format($importe, 2)}}</td>
            <td style="text-align: right">${{ number_format($importe, 2)}}</td>
            
            <td style="text-align: right">${{number_format($impuestos, 2)}}</td>
            <td style="text-align: right">${{ number_format($total, 2)}}</td>
        </tr>

        @if ($key % 18 == 0 && $key != 0)
        </table>
        <div class="page-break"></div>
        <table class="articulos-table" style="margin-top: 0px;">
            <tr>
                <th>
                    <p>#</p>
                </th>
                <th  style="width: 150px">
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
            @endif
       

        @endforeach
        
        <?php
        $totalUnidad = 0;
        $totalImporte = 0;
        $totalSubtotal = 0;
        $totalImpuestos = 0;
        $totalTotal = 0;

        foreach ($proveedorPorCompra ['compras'] as $compra) {
            $totalUnidad += $compra['purchaseDetails_quantity'];
            if ($moneda == 'Todos') {
                if ($compra['purchase_money'] != $parametro[0]->generalParameters_defaultMoney) {
                    $totalImporte += $compra['purchaseDetails_amount'] * $compra['purchase_typeChange'];
                    $totalSubtotal += $compra['purchaseDetails_amount'] * $compra['purchase_typeChange'];
                    $totalImpuestos += $compra['purchaseDetails_amount'] * ($compra['purchaseDetails_ivaPorcent'] / 100) * $compra['purchase_typeChange'];
                    $totalTotal += $compra['purchaseDetails_total'] * $compra['purchase_typeChange'];
                } else {
                    $totalImporte += $compra['purchaseDetails_amount'];
                    $totalSubtotal += $compra['purchaseDetails_amount'];
                    $totalImpuestos += $compra['purchaseDetails_amount'] * ($compra['purchaseDetails_ivaPorcent'] / 100);
                    $totalTotal += $compra['purchaseDetails_total'];
                }
            } else {
                $totalImporte += $compra['purchaseDetails_amount'];
                $totalSubtotal += $compra['purchaseDetails_amount'];
                $totalImpuestos += $compra['purchaseDetails_amount'] * ($compra['purchaseDetails_ivaPorcent'] / 100);
                $totalTotal += $compra['purchaseDetails_total'];
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
                <p style="text-align: center">{{ number_format($totalUnidad, $compra['units_decimalVal']) }}</p>
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
        </table>
        <br>

        
        @endforeach
</table>

</body>

</html>
