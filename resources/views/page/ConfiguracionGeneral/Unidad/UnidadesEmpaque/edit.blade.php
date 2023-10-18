@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => ['configuracion.unidades-empaque.update', Crypt::encrypt($unidadEmp_edit['packaging_units_id'])],
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

                    <h2 class="text-black">Datos generales</h2>
                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameUnidadEmpaque', 'Unidad Empaque', 'negrita') !!}
                            {!! Form::text('nameUnidadEmpaque', $unidadEmp_edit['packaging_units_packaging'], [
                                'class' => 'form-control',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::label('namePeso', 'Peso', ['class' => 'negrita']) !!}
                            {!! Form::number('namePeso', $unidadEmp_edit['packaging_units_weight'], ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameUnidad', 'Unidad', 'negrita') !!}
                            {!! Form::select('nameUnidad', $unidad_unidad_array, $unidadEmp_edit['packaging_units_unit'], [
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
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $unidadEmp_edit['packaging_units_status'], [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-12 mt50 display-flex text-center">
                        {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                        {!! Form::submit('Guardar Unidad', ['class' => 'btn btn-warning enviar']) !!}

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
            const $select = jQuery(
                '#select-search-hide-dg, #select-search-hide-nameUnidad, #select-search-hide-sucursal, #select-search-hide-numDecimalValida, #select-search-hide-nameclaveSAT, #select-search-hide-status'
            ).select2({
                minimumResultsForSearch: -1
            });

            jQuery("#select-basic-empresa").select2();

            jQuery('#basicForm').validate({
                rules: {
                    nameUnidad: {
                        required: true,
                        maxlength: 10,
                    },
                    nameUnidadEmpaque: {
                        required: true,
                        maxlength: 100,
                    },
                },
                messages: {
                    nameUnidad: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    nameUnidadEmpaque: {
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
            jQuery('#regreso').click(function() {
                window.location.href = "{{ route('configuracion.unidades-empaque.index') }}";
            });
        });
    </script>
@endsection
