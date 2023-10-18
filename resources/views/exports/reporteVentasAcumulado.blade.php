<table>
    <tr>
        <th style="width: 50px">
            <p>CLAVE</p>
        </th>
        <th>
            <p>DESCRIPCIÓN</p>
        </th>
        <th>
            <p>PRECIO</p>
        </th>
        <th>
            <p>TOTAL</p>
        </th>
    </tr>


    @foreach ($ventas as $venta)
        <tr>
            <td>{{ $venta->salesDetails_article }}</td>
            <td>{{ $venta->salesDetails_descript }}</td>
            <td>${{ number_format($venta->salesDetails_unitCost, 2) }}</td>
            <td>${{ number_format($venta->salesDetails_total, 2) }}</td>

        </tr>
    @endforeach
</table>

<table class="ventas-table">
    <?php
    $total = 0;
    foreach ($ventas as $venta) {
        $total += $venta->salesDetails_total;
    }
    ?>
    
    <tr>
        <th>
            <p> </p>
        </th>
        <th>
            <p> </p>
        </th>
        <th>
            <p> </p>
        </th>
        <th>
            <strong><p>${{number_format($total, 2)}}</p></strong>

        </th>
    </tr>
</table>