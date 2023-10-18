@extends('layouts.layout')

@section('content')
    @foreach (auth()->user()->getAllPermissions()->where('categoria', '=', 'Tesorería')->pluck('name')->toArray() as $permisos)
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
                    'route' => ['modulo.tesoreria.store-tesoreria', 'id' => isset($tesoreria) ? $tesoreria->treasuries_id : 0],
                    'id' => 'progressWizard',
                    'class' => 'panel-wizard',
                ]) !!}

                <input type="hidden" value="{{ isset($tesoreria) ? $tesoreria->treasuries_id : 0 }}" name="id"
                    id="id" />

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


                            @can('Afectar')
                                @if (isset($tesoreria) &&
                                        $tesoreria->treasuries_status !== 'FINALIZADO' &&
                                        $tesoreria->treasuries_status !== 'CANCELADO')
                                    <li><a href="#" id="afectar-boton"> Finalizar <span
                                                class="glyphicon glyphicon-play pull-right"></span></a></li>
                                @endif

                                @if (!isset($tesoreria))
                                    <li><a href="#" id="afectar-boton"> Afectar <span
                                                class="glyphicon glyphicon-play pull-right"></span></a></li>
                                @endif
                            @endcan
                            <li><a href="#" id="eliminar-boton">Eliminar <span
                                        class="glyphicon glyphicon-remove-circle pull-right"></span></a></li>

                            @can('Cancelar')
                                @if (isset($tesoreria) && $tesoreria->treasuries_status === 'FINALIZADO')
                                    <li><a href="#" id="cancelar-boton">Cancelar <span
                                                class="glyphicon glyphicon-trash pull-right"></span></a></li>
                                @endif
                            @endcan
                            @if (isset($tesoreria) && $tesoreria->treasuries_status !== 'INICIAL')
                                <li><a href="{{ route('vista.modulo.tesoreria.reportes', ['idTesoreria' => $tesoreria['treasuries_id']]) }}"
                                        target="_blank">Reporte <span
                                            class="glyphicon glyphicon-list-alt pull-right"></span></a>
                                </li>
                            @endif

                            @if (isset($tesoreria))
                                <li>
                                    <a href="#" id="copiar-movimiento">Copiar <span
                                            class="fa fa-copy pull-right"></span></a>
                                </li>
                            @endif

                            @if (isset($tesoreria))
                                <li class="divider"></li>
                                <li><a href="{{ route('vista.modulo.tesoreria.create-tesoreria') }}"
                                        id="nuevo-boton">Nuevo<span class="fa fa-file-o pull-right"></span></a></li>
                                <li><a href="{{ route('vista.modulo.tesoreria.anexos', ['id' => $tesoreria->treasuries_id]) }}"
                                        id="anexos-boton">Anexos <span
                                            class="glyphicon glyphicon-paperclip pull-right"></span></a></li>
                                <li><a href="" data-toggle="modal" data-target="#ModalFlujo">
                                        Ver flujo
                                        <span class="glyphicon glyphicon-transfer pull-right"></span>
                                    </a></li>
                                <li><a href="" data-toggle="modal" data-target="#informacionProveedorModal">
                                        Inf. Cuentas
                                        <span class="fa fa-money pull-right"></span>
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
                            {!! Form::select('movimientos', $movimientos, isset($tesoreria) ? $tesoreria['treasuries_movement'] : null, [
                                'id' => 'select-movimiento',
                                'class' => 'widthAll select-movimiento',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-2" style="display: none">
                        <div class="form-group">
                            <label for="typeOrigin" class="negrita">typeORIGIN:</label>
                            <input type="text" class="form-control" name="typeOrigin" readonly id="typeOrigin"
                                value="{{ isset($tesoreria) ? $tesoreria['treasuries_originType'] : null }}" />
                        </div>
                    </div>


                    <div class="col-md-2" style="display: none">
                        <div class="form-group">
                            <label for="origin" class="negrita">ORIGIN:</label>
                            <input type="text" class="form-control" name="origin" readonly id="origin" />
                        </div>
                    </div>

                    <div class="col-md-2" id="folio">
                        <div class="form-group">
                            <label for="folio" class="negrita">Folio:</label>
                            <input type="number" class="form-control" id="folio" name="folio" readonly
                                value="{{ isset($tesoreria) ? $tesoreria['treasuries_movementID'] : null }}" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fechaEmision" class="negrita">Fecha</label>
                            <input type="date" class="form-control input-date" name="fechaEmision" id="fechaEmision"
                                placeholder="Fecha Emisión"
                                value="{{ isset($tesoreria) ? $tesoreria['treasuries_issuedate'] : $fecha_actual }}">
                        </div>
                    </div>

                    <div class="col-md-2" id="moneda">
                        <div class="form-group">
                            {!! Form::labelValidacion('nameMoneda', 'Moneda', 'negrita') !!}
                            {!! Form::select(
                                'nameMoneda',
                                $selectMonedas,
                                isset($tesoreria) ? $tesoreria['treasuries_money'] : $parametro->generalParameters_defaultMoney,
                                [
                                    'id' => 'select-search-hided',
                                    'class' => 'widthAll select-status',
                                ],
                            ) !!}
                        </div>
                    </div>

                    <div class="col-md-2" id="tipoCambio">
                        <div class="form-group">
                            {!! Form::labelValidacion('nameTipoCambio', 'Tipo de Cambio', 'negrita') !!}
                            {!! Form::text(
                                'nameTipoCambio',
                                isset($tesoreria) ? floatVal($tesoreria['treasuries_typeChange']) : floatVal($parametro->money_change),
                                [
                                    'class' => 'form-control',
                                    'readonly',
                                    'id' => 'nameTipoCambio',
                                ],
                            ) !!}
                        </div>
                    </div>

                    <div class="col-md-4" id="concepto">
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

                    <div class="col-md-3" id="beneficiario">
                        <label for="beneficiario" class="negrita">Cliente/Proveedor</label>
                        <div class="input-group form-group mb15">
                            <input type="text" class="form-control"
                                value="{{ isset($tesoreria) ? $tesoreria->treasuries_beneficiary : null }}"
                                name="beneficiario" id="beneficiarioInput">

                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" data-toggle="modal"
                                    data-target=".modalBeneficiario" id="beneficiarioModal" disabled>...</button>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-5" id="beneficiarioName">
                        <div class="form-group">
                            <label for="beneficiarioName" class="negrita">Nombre Cliente/Proveedor</label>
                            <input type="text" class="form-control"
                                value="{{ isset($tesoreria) ? ($tesoreria->treasuries_movement === 'Solicitud Depósito' || $tesoreria->treasuries_movement === 'Depósito' || ($tesoreria->treasuries_movement === 'Sol. de Cheque/Transferencia' && $tesoreria->treasuries_originType === 'CxC') || ($tesoreria->treasuries_movement === 'Transferencia Electrónica' && $tesoreria->treasuries_reference === 'Devolución de Anticipo') ? (isset($nameProveedor) ? $nameProveedor['customers_businessName'] : null) : (isset($nameProveedor) ? $nameProveedor['providers_name'] : null)) : null }}"
                                name="beneficiarioName" id="beneficiarioInputName" readonly>
                        </div>
                    </div>

                    <div class="col-md-4" id="contenedorFormaPago">
                        <div class="form-group">
                            <label for="formaPago" class="negrita">Forma de Pago:<span class='asterisk'>
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

                    <div class="col-md-4" id="referencias">
                        <div class="form-group">
                            <label for="proveedorReferencia" class="negrita">Notas:</label>
                            <input type="text" class="form-control"
                                value="{{ isset($tesoreria) ? $tesoreria['treasuries_reference'] : null }}"
                                name="proveedorReferencia" id="proveedorReferencia">
                        </div>
                    </div>


                    <div class="col-md-4" style="display: none" id="cuentaPago">
                        <label for="cuenta" class="negrita">Cuenta de Bancos/Efectivo<span class='asterisk'>
                                *</span></label>
                        <div class="input-group form-group mb15">
                            <input type="text" class="form-control" id="cuentaKey" name="cuentaKey"
                                value="{{ isset($tesoreria) ? $tesoreria->treasuries_moneyAccount : '' }}" readonly
                                placeholder="Selecciona una..." />

                            <input type="hidden" name="tipoCuenta"
                                value="{{ isset($tipoCuenta) ? $tipoCuenta->moneyAccounts_accountType : '' }}"
                                id="tipoCuenta" />
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" data-toggle="modal" data-target=".modal1"
                                    id="cuentaModal">...</button>
                            </span>
                        </div>
                    </div>

                    <div class="transferencia">
                        <div class="col-md-3">
                            <label for="cuenta" class="negrita">Cuenta de Bancos/Efectivo<span class='asterisk'>
                                    *</span></label>
                            <div class="input-group form-group mb15">
                                <input type="text" class="form-control" id="cuentaTrans" name="cuentaTrans"
                                    value="{{ isset($tesoreria) ? ($tesoreria->treasuries_movement === 'Egreso' ? $tesoreria->treasuries_moneyAccount : $tesoreria->treasuries_moneyAccountOrigin) : '' }}"
                                    readonly />
                                <input type="hidden" name="tipoCuentaTrans" id="tipoCuentaTrans"
                                    value="{{ isset($tipoCuenta) ? $tipoCuenta->moneyAccounts_accountType : '' }}" />
                                <input type="hidden" name="tipoCambio2"
                                    value="{{ isset($tipoCuenta2) ? $tipoCuenta2->moneyAccounts_money : '' }}"
                                    id="tipoCambio2" />
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" data-toggle="modal"
                                        data-target=".modal1" id="cuentaModal">...</button>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="claveBancaria" class="negrita">Nombre de la Cuenta de Bancos/Efectivo:</label>
                                <input type="text" class="form-control" name="claveBancaria" id="claveBancaria"
                                    value="" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4" id="importe">
                        <div class="form-group">
                            <label for="importe" class="negrita">Importe:</label>
                            <input type="text" class="form-control import"
                                value="{{ isset($tesoreria) ? '$' . number_format($tesoreria['treasuries_total'], 2) : null }}"
                                name="importe" id="importeInput">
                        </div>
                    </div>

                    <div class="transferencia">
                        <div class="col-md-12"></div>
                        <div class="col-md-3" id="cuentaD">
                            <label for="cuentaD" class="negrita">Cuenta de Bancos/Efectivo Destino<span
                                    class='asterisk'>
                                    *</span></label>
                            <div class="input-group form-group mb15">
                                <input type="text" class="form-control" id="cuentaDKeyInput" name="cuentaDKey"
                                    value="{{ isset($tesoreria) ? $tesoreria->treasuries_moneyAccountDestiny : '' }}"
                                    readonly />
                                <input type="hidden" name="tipoCuentaDTrans" id="tipoCuentaDTrans"
                                    value="{{ isset($tipoCuentaD) ? $tipoCuentaD->moneyAccounts_accountType : '' }}" />

                                <input type="hidden" name="tipoCambioDestino2"
                                    value="{{ isset($tipoCuentaDestino2) ? $tipoCuentaDestino2->moneyAccounts_money : '' }}"
                                    id="tipoCambioDestino2" />
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" data-toggle="modal"
                                        data-target=".modal122" id="cuentaDModal">...</button>
                                </span>
                            </div>
                        </div>

                        <div class="col-md-5" id="claveBancariaD">
                            <div class="form-group">
                                <label for="claveBancariaD" class="negrita">Nombre de la Cuenta de
                                    Bancos/Efectivo:</label>
                                <input type="text" class="form-control" name="claveBancariaD"
                                    id="claveBancariaDInput" value="" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4" id="saldoCta">
                        <div class="form-group">
                            <label for="saldoCuenta" class="negrita">Saldo Cuenta:</label>
                            <input type="text" class="form-control" value="" name="saldoCuenta"
                                id="saldoCuenta" readonly>
                        </div>
                    </div>
                    <div class="{{ isset($tesoreria) ? ($tesoreria['treasuries_movement'] === 'Transferencia Electrónica' || $tesoreria['treasuries_movement'] === 'Sol. de Cheque/Transferencia' ? 'col-md-4' : 'col-md-4') : 'col-md-4' }}"
                        id="observaciones">
                        <div class="form-group">
                            <label for="observaciones" class="negrita">Comentarios:</label>
                            <input type="text" class="form-control"
                                value="{{ isset($tesoreria) ? $tesoreria['treasuries_observations'] : null }}"
                                name="observaciones" id="observacionesInput">
                        </div>
                    </div>

                    {{-- <div class="col-md-6" id="saldo">
                        <div class="form-group">
                            <label for="saldo" class="negrita">Saldo:</label>
                            <input type="text" class="form-control"
                                value="{{ isset($tesoreria) ? '$' . number_format($tesoreria['treasuries_accountBalance'], 2) : null }}"
                                name="saldo" id="saldo" readonly>
                        </div>
                    </div> --}}
                    @if (isset($origenCheque))
                        <div class="col-md-12" id="tablaContenedor">
                            <div class="tab-content table-panel">
                                <table class="table table-striped table-bordered widthAll">
                                    <thead>
                                        <tr>
                                            <th class="id" style="display: none">Id</th>
                                            <th class="aplica">Operación Origen</th>
                                            <th class="aplicaC">Folio Operación Origen</th>
                                            <th class="importe">Importe</th>
                                            <th class="formaP">Forma de Pago</th>
                                        </tr>
                                    </thead>

                                    <tbody id="articleItem">
                                        <tr id="solicitudesCheque">
                                            <td class="id" style="display: none">
                                                <input type="hidden" id="id"
                                                    value="{{ isset($origenCheque) ? $origenCheque->treasuries_id : '' }}" />
                                            </td>
                                            <td class="aplica"><input id="aplicaInput" type="text"
                                                    class="botonesArticulos" name="Din[]"
                                                    value="{{ isset($origenCheque) ? $origenCheque->treasuries_movement : '' }}"
                                                    readonly></td>
                                            <td class="aplicaC"><input id="aplicaCInput" type="text"
                                                    class="botonesArticulos" name="Din[]"
                                                    value="{{ isset($origenCheque) ? $origenCheque->treasuries_movementID : '' }}"
                                                    readonly></td>
                                            <td class="importe"><input id="importeInput" type="text"
                                                    class="botonesArticulos" name="Din[]"
                                                    value="{{ isset($origenCheque) ? number_format($origenCheque->treasuries_total, 2) : '' }}"
                                                    readonly></td>
                                            <td class="formaP"><input id="formaPInput" type="text"
                                                    class="botonesArticulos" name="Din[]"
                                                    value="{{ isset($origenCheque) ? $formasPagoArray[$origenCheque->treasuries_paymentMethod] : '' }}"
                                                    readonly></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="col-md-3 pull-right" id="totalInformativo">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-addon">Total</span>
                                        <input type="text" class="form-control" id="totalCompleto"
                                            name="totalCompleto"
                                            value="{{ isset($origenCheque) ? number_format($origenCheque->treasuries_total, 2) : '' }}"
                                            readonly>
                                    </div>
                                </div>
                            </div>
                    @endif

                    <div class="col-md-12"></div>
                    <div class="col-md-1 pull-left">
                        <div class="input-group input-group-sm mt5" style="margin-left: -5px">
                            @if (isset($tesoreria) ? $tesoreria['treasuries_status'] === 'INICIAL' : 'INICIAL')
                                <span class="label label-default" id="status">INICIAL</span>
                            @elseif($tesoreria['treasuries_status'] === 'POR AUTORIZAR')
                                <span class="label label-warning" id="status">POR AUTORIZAR</span>
                            @elseif($tesoreria['treasuries_status'] === 'FINALIZADO')
                                <span class="label label-success" id="status">FINALIZADO</span>
                            @elseif($tesoreria['treasuries_status'] === 'CANCELADO')
                                <span class="label label-danger" id="status">CANCELADO</span>
                            @endif

                        </div>
                    </div>

                </div><!-- panel -->

                <div class="col-md-12 mt20 display-right" id="botonForm">
                    {!! Form::submit('Crear/Guardar', ['class' => 'btn btn-success']) !!}
                </div>
                <input type="hidden" id="inputDataDin" readonly>
                {!! Form::close() !!}
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
                        value="{{ isset($tesoreria) ? (isset($primerFlujodeTesoreria) ? $primerFlujodeTesoreria : '') : '' }}" />
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

    <div class="modal fade bd-example-modal-lg modal1" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="cuentaModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Lista de Cuentas de Dinero</h5>

                </div>
                <div class="modal-body">
                    <div class="panel table-panel">
                        <table id="shTable1" class="table table-striped table-bordered widthAll">
                            <thead>
                                <tr>
                                    <th>Clave</th>
                                    <th>Nombre</th>
                                    <th>Moneda</th>
                                    <th style="display: none">Clave</th>
                                    <th style="display: none">Tipo Cuenta</th>
                                    <th style="display: none">No. Cuenta</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($moneyAccounts as $moneyAccount)
                                    <tr>
                                        <td>{{ $moneyAccount->moneyAccounts_key }}</td>
                                        <td>{{ $moneyAccount->instFinancial_name }}</td>
                                        <td>{{ $moneyAccount->moneyAccounts_money }}</td>
                                        <td style="display: none">{{ $moneyAccount->moneyAccounts_keyAccount }}</td>
                                        <td style="display: none">{{ $moneyAccount->moneyAccounts_accountType }}</td>
                                        <td style="display: none">{{ $moneyAccount->moneyAccounts_numberAccount }}</td>
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

    <div class="modal fade bd-example-modal-lg modalBeneficiario" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true" id="beneficiarioModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Lista de Proveedores</h5>
                </div>
                <div class="modal-body">
                    <div class="panel table-panel">
                        <table id="shTable11" class="table table-striped table-bordered widthAll">
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
                    <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade bd-example-modal-lg modal122" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true" id="cuentaDModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Lista de Cuentas de Dinero</h5>

                </div>
                <div class="modal-body">
                    <div class="panel table-panel">
                        <table id="shTable2" class="table table-striped table-bordered widthAll">
                            <thead>
                                <tr>
                                    <th>Clave</th>
                                    <th>Nombre</th>
                                    <th style="display: none">clave</th>
                                    <th style="display: none">Tipo Cuenta</th>
                                    <th style="display: ">Tipo Moneda</th>
                                    <th style="display: none">No. Cuenta</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($moneyAccounts as $moneyAccount)
                                    <tr>
                                        <td>{{ $moneyAccount->moneyAccounts_key }}</td>
                                        <td>{{ $moneyAccount->instFinancial_name }}</td>
                                        <td style="display: none">{{ $moneyAccount->moneyAccounts_keyAccount }}</td>
                                        <td style="display: none">{{ $moneyAccount->moneyAccounts_accountType }}</td>
                                        </td>
                                        <td style="display: ">{{ $moneyAccount->moneyAccounts_money }}</td>
                                        <td style="display: none">{{ $moneyAccount->moneyAccounts_numberAccount }}</td>

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

    <div class="modal fade bd-example-modal-lg modal9" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="informacionProveedorModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Información Cuentas</h5>
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs nav-line" style="margin: 0px !important">
                        {{-- <li class="active"><a href="#activities2" data-toggle="tab"><strong></strong></a></li>
                <li class=""><a href="#followers2" data-toggle="tab"><strong>Artículo</strong></a></li> --}}
                        <li class="active"><a href="#following2" data-toggle="tab"><strong>Cuentas
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


                                        <div class="col-md-12 scrollInfoProveedor">
                                            <h5 class="mb10">Cuentas</h5>
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
                                                                            <th>Cuenta</th>
                                                                            <th>Tipo</th>
                                                                            <th>Saldo Inicial</th>
                                                                            <th>Saldo</th>
                                                                            <th>Empresa</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @if (isset($infoCuentas))
                                                                            @foreach ($infoCuentas as $movimiento)
                                                                                @if ($movimiento->moneyAccountsBalance_money == trim($moneda->money_key))
                                                                                    <tr>
                                                                                        <td>{{ $movimiento->moneyAccountsBalance_moneyAccount }}
                                                                                        </td>
                                                                                        </td>
                                                                                        <td>{{ $movimiento->moneyAccountsBalance_accountType }}
                                                                                        </td>
                                                                                        <td>{{ '$' . number_format($movimiento->moneyAccountsBalance_initialBalance, 2) }}
                                                                                        <td>{{ '$' . number_format($movimiento->moneyAccountsBalance_balance, 2) }}
                                                                                        </td>
                                                                                        </td>
                                                                                        <td>{{ $movimiento->companies_name }}
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

    <div class="modal fade" id="modalAfectar" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center" id="exampleModalCenterTitle">
                        Modulo Tesorería
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <div class="form-group container-respuesta" name="accionAfectarCheque">
                                        <input type="radio" id="chequeRadio" name="accionAfectar"
                                            value="Generar Transferencia Electrónica">
                                        <label for="cheque" name="accionCheque" id="accionCheque">Generar
                                        Transferencia Electrónica</label>

                                    </div>
                                </div>

                                <div class="col-md-12" name="accionAfectarDeposito" id="deposito">
                                    <div class="form-group container-respuesta" id="deposito">
                                        <input type="radio" id="depositoRadio" name="accionAfectarD"
                                            value="Generar Depósito">
                                        <label for="deposito" name="accionDeposito" id="accionDeposito">Generar
                                            Depósito</label>
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



        @include('include.mensaje')


        <script src="{{ asset('js/PROCESOS/tesoreria.js') }}"></script>

        <?php
        $select_conceptosArray = [];
        
        foreach ($select_conceptos as $conceptoItem) {
            $select_conceptosArray[$conceptoItem->moduleConcept_name] = $conceptoItem->moduleConcept_name;
        }
        ?>
        <script>
            $('#select-moduleConcept').val(
                    '{{ isset($tesoreria) ? (array_key_exists($tesoreria['treasuries_concept'], $select_conceptosArray) ? $tesoreria['treasuries_concept'] : '') : '' }}'
                )
                .trigger(
                    'change.select2');

            $('#select-PaymentMethod').val('{{ isset($tesoreria) ? $tesoreria['treasuries_paymentMethod'] : '' }}').trigger(
                'change.select2');

            let isOrigenCxp =
                "{{ isset($tesoreria) ? ($tesoreria['treasuries_origin'] === 'Sol. de Cheque/Transferencia' || $tesoreria['treasuries_origin'] === 'Solicitud Depósito' ? true : false) : false }}";






            if (isOrigenCxp) {
                $('.import').attr('readonly', true);
            } else {
                $('.import').attr('readonly', false);
            }
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
