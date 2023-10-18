<table>
    <thead>
        <tr>
            <th style="width: 130px; text-align: center;">Movimiento</th>
            <th style="width: 130px; text-align: center;">Folio</th>
            <th style="width: 130px; text-align: center;">Fecha de Emisión</th>
            <th style="width: 130px; text-align: center;">Concepto</th>
            <th style="width: 130px; text-align: center;">Moneda</th>
            <th style="width: 130px; text-align: center;">Cuenta Dinero</th>
            <th style="width: 130px; text-align: center;">Sucursal</th>
            <th style="width: 130px; text-align: center;">Usuario</th>
            <th style="width: 130px; text-align: center;">Estatus</th>
            <th style="width: 130px; text-align: center;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($tesoreria as $din)
            <tr>
                <td style="text-align: center">{{ $din['treasuries_movement'] }}</td>
                <td style="text-align: center">{{ $din['treasuries_movementID'] }}</td>
                <td style="text-align: center">
                    {{ \Carbon\Carbon::parse($din['treasuries_issuedate'])->format('d/m/Y') }}</td>
                <td style="text-align: center">{{ $din['treasuries_concept'] }}</td>
                <td style="text-align: center">{{ $din['treasuries_money'] }}</td>
                <td style="text-align: center">
                    {{ $din['treasuries_moneyAccount'] === null ? $din['treasuries_moneyAccountOrigin'] : $din['treasuries_moneyAccount'] }}
                </td>
                <td style="text-align: center">{{ $din['treasuries_branchOffice'] }}</td>
                <td style="text-align: center">{{ $din['treasuries_user'] }}</td>
                <td style="text-align: center">{{ $din['treasuries_status'] }}</td>
                <td style="text-align: center">${{ number_format($din['treasuries_total'], 2) }}</td>
            </tr>
        @endforeach
</table>
