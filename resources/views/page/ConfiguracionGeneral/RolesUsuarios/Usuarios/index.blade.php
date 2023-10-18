@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class="media display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                     <i class="fa fa-users" aria-hidden="true"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Usuarios</li>
                    </ul>
                    <h4>Usuarios</h4>
                    <div class="breadcrumb">
                        <span>Crea y administra los usuarios que tendrán acceso al sistema.</span>
                    </div>
                </div>
        </div>
            <div class="object-create"> 
                <a href="{{route("configuracion.usuarios.create")}}" class="btn btn-success">Crear Usuario</a>
            </div>
        </div><!-- media -->
    </div><!-- pageheader -->

    <div class="contentpanel">
        <div class="row row-stat">
            {!! Form::open(['route' => 'configuracion.usuarios.filtro', 'method'=>'POST']) !!}
            <div class="col-md-5">
                <div class="form-group">
                    {!! Form::label('nombre', 'Nombre', array('class' => 'negrita')) !!}
                    {!! Form::text('nombre',
                    session()->has('nombre') ? session()->get('nombre') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('user', 'Usuario', array('class' => 'negrita')) !!}
                    {!! Form::text('user',
                    session()->has('user') ? session()->get('user') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-2"> 
                <div class="form-group">
                    {!! Form::label('rol', 'Rol', array('class' => 'negrita')) !!}
                    {!! Form::select('rol', $select_roles, session()->has('rol') ? session()->get('rol') : null, array('id' => 'select-search-roles', "class" => 'widthAll select-status', 'placeholder' => 'Todos')) !!} 
                </div>
            </div>     

            <div class="col-md-1"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja',  'Todos' => 'Todos'], session()->has('status') ? session()->get('status') : 'Alta', array('id' => 'select-search-hide', "class" => 'widthAll select-status')) !!} 
                </div>
            </div>


            <div class="col-md-6"> 
                <a href="{{route('configuracion.usuarios.index')}}" class="btn btn-default">Restablecer</a>
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
                                    {!!Form::checkbox('username', '2', true, ['id' => 'checkusername'])!!}
                                    {!!Form::label('checkusername', 'Usuario', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('email', '3', false, ['id' => 'checkemail'])!!}
                                    {!!Form::label('checkemail', 'Correo Electrónico', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('rol', '4', true, ['id' => 'checkrol'])!!}
                                    {!!Form::label('checkrol', 'Rol', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('status', '5', false, ['id' => 'checkstatus'])!!}
                                    {!!Form::label('checkstatus', 'Estatus', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('bloq', '6', false, ['id' => 'checkbloq'])!!}
                                    {!!Form::label('checkbloq', 'Bloquear', array('class' => 'negrita'))!!}
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
                                <th>Usuario</th>
                                <th>Correo Electrónico</th>
                                <th>Rol</th>
                                <th>Estatus</th>
                                <th>Bloquear Precios Venta</th>

                               
                            </tr>
                        </thead>
                 
                        <tbody>

                            @if(session()->has('user_filtro_array'))
                            @foreach (session('user_filtro_array') as $user)
                                 @include('include.ConfiguracionGeneral.usuariosItem')
                            @endforeach
                         @else
                             @foreach ($user_array as $user)
                                 @include('include.ConfiguracionGeneral.usuariosItem')
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
        jQuery('#select-search-hide, #select-search-roles').select2({
                    minimumResultsForSearch: -1
        });
        
    });
</script>

@endsection