
<style>

.informacion-ventasContado {
    width: 100%;
}

.informacion-ventasContado tr td {

    text-align: center;
}
</style>


<table class="cabecera ancho">
        <tr>
           

            <td colspan="10">
                <h3><strong>REPORTE - FORMA COBRO CXC </strong></h3>
            </td>

            <td class="info-compra">
                <p><strong>Fecha de Emisión &nbsp; </strong> {{ \Carbon\Carbon::now()->isoFormat('LL') }}</p>
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
        <tr>
            <th colspan="10" style="text-align: center"> <h3><strong>RESUMEN DE OPERACIONES DE CONTADO</strong></h3></th>
        </tr>

      <tr>
        <th><strong>FECHA</strong></th>
        <th><strong>OPERACIÓN</strong></th>
        <th><strong>FOLIO</strong></th>
        <th><strong>FORMA DE PAGO</strong></th>
        <th><strong>REFERENCIA</strong></th>
        <th><strong>INGRESO</strong></th>
        <th><strong>EGRESO</strong></th>
        <th><strong>TOTAL DÓLAR</strong></th>
        <th><strong>TIPO CAMBIO</strong></th>
        <th><strong>TOTAL PESOS</strong></th>
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
            <td>$ {{ number_format($item->sales_total, 2) }}</td>
            @else
            <td>-</td>
            @endif
            <td>-</td>
            @if($item->sales_typeChange != 1)
            <td>$ {{ number_format($item->sales_total, 2) }}</td>
            <?php $total_dolar += $item->sales_total; ?>
            @else
            <td>-</td>
            @endif
            <td>$ {{ $item->sales_typeChange }}</td>
            <td>$ {{ number_format(($item->sales_total * $item->sales_typeChange ), 2) }}</td>
            <?php $totales += ($item->sales_total * $item->sales_typeChange); ?>
        </tr>
        @endforeach

        <tr>
            <td colspan="7" style="text-align: right"><strong>TOTALES</strong></td>
            <td><strong>$ {{ number_format($total_dolar, 2) }}</strong></td>
            <td><strong>-</strong></td>
            <td><strong>$ {{ number_format($totales, 2) }}</strong></td>
        </tr>

    </table>


    <br><br>

    <table class="informacion-ventasContado">
        <tr>
            <th colspan="9" style="text-align: center"> <h3><strong>RESUMEN DE OPERACIONES A CRÉDITO </strong></h3></th>
        </tr>
       
        <tr>
            <th><strong>FECHA</strong></th>
            <th><strong>OPERACIÓN</strong></th>
            <th><strong>FOLIO</strong></th>
            <th><strong>FORMA DE PAGO</strong></th>
            <th><strong>REFERENCIA</strong></th>
            <th><strong>INGRESO</strong></th>
            <th><strong>TOTAL DÓLAR</strong></th>
            <th><strong>TIPO CAMBIO</strong></th>
            <th><strong>TOTAL PESOS</strong></th>
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
                <td>$ {{ number_format($item->accountsReceivable_balance, 2) }}</td>
                @else
                <td>-</td>
                @endif
                @if($item->accountsReceivable_typeChange != 1)
                <td>$ {{ number_format($item->accountsReceivable_balance, 2) }}</td>
                <?php $total_dolar += $item->accountsReceivable_balance; ?>
                @else
                <td>-</td>
                @endif
                <td>$ {{ $item->accountsReceivable_typeChange }}</td>
                <td>$ {{ number_format(($item->accountsReceivable_balance * $item->accountsReceivable_typeChange ), 2) }}</td>

                <?php $totales += ($item->accountsReceivable_balance * $item->accountsReceivable_typeChange); ?>
                <td></td>
            </tr>
            @endforeach

            <tr>
                <td colspan="6" style="text-align: right"><strong>TOTALES</strong></td>
                <td><strong>$ {{ number_format($total_dolar, 2) }}</strong></td>
                <td><strong>-</strong></td>
                <td><strong>$ {{ number_format($totales, 2) }}</strong></td>
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