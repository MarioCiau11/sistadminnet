<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPORTE - FORMA COBRO CXC</title>

    <link rel="stylesheet" href="{{ asset('css/reportes/reportes.css') }}">


</head>

<body>
    <table class="cabecera ancho">
        <tr>
            <td class="logo">
                <img src="{{ $logo }}" alt="Logo de la empresa">
            </td>

            <td>
                <h3><strong>REPORTE - FORMA COBRO CXC </strong></h3>
            </td>

            <td class="info-compra">
                <p><strong>Fecha de Emisión </strong> {{ \Carbon\Carbon::now()->isoFormat('LL') }}</p>
            </td>
        </tr>

    </table>

    <?php

    //  dd($ventas_contado, $cobros_credido, $movimientos);

      //sacar los metodos de pago de los movimientos
        $metodos_pago = array();
        $totales_metodos_pago = array();
        $totales_metodos_pago2 = array('total' => 0);
        foreach ($movimientos as $item) {
            if(!in_array($item->formsPayment_name, $metodos_pago)){
                array_push($metodos_pago, $item->formsPayment_name);
            }
        }

        foreach ($metodos_pago as $metodo_pago) {

            
            
            foreach ($movimientos  as $movimiento) {
               
                if($metodo_pago == $movimiento->formsPayment_name){
                    if(!isset($totales_metodos_pago[$metodo_pago])){
                        $totales_metodos_pago[$metodo_pago] = 0;
                    }

                    if(isset($movimiento->sales_total)){
                        $totales_metodos_pago[$metodo_pago] += ($movimiento->sales_total * $movimiento->sales_typeChange);
                        $totales_metodos_pago2['total'] += ($movimiento->sales_total * $movimiento->sales_typeChange);
                    }else{
                        $totales_metodos_pago[$metodo_pago] +=( $movimiento->accountsReceivable_balance * $movimiento->accountsReceivable_typeChange);
                        $totales_metodos_pago2['total'] += ($movimiento->accountsReceivable_balance * $movimiento->accountsReceivable_typeChange);
                    }
                }
            }
            
        }

    ?>


    <table class="informacion-ventasContado">
      <th style="border: 1px solid black" colspan="10">RESUMEN DE OPERACIONES DE CONTADO</th>

      <tr>
        <th>FECHA</th>
        <th>OPERACIÓN</th>
        <th>FOLIO</th>
        <th>FORMA DE PAGO</th>
        <th>REFERENCIA</th>
        <th>INGRESO</th>
        <th>EGRESO</th>
        <th>TOTAL DÓLAR</th>
        <th>TIPO CAMBIO</th>
        <th>TOTAL PESOS</th>
      </tr>
      <?php

      $total_dolar = 0;
      $totales = 0;

      ?>

        @foreach ($ventas_contado as $item)
        <tr>
            <td>{{ $item->sales_issuedate }}</td>
            <td>{{ $item->sales_movement }}</td>
            <td>{{ $item->sales_movementID }}</td>
            <td>{{ $item->formsPayment_name }}</td>
            <td>{{ $item->sales_reference }}</td>
            @if($item->sales_typeChange == 1)
            <td style="text-align: right;">$ {{ number_format($item->sales_total, 2) }}</td>
            @else
            <td>-</td>
            @endif
            <td>-</td>
            @if($item->sales_typeChange != 1)
            <td style="text-align: right;">$ {{ number_format($item->sales_total, 2) }}</td>
            <?php $total_dolar += $item->sales_total; ?>
            @else
            <td>-</td>
            @endif
            <td>$ {{ $item->sales_typeChange }}</td>
            <td style="text-align: right">$ {{ number_format(($item->sales_total * $item->sales_typeChange ), 2) }}</td>
            <?php $totales += ($item->sales_total * $item->sales_typeChange); ?>
        </tr>
        @endforeach

        <tr>
            <td colspan="7" style="text-align: right"><strong>TOTALES</strong></td>
            <td><strong>$ {{ number_format($total_dolar, 2) }}</strong></td>
            <td><strong>-</strong></td>
            <td style="text-align: right;"><strong>$ {{ number_format($totales, 2) }}</strong></td>
        </tr>

    </table>


    <br><br>

    <table class="informacion-ventasContado">
        <th style="border: 1px solid black" colspan="9">RESUMEN DE OPERACIONES A CRÉDITO</th>

        <tr>
            <th>FECHA</th>
            <th>OPERACIÓN</th>
            <th>FOLIO</th>
            <th>FORMA DE PAGO</th>
            <th>REFERENCIA</th>
            <th>INGRESO</th>
            <th>TOTAL DÓLAR</th>
            <th>TIPO CAMBIO</th>
            <th>TOTAL PESOS</th>
            <th></th>
          </tr>

            <?php
            $total_dolar = 0;
            $totales = 0;
            ?>

            @foreach ($cobros_credido as $item)
            <tr>
                <td>{{ $item->accountsReceivable_issuedate }}</td>
                <td>{{ $item->accountsReceivable_movement }}</td>
                <td>{{ $item->accountsReceivable_movementID }}</td>
                <td>{{ $item->formsPayment_name }}</td>
                <td>{{ $item->accountsReceivable_reference }}</td>
                @if($item->accountsReceivable_typeChange == 1)
                <td style="text-align: right;">$ {{ number_format($item->accountsReceivable_balance, 2) }}</td>
                @else
                <td>-</td>
                @endif
                @if($item->accountsReceivable_typeChange != 1)
                <td style="text-align: right;">$ {{ number_format($item->accountsReceivable_balance, 2) }}</td>
                <?php $total_dolar += $item->accountsReceivable_balance; ?>
                @else
                <td>-</td>
                @endif
                <td>$ {{ $item->accountsReceivable_typeChange }}</td>
                <td style="text-align: right">$ {{ number_format(($item->accountsReceivable_balance * $item->accountsReceivable_typeChange ), 2) }}</td>

                <?php $totales += ($item->accountsReceivable_balance * $item->accountsReceivable_typeChange); ?>
                <td></td>
            </tr>
            @endforeach

            <tr>
                <td colspan="6" style="text-align: right"><strong>TOTALES</strong></td>
                <td><strong>$ {{ number_format($total_dolar, 2) }}</strong></td>
                <td><strong>-</strong></td>
                <td style="text-align: right;"><strong>$ {{ number_format($totales, 2) }}</strong></td>
            </tr>
      </table>


      <table style="margin: 50px 25px">
            @foreach ($metodos_pago as $item)

            <tr>
                <td style="border: 1px solid black;">TOTALES {{ $item }}</td>
                <td style="border: 1px solid black;">$ {{ number_format($totales_metodos_pago[$item], 2) }}</td>
            </tr>

            @endforeach

            <tr>
                <th>GRAN TOTAL:</th>
                <th>$ {{ number_format($totales_metodos_pago2['total'], 2) }}</th>
            </tr>
      </table>

</body>

</html>
