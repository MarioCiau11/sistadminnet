let articlesDelete = {};
let result = 1000;
let articulosSerie = {};
let articulosSerieTrans = {};
let articulosSerieQuitar = {};

const posiblesLotesSeries = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

jQuery(document).ready(function () {
    moment.locale("es-mx");
    jQuery('#select-search-hide').select2({
        minimumResultsForSearch: -1
    });

    jQuery('#select-search-grupo').select2({
        minimumResultsForSearch: -1
    });
    jQuery('#select-search-type').select2({
        minimumResultsForSearch: -1
    });
    jQuery('#select-search-familia').select2({
        minimumResultsForSearch: -1
    });
    let movimientoActualModulo = $("#select-movimiento").val();
    let jsonGuardadoDetalle = $("#inputDataArticles2").val();

    if (jsonGuardadoDetalle != "") {
        let jsonDecode = JSON.parse(jsonGuardadoDetalle);
        let seriesData = jsonDecode["series"];
        let idSeriesData = jsonDecode["idSeries"];
        let seriesDeleteData = jsonDecode["seriesD"];
        let idsSeriesDeleteData = jsonDecode["idSeriesD"];

        if (seriesData != undefined) {
            let keysIdentificador = Object.keys(seriesData);

            keysIdentificador.forEach((element) => {
                articulosSerie = {
                    ...articulosSerie,
                    [element]: {
                        serie: seriesData[element],
                        ids: idSeriesData[element],
                    },
                };
            });
        }

        if (seriesDeleteData != undefined) {
            let keysIdentificadorDelete = Object.keys(seriesDeleteData);

            if (movimientoActualModulo == "Ajuste de Inventario") {
                keysIdentificadorDelete.forEach((element) => {
                    articulosSerieQuitar = {
                        ...articulosSerieQuitar,
                        [element]: {
                            serie: seriesDeleteData[element],
                            ids: idsSeriesDeleteData[element],
                        },
                    };
                });
            }

            if (
                movimientoActualModulo == "Transferencia entre Alm." ||
                movimientoActualModulo == "Tránsito" ||
                movimientoActualModulo == "Salida por Traspaso" ||
                movimientoActualModulo == "Entrada por Traspaso"
            ) {
                keysIdentificadorDelete.forEach((element) => {
                    articulosSerieTrans = {
                        ...articulosSerieTrans,
                        [element]: {
                            serie: seriesDeleteData[element],
                            ids: idsSeriesDeleteData[element],
                        },
                    };
                });
            }
        }

        console.log(articulosSerie, articulosSerieQuitar, articulosSerieTrans);
    }

    const tableProveedores = jQuery("#shTable2").DataTable({
        select: {
            style: "single",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    $("#tooltip").tooltip();

    //Evento select para cambiar el value de los inputs del proveedor
    tableProveedores.on("select", function (e, dt, type, indexex) {
        const rowData = tableProveedores.row(indexex).data();
        $("#proveedorKey").val(rowData[0]);
        $("#proveedorName").val(rowData[1]);

        getProveedorByClave(rowData[0]);
    });

    //Tabla de almacenes
    const tablaAlmacenes = jQuery("#shTable4").DataTable({
        select: {
            style: "single",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    const tablaAlmacenesDestino = jQuery("#shTable9").DataTable({
        select: {
            style: "single",
        },
        columnDefs: [
            {
                targets: [3],
                visible: false,
            },
        ],
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    //Tabla articulos
    const tablaArticulos = jQuery("#shTable5").DataTable({
        select: {
            style: "multi",
        },
        columnDefs: [
            {
                targets: [3, 4, 5, 6],
                visible: false,
            },
        ],

        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    const selectedMovimiento = $('#select-movimiento').val();

    $('#select-movimiento').on('change', function() {
        var selectedMovimiento = $(this).val();
        // Lógica para filtrar los conceptos según el movimiento seleccionado
        if (selectedMovimiento === 'Tránsito') {
            $.ajax({
                url: '/api/inventarios/getConceptosByMovimiento',
                method: 'GET',
                data: { movimiento: null },
                // dataType: 'json',
                success: function (data) {
                    console.log(data);
                    var selectConceptos = $('#select-moduleConcept');
                    selectConceptos.empty();
                    selectConceptos.append($('<option>', {
                        value: '',
                        text: 'Selecciona uno...'
                    }));
                    $.each(data, function (key, value) {
                        selectConceptos.append($('<option>', {
                            value: value.moduleConcept_name,
                            text: value.moduleConcept_name
                        }));
                    });
                }
            });
        } else {
            $.ajax({
                url: '/api/inventarios/getConceptosByMovimiento',
                method: 'GET',
                data: { movimiento: selectedMovimiento },
                // dataType: 'json',
                success: function (data) {
                    console.log(data);
                    var selectConceptos = $('#select-moduleConcept');
                    var conceptoValue = selectConceptos.val(); // Obtener el valor del concepto actualmente seleccionado
                    selectConceptos.empty();
                    selectConceptos.append($('<option>', { 
                        value: '',
                        text: 'Selecciona uno...'
                    }));
                    $.each(data, function(key, value) {
                        selectConceptos.append($('<option>', { 
                            value: value.moduleConcept_name,
                            text: value.moduleConcept_name,
                            selected: (value.moduleConcept_name === conceptoValue) // Establecer como seleccionado si coincide con el concepto actual
                        }));
                    });
                }
            });
        }
        
    });

    if ( selectedMovimiento === "Tránsito") {
        $.ajax({
            url: '/api/inventarios/getConceptosByMovimiento',
            method: 'GET',
            data: { movimiento: null },
            // dataType: 'json',
            success: function (data) {
                console.log(data);
                var selectConceptos = $('#select-moduleConcept');
                var conceptoValue = selectConceptos.val(); // Obtener el valor del concepto actualmente seleccionado
                selectConceptos.empty();
                selectConceptos.append($('<option>', { 
                    value: '',
                    text: 'Selecciona uno...'
                }));
                $.each(data, function(key, value) {
                    selectConceptos.append($('<option>', { 
                        value: value.moduleConcept_name,
                        text: value.moduleConcept_name,
                        selected: (value.moduleConcept_name === conceptoValue) // Establecer como seleccionado si coincide con el concepto actual
                    }));
                });
            }
        });
    } else {
        $.ajax({
            url: '/api/inventarios/getConceptosByMovimiento',
            method: 'GET',
            data: { movimiento: selectedMovimiento },
            // dataType: 'json',
            success: function (data) {
                console.log(data);
                var selectConceptos = $('#select-moduleConcept');
                var conceptoValue = selectConceptos.val(); // Obtener el valor del concepto actualmente seleccionado
                selectConceptos.empty();
                selectConceptos.append($('<option>', { 
                    value: '',
                    text: 'Selecciona uno...'
                }));
                $.each(data, function(key, value) {
                    selectConceptos.append($('<option>', { 
                        value: value.moduleConcept_name,
                        text: value.moduleConcept_name,
                        selected: (value.moduleConcept_name === conceptoValue) // Establecer como seleccionado si coincide con el concepto actual
                    }));
                });
            }
        });
    }

    //Evento select para cambiar el value de los inputs del proveedor
    tablaAlmacenes.on("select", function (e, dt, type, indexex) {
        const rowData = tablaAlmacenes.row(indexex).data();
        $("#almacenKey").val(rowData[0]);
        $("#almacenTipoKey").val(rowData[2]);

        $("#almacenKey").change();
        $("#almacenKey").trigger("keyup");
    });

    tablaAlmacenesDestino.on("select", function (e, dt, type, indexex) {
        const rowData = tablaAlmacenesDestino.row(indexex).data();
        $("#almacenDestinoKey").val(rowData[0]);
        $("#almacenTipoDestinoKey").val(rowData[3]);

        $("#almacenDestinoKey").trigger("keyup");
    });

    const leyendas = {
        'Ajuste de Inventario': 'Este proceso permite realizar afectaciones al inventario en positivo(aumentando) o negativo (disminuyendo).',
        'Transferencia entre Alm.': 'Realiza traspasos internos entre almacenes de la misma sucursal.',
        'Salida por Traspaso': 'Este proceso, envía inventario a los almacenes de tus sucursales.',
        'Entrada por Traspaso': 'Realiza la recepción del inventario a tu almacén.',
    };

    // Función para mostrar la leyenda correspondiente
    function mostrarLeyenda(movimiento) {
        const leyenda = leyendas[movimiento];
        if (leyenda) {
            $('#leyenda').text(leyenda);
            $('#leyenda-container').show();
        } else {
            $('#leyenda-container').hide();
        }
    }

    // Evento para actualizar la leyenda al cambiar la opción seleccionada
    $('#select-movimiento').on('change', function () {
        const selectedMovimiento = $(this).val();
        mostrarLeyenda(selectedMovimiento);
    });

    // Mostrar la leyenda inicial al cargar la página si hay una opción seleccionada
    mostrarLeyenda(selectedMovimiento);


    jQuery("#agregarArticulos").on("click", async function () {
        const rowData = tablaArticulos.rows(".selected").data();
        // console.log(rowData);

        let datos = [];
        let claves = Object.keys(rowData);
        for (let i = 0; i < claves.length; i++) {
            if (!isNaN(claves[i])) {
                datos.push(rowData[claves[i]]);
            }
        }

        if (datos.length > 0) {
            $(".keyArticulo").each(function () {
                let iskeyVacio = $(this).attr("id").split("-").length === 2;
                if (iskeyVacio) {
                    $(this).parent().parent().remove();
                }
            });

            $("#controlArticulo").hide();
            $("#controlArticulo2").hide();
            let tipoMov = $("#select-movimiento").val();
            let estatus = jQuery("#status").text().trim();
            let almacen = $("#almacenKey").val();

            $("tooltip").tooltip({
                content: "Descripción",
            });

            for (let i = 0; i < datos.length; i++) {
                let articleKey = datos[i][0];
                let articleName = datos[i][1];
                let articleIva = datos[i][2];
                let articleUnidad = datos[i][4];
                let articleUnidadTraspaso = datos[i][6];
                let tipo = datos[i][5];

                // console.log(datos, articleUnidadTraspaso, articleUnidad);
                if ($(".td-aplica").is(":visible")) {
                    $("#articleItem").append(`
                       <tr id="${articleKey}-${result}">
                       <td class=""></td>
                       <td id="btnInput"><input name="dataArticulos[]" id="keyArticulo-${articleKey}-${result}" type="text" class="keyArticulo" value='${articleKey}' onchange="buscadorArticulos('keyArticulo-${articleKey}-${result}')" readonly>
               
                       <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal3">...</button>
                       ${
                           tipo === "Serie" &&
                           tipoMov == "Ajuste de Inventario" &&
                           estatus == "INICIAL"
                               ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal6" id="modalSerie">S</button>'
                               : ""
                       }

                        ${
                            (tipo === "Serie" &&
                                tipoMov == "Transferencia entre Alm." &&
                                estatus == "INICIAL") ||
                            (tipo === "Serie" &&
                                tipoMov == "Salida por Traspaso" &&
                                estatus == "INICIAL")
                                ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal7" id="modalSerie2">S</button>'
                                : ""
                        }
               
                       </td>
                       <td><input name="dataArticulos[]" id="desp-${articleKey}-${result}" type="text" class="botonesArticulos" value='${articleName}' readonly title="${articleName}"></td>
                       <td>
                               <input name="dataArticulos[]" id="canti-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value=''>
                       </td>
                       <td class="costoArticulo" ${
                           tipoMov == "Ajuste de Inventario"
                               ? 'style="display: "'
                               : 'style="display: none"'
                       }><input name="dataArticulos[]" id="c_unitario-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='' onchange="calcularImporte('${articleKey}', '${result}')"></td>
                       <td>
                           <select name="dataArticulos[]" id="unid-${articleKey}-${result}" class="botonesArticulos" value='${
                        tipoMov != "Salida por Traspaso"
                            ? articleUnidad
                            : articleUnidadTraspaso
                    }' onchange="recalcularCantidadInventario('${articleKey}', '${result}')" >
                           </select>
                       </td>
                       <td><input name="dataArticulos[]" id="c_Inventario-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
              
                       <td class="totalArticulo" ${
                           tipoMov == "Ajuste de Inventario"
                               ? 'style="display: "'
                               : 'style="display: none"'
                       }><input name="dataArticulos[]" id="importe_total-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                       <td
                               style="display: flex; justify-content: center; align-items: center" class='eliminacion-articulo'>
                               <i class="fa fa-trash-o"  onclick="eliminarArticulo('${articleKey}', '${result}')" aria-hidden="true"
                                   style="color: red; font-size: 25px; cursor: pointer;"></i>
                       </td>
                       <td style="display: none">
                           <input name="dataArticulos[]" id="tipoArticulo-${articleKey}-${result}" type="hidden" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value='${tipo}'>
                       </td>
                        <td style="display: none">
                            <input id="decimales-${articleKey}-${result}" type="text" value="" readonly>
                        </td>
                       </tr>
               `);
                } else {
                    $("#articleItem").append(`
                        <tr id="${articleKey}-${result}">
                        <td id="btnInput"><input name="dataArticulos[]" id="keyArticulo-${articleKey}-${result}" type="text" class="keyArticulo" value='${articleKey}' onchange="buscadorArticulos('keyArticulo-${articleKey}-${result}')">
                
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal3">...</button>
                        ${
                            tipo === "Serie" &&
                            tipoMov == "Ajuste de Inventario" &&
                            estatus == "INICIAL"
                                ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal6" id="modalSerie">S</button>'
                                : ""
                        }

                        ${
                            (tipo === "Serie" &&
                                tipoMov == "Transferencia entre Alm." &&
                                estatus == "INICIAL") ||
                            (tipo === "Serie" &&
                                tipoMov == "Salida por Traspaso" &&
                                estatus == "INICIAL")
                                ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal7" id="modalSerie2">S</button>'
                                : ""
                        }
                        </td>
                        <td><input name="dataArticulos[]" id="desp-${articleKey}-${result}" type="text" class="botonesArticulos" value='${articleName}' readonly title="${articleName}"></td>
                        <td>
                                <input name="dataArticulos[]" id="canti-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value=''>
                        </td>
                        <td class="costoArticulo" ${
                            tipoMov == "Ajuste de Inventario"
                                ? 'style="display: "'
                                : 'style="display: none"'
                        }><input name="dataArticulos[]" id="c_unitario-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='' onchange="calcularImporte('${articleKey}', '${result}')"></td>
                        <td>
                            <select name="dataArticulos[]" id="unid-${articleKey}-${result}" class="botonesArticulos" value='${
                        tipoMov != "Salida por Traspaso"
                            ? articleUnidad
                            : articleUnidadTraspaso
                    }' onchange="recalcularCantidadInventario('${articleKey}', '${result}')" >
                            </select>
                        </td>
                        <td><input name="dataArticulos[]" id="c_Inventario-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>

                        <td class="totalArticulo" ${
                            tipoMov == "Ajuste de Inventario"
                                ? 'style="display: "'
                                : 'style="display: none"'
                        }><input name="dataArticulos[]" id="importe_total-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                        <td
                                style="display: flex; justify-content: center; align-items: center" class='eliminacion-articulo'>
                                <i class="fa fa-trash-o"  onclick="eliminarArticulo('${articleKey}', '${result}')" aria-hidden="true"
                                    style="color: red; font-size: 25px; cursor: pointer;"></i>
                        </td>
                             <td style="display: none">
                                   <input name="dataArticulos[]" id="tipoArticulo-${articleKey}-${result}" type="hidden" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value='${tipo}'>
                               </td>
                            <td style="display: none">
                                <input id="decimales-${articleKey}-${result}" type="text" value="" readonly>
                            </td>
                        </tr>
                `);
                }

                await $.ajax({
                    url: "/comercial/ventas/api/getCosto",
                    type: "GET",
                    data: {
                        articulo: articleKey,
                        almacen: almacen,
                    },
                    success: function (data) {
                        if (data != null) {
                            let costoFormato = currency(
                                data.articlesCost_averageCost,
                                {
                                    separator: ",",
                                    precision: 2,
                                    symbol: "",
                                }
                            ).format();

                            $("#c_unitario-" + articleKey + "-" + result).val(
                                costoFormato
                            );

                            //agregar el atributo data para el costo unitario al input
                            $("#c_unitario-" + articleKey + "-" + result).attr(
                                "data",
                                costoFormato
                            );
                        }
                    },
                });

                const selectOptions = $("#unid-" + articleKey + "-" + result); //Obtenemos el select para añadirle las multiunidades correspondientes

                await $.ajax({
                    url: "/logistica/compras/api/getMultiUnidad",
                    type: "GET",
                    data: {
                        factorUnidad: articleKey,
                    },
                    success: function (data) {
                        let unidadPorDefecto =
                            tipoMov != "Salida por Traspaso"
                                ? articleUnidad
                                : articleUnidadTraspaso;
                        let unidadPorDefectoIndex = {};
                        data.forEach((element) => {
                            if (
                                element.articlesUnits_unit == unidadPorDefecto
                            ) {
                                unidadPorDefectoIndex = element;
                            }

                            selectOptions.append(`
                                <option value="${element.articlesUnits_unit}-${element.articlesUnits_factor}">${element.articlesUnits_unit}-${element.articlesUnits_factor}</option>
                                `);
                        });

                        if (Object.keys(unidadPorDefectoIndex).length > 0) {
                            $("#unid-" + articleKey + "-" + result).val(
                                unidadPorDefecto +
                                    "-" +
                                    unidadPorDefectoIndex.articlesUnits_factor
                            );
                        }
                        selectOptions.change();
                        result++;
                    },
                });

                contadorArticulos++;
            }

            $("#shTable5").DataTable().rows(".selected").deselect();

            jQuery("#cantidadArticulos").val(contadorArticulos);
        }
    });

    jQuery("#select-proveedorCondicionPago").on("change", function () {
        tipoCondicionPago();
    });

    //evento change para cambiar el valule del tipo de cambio

    jQuery("#select-moneda").on("change", function (e) {
        $.ajax({
            url: "/getTipoCambio",
            type: "GET",
            data: {
                tipoCambio: jQuery("#select-moneda").val(),
            },
            success: function (data) {
                $("#nameTipoCambio").val(parseFloat(data.money_change));
            },
        });
    });

    const $select = jQuery(
        "#select-movimiento, #select-moduleConcept, #select-listaPrecios, #select-proveedorCondicionPago, #select-moneda"
    ).select2({
        minimumResultsForSearch: -1,
    });

    jQuery("#select-moneda").select2();

    $("#proveedorModal").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#almacenesModal").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#articulosModal").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#modalCancelar").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#modalSerie").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#ModalFlujo").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#ModalFlujoSelect").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#almacenesModal").on("show.bs.modal", function (e) {
        let isValid = validateMovimiento();

        if (isValid) {
            showMessage("No se ha seleccionado el tipo de movimiento", "error");
            return false;
        }
    });

    $("#almacenesDestinoModal").on("show.bs.modal", function (e) {
        let isValid = validateMovimiento();

        if (isValid) {
            showMessage("No se ha seleccionado el tipo de movimiento", "error");
            return false;
        }

        let movimiento = $("#select-movimiento").val();

        $.ajax({
            url: "/getAlmacenesDestino",
            type: "GET",
            data: {
                movimiento: movimiento,
            },
            success: function ({ data, estatus }) {
                if (estatus == 200) {
                    //limpiamos la tabla
                    $("#shTable9").DataTable().clear().draw();
                    //recorremos los datos
                    data.forEach((element) => {
                        $("#shTable9")
                            .DataTable()
                            .row.add([
                                element.depots_key,
                                element.depots_name,
                                element.branchOffices_name,
                                element.depots_type,
                            ])
                            .draw();
                    });
                }
            },
        });
    });

    //vamos a cambiar el label de almacenDestino dependiendo del movimiento
    $("#select-movimiento").on("change", function () {
        let movimiento = $("#select-movimiento").val();

        if (movimiento == "Salida por Traspaso") {
            $("#almacenDestino").text("Almacén al que se envía");
        } else {
            $("#almacenDestino").text("Almacén Destino");
        }
    });


    jQuery("#select-movimiento").change(function () {
        let mov = jQuery(this).val();

        if (mov = "Ajuste de Inventario") {
            jQuery(".tablaInventario").text("Total Ajustado");
        } else {
            //ponemos que el th diga "Costo Unitario"
            jQuery(".tablaInventario").text("Importe Total");
        }
    });

    $("#articulosModal").on("show.bs.modal", function (e) {
        let isValid = validateMovimiento();

        if (isValid) {
            showMessage("No se ha seleccionado el tipo de movimiento", "error");
            return false;
        }
        const isInvalid2 = validateAlmacenArticulo();
        const isInvalid3 = validateCantidad();
        const isInvalid4 = validateImporte();

        if (isInvalid2) {
            showMessage(
                "No se ha seleccionado el almacén en el movimiento",
                "error"
            );
        }
        if (isInvalid3) {
            showMessage("Por favor ingresa la cantidad del artículo", "error");
        }
        if (isInvalid4) {
            showMessage("Por favor ingresa el importe del artículo", "error");
        }
        if (isInvalid2 || isInvalid3 || isInvalid4) {
            return false;
        }
    });

    var negativo = false;

    $("form").keypress(function (e) {
        if (e.which == 13) {
            return false;
        }
    });

    const $validator = jQuery("#progressWizard").validate({
        submitHandler: function (form) {
            let isCantidadVacia = validateCantidad();
            let isImporteVacia = validateImporte();

            if (isCantidadVacia) {
                $valid = false;
                showMessage(
                    "Por favor ingresa la cantidad del artículo",
                    "error"
                );
                return false;
            }

            $("#totalCompleto").val($("#totalCompleto").attr("data"));

            jsonArticulos();
            form.submit();
        },
        rules: {
            movimientos: {
                required: true,
                maxlength: 100,
            },
            nameMoneda: {
                required: true,
                maxlength: 50,
            },
            nameTipoCambio: {
                required: true,
            },
            concepto: {
                required: true,
                maxlength: 50,
            },
            proveedorKey: {
                required: true,
                maxlength: 50,
            },
            proveedorCondicionPago: {
                required: true,
                maxlength: 50,
            },
            almacenKey: {
                required: true,
                maxlength: 50,
            },
            almacenDestinoKey: {
                required: true,
                maxlength: 50,
            },
            proveedorReferencia: {
                maxlength: 100,
            },
        },
        messages: {
            movimientos: {
                required: "Por favor llena este campo",
                maxlength: jQuery.validator.format("Maximo de {0} caracteres"),
            },
            nameMoneda: {
                required: "Por favor llena este campo",
                maxlength: jQuery.validator.format("Maximo de {0} caracteres"),
            },
            nameTipoCambio: {
                required: "Por favor llena este campo",
                maxlength: jQuery.validator.format("Maximo de {0} caracteres"),
            },
            concepto: {
                required: "Por favor llena este campo",
                maxlength: jQuery.validator.format("Maximo de {0} caracteres"),
            },
            proveedorKey: {
                required: "Por favor llena este campo",
                maxlength: jQuery.validator.format("Maximo de {0} caracteres"),
            },
            proveedorCondicionPago: {
                required: "Por favor llena este campo",
                maxlength: jQuery.validator.format("Maximo de {0} caracteres"),
            },
            almacenKey: {
                required: "Por favor llena este campo",
                maxlength: jQuery.validator.format("Maximo de {0} caracteres"),
            },
            almacenDestinoKey: {
                required: "Por favor llena este campo",
                maxlength: jQuery.validator.format("Maximo de {0} caracteres"),
            },
            proveedorReferencia: {
                maxlength: jQuery.validator.format("Maximo de {0} caracteres"),
            },
        },
        highlight: function (element) {
            jQuery(element).closest(".form-group").addClass("has-error");
        },
        unhighlight: function (element) {
            jQuery(element).closest(".form-group").removeClass("has-error");
        },
        success: function (element) {
            jQuery(element).closest(".form-group").removeClass("has-error");
        },
    });

    $select.rules("add", {
        required: true,
        messages: {
            required: "Este campo es requerido",
        },
    });

    $select.on("change", function () {
        $(this).trigger("blur");
    });

    // Progress Wizard
    jQuery("#progressWizard").bootstrapWizard({
        onTabShow: function (tab, navigation, index) {
            tab.prevAll().addClass("done");
            tab.nextAll().removeClass("done");
            tab.removeClass("done");

            var $total = navigation.find("li").length;
            var $current = index + 1;

            if ($current >= $total) {
                $("#progressWizard").find(".wizard .next").addClass("hide");
                $("#progressWizard")
                    .find(".wizard .finish")
                    .removeClass("hide");
            } else {
                $("#progressWizard").find(".wizard .next").removeClass("hide");
                $("#progressWizard").find(".wizard .finish").addClass("hide");
            }

            var $percent = ($current / $total) * 100;
            $("#progressWizard")
                .find(".progress-bar")
                .css("width", $percent + "%");
        },
        onTabClick: function (tab, navigation, index) {
            var $valid = jQuery("#progressWizard").valid();
            let isCantidadVacia = validateCantidad();
            let isImporteVacia = validateImporte();

            if (isCantidadVacia) {
                $valid = false;
                showMessage(
                    "Por favor ingresa la cantidad del artículo",
                    "error"
                );
            } else if (isImporteVacia) {
                $valid = false;
                showMessage(
                    "Por favor ingresa el importe del artículo",
                    "error"
                );
            }

            if (!$valid) {
                $validator.focusInvalid();
                return false;
            }
            return true;
        },
        onNext: function (tab, navigation, index) {
            var $valid = jQuery("#progressWizard").valid();
            let isCantidadVacia = validateCantidad();
            let isImporteVacia = validateImporte();

            if (isCantidadVacia) {
                $valid = false;
                showMessage(
                    "Por favor ingresa la cantidad del artículo",
                    "error"
                );
            } else if (isImporteVacia) {
                $valid = false;
                showMessage(
                    "Por favor ingresa el importe del artículo",
                    "error"
                );
            }

            if (!$valid) {
                $validator.focusInvalid();
                return false;
            }
        },
    });

    jQuery("#timepicker").timepicker({
        defaultTIme: false,
    });
    jQuery("#datepickerInicia").datepicker();
    jQuery("#timepicker2").timepicker({
        defaultTIme: false,
    });
    jQuery("#datepickerInicia2").datepicker();

    //Luis - Validaciones de compras
    function validateMovimiento() {
        const estadoConcepto =
            $("#select-movimiento").val() === "" ? true : false;

        if (estadoConcepto) {
            return true;
        } else {
            return false;
        }
    }

    function validateConceptoProveedor() {
        const estadoConcepto =
            $("#select-moduleConcept").val() === "" ? true : false;

        if (estadoConcepto) {
            return true;
        } else {
            return false;
        }
    }

    function validateProveedorArticulo() {
        const estadoProveedor = $("#proveedorKey").val() === "" ? true : false;

        if (estadoProveedor) {
            return true;
        } else {
            return false;
        }
    }

    $("#almacenKey").on("change", function () {
        const isValid = validateAlmacenArticulo();

        if (!isValid) {
            let key = $(this).val() !== " " ? $(this).val() : false;

            if (key) {
                getAlmacenByClave(key);
            } else {
                $("#almacenKey").val("");
            }
        }
    });

    $("#almacenDestinoKey").on("change", function () {
        const isValid = validateAlmacenDestino();

        if (!isValid) {
            let key = $(this).val() !== " " ? $(this).val() : false;

            if (key) {
                getAlmacenDestinoByClave(key);
            } else {
                $("#almacenDestinoKey").val("");
            }
        }
    });

    //funcion para validar el almacen
    function validateAlmacenArticulo() {
        const estadoAlmacen = $("#almacenKey").val() === "" ? true : false;

        if (estadoAlmacen) {
            return true;
        } else {
            return false;
        }
    }

    function showMessage(mensaje, icon) {
        swal(mensaje, {
            button: "OK",
            icon: icon,
        });
    }

    function showMessage2(titulo, mensaje, icon) {
        swal({
            title: titulo,
            text: mensaje,
            icon: icon,
        });
    }

    function showMessage3(titulo, mensaje, icon, clave_posicion_articulo) {
        swal({
            title: titulo,
            text: mensaje,
            icon: icon,
        }).then((result) => {
            if (result) {
                $("#montoRecibir-" + clave_posicion_articulo).focus();
            }
        });
    }

    const afectar = function (e) {
        e.preventDefault();
        let status = $("#status").text().trim();
        let articulos = $("#cantidadArticulos").val();
        let movimiento = $("#select-movimiento").val();

        const genero = obtenerGeneroDelMovimiento();
        let articulo = "la";
        if (genero === "F") {
            articulo = "el";
        }

        let validarMov = validateMovimiento();
        if (validarMov) {
            showMessage("Por favor selecciona un movimiento", "error");
            return false;
        }
        const isInvalid = validateCantidad();
        const concepto = validateConceptoProveedor();

        let isInvalid2 = false;
        if (movimiento === "Ajuste de Inventario") {
            isInvalid2 = validateImporte();
        }
        let isInvalid3 = false;
        let isInvalid4 = false;
        if (movimiento != "Ajuste de Inventario") {
            isInvalid3 = validateAlmacenDestino();
            isInvalid4 = validateCantidadNegativa();
        }
        const tdAplica = $(".td-aplica").is(":visible");

        if (isInvalid4) {
            showMessage(
                "No se puede afectar un movimiento con cantidades negativas",
                "error"
            );
            return;
        }

        if (status === "INICIAL") {
            if (articulos > 0) {
                if (isInvalid || isInvalid2) {
                    showMessage(
                        "La cantidad o el costo no pueden ser menores a cero",
                        "error"
                    );
                } else {
                    if (
                        movimiento == "Tránsito" ||
                        (movimiento == "Entrada por Traspaso" && tdAplica == false)
                    ) {
                        showMessage2(
                            "Precaución",
                            "No se puede afectar directamente",
                            "warning"
                        );
                    } else {
                        if (isInvalid3) {
                            showMessage(
                                "Por favor selecciona un almacen destino",
                                "error"
                            );
                        } else if (concepto) {
                            showMessage(
                                "Por favor selecciona un concepto",
                                "error"
                            );
                        } else {
                            swal({
                                title: "¿Está seguro que desea generar " + articulo + " " + movimiento + "?",
                                text: "¡Con el nuevo estatus diferente a INICIAL no podrá realizar cambios!",
                                icon: "warning",
                                buttons: true,
                                dangerMode: true,
                                buttons: ["Cancelar", "Aceptar"],
                            }).then((willDelete) => {
                                if (willDelete) {
                                    let listaArticulos = jsonArticulos();
                                    console.log(listaArticulos);
                                    $("#loader").show();

                                    if (
                                        jQuery("#select-movimiento").val() ==
                                            "Ajuste de Inventario" &&
                                        status == "INICIAL"
                                    ) {
                                        let isSerieVacioNuevas = false;
                                        let isSerieRepetidoNuevas = false;
                                        let isSerieVacioEliminar = false;
                                        let isSerieRepetidoEliminar = false;

                                        const keyListaArticulos =
                                            Object.keys(listaArticulos);
                                        // console.log(negativoActivo[0], negativoActivo[1]);

                                        let claveArticuloSerie = "";
                                        let repetidos = {};

                                        keyListaArticulos.forEach(
                                            (keyArticulo) => {
                                                if (
                                                    listaArticulos[keyArticulo]
                                                        .tipoArticulo == "Serie"
                                                ) {
                                                    let isNegativo =
                                                        listaArticulos[
                                                            keyArticulo
                                                        ].cantidad.charAt(0) ==
                                                        "-";

                                                    if (!isNegativo) {
                                                        if (
                                                            listaArticulos[
                                                                keyArticulo
                                                            ]
                                                                .asignacionSerie ===
                                                                undefined ||
                                                            listaArticulos[
                                                                keyArticulo
                                                            ]
                                                                .asignacionSerie ===
                                                                null
                                                        ) {
                                                            isSerieVacioNuevas = true;
                                                            isSerieVacioEliminar = false;
                                                            claveArticuloSerie =
                                                                keyArticulo;
                                                            return false;
                                                        }
                                                    } else {
                                                        if (
                                                            listaArticulos[
                                                                keyArticulo
                                                            ].eliminarSerie ===
                                                                undefined ||
                                                            listaArticulos[
                                                                keyArticulo
                                                            ].eliminarSerie ===
                                                                null
                                                        ) {
                                                            isSerieVacioEliminar = true;
                                                            isSerieVacioNuevas = false;
                                                            claveArticuloSerie =
                                                                keyArticulo;
                                                            return false;
                                                        }
                                                    }
                                                }
                                            }
                                        );

                                        if (
                                            isSerieVacioNuevas &&
                                            !isSerieVacioEliminar
                                        ) {
                                            showMessage2(
                                                "Artículo Serie",
                                                `Por favor, verifique que el artículo con clave ${
                                                    claveArticuloSerie.split(
                                                        "-"
                                                    )[0]
                                                } tenga completo sus números de serie`,
                                                "warning"
                                            );
                                            $("#loader").hide();
                                        } else {
                                            if (!isSerieVacioNuevas) {
                                                keyListaArticulos.forEach(
                                                    (keyArticulo) => {
                                                        if (
                                                            listaArticulos[
                                                                keyArticulo
                                                            ].tipoArticulo ==
                                                            "Serie"
                                                        ) {
                                                            listaArticulos[
                                                                keyArticulo
                                                            ][
                                                                "asignacionSerie"
                                                            ]?.forEach(
                                                                (serie) => {
                                                                    repetidos[
                                                                        serie
                                                                    ] =
                                                                        (repetidos[
                                                                            serie
                                                                        ] ||
                                                                            0) +
                                                                        1;
                                                                }
                                                            );
                                                        }
                                                    }
                                                );

                                                let keySeries =
                                                    Object.keys(repetidos);

                                                keySeries.forEach(
                                                    (keySerie) => {
                                                        if (
                                                            repetidos[
                                                                keySerie
                                                            ] !== 1
                                                        ) {
                                                            showMessage(
                                                                "Por favor, ingresa diferentes series",
                                                                "error"
                                                            );
                                                            $("#loader").hide();
                                                            isSerieRepetidoNuevas = true;
                                                            return false;
                                                        }
                                                    }
                                                );
                                            }
                                        }

                                        if (!isSerieRepetidoNuevas) {
                                            if (
                                                isSerieVacioEliminar &&
                                                !isSerieVacioNuevas
                                            ) {
                                                showMessage2(
                                                    "Artículo Serie",
                                                    `Por favor, verifique que el artículo con clave ${
                                                        claveArticuloSerie.split(
                                                            "-"
                                                        )[0]
                                                    } tenga completo sus números de serie a eliminar`,
                                                    "warning"
                                                );
                                                $("#loader").hide();
                                            } else {
                                                if (!isSerieVacioEliminar) {
                                                    keyListaArticulos.forEach(
                                                        (keyArticulo) => {
                                                            if (
                                                                listaArticulos[
                                                                    keyArticulo
                                                                ]
                                                                    .tipoArticulo ==
                                                                "Serie"
                                                            ) {
                                                                listaArticulos[
                                                                    keyArticulo
                                                                ][
                                                                    "eliminarSerie"
                                                                ]?.forEach(
                                                                    (serie) => {
                                                                        repetidos[
                                                                            serie
                                                                        ] =
                                                                            (repetidos[
                                                                                serie
                                                                            ] ||
                                                                                0) +
                                                                            1;
                                                                    }
                                                                );
                                                            }
                                                        }
                                                    );

                                                    let keySeries =
                                                        Object.keys(repetidos);

                                                    keySeries.forEach(
                                                        (keySerie) => {
                                                            if (
                                                                repetidos[
                                                                    keySerie
                                                                ] !== 1
                                                            ) {
                                                                showMessage(
                                                                    "Por favor, ingresa diferentes series para eliminar",
                                                                    "error"
                                                                );
                                                                $(
                                                                    "#loader"
                                                                ).hide();
                                                                isSerieRepetidoEliminar = true;
                                                                return false;
                                                            }
                                                        }
                                                    );
                                                }
                                            }
                                        }

                                        if (
                                            !isSerieVacioNuevas &&
                                            !isSerieRepetidoNuevas &&
                                            !isSerieVacioEliminar &&
                                            !isSerieRepetidoEliminar
                                        ) {
                                            $.ajax({
                                                url: "/logistica/inventarios/afectar",
                                                type: "POST",
                                                data: $(
                                                    "#progressWizard"
                                                ).serialize(),
                                                success: function ({
                                                    mensaje,
                                                    estatus,
                                                    id,
                                                    transito,
                                                }) {
                                                    $("#loader").hide();
                                                    if (estatus === 200) {
                                                        if (transito) {
                                                            showMessage2(
                                                                "Se generó automaticamente.",
                                                                "Proceso: " +
                                                                    transito.inventories_movement +
                                                                    " " +
                                                                    transito.inventories_movementID,
                                                                "info"
                                                            );
                                                        } else {
                                                            showMessage2(
                                                                "Afectación exitosa",
                                                                mensaje,
                                                                "success"
                                                            );
                                                        }
                                                        let ruta =
                                                            window.location
                                                                .href;
                                                        let ruta2 =
                                                            ruta.split("/");
                                                        if (ruta2.length > 5) {
                                                            ruta + "/" + id;
                                                        } else {
                                                            ruta += "/" + id;
                                                        }
                                                        setTimeout(function () {
                                                            window.location.href =
                                                                ruta;
                                                        }, 1000);
                                                    } else {
                                                        showMessage2(
                                                            "Error",
                                                            mensaje,
                                                            "error"
                                                        );
                                                    }

                                                    if (estatus === 400) {
                                                        showMessage2(
                                                            "Precaución",
                                                            mensaje,
                                                            "warning"
                                                        );
                                                    }
                                                },
                                            });
                                        }
                                    } else if (
                                        (jQuery("#select-movimiento").val() ==
                                            "Transferencia entre Alm." &&
                                            status == "INICIAL") ||
                                        (jQuery("#select-movimiento").val() ==
                                            "Salida por Traspaso" &&
                                            status == "INICIAL")
                                    ) {
                                        let isSerieVacio = false;
                                        let isSerieRepetido = false;

                                        let claveArticuloSerie = "";
                                        let repetidos = {};
                                        const keyListaArticulos =
                                            Object.keys(listaArticulos);

                                        keyListaArticulos.forEach(
                                            (keyArticulo) => {
                                                if (
                                                    listaArticulos[keyArticulo]
                                                        .tipoArticulo == "Serie"
                                                ) {
                                                    if (
                                                        listaArticulos[
                                                            keyArticulo
                                                        ].transferirSerie ===
                                                            undefined ||
                                                        listaArticulos[
                                                            keyArticulo
                                                        ].transferirSerie ===
                                                            null
                                                    ) {
                                                        isSerieVacio = true;
                                                        claveArticuloSerie =
                                                            keyArticulo;
                                                        return false;
                                                    }
                                                }
                                            }
                                        );

                                        if (isSerieVacio) {
                                            showMessage2(
                                                "Artículo Serie",
                                                `Por favor, verifique que el artículo con clave ${
                                                    claveArticuloSerie.split(
                                                        "-"
                                                    )[0]
                                                } tenga completo sus números de serie a transferir/transladar`,
                                                "warning"
                                            );
                                            $("#loader").hide();
                                        } else {
                                            keyListaArticulos.forEach(
                                                (keyArticulo) => {
                                                    if (
                                                        listaArticulos[
                                                            keyArticulo
                                                        ].tipoArticulo ==
                                                        "Serie"
                                                    ) {
                                                        listaArticulos[
                                                            keyArticulo
                                                        ][
                                                            "transferirSerie"
                                                        ].forEach((serie) => {
                                                            repetidos[serie] =
                                                                (repetidos[
                                                                    serie
                                                                ] || 0) + 1;
                                                        });
                                                    }
                                                }
                                            );

                                            let keySeries =
                                                Object.keys(repetidos);

                                            keySeries.forEach((keySerie) => {
                                                if (repetidos[keySerie] !== 1) {
                                                    showMessage(
                                                        "Por favor, ingresa diferentes series para transferir/transladar",
                                                        "error"
                                                    );
                                                    $("#loader").hide();
                                                    isSerieRepetido = true;
                                                    return false;
                                                }
                                            });
                                        }

                                        if (!isSerieVacio && !isSerieRepetido) {
                                            $.ajax({
                                                url: "/logistica/inventarios/afectar",
                                                type: "POST",
                                                data: $(
                                                    "#progressWizard"
                                                ).serialize(),
                                                success: function ({
                                                    mensaje,
                                                    estatus,
                                                    id,
                                                    transito,
                                                }) {
                                                    $("#loader").hide();
                                                    if (estatus === 200) {
                                                        if (transito) {
                                                            showMessage2(
                                                                "Se genero automaticamente.",
                                                                "Movimiento: " +
                                                                    transito.inventories_movement +
                                                                    " " +
                                                                    transito.inventories_movementID,
                                                                "info"
                                                            );
                                                        } else {
                                                            showMessage2(
                                                                "Afectación exitosa",
                                                                mensaje,
                                                                "success"
                                                            );
                                                        }
                                                        let ruta =
                                                            window.location
                                                                .href;
                                                        let ruta2 =
                                                            ruta.split("/");
                                                        if (ruta2.length > 5) {
                                                            ruta + "/" + id;
                                                        } else {
                                                            ruta += "/" + id;
                                                        }
                                                        setTimeout(function () {
                                                            window.location.href =
                                                                ruta;
                                                        }, 1000);
                                                    } else {
                                                        showMessage2(
                                                            "Error",
                                                            mensaje,
                                                            "error"
                                                        );
                                                    }

                                                    if (estatus === 400) {
                                                        showMessage2(
                                                            "Precaución",
                                                            mensaje,
                                                            "warning"
                                                        );
                                                    }
                                                },
                                            });
                                        }
                                    } else {
                                        $.ajax({
                                            url: "/logistica/inventarios/afectar",
                                            type: "POST",
                                            data: $(
                                                "#progressWizard"
                                            ).serialize(),
                                            success: function ({
                                                mensaje,
                                                estatus,
                                                id,
                                                transito,
                                            }) {
                                                $("#loader").hide();
                                                if (estatus === 200) {
                                                    if (transito) {
                                                        showMessage2(
                                                            "Se genero automaticamente.",
                                                            "Movimiento: " +
                                                                transito.inventories_movement +
                                                                " " +
                                                                transito.inventories_movementID,
                                                            "info"
                                                        );
                                                    } else {
                                                        showMessage2(
                                                            "Afectación exitosa",
                                                            mensaje,
                                                            "success"
                                                        );
                                                    }
                                                    let ruta =
                                                        window.location.href;
                                                    let ruta2 = ruta.split("/");
                                                    if (ruta2.length > 5) {
                                                        ruta + "/" + id;
                                                    } else {
                                                        ruta += "/" + id;
                                                    }
                                                    setTimeout(function () {
                                                        window.location.href =
                                                            ruta;
                                                    }, 1000);
                                                } else {
                                                    showMessage2(
                                                        "Error",
                                                        mensaje,
                                                        "error"
                                                    );
                                                }

                                                if (estatus === 400) {
                                                    showMessage2(
                                                        "Precaución",
                                                        mensaje,
                                                        "warning"
                                                    );
                                                }
                                            },
                                        });
                                    }
                                }
                            });
                        }
                    }
                }
            } else {
                showMessage("No se puede afectar sin artículos", "error");
            }
        } else if (movimiento === "Tránsito") {
            $("#modalCompra").modal({
                backdrop: "static",
                keyboard: true,
                show: true,
            });

            $("#modalCompra2").modal({
                backdrop: "static",
                keyboard: true,
                show: false,
            });
        } else {
            $("#modalCompra2").modal({
                backdrop: "static",
                keyboard: true,
                show: true,
            });

            $("#modalCompra").modal({
                backdrop: "static",
                keyboard: true,
                show: false,
            });
        }
    };

    function obtenerGeneroDelMovimiento() {
        const movimiento = $("#select-movimiento").val();

        //si el movimiento es diferente de aplicación el genero es F
        if (movimiento !== "Ajuste de Inventario") {
            return "M";
        } else {
            return "F";
        }
    }

    $("#copiar-compra").click(function (e) {
        e.preventDefault();
        //Enviamos el formulario para copiar la compra
        const form = jQuery("#progressWizard");
        const inputCopiar =
            '<input type="text" name="copiar" value="copiar" readonly>';
        form.append(inputCopiar);

        $('input[id^="id-"]').each(function (index, value) {
            $(this).remove();
        });

        $("#idCompra").val("0");

        calcularTotales();
        jsonArticulos();
        form.submit();
    });

    jQuery("#btn-modal-compra").click(function (e) {
        e.preventDefault();

        let radioEntradaCompra = jQuery("#generarReciboTraspaso").is(
            ":checked"
        );

        if (radioEntradaCompra === false) {
            showMessage("Seleccione una opción", "error");
        }

        let movimiento;
        if (radioEntradaCompra === true) {
            let listaArticulos = jsonArticulos();
            let isSerieVacio = false;
            let isSerieRepetido = false;

            let claveArticuloSerie = "";
            let repetidos = {};
            const keyListaArticulos = Object.keys(listaArticulos);

            keyListaArticulos.forEach((keyArticulo) => {
                if (listaArticulos[keyArticulo].tipoArticulo == "Serie") {
                    if (
                        listaArticulos[keyArticulo].transferirSerie ===
                            undefined ||
                        listaArticulos[keyArticulo].transferirSerie === null
                    ) {
                        isSerieVacio = true;
                        claveArticuloSerie = keyArticulo;
                        return false;
                    }
                }
            });

            if (isSerieVacio) {
                showMessage2(
                    "Artículo Serie",
                    `Por favor, verifique que el artículo con clave ${
                        claveArticuloSerie.split("-")[0]
                    } tenga completo sus números de serie a transferir/transladar`,
                    "warning"
                );
                $("#loader").hide();
            } else {
                keyListaArticulos.forEach((keyArticulo) => {
                    if (listaArticulos[keyArticulo].tipoArticulo == "Serie") {
                        listaArticulos[keyArticulo]["transferirSerie"].forEach(
                            (serie) => {
                                repetidos[serie] = (repetidos[serie] || 0) + 1;
                            }
                        );
                    }
                });

                let keySeries = Object.keys(repetidos);

                keySeries.forEach((keySerie) => {
                    if (repetidos[keySerie] !== 1) {
                        showMessage(
                            "Por favor, ingresa diferentes series para transferir/transladar",
                            "error"
                        );
                        $("#loader").hide();
                        isSerieRepetido = true;
                        return false;
                    }
                });
            }

            if (!isSerieVacio && !isSerieRepetido) {
                movimiento = "Entrada por Traspaso";
                $("#select-movimiento").val(movimiento);
                $("#idCompra").val("0");

                //Verificamos si el usuario tiene permisos para afectar
                let $domPermiso = movimiento.replace(/\s/g, "");

                if ($("#" + $domPermiso).val() !== "true") {
                    showMessage2(
                        "Permisos Insuficientes",
                        "No tienes permisos para afectar",
                        "warning"
                    );
                } else {
                    $("#modalCompra2").modal({
                        backdrop: "static",
                        keyboard: true,
                        show: true,
                    });
                }
            }

            // $('#select-movimiento').trigger('change');
        }

        $("#modalCompra").modal("hide");
    });

    jQuery("#btn-modal-compra2").click(function (e) {
        e.preventDefault();
        let isMayorCantidadRecibir = false;

        let radioCantidadIndicada = jQuery("#cantidadIndicada").is(":checked");
        let radioCantidadPendiente =
            jQuery("#cantidadPendiente").is(":checked");

        // console.log("entro", radioCantidadIndicada, radioCantidadPendiente);
        $('input[id^="id-"]').each(function (index, value) {
            $(this).remove();
        });

        if (radioCantidadIndicada === true) {
            $('input[id^="montoRecibir-"]').each(function (index, input) {
                let filaArticulo = input.id;
                let cantidadRecibir =
                    $(input).val() === "" ? 0 : parseFloat($(input).val());

                let arrayInformacionInput = filaArticulo.split("-");
                let clave_posicion_articulo =
                    arrayInformacionInput[1] + "-" + arrayInformacionInput[2];
                let cantidadPendiente = parseFloat(
                    $(`#montoPendiente-${clave_posicion_articulo}`).val()
                );

                if (cantidadPendiente < cantidadRecibir) {
                    if (cantidadRecibir > 0) {
                        isMayorCantidadRecibir = true;

                        $("#modalCompra2").modal("hide");
                        $("#modalCompra").modal("hide");

                        showMessage3(
                            "Error al afectar",
                            "La cantidad recibida es mayor a la cantidad pendiente",
                            "error",
                            clave_posicion_articulo
                        );

                        jQuery(
                            "#montoRecibir-" + clave_posicion_articulo
                        ).focus();

                        $("#montoRecibir-" + clave_posicion_articulo).focus();
                        return false;
                    }
                }
            });

            if (!isMayorCantidadRecibir) {
                $('input[id^="montoRecibir-"]').each(function (index, input) {
                    let filaArticulo = input.id;
                    let cantidadRecibir =
                        $(input).val() === "" ? 0 : parseFloat($(input).val());

                    let arrayInformacionInput = filaArticulo.split("-");
                    let clave_posicion_articulo =
                        arrayInformacionInput[1] +
                        "-" +
                        arrayInformacionInput[2];

                    if (cantidadRecibir === 0) {
                        $("#" + clave_posicion_articulo).remove();
                    } else {
                        $("#canti-" + clave_posicion_articulo).val(
                            cantidadRecibir
                        );
                        $("#unid-" + clave_posicion_articulo).trigger("change");
                        $("#c_unitario-" + clave_posicion_articulo).trigger(
                            "onchange"
                        );
                        calcularTotales();
                    }
                });
                let listaArticulos = jsonArticulos();
                console.log(listaArticulos);
                let isSerieVacio = false;
                let isSerieRepetido = false;

                let claveArticuloSerie = "";
                let repetidos = {};
                const keyListaArticulos = Object.keys(listaArticulos);

                keyListaArticulos.forEach((keyArticulo) => {
                    if (listaArticulos[keyArticulo].tipoArticulo == "Serie") {
                        if (
                            listaArticulos[keyArticulo].transferirSerie ===
                                undefined ||
                            listaArticulos[keyArticulo].transferirSerie === null
                        ) {
                            isSerieVacio = true;
                            claveArticuloSerie = keyArticulo;
                            return false;
                        }
                    }
                });

                if (isSerieVacio) {
                    showMessage2(
                        "Artículo Serie",
                        `Por favor, verifique que el artículo con clave ${
                            claveArticuloSerie.split("-")[0]
                        } tenga completo sus números de serie a transferir/transladar`,
                        "warning"
                    );
                    $("#loader").hide();
                } else {
                    keyListaArticulos.forEach((keyArticulo) => {
                        if (
                            listaArticulos[keyArticulo].tipoArticulo == "Serie"
                        ) {
                            listaArticulos[keyArticulo][
                                "transferirSerie"
                            ].forEach((serie) => {
                                repetidos[serie] = (repetidos[serie] || 0) + 1;
                            });
                        }
                    });

                    let keySeries = Object.keys(repetidos);

                    keySeries.forEach((keySerie) => {
                        if (repetidos[keySerie] !== 1) {
                            showMessage(
                                "Por favor, ingresa diferentes series para transferir/transladar",
                                "error"
                            );
                            $("#loader").hide();
                            isSerieRepetido = true;
                            return false;
                        }
                    });
                }

                if (!isSerieVacio && !isSerieRepetido) {
                    jQuery("#progressWizard").submit();
                    $("#modalCompra2").modal("hide");
                }
            }
        }

        // // console.log(articulos);
        if (radioCantidadPendiente === true) {
            $('input[id^="montoPendiente-"]').each(function (index, input) {
                let filaArticulo = input.id;

                let cantidadPendiente =
                    $(input).val() === "" ? 0 : parseFloat($(input).val());

                let arrayInformacionInput = filaArticulo.split("-");

                let clave_posicion_articulo =
                    arrayInformacionInput[1] + "-" + arrayInformacionInput[2];

                if (cantidadPendiente === 0) {
                    $("#" + clave_posicion_articulo).remove();
                } else {
                    $("#canti-" + clave_posicion_articulo).val(
                        cantidadPendiente
                    );
                    $("#unid-" + clave_posicion_articulo).trigger("change");
                    $("#c_unitario-" + clave_posicion_articulo).trigger(
                        "onchange"
                    );
                    calcularTotales();
                }

                // console.log(filaArticulo, cantidadPendiente);
            });
            let listaArticulos = jsonArticulos();
            let isSerieVacio = false;
            let isSerieRepetido = false;

            let claveArticuloSerie = "";
            let repetidos = {};
            const keyListaArticulos = Object.keys(listaArticulos);

            keyListaArticulos.forEach((keyArticulo) => {
                if (listaArticulos[keyArticulo].tipoArticulo == "Serie") {
                    if (
                        listaArticulos[keyArticulo].transferirSerie ===
                            undefined ||
                        listaArticulos[keyArticulo].transferirSerie === null
                    ) {
                        isSerieVacio = true;
                        claveArticuloSerie = keyArticulo;
                        return false;
                    }
                }
            });

            if (isSerieVacio) {
                showMessage2(
                    "Artículo Serie",
                    `Por favor, verifique que el artículo con clave ${
                        claveArticuloSerie.split("-")[0]
                    } tenga completo sus números de serie a transferir/transladar`,
                    "warning"
                );
                return false;
            } else {
                keyListaArticulos.forEach((keyArticulo) => {
                    if (listaArticulos[keyArticulo].tipoArticulo == "Serie") {
                        listaArticulos[keyArticulo]["transferirSerie"].forEach(
                            (serie) => {
                                repetidos[serie] = (repetidos[serie] || 0) + 1;
                            }
                        );
                    }
                });

                let keySeries = Object.keys(repetidos);

                keySeries.forEach((keySerie) => {
                    if (repetidos[keySerie] !== 1) {
                        showMessage(
                            "Por favor, ingresa diferentes series para transferir/transladar",
                            "error"
                        );
                        $("#loader").hide();
                        isSerieRepetido = true;
                        return false;
                    }
                });
            }

            if (!isSerieVacio && !isSerieRepetido) {
                jQuery("#progressWizard").submit();
                $("#modalCompra2").modal("hide");
            }
        }
    });

    const eliminar = function (e) {
        e.preventDefault();

        const id = $("#idCompra").val();

        if (id === "0") {
            showMessage("No se ha seleccionado ningun movimiento", "error");
        } else {
            swal({
                title: "¿Está seguro de eliminar el movimiento?",
                text: "Una vez eliminada no podrá recuperarla",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "/eliminarInventario",
                        type: "get",
                        data: { id: id },
                        success: function ({ estatus, mensaje }) {
                            if (estatus === 200) {
                                showMessage2(
                                    "Eliminacion exitosa",
                                    mensaje,
                                    "success"
                                );
                                setTimeout(function () {
                                    window.location.href =
                                        "/logistica/inventario";
                                }, 1000);
                            } else {
                                showMessage2(
                                    "Error al eliminar",
                                    mensaje,
                                    "error"
                                );
                            }

                            if (estatus === 500) {
                                showMessage2(
                                    "Error al eliminar",
                                    mensaje,
                                    "error"
                                );
                            }
                        },
                    });
                }
            });
        }
    };

    //Recalculamos los articulos al momento de pintarlos en la tabla
    calcularTotales();
    disabledCompra();

    const cancelar = function (e) {
        e.preventDefault();

        let status = $("#status").text().trim();
        let movimiento = $("#select-movimiento").val();
        const folio = $("#folioCompra").val();
        const id = $("#idCompra").val();

        if (movimiento === "Tránsito") {
            //mostrar mensaje de no se puede cancelar
            showMessage2(
                "Precaución",
                "No se puede cancelar un movimiento de tránsito directamente.",
                "warning"
            );
        }

        if (status === "FINALIZADO" && movimiento !== "Tránsito") {
            swal({
                title: "¿Está seguro de cancelar el movimiento?",
                text: movimiento + " : " + folio + "",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                if (willDelete) {
                    $("#loader").show();
                    $.ajax({
                        url: "/cancelarInventario/",
                        type: "get",
                        data: { id: id },
                        success: function ({ estatus, mensaje }) {
                            $("#loader").hide();
                            if (estatus === 200) {
                                showMessage2(
                                    "Cancelacion exitosa",
                                    mensaje,
                                    "success"
                                );
                                setTimeout(function () {
                                    window.location.href =
                                        "/logistica/inventario/" + id;
                                }, 1000);
                            } else if (estatus === 400) {
                                showMessage2("Precaución", mensaje, "warning");
                            } else {
                                showMessage2(
                                    "Error al cancelar",
                                    mensaje,
                                    "error"
                                );
                            }
                        },
                    });
                }
            });
        } else if (status === "POR AUTORIZAR" && movimiento === "Orden de Compra") {
            $("#modalCancelar").modal({
                backdrop: "static",
                keyboard: true,
                show: true,
            });
        }
    };

    jQuery("#btn-modal-cancelar").click(function (e) {
        e.preventDefault();

        const radioMovimientoCompleto = $("#cancelarMovimientoCompleto").is(
            ":checked"
        );
        const radioTodoPendiente = $("#cantidadPendienteCancelar").is(
            ":checked"
        );

        if (radioMovimientoCompleto) {
            const id = $("#idCompra").val();
            $.ajax({
                url: "/cancelarOrden/",
                type: "get",
                data: { id: id },
                success: function ({ estatus, mensaje }) {
                    if (estatus === 200) {
                        showMessage2("Cancelacion exitosa", mensaje, "success");
                        setTimeout(function () {
                            window.location.href = "/logistica/inventario";
                        }, 1000);
                    } else {
                        showMessage2("Error al cancelar", mensaje, "error");
                    }

                    if (estatus === 500) {
                        showMessage2("Error al cancelar", mensaje, "error");
                    }
                },
            });
        }

        if (radioTodoPendiente) {
            const id = $("#idCompra").val();
            swal({
                title: "¿Está seguro que desea cancelar?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "/cancelarOrdenPendiente/",
                        type: "get",
                        data: { id: id },
                        success: function ({ estatus, mensaje }) {
                            if (estatus === 200) {
                                showMessage2(
                                    "Cancelacion exitosa",
                                    mensaje,
                                    "success"
                                );
                                setTimeout(function () {
                                    window.location.href =
                                        "/logistica/inventario/" + id;
                                    calcularTotales();
                                }, 1000);
                            } else {
                                showMessage2(
                                    "Error al cancelar",
                                    mensaje,
                                    "error"
                                );
                            }
                        },
                    });
                }
            });
        }
    });

    let estatus = jQuery("#status").text().trim();
    let movimiento = jQuery("#select-movimiento").val();
    if (estatus === "FINALIZADO" || estatus === "CANCELADO") {
        // jQuery("#afectar-boton").prop("disabled", true);
        jQuery("#afectar-boton").unbind("click");
        jQuery("#afectar-boton").hide();
    } else {
        // jQuery("#afectar-boton").prop("disabled", false);
        jQuery("#afectar-boton").click(afectar);
        jQuery("#afectar-boton").show();
    }

    if (estatus === "INICIAL") {
        jQuery("#eliminar-boton").click(eliminar);
        jQuery("#eliminar-boton").show();
    } else {
        jQuery("#eliminar-boton").unbind("click");
        jQuery("#eliminar-boton").hide();
    }

    if (
        estatus === "CANCELADO" ||
        estatus === "INICIAL" ||
        (estatus === "FINALIZADO" && movimiento === "Orden de Compra")
    ) {
        jQuery("#cancelar-boton").unbind("click");
        jQuery("#cancelar-boton").hide();
    } else {
        jQuery("#cancelar-boton").click(cancelar);
        jQuery("#cancelar-boton").show();
    }

    jQuery("#select-movimiento").change(function () {
        let mov = jQuery(this).val();

        if (mov != "Ajuste de Inventario") {
            jQuery(".almacenDestinoDiv").show();
            jQuery(".totalArticulo").hide();
            jQuery(".costoArticulo").hide();
            jQuery("#totalCompleto").hide();
        } else {
            jQuery(".almacenDestinoDiv").hide();
            jQuery(".totalArticulo").show();
            jQuery(".costoArticulo").show();
            jQuery("#totalCompleto").show();
        }
    });

    //accion boton siguiente
    $("#siguiente-Flujo").click(async function (e) {
        e.preventDefault();
        const dataCompraFlujo = $("#data-info").val();
        if (dataCompraFlujo !== "") {
            $("#loadingFlujo").show();
            await $.ajax({
                url: "/modulos/flujo/api/siguiente",
                method: "GET",
                data: {
                    dataFlujo: dataCompraFlujo,
                },
                success: function ({ status, data }) {
                    $("#loadingFlujo").hide();
                    if (status === 200) {
                        if (data !== null && data.length > 0) {
                            let bodyTable = $("#movimientosTr");
                            bodyTable.html("");

                            $("#data-info").val(JSON.stringify(data[0]));
                            generarTablaFlujo(bodyTable, data);
                        }
                    }
                },
            });
        }
    });

    //accion boton anterior
    $("#anterior-Flujo").click(async function (e) {
        e.preventDefault();
        const dataCompraFlujo = $("#data-info").val();
        $("#loadingFlujo").show();
        if (dataCompraFlujo !== "") {
            await $.ajax({
                url: "/modulos/flujo/api/anterior",
                method: "GET",
                data: {
                    dataFlujo: dataCompraFlujo,
                },
                success: function ({ status, data }) {
                    $("#loadingFlujo").hide();
                    if (data !== null && data.length > 0) {
                        let bodyTable = $("#movimientosTr");
                        bodyTable.html("");
                        $("#data-info").val(JSON.stringify(data[0]));
                        generarTablaFlujo(bodyTable, data);
                    }
                },
            });
        }
    });

    $(".modal6").on("show.bs.modal", function (e) {
        const isInvalid3 = validateCantidad();
        if (isInvalid3) {
            showMessage("Por favor ingresa la cantidad del articulo", "error");
            return false;
        } else {
            let cuerpoModal = jQuery("#form-articulos-serie");
            let ruta = window.location.href;
            let ruta2 = ruta.split("/");
            let idCompra = "";

            if (ruta2.length > 5) {
                idCompra = ruta2[ruta2.length - 1];
            }

            cuerpoModal.html(""); //Limpiamos el cuerpo del modal
            let identificadorFila = $(e.relatedTarget)
                .parent()
                .parent()
                .attr("id");
            let cantidad = parseFloat($("#canti-" + identificadorFila).val());
            let idArticulo = $("#id-" + identificadorFila).val();
            let claveArticulo = identificadorFila.split("-")[0];
            let tituloClaveArticulo = jQuery(".articuloKeySerie");
            tituloClaveArticulo.text(claveArticulo); //Asignamos el valor de la clave del articulo al titulo del modal

            //Guardamos la clave y la posicion del td
            jQuery("#clavePosicion").val(identificadorFila);
            const isPropiedadExiste = articulosSerie.hasOwnProperty(
                identificadorFila
            )
                ? true
                : false;

            const isPropiedadExiste2 = articulosSerieQuitar.hasOwnProperty(
                identificadorFila
            )
                ? true
                : false;

            let contador = 1;

            if (idCompra !== "") {
                $.ajax({
                    url: "/logistica/inventarios/api/getArticulosSerie",
                    method: "GET",
                    data: {
                        idCompra: idCompra,
                        claveArticulo: identificadorFila.split("-")[0],
                        id: idArticulo,
                        limit: cantidad,
                    },
                    success: function ({ status, data }) {
                        let estado = $("#status").text();
                        // console.log(estado);
                        if (status === 200) {
                            //Agremos los inputs de acuerdo a la cantidad ingresada por el usuario
                            if (data.length > 0) {
                                data.forEach((articuloSerie) => {
                                    cuerpoModal.append(
                                        `<div class="col-md-2">
                                        <span><strong>Articulo :</strong></span>
                                    </div>
                                    <div class="col-md-10" style="margin-bottom: 5px;">
    
                                        <input type="text" class="form-control ${identificadorFila} ${
                                            articuloSerie.lotSeriesMov2_id
                                        }" id="serie" name="serie[]"
                                            placeholder="Ingrese la serie del articulo"
                                                value="${
                                                    articuloSerie.lotSeriesMov2_lotSerie
                                                }"
                                           ${
                                               estado === "FINALIZADO" ||
                                               estado === "CANCELADO"
                                                   ? "disabled"
                                                   : ""
                                           } >
                                    </div>`
                                    );
                                });

                                while (data.length < cantidad) {
                                    cuerpoModal.append(
                                        `<div class="col-md-2">
                                        <span><strong>Articulo :</strong></span>
                                    </div>
                                    <div class="col-md-10 seriesGeneradas" style="margin-bottom: 5px;">
                                        <input type="text" class="form-control ${identificadorFila}" id="serie" name="serie[]"
                                            placeholder="Ingrese la serie del articulo"
                                            value="${
                                                isPropiedadExiste
                                                    ? articulosSerie[
                                                          identificadorFila
                                                      ]["serie"][
                                                          cantidad - 1
                                                      ] === undefined
                                                        ? ""
                                                        : articulosSerie[
                                                              identificadorFila
                                                          ]["serie"][
                                                              cantidad - 1
                                                          ]
                                                    : ""
                                            }"
                                            ${
                                                estado === "FINALIZADO"
                                                    ? "disabled"
                                                    : ""
                                            }>
                                    </div>`
                                    );

                                    cantidad--;
                                }
                            } else {
                                while (contador <= cantidad) {
                                    //Agremos los inputs de acuerdo a la cantidad ingresada por el usuario
                                    cuerpoModal.append(
                                        `<div class="col-md-2">
                                        <span><strong>Articulo :</strong></span>
                                    </div>
                                    <div class="col-md-10 seriesGeneradas" style="margin-bottom: 5px;">
                                        <input type="text" class="form-control ${identificadorFila}" id="serie" name="serie[]"
                                            placeholder="Ingrese la serie del articulo"
                                            value="${
                                                isPropiedadExiste
                                                    ? articulosSerie[
                                                          identificadorFila
                                                      ]["serie"][
                                                          contador - 1
                                                      ] === undefined
                                                        ? ""
                                                        : articulosSerie[
                                                              identificadorFila
                                                          ]["serie"][
                                                              contador - 1
                                                          ]
                                                    : ""
                                            }"
                                            ${
                                                estado === "FINALIZADO"
                                                    ? "disabled"
                                                    : ""
                                            }>
                                    </div>`
                                    );

                                    contador++;
                                }
                            }
                        }
                    },
                });
            } else {
                while (contador <= cantidad) {
                    //Agremos los inputs de acuerdo a la cantidad ingresada por el usuario
                    cuerpoModal.append(
                        `<div class="col-md-2">
                        <span><strong>Articulo :</strong></span>
                    </div>
                    <div class="col-md-10 seriesGeneradas" style="margin-bottom: 5px;">
                        <input type="text" class="form-control ${identificadorFila}" id="serie" name="serie[]"
                            placeholder="Ingrese la serie del articulo"
                            value="${
                                isPropiedadExiste
                                    ? articulosSerie[identificadorFila][
                                          "serie"
                                      ][contador - 1] === undefined
                                        ? ""
                                        : articulosSerie[identificadorFila][
                                              "serie"
                                          ][contador - 1]
                                    : ""
                            }"
                            >
                    </div>`
                    );

                    contador++;
                }
            }

            let estado = $("#status").text();
            if (cantidad < 1) {
                if (estado === "INICIAL") {
                    $("#quitarSeries").show();
                    cuerpoModal.append(
                        `<div class="col-md-12" ">
                    <h5 class="text-center">Seleccionar las series, que desea quitar del almacén</h5>
                    </div>`
                    );
                } else {
                    cuerpoModal.append(
                        `<div class="col-md-12" ">
                        <h5 class="text-center">Series eliminadas del almacén</h5>
                        </div>`
                    );
                }

                let almacen = $("#almacenKey").val();
                if (idCompra !== "") {
                    $.ajax({
                        url: "/logistica/inventarios/api/getSeriesSeleccionados",
                        method: "GET",
                        data: {
                            idCompra: idCompra,
                            almacen: almacen,
                            claveArticulo: identificadorFila.split("-")[0],
                        },
                        success: function ({ status, data, data2 }) {
                            let estado = $("#status").text();
                            // console.log(data, data2);
                            //quitar el signo negativo de la cantidad
                            cantidad = cantidad * -1;

                            if (status === 200) {
                                if (data2.length > 0) {
                                    if (estado === "INICIAL") {
                                        while (contador <= cantidad) {
                                            articuloSerie =
                                                data2[contador - 1] ===
                                                undefined
                                                    ? ""
                                                    : data2[contador - 1];
                                            console.log(
                                                articuloSerie,
                                                identificadorFila,
                                                idArticulo
                                            );
                                            if (
                                                articuloSerie.delSeriesMov_articleID ==
                                                    idArticulo ||
                                                articuloSerie === ""
                                            ) {
                                                cuerpoModal.append(
                                                    //agregar el valor de la serie en el select
                                                    `<div class="col-md-2">
                                            <span><strong>Articulo :</strong></span>
                                        </div>
                                        <div class="col-md-10 " style="margin-bottom: 5px;">
                                            <select class="form-control ${identificadorFila} ${
                                                        articuloSerie.delSeriesMov_id
                                                    }" id="serie" name="serie[]" 
                                            >
                                            <option value="">Seleccione una serie</option>
                                                ${data.map(
                                                    (serie) =>
                                                        `<option value="${
                                                            serie.lotSeries_lotSerie
                                                        }" ${
                                                            articuloSerie.delSeriesMov_lotSerie ===
                                                            serie.lotSeries_lotSerie
                                                                ? "selected"
                                                                : ""
                                                        }>${
                                                            serie.lotSeries_lotSerie
                                                        }</option>`
                                                )}
                                            </select>
                                        </div>`
                                                );
                                            }
                                            contador++;
                                        }
                                    }
                                    if (estado === "FINALIZADO") {
                                        data2.forEach((articuloSerie) => {
                                            // console.log(articuloSerie, identificadorFila, idArticulo);
                                            if (
                                                articuloSerie.delSeriesMov_articleID ==
                                                    idArticulo &&
                                                articuloSerie.delSeriesMov_affected ===
                                                    "1"
                                            ) {
                                                cuerpoModal.append(
                                                    //agregar el valor de la serie en el select
                                                    `<div class="col-md-2">
                                            <span><strong>Articulo :</strong></span>
                                        </div>
                                        <div class="col-md-10 " style="margin-bottom: 5px;">
                                            <select class="form-control ${identificadorFila} ${articuloSerie.delSeriesMov_id}" id="serie" name="serie[]" 
                                            disabled>
                                            <option value="${articuloSerie.delSeriesMov_lotSerie}">${articuloSerie.delSeriesMov_lotSerie}</option>
                                            </select>
                                        </div>`
                                                );
                                            }
                                            contador++;
                                        });
                                    }

                                    if (estado === "CANCELADO") {
                                        data2.forEach((articuloSerie) => {
                                            // console.log(articuloSerie, identificadorFila, idArticulo);
                                            if (
                                                articuloSerie.delSeriesMov_articleID ==
                                                    idArticulo &&
                                                articuloSerie.delSeriesMov_affected ===
                                                    "1"
                                            ) {
                                                cuerpoModal.append(
                                                    //agregar el valor de la serie en el select
                                                    `<div class="col-md-2">
                                            <span><strong>Articulo :</strong></span>
                                        </div>
                                        <div class="col-md-10 " style="margin-bottom: 5px;">
                                            <select class="form-control ${identificadorFila} ${articuloSerie.delSeriesMov_id}" id="serie" name="serie[]" 
                                            disabled>
                                            <option value="${articuloSerie.delSeriesMov_lotSerie}">${articuloSerie.delSeriesMov_lotSerie}</option>
                                            </select>
                                        </div>`
                                                );
                                            }
                                            contador++;
                                        });
                                    }
                                } else {
                                    while (contador <= cantidad) {
                                        //Agremos los selects de acuerdo a la cantidad ingresada por el usuario y las series que se encuentran en el almacen

                                        cuerpoModal.append(
                                            `<div class="col-md-1">
                                        <span><strong>Serie :</strong></span>
                                    </div>
                                    <div class="col-md-11" style="margin-bottom: 5px;">
                                        <select class="form-control ${identificadorFila}" id="serie" name="serie[]"
                                        ${
                                            estado === "FINALIZADO"
                                                ? "disabled"
                                                : ""
                                        }
                                        >
                                        <option value="">Seleccione una serie</option>
                                        ${data.map((serie) => {
                                            return `<option value="${serie.lotSeries_lotSerie}">${serie.lotSeries_lotSerie}</option>`;
                                        })}
                                        </select>
                                    </div>`
                                        );

                                        contador++;
                                    }
                                }
                            }
                        },
                    });
                } else {
                    $.ajax({
                        url: "/logistica/inventarios/api/getSeries",
                        method: "GET",
                        data: {
                            almacen: almacen,
                            claveArticulo: identificadorFila.split("-")[0],
                        },
                        success: function ({ status, data }) {
                            //quitar el signo negativo de la cantidad
                            cantidad = cantidad * -1;
                            // while (contador <= cantidad) {
                            //Agremos los selects de acuerdo a la cantidad ingresada por el usuario y las series que se encuentran en el almacen
                            if (isPropiedadExiste2) {
                                let serie =
                                    articulosSerieQuitar[identificadorFila][
                                        "serie"
                                    ];

                                // console.log(contador, cantidad, serie);
                                while (contador <= cantidad) {
                                    serieP = serie[contador - 1];
                                    console.log(
                                        serie,
                                        serieP,
                                        contador,
                                        cantidad
                                    );
                                    cuerpoModal.append(
                                        `<div class="col-md-2">
                                        <span><strong>Articulo :</strong></span>
                                    </div>
                                    <div class="col-md-10 " style="margin-bottom: 5px;">
                                        <select class="form-control ${identificadorFila}" id="serie" name="serie[]">
                                        <option value="">Seleccione una serie</option>
                                            ${data.map(
                                                (serie) =>
                                                    `<option value="${
                                                        serie.lotSeries_lotSerie
                                                    }" ${
                                                        serieP ===
                                                        serie.lotSeries_lotSerie
                                                            ? "selected"
                                                            : ""
                                                    }>${
                                                        serie.lotSeries_lotSerie
                                                    }</option>`
                                            )}
                                        </select>
                                    </div>`
                                    );

                                    contador++;
                                }
                            } else {
                                while (contador <= cantidad) {
                                    cuerpoModal.append(
                                        `<div class="col-md-1">
                                    <span><strong>Serie :</strong></span>
                                </div>
                                <div class="col-md-11" style="margin-bottom: 5px;">
                                    <select class="form-control ${identificadorFila}" id="serie" name="serie[]">
                                    <option value="">Seleccione una serie</option>
                                    ${data.map((serie) => {
                                        return `<option value="${serie.lotSeries_lotSerie}">${serie.lotSeries_lotSerie}</option>`;
                                    })}
                                    </select>
                                </div>`
                                    );

                                    contador++;
                                }
                            }
                            // }
                        },
                    });
                }

                jQuery("#generarLotesSeries").hide();
                jQuery("#modal6Agregar").hide();
            } else {
                if (estado === "INICIAL") {
                    jQuery("#generarLotesSeries").show();
                    jQuery("#modal6Agregar").show();
                    $("#quitarSeries").hide();
                }
            }
        }
    });

    $(".modal7").on("show.bs.modal", function (e) {
        const isInvalid3 = validateCantidad();
        if (isInvalid3) {
            showMessage("Por favor ingresa la cantidad del articulo", "error");
            return false;
        } else {
            let cuerpoModal = jQuery("#form-articulos-serie2");
            let ruta = window.location.href;
            let ruta2 = ruta.split("/");
            let idCompra = "";

            if (ruta2.length > 5) {
                idCompra = ruta2[ruta2.length - 1];
            }

            cuerpoModal.html(""); //Limpiamos el cuerpo del modal
            let identificadorFila = $(e.relatedTarget)
                .parent()
                .parent()
                .attr("id");

            let movimiento = $("#select-movimiento").val();

            let transito = false;
            let cantidadIndi = false;
            if (movimiento === "Tránsito" || movimiento === "Recibo") {
                transito = true;
            }

            if (transito === false) {
                cantidad = parseFloat($("#canti-" + identificadorFila).val());
            } else {
                cantidad = parseFloat(
                    $("#montoPendiente-" + identificadorFila).val()
                );

                let montoRecibir = $(
                    "#montoRecibir-" + identificadorFila
                ).val();
                if (montoRecibir !== "") {
                    cantidad = parseFloat(montoRecibir);
                    cantidadIndi = true;
                }
            }

            let idArticulo = $("#id-" + identificadorFila).val();
            let claveArticulo = identificadorFila.split("-")[0];
            let tituloClaveArticulo = jQuery(".articuloKeySerie2");
            tituloClaveArticulo.text(claveArticulo); //Asignamos el valor de la clave del articulo al titulo del modal

            jQuery("#clavePosicion2").val(identificadorFila);
            const isPropiedadExiste3 = articulosSerieTrans.hasOwnProperty(
                identificadorFila
            )
                ? true
                : false;

            let contador = 1;

            let estado = $("#status").text();

            if (estado === "POR AUTORIZAR") {
                $("#quitarSeries2").show();
            }
            if (estado === "INICIAL") {
                cuerpoModal.append(
                    `<div class="col-md-12" ">
                       <h5 class="text-center">Seleccionar las series, que desea quitar del almacén</h5>
                    </div>`
                );
            } else {
                cuerpoModal.append(
                    `<div class="col-md-12" ">
                       <h5 class="text-center">Series trasferidos/trasladados</h5>
                    </div>`
                );
            }

            let almacen = $("#almacenKey").val();
            if (idCompra !== "") {
                $.ajax({
                    url: "/logistica/inventarios/api/getSeriesSeleccionados",
                    method: "GET",
                    data: {
                        idCompra: idCompra,
                        almacen: almacen,
                        claveArticulo: identificadorFila.split("-")[0],
                    },
                    success: function ({ status, data, data2 }) {
                        console.log(data, data2);
                        let estado = $("#status").text();
                        let movimiento = $("#select-movimiento").val();
                        if (status === 200) {
                            if (data2.length > 0) {
                                if (
                                    estado === "INICIAL" &&
                                    movimiento !== "Entrada por Traspaso"
                                ) {
                                    while (contador <= cantidad) {
                                        articuloSerie =
                                            data2[contador - 1] === undefined
                                                ? ""
                                                : data2[contador - 1];
                                        console.log(
                                            articuloSerie,
                                            identificadorFila,
                                            idArticulo
                                        );
                                        if (
                                            articuloSerie.delSeriesMov_articleID ==
                                                idArticulo ||
                                            articuloSerie === ""
                                        ) {
                                            cuerpoModal.append(
                                                //agregar el valor de la serie en el select
                                                `<div class="col-md-2">
                                        <span><strong>Articulo :</strong></span>
                                    </div>
                                    <div class="col-md-10 " style="margin-bottom: 5px;">
                                        <select class="form-control ${identificadorFila} ${
                                                    articuloSerie.delSeriesMov_id
                                                }" id="serie" name="serie[]" 
                                        >
                                        <option value="">Seleccione una serie</option>
                                            ${data.map(
                                                (serie) =>
                                                    `<option value="${
                                                        serie.lotSeries_lotSerie
                                                    }" ${
                                                        articuloSerie.delSeriesMov_lotSerie ===
                                                        serie.lotSeries_lotSerie
                                                            ? "selected"
                                                            : ""
                                                    }>${
                                                        serie.lotSeries_lotSerie
                                                    }</option>`
                                            )}
                                        </select>
                                    </div>`
                                            );
                                        }
                                        contador++;
                                    }
                                }

                                if (estado === "FINALIZADO") {
                                    while (contador <= cantidad) {
                                        data2.forEach((articuloSerie) => {
                                            // console.log(articuloSerie, identificadorFila, idArticulo);
                                            if (
                                                articuloSerie.delSeriesMov_articleID ==
                                                    idArticulo &&
                                                articuloSerie.delSeriesMov_affected ===
                                                    "1"
                                            ) {
                                                cuerpoModal.append(
                                                    //agregar el valor de la serie en el select
                                                    `<div class="col-md-2">
                                             <span><strong>Serie :</strong></span>
                                         </div>
                                         <div class="col-md-10 " style="margin-bottom: 5px;">
                                             <select class="form-control ${identificadorFila} ${articuloSerie.delSeriesMov_id}" id="serie" name="serie[]" disabled>
                                            <option value="${articuloSerie.delSeriesMov_lotSerie}">${articuloSerie.delSeriesMov_lotSerie}</option>
                                             </select>
                                         </div>`
                                                );
                                            }
                                            contador++;
                                        });
                                    }
                                }

                                if (estado === "CANCELADO") {
                                    data2.forEach((articuloSerie) => {
                                        // console.log(articuloSerie, identificadorFila, idArticulo);
                                        if (
                                            articuloSerie.delSeriesMov_articleID ==
                                                idArticulo &&
                                            articuloSerie.delSeriesMov_affected ===
                                                "1"
                                        ) {
                                            cuerpoModal.append(
                                                //agregar el valor de la serie en el select
                                                `<div class="col-md-2">
                                             <span><strong>Serie :</strong></span>
                                         </div>
                                         <div class="col-md-10 " style="margin-bottom: 5px;">
                                             <select class="form-control ${identificadorFila} ${articuloSerie.delSeriesMov_id}" id="serie" name="serie[]" disabled>
                                            <option value="${articuloSerie.delSeriesMov_lotSerie}">${articuloSerie.delSeriesMov_lotSerie}</option>
                                             </select>
                                         </div>`
                                            );
                                        }

                                        contador++;
                                    });
                                }

                                if (estado === "POR AUTORIZAR") {
                                    data2.forEach((articuloSerie) => {
                                        if (contador <= cantidad) {
                                            // console.log(articuloSerie, identificadorFila, idArticulo);
                                            if (
                                                articuloSerie.delSeriesMov_articleID ==
                                                idArticulo
                                            ) {
                                                cuerpoModal.append(
                                                    //agregar el valor de la serie en el select
                                                    `<div class="col-md-2">
                                                 <span><strong>Serie :</strong></span>
                                             </div>
                                             <div class="col-md-10 " style="margin-bottom: 5px;">
                                                 <select class="form-control ${identificadorFila} ${articuloSerie.delSeriesMov_id}" id="serie" name="serie[]" disabled>
                                                <option value="${articuloSerie.delSeriesMov_lotSerie}">${articuloSerie.delSeriesMov_lotSerie}</option>
                                                 </select>
                                             </div>`
                                                );
                                            }
                                        }
                                        contador++;
                                    });
                                }

                                if (
                                    estado === "INICIAL" &&
                                    movimiento === "Entrada por Traspaso"
                                ) {
                                    data2.forEach((articuloSerie) => {
                                        // console.log(articuloSerie, identificadorFila, idArticulo);
                                        if (
                                            articuloSerie.delSeriesMov_articleID ==
                                            idArticulo
                                        ) {
                                            cuerpoModal.append(
                                                //agregar el valor de la serie en el select
                                                `<div class="col-md-2">
                                             <span><strong>Serie :</strong></span>
                                         </div>
                                         <div class="col-md-10 " style="margin-bottom: 5px;">
                                             <select class="form-control ${identificadorFila} ${articuloSerie.delSeriesMov_id}" id="serie" name="serie[]" disabled>
                                            <option value="${articuloSerie.delSeriesMov_lotSerie}">${articuloSerie.delSeriesMov_lotSerie}</option>
                                             </select>
                                         </div>`
                                            );
                                        }

                                        contador++;
                                    });
                                }
                            } else {
                                while (contador <= cantidad) {
                                    //Agremos los selects de acuerdo a la cantidad ingresada por el usuario y las series que se encuentran en el almacen

                                    cuerpoModal.append(
                                        `<div class="col-md-1">
                                        <span><strong>Serie :</strong></span>
                                    </div>
                                    <div class="col-md-11" style="margin-bottom: 5px;">
                                        <select class="form-control ${identificadorFila}" id="serie" name="serie[]">
                                        <option value="">Seleccione una serie</option>
                                        ${data.map((serie) => {
                                            return `<option value="${serie.lotSeries_lotSerie}">${serie.lotSeries_lotSerie}</option>`;
                                        })}
                                        </select>
                                    </div>`
                                    );

                                    contador++;
                                }
                            }
                        }
                    },
                });
            } else {
                $.ajax({
                    url: "/logistica/inventarios/api/getSeries",
                    method: "GET",
                    data: {
                        almacen: almacen,
                        claveArticulo: identificadorFila.split("-")[0],
                    },
                    success: function ({ status, data }) {
                        // console.log(data);
                        //quitar el signo negativo de la cantidad
                        //Agremos los selects de acuerdo a la cantidad ingresada por el usuario y las series que se encuentran en el almacen
                        if (isPropiedadExiste3) {
                            let serie =
                                articulosSerieTrans[identificadorFila]["serie"];

                            while (contador <= cantidad) {
                                serieP = serie[contador - 1];
                                cuerpoModal.append(
                                    `<div class="col-md-2">
                                        <span><strong>Articulo :</strong></span>
                                    </div>
                                    <div class="col-md-10 " style="margin-bottom: 5px;">
                                        <select class="form-control ${identificadorFila}" id="serie" name="serie[]">
                                        <option value="">Seleccione una serie</option>
                                            ${data.map(
                                                (serie) =>
                                                    `<option value="${
                                                        serie.lotSeries_lotSerie
                                                    }" ${
                                                        serieP ===
                                                        serie.lotSeries_lotSerie
                                                            ? "selected"
                                                            : ""
                                                    }>${
                                                        serie.lotSeries_lotSerie
                                                    }</option>`
                                            )}
                                        </select>
                                    </div>`
                                );

                                contador++;
                            }
                        } else {
                            while (contador <= cantidad) {
                                cuerpoModal.append(
                                    `<div class="col-md-1">
                                    <span><strong>Serie :</strong></span>
                                </div>
                                <div class="col-md-11" style="margin-bottom: 5px;">
                                    <select class="form-control ${identificadorFila}" id="serie" name="serie[]">
                                    <option value="">Seleccione una serie</option>
                                    ${data.map((serie) => {
                                        return `<option value="${serie.lotSeries_lotSerie}">${serie.lotSeries_lotSerie}</option>`;
                                    })}
                                    </select>
                                </div>`
                                );

                                contador++;
                            }
                        }
                    },
                });
            }

            // console.log(articulosSerieTrans, isPropiedadExiste3);
        }
    });

    const generarSerie = () => {
        return posiblesLotesSeries.charAt(
            Math.floor(Math.random() * posiblesLotesSeries.length)
        );
    };

    const formarCadenaSerie = () => {
        return generarSerie() + generarSerie() + generarSerie();
        estatus;
    };

    //Asignacion de LOTES DE SERIES
    jQuery("#generarLotesSeries").click(function (e) {
        const $inputsModalClave = jQuery("#clavePosicion").val();
        const $inputsGenerados = jQuery("." + $inputsModalClave);

        $inputsGenerados.each(function (index, input) {
            let serie = `${formarCadenaSerie()}-${formarCadenaSerie()}-${formarCadenaSerie()}`;
            jQuery(input).val(serie);
        });
    });

    $("#quitarSeries").click(function () {
        let isSerieVacio = false;
        let isRepetidoSerie = false;
        let identificadorFila = jQuery("#clavePosicion").val();
        $('select[name^="serie[]"]').each(function (index) {
            if ($(this).val() === "") {
                showMessage(
                    "Por favor seleccione la serie del artículo",
                    "error"
                );
                isSerieVacio = true;
                return false;
            }
        });

        if (isSerieVacio) {
            return false;
        }

        if (!isSerieVacio) {
            let arraySerie = [];
            let arrayClaves = [];
            let repetidos = {};

            $('select[name^="serie[]"]').each(function (index, articulo) {
                let clase = $(articulo).attr("class").split(" ");
                if (clase.length > 2) {
                    let clave = clase[2];
                    arrayClaves.push(clave);
                }

                arraySerie.push($(articulo).val());
            });

            arraySerie.forEach((serie) => {
                repetidos[serie] = (repetidos[serie] || 0) + 1;
            });

            let keySeries = Object.keys(repetidos);
            keySeries.forEach((keySerie) => {
                if (repetidos[keySerie] !== 1) {
                    showMessage(
                        "Por favor, ingresa diferentes series",
                        "error"
                    );
                    isRepetidoSerie = true;
                    return false;
                }
            });

            if (isRepetidoSerie) {
                return false;
            }

            if (!isRepetidoSerie) {
                articulosSerieQuitar = {
                    ...articulosSerieQuitar,
                    [identificadorFila]: {
                        serie: arraySerie,
                        ids: arrayClaves,
                    },
                };
                $(".modal6").modal("hide");
            }
        }
    });

    $("#quitarSeries2").click(function () {
        let movimiento = $("#select-movimiento").val();
        let isSerieVacio = false;
        let isRepetidoSerie = false;
        let identificadorFila = jQuery("#clavePosicion2").val();

        $('select[name^="serie[]"]').each(function (index) {
            if ($(this).val() === "") {
                showMessage(
                    "Por favor seleccione la serie del artículo",
                    "error"
                );
                isSerieVacio = true;
                return false;
            }
        });

        if (isSerieVacio) {
            return false;
        }

        if (!isSerieVacio) {
            let arraySerie = [];
            let arrayClaves = [];
            let repetidos = {};

            $('select[name^="serie[]"]').each(function (index, articulo) {
                let clase = $(articulo).attr("class").split(" ");
                if (clase.length > 2) {
                    let clave = clase[2];
                    arrayClaves.push(clave);
                }

                arraySerie.push($(articulo).val());
            });

            arraySerie.forEach((serie) => {
                repetidos[serie] = (repetidos[serie] || 0) + 1;
            });

            let keySeries = Object.keys(repetidos);
            keySeries.forEach((keySerie) => {
                if (repetidos[keySerie] !== 1) {
                    showMessage(
                        "Por favor, ingresa diferentes series",
                        "error"
                    );
                    isRepetidoSerie = true;
                    return false;
                }
            });

            if (isRepetidoSerie) {
                return false;
            }

            if (!isRepetidoSerie) {
                if (movimiento != "Tránsito") {
                    articulosSerieTrans = {
                        ...articulosSerieTrans,
                        [identificadorFila]: {
                            serie: arraySerie,
                            ids: arrayClaves,
                        },
                    };
                } else {
                    articulosSerieTrans = {
                        ...articulosSerieTrans,
                        [identificadorFila]: {
                            serie: arraySerie,
                        },
                    };
                }
                $(".modal7").modal("hide");
            }
        }
    });

    $("#modal6Agregar").click(function () {
        let isSerieVacio = false;
        let isRepetidoSerie = false;
        let identificadorFila = jQuery("#clavePosicion").val();
        $('input[name^="serie[]"]').each(function (index, input) {
            if ($(input).val() === "") {
                showMessage("Por favor ingresa la serie del artículo", "error");
                isSerieVacio = true;
                return false;
            }
        });

        if (isSerieVacio) {
            return false;
        }

        if (!isSerieVacio) {
            let arraySerie = [];
            let arrayClaves = [];
            let repetidos = {};

            $('input[name^="serie[]"]').each(function (index, articulo) {
                let clase = $(articulo).attr("class").split(" ");
                if (clase.length > 2) {
                    let clave = clase[2];
                    arrayClaves.push(clave);
                }

                arraySerie.push($(articulo).val());
            });

            arraySerie.forEach((serie) => {
                repetidos[serie] = (repetidos[serie] || 0) + 1;
            });

            let keySeries = Object.keys(repetidos);
            keySeries.forEach((keySerie) => {
                if (repetidos[keySerie] !== 1) {
                    showMessage(
                        "Por favor, ingresa diferentes series",
                        "error"
                    );
                    isRepetidoSerie = true;
                    return false;
                }
            });

            if (isRepetidoSerie) {
                return false;
            }

            if (!isRepetidoSerie) {
                articulosSerie = {
                    ...articulosSerie,
                    [identificadorFila]: {
                        serie: arraySerie,
                        ids: arrayClaves,
                    },
                };
                $(".modal6").modal("hide");
            }
        }
    });

    //Costo promedio
    let articulo = 0;
    let compra = 0;
    let entro = false;
    $('input[id^="keyArticulo-"]').each(function (index, input) {
        $(input).focus(function (e) {
            let id = $(input).attr("id").split("-")[1];
            compra = $("#idCompra").val();
            articulo = id;
            entro = true;
        });
    });

    $("#costoPromedio").click(function () {
        if (entro == true) {
            $("#loading2").show();
            $.ajax({
                url: "/getCostoPromedio2/inventarios",
                method: "GET",
                data: {
                    id: articulo,
                    idInventario: compra,
                },
                success: function ({ estatus, data, articulosByAlmacen }) {
                    $("#loading2").hide();
                    if (estatus === 200) {
                        if (data !== null) {
                            let precioLista = currency(
                                data.articles_listPrice1,
                                {
                                    separator: ",",
                                    precision: 2,
                                    symbol: "$",
                                }
                            ).format();

                            let ultimoCosto = truncarDecimales(
                                formatoMexico(data.articlesCost_lastCost),
                                2
                            );

                            let ultimoCostoPromedio = truncarDecimales(
                                formatoMexico(data.articlesCost_averageCost),
                                2
                            );
                            $("#articuloCostoPromedio").val(data.articles_key);
                            $("#descripcionCostoPromedio").val(
                                data.articles_descript
                            );

                            let articlesInv_inventory = truncarDecimales(
                                formatoMexico(data.articlesInv_inventory),
                                2
                            );

                            $("#disponibleCostoPromedio").val(
                                articlesInv_inventory
                            );
                            $("#existenciaCostoPromedio").val(
                                articlesInv_inventory
                            );
                            $("#descripcionCostoPromedio2").val(
                                data.articles_descript
                            );
                            $("#tipoCostoPromedio").val(data.articles_type);
                            $("#estatusCostoPromedio").val(
                                data.articles_status
                            );
                            $("#precioCostoPromedio").val(precioLista);
                            $("#unidadCostoPromedio").val(data.units_unit);
                            $("#categoriaCostoPromedio").val(
                                data.articles_category
                            );
                            $("#familiaCostoPromedio").val(
                                data.articles_family
                            );
                            $("#ivaCostoPromedio").val(
                                data.articles_porcentIva
                            );
                            $("#ultimoCostoPromedio").val(ultimoCosto);
                            $("#ultimoCostoPromedio2").val(ultimoCostoPromedio);

                            articulosByAlmacen.forEach((element, index) => {
                                let inventario = currency(
                                    element.articlesInv_inventory,
                                    {
                                        separator: ",",
                                        precision: 2,
                                        symbol: "",
                                    }
                                ).format();
                                $("#almacenCostoPromedio" + index).html(
                                    element.depots_name
                                );
                                $("#inventarioCostoPromedio" + index).html(
                                    inventario
                                );
                            });
                        }
                    }
                },
            });
        }
    });

    $("#costoPromedioModal").on("hidden.bs.modal", function () {
        //REGRESAR el tab a la primera pestaña
        $("#activities").addClass("active");
        $("#followers").removeClass("active");
        $("#following").removeClass("active");

        $("#li1").addClass("active");
        $("#li2").removeClass("active");
        $("#li3").removeClass("active");
    });

    //Aqui comienza el flujo
    let primerFlujo = $("#movimientoFlujo").val();

    if (primerFlujo !== "") {
        let bodyTable = $("#movimientosTr");

        let jsonFlujo = JSON.parse(primerFlujo);
        generarTablaFlujo(bodyTable, jsonFlujo);
    }

    function generarTablaFlujo(bodyTable, jsonFlujo) {
        jsonFlujo.forEach((element) => {
            let tr = document.createElement("tr");
            let dataOrigen = "";
            let destino = "";

            if (element.movementFlow_cancelled === "1") {
                dataOrigen =
                    "<span class='badge badge-danger'>C</span>" +
                    element.movementFlow_movementOrigin +
                    " " +
                    element.movementFlow_movementOriginID;
                destino =
                    "<span class='badge badge-danger'>C</span>" +
                    element.movementFlow_movementDestinity +
                    " " +
                    element.movementFlow_movementDestinityID;
            } else {
                dataOrigen =
                    element.movementFlow_movementOrigin +
                    " " +
                    element.movementFlow_movementOriginID;
                destino =
                    element.movementFlow_movementDestinity +
                    " " +
                    element.movementFlow_movementDestinityID;
            }

            let td1 = document.createElement("td");
            let td2 = document.createElement("td");
            let td5 = document.createElement("td");
            let td3 = document.createElement("td");
            let td4 = document.createElement("td");

            td1.innerHTML = dataOrigen;
            td2.innerHTML = element.movementFlow_moduleOrigin;
            td5.innerHTML = "-------";
            td3.innerHTML = destino;
            td4.innerHTML = element.movementFlow_moduleDestiny;
            tr.appendChild(td1);
            tr.appendChild(td2);
            tr.appendChild(td5);
            tr.appendChild(td3);
            tr.appendChild(td4);

            if (jsonFlujo.length > 1) {
                tr.setAttribute("class", "flujoM");
                tr.setAttribute("data-info", JSON.stringify(element));
                tr.addEventListener("click", function (e) {
                    let dataSeleccionada = $(this).attr("data-info");
                    let bodyTable = $("#movimientosTr");
                    let arrayObjeto = [JSON.parse(dataSeleccionada)];

                    bodyTable.html("");
                    $("#data-info").val(dataSeleccionada);

                    generarTablaFlujo(bodyTable, arrayObjeto);
                });
            } else {
                $("#data-info").val(JSON.stringify(element));
            }

            bodyTable.append(tr);
        });

        if (jsonFlujo.length > 1) {
            $(".closeModalFlujo").show();
            $(".optionFlujo").hide();
        } else {
            $(".closeModalFlujo").hide();
            $(".optionFlujo").show();
        }
    }

    $(".flujoPrincipal").click(function () {
        $("#movimientosTr").html("");
        generarTablaFlujo($("#movimientosTr"), JSON.parse(primerFlujo));
    });

    //Revisamos si tiene permiso para afectar, cancelar o guardar
    if (movimiento !== "" && movimiento !== "Tránsito") {
        let movimientoSinEspacios = movimiento.replace(/\s/g, "");

        // Escapar el punto en el nombre del movimiento
        let movimientoConEscape = movimientoSinEspacios.replace(/\./g, "\\.");

        if ($("#" + movimientoConEscape).length === 0) {
            $("#afectar-boton").hide();
            $("#cancelar-boton").hide();
            $(".finish").hide();
        }
    }


    $("#almacenKey").change(function () {
        let almacen = $(this).val();
        if (almacen !== "") {
            $.ajax({
                url: "/invArtDepot",
                type: "GET",
                data: {
                    depot: $("#almacenKey").val(),
                },
                success: function ({ estatus, dataArticulos, sucursal }) {
                    if (estatus === 200) {
                        tablaArticulos.clear().draw();
                        let keyArticulos = Object.keys(dataArticulos);

                        keyArticulos.forEach((key) => {
                            let cantidad = currency(
                                dataArticulos[key].articlesInv_inventory,
                                {
                                    separator: ",",
                                    precision: 2,
                                    decimal: ".",
                                    symbol: "",
                                }
                            ).format();

                            if (
                                dataArticulos[key].articlesInv_branchKey ==
                                    sucursal ||
                                dataArticulos[key].articlesInv_branchKey == null
                            ) {
                                tablaArticulos.row
                                    .add([
                                        dataArticulos[key].articles_key,
                                        dataArticulos[key].articles_descript,
                                        cantidad,
                                        dataArticulos[key].articles_porcentIva,
                                        dataArticulos[key].articles_unitSale,
                                        dataArticulos[key].articles_type,
                                        dataArticulos[key].articles_transfer,
                                    ])
                                    .draw();
                            }
                        });
                    }
                },
            });
        }
    });

    $("#ArtExistencia").change(function () {
        let checkArticulosExistentes = $("#ArtExistencia").is(":checked");
        console.log(checkArticulosExistentes);
        let almacen = $(this).val();
        if (almacen !== "") {
            $.ajax({
                url: "/invArtExistencia",
                type: "GET",
                data: {
                    depot: $("#almacenKey").val(),
                    checkArticulosExistentes : checkArticulosExistentes
                },
                success: function ({ estatus, dataArticulos, sucursal }) {
                    if (estatus === 200) {
                        tablaArticulos.clear().draw();
                        let keyArticulos = Object.keys(dataArticulos);

                        keyArticulos.forEach((key) => {
                            let cantidad = currency(
                                dataArticulos[key].articlesInv_inventory,
                                {
                                    separator: ",",
                                    precision: 2,
                                    decimal: ".",
                                    symbol: "",
                                }
                            ).format();

                            if (
                                dataArticulos[key].articlesInv_branchKey ==
                                    sucursal ||
                                dataArticulos[key].articlesInv_branchKey == null
                            ) {
                                tablaArticulos.row
                                    .add([
                                        dataArticulos[key].articles_key,
                                        dataArticulos[key].articles_descript,
                                        cantidad,
                                        dataArticulos[key].articles_porcentIva,
                                        dataArticulos[key].articles_unitSale,
                                        dataArticulos[key].articles_type,
                                        dataArticulos[key].articles_transfer,
                                    ])
                                    .draw();
                            }
                        });
                    }
                },
            });
        }
    });

    $("#select-search-hide").change(function () {
        let categoria = $("#select-search-hide").val();

        let almacen = $(this).val();
        if (almacen !== "") {
            $.ajax({
                url: "/invArtCategoria",
                type: "GET",
                data: {
                    depot: $("#almacenKey").val(),
                    categoria : categoria
                },
                success: function ({ estatus, dataArticulos, sucursal }) {
                    if (estatus === 200) {
                        tablaArticulos.clear().draw();
                        let keyArticulos = Object.keys(dataArticulos);

                        keyArticulos.forEach((key) => {
                            let cantidad = currency(
                                dataArticulos[key].articlesInv_inventory,
                                {
                                    separator: ",",
                                    precision: 2,
                                    decimal: ".",
                                    symbol: "",
                                }
                            ).format();

                            if (
                                dataArticulos[key].articlesInv_branchKey ==
                                    sucursal ||
                                dataArticulos[key].articlesInv_branchKey == null
                            ) {
                                tablaArticulos.row
                                    .add([
                                        dataArticulos[key].articles_key,
                                        dataArticulos[key].articles_descript,
                                        cantidad,
                                        dataArticulos[key].articles_porcentIva,
                                        dataArticulos[key].articles_unitSale,
                                        dataArticulos[key].articles_type,
                                        dataArticulos[key].articles_transfer,
                                    ])
                                    .draw();
                            }
                        });
                    }
                },
            });
        }
    });

    $("#select-search-grupo").change(function () {
        let grupo = $("#select-search-grupo").val();


        let almacen = $(this).val();
        if (almacen !== "") {
            $.ajax({
                url: "/invArtGrupo",
                type: "GET",
                data: {
                    depot: $("#almacenKey").val(),
                    grupo : grupo
                },
                success: function ({ estatus, dataArticulos, sucursal }) {
                    if (estatus === 200) {
                        tablaArticulos.clear().draw();
                        let keyArticulos = Object.keys(dataArticulos);

                        keyArticulos.forEach((key) => {
                            let cantidad = currency(
                                dataArticulos[key].articlesInv_inventory,
                                {
                                    separator: ",",
                                    precision: 2,
                                    decimal: ".",
                                    symbol: "",
                                }
                            ).format();

                            if (
                                dataArticulos[key].articlesInv_branchKey ==
                                    sucursal ||
                                dataArticulos[key].articlesInv_branchKey == null
                            ) {
                                tablaArticulos.row
                                    .add([
                                        dataArticulos[key].articles_key,
                                        dataArticulos[key].articles_descript,
                                        cantidad,
                                        dataArticulos[key].articles_porcentIva,
                                        dataArticulos[key].articles_unitSale,
                                        dataArticulos[key].articles_type,
                                        dataArticulos[key].articles_transfer,
                                    ])
                                    .draw();
                            }
                        });
                    }
                },
            });
        }
    });

    $("#select-search-familia").change(function () {
        let familia = $("#select-search-familia").val();


        let almacen = $(this).val();
        if (almacen !== "") {
            $.ajax({
                url: "/invArtFamilia",
                type: "GET",
                data: {
                    depot: $("#almacenKey").val(),
                    familia : familia
                },
                success: function ({ estatus, dataArticulos, sucursal }) {
                    if (estatus === 200) {
                        tablaArticulos.clear().draw();
                        let keyArticulos = Object.keys(dataArticulos);

                        keyArticulos.forEach((key) => {
                            let cantidad = currency(
                                dataArticulos[key].articlesInv_inventory,
                                {
                                    separator: ",",
                                    precision: 2,
                                    decimal: ".",
                                    symbol: "",
                                }
                            ).format();

                            if (
                                dataArticulos[key].articlesInv_branchKey ==
                                    sucursal ||
                                dataArticulos[key].articlesInv_branchKey == null
                            ) {
                                tablaArticulos.row
                                    .add([
                                        dataArticulos[key].articles_key,
                                        dataArticulos[key].articles_descript,
                                        cantidad,
                                        dataArticulos[key].articles_porcentIva,
                                        dataArticulos[key].articles_unitSale,
                                        dataArticulos[key].articles_type,
                                        dataArticulos[key].articles_transfer,
                                    ])
                                    .draw();
                            }
                        });
                    }
                },
            });
        }
    });

    $("#almacenKey").change();
}); //Fin de document ready

