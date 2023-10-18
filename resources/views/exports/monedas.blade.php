<table>
    <thead>
        <tr>
            <th>Clave</th>
            <th>Nombre</th>
            <th>Cambio</th>
            <th>Descripción</th>
            <th>Clave SAT</th>
            <th>Estatus</th>
            <th>Fecha creación</th>
            <th>Ultima actualización</th>

        </tr>
    </thead>
    <tbody>
        @foreach ($monedas as $moneda)
            <tr>
                <td>{{ $moneda->money_key }}</td>
                <td>{{ $moneda->money_key }}</td>
                <td>{{ $moneda->money_change }}</td>
                <td>{{ $moneda->money_descript }}</td>
                <td>{{ $moneda->money_keySat }}</td>
                <td>{{ $moneda->money_status }}</td>
                <td>{{ $moneda->created_at }}</td>
                <td>{{ $moneda->updated_at }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
