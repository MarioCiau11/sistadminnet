<table>
    <thead>
    <tr>
        <th>Clave</th>
        <th>Nombre</th>
        <th>Nombre Corto</th>
        <th>RFC</th>
        <th>CURP</th>
        <th>Tipo</th>
        <th>Estatus</th>
        <th>Dirección</th>
        <th>Avenidas</th>
        <th>Numero exterior</th>
        <th>Numero interior</th>
        <th>Colonia</th>
        <th>Municipio</th>
        <th>Estado</th>
        <th>Pais</th>
        <th>Código Postal</th>
        <th>Observaciones</th>
        <th>Teléfono 1</th>
        <th>Teléfono 2</th>
        <th>Celular</th>
        <th>Contacto 1</th>
        <th>Correo 1</th>
        <th>Contacto 2</th>
        <th>Correo 2</th>
        <th>Grupo</th>
        <th>Categoria</th>
        <th>Condicion de credito</th>
        <th>Forma de pago</th>
        <th>Moneda</th>
        <th>Regimen fiscal</th>
        <th>Cuenta bancaria</th>
        <th>Fecha creación</th>
        <th>Ultima actualización</th>

    </tr>
    </thead>
    <tbody>
    @foreach($proveedores as $proveedor)
        <tr>
            <td>{{ $proveedor->providers_key }}</td>
            <td>{{ $proveedor->providers_name }}</td>
            <td>{{ $proveedor->providers_nameShort }}</td>
            <td>{{ $proveedor->providers_RFC }}</td>
            <td>{{ $proveedor->providers_CURP}}</td>
            <td>{{ $proveedor->providers_type}}</td>
            <td>{{ $proveedor->providers_status}}</td>
            <td>{{ $proveedor->providers_address}}</td>
            <td>{{ $proveedor->providers_roads}}</td>
            <td>{{ $proveedor->providers_outdoorNumber}}</td>
            <td>{{ $proveedor->providers_interiorNumber}}</td>
            <td>{{ $proveedor->providers_colonyFractionation}}</td>
            <td>{{ $proveedor->providers_townMunicipality}}</td>
            <td>{{ $proveedor->providers_state}}</td>
            <td>{{ $proveedor->providers_country}}</td>
            <td>{{ $proveedor->providers_cp}}</td>
            <td>{{ $proveedor->providers_observations}}</td>
            <td>{{ $proveedor->providers_phone1}}</td>
            <td>{{ $proveedor->providers_phone2}}</td>
            <td>{{ $proveedor->providers_cellphone}}</td>
            <td>{{ $proveedor->providers_contact1}}</td>
            <td>{{ $proveedor->providers_mail1}}</td>
            <td>{{ $proveedor->providers_contact2}}</td>
            <td>{{ $proveedor->providers_mail2}}</td>
            <td>{{ $proveedor->providers_group}}</td>
            <td>{{ $proveedor->providers_category}}</td>
            <td>{{ $proveedor->providers_creditCondition}}</td>
            <td>{{ $proveedor->providers_formPayment}}</td>
            <td>{{ $proveedor->providers_money}}</td>
            <td>{{ $proveedor->providers_taxRegime}}</td>
            <td>{{ $proveedor->providers_bankAccount}}</td>
            <td>{{ $proveedor->created_at}}</td>
            <td>{{ $proveedor->updated_at}}</td>
        </tr>
    @endforeach
    </tbody>
</table>