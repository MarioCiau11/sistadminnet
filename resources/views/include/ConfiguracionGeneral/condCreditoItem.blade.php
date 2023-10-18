<tr>
    <td class="td-option">
        <div class="contenedor-opciones" style="flex-wrap: nowrap; width:100%">
            <a href="{{ route('configuracion.condiciones-credito.show', ['condiciones_termino' => Crypt::encrypt($condCredito['creditConditions_id'])]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('configuracion.condiciones-credito.edit', ['condiciones_termino' => Crypt::encrypt($condCredito['creditConditions_id'])]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($condCredito['creditConditions_status'] !== 'Baja')
                {!! Form::open([
                    'route' => [
                        'configuracion.condiciones-credito.destroy',
                        'condiciones_termino' => Crypt::encrypt($condCredito['creditConditions_id']),
                    ],
                    'method' => 'DELETE',
                    'class' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif


        </div>
    </td>
    <td>{{ $condCredito['creditConditions_name'] }}</td>
    <td>{{ $condCredito['creditConditions_type'] }}</td>
    <td>{{ $condCredito['creditConditions_days'] }}</td>
    <td>{{ $condCredito['creditConditions_typeDays'] }}</td>
    <td>{{ $condCredito['creditConditions_workDays'] }}</td>
    <td>{{ $condCredito['creditConditions_paymentMethod'] }}</td>
    <td>{{ $condCredito['creditConditions_status'] }}</td>
</tr>
