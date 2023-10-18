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
                            <h4>Cambio de costos</h4>
                        </div>
                        
                    </div>

                </div><!-- media -->
                
            </div><!-- pageheader -->

            {{-- <ul class="nav nav-justified nav-wizard">
                <li><a href="#tab1-2" data-toggle="tab"><strong>Datos generales - </strong> Movimiento</a></li>
            </ul> --}}
{{-- 
            <div class="progress progress-xs">
                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="45" aria-valuemin="0"
                    aria-valuemax="100"></div>
            </div> --}}

            


            <div class="tab-content">


                <div class="tab-pane active" id="tab1-2">

                    <div class="col-md-10"></div>
                    <div class="col-md-10"></div>

                    <div class="col-md-2">
                        <button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown">
                            Menú de opciones <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu" id="Opciones">
                            <li><a href="" id="btn-procesar">Procesar <span
                                class="glyphicon glyphicon-play pull-right"></span></a></li>
                            <li><a href="{{ route('herramienta.cambioCostos.index') }}" id="nuevo-boton">Nuevo <span
                                class="fa fa-file-o pull-right"></span></a></li>
                        </ul>
                    </div>
                    <div class="col-md-12 cabecera-informacion">
                       

                        <div class="col-md-2">
                            <!-- Movimientos -->
                            <div class="form-group">
                                {!! Form::labelValidacion('listCompras', 'Lista de compras', 'negrita') !!}
                                {!! Form::select(
                                    'listaProveedor',
                                    $listaProveedor,
                                    [],
                                    ['id' => 'listCompras', 'class' => 'widthAll select-movimiento', 'placeholder' => 'Seleccione uno...'],
                                ) !!}
                                   <input type="hidden" name="inputDataArticles" class="form-control" id="inputDataArticles">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="asistente" class="negrita">Asistente</label>
                                <a href="#" class="btn btn-default btn-block" data-toggle="modal" data-target=".modal6" id="btn-asistente">Asistente</a>
                            </div>
                        </div>

                        
                        
                        


                      

                        <div class="col-md-12">
                            <div class="tab-content table-panel">
                                <table id="shTable10" class="table table-striped table-bordered widthAll">
                                    <thead>
                                        <tr>
                                            <th>Artículo</th>
                                            <th>Nombre</th>
                                            <th>Ultimo Costo</th>
                                            <th>Costo promedio</th>
                                            {{-- <th>Enviar</th> --}}
                                            <th>Costo Nuevo</th>
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
                                                {{-- <td><input id="" type="text" class="botonesArticulos"
                                                        disabled></td> --}}
                                                <td><input id="" type="text" class="botonesArticulos"
                                                        disabled></td>
                                            </tr>
                                        </tbody>
                                </table>

                            </div><!-- panel -->
                        </div>


                    </div> {{--  fin cabecera-informacion --}}

                </div> <!-- row tab1-2-->




                {{-- <ul class="list-unstyled wizard" id="botonesWizard">
                    <li class="pull-right finish hide"><button type="submit" class="btn btn-success">Crear
                            Movimiento</button></li>
                </ul> --}}

                {!! Form::close() !!}


            </div>

        </div>
    </div>

</div>

<div class="modal fade bd-example-modal-sm in modal6" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" aria-hidden="true" id="modalCambioCostos">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-header">
                <h5 class="modal-title">Asistente: <strong class="articuloKeySerie"></strong></h5>
            </div>
            <div class="modal-body">
                <input style="display: none" type="hidden" value="" id="clavePosicion" />
                <div class="row" id="form-articulos-serie">

                    <div style="display: flex; justify-content: space-evenly;">
                        <label><input type="radio" name="opciones" id="porcentaje" class="opciones"> Porcentaje</label>
                        <label><input type="radio" name="opciones" id="importe" class="opciones"> Importe</label>
                    </div>

                    <h5 class="text-center mt-3">Base</h5>
                    <div class="col-md-12">
                        <select name="bases" id="bases" class="form-control input-sm">
                            <option value="">Seleccione una base</option>
                            <option value="Ultimo Costo" selected>Ultimo Costo</option>
                            <option value="Costo Promedio">Costo Promedio</option>
                        </select>
                    </div>

                    <div class="col-md-12 text-center mt-3">
                        <label><input type="checkbox" name="positivo" id="positivo" class="indicadores"> +</label>
                        <label><input type="checkbox" name="negativo" id="negativo" class="indicadores"> -</label>
                    </div>

                    <div class="col-md-12 mt-3">
                        <input type="text" class="form-control" placeholder="Valor" id="valor">
                    </div>

                    <div class="col-md-12 mt-3">
                        <button type="button" class="btn btn-primary btn-block" name="btnAplicar" id="btnAplicar">Aplicar</button>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                {{-- <button type="button" class="btn btn-primary" id="generarLotesSeries">Generar Lote de Series</button>
                <button type="button" class="btn btn-success" id="modal6Agregar">Agregar</button> --}}
            </div>
        </div>
    </div>
</div>




<script src="{{ asset('js/HERRAMIENTA/cambioCostos.js') }}"></script>



@endsection
