@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => ['configuracion.usuarios.update', Crypt::encrypt($user_edit['user_id'])],
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
                            <h2 class="text-black">Datos Generales del Usuario</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nombre', 'Nombre', 'negrita') !!}
                            {!! Form::text('nombre', $user_edit['user_name'], ['class' => 'form-control', 'id' => 'nombreInput']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('user', 'Usuario', 'negrita') !!}
                            {!! Form::text('user', $user_edit['username'], ['class' => 'form-control', 'id' => 'userInput']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::label('email', 'Correo Electrónico', ['class' => 'negrita']) !!}
                            {!! Form::text('email', $user_edit['user_email'], ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::label('changePassword', 'Cambiar contraseña', ['class' => 'negrita']) !!}
                            {!! Form::checkbox('changePassword', 'changePassword', [], ['id' => 'changePass']) !!}
                        </div>
                    </div>

                    <div class="change-password">
                        <div class="col-md-12">
                            <div class="form-group mt10">
                                {!! Form::labelValidacion('pass', 'Contraseña', 'negrita') !!}
                                <input id="pass" type="password" class="form-control" name="pass" />

                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group
                            mt10">
                                <input type="checkbox" id="mostrarPass" onclick="mostrarContrasena()" />
                                <label for="mostrarPass"><strong>Mostrar contraseña</strong></label>
                            </div>
                        </div>

                        
                        <div class="col-md-12">
                            <div class="form-group mt10">
                                {!! Form::labelValidacion('conpass', 'Confirmar Contraseña', 'negrita') !!}
                                <input id="conpass" type="password" class="form-control" name="conpass" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="form-group
                        mt10">
                            <input type="checkbox" id="mostrarConPass" onclick="mostrarContrasena2()" />
                            <label for="mostrarConPass"><strong>Mostrar contraseña</strong></label>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group mt10">
                            {!! Form::label('status', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja'], $user_edit['user_status'], [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>

                    {{ $user_edit['identificador'] }}
                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::label('rol', 'Rol', ['class' => 'negrita']) !!}
                            {!! Form::select('rol', $select_roles, $user_edit['user_rol'], [
                                'id' => 'select-search-hide-rol',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('empresas[]', 'Empresas', 'negrita') !!}
                            {!! Form::select('empresas[]', $edit_empresas_array, $empresasRelacionadasUsuario, [
                                'id' => 'select-multi',
                                'class' => 'widthAll',
                                'multiple',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('sucursales[]', 'Sucursales', 'negrita') !!}
                            {!! Form::select('sucursales[]', $edit_sucursales_array, $sucursalesRelacionadasUsuario, [
                                'id' => 'select-multi2',
                                'class' => 'widthAll',
                                'multiple',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('selectCliente', 'Clientes', ['class' => 'negrita']) !!}
                            {!! Form::select('selectCliente', $edit_clientes_array, $user_edit['user_defaultCustomer'], [
                                'id' => 'select-basic-customer',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('selectAlmacen', 'Almacén', ['class' => 'negrita']) !!}
                            {!! Form::select('selectAlmacen', $edit_almacenes_array, $user_edit['user_defaultDepot'], [
                                'id' => 'select-basic-depot',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('selectAgente', 'Agente', ['class' => 'negrita']) !!}
                            {!! Form::select('selectAgente', $edit_agentes_array, $user_edit['user_defaultAgent'], [
                                'id' => 'select-basic-agent',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('selectCuenta', 'Cuenta Concentradora', ['class' => 'negrita']) !!}
                            {!! Form::select('selectCuenta', $edit_cuentas_array, $user_edit['user_concentrationAccount'], [
                                'id' => 'select-basic-account',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('selectCaja', 'Cuenta de Caja Principal', ['class' => 'negrita']) !!}
                            {!! Form::select('selectCaja', $edit_cuentaCaja_array, $user_edit['user_mainAccount'], [
                                'id' => 'select-basic-caja',
                                'class' => 'widthAll select-status select-control',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::label('bloq', 'Bloquear Precios en Ventas', ['class' => 'negrita']) !!}
                            {!! Form::checkbox('bloq', '0', $user_edit['user_block_sale_prices']) !!}
                        </div>
                        <div class="form-group mt10">
                            {!! Form::label('bloqCosto', 'Bloquear Costo Unitario en Compras', ['class' => 'negrita']) !!}
                            {!! Form::checkbox('bloqCosto', '0', $user_edit['user_blockPurchaseCost']) !!}
                        </div>
                        <div class="form-group mt10">
                            {!! Form::label('verCosto', 'Ver Costos en Compras', ['class' => 'negrita']) !!}
                            {!! Form::checkbox('verCosto', '0', $user_edit['user_viewPurchaseCost']) !!}
                        </div>
                        <div class="form-group mt10">
                            {!! Form::label('verCostoInfo', 'Ver Costos en Información del Artículo', ['class' => 'negrita']) !!}
                            {!! Form::checkbox('verCostoInfo', '0', $user_edit['user_viewArticleInformationCost']) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mt10" style="display: flex; flex-direction: column">
                            {!! Form::label('Dashboard', 'Dashboard:', ['class' => 'negrita']) !!}
                            <div style="display: flex; flex-direction: column">
                                <div>
                                    {!! Form::label('Top 10 Productos más Vendidos', 'Top 10 Productos más Vendidos', ['class' => 'negrita']) !!}
                                    {!! Form::checkbox(
                                        'Top 10 Productos más Vendidos',
                                        'Top_10_Productos_más_Vendidos',
                                        $user_edit->user_getTop10SalesArticles != 0 ? true : false,
                                    ) !!}
                                </div>
                                <div>
                                    {!! Form::label('Ventas Netas por Familia', 'Ventas Netas por Familia', ['class' => 'negrita']) !!}
                                    {!! Form::checkbox(
                                        'Ventas Netas por Familia',
                                        'Ventas_Netas_por_Familia',
                                        $user_edit->user_getSalesByFamily != 0 ? true : false,
                                    ) !!}
                                </div>
                                <div>
                                    {!! Form::label('Ventas Mes Actual VS Mes Anterior', 'Ventas Mes Actual VS Mes Anterior', ['class' => 'negrita']) !!}
                                    {!! Form::checkbox(
                                        'Ventas Mes Actual VS Mes Anterior',
                                        'Ventas_Mes_Actual_VS_Mes_Anterior',
                                        $user_edit->user_getCurrentSaleVSPreviousSale != 0 ? true : false,
                                    ) !!}
                                </div>
                                <div>
                                    {!! Form::label('Flujo y Ventas', 'Flujo y Ventas', ['class' => 'negrita']) !!}
                                    {!! Form::checkbox(
                                        'Flujo y Ventas',
                                        'Flujo_y_Ventas',
                                        $user_edit->user_getSalesAndFlows != 0 ? true : false,
                                    ) !!}
                                </div>
                                <div>
                                    {!! Form::label('Ventas VS Ganancia', 'Ventas VS Ganancia', ['class' => 'negrita']) !!}
                                    {!! Form::checkbox(
                                        'Ventas VS Ganancia',
                                        'Ventas_VS_Ganancia',
                                        $user_edit->user_calculateSalesSummary != 0 ? true : false,
                                    ) !!}
                                </div>
                                <div>
                                    {!! Form::label('Ganancia VS Gastos', 'Ganancia VS Gastos', ['class' => 'negrita']) !!}
                                    {!! Form::checkbox(
                                        'Ganancia VS Gastos',
                                        'Ganancia_VS_Gastos',
                                        $user_edit->user_getEarningAndExpenses != 0 ? true : false,
                                    ) !!}
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mt50 display-flex text-center">
                        {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                        {!! Form::submit('Guardar usuario', ['class' => 'btn btn-warning enviar']) !!}

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
            jQuery('#select-search-hide-dg, #select-search-hide-rol').select2({
                minimumResultsForSearch: -1,

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

            $select3.rules('add', {
                required: true,
                messages: {
                    required: "Este campo es requerido",
                }
            });

            $select3.on('change', function() {
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

            const changePass = jQuery('#changePass');
            const change_password = jQuery('.change-password');

            changePass.on('click', function() {
                change_password.toggle();
            });

            jQuery('#regreso').click(function() {
                window.location.href = "{{ route('configuracion.usuarios.index') }}";
            });

            //Convertimos el user y nombres en mayuscula
            $('#userInput').keyup((e) => {
                $('#userInput').val(e.target.value.toUpperCase());
            });

            $('#nombreInput').keyup((e) => {
                $('#nombreInput').val(e.target.value.toUpperCase());
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
