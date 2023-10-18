@extends('layouts.layout')


@section('content')
<div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open([
                    'route' => ['herramienta.store'],
                    'id' => 'progressWizard',
                    'class' => 'panel-wizard',
                    'method' => 'POST',
                ]) !!}

                {!! Form::macro('labelValidacion', function ($name, $labelName, $classes) {
                    return "<label for='" .
                        $name .
                        "' class='" .
                        $classes .
                        "'>" .
                        $labelName .
                        "<span class='asterisk'> *</span></label>";
                }) !!}

                <div class="pageheader">
                    <div class="media display-space-between">
                        <div>
                            <div class="pageicon pull-left mr10">
                                <span class="glyphicon glyphicon-wrench"></span>
                            </div>
                            <div class="media-body">
                                <ul class="breadcrumb">
                                    <li><a href="{{ route('dashboard.index') }}"><i class="glyphicon glyphicon-wrench"></i></a></li>
                                    <li>Herramienta</li>
                                </ul>
                                <h4>Movimientos al inventario</h4>
                            </div>
                            
                        </div>

                    </div><!-- media -->
                    
                </div><!-- pageheader -->

                <ul class="nav nav-justified nav-wizard">
                    <li><a href="#tab1-2" data-toggle="tab"><strong>Datos generales - </strong> Movimiento</a></li>
                </ul>

                <div class="progress progress-xs">
                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="45" aria-valuemin="0"
                        aria-valuemax="100"></div>
                </div>

                


                <div class="tab-content">


                    <div class="tab-pane active" id="tab1-2">

                        <div class="col-md-10"></div>
                        <div class="col-md-10"></div>
    
                        <div class="col-md-2">
                            <button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown">
                                Menú de opciones <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu" id="Opciones">
                                <li><a href="#" id="btn-procesar">Procesar <span
                                    class="glyphicon glyphicon-play pull-right"></span></a></li>
                                <li><a href="{{ route('herramienta.index') }}" id="nuevo-boton">Nuevo <span
                                    class="fa fa-file-o pull-right"></span></a></li>
                            </ul>
                        </div>
                        <div class="col-md-12 cabecera-informacion">
                            <div class="col-md-2">
                                <!-- Movimientos -->
                                <div class="form-group">
                                    {!! Form::labelValidacion('empresas', 'Empresa', 'negrita') !!}
                                    {!! Form::select(
                                        'empresas',
                                        $empresas,
                                        isset($empresa) ? $empresa->companies_key : null,
                                        ['id' => 'select-empresa', 'class' => 'widthAll select-movimiento', 'placeholder' => 'Seleccione uno...'],
                                    ) !!}
                                </div>
                            </div>

                            <div class="col-md-2">
                                <!-- Movimientos -->
                                <div class="form-group">
                                    {!! Form::labelValidacion('sucursales', 'Sucursal', 'negrita') !!}
                                    {!! Form::select(
                                        'sucursales',
                                        $sucursales,
                                        isset($sucursalSession) ? $sucursalSession->branchOffices_key : null,
                                        ['id' => 'select-sucursal', 'class' => 'widthAll select-movimiento', 'placeholder' => 'Seleccione uno...'],
                                    ) !!}
                                </div>
                            </div>

                            <div class="col-md-2">
                                <!-- Movimientos -->
                                <div class="form-group">
                                    {!! Form::labelValidacion('almacenes', 'Almacen', 'negrita') !!}
                                    {!! Form::select(
                                        'almacenes',
                                        $almacenes,
                                        null,
                                        ['id' => 'select-almacen', 'class' => 'widthAll select-movimiento', 'placeholder' => 'Seleccione uno...'],
                                    ) !!}
                                </div>
                            </div>

                            <div class="col-md-2">
                                <!-- Movimientos -->
                                <div class="form-group">
                                    {!! Form::labelValidacion('empresaDestino', 'Empresa Destino', 'negrita') !!}
                                    {!! Form::select(
                                        'empresaDestino',
                                        $empresas,
                                        null,
                                        ['id' => 'select-empresaDestino', 'class' => 'widthAll select-movimiento', 'placeholder' => 'Seleccione uno...'],
                                    ) !!}
                                </div>
                            </div>


                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="sucursalDestino" class="negrita">Sucursal Destino<span class='asterisk'>
                                            *</span></label>
                                    <select name="sucursalDestino" id="select-sucursalDestino" class = "widthAll select-movimiento">
                                        <option  value="" hidden>Seleccione uno...</option>
                                        @foreach ($sucursalesDestino as $sucursales)
                                            <option value="{{ $sucursales->branchOffices_key }}">
                                                {{ $sucursales->branchOffices_name }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>


                            <div class="col-md-2">
                                <!-- Movimientos -->
                                <div class="form-group">
                                    {!! Form::labelValidacion('almacenDestino', 'Almacen Destino', 'negrita') !!}
                                    {!! Form::select(
                                        'almacenDestino',
                                        $sucursalesDestino,
                                        null,
                                        ['id' => 'select-almacenDestino', 'class' => 'widthAll select-movimiento', 'placeholder' => 'Seleccione uno...'],
                                    ) !!}
                                    <input type="hidden" name="inputDataArticles" class="form-control" id="inputDataArticles">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="tab-content table-panel">
                                    <table id="shTable1" class="table table-striped table-bordered widthAll">
                                        <thead>
                                            <tr>
                                                <th>Clave</th>
                                                <th>Nombre</th>
                                                <th>Existencia Actual</th>
                                                <th>Enviar</th>
                                                <th>Costo</th>
                                            </tr>
                                        </thead>
                                            <tbody id="articleItem">
                                                <tr id="controlArticulo">
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                    <td><input id="" type="text" class="botonesArticulos"
                                                            disabled></td>
                                                </tr>
                                            </tbody>
                                    </table>

                                </div><!-- panel -->
                            </div>


                        </div> {{--  fin cabecera-informacion --}}

                    </div> <!-- row tab1-2-->




                    <ul class="list-unstyled wizard" id="botonesWizard">
                        <li class="pull-right finish hide"><button type="submit" class="btn btn-success">Crear
                                Movimiento</button></li>
                    </ul>

                    {!! Form::close() !!}


                </div>

            </div>
        </div>

    </div>

    <script>
    window.addEventListener('load', function() {
        jQuery("#select-empresa").attr('readonly', true);
        jQuery("#select-sucursal").attr('readonly', true);
        jQuery("#select-sucursalDestino").attr('readonly', true);
        jQuery("#select-almacenDestino").attr('readonly', true);
    });
    </script>

    <script src="{{ asset('js/HERRAMIENTA/herramienta.js') }}"></script>
@endsection
