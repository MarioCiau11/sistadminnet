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
    <table class="informacion-proveedor3">
        @foreach ($proveedoresEstado as $proveedorCXP)
            <tr>
                <td>
                    <p>{{ $proveedorCXP['customers_key'] . ' - ' . $proveedorCXP['customers_businessName'] }}</p>
                </td>
            </tr>

            <tr>
                <table class="articulos-table">
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
                    @foreach ($proveedorCXP['cuentasxp'] as $movientos)
                        <tr>
                            <td>{{ $movientos['assistant_movement'] }}
                                {{ $movientos['assistant_movementID'] }}</td>
                            <td>{{ $movientos['accountsReceivable_formPayment'] }}</td>
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
                            <p> </p>
                        </th>
                                                <th>
                            <p> </p>
                        </th>
                        <th>
                            <p>TOTALES</p>
                        </th>
                        <th>
                            <p style="text-align: right;">${{ number_format($totalCargos, 2) }}</p>
                        </th>
                        <th>
                            <p style="text-align: right;">${{ number_format($totalAbonos, 2) }}</p>
                        </th>
                        <th>
                            <p style="text-align: right;">${{ number_format($totalSaldo, 2) }}</p>
                        </th>
                    </tr>
                </table>
            </tr>
            <br>
        @endforeach
    </table>

</body>

</html>
