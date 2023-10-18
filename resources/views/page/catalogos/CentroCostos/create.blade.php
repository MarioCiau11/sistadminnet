@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open(['route' => 'catalogo.centroCostos.store', 'id' => 'basicForm']) !!}

                    {!! Form::macro('labelValidacion', function ($name, $labelName, $classes) {
                        return "<label for= '" .
                            $name .
                            "' class= '" .
                            $classes .
                            "'>" .
                            $labelName .
                            "<span class='asterisk'> *</span> </label>";
                    }) !!}

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <h2 class="text-black">Datos Generales del Centro de Costo</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('keyCentroCosto', 'Clave del Centro de Costo', 'negrita') !!}
                            {!! Form::text('keyCentroCosto', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameCentroCosto', 'Nombre del Centro de Costo', 'negrita') !!}
                            {!! Form::text('nameCentroCosto', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-12"></div>


                    <div class="col-md-3">
                        <div class="form-group mt10">
                            {!! Form::label('status', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja'], 'Alta', [
                                'id' => 'select-search-hide-status',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12 mt20 display-center">
                        {!! Form::submit('Crear Centro de Costo', ['class' => 'btn btn-success enviar']) !!}
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
            
        jQuery(document).ready(function() {
            //Ocultamos el ProdServ
            const $select = jQuery('#select-search-hide-status, #select-search-hide-modulo').select2({
                minimumResultsForSearch: -1
            });

            jQuery('#basicForm').validate({
                rules: {
                    nameCentroCosto: {
                        required: true,
                    },
                    keyCentroCosto: {
                        required: true,
                        maxlength: 50,
                    },

                },
                messages: {
                        keyCentroCosto: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Máximo de {0} caracteres')
                    },
                        nameCentroCosto: {
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
            });

        });
    </script>
@endsection
