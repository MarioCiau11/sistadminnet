<tr>
    <td class="td-option">
        <div class="contenedor-opciones">
            <a href="{{ route('catalogo.concepto-gastos.show', ['razones_gasto' => Crypt::encrypt($gasto['expenseConcepts_id'])]) }}"
                class="show" data-toggle="tooltip" data-placement="top" title="Mostrar registro"><i class="fa fa-eye"
                    aria-hidden="true"></i></a>
            <a href="{{ route('catalogo.concepto-gastos.edit', ['razones_gasto' => Crypt::encrypt($gasto['expenseConcepts_id'])]) }}"
                class="edit" data-toggle="tooltip" data-placement="top" title="Editar registro"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

            @if ($gasto['expenseConcepts_status'] == 'Alta')
                {!! Form::open([
                    'route' => ['catalogo.concepto-gastos.destroy', 'razones_gasto' => Crypt::encrypt($gasto['expenseConcepts_id'])],
                    'method' => 'DELETE',
                    'class' => 'deleteForm',
                ]) !!}
                <a href="" class="delete" data-toggle="tooltip" data-placement="top" title="Eliminar registro"><i
                        class="fa-regular fa-circle-down" aria-hidden="true"></i></a>
                {!! Form::close() !!}
            @endif


        </div>
    </td>

    <td>{{ $gasto['expenseConcepts_concept'] }}</td>
    <td>{{ $gasto['expenseConcepts_category'] }}</td>
    <td>{{ $gasto['expenseConcepts_group'] }}</td>
    <td>{{ (isset($gasto['expenseConcepts_tax']) ? $gasto['expenseConcepts_tax'] : 0) . '%' }}</td>
    <td>{{ (isset($gasto['expenseConcepts_retention']) ? $gasto['expenseConcepts_retention'] : 0) . '%' }}</td>
    <td>{{ (isset($gasto['expenseConcepts_retention2']) ? $gasto['expenseConcepts_retention2'] : 0) . '%' }}</td>
    <td>{{ $gasto['expenseConcepts_exemptIVA'] }}</td>
    <td>{{ $gasto['expenseConcepts_status'] }}</td>
</tr>
