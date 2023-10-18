
<table class="ancho">
    <tr>
        <td colspan="7" style="text-align: center; font-weight:bold; padding:5px">
            <p><strong>Compras Acumuladas por Artículo y Proveedor</strong></p>
        </td>
    </tr>

</table>

<table class="informacion-prov2">

    <?php
    $articulos_array = array();
    $proveedores_array = array();

    foreach ($compras as $compra) {
        $articulos_array[] = $compra['assistantUnit_account'] . '-' . $compra['articles_descript'];
    }

    $articulos_array = array_unique($articulos_array);

    foreach ($compras as $compra) {
        $proveedores_array[$compra['providers_key']] = $compra['providers_key'] . '-' . $compra['providers_name'];
    }

    $proveedores_array = array_unique($proveedores_array);
    ?>

    @foreach ($articulos_array as $articulo)
        <tr>

            <td colspan="3" style="text-align: left;">
                <strong><p>{{ $articulo }}</p></strong>

            </td>

        </tr>

        @foreach ($proveedores_array as $proveedor)
            <?php
            $articuloPorCompra = [
                'providers_key' => '',
                'providers_name' => '',
                'compras' => []
            ];
            ?>

            @foreach ($compras as $compra)
                @if ($articulo == $compra['assistantUnit_account'] . '-' . $compra['articles_descript'] && $proveedor == $compra['providers_key'] . '-' . $compra['providers_name'])
                    <?php
                    $articuloPorCompra['providers_key'] = $compra['providers_key'];
                    $articuloPorCompra['providers_name'] = $compra['providers_name'];
                    $articuloPorCompra['compras'][] = $compra;
                    ?>
                @endif
            @endforeach

            @if (count($articuloPorCompra['compras']) > 0)
                <tr>

                    <td colspan="3" style="font-size: 11px">
                        <p>{{ $articuloPorCompra['providers_key'] }} - {{ $articuloPorCompra['providers_name'] }}</p>

                    </td>
                </tr>

                    <tr>
                        <th>
                            <p>PERÍODO</p>
                        </th>
                        <th>
                            <p>COMPRAS NETAS</p>
                        </th>
                        <th>
                            <p>CANTIDAD NETA</p>
                        </th>
                    </tr>

                    <?php
                    $mes = [];
                    $comprasNetas = [];
                    $cantidadNetas = [];

                    //el mes no se tiene que repetir

                    foreach ($articuloPorCompra['compras'] as $key => $compra) {
                        $meses = [
                            '1' => 'ENERO',
                            '2' => 'FEBRERO',
                            '3' => 'MARZO',
                            '4' => 'ABRIL',
                            '5' => 'MAYO',
                            '6' => 'JUNIO',
                            '7' => 'JULIO',
                            '8' => 'AGOSTO',
                            '9' => 'SEPTIEMBRE',
                            '10' => 'OCTUBRE',
                            '11' => 'NOVIEMBRE',
                            '12' => 'DICIEMBRE',
                        ];

                        $mes[$compra['assistantUnit_period']] = $meses[$compra['assistantUnit_period']];

                    }

                    foreach ($mes as $key => $meses) {
                        $comprasNetas[$key] = 0;
                        $cantidadNetas[$key] = 0;
                    }

                    foreach ($articuloPorCompra['compras'] as $key => $compra) {
                        $comprasNetas[$compra['assistantUnit_period']] += $compra['assistantUnit_charge'];
                        $cantidadNetas[$compra['assistantUnit_period']] += $compra['assistantUnit_chargeUnit'];
                    }

                    ?>

                    @foreach ($mes as $key => $meses)
                        <tr>
                            <td>
                                <p>{{ $meses }}</p>
                            </td>
                            <td>
                                <p>${{ number_format($comprasNetas[$key], 2) }}</p>
                            </td>
                            <td>
                                <p>{{ number_format($cantidadNetas[$key], 0) }}</p>
                            </td>
                        </tr>

                    @endforeach

                    <tr>
                        <th>
                            <strong><p>TOTAL:</p></strong>
                        </th>
                        <th>
                            <strong><p>${{ number_format(array_sum($comprasNetas), 2) }}</p></strong>
                        </th>
                        <th>
                            <strong><p>${{ number_format(array_sum($comprasNetas), 2) }}</p></strong>
                        </th>
                    </tr>


            @endif

        @endforeach 
    @endforeach



    <?php
    $sumaComprasNetas = 0;
    $sumaCantidadNeta = 0;


    foreach ($compras as $compra) {
        $sumaComprasNetas += $compra['assistantUnit_charge'];
        $sumaCantidadNeta += $compra['assistantUnit_chargeUnit'];
    }
    

        ?>
    <tr>
        <th>
            <p>TOTAL:</p>
        </th>
        <th>
            <p>${{ number_format($sumaComprasNetas, 2) }}</p>
        </th>
        <th>
            <p>{{ number_format($sumaCantidadNeta, 0) }}</p>
        </th>
    </tr>

</table>