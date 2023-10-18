<table>
    <thead>
    <tr>
        <th>Clave</th>
        <th>Nombre</th>
        <th>Nombre Empresa</th>
        <th>Dirección</th>
        <th>Colonia</th>
        <th>Código Postal</th>
        <th>Ciudad</th>
        <th>Estado</th>
        <th>Pais</th>
        <th>Estatus</th>
        <th>Fecha de Creación</th>
        <th>Ultima Actualización</th>

    </tr>
    </thead>
    <tbody>
    @foreach($sucursales as $sucursal)
        <tr>
            <td>{{ $sucursal->branchOffices_key }}</td>
            <td>{{ $sucursal->branchOffices_name}}</td>
            <td>{{ $sucursal->companies_name}}</td> 
            <td>{{ $sucursal->branchOffices_addres}}</td>
            <td>{{ $sucursal->branchOffices_suburb}}</td> 
            <td>{{ $sucursal->branchOffices_cp}}</td>  
            <td>{{ $sucursal->branchOffices_city}}</td> 
            <td>{{ $sucursal->branchOffices_state}}</td>
            <td>{{ $sucursal->branchOffices_country}}</td>
            <td>{{ $sucursal->branchOffices_status}}</td>
            <td>{{ $sucursal->created_at}}</td>
            <td>{{ $sucursal->updated_at}}</td>  
        </tr>
    @endforeach
    </tbody>
</table>