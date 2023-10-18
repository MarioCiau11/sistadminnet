    <tr>
        <td class="td-option">
            <div class="contenedor-opciones">
                <a href="{{ route('catalogo.articulos.show', ['articulo' => Crypt::encrypt($articulo->articles_id)]) }}"
                    class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                        aria-hidden="true"></i></a>
                <a href="{{ route('catalogo.articulos.edit', ['articulo' => Crypt::encrypt($articulo->articles_id)]) }}"
                    class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                        class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

                {!! Form::open([
                    'route' => ['catalogo.articulos.destroy', 'articulo' => Crypt::encrypt($articulo->articles_id)],
                    'method' => 'DELETE',
                    'id' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}

            </div>
        </td>
        <td>{{ $articulo->articles_key }}</td>
        <td>{{ $articulo->articles_type == 'Normal' ? 'Producto' : ($articulo->articles_type == 'Serie' ? 'Serializado' : ($articulo->articles_type == 'Kit' ? 'Kit/Combo' : 'Servicio')) }}
        </td>
        <td>{{ $articulo->articles_descript }}</td>
        <td>{{ $articulo->articles_descript2 }}</td>
        <td>{{ $articulo->articles_category }}</td>
        <td>{{ $articulo->articles_group }}</td>
        <td>{{ $articulo->articles_family }}</td>
        <td>{{ $unidad[$articulo->articles_unitBuy] }}</td>
        <td>{{ $unidad[$articulo->articles_unitSale] }}</td>
        <td>{{ $unidad[$articulo->articles_transfer] }}</td>
        <td>{{ number_format($articulo->articles_porcentIva, 2) . '%' }}</td>
        <td>{{ $articulo->articles_status }}</td>
        <td>$ {{ number_format($articulo->articles_listPrice1, 2) }}</td>
        <td>$ {{ number_format($articulo->articles_listPrice2, 2) }}</td>
        <td>$ {{ number_format($articulo->articles_listPrice3, 2) }}</td>
        <td>$ {{ number_format($articulo->articles_listPrice4, 2) }}</td>
        <td>$ {{ number_format($articulo->articles_listPrice5, 2) }}</td>
    </tr>
