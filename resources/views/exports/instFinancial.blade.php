<table>
    <thead>
    <tr>
        <th>Clave</th>
        <th>Nombre</th>
        <th>Ciudad</th>
        <th>Estado</th>
        <th>Pais</th>
        <th>Estatus</th>
    </tr>
    </thead>
    <tbody>
    @foreach($instituciones as $institucion)
        <tr>
            <td>{{$institucion->instFinancial_key}}</td>
            <td>{{$institucion->instFinancial_name}}</td>
            <td>{{$institucion->instFinancial_city}}</td>
            <td>{{$institucion->instFinancial_state}}</td>
            <td>{{$institucion->instFinancial_country}}</td>
            <td>{{$institucion->instFinancial_status}}</td>
            
        </tr>
    @endforeach
    </tbody>
</table>