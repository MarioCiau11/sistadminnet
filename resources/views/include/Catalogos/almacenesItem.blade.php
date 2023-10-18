<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('catalogo.almacen.show', ['almacen' => Crypt::encrypt($almacen['depots_id'])]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('catalogo.almacen.edit', ['almacen' => Crypt::encrypt($almacen['depots_id'])]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($almacen->depots_status == 'Alta')
                {!! Form::open([
                    'route' => ['catalogo.almacen.destroy', 'almacen' => Crypt::encrypt($almacen['depots_id'])],
                    'method' => 'DELETE',
                    'id' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif

        </div>
    </td>
    <td>{{ $almacen->depots_key }}</td>
    <td>{{ $almacen->depots_name }}</td>
    <td>{{ $almacen->branchOffices_name }}</td>
    <td>{{ $almacen->depots_type }}</td>
    <td>{{ $almacen->depots_status }}</td>
</tr>
