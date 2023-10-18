@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => ['configuracion.formas-pago.update', Crypt::encrypt($formaPago_edit['formsPayment_id'])],
                        'method' => 'PUT',
                        'id' => 'basicForm',
                        'files' => true,
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
                            <h2 class="text-black">Datos Generales de la Forma de Pago</h2>

                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('keyFormaPago', 'Clave', 'negrita') !!}
                            {!! Form::text('keyFormaPago', $formaPago_edit['formsPayment_key'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameFormaPago', 'Nombre', 'negrita') !!}
                            {!! Form::text('nameFormaPago', $formaPago_edit['formsPayment_name'], ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-12"></div>

                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('description', 'Descripción', ['class' => 'negrita']) !!}
                            {!! Form::textarea('description', $formaPago_edit['formsPayment_descript'], [
                                'class' => 'form-control',
                                'rows' => 2,
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12"></div>

                    <div class="col-md-5">
                        <div class="form-group">
                            {!! Form::labelValidacion('formaPagoSat', 'Forma de pago SAT', 'negrita') !!}
                            {!! Form::select('formaPagoSat', $edit_formaPago_array, $formaPago_edit['formsPayment_sat'], [
                                'id' => 'select-basic-SAT',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="form-group">
                            {!! Form::labelValidacion('moneda', 'Moneda', 'negrita') !!}
                            {!! Form::select('moneda', $edit_money_array, trim($formaPago_edit['formsPayment_money']), [
                                'id' => 'select-basic-moneda',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}

                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('status', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja'], $formaPago_edit['formsPayment_status'], [
                                'id' => 'select-search-hide',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12 mt20 display-flex text-center">
                        {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                        {!! Form::submit('Guardar forma de pago', ['class' => 'btn btn-warning enviar']) !!}
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
            jQuery('#select-search-hide').select2({
                minimumResultsForSearch: -1
            });

            const $select = jQuery("#select-basic-SAT, #select-basic-moneda").select2();

            jQuery('#basicForm').validate({
                rules: {
                    keyFormaPago: {
                        required: true,
                        maxlength: 10,
                    },
                    nameFormaPago: {
                        required: true,
                        maxlength: 50,
                    },
                    description: {
                        maxlength: 50,
                    },
                    moneda: {
                        required: true,
                    }
                },
                messages: {
                    keyFormaPago: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    nameFormaPago: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    description: {
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    moneda: {
                        required: 'Este campo es requerido',
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
                window.location.href = "{{ route('configuracion.formas-pago.index') }}";
            });
        });
    </script>
@endsection
