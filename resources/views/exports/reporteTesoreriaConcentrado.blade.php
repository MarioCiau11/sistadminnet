<table class="ancho">
    <tr>
        <td colspan="7" style="text-align: center; font-weight:bold; padding:5px">
            <p><strong>AUXILIARES POR CUENTA DE DINERO NIVEL - CONCENTRADO</strong></p>
        </td>
    </tr>

</table>

<table>
    <thead>
        <tr>
            <td>CUENTA</td>
            <td>DESCRIPCIÓN</td>
            <td style="width: 250px">NÚMERO CUENTA</td>
            <td>SALDO INICIAL</td>
            <td>CARGOS</td>
            <td>ABONOS</td>
            <td>SALDO FINAL</td>
        </tr>
    </thead>
    <tbody>
        @foreach ($tesorerias as $tesoreria)
            <tr>
                <td>
                    {{ $tesoreria->moneyAccountsBalance_moneyAccount }}
                </td>
                <td>{{ $tesoreria->moneyAccounts_referenceBank }}</td>
                <td style="text-align: center">{{ $tesoreria->moneyAccounts_numberAccount }}</td>
                <td>${{ number_format($tesoreria->moneyAccountsBalance_initialBalance, 2) }}</td>
                <td>${{ number_format($tesoreria->assistant_charge, 2) }}</td>
                <td>${{ number_format($tesoreria->assistant_payment, 2) }}</td>
                <td>${{ number_format($tesoreria->moneyAccountsBalance_balance, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
