@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class=" display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                    <i class="fa fa-building-o"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Lista de Precios Proveedor</li>
                    </ul>
                    <h4>Lista de Precios Proveedor</h4>
                </div>
        </div>

        <div class="object-create">
         

              <div class="btn-group mr5">
                <a href="{{route("listaCreate")}}" class="btn btn-success">Crear Lista</a>
              </div>
        </div>
    </div><!-- media -->
</div><!-- pageheader -->
    
    <div class="contentpanel">
        <div class="row row-stat">
        {!! Form::open(['route' => 'listaFiltro', 'method'=>'POST']) !!}
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('keyProvider', 'Clave', array('class' => 'negrita')) !!}
                    {!! Form::text('keyProvider', session()->has('keyProvider') ? session()->get('keyProvider') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-4"> 
                <div class="form-group">
                    {!! Form::label('nameProvider', 'Nombre', array('class' => 'negrita')) !!}
                    {!! Form::text('nameProvider',session()->has('nameProvider') ? session()->get('nameProvider') : null,['class'=>'form-control']) !!}
                </div>
            </div>


            <div class="col-md-4"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja', 'Todos' => 'Todos'], session()->has('status') ? session()->get('status') : 'Alta', array('id' => 'select-search-type', "class" => 'widthAll select-status')) !!} 
                </div>
            </div>

       
            <div class="col-md-6"> 

                <a href="{{route('catalogo.proveedor.index')}}" class="btn btn-default">Restablecer</a>
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
                                    {!!Form::checkbox('Salary', '3', false, ['id' => 'checkSalary'])!!}
                                    {!!Form::label('checkSalary', 'Estatus', array('class' => 'negrita'))!!}
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
                                <th>Estatus</th>
                               
                            </tr>
                        </thead>
                 
                       
                        <tbody>
                            @if(session()->has('provider_filtro_array'))
                               @foreach (session('provider_filtro_array') as $proveedor)
                                    @include('include.Catalogos.proveedoresListItem')
                               @endforeach
                            @else
                                @foreach ($proveedor_collection as $proveedor)
                                    @include('include.Catalogos.proveedoresListItem')
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

        jQuery('#select-search-grupo, #select-search-grupo2').select2({
                    minimumResultsForSearch: -1
        });
        jQuery('#select-search-type').select2({
                    minimumResultsForSearch: -1
        });
    });
</script>

@endsection