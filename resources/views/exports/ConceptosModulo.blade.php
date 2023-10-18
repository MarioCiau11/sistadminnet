<table>
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Proceso</th>
        <th>Estatus</th>
        <th>Fecha de Creación</th>
        <th>Ultima Actualización</th>

    </tr>
    </thead>
    <tbody>
    @foreach($concepto as $concepto)
        <tr>
            <td>{{ $concepto->moduleConcept_name}}</td>
            <td>{{ $concepto->moduleConcept_module}}</td>
            <td>{{ $concepto->moduleConcept_status}}</td>
            <td>{{ $concepto->created_at}}</td>
            <td>{{ $concepto->updated_at}}</td>
        </tr>
    @endforeach
    </tbody>
</table>