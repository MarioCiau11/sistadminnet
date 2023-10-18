<tr>
    <td class="td-option">
        <div>
            <a href="{{ route('vista.modulo.tesoreria.create-tesoreria', ['id' => $din['treasuries_id']]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i
                    class="fa fa-folder-open" aria-hidden="true" style="color:black"></i></a>

        </div>
    </td>
    <td>
        {{-- Creamos un objeto que nos devolvera el icono correspondiente a cada estado --}}
        @switch($din['treasuries_status'])
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

        {{ $din['treasuries_movement'] }}
    </td>
    <td>{{ $din['treasuries_movementID'] }}</td>
    <td>{{ \Carbon\Carbon::parse($din['treasuries_issuedate'])->format('d/m/Y') }}</td>
    <td>{{ $din['treasuries_concept'] }}</td>
    <td>{{ $din['treasuries_money'] }}</td>
    <td>{{ $din['treasuries_typeChange'] == null ? '$0.00' : '$' . number_format($din['treasuries_typeChange'], 2) }}
    </td>

    <td>{{ $din['treasuries_moneyAccount'] === null ? $din['treasuries_moneyAccountOrigin'] : $din['treasuries_moneyAccount'] }}
    </td>
    <td>{{ $din['treasuries_moneyAccountOrigin'] }}</td>
    <td>{{ $din['treasuries_moneyAccountDestiny'] }}</td>
    <td>{{ $din['treasuries_accountBalance'] }}</td>
    <td>{{ $din['formsPayment_name'] }}</td>
    <td>{{ $din['treasuries_movement'] === 'Solicitud Depósito' || $din['treasuries_movement'] === 'Depósito' || ($din['treasuries_movement'] === 'Sol. de Cheque/Transferencia' && $din['treasuries_originType'] === 'CxC') || ($din['treasuries_movement'] === 'Transferencia Electrónica' && $din['treasuries_reference'] === 'Devolución de Anticipo') ? $din['customers_businessName'] : $din['providers_name'] }}
    </td>
    <td>{{ $din['treasuries_reference'] }}</td>
    <td>{{ $din['treasuries_observations'] }}</td>
    <td>{{ $din['treasuries_company'] }}</td>
    <td>{{ $din['treasuries_branchOffice'] }}</td>
    <td>{{ $din['treasuries_user'] }}</td>
    <td>{{ $din['treasuries_status'] }}</td>
    <td style="text-align: right">${{ number_format($din['treasuries_amount'], 2) }}</td>
    <td style="text-align: right">${{ number_format($din['treasuries_taxes'], 2) }}</td>
    <td style="text-align: right">${{ number_format($din['treasuries_total'], 2) }}</td>
</tr>

{{-- <td>{{ $din['purchase_status'] }}</td>
    <td>{{ $din['purchase_amount'] == null ? '$0.00' : '$' . number_format($din['purchase_amount'], 2) }}
    </td>
    <td>{{ $din['purchase_taxes'] == null ? '$0.00' : '$' . number_format($din['purchase_taxes'], 2) }}</td> --}}


<?php
// $importe = $din['purchase_amount'] !== null ? (float) $din['purchase_amount'] : 0;
// $taxes = $din['purchase_taxes'] !== null ? (float) $din['purchase_taxes'] : 0;
// $total = $importe + $taxes;
?>


{{-- <td>{{ $din['purchase_total'] == null ? '$0.00' : '$' . number_format($total, 2) }}</td> --}}
{{-- <td>{{ $din['purchase_lines'] }}</td>
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
