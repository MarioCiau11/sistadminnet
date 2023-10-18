<table>
    <thead>
    <tr>
        <th>Clave</th>
        <th>Nombre</th>
        <th>Tipo</th>
        <th>Categoría</th>
        <th>Grupo</th>
        <th>Sucursal</th>
        <th>Estatus</th>
        <th>Fecha creación</th>
        <th>Ultima actualización</th>

    </tr>
    </thead>
    <tbody>
    @foreach($agentes as $agente)
        <tr>
            <td>{{ $agente->agents_key}}</td>
            <td>{{ $agente->agents_name }}</td>
            <td>{{ $agente->agents_type }}</td>
            <td>{{ $agente->agents_category }}</td>
            <td>{{ $agente->agents_group }}</td>
            <td>{{ $agente->branchOffices_name}}</td>
            <td>{{ $agente->agents_status}}</td>
            <td>{{ $agente->created_at}}</td>
            <td>{{ $agente->updated_at}}</td>
        </tr>
    @endforeach
    </tbody>
</table>