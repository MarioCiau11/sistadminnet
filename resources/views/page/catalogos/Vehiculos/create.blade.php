@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open(['route' => 'catalogo.vehiculos.store', 'id' => 'basicForm']) !!}

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
                            {!! Form::text('keyClave', null, ['class' => 'form-control', 'disabled', 'id' => 'keyClaveID']) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameNombre', 'Nombre', 'negrita') !!}
                            {!! Form::text('nameNombre', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::label('namePlacas', 'Placas', ['class' => 'negrita']) !!}
                            {!! Form::text('namePlacas', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::label('capacidadVolumen', 'Capacidad en Volumen', ['class' => 'negrita']) !!}
                            {!! Form::number('capacidadVolumen', null, [
                                'class' => 'form-control',
                                'min' => '1',
                                'max' => '9999',
                                'step' => '0.01',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::label('capacidadPeso', 'Capacidad en Peso', ['class' => 'negrita']) !!}
                            {!! Form::number('capacidadPeso', null, [
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
                            {!! Form::select('agenteXOmision', $select_agente, null, [
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
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], 'Alta', [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameSucursal', 'Sucursal', 'negrita') !!}
                            {!! Form::select('nameSucursal', $select_sucursales, null, [
                                'id' => 'select-search-hide-dgS',
                                'class' => 'widthAll select-sucursal',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mt10">
                            {!! Form::label('nameCategoria', 'Categoria', ['class' => 'negrita']) !!}
                            {!! Form::select('nameCategoria', $select_categoria, null, [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mt10">
                            {!! Form::label('nameGrupo', 'Grupo', ['class' => 'negrita']) !!}
                            {!! Form::select('nameGrupo', $select_grupo, null, [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-grupo',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>



                    <div class="col-md-12 mt20 display-center">
                        {!! Form::submit('Crear Vehículo', ['class' => 'btn btn-success enviar']) !!}
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
                        required: false,
                        min: 1,
                        maxlength: 10,
                    },
                    capacidadPeso: {
                        number: true,
                        required: false,
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

        $.get('/create/getIDVehiculo', function(resp) {
            $.each(resp, function(i, item) {
                $('#keyClaveID').val(item)
            });
        });
    </script>
@endsection
