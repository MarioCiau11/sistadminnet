@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => ['catalogo.concepto-gastos.update', Crypt::encrypt($concepto['expenseConcepts_id'])],
                        'id' => 'basicForm',
                        'method' => 'PUT',
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
                            <h2 class="text-black">Datos Generales de la Razón de Gasto</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span class="asterisk">*</span></p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('concepto', 'Nombre/Concepto', 'negrita') !!}
                            {!! Form::text('concepto', $concepto['expenseConcepts_concept'], ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="col-md-12">
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('impuesto', '% Impuestos', ['class' => 'negrita']) !!}
                            {!! Form::number('impuesto', isset($concepto['expenseConcepts_tax']) ? $concepto['expenseConcepts_tax'] : 0, [
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('retencion', '% Retención 1 - ISR', ['class' => 'negrita']) !!}
                            {!! Form::number(
                                'retencion',
                                isset($concepto['expenseConcepts_retention']) ? $concepto['expenseConcepts_retention'] : 0,
                                [
                                    'class' => 'form-control',
                                ],
                            ) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('retencion2', '% Retención 2 - IVA', ['class' => 'negrita']) !!}
                            {!! Form::number(
                                'retencion2',
                                isset($concepto['expenseConcepts_retention2']) ? $concepto['expenseConcepts_retention2'] : 0,
                                [
                                    'class' => 'form-control',
                                ],
                            ) !!}
                        </div>
                    </div>

                    <div class="col-md-12">

                    </div>

                    {{-- <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('retencion3', '% Retención 3', ['class' => 'negrita']) !!}
                            {!! Form::number('retencion3', number_format($concepto['expenseConcepts_retention3'], 2), [
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                    </div> --}}

                    <div class="col-md-3 ">
                        {!! Form::label('grupo', 'Grupo', ['class' => 'negrita']) !!}
                        {!! Form::select('grupo', $grupo_array, $concepto['expenseConcepts_group'], [
                            'id' => 'select-search-hide-type',
                            'class' => 'widthAll select-status',
                            'placeholder' => 'Seleccione uno...'
                        ]) !!}
                    </div>

                    <div class="col-md-3 ">
                        {!! Form::label('categoria', 'Categoría', ['class' => 'negrita']) !!}
                        {!! Form::select('categoria', $categoria_array, $concepto['expenseConcepts_category'], [
                            'id' => 'select-search-hide-dg2',
                            'class' => 'widthAll select-status',
                            'placeholder' => 'Seleccione uno...'
                        ]) !!}
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mt10">
                            {!! Form::label('statusDG', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('statusDG', ['Alta' => 'Alta', 'Baja' => 'Baja'], $concepto['expenseConcepts_status'], [
                                'id' => 'select-search-hide-dg',
                                'class' => 'widthAll select-status',
                            ]) !!}
                        </div><!-- form-group -->
                    </div>

                    {{-- <?php $check = $concepto['expenseConcepts_exemptIVA'] == 0 ? false : true; ?>
                    <div class="col-md-2">
                        <div class="form-group exento-iva">
                            <div>
                                {!! Form::label('exentoIvA', 'Exento IVA', ['class' => 'negrita']) !!}
                                {!! Form::checkbox('iva', $check, $check, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    </div> --}}


                    <div class="col-md-12 mt50 display-flex text-center">
                        {!! Form::button('Cancelar', ['class' => 'btn btn-danger', 'id' => 'regreso']) !!}
                        {!! Form::submit('Guardar Razón de Gasto', ['class' => 'btn btn-warning enviar']) !!}
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

        jQuery('#regreso').click(function() {
            window.location.href = "{{ route('catalogo.concepto-gastos.index') }}";
        });
    </script>
@endsection
