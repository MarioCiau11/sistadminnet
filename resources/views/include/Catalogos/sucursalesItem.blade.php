<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('catalogo.sucursal.show', ['sucursal' => Crypt::encrypt($sucursal->branchOffices_id)]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('catalogo.sucursal.edit', ['sucursal' => Crypt::encrypt($sucursal->branchOffices_id)]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($sucursal->branchOffices_status == 'Alta')
                {!! Form::open([
                    'route' => ['catalogo.sucursal.destroy', 'sucursal' => Crypt::encrypt($sucursal->branchOffices_id)],
                    'method' => 'DELETE',
                    'class' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif


        </div>
    </td>

    <td>{{ $sucursal->branchOffices_key }}</td>
    <td>{{ $sucursal->branchOffices_name }}</td>
    <td>{{ $sucursal->companies_name }}</td>
    <td>{{ $sucursal->branchOffices_addres }}</td>
    <td>{{ $sucursal->branchOffices_suburb }}</td>
    <td>{{ $sucursal->branchOffices_cp }}</td>
    <td>{{ $sucursal->branchOffices_city }}</td>
    <td>{{ $sucursal->branchOffices_state }}</td>
    <td>{{ $sucursal->branchOffices_country }}</td>
    <td>{{ $sucursal->branchOffices_status }}</td>

</tr>
