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
        $articulos_array = array();
        $articulosSeries_array = array();

        foreach ($inventarios as $inventario) {
            $articulos_array[] = $inventario['assistantUnit_account'] . '-' . $inventario['articles_descript'];
        }

        $articulos_array = array_unique($articulos_array);

        foreach ($inventarios as $inventario) {
            $articulosSeries_array[] = $inventario['assistantUnit_account'] . '-' . $inventario['articles_descript'] . '-' . $inventario['lotSeries_lotSerie'];
        }

        
        ?>

            @foreach ($articulos_array as $articulo)
            <?php
            $articuloCompras = array();
            $articuloCompras['articles_key'] = explode('-', $articulo)[0];
            $articuloCompras['articles_descript'] = explode('-', $articulo)[1];
            $articuloCompras['inventarios'] = array();
            $articuloCompras['lotSeries_lotSerie'] = '';
            foreach ($inventarios as $inventario) {
            if ($inventario['assistantUnit_account'] . '-' . $inventario['articles_descript'] == $articulo) {
                $articuloCompras['inventarios'][] = $inventario;
            }
            }
            ?>
            @if ($nameSerie == 'Si')
                @foreach ($articulosSeries_array as $articuloSerie)
                    <?php
                    $articuloComprasSerie = array();
                    $articuloComprasSerie['articles_key'] = explode('-', $articuloSerie)[0];
                    $articuloComprasSerie['articles_descript'] = explode('-', $articuloSerie)[1];
                    $articuloComprasSerie['inventarios'] = array();
                    $articuloComprasSerie['lotSeries_lotSerie'] = explode('-', $articuloSerie)[2];
                    foreach ($inventarios as $inventario) {
                        if ($inventario['assistantUnit_account'] . '-' . $inventario['articles_descript'] . '-' . $inventario['lotSeries_lotSerie'] == $articuloSerie) {
                            $articuloComprasSerie['inventarios'][] = $inventario;
                        }
                    }
                    ?>
                    @if ($articuloComprasSerie['articles_key'] == $articuloCompras['articles_key'] && $articuloComprasSerie['articles_descript'] == $articuloCompras['articles_descript'])
                        @if (count($articuloComprasSerie['inventarios']) > 0)
                            <?php $articuloCompras = $articuloComprasSerie; ?>
                        @endif
                    @endif
                @endforeach
            @endif

            @if (count($articuloCompras['inventarios']) > 0)
                <tr>
                    <td>
                        <p> <strong> {{ $articuloCompras['articles_key'] }} - {{ $articuloCompras['articles_descript'] }} </strong> 
                            @if ($nameSerie == 'Si')
                                {{ $articuloCompras['lotSeries_lotSerie'] }}
                            @endif

                        </p>
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
                    $decimal = 0;
                    foreach ($articuloCompras['inventarios'] as $key => $inventario) {
                        if ($inventario['units_decimalVal'] > $decimal) {
                            $decimal = $inventario['units_decimalVal'];
                        }
                    }
                    
                    ?>
                    @foreach ($articuloCompras['inventarios'] as $key => $inventario)       
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td style="text-align: left;">{{ $inventario['assistantUnit_movement'] }}
                                {{ $inventario['assistantUnit_movementID'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($inventario['created_at'])->format('d/m/Y') }}</td>
                            <td style="text-align: right;">
                                {{ number_format($inventario['assistantUnit_chargeUnit'], $decimal) }}</td>
                            <td style="text-align: right;">
                                {{ number_format($inventario['assistantUnit_paymentUnit'], $decimal) }}</td>
                            <?php
                            
                            $entradas += $inventario['assistantUnit_chargeUnit'];
                            $salidas += $inventario['assistantUnit_paymentUnit'];
                            
                            $decimal = 0;
                            foreach ($articuloCompras['inventarios'] as $key => $inventario) {
                                if ($inventario['units_decimalVal'] > $decimal) {
                                    $decimal = $inventario['units_decimalVal'];
                                }
                            }
                            
                            ?>
                            <td style="text-align: right;">{{ number_format($entradas - $salidas, $decimal) }}
                            </td>
    
    
    
                        </tr>
                    @endforeach
                </table>

            @endif

        @endforeach

    </table>

    




        

</body>

</html>

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
        $articulos_array = array();
        $articulosSeries_array = array();
        

        foreach ($inventarios as $inventario) {
            $articulos_array[] = $inventario['assistantUnit_account'] . '-' . $inventario['articles_descript'];
        }

        $articulos_array = array_unique($articulos_array);

        

        foreach ($inventarios as $inventario) {
            if ($inventario['lotSeries_lotSerie'] != null) {
                $articulosSeries_array[] = $inventario['assistantUnit_account'] . '-' . $inventario['articles_descript'] . '-' . $inventario['lotSeries_lotSerie'];
            }
        }

        $articulosSeries_array = array_unique($articulosSeries_array);

        
        
        ?>

            @foreach ($articulos_array as $articulo)
            <?php
            $articuloCompras = array();
            $articuloCompras['articles_key'] = explode('-', $articulo)[0];
            $articuloCompras['articles_descript'] = explode('-', $articulo)[1];
            $articuloCompras['lotSeries_lotSerie'] = '';
            $articuloCompras['inventarios'] = array();
            foreach ($inventarios as $inventario) {
                if ($inventario['assistantUnit_account'] == $articuloCompras['articles_key'] && $inventario['articles_descript'] == $articuloCompras['articles_descript']) {
                    $articuloCompras['inventarios'][] = $inventario;
                }
            }
            dd($articuloCompras['inventarios']);
            ?>
            @if (count($articuloCompras['inventarios']) > 0)
            <tr>
                <td>
                    <p> <strong> {{ $articuloCompras['articles_key'] }} - {{ $articuloCompras['articles_descript'] }} </strong> </p>
                

            @if ($nameSerie == 'Si')
            <p> <strong>Series: </strong> </p>
                @foreach ($articulosSeries_array as $articuloSerie)
                    <?php
                         $articuloComprasSerie = array();
                         $articuloComprasSerie['articles_key'] = explode('-', $articuloSerie)[0];
                         $articuloComprasSerie['articles_descript'] = explode('-', $articuloSerie)[1];
                         $articuloComprasSerie['lotSeries_lotSerie'] = explode('-', $articuloSerie)[2];
                    
                    ?>
                    @if ($articuloComprasSerie['articles_key'] == $articuloCompras['articles_key'] && $articuloComprasSerie['articles_descript'] == $articuloCompras['articles_descript'])
                    
                     <strong> {{ $articuloComprasSerie['lotSeries_lotSerie'] }} </strong>
                    
                        
                    @endif
                @endforeach
                
            @endif

                            

                        </p>
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
                    $decimal = 0;
                    foreach ($articuloCompras['inventarios'] as $key => $inventario) {
                        if ($inventario['units_decimalVal'] > $decimal) {
                            $decimal = $inventario['units_decimalVal'];
                        }
                    }
                    
                    ?>
                    @foreach ($articuloCompras['inventarios'] as $key => $inventario)  
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td style="text-align: left;">{{ $inventario['assistantUnit_movement'] }}
                                {{ $inventario['assistantUnit_movementID'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($inventario['created_at'])->format('d/m/Y') }}</td>
                            <td style="text-align: right;">
                                {{ number_format($inventario['assistantUnit_chargeUnit'], $decimal) }}</td>
                            <td style="text-align: right;">
                                {{ number_format($inventario['assistantUnit_paymentUnit'], $decimal) }}</td>
                            <?php
                            
                            $entradas += $inventario['assistantUnit_chargeUnit'];
                            $salidas += $inventario['assistantUnit_paymentUnit'];
                            
                            $decimal = 0;
                            foreach ($articuloCompras['inventarios'] as $key => $inventario) {
                                if ($inventario['units_decimalVal'] > $decimal) {
                                    $decimal = $inventario['units_decimalVal'];
                                }
                            }
                            
                            ?>
                            <td style="text-align: right;">{{ number_format($entradas - $salidas, $decimal) }}
                            </td>
    
    
    
                        </tr>
                    @endforeach
                </table>

            @endif

        @endforeach

    </table>

</body>

</html>

