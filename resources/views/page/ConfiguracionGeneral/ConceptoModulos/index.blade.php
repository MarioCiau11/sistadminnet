@extends('layouts.layout')


@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class="media display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Conceptos de Procesos</li>
                    </ul>
                    <h4>Conceptos de Procesos</h4>
                    <div class="breadcrumb">
                        <span>Crea y administra los conceptos que usarás en tus procesos operativos para identificarlos fácilmente. </span>
                    </div>
                </div>
        </div>
            <div class="object-create"> 
                <a href="{{route("configuracion.concepto-modulos.create")}}" class="btn btn-success">Crear Concepto</a>
            </div>
        </div><!-- media -->
    </div><!-- pageheader -->
    
    <div class="contentpanel">
        <div class="row row-stat">
            {!! Form::open(['route' => 'configuracion.concepto-modulos.filtro', 'method'=>'POST']) !!}
            

            <div class="col-md-8"> 
                <div class="form-group">
                    {!! Form::label('nameConcepto', 'Nombre', array('class' => 'negrita')) !!}
                    {!! Form::text('nameConcepto',
                    session()->has('nameConcepto') ? session()->get('nameConcepto') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{-- 'Todos', 'Compras', 'Inventarios', 'Ventas', 'Cuentas por pagar', 'Cuentas por cobrar', 'Tesorería', 'Activo Fijo' --}}
                    {!! Form::label('nameProceso', 'Proceso', array('class' => 'negrita')) !!}
                    {!! Form::select('nameProceso', ['Todos' => 'Todos', 'Compras' => 'Compras', 'Inventarios' => 'Inventarios', 'Ventas' => 'Ventas', 'Cuentas por Pagar' => 'Cuentas por Pagar', 'Cuentas por Cobrar' => 'Cuentas por Cobrar', 'Tesorería' => 'Tesorería', 'Activo Fijo' => 'Activo Fijo'], session()->has('nameProceso') ? session()->get('nameProceso') : 'Todos', array('id' => 'select-search-hided', "class" => 'widthAll select-proceso')) !!}
                </div>
            </div>

            <div class="col-md-1"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja', 'Todos' => 'Todos'], session()->has('status') ? session()->get('status') : 'Alta', array('id' => 'select-search-hide', "class" => 'widthAll select-status')) !!} 
                </div>
            </div>

            <div class="col-md-6"> 
                <a href="{{route('configuracion.concepto-modulos.index')}}" class="btn btn-default">Restablecer</a>
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
                                    {!!Form::checkbox('modulo', '2', true, ['id' => 'checkModulo'])!!}
                                    {!!Form::label('checkModulo', 'Proceso', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            {{-- <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('movimiento', '3', true, ['id' => 'checkModulo1'])!!}
                                    {!!Form::label('checkModulo1', 'Movimiento', array('class' => 'negrita'))!!}
                                </div>
                            </li> --}}
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('estatus', '3', false, ['id' => 'checkEstatus'])!!}
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
                                <th>Nombre</th>
                                <th>Proceso</th>
                                {{-- <th>Movimiento</th> --}}
                                <th>Estatus</th>                               
                            </tr>
                        </thead>
                        <tbody>
                            @if (session()->has('concepto_filtro_array'))
                                @foreach (session('concepto_filtro_array') as $concepto)
                                    @include('include.ConfiguracionGeneral.conceptosItem')
                                @endforeach
                            @else
                                @foreach ($concepto_array as $concepto)
                                    @include('include.ConfiguracionGeneral.conceptosItem')
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
        jQuery('#select-search-hide, #select-search-hided').select2({
                    minimumResultsForSearch: -1
        });
     });
</script>

@endsection