@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="pageheader">
            <div class="media display-space-between">
                <div>
                    <div class="pageicon pull-left mr10">
                        <span class="glyphicon glyphicon-shopping-cart"></span>
                    </div>
                    <div class="media-body">
                        <ul class="breadcrumb">
                            <li><a href="{{ route('dashboard.index') }}"><i class="glyphicon glyphicon-home"></i></a></li>
                            <li>Compras por Unidad</li>
                        </ul>
                        <h4>Compras por Unidad</h4>
                    </div>
                </div>

            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open(['route' => 'reportes.compras.unidad.filtro', 'method' => 'POST', 'id' => 'formValidate']) !!}
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameMov', 'Movimiento', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameMov',
                            ['Entrada por Compra' => 'Entrada por Compra', 'Todos' => 'Todos'],
                            session()->has('nameMov') ? session()->get('nameMov') : null,
                            ['class' => 'widthAll select-movement select-search-hided'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameUnidad', 'Unidad', ['class' => 'negrita']) !!}
                        {!! Form::select('nameUnidad', $unidad, session()->has('nameUnidad') ? session()->get('nameUnidad') : null, [
                            'class' => 'widthAll select-movement select-search-hided',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameAlmacen', 'Almacén', ['class' => 'negrita']) !!}
                        {!! Form::select('nameAlmacen', $almacen, session()->has('nameAlmacen') ? session()->get('nameAlmacen') : null, [
                            'class' => 'widthAll select-movement select-search-hided',
                        ]) !!}
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
                                'class' => 'widthAll select-status select-search-hide',
                            ],
                        ) !!}


                    </div>
                </div>

                <div class="col-md-6">
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


                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('nameMoneda', 'Moneda', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameMoneda',
                            $selectMonedas,
                            session()->has('nameMoneda') ? session()->get('nameMoneda') : $parametro[0]->generalParameters_defaultMoney,
                            [
                                'class' => 'widthAll select-status select-search-hided',
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
                            session()->has('status') ? session()->get('status') : null,
                            ['class' => 'widthAll select-status select-search-hide'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-9 fecha-rango">
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('nameFechaInicio', 'Fecha Inicio', ['class' => 'negrita']) !!}
                            <div class="form-group">
                                <input type="text" class="form-control datepicker" name="fechaInicio" id="fechaInicial"
                                    placeholder="YYYY/MM/DD" autocomplete="off"
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
                                    placeholder="YYYY/MM/DD" autocomplete="off"
                                    value="{{ session()->has('fechaFinal') ? session()->get('fechaFinal') : '' }}">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                            </div><!-- input-group -->
                        </div>
                    </div>
                </div>

                <div class="col-md-12"></div>



                <div class="col-md-6">
                    <a href="{{ route('vista.reportes.compras-unidad') }}" class="btn btn-default">Restablecer</a>
                    {!! Form::submit('Búsqueda', ['class' => 'btn btn-primary', 'name' => 'action']) !!}
                    {!! Form::submit('Exportar excel', ['class' => 'btn btn-info', 'name' => 'action']) !!}
                    {!! Form::submit('Exportar PDF', ['class' => 'btn btn-danger', 'name' => 'action']) !!}
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
                                        {!! Form::checkbox('Movimiento', '0', true, ['id' => 'checkMovimiento']) !!}
                                        {!! Form::label('checkMovimiento', 'Movimiento', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Unidad', '1', true, ['id' => 'checkUnidad']) !!}
                                        {!! Form::label('checkUnidad', 'Unidad', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Almacen', '2', true, ['id' => 'checkAlmacen']) !!}
                                        {!! Form::label('checkAlmacen', 'Almacén', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Sucursal', '3', true, ['id' => 'checkSucursal']) !!}
                                        {!! Form::label('checkSucursal', 'Sucursal', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Fecha', '4', true, ['id' => 'checkFecha']) !!}
                                        {!! Form::label('checkFecha', 'Fecha', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Moneda', '5', true, ['id' => 'checkMoneda']) !!}
                                        {!! Form::label('checkMoneda', 'Moneda', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Estatus', '6', true, ['id' => 'checkEstatus']) !!}
                                        {!! Form::label('checkEstatus', 'Estatus', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('TotalUnidad', '7', false, ['id' => 'checkTotalUnidad']) !!}
                                        {!! Form::label('checkTotalUnidad', 'Total Unidad', ['class' => 'negrita']) !!}
                                    </div>
                                </li>



                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Total', '8', false, ['id' => 'checkTotal']) !!}
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
                                    <th>Movimiento</th>
                                    <th>Unidad</th>
                                    <th>Almacén</th>
                                    <th>Sucursal</th>
                                    <th>Fecha</th>
                                    <th>Moneda</th>
                                    <th>Estatus</th>
                                    <th>Total Unidad</th>

                                    <th>Importe</th>


                                </tr>
                            </thead>

                            <tbody>


                                @if (session()->has('reportes_filtro_array'))
                                    @foreach (session('reportes_filtro_array') as $compra)
                                        @include('include.reportes.reporteComprasUnidad')
                                    @endforeach
                                @else
                                    @foreach ($compras as $compra)
                                        @include('include.reportes.reporteComprasUnidad')
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
