@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => ['configuracion.monedas.update', Crypt::encrypt($money_edit['money_id'])],
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
                            <h2 class="text-black">Datos Generales de la Moneda</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('keyMoneda', 'Clave', 'negrita') !!}
                            {!! Form::text('keyMoneda', $money_edit['money_key'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameMoneda', 'Nombre', 'negrita') !!}
                            {!! Form::text('nameMoneda', $money_edit['money_name'], ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameclaveSAT', 'Clave SAT', 'negrita') !!}
                            {!! Form::select('nameclaveSAT', $edit_money_array, $money_edit['money_keySat'], [
                                'id' => 'select-search-hide-sucursal',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameTipoCambio', 'Tipo de Cambio', 'negrita') !!}
                            {!! Form::number('nameTipoCambio', (float) $money_edit['money_change'], [
                                'class' => 'form-control',
                                'min' => '1',
                                'max' => '999',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::label('nameDescripcion', 'Descripción', ['class' => 'negrita']) !!}
                            {!! Form::textarea('nameDescripcion', $money_edit['money_descript'], ['class' => 'form-control', 'rows' => 4]) !!}

                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group">
                            {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $money_edit['money_status'], [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12 mt50 display-flex text-center">
                        {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                        {!! Form::submit('Guardar Moneda', ['class' => 'btn btn-warning enviar']) !!}

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
            jQuery(
                "#select-search-hide, #select-search-hide-dg, #select-search-hide-type, #select-search-hide-sucursal"
            ).select2({
                minimumResultsForSearch: -1,
            });

            jQuery(document).ready(function() {
                jQuery(
                    "#select-search-hide, #select-search-hide-dg, #select-search-hide-type"
                ).select2({
                    minimumResultsForSearch: -1,
                });

                const $select = jQuery("#select-search-hide-sucursal").select2();

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
                    },
                    highlight: function(element) {
                        jQuery(element).closest(".form-group").addClass("has-error");
                    },
                    unhighlight: function(element) {
                        console.log(element);
                        jQuery(element).closest(".form-group").removeClass("has-error");
                    },
                    success: function(element) {
                        jQuery(element).closest(".form-group").removeClass("has-error");
                    },
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
            });
            jQuery('#regreso').click(function() {
                window.location.href = "{{ route('configuracion.monedas.index') }}";
            });
        });
    </script>
@endsection
