@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => ['configuracion.motivos-cancelacion.update', $motivo_edit['reasonCancellations_id']],
                        'method' => 'PUT',
                         'id' => 'basicForm']) !!}

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
                            <h2 class="text-black">Datos Generales del Motivo de Cancelación</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameMotivo', 'Motivo de Cancelación', 'negrita') !!}
                            {!! Form::text('nameMotivo', $motivo_edit['reasonCancellations_name'], ['class' => 'form-control']) !!}
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
                                $motivo_edit['reasonCancellations_module'],
                                ['id' => 'select-search-hide-modulo', 'class' => 'widthAll select-status', 'placeholder' => 'Seleccione uno...'],
                            ) !!}
                        </div>
                    </div>

                    <div class="col-md-12"></div>


                    <div class="col-md-3">
                        <div class="form-group mt10">
                            {!! Form::label('status', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja'], $motivo_edit['reasonCancellations_status'], [
                                'id' => 'select-search-hide-status',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12 mt20 display-center">
                        {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                        {!! Form::submit('Guardar Motivo', ['class' => 'btn btn-warning enviar']) !!}
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
                    nameMotivo: {
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
                    nameMotivo: {
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

                        //evento change a un input al cargar la pagina
            $("#select-search-hide-modulo").trigger("change");
            jQuery('#regreso').click(function() {
                window.location.href = "{{ route('configuracion.concepto-modulos.index') }}";
            });

        });
    </script>
@endsection
