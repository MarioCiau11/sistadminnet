@extends('layouts.layout')

@section('content')
    <?php $existe = false; ?>
    @foreach (auth()->user()->getAllPermissions()->where('categoria', '=', 'Compras')->pluck('name')->toArray() as $permisos)
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
                        <span class="fa-solid fa-cart-shopping"></span>
                    </div>
                    <div class="media-body">
                        <ul class="breadcrumb">
                            <li><a href="{{ route('dashboard.index') }}"><i class="glyphicon glyphicon-home"></i></a></li>
                            <li>Compras</li>
                        </ul>
                        <h4>Compras</h4>
                        <div class="breadcrumb">
                            <span>Crea órdenes de compra para solicitar a tus proveedores productos y Realiza la recepción
                                de mercancía a tu almacén.</span>
                        </div>
                    </div>
                </div>
                @if ($existe)
                    <div class="object-create">
                        <a href="{{ route('vista.modulo.compras.create-compra') }}" class="btn btn-success">Crear
                            Proceso/Operación</a>
                    </div>
                @endif

            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open(['route' => 'logistica.compras.filtro', 'method' => 'POST', 'id' => 'formValidate']) !!}
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
                        {!! Form::label('nameKey', 'Clave o Nombre Proveedor', ['class' => 'negrita']) !!}
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
                                'Orden de Compra' => 'Orden de Compra',
                                'Entrada por Compra' => 'Entrada por Compra',
                                'Rechazo de Compra' => 'Rechazo de Compra',
                            ],
                            session()->has('nameMov') ? session()->get('nameMov') : null,
                            ['class' => 'widthAll select-movement select-search-hided'],
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
                            session()->has('status') ? session()->get('status') : null,
                            ['class' => 'widthAll select-status select-search-hide'],
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
                            [
                                'id' => 'fechaSelect',
                                'class' => 'widthAll select-status select-search-hide',
                                'placeholder' => 'Seleccione una opción',
                            ],
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
                                'class' => 'widthAll select-status select-search-hide',
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
                                'class' => 'widthAll select-status select-search-hide',
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
                                'class' => 'widthAll select-status select-search-hided',
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

                <div class="col-md-12"></div>



                <div class="col-md-6">
                    <a href="{{ route('vista.modulo.compras') }}" class="btn btn-default">Restablecer</a>
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
                                        {!! Form::checkbox('Date', '3', true, ['id' => 'checkDate']) !!}
                                        {!! Form::label('checkDate', 'Fecha', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Age', '4', false, ['id' => 'checkAge']) !!}
                                        {!! Form::label('checkAge', 'Concepto de la Operación', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Salary', '5', false, ['id' => 'checkSalary']) !!}
                                        {!! Form::label('checkSalary', 'Moneda', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('ProveedorC', '6', false, ['id' => 'checkProveedorC']) !!}
                                        {!! Form::label('checkProveedorC', 'Clave Proveedor', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Proveedor', '7', true, ['id' => 'checkProveedor']) !!}
                                        {!! Form::label('checkProveedor', 'Proveedor', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Condicion', '8', true, ['id' => 'checkCondicion']) !!}
                                        {!! Form::label('checkCondicion', 'Término de Crédito', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('FechaExp', '9', true, ['id' => 'checkFechaExp']) !!}
                                        {!! Form::label('checkFechaExp', 'Fecha de vencimiento', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Referencia', '10', false, ['id' => 'checkReferencia']) !!}
                                        {!! Form::label('checkReferencia', 'Notas/Folio Factura', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Sucursal', '11', false, ['id' => 'checkSucursal']) !!}
                                        {!! Form::label('checkSucursal', 'Sucursal', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Almacen', '12', false, ['id' => 'checkAlmacen']) !!}
                                        {!! Form::label('checkAlmacen', 'Almacen', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Motivos', '13', false, ['id' => 'checkMotivos']) !!}
                                        {!! Form::label('checkMotivos', 'Motivo Cancelación', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Usuario', '14', false, ['id' => 'checkUsuario']) !!}
                                        {!! Form::label('checkUsuario', 'Usuario', ['class' => 'negrita']) !!}
                                    </div>
                                </li>


                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Estatus', '15', true, ['id' => 'checkEstatus']) !!}
                                        {!! Form::label('checkEstatus', 'Estatus/Estado', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Importe', '16', true, ['id' => 'checkImporte']) !!}
                                        {!! Form::label('checkImporte', 'Importe', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Impuestos', '17', true, ['id' => 'checkImpuestos']) !!}
                                        {!! Form::label('checkImpuestos', 'Impuestos', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Total', '18', true, ['id' => 'checkTotal']) !!}
                                        {!! Form::label('checkTotal', 'Total', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>


                {{-- En el controlador se pasa la data filtrada y se le enviamos al index como json_encode y en la vista usamos json_decode para
            manipular los datos. --}}
                {{-- @if (session('test'))
               {{  session('test')['name'] }}
            @else
            {{$message}}
            @endif --}}


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
                                    <th>Clave Proveedor</th>
                                    <th>Proveedor</th>
                                    <th>Término de Crédito</th>
                                    <th>Fecha de vencimiento</th>
                                    <th>Notas/Folio Factura</th>
                                    <th>Sucursal</th>
                                    <th>Almacén</th>
                                    <th>Motivo de Cancelación</th>
                                    <th>Usuario</th>
                                    <th>Estatus/Estado</th>
                                    <th>Importe</th>
                                    <th>Impuestos</th>
                                    <th>Total</th>

                                </tr>
                            </thead>

                            <tbody>


                                @if (session()->has('compras_filtro_array'))
                                    @foreach (session('compras_filtro_array') as $compra)
                                        @include('include.modulos.comprasItem')
                                    @endforeach
                                @else
                                    @foreach ($compras as $compra)
                                        @include('include.modulos.comprasItem')
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

                    jQuery('.select-search-hide, .select-search-hided').select2({
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
