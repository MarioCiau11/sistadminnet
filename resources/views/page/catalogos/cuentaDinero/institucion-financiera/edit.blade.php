@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => ['catalogo.instituciones-financieras.update', Crypt::encrypt($instFinancial->instFinancial_id)],
                        'method' => 'PUT',
                        'id' => 'basicForm',
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

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <h2 class="text-black">Datos generales</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('keyInstitucionFinanciera', 'Clave', 'negrita') !!}
                            {!! Form::text('keyInstitucionFinanciera', $instFinancial->instFinancial_key, [
                                'class' => 'form-control',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameInstitucionFinanciera', 'Nombre', 'negrita') !!}
                            {!! Form::text('nameInstitucionFinanciera', $instFinancial->instFinancial_name, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-12"></div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('pais', 'País', 'negrita') !!}
                            {!! Form::select('pais', $edit_pais_array, $instFinancial->instFinancial_country, [
                                'id' => 'select-basic-country',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('estado', 'Estado', 'negrita') !!}
                            {!! Form::select('estado', $edit_estado_array, $instFinancial->instFinancial_state, [
                                'id' => 'select-basic-estado',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('ciudad', 'Ciudad', 'negrita') !!}
                            {!! Form::select('ciudad', $edit_ciudad_array, $instFinancial->instFinancial_city, [
                                'id' => 'select-basic-ciudad',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-12"></div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('status', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja'], $instFinancial->instFinancial_status, [
                                'id' => 'select-search-hide-status',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12 mt50 display-flex text-center">
                        {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                        {!! Form::submit('Guardar institución financiera', ['class' => 'btn btn-warning enviar']) !!}

                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <script>
        
        //ahora hacemos que cuando le de clic en el botón de guardar aparezca el loader
        jQuery(".enviar").click(function() {
                //solo mostrar el loader si los campos están validados
                if (jQuery("#basicForm").valid()) {
                    jQuery("#loader").show();
                }
            });
            
        jQuery(document).ready(function() {
            jQuery('#select-search-hide-status').select2({
                minimumResultsForSearch: -1
            });

            const $select = jQuery("#select-basic-country, #select-basic-estado, #select-basic-ciudad").select2();

            jQuery('#basicForm').validate({
                rules: {
                    keyInstitucionFinanciera: {
                        required: true,
                        maxlength: 20,
                    },

                    nameInstitucionFinanciera: {
                        required: true,
                        maxlength: 50,
                    },
                    pais: {
                        required: true,
                    },
                    estado: {
                        required: true,
                    },
                    ciudad: {
                        required: true,
                    },

                },
                messages: {
                    keyInstitucionFinanciera: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    nameInstitucionFinanciera: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    pais: {
                        required: "Este campo es requerido",
                    },
                    estado: {
                        required: "Este campo es requerido",
                    },
                    ciudad: {
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

            jQuery('#regreso').click(function() {
                window.location.href = "{{ route('catalogo.instituciones-financieras.index') }}";
            });
        });
    </script>
@endsection
