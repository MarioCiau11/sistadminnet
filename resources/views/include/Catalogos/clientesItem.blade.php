<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('catalogo.clientes.show', ['cliente' => Crypt::encrypt($cliente['customers_key'])]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('catalogo.clientes.edit', ['cliente' => Crypt::encrypt($cliente['customers_key'])]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($cliente['customers_status'] == 'Alta')
                {!! Form::open([
                    'route' => ['catalogo.clientes.destroy', 'cliente' => Crypt::encrypt($cliente['customers_key'])],
                    'method' => 'DELETE',
                    'class' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif


        </div>
    </td>
    <td>{{ $cliente['customers_key'] }}</td>
    <td>{{ $cliente['customers_businessName'] }}</td>
    <td>{{ $cliente['customers_name'] }}</td>
    <td>{{ $cliente['customers_RFC'] }}</td>
    <td>{{ $cliente['customers_type'] == 0 ? 'Fisica' : 'Moral' }}</td>
    <td>{{ $cliente['customers_status'] }}</td>

</tr>
