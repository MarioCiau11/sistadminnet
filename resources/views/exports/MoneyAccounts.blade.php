<table>
    <thead>
        <tr>
            <th>Clave</th>
            <th>Banco</th>
            <th>No. de cuenta</th>
            <th>Cuenta</th>
            <th>Referencia Banco</th>
            <th>Convenio Banco</th>
            <th>Tipo de cuenta</th>
            <th>Moneda</th>
            <th>Empresa</th>
            <th>Estatus</th>
            <th>Fecha de creación</th>
            <th>Fecha de actualización</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($moneyAccounts as $moneyAccount)
            <tr>
                <td>{{ $moneyAccount->moneyAccounts_key }}</td>
                <td>{{ $moneyAccount->instFinancial_name }}</td>
                <td>{{ $moneyAccount->moneyAccounts_numberAccount }}</td>
                {{-- <td>{{$moneyAccount->moneyAccounts_keyAccount }}</td>
                 --}}
                 {{-- Comprobamos que si es número el campo, lo muestre con formato de cuenta bancaria. Si es texto, lo muestre como está --}}
                <td>{{ is_numeric($moneyAccount->moneyAccounts_keyAccount) ? formatAccountNumber($moneyAccount->moneyAccounts_keyAccount) : $moneyAccount->moneyAccounts_keyAccount }}</td>
                <td>{{ $moneyAccount->moneyAccounts_referenceBank }}</td>
                <td>{{ $moneyAccount->moneyAccounts_bankAgreement }}</td>
                <td>{{ $moneyAccount->moneyAccounts_accountType }}</td>
                <td>{{ $moneyAccount->money_key }}</td>
                <td>{{ $moneyAccount->companies_name }}</td>
                <td>{{ $moneyAccount->moneyAccounts_status }}</td>
                <td>{{ $moneyAccount->created_at }}</td>
                <td>{{ $moneyAccount->updated_at }}</td>

            </tr>
        @endforeach
    </tbody>
</table>

<?php
function formatAccountNumber($accountNumber)
{
    $accountNumber = str_replace(' ', '', $accountNumber);
    $accountNumber = str_split($accountNumber, 4);
    $accountNumber = implode(' ', $accountNumber);
    return $accountNumber;
}
?>

