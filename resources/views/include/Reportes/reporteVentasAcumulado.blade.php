<tr>
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
    <td>{{ $venta['salesDetails_descript'] }}</td>
    <td>{{ $venta['salesDetails_unit'] }}</td>
    <td>{{ $venta['sales_user'] }}</td>
    <td>{{ $venta['sales_status'] }}</td>
    <td>{{ \Carbon\Carbon::parse($venta['sales_issuedate'])->format('d/m/Y') }}</td>
    <td>{{ $venta['depots_name'] }}</td>
    <td>{{ $venta['branchOffices_name'] }}</td>
    <td>{{ $venta['sales_money'] }}</td>
</tr>
