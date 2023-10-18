<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Cuentas por Pagar</title>

    <link rel="stylesheet" href="{{ asset('css/reportes/reportes.css') }}">


</head>

<body>
    <table class="cabecera ancho">
        <tr>
            <td class="logo">
                <img src="{{ $logo }}" alt="Logo de la empresa">
            </td>


            <td class="info-empresa">
                <h3>{{ $cuentasxpagar->companies_name }}</h3>
                <p>R.F.C. {{ $cuentasxpagar->companies_rfc }}</p>
            </td>

            <td class="info-compra">
                <p><strong>Fecha Emision: </strong>
                    {{ \Carbon\Carbon::parse($cuentasxpagar->accountsPayable_issuedate)->format('d/m/Y') }}</p>
                <h3><strong>Folio :</strong> <span
                        class="folio-bold">{{ $cuentasxpagar->accountsPayable_movementID }}</span>
                </h3>
                <p><strong>Estatus: </strong> {{ $cuentasxpagar->accountsPayable_status }}</p>
                <p><strong>Sucursal: </strong>{{ session('sucursal')->branchOffices_key }} -
                    {{ session('sucursal')->branchOffices_name }}</p>
            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>{{ $cuentasxpagar->accountsPayable_movement }} -
                        {{ $cuentasxpagar->accountsPayable_movementID }}</strong></h3>
            </td>
        </tr>

    </table>

    <table class="informacion-proveedor">
        <tr>
            <td>
                <p>PROVEEDOR:</p>
            </td>
            <td>
                <p>{{ $cuentasxpagar->providers_name }}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>FORMA:</p>
            </td>
            <td>
                @if ($cuentasxpagar->formsPayment_name == null)
                    <p>POR DEFINIR</p>
                @else
                    <p>{{ $cuentasxpagar->formsPayment_name }}</p>
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <p>CONCEPTO:</p>
            </td>
            <td>
                @if ($cuentasxpagar->accountsPayable_concept == null)
                    <p>SIN CONCEPTO</p>
                @else
                    <p>{{ $cuentasxpagar->accountsPayable_concept }}</p>
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <p>OBSERVACIONES:</p>
            </td>
            <td>
                @if ($cuentasxpagar->accountsPayable_observations == null)
                    <p>SIN OBSERVACIONES</p>
                @else
                    <p>{{ $cuentasxpagar->accountsPayable_observations }}</p>
                @endif
            </td>
        </tr>

    </table>

    <table class="articulos-table">
        <tr>
            @if (
                $cuentasxpagar->accountsPayable_movement == 'Entrada por Compra' ||
                    $cuentasxpagar->accountsPayable_movement == 'Factura de Gasto' ||
                    $cuentasxpagar->accountsPayable_movement == 'Anticipo')
                <th>MOVIMIENTO</th>
                <th>IMPUESTOS</th>
                <th>IMPORTE</th>
            @elseif ($cuentasxpagar->accountsPayable_movement == 'Aplicación')
                <th>#</th>
                <th>APLICACIÓN</th>
                <th>IMPORTE</th>
            @elseif ($cuentasxpagar->accountsPayable_movement == 'Pago de Facturas')
                <th>#</th>
                <th>APLICACIÓN</th>
                <th>CUENTA</th>
                <th>IMPORTE</th>
            @endif


        </tr>

        @foreach ($cuentas_pagar as $key => $cxp)
            <tr>
                @if (
                    $cuentasxpagar->accountsPayable_movement == 'Entrada por Compra' ||
                        $cuentasxpagar->accountsPayable_movement == 'Factura de Gasto' ||
                        $cuentasxpagar->accountsPayable_movement == 'Anticipo')
                    <td>{{ $cxp->accountsPayable_movement }} {{ $cxp->accountsPayable_movementID }}</td>
                    <td style="text-align: right">${{ number_format($cxp->accountsPayable_taxes, 2, '.', ',') }}</td>
                    <td style="text-align: right">${{ number_format($cxp->accountsPayable_total, 2) }}</td>
                @elseif ($cuentasxpagar->accountsPayable_movement == 'Aplicación')
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $cxp->accountsPayableDetails_apply }} {{ $cxp->accountsPayableDetails_applyIncrement }}
                    </td>
                    <td style="text-align: right">${{ number_format($cxp->accountsPayableDetails_amount, 2) }}</td>
                @elseif ($cuentasxpagar->accountsPayable_movement == 'Pago de Facturas')
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $cxp->accountsPayableDetails_apply }} {{ $cxp->accountsPayableDetails_applyIncrement }}
                    </td>
                    <td>{{ $cxp->accountsPayable_moneyAccount }}</td>
                    <td style="text-align: right">${{ number_format($cxp->accountsPayableDetails_amount, 2) }}</td>
                @endif
            </tr>
        @endforeach
    </table>

    <table class="costos-desglosados">

        <tr>
            <td class="anchoCompleto">
                <p style="text-align: right"><?php
                $formato = new NumeroALetras();
                echo $formato->toMoney($cuentasxpagar->accountsPayable_total, 2, $cuentasxpagar->money_key, 'CENTAVOS '), $cuentasxpagar->money_keySat;
                
                ?></p>
            </td>
            <td>
                <p style="text-align: right">Total</p>
            </td>
            <td>
                <p class="numeros-reportes" style="text-align: right">$
                    {{ number_format($cuentasxpagar->accountsPayable_total, 2) }}</p>
            </td>
        </tr>

    </table>
</body>

</html>
