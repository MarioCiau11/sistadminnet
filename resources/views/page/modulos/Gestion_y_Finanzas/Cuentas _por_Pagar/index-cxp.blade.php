@extends('layouts.layout')

@section('content')
    <?php $existe = false; ?>
    @foreach (auth()->user()->getAllPermissions()->where('categoria', '=', 'Cuentas por pagar')->pluck('name')->toArray() as $permisos)
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
                        <span class="fa-solid fa-comments-dollar"></span>
                    </div>
                    <div class="media-body">
                        <ul class="breadcrumb">
                            <li><a href="{{ route('dashboard.index') }}"><i class="glyphicon glyphicon-home"></i></a></li>
                            <li>Cuentas por Pagar</li>
                        </ul>
                        <h4>Cuentas por Pagar</h4>
                        <div class="breadcrumb">
                            <span>Registra tus comprobantes de egreso y controla las salidas de dinero de tu negocio.</span>
                        </div>
                    </div>
                </div>


                @if ($existe)
                    <div class="object-create">
                        <div class="object-create">
                            <a href="{{ route('vista.modulo.cuentasPagar.create-cxp') }}" class="btn btn-success">Crear
                                Proceso/Operación</a>
                        </div>

                    </div><!-- media -->
                @endif


            </div><!-- pageheader -->

            <div class="contentpanel">
                <div class="row row-stat">
                    {!! Form::open(['route' => 'modulo.cuentasPagar.filtro', 'method' => 'POST']) !!}
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
                            {!! Form::label('nameKey', 'Clave o Nombre del Proveedor', ['class' => 'negrita']) !!}
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
                                    'Todos' => 'Todos',
                                    'Gastos' => 'Gastos',
                                    'Entrada por Compra' => 'Entrada por Compra',
                                    'Anticipo' => 'Anticipo',
                                    'Aplicación' => 'Aplicación',
                                    'Pago de Facturas' => 'Pago de Facturas',
                                ],
                                session()->has('nameMov') ? session()->get('nameMov') : 'Todos',
                                ['id' => 'select-movement', 'class' => 'widthAll select-movement'],
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
                    <div class="col-md-12"></div>

                    <div class="col-md-4">
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

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('nameUsuario', 'Usuario', ['class' => 'negrita']) !!}
                            {!! Form::select(
                                'nameUsuario',
                                ['Todos' => 'Todos', ...$select_users],
                                session()->has('nameUsuario') ? session()->get('nameUsuario') : auth()->user()->username,
                                [
                                    'id' => 'select-user',
                                    'class' => 'widthAll select-user',
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
                                    'id' => 'select-sucursal',
                                    'class' => 'widthAll select-sucursal',
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
                                    'id' => 'select-money',
                                    'class' => 'widthAll select-money',
                                ],
                            ) !!}
                        </div>
                    </div>

                    <div class="col-md-8 fecha-rango">
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('nameFechaInicio', 'Fecha Inicio', ['class' => 'negrita']) !!}
                                <div class="form-group">
                                    <input type="text" class="form-control datepicker" name="fechaInicio"
                                        id="fechaInicial" placeholder="DD/MM/AAAA" autocomplete="off"
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

                        <a href="{{ route('vista.modulo.cuentasPagar.index') }}" class="btn btn-default">Restablecer</a>
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
                                            {!! Form::checkbox('ID', '2', true, ['id' => 'checkID']) !!}
                                            {!! Form::label('checkID', 'Folio', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('fecha', '3', true, ['id' => 'nameFecha']) !!}
                                            {!! Form::label('nameFecha', 'Fecha', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('nameVen', '4', true, ['id' => 'nameVen']) !!}
                                            {!! Form::label('nameVen', 'Fecha de Vencimiento', ['class' => 'negrita']) !!}
                                        </div>
                                    <li>

                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('nameCambio', '5', false, ['id' => 'nameCambio']) !!}
                                            {!! Form::label('nameCambio', 'Tipo de Cambio', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('ctaDinero', '6', false, ['id' => 'ctaDinero']) !!}
                                            {!! Form::label('ctaDinero', 'Cuenta Dinero', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('claveProvider', '7', true, ['id' => 'claveProvider']) !!}
                                            {!! Form::label('claveProvider', 'Clave Proveedor', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('nameProvider', '8', true, ['id' => 'nameProvider']) !!}
                                            {!! Form::label('nameProvider', 'Nombre Proveedor', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('conPago', '9', false, ['id' => 'conPago']) !!}
                                            {!! Form::label('conPago', 'Término de Crédito', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('Moneda', '10', true, ['id' => 'Moneda']) !!}
                                            {!! Form::label('Moneda', 'Moneda', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('formPago', '11', false, ['id' => 'formPago']) !!}
                                            {!! Form::label('formPago', 'Forma de Pago', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('nameObservaciones', '12', false, ['id' => 'nameObservaciones']) !!}
                                            {!! Form::label('nameObservaciones', 'Comentarios adicionales', ['class' => 'negrita']) !!}

                                        </div>
                                    </li>

                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('nameImporte', '13', true, ['id' => 'nameImporte']) !!}
                                            {!! Form::label('nameImporte', 'Importe', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('nameImpuestos', '14', true, ['id' => 'nameImpuestos']) !!}
                                            {!! Form::label('nameImpuestos', 'Impuestos', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('nameTotal', '15', true, ['id' => 'nameTotal']) !!}
                                            {!! Form::label('nameTotal', 'Total', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('nameConcepto', '16', false, ['id' => 'nameConcepto']) !!}
                                            {!! Form::label('nameConcepto', 'Concepto de la Operación', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('Referencias', '17', false, ['id' => 'Referencias']) !!}
                                            {!! Form::label('Referencias', 'Notas/Folio Factura', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('Balance', '18', false, ['id' => 'Balance']) !!}
                                            {!! Form::label('Balance', 'Balance', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('Empresa', '19', false, ['id' => 'Empresa']) !!}
                                            {!! Form::label('Empresa', 'Empresa', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('Sucursal', '20', true, ['id' => 'Sucursal']) !!}
                                            {!! Form::label('Sucursal', 'Sucursal', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('Usuario', '21', true, ['id' => 'Usuario']) !!}
                                            {!! Form::label('Usuario', 'Usuario', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('Estatus', '22', true, ['id' => 'Estatus']) !!}
                                            {!! Form::label('Estatus', 'Estatus/Estado', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>

                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('Saldo', '23', true, ['id' => 'checkSaldo']) !!}
                                            {!! Form::label('checkSaldo', 'Saldo', ['class' => 'negrita']) !!}
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
                                        <th>Tipo de Cambio</th>
                                        <th>Cuenta Dinero</th>
                                        <th>Clave Proveedor</th>
                                        <th>Nombre Proveedor</th>
                                        <th>Término de Crédito</th>
                                        <th>Moneda</th>
                                        <th>Forma de Pago</th>
                                        <th>Comentarios adicionales</th>
                                        <th>Importe</th>
                                        <th>Impuestos</th>
                                        <th>Total</th>
                                        <th>Concepto de la Operación</th>
                                        <th>Notas/Folio Factura</th>
                                        <th>Balance</th>
                                        <th>Empresa</th>
                                        <th>Sucursal</th>
                                        <th>Usuario</th>
                                        <th>Estatus/Estado</th>
                                        <th>Saldo</th>

                                    </tr>
                                </thead>

                                <tbody>


                                    @if (session()->has('CXP_filtro_array'))
                                        @foreach (session('CXP_filtro_array') as $cuentaxpagar)
                                            @include('include.modulos.cuentasxpagarItem')
                                        @endforeach
                                    @else
                                        @foreach ($cuentasxpagar as $cuentaxpagar)
                                            @include('include.modulos.cuentasxpagarItem')
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
                            dateFormat: 'yy/mm/dd',
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
