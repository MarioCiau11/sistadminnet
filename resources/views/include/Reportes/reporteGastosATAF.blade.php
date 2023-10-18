<tr>
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
    <td>{{ $gasto['providers_name'] }}</td>
    <td>{{ $gasto['providers_category'] }}</td>
    <td>{{ $gasto['providers_group'] }}</td>
    <td>{{ $gasto['expensesDetails_concept'] }}</td>
    <td>{{ $gasto['branchOffices_name'] }}</td>
    <td>{{ \Carbon\Carbon::parse($gasto['expenses_issueDate'])->format('d/m/Y') }}</td>
    <td> {{ $gasto['expenses_antecedentsName'] || $gasto['expenses_fixedAssetsName'] . '-' . $gasto['expenses_fixedAssetsSerie'] == '' ? 'Factura' . ' - ' . $gasto['expenses_antecedentsName'] : $gasto['expenses_fixedAssetsName'] . '-' . $gasto['expenses_fixedAssetsSerie'] }}
    </td>
    {{-- <td>{{ isset($gasto['expensesDetails_antecedentsName']) ? $gasto['expensesDetails_fixedAssetsName'] : '' }}</td> --}}

    {{-- <td>{{ $gasto['purchase_lastChange'] }}</td> --}}
    <td>{{ $gasto['expenses_money'] }}</td>
    <td>{{ $gasto['expenses_status'] }}</td>

    {{-- || $gasto['expenses_fixedAssetsName']}} </td> --}}

</tr>
