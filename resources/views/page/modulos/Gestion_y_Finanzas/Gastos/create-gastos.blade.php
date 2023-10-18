@extends('layouts.layout')

@section('content')
    @foreach (auth()->user()->getAllPermissions()->where('categoria', '=', 'Gastos')->pluck('name')->toArray() as $permisos)
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
                    'route' => ['modulo.gastos.store-gasto', 'id' => isset($gasto) ? $gasto->expenses_id : 0],
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

                <div class="col-md-12">
                    <div class="col-md-12">
                        <p class="titulo text-left">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="col-md-10">
                        <h2 class="text-black">Datos Generales</h2>
                    </div>
                    <div class="col-md-2 btn-action">
                        <button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown">
                            Menú de opciones <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu" id="Opciones">

                            @can('Afectar')
                                <li><a href="#" id="afectar-boton"> Finalizar <span
                                            class="glyphicon glyphicon-play pull-right"></span></a></li>
                            @endcan


                            <li><a href="#" id="eliminar-boton">Eliminar <span
                                        class="glyphicon glyphicon-remove-circle pull-right"></span></a></li>

                            @can('Cancelar')
                                <li><a href="#" id="cancelar-boton">Cancelar <span
                                            class="glyphicon glyphicon-trash pull-right"></span></a></li>
                            @endcan


                            @if (isset($gasto) && $gasto->expenses_status !== 'INICIAL')
                                <li><a href="{{ route('vista.modulo.gastos.reportes', ['idGasto' => $gasto['expenses_id']]) }}"
                                        target="_blank">Reporte <span
                                            class="glyphicon glyphicon-list-alt pull-right"></span></a>
                                </li>
                            @endif

                            @if (isset($gasto))
                                <li>
                                    <a href="#" id="copiar-gasto">Copiar <span
                                            class="fa fa-copy pull-right"></span></a>
                                </li>
                            @endif

                            @if (isset($gasto))
                                <li class="divider"></li>
                                <li><a href="{{ route('vista.modulo.gastos.create-gasto') }}" id="nuevo-boton">Nuevo<span
                                            class="fa fa-file-o pull-right"></span></a></li>
                                <li><a href="{{ route('vista.modulo.gastos.anexos', ['id' => $gasto->expenses_id]) }}"
                                        id="anexos-boton">Anexos
                                        <span class="glyphicon glyphicon-paperclip pull-right"></span></a></li>

                                <li><a href="" data-toggle="modal" data-target="#ModalFlujo">
                                        ver flujo
                                        <span class="glyphicon glyphicon-transfer pull-right"></span>
                                    </a></li>

                                <li><a href="" data-toggle="modal" data-target="#informacionProveedorModal">
                                        Inf. Proveedor
                                        <span class="glyphicon glyphicon-exclamation-sign pull-right"></span>
                                    </a></li>
                            @endif

                            {{-- <li><a href="#">Separated link</a></li> --}}
                        </ul>
                    </div>
                </div>

                <div class="col-md-12"></div>

                <div class="col-md-12 cabecera-informacion">
                    <div id="leyenda-container" style="display: none;">
                        <p id="leyenda"></p>
                    </div>
                    <div class="col-md-3">
                        <!-- Movimientos -->
                        <div class="form-group">
                            {!! Form::labelValidacion('movimientos', 'Proceso/Operación', 'negrita') !!}
                            {!! Form::select('movimientos', $movimientos, isset($gasto) ? $gasto['expenses_movement'] : null, [
                                'id' => 'select-movimiento',
                                'class' => 'widthAll select-movimiento',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-2" style="display: none">
                        <div class="form-group">
                            <label for="id" class="negrita">ID:</label>
                            <input type="number" class="form-control" name="id" id="idGasto"
                                value="{{ isset($gasto) ? $gasto['expenses_id'] : 0 }}"readonly>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="folio" class="negrita">Folio:</label>
                            <input type="number" class="form-control" name="folio"
                                value="{{ isset($gasto) ? $gasto['expenses_movementID'] : '' }}" readonly id="folioGasto" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fechaEmision" class="negrita">Fecha</label>
                            <input type="date" class="form-control input-date" name="fechaEmision" id="fechaEmision"
                                placeholder="Fecha Emisión"
                                value="{{ isset($gasto) ? \Carbon\Carbon::parse($gasto['expenses_issueDate'])->format('Y-m-d') : $fecha_actual }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::labelValidacion('nameMoneda', 'Moneda', 'negrita') !!}
                            {!! Form::select(
                                'nameMoneda',
                                $selectMonedas,
                                isset($gasto) ? $gasto['expenses_money'] : $parametro->generalParameters_defaultMoney,
                                [
                                    'id' => 'select-search-hided',
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
                                isset($gasto) ? floatVal($gasto['expenses_typeChange']) : floatVal($parametro->money_change),
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
                        <label for="proveedor" class="negrita">Proveedor/Acreedor<span class='asterisk'>
                                *</span></label>
                        <div class="input-group form-group mb15">
                            <input type="text" class="form-control" id="proveedorKey" name="proveedorKey"
                                value="{{ isset($gasto) ? $gasto['expenses_provider'] : null }}" />
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" data-toggle="modal" data-target=".modal2"
                                    id="provedorModal">...</button>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="proveedorName" class="negrita">Nombre/Razón Social del Proveedor/Acreedor</label>
                            <input type="text" class="form-control" name="proveedorName" id="proveedorName"
                                value="{{ isset($gasto) ? $nameProveedor['providers_name'] : null }}" readonly>
                        </div>
                    </div>

                    <div class="col-md-4" id="contenedorCuenta">
                        <label for="cuenta" class="negrita">Cuenta de Bancos/Efectivo <span class='asterisk'>
                                *</span></label>
                        <div class="input-group form-group mb15">
                            <input type="text" class="form-control" id="cuentaKey" name="cuentaKey"
                                value="{{ isset($tipoCuenta2) ? $tipoCuenta2['moneyAccounts_key'] : '' }}" readonly />
                            <input type="hidden" class="form-control" name="tipoCuenta" id="tipoCuenta"
                                value="{{ isset($tipoCuenta2) ? $tipoCuenta2['moneyAccounts_accountType'] : '' }}"
                                readonly />
                            <input type="hidden" class="form-control" name="tipoCambio" id="tipoCambio"
                                value="{{ isset($tipoCuenta2) ? $tipoCuenta2['moneyAccounts_money'] : '' }}" readonly />

                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" data-toggle="modal" data-target=".modal1"
                                    id="cuentaModal">...</button>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-12"></div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="proveedorCondicionPago" class="negrita">Término de Crédito<span class='asterisk'>
                                    *</span></label>
                            <select name="proveedorCondicionPago" id="select-proveedorCondicionPago" class="widthAll">
                                <option value="" selected aria-disabled="true">Selecciona uno...
                                </option>

                                @foreach ($select_condicionPago as $condicionPago)
                                    <option value="{{ $condicionPago->creditConditions_id }}">
                                        {{ $condicionPago->creditConditions_name }}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="proveedorFechaVencimiento" class="negrita">Fecha vencimiento:</label>
                            <input type="date" class="form-control input-date" name="proveedorFechaVencimiento"
                                id="proveedorFechaVencimiento"
                                value='{{ isset($gasto) ? \Carbon\Carbon::parse($gasto['expenses_expiration'])->format('Y-m-d') : null }}'>
                        </div>

                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="formaPago" class="negrita">Forma de pago:<span class='asterisk'>
                                    *</span></label>
                            <select name="formaPago" id="select-PaymentMethod" class="widthAll">
                                <option value="">Selecciona uno...</option>

                                @foreach ($select_forma as $forma)
                                    <option value="{{ $forma->formsPayment_key }}">
                                        {{ $forma->formsPayment_name }}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>


                    <div class="col-md-12" id="contenedorObservaciones">
                        <div class="form-group">
                            <label for="observaciones" class="negrita">Observaciones:</label>
                            <input type="text" class="form-control"
                                value="{{ isset($gasto) ? $gasto['expenses_observations'] : null }}" name="observaciones"
                                id="observaciones">
                        </div>
                    </div>

                    <script>
                        let mov = $('#select-movimiento').val();
                    </script>


                    <div class="col-md-2">
                        <div class="col-md-4 mt10">
                            <div class="checkbox block"><label><input type="checkbox" name="antecedentes"
                                        id="antecedentes"
                                        {{ isset($gasto) ? ($gasto['expenses_antecedents'] === '1' ? 'checked' : '') : '' }}>
                                    <strong>Factura Relacionada</strong></label></div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="col-md-4 mt10">
                            <div class="checkbox block"><label><input type="checkbox" name="activoFijo" id="activoFijo"
                                        {{ isset($gasto) ? ($gasto['expenses_fixedAssets'] === '1' ? 'checked' : '') : '' }}>
                                    <strong>Activo
                                        Fijo</strong></label></div>
                        </div>
                    </div>

                    <div class="col-md-4" id="contenedorAntecedente">
                        <label for="proveedor" class="negrita">Factura Relacionada:</label>
                        <div class="input-group form-group mb15">
                            <input type="text" class="form-control" id="antecedentesName" name="antecedentesName"
                                value="{{ isset($gasto) ? $gasto['expenses_antecedentsName'] : null }}" />
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" data-toggle="modal" data-target=".modal5"
                                    id="antecedentesModal">...</button>
                            </span>
                        </div>
                    </div>



                    {{-- <div class="col-md-4">
                        <label for="proveedor" class="negrita">Proveedor <span class='asterisk'>
                                *</span></label>
                        <div class="input-group form-group mb15">
                            <input type="text" class="form-control" id="proveedorKey" name="proveedorKey"
                                value="{{ isset($gasto) ? $gasto['expenses_provider'] : null }}" />
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" data-toggle="modal" data-target=".modal2"
                                    id="provedorModal">...</button>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="proveedorName" class="negrita">Nombre:</label>
                            <input type="text" class="form-control" name="proveedorName" id="proveedorName"
                                value="{{ isset($gasto) ? $nameProveedor['providers_name'] : null }}" readonly>
                        </div>
                    </div> --}}

                    <div class="col-md-2" id="contenedorActivoFijo">
                        <label for="proveedor" class="negrita">Clave:</label>
                        <div class="input-group form-group mb15">
                            <input type="text" class="form-control" id="activoFijoNombre" name="activoFijoNombre"
                                value="{{ isset($gasto) ? $gasto['expenses_fixedAssetsName'] : null }}" readonly />
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" data-toggle="modal" data-target=".modal6"
                                    id="activoFijoModal">...</button>
                            </span>
                        </div>
                    </div>


                    <div class="col-md-2" id="contenedorActivoFijoSerie">
                        <label for="proveedor" class="negrita">Serie:</label>
                        <div class="input-group form-group mb15">
                            <input type="text" class="form-control" id="activoFijoSerie" name="activoFijoSerie"
                                value="{{ isset($gasto) ? $gasto['expenses_fixedAssetsSerie'] : null }}" />
                            <span class="input-group-btn">
                            </span>
                        </div>
                    </div>





                    <div id="contenedorTabla">
                        <div class="tablaDesborde">
                            <div class="tab-content table-panel">
                                <table class="table table-striped table-bordered gastosM widthAll">
                                    <thead>
                                        <tr>
                                            <th style="display: none">ID</th>
                                            <th style="{{ isset($gasto) ? ($gasto['expenses_movement'] === 'Reposición Caja' ? 'display:block' : 'display: none') : 'display: none' }}"
                                                class='establecimiento'>Negocio</th>
                                            <th>Razón del Gasto</th>
                                            <th>Folio Nota/Factura</th>
                                            <th>Cantidad</th>
                                            <th>Importe Unit.</th>
                                            <th>Importe Sin Imptos.</th>
                                            <th>% IVA</th>
                                            <th>IVA</th>
                                            <th class='retencion'>% Ret. ISR</th>
                                            <th class='retencion'>Ret. ISR</th>
                                            <th class='retencion'>% Ret. IVA</th>
                                            <th class='retencion'>Ret. IVA</th>
                                            <th>Importe Total</th>
                                            <th class="eliminacion-concepto"></th>

                                            {{-- <th>Pendiente</th>
                                            <th>A recibir</th> --}}
                                        </tr>
                                    </thead>

                                    @if (isset($gastoD) && count($gastoD) > 0)
                                        <tr id="controlConcepto2" style="display: none">
                                            <td style="display: none"><input type="text" name="dataConceptos"
                                                    id="id-" value="" />
                                            </td>
                                            <td style="display: none" class='establecimiento'><input id="establecimiento"
                                                    type="text" class="" disabled style="width: 5px">
                                                {{-- <button type="button" class="btn btn-default btn-sm" data-toggle="modal"
                                                data-target=".modal3">...</button> --}}
                                            </td>
                                            <td><input type="text" class="keyArticulo" disabled id="btnInput">
                                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                                    data-target=".modal4">...</button>
                                            </td>
                                            <td><input id="" type="text" class="botonesArticulos" disabled>
                                            </td>
                                            <td><input id="" type="text" class="botonesArticulos" disabled>
                                            </td>
                                            <td><input id="" type="text" class="botonesArticulos" disabled>
                                            </td>
                                            <td><input id="" type="text" class="botonesArticulos" disabled>
                                            </td>
                                            <td><input id="" type="text" class="botonesArticulos" disabled>
                                            </td>
                                            <td><input id="" type="text" class="botonesArticulos" disabled>
                                            </td>
                                            <td style="display: none" class='retencion'><input id=""
                                                    type="text" class="botonesArticulos" disabled></td>
                                            <td style="display: none" class='retencion'><input id=""
                                                    type="text" class="botonesArticulos" disabled></td>
                                            <td style="display: none" class='retencion'><input id=""
                                                    type="text" class="botonesArticulos" disabled></td>
                                            <td style="display: none" class='retencion'><input id=""
                                                    type="text" class="botonesArticulos" disabled>
                                            </td>
                                            <td><input id="" type="text" class="botonesArticulos" disabled>
                                            </td>
                                            <td style="display: flex; justify-content: center; align-items: center">

                                                <i class="fa fa-trash-o  btn-delete-articulo" aria-hidden="true"
                                                    style="color: red; font-size: 25px; cursor: pointer;"></i>
                                            </td>
                                            {{-- <td><input id="" type="text" class="botonesArticulos"
                                                disabled></td>
                                        <td><input id="" type="text" class="botonesArticulos"
                                                disabled></td> --}}

                                        </tr>
                                        <tbody id="conceptoItem">
                                            <?php
                                            $retencionesDisponibles = false;
                                            $totalConceptos = 0;
                                            ?>
                                            @foreach ($gastoD as $key => $gastoDetalle)
                                                <?php
                                                $conceptoClaveId = str_replace(' ', '', $gastoDetalle['expensesDetails_id']);
                                                ?>
                                                <tr id="{{ $conceptoClaveId . '-' . $key }}">
                                                    <td style="display: none"><input type="text" name="dataConceptos"
                                                            id="id-{{ $conceptoClaveId . '-' . $key }}"
                                                            value="{{ $gastoDetalle['expensesDetails_id'] }}" />
                                                    </td>

                                                    <td style="{{ $gasto['expenses_movement'] === 'Reposición Caja' ? '' : 'display: none' }}"
                                                        class='establecimiento'>
                                                        <input id="keyArticulo" type="text" class="keyArticulo"
                                                            readonly style='display:none' value='{{ $conceptoClaveId }}'
                                                            name="dataConceptos[]">
                                                        <input id="establecimiento-{{ $conceptoClaveId . '-' . $key }}"
                                                            type="text" class="keyArticulo" readonly
                                                            name="dataConceptos[]"
                                                            value='{{ $gastoDetalle['expensesDetails_establishment'] }}'>
                                                        <button type="button" class="btn btn-default btn-sm"
                                                            data-toggle="modal" data-target=".modal3"
                                                            id="modal-{{ $conceptoClaveId . '-' . $key }}">...</button>
                                                        {{-- <button type="button" class="btn btn-default btn-sm" data-toggle="modal"
                                                    data-target=".modal3">...</button> --}}
                                                    </td>

                                                    <td id="btnInput"><input
                                                            id="concept-{{ $conceptoClaveId . '-' . $key }}"
                                                            type="text" class="keyArticulo" readonly
                                                            value='{{ $gastoDetalle['expensesDetails_concept'] }}'
                                                            title='{{ $gastoDetalle['expensesDetails_concept'] }}'
                                                            name="dataConceptos[]">
                                                        <button type="button" class="btn btn-info btn-sm"
                                                            data-toggle="modal" data-target=".modal4">...</button>
                                                    </td>
                                                    <td><input id="ref-{{ $conceptoClaveId . '-' . $key }}"
                                                            type="text" class="botonesArticulos"
                                                            name="dataConceptos[]"
                                                            value="{{ $gastoDetalle['expensesDetails_reference'] }}">
                                                    </td>
                                                    <td><input id="cantidad-{{ $conceptoClaveId . '-' . $key }}"
                                                            type="number" class="botonesArticulos"
                                                            value='{{ $gastoDetalle['expensesDetails_quantity'] }}'
                                                            name="dataConceptos[]" min="1"
                                                            onchange="actualizarTotales('{{ $conceptoClaveId }}', '{{ $key }}')">
                                                    </td>
                                                    <td><input id="precio-{{ $conceptoClaveId . '-' . $key }}"
                                                            type="text" class="botonesArticulos"
                                                            onchange="calcularImporteRetencion('{{ $conceptoClaveId }}', '{{ $key }}')"
                                                            name="dataConceptos[]"
                                                            value="{{ number_format($gastoDetalle['expensesDetails_price'], 2) }}">
                                                    </td>
                                                    <td><input id="importe-{{ $conceptoClaveId . '-' . $key }}"
                                                            type="text" class="botonesArticulos" readonly
                                                            name="dataConceptos[]"
                                                            value="{{ number_format($gastoDetalle['expensesDetails_amount'], 2) }}">
                                                    </td>
                                                    <td><input id="pIva-{{ $conceptoClaveId . '-' . $key }}"
                                                            type="text" class="botonesArticulos" readonly
                                                            value='{{ $gastoDetalle['expensesDetails_vat'] == '.0000' ? '' : $gastoDetalle['expensesDetails_vat'] }}'
                                                            name="dataConceptos[]">
                                                    </td>
                                                    <td><input id="iva-{{ $conceptoClaveId . '-' . $key }}"
                                                            type="text" class="botonesArticulos" readonly
                                                            value='{{ number_format($gastoDetalle['expensesDetails_vatAmount'], 2) }}'
                                                            name="dataConceptos[]">
                                                    </td>

                                                    @if (
                                                        $gastoDetalle['expensesDetails_retention1'] !== '.0000' ||
                                                            $gastoDetalle['expensesDetails_retentionISR'] !== '.0000' ||
                                                            $gastoDetalle['expensesDetails_retention2'] !== '.0000' ||
                                                            $gastoDetalle['expensesDetails_retentionIVA'] !== '.0000')
                                                        <?php $retencionesDisponibles = true; ?>
                                                    @endif


                                                    <td class="retencion2"><input
                                                            id="pRet1-{{ $conceptoClaveId . '-' . $key }}" type="text"
                                                            class="botonesArticulos" readonly
                                                            value='{{ $gastoDetalle['expensesDetails_retention1'] == '.0000' ? '' : $gastoDetalle['expensesDetails_retention1'] }}'
                                                            name="dataConceptos[]"></td>
                                                    <td class="retencion2"><input
                                                            id="pRetISR-{{ $conceptoClaveId . '-' . $key }}"
                                                            type="text" class="botonesArticulos" readonly
                                                            value='{{ number_format($gastoDetalle['expensesDetails_retentionISR'], 2) }}'
                                                            name="dataConceptos[]"></td>
                                                    <td class="retencion2"><input
                                                            id="pRet2-{{ $conceptoClaveId . '-' . $key }}" type="text"
                                                            class="botonesArticulos" readonly
                                                            value='{{ $gastoDetalle['expensesDetails_retention2'] == '.0000' ? '' : $gastoDetalle['expensesDetails_retention2'] }}'
                                                            name="dataConceptos[]"></td>
                                                    <td class="retencion2"><input
                                                            id="retIva-{{ $conceptoClaveId . '-' . $key }}"
                                                            type="text" class="botonesArticulos" readonly
                                                            value='{{ number_format($gastoDetalle['expensesDetails_retentionIVA'], 2) }}'
                                                            name="dataConceptos[]">
                                                    </td>


                                                    <?php
                                                    $totalConceptos += (float) $gastoDetalle['expensesDetails_total'];
                                                    ?>


                                                    <td><input id="total-{{ $conceptoClaveId . '-' . $key }}"
                                                            type="text" class="botonesArticulos" readonly
                                                            value='{{ number_format($gastoDetalle['expensesDetails_total'], 2) }}'
                                                            name="dataConceptos[]">
                                                    </td>
                                                    <td style="display: flex; justify-content: center; align-items: center"
                                                        class="eliminacion-concepto">

                                                        <i class="fa fa-trash-o  btn-delete-articulo"
                                                            onclick="eliminarConcepto('{{ $conceptoClaveId }}', '{{ $key }}')"
                                                            aria-hidden="true"
                                                            style="color: red; font-size: 25px; cursor: pointer;"></i>
                                                    </td>
                                                    {{-- <td><input id="" type="text" class="botonesArticulos"
                                                    disabled></td>
                                            <td><input id="" type="text" class="botonesArticulos"
                                                    disabled></td> --}}

                                                </tr>
                                            @endforeach

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
                                        <tbody id="conceptoItem">
                                            <tr id="controlArticulo">
                                                <td style="display: none"><input type="text" name="dataConceptos"
                                                        id="id-" value="" />
                                                </td>
                                                <td style="display: none" class='establecimiento'><input
                                                        id="establecimiento" type="text" class="" disabled
                                                        style="width: 90%">
                                                    {{-- <button type="button" class="btn btn-default btn-sm" data-toggle="modal"
                                                    data-target=".modal3">...</button> --}}
                                                </td>
                                                <td id="btnInput"><input id="" type="text"
                                                        class="keyArticulo" disabled>
                                                    <button type="button" class="btn btn-info btn-sm"
                                                        data-toggle="modal" data-target=".modal4">...</button>
                                                </td>
                                                <td><input id="" type="text" class="botonesArticulos"
                                                        disabled>
                                                </td>
                                                <td><input id="" type="text" class="botonesArticulos"
                                                        disabled>
                                                </td>
                                                <td><input id="" type="text" class="botonesArticulos"
                                                        disabled>
                                                </td>
                                                <td><input id="" type="text" class="botonesArticulos"
                                                        disabled>
                                                </td>
                                                <td><input id="" type="text" class="botonesArticulos"
                                                        disabled>
                                                </td>
                                                <td><input id="" type="text" class="botonesArticulos"
                                                        disabled>
                                                </td>
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
                                                        disabled>
                                                </td>
                                                <td style="display: flex; justify-content: center; align-items: center"
                                                    class="eliminacion-concepto">

                                                    <i class="fa fa-trash-o  btn-delete-articulo" aria-hidden="true"
                                                        style="color: red; font-size: 25px; cursor: pointer;"></i>
                                                </td>
                                                {{-- <td><input id="" type="text" class="botonesArticulos"
                                                    disabled></td>
                                            <td><input id="" type="text" class="botonesArticulos"
                                                    disabled></td> --}}

                                            </tr>
                                        </tbody>
                                    @endif
                                </table>
                            </div><!-- panel -->





                        </div>
                    </div>

                    <div class="col-md-3 pull-right">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Total</span>
                            <input type="text" class="form-control" id="totalCompleto" name="totalCompleto"
                                value="" readonly>
                        </div>
                    </div>
                    <div class="col-md-3 pull-right" style="display: none">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Impuestos</span>
                            <input type="text" class="form-control" id="impuestosCompleto" name="impuestosCompleto"
                                readonly>
                        </div>
                    </div>

                    <div class="col-md-3 pull-right" style="display: none">
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon"> Sub-Total</span>
                            <input type="text" class="form-control" id="subTotalCompleto" name="subTotalCompleto"
                                readonly>
                        </div>
                    </div>
                    <div class="col-md-1 pull-left">
                        <div class="input-group input-group-sm mt5" style="margin-left: -5px">
                            @if (isset($gasto) ? $gasto['expenses_status'] === 'INICIAL' : 'INICIAL')
                                <span class="label label-default" id="status">INICIAL</span>
                            @elseif($gasto['expenses_status'] === 'POR AUTORIZAR')
                                <span class="label label-warning" id="status">POR AUTORIZAR</span>
                            @elseif($gasto['expenses_status'] === 'FINALIZADO')
                                <span class="label label-success" id="status">FINALIZADO</span>
                            @elseif($gasto['expenses_status'] === 'CANCELADO')
                                <span class="label label-danger" id="status">CANCELADO</span>
                            @endif

                        </div>
                    </div>

                    <div class="col-md-12 mt20 display-left">
                        {!! Form::submit('Crear/Guardar', ['class' => 'btn btn-success', 'id' => 'enviarForm']) !!}
                    </div>
                    <input type="text" id="inputDataConceptos" readonly hidden />
                    <input type="text" id="inputDataConceptosDelete" name="dataConceptosDelete" readonly hidden />

                    {!! Form::close() !!}
                </div>
            </div>

            <div class="modal fade bd-example-modal-lg modal1" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true" id="cuentaModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLongTitle">Lista de cuentas de dinero</h5>

                        </div>
                        <div class="modal-body">
                            <div class="panel table-panel">
                                <table id="shTable1" class="table table-striped table-bordered widthAll">
                                    <thead>
                                        <tr>
                                            <th>Cuenta</th>
                                            <th>Moneda</th>
                                            <th style="display: none">tipo</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($moneyAccounts as $moneyAccount)
                                            <tr>
                                                <td>{{ $moneyAccount->moneyAccounts_key }}</td>
                                                <td>{{ $moneyAccount->money_key }}
                                                </td>
                                                <td style="display: none">{{ $moneyAccount->moneyAccounts_accountType }}
                                                </td>

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


            <div class="modal fade bd-example-modal-lg modal3" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true" id="establecimientoModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLongTitle">Acreedores/Resonsables</h5>

                        </div>
                        <input type="hidden" id="auxEstablecimiento">
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
                                        @foreach ($proveedores as $proveedor)
                                            <tr>
                                                <td>{{ $proveedor->providers_key }}</td>
                                                <td>{{ $proveedor->providers_name }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div><!-- panel -->
                        </div>

                        <div class="modal-footer">
                            <button type="button" id="agregarEstablecimiento" class="btn btn-success"
                                data-dismiss="modal">Agregar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade bd-example-modal-lg modal4" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true" id="conceptoModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLongTitle"> Conceptos - Gastos</h5>

                        </div>
                        <div class="modal-body">
                            <div class="panel table-panel">
                                <table id="shTable5" class="table table-striped table-bordered widthAll">
                                    <thead>
                                        <tr>
                                            <th style="display: none">id</th>
                                            <th>Nombre</th>
                                            <th style="display: none">IVA</th>
                                            <th style="display: none">ret1</th>
                                            <th style="display: none">ret2</th>
                                            <th style="display: none">ret3</th>
                                            <th style="display: none">exentoIva</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @foreach ($conceptos as $concepto)
                                            <tr>
                                                <td style="display: none">{{ $concepto->expenseConcepts_id }}</td>
                                                <td>{{ $concepto->expenseConcepts_concept }}</td>
                                                <td style="display: none">{{ $concepto->expenseConcepts_tax }}</td>
                                                <td style="display: none">{{ $concepto->expenseConcepts_retention }}</td>
                                                <td style="display: none">{{ $concepto->expenseConcepts_retention2 }}</td>
                                                <td style="display: none">{{ $concepto->expenseConcepts_retention3 }}</td>
                                                <td style="display: none">{{ $concepto->expenseConcepts_exemptIVA }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div><!-- panel -->
                        </div>

                        <div class="modal-footer">
                            <button type="button" id="agregarConceptos" class="btn btn-success"
                                data-dismiss="modal">Agregar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Flujo -->
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
                                value="{{ isset($gasto) ? (isset($primerFlujoDeGastos) ? $primerFlujoDeGastos : '') : '' }}" />
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


            <div class="modal fade bd-example-modal-lg modal5" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true" id="antecedentesModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLongTitle">Lista de antecedentes</h5>

                        </div>
                        <div class="modal-body">
                            <div class="panel table-panel">
                                <table id="shTable6" class="table table-striped table-bordered widthAll">
                                    <thead>
                                        <tr>
                                            <th>Folio</th>
                                            <th>Cliente</th>
                                            <th>Fecha</th>
                                            <th>Factura</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        @foreach ($antecedentes as $antecedente)
                                            <tr>
                                                <td>{{ $antecedente->sales_movementID }}</td>
                                                <td>{{ $antecedente->customers_businessName }}</td>
                                                <td>{{ $antecedente->sales_issuedate }}</td>
                                                <td>{{ $antecedente->sales_movement }} -
                                                    {{ $antecedente->sales_movementID }}</td>

                                            </tr>
                                        @endforeach
                                </table>
                            </div><!-- panel -->
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade bd-example-modal-lg modal6" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true" id="activoFijoModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLongTitle">Lista de articulos</h5>

                        </div>
                        <div class="modal-body">
                            <div class="panel table-panel">
                                <table id="shTable7" class="table table-striped table-bordered widthAll">
                                    <thead>
                                        <tr>
                                            <th>Clave</th>
                                            <th>Nombre</th>
                                            <th>Serie</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($aticulosSerie as $articulo)
                                            <tr>
                                                <td>{{ $articulo->lotSeries_article }}</td>
                                                <td>{{ $articulo->articles_descript }}</td>
                                                <td>{{ $articulo->lotSeries_lotSerie }}</td>
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
                aria-labelledby="myLargeModalLabel" aria-hidden="true" id="informacionProveedorModal" style="display: ">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLongTitle">Información Proveedor</h5>
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs nav-line" style="margin: 0px !important">
                                {{-- <li class="active"><a href="#activities2" data-toggle="tab"><strong></strong></a></li>
                                <li class=""><a href="#followers2" data-toggle="tab"><strong>Artículo</strong></a></li> --}}
                                <li class="active"><a href="#following2" data-toggle="tab"><strong>Datos
                                            Generales</strong></a>
                                </li>
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
                                                            <label for="articuloCostoPromedio"
                                                                class="negrita">RFC:</label>
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
                                                            <label for="articuloCostoPromedio"
                                                                class="negrita">Tipo:</label>
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
                                                    <h5 class="mb10">Movimientos</h5>
                                                    <!-- Nav tabs -->
                                                    <ul class="nav nav-tabs nav-justified">

                                                        @if (isset($monedasMov))
                                                            @foreach ($monedasMov as $moneda)
                                                                <li class="{{ $loop->first ? 'active' : '' }}">
                                                                    <a href="#{{ $moneda->money_key }}"
                                                                        data-toggle="tab">
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
                let contadorConceptosString = '{{ isset($gastoD) ? count($gastoD) : 0 }}';
                let contadorConceptos = parseInt(contadorConceptosString);
                jQuery('#cantidadArticulos').val(contadorConceptos);
                let monedaDefecto = "{{ isset($parametro) ? $parametro['generalParameters_defaultMoney'] : PESOS }}";
            </script>
            <script src="{{ asset('js/PROCESOS/gastos.js') }}"></script>
            <script>
                const conceptosGasto = "{{ isset($gastoD) ? count($gastoD) : 0 }}";

                if (conceptosGasto == 0) {
                    $(".retencion").hide();
                }


                $('#select-proveedorCondicionPago').val('{{ isset($gasto) ? $gasto['expenses_condition'] : '' }}').trigger(
                    'change.select2').change();

                $('#select-PaymentMethod').val('{{ isset($gasto) ? $gasto['expenses_paymentMethod'] : '' }}').trigger(
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