function disabledCompra() {
    let status = $("#status").text().trim();
    let movimiento = $("#select-movimiento").val();
    if (movimiento === "Entrada por Traspaso") {
        $("input[id^='canti-']").attr("readonly", true);
        $("input[id^='c_unitario-']").attr("readonly", true);
        $("#articleItem")
            .find("input[name='dataArticulos[]'], select")
            .attr("readonly", true);
    }

    if (status !== "INICIAL") {
        $("#articleItem")
            .find("input[type='number'], select")
            .attr("readonly", true);
        //buscar los select y deshabilitarlos
        $("#articleItem").find("select").attr("disabled", true);
        $("#articleItem").find(".agregarArticulos").attr("disabled", true);
        $("input[id^='canti-']").attr("readonly", true);
        $("input[id^='c_unitario-']").attr("readonly", true);
        $(".eliminacion-articulo").hide();
        $("#select-movimiento").attr("readonly", true);
        $("#fechaEmision").attr("readonly", true);
        $("#select-moneda").attr("readonly", true);
        $("#nameTipoCambio").attr("readonly", true);
        $("#select-moduleConcept").attr("readonly", true);
        $("#proveedorKey").attr("readonly", true);
        $("#proveedorReferencia").attr("readonly", true);
        $("#almacenKey").attr("readonly", true);
        $("#Destino").attr("disabled", true);
        $("#almacenDestinoKey").attr("readonly", true);
        $("#almacenDestinoModal").attr("disabled", true);
        $('input[id^="keyArticulo-"]').attr("readonly", true);
        $("#botonesWizard").hide();
        if (
            movimiento === "Entrada por Compra" ||
            movimiento === "Rechazo de Compra"
        ) {
            $(".accion-pendiente").hide();
            $(".accion-recibir").hide();
        }

        $('input[id^="keyArticulo-"]').removeAttr("onchange");

        $("#modal6Agregar").hide();
        $("#generarLotesSeries").hide();
        $("#quitarSeries").hide();
        $("#quitarSeries2").hide();

        //buscar los input con name serie y deshabilitarlos
        // $("input[name='serie']").attr("readonly", true);
    } else {
        $(".accion-pendiente").hide();
        $(".accion-recibir").hide();
    }
}

