<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Tesoreria</title>

    <link rel="stylesheet" href="{{ asset('css/reportes/reportes.css') }}">


</head>

<body>
    <table class="cabecera ancho">
        <tr>
            <td class="logo">
                <img src="{{ $logo }}" alt="Logo de la empresa">
            </td>


            <td class="info-empresa">
                <h3>{{ $tesoreria->companies_name }}</h3>
                <p>R.F.C. {{ $tesoreria->companies_rfc }}</p>
            </td>

            <td class="info-compra">
                <p><strong>Fecha Emision: </strong>
                    {{ \Carbon\Carbon::parse($tesoreria->treasuries_issuedate)->format('d/m/Y') }}</p>
                <h3><strong>Folio :</strong> <span class="folio-bold">{{ $tesoreria->treasuries_movementID }}</span>
                </h3>
                <p><strong>Estatus: </strong> {{ $tesoreria->treasuries_status }}</p>
                <p><strong>Sucursal: </strong> {{ $tesoreria->branchOffices_name }}</p>
            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>{{ $tesoreria->treasuries_movement }}</strong></h3>
            </td>
        </tr>

    </table>


    <table class="informacion-proveedor">
        <tr>
            <td>
                <p>CUENTA</p>
            </td>

            <td>
                @if ($tesoreria->treasuries_moneyAccount !== null)
                    <p>{{ $tesoreria->treasuries_moneyAccount }}</p>
                @else
                    <p>{{ $tesoreria->treasuries_moneyAccountOrigin }}</p>
                @endif
            </td>
        </tr>

        @if ($tesoreria->treasuries_movement == 'Traspaso Cuentas')
            <tr>
                <td>
                    <p>CUENTA DESTINO</p>
                </td>

                <td>
                    <p>{{ $tesoreria->treasuries_moneyAccountDestiny }}</p>
                </td>
            </tr>
        @endif


        <tr>
            <td>
                <p>BENEFICIARIO:</p>
            </td>
            <td>
                @if ($tesoreria->treasuries_movement == 'Sol. de Cheque/Transferencia' || $tesoreria->treasuries_movement == 'Transferencia Electrónica')
                    <p>{{ $tesoreria->providers_name }}</p>
                @else
                    <p>{{ $tesoreria->customers_businessName }}</p>
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <p>REFERENCIA:</p>
            </td>
            <td>
                <p>{{ $tesoreria->treasuries_reference }}</p>
            </td>
        </tr>

        <tr>
            <td>
                <p>FORMA PAGO:</p>
            </td>
            <td>
                <p>{{ $tesoreria->treasuries_paymentMethod }}</p>
            </td>
        </tr>

        <tr>
            <td>
                <p>CONCEPTO:</p>
            </td>
            <td>
                <p>{{ $tesoreria->treasuries_concept }}</p>
            </td>
        </tr>

        <tr>
            <td>
                <p>OBSERVACIONES:</p>
            </td>
            <td>
                <p>{{ $tesoreria->treasuries_observations }}</p>
            </td>
        </tr>

        <tr>
            <td>
                <p>IMPORTE:</p>
            </td>
            <td style="text-align: right">
                <p>${{ number_format($tesoreria->treasuries_amount, 2) }}</p>
            </td>
        </tr>


    </table>

    @if (
        $tesoreria->treasuries_movement == 'Sol. de Cheque/Transferencia' ||
            $tesoreria->treasuries_movement == 'Transferencia Electrónica' ||
            $tesoreria->treasuries_movement == 'Solicitud Depósito' ||
            $tesoreria->treasuries_movement == 'Depósito')
        <table class="articulos-table">
            <tr>
                <th>
                    <p>IMPORTE</p>
                </th>
                <th>
                    <p>FORMA PAGO</p>
                </th>
                <th>
                    <p>REFERENCIA</p>
                </th>
            </tr>


            @foreach ($tesoreriaDin as $key => $tesoreria)
                <tr>
                    <td style="text-align: right">${{ number_format($tesoreria->treasuries_amount, 2) }}</td>
                    <td style="text-align: right">{{ $tesoreria->treasuries_paymentMethod }}</td>
                    <td style="text-align: right">
                        {{ $tesoreria->treasuries_reference }} {{ $tesoreria->treasuries_origin }}
                        {{ $tesoreria->treasuries_originID }}</td>
                </tr>
            @endforeach

        </table>
    @endif
    <table class="costos-desglosados">
        <tr>
            <td class="anchoCompleto">

            </td>
            <td>
                <p style="text-align: right">Subtotal</p>
            </td>
            <td>
                <p class="numeros-reportes" style="text-align: right">$
                    {{ number_format($tesoreria->treasuries_amount, 2) }}</p>
            </td>
        </tr>
        <tr>
            <td class="anchoCompleto"></td>
            <td>
                <p style="text-align: right">Impuesto</p>
            </td>
            <td>
                <p class="numeros-reportes" style="text-align: right">$
                    {{ number_format($tesoreria->treasuries_taxes, 2) }}</p>
            </td>
        </tr>

        <tr>
            <td class="anchoCompleto">
                <p style="text-align: right"><?php
                $totalReporte = $tesoreria->treasuries_amount + $tesoreria->treasuries_taxes;
                $formato = new NumeroALetras();
                $formato->apocope = true;
                echo $formato->toMoney($totalReporte, 2, $tesoreria->money_key, 'CENTAVOS '), $tesoreria->money_keySat;
                
                ?></p>
            </td>
            <td>
                <p style="text-align: right">Total</p>
            </td>
            <td>
                <p class="numeros-reportes" style="text-align: right">$ {{ number_format($totalReporte, 2) }}</p>
            </td>
        </tr>

    </table>
</body>

</html>
