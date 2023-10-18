@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="contentpanel">
        <div class="row row-stat">
            <div class="contenedor-formulario">
                {!! Form::open(['route' => 'catalogo.instituciones-financieras.store', 'id' => 'basicForm']) !!}

                {!! Form::macro('labelValidacion', function ($name, $labelName, $classes){
                    return "<label for= '".$name."' class= '".$classes."'>".$labelName."<span class='asterisk'> *</span> </label>";
                }) !!}

                <h2 class="text-black">Datos generales</h2>
                <div class="col-md-6">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('keyInstitucionFinanciera', 'Clave', 'negrita') !!}
                        {!! Form::text('keyInstitucionFinanciera',$instFinancial->instFinancial_key,['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                 <div class="col-md-6">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('nameInstitucionFinanciera', 'Nombre', 'negrita') !!}
                        {!! Form::text('nameInstitucionFinanciera', $instFinancial->instFinancial_name,['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-12"></div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::labelValidacion('pais', 'País', 'negrita') !!}
                         {!! Form::select('pais', $show_pais_array , $instFinancial->instFinancial_country, array('id' => 'select-basic-country', 'class' => 'widthAll select-status', 'placeholder' => 'Seleccione uno...', 'disabled')) !!}
                    </div>
                </div>

                   <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::labelValidacion('estado', 'Estado', 'negrita') !!}
                         {!! Form::select('estado', $show_estado_array, $instFinancial->instFinancial_state, array('id' => 'select-basic-estado', 'class' => 'widthAll select-status', 'placeholder' => 'Seleccione uno...', 'disabled')) !!}
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::labelValidacion('ciudad', 'Ciudad', 'negrita') !!}
                         {!! Form::select('ciudad', $show_ciudad_array, $instFinancial->instFinancial_city, array('id' => 'select-basic-ciudad', 'class' => 'widthAll select-status', 'placeholder' => 'Seleccione uno...', 'disabled' )) !!}
                    </div>
                </div>
          
                
                <div class="col-md-12"></div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('status', 'Estatus', array('class' => 'negrita')) !!}
                        {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja'], $instFinancial->instFinancial_status, array('id' => 'select-search-hide-status', "class" => 'widthAll select-status', 'disabled')) !!} 
                    </div>
                </div>


                <div class="col-md-5">
                    <div class="form-group">
                        {!! Form::label('created_at', 'Fecha de creación', array('class' => 'negrita')) !!}
                        {!! Form::text('createat', $instFinancial->created_at, ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="form-group">
                        {!! Form::label('status', 'Fecha de actualización', array('class' => 'negrita')) !!}
                        {!! Form::text('updateat', $instFinancial->updated_at, ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>

                
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function (){
        jQuery('#select-search-hide-status').select2({
                    minimumResultsForSearch: -1
        });

        jQuery("#select-basic-country, #select-basic-estado, #select-basic-ciudad").select2();

        jQuery('#basicForm').validate({
            rules:{
                keyInstitucionFinanciera:{
                    required: true,
                    maxlength: 20,
                },

                nameInstitucionFinanciera:{
                    required: true,
                    maxlength: 50,
                },
                pais:{
                    required: true,
                },
                estado:{
                    required: true,
                },
                ciudad:{
                    required: true,
                },
               
            },
            messages:{
                keyInstitucionFinanciera:{
                    required: "Este campo es requerido",
                    maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                },
                nameInstitucionFinanciera:{
                    required: "Este campo es requerido",
                    maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                },
                pais:{
                    required: "Este campo es requerido",
                },
                estado:{
                    required: "Este campo es requerido",
                },
                ciudad:{
                    required: "Este campo es requerido",
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