//Evento para calcular la cantidad por el factor de la unidad (usamos la clave y la unidad de compra asignada)
function changeCantidadInventario(clave, posicion) {
    let decimal = $("#decimales-" + clave + "-" + posicion).val();
    let status = $("#status").text().trim();

    // console.log(decimal, status);
    if (decimal !== "" && status !== "FINALIZADO") {
        operacionCantidadInventario(clave, posicion, decimal);
        changeCostoImporte(clave, posicion);
        importeTotal(clave, posicion);
        siguienteCampo(clave, posicion);
    }
}

//Recalculamos el valor de la cantidad inventario cuando se cambie la unidad del articulo
function recalcularCantidadInventario(clave, posicion) {
    let unidDecimal = $("#unid-" + clave + "-" + posicion).val();
    if (unidDecimal !== null) {
        //Obtenemos los decimales de la unidad del articulo
        let decimales = unidDecimal.split("-")[0];

        $.ajax({
            url: "/api/unidadFactor/decimales",
            method: "GET",
            data: { unidadFactor: decimales },
            success: function ({ status, data }) {
                if (status) {
                    let { units_decimalVal: decimal } = data;
                    $("#decimales-" + clave + "-" + posicion).val(decimal);
                    operacionCantidadInventario(clave, posicion, decimal);
                } else {
                    showMessage2(
                        "error",
                        "No pudimos obtener los decimales de la unidad a utilizar",
                        data
                    );
                }
            },
        });
    }
}

function calcularImporte(clave, posicion) {
    let importe = parseFloat(
        $("#c_unitario-" + clave + "-" + posicion)
            .val()
            .replace(/[$,]/g, "")
            .replace(/[^0-9,.]/g, "")
            .replace(/,/g, ".")
    );

    if (isNaN(importe)) {
        importe = "";
        $("#c_unitario-" + clave + "-" + posicion).val(importe);
    }

    if (importe !== "") {
        let truncarImporte = truncarDecimales(importe, 2);
        $("#c_unitario-" + clave + "-" + posicion).val(
            formatoMexico(truncarImporte)
        );
    }
    changeCostoImporte(clave, posicion);
    importeTotal(clave, posicion);
}

function siguienteCampo(clave) {
    $("#c_unitario-" + clave).focus();
}

//Operacion para hallar la cantidad inventario
function operacionCantidadInventario(clave, posicion, decimal) {
    let inputCantidad =
        $("#canti-" + clave + "-" + posicion).val() !== ""
            ? parseFloat(
                  $("#canti-" + clave + "-" + posicion)
                      .val()
                      .replace(/[$,]/g, "")
                      .replace(/[^0-9,.-]/g, "")
                      .replace(/,/g, ".")
              )
            : "";

    if (isNaN(inputCantidad)) {
        inputCantidad = "";
        $("#canti-" + clave + "-" + posicion).val("");
    }

    if (inputCantidad !== "") {
        let inputFactor = $("#unid-" + clave + "-" + posicion).val();
        let inputFactorArray = inputFactor.trim().split("-");
        let cantidadInventario = $("#c_Inventario-" + clave + "-" + posicion);
        let costoUnitario = $("#c_unitario-" + clave + "-" + posicion);

        let inputCantidadString = inputCantidad.toString();

        console.log(inputCantidadString);
        if (inputCantidadString.charAt(0) !== "-") {
            if (inputCantidad !== "" && inputCantidad > 0) {
                inputCantidad = truncarDecimales(inputCantidad, decimal);

                let cantidad =
                    parseFloat(inputCantidad) * parseFloat(inputFactorArray[1]);
                cantidad = truncarDecimales(cantidad, decimal);

                let cantidadFormat = currency(inputCantidad, {
                    separator: ",",
                    precision: parseInt(decimal),
                    symbol: "",
                }).format();

                // console.log(cantidadFormat, cantidadInventario);
                let cantidadInventarioFormat = currency(cantidad, {
                    separator: ",",
                    precision: parseInt(decimal),
                    symbol: "",
                }).format();

                $("#canti-" + clave + "-" + posicion).val(cantidadFormat);
                cantidadInventario.val(cantidadInventarioFormat);
            } else {
                cantidadInventario.val("");
            }
        } else {
            if ($("#status").text().trim() !== "FINALIZADO") {
                cantidadInventario.val("");
                costoUnitario.val("");
            }
            //quitamos el signo negativo
            inputCantidad = truncarDecimales(inputCantidad, decimal);
            let cantidadFormat = currency(inputCantidad, {
                separator: ",",
                precision: parseInt(decimal),
                symbol: "",
            }).format();
            $("#canti-" + clave + "-" + posicion).val(cantidadFormat);
            let cantidad = inputCantidadString.replace("-", "");
            let resultado =
                parseFloat(cantidad) * parseFloat(inputFactorArray[1]);
            cantidad = truncarDecimales(cantidad, decimal);
            let cantidadInventarioFormat = currency(resultado, {
                separator: ",",
                precision: parseInt(decimal),
                symbol: "",
            }).format();
            cantidadInventario.val("-" + cantidadInventarioFormat);
        }
    }
}

