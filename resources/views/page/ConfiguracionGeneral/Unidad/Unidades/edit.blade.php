@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => ['configuracion.unidades.update', Crypt::encrypt($unidad->units_id)],
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
                            <h2 class="text-black">Datos generales de la Unidad de Medida</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameUnidad', 'Unidad de Medida', 'negrita') !!}
                            {!! Form::text('nameUnidad', $unidad['units_unit'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('numDecimalValida', 'Decimal Válida', 'negrita') !!}
                            {!! Form::number('numDecimalValida', $unidad['units_decimalVal'], [
                                'class' => 'form-control',
                                'min' => '1',
                                'max' => '999',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameclaveSAT', 'Clave SAT', 'negrita') !!}
                            {!! Form::select('nameclaveSAT', $edit_unidad_array, $unidad['units_keySat'], [
                                'id' => 'select-search-hide-sucursal',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-12">
                    </div>

                    <div class="col-md-1">
                        <div class="form-group mt10">
                            {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $unidad['units_status'], [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-12 mt50 display-flex text-center">
                        {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                        {!! Form::submit('Guardar Unidad de Medida', ['class' => 'btn btn-warning enviar']) !!}

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
                    '#select-search-hide-dg, #select-search-hide-nameUnidad, #select-search-hide-numDecimalValida, #select-search-hide-status, #select-search-hide-sucursal'
                    )
                .select2({
                    minimumResultsForSearch: -1
                });

            const $select = jQuery("#select-search-hide-sucursal").select2();

            jQuery('#basicForm').validate({
                rules: {
                    nameUnidad: {
                        required: true,
                        maxlength: 50,
                    },
                    numDecimalValida: {
                        required: true,
                        number: true,
                        min: 0,
                    },
                    nameclaveSAT: {
                        required: true,
                    }
                },
                messages: {
                    nameUnidad: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Máximo de {0} caracteres')
                    },
                    numDecimalValida: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Máximo de {0} caracteres'),
                        number: "Este campo debe ser un numero",
                        max: jQuery.validator.format('Máximo de {0} caracteres'),
                        min: jQuery.validator.format('Mínimo de {0} caracteres'),
                    },
                    nameclaveSAT: {
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
                window.location.href = "{{ route('configuracion.unidades.index') }}";
            });

        });
    </script>
@endsection
