<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de gastos</title>

    <link rel="stylesheet" href="{{ asset('css/reportes/reportes.css') }}">


</head>

<body>
    <table class="cabecera ancho">
        <tr>
            <td class="logo">
                <img src="{{ $logo }}" alt="Logo de la empresa">
            </td>


            <td class="info-empresa">
                <h3>{{ $gasto->companies_name }}</h3>
                <p>R.F.C. {{ $gasto->companies_rfc }}</p>
            </td>

            <td class="info-compra">
                <p><strong>Fecha Emision: </strong>
                    {{ \Carbon\Carbon::parse($gasto->expenses_issueDate)->format('d/m/Y') }}</p>
                <h3><strong>Folio :</strong> <span class="folio-bold">{{ $gasto->expenses_movementID }}</span>
                </h3>
                <p><strong>Estatus: </strong> {{ $gasto->expenses_status }}</p>
            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>{{ $gasto->expenses_movement }}</strong></h3>
            </td>
        </tr>

    </table>

    <table class="informacion-proveedor">
        <tr>
            <td>
                <p>PROVEEDOR:</p>
            </td>
            <td>
                <p>{{ $gasto->providers_name }}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>DOMICILIO:</p>
            </td>
            <td>
                <p>{{ $gasto->providers_address . ' ' . $gasto->providers_roads . ' ' . $gasto->providers_outdoorNumber . ' ' . $gasto->providers_interiorNumber . ' ' . $gasto->providers_colonyFractionation . ' ' . $gasto->providers_state . ' ' . $gasto->providers_country }}

            </td>
        </tr>
        <tr>
            <td>
                <p>CONDICIÓN:</p>
            </td>
            <td>
                <p>{{ $gasto->creditConditions_name }}</p>
            </td>
        </tr>

    </table>

    <table class="articulos-table">
        <tr>
            <th>
                <p>#</p>
            </th>
            <th>
                <p>CONCEPTO</p>
            </th>
            <th>
                <p>REFERENCIA</p>
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

        <?php
        $sumaRetenciones = 0;
        $sumaImpuestos = 0;
        $sumaImportes = 0;
        $sumaTotal = 0;
        ?>

        @foreach ($concepto_gastos as $key => $gastoConcepto)
            <?php
            $retencionesTotales = $gastoConcepto->expensesDetails_retentionISR + $gastoConcepto->expensesDetails_retentionIVA;
            
            $sumaRetenciones += $retencionesTotales;
            $sumaImpuestos += $gastoConcepto->expensesDetails_vatAmount;
            $sumaImportes += $gastoConcepto->expensesDetails_amount;
            ?>
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $gastoConcepto->expensesDetails_concept }}</td>
                <td>{{ $gastoConcepto->expensesDetails_reference }}</td>
                <td style="text-align: right">${{ number_format($gastoConcepto->expensesDetails_amount, 2) }}</td>
                <td style="text-align: right">${{ number_format($gastoConcepto->expensesDetails_vatAmount, 2) }}</td>
                <td style="text-align: right">${{ number_format($retencionesTotales, 2) }}</td>
                <td style="text-align: right">${{ number_format($gastoConcepto->expensesDetails_total, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <table class="costos-desglosados">
        <tr>
            <td class="anchoCompleto">

            </td>
            <td>
                <p style="text-align: right">Importes</p>
            </td>
            <td>
                <p class="numeros-reportes" style="text-align: right">$ {{ number_format($sumaImportes, 2) }}</p>
            </td>
        </tr>
        <tr>
            <td class="anchoCompleto"></td>
            <td>
                <p style="text-align: right">Impuesto</p>
            </td>
            <td>
                <p class="numeros-reportes" style="text-align: right">$ {{ number_format($sumaImpuestos, 2) }}</p>
            </td>
        </tr>

        <tr>
            <td class="anchoCompleto"></td>
            <td>
                <p style="text-align: right">Retenciones</p>
            </td>
            <td>
                <p class="numeros-reportes" style="text-align: right">$ {{ number_format($sumaRetenciones, 2) }}</p>
            </td>
        </tr>

        <tr>
            <?php
            $sumaTotal = $sumaImportes + $sumaImpuestos + $sumaRetenciones;
            ?>

            <td class="anchoCompleto">
                <p style="text-align: right"><?php
                $formato = new NumeroALetras();
                echo $formato->toMoney($sumaTotal, 2, $gasto->money_key, 'CENTAVOS '), $gasto->money_keySat;
                
                ?></p>
            </td>
            <td>
                <p style="text-align: right">Total</p>
            </td>
            <td>
                <p class="numeros-reportes" style="text-align: right">$ {{ number_format($sumaTotal, 2) }}</p>
            </td>
        </tr>

    </table>
</body>

</html>
