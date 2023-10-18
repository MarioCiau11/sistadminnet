<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('configuracion.usuarios.show', ['usuario' => Crypt::encrypt($user['user_id'])]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('configuracion.usuarios.edit', ['usuario' => Crypt::encrypt($user['user_id'])]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($user['user_status'] == 'Alta')
                {!! Form::open([
                    'route' => ['configuracion.usuarios.destroy', 'usuario' => Crypt::encrypt($user['user_id'])],
                    'method' => 'DELETE',
                    'class' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif

        </div>
    </td>
    <td>{{ $user['user_name'] }}</td>
    <td>{{ $user['username'] }}</td>
    <td>{{ $user['user_email'] }}</td>
    <td>{{ $user['user_rol'] }}</td>
    <td>{{ $user['user_status'] }}</td>
    <td>{{ $user['user_block_sale_prices'] }}</td>
</tr>
