@extends('layouts.layout')


@section('content')
    @foreach (auth()->user()->getAllPermissions()->where('categoria', '=', 'Ventas')->pluck('name')->toArray() as $permisos)
        <?php
        $mov = str_replace(' ', '', substr($permisos, 0, -2));
        $letra = substr($permisos, -1);
        ?>
        @if ($letra === 'E')
            <input type="hidden" value="true" id="{{ $mov }}">
        @endif
    @endforeach
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open([
                    'route' => ['modulo.ventas.store-venta'],
                    'id' => 'progressWizard',
                    'class' => 'panel-wizard',
                    'method' => 'POST',
                ]) !!}

                {!! Form::macro('labelValidacion', function ($name, $labelName, $classes) {
                    return "<label for='" .
                        $name .
                        "' class='" .
                        $classes .
                        "'>" .
                        $labelName .
                        "<span class='asterisk'> *</span></label>";
                }) !!}



                <ul class="nav nav-justified nav-wizard">
                    <li><a href="#tab1-2" data-toggle="tab"><strong>Datos Generales del Proceso</strong></a></li>
                    {{-- <li><a href="#tab2-2" data-toggle="tab"><strong>Informacion adicional</strong> </a></li> --}}
                </ul>





                <div class="tab-content">
                    <div class="col-md-4 observaciones">
                        <p class="titulo text-left">Identifica los campos obligatorios con <span class="asterisk">*</span>
                        </p>
                    </div>
                    <div class="col-md-6"></div>
                    <div class="col-md-2 btn-action">
                        <button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown">
                            Menú de opciones <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu" id="Opciones">

                            @can('Afectar')
                                <li><a href="#" id="afectar-boton"> Avanzar <span
                                            class="glyphicon glyphicon-play pull-right"></span></a></li>
                            @endcan

                            @if (isset($venta) && $venta->sales_movementID == null)
                                <li><a href="#" id="eliminar-boton">Eliminar <span
                                            class="glyphicon glyphicon-remove-circle pull-right"></span></a></li>
                            @endif

                            @can('Cancelar')
                                <li><a href="#" id="cancelar-boton">Cancelar <span
                                            class="glyphicon glyphicon-trash pull-right"></span></a></li>
                            @endcan

                            @if (isset($venta) && $venta->sales_movement !== 'Pedido' && $venta->sales_status !== 'INICIAL')
                                <li><a href="{{ route('vista.modulo.ventas.anexos', ['id' => $venta->sales_id]) }}"
                                        id="anexos-boton">Anexos <span
                                            class="glyphicon glyphicon-paperclip pull-right"></span></a></li>
                                <li><a href="{{ route('vista.modulo.ventas.reportes', ['idVenta' => $venta['sales_id']]) }}"
                                        target="_blank">Reporte <span
                                            class="glyphicon glyphicon-list-alt pull-right"></span></a>
                                </li>
                            @endif
                            @if (isset($venta) && $venta->sales_movement === 'Pedido' && $venta->sales_status !== 'INICIAL')
                                <li><a href="{{ route('vista.modulo.ventas.reportes', ['idVenta' => $venta['sales_id']]) }}"
                                        target="_blank">Nota de Venta<span
                                            class="glyphicon glyphicon-list-alt pull-right"></span></a>
                                </li>
                            @endif
                            @if (isset($venta) && $venta->sales_movement === 'Pedido' && $venta->sales_status !== 'INICIAL')
                                <li><a href="{{ route('vista.modulo.notaVenta.reportes', ['idVenta' => $venta['sales_id']]) }}"
                                        target="_blank">Nota de Venta S/I<span
                                            class="glyphicon glyphicon-list-alt pull-right"></span></a>
                                </li>
                            @endif
                            {{-- mostramos el reporte cuando la cotización ya tenga asignada un folio --}}

                            @if (isset($venta) &&
                                    $venta->sales_movement === 'Cotización' &&
                                    $venta->sales_status !== 'CANCELADO' &&
                                    $venta->sales_movementID !== null)
                                <li><a id="reporte-impuestos"
                                        href="{{ route('vista.modulo.ventas.cotizacion', ['idVenta' => $venta['sales_id']]) }}"
                                        target="_blank">Rep. con Imptos <span
                                            class="glyphicon glyphicon-list-alt pull-right"></span></a>
                                </li>

                                <li><a id="reporte-sinimpuestos"
                                        href="{{ route('vista.modulo.ventas.cotizacion-sin-impuestos', ['idVenta' => $venta['sales_id']]) }}"
                                        target="_blank">Rep. sin Imptos <span
                                            class="glyphicon glyphicon-list-alt pull-right"></span></a>
                                </li>
                            @endif

                            @if (isset($venta) &&
                                    $venta->sales_movement === 'Pedido' &&
                                    $venta->sales_status !== 'CANCELADO' &&
                                    $venta->sales_movementID !== null)
                                <li><a id="reporte-impuestos"
                                        href="{{ route('vista.modulo.ventas.formato-entrega', ['idVenta' => $venta['sales_id']]) }}"
                                        target="_blank">Formato Entrega<span
                                            class="glyphicon glyphicon-list-alt pull-right"></span></a>
                                </li>
                            @endif

                            @if (isset($venta) && $venta->sales_movement !== 'Rechazo de Venta')
                                <li>
                                    <a href="#" id="copiar-compra">Copiar <span
                                            class="fa fa-copy pull-right"></span></a>
                                </li>
                            @endif

                            @if (isset($venta))
                                <li class="divider"></li>
                                <li><a href="{{ route('vista.modulo.ventas.create-venta') }}" id="nuevo-boton">Nuevo<span
                                            class="fa fa-file-o pull-right"></span></a></li>

                                <li><a href="" data-toggle="modal" data-target="#ModalFlujo">
                                        Ver flujo
                                        <span class="glyphicon glyphicon-transfer pull-right"></span>
                                    </a></li>
                                @if (
                                    $venta->sales_movement === 'Factura' &&
                                        $venta->sales_status === 'FINALIZADO' &&
                                        ($venta->sales_typeCondition === 'CONTADO' || $venta->sales_typeCondition === 'Contado'))
                                    <li><a href="" data-toggle="modal" data-target="#ventasCalculadora">
                                            Cobro
                                            <span class="glyphicon glyphicon-credit-card pull-right"></span>
                                        </a></li>
                                @endif

                                @if (
                                    $venta->sales_movement === 'Factura' &&
                                        $venta->sales_status === 'FINALIZADO' &&
                                        $informacionMoneda->money_keySat === 'USD' &&
                                        substr(strtoupper($infoProveedor->customers_RFC), 0, 2) === 'XE')
                                    <li><a href="" data-toggle="modal" data-target="#modalComercioExterior">
                                            Comercio Exterior
                                            <span class="glyphicon  glyphicon-plane pull-right"></span>
                                        </a></li>
                                @endif

                                <li><a href="#" data-toggle="modal" data-target="#costoPromedioModal"
                                        id="costoPromedio">
                                        Inf. Articulo
                                        <span class="fa fa-tag pull-right"></span></a>
                                </li>


                                <li><a href="#" data-toggle="modal" data-target="#informacionProveedorModal">
                                        Inf. Cliente
                                        <span class="glyphicon glyphicon-exclamation-sign pull-right"></span>
                                    </a></li>
                                @if ($venta->sales_movement === 'Factura')
                                    @if (
                                        ($venta->sales_stamped == '0' || $venta->sales_stamped == null) &&
                                            (session('company')->companies_calculateTaxes == '0' || session('company')->companies_calculateTaxes == 0))
                                        <li>
                                            <a href="#" id="timbrado">
                                                Timbrado
                                                <span class="glyphicon glyphicon-repeat pull-right"></span>
                                            </a>
                                        </li>
                                    @endif
                                @endif
                                @if ($venta->sales_movement === 'Cotización' && $venta->sales_status === 'INICIAL' && $venta->sales_movementID == null)
                                    <li><a href="" data-toggle="modal" id="asignar-folio">
                                            Asignar folio
                                            {{-- <span class="glyphicon glyphicon-credit-card pull-right"></span> --}}
                                            <span class="fa fa-gears pull-right"></span>
                                        </a></li>
                                @endif
                                @if (
                                    $venta->sales_movement === 'Cotización' &&
                                        ($venta->sales_status === 'INICIAL' || $venta->sales_status === 'POR AUTORIZAR'))
                                    <li><a href="" data-toggle="modal" data-target="#email-modal">
                                            Enviar Email
                                            <span class="glyphicon glyphicon-envelope pull-right"></span>
                                        </a></li>
                                @endif
                                {{-- <li><a href="#" data-toggle="modal" data-target="#cancelarFacturaModal">
                                Cancelar Factura
                                <span class="fa fa-minus-circle pull-right"></span>
                            </a></li> --}}
                            @endif
                            {{-- <li><a href="#">Separated link</a></li> --}}
                        </ul>
                    </div>

                    <div class="tab-pane active" id="tab1-2">
                        <div class="col-md-12 cabecera-informacion">
                            <div id="leyenda-container" style="display: none;">
                                <p id="leyenda"></p>
                            </div>
                            <div class="col-md-4">
                                <!-- Movimientos -->
                                <div class="form-group">
                                    {!! Form::labelValidacion('movimientos', 'Proceso/Operación', 'negrita') !!}
                                    {!! Form::select('movimientos', $movimientos, isset($venta) ? $venta['sales_movement'] : null, [
                                        'id' => 'select-movimiento',
                                        'class' => 'widthAll select-movimiento',
                                        'placeholder' => 'Seleccione uno...',
                                    ]) !!}
                                </div>
                            </div>


                            <div class="col-md-2" style="display: none">
                                <div class="form-group">
                                    <label for="id" class="negrita">ID:</label>
                                    <input type="number" class="form-control" name="id" id="idCompra"
                                        value="{{ isset($venta) ? $venta['sales_id'] : 0 }}"readonly>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="folio" class="negrita">Folio:</label>
                                    <input type="number" class="form-control" name="folio" id="folioCompra"
                                        value="{{ isset($venta) ? $venta['sales_movementID'] : null }}"readonly>
                                </div>
                            </div>

                            <div class="col-md-2" style="display: none">
                                <div class="form-group">
                                    <label for="origin" class="negrita">ORIGIN:</label>
                                    <input type="text" class="form-control" name="origin" readonly id="origin" />
                                </div>
                            </div>

                            <div class="col-md-2" style="display: none">
                                <div class="form-group">
                                    <label for="originDato" class="negrita">ORIGIN-DATO</label>
                                    <input type="text" class="form-control" name="originDato" readonly
                                        id="originDato" value="{{ isset($venta) ? $venta['sales_origin'] : null }}" />
                                </div>
                            </div>

                            <div class="col-md-2" style="display: none">
                                <div class="form-group">
                                    <label for="originID" class="negrita">ORIGIN-ID</label>
                                    <input type="text" class="form-control" name="originID" readonly id="originID"
                                        value="{{ isset($venta) ? $venta['sales_originID'] : null }}" />
                                </div>
                            </div>

                            <div class="col-md-2" style="display: none">
                                <div class="form-group">
                                    <label for="originType" class="negrita">ORIGIN-TYPE:</label>
                                    <input type="text" class="form-control" name="originType" readonly
                                        id="originType"
                                        value="{{ isset($venta) ? $venta['sales_originType'] : null }}" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fechaEmision" class="negrita">Fecha</label>
                                    <input type="date" class="form-control input-date" name="fechaEmision"
                                        id="fechaEmision" placeholder="Fecha Emisión"
                                        value="{{ isset($venta) ? \Carbon\Carbon::parse($venta['sales_issuedate'])->format('Y-m-d') : date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    {!! Form::labelValidacion('nameMoneda', 'Moneda', 'negrita') !!}
                                    {!! Form::select(
                                        'nameMoneda',
                                        $selectMonedas,
                                        isset($venta) ? $venta['sales_money'] : $parametro->generalParameters_defaultMoney,
                                        [
                                            'id' => 'select-moneda',
                                            'class' => 'widthAll select-status',
                                        ],
                                    ) !!}
                                    <input type="hidden" id="clave_sat_moneda"
                                        value="{{ isset($informacionMoneda) ? $informacionMoneda->money_keySat : '' }}" />
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    {!! Form::labelValidacion('nameTipoCambio', 'Tipo de Cambio', 'negrita') !!}
                                    {!! Form::text(
                                        'nameTipoCambio',
                                        isset($venta) ? floatVal($venta['sales_typeChange']) : floatVal($parametro->money_change),
                                        [
                                            'class' => 'form-control',
                                            'readonly',
                                            'id' => 'nameTipoCambio',
                                        ],
                                    ) !!}
                                </div>
                            </div>



                            <div class="col-md-12"></div>

                            <div class="col-md-3">
                                <label for="proveedor" class="negrita">Cliente <span class='asterisk'>
                                        *</span></label>
                                <div class="input-group form-group mb15">
                                    <input type="number" class="form-control" id="proveedorKey" name="proveedorKey"
                                        value="{{ isset($venta) ? $venta['sales_customer'] : null }}" />
                                    <input type="hidden" class="form-control" id="rfc-cliente" name="rfc_cliente"
                                        value="{{ isset($venta) ? $infoProveedor->customers_RFC : '' }}">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" data-toggle="modal"
                                            data-target=".modal1" id="provedorModal">...</button>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="proveedorName" class="negrita">Nombre/Razón Social del Cliente:</label>
                                    <input type="text" class="form-control" name="proveedorName" id="proveedorName"
                                        value="{{ isset($venta) ? $nameProveedor['customers_businessName'] : null }}"
                                        readonly>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="precioLista" class="negrita">Precio Lista: <span class='asterisk'>
                                            *</span></label>
                                    <select name="precioListaSelect" id="select-precioListaSelect" class="widthAll">
                                        <option value="" selected aria-disabled="true">Selecciona uno...
                                        </option>
                                        <option value="articles_listPrice1">Precio 1</option>
                                        <option value="articles_listPrice2">Precio 2</option>
                                        <option value="articles_listPrice3">Precio 3</option>
                                        <option value="articles_listPrice4">Precio 4</option>
                                        <option value="articles_listPrice5">Precio 5</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12"></div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="proveedorCondicionPago" class="negrita">Término de Crédito <span
                                            class='asterisk'> *</span></label>
                                    <select name="proveedorCondicionPago" id="select-proveedorCondicionPago"
                                        class="widthAll">
                                        <option value="" selected aria-disabled="true">Selecciona uno...
                                        </option>

                                        @foreach ($select_condicionPago as $condicionPago)
                                            <option value="{{ $condicionPago->creditConditions_id }}">
                                                {{ $condicionPago->creditConditions_name }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4" style="display: none">
                                <div class="form-group">
                                    <label for="tipoCondicion" class="negrita">Tipo Condicion:</label>
                                    <input type="text" class="form-control input-date" name="tipoCondicion"
                                        id="tipoCondicion"
                                        value='{{ isset($venta) ? $venta['sales_typeCondition'] : null }}' readonly>
                                </div>

                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="proveedorFechaVencimiento" class="negrita">Fecha vencimiento:</label>
                                    <input type="date" class="form-control input-date"
                                        name="proveedorFechaVencimiento" id="proveedorFechaVencimiento"
                                        value='{{ isset($venta) ? \Carbon\Carbon::parse($venta['sales_expiration'])->format('Y-m-d') : null }}'>
                                </div>

                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="concepto" class="negrita">Concepto de la Operación<span class='asterisk'>
                                            *</span></label>
                                    <select name="concepto" id="select-moduleConcept" class="widthAll">
                                        <!-- Agregar el atributo data-todos a la opción "Selecciona uno..." -->
                                        <option value="" data-todos="todos">Selecciona uno...</option>

                                        @foreach ($select_conceptos as $concepto)
                                            <option value="{{ $concepto->moduleConcept_name }}">
                                                {{ $concepto->moduleConcept_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12"></div>



                            <div class="col-md-12"></div>

                            <div class="col-md-2">
                                <label for="almacen" class="negrita">Almacén <span class='asterisk'>
                                        *</span></label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="almacenKey" name="almacenKey"
                                        value="{{ isset($venta) ? $venta['sales_depot'] : null }}" />
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" data-toggle="modal"
                                            data-target=".modal2" id="almacenModal">...</button>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label for="seller" class="negrita">Vendedor <span class='asterisk'>
                                        *</span></label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="sellerKey" name="sellerKey"
                                        value="{{ isset($venta) ? $venta['sales_seller'] : null }}" readonly />
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" data-toggle="modal"
                                            data-target=".modal5" id="sellerModal">...</button>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="proveedorReferencia" class="negrita">Nota:</label>
                                    <input type="text" class="form-control"
                                        value="{{ isset($venta) ? $venta['sales_reference'] : null }}"
                                        name="proveedorReferencia" id="proveedorReferencia">
                                </div>
                            </div>

                            <div class="col-md-3" id="contrato">
                                <div class="form-group">
                                    <label for="clienteCFDI" class="negrita">Identificador CFDI</label>
                                    <select name="clienteCFDI" id="select-clienteCFDI" class="widthAll">
                                        <option value="" selected aria-disabled="true">Selecciona uno...
                                        </option>

                                        @foreach ($selectCFDI as $usoCFDI)
                                            <option value="{{ $usoCFDI->c_UsoCFDI }}">
                                                {{ $usoCFDI->descripcion . ' - ' . $usoCFDI->c_UsoCFDI }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3 motivoCancelacionDiv" style="display: none">
                                <div class="form-group">
                                    <label for="motivoCancelacion" class="negrita">Motivo de Cancelación<span
                                            class='asterisk'>
                                            *</span></label>
                                    <select name="motivoCancelacion" id="select-moduleCancellation" class="widthAll">
                                        <option value="">Selecciona uno...</option>

                                        @foreach ($select_motivos as $motivo)
                                            <option value="{{ $motivo->reasonCancellations_name }}">
                                                {{ $motivo->reasonCancellations_name }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6" style="display: none">
                                <label for="timbrado" class="negrita">Timbrado</label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="timbradoKey" name="timbradoKey"
                                    value="{{ isset($venta) ? ($venta['sales_stamped'] ?? 0) : null }}" />
                                </div>
                            </div>
                            <div class="col-md-6" style="display: none">
                                <label for="empresaImpuesto" class="negrita">Empresa</label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="empresaImpuesto"
                                        name="empresaImpuesto"
                                        value="{{ isset(session('company')->companies_calculateTaxes) ? session('company')->companies_calculateTaxes : null }}" />
                                </div>
                            </div>
                            <div class="col-md-6" style="display: none">
                                <label for="vendedorDefault" class="negrita">Vendedor2</label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="vendedorDefault"
                                        name="vendedorDefault"
                                        value="{{ isset($usuario->user_defaultAgent) ? $usuario->user_defaultAgent : null }}" />
                                </div>
                            </div>
                            <div class="col-md-6" style="display: none">
                                <label for="almacenDefault" class="negrita">Almacen2</label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="almacenDefault" name="almacenDefault"
                                        value="{{ isset($usuario->user_defaultDepot) ? $usuario->user_defaultDepot : null }}" />
                                </div>
                            </div>
                            <div class="col-md-6" style="display: none">
                                <label for="clienteDefault" class="negrita">Cliente2</label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="clienteDefault" name="clienteDefault"
                                        value="{{ isset($usuario->user_defaultCustomer) ? $usuario->user_defaultCustomer : null }}" />
                                </div>
                            </div>
                            <div class="col-md-12"></div>

                            <div id="contenedorTabla">
                                <div class="tablaDesborde ventasTabla">
                                    <div class="tab-content table-panel">
                                        <table class="table table-striped table-bordered ventasM widthAll">
                                            <thead>
                                                <tr>
                                                    <th style="display: none">Id</th>
                                                    <th style="display: none" class="td-aplica">Operación Origen</th>
                                                    <th style="display: none" class="td-consecutivo">Consecutivo
                                                    </th>
                                                    <th>Producto/Item</th>
                                                    <th style="display: none">Referencia-article</th>
                                                    <th>Nombre del Producto</th>
                                                    <th>Cantidad</th>
                                                    <th>Precio</th>
                                                    <th>Unidad de Venta</th>
                                                    <th style="display: none" class="unidadEmpaque">Unidad Empaque</th>
                                                    {{-- <th style="display: none" class="cantidadNeta">Cantidad Neta</th> --}}
                                                    <th>Factor Inv.</th>
                                                    <th>Importe sin IVA</th>
                                                    <th>% Desc. </th>
                                                    <th>Imp. Desc.</th>
                                                    <th>%IVA</th>
                                                    <th>Importe IVA</th>
                                                    <th class='retencion'>% Ret. ISR</th>
                                                    <th class='retencion'>Imp. ISR </th>
                                                    <th class='retencion'>% Ret. IVA</th>
                                                    <th class='retencion'>Imp. IVA</th>
                                                    <th>Importe total</th>
                                                    <th>Observaciones</th>
                                                    <th class="eliminacion-articulo"
                                                        style="width: 10px !important; weight: ">
                                                    </th>

                                                    {{-- botones de afectar --}}
                                                    <th style="display: none" class="accion-pendiente">
                                                        <p>Pendiente</p>
                                                    </th>
                                                    <th style="display: none" class="accion-recibir">A Enviar</th>
                                                    <th style="display: none">Tipo</th>
                                                    <th style="display: none">Decimales</th>
                                                </tr>
                                            </thead>


                                            @if (isset($articulosByVenta) && count($articulosByVenta) > 0)
                                                <tr id="controlArticulo2" style="display: none">
                                                    <td style="display: none"><input type="text" name="dataArticulos"
                                                            id="id-" value="" />
                                                    </td>
                                                    {{-- boton aplica --}}
                                                    <td style="display: none"><input type="text" name="dataArticulos"
                                                            id="id-" value="" />
                                                    </td>
                                                    {{-- boton consecutivo --}}

                                                    <td style="display: none"><input type="text" name="dataArticulos"
                                                            id="id-" value="" />
                                                    </td>

                                                    {{-- boton articulo --}}
                                                    <td id="btnInput"><input id="keyArticulo" type="text"
                                                            class="keyArticulo"
                                                            onchange="buscadorArticulos('keyArticulo')">
                                                        <button type="button" class="btn btn-info btn-sm"
                                                            data-toggle="modal" data-target=".modal3">...</button>
                                                    </td>
                                                    {{-- boton referencia-article --}}

                                                    <td style="display: none"><input type="text"
                                                            class="botonesArticulos" disabled value="" />
                                                    </td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td style="display: none" class='retencion'><input id=""
                                                            type="text" class="botonesArticulos" disabled></td>
                                                    <td style="display: none" class='retencion'><input id=""
                                                            type="text" class="botonesArticulos" disabled></td>
                                                    <td style="display: none" class='retencion'><input id=""
                                                            type="text" class="botonesArticulos" disabled></td>
                                                    <td style="display: none" class='retencion'><input id=""
                                                            type="text" class="botonesArticulos" disabled>
                                                    </td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td style="display: none" class="unidadEmpaque"><input id=""
                                                            type="text" class="botonesArticulos" disabled></td>
                                                    {{-- <td style="display: none" class="cantidadNeta"><input id=""
                                                        type="text" class="botonesArticulos" disabled></td> --}}
                                                    <td>
                                                        <input id="" type="text" class="botonesArticulos"
                                                            disabled>
                                                    </td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td
                                                        style="display: flex; justify-content: center; align-items: center; width: 30px !important">

                                                        <i class="fa fa-trash-o  btn-delete-articulo" aria-hidden="true"
                                                            style="color: red; font-size: 25px; cursor: pointer;"></i>
                                                    </td>
                                                    {{-- botones de afectar --}}
                                                    <td class="accion-pendiente">
                                                        <input id="" type="text"
                                                            class="botonesArticulos botonPendiente" readonly>
                                                    </td>
                                                    <td class="accion-recibir">
                                                        <input id="" type="text"
                                                            class="botonesArticulos botonRecibir">
                                                    </td>
                                                    <td style="display: none">
                                                        <input id="" type="text" class="botonesArticulos"
                                                            readonly>
                                                    </td>
                                                    <td style="display: none">
                                                        <input id="" type="text" readonly>
                                                    </td>

                                                </tr>
                                                <tbody id="articleItem">
                                                    <?php
                                                    $isInfoAplica = false; //Cambiara a true cuando una entrada venga de una orden de compra
                                                    
                                                    $retencionesDisponibles = false;
                                                    ?>
                                                    @foreach ($articulosByVenta as $key => $detalle)
                                                        <?php
                                                        
                                                        if ($detalle['salesDetails_applyIncrement'] != null && $detalle['salesDetails_apply'] != null) {
                                                            $isInfoAplica = true;
                                                        }
                                                        
                                                        ?>
                                                        <tr id="{{ $detalle['salesDetails_article'] . '-' . $key }}">
                                                            <td style="display: none">
                                                                <input type="text" name="dataArticulos"
                                                                    id="id-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    value="{{ $detalle['salesDetails_id'] }}" readonly />
                                                            </td>
                                                            <td style="display: none" class="aplicaA">
                                                                @if ($detalle['salesDetails_apply'] != null)
                                                                    <input type="text" style="width: 90px;"
                                                                        name="dataArticulos"
                                                                        id="id-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                        value="{{ $detalle['salesDetails_apply'] }} {{ $detalle['salesDetails_applyIncrement'] }}"
                                                                        readonly />
                                                                @endif
                                                            </td>
                                                            <td style="display: none" class="aplicaIncre">
                                                                @if ($detalle['salesDetails_apply'] != null)
                                                                    <input type="text" style="width: 90px; "
                                                                        name="dataArticulos"
                                                                        id="aplicaIncre-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                        value="{{ $detalle['salesDetails_applyIncrement'] }}"
                                                                        readonly />
                                                                @endif
                                                            </td>
                                                            <td id="btnInput"><input name="dataArticulos[]"
                                                                    id="keyArticulo-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="keyArticulo"
                                                                    value="{{ $detalle['salesDetails_article'] }}"
                                                                    title="{{ $detalle['salesDetails_article'] }}"
                                                                    onchange="buscadorArticulos('keyArticulo-{{ $detalle['salesDetails_article'] . '-' . $key }}')">

                                                                @if (!isset($detalle['salesDetails_apply']))
                                                                    <button type="button" class="btn btn-info btn-sm"
                                                                        data-toggle="modal"
                                                                        data-target=".modal3">...</button>
                                                                    {{-- @if ($venta['sales_movement'] === 'Pedido')
                                                                    <button type="button"
                                                                        class="btn btn-default btn-sm"
                                                                        data-toggle="modal" data-target=".modal7"
                                                                        id="modalEmpaque"><span
                                                                            class="glyphicon glyphicon-tags"></span></button>
                                                                @endif --}}

                                                                    @if ($detalle['salesDetails_type'] == 'Kit')
                                                                        <button type="button"
                                                                            class="btn btn-default btn-sm"
                                                                            data-toggle="modal" data-target=".modal7"
                                                                            id="modalEmpaque"><span
                                                                                class="glyphicon glyphicon-tags"></span></button>
                                                                    @endif

                                                                    @if (
                                                                        ($venta['sales_movement'] === 'Factura' && $detalle['salesDetails_type'] == 'Serie') ||
                                                                            ($venta['sales_movement'] === 'Pedido' && $detalle['salesDetails_type'] == 'Serie'))
                                                                        <button type="button"
                                                                            class="btn btn-warning btn-sm"
                                                                            data-toggle="modal" data-target=".modal4"
                                                                            id="modalSerie2">S</button>
                                                                    @endif
                                                                @else
                                                                    @if ($venta['sales_movement'] !== 'Rechazo de Venta')
                                                                        <button type="button" class="btn btn-info btn-sm"
                                                                            data-toggle="modal"
                                                                            data-target=".modal3">...</button>
                                                                    @endif
                                                                    {{-- @if ($venta['sales_movement'] === 'Pedido')
                                                                    <button type="button"
                                                                        class="btn btn-default btn-sm"
                                                                        data-toggle="modal" data-target=".modal7"
                                                                        id="modalEmpaque"><span
                                                                            class="glyphicon glyphicon-tags"></span></button>
                                                                @endif --}}

                                                                    @if ($detalle['salesDetails_type'] == 'Kit')
                                                                        <button type="button"
                                                                            class="btn btn-default btn-sm"
                                                                            data-toggle="modal" data-target=".modal7"
                                                                            id="modalEmpaque"><span
                                                                                class="glyphicon glyphicon-tags"></span></button>
                                                                    @endif

                                                                    @if (
                                                                        ($venta['sales_movement'] === 'Factura' && $detalle['salesDetails_type'] == 'Serie') ||
                                                                            ($venta['sales_movement'] === 'Pedido' && $detalle['salesDetails_type'] == 'Serie'))
                                                                        <button type="button"
                                                                            class="btn btn-warning btn-sm"
                                                                            data-toggle="modal" data-target=".modal4"
                                                                            id="modalSerie2">S</button>
                                                                    @endif
                                                                @endif



                                                            </td>
                                                            <td style="display: none">
                                                                <input type="text" name="dataArticulos[]"
                                                                    id="referenceArticle-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    value="{{ $detalle['salesDetails_id'] }}" readonly />
                                                            </td>
                                                            <td><input name="dataArticulos[]"
                                                                    id="desp-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos"
                                                                    value="{{ $detalle['salesDetails_descript'] }}"
                                                                    readonly
                                                                    title="{{ $detalle['salesDetails_descript'] }}"></td>
                                                            <td>
                                                                <?php
                                                                $cantidadArticulos = floatVal($detalle['salesDetails_quantity']);
                                                                $decimales = strlen($cantidadArticulos) - strrpos($cantidadArticulos, '.') - 1;
                                                                
                                                                $cantidadCancelada = floatVal($detalle['salesDetails_quantity'] - $detalle['salesDetails_canceledAmount']);
                                                                $decimalesCancelada = strlen($cantidadCancelada) - strrpos($cantidadCancelada, '.') - 1;
                                                                ?>
                                                                <input name="dataArticulos[]"
                                                                    id="canti-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    onchange="changeCantidadInventario('{{ $detalle['salesDetails_article'] }}', '{{ $key }}')"
                                                                    onfocus="changeCantidadInventario('{{ $detalle['salesDetails_article'] }}', '{{ $key }}')"
                                                                    value="{{ $detalle['salesDetails_canceledAmount'] == null ? number_format($cantidadArticulos, $decimalesCancelada) : number_format($cantidadCancelada, $decimalesCancelada) }}">
                                                            </td>

                                                            {{-- <script>
                                                            let factorMoneda2 = parseFloat($("#nameTipoCambio").val());
                                                            let precision = 2;
                                                            if(factorMoneda2!=1){
                                                                precision = 4;
                                                            }else{
                                                                precision = 2;
                                                            }

                                                            console.log(precision);
                                                        </script> --}}
                                                            <td><input name="dataArticulos[]"
                                                                    id="c_unitario-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    value="{{ number_format(floatVal($detalle['salesDetails_unitCost']), 4) }}"
                                                                    onchange="calcularImporte('{{ $detalle['salesDetails_article'] }}', '{{ $key }}')">
                                                            </td>
                                                            <td>
                                                                <select name="dataArticulos[]"
                                                                    id="unid-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    class="botonesArticulos"
                                                                    value="{{ $detalle['salesDetails_unit'] }}"
                                                                    onchange="recalcularCantidadInventario('{{ $detalle['salesDetails_article'] }}', '{{ $key }}')">
                                                                </select>
                                                            </td>
                                                            <td style="display: none" class="unidadEmpaque">
                                                                <select name="dataArticulos[]"
                                                                    id="unidadEmpaque-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    class="botonesArticulos"
                                                                    value="{{ $detalle['salesDetails_packingUnit'] }}">
                                                                </select>
                                                            </td>
                                                            {{-- <td style="display: none" class="cantidadNeta"><input
                                                                name="dataArticulos[]"
                                                                id="cantidadNeta-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                type="text" class="botonesArticulos sinBotones"
                                                                value="{{ floatVal($detalle['salesDetails_netQuantity']) }}"
                                                                onchange="recalcularCantidadInventario('{{ $detalle['salesDetails_article'] }}', '{{ $key }}')">
                                                        </td> --}}
                                                            <td><input name="dataArticulos[]"
                                                                    id="c_Inventario-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    value="{{ $detalle['salesDetails_canceledAmount'] == null ? $detalle['salesDetails_inventoryAmount'] : $detalle['salesDetails_inventoryAmount'] - $detalle['salesDetails_canceledAmount'] * $detalle['salesDetails_factor'] }}"
                                                                    readonly></td>
                                                            <td><input name="dataArticulos[]"
                                                                    id="importe-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    value="{{ number_format(floatVal($detalle['salesDetails_amount']), 4) }}"
                                                                    readonly></td>
                                                            <td><input name="dataArticulos[]"
                                                                    id="porDesc-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    value="{{ number_format(floatVal($detalle['salesDetails_discountPorcent']), 4) }}"
                                                                    onchange="descuentoLineal('{{ $detalle['salesDetails_article'] }}', '{{ $key }}')">
                                                            </td>
                                                            <td><input name="dataArticulos[]"
                                                                    id="descuento-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    value="{{ number_format(floatVal($detalle['salesDetails_discount']), 4) }}"
                                                                    readonly></td>
                                                            <td><input name="dataArticulos[]"
                                                                    id="iva-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    value="{{ floatVal($detalle['salesDetails_ivaPorcent']) }}"
                                                                    readonly>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $porcentajeIva = (float) $detalle['salesDetails_ivaPorcent'] / 100;
                                                                $importeIvaView = $detalle['salesDetails_amount'] * $porcentajeIva;
                                                                ?>
                                                                <input name="dataArticulos[]"
                                                                    id="importe_iva-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    value="{{ number_format(floatVal($importeIvaView), 4) }}"
                                                                    readonly>
                                                            </td>

                                                            @if (
                                                                $detalle['salesDetails_retention1'] !== '.0000' ||
                                                                    $detalle['salesDetails_retentionISR'] !== '.0000' ||
                                                                    $detalle['salesDetails_retention2'] !== '.0000' ||
                                                                    $detalle['salesDetails_retentionIVA'] !== '.0000')
                                                                <?php $retencionesDisponibles = true; ?>
                                                            @endif

                                                            <td class="retencion2"><input
                                                                    id="pRet1-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos" readonly
                                                                    value='{{ $detalle['salesDetails_retention1'] }}'
                                                                    name="dataArticulos[]"></td>
                                                            <td class="retencion2"><input
                                                                    id="pRetISR-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos" readonly
                                                                    value='{{ number_format(floatVal($detalle['salesDetails_retentionISR']), 2) }}'
                                                                    name="dataArticulos[]"></td>
                                                            <td class="retencion2"><input
                                                                    id="pRet2-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos" readonly
                                                                    value='{{ $detalle['salesDetails_retention2'] }}'
                                                                    name="dataArticulos[]"></td>
                                                            <td class="retencion2"><input
                                                                    id="retIva-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos" readonly
                                                                    value='{{ number_format(floatVal($detalle['salesDetails_retentionIVA']), 2) }}'
                                                                    name="dataArticulos[]">
                                                            </td>
                                                            <td><input name="dataArticulos[]"
                                                                    id="importe_total-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    value="{{ number_format(floatVal($detalle['salesDetails_total']), 2) }}"
                                                                    readonly></td>
                                                            <td><input name="dataArticulos[]"
                                                                    id="observacion-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos"
                                                                    value="{{ $detalle['salesDetails_observations'] }}"
                                                                    title="{{ $detalle['salesDetails_observations'] }}">
                                                            </td>

                                                            <td style="display: flex; justify-content: center; align-items: center; height: 55px"
                                                                class="eliminacion-articulo">
                                                                <i class="fa fa-trash-o"
                                                                    onclick="eliminarArticulo('{{ $detalle['salesDetails_article'] }}', '{{ $key }}')"
                                                                    aria-hidden="true"
                                                                    style="color: red; font-size: 25px; cursor: pointer;"></i>
                                                            </td>
                                                            @if ($venta['sales_status'] !== 'FINALIZADO' && $venta['sales_status'] !== 'CANCELADO')
                                                                {{-- botones de afectar --}}

                                                                <?php
                                                                $pendienteArticulo = floatval($detalle['salesDetails_outstandingAmount']);
                                                                
                                                                if ($pendienteArticulo < 0) {
                                                                    $pendienteArticulo = 0;
                                                                }
                                                                ?>
                                                                <td class="accion-pendiente">
                                                                    <input
                                                                        id="montoPendiente-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                        type="text"
                                                                        class="botonesArticulos botonPendiente"
                                                                        value="{{ $pendienteArticulo }}" readonly>
                                                                </td>
                                                                <td class="accion-recibir">
                                                                    <input
                                                                        id="montoRecibir-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                        type="text"
                                                                        class="botonesArticulos botonRecibir"
                                                                        {{ $detalle['salesDetails_outstandingAmount'] == null ? 'readonly' : '' }}
                                                                        onchange="validarInput('{{ $detalle['salesDetails_article'] }}', '{{ $key }}')">
                                                                </td>
                                                                <script>
                                                                    $('.accion-pendiente').attr('style', 'display: ');
                                                                    $('.accion-recibir').attr('style', 'display: ');
                                                                </script>
                                                            @endif

                                                            <td style="display: none">
                                                                <input
                                                                    id="tipoArticulo-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos"
                                                                    value="{{ $detalle['salesDetails_type'] }}" readonly>
                                                            </td>

                                                            <td style="display: none">
                                                                <input
                                                                    id="decimales-{{ $detalle['salesDetails_article'] . '-' . $key }}"
                                                                    type="text" value="" readonly>
                                                            </td>
                                                        </tr>
                                                        <script>
                                                            $.ajax({
                                                                url: "/comercial/ventas/api/getMultiUnidad",
                                                                type: "GET",
                                                                data: {
                                                                    factorUnidad: "{{ $detalle['salesDetails_article'] }}",
                                                                },
                                                                success: function(data) {
                                                                    let unidadPorDefecto = "{{ $detalle['salesDetails_unit'] }}";
                                                                    let unidadPorDefectoIndex = {};
                                                                    data.forEach((element) => {
                                                                        if (element.articlesUnits_unit == unidadPorDefecto) {
                                                                            unidadPorDefectoIndex = element;
                                                                        }

                                                                        $("#unid-" + '{{ $detalle['salesDetails_article'] . '-' . $key }}').append(`
                                                                    <option value="${element.articlesUnits_unit}-${element.articlesUnits_factor}">${element.articlesUnits_unit}-${element.articlesUnits_factor}</option>
                                                                    `);

                                                                        $('input[id="canti-{{ $detalle['salesDetails_article'] . '-' . $key }}"]')
                                                                            .focus();
                                                                    });

                                                                    if (Object.keys(unidadPorDefectoIndex).length > 0) {
                                                                        $("#unid-" + '{{ $detalle['salesDetails_article'] . '-' . $key }}').val(
                                                                            unidadPorDefecto +
                                                                            "-" +
                                                                            unidadPorDefectoIndex.articlesUnits_factor
                                                                        );

                                                                        $("#unid-" + '{{ $detalle['salesDetails_article'] . '-' . $key }}').change();
                                                                    }
                                                                },
                                                            });

                                                            $.ajax({
                                                                url: "/listaEmpaques",
                                                                type: "GET",
                                                                success: function({
                                                                    estatus,
                                                                    data
                                                                }) {

                                                                    let empaquePorDefecto = "{{ $detalle['salesDetails_packingUnit'] }}";
                                                                    data.forEach((element) => {
                                                                        $("#unidadEmpaque-" + '{{ $detalle['salesDetails_article'] . '-' . $key }}')
                                                                            .append(`
                                                                            <option value="${element.packaging_units_packaging+'-'+element.packaging_units_weight+'-'+element.packaging_units_unit}">${element.packaging_units_packaging+'-'+element.packaging_units_weight+'-'+element.packaging_units_unit}</option>
                                                                            `);


                                                                        if (empaquePorDefecto == element.packaging_units_packaging + '-' + element
                                                                            .packaging_units_weight + '-' + element.packaging_units_unit) {
                                                                            $("#unidadEmpaque-" + '{{ $detalle['salesDetails_article'] . '-' . $key }}')
                                                                                .val(empaquePorDefecto);
                                                                        }
                                                                    });

                                                                },
                                                            });
                                                        </script>
                                                    @endforeach

                                                    <script>
                                                        const isAplica = '{{ $isInfoAplica }}';
                                                        if (isAplica) {
                                                            $('.td-aplica').attr('style', 'display: ');
                                                            $('.td-consecutivo').hide();
                                                            $('.aplicaA').show();
                                                            $('.aplicaIncre').hide();

                                                        }
                                                    </script>
                                                    @if (!$retencionesDisponibles)
                                                        <script>
                                                            $('.retencion').hide();
                                                            $('.retencion2').hide();
                                                        </script>
                                                    @else
                                                        <script>
                                                            $('.retencion2').show();
                                                        </script>
                                                    @endif

                                                </tbody>
                                            @else
                                                <tbody id="articleItem">
                                                    <tr id="controlArticulo">
                                                        <td style="display: none"><input type="text"
                                                                name="dataArticulos" id="id-" value="" />
                                                        </td>
                                                        {{-- boton aplica --}}
                                                        <td style="display: none"><input type="text"
                                                                name="dataArticulos" id="" value="" />
                                                        </td>
                                                        <td style="display: none; width: 25px"><input type="text"
                                                                name="dataArticulos" id="" value="" />
                                                        </td>
                                                        {{-- boton aplica --}}
                                                        <td id="btnInput"><input id="keyArticulo" type="text"
                                                                class="keyArticulo"
                                                                onchange="buscadorArticulos('keyArticulo')">
                                                            <button type="button" class="btn btn-info btn-sm"
                                                                data-toggle="modal" data-target=".modal3">...</button>
                                                        </td>
                                                        <td style="display: none"><input type="text"
                                                                name="dataArticulos" id="" value="" />
                                                        </td>
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td style="display: none" class="unidadEmpaque"><input
                                                                id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td style="display: none" class="cantidadNeta"><input
                                                                id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td>
                                                            <input id="" type="text" class="botonesArticulos"
                                                                disabled>
                                                        </td>
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td style="display: none" class='retencion'><input id=""
                                                                type="text" class="botonesArticulos" disabled></td>
                                                        <td style="display: none" class='retencion'><input id=""
                                                                type="text" class="botonesArticulos" disabled></td>
                                                        <td style="display: none" class='retencion'><input id=""
                                                                type="text" class="botonesArticulos" disabled></td>
                                                        <td style="display: none" class='retencion'><input id=""
                                                                type="text" class="botonesArticulos" disabled>
                                                        </td>
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td
                                                            style="display: flex; justify-content: center; align-items: center; width: 30px !important">

                                                            <i class="fa fa-trash-o  btn-delete-articulo"
                                                                aria-hidden="true"
                                                                style="color: red; font-size: 25px; cursor: pointer;"></i>
                                                        </td>
                                                        {{-- botones de afectar --}}
                                                        <td style="display: none" class="accion-pendiente">
                                                            <input id="" type="text"
                                                                class="botonesArticulos botonPendiente" readonly>
                                                        </td>
                                                        <td style="display: none" class="accion-recibir">
                                                            <input id="" type="text"
                                                                class="botonesArticulos botonRecibir">
                                                        </td>
                                                        <td style="display: none">
                                                            <input id="" type="text" class="botonesArticulos"
                                                                readonly>
                                                        </td>
                                                        <td style="display: none">
                                                            <input id="" type="text" readonly>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            @endif
                                        </table>


                                    </div><!-- panel -->
                                </div>
                            </div>

                            <div class="col-md-12 mt10 mb10 ">
                                <div class="col-md-2 pull-right">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-addon"> Artículos: </span>
                                        <input type="text" class="form-control" id="cantidadArticulos"
                                            name="cantidadArticulos" value="0" readonly>
                                    </div>
                                </div>

                                <div class="col-md-2 pull-right">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-addon">Total:</span>
                                        <input type="text" class="form-control" id="totalCompleto"
                                            name="totalCompleto" readonly>
                                    </div>
                                </div>

                                <div class="col-md-2 pull-right">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-addon">Retenciones</span>
                                        <input type="text" class="form-control" id="retencionesCompleto"
                                            name="retencionesCompleto" readonly>
                                    </div>
                                </div>

                                <div class="col-md-2 pull-right">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-addon">Impuestos</span>
                                        <input type="text" class="form-control" id="impuestosCompleto"
                                            name="impuestosCompleto" readonly>
                                    </div>
                                </div>

                                <div class="col-md-2 pull-right">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-addon"> Total Descuento</span>
                                        <input type="text" class="form-control" id="totalDescuento"
                                            name="totalDescuento" readonly>
                                    </div>
                                </div>

                                <div class="col-md-2 pull-right">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-addon"> Sub-Total</span>
                                        <input type="text" class="form-control" id="subTotalCompleto"
                                            name="subTotalCompleto" readonly>
                                    </div>
                                </div>






                                <div class="col-md-1 pull-left">
                                    <div class="input-group input-group-sm mt5" style="margin-left: -5px">
                                        @if (isset($venta) ? $venta['sales_status'] === 'INICIAL' : 'INICIAL')
                                            <span class="label label-default" id="status">INICIAL</span>
                                        @elseif($venta['sales_status'] === 'POR AUTORIZAR')
                                            <span class="label label-warning" id="status">POR AUTORIZAR</span>
                                        @elseif($venta['sales_status'] === 'FINALIZADO')
                                            <span class="label label-success" id="status">FINALIZADO</span>
                                        @elseif($venta['sales_status'] === 'CANCELADO')
                                            <span class="label label-danger" id="status">CANCELADO</span>
                                        @endif

                                    </div>
                                </div>


                                <div class="col-md-2 pull-right">
                                    <div class="input-group input-group-sm" style="display: none">
                                        <span class="input-group-addon">% ISR</span>
                                        <input type="text" class="form-control" id="porcentajeISR"
                                            name="porcentajeISR" readonly>
                                    </div>
                                </div>

                                <div class="col-md-2 pull-right" style="display: none">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-addon">Retención ISR</span>
                                        <input type="text" class="form-control" id="retencionISR" name="retencionISR"
                                            readonly>
                                    </div>
                                </div>

                                <div class="col-md-2 pull-right" style="display: none">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-addon">% IVA</span>
                                        <input type="text" class="form-control" id="porcentajeIVA"
                                            name="porcentajeIVA" readonly>
                                    </div>
                                </div>

                                <div class="col-md-2 pull-right" style="display: none">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-addon">Retención IVA</span>
                                        <input type="text" class="form-control" id="retencionIVA" name="retencionIVA"
                                            readonly>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>




                    {{-- <div class="tab-pane active" id="tab2-2">

                    <div class="col-md-12">
                        <h3>Otros datos - Entrega Material</h3>
                    </div>

                    <div class="col-md-3 mt10">
                        <div class="form-group">
                            <label for="folioTicket" class="negrita">Folio ticket:</label>
                            <input type="text" class="form-control" name="folioTicket" id="folioTicket"
                                value="{{ isset($venta) ? $venta['sales_ticket'] : '' }}">
                        </div>
                    </div>

                    <div class="col-md-3 mt10">
                        <div class="form-group">
                            <label for="material" class="negrita">Material:</label>
                            <input type="text" class="form-control" name="material" id="material"
                                value="{{ isset($venta) ? $venta['sales_material'] : '' }}">
                        </div>
                    </div>


                    <div class="col-md-3 mt10">
                        <div class="form-group">
                            <label for="pesoSalida" class="negrita">Peso Salida:</label>
                            <input type="number" class="form-control" name="pesoSalida" id="pesoSalida"
                                step="0" value="{{ isset($venta) ? $venta['sales_outputWeight'] : '' }}">
                        </div>
                    </div>


                    <div class="col-md-3 mt10">
                        {!! Form::label('fechaEntrada', 'Fecha y Hora', ['class' => 'negrita']) !!}
                        <input type="datetime-local" name="fechaHoraSalida" class="form-control"
                            placeholder="mm/dd/yyyy" style="line-height: 17px"
                            value="{{ isset($venta) ? $venta['sales_dateTime'] : '' }}" id="fechaSalida">
                    </div>

                    <div class="col-md-12">

                    </div>

                    <div class="col-md-12">
                        <h3>Orden Entrega</h3>
                    </div>

                    <div class="col-md-3 mt10">
                        <div class="form-group">
                            <label for="choferName" class="negrita">Chofer:</label>
                            <select name="choferName" id="select-choferName" class="widthAll">
                                <option value="" selected aria-disabled="true">Selecciona uno...
                                </option>

                                @foreach ($select_agentes as $agente)
                                    <option value="{{ $agente->agents_key }}">
                                        {{ $agente->agents_name }}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 mt10">
                        <div class="form-group">
                            <label for="vehiculoName" class="negrita">Vehiculo: </label>
                            <select name="vehiculoName" id="select-vehiculoName" class="widthAll">
                                <option value="" selected aria-disabled="true">Selecciona uno...
                                </option>

                                @foreach ($select_vehiculos as $vehiculo)
                                    <option value="{{ $vehiculo->vehicles_key }}">
                                        {{ $vehiculo->vehicles_name }}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 mt10">
                        <div class="form-group">
                            <label for="placas" class="negrita">Placas:</label>
                            <input type="text" class="form-control" name="placas" id="placas" readonly
                                value="{{ isset($venta) ? $venta['sales_plates'] : '' }}">
                        </div>
                    </div>

                    <div class="col-md-3 mt10">
                        <div class="form-group">
                            <label for="lugarEntrega" class="negrita">Lugar Entrega:</label>
                            <input type="text" class="form-control" name="lugarEntrega" id="lugarEntrega"
                                value="{{ isset($venta) ? $venta['sales_placeDelivery'] : '' }}">
                        </div>
                    </div>

                    <div class="col-md-12"></div>

                    <div class="col-md-4 mt10">
                        <div class="form-group">
                            <label for="numeroBooking" class="negrita">Numero Booking:</label>
                            <input type="text" class="form-control" name="numeroBooking" id="numeroBooking"
                                value="{{ isset($venta) ? $venta['sales_bookingNumber'] : '' }}">
                        </div>
                    </div>

                    <div class="col-md-4 mt10">
                        <div class="form-group">
                            <label for="sello" class="negrita">Sello:</label>
                            <input type="text" class="form-control" name="sello" id="sello"
                                value="{{ isset($venta) ? $venta['sales_stamp'] : '' }}">
                        </div>
                    </div>

                    <div class="col-md-4 mt10">
                        <div class="form-group">
                            <label for="fechaSalida2" class="negrita">Fecha Salida:</label>
                            <input type="date" name="fechaSalida2" class="form-control" placeholder="mm/dd/yyyy"
                                style="line-height: 17px"
                                value="{{ isset($venta) ? $venta['sales_departureDate'] : '' }}" id="fechaSalida2">
                        </div>
                    </div>

                    <div class="col-md-12">

                    </div>

                    <div class="col-md-3 mt10">
                        <div class="form-group">
                            <label for="buqueName" class="negrita">Nombre del buque:</label>
                            <input type="text" class="form-control" name="buqueName" id="buqueName"
                                value="{{ isset($venta) ? $venta['sales_shipName'] : '' }}">
                        </div>
                    </div>

                    <div class="col-md-3 mt10">
                        <div class="form-group">
                            <label for="destinoFinal" class="negrita">Destino final:</label>
                            <input type="text" class="form-control" name="destinoFinal" id="destinoFinal"
                                value="{{ isset($venta) ? $venta['sales_finalDestiny'] : '' }}">
                        </div>
                    </div>

                    <div class="col-md-3 mt10">
                        <div class="form-group">
                            <label for="numeroContrato" class="negrita">Número de contrato:</label>
                            <input type="text" class="form-control" name="numeroContrato" id="numeroContrato"
                                value="{{ isset($venta) ? $venta['sales_contractNumber'] : '' }}">
                        </div>
                    </div>

                    <div class="col-md-3 mt10">
                        <div class="form-group">
                            <label for="tipoContenedor" class="negrita">Tipo de contenedor:</label>
                            <input type="text" class="form-control" name="tipoContenedor" id="tipoContenedor"
                                value="{{ isset($venta) ? $venta['sales_containerType'] : '' }}">
                        </div>
                    </div>




                    <div class="col-md-12 ">
                        <div class="input-group input-group-sm mt5" style="margin-bottom: 10px;">
                            <span class="label label-default" id="status">
                                {{ isset($venta) ? $venta['sales_status'] : 'INICIAL' }}</span>
                        </div>
                    </div>
                    <div class="col-md-12">

                    </div>




                </div> --}}
                    <ul class="list-unstyled wizard" id="botonesWizard">
                        {{-- <li class="pull-left previous" style="margin: 10px"><button type="button"
                            class="btn btn-default">Anterior</button>
                    </li>
                    <li class="pull-right next" style="margin: 10px"><button type="button"
                            class="btn btn-primary">Siguiente</button>
                    </li> --}}
                    </ul>
                    {{-- Input donde asignamos los nuevos valos de los articulos para manipularlo facilmente --}}
                    <input type="text" id="inputDataArticles" readonly hidden />
                    {{-- Añadimos los id que eliminamos de los articulos --}}
                    <input type="text" id="inputDataArticlesDelete" name="dataArticulosDelete" readonly hidden />
                    {{-- Añadimos el json del cobro factura --}}
                    <input type="text" id="inputJsonCobroFactura" name="inputJsonCobroFactura" readonly hidden />
                    {{-- Añadimos el comercio exterior --}}
                    <input type="text" id="inputJsonComercioExterior" name="inputJsonComercioExterior" readonly
                        hidden />
                    {{-- Añadimos el json de los datos de la factura --}}
                    <input type="text" id="dataFacturaInfo" name="dataFacturaInfo" readonly hidden />
                    {{-- Añadimos el jsonData del modulo --}}
                    <input type="text" id="inputJsonData" name="inputJsonData"
                        value="{{ isset($venta) ? $venta['sales_jsonData'] : '' }}" readonly hidden />


                </div>
                <button type="submit" class="btn btn-success" id="crearMov">Crear/Guardar</button>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg modal1" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="proveedorModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Lista de Clientes</h5>

                    </div>
                    <div class="modal-body">
                        <div class="panel table-panel">
                            <table id="shTable2" class="table table-striped table-bordered widthAll">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Nombre</th>
                                        <th>Nombre Empresa</th>
                                        <th>RFC</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($clientes as $cliente)
                                        <tr>
                                            <td>{{ $cliente->customers_key }}</td>
                                            <td>{{ $cliente->customers_name }}</td>
                                            <td>{{ $cliente->customers_businessName }}</td>
                                            <td>{{ $cliente->customers_RFC }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div><!-- panel -->
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg modal2" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="almacenesModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Lista de almacenes</h5>

                    </div>
                    <div class="modal-body">
                        <div class="panel table-panel">
                            <table id="shTable4" class="table table-striped table-bordered widthAll">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Nombre</th>

                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($almacenes as $almacen)
                                        <tr>
                                            <td>{{ $almacen->depots_key }}</td>
                                            <td>{{ $almacen->depots_name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div><!-- panel -->
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg modal5" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="vendedoresModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Lista de Vendedores</h5>

                    </div>
                    <div class="modal-body">
                        <div class="panel table-panel">
                            <table id="shTable6" class="table table-striped table-bordered widthAll">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Nombre</th>

                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($vendedores as $vendedor)
                                        <tr>
                                            <td>{{ $vendedor->agents_key }}</td>
                                            <td>{{ $vendedor->agents_name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div><!-- panel -->
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg modal3" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="articulosModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <div class="col-md-2">
                            <label for="ArticulosConExistencia"><span>Con existencia</span></label>
                            <input type="checkbox" name="all" id="ArtExistencia" />
                        </div>
                         <div class="col-md-2">
                            <div class="form-group">
                                {!! Form::label('categoria', 'Categoria', ['class' => 'negrita']) !!}
                                {!! Form::select(
                                    'categoria',
                                    $select_categoria, null,
                                    ['id' => 'select-search-hide', 'class' => 'widthAll', 'placeholder' => 'Seleccione uno...'],
                                ) !!}
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                {!! Form::label('grupo', 'Grupo', ['class' => 'negrita']) !!}
                                {!! Form::select('grupo', $select_grupo, null, [
                                    'id' => 'select-search-grupo',
                                    'class' => 'widthAll',
                                    'placeholder' => 'Seleccione uno...',
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                {!! Form::label('familia', 'Familia', ['class' => 'negrita']) !!}
                                {!! Form::select('familia', $select_familia, null, [
                                    'id' => 'select-search-familia',
                                    'class' => 'widthAll',
                                    'placeholder' => 'Seleccione uno...',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="panel table-panel">
                            <table id="shTable5" class="table table-striped table-bordered widthAll">
                                <thead>
                                    <tr>
                                        <th colspan="4"  class="text-center">
                                            <h5 class="modal-title text-center" id="exampleModalLongTitle">Lista de Artículos</h5>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Nombre</th>
                                        <th>Precio Lista</th>
                                        <th>Disponible</th>
                                        <th style="display: none">Almacen</th>
                                        <th style="display: none">Iva</th>
                                        <th style="display: none">Unidad</th>
                                        <th style="display: none">Tipo</th>
                                        <th style="display: none">Ret 1</th>
                                        <th style="display: none">Ret 2</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    {{-- <?php dd($articulos); ?>k --}}
                                    @foreach ($articulos as $articulo)
                                        <tr>
                                            <td>{{ $articulo->articles_key }}</td>
                                            <td>{{ $articulo->articles_descript }}</td>
                                            <td>{{ '$' . floatVal($articulo->articles_listPrice1) }}</td>
                                            <td>{{ $articulo->articlesInv_inventory }}</td>
                                            <td style="display: none">{{ $articulo->articlesInv_depot }}</td>
                                            <td style="display: none">{{ $articulo->articles_porcentIva }}</td>
                                            <td style="display: none">{{ $unidad[$articulo->articles_unitSale] }}</td>
                                            <td style="display: none">{{ $articulo->articles_type }}</td>
                                            <td style="display: none">{{ $articulo->articles_retention1 }}</td>
                                            <td style="display: none">{{ $articulo->articles_retention2 }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div><!-- panel -->
                    </div>

                    <div class="modal-footer">
                        <button type="button" id="agregarArticulos" class="btn btn-success"
                            data-dismiss="modal">Agregar</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal Orden de Compra -->
        <div class="modal fade" id="modalCompra" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-center" id="exampleModalCenterTitle">Elige tu Proceso Siguiente:</h5>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="generarFactura" name="accionVenta"
                                            value="Generar Factura" checked>
                                        <label>Genera la Factura al Cliente (Contado o Crédito)</label>

                                    </div>
                                </div>

                                <div class="col-md-12" id="pedidoContenedor">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="generarPedido" name="accionVenta"
                                            value="Generar Pedido">
                                        <label>Genera el Pedido al Cliente (Parcial o Total)</label>

                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="generarVentaPerdida" name="accionVenta"
                                            value="Generar Rechazo de Venta">
                                        <label>Genera el Rechazo de Venta (Parcial o Total)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer display-center">
                        <button type="button" class="btn btn-primary" id="btn-modal-compra">Aceptar</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal - Generar 'Entrada por Compra' de 'Orden de Compra' -->
        <div class="modal fade" id="modalCompra2" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-center" id="exampleModalCenterTitle">
                            Avanza tu Proceso por la Cantidad
                        </h5>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="cantidadIndicada" name="accionEntradaCompra"
                                            value="Cantidad Indicada">
                                        <label>Recibida (Parcial)</label>

                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="cantidadPendiente" name="accionEntradaCompra"
                                            value="Cantidad Pendiente" checked>
                                        <label>Completa (Total/Pendiente)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer display-center">
                        <button type="button" class="btn btn-primary" id="btn-modal-compra2">Aceptar</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- modal cancelar --}}
        <div class="modal fade" id="modalCancelar" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-center" id="exampleModalCenterTitle">Cancelar Ventas</h5>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="cancelarMovimientoCompleto" name="accionCancelar"
                                            value="Movimiento Completo">
                                        <label>Movimiento Completo</label>

                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="cantidadPendienteCancelar" name="accionCancelar"
                                            value="Todo el pendiente">
                                        <label>Todo el pendiente</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer display-center">
                        <button type="button" class="btn btn-primary" id="btn-modal-cancelar">Aceptar</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="modalCancelar" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-center" id="exampleModalCenterTitle">Cancelar Orden de Compra</h5>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="generarEntradaCompra" name="accionCancelar"
                                            value="Generar Entrada por Compra">
                                        <label>Movimiento Completo</label>

                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="cantidadIndicadaCancelar" name="accionCancelar"
                                            value="Generar Entrada por Compra">
                                        <label>Cantidad Indicada</label>

                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="cantidadPendienteCancelar" name="accionCancelar"
                                            value="Generar Rechazo de Compra">
                                        <label>Todo el pendiente</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer display-center">
                        <button type="button" class="btn btn-primary" id="btn-modal-compra">Aceptar</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="ModalFlujo" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        id="modalFlujoClose">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Posición del Movimiento</h5>
                    </div>
                    <div class="modal-body contenedor-flujo">
                        <input type="hidden" id="movimientoFlujo"
                            value="{{ isset($venta) ? (isset($primerFlujodeVenta) ? $primerFlujodeVenta : '') : '' }}" />
                        <input type="hidden" value="" style="display: none" id="data-info" />
                        <table class="flujo-table">
                            <tr>
                                <th>Origenes</th>
                                <th>Módulo</th>
                                <th style="text-align: center">-------</th>
                                <th>Movimientos Generados</th>
                                <th>Módulo</th>
                            </tr>

                            <tbody id="movimientosTr">

                            </tbody>

                        </table>
                    </div>
                    <div class="modal-footer footer-flujo">




                        <button type='button' class='btn btn-info flujoPrincipal'>Flujo Principal <span
                                class="glyphicon glyphicon-refresh"></span></button>
                        <button type='button' class='btn btn-danger closeModalFlujo' data-dismiss='modal'>Cerrar
                            <span class="glyphicon glyphicon-log-out"></span></button>
                        <button type="button" class="btn btn-secondary optionFlujo" id="anterior-Flujo"><span
                                class="glyphicon glyphicon-arrow-left"></span> Anterior </button>
                        <button type="button" class="btn btn-primary optionFlujo" id="siguiente-Flujo">Siguiente
                            <span class="glyphicon glyphicon-arrow-right"></span></button>


                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg 9" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="costoPromedioModal" style="display: ">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <div class="lds-roller" id="loading2" style="display: none">
                            <div class="lds-roller2">
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </div>
                        <h5 class="modal-title" id="exampleModalLongTitle">Información del Artículo</h5>
                        <ul class="nav nav-tabs nav-line" style="margin: 0px !important">
                            <li id="li1" class="active"><a href="#activities"
                                    data-toggle="tab"><strong>General</strong></a></li>
                            <li id="li2" class=""><a href="#followers"
                                    data-toggle="tab"><strong>Artículo</strong></a></li>
                            @if ($usuario->user_viewArticleInformationCost === '1')
                                <li id="li3" class=""><a href="#following"
                                        data-toggle="tab"><strong>Costo</strong></a>
                                </li>
                            @endif

                            @if (isset($articulosKitInfo) && count($articulosKitInfo) > 0)
                                <li id="li4" class=""><a href="#KitsArticulos"
                                        data-toggle="tab"><strong>Kit</strong></a>
                                </li>
                            @endif
                            <li id="li5" class=""><a href="#siguiente"
                                    data-toggle="tab"><strong>Especificaciones</strong></a>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <div class="col-sm-8 col-md-12">
                            <!-- Tab panes -->
                            <div class="tab-content nopadding noborder">
                                <div class="tab-pane active" id="activities">
                                    <div class="activity-list">

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="articuloCostoPromedio" class="negrita">Artículo:</label>
                                                <input type="text" readonly class="form-control input-sm"
                                                    value="{{ isset($infoArticulo) ? $infoArticulo->articles_key : '' }}"
                                                    name="articuloCostoPromedio" id="articuloCostoPromedio">
                                            </div>
                                        </div>

                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="descripcionCostoPromedio"
                                                    class="negrita">Descripción:</label>
                                                <input type="text" readonly class="form-control input-sm"
                                                    value="{{ isset($infoArticulo) ? $infoArticulo->articles_descript : '' }}"
                                                    name="descripcionCostoPromedio" id="descripcionCostoPromedio">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="disponibleCostoPromedio" class="negrita">Disponible:</label>
                                                <input type="text" readonly class="form-control input-sm"
                                                    value="{{ isset($infoArticulo) ? number_format($infoArticulo->articlesInv_inventory, 2) : '' }}"
                                                    name="disponibleCostoPromedio" id="disponibleCostoPromedio">
                                            </div>
                                        </div>


                                        <div class="col-md-4 pull-right">
                                            <div class="form-group">
                                                <label for="existenciaCostoPromedio" class="negrita">Existencia:</label>
                                                <input type="text" readonly class="form-control input-sm"
                                                    value="{{ isset($infoArticulo) ? number_format($infoArticulo->articlesInv_inventory, 2) : '' }}"
                                                    name="existenciaCostoPromedio" id="existenciaCostoPromedio">
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <table class="table">
                                                @if (isset($articulosByAlmacen) && isset($infoArticulo->articlesInv_inventory))
                                                    <tr>
                                                        <th>Almacén</th>
                                                        <th>Disponible</th>
                                                    </tr>

                                                    <tbody class="tableAlmacenesDisponibles">
                                                        @foreach ($articulosByAlmacen as $item)
                                                            <?php
                                                            if (isset($articulosByAlmacen)) {
                                                                $inventario2 = floatVal($item->articlesInv_inventory);
                                                                $decimales2 = strlen($inventario2) - strrpos($inventario2, '.') - 1;
                                                            }
                                                            ?>
                                                            <tr>
                                                                <td id="almacenCostoPromedio{{ $key }}">
                                                                    {{ $item->depots_name }}</td>
                                                                <td id="inventarioCostoPromedio{{ $key }}">
                                                                    {{ number_format($inventario2, $decimales2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                @else
                                                    <tr>
                                                        <td>No hay información</td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>

                                        <div class="col-md-12" id="contenedorImagen">

                                            @if (isset($imagenesArticulo) && $imagenesArticulo != null && count($imagenesArticulo) > 0)
                                                <div class="panel panel-default widget-slider">
                                                    <div class="panel-heading">
                                                        <h5 class="panel-title">Imagenes Artículo</h5>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div id="carousel-example-generic" class="carousel slide"
                                                            data-ride="carousel">

                                                            <!-- Wrapper for slides -->
                                                            <div class="carousel-inner">

                                                                @foreach ($imagenesArticulo as $articuloImg)
                                                                    <div
                                                                        class="item {{ $loop->first ? 'active' : '' }}">
                                                                        <?php
                                                                        $FileArray = explode('/', $articuloImg['articlesImg_file']);
                                                                        $longitudFile = count($FileArray);
                                                                        $file = $FileArray[$longitudFile - 1];
                                                                        $quitamosDoblesDiagonales = str_replace(['//', '///', '////'], '/', 'archivo/' . $articuloImg['articlesImg_path']);
                                                                        ?>
                                                                        <div class='imgContenedorPreview'
                                                                            style="display:flex; justify-content:center"
                                                                            id="{{ $articuloImg['articlesImg_id'] }}">

                                                                            <a data-fancybox='demo'
                                                                                data-src='{{ url($quitamosDoblesDiagonales) }}'>
                                                                                <img src='{{ url($quitamosDoblesDiagonales) }}'
                                                                                    class="imgPreview">
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                @endforeach

                                                            </div><!-- carousel-inner -->

                                                            <!-- Controls -->
                                                            <a style="color:black" class="left carousel-control"
                                                                href="#carousel-example-generic" data-slide="prev">
                                                                <span class="fa fa-angle-left"
                                                                    style="border: 1px solid black; padding: 3px 7px; border-radius:10px"></span>
                                                            </a>
                                                            <a style="color:black" class="right carousel-control"
                                                                href="#carousel-example-generic" data-slide="next">
                                                                <span class="fa fa-angle-right"
                                                                    style="border: 1px solid black; padding: 3px 7px; border-radius:10px"></span>
                                                            </a>

                                                        </div><!-- carousel -->
                                                    </div><!-- panel-body -->
                                                </div>
                                            @endif

                                        </div>



                                    </div><!-- activity-list -->
                                    <button aria-hidden="true" class="btn btn-white btn-block" data-dismiss="modal">
                                        Cerrar</button>
                                </div><!-- tab-pane -->

                                <div class="tab-pane" id="followers">
                                    <div class="follower-list">

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="descripcionCostoPromedio2"
                                                    class="negrita">Descripción:</label>
                                                <input type="text" readonly class="form-control input-sm"
                                                    value="{{ isset($infoArticulo) ? $infoArticulo->articles_descript : '' }}"
                                                    name="descripcionCostoPromedio2" id="descripcionCostoPromedio2">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="tipoCostoPromedio" class="negrita">Tipo:</label>
                                                <input type="text" readonly class="form-control input-sm"
                                                    value="{{ isset($infoArticulo) ? $infoArticulo->articles_type : '' }}"
                                                    name="tipoCostoPromedio" id="tipoCostoPromedio">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="estatusCostoPromedio" class="negrita">Estatus:</label>
                                                <input type="text" readonly class="form-control input-sm"
                                                    value="{{ isset($infoArticulo) ? $infoArticulo->articles_status : '' }}"
                                                    name="estatusCostoPromedio" id="estatusCostoPromedio">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="precioCostoPromedio" class="negrita">Precio
                                                    Lista:</label>
                                                <input type="text" readonly class="form-control input-sm"
                                                    value="{{ isset($infoArticulo) ? '$' . floatVal($infoArticulo->articles_listPrice1) : '' }}"
                                                    name="precioCostoPromedio" id="precioCostoPromedio">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="unidadCostoPromedio" class="negrita">Unidad de
                                                    Compra:</label>
                                                <input type="text" readonly class="form-control input-sm"
                                                    value="{{ isset($infoArticulo) ? (isset($infoArticulo->units_unit) ? $infoArticulo->units_unit : $unidad[$infoArticulo->articles_unitBuy]) : '' }}"
                                                    name="unidadCostoPromedio" id="unidadCostoPromedio">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="categoriaCostoPromedio" class="negrita">Categoría</label>
                                                <input type="text" readonly class="form-control input-sm"
                                                    value="{{ isset($infoArticulo) ? $infoArticulo->articles_category : '' }}"
                                                    name="categoriaCostoPromedio" id="categoriaCostoPromedio">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="familiaCostoPromedio" class="negrita">Familia:</label>
                                                <input type="text" readonly class="form-control input-sm"
                                                    value="{{ isset($infoArticulo) ? $infoArticulo->articles_family : '' }}"
                                                    name="familiaCostoPromedio" id="familiaCostoPromedio">
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="ivaCostoPromedio" class="negrita">% IVA:</label>
                                                <input type="text" readonly class="form-control input-sm"
                                                    value="{{ isset($infoArticulo) ? $infoArticulo->articles_porcentIva : '' }}"
                                                    name="ivaCostoPromedio" id="ivaCostoPromedio">
                                            </div>
                                        </div>
                                    </div>
                                    <!--follower-list -->
                                    <button aria-hidden="true" class="btn btn-white btn-block" data-dismiss="modal">
                                        Cerrar</button>
                                </div><!-- tab-pane -->

                                <div class="tab-pane" id="following">
                                    <div class="activity-list">

                                        <div class="col-md-12">

                                            <div class="form-group">
                                                <label class="col-sm-4 control-label"><strong>Último Costo:</strong>
                                                </label>
                                                <div class="col-sm-8">
                                                    <input type="text" readonly class="form-control input-sm"
                                                        value="{{ isset($infoArticulo) ? '$' . floatVal($infoArticulo->articlesCost_lastCost) : '' }}"
                                                        id="ultimoCostoPromedio">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-sm-4 control-label"><strong>Último Costo
                                                        Promedio:</strong> </label>
                                                <div class="col-sm-8">
                                                    <input type="text" readonly class="form-control input-sm"
                                                        value="{{ isset($infoArticulo) ? '$' . floatVal($infoArticulo->articlesCost_averageCost) : '' }}"
                                                        id="ultimoCostoPromedio2">
                                                </div>
                                            </div>

                                        </div>
                                    </div><!-- activity-list -->
                                    <button aria-hidden="true" class="btn btn-white btn-block" data-dismiss="modal">
                                        Cerrar</button>
                                </div><!-- tab-pane -->


                                @if (isset($articulosKitInfo) && count($articulosKitInfo) > 0)
                                    <div class="tab-pane" id="KitsArticulos">
                                        <div class="activity-list">

                                            <div class="col-md-12">
                                                <table class="table">
                                                    @if (isset($articulosKitInfo))
                                                        <tr>
                                                            <th>Artículo</th>
                                                            <th>Disponible</th>
                                                        </tr>

                                                        <tbody class="tableKitsArticulos">
                                                            @foreach ($articulosKitInfo as $item)
                                                                <?php
                                                                if (isset($articulosKitInfo)) {
                                                                    $cantidad = floatVal($item->articlesInv_inventory);
                                                                    $decimales = strlen($cantidad) - strrpos($cantidad, '.') - 1;
                                                                }
                                                                ?>
                                                                <tr>
                                                                    <td id="articuloKit{{ $key }}">
                                                                        {{ $item->articles_descript }}</td>
                                                                    <td id="cantidadKit{{ $key }}">
                                                                        {{ number_format($cantidad, $decimales) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    @else
                                                        <tr>
                                                            <td>No hay información</td>
                                                        </tr>
                                                    @endif
                                                </table>
                                            </div>

                                        </div><!-- activity-list -->
                                        <button aria-hidden="true" class="btn btn-white btn-block"
                                            data-dismiss="modal">
                                            Cerrar</button>
                                    </div><!-- tab-pane -->
                                @endif


                                <div class="tab-pane" id="siguiente">
                                    <div class="activity-list">

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="especificacionesArticulo"
                                                    class="negrita">Descripción:</label>
                                                <textarea class="form-control input-sm" id="especificacionesArticulo" name="especificacionesArticulo">{{ isset($infoArticulo) ? $infoArticulo->articles_specifications : '' }}</textarea>
                                            </div>
                                        </div>


                                        {{-- <div class="col-md-12">
                                        <div class="form-group">
                                            {!! Form::label('especificacionesArticulo', 'Especificaciones', ['class' => 'negrita']) !!}
                                            {!! Form::textarea('especificacionesArticulo', isset($infoArticulo) ? $infoArticulo->articles_specifications : '',
                                            [
                                                'class' => 'form-control input-sm',
                                                'id' => 'especificacionesArticulo',
                                                // 'autocomplete' => 'on',
                                            ]) !!}
                                        </div>
                                    </div> --}}
                                    </div><!-- activity-list -->
                                </div><!-- tab-pane -->




                            </div><!-- tab-content -->

                        </div>
                    </div>

                    <div class="modal-footer">

                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade bd-example-modal-lg modal6" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="informacionProveedorModal" style="display: ">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Información Cliente</h5>
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs nav-line" style="margin: 0px !important">
                            {{-- <li class="active"><a href="#activities2" data-toggle="tab"><strong></strong></a></li> --}}
                            <li class="active"><a href="#following2" data-toggle="tab"><strong>Datos Generales</strong></a></li>
                            <li class=""><a href="#followers2" data-toggle="tab"><strong>Anticipos y Pendientes por Cobrar</strong></a></li>
                        </ul>
                    </div>
                    <div>
                        <div>
                            <div class="col-sm-8 col-md-12">
                                <!-- Tab panes -->
                                <div class="tab-content nopadding noborder" style="margin-top: 15px;">

                                    <div class="tab-pane active" id="following2">
                                        <div class="activity-list">

                                            <div class="col-md-6">

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio"
                                                            class="negrita">Nombre:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->customers_businessName : '' }}"
                                                            name="infNombreProveedor" id="infNombreProveedor">
                                                    </div>
                                                </div>

                                                @php
                                                    //vamos a quitar todos los números y caracteres especiales a customers_colonyFractionation para dejar solo letras y acentos y también dejaremos los espacios
                                                    isset($infoProveedor) ? $infoProveedor->customers_colonyFractionation = preg_replace('/[^A-Za-záéíóúÁÉÍÓÚ ]/', '', $infoProveedor->customers_colonyFractionation) : '';
                                                    // $infoProveedor->customers_colonyFractionation = preg_replace('/[^A-Za-záéíóúÁÉÍÓÚ ]/', '', $infoProveedor->customers_colonyFractionation);
                                                @endphp
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio"
                                                            class="negrita">Dirección:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->customers_addres . ' Ext. ' . $infoProveedor->customers_outdoorNumber . ' ' . $infoProveedor->customers_roads . '. Col: ' . $infoProveedor->customers_colonyFractionation : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio"
                                                            class="negrita">Telefono:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->customers_cellphone : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio" class="negrita">RFC:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->customers_RFC : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio"
                                                            class="negrita">Categoria:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->customers_category : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio"
                                                            class="negrita">Grupo:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->customers_group : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>
                                                <?php
                                                if (isset($infoProveedor)) {
                                                    if ($infoProveedor->customers_type === '0') {
                                                        $infoProveedor->customers_type = 'Persona Física';
                                                    } else {
                                                        $infoProveedor->customers_type = 'Persona Moral';
                                                    }
                                                }
                                                
                                                ?>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio" class="negrita">Tipo:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->customers_type : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>


                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio" class="negrita">Saldo
                                                            Global:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($saldoGeneral) ? '$' . number_format($saldoGeneral, 2) : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>


                                            </div>
                                            <div class="col-md-6">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio"
                                                            class="negrita">Lista de Precios:</label>
                                                            {{-- hacemos un operador ternario customers_priceList, si es listPrice1 será Precio 1, si es listPrice2 será Precio 2, si es listPrice3 será Precio 3 así hasta el 5 --}}
                                                        <input type="text" readonly class="form-control input-sm"
                                                        value="{{ isset($infoProveedor) ? 
                                                                  ($infoProveedor->customers_priceList === 'listPrice1' ? 'Precio 1' :
                                                                   ($infoProveedor->customers_priceList === 'listPrice2' ? 'Precio 2' :
                                                                    ($infoProveedor->customers_priceList === 'listPrice3' ? 'Precio 3' :
                                                                     ($infoProveedor->customers_priceList === 'listPrice4' ? 'Precio 4' :
                                                                      ($infoProveedor->customers_priceList === 'listPrice5' ? 'Precio 5' : '')))))
                                                                 : '' }}"
                                                        name="articuloCostoPromedio" id="articuloCostoPromedio">

                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio"
                                                            class="negrita">Condición de Pago:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->creditConditions_name : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio"
                                                            class="negrita">Límite de Crédito:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="$ {{ isset($infoProveedor) ? number_format($infoProveedor->customers_creditLimit, 2) : '0.00' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio" class="negrita">Regimen Fiscal:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->regimenFiscal->descripcion . ' - ' . $infoProveedor->customers_taxRegime ?? '' : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <table class="table table-hover mb30">
                                                        <thead>
                                                            <tr>
                                                                {{-- pondremos Top 5 Productos más Vendidos y abajo ponemos en chiquito últimos 60 días --}}
                                                                <th colspan="2">Top 5 Productos más Vendidos
                                                                    <br>
                                                                    <small>(Últimos 60 días)</small></th>
                                                            </tr>
                                                            <tr>
                                                                <th>Producto</th>
                                                                <th>Cantidad</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if (isset($top5Productos))
                                                                       @foreach ($top5Productos as $item)
                                                                            <tr>
                                                                                <td>{{ $item->salesDetails_descript }}</td>
                                                                                <td>{{ $item->total }}</td>
                                                                            </tr>
                                                                          @endforeach
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>


                                        </div><!-- activity-list -->
                                    </div><!-- tab-pane -->
                                    <div class="tab-pane" id="followers2">
                                        <div class="activity-list">

                                            <div class="col-md-12">
                                                <h5 class="mb10">Movimientos</h5>
                                                <!-- Nav tabs -->
                                                <ul class="nav nav-tabs nav-justified">

                                                    @if (isset($monedasMov))
                                                        @foreach ($monedasMov as $moneda)
                                                            <li class="{{ $loop->first ? 'active' : '' }}">
                                                                <a href="#{{ $moneda->money_key }}" data-toggle="tab">
                                                                    {{ $moneda->money_key }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    @endif

                                                </ul>

                                                <!-- Tab panes -->
                                                <div class="tab-content mb30 scrollInfoProveedor">

                                                    @if (isset($monedasMov))
                                                        @foreach ($monedasMov as $moneda)
                                                            <div class="tab-pane {{ $loop->first ? 'active' : '' }}"
                                                                id="{{ trim($moneda->money_key) }}">
                                                                <div class="table-responsive">
                                                                    <table class="table table-hover mb30 prueba">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Mov+Folio</th>
                                                                                <th>Referencia</th>
                                                                                <th>Días</th>
                                                                                <th>Saldo</th>
                                                                                <th>Fecha</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @if (isset($movimientosProveedor))
                                                                                @foreach ($movimientosProveedor as $movimiento)
                                                                                    @if ($movimiento->accountsReceivable_money == trim($moneda->money_key))
                                                                                        <tr>
                                                                                            <td>{{ $movimiento->accountsReceivable_movement . ' ' . $movimiento->accountsReceivable_movementID }}
                                                                                            </td>
                                                                                            <td>{{ $movimiento->accountsReceivable_reference }}
                                                                                            </td>
                                                                                            <td>{{ $movimiento->accountsReceivable_moratoriumDays }}
                                                                                            </td>
                                                                                            <td>{{ '$' . number_format($movimiento->accountsReceivable_balance, 2) }}
                                                                                            </td>
                                                                                            <td>{{ \Carbon\Carbon::parse($movimiento->accountsReceivable_issuedate)->format('d/m/Y') }}
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endif
                                                                                @endforeach
                                                                            @endif
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div><!-- tab-content -->

                                            </div>
                                        </div><!-- activity-list -->
                                    </div><!-- tab-pane -->



                                </div><!-- tab-content -->

                            </div>
                        </div><!-- panel -->
                    </div>

                    <div class="modal-footer">
                        <!--follower-list -->
                        <button aria-hidden="true" class="btn btn-white btn-block" data-dismiss="modal">
                            Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg modal4" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="modalSerie2">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle"> Serie del artículo: <strong
                                class="articuloKeySerie2"></strong></h5>

                    </div>
                    <div class="modal-body">
                        <input style="display: none" type="hidden" value="" id="clavePosicion2" />
                        <div class="row" id="form-articulos-serie2">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="quitarSeries2">
                            Seleccionar series
                        </button>
                        {{-- <button type="button" class="btn btn-primary" id="generarLotesSeries">
                    Generar Lote de Series
                </button> --}}
                        {{-- <button type="button" class="btn btn-success" id="modal6Agregar">Agregar</button> --}}
                    </div>
                </div>
            </div>
        </div>



        <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
            aria-hidden="true" id="cancelarFacturaModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Cancelar Factura</h5>
                        <ul class="nav nav-tabs nav-line" style="margin: 0px !important">
                            <li class="active"><a href="#following2" data-toggle="tab"><strong>Datos
                                        de la Cancelación</strong></a>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <div class="col-sm-8 col-md-12">
                            <!-- Tab panes -->
                            <div class="tab-content nopadding noborder">
                                <div class="tab-pane active" id="activities">
                                    <div class="activity-list">
                                        <br>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="motivoCancelacion" class="negrita">Motivo de
                                                    Cancelación:</label>
                                                <select name="motivoCancelacion" id="select-motivoCancelacion"
                                                    class="widthAll">
                                                    <option value="" selected aria-disabled="true">Selecciona una
                                                        opción: </option>
                                                    @foreach ($select_MotivoCancelacion as $motivo)
                                                        <option value="{{ $motivo->c_Cancelacion }}">
                                                            {{ $motivo->descripcion }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="folioSustitucion" class="negrita">Folio de
                                                    Sustitución:</label>
                                                <select name="folioSustitucion" id="select-folioSustitucion"
                                                    class="widthAll" disabled>
                                                    <option value="" selected aria-disabled="true">Selecciona una
                                                        opción</option>
                                                    @foreach ($facturasTimbradas as $factura)
                                                        <option value="{{ $factura->cfdi_UUID }}">
                                                            {{ $factura->sales_movement }}
                                                            {{ $factura->sales_movementID }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                    </div><!-- activity-list -->


                                    <div class="modal-footer">
                                        <button aria-hidden="true" class="btn btn-white" data-dismiss="modal">
                                            Cerrar
                                        </button>
                                        <button type="button" class="btn btn-success"
                                            id="btn-modal-cancelarFactura">Aceptar</button>
                                    </div>

                                </div><!-- tab-pane -->

                            </div><!-- tab-content -->

                        </div>
                    </div>

                    <div class="modal-footer">

                    </div>
                </div>
            </div>
        </div>

        {{-- <div class="modal fade bd-example-modal-lg modal7" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true" id="modalListaEmpaque">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="modal-header">
                    <div style="display: flex; justify-content:space-between; margin-top:10px">
                        <h5 class="modal-title" id="exampleModalLongTitle"> Lista de empaque: <strong
                                class="serieEmpaqueKey"></strong>
                        </h5>
                        <h5> Cantidad/Peso: <strong class="serieEmpaqueCantidad"></strong>
                        </h5>
                    </div>

                    <div class="progress progress-striped active">
                        <div style="width: 0%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="0"
                            role="progressbar" class="progress-bar progress-bar-info" id="progressBarEmpaque">
                            <span class="" id="porcentajeBar">0% Complete (success)</span>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <input style="display: none" type="hidden" value="" id="unidadEmpaqueKey" />
                    <input style="display: none" type="hidden" value="" id="referenceTabla" />
                    <!--table-responsive -->
                    <div class="table-responsive">
                        <div class="panel ">
                            <div>
                                <div class="table-responsive">
                                    <div class="panel-body">
                                        <table id="tableEmpaque"
                                            class="table table-fixed table-striped-col nowrap col-sm-12">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">No. </th>
                                                    <th class="text-center">Clave</th>
                                                    <th class="text-center">Nombre</th>
                                                    <th class="text-center">Empaque</th>
                                                    <th class="text-center">Peso</th>
                                                    <th class="text-center">Peso Unidad</th>
                                                    <th class="text-center">Neto</th>
                                                    <!-- <th class="text-center"></th> -->
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                            <tfoot>

                                                <tr>
                                                    <th style="visibility: hidden" class="text-center"></th>
                                                    <th style="visibility: hidden" class="text-center"></th>
                                                    <th style="visibility: hidden" class="text-center"></th>
                                                    <th style="visibility: hidden" class="text-center"></th>
                                                    <th class="text-center unidadEmpaqueCantidad">Total Bruto: <span
                                                            id="totalBrutoEmpaque">0</span></th>
                                                    <th class="text-center">Total Un. Empaque: <span
                                                            id="totalUnidadEmpaque">0</span>
                                                    </th>
                                                    <th class="text-center unidadEmpaqueAcumulado"> Total Neto: <span
                                                            id="totalNetoEmpaque">0</span>
                                                    </th>

                                                    <!-- <th class="text-center"></th> -->
                                                </tr>

                                            </tfoot>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="modal7Agregar">Agregar</button>
                    <button type="button" class="btn btn-success" id="generarEmpaques">
                        Generar lista de empaque
                    </button>
                </div>

            </div>
        </div>
    </div> --}}


        <div class="modal fade bd-example-modal-lg modal7" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="modalKitVentas">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        id="cerrar-kitModal" style="display:none">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="exampleModalLongTitle"> Asistente Kits <strong
                                    class="serieEmpaqueKey"></strong>
                            </h5>
                        </div>

                        <div class="panel-body" style="padding: 1px">

                            <div class="col-md-4">
                                <div class="col" style="display:flex;  justify-content: space-between;">
                                    <h5> Cantidad: </h5>
                                    <input type="number" class="form-control input-sm" id="cantidadKit"
                                        name="cantidadKit" value="" style="width: 60%;" />
                                </div>
                            </div>

                            <div class="col-md-3">
                                <h5> Artículo: <strong class="serieEmpaqueCantidad"></strong>
                                </h5>
                            </div>

                            <div class="col-md-4">
                                <h5> Descripción: <strong class="serieEmpaqueDescripcion"></strong>
                                </h5>
                            </div>




                        </div>

                    </div>
                    <div class="modal-body">
                        {{-- <input style="display: none" type="hidden" value="" id="unidadEmpaqueKey" /> --}}
                        <input style="display: none" type="hidden" value="" id="referenceTabla" />
                        <!--table-responsive -->
                        <div class="table-responsive">
                            <div class="panel ">
                                <div>
                                    <div class="table-responsive">
                                        <div class="panel-body">
                                            <table id="tableKits" class="table table-fixed table-striped-col col-sm-12">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">Componente </th>
                                                        <th class="text-center">Artículo</th>
                                                        <th class="text-center">Descripción</th>
                                                        <th class="text-center">Disponible</th>
                                                        <th class="text-center">Cantidad</th>
                                                        <th class="text-center">Tipo</th>
                                                        <th class="text-center">Observaciones</th>
                                                        <th class="text-center">-</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>

                                            </table>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="generarKits">
                            Generar kit
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg modal44" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="modalSerie22">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle"> Serie del artículo: <strong
                                class="articuloKeySerie22"></strong></h5>

                    </div>
                    <div class="modal-body">
                        <input style="display: none" type="hidden" value="" id="clavePosicion22" />
                        <div class="row" id="form-articulos-serie22">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="quitarSeries22">
                            Seleccionar series
                        </button>
                        {{-- <button type="button" class="btn btn-primary" id="generarLotesSeries">
                Generar Lote de Series
            </button> --}}
                        {{-- <button type="button" class="btn btn-success" id="modal6Agregar">Agregar</button> --}}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg modal9" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="modalComercioExterior">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <div style="display: flex; justify-content:space-between; margin-top:10px">
                            <h5 class="modal-title" id="exampleModalLongTitle"> Comercio Exterior
                            </h5>
                            @if (!isset($venta))
                                <div class="btn-group mr5 open">
                                    <button type="button" class="btn btn-primary">Acción</button>
                                    <button type="button" class="btn btn-primary dropdown-toggle"
                                        data-toggle="dropdown">
                                        <span class="caret"></span>
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="" id="agregarDatosComercio">Agregar</a></li>
                                        <li><a href="" id="GuardarComercioExterior">Guardar</a></li>
                                    </ul>
                                </div>
                            @else
                                @if ($venta->sales_movement === 'Factura' && $venta->sales_status !== 'FINALIZADO')
                                    <div class="btn-group mr5 open">
                                        <button type="button" class="btn btn-primary">Acción</button>
                                        <button type="button" class="btn btn-primary dropdown-toggle"
                                            data-toggle="dropdown">
                                            <span class="caret"></span>
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li><a href="" id="agregarDatosComercio">Agregar</a></li>
                                            <li><a href="" id="GuardarComercioExterior">Guardar</a></li>
                                        </ul>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-6">
                                <label for="incoTerm" class="negrita">Incoterm</label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control " id="incoTermKey" name="incoTermKey"
                                        value="{{ isset($comercioExt) ? $comercioExt->salesForeingTrade_incoterm : '' }}"
                                        readonly />
                                    <span class="input-group-btn ">
                                        <button type="button" class="btn btn-default" data-toggle="modal"
                                            data-target=".modal10" id="incoTermModal">...</button>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="incoTermName" class="negrita">:</label>
                                    <input type="text" class="form-control " name="incoTermName"
                                        id="incoTermName"
                                        value="{{ isset($comercioExt) ? (isset($incotermNombre) ? $incotermNombre->descripcion : '') : '' }}"
                                        readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="checkbox" name="subdivision" id="subdivision"
                                        {{ isset($comercioExt) ? ($comercioExt->salesForeingTrade_subdivision !== '0' ? 'checked' : '') : '' }}>
                                    <label for="subdivision">Subdivisión</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="checkbox" name="origen" id="origen"
                                        {{ isset($comercioExt) ? ($comercioExt->salesForeingTrade_certificateOforigin !== '0' ? 'checked' : '') : '' }}>
                                    <label for="origen">Certificado Origen</label>
                                </div>
                            </div>


                            <div class="col-md-12">
                                <hr>
                            </div>

                            <div class="col-md-6">
                                <label for="motivoTraslado" class="negrita">Motivo Traslado</label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control " id="motivoTrasladoKey"
                                        name="motivoTrasladoKey"
                                        value="{{ isset($comercioExt) ? $comercioExt->salesForeingTrade_transferReason : '' }}"
                                        readonly />
                                    <span class="input-group-btn ">
                                        <button type="button" class="btn btn-default" data-toggle="modal"
                                            data-target=".modal11" id="motivoTrasladoModal">...</button>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="motivoTrasName" class="negrita">:</label>
                                    <input type="text" class="form-control " name="motivoTrasName"
                                        id="motivoTrasName"
                                        value="{{ isset($comercioExt) ? (isset($trasladoNombre) ? $trasladoNombre->descripcion : '') : '' }}"
                                        readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipoOperacion" class="negrita">Tipo Operación <span
                                            class="asterisk">*</span></label>
                                    <select name="tipoOperacion" id="select-tipoOperacion" class="widthAll">
                                        <option value="">Selecciona uno...</option>

                                        @foreach ($select_tipoOperacion as $tipoOperacion)
                                            <option value="{{ $tipoOperacion->c_TipoOperacion }}">
                                                {{ $tipoOperacion->descripcion }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipoOperacion" class="negrita">Clave Pedimento:</label>
                                    <select name="tipoOperacion" id="select-clavePedimento" class="widthAll">
                                        <option value="">Selecciona uno...</option>

                                        @foreach ($select_clavePedimento as $clavePedimento)
                                            <option value="{{ $clavePedimento->c_ClavePedimento }}">
                                                {{ $clavePedimento->descripcion }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>


                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="numCertificadoOrigen" class="negrita">Núm. Certificado Origen:</label>
                                    <input type="text" class="form-control"
                                        value="{{ isset($comercioExt) ? $comercioExt->salesForeingTrade_numberCertificateOrigin : '' }}"
                                        name="numCertificadoOrigen" id="numCertificadoOrigen" readonly>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="numExportadorConfiable" class="negrita">Núm. Exportador
                                        Confiable:</label>
                                    <input type="text" class="form-control"
                                        value="{{ isset($comercioExt) ? $comercioExt->salesForeingTrade_trustedExportedNumber : '' }}"
                                        name="numExportadorConfiable" id="numExportadorConfiable" readonly>
                                </div>
                            </div>


                        </div> <!-- row -->


                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade bd-example-modal-lg modal10" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="incoTermModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">IncoTerm</h5>

                    </div>
                    <div class="modal-body">
                        <div class="panel table-panel">
                            <table id="shTable10" class="table table-striped table-bordered widthAll">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Descripción</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($select_incoTerm as $incoTerm)
                                        <tr>
                                            <td>{{ $incoTerm->c_INCOTERM }}</td>
                                            <td>{{ $incoTerm->descripcion }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div><!-- panel -->
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade bd-example-modal-lg modal11" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="motivoTrasladoModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Motivo Traslado</h5>

                    </div>
                    <div class="modal-body">
                        <div class="panel table-panel">
                            <table id="shTable11" class="table table-striped table-bordered widthAll">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Descripción</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($select_motivoTraslado as $motivoTras)
                                        <tr>
                                            <td>{{ $motivoTras->c_MotivoTraslado }}</td>
                                            <td>{{ $motivoTras->descripcion }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div><!-- panel -->
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg modal13" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="facturaModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Información Factura</h5>

                    </div>
                    <div class="modal-body">
                        <div class="panel table-panel">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="rdio rdio-primary">
                                        <input type="radio" name="radioFactura" value="2" id="radioPrimary"
                                            checked>
                                        <label for="radioPrimary">Factura normal</label>
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="rdio rdio-warning">
                                        <input type="radio" name="radioFactura" value="3" id="radioWarning">
                                        <label for="radioWarning">Factura relacionada</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 facturaRelacion" style="display: none">
                                <div class="form-group">
                                    <label for="facturaRelacion" class="negrita">Factura relacion:</label>
                                    <select name="facturaRelacion" id="select-facturaRelacion" class="widthAll">
                                        <option value="" selected aria-disabled="true">Selecciona una opción
                                        </option>
                                        @foreach ($facturasTimbradas as $factura)
                                            <option value="{{ $factura->sales_id }}">
                                                {{ $factura->sales_movement }} {{ $factura->sales_movementID }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div><!-- panel -->
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal"
                            id="facturaInfo-aceptar">Aceptar</button>
                        <button type="button" class="btn btn-success" data-dismiss="modal"
                            id="facturaInfo-aceptar2" style="display: none">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal calculadora ventas -->
        <div class="modal fade" id="ventasCalculadora" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-center" id="exampleModalCenterTitle">
                            Cobro - Factura
                        </h5>
                    </div>
                    <div class="modal-body">
                        <!-- formulario -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cantidad1" class="negrita">Importe:</label>
                                    <input type="text" class="form-control formatoMoney" name="cantidad1"
                                        id="cantidad1"
                                        value="{{ isset($cobroVenta) ? '$' . floatVal($cobroVenta->salesPayment_amount1) : '$0.00' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="metodoPago1" class="negrita">Forma Cobro:</label>
                                    <select name="metodoPago1" id="metodoPago1" class="widthAll cobrosForm">
                                        <option value="" selected aria-disabled="true">Selecciona uno...</option>

                                        @foreach ($select_formaPago as $forma)
                                            <option value="{{ $forma->formsPayment_key }}"
                                                data-target="{{ $forma->formsPayment_sat }}">
                                                {{ $forma->formsPayment_key . ' - ' . $forma->formsPayment_name }}
                                            </option>
                                        @endforeach

                                    </select>


                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cantidad2" class="negrita">Importe:</label>
                                    <input type="text" class="form-control formatoMoney" name="cantidad2"
                                        id="cantidad2"
                                        value="{{ isset($cobroVenta) ? '$' . floatVal($cobroVenta->salesPayment_amount2) : '$0.00' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="metodoPago2" class="negrita">Forma Cobro:</label>
                                    <select name="metodoPago2" id="metodoPago2" class="widthAll cobrosForm">
                                        <option value="">Selecciona uno...</option>
                                        @foreach ($select_formaPago as $forma)
                                            <option value="{{ $forma->formsPayment_key }}"
                                                data-target="{{ $forma->formsPayment_sat }}">
                                                {{ $forma->formsPayment_key . ' - ' . $forma->formsPayment_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cantidad3" class="negrita">Importe:</label>
                                    <input type="text" class="form-control formatoMoney" name="cantidad3"
                                        id="cantidad3"
                                        value="{{ isset($cobroVenta) ? '$' . floatVal($cobroVenta->salesPayment_amount3) : '$0.00' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="metodoPago3" class="negrita">Forma Cobro:</label>
                                    <select name="metodoPago3" id="metodoPago3" class="widthAll cobrosForm">
                                        <option value="">Selecciona uno...</option>

                                        @foreach ($select_formaPago as $forma)
                                            <option value="{{ $forma->formsPayment_key }}"
                                                data-target="{{ $forma->formsPayment_sat }}">
                                                {{ $forma->formsPayment_key . ' - ' . $forma->formsPayment_name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cuentaPago" class="negrita">Cuenta de dinero:</label>
                                    <select name="cuentaPago" id="cuentaPago" class="widthAll">
                                        <option value="">Selecciona uno...</option>

                                        @foreach ($moneyAccounts as $account)
                                            <option value="{{ $account->moneyAccounts_key }}">
                                                {{ $account->moneyAccounts_key }}</option>
                                        @endforeach

                                    </select>

                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="metodoPago7" class="negrita">Forma de cambio:</label>
                                    <select name="metodoPago7" id="metodoPago7" class="widthAll">
                                        <option value="">Selecciona uno...</option>

                                        @foreach ($select_formaPago as $forma)
                                            <option value="{{ $forma->formsPayment_key }}"
                                                data-target="{{ $forma->formsPayment_sat }}">
                                                {{ $forma->formsPayment_key . ' - ' . $forma->formsPayment_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="infAdicional" class="negrita">Información Adicional:</label>
                                    <input type="text" class="form-control" name="infAdicional" id="infAdicional"
                                        value="{{ isset($cobroVenta) ? $cobroVenta->salesPayment_additionalInformation : '' }}"
                                        readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="totalC" class="negrita">Total Factura:</label>
                                    <input type="text" class="form-control" name="totalC" id="totalC"
                                        value="{{ isset($cobroVenta) ? '$' . floatVal($cobroVenta->salesPayment_fullCharge) : '$0.00' }}"
                                        readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cambioTotal" class="negrita">Total Cobrado:</label>
                                    <input type="text" class="form-control" name="totalCobrado" id="totalCobrado"
                                        value="$0" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cambioTotal" class="negrita">Cambio:</label>
                                    <input type="text" class="form-control" name="cambioTotal" id="cambioTotal"
                                        value="{{ isset($cobroVenta) ? '$' . floatVal($cobroVenta->salesPayment_Change) : '$0.00' }}"
                                        readonly>
                                </div>
                            </div>


                            <input type="hidden" class="form-control" name="tipoCuenta" id="tipoCuenta"
                                value="" readonly>




                        </div>
                        <div class="modal-footer display-center">

                            @if (!isset($venta))
                                <button type="button" class="btn btn-success"
                                    id="GuardarCobroFactura">Guardar</button>
                                <button type="button" class="btn btn-primary" id="btn-modal-venta">Afectar</button>
                            @else
                                @if ($venta->sales_status !== 'FINALIZADO')
                                    <button type="button" class="btn btn-success"
                                        id="GuardarCobroFactura">Guardar</button>
                                    <button type="button" class="btn btn-primary"
                                        id="btn-modal-venta">Afectar</button>
                                @endif
                            @endif

                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="email-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form
                        action="{{ route('vista.modulo.ventas.emailCotizacion', ['idVenta' => isset($venta) ? $venta->sales_id : 0]) }}"
                        method="POST">
                        @csrf
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="modal-header">
                            <h5 class="modal-title">Cuenta de correo del cliente</h5>
                        </div>
                        <div class="modal-body">
                            <p>Seleccione las direcciones de correo electrónico para enviar la información</p>
                            <hr>
                            <ol type="A">
                                @if (!empty($venta->customer))
                                    <li>
                                        <label>
                                            <input class="mailTo" checked type="checkbox" name="email[]"
                                                value="{{ $venta->customer->customers_mail1 }}">
                                            {{ $venta->customer->customers_mail1 }}
                                        </label>
                                    </li>
                                    <li>
                                        <label>
                                            <input class="mailTo" type="checkbox" name="email[]"
                                                value="{{ $venta->customer->customers_mail2 }}">
                                            {{ $venta->customer->customers_mail2 }}
                                        </label>
                                    </li>
                                    {{-- <li>
                                    <label>
                                        <input class="mailTo" type="checkbox" value="recepcion@meridanotaria55.com"> recepcion@meridanotaria55.com
                                    </label>
                                </li> --}}
                                    <li>
                                        <label class="form-check-label">
                                            <input class="mailTo form-check-input" type="checkbox" value="other">
                                            Otro correo:
                                            <input class="form-control form-control-sm" type="email"
                                                id="anotherEmail" name="anotherEmail"
                                                placeholder="Correo electrónico">
                                        </label>
                                    </li>
                                @endif
                            </ol>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Enviar</button>
                            {{-- <a href="#" class="btn btn-secondary close-email">Cancelar</a> --}}
                            <button aria-hidden="true" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>




        @include('include.mensaje')

        <script>
            const detalleVentas = "{{ isset($articulosByVenta) ? count($articulosByVenta) : 0 }}";

            if (detalleVentas == 0) {
                $(".retencion").hide();
            }
            //  $(".retencion").hide();
            //variable para ver si se puede modificar el precio de venta
            let precioVariable = '{{ Auth::user()->user_block_sale_prices }}';
            const isSalesPaymentSave = '{{ isset($cobroVenta) }}';

            let calculoImpuestos = '{{ session('company')->companies_calculateTaxes }}';

            $('input[id^="c_unitario"]').each(function(index, value) {
                if (precioVariable == '1') {
                    $(this).attr('readonly', true);
                }
            });
            let contadorArticulosString = '{{ isset($articulosByVenta) ? count($articulosByVenta) : 0 }}';
            let contadorArticulos = parseInt(contadorArticulosString);
            jQuery('#cantidadArticulos').val(contadorArticulos);
            let monedaDefecto = "{{ isset($parametro) ? $parametro['generalParameters_defaultMoney'] : PESOS }}";
            let movimiento = $("#select-movimiento").val();

            if (movimiento === "Pedido") {
                $("#pedidoContenedor").hide();
                // $(".cantidadNeta").show();
                // $(".unidadEmpaque").show();
            } else {
                $("#pedidoContenedor").show();
                // $(".cantidadNeta").hide();
                // $(".unidadEmpaque").hide();
            }


            $("#select-tipoOperacion").val('{{ isset($comercioExt) ? $comercioExt->salesForeingTrade_operationType : '' }}')
                .trigger('change');
            $("#select-clavePedimento").val('{{ isset($comercioExt) ? $comercioExt->salesForeingTrade_petitionKey : '' }}')
                .trigger('change');

            $('#select-moduleCancellation').val('{{ isset($venta) ? $venta['sales_reasonCancellation'] : '' }}').trigger(
                'change.select2');

            let mov = jQuery("#select-movimiento").val();

            if (mov != "Rechazo de Venta") {
                jQuery(".motivoCancelacionDiv").hide();
            } else {
                jQuery(".motivoCancelacionDiv").show();
                jQuery("#contrato").hide();
            }
        </script>
        <script src="{{ asset('js/PROCESOS/ventas.js') }}"></script>
        <script>
            $(document).ready(function() {
                $('#select-moduleConcept').val('{{ isset($venta) ? $venta['sales_concept'] : '' }}').trigger(
                    'change.select2');

                $('#select-choferName').val('{{ isset($venta) ? $venta['sales_driver'] : '' }}').trigger(
                    'change.select2');

                $('#select-vehiculoName').val('{{ isset($venta) ? $venta['sales_vehicle'] : '' }}').trigger(
                    'change.select2');

                $('#select-proveedorCondicionPago').val('{{ isset($venta) ? $venta['sales_condition'] : '' }}')
                    .trigger(
                        'change.select2').change();

                $('#select-clienteCFDI').val('{{ isset($venta) ? $venta['sales_identificationCFDI'] : '' }}').trigger(
                    'change.select2');

                $('#select-precioListaSelect').val('{{ isset($venta) ? $venta['sales_listPrice'] : '' }}').trigger(
                    'change.select2');

                $('#metodoPago1').val('{{ isset($cobroVenta) ? $cobroVenta['salesPayment_paymentMethod1'] : '' }}')
                    .trigger(
                        'change.select2');
                $('#metodoPago2').val('{{ isset($cobroVenta) ? $cobroVenta['salesPayment_paymentMethod2'] : '' }}')
                    .trigger(
                        'change.select2');
                $('#metodoPago3').val('{{ isset($cobroVenta) ? $cobroVenta['salesPayment_paymentMethod3'] : '' }}')
                    .trigger(
                        'change.select2');
                $('#metodoPago7').val(
                    '{{ isset($cobroVenta) ? $cobroVenta['salesPayment_paymentMethodChange'] : '' }}').trigger(
                    'change.select2');
                $('#cuentaPago').val('{{ isset($cobroVenta) ? $cobroVenta['salesPayment_moneyAccount'] : '' }}')
                    .trigger(
                        'change.select2');


                $(".formatoMoney").trigger('change');

                let estadoMovimiento = $("#status").text().trim();
                let movimiento = $("#select-movimiento").val();

                if (movimiento == "Factura" && estadoMovimiento == "FINALIZADO") {
                    $('#metodoPago1').attr('readonly', true);
                    $('#metodoPago2').attr('readonly', true)
                    $('#metodoPago3').attr('readonly', true)
                    $('#metodoPago7').attr('readonly', true)
                    $('#cuentaPago').attr('readonly', true)
                    $("#cantidad1").attr('readonly', true);
                    $("#cantidad2").attr('readonly', true);
                    $("#cantidad3").attr('readonly', true);
                    $("#infAdicional").attr('readonly', true);

                }
            });

            $("#cuentaPago").change();
        </script>
        <script>
            $(document).ready(function() {
                $('.prueba').DataTable({
                    "searching": false,
                    "info": false,
                    "ordering": false,
                    "language": {
                        "lengthMenu": "Mostrar _MENU_ registros por página",
                        "zeroRecords": "No se encontraron registros",
                        "info": "Mostrando página _PAGE_ de _PAGES_",
                        "infoEmpty": "No hay registros disponibles",
                        "infoFiltered": "(filtrado de _MAX_ registros totales)",
                        "search": "Buscar:",
                        "paginate": {
                            "first": "Primero",
                            "last": "Último",
                            "next": "Siguiente",
                            "previous": "Anterior"
                        },
                    }
                });

            });

            window.addEventListener("pageshow", function(event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });
        </script>
    @endsection
