@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class="media display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                    <i class="fa fa-building"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Centro de Costos</li>
                    </ul>
                    <h4>Centro de Costos</h4>
                    <div class="breadcrumb">
                        <span>Configura los centros de costos de tu empresa</span>
                    </div>
                </div>
        </div>
            <div class="object-create"> 
                <a href="{{route("catalogo.centroCostos.create")}}" class="btn btn-success">Crear Centro de Costo</a>
            </div>
        </div><!-- media -->
    </div><!-- pageheader -->
    
    <div class="contentpanel">
        <div class="row row-stat">
            {!! Form::open(['route' => 'catalogo.centroCostos.filtro', 'method'=>'POST']) !!}
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('keyCentroCosto', 'Clave', array('class' => 'negrita')) !!}
                    {!! Form::text('keyCentroCosto',session()->has('keyCentroCosto') ? session()->get('keyCentroCosto') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-8"> 
                <div class="form-group">
                    {!! Form::label('nameCentroCosto', 'Nombre', array('class' => 'negrita')) !!}
                    {!! Form::text('nameCentroCosto', session()->has('nameCentroCosto') ? session()->get('nameCentroCosto') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-1"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja',  'Todos' => 'Todos'], session()->has('status') ? session()->get('status') : 'Alta', array('id' => 'select-search-hide', "class" => 'widthAll select-status')) !!} 
                </div>
            </div>

            <div class="col-md-6"> 
                <a href="{{route('catalogo.centroCostos.index')}}" class="btn btn-default">Restablecer</a>
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
                                    {!!Form::checkbox('office', '3', true, ['id' => 'checkOffice'])!!}
                                    {!!Form::label('checkOffice', 'Estatus', array('class' => 'negrita'))!!}
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
                                <th>Estatus</th>
                               
                            </tr>
                        </thead>
                 
                        <tbody>

                            
                            @if(session()->has('centroCostos_filtro_array'))
                               @foreach (session('centroCostos_filtro_array') as $costos)
                                    @include('include.Catalogos.centroCostosItem')
                               @endforeach
                            @else
                                @foreach ($centroCostos as $costos)
                                    @include('include.Catalogos.centroCostosItem')
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