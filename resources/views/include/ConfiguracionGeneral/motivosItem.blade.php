<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('configuracion.motivos-cancelacion.show', ['motivos_cancelacion' => Crypt::encrypt($motivo['reasonCancellations_id'])]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('configuracion.motivos-cancelacion.edit', ['motivos_cancelacion' => Crypt::encrypt($motivo['reasonCancellations_id'])]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($motivo['reasonCancellations_status'] == 'Alta')
                {!! Form::open([
                    'route' => [
                        'configuracion.motivos-cancelacion.destroy',
                        'motivos_cancelacion' => $motivo['reasonCancellations_id'],
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


    <td>{{ $motivo['reasonCancellations_name'] }}</td>
    <td>{{ $motivo['reasonCancellations_module'] }}</td>
    <td>{{ $motivo['reasonCancellations_status'] }}</td>
</tr>
