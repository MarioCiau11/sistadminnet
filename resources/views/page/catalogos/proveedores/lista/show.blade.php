@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open(['route' => 'listastore', 'id' => 'basicForm']) !!}

                    {!! Form::macro('labelValidacion', function ($name, $labelName, $classes) {
                        return "<label for= '" .
                            $name .
                            "' class= '" .
                            $classes .
                            "'>" .
                            $labelName .
                            "<span class='asterisk'> *</span> </label>";
                    }) !!}

                    <ul class="nav nav-justified nav-wizard">
                        <li class="active"><a href="#tab1" data-toggle="tab">Datos generales</a></li>
                        <li> <a href="#tab2" data-toggle="tab">Lista</a></li>
                        {{-- <li> <a href="#tab3" data-toggle="tab">Cargar Imagenes</a></li> --}}
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">
                        <h2 class="text-black">Datos generales</h2>
                        <div class="col-md-4">
                            <div class="form-group mt10">
                                {!! Form::labelValidacion('keyList', 'Clave', 'negrita') !!}
                                {!! Form::text('keyList', $provider['listProvider_id'], ['class' => 'form-control', 'disabled', 'id' => 'keyList']) !!}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mt10">
                                {!! Form::labelValidacion('nameList', 'Nombre', 'negrita') !!}
                                {!! Form::text('nameList', $provider['listProvider_name'], ['class' => 'form-control', 'disabled']) !!}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mt10">
                                {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                                {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $provider['listProvider_status'], [
                                    'id' => 'select-search-hide-dg',
                                    'class' => 'widthAll select-status',
                                    'disabled',
                                ]) !!}
                            </div>
                        </div>
                    </div>
        
                    <div class="tab-pane" id="tab2">
                  
                        <h2>Agregar Artículos a la Lista</h2>

                        <div class="col-md-12">
                           
                            <div class="panel table-panel">   
                                <table id="shTable1" class="table table-striped table-bordered widthAll">
                                    <thead class="">
                                        <tr>
                                            <th>Clave</th>
                                            <th>Nombre</th>
                                            <th>Último Costo</th>
                                            <th>Costo promedio</th>
                                        
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($listaArticulos as $articulo)
                                        <tr>
                                            <td>{{ $articulo->articlesList_article }}</td>
                                            <td>{{ $articulo->articlesList_nameArticle }}</td>
                                            <td>{{ "$".number_format($articulo->articlesList_lastCost, 2) }}</td>
                                            <td>{{ "$".number_format($articulo->articlesList_averageCost, 2) }}</td>
                                        </tr>
                                        @endforeach
                                        
                                    </tbody>
                                </table>
                            </div><!-- panel -->
                        </div>

                    </div> <!-- tab-pane -->

                </div>


                    <div class="col-md-12 mt20 display-center">
                        {{-- {!! Form::submit('Crear Moneda', ['class' => 'btn btn-success enviar']) !!} --}}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <script>
        
            
        jQuery(document).ready(function() {
            jQuery(
                "#select-search-hide-dg"
            ).select2({
                minimumResultsForSearch: -1,
            });

                        // const $select = jQuery("#select-search-hide-sucursal").select2();
                 const tablaPrincipal = jQuery("#shTable1").DataTable({
                paging: false,
                ordering: false,
                info: false,
                searching: false,
                language: language,
                fnDrawCallback: function (oSettings) {
                    jQuery("#shTable_paginate ul").addClass("pagination-active");
                },
            });

            const tablaArticulos = jQuery("#shTable2").DataTable({
                select: {
                    style: "multi",
                },
                language: language,
                fnDrawCallback: function (oSettings) {
                    jQuery("#shTable_paginate ul").addClass("pagination-active");
                },
            });

            jQuery("#agregarArticulo").click(function(){
                let data = tablaArticulos.rows({ selected: true }).data();
                //limpiar tabla antes de agregar
                tablaPrincipal.clear();

                tablaPrincipal.rows.add(data).draw();
                jQuery(".bs-example-modal-static").modal("hide");
            });

            jQuery("#basicForm").submit(function(e) {
                e.preventDefault();
                let data = tablaPrincipal.rows().data();
                // console.log(data);
                // return;
                let articulos = [];
                data.each(function(value, index) {
                   //ARMAR JSON
                   console.log(value);
                     articulos.push({
                          "clave": value[0],
                          "nombre": value[1],
                          "costo": value[2],
                     });
                });
                // return
                jQuery("#articulos").val(JSON.stringify(articulos));
                this.submit();
            });

            // const $select = jQuery("#select-search-hide-sucursal").select2();

            jQuery("#basicForm").validate({
                rules: {
                    keyMoneda: {
                        required: true,
                        maxlength: 10,
                    },
                    nameMoneda: {
                        required: true,
                        maxlength: 100,
                    },
                },
                messages: {
                    keyMoneda: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format("Máximo de {0} caracteres"),
                    },
                    nameMoneda: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format("Máximo de {0} caracteres"),
                    },
                  
                },
                highlight: function(element) {
                    jQuery(element).closest(".form-group").addClass("has-error");
                },
                unhighlight: function(element) {
                    console.log(element);
                    jQuery(element).closest(".form-group").removeClass("has-error");
                },
                success: function(element) {
                    jQuery(element).closest(".form-group").removeClass("has-error");
                },
            });


        });
    </script>
@endsection
