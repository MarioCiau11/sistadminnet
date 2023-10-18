@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">

                    {!! Form::open([
                        'route' => ['catalogo.clientes.update', Crypt::encrypt($customer['customers_key'])],
                        'method' => 'PUT',
                        'id' => 'valWizard',
                        'files' => true,
                        'class' => 'panel-wizard',
                    ]) !!}
                    {!! Form::macro('labelValidacion', function ($name, $labelName, $classes) {
                        return "<label for='" .
                            $name .
                            "' class='" .
                            $classes .
                            "'>" .
                            $labelName .
                            "<span class='asterisk'> *</span></label>";
                    }) !!}

                    <ul class="nav nav-justified nav-wizard">
                        <li><a href="#tab1-4" data-toggle="tab">Datos Generales del Cliente</a></li>
                        <li><a href="#tab2-4" data-toggle="tab">Datos fiscales</a></li>
                        <li><a href="#tab3-4" data-toggle="tab">Documentos digitales</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane" id="tab1-4">


                            <div class="col-md-12">
                                <div class="col-md-6">
                                    <h2 class="text-black">Datos Generales del Cliente</h2>
                                </div>
                                <div class="col-md-6">
                                    <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                {!! Form::label('clave', 'Clave', ['class' => 'negrita']) !!}
                                {!! Form::text('clave', $customer['customers_key'], ['id' => 'clave', 'class' => 'form-control', 'disabled']) !!}
                            </div>

                            <?php
                            
                            $typePersona = $customer['customers_type'] == 0 ? 'Fisica' : 'Moral';
                            if ($typePersona == 'Fisica') {
                                $PF = true;
                                $PM = false;
                            } else {
                                $PF = false;
                                $PM = true;
                            }
                            ?>


                            <div class="form-group">
                                <div class="col-md-8">
                                    <div class="col-md-4 mt10">
                                        {!! Form::labelValidacion('tipoPersona', 'Persona Física', 'negrita') !!}
                                        {!! Form::radio('tipoPersona', 0, $PF, ['id' => 'personaFisica']) !!}
                                    </div>

                                    <div class="col-md-4 mt10">
                                        {!! Form::labelValidacion('tipoPersona', 'Persona Moral', 'negrita') !!}
                                        {!! Form::radio('tipoPersona', 1, $PM, ['id' => 'personaMoral']) !!}
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-12"></div>

                            <div class="form-group">
                                <div class="col-md-12">
                                    {!! Form::labelValidacion('razonSocial', 'Razón social', 'negrita') !!}
                                    {!! Form::text('razonSocial', $customer['customers_businessName'], ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::labelValidacion('rfc', 'RFC', 'negrita') !!}
                                    {!! Form::text('rfc', $customer['customers_RFC'], ['class' => 'form-control']) !!}
                                </div>
                            </div>


                            <div class="form-group">
                                <div class="col-md-6">
                                    {!! Form::labelValidacion('curp', 'CURP', 'negrita') !!}
                                    {!! Form::text('curp', $customer['customers_CURP'], ['class' => 'form-control']) !!}
                                </div>
                            </div>


                            <div class="col-md-12">
                                <h2 class="text-black">Información de representante legal</h2>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::labelValidacion('nameRepresentante', 'Nombre(s)', 'negrita') !!}
                                    {!! Form::text('nameRepresentante', $customer['customers_name'], ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::labelValidacion('apellidoPaterno', 'Apellido Paterno', 'negrita') !!}
                                    {!! Form::text('apellidoPaterno', $customer['customers_lastName'], ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-4">
                                    {!! Form::labelValidacion('apellidoMaterno', 'Apellido Materno', 'negrita') !!}
                                    {!! Form::text('apellidoMaterno', $customer['customers_lastName2'], ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('telefono', 'Teléfono celular', ['class' => 'negrita']) !!}
                                    {!! Form::number('telefono', $customer['customers_cellphone'], ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-8">
                                    {!! Form::label('email', 'Correo electrónico', ['class' => 'negrita']) !!}
                                    {!! Form::text('email', $customer['customers_mail'], ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-12">
                                <h2 class="text-black">DOMICILIO DE LA RAZÓN SOCIAL</h2>
                            </div>


                            <div class="col-md-6">
                            <div class="form-group">
                                    {!! Form::labelValidacion('direccion', 'Dirección', 'negrita') !!}
                                    {!! Form::text('direccion', $customer['customers_addres'], ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::labelValidacion('numExt', 'Exterior', 'negrita') !!}
                                    {!! Form::text('numExt', $customer['customers_outdoorNumber'], ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('numInt', 'Interior', ['class' => 'negrita']) !!}
                                    {!! Form::text('numInt', $customer['customers_interiorNumber'], ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-12">
                                    {!! Form::label('entreVialidades', 'Cruzamiento/Tablaje/Lote/Otro', ['class' => 'negrita']) !!}
                                    {!! Form::text('entreVialidades', $customer['customers_roads'], ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                    <div class="form-group">
                                    {!! Form::labelValidacion('pais', 'País', 'negrita') !!}
                                    {!! Form::select('pais', $edit_pais_array, $customer['customers_country'], [
                                        'id' => 'select-basic-pais',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno...',
                                    ]) !!}
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::labelValidacion('estado', 'Estado', 'negrita') !!}
                                    {!! Form::select('estado', $edit_estado_array, $customer['customers_state'], [
                                        'id' => 'select-basic-estado',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno...',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::labelValidacion('localidadMuni', 'Localidad/Municipio', 'negrita') !!}
                                    {!! Form::select('localidadMuni', $edit_municipio_array, $customer['customers_townMunicipality'], [
                                        'id' => 'select-basic-localidadMuni',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno...',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::labelValidacion('codigoPostal', 'Código Postal', 'negrita') !!}
                                    <input type="text" name="cpBusqueda" id="cpBusqueda" tabindex="-1"
                                        value="{{ $customer['customers_cp'] }}" />

                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::labelValidacion('coloniaFracc', 'Colonia', 'negrita') !!}
                                    <input type="text" name="coloniaBusqueda" id="coloniaBusqueda" tabindex="-1"
                                        value="{{ $customer['customers_colonyFractionation'] }}" />

                                </div>
                            </div>
                            <div class="col-md-12"></div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('telefono1', 'Teléfono 1', ['class' => 'negrita']) !!}
                                    {!! Form::number('telefono1', $customer['customers_phone1'], ['class' => 'form-control']) !!}
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('telefono2', 'Teléfono 2', ['class' => 'negrita']) !!}
                                    {!! Form::number('telefono2', $customer['customers_phone2'], ['class' => 'form-control']) !!}
                                </div>
                            </div>


                            <div class="col-md-12"></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('contacto1', 'Contacto 1 / Representante legal', ['class' => 'negrita']) !!}
                                    {!! Form::text('contacto1', $customer['customers_contac1'], ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6">
                                    {!! Form::label('contacto2', 'Contacto 2', ['class' => 'negrita']) !!}
                                    {!! Form::text('contacto2', $customer['customers_contac2'], ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('email1', 'Correo electrónico', ['class' => 'negrita']) !!}
                                    {!! Form::text('email1', $customer['customers_mail1'], ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6">
                                    {!! Form::label('email2', 'Correo electrónico (adicional)', ['class' => 'negrita']) !!}
                                    {!! Form::text('email2', $customer['customers_mail2'], ['class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>

                            <div class="form-group">
                                <div class="col-md-12">
                                    {!! Form::label('observaciones', 'Observaciones', ['class' => 'negrita']) !!}
                                    {!! Form::textarea('observaciones', $customer['customers_observations'], [
                                        'class' => 'form-control',
                                        'rows' => 2,
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-12"></div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('grupo', 'Grupo', ['class' => 'negrita']) !!}
                                    {!! Form::select('grupo', $grupo_array, $customer['customers_group'], [
                                        'id' => 'select-search-hide-grupo',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno...',
                                    ]) !!}
                                </div>
                            </div>



                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('categoria', 'Categoria', ['class' => 'negrita']) !!}
                                    {!! Form::select('categoria', $categoria_array, $customer['customers_category'], [
                                        'id' => 'select-search-hide-categoria',
                                        'class' => 'widthAll select-grupo',
                                        'placeholder' => 'Seleccione uno...',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-4">
                                    {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                                    {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $customer['customers_status'], [
                                        'id' => 'select-search-hide-dg',
                                        'class' => 'widthAll select-status',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-12">
                                <h2 class="text-black">Condiciones comerciales</h2>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('listaPrecios', 'Lista de precios', ['class' => 'negrita']) !!}
                                    {!! Form::select(
                                        'listaPrecios',
                                        [
                                            'listPrice1' => 'Precio 1',
                                            'listPrice2' => 'Precio 2',
                                            'listPrice3' => 'Precio 3',
                                            'listPrice4' => 'Precio 4',
                                            'listPrice5' => 'Precio 5',
                                        ],
                                        $customer['customers_priceList'],
                                        ['id' => 'select-basic-listaPrecios', 'class' => 'widthAll select-grupo', 'placeholder' => 'Seleccione uno...'],
                                    ) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6">
                                    {!! Form::label('condicionPago', 'Condición de pago', ['class' => 'negrita']) !!}
                                    {!! Form::select('condicionPago', $condicion_array, $customer['customers_creditCondition'], [
                                        'id' => 'select-search-hide-dg',
                                        'class' => 'widthAll select-status',
                                        'placeholder' => 'Seleccione uno...',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6">
                                    {!! Form::label('limiteCredito', 'Limite de crédito', ['class' => 'negrita']) !!}
                                    {!! Form::text('limiteCredito', $customer['customers_creditLimit'], ['class' => 'form-control']) !!}
                                </div>
                            </div>
                        </div><!-- tab-pane -->

                        <div class="tab-pane" id="tab2-4">
                            <div class="col-md-12">
                                <h2 class="text-black">Datos fiscales</h2>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('identificadorCFDI', 'Régimen Fiscal', ['class' => 'negrita']) !!}
                                    {!! Form::select('regimenFiscal', $show_regimen_array, $customer['customers_taxRegime'], [
                                        'id' => 'select-basic-regimenFiscal',
                                        'class' => 'widthAll select-grupo',
                                        'placeholder' => 'Seleccione uno...',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('identificadorCFDI', 'Identificador de CFDI', ['class' => 'negrita']) !!}
                                    <select name="identificadorCFDI" id="select-basic-identificadorCFDI"
                                        class="widthAll select-grupo">
                                        <option value>Seleccione uno...</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12"></div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('identidadFiscal', 'Número registro identidad fiscal', ['class' => 'negrita']) !!}
                                    {!! Form::Text('identidadFiscal', $customer['customers_numRegIdTrib'], ['class' => 'form-control']) !!}
                                </div>
                            </div>


                            <div class="col-md-12"></div>

                        </div><!-- tab-pane -->

                        <div class="tab-pane" id="tab3-4">
                            <div class="col-md-12">
                                <h2 class="text-black">Documentos digitales</h2>
                            </div>

                            @foreach ($documentosCustomer as $document)
                                <?php
                                // nombre de los files
                                $pathFileArray = explode('/', $document['customersFiles_path']);
                                $longitudPath = count($pathFileArray);
                                $nameFileArray = explode('-', $pathFileArray[$longitudPath - 1]);
                                $nameFile = $nameFileArray[count($nameFileArray) - 1];
                                
                                //nameFiles de los documentos digitales
                                $FileArray = explode('/', $document['customersFiles_file']);
                                $longitudFile = count($FileArray);
                                $file = $FileArray[$longitudFile - 1];
                                
                                ?>

                                <div class="form-group">
                                    <div class="col-md-12">
                                        <div class="field_wrapper_edit">
                                            <div>
                                                <div class='col-md-12 mt10'>
                                                    <label class='negrita'>Nombre del documento</label>
                                                    <input class="form-control"
                                                        id="{{ $document['customersFiles_id'] . '-' . 'nombre' }}"
                                                        name="{{ $document['customersFiles_id'] }}-nombre" type='text'
                                                        value="{{ $nameFile }}" disabled />
                                                </div>

                                                <div class='col-md-12 mt10'>
                                                    {!! Form::label('nombreActualArchivo', 'Nombre del archivo actual', ['class' => 'negrita']) !!}
                                                    {!! Form::text('nombreActualArchivo', $file, ['class' => 'form-control', 'disabled']) !!}
                                                </div>

                                                <div class='col-md-7 mt10'>
                                                    <input type='file' name="{{ $document['customersFiles_id'] }}-file"
                                                        id="{{ $document['customersFiles_id'] . '-' . 'file' }}"
                                                        disabled />
                                                </div>

                                                <div class="col-md-4 mt10">
                                                    {!! Form::label('editNameDoc', 'Editar Campos', ['class' => 'negrita']) !!}
                                                    {!! Form::checkbox('docsEdit[]', $document['customersFiles_id'], null, [
                                                        'id' => $document['customersFiles_id'] . '-all',
                                                    ]) !!}
                                                </div>



                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    $("#{{ $document['customersFiles_id'] }}-all").click(function() {
                                        if ($("#{{ $document['customersFiles_id'] }}-all").is(':checked')) {
                                            $("#{{ $document['customersFiles_id'] }}-nombre").prop('disabled', false);
                                            $("#{{ $document['customersFiles_id'] }}-file").prop('disabled', false);
                                        } else {
                                            $("#{{ $document['customersFiles_id'] }}-nombre").prop('disabled', true);
                                            $("#{{ $document['customersFiles_id'] }}-file").prop('disabled', true);
                                        }

                                    });
                                </script>
                            @endforeach



                            <div class="form-group">
                                <div class="col-md-12">
                                    <div class="field_wrapper_create">

                                    </div>
                                </div>
                            </div>


                            <a href="javascript:void(0);" class="add_button btn btn-primary" title="Add field"><i
                                    class="fa fa-plus" aria-hidden="true"></i> Agregar otro archivo</a>
                        </div><!-- tab-pane -->
                    </div><!-- tab-content -->

                    <div class="col-md-12 display-flex text-center">
                    <ul class="list-unstyled wizard">
                        <li class="pull-left previous"><button type="button" class="btn btn-default">Anterior</button>
                        </li>
                            {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                            <li class="pull-right next"><button type="button" class="btn btn-primary">Siguiente</button>
                            </li>
                            <li class="pull-right finish hide"><button type="submit"
                                class="btn btn-primary enviar">Actualizar</button></li>
                            </ul>
                        </div>


                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    </div>


    <script>
        //ahora hacemos que cuando le de clic en el botón de guardar aparezca el loader
        jQuery(".enviar").click(function() {
            //solo mostrar el loader si los campos están validados
            if (jQuery("#valWizard").valid()) {
                jQuery("#loader").show();
            }
        });

        jQuery('#regreso').click(function() {
            window.location.href = "{{ route('catalogo.clientes.index') }}";
        });
        jQuery(document).ready(function() {

            $("#cpBusqueda").select2({
                placeholder: "Seleccione un codigo postal",
                destroy: true,
                allowClear: true,
                minimumInputLength: 3,
                ajax: {
                    url: "/cp/busqueda/",
                    dataType: 'json',
                    data: function(params) {
                        const queryParameters = {
                            search: params,
                            estado: $('#select-basic-estado').val(),
                        }
                        return queryParameters;
                    },
                    results: function(data) {
                        return {
                            results: $.map(data, function(item, key) {
                                return {
                                    text: item.c_CodigoPostal,
                                    id: item.c_CodigoPostal
                                }
                            })
                        };
                    },
                },
            });
            $("#select2-chosen-1").text('{{ $customer['customers_cp'] }}');


            //Buscamos las colonias de acuerdo a la ciudad
            $("#coloniaBusqueda").select2({
                placeholder: "Seleccione una colonia",
                destroy: true,
                allowClear: true,
                minimumInputLength: 3,
                ajax: {
                    url: "/colonia/busqueda/",
                    dataType: 'json',
                    data: function(params) {
                        const queryParameters = {
                            search: params,
                            cp: $("#cpBusqueda").val(),
                        }
                        return queryParameters;
                    },
                    results: function(data) {
                        return {
                            results: $.map(data, function(item, key) {
                                return {
                                    text: item.asentamiento,
                                    id: item.asentamiento + '-' + item.c_Colonia + '-' + item
                                        .c_CodigoPostal
                                }
                            })
                        };
                    },

                },
            });
            $("#select2-chosen-2").text('{{ explode('-', $customer['customers_colonyFractionation'])[0] }}');

            var maxField = 25; //Input fields increment limitation
            var addButton = $('.add_button'); //Add button selector
            var wrapperCreate = $('.field_wrapper_create'); //Input field wrapper
            // var fieldHTML = '<div><input type="text" name="field_name[]" value=""/><a href="javascript:void(0);" class="remove_button" title="Remove field"><img src="remove-icon.png"/></a></div>'; //New input field html 

            var fieldHTML =
                "<div><div class='col-md-11 mt10'><label for='nombreDocumento' class='negrita'>Nombre del documento</label><input class='form-control' name='nombreDocumento[]' type='text'></div><div class='col-md-1'><a href='javascript:void(0);' class='remove_button btn btn-danger' title='Remove field'><i class='fa fa-times' aria-hidden='true'></i></a></div><div class='col-md-12 mt10'><input type='file' name='field_name[]'/></div></div>"; //New input field html 
            var x = 1; //Initial field counter is 1
            $(addButton).click(function() { //Once add button is clicked
                if (x < maxField) { //Check maximum number of input fields
                    x++; //Increment field counter
                    $(wrapperCreate).append(fieldHTML); // Add field html
                }
            });

            $(wrapperCreate).on('click', '.remove_button', function(e) { //Once remove button is clicked
                e.preventDefault();
                $(this).parent('div').parent('div').remove(); //Remove field html
                x--; //Decrement field counter
            });


            jQuery('#select-search-hide-grupo, #select-search-hide-categoria, #select-search-hide-dg').select2({
                minimumResultsForSearch: -1
            });

            const $select = jQuery(
                    "#select-basic-coloniaFraccionamiento, #select-basic-localidadMuni, #select-basic-estado, #select-basic-pais, #select-basic-codigoPostal, #select-basic-listaPrecios, #select-basic-condicionPago, #select-basic-identificadorCFDI, #select-basic-regimenFiscal"
                )
                .select2();

            // Wizard With Form Validation
            jQuery("#valWizard").bootstrapWizard({
                onTabShow: function(tab, navigation, index) {
                    tab.prevAll().addClass("done");
                    tab.nextAll().removeClass("done");
                    tab.removeClass("done");

                    var $total = navigation.find("li").length;
                    var $current = index + 1;

                    if ($current >= $total) {
                        $("#valWizard").find(".wizard .next").addClass("hide");
                        $("#valWizard").find(".wizard .finish").removeClass("hide");
                    } else {
                        $("#valWizard").find(".wizard .next").removeClass("hide");
                        $("#valWizard").find(".wizard .finish").addClass("hide");
                    }
                },
                onTabClick: function(tab, navigation, index) {
                    var $valid = jQuery("#valWizard").valid();
                    if (!$valid) {
                        $validator.focusInvalid();
                        return false;
                    }
                    return true;
                },
                onNext: function(tab, navigation, index) {
                    var $valid = jQuery("#valWizard").valid();
                    if (!$valid) {
                        $validator.focusInvalid();
                        return false;
                    }
                },
            });


            // Wizard With Form Validation
            var $validator = jQuery("#valWizard").validate({
                submitHandler: function(form) {
                    let docNameData = false; //banderaInfoFU
                    let docNameVacio = false; //banderaNullFU
                    let fileData = false; //banderaInfoF
                    let fileVacio = false; //banderaNullF

                    let isMayor = false; //banderaMayor

                    $('input[name="nombreDocumento[]"]').each(function(key) {
                        if (key > 0) {
                            isMayor = true;
                        }
                    });

                    if (isMayor) {

                        $('input[name="nombreDocumento[]"]').each(function(key) {
                            // Manejamos la accion
                            if ($(this).val() != '') {
                                docNameData = true;

                            } else {
                                docNameVacio = true;
                                return false;
                            }

                        });

                        $('input[name="field_name[]"]').each(function(key) {
                            // Manejamos la accion
                            if ($(this).val() != '') {
                                fileData = true;
                            } else {
                                fileVacio = true;
                                return false;
                            }
                            //   console.log(key);
                        });

                        if (docNameData == true && fileData == true && fileVacio != true) {
                            form.submit();
                        } else {
                            mensajeError();
                            return false;
                        }

                    } else {
                        form.submit();
                    }
                    return false;
                },
                rules: {
                    tipoPersona: {
                        required: true,
                    },
                    razonSocial: {
                        required: true,
                    },
                    rfc: {
                        required: true,
                        maxlength: 15,
                    },
                    curp: {
                        required: function() {
                            if (jQuery('#personaFisica')[0].checked) {
                                return true;
                            }
                            return false;
                        },
                        maxlength: 18,
                    },
                    nameRepresentante: {
                        required: function() {
                            if (jQuery('#personaMoral')[0].checked) {
                                return true;
                            }
                            return false;
                        },
                        maxlength: 100,
                    },
                    apellidoPaterno: {
                        required: function() {
                            if (jQuery('#personaMoral')[0].checked) {
                                return true;
                            }
                            return false;
                        },
                        maxlength: 100,
                    },
                    apellidoMaterno: {
                        required: function() {
                            if (jQuery('#personaMoral')[0].checked) {
                                return true;
                            }
                            return false;
                        },
                        maxlength: 100,
                    },
                    telefono: {
                        maxlength: 10,
                    },
                    telefono1: {
                        maxlength: 10,
                    },
                    telefono2: {
                        maxlength: 10,
                    },
                    email: {
                        email: true,
                        maxlength: 50,
                    },
                    direccion: {
                        required: true,
                        maxlength: 100,
                    },
                    entreVialidades: {
                        maxlength: 100,
                    },
                    numExt: {
                        required: true,
                        maxlength: 50,
                    },
                    numInt: {

                        maxlength: 50,
                    },
                    coloniaBusqueda: {
                        required: true,
                    },
                    localidadMuni: {
                        required: true,
                    },
                    estado: {
                        required: true,
                    },
                    pais: {
                        required: true,
                    },
                    cpBusqueda: {
                        required: true,
                        minlength: 5,
                        maxlength: 5,
                    },
                    contacto1: {
                        maxlength: 50,
                    },
                    contacto2: {
                        maxlength: 50,
                    },
                    email1: {
                        maxlength: 50,
                        email: true,

                    },
                    email2: {
                        maxlength: 50,
                        email: true,
                    },
                    observaciones: {
                        maxlength: 250,
                    },
                    identidadFiscal: {
                        required: function() {
                            let rfcCliente = $("input[name='rfc']").val().toUpperCase();
                            let primerasDosLetras = rfcCliente.slice(0, 2);

                            if (primerasDosLetras == "XE") {
                                return true;
                            }

                            return false;
                        },
                    },
                },
                messages: {
                    tipoPersona: {
                        required: "Este campo es requerido",
                    },
                    razonSocial: {
                        required: 'Este campo es requerido',
                    },
                    rfc: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    curp: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    nameRepresentante: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    apellidoPaterno: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    apellidoMaterno: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    telefono: {
                        maxlength: jQuery.validator.format('Máximo de {0} caracteres'),
                    },
                    telefono1: {
                        maxlength: jQuery.validator.format('Máximo de {0} caracteres'),
                    },
                    telefono2: {
                        maxlength: jQuery.validator.format('Máximo de {0} caracteres'),
                    },
                    email: {
                        email: 'Ingrese un correo valido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    direccion: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    entreVialidades: {
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    numExt: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    numInt: {
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    coloniaFracc: {
                        required: 'Este campo es requerido',
                    },
                    localidadMuni: {
                        required: 'Este campo es requerido',
                    },
                    estado: {
                        required: 'Este campo es requerido',
                    },
                    pais: {
                        required: 'Este campo es requerido',
                    },
                    codigoPostal: {
                        required: "Este campo es requerido",
                    },
                    contacto1: {
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    contacto2: {
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    email1: {
                        email: 'Ingrese un correo valido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    email2: {
                        email: 'Ingrese un correo valido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    observaciones: {
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    'nombreDocumento[]': {
                        required: "Por favor llena este campo",
                    },
                    'field_name[]': {
                        required: "Por favor llena este campo",
                    },
                    identidadFiscal: {
                        required: "Este campo es requerido",
                    },

                },
                highlight: function(element) {
                    jQuery(element)
                        .closest(".form-group")
                        .removeClass("has-success")
                        .addClass("has-error");
                },
                success: function(element) {
                    jQuery(element).closest(".form-group").removeClass("has-error");
                },

            });

            $select.rules('add', {
                required: true,
                messages: {
                    required: "Este campo es requerido",
                }
            });

            $select.on('change', function() {
                $(this).trigger('blur');
            });

            function mensajeError() {
                swal({
                    icon: "warning",
                    title: "¡Atención!",
                    text: "Debe seleccionar un archivo por cada nombre de documento",
                    confirm: true,
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                });
                jQuery("#loader").hide();

            }


            $("#select-basic-regimenFiscal").change(async () => {
                let isEmpty = $("#select-basic-regimenFiscal").val() == "";
                let cfdiGuardado =
                    "{{ isset($customer) ? $customer['customers_identificationCFDI'] : '' }}";
                let existe = false;



                if (!isEmpty) {
                    await $.ajax({
                        url: "/cfdi/regimen",
                        method: "GET",
                        data: {
                            regimen: $("#select-basic-regimenFiscal").val(),
                        },
                        success: ({
                            status,
                            data
                        }) => {
                            $("#select-basic-identificadorCFDI").children().remove();
                            if (status == 200) {
                                $("#select-basic-identificadorCFDI").append(
                                    `<option value>Seleccione Uno ...</option>`
                                );
                                data.forEach(element => {
                                    $("#select-basic-identificadorCFDI").append(
                                        `<option value="${element.claveCFDI}">${element.descripcion} - ${element.claveCFDI}</option>`
                                    );

                                    if (element.claveCFDI ===
                                        cfdiGuardado
                                    ) {
                                        existe = true;
                                    }
                                });

                                if (existe) {
                                    $('#select-basic-identificadorCFDI').val(
                                        '{{ isset($customer) ? $customer['customers_identificationCFDI'] : '' }}'
                                    ).trigger(
                                        'change.select2');



                                } else {
                                    $('#select-basic-identificadorCFDI').val("")
                                        .trigger(
                                            'change.select2');

                                }



                            }
                        }
                    })
                } else {
                    $("#select-basic-identificadorCFDI").children().remove();
                    $("#select-basic-identificadorCFDI").append(
                        `<option value>Seleccione Uno ...</option>`
                    );
                    $('#select-basic-identificadorCFDI').val("")
                        .trigger(
                            'change.select2');
                }

            });

            $("#select-basic-regimenFiscal").change();


        });
    </script>
@endsection
