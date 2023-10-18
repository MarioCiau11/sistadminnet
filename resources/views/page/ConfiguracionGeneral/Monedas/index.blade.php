@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class="media display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                     <i class="fa fa-money" aria-hidden="true"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Monedas</li>
                    </ul>
                    <h4>Monedas</h4>
                    <div class="breadcrumb">
                        <span>Crea y administra las monedas que vas a utilizar en tus transacciones o procesos operativos.</span>
                    </div>
                </div>
        </div>
            <div class="object-create"> 
                <a href="{{route("configuracion.monedas.create")}}" class="btn btn-success">Crear moneda</a>
            </div>
        </div><!-- media -->
    </div><!-- pageheader -->

    <div class="contentpanel">
        <div class="row row-stat">
            {!! Form::open(['route' => 'configuracion.monedas.filtro', 'method'=>'POST']) !!}
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('keyMoneda', 'Clave', array('class' => 'negrita')) !!}
                    {!! Form::text('keyMoneda',
                    session()->has('keyMoneda') ? session()->get('keyMoneda') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-8"> 
                <div class="form-group">
                    {!! Form::label('nameMoneda', 'Nombre', array('class' => 'negrita')) !!}
                    {!! Form::text('nameMoneda', 
                     session()->has('nameMoneda') ? session()->get('nameMoneda') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-1"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja',  'Todos' => 'Todos'], session()->has('status') ? session()->get('status') : 'Alta', array('id' => 'select-search-hided', "class" => 'widthAll select-status')) !!} 
                </div>
            </div>

            <div class="col-md-6"> 
                <a href="{{route('configuracion.monedas.index')}}" class="btn btn-default">Restablecer</a>
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
                                    {!!Form::checkbox('cambio', '3', true, ['id' => 'checkCambio'])!!}
                                    {!!Form::label('checkCambio', 'Cambio', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('descripción', '4', false, ['id' => 'checkDescripcion'])!!}
                                    {!!Form::label('checkDescripcion', 'Descripción', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('clavesat', '5', true, ['id' => 'checkClaveSAT'])!!}
                                    {!!Form::label('checkClaveSAT', 'Clave SAT', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('estatus', '6', false, ['id' => 'checkEstatus'])!!}
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
                                <th>Tipo cambio</th>
                                <th>Descripción</th>
                                <th>Clave SAT</th>
                                <th>Estatus</th>
                                
                            </tr>
                        </thead>
                 
                        <tbody>
                        {{-- En el controlador se pasa la data filtrada y se le enviamos al index como json_encode y en la vista usamos json_decode para
                        manipular los datos. --}}
                        
                        @if(session()->has('money_filtro_array'))
                           @foreach (session('money_filtro_array') as $money)
                                @include('include.ConfiguracionGeneral.monedasItem')
                           @endforeach
                        @else
                            @foreach ($money_array as $money)
                                @include('include.ConfiguracionGeneral.monedasItem')
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
        jQuery('#select-search-hided, #select-search-roles').select2({
                    minimumResultsForSearch: -1
        });
        
    });
</script>




@endsection

