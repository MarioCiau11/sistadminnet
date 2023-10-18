<table>
    <thead>
    <tr>
        <th>Clave</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Moneda</th>
        <th>Forma de pago</th>
        <th>Estatus</th>
        <th>Fecha creación</th>
        <th>Ultima actualización</th>

    </tr>
    </thead>
    <tbody>
    @foreach($formaPagos as $formaPago)
        <tr>
            <td>{{ $formaPago->formsPayment_key }}</td>
            <td>{{ $formaPago->formsPayment_name }}</td>
            <td>{{ $formaPago->formsPayment_descript }}</td>
            <td>{{ $formaPago->formsPayment_money}}</td>
            <td>{{ $formaPago->formsPayment_sat}}</td>
            <td>{{ $formaPago->formsPayment_status}}</td>
            <td>{{ $formaPago->created_at}}</td>
            <td>{{ $formaPago->updated_at}}</td>
        </tr>
    @endforeach
    </tbody>
</table>