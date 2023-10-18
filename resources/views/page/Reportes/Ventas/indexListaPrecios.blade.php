@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="pageheader">
            <div class="media display-space-between">
                <div>
                    <div class="pageicon pull-left mr10">
                        <span class="fa-solid fa-list-ol"></span>
                    </div>
                    <div class="media-body">
                        <ul class="breadcrumb">
                            <li><a href="{{ route('dashboard.index') }}"><i class="glyphicon glyphicon-home"></i></a></li>
                            <li>Lista de precios</li>
                        </ul>
                        <h4>Lista de precios</h4>
                    </div>
                </div>

            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open(['route' => 'reportes.listaPrecios.filtro', 'method' => 'POST', 'id' => 'formValidate']) !!}

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('listaPrecio', 'Lista de Precio', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'listaPrecio',
                            [
                                'Precio 1' => 'Precio 1',
                                'Precio 2' => 'Precio 2',
                                'Precio 3' => 'Precio 3',
                                'Precio 4' => 'Precio 4',
                                'Precio 5' => 'Precio 5',
                            ],
                            session()->has('listaPrecio') ? session()->get('listaPrecio') : null,
                            ['class' => 'widthAll select-movement select-search-hided'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('nameFoto', 'Foto', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'nameFoto',
                            [
                                'Si' => 'Si',
                                'No' => 'No',
                            ],
                            session()->has('nameFoto') ? session()->get('nameFoto') : null,
                            ['id' => 'select-search-hided', 'class' => 'widthAll select-status select-search-hide'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-12"></div>

                <div class="col-md-6">
                    <a href="{{ route('vista.reportes.listaPrecios') }}" class="btn btn-default">Restablecer</a>
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
                                        {!! Form::label('checkCliente', 'Artículo', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                @switch(session()->get('listaPrecio'))
                                    @case('Precio 1')
                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 1', '1', true, ['id' => 'checkPrecio1']) !!}
                                                {!! Form::label('checkPrecio1', 'Precio 1', ['class' => 'negrita']) !!}
                                            </div>
                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 2', '2', false, ['id' => 'checkPrecio2']) !!}
                                                {!! Form::label('checkPrecio2', 'Precio 2', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 3', '3', false, ['id' => 'checkPrecio3']) !!}
                                                {!! Form::label('checkPrecio3', 'Precio 3', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 4', '4', false, ['id' => 'checkPrecio4']) !!}
                                                {!! Form::label('checkPrecio4', 'Precio 4', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 5', '5', false, ['id' => 'checkPrecio5']) !!}
                                                {!! Form::label('checkPrecio5', 'Precio 5', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>
                                    @break

                                    @case('Precio 2')
                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 1', '1', false, ['id' => 'checkPrecio1']) !!}
                                                {!! Form::label('checkPrecio1', 'Precio 1', ['class' => 'negrita']) !!}
                                            </div>
                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 2', '2', true, ['id' => 'checkPrecio2']) !!}
                                                {!! Form::label('checkPrecio2', 'Precio 2', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 3', '3', false, ['id' => 'checkPrecio3']) !!}
                                                {!! Form::label('checkPrecio3', 'Precio 3', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 4', '4', false, ['id' => 'checkPrecio4']) !!}
                                                {!! Form::label('checkPrecio4', 'Precio 4', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 5', '5', false, ['id' => 'checkPrecio5']) !!}
                                                {!! Form::label('checkPrecio5', 'Precio 5', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>
                                    @break

                                    @case('Precio 3')
                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 1', '1', false, ['id' => 'checkPrecio1']) !!}
                                                {!! Form::label('checkPrecio1', 'Precio 1', ['class' => 'negrita']) !!}
                                            </div>
                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 2', '2', false, ['id' => 'checkPrecio2']) !!}
                                                {!! Form::label('checkPrecio2', 'Precio 2', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 3', '3', true, ['id' => 'checkPrecio3']) !!}
                                                {!! Form::label('checkPrecio3', 'Precio 3', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 4', '4', false, ['id' => 'checkPrecio4']) !!}
                                                {!! Form::label('checkPrecio4', 'Precio 4', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 5', '5', false, ['id' => 'checkPrecio5']) !!}
                                                {!! Form::label('checkPrecio5', 'Precio 5', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>
                                    @break

                                    @case('Precio 4')
                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 1', '1', false, ['id' => 'checkPrecio1']) !!}
                                                {!! Form::label('checkPrecio1', 'Precio 1', ['class' => 'negrita']) !!}
                                            </div>
                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 2', '2', false, ['id' => 'checkPrecio2']) !!}
                                                {!! Form::label('checkPrecio2', 'Precio 2', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 3', '3', false, ['id' => 'checkPrecio3']) !!}
                                                {!! Form::label('checkPrecio3', 'Precio 3', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 4', '4', true, ['id' => 'checkPrecio4']) !!}
                                                {!! Form::label('checkPrecio4', 'Precio 4', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 5', '5', false, ['id' => 'checkPrecio5']) !!}
                                                {!! Form::label('checkPrecio5', 'Precio 5', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>
                                    @break

                                    @case('Precio 5')
                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 1', '1', false, ['id' => 'checkPrecio1']) !!}
                                                {!! Form::label('checkPrecio1', 'Precio 1', ['class' => 'negrita']) !!}
                                            </div>
                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 2', '2', false, ['id' => 'checkPrecio2']) !!}
                                                {!! Form::label('checkPrecio2', 'Precio 2', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 3', '3', false, ['id' => 'checkPrecio3']) !!}
                                                {!! Form::label('checkPrecio3', 'Precio 3', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 4', '4', false, ['id' => 'checkPrecio4']) !!}
                                                {!! Form::label('checkPrecio4', 'Precio 4', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 5', '5', true, ['id' => 'checkPrecio5']) !!}
                                                {!! Form::label('checkPrecio5', 'Precio 5', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>
                                    @break

                                    @default
                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 1', '1', true, ['id' => 'checkPrecio1']) !!}
                                                {!! Form::label('checkPrecio1', 'Precio 1', ['class' => 'negrita']) !!}
                                            </div>
                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 2', '2', false, ['id' => 'checkPrecio2']) !!}
                                                {!! Form::label('checkPrecio2', 'Precio 2', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 3', '3', false, ['id' => 'checkPrecio3']) !!}
                                                {!! Form::label('checkPrecio3', 'Precio 3', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 4', '4', false, ['id' => 'checkPrecio4']) !!}
                                                {!! Form::label('checkPrecio4', 'Precio 4', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>

                                        <li>
                                            <div class="ckbox ckbox-primary">
                                                {!! Form::checkbox('Precio 5', '5', false, ['id' => 'checkPrecio5']) !!}
                                                {!! Form::label('checkPrecio5', 'Precio 5', ['class' => 'negrita']) !!}
                                            </div>

                                        </li>
                                    @break
                                @endswitch



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
                            <thead>
                                <tr>
                                    <th style="text-align: left">Artículo</th>
                                    <th style="text-align: center">Lista Precio 1</th>
                                    <th style="text-align: center">Lista Precio 2</th>
                                    <th style="text-align: center">Lista Precio 3</th>
                                    <th style="text-align: center">Lista Precio 4</th>
                                    <th style="text-align: center">Lista Precio 5</th>
                                </tr>
                            </thead>

                            <tbody>


                                @if (session()->has('reportes_filtro_array'))
                                    @foreach (session('reportes_filtro_array') as $art)
                                        @include('include.reportes.reporteListaPrecios')
                                    @endforeach
                                @else
                                    @foreach ($articulos as $art)
                                        @include('include.reportes.reporteListaPrecios')
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
