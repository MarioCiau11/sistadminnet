<tr>
    <td class="td-option">
        <div>
            <a href="{{ route('vista.modulo.compras.create-compra', ['id' => $compra['purchase_id']]) }}" class="show"
                data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-folder-open"
                    aria-hidden="true" style="color:black"></i></a>

        </div>
    </td>
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
    <td>{{ $compra['purchase_movementID'] }}</td>
    <td>{{ \Carbon\Carbon::parse($compra['purchase_issueDate'])->format('d/m/Y') }}</td>
    {{-- <td>{{ $compra['purchase_lastChange'] }}</td> --}}
    <td>{{ $compra['purchase_concept'] }}</td>
    <td>{{ $compra['purchase_money'] }}</td>
    {{-- <td>{{ $compra['purchase_typeChange'] }}</td> --}}
    <td>{{ $compra['purchase_provider'] }}</td>
    <td>{{ $compra['providers_name'] }}</td>
    <td>{{ $compra['creditConditions_name'] }}</td>
    <td>{{ \Carbon\Carbon::parse($compra['purchase_expiration'])->format('d/m/Y') }}</td>
    <td>{{ $compra['purchase_reference'] }}</td>
    {{-- <td>{{ $compra['companies_nameShort'] }}</td> --}}
    <td>{{ $compra['branchOffices_name'] }}</td>
    <td>{{ $compra['depots_name'] }}</td>
    <td>{{ $compra['purchase_reasonCancellation'] }}</td>
    <td>{{ $compra['purchase_user'] }}</td>
    <td>{{ $compra['purchase_status'] }}</td>
    <td style="text-align: right">
        {{ $compra['purchase_amount'] == null ? '$0.00' : '$' . number_format($compra['purchase_amount'], 2) }}
    </td>
    <td style="text-align: right">
        {{ $compra['purchase_taxes'] == null ? '$0.00' : '$' . number_format($compra['purchase_taxes'], 2) }}</td>


    <?php
    $importe = $compra['purchase_amount'] !== null ? (float) $compra['purchase_amount'] : 0;
    $taxes = $compra['purchase_taxes'] !== null ? (float) $compra['purchase_taxes'] : 0;
    $total = $importe + $taxes;
    ?>


    <td style="text-align: right">{{ $compra['purchase_total'] == null ? '$0.00' : '$' . number_format($total, 2) }}
    </td>
    {{-- <td>{{ $compra['purchase_lines'] }}</td>
    <td>{{ $compra['purchase_originType'] }}</td>
    <td>{{ $compra['purchase_origin'] }}</td>
    <td>{{ $compra['purchase_originID'] }}</td>
    <td>{{ $compra['purchase_ticket'] }}</td>
    <td>{{ $compra['purchase_operator'] }}</td>
    <td>{{ $compra['purchase_plates'] }}</td>
    <td>{{ $compra['purchase_material'] }}</td>
    <td>{{ $compra['purchase_inputWeight'] }}</td>
    <td>{{ $compra['purchase_inputDateTime'] }}</td>
    <td>{{ $compra['purchase_outputWeight'] }}</td>
    <td>{{ $compra['purchase_outputDateTime'] }}</td> --}}
</tr>
