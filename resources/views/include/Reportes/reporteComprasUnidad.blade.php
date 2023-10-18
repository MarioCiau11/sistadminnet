<tr>
    <td>
        {{-- Creamos un objeto que nos devolvera el icono correspondiente a cada estado --}}
        @switch($compra['purchase_status'])
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

        {{ $compra['purchase_movement'] }}
    </td>
    <td>{{ $compra['purchaseDetails_unit'] }}</td>
    <td>{{ $compra['depots_name'] }}</td>
    <td>{{ $compra['branchOffices_name'] }}</td>
    <td>{{ \Carbon\Carbon::parse($compra['purchase_issueDate'])->format('d/m/Y') }}</td>
    {{-- <td>{{ $compra['purchase_lastChange'] }}</td> --}}
    <td>{{ $compra['purchase_money'] }}</td>
    <td>{{ $compra['purchase_status'] }}</td>
    <td>{{ number_format($compra['purchaseDetails_inventoryAmount'], $compra['units_decimalVal']) }}</td>
    <?php
    $importe = $compra['purchaseDetails_unitCost'];
    ?>
    <td style="text-align: right;">${{ number_format($importe, 2) }}</td>

</tr>
