<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Tesoreria Desglosado</title>

    <link rel="stylesheet" href="{{ asset('css/reportes/reportes.css') }}">
    <style>
        .page-break {
            page-break-after: always;
        }
    </style>


</head>

<body>
    <table class="cabecera ancho">
        <tr>
            <td class="logo">
                <img src="{{ $logo }}" alt="Logo de la empresa">
            </td>

            <td class="info-empresa">
                <h3>{{ session('company')->companies_name }}</h3>
                <p>R.F.C. {{ session('company')->companies_rfc }}</p>
            </td>

            <td class="info-compra">
                <p><strong>Fecha de Emisión </strong> {{ \Carbon\Carbon::now()->isoFormat('LL') }}</p>

            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>REPORTE DE TESORERIA DESGLOSADO</strong></h3>
            </td>
        </tr>

    </table>

    <table class="articulos-table">
        <tr>
            <th>
                <p>CUENTA</p>
            </th>
            <th style="width: 100px">
                <p>MOVIMIENTO</p>
            </th>
            <th>
                <p>REFERENCIA</p>
            </th>
            <th style="text-align: left">
                <p>BENEFICIARIO</p>
            </th>
            {{-- <th>
                <p>INICIO</p>
            </th> --}}
            <th>
                <p>CARGOS</p>
            </th>
            <th>
                <p>ABONOS</p>
            </th>
            <th>
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
                        <td> {{ $bancosAuxiliar['cuenta'] }} </td>
                        </td>
                        <td>{{ $bancosAuxiliar['movimiento'] }}</td>
                        <td>{{ $bancosAuxiliar['referencia'] }}</td>
                        <td style="text-align: left">{{ $bancosAuxiliar['beneficiario'] }}</td>

                        <td style="text-align: right">
                            {{ $bancosAuxiliar['cargos'] === null ? 0 : number_format($bancosAuxiliar['cargos'], 2) }}
                        </td>
                        <td style="text-align: right">
                            {{ $bancosAuxiliar['abonos'] === null ? 0 : number_format($bancosAuxiliar['abonos'], 2) }}
                        </td>
                        <td style="text-align: right">{{ number_format($saldosFinales, 2) }}</td>
                    </tr>
                @endforeach

                <tr>
                    <th>
                        <p>{{ $fecha }} </p>
                    </th>
                    <th>
                        <p></p>
                    </th>
                    <th></th>
                    <th>
                        <p style="text-align: right">Totales</p>
                    </th>

                    <th>
                        <p style="text-align: right">${{ number_format($totalCargos, 2) }}</p>
                    </th>
                    <th>
                        <p style="text-align: right">${{ number_format($totalAbonos, 2) }}</p>
                    </th>
                    <th>
                        <p style="text-align: right">${{ number_format($saldosFinales, 2) }}</p>
                    </th>
                </tr>
            @endforeach
        @endforeach
    </table>

</body>

</html>
