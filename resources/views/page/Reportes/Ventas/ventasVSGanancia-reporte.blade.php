<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VENTAS VS GANANCIA SUCURSAL/POR AGRUPADOR</title>

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

        </tr>

    </table>


    <table class="ancho">
        <tr>
            <td>
                <h3><strong>VENTAS VS GANANCIA SUCURSAL/POR AGRUPADOR</strong></h3>
                <h3><strong>{{ $fecha }}</strong></h3>

            </td>
        </tr>

    </table>


    <table class="informacion-prov2">

        @foreach ($sucursalesVentas as $sucursalesPorVenta)
            <tr>

                <td>
                    <p>{{ $sucursalesPorVenta['branchOffices_name'] }}</p>
                </td>

            </tr>


            <table class="informacion-prov2">
                <tr>
                    <td>
                        @if ($agrupador == 'Categoría')
                            <p>VENTAS POR CATEGORÍA</p>
                        @elseif($agrupador == 'Grupo')
                            <p>VENTAS POR GRUPO</p>
                        @elseif($agrupador == 'Familia')
                            <p>VENTAS POR FAMILIA</p>
                        @endif

                    </td>
                </tr>
                <table class="articulos-table" style="margin-top: 0px;">
                    <tr>
                    @if ($agrupador == 'Categoría')
                        <th>CATEGORÍA</th>
                    @elseif($agrupador == 'Grupo')
                        <th>GRUPO</th>
                    @elseif($agrupador == 'Familia')
                        <th>FAMILIA</th>
                    @endif
                        <th>
                            <p>VENTAS</p>
                        </th>
                    </tr>

                    <?php
                    $sumaTotal = [];
                    $totalVentas = 0;

                    // dd($sucursalesPorVenta['ventas']);
                    
                    foreach ($sucursalesPorVenta['ventas'] as $ventas) {
                        if ($agrupador == 'Categoría' && $ventas['articles_category'] != null) {
                            if (!array_key_exists($ventas['articles_category'] . '--' . $ventas['branchOffices_name'], $sumaTotal)) {
                                $sumaTotal[$ventas['articles_category'] . '--' . $ventas['branchOffices_name']] = $ventas['salesDetails_total'] * $ventas['sales_typeChange'];
                            } else {
                                $sumaTotal[$ventas['articles_category'] . '--' . $ventas['branchOffices_name']] += $ventas['salesDetails_total'] * $ventas['sales_typeChange'];
                            }
                        } elseif ($agrupador == 'Grupo' && $ventas['articles_group'] != null) {
                            if (!array_key_exists($ventas['articles_group'] . '--' . $ventas['branchOffices_name'], $sumaTotal)) {
                                $sumaTotal[$ventas['articles_group'] . '--' . $ventas['branchOffices_name']] = $ventas['salesDetails_total'] * $ventas['sales_typeChange'];
                            } else {
                                $sumaTotal[$ventas['articles_group'] . '--' . $ventas['branchOffices_name']] += $ventas['salesDetails_total'] * $ventas['sales_typeChange'];
                            }
                        } elseif ($agrupador == 'Familia' && $ventas['articles_family'] != null) {
                            if (!array_key_exists($ventas['articles_family'] . '--' . $ventas['branchOffices_name'], $sumaTotal)) {
                                $sumaTotal[$ventas['articles_family'] . '--' . $ventas['branchOffices_name']] = $ventas['salesDetails_total'] * $ventas['sales_typeChange'];
                            } else {
                                $sumaTotal[$ventas['articles_family'] . '--' . $ventas['branchOffices_name']] += $ventas['salesDetails_total'] * $ventas['sales_typeChange'];
                            }
                        }
                    }
                    
                    foreach ($sucursalesPorVenta['ventas'] as $ventas) {
                        if ($agrupador == 'Categoría' && $ventas['articles_category'] != null) {
                            $totalVentas += $ventas['salesDetails_total'] * $ventas['sales_typeChange'];
                        } elseif ($agrupador == 'Grupo' && $ventas['articles_group'] != null) {
                            $totalVentas += $ventas['salesDetails_total'] * $ventas['sales_typeChange'];
                        } elseif ($agrupador == 'Familia' && $ventas['articles_family'] != null) {
                            $totalVentas += $ventas['salesDetails_total'] * $ventas['sales_typeChange'];
                        }
                    }
                    
                    $keyInformacion = array_keys($sumaTotal);
                    
                    ?>

                    @foreach ($keyInformacion as $referencia)
                        <?php
                        $key = explode('--', $referencia)[0];
                        ?>

                        <tr>
                            @if ($agrupador == 'Categoría' && $ventas['articles_category'] != null)
                                <td style="text-align:left">{{ $key }}</td>
                            @elseif($agrupador == 'Grupo' && $ventas['articles_group'] != null)
                                <td style="text-align:left">{{ $key }}</td>
                            @elseif($agrupador == 'Familia' && $ventas['articles_family'] != null)
                                <td style="text-align:left">{{ $key }}</td>
                            @endif

                            @if ($agrupador == 'Categoría' && $ventas['articles_category'] != null)
                                <td style="text-align:right">$ {{ number_format(array_key_exists($referencia, $sumaTotal) ? $sumaTotal[$referencia] : 0, 2) }}
                                </td>
                            @elseif($agrupador == 'Grupo' && $ventas['articles_group'] != null)
                                <td style="text-align:right">$ {{ number_format(array_key_exists($referencia, $sumaTotal) ? $sumaTotal[$referencia] : 0, 2) }}
                                </td>
                            @elseif($agrupador == 'Familia' && $ventas['articles_family'] != null)
                                <td style="text-align:right">$ {{ number_format(array_key_exists($referencia, $sumaTotal) ? $sumaTotal[$referencia] : 0, 2) }}
                                </td>
                            @endif

                        </tr>
                        <?php
                        //  dd($keyInformacion, $sumaTotal);
                        
                        ?>
                    @endforeach


                    <tr>
                        <th style="text-align: right;">
                            <p>Ventas Totales</p>
                        </th>
                        <th style="text-align:right">
                            <p>$ {{ number_format($totalVentas, 2) }}</p>

                        </th>
                    </tr>
                </table>
            </table>

            <table class="informacion-prov2">
                <tr>
                    <td>
                        @if ($agrupador == 'Categoría')
                            <p>GANANCIA POR CATEGORÍA</p>
                        @elseif($agrupador == 'Grupo')
                            <p>GANANCIA POR GRUPO</p>
                        @elseif($agrupador == 'Familia')
                            <p>GANANCIA POR FAMILIA</p>
                        @endif

                    </td>
                </tr>
                <table class="articulos-table" style="margin-top: 0px;">
                    <tr>
                        @if ($agrupador == 'Categoría')
                        <th>CATEGORÍA</th>
                    @elseif($agrupador == 'Grupo')
                        <th>GRUPO</p>
                    @elseif($agrupador == 'Familia')
                        <th>FAMILIA</th>
                    @endif
                        <th>
                            <p>GANANCIA</p>
                        </th>
                    </tr>

                    <?php
                    $sumaCosto = [];
                    $sumaTotalCosto = [];
                    $sumaServicio = [];
                    $totalCosto = 0;
                    $ganancia = 0;
                    
                    // dd($sucursalesPorVenta['ventas']);
                    foreach ($sucursalesPorVenta['ventas'] as $ventas) {
                        // dd($ventas);
                        $costo = 0;
                        if ($agrupador == 'Categoría' && $ventas['articles_category'] != null) {
                            $costo = $ventas['salesDetails_saleCost'] == null ? $ventas['salesDetails_unitCost'] : $ventas['salesDetails_saleCost'];
                    
                            if (!array_key_exists($ventas['articles_category'] . '--' . $ventas['branchOffices_name'], $sumaCosto)) {
                                $sumaCosto[$ventas['articles_category'] . '--' . $ventas['branchOffices_name']] = $costo;
                                if($ventas['salesDetails_type'] == 'Servicio'){
                                    if (!array_key_exists($ventas['articles_category'] . '--' . $ventas['branchOffices_name'], $sumaServicio)) {
                                    $sumaServicio[$ventas['articles_category'] . '--' . $ventas['branchOffices_name']] = $ventas['salesDetails_unitCost'];
                                    }else{
                                        $sumaServicio[$ventas['articles_category'] . '--' . $ventas['branchOffices_name']] += $ventas['salesDetails_unitCost'];
                                    }
                                }
                                // dd($sumaCosto);
                               
                            } else {
                                $sumaCosto[$ventas['articles_category'] . '--' . $ventas['branchOffices_name']] += $costo;
                                if($ventas['salesDetails_type'] == 'Servicio'){
                                    if (!array_key_exists($ventas['articles_category'] . '--' . $ventas['branchOffices_name'], $sumaServicio)) {
                                    $sumaServicio[$ventas['articles_category'] . '--' . $ventas['branchOffices_name']] = $ventas['salesDetails_unitCost'];
                                    }else{
                                        $sumaServicio[$ventas['articles_category'] . '--' . $ventas['branchOffices_name']] += $ventas['salesDetails_unitCost'];
                                    }
                                }
                            }
                        } elseif ($agrupador == 'Grupo' && $ventas['articles_group'] != null) {
                            $costo = $ventas['salesDetails_saleCost'] == null ? $ventas['salesDetails_unitCost'] : $ventas['salesDetails_saleCost'];
                            if (!array_key_exists($ventas['articles_group'] . '--' . $ventas['branchOffices_name'], $sumaCosto)) {
                                $sumaCosto[$ventas['articles_group'] . '--' . $ventas['branchOffices_name']] = $costo;
                                if($ventas['salesDetails_type'] == 'Servicio'){
                                    if (!array_key_exists($ventas['articles_group'] . '--' . $ventas['branchOffices_name'], $sumaServicio)) {
                                    $sumaServicio[$ventas['articles_group'] . '--' . $ventas['branchOffices_name']] = $ventas['salesDetails_unitCost'];
                                    }else{
                                        $sumaServicio[$ventas['articles_group'] . '--' . $ventas['branchOffices_name']] += $ventas['salesDetails_unitCost'];
                                    }
                                }
                            } else {
                                $sumaCosto[$ventas['articles_group'] . '--' . $ventas['branchOffices_name']] += $costo;
                                if($ventas['salesDetails_type'] == 'Servicio'){
                                    if (!array_key_exists($ventas['articles_group'] . '--' . $ventas['branchOffices_name'], $sumaServicio)) {
                                    $sumaServicio[$ventas['articles_group'] . '--' . $ventas['branchOffices_name']] = $ventas['salesDetails_unitCost'];
                                    }else{
                                        $sumaServicio[$ventas['articles_group'] . '--' . $ventas['branchOffices_name']] += $ventas['salesDetails_unitCost'];
                                    }
                                }
                            }
                        } elseif ($agrupador == 'Familia' && $ventas['articles_family'] != null) {
                            $costo = $ventas['salesDetails_saleCost'] == null ? $ventas['salesDetails_unitCost'] : $ventas['salesDetails_saleCost'];
                            if (!array_key_exists($ventas['articles_family'] . '--' . $ventas['branchOffices_name'], $sumaCosto)) {
                                $sumaCosto[$ventas['articles_family'] . '--' . $ventas['branchOffices_name']] = $costo;
                                if($ventas['salesDetails_type'] == 'Servicio'){
                                    if (!array_key_exists($ventas['articles_family'] . '--' . $ventas['branchOffices_name'], $sumaServicio)) {
                                    $sumaServicio[$ventas['articles_family'] . '--' . $ventas['branchOffices_name']] = $ventas['salesDetails_unitCost'];
                                    }else{
                                        $sumaServicio[$ventas['articles_family'] . '--' . $ventas['branchOffices_name']] += $ventas['salesDetails_unitCost'];
                                    }
                                }
                            } else {
                                $sumaCosto[$ventas['articles_family'] . '--' . $ventas['branchOffices_name']] += $costo;
                                if($ventas['salesDetails_type'] == 'Servicio'){
                                    if (!array_key_exists($ventas['articles_family'] . '--' . $ventas['branchOffices_name'], $sumaServicio)) {
                                    $sumaServicio[$ventas['articles_family'] . '--' . $ventas['branchOffices_name']] = $ventas['salesDetails_unitCost'];
                                    }else{
                                        $sumaServicio[$ventas['articles_family'] . '--' . $ventas['branchOffices_name']] += $ventas['salesDetails_unitCost'];
                                    }
                                }
                            }
                        }
                    }

                    // dd($sumaCosto, $sumaTotalCosto);
                    
                    foreach ($sucursalesPorVenta['ventas'] as $ventas) {
                        $costo = 0;
                        if ($agrupador == 'Categoría' && $ventas['articles_category'] != null) {
                            $costo = $ventas['salesDetails_saleCost'] == null ? $ventas['salesDetails_unitCost'] : $ventas['salesDetails_saleCost'];
                            $totalCosto += $costo;
                        } elseif ($agrupador == 'Grupo' && $ventas['articles_group'] != null) {
                            $costo = $ventas['salesDetails_saleCost'] == null ? $ventas['salesDetails_unitCost'] : $ventas['salesDetails_saleCost'];
                            $totalCosto += $costo;
                        } elseif ($agrupador == 'Familia' && $ventas['articles_family'] != null) {
                            $costo = $ventas['salesDetails_saleCost'] == null ? $ventas['salesDetails_unitCost'] : $ventas['salesDetails_saleCost'];
                            $totalCosto += $costo;
                        }
                    }

                    // dd($totalVentas, $totalCosto, $sumaCosto, $ganancia, $sumaServicio);
                    
                    ?>

                    @foreach ($keyInformacion as $referencia)
                        <?php
                        $key = explode('--', $referencia)[0];
                        $ganancia = $sumaTotal[$referencia] - $sumaCosto[$referencia];
                        // dd($sumaTotal[$referencia], $sumaCosto[$referencia], $ganancia);
                        ?>
                        <tr>
                            @if ($agrupador == 'Categoría' && $ventas['articles_category'] != null)
                                <td style="text-align:left">{{ $key }}</td>
                            @elseif($agrupador == 'Grupo' && $ventas['articles_group'] != null)
                                <td style="text-align:left">{{ $key }}</td>
                            @elseif($agrupador == 'Familia' && $ventas['articles_family'] != null)
                                <td style="text-align:left">{{ $key }}</td>
                            @endif

                            @if ($agrupador == 'Categoría' && $ventas['articles_category'] != null)
                                {{-- <td style="text-align:right">$ {{ number_format(array_key_exists($referencia, $sumaTotalCosto) ? $sumaTotalCosto[$referencia] : 0, 2) }}
                                </td> --}}
                                    <td style="text-align:right">$ {{ number_format(array_key_exists($referencia, $sumaServicio) ? $sumaServicio[$referencia] : $ganancia, 2) }}
                                    </td>
                        
                            @elseif($agrupador == 'Grupo' && $ventas['articles_group'] != null)
                                <td style="text-align:right">$ {{ number_format(array_key_exists($referencia, $sumaServicio) ? $sumaServicio[$referencia] : $ganancia, 2) }}
                                </td>
                            @elseif($agrupador == 'Familia' && $ventas['articles_family'] != null)
                                <td style="text-align:right">$ {{ number_format(array_key_exists($referencia, $sumaServicio) ? $sumaServicio[$referencia] : $ganancia, 2) }}
                                </td>
                            @endif

                        </tr>
                    @endforeach

                    <tr>
                        <th style="text-align: right;">
                            <p>Ganancias Totales</p>
                        </th>
                        <?php
                        $sumaServicio = array_sum($sumaServicio);
                        $productos = array_sum($sumaTotal)-array_sum($sumaCosto);
                        // dd($sumaServicio, $ganancia);
                        ?>
                        <th style="text-align:right">
                            <p>$ {{ number_format($sumaServicio+$productos, 2) }}</p>

                        </th>
                    </tr>
                </table>
            </table>
        @endforeach
    </table>

</body>

</html>
