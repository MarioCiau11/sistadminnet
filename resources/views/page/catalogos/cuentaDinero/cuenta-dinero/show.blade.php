@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="contentpanel">
        <div class="row row-stat">
            <div class="contenedor-formulario">
                {!! Form::open(['route' => 'catalogo.cuenta-dinero.store', 'id' => 'basicForm']) !!}

                {!! Form::macro('labelValidacion', function ($name, $labelName, $classes){
                    return "<label for= '".$name."' class= '".$classes."'>".$labelName."<span class='asterisk'> *</span> </label>";
                }) !!}

                <h2 class="text-black">Datos Generales de la Cuenta de Banco/Efectivo</h2>
                <div class="col-md-4">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('keyClaveBanco', 'Clave', 'negrita') !!}
                        {!! Form::text('keyClaveBanco',$moneyAccounts->moneyAccounts_key,['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="form-group mt10">
                        {!! Form::labelValidacion("nameBanco", 'Banco', 'negrita') !!}
                        {!! Form::select('nameBanco', $selectInstFinancial, $moneyAccounts->moneyAccounts_bank, array('id' => 'select-search-hide-nameBanco', "class" => 'widthAll select-status', 'placeholder' => "Seleccione uno...", 'disabled')) !!} 
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group mt10">
                        {!! Form::labelValidacion('tipoCuenta', 'Tipo de cuenta', 'negrita') !!}
                        {!! Form::select('tipoCuenta', ['Caja' => 'Caja/Efectivos', 'Banco' => 'Banco'], $moneyAccounts->moneyAccounts_accountType, array('id' => 'select-search-hide-tipoCuenta', "class" => 'widthAll select-status', 'placeholder' => "Seleccione uno...", 'disabled')) !!} 
                    </div><!-- form-group -->
                </div>

                <div class="col-md-12">
                </div>

                
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::labelValidacion("numeroCuenta", 'Número de cuenta', 'negrita') !!}
                        {!! Form::text('numeroCuenta',$moneyAccounts->moneyAccounts_numberAccount,['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                 <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::labelValidacion("cuentaCLABE", 'CLABE', 'negrita') !!}
                        {!! Form::text('cuentaCLABE',$moneyAccounts->moneyAccounts_keyAccount,['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-12"></div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::labelValidacion("empresa", 'Empresa', 'negrita') !!}
                         {!! Form::select('empresa', $selectEmpresas, $moneyAccounts->moneyAccounts_company, array('id' => 'select-basic-empresa', 'class' => 'widthAll select-status', 'placeholder' => 'Seleccione uno...', 'disabled' )) !!}
                    </div>
                </div>

                
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::labelValidacion("convenioBanco", 'Convenio banco', 'negrita') !!}
                        {!! Form::text('convenioBanco',$moneyAccounts->moneyAccounts_bankAgreement,['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                  <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::labelValidacion("moneda", 'Moneda', 'negrita') !!}
                        {!! Form::select('moneda', $selectMonedas, $moneyAccounts->moneyAccounts_money, array('id' => 'select-search-hide-moneda', "class" => 'widthAll select-status', 'placeholder' => "Seleccione uno...", 'disabled')) !!} 
                    </div>
                </div>

                  <div class="col-md-12"></div>

                  <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::labelValidacion("rBanco", 'Referencia banco', 'negrita') !!}
                        {!! Form::text('rBanco',$moneyAccounts->moneyAccounts_referenceBank,['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                         {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                         {!! Form::select('status', ["Alta" => "Alta", "Baja" => "Baja"], $moneyAccounts->moneyAccounts_status, array('id' => 'select-search-hide-status', 'class' => 'widthAll select-status', 'disabled')) !!}
                    </div>
                </div>


                 <div class="col-md-6">
                    <div class="form-group mt10">
                         {!! Form::label('createat', 'Fecha de creación', array('class' => 'negrita')) !!}
                        {!! Form::text('createat', $moneyAccounts->created_at, ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mt10">
                         {!! Form::label('updateat', 'Fecha de actualización', array('class' => 'negrita')) !!}
                        {!! Form::text('updateat', $moneyAccounts->updated_at, ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>
                
                
                
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function (){
        jQuery('#select-search-hide-dg, #select-search-hide-tipoCuenta,  #select-search-hide-status').select2({
                    minimumResultsForSearch: -1
        });

        jQuery("#select-basic-empresa, #select-search-hide-moneda, #select-search-hide-nameBanco").select2();

        jQuery('#basicForm').validate({
            rules:{
                keyClaveBanco:{
                    required: true,
                    maxlength: 10,
                },
                nameBanco:{
                    required: true,
                    maxlength: 100,
                },
                tipoCuenta:{
                    required: true,
                },
                numeroCuenta:{
                    required: true,
                    maxlength: 50,
                },
                cuentaCLABE:{
                    required: true,
                    maxlength: 50,
                },
                empresa:{
                    required: true,
                },
                moneda:{
                    required: true,
                },
                convenioBanco:{
                    required: true,
                    maxlength: 50,
                },
                 rBanco:{
                    required: true,
                    maxlength: 50,
                },
            },
            messages:{
                keyClaveBanco:{
                    required: "Este campo es requerido",
                    maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                },
                nameBanco: {
                    required: "Este campo es requerido",
                    maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                },
                tipoCuenta:{
                    required: "Este campo es requerido",
                },
                numeroCuenta: {
                    required: "Este campo es requerido",
                    maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                },
                cuentaCLABE:{
                    required: "Este campo es requerido",
                    maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                },
                empresa:{
                    required: "Este campo es requerido",
                },
                moneda:{
                    required: "Este campo es requerido",
                },
                convenioBanco:{
                    required: "Este campo es requerido",
                    maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                },
                 rBanco:{
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