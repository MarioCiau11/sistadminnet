@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="pageheader">
            <div class="media display-space-between">
                <div>
                    <div class="pageicon pull-left mr10">
                        <span class="fa-solid fa-file-waveform"></span>
                    </div>
                    <div class="media-body">
                        <ul class="breadcrumb">
                            <li><a href="{{ route('dashboard.index') }}"><i class="glyphicon glyphicon-home"></i></a></li>
                            <li>Antiguedad de Saldos</li>
                        </ul>
                        <h4>Antiguedad de Saldos</h4>
                    </div>
                </div>

            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open(['route' => 'reportes.cxc.antiguedad-saldos.filtro', 'method' => 'POST', 'id' => 'formValidate']) !!}

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('nameClienteUno', 'Del Cliente', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameClienteUno', $clientes,  session()->has('nameClienteUno') ? session()->get('nameClienteUno') : null,
                            ['class' => 'widthAll select-movement select-search-hided'],
                        ) !!}
                    </div>
                </div>

                
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('nameClienteDos', 'Al Cliente', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameClienteDos', $clientes,  session()->has('nameClienteDos') ? session()->get('nameClienteDos') : null,
                            ['class' => 'widthAll select-movement select-search-hided'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('nameCliente', 'Buscar Cliente', ['class' => 'negrita']) !!}
                        {!! Form::text('nameCliente', session()->has('nameCliente') ? session()->get('nameCliente') : null, [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-2"> 
                    <div class="form-group">
                        {!! Form::label('nameCategoria', 'Categoría', array('class' => 'negrita')) !!}
                        {!! Form::select(
                            'nameCategoria', $categorias, session()->has('nameCategoria') ? session()->get('nameCategoria') : null, 
                            ['class' => 'widthAll select-movement select-search-hided'],
                            ) !!} 
                    </div>
                </div> 
    
                <div class="col-md-2"> 
                    <div class="form-group">
                        {!! Form::label('nameGrupo', 'Grupo', array('class' => 'negrita')) !!}
                        {!! Form::select('nameGrupo', $grupos, session()->has('nameGrupo') ? session()->get('nameGrupo') : null, 
                            ['class' => 'widthAll select-movement select-search-hided'],
        
                        ) !!} 
                    </div>
                </div> 

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('namePlazo', 'Plazo', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'namePlazo',
                            ['Todos' => 'Todos',
                            'A partir del 1 al 15' => 'A partir del 1 al 15',
                            'A partir del 16 al 30' => 'A partir del 16 al 30',
                            'A partir del 31 al 60' => 'A partir del 31 al 60',
                            'A partir del 61 al 90' => 'A partir del 61 al 90',
                            'Más de 90 días' => 'Más de 90 días'],
                            session()->has('namePlazo') ? session()->get('namePlazo') : null,
                            ['class' => 'widthAll select-movement select-search-hided'],
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

                <div class="col-md-12">

                </div>




                <div class="col-md-6">
                    <a href="{{ route('vista.reportes.cxc-antiguedad-saldos') }}" class="btn btn-default">Restablecer</a>
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
                                        {!! Form::label('checkMovimiento', 'Cliente', ['class' => 'negrita']) !!}
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
                                        {!! Form::checkbox('Movimiento2', '3', true, ['id' => 'checkMovimiento2']) !!}
                                        {!! Form::label('checkMovimiento2', 'Movimiento', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Cliente', '4', true, ['id' => 'checkCliente']) !!}
                                        {!! Form::label('checkCliente', 'Fecha', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Concepto', '5', true, ['id' => 'checkConcepto']) !!}
                                        {!! Form::label('checkConcepto', 'Sucursal', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Sucursal', '6', true, ['id' => 'checkSucursal']) !!}
                                        {!! Form::label('checkSucursal', 'Fecha de vencimiento', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Fecha', '7', true, ['id' => 'checkFecha']) !!}
                                        {!! Form::label('checkFecha', 'Moneda', ['class' => 'negrita']) !!}
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
                                    <th style="text-align: center">Cliente</th>
                                    <th style="text-align: center">Categoría</th>
                                    <th style="text-align: center">Grupo</th>
                                    <th style="text-align: center">Movimiento</th>
                                    <th style="text-align: center">Fecha de Expedición</th>
                                    <th style="text-align: center">Sucursal</th>
                                    <th style="text-align: center">Fecha de vencimiento</th>
                                    <th style="text-align: center">Moneda</th>


                                </tr>
                            </thead>

                            <tbody>


                                @if (session()->has('reportes_filtro_array'))
                                    @foreach (session('reportes_filtro_array') as $cxc)
                                        @include('include.reportes.reporteCXCAntiguedadSaldos')
                                    @endforeach
                                @else
                                    @foreach ($cuentasxcobrar as $cxc)
                                        @include('include.reportes.reporteCXCAntiguedadSaldos')
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
