@extends('layouts.layout')


@section('content')
    <div class="mainpanel">
        <div class="pageheader">
            <div class="media display-space-between">
                <div>
                    <div class="pageicon pull-left mr10">
                        <i class="fa fa-credit-card" aria-hidden="true"></i>
                    </div>
                    <div class="media-body">
                        <ul class="breadcrumb">
                            <li><a href="{{ route('dashboard.index') }}"><i class="glyphicon glyphicon-home"></i></a></li>
                            <li>Formas de Pago y Cobro</li>
                        </ul>
                        <h4>Formas de Pago y Cobro</h4>
                        <div class="breadcrumb">
                            <span>Crea y administra las formas de pago y cobranza que vas a utilizar en tus transacciones o procesos operativos.</span>
                        </div>
                    </div>
                </div>
                <div class="object-create">
                    <a href="{{ route('configuracion.formas-pago.create') }}" class="btn btn-success">Crear forma de
                        pago</a>
                </div>
            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open(['route' => 'configuracion.formas-pago.filtro', 'method' => 'POST']) !!}
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('keyFormaPago', 'Clave', ['class' => 'negrita']) !!}
                        {!! Form::text('keyFormaPago', session()->has('keyForma') ? session('keyForma') : null, [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label('nameFormaPago', 'Nombre', ['class' => 'negrita']) !!}
                        {!! Form::text('nameFormaPago', session()->has('nameForma') ? session('nameForma') : null, [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-1">
                    <div class="form-group">
                        {!! Form::label('status', 'Estatus', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'status',
                            ['Alta' => 'Alta', 'Baja' => 'Baja', 'Todos' => 'Todos'],
                            session()->has('status') ? session('status') : 'Alta',
                            ['id' => 'select-search-hide', 'class' => 'widthAll select-status'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <a href="{{ route('configuracion.formas-pago.index') }}" class="btn btn-default">Restablecer</a>
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
                                        {!! Form::checkbox('clave', '1', true, ['id' => 'checkClave']) !!}
                                        {!! Form::label('checkClave', 'Clave', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('nombre', '2', true, ['id' => 'checkNombre']) !!}
                                        {!! Form::label('checkNombre', 'Nombre', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('descripcion', '3', true, ['id' => 'checkDescripcion']) !!}
                                        {!! Form::label('checkDescripcion', 'Descripción', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('moneda', '4', true, ['id' => 'checkMoneda']) !!}
                                        {!! Form::label('checkMoneda', 'Moneda', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('formaPago', '5', false, ['id' => 'checkFormaPago']) !!}
                                        {!! Form::label('checkFormaPago', 'Forma de Pago', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('estatus', '6', false, ['id' => 'checkEstatus']) !!}
                                        {!! Form::label('checkEstatus', 'Estatus', ['class' => 'negrita']) !!}
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
                                    <th>Opciones</th>
                                    <th>Clave</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Moneda</th>
                                    <th>Forma de Pago</th>
                                    <th>Estatus</th>

                                </tr>
                            </thead>
                            <tbody>
                                @if (session()->has('formas_filtro_array'))
                                    @foreach (session('formas_filtro_array') as $forma)
                                        @include('include.ConfiguracionGeneral.formasItem')
                                    @endforeach
                                @else
                                    @foreach ($formas_array as $forma)
                                        @include('include.ConfiguracionGeneral.formasItem')
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
                    jQuery('#select-search-hide').select2({
                        minimumResultsForSearch: -1
                    });
                });
            </script>

        @endsection
