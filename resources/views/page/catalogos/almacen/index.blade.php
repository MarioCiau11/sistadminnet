@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class="media display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Almacenes</li>
                    </ul>
                    <h4>Almacenes</h4>
                    <div class="breadcrumb">
                        <span>Crea y administra tus almacenes para distribuir y gestionar tu inventario.</span>
                    </div>
                </div>
        </div>
            <div class="object-create"> 
                <a href="{{route("catalogo.almacen.create")}}" class="btn btn-success">Crear almacén</a>
            </div>
        </div><!-- media -->
    </div><!-- pageheader -->

    <div class="contentpanel">
        <div class="row row-stat">
            {!! Form::open(['route' => 'catalogo.almacen.filtro', 'method'=>'POST']) !!}
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('keyAlmacen', 'Clave', array('class' => 'negrita')) !!}
                    {!! Form::text('keyAlmacen',
                    session()->has('keyAlmacen') ? session()->get('keyAlmacen') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-8"> 
                <div class="form-group">
                    {!! Form::label('nameAlmacen', 'Nombre', array('class' => 'negrita')) !!}
                    {!! Form::text('nameAlmacen',
                    session()->has('nameAlmacen') ? session()->get('nameAlmacen') : null,['class'=>'form-control']) !!}
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
                <a href="{{route('catalogo.almacen.index')}}" class="btn btn-default">Restablecer</a>
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
                                    {!!Form::checkbox('sucursal', '3', true, ['id' => 'checkSucursal'])!!}
                                    {!!Form::label('checkSucursal', 'Sucursal', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('type', '4', true, ['id' => 'checkType'])!!}
                                    {!!Form::label('checkType', 'Tipo', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('estatus', '5', false, ['id' => 'checkEstatus'])!!}
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
                                <th>Sucursal</th>
                                <th>Tipo</th>
                                <th>Estatus</th>
                               
                            </tr>
                        </thead>
                 
                        <tbody>
                            @if(session()->has('almacen_filtro_array'))
                                @foreach((session('almacen_filtro_array')) as $almacen)
                                @include('include.Catalogos.almacenesItem')
                                @endforeach
                                @else
                                @foreach($almacenes as $almacen)
                                @include('include.Catalogos.almacenesItem')
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
    });
</script>

@endsection