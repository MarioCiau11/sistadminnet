@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open(['route' => 'catalogo.sucursal.store', 'id' => 'basicForm']) !!}

                    {!! Form::macro('labelValidacion', function ($name, $labelName, $classes) {
                        return "<label for= '" .
                            $name .
                            "' class= '" .
                            $classes .
                            "'>" .
                            $labelName .
                            "<span class='asterisk'> *</span> </label>";
                    }) !!}
                    {!! Form::macro('labelNOValidacion', function ($name, $labelName, $classes) {
                        return "<label for= '" . $name . "' class= '" . $classes . "'>" . $labelName . '</label>';
                    }) !!}
                    <h2 class="text-black">Datos Generales de la Razón de Gasto</h2>
                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('concepto', 'Nombre', 'negrita') !!}
                            {!! Form::text('concepto', $concepto['expenseConcepts_concept'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('impuesto', '% Impuestos', ['class' => 'negrita']) !!}
                            {!! Form::number('impuesto', $concepto['expenseConcepts_tax'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('retencion', '% Retención 1 - ISR', ['class' => 'negrita']) !!}
                            {!! Form::number('retencion', $concepto['expenseConcepts_retention'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('retencion2', '% Retención 2 - IVA', ['class' => 'negrita']) !!}
                            {!! Form::number('retencion2', $concepto['expenseConcepts_retention2'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">

                    </div>

                    {{-- <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('retencion3', '% Retención 3', ['class' => 'negrita']) !!}
                            {!! Form::number('retencion3', $concepto['expenseConcepts_retention3'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div> --}}

                    <div class="col-md-3 ">
                        {!! Form::label('grupo', 'Grupo', ['class' => 'negrita']) !!}
                        {!! Form::select('grupo', $grupo_array, $concepto['expenseConcepts_group'], [
                            'id' => 'select-search-hide-type',
                            'class' => 'widthAll select-status',
                            'placeholder' => 'Seleccione uno...',
                            'disabled',
                        ]) !!}
                    </div>

                    <div class="col-md-3 ">
                        {!! Form::label('categoria', 'Categoría', ['class' => 'negrita']) !!}
                        {!! Form::select('categoria', $categoria_array, $concepto['expenseConcepts_category'], [
                            'id' => 'select-search-hide-dg2',
                            'class' => 'widthAll select-status',
                            'placeholder' => 'Seleccione uno...',
                            'disabled',
                        ]) !!}
                    </div>

                    <div class="col-md-1">
                        <div class="form-group">
                            {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $concepto['expenseConcepts_status'], [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                                'disabled',
                            ]) !!}
                        </div><!-- form-group -->
                    </div>

                    {{-- <div class="col-md-1">
                        <div class="form-group exento-iva">
                            <div>
                                <?php $check = $concepto['expenseConcepts_exemptIVA'] == 0 ? false : true; ?>

                                {!! Form::label('exentoIvA', 'Exento IVA', ['class' => 'negrita']) !!}
                                {!! Form::checkbox('iva', $check, $check, ['class' => 'form-control', 'disabled']) !!}
                            </div>
                        </div>
                    </div> --}}

                    <div class="col-md-12"></div>

                    <div class="col-md-12"></div>
                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelNOValidacion('createat', 'Fecha de Creación', 'negrita') !!}
                            {!! Form::text('createat', $concepto['created_at'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelNOValidacion('updateat', 'Fecha de Actualización', 'negrita') !!}
                            {!! Form::text('updateat', $concepto['updated_at'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>


                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function() {
            jQuery('#select-search-hide-dg, #select-search-hide-dg2, #select-search-hide-type').select2({
                minimumResultsForSearch: -1
            });
            jQuery('#basicForm').validate({
                rules: {
                    concepto: {
                        required: true,
                        maxlength: 50,
                    }
                },
                messages: {
                    concepto: {
                        required: 'Este campo es requerido'
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
