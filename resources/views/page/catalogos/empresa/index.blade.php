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
                        <li>Empresas</li>
                    </ul>
                    <h4>Empresas</h4>
                    <div class="breadcrumb">
                        <span>Configura la información de tu empresa y adapta ERPNET a tu negocio.</span>
                    </div>
                </div>
        </div>
            <div class="object-create"> 
                <a href="{{route("catalogo.empresa.create")}}" class="btn btn-success">Crear empresa</a>
            </div>
        </div><!-- media -->
    </div><!-- pageheader -->
    
    <div class="contentpanel">
        <div class="row row-stat">
            {!! Form::open(['route' => 'catalogo.empresa.filtro', 'method'=>'POST']) !!}
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('keyCompany', 'Clave', array('class' => 'negrita')) !!}
                    {!! Form::text('keyCompany',session()->has('keyEmpresa') ? session()->get('keyEmpresa') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-8"> 
                <div class="form-group">
                    {!! Form::label('nameEmpresa', 'Nombre', array('class' => 'negrita')) !!}
                    {!! Form::text('nameEmpresa', session()->has('nameEmpresa') ? session()->get('nameEmpresa') : null,['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="col-md-1"> 
                <div class="form-group">
                    {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                    {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja',  'Todos' => 'Todos'], session()->has('status') ? session()->get('status') : 'Alta', array('id' => 'select-search-hide', "class" => 'widthAll select-status')) !!} 
                </div>
            </div>

            <div class="col-md-6"> 
                <a href="{{route('catalogo.empresa.index')}}" class="btn btn-default">Restablecer</a>
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
                                    {!!Form::checkbox('Age', '4', false, ['id' => 'checkAge'])!!}
                                    {!!Form::label('checkAge', 'RFC', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Date', '5', false, ['id' => 'checkDate'])!!}
                                    {!!Form::label('checkDate', 'Estatus', array('class' => 'negrita'))!!}
                                </div>
                            </li>
                            <li>
                                <div class="ckbox ckbox-primary">
                                    {!!Form::checkbox('Salary', '6', false, ['id' => 'checkSalary'])!!}
                                    {!!Form::label('checkSalary', 'Logo', array('class' => 'negrita'))!!}
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
                                <th>Nombre Corto</th>
                                <th>RFC</th>
                                <th>Estatus</th>
                                <th>Logo</th>
                               
                            </tr>
                        </thead>
                 
                        <tbody>
                            {{-- En el controlador se pasa la data filtrada y se le enviamos al index como json_encode y en la vista usamos json_decode para
                            manipular los datos. --}}
                            
                            @if(session()->has('empresa_filtro_array'))
                               @foreach (session('empresa_filtro_array') as $empresa)
                                    @include('include.Catalogos.empresasItem')
                               @endforeach
                            @else
                                @foreach ($empresa_collection as $empresa)
                                    @include('include.Catalogos.empresasItem')
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