<tr>
    <td style="text-align: left">{{ $cxp['providers_name'] }}</td>
    <td style="text-align: center">{{ $cxp['providers_category'] }}</td>
    <td style="text-align: center">{{ $cxp['providers_group'] }}</td>
    <td style="text-align: center">{{ $cxp['accountsPayableP_movement'] }}</td>
    <td style="text-align: center">{{ \Carbon\Carbon::parse($cxp['accountsPayableP_issuedate'])->format('d/m/Y') }}</td>
    <td style="text-align: center">{{ $cxp['branchOffices_name'] }}</td>
    <td style="text-align: center">{{ \Carbon\Carbon::parse($cxp['accountsPayableP_expiration'])->format('d/m/Y') }}</td>
    <td style="text-align: center">{{ $cxp['money_key'] }}</td>

</tr>
