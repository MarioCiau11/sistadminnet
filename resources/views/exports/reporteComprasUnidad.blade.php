<table class="articulos-table">
    <tr>
        <th>
            <p>#</p>
        </th>
        <th>
            <p>FECHA MOV.</p>
        </th>
        <th>
            <p>TOTAL UNIDAD</p>
        </th>
        <th>
            <p>UNIDAD</p>
        </th>
        <th>
            <p>COSTO PROM</p>
        </th>
        <th>
            <p>IMPORTE</p>
        </th>
    </tr>

    @foreach ($compras as $key => $compra)
        <tr>
            <td style="text-align: center">{{ $key + 1 }}</td>
            <td style="text-align: center">{{ $compra->purchase_issueDate }}</td>
            <td style="text-align: center">{{ number_format($compra->purchaseDetails_unitCost, 2) }}</td>
            <td style="text-align: center">{{ $compra->purchaseDetails_unit }}</td>
            <td>${{ number_format($compra->articlesCost_averageCost, 2) }}</td>
            <?php
            $importe = $compra->purchaseDetails_unitCost * $compra->articlesCost_averageCost;
            ?>
            <td >${{ number_format($importe, 2) }}</td>
        </tr>
    @endforeach
</table>

<table class="articulos-table2">
   
    <?php

    $total = 0;
    $totalImporte = 0;
    foreach ($compras as $compra) {
        $total += $compra->purchaseDetails_unitCost;
        $importe = $compra->purchaseDetails_unitCost * $compra->articlesCost_averageCost;
        $totalImporte += $importe;
    }
    ?>
    <tr>
        <th>
            <p> </p>
        </th>
        <th>
            <p>TOTALES</p>
        </th>
        <th>
            <p>{{number_format($total, 2)}}</p>
        </th>
        <th>
            <p>KG</p>
        </th>
        <th>
            <p>TOTAL IMPORTE</p>
        </th>
        <th>
            <p>${{number_format($totalImporte, 2)}}</p>
        </th>
    </tr>
</table>