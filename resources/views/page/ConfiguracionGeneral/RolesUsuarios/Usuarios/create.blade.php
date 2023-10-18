@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open(['route' => 'configuracion.usuarios.store', 'id' => 'basicForm']) !!}

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
                            <h2 class="text-black">Datos Generales del Usuario</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nombre', 'Nombre', 'negrita') !!}
                            {!! Form::text('nombre', null, ['class' => 'form-control', 'id' => 'nombreInput']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('user', 'Usuario', 'negrita') !!}
                            {!! Form::text('user', null, ['class' => 'form-control', 'id' => 'userInput']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('email', 'Correo Electrónico', 'negrita') !!}
                            {!! Form::text('email', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('pass', 'Contraseña', 'negrita') !!}
                            <input id="pass" type="password" class="form-control" name="pass" />

                        </div>
                    </div>

                    {{-- Ponemos una opción para que el usuario pueda elegir si quiere que su contraseña sea visible o no --}}
                    <div class="col-md-12">
                        <div class="form-group
                        mt10">
                            <input type="checkbox" id="mostrarPass" onclick="mostrarContrasena()" />
                            <label for="mostrarPass">Mostrar contraseña</label>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('conpass', 'Confirmar Contraseña', 'negrita') !!}
                            <input id="conpass" type="password" class="form-control" name="conpass" />
                        </div>
                    </div>

                    {{-- Ponemos una opción para que el usuario pueda elegir si quiere que su contraseña sea visible o no --}}
                    <div class="col-md-12">
                        <div class="form-group
                        mt10">
                            <input type="checkbox" id="mostrarConPass" onclick="mostrarContrasena2()" />
                            <label for="mostrarConPass">Mostrar contraseña</label>
                        </div>
                    </div>


                    <div class="col-md-1">
                        <div class="form-group mt10">
                            {!! Form::label('status', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja'], '1', [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::label('rol', 'Rol', ['class' => 'negrita']) !!}
                            {!! Form::select('rol', $select_roles, null, [
                                'id' => 'select-search-hide-rol',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('empresas[]', 'Empresas', 'negrita') !!}
                            {!! Form::select('empresas[]', $create_empresas_array, null, [
                                'id' => 'select-multi',
                                'class' => 'widthAll',
                                'multiple',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('sucursales[]', 'Sucursales', 'negrita') !!}
                            {!! Form::select('sucursales[]', $create_sucursales_array, null, [
                                'id' => 'select-multi2',
                                'class' => 'widthAll',
                                'multiple',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('selectCliente', 'Clientes', ['class' => 'negrita']) !!}
                            {!! Form::select('selectCliente', $clientes_array, null, [
                                'id' => 'select-basic-customer',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('selectAlmacen', 'Almacén', ['class' => 'negrita']) !!}
                            {!! Form::select('selectAlmacen', $almacenes_array, null, [
                                'id' => 'select-basic-depot',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('selectAgente', 'Agente', ['class' => 'negrita']) !!}
                            {!! Form::select('selectAgente', $agentes_array, null, [
                                'id' => 'select-basic-agent',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('selectCuenta', 'Cuenta Concentradora', ['class' => 'negrita']) !!}
                            {!! Form::select('selectCuenta', $cuentas_array, null, [
                                'id' => 'select-basic-account',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('selectCaja', 'Cuenta de Caja Principal', ['class' => 'negrita']) !!}
                            {!! Form::select('selectCaja', $cuentaCaja_array, null, [
                                'id' => 'select-basic-caja',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            <label for="bloq" class="negrita">Bloquear Precios en Ventas</label>
                            <input type="hidden" name="bloq" value="0">
                            <input type="checkbox" name="bloq" value="1">
                        </div>
                           <div class="form-group mt10">
                            <label for="bloqCosto" class="negrita">Bloquear Costo Unitario en Compras</label>
                            <input type="hidden" name="bloqCosto" value="0">
                            <input type="checkbox" name="bloqCosto" value="1">
                        </div>
                        <div class="form-group mt10">
                            <label for="verCosto" class="negrita">Ver Costos en Compras</label>
                            <input type="hidden" name="verCosto" value="0">
                            <input type="checkbox" name="verCosto" value="1">
                        </div>
                        <div class="form-group mt10">
                            <label for="verCostoInfo" class="negrita">Ver Costos en Información del Artículo</label>
                            <input type="hidden" name="verCostoInfo" value="0">
                            <input type="checkbox" name="verCostoInfo" value="1">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10" style="display: flex; flex-direction: column">
                            {!! Form::label('Dashboard', 'Dashboard:', ['class' => 'negrita']) !!}
                            <div style="display: flex; flex-direction: column">
                                @foreach ($permisosDashboard as $permiso)
                                    <div>
                                        <label for="{{$permiso->name}}" class="negrita">{{$permiso->name}}</label>
                                        <input type="hidden" name="{{$permiso->name}}" value="0">
                                        <input type="checkbox" name="{{$permiso->name}}" value="1">
                                    </div>
                                @endforeach

                            </div>
                        </div>
                    </div>




                    <div class="col-md-12 mt20 display-center">
                        {!! Form::submit('Crear Usuario', ['class' => 'btn btn-success enviar']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <script>
        
        //ahora hacemos que cuando le de clic en el botón de guardar aparezca el loader
        // jQuery(".enviar").click(function() {
        //         //solo mostrar el loader si los campos están validados
        //         if (jQuery("#basicForm").valid()) {
        //             jQuery("#loader").show();
        //         }
        //     });

            
        jQuery(document).ready(function() {
            jQuery('#select-search-hide-dg, #select-search-hide-rol').select2({
            });
            

            $("#e2").select2({
                placeholder: "Select a State",
                allowClear: true
            });
            $("#e2_2").select2({
                placeholder: "Select a State"
            });

            const $select2 = jQuery('#select-search-hide-dg, #select-search-hide-rol').select2({
                minimumResultsForSearch: -1,
                placeholder: 'Seleccione uno...',

            });

            const $select = jQuery("#select-multi").select2({
                placeholder: 'Seleccione uno...',
                allowClear: true
            });

            const $select3 = jQuery("#select-multi2").select2({
                placeholder: 'Seleccione uno...',
                allowClear: true
            });

            const $select4 = jQuery("#select-basic-customer, #select-basic-depot, #select-basic-agent, #select-basic-account, #select-basic-caja").select2();


            //Convertimos el user y nombres en mayuscula
            $('#userInput').keyup((e) => {
                $('#userInput').val(e.target.value.toUpperCase());
            });

            $('#nombreInput').keyup((e) => {
                $('#nombreInput').val(e.target.value.toUpperCase());
            });

            jQuery('#basicForm').validate({
                rules: {
                    nombre: {
                        required: true,
                        maxlength: 100,
                    },
                    user: {
                        required: true,
                        maxlength: 10,
                    },
                    email: {
                        required: true,
                        maxlength: 100,
                    },
                    pass: {
                        required: true,
                        maxlength: 20,
                    },
                    conpass: {
                        required: true,
                        maxlength: 20,
                        equalTo: "#pass",
                    },
                    rol: {
                        required: true,
                        maxlength: 20,
                    },
                    'empresas[]': {
                        required: true,
                        maxlength: 20,
                    },
                    'sucursales[]': {
                        required: true,
                        maxlength: 20,
                    },
                },
                messages: {
                    nombre: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    user: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    email: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    pass: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    conpass: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres'),
                        equalTo: "Las contraseñas no coinciden",
                    },
                    rol: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    'empresas[]': {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    'sucursales[]': {
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

            $select2.rules('add', {
                required: true,
                messages: {
                    required: "Este campo es requerido",
                }
            });

            $select2.on('change', function() {
                $(this).trigger('blur');
            });

            $select3.rules('add', {
                required: true,
                messages: {
                    required: "Este campo es requerido",
                }
            });
        });

        //función para mostrar la contraseña
        function mostrarContrasena() {
            var tipo = document.getElementById("pass");
            if (tipo.type == "password") {
                tipo.type = "text";
            } else {
                tipo.type = "password";
            }
        }

        //función para mostrar la contraseña
        function mostrarContrasena2() {
            var tipo = document.getElementById("conpass");
            if (tipo.type == "password") {
                tipo.type = "text";
            } else {
                tipo.type = "password";
            }
        }
    </script>
@endsection
