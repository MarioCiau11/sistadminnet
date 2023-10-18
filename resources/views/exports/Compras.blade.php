<table>
    <thead>
        <tr>
            <th>Movimiento</th>
            <th>Folio</th>
            <th>Fecha de Expedición</th>
            <th>Concepto</th>
            <th>Moneda</th>
            <th>Tipo Cambio</th>
            <th>Clave Proveedor</th>
            <th>Nombre Proveedor</th>
            <th>Condición de Crédito</th>
            <th>Fecha de vencimiento</th>
            <th>Referencia</th>
            <th>Empresa</th>
            <th>Sucursal</th>
            <th>Almacén</th>
            <th>Motivo de Cancelación</th>
            <th>Usuario</th>
            <th>Estatus</th>
            <th>Importe</th>
            <th>Impuestos</th>
            <th>Total</th>
            {{-- <th>Ticket</th>
            <th>Operador</th>
            <th>Placas</th>
            <th>Material</th>
            <th>Peso de Entrada</th>
            <th>Fecha de Entrada</th>
            <th>Peso de Salida</th>
            <th>Fecha de Salida</th> --}}
            <th>Fecha de Creación</th>
            <th>Última Modificación</th>

        </tr>
    </thead>
    <tbody>
        @foreach ($compras as $compra)
            <tr>
                <td>{{ $compra['purchase_movement'] }}</td>
                <td>{{ $compra['purchase_movementID'] }}</td>
                <td>{{ \Carbon\Carbon::parse($compra['purchase_issueDate'])->format('d/m/Y') }}</td>
                <td>{{ $compra['purchase_concept'] }}</td>
                <td>{{ $compra['purchase_money'] }}</td>
                <td>{{ $compra['purchase_typeChange'] }}</td>
                <td>{{ $compra['purchase_provider'] }}</td>
                <td>{{ $compra['providers_name'] }}</td>
                <td>{{ $compra['creditConditions_name'] }}</td>
                <td>{{ \Carbon\Carbon::parse($compra['purchase_expiration'])->format('d/m/Y') }}</td>
                <td>{{ $compra['purchase_reference'] }}</td>
                <td>{{ $compra['companies_nameShort'] }}</td>
                <td>{{ $compra['branchOffices_name'] }}</td>
                <td>{{ $compra['depots_name'] }}</td>
                <td>{{ $compra['purchase_reasonCancellation'] }}</td>
                <td>{{ $compra['purchase_user'] }}</td>
                <td>{{ $compra['purchase_status'] }}</td>
                <td>{{ $compra['purchase_amount'] == null ? '$0.00' : '$' . number_format($compra['purchase_amount'], 2) }}
                </td>
                <td>{{ $compra['purchase_taxes'] == null ? '$0.00' : '$' . number_format($compra['purchase_taxes'], 2) }}
                </td>
                <?php
                $importe = $compra['purchase_amount'] !== null ? (float) $compra['purchase_amount'] : 0;
                $taxes = $compra['purchase_taxes'] !== null ? (float) $compra['purchase_taxes'] : 0;
                $total = $importe + $taxes;
                ?>
                <td>{{ $compra['purchase_total'] == null ? '$0.00' : '$' . number_format($total, 2) }}</td>
                {{-- <td>{{ $compra['purchase_ticket'] }}</td>
                <td>{{ $compra['purchase_operator'] }}</td>
                <td>{{ $compra['purchase_plates'] }}</td>
                <td>{{ $compra['purchase_material'] }}</td>
                <td>{{ $compra['purchase_inputWeight'] }}</td>
                <td>{{ $compra['purchase_inputDateTime'] }}</td>
                <td>{{ $compra['purchase_outputWeight'] }}</td>
                <td>{{ $compra['purchase_outputDateTime'] }}</td> --}}
                <td>{{ $compra['created_at'] }}</td>
                <td>{{ $compra['updated_at'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
