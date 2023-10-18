<tr>
    <td>{{ $compra['customers_businessName'] }}</td>
    <td>{{ $compra['customers_category'] }}</td>
    <td>{{ $compra['customers_group'] }}</td>
    <td>
        {{-- Creamos un objeto que nos devolvera el icono correspondiente a cada estado --}}
        @switch($compra['sales_status'])
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

        {{ $compra['sales_movement'] }}
    </td>
    <td>{{ $compra['articles_descript'] }}</td>
    <td>{{ $compra['articles_category'] }}</td>
    <td>{{ $compra['articles_group'] }}</td>
    <td>{{ $compra['articles_family'] }}</td>
    <td>{{ \Carbon\Carbon::parse($compra['sales_issuedate'])->format('d/m/Y') }}</td>
    {{-- <td>{{ $compra['purchase_lastChange'] }}</td> --}}
    <td>{{ $compra['assistantUnit_year'] }}</td>
    <td>{{ $compra['sales_money'] }}</td>

</tr>
