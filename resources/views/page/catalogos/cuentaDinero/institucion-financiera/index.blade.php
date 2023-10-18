@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class="media display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Instituciones Financieras</li>
                    </ul>
                    <h4>Instituciones Financieras</h4>
                    <div class="breadcrumb">
                        <span>Crea y administra las instituciones financieras con el cual trabaja hoy tu negocio.</span>
                    </div>
                </div>
        </div>
            <div class="object-create"> 
                <a href="{{route("catalogo.instituciones-financieras.create")}}" class="btn btn-success">Crear Institución Financiera</a>
            </div>
        </div><!-- media -->
    </div><!-- pageheader -->
    
    <div class="contentpanel">
        <div class="row row-stat">
            {!! Form::open(['route' => 'catalogo.instituciones-financieras.filtro', 'method'=>'POST']) !!}
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('keyInstFinancial', 'Clave', array('class' => 'negrita')) !!}
                    {!! Form::text('keyInstFinancial',
                    session()->has('keyInstFinancial') ? session()->get('keyInstFinancial') : null
                    ,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-8"> 
                <div class="form-group">
                    {!! Form::label('nameInstFinancial', 'Nombre', array('class' => 'negrita')) !!}
                    {!! Form::text('nameInstFinancial',
                    session()->has('nameInstFinancial') ? session()->get('nameInstFinancial') : null
                    ,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-1"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja', 'Todos' => 'Todos'], session()->has('status') ? session()->get('status') : 'Alta', array('id' => 'select-search-hide', "class" => 'widthAll select-status' )) !!}
                </div>
            </div>

            <div class="col-md-6"> 

                <a href="{{route('catalogo.instituciones-financieras.index')}}" class="btn btn-default">Restablecer</a>
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
                                    {!!Form::checkbox('key', '1', true, ['id' => 'checkKey'])!!}
                                    {!!Form::label('checkKey', 'Clave', array('class' => 'negrita'))!!}
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
                                    {!!Form::checkbox('ciudad', '3', false, ['id' => 'checkCiudad'])!!}
                                    {!!Form::label('checkCiudad', 'Ciudad', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Estado', '4', false, ['id' => 'checkEstado'])!!}
                                    {!!Form::label('checkEstado', 'Estado', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('pais', '5', false, ['id' => 'checkPais'])!!}
                                    {!!Form::label('checkPais', 'País', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('status', '6', false, ['id' => 'checkStatus'])!!}
                                    {!!Form::label('checkStatus', 'Estatus', array('class' => 'negrita'))!!}
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
                                <th>Clave</th>
                                <th>Nombre</th>
                                <th>Ciudad</th>
                                <th>Estado</th>
                                <th>País</th>
                                <th>Estatus</th>
                            </tr>
                        </thead>
                 
                        <tbody>
                        {{-- En el controlador se pasa la data filtrada y se le enviamos al index como json_encode y en la vista usamos json_decode para
                        manipular los datos. --}}
                       
                        @if(session()->has('instFinancial'))
                           @foreach (session('instFinancial') as $institucion)
                                @include('include.Catalogos.instFinancialItem')
                           @endforeach
                        @else
                            @foreach ($instituciones as $institucion)
                                @include('include.Catalogos.instFinancialItem')
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
     jQuery(document).ready(function () {
        jQuery('#select-search-hide').select2({
                    minimumResultsForSearch: -1
                });
     });
</script>


@endsection