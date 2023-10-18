<table>
    <thead>
        <tr>
            <th>Movimiento</th>
            <th>Folio</th>
            <th>Fecha de Emisión</th>
            <th>Concepto</th>
            <th>Moneda</th>
            <th>Tipo de Cambio</th>
            <th>Fecha Vencimiento</th>
            <th>Referencia</th>
            <th>Sucursal</th>
            <th>Almacén</th>
            <th>Almacén Destino</th>
            <th>Usuario</th>
            <th>Estatus</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($inventarios as $inventario)
            <tr>
                <td>{{ $inventario['inventories_movementID'] }}</td>
                <td> {{ $inventario['inventories_movement'] }} </td>
                <td>{{ \Carbon\Carbon::parse($inventario['inventories_issuedate'])->format('d/m/Y') }}</td>
                {{-- <td>{{ $inventario['inventories_lastChange'] }}</td> --}}
                <td>{{ $inventario['inventories_concept'] }}</td>
                <td>{{ $inventario['inventories_money'] }}</td>
                <td>{{ $inventario['inventories_typeChange'] }}</td>
                <td>{{ \Carbon\Carbon::parse($inventario['inventories_expiration'])->format('d/m/Y') }}</td>
                <td>{{ $inventario['inventories_reference'] }}</td>
                {{-- <td>{{ $inventario['companies_nameShort'] }}</td> --}}
                <td>{{ $inventario['branchOffices_name'] }}</td>
                <td>{{ $inventario['depots_name'] }}</td>
                <td>{{ $inventario['depots_nameDestiny'] }}
                </td>
                <td>{{ $inventario['inventories_user'] }}</td>
                <td>{{ $inventario['inventories_status'] }}</td>
            </tr>
               
                @endforeach
    </tbody>
</table>