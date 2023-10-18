<table class="ancho">
    <tr>
        <td colspan="7" style="text-align: center; font-weight:bold; padding:5px">
            <p><strong>ESTADO DE CUENTA</strong></p>
        </td>
    </tr>

</table>

<table class="informacion-proveedor2">
    @foreach ($proveedoresEstado as $proveedorCXP)
        <tr>
            {{-- <td>
                <p>{{ $proveedorCXP['providers_key'] . ' - ' . $proveedorCXP['providers_name'] }}</p>
            </td> --}}
        </tr>


        <table class="articulos-table4">
            <tr>
                <th style="text-align: center"><strong>PROVEEDOR</strong></th>
                <th style="text-align: center">
                    <strong>MOVIMIENTO</strong>
                </th>
                <th style="text-align: center">
                    <strong>FECHA</strong>
                </th>
                <th style="text-align: center">
                    <strong>CARGOS</strong>
                </th>
                <th style="text-align: center">
                    <strong>ABONOS</strong>
                </th>
                <th style="text-align: center">
                    <strong>SALDO</strong>
                </th>
                <th style="text-align: center">
                    <strong>MONEDA</strong>
                </th>
            </tr>

            <?php $saldo = 0; ?>
            @foreach ($proveedorCXP['cuentasxp'] as $movientos)
                <tr>
                    <td style="text-align: center">{{ $movientos['providers_name'] }}</td>
                    <td>{{ $movientos['assistant_movement'] }} {{ $movientos['assistant_movementID'] }}
                    </td>
                    <td>{{ \Carbon\Carbon::parse($movientos['created_at'])->format('d/m/Y') }}
                    </td>
                    <td>${{ number_format($movientos['assistant_charge'], 2) }}</td>
                    <td>${{ number_format($movientos['assistant_payment'], 2) }}</td>
                    @if ($movientos['assistant_charge'] !== null)
                        <?php $saldo += floatVal($movientos['assistant_charge']); ?>
                    @else
                        <?php $saldo -= floatVal($movientos['assistant_payment']); ?>
                    @endif
                    <td>${{ number_format($saldo, 2) }}</td>

                    <td>{{ $movientos['assistant_money'] }}</td>
                </tr>
            @endforeach
            <?php
            $totalCargos = 0;
            $totalAbonos = 0;
            $totalSaldo = 0;
            $totalRedondeo = 0;
            
            foreach ($proveedorCXP['cuentasxp'] as $movientos) {
                $totalCargos += $movientos['assistant_charge'];
                $totalAbonos += $movientos['assistant_payment'];
                $totalSaldo = $totalCargos - $totalAbonos;
                $totalRedondeo += 0;
            }
            ?>

            <tr>
                <th>
                    <p> </p>
                </th>
                <th>
                    <p> </p>
                </th>
                <th>
                    <p>TOTALES</p>
                </th>
                <th>
                    <p>${{ number_format($totalCargos, 2) }}</p>
                </th>
                <th>
                    <p>${{ number_format($totalAbonos, 2) }}</p>
                </th>
                <th>
                    <p>${{ number_format($totalSaldo, 2) }}</p>
                </th>
                <th>
                    <p>{{ $movientos['assistant_money'] }}</p>
                </th>
            </tr>
        </table>
        <br>
    @endforeach
</table>
