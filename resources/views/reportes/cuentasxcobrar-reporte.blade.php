<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Cuentas por Cobrar</title>

    <link rel="stylesheet" href="{{ asset('css/reportes/reportes.css') }}">


</head>

<body>
    <table class="cabecera ancho">
        <tr>
            <td class="logo">
                <img src="{{ $logo }}" alt="Logo de la empresa">
            </td>


            <td class="info-empresa">
                <h3>{{ $cuentasxcobrar->companies_name }}</h3>
                <p>R.F.C. {{ $cuentasxcobrar->companies_rfc }}</p>
            </td>

            <td class="info-compra">
                <p><strong>Fecha Emision: </strong>
                    {{ \Carbon\Carbon::parse($cuentasxcobrar->accountsReceivable_issuedate)->format('d/m/Y') }}</p>
                <h3><strong>Folio :</strong> <span
                        class="folio-bold">{{ $cuentasxcobrar->accountsReceivable_movementID }}</span>
                </h3>
                <p><strong>Estatus: </strong> {{ $cuentasxcobrar->accountsReceivable_status }}</p>
                <p><strong>Sucursal: </strong>{{ session('sucursal')->branchOffices_key }} -
                    {{ session('sucursal')->branchOffices_name }}</p>
            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>{{ $cuentasxcobrar->accountsReceivable_movement }} -
                        {{ $cuentasxcobrar->accountsReceivable_movementID }}</strong></h3>
            </td>
        </tr>

    </table>

    <table class="informacion-proveedor">
        <tr>
            <td>
                <p>PROVEEDOR:</p>
            </td>
            <td>
                <p>{{ $cuentasxcobrar->customers_name }}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>FORMA:</p>
            </td>
            <td>
                @if ($cuentasxcobrar->formsPayment_name == null)
                    <p>POR DEFINIR</p>
                @else
                    <p>{{ $cuentasxcobrar->formsPayment_name }}</p>
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <p>CONCEPTO:</p>
            </td>
            <td>
                @if ($cuentasxcobrar->accountsReceivable_concept == null)
                    <p>SIN CONCEPTO</p>
                @else
                    <p>{{ $cuentasxcobrar->accountsReceivable_concept }}</p>
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <p>OBSERVACIONES:</p>
            </td>
            <td>
                @if ($cuentasxcobrar->accountsReceivable_observations == null)
                    <p>SIN OBSERVACIONES</p>
                @else
                    <p>{{ $cuentasxcobrar->accountsReceivable_observations }}</p>
                @endif
            </td>
        </tr>

    </table>

    <table class="articulos-table">
        <tr>
            @if (
                $cuentasxcobrar->accountsReceivable_movement == 'Factura' ||
                    $cuentasxcobrar->accountsReceivable_movement == 'Anticipo Clientes')
                <th>MOVIMIENTO</th>
                <th>IMPUESTOS</th>
                <th>IMPORTE</th>
            @elseif ($cuentasxcobrar->accountsReceivable_movement == 'Aplicación')
                <th>#</th>
                <th>APLICACIÓN</th>
                <th>IMPORTE</th>
            @elseif ($cuentasxcobrar->accountsReceivable_movement == 'Cobro de Facturas')
                <th>#</th>
                <th>APLICACIÓN</th>
                <th>CUENTA</th>
                <th>IMPORTE</th>
            @endif


        </tr>

        @foreach ($cuentas_cobrar as $key => $cxp)
            <tr>
                @if (
                    $cuentasxcobrar->accountsReceivable_movement == 'Factura' ||
                        $cuentasxcobrar->accountsReceivable_movement == 'Anticipo Clientes')
                    <td>{{ $cxp->accountsReceivable_movement }} {{ $cxp->accountsReceivable_movementID }}</td>
                    <td style="text-align: right">${{ number_format($cxp->accountsReceivable_taxes, 2, '.', ',') }}
                    </td>
                    <td style="text-align: right">${{ number_format($cxp->accountsReceivable_total, 2) }}</td>
                @elseif ($cuentasxcobrar->accountsReceivable_movement == 'Aplicación')
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $cxp->accountsReceivableDetails_apply }}
                        {{ $cxp->accountsReceivableDetails_applyIncrement }}</td>
                    <td style="text-align: right">${{ number_format($cxp->accountsReceivableDetails_amount, 2) }}</td>
                @elseif ($cuentasxcobrar->accountsReceivable_movement == 'Cobro de Facturas')
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $cxp->accountsReceivableDetails_apply }}
                        {{ $cxp->accountsReceivableDetails_applyIncrement }}</td>
                    <td>{{ $cxp->accountsReceivable_moneyAccount }}</td>
                    <td style="text-align: right">${{ number_format($cxp->accountsReceivableDetails_amount, 2) }}</td>
                @endif
            </tr>
        @endforeach
    </table>

    <table class="costos-desglosados">

        <tr>
            <td class="anchoCompleto">
                <p style="text-align: right"><?php
                $formato = new NumeroALetras();
                echo $formato->toMoney($cuentasxcobrar->accountsReceivable_total, 2, $cuentasxcobrar->money_key, 'CENTAVOS '), $cuentasxcobrar->money_keySat;
                ?></p>
            </td>
            <td>
                <p style="text-align: right">Total</p>
            </td>
            <td>
                <p class="numeros-reportes" style="text-align: right">$
                    {{ number_format($cuentasxcobrar->accountsReceivable_total, 2) }}</p>

            </td>
        </tr>

    </table>
</body>

</html>
