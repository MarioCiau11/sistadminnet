@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="contentpanel">
        <div class="row row-stat">
            <div class="contenedor-formulario">
                {!! Form::open(['route' => ['configuracion.unidades.show', Crypt::encrypt($unidad->units_id)], 'id' => 'basicForm']) !!}

                {!! Form::macro('labelValidacion', function ($name, $labelName, $classes){
                    return "<label for= '".$name."' class= '".$classes."'>".$labelName."<span class='asterisk'> *</span> </label>";
                }) !!}
                {!! Form::macro('labelNOValidacion', function ($name, $labelName, $classes){
                    return "<label for= '".$name."' class= '".$classes."'>".$labelName."</label>";
                }) !!}
                <h2 class="text-black">Datos Generales de la Unidad de Medida</h2>
                <div class="col-md-4">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('nameUnidad', 'Unidad de Medida', 'negrita') !!}
                        {!! Form::text('nameUnidad', $unidad['units_unit'],['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('numDecimalValida', 'Decimal Válida', 'negrita') !!}
                        {!! Form::text('numDecimalValida', $unidad['units_decimalVal'] ,['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group mt10">
                        {!! Form::labelValidacion("nameclaveSAT", 'Clave SAT', 'negrita') !!}
                        {!! Form::select('nameclaveSAT', $show_unidad_array, $unidad['units_keySat'], array('id' => 'select-search-hide-sucursal', 'class' => 'widthAll select-status select-control', 'placeholder' => 'Seleccione uno...', 'disabled' )) !!}
                    </div><!-- form-group -->
                </div>

                <div class="col-md-6">
                    <div class="form-group mt10">
                        {!! Form::labelNOValidacion("createat", 'Fecha de Creación', 'negrita') !!}
                        {!! Form::text('createat', $unidad['created_at'], ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mt10">
                        {!! Form::labelNOValidacion("updateat", 'Fecha de Actualización', 'negrita') !!}
                        {!! Form::text('updateat', $unidad['updated_at'], ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-1">
                    <div class="form-group mt10">
                        {!! Form::label('statusDG', 'Estatus', array('class' => 'negrita')) !!}
                        {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $unidad['units_status'], array('id' => 'select-search-hide-dg', "class" => 'widthAll select-status', 'disabled')) !!} 
                    </div>
                </div>
                

            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function (){
        jQuery('#select-search-hide-dg, #select-search-hide-nameUnidad, #select-search-hide-numDecimalValida, #select-search-hide-nameclaveSAT, #select-search-hide-status, #select-search-hide-sucursal').select2({
                    minimumResultsForSearch: -1
        });

        jQuery("#select-basic-empresa").select2();

        jQuery('#basicForm').validate({
            rules:{
                nameUnidad:{
                    required: true,
                    maxlength: 50,
                },
                numDecimalValida:{
                    required: true,
                    maxlength: 100,
                },
                nameclaveSAT:{
                    required: true,
                }
            },
            messages:{
                nameUnidad:{
                    required: "Este campo es requerido",
                    maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                },
                numDecimalValida: {
                    required: "Este campo es requerido",
                    maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                },
                nameclaveSAT:{
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
    });
</script>
@endsection
