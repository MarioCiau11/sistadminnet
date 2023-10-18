<table class="ancho">
    <tr>
        <td colspan="12" style="text-align: center; font-weight: bold; padding:5px">
            <h3><strong>COSTO DEL INVENTARIO AL DÍA</strong></h3>
            <?php
            $costoGlobal = 0;
            $totalInventario = 0;
            
            foreach ($inventario as $key => $inv) {
                $costoGlobal += $inv->articlesCost_averageCost * $inv->articlesInv_inventory;
                $totalInventario += $inv->articlesInv_inventory;
            }
            ?>
            <h4>COSTO GLOBAL: ${{ number_format($costoGlobal, 2) }}</h4>
            <h4>TOTAL INVENTARIO: {{ number_format($totalInventario, 2) }} </h4>
        </td>
    </tr>


    <tr>
        <th style="width: 50px">

            <p>CLAVE</p>
        </th>
        <th>
            <p>NOMBRE DEL ARTÍCULO</p>
        </th>
        <th>
            <p>ALMACÉN</p>
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
            <p>COSTO</p>
        </th>
        <th>
            <p>EXISTENCIA</p>
        </th>
        <th>
            <p>PRECIO VENTA - LISTA 1</p>
        </th>
        <th>
            <p>PRECIO VENTA - LISTA 2</p>
        </th>
        <th>
            <p>PRECIO VENTA - LISTA 3</p>
        </th>
        <th>
            <p>PRECIO VENTA - LISTA 4</p>
        </th>
        <th>
            <p>PRECIO VENTA - LISTA 5</p>
        </th>
    </tr>

    @foreach ($inventario as $key => $inv)
        <tr>
            <td>{{ $inv->articles_key }}</td>
            <td>{{ $inv->articles_descript }}</td>
            <td>{{ $inv->depots_name }}</td>
            <td>{{ $inv->articles_family }}</td>
            <td>{{ $inv->articles_category }}</td>
            <td>{{ $inv->articles_group }}</td>
            <td style="text-align: right;">${{ number_format($inv->articlesCost_averageCost, 2) }}</td>
            @if ($inv->articlesInv_inventory === null)
                <td style="text-align: center;">0</td>
            @else
                <td style="text-align: center;">{{ number_format($inv->articlesInv_inventory, 0) }}</td>
            @endif
            <td style="text-align: right;">${{ number_format($inv->articles_listPrice1, 2) }}</td>
            <td style="text-align: right;">${{ number_format($inv->articles_listPrice2, 2) }}</td>
            <td style="text-align: right;">${{ number_format($inv->articles_listPrice3, 2) }}</td>
            <td style="text-align: right;">${{ number_format($inv->articles_listPrice4, 2) }}</td>
            <td style="text-align: right;">${{ number_format($inv->articles_listPrice5, 2) }}</td>
        </tr>
    @endforeach
</table>
