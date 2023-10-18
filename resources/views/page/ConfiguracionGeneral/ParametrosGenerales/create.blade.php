@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open(['route' => 'configuracion.parametros-generales.store', 'id' => 'basicForm']) !!}




                    <div class="col-md-12">
                    </div>

                    <h2 class="text-black">Configuración general del sistema</h2>



                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('diasHabiles', 'Dias Hábiles', ['class' => 'negrita']) !!}

                            {!! Form::select(
                                'diasHabiles',
                                ['Lun-Vie' => 'Lun-Vie', 'Lun-Sab' => 'Lun-Sab', 'Todos' => 'Todos'],
                                isset($parametro) ? $parametro['generalParameters_businessDays'] : null,
                                [
                                    'id' => 'select-search-hide-diasHabiles',
                                    'class' => 'widthAll select-status',
                                    'placeholder' => 'Seleccione uno...',
                                ],
                            ) !!}
                        </div>
                    </div>

                    <?php
                    $factura = isset($parametro) ? $parametro['generalParameters_billsNot'] : 0;
                    if ($factura == 1) {
                        $factura = true;
                    } else {
                        $factura = false;
                    }
                    
                    ?>
                    <div class="col-md-3">
                        <div class="form-group exento-iva">
                            <div>
                                {!! Form::label('facturasSin', 'Facturas (sin Existencia)', ['class' => 'negrita']) !!}
                                {!! Form::checkbox('facturasSin', true, $factura, ['class' => 'form-control ', 'id' => 'checkboxPrimary']) !!}
                            </div>
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-md-6">
                                {!! Form::label('ejercicioInicia', 'Ejercicio inicia', ['class' => 'negrita']) !!}
                                <input type="text" name="ejercicioInicia" class="form-control" placeholder="dd/mm/yyyy"
                                    id="datepickerInicia"
                                    value={{ isset($parametro) ? $parametro['generalParameters_exerciseStarts'] : null }}>
                                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                            </div>

                            <div class="col-md-6">
                                {!! Form::label('ejercicioTermina', 'Ejercicio termina', ['class' => 'negrita']) !!}
                                <input type="text" name="ejercicioTermina" class="form-control" placeholder="dd/mm/yyyy"
                                    id="datepickerTermina"
                                    value={{ isset($parametro) ? $parametro['generalParameters_exerciseEnds'] : null }}>
                                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('monedaPredeterminada', 'Moneda Predeterminada', ['class' => 'negrita']) !!}

                            {!! Form::select(
                                'monedaPredeterminada',
                                $monedas,
                                isset($parametro) ? $parametro['generalParameters_defaultMoney'] : null,
                                ['id' => 'select-search-hide-moneda', 'class' => 'widthAll select-status', 'placeholder' => 'Seleccione uno...'],
                            ) !!}
                        </div>
                    </div>
                    <div class="col-md-12">
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('especifications', 'Términos y condiciones: Reporte - Cotización', ['class' => 'negrita']) !!}
                            {!! Form::textarea(
                                'especifications',
                                isset($parametro) ? $parametro['generalParameters_termsConditionsReportQuote'] : null,
                                [
                                    'class' => 'form-control',
                                    'id' => 'especifications',
                                    'autocomplete' => 'on',
                                ],
                            ) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('especifications2', 'Términos y condiciones: Reporte - Nota de venta', ['class' => 'negrita']) !!}
                            {!! Form::textarea(
                                'especifications2',
                                isset($parametro) ? $parametro['generalParameters_termsConditionsReportSalesNote'] : null,
                                [
                                    'class' => 'form-control',
                                    'id' => 'especifications2',
                                    'autocomplete' => 'on',
                                ],
                            ) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('especifications3', 'Términos y condiciones: Reporte - Formato de Entrega', [
                                'class' => 'negrita',
                            ]) !!}
                            {!! Form::textarea(
                                'especifications3',
                                isset($parametro) ? $parametro['generalParameters_termsConditionsReportDeliveryFormat'] : null,
                                [
                                    'class' => 'form-control',
                                    'id' => 'especifications3',
                                    'autocomplete' => 'on',
                                ],
                            ) !!}
                        </div>
                    </div>
                    <div class="col-md-12">
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('especifications4', 'Texto descriptivo para los Reportes por Módulo', ['class' => 'negrita']) !!}
                            {!! Form::textarea('especifications4', isset($parametro) ? $parametro['generalParameters_defaultText'] : null, [
                                'class' => 'form-control',
                                'id' => 'especifications4',
                                'autocomplete' => 'on',
                                'placeholder' => 'Ejemplo: Gracias por hacer negocios con nosotros!',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="active"><a href="#home" aria-controls="home" role="tab"
                                            data-toggle="tab">Compras</a></li>
                                    <li><a href="#profile" aria-controls="profile" role="tab"
                                            data-toggle="tab">Inventarios</a></li>
                                    <li><a href="#contact" aria-controls="contact" role="tab"
                                            data-toggle="tab">Ventas</a></li>
                                    <li><a href="#cxp" aria-controls="cxp" role="tab" data-toggle="tab">CXP</a></li>
                                    <li><a href="#cxc" aria-controls="cxc" role="tab" data-toggle="tab">CXC</a></li>
                                    <li><a href="#gastos" aria-controls="gastos" role="tab" data-toggle="tab">Gastos</a>
                                    </li>
                                    <li><a href="#tesoreria" aria-controls="tesoreria" role="tab"
                                            data-toggle="tab">Tesorería</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane active" id="home">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th class="table-head">Movimiento</th>
                                                    <th class="table-des">Último Consecutivo</th>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consOrdenCompra', 'Orden de Compra', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consOrdenCompra',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consOrderPurchase'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consOrderPurchase']
                                                                : $consOrdenEntrada,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consOrdenCompra'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consEntradaCompra', 'Entrada por Compra', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consEntradaCompra',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consEntryPurchase'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consEntryPurchase']
                                                                : $consOrdenEntrada,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consEntradaCompra'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="profile">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th class="table-head">Movimiento</th>
                                                    <th class="table-des">Último Consecutivo</th>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consAjuste', 'Ajuste de Inventario', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consAjuste',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consAdjustment'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consAdjustment']
                                                                : $consAjuste,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consAjuste'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consTransferencia', 'Transferencia entre Alm.', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::text(
                                                            'consTransferencia',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consTransfer'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consTransfer']
                                                                : $consTransferenciaAlmacen,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consTransferencia'],
                                                        ) !!}

                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="contact">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th class="table-head">Movimiento</th>
                                                    <th class="table-des">Último Consecutivo</th>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consCotizacion', 'Cotización', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consCotizacion',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consQuotation'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consQuotation']
                                                                : $consCotizacion,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consCotizacion'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consPedido', 'Pedido', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consPedido',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consDemand'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consDemand']
                                                                : $consPedido,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consPedido'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consFactura', 'Factura', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consFactura',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consBill'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consBill']
                                                                : $consFactura,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consFactura'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="cxp">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th class="table-head">Movimiento</th>
                                                    <th class="table-des">Último Consecutivo</th>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consAnticipo', 'Anticipo', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consAnticipo',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consAdvance'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consAdvance']
                                                                : $consAnticipo,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consAnticipo'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consAplicacion', 'Aplicación', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consAplicacion',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consApplication'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consApplication']
                                                                : $consAplicacion,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consAplicacion'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consPago', 'Pago de Facturas', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consPago',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consPayment'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consPayment']
                                                                : $consPago,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consPago'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="cxc">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th class="table-head">Movimiento</th>
                                                    <th class="table-des">Último Consecutivo</th>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consAnticipoCXC', 'Anticipo', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consAnticipoCXC',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consAdvanceCXC'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consAdvanceCXC']
                                                                : $consAnticipoCXC,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consAnticipoCXC'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consAplicacionCXC', 'Aplicación', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consAplicacionCXC',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consApplicationCXC'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consApplicationCXC']
                                                                : $consAplicacionCXC,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consAplicacionCXC'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consDevolucionAnticipo', 'Devolución Anticipo', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consDevolucionAnticipo',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consReturnAdvance'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consReturnAdvance']
                                                                : $consDevolucionAnticipo,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consDevolucionAnticipo'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consCobro', 'Cobro', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consCobro',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consCollection'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consCollection']
                                                                : $consCobro,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consCobro'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="gastos">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th class="table-head">Movimiento</th>
                                                    <th class="table-des">Último Consecutivo</th>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consGasto', 'Factura de Gasto', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consGasto',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consExpense'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consExpense']
                                                                : $consGasto,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consGasto'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consCajaChica', 'Reposición Caja', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consCajaChica',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consPettyCash'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consPettyCash']
                                                                : $consCajaChica,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consCajaChica'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="tesoreria">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th class="table-head">Movimiento</th>
                                                    <th class="table-des">Último Consecutivo</th>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consTransferenciaT', 'Traspaso Cuentas', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consTransferenciaT',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consTransferT'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consTransferT']
                                                                : $consTransferenciaT,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consTransferenciaT'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="table-head">
                                                        {!! Form::label('consEgreso', 'Egreso', ['class' => 'negrita']) !!}
                                                    </td>
                                                    <td class="table-des">
                                                        {!! Form::number(
                                                            'consEgreso',
                                                            isset($parametro->consecutivos) && $parametro->consecutivos['generalConsecutives_consEgress'] !== null
                                                                ? $parametro->consecutivos['generalConsecutives_consEgress']
                                                                : $consEgreso,
                                                            ['class' => 'form-control cons-input', 'data-cons' => 'consEgreso'],
                                                        ) !!}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="col-md-6">
                        <h2 class="text-black">Bóveda de documentos de clientes</h2>
                        <div class="form-group mt10">
                            {!! Form::label('rutaDocumentosClientes', 'Ruta Bóveda Digital', ['class' => 'negrita']) !!}
                            {!! Form::text(
                                'rutaDocumentosClientes',
                                isset($parametro) ? $parametro['generalParameters_filesCustomers'] : null,
                                ['class' => 'form-control', 'placeholder' => 'cliente/clienteClave'],
                            ) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h2 class="text-black">Bóveda de documentos de proveedores</h2>
                        <div class="form-group mt10">
                            {!! Form::label('rutaDocumentosProveedores', 'Ruta Bóveda Digital', ['class' => 'negrita']) !!}
                            {!! Form::text(
                                'rutaDocumentosProveedores',
                                isset($parametro) ? $parametro['generalParameters_filesProviders'] : null,
                                ['class' => 'form-control', 'placeholder' => 'proveedor/proveedorClave'],
                            ) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h2 class="text-black">Bóveda de documentos de movimientos</h2>
                        <div class="form-group mt10">
                            {!! Form::label('rutaDocumentosMovimientos', 'Ruta Bóveda Digital', ['class' => 'negrita']) !!}
                            {!! Form::text(
                                'rutaDocumentosMovimientos',
                                isset($parametro) ? $parametro['generalParameters_filesMovements'] : null,
                                ['class' => 'form-control', 'placeholder' => '/SERVER/EMPRESA/MODULO/EJERCICIO/PERIODO'],
                            ) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h2 class="text-black">Bóveda de fotos de artículos</h2>
                        <div class="form-group mt10">
                            {!! Form::label('rutaFotosArticulos', 'Ruta Bóveda Digital', ['class' => 'negrita']) !!}
                            {!! Form::text('rutaFotosArticulos', isset($parametro) ? $parametro['generalParameters_filesArticles'] : null, [
                                'class' => 'form-control',
                                'placeholder' => 'ARTICULO/FOTO',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-12 mt20 display-center">
                        {!! Form::submit('Guardar parámetros generales', ['class' => 'btn btn-success enviar']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    @include ('include.mensaje')

    <script>
        //ahora hacemos que cuando le de clic en el botón de guardar aparezca el loader
        jQuery(".enviar").click(function() {
            //solo mostrar el loader si los campos están validados
            if (jQuery("#basicForm").valid()) {
                jQuery("#loader").show();
            }
        });


        jQuery(document).ready(function() {

            const consecutivos = {
                consOrdenCompra: parseInt('{{ $consOrdenEntrada }}'),
                consEntradaCompra: parseInt('{{ $consEntrada }}'),
                consAjuste: parseInt('{{ $consAjuste }}'),
                consTransferencia: parseInt('{{ $consTransferenciaAlmacen }}'),
                consCotizacion: parseInt('{{ $consCotizacion }}'),
                consPedido: parseInt('{{ $consPedido }}'),
                consFactura: parseInt('{{ $consFactura }}'),
                consAnticipo: parseInt('{{ $consAnticipo }}'),
                consAplicacion: parseInt('{{ $consAplicacion }}'),
                consPago: parseInt('{{ $consPago }}'),
                consAnticipoCXC: parseInt('{{ $consAnticipoCXC }}'),
                consAplicacionCXC: parseInt('{{ $consAplicacionCXC }}'),
                consDevolucionAnticipo: parseInt('{{ $consDevolucionAnticipo }}'),
                consCobro: parseInt('{{ $consCobro }}'),
                consGasto: parseInt('{{ $consGasto }}'),
                consCajaChica: parseInt('{{ $consCajaChica }}'),
                consTransferenciaT: parseInt('{{ $consTransferenciaT }}'),
                consEgreso: parseInt('{{ $consEgreso }}'),
            };

            //validamos que el consecutivo que se ingrese sea mayor al de las variables declaradas. Si pone un número menor salta un error
            $('.cons-input').on('change', function() {
                const input = parseInt($(this).val());
                const variableName = $(this).data('cons');

                if (input < consecutivos[variableName]) {
                    swal({
                        title: 'Error',
                        text: 'El consecutivo no puede ser menor al que ya está establecido',
                        icon: 'error',
                        button: 'Aceptar',
                    });
                    $(this).val(consecutivos[variableName]);
                }
            });


            //validamos que todos los campos de consecutivos solo acepten números enteros
            jQuery(
                    '#consOrdenCompra, #consEntradaCompra, #consAjuste, #consTransferencia, #consCotizacion, #consPedido, #consFactura, #consAnticipo, #consAplicacion, #consPago, #consAnticipoCXC, #consAplicacionCXC, #consDevolucionAnticipo, #consCobro, #consGasto, #consCajaChica, #consTransferenciaT, #consEgreso')
                .on('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            jQuery('#datepickerInicia').attr('readonly', 'readonly');
            jQuery('#datepickerTermina').attr('readonly', 'readonly');




            jQuery('#datepickerInicia').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                startDate: '-3d'
            });

            jQuery('#datepickerTermina').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                startDate: '-3d'
            });

            jQuery(
                    '#select-search-hide-tiposDias, #select-search-tipoCredito, #select-search-hide-diasHabiles,#select-search-hide-status, #select-search-hide-moneda'
                )
                .select2({
                    minimumResultsForSearch: -1
                });

            $('#select-search-hide-moneda').val(
                '{{ isset($parametro) ? $parametro['generalParameters_defaultMoney'] : '' }}').trigger(
                'change.select2');

            ClassicEditor
                .create(document.querySelector('#especifications'), {
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'bold',
                            'italic',
                            'link',
                            'bulletedList',
                            'numberedList',
                            'blockQuote',
                            '|',
                            'undo',
                            'redo'
                        ]
                    },
                    language: 'es',
                    removeButtons: 'Image, MediaEmbed' // Aquí se especifican los botones a remover

                })
                .catch(error => {
                    console.error(error);
                });
            ClassicEditor
                .create(document.querySelector('#especifications2'), {
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'bold',
                            'italic',
                            'link',
                            'bulletedList',
                            'numberedList',
                            'blockQuote',
                            '|',
                            'undo',
                            'redo'
                        ]
                    },
                    language: 'es',
                    removeButtons: 'Image, MediaEmbed' // Aquí se especifican los botones a remover

                })
                .catch(error => {
                    console.error(error);
                });
            ClassicEditor
                .create(document.querySelector('#especifications3'), {
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'bold',
                            'italic',
                            'link',
                            'bulletedList',
                            'numberedList',
                            'blockQuote',
                            '|',
                            'undo',
                            'redo'
                        ]
                    },
                    language: 'es',
                    removeButtons: 'Image, MediaEmbed' // Aquí se especifican los botones a remover

                })
                .catch(error => {
                    console.error(error);
                });
            ClassicEditor
                .create(document.querySelector('#especifications4'), {
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'bold',
                            'italic',
                            'link',
                            'bulletedList',
                            'numberedList',
                            'blockQuote',
                            '|',
                            'undo',
                            'redo'
                        ]
                    },
                    language: 'es',
                    removeButtons: 'Image, MediaEmbed' // Aquí se especifican los botones a remover

                })
                .catch(error => {
                    console.error(error);
                });
        });
    </script>
@endsection
