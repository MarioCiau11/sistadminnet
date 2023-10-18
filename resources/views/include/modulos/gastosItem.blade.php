<tr>
    <td class="td-option">
        <div>
            <a href="{{ route('vista.modulo.gastos.create-gasto', ['id' => $gasto['expenses_id']]) }}" class="show"
                data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-folder-open"
                    aria-hidden="true" style="color:black"></i></a>

        </div>
    </td>
    <td>
        {{-- Creamos un objeto que nos devolvera el icono correspondiente a cada estado --}}
        @switch($gasto['expenses_status'])
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

        {{ $gasto['expenses_movement'] }}
    </td>
    <td>{{ $gasto['expenses_movementID'] }}</td>
    <td>{{ \Carbon\Carbon::parse($gasto['expenses_issueDate'])->format('d/m/Y') }}</td>
    <td>{{ $gasto['expenses_lastChange'] }}</td>
    <td>{{ $gasto['expenses_money'] }}</td>
    <td>{{ $gasto['expenses_typeChange'] == null ? '$0.00' : '$' . number_format($gasto['expenses_typeChange'], 2) }}
    </td>
    <td>{{ $gasto['expenses_provider'] }}</td>
    <td>{{ $gasto['providers_name'] }}</td>
    <td>{{ $gasto['expenses_observations'] }}</td>
    <td>{{ $gasto['expenses_moneyAccount'] }}</td>
    <td>{{ $gasto['formsPayment_name'] }}</td>
    <td>{{ $gasto['creditConditions_name'] }}</td>
    <td>{{ \Carbon\Carbon::parse($gasto['expenses_expiration'])->format('d/m/Y') }}</td>
    <td style="text-align: right">
        {{ $gasto['expenses_total'] == null ? '$0.00' : '$' . number_format($gasto['expenses_total'], 2) }}</td>
    <td>{{ $gasto['expenses_antecedentsName'] }}</td>
    <td>{{ $gasto['expenses_fixedAssetsName'] }}</td>
    <td>{{ $gasto['companies_nameShort'] }}</td>
    <td>{{ $gasto['branchOffices_name'] }}</td>
    <td>{{ $gasto['expenses_user'] }}</td>
    <td>{{ $gasto['expenses_status'] }}</td>


    {{-- <td>{{ $gasto['purchase_lines'] }}</td>
    <td>{{ $gasto['purchase_originType'] }}</td>
    <td>{{ $gasto['purchase_origin'] }}</td>
    <td>{{ $gasto['purchase_originID'] }}</td>
    <td>{{ $gasto['purchase_ticket'] }}</td>
    <td>{{ $gasto['purchase_operator'] }}</td>
    <td>{{ $gasto['purchase_plates'] }}</td>
    <td>{{ $gasto['purchase_material'] }}</td>
    <td>{{ $gasto['purchase_inputWeight'] }}</td>
    <td>{{ $gasto['purchase_inputDateTime'] }}</td>
    <td>{{ $gasto['purchase_outputWeight'] }}</td>
    <td>{{ $gasto['purchase_outputDateTime'] }}</td> --}}
</tr>
