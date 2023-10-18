<tr>
    <td>{{ $venta['branchOffices_name'] }}</td>
    <td>{{ \Carbon\Carbon::parse($venta['sales_issuedate'])->format('d/m/Y') }}</td>
        <td>{{ $venta['salesDetails_descript'] }}</td>
        <td>{{ $venta['articles_category'] }}</td>
        <td>{{ $venta['articles_group'] }}</td>
        <td>{{ $venta['articles_family'] }}</td>
    {{-- <td>{{ $compra['purchase_lastChange'] }}</td> --}}

</tr>