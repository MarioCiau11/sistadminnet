<tr>
    <td class="td-option" style="padding-right: 3px;padding-left: 3px;padding-bottom: 20px;">

        <div>
            <a href="{{ route('vista.modulo.ventas.create-venta', ['id' => $venta['sales_id']]) }}" class="show"
                data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-folder-open"
                    aria-hidden="true" style="color:black"></i></a>

        </div>
    </td>
    <td>
        {{-- Creamos un objeto que nos devolvera el icono correspondiente a cada estado --}}
        @switch($venta['sales_status'])
            @case('INICIAL')
                <span class="glyphicon glyphicon-exclamation-sign "></span>
            @break

            @case('POR AUTORIZAR')
                <span class="glyphicon glyphicon-asterisk warning"></span>
            @break

            @case('FINALIZADO')
                <span class="glyphicon glyphicon-ok success"></span>
            @break

            @case('CANCELADO')
                <span class="glyphicon glyphicon-remove cancelado"></span>
            @break

            @default
        @endswitch

        {{ $venta['sales_movement'] }}
    </td>
    <td>{{ $venta['sales_movementID'] }}</td>
    <td>{{ \Carbon\Carbon::parse($venta['sales_issuedate'])->format('d/m/Y') }}</td>
    <td>{{ $venta['sales_money'] }}</td>
    <td>{{ $venta['sales_typeChange'] == null ? '$0.00' : '$' . number_format($venta['sales_typeChange'], 2) }}</td>
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
    <td style="text-align: right">
        {{ $venta['sales_amount'] == null ? '$0.00' : '$' . number_format($venta['sales_amount'], 2) }}</td>
    <td style="text-align: right">
        {{ $venta['sales_taxes'] == null ? '$0.00' : '$' . number_format($venta['sales_taxes'], 2) }}</td>
    <?php
    $importe = $venta['sales_amount'] !== null ? (float) $venta['sales_amount'] : 0;
    $taxes = $venta['sales_taxes'] !== null ? (float) $venta['sales_taxes'] : 0;
    $total = $importe + $taxes;
    ?>


    <td style="text-align: right">
        {{ $venta['sales_total'] == null ? '$0.00' : '$' . number_format($venta['sales_total'], 2) }}</td>
    <td>{{ $venta['sales_user'] }}</td>
    <td>{{ $venta['sales_status'] }}</td>
    <td>{{ $venta['depots_name'] }}</td>
    <td>{{ $venta['sales_reasonCancellation'] }}</td>
    <td>{{ $venta['branchOffices_name'] }}</td>
    <td>{{ $venta['sales_seller'] }}</td>
    {{-- <td>{{ number_format($venta['sales_advanced'], 2) . ' %' }}</td> --}}
    <td>{{ $venta['sales_stamped'] ? 'SI' : 'NO' }}</td>

</tr>
