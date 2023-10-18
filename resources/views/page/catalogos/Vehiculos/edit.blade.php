@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => ['catalogo.vehiculos.update', Crypt::encrypt($vehiculo_edit->vehicles_key)],
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
                            <h2 class="text-black">Datos Generales del Vehículo</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('keyClave', 'Clave', 'negrita') !!}
                            {!! Form::text('keyClave', $vehiculo_edit->vehicles_key, ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameNombre', 'Nombre', 'negrita') !!}
                            {!! Form::text('nameNombre', $vehiculo_edit->vehicles_name, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::label('namePlacas', 'Placas', ['class' => 'negrita']) !!}
                            {!! Form::text('namePlacas', $vehiculo_edit->vehicles_plates, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('capacidadVolumen', 'Capacidad Volúmen','negrita') !!}
                            {!! Form::number('capacidadVolumen', (float) $vehiculo_edit->vehicles_capacityVolume, [
                                'class' => 'form-control',
                                'min' => '1',
                                'max' => '9999',
                                'step' => '0.01',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::label('capacidadPeso', 'Capacidad Peso', ['class' => 'negrita']) !!}
                            {!! Form::number('capacidadPeso', (float) $vehiculo_edit->vehicles_capacityWeight, [
                                'class' => 'form-control',
                                'min' => '1',
                                'max' => '9999',
                                'step' => '0.01',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::label('agenteXOmision', 'Operativo por Omisión', ['class' => 'negrita']) !!}
                            {!! Form::select('agenteXOmision', $select_agente, $vehiculo_edit->vehicles_defaultAgent, [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>

                    <div class="col-md-2">
                        <div class="form-group mt10">
                            {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $vehiculo_edit->vehicles_status, [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::label('nameSucursal', 'Sucursal', ['class' => 'negrita']) !!}
                            {!! Form::select('nameSucursal', $select_sucursales, $vehiculo_edit->vehicles_branchOffice, [
                                'id' => 'select-search-hide-dgS',
                                'class' => 'widthAll select-sucursal',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mt10">
                            {!! Form::label('nameCategoria', 'Categoria', ['class' => 'negrita']) !!}
                            {!! Form::select('nameCategoria', $select_categoria, $vehiculo_edit->vehicles_category, [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mt10">
                            {!! Form::label('nameGrupo', 'Grupo', ['class' => 'negrita']) !!}
                            {!! Form::select('nameGrupo', $select_grupo, $vehiculo_edit->vehicles_group, [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-grupo',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>



                    <div class="col-md-12 mt50 display-flex text-center">
                        {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                        {!! Form::submit('Guardar Vehículo', ['class' => 'btn btn-warning enviar']) !!}

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
                    '#select-search-hide-dg, #select-search-hide-keyClave, #select-search-hide-nameNombre, #select-search-hide-nameTipo, #select-search-hide-status, #select-search-hide-tipo'
                )
                .select2({
                    minimumResultsForSearch: -1
                });

            const $select = jQuery(
                    "#select-search-hide-dgS"
                )
                .select2({
                    minimumResultsForSearch: -1
                });
            jQuery("#select-basic-empresa").select2();

            jQuery('#basicForm').validate({
                rules: {
                    keyClave: {
                        required: true,
                        maxlength: 10,
                    },
                    nameNombre: {
                        required: true,
                        maxlength: 100,
                    },
                    nameSucursal: {
                        required: true,
                    },
                    capacidadVolumen: {
                        number: true,
                        min: 0,
                        maxlength: 10,
                    },
                    capacidadPeso: {
                        number: true,
                        min: 0,
                        maxlength: 10,
                    },
                    namePlacas: {
                        maxlength: 10,
                    },
                },
                messages: {
                    keyClave: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    nameNombre: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    nameSucursal: {
                        required: "Este campo es requerido",
                    },
                    capacidadVolumen: {
                        number: "Este campo debe ser numérico",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                        min: jQuery.validator.format('Por favor ingrese un valor mayor o igual a {0}'),
                        max: jQuery.validator.format('Por favor ingrese un valor menor o igual a {0}'),
                    },
                    capacidadPeso: {
                        number: "Este campo debe ser numérico",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                        min: jQuery.validator.format('Por favor ingrese un valor mayor o igual a {0}'),
                        max: jQuery.validator.format('Por favor ingrese un valor menor o igual a {0}'),
                    },
                    namePlacas: {
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
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

        jQuery('#regreso').click(function() {
            window.location.href = "{{ route('catalogo.vehiculos.index') }}";
        });
    </script>
@endsection
