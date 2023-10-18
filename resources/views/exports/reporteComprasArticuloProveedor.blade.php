<table class="ancho">
    <tr>
        <td colspan="10" style="text-align: center; font-weight: bold; padding:5px">
            <h3><strong>REPORTE DE COMPRAS POR PROVEEDOR Y ARTÍCULO
            </strong></h3>
        </td>
    </tr>
</table>
 <table class="articulos-table">
     @foreach ($proveedorCompras as $proveedorPorCompra)
         <tr>
             <td colspan="10" style="text-align: center; font-weight: bold; height: 30px">
                 <p>{{ $proveedorPorCompra['providers_name'] }}</p>

             </td>



         </tr>

         <tr>
             <th>
                 <p>#</p>
             </th>
             <th style="font-weight: bold">
                 <p>MOVIMIENTO</p>
             </th>
             <th style="font-weight: bold">
                 <p>NOMBRE DEL ARTICULO</p>
             </th>
             <th style="font-weight: bold">
                 <p>TOTAL UNIDAD</p>
             </th>
             <th style="font-weight: bold">
                 <p>UNIDAD</p>
             </th>
             <th style="font-weight: bold">
                 <p>COSTO</p>
             </th>
             <th style="font-weight: bold">
                 <p>IMPORTE</p>
             </th>
             <th style="font-weight: bold">
                 <p>SUBTOTAL</p>
             </th>
             <th style="font-weight: bold; width: 150%">
                 <p>IMPUESTOS</p>
             </th>
             <th style="font-weight: bold">
                 <p>TOTAL</p>
             </th>
         </tr>


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
             <th style="font-weight: bold">
                 <p>TOTAL UNIDAD</p>
             </th>
             <th style="font-weight: bold">
                 <p>{{ number_format($totalUnidad, 2) }}</p>
             </th>
             <th>
                 <p> </p>
             </th>
             <th style="font-weight: bold">
                 <p>TOTALES</p>
             </th>
             <th style="font-weight: bold">

                 <p>${{ number_format($totalImporte, 2) }}</p>
             </th>
             <th style="font-weight: bold">
                 <p>${{ number_format($totalSubtotal, 2) }}</p>
             </th>
             <th style="font-weight: bold">
                 <p>${{ number_format($totalImpuestos, 2) }}</p>
             </th>
             <th style="font-weight: bold">
                 <p>${{ number_format($totalTotal, 2) }}</p>
             </th>
         </tr>
     @endforeach
 </table>
 <br>
