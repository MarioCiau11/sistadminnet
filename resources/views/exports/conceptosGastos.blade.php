<table>
    <thead>
        <tr>
            <th>Razón</th>
            <th>Impuesto</th>
            <th>Retencion</th>
            <th>Retencion 2</th>
            <th>Retencion 3</th>
            <th>Excento IVA</th>
            <th>Grupo</th>
            <th>Categoria</th>
            <th>Estatus</th>
            <th>Fecha creación</th>
            <th>Ultima actualización</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($conceptos as $concepto)
            <tr>
                <td>{{ $concepto->expenseConcepts_concept }}</td>
                <td>{{ ($concepto->expenseConcepts_tax !== null ? $concepto->expenseConcepts_tax : 0) . ' %' }}</td>
                <td>{{ ($concepto->expenseConcepts_retention !== null ? $concepto->expenseConcepts_retention : 0) . ' %' }}
                </td>
                <td>{{ ($concepto->expenseConcepts_retention2 !== null ? $concepto->expenseConcepts_retention2 : 0) . ' %' }}
                </td>
                <td>{{ ($concepto->expenseConcepts_retention3 !== null ? $concepto->expenseConcepts_retention3 : 0) . ' %' }}
                </td>
                <td>{{ $concepto->expenseConcepts_exemptIVA == 0 ? 'No' : 'Si' }}</td>
                <td>{{ $concepto->expenseConcepts_group }}</td>
                <td>{{ $concepto->expenseConcepts_category }}</td>
                <td>{{ $concepto->expenseConcepts_status }}</td>
                <td>{{ $concepto->created_at }}</td>
                <td>{{ $concepto->updated_at }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
