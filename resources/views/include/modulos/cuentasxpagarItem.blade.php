<tr>
    <td class="td-option">
        <div class="">
            <a href="{{ route('vista.modulo.cuentasPagar.create-cxp', ['id' => $cuentaxpagar['accountsPayable_id']]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i
                    class="fa fa-folder-open" aria-hidden="true" style="color:black"></i></a>
        </div>
    </td>
    <td>
        @switch($cuentaxpagar['accountsPayable_status'])
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
        {{ $cuentaxpagar['accountsPayable_movement'] }}
    </td>
    <td>{{ $cuentaxpagar['accountsPayable_movementID'] }}</td>
    <td>{{ \Carbon\Carbon::parse($cuentaxpagar['accountsPayable_issuedate'])->format('d/m/Y') }}</td>
    <td>{{ $cuentaxpagar['accountsPayable_expiration'] == null ? '' : \Carbon\Carbon::parse($cuentaxpagar['accountsPayable_expiration'])->format('d/m/Y') }}
    </td>
    <td>{{ $cuentaxpagar['accountsPayable_typeChange'] == null ? '$0.00' : '$' . number_format($cuentaxpagar['accountsPayable_typeChange'], 2) }}
    </td>
    <td>{{ $cuentaxpagar['accountsPayable_moneyAccount'] }}</td>
    <td>{{ $cuentaxpagar['accountsPayable_provider'] }}</td>
    <td>{{ $cuentaxpagar['providers_name'] }}</td>
    <td>{{ $cuentaxpagar['creditConditions_name'] }}</td>
    <td>{{ $cuentaxpagar['accountsPayable_money'] }}</td>
    <td>{{ $cuentaxpagar['formsPayment_name'] }}</td>
    <td>{{ $cuentaxpagar['accountsPayable_observations'] }}</td>
    <td style="text-align: right">
        {{ $cuentaxpagar['accountsPayable_amount'] == null ? '$0.00' : '$' . number_format($cuentaxpagar['accountsPayable_amount'], 2) }}
    </td>
    <td style="text-align: right">
        {{ $cuentaxpagar['accountsPayable_taxes'] == null ? '$0.00' : '$' . number_format($cuentaxpagar['accountsPayable_taxes'], 2) }}
    </td>
    <td style="text-align: right">
        {{ $cuentaxpagar['accountsPayable_total'] == null ? '$0.00' : '$' . number_format($cuentaxpagar['accountsPayable_total'], 2) }}
    </td>
    <td>{{ $cuentaxpagar['accountsPayable_concept'] }}</td>
    <td>{{ $cuentaxpagar['accountsPayable_reference'] }}</td>
    @if ($cuentaxpagar['accountsPayable_status'] === 'FINALIZADO')
        <td style="text-align: right">
            {{ '$0.00' }}
        </td>
    @else
        <td style="text-align: right">
            {{ $cuentaxpagar['accountsPayable_balance'] == null ? '$0.00' : '$' . number_format($cuentaxpagar['accountsPayable_balance'], 2) }}
        </td>
    @endif
    </td>
    <td>{{ $cuentaxpagar['companies_nameShort'] }}</td>
    <td>{{ $cuentaxpagar['branchOffices_name'] }}</td>
    <td>{{ $cuentaxpagar['accountsPayable_user'] }}</td>
    <td>{{ $cuentaxpagar['accountsPayable_status'] }}</td>
    @if ($cuentaxpagar['accountsPayable_status'] === 'FINALIZADO')
        <td style="text-align: right">
            {{ '$0.00' }}
        </td>
    @else
        <td style="text-align: right">
            {{ $cuentaxpagar['accountsPayable_balance'] == null ? '$0.00' : '$' . number_format($cuentaxpagar['accountsPayable_balance'], 2) }}
        </td>
    @endif
</tr>
