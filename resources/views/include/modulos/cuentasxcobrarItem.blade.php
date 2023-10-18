<tr>
    <td class="td-option" style="padding-right: 3px;padding-left: 3px;padding-bottom: 20px;">
        <div>
            <a href="{{ route('vista.modulo.cuentasCobrar.create-cxc', ['id' => $cxc['accountsReceivable_id']]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i
                    class="fa fa-folder-open" aria-hidden="true" style="color: black; " </div>
    </td>
    <td>
        {{-- Creamos un objeto que nos devolvera el icono correspondiente a cada estado --}}
        @switch($cxc['accountsReceivable_status'])
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

        {{ $cxc['accountsReceivable_movement'] }}
    </td>
    <td>{{ $cxc['accountsReceivable_movementID'] }}</td>
    <td>{{ \Carbon\Carbon::parse($cxc['accountsReceivable_issuedate'])->format('d/m/Y') }}</td>
    <td>{{ $cxc['accountsReceivable_expiration'] != null ? \Carbon\Carbon::parse($cxc['accountsReceivable_expiration'])->format('d/m/Y') : null }}
    </td>
    <td>{{ $cxc['accountsReceivable_money'] }}</td>
    <td>{{ $cxc['accountsReceivable_typeChange'] == null ? '$0.00' : '$' . number_format($cxc['accountsReceivable_typeChange'], 2) }}
    </td>
    <td>{{ $cxc['accountsReceivable_moneyAccount'] }}</td>
    <td>{{ $cxc['accountsReceivable_customer'] }}</td>
    <td>{{ $cxc['customers_businessName'] }}</td>
    <td>{{ $cxc['accountsReceivable_formPayment'] }}</td>
    {{-- <td>{{ $cxc['providers_name'] }}</td> --}}
    <?php
    $importe = $cxc['accountsReceivable_amount'] !== null ? (float) $cxc['accountsReceivable_amount'] : 0;
    $taxes = $cxc['accountsReceivable_taxes'] !== null ? (float) $cxc['accountsReceivable_taxes'] : 0;
    $retencionISR = $cxc['accountsReceivable_retentionISR'] !== null || $cxc['accountsReceivable_retentionISR'] != 0.0 ? (float) $cxc['accountsReceivable_retentionISR'] : 0;
    $retencionIVA = $cxc['accountsReceivable_retentionIVA'] !== null || $cxc['accountsReceivable_retentionIVA'] != 0.0 ? (float) $cxc['accountsReceivable_retentionIVA'] : 0;
    $total = $importe + $taxes - ($retencionISR + $retencionIVA);
    ?>
    <td>{{ $cxc['accountsReceivable_observations'] }}</td>
    <td style="text-align: right">
        {{ $cxc['accountsReceivable_amount'] == null ? '$0.00' : '$' . number_format($importe, 2) }}
    </td>


    {{-- <td>{{ $cxc['companies_nameShort'] }}</td> --}}
    <td style="text-align: right">
        {{ $cxc['accountsReceivable_taxes'] == null ? '$0.00' : '$' . number_format($cxc['accountsReceivable_taxes'], 2) }}
    </td>
    <td style="text-align: right">
        {{ $cxc['accountsReceivable_movement'] == 'Factura' ? '$' . number_format($retencionISR + $retencionIVA, 2) : '$0.00' }}
    </td>


    <td style="text-align: right">
        {{ $cxc['accountsReceivable_movement'] != 'Factura' ? '$' . number_format($cxc['accountsReceivable_total'], 2) : '$' . number_format($total, 2) }}
    </td>
    @if ($cxc['accountsReceivable_status'] === 'FINALIZADO')
        <td style="text-align: right">
            {{ '$0.00' }}
        </td>
    @else
        <td style="text-align: right">
            {{ $cxc['accountsReceivable_balance'] == null ? '$0.00' : '$' . number_format($cxc['accountsReceivable_balance'], 2) }}
        </td>
    @endif

    <td>{{ $cxc['accountsReceivable_concept'] }}</td>
    <td>{{ $cxc['creditConditions_name'] }}</td>
    <td>{{ $cxc['accountsReceivable_reference'] }}</td>
    <td>{{ $cxc['branchOffices_name'] }}</td>
    <td>{{ $cxc['accountsReceivable_user'] }}</td>
    <td>{{ $cxc['accountsReceivable_status'] }}</td>
    <td>{{ $cxc['accountsReceivable_stamped'] ? 'SI' : 'NO' }}</td>


</tr>
