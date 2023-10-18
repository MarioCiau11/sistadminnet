@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class="media display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                     <i class="fa fa-wrench" aria-hidden="true"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Roles</li>
                    </ul>
                    <h4>Roles</h4>
                    <div class="breadcrumb">
                        <span>Crea y administra los roles o perfiles de usuario que usarás en el sistema, para controlar los permisos de los usuarios.</span>
                    </div>
                </div>
        </div>
            <div class="object-create"> 
                <a href="{{route("configuracion.roles.create")}}" class="btn btn-success">Crear Rol</a>
            </div>
        </div><!-- media -->
    </div><!-- pageheader -->

    <div class="contentpanel">
        <div class="row row-stat">
            {!! Form::open(['route' => 'configuracion.roles.filtro', 'method'=>'POST']) !!}
            <div class="col-md-11">
                <div class="form-group">
                    {!! Form::label('nombre', 'Nombre', array('class' => 'negrita')) !!}
                    {!! Form::text('nombre',
                    session()->has('nombre') ? session()->get('nombre') : null
                    ,['class'=>'form-control']) !!}
                </div>
            </div>


            <div class="col-md-1"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja', 'Todos' => 'Todos'], 
                    session()->has('status') ? session()->get('status') : 'Alta'                    
                    , array('id' => 'select-search-hide', "class" => 'widthAll select-status')) !!} 
                </div>
            </div>


            <div class="col-md-6"> 

                <a href="{{route('configuracion.roles.index')}}" class="btn btn-default">Restablecer</a>
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
                                    {!!Form::label('checkName', 'Nombre', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('identificador', '2', true, ['id' => 'checkIdentificador'])!!}
                                    {!!Form::label('checkIdentificador', 'Identificador', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('descripcion', '3', false, ['id' => 'checkDescripcion'])!!}
                                    {!!Form::label('checkDescripcion', 'Descripción', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Estatus', '4', false, ['id' => 'checkEstatus'])!!}
                                    {!!Form::label('checkEstatus', 'Estatus', array('class' => 'negrita'))!!}
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
                                <th>Nombre</th>
                                <th>Identificador</th>
                                <th>Descripción</th>
                                <th>Estatus</th>
                            </tr>
                        </thead>
                 
                        <tbody>
                            
                            @if(session()->has('roles_filtro_array'))
                               @foreach (session('roles_filtro_array') as $rol)
                                    @include('include.ConfiguracionGeneral.roles')
                               @endforeach
                            @else
                                @foreach ($roles as $rol)
                                @include('include.ConfiguracionGeneral.roles')
                                @endforeach 
                            @endif
                        </tbody>
                    </table>
                </div><!-- panel -->
                
            </div>

        </div>
    <div>
</div>


<script>
    jQuery(document).ready(function (){
        jQuery('#select-search-hide').select2({
                    minimumResultsForSearch: -1
        });
    });
</script>
@include('include.mensaje')

@endsection