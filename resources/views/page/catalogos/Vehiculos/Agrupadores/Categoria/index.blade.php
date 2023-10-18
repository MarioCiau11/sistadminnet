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
                            <li>Categorías de Vehículos</li>
                        </ul>
                        <h4>Categorías de Vehículos</h4>
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
                        @foreach ($categorias as $categoria)
                            <tr>
                                <td class="td-option">
                                    <div class="contenedor-opciones">

                                        <a href="{{ route('editCategoria', [Crypt::encrypt($categoria->categoryVehicle_id), 'tipo' => 'Vehiculo']) }}"
                                            class="edit" data-toggle="tooltip" data-placement="top"
                                            title="Editar registro"><i class="fa fa-pencil-square-o"
                                                aria-hidden="true"></i></a>

                                        {!! Form::open([
                                            'route' => ['deleteCategory', 'categoria' => Crypt::encrypt($categoria->categoryVehicle_id), 'tipo' => 'Vehiculo'],
                                            'method' => 'DELETE',
                                            'id' => 'deleteForm',
                                        ]) !!}
                                        <a href="" class="delete" data-toggle="tooltip" data-placement="top"
                                            title="Eliminar registro"><i class="fa-regular fa-circle-down"
                                                aria-hidden="true"></i></a>
                                        {!! Form::close() !!}

                                    </div>
                                </td>
                                <td>{{ $categoria->categoryVehicle_id }}</td>
                                <td>{{ $categoria->categoryVehicle_name }}</td>
                                <td>{{ $categoria->categoryVehicle_status }}</td>

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
