@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class="display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                    <i class="fa-solid fa-universal-access"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Clientes</li>
                    </ul>
                    <h4>Clientes</h4>
                    <div class="breadcrumb">
                        <span>Crea, edita y administra tus clientes para asociarlos en las transacciones
                            <br> o procesos operativos que registres a su nombre.</span>
                    </div>
                </div>
        </div>
        <div class="object-create">
            <div class="btn-group mr5">

                <a href="{{route("categoriaCreate", ['tipo' => 'Cliente'])}}" class="btn btn-default">Crear Categoría</a>
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                  <span class="caret"></span>
                  <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="{{route("categoriaIndex", ['tipo' => 'Cliente'])}}">Ver Categorías</a></li> 
                </ul>
              </div>

              <div class="btn-group mr5">
                <a href="{{route("grupoCreate", ['tipo' => 'Cliente'])}}" class="btn btn-default">Crear Grupo</a>
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                  <span class="caret"></span>
                  <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="{{route("grupoIndex", ['tipo' => 'Cliente'])}}">Ver Grupos</a></li>
                </ul>
              </div>
              <div class="btn-group mr5">
                <a href="{{route("catalogo.clientes.create")}}" class="btn btn-success">Crear Cliente</a>
              </div>
        </div>
    </div><!-- media -->
</div><!-- pageheader -->
    
    <div class="contentpanel">
        <div class="row row-stat">
            {!! Form::open(['route' => 'catalogo.clientes.filtro', 'method'=>'POST']) !!}
            <div class="col-md-2">
                <div class="form-group">
                    {!! Form::label('keyCliente', 'Clave', array('class' => 'negrita')) !!}
                    {!! Form::text('keyCliente',session()->has('keyCustomer') ? session()->get('keyCustomer') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-2"> 
                <div class="form-group">
                    {!! Form::label('nameCliente', 'Nombre', array('class' => 'negrita')) !!}
                    {!! Form::text('nameCliente',session()->has('nameCustomer') ? session()->get('nameCustomer') : null,['class'=>'form-control']) !!}
                </div>
            </div>

             <div class="col-md-2"> 
                <div class="form-group">
                    {!! Form::label('razonSocial', 'Razon Social', array('class' => 'negrita')) !!}
                    {!! Form::text('razonSocial',session()->has('bussinesName') ? session()->get('bussinesName') : null,['class'=>'form-control']) !!}
                </div>
            </div>

             <div class="col-md-2"> 
                <div class="form-group">
                    {!! Form::label('categoria', 'Categoría', array('class' => 'negrita')) !!}
                    {!! Form::select('categoria', $categoria_array, session()->has('category') ? session()->get('category') : null, array('id' => 'select-search-hide-categoria', "class" => 'widthAll select-status', 'placeholder' => 'Seleccione uno...')) !!} 
                </div>
            </div>


             <div class="col-md-2"> 
                <div class="form-group">
                    {!! Form::label('grupo', 'Grupo', array('class' => 'negrita')) !!}
                    {!! Form::select('grupo', $grupo_array, session()->has('group') ? session()->get('group') : null, array('id' => 'select-search-hide-grupo', "class" => 'widthAll select-status', 'placeholder' => 'Seleccione uno...')) !!} 
                </div>
            </div>

            <div class="col-md-2"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja', 'Todos' => 'Todos'], session()->has('status') ? session()->get('status') : null, array('id' => 'select-search-hide', "class" => 'widthAll select-status')) !!} 
                </div>
            </div>

            <div class="col-md-12"></div>
            <div class="col-md-6"> 

                <a href="{{route('catalogo.clientes.index')}}" class="btn btn-default">Restablecer</a>
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
                                    {!!Form::label('checkPosition', 'Razon Social', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('office', '3', false, ['id' => 'checkOffice'])!!}
                                    {!!Form::label('checkOffice', 'Nombre', array('class' => 'negrita'))!!}
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
                                    {!!Form::checkbox('Date', '5', false, ['id' => 'checkDate'])!!}
                                    {!!Form::label('checkDate', 'Tipo', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Salary', '6', false, ['id' => 'checkSalary'])!!}
                                    {!!Form::label('checkSalary', 'Estatus', array('class' => 'negrita'))!!}
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
                                <th>Razón Social</th>
                                <th>Nombre</th>
                                <th>RFC</th>
                                <th>Tipo</th>
                                <th>Estatus</th>
                               
                            </tr>
                        </thead>
                 
                        <tbody>
                            @if(session()->has('customer_filtro_array'))
                               @foreach (session('customer_filtro_array') as $cliente)
                                    @include('include.Catalogos.clientesItem')
                               @endforeach
                            @else
                                @foreach ($cliente_collection as $cliente)
                                    @include('include.Catalogos.clientesItem')
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
        jQuery('#select-search-hide, #select-search-hide-categoria, #select-search-hide-grupo').select2({
                    minimumResultsForSearch: -1
        });
     });
</script>
@endsection