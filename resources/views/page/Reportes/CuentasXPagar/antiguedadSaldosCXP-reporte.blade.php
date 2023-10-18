<<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ANTIGÜEDAD DE SALDOS</title>

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
                <p><strong>Fecha de Emisión: </strong> {{ \Carbon\Carbon::now()->isoFormat('LL') }}</p>

            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>ANTIGÜEDAD DE SALDOS</strong></h3>
            </td>
        </tr>

    </table>
    <table class="informacion-proveedor2">

        @foreach ($proveedoresEstado as $proveedorCXP)
            <tr>
                <td colspan="11"
                style="">
                <h3 style="color: #fff">
                    <p>{{ $proveedorCXP['providers_key'] . ' - ' . $proveedorCXP['providers_name'] }}</p>
                </td>
            </tr>
                <table class="articulos-table4" style="width: 100%">
                <tr>
                    <th style="width: 150px">
                        <p>MOVIMIENTO</p>
                    </th>
                    <th style="width: 150px">
                        <p>REFERENCIA</p>
                    </th>
                    <th style="width: 100px">
                        <p>EMISIÓN</p>
                    </th>
                    <th style="width: 100px">
                        <p>VENCIMIENTO</p>
                    </th>
                    <th style="width: 80px">
                        <p>DÍAS</p>
                    </th>
                    <th style="width: 100px">
                        <p>AL CORRIENTE</p>
                    </th>
                    <th style="width: 80px">
                        <p>DE 1 A 15</p>
                    </th>
                    <th style="width: 80px">
                        <p>DE 16 A 30</p>
                    </th>
                    <th style="width: 80px">
                        <p>DE 31 A 60</p>
                    </th>
                    <th style="width: 80px">
                        <p>DE 61 A 90</p>
                    </th>
                    <th style="width: 80px">
                        <p>MÁS DE 90 DÍAS</p>
                    </th>
                </tr>


                @foreach ($proveedorCXP['cuentasxp'] as $key => $movientos)
                    <tr>
                        <?php 
                        
                        if($key % 7 == 0 && $key != 0){
                            echo '</table>';
                            echo '<div class="page-break"></div>';
                            echo '<table class="articulos-table4" style="width: 100%">
                            <tr>
                                <th style="width: 150px">
                                    <p>MOVIMIENTO</p>
                                </th>
                                <th style="width: 150px">
                                    <p>REFERENCIA</p>
                                </th>
                                <th style="width: 100px">
                                    <p>EMISIÓN</p>
                                </th>
                                <th style="width: 100px">
                                    <p>VENCIMIENTO</p>
                                </th>
                                <th style="width: 80px">
                                    <p>DÍAS</p>
                                </th>
                                <th style="width: 100px">
                                    <p>AL CORRIENTE</p>
                                </th>
                                <th style="width: 80px">
                                    <p>DE 1 A 15</p>
                                </th>
                                <th style="width: 80px">
                                    <p>DE 16 A 30</p>
                                </th>
                                <th style="width: 80px">
                                    <p>DE 31 A 60</p>
                                </th>
                                <th style="width: 80px">
                                    <p>DE 61 A 90</p>
                                </th>
                                <th style="width: 80px">
                                    <p>MÁS DE 90 DÍAS</p>
                                </th>
                            </tr>';
                        }
                            
                        ?>
                        <td>{{ $movientos['accountsPayableP_movement'] }} {{ $movientos['accountsPayableP_movementID'] }}</td>
                        <td>{{ $movientos['accountsPayableP_reference'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($movientos['accountsPayableP_issuedate'])->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($movientos['accountsPayableP_expiration'])->format('d/m/Y') }}
                        </td>
                        <td>{{ $movientos['accountsPayableP_moratoriumDays'] }}</td>
                        @if ($movientos['accountsPayableP_moratoriumDays'] <= 0)
                            <td style="text-align: right;">$ {{ number_format($movientos['accountsPayableP_balanceTotal'], 2) }}</td>
                        @else
                            <td style="text-align: right;">$ 0.00</td>
                        @endif
                        @if ($movientos['accountsPayableP_moratoriumDays'] >= 1 && $movientos['accountsPayableP_moratoriumDays'] <= 15)
                            <td style="text-align: right;">$ {{ number_format($movientos['accountsPayableP_balanceTotal'], 2) }}</td>
                        @else
                            <td style="text-align: right;">$ 0.00</td>
                        @endif
                        @if ($movientos['accountsPayableP_moratoriumDays'] >= 16 && $movientos['accountsPayableP_moratoriumDays'] <= 30)
                            <td style="text-align: right;">$ {{ number_format($movientos['accountsPayableP_balanceTotal'], 2) }}</td>
                        @else
                            <td style="text-align: right;">$ 0.00</td>
                        @endif
                        @if ($movientos['accountsPayableP_moratoriumDays'] >= 31 && $movientos['accountsPayableP_moratoriumDays'] <= 60)
                            <td style="text-align: right;">$ {{ number_format($movientos['accountsPayableP_balanceTotal'], 2) }}</td>
                        @else
                            <td style="text-align: right;">$ 0.00</td>
                        @endif
                        @if ($movientos['accountsPayableP_moratoriumDays'] >= 61 && $movientos['accountsPayableP_moratoriumDays'] <= 90)
                            <td style="text-align: right;">$ {{ number_format($movientos['accountsPayableP_balanceTotal'], 2) }}</td>
                        @else
                            <td style="text-align: right;">$ 0.00</td>
                        @endif
                        @if ($movientos['accountsPayableP_moratoriumDays'] >= 91)
                            <td style="text-align: right;">$ {{ number_format($movientos['accountsPayableP_balanceTotal'], 2) }}</td>
                        @else
                            <td style="text-align: right;">$ 0.00</td>
                        @endif
                    </tr>
                @endforeach
                <?php
                $totalCorriente = 0;
                $total1a15 = 0;
                $total16a30 = 0;
                $total31a60 = 0;
                $total61a90 = 0;
                $total91mas = 0;
                
                if ($proveedorCXP['cuentasxp'] != null) {
                    foreach ($proveedorCXP['cuentasxp'] as $movientos) {
                        if ($movientos['accountsPayableP_moratoriumDays'] <= 0) {
                            $totalCorriente += $movientos['accountsPayableP_balanceTotal'];
                        }
                        if ($movientos['accountsPayableP_moratoriumDays'] > 0 && $movientos['accountsPayableP_moratoriumDays'] <= 15) {
                            $total1a15 += $movientos['accountsPayableP_balanceTotal'];
                        }
                        if ($movientos['accountsPayableP_moratoriumDays'] > 16 && $movientos['accountsPayableP_moratoriumDays'] <= 30) {
                            $total16a30 += $movientos['accountsPayableP_balanceTotal'];
                        }
                        if ($movientos['accountsPayableP_moratoriumDays'] > 31 && $movientos['accountsPayableP_moratoriumDays'] <= 60) {
                            $total31a60 += $movientos['accountsPayableP_balanceTotal'];
                        }
                        if ($movientos['accountsPayableP_moratoriumDays'] > 61 && $movientos['accountsPayableP_moratoriumDays'] <= 90) {
                            $total61a90 += $movientos['accountsPayableP_balanceTotal'];
                        }
                        if ($movientos['accountsPayableP_moratoriumDays'] > 91) {
                            $total91mas += $movientos['accountsPayableP_balanceTotal'];
                        }
                
                        $totalCompleto = $totalCorriente + $total1a15 + $total16a30 + $total31a60 + $total61a90 + $total91mas;
                    }
                }
                ?>

                <tr>
                    <th>
                        <p>Totales</p>
                    </th>
                    <th>
                        <p>${{ number_format($totalCompleto, 2) . ' ' . $movientos['money_key'] }}</p>

                    </th>
                    <th>
                        <p></p>
                    </th>
                    <th>
                        
                    </th>
                    <th>
                        <td style="text-align: right; font-weight: bold">${{ number_format($totalCorriente, 2) }}</td>

                        <td style="text-align: right; font-weight: bold">${{ number_format($total1a15, 2) }}</td>

                        <td style="text-align: right; font-weight: bold">${{ number_format($total16a30, 2) }}</td>

                        <td style="text-align: right; font-weight: bold">${{ number_format($total31a60, 2) }}</td>

                        <td style="text-align: right; font-weight: bold">${{ number_format($total61a90, 2) }}</td>

                        <td style="text-align: right; font-weight: bold">${{ number_format($total91mas, 2) }}</td>
                    </th>
                </tr>
            </table>
            <br>
        @endforeach
        {{-- hacemos un total general de todas las cuentas por pagar --}}
        <?php
        $totalGeneral = 0;
        if ($proveedoresEstado != null) {
            foreach ($proveedoresEstado as $proveedorCXP) {
                foreach ($proveedorCXP['cuentasxp'] as $movientos) {
                    $totalGeneral += $movientos['accountsPayableP_balanceTotal'];
                }
            }
        }
        // dd($totalGeneral);
        ?>
        <table class="articulos-table4" style="width: 100%">
            <tr>
                <td>
                    <p><strong>TOTAL GENERAL: </strong> {{ number_format($totalGeneral, 2) }}</p>
                </td>
            </tr>
        </table>
    </table>

</body>

</html>
