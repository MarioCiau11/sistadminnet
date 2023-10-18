<table>
    <thead>
        <tr>
            <th>Movimiento</th>
            <th>Folio</th>
            <th>Fecha de Emisión</th>
            <th>Fecha de Vencimiento</th>
            <th>Moneda</th>
            <th>Tipo de Cambio</th>
            <th>Cuenta Dinero</th>
            <th>Cliente</th>
            <th>Nombre Cliente</th>
            <th>Forma de Pago</th>
            <th>Observaciones</th>
            <th>Importe antes de Impuestos</th>
            <th>Impuestos</th>
            <th>Rentenciones</th>
            <th>Importe Total</th>
            <th>Concepto</th>
            <th>Condición de Pago</th>
            <th>Referencia</th>
            <th>Sucursal</th>
            <th>Usuario</th>
            <th>Estatus</th>
            <th>Timbrado</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cuentasxcobrar as $cxc)
        <tr>
            <td>{{ $cxc['accountsReceivable_movement'] }} </td>
            <td>{{ $cxc['accountsReceivable_movementID'] }}</td>
            <td>{{ \Carbon\Carbon::parse($cxc['accountsReceivable_issuedate'])->format('d/m/Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($cxc['accountsReceivable_expiration'])->format('d/m/Y') }}</td>
            <td>{{ $cxc['accountsReceivable_money'] }}</td>
            <td>{{ $cxc['accountsReceivable_typeChange'] }}</td>
            <td>{{ $cxc['accountsReceivable_moneyAccount'] }}</td>
            <td>{{ $cxc['accountsReceivable_customer'] }}</td>
            <td>{{ $cxc['customers_businessName'] }}</td>
            <td>{{ $cxc['accountsReceivable_formPayment'] }}</td>
            <?php
            $importe = $cxc['accountsReceivable_amount'] !== null ? (float) $cxc['accountsReceivable_amount'] : 0;
            $taxes = $cxc['accountsReceivable_taxes'] !== null ? (float) $cxc['accountsReceivable_taxes'] : 0;
            $retencionISR = $cxc['accountsReceivable_retentionISR'] !== null || $cxc['accountsReceivable_retentionISR'] != 0.0 ? (float) $cxc['accountsReceivable_retentionISR'] : 0;
            $retencionIVA = $cxc['accountsReceivable_retentionIVA'] !== null || $cxc['accountsReceivable_retentionIVA'] != 0.0 ? (float) $cxc['accountsReceivable_retentionIVA'] : 0;
            $total = $importe + $taxes - ($retencionISR + $retencionIVA);
            ?>
            <td>{{ $cxc['accountsReceivable_observations'] }}</td>
            <td style="text-align: right">
                {{ $cxc['accountsReceivable_amount'] == null ? '$0.00' : '$' . number_format($importe, 2) }}
            </td>
        
            {{-- <td>{{ $cxc['companies_nameShort'] }}</td> --}}
            <td style="text-align: right">
                {{ $cxc['accountsReceivable_taxes'] == null ? '$0.00' : '$' . number_format($cxc['accountsReceivable_taxes'], 2) }}
            </td>
            <td style="text-align: right">{{ $cxc['accountsReceivable_movement'] == 'Factura' ? '$'.number_format($retencionISR + $retencionIVA, 2) : '$0.00'}}</td>
            <td style="text-align: right">
            {{  $cxc['accountsReceivable_movement'] != 'Factura' ? '$'.number_format($cxc['accountsReceivable_total'], 2) : '$' . number_format($total, 2) }}</td>
            <td>{{ $cxc['accountsReceivable_concept'] }}</td>
            <td>{{ $cxc['creditConditions_name'] }}</td>
            <td>{{ $cxc['accountsReceivable_reference'] }}</td>
            <td>{{ $cxc['branchOffices_name'] }}</td>
            <td>{{ $cxc['accountsReceivable_user'] }}</td>
            <td>{{ $cxc['accountsReceivable_status'] }}</td>
            <td>{{ $cxc['accountsReceivable_status'] }}</td>
        </tr>
            
        @endforeach
    </tbody>
</table>