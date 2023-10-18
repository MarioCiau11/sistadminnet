@extends('layouts.layout')

@section('content')
    <?php $existe = false; ?>
    @foreach (auth()->user()->getAllPermissions()->where('categoria', '=', 'Ventas')->pluck('name')->toArray() as $permisos)
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
                        <span class="fa-solid fa-cash-register"></span>
                    </div>
                    <div class="media-body">
                        <ul class="breadcrumb">
                            <li><a href="{{ route('dashboard.index') }}"><i class="glyphicon glyphicon-home"></i></a></li>
                            <li>Ventas</li>
                        </ul>
                        <h4>Ventas</h4>
                        <div class="breadcrumb">
                            <span>Registra tus operaciones para agilizar tus ventas.</span>
                        </div>
                    </div>
                </div>

                @if ($existe)
                    <div class="object-create">
                        <a href="{{ route('vista.modulo.ventas.create-venta') }}" class="btn btn-success">Crear
                            Proceso/Operación</a>
                    </div>
                @endif

            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open(['route' => 'logistica.ventas.filtro', 'method' => 'POST']) !!}
                <div class="col-md-1">
                    <div class="form-group">
                        {!! Form::label('nameFolio', 'Folio', ['class' => 'negrita']) !!}
                        {!! Form::text('nameFolio', session()->has('nameFolio') ? session()->get('nameFolio') : null, [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameKey', 'Clave o Nombre Cliente', ['class' => 'negrita']) !!}
                        {!! Form::text('nameKey', session()->has('nameKey') ? session()->get('nameKey') : null, [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('referenciaMov', 'Notas', ['class' => 'negrita']) !!}
                        {!! Form::text('referenciaMov', session()->has('referenciaMov') ? session()->get('referenciaMov') : null, [
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
                                'Cotización' => 'Cotización',
                                'Pedido' => 'Pedido',
                                'Factura' => 'Factura',
                                'Rechazo de Venta' => 'Rechazo de Venta',
                            ],
                            session()->has('nameMov') ? session()->get('nameMov') : null,
                            ['id' => 'select-movimiento', 'class' => 'widthAll select-movement'],
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
                            session()->has('status') ? session()->get('status') : null,
                            ['id' => 'select-status', 'class' => 'widthAll select-status'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-12"></div>


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
                            [
                                'id' => 'fechaSelect',
                                'class' => 'widthAll select-status select-search-hide',
                                'placeholder' => 'Seleccione una opción',
                            ],
                        ) !!}

                    </div>
                </div>

                <div class="col-md-3">
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
                                'id' => 'select-search-hided',
                                'class' => 'widthAll select-moneda',
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
                            ['id' => 'select-timbrado', 'class' => 'widthAll select-status'],
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

                    <a href="{{ route('vista.modulo.ventas') }}" class="btn btn-default">Restablecer</a>
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
                                        {!! Form::checkbox('Date', '4', true, ['id' => 'checkDate']) !!}
                                        {!! Form::label('checkDate', 'Moneda', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Salary', '5', false, ['id' => 'checkSalary']) !!}
                                        {!! Form::label('checkSalary', 'Tipo de Cambio', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Concepto', '6', true, ['id' => 'checkConcepto']) !!}
                                        {!! Form::label('checkConcepto', 'Concepto de la Operación', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Cliente', '7', true, ['id' => 'checkCliente']) !!}
                                        {!! Form::label('checkCliente', 'Clave Cliente', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('ClienteClave', '8', true, ['id' => 'checkClienteClave']) !!}
                                        {!! Form::label('checkClienteClave', 'Cliente', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('ListaPrecios', '9', true, ['id' => 'checkListaPrecios']) !!}
                                        {!! Form::label('checkListaPrecios', 'Lista de Precios', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('CondicionPago', '10', true, ['id' => 'checkCondicionPago']) !!}
                                        {!! Form::label('checkCondicionPago', 'Término de Crédito', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('FechaVencimiento', '11', false, ['id' => 'checkFechaVencimiento']) !!}
                                        {!! Form::label('checkFechaVencimiento', 'Vencimiento', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Referencia', '12', true, ['id' => 'checkReferencia']) !!}
                                        {!! Form::label('checkReferencia', 'Estatus/Estado', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                {{-- <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Contrato', '13', false, ['id' => 'checkContrato']) !!}
                                        {!! Form::label('checkContrato', 'Contrato', ['class' => 'negrita']) !!}
                                    </div>
                                </li> --}}

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('SubTotal', '13', false, ['id' => 'checkSubTotal']) !!}
                                        {!! Form::label('checkSubTotal', 'SubTotal', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Impuestos', '14', false, ['id' => 'checkImpuestos']) !!}
                                        {!! Form::label('checkImpuestos', 'Impuestos', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Total', '15', true, ['id' => 'checkTotal']) !!}
                                        {!! Form::label('checkTotal', 'Total', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Usuario', '16', false, ['id' => 'checkUsuario']) !!}
                                        {!! Form::label('checkUsuario', 'Usuario', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Estatus', '17', true, ['id' => 'checkEstatus']) !!}
                                        {!! Form::label('checkEstatus', 'Estatus/Estado', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Almacen', '18', false, ['id' => 'checkAlmacen']) !!}
                                        {!! Form::label('checkAlmacen', 'Almacen', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Motivo', '19', false, ['id' => 'checkMotivo']) !!}
                                        {!! Form::label('checkMotivo', 'Motivo Cancelación', ['class' => 'negrita']) !!}
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
                                        {!! Form::checkbox('Vendedor', '21', true, ['id' => 'checkAvance']) !!}
                                        {!! Form::label('checkAvance', 'Vendedor', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Timbrado', '22', true, ['id' => 'checkTimbrado']) !!}
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
                                    <th>Moneda</th>
                                    <th>Tipo de Cambio</th>
                                    <th>Concepto de la Operación</th>
                                    <th>Clave Cliente</th>
                                    <th>Cliente</th>
                                    <th>Lista de Precios</th>
                                    <th>Término de Crédito</th>
                                    <th>Vencimiento</th>
                                    <th>Notas</th>
                                    <th>SubTotal</th>
                                    <th>Impuestos</th>
                                    <th>Total</th>
                                    <th>Usuario</th>
                                    <th>Estatus/Estado</th>
                                    <th>Almacen</th>
                                    <th>Motivo Cancelación</th>
                                    <th>Sucursal</th>
                                    <th>Vendedor</th>
                                    <th>Timbrado</th>


                                </tr>
                            </thead>

                            <tbody>


                                @if (session()->has('ventas_filtro_array'))
                                    @foreach (session('ventas_filtro_array') as $venta)
                                        @include('include.modulos.ventasItem')
                                    @endforeach
                                @else
                                    @foreach ($ventas as $venta)
                                        @include('include.modulos.ventasItem')
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
                    const $fecha_select = jQuery('#fechaSelect');

                    $fecha_select.val() == 'Rango Fechas' ? $fecha_rango.show() : $fecha_rango.hide();


                    const $form = jQuery('#formValidate');

                    jQuery(
                            '.select-search-hide, .select-search-hided, .select-movement, .select-status, .select-sucursal, .select-moneda, .select-timbrado'
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
