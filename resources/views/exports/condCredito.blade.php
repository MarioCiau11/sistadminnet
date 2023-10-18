<table>
    <thead>
    <tr>
        <th>Id</th>
        <th>Nombre del Término</th>
        <th>Tipo de Término</th>
        <th>Días vencimiento</th>
        <th>Tipo días</th>
        <th>Días hábiles</th>
        <th>Método de pago</th>
        <th>Estatus</th>
        <th>Fecha creación</th>
        <th>Ultima actualización</th>

    </tr>
    </thead>
    <tbody>
    @foreach($condCreditos as $condCredito)
        <tr>
            <td>{{$condCredito['creditConditions_id']}}</td>
            <td>{{$condCredito['creditConditions_name']}}</td>
            <td>{{$condCredito['creditConditions_type']}}</td>
            <td>{{$condCredito['creditConditions_days']}}</td>
            <td>{{$condCredito['creditConditions_typeDays']}}</td>
            <td>{{$condCredito['creditConditions_workDays']}}</td>
            <td>{{$condCredito['creditConditions_paymentMethod']}}</td>
            <td>{{$condCredito['creditConditions_status']}}</td>
            <td>{{$condCredito['created_at']}}</td>
            <td>{{$condCredito['updated_at']}}</td>
        </tr>
    @endforeach
    </tbody>
</table>