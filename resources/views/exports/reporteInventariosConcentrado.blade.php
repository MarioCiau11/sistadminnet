
<table class="ancho">
    <tr>
        <td colspan="5" style="text-align: center; font-weight:bold; padding:5px">
            <p><strong>Auxiliares - Inventario (Unidades) </strong></p>
        </td>
    </tr>

</table>
<table class="articulos-table2">
    <tr>
        <th style="width: 50px">
            <p>CLAVE</p>
        </th>
        <th style="width: 500px">
            <p>NOMBRE DEL ARTÍCULO</p>
        </th>
        <th style="width: 100px">
            <p>ENTRADAS</p>
        </th>
        <th>
            <p>SALIDAS</p>
        </th>
        <th style="width: 100px">
            <p>SALDO</p>
        </th>
    </tr>

    <?php
    $entradas = [];
    $salidas = [];
    $saldo = [];
    $decimal = 0;
    $totalEntrada = 0;
    $totalSalida = 0;
    $totalSaldo = 0;
    
    foreach ($inventario_array as $inv) {
        if (!array_key_exists($inv['assistantUnit_account'], $entradas)) {
            $entradas[$inv['assistantUnit_account']] = $inv->assistantUnit_chargeUnit;
        } else {
            $entradas[$inv['assistantUnit_account']] += $inv->assistantUnit_chargeUnit;
        }
    }
    
    foreach ($inventario_array as $inv) {
        if (!array_key_exists($inv['assistantUnit_account'], $salidas)) {
            $salidas[$inv['assistantUnit_account']] = $inv->assistantUnit_paymentUnit;
        } else {
            $salidas[$inv['assistantUnit_account']] += $inv->assistantUnit_paymentUnit;
        }
    }
    
    foreach ($inventario_array as $inv) {
        if (!array_key_exists($inv['assistantUnit_account'], $saldo)) {
            $saldo[$inv['assistantUnit_account']] = $inv->assistantUnit_chargeUnit - $inv->assistantUnit_paymentUnit;
        } else {
            $saldo[$inv['assistantUnit_account']] += $inv->assistantUnit_chargeUnit - $inv->assistantUnit_paymentUnit;
        }
    }
    
    foreach ($inventario as $key => $inv) {
        if ($inv['units_decimalVal'] > $decimal) {
            $decimal = $inv['units_decimalVal'];
        }
    }
    
    ?>
    @foreach ($articulos as $key => $articulo)
        <tr>
            <td>{{ $articulo->assistantUnit_account }}</td>
            <td>{{ $articulo->articles_descript }}</td>
            <td style="text-align: right;">
                {{ number_format(array_key_exists($articulo->assistantUnit_account, $entradas) ? $entradas[$articulo->assistantUnit_account] : 0, $decimal) }}
                <?php $totalEntrada += $entradas[$articulo->assistantUnit_account]; ?>
            </td>
            <td style="text-align: right;">
                {{ number_format(array_key_exists($articulo->assistantUnit_account, $salidas) ? $salidas[$articulo->assistantUnit_account] : 0, $decimal) }}
                <?php $totalSalida += $salidas[$articulo->assistantUnit_account]; ?>
            </td>
            <td style="text-align: right;">
                {{ number_format(array_key_exists($articulo->assistantUnit_account, $saldo) ? $saldo[$articulo->assistantUnit_account] : 0, $decimal) }}
                <?php $totalSaldo += $saldo[$articulo->assistantUnit_account]; ?>
            </td>
        </tr>
    @endforeach

    <tr>
        <td></td>
        <td style="text-align: right;"><strong>TOTAL</strong></td>
        <td style="text-align: right;">
            <strong>{{ number_format($totalEntrada, 0) }}</strong>
        </td>
        <td style="text-align: right;">
            <strong>{{ number_format($totalSalida, 0) }}</strong>
        </td>
        <td style="text-align: right;">
            <strong>{{ number_format($totalSaldo, 0) }}</strong>
        </td>
    </tr>
</table>

<table class="articulos-table2">

    <?php
    $entradas = 0;
    $salidas = 0;
    $saldo = 0;
    $decimal = 0;

    foreach ($inventario as $key => $inv) {
        if ($inv['units_decimalVal'] > $decimal) {
            $decimal = $inv['units_decimalVal'];
        }
    }

    //Ahora se hace el calculo de las entradas y salidas
    foreach ($inventario as $key => $inv) {
        $entradas += $inv->assistantUnit_chargeUnit;
        $salidas += $inv->assistantUnit_paymentUnit;
        $saldo += $inv->assistantUnit_chargeUnit - $inv->assistantUnit_paymentUnit;
    }
    ?>    
        <tr>
            <td style=" border: none;"></td>
            <td style="border: none; text-align: right;"><strong>Totales</strong></td>
            <td style="text-align: right; border: none;"><strong>{{ number_format($entradas, 2) }}</strong></td>
            <td style="text-align: right; border: none;">
                <strong>{{ number_format($salidas, 2) }}</strong></td>
            <td style="text-align: right; border: none;"><strong>{{ number_format($saldo, 2) }}</strong>
            </td>
        </tr>
</table>