function truncarDecimales(numero, decimalesPermitidos) {
    let deciPermitido = parseInt(decimalesPermitidos);

    let numeroString = numero.toString();
    let numeroArray = numeroString.split(".");
    let numeroPermitido = "";
    let decimalesValidos = ".";

    if (numeroArray.length > 1) {
        let decimalesArray = numeroArray[1].split("");
        decimalesArray.forEach((element, index) => {
            if (index < deciPermitido) {
                decimalesValidos += element;
            }
            if (index > deciPermitido) {
                return;
            }
        });

        numeroPermitido = numeroArray[0] + decimalesValidos;
    } else {
        numeroPermitido = numero;
    }

    return numeroPermitido;
}
//Operacion para hallar el importe del artuculo
function changeCostoImporte(clave, posicion) {
    let inputCosto = parseFloat(
        $("#c_unitario-" + clave + "-" + posicion)
            .val()
            .replace(/[$,]/g, "")
    );
    let inputCantidad = parseFloat(
        $("#canti-" + clave + "-" + posicion)
            .val()
            .replace(/[$,]/g, "")
    );

    let CantidadString = $("#canti-" + clave + "-" + posicion)
        .val()
        .toString();

    let inputImporte = $("#importe-" + clave + "-" + posicion);
    let inputImporteTotal = $("#importe_total-" + clave + "-" + posicion);

    if (CantidadString.charAt(0) !== "-") {
        if (
            inputCosto !== "" &&
            inputCosto > 0 &&
            inputCantidad !== "" &&
            inputCantidad > 0
        ) {
            let importe = inputCosto * inputCantidad;
            let importeFormato = truncarDecimales(formatoMexico(importe), 2);
            inputImporte.val(importeFormato);
            inputImporteTotal.val(importeFormato); //Importe total modificado
        } else {
            inputImporte.val("");
            inputImporteTotal.val("");
        }
    } else {
        if ($("#status").text().trim() !== "FINALIZADO") {
            inputImporte.val("");
            inputImporteTotal.val("");
        }
    }
}

