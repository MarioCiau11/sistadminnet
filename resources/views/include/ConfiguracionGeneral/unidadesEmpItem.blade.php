<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('configuracion.unidades-empaque.show', ['unidades_empaque' => Crypt::encrypt($unidad['packaging_units_id'])]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('configuracion.unidades-empaque.edit', ['unidades_empaque' => Crypt::encrypt($unidad['packaging_units_id'])]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($unidad['packaging_units_status'] == 'Alta')
                {!! Form::open([
                    'route' => ['configuracion.unidades-empaque.destroy', 'unidades_empaque' => $unidad['packaging_units_id']],
                    'method' => 'DELETE',
                    'class' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif


        </div>
    </td>
    <td>{{ $unidad['packaging_units_packaging'] }}</td>
    <td>{{ $unidad['packaging_units_weight'] }}</td>
    <td>{{ $unidad['packaging_units_unit'] }}</td>
    <td>{{ $unidad['packaging_units_status'] }}</td>
</tr>
