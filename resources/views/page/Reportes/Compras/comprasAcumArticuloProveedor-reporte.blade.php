<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPORTE DE COMPRAS ACUMULADAS POR ARTÍCULO Y PROVEEDOR</title>

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
                <h3><strong>REPORTE DE COMPRAS ACUMULADAS POR PROVEEDOR Y ARTÍCULO</strong></h3>
                {{-- <h3><strong>{{$fecha}}</strong></h3> --}}
            </td>
        </tr>

    </table>
    <table class="articulos-table">

    <?php
    $articulos_array = array();

    foreach ($compras as $compra) {
        $articulos_array[] = $compra['assistantUnit_account'] . '-' . $compra['articles_descript'];
    }

    $articulos_array = array_unique($articulos_array);
    ?>

    @foreach ($articulos_array as $articulo)
        <tr>
            <td colspan="3" style="text-align: left; padding: 10px;">
                <strong><p>{{ $articulo }}</p></strong>
            </td>
        </tr>

        <?php
        $proveedores_array = [];

        foreach ($compras as $compra) {
            if ($articulo == $compra['assistantUnit_account'] . '-' . $compra['articles_descript']) {
                $proveedor = $compra['providers_key'] . '-' . $compra['providers_name'];
                $proveedores_array[$proveedor][] = $compra;
            }
        }
        ?>

        @foreach ($proveedores_array as $proveedor => $comprasProveedor)
            <tr>
                <td colspan="3" style="font-size: 11px; text-align: center">
                    <p>{{ $proveedor }}</p>
                </td>
            </tr>

            <tr class="articulos-table">
                <th style="border: 1px solid black;">
                    <p>PERÍODO</p>
                </th>
                <th style="border: 1px solid black;">
                    <p>COMPRAS NETAS</p>
                </th>
                <th style="border: 1px solid black;">
                    <p>CANTIDAD NETA</p>
                </th>
            </tr>

            <?php
            $mes = [];
            $comprasNetas = [];
            $cantidadNetas = [];

            foreach ($comprasProveedor as $compra) {
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

            foreach ($comprasProveedor as $compra) {
                $comprasNetas[$compra['assistantUnit_period']] += $compra['assistantUnit_charge'];
                $cantidadNetas[$compra['assistantUnit_period']] += $compra['assistantUnit_chargeUnit'];
            }
            ?>

            @foreach ($mes as $key => $meses)
                <tr>
                    <td style="text-align: center">
                        <p>{{ $meses }}</p>
                    </td>
                    <td style="text-align: center">
                        <p>${{ number_format($comprasNetas[$key], 2) }}</p>
                        </td>
                        <td style="text-align: center">
                            <p>{{ number_format($cantidadNetas[$key], 0) }}</p>
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <th>
                        <p>TOTAL:</p>
                    </th>
                    <th>
                        <p>${{ number_format(array_sum($comprasNetas), 2) }}</p>
                    </th>
                    <th>
                        <p>{{ number_format(array_sum($cantidadNetas), 0) }}</p>
                    </th>
                </tr>

            @endforeach

        @endforeach

    </table>



    <table class="articulos-table">

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





</body>

</html>