@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open(['route' => 'catalogo.almacen.store', 'id' => 'basicForm']) !!}

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
                            <h2 class="text-black">Datos Generales del Almacén</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('keyAlmacen', 'Clave', 'negrita') !!}
                            {!! Form::text('keyAlmacen', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameSucursal', 'Nombre del Almacén', 'negrita') !!}
                            {!! Form::text('nameAlmacen', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group mt10">
                            {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], 'Alta', [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-12">
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::labelValidacion('type', 'Tipo de Almacén', 'negrita') !!}
                            {!! Form::select('type', ['Normal' => 'De Inventario', 'Activo Fijo' => 'Para Activos Fijos'], 'Normal', [
                                'id' => 'select-search-hide-type',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::labelValidacion('sucursal', 'Sucursal a la que pertenece', 'negrita') !!}
                            {!! Form::select('sucursal', $select_sucursales, null, [
                                'id' => 'select-search-hide-sucursal',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12 mt20 display-center">
                        {!! Form::submit('Crear almacén', ['class' => 'btn btn-success enviar']) !!}
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
            const $select = jQuery('#select-search-hide-dg, #select-search-hide-type, #select-search-hide-sucursal')
                .select2({
                    minimumResultsForSearch: -1
                });

            jQuery('#basicForm').validate({
                rules: {
                    keyAlmacen: {
                        required: true,
                        maxlength: 10,
                    },
                    nameAlmacen: {
                        required: true,
                        maxlength: 100,
                    },
                    type: {
                        required: true,
                    },
                    sucursal: {
                        required: true,
                    }
                },
                messages: {
                    keyAlmacen: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    nameAlmacen: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    type: {
                        required: "Este campo es requerido",
                    },
                    sucursal: {
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
        });
    </script>
@endsection
