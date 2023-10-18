<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('catalogo.instituciones-financieras.show', ['instituciones_financiera' => Crypt::encrypt($institucion['instFinancial_id'])]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('catalogo.instituciones-financieras.edit', ['instituciones_financiera' => Crypt::encrypt($institucion['instFinancial_id'])]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($institucion->instFinancial_status == 'Alta')
                {!! Form::open([
                    'route' => [
                        'catalogo.instituciones-financieras.destroy',
                        'instituciones_financiera' => Crypt::encrypt($institucion['instFinancial_id']),
                    ],
                    'method' => 'DELETE',
                    'id' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif

        </div>
    </td>
    <td>{{ $institucion->instFinancial_key }}</td>
    <td>{{ $institucion->instFinancial_name }}</td>
    <td>{{ $institucion->instFinancial_city }}</td>
    <td>{{ $institucion->instFinancial_state }}</td>
    <td>{{ $institucion->instFinancial_country }}</td>
    <td>{{ $institucion->instFinancial_status }}</td>

</tr>
