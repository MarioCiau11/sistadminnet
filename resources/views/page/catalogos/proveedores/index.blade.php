@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class=" display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                    <i class="fa fa-handshake-o" aria-hidden="true"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Proveedores/Acreedores</li>
                    </ul>
                    <h4>Proveedores/Acreedores</h4>
                    <div class="breadcrumb">
                        <span>Crea, edita y administra tus proveedores/acreedores para asociarlos en las transacciones
                             <br> o procesos operativos que registres a su nombre.</span>
                    </div>
                </div>
        </div>

        <div class="object-create">
            <div class="btn-group mr5">

                <a href="{{route("categoriaCreate", ['tipo' => 'Proveedor'])}}" class="btn btn-default">Crear Categoría</a>
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                  <span class="caret"></span>
                  <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="{{route("categoriaIndex", ['tipo' => 'Proveedor'])}}">Ver Categorías</a></li> 
                </ul>
              </div>

              <div class="btn-group mr5">
                <a href="{{route("grupoCreate", ['tipo' => 'Proveedor'])}}" class="btn btn-default">Crear Grupo</a>
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                  <span class="caret"></span>
                  <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="{{route("grupoIndex", ['tipo' => 'Proveedor'])}}">Ver Grupos</a></li>
                </ul>
              </div>
              <div class="btn-group mr5">
                <a href="{{route("catalogo.proveedor.create")}}" class="btn btn-success">Crear Proveedor</a>
              </div>
        </div>
    </div><!-- media -->
</div><!-- pageheader -->
    
    <div class="contentpanel">
        <div class="row row-stat">
        {!! Form::open(['route' => 'catalogo.proveedor.filtro', 'method'=>'POST']) !!}
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('keyProvider', 'Clave', array('class' => 'negrita')) !!}
                    {!! Form::text('keyProvider', session()->has('keyProvider') ? session()->get('keyProvider') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-3"> 
                <div class="form-group">
                    {!! Form::label('nameProvider', 'Nombre', array('class' => 'negrita')) !!}
                    {!! Form::text('nameProvider',session()->has('nameProvider') ? session()->get('nameProvider') : null,['class'=>'form-control']) !!}
                </div>
            </div>

             <div class="col-md-2"> 
                <div class="form-group">
                    {!! Form::label('categoria', 'Categoría', array('class' => 'negrita')) !!}
                    {!! Form::select('categoria', $categoria_array, session()->has('categoria') ? session()->get('categoria') : null, array('id' => 'select-search-grupo2', "class" => 'widthAll', 'placeholder' => 'Seleccione uno...')) !!} 
                </div>
            </div> 

            <div class="col-md-2"> 
                <div class="form-group">
                    {!! Form::label('grupo', 'Grupo', array('class' => 'negrita')) !!}
                    {!! Form::select('grupo', $grupo_array, session()->has('grupo') ? session()->get('grupo') : null, array('id' => 'select-search-grupo', "class" => 'widthAll', 'placeholder' => 'Seleccione uno...')) !!} 
                </div>
            </div> 

            <div class="col-md-2"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja', 'Todos' => 'Todos'], session()->has('status') ? session()->get('status') : 'Alta', array('id' => 'select-search-type', "class" => 'widthAll select-status')) !!} 
                </div>
            </div>

            <div class="col-md-12"></div>
       
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
                                    {!!Form::checkbox('office', '3', true, ['id' => 'checkOffice'])!!}
                                    {!!Form::label('checkOffice', 'Nombre Corto', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Age', '4', true, ['id' => 'checkAge'])!!}
                                    {!!Form::label('checkAge', 'RFC', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Age', '5', false, ['id' => 'checkCurp'])!!}
                                    {!!Form::label('checkCurp', 'CURP', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Date', '6', true, ['id' => 'checkDate'])!!}
                                    {!!Form::label('checkDate', 'Tipo', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Categoría', '7', true, ['id' => 'checkCategoria'])!!}
                                    {!!Form::label('checkCategoria', 'Categoría', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Grupo', '8', true, ['id' => 'checkGrupo'])!!}
                                    {!!Form::label('checkGrupo', 'Grupo', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Salary', '9', false, ['id' => 'checkSalary'])!!}
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
                                <th>Nombre Corto</th>
                                <th>RFC</th>
                                <th>Curp</th>
                                <th>Tipo</th>
                                <th>Categoría</th>
                                <th>Grupo</th>
                                <th>Estatus</th>
                               
                            </tr>
                        </thead>
                 
                       
                        <tbody>
                            @if(session()->has('provider_filtro_array'))
                               @foreach (session('provider_filtro_array') as $proveedor)
                                    @include('include.Catalogos.proveedoresItem')
                               @endforeach
                            @else
                                @foreach ($proveedor_collection as $proveedor)
                                    @include('include.Catalogos.proveedoresItem')
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