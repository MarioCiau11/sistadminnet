<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('configuracion.formas-pago.show', ['formas_pago' => Crypt::encrypt($forma['formsPayment_id'])]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('configuracion.formas-pago.edit', ['formas_pago' => Crypt::encrypt($forma['formsPayment_id'])]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($forma['formsPayment_status'] !== 'Baja')
                {!! Form::open([
                    'route' => ['configuracion.formas-pago.destroy', 'formas_pago' => Crypt::encrypt($forma['formsPayment_id'])],
                    'method' => 'DELETE',
                    'class' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif


        </div>
    </td>
    <td>{{ $forma['formsPayment_key'] }}</td>
    <td>{{ $forma['formsPayment_name'] }}</td>
    <td>{{ $forma['formsPayment_descript'] }}</td>
    <td>{{ $forma['formsPayment_money'] }}</td>
    <td>{{ $forma['formsPayment_sat'] }}</td>
    <td>{{ $forma['formsPayment_status'] }}</td>
</tr>
