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
                            <li>Grupos de Conceptos de Gastos</li>
                        </ul>
                        <h4>Grupos de Conceptos de Gastos</h4>
                    </div>
                </div>
            </div><!-- media -->
        </div><!-- pageheader -->


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
                        @foreach ($grupos as $grupo)
                            <tr>
                                <td class="td-option">
                                    <div class="contenedor-opciones">

                                        <a href="{{ route('editGrupo', [Crypt::encrypt($grupo->groupExpenseConcept_id), 'tipo' => 'Concepto']) }}"
                                            class="edit" data-toggle="tooltip" data-placement="top"
                                            title="Editar registro"><i class="fa fa-pencil-square-o"
                                                aria-hidden="true"></i></a>

                                        {!! Form::open([
                                            'route' => ['deleteGrupo', 'grupo' => Crypt::encrypt($grupo->groupExpenseConcept_id), 'tipo' => 'Concepto'],
                                            'method' => 'DELETE',
                                            'id' => 'deleteForm',
                                        ]) !!}
                                        <a href="" class="delete" data-toggle="tooltip" data-placement="top"
                                            title="Eliminar registroo"><i class="fa-regular fa-circle-down"
                                                aria-hidden="true"></i></a>
                                        {!! Form::close() !!}

                                    </div>
                                </td>
                                <td>{{ $grupo->groupExpenseConcept_id }}</td>
                                <td>{{ $grupo->groupExpenseConcept_name }}</td>
                                <td>{{ $grupo->groupExpenseConcept_status }}</td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div><!-- panel -->

        </div>

    </div>
    <div>
    </div>

    @include('include.mensaje')
    <script>
        jQuery(document).ready(function() {
            jQuery('#select-search-hide').select2({
                minimumResultsForSearch: -1
            });
        });
    </script>
@endsection
