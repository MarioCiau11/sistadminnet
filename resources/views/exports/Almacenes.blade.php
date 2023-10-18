<table>
    <thead>
    <tr>
        <th>Clave</th>
        <th>Nombre</th>
        <th>Sucursal</th>
        <th>Tipo</th>
        <th>Estatus</th>
        <th>Fecha creación</th>
        <th>Ultima actualización</th>

    </tr>
    </thead>
    <tbody>
    @foreach($almacenes as $almacen)
        <tr>
            <td>{{ $almacen->depots_key}}</td>
            <td>{{ $almacen->depots_name }}</td>
            <td>{{ $almacen->branchOffices_name }}</td>
            <td>{{ $almacen->depots_type }}</td>
            <td>{{ $almacen->depots_status}}</td>
            <td>{{ $almacen->created_at}}</td>
            <td>{{ $almacen->updated_at}}</td>
        </tr>
    @endforeach
    </tbody>
</table>