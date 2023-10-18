<tr>
    <td>{{ $venta['customers_key'] }}</td>
    <td>{{ $venta['customers_name'] }}</td>
    <td>{{ $venta['salesDetails_descript'] }}</td>
    <td>{{ \Carbon\Carbon::parse($venta['sales_issuedate'])->format('d/m/Y') }}</td>
    {{-- <td>{{ $venta['purchase_lastChange'] }}</td> --}}
    <td>{{ $venta['branchOffices_name'] }}</td>
    <td>{{ $venta['sales_money'] }}</td>
    <td>{{ $venta['delSeriesMov2_lotSerie'] }}</td>
</tr>