@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="contentpanel">
            <div class="row row-stat">
                <div class="contenedor-formulario">
                    {!! Form::open(['route' => 'configuracion.concepto-modulos.store', 'id' => 'basicForm']) !!}

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
                            <h2 class="text-black">Datos Generales del Concepto</h2>
                        </div>
                        <div class="col-md-6">
                            <p class="titulo text-right">Identifica los campos obligatorios con <span
                                    class="asterisk">*</span></p>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('nameConcepto', 'Nombre del Concepto', 'negrita') !!}
                            {!! Form::text('nameConcepto', null, ['class' => 'form-control']) !!}
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
                                    'Tesorería' => 'DIN - Tesorería',
                                    'Activo Fijo' => 'AF - Activo Fijo',
                                ],
                                null,
                                ['id' => 'select-search-hide-modulo', 'class' => 'widthAll select-status', 'placeholder' => 'Seleccione uno...'],
                            ) !!}
                        </div>
                    </div>

                    {{-- <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('movimiento', 'Movimiento', 'negrita') !!}
                            {!! Form::select('movimiento', [], null, [
                                'id' => 'select-search-hide-movimiento',
                                'class' => 'widthAll select-status',
                                'placeholder' => 'Seleccione uno...',
                            ]) !!}
                        </div>
                    </div> --}}
                    <div class="col-md-4">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('movimiento[]', 'Movimiento', 'negrita') !!}
                            {!! Form::select('movimiento[]', [], null, [
                                'id' => 'select-search-hide-movimiento',
                                'class' => 'widthAll',
                                'multiple',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-12"></div>

                    <div class="col-md-6" id="claveProdServ">
                        <div class="form-group mt10">
                            {!! Form::labelValidacion('claveProd', 'Clave de Producto o Servicio', 'negrita') !!}
                            {!! Form::text('prodServ', null, ['class' => 'form-control', 'id' => 'prodServ', 'autocomplete' => 'on']) !!}
                            <span class="error-login" id="mensaje-error-prodServ" style="display: none">
                                No se encontraron resultados para la búsqueda
                            </span>
                        </div>
                    </div>



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
                        {!! Form::submit('Crear concepto', ['class' => 'btn btn-success enviar']) !!}
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

            // Inicializar el objeto con valores predeterminados en caso de que esté vacío
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

                // Actualizar las opciones de la lista desplegable de movimientos
                var opciones = opcionesMovimientos[selectedModulo] || {};
                $selectMovimiento.empty().append("<option value=''>Seleccione uno...</option>");
                $.each(opciones, function(key, value) {
                    $selectMovimiento.append("<option value='" + key + "'>" + value + "</option>");
                });
            });

            // Disparar el evento de cambio para cargar las opciones iniciales
            $("#select-search-hide-modulo").trigger("change");

            //Ocultamos el ProdServ
            const $select = jQuery(
                    '#select-search-hide-status, #select-search-hide-modulo')
                .select2({
                    minimumResultsForSearch: -1
                });

            const $select2 = jQuery("#select-search-hide-movimiento").select2({
                placeholder: 'Seleccione uno...',
                allowClear: true
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
                    movimiento: {
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
                    movimiento: {
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

            //ProdServ SAT
            $("#prodServ").autocomplete({
                minLength: 3,
                source: function(request, response) {
                    $.ajax({
                        url: "/prodServ/busqueda",
                        type: "GET",
                        data: {
                            prodServ: jQuery('#prodServ').val()
                        },
                        success: function({
                            prodServ
                        }) {
                            if (prodServ.length > 0) {
                                response(prodServ);
                                jQuery('#mensaje-error-prodServ').hide();
                            } else {
                                jQuery('#mensaje-error-prodServ').show();
                            }

                        }
                    })
                }
            });



            $("#select-search-hide-modulo").change(() => {
                let modulo = $("#select-search-hide-modulo").val();
                if (modulo === "Cuentas por Cobrar") {
                    $("#claveProdServ").show();
                } else {
                    $("#prodServ").val("")
                    $("#claveProdServ").hide();
                }
            });

            $select.rules('add', {
                required: true,
                messages: {
                    required: "Este campo es requerido",
                }
            });

            $select.on('change', function() {
                $(this).trigger('blur');
            });
            //evento change a un input al cargar la pagina
            $("#select-search-hide-modulo").trigger("change");

        });
    </script>
@endsection
