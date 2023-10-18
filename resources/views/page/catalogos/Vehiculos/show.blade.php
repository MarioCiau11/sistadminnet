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
                <h2 class="text-black">Datos Generales del Vehículo</h2>
                <div class="col-md-4">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('keyClave', 'Clave', 'negrita') !!}
                        {!! Form::text('keyClave',$vehiculo->vehicles_key,['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mt10" >
                        {!! Form::labelValidacion('nameNombre', 'Nombre', 'negrita') !!}
                        {!! Form::text('nameNombre',$vehiculo->vehicles_name,['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mt10" >
                        {!! Form::label('namePlacas', 'Placas', array('class' => 'negrita')) !!}
                        {!! Form::text('namePlacas',$vehiculo->vehicles_plates,['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-12">
                </div>

                <div class="col-md-4">
                    <div class="form-group mt10">
                        {!! Form::labelValidacion('capacidadVolumen', 'Capacidad Volumen', 'negrita') !!}
                        {!! Form::text('capacidadVolumen',$vehiculo->vehicles_capacityVolume,['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mt10">
                        {!! Form::label('capacidadPeso', 'Capacidad Peso', array('class' => 'negrita')) !!}
                        {!! Form::text('capacidadPeso',$vehiculo->vehicles_capacityWeight,['class'=>'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mt10">
                        {!! Form::label('agenteXOmision', 'Operativo por Omisión', array('class' => 'negrita')) !!}
                        {!! Form::select('agenteXOmision',$select_agente,$vehiculo->vehicles_defaultAgent , array('id' => 'select-search-hide-dg', "class" => 'widthAll select-status', 'placeholder' => 'Seleccione uno...', 'disabled')) !!} 
                    </div>
                </div>

                <div class="col-md-12">
                </div>

                <div class="col-md-1">
                    <div class="form-group mt10">
                        {!! Form::label('statusDG', 'Estatus', array('class' => 'negrita')) !!}
                        {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'],$vehiculo->vehicles_status , array('id' => 'select-search-hide-dg', "class" => 'widthAll select-status', 'placeholder' => 'Seleccione uno...', 'disabled')) !!} 
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mt10">
                        {!! Form::labelValidacion('nameSucursal', 'Sucursal', 'negrita') !!}
                        {!! Form::select('nameSucursal', $select_sucursales,$vehiculo->vehicles_branchOffice, array('id' => 'select-search-hide-dg', "class" => 'widthAll select-sucursal', 'placeholder' => 'Seleccione uno...', 'disabled')) !!} 
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group mt10">
                        {!! Form::label('nameCategoria', 'Categoria', ['class' => 'negrita']) !!}
                        {!! Form::select('nameCategoria', $select_categoria, $vehiculo->vehicles_category, [
                            'id' => 'select-search-hide-dg',
                            'class' => 'widthAll select-status',
                            'placeholder' => 'Seleccione uno...',
                            'disabled'
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group mt10">
                        {!! Form::label('nameGrupo', 'Grupo', ['class' => 'negrita']) !!}
                        {!! Form::select('nameGrupo', $select_grupo, $vehiculo->vehicles_group, [
                            'id' => 'select-search-hide-dg',
                            'class' => 'widthAll select-grupo',
                            'placeholder' => 'Seleccione uno...',
                            'disabled'
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-12">
                </div>        
                <div class="col-md-6">
                    <div class="form-group mt10">
                        {!! Form::labelNOValidacion("createat", 'Fecha de Creación', 'negrita') !!}
                        {!! Form::text('createat',$vehiculo->created_at , ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mt10">
                        {!! Form::labelNOValidacion("updateat", 'Fecha de Actualización', 'negrita') !!}
                        {!! Form::text('updateat', $vehiculo->updated_at, ['class' => 'form-control', 'disabled']) !!}
                    </div>
                </div>        
        </div>
    </div>
</div>

<script>
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
                nameSucursal:{
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
                nameSucursal:{
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
