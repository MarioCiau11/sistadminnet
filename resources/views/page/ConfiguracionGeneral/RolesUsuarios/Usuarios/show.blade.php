@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="contentpanel">
        <div class="row row-stat">
            <div class="contenedor-formulario">
                {!! Form::open(['route' => 'configuracion.usuarios.store', 'id' => 'basicForm']) !!}

                {!! Form::macro('labelValidacion', function ($name, $labelName, $classes){
                    return "<label for= '".$name."' class= '".$classes."'>".$labelName."<span class='asterisk'> *</span> </label>";
                }) !!}

                {!! Form::macro('labelNOValidacion', function ($name, $labelName, $classes){
                    return "<label for= '".$name."' class= '".$classes."'>".$labelName."</label>";
                }) !!}

                <h2 class="text-black">Datos Generales del Usuario</h2>
                <div class="col-md-12">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('nombre', 'Nombre', 'negrita') !!}
                        {!! Form::text('nombre',$user['user_name'],['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('user', 'Usuario', 'negrita') !!}
                        {!! Form::text('user',$user['username'],['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group mt10" >
                        {!! Form::labelNOValidacion('email', 'Correo Electrónico', 'negrita') !!}
                        {!! Form::text('email',$user['user_email'],['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('pass', 'Contraseña (Cifrada para su protección)', 'negrita') !!}
                        {!! Form::text('pass',$user['password'],['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

    

                <div class="col-md-1">
                    <div class="form-group mt10">
                        {!! Form::label('statusDG', 'Estatus', array('class' => 'negrita')) !!}
                        {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'],$user['user_status'], array('id' => 'select-search-hide-dg', "class" => 'widthAll select-status', 'disabled')) !!} 
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group mt10">
                        {!! Form::label('rol', 'Rol', array('class' => 'negrita')) !!}
                        {!! Form::select('rol', $select_roles ,$user['user_rol'] , array('id' => 'select-search-hide-rol', "class" => 'widthAll select-status', 'placeholder' => 'Seleccionar un rol', 'disabled' )) !!} 
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group mt10">
                        {!! Form::labelValidacion('empresas[]', 'Empresas', 'negrita') !!}
                        {!! Form::select('empresas[]', $show_empresas_array, $empresasRelacionadasUsuario, array('id' => 'select-multi', "class" => 'widthAll','multiple', 'disabled' )) !!} 
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group mt10">
                        {!! Form::labelValidacion('sucursales[]', 'Sucursales', 'negrita') !!}
                        {!! Form::select('sucursales[]', $show_sucursales_array, $sucursalesRelacionadasUsuario, array('id' => 'select-multi2', "class" => 'widthAll','multiple', 'disabled' )) !!} 
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('selectCliente', 'Clientes', ['class' => 'negrita']) !!}
                        {!! Form::select('selectCliente', $show_clientes_array, ($user['user_defaultCustomer'] ?? null), [
                            'id' => 'select-basic-customer',
                            'class' => 'widthAll select-status select-control',
                            'disabled',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('selectAlmacen', 'Almacén', ['class' => 'negrita']) !!}
                        {!! Form::select('selectAlmacen', $show_almacenes_array, $user['user_defaultDepot'], [
                            'id' => 'select-basic-depot',
                            'class' => 'widthAll select-status select-control',
                            'disabled',
                            'placeholder' => 'Seleccione uno...',
                        ]) !!}
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('selectAgente', 'Agente', ['class' => 'negrita']) !!}
                        {!! Form::select('selectAgente', $show_agentes_array, $user['user_defaultAgent'], [
                            'id' => 'select-basic-agent',
                            'class' => 'widthAll select-status select-control',
                            'disabled',
                            'placeholder' => 'Seleccione uno...',

                        ]) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('selectCuenta', 'Cuenta Concentradora', ['class' => 'negrita']) !!}
                        {!! Form::select('selectCuenta', $show_cuentas_array, $user['user_concentrationAccount'], [
                            'id' => 'select-basic-account',
                            'class' => 'widthAll select-status select-control',
                            'disabled',
                            'placeholder' => 'Seleccione uno...',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('selectCaja', 'Cuenta de Caja Principal', ['class' => 'negrita']) !!}
                        {!! Form::select('selectCaja', $show_cuentaCaja_array, $user['user_mainAccount'], [
                            'id' => 'select-basic-caja',
                            'class' => 'widthAll select-status select-control',
                            'disabled',
                            'placeholder' => 'Seleccione uno...',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mt10">
                        <div class="ckbox ckbox-primary">
                            {!! Form::checkbox('bloq', '1', $user['user_block_sale_prices'], ['id' => 'bloq', 'disabled']) !!} 
                            {!! Form::label('bloq', 'Bloquear Precios en Ventas', array('class' => 'negrita')) !!}
                        </div>
                    </div>
                    <div class="form-group mt10">
                        <div class="ckbox ckbox-primary">
                            {!! Form::checkbox('bloqCosto', '1', $user['user_blockPurchaseCost'], ['id' => 'bloqCosto', 'disabled']) !!}
                            {!! Form::label('bloqCosto', 'Bloquear Costo Unitario en Compras', array('class' => 'negrita')) !!}
                        </div> 
                    </div>
                     <div class="form-group mt10">
                        <div class="ckbox ckbox-primary">
                            {!! Form::checkbox('verCosto', '1', $user['user_viewPurchaseCost'], ['id' => 'verCosto', 'disabled']) !!}
                            {!! Form::label('verCosto', 'Ver Costos en Compras', array('class' => 'negrita')) !!}
                        </div>
                    </div>
                      <div class="form-group mt10">
                        <div class="ckbox ckbox-primary">
                            {!! Form::checkbox('verCostoInfo', '1', $user['user_viewArticleInformationCost'], ['id' => 'verCostoInfo', 'disabled']) !!}
                            {!! Form::label('verCostoInfo', 'Ver Costos en Información del Artículo', array('class' => 'negrita')) !!}
                        </div>
                    </div>
                </div>


                <div class="col-md-6">
                    <div class="form-group mt10" style="display: flex; flex-direction: column">
                        {!! Form::label('Dashboard', 'Dashboard:', ['class' => 'negrita']) !!}
                        <div style="display: flex; flex-direction: column">
                            <div class="ckbox ckbox-primary">
                                {!! Form::checkbox(
                                    'Top 10 Productos más Vendidos',
                                    'Top_10_Productos_más_Vendidos',
                                    $user->user_getTop10SalesArticles != 0 ? true : false,
                                    ['disabled'],
                                ) !!}
                                {!! Form::label('Top 10 Productos más Vendidos', 'Top 10 Productos más Vendidos', ['class' => 'negrita']) !!}
                            </div>
                            <div class="ckbox ckbox-primary">
                                {!! Form::checkbox(
                                    'Ventas Netas por Familia',
                                    'Ventas_Netas_por_Familia',
                                    $user->user_getNetSalesByFamily != 0 ? true : false,
                                    ['disabled'],
                                ) !!}
                                {!! Form::label('Ventas Netas por Familia', 'Ventas Netas por Familia', ['class' => 'negrita']) !!}
                            </div>
                            <div class="ckbox ckbox-primary">
                                {!! Form::checkbox(
                                    'Ventas Mes Actual VS Mes Anterior',
                                    'Ventas_Mes_Actual_VS_Mes_Anterior',
                                    $user->user_getCurrentMonthSalesVsPreviousMonth != 0 ? true : false,
                                    ['disabled'],
                                ) !!}
                                {!! Form::label('Ventas Mes Actual VS Mes Anterior', 'Ventas Mes Actual VS Mes Anterior', ['class' => 'negrita']) !!}
                            </div>
                            <div class="ckbox ckbox-primary">
                                {!! Form::checkbox(
                                    'Flujo y Ventas',
                                    'Flujo_y_Ventas',
                                    $user->user_getFlowAndSales != 0 ? true : false,
                                    ['disabled'],
                                ) !!}
                                {!! Form::label('Flujo y Ventas', 'Flujo y Ventas', ['class' => 'negrita']) !!}
                            </div>
                            <div class="ckbox ckbox-primary">
                                {!! Form::checkbox(
                                    'Ventas VS Ganancia',
                                    'Ventas_VS_Ganancia',
                                    $user->user_getSalesVsProfit != 0 ? true : false,
                                    ['disabled'],
                                ) !!}
                                {!! Form::label('Ventas VS Ganancia', 'Ventas VS Ganancia', ['class' => 'negrita']) !!}
                            </div>
                            <div class="ckbox ckbox-primary">
                                {!! Form::checkbox(
                                    'Ganancia VS Gastos',
                                    'Ganancia_VS_Gastos',
                                    $user->user_getProfitVsExpenses != 0 ? true : false,
                                    ['disabled'],
                                ) !!}
                                {!! Form::label('Ganancia VS Gastos', 'Ganancia VS Gastos', ['class' => 'negrita']) !!}
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mt10">
                        {!! Form::labelNOValidacion("createat", 'Fecha de Creación', 'negrita') !!}
                        {!! Form::text('createat',$user['created_at'] , ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mt10">
                        {!! Form::labelNOValidacion("updateat", 'Fecha de Actualización', 'negrita') !!}
                        {!! Form::text('updateat', $user['updated_at'], ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function (){
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

         jQuery('#select-search-hide-dg, #select-search-hide-rol').select2({
            minimumResultsForSearch: -1,
            placeholder: 'Seleccione uno...',
            
        });

        jQuery("#select-multi").select2({
            placeholder: 'Seleccione uno...',
            allowClear: true
        });

        jQuery("#select-multi2").select2({
            placeholder: 'Seleccione uno...',
            allowClear: true
        });

        const $select4 = jQuery("#select-basic-customer, #select-basic-depot, #select-basic-agent, #select-basic-account, #select-basic-caja").select2();
        jQuery('#basicForm').validate({
            rules:{
                nombre:{
                    required: true,
                    maxlength: 100,
                },
                user:{
                    required: true,
                    maxlength: 10,
                },
                pass:{
                    required: true,
                    maxlength: 20,
                },
                conpass:{
                    required: true,
                    maxlength: 20,
                    equalTo: "#pass",
                },
                rol:{
                    required: true,
                    maxlength: 20,
                },
                'empresas[]':{
                    required: true,
                    maxlength: 20,

                },
                'sucursales[]':{
                    required: true,
                    maxlength: 20,

                },
            },
            messages:{
                nombre:{
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

        })
    });
</script>
@endsection
