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
                                {!! Form::text('keyList', null, ['class' => 'form-control', 'disabled', 'id' => 'keyList']) !!}
                            </div>
                        </div>
    
                        <div class="col-md-4">
                            <div class="form-group mt10">
                                {!! Form::labelValidacion('nameList', 'Nombre', 'negrita') !!}
                                {!! Form::text('nameList', null, ['class' => 'form-control', 'id' => 'nameList']) !!}
                            </div>
                        </div>

                        <div class="col-md-4" style="display: none">
                            <div class="form-group mt10">
                                {!! Form::labelValidacion('articulos', 'Articulos', 'negrita') !!}
                                {!! Form::text('articulos', null, ['class' => 'form-control', 'id' => 'articulos']) !!}
                            </div>
                        </div>
    
                        <div class="col-md-4">
                            <div class="form-group mt10">
                                {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                                {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], 'Alta', [
                                    'id' => 'select-search-hide-dg',
                                    'class' => 'widthAll select-status',
                                ]) !!}
                            </div>
                        </div>

                    </div> <!-- tab-pane -->

                    <div class="tab-pane" id="tab2">
                  
                        <h2>Agregar Artículos a la Lista</h2>


                        <div class="col-md-3">
                            <a href="#" class="btn btn-primary btn-block" data-toggle="modal" data-target=".bs-example-modal-static"><span class="glyphicon glyphicon-plus"></span> Agregar Artículos</a>
                        </div>
                        <div class="col-md-12">
                           
                            <div class="panel table-panel">   
                                <table id="shTable1" class="table table-striped table-bordered widthAll">
                                    <thead class="">
                                        <tr>
                                            <th>Clave</th>
                                            <th>Nombre</th>
                                            <th>Último Costo</th>
                                            <th>Costo promedio</th>
                                            <th>Acciones</th>
                                        
                                        </tr>
                                    </thead>
                            
                                
                                    <tbody id="trArticulos">
                                        {{-- Aqui mostramos los datos de los articulos --}}
                                    </tbody>
                                </table>
                            </div><!-- panel -->
                        </div>

                    </div> <!-- tab-pane -->

                </div>




                    <div class="col-md-12 mt20 display-center">
                        {!! Form::submit('Crear Lista', ['class' => 'btn btn-success enviar']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog" style="width: 800px">
          <div class="modal-content">
              <div class="modal-header">
                  <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                  <h4 class="modal-title">Lista de Artículos</h4>
              </div>
 
              <div class="modal-body">
                <div class="panel table-panel">
                    <table id="shTable2" class="table table-striped table-bordered widthAll">
                        <thead>
                            <tr>
                                <th>Clave</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                {{-- <th>Ultimo Costo</th> --}}
                            </tr>
                        </thead>

                        <tbody>
                          @foreach ($articulos as $articulo)
                            <tr>
                                <td>{{ $articulo->articles_key }}</td>
                                <td>{{ $articulo->articles_descript }}</td>
                                <td>{{ $articulo->articles_type }}</td>
                                {{-- <td>{{ $articulo->articles_listPrice1 }}</td> --}}
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <input type="button" value="Agregar" id="agregarArticulo" class="btn btn-success">
                </div>

              </div><!-- modal-body -->
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

            const tablaPrincipal = jQuery("#shTable1").DataTable({
                paging: false,
                ordering: false,
                info: false,
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

            jQuery("#agregarArticulo").click(async function(){
                const rowData = tablaArticulos.rows(".selected").data();
                //limpiar tabla antes de agregar
                let datos = [];
                let claves = Object.keys(rowData);
                for (let i = 0; i < claves.length; i++) {
                    if (!isNaN(claves[i])) {
                        datos.push(rowData[claves[i]]);
                    }
                }
                // console.log(datos);
                //    tablaPrincipal.clear();
              
                for (let i = 0; i < datos.length; i++) {
                    const articleKey = datos[i][0];
        
                    // Verifica si el artículo ya existe en la tabla
                    const existingRow = tablaPrincipal.rows().data().toArray().find(row => row[0] === articleKey);
                    if (!existingRow) {
                        await $.ajax({
                            url: "/lista/getCosto",
                            type: "GET",
                            data: {
                                articulo: datos[i][0],
                            },
                            success: function(data) {
                                 console.log(data);

                                 let ultimoCosto = 0;
                                 let costoPromedio = data.articlesCost_averageCost == null ? 0 : data.articlesCost_averageCost;

                                 let costoFormato = currency(ultimoCosto, { separator: ",", decimal: ".", precision: 2, symbol: "$" }).format();
                                 let costoPromedioFormato = currency(costoPromedio, { separator: ",", decimal: ".", precision: 2, symbol: "$" }).format();

               
                                tablaPrincipal.row.add([
                                    datos[i][0],
                                    datos[i][1],
                                    costoFormato,
                                    costoPromedioFormato,
                                    '<button type="button" class="btn btn-danger btn-sm btn-remove-article"><span class="glyphicon glyphicon-trash"></span> Eliminar</button>' // Botón de eliminación
                                ]).draw(false);
                            },
                        });


                        
                    }
                }
                  $("#shTable1").DataTable().rows(".selected").deselect();


                // tablaPrincipal.rows.add(data).draw();
                jQuery(".bs-example-modal-static").modal("hide");
            });

            jQuery("#basicForm").submit(async function(e) {
                e.preventDefault();
                let data = await tablaPrincipal.rows().data();
                console.log(data);
                // return;
                let articulos = [];
                data.each(function(value, index) {
                   //ARMAR JSON
                //    console.log(value);
                     articulos.push({
                          "clave": value[0],
                          "nombre": value[1],
                          "costo": value[2],
                          "promedio": value[3],
                     });
                });

                //validar que el ultimo costo no sea 0
                // for (let i = 0; i < articulos.length; i++) {
                //     if (articulos[i].costo == '$0.00' || articulos[i].costo == '$0.00') {
                //         // console.log(articulos[i].costo);
                //         swal("Error", "El articulo " + articulos[i].clave + " no tiene costo", "error");
                //         return;
                //     }
                // }
                // console.log(articulos);
                const isValid = validarNombre();
                if (isValid) {
                    swal("Error", "El nombre de la lista no puede estar vacio", "error");
                    return;
                }
                // return
                jQuery("#articulos").val(JSON.stringify(articulos));
                this.submit();
            });

            // Agrega un manejador de eventos para el botón de eliminación
            jQuery("#shTable1").on("click", ".btn-remove-article", function() {
                const row = $(this).closest("tr");
                tablaPrincipal.row(row).remove().draw(false);
            });
            // const $select = jQuery("#select-search-hide-sucursal").select2();

            function validarNombre() {
                const estadoLista =
                    $("#nameList").val() === "" ? true : false;

                if (estadoLista) {
                    return true;
                } else {
                    return false;
                }
                
            }

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

            
            $.get('/lista/getId', function(resp) {
                    $.each(resp, function(i, item) {
                        $('#keyList').val(item)
                    });
                });

        });
    </script>
@endsection
