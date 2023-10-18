@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class=" display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                     <i class="fa-solid fa-user-secret" aria-hidden="true"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Operativos</li>
                    </ul>
                    <h4>Operativos</h4>
                    <div class="breadcrumb">
                        <span>Crea, edita y administra los operativos de tipo vendedor y choferes.</span>
                    </div>
                </div>
        </div>

            <div class="object-create">
                <div class="btn-group mr5">

                    <a href="{{route("categoriaCreate", ['tipo' => 'Agente'])}}" class="btn btn-default">Crear Categoría</a>
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                      <span class="caret"></span>
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                      <li><a href="{{route("categoriaIndex", ['tipo' => 'Agente'])}}">Ver Categorías</a></li> 
                    </ul>
                  </div>

                  <div class="btn-group mr5">
                    <a href="{{route("grupoCreate", ['tipo' => 'Agente'])}}" class="btn btn-default">Crear Grupo</a>
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                      <span class="caret"></span>
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                      <li><a href="{{route("grupoIndex", ['tipo' => 'Agente'])}}">Ver Grupos</a></li>
                    </ul>
                  </div>
                  <div class="btn-group mr5">
                    <a href="{{route("catalogo.agentes.create")}}" class="btn btn-success">Crear Operativo</a>
                  </div>
            </div>
        </div><!-- media -->
    </div><!-- pageheader -->

    <div class="contentpanel">
        <div class="row row-stat">
            {!! Form::open(['route' => 'catalogo.agentes.filtro', 'method'=>'POST']) !!}
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('keyAgente', 'Clave', array('class' => 'negrita')) !!}
                    {!! Form::text('keyAgente',
                    session()->has('keyAgente') ? session()->get('keyAgente') :null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-3"> 
                <div class="form-group">
                    {!! Form::label('nameAgente', 'Nombre', array('class' => 'negrita')) !!}
                    {!! Form::text('nameAgente',
                    session()->has('nameAgente') ? session()->get('nameAgente') :null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-3"> 
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

            <div class="col-md-1"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja', 'Todos' => 'Todos'],
                    session()->has('status') ? session()->get('status') : 'Alta', array('id' => 'select-search-type', "class" => 'widthAll select-status')) !!} 
                </div>
            </div>

            <div class="col-md-12"></div>

            <div class="col-md-6"> 
                <a href="{{route('catalogo.agentes.index')}}" class="btn btn-default">Restablecer</a>
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
                                    {!!Form::checkbox('key', '1', true, ['id' => 'checKey'])!!}
                                    {!!Form::label('checKey', 'Clave', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('name', '2', true, ['id' => 'checkName'])!!}
                                    {!!Form::label('checkName', 'Nombre', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('type', '3', true, ['id' => 'checkType'])!!}
                                    {!!Form::label('checkType', 'Tipo', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('categoria', '4', true, ['id' => 'checkCategoria'])!!}
                                    {!!Form::label('checkCategoria', 'Categoría', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('grupo', '5', true, ['id' => 'checkGrupo'])!!}
                                    {!!Form::label('checkGrupo', 'Grupo', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('sucursal', '6', false, ['id' => 'checkSucursal'])!!}
                                    {!!Form::label('checkSucursal', 'Sucursal', array('class' => 'negrita'))!!}
                                </div>
                            </li>

                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('status', '6', true, ['id' => 'checkStatus'])!!}
                                    {!!Form::label('checkStatus', 'Estatus', array('class' => 'negrita'))!!}
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
                                <th>Tipo</th>
                                <th>Categoría</th>
                                <th>Grupo</th>
                                <th>Sucursal</th>
                                <th>Estatus</th>
                               
                            </tr>
                        </thead>
                 
                        <tbody>
                            @if(session()->has('agente_filtro_array'))
                                @foreach((session('agente_filtro_array')) as $agente)
                                @include('include.Catalogos.agentesItem')
                                @endforeach
                                @else
                                @foreach($agentes as $agente)
                                @include('include.Catalogos.agentesItem')
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
        jQuery('#select-search-type').select2({
                    minimumResultsForSearch: -1
        });
    });
</script>

@endsection