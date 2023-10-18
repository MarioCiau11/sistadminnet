<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESTADO DE CUENTA</title>

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

                {{-- <p><strong>Sucursal:</strong> <span class="folio-bold">{{ $sucursal }}</span> --}}
                </p>
            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>ESTADO DE CUENTA</strong></h3>
            </td>
        </tr>

    </table>
    <table class="informacion-proveedor3" style="width: 100%; margin-top: 25px;">
        @foreach ($proveedoresEstado as $proveedorCXP)
            <tr>
                <td colspan="7">
                    <p>{{ $proveedorCXP['providers_key'] . ' - ' . $proveedorCXP['providers_name'] }}</p>
                </td>
            </tr>

            <tr>
                    <tr>
                        <th style="width: 150px">
                            <p>MOVIMIENTO</p>
                        </th>
                        <th style="width: 150px">
                            <p>FORMA DE PAGO</p>
                        </th>
                        <th style="width: 150px">
                            <p>FECHA</p>
                        </th>
                        <th style="width: 150px">
                            <p>CARGOS</p>
                        </th>
                        <th style="width: 150px">
                            <p>ABONOS</p>
                        </th>
                        <th style="width: 150px">
                            <p>SALDO</p>
                        </th>
                        <th style="width: 150px">
                            <p>MONEDA</p>
                        </th>
                    </tr>
                    <?php $saldo = 0; ?>
                    @foreach ($proveedorCXP['cuentasxp'] as $key => $movientos)

                    <?php 

                    if($key % 22 == 0 && $key != 0){
                        echo '</table>';
                        echo '<div class="page-break"></div>';
                        echo '<table class="informacion-proveedor3" style="width: 100%; margin-top: 25px;">
                            <tr>
                        <th style="width: 150px">
                            <p>MOVIMIENTO</p>
                        </th>
                        <th style="width: 150px">
                            <p>FORMA DE PAGO</p>
                        </th>
                        <th style="width: 150px">
                            <p>FECHA</p>
                        </th>
                        <th style="width: 150px">
                            <p>CARGOS</p>
                        </th>
                        <th style="width: 150px">
                            <p>ABONOS</p>
                        </th>
                        <th style="width: 150px">
                            <p>SALDO</p>
                        </th>
                        <th style="width: 150px">
                            <p>MONEDA</p>
                        </th>
                    </tr>';
                            
                    }

                    ?>


                        <tr>
                            <td>{{ $movientos['assistant_movement'] }}
                                {{ $movientos['assistant_movementID'] }}</td>
                            <td>{{ $movientos['accountsPayable_formPayment'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($movientos['created_at'])->format('d/m/Y') }}
                            </td>
                            <td style="text-align: right;">${{ number_format($movientos['assistant_charge'], 2) }}</td>
                            <td style="text-align: right;">${{ number_format($movientos['assistant_payment'], 2) }}</td>
                            @if ($movientos['assistant_charge'] !== null)
                                <?php $saldo += floatVal($movientos['assistant_charge']); ?>
                            @else
                                <?php $saldo -= floatVal($movientos['assistant_payment']); ?>
                            @endif
                            <td style="text-align: right;">${{ number_format($saldo, 2) }}</td>
                            <td>{{ $movientos['assistant_money'] }}</td>
                        </tr>
                    @endforeach
                    <?php
                    $totalCargos = 0;
                    $totalAbonos = 0;
                    $totalSaldo = 0;
                    
                    foreach ($proveedorCXP['cuentasxp'] as $movientos) {
                        $totalCargos += $movientos['assistant_charge'];
                        $totalAbonos += $movientos['assistant_payment'];
                        $totalSaldo = $totalCargos - $totalAbonos;
                    }
                    ?>

                    <tr>
                        
                        
                        <th>
                            <td> </td>
                            <td style=" font-weight: bold">TOTALES</td>

                            <td style="text-align: right; font-weight: bold">${{ number_format($totalCargos, 2) }}</td>
     
                            <td style="text-align: right; font-weight: bold">${{ number_format($totalAbonos, 2) }}</td>
                       
                            <td style="text-align: right; font-weight: bold">${{ number_format($totalSaldo, 2) }}</td>
                        </th>
                    </tr>
       
            </tr>
            <br>
        @endforeach
    </table>

</body>

</html>
