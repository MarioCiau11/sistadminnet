<table>
    <thead>
    <tr>
        <th>Clave</th>
        <th>Nombre</th>
        <th>Nombre Corto</th>
        <th>Descripción</th>
        <th>Estatus</th>
        <th>Nombre logo</th>
        <th>Dirección</th>
        <th>Colonia</th>
        <th>Código Postal</th>
        <th>Ciudad</th>
        <th>Estado</th>
        <th>Pais</th>
        <th>Teléfono 1</th>
        <th>Teléfono 2</th>
        <th>Correo</th>
        <th>RFC</th>
        <th>Regimen Fiscal</th>
        <th>Registro Patronal</th>
        <th>Representante</th>
        <th>Nombre Archivo Llave-Sat</th>
        <th>Nombre Archivo Certificado-Sat</th>
        <th>Ruta de documentos</th>
        <th>Fecha creación</th>
        <th>Ultima actualización</th>

    </tr>
    </thead>
    <tbody>
    @foreach($empresas as $empresa)
        <tr>
            <td>{{ $empresa->companies_key }}</td>
            <td>{{ $empresa->companies_name }}</td>
            <td>{{ $empresa->companies_nameShort }}</td>
            <td>{{ $empresa->companies_descript }}</td>
            <td>{{ $empresa->companies_status}}</td>
            <td>{{ trim($empresa->companies_logo, ''.$empresa->companies_routeFiles)}}</td>
            <td>{{ $empresa->companies_addres}}</td>
            <td>{{ $empresa->companies_suburb}}</td>
            <td>{{ $empresa->companies_cp}}</td>
            <td>{{ $empresa->companies_city}}</td>
            <td>{{ $empresa->companies_state}}</td>
            <td>{{ $empresa->companies_country}}</td>
            <td>{{ $empresa->companies_phone1}}</td>
            <td>{{ $empresa->companies_phone2}}</td>
            <td>{{ $empresa->companies_mail}}</td>
            <td>{{ $empresa->companies_rfc}}</td>
            <td>{{ $empresa->companies_taxRegime}}</td>
            <td>{{ $empresa->companies_employerRegistration}}</td>
            <td>{{ $empresa->companies_representative}}</td>
            <td>{{ trim($empresa->companies_routeKey, ''.$empresa->companies_routeFiles.'CFDI/')}}</td>
            <td>{{ trim($empresa->companies_routeCertificate, ''.$empresa->companies_routeFiles.'CFDI/')}}</td>
            <td>{{ $empresa->companies_routeFiles}}</td>
            <td>{{ $empresa->created_at}}</td>
            <td>{{ $empresa->updated_at}}</td>
        </tr>
    @endforeach
    </tbody>
</table>