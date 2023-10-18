<table>
    <thead>
        <tr>
            <td>Movimiento</td>
            <td>Folio</td>
            <td>Fecha Expedición</td>
            <td>Último Cambio</td>
            <td>Moneda</td>
            <td>Tipo de Cambio</td>
            <td>Clave Proveedor</td>
            <td>Proveedor</td>
            <td>Observaciones</td>
            <td>Cuenta Dinero</td>
            <td>Forma de Pago</td>
            <td>Condición de Crédito</td>
            <td>Fecha de vencimiento</td>
            <td>Total</td>
            <td>Antecedentes</td>
            <td>Activo Fijo</td>
            <td>Empresa</td>
            <td>Sucursal</td>
            <td>Usuario</td>
            <td>Estatus</td>

        </tr>
    </thead>
    <tbody>
        @foreach ($gastos as $gasto)
        <tr>
            <td> {{ $gasto['expenses_movement'] }}</td>
            <td>{{ $gasto['expenses_movementID'] }}</td>
            <td>{{ \Carbon\Carbon::parse($gasto['expenses_issueDate'])->format('d-m-Y') }}</td>
            <td>{{ $gasto['expenses_lastChange'] }}</td>
            <td>{{ $gasto['expenses_money'] }}</td>
            <td>{{ $gasto['expenses_typeChange'] }}</td>
            <td>{{ $gasto['expenses_provider'] }}</td>
            <td>{{ $gasto['providers_name'] }}</td>
            <td>{{ $gasto['expenses_observations'] }}</td>
            <td>{{ $gasto['expenses_moneyAccount'] }}</td>
            <td>{{ $gasto['formsPayment_name'] }}</td>
            <td>{{ $gasto['creditConditions_name'] }}</td>
            <td>{{ \Carbon\Carbon::parse($gasto['expenses_expiration'])->format('d-m-Y') }}</td>
            <td>{{ $gasto['expenses_total'] == null ? '$0.00' : '$' . number_format($gasto['expenses_total'], 2) }}</td>
            <td>{{ $gasto['expenses_antecedents'] }}</td>
            <td>{{ $gasto['expenses_fixedAssets'] }}</td>
            <td>{{ $gasto['companies_nameShort'] }}</td>
            <td>{{ $gasto['branchOffices_name'] }}</td>
            <td>{{ $gasto['expenses_user'] }}</td>
            <td>{{ $gasto['expenses_status'] }}</td>
        </tr>
            
        @endforeach
    </tbody>
</table>