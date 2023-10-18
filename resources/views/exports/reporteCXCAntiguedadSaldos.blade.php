<table class="ancho">
    <tr>
        <td colspan="13" style="text-align: center; font-weight:bold; padding:5px">
            <p><strong>ANTIGUEDAD DE SALDOS</strong></p>
        </td>
    </tr>

</table>

<table class="informacion-proveedor2">
    @foreach ($cuentasxcobrar as $cxc)
        <tr>

        </tr>
        <thead class="articulos-table4">
            <tr>
                <td style="text-align: center"><strong>CLIENTE</strong></td>
                <td style="text-align: center"><strong>MOVIMIENTO</strong></td>
                <td style="text-align: center"><strong>MONEDA</strong></td>
                <td style="text-align: center"><strong>REFERENCIA</strong></td>
                <td style="text-align: center"><strong>EMISIÓN</strong></td>
                <td style="text-align: center"><strong>VENCIMIENTO</strong></td>
                <td style="text-align: center"><strong>DÍAS</strong></td>
                <td style="text-align: center"><strong>AL CORRIENTE</strong></td>
                <td style="text-align: center"><strong>DE 1 AL 15</strong></td>
                <td style="text-align: center"><strong>DE 16 A 30</strong></td>
                <td style="text-align: center"><strong>DE 31 A 60</strong></td>
                <td style="text-align: center"><strong>DE 61 A 90</strong></td>
                <td style="text-align: center"><strong>MÁS DE 90 DÍAS</strong></td>
            </tr>
        </thead>
        <tbody>

            {{-- <tr> --}}
            {{-- <?php dd($cxp['cuentasxp']); ?> --}}
            @foreach ($cxc['cuentasxc'] as $cuenta)
                <tr>

                    <td style="text-align: center">{{ $cuenta['customers_businessName'] }}</td>
                    <td style="text-align: center">{{ $cuenta['accountsReceivableP_movement'] }}</td>
                    <td style="text-align: center">{{ $cuenta['accountsReceivableP_money'] }}</td>
                    <td style="text-align: center">{{ $cuenta['accountsReceivableP_reference'] }}</td>
                    <td style="text-align: center">
                        {{ \Carbon\Carbon::parse($cuenta['accountsReceivableP_issuedate'])->format('d/m/Y') }}</td>
                    <td style="text-align: center">
                        {{ \Carbon\Carbon::parse($cuenta['accountsReceivableP_expiration'])->format('d/m/Y') }}</td>

                    @if ($cuenta['accountsReceivableP_moratoriumDays'] === 0)
                        <td style="text-align: center">0</td>
                    @else
                        <td style="text-align: center">{{ $cuenta['accountsReceivableP_moratoriumDays'] }}</td>
                    @endif
                    @if ($cuenta['accountsReceivableP_moratoriumDays'] <= 0)
                        <td>$ {{ number_format($cuenta['accountsReceivableP_balanceTotal'], 2) }}</td>
                    @else
                        <td></td>
                    @endif
                    @if ($cuenta['accountsReceivableP_moratoriumDays'] >= 0 && $cuenta['accountsReceivableP_moratoriumDays'] <= 15)
                        <td>$ {{ number_format($cuenta['accountsReceivableP_balanceTotal'], 2) }}</td>
                    @else
                        <td></td>
                    @endif
                    @if ($cuenta['accountsReceivableP_moratoriumDays'] >= 16 && $cuenta['accountsReceivableP_moratoriumDays'] <= 30)
                        <td>$ {{ number_format($cuenta['accountsReceivableP_balanceTotal'], 2) }}</td>
                    @else
                        <td></td>
                    @endif
                    @if ($cuenta['accountsReceivableP_moratoriumDays'] >= 31 && $cuenta['accountsReceivableP_moratoriumDays'] <= 60)
                        <td>$ {{ number_format($cuenta['accountsReceivableP_balanceTotal'], 2) }}</td>
                    @else
                        <td></td>
                    @endif
                    @if ($cuenta['accountsReceivableP_moratoriumDays'] >= 61 && $cuenta['accountsReceivableP_moratoriumDays'] <= 90)
                        <td>$ {{ number_format($cuenta['accountsReceivableP_balanceTotal'], 2) }}</td>
                    @else
                        <td></td>
                    @endif
                    @if ($cuenta['accountsReceivableP_moratoriumDays'] >= 91)
                        <td>$ {{ number_format($cuenta['accountsReceivableP_balanceTotal'], 2) }}</td>
                    @else
                        <td></td>
                    @endif
                </tr>
            @endforeach
            {{-- </tr> --}}
    @endforeach
        <?php
        $totalGeneral = 0;
        if ($cuentasxcobrar != null) {
            foreach ($cuentasxcobrar as $clienteCXC) {
                foreach ($clienteCXC['cuentasxc'] as $movientos) {
                    $totalGeneral += $movientos['accountsReceivableP_balanceTotal'];
                }
            }
        }
        // dd($totalGeneral);
        ?>
            <tr>
                <td>
                    <p><strong>TOTAL GENERAL: </strong> {{ number_format($totalGeneral, 2) }}</p>
                </td>
            </tr>
    </tbody>
</table>
