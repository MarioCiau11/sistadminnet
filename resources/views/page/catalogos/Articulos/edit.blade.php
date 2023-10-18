@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => ['catalogo.articulos.update', Crypt::encrypt($articulo->articles_id)],
                        'method' => 'PUT',
                        'id' => 'basicForm',
                        'enctype' => 'multipart/form-data',
                    ]) !!}

                    {!! Form::macro('labelValidacion', function ($name, $labelName, $classes) {
                        return "<label for= '" .
                            $name .
                            "' class= '" .
                            $classes .
                            "'>" .
                            $labelName .
                            "<span class='asterisk'> *</span> </label>";
                    }) !!}

                    {!! Form::macro('labelNOValidacion', function ($name, $labelName, $classes) {
                        return "<label for= '" . $name . "' class= '" . $classes . "'>" . $labelName . ' </label>';
                    }) !!}


                    <ul class="nav nav-justified nav-wizard">
                        <li class="active"><a href="#tab1" data-toggle="tab">Datos Generales del Producto</a></li>
                        <li class="tab2None" style="display: none"><a href="#tab2" data-toggle="tab">Kit</a></li>
                        <li> <a href="#tab3" data-toggle="tab">Cargar Imagenes / Especificaciones</a></li>
                    </ul>


                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">
                            <div class="col-md-12">
                                <div class="col-md-6">
                                    {{-- <h2 class="text-black">Datos Generales del Almacén</h2> --}}
                                </div>
                                <div class="col-md-6">
                                    <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::labelValidacion('keyClave', 'Clave', 'negrita') !!}
                                    {!! Form::text('keyClave', $articulo->articles_key, ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::labelValidacion('nameTipo', 'Tipo', 'negrita') !!}
                                    {!! Form::select(
                                        'nameTipo',
                                        ['Normal' => 'Producto', 'Serie' => 'Serializado', 'Kit' => 'Kit/Combo', 'Servicio' => 'Servicio'],
                                        $articulo->articles_type,
                                        ['id' => 'select-search-hide-dg', 'class' => 'widthAll select-status', 'placeholder' => 'Seleccione uno'],
                                    ) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                                    {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $articulo->articles_status, [
                                        'id' => 'select-search-hide-tipo',
                                        'class' => 'widthAll select-tipo',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>

                            <div class="col-md-8">
                                <div class="form-group mt10">
                                    {!! Form::labelValidacion('descripcion1', 'Nombre del Producto / Item', 'negrita') !!}
                                    {!! Form::text('descripcion1', $articulo->articles_descript, ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::label('descripcion2', 'Descripción Comercio Exterior', ['class' => 'negrita']) !!}
                                    {!! Form::text('descripcion2', $articulo->articles_descript2, ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>
                            
                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::labelValidacion('unidadCompra', 'Unidad de Medida / Como se compra', 'negrita') !!}
                                    {!! Form::select('unidadCompra', $select_ConfUnidades, $articulo->articles_unitBuy, [
                                        'id' => 'select-search-hide-unidadcompra',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::labelValidacion('unidadVenta', 'Unidad de Medida / Como se vende', 'negrita') !!}

                                    {!! Form::select('unidadVenta', $select_ConfUnidades, $articulo->articles_unitSale, [
                                        'id' => 'select-search-hide-unidadventa',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::labelValidacion('unidadTraspaso', 'Unidad de Medida / Como se traspasa entre sucursales', 'negrita') !!}
                                    {!! Form::select('unidadTraspaso', $select_ConfUnidades, $articulo->articles_transfer, [
                                        'id' => 'select-search-hide-unidadtraspaso',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno',
                                    ]) !!}
                                </div>
                            </div>


                            <div class="col-md-12"></div>

                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::label('categoria', 'Categoría', ['class' => 'negrita']) !!}
                                    {!! Form::select('categoria', $select_categoria, $articulo->articles_category, [
                                        'id' => 'select-search-hide-dg',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::label('grupo', 'Grupo', ['class' => 'negrita']) !!}
                                    {!! Form::select('grupo', $select_grupo, $articulo->articles_group, [
                                        'id' => 'select-search-hide-dg',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::label('familia', 'Familia', ['class' => 'negrita']) !!}
                                    {!! Form::select('familia', $select_familia, $articulo->articles_family, [
                                        'id' => 'select-search-hide-dg',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-12"> </div>

                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::labelValidacion('iva', 'IVA', 'negrita') !!}
                                    {!! Form::number('iva', $articulo->articles_porcentIva, ['class' => 'form-control', 'id' => 'ivaInput']) !!}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::label('retencion1', 'Retención ISR', ['class' => 'negrita']) !!}
                                    {!! Form::number('retencion1', floatval($articulo->articles_retention1), [
                                        'class' => 'form-control',
                                        'id' => 'retencion1',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::label('retencion2', 'Retención IVA', ['class' => 'negrita']) !!}
                                    {!! Form::number('retencion2', floatval($articulo->articles_retention2), [
                                        'class' => 'form-control',
                                        'id' => 'retencion2',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-12"> </div>

                            <div class="col-md-12">
                                <h2 class="text-black">Multi-Unidades del Producto</h2>
                            </div>

                            @if (count($multiUnidadesArticulo) == 0)
                                <div class="col-md-12" id="multiUnidades">
                                    <div class="col-md-5">
                                        <div class="form-group mt10">
                                            {!! Form::labelValidacion('factorUnidad', 'Unidad de Medida', 'negrita') !!}
                                            {!! Form::select('factorUnidad[]', $select_multiUnidad, null, [
                                                'class' => 'widthAll multi-select',
                                                'placeholder' => 'Seleccione uno',
                                                'required',
                                            ]) !!}
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="form-group mt10">
                                            {!! Form::labelValidacion('factor', 'Factor de conversión', 'negrita') !!}
                                            {!! Form::number('factor[]', null, ['class' => 'form-control', 'required', 'min' => '1', 'max' => '9999']) !!}
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <a href='javascript:void(0);' class='remove_button btn btn-danger'
                                            title='Remove field'><i class='fa fa-times' aria-hidden='true'></i></a>
                                    </div>
                                </div>
                            @else
                                <div class="moreMultiUnidades">
                                    @foreach ($multiUnidadesArticulo as $multiUnidadArticulo)
                                        <div class="col-md-12" id="multiUnidades">
                                            <div class="col-md-5">
                                                <div class="form-group mt10">
                                                    {!! Form::labelValidacion('factorUnidad', 'Unidad', 'negrita') !!}
                                                    {!! Form::select('factorUnidad[]', $select_multiUnidad, $multiUnidadArticulo->articlesUnits_unit, [
                                                        'class' => 'widthAll multi-select',
                                                        'placeholder' => 'Seleccione uno',
                                                        'required',
                                                    ]) !!}
                                                </div>
                                            </div>

                                            <div class="col-md-5">
                                                <div class="form-group mt10">
                                                    {!! Form::labelValidacion('factor', 'Factor', 'negrita') !!}
                                                    {!! Form::number('factor[]', $multiUnidadArticulo->articlesUnits_factor, [
                                                        'class' => 'form-control',
                                                        'required',
                                                    ]) !!}
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <a href='javascript:void(0);' class='remove_button btn btn-danger'
                                                    title='Remove field'>
                                                    <i class='fa fa-times' aria-hidden='true'></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach


                                </div>
                            @endif

                            {{-- <div class="col-md-12" id="multiUnidades">
                    <div class="col-md-5">
                        <div class="form-group mt10" >
                         {!! Form::labelValidacion('factorUnidad', 'Unidad', 'negrita') !!}
                        {!! Form::select('factorUnidad[]', $select_multiUnidad, null, array("class" => 'widthAll multi-select', 'placeholder' => 'Seleccione uno', 'required')) !!} 
                        </div>
                     </div>

                    <div class="col-md-5">
                      <div class="form-group mt10" >
                        {!! Form::labelValidacion('factor', 'Factor', 'negrita') !!}
                        {!! Form::number('factor[]',null,['class'=>'form-control', 'required']) !!}
                      </div>
                    </div>

                    <div class="col-md-2">
                        <a href='javascript:void(0);' class='remove_button btn btn-danger' title='Remove field'><i class='fa fa-times' aria-hidden='true'></i></a>
                    </div>
                </div> --}}

                            <div class="col-md-4">
                                <a href="javascript:void(0);" class="add_button btn btn-primary"
                                    title="Añador Multi-Unidad"><i class="fa fa-plus" aria-hidden="true"></i> Agregar otra
                                    unidad</a>
                            </div>

                            <div class="col-md-12">
                                <h2 class="text-black">Precios</h2>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::labelValidacion('precio1', 'Lista 1/Precio Lista', 'negrita') !!}
                                    {!! Form::text('precio1', '$' . number_format($articulo->articles_listPrice1, 2), [
                                        'class' => 'form-control',
                                        'id' => 'precio1',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::labelNOValidacion('precio2', 'Lista 2/Precio 2', 'negrita') !!}
                                    {!! Form::text('precio2', '$' . number_format($articulo->articles_listPrice2, 2), [
                                        'class' => 'form-control',
                                        'id' => 'precio2',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::labelNOValidacion('precio3', 'Lista 3/Precio 3', 'negrita') !!}
                                    {!! Form::text('precio3', '$' . number_format($articulo->articles_listPrice3, 2), [
                                        'class' => 'form-control',
                                        'id' => 'precio3',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-12"> </div>

                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::labelNOValidacion('precio4', 'Lista 4/Precio 4', 'negrita') !!}
                                    {!! Form::text('precio4', '$' . number_format($articulo->articles_listPrice4, 2), [
                                        'class' => 'form-control',
                                        'id' => 'precio4',
                                    ]) !!}
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group mt10">
                                    {!! Form::labelNOValidacion('precio5', 'Lista 5/Precio 5', 'negrita') !!}
                                    {!! Form::text('precio5', '$' . number_format($articulo->articles_listPrice5, 2), [
                                        'class' => 'form-control',
                                        'id' => 'precio5',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-4" id="inputCostoKit">
                                <div class="form-group mt10">
                                    {!! Form::labelNOValidacion('ultimoCosto', 'Costo del kit', 'negrita') !!}
                                    {!! Form::text('ultimoCosto', '$' . number_format($articulo->articles_costoTotal, 2), [
                                        'class' => 'form-control',
                                        'id' => 'costoKit',
                                        'readonly',
                                    ]) !!}
                                </div>
                            </div>


                            <div class="col-md-12">
                                <h2 class="text-black">Información Fiscal</h2>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mt10">
                                    {!! Form::label('claveProd', 'Clave de Producto o Servicio', ['class' => 'negrita']) !!}

                                    {!! Form::text('prodServ', $articulo->articles_productService, [
                                        'class' => 'form-control',
                                        'id' => 'prodServ',
                                        'autocomplete' => 'on',
                                    ]) !!}
                                    <span class="error-login" id="mensaje-error-prodServ" style="display: none">
                                        No se encontraron resultados para la búsqueda
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mt10">
                                    {!! Form::label('objImpuesto', 'Objeto de Impuesto', ['class' => 'negrita']) !!}
                                    {!! Form::select('objImpuesto', $create_objImp_array, $articulo->articles_objectTax, [
                                        'id' => 'select-search-hide-objImp',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>

                            <div class="col-md-6">
                                <div class="form-group mt10">
                                    {!! Form::label('fraccionArancelaria', 'Fracción Arancelaria', ['class' => 'negrita']) !!}

                                    {!! Form::text('fraccionArancelaria', $articulo->articles_tariffFraction, [
                                        'class' => 'form-control',
                                        'id' => 'fraccionArancelaria',
                                        'autocomplete' => 'on',
                                    ]) !!}
                                    <span class="error-login" id="mensaje-error-fraccionArancelaria"
                                        style="display: none">
                                        No se encontraron resultados para la búsqueda
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mt10">
                                    {!! Form::label('unidadAduana', 'Unidad Aduana', ['class' => 'negrita']) !!}

                                    {!! Form::text('unidadAduana', $articulo->articles_customsUnit, [
                                        'class' => 'form-control',
                                        'id' => 'unidadAduana',
                                        'autocomplete' => 'on',
                                    ]) !!}
                                    <span class="error-login" id="mensaje-error-unidadAduana" style="display: none">
                                        No se encontraron resultados para la búsqueda
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab2">
                            <div style="display: flex; justify-content: flex-end; align-items: flex-end">
                                <button type="button" id="search_button" class="btn btn-info" data-toggle="modal"
                                    data-target="#catArticulos">Buscar Productos</button>
                            </div>
                            <table id="editable" class="table table-striped">
                                <thead>
                                    <th>Clave</th>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th>Costo producto</th>
                                    <th>Cantidad</th>
                                    <th>Acción</th>
                                </thead>

                                <tbody id="trArticulos">
                                    {{-- Aqui mostramos los datos de los articulos --}}
                                    @if (isset($kitArticles))

                                        @foreach ($kitArticles as $article)
                                            <tr id="{{ $article['kitArticles_article'] }}">
                                                <td class="uneditable" tabindex="1">
                                                    {{ $article['kitArticles_article'] }}</td>
                                                <td class="uneditable" tabindex="1">
                                                    {{ $article['kitArticles_articleDesp'] }}</td>
                                                <td class="uneditable" tabindex="1">
                                                    {{ $article['kitArticles_tipo'] }}</td>
                                                <td class="uneditable numeroP"
                                                    id="costo-{{ $article['kitArticles_article'] }}" tabindex="1">
                                                    {{ '$' . number_format($article['articlesCost_averageCost'], 2) }}
                                                </td>
                                                <td class="numeroP" id="cantidad-{{ $article['kitArticles_article'] }}"
                                                    tabindex="1">
                                                    {{ number_format($article['kitArticles_cantidad'], 2) }}
                                                </td>
                                                <td class="uneditable" tabindex="1">
                                                    <buttom class='btn btn-danger'
                                                        onclick="eliminarFila('{{ $article['kitArticles_article'] }}', '{{ $article['kitArticles_id'] }}')">
                                                        X
                                                    </buttom>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>

                                <tfoot>
                                    <th><strong>TOTAL</strong></th>
                                    <th></th>
                                    <th></th>
                                    <th class="numeroP" id="costoTotal"><strong></strong></th>
                                    <th class="numeroP" id="cantidadTotal"><strong></strong></th>
                                </tfoot>
                            </table>

                            <input type="hidden" name="articulosLista" id="articulosLista" value="" />
                        </div><!-- tab-pane -->

                        <div class="tab-pane" id="tab3">
                            <div class="imgCont">
                                <div class="container-input">
                                    <input type="file" class="inputfile inputfile-5"
                                        data-multiple-caption="{count} archivos seleccionados" name="files[]"
                                        id="file-5" accept="image/*" multiple>

                                    <label for="file-5">
                                        <figure>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="iborrainputfile"
                                                width="20" height="17" viewBox="0 0 20 17">
                                                <path
                                                    d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z">
                                                </path>
                                            </svg>
                                        </figure>
                                        <span class="iborrainputfile">Seleccionar archivo</span>
                                    </label>
                                </div>
                                <div class="gallery">
                                    @if (isset($articulosImg) && $articulosImg != null)
                                        @foreach ($articulosImg as $articuloImg)
                                            <?php
                                            $FileArray = explode('/', $articuloImg['articlesImg_file']);
                                            $longitudFile = count($FileArray);
                                            $file = $FileArray[$longitudFile - 1];
                                            $quitamosDoblesDiagonales = str_replace(['//', '///', '////'], '/', 'archivo/' . $articuloImg['articlesImg_path']);
                                            
                                            ?>
                                            <div class='imgContenedorPreview' id="{{ $articuloImg['articlesImg_id'] }}">
                                                <input type="button" class="btn btn-danger imgBtn"
                                                    onclick="eliminarImg('{{ $articuloImg['articlesImg_id'] }}')"
                                                    value="X">
                                                <a data-fancybox='demo' data-src='{{ url($quitamosDoblesDiagonales) }}'>
                                                    <img src='{{ url($quitamosDoblesDiagonales) }}' class="imgPreview">
                                                </a>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    {!! Form::label('especifications', 'Especificaciones', ['class' => 'negrita']) !!}
                                    {!! Form::textarea('especifications', $articulo->articles_specifications, [
                                        'class' => 'form-control',
                                        'id' => 'especifications',
                                        'autocomplete' => 'on',
                                    ]) !!}
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-md-12 mt50 display-flex text-center">
                        {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                        {!! Form::submit('Guardar Producto', ['class' => 'btn btn-warning enviar']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para buscar los articulos --}}

    <div class="modal fade bd-example-modal-lg modal85" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true" id="catArticulos">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Lista de Productos</h5>

                </div>
                <div class="modal-body">
                    <div class="panel table-panel">
                        <table id="shTable85" class="table table-striped table-bordered widthAll">
                            <thead>
                                <tr>
                                    <th>Clave</th>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($articulos as $articulo)
                                    <tr>
                                        <td>{{ $articulo->articles_key }}</td>
                                        <td>{{ $articulo->articles_descript }}</td>
                                        <td>{{ $articulo->articles_type }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div><!-- panel -->
                </div>

                <div class="modal-footer">
                    <button type="button" id="agregarArticulos" class="btn btn-success"
                        data-dismiss="modal">Agregar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let articulosSeleccionados = {
            costoTotal: 0,
            cantidadTotal: 0,
        };
        //ahora hacemos que cuando le de clic en el botón de guardar aparezca el loader
        jQuery(".enviar").click(function() {
            //solo mostrar el loader si los campos están validados
            if (jQuery("#basicForm").valid()) {
                jQuery("#loader").show();
            }
        });


        jQuery(document).ready(function() {
            // Multiple images preview in browser
            const imagesPreview = function(input, placeToInsertImagePreview) {
                $(".sinGuardar").remove();
                if (input.files) {

                    const filesAmount = input.files.length;
                    let identificador = 0;
                    for (i = 0; i < filesAmount; i++) {
                        //Validamos q los files sean imagenes y no documentos
                        let tipoImagen = input.files[i].type.trim();
                        if (tipoImagen == 'image/png' || tipoImagen == 'image/jpg' || tipoImagen ==
                            'image/jpeg') {
                            jQuery("#loader").show();
                            const reader = new FileReader();
                            reader.onload = function(event) {
                                identificador++;
                                $($.parseHTML(
                                        `<div class='imgContenedorPreview sinGuardar'>
                                             <a data-fancybox='demo' data-src='${event.target.result}'>
                                                <img src='${event.target.result}' class="imgPreview">
                                            </a>
                                            </div>`
                                    ))
                                    .appendTo(
                                        placeToInsertImagePreview);
                                jQuery("#loader").hide();
                            }
                            reader.readAsDataURL(input.files[i]);
                        }
                    }
                }

            };

            $('#file-5').on('change', function() {
                imagesPreview(this, 'div.gallery');
            });

            const $select = jQuery(
                    '#select-search-hide-dg, #select-search-hide-keyClave, #select-search-hide-nameNombre, #select-search-hide-nameTipo, #select-search-hide-status, #select-search-hide-tipo, #select-search-hide-unidadventa, #select-search-hide-unidadtraspaso, #select-search-hide-unidadcompra, #select-search-hide-claveProd, #categoria, #grupo, #familia,  #select-search-hide-objImp, .multi-select'
                )
                .select2({
                    minimumResultsForSearch: -1
                });



            jQuery("#select-basic-empresa").select2();


            jQuery('#basicForm').validate({
                submitHandler: function(form) {
                    let banderaNullFU = false;
                    let banderaInfoFU = false;
                    let banderaNullF = false;
                    let banderaInfoF = false;

                    let validar = validarUnidadVenta();
                    if (validar) {
                        mensajeError(
                            'Debe seleccionar una unidad para cada factor O Ingresar un factor por unidad'
                        );
                        return false;
                    }

                    let validar2 = validarUnidadTraspaso();
                    if (validar2) {
                        mensajeError(
                            'Debe seleccionar una unidad para cada factor O Ingresar un factor por unidad'
                        );
                        return false;
                    }

                    let validar3 = validarUnidadCompra();
                    if (validar3) {
                        mensajeError(
                            'Debe seleccionar una unidad para cada factor O Ingresar un factor por unidad'
                        );
                        return false;
                    }

                    $("select[name='factorUnidad[]']").each(function() {

                        if ($(this).val() != '') {
                            banderaInfoFU = true;
                        } else {
                            banderaNullFU = true;
                        }
                    });


                    $("input[name='factor[]']").each(function() {
                        if ($(this).val() != '') {
                            banderaInfoF = true;
                        } else {
                            banderaNullF = true;
                        }
                    });


                    if (banderaNullFU == true && banderaInfoFU == true) {
                        mensajeError(
                            'Debe seleccionar una unidad para cada factor O Ingresar un factor por unidad'
                        );
                        return false;
                    }

                    //validamos que exista un producto por lo menos
                    let isEmptyArticulos = false;
                    if ($('#select-search-hide-dg').val().trim() === 'Kit') {
                        //Recorremos el json de articulos
                        let keysArticulos = Object.keys(articulosSeleccionados);


                        if (keysArticulos.length == 2) {
                            mensajeError(
                                'Debe seleccionar al menos un producto'
                            );
                            isEmptyArticulos = true;
                            return false;
                        }

                        keysArticulos.forEach(keys => {
                            let isTexto = isNaN(keys);
                            if (!isTexto) {
                                if (articulosSeleccionados[keys].cantidad != '0') {
                                    articulosSeleccionados = {
                                        ...articulosSeleccionados,
                                        costoTotal: $('#costoTotal').text().trim().replace(
                                            /[$,]/g,
                                            ""),
                                        cantidadTotal: $('#cantidadTotal').text().trim()
                                            .replace(
                                                /[$,]/g,
                                                ""),
                                    };
                                    $('#articulosLista').attr('value', JSON.stringify(
                                        articulosSeleccionados));
                                } else {
                                    mensajeError(
                                        'Debe seleccionar al menos un producto'
                                    );
                                    isEmptyArticulos = true;
                                    return false;
                                }
                            }
                        });

                        if (isEmptyArticulos) {
                            return false;
                        }
                    }


                    if (banderaNullF == false && banderaInfoF == true) {
                        form.submit();
                    } else {
                        mensajeError(
                            'Debe seleccionar una unidad para cada factor O Ingresar un factor por unidad'
                        );
                        return false;
                    }

                    return false;
                },
                rules: {
                    keyClave: {
                        required: true,
                        maxlength: 10,
                    },
                    nameTipo: {
                        required: true,
                        maxlength: 100,
                    },
                    statusDG: {
                        required: true,
                    },
                    descripcion1: {
                        required: true,
                        maxlength: 100,
                    },
                    descripcion2: {
                        maxlength: 35,
                    },
                    unidadVenta: {
                        required: true,
                    },
                    unidadTraspaso: {
                        required: true,
                    },
                    unidadCompra: {
                        required: true,
                    },
                    iva: {
                        required: true,
                    },
                    precio1: {
                        required: true,
                        minlength: 0,
                    },
                    precio2: {
                        minlength: 0,
                    },
                    precio3: {
                        minlength: 0,
                    },
                    precio4: {
                        minlength: 0,
                    },
                    precio5: {
                        minlength: 0,
                    },
                    "factorUnidad[]": {
                        required: true,
                    },
                    "factor[]": {
                        required: true,
                    },
                },
                messages: {
                    keyClave: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    nameTipo: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    statusDG: {
                        required: "Este campo es requerido",
                    },
                    descripcion1: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    descripcion2: {
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    unidadVenta: {
                        required: "Este campo es requerido",
                    },
                    unidadTraspaso: {
                        required: "Este campo es requerido",
                    },
                    unidadCompra: {
                        required: "Este campo es requerido",
                    },
                    iva: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    precio1: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                        minlength: "El valor debe ser mayor a 0"
                    },
                    precio2: {
                        minlength: "El valor debe ser mayor a 0"
                    },
                    precio3: {
                        minlength: "El valor debe ser mayor a 0"
                    },
                    precio4: {
                        minlength: "El valor debe ser mayor a 0"
                    },
                    precio5: {
                        minlength: "El valor debe ser mayor a 0"
                    },
                    "factorUnidad[]": {
                        required: "Este campo es requerido",
                    },
                    "factor[]": {
                        required: "Este campo es requerido",
                    },

                },
                highlight: function(element) {
                    jQuery(element).closest('.form-group').addClass('has-error');
                },
                unhighlight: function(element) {
                    jQuery(element).closest('.form-group').removeClass('has-error');
                },
                success: function(element) {
                    jQuery(element).closest('.form-group').removeClass('has-error');
                }

            })

            $select.rules('add', {
                required: true,
                messages: {
                    required: "Este campo es requerido",
                }
            });

            $select.on('change', function() {
                $(this).trigger('blur');
            });

            $("#prodServ").autocomplete({
                minLength: 3,
                source: function(request, response) {
                    $.ajax({
                        url: "/prodServ/busqueda",
                        type: "GET",
                        data: {
                            prodServ: jQuery('#prodServ').val()
                        },
                        success: function({
                            prodServ
                        }) {
                            if (prodServ.length > 0) {
                                response(prodServ);
                                jQuery('#mensaje-error-prodServ').hide();
                            } else {
                                jQuery('#mensaje-error-prodServ').show();
                            }

                        }
                    })
                }
            });


            $("#fraccionArancelaria").autocomplete({
                minLength: 2,
                source: function(request, response) {
                    $.ajax({
                        url: "/fraccionArancelaria/busqueda",
                        type: "GET",
                        data: {
                            fraccionArancelaria: jQuery('#fraccionArancelaria').val()
                        },
                        success: function({
                            fraccionArancelaria
                        }) {
                            if (fraccionArancelaria.length > 0) {
                                response(fraccionArancelaria);
                                jQuery('#mensaje-error-fraccionArancelaria').hide();
                            } else {
                                jQuery('#mensaje-error-fraccionArancelaria').show();
                            }

                        }
                    })
                },
                select: function(event, ui) {
                    let {
                        label
                    } = ui.item;
                    label = label.split('-');
                    jQuery('#unidadAduana').val(label[2].trim());
                    jQuery('#unidadAduana').autocomplete("search", label[2].trim());
                }
            });

            $("#unidadAduana").autocomplete({
                minLength: 2,
                source: function(request, response) {
                    $.ajax({
                        url: "/unidadAduana/busqueda",
                        type: "GET",
                        data: {
                            unidadAduana: jQuery('#unidadAduana').val()
                        },
                        success: function({
                            unidadAduana
                        }) {
                            if (unidadAduana.length > 0) {
                                response(unidadAduana);
                                jQuery('#mensaje-error-unidadAduana').hide();
                            } else {
                                jQuery('#mensaje-error-unidadAduana').show();
                            }

                        }
                    })
                }
            });

            jQuery('.add_button').on('click', function() {

                $selectUnidades = $('.multi-select').select2('destroy');

                const objSelect = jQuery('#multiUnidades').clone(true);

                jQuery('.moreMultiUnidades').append(objSelect);

                jQuery('.multi-select').select2({
                    minimumResultsForSearch: -1
                });
                resetValitacion();
            });

            jQuery('.remove_button').on('click', function() {
                if (jQuery('.remove_button').length > 1) {
                    jQuery(this).parent().parent().remove();
                    resetValitacion();
                }
            });


            function resetValitacion() {
                const formValidador = jQuery('#basicForm').validate();
                formValidador.resetForm();
            }

            function mensajeError(message) {
                swal({
                    icon: "warning",
                    title: "¡Atención!",
                    text: message,
                    confirm: true,
                    closeOnClickOutside: false,
                    closeOnEsc: false,


                });
                jQuery("#loader").hide();
            }

            const formatoPrecio = ($id) => {
                let precio = $('#' + $id).val().replace(/['$', ',']/g, '');
                let formatoPrecio = currency(precio, {
                    separator: ',',
                    decimal: '.',
                    precision: 2,
                    symbol: '$'
                }).format();

                $('#' + $id).val(formatoPrecio);
            }

            $('#precio1').change(() => {
                formatoPrecio('precio1');
            });
            $('#precio2').change(() => {
                formatoPrecio('precio2');
            });
            $('#precio3').change(() => {
                formatoPrecio('precio3');
            });
            $('#precio4').change(() => {
                formatoPrecio('precio4');
            });
            $('#precio5').change(() => {
                formatoPrecio('precio5');
            });

            jQuery('#regreso').click(function() {
                window.location.href = "{{ route('catalogo.articulos.index') }}";
            });

            $('#ivaInput').change(() => {
                let iva = $('#ivaInput').val();
                if (iva != 0 && iva != 16) {
                    swal({
                        icon: "warning",
                        title: "¡Atención!",
                        text: "El iva solo puede ser 0 o 16",
                        confirm: true,
                        closeOnClickOutside: false,
                        closeOnEsc: false,
                    });
                    if (jQuery('#select-search-hide-objImp').val() == '02') {
                        $('#ivaInput').val(16.00);
                    }

                }

                if (iva == 0) {
                    //hacer que el select de impuesto sea 01
                    $('#select-search-hide-objImp').val('01').trigger('change');
                } else {
                    $('#select-search-hide-objImp').val('02').trigger('change');
                }
            });



            jQuery('#select-search-hide-objImp').change(function() {
                //sacar el valor del cambio
                let valor = jQuery(this).val();
                if (valor == '01') {
                    jQuery('#ivaInput').val('0.00');
                    // jQuery('#ivaInput').prop('readonly', true);
                } else {
                    jQuery('#ivaInput').val('16.00');
                    // jQuery('#ivaInput').prop('readonly', false);
                }


            });

            jQuery('#select-search-hide-objImp').change();

            function validarUnidadVenta() {

                let estado = true;
                let unidadVenta = jQuery('#select-search-hide-unidadventa').find("option:selected").text();

                //validar que ya exista en las multi unidades
                $('.multi-select').each(function() {
                    let unidad = $(this).find("option:selected").text();
                    if (unidad == unidadVenta) {
                        estado = false;
                    }
                });

                return estado;

            }

            function validarUnidadTraspaso() {

                let estado = true;
                let unidadVenta = jQuery('#select-search-hide-unidadtraspaso').find("option:selected").text();

                //validar que ya exista en las multi unidades
                $('.multi-select').each(function() {
                    let unidad = $(this).find("option:selected").text();
                    if (unidad == unidadVenta) {
                        estado = false;
                    }
                });

                return estado;

            }

            function validarUnidadCompra() {

                let estado = true;
                let unidadVenta = jQuery('#select-search-hide-unidadcompra').find("option:selected").text();

                //validar que ya exista en las multi unidades
                $('.multi-select').each(function() {
                    let unidad = $(this).find("option:selected").text();
                    if (unidad == unidadVenta) {
                        estado = false;
                    }
                });

                return estado;

            }

            //Mostramos la siguiente pestaña cuando el tipo sea Kit
            $("#select-search-hide-dg").change(() => {
                let tipoArticulo = $("#select-search-hide-dg").val();
                let mostrarPestaña = $(".tab2None");
                let costoKit = $("#inputCostoKit");
                if (tipoArticulo === "Kit") {
                    mostrarPestaña.show();
                    costoKit.show();
                } else {
                    mostrarPestaña.hide();
                    costoKit.hide();
                }
            });

            // Basic Wizard
            jQuery('#basicForm').bootstrapWizard({
                onTabShow: function(tab, navigation, index) {
                    tab.prevAll().addClass('done');
                    tab.nextAll().removeClass('done');
                    tab.removeClass('done');

                    const $total = navigation.find('li').length;
                    const $current = index + 1;

                    if ($current >= $total) {
                        $('#basicForm').find('.wizard .next').addClass('hide');
                    } else {
                        $('#basicForm').find('.wizard .next').removeClass('hide');

                    }
                }
            });

            $("#select-search-hide-dg").trigger('change');


            const tablaArticulos = jQuery("#shTable85").DataTable({
                select: {
                    style: "multi",
                },
                language: language,
                fnDrawCallback: function(oSettings) {
                    jQuery("#shTable_paginate ul").addClass("pagination-active");
                },
            });

            jQuery("#agregarArticulos").on("click", async function() {
                const rowData = tablaArticulos.rows(".selected").data();
                // console.log(rowData);
                let datos = [];
                let claves = Object.keys(rowData);
                for (let i = 0; i < claves.length; i++) {
                    if (!isNaN(claves[i])) {
                        datos.push(rowData[claves[i]]);
                    }
                }

                for (let i = 0; i < datos.length; i++) {
                    if ($("#" + datos[i][0]).length === 0) {
                        await $.ajax({
                            url: "/catalogo/articulos/ultimoCosto",
                            type: "GET",
                            data: {
                                articulo: datos[i][0],
                            },
                            success: function(data) {
                                 console.log(data);

                                if (data != null) {
                                    let costoPromedio = data.articlesCost_averageCost ===
                                        null ||
                                        data.articlesCost_averageCost === undefined ?
                                        0 : data.articlesCost_averageCost;

                                    let costoFormato = currency(costoPromedio, {
                                        separator: ",",
                                        decimal: ".",
                                        precision: 2,
                                        formatWithSymbol: true,
                                        symbol: "$",
                                    }).format();


                                    $('#trArticulos').append(`<tr id="${datos[i][0]}">
                                        <td class="uneditable" tabindex="1">${datos[i][0]}</td>
                                        <td class="uneditable" tabindex="1">${datos[i][1]}</td>
                                        <td class="uneditable" tabindex="1">${datos[i][2]}</td>
                                        <td class="uneditable numeroP" id="costo-${datos[i][0]}" tabindex="1">${ costoFormato }</td>
                                        <td class="numeroP" id="cantidad-${datos[i][0]}" tabindex="1">1.00</td>
                                        <td class="uneditable" tabindex="1"> 
                                            <buttom class='btn btn-danger' onclick="eliminarFila('${datos[i][0]}')">
                                                X
                                            </buttom>
                                        </td>
                                    </tr>`);

                                    articulosSeleccionados = {
                                        ...articulosSeleccionados,
                                        [datos[i][0]]: {
                                            clave: datos[i][0],
                                            articulo: datos[i][1],
                                            tipo: datos[i][2],
                                            costo: costoPromedio,
                                            cantidad: 1,
                                        }
                                    };
                                }
                            },
                        });


                        $('#editable').editableTableWidget().numericInputExample().find('td:first')
                            .focus();

                        //Validamos que el campo sea de tipo numero y sea editable
                        $('#editable td').on('validate', function(evt, newValue) {
                            let isEditable = $(this).hasClass('uneditable');
                            if (isNaN(newValue) || isEditable) {
                                return false; // mark cell as invalid 
                            }

                            let campoActualizar = $(this).attr('id').split('-');
                            let propiedad = campoActualizar[0];
                            let key = campoActualizar[1];

                            articulosSeleccionados[key] = {
                                ...articulosSeleccionados[key],
                                [`${propiedad}`]: newValue,
                            }
                        });

                        $('#editable td').on('click', function(evt, newValue) {
                            let isEditable = $(this).hasClass('uneditable');
                            if (isEditable) {
                                return false;
                            }
                        });

                        $('#editable td').on('dblclick', function(evt, newValue) {
                            let isEditable = $(this).hasClass('uneditable');
                            if (isEditable) {
                                return false;
                            }
                        });
                    }
                }


                let costoTotal = $("#costoTotal").text().trim();
                $("#costoKit").val("");
                $("#costoKit").val(formatoMexico(costoTotal));
                $("#shTable85").DataTable().rows(".selected").deselect();
            });

            $('#editable').editableTableWidget().numericInputExample().find('td:first')
                .focus();

            $('#editable td').on('validate', function(evt, newValue) {
                let isEditable = $(this).hasClass('uneditable');
                if (isNaN(newValue) || isEditable) {
                    return false; // mark cell as invalid 
                }

                let campoActualizar = $(this).attr('id').split('-');
                let propiedad = campoActualizar[0];
                let key = campoActualizar[1];

                articulosSeleccionados[key] = {
                    ...articulosSeleccionados[key],
                    [`${propiedad}`]: newValue,
                }
            });

            $('#editable td').on('click', function(evt, newValue) {
                let isEditable = $(this).hasClass('uneditable');
                if (isEditable) {
                    return false;
                }
            });

            $('#editable td').on('dblclick', function(evt, newValue) {
                let isEditable = $(this).hasClass('uneditable');
                if (isEditable) {
                    return false;
                }
            });


            //Buscamos los articulos relaciones cuando el articulo sea de tipo Kit

            let tipoArticulo = $("#select-search-hide-dg").val();

            if (tipoArticulo == "Kit") {
                //Hacemos el ajax para buscar los articulos relacionados
                $.ajax({
                    url: '/catalogo/articulos/relaciones/',
                    method: "GET",
                    data: {
                        articulo: $('input[name="keyClave"]').val(),
                    },
                    success: function({
                        data,
                        status
                    }) {
                        if (status) {
                            if (data.length != 0) {
                                
                                //console.log(data);
                                //Llenamos nuesto objeto con los datos recibidos
                                data.forEach(articulo => {
                                    console.log(articulo);
                                    articulosSeleccionados = {
                                        ...articulosSeleccionados,
                                        [`${articulo.kitArticles_article}`]: {
                                            id: articulo.kitArticles_id,
                                            clave: articulo.kitArticles_article,
                                            articulo: articulo.kitArticles_articleDesp,
                                            tipo: articulo.kitArticles_tipo,
                                            //Si costo es nulo que ponga 0 
                                            costo: articulo.articlesCost_averageCost === null ||
                                                articulo.articlesCost_averageCost === undefined ?
                                                0 : articulo.articlesCost_averageCost,
                                            cantidad: articulo.kitArticles_cantidad,
                                        },
                                        cantidadTotal: $('#cantidadTotal').text().replace(
                                            /[$,]/g, ""),
                                        costoTotal: $('#costoTotal').text().replace(/[$,]/g,
                                            ""),
                                    }
                                });
                            }
                        }
                    },
                })

            }
        });

        //colocar comas a los numeros
        const formatoMexico = (number) => {
            const exp = /(\d)(?=(\d{3})+(?!\d))/g;
            const rep = "$1,";
            let arr = number.toString().split(".");
            arr[0] = arr[0].replace(exp, rep);
            return arr[1] ? arr.join(".") : arr[0];
        };

        function eliminarFila(idFila, identificadorBD = null) {
            let costosTotal = $('#costoTotal').text().trim().replace(/[$,]/g, "");
            let costo = $("#costo-" + idFila).text().trim().replace(/[$,]/g, "");

            let cantidadTotal = $('#cantidadTotal').text().trim().replace(/[$,]/g, "");
            let cantidad = $("#cantidad-" + idFila).text().trim().replace(/[$,]/g, "");
            //Reseteamos los totales
            if (identificadorBD != null) {
                swal({
                    title: "¿Está seguro de eliminar el articulo del kit?",
                    text: "Esta acción eliminará la relación que hay en la base de datos",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                    buttons: ["Cancelar", "Aceptar"],
                }).then((willDelete) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (willDelete) {
                        $.ajax({
                            url: '/comercial/delete/articuloKit/',
                            method: 'GET',
                            data: {
                                id: identificadorBD,
                            },
                            success: function({
                                status
                            }) {
                                if (status == 200) {
                                    let nuevoCosto = parseFloat(costosTotal) - parseFloat(costo);
                                    let nuevaCantidad = parseFloat(cantidadTotal) - parseFloat(
                                        cantidad);
                                    $('#costoTotal').text('$' + formatoMexico(nuevoCosto));
                                    $("#cantidadTotal").text(formatoMexico(nuevaCantidad));
                                    $("#costoKit").val("");
                                    $("#costoKit").val('$' + formatoMexico(nuevoCosto));

                                    $("#" + idFila).remove();
                                    delete articulosSeleccionados[idFila];

                                    showMessage2('Articulo removido del Kit', 'El articulo con clave ' +
                                        identificadorBD + ' se elimino la referencia con el kit',
                                        'success');
                                } else {
                                    showMessage2('Error al remover el articulo del kit',
                                        'Contacte al administrador del sistema', 'error');
                                }
                            }
                        });

                    }
                })

            } else {
                let nuevoCosto = parseFloat(costosTotal) - parseFloat(costo);
                let nuevaCantidad = parseFloat(cantidadTotal) - parseFloat(cantidad);
                let costoFormato = currency
                $('#costoTotal').text('$' + formatoMexico(nuevoCosto));
                $("#cantidadTotal").text(formatoMexico(nuevaCantidad));
                $("#costoKit").val("");
                $("#costoKit").val('$' + formatoMexico(nuevoCosto));

                $("#" + idFila).remove();
                delete articulosSeleccionados[idFila];
            }
        }

        function trunc(x) {
            let t = x.toString();
            let regex = /(\d*.\d{0,2})/;
            return t.match(regex)[0];
        }

        function eliminarImg(id) {
            $("#loader").show();
            $.ajax({
                url: '/articulo/eliminar/img/',
                method: 'GET',
                data: {
                    idImg: id
                },
                success: function({
                    status,
                    mensaje
                }) {
                    if (status) {
                        $('#' + id).remove();
                        showMessage2('Imagen Articulo', mensaje, 'success');
                    } else {
                        showMessage2('Imagen Articulo', mensaje, 'error')
                    }
                }
            })
        }

        function showMessage2(titulo, mensaje, icon) {
            swal({
                title: titulo,
                text: mensaje,
                icon: icon,
                button: false,
                timer: 1500
            });
            $("#loader").hide();
        }

        ;
        (function(document, window, index) {
            let inputs = document.querySelectorAll('.inputfile');
            Array.prototype.forEach.call(inputs, function(input) {
                let label = input.nextElementSibling,
                    labelVal = label.innerHTML;

                input.addEventListener('change', function(e) {
                    let fileName = '';
                    if (this.files && this.files.length > 1)
                        fileName = (this.getAttribute('data-multiple-caption') || '').replace('{count}',
                            this.files.length);
                    else
                        fileName = e.target.value.split('\\').pop();

                    if (fileName)
                        label.querySelector('span').innerHTML = fileName;
                    else
                        label.innerHTML = labelVal;
                });
            });
        }(document, window, 0));

        ClassicEditor
        .create(document.querySelector('#especifications'), {
            toolbar: {
                items: [
                    'heading',
                    '|',
                    'bold',
                    'italic',
                    'link',
                    'bulletedList',
                    'numberedList',
                    'blockQuote',
                    '|',
                    'undo',
                    'redo'
                ]
            },
            language: 'es',
            removeButtons: 'Image, MediaEmbed' // Aquí se especifican los botones a remover
            
        })
        .catch(error => {
            console.error(error);
        });
    </script>
@endsection
