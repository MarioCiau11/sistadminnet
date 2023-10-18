<table class="ancho">
    <tr>
        <td colspan="8" style="text-align: center; font-weight:bold; padding:5px">
            <p><strong>Gastos por Antecedentes o Activo Fijo</strong></p>
        </td>
    </tr>

</table>

<table>
    <thead>
        <tr>
            <td>Movimiento</td>
            <td>Proveedor</td>
            <td>Concepto</td>
            <td>Sucursal</td>
            <td>Fecha</td>
            <td>Antecedente/Activo Fijo</td>
            <td>Moneda</td>
            <td>Estatus</td>
        </tr>
    </thead>
    <tbody>
        @foreach ($gastos as $gasto)
        <tr>
                <td>           
                    {{ $gasto['expenses_movement'] }}
                </td>
                <td>{{ $gasto['providers_name'] }}</td>
                <td>{{ $gasto['expensesDetails_concept'] }}</td>
                <td>{{ $gasto['branchOffices_name'] }}</td>
                <td>{{ \Carbon\Carbon::parse($gasto['expenses_issueDate'])->format('d/m/Y') }}</td>
                <td> {{ $gasto['expenses_antecedentsName'] || $gasto['expenses_fixedAssetsName'] == '' ? $gasto['expenses_antecedentsName'] : $gasto['expenses_fixedAssetsName'] }}</td>
                <td>{{ $gasto['expenses_money'] }}</td>
                <td>{{ $gasto['expenses_status'] }}</td>
            
        </tr>
        @endforeach
    </tbody>
</table>