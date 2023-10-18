<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('catalogo.proveedor.show', ['proveedor' => Crypt::encrypt($proveedor['providers_key'])]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('catalogo.proveedor.edit', ['proveedor' => Crypt::encrypt($proveedor['providers_key'])]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($proveedor['providers_status'] == 'Alta')
                {!! Form::open([
                    'route' => ['catalogo.proveedor.destroy', 'proveedor' => Crypt::encrypt($proveedor['providers_key'])],
                    'method' => 'DELETE',
                    'class' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif


        </div>
    </td>

    <td>{{ $proveedor['providers_key'] }}</td>
    <td>{{ $proveedor['providers_name'] }}</td>
    <td>{{ $proveedor['providers_nameShort'] }}</td>
    <td>{{ $proveedor['providers_RFC'] }}</td>
    <td>{{ $proveedor['providers_CURP'] }}</td>
    <td>{{ $proveedor['providers_type'] }}</td>
    <td>{{ $proveedor['providers_category'] }}</td>
    <td>{{ $proveedor['providers_group'] }}</td>
    <td>{{ $proveedor['providers_status'] }}</td>

</tr>
