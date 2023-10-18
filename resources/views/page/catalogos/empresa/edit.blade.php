@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => ['catalogo.empresa.update', Crypt::encrypt($empresas_edit['companies_id'])],
                        'method' => 'PUT',
                        'id' => 'basicForm',
                        'files' => true,
                        'enctype' => 'multipart/form-data',
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

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <h2 class="text-black">Datos Generales de la Empresa</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('keyCompany', 'Clave', 'negrita') !!}
                            {!! Form::text('keyCompany', $empresas_edit['companies_key'], ['class' => 'form-control', 'disabled']) !!}

                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-8 mt10">
                        <div class="form-group">
                            {!! Form::labelValidacion('company', 'Nombre', 'negrita') !!}
                            {!! Form::text('company', $empresas_edit['companies_name'], ['class' => 'form-control']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-12">
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('shortCompany', 'Nombre Corto', 'negrita') !!}
                            {!! Form::text('shortCompany', $empresas_edit['companies_nameShort'], ['class' => 'form-control']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $empresas_edit['companies_status'], [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div><!-- form-group -->
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            @if ($empresas_edit['companies_logo'] != '')
                                <img src="{{ url('archivo/' . $empresas_edit['companies_logo']) }}"
                                    class="img-responsive editImage" alt="">
                            @else
                                <img src="{{ url('archivo/default.png') }}" class="img-responsive editImage" alt="">
                            @endif
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('status', 'Logo de la Empresa', ['class' => 'negrita']) !!}
                            <div class="contenedor-carga-imagen">
                                <button class="form-control btn-upload-logo-empresa btn-primary"><i
                                        class="fa fa-upload upload-icon" aria-hidden="true"></i>Seleccionar archivo</button>
                                <span class="title-img form-control">Archivo no seleccionado</span>
                            </div>
                            {!! Form::file('logoEmpresa', ['class' => 'logoEmpresa']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-12">
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('description', 'Descripción', ['class' => 'negrita']) !!}
                            {!! Form::textarea('description', $empresas_edit['companies_descript'], [
                                'class' => 'form-control',
                                'rows' => 2,
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('webSite', 'Sitio Web', ['class' => 'negrita']) !!}
                            {!! Form::text('webSite', $empresas_edit['companies_website'], ['class' => 'form-control']) !!}
                            <span class="help-block">Ejemplo: www.google.com</span>
                           </div> 
                    </div>

                    <div class="col-md-12 mt10">
                        <h2 class="text-black">Información General</h2>
                    </div>

                    <div class="col-md-12 mt10">
                        <div class="form-group">
                            {!! Form::labelValidacion('address', 'Dirección', 'negrita') !!}
                            {!! Form::text('address', $empresas_edit['companies_addres'], ['class' => 'form-control']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('country', 'País', 'negrita') !!}
                            {!! Form::select('country', $edit_pais_array, $empresas_edit['companies_country'], [
                                'id' => 'select-basic-country',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('state', 'Estado', 'negrita') !!}
                            {!! Form::select('state', $edit_estado_array, $empresas_edit['companies_state'], [
                                'id' => 'select-basic-state',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('city', 'Ciudad', 'negrita') !!}
                            {!! Form::select('city', $edit_ciudad_array, $empresas_edit['companies_city'], [
                                'id' => 'select-basic-city',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-12">
                    </div>


                    <div class="col-md-6">
                        <div class="form-group ">
                            {!! Form::labelValidacion('cpBusqueda', 'Código Postal', 'negrita') !!}
                            <input type="text" name="cpBusqueda" id="cpBusqueda" tabindex="-1"
                                value="{{ $empresas_edit['companies_cp'] }}" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group ">
                            {!! Form::labelValidacion('coloniaBusqueda', 'Colonia', 'negrita') !!}
                            <input type="text" name="coloniaBusqueda" id="coloniaBusqueda" tabindex="-1"
                                value="{{ $empresas_edit['companies_suburb'] }}" />
                        </div>
                    </div>



                    <div class="col-md-12">
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('phone1', 'Teléfono Oficina', ['class' => 'negrita']) !!}
                            {!! Form::text('phone1', $empresas_edit['companies_phone1'], ['class' => 'form-control']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('phone2', 'Celular 1', ['class' => 'negrita']) !!}
                            {!! Form::text('phone2', $empresas_edit['companies_phone2'], ['class' => 'form-control']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('phone3', 'Celular 2', ['class' => 'negrita']) !!}
                            {!! Form::text('phone3', $empresas_edit['companies_phone3'], ['class' => 'form-control']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::labelValidacion('mail', 'Correo Electrónico', 'negrita') !!}
                            {!! Form::text('mail', $empresas_edit['companies_mail'], ['class' => 'form-control']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('proveedor', 'Proveedor referencia', ['class' => 'negrita']) !!}
                            {!! Form::select('proveedor', $create_proveedor_array, $empresas_edit['companies_referenceProvider'], [
                                'id' => 'select-basic-proveedor',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12 mt20">
                    </div>

                    <div class="col-md-12 mt10">
                        <h2 class="text-black">Datos Fiscales</h2>
                    </div>

                    <div class="col-md-12 mt10">
                        <div class="form-group">
                            {{ Form::checkbox('stamped', '1', $empresas_edit['companies_stamped'] === '1' ? true : false) }}
                            {!! Form::label('stamped', 'Timbrar Facturas', ['class' => 'negrita']) !!}
                        </div>
                    </div>

                    <div class="col-md-5 mt10">
                        <div class="form-group">
                            {!! Form::labelValidacion('rfc', 'RFC', 'negrita') !!}
                            {!! Form::text('rfc', $empresas_edit['companies_rfc'], ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-7 mt10">
                        <div class="form-group">
                            {!! Form::labelValidacion('taxRegime', 'Régimen Fiscal', 'negrita') !!}
                            {!! Form::select('taxRegime', $edit_regimen_array, $empresas_edit['companies_taxRegime'], [
                                'id' => 'select-basic-taxRegime',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>

                    <div class="col-md-5">
                        <div class="form-group">
                            {!! Form::label('employerRegistration', 'Registro Patronal o CURP', ['class' => 'negrita']) !!}
                            {!! Form::text('employerRegistration', $empresas_edit['companies_employerRegistration'], [
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="form-group">
                            {!! Form::label('manager', 'Representante', ['class' => 'negrita']) !!}
                            {!! Form::text('manager', $empresas_edit['companies_representative'], ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>

                    <div class="col-md-6">
                        <div class="certificados">
                            {!! Form::file('certificadoKey', ['class' => 'certificados-sat', 'id' => 'key']) !!}
                            <span class="negrita">Ruta Llave (Visible desde servidor SQL)</span>
                            <div class="ruta-certificado">
                                {!! Form::text(
                                    'key',
                                    trim($empresas_edit['companies_routeKey'], '' . $empresas_edit['empresas_rutaDocumentos'] . 'CFDI/'),
                                    ['class' => 'form-control', 'id' => 'certificate-key', 'disabled'],
                                ) !!}
                                <button id="btn-certicate-key" class="btn btn-primary btn-cerificados"><i
                                        class="fa fa-upload upload-icon" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="certificados">
                            {!! Form::file('certificadoCer', ['class' => 'certificados-sat', 'id' => 'cer']) !!}
                            <span class="negrita">Ruta Certificado (Visible desde servidor SQL)</span>
                            <div class="ruta-certificado">
                                {!! Form::text(
                                    'cer',
                                    trim($empresas_edit['companies_routeCertificate'], '' . $empresas_edit['empresas_rutaDocumentos'] . 'CFDI'),
                                    ['class' => 'form-control', 'id' => 'certificate-cer', 'disabled'],
                                ) !!}
                                <button id="btn-certicate-cer" class="btn btn-primary btn-cerificados"><i
                                        class="fa fa-upload upload-icon" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mt20">
                    </div>

                    <div class="col-md-6">
                        {!! Form::label('passwordKey', 'Contraseña SAT', ['class' => 'negrita']) !!}
                        <input id="passwordKey" type="password" class="form-control" name="passwordKey" disabled />

                    </div>

                    <div class="col-md-6">
                        <div class="form-group"
                            style="display: flex; flex-direction: column; justify-content: start; align-items: start">
                            {!! Form::label('changePassword', 'Cambiar contraseña SAT', ['class' => 'negrita']) !!}
                            <input type="checkbox" id="changePassword" name="changePassword">
                        </div>
                    </div>


                    <div class="col-md-12 mt20">
                    </div>

                    <h2 class="text-black mt20 col-md-12">Bóveda Documentos</h2>

                    <div class="col-md-12 mt10">
                        <div class="form-group" id="rut">
                            {!! Form::labelValidacion('routeBov', 'Ruta', 'negrita') !!}
                            {!! Form::text('routeBov', $empresas_edit['companies_routeFiles'], [
                                'class' => 'form-control',
                                'id' => 'keyRuta',
                            ]) !!}

                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>
                    <h2 class="text-black mt20 col-md-12">Otros</h2>

                    <div class="col-md-12 mt10">
                        <div class="form-group">
                            {{ Form::checkbox('calcularImpuesto', '1', $empresas_edit['companies_calculateTaxes'] === '1' ? true : false) }}
                            {!! Form::label('calcularImpuesto', 'No calcular impuesto', ['class' => 'negrita']) !!}
                        </div>
                    </div>

                    <div class="col-md-12 mt50 display-flex text-center">
                        {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                        {!! Form::submit('Guardar Empresa', ['class' => 'btn btn-warning enviar', 'id' => 'btn-crear-empresa']) !!}

                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
            <div>
            </div>


            <script>
                jQuery(document).ready(function() {
                    $('.aviso').hide();

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
                    $("#select2-chosen-1").text('{{ $empresas_edit['companies_cp'] }}');


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
                    $("#select2-chosen-2").text('{{ explode('-', $empresas_edit['companies_suburb'])[0] }}');

                    jQuery('#select-search-hide-dg, #select-search-hide-ig').select2({
                        minimumResultsForSearch: -1
                    });

                    const $select = jQuery(
                            "#select-basic-country, #select-basic-state, #select-basic-city, #select-basic-suburb, #select-basic-cp, #select-basic-taxRegime, #select-basic-proveedor"
                        )
                        .select2();

                    //cargamos la imagen al input file
                    jQuery(".btn-upload-logo-empresa").on("click", function(e) {
                        e.preventDefault();
                        jQuery(".logoEmpresa").click();
                    });

                    //Añadimos el nombre a nuestro input file personalizado
                    jQuery(".logoEmpresa").on("change", function(e) {
                        if (e.target.files[0].type === "image/jpeg" || e.target.files[0].type === "image/png" || e
                            .target.files[0].type === "image/jpg") {
                            if ($('.title-img').hasClass('error-img')) {
                                $('.title-img').removeClass('error-img');
                            }
                            $('.title-img')[0].innerHTML = e.target.files[0].name;
                        } else {
                            if (!$('.title-img').hasClass('error-img')) {
                                $('.title-img').addClass('error-img');
                                $('.title-img')[0].innerHTML = "Solo formatos JPG y PNG";
                            }
                        }
                    });

                    let estadoForm = true;

                    //si stamped es 1 entonces validar que los campos de certificados sean requeridos, si on nulos los campos que tire un mensaje un swal
                    let rutallave = '{{ $empresas_edit['companies_routeKey'] }}';
                    let rutacertificado = '{{ $empresas_edit['companies_routeCertificate'] }}';
                    let passwordKey = '{{ $empresas_edit['companies_passwordKey'] }}';
                    // console.log(rutallave);
                    // console.log(rutacertificado);
                    // console.log(passwordKey);
                    // console.log($('#passwordKey').val());
                    // console.log($('#key').val());
                    // console.log($('#cer').val());
                    $('#btn-crear-empresa').on('click', function(event) {
                            var isStampedChecked = $("input[name='stamped']").is(":checked");
                            //ahora comprobamos que los campos de certificados no esten vacios
                            if (isStampedChecked && (rutallave == '' || rutacertificado == '' || passwordKey == '')) {
                                    event.preventDefault(); // Prevenir el envío del formulario
                                    // Mostrar el mensaje de SweetAlert
                                    swal({
                                        icon: 'warning',
                                        title: 'Datos requeridos',
                                        text: 'Debes completar los campos de certificados antes de crear la empresa.',
                                    });

                                    estadoForm = false;
                                
                            }
                             
                            else {
                                estadoForm = true;
                            }
                        })

                    // Basic Form
                    jQuery("#basicForm").validate({
                        submitHandler: function(form) {
                            console.log(form, estadoForm);
                            if (!$('.title-img').hasClass('error-img') && estadoForm) {

                                form.submit();

                            } else {
                                return false;
                            }
                        },
                        rules: {
                            keyCompany: {
                                required: true,
                                maxlength: 10,
                            },
                            company: {
                                required: true,
                                maxlength: 100,
                            },
                            shortCompany: {
                                required: true,
                                maxlength: 50,
                            },
                            description: {
                                maxlength: 250,
                            },
                            address: {
                                required: true,
                                maxlength: 100,
                            },
                            cpBusqueda: {
                                required: true,
                                maxlength: 5,
                                minlength: 5,
                            },
                            country: {
                                required: true,
                            },
                            state: {
                                required: true,
                            },
                            city: {
                                required: true,
                            },
                            coloniaBusqueda: {
                                required: true,
                            },
                            mail: {
                                required: true,
                                email: true,
                            },
                            rfc: {
                                required: true,
                                maxlength: 100,
                            },
                            taxRegime: {
                                required: true,
                            },
                            employerRegistration: {
                                maxlength: 20,
                                required: function() {
                                    return $("#select-basic-taxRegime").val() === "612" ? true :
                                        false;
                                }
                            },
                            manager: {
                                maxlength: 250,
                            },
                            phone1: {
                                maxlength: 10,
                                minlength: 10,
                            },
                            phone2: {
                                maxlength: 10,
                                minlength: 10,
                            },
                            routeBov: {
                                required: true,
                                maxlength: 100,
                            },
                            //si stamped es 1 entonces validar que los campos de certificados sean requeridos
                            passwordKey: {
                                required: function() {
                                    return $("input[name='stamped']").is(":checked") ? true :
                                        false;
                                }
                            },
                            key: {
                                required: function() {
                                    return $("input[name='stamped']").is(":checked") ? true :
                                        false;
                                }
                            },
                            cer: {
                                required: function() {
                                    return $("input[name='stamped']").is(":checked") ? true :
                                        false;
                                }
                            },

                        },
                        messages: {
                            keyCompany: {
                                required: "Este campo es requerido",
                                maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                            },
                            company: {
                                required: "Este campo es requerido",
                                maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                            },
                            shortCompany: {
                                required: "Este campo es requerido",
                                maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                            },
                            description: {
                                maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                            },
                            address: {
                                required: "Este campo es requerido",
                                maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                            },
                            cpBusqueda: {
                                required: "Este campo es requerido",
                                maxlength: "El Código Postal debe ser de 5 digitos",
                                minlength: "El Código Postal debe ser de 5 digitos",
                                max: jQuery.validator.format('Por favor ingresa un valor menor o igual a {0}'),
                                min: jQuery.validator.format('Por favor ingresa un valor mayor o igual a {0}'),

                            },
                            country: {
                                required: "Este campo es requerido"
                            },
                            state: {
                                required: "Este campo es requerido"
                            },
                            city: {
                                required: "Este campo es requerido"
                            },
                            coloniaBusqueda: {
                                required: "Este campo es requerido"
                            },
                            mail: {
                                required: "Este campo es requerido",
                                email: "Ingrese un correo valido"
                            },
                            rfc: {
                                required: "Este campo es requerido",
                                maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                            },
                            taxRegime: {
                                required: "Este campo es requerido",
                            },
                            employerRegistration: {
                                maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                                required: "Este campo es requerido de acuerdo al regimen fiscal seleccionado"
                            },
                            manager: {
                                maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                            },
                            phone1: {
                                maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                                minlength: jQuery.validator.format('Maximo de {0} caracteres'),
                            },
                            phone2: {
                                maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                                minlength: jQuery.validator.format('Maximo de {0} caracteres'),
                            },
                            routeBov: {
                                required: "Este campo es requerido",
                                maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                            },
                            passwordKey: {
                                required: "Este campo es requerido",
                            },
                            key: {
                                required: "Este campo es requerido",
                            },
                            cer: {
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

                    //evento click al btn del certificado cer
                    jQuery("#btn-certicate-cer").on('click', function(e) {
                        e.preventDefault();
                        jQuery("#cer").click();

                    });

                    //Evento para escuchar el cambio al input file del certificado cer
                    jQuery("#cer").on('change', function(e) {
                        $('#certificate-cer')[0].value = e.target.files[0].name;
                    });

                    //evento click al btn del certificado key
                    jQuery("#btn-certicate-key").on('click', function(e) {
                        e.preventDefault();
                        jQuery("#key").click();

                    });

                    //Evento para escuchar el cambio al input file del certificado key
                    jQuery("#key").on('change', function(e) {
                        $('#certificate-key')[0].value = e.target.files[0].name;
                    });

                    jQuery("#keyRuta").on('change', function(e) {
                        let cadena = jQuery("#keyRuta").val();
                        let inicioDiagonal = cadena.indexOf("/");

                        let cadena2 = cadena.slice(-1);

                        let cadena3 = cadena.substr(cadena.length - 2, cadena.length);


                        let estado;

                        //si la ruta empieza con diagonal se le quita para que no se duplique
                        if (cadena2 != '/' || cadena3 == '//' || inicioDiagonal == 0) {
                            $('#keyRuta').css({
                                'color': '#a94442',
                                'border': '1px solid #a94442'
                            });
                            $('.aviso').show();


                            estado = false;
                        } else {
                            estado = true;
                            $('#keyRuta').css({
                                'color': 'black',
                                'border': '1px solid #ccc'
                            });
                            $('.aviso').hide();
                        }

                        //si la ruta empieza con diagonal se le quita para que no se duplique


                        estadoForm = estado;




                    });

                    //ahora hacemos que cuando le de clic en el botón de guardar aparezca el loader
                    jQuery(".enviar").click(function() {
                        //solo mostrar el loader si los campos están validados
                        if (jQuery("#basicForm").valid()) {
                            //validamos si el estado del formulario es true
                            if (estadoForm) {
                                jQuery("#loader").show();
                            } else {
                                jQuery("#loader").hide();
                            }
                        }
                    });

                    $("#changePassword").change(() => {
                        if ($("#changePassword").prop('checked')) {
                            $("#passwordKey").prop('disabled', false);
                        } else {
                            $("#passwordKey").prop('disabled', true);
                        }
                    });

                    jQuery('#regreso').on('click', function() {
                        window.location.href = "{{ route('catalogo.empresa.index') }}";
                    });

                });
            </script>
        @endsection