const formatoMexico = (number) => {
    const exp = /(\d)(?=(\d{3})+(?!\d))/g;
    const rep = "$1,";
    let arr = number.toString().split(".");
    arr[0] = arr[0].replace(exp, rep);
    return arr[1] ? arr.join(".") : arr[0];
};

function round(num) {
    var m = Number((Math.abs(num) * 100).toPrecision(15));
    return Math.round(m) / 100 * Math.sign(num);
}


//Operacion para hallar el importe total
function importeTotal(clave, posicion) {
    let cantidadInput = $("#canti-" + clave + "-" + posicion);
    let costoInput = $("#c_unitario-" + clave + "-" + posicion);

    let CantidadString = cantidadInput.val().toString();

    if (CantidadString.charAt(0) !== "-") {
        let cantidad = parseFloat(cantidadInput.val().replace(/[$,]/g, ""));
        let costo = parseFloat(costoInput.val().replace(/[$,]/g, ""));
        let importeTotal = cantidad * costo;
        let resultado = importeTotal;

        if (cantidad !== NaN && cantidad > 0 && costo !== NaN && costo > 0) {
            let resultadoFormato = truncarDecimales(
                round(resultado),
                2
            );

            $("#importe_total-" + clave + "-" + posicion).val(formatoMexico(resultadoFormato));
            calcularTotales();
            jsonArticulos();
        } else {
            $("#totalCompleto").val("0.00");
        }
    } else {
        let costo = parseFloat(costoInput.attr("data").replace(/[$,]/g, ""));
        let cantidad = CantidadString.replace("-", "");
        let importeTotal = parseFloat(cantidad) * costo;

        $("#importe_total-" + clave + "-" + posicion).attr(
            "data",
            importeTotal
        );
        calcularTotales();
        jsonArticulos();
    }
}

