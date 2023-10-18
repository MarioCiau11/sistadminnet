<table>
    <thead>
    <tr>
        <th>Unidad</th>
        <th>Decimal Valida</th>
        <th>Clave Sat</th>
        <th>Estatus</th>
        <th>Fecha creación</th>
        <th>Ultima actualización</th>

    </tr>
    </thead>
    <tbody>
    @foreach($unidades as $unidad)
        <tr>
            <td>{{ $unidad->units_unit }}</td>
            <td>{{ $unidad->units_decimalVal }}</td>
            <td>{{ $unidad->nombre }}</td>
            <td>{{ $unidad->units_status }}</td>
            <td>{{ $unidad->created_at }}</td>
            <td>{{ $unidad->updated_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>