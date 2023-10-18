<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('configuracion.concepto-modulos.show', ['concepto_proceso' => Crypt::encrypt($concepto['moduleConcept_id'])]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('configuracion.concepto-modulos.edit', ['concepto_proceso' => Crypt::encrypt($concepto['moduleConcept_id'])]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($concepto['moduleConcept_status'] == 'Alta')
                {!! Form::open([
                    'route' => ['configuracion.concepto-modulos.destroy', 'concepto_proceso' => $concepto['moduleConcept_id']],
                    'method' => 'DELETE',
                    'class' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif
        </div>
    </td>


    <td>{{ $concepto['moduleConcept_name'] }}</td>
    <td>{{ $concepto['moduleConcept_module'] }}</td>
    {{-- <td>{{ $concepto['moduleConcept_movement'] }}</td> --}}
    <td>{{ $concepto['moduleConcept_status'] }}</td>
</tr>
