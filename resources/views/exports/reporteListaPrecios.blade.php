<table class="informacion-categoria" style="width: 500px; text-align: center; margin: 0 auto;">
    <tr>
        <td colspan="1"></td>
        <td colspan="3" style="text-align: center;">Lista de {{ $listaPrecio }}</td>
    </tr>
    @foreach ($categoriaArticulo as $categoriaPorArticulo)
        @if ($nameFoto == 'Si')
            <tr>
                <td colspan="4"
                    style="border: 3px solid rgb(0, 0, 0); border-radius: 3px; padding: 3px; background-color: #1e5a5c; border-color: #225054; text-align: center">
                    <strong>
                        <p style="color: #fff">{{ $categoriaPorArticulo['articles_category'] }}</p>
                    </strong>

                </td>

            </tr>
        @else
            <tr>
                <td colspan="3"
                    style="border: 3px solid rgb(0, 0, 0); border-radius: 3px; padding: 3px; background-color: #1e5a5c; border-color: #225054; text-align: center">
                    <strong>
                        <p style="color: #fff">{{ $categoriaPorArticulo['articles_category'] }}</p>
                    </strong>

                </td>

            </tr>
        @endif


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

                    @if ($nameFoto == 'Si')
                        <td></td>
                    @endif
                    <td style="width: 150px; text-align: left">{{ $art['articles_key'] }}</td>

                    <td style="width: 500px; text-align: left">{{ $art['articles_descript'] }}</td>


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
                                {{ number_format($art['articles_listPrice1'], 2) }}
                            </td>
                        @elseif ($listaPrecio == 'Precio 2')
                            <td style="width: 100px; text-align: right">
                                {{ number_format($art['articles_listPrice2'], 2) }}
                            </td>
                        @elseif ($listaPrecio == 'Precio 3')
                            <td style="width: 100px; text-align: right">
                                {{ number_format($art['articles_listPrice3'], 2) }}
                            </td>
                        @elseif ($listaPrecio == 'Precio 4')
                            <td style="width: 100px; text-align: right">
                                {{ number_format($art['articles_listPrice4'], 2) }}
                            </td>
                        @elseif ($listaPrecio == 'Precio 5')
                            <td style="width: 100px; text-align: right">
                                {{ number_format($art['articles_listPrice5'], 2) }}
                            </td>
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

        <br>
    @endforeach
</table>
