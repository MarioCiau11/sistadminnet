@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="contentpanel">
        <div class="row row-stat">
            <div class="contenedor-formulario">
                {!! Form::open(['route' => 'configuracion.unidades.store', 'id' => 'basicForm']) !!}

                {!! Form::macro('labelValidacion', function ($name, $labelName, $classes){
                    return "<label for= '".$name."' class= '".$classes."'>".$labelName."<span class='asterisk'> *</span> </label>";
                }) !!}
                {!! Form::macro('labelNOValidacion', function ($name, $labelName, $classes){
                    return "<label for= '".$name."' class= '".$classes."'>".$labelName."</label>";
                }) !!}
                <h2 class="text-black">Datos generales</h2>
                <div class="col-md-4">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('nameUnidadEmpaque', 'Unidad Empaque', 'negrita') !!}
                        {!! Form::text('nameUnidadEmpaque', $unidadEmp_array['packaging_units_packaging'],['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mt10" >
                        {!! Form::labelNOValidacion('namePeso', 'Peso', 'negrita') !!}
                        {!! Form::text('namePeso', $unidadEmp_array['packaging_units_weight'],['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group mt10">
                        {!! Form::labelNOValidacion("nameUnidad", 'Unidad', 'negrita') !!}
                        {!! Form::select('nameUnidad', $unidad_unidad_array, $unidadEmp_array['packaging_units_unit'], array('id' => 'select-search-hide-sucursal', 'class' => 'widthAll select-status select-control', 'placeholder' => 'Seleccione uno...', 'disabled' )) !!}
                    </div><!-- form-group -->
                </div>

                <div class="col-md-6">
                    <div class="form-group mt10">
                        {!! Form::labelNOValidacion("createat", 'Fecha de Creación', 'negrita') !!}
                        {!! Form::text('createat', $unidadEmp_array['created_at'], ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mt10">
                        {!! Form::labelNOValidacion("updateat", 'Fecha de Actualización', 'negrita') !!}
                        {!! Form::text('updateat', $unidadEmp_array['updated_at'], ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-1">
                    <div class="form-group mt10">
                        {!! Form::label('statusDG', 'Estatus', array('class' => 'negrita')) !!}
                        {!! Form::select('statusDG', ['1' => 'Alta', '0' => 'Baja'], $unidadEmp_array['packaging_units_status'], array('id' => 'select-search-hide-dg', "class" => 'widthAll select-status', 'disabled')) !!} 
                    </div>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function (){
        jQuery('#select-search-hide-dg, #select-search-hide-nameUnidad, #select-search-hide-sucursal, #select-search-hide-nameUnidadEmpaque, #select-search-hide-namePeso, #select-search-hide-status').select2({
                    minimumResultsForSearch: -1
        });

        jQuery("#select-basic-empresa").select2();

        jQuery('#basicForm').validate({
            rules:{
                nameUnidad:{
                    required: true,
                    maxlength: 10,
                },
                nameUnidadEmpaque:{
                    required: true,
                    maxlength: 100,
                },
        
            },
            messages:{
                nameUnidad:{
                    required: "Este campo es requerido",
                    maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                },
                nameUnidadEmpaque: {
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
