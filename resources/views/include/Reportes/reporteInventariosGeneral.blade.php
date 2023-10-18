<tr>
    <td style="text-align: center">{{ $inv['assistantUnit_movement'] }}</td>
    <td style="text-align: center">
        {{ $inv['articles_descript']}}
    </td>
    <td style="text-align: center">{{ \Carbon\Carbon::parse($inv['created_at'])->format('d/m/Y') }}</td>
    <td style="text-align: center">{{ $inv['articles_category'] }}</td>
    <td style="text-align: center">{{ $inv['articles_family'] }}</td>
    <td style="text-align: center">{{ $inv['articles_group'] }}</td>
    <td style="text-align: center">{{ $inv['depots_name'] }}</td>

</tr>
