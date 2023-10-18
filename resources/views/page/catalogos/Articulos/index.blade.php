@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="pageheader">
            <div class=" display-space-between">
                <div>
                    <div class="pageicon pull-left mr10">
                        <i class="fa-solid fa-barcode"></i>
                    </div>
                    <div class="media-body">
                        <ul class="breadcrumb">
                            <li><a href="{{ route('dashboard.index') }}"><i class="glyphicon glyphicon-home"></i></a></li>
                            <li>Productos / Items</li>
                        </ul>
                        <h4>Productos / Items</h4>
                        <div class="breadcrumb">
                            <span>Crea, edita y administra cada detalle de tus Productos/ Items.</span>
                        </div>
                    </div>
                </div>

                <div class="object-create">
                    <div class="btn-group mr5">

                        <a href="{{ route('categoriaCreate', ['tipo' => 'Articulo']) }}" class="btn btn-default">Crear
                            Categoría</a>
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="{{ route('categoriaIndex', ['tipo' => 'Articulo']) }}">Ver Categorías</a></li>
                        </ul>
                    </div>

                    <div class="btn-group mr5">
                        <a href="{{ route('grupoCreate', ['tipo' => 'Articulo']) }}" class="btn btn-default">Crear Grupo</a>
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="{{ route('grupoIndex', ['tipo' => 'Articulo']) }}">Ver Grupos</a></li>
                        </ul>
                    </div>
                    <div class="btn-group mr5">
                        <a href="{{ route('familiaCreate', ['tipo' => 'Articulo']) }}" class="btn btn-default">Crear
                            Familia</a>
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="{{ route('familiaIndex', ['tipo' => 'Articulo']) }}">Ver Familias</a></li>
                        </ul>
                    </div>
                    <div class="btn-group mr5">
                        <a href="{{ route('catalogo.articulos.create') }}" class="btn btn-success">Crear Producto</a>
                    </div>
                </div>
            </div><!-- media -->
        </div><!-- pageheader -->


        <div class="contentpanel">
            <div class="row row-stat">
                {!! Form::open(['route' => 'catalogo.articulo.filtro', 'method' => 'POST']) !!}
                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('claveArticulo', 'Clave', ['class' => 'negrita']) !!}
                        {!! Form::text('claveArticulo', session()->has('claveArticulo') ? session()->get('claveArticulo') : null, [
                            'class' => 'form-control',
                        ]) !!}
                </div>
            </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('nameArticulo', 'Nombre', ['class' => 'negrita']) !!}
                        {!! Form::text('nameArticulo', session()->has('nameArticulo') ? session()->get('nameArticulo') : null, [
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('categoria', 'Categoria', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'categoria',
                            $select_categoria,
                            session()->has('categoria') ? session()->get('categoria') : null,
                            ['id' => 'select-search-hide', 'class' => 'widthAll', 'placeholder' => 'Seleccione uno...'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('grupo', 'Grupo', ['class' => 'negrita']) !!}
                        {!! Form::select('grupo', $select_grupo, session()->has('grupo') ? session()->get('grupo') : null, [
                            'id' => 'select-search-grupo',
                            'class' => 'widthAll',
                            'placeholder' => 'Seleccione uno...',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('familia', 'Familia', ['class' => 'negrita']) !!}
                        {!! Form::select('familia', $select_familia, session()->has('familia') ? session()->get('familia') : null, [
                            'id' => 'select-search-familia',
                            'class' => 'widthAll',
                            'placeholder' => 'Seleccione uno...',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-1">
                    <div class="form-group">
                        {!! Form::label('status', 'Estatus', ['class' => 'negrita']) !!}
                        {!! Form::select(
                            'status',
                            ['Alta' => 'Alta', 'Baja' => 'Baja', 'Todos' => 'Todos'],
                            session()->has('status') ? session()->get('status') : 'Alta',
                            ['id' => 'select-search-type', 'class' => 'widthAll select-status'],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-12"></div>


                <div class="col-md-6">
                    <a href="{{ route('catalogo.articulos.index') }}" class="btn btn-default">Restablecer</a>
                    {!! Form::submit('Búsqueda', ['class' => 'btn btn-primary', 'name' => 'action']) !!}
                    {!! Form::submit('Exportar excel', ['class' => 'btn btn-info', 'name' => 'action']) !!}
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
                                        {!! Form::checkbox('clave', '1', true, ['id' => 'checkClave']) !!}
                                        {!! Form::label('checkClave', 'Clave', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('tipo', '2', true, ['id' => 'checkTipo']) !!}
                                        {!! Form::label('checkTipo', 'Tipo', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('descripción1', '3', true, ['id' => 'checkDescripción1']) !!}
                                        {!! Form::label('checkDescripción1', 'Descripción 1', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('descripción2', '4', false, ['id' => 'checkDescripción2']) !!}
                                        {!! Form::label('checkDescripción2', 'Descripción 2', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('category', '5', false, ['id' => 'checkCategory']) !!}
                                        {!! Form::label('checkCategory', 'Categoria', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('group', '6', false, ['id' => 'checkGroup']) !!}
                                        {!! Form::label('checkGroup', 'Grupo', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('family', '7', false, ['id' => 'checkFamily']) !!}
                                        {!! Form::label('checkFamily', 'Familia', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('UnidadVenta', '8', false, ['id' => 'checkUnidadVenta']) !!}
                                        {!! Form::label('checkUnidadVenta', 'Unidad venta', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('unidadTraspaso', '9', false, ['id' => 'checkUnidadTraspaso']) !!}
                                        {!! Form::label('checkUnidadTraspaso', 'Unidad traspaso', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('unidadCompra', '10', false, ['id' => 'checkUnidadCompra']) !!}
                                        {!! Form::label('checkUnidadCompra', 'Unidad compra', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('iva', '11', false, ['id' => 'checkIva']) !!}
                                        {!! Form::label('checkIva', 'IVA', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                        {!! Form::checkbox('estatus', '12', false, ['id' => 'checkStatus']) !!}
                                        {!! Form::label('checkStatus', 'Estatus', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                    {!! Form::checkbox('lista', '13', true, ['id' => 'checkLista']) !!}
                                    {!! Form::label('checkLista', 'Lista Precio 1', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                    {!! Form::checkbox('lista', '14', true, ['id' => 'checkLista']) !!}
                                    {!! Form::label('checkLista', 'Lista Precio 2', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                    {!! Form::checkbox('lista', '15', true, ['id' => 'checkLista']) !!}
                                    {!! Form::label('checkLista', 'Lista Precio 3', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                    {!! Form::checkbox('lista', '16', true, ['id' => 'checkLista']) !!}
                                    {!! Form::label('checkLista', 'Lista Precio 4', ['class' => 'negrita']) !!}
                                    </div>
                                </li>
                                <li>
                                    <div class="ckbox ckbox-primary">
                                    {!! Form::checkbox('lista', '17', true, ['id' => 'checkLista']) !!}
                                    {!! Form::label('checkLista', 'Lista Precio 5', ['class' => 'negrita']) !!}
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
                                    <th>Tipo</th>
                                    <th>Nombre del Producto / Item</th>
                                    <th>Descripción 2</th>
                                    <th>Categoria</th>
                                    <th>Grupo</th>
                                    <th>Familia</th>
                                    <th>Unidad de Medida / Como se compra</th>
                                    <th>Unidad de Medida / Como se vende</th>
                                    <th>Unidad de Medida / Como se traspasa entre sucursales</th>
                                    <th>IVA</th>
                                    <th>Estatus</th>
                                    <th>Precio 1</th>
                                    <th>Precio 2</th>
                                    <th>Precio 3</th>
                                    <th>Precio 4</th>
                                    <th>Precio 5</th>


                                </tr>
                            </thead>

                            <tbody>
                                @if (session()->has('articulos_filtro'))
                                    @foreach (session('articulos_filtro') as $articulo)
                                        @include('include.Catalogos.articulosItem')
                                    @endforeach
                                @else
                                    @foreach ($articulos as $articulo)
                                        @include('include.Catalogos.articulosItem')
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
                jQuery(document).ready(function() {
                    jQuery('#select-search-hide').select2({
                        minimumResultsForSearch: -1
                    });

                    jQuery('#select-search-grupo').select2({
                        minimumResultsForSearch: -1
                    });
                    jQuery('#select-search-type').select2({
                        minimumResultsForSearch: -1
                    });
                    jQuery('#select-search-familia').select2({
                        minimumResultsForSearch: -1
                    });
                });
            </script>

            @include('include.mensaje')
        @endsection
