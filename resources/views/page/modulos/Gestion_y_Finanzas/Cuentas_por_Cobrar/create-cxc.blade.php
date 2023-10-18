@extends('layouts.layout')

@section('content')
    @foreach (auth()->user()->getAllPermissions()->where('categoria', '=', 'Cuentas por cobrar')->pluck('name')->toArray() as $permisos)
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
                    'route' => ['modulo.cuentasCobrar.store-cxc'],
                    'id' => 'progressWizard',
                    'class' => 'panel-wizard',
                    'method' => 'POST',
                ]) !!}
                {{-- <input type="hidden" style="display: none" value="{{ isset($cxc) ? $cxc->accountsReceivable_id : 0 }}"
                    name="idCxp" readonly /> --}}

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
                        <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span>
                        </p>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="col-md-10">
                        <h2 class="text-black">Datos Generales</h2>
                    </div>
                    <div class="col-md-2 btn-action">
                        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                            Menú de opciones <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu" id="Opciones">


                            @if (!isset($cxc))
                                @can('Afectar')
                                    <li><a href="#" id="afectar-boton"> Afectar <span
                                                class="glyphicon glyphicon-play pull-right"></span></a></li>
                                @endcan

                                <li><a href="#" id="eliminar-boton">Eliminar <span
                                            class="glyphicon glyphicon-remove-circle pull-right"></span></a></li>
                            @else
                                @if ($cxc->accountsReceivable_status !== 'FINALIZADO')
                                    @can('Afectar')
                                        <li><a href="#" id="afectar-boton"> Avanzar <span
                                                    class="glyphicon glyphicon-play pull-right"></span></a></li>
                                    @endcan

                                    <li><a href="#" id="eliminar-boton">Eliminar <span
                                                class="glyphicon glyphicon-remove-circle pull-right"></span></a></li>
                                @endif
                            @endif

                            @can('Cancelar')
                                <li><a href="#" id="cancelar-boton">Cancelar <span
                                            class="glyphicon glyphicon-trash pull-right"></span></a></li>
                            @endcan

                            @if (isset($cxc) && $cxc->accountsReceivable_status !== 'INICIAL')
                                <li><a href="{{ route('vista.modulo.cuentasCxC.reportes', ['idCXC' => $cxc['accountsReceivable_id']]) }}"
                                        target="_blank">Reporte <span
                                            class="glyphicon glyphicon-list-alt pull-right"></span></a>
                                </li>
                            @endif

                            @if (isset($cxc))
                                <li>
                                    <a href="#" id="copiar-compra">Copiar <span
                                            class="fa fa-copy pull-right"></span></a>
                                </li>
                            @endif

                            @if (isset($cxc))
                                <li class="divider"></li>
                                <li><a href="{{ route('vista.modulo.cuentasCobrar.create-cxc') }}"
                                        id="nuevo-boton">Nuevo<span class="fa fa-file-o pull-right"></span></a></li>
                                <li><a href="{{ route('vista.modulo.cxc.anexos', ['id' => $cxc->accountsReceivable_id]) }}"
                                        id="anexos-boton">Anexos <span
                                            class="glyphicon glyphicon-paperclip pull-right"></span></a></li>
                                <li><a href="" data-toggle="modal" data-target="#ModalFlujo">
                                        Ver flujo
                                        <span class="glyphicon glyphicon-transfer pull-right"></span>
                                    </a></li>
                                <li><a href="#" data-toggle="modal" data-target="#informacionProveedorModal">
                                        Inf. Cliente
                                        <span class="glyphicon glyphicon-exclamation-sign pull-right"></span>
                                    </a></li>
                                @if (
                                    ($cxc->accountsReceivable_stamped == '0' || $cxc->accountsReceivable_stamped == null) &&
                                        session('company')->companies_calculateTaxes == '0')
                                    @if (
                                        $cxc->accountsReceivable_movement === 'Anticipo Clientes' ||
                                            $cxc->accountsReceivable_movement === 'Aplicación' ||
                                            $cxc->accountsReceivable_movement === 'Cobro de Facturas' ||
                                            $cxc->accountsReceivable_movement === 'Devolución de Anticipo')
                                        <li><a href="#" id="timbrado">
                                                Timbrado
                                                <span class="glyphicon glyphicon-repeat pull-right"></span>
                                            </a></li>
                                    @endif
                                @endif
                            @endif

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
                            {!! Form::select('movimientos', $movimientos, isset($cxc) ? $cxc->accountsReceivable_movement : null, [
                                'id' => 'select-movimiento',
                                'class' => 'widthAll select-movimiento',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-2" style="display: none">
                        <div class="form-group">
                            <label for="id" class="negrita">ID:</label>
                            <input type="number" class="form-control" name="id" id="idCXP"
                                value="{{ isset($cxc) ? $cxc['accountsReceivable_id'] : 0 }}"readonly>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="folio" class="negrita">Folio:</label>
                            <input type="number" class="form-control" name="folio"
                                value="{{ isset($cxc) ? $cxc->accountsReceivable_movementID : '' }}" readonly
                                id="folioMov" />
                        </div>
                    </div>

                    <div class="col-md-2" style="display: none">
                        <div class="form-group">
                            <label for="origin" class="negrita">ORIGIN:</label>
                            <input type="text" class="form-control" name="origin" readonly id="origin" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fechaEmision" class="negrita">Fecha Emisión</label>
                            <input type="date" class="form-control input-date" name="fechaEmision" id="fechaEmision"
                                placeholder="Fecha Emisión"
                                value="{{ isset($cxc) ? \Carbon\Carbon::parse($cxc['accountsReceivable_issuedate'])->format('Y-m-d') : date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::labelValidacion('nameMoneda', 'Moneda', 'negrita') !!}
                            {!! Form::select(
                                'nameMoneda',
                                $selectMonedas,
                                isset($cxc) ? $cxc->accountsReceivable_money : $parametro->generalParameters_defaultMoney,
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
                                isset($cxc) ? floatVal($cxc->accountsReceivable_typeChange) : floatVal($parametro->money_change),
                                [
                                    'class' => 'form-control',
                                    'readonly',
                                    'id' => 'nameTipoCambio',
                                ],
                            ) !!}
                        </div>
                    </div>
                    <div class="col-md-12">
                    </div>
                    <div class="col-md-4">
                        <label for="proveedor" class="negrita">Cliente <span class='asterisk'>
                                *</span></label>
                        <div class="input-group form-group mb15">
                            <input type="text" class="form-control" id="proveedorKey" name="proveedorKey"
                                value="{{ isset($cxc) ? $cxc->accountsReceivable_customer : null }}" />
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" data-toggle="modal" data-target=".modal2"
                                    id="proveedorModal">...</button>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="proveedorName" class="negrita">Nombre/Razón Social del Cliente:</label>
                            <input type="text" class="form-control" name="proveedorName" id="proveedorName"
                                value="" readonly>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="referencia" class="negrita">Notas/Comentarios:</label>
                            <input type="text" class="form-control"
                                value="{{ isset($cxc) ? $cxc->accountsReceivable_reference : null }}" name="referencia"
                                id="referencia">
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
                            <label for="proveedorFormaPago" class="negrita">Forma de pago <span class='asterisk'>
                                    *</span></label>
                            <select name="proveedorFormaPago" id="select-proveedorFormaPago" class="widthAll">
                                <option value="" selected aria-disabled="true">Selecciona uno...
                                </option>

                                @foreach ($select_formaPago as $formaPago)
                                    <option value="{{ $formaPago->formsPayment_key }}">
                                        {{ $formaPago->formsPayment_name }}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="cuenta" class="negrita">Seleccione la cuenta de Banco o Efectivo <span
                                class='asterisk'>
                                *</span></label>
                        <div class="input-group form-group mb15">
                            <input type="text" class="form-control" id="cuentaKey" name="cuentaKey"
                                value="{{ isset($cxc) ? $cxc->accountsReceivable_moneyAccount : '' }}" readonly
                                placeholder="Selecciona una..." />

                            <input type="hidden" name="tipoCuenta"
                                value="{{ isset($tipoCuenta) ? $tipoCuenta->moneyAccounts_accountType : '' }}"
                                id="tipoCuenta" />

                            <input type="hidden" name="tipoCambio"
                                value="{{ isset($tipoCuenta) ? $tipoCuenta->moneyAccounts_money : '' }}"
                                id="tipoCambio" />

                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" data-toggle="modal" data-target=".modal1"
                                    id="cuentaModal">...</button>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="importe" class="negrita">Importe: <span class='asterisk'>
                                    *</span></label>
                            <input type="text" class="form-control"
                                value="{{ isset($cxc) ? '$' . number_format($cxc->accountsReceivable_amount, 2) : null }}"
                                name="importe" id="importe">
                        </div>
                    </div>
                    <input type="hidden" class="form-control"
                        value="{{ isset($cxc) ? $cxc->accountsReceivable_originID : null }}" name="keyAnticipo"
                        id="keyAnticipo">
                    <div class="col-md-4" id="anticipoAplicar">
                        <label for="anticipo" id="anticipoChange" class="negrita">Anticipo/NC a Aplicar<span
                                class='asterisk'>
                                *</span></label>
                        <div class="input-group form-group mb15">
                            <input type="text" class="form-control" id="anticiposKey" name="anticiposKey" readonly
                                value="{{ isset($cxc) ? $cxc->accountsReceivable_origin . ' ' . $cxc->accountsReceivable_originID : null }}" />
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" data-toggle="modal" data-target=".7"
                                    id="anticiposModal">...</button>
                            </span>
                        </div>
                    </div>

                    <div
                        class="{{ isset($cxc) && $cxc->accountsReceivable_movement == 'Factura' ? 'col-md-2' : 'col-md-4' }} contenedoresImporte">
                        <div class="form-group">
                            <label for="impuesto" class="negrita">Impuesto:</label>
                            <input type="text" class="form-control"
                                value="{{ isset($cxc) ? number_format($cxc->accountsReceivable_taxes, 2) : null }}"
                                name="impuesto" id="impuesto" readonly>
                        </div>
                    </div>

                    <div class="col-md-4 contenedoresImporte">
                        <div class="form-group">
                            <label for="importeTotal" class="negrita">Importe Total:</label>
                            <input type="text" class="form-control"
                                value="{{ isset($cxc) ? number_format($cxc->accountsReceivable_total, 2) : null }}"
                                name="importeTotal" id="importeTotal" readonly>
                        </div>
                    </div>

                    @if (isset($cxc) && $cxc->accountsReceivable_movement == 'Factura')
                        <div class="col-md-2 contenedoresImporte">
                            <div class="form-group">
                                <label for="IVAISR" class="negrita">IVA + ISR:</label>
                                <?php
                                $isrIva = 0;
                                if (isset($cxc)) {
                                    $retencionISR = $cxc['accountsReceivable_retentionISR'] !== null || $cxc['accountsReceivable_retentionISR'] != 0.0 ? (float) $cxc['accountsReceivable_retentionISR'] : 0;
                                    $retencionIVA = $cxc['accountsReceivable_retentionIVA'] !== null || $cxc['accountsReceivable_retentionIVA'] != 0.0 ? (float) $cxc['accountsReceivable_retentionIVA'] : 0;
                                    $isrIva = $retencionISR + $retencionIVA;
                                }
                                ?>
                                <input type="text" class="form-control"
                                    value="{{ isset($cxc) ? number_format($isrIva, 2) : null }}" name="impuesto"
                                    id="impuesto" readonly>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-12 contenedoresImporte">
                    </div>


                    <div class="col-md-4" id="saldoAnticipoDIV">
                        <div class="form-group">
                            <label for="saldo" class="negrita">Saldo</label>
                            <input type="text" class="form-control"
                                value="{{ isset($cxc) ? number_format($cxc->accountsReceivable_balance, 2) : null }}"
                                name="saldo" id="saldoAnticipo" readonly>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="saldoProveedor" class="negrita">Saldo Cliente:</label>
                            <input type="text" class="form-control" value="" name="saldoProveedor"
                                id="saldoProveedor" readonly>
                        </div>
                    </div>
                    <div class="col-md-12 espacioContenedor"></div>

                    <div class="col-md-4" style="display: none">
                        <div class="form-group">
                            <input type="text" class="form-control" id="cantidadArticulos" name="cantidadArticulos"
                                readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('identificadorCFDI', 'Identificador de CFDI', ['class' => 'negrita']) !!}
                            <select name="identificadorCFDI" id="select-basic-identificadorCFDI" readonly
                                class="widthAll select-grupo">
                                <option value>Seleccione uno...</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="observaciones" class="negrita">Observaciones:</label>
                            <input type="text" class="form-control"
                                value="{{ isset($cxc) ? $cxc->accountsReceivable_observations : null }}"
                                name="observaciones" id="observaciones">
                        </div>
                    </div>


                    <div class="col-md-6" style="display: none">
                        <label for="timbrado" class="negrita">Timbrado</label>
                        <div class="input-group form-group mb15">
                            <input type="text" class="form-control" id="timbradoKey" name="timbradoKey"
                                value="{{ isset($cxc) ? $cxc['accountsReceivable_stamped'] ?? 0 : 0 }}" />
                        </div>
                    </div>

                    <div class="col-md-6" style="display: none">
                        <label for="empresaImpuesto" class="negrita">Empresa</label>
                        <div class="input-group form-group mb15">
                            <input type="text" class="form-control" id="empresaImpuesto" name="empresaImpuesto"
                                value="{{ isset(session('company')->companies_calculateTaxes) ? session('company')->companies_calculateTaxes : null }}" />
                        </div>
                    </div>

                    <div class="col-md-12">

                        <div class="tab-content table-panel">
                            <table class="table table-striped table-bordered cxcM widthAll">
                                <thead>
                                    <tr>

                                        <th style="display: none" class="aplica">Operación a Aplicar</th>
                                        <th style="display: none" class="aplicaC">Folio de la Operación</th>
                                        <th style="display: none" class="importe">Importe</th>
                                        <th style="display: none" class="importe">Saldo Pendiente</th>
                                        <th style="display: none" class="importe">%</th>
                                        <th style="display: none" class="accion"></th>
                                        <th style="display: none">Importe Total</th>

                                        {{-- <th>Pendiente</th>
                                            <th>A recibir</th> --}}
                                    </tr>
                                </thead>

                                @if (isset($cxcDetails) && $cxcDetails->count() > 0)
                                    <tr id="controlArticulo" style="display: none">
                                        <td style="display: none" class="">
                                            <input type="text" name="movId" id="movId" />
                                        </td>
                                        <td style="display: none" class="aplica">
                                            <select name="aplicaSelect" id="aplicaSelect" class="botonesAplica">
                                                <option value="Factura">Factura</option>
                                            </select>
                                        </td>
                                        <td style="display: none" class="aplicaC" id="btnInput"><input id=""
                                                type="text" class="botonesArticulos" disabled>
                                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                                data-target=".modal4" id="agregarAplicaModal">...</button>
                                        </td>
                                        <td style="display: none; justify-content: center; align-items: center"
                                            class="importe"><input id="" type="text"
                                                class="botonesArticulos" disabled></td>
                                        <td style="display: none; justify-content: center; align-items: center"
                                            class="diferencia"><input id="" type="text"
                                                class="botonesArticulos" disabled></td>
                                        <td style="display: none; justify-content: center; align-items: center"
                                            class="porcentaje"><input id="" type="text"
                                                class="botonesArticulos" disabled></td>
                                        <td style="display: none" class="accion">

                                            <i class="fa fa-trash-o  btn-delete-articulo" aria-hidden="true"
                                                style="color: red; font-size: 25px; cursor: pointer;"></i>
                                        </td>
                                        <td style="display: none; justify-content: center; align-items: center"
                                            id="importeT-"><input id="" type="text" class="botonesArticulos"
                                                disabled></td>
                                        {{-- <td><input id="" type="text" class="botonesArticulos"
                                            disabled></td>
                                    <td><input id="" type="text" class="botonesArticulos"
                                            disabled></td> --}}

                                    </tr>
                                    <tbody id="articleItem">
                                        @foreach ($cxcDetails as $key => $cxcDetail)
                                            <tr
                                                id="{{ str_replace(' ', '', $cxcDetail['accountsReceivableDetails_apply']) . '-' . $key }}">
                                                <td style="display: none" class="">
                                                    <input type="hidden"
                                                        id="id-{{ str_replace(' ', '', $cxcDetail->accountsReceivableDetails_apply) . '-' . $key }}"
                                                        value="{{ $cxcDetail->accountsReceivableDetails_id }}" />
                                                    <input type="text" name="cxp[]"
                                                        id="movId-{{ $cxcDetail->accountsReceivableDetails_id }}"
                                                        value="{{ $cxcDetail->accountsReceivableDetails_movReference }}" />
                                                </td>
                                                <td style="display: none" class="">
                                                    <input type="text" name="cxp[]"
                                                        id="idDetalle-{{ $cxcDetail->accountsReceivableDetails_id }}"
                                                        value="{{ $cxcDetail->accountsReceivableDetails_id }}" />
                                                </td>
                                                <td style="display: none" class="aplica">
                                                    <select name="cxp[]"
                                                        id="aplicaSelect-{{ $cxcDetail->accountsReceivableDetails_id }}"
                                                        class="botonesAplica selectsAplica"
                                                        onchange="buscarMov2('{{ $cxcDetail->accountsReceivableDetails_id }}')">
                                                        <option value="{{ $cxcDetail->accountsReceivableDetails_apply }}"
                                                            selected>{{ $cxcDetail->accountsReceivableDetails_apply }}
                                                        </option>
                                                    </select>
                                                </td>
                                                <td style="display: none" class="aplicaC" id="btnInput"><input
                                                        id="aplicaC-{{ $cxcDetail->accountsReceivableDetails_id }}"
                                                        type="text" class="botonesArticulos" readonly
                                                        value="{{ $cxcDetail->accountsReceivableDetails_applyIncrement }}"
                                                        name="cxp[]">
                                                    <button type="button" class="btn btn-info btn-sm botonesAplicaModal"
                                                        data-toggle="modal" data-target=".modal4"
                                                        id="agregarAplicaModal-{{ $cxcDetail->accountsReceivableDetails_id }}">...</button>
                                                </td>
                                                <td style="display: none; justify-content: center; align-items: center"
                                                    class="importe"><input
                                                        id="importe-{{ $cxcDetail->accountsReceivableDetails_id }}"
                                                        type="text" class="botonesArticulos"
                                                        value="{{ '$' . number_format($cxcDetail->accountsReceivableDetails_amount, 2) }}"
                                                        name="cxp[]"
                                                        onchange="calcularTotal('{{ str_replace(' ', '', $cxcDetail['accountsReceivableDetails_apply']) }}','{{ $cxcDetail->accountsReceivableDetails_id }}')">
                                                </td>
                                                <td style="display: none; justify-content: center; align-items: center"
                                                    class="diferencia"><input
                                                        id="diferencia-{{ $cxcDetail->accountsReceivableDetails_id }}"
                                                        type="text" class="botonesArticulos" readonly name="cxp[]"
                                                        value="$0.00"></td>
                                                <td style="display: none; justify-content: center; align-items: center"
                                                    class="porcentaje"><input
                                                        id="porcentaje-{{ $cxcDetail->accountsReceivableDetails_id }}"
                                                        type="text" class="botonesArticulos" readonly name="cxp[]"
                                                        value="0"></td>
                                                <td style="display: none" class="accion">

                                                    <i class="fa fa-trash-o  btn-delete-articulo" aria-hidden="true"
                                                        style="color: red; font-size: 25px; cursor: pointer;"
                                                        onclick="eliminarArticulo('{{ str_replace(' ', '', $cxcDetail['accountsReceivableDetails_apply']) }}', '{{ $key }}')"></i>
                                                </td>
                                                <td style="display: none; justify-content: center; align-items: center"
                                                    id=""><input
                                                        id="importeT-{{ $cxcDetail->accountsReceivableDetails_id }}"
                                                        type="text" class="botonesArticulos" readonly name="cxp[]"
                                                        value="{{ count($ventasMov) > 0 ? $ventasMov[$cxcDetail['accountsReceivableDetails_movReference']] : '' }}">
                                                </td>
                                                <td style="display: none; justify-content: center; align-items: center"
                                                    id=""><input
                                                        id="sucursal-{{ $cxcDetail->accountsReceivableDetails_id }}"
                                                        type="text" class="botonesArticulos" readonly
                                                        value="{{ $cxcDetail->accountsReceivableDetails_branchOffice }}"
                                                        name="cxp[]"></td>
                                            </tr>
                                            <script>
                                                $("#aplicaSelect-" + '{{ $cxcDetail->accountsReceivableDetails_id }}').val(
                                                    '{{ $cxcDetail->accountsReceivableDetails_apply }}');

                                                $("#aplicaSelect-" + '{{ $cxcDetail->accountsReceivableDetails_id }}').trigger('change');

                                                function buscarMov2(clave) {

                                                    let selectChange = $("#aplicaSelect-" + clave);

                                                    let id = selectChange.attr("id").split("-")[1];

                                                    // $("#aplicaC-" + id).val("");
                                                    // $("#importe-" + id).val("");

                                                    $("#agregarAplicaModal-" + id).on("click", function(event) {
                                                        event.preventDefault();
                                                        jQuery("#shTable5").DataTable().clear().draw();

                                                        let mov = selectChange.val();

                                                        proveedor = $("#proveedorKey").val();
                                                        if (mov !== "") {
                                                            $.ajax({
                                                                url: "/aplicaFolio/cxc",
                                                                method: "GET",
                                                                data: {
                                                                    proveedor: proveedor,
                                                                    movimiento: mov,
                                                                    moneda: $("#select-search-hided").val(),
                                                                },
                                                                success: function({
                                                                    estatus,
                                                                    dataProveedor
                                                                }) {
                                                                    if (estatus === 200) {
                                                                        if (dataProveedor.length > 0) {
                                                                            tableCXPP.clear().draw();
                                                                            dataProveedor.forEach((element) => {
                                                                                let fecha =
                                                                                    (element.accountsReceivable_expiration =
                                                                                        moment(
                                                                                            element.accountsReceivable_expiration
                                                                                        ).format("YYYY-MM-DD"));
                                                                                let importe = currency(
                                                                                    element.accountsReceivable_total, {
                                                                                        separator: ",",
                                                                                        precision: 2,
                                                                                        decimal: ".",
                                                                                        symbol: "",
                                                                                    }
                                                                                ).format();

                                                                                let saldo = currency(
                                                                                    element.accountsReceivable_balance, {
                                                                                        separator: ",",
                                                                                        precision: 2,
                                                                                        symbol: "",
                                                                                    }
                                                                                ).format();

                                                                                jQuery("#shTable5")
                                                                                    .DataTable()
                                                                                    .row.add([
                                                                                        element.accountsReceivable_id,
                                                                                        element.accountsReceivable_movement,
                                                                                        element.accountsReceivable_movementID,
                                                                                        fecha,
                                                                                        importe,
                                                                                        saldo,
                                                                                        element.accountsReceivable_money,
                                                                                        element.accountsReceivable_branchOffice,
                                                                                    ])
                                                                                    .draw();
                                                                            });
                                                                        } else {
                                                                            jQuery("#shTable5").DataTable().clear().draw();
                                                                        }
                                                                    }
                                                                },
                                                            });
                                                        }
                                                    });
                                                }
                                            </script>
                                        @endforeach
                                    </tbody>
                                @else
                                    <tbody id="articleItem">
                                        <tr id="controlArticulo">
                                            <td style="display: none" class="">
                                                <input type="text" name="movId" id="movId" />
                                            </td>
                                            <td style="display: none" class="aplica">
                                                <select name="aplicaSelect" id="aplicaSelect" class="botonesAplica">
                                                    <option value="Factura">Factura</option>

                                                </select>
                                            </td>
                                            <td style="display: none" class="aplicaC"><input id=""
                                                    type="text" class="botonesArticulos" disabled>
                                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                                    data-target=".modal4" id="agregarAplicaModal">...</button>
                                            </td>
                                            <td style="display: none; justify-content: center; align-items: center"
                                                class="importe"><input id="" type="text"
                                                    class="botonesArticulos" disabled></td>
                                            <td style="display: none; justify-content: center; align-items: center"
                                                class="diferencia"><input id="" type="text"
                                                    class="botonesArticulos" disabled></td>
                                            <td style="display: none; justify-content: center; align-items: center"
                                                class="porcentaje"><input id="" type="text"
                                                    class="botonesArticulos" disabled></td>
                                            <td style="display: none" class="accion">

                                                <i class="fa fa-trash-o  btn-delete-articulo" aria-hidden="true"
                                                    style="color: red; font-size: 25px; cursor: pointer;"></i>
                                            </td>
                                            <td style="display: none; justify-content: center; align-items: center"
                                                id="importeT-"><input id="" type="text"
                                                    class="botonesArticulos" disabled></td>
                                            {{-- <td><input id="" type="text" class="botonesArticulos"
                                                    disabled></td>
                                            <td><input id="" type="text" class="botonesArticulos"
                                                    disabled></td> --}}

                                        </tr>
                                    </tbody>
                                @endif
                            </table>

                            <div class="col-md-3 pull-right">
                                <div class="input-group input-group-sm" id="total-input" style="display: none">
                                    <span class="input-group-addon">Total</span>
                                    <div style="display: flex">
                                        <input type="text" class="form-control" id="totalCompleto"
                                            name="totalCompleto" readonly
                                            value="{{ isset($cxc) ? number_format($cxc->accountsReceivable_total, 2) : null }}">
                                        <button type="button" class="btn btn-info" id="btn-importe">Importe</button>
                                    </div>

                                </div>

                            </div>



                        </div>
                    </div><!-- panel -->



                </div>

                <div class="col-md-1 pull-left">
                    <div class="input-group input-group-sm mt5" style="margin-left: -5px">
                        @if (isset($cxc) ? $cxc['accountsReceivable_status'] === 'INICIAL' : 'INICIAL')
                            <span class="label label-default" id="status">INICIAL</span>
                        @elseif($cxc['accountsReceivable_status'] === 'POR AUTORIZAR')
                            <span class="label label-warning" id="status">POR AUTORIZAR</span>
                        @elseif($cxc['accountsReceivable_status'] === 'FINALIZADO')
                            <span class="label label-success" id="status">FINALIZADO</span>
                        @elseif($cxc['accountsReceivable_status'] === 'CANCELADO')
                            <span class="label label-danger" id="status">CANCELADO</span>
                        @endif

                    </div>
                </div>

                <div class="col-md-12 mt20 display-right" id="botonForm">
                    {!! Form::submit('Crear/Guardar', ['class' => 'btn btn-success']) !!}
                </div>



                <input type="hidden" id="inputDataCxp" readonly>

                <input type="text" id="inputDataArticlesDelete" name="dataArticulosDelete" readonly hidden />

                <input type="text" id="dataFacturaInfo" name="dataFacturaInfo" readonly hidden />
                {!! Form::close() !!}

            </div>
        </div>
    </div>

    <div class="modal fade bd-example-modal-lg modal1" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="cuentaModalVentana">
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
                                    <th>Clave</th>
                                    <th>Moneda</th>
                                    <th style="display: none">Cuenta</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($moneyAccounts as $moneyAccount)
                                    <tr>
                                        <td>{{ $moneyAccount->moneyAccounts_key }}</td>
                                        <td>{{ $moneyAccount->moneyAccounts_money }}</td>
                                        <td style="display: none">{{ $moneyAccount->moneyAccounts_accountType }}</td>

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


    <div class="modal fade bd-example-modal-lg modal2" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="proveedorModalVentana">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Lista de clientes</h5>

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


    <div class="modal fade bd-example-modal-lg modal3" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="aplicaModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Lista de Movimientos</h5>

                </div>
                <div class="modal-body">
                    <div class="panel table-panel">
                        <table id="shTable4" class="table table-striped table-bordered widthAll">
                            <thead>
                                <tr>
                                    <th>Movimiento</th>
                                    <th>Estatus</th>
                                    <th>Origen</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($aplica as $aplicacion)
                                    <tr>
                                        <td>{{ $aplicacion->accountsReceivable_movement }}</td>
                                        <td>{{ $aplicacion->accountsReceivable_status }}</td>
                                        <td>{{ $aplicacion->accountsReceivable_originType }}</td>
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

    <div class="modal fade bd-example-modal-lg modal4" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="aplica-Modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Lista de Movimientos</h5>

                </div>
                <div class="modal-body">
                    <input type="hidden" id="fila-id" value="">
                    <div class="panel table-panel">
                        <table id="shTable5" class="table table-striped table-bordered widthAll">
                            <thead>
                                <tr>
                                    <th style="display: none">ID Movimiento</th>
                                    <th>Movimiento</th>
                                    <th>Consecutivo</th>
                                    <th>Fecha de Vencimiento</th>
                                    <th>Importe Total</th>
                                    <th>Saldo</th>
                                    <th>Moneda</th>
                                    <th>Sucursal</th>
                                </tr>
                            </thead>

                            <tbody id="limpiarModal">
                                <!-- Llenamos la informacion por medio de ajax  -->
                            </tbody>
                        </table>
                    </div><!-- panel -->
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal"
                        id="agregarAplica">Agregar</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modalAfectar" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center" id="exampleModalCenterTitle">
                        {{-- Prueba --}}
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group container-respuesta">
                                    <input type="radio" id="pagoRadio" name="accionAfectar"
                                        value="Generar Cobro de Facturas" checked>
                                    <label>Generar Cobro de Facturas</label>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="modal-footer display-center">
                    <button type="button" class="btn btn-primary" id="btn-modal-afectar">Aceptar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAfectar2" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center" id="exampleModalCenterTitle2">
                        {{-- Prueba --}}
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-group container-respuesta">
                                    <input type="radio" id="aplicacionRadio" name="accionAfectar2"
                                        value="Generar Aplicacion" checked>
                                    <label>Generar Aplicación</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group container-respuesta">
                                    <input type="radio" id="devolucionRadio" name="accionAfectar2"
                                        value="Generar Devolución de Anticipo">
                                    <label>Generar Devolución de Anticipo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer display-center">
                    <button type="button" class="btn btn-primary" id="btn-modal-afectar2">Aceptar</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade bd-example-modal-lg 7" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="modalAnticipos">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Lista de anticipos</h5>

                </div>
                <div class="modal-body">
                    <div class="panel table-panel">
                        <table id="shTable6" class="table table-striped table-bordered widthAll">
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Movimiento</th>
                                    <th>Estatus</th>
                                    <th>Saldo</th>
                                    <th>Cuenta</th>
                                </tr>
                            </thead>

                            <tbody>
                                @if (isset($anticipos))
                                    @foreach ($anticipos as $anticipo)
                                        <tr>
                                            <td>{{ $anticipo->accountsReceivable_movementID }}</td>
                                            <td>{{ $anticipo->accountsReceivable_movement }}</td>
                                            <td>{{ $anticipo->accountsReceivable_status }}</td>
                                            <td>{{ number_format($anticipo->accountsReceivable_balance, 2) }}</td>
                                            <td>{{ $anticipo->accountsReceivable_moneyAccount }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div><!-- panel -->
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal"
                        id="agregaAnticipo">Aceptar</button>
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
                    <h5 class="modal-title" id="exampleModalLongTitle2">

                    </h5>

                </div>
                <div class="modal-body">
                    <div class="panel table-panel">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="rdio rdio-primary">
                                    <input type="radio" name="radioFactura" value="2" id="radioPrimary" checked>
                                    <label for="radioPrimary" id="labelCXCNormal">

                                    </label>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="rdio rdio-warning">
                                    <input type="radio" name="radioFactura" value="3" id="radioWarning">
                                    <label for="radioWarning" id="labelCXCRelacionado">

                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 facturaRelacion" style="display: none">
                            <div class="form-group">
                                <label for="facturaRelacion" class="negrita" id="labelCXCRelacion">
                                </label>
                                <select name="facturaRelacion" id="select-facturaRelacion" class="widthAll">
                                    <option value="" selected aria-disabled="true">Selecciona una opción</option>
                                </select>
                            </div>
                        </div>

                    </div><!-- panel -->
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal"
                        id="facturaInfo-aceptar">Aceptar</button>
                    <button type="button" class="btn btn-success" data-dismiss="modal" id="facturaInfo-aceptar2"
                        style="display: none">Aceptar</button>
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
                        value="{{ isset($cxc) ? (isset($primerFlujodeCXC) ? $primerFlujodeCXC : '') : '' }}" />
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

    <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"
        id="cancelarFacturaModal">
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
                                            <label for="motivoCancelacion" class="negrita">Motivo de Cancelación:</label>
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
                                            <label for="folioSustitucion" class="negrita">Folio de Sustitución:</label>
                                            <select name="folioSustitucion" id="select-folioSustitucion" class="widthAll"
                                                disabled>
                                                <option value="" selected aria-disabled="true">Selecciona una opción
                                                </option>
                                                @foreach ($cxcTimbradas as $factura)
                                                    <option value="{{ $factura->cfdi_UUID }}">
                                                        {{ $factura->accountsReceivable_movement }}
                                                        {{ $factura->accountsReceivable_movementID }}</option>
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

    <div class="modal fade bd-example-modal-lg modal7" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="informacionProveedorModal" style="display: ">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Información Cliente</h5>
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
                                                    <label for="articuloCostoPromedio" class="negrita">Nombre:</label>
                                                    <input type="text" readonly class="form-control input-sm"
                                                        value="{{ isset($infoProveedor) ? $infoProveedor->customers_businessName : '' }}"
                                                        name="infNombreProveedor" id="infNombreProveedor">
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="articuloCostoPromedio" class="negrita">Dirección:</label>
                                                    <input type="text" readonly class="form-control input-sm"
                                                        value="{{ isset($infoProveedor) ? $infoProveedor->customers_addres . ' x ' . $infoProveedor->customers_roads . ' No. Ext. ' . $infoProveedor->customers_outdoorNumber . ' Colonia: ' . $infoProveedor->customers_colonyFractionation : '' }}"
                                                        name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="articuloCostoPromedio" class="negrita">Telefono:</label>
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
                                                    <label for="articuloCostoPromedio" class="negrita">Categoria:</label>
                                                    <input type="text" readonly class="form-control input-sm"
                                                        value="{{ isset($infoProveedor) ? $infoProveedor->customers_category : '' }}"
                                                        name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="articuloCostoPromedio" class="negrita">Grupo:</label>
                                                    <input type="text" readonly class="form-control input-sm"
                                                        value="{{ isset($infoProveedor) ? $infoProveedor->customers_group : '' }}"
                                                        name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="articuloCostoPromedio" class="negrita">Tipo:</label>
                                                    <input type="text" readonly class="form-control input-sm"
                                                        value="{{ isset($infoProveedor) ? $infoProveedor->customers_type : '' }}"
                                                        name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="articuloCostoPromedio" class="negrita">Condición:</label>
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

                                        <div class="col-md-6">
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


    @include('include.mensaje')
    <script>
        let calculoImpuestos = '{{ session('company')->companies_calculateTaxes }}';
        let contadorArticulosString = '{{ isset($cxcDetails) ? count($cxcDetails) : 0 }}';
        // console.log(contadorArticulosString);
        let contadorArticulos = parseInt(contadorArticulosString);
        // console.log(contadorArticulos);
        jQuery('#cantidadArticulos').val(contadorArticulos);
        $('#select-moduleConcept').val('{{ isset($cxc) ? $cxc->accountsReceivable_concept : '' }}').trigger(
            'change.select2');
    </script>
    <script src="{{ asset('js/PROCESOS/cxc.js') }}"></script>
    <script>
        jQuery(document).ready(function() {
            $('#select-proveedorFormaPago').val('{{ isset($cxc) ? $cxc->accountsReceivable_formPayment : '' }}')
                .trigger(
                    'change.select2');



            let valor = $("#importe").val();

            $("#totalCompleto").val(valor);

            $("#proveedorKey").keyup(async () => {
                let isEmpty = $("#proveedorKey").val() == "";
                let cfdiGuardado =
                    "{{ isset($cxc) ? $cxc['accountsReceivable_CFDI'] : '' }}";
                let existe = false;

                if (!isEmpty) {
                    await $.ajax({
                        url: "/cfdi/regimen",
                        method: "GET",
                        data: {
                            cliente: $("#proveedorKey").val(),
                        },
                        success: ({
                            status,
                            data,
                            cfdi
                        }) => {

                            $("#select-basic-identificadorCFDI").children().remove();
                            if (status == 200) {
                                // console.log(data, cfdi);
                                $("#select-basic-identificadorCFDI").append(
                                    `<option value>Seleccione Uno ...</option>`
                                );
                                data.forEach(element => {
                                    $("#select-basic-identificadorCFDI").append(
                                        `<option value="${element.claveCFDI}">${element.descripcion} - ${element.claveCFDI}</option>`
                                    );

                                    if (element.claveCFDI ===
                                        cfdiGuardado
                                    ) {
                                        existe = true;
                                    }
                                });

                                if (existe) {
                                    $('#select-basic-identificadorCFDI').val(
                                        '{{ isset($cxc) ? $cxc['accountsReceivable_CFDI'] : '' }}'
                                    ).trigger(
                                        'change.select2');
                                    $("#select-basic-identificadorCFDI").attr("readonly",
                                        true);
                                }
                                if (!existe) {

                                    //si el cliente ya tiene un cfdi configurado lo mostramos en el select

                                    if (cfdi !== null) {
                                        $('#select-basic-identificadorCFDI').val(
                                            "S01"
                                        ).trigger(
                                            'change.select2');
                                        //agregar el readonly
                                        $("#select-basic-identificadorCFDI").attr(
                                            "readonly", true);
                                    } else {
                                        $('#select-basic-identificadorCFDI').val("S01")
                                            .trigger(
                                                'change.select2');
                                        $("#select-basic-identificadorCFDI").attr(
                                            "readonly", true);
                                    }

                                }



                            } else {
                                swal({
                                    title: "REGIMEN FISCAL NO CONFIGURADO",
                                    text: "El cliente no tiene configurado un regimen fiscal",
                                    icon: "error",
                                });

                                $("#select-basic-identificadorCFDI").append(
                                    `<option value>Seleccione Uno ...</option>`
                                );
                                $('#select-basic-identificadorCFDI').val("")
                                    .trigger(
                                        'change.select2');
                            }
                        }
                    })
                } else {
                    $("#select-basic-identificadorCFDI").children().remove();
                    $("#select-basic-identificadorCFDI").append(
                        `<option value>Seleccione Uno ...</option>`
                    );
                    $('#select-basic-identificadorCFDI').val("")
                        .trigger(
                            'change.select2');
                }

            });

            if ($("#select-moduleConcept").val() !== '') {
                $("#proveedorKey").keyup();
            }

        });
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
