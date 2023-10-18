<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('catalogo.agentes.show', ['operativo' => Crypt::encrypt($agente['agents_key'])]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('catalogo.agentes.edit', ['operativo' => Crypt::encrypt($agente['agents_key'])]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($agente->agents_status == 'Alta')
                {!! Form::open([
                    'route' => ['catalogo.agentes.destroy', 'operativo' => Crypt::encrypt($agente['agents_key'])],
                    'method' => 'DELETE',
                    'id' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif

        </div>
    </td>
    <td>{{ $agente->agents_key }}</td>
    <td>{{ $agente->agents_name }}</td>
    <td>{{ $agente->agents_type }}</td>
    <td>{{ $agente->agents_category }}</td>
    <td>{{ $agente->agents_group }}</td>
    <td>{{ $agente->branchOffices_name }}</td>
    <td>{{ $agente->agents_status }}</td>
</tr>
