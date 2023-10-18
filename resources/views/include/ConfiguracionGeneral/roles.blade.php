<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('configuracion.roles.show', ['role' => Crypt::encrypt($rol->id)]) }}" class="show"
                data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('configuracion.roles.edit', ['role' => Crypt::encrypt($rol->id)]) }}" class="edit"
                data-toggle="tooltip" data-placement="top" title="Editar registro"><i class="fa fa-pencil-square-o"
                    aria-hidden="true"></i></a>

            @if ($rol->status == 'Alta')
                {!! Form::open([
                    'route' => ['configuracion.roles.destroy', Crypt::encrypt($rol->id)],
                    'method' => 'DELETE',
                    'class' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif
        </div>
    </td>


    <td>{{ $rol->name }}</td>
    <td>{{ $rol->identifier }}</td>
    <td>{{ $rol->descript }}</td>
    <td>{{ $rol->status }}</td>
</tr>
