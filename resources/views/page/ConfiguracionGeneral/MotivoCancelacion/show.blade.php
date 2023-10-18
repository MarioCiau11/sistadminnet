@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open(['route' => 'configuracion.motivos-cancelacion.store', 'id' => 'basicForm']) !!}

                    {!! Form::macro('labelValidacion', function ($name, $labelName, $classes) {
                        return "<label for= '" .
                            $name .
                            "' class= '" .
                            $classes .
                            "'>" .
                            $labelName .
                            "<span class='asterisk'> *</span> </label>";
                    }) !!}

                    <h2 class="text-black">Datos Generales del Motivo de Cancelación</h2>

                    <div class="col-md-9">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameMotivo', 'Motivo de Cancelación', 'negrita') !!}
                            {!! Form::text('nameMotivo', $motivo_show['reasonCancellations_name'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('modulo', 'Módulo', 'negrita') !!}
                            {!! Form::select(
                                'modulo',
                                [
                                    'Ventas' => 'VTAS - Ventas',
                                    'Compras' => 'COMS - Compras',
                                    'Inventarios' => 'INV - Inventarios',
                                ],
                                $motivo_show['reasonCancellations_module'],
                                ['id' => 'select-search-hide-modulo', 'class' => 'widthAll select-status', 'placeholder' => 'Seleccione uno...'
                                , 'disabled'],
                            ) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::label('createat', 'Fecha de Creación', ['class' => 'negrita']) !!}
                            {!! Form::text('createat', $motivo_show['created_at'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::label('updateat', 'Fecha de Actualización', ['class' => 'negrita']) !!}
                            {!! Form::text('updateat', $motivo_show['updated_at'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>


                    <div class="col-md-3">
                        <div class="form-group mt10">
                            {!! Form::label('status', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja'], $motivo_show['reasonCancellations_status'], [
                                'id' => 'select-search-hide-status',
                                'class' => 'widthAll select-status',
                                'disabled'
                            ]) !!}
                        </div>
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
                    nameConcepto: {
                        required: true,
                        maxlength: 50,
                    },
                    modulo: {
                        required: true,
                    },
                    prodServ: {
                        required: true,
                    },

                },
                messages: {
                    nameConcepto: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    modulo: {
                        required: "Este campo es requerido",
                    },
                    prodServ: {
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
