@extends('layouts.layout')


@section('content')
    @foreach (auth()->user()->getAllPermissions()->where('categoria', '=', 'Compras')->pluck('name')->toArray() as $permisos)
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
                    'route' => ['modulo.compras.store-compra'],
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
                    {{-- <li><a href="#tab2-2" data-toggle="tab"><strong>Otros datos - </strong> Recepción de Material</a></li> --}}
                </ul>

                {{-- <div class="progress progress-xs">
                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="45" aria-valuemin="0"
                        aria-valuemax="100"></div>
                </div> --}}



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


                            <li><a href="#" id="eliminar-boton">Eliminar <span
                                        class="glyphicon glyphicon-remove-circle pull-right"></span></a></li>

                            @can('Cancelar')
                                <li><a href="#" id="cancelar-boton">Cancelar <span
                                            class="glyphicon glyphicon-trash pull-right"></span></a></li>
                            @endcan

                            @if (isset($compra) && $compra->purchase_status !== 'INICIAL')
                                <li><a href="{{ route('vista.modulo.compras.reportes', ['idCompra' => $compra['purchase_id']]) }}"
                                        target="_blank">Reporte <span
                                            class="glyphicon glyphicon-list-alt pull-right"></span></a>
                                </li>
                            @endif


                            @can('Afectar')
                                @if (isset($compra) && $compra->purchase_movement !== 'Rechazo de Compra')
                                    <li>
                                        <a href="#" id="copiar-compra">Copiar <span
                                                class="fa fa-copy pull-right"></span></a>
                                    </li>
                                @endif
                            @endcan


                            @if (isset($compra))
                                <li class="divider"></li>
                                <li><a href="{{ route('vista.modulo.compras.create-compra') }}" id="nuevo-boton">Nuevo<span
                                            class="fa fa-file-o pull-right"></span></a></li>
                                <li><a href="{{ route('vista.modulo.compras.anexos', ['id' => $compra->purchase_id]) }}"
                                        id="anexos-boton">Anexos <span
                                            class="glyphicon glyphicon-paperclip pull-right"></span></a></li>
                                <li><a href="" data-toggle="modal" data-target="#ModalFlujo">
                                        Ver flujo
                                        <span class="glyphicon glyphicon-transfer pull-right"></span>
                                    </a></li>
                                {{-- @if ($compra->purchase_status == 'FINALIZADO') --}}

                                <li><a href="#" data-toggle="modal" data-target="#costoPromedioModal"
                                        id="costoPromedio">
                                        Inf. Articulo
                                        <span class="fa fa-tag pull-right"></span></a>
                                </li>
                                {{-- @endif --}}
                                <li><a href="" data-toggle="modal" data-target="#informacionProveedorModal">
                                        Inf. Proveedor
                                        <span class="glyphicon glyphicon-exclamation-sign pull-right"></span>
                                    </a></li>
                            @endif
                            {{-- <li><a href="#">Separated link</a></li> --}}
                        </ul>
                    </div>

                    <div class="tab-pane active" id="tab1-2">
                        <div class="col-md-12 cabecera-informacion">
                            <div id="leyenda-container" style="display: none;">
                                <p id="leyenda"></p>
                            </div>
                            <div class="col-md-3">
                                <!-- Movimientos -->
                                <div class="form-group">
                                    {!! Form::labelValidacion('movimientos', 'Proceso/Operación', 'negrita') !!}
                                    {!! Form::select('movimientos', $movimientos, isset($compra) ? $compra['purchase_movement'] : null, [
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
                                        value="{{ isset($compra) ? $compra['purchase_id'] : 0 }}"readonly>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="folio" class="negrita">Folio:</label>
                                    <input type="number" class="form-control" name="folio" id="folioCompra"
                                        value="{{ isset($compra) ? $compra['purchase_movementID'] : null }}"readonly>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fechaEmision" class="negrita">Fecha</label>
                                    <input type="date" class="form-control input-date" name="fechaEmision"
                                        id="fechaEmision" placeholder="Fecha Emisión"
                                        value="{{ isset($compra) ? \Carbon\Carbon::parse($compra['purchase_issueDate'])->format('Y-m-d') : date('Y-m-d') }}">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    {!! Form::labelValidacion('nameMoneda', 'Moneda', 'negrita') !!}
                                    {!! Form::select(
                                        'nameMoneda',
                                        $selectMonedas,
                                        isset($compra) ? trim($compra['purchase_money']) : $parametro->generalParameters_defaultMoney,
                                        [
                                            'id' => 'select-moneda',
                                            'class' => 'widthAll select-status',
                                        ],
                                    ) !!}
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    {!! Form::labelValidacion('nameTipoCambio', 'Tipo de Cambio', 'negrita') !!}
                                    {!! Form::text(
                                        'nameTipoCambio',
                                        isset($compra) ? floatVal($compra['purchase_typeChange']) : floatVal($parametro->money_change),
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
                                <div class="form-group">
                                    <label for="concepto" class="negrita">Concepto de la Operación<span class='asterisk'>
                                            *</span></label>
                                    <select name="concepto" id="select-moduleConcept" class="widthAll">
                                        <option value="">Selecciona uno...</option>


                                        @foreach ($select_conceptos as $concepto)
                                            <option value="{{ $concepto->moduleConcept_name }}">
                                                {{ $concepto->moduleConcept_name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="proveedor" class="negrita">Proveedor <span class='asterisk'>
                                        *</span></label>
                                <div class="input-group form-group mb15">
                                    <input type="number" class="form-control" id="proveedorKey" name="proveedorKey"
                                        value="{{ isset($compra) ? $compra['purchase_provider'] : null }}" />
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" data-toggle="modal"
                                            data-target=".modal1" id="provedorModal">...</button>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="proveedorName" class="negrita">Nombre/Razón Social del Proveedor:</label>
                                    <input type="text" class="form-control" name="proveedorName" id="proveedorName"
                                        value="{{ isset($compra) ? $nameProveedor['providers_name'] : null }}" readonly>
                                </div>
                            </div>

                            <div class="col-md-12"></div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="proveedorName" class="negrita">Lista proveedor: <span class='asterisk'>
                                            *</span></label>
                                    <select name="listaProveedor" id="select-precioProvee" class="widthAll">
                                        @foreach ($listaPreciosProveedor as $listProvider)
                                            <option value="{{ $listProvider->listProvider_id }}">
                                                {{ $listProvider->listProvider_name }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                            <div class="col-md-3">
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

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="proveedorFechaVencimiento" class="negrita">Fecha vencimiento:</label>
                                    <input type="date" class="form-control input-date"
                                        name="proveedorFechaVencimiento" id="proveedorFechaVencimiento"
                                        value='{{ isset($compra) ? \Carbon\Carbon::parse($compra['purchase_expiration'])->format('Y-m-d') : null }}'>
                                </div>

                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="proveedorReferencia" class="negrita">Notas/Folio Factura:</label>
                                    <input type="text" class="form-control"
                                        value="{{ isset($compra) ? $compra['purchase_reference'] : null }}"
                                        name="proveedorReferencia" id="proveedorReferencia">
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

                            <div class="col-md-12"></div>

                            <div class="col-md-4">
                                <label for="almacen" class="negrita">Almacén donde se ingresará el inventario<span
                                        class='asterisk'>
                                        *</span></label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="almacenKey" name="almacenKey"
                                        value="{{ isset($compra) ? $compra['purchase_depot'] : null }}" />
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" data-toggle="modal"
                                            data-target=".modal2" id="almacenModal">...</button>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6" style="display: none">
                                <label for="almacenTipo" class="negrita">Almacén Tipo<span class='asterisk'>
                                        *</span></label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="almacenTipoKey" name="almacenTipoKey"
                                        value="{{ isset($compra) ? $compra['purchase_depotType'] : null }}" />

                                </div>
                            </div>
                            {{-- <div class="col-md-6" style="display: none">
                                <label for="conceptoGuardado" class="negrita">Concepto<span class='asterisk'>
                                        *</span></label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="conceptoGuardado" name="conceptoGuardado"
                                        value="{{ isset($compra) ? $compra['purchase_concept'] : null }}" />

                                </div>
                            </div> --}}
                            <div class="col-md-12"></div>

                            <div class="col-md-6" style="display: none">
                                <label for="verCosto" class="negrita">Ver costo<span class='asterisk'>
                                        *</span></label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="verCosto" name="verCosto"
                                        value="{{ isset($usuario) ? $usuario['user_viewPurchaseCost'] : null }}" />

                                </div>
                            </div>
                            <div class="col-md-6" style="display: none">
                                <label for="bloquearCosto" class="negrita">Bloquear costo<span class='asterisk'>
                                        *</span></label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="bloquearCosto" name="bloquearCosto"
                                        value="{{ isset($usuario) ? $usuario['user_blockPurchaseCost'] : null }}" />

                                </div>
                            </div>

                            <div id="contenedorTabla">
                                <div class="tablaDesborde">
                                    <div class="tab-content table-panel">
                                        <table class="table table-striped table-bordered compraM widthAll">
                                            <thead>
                                                {{-- ponemos una cabecera que diga "Lista de Articulos" que tenga tenga el colspan de las columnas de la tabla --}}
                                                <tr>
                                                    <th colspan="20" class="text-center">Lista de Productos</th>
                                                </tr>
                                                <tr>
                                                    <th style="display: none">Id</th>
                                                    <th style="display: none;" class="td-aplica">OPERACIÓN ORIGEN</th>
                                                    <th style="display: none;" class="td-consecutivo">Consecutivo
                                                    </th>
                                                    <th>Producto/Item</th>
                                                    <th style="display: none">Referencia-article</th>
                                                    <th>Nombre del Producto</th>
                                                    <th>Cantidad</th>
                                                    <th class="td-costo">Costo Unitario</th>

                                                    <th>Unidad de Compra</th>
                                                    <th>Cantidad que Ingresa</th>
                                                    <th>Importe sin IVA</th>
                                                    <th>% Desc. </th>
                                                    <th>Imp. Desc.</th>
                                                    <th>%IVA</th>
                                                    <th>Importe IVA</th>
                                                    <th>Importe total</th>
                                                    <th class="eliminacion-articulo"></th>

                                                    {{-- botones de afectar --}}
                                                    <th style="display: none;" class="accion-pendiente">
                                                        <p>Pendiente</p>
                                                    </th>
                                                    <th style="display: none;" class="accion-recibir">A recibir
                                                    </th>
                                                    <th style="display: none">Tipo</th>
                                                    <th style="display: none">Decimales</th>
                                                </tr>
                                            </thead>


                                            @if (isset($articulosByCompra) && count($articulosByCompra) > 0)
                                                <tr id="controlArticulo2" style="display: none">
                                                    <td style="display: none"><input type="text" name="dataArticulos"
                                                            id="id-" value="" />
                                                    </td>
                                                    {{-- boton aplica --}}
                                                    <td style="display: none"><input type="text" name="dataArticulos"
                                                            id="id-" value="" />
                                                    </td>
                                                    <td style="display: none"><input type="text" name="dataArticulos"
                                                            id="id-" value="" />
                                                    </td>

                                                    {{-- boton aplica --}}
                                                    <td id="btnInput"><input id="keyArticulo" type="text"
                                                            class="keyArticulo" value=""
                                                            onchange="buscadorArticulos('keyArticulo')">
                                                        <button type="button" class="btn btn-info btn-sm"
                                                            data-toggle="modal" data-target=".modal3">...</button>
                                                    </td>
                                                    <td style="display: none"><input type="text"
                                                            class="botonesArticulos" disabled value="" />
                                                    </td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td class="td-costo"><input id="" type="text"
                                                            class="botonesArticulos" disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
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
                                                        style="display: flex; justify-content: center; align-items: center">

                                                        <i class="fa-regular fa fa-trash-o  btn-delete-articulo"
                                                            aria-hidden="true"
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
                                                    ?>
                                                    @foreach ($articulosByCompra as $key => $detalle)
                                                        <?php
                                                        
                                                        if ($detalle['purchaseDetails_applyIncrement'] != null) {
                                                            $isInfoAplica = true;
                                                        }
                                                        ?>
                                                        <tr id="{{ $detalle['purchaseDetails_article'] . '-' . $key }}">
                                                            <td style="display: none">
                                                                <input type="text" name="dataArticulos"
                                                                    id="id-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    value="{{ $detalle['purchaseDetails_id'] }}"
                                                                    readonly />
                                                            </td>
                                                            {{-- boton aplica --}}
                                                            {{-- @if ($detalle['purchaseDetails_apply'] != null) --}}
                                                            <td style="display: none" class="aplicaA">
                                                                @if ($detalle['purchaseDetails_apply'] != null)
                                                                    <input type="text"
                                                                        name="dataArticulos"
                                                                        id="id-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                        value="{{ $detalle['purchaseDetails_apply'] }} {{ $detalle['purchaseDetails_applyIncrement'] }}"
                                                                        readonly />
                                                                @endif
                                                            </td>
                                                            {{-- <script>
                                                    $('.td-aplica').attr('style', 'display: ');
                                                </script> --}}
                                                            {{-- @endif --}}
                                                            {{-- @if ($detalle['purchaseDetails_applyIncrement'] != null) --}}
                                                            <td style="display: none;" class="aplicaIncre">
                                                                @if ($detalle['purchaseDetails_apply'] != null)
                                                                    <input type="text" name="dataArticulos"
                                                                        id="aplicaIncre-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                        value="{{ $detalle['purchaseDetails_applyIncrement'] }}"
                                                                        readonly/>
                                                                @endif
                                                            </td>
                                                            {{-- <script>
                                                    $('.td-consecutivo').attr('style', 'display: ');
                                                </script> --}}
                                                            {{-- @endif --}}
                                                            {{-- boton aplica --}}

                                                            <td id="btnInput"><input name="dataArticulos[]"
                                                                    id="keyArticulo-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    type="text" class="keyArticulo"
                                                                    value="{{ $detalle['purchaseDetails_article'] }}"
                                                                    title="{{ $detalle['purchaseDetails_article'] }}"
                                                                    onchange="buscadorArticulos('keyArticulo-{{ $detalle['purchaseDetails_article'] . '-' . $key }}')">

                                                                @if (!isset($detalle['purchaseDetails_apply']))
                                                                    <button type="button"
                                                                        class="btn btn-info btn-sm addArticle"
                                                                        data-toggle="modal" data-target=".modal3"
                                                                        id="addArticle">...</button>
                                                                @else
                                                                    @if ($compra['purchase_movement'] !== 'Rechazo de Compra')
                                                                        <button type="button"
                                                                            class="btn btn-info btn-sm addArticle"
                                                                            data-toggle="modal" data-target=".modal3"
                                                                            id="addArticle">...</button>
                                                                    @endif
                                                                @endif


                                                                @if ($compra['purchase_movement'] == 'Entrada por Compra' && $detalle['purchaseDetails_type'] == 'Serie')
                                                                    <button type="button" class="btn btn-warning btn-sm"
                                                                        data-toggle="modal" data-target=".modal6"
                                                                        id="modalSerie">S</button>
                                                                @endif
                                                            </td>
                                                            <td style="display: none">
                                                                <input type="text" name="dataArticulos[]"
                                                                    id="referenceArticle-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    value="{{ $detalle['purchaseDetails_id'] }}" readonly
                                                                    title="{{ $detalle['purchaseDetails_id'] }}">

                                                                />
                                                            </td>
                                                            <td><input name="dataArticulos[]"
                                                                    id="desp-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos"
                                                                    value="{{ $detalle['purchaseDetails_descript'] }}"
                                                                    readonly
                                                                    title="{{ $detalle['purchaseDetails_descript'] }}">
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $cantidadArticulos = floatVal($detalle['purchaseDetails_quantity']);
                                                                $decimales = strlen($cantidadArticulos) - strrpos($cantidadArticulos, '.') - 1;
                                                                
                                                                $cantidadCancelada = floatVal($detalle['purchaseDetails_quantity'] - $detalle['purchaseDetails_canceledAmount']);
                                                                $decimalesCancelada = strlen($cantidadCancelada) - strrpos($cantidadCancelada, '.') - 1;
                                                                ?>
                                                                <input name="dataArticulos[]"
                                                                    id="canti-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    onchange="changeCantidadInventario('{{ $detalle['purchaseDetails_article'] }}', '{{ $key }}')"
                                                                    onfocus="changeCantidadInventario('{{ $detalle['purchaseDetails_article'] }}', '{{ $key }}')"
                                                                    value="{{ $detalle['purchaseDetails_canceledAmount'] == null ? number_format($cantidadArticulos, $decimales) : number_format($cantidadCancelada, $decimalesCancelada) }}">
                                                            </td>
                                                            <td class="td-costo"><input name="dataArticulos[]"
                                                                    id="c_unitario-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    value="{{ number_format(floatVal($detalle['purchaseDetails_unitCost']), 4) }}"
                                                                    onchange="calcularImporte('{{ $detalle['purchaseDetails_article'] }}', '{{ $key }}')">
                                                            </td>


                                                            <td>
                                                                <select name="dataArticulos[]"
                                                                    id="unid-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    class="botonesArticulos"
                                                                    value="{{ $detalle['purchaseDetails_unit'] }}"
                                                                    onchange="recalcularCantidadInventario('{{ $detalle['purchaseDetails_article'] }}', '{{ $key }}')">
                                                                </select>
                                                            </td>
                                                            <td><input name="dataArticulos[]"
                                                                    id="c_Inventario-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    value="{{ $detalle['purchaseDetails_canceledAmount'] == null ? floatVal($detalle['purchaseDetails_inventoryAmount']) : floatVal($detalle['purchaseDetails_inventoryAmount'] - $detalle['purchaseDetails_canceledAmount'] * $detalle['purchaseDetails_factor']) }}"
                                                                    readonly></td>
                                                            <td><input name="dataArticulos[]"
                                                                    id="importe-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    value="{{ number_format(floatVal($detalle['purchaseDetails_amount']), 2) }}"
                                                                    readonly></td>
                                                            <td><input name="dataArticulos[]"
                                                                    id="porDesc-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    value="{{ number_format(floatVal($detalle['purchaseDetails_discountPorcent']), 2) }}"
                                                                    onchange="descuentoLineal('{{ $detalle['purchaseDetails_article'] }}', '{{ $key }}')">
                                                            </td>
                                                            <td><input name="dataArticulos[]"
                                                                    id="descuento-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    value="{{ number_format(floatVal($detalle['purchaseDetails_discount']), 2) }}"
                                                                    readonly></td>
                                                            <td><input name="dataArticulos[]"
                                                                    id="iva-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    type="number" class="botonesArticulos sinBotones"
                                                                    value="{{ floatVal($detalle['purchaseDetails_ivaPorcent']) }}"
                                                                    readonly>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $porcentajeIva = (float) $detalle['purchaseDetails_ivaPorcent'] / 100;
                                                                $importeIvaView = $detalle['purchaseDetails_amount'] * $porcentajeIva;
                                                                ?>
                                                                <input name="dataArticulos[]"
                                                                    id="importe_iva-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    value="{{ number_format(floatVal($importeIvaView), 2) }}"
                                                                    readonly>
                                                            </td>

                                                            </td>
                                                            <td><input name="dataArticulos[]"
                                                                    id="importe_total-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos sinBotones"
                                                                    value="{{ number_format(floatVal($detalle['purchaseDetails_total']), 2) }}"
                                                                    readonly></td>

                                                            <td style="display: flex; justify-content: center; align-items: center"
                                                                class="eliminacion-articulo">
                                                                <i class="fa-regular fa fa-trash-o"
                                                                    onclick="eliminarArticulo('{{ $detalle['purchaseDetails_article'] }}', '{{ $key }}')"
                                                                    aria-hidden="true"
                                                                    style="color: red; font-size: 25px; cursor: pointer;"></i>
                                                            </td>
                                                            @if ($compra['purchase_status'] !== 'FINALIZADO')
                                                                <?php
                                                                $pendienteArticulo = floatval($detalle['purchaseDetails_outstandingAmount']);
                                                                
                                                                if ($pendienteArticulo < 0) {
                                                                    $pendienteArticulo = 0;
                                                                }
                                                                ?>
                                                                {{-- botones de afectar --}}
                                                                <td class="accion-pendiente">
                                                                    <input
                                                                        id="montoPendiente-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                        type="text"
                                                                        class="botonesArticulos botonPendiente"
                                                                        value="{{ $pendienteArticulo }}" readonly>
                                                                </td>
                                                                <td class="accion-recibir">
                                                                    <input
                                                                        id="montoRecibir-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                        type="text"
                                                                        class="botonesArticulos botonRecibir"
                                                                        {{ $detalle['purchaseDetails_outstandingAmount'] == null ? 'readonly' : '' }}
                                                                        onchange="validarInput('{{ $detalle['purchaseDetails_article'] }}', '{{ $key }}')">
                                                                </td>
                                                                <script>
                                                                    $('.accion-pendiente').attr('style', 'width: 100px !important');
                                                                    $('.accion-recibir').attr('style', 'width: 100px !important');
                                                                </script>
                                                            @endif

                                                            <td style="display: none">
                                                                <input
                                                                    id="tipoArticulo-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos"
                                                                    value="{{ $detalle['purchaseDetails_type'] }}"
                                                                    readonly>
                                                            </td>

                                                            <td style="display: none">
                                                                <input
                                                                    id="decimales-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"
                                                                    type="text" value="" readonly>
                                                            </td>

                                                        </tr>
                                                        <script>
                                                            $.ajax({
                                                                url: "/logistica/compras/api/getMultiUnidad",
                                                                type: "GET",
                                                                data: {
                                                                    factorUnidad: "{{ $detalle['purchaseDetails_article'] }}",
                                                                },
                                                                success: function(data) {
                                                                    let unidadPorDefecto = "{{ $detalle['purchaseDetails_unit'] }}";
                                                                    let unidadPorDefectoIndex = {};
                                                                    data.forEach((element) => {
                                                                        if (element.articlesUnits_unit == unidadPorDefecto) {
                                                                            unidadPorDefectoIndex = element;
                                                                        }

                                                                        $("#unid-" + '{{ $detalle['purchaseDetails_article'] . '-' . $key }}').append(`
                                                        <option value="${element.articlesUnits_unit}-${element.articlesUnits_factor}">${element.articlesUnits_unit}-${element.articlesUnits_factor}</option>
                                                        `);

                                                                        $('input[id="canti-{{ $detalle['purchaseDetails_article'] . '-' . $key }}"]')
                                                                            .focus();



                                                                    });

                                                                    if (Object.keys(unidadPorDefectoIndex).length > 0) {
                                                                        $("#unid-" + '{{ $detalle['purchaseDetails_article'] . '-' . $key }}').val(
                                                                            unidadPorDefecto +
                                                                            "-" +
                                                                            unidadPorDefectoIndex.articlesUnits_factor
                                                                        );

                                                                    }

                                                                    $("#unid-" + '{{ $detalle['purchaseDetails_article'] . '-' . $key }}').change();
                                                                    $('#c_unitario-' + '{{ $detalle['purchaseDetails_article'] . '-' . $key }}').change();
                                                                },
                                                            });
                                                        </script>
                                                    @endforeach

                                                    <script>
                                                        const isAplica = '{{ $isInfoAplica }}';
                                                        if (isAplica) {
                                                            $('.td-aplica').attr('style', 'width: 10px');
                                                            $('.td-consecutivo').hide();
                                                            $('.aplicaA').show();
                                                            $('.aplicaIncre').hide();
                                                            console.log(isAplica);

                                                        }
                                                    </script>


                                                </tbody>
                                            @else
                                                <tbody id="articleItem">
                                                    <tr id="controlArticulo">
                                                        <td style="display: none"><input type="text"
                                                                name="dataArticulos" id="id-" value="" />
                                                        </td>
                                                        {{-- boton aplica --}}
                                                        <td style="display: none"><input type="text"
                                                                name="dataArticulos" id="id-" value="" />
                                                        </td>
                                                        <td style="display: none"><input type="text"
                                                                name="dataArticulos" id="id-" value="" />
                                                        </td>
                                                        {{-- boton aplica --}}
                                                        <td id="btnInput"><input id="keyArticulo" type="text"
                                                                class="keyArticulo"
                                                                onchange="buscadorArticulos('keyArticulo')">
                                                            <button type="button" class="btn btn-info btn-sm"
                                                                data-toggle="modal" data-target=".modal3"
                                                                id="addArticle">...</button>
                                                        </td>
                                                        <td style="display: none"><input type="text"
                                                                name="dataArticulos" id="id-" value="" />
                                                        </td>
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td class="td-costo"><input id="" type="text"
                                                                class="botonesArticulos" disabled></td>
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
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
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td><input id="" type="text" class="botonesArticulos"
                                                                disabled></td>
                                                        <td
                                                            style="display: flex; justify-content: center; align-items: center">

                                                            <i class="fa-regular fa fa-trash-o  btn-delete-articulo"
                                                                aria-hidden="true"
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
                                        <span class="input-group-addon">Total</span>
                                        <input type="text" class="form-control" id="totalCompleto"
                                            name="totalCompleto" readonly>
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
                                        <span class="input-group-addon"> Sub-Total</span>
                                        <input type="text" class="form-control" id="subTotalCompleto"
                                            name="subTotalCompleto" readonly>
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
                                    <div class="input-group input-group-sm mt5" style="margin-left: -5px">
                                        @if (isset($compra) ? $compra['purchase_status'] === 'INICIAL' : 'INICIAL')
                                            <span class="label label-default" id="status">INICIAL</span>
                                        @elseif($compra['purchase_status'] === 'POR AUTORIZAR')
                                            <span class="label label-warning" id="status">POR AUTORIZAR</span>
                                        @elseif($compra['purchase_status'] === 'FINALIZADO')
                                            <span class="label label-success" id="status">FINALIZADO</span>
                                        @elseif($compra['purchase_status'] === 'CANCELADO')
                                            <span class="label label-danger" id="status">CANCELADO</span>
                                        @endif

                                    </div>
                                </div>



                            </div>
                        </div>
                    </div>



                    {{-- <div class="tab-pane active" id="tab2-2">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="folioTicket" class="negrita">Folio ticket:</label>
                                <input type="text" class="form-control" name="folioTicket" id="folioTicket"
                                    value="{{ isset($compra) ? $compra['purchase_ticket'] : '' }}">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="operador" class="negrita">Operador:</label>
                                <input type="text" class="form-control" name="operador" id="operador"
                                    value="{{ isset($compra) ? $compra['purchase_operator'] : '' }}">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="placas" class="negrita">Placas:</label>
                                <input type="text" class="form-control" name="placas" id="placas"
                                    value="{{ isset($compra) ? $compra['purchase_plates'] : '' }}">
                            </div>
                        </div>

                        <div class="col-md-12"></div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="material" class="negrita">Material:</label>
                                <input type="text" class="form-control" name="material" id="material"
                                    value="{{ isset($compra) ? $compra['purchase_material'] : '' }}">
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pesoEntrada" class="negrita">Peso entrada:</label>
                                <input type="number" class="form-control" name="pesoEntrada" id="pesoEntrada"
                                    step="0" value="{{ isset($compra) ? $compra['purchase_inputWeight'] : '' }}">
                            </div>
                        </div>


                        <div class="col-md-6">
                            {!! Form::label('fechaEntrada', 'Fecha y Hora', ['class' => 'negrita']) !!}
                            <input type="datetime-local" name="fechaHoraEntrada" class="form-control"
                                placeholder="mm/dd/yyyy" style="line-height: 17px"
                                value="{{ isset($compra) ? $compra['purchase_inputDateTime'] : '' }}"
                                id="fechaEntradaDatos">
                        </div>


                        <div class="col-md-12">

                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pesoSalida" class="negrita">Peso Salida:</label>
                                <input type="number" class="form-control" name="pesoSalida" id="pesoSalida"
                                    step="0" value="{{ isset($compra) ? $compra['purchase_outputWeight'] : '' }}">
                            </div>
                        </div>


                        <div class="col-md-6">
                            {!! Form::label('fechaEntrada', 'Fecha y Hora', ['class' => 'negrita']) !!}
                            <input type="datetime-local" name="fechaHoraSalida" class="form-control"
                                placeholder="mm/dd/yyyy" style="line-height: 17px"
                                value="{{ isset($compra) ? $compra['purchase_outputDateTime'] : '' }}" id="fechaSalida">
                        </div>

                        <div class="col-md-12 ">
                            <div class="input-group input-group-sm mt5" style="margin-bottom: 10px;">
                                <span class="label label-default" id="status">
                                    {{ isset($compra) ? $compra['purchase_status'] : 'INICIAL' }}</span>
                            </div>
                        </div>
                        <div class="col-md-12">

                        </div>




                    </div> --}}
                    <ul class="list-unstyled wizard" id="botonesWizard">
                        {{-- <li class="pull-left previous"><button type="button" class="btn btn-default">Anterior</button>
                        </li> --}}
                        <li class="pull-right next"><button type="button" class="btn btn-primary"
                                id="next">Siguiente</button>
                        </li>
                    </ul>

                    {{-- Input donde asignamos los nuevos valos de los articulos para manipularlo facilmente --}}
                    <input type="text" id="inputDataArticles" readonly hidden />
                    {{-- Añadimos los id que eliminamos de los articulos --}}
                    <input type="text" id="inputDataArticlesDelete" name="dataArticulosDelete" readonly hidden />
                    <input type="text" id="inputDataSaveArticles"
                        value="{{ isset($compra) ? $compra->purchase_jsonData : '' }}" name="inputDataSaveArticles"
                        readonly hidden />
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
                        <h5 class="modal-title" id="exampleModalLongTitle">Lista de proveedores</h5>

                    </div>
                    <div class="modal-body">
                        <div class="panel table-panel">
                            <table id="shTable2" class="table table-striped table-bordered widthAll">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Nombre</th>
                                        <th>RFC</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($proveedores as $proveedor)
                                        <tr>
                                            <td>{{ $proveedor->providers_key }}</td>
                                            <td>{{ $proveedor->providers_name }}</td>
                                            <td>{{ $proveedor->providers_RFC }}</td>
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
                                        <th style="display: none">Tipo</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($almacenes as $almacen)
                                        <tr>
                                            <td>{{ $almacen->depots_key }}</td>
                                            <td>{{ $almacen->depots_name }}</td>
                                            <td style="display: none">{{ $almacen->depots_type }}</td>
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
                            <div class="form-group">
                                <label for="todosLosArticulos"><span>Todos los articulos</span></label>
                                <input type="checkbox" name="all" id="todosLosArt" />
                            </div>
                        </div>
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
                                        <th colspan="5"  class="text-center">
                                            <h5 class="modal-title text-center" id="exampleModalLongTitle">Lista de Artículos</h5>
                                        </th>
                                    </tr>
                                        <th>Clave</th>
                                        <th>Nombre</th>
                                        <th style="display: none">Iva</th>
                                        <th style="display: none">Unidad</th>
                                        <th style="display: none">Tipo</th>

                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($articulos as $articulo)
                                        <tr>
                                            <td>{{ $articulo->articles_key }}</td>
                                            <td>{{ $articulo->articles_descript }}</td>
                                            <td style="display: none">{{ $articulo->articles_porcentIva }}</td>
                                            <td style="display: none">{{ $unidad[$articulo->articles_unitBuy] }}</td>
                                            <td style="display: none">{{ $articulo->articles_type }}</td>
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

        <!-- Modal para agregar el serie al articulo correspondiente  -->
        <div class="modal fade bd-example-modal-lg modal6" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="modalSerie">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle"> Serie del artículo: <strong
                                class="articuloKeySerie"></strong></h5>

                    </div>
                    <div class="modal-body">
                        <input style="display: none" type="hidden" value="" id="clavePosicion" />
                        <div class="row" id="form-articulos-serie">

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="generarLotesSeries">
                            Generar Lote de Series
                        </button>
                        <button type="button" class="btn btn-success" id="modal6Agregar">Agregar</button>
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
                        <h5 class="modal-title text-center" id="exampleModalCenterTitle">Elige tu Proceso Siguiente</h5>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="generarEntradaCompra" name="accionCompra"
                                            value="Generar Entrada por Compra" checked>
                                        <label>Genera la Entrada por Compra (Parcial o Total)</label>

                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="generarCompraRechazada" name="accionCompra"
                                            value="Generar Rechazo de Compra">
                                        <label>Genera el Rechazo de Compra (Parcial o Total)</label>
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
                            Avanza tu proceso por la cantidad:
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
                        <h5 class="modal-title text-center" id="exampleModalCenterTitle">Cancelar Orden de Compra</h5>
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

                                {{-- <div class="col-md-12">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="cantidadIndicadaCancelar" name="accionCancelar"
                                            value="Generar Entrada por Compra">
                                        <label>Cantidad Indicada</label>

                                    </div>
                                </div> --}}

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
        <div class="modal fade" id="ModalFlujo" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="modalFlujoClose">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <div class="lds-roller" id="loadingFlujo" style="display: none">
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
                        <h5 class="modal-title" id="exampleModalLongTitle">Posición del Movimiento</h5>
                    </div>
                    <div class="modal-body contenedor-flujo">
                        <input type="hidden" id="movimientoFlujo"
                            value="{{ isset($compra) ? (isset($primerFlujoDeCompra) ? $primerFlujoDeCompra : '') : '' }}" />
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
                        <button type='button' class='btn btn-danger closeModalFlujo' data-dismiss='modal'>Cerrar <span
                                class="glyphicon glyphicon-log-out"></span></button>
                        <button type="button" class="btn btn-secondary optionFlujo" id="anterior-Flujo"><span
                                class="glyphicon glyphicon-arrow-left"></span> Anterior </button>
                        <button type="button" class="btn btn-primary optionFlujo" id="siguiente-Flujo">Siguiente
                            <span class="glyphicon glyphicon-arrow-right"></span></button>



                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg modal8" tabindex="-1" role="dialog"
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
                        <!-- Nav tabs -->
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
                            <li id="li4" class=""><a href="#siguiente"
                                    data-toggle="tab"><strong>Especificaciones</strong></a>
                            </li>

                        </ul>
                    </div>
                    <div>
                        <div>
                            <div class="col-sm-8 col-md-12">
                                <!-- Tab panes -->
                                <div class="tab-content nopadding noborder" style="margin-top: 15px;">
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

                                            <?php
                                            
                                            if (isset($infoArticulo)) {
                                                $inventario = floatVal($infoArticulo->articlesInv_inventory);
                                                $decimales = strlen($inventario) - strrpos($inventario, '.') - 1;
                                            }
                                            
                                            ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="disponibleCostoPromedio"
                                                        class="negrita">Disponible:</label>
                                                    <input type="text" readonly class="form-control input-sm"
                                                        value="{{ isset($infoArticulo) ? number_format($inventario, $decimales) : '' }}"
                                                        name="disponibleCostoPromedio" id="disponibleCostoPromedio">
                                                </div>
                                            </div>


                                            <div class="col-md-4 pull-right">
                                                <div class="form-group">
                                                    <label for="existenciaCostoPromedio"
                                                        class="negrita">Existencia:</label>
                                                    <input type="text" readonly class="form-control input-sm"
                                                        value="{{ isset($infoArticulo) ? number_format($inventario, $decimales) : '' }}"
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
                                                                        {{ number_format($inventario2, $decimales2) }}
                                                                    </td>
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
                                                        value="{{ isset($infoArticulo) ? $infoArticulo->units_unit : '' }}"
                                                        name="unidadCostoPromedio" id="unidadCostoPromedio">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="categoriaCostoPromedio"
                                                        class="negrita">Categoría</label>
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

                                                <div class="form-group">
                                                    <label class="col-sm-4 control-label"><strong>Costo Proveedor:</strong> </label>
                                                    <div class="col-sm-8">
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($listaProveedor) ? '$' . floatVal($listaProveedor->articlesList_lastCost) : '' }}"
                                                            id="costoProveedor">
                                                    </div>
                                                </div>

                                            </div>
                                        </div><!-- activity-list -->
                                    </div><!-- tab-pane -->

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
                        </div><!-- panel -->
                    </div>

                    <div class="modal-footer">
                        <!--follower-list -->
                        <button aria-hidden="true" class="btn btn-white btn-block" data-dismiss="modal"
                            id="cerrarModalArticulo">
                            Cerrar</button>
                    </div>
                </div>
            </div>
        </div>


        {{-- division --}}

        <div class="modal fade bd-example-modal-lg modal9" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="informacionProveedorModal" style="display: ">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <div class="lds-roller" id="loadingProveedor" style="display: none">
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
                        <h5 class="modal-title" id="exampleModalLongTitle">Información Proveedor</h5>
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs nav-line" style="margin: 0px !important">
                            {{-- <li class="active"><a href="#activities2" data-toggle="tab"><strong></strong></a></li> --}}
                            <li class="active"><a href="#following2" data-toggle="tab"><strong>Datos Generales</strong></a></li>
                            <li class=""><a href="#followers2" data-toggle="tab"><strong>Pendientes por Pagar</strong></a></li>
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
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->providers_name : '' }}"
                                                            name="infNombreProveedor" id="infNombreProveedor">
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio"
                                                            class="negrita">Dirección:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->providers_address : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio"
                                                            class="negrita">Telefono:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->providers_phone1 : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio" class="negrita">RFC:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->providers_RFC : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio"
                                                            class="negrita">Categoria:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->providers_category : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio"
                                                            class="negrita">Grupo:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->providers_group : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio" class="negrita">Tipo:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->providers_type : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>


                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio"
                                                            class="negrita">Condición:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->creditConditions_name : '' }}"
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

                                            <div class="col-md-6 scrollInfoProveedor">

                                                <div class="col-md-5">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio" class="negrita">Lista de Precios:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->listaProveedores->listProvider_name ?? '' : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>
                                                <div class="col-md-7">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio" class="negrita">Forma de Pago:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->formaPago->formsPayment_name ?? '' : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio" class="negrita">Regimen Fiscal:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) && $infoProveedor->regimenFiscal ? $infoProveedor->regimenFiscal->descripcion : '' }} 
                                                                   {{ isset($infoProveedor->providers_taxRegime) ? ' - ' . $infoProveedor->providers_taxRegime : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>



                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio"
                                                            class="negrita">Teléfono 1:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->providers_phone2 : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="articuloCostoPromedio"
                                                            class="negrita">Correo Eléctronico 1:</label>
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoProveedor) ? $infoProveedor->providers_mail1 : '' }}"
                                                            name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                    </div>
                                                </div>
                                                {{-- Ahora vmoas a hacer un apartado para mostrar la última fecha de pago del proveedor --}}
                                                <div class="col-md-12">
                                                    <table class="table table-hover mb30">
                                                        <thead>
                                                            <tr>
                                                                <th colspan="4">Última Fecha de Pago</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if (isset($ultimoPago))
                                                                        <tr>
                                                                            {{-- ponemos en la primera columna la fecha de pago de accountsPayable_issuedate --}}
                                                                            <td>{{ \Carbon\Carbon::parse($ultimoPago->accountsPayable_issuedate)->format('d/m/Y') }}
                                                                            {{-- <td></td> --}}
                                                                            <td>{{ $ultimoPago->accountsPayable_movement . ' ' . $ultimoPago->accountsPayable_movementID }}
                                                                            </td>
                                                                            </td>
                                                                            <td>Por: ${{ number_format($ultimoPago->accountsPayable_total, 2) }}
                                                                            </td>
                                                                            {{-- ponemos un botón de ver con un href que nos lleve al movimiento --}}
                                                                            {{-- <td><a href="{{ route('verMovimiento', ['id' => $ultimoPago->accountsPayable_id]) }}" class="btn btn-info btn-sm">Ver</a></td> --}}
                                                                            <td>
                                                                                <a style="color: red" href="{{ route('vista.modulo.cuentasPagar.create-cxp', ['id' => $ultimoPago->accountsPayable_id]) }}">Ver</a>
                                                                            </td>

                                                                        </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="col-md-12">
                                                    <table class="table table-hover mb30">
                                                        <thead>
                                                            <tr>
                                                                <th colspan="2">Top 5 Productos más Comprados</th>
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
                                                                                <td>{{ $item->purchaseDetails_descript }}</td>
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
                                            <div class="col-md-12 scrollInfoProveedor">
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
                                                                                    @if ($movimiento->accountsPayable_money == trim($moneda->money_key))
                                                                                        <tr>
                                                                                            <td>{{ $movimiento->accountsPayable_movement . ' ' . $movimiento->accountsPayable_movementID }}
                                                                                            </td>
                                                                                            </td>
                                                                                            <td>{{ $movimiento->accountsPayable_reference }}
                                                                                            </td>
                                                                                            <td>{{ $movimiento->accountsPayable_moratoriumDays }}
                                                                                            </td>
                                                                                            <td>{{ '$' . number_format($movimiento->accountsPayable_balance, 2) }}
                                                                                            </td>
                                                                                            <td>{{ \Carbon\Carbon::parse($movimiento->accountsPayable_issuedate)->format('d/m/Y') }}
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
        @include('include.mensaje')

        <script>
            let calculoImpuestos = '{{ session('company')->companies_calculateTaxes }}';


            let contadorArticulosString = '{{ isset($articulosByCompra) ? count($articulosByCompra) : 0 }}';
            let contadorArticulos = parseInt(contadorArticulosString);
            jQuery('#cantidadArticulos').val(contadorArticulos);
            let monedaDefecto = "{{ isset($parametro) ? $parametro['generalParameters_defaultMoney'] : PESOS }}";

            if (jQuery("#select-movimiento").val() != "Rechazo de Compra") {
                jQuery(".motivoCancelacionDiv").hide();
            } else {
                jQuery(".motivoCancelacionDiv").show();

            }
        </script>
        <script src="{{ asset('js/PROCESOS/compras.js') }}"></script>
        <script>
            $('#select-moduleConcept').val('{{ isset($compra) ? $compra['purchase_concept'] : '' }}').trigger(
                'change.select2');

                
            $('#select-precioProvee').val('{{ isset($compra) ? $compra['purchase_listPriceProvider'] : null }}').trigger(
                'change.select2');

            $('#select-proveedorCondicionPago').val('{{ isset($compra) ? $compra['purchase_condition'] : '' }}').trigger(
                'change.select2').change();


            jQuery("#select-movimiento").on("change", function() {
                console.log("cambio");
            });



            $('#select-moneda').val(
                    '{{ isset($compra) ? trim($compra['purchase_money']) : $parametro->generalParameters_defaultMoney }}')
                .trigger(
                    'change.select2');

            $('#select-moduleCancellation').val('{{ isset($compra) ? $compra['purchase_reasonCancellation'] : '' }}').trigger(
                'change.select2');
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
