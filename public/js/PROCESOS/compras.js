

let articlesDelete = {};
let result = 1000;
let articulosSerie = {};
const posiblesLotesSeries = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

const movimientosArray = {
    Compras: "PROC_PURCHASE",
    CxP: "PROC_ACCOUNTS_PAYABLE",
    Din: "PROC_TREASURY",
    Gastos: "PROC_EXPENSES",
};

jQuery(document).ready(function () {
    let checkTodosArticulos = false;
    let checkArticulosExistentes = false;
    let listaPrecio = $("#select-precioProvee").val();
    if (listaPrecio == null) {
        $("#select-precioProvee").val(1);
        $("#select-precioProvee").trigger("change");
    }
    moment.locale("es-mx");
    //validamos el json detalle del movimiento
    let jsonGuardadoDetalle = $("#inputDataSaveArticles").val();

    if (jsonGuardadoDetalle != "") {
        let jsonDecode = JSON.parse(jsonGuardadoDetalle);
        let seriesData = jsonDecode["series"];
        let idSeriesData = jsonDecode["idSeries"];

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

        // getProveedorByClave(rowData[0]);
        $("#proveedorKey").trigger("change");
    });

    //Tabla de almacenes
    const tablaAlmacenes = jQuery("#shTable4").DataTable({
        select: {
            style: "single",
        },
        language: language,
        order: [[1, "desc"]], // Especificar la columna 2 (índice 1) para la ordenación ascendente
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    // Obtener el valor actual del campo de almacén
    var almacenActual = $('#almacenKey').val();

    // Verificar si el campo de almacén está vacío
    if (almacenActual === '') {
        // Obtener el primer almacén de la tabla de almacenes
        var primerAlmacen = $('#shTable4 tbody tr:first-child td:first-child').text();
        // Obtener el valor del tipo de almacén de la primera fila
        var primerAlmacenTipo = $('#shTable4 tbody tr:first-child td:nth-child(3)').text();

        // Establecer el valor del campo de almacén con el primer almacén encontrado
        $('#almacenKey').val(primerAlmacen);
        $('#almacenTipoKey').val(primerAlmacenTipo);

        // Disparar el evento 'change' en el campo de almacén para actualizar su valor
        $('#almacenKey').trigger('change');
    }

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

    const leyendas = {
        'Orden de Compra': 'Este proceso es de control y no genera movimientos en tu inventario.',
        'Entrada por Compra': 'Se recibe la factura de compra. Con esta operación, afectará tu inventario, costo promedio y generará la cuenta por pagar para programar pagos.',
        'Rechazo de Compra': 'En caso que la Orden de Compra ya no exista seguimiento alguno, finaliza tu proceso con este proceso estadístico, asignado el motivo del rechazo.'
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

        if (selectedMovimiento == "Entrada por Compra") {
            $("#afectar-boton").html("Finalizar <span class='glyphicon glyphicon-play pull-right'></span>");
        } else {
            $("#afectar-boton").html("Avanzar <span class='glyphicon glyphicon-play pull-right'></span>");
        }
    });

    // Mostrar la leyenda inicial al cargar la página si hay una opción seleccionada
    const selectedMovimiento = $('#select-movimiento').val();
    mostrarLeyenda(selectedMovimiento);
    //Tabla articulos
    if (selectedMovimiento == "Entrada por Compra") {
        $("#afectar-boton").html("Finalizar <span class='glyphicon glyphicon-play pull-right'></span>");
    } else {
        $("#afectar-boton").html("Avanzar <span class='glyphicon glyphicon-play pull-right'></span>");
    }


    
    const tablaArticulos = jQuery("#shTable5").DataTable({
        ajax: {
            url: `/articulos/listaProveedorCompras/${checkTodosArticulos}/${listaPrecio}`,
        },
        columns: [
            {
                data: "clave",
            },
            {
                data: "nombre",
            },
            {
                data: "iva",
                visible: false,
            },
            {
                data: "unidad",
                visible: false,
            },
            {
                data: "tipo",
                visible: false,
            },
            {
                data: "ultimoCosto",
                visible: false,
            },
        ],
        select: {
            style: "multi",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    $('#select-movimiento').on('change', function() {
        var selectedMovimiento = $(this).val();
        if (selectedMovimiento === "Rechazo de Compra") {
            // Lógica para traer todos los conceptos sin filtrar
            $.ajax({
                url: '/api/compra/getConceptosByMovimiento',
                method: 'GET',
                data: { movimiento: null }, // O cualquier otro valor que indique "todos los conceptos"
                // dataType: 'json',
                success: function (data) {
                    console.log(data);
                    var selectConceptos = $('#select-moduleConcept');
                    selectConceptos.empty();
                    selectConceptos.append($('<option>', { 
                        value: '',
                        text: 'Selecciona uno...'
                    }));
                    $.each(data, function(key, value) {
                        selectConceptos.append($('<option>', { 
                            value: value.moduleConcept_name,
                            text: value.moduleConcept_name
                        }));
                    });
                }
            });
        } else {
            // Lógica para filtrar los conceptos según el movimiento seleccionado
            $.ajax({
                url: '/api/compra/getConceptosByMovimiento',
                method: 'GET',
                data: { movimiento: selectedMovimiento },
                // dataType: 'json',
                success: function (data) {
                    console.log(data);
                    var selectConceptos = $('#select-moduleConcept');
                    selectConceptos.empty();
                    selectConceptos.append($('<option>', { 
                        value: '',
                        text: 'Selecciona uno...'
                    }));
                    $.each(data, function(key, value) {
                        selectConceptos.append($('<option>', { 
                            value: value.moduleConcept_name,
                            text: value.moduleConcept_name
                        }));
                    });
                }
            });
        }
    });

    //hacemos lo mismo que el evento change de arriba, pero para cuando se recargue la pagina
    if (selectedMovimiento === "Rechazo de Compra") {
        // Lógica para traer todos los conceptos sin filtrar
        $.ajax({
            url: '/api/compra/getConceptosByMovimiento',
            method: 'GET',
            data: { movimiento: null }, // O cualquier otro valor que indique "todos los conceptos"
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
        // Lógica para filtrar los conceptos según el movimiento seleccionado
        $.ajax({
            url: '/api/compra/getConceptosByMovimiento',
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
                $.each(data, function (key, value) {
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
        $("#almacenKey").trigger("keyup");
    });


    let verCosto = $("#verCosto").val();
    let bloquearCosto = $("#bloquearCosto").val();

    //si ver costo es igual a 1, le agregamos la clase display: none para ocultar el costo unitario
    if (verCosto === '0') {
        $(".td-costo").css("display", "none");
    }

    if (bloquearCosto === '1') {
        $(".bloquearCosto").prop("readonly", true);
    }


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
        console.log(datos);

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
                console.log(calculoImpuestos);
                let articleKey = datos[i]["clave"];
                let articleName = datos[i]["nombre"];

                let articleIva;

                if (calculoImpuestos === "0") {
                    articleIva = datos[i]["iva"];
                } else {
                    articleIva = "";
                }
                let articleUnidad = datos[i]["unidad"];
                let tipo = datos[i]["tipo"];
                let ultimoCosto = datos[i]["ultimoCosto"];
                console.log(tipo);

                if ($(".td-aplica").is(":visible")) {
                    $("#articleItem").append(`
                       <tr id="${articleKey}-${result}">
                       <td class=""></td>
                       <td id="btnInput"><input name="dataArticulos[]" id="keyArticulo-${articleKey}-${result}" type="text" class="keyArticulo" value='${articleKey}' title="${articleKey}" onchange="buscadorArticulos('keyArticulo-${articleKey}-${result}')" />
               
                       <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal3">...</button>

                       ${
                           tipo === "Serie" &&
                           tipoMov == "Entrada por Compra" &&
                           estatus == "INICIAL"
                               ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal6" id="modalSerie">S</button>'
                               : ""
                       }
               
                       </td>
                       <td><input name="dataArticulos[]" id="desp-${articleKey}-${result}" type="text" class="botonesArticulos" value='${articleName}' readonly title="${articleName}"></td>
                       <td>
                               <input name="dataArticulos[]" id="canti-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value=''>
                       </td>
                       <td class="td-costo"><input name="dataArticulos[]" id="c_unitario-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones bloquearCosto" value='${ultimoCosto}' onchange="calcularImporte('${articleKey}', '${result}')"></td>
                       <td>
                           <select name="dataArticulos[]" id="unid-${articleKey}-${result}" class="botonesArticulos" value='${articleUnidad}' onchange="recalcularCantidadInventario('${articleKey}', '${result}')" >
                           </select>
                       </td>
                       <td><input name="dataArticulos[]" id="c_Inventario-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                       <td><input name="dataArticulos[]" id="importe-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                       <td><input name="dataArticulos[]" id="porDesc-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="descuentoLineal('${articleKey}', '${result}')" value=''></td>
                       <td><input name="dataArticulos[]" id="descuento-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                       <td><input name="dataArticulos[]" id="iva-${articleKey}-${result}" type="number" class="botonesArticulos sinBotones" value='${articleIva}' readonly></td>
                       <td><input name="dataArticulos[]" id="importe_iva-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                       <td><input name="dataArticulos[]" id="importe_total-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
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
                        <td id="btnInput"><input name="dataArticulos[]" id="keyArticulo-${articleKey}-${result}" type="text" class="keyArticulo" value='${articleKey}' onchange="buscadorArticulos('keyArticulo-${articleKey}-${result}')" />
                
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal3">...</button>


                             ${
                                 tipo === "Serie" &&
                                 tipoMov == "Entrada por Compra" &&
                                 estatus == "INICIAL"
                                     ? '<button type="button" class="btn btn-warning btn-sm modalSerie" data-toggle="modal" data-target=".modal6" id="modalSerie">S</button>'
                                     : ""
                             }
               
                        </td>
                        <td><input name="dataArticulos[]" id="desp-${articleKey}-${result}" type="text" class="botonesArticulos" value='${articleName}' readonly title="${articleName}"></td>
                        <td>
                                <input name="dataArticulos[]" id="canti-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value=''>
                        </td>
                        <td class="td-costo"><input name="dataArticulos[]" id="c_unitario-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones bloquearCosto" value='${ultimoCosto}' onchange="calcularImporte('${articleKey}', '${result}')"></td>
                        <td>
                            <select name="dataArticulos[]" id="unid-${articleKey}-${result}" class="botonesArticulos" value='${articleUnidad}' onchange="recalcularCantidadInventario('${articleKey}', '${result}')" >
                            </select>
                        </td>
                        <td><input name="dataArticulos[]" id="c_Inventario-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                        <td><input name="dataArticulos[]" id="importe-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                        <td><input name="dataArticulos[]" id="porDesc-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="descuentoLineal('${articleKey}', '${result}')" value=''></td>
                        <td><input name="dataArticulos[]" id="descuento-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                        <td><input name="dataArticulos[]" id="iva-${articleKey}-${result}" type="number" class="botonesArticulos sinBotones" value='${articleIva}' readonly></td>
                        <td><input name="dataArticulos[]" id="importe_iva-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                        <td><input name="dataArticulos[]" id="importe_total-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
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

                

                let verCosto = $("#verCosto").val();
                let bloquearCosto = $("#bloquearCosto").val();

                //si ver costo es igual a 1, le agregamos la clase display: none para ocultar el costo unitario
                if (verCosto === '0') {
                    $(".td-costo").css("display", "none");
                }

                if (bloquearCosto === '1') {
                    $(".bloquearCosto").prop("readonly", true);
                }

                if (ultimoCosto == 0) {
                    await $.ajax({
                        url: "/comercial/ventas/api/getCosto",
                        type: "GET",
                        data: {
                            articulo: articleKey,
                            almacen: almacen,
                        },
                        success: function (data) {
                            // console.log(data);

                            if (data != null) {
                                let ultimoCosto =
                                    data.articlesCost_lastCost === null ||
                                    data.articlesCost_lastCost === undefined
                                        ? 0.0
                                        : data.articlesCost_lastCost;
                                let tipoCambio = parseFloat(
                                    $("#nameTipoCambio").val()
                                );
                                let costoFormato = truncarDecimales(
                                    formatoMexico(ultimoCosto / tipoCambio),
                                    2
                                );
                                $(
                                    "#c_unitario-" + articleKey + "-" + result
                                ).val(costoFormato);
                                $(
                                    "#c_unitario-" + articleKey + "-" + result
                                ).change();
                            }
                        },
                    });
                }
                const selectOptions = $("#unid-" + articleKey + "-" + result); //Obtenemos el select para añadirle las multiunidades correspondientes

                await $.ajax({
                    url: "/logistica/compras/api/getMultiUnidad",
                    type: "GET",
                    data: {
                        factorUnidad: articleKey,
                    },
                    success: function (data) {
                        let unidadPorDefecto = articleUnidad;
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

    jQuery("#select-proveedorCondicionPago").trigger("change");





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
        "#select-movimiento, #select-moduleConcept, #select-listaPrecios, #select-proveedorCondicionPago, #select-moneda, #select-moduleCancellation"
    ).select2({
        minimumResultsForSearch: -1,
    });

    jQuery("#select-precioProvee").select2();

    // jQuery("#select-moneda").select2();

    $("#select-precioProvee").change(() => {
        let listaPrecio = $("#select-precioProvee").val();
        let checkTodosArticulos = $("#todosLosArt").is(":checked");
        if (listaPrecio != "") {
            tablaArticulos.ajax
                .url(
                    `/articulos/listaProveedorCompras/${checkTodosArticulos}/${listaPrecio}`
                )
                .load();
        }
    });

    $("#todosLosArt").change(() => {
        let listaPrecio = $("#select-precioProvee").val();
        let checkTodosArticulos = $("#todosLosArt").is(":checked");
        if (listaPrecio != "") {
            tablaArticulos.ajax
                .url(
                    `/articulos/listaProveedorCompras/${checkTodosArticulos}/${listaPrecio}`
                )
                .load();
        }
    });

    $("#ArtExistencia").change(() => {
        let checkArticulosExistentes = $("#ArtExistencia").is(":checked");
        //aquí no importa si la lista de precio esta vacia, ya que si no hay lista de precio, no se mostraran articulos
        tablaArticulos.ajax
            .url(
                `/articulos/articulosConExistencia/${checkArticulosExistentes}`
        )
            .load();
    });

    $("#select-search-hide").change(() => {
        let categoria = $("#select-search-hide").val();
        console.log(categoria);

        if (categoria != "") {
            tablaArticulos.ajax
                .url(`/articulos/categoria/${categoria}`)
                .load();
        }
    });

    $("#select-search-grupo").change(() => {
        let grupo = $("#select-search-grupo").val();

        if (grupo != "") {
            tablaArticulos.ajax.url(`/articulos/grupo/${grupo}`).load();
        }
    });

    $("#select-search-familia").change(() => {
        let familia = $("#select-search-familia").val();

        if (familia != "") {
            tablaArticulos.ajax.url(`/articulos/familia/${familia}`).load();
        }
    });

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

    $("form").keypress(function (e) {
        if (e.which == 13) {
            return false;
        }
    });

    $("#proveedorModal").on("show.bs.modal", function (e) {
        const isInvalid = validateConceptoProveedor();

        if (isInvalid) {
            showMessage(
                "No se ha seleccionado el concepto en el movimiento",
                "error"
            );
            return false;
        }
    });

    jQuery("#select-movimiento").on("change", function (e) {
        let mov = jQuery("#select-movimiento").val();

        if (mov != "Rechazo de Compra") {
            jQuery(".motivoCancelacionDiv").hide();
        } else {
            jQuery(".motivoCancelacionDiv").show();
        }
    });

    $("#articulosModal").on("show.bs.modal", function (e) {
        const isInvalid = validateProveedorArticulo();
        const isInvalid2 = validateAlmacenArticulo();
        const isInvalid3 = validateCantidad();
        const isInvalid4 = validateImporte();

        if (isInvalid) {
            showMessage(
                "No se ha seleccionado al proveedor en el movimiento",
                "error"
            );
        }
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
        if (isInvalid || isInvalid2 || isInvalid3 || isInvalid4) {
            return false;
        }
    });

    const $validator = jQuery("#progressWizard").validate({
        submitHandler: function (form) {
            jsonArticulos();
            form.submit();
        },
        rules: {
            movimientos: {
                required: function () {
                    let movimiento = $("#select-movimiento").val();
                    if (movimiento === "") {
                        return true;
                    }

                    return false;
                },
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
            //el motivo solo sera requerido si el movimiento es Rechazo de Compra
            motivoCancelacion: {
                required: function () {
                    let movimiento = $("#select-movimiento").val();
                    if (movimiento === "Rechazo de Compra") {
                        return true;
                    }

                    return false;
                },
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
            proveedorReferencia: {
                maxlength: 100,
            },
        },
        messages: {
            movimientos: {
                required: function () {
                    let movimiento = $("#select-movimiento").val();
                    if (movimiento === "") {
                        return "Por favor selecciona un movimiento";
                    }
                },
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
            motivoCancelacion: {
                required: function () {
                    let movimiento = $("#select-movimiento").val();
                    if (movimiento === "Rechazo de Compra") {
                        return "Por favor llena este campo";
                    }
                },
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

    // Evento keyUp para cambiar el value de los inputs del proveedor
    $("#proveedorKey").change(function () {
        const isValid = validateConcepto();

        let key = $(this).val() !== " " ? $(this).val() : false;

        if (!isValid) {
            if (key) {
                getProveedorByClave(key);
            } else {
                jQuery("#proveedorFechaVencimiento").val("");
                jQuery("#select-proveedorCondicionPago").val("");
                jQuery("#select-proveedorCondicionPago").change();
                $("#proveedorName").val("");
            }
        } else {
            jQuery("#proveedorKey").val("");
            showMessage(
                "No se ha seleccionado el concepto en el movimiento",
                "error"
            );
        }
    });

    $("#almacenKey").on("change", function () {
        const isValid = validateAlmacenArticulo();

        if (!isValid) {
            let key = $(this).val() !== " " ? $(this).val() : false;

            if (key) {
                getAlmacenByClave(key);
            } else {
                $("#almacenKey").val("");
            }
        } else {
            showMessage(
                "No se ha seleccionado el almacen en el movimiento",
                "error"
            );
        }
    });

    function tipoCondicionPago() {
        let condicionPago =
            jQuery("#select-proveedorCondicionPago").val() !== ""
                ? jQuery("#select-proveedorCondicionPago")
                      .find("option:selected")
                      .text()
                      .trim()
                : false;

        if (
            condicionPago === "CONTADO" ||
            condicionPago === "contado" ||
            condicionPago === "Contado"
        ) {
            let fechaEmision = document.getElementById("fechaEmision").value;
            let fechaActual = moment(fechaEmision).format("YYYY-MM-DD");

            jQuery("#proveedorFechaVencimiento").val(fechaActual);
        } else if (condicionPago) {
            let condicionPago = jQuery("#select-proveedorCondicionPago").val();

            $.ajax({
                url: "/logistica/compras/api/getCondicionPago",
                type: "GET",
                data: {
                    condicionPago: condicionPago,
                },
                success: function (data) {
                    //Validamos la fecha de vencimiento conforme a los dias habiles que maneja la empresa
                    if (Object.keys(data).length > 0) {
                        let fechaActual = moment();
                        let fechaVencimiento = moment(fechaActual).add(
                            data.creditConditions_days,
                            "days"
                        );

                        if (
                            data.creditConditions_typeDays.trim() ==
                                "Naturales" ||
                            data.creditConditions_workDays.trim() == "Todos"
                        ) {
                            fechaVencimiento = moment(fechaActual).add(
                                data.creditConditions_days,
                                "days"
                            );
                        }

                        if (
                            data.creditConditions_typeDays.trim() == "Hábiles"
                        ) {
                            let habilesEmpresa =
                                data.creditConditions_workDays.trim();

                            if (habilesEmpresa == "Lun-Vie") {
                                while (!fechaActual.isAfter(fechaVencimiento)) {
                                    if (
                                        fechaActual.isoWeekday() === 6 ||
                                        fechaActual.isoWeekday() === 7
                                    ) {
                                        fechaVencimiento.add(1, "days");
                                    }
                                    fechaActual.add(1, "days");
                                }
                            }

                            if (habilesEmpresa == "Lun-Sab") {
                                while (!fechaActual.isAfter(fechaVencimiento)) {
                                    if (fechaActual.isoWeekday() === 7) {
                                        fechaVencimiento.add(1, "days");
                                    }
                                    fechaActual.add(1, "days");
                                }
                            }
                        }

                        jQuery("#proveedorFechaVencimiento").val(
                            fechaVencimiento.format("YYYY-MM-DD")
                        );
                    }
                },
            });
        } else {
            jQuery("#proveedorFechaVencimiento").val("");
        }
    }

    async function getProveedorByClave(clave) {
        await $.ajax({
            url: "/logistica/compras/api/getProveedor",
            type: "GET",
            data: {
                proveedor: clave,
            },
            success: function (data) {
                if (Object.keys(data).length > 0) {
                    if (data.providers_money !== null) {
                        jQuery("#select-moneda").val(data.providers_money);
                        jQuery("#select-moneda").change();
                        // jQuery("#select-moneda").attr("readonly", true);
                    } else {
                        jQuery("#select-moneda").val(monedaDefecto);
                        jQuery("#select-moneda").change();
                        // jQuery("#select-moneda").attr("readonly", false);
                    }
                    if (data.providers_creditCondition !== null) {
                        jQuery("#select-proveedorCondicionPago").val(
                            data.providers_creditCondition
                        );
                        jQuery("#select-proveedorCondicionPago").change();
                        // jQuery("#select-proveedorCondicionPago").attr(
                        //     "readonly",
                        //     true
                        // );

                        // jQuery("#select-moneda").val(
                        //     data.providers_money
                        // );
                        // jQuery("#select-moneda").change();
                        // jQuery("#select-moneda").attr(
                        //     "readonly",
                        //     true
                        // );

                        $("#proveedorName").val(data.providers_name);
                        tipoCondicionPago();
                    } else {
                        // jQuery("#select-proveedorCondicionPago").attr(
                        //     "readonly",
                        //     false
                        // );
                        // jQuery("#select-moneda").val(
                        //     data.providers_money
                        // );
                        // jQuery("#select-moneda").change();
                        // jQuery("#select-moneda").attr("readonly", false);
                        $("#proveedorName").val(data.providers_name);
                    }
                    if (data.providers_priceList != null) {
                        if (listaPrecio == null) {
                            $("#select-precioProvee").val(
                                data.providers_priceList
                            );
                        } else {
                            $("#select-precioProvee").val(listaPrecio);
                        }
                        $("#select-precioProvee").trigger("change");
                    } else {
                        //Mostramos la lista por default
                        $("#select-precioProvee").val(1);
                        $("#select-precioProvee").trigger("change");
                    }
                } else {
                    jQuery("#proveedorFechaVencimiento").val("");
                    jQuery("#select-proveedorCondicionPago").val("");
                    jQuery("#select-proveedorCondicionPago").change();
                    // jQuery("#select-money").val("");
                    jQuery("#select-money").change();
                    $("#proveedorName").val("");
                }

                // if (data.providers_money !== 'PESOS') {
                //     jQuery("#select-moneda").val(data.providers_money);
                //     jQuery("#select-moneda").change();
                //     jQuery("#select-moneda").attr("readonly", true);
                // }

                //validamos que la clave del proveedor exista
                if (Object.keys(data).length > 0) {
                    jQuery("#proveedorKey").val(data.providers_key);
                } else {
                    jQuery("#proveedorKey").val("");
                    jQuery("#proveedorKey").change();
                    jQuery("#proveedorKey").attr("readonly", false);
                    //insertar un mensaje de alerta
                    swal({
                        title: "Error",
                        text: "La clave del proveedor no existe",
                        icon: "error",
                        button: "Aceptar",
                    });
                }
            },
        });
    }

    async function getAlmacenByClave(clave) {
        await $.ajax({
            url: "/logistica/compras/api/getAlmacen",
            type: "GET",
            data: {
                almacen: clave,
            },
            success: function (data) {
                console.log(data);

                if (Object.keys(data).length > 0) {
                    jQuery("#almacenKey").val(data.depots_key);
                } else {
                    jQuery("#almacenKey").val("");

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
    jQuery("#timepicker").timepicker({
        defaultTIme: false,
    });
    jQuery("#datepickerInicia").datepicker();
    jQuery("#timepicker2").timepicker({
        defaultTIme: false,
    });
    jQuery("#datepickerInicia2").datepicker();

    const validarSerie = (series, articulo) => {
        const respuesta = $.ajax({
            url: "/logistica/compras/api/verificacionSeries",
            method: "GET",
            data: {
                claveArticulo: articulo,
                series: series,
            },
            success: function ({ status, data }) {
                if (status === 200) {
                    if (data) {
                        showMessage(
                            "Encontramos el mismo artículo con la misma serie",
                            "error"
                        );
                    }
                }
            },
        });

        return respuesta;
    };

    const afectar = function (e) {
        e.preventDefault();
        let validarMov = validateMovimiento();
        if (validarMov) {
            showMessage("Por favor selecciona un movimiento", "error");
            return false;
        }

        let validarCondicion = validateCondicionPago();
        if (validarCondicion) {
            showMessage("Por favor selecciona una condición de pago", "error");
            return false;
        }

        let validarConcepto = validateConcepto();
        if (validarConcepto) {
            showMessage("Por favor selecciona un concepto", "error");
            return false;
        }

        let validarListaPrecios = validateListPrecio();
        if (validarListaPrecios) {
            showMessage("Por favor selecciona una lista de precios", "error");
            return false;
        }

        let validateAmacenActivoFijo = validateAlmacenActivoFijo();
        console.log(validateAmacenActivoFijo);
        if (validateAmacenActivoFijo) {
            console.log("entro");
            showMessage("Por favor verifica que los artículos sean de tipo serie", "error");
            return false;
        }

        let status = $("#status").text().trim();
        let articulos = $("#cantidadArticulos").val();
        let movimiento = $("#select-movimiento").val();
        const isInvalid = validateCantidad();
        const isInvalid2 = validateImporte();

        if (movimiento === "Rechazo de Compra") {
            let motivoCancelacion = validateMotivoCancelacion();
            if (motivoCancelacion) {
                showMessage(
                    "Por favor ingresa un motivo de cancelación",
                    "error"
                );
                return false;
            }
        }

        //si tiene permiso
        if (status === "INICIAL") {
            if (articulos > 0) {
                if (isInvalid || isInvalid2) {
                    showMessage(
                        "La cantidad o el costo no pueden ser menores a cero",
                        "error"
                    );
                } else {
                    swal({
                        title: "¿Está seguro que desea generar la " + movimiento + "?",
                        text: "¡Con el nuevo estatus diferente a INICIAL no podrá realizar cambios!",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                        buttons: ["Cancelar", "Aceptar"],
                    }).then((willDelete) => {
                        if (willDelete) {
                            let listaArticulos = jsonArticulos();

                            $("#loader").show();
                            if (
                                jQuery("#select-movimiento").val() ==
                                    "Entrada por Compra" &&
                                status == "INICIAL"
                            ) {
                                let isSerieVacio = false;
                                let isSerieRepetido = false;
                                let claveArticuloSerie = "";
                                let repetidos = {};
                                const keyListaArticulos =
                                    Object.keys(listaArticulos);

                                keyListaArticulos.forEach((keyArticulo) => {
                                    if (
                                        listaArticulos[keyArticulo]
                                            .tipoArticulo == "Serie"
                                    ) {
                                        if (
                                            listaArticulos[keyArticulo]
                                                .asignacionSerie ===
                                                undefined ||
                                            listaArticulos[keyArticulo]
                                                .asignacionSerie === null
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
                                        } tenga completo sus números de serie`,
                                        "error"
                                    );
                                } else {
                                    keyListaArticulos.forEach((keyArticulo) => {
                                        if (
                                            listaArticulos[keyArticulo]
                                                .tipoArticulo == "Serie"
                                        ) {
                                            listaArticulos[keyArticulo][
                                                "asignacionSerie"
                                            ].forEach((serie) => {
                                                repetidos[serie] =
                                                    (repetidos[serie] || 0) + 1;
                                            });
                                        }
                                    });

                                    let keySeries = Object.keys(repetidos);

                                    keySeries.forEach((keySerie) => {
                                        if (repetidos[keySerie] !== 1) {
                                            showMessage(
                                                "Por favor, ingresa diferentes series",
                                                "error"
                                            );
                                            isSerieRepetido = true;
                                            return false;
                                        }
                                    });
                                }

                                if (!isSerieVacio && !isSerieRepetido) {
                                    $.ajax({
                                        url: "/logistica/afectar",
                                        type: "POST",
                                        data: $("#progressWizard").serialize(),
                                        success: function ({
                                            mensaje,
                                            estatus,
                                            id,
                                        }) {
                                            $("#loader").hide();
                                            if (estatus === 200) {
                                                showMessage2(
                                                    "Afectación exitosa",
                                                    mensaje,
                                                    "success"
                                                );
                                                let ruta = window.location.href;
                                                let ruta2 = ruta.split("/");
                                                if (ruta2.length > 5) {
                                                    ruta + "/" + id;
                                                } else {
                                                    ruta += "/" + id;
                                                }
                                                setTimeout(function () {
                                                    window.location.href = ruta;
                                                }, 1000);
                                            } else {
                                                showMessage2(
                                                    "Error",
                                                    mensaje,
                                                    "error"
                                                );
                                            }
                                        },
                                    });
                                }
                            } else {
                                $.ajax({
                                    url: "/logistica/afectar",
                                    type: "POST",
                                    data: $("#progressWizard").serialize(),
                                    success: function ({
                                        mensaje,
                                        estatus,
                                        id,
                                    }) {
                                        $("#loader").hide();
                                        if (estatus === 200) {
                                            showMessage2(
                                                "Afectación exitosa",
                                                mensaje,
                                                "success"
                                            );
                                            let ruta = window.location.href;
                                            let ruta2 = ruta.split("/");
                                            if (ruta2.length > 5) {
                                                ruta + "/" + id;
                                            } else {
                                                ruta += "/" + id;
                                            }
                                            setTimeout(function () {
                                                window.location.href = ruta;
                                            }, 1000);
                                        } else {
                                            showMessage2(
                                                "Error",
                                                mensaje,
                                                "error"
                                            );
                                        }
                                    },
                                });
                            }
                        }
                    });
                }
            } else {
                showMessage(
                    "No se puede afectar una compra sin artículos",
                    "error"
                );
            }
        } else if (movimiento === "Orden de Compra") {
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

        form.submit();
    });

    jQuery("#btn-modal-compra").click(function (e) {
        e.preventDefault();

        let radioEntradaCompra = jQuery("#generarEntradaCompra").is(":checked");
        let radioCompraRechazada = jQuery("#generarCompraRechazada").is(
            ":checked"
        );

        let movimiento;
        if (radioEntradaCompra === true) {
            movimiento = "Entrada por Compra";
            $("#select-movimiento").val(movimiento);
            $("#idCompra").val("0");

            // $('#select-movimiento').trigger('change');
        }
        if (radioCompraRechazada === true) {
            movimiento = "Rechazo de Compra";
            $("#select-movimiento").val(movimiento);
            $("#idCompra").val("0");
            // $('#select-movimiento').trigger('change');
        }

        //Verificamos si el usuario tiene permisos para afectar
        let $domPermiso = movimiento.replace(/\s/g, "");

        if ($("#" + $domPermiso).val() !== "true") {
            showMessage3(
                "Permisos Insuficientes",
                "No tienes permisos para afectar",
                "warning"
            );
        } else {
            if (radioEntradaCompra === true || radioCompraRechazada === true) {
                $("#modalCompra2").modal({
                    backdrop: "static",
                    keyboard: true,
                    show: true,
                });
            }

            if (
                radioEntradaCompra === false &&
                radioCompraRechazada === false
            ) {
                showMessage("Seleccione una opción", "error");
            }
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
                            "onkeyup"
                        );
                        calcularTotales();
                    }
                });
                jQuery("#progressWizard").submit();
                $("#modalCompra2").modal("hide");
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
                        "onkeyup"
                    );
                    calcularTotales();
                }

                // console.log(filaArticulo, cantidadPendiente);
            });
            $("#progressWizard").submit();
            $("#modalCompra2").modal("hide");
        }
    });

    //Capturamos el evento click del boton del modalSerie
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

            let contador = 1;

            if (idCompra !== "") {
                $.ajax({
                    url: "/logistica/compras/api/getArticulosSerie",
                    method: "GET",
                    data: {
                        idCompra: idCompra,
                        claveArticulo: identificadorFila.split("-")[0],
                        id: idArticulo,
                        limit: cantidad,
                    },
                    success: function ({ status, data }) {
                        console.log(data);
                        let estado = $("#status").text();
                        if (status === 200) {
                            //Agremos los inputs de acuerdo a la cantidad ingresada por el usuario
                            if (data.length > 0) {
                                data.forEach((articuloSerie) => {
                                    cuerpoModal.append(
                                        `<div class="col-md-2">
                                        <span><strong>Articulo :</strong></span>
                                    </div>
                                    <div class="col-md-10 " style="margin-bottom: 5px;">
    
                                        <input type="text" class="form-control ${identificadorFila} ${
                                            articuloSerie.lotSeriesMov_id
                                        }" id="serie" name="serie[]"
                                            placeholder="Ingrese la serie del articulo"
                                                value="${
                                                    articuloSerie.lotSeriesMov_lotSerie
                                                }"
                                                ${
                                                    estado === "FINALIZADO" ||
                                                    estado === "CANCELADO"
                                                        ? "disabled"
                                                        : ""
                                                }
                                            >
                                    </div>`
                                    );
                                });

                                while (data.length < cantidad) {
                                    cuerpoModal.append(
                                        `<div class="col-md-2">
                                        <span><strong>Articulo :</strong></span>
                                    </div>
                                    <div class="col-md-10 " style="margin-bottom: 5px;">
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
                                            >
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
                                    <div class="col-md-10 " style="margin-bottom: 5px;">
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
                                            >
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
                    <div class="col-md-10 " style="margin-bottom: 5px;">
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

    const eliminar = function (e) {
        e.preventDefault();

        const id = $("#idCompra").val();

        if (id === "0") {
            showMessage("No se ha seleccionado ninguna compra", "error");
        } else {
            swal({
                title: "¿Está seguro de eliminar la compra?",
                text: "Una vez eliminada no podrá recuperarla",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                if (willDelete) {
                    $("#loader").show();
                    $.ajax({
                        url: "/eliminarCompra",
                        type: "get",
                        data: { id: id },
                        success: function ({ estatus, mensaje }) {
                            $("#loader").hide();
                            if (estatus === 200) {
                                showMessage2(
                                    "Eliminacion exitosa",
                                    mensaje,
                                    "success"
                                );
                                setTimeout(function () {
                                    window.location.href = "/logistica/compra";
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

        if (
            (status === "FINALIZADO" && movimiento === "Entrada por Compra") ||
            (status === "FINALIZADO" && movimiento === "Rechazo de Compra")
        ) {
            swal({
                title: "¿Está seguro de cancelar la compra?",
                text: movimiento + " : " + folio + "",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                if (willDelete) {
                    $("#loader").show();
                    $.ajax({
                        url: "/cancelarCompra/",
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
                                        "/logistica/compra/" + id;
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
        } else if (status === "POR AUTORIZAR" && movimiento === "Orden de Compra") {
            const id = $("#idCompra").val();
            swal({
                title: "¿Está seguro de cancelar la compra?",
                text: movimiento + " : " + folio + "",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "/cancelarOrden/",
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
                                        "/logistica/compra/" + id;
                                }, 1000);
                            } else {
                                showMessage2(
                                    "Error al cancelar",
                                    mensaje,
                                    "error"
                                );
                            }

                            if (estatus === 500) {
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
                            window.location.href = "/logistica/compra";
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
                                        "/logistica/compra/" + id;
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

    //Generador de series
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

        if (dataCompraFlujo !== "") {
            $("#loadingFlujo").show();

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
    let editor;
    ClassicEditor
        .create(document.querySelector('#especificacionesArticulo'), {
            language: 'es',
            toolbar: {
                items: []
            },
            readOnly: true
        })
        .then(newEditor => {
            editor = newEditor;
        })
        .catch(error => {
            console.error(error);
        });


    //Costo promedio
    let articulo = 0;
    let compra = 0;
    let Proveedor = 0;
    let entro = false;
    $('input[id^="keyArticulo-"]').each(function (index, input) {
        $(input).focus(function (e) {
            let id = $(input).attr("id").split("-")[1];
            compra = $("#idCompra").val();
            proveedor = $("#proveedorKey").val();
            articulo = id;
            entro = true;
        });
    });


    

    $("#costoPromedio").click(function () {
        if (entro) {
            $("#loading2").show();
            // console.log("#loader");
            console.log(articulo);
            $.ajax({
                url: "/getCostoPromedio",
                method: "GET",
                data: {
                    id: articulo,
                    idCompra: compra,
                    idProveedor: proveedor,
                },
                success: function ({ estatus, data, articulosByAlmacen, listaProveedor }) {
                    console.log(estatus, data, articulosByAlmacen, listaProveedor);
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
                            if (data.articles_specifications == null) {
                                editor.setData("<b>Sin especificaciones</b>");
                            } else {
                                // editor.setData("");
                                editor.setData(data.articles_specifications);
                            }


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
                            $("#ultimoCostoPromedio").val("$" + ultimoCosto);
                            $("#ultimoCostoPromedio2").val(
                                "$" + ultimoCostoPromedio
                            );
                            let formatInventario = "";

                            //limpiamos la tabla tableAlmacenesDisponibles
                            $(".tableAlmacenesDisponibles").html("");
                            articulosByAlmacen.forEach((element, index) => {
                                formatInventario = currency(
                                    element.articlesInv_inventory,
                                    {
                                        separator: ",",
                                        precision: 2,
                                        symbol: "",
                                    }
                                ).format();

                                //agregamos los datos a la tabla
                                $(".tableAlmacenesDisponibles").append(`
                                    <tr>
                                <td>${element.depots_name}</td>
                                <td style="margin-right: 30px">${formatInventario}</td>
                                </tr>
                                `);
                                // $("#almacenCostoPromedio"+index).text(
                                //     element.depots_name
                                // );
                                // $("#inventarioCostoPromedio"+index).text(
                                //     formatInventario
                                // );
                            });
                            if(listaProveedor != null){

                              let  formatCost = currency(
                                listaProveedor.articlesList_lastCost,
                                    {
                                        separator: ",",
                                        precision: 2,
                                        symbol: "$",
                                    }
                                ).format();

                                $("#costoProveedor").val(formatCost);
                            }else{
                                $("#costoProveedor").val("");
                            }
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
        $("#siguiente").removeClass("active");

        $("#li1").addClass("active");
        $("#li2").removeClass("active");
        $("#li3").removeClass("active");
        $("#li4").removeClass("active");
    });


    //Aqui comienza el flujo
    let primerFlujo = $("#movimientoFlujo").val();

    if (primerFlujo !== "") {
        let bodyTable = $("#movimientosTr");

        let jsonFlujo = JSON.parse(primerFlujo);
        generarTablaFlujo(bodyTable, jsonFlujo);
        console.log(jsonFlujo);
    }

    function generarTablaFlujo(bodyTable, jsonFlujo) {
        jsonFlujo.forEach(async (element) => {
            let tr = document.createElement("tr");
            let dataOrigen = "";
            let destino = "";
            let cancelarOrigen = "";
            let cancelarDestino = "";

            if (element.movementFlow_cancelled === "1") {
                await $.ajax({
                    url: "/status/movimiento",
                    method: "GET",
                    data: {
                        folioO: element.movementFlow_movementOriginID,
                        dbO: movimientosArray[
                            element.movementFlow_moduleOrigin
                        ],
                        movimientoO: element.movementFlow_movementOrigin,
                        folioD: element.movementFlow_movementDestinityID,
                        dbD: movimientosArray[
                            element.movementFlow_moduleDestiny
                        ],
                        movimientoD: element.movementFlow_movementDestinity,
                        sucursal: element.movementFlow_branch,
                        empresa: element.movementFlow_company,
                    },
                    success: function ({ status, data }) {
                        if (status) {
                            if (data.statusOrigen === "CANCELADO") {
                                cancelarOrigen =
                                    '<span class="badge badge-danger">C</span>';
                            }

                            if (data.statusDestino === "CANCELADO") {
                                cancelarDestino =
                                    '<span class="badge badge-danger">C</span>';
                            }
                        }
                    },
                });

                dataOrigen =
                    cancelarOrigen +
                    " " +
                    element.movementFlow_movementOrigin +
                    " " +
                    element.movementFlow_movementOriginID;
                destino =
                    cancelarDestino +
                    " " +
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
    if (movimiento !== "") {
        let movimientoSinEspacios = movimiento.replace(/\s+/g, "");

        if ($("#" + movimientoSinEspacios).length === 0) {
            $("#afectar-boton").hide();
            $("#cancelar-boton").hide();
            $("#crearMov").hide();
        }
    }

    if (
        $("#status").text().trim() === "FINALIZADO" ||
        $("#status").text().trim() === "CANCELADO" ||
        $("#status").text().trim() === "POR AUTORIZAR"
    ) {
        $("#crearMov").hide();
    }
}); //Fin de document ready

function disabledCompra() {
    let status = $("#status").text().trim();
    let movimiento = $("#select-movimiento").val();
    if (status !== "INICIAL") {
        $("#articleItem")
            .find("input[type='number'], select")
            .attr("readonly", true);
        $("#articleItem").find("select").attr("disabled", true);
        //desactivamos el boton de agregar articulo
        $("#modalSerie").attr("disabled", false);
        $(".addArticle").attr("disabled", true);
        //desactivamos los input de serie y lote
        $("#serie").attr("readonly", true);
        $("#generarLotesSeries").hide();
        $("#modal6Agregar").hide();
        $("#fechaEmision").attr("readonly", true);
        $('input[id^="canti-"]').attr("readonly", true);
        $('input[id^="keyArticulo-"]').attr("readonly", true);
        $('input[id^="c_unitario-"]').attr("readonly", true);
        $(".eliminacion-articulo").hide();
        $("#select-movimiento").attr("readonly", true);
        $("#select-moneda").attr("readonly", true);
        $("#proveedorFechaVencimiento").attr("readonly", true);
        $("#fechaEmision").attr("readonly", true);
        $("#nameTipoCambio").attr("readonly", true);
        $("#select-moduleConcept").attr("readonly", true);
        $("#proveedorKey").attr("readonly", true);
        $("#provedorModal").attr("disabled", true);
        $("#select-proveedorCondicionPago").attr("readonly", true);
        $("#select-moduleCancellation").attr("readonly", true);
        $("#proveedorReferencia").attr("readonly", true);
        $("#almacenKey").attr("readonly", true);
        $("#almacenModal").attr("disabled", true);
        $("#folioTicket").attr("readonly", true);
        $("#operador").attr("readonly", true);
        $("#placas").attr("readonly", true);
        $("#material").attr("readonly", true);
        $("#pesoEntrada").attr("readonly", true);
        $("#fechaEntradaDatos").attr("readonly", true);
        $("#pesoSalida").attr("readonly", true);
        $("#fechaSalida").attr("readonly", true);
        $("#select-precioProvee").attr("readonly", true);


        $('input[id^="keyArticulo-"]').removeAttr("onkeyup");

        $("#botonesWizard").hide();
        if (
            movimiento === "Entrada por Compra" ||
            movimiento === "Rechazo de Compra"
        ) {
            $(".accion-pendiente").hide();
            $(".accion-recibir").hide();
        }
    } else {
        $(".accion-pendiente").hide();
        $(".accion-recibir").hide();
    }

    if ($(".aplicaA").is(":visible") && $(".aplicaIncre").is(":visible")) {
        $('input[id^="keyArticulo-"]').attr("readonly", true);
        $('input[id^="keyArticulo-"]').removeAttr("onkeyup");
    }
}

//Evento para calcular la cantidad por el factor de la unidad (usamos la clave y la unidad de compra asignada)
function changeCantidadInventario(clave, posicion) {
    let decimal = $("#decimales-" + clave + "-" + posicion).val();
    let status = $("#status").text().trim();

    // console.log(decimal, status);
    if (decimal !== "" && status !== "FINALIZADO") {
        operacionCantidadInventario(clave, posicion, decimal);
        changeCostoImporte(clave, posicion, decimal);
        importeIva(clave, posicion);
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
                    // console.log();
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
        let truncarImporte = truncarDecimales(importe, 4);
        $("#c_unitario-" + clave + "-" + posicion).val(
            formatoMexico(truncarImporte)
        );
    }

    changeCostoImporte(clave, posicion);
    importeIva(clave, posicion);
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
                      .replace(/[^0-9,.]/g, "")
                      .replace(/,/g, ".")
              )
            : "";

    if (isNaN(inputCantidad)) {
        inputCantidad = "";
        $("#canti-" + clave + "-" + posicion).val("");
    }

    // console.log(inputCantidad);
    // console.log(clave, posicion, decimal, inputCantidad);

    if (inputCantidad !== "") {
        // console.log(inputCantidad);
        let inputFactor = $("#unid-" + clave + "-" + posicion).val();
        let inputFactorArray = inputFactor.trim().split("-");
        let cantidadInventario = $("#c_Inventario-" + clave + "-" + posicion);

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
    let inputImporte = $("#importe-" + clave + "-" + posicion);

    if (
        inputCosto !== "" &&
        inputCosto > 0 &&
        inputCantidad !== "" &&
        inputCantidad > 0
    ) {
        let importe = inputCosto * inputCantidad;
        let importeFormato = truncarDecimales(formatoMexico(importe), 2);
        let inputCostoFormato = formatoMexico(inputCosto);

        $("#c_unitario-" + clave + "-" + posicion).val(inputCostoFormato);

        inputImporte.val(formatoMexico(importeFormato));
        descuentoLineal(clave, posicion);
    } else {
        inputImporte.val("");
    }
}

//colocar comas a los numeros
const formatoMexico = (number) => {
    const exp = /(\d)(?=(\d{3})+(?!\d))/g;
    const rep = "$1,";
    let arr = number.toString().split(".");
    arr[0] = arr[0].replace(exp, rep);
    return arr[1] ? arr.join(".") : arr[0];
};

function round(num) {
    var m = Number((Math.abs(num) * 100).toPrecision(15));
    return (Math.round(m) / 100) * Math.sign(num);
}

//Operacion calcular el importe iva
function importeIva(clave, posicion) {
    let inputImporte = $("#importe-" + clave + "-" + posicion);
    let importe = parseFloat(inputImporte.val().replace(/[$,]/g, ""));

    let iva = parseFloat(
        $("#iva-" + clave + "-" + posicion)
            .val()
            .replace(/[$,]/g, "")
    );
    let porcentajeiVA;
    let importeIva;
    if (iva > 0) {
        porcentajeiVA = iva / 100;
        importeIva = importe * porcentajeiVA;
    } else {
        porcentajeiVA = 1;
        importeIva = 0;
    }

    if (importe !== NaN && importe > 0) {
        let importeIvaFormato = truncarDecimales(round(importeIva), 2);
        $("#importe_iva-" + clave + "-" + posicion).val(
            formatoMexico(importeIvaFormato)
        );
    } else {
        $("#importe_iva-" + clave + "-" + posicion).val("");
    }
}

function descuentoLineal(clave, posicion) {
    let inputImporte = $("#importe-" + clave + "-" + posicion);
    let importe = parseFloat(
        inputImporte
            .val()
            .replace(/[$,]/g, "")
            .replace(/[^0-9,.]/g, "")
            .replace(/,/g, ".")
    );

    let inputDescuento = $("#porDesc-" + clave + "-" + posicion);
    let descuento = parseFloat(
        inputDescuento
            .val()
            .replace(/[$,]/g, "")
            .replace(/[^0-9,.]/g, "")
            .replace(/,/g, ".")
    );

    if (isNaN(descuento)) {
        descuento = "";
        inputDescuento.val(descuento);
    } else {
        inputDescuento.val(descuento);
    }

    let inputDescuentoImporte = $("#descuento-" + clave + "-" + posicion);

    // console.log(importe, descuento, inputDescuentoImporte);
    if (descuento > 0) {
        let descuentoImporte = (importe * descuento) / 100;
        let descuentoImporteFormato = truncarDecimales(descuentoImporte, 2);
        inputDescuentoImporte.val(formatoMexico(descuentoImporteFormato));
        importeTotal(clave, posicion);
    } else {
        inputDescuentoImporte.val("");
        importeTotal(clave, posicion);
    }
}

//Operacion para hallar el importe total
function importeTotal(clave, posicion) {
    let inputImporte = $("#importe-" + clave + "-" + posicion);
    let inputImporteIva = $("#importe_iva-" + clave + "-" + posicion);
    let importe = parseFloat(inputImporte.val().replace(/[$,]/g, ""));
    let inputDescuento = $("#descuento-" + clave + "-" + posicion);
    let descuento;

    descuento = parseFloat(inputDescuento.val().replace(/[$,]/g, ""));

    let importeIva = parseFloat(inputImporteIva.val().replace(/[$,]/g, ""));

    let importeTotal = importe + importeIva;

    if (descuento > 0) {
        importeTotal = importeTotal - descuento;
    }

    let resultado = importeTotal;

    if (importe !== NaN && importe > 0 && importeIva !== NaN) {
        let resultadoFormato = truncarDecimales(round(resultado), 2);

        $("#importe_total-" + clave + "-" + posicion).val(
            formatoMexico(resultadoFormato)
        );
        calcularTotales();
        jsonArticulos();
    } else {
        $("#importe_total-" + clave + "-" + posicion).val("");
        $("#subTotalCompleto").val("0.00");
        $("#totalDescuento").val("0.00");
        $("#impuestosCompleto").val("0.00");
        $("#totalCompleto").val("0.00");
    }
}

//Operacion para hallar los totales
function calcularTotales() {
    let subTotal = 0;
    let iva = 0;
    let total = 0;
    let descuento = 0;

    $('input[id^="importe-"]').each(function (index, value) {
        let idIntputs = $(this).attr("id");
        let importes = $("#" + idIntputs)
            .val()
            .replace(/[$,]/g, "");

        if (importes !== "" && importes > 0) {
            subTotal += parseFloat(importes);
        }
    });

    $('input[id^="importe_iva-"]').each(function (index, value) {
        let idIntputs = $(this).attr("id");
        let impuestos = $("#" + idIntputs)
            .val()
            .replace(/[$,]/g, "");
        if (impuestos !== "" && impuestos > 0) {
            iva += parseFloat(impuestos);
        }
    });

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

    $('input[id^="descuento-"]').each(function (index, value) {
        let idIntputs = $(this).attr("id");
        let descuentos = parseFloat(
            $("#" + idIntputs)
                .val()
                .replace(/[$,]/g, "")
        );
        if (descuentos !== "" && descuentos > 0) {
            descuento += parseFloat(descuentos);
        }
    });

    // console.log(subTotal, iva, total);
    let subTotalCompleto = truncarDecimales(round(subTotal), 2);
    let impuestosCompleto = truncarDecimales(round(iva), 2);
    let totalCompleto = truncarDecimales(round(subTotal + iva), 2);
    let totalDescuento = truncarDecimales(round(descuento), 2);

    $("#subTotalCompleto").val("$" + formatoMexico(subTotalCompleto));
    $("#impuestosCompleto").val("$" + formatoMexico(impuestosCompleto));
    $("#totalCompleto").val("$" + formatoMexico(totalCompleto));
    $("#totalDescuento").val("$" + formatoMexico(totalDescuento));
}

function validateMovimiento() {
    const estadoConcepto = $("#select-movimiento").val() === "" ? true : false;

    if (estadoConcepto) {
        return true;
    } else {
        return false;
    }
}

function validateConcepto() {
    const estadoConcepto =
        $("#select-moduleConcept").val() === "" ? true : false;

    if (estadoConcepto) {
        return true;
    } else {
        return false;
    }
}

//hacemos una función para validar que si el almacén es de tipo Serie que no se puedan agregar articulos sin serie


function validateCondicionPago() {
    const estadoConcepto =
        $("#select-proveedorCondicionPago").val() === "" ||
        $("#select-proveedorCondicionPago").val() === null
            ? true
            : false;

    if (estadoConcepto) {
        return true;
    } else {
        return false;
    }
}

//Validamos si algun input de cantidad esta vacio
function validateCantidad() {
    let estado = false;
    $('input[id^="canti-"]').each(function (index, value) {
        let idIntputs = $(this).attr("id");
        let tieneClaveArticulo = idIntputs.split("-");
        let cantidad = $("#" + idIntputs).val();
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

function validateAlmacenActivoFijo() {
  let estado = false; // Establecer el estado inicial en verdadero
  let tipoAlmacen = $("#almacenTipoKey").val();

  $('input[id^="tipoArticulo-"]').each(function (index, value) {
    let tipoArticulo = $(this).val();
    console.log(tipoArticulo, tipoAlmacen);

    if (tipoArticulo !== "Serie" && tipoAlmacen === "Activo Fijo") {
      estado = true; // Si alguna condición no se cumple, establecer el estado en falso
      return false; // Salir del bucle each
      }
  });

  console.log(estado);
  return estado;
}





function validateMotivoCancelacion() {
    const estadoMotivo =
        $("#select-moduleCancellation").val() === "" ? true : false;

    if (estadoMotivo) {
        return true;
    } else {
        return false;
    }
}

//Validamos si algun input de importe esta vacio

function validateImporte() {
    let estado = false;
    $('input[id^="importe-"]').each(function (index, value) {
        let idIntputs = $(this).attr("id");
        let tieneClaveArticulo = idIntputs.split("-");
        let cantidad = $("#" + idIntputs).val();
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
            let c_unitario = $("#c_unitario-" + key + "-" + identificador[2])
                .val()
                .replace(/[$,]/g, "");
            let importe = $("#importe-" + key + "-" + identificador[2])
                .val()
                .replace(/[$,]/g, "");
            let iva = $("#iva-" + key + "-" + identificador[2]).val();
            let importe_iva = $("#importe_iva-" + key + "-" + identificador[2])
                .val()
                .replace(/[$,]/g, "");
            let importe_total = $(
                "#importe_total-" + key + "-" + identificador[2]
            )
                .val()
                .replace(/[$,]/g, "");
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

            let porcentajeDescuento = $(
                "#porDesc-" + key + "-" + identificador[2]
            ).val();

            let descuento = $(
                "#descuento-" + key + "-" + identificador[2]
            ).val();

            if (tipoArticulo === "Normal" || tipoArticulo === "Servicio") {
                articulosLista = {
                    ...articulosLista,
                    [key + "-" + identificador[2]]: {
                        id: id,
                        cantidad: cantidad,
                        unidad: unidad,
                        c_unitario: c_unitario,
                        importe: importe,
                        iva: iva,
                        importe_iva: importe_iva,
                        porcentajeDescuento: porcentajeDescuento,
                        descuento: descuento,
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
                        importe: importe,
                        iva: iva,
                        importe_iva: importe_iva,
                        porcentajeDescuento: porcentajeDescuento,
                        descuento: descuento,
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
                        aplicaIncre: aplicaIncre,
                    },
                };
            }
        }
    });

    inputSaveArticulo.attr("name", "dataArticulosJson");
    inputSaveArticulo.attr("value", JSON.stringify(articulosLista));

    return articulosLista;
}

//Luis - Validaciones de compras

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

//funcion para validar el almacen
function validateAlmacenArticulo() {
    const estadoAlmacen = $("#almacenKey").val() === "" ? true : false;

    if (estadoAlmacen) {
        return true;
    } else {
        return false;
    }
}

function validateListPrecio() {
    const estadoListaPrecio =
        $("#select-precioProvee").val() === "" ? true : false;

    if (estadoListaPrecio) {
        return true;
    } else {
        return false;
    }
}
//funcion ajax para comprobar que almacenKey no este vacio y exista en la base de datos

function showMessage(mensaje, icon) {
    swal(mensaje, {
        button: "OK",
        icon: icon,
    });

    $("#loader").hide();
}

function showMessage2(titulo, mensaje, icon) {
    swal({
        title: titulo,
        text: mensaje,
        icon: icon,
    });
    $("#loader").hide();
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

    $("#loader").hide();
}

async function buscadorArticulos(filaReferencia) {
    let tipoMov = $("#select-movimiento").val();
    let estatus = jQuery("#status").text().trim();
    let value = jQuery("#" + filaReferencia).val();
    let almacen = $("#almacenKey").val();

    const isInvalid = validateProveedorArticulo();
    const isInvalid2 = validateAlmacenArticulo();
    const isInvalid3 = validateListPrecio();

    if (isInvalid) {
        showMessage(
            "No se ha seleccionado al proveedor en el movimiento",
            "error"
        );
    }
    if (isInvalid2) {
        showMessage(
            "No se ha seleccionado el almacén en el movimiento",
            "error"
        );
    }

    if (isInvalid3) {
        showMessage("No se ha seleccionado la lista del proveedor", "error");
    }

    if (isInvalid || isInvalid2) {
        jQuery("#" + filaReferencia).val("");
        return false;
    }

    let listaPrecioB = $("#select-precioProvee").val();


    await $.ajax({
        url: "/herramientas/buscar_articulo",
        method: "GET",
        data: {
            clave: value,
            listPrecio: listaPrecioB,
        },
        success: function ({ status, data }) {
            if (status) {
                let {
                    articles_key: articleKey,
                    articles_type: tipo,
                    articles_descript: articleName,
                    articles_porcentIva: articleIva,
                    unidad: articleUnidad,
                    articlesList_lastCost: ultimoCost,
                } = data;

                if (calculoImpuestos === "0") {
                    articleIva = articleIva;
                } else {
                    articleIva = "";
                }

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
                               <td id="btnInput"><input name="dataArticulos[]" id="keyArticulo-${articleKey}-${result}" type="text" value="${articleKey}" class="keyArticulo"   title="${articleKey}" onchange="buscadorArticulos('keyArticulo-${articleKey}-${result}')" />
               
                               <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal3">...</button>

                               ${
                                   tipo === "Serie" &&
                                   tipoMov == "Entrada por Compra" &&
                                   estatus == "INICIAL"
                                       ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal6" id="modalSerie">S</button>'
                                       : ""
                               }
               
                               </td>
                               <td><input name="dataArticulos[]" id="desp-${articleKey}-${result}" type="text" class="botonesArticulos" value='${articleName}' readonly title="${articleName}"></td>
                               <td>
                                       <input name="dataArticulos[]" id="canti-${articleKey}-${result}" type="number" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value=''>
                               </td>
                               <td class="td-costo"><input name="dataArticulos[]" id="c_unitario-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones bloquearCosto" value='${
                                ultimoCost == undefined ? 0 : ultimoCost
                            }' onchange="calcularImporte('${articleKey}', '${result}')"></td>
                               <td>
                                   <select name="dataArticulos[]" id="unid-${articleKey}-${result}" class="botonesArticulos" value='' onchange="recalcularCantidadInventario('${articleKey}', '${result}')" >
                                   </select>
                               </td>
                               <td><input name="dataArticulos[]" id="c_Inventario-${articleKey}-${result}" type="number" class="botonesArticulos sinBotones" value='' readonly></td>
                               <td><input name="dataArticulos[]" id="importe-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                               <td><input name="dataArticulos[]" id="porDesc-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="descuentoLineal('${articleKey}', '${result}')" value=''></td>
                               <td><input name="dataArticulos[]" id="descuento-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                               <td><input name="dataArticulos[]" id="iva-${articleKey}-${result}" type="number" class="botonesArticulos sinBotones" value='${articleIva}' readonly></td>
                               <td><input name="dataArticulos[]" id="importe_iva-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                               <td><input name="dataArticulos[]" id="importe_total-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
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
                                <td id="btnInput"><input name="dataArticulos[]" id="keyArticulo-${articleKey}-${result}" type="text" class="keyArticulo" value='${articleKey}' onchange="buscadorArticulos('keyArticulo-${articleKey}-${result}')" />
                
                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal3">...</button>
                                     ${
                                         tipo === "Serie" &&
                                         tipoMov == "Entrada por Compra" &&
                                         estatus == "INICIAL"
                                             ? '<button type="button" class="btn btn-warning btn-sm modalSerie" data-toggle="modal" data-target=".modal6" id="modalSerie">S</button>'
                                             : ""
                                     }
               
                                </td>
                                <td><input name="dataArticulos[]" id="desp-${articleKey}-${result}" type="text" class="botonesArticulos" value='${articleName}' readonly title="${articleName}"></td>
                                <td>
                                        <input name="dataArticulos[]" id="canti-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value=''>
                                </td>
                                <td class="td-costo"><input name="dataArticulos[]" id="c_unitario-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones bloquearCosto" value='${
                                    ultimoCost == undefined ? 0 : ultimoCost
                                }' onchange="calcularImporte('${articleKey}', '${result}')"></td>
                                <td>
                                    <select name="dataArticulos[]" id="unid-${articleKey}-${result}" class="botonesArticulos" value='' onchange="recalcularCantidadInventario('${articleKey}', '${result}')" >
                                    </select>
                                </td>
                                <td><input name="dataArticulos[]" id="c_Inventario-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                                <td><input name="dataArticulos[]" id="importe-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                                <td><input name="dataArticulos[]" id="porDesc-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="descuentoLineal('${articleKey}', '${result}')" value=''></td>
                                <td><input name="dataArticulos[]" id="descuento-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                                <td><input name="dataArticulos[]" id="iva-${articleKey}-${result}" type="number" class="botonesArticulos sinBotones" value='${articleIva}' readonly></td>
                                <td><input name="dataArticulos[]" id="importe_iva-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                                <td><input name="dataArticulos[]" id="importe_total-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
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

                let verCosto = $("#verCosto").val();
                let bloquearCosto = $("#bloquearCosto").val();
                console.log(verCosto, bloquearCosto);

                //si ver costo es igual a 1, le agregamos la clase display: none para ocultar el costo unitario
                if (verCosto === '0') {
                    $(".td-costo").css("display", "none");
                }

                if (bloquearCosto === '1') {
                    $(".bloquearCosto").prop("readonly", true);
                }


                let selectOptions = $("#unid-" + articleKey + "-" + result); //Obtenemos el select para añadirle las multiunidades correspondientes

                $.ajax({
                    url: "/logistica/compras/api/getMultiUnidad",
                    type: "GET",
                    data: {
                        factorUnidad: articleKey,
                    },
                    success: function (data) {
                        let unidadPorDefecto = articleUnidad;
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
                            selectOptions.val(
                                unidadPorDefecto +
                                    "-" +
                                    unidadPorDefectoIndex.articlesUnits_factor
                            );
                        }

                        selectOptions.change();
                    },
                });

                if (ultimoCost == undefined) {
                    $.ajax({
                        url: "/comercial/ventas/api/getCosto",
                        type: "GET",
                        data: {
                            articulo: articleKey,
                            almacen: almacen,
                        },
                        success: function (data) {
                            if (data != null) {
                                let ultimoCosto =
                                    data.articlesCost_lastCost === null ||
                                    data.articlesCost_lastCost === undefined
                                        ? 0.0
                                        : data.articlesCost_lastCost;

                                // console.log(data.articlesCost_lastCost);
                                let tipoCambio = parseFloat(
                                    $("#nameTipoCambio").val()
                                );
                                let costoFormato = truncarDecimales(
                                    formatoMexico(ultimoCosto / tipoCambio),
                                    2
                                );
                                $(
                                    "#c_unitario-" + articleKey + "-" + result
                                ).val(costoFormato);
                                $(
                                    "#c_unitario-" + articleKey + "-" + result
                                ).change();
                            }
                            result++;
                        },
                    });
                }

                contadorArticulos++;
                $("#cantidadArticulos").val(contadorArticulos);
            } else {
                //limpiamos los inputs de referencia
                if (filaReferencia !== "keyArticulo") {
                    let posicion = filaReferencia.split("-")[2];
                    let articulo = filaReferencia.split("-")[1];
                    $("#canti-" + articulo + "-" + posicion).val("");
                    $("#c_unitario-" + articulo + "-" + posicion).val("");
                    $("#c_Inventario-" + articulo + "-" + posicion).val("");
                    $("#importe-" + articulo + "-" + posicion).val("");
                    $("#porDesc-" + articulo + "-" + posicion).val("");
                    $("#descuento-" + articulo + "-" + posicion).val("");
                    $("#importe_iva-" + articulo + "-" + posicion).val("");
                    $("#importe_total-" + articulo + "-" + posicion).val("");
                    $("#unid-" + articulo + "-" + posicion).val("");
                    $("#desp-" + articulo + "-" + posicion).val("");
                    $("#iva-" + articulo + "-" + posicion).val("");
                    $("#unid-" + articulo + "-" + posicion).change();
                }
            }
        },
    });
}

//funcion para detectar los comandos de teclado
$(document).keydown(function (e) {
    if (e.ctrlKey && e.keyCode === 40) {
        let tipoMov = $("#select-movimiento").val();
        if ($("#status").text().trim() !== "INICIAL") {
            return false;
        }

        if (tipoMov === "Rechazo de Compra") {
            return false;
        }

        const isInvalid = validateProveedorArticulo();
        const isInvalid2 = validateAlmacenArticulo();

        if (isInvalid) {
            showMessage(
                "No se ha seleccionado al proveedor en el movimiento",
                "error"
            );
        }
        if (isInvalid2) {
            showMessage(
                "No se ha seleccionado el almacén en el movimiento",
                "error"
            );
        }

        if (isInvalid || isInvalid2) {
            return false;
        }

        if ($(".td-aplica").is(":visible")) {
            $("#articleItem").append(`
                   <tr id="${result}">
                   <td class=""></td>
                   <td id="btnInput"><input name="dataArticulos[]" id="keyArticulo-${result}" type="text" class="keyArticulo" value='' title="" onchange="buscadorArticulos('keyArticulo-${result}')" />
               
                   <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal3">...</button>

                   </td>
                   <td><input name="dataArticulos[]" id="desp-${result}" type="text" class="botonesArticulos" value='' readonly title=""></td>
                   <td>
                           <input name="dataArticulos[]" id="canti-${result}" type="text" class="botonesArticulos sinBotones" value=''>
                   </td>
                   <td class="td-costo"><input name="dataArticulos[]" id="c_unitario-${result}" type="any" class="botonesArticulos sinBotones bloquearCosto" value=''></td>
                   <td>
                       <select name="dataArticulos[]" id="unid-${result}" class="botonesArticulos" value='' >
                       </select>
                   </td>
                   <td><input name="dataArticulos[]" id="c_Inventario-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                   <td><input name="dataArticulos[]" id="importe-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                   <td><input name="dataArticulos[]" id="porDesc-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                   <td><input name="dataArticulos[]" id="descuento-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                   <td><input name="dataArticulos[]" id="iva-${result}" type="number" class="botonesArticulos sinBotones" value='' readonly></td>
                   <td><input name="dataArticulos[]" id="importe_iva-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                   <td><input name="dataArticulos[]" id="importe_total-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                   <td
                           style="display: flex; justify-content: center; align-items: center" class='eliminacion-articulo' onclick="eliminarArticulo('ninguno', '${result}')">
                           <i class="fa fa-trash-o"  aria-hidden="true"
                               style="color: red; font-size: 25px; cursor: pointer;" ></i>
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
                    <td id="btnInput"><input name="dataArticulos[]" id="keyArticulo-${result}" type="text" class="keyArticulo" value='' onchange="buscadorArticulos('keyArticulo-${result}')" />
                
                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal3">...</button>

                    </td>
                    <td><input name="dataArticulos[]" id="desp-${result}" type="text" class="botonesArticulos" value='' readonly title=""></td>
                    <td>
                            <input name="dataArticulos[]" id="canti-${result}" type="text" class="botonesArticulos sinBotones" value=''>
                    </td>
                    <td class="td-costo"><input name="dataArticulos[]" id="c_unitario-${result}" type="any" class="botonesArticulos sinBotones bloquearCosto" value='' ></td>
                    <td>
                        <select name="dataArticulos[]" id="unid-${result}" class="botonesArticulos" value=''>
                        </select>
                    </td>
                    <td><input name="dataArticulos[]" id="c_Inventario-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                    <td><input name="dataArticulos[]" id="importe-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                    <td><input name="dataArticulos[]" id="iva-${result}" type="number" class="botonesArticulos sinBotones" value='' readonly></td>
                    <td><input name="dataArticulos[]" id="porDesc-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                    <td><input name="dataArticulos[]" id="descuento-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                    <td><input name="dataArticulos[]" id="importe_iva-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                    <td><input name="dataArticulos[]" id="importe_total-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                    <td
                            style="display: flex; justify-content: center; align-items: center" class='eliminacion-articulo' onclick="eliminarArticulo('ninguno', '${result}')">
                            <i class="fa fa-trash-o" aria-hidden="true"
                                style="color: red; font-size: 25px; cursor: pointer;"></i>
                    </td>
                         <td style="display: none">
                               <input name="dataArticulos[]" id="tipoArticulo-${result}" type="hidden" class="botonesArticulos sinBotones"  value=''>
                           </td>
                    </tr>
            <td style="display: none">
                <input id="decimales-${result}" type="text" value="" readonly>
            </td>
            `);
        }

        result++;
        contadorArticulos++;

        $("#cantidadArticulos").val(contadorArticulos);

        let verCosto = $("#verCosto").val();
        let bloquearCosto = $("#bloquearCosto").val();

        //si ver costo es igual a 1, le agregamos la clase display: none para ocultar el costo unitario
        if (verCosto === '0') {
            $(".td-costo").css("display", "none");
        }

        if (bloquearCosto === '1') {
            $(".bloquearCosto").prop("readonly", true);
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
