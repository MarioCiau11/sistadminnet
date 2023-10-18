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
                            <li>Ventas Acumuladas por Artículo y Cliente</li>
                        </ul>
                        <h4>Ventas Acumuladas por Artículo y Cliente</h4>
                    </div>
                </div>

            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open([
                    'route' => 'reportes.ventas.acumulado-cliente.filtro',
                    'method' => 'POST',
                    'id' => 'formValidate',
                ]) !!}


                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('nameClienteUno', 'Del Cliente', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameClienteUno', $clientes,  session()->has('nameClienteUno') ? session()->get('nameClienteUno') : null,
                            ['class' => 'widthAll select-movement select-search-hided'],
                        ) !!}
                    </div>
                </div>


                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('nameClienteDos', 'Al Cliente', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameClienteDos', $clientes,  session()->has('nameClienteDos') ? session()->get('nameClienteDos') : null,
                            ['class' => 'widthAll select-movement select-search-hided'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('nameCategoria', 'Categoría', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameCategoria',
                            $categorias,
                            session()->has('nameCategoria') ? session()->get('nameCategoria') : null,
                            ['class' => 'widthAll select-movement select-search-hided'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('nameGrupo', 'Grupo', ['class' => 'negrita']) !!}
                        {!! Form::select('nameGrupo', $grupos, session()->has('nameGrupo') ? session()->get('nameGrupo') : null, [
                            'class' => 'widthAll select-movement select-search-hided',
                        ]) !!}
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

                
                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('nameEjercicio', 'Ejercicio', ['class' => 'negrita']) !!}
                        
                        {!! Form::select('nameEjercicio', $select_anio, session()->has('nameEjercicio') ? session()->get('nameEjercicio') : $select_anio_actual, [
                            'class' => 'widthAll select-movement select-search-hided',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameArticuloUno', 'Del Artículo', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameArticuloUno', $articulos,  session()->has('nameArticuloUno') ? session()->get('nameArticuloUno') : null,
                            ['class' => 'widthAll select-movement select-search-hided'],
                        ) !!}
                    </div>
                </div>


                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameArticuloDos', 'Al Artículo', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameArticuloDos', $articulos,  session()->has('nameArticuloDos') ? session()->get('nameArticuloDos') : null,
                            ['class' => 'widthAll select-movement select-search-hided'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('categoria', 'Categoria', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'categoria', $select_categoria,  session()->has('categoria') ? session()->get('categoria') : null,
                            ['class' => 'widthAll select-movement select-search-hided'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('grupo', 'Grupo', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'grupo', $select_grupo,  session()->has('grupo') ? session()->get('grupo') : null,
                            ['class' => 'widthAll select-movement select-search-hided'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('familia', 'Familia', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'familia', $select_familia,  session()->has('familia') ? session()->get('familia') : null,
                            ['class' => 'widthAll select-movement select-search-hided'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-12"></div>



                <div class="col-md-6">
                    <a href="{{ route('vista.reportes.ventas-acumulado-cliente') }}"
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
                                        {!! Form::checkbox('Proveedor', '0', true, ['id' => 'checkProveedor']) !!}
                                        {!! Form::label('checkProveedor', 'Cliente', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Categoria', '1', true, ['id' => 'checkCategoria']) !!}
                                        {!! Form::label('checkCategoria', 'Categoria', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Grupo', '2', true, ['id' => 'checkGrupo']) !!}
                                        {!! Form::label('checkGrupo', 'Grupo', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Movimiento', '3', true, ['id' => 'checkMovimiento']) !!}
                                        {!! Form::label('checkMovimiento', 'Movimiento', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Articulo', '4', true, ['id' => 'checkArticulo']) !!}
                                        {!! Form::label('checkArticulo', 'Artículo', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Unidad', '5', true, ['id' => 'checkUnidad']) !!}
                                        {!! Form::label('checkUnidad', 'Categoría', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Fecha', '6', true, ['id' => 'checkFecha']) !!}
                                        {!! Form::label('checkFecha', 'Grupo', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Almacen', '7', true, ['id' => 'checkAlmacen']) !!}
                                        {!! Form::label('checkAlmacen', 'Familia', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Sucursal', '8', false, ['id' => 'checkSucursal']) !!}
                                        {!! Form::label('checkSucursal', 'Fecha', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Moneda', '9', true, ['id' => 'checkMoneda']) !!}
                                        {!! Form::label('checkMoneda', 'Ejercicio', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Moneda', '10', true, ['id' => 'checkMoneda1']) !!}
                                        {!! Form::label('checkMoneda1', 'Moneda', ['class' => 'negrita']) !!}
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
                                    <th>Cliente</th>
                                    <th>Categoría</th>
                                    <th>Grupo</th>
                                    <th>Movimiento</th>
                                    <th>Artículo</th>
                                    <th>Categoría</th>
                                    <th>Grupo</th>
                                    <th>Familia</th>
                                    <th>Fecha</th>
                                    <th>Ejercicio</th>
                                    <th>Moneda</th>
                                    


                                </tr>
                            </thead>
                            <tbody>


                                @if (session()->has('reportes_filtro_array'))
                                    @foreach (session('reportes_filtro_array') as $compra)
                                    @include('include.reportes.reporteVentasArticuloProveedor')

                                    @endforeach
                                @else
                                    @foreach ($ventas as $compra)
                                    @include('include.reportes.reporteVentasArticuloProveedor')

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

                    jQuery('.select-search-hide, .select-search-hided').select2();

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
