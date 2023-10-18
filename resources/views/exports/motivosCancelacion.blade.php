<table>
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Módulo</th>
        <th>Estatus</th>
        <th>Fecha de Creación</th>
        <th>Ultima Actualización</th>

    </tr>
    </thead>
    <tbody>
    @foreach($motivo as $motivo)
        <tr>
            <td>{{ $motivo->reasonCancellations_name}}</td>
            <td>{{ $motivo->reasonCancellations_module}}</td>
            <td>{{ $motivo->reasonCancellations_status}}</td>
            <td>{{ $motivo->created_at}}</td>
            <td>{{ $motivo->updated_at}}</td>
        </tr>
    @endforeach
    </tbody>
</table>