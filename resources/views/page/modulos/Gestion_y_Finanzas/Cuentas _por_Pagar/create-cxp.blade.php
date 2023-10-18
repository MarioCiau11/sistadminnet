@extends('layouts.layout')

@section('content')
    @foreach (auth()->user()->getAllPermissions()->where('categoria', '=', 'Cuentas por pagar')->pluck('name')->toArray() as $permisos)
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
                    'route' => ['modulo.cuentasPagar.store-cxp'],
                    'id' => 'progressWizard',
                    'class' => 'panel-wizard',
                    'method' => 'POST',
                ]) !!}
                {{-- <input type="hidden" style="display: none" value="{{ isset($cxp) ? $cxp->accountsPayable_id : 0 }}"
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
                        <p class="titulo text-left">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
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


                            @if (!isset($cxp))
                                @can('Afectar')
                                    <li><a href="#" id="afectar-boton"> Afectar <span
                                                class="glyphicon glyphicon-play pull-right"></span></a></li>
                                @endcan


                                <li><a href="#" id="eliminar-boton">Eliminar <span
                                            class="glyphicon glyphicon-remove-circle pull-right"></span></a></li>
                            @else
                                @if ($cxp->accountsPayable_status !== 'FINALIZADO' && $cxp->accountsPayable_movement !== 'Sol. de Cheque/Transferencia')
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
                            @if (isset($cxp) && $cxp->accountsPayable_status !== 'INICIAL')
                                <li><a href="{{ route('vista.modulo.cuentas.reportes', ['idCXP' => $cxp['accountsPayable_id']]) }}"
                                        target="_blank">Reporte <span
                                            class="glyphicon glyphicon-list-alt pull-right"></span></a>
                                </li>
                            @endif

                            @if (isset($cxp))
                                @can('Afectar')
                                    <li>
                                        <a href="#" id="copiar-compra">Copiar <span
                                                class="fa fa-copy pull-right"></span></a>
                                    </li>
                                @endcan
                            @endif

                            @if (isset($cxp))
                                <li class="divider"></li>
                                <li><a href="{{ route('vista.modulo.cuentasPagar.create-cxp') }}"
                                        id="nuevo-boton">Nuevo<span class="fa fa-file-o pull-right"></span></a></li>
                                <li><a href="{{ route('vista.modulo.cuentasPagar.anexos', ['id' => $cxp->accountsPayable_id]) }}"
                                        id="anexos-boton">Anexos <span
                                            class="glyphicon glyphicon-paperclip pull-right"></span></a></li>
                                <li><a href="" data-toggle="modal" data-target="#ModalFlujo">
                                        Ver flujo
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
                            {!! Form::select('movimientos', $movimientos, isset($cxp) ? $cxp->accountsPayable_movement : null, [
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
                                value="{{ isset($cxp) ? $cxp['accountsPayable_id'] : 0 }}"readonly>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="folio" class="negrita">Folio:</label>
                            <input type="number" class="form-control" name="folio"
                                value="{{ isset($cxp) ? $cxp->accountsPayable_movementID : '' }}" readonly id="folioMov" />
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
                            <label for="fechaEmision" class="negrita">Fecha</label>
                            <input type="date" class="form-control input-date" name="fechaEmision" id="fechaEmision"
                                placeholder="Fecha Emisión"
                                value="{{ isset($cxp) ? $cxp->accountsPayable_issuedate : $fecha_actual }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::labelValidacion('nameMoneda', 'Moneda', 'negrita') !!}
                            {!! Form::select(
                                'nameMoneda',
                                $selectMonedas,
                                isset($cxp) ? $cxp->accountsPayable_money : $parametro->generalParameters_defaultMoney,
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
                                isset($cxp) ? floatVal($cxp->accountsPayable_typeChange) : floatVal($parametro->money_change),
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

                    <div class="col-md-4">
                        <label for="proveedor" class="negrita">Proveedor/Acreedor<span class='asterisk'>
                                *</span></label>
                        <div class="input-group form-group mb15">
                            <input type="text" class="form-control" id="proveedorKey" name="proveedorKey"
                                value="{{ isset($cxp) ? $cxp->accountsPayable_provider : null }}" />
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" data-toggle="modal" data-target=".modal2"
                                    id="proveedorModal">...</button>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="proveedorName" class="negrita">Nombre/Razón Social del Proveedor</label>
                            <input type="text" class="form-control" name="proveedorName" id="proveedorName"
                                value="" readonly>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="referencia" class="negrita">Notas/Folio Factura</label>
                            <input type="text" class="form-control"
                                value="{{ isset($cxp) ? $cxp->accountsPayable_reference : null }}" name="referencia"
                                id="referencia">
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
                                value="{{ isset($cxp) ? $cxp->accountsPayable_moneyAccount : '' }}" readonly
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
                                value="{{ isset($cxp) ? '$' . number_format($cxp->accountsPayable_amount, 2) : null }}"
                                name="importe" id="importe">
                        </div>
                    </div>

                    <div class="col-md-4" id="anticipoAplicar">
                        <label for="anticipo" class="negrita">Anticipo/NC a Aplicar<span class='asterisk'>
                                *</span></label>
                        <div class="input-group form-group mb15">
                            <input type="text" class="form-control" id="anticiposKey" name="anticiposKey" readonly
                                value="{{ isset($cxp) ? $cxp->accountsPayable_origin . ' ' . $cxp->accountsPayable_originID : null }}" />
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" data-toggle="modal" data-target=".modal6"
                                    id="anticiposModal">...</button>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4 contenedoresImporte">
                        <div class="form-group">
                            <label for="impuesto" class="negrita">Impuesto:</label>
                            <input type="text" class="form-control"
                                value="{{ isset($cxp) ? number_format($cxp->accountsPayable_taxes, 2) : null }}"
                                name="impuesto" id="impuesto">
                        </div>
                    </div>
                    <div class="col-md-4 contenedoresImporte">
                        <div class="form-group">
                            <label for="importeTotal" class="negrita">Importe Total:</label>
                            <input type="text" class="form-control"
                                value="{{ isset($cxp) ? number_format($cxp->accountsPayable_total, 2) : null }}"
                                name="importeTotal" id="importeTotal" readonly>
                        </div>
                    </div>

                    <div class="col-md-4" id="saldoAnticipoDIV">
                        <div class="form-group">
                            <label for="saldo" class="negrita">Saldo</label>
                            <input type="text" class="form-control"
                                value="{{ isset($cxp) ? number_format($cxp->accountsPayable_balance, 2) : null }}"
                                name="saldo" id="saldoAnticipo" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="saldoProveedor" class="negrita">Saldo Proveedor:</label>
                            <input type="text" class="form-control" value="" name="saldoProveedor"
                                id="saldoProveedor" readonly>
                        </div>
                    </div>


                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="observaciones" class="negrita">Observaciones:</label>
                            <input type="text" class="form-control"
                                value="{{ isset($cxp) ? $cxp->accountsPayable_observations : null }}"
                                name="observaciones" id="observaciones">
                        </div>
                    </div>
                    <div class="col-md-12 espacioContenedor"></div>

                    <div class="col-md-4" style="display: none">
                        <div class="form-group">
                            <input type="text" class="form-control" id="cantidadArticulos" name="cantidadArticulos"
                                readonly>
                        </div>
                    </div>



                    <div class="col-md-12">

                        <div class="tab-content table-panel">
                            <table class="table table-striped table-bordered  cxpM widthAll">
                                <thead>
                                    <tr>

                                        <th style="display: none" class="aplica">Operación a Aplicar</th>
                                        <th style="display: none" class="aplicaC">Folio de la Operación</th>
                                        <th style="display: none" class="importe">Importe a Aplicar</th>
                                        <th style="display: none" class="importe">Saldo Pendiente</th>
                                        <th style="display: none" class="importe">%</th>
                                        <th style="display: none" class="accion"></th>
                                        <th style="display: none">Importe Total</th>

                                        {{-- <th>Pendiente</th>
                                            <th>A recibir</th> --}}
                                    </tr>
                                </thead>

                                @if (isset($cxpDetails) && $cxpDetails->count() > 0)
                                    <tr id="controlArticulo" style="display: none">
                                        <td style="display: none" class="">
                                            <input type="text" name="movId" id="movId" />
                                        </td>
                                        <td style="display: none" class="aplica">
                                            <select name="aplicaSelect" id="aplicaSelect" class="botonesAplica">
                                                <option value="Entrada por Compra">Entrada por Compra</option>
                                                <option value="Factura de Gasto">Factura de Gasto</option>
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
                                        @foreach ($cxpDetails as $key => $cxpDetail)
                                            <tr
                                                id="{{ str_replace(' ', '', $cxpDetail['accountsPayableDetails_apply']) . '-' . $key }}">
                                                <td style="display: none" class="">
                                                    <input type="hidden"
                                                        id="id-{{ str_replace(' ', '', $cxpDetail->accountsPayableDetails_apply) . '-' . $key }}"
                                                        value="{{ $cxpDetail->accountsPayableDetails_id }}" />
                                                    <input type="text" name="cxp[]"
                                                        id="movId-{{ $cxpDetail->accountsPayableDetails_id }}"
                                                        value="{{ $cxpDetail->accountsPayableDetails_movReference }}" />
                                                </td>
                                                <td style="display: none" class="">
                                                    <input type="text" name="cxp[]"
                                                        id="idDetalle-{{ $cxpDetail->accountsPayableDetails_id }}"
                                                        value="{{ $cxpDetail->accountsPayableDetails_id }}" />
                                                </td>
                                                <td style="display: none" class="aplica">
                                                    <select name="cxp[]"
                                                        id="aplicaSelect-{{ $cxpDetail->accountsPayableDetails_id }}"
                                                        class="botonesAplica selectsAplica"
                                                        onchange="buscarMov2('{{ $cxpDetail->accountsPayableDetails_id }}')">
                                                    </select>
                                                </td>
                                                <td style="display: none" class="aplicaC"><input
                                                        id="aplicaC-{{ $cxpDetail->accountsPayableDetails_id }}"
                                                        type="text" class="botonesArticulos" readonly
                                                        value="{{ $cxpDetail->accountsPayableDetails_applyIncrement }}"
                                                        name="cxp[]">
                                                    <button type="button" class="btn btn-info btn-sm botonesAplicaModal"
                                                        data-toggle="modal" data-target=".modal4"
                                                        id="agregarAplicaModal-{{ $cxpDetail->accountsPayableDetails_id }}">...</button>
                                                </td>
                                                <td style="display: none; justify-content: center; align-items: center"
                                                    class="importe"><input
                                                        id="importe-{{ $cxpDetail->accountsPayableDetails_id }}"
                                                        type="text" class="botonesArticulos"
                                                        value="{{ '$' . number_format($cxpDetail->accountsPayableDetails_amount, 2) }}"
                                                        name="cxp[]"
                                                        onchange="calcularTotal('{{ str_replace(' ', '', $cxpDetail['accountsPayableDetails_apply']) }}','{{ $cxpDetail->accountsPayableDetails_id }}')">
                                                </td>
                                                <td style="display: none; justify-content: center; align-items: center"
                                                    class="diferencia"><input
                                                        id="diferencia-{{ $cxpDetail->accountsPayableDetails_id }}"
                                                        type="text" class="botonesArticulos" readonly name="cxp[]"
                                                        value="$0.00"></td>
                                                <td style="display: none; justify-content: center; align-items: center"
                                                    class="porcentaje"><input
                                                        id="porcentaje-{{ $cxpDetail->accountsPayableDetails_id }}"
                                                        type="text" class="botonesArticulos" readonly name="cxp[]"
                                                        value="0"></td>
                                                <td style="display: none" class="accion">

                                                    <i class="fa fa-trash-o  btn-delete-articulo" aria-hidden="true"
                                                        style="color: red; font-size: 25px; cursor: pointer;"
                                                        onclick="eliminarArticulo('{{ str_replace(' ', '', $cxpDetail['accountsPayableDetails_apply']) }}', '{{ $key }}')"></i>
                                                </td>
                                                <td style="display: none; justify-content: center; align-items: center"
                                                    id=""><input
                                                        id="importeT-{{ $cxpDetail->accountsPayableDetails_id }}"
                                                        type="text" class="botonesArticulos" readonly name="cxp[]"
                                                        value="{{ count($comprasMov) > 0 ? $comprasMov[$cxpDetail['accountsPayableDetails_movReference']] : '' }}">
                                                </td>
                                                <td style="display: none; justify-content: center; align-items: center"
                                                    id=""><input
                                                        id="sucursal-{{ $cxpDetail->accountsPayableDetails_id }}"
                                                        type="text" class="botonesArticulos" readonly
                                                        value="{{ $cxpDetail->accountsPayableDetails_branchOffice }}"
                                                        name="cxp[]"></td>
                                            </tr>
                                            <script>
                                                if ('{{ $cxpDetail->accountsPayableDetails_apply }}' === "Entrada por Compra") {
                                                    $("#aplicaSelect-" + '{{ $cxpDetail->accountsPayableDetails_id }}').append(`<option value="{{ $cxpDetail->accountsPayableDetails_apply }}"  selected>{{ $cxpDetail->accountsPayableDetails_apply }}</option>
                                <option value="Factura de Gasto">Factura de Gasto</option>`);
                                                } else {
                                                    $("#aplicaSelect-" + '{{ $cxpDetail->accountsPayableDetails_id }}').append(
                                                        `<option value="Entrada por Compra">Entrada por Compra</option>
                                <option value="{{ $cxpDetail->accountsPayableDetails_apply }}"  selected>{{ $cxpDetail->accountsPayableDetails_apply }}</option>`
                                                    );
                                                }
                                                $("#aplicaSelect-" + '{{ $cxpDetail->accountsPayableDetails_id }}').val(
                                                    '{{ $cxpDetail->accountsPayableDetails_apply }}');

                                                $("#aplicaSelect-" + '{{ $cxpDetail->accountsPayableDetails_id }}').trigger('change');

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
                                                                url: "/aplicaFolio",
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
                                                                                    (element.accountsPayable_expiration =
                                                                                        moment(
                                                                                            element.accountsPayable_expiration
                                                                                        ).format("YYYY-MM-DD"));
                                                                                let importe = currency(
                                                                                    element.accountsPayable_total, {
                                                                                        separator: ",",
                                                                                        precision: 2,
                                                                                        decimal: ".",
                                                                                        symbol: "",
                                                                                    }
                                                                                ).format();

                                                                                let saldo = currency(
                                                                                    element.accountsPayable_balance, {
                                                                                        separator: ",",
                                                                                        precision: 2,
                                                                                        symbol: "",
                                                                                    }
                                                                                ).format();

                                                                                jQuery("#shTable5")
                                                                                    .DataTable()
                                                                                    .row.add([
                                                                                        element.accountsPayable_id,
                                                                                        element.accountsPayable_movement,
                                                                                        element.accountsPayable_movementID,
                                                                                        fecha,
                                                                                        importe,
                                                                                        saldo,
                                                                                        element.accountsPayable_money,
                                                                                        element
                                                                                        .accountsPayable_branchOffice,
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

                                                    $("selectMovSucursal").on("change", function() {
                                                        let mov = selectChange.val();
                                                        proveedor = $("#proveedorKey").val();
                                                        sucursal = $("#selectMovSucursal").val()
                                                        if (mov !== "") {
                                                            $.ajax({
                                                                url: "/aplicaFolio",
                                                                method: "GET",
                                                                data: {
                                                                    proveedor: proveedor,
                                                                    sucursal: sucursal,
                                                                    movimiento: mov,
                                                                    moneda: $("#select-search-hided").val(),
                                                                },
                                                                success: function({
                                                                    estatus,
                                                                    dataProveedor
                                                                }) {
                                                                    console.log(dataProveedor);
                                                                    if (estatus === 200) {
                                                                        if (dataProveedor.length > 0) {
                                                                            tableCXPP.clear().draw();
                                                                            dataProveedor.forEach((element) => {
                                                                                let fecha =
                                                                                    (element.accountsPayable_expiration =
                                                                                        moment(
                                                                                            element.accountsPayable_expiration
                                                                                        ).format("YYYY-MM-DD"));
                                                                                let importe = currency(
                                                                                    element.accountsPayable_total, {
                                                                                        separator: ",",
                                                                                        precision: 2,
                                                                                        decimal: ".",
                                                                                        symbol: "",
                                                                                    }
                                                                                ).format();

                                                                                let saldo = currency(
                                                                                    element.accountsPayable_balance, {
                                                                                        separator: ",",
                                                                                        precision: 2,
                                                                                        symbol: "",
                                                                                    }
                                                                                ).format();

                                                                                jQuery("#shTable5")
                                                                                    .DataTable()
                                                                                    .row.add([
                                                                                        element.accountsPayable_id,
                                                                                        element.accountsPayable_movement,
                                                                                        element.accountsPayable_movementID,
                                                                                        fecha,
                                                                                        importe,
                                                                                        saldo,
                                                                                        element.accountsPayable_money,
                                                                                        element.accountsPayable_branchOffice,
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
                                                    <option value="Entrada por Compra">Entrada por Compra</option>
                                                    <option value="Factura de Gasto">Factura de Gasto</option>
                                                </select>
                                            </td>
                                            <td style="display: none" class="aplicaC" id="btnInput"><input
                                                    id="" type="text" class="botonesArticulos" disabled>
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
                                            value="{{ isset($cxp) ? number_format($cxp->accountsPayable_total, 2) : null }}">
                                        <button type="button" class="btn btn-info" id="btn-importe">Importe</button>
                                    </div>

                                </div>

                            </div>



                        </div>
                    </div><!-- panel -->



                </div>

                <div class="col-md-1 pull-left">
                    <div class="input-group input-group-sm mt5" style="margin-left: -5px">
                        @if (isset($cxp) ? $cxp['accountsPayable_status'] === 'INICIAL' : 'INICIAL')
                            <span class="label label-default" id="status">INICIAL</span>
                        @elseif($cxp['accountsPayable_status'] === 'POR AUTORIZAR')
                            <span class="label label-warning" id="status">POR AUTORIZAR</span>
                        @elseif($cxp['accountsPayable_status'] === 'FINALIZADO')
                            <span class="label label-success" id="status">FINALIZADO</span>
                        @elseif($cxp['accountsPayable_status'] === 'CANCELADO')
                            <span class="label label-danger" id="status">CANCELADO</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-12 mt20 display-right" id="botonForm">
                    {!! Form::submit('Crear/Guardar', ['class' => 'btn btn-success']) !!}
                </div>



                <input type="hidden" id="inputDataCxp" readonly>

                <input type="text" id="inputDataArticlesDelete" name="dataArticulosDelete" readonly hidden />
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
                                        <td>{{ $aplicacion->accountsPayable_movement }}</td>
                                        <td>{{ $aplicacion->accountsPayable_status }}</td>
                                        <td>{{ $aplicacion->accountsPayable_originType }}</td>
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
                <div class="modal-header" style="display: flex; justify-content: space-between">
                    <div class="col-md-7">
                        <h5 class="modal-title" id="exampleModalLongTitle">Lista de Movimientos</h5>
                    </div>

                    <div class="col-md-5">
                        <select name="sucursalesMovEmpresa" id="selectMovSucursal"
                            value="{{ session('sucursal')->branchOffices_key }}" class="widthAll">
                            <option value="Todos">Todos</option>
                            @foreach ($empresaSucursales as $empresaSucursal)
                                <option value="{{ $empresaSucursal->branchOffices_key }}"
                                    {{ session('sucursal')->branchOffices_key === $empresaSucursal->branchOffices_key ? 'selected' : '' }}>
                                    {{ $empresaSucursal->branchOffices_key . ' - ' . $empresaSucursal->branchOffices_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

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
                                        value="Generar Pago de Facturas" checked>
                                    <label>Generar Pago de Facturas</label>

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

    <div class="modal fade bd-example-modal-lg modal6" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
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
                                            <td>{{ $anticipo->accountsPayable_movementID }}</td>
                                            <td>{{ $anticipo->accountsPayable_movement }}</td>
                                            <td>{{ $anticipo->accountsPayable_status }}</td>
                                            <td>{{ number_format($anticipo->accountsPayable_balance, 2) }}</td>
                                            <td>{{ $anticipo->accountsPayable_moneyAccount }}</td>
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
                        value="{{ isset($cxp) ? (isset($primerFlujodeCXP) ? $primerFlujodeCXP : '') : '' }}" />
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

    <div class="modal fade bd-example-modal-lg modal9" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="informacionProveedorModal" style="display: ">
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
                        <div class="col-sm-9 col-md-12">
                            <!-- Tab panes -->
                            <div class="tab-content nopadding noborder" style="margin-top: 15px;">

                                <div class="tab-pane active" id="following2">
                                    <div class="activity-list">

                                        <div class="col-md-6">

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="articuloCostoPromedio" class="negrita">Nombre:</label>
                                                    <input type="text" readonly class="form-control input-sm"
                                                        value="{{ isset($infoProveedor) ? $infoProveedor->providers_name : '' }}"
                                                        name="infNombreProveedor" id="infNombreProveedor">
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="articuloCostoPromedio" class="negrita">Dirección:</label>
                                                    <input type="text" readonly class="form-control input-sm"
                                                        value="{{ isset($infoProveedor) ? $infoProveedor->providers_address : '' }}"
                                                        name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="articuloCostoPromedio" class="negrita">Telefono:</label>
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
                                                    <label for="articuloCostoPromedio" class="negrita">Categoria:</label>
                                                    <input type="text" readonly class="form-control input-sm"
                                                        value="{{ isset($infoProveedor) ? $infoProveedor->providers_category : '' }}"
                                                        name="articuloCostoPromedio" id="articuloCostoPromedio">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="articuloCostoPromedio" class="negrita">Grupo:</label>
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
                                            <div class="tab-content mb30" id="scrollInfoProveedor">

                                                @if (isset($monedasMov))
                                                    @foreach ($monedasMov as $moneda)
                                                        <div class="tab-pane {{ $loop->first ? 'active' : '' }}"
                                                            id="{{ trim($moneda->money_key) }}">
                                                            <div>
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
        let contadorArticulosString = '{{ isset($cxpDetails) ? count($cxpDetails) : 0 }}';
        // console.log(contadorArticulosString);
        let contadorArticulos = parseInt(contadorArticulosString);
        // console.log(contadorArticulos);
        jQuery('#cantidadArticulos').val(contadorArticulos);
    </script>
    <script src="{{ asset('js/PROCESOS/cxp.js') }}"></script>
    <script>
        $('#select-moduleConcept').val('{{ isset($cxp) ? $cxp->accountsPayable_concept : '' }}').trigger(
            'change.select2');

        $('#select-proveedorFormaPago').val('{{ isset($cxp) ? $cxp->accountsPayable_formPayment : '' }}').trigger(
            'change.select2');

        let valor = $("#importe").val();

        $("#totalCompleto").val(valor);
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