//Operacion para hallar los totales
function calcularTotales() {
    let total = 0;

    let negativo = false;
    $('input[id^="canti-"]').each(function (index, value) {
        let cantidad = $(this).val();
        let cantidadString = cantidad.toString();
        if (cantidadString.charAt(0) === "-") {
            negativo = true;
        }
    });

    if (negativo === false) {
        $('input[id^="importe_total-"]').each(function (index, value) {
            let idIntputs = $(this).attr("id");
            let totales = parseFloat(
                $("#" + idIntputs)
                    .val()
                    .replace(/[$,]/g, "")
            );
            if (totales !== "" && totales > 0) {
                total += parseFloat(totales);
            }
        });

        let totalCompleto = truncarDecimales(round(total), 2);

        $("#totalCompleto").val(formatoMexico(totalCompleto));
        $("#totalCompleto").attr("data", formatoMexico(totalCompleto));
    } else {
        $('input[id^="importe_total-"]').each(function (index, value) {
            let idIntputs = $(this).attr("id");
            let totales = parseFloat($("#" + idIntputs).attr("data"));
            if (totales !== "" && totales > 0) {
                total += parseFloat(totales);
            }
        });

        let totalCompleto = currency(total, {
            separator: ",",
            precision: 2,
        }).format();

        $("#totalCompleto").attr("data", totalCompleto);
    }
}

//Validamos si algun input de cantidad esta vacio
function validateCantidad() {
    let movimiento = $("#select-movimiento").val();
    let estado = false;

    $('input[id^="canti-"]').each(function (index, value) {
        let idIntputs = $(this).attr("id");
        let tieneClaveArticulo = idIntputs.split("-");
        let cantidad = $("#" + idIntputs)
            .val()
            .replace(/[$,]/g, "");
        if (cantidad === "" || cantidad == "0" || cantidad == "0.00") {
            if (tieneClaveArticulo.length == 2) {
                estado = false;
            } else {
                estado = true;
            }
        }
    });

    return estado;
}

function validateCantidadNegativa() {
    let estado = false;
    $('input[id^="canti-"]').each(function (index, value) {
        let idIntputs = $(this).attr("id");
        let cantidad = $("#" + idIntputs)
            .val()
            .replace(/[$,]/g, "");
        if (cantidad.charAt(0) === "-") {
            estado = true;
        }
    });
    return estado;
}

//validamos el almacen de destino
function validateAlmacenDestino() {
    let estado = false;
    let almacenDestino = $("#almacenDestinoKey").val();
    if (almacenDestino === "" || almacenDestino === null) {
        estado = true;
    }
    return estado;
}

//Validamos si algun input de importe esta vacio

function validateImporte() {
    let movimiento = $("#select-movimiento").val();
    let estado = false;
    let negativo = false;

    if (movimiento !== "Salida por Traspaso") {
        $('input[id^="canti-"]').each(function (index, value) {
            let cantidad = $(this).val();
            // let tieneClaveArticulo = idIntputs.split("-");
            let cantidadString = cantidad.toString();
            if (cantidadString.charAt(0) === "-") {
                negativo = true;
            }
        });

        if (negativo === false) {
            $('input[id^="importe_total-"]').each(function (index, value) {
                let idIntputs = $(this).attr("id");
                let tieneClaveArticulo = idIntputs.split("-");
                let cantidad = $("#" + idIntputs)
                    .val()
                    .replace(/[$,]/g, "");
                if (cantidad === "" || cantidad == "0" || cantidad == "0.00") {
                    if (tieneClaveArticulo.length == 2) {
                        estado = false;
                    } else {
                        estado = true;
                    }
                }
            });
        } else {
            $('input[id^="importe_total-"]').each(function (index, value) {
                let idIntputs = $(this).attr("id");
                let cantidad = $("#" + idIntputs).attr("data");
                if (cantidad === "") {
                    estado = true;
                }
            });
        }
    } else {
        estado = false;
    }
    return estado;
}

truncateDecimals = function (number, digits) {
    var multiplier = Math.pow(10, digits),
        adjustedNum = number * multiplier,
        truncatedNum = Math[adjustedNum < 0 ? "ceil" : "floor"](adjustedNum);
    return truncatedNum / multiplier;
};

//Eliminamos el articulo de la tabla

function eliminarArticulo(clave, posicion) {
    if (contadorArticulos > 0) {
        if (clave !== "ninguno") {
            articlesDelete = {
                ...articlesDelete,
                [clave + "-" + posicion]: jQuery(
                    "#id-" + clave + "-" + posicion
                ).val(),
            };

            $("#" + clave + "-" + posicion).remove();
        } else {
            $("#" + posicion).remove();
        }

        contadorArticulos--;
        calcularTotales();
        jQuery("#cantidadArticulos").val(contadorArticulos);
        jQuery("#inputDataArticlesDelete").attr(
            "value",
            JSON.stringify(articlesDelete)
        );
        jQuery("#keyArticulo").val("");
    }

    if (contadorArticulos === 0) {
        $("#controlArticulo").show();
        $("#controlArticulo2").show();
        jQuery("#keyArticulo").val("");
    }
}

function jsonArticulos() {
    let articulosLista = {};
    let key = "";
    const inputSaveArticulo = $("#inputDataArticles");

    $('input[name="dataArticulos[]"]').each(function (index, value) {
        let idIntputs = $(value).attr("id");
        let articuloName = idIntputs.split("-")[0];

        if (articuloName === "keyArticulo") {
            key = $(value).val();
        } else {
            let identificador = idIntputs.split("-");

            let id = $("#id-" + key + "-" + identificador[2]).val();
            let cantidad = $("#canti-" + key + "-" + identificador[2]).val();
            let unidad = $("#unid-" + key + "-" + identificador[2]).val();
            let c_unitario = "";
            if (cantidad.charAt(0) !== "-") {
                c_unitario = $("#c_unitario-" + key + "-" + identificador[2])
                    .val()
                    .replace(/[$,]/g, "");
            } else {
                //obtener el atributo data del input
                c_unitario = $("#c_unitario-" + key + "-" + identificador[2])
                    .attr("data")
                    .replace(/[$,]/g, "");
            }

            let importe_total = "";
            if (cantidad.charAt(0) !== "-") {
                importe_total = $(
                    "#importe_total-" + key + "-" + identificador[2]
                )
                    .val()
                    .replace(/[$,]/g, "");
            } else {
                importe_total = $(
                    "#importe_total-" + key + "-" + identificador[2]
                )
                    .attr("data")
                    .replace(/[$,]/g, "");
            }
            let pendiente = $(
                "#pendiente-" + key + "-" + identificador[2]
            ).val();
            let recibir = $("#recibir-" + key + "-" + identificador[2]).val();
            let c_Inventario = $(
                "#c_Inventario-" + key + "-" + identificador[2]
            ).val();
            let desp = $("#desp-" + key + "-" + identificador[2]).val();
            let referenceArticle = $(
                "#referenceArticle-" + key + "-" + identificador[2]
            ).val();
            let tipoArticulo = $(
                "#tipoArticulo-" + key + "-" + identificador[2]
            ).val();
            let aplicaIncre = $(
                "#aplicaIncre-" + key + "-" + identificador[2]
            ).val();
            if (tipoArticulo === "Normal" || tipoArticulo === "Servicio") {
                articulosLista = {
                    ...articulosLista,
                    [key + "-" + identificador[2]]: {
                        id: id,
                        cantidad: cantidad,
                        unidad: unidad,
                        c_unitario: c_unitario,
                        importe_total: importe_total,
                        pendiente: pendiente,
                        recibir: recibir,
                        c_Inventario: c_Inventario,
                        desp: desp,
                        referenceArticle: referenceArticle,
                        tipoArticulo: tipoArticulo,
                        aplicaIncre: aplicaIncre,
                    },
                };
            } else if (tipoArticulo === "Serie") {
                articulosLista = {
                    ...articulosLista,
                    [key + "-" + identificador[2]]: {
                        id: id,
                        cantidad: cantidad,
                        unidad: unidad,
                        c_unitario: c_unitario,
                        importe_total: importe_total,
                        pendiente: pendiente,
                        recibir: recibir,
                        c_Inventario: c_Inventario,
                        desp: desp,
                        referenceArticle: referenceArticle,
                        tipoArticulo: tipoArticulo,
                        asignacionSerie:
                            articulosSerie[key + "-" + identificador[2]]?.serie,
                        asignacionIdsSerie:
                            articulosSerie[key + "-" + identificador[2]]?.ids,
                        eliminarSerie:
                            articulosSerieQuitar[key + "-" + identificador[2]]
                                ?.serie,
                        eliminarIdsSerie:
                            articulosSerieQuitar[key + "-" + identificador[2]]
                                ?.ids,
                        transferirSerie:
                            articulosSerieTrans[key + "-" + identificador[2]]
                                ?.serie,
                        transferirIdsSerie:
                            articulosSerieTrans[key + "-" + identificador[2]]
                                ?.ids,
                        aplicaIncre: aplicaIncre,
                    },
                };

                console.log(articulosSerie[key + "-" + identificador[2]]);
            }
        }
    });

    inputSaveArticulo.attr("name", "dataArticulosJson");
    inputSaveArticulo.attr("value", JSON.stringify(articulosLista));

    return articulosLista;
}

async function getAlmacenByClave(clave) {
    let movimiento = $("#select-movimiento").val();
    await $.ajax({
        url: "/logistica/compras/api/getAlmacen",
        type: "GET",
        data: {
            almacen: clave,
        },
        success: function (data) {
            // console.log(data);

            if (Object.keys(data).length > 0) {
                jQuery("#almacenKey").val(data.depots_key);
            } else {
                if (movimiento !== "Salida por Traspaso") {
                    if (movimiento !== "Entrada por Traspaso") {
                        if (movimiento !== "Tránsito") {
                            jQuery("#almacenKey").val("");

                            swal({
                                title: "Error",
                                text: "La clave del almacén no existe",
                                icon: "error",
                                button: "Aceptar",
                            });
                        }
                    }
                }
            }
        },
    });
}

async function getAlmacenDestinoByClave(clave) {
    await $.ajax({
        url: "/logistica/compras/api/getAlmacen",
        type: "GET",
        data: {
            almacen: clave,
        },
        success: function (data) {
            console.log(data);

            if (Object.keys(data).length > 0) {
                jQuery("#almacenDestinoKey").val(data.depots_key);
            } else {
                jQuery("#almacenDestinoKey").val("");

                swal({
                    title: "Error",
                    text: "La clave del almacén no existe",
                    icon: "error",
                    button: "Aceptar",
                });
            }
        },
    });
}

async function buscadorArticulos(filaReferencia) {
    let tipoMov = $("#select-movimiento").val();
    let value = jQuery("#" + filaReferencia).val();
    let estatus = jQuery("#status").text().trim();

    await $.ajax({
        url: "/herramientas/buscar_articulo",
        method: "GET",
        data: {
            clave: value,
        },
        success: function ({ status, data }) {
            console.log(data);
            if (status) {
                const {
                    articles_key: articleKey,
                    articles_type: tipo,
                    articles_descript: articleName,
                    articles_unitSale: articleUnidad,
                    articles_transfer: articleUnidadTraspaso,
                } = data;

                // console.log(data);

                if (filaReferencia !== "keyArticulo") {
                    let articulo = filaReferencia.split("-")[1];
                    let posicion = filaReferencia.split("-")[2];
                    articlesDelete = {
                        ...articlesDelete,
                        [articulo + "-" + posicion]: jQuery(
                            "#id-" + articulo + "-" + posicion
                        ).val(),
                    };

                    jQuery("#inputDataArticlesDelete").attr(
                        "value",
                        JSON.stringify(articlesDelete)
                    );
                    $("#" + filaReferencia)
                        .parent()
                        .parent()
                        .remove();

                    contadorArticulos--;
                } else {
                    $("#" + filaReferencia)
                        .parent()
                        .parent()
                        .hide();
                }

                if ($(".td-aplica").is(":visible")) {
                    $("#articleItem").append(`
                           <tr id="${articleKey}-${result}">
                           <td class=""></td>
                           <td id="btnInput"><input name="dataArticulos[]" id="keyArticulo-${articleKey}-${result}" type="text" class="keyArticulo" value='${articleKey}' onchange="buscadorArticulos('keyArticulo-${articleKey}-${result}')" readonly>
               
                           <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal3">...</button>
                            ${
                                tipo === "Serie" &&
                                tipoMov == "Ajuste de Inventario" &&
                                estatus == "INICIAL"
                                    ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal6" id="modalSerie">S</button>'
                                    : ""
                            }

                                ${
                                    (tipo === "Serie" &&
                                        tipoMov == "Transferencia entre Alm." &&
                                        estatus == "INICIAL") ||
                                    (tipo === "Serie" &&
                                        tipoMov == "Salida por Traspaso" &&
                                        estatus == "INICIAL")
                                        ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal7" id="modalSerie2">S</button>'
                                        : ""
                                }
                           </td>
                           <td><input name="dataArticulos[]" id="desp-${articleKey}-${result}" type="text" class="botonesArticulos" value='${articleName}' readonly title="${articleName}"></td>
                           <td>
                                   <input name="dataArticulos[]" id="canti-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value=''>
                           </td>
                           <td class="costoArticulo" ${
                               tipoMov == "Ajuste de Inventario"
                                   ? 'style="display: "'
                                   : 'style="display: none"'
                           }><input name="dataArticulos[]" id="c_unitario-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='' onchange="calcularImporte('${articleKey}', '${result}')"></td>
                           <td>
                               <select name="dataArticulos[]" id="unid-${articleKey}-${result}" class="botonesArticulos" value='${
                        tipoMov != "Salida por Traspaso"
                            ? articleUnidad
                            : articleUnidadTraspaso
                    }' onchange="recalcularCantidadInventario('${articleKey}', '${result}')" >
                               </select>
                           </td>
                           <td><input name="dataArticulos[]" id="c_Inventario-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
              
                           <td class="totalArticulo" ${
                               tipoMov == "Ajuste de Inventario"
                                   ? 'style="display: "'
                                   : 'style="display: none"'
                           }><input name="dataArticulos[]" id="importe_total-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                           <td
                                   style="display: flex; justify-content: center; align-items: center" class='eliminacion-articulo'>
                                   <i class="fa fa-trash-o"  onclick="eliminarArticulo('${articleKey}', '${result}')" aria-hidden="true"
                                       style="color: red; font-size: 25px; cursor: pointer;"></i>
                           </td>
                           <td style="display: none">
                               <input name="dataArticulos[]" id="tipoArticulo-${articleKey}-${result}" type="hidden" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value='${tipo}'>
                           </td>
                            <td style="display: none">
                                <input id="decimales-${articleKey}-${result}" type="text" value="" readonly>
                            </td>
                           </tr>
                   `);
                } else {
                    $("#articleItem").append(`
                            <tr id="${articleKey}-${result}">
                            <td id="btnInput"><input name="dataArticulos[]" id="keyArticulo-${articleKey}-${result}" type="text" class="keyArticulo" value='${articleKey}' onchange="buscadorArticulos('keyArticulo-${articleKey}-${result}')">
                
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal3">...</button>
               
                                ${
                                    tipo === "Serie" &&
                                    tipoMov == "Ajuste de Inventario" &&
                                    estatus == "INICIAL"
                                        ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal6" id="modalSerie">S</button>'
                                        : ""
                                }

                                    ${
                                        (tipo === "Serie" &&
                                            tipoMov == "Transferencia entre Alm." &&
                                            estatus == "INICIAL") ||
                                        (tipo === "Serie" &&
                                            tipoMov == "Salida por Traspaso" &&
                                            estatus == "INICIAL")
                                            ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal7" id="modalSerie2">S</button>'
                                            : ""
                                    }
                            </td>
                            <td><input name="dataArticulos[]" id="desp-${articleKey}-${result}" type="text" class="botonesArticulos" value='${articleName}' readonly title="${articleName}"></td>
                            <td>
                                    <input name="dataArticulos[]" id="canti-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value=''>
                            </td>
                            <td class="costoArticulo" ${
                                tipoMov == "Ajuste de Inventario"
                                    ? 'style="display: "'
                                    : 'style="display: none"'
                            }><input name="dataArticulos[]" id="c_unitario-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='' onchange="calcularImporte('${articleKey}', '${result}')"></td>
                            <td>
                                <select name="dataArticulos[]" id="unid-${articleKey}-${result}" class="botonesArticulos" value='${
                        tipoMov != "Salida por Traspaso"
                            ? articleUnidad
                            : articleUnidadTraspaso
                    }' onchange="recalcularCantidadInventario('${articleKey}', '${result}')" >
                                </select>
                            </td>
                            <td><input name="dataArticulos[]" id="c_Inventario-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>

                            <td class="totalArticulo" ${
                                tipoMov == "Ajuste de Inventario"
                                    ? 'style="display: "'
                                    : 'style="display: none"'
                            }><input name="dataArticulos[]" id="importe_total-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                            <td
                                    style="display: flex; justify-content: center; align-items: center" class='eliminacion-articulo'>
                                    <i class="fa fa-trash-o"  onclick="eliminarArticulo('${articleKey}', '${result}')" aria-hidden="true"
                                        style="color: red; font-size: 25px; cursor: pointer;"></i>
                            </td>
                                 <td style="display: none">
                                       <input name="dataArticulos[]" id="tipoArticulo-${articleKey}-${result}" type="hidden" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value='${tipo}'>
                                   </td>
                                <td style="display: none">
                                    <input id="decimales-${articleKey}-${result}" type="text" value="" readonly>
                                </td>
                            </tr>
                    `);
                }

                const selectOptions = $("#unid-" + articleKey + "-" + result); //Obtenemos el select para añadirle las multiunidades correspondientes
                $.ajax({
                    url: "/logistica/compras/api/getMultiUnidad",
                    type: "GET",
                    data: {
                        factorUnidad: articleKey,
                    },
                    success: function (data) {
                        let unidadPorDefecto =
                            tipoMov != "Salida por Traspaso"
                                ? articleUnidad
                                : articleUnidadTraspaso;
                        let unidadPorDefectoIndex = {};
                        data.forEach((element) => {
                            if (
                                element.articlesUnits_unit == unidadPorDefecto
                            ) {
                                unidadPorDefectoIndex = element;
                            }

                            selectOptions.append(`
                                    <option value="${element.articlesUnits_unit}-${element.articlesUnits_factor}">${element.articlesUnits_unit}-${element.articlesUnits_factor}</option>
                                    `);
                        });

                        if (Object.keys(unidadPorDefectoIndex).length > 0) {
                            $("#unid-" + articleKey + "-" + result).val(
                                unidadPorDefecto +
                                    "-" +
                                    unidadPorDefectoIndex.articlesUnits_factor
                            );
                        }
                        selectOptions.change();
                    },
                });

                $.ajax({
                    url: "/comercial/ventas/api/getCosto",
                    type: "GET",
                    data: {
                        articulo: articleKey,
                        almacen: $("#almacenKey").val(),
                    },
                    success: function (data) {
                        if (data != null) {
                            let costoFormato = currency(
                                data.articlesCost_averageCost,
                                {
                                    separator: ",",
                                    precision: 2,
                                    symbol: "",
                                }
                            ).format();

                            $("#c_unitario-" + articleKey + "-" + result).val(
                                costoFormato
                            );

                            //agregar el atributo data para el costo unitario al input
                            $("#c_unitario-" + articleKey + "-" + result).attr(
                                "data",
                                costoFormato
                            );

                            result++;
                        }
                    },
                });

                contadorArticulos++;

                $("#cantidadArticulos").val(contadorArticulos);
            } else {
                //limpiamos los inputs de referencia
                if (filaReferencia !== "keyArticulo") {
                    let posicion = filaReferencia.split("-")[2];
                    let articulo = filaReferencia.split("-")[1];

                    $("#desp-" + articulo + "-" + posicion).val("");
                    $("#canti-" + articulo + "-" + posicion).val("");
                    $("#c_unitario-" + articulo + "-" + posicion).val("");
                    $("#unid-" + articulo + "-" + posicion).val("");
                    $("#unid-" + articulo + "-" + posicion).change();
                    $("#c_Inventario-" + articulo + "-" + posicion).val("");
                    $("#importe_total-" + articulo + "-" + posicion).val("");
                }
            }
        },
    });
}

//funcion para detectar los comandos de teclado
$(document).keydown(function (e) {
    let tipoMov = $("#select-movimiento").val().trim();

    if (tipoMov !== "Entrada por Traspaso") {
        if (e.ctrlKey && e.keyCode === 40) {
            if ($("#status").text().trim() !== "INICIAL") {
                return false;
            }

            if ($(".td-aplica").is(":visible")) {
                $("#articleItem").append(`
                       <tr id="${result}">
                       <td class=""></td>
                       <td id="btnInput"><input name="dataArticulos[]" id="keyArticulo-${result}" type="text" class="keyArticulo" value='' onchange="buscadorArticulos('keyArticulo-${result}')" readonly>
               
                       <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal3">...</button>
               
                       </td>
                       <td><input name="dataArticulos[]" id="desp-${result}" type="text" class="botonesArticulos" value='' readonly title=""></td>
                       <td>
                               <input name="dataArticulos[]" id="canti-${result}" type="text" class="botonesArticulos sinBotones" value=''>
                       </td>
                       <td class="costoArticulo" ${
                           tipoMov == "Ajuste de Inventario"
                               ? 'style="display: "'
                               : 'style="display: none"'
                       }><input name="dataArticulos[]" id="c_unitario-${result}" type="any" class="botonesArticulos sinBotones" value='' ></td>
                       <td>
                           <select name="dataArticulos[]" id="unid-${result}" class="botonesArticulos" value='' >
                           </select>
                       </td>
                       <td><input name="dataArticulos[]" id="c_Inventario-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
              
                       <td class="totalArticulo" ${
                           tipoMov == "Ajuste de Inventario"
                               ? 'style="display: "'
                               : 'style="display: none"'
                       }><input name="dataArticulos[]" id="importe_total-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                       <td
                               style="display: flex; justify-content: center; align-items: center" class='eliminacion-articulo'>
                               <i class="fa fa-trash-o"  onclick="eliminarArticulo('ninguno', '${result}')" aria-hidden="true"
                                   style="color: red; font-size: 25px; cursor: pointer;"></i>
                       </td>
                       <td style="display: none">
                           <input name="dataArticulos[]" id="tipoArticulo-${result}" type="hidden" class="botonesArticulos sinBotones" value=''>
                       </td>
                        <td style="display: none">
                               <input name="dataArticulos[]" id="tipoArticulo-${result}" type="hidden" class="botonesArticulos sinBotones" value=''>
                           </td>
                        <input id="decimales-${result}" type="text" value="" readonly>
                       </tr>
               `);
            } else {
                $("#articleItem").append(`
                        <tr id="${result}">
                        <td id="btnInput"><input name="dataArticulos[]" id="keyArticulo-${result}" type="text" class="keyArticulo" value='' onchange="buscadorArticulos('keyArticulo-${result}')">
                
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal3">...</button>
                        
               
                        </td>
                        <td><input name="dataArticulos[]" id="desp-${result}" type="text" class="botonesArticulos" value='' readonly title=""></td>
                        <td>
                                <input name="dataArticulos[]" id="canti-${result}" type="text" class="botonesArticulos sinBotones"   value=''>
                        </td>
                        <td class="costoArticulo" ${
                            tipoMov == "Ajuste de Inventario"
                                ? 'style="display: "'
                                : 'style="display: none"'
                        }><input name="dataArticulos[]" id="c_unitario-${result}" type="any" class="botonesArticulos sinBotones" value='' ></td>
                        <td>
                            <select name="dataArticulos[]" id="unid-${result}" class="botonesArticulos" value=''>
                            </select>
                        </td>
                        <td><input name="dataArticulos[]" id="c_Inventario-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>

                        <td class="totalArticulo" ${
                            tipoMov == "Ajuste de Inventario"
                                ? 'style="display: "'
                                : 'style="display: none"'
                        } ><input name="dataArticulos[]" id="importe_total-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                        <td
                                style="display: flex; justify-content: center; align-items: center" class='eliminacion-articulo'>
                                <i class="fa fa-trash-o"  onclick="eliminarArticulo('ninguno', '${result}')" aria-hidden="true"
                                    style="color: red; font-size: 25px; cursor: pointer;"></i>
                        </td>
                             <td style="display: none">
                                   <input name="dataArticulos[]" id="tipoArticulo-${result}" type="hidden" class="botonesArticulos sinBotones" value=''>
                               </td>
                            <input id="decimales-${result}" type="text" value="" readonly>
                        </tr>
                `);
            }

            result++;
            contadorArticulos++;

            $("#cantidadArticulos").val(contadorArticulos);
        }
    }
});

function validarInput(clave, posicion) {
    let cantidadRecibir = parseFloat(
        $("#montoRecibir-" + clave + "-" + posicion)
            .val()
            .replace(/[$,]/g, "")
            .replace(/[^0-9,.]/g, "")
            .replace(/,/g, ".")
    );

    if (isNaN(cantidadRecibir)) {
        cantidadRecibir = "";
        $("#montoRecibir-" + clave + "-" + posicion).val(cantidadRecibir);
    } else {
        $("#montoRecibir-" + clave + "-" + posicion).val(cantidadRecibir);
    }
}
