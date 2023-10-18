<tr>
    <td>{{ $compra['providers_name'] }}</td>
    <td>{{ $compra['providers_category'] }}</td>
    <td>{{ $compra['providers_group'] }}</td>
    <td>{{ $compra['purchase_movement'] }}</td>
    <td>{{ $compra['articles_descript'] }}</td>
    <td>{{ $compra['purchaseDetails_unit'] }}</td>
    <td>{{ \Carbon\Carbon::parse($compra['purchase_issueDate'])->format('d/m/Y') }}</td>
    <td>{{$compra['depots_name']}}</td>
    <td>{{ $compra['branchOffices_name'] }}</td>
    <td>{{ $compra['purchase_money'] }}</td>
    <td>{{ $compra['purchase_status'] }}</td>
    {{-- <td>{{ $compra['purchase_lastChange'] }}</td> --}}

</tr>