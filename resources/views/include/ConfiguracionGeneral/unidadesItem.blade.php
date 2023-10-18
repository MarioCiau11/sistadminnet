<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('configuracion.unidades.show', ['unidade' => Crypt::encrypt($unidad['units_id'])]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('configuracion.unidades.edit', ['unidade' => Crypt::encrypt($unidad['units_id'])]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($unidad['units_status'] == 'Alta')
                {!! Form::open([
                    'route' => ['configuracion.unidades.destroy', 'unidade' => $unidad['units_id']],
                    'method' => 'DELETE',
                    'class' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif


        </div>
    </td>
    <td>{{ $unidad->units_unit }}</td>
    <td>{{ $unidad->units_decimalVal }}</td>
    <td>{{ $unidad->nombre }}</td>
    <td>{{ $unidad->units_status }}</td>
</tr>
