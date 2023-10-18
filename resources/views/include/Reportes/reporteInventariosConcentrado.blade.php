<tr>
<td>
        {{ $inv['articles_descript']}}
    </td>
    <td>{{ \Carbon\Carbon::parse($inv['created_at'])->format('d/m/Y') }}</td>
    <td>{{ $inv['articles_category'] }}</td>
    <td>{{ $inv['articles_group'] }}</td>
    <td>{{ $inv['articles_family'] }}</td>
    <td>{{ $inv['depots_name'] }}</td>

</tr>
