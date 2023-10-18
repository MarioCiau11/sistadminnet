<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KARDEX DE INVENTARIOS - GENERAL</title>

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
                <h3><strong>KARDEX DE INVENTARIOS - GENERAL </strong></h3>
                <h3><strong>{{ $nameFecha }}</strong></h3>
            </td>

        </tr>

    </table>
    <table class="articulos-table">
        <tr>
            <th>
                <p>#</p>
            </th>
            <th>
                <p>CLAVE</p>
            </th>
            <th>
                <p>NOMBRE</p>
            </th>
            <th>
                <p>INVENTARIO ANTERIOR</p>
            </th>
            <th>
                <p>COMPRAS</p>
            </th>
            @foreach ($clientes as $cliente)
                <th>
                    <p>{{ $cliente }}</p>
                </th>
            @endforeach
            <th>
                <p>TOTAL INVENTARIO</p>
            </th>
            <th>
                <p>PRECIO</p>
            </th>
            <th>
                <p>TOTAL</p>
            </th>
        </tr>

        <?php
        $sumaInventario = [];
        $sumaCompras = [];
        $sumaVentas = [];
        $sumaClientesArray = [];
        $promedioCosto = [];
        $contador = [];
        //  DD($precios);
        foreach ($inventario_array as $inv) {
            if (!array_key_exists($inv['assistantUnit_account'], $sumaInventario)) {
                $sumaInventario[$inv['assistantUnit_account']] = $inv->assistantUnit_chargeUnit;
            } else {
                $sumaInventario[$inv['assistantUnit_account']] += $inv->assistantUnit_chargeUnit;
            }
        }
        
        foreach ($compras as $inv) {
            if (!array_key_exists($inv['assistantUnit_account'], $sumaCompras)) {
                $sumaCompras[$inv['assistantUnit_account']] = $inv->assistantUnit_chargeUnit;
            } else {
                $sumaCompras[$inv['assistantUnit_account']] += $inv->assistantUnit_chargeUnit;
            }
        }
        
        foreach ($clientes as $key => $cliente) {
            $clienteD = explode('-', $cliente);
            foreach ($ventas as $inv) {
                if ($inv['asssistantUnit_costumer'] == $clienteD[0]) {
                    if (!array_key_exists($inv['assistantUnit_account'] . '-' . $clienteD[1], $sumaVentas)) {
                        $sumaVentas[$inv['assistantUnit_account'] . '-' . $clienteD[1]] = $inv->assistantUnit_paymentUnit;
                    } else {
                        $sumaVentas[$inv['assistantUnit_account'] . '-' . $clienteD[1]] += $inv->assistantUnit_paymentUnit;
                    }
        
                    if (!array_key_exists($clienteD[1], $sumaClientesArray)) {
                        $sumaClientesArray[$clienteD[1]] = $inv->assistantUnit_paymentUnit;
                    } else {
                        $sumaClientesArray[$clienteD[1]] += $inv->assistantUnit_paymentUnit;
                    }
                }
            }
        }
        
        foreach ($precios as $inv) {
            foreach ($inv as $key => $value) {
                // dd($value);
                if (!array_key_exists($value['salesDetails_article'], $promedioCosto)) {
                    $promedioCosto[$value['salesDetails_article']] = $value->salesDetails_unitCost;
                    $contador[$value['salesDetails_article']] = 1;
                } else {
                    $promedioCosto[$value['salesDetails_article']] += $value->salesDetails_unitCost;
                    $contador[$value['salesDetails_article']] += 1;
                }
            }
        }
        $invCompraSuma = 0;
        $sumaClientesVentas = 0;
        
        $contador2 = 1;
        $totalFin = 0;
        $totalFin2 = 0;
        
        ?>


        @foreach ($articulos as $key => $articulo)
            <?php
            
            $decimal = 0;
            
            foreach ($articulos as $key => $articulo) {
                if ($articulo->units_decimalVal > $decimal) {
                    $decimal = $articulo->units_decimalVal;
                }
            }
            
            ?>
            <tr>
                <td>
                    <p>{{ $contador2 }}</p>
                </td>
                <td>
                    <p>{{ $articulo->assistantUnit_account }}</p>
                </td>
                <td>
                    <p>{{ $articulo->articles_descript }}</p>
                </td>
                <td>
                    <p style="text-align: right;">
                        {{ number_format(array_key_exists($articulo->assistantUnit_account, $sumaInventario) ? $sumaInventario[$articulo->assistantUnit_account] : 0, $decimal) }}
                    </p>
                </td>
                <td>
                    <p style="text-align: right;">
                        {{ number_format(array_key_exists($articulo->assistantUnit_account, $sumaCompras) ? $sumaCompras[$articulo->assistantUnit_account] : 0, $decimal) }}
                    </p>
                </td>
                <?php $sumaClientes = 0; ?>
                @foreach ($clientes as $cliente)
                    <td class="clientes">
                        <?php $cliente = explode('-', $cliente);
                        ?>
                        <p style="text-align: right;">
                            {{ array_key_exists($articulo->assistantUnit_account . '-' . $cliente[1], $sumaVentas) ? number_format($sumaVentas[$articulo->assistantUnit_account . '-' . $cliente[1]], 2) : 0 }}
                        </p>
                        <?php $sumaClientes += array_key_exists($articulo->assistantUnit_account . '-' . $cliente[1], $sumaVentas) ? $sumaVentas[$articulo->assistantUnit_account . '-' . $cliente[1]] : 0;
                        $sumaClientesVentas = $sumaClientes;
                        ?>
                    </td>
                @endforeach
                <?php $sumaClientes = 0;
                $sumaTotalInv = [];
                $precio = [];
                ?>
                <td>
                    <p class="totalInv" style="text-align: right;">
                        <?php $invCompraSuma = (array_key_exists($articulo->assistantUnit_account, $sumaInventario) ? $sumaInventario[$articulo->assistantUnit_account] : 0) + (array_key_exists($articulo->assistantUnit_account, $sumaCompras) ? $sumaCompras[$articulo->assistantUnit_account] : 0);
                        
                        $totalInvFin = $sumaTotalInv[$articulo->assistantUnit_account] = $invCompraSuma - $sumaClientesVentas;
                        
                        ?>
                        {{ $totalInvFin }}
                        <?php $totalFin2 += $totalInvFin; ?>
                    </p>
                </td>
                <td>
                    <?php
                    $precio[$articulo->assistantUnit_account] = (array_key_exists($articulo->assistantUnit_account, $promedioCosto) ? $promedioCosto[$articulo->assistantUnit_account] : 0) / (array_key_exists($articulo->assistantUnit_account, $contador) ? $contador[$articulo->assistantUnit_account] : 1);
                    ?>
                    <p style="text-align: right;">
                        ${{ number_format($precio[$articulo->assistantUnit_account], 2, '.', ',') }}</p>
                </td>
                <td>
                    <?php
                    $total = $sumaTotalInv[$articulo->assistantUnit_account] * $precio[$articulo->assistantUnit_account];
                    
                    ?>
                    <p style="text-align: right;">${{ number_format($total, 2, '.', ',') }}</p>
                    <?php $totalFin += $total; ?>
                </td>
            </tr>
            <?php $contador2++; ?>
        @endforeach
        <?php
        //   dd($sumaClientesArray);
        ?>
        <tr>
            <th>
                <p></p>
            </th>
            <th>
                <p></p>
            </th>
            <th>
                <p></p>
            </th>
            <th>
                <p style="text-align: right;"><?php
                $valor = array_sum($sumaInventario);
                ?>
                    {{ number_format($valor, $decimal) }}

                </p>
            </th>
            <th>
                <p style="text-align: right;"><?php
                $valor2 = array_sum($sumaCompras);
                ?>
                    {{ number_format($valor2, $decimal) }}
                </p>
            </th>
            @foreach ($clientes as $cliente)
                <th>
                    <p style="text-align: right;">
                        <?php $cliente = explode('-', $cliente);
                        ?>
                        {{ array_key_exists($cliente[1], $sumaClientesArray) ? $sumaClientesArray[$cliente[1]] : 0 }}
                    </p>

                </th>
            @endforeach
            <th>
                <p style="text-align: right;">
                    {{ $totalFin2 }}
                </p>
            </th>
            <th>
                <p>TOTAL</p>
            </th>
            <th>
                <p style="text-align: right;">{{ '$' . number_format($totalFin, 2, '.', ',') }}</p>
            </th>
        </tr>

    </table>

</body>


</html>
