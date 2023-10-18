<tr>
    {{-- <?php  dd($movimiento); ?> --}}
    @if(isset($movimiento['sales_issuedate']))
        <td>{{ $movimiento['sales_issuedate'] }}</td>
    @else
        <td>{{ $movimiento['accountsReceivable_issuedate'] }}</td>
    @endif
    {{-- <td></td> --}}
    <td>{{ $movimiento['branchOffices_name'] }}</td>
    @if(isset($movimiento['sales_money']))
    <td>{{ $movimiento['sales_money'] }}</td>
@else
    <td>{{ $movimiento['accountsReceivable_money'] }}</td>
@endif
</tr>