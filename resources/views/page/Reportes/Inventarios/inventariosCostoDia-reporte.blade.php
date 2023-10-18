<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COSTO DEL INVENTARIO AL DÍA</title>

    <link rel="stylesheet" href="{{ asset('css/reportes/reportes.css') }}">
    <style>
        .page-break {
            page-break-after: always;
        }
    </style>


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

    </table>


    <table class="articulos-table">
        <tr>
            <th>
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
            <?php
            //hacemos que haga un salto de página cada 15 registros
            if ($key % 9 == 0 && $key != 0) {
                echo '</table>';
                echo '<div class="page-break"></div>';
                echo '<table class="cabecera ancho">
                <tr>
                    <td class="logo">
                        <img src="' .
$logo .
'" alt="Logo de la empresa">
                    </td>
        
        
                    <td class="info-empresa">
                        <h3>' .
session('company')->companies_name .
'</h3>
                        <p>R.F.C. ' .
session('company')->companies_rfc .
'</p>
                    </td>
                </tr>
    
            </table>';

echo '<table class="ancho">
                <tr>
                    <td>
                        <h3><strong>COSTO DEL INVENTARIO AL DÍA</strong></h3>
                    </td>
                </tr>
    
            </table>';

echo '<table class="articulos-table">
                <tr>
                    <th>
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
                    </tr>';
            }
            ?>
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




</body>

</html>
