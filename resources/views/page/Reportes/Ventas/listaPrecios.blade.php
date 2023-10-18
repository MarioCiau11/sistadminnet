<?php
use Luecano\NumeroALetras\NumeroALetras;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LISTA DE PRECIOS</title>

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

            <td class="info-venta">
                <p><strong>{{ $listaPrecio }}</strong></p>
                <p>{{ \Carbon\Carbon::now()->isoFormat('LL') }}</p>
            </td>
        </tr>

    </table>
    <table class="informacion-categoria" style="width: 500px; text-align: center; margin: 0 auto;">
        @foreach ($categoriaArticulo as $categoriaPorArticulo)
            <tr>
                <td colspan="4"
                    style="border: 3px solid rgb(0, 0, 0); border-radius: 3px; padding: 3px; background-color: #1e5a5c; border-color: #225054;">
                    <h3 style="color: #fff">{{ $categoriaPorArticulo['articles_category'] }}</h3>

                </td>

            </tr>



            <tr>
                @if ($nameFoto == 'Si')
                    <th>
                        Foto
                    </th>
                @endif
                <th style="padding: 5px;">
                    Clave
                </th>
                <th>
                    Descripción
                </th>
                <th>
                    Precio
                </th>
            </tr>

            @foreach ($categoriaPorArticulo['precios'] as $key => $art)
                @if ($art['articles_category'] == $categoriaPorArticulo['articles_category'] || $art['articles_category'] == null)
                    <tr>
                        <?php
                        //hacemos que haga un salto de pagina cada 30 articulos
                        if ($key % 22 == 0 && $key != 0) {
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
                                            
                                                    <td class="info-venta">
                                            
                                                        <p><strong>' .
                                $listaPrecio .
                                '</strong></p>
                                            
                                                        <p>' .
                                \Carbon\Carbon::now()->isoFormat('LL') .
                                '</p>
                                            
                                                    </td>
                                            
                                                </tr>
                                            
                                            </table>';
                        
                            echo '<table class="informacion-categoria" style="width: 500px; text-align: center; margin: 0 auto;">
                                            
                                                <tr>
                                            
                                                    <td colspan="4" style="border: 3px solid rgb(0, 0, 0); border-radius: 3px; padding: 3px; background-color: #1e5a5c; border-color: #225054;">
                                            
                                                        <h3 style="color: #fff">' .
                                $categoriaPorArticulo['articles_category'] .
                                '</h3>
                                            
                                                    </td>
                                            
                                                </tr>
                                            
                                                <tr>';
                        
                            if ($nameFoto == 'Si') {
                                echo '<th></th>';
                            }
                        
                            echo '<th style="padding: 5px;">Clave</th>
                                            
                                                    <th>Descripción</th>
                                            
                                                    <th>Precio</th>
                                            
                                                </tr>';
                        }
                        
                        ?>
                        <?php
                        $defaultImage = storage_path('app/empresas/images.png');
                        ?>
                        @if ($nameFoto == 'Si')
                            @if ($art['articlesImg_path'] != null)
                                <?php
                                $image = 'archivo/' . $art['articlesImg_path'];
                                //ruta local
                                $ruta = storage_path('app/empresas/' . $art['articlesImg_path']);
                                
                                ?>
                                @if (file_exists($ruta))
                                    <td><img src="{{ url($image) }}" alt=""
                                            style="width: 40px; height: 40px;"></td>
                                @else
                                    <td><img src="{{ $defaultImage }}" alt=""
                                            style="width: 40px; height: 40px;"></td>
                                @endif
                            @else
                                <?php
                                $image = $defaultImage;
                                ?>
                                <td style="width: 40px; text-align: left"><img src="{{ $image }}" alt=""
                                        style="width: 40px; height: 40px;"></td>
                            @endif
                        @endif
                        <td style="width: 100px; text-align: center">{{ $art['articles_key'] }}</td>

                        <td style="width: 300px; text-align: left">{{ $art['articles_descript'] }}</td>


                        <?php
                        //calculamos el precio con impuestos
                        if ($listaPrecio == 'Precio 1') {
                            $precio = $art['articles_listPrice1'] + ($art['articles_listPrice1'] * $art['articles_porcentIva']) / 100;
                        } elseif ($listaPrecio == 'Precio 2') {
                            $precio = $art['articles_listPrice2'] + ($art['articles_listPrice2'] * $art['articles_porcentIva']) / 100;
                        } elseif ($listaPrecio == 'Precio 3') {
                            $precio = $art['articles_listPrice3'] + ($art['articles_listPrice3'] * $art['articles_porcentIva']) / 100;
                        } elseif ($listaPrecio == 'Precio 4') {
                            $precio = $art['articles_listPrice4'] + ($art['articles_listPrice4'] * $art['articles_porcentIva']) / 100;
                        } elseif ($listaPrecio == 'Precio 5') {
                            $precio = $art['articles_listPrice5'] + ($art['articles_listPrice5'] * $art['articles_porcentIva']) / 100;
                        }
                        ?>
                        @if (session('company')->companies_calculateTaxes == 1)
                            @if ($listaPrecio == 'Precio 1')
                                <td style="width: 100px; text-align: right">
                                    {{ number_format($art['articles_listPrice1'], 2) }}</td>
                            @elseif ($listaPrecio == 'Precio 2')
                                <td style="width: 100px; text-align: right">
                                    {{ number_format($art['articles_listPrice2'], 2) }}</td>
                            @elseif ($listaPrecio == 'Precio 3')
                                <td style="width: 100px; text-align: right">
                                    {{ number_format($art['articles_listPrice3'], 2) }}</td>
                            @elseif ($listaPrecio == 'Precio 4')
                                <td style="width: 100px; text-align: right">
                                    {{ number_format($art['articles_listPrice4'], 2) }}</td>
                            @elseif ($listaPrecio == 'Precio 5')
                                <td style="width: 100px; text-align: right">
                                    {{ number_format($art['articles_listPrice5'], 2) }}</td>
                            @endif
                        @else
                            @if ($listaPrecio == 'Precio 1')
                                <td style="width: 100px; text-align: right">{{ number_format($precio, 2) }}</td>
                            @elseif ($listaPrecio == 'Precio 2')
                                <td style="width: 100px; text-align: right">{{ number_format($precio, 2) }}</td>
                            @elseif ($listaPrecio == 'Precio 3')
                                <td style="width: 100px; text-align: right">{{ number_format($precio, 2) }}</td>
                            @elseif ($listaPrecio == 'Precio 4')
                                <td style="width: 100px; text-align: right">{{ number_format($precio, 2) }}</td>
                            @elseif ($listaPrecio == 'Precio 5')
                                <td style="width: 100px; text-align: right">{{ number_format($precio, 2) }}</td>
                            @endif
                        @endif
                    </tr>
                @endif
            @endforeach
        @endforeach
    </table>

    <table class="cabecera ancho" style="width: 500px; text-align: center; margin: 0 auto;">
        <tr>

            <td class="info-empresa"
                style="border: 2px solid rgb(0, 0, 0); border-radius: 5px; padding: 5px; background-color: #dbe4f0; border-color: #225054;">
                <h2>Precios sujetos a cambios sin previo aviso.</h2>
                <br>
                <p>{{ session('company')->companies_addres }} Col. {{ session('company')->companies_suburb }},
                    C.P.{{ session('company')->companies_cp }}, {{ session('company')->companies_city }},
                    {{ session('company')->companies_state }}.</p>
                <br>

                <p>Informes al {{ session('company')->companies_phone1 }} o al
                    {{ session('company')->companies_phone2 }}</p>
                <br>
                <p>Correo electrónico: {{ session('company')->companies_mail }}</p>
                <h3> {{ session('company')->companies_descript }}</h3>

                <p>
                    Horarios de atención: Lunes a Viernes de 9 am a 6 pm.

                </p>
            </td>
        </tr>

    </table>
</body>

</html>
