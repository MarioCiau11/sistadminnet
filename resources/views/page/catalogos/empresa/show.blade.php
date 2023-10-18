@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open(['route' => 'catalogo.empresa.store', 'method' => 'POST', 'id' => 'basicForm', 'files' => true]) !!}

                    {!! Form::macro('labelValidacion', function ($name, $labelName, $classes) {
                        return "<label for='" .
                            $name .
                            "' class='" .
                            $classes .
                            "'>" .
                            $labelName .
                            "<span class='asterisk'> *</span></label>";
                    }) !!}
                    {!! Form::macro('labelNOValidacion', function ($name, $labelName, $classes) {
                        return "<label for='" . $name . "' class='" . $classes . "'>" . $labelName . '</label>';
                    }) !!}
                    <h2 class="text-black">Datos Generales de la Empresa</h2>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('keyCompany', 'Clave', 'negrita') !!}
                            {!! Form::text('keyCompany', $cat_empresas['companies_key'], ['class' => 'form-control', 'disabled']) !!}

                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-8 mt10">
                        <div class="form-group">
                            {!! Form::labelValidacion('company', 'Nombre', 'negrita') !!}
                            {!! Form::text('company', $cat_empresas['companies_name'], ['class' => 'form-control', 'disabled']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-12">
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('shortCompany', 'Nombre Corto', 'negrita') !!}
                            {!! Form::text('shortCompany', $cat_empresas['companies_nameShort'], ['class' => 'form-control', 'disabled']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $cat_empresas['companies_status'], [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                                'disabled',
                            ]) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            @if ($cat_empresas['companies_logo'] != '')
                                <img src="{{ url('archivo/' . $cat_empresas['companies_logo']) }}"
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
                                <button class="form-control  btn-primary" disabled><i class="fa fa-upload upload-icon"
                                        aria-hidden="true"></i>Seleccionar archivo</button>
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
                            {!! Form::textarea('description', $cat_empresas['companies_descript'], [
                                'class' => 'form-control',
                                'rows' => 2,
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('webSite', 'Sitio Web', ['class' => 'negrita']) !!}
                            {!! Form::text('webSite', $cat_empresas['companies_website'], ['class' => 'form-control', 'disabled']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-12 mt10">
                        <h2 class="text-black">Información General</h2>
                    </div>


                    <div class="col-md-12 mt10">
                        <div class="form-group">
                            {!! Form::labelValidacion('address', 'Dirección', 'negrita') !!}
                            {!! Form::text('address', $cat_empresas['companies_addres'], ['class' => 'form-control', 'disabled']) !!}
                        </div><!-- form-group -->
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('country', 'País', 'negrita') !!}
                            {!! Form::select('country', $show_pais_array, $cat_empresas['companies_country'], [
                                'id' => 'select-basic-country',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('state', 'Estado', 'negrita') !!}
                            {!! Form::select('state', $show_estado_array, $cat_empresas['companies_state'], [
                                'id' => 'select-basic-state',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('city', 'Ciudad', 'negrita') !!}
                            {!! Form::select('city', $show_ciudad_array, $cat_empresas['companies_city'], [
                                'id' => 'select-basic-city',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>


                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::labelValidacion('cp', 'Código Postal', 'negrita') !!}
                            {!! Form::text('address', $cat_empresas['companies_cp'], ['class' => 'form-control', 'disabled']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::labelValidacion('suburb', 'Colonia', 'negrita') !!}
                            {!! Form::text('address', explode('-', $cat_empresas['companies_suburb'])[0], [
                                'class' => 'form-control',
                                'disabled',
                            ]) !!}
                        </div><!-- form-group -->
                    </div>


                    <div class="col-md-12">
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('phone1', 'Teléfono Oficina', ['class' => 'negrita']) !!}
                            {!! Form::text('phone1', $cat_empresas['companies_phone1'], ['class' => 'form-control', 'disabled']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('phone2', 'Celular 1', ['class' => 'negrita']) !!}
                            {!! Form::text('phone2', $cat_empresas['companies_phone2'], ['class' => 'form-control', 'disabled']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('phone3', 'Celular 2', ['class' => 'negrita']) !!}
                            {!! Form::text('phone3', $cat_empresas['companies_phone3'], ['class' => 'form-control', 'disabled']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('mail', 'Correo Electrónico', ['class' => 'negrita']) !!}
                            {!! Form::text('mail', $cat_empresas['companies_mail'], ['class' => 'form-control', 'disabled']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-12 mt20">
                    </div>

                    <div class="col-md-12 mt10">
                        <h2 class="text-black">Datos Fiscales</h2>
                    </div>
                    <div class="col-md-12 mt10">
                        <div class="form-group">
                            {{ Form::checkbox('stamped', '1', $cat_empresas['companies_stamped'] === '1' ? true : false, ['disabled']) }}
                            {!! Form::label('stamped', 'Timbrar Facturar', ['class' => 'negrita']) !!}
                        </div>
                    </div>

                    <div class="col-md-5 mt10">
                        <div class="form-group">
                            {!! Form::labelValidacion('rfc', 'RFC', 'negrita') !!}
                            {!! Form::text('rfc', $cat_empresas['companies_rfc'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-7 mt10">
                        <div class="form-group">
                            {!! Form::labelValidacion(
                                '
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        taxRegime',
                                'Régimen Fiscal',
                                'negrita',
                            ) !!}
                            {!! Form::select('taxRegime', $show_regimen_array, $cat_empresas['companies_taxRegime'], [
                                'id' => 'select-basic-taxRegime',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>

                    <div class="col-md-5">
                        <div class="form-group">
                            {!! Form::label('employerRegistration', 'Registro Patronal o CURP', ['class' => 'negrita']) !!}
                            {!! Form::text('employerRegistration', $cat_empresas['companies_employerRegistration'], [
                                'class' => 'form-control',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="form-group">
                            {!! Form::label('manager', 'Representante', ['class' => 'negrita']) !!}
                            {!! Form::text('manager', $cat_empresas['companies_representative'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>

                    <div class="col-md-6">
                        <div class="certificados">
                            {!! Form::file('certificadoKey', ['class' => 'certificados-sat', 'id' => 'key']) !!}
                            <span class="negrita">Ruta Llave (Visible desde servidor SQL)</span>
                            <div class="ruta-certificado">
                                {!! Form::text('key', $cat_empresas['companies_routeKey'], [
                                    'class' => 'form-control',
                                    'id' => 'certificate-key',
                                    'disabled',
                                ]) !!}
                                <button id="btn-certicate-key" class="btn btn-primary btn-cerificados" disabled><i
                                        class="fa fa-upload upload-icon" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="certificados">
                            {!! Form::file('certificadoCer', ['class' => 'certificados-sat', 'id' => 'cer']) !!}
                            <span class="negrita">Ruta Certificado (Visible desde servidor SQL)</span>
                            <div class="ruta-certificado">
                                {!! Form::text('key', $cat_empresas['companies_routeCertificate'], [
                                    'class' => 'form-control',
                                    'id' => 'certificate-cer',
                                    'disabled',
                                ]) !!}
                                <button id="btn-certicate-cer" class="btn btn-primary btn-cerificados" disabled><i
                                        class="fa fa-upload upload-icon" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mt20">
                    </div>

                    <div class="col-md-6">
                        {!! Form::file('certificadoCer', ['class' => 'certificados-sat', 'id' => 'cer']) !!}
                        <span class="negrita">Contraseña SAT</span>
                        {!! Form::text('passwordKey', $cat_empresas['companies_passwordKey'], ['class' => 'form-control', 'disabled']) !!}
                    </div>


                    <div class="col-md-12 mt20">
                    </div>

                    <h2 class="text-black mt20 col-md-12">Bóveda Documentos</h2>

                    <div class="col-md-12 mt10">
                        <div class="form-group">
                            {!! Form::label('route', 'Ruta', ['class' => 'negrita']) !!}
                            {!! Form::text('route', $cat_empresas['companies_routeFiles'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>
                    <h2 class="text-black mt20 col-md-12">Otros</h2>

                    <div class="col-md-12 mt10">
                        <div class="form-group">
                            {{ Form::checkbox('calcularImpuesto', '1', $cat_empresas['companies_calculateTaxes'] === '1' ? true : false, ['disabled']) }}
                            {!! Form::label('calcularImpuesto', 'No calcular impuesto', ['class' => 'negrita']) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelNOValidacion('createat', 'Fecha de Creación', 'negrita') !!}
                            {!! Form::text('createat', $cat_empresas['created_at'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelNOValidacion('updateat', 'Fecha de Actualización', 'negrita') !!}
                            {!! Form::text('updateat', $cat_empresas['updated_at'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
            <div>
            </div>



            <script>
                jQuery(document).ready(function() {
                    jQuery('#select-search-hide-dg, #select-search-hide-ig').select2({
                        minimumResultsForSearch: -1
                    });

                    jQuery(
                            "#select-basic-country, #select-basic-state, #select-basic-city, #select-basic-suburb, #select-basic-cp, #select-basic-taxRegime"
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


                    // Basic Form
                    jQuery("#basicForm").validate({
                        submitHandler: function(form) {
                            if (!$('.title-img').hasClass('error-img')) {
                                form.submit();
                            } else {
                                return false;
                            }
                        },
                        rules: {
                            key: {
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
                            suburb: {
                                required: true,
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
                            cp: {
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
                            },
                            manager: {
                                maxlength: 250,
                            },
                            phone1: {
                                maxlength: 50,
                            },
                            phone2: {
                                maxlength: 50,
                            }

                        },
                        messages: {
                            key: {
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
                            suburb: {
                                required: "Este campo es requerido",
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
                            cp: {
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
                                maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                            },
                            manager: {
                                maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                            },
                            phone1: {
                                maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                            },
                            phone2: {
                                maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                            }
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

                });
            </script>
        @endsection
