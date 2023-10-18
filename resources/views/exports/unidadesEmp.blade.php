<table>
    <thead>
    <tr>
        <th>Unidad Empaque</th>
        <th>Peso</th>
        <th>Unidad</th>
        <th>Estatus</th>
        <th>Fecha creación</th>
        <th>Ultima actualización</th>

    </tr>
    </thead>
    <tbody>
    @foreach($unidadesEmp as $unidad)
        <tr>
            <td>{{ $unidad->packaging_units_packaging }}</td>
            <td>{{ $unidad->packaging_units_weight }}</td>
            <td>{{ $unidad->packaging_units_unit }}</td>
            <td>{{ $unidad->packaging_units_status }}</td>
            <td>{{ $unidad->created_at }}</td>
            <td>{{ $unidad->updated_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>