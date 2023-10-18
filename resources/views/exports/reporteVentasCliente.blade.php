<table>
    
    @foreach ($clientesVentas as $clientesPorVenta)
    <tr>
        <td colspan="10" style="text-align: center; font-weight: bold; height: 30px">
            <p>{{ $clientesPorVenta['customers_businessName']}}</p>

        </td>

    </tr>




        <tr>
            <th>
                <strong><p>#</p></strong>
            </th>
            <th  style="width: 100px">
                <p>MOVIMIENTO</p>
            </th>
            <th >
                <strong><p>NOMBRE DEL ARTÍCULO</p></strong>
            </th>
            <th>
                <strong><p>TOTAL UNIDAD</p></strong>
            </th>
            <th>
                <strong><p>UNIDAD</p></strong>
            </th>
            <th>
                <strong><p>COSTO</p></strong>
            </th>
                <th>
                <strong><p>IMPORTE</p></strong>
            </th>
            <th>
                <strong><p>SUBTOTAL</p></strong>
            </th>
            <th style="width: 100px">
                <strong><p>IMPUESTOS</p></strong>
            </th>
            <th>
                <strong><p>TOTAL</p></strong>
            </th>
        </tr>
        

        @foreach ($clientesPorVenta ['ventas'] as $key => $venta)
        <tr>
            <td>{{ $key + 1}}</td>
            <td>{{ $venta['sales_movement']}}</td>
            <td>{{ $venta['salesDetails_article']}} - {{ $venta['salesDetails_descript']}}</td>
            <td>{{ $venta['salesDetails_quantity']}}</td>
            <td>{{ $venta['salesDetails_unit']}}</td>
            <td>${{ number_format($venta['salesDetails_unitCost'], 2)}}</td>
            <td>${{ number_format($venta['salesDetails_amount'], 2)}}</td>
            <td>${{ number_format($venta['salesDetails_amount'], 2)}}</td>
            <?php
            $impuestos = $venta['salesDetails_amount'] * ($venta['salesDetails_ivaPorcent'] / 100);
            ?>
            <td>${{number_format($impuestos, 2)}}</td>
            <td>${{ number_format($venta['salesDetails_total'], 2)}}</td>
        </tr>


            <?php
            $totalUnidad = 0;
            $totalImporte = 0;
            $totalSubtotal = 0;
            $totalImpuestos = 0;
            $totalTotal = 0;

            foreach ($clientesPorVenta ['ventas'] as $venta) {
                $totalUnidad += $venta['salesDetails_unitCost'];
                $totalImporte += $venta['salesDetails_amount'];
                $totalSubtotal += $venta['salesDetails_amount'];
                $totalImpuestos += $venta['salesDetails_amount'] * ($venta['salesDetails_ivaPorcent'] / 100);
                $totalTotal += $venta['salesDetails_total'];
            }
            ?>

@endforeach
        
        <?php
        $totalUnidad = 0;
        $totalImporte = 0;
        $totalSubtotal = 0;
        $totalImpuestos = 0;
        $totalTotal = 0;

        foreach ($clientesPorVenta ['ventas'] as $venta) {
            $totalUnidad += $venta['salesDetails_quantity'];
            $totalImporte += $venta['salesDetails_amount'];
            $totalSubtotal += $venta['salesDetails_amount'];
            $totalImpuestos += $venta['salesDetails_amount'] * ($venta['salesDetails_ivaPorcent'] / 100);
            $totalTotal += $venta['salesDetails_total'];
        }
        ?>

        <tr>
            <th>
                <p> </p>
            </th>
            <th>
                <p> </p>
            </th>
            <th style="text-align: left">
                <strong><p>TOTAL UNIDAD</p></strong>
            </th>
            <th style="text-align: right">
                <strong><p>{{ ($totalUnidad) }}</p></strong>
            </th>
            <th>
                <p> </p>
            </th>
            <th>
                <strong><p>TOTALES</p></strong>
            </th>
            <th>
                <strong><p>${{ number_format($totalImporte, 2)}}</p></strong>
            </th>
            <th>
                <strong><p>${{ number_format( $totalSubtotal, 2) }}</p></strong>
            </th>
            <th>
                <strong><p>${{ number_format($totalImpuestos,2)}}</p></strong>
            </th>
            <th>
                <strong><p>${{ number_format($totalTotal, 2) }}</p></strong>
            </th>
        </tr>
        <br>
        @endforeach
</table>