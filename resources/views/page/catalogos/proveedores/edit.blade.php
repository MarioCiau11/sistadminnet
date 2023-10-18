@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => ['catalogo.proveedor.update', Crypt::encrypt($edit_provider['providers_key'])],
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
                        <li><a href="#tab1-4" data-toggle="tab">Datos Generales del Proveedor</a></li>
                        <li><a href="#tab3-4" data-toggle="tab">Documentos digitales</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane" id="tab1-4">
                            <div class="col-md-12">
                                <div class="col-md-6">
                                    {{-- <h2 class="text-black">Datos Generales de la Empresa</h2> --}}
                                </div>
                                <div class="col-md-6">
                                    <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-1">
                                    <div class="form-group">
                                        {!! Form::label('keyProveedor', 'Clave', ['class' => 'negrita']) !!}
                                        {!! Form::text('keyProveedor', $edit_provider['providers_key'], [
                                            'class' => 'form-control',
                                            'disabled',
                                            'id' => 'keyProveedor',
                                        ]) !!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-7 ">
                                        <div class="form-group">
                                            {!! Form::labelValidacion('nameProveedor', 'Nombre/Razón Social', 'negrita') !!}
                                            {!! Form::text('nameProveedor', $edit_provider['providers_name'], ['class' => 'form-control']) !!}
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        {!! Form::label('nameShortProveedor', 'Nombre Comercial', ['class' => 'negrita']) !!}
                                        {!! Form::text('nameShortProveedor', $edit_provider['providers_nameShort'], ['class' => 'form-control']) !!}
                                    </div>

                                    <div class="col-md-6 mt10">
                                        {!! Form::label('rfcProveedor', 'RFC', ['class' => 'negrita']) !!}
                                        {!! Form::text('rfcProveedor', $edit_provider['providers_RFC'], ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="col-md-6 mt10">
                                        {!! Form::label('curpProveedor', 'CURP', ['class' => 'negrita']) !!}
                                        {!! Form::text('curpProveedor', $edit_provider['providers_CURP'], ['class' => 'form-control']) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('type', 'Es:', ['class' => 'negrita']) !!}
                                        {!! Form::select(
                                            'type',
                                            ['Acreedor' => 'Acreedor', 'Proveedor' => 'Proveedor'],
                                            $edit_provider['providers_type'],
                                            ['id' => 'select-search-hide-type', 'class' => 'widthAll select-status'],
                                        ) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                                        {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $edit_provider['providers_status'], [
                                            'id' => 'select-search-hide-dg',
                                            'class' => 'widthAll select-status',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('grupo', 'Grupo', ['class' => 'negrita']) !!}
                                        {!! Form::select('grupo', $grupo_array, $edit_provider['providers_group'], [
                                            'id' => 'select-search-hide-type',
                                            'class' => 'widthAll select-status',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('categoria', 'Categoría', ['class' => 'negrita']) !!}
                                        {!! Form::select('categoria', $categoria_array, $edit_provider['providers_category'], [
                                            'id' => 'select-search-hide-dg',
                                            'class' => 'widthAll select-status',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-12">
                                        <h3 class="">DOMICILIO</h3>
                                    </div>

                                    <div class="col-md-8">
                                        {!! Form::label('direccionProveedor', 'Dirección', ['class' => 'negrita']) !!}
                                        {!! Form::text('direccionProveedor', $edit_provider['providers_address'], ['class' => 'form-control']) !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!! Form::label('outdoorNumber', 'Interior', ['class' => 'negrita']) !!}
                                        {!! Form::text('outdoorNumber', $edit_provider['providers_outdoorNumber'], ['class' => 'form-control']) !!}
                                    </div>

                                    <div class="col-md-2">
                                        {!! Form::label('interiorNumber', 'Exterior', ['class' => 'negrita']) !!}
                                        {!! Form::text('interiorNumber', $edit_provider['providers_interiorNumber'], ['class' => 'form-control']) !!}
                                    </div>

                                    <div class="col-md-12 mt10">
                                        {!! Form::label('vialidades', 'Cruzamientos/Tablaje/Lote/Otro', ['class' => 'negrita']) !!}
                                        {!! Form::text('vialidades', $edit_provider['providers_roads'], ['class' => 'form-control']) !!}
                                    </div>

                                    <div class="col-md-12 mt10"></div>

                                    <div class="col-md-6">
                                        <div class="form-group ">
                                            {!! Form::label('cpBusqueda', 'Código Postal', ['class' => 'negrita']) !!}

                                            <input type="text" name="cpBusqueda" id="cpBusqueda" tabindex="-1"
                                                value="{{ $edit_provider['providers_cp'] }}"
                                                placeholder="Ingrese su codigo postal" />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group ">
                                            {!! Form::label('coloniaBusqueda', 'Colonia', ['class' => 'negrita']) !!}
                                            <input type="text" name="coloniaBusqueda" id="coloniaBusqueda"
                                                value="{{ $edit_provider['providers_colonyFractionation'] }}" tabindex="-1"
                                                placeholder="Ingrese su colonia" />
                                        </div>
                                    </div>

                                    <div class="col-md-12"></div>

                                    <div class="col-md-4 mt10">
                                        {!! Form::label('municipio', 'Localidad/ Municipio', ['class' => 'negrita']) !!}
                                        {!! Form::select('municipio', $edit_municipio_array, $edit_provider['providers_townMunicipality'], [
                                            'id' => 'select-basic-city',
                                            'class' => 'widthAll select-status',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-5 mt10">
                                        {!! Form::label('estado', 'Estado', ['class' => 'negrita']) !!}
                                        {!! Form::select('estado', $edit_estado_array, $edit_provider['providers_state'], [
                                            'id' => 'select-basic-state',
                                            'class' => 'widthAll select-status',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('pais', 'País', ['class' => 'negrita']) !!}
                                        {!! Form::select('pais', $edit_pais_array, $edit_provider['providers_country'], [
                                            'id' => 'select-basic-country',
                                            'class' => 'widthAll select-status',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-12 mt10">
                                        {!! Form::label('observaciones', 'Observaciones', ['class' => 'negrita']) !!}
                                        {!! Form::textarea('observaciones', $edit_provider['providers_observations'], [
                                            'class' => 'form-control',
                                            'rows' => 2,
                                        ]) !!}
                                    </div>

                                    <div class="col-md-4 mt10">
                                        {!! Form::label('phone1', 'Teléfono 1', ['class' => 'negrita']) !!}
                                        {!! Form::number('phone1', $edit_provider['providers_phone1'], ['class' => 'form-control']) !!}
                                    </div>

                                    <div class="col-md-4 mt10">
                                        {!! Form::label('phone2', 'Teléfono 2', ['class' => 'negrita']) !!}
                                        {!! Form::number('phone2', $edit_provider['providers_phone2'], ['class' => 'form-control']) !!}
                                    </div>

                                    <div class="col-md-4 mt10">
                                        {!! Form::label('cellphone', 'Teléfono celular', ['class' => 'negrita']) !!}
                                        {!! Form::number('cellphone', $edit_provider['providers_cellphone'], ['class' => 'form-control']) !!}
                                    </div>

                                    <div class="col-md-6 mt10">
                                        {!! Form::label('contacto1', 'Contacto 1', ['class' => 'negrita']) !!}
                                        {!! Form::text('contacto1', $edit_provider['providers_contact1'], ['class' => 'form-control']) !!}
                                    </div>

                                    <div class="col-md-6 mt10">
                                        {!! Form::label('correo1', 'Correo electrónico 1', ['class' => 'negrita']) !!}
                                        {!! Form::text('correo1', $edit_provider['providers_mail1'], ['class' => 'form-control']) !!}
                                    </div>

                                    <div class="col-md-6 mt10">
                                        {!! Form::label('contacto2', 'Contacto 2', ['class' => 'negrita']) !!}
                                        {!! Form::text('contacto2', $edit_provider['providers_contact2'], ['class' => 'form-control']) !!}
                                    </div>

                                    <div class="col-md-6 mt10">
                                        {!! Form::label('correo2', 'Correo electrónico 2', ['class' => 'negrita']) !!}
                                        {!! Form::text('correo2', $edit_provider['providers_mail2'], ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="col-md-12">
                                        <h3 class="">INFORMACIÓN COMERCIAL Y FISCAL</h3>
                                    </div>
                                    <div class="col-md-3 mt10">
                                        {!! Form::label('condicion', 'Término de Crédito', ['class' => 'negrita']) !!}


                                        {!! Form::select('condicion', $condicion_array, $edit_provider['providers_creditCondition'], [
                                            'id' => 'select-basic-condition',
                                            'class' => 'widthAll select-status',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('formaPago', 'Forma de pago', ['class' => 'negrita']) !!}
                                        {!! Form::select('formaPago', $forma_pago_array, $edit_provider['providers_formPayment'], [
                                            'id' => 'select-basic-payment',
                                            'class' => 'widthAll select-status',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('nameMoneda', 'Moneda', ['class' => 'negrita']) !!}
                                        {!! Form::select('nameMoneda', $monedas, $edit_provider['providers_money'], [
                                            'id' => 'select-basic-money',
                                            'class' => 'widthAll select-status',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('regimenFiscal', 'Régimen fiscal', ['class' => 'negrita']) !!}
                                        {!! Form::select('regimenFiscal', $edit_regimen_array, $edit_provider['providers_taxRegime'], [
                                            'id' => 'select-basic-taxRegime',
                                            'class' => 'widthAll select-status',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-3 mt10">
                                        {!! Form::label('listaPrecios', 'Lista de Precios', ['class' => 'negrita']) !!}
                                        {!! Form::select('listaPrecios', $listaProveedor, $edit_provider['providers_priceList'], [
                                            'id' => 'select-search-hide-type',
                                            'class' => 'widthAll select-status',
                                            'placeholder' => 'Seleccione uno...',
                                        ]) !!}
                                    </div>

                                    <div class="col-md-12 mt10">
                                        {!! Form::label('cuentaBancaria', 'Cuenta bancaria', ['class' => 'negrita']) !!}
                                        {!! Form::text('cuentaBancaria', $edit_provider['providers_bankAccount'], ['class' => 'form-control']) !!}
                                    </div>
                                </div>



                            </div><!-- tab-pane -->
                        </div>
                        <div class="tab-pane" id="tab3-4">

                            <div class="col-md-12">
                                <h2 class="text-black">Documentos digitales</h2>
                            </div>



                            @foreach ($documentosProveedor as $document)
                                <?php
                                // nombre de los files
                                $pathFileArray = explode('/', $document['providersFiles_path']);
                                $longitudPath = count($pathFileArray);
                                $nameFileArray = explode('-', $pathFileArray[$longitudPath - 1]);
                                $nameFile = $nameFileArray[count($nameFileArray) - 1];
                                
                                //nameFiles de los documentos digitales
                                $FileArray = explode('/', $document['providersFiles_file']);
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
                                                        id="{{ $document['providersFiles_id'] . '-' . 'nombre' }}"
                                                        name="{{ $document['providersFiles_id'] }}-nombre" type='text'
                                                        value="{{ $nameFile }}" disabled />
                                                </div>

                                                <div class='col-md-12 mt10'>
                                                    {!! Form::label('nombreActualArchivo', 'Nombre del archivo actual', ['class' => 'negrita']) !!}
                                                    {!! Form::text('nombreActualArchivo', $file, ['class' => 'form-control', 'disabled']) !!}
                                                </div>

                                                <div class='col-md-7 mt10'>
                                                    <input type='file' name="{{ $document['providersFiles_id'] }}-file"
                                                        id="{{ $document['providersFiles_id'] . '-' . 'file' }}" disabled />
                                                </div>

                                                <div class="col-md-4 mt10">
                                                    {!! Form::label('editNameDoc', 'Editar Campos', ['class' => 'negrita']) !!}
                                                    {!! Form::checkbox('docsEdit[]', $document['providersFiles_id'], null, [
                                                        'id' => $document['providersFiles_id'] . '-all',
                                                    ]) !!}
                                                </div>



                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    $("#{{ $document['providersFiles_id'] }}-all").click(function() {
                                        if ($("#{{ $document['providersFiles_id'] }}-all").is(':checked')) {
                                            $("#{{ $document['providersFiles_id'] }}-nombre").prop('disabled', false);
                                            $("#{{ $document['providersFiles_id'] }}-file").prop('disabled', false);
                                        } else {
                                            $("#{{ $document['providersFiles_id'] }}-nombre").prop('disabled', true);
                                            $("#{{ $document['providersFiles_id'] }}-file").prop('disabled', true);
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
                        <li class="pull-left previous"><button type="button" class="btn btn-default">Anterior</button></li>
                        {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                        <li class="pull-right next"><button type="button" class="btn btn-primary">Siguiente</button></li>
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
            window.location.href = "{{ route('catalogo.proveedor.index') }}";
        });

        $(document).ready(function() {
            $("#cpBusqueda").select2({
                destroy: true,
                allowClear: true,
                minimumInputLength: 3,
                ajax: {
                    url: "/cp/busqueda/",
                    dataType: 'json',
                    data: function(params) {
                        const queryParameters = {
                            search: params,
                            estado: $('#select-basic-state').val(),
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
            let existeCp = "{{ isset($edit_provider['providers_cp']) }}";

            if (existeCp == "1") {
                $("#select2-chosen-1").text('{{ $edit_provider['providers_cp'] }}');
            }
            //Buscamos las colonias de acuerdo a la ciudad
            $("#coloniaBusqueda").select2({
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
            let existeColonia = "{{ isset($edit_provider['providers_colonyFractionation']) }}";

            if (existeColonia == "1") {
                $("#select2-chosen-2").text(
                    '{{ explode('-', $edit_provider['providers_colonyFractionation'])[0] }}');
            }


            jQuery('#select-search-hide-dg, #select-search-hide-type, #select-search-hide-sucursal').select2({
                minimumResultsForSearch: -1
            });


            jQuery(
                    "#select-basic-country, #select-basic-state, #select-basic-city, #select-basic-suburb, #select-basic-cp, #select-basic-taxRegime, #select-basic-condition, #select-basic-payment, #select-basic-money"
                )
                .select2();

            $('#nameDoc1').on('keyup', function(e) {
                let valor = e.target.value;

                if (valor.length > 0) {
                    $('#fileDoc').prop('required', true);
                } else {
                    $('#fileDoc').prop('required', false);
                }


            });

            var maxField = 25; //Input fields increment limitation
            var addButton = $('.add_button'); //Add button selector
            var wrapperCreate = $('.field_wrapper_create'); //Input field wrapper
            // var fieldHTML = '<div><input type="text" name="field_name[]" value=""/><a href="javascript:void(0);" class="remove_button" title="Remove field"><img src="remove-icon.png"/></a></div>'; //New input field html 

            var fieldHTML =
                "<div><div class='col-md-11 mt10'><label for='nombreDocumento' class='negrita'>Nombre del documento</label><input class='form-control' name='nombreDocumento[]' type='text' id='nameDoc1'></div><div class='col-md-1'><a href='javascript:void(0);' class='remove_button btn btn-danger' title='Remove field'><i class='fa fa-times' aria-hidden='true'></i></a></div><div class='col-md-12 mt10'><input type='file' name='field_name[]' id='fileDoc'/></div></div>"; //New input field html 
            var x = 1; //Initial field counter is 1
            $(addButton).click(function() { //Once add button is clicked
                if (x < maxField) { //Check maximum number of input fields
                    x++; //Increment field counter
                    $(wrapperCreate).append(fieldHTML); // Add field html
                }

                // $('#nameDoc1').prop('required',true);
                // $('#fileDoc').prop('required',true);
            });

            $(wrapperCreate).on('click', '.remove_button', function(e) { //Once remove button is clicked
                e.preventDefault();
                $(this).parent('div').parent('div').remove(); //Remove field html
                x--; //Decrement field counter
            });




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

            var validator = jQuery('#valWizard').validate({
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
                    nameProveedor: {
                        required: true,
                        maxlength: 100,
                    },
                    'nombreDocumento[]': {
                        required: true,
                    },
                    'field_name[]': {
                        required: true,
                    },
                },
                messages: {
                    nameProveedor: {
                        required: "Por favor llena este campo",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    'nombreDocumento[]': {
                        required: "Por favor llena este campo",
                    },
                    'field_name[]': {
                        required: "Por favor llena este campo",
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
        });
    </script>
@endsection
