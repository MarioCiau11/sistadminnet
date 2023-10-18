<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPORTE UTILIDAD VENTAS VS GASTOS</title>

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
            </td>
        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>REPORTE UTILIDAD DE VENTAS VS GASTOS </strong></h3>
                <h3><strong>{{$nameFecha}}</strong></h3>
                <h3><strong>
                    @if ($nameCliente === 'Todos')
                        {{$nameCliente}}
                    @else
                    {{ isset($venta[0]->sales_customer) ? $venta[0]->sales_customer : '' }} - {{ isset($venta[0]->customers_businessName) ? $venta[0]->customers_businessName : '' }}
                    @endif
                </strong></h3>
            </td>
            
        </tr>

    </table>

    <?php 
    
    //  dd($venta, $gastos);

    
    ?>
    <table class="informacion-prov2">
    
        <tr>
            <td>
                <h3>VENTAS</h2>

            </td>
    
        </tr>
    
        <table class="articulos-table">
            <tr>
                <th style="width: 20%">
                    <p>CLIENTE</p>
                </th>
                @foreach ($venta as $ventas)
                <th>
                    <p>{{ $ventas->customers_key }} - {{ $ventas->customers_businessName }}</p>
                </th>
                @endforeach
            </tr>
            <tr>
                <th>
                    <p style="font-weight: normal;">FOLIO FACTURA</p>
                </th>
                @foreach ($venta as $ventas)
                <th>
                    <p style="font-weight: normal;">{{ $ventas->sales_movementID }}</p>
                </th>
                @endforeach
            </tr>
            <tr>
                <th>
                    <p style="font-weight: normal;">MONTO (PESOS/DLLS)</p>
                </th>
                @foreach ($venta as $ventas)
                <th>
                    <p style="font-weight: normal;">${{ number_format($ventas->sales_total, 2) }}</p>
                </th>
                @endforeach
            </tr>
            <tr>
                <th>
                    <p style="font-weight: normal;">TIPO DE CAMBIO DE FACT.</p>
                </th>
                @foreach ($venta as $ventas)
                <th>
                    <p style="font-weight: normal;">${{ number_format($ventas->sales_typeChange, 4) }}</p>
                </th>
                @endforeach
            </tr>
            <tr>
                <th>
                    <p style="font-weight: normal;">COSTO</p>
                </th>
                @foreach ($venta as $ventas)
                    <th>
                        <?php
                        $totalSaleCost = 0; // Variable para sumar los costos de venta
                        ?>
                        @foreach ($detalleVentas as $detalle)
                            @if ($detalle->salesDetails_saleID === $ventas->sales_id)
                                @if (isset($detalle->salesDetails_saleCost))
                                    <?php
                                    $totalSaleCost += $detalle->salesDetails_saleCost; // Sumar el costo de venta
                                    ?>
                                @endif
                            @endif
                        @endforeach
                            <p style="font-weight: normal;"> ${{ number_format($totalSaleCost, 2) }}</p>
                    </th>
                @endforeach
            </tr>

            <tr>
                <th>
                    <p>TOTAL DE VENTAS</p>
                </th>
                @foreach ($venta as $key => $ventas)
                <th>
                    <?php 
                        $totalVenta = $ventas->sales_total * $ventas->sales_typeChange;  
                        
                        
                    ?>
                    <p>${{ number_format($totalVenta, 2) }}</p>
                    <?php 
                    //a $totalVenta le restamos el costo de venta respectivo    
                        $totalVentaArray[$ventas->sales_id] = $totalVenta;
                        
                    ?>
                </th>
                @endforeach
            </tr>
            </table>
    </table>

    <?php 
    $arrayConceptos = [];
    $arrayConceptosFactura = [];

    foreach ($gastos as $gasto) {
        if(!array_key_exists( $gasto->expensesDetails_concept, $arrayConceptos)){
            $arrayConceptos[$gasto->expensesDetails_concept] = $gasto->expensesDetails_concept;
        }
    }

    
    foreach ($gastos as $gasto) {
        if(!array_key_exists( $gasto->expensesDetails_concept.'-'.$gasto->expenses_antecedentsName, $arrayConceptosFactura)){
            $arrayConceptosFactura[$gasto->expensesDetails_concept.'-'.$gasto->expenses_antecedentsName] = $gasto->expensesDetails_total;
        } else {
            $arrayConceptosFactura[$gasto->expensesDetails_concept.'-'.$gasto->expenses_antecedentsName] += $gasto->expensesDetails_total;
        }
    }
    // dd($arrayConceptos, $gastos, $arrayConceptosFactura);
    ?>
    <table class="informacion-prov2">
    
        <tr>
            <td>
                <h3>GASTOS</h2>
            </td>
        </tr>
    
        <table class="articulos-table">
            <tr>
                <th style="width: 20%">
                    <p>CLIENTE</p>
                </th>
                @foreach ($venta as $ventas)
                <th id="{{ trim($ventas->sales_id) }}">
                    <p>{{ $ventas->customers_key }} - {{ $ventas->customers_businessName }}</p>
                </th>
            
                @endforeach
            </tr>
                <tr>
                    <th>
                        @foreach ($arrayConceptos as $concepto)
                            <p style="font-weight: normal;">{{ $concepto }}</p>
                        @endforeach
                    </th>
                    @foreach ($venta as $folio)
                    <th>
                        @foreach ($arrayConceptos as $concepto)
                            @if(array_key_exists($concepto.'-'.$folio->sales_movementID, $arrayConceptosFactura))
                                <p style="font-weight: normal;">${{ number_format($arrayConceptosFactura[$concepto.'-'.$folio->sales_movementID], 2) }}</p>
                            @else
                                <p style="font-weight: normal;">-</p>
                            @endif
                        @endforeach
                    </th>
                    @endforeach
                </tr>
                <tr>
                    <th>
                        <p>TOTAL DE GASTOS</p>
                    </th>
                    @foreach ($venta as $ventas)
                    <th id="{{ trim($ventas->sales_id) }}">
                        <?php $totalGastos = 0 ?>
                        @foreach ($gastos as $gasto)
                        
                        @if ($gasto->expenses_antecedentsName === $ventas->sales_movementID)
                        <?php $totalGastos += ($gasto->expensesDetails_total * $gasto->expenses_typeChange) ?>
                        @endif
                        @endforeach
                        <p>${{ number_format($totalGastos, 2) }}</p>
                        <?php 
                            $totalGastosArray[$ventas->sales_id] = $totalGastos;
                        ?>
                    @endforeach

                </tr>
  
            <tr>
                <th style="border: 1px solid black;"> 
                    <p>GANANCIA/PERDIDA</p>
                </th>
                @foreach ($venta as $ventas)
                <th style="border: 1px solid black;">
                    <?php 
                        $totalSaleCostArray = []; // Inicializa el arreglo para almacenar los costos totales

                        foreach ($detalleVentas as $detalle) {
                            if (isset($detalle->salesDetails_saleCost)) {
                                if (!isset($totalSaleCostArray[$detalle->salesDetails_saleID])) {
                                    $totalSaleCostArray[$detalle->salesDetails_saleID] = 0; // Inicializa el costo total para cada venta
                                }
                                $totalSaleCostArray[$detalle->salesDetails_saleID] += $detalle->salesDetails_saleCost; // Sumar el costo de venta
                            }
                        }

                        $ganancia = $totalVentaArray[$ventas->sales_id] - $totalGastosArray[$ventas->sales_id] - $totalSaleCostArray[$ventas->sales_id];
                    ?>
                    <p>${{ number_format($ganancia, 2) }}</p>
                </th>
                @endforeach

            </tr>
        </table>
            <br>
    </table>
    </body>
    
    </html>