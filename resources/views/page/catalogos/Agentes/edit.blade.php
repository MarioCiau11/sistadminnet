@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => ['catalogo.agentes.update', Crypt::encrypt($agentes_edit->agents_key)],
                        'method' => 'PUT',
                        'id' => 'basicForm',
                    ]) !!}

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
                            <h2 class="text-black">Datos Generales del Operativo</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('keyClave', 'Clave', 'negrita') !!}
                            {!! Form::text('keyClave', $agentes_edit->agents_key, ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameNombre', 'Nombre', 'negrita') !!}
                            {!! Form::text('nameNombre', $agentes_edit->agents_name, ['class' => 'form-control']) !!}
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::label('nameCategoria', 'Categoria', ['class' => 'negrita']) !!}
                            {!! Form::select('nameCategoria', $select_categoria, $agentes_edit->agents_category, [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::label('nameGrupo', 'Grupo', ['class' => 'negrita']) !!}
                            {!! Form::select('nameGrupo', $select_grupo, $agentes_edit->agents_group, [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-grupo',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                        {!! Form::labelValidacion('nombreSuc', 'Sucursal', 'negrita') !!}
                            {!! Form::select('nombreSuc', $select_sucursales, $agentes_edit->agents_branchOffice, [
                                'id' => 'select-search-hide-dgS',
                                'class' => 'widthAll select-sucursal',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>

                    <div class="col-md-1">
                        <div class="form-group mt10">
                            {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $agentes_edit->agents_status, [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameTipo', 'Tipo de Operativo', 'negrita') !!}
                            {!! Form::select(
                                'nameTipo',
                                ['Vendedor' =>'Vendedor', 'Operador' => 'Operador'],
                                $agentes_edit->agents_type,
                                ['id' => 'select-search-hide-tipo', 'class' => 'widthAll select-tipo', 'placeholder' => 'Seleccione uno...'],
                            ) !!}
                        </div>
                    </div>


                    <div class="col-md-12 mt50 display-flex text-center">
                        {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                        {!! Form::submit('Guardar Operativo', ['class' => 'btn btn-warning enviar']) !!}

                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function() {
            jQuery(
                    '#select-search-hide-dg, #select-search-hide-keyClave, #select-search-hide-nameNombre, #select-search-hide-nameTipo, #select-search-hide-status, #select-search-hide-tipo'
                    )
                .select2({
                    minimumResultsForSearch: -1
                });

            const $select = jQuery(
                '#select-search-hide-dgS, #select-search-hide-keyClave, #select-search-hide-nameTipo, #select-search-hide-tipo'
            ).select2({
                minimumResultsForSearch: -1
            });
            jQuery("#select-basic-empresa").select2();

            jQuery('#basicForm').validate({
                rules: {
                    keyClave: {
                        required: true,
                        maxlength: 10,
                    },
                    nameNombre: {
                        required: true,
                        maxlength: 100,
                    },
                    nameTipo: {
                        required: true,
                    }
                },
                messages: {
                    keyClave: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    nameNombre: {
                        required: "Este campo es requerido",
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    nameTipo: {
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

            $select.rules('add', {
                required: true,
                messages: {
                    required: "Este campo es requerido",
                }
            });

            $select.on('change', function() {
                $(this).trigger('blur');
            });
            jQuery('#regreso').click(function() {
                window.location.href = "{{ route('catalogo.agentes.index') }}";
            });

            
        //ahora hacemos que cuando le de clic en el botón de guardar aparezca el loader
        jQuery(".enviar").click(function() {
                //solo mostrar el loader si los campos están validados
                if (jQuery("#basicForm").valid()) {
                    jQuery("#loader").show();
                }
            });
        });
    </script>
@endsection
