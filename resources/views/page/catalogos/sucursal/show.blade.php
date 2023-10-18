@extends('layouts.layout')
@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => ['catalogo.sucursal.update', Crypt::encrypt($sucursal->sucursales_id)],
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

                    <h2 class="text-black">Datos Generales del Proveedor</h2>
                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('keySucursal', 'Clave', 'negrita') !!}
                            {!! Form::text('keySucursal', $sucursal->branchOffices_key, ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameSucursal', 'Nombre', 'negrita') !!}
                            {!! Form::text('nameSucursal', $sucursal->branchOffices_name, ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group mt10">
                            {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $sucursal->branchOffices_status, [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                                'disabled',
                            ]) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-12">
                    </div>

                    <h2 class="text-black">Información</h2>
                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('address', 'Dirección', 'negrita') !!}
                            {!! Form::text('address', $sucursal->branchOffices_addres, ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('country', 'País', 'negrita') !!}
                            {!! Form::select('country', $show_pais_array, $sucursal->branchOffices_country, [
                                'id' => 'select-basic-country',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('state', 'Estado', 'negrita') !!}
                            {!! Form::select('state', $show_estado_array, $sucursal->branchOffices_state, [
                                'id' => 'select-basic-state',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::labelValidacion('city', 'Ciudad', 'negrita') !!}
                            {!! Form::select('city', $show_ciudad_array, $sucursal->branchOffices_city, [
                                'id' => 'select-basic-city',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::labelValidacion('cp', 'Código Postal', 'negrita') !!}
                            {!! Form::text('address', $sucursal->branchOffices_cp, ['class' => 'form-control', 'disabled']) !!}
                        </div><!-- form-group -->
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::labelValidacion('suburb', 'Colonia', 'negrita') !!}
                            {!! Form::text('address', explode('-', $sucursal->branchOffices_suburb)[0], [
                                'class' => 'form-control',
                                'disabled',
                            ]) !!}
                        </div><!-- form-group -->
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
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('selectCuenta', 'Cuenta Concentradora', ['class' => 'negrita']) !!}
                            {!! Form::select('selectCuenta', $show_cuentas_array, $sucursal->branchOffices_concentrationAccount, [
                                'id' => 'select-basic-account',
                                'class' => 'widthAll select-status select-control',
                                'disabled',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::label('createat', 'Fecha de creación', ['class' => 'negrita']) !!}
                            {!! Form::text('createat', $sucursal->created_at, ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::label('updateat', 'Fecha de actualización', ['class' => 'negrita']) !!}
                            {!! Form::text('updateat', $sucursal->updated_at, ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>


                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function() {

            $("#cpBusqueda").autocomplete({
                minLength: 3,
                source: function(request, response) {
                    $.ajax({
                        url: "/cp/busqueda",
                        type: "GET",
                        data: {
                            cpBusqueda: jQuery('#cpBusqueda').val()
                        },
                        success: function({
                            cp
                        }) {
                            response(cp)
                        }
                    })
                }
            })

            $("#coloniaBusqueda").autocomplete({
                minLength: 5,
                source: function(request, response) {
                    $.ajax({
                        url: "/colonia/busqueda",
                        type: "GET",
                        data: {
                            coloniaBusqueda: jQuery('#coloniaBusqueda').val()
                        },
                        success: function({
                            colonia
                        }) {
                            response(colonia);
                        }
                    })
                }
            })

            jQuery('#select-search-hide-dg').select2({
                minimumResultsForSearch: -1
            });

            jQuery(
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
                    cp: {
                        required: true,
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
                    cp: {
                        required: "Este campo es requerido"
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

            jQuery('#regreso').click(function() {
                window.location.href = "{{ route('configuracion.monedas.index') }}";
            });
        });
    </script>
@endsection
