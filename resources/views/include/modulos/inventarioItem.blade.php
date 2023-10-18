<tr>
    <td class="td-option">
        <div>
            <a href="{{ route('vista.modulo.inventarios.create-inventario', ['id' => $inventario['inventories_id']]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i
                    class="fa fa-folder-open" aria-hidden="true" style="color:black"></i></a>

        </div>
    </td>
    <td>
        {{-- Creamos un objeto que nos devolvera el icono correspondiente a cada estado --}}
        @switch($inventario['inventories_status'])
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

        {{ $inventario['inventories_movement'] }}
    </td>
    <td>{{ $inventario['inventories_movementID'] }}</td>
    <td>{{ \Carbon\Carbon::parse($inventario['inventories_issuedate'])->format('d/m/Y') }}</td>

    <td>{{ $inventario['inventories_concept'] }}</td>
    <td>{{ $inventario['inventories_money'] }}</td>
    <td>{{ $inventario['inventories_typeChange'] }}</td>
    <td>{{ $inventario['inventories_reference'] }}</td>
    <td>{{ $inventario['branchOffices_name'] }}</td>
    <td>{{ $inventario['depots_name'] }}</td>
    <td>{{ $inventario['depots_nameDestiny'] }}
    </td>

    <td>{{ $inventario['inventories_user'] }}</td>
    <td>{{ $inventario['inventories_status'] }}</td>
    <td style="text-align: right">
        {{ $inventario['inventories_total'] == null ? '$0.00' : '$' . number_format($inventario['inventories_total'], 2) }}
    </td>
</tr>
