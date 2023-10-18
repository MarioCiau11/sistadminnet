<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GASTOS POR ANTECEDENTE Y ACTIVO FIJO</title>

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
                <h3>{{ $gastoConcepto->companies_name }}</h3>
                <p>R.F.C. {{ $gastoConcepto->companies_rfc }}</p>
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
                <h3><strong>REPORTE DE GASTOS POR ANTECEDENTE O ACTIVO FIJO</strong></h3>
                <h3><strong>{{ $namefecha }}</strong></h3>
            </td>
        </tr>

    </table>


    <table class="articulos-table2">
        <tr>
            <th>
                <p>#</p>
            </th>
            <th>
                <p>MOVIMIENTO</p>
            </th>
            <th>
                <p>FECHA DE EMISIÓN</p>
            </th>
            <th>
                <p>FECHA VENCIMIENTO</p>
            </th>
            <th>
                <p>PROVEEDOR</p>
            </th>
            <th>
                <p>CONCEPTO</p>
            </th>
            <th>
                <p>REFERENCIA</p>
            </th>
            <th>
                <p>ANTECEDENTE/ACTIVO FIJO</p>
            </th>
            <th>
                <p>CANTIDAD</p>
            </th>
            <th>
                <p>IMPORTE</p>
            </th>
            <th>
                <p>IMPUESTOS</p>
            </th>
            <th>
                <p>RETENCIONES</p>
            </th>
            <th>
                <p>TOTAL</p>
            </th>
        </tr>

        @foreach ($gastos as $key => $gasto)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $gasto->expenses_movement . ' ' . $gasto->expenses_movementID }}</td>
                <td>{{ \Carbon\Carbon::parse($gasto->expenses_issueDate)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($gasto->expenses_expiration)->format('d/m/Y') }}</td>
                <td>{{ $gasto->providers_name }}</td>
                <td>{{ $gasto->expensesDetails_concept }}</td>
                <td>{{ $gasto->expensesDetails_reference }}</td>
                <td> {{ $gasto['expenses_antecedentsName'] || $gasto['expenses_fixedAssetsName'].' - '.$gasto['expenses_fixedAssetsSerie'] == '' ? 'Factura' . ' - '.$gasto['expenses_antecedentsName'] : $gasto['expenses_fixedAssetsName'].' - '.$gasto['expenses_fixedAssetsSerie'] }}
                </td>
                <td>{{ number_format($gasto->expensesDetails_quantity, 0) }}</td>
                <td style="text-align: right;">${{ number_format($gasto->expensesDetails_amount, 2) }}</td>
                <td style="text-align: right;">${{ number_format($gasto->expensesDetails_vatAmount, 2) }}</td>
                <?php
                $retenciones = $gasto->expensesDetails_retentionISR + $gasto->expensesDetails_retentionIVA;
                ?>
                <td style="text-align: right;">${{ number_format($retenciones, 2) }}</td>
                <td style="text-align: right;">${{ number_format($gasto->expensesDetails_total, 2) }}</td>
            </tr>
        @endforeach
    </table>
   

    <?php
    $importe = 0;
    $impuestos = 0;
    $retenciones = 0;
    $total = 0;
    
    foreach ($gastos as $key => $gasto) {
        $importe += $gasto->expensesDetails_amount;
        $impuestos += $gasto->expensesDetails_vatAmount;
        $retenciones += $gasto->expensesDetails_retentionISR + $gasto->expensesDetails_retentionIVA;
        $total += $gasto->expensesDetails_total;
    }
    ?>
    <table class="costos-desglosados" style="text-align: right">
        <tr>
            <td class="anchoCompleto">

            </td>
            <td>
                <p>Total Importe</p>
            </td>



            <td>
                <p class="numeros-reportes"> {{ '$' . number_format($importe, 2) }}</p>
            </td>

        </tr>
        <tr>
            <td class="anchoCompleto"></td>
            <td>
                <p>Impuestos</p>
            </td>
            <td>
                <p class="numeros-reportes">$ {{ number_format($impuestos, 2) }}</p>
            </td>
        </tr>

        <tr>
            <td class="anchoCompleto"></td>
            <td>
                <p>Retenciones</p>
            </td>
            <td>
                <p class="numeros-reportes">$ {{ number_format($retenciones, 2) }}</p>
            </td>
        </tr>

        <tr>
            <td class="anchoCompleto">

            </td>
            <td>
                <p>Total</p>
            </td>
            <td>
                <p class="numeros-reportes">$ {{ number_format($total, 2) }}</p>
            </td>
        </tr>

    </table>
</body>

</html>
