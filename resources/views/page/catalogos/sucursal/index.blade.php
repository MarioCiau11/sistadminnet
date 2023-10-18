@extends('layouts.layout')


@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class="media display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                    <i class="fa fa-building-o"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Sucursales</li>
                    </ul>
                    <h4>Sucursales</h4>
                    <div class="breadcrumb">
                        <span>Configura la información de tus sucursales y adapta ERPNET a tu empresa.</span>
                    </div>
                </div>
        </div>
            <div class="object-create"> 
                <a href="{{route("catalogo.sucursal.create")}}" class="btn btn-success">Crear sucursal</a>
            </div>
        </div><!-- media -->
    </div><!-- pageheader -->

    <div class="contentpanel">
        <div class="row row-stat">
            {!! Form::open(['route' => 'catalogo.sucursal.filtro', 'method'=>'POST']) !!}
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('keySucursal', 'Clave', array('class' => 'negrita')) !!}
                    {!! Form::text('keySucursal',
                    session()->has('keySucursal') ? session()->get('keySucursal') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-8"> 
                <div class="form-group">
                    {!! Form::label('nameSucursal', 'Nombre', array('class' => 'negrita')) !!}
                    {!! Form::text('nameSucursal',
                    session()->has('nameSucursal') ? session()->get('nameSucursal') : null,['class'=>'form-control']) !!}
                </div>
            </div>


            <div class="col-md-1"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja', 'Todos' => 'Todos'], 
                    session()->has('status') ? session()->get('status') : 'Alta', array('id' => 'select-search-hide', "class" => 'widthAll select-status')) !!} 
                </div>
            </div>

            <div class="col-md-6"> 

                <a href="{{route('catalogo.sucursal.index')}}" class="btn btn-default">Restablecer</a>
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
                                    {!!Form::checkbox('clave', '1', true, ['id' => 'checkClave'])!!}
                                    {!!Form::label('checkClave', 'Clave', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('nombre', '2', true, ['id' => 'checkNombre'])!!}
                                    {!!Form::label('checkNombre', 'Nombre', array('class' => 'negrita'))!!}
                                </div>
                            </li>

                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('nombreEmpresa', '3', false, ['id' => 'checkNombreEmpresa'])!!}
                                    {!!Form::label('checkNombreEmpresa', 'Nombre Empresa', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('direccion', '4', false, ['id' => 'checkDireccion'])!!}
                                    {!!Form::label('checkDireccion', 'Dirección', array('class' => 'negrita'))!!}
                                </div>
                            </li>

                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('colonia', '5', false, ['id' => 'checkColonia'])!!}
                                    {!!Form::label('checkColonia', 'Colonia', array('class' => 'negrita'))!!}
                                </div>
                            </li>

                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('codigoPostal', '6', false, ['id' => 'checkCodigoPostal'])!!}
                                    {!!Form::label('checkCodigoPostal', 'Código Postal', array('class' => 'negrita'))!!}
                                </div>
                            </li>

                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('ciudad', '7',false, ['id' => 'checkCiudad'])!!}
                                    {!!Form::label('checkCiudad', 'Ciudad', array('class' => 'negrita'))!!}
                                </div>
                            </li>

                             <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('estado', '8', false, ['id' => 'checkEstado'])!!}
                                    {!!Form::label('checkEstado', 'Estado', array('class' => 'negrita'))!!}
                                </div>
                            </li>

                             <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('pais', '9', false, ['id' => 'checkPais'])!!}
                                    {!!Form::label('checkPais', 'País', array('class' => 'negrita'))!!}
                                </div>
                            </li>

                             <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('estatus', '10', false, ['id' => 'checkEstatus'])!!}
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
                        <thead>
                            <tr>
                                
                                <th>Opciones</th>
                                <th>Clave</th>
                                <th>Nombre</th>
                                <th>Nombre Empresa</th>
                                <th>Dirección</th>
                                <th>Colonia</th>
                                <th>Código Postal</th>
                                <th>Ciudad</th>
                                <th>Estado</th>
                                <th>País</th>
                                <th>Estatus</th>
                                
                            </tr>
                        </thead>
                 
                        <tbody>
                        {{-- En el controlador se pasa la data filtrada y se le enviamos al index como json_encode y en la vista usamos json_decode para
                        manipular los datos. --}}
                        
                        @if(session()->has('sucursal_filtro'))
                           @foreach (session('sucursal_filtro') as $sucursal)
                                @include('include.Catalogos.sucursalesItem')
                           @endforeach
                        @else
                            @foreach ($sucursales as $sucursal)
                                @include('include.Catalogos.sucursalesItem')
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
    jQuery(document).ready(function () {
       jQuery('#select-search-hide').select2({
                   minimumResultsForSearch: -1
        });
    });
</script>

@include('include.mensaje')
@endsection