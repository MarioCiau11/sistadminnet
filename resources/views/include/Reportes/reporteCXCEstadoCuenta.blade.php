<tr>
    <td style="text-align: center">{{ $cxc['customers_businessName'] }}</td>
    <td style="text-align: center">{{ $cxc['customers_category'] }}</td>
    <td style="text-align: center">{{ $cxc['customers_group'] }}</td>
    <td style="text-align: center">{{ $cxc['assistant_movement'] }}</td>
    <td style="text-align: center">{{ \Carbon\Carbon::parse($cxc['created_at'])->format('d/m/Y') }}
    </td>
    <td style="text-align: center">{{ $cxc['branchOffices_name'] }}</td>
    </td>
    <td style="text-align: center">{{ $cxc['assistant_money'] }}</td>

</tr>
