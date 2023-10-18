@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="contentpanel">
        <div class="row row-stat">
            <div class="contenedor-formulario">
                {!! Form::open(['route' => 'catalogo.concepto-gastos.store', 'id' => 'basicForm']) !!}

                {!! Form::macro('labelValidacion', function ($name, $labelName, $classes){
                    return "<label for= '".$name."' class= '".$classes."'>".$labelName."<span class='asterisk'> *</span> </label>";
                }) !!}

                <div class="col-md-12">
                    <div class="col-md-6">
                        <h2 class="text-black">Datos Generales de la Razón de Gasto</h2>
                    </div>
                    <div class="col-md-6">
                        <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('concepto', 'Razón/Concepto', 'negrita') !!}
                        {!! Form::text('concepto',null,['class'=>'form-control']) !!}
                    </div>
                </div>

                <div class="col-md-12">
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('impuesto', '% Impuestos', array('class' => 'negrita')) !!}
                        {!! Form::number('impuesto', 16, ['class'=>'form-control', 'max' => '100', 'min' => '1']) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('retencion', '% Retención 1 - ISR', array('class' => 'negrita')) !!}
                        {!! Form::number('retencion', null, ['class'=>'form-control', 'max' => '100', 'min' => '1']) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('retencion2', '% Retención 2 - IVA', array('class' => 'negrita')) !!}
                        {!! Form::number('retencion2', null, ['class'=>'form-control', 'max' => '100', 'min' => '1']) !!}
                    </div>
                </div>

               <div class="col-md-12">

               </div>

                {{-- <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('retencion3', '% Retención 3', array('class' => 'negrita')) !!}
                        {!! Form::number('retencion3', null, ['class'=>'form-control', 'max' => '100', 'min' => '1']) !!}
                    </div>
                </div> --}}

                <div class="col-md-3 ">                               
                    {!!Form::label('grupo', 'Grupo', array('class' => 'negrita'))!!}
                    {!! Form::select('grupo',$grupo_array, null, array('id' => 'select-search-hide-type', "class" => 'widthAll select-status', 'placeholder' => 'Seleccione uno...')) !!} 
                </div>

                <div class="col-md-3 ">
                {!! Form::label('categoria', 'Categoría', array('class' => 'negrita')) !!}
                {!! Form::select('categoria', $categoria_array, null, array('id' => 'select-search-hide-dg2', "class" => 'widthAll select-status', 'placeholder' => 'Seleccione uno...')) !!} 
                </div>
                
                <div class="col-md-2">
                    <div class="form-group mt10">
                        {!! Form::label('statusDG', 'Estatus', array('class' => 'negrita')) !!}
                        {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], 'Alta', array('id' => 'select-search-hide-dg', "class" => 'widthAll select-status')) !!} 
                    </div><!-- form-group -->
                </div>

                {{-- <div class="col-md-2">
                    <div class="form-group exento-iva">
                        <div>
                            {!! Form::label('exentoIvA', 'Exento IVA', array('class' => 'negrita')) !!}
                            {!! Form::checkbox('iva', true, false, array('class' => 'form-control')) !!}
                        </div>
                    </div>
                </div> --}}

                <div class="col-md-12 mt20 display-center">
                    {!! Form::submit("Crear Razón", ['class' => 'btn btn-success enviar']) !!}
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

            
    jQuery(document).ready(function (){
        jQuery('#select-search-hide-dg, #select-search-hide-dg2, #select-search-hide-type' ).select2({
                    minimumResultsForSearch: -1
        });
        jQuery('#basicForm').validate({
            rules:{
               concepto:{
                required: true,
                maxlength: 50,
               },
                impuesto:{
                 
                 number: true,
                 maxlength: 8,
                 minlength: 0,
                },
                retencion:{
                 
                 number: true,
                 maxlength: 8,
                 minlength: 0,
                },
                retencion2:{
                 
                 number: true,
                 maxlength: 8,
                 minlength: 0,
                },
                retencion3:{
                 
                 number: true,
                 maxlength: 8,
                 minlength: 0,
                },
            },
            messages:{
                concepto:{
                    required: 'Este campo es requerido',
                     maxlength: jQuery.validator.format('áaximo de {0} caracteres')
                },
                impuesto:{
                  
                    number: 'Este campo debe ser numérico',
                    maxlength: jQuery.validator.format('máximo de {0} digitos'),
                    minlength: "ingresa un valor mayor a 0",
                    min: jQuery.validator.format('El valor mínimo es {0}'),
                    max: jQuery.validator.format('El valor máximo es {0}'),
                },
                retencion:{
                    
                    number: 'Este campo debe ser numérico',
                    maxlength: jQuery.validator.format('máximo de {0} digitos'),
                    minlength: "ingresa un valor mayor a 0",
                    min: jQuery.validator.format('El valor mínimo es {0}'),
                    max: jQuery.validator.format('El valor máximo es {0}'),
                },
                retencion2:{
                  
                    number: 'Este campo debe ser numérico',
                    maxlength: jQuery.validator.format('máximo de {0} digitos'),
                    minlength: "ingresa un valor mayor a 0",
                    min: jQuery.validator.format('El valor mínimo es {0}'),
                    max: jQuery.validator.format('El valor máximo es {0}'),
                },
                retencion3:{
                    
                    number: 'Este campo debe ser numérico',
                    maxlength: jQuery.validator.format('máximo de {0} digitos'),
                    minlength: "ingresa un valor mayor a 0",
                    min: jQuery.validator.format('El valor mínimo es {0}'),
                    max: jQuery.validator.format('El valor máximo es {0}'),
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