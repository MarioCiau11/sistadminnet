    <table class="cabecera ancho">
        <tr>

            <td colspan="7" style="text-align: center; font-weight:bold; padding:5px">
                <h3><strong>REPORTE - VENTAS CON SERIES </strong></h3>
            </td>

        </tr>

        <tr>

            <td colspan="7" style="text-align: right; font-weight:bold; padding:5px">
                <p><strong>Fecha de Emisión: &nbsp; </strong> {{ \Carbon\Carbon::now()->isoFormat('LL') }}</p>
            </td>

        </tr>

    </table>

    <?php
    
    $articulos_array = [];
    $clientesVentas = array();
    $ventasSeries = [];
    
    foreach ($ventas as $venta) {
        $articulos_array[] = $venta['salesDetails_article'] . ' - ' . $venta['salesDetails_descript'];
    }
    
    $articulos_array = array_unique($articulos_array);
    
    foreach ($ventas as $venta) {
        $clientesVentas[$venta['customers_key']] = $venta['customers_key'] . ' - ' . $venta['customers_businessName'];
        // dd($venta['customers_key']);
    }
    
    foreach ($ventas as $venta) {
        $ventasSeries[$venta['sales_movementID']] = $venta['sales_movementID'];
    }
    
    $clientesVentas = array_unique($clientesVentas);

    $articulosKit_array = [];
    $clientesVentasKit = array();
    $ventasSeriesKit = [];


    if (isset($kits)) {
        foreach ($kits as $kit) {
            $articulosKit_array[] = $kit['articles_key'] . ' - ' . $kit['articles_descript'];
        }

        $articulosKit_array = array_unique($articulosKit_array);
    }

    foreach ($kits as $kit) {
        $clientesVentasKit[$kit['customers_key']] = $kit['customers_key'] . ' - ' . $kit['customers_businessName'];
        // dd($venta['customers_key']);
    }

    //si $Kits tiene datos hacemos el foreach, de lo contrario no
    foreach ($kits as $kit) {
        $ventasSeriesKit[$kit['sales_movementID']] = $kit['sales_movementID'];
    }


    $articulos2_array = array_merge($articulos_array, $articulosKit_array);
    $articulos2_array = array_unique($articulos2_array);

    $combinedData = array_merge($ventas, $kits);
    // dd($combinedData);
    //ahora ordenamos por sales_movementID de manera ascendente
    usort($combinedData, function ($a, $b) {
        return $a['sales_movementID'] <=> $b['sales_movementID'];
    });


    
    ?>

    <table class="informacion-prov2">
      

        @foreach ($articulos2_array as $articulo)
        <tr>   
                <th colspan="7" style="text-align: center; font-weight:bold;"><p >{{ $articulo }}</p></th>

        </tr>
        <tr>


        </tr>
        
    <table class="articulos-table2">
        <tr>
            <th>
                <p>Fecha</p>
            </th>
            <th>
                <p>Operación</p>
            </th>
            <th >
                <p>Referencia</p>
            </th>
            <th>
                <p>Cliente</p>
            </th>
            <th>
                <p>UNIDAD</p>
            </th>
            <th>
                <p>Cantidad</p>
            </th>
                <th>
                <p>Series</p>
            </th>
        </tr>

        @foreach ($combinedData as $data)
        @php
        $identifier = '';

        if (isset($data['salesDetails_article'])) {
            $identifier = $data['salesDetails_article'] . ' - ' . $data['salesDetails_descript'];
        } elseif (isset($data['articles_key'])) {
            $identifier = $data['articles_key'] . ' - ' . $data['articles_descript'];
        }
        // dd($identifier);
        @endphp
            @if ($identifier === $articulo)
        <tr>
            <td>
                <p>{{ \Carbon\Carbon::parse($data['sales_issuedate'])->format('d/m/Y') }}</p>
            </td>
            <td>
                <p>{{ $data['sales_movement'].'-'.$data['sales_movementID'] }}</p>
            </td>
            <td>
                <p>{{ $data['sales_reference'] }}</p>
            </td>
            <td>
                <p>{{ $data['customers_key'].' - '.$data['customers_businessName'] }}</p>
            </td>
            <td>
                <p>{{ $data['salesDetails_unit'] }}</p>
            </td>
            <td>
                {{-- <p>{{ $venta['salesDetails_quantity'] }}</p>  --}}
                <p>1</p>
            </td>
            <td>
                <p>{{ $data['delSeriesMov2_lotSerie'] }}</p>
            </td>
        </tr>
        @endif
        @endforeach

    </table>
        @endforeach


    </table>

