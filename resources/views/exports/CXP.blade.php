<table>
    <thead>
        <tr>
            <th>Movimiento</th>
            <th>Folio</th>
            <th>Fecha de Emisión</th>
            <th>Moneda</th>
            <th>Tipo de Cambio</th>
            <th>Cuenta Dinero</th>
            <th>Proveedor/Acreedor</th>
            <th>Nombre Proveedor</th>
            <th>Condición de Crédito</th>
            <th>Fecha de vencimiento</th>
            <th>Forma de Pago</th>
            <th>Observaciones</th>
            <th>Importe</th>
            <th>Impuestos</th>
            <th>Total</th>
            <th>Concepto</th>
            <th>Referencias</th>
            <th>Balance</th>
            <th>Empresa</th>
            <th>Sucursal</th>
            <th>Usuario</th>
            <th>Estatus</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cuentasxpagar as $cuentaxpagar)
            <tr>
                <td>{{ $cuentaxpagar['accountsPayable_movement'] }}</td>
                <td>{{ $cuentaxpagar['accountsPayable_movementID'] }}</td>
                <td>{{ $cuentaxpagar['accountsPayable_issuedate'] }}</td>
                <td>{{ $cuentaxpagar['accountsPayable_money'] }}</td>
                <td>{{ $cuentaxpagar['accountsPayable_typeChange'] }}</td>
                <td>{{ $cuentaxpagar['accountsPayable_moneyAccount'] }}</td>
                <td>{{ $cuentaxpagar['accountsPayable_provider'] }}</td>
                <td>{{ $cuentaxpagar['providers_name'] }}</td>
                <td>{{ $cuentaxpagar['creditConditions_name'] }}</td>
                <td>{{ \Carbon\Carbon::parse($cuentaxpagar['accountsPayable_expiration'])->format('d-m-Y') }}</td>
                <td>{{ $cuentaxpagar['formsPayment_name'] }}</td>
                <td>{{ $cuentaxpagar['accountsPayable_observations'] }}</td>
                <td>{{ $cuentaxpagar['accountsPayable_amount'] == null ? '$0.00' : '$' . number_format($cuentaxpagar['accountsPayable_amount'], 2) }}</td>
                <td>{{ $cuentaxpagar['accountsPayable_taxes'] == null ? '$0.00' : '$' . number_format($cuentaxpagar['accountsPayable_taxes'], 2) }}</td>
                <td>{{ $cuentaxpagar['accountsPayable_total'] == null ? '$0.00' : '$' . number_format($cuentaxpagar['accountsPayable_total'], 2) }}</td>
                <td>{{ $cuentaxpagar['accountsPayable_concept'] }}</td>
                <td>{{ $cuentaxpagar['accountsPayable_reference'] }}</td>
                <td>{{ $cuentaxpagar['accountsPayable_balance'] == null ? '$0.00' : '$' . number_format($cuentaxpagar['accountsPayable_balance'], 2) }}</td>
                <td>{{ $cuentaxpagar['companies_nameShort'] }}</td>
                <td>{{ $cuentaxpagar['branchOffices_name'] }}</td>
                <td>{{ $cuentaxpagar['accountsPayable_user'] }}</td>
                <td>{{ $cuentaxpagar['accountsPayable_status'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
