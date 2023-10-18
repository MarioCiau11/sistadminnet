@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="pageheader">
        <div class="media display-space-between">
            <div>
                <div class="pageicon pull-left mr10">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                 <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href="{{ route( "dashboard.index" )}}"><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Cuentas de Banco/Efectivos</li>
                    </ul>
                    <h4>Cuentas de Banco/Efectivos</h4>
                    <div class="breadcrumb">
                        <span>Crea, edita y administra tus cuentas bancarias y de efectivos para asociarlos en las transacciones o procesos operativos que realices.</span>
                    </div>
                </div>
        </div>
            <div class="object-create"> 
                <a href="{{route("catalogo.cuenta-dinero.create")}}" class="btn btn-success">Crear Cuenta</a>
            </div>
        </div><!-- media -->
    </div><!-- pageheader -->

    <div class="contentpanel">
        <div class="row row-stat">
            {!! Form::open(['route' => 'catalogo.cuenta-dinero.filtro', 'method'=>'POST']) !!}
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('keyCDinero', 'Clave', array('class' => 'negrita')) !!}
                    {!! Form::text('keyCDinero',
                     session()->has('keyCDinero') ? session()->get('keyCDinero') : null
                    ,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-8"> 
                <div class="form-group">
                    {!! Form::label('nameBanco', 'Nombre del Banco', array('class' => 'negrita')) !!}
                    {!! Form::text('nameBanco',
                    session()->has('nameBanco') ? session()->get('nameBanco') : null
                    ,['class'=>'form-control']) !!}
                </div>
            </div>


            <div class="col-md-1"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja', 'Todos' => 'Todos'], 
                      session()->has('status') ? session()->get('status') : 'Alta'
                    , array('id' => 'select-search-hide-status', "class" => 'widthAll select-status')) !!} 
                </div>
            </div>

            <div class="col-md-10">
                <div class="form-group">
                    {!! Form::label('numeroCuenta', 'No. de cuenta', array('class' => 'negrita')) !!}
                    {!! Form::text('numeroCuenta',
                     session()->has('numeroCuenta') ? session()->get('numeroCuenta') : null
                    ,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    {!! Form::label('typeCuenta', 'Tipo de cuenta', array('class' => 'negrita')) !!}
                    {!! Form::select('typeCuenta', ['Caja' => 'Caja', 'Banco' => 'Banco'], 
                    session()->has('typeCuenta') ? session()->get('typeCuenta') : null
                    , array('id' => 'select-search-hide-typeCuenta', "class" => 'widthAll select-status', 'placeholder' => 'Tipo de cuenta')) !!} 
                </div>
            </div>

            <div class="col-md-6"> 
                <a href="{{route('catalogo.cuenta-dinero.index')}}" class="btn btn-default">Restablecer</a>
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
                                    {!!Form::checkbox('banco', '2', true, ['id' => 'checkBanco'])!!}
                                    {!!Form::label('checkBanco', 'Banco', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('NoCuenta', '3', true, ['id' => 'checkNoCuenta'])!!}
                                    {!!Form::label('checkNoCuenta', 'No. de cuenta', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('cClabe', '4', false, ['id' => 'checkcClabe'])!!}
                                    {!!Form::label('checkcClabe', 'Cuenta CLABE', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('rBanco', '5', false, ['id' => 'checkRBanco'])!!}
                                    {!!Form::label('checkRBanco', 'Referencia Banco', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('cBanco', '6', false, ['id' => 'checkCBanco'])!!}
                                    {!!Form::label('checkCBanco', 'Convenio banco', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                             <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('tCuenta', '7', true, ['id' => 'checkTCuenta'])!!}
                                    {!!Form::label('checkTCuenta', 'Tipo de cuenta', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                             <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('moneda', '8', false, ['id' => 'checkMoneda'])!!}
                                    {!!Form::label('checkMoneda', 'Moneda', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('empresa', '9', true, ['id' => 'checkEmpresa'])!!}
                                    {!!Form::label('checkEmpresa', 'Empresa', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('status', '10', false, ['id' => 'checkStatus'])!!}
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
                                <th>Banco</th>
                                <th>No. de cuenta</th>
                                <th>Cuenta CLABE</th>
                                <th>Referencia Banco</th>
                                <th>Convenio Banco</th>
                                <th>Tipo de cuenta</th>
                                <th>Moneda</th>
                                <th>Empresa</th>
                                <th>Estatus</th>
                            </tr>
                        </thead>
                 
                        <tbody>
                             @if(session()->has('moneyAccounts'))
                               @foreach (session('moneyAccounts') as $moneyAccount)
                                    @include('include.Catalogos.cuentasDinero')
                               @endforeach
                            @else
                                @foreach ($moneyAccounts as $moneyAccount)
                                    @include('include.Catalogos.cuentasDinero')
                                @endforeach 
                            @endif
                            
                            
                        </tbody>
                    </table>
                </div><!-- panel -->
                
            </div>

        </div>
    <div>
</div>

<script>
    jQuery(document).ready(function () {
       jQuery('#select-search-hide-status, #select-search-hide-typeCuenta').select2({
                   minimumResultsForSearch: -1
        });
    });
</script>

@include('include.mensaje')
@endsection