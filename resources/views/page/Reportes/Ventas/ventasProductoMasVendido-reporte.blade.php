<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRODUCTOS MÁS VENDIDOS</title>

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
                <h3><strong>PRODUCTOS MÁS VENDIDOS</strong></h3>
                <h3><strong>{{$fecha}}</strong></h3>

            </td>
        </tr>

    </table>


    <table class="informacion-prov2">
    
        @foreach ($listasVentas as $listasPorVentas)
        <tr>
            
            <td>
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

        <table class="articulos-table" style="margin-top: 0px;">
            <tr>
                <th>
                    <p>CLAVE</p>
                </th>
                <th>
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


            @foreach ($keyInformacion as $fila => $referencia)
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

                {{-- hacemos un salto de página cada 25 registros --}}
                @if($fila % 20 == 0 && $fila !=0)
        </table>
            <div class="page-break"></div>
            <table class="articulos-table" style="margin-top: 0px;">
            <tr>
                <th>
                    <p>CLAVE</p>
                </th>
                <th>
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
            @endif
                

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
                <th style="text-align: right">
                    <p>$ {{ number_format($totalTotal, 2) }}</p>
                </th>
            </tr>
            </table>
            <br>

            @endforeach
    </table>
    </body>
    
    </html>
    