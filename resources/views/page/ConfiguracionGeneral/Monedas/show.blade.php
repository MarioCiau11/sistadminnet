@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open(['route' => 'catalogo.sucursal.store', 'id' => 'basicForm']) !!}

                    {!! Form::macro('labelValidacion', function ($name, $labelName, $classes) {
                        return "<label for= '" .
                            $name .
                            "' class= '" .
                            $classes .
                            "'>" .
                            $labelName .
                            "<span class='asterisk'> *</span> </label>";
                    }) !!}

                    {!! Form::macro('labelNoValidacion', function ($name, $labelName, $classes) {
                        return "<label for= '" . $name . "' class= '" . $classes . "'>" . $labelName . '</label>';
                    }) !!}


                    <h2 class="text-black">Datos Generales de la Moneda</h2>
                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('keyMoneda', 'Clave', 'negrita') !!}
                            {!! Form::text('keyMoneda', $money['money_key'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameMoneda', 'Nombre', 'negrita') !!}
                            {!! Form::text('nameMoneda', $money['money_key'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameclaveSAT', 'Clave SAT', 'negrita') !!}
                            {!! Form::select('nameclaveSAT', $show_money_array, $money['money_keySat'], [
                                'id' => 'select-search-hide-sucursal',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                                'disabled',
                            ]) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameTipoCambio', 'Tipo de Cambio', 'negrita') !!}
                            {!! Form::text('nameTipoCambio', (float) $money['money_change'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelNoValidacion('nameDescripcion', 'Descripción', 'negrita') !!}
                            {!! Form::textarea('nameDescripcion', $money['money_descript'], [
                                'class' => 'form-control',
                                'rows' => 4,
                                'disabled',
                            ]) !!}

                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group">
                            {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $money['monedas_estatus'], [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12"></div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelNoValidacion('createat', 'Fecha de Creación', 'negrita') !!}
                            {!! Form::text('createat', $money['created_at'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelNoValidacion('updateat', 'Fecha de Actualización', 'negrita') !!}
                            {!! Form::text('updateat', $money['updated_at'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function() {
            jQuery(
                "#select-search-hide, #select-search-hide-dg, #select-search-hide-type, #select-search-hide-sucursal"
            ).select2({
                minimumResultsForSearch: -1,
            });

            jQuery("#select-search-hide-sucursal").select2();

            jQuery("#basicForm").validate({
                rules: {
                    keyMoneda: {
                        required: true,
                        maxlength: 10,
                    },
                    nameMoneda: {
                        required: true,
                        maxlength: 100,
                    },
                    nameTipoCambio: {
                        required: true,
                    },
                    nameclaveSAT: {
                        required: true,
                    },
                },
                messages: {
                    keyMoneda: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format("Máximo de {0} caracteres"),
                    },
                    nameMoneda: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format("Máximo de {0} caracteres"),
                    },
                    nameTipoCambio: {
                        required: "Este campo es requerido",
                        min: jQuery.validator.format("Mínimo de {0}"),
                        max: jQuery.validator.format("Máximo de {0}"),
                    },
                    nameclaveSAT: {
                        required: "Este campo es requerido",
                    },
                },
                highlight: function(element) {
                    jQuery(element).closest(".form-group").addClass("has-error");
                },
                unhighlight: function(element) {
                    jQuery(element).closest(".form-group").removeClass("has-error");
                },
                success: function(element) {
                    jQuery(element).closest(".form-group").removeClass("has-error");
                },
            });

            jQuery('#regreso').click(function() {
                window.location.href = "{{ route('configuracion.monedas.index') }}";
            });
        });
    </script>
@endsection
