@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="pageheader">
            <div class="media display-space-between">
                <div>
                    <div class="pageicon pull-left mr10">
                        <span class="fa-solid fa-code-compare"></span>
                    </div>
                    <div class="media-body">
                        <ul class="breadcrumb">
                            <li><a href="{{ route('dashboard.index') }}"><i class="glyphicon glyphicon-home"></i></a></li>
                            <li>Ventas vs Ganancia</li>
                        </ul>
                        <h4>Ventas vs Ganancia</h4>
                    </div>
                </div>
            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open([
                    'route' => 'reportes.ventas.ganancia.filtro', 
                    'method' => 'POST',
                    'id' => 'formValidate',
                    ]) !!}
                
                <div class="col-md-4">
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
                        {!! Form::label('nameAgrupador', 'Agrupador de Artículo', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameAgrupador',
                            [
                                'Categoría' => 'Categoría',
                                'Grupo' => 'Grupo',
                                'Familia' => 'Familia',
                            ],
                            session()->has('nameAgrupador') ? session()->get('nameAgrupador') : 'Categoría',
                            [
                                'class' => 'widthAll select-status select-search-hide'
                            ],
                        ) !!}

                    </div>
                </div>

                <div class="col-md-12">
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
                    

                    <a href="{{ route('vista.reportes.ventas-ganancia') }}" class="btn btn-default">Restablecer</a>
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
                                        {!! Form::checkbox('Cliente', '0', true, ['id' => 'checkCliente']) !!}
                                        {!! Form::label('checkCliente', 'Sucursal', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Salary', '1', true, ['id' => 'checkSalary']) !!}
                                        {!! Form::label('checkSalary', 'Fecha', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('name', '2', true, ['id' => 'checkName']) !!}
                                        {!! Form::label('checkName', 'Artículo', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                    @if (session()->get('nameAgrupador') == 'Categoría')
                                    <li>
                                    
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('position', '3', true, ['id' => 'checkPosition']) !!}
                                            {!! Form::label('checkPosition', 'Categoría', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('office', '4', false, ['id' => 'checkOffice']) !!}
                                            {!! Form::label('checkOffice', 'Grupo', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('Date', '5', false, ['id' => 'checkDate']) !!}
                                            {!! Form::label('checkDate', 'Familia', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    @elseif (session()->get('nameAgrupador') == 'Grupo')
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('position', '3', false, ['id' => 'checkPosition']) !!}
                                            {!! Form::label('checkPosition', 'Categoría', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('office', '4', true, ['id' => 'checkOffice']) !!}
                                            {!! Form::label('checkOffice', 'Grupo', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('Date', '5', false, ['id' => 'checkDate']) !!}
                                            {!! Form::label('checkDate', 'Familia', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    @elseif (session()->get('nameAgrupador') == 'Familia')
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('position', '3', false, ['id' => 'checkPosition']) !!}
                                            {!! Form::label('checkPosition', 'Categoría', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('office', '4', false, ['id' => 'checkOffice']) !!}
                                            {!! Form::label('checkOffice', 'Grupo', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="ckbox ckbox-primary">
                                            {!! Form::checkbox('Date', '5', true, ['id' => 'checkDate']) !!}
                                            {!! Form::label('checkDate', 'Familia', ['class' => 'negrita']) !!}
                                        </div>
                                    </li>
                                    @endif


                            </ul>
                        </div>
                    </div>
                </div>


                <div class="col-md-12">
                    <div class="panel table-panel">
                        <table id="shTable" class="table table-striped table-bordered widthAll">
                            <thead class="">
                                <tr>
                                    <th>Sucursal</th>
                                    <th>Fecha de Emisión</th>
                                    <th>Artículo</th>
                                    <th>Categoría</th>
                                    <th>Grupo</th>
                                    <th>Familia</th>
                                </tr>
                            </thead>

                            <tbody>


                                @if (session()->has('reportes_filtro_array'))
                                    @foreach (session('reportes_filtro_array') as $venta)
                                        @include('include.reportes.reporteVentasGanancia')
                                    @endforeach
                                @else
                                    @foreach ($ventas as $venta)
                                        @include('include.reportes.reporteVentasGanancia')
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
                            '.select-search-hide, .select-search-hided, .select-movement, .select-status, .select-sucursal, .select-moneda, .select-timbrado')
                        .select2();

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
