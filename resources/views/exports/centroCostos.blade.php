<table>
    <thead>
    <tr>
        <th>Clave</th>
        <th>Nombre</th>
        <th>Estatus</th>
        <th>Fecha creación</th>
        <th>Ultima actualización</th>

    </tr>
    </thead>
    <tbody>
    @foreach($centroCostos as $centroCosto)
        <tr>
            <td>{{ $centroCosto->costCenter_key }}</td>
            <td>{{ $centroCosto->costCenter_name }}</td>
            <td>{{ $centroCosto->costCenter_status }}</td>
            <td>{{ $centroCosto->created_at}}</td>
            <td>{{ $centroCosto->updated_at}}</td>
        </tr>
    @endforeach
    </tbody>
</table>