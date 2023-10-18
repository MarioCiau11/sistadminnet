@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class="media display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                     <i class="fa fa-info" aria-hidden="true"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Unidades de Medida</li>
                    </ul>
                    <h4>Unidades de Medida</h4>
                    <div class="breadcrumb">
                        <span>Crea y administra las unidades de medida que vas a utilizar en tus transacciones relacionadas a movimientos del inventario.</span>
                    </div>
                </div>
        </div>
            <div class="object-create"> 
                <a href="{{route("configuracion.unidades.create")}}" class="btn btn-success">Crear Unidad de Medida</a>
            </div>
        </div><!-- media -->
    </div><!-- pageheader -->

    <div class="contentpanel">
        <div class="row row-stat">
            {!! Form::open(['route' => 'configuracion.unidades.filtro', 'method'=>'POST']) !!}
            <div class="col-md-10">
                <div class="form-group">
                    {!! Form::label('keyUnidad', 'Unidad de Medida', array('class' => 'negrita')) !!}
                    {!! Form::text('keyUnidad',  session()->has('keyUnidad') ? session()->get('keyUnidad') : null,['class'=>'form-control']) !!}
                </div>
            </div>  
            <div class="col-md-2"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja',  'Todos' => 'Todos'], session()->has('status') ? session()->get('status') : 'Alta', array('id' => 'select-search-hide', "class" => 'widthAll select-status')) !!} 
                </div>
            </div>
            <div class="col-md-6"> 
                <a href="{{route('configuracion.unidades.index')}}" class="btn btn-default">Restablecer</a>
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
                                    {!!Form::checkbox('unidad', '1', true, ['id' => 'CheckUnidad'])!!}
                                    {!!Form::label('CheckUnidad', 'Unidad de Medida', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('decimal', '2', true, ['id' => 'CheckDecimal'])!!}
                                    {!!Form::label('CheckDecimal', 'Decimal Válida', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('clave', '3', true, ['id' => 'checkClave'])!!}
                                    {!!Form::label('checkClave', 'Clave Sat', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('estatus', '4', false, ['id' => 'CheckEstatus'])!!}
                                    {!!Form::label('CheckEstatus', 'Estatus', array('class' => 'negrita'))!!}
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
                                <th>Unidad de Medida</th>
                                <th>Decimal Valida</th>
                                <th>Clave Sat</th>
                                <th>Estatus</th>
                               
                            </tr>
                        </thead>
                 
                        <tbody>
                            
                            @if(session()->has('unidad_filtro'))
                            @foreach (session('unidad_filtro') as $unidad)
                                 @include('include.ConfiguracionGeneral.unidadesItem')
                            @endforeach
                         @else
                             @foreach ($unity as $unidad)
                             
                                 @include('include.ConfiguracionGeneral.unidadesItem')
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