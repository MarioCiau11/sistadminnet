<tr>
    <td style="text-align: center">
        {{ $din['assistant_account']}}
    </td>
    <td style="text-align: center">{{ $din['assistant_movement']. ' ' . $din['assistant_movementID'] }}</td>
    <td style="text-align: center">{{ \Carbon\Carbon::parse($din['created_at'])->format('d/m/Y') }}</td>
    <td style="text-align: center">{{ $din['assistant_money'] }}</td>

</tr>
