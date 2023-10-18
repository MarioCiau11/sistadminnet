<tr>
    <td>{{ $compra['providers_name'] }}</td>
    <td>{{ $compra['providers_category'] }}</td>
    <td>{{ $compra['providers_group'] }}</td>
    <td>{{ $compra['articles_descript'] }}</td>
    <td>{{ $compra['articles_category'] }}</td>
    <td>{{ $compra['articles_group'] }}</td>
    <td>{{ $compra['articles_family'] }}</td>
    <td>{{ \Carbon\Carbon::parse($compra['purchase_issueDate'])->format('d/m/Y') }}</td>
    <td>{{ $compra['purchase_money'] }}</td>
    <td>{{ $compra['assistantUnit_year'] }}</td>

    {{-- <td>{{ $compra['purchase_lastChange'] }}</td> --}}

</tr>