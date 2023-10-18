@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open(['route' => 'catalogo.instituciones-financieras.store', 'id' => 'basicForm']) !!}

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
                            {!! Form::text('keyInstitucionFinanciera', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameInstitucionFinanciera', 'Nombre', 'negrita') !!}
                            {!! Form::text('nameInstitucionFinanciera', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-12"></div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('pais', 'País', 'negrita') !!}
                            {!! Form::select('pais', $create_pais_array, null, [
                                'id' => 'select-basic-country',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('estado', 'Estado', 'negrita') !!}
                            {!! Form::select('estado', $create_estado_array, null, [
                                'id' => 'select-basic-estado',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('ciudad', 'Ciudad', 'negrita') !!}
                            {!! Form::select('ciudad', $create_ciudad_array, null, [
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
                            {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja'], 'Alta', [
                                'id' => 'select-search-hide-status',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12 mt20 display-center">
                        {!! Form::submit('Crear Institución Financiera', ['class' => 'btn btn-success enviar']) !!}
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

        });
    </script>
@endsection
