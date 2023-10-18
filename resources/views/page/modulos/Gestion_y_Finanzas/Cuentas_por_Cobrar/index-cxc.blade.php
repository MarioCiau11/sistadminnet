@extends('layouts.layout')

@section('content')
    <?php $existe = false; ?>
    @foreach (auth()->user()->getAllPermissions()->where('categoria', '=', 'Cuentas por cobrar')->pluck('name')->toArray() as $permisos)
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
                        <span class="fa-solid fa-filter-circle-dollar"></span>
                    </div>
                    <div class="media-body">
                        <ul class="breadcrumb">
                            <li><a href="{{ route('dashboard.index') }}"><i class="glyphicon glyphicon-home"></i></a></li>
                            <li>Cuentas Por Cobrar</li>
                        </ul>
                        <h4>Cuentas Por Cobrar</h4>
                        <div class="breadcrumb">
                            <span>Registra tus comprobantes de ingreso y controla las entradas de dinero de tu negocio.
                                Gestiona tu cartera.</span>
                        </div>
                    </div>
                </div>
                @if ($existe)
                    <div class="object-create">
                        <a href="{{ route('vista.modulo.cuentasCobrar.create-cxc') }}" class="btn btn-success">Crear
                            Proceso/Operación</a>
                    </div>
                @endif
            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open(['route' => 'modulo.cuentasxcobrar.filtro', 'method' => 'POST']) !!}
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
                        {!! Form::label('nameKey', 'Clave o Nombre del Cliente', ['class' => 'negrita']) !!}
                        {!! Form::text('nameKey', session()->has('nameKey') ? session()->get('nameKey') : null, [
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
                                'Factura' => 'Factura',
                                'Anticipo Clientes' => 'Anticipo Clientes',
                                'Aplicación' => 'Aplicación',
                                'Cobro de Facturas' => 'Cobro de Facturas',
                                'Todos' => 'Todos',
                            ],
                            session()->has('nameMov') ? session()->get('nameMov') : 'Todos',
                        
                            ['id' => 'select-search-hided', 'class' => 'widthAll select-movement'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('status', 'Estatus/Estado', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'status',
                            [
                                'Todos' => 'Todos',
                                'INICIAL' => 'INICIAL',
                                'POR AUTORIZAR' => 'POR AUTORIZAR',
                                'FINALIZADO' => 'FINALIZADO',
                                'CANCELADO' => 'CANCELADO',
                            ],
                            session()->has('status') ? session()->get('status') : 'POR AUTORIZAR',
                        
                            ['id' => 'select-search-hide', 'class' => 'widthAll select-status'],
                        ) !!}
                    </div>
                </div>
                <div class="col-md-12"> </div>
                <div class="col-md-2">
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

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('nameFechaVen', 'Fecha de Vencimiento', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameFechaVen',
                            [
                                'Hoy' => 'Hoy',
                                'Ayer' => 'Ayer',
                                'Semana' => 'Semana',
                                'Mes' => 'Mes',
                                'Año Móvil' => 'Año Móvil',
                                'Año Pasado' => 'Año Pasado',
                                'Rango Fechas' => 'Rango Fechas',
                            ],
                            session()->has('nameFechaVen') ? session()->get('nameFechaVen') : 'Mes',
                            ['id' => 'select-fechaVen', 'class' => 'widthAll select-status'],
                        ) !!}

                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('nameUsuario', 'Usuario', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameUsuario',
                            $select_users,
                            session()->has('nameUsuario') ? session()->get('nameUsuario') : auth()->user()->username,
                            [
                                'id' => 'select-search-hide',
                                'class' => 'widthAll select-status',
                            ],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-2">
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

                <div class="col-md-2">
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

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('timbrado', 'Timbrado', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'timbrado',
                            [
                                'Todos' => 'Todos',
                                'Si' => 'Si',
                                'No' => 'No',
                            ],
                            session()->has('timbrado') ? session()->get('timbrado') : 'Todos',
                            ['id' => 'select-search-hide', 'class' => 'widthAll select-status'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-8 fecha-rango">
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('nameFechaInicio', 'Fecha Inicio', ['class' => 'negrita']) !!}
                            <div class="form-group">
                                <input type="text" class="form-control datepicker" name="fechaInicio" id="fechaInicial"
                                    placeholder="DD-MM-YYYY" autocomplete="off"
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
                                    placeholder="DD-MM/-YYYY" autocomplete="off"
                                    value="{{ session()->has('fechaFinal') ? session()->get('fechaFinal') : '' }}">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                            </div><!-- input-group -->
                        </div>
                    </div>
                </div>

                <div class="col-md-8 fechaVen-rango">
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('nameFechaInicio', 'Fecha Inicio Vencimiento', ['class' => 'negrita']) !!}
                            <div class="form-group">
                                <input type="text" class="form-control datepicker" name="fechaInicioVen"
                                    id="fechaInicioVen" placeholder="DD/MM/YYYY" autocomplete="off"
                                    value="{{ session()->has('fechaInicioVen') ? session()->get('fechaInicioVen') : '' }}">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                            </div><!-- input-group -->
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group  form-group">
                            {!! Form::label('nameFechaFinal', 'Fecha Final Vencimiento', ['class' => 'negrita']) !!}
                            <div class="form-group">
                                <input type="text" class="form-control datepicker" name="fechaFinalVen"
                                    id="fechaFinalVen" placeholder="DD/MM/YYYY" autocomplete="off"
                                    value="{{ session()->has('fechaFinalVen') ? session()->get('fechaFinalVen') : '' }}">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                            </div><!-- input-group -->
                        </div>
                    </div>
                </div>



                <div class="col-md-6">

                    <a href="{{ route('vista.modulo.cuentasCobrar.index') }}" class="btn btn-default">Restablecer</a>
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
                                        {!! Form::checkbox('Age', '3', true, ['id' => 'checkAge']) !!}
                                        {!! Form::label('checkAge', 'Fecha', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('ExpirationDate', '4', true, ['id' => 'checkExpirationDate']) !!}
                                        {!! Form::label('checkExpirationDate', 'Fecha de Vencimiento', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Salary', '5', true, ['id' => 'checkSalary']) !!}
                                        {!! Form::label('checkSalary', 'Moneda', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('TipoCambio', '6', false, ['id' => 'checkTipoCambio']) !!}
                                        {!! Form::label('checkTipoCambio', 'Tipo de Cambio', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('CuentaDinero', '7', false, ['id' => 'checkCuentaDinero']) !!}
                                        {!! Form::label('checkCuentaDinero', 'Cuenta de Dinero', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Cliente', '8', true, ['id' => 'checkCliente']) !!}
                                        {!! Form::label('checkCliente', 'Cliente', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('ClienteN', '9', true, ['id' => 'checkClienteN']) !!}
                                        {!! Form::label('checkClienteN', '   Nombre Cliente', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('FormaPago', '10', false, ['id' => 'checkFormaPago']) !!}
                                        {!! Form::label('checkFormaPago', 'Forma de Pago', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Observaciones', '11', false, ['id' => 'checkObservaciones']) !!}
                                        {!! Form::label('checkObservaciones', 'Comentarios adicionales', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('ImporteADI', '12', false, ['id' => 'checkImporteADI']) !!}
                                        {!! Form::label('checkImporteADI', 'Importe Antes de Impuestos', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('ImporteImpuestos', '13', false, ['id' => 'checkImporteImpuestos']) !!}
                                        {!! Form::label('checkImporteImpuestos', 'Impuestos', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('isRIva', '14', false, ['id' => 'checkisRIva']) !!}
                                        {!! Form::label('checkisRIva', 'Retenciones', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('ImporteTotal', '15', true, ['id' => 'checkImporteTotal']) !!}
                                        {!! Form::label('checkImporteTotal', 'Importe Total', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('saldoMov', '16', true, ['id' => 'checksaldoMov']) !!}
                                        {!! Form::label('checksaldoMov', 'Saldo', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Concepto', '17', true, ['id' => 'checkConcepto']) !!}
                                        {!! Form::label('checkConcepto', 'Concepto de la Operación', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Condicion', '18', false, ['id' => 'checkCondicion']) !!}
                                        {!! Form::label('checkCondicion', 'Término de Crédito', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Referencia', '19', false, ['id' => 'checkReferencia']) !!}
                                        {!! Form::label('checkReferencia', 'Notas', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Sucursal', '20', true, ['id' => 'checkSucursal']) !!}
                                        {!! Form::label('checkSucursal', 'Sucursal', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Usuario', '21', true, ['id' => 'checkUsuario']) !!}
                                        {!! Form::label('checkUsuario', 'Usuario', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Estatus', '22', true, ['id' => 'checkEstatus']) !!}
                                        {!! Form::label('checkEstatus', 'Estatus/Estado', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Timbrado', '23', true, ['id' => 'checkTimbrado']) !!}
                                        {!! Form::label('checkTimbrado', 'Timbrado', ['class' => 'negrita']) !!}
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
                                    <th>Fecha de Vencimiento</th>
                                    <th>Moneda</th>
                                    <th>Tipo de Cambio</th>
                                    <th>Cuenta Dinero</th>
                                    <th>Cliente</th>
                                    <th>Nombre Cliente</th>
                                    <th>Forma de Pago</th>
                                    <th>Comentarios adicionales</th>
                                    <th>Importe antes de Impuestos</th>
                                    <th>Impuestos</th>
                                    <th>Retenciones</th>
                                    <th>Importe Total</th>
                                    <th>Saldo</th>
                                    <th>Concepto de la Operación</th>
                                    <th>Término de Crédito</th>
                                    <th>Notas</th>
                                    <th>Sucursal</th>
                                    <th>Usuario</th>
                                    <th>Estatus/Estado</th>
                                    <th>Timbrado</th>
                                </tr>
                            </thead>

                            <tbody>


                                @if (session()->has('CXC_filtro_array'))
                                    @foreach (session('CXC_filtro_array') as $cxc)
                                        @include('include.modulos.cuentasxcobrarItem')
                                    @endforeach
                                @else
                                    @foreach ($cuentasxcobrar as $cxc)
                                        @include('include.modulos.cuentasxcobrarItem')
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
                    const $fechaVen_rango = $('.fechaVen-rango');
                    const $fechaInicioVen = $('#fechaInicioVen');
                    const $fechaFinalVen = $('#fechaFinalVen');
                    const $fecha_selectVen = jQuery('#select-fechaVen');

                    $fecha_select.val() == 'Rango Fechas' ? $fecha_rango.show() : $fecha_rango.hide();

                    $fecha_selectVen.val() == 'Rango Fechas' ? $fechaVen_rango.show() : $fechaVen_rango.hide();


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

                    $fecha_selectVen.on('change', function() {
                        let option = jQuery(this).val();

                        if (option === 'Rango Fechas') {
                            $fechaVen_rango.show();
                        } else {
                            $fechaInicioVen.val('');
                            $fechaFinalVen.val('');
                            $fechaVen_rango.hide();
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

                            },
                            fechaInicioVen: {
                                required: true,
                                date: true
                            },
                            fechaFinalVen: {
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
                            },
                            fechaInicioVen: {
                                required: 'Ingrese una fecha de inicio',
                                date: 'Ingrese una fecha válida'
                            },
                            fechaFinalVen: {
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
