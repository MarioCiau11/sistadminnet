@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="pageheader">
            <div class="media display-space-between">
                <div>
                    <div class="pageicon pull-left mr10">
                        <span class="glyphicon glyphicon-wrench"></span>
                    </div>
                    <div class="media-body">
                        <ul class="breadcrumb">
                            <li><a href="{{ route('dashboard.index') }}"><i class="glyphicon glyphicon-home"></i></a></li>
                            <li>Parametros generales</li>
                        </ul>
                        <h4>Parametros generales</h4>
                    </div>
                </div>
                <div class="object-create">
                    <a href="{{ route('configuracion.parametros-generales.create') }}" class="btn btn-success">Crear
                        parametro general</a>
                </div>
            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open(['route' => 'configuracion.unidades.filtro', 'method' => 'POST']) !!}
                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label('nameUnidad', 'Unidad', ['class' => 'negrita']) !!}
                        {!! Form::text('nameUnidad', null, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="col-md-3">

                </div>

                <div class="col-md-1">
                    <div class="form-group">
                        {!! Form::label('status', 'Estatus', ['class' => 'negrita']) !!}
                        {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja', 'Todos' => 'Todos'], 1, [
                            'id' => 'select-search-hide',
                            'class' => 'widthAll select-status',
                        ]) !!}
                    </div>
                </div>


                <div class="col-md-6">

                    <a href="{{ route('configuracion.unidades.index') }}" class="btn btn-default">Restablecer</a>
                    {!! Form::submit('Búsqueda', ['class' => 'btn btn-primary']) !!}
                    <a href="#" class="btn btn-info">Exportar excel</a>
                    {!! Form::close() !!}
                </div>

                <div class="col-md-6">
                    <div class="btn-columns">
                        <div class="btn-group">
                            <button data-toggle="dropdown" class="btn btn-sm mt5 btn-white border dropdown-toggle"
                                type="button">
                                Columnas <span class="caret"></span>
                            </button>
                            <ul role="menu" id="shCol" class="dropdown-menu dropdown-menu-sm pull-right">
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Opciones', '0', true, ['id' => 'checkOpciones']) !!}
                                        {!! Form::label('checkOpciones', 'Opciones', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('name', '1', true, ['id' => 'checkName']) !!}
                                        {!! Form::label('checkName', 'Name', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('position', '2', true, ['id' => 'checkPosition']) !!}
                                        {!! Form::label('checkPosition', 'Position', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('office', '3', true, ['id' => 'checkOffice']) !!}
                                        {!! Form::label('checkOffice', 'Office', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Age', '4', true, ['id' => 'checkAge']) !!}
                                        {!! Form::label('checkAge', 'Age', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Date', '5', true, ['id' => 'checkDate']) !!}
                                        {!! Form::label('checkDate', 'Date', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('Salary', '6', true, ['id' => 'checkSalary']) !!}
                                        {!! Form::label('checkSalary', 'Salary', ['class' => 'negrita']) !!}
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>


                {{-- En el controlador se pasa la data filtrada y se le enviamos al index como json_encode y en la vista usamos json_decode para
            manipular los datos. --}}
                {{-- @if (session('test'))
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
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Office</th>
                                    <th>Age</th>
                                    <th>Start date</th>
                                    <th>Salary</th>

                                </tr>
                            </thead>

                            <tbody>

                                <tr>
                                    <td class="td-option">
                                        <div class="contenedor-opciones">
                                            <a href="{{ route('configuracion.parametros-generales.show', ['parametros_generale' => 1]) }}"
                                                class="show" data-toggle="tooltip" data-placement="top"
                                                title="Mostrar registro"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                            <a href="{{ route('configuracion.parametros-generales.edit', ['parametros_generale' => 1]) }}"
                                                class="edit" data-toggle="tooltip" data-placement="top"
                                                title="Editar registro"><i class="fa fa-pencil-square-o"
                                                    aria-hidden="true"></i></a>

                                            {!! Form::open([
                                                'route' => ['configuracion.parametros-generales.destroy', 'parametros_generale' => 1],
                                                'method' => 'DELETE',
                                                'id' => 'deleteForm',
                                            ]) !!}
                                            <a href="" class="delete" data-toggle="tooltip" data-placement="top"
                                                title="Eliminar registro"><i class="fa-regular fa-circle-down"
                                                    aria-hidden="true"></i></a>
                                            {!! Form::close() !!}

                                        </div>
                                    </td>
                                    <td>Tiger Nixon</td>
                                    <td>System Architect</td>
                                    <td>Edinburgh</td>
                                    <td>61</td>
                                    <td>2011/04/25</td>
                                    <td>$320,800</td>
                                </tr>
                            </tbody>
                        </table>
                    </div><!-- panel -->

                </div>

            </div>
            <div>
            </div>


            <script>
                jQuery(document).ready(function() {
                    jQuery('#select-search-hide').select2({
                        minimumResultsForSearch: -1
                    });
                });
            </script>
        @endsection
