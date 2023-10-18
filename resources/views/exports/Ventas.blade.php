<table>
    <thead>
        <tr>
            <th>Movimiento</th>
            <th>Folio</th>
            <th>Fecha de Emisión</th>
            <th>Moneda</th>
            <th>Tipo de Cambio</th>
            <th>Concepto</th>
            <th>Clave Cliente</th>
            <th>Cliente</th>
            <th>Lista de Precios</th>
            <th>Condición de Pago</th>
            <th>Vencimiento</th>
            <th>Referencia</th>
            <th>Vendedor</th>
            <th>Motivo de Cancelación</th>
            <th>SubTotal</th>
            <th>IVA</th>
            <th>Retencion 1</th>
            <th>Retencion 2</th>
            <th>Total</th>
            <th>Usuario</th>
            <th>Estatus</th>
            <th>Almacen</th>
            <th>Sucursal</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($ventas as $venta)
<tr>
            
    <td>{{ $venta['sales_movement'] }}</td>
    <td>{{ $venta['sales_movementID'] }}</td>
    <td>{{ \Carbon\Carbon::parse($venta['sales_issuedate'])->format('d/m/Y') }}</td>
    <td>{{ $venta['sales_money'] }}</td>
    <td>{{ $venta['sales_typeChange'] }}</td>
    <td>{{ $venta['sales_concept'] }}</td>
    <td>{{ $venta['sales_customer'] }}</td>
    <td>{{ $venta['customers_businessName'] }}</td>
    @if ($venta['sales_listPrice'] === 'articles_listPrice1')
        <td>Precio 1</td>
    @elseif($venta['sales_listPrice'] === 'articles_listPrice2')
        <td>Precio 2</td>
    @elseif($venta['sales_listPrice'] === 'articles_listPrice3')
        <td>Precio 3</td>
    @elseif($venta['sales_listPrice'] === 'articles_listPrice4')
        <td>Precio 4</td>
    @elseif($venta['sales_listPrice'] === 'articles_listPrice5')
        <td>Precio 5</td>
    @endif
    <td>{{ $venta['creditConditions_name'] }}</td>
    <td>{{ \Carbon\Carbon::parse($venta['sales_expiration'])->format('d/m/Y') }}</td>
    <td>{{ $venta['sales_reference'] }}</td>
    {{-- <td>{{ $venta['companies_nameShort'] }}</td> --}}
    <td>{{ $venta['sales_seller'] }}</td>
    <td>{{ $venta['sales_reasonCancellation'] }}</td>
    <td>{{ $venta['sales_amount'] == null ? '$0.00' : '$' . number_format($venta['sales_amount'], 2) }}</td>
    <td>{{ $venta['sales_taxes'] == null ? '$0.00' : '$' . number_format($venta['sales_taxes'], 2) }}</td>
    <td>{{ $venta['salesDetails_retentionISR'] == null ? '$0.00' : '$' . number_format($venta['salesDetails_retentionISR'], 2) }}</td>
    <td>{{ $venta['salesDetails_retentionIVA'] == null ? '$0.00' : '$' . number_format($venta['salesDetails_retentionIVA'], 2) }}</td>
    <?php
    $importe = $venta['sales_amount'] !== null ? (float) $venta['sales_amount'] : 0;
    $taxes = $venta['sales_taxes'] !== null ? (float) $venta['sales_taxes'] : 0;
    $total = $importe + $taxes;
    ?>
    <td>{{ $venta['sales_total'] == null ? '$0.00' : '$' . number_format($venta['sales_total'], 2) }}</td>
    <td>{{ $venta['sales_user'] }}</td>
    <td>{{ $venta['sales_status'] }}</td>
    <td>{{ $venta['depots_name'] }}</td>
    <td>{{ $venta['branchOffices_name'] }}</td>

</tr>
@endforeach
    </tbody>
</table>