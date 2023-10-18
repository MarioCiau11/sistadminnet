<table class="ancho">
    <tr>
        <td colspan="4" style="text-align: center; font-weight:bold; padding:5px">
            <p><strong>VENTAS VS GANANCIA SUCURSAL/POR AGRUPADOR</strong></p>
        </td>
    </tr>

</table>

<table class="informacion-prov2">

    @foreach ($sucursalesVentas as $sucursalesPorVenta)
        <tr>

            <td colspan="4" style="text-align: center; font-weight: bold; height: 30px">
                <p>{{ $sucursalesPorVenta['branchOffices_name'] }}</p>
            </td>

        </tr>
        <tr>
            <td colspan="4" style="text-align: center; font-weight: bold; height: 20px">
                @if ($agrupador == 'Categoría')
                    <p>VENTAS POR CATEGORÍA</p>
                @elseif($agrupador == 'Grupo')
                    <p>VENTAS POR GRUPO</p>
                @elseif($agrupador == 'Familia')
                    <p>VENTAS POR FAMILIA</p>
                @endif

            </td>
        </tr>
        <tr>
            @if ($agrupador == 'Categoría')
                <th colspan="2">CATEGORÍA</th>
            @elseif($agrupador == 'Grupo')
                <th colspan="2">GRUPO</th>
            @elseif($agrupador == 'Familia')
                <th colspan="2">FAMILIA</th>
            @endif
                <th colspan="2">
                    <p>VENTAS</p>
                </th>
            </tr>

            <?php
            $sumaTotal = [];
            $totalVentas = 0;
            
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
                    <td style="text-align:left" colspan="2">{{ $key }}</td>
                @elseif($agrupador == 'Grupo' && $ventas['articles_group'] != null)
                    <td style="text-align:left" colspan="2">{{ $key }}</td>
                @elseif($agrupador == 'Familia' && $ventas['articles_family'] != null)
                    <td style="text-align:left" colspan="2">{{ $key }}</td>
                @endif

                @if ($agrupador == 'Categoría' && $ventas['articles_category'] != null)
                    <td style="text-align:right" colspan="2">$ {{ number_format(array_key_exists($referencia, $sumaTotal) ? $sumaTotal[$referencia] : 0, 2) }}
                    </td>
                @elseif($agrupador == 'Grupo' && $ventas['articles_group'] != null)
                    <td style="text-align:right" colspan="2">$ {{ number_format(array_key_exists($referencia, $sumaTotal) ? $sumaTotal[$referencia] : 0, 2) }}
                    </td>
                @elseif($agrupador == 'Familia' && $ventas['articles_family'] != null)
                    <td style="text-align:right" colspan="2">$ {{ number_format(array_key_exists($referencia, $sumaTotal) ? $sumaTotal[$referencia] : 0, 2) }}
                    </td>
                @endif

            </tr>
        @endforeach

        <tr>
            <th style="text-align: right;">
                <p>Ventas Totales</p>
            </th>
            <th style="text-align:right" colspan="3">
                <p>$ {{ number_format($totalVentas, 2) }}</p>

            </th>
        </tr>


        <tr>
            <td colspan="4" style="text-align: center; font-weight: bold; height: 20px">

                @if ($agrupador == 'Categoría')
                    <p>GANANCIA POR CATEGORÍA</p>
                @elseif($agrupador == 'Grupo')
                    <p>GANANCIA POR GRUPO</p>
                @elseif($agrupador == 'Familia')
                    <p>GANANCIA POR FAMILIA</p>
                @endif

            </td>
        </tr>

        <tr>
            @if ($agrupador == 'Categoría')
            <th colspan="2">CATEGORÍA</th>
        @elseif($agrupador == 'Grupo')
            <th colspan="2">GRUPO</th>
        @elseif($agrupador == 'Familia')
            <th colspan="2">FAMILIA</th>
        @endif
            <th colspan="2">
                <p>GANANCIA</p>
            </th>
        </tr>

        <?php
        $sumaCosto = [];
        $sumaTotalCosto = [];
        $sumaServicio = [];
        $totalCosto = 0;
        $ganancia = 0;
        
        foreach ($sucursalesPorVenta['ventas'] as $ventas) {
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
        
        ?>

        @foreach ($keyInformacion as $referencia)
        <?php
        $key = explode('--', $referencia)[0];
        $ganancia = $sumaTotal[$referencia] - $sumaCosto[$referencia];
        ?>
        <tr>
            @if ($agrupador == 'Categoría' && $ventas['articles_category'] != null)
                <td style="text-align:left" colspan="2">{{ $key }}</td>
            @elseif($agrupador == 'Grupo' && $ventas['articles_group'] != null)
                <td style="text-align:left" colspan="2">{{ $key }}</td>
            @elseif($agrupador == 'Familia' && $ventas['articles_family'] != null)
                <td style="text-align:left" colspan="2">{{ $key }}</td>
            @endif

            @if ($agrupador == 'Categoría' && $ventas['articles_category'] != null)
                <td style="text-align:right" colspan="2">$ {{ number_format(array_key_exists($referencia, $sumaServicio) ? $sumaServicio[$referencia] : $ganancia, 2) }}
                </td>
            @elseif($agrupador == 'Grupo' && $ventas['articles_group'] != null)
                <td style="text-align:right" colspan="2">$ {{ number_format(array_key_exists($referencia, $sumaServicio) ? $sumaServicio[$referencia] : $ganancia, 2) }}
                </td>
            @elseif($agrupador == 'Familia' && $ventas['articles_family'] != null)
                <td style="text-align:right" colspan="2">$ {{ number_format(array_key_exists($referencia, $sumaServicio) ? $sumaServicio[$referencia] : $ganancia, 2) }}
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
            <th style="text-align:right" colspan="3">
                <p>$ {{ number_format($sumaServicio+$productos, 2) }}</p>

            </th>
        </tr>
    @endforeach
</table>
