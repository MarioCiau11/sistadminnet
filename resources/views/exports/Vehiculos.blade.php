<table>
    <thead>
    <tr>
        <th>Clave</th>
        <th>Nombre</th>
        <th>Placas</th>
        <th>Volumen</th>
        <th>Peso</th>
        <th>Operativo</th>
        <th>Sucursal</th>
        <th>Estatus</th>
        <th>Fecha creación</th>
        <th>Ultima actualización</th>

    </tr>
    </thead>
    <tbody>
    @foreach($vehiculos as $vehiculo)
        <tr>
            <td>{{ $vehiculo->vehicles_key}}</td>
            <td>{{ $vehiculo->vehicles_name }}</td>
            <td>{{ $vehiculo->vehicles_plates }}</td>
            <td>{{ $vehiculo->vehicles_capacityVolume }}</td>
            <td>{{ $vehiculo->vehicles_capacityWeight}}</td>
            <td>{{ $vehiculo->vehicles_defaultAgent}}</td>
            <td>{{ $vehiculo->branchOffices_name}}</td>
            <td>{{ $vehiculo->vehicles_status}}</td>
            <td>{{ $vehiculo->created_at}}</td>
            <td>{{ $vehiculo->updated_at}}</td>
        </tr>
    @endforeach
    </tbody>
</table>