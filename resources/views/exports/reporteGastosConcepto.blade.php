<table class="ancho">
    <tr>
        <td colspan="12" style="text-align: center; font-weight:bold; padding:5px">
            <p><strong>Gastos por Concepto</strong></p>
        </td>
    </tr>

</table>

<table class="articulos-table">
    <tr>
        <th>
            <p>#</p>
        </th>
        <th>
            <p>MOVIMIENTO</p>
        </th>
        <th>
            <p>FECHA EMISIÓN</p>
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
            <p>CANTIDAD</p>
        </th>
        <th  style="width: 70px">
            <p>IMPORTE</p>
        </th>
        <th  style="width: 80px">

            <p>IMPUESTOS</p>
        </th>
        <th  style="width: 100px">

            <p>RETENCIONES</p>
        </th>
        <th  style="width: 60px">

            <p>TOTAL</p>
        </th>
    </tr>

    @foreach ($gastos as $key => $gasto)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $gasto->expenses_movement}} {{ $gasto->expenses_movementID }}</td>
            <td>{{ \Carbon\Carbon::parse($gasto->expenses_issueDate)->format('d/m/Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($gasto->expenses_expiration)->format('d/m/Y') }}</td>
            <td>{{ $gasto->providers_name }}</td>
            <td>{{ $gasto->expensesDetails_concept }}</td>
            <td>{{ $gasto->expensesDetails_reference }}</td>
            <td style="text-align: right;">{{ number_format($gasto->expensesDetails_quantity, 0) }}</td>
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
            <p class="numeros-reportes">${{ number_format($importe, 2) }}</p>
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