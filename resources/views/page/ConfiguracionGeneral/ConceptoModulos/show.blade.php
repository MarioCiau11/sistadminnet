@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open([
                        'route' => 'configuracion.condiciones-credito.store',
                        $concepto_show['moduleConcept_id'],
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

                    {!! Form::macro('labelNOValidacion', function ($name, $labelName, $classes) {
                        return "<label for= '" . $name . "' class= '" . $classes . "'>" . $labelName . '</label>';
                    }) !!}


                    <h2 class="text-black">Datos Generales del Concepto</h2>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameConcepto', 'Nombre del Concepto', 'negrita') !!}
                            {!! Form::text('nameConcepto', $concepto_show['moduleConcept_name'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('modulo', 'Proceso', 'negrita') !!}
                            {!! Form::select(
                                'modulo',
                                [
                                    'Ventas' => 'VTAS - Ventas',
                                    'Compras' => 'COMS - Compras',
                                    'Inventarios' => 'INV - Inventarios',
                                    'Cuentas por Pagar' => 'CXP - Cuentas por Pagar',
                                    'Cuentas por Cobrar' => 'CXC - Cuentas por Cobrar',
                                    'Gastos' => 'GAS - Gastos',
                                    'Tesorería' => 'TES - Tesorería',
                                    'Activo Fijo' => 'AF - Activo Fijo',
                                ],
                                $concepto_show['moduleConcept_module'],
                                [
                                    'id' => 'select-search-hide-modulo',
                                    'class' => 'widthAll select-status',
                                    'placeholder' => 'Seleccione uno...',
                                    'disabled',
                                ],
                            ) !!}
                        </div>
                    </div>



                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('movimiento[]', 'Movimiento', 'negrita') !!}
                            {!! Form::select('movimiento[]', $movimientosList, $movimientosRelacionadosConcepto, [
                                'id' => 'select-search-hide-movimiento',
                                'class' => 'widthAll',
                                'multiple',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12">

                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelNOValidacion('createat', 'Fecha de Creación', 'negrita') !!}
                            {!! Form::text('createat', $concepto_show['created_at'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::labelNOValidacion('updateat', 'Fecha de Actualización', 'negrita') !!}
                            {!! Form::text('updateat', $concepto_show['updated_at'], ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    </div>

                    <div class="col-md-6" id="claveProdServ">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('claveProd', 'Clave de Producto o Servicio', 'negrita') !!}
                            {!! Form::text('prodServ', $concepto_show['moduleConcept_prodServ'], [
                                'class' => 'form-control',
                                'id' => 'prodServ',
                                'autocomplete' => 'on',
                                'disabled',
                            ]) !!}
                            <span class="error-login" id="mensaje-error-prodServ" style="display: none">
                                No se encontraron resultados para la búsqueda
                            </span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mt10">
                            {!! Form::label('status', 'Estatus', ['class' => 'negrita']) !!}
                            {!! Form::select('status', ['Alta' => 'Alta', 'Baja' => 'Baja'], $concepto_show['moduleConcept_status'], [
                                'id' => 'select-search-hide-status',
                                'class' => 'widthAll select-status',
                                'disabled',
                            ]) !!}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function() {
            var opcionesMovimientos = {
                'Ventas': {
                    'Cotización': 'Cotización',
                    'Pedido': 'Pedido',
                    'Factura': 'Factura'
                },
                'Cuentas por Cobrar': {
                    'Anticipo Clientes': 'Anticipo Clientes',
                    'Aplicación': 'Aplicación',
                    'Devolución Anticipo': 'Devolución Anticipo',
                    'Cobro de Facturas': 'Cobro de Facturas'
                },
                'Tesorería': {
                    'Traspaso Cuentas': 'Traspaso Cuentas',
                    'Ingreso': 'Ingreso',
                    'Egreso': 'Egreso',
                    'Depósito': 'Depósito',
                    'Transferencia Electrónica': 'Transferencia Electrónica'
                },
                'Compras': {
                    'Orden de Compra': 'Orden de Compra',
                    'Entrada por Compra': 'Entrada por Compra'
                },
                'Inventarios': {
                    'Ajuste de Inventario': 'Ajuste de Inventario',
                    'Salida por Traspaso': 'Salida por Traspaso',
                    'Entrada por Traspaso': 'Entrada por Traspaso',
                    'Transferencia entre Alm.': 'Transferencia entre Alm.',
                },
                'Cuentas por Pagar': {
                    'Anticipo': 'Anticipo',
                    'Aplicación': 'Aplicación',
                    'Pago de Facturas': 'Pago de Facturas'
                },
                // Agrega más opciones para los otros módulos
            };

            // Evento de cambio en la lista desplegable de módulos
            $("#select-search-hide-modulo").change(function() {
                var selectedModulo = $(this).val();
                var $selectMovimiento = $("#select-search-hide-movimiento");
                console.log(selectedModulo);

                // Actualizar las opciones de la lista desplegable de movimientos
                var opciones = opcionesMovimientos[selectedModulo] || {};
                $selectMovimiento.empty();

                $.each(opciones, function(key, value) {
                    $selectMovimiento.append("<option value='" + key + "'>" + value + "</option>");
                });

                // Establecer los valores seleccionados
                var movimientosSeleccionados = {!! json_encode($movimientosRelacionadosConcepto) !!};
                $selectMovimiento.val(movimientosSeleccionados).trigger('change');
            });
            jQuery('#select-search-hide-status, #select-search-hide-modulo, #select-search-hide-movimiento')
                .select2({
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

                },
                messages: {
                    nameConcepto: {
                        required: 'Este campo es requerido',
                        maxlength: jQuery.validator.format('Maximo de {0} caracteres')
                    },
                    modulo: {
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
            })


            $("#select-search-hide-modulo").change(() => {
                let modulo = $("#select-search-hide-modulo").val();
                if (modulo === "Cuentas por Cobrar") {
                    $("#claveProdServ").show();
                } else {
                    $("#prodServ").val("")
                    $("#claveProdServ").hide();
                }
            });

            //evento change a un input al cargar la pagina
            $("#select-search-hide-modulo").trigger("change");
        });
    </script>
@endsection
