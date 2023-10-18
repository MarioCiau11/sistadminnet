@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class=" display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                     <i class="fa fa-car" aria-hidden="true"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Vehículos</li>
                    </ul>
                    <h4>Vehículos</h4>
                    <div class="breadcrumb">
                        <span>Crea, edita y administra los vehículos/camiones que utilices en los procesos de traslados de inventario.</span>
                    </div>
                </div>
        </div>
            <div class="object-create">
                <div class="btn-group mr5">

                    <a href="{{route("categoriaCreate", ['tipo' => 'Vehiculo'])}}" class="btn btn-default">Crear Categoría</a>
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                      <span class="caret"></span>
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                      <li><a href="{{route("categoriaIndex", ['tipo' => 'Vehiculo'])}}">Ver Categorías</a></li> 
                    </ul>
                  </div>

                  <div class="btn-group mr5">
                    <a href="{{route("grupoCreate", ['tipo' => 'Vehiculo'])}}" class="btn btn-default">Crear Grupo</a>
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                      <span class="caret"></span>
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                      <li><a href="{{route("grupoIndex", ['tipo' => 'Vehiculo'])}}">Ver Grupos</a></li>
                    </ul>
                  </div>
                  <div class="btn-group mr5">
                    <a href="{{route("catalogo.vehiculos.create")}}" class="btn btn-success">Crear Vehículos</a>

                  </div>
            </div>
        </div><!-- media -->
    </div><!-- pageheader -->

    <div class="contentpanel">
        <div class="row row-stat">
            {!! Form::open(['route' => 'catalogo.vehiculos.filtro', 'method'=>'POST']) !!}
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('keyVehiculo', 'Clave', array('class' => 'negrita')) !!}
                    {!! Form::text('keyVehiculo',
                    session()->has('keyVehiculo') ? session()->get('keyVehiculo') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-3"> 
                <div class="form-group">
                    {!! Form::label('nameVehiculo', 'Nombre', array('class' => 'negrita')) !!}
                    {!! Form::text('nameVehiculo',
                    session()->has('nameVehiculo') ? session()->get('nameVehiculo') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-2"> 
                <div class="form-group">
                    {!! Form::label('categoria', 'Categoría', array('class' => 'negrita')) !!}
                    {!! Form::select('categoria', $categoria_array, session()->has('categoria') ? session()->get('categoria') : null, array('id' => 'select-search-hide', "class" => 'widthAll' , 'placeholder' => 'Selecciona...')) !!} 
                </div>
            </div> 

            <div class="col-md-2"> 
                <div class="form-group">
                    {!! Form::label('grupo', 'Grupo', array('class' => 'negrita')) !!}
                    {!! Form::select('grupo', $grupo_array, session()->has('grupo') ? session()->get('grupo') : null, array('id' => 'select-search-grupo', "class" => 'widthAll', 'placeholder' => 'Selecciona...')) !!} 
                </div>
            </div> 

            <div class="col-md-2"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja', 'Todos' => 'Todos'], 
                    session()->has('status') ? session()->get('status') : 'Alta', array('id' => 'select-status', "class" => 'widthAll select-search-type')) !!} 
                </div>
            </div>

            <div class="col-md-12"></div>

            <div class="col-md-6"> 
                <a href="{{route('catalogo.vehiculos.index')}}" class="btn btn-default">Restablecer</a>
                {!!Form::submit('Búsqueda', ['class' => 'btn btn-primary', 'name' => 'action'])!!}
                {!!Form::submit('Exportar excel', ['class' => 'btn btn-info', 'name' => 'action'])!!}
                {!! Form::close() !!}
            </div>

            <div class="col-md-6">
                <div class="btn-columns">
                    <div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-sm mt5 btn-white border dropdown-toggle" type="button">
                           Columnas <span class="caret"></span>
                        </button>
                        <ul role="menu" id="shCol" class="dropdown-menu dropdown-menu-sm pull-right">
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Opciones', '0', true, ['id' => 'checkOpciones'])!!}
                                    {!!Form::label('checkOpciones', 'Opciones', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('name', '1', true, ['id' => 'checkName'])!!}
                                    {!!Form::label('checkName', 'Clave', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('position', '2', true, ['id' => 'checkPosition'])!!}
                                    {!!Form::label('checkPosition', 'Nombre', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('categoria', '3', true, ['id' => 'checkCategoria'])!!}
                                    {!!Form::label('checkCategoria', 'Categoría', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('grupo', '4', true, ['id' => 'checkGrupo'])!!}
                                    {!!Form::label('checkGrupo', 'Grupo', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('office', '5', true, ['id' => 'checkOffice'])!!}
                                    {!!Form::label('checkOffice', 'Placas', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Age', '6', false, ['id' => 'checkAge'])!!}
                                    {!!Form::label('checkAge', 'Volumen', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Date', '7', false, ['id' => 'checkDate'])!!}
                                    {!!Form::label('checkDate', 'Peso', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Salary', '8', true, ['id' => 'checkSalary'])!!}
                                    {!!Form::label('checkSalary', 'Operativo', array('class' => 'negrita'))!!}
                                </div>
                            </li>

                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Salary2', '9', true, ['id' => 'checkSalary2'])!!}
                                    {!!Form::label('checkSalary2', 'Sucursal', array('class' => 'negrita'))!!}
                                </div>
                            </li>

                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('estatus', '10', true, ['id' => 'checkEstatus'])!!}
                                    {!!Form::label('checkEstatus', 'Estatus', array('class' => 'negrita'))!!}
                                </div>
                            </li>  
                        </ul>
                    </div>
                </div>
            </div>


           {{-- En el controlador se pasa la data filtrada y se le enviamos al index como json_encode y en la vista usamos json_decode para
            manipular los datos. --}}
            {{-- @if(session('test'))
               {{  session('test')['name'] }}
            @else
            {{$message}}
            @endif --}}

           
            <div class="col-md-12">
                <div class="panel table-panel">   
                    <table id="shTable" class="table table-striped table-bordered widthAll">
                        <thead class="">
                            <tr>
                                <th>Opciones</th>
                                <th>Clave</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Grupo</th>
                                <th>Placas</th>
                                <th>Capacidad en Volumen</th>
                                <th>Capacidad en Peso</th>
                                <th>Operativo</th>
                                <th>Sucursal</th>
                                <th>Estatus</th>
                                
                            </tr>
                        </thead>
                 
                        <tbody>
                            @if(session()->has('vehiculo_filtro_array'))
                            @foreach((session('vehiculo_filtro_array')) as $vehiculo)
                            @include('include.Catalogos.vehiculosItem')
                            @endforeach
                            @else
                            @foreach($vehiculos as $vehiculo)
                            @include('include.Catalogos.vehiculosItem')
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
    jQuery(document).ready(function (){
        jQuery('#select-search-hide').select2({
                    minimumResultsForSearch: -1
        });

        jQuery('#select-search-grupo').select2({
                    minimumResultsForSearch: -1
        });
        jQuery('#select-status').select2({
                    minimumResultsForSearch: -1
        });
        jQuery('#select-search-type').select2({
                    minimumResultsForSearch: -1
        });
    });
</script>

@endsection