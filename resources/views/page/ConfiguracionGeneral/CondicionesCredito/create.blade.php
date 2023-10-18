@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open(['route' => 'configuracion.condiciones-credito.store', 'id' => 'basicForm']) !!}

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
                            <h2 class="text-black">Datos generales del término</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameCondicionCredito', 'Nombre', 'negrita') !!}
                            {!! Form::text('nameCondicionCredito', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('tipoCredito', 'Tipo de término', 'negrita') !!}
                            {!! Form::select('tipoCredito', ['Contado' => 'Contado', 'Crédito' => 'Crédito'], '1', [
                                'id' => 'select-search-tipoCredito',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12"></div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('vencimiento', 'Días de Vencimiento', ['class' => 'negrita']) !!}
                            {!! Form::number('vencimiento', null, ['class' => 'form-control', 'min' => '1', 'max' => '365']) !!}
                        </div>
                    </div>


                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('tiposDias', 'Tipos de Días', ['class' => 'negrita']) !!}

                            {!! Form::select('tiposDias', ['Naturales' => 'Naturales', 'Hábiles' => 'Hábiles'], null, [
                                'id' => 'select-search-hide-tiposDias',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('diasHabiles', 'Días Hábiles', ['class' => 'negrita']) !!}

                            {!! Form::select('diasHabiles', ['Lun-Vie' => 'Lun-Vie', 'Lun-Sab' => 'Lun-Sab', 'Todos' => 'Todos'], null, [
                                'id' => 'select-search-hide-diasHabiles',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('metodoPago', 'Método de pago', ['class' => 'negrita']) !!}
                            {!! Form::select('metodoPago', $create_metodoPago_array, '1', [
                                'id' => 'select-basic-metodo-pago',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('status', 'Estatus', ['class' => 'negrita']) !!}

                            {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja'], '1', [
                                'id' => 'select-search-hide-status',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12 mt20 display-center">
                        {!! Form::submit('Crear Término de Crédito', ['class' => 'btn btn-success enviar']) !!}
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
            const $select2 = jQuery(
                    '#select-search-hide-tiposDias, #select-search-tipoCredito, #select-search-hide-diasHabiles,#select-search-hide-status'
                )
                .select2({
                    minimumResultsForSearch: -1
                });

            jQuery("#select-basic-metodo-pago").select2();

            jQuery('#basicForm').validate({
                rules: {
                    nameCondicionCredito: {
                        required: true,
                        maxlength: 50,
                    },
                    vencimiento: {
                        required: function() {
                            return $("#select-search-tipoCredito").val() === "Crédito";

                        },
                        number: true,
                        min: 1,
                        maxlength: 3,
                    },
                    tiposDias: {
                        required: function() {
                            return $("#select-search-tipoCredito").val() === "Crédito";
                        }
                    },

                    diasHabiles: {
                        required: function() {
                            return $("#select-search-tipoCredito").val() === "Crédito";
                        }
                    },
                    tipoCredito: {
                        required: true,
                    }
                },
                messages: {
                    nameCondicionCredito: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    vencimiento: {
                        required: "Este campo es requerido",
                        number: "Este campo debe ser un número",
                        min: jQuery.validator.format('Minimo de {0} día'),
                        maxlength: jQuery.validator.format('Maximo de {0} días'),
                        step: "Este campo debe ser un número entero",
                    },
                    tiposDias: {
                        required: "Este campo es requerido",
                    },
                    diasHabiles: {
                        required: "Este campo es requerido",
                    },
                    tipoCredito: {
                        required: "Este campo es requerido",
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

            $select2.rules('add', {
                required: function() {
                    if ($("#select-search-tipoCredito").val() === "Contado") {
                        return false;
                    }
                    return true;
                },
                messages: {
                    required: "Este campo es requerido",
                }
            });

            $select2.on('change', function() {
                $(this).trigger('blur');
            });

            const tipoCredito = jQuery('#select-search-tipoCredito');

            if (tipoCredito.val() === 'Contado') {
                jQuery('#vencimiento').val("").attr('disabled', true).trigger('blur');
                jQuery('#select-search-hide-tiposDias').val("").attr('disabled', true).trigger('change');
                jQuery('#select-search-hide-diasHabiles').val("").attr('disabled', true).trigger('change');

            }

            tipoCredito.on('change', function() {
                if (tipoCredito.val() === 'Contado') {
                    jQuery('#vencimiento').val("").attr('disabled', true).trigger('blur');
                    jQuery('#select-search-hide-tiposDias').val("").attr('disabled', true).trigger(
                        'change');
                    jQuery('#select-search-hide-diasHabiles').val("").attr('disabled', true).trigger(
                        'change');
                } else {
                    jQuery('#vencimiento').attr('disabled', false);
                    jQuery('#select-search-hide-tiposDias').attr('disabled', false);
                    jQuery('#select-search-hide-diasHabiles').attr('disabled', false);

                }
            });

            jQuery('#select-search-hide-tiposDias').on('change', function() {
                if (jQuery('#select-search-hide-tiposDias').val() === 'Naturales' || jQuery(
                        '#select-search-hide-tiposDias').val() === '') {
                    jQuery('#select-search-hide-diasHabiles').val("").attr('disabled', true).trigger(
                        'change');
                } else {
                    jQuery('#select-search-hide-diasHabiles').attr('disabled', false);
                }
            });
        });
    </script>
@endsection
