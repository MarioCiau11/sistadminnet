<table class="cabecera ancho">
    <tr>

        <td colspan="7" style="text-align: center; font-weight:bold; padding:5px">
            <h3><strong>REPORTE - COMPRAS CON SERIES </strong></h3>
        </td>

    </tr>

    <tr>

        <td colspan="7" style="text-align: right; font-weight:bold; padding:5px">
            <p><strong>Fecha de Emisión: &nbsp; </strong> {{ \Carbon\Carbon::now()->isoFormat('LL') }}</p>
        </td>

    </tr>

</table>

<?php 

$articulos_array = array();
$clientesCompras = array();
$comprasSeries = array();


foreach ($compras as $compra) {
    $articulos_array[] = $compra['purchaseDetails_descript'];
}

$articulos_array = array_unique($articulos_array);

foreach ($compras as $compra) {
    $clientesCompras[$compra['providers_key']] = $compra['providers_key']." - ".$compra['providers_name'];
}

foreach ($compras as $compra) {
    $comprasSeries[$compra['purchase_movementID']] = $compra['purchase_movementID'];
}


$clientesCompras = array_unique($clientesCompras);



// dd($articulos_array, $clientescompras, $ventas, $ventasSeries);



?>

<table class="informacion-prov2">
  

    @foreach ($articulos_array as $articulo)
    <tr>   
            <th colspan="7" style="text-align: center; font-weight:bold;"><p >{{ $articulo }}</p></th>

    </tr>
    <tr>


    </tr>
    
<table class="articulos-table2">
    <tr>
        <th>
            <p>Fecha</p>
        </th>
        <th>
            <p>Operación</p>
        </th>
        <th >
            <p>Referencia</p>
        </th>
        <th>
            <p>Cliente</p>
        </th>
        <th>
            <p>UNIDAD</p>
        </th>
        <th>
            <p>Cantidad</p>
        </th>
            <th>
            <p>Series</p>
        </th>
    </tr>

    @foreach ($compras as $key => $compra)
    @if ($compra['purchaseDetails_descript'] == $articulo)
    <tr>
        <td>
            <p>{{ \Carbon\Carbon::parse($compra['purchase_issueDate'])->format('d/m/Y') }}</p>
        </td>
        <td>
            <p>{{ $compra['purchase_movement'].'-'.$compra['purchase_movementID'] }}</p>
        </td>
        <td>
            <p>{{ $compra['purchase_reference'] }}</p>
        </td>
        <td>
            <p>{{ $compra['providers_key'].' - '.$compra['providers_name'] }}</p>
        </td>
        <td>
            <p>{{ $compra['purchaseDetails_unit'] }}</p>
        </td>
        <td>
            {{-- <p>{{ $compra['purchaseDetails_quantity'] }}</p>  --}}
            <p>1</p>
        </td>
        <td>
            <p>{{ $compra['lotSeriesMov_lotSerie'] }}</p>
        </td>
    </tr>
    @endif
    @endforeach

</table>
    @endforeach


</table>

