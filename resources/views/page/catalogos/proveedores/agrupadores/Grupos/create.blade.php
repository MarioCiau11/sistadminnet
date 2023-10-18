@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="contentpanel">
        <div class="row row-stat">
            <div class="contenedor-formulario">
                {!! Form::open(['route' => ['agregarGrupo', 'tipo' => 'Proveedor'],'id' => 'basicForm']) !!}

                {!! Form::macro('labelValidacion', function ($name, $labelName, $classes){
                    return "<label for= '".$name."' class= '".$classes."'>".$labelName."<span class='asterisk'> *</span> </label>";
                }) !!}

                <div class="col-md-12">
                    <div class="col-md-6">
                        <h2 class="text-black">Datos generales</h2>
                    </div>
                    <div class="col-md-6">
                        <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('keyClave', 'Clave', 'negrita') !!}
                        {!! Form::text('keyClave',null,['class'=>'form-control', 'disabled', 'id'=>'keyGrupo']) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('nameNombre', 'Nombre', 'negrita') !!}
                        {!! Form::text('nameNombre',null,['class'=>'form-control']) !!}
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group mt10">
                        {!! Form::label('statusDG', 'Estatus', array('class' => 'negrita')) !!}
                        {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], 'Alta', array('id' => 'select-search-hide-dg', "class" => 'widthAll select-status')) !!} 
                    </div>
                </div>

                
                
                <div class="col-md-12 mt20 display-center">
                    {!! Form::submit("Crear Grupo", ['class' => 'btn btn-success enviar']) !!}
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
        jQuery('#select-search-hide-dg, #select-search-hide-keyClave, #select-search-hide-nameNombre, #select-search-hide-nameTipo, #select-search-hide-status, #select-search-hide-tipo').select2({
                    minimumResultsForSearch: -1
        });

        jQuery("#select-basic-empresa").select2();

        jQuery('#basicForm').validate({
            rules:{
                keyClave:{
                    required: true,
                    maxlength: 10,
                },
                nameNombre:{
                    required: true,
                    maxlength: 100,
                },
                nameTipo:{
                    required: true,
                },
                nombreSuc:{
                    required: true,
                }
            },
            messages:{
                keyClave:{
                    required: "Este campo es requerido",
                    maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                },
                nameNombre: {
                    required: "Este campo es requerido",
                    maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                },
                nameTipo:{
                    required: "Este campo es requerido",
                },
                nombreSuc:{
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

        $.get('/grupo/getGrupo', { tipo: 'Proveedor'},function(resp) {
                $.each(resp, function(i, item) {
                    $('#keyGrupo').val(item)
                });
            });
    });
</script>
@endsection
