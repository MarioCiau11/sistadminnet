<tr>
    @if ($venta['sales_listPrice'] == 'articles_listPrice1')
        <td>Precio 1</td>
    @elseif($venta['sales_listPrice'] == 'articles_listPrice2')
        <td>Precio 2</td>
    @elseif($venta['sales_listPrice'] == 'articles_listPrice3')
        <td>Precio 3</td>
    @elseif($venta['sales_listPrice'] == 'articles_listPrice4')
        <td>Precio 4</td>
    @elseif($venta['sales_listPrice'] == 'articles_listPrice5')
        <td>Precio 5</td>
        @endif
        <td>{{ $venta['salesDetails_descript'] }}</td>
        <td>{{ $venta['articles_category'] }}</td>
        <td>{{ $venta['articles_group'] }}</td>
        <td>{{ $venta['articles_family'] }}</td>
    <td>{{ \Carbon\Carbon::parse($venta['sales_issuedate'])->format('d/m/Y') }}</td>
    {{-- <td>{{ $compra['purchase_lastChange'] }}</td> --}}
    <td>{{ $venta['branchOffices_name'] }}</td>

</tr>