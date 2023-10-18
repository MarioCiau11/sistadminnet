@extends('layouts.layout')

@section('content')
<div class="mainpanel">
    <div class="contentpanel">
        <div class="row row-stat">
            <div class="contenedor-formulario">
                {!! Form::open(['route' => 'catalogo.sucursal.store', 'id' => 'basicForm']) !!}

                {!! Form::macro('labelValidacion', function ($name, $labelName, $classes){
                    return "<label for= '".$name."' class= '".$classes."'>".$labelName."<span class='asterisk'> *</span> </label>";
                }) !!}
                {!! Form::macro('labelNOValidacion', function ($name, $labelName, $classes){
                    return "<label for= '".$name."' class= '".$classes."'>".$labelName."</label>";
                }) !!}
                <h2 class="text-black">Datos Generales del Almacén</h2>
                <div class="col-md-4">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('keyAlmacen', 'Clave', 'negrita') !!}
                        {!! Form::text('keyAlmacen',$almacen['depots_key'],['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="form-group mt10">
                        {!! Form::labelValidacion("nameSucursal", 'Nombre del Almacén', 'negrita') !!}
                        {!! Form::text('nameAlmacen',$almacen['depots_name'] , ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-1">
                    <div class="form-group mt10">
                        {!! Form::label('statusDG', 'Estatus', array('class' => 'negrita')) !!}
                        {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $almacen['depots_status'], array('id' => 'select-search-hide-dg', "class" => 'widthAll select-status', 'disabled')) !!} 
                    </div><!-- form-group -->
                </div>

                <div class="col-md-12">
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::labelValidacion("type", 'Tipo', 'negrita') !!}
                        {!! Form::select('type', ['Normal' => 'De Inventario', 'Activo Fijo' => 'Para Activos Fijos'],$almacen['depots_type'] , array('id' => 'select-search-hide-type', "class" => 'widthAll select-status', 'disabled')) !!} 
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::labelValidacion("sucursal", 'Sucursal a la que pertenece', 'negrita') !!}
                        {!! Form::select('sucursal',$select_sucursales, $almacen['depots_branchlId'], array('id' => 'select-search-hide-sucursal', 'class' => 'widthAll select-status select-control', 'placeholder' => 'Seleccione uno...', 'disabled' )) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mt10">
                        {!! Form::labelNOValidacion("createat", 'Fecha de Creación', 'negrita') !!}
                        {!! Form::text('createat',$almacen['created_at'] , ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mt10">
                        {!! Form::labelNOValidacion("updateat", 'Fecha de Actualización', 'negrita') !!}
                        {!! Form::text('updateat', $almacen['updated_at'], ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>
                
                
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function (){
        jQuery('#select-search-hide-dg, #select-search-hide-type, #select-search-hide-sucursal').select2({
                    minimumResultsForSearch: -1
        });

        jQuery('#basicForm').validate({
            rules:{
                keyAlmacen:{
                    required: true,
                    maxlength: 10,
                },
                nameAlmacen:{
                    required: true,
                    maxlength: 100,
                },
                type:{
                    required: true,
                },
                sucursal:{
                    required: true,
                }
            },
            messages:{
                keyAlmacen:{
                    required: "Este campo es requerido",
                    maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                },
                nameAlmacen: {
                    required: "Este campo es requerido",
                    maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                },
                type:{
                    required: "Este campo es requerido",
                },
                sucursal: {
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