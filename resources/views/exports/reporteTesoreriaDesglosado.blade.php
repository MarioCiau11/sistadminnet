<table class="ancho">
    <tr>
        <td colspan="7" style="text-align: center; font-weight:bold; padding:5px">
            <p><strong>AUXILIARES POR CUENTA DE DINERO NIVEL - DESGLOSADO</strong></p>
        </td>
    </tr>

</table>
<table>

    <table>
        <tr>
            <th style="border: solid:black">
                <p>CUENTA</p>
            </th>
            <th style="border: solid:black; width: 100px">
                <p>MOVIMIENTO</p>
            </th>
            <th style="border: solid:black">
                <p>REFERENCIA</p>
            </th>
            <th style="border: solid:black">
                <p>BENEFICIARIO</p>
            </th>
            <th style="border: solid:black">
                <p>CARGOS</p>
            </th>
            <th style="border: solid:black">
                <p>ABONOS</p>
            </th>
            <th style="border: solid:black;">
                <p>SALDO</p>
            </th>
        </tr>


        <?php
        //Guardamos en un historial los saldos de las cuentas por cuenta y fecha
        $historialDesglose = [];
        ?>
                       @foreach ($tesorerias as $fecha => $tesoreriaFechas)
                    @foreach ($tesoreriaFechas as $auxiliar)
                        <?php
                        $totalCargos = 0;
                        $totalAbonos = 0;
                        $saldosFinales = 0;
                        $isPrimerIngreso = false;
                        $montoInicial = 0;

                        ?>
                        @foreach ($auxiliar as $key => $bancosAuxiliar)
                            <?php
                            //  dd($bancosAuxiliar);
                            if ($bancosAuxiliar['cargos'] != null) {
                                $totalCargos += (float) $bancosAuxiliar['cargos'];
                                // dd($totalCargos);
                            }
                            if ($bancosAuxiliar['abonos'] != null) {
                                $totalAbonos += (float) $bancosAuxiliar['abonos'];
                                // dd($totalAbonos);
                            }
                    
                            if ($key == 0) {
                                // $montoInicial = array_key_exists($bancosAuxiliar['cuenta'], $saldoFinal) ? $historialDesglose[$bancosAuxiliar['cuenta']] : $montoInicial;
                                  $montoInicial = array_key_exists($bancosAuxiliar['cuenta'], $saldoFinal) ? $saldoFinal[$bancosAuxiliar['cuenta']]  : $montoInicial;
                                // $montoInicial = $saldoFinal[$bancosAuxiliar['cuenta']];
                                $saldosFinales = $montoInicial + $totalCargos - $totalAbonos;
                            } else {
                                $saldosFinales = $montoInicial + $totalCargos - $totalAbonos;
                            }
                            // dd($saldosFinales, $montoInicial);
                    
                            if (!array_key_exists($bancosAuxiliar['cuenta'], $historialDesglose)) {
                                //Creamos la key en el arreglo q contendra los saldos finales
                                $historialDesglose[$bancosAuxiliar['cuenta']] = $saldosFinales;
                                $saldoFinal[$bancosAuxiliar['cuenta']] = $saldosFinales;
                            } else {
                                //Incrementamos el saldo final de la misma key
                                $historialDesglose[$bancosAuxiliar['cuenta']] = $saldosFinales;
                                $saldoFinal[$bancosAuxiliar['cuenta']] = $saldosFinales;
                            }
                            // dd($historialDesglose);
                            ?>

                    <tr>
                        <td style="border: solid:black"> {{ $bancosAuxiliar['cuenta'] }} </td>
                        <td style="border: solid:black">{{ $bancosAuxiliar['movimiento'] }}</td>
                        <td style="border: solid:black">{{ $bancosAuxiliar['referencia'] }}</td>
                        <td style="border: solid:black">{{ $bancosAuxiliar['beneficiario'] }}</td>
                        <td style="border: solid:black">
                            {{ $bancosAuxiliar['cargos'] === null ? 0 : number_format($bancosAuxiliar['cargos'], 2) }}
                        </td>
                        <td style="border: solid:black">
                            {{ $bancosAuxiliar['abonos'] === null ? 0 : number_format($bancosAuxiliar['abonos'], 2) }}
                        </td>
                        <td style="border: solid:black">{{ number_format($saldosFinales, 2) }}</td>
                    </tr>
                @endforeach

                <tr>
                    <th style="font-weight: bold; border: solid:black">
                        <p>{{ $bancosAuxiliar['fecha'] }} </p>
                    </th>
                    <th style="font-weight: bold; border: solid:black">
                        <p></p>
                    </th>
                    <th style="font-weight: bold; border: solid:black">
                        <p></p>
                    </th>

                    <th style="font-weight: bold; border: solid:black">
                        <p>Totales</p>
                    </th>

                    <th style="font-weight: bold; border: solid:black">
                        <p>${{ number_format($totalCargos, 2) }}</p>
                    </th>
                    <th style="font-weight: bold; border: solid:black">
                        <p>${{ number_format($totalAbonos, 2) }}</p>
                    </th>
                    <th style="font-weight: bold; border: solid:black">
                        <p>${{ number_format($saldosFinales, 2) }}</p>
                    </th>
                </tr>
            @endforeach
        @endforeach



    </table>
</table>
