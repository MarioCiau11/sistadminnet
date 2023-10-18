@extends('layouts.layout')


@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class="media display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                    <i class="fa fa-folder" aria-hidden="true"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Términos de Crédito</li>
                    </ul>
                    <h4>Términos de Crédito</h4>
                    <div class="breadcrumb">
                        <span>Crea y administra los términos de pago y cobranza que vas a utilizar en tus transacciones o procesos operativos.</span>
                    </div>
                </div>
        </div>
            <div class="object-create"> 
                <a href="{{route("configuracion.condiciones-credito.create")}}" class="btn btn-success">Crear Término de Crédito</a>
            </div>
        </div><!-- media -->
    </div><!-- pageheader -->
    
    <div class="contentpanel">
        <div class="row row-stat">
            {!! Form::open(['route' => 'configuracion.condiciones-credito.filtro', 'method'=>'POST']) !!}
            

            <div class="col-md-11"> 
                <div class="form-group">
                    {!! Form::label('nameFormaPago', 'Nombre', array('class' => 'negrita')) !!}
                    {!! Form::text('nameFormaPago',session()->has('nameFormaPago') ? session()->get('nameFormaPago') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-1"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja',  'Todos' => 'Todos'], session()->has('status') ? session()->get('status') : 'Alta', array('id' => 'select-search-hide', "class" => 'widthAll select-status')) !!} 
                </div>
            </div>

            <div class="col-md-6"> 
                <a href="{{route('configuracion.condiciones-credito.index')}}" class="btn btn-default">Restablecer</a>
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
                                    {!!Form::checkbox('condCredito_tipo', '2', true, ['id' => 'checkCreditoTipo'])!!}
                                    {!!Form::label('checkCreditoTipo', 'Tipo de término', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('condCredito_dias', '3', true, ['id' => 'checkCondCredioDias'])!!}
                                    {!!Form::label('checkCondCredioDias', 'Dias Vencimiento', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('condCredito_tipoDias', '4', true, ['id' => 'checkCondCreditoTipoDias'])!!}
                                    {!!Form::label('checkCondCreditoTipoDias', 'Tipo días', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('condCredito_diasHabiles', '5', false, ['id' => 'checkCondCreditoDiasHabiles'])!!}
                                    {!!Form::label('checkCondCreditoDiasHabiles', 'Días hábiles', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('condCredito_metodoPago', '6', true, ['id' => 'checkCondCreditoMetodoPago'])!!}
                                    {!!Form::label('checkCondCreditoMetodoPago', 'Método de pago', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('condCredito_estatus', '7',false, ['id' => 'checkCondCreditoEstatus'])!!}
                                    {!!Form::label('checkCondCreditoEstatus', 'Estatus', array('class' => 'negrita'))!!}
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
                                <th>Nombre del término</th>
                                <th>Tipo de término</th>
                                <th>Días de Vencimiento</th>
                                <th>Tipo de Días</th>
                                <th>Días Hábiles</th>
                                <th>Método de Pago</th>
                                <th>Estatus</th>
                               
                            </tr>
                        </thead>
                 
                        <tbody>
                            
                            {{-- En el controlador se pasa la data filtrada y se le enviamos al index como json_encode y en la vista usamos json_decode para
                            manipular los datos. --}}
                        
                            @if(session()->has('condicionCredito_filtro_array'))
                               @foreach (session('condicionCredito_filtro_array') as $condCredito)
                                    @include('include.ConfiguracionGeneral.condCreditoItem')
                               @endforeach
                            @else
                                @foreach ($condCredito_array as $condCredito)
                                    @include('include.ConfiguracionGeneral.condCreditoItem')
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