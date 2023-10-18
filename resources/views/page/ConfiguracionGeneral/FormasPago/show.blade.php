@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open(['route' => 'catalogo.concepto-gastos.store', 'id' => 'basicForm']) !!}

                    {!! Form::macro('labelValidacion', function ($name, $labelName, $classes) {
                        return "<label for= '" .
                            $name .
                            "' class= '" .
                            $classes .
                            "'>" .
                            $labelName .
                            "<span class='asterisk'> *</span> </label>";
                    }) !!}

                    {!! Form::macro('labelNOValidacion', function ($name, $labelName, $classes) {
                        return "<label for= '" . $name . "' class= '" . $classes . "'>" . $labelName . '</label>';
                    }) !!}



                    <h2 class="text-black">Datos Generales de la Forma de Pago</h2>
                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('keyFormaPago', 'Clave', 'negrita') !!}
                            {!! Form::text('keyFormaPago', $forma['formsPayment_key'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameFormaPago', 'Nombre', 'negrita') !!}
                            {!! Form::text('nameFormaPago', $forma['formsPayment_name'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-12"></div>

                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('description', 'Descripción', ['class' => 'negrita']) !!}
                            {!! Form::textarea('description', $forma['formsPayment_descript'], [
                                'class' => 'form-control',
                                'rows' => 2,
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12"></div>

                    <div class="col-md-5">
                        <div class="form-group">
                            {!! Form::labelValidacion('formaPagoSat', 'Forma de pago SAT', 'negrita') !!}
                            {!! Form::select('formaPagoSat', $show_formaPago_array, $forma['formsPayment_sat'], [
                                'id' => 'select-basic-SAT',
                                'class' => 'widthAll select-status select-control',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="form-group">
                            {!! Form::labelValidacion('moneda', 'Moneda', 'negrita') !!}
                            {!! Form::select('moneda', $show_money_array, trim($forma['formsPayment_money']), [
                                'id' => 'select-basic-moneda',
                                'class' => 'widthAll select-status select-control',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('status', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja'], $forma['formsPayment_status'], [
                                'id' => 'select-search-hide',
                                'class' => 'widthAll select-status',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelNOValidacion('createat', 'Fecha de Creación', 'negrita') !!}
                            {!! Form::text('createat', $forma['created_at'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelNOValidacion('updateat', 'Fecha de Actualización', 'negrita') !!}
                            {!! Form::text('updateat', $forma['updated_at'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function() {
            jQuery('#select-search-hide').select2({
                minimumResultsForSearch: -1
            });

            jQuery("#select-basic-SAT, #select-basic-moneda").select2();

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

            jQuery('#regreso').click(function() {
                window.location.href = "{{ route('configuracion.formas-pago.index') }}";
            });
        });
    </script>
@endsection
