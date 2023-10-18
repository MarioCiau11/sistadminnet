@extends('layouts.layout')

@section('content')
    <?php $existe = false; ?>
    @foreach (auth()->user()->getAllPermissions()->where('categoria', '=', 'Tesorería')->pluck('name')->toArray() as $permisos)
        <?php
        $mov = substr($permisos, 0, -2);
        $letra = substr($permisos, -1);
        ?>
        @if ($letra === 'E')
            <?php
            $existe = true;
            ?>
        @endif
    @endforeach
    <div class="mainpanel">
        <div class="pageheader">
            <div class="media display-space-between">
                <div>
                    <div class="pageicon pull-left mr10">
                        <span class="fa-solid fa-money-bill-transfer"></span>
                    </div>
                    <div class="media-body">
                        <ul class="breadcrumb">
                            <li><a href="{{ route('dashboard.index') }}"><i class="glyphicon glyphicon-home"></i></a></li>
                            <li>Tesorería/Bancos</li>
                        </ul>
                        <h4>Tesorería/Bancos</h4>
                        <div class="breadcrumb">
                            <span>Controla tus operaciones de dinero con tus cuentas de banco, efectivo y tarjetas
                                bancarias.</span>
                        </div>
                    </div>
                </div>

                @if ($existe)
                    <div class="object-create">
                        <a href="{{ route('vista.modulo.tesoreria.create-tesoreria') }}" class="btn btn-success">Crear
                            Proceso/Operación</a>
                    </div>
                @endif

            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open(['route' => 'modulo.tesoreria.filtro', 'method' => 'POST', 'id' => 'formValidate']) !!}
                <div class="col-md-4 ">
                    <div class="form-group">
                        {!! Form::label('nameFolio', 'Folio', ['class' => 'negrita']) !!}
                        {!! Form::text('nameFolio', session()->has('nameFolio') ? session()->get('nameFolio') : null, [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameMov', 'Proceso/Operación', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameMov',
                            [
                                'Todos' => 'Todos',
                                'Egreso' => 'Egreso',
                                'Ingreso' => 'Ingreso',
                                'Sol. de Cheque/Transferencia' => 'Sol. de Cheque/Transferencia',
                                'Transferencia Electrónica' => 'Transferencia Electrónica',
                                'Solicitud Depósito' => 'Solicitud Depósito',
                                'Depósito' => 'Depósito',
                                'Traspaso Cuentas' => 'Traspaso Cuentas',
                            ],
                            session()->has('nameMov') ? session()->get('nameMov') : null,
                            ['id' => 'select-search-hided', 'class' => 'widthAll select-movement'],
                        ) !!}
                    </div>
                </div>


                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('cuentasDinero', 'Cuentas de Dinero', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'cuentasDinero',
                            $select_cuentas,
                            session()->has('cuentasDinero') ? session()->get('cuentasDinero') : null,
                            [
                                'id' => 'select-search-hide',
                                'class' => 'widthAll select-status',
                            ],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('status', 'Estatus', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'status',
                            [
                                'Todos' => 'Todos',
                                'INICIAL' => 'INICIAL',
                                'POR AUTORIZAR' => 'POR AUTORIZAR',
                                'FINALIZADO' => 'FINALIZADO',
                                'CANCELADO' => 'CANCELADO',
                            ],
                            session()->has('status') ? session()->get('status') : 'Todos',
                        
                            ['id' => 'select-search-hide', 'class' => 'widthAll select-status'],
                        ) !!}
                    </div>
                </div>
                <div class="col-md-12"> </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameFecha', 'Fecha', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameFecha',
                            [
                                'Hoy' => 'Hoy',
                                'Ayer' => 'Ayer',
                                'Semana' => 'Semana',
                                'Mes' => 'Mes',
                                'Año Móvil' => 'Año Móvil',
                                'Año Pasado' => 'Año Pasado',
                                'Rango Fechas' => 'Rango Fechas',
                            ],
                            session()->has('nameFecha') ? session()->get('nameFecha') : 'Mes',
                            ['id' => 'select-fecha', 'class' => 'widthAll select-status'],
                        ) !!}

                    </div>
                </div>


                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameUsuario', 'Usuario', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameUsuario',
                            ['Todos' => 'Todos', ...$select_users],
                        
                            session()->has('nameUsuario') ? session()->get('nameUsuario') : auth()->user()->username,
                            [
                                'id' => 'select-search-hide',
                                'class' => 'widthAll select-status',
                            ],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameSucursal', 'Sucursal', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameSucursal',
                            $select_sucursales,
                            session()->has('nameSucursal') ? session()->get('nameSucursal') : session('sucursal')->branchOffices_key,
                            [
                                'id' => 'select-search-hide',
                                'class' => 'widthAll select-status',
                            ],
                        ) !!}

                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameMoneda', 'Moneda', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameMoneda',
                            $selectMonedas,
                            session()->has('nameMoneda') ? session()->get('nameMoneda') : $parametro->generalParameters_defaultMoney,
                            [
                                'id' => 'select-search-hided',
                                'class' => 'widthAll select-status',
                            ],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-8 fecha-rango">
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('nameFechaInicio', 'Fecha Inicio', ['class' => 'negrita']) !!}
                            <div class="form-group">
                                <input type="text" class="form-control datepicker" name="fechaInicio" id="fechaInicial"
                                    placeholder="DD/MM/AAAA" autocomplete="off"
                                    value="{{ session()->has('fechaInicio') ? session()->get('fechaInicio') : '' }}">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                            </div><!-- input-group -->
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group  form-group">
                            {!! Form::label('nameFechaFinal', 'Fecha Final', ['class' => 'negrita']) !!}
                            <div class="form-group">
                                <input type="text" class="form-control datepicker" name="fechaFinal" id="fechaFinal"
                                    placeholder="DD/MM/AAAA" autocomplete="off"
                                    value="{{ session()->has('fechaFinal') ? session()->get('fechaFinal') : '' }}">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                            </div><!-- input-group -->
                        </div>
                    </div>
                </div>


                <div class="col-md-6">
                    <a href="{{ route('vista.modulo.tesoreria.index') }}" class="btn btn-default">Restablecer</a>
                    {!! Form::submit('Búsqueda', ['class' => 'btn btn-primary', 'name' => 'action']) !!}
                    {!! Form::submit('Exportar excel', ['class' => 'btn btn-info', 'name' => 'action']) !!}

                    {!! Form::close() !!}
                </div>

                <div class="col-md-6">
                    <div class="btn-columns">
                        <div class="btn-group">
                            <button data-toggle="dropdown" class="btn btn-sm mt5 btn-white border dropdown-toggle"
                                type="button">
                                Columnas <span class="caret"></span>
                            </button>
                            <ul role="menu" id="shCol" class="dropdown-menu dropdown-menu-sm pull-right">
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Opciones', '0', true, ['id' => 'checkOpciones']) !!}
                                        {!! Form::label('checkOpciones', 'Opciones', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('name', '1', true, ['id' => 'checkName']) !!}
                                        {!! Form::label('checkName', 'Proceso/Operación', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('position', '2', true, ['id' => 'checkPosition']) !!}
                                        {!! Form::label('checkPosition', 'Folio', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('office', '3', true, ['id' => 'checkOffice']) !!}
                                        {!! Form::label('checkOffice', 'Fecha', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Age', '4', true, ['id' => 'checkAge']) !!}
                                        {!! Form::label('checkAge', 'Concepto de la Operación', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Date', '5', true, ['id' => 'checkDate']) !!}
                                        {!! Form::label('checkDate', 'Moneda', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Salary', '6', false, ['id' => 'checkSalary']) !!}
                                        {!! Form::label('checkSalary', 'Tipo de Cambio', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('CuentaDinero', '7', true, ['id' => 'checkCuentaDinero']) !!}
                                        {!! Form::label('checkCuentaDinero', 'Cuenta Dinero', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('CuentaDineroOrigen', '8', false, ['id' => 'checkCuentaDineroOrigen']) !!}
                                        {!! Form::label('checkCuentaDineroOrigen', 'Cuenta Dinero Origen', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('CuentaDineroDestino', '9', false, ['id' => 'checkCuentaDineroDestino']) !!}
                                        {!! Form::label('checkCuentaDineroDestino', 'Cuenta Dinero Destino', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('BalanceCuenta', '10', false, ['id' => 'checkBalanceCuenta']) !!}
                                        {!! Form::label('checkBalanceCuenta', 'Balance de Cuenta', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('FormaPago', '11', false, ['id' => 'checkFormaPago']) !!}
                                        {!! Form::label('checkFormaPago', 'Forma de Pago', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Beneficiario', '12', true, ['id' => 'checkBeneficiario']) !!}
                                        {!! Form::label('checkBeneficiario', 'Beneficiario', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Referencia', '13', true, ['id' => 'checkReferencia']) !!}
                                        {!! Form::label('checkReferencia', 'Notas', ['class' => 'negrita']) !!}
                                    </div>
                                </li>


                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Observaciones', '14', true, ['id' => 'checkObservaciones']) !!}
                                        {!! Form::label('checkObservaciones', 'Comentarios adicionales', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Empresa', '15', false, ['id' => 'checkEmpresa']) !!}
                                        {!! Form::label('checkEmpresa', 'Empresa', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Sucursal', '16', false, ['id' => 'checkSucursal']) !!}
                                        {!! Form::label('checkSucursal', 'Sucursal', ['class' => 'negrita']) !!}
                                    </div>
                                </li>


                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Usuario', '17', true, ['id' => 'checkUsuario']) !!}
                                        {!! Form::label('checkUsuario', 'Usuario', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Estatus', '18', true, ['id' => 'checkEstatus']) !!}
                                        {!! Form::label('checkEstatus', 'Estatus', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Importe', '19', false, ['id' => 'checkImporte']) !!}
                                        {!! Form::label('checkImporte', 'Importe', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Impuesto', '20', false, ['id' => 'checkImpuesto']) !!}
                                        {!! Form::label('checkImpuesto', 'Impuesto', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Total', '21', true, ['id' => 'checkTotal']) !!}
                                        {!! Form::label('checkTotal', 'Total', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>


                <div class="col-md-12">
                    <div class="panel table-panel">
                        <table id="shTable" class="table table-striped table-bordered widthAll">
                            <thead class="">
                                <tr>
                                    <th></th>
                                    <th>Proceso/Operación</th>
                                    <th>Folio</th>
                                    <th>Fecha</th>
                                    <th>Concepto de la Operación</th>
                                    <th>Moneda</th>
                                    <th>Tipo de Cambio</th>
                                    <th>Cuenta Dinero</th>
                                    <th>Cuenta Dinero Origen</th>
                                    <th>Cuenta Dinero Destino</th>
                                    <th>Balance de Cuenta</th>
                                    <th>Forma de Pago</th>
                                    <th>Beneficiario</th>
                                    <th>Notas</th>
                                    <th>Comentarios adicionales</th>
                                    <th>Empresa</th>
                                    <th>Sucursal</th>
                                    <th>Usuario</th>
                                    <th>Estatus</th>
                                    <th>Importe</th>
                                    <th>Impuesto</th>
                                    <th>Total</th>

                                </tr>
                            </thead>

                            <tbody>


                                @if (session()->has('tesoreria_filtro_array'))
                                    @foreach (session('tesoreria_filtro_array') as $din)
                                        @include('include.modulos.tesoreriaItem')
                                    @endforeach
                                @else
                                    @foreach ($tesoreria as $din)
                                        @include('include.modulos.tesoreriaItem')
                                    @endforeach
                                @endif

                            </tbody>
                        </table>
                    </div><!-- panel -->

                </div>

            </div>
            <div>
            </div>

            @include('include.mensaje')

            <script src="{{ asset('js/language/DatePicker/datePicker.js') }}"></script>
            <script>
                jQuery(document).ready(function() {
                    $.datepicker.setDefaults($.datepicker.regional["es"]);

                    const $fecha_rango = $('.fecha-rango');
                    const $fechaInicio = $('#fechaInicial');
                    const $fechaFinal = $('#fechaFinal');
                    const $fecha_select = jQuery('#select-fecha');

                    $fecha_select.val() == 'Rango Fechas' ? $fecha_rango.show() : $fecha_rango.hide();


                    const $form = jQuery('#formValidate');

                    jQuery(
                            '.select-search-hide, .select-movement, .select-user, .select-sucursal, .select-fecha, .select-money, .select-status'
                        )
                        .select2({
                            minimumResultsForSearch: -1
                        });

                    jQuery('.datepicker').datepicker({
                        dateFormat: 'yy-mm-dd',
                    });

                    $fecha_select.on('change', function() {
                        let option = jQuery(this).val();

                        if (option === 'Rango Fechas') {
                            $fecha_rango.show();
                        } else {
                            $fechaInicio.val('');
                            $fechaFinal.val('');
                            $fecha_rango.hide();
                        }
                    });

                    //Validamos los inputs de rangos de fechas
                    $form.validate({
                        rules: {
                            fechaInicio: {
                                required: true,
                                date: true
                            },
                            fechaFinal: {
                                required: true,
                                date: true
                            }
                        },
                        messages: {
                            fechaInicio: {
                                required: 'Ingrese una fecha de inicio',
                                date: 'Ingrese una fecha válida'
                            },
                            fechaFinal: {
                                required: 'Ingrese una fecha de fin',
                                date: 'Ingrese una fecha válida'
                            }
                        },
                        highlight: function(element) {
                            jQuery(element).closest(".form-group").addClass("has-error");
                        },
                        unhighlight: function(element) {
                            jQuery(element).closest(".form-group").removeClass("has-error");
                        },
                        success: function(element) {
                            jQuery(element).closest(".form-group").removeClass("has-error");
                        },
                    });



                });
            </script>
        @endsection
