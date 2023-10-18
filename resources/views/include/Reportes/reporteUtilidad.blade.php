<tr>
    <td style="text-align: center">{{ $utilidad['sales_movement'] }}</td>
    <td style="text-align: center">
        {{ $utilidad['customers_businessName'] }}
    </td>
    <td style="text-align: center">{{ \Carbon\Carbon::parse($utilidad['sales_issuedate'])->format('d/m/Y') }}</td>

</tr>
