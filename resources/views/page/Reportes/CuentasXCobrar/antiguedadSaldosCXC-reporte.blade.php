<?php
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

        @foreach ($clientesEstado as $clienteCXC)
            <tr>
                <td>
                    <p>{{ $clienteCXC['customers_key'] . ' - ' . $clienteCXC['customers_businessName'] }}</p>

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


                @foreach ($clienteCXC['cuentasxc'] as $movientos)
                    <tr>
                        <td>{{ $movientos['accountsReceivableP_movement'] }} {{ $movientos['accountsReceivableP_movementID'] }}</td>
                        <td>{{ $movientos['accountsReceivableP_reference'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($movientos['accountsReceivableP_issuedate'])->format('d/m/Y') }}
                        </td>
                        <td>{{ \Carbon\Carbon::parse($movientos['accountsReceivableP_expiration'])->format('d/m/Y') }}
                        </td>
                        <td>{{ $movientos['accountsReceivableP_moratoriumDays'] }}</td>
                        @if ($movientos['accountsReceivableP_moratoriumDays'] <= 0)
                            <td style="text-align: right">$ {{ number_format($movientos['accountsReceivableP_balanceTotal'], 2) }}</td>
                        @else
                            <td style="text-align: right">$ 0.00</td>
                        @endif
                        @if ($movientos['accountsReceivableP_moratoriumDays'] > 0 &&
                            $movientos['accountsReceivableP_moratoriumDays'] <= 15)
                            <td style="text-align: right">$ {{ number_format($movientos['accountsReceivableP_balanceTotal'], 2) }}</td>
                        @else
                            <td style="text-align: right">$ 0.00</td>
                        @endif
                        @if ($movientos['accountsReceivableP_moratoriumDays'] > 16 &&
                            $movientos['accountsReceivableP_moratoriumDays'] <= 30)
                            <td style="text-align: right">$ {{ number_format($movientos['accountsReceivableP_balanceTotal'], 2) }}</td>
                        @else
                            <td  style="text-align: right">$ 0.00</td>
                        @endif
                        @if ($movientos['accountsReceivableP_moratoriumDays'] > 31 &&
                            $movientos['accountsReceivableP_moratoriumDays'] <= 60)
                            <td style="text-align: right">$ {{ number_format($movientos['accountsReceivableP_balanceTotal'], 2) }}</td>
                        @else
                            <td style="text-align: right">$ 0.00</td>
                        @endif
                        @if ($movientos['accountsReceivableP_moratoriumDays'] > 61 &&
                            $movientos['accountsReceivableP_moratoriumDays'] <= 90)
                            <td style="text-align: right">$ {{ number_format($movientos['accountsReceivableP_balanceTotal'], 2) }}</td>
                        @else
                            <td style="text-align: right">$ 0.00</td>
                        @endif
                        @if ($movientos['accountsReceivableP_moratoriumDays'] > 91)
                            <td style="text-align: right">$ {{ number_format($movientos['accountsReceivableP_balanceTotal'], 2) }}</td>
                        @else
                            <td style="text-align: right">$ 0.00</td>
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
                
                if ($clienteCXC['cuentasxc'] != null) {
                    foreach ($clienteCXC['cuentasxc'] as $movientos) {
                        if ($movientos['accountsReceivableP_moratoriumDays'] <= 0) {
                            $totalCorriente += $movientos['accountsReceivableP_balanceTotal'];
                        }
                        if ($movientos['accountsReceivableP_moratoriumDays'] > 0 && $movientos['accountsReceivableP_moratoriumDays'] <= 15) {
                            $total1a15 += $movientos['accountsReceivableP_balanceTotal'];
                        }
                        if ($movientos['accountsReceivableP_moratoriumDays'] > 16 && $movientos['accountsReceivableP_moratoriumDays'] <= 30) {
                            $total16a30 += $movientos['accountsReceivableP_balanceTotal'];
                        }
                        if ($movientos['accountsReceivableP_moratoriumDays'] > 31 && $movientos['accountsReceivableP_moratoriumDays'] <= 60) {
                            $total31a60 += $movientos['accountsReceivableP_balanceTotal'];
                        }
                        if ($movientos['accountsReceivableP_moratoriumDays'] > 61 && $movientos['accountsReceivableP_moratoriumDays'] <= 90) {
                            $total61a90 += $movientos['accountsReceivableP_balanceTotal'];
                        }
                        if ($movientos['accountsReceivableP_moratoriumDays'] > 91) {
                            $total91mas += $movientos['accountsReceivableP_balanceTotal'];
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
        {{-- hacemos un total de los totales ya que <p>${{ number_format($totalCompleto, 2) . ' ' . $movientos['money_key'] }}</p> solo lo hace por cliente --}}
        <?php
        $totalGeneral = 0;
        if ($clientesEstado != null) {
            foreach ($clientesEstado as $clienteCXC) {
                foreach ($clienteCXC['cuentasxc'] as $movientos) {
                    $totalGeneral += $movientos['accountsReceivableP_balanceTotal'];
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
