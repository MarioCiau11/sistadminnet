@extends('layouts.layout')
@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => ['catalogo.sucursal.update', Crypt::encrypt($sucursal->branchOffices_id)],
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
                            <h2 class="text-black">Datos Generales de la Sucursal</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('keySucursal', 'Clave', 'negrita') !!}
                            {!! Form::text('keySucursal', $sucursal->branchOffices_key, ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameSucursal', 'Nombre', 'negrita') !!}
                            {!! Form::text('nameSucursal', $sucursal->branchOffices_name, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group mt10">
                            {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $sucursal->branchOffices_status, [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-12">
                    </div>

                    <h2 class="text-black">Información</h2>
                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('address', 'Dirección', 'negrita') !!}
                            {!! Form::text('address', $sucursal->branchOffices_addres, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>

                    <div class="col-md-12">
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('country', 'País', 'negrita') !!}
                            {!! Form::select('country', $edit_pais_array, $sucursal->branchOffices_country, [
                                'id' => 'select-basic-country',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('state', 'Estado', 'negrita') !!}
                            {!! Form::select('state', $edit_estado_array, $sucursal->branchOffices_state, [
                                'id' => 'select-basic-state',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('city', 'Ciudad', 'negrita') !!}
                            {!! Form::select('city', $edit_ciudad_array, $sucursal->branchOffices_city, [
                                'id' => 'select-basic-city',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-12">
                    </div>

                    <div class="col-md-6">
                        <div class="form-group ">
                            {!! Form::labelValidacion('cpBusqueda', 'Código Postal', 'negrita') !!}
                            <input type="text" name="cpBusqueda" id="cpBusqueda" tabindex="-1"
                                value="{{ $sucursal['branchOffices_cp'] }}" />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group ">
                            {!! Form::labelValidacion('coloniaBusqueda', 'Colonia', 'negrita') !!}
                            <input type="text" name="coloniaBusqueda" id="coloniaBusqueda" tabindex="-1"
                                value="{{ $sucursal['branchOffices_suburb'] }}" />
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>


                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::labelValidacion('empresa', 'Empresa', 'negrita') !!}
                            {!! Form::select('empresa', $empresas, $sucursal->branchOffices_companyId, [
                                'id' => 'select-basic-empresa',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('selectCuenta', 'Cuenta Concentradora', ['class' => 'negrita']) !!}
                            {!! Form::select('selectCuenta', $edit_cuentas_array, $sucursal['branchOffices_concentrationAccount'], [
                                'id' => 'select-basic-account',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12 mt50 display-flex text-center">
                        {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                        {!! Form::submit('Guardar Sucursal', ['class' => 'btn btn-warning enviar']) !!}
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

            $("#cpBusqueda").select2({
                placeholder: "Seleccione un codigo postal",
                destroy: true,
                allowClear: true,
                minimumInputLength: 3,
                ajax: {
                    url: "/cp/busqueda/",
                    dataType: 'json',
                    data: function(params) {
                        const queryParameters = {
                            search: params,
                            estado: $('#select-basic-state').val(),
                        }
                        return queryParameters;
                    },
                    results: function(data) {
                        return {
                            results: $.map(data, function(item, key) {
                                return {
                                    text: item.c_CodigoPostal,
                                    id: item.c_CodigoPostal
                                }
                            })
                        };
                    },
                },
            });
            $("#select2-chosen-1").text('{{ $sucursal['branchOffices_cp'] }}');


            //Buscamos las colonias de acuerdo a la ciudad
            $("#coloniaBusqueda").select2({
                placeholder: "Seleccione una colonia",
                destroy: true,
                allowClear: true,
                minimumInputLength: 3,
                ajax: {
                    url: "/colonia/busqueda/",
                    dataType: 'json',
                    data: function(params) {
                        const queryParameters = {
                            search: params,
                            cp: $("#cpBusqueda").val(),
                        }
                        return queryParameters;
                    },
                    results: function(data) {
                        return {
                            results: $.map(data, function(item, key) {
                                return {
                                    text: item.asentamiento,
                                    id: item.asentamiento + '-' + item.c_Colonia + '-' + item
                                        .c_CodigoPostal
                                }
                            })
                        };
                    },

                },
            });
            $("#select2-chosen-2").text('{{ explode('-', $sucursal['branchOffices_suburb'])[0] }}');

            jQuery('#select-search-hide-dg').select2({
                minimumResultsForSearch: -1
            });

            const $select = jQuery(
                    "#select-basic-country, #select-basic-state, #select-basic-city, #select-basic-suburb, #select-basic-cp, #select-basic-taxRegime, #select-basic-empresa, #select-basic-account"
                )
                .select2();

            // Basic Form
            jQuery("#basicForm").validate({
                rules: {
                    keySucursal: {
                        required: true,
                        maxlength: 10,
                    },
                    nameSucursal: {
                        required: true,
                        maxlength: 100,
                    },
                    address: {
                        required: true,
                        maxlength: 100,
                    },
                    suburb: {
                        required: true,
                    },
                    country: {
                        required: true,
                    },
                    state: {
                        required: true,
                    },
                    city: {
                        required: true,
                    },
                    cpBusqueda: {
                        required: true,
                        maxlength: 5,
                        minlength: 5,
                    },
                    empresa: {
                        required: true,
                    }
                },
                messages: {
                    keySucursal: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    nameSucursal: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    address: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                    },
                    suburb: {
                        required: "Este campo es requerido",
                    },
                    country: {
                        required: "Este campo es requerido"
                    },
                    state: {
                        required: "Este campo es requerido"
                    },
                    city: {
                        required: "Este campo es requerido"
                    },
                    cpBusqueda: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                        minlength: jQuery.validator.format('Minimo de {0} caracteres'),
                        min: jQuery.validator.format('Por favor ingresa un valor mayor o igual a {0}'),
                        max: jQuery.validator.format('Por favor ingresa un valor menor o igual a {0}'),
                    },
                    empresa: {
                        required: "Este campo es requerido"
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

            jQuery('#regreso').click(function() {
                window.location.href = "{{ route('catalogo.sucursal.index') }}";
            });
        });
    </script>
@endsection
