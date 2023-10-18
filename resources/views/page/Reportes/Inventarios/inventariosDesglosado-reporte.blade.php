<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KARDEX DE INVENTARIOS - DESGLOSE</title>

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
                <h3><strong>KARDEX DE INVENTARIOS - DESGLOSE </strong></h3>
                <h3><strong>{{ $fecha }}</strong></h3>
            </td>
        </tr>

    </table>
    <table class="informacion-proveedor3">

        <?php
        $articulos_array = [];
        $articulosSeries_array = [];
        //Primero Vemos si tiene series
        foreach ($inventarios as $inventario) {
            if ($inventario['articles_type'] == 'Serie') {
                $idArticulo = $inventario['assistantUnit_account'];
                $descripcionArticulo = $inventario['articles_descript'];
                if (isset($inventario['series'])) {
                    foreach ($inventario['series'] as $key => $serie) {
                        $miSerie = '';
        
                        if (isset($serie['lotSeriesMov2_lotSerie'])) {
                            $miSerie = $serie['lotSeriesMov2_lotSerie'];
                        }
        
                        if (isset($serie['lotSeriesMov_lotSerie'])) {
                            $miSerie = $serie['lotSeriesMov_lotSerie'];
                        }
        
                        if (isset($serie['delSeriesMov2_lotSerie'])) {
                            $miSerie = $serie['delSeriesMov2_lotSerie'];
                        }
        
                        if (isset($serie['delSeriesMov_lotSerie'])) {
                            $miSerie = $serie['delSeriesMov_lotSerie'];
                        }
        
                        $articulosSeries_array[$idArticulo . '-' . $miSerie] = $idArticulo . '-' . $inventario['articles_descript'] . '-' . $miSerie;
                    }
                }
            }
        }
        
        $articulosSeries_array = array_unique($articulosSeries_array);
        
        foreach ($inventarios as $inventario) {
            //Validamos que el articulo de inventario sea de tipo serie y sea de la key
            if ($inventario['articles_type'] == 'Serie') {
                $idArticulo = $inventario['assistantUnit_account'];
                $descripcionArt = $inventario['articles_descript'];
        
                if (isset($inventario['series'])) {
                    foreach ($inventario['series'] as $key => $serie) {
                        $miSerie = '';
        
                        if (isset($serie['lotSeriesMov2_lotSerie'])) {
                            $miSerie = $serie['lotSeriesMov2_lotSerie'];
                        }
        
                        if (isset($serie['lotSeriesMov_lotSerie'])) {
                            $miSerie = $serie['lotSeriesMov_lotSerie'];
                        }
        
                        if (isset($serie['delSeriesMov2_lotSerie'])) {
                            $miSerie = $serie['delSeriesMov2_lotSerie'];
                        }
        
                        if (isset($serie['delSeriesMov_lotSerie'])) {
                            $miSerie = $serie['delSeriesMov_lotSerie'];
                        }
        
                        $articulos_array[] = $inventario['assistantUnit_account'] . '-' . $inventario['articles_descript'] . '-' . $miSerie;
                    }
                }
            } else {
                $articulos_array[] = $inventario['assistantUnit_account'] . '-' . $inventario['articles_descript'];
            }
        }
        
        $articulos_array = array_unique($articulos_array);
        ?>

        @foreach ($articulos_array as $articulo)
            <?php
            $articuloCompras = [];
            foreach ($inventarios as $inventario) {
                if ($inventario['articles_type'] == 'Serie') {
                    $idArticulo = $inventario['assistantUnit_account'];
                    $descripcionArt = $inventario['articles_descript'];
            
                    if (isset($inventario['series'])) {
                        foreach ($inventario['series'] as $key => $serie) {
                            $miSerie = '';
            
                            if (isset($serie['lotSeriesMov2_lotSerie'])) {
                                $miSerie = $serie['lotSeriesMov2_lotSerie'];
                            }
            
                            if (isset($serie['lotSeriesMov_lotSerie'])) {
                                $miSerie = $serie['lotSeriesMov_lotSerie'];
                            }
            
                            if (isset($serie['delSeriesMov2_lotSerie'])) {
                                $miSerie = $serie['delSeriesMov2_lotSerie'];
                            }
            
                            if (isset($serie['delSeriesMov_lotSerie'])) {
                                $miSerie = $serie['delSeriesMov_lotSerie'];
                            }
            
                            $articuloCompras[$inventario['assistantUnit_account'] . '-' . $inventario['articles_descript'] . '-' . $miSerie][] = $inventario;
                        }
                    }
                } else {
                    $articuloCompras[$inventario['assistantUnit_account'] . '-' . $inventario['articles_descript']][] = $inventario;
                }
            }

            if ($nameExistencia == "Si") {
                $lastEntry = end($articuloCompras[$articulo]);
                $total = $lastEntry['assistantUnit_chargeUnit'] - $lastEntry['assistantUnit_paymentUnit'];
            
                if ($total <= 0) {
                    continue; // Saltar a la siguiente iteración si el total es 0 o negativo
                }
            }
            ?>
            @if (count($articuloCompras) > 0)
                <tr>
                    <td>
                        <p> <strong> {{ $articulo }} </strong> </p>
                    </td>
                </tr>

                <table class="articulos-table2">
                    <tr>
                        <th>
                            <p>#</p>
                        </th>
                        <th style="width: 150px">
                            <p>MOVIMIENTO</p>
                        </th>
                        <th>
                            <p>FECHA</p>
                        </th>
                        <th>
                            <p>ENTRADAS</p>
                        </th>
                        <th>
                            <p>SALIDAS</p>
                        </th>
                        <th>
                            <p>SALDO</p>
                        </th>
                    </tr>

                    <?php
                    
                    $entradas = 0;
                    $salidas = 0;
                    $total = 0;
                    
                    $chargeUnit = strlen(floatVal($inventario['assistantUnit_chargeUnit'])) - strrpos($inventario['assistantUnit_chargeUnit'], '.') - 1;
                    $paymentUnit = strlen(floatVal($inventario['assistantUnit_paymentUnit'])) - strrpos($inventario['assistantUnit_paymentUnit'], '.') - 1;
                    
                    ?>

                    @foreach ($articuloCompras[$articulo] as $key => $inv)
                        <?php
                        if ($inv['articles_type'] == 'Serie') {
                            $entrada = $inv['assistantUnit_chargeUnit'] != null ? 1 : 0;
                            $salida = $inv['assistantUnit_paymentUnit'] != null ? 1 : 0;
                        } else {
                            $entrada = $inv['assistantUnit_chargeUnit'];
                            $salida = $inv['assistantUnit_paymentUnit'];
                        }
                        ?>

                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td style="text-align: left;">{{ $inv['assistantUnit_movement'] }}
                                {{ $inv['assistantUnit_movementID'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($inv['created_at'])->format('d/m/Y') }}</td>
                            <td style="text-align: right;">
                                {{ number_format($entrada, $chargeUnit) }}</td>
                            <td style="text-align: right;">
                                {{ number_format($salida, $paymentUnit) }}</td>

                            <?php
                            if ($inv['articles_type'] == 'Serie') {
                                $entradas += $inv['assistantUnit_chargeUnit'] != null ? 1 : 0;
                                $salidas += $inv['assistantUnit_paymentUnit'] != null ? 1 : 0;
                            } else {
                                $entradas += $inv['assistantUnit_chargeUnit'];
                                $salidas += $inv['assistantUnit_paymentUnit'];
                            }
                            $total = $entradas - $salidas;
                            ?>
                            <td style="text-align: right;">{{ number_format($total, $chargeUnit) }}
                            </td>
                        </tr>
                    @endforeach

                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="text-align: right;"><strong>Ultimo Saldo:</strong></td>
                        <td style="text-align: right;">{{ number_format($total, $chargeUnit) }}</td>
                    </tr>
                </table>
            @endif
        @endforeach

    </table>

</body>

</html>
