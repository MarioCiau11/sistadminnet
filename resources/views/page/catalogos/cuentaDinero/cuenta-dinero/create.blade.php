@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open(['route' => 'catalogo.cuenta-dinero.store', 'id' => 'basicForm']) !!}

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
                            <h2 class="text-black">Datos Generales de la Cuenta de Banco/Efectivo</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('keyClaveBanco', 'Clave', 'negrita') !!}
                            {!! Form::text('keyClaveBanco', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameBanco', 'Banco', 'negrita') !!}
                            {!! Form::select('nameBanco', $selectInstFinancial, null, [
                                'id' => 'select-search-hide-nameBanco',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('tipoCuenta', 'Tipo de cuenta', 'negrita') !!}
                            {!! Form::select('tipoCuenta', ['Caja' => 'Caja/Efectivos', 'Banco' => 'Banco'], null, [
                                'id' => 'select-search-hide-tipoCuenta',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-12">
                    </div>


                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::labelValidacion('numeroCuenta', 'Número de cuenta', 'negrita') !!}
                            {!! Form::text('numeroCuenta', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::labelValidacion('cuentaCLABE', 'CLABE', 'negrita') !!}
                            {!! Form::text('cuentaCLABE', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-12"></div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('empresa', 'Empresa', 'negrita') !!}
                            {!! Form::select('empresa', $selectEmpresas, null, [
                                'id' => 'select-basic-empresa',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::labelValidacion('convenioBanco', 'Convenio banco', 'negrita') !!}
                            {!! Form::text('convenioBanco', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::labelValidacion('moneda', 'Moneda', 'negrita') !!}
                            {!! Form::select('moneda', $selectMonedas, null, [
                                'id' => 'select-search-hide-moneda',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12"></div>

                    <div class="col-md-8">
                        <div class="form-group">
                            {!! Form::labelValidacion('rBanco', 'Referencia banco', 'negrita') !!}
                            {!! Form::text('rBanco', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('status', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja'], 'Alta', [
                                'id' => 'select-search-hide-status',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-12 mt20 display-center">
                        {!! Form::submit('Crear Cuenta', ['class' => 'btn btn-success enviar']) !!}
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
                '#select-search-hide-dg, #select-search-hide-tipoCuenta,  #select-search-hide-status').select2({
                minimumResultsForSearch: -1
            });

            const $select = jQuery(
                "#select-basic-empresa, #select-search-hide-moneda, #select-search-hide-nameBanco").select2();

            jQuery('#basicForm').validate({
                rules: {
                    keyClaveBanco: {
                        required: true,
                        maxlength: 10,
                    },
                    nameBanco: {
                        required: true,
                        maxlength: 100,
                    },
                    tipoCuenta: {
                        required: true,
                    },
                    numeroCuenta: {
                        required: true,
                        maxlength: 50,
                    },
                    cuentaCLABE: {
                        required: true,
                        maxlength: 50,
                    },
                    empresa: {
                        required: true,
                    },
                    moneda: {
                        required: true,
                    },
                    convenioBanco: {
                        required: true,
                        maxlength: 50,
                    },
                    rBanco: {
                        required: true,
                        maxlength: 50,
                    },
                },
                messages: {
                    keyClaveBanco: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    nameBanco: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    tipoCuenta: {
                        required: "Este campo es requerido",
                    },
                    numeroCuenta: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    cuentaCLABE: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    empresa: {
                        required: "Este campo es requerido",
                    },
                    moneda: {
                        required: "Este campo es requerido",
                    },
                    convenioBanco: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    rBanco: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
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

            $select2.rules('add', {
                required: true,
                messages: {
                    required: "Este campo es requerido",
                }
            });

            $select2.on('change', function() {
                $(this).trigger('blur');
            });
        });
    </script>
@endsection
