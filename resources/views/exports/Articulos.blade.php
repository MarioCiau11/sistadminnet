<table>
    <thead>
        <tr>
            <th>Clave</th>
            <th>Tipo</th>
            <th>Descripción 1</th>
            <th>Descripción 2</th>
            <th>Categoria</th>
            <th>Grupo</th>
            <th>Familia</th>
            <th>Unidad de Medida / Como se compra</th>
            <th>Unidad de Medida / Como se vende</th>
            <th>Unidad de Medida / Como se traspasa entre sucursales</th>
            <td>Lista 1/Precio Lista</td>
            <td>Lista 2/Precio Lista 2</td>
            <td>Lista 3/Precio Lista 3</td>
            <td>Lista 4/Precio Lista 4</td>
            <td>Lista 5/Precio Lista 5</td>
            <th>Iva</th>
            <th>Estatus</th>
            <th>Fecha de creación</th>
            <th>Fecha de actualización</th>

        </tr>
    </thead>
    <tbody>
        @foreach ($articulos as $articulo)
            <tr>
                <td>{{ $articulo->articles_key }}</td>
                <td>{{ $articulo->articles_type == 'Normal' ? 'Producto' : ($articulo->articles_type == 'Serie' ? 'Serializado' : ($articulo->articles_type == 'Kit' ? 'Kit/Combo' : 'Servicio')) }}</td>
                <td>{{ $articulo->articles_descript }}</td>
                <td>{{ $articulo->articles_descript2 }}</td>
                <td>{{ $articulo->articles_category }}</td>
                <td>{{ $articulo->articles_group }}</td>
                <td>{{ $articulo->articles_family }}</td>
                <td>{{ $unidad[$articulo->articles_unitBuy] }}</td>
                <td>{{ $unidad[$articulo->articles_unitSale] }}</td>
                <td>{{ $unidad[$articulo->articles_transfer] }}</td>
                <td>{{ '$' . number_format($articulo->articles_listPrice1, 2) }}</td>
                <td>{{ '$' . number_format($articulo->articles_listPrice2, 2) }}</td>
                <td>{{ '$' . number_format($articulo->articles_listPrice3, 2) }}</td>
                <td>{{ '$' . number_format($articulo->articles_listPrice4, 2) }}</td>
                <td>{{ '$' . number_format($articulo->articles_listPrice5, 2) }}</td>
                <td>{{ number_format($articulo->articles_porcentIva, 2) . '%' }}</td>
                <td>{{ $articulo->articles_status }}</td>
                <td>{{ $articulo->created_at }}</td>
                <td>{{ $articulo->updated_at }}</td>

            </tr>
        @endforeach
    </tbody>
</table>
