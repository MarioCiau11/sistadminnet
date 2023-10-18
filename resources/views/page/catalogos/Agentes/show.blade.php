@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="contentpanel">
        <div class="row row-stat">
            <div class="contenedor-formulario">
                {!! Form::open(['route' => 'catalogo.agentes.store', 'id' => 'basicForm']) !!}

                {!! Form::macro('labelValidacion', function ($name, $labelName, $classes){
                    return "<label for= '".$name."' class= '".$classes."'>".$labelName."<span class='asterisk'> *</span> </label>";
                }) !!}
                {!! Form::macro('labelNOValidacion', function ($name, $labelName, $classes){
                    return "<label for= '".$name."' class= '".$classes."'>".$labelName."</label>";
                }) !!}

                <h2 class="text-black">Datos Generales del Operativo</h2>
                <div class="col-md-4">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('keyClave', 'Clave', 'negrita') !!}
                        {!! Form::text('keyClave',$agentes->agents_key,['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('nameNombre', 'Nombre', 'negrita') !!}
                        {!! Form::text('nameNombre',$agentes->agents_name,['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mt10">
                        {!! Form::label('nameCategoria', 'Categoria', array('class' => 'negrita')) !!}
                        {!! Form::select('nameCategoria', $select_categoria, $agentes->agents_category, array('id' => 'select-search-hide-dg', "class" => 'widthAll select-status', 'placeholder' => 'Seleccione uno...', 'disabled')) !!} 
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mt10">
                        {!! Form::label('nameGrupo', 'Grupo', array('class' => 'negrita')) !!}
                        {!! Form::select('nameGrupo', $select_grupo, $agentes->agents_group, array('id' => 'select-search-hide-dg', "class" => 'widthAll select-grupo', 'placeholder' => 'Seleccione uno...', 'disabled')) !!} 
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mt10">
                    {!! Form::labelValidacion('nombreSuc', 'Sucursal', 'negrita') !!}
                        {!! Form::select('nameSucursal', $select_sucursales,$agentes->agents_branchOffice , array('id' => 'select-search-hide-dg', "class" => 'widthAll select-sucursal', 'placeholder' => 'Seleccione uno...', 'disabled')) !!} 
                    </div>
                </div>

                <div class="col-md-12">
                </div>

                <div class="col-md-2">
                    <div class="form-group mt10">
                        {!! Form::label('statusDG', 'Estatus', array('class' => 'negrita')) !!}
                        {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $agentes->agents_status, array('id' => 'select-search-hide-dg', "class" => 'widthAll select-status', 'disabled')) !!} 
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group mt10">
                        {!! Form::labelValidacion('nameTipo', 'Tipo de Operativo','negrita') !!}
                        {!! Form::select('nameTipo', ['Vendedor' =>'Vendedor', 'Operador' => 'Operador'], $agentes->agents_type, array('id' => 'select-search-hide-dg', "class" => 'widthAll select-status', 'placeholder' => 'Seleccione uno...', 'disabled')) !!} 
                    </div>
                </div>

                <div class="col-md-12">
                </div>

                <div class="col-md-6">
                    <div class="form-group mt10">
                        {!! Form::labelNOValidacion("createat", 'Fecha de Creación', 'negrita') !!}
                        {!! Form::text('createat', $agentes->created_at, ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mt10">
                        {!! Form::labelNOValidacion("updateat", 'Fecha de Actualización', 'negrita') !!}
                        {!! Form::text('updateat', $agentes->updated_at, ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function (){
        jQuery('#select-search-hide-dg, #select-search-hide-keyClave, #select-search-hide-nameNombre, #select-search-hide-nameTipo, #select-search-hide-status').select2({
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
