@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="pageheader">
            <div class="media display-space-between">
                <div>
                    <div class="pageicon pull-left mr10">
                        <span class="fa  fa-dollar"></span>
                    </div>
                    <div class="media-body">
                        <ul class="breadcrumb">
                            <li><a href="{{ route('dashboard.index') }}"><i class="glyphicon glyphicon-home"></i></a></li>
                            <li>Gastos por Antecedentes o Activo Fijo</li>
                        </ul>
                        <h4>Gastos por Antecedentes o Activo Fijo</h4>
                    </div>
                </div>

            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open([
                    'route' => 'reportes.gastos.antecedente-activo-fijo.filtro',
                    'method' => 'POST',
                    'id' => 'formValidate',
                ]) !!}
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameMov', 'Movimiento', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameMov',
                            ['Todos' => 'Todos', 'Reposición Caja' => 'Reposición Caja', 'Factura de Gasto' => 'Factura de Gasto'],
                            session()->has('nameMov') ? session()->get('nameMov') : null,
                            ['class' => 'widthAll select-movement select-mov'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameProveedor', 'Proveedor', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameProveedor',
                            $proveedor,
                            session()->has('nameProveedor') ? session()->get('nameProveedor') : null,
                            ['class' => 'widthAll select-movement select-prov'],
                        ) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameCategoria', 'Categoría', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameCategoria',
                            $categorias,
                            session()->has('nameCategoria') ? session()->get('nameCategoria') : null,
                            ['class' => 'widthAll select-movement select-cat'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameGrupo', 'Grupo', ['class' => 'negrita']) !!}
                        {!! Form::select('nameGrupo', $grupos, session()->has('nameGrupo') ? session()->get('nameGrupo') : null, [
                            'class' => 'widthAll select-movement select-grupo',
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
                                'class' => 'widthAll select-status select-suc',
                            ],
                        ) !!}

                    </div>
                </div>

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
                                'class' => 'widthAll select-status select-fecha',
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
                            session()->has('nameMoneda') ? session()->get('nameMoneda') : $parametro->generalParameters_defaultMoney,
                            [
                                'class' => 'widthAll select-status select-moneda',
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
                            ['class' => 'widthAll select-status select-estatus'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-9 fecha-rango">
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






                <div class="col-md-2">
                    <div class="col-md-2 mt10">
                        {!! Form::label('antecedentes', 'Antecedentes', ['class' => 'negrita']) !!}
                        {!! Form::checkbox('antecedentes', '1', session()->has('antecedentes') ? session()->get('antecedentes') : null, [
                            'class' => 'widthAll select-status select-antecedentes',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="col-md-6 mt10">
                        {!! Form::label('activoFijo', 'Activo Fijo', ['class' => 'negrita']) !!}
                        {!! Form::checkbox('activoFijo', '1', session()->has('activoFijo') ? session()->get('activoFijo') : null, [
                            'class' => 'widthAll select-status select-activoFijo',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-2" id="contenedorAntecedente">
                    <div class="form-group">
                        {!! Form::label('nameAntecedentes', 'Antecedente', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameAntecedentes',
                            $antecedente,
                            session()->has('nameAntecedentes') ? session()->get('nameAntecedentes') : null,
                            [
                                'class' => 'widthAll select-status select-moneda',
                            ],
                        ) !!}
                    </div>
                </div>


                {{-- <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('keyFormaPago', 'Clave', ['class' => 'negrita']) !!}
                        {!! Form::text('keyFormaPago', session()->has('keyForma') ? session('keyForma') : null, [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                </div> --}}

                <div class="col-md-3" id="contenedorActivoFijo">
                    <div class="form-group">
                        {!! Form::label('nameActivoFijo', 'Artículo', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameActivoFijo',
                            $articulo,
                            session()->has('nameActivoFijo') ? session()->get('nameActivoFijo') : null,
                            ['class' => 'widthAll select-movement select-activoFijo', 'id' => 'activoFijoNombre'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-3" id="contenedorActivoFijoSerie">
                    <div class="form-group">
                        {!! Form::label('activoFijoNombreSerie', 'Serie', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'activoFijoNombreSerie',
                            $serie,
                            session()->has('activoFijoNombreSerie') ? session()->get('activoFijoNombreSerie') : null,
                            ['class' => 'widthAll select-movement select-activoFijoSerie', 'id' => 'activoFijoNombreSerie'],
                        ) !!}
                    </div>
                </div>



                <div class="col-md-12"></div>
                <div class="col-md-12"></div>



                <div class="col-md-6">
                    <a href="{{ route('vista.reportes.gastos-antecedente-activo-fijo') }}"
                        class="btn btn-default">Restablecer</a>
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
                                        {!! Form::label('checkUnidad', 'Proveedor', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Categoria', '2', true, ['id' => 'checkCategoria']) !!}
                                        {!! Form::label('checkCategoria', 'Categoria', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Grupo', '3', true, ['id' => 'checkGrupo']) !!}
                                        {!! Form::label('checkGrupo', 'Grupo', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Almacen', '4', true, ['id' => 'checkAlmacen']) !!}
                                        {!! Form::label('checkAlmacen', 'Concepto', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Sucursal', '5', true, ['id' => 'checkSucursal']) !!}
                                        {!! Form::label('checkSucursal', 'Sucursal', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Fecha', '6', true, ['id' => 'checkFecha']) !!}
                                        {!! Form::label('checkFecha', 'Fecha', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Moneda1', '7', true, ['id' => 'checkMoneda1']) !!}
                                        {!! Form::label('checkMoneda1', 'AT/AF', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Moneda', '8', true, ['id' => 'checkMoneda']) !!}
                                        {!! Form::label('checkMoneda', 'Moneda', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Estatus', '9', true, ['id' => 'checkEstatus']) !!}
                                        {!! Form::label('checkEstatus', 'Estatus', ['class' => 'negrita']) !!}
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
                                    <th>Proveedor</th>
                                    <th>Categoria</th>
                                    <th>Grupo</th>
                                    <th>Concepto</th>
                                    <th>Sucursal</th>
                                    <th>Fecha</th>
                                    <th>Antecedente/Activo Fijo</th>
                                    <th>Moneda</th>
                                    <th>Estatus</th>


                                </tr>
                            </thead>

                            <tbody>


                                @if (session()->has('reportes_filtro_array'))
                                    @foreach (session('reportes_filtro_array') as $gasto)
                                        @include('include.reportes.reporteGastosATAF')
                                    @endforeach
                                @else
                                    @foreach ($gastos as $gasto)
                                        @include('include.reportes.reporteGastosATAF')
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
                            '.select-mov, .select-prov, .select-cat, .select-grupo, .select-suc, .select-fecha, .select-moneda, .select-estatus, .select-movement'
                        )
                        .select2();


                    jQuery('.datepicker').datepicker({
                        dateFormat: 'dd-mm-yy',
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
                                // date: true
                            },
                            fechaFinal: {
                                required: true,
                                // date: true
                            }
                        },
                        messages: {
                            fechaInicio: {
                                required: 'Ingrese una fecha de inicio',
                                // date: 'Ingrese una fecha válida'
                            },
                            fechaFinal: {
                                required: 'Ingrese una fecha de fin',
                                // date: 'Ingrese una fecha válida'
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





                    jQuery("#activoFijo").on("change", function(e) {
                        e.preventDefault();
                        if (jQuery("#activoFijo").is(":checked")) {
                            jQuery("#contenedorActivoFijo").show();
                            jQuery("#contenedorActivoFijoSerie").show();

                            jQuery("#activoFijoNombre").focus();
                        } else {
                            jQuery("#contenedorActivoFijo").hide();
                            jQuery("#contenedorActivoFijoSerie").hide();
                        }
                    });
                    jQuery("#antecedentes").on("change", function(e) {
                        if (jQuery("#antecedentes").is(":checked")) {
                            jQuery("#contenedorAntecedente").show();
                            jQuery("#antecedentesName").focus();
                        } else {
                            jQuery("#contenedorAntecedente").hide();
                        }
                    });

                    $("#activoFijo").trigger('change');
                    $("#antecedentes").trigger('change');

                });
            </script>

        @endsection
