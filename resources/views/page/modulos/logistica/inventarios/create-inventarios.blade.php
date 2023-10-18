@extends('layouts.layout')
@section('content')
    @foreach (auth()->user()->getAllPermissions()->where('categoria', '=', 'Inventarios')->pluck('name')->toArray() as $permisos)
        <?php
        $mov = str_replace(' ', '', substr($permisos, 0, -2));
        $letra = substr($permisos, -1);
        // dd($inventario);
        ?>
        @if ($letra === 'E')
            <input type="hidden" value="true" id="{{ $mov }}">
        @endif
    @endforeach
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open([
                    'route' => ['modulo.inventarios.store-inventario'],
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
                    <li><a href="#tab1-2" data-toggle="tab"><strong>Datos generales - </strong> Movimiento</a></li>
                </ul>

                <div class="progress progress-xs">
                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="45" aria-valuemin="0"
                        aria-valuemax="100"></div>
                </div>



                <div class="tab-content">
                    <div class="col-md-4 observaciones">
                        <p class="titulo text-left">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                    </div>
                    <div class="col-md-6"></div>
                    <div class="col-md-2 btn-action">
                        <button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown">
                            Menú de opciones <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu" id="Opciones">

                            @can('Afectar')
                                <li><a href="#" id="afectar-boton"> Afectar <span
                                            class="glyphicon glyphicon-play pull-right"></span></a></li>
                            @endcan


                            <li><a href="#" id="eliminar-boton">Eliminar <span
                                        class="glyphicon glyphicon-remove-circle pull-right"></span></a></li>

                            @can('Cancelar')
                            @if(isset($inventario) && $inventario->inventories_status !== 'POR AUTORIZAR' && $inventario->inventories_movement !== 'Tránsito')
                                <li><a href="#" id="cancelar-boton">Cancelar <span
                                            class="glyphicon glyphicon-trash pull-right"></span></a></li>
                                @endif
                            @endcan

                            @if (isset($inventario) && $inventario->inventories_status !== 'INICIAL')
                                <li><a href="{{ route('vista.modulo.inventario.reportes', ['idInventario' => $inventario['inventories_id']]) }}"
                                        target="_blank">Reporte <span
                                            class="glyphicon glyphicon-list-alt pull-right"></span></a>
                                </li>
                            @endif

                            @if(isset($inventario) && $inventario->inventories_status !== 'POR AUTORIZAR' && $inventario->inventories_movement !== 'Tránsito')
                                <li>
                                    <a href="#" id="copiar-compra">Copiar <span
                                            class="fa fa-copy pull-right"></span></a>
                                </li>
                            @endif

                            @if (isset($inventario))
                                <li class="divider"></li>
                                <li><a href="{{ route('vista.modulo.inventarios.create-inventario') }}"
                                        id="nuevo-boton">Nuevo<span class="fa fa-file-o pull-right"></span></a></li>
                                <li><a href="{{ route('vista.modulo.inventarios.anexos', ['id' => $inventario->inventories_id]) }}"
                                        id="anexos-boton">Anexos <span
                                            class="glyphicon glyphicon-paperclip pull-right"></span></a></li>
                                <li><a href="" data-toggle="modal" data-target="#ModalFlujo">
                                        Ver flujo
                                        <span class="glyphicon glyphicon-transfer pull-right"></span>
                                    </a></li>

              
                                    <li><a href="#" data-toggle="modal" data-target="#costoPromedioModal"
                                            id="costoPromedio">
                                            Inf. Articulo
                                            <span class="fa fa-tag pull-right"></span></a>
                                    </li>
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
                                    {!! Form::select('movimientos', $movimientos, isset($inventario) ? $inventario['inventories_movement'] : null, [
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
                                        value="{{ isset($inventario) ? $inventario['inventories_id'] : 0 }}"readonly>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="folio" class="negrita">Folio:</label>
                                    <input type="number" class="form-control" name="folio" id="folioCompra"
                                        value="{{ isset($inventario) ? $inventario['inventories_movementID'] : null }}"readonly>
                                </div>
                            </div>

                               <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fechaEmision" class="negrita">Fecha</label>
                                    <input type="date" class="form-control input-date" name="fechaEmision"
                                        id="fechaEmision" placeholder="Fecha Emisión"
                                        value="{{ isset($inventario) ? \Carbon\Carbon::parse($inventario['inventories_issuedate'])->format('Y-m-d') : $fecha_actual }}"
                                        >
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    {!! Form::labelValidacion('nameMoneda', 'Moneda', 'negrita') !!}
                                    {!! Form::select(
                                        'nameMoneda',
                                        $selectMonedas,
                                        isset($inventario) ? trim($inventario['inventories_money']) : $parametro->generalParameters_defaultMoney,
                                        [
                                            'id' => 'select-moneda',
                                            'readonly' => true,
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
                                        isset($inventario) ? number_format($inventario['inventories_typeChange'], 2) : floatVal($parametro->money_change),
                                        [
                                            'class' => 'form-control',
                                            'readonly',
                                            'id' => 'nameTipoCambio',
                                        ],
                                    ) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>



                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="concepto" class="negrita">Concepto de la Operación<span class='asterisk'>
                                            *</span></label>
                                    <select name="concepto" id="select-moduleConcept" class="widthAll">
                                        <option value="" data-todos="todos">Selecciona uno...</option>


                                        @foreach ($select_conceptos as $concepto)
                                            <option value="{{ $concepto->moduleConcept_name }}">
                                                {{ $concepto->moduleConcept_name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="proveedorReferencia" class="negrita">Notas:</label>
                                    <input type="text" class="form-control"
                                        value="{{ isset($inventario) ? $inventario['inventories_reference'] : null }}"
                                        name="proveedorReferencia" id="proveedorReferencia">
                                </div>
                            </div>

                            <div class="col-md-12"></div>

                            <div class="col-md-4">
                                <label for="almacen" class="negrita">Almacén del Inventario<span class='asterisk'>
                                        *</span></label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="almacenKey" name="almacenKey"
                                        value="{{ isset($inventario) ? $inventario['inventories_depot'] : null }}" />
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" data-toggle="modal"
                                            data-target=".modal2" id="Destino">...</button>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6" style="display: none">
                                <label for="almacenTipo" class="negrita">Almacén Tipo<span class='asterisk'>
                                        *</span></label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="almacenTipoKey" name="almacenTipoKey"
                                        value="{{ isset($inventario) ? $inventario['inventories_depotType'] : null }}" />
                        
                                </div>
                            </div>

                            <div class="col-md-4 almacenDestinoDiv" style="display: none">
                                <label for="almacenDestino" id="almacenDestino" class="negrita">Almacén Destino<span class='asterisk'>
                                        *</span></label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="almacenDestinoKey"
                                        name="almacenDestinoKey"
                                        value="{{ isset($inventario) ? $inventario['inventories_depotDestiny'] : null }}" />
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" data-toggle="modal"
                                            data-target=".modal9" id="almacenDestinoModal">...</button>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6" style="display: none">
                                <label for="almacenTipoDestino" class="negrita">Almacén Destino Tipo<span class='asterisk'>
                                        *</span></label>
                                <div class="input-group form-group mb15">
                                    <input type="text" class="form-control" id="almacenTipoDestinoKey" name="almacenTipoDestinoKey"
                                        value="{{ isset($inventario) ? $inventario['inventories_depotDestinyType'] : null }}" />
                        
                                </div>
                            </div>




                            <div class="col-md-12"></div>



                            <div class="col-md-12"></div>

                                <div id="contenedorTabla">
                                <div class="tablaDesborde">

                                <div class="tab-content table-panel">
                                    <table class="table table-striped table-bordered inventarioM widthAll">
                                        @if (isset($articulosByInventario) && count($articulosByInventario) > 0)
                                            <thead>
                                                <tr>
                                                    <th style="display: none">Id</th>
                                                    <th style="display: none; width: 50px;" class="td-aplica">Aplica</th>
                                                    <th style="display: none" class="td-consecutivo">Consecutivo
                                                    </th>
                                                    <th>Producto/Item</th>
                                                    <th style="display: none">Referencia-article</th>
                                                    <th>Nombre del Producto</th>
                                                    <th >Cantidad</th>
                                                    <th class="costoArticulo">Costo Unitario</th>
                                                    <th>Unidad de Venta</th>
                                                    <th>Cantidad del Inv.</th>
                                                    <th class="totalArticulo tablaInventario">Importe total</th> {{-- aquí --}}
                                                    <th class="eliminacion-articulo"></th>

                                                    {{-- botones de afectar --}}
                                                    <th style="display: none" class="accion-pendiente">
                                                        <p>Pendiente</p>
                                                    </th>
                                                    <th style="display: none" class="accion-recibir">A recibir</th>
                                                    <th style="display: none">Tipo</th>
                                                    <th style="display: none">Decimales</th>
                                                </tr>
                                            </thead>
                                            <tr id="controlArticulo2" style="display: none">
                                                <td style="display: none"><input type="text" name="dataArticulos"
                                                        id="id-" value="" />
                                                </td>
                                                {{-- boton aplica --}}
                                                <td style="display: none; width: 10px"><input type="text"
                                                        name="dataArticulos" id="id-" value="" />
                                                </td>
                                                <td style="display: none; width: 10px"><input type="text"
                                                        name="dataArticulos" id="id-" value="" />
                                                </td>

                                                {{-- boton aplica --}}
                                                <td id="btnInput"><input id="keyArticulo" type="text" class="keyArticulo"
                                                        onchange="buscadorArticulos('keyArticulo')">
                                                    <button type="button" class="btn btn-info btn-sm"
                                                        data-toggle="modal" data-target=".modal3">...</button>
                                                </td>
                                                <td style="display: none"><input type="text" class="botonesArticulos"
                                                        disabled value="" />
                                                </td>
                                                <td><input id="" type="text"
                                                        class="botonesArticulos" disabled></td>
                                                <td><input id="" type="text"
                                                        class="botonesArticulos" disabled></td>
                                                <td class="costoArticulo"><input id="" type="text"
                                                        class="botonesArticulos" disabled></td>
                                                <td style><input id="" type="text" class="botonesArticulos"
                                                        disabled></td>
                                                <td>
                                                    <input id="" type="text" class="botonesArticulos"
                                                        disabled>
                                                </td>

                                                <td class="totalArticulo"><input id="" type="text"
                                                        class="botonesArticulos" disabled></td>
                                                <td style="display: flex; justify-content: center; align-items: center">

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

                                            </tr>
                                            <tbody id="articleItem">
                                                <?php
                                                $isInfoAplica = false; //Cambiara a true cuando una entrada venga de una orden de compra
                                                ?>
                                                @foreach ($articulosByInventario as $key => $detalle)
                                                    <?php
                                                    
                                                    if ($detalle['inventoryDetails_applyIncrement'] != null) {
                                                        $isInfoAplica = true;
                                                    }
                                                    ?>
                                                    <tr id="{{ $detalle['inventoryDetails_article'] . '-' . $key }}">
                                                        <td style="display: none">
                                                            <input type="text" name="dataArticulos"
                                                                id="id-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"
                                                                value="{{ $detalle['inventoryDetails_id'] }}" readonly />
                                                        </td>
                                                        {{-- boton aplica --}}
                                                        {{-- @if ($detalle['inventoryDetails_apply'] != null) --}}
                                                        <td style="display: none;" class="aplicaA">
                                                            @if ($detalle['inventoryDetails_apply'] != null)
                                                                <input type="text"
                                                                    name="dataArticulos"
                                                                    id="id-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"
                                                                    value="{{ $detalle['inventoryDetails_apply'] }} - {{ $detalle['inventoryDetails_applyIncrement'] }}"
                                                                    readonly />
                                                            @endif
                                                        </td>
                                                        {{-- <script>
                                                $('.td-aplica').attr('style', 'display: ');
                                            </script> --}}
                                                        {{-- @endif --}}
                                                        {{-- @if ($detalle['inventoryDetails_applyIncrement'] != null) --}}
                                                        <td style="display: none;" class="aplicaIncre">
                                                            @if ($detalle['inventoryDetails_apply'] != null)
                                                                <input type="text"
                                                                    name="dataArticulos"
                                                                    id="aplicaIncre-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"
                                                                    value="{{ $detalle['inventoryDetails_applyIncrement'] }}"
                                                                    readonly />
                                                            @endif
                                                        </td>
                                                        {{-- <script>
                                                $('.td-consecutivo').attr('style', 'display: ');
                                            </script> --}}
                                                        {{-- @endif --}}
                                                        {{-- boton aplica --}}

                                                        <td id="btnInput"><input name="dataArticulos[]"
                                                                id="keyArticulo-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"
                                                                type="text" class="keyArticulo"
                                                                value="{{ $detalle['inventoryDetails_article'] }}"
                                                                onchange="buscadorArticulos('keyArticulo-{{ $detalle['inventoryDetails_article'] . '-' . $key }}')">

                                                            @if (!isset($detalle['inventoryDetails_apply']))
                                                                <button type="button" class="btn btn-info btn-sm agregarArticulos"
                                                                    data-toggle="modal" data-target=".modal3">...</button>
                                                            @endif

                                                            @if ($inventario['inventories_movement'] == 'Ajuste de Inventario' && $detalle['inventoryDetails_type'] == 'Serie')
                                                                    <button type="button" class="btn btn-warning btn-sm"
                                                                        data-toggle="modal" data-target=".modal6"
                                                                        id="modalSerie">S</button>
                                                            @endif

                                                            @if ($inventario['inventories_movement'] != 'Ajuste de Inventario' && $detalle['inventoryDetails_type'] == 'Serie')
                                                                    <button type="button" class="btn btn-warning btn-sm"
                                                                        data-toggle="modal" data-target=".modal7"
                                                                        id="modalSerie2">S</button>
                                                            @endif

                                                        </td>
                                                        <td style="display: none">
                                                            <input type="text" name="dataArticulos[]"
                                                                id="referenceArticle-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"
                                                                value="{{ $detalle['inventoryDetails_id'] }}" readonly />
                                                        </td>
                                                        <td><input name="dataArticulos[]"
                                                                id="desp-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"
                                                                type="text" class="botonesArticulos"
                                                                value="{{ $detalle['inventoryDetails_descript'] }}"
                                                                readonly
                                                                title="{{ $detalle['inventoryDetails_descript'] }}"></td>
                                                        <td>
                                                            <?php
                                                            $cantidadArticulos = floatVal($detalle['inventoryDetails_quantity']);
                                                            $decimales = strlen($cantidadArticulos) - strrpos($cantidadArticulos, '.') - 1;
                                                                
                                                            $cantidadCancelada = floatVal($detalle['inventoryDetails_quantity'] - $detalle['purchaseDetails_canceledAmount']);
                                                            $decimalesCancelada = strlen($cantidadCancelada) - strrpos($cantidadCancelada, '.') - 1;
                                                            ?>

                                                            <input name="dataArticulos[]"
                                                                id="canti-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"
                                                                type="text" class="botonesArticulos sinBotones"
                                                                onchange="changeCantidadInventario('{{ $detalle['inventoryDetails_article'] }}', '{{ $key }}')"
                                                                onfocus="changeCantidadInventario('{{ $detalle['inventoryDetails_article'] }}', '{{ $key }}')"
                                                                value="{{ $detalle['inventoryDetails_canceledAmount'] == null ? number_format($detalle['inventoryDetails_quantity'], $decimales) : number_format($detalle['inventoryDetails_quantity'] - $detalle['inventoryDetails_canceledAmount'], $decimales) }}">
                                                        </td>
                                                        <td class="costoArticulo"><input
                                                                name="dataArticulos[]"
                                                                id="c_unitario-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"
                                                                type="text" class="botonesArticulos sinBotones"
                                                                value="{{ number_format($detalle['inventoryDetails_unitCost'], 2) }}"
                                                            onchange="calcularImporte('{{ $detalle['inventoryDetails_article'] }}', '{{ $key }}')">
                                                        </td>
                                                        <td>
                                                            <select name="dataArticulos[]"
                                                                id="unid-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"
                                                                class="botonesArticulos"
                                                                value="{{ $detalle['inventoryDetails_unit'] }}"
                                                                onchange="recalcularCantidadInventario('{{ $detalle['inventoryDetails_article'] }}', '{{ $key }}')">
                                                            </select>
                                                        </td>
                                                        <td><input name="dataArticulos[]"
                                                                id="c_Inventario-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"
                                                                type="text" class="botonesArticulos sinBotones"
                                                                value="{{ $detalle['inventoryDetails_canceledAmount'] == null ? $detalle['inventoryDetails_inventoryAmount'] : $detalle['inventoryDetails_inventoryAmount'] - $detalle['inventoryDetails_canceledAmount'] * $detalle['inventoryDetails_factor'] }}"
                                                                readonly></td>

                                                        <td class="totalArticulo"><input name="dataArticulos[]"
                                                                id="importe_total-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"
                                                                type="text" class="botonesArticulos sinBotones"
                                                                value="{{ number_format($detalle['inventoryDetails_total'], 2) }}"
                                                                readonly></td>

                                                        <td style="display: flex; justify-content: center; align-items: center"
                                                            class="eliminacion-articulo">
                                                            <i class="fa fa-trash-o"
                                                                onclick="eliminarArticulo('{{ $detalle['inventoryDetails_article'] }}', '{{ $key }}')"
                                                                aria-hidden="true"
                                                                style="color: red; font-size: 25px; cursor: pointer;"></i>
                                                        </td>
                                                        @if ($inventario['inventories_status'] === 'POR AUTORIZAR')
                                                            {{-- botones de afectar --}}
                                                            <td class="accion-pendiente">
                                                                <input
                                                                    id="montoPendiente-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos botonPendiente"
                                                                    value="{{ floatVal($detalle['inventoryDetails_outstandingAmount']) }}"
                                                                    readonly>
                                                            </td>
                                                            <td class="accion-recibir">
                                                                <input
                                                                    id="montoRecibir-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"
                                                                    type="text" class="botonesArticulos botonRecibir"
                                                                    {{ $detalle['inventoryDetails_outstandingAmount'] == null ? 'readonly' : '' }}
                                                                    onchange="validarInput('{{ $detalle['inventoryDetails_article'] }}', '{{ $key }}')">
                                                            </td>
                                                            <script>
                                                                $('.accion-pendiente').attr('style', 'display: ');
                                                                $('.accion-recibir').attr('style', 'display: ');
                                                            </script>
                                                        @endif

                                                        <td style="display: none">
                                                            <input
                                                                id="tipoArticulo-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"
                                                                type="text" class="botonesArticulos"
                                                                value="{{ $detalle['inventoryDetails_type'] }}" readonly>
                                                        </td>
                                                         <td style="display: none">
                                                            <input
                                                                id="decimales-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"
                                                                type="text" value="" readonly>
                                                        </td>
                                                    </tr>
                                                    <script>
                                                        $.ajax({
                                                            url: "/logistica/compras/api/getMultiUnidad",
                                                            type: "GET",
                                                            data: {
                                                                factorUnidad: "{{ $detalle['inventoryDetails_article'] }}",
                                                            },
                                                            success: function(data) {
                                                                let unidadPorDefecto = "{{ $detalle['inventoryDetails_unit'] }}";
                                                                let unidadPorDefectoIndex = {};
                                                                data.forEach((element) => {
                                                                    if (element.articlesUnits_unit == unidadPorDefecto) {
                                                                        unidadPorDefectoIndex = element;
                                                                    }

                                                                    $("#unid-" + '{{ $detalle['inventoryDetails_article'] . '-' . $key }}').append(`
                                                    <option value="${element.articlesUnits_unit}-${element.articlesUnits_factor}">${element.articlesUnits_unit}-${element.articlesUnits_factor}</option>
                                                    `);

                                                                    $('input[id="canti-{{ $detalle['inventoryDetails_article'] . '-' . $key }}"]')
                                                                        .focus();

                                                                });


                                                                if (Object.keys(unidadPorDefectoIndex).length > 0) {
                                                                    $("#unid-" + '{{ $detalle['inventoryDetails_article'] . '-' . $key }}').val(
                                                                        unidadPorDefecto +
                                                                        "-" +
                                                                        unidadPorDefectoIndex.articlesUnits_factor
                                                                    );

                                                                   
                                                                }
                                                                 $("#unid-" + '{{ $detalle['inventoryDetails_article'] . '-' . $key }}').change();
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


                                            </tbody>
                                        @else
                                            <thead>
                                                <tr>
                                                    <th style="display: none">Id</th>
                                                    <th style="display: none" class="td-aplica">Aplica</th>
                                                    <th style="display: none" class="td-consecutivo">Consecutivo
                                                    </th>
                                                    <th>Producto/Item</th>
                                                    <th style="display: none">Referencia-article</th>
                                                    <th style="text-align: center; ">Nombre del Producto</th>
                                                    <th>Cantidad</th>
                                                    <th class="costoArticulo">Costo Unitario</th>
                                                    <th>Unidad de Venta</th>
                                                    <th>Cantidad de Inv.</th>
                                                    <th class="totalArticulo tablaInventario">Importe total</th>
                                                    <th class="eliminacion-articulo"></th>

                                                    {{-- botones de afectar --}}
                                                    <th style="display: none" class="accion-pendiente">
                                                        <p>Pendiente</p>
                                                    </th>
                                                    <th style="display: none" class="accion-recibir">A recibir</th>
                                                    <th style="display: none">Tipo</th>
                                                    <th style="display: none">Decimales</th>
                                                </tr>
                                            </thead>
                                            <tbody id="articleItem">
                                                <tr id="controlArticulo">
                                                    <td style="display: none"><input type="text" name="dataArticulos"
                                                            id="id-" value="" />
                                                    </td>
                                                    {{-- boton aplica --}}
                                                    <td style="display: none"><input type="text" name="dataArticulos"
                                                            id="id-" value="" />
                                                    </td>
                                                    <td style="display: none;"><input type="text"
                                                            name="dataArticulos" id="id-" value="" />
                                                    </td>
                                                    {{-- boton aplica --}}
                                                    <td id="btnInput"><input id="keyArticulo" type="text" class="keyArticuloInv"
                                                            onchange="buscadorArticulos('keyArticulo')">
                                                        <button type="button" class="btn btn-info btn-sm"
                                                            data-toggle="modal" data-target=".modal3">...</button>
                                                    </td>
                                                    <td style="display: none"><input type="text" name="dataArticulos"
                                                            id="id-" value="" />
                                                    </td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text"
                                                            class="botonesArticulos" disabled></td>
                                                    <td class="costoArticulo"><input id="" type="text"
                                                            class="botonesArticulos" disabled></td>
                                                    <td><input id=""
                                                            type="text" class="botonesArticulos" disabled></td>
                                                    <td><input id=""
                                                            type="text" class="botonesArticulos" disabled></td>
                                                    <td class="totalArticulo"><input id="" type="text"
                                                            class="botonesArticulos" disabled></td>
                                                    <td
                                                        style="display: flex; justify-content: center; align-items: center;">

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
                                                        <input id="" type="text" class="botonesArticulos"
                                                            readonly>
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

                                <div class="col-md-3 pull-right">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-addon">Total</span>
                                        <input type="text" class="form-control" id="totalCompleto"
                                            name="totalCompleto" readonly>
                                    </div>
                                </div>



                                <div class="col-md-1 pull-left">
                                    <div class="input-group input-group-sm mt5" style="margin-left: -5px">
                                        @if (isset($inventario) ? $inventario['inventories_status'] === 'INICIAL' : 'INICIAL')
                                            <span class="label label-default" id="status">INICIAL</span>
                                        @elseif($inventario['inventories_status'] === 'POR AUTORIZAR')
                                            <span class="label label-warning" id="status">POR AUTORIZAR</span>
                                        @elseif($inventario['inventories_status'] === 'FINALIZADO')
                                            <span class="label label-success" id="status">FINALIZADO</span>
                                        @elseif($inventario['inventories_status'] === 'CANCELADO')
                                            <span class="label label-danger" id="status">CANCELADO</span>
                                        @endif
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>




                    <ul class="list-unstyled wizard" id="botonesWizard">
                        <li class="pull-right finish hide"><button type="submit" class="btn btn-success">Crear
                                Movimiento</button></li>
                    </ul>

                    {{-- Input donde asignamos los nuevos valos de los articulos para manipularlo facilmente --}}
                    <input type="text" id="inputDataArticles" readonly hidden />
                    <input type="text" id="inputDataArticles2"  name="dataArticulosJson2" value="{{ isset($inventario) ? $inventario['inventories_jsonData'] : null }}" readonly hidden />
                    {{-- Añadimos los id que eliminamos de los articulos --}}
                    <input type="text" id="inputDataArticlesDelete" name="dataArticulosDelete" readonly hidden />
                    {!! Form::close() !!}


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

        <div class="modal fade bd-example-modal-lg modal9" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" id="almacenesDestinoModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Lista de almacenes destino</h5>

                    </div>
                    <div class="modal-body">
                        <div class="panel table-panel">
                            <table id="shTable9" class="table table-striped table-bordered widthAll">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Nombre</th>
                                        <th>Sucursal</th>
                                        <th style="display: none">Tipo</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($almacenesDestino as $almacen)
                                        {{-- @if() --}}
                                        <tr>
                                            <td>{{ $almacen->depots_key }}</td>
                                            <td>{{ $almacen->depots_name }}</td>
                                            <td>{{ $almacen->branchOffices_name }}</td>
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
                                        <th colspan="3"  class="text-center">
                                            <h5 class="modal-title text-center" id="exampleModalLongTitle">Lista de Artículos</h5>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Nombre</th>
                                        <th>Disponible</th>
                                        <th style="display: none">Iva</th>
                                        <th style="display: none">Unidad</th>
                                        <th style="display: none">Tipo</th>
                                        <th style="display: none">Unidad 2</th>

                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($articulos as $articulo)
                                        <tr>
                                            <td>{{ $articulo->articles_key }}</td>
                                            <td>{{ $articulo->articles_descript }}</td>
                                            <td style="display: none">{{ $articulo->articles_porcentIva }}</td>
                                            <td style="display: none">{{ $articulo->articles_unitSale }}</td>
                                            <td style="display: none">{{ $articulo->articles_type }}</td>

                                            <td>{{ number_format($articulo->articlesInv_inventory, 2) }}</td>

                                            <td style="display: none">{{ $articulo->articles_transfer }}</td>
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
                        <button type="button" style="display: none" class="btn btn-success" id="quitarSeries">
                            Seleccionar series
                        </button>
                        <button type="button" class="btn btn-primary" id="generarLotesSeries">
                            Generar Lote de Series
                        </button>
                        <button type="button" class="btn btn-success" id="modal6Agregar">Agregar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg modal7" tabindex="-1" role="dialog"
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



        <!-- Modal Orden de Compra -->
        <div class="modal fade" id="modalCompra" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-center" id="exampleModalCenterTitle">Módulo - Inventarios</h5>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="generarReciboTraspaso" name="accionCompra"
                                            value="Generar Entrada por Traspaso" checked>
                                            <label>Generar Entrada por Traspaso</label>

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
                            Generar movimiento por:
                        </h5>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="cantidadIndicada" name="accionEntradaCompra"
                                            value="Cantidad Indicada">
                                        <label>Cantidad Indicada</label>

                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group container-respuesta">
                                        <input type="radio" id="cantidadPendiente" name="accionEntradaCompra"
                                            value="Cantidad Pendiente" checked>
                                        <label>Cantidad Pendiente</label>
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
                            value="{{ isset($inventario) ? (isset($primerFlujoDeInventario) ? $primerFlujoDeInventario : '') : '' }}" />
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
                        <button type="button" class="btn btn-primary optionFlujo" id="siguiente-Flujo">Siguiente <span
                                class="glyphicon glyphicon-arrow-right"></span></button>


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
                            <li id="li3" class=""><a href="#following" data-toggle="tab"><strong>Costo</strong></a>
                            </li>
                            @endif
                        </ul>
                    </div>
                    <div>
                        <div class="col-sm-8 col-md-12">
                                <!-- Tab panes -->
                                <div class="tab-content nopadding noborder"  style="margin-top: 15px;">
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
                                                $inventario_info = floatVal($infoArticulo->articlesInv_inventory);
                                                $decimales = strlen($inventario_info) - strrpos($inventario_info, '.') - 1;
                                            }
                                            
                                            ?>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="disponibleCostoPromedio"
                                                        class="negrita">Disponible:</label>
                                                    <input type="text" readonly class="form-control input-sm"
                                                        value="{{ isset($infoArticulo) ? number_format($inventario_info, $decimales) : '' }}"
                                                        name="disponibleCostoPromedio" id="disponibleCostoPromedio">
                                                </div>
                                            </div>


                                            <div class="col-md-4 pull-right">
                                                <div class="form-group">
                                                    <label for="existenciaCostoPromedio"
                                                        class="negrita">Existencia:</label>
                                                    <input type="text" readonly class="form-control input-sm"
                                                        value="{{ isset($infoArticulo) ? number_format($inventario_info, $decimales) : '' }}"
                                                        name="existenciaCostoPromedio" id="existenciaCostoPromedio">
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <table class="table">
                                                    @if (isset($articulosByAlmacen))
                                                        <tr>
                                                            <th>Almacén</th>
                                                            <th>Disponible</th>
                                                        </tr>

                                                        <tbody class="tableAlmacenesDisponibles">

                                                        
                                                        @foreach ($articulosByAlmacen as $key => $item)
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
                                                        value="{{ isset($infoArticulo) ? '$' . number_format($infoArticulo->articles_listPrice1, 2) : '' }}"
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
                                                            value="{{ isset($infoArticulo) ? '$' . number_format($infoArticulo->articlesCost_lastCost, 2) : '' }}"
                                                            id="ultimoCostoPromedio">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-sm-4 control-label"><strong>Último Costo
                                                            Promedio:</strong> </label>
                                                    <div class="col-sm-8">
                                                        <input type="text" readonly class="form-control input-sm"
                                                            value="{{ isset($infoArticulo) ? '$' . number_format($infoArticulo->articlesCost_averageCost, 2) : '' }}"
                                                            id="ultimoCostoPromedio2">
                                                    </div>
                                                </div>

                                            </div>

                                        </div><!-- activity-list -->
                                        <button aria-hidden="true" class="btn btn-white btn-block" data-dismiss="modal">
                                            Cerrar</button>
                                    </div><!-- tab-pane -->



                                </div><!-- tab-content -->
                        </div><!-- panel -->
                    </div>

                    <div class="modal-footer">

                    </div>
                </div>
            </div>
        </div>

        @include('include.mensaje')

        <script>
            let contadorArticulosString = '{{ isset($articulosByInventario) ? count($articulosByInventario) : 0 }}';
            let contadorArticulos = parseInt(contadorArticulosString);
            jQuery('#cantidadArticulos').val(contadorArticulos);
            let monedaDefecto = "{{ isset($parametro) ? $parametro['generalParameters_defaultMoney'] : PESOS }}";


            window.addEventListener('load', function() {
                jQuery("#select-moneda").attr('readonly', true);
            });

            if (jQuery("#select-movimiento").val() != 'Ajuste de Inventario') {
                jQuery(".almacenDestinoDiv").show();
                jQuery(".costoArticulo").hide();
                jQuery(".totalArticulo").hide();
                jQuery("#totalCompleto").hide();
            } else {
                jQuery(".almacenDestinoDiv").hide();
                jQuery(".costoArticulo").show();
                jQuery(".totalArticulo").show();
                jQuery("#totalCompleto").show();
            }

            //poner el valor del costo del articulo en el input costo
            $('input[id^="c_unitario-"]').each(function(index, value) {
                $(this).attr('data', $(this).val());
            });

            $('input[id^="importe_total-"]').each(function(index, value) {
                $(this).attr('data', $(this).val());
            });
        </script>
        <script src="{{ asset('js/PROCESOS/inventarios.js') }}"></script>
        <script>
            $('#select-moduleConcept').val('{{ isset($inventario) ? $inventario['inventories_concept'] : '' }}').trigger(
                'change.select2');


            $('#select-proveedorCondicionPago').val('{{ isset($inventario) ? $inventario['inventories_condition'] : '' }}')
                .trigger(
                    'change.select2');

            window.addEventListener("pageshow", function (event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });
        </script>
    @endsection
