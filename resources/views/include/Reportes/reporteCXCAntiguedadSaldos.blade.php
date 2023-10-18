<tr>
    <td style="text-align: center">{{ $cxc['customers_businessName'] }}</td>
    <td style="text-align: center">{{ $cxc['customers_category'] }}</td>
    <td style="text-align: center">{{ $cxc['customers_group'] }}</td>
    <td style="text-align: center">{{ $cxc['accountsReceivableP_movement'] }}</td>
    <td style="text-align: center">{{ \Carbon\Carbon::parse($cxc['accountsReceivableP_issuedate'])->format('d/m/Y') }}
    </td>
    <td style="text-align: center">{{ $cxc['branchOffices_name'] }}</td>
    <td style="text-align: center">{{ \Carbon\Carbon::parse($cxc['accountsReceivableP_expiration'])->format('d/m/Y') }}
    </td>
    <td style="text-align: center">{{ $cxc['money_key'] }}</td>

</tr>
