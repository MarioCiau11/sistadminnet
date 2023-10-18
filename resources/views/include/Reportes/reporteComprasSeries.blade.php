<tr>
    <td>{{ $compra['providers_key'] }}</td>
    <td>{{ $compra['providers_name'] }}</td>
    <td>{{ $compra['purchaseDetails_descript'] }}</td>
    <td>{{ \Carbon\Carbon::parse($compra['purchase_issueDate'])->format('d/m/Y') }}</td>
    {{-- <td>{{ $compra['purchase_lastChange'] }}</td> --}}
    <td>{{ $compra['branchOffices_name'] }}</td>
    <td>{{ $compra['purchase_money'] }}</td>
    <td>{{ $compra['lotSeriesMov_lotSerie'] }}</td>
</tr>