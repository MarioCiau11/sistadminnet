<table class="ancho">
    <tr>
        <td colspan="8" style="text-align: center; font-weight: bold; height: 60px">
            <p><strong>PRODUCTOS MÁS VENDIDOS</strong></p>
        </td>
    </tr>

</table>

<table class="informacion-prov2">
    
    @foreach ($listasVentas as $listasPorVentas)
    <tr>
        
        <td colspan="8" style="text-align: center; font-weight: bold; height: 30px">
            @if ($listasPorVentas['sales_listPrice'] == 'articles_listPrice1')
                <p>Precio 1</p>
            @elseif($listasPorVentas['sales_listPrice'] == 'articles_listPrice2')
                <p>Precio 2</p>
            @elseif($listasPorVentas['sales_listPrice'] == 'articles_listPrice3')
                <p>Precio 3</p>
            @elseif($listasPorVentas['sales_listPrice'] == 'articles_listPrice4')
                <p>Precio 4</p>
            @elseif($listasPorVentas['sales_listPrice'] == 'articles_listPrice5')
                <p>Precio 5</p>
            @endif

        </td>

    </tr>

        <tr>
            <th style="width: 50px">
                <p>CLAVE</p>
            </th>
            <th >
                <p>DESCRIPCIÓN</p>
            </th>
            <th>
                <p>FAMILIA</p>
            </th>
            <th>
                <p>CATEGORÍA</p>
            </th>
            <th>
                <p>GRUPO</p>
            </th>
            <th>
                <p>CANTIDAD</p>
            </th>
            <th>
                <p>PRECIO DE VENTA</p>
            </th>
            <th>
                <p>TOTAL</p>
            </th>
        </tr>

<?php

$sumaCantidad = [];
$sumaTotal = [];
$totalTotal = 0;

        

foreach ($listasPorVentas['ventas'] as $key => $venta) {
    if (!array_key_exists($venta['salesDetails_article'] . '--' . $venta['sales_listPrice'] . '--' . $venta['salesDetails_descript'] . '--' . $venta['articles_family'] . '--' . $venta['articles_category'] . '--' . $venta['articles_group'] . '--' . $venta['salesDetails_unitCost'], $sumaCantidad)) {
        $sumaCantidad[$venta['salesDetails_article'] . '--' . $venta['sales_listPrice'] . '--' . $venta['salesDetails_descript'] . '--' . $venta['articles_family'] . '--' . $venta['articles_category'] . '--' . $venta['articles_group'] . '--' . $venta['salesDetails_unitCost']] = $venta['salesDetails_quantity'];
    } else {
        $sumaCantidad[$venta['salesDetails_article'] . '--' . $venta['sales_listPrice'] . '--' . $venta['salesDetails_descript'] . '--' . $venta['articles_family'] . '--' . $venta['articles_category'] . '--' . $venta['articles_group'] . '--' . $venta['salesDetails_unitCost']] += $venta['salesDetails_quantity'];
    }

}

foreach ($listasPorVentas['ventas'] as $key => $venta) {
    if (!array_key_exists($venta['salesDetails_article'] . '--' . $venta['sales_listPrice'] . '--' . $venta['salesDetails_descript'] . '--' . $venta['articles_family'] . '--' . $venta['articles_category'] . '--' . $venta['articles_group'] . '--' . $venta['salesDetails_unitCost'], $sumaTotal)) {
        $sumaTotal[$venta['salesDetails_article'] . '--' . $venta['sales_listPrice'] . '--' . $venta['salesDetails_descript'] . '--' . $venta['articles_family'] . '--' . $venta['articles_category'] . '--' . $venta['articles_group'] . '--' . $venta['salesDetails_unitCost']] = $venta['salesDetails_total'];
    } else {
        $sumaTotal[$venta['salesDetails_article'] . '--' . $venta['sales_listPrice'] . '--' . $venta['salesDetails_descript'] . '--' . $venta['articles_family'] . '--' . $venta['articles_category'] . '--' . $venta['articles_group'] . '--' . $venta['salesDetails_unitCost']] += $venta['salesDetails_total'];
    }
}


foreach ($listasPorVentas['ventas'] as $key => $venta) {
    $totalTotal += $venta['salesDetails_total'];
}


arsort($sumaCantidad);
$keyInformacion = array_keys($sumaCantidad);





?>
        
        @foreach ($keyInformacion as $referencia)
            
        <?php
        $key = explode('--', $referencia);
            
        ?>
        <tr>
             
            <td>{{ $key[0] }}</td>
            <td style="text-align: left">{{ $key[2] }}</td>   
            <td>{{ $key[3] }}</td> 
            <td>{{ $key[4] }}</td>
            <td>{{ $key[5] }}</td>  
            <td>
                {{ number_format(array_key_exists($referencia, $sumaCantidad) ? $sumaCantidad[$referencia] : 0, 0) }}    
            </td>  
            <td style="text-align: right">${{ number_format($key[6], 2) }}</td>
            <td style="text-align: right">$ {{ number_format(array_key_exists($referencia, $sumaTotal) ? $sumaTotal[$referencia] : 0, 2) }}</td>
        </tr>

        @endforeach

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
                <p></p>
            </th>
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
                <p>{{ number_format($totalTotal, 2) }}</p>
            </th>
        </tr>
    
        <br>

        
        @endforeach
</table>