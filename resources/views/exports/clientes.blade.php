<table>
    <thead>
    <tr>
        <th>Clave</th>
        <th>Tipo</th>
        <th>Razon Social</th>
        <th>RFC</th>
        <th>CURP</th>
        <th>Nombre</th>
        <th>Apellido Paterno</th>
        <th>Apellido Materno</th>
        <th>Celular</th>
        <th>Correo</th>
        <th>Nombre</th>
        <th>Dirección</th>
        <th>Avenidas</th>
        <th>Numero exterior</th>
        <th>Numero interior</th>
        <th>Colonia / Fraccionamiento</th>
        <th>Localidad / Municipio</th>
        <th>Estado</th>
        <th>Pais</th>
        <th>Código Postal</th>
        <th>Teléfono 1</th>
        <th>Contacto 1</th>
        <th>Correo 1</th>
        <th>Teléfono 2</th>
        <th>Contacto 2</th>
        <th>Correo 2</th>
        <th>Observaciones</th>
        <th>Grupo</th>
        <th>Categoria</th>
        <th>Estatus</th>
        <th>Precio de lista</th>
        <th>Condicion de pago</th>
        <th>Limite de credito</th>
        <th>Identificador de CFDI</th>
        <th>Regimen fiscal</th>
        <th>Fecha creación</th>
        <th>Ultima actualización</th>

    </tr>
    </thead>
    <tbody>
    @foreach($clientes as $cliente)
        <tr>
            <td>{{ $cliente->customers_key }}</td>
            <td>{{ $cliente->customers_type == 0 ? 'Fisica' : 'Moral' }}</td>
            <td>{{ $cliente->customers_businessName }}</td>
            <td>{{ $cliente->customers_RFC }}</td>
            <td>{{ $cliente->customers_CURP}}</td>
            <td>{{ $cliente->customers_name}}</td>
            <td>{{ $cliente->customers_lastName}}</td>
            <td>{{ $cliente->customers_lastName2}}</td>
            <td>{{ $cliente->customers_cellphone}}</td>
            <td>{{ $cliente->customers_mail}}</td>
            <td>{{ $cliente->customers_addres}}</td>
            <td>{{ $cliente->customers_roads}}</td>
            <td>{{ $cliente->customers_outdoorNumber}}</td>
            <td>{{ $cliente->customers_interiorNumber}}</td>
            <td>{{ $cliente->customers_colonyFractionation}}</td>
            <td>{{ $cliente->customers_townMunicipality}}</td>
            <td>{{ $cliente->customers_state}}</td>
            <td>{{ $cliente->customers_country}}</td>
            <td>{{ $cliente->customers_cp}}</td>
            <td>{{ $cliente->customers_phone1}}</td>
            <td>{{ $cliente->customers_contact1}}</td>
            <td>{{ $cliente->customers_mail1}}</td>
            <td>{{ $cliente->customers_phone2}}</td>
            <td>{{ $cliente->customers_contact2}}</td>
            <td>{{ $cliente->customers_mail2}}</td>
            <td>{{ $cliente->customers_observations}}</td>
            <td>{{ $cliente->customers_group}}</td>
            <td>{{ $cliente->customers_category}}</td>
            <td>{{ $cliente->customers_status}}</td>
            <td>{{ $cliente->customers_priceList}}</td>
            <td>{{ $cliente->customers_creditLimit}}</td>
            <td>{{ $cliente->customers_identificationCFDI}}</td>
            <td>{{ $cliente->customers_taxRegime}}</td>
            <td>{{ $cliente->created_at}}</td>
            <td>{{ $cliente->updated_at}}</td>
        </tr>
    @endforeach
    </tbody>
</table>