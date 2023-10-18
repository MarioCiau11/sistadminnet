<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('catalogo.vehiculos.show', ['vehiculo' => Crypt::encrypt($vehiculo['vehicles_key'])]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('catalogo.vehiculos.edit', ['vehiculo' => Crypt::encrypt($vehiculo['vehicles_key'])]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($vehiculo->vehicles_status == 'Alta')
                {!! Form::open([
                    'route' => ['catalogo.vehiculos.destroy', 'vehiculo' => Crypt::encrypt($vehiculo['vehicles_key'])],
                    'method' => 'DELETE',
                    'id' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif

        </div>
    </td>
    <td>{{ $vehiculo->vehicles_key }}</td>
    <td>{{ $vehiculo->vehicles_name }}</td>
    <td>{{ $vehiculo->vehicles_category }}</td>
    <td>{{ $vehiculo->vehicles_group }}</td>
    <td>{{ $vehiculo->vehicles_plates }}</td>
    <td>{{ (float) $vehiculo->vehicles_capacityVolume }}</td>
    <td>{{ (float) $vehiculo->vehicles_capacityWeight }}</td>
    <td>{{ $vehiculo->vehicles_defaultAgent }}</td>
    <td>{{ $vehiculo->branchOffices_name }}</td>
    <td>{{ $vehiculo->vehicles_status }}</td>
</tr>
