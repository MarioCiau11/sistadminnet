let articlesDelete = {};
let result = 1000;
let articulosEmpaque = {};

let articulosSerieTrans = {}; //Venta normar series
let articulosSerieTrans2 = {}; //Venta kits con series
let articulosKits = {}; //Venta kits

let tablaArticulos, tablaEmpaques, tablaKits;
const posiblesLotesSeries = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
const movimientosArray = {
    Ventas: "PROC_SALES",
    CxC: "PROC_ACCOUNTS_RECEIVABLE",
    Din: "PROC_TREASURY",
};

jQuery(document).ready(function () {
    moment.locale("es-mx");
    let checkArticulosExistentes = false;
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

    //validamos el json detalle del movimiento
    let jsonGuardadoDetalle = $("#inputJsonData").val();

    if (jsonGuardadoDetalle != "") {
        let jsonDecode = JSON.parse(jsonGuardadoDetalle);
        console.log(jsonDecode);
        let seriesData = jsonDecode["series"];
        let idSeriesData = jsonDecode["idSeries"];
        let kits = jsonDecode["kits"];

        if (seriesData != undefined) {
            let keysIdentificador = Object.keys(seriesData);
            keysIdentificador.forEach((element) => {
                articulosSerieTrans = {
                    ...articulosSerieTrans,
                    [element]: {
                        serie: seriesData[element],
                        ids: idSeriesData[element],
                    },
                };
            });
        }

        if (kits != undefined) {
            let keysIdent = Object.keys(kits);
            articulosKits = kits;

            keysIdent.forEach((element) => {
                let keysSeries = Object.keys(kits[element]["ventaSeriesKits"]);

                keysSeries.forEach((seriesKit) => {
                    articulosSerieTrans2 = {
                        ...articulosSerieTrans2,
                        [seriesKit]: {
                            serie: kits[element]["ventaSeriesKits"][seriesKit][
                                "serie"
                            ],
                            ids: kits[element]["ventaSeriesKits"][seriesKit][
                                "ids"
                            ],
                        },
                    };
                });
            });
        }
    }

    const leyendas = {
        'Cotización': 'Genera tu cotización de venta y envíala automáticamente a tu cliente.',
        'Pedido': 'Con este proceso formaliza y organiza el surtido del cliente.',
        'Factura': 'Con la factura se realiza el descuento del inventario y se emite el comprobante de tipo ingreso.',
        'Rechazo de Venta': 'Finaliza tu proceso indicando el motivo por el cuál no se completó la operación.'
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

        if (selectedMovimiento == "Factura" || selectedMovimiento == "Rechazo de Venta") {
            $("#afectar-boton").html("Finalizar <span class='glyphicon glyphicon-play pull-right'></span>");
        } else {
            $("#afectar-boton").html("Avanzar <span class='glyphicon glyphicon-play pull-right'></span>");
        }
    });

    // Mostrar la leyenda inicial al cargar la página si hay una opción seleccionada
    const selectedMovimiento = $('#select-movimiento').val();
    mostrarLeyenda(selectedMovimiento);
    if (selectedMovimiento == "Factura" || selectedMovimiento == "Rechazo de Venta") {
        $("#afectar-boton").html("Finalizar <span class='glyphicon glyphicon-play pull-right'></span>");
    } else {
        $("#afectar-boton").html("Avanzar <span class='glyphicon glyphicon-play pull-right'></span>");
    }


    let retencion = false;

    const tableIncoTerm = jQuery("#shTable10").DataTable({
        select: {
            style: "single",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    const motivoTraslado = jQuery("#shTable11").DataTable({
        select: {
            style: "single",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    const tableClientes = jQuery("#shTable2").DataTable({
        select: {
            style: "single",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    $("#tooltip").tooltip();

    tableIncoTerm.on("select", function (e, dt, type, indexex) {
        const rowData = tableIncoTerm.row(indexex).data();
        $("#incoTermKey").val(rowData[0]);
        $("#incoTermName").val(rowData[1]);
    });

    motivoTraslado.on("select", function (e, dt, type, indexex) {
        const rowData = motivoTraslado.row(indexex).data();
        $("#motivoTrasladoKey").val(rowData[0]);
        $("#motivoTrasName").val(rowData[1]);
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



    const tablaVendedores = jQuery("#shTable6").DataTable({
        select: {
            style: "single",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    //Tabla articulos
    tablaArticulos = jQuery("#shTable5").DataTable({
        select: {
            style: "multi",
        },
        columnDefs: [
            {
                targets: [4, 5, 6, 7, 8, 9],
                visible: false,
            },
        ],
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    let precioDefault = "";
    jQuery("#select-precioListaSelect").change(function () {
        precioDefault = jQuery(this).val();
        // console.log(precioDefault);
        // proveedor = $("#proveedorKey").val();
        // mov = $(this).val();
    });


    //Evento select para cambiar el value de los inputs del proveedor
    tableClientes.on("select", function (e, dt, type, indexex) {
        const rowData = tableClientes.row(indexex).data();
        $("#proveedorKey").val(rowData[0]);
        $("#proveedorName").val(rowData[1]);
        $("#proveedorKey").change();

        // getProveedorByClave(rowData[0]);
    });

    // Obtener cliente por defecto y establecerlo en el campo
    const clienteDefault = $('#clienteDefault').val();
    const defaultRowCliente = tableClientes.rows().data().filter(function (row) {
      return row[0] === clienteDefault;
    })[0];

    // Esto no se hará hasta que #select-moduleConcept tenga un valor
    $("#select-movimiento").on("change", function () {
      if ($(this).val() !== "") {
        if (defaultRowCliente && $("#proveedorKey").val() === "") {
          $("#proveedorKey").val(defaultRowCliente[0]);
          $("#proveedorKey").keyup();

          // Llamar a la función getProveedorByClave con el valor del proveedor por defecto
          getProveedorByClave(defaultRowCliente[0]);
        }
      }
    });

    $('#select-movimiento').on('change', function () {
        //LE PONEMOS UN TRIgger para que se ejecute el evento change
        var selectedMovimiento = $(this).val();
        console.log(selectedMovimiento);

        if (selectedMovimiento === "Rechazo de Venta") {
            // Lógica para traer todos los conceptos sin filtrar
            $.ajax({
                url: '/api/ventas/getConceptosByMovimiento',
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
                url: '/api/ventas/getConceptosByMovimiento',
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

    //hacemos lo mismo que 
    if (selectedMovimiento === "Rechazo de Venta") {
        // Lógica para traer todos los conceptos sin filtrar
        $.ajax({
            url: '/api/ventas/getConceptosByMovimiento',
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
            url: '/api/ventas/getConceptosByMovimiento',
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
        $("#almacenKey").keyup();
    });

    //obtener almacen por defecto y establecerlo en el campo
    const almacenDefault = $('#almacenDefault').val();
    const defaultRowAlmacen = tablaAlmacenes.rows().data().filter(function (row) {
        return row[0] === almacenDefault;
    })[0];

    if (defaultRowAlmacen && $("#almacenKey").val() === "") {
        $("#almacenKey").val(defaultRowAlmacen[0]);
        $("#almacenKey").keyup();
    }

    //Evento select para cambiar el value de los inputs del vendedor
    tablaVendedores.on("select", function (e, dt, type, indexex) {
        const rowData = tablaVendedores.row(indexex).data();
        $("#sellerKey").val(rowData[1]);
        $("#sellerKey").keyup();
    });

      // Obtener vendedor por defecto y establecerlo en el campo
      const vendedorDefault = $('#vendedorDefault').val();
      const defaultRow = tablaVendedores.rows().data().filter(function (row) {
        return row[0] === vendedorDefault;
      })[0];

      if (defaultRow && $("#sellerKey").val() === "") {
        $("#sellerKey").val(defaultRow[1]);
        $("#sellerKey").keyup();
      }

    //Tabla de almacenes
    tablaEmpaques = jQuery("#tableEmpaque").DataTable({
        select: {
            style: "single",
        },
        language: language,
        searching: false,
        paging: false,
        info: false,
        responsive: true,
        scroll: true,
        scrollY: "40vh",
        autoWidth: true,
        scrollCollapse: true,
        columnDefs: [
            {
                targets: [0],
                visible: false,
            },
        ],

        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    tablaKits = jQuery("#tableKits").DataTable({
        language: language,
        searching: false,
        paging: false,
        info: false,
        responsive: true,
        scroll: true,
        scrollY: "40vh",
        autoWidth: true,
        scrollCollapse: true,

        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    $("#asignar-folio").on("click", function () {
        let sales_id = $("#idCompra").val();
        $.ajax({
            url: "/asignarFolio",
            type: "GET",
            data: {
                sales_id: sales_id,
            },
            success: function ({ status, data, folio }) {
                if (status == true) {
                    $("#folioCompra").val(folio);
                    $("#select-movimiento").attr("readonly", true);
                    swal({
                        title: "Folio asignado",
                        text: data,
                        icon: "success",
                        button: "Ok",
                    });                   
                }

                $("#asignar-folio").hide();
                $("#eliminar-boton").hide();
                $("#reporte-impuestos").show();
                $("#reporte-sinimpuestos").show();
            },
        });
    });

 

    jQuery("#agregarArticulos").on("click", async function () {
        //Eliminamos las filas que no tengan una key
        let claveMonedaSat = $("#clave_sat_moneda").val().trim() !== "MXN";
        let rfc = $("#rfc-cliente").val().trim().toUpperCase();
        let isRfcComercioExterior = rfc.slice(0, 2) == "XE";
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

            $("tooltip").tooltip({
                content: "Descripción",
            });

            for (let i = 0; i < datos.length; i++) {
                let articleKey = datos[i][0];
                let articleName = datos[i][1];
                const isRetencionesVisible = $(".retencion").is(":visible");
                let articleIva;
                let retencion1;
                let retencion2;
                if (datos[i][8] !== 0 || datos[i][9] !== 0) {
                    retencion = true;
                }

                if (claveMonedaSat && isRfcComercioExterior) {
                    articleIva = "";
                } else {
                    if (calculoImpuestos === "0") {
                        articleIva = datos[i][5];
                        retencion1 = datos[i][8];
                        retencion2 = datos[i][9];
                    } else {
                        articleIva = "";
                        retencion1 = "";
                        retencion2 = "";
                    }
                }
                let articleUnidad = datos[i][6];
                let tipo = datos[i][7];

                // console.log(retencion1, retencion2, typeof retencion1, typeof retencion2);
                // console.log(tipo);

                if (isRetencionesVisible) {
                    retencion = true;
                }

                //convertir a float el factor de la unidad
                let factor = parseFloat($("#nameTipoCambio").val());
                let precio = parseFloat(datos[i][2].replace(/['$', ',']/g, ""));
                let precioDecimal = precio / factor;
                let precioFinal = truncarDecimales(
                    formatoMexico(precioDecimal),
                    4
                );

                //eliminamos la fila si no tiene clave

                if ($(".td-aplica").is(":visible")) {
                    $("#articleItem").append(`
                       <tr id="${articleKey}-${result}">
              
                       <td class="" style="width: 5px"></td>
                       <td id="btnInput"><input  name="dataArticulos[]" id="keyArticulo-${articleKey}-${result}" type="text" class="keyArticulo" value='${articleKey}' onchange="buscadorArticulos('keyArticulo-${articleKey}-${result}')" readonly>
               
                       <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal3">...</button>
                       ${
                           (tipo === "Serie" && tipoMov == "Factura") ||
                           (tipoMov == "Pedido" &&
                               tipo === "Serie" &&
                               estatus == "INICIAL")
                               ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal4" id="modalSerie2">S</button>'
                               : ""
                       }

                       ${
                           tipo == "Kit" && estatus == "INICIAL"
                               ? '<button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target=".modal7" id="modalKits"><span class="glyphicon glyphicon-tags"></span></button>'
                               : ""
                       }
               
                       </td>
                       <td><input name="dataArticulos[]" id="desp-${articleKey}-${result}" type="text" class="botonesArticulos" value='${articleName}' readonly title="${articleName}"></td>
                       <td>
                               <input name="dataArticulos[]" id="canti-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value='1'>
                       </td>
                       <td><input name="dataArticulos[]" id="c_unitario-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='${precioFinal}' onchange="calcularImporte('${articleKey}', '${result}')" ${
                        precioVariable === "1" ? "readonly" : ""
                    }></td>
                       <td>
                       <select name="dataArticulos[]" id="unid-${articleKey}-${result}" class="botonesArticulos" value='${articleUnidad}' onchange="recalcularCantidadInventario('${articleKey}', '${result}')" >
                       </select>
                       </td>
                       <td style="display:none" class="unidadEmpaque">
                           <select name="dataArticulos[]" id="unidadEmpaque-${articleKey}-${result}" class="botonesArticulos" value='${articleUnidad}'>
                           </select>
                       </td>
                       <td><input name="dataArticulos[]" id="c_Inventario-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                       <td><input name="dataArticulos[]" id="importe-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                       <td><input name="dataArticulos[]" id="porDesc-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="descuentoLineal('${articleKey}', '${result}')" value=''></td>
                       <td><input name="dataArticulos[]" id="descuento-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                       <td><input name="dataArticulos[]" id="iva-${articleKey}-${result}" type="number" class="botonesArticulos sinBotones" value='${articleIva}' readonly></td>
                       <td><input name="dataArticulos[]" id="importe_iva-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                       <td ${
                           retencion
                               ? 'style="display: "'
                               : 'style="display: none"'
                       } class="retencion2"><input id="pRet1-${articleKey}-${result}"
                            type="text" class="botonesArticulos" readonly value='${
                                retencion1 == "NaN" || retencion1 == null
                                    ? ""
                                    : retencion1
                            }' name="dataArticulos[]"></td>
                    <td ${
                        retencion
                            ? 'style="display: "'
                            : 'style="display: none"'
                    } class="retencion2"><input id="pRetISR-${articleKey}-${result}"
                            type="text" class="botonesArticulos" readonly value='0.00' name="dataArticulos[]"></td>
                    <td ${
                        retencion
                            ? 'style="display: "'
                            : 'style="display: none"'
                    } class="retencion2"><input id="pRet2-${articleKey}-${result}"
                            type="text" class="botonesArticulos" readonly value='${
                                retencion2 == "NaN" || retencion2 == null
                                    ? ""
                                    : retencion2
                            }' name="dataArticulos[]"></td>
                    <td ${
                        retencion
                            ? 'style="display: "'
                            : 'style="display: none"'
                    } class="retencion2"><input id="retIva-${articleKey}-${result}" type="text" class="botonesArticulos" readonly value='0.00' name="dataArticulos[]">
                    </td>
                       <td><input name="dataArticulos[]" id="importe_total-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                       <td><input name="dataArticulos[]" id="observacion-${articleKey}-${result}" type="text" class="botonesArticulos" title="${articleName}"></td>
                       <td
                               style="display: flex; justify-content: center; align-items: center; width: 30px !important" class='eliminacion-articulo'>
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

                    changeCostoImporte(articleKey, result);
                    importeIva(articleKey, result);
                    importeTotal(articleKey, result);
                    siguienteCampo(articleKey, result);
                } else {
                    $("#articleItem").append(`
                        <tr id="${articleKey}-${result}">
                        <td id="btnInput"><input name="dataArticulos[]" id="keyArticulo-${articleKey}-${result}" type="text" class="keyArticulo" onchange="buscadorArticulos('keyArticulo-${articleKey}-${result}')" value='${articleKey}' >
                
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal3">...</button>
                        ${
                            (tipo === "Serie" && tipoMov == "Factura") ||
                            (tipoMov == "Pedido" &&
                                tipo === "Serie" &&
                                estatus == "INICIAL")
                                ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal4" id="modalSerie2">S</button>'
                                : ""
                        }

                        ${
                            tipo == "Kit" && estatus == "INICIAL"
                                ? '<button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target=".modal7" id="modalKits"><span class="glyphicon glyphicon-tags"></span></button>'
                                : ""
                        }
               
                        </td>
                        <td><input name="dataArticulos[]" id="desp-${articleKey}-${result}" type="text" class="botonesArticulos" value='${articleName}' readonly title="${articleName}"></td>
                        <td>
                                <input name="dataArticulos[]" id="canti-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value='1'>
                        </td>
                        <td><input name="dataArticulos[]" id="c_unitario-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='${precioFinal}' onchange="calcularImporte('${articleKey}', '${result}')" ${
                        precioVariable === "1" ? "readonly" : ""
                    }></td>
                        <td>
                            <select name="dataArticulos[]" id="unid-${articleKey}-${result}" class="botonesArticulos" value='${articleUnidad}' onchange="recalcularCantidadInventario('${articleKey}', '${result}')" >
                            </select>
                        </td>
                        <td  style="display:none" class="unidadEmpaque">
                        <select name="dataArticulos[]" id="unidadEmpaque-${articleKey}-${result}" class="botonesArticulos" value='${articleUnidad}'>
                        </select>
                        </td>
                        <td><input name="dataArticulos[]" id="c_Inventario-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                        <td><input name="dataArticulos[]" id="importe-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                        <td><input name="dataArticulos[]" id="porDesc-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="descuentoLineal('${articleKey}', '${result}')" value=''></td>
                        <td><input name="dataArticulos[]" id="descuento-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                        <td><input name="dataArticulos[]" id="iva-${articleKey}-${result}" type="number" class="botonesArticulos sinBotones" value='${articleIva}' readonly></td>
                        <td><input name="dataArticulos[]" id="importe_iva-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                        <td ${
                            retencion
                                ? 'style="display: "'
                                : 'style="display: none"'
                        } class="retencion2"><input id="pRet1-${articleKey}-${result}"
                                type="text" class="botonesArticulos" readonly value='${
                                    retencion1 == "NaN" || retencion1 == null
                                        ? ""
                                        : retencion1
                                }' name="dataArticulos[]"></td>
                        <td ${
                            retencion
                                ? 'style="display: "'
                                : 'style="display: none"'
                        } class="retencion2"><input id="pRetISR-${articleKey}-${result}"
                                type="text" class="botonesArticulos" readonly value='0.00' name="dataArticulos[]"></td>
                        <td ${
                            retencion
                                ? 'style="display: "'
                                : 'style="display: none"'
                        } class="retencion2"><input id="pRet2-${articleKey}-${result}"
                                type="text" class="botonesArticulos" readonly value='${
                                    retencion2 == "NaN" || retencion2 == null
                                        ? ""
                                        : retencion2
                                }' name="dataArticulos[]"></td>
                        <td ${
                            retencion
                                ? 'style="display: "'
                                : 'style="display: none"'
                        } class="retencion2"><input id="retIva-${articleKey}-${result}" type="text" class="botonesArticulos" readonly value='0.00' name="dataArticulos[]">
                        </td>
                        <td><input name="dataArticulos[]" id="importe_total-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                        <td><input name="dataArticulos[]" id="observacion-${articleKey}-${result}" type="text" class="botonesArticulos"></td>
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
                    changeCostoImporte(articleKey, result);
                    importeIva(articleKey, result);
                    importeTotal(articleKey, result);
                    siguienteCampo(articleKey, result);
                }

                const selectOptions = $("#unid-" + articleKey + "-" + result); //Obtenemos el select para añadirle las multiunidades correspondientes
                const selectOptionsEmpaque = $(
                    "#unidadEmpaque-" + articleKey + "-" + result
                ); //Obtenemos el select para añadirle las multiunidades correspondientes
                await $.ajax({
                    url: "/comercial/ventas/api/getMultiUnidad",
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

                await $.ajax({
                    url: "/listaEmpaques",
                    type: "GET",
                    success: function ({ estatus, data }) {
                        data.forEach((element) => {
                            selectOptionsEmpaque.append(`
                                <option value="${
                                    element.packaging_units_packaging +
                                    "-" +
                                    element.packaging_units_weight +
                                    "-" +
                                    element.packaging_units_unit
                                }">${
                                element.packaging_units_packaging +
                                "-" +
                                element.packaging_units_weight +
                                "-" +
                                element.packaging_units_unit
                            }</option>
                                `);
                        });
                    },
                });

                contadorArticulos++;
            }

            if (retencion) {
                $(".retencion").show();
                $(".retencion2").show();
            }

            $("#shTable5").DataTable().rows(".selected").deselect();

            jQuery("#cantidadArticulos").val(contadorArticulos);
        }
    });

    jQuery("#select-proveedorCondicionPago").on("change", function () {
        tipoCondicionPago();
        agregarTipo();
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
                $("#clave_sat_moneda").val(data.money_keySat);
            },
        });
    });

    const $select = jQuery(
        "#select-movimiento, #select-moduleConcept, #select-listaPrecios, #select-proveedorCondicionPago, #select-moneda, #select-precioListaSelect, #metodoPago1,  #metodoPago2, #metodoPago3, #cuentaPago, #metodoPago7, #select-clienteCFDI, #select-motivoCancelacion, #select-folioSustitucion, #select-facturaRelacion, #select-moduleCancellation"
    ).select2({
        minimumResultsForSearch: -1,
    });

    jQuery(
        "#select-choferName, #select-vehiculoName, #select-tipoOperacion, #select-clavePedimento"
    ).select2();

    jQuery("#select-vehiculoName").on("change", function (e) {
        $.ajax({
            url: "/getPlacas",
            type: "GET",
            data: {
                vehiculo: jQuery("#select-vehiculoName").val(),
            },
            success: function (data) {
                $("#placas").val(data[0].vehicles_plates);
            },
        });
    });


    let FolioVenta = jQuery("#folioCompra").val();

    if(FolioVenta != ""){
        jQuery("#select-movimiento").attr("readonly", true);
    }else{
        //si no hay folio permitir cambiar el movimiento
        jQuery("#select-movimiento").attr("readonly", false);
    }

    jQuery("#select-movimiento").on("change", function (e) {
    
        let mov = jQuery("#select-movimiento").val();

        if (mov != "Rechazo de Venta") {
            jQuery(".motivoCancelacionDiv").hide();
        } else {
            jQuery(".motivoCancelacionDiv").show();
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
    $("#vendedoresModal").modal({
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
    $("#cancelarFacturaModal").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#modalKitVentas").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#modalComercioExterior").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#ModalFlujo").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#facturaModal").modal({
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

    $("#facturaInfo-aceptar").on("click", function () {
        //sacar el valor del radio button
        const inputFacturaRel = $("#dataFacturaInfo");

        let radioNormal = false,
            radioRelacion = false;

        if ($("#radioPrimary").is(":checked")) {
            radioNormal = true;
        }

        if ($("#radioWarning").is(":checked")) {
            radioRelacion = true;
        }

        let facturaRel = $("#select-facturaRelacion").val();

        let infoFactura = {
            Normal: radioNormal,
            Relacionado: radioRelacion,
            facturaRelacion: facturaRel,
        };

        inputFacturaRel.attr("name", "dataFacturaInfo");
        inputFacturaRel.attr("value", JSON.stringify(infoFactura));

        $("#facturaModal").modal("hide");

        afectarRequest("/comercial/ventas/afectar");
    });

    $("#facturaInfo-aceptar2").on("click", function () {
        //sacar el valor del radio button
        const inputFacturaRel = $("#dataFacturaInfo");

        let radioNormal = false,
            radioRelacion = false;

        if ($("#radioPrimary").is(":checked")) {
            radioNormal = true;
        }

        if ($("#radioWarning").is(":checked")) {
            radioRelacion = true;
        }

        let facturaRel = $("#select-facturaRelacion").val();

        let infoFactura = {
            Normal: radioNormal,
            Relacionado: radioRelacion,
            facturaRelacion: facturaRel,
        };

        inputFacturaRel.attr("name", "dataFacturaInfo");
        inputFacturaRel.attr("value", JSON.stringify(infoFactura));

        $("#facturaModal").modal("hide");

        requestTimbrado();
    });

    $("#radioWarning").on("click", function () {
        if ($("#radioWarning").is(":checked")) {
            $(".facturaRelacion").show();
        }
    });

    $("#radioPrimary").on("click", function () {
        if ($("#radioPrimary").is(":checked")) {
            $(".facturaRelacion").hide();
        }
    });

    $("#proveedorModal").on("show.bs.modal", function (e) {
        const isInvalid = validateMovimiento();

        if (isInvalid) {
            showMessage(
                "No se ha seleccionado el tipo de movimiento",
                "error"
            );
            return false;
        }
    });

    $("#email-modal").on("show.bs.modal", function (e) {
        // Aquí puedes agregar tus condiciones para activar o desactivar el modal
        const movimiento = $("#select-movimiento").val();
        const estatus = $("#select-estatus").val();

        if (movimiento === "COTIZACIÓN" && (estatus === "SIN AFECTAR" || estatus === "PENDIENTE")) {
            // Si cumple las condiciones, habilita el botón de enviar email
            $("#enviar-email-btn").prop("disabled", false);
        } else {
            // Si no cumple las condiciones, deshabilita el botón de enviar email
            $("#enviar-email-btn").prop("disabled", true);
        }
    });


    $("#ventasCalculadora").on("show.bs.modal", function (e) {
        if (isSalesPaymentSave !== "1") {
            $("#cambioTotal").val("$0.00");
            let totalFactura = $("#totalCompleto").val();
            console.log(totalFactura);

            $("#totalC").val(totalFactura);
            $("#cantidad1").val(totalFactura);
            $("#totalCobrado").val(totalFactura);
            $("#metodoPago7").val("1").change();
        }
    });

    $(".modal4").on("show.bs.modal", function (e) {
        const isInvalid3 = validateCantidad();

        if (isInvalid3) {
            showMessage("Por favor ingresa la cantidad del articulo", "error");
            return false;
        } else {
            let cuerpoModal = jQuery("#form-articulos-serie2");
            let ruta = window.location.href;
            let ruta2 = ruta.split("/");
            // console.log(ruta2);
            let idCompra = "";

            if (ruta2.length > 6) {
                idCompra = ruta2[ruta2.length - 1];
            }

            cuerpoModal.html(""); //Limpiamos el cuerpo del modal
            let identificadorFila = $(e.relatedTarget)
                .parent()
                .parent()
                .attr("id");

            let movimiento = $("#select-movimiento").val();

            cantidad = parseFloat($("#canti-" + identificadorFila).val());

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
                       <h5 class="text-center">Seleccionar las series, que desea vender del almacén</h5>
                    </div>`
                );
            } else {
                cuerpoModal.append(
                    `<div class="col-md-12" ">
                       <h5 class="text-center">Series vendidos</h5>
                    </div>`
                );
            }

            let almacen = $("#almacenKey").val();
            if (idCompra != "") {
                $.ajax({
                    url: "/logistica/ventas/api/getSeriesSeleccionados",
                    method: "GET",
                    data: {
                        idCompra: idCompra,
                        almacen: almacen,
                        claveArticulo: identificadorFila.split("-")[0],
                        articleId: idArticulo,
                        tipo: "Serie",
                    },
                    success: function ({ status, data, data2 }) {
                        let estado = $("#status").text();
                        let movimiento = $("#select-movimiento").val();

                        if (status === 200) {
                            if (data2.length > 0) {
                                if (
                                    estado === "INICIAL" ||
                                    (estado === "POR AUTORIZAR" && movimiento !== "Pedido")
                                ) {
                                    while (contador <= cantidad) {
                                        articuloSerie =
                                            data2[contador - 1] === undefined
                                                ? ""
                                                : data2[contador - 1];
                                        console.log(
                                            articuloSerie.delSeriesMov2_articleID,
                                            idArticulo,
                                            data,
                                            data2
                                        );

                                        if (
                                            articuloSerie.delSeriesMov2_articleID ==
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
                                                    articuloSerie.delSeriesMov2_id
                                                }" id="serie" name="serie2[]" value="${
                                                    articuloSerie.delSeriesMov2_lotSerie
                                                }"
                                        >
                                        <option value="">Seleccione una serie</option>
                                            ${data.map(
                                                (serie) =>
                                                    `<option value="${
                                                        serie.lotSeries_lotSerie
                                                    }" ${
                                                        articuloSerie.delSeriesMov2_lotSerie ===
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
                                        } else if (
                                            articuloSerie.delSeriesMov2_article ==
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
                                                    articuloSerie.delSeriesMov2_id
                                                }" id="serie" name="serie2[]" value="${
                                                    articuloSerie.delSeriesMov2_lotSerie
                                                }"
                                            >
                                            <option value="">Seleccione una serie</option>
                                                ${data.map(
                                                    (serie) =>
                                                        `<option value="${
                                                            serie.lotSeries_lotSerie
                                                        }" ${
                                                            articuloSerie.delSeriesMov2_lotSerie ===
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
                                        $("#serie").change();
                                        contador++;
                                    }
                                }

                                if (
                                    estado === "FINALIZADO" ||
                                    estado === "CANCELADO" ||
                                (estado === "POR AUTORIZAR" && movimiento === "Pedido")
                                ) {
                                    data2.forEach((articuloSerie) => {
                                        // console.log(data, data2, idArticulo);
                                        // console.log(articuloSerie, identificadorFila, idArticulo);
                                        if (
                                            (articuloSerie.delSeriesMov2_articleID ==
                                                idArticulo &&
                                                articuloSerie.delSeriesMov2_affected ===
                                                    "1") ||
                                            (articuloSerie.delSeriesMov2_articleID ==
                                                idArticulo &&
                                                articuloSerie.delSeriesMov2_affected ==
                                                    "0")
                                        ) {
                                            cuerpoModal.append(
                                                //agregar el valor de la serie en el select
                                                `<div class="col-md-2">
                                             <span><strong>Serie :</strong></span>
                                         </div>
                                         <div class="col-md-10 " style="margin-bottom: 5px;">
                                             <select class="form-control ${identificadorFila} ${articuloSerie.delSeriesMov2_id}" id="serie" name="serie2[]" disabled>
                                            <option value="${articuloSerie.delSeriesMov2_lotSerie}">${articuloSerie.delSeriesMov2_lotSerie}</option>
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
                                            <select class="form-control ${identificadorFila}" id="serie" name="serie2[]">
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
                                        <select class="form-control ${identificadorFila}" id="serie" name="serie2[]">
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
                                    <select class="form-control ${identificadorFila}" id="serie" name="serie2[]">
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

    $("#quitarSeries2").click(function () {
        let movimiento = $("#select-movimiento").val();
        let isSerieVacio = false;
        let isRepetidoSerie = false;
        let identificadorFila = jQuery("#clavePosicion2").val();

        $('select[name^="serie2[]"]').each(function (index) {
            console.log(index);
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

            $('select[name^="serie2[]"]').each(function (index, articulo) {
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
                articulosSerieTrans = {
                    ...articulosSerieTrans,
                    [identificadorFila]: {
                        serie: arraySerie,
                        ids: arrayClaves,
                    },
                };

                $(".modal4").modal("hide");
            }
        }
    });

    //funcion para  articulos serie de tipo kit
    $(".modal44").on("show.bs.modal", function (e) {
        const isInvalid3 = validateCantidad();

        let cuerpoModal = jQuery("#form-articulos-serie22");
        let ruta = window.location.href;
        let ruta2 = ruta.split("/");
        // console.log(ruta2);
        let idCompra = "";

        if (ruta2.length > 6) {
            idCompra = ruta2[ruta2.length - 1];
        }

        cuerpoModal.html(""); //Limpiamos el cuerpo del modal
        let identificadorFila = $(e.relatedTarget)
            .parent()
            .parent()
            .find("td:eq(1)");

        let claveArticulo = identificadorFila.text();
        let movimiento = $("#select-movimiento").val();
        let cantidad = parseFloat(
            $(e.relatedTarget).parent().parent().find("td:eq(4)").text()
        );

        let idArticulo = $(".serieEmpaqueCantidad").text();

        let tituloClaveArticulo = jQuery(".articuloKeySerie22");
        tituloClaveArticulo.text(claveArticulo); //Asignamos el valor de la clave del articulo al titulo del modal

        jQuery("#clavePosicion22").val(claveArticulo);
        // console.log(articulosSerieTrans2);
        const isPropiedadExiste3 = articulosSerieTrans2.hasOwnProperty(
            claveArticulo
        )
            ? true
            : false;

        let contador = 1;

        let estado = $("#status").text();

        if (estado === "POR AUTORIZAR") {
            $("#quitarSeries22").show();
        }
        if (estado === "INICIAL") {
            cuerpoModal.append(
                `<div class="col-md-12" ">
                       <h5 class="text-center">Seleccionar las series, que desea vender del almacén</h5>
                    </div>`
            );
        } else {
            cuerpoModal.append(
                `<div class="col-md-12" ">
                       <h5 class="text-center">Series vendidos</h5>
                    </div>`
            );
        }

        let almacen = $("#almacenKey").val();
        // console.log(isPropiedadExiste3);
        if (idCompra != "" && isPropiedadExiste3) {
            $.ajax({
                url: "/logistica/ventas/api/getSeriesSeleccionados",
                method: "GET",
                data: {
                    idCompra: idCompra,
                    almacen: almacen,
                    claveArticulo: claveArticulo,
                    tipo: "Kit",
                },
                success: function ({ status, data, data2 }) {
                    console.log(data, data2, idArticulo);
                    let estado = $("#status").text();
                    let movimiento = $("#select-movimiento").val();
                    if (status === 200) {
                        if (data2.length > 0) {
                            if (
                                estado === "INICIAL" ||
                                estado === "POR AUTORIZAR"
                            ) {
                                while (contador <= cantidad) {
                                    articuloSerie =
                                        data2[contador - 1] === undefined
                                            ? ""
                                            : data2[contador - 1];

                                    if (
                                        articuloSerie.delSeriesMov2_articleID ==
                                            idArticulo ||
                                        articuloSerie === ""
                                    ) {
                                        console.log(
                                            articuloSerie.delSeriesMov2_articleID,
                                            idArticulo
                                        );
                                        cuerpoModal.append(
                                            //agregar el valor de la serie en el select
                                            `<div class="col-md-2">
                                        <span><strong>Articulo :</strong></span>
                                    </div>
                                    <div class="col-md-10 " style="margin-bottom: 5px;">
                                        <select class="form-control ${claveArticulo} ${
                                                articuloSerie.delSeriesMov2_id
                                            }" id="serie" name="serie[]" value="${
                                                articuloSerie.delSeriesMov2_lotSerie
                                            }"
                                        >
                                        <option value="">Seleccione una serie</option>
                                            ${data.map(
                                                (serie) =>
                                                    `<option value="${
                                                        serie.lotSeries_lotSerie
                                                    }" ${
                                                        articuloSerie.delSeriesMov2_lotSerie ===
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

                            if (
                                estado === "FINALIZADO" ||
                                estado === "CANCELADO"
                            ) {
                                data2.forEach((articuloSerie) => {
                                    // console.log(articuloSerie, claveArticulo, idArticulo);
                                    if (
                                        articuloSerie.delSeriesMov2_articleID ==
                                            idArticulo &&
                                        articuloSerie.delSeriesMov2_affected ===
                                            "1"
                                    ) {
                                        cuerpoModal.append(
                                            //agregar el valor de la serie en el select
                                            `<div class="col-md-2">
                                             <span><strong>Serie :</strong></span>
                                         </div>
                                         <div class="col-md-10 " style="margin-bottom: 5px;">
                                             <select class="form-control ${claveArticulo} ${articuloSerie.delSeriesMov2_id}" id="serie" name="serie[]" disabled>
                                            <option value="${articuloSerie.delSeriesMov2_lotSerie}">${articuloSerie.delSeriesMov2_lotSerie}</option>
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
                                            <select class="form-control ${claveArticulo}" id="serie" name="serie[]">
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
                    claveArticulo: claveArticulo,
                },
                success: function ({ status, data }) {
                    // console.log(data);
                    //quitar el signo negativo de la cantidad
                    //Agremos los selects de acuerdo a la cantidad ingresada por el usuario y las series que se encuentran en el almacen
                    if (isPropiedadExiste3) {
                        let serie =
                            articulosSerieTrans2[claveArticulo]["serie"];

                        while (contador <= cantidad) {
                            serieP = serie[contador - 1];
                            cuerpoModal.append(
                                `<div class="col-md-2">
                                        <span><strong>Articulo :</strong></span>
                                    </div>
                                    <div class="col-md-10 " style="margin-bottom: 5px;">
                                        <select class="form-control ${claveArticulo}" id="serie" name="serie[]">
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
                                    <select class="form-control ${claveArticulo}" id="serie" name="serie[]">
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

        // console.log(articulosSerieTrans2, isPropiedadExiste3);
    });
    //funcion para quitar articulos serie de tipo kit
    $("#quitarSeries22").click(function () {
        let movimiento = $("#select-movimiento").val();
        let isSerieVacio = false;
        let isRepetidoSerie = false;
        let identificadorFila = jQuery("#clavePosicion22").val();

        // console.log(identificadorFila);

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

                // console.log(clase);
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
                articulosSerieTrans2 = {
                    ...articulosSerieTrans2,
                    [identificadorFila]: {
                        serie: arraySerie,
                        ids: arrayClaves,
                    },
                };

                $(".modal44").modal("hide");
            }
        }
    });

    //validar si la forma de cobro es tarjeta de crédito o debito, si es así, se habilita el campo de información adicional
    $(".cobrosForm").on("change", function () {
        let cobros = $(".cobrosForm");
        let isActive = false;
        // let codigo_sat = $("#metodoPago2 option:selected").data();
        cobros.each(function (index, element) {
            //verificamos que el element es de tipo select
            if (element.nodeName == "SELECT") {
                let id = $(element).attr("id");
                let codigo_sat = $(`#${id} option:selected`).data();

                if (codigo_sat?.target == "04" || codigo_sat?.target == "28") {
                    isActive = true;
                }
            }
        });

        if (isActive) {
            $("#infAdicional").attr("readonly", false);
        } else {
            $("#infAdicional").attr("readonly", true);
            $("#infAdicional").val("");
        }
    });

    // Asignar evento "change" a los selectores con la clase "metodoPagoSelect"
    $(".cobrosForm").on("change", function () {
      let metodoPago = $(this).find("option:selected").data();

      if (metodoPago?.target == "01") {
        $("#metodoPago7").val($(this).val()).change();
      } else {
        $("#metodoPago7").val("").change();
      }
    });



    let idVenta = $("#idCompra").val();
    let contador = 1;

    let arrayCantidades = [];
    let arrayDisponible = [];

    //funcion modal para generar los kits
    $(".modal7").on("show.bs.modal", function (e) {
        // const isInvalid3 = validateCantidad();
        // if (isInvalid3) {
        //     showMessage("Por favor ingresa la cantidad del articulo", "error");
        //     return false;
        // }

        let idVenta = $("#idCompra").val();
        let entroCopiado = false;

        let identificadorFila = $(e.relatedTarget).parent().parent().attr("id");
        const isExistente = articulosKits.hasOwnProperty(identificadorFila)
            ? true
            : false;

        let cantidad = parseFloat(
            $("#canti-" + identificadorFila)
                .val()
                .replace(/[$,]/g, "")
        );

        if (isNaN(cantidad)) {
            cantidad = 1;
        }

        let referenciaId = $("#id-" + identificadorFila).val();

        // let idArticulo = $("#id-" + identificadorFila).val();
        let claveArticulo = identificadorFila.split("-")[0];

        let almacen = $("#almacenKey").val();

        let statusVenta = $("#status").text();

        let claveArticuloText = jQuery(".serieEmpaqueCantidad");
        let descriptArticuloText = jQuery(".serieEmpaqueDescripcion");
        claveArticuloText.text(claveArticulo);
        let descripcion = $("#desp-" + identificadorFila).val();
        descriptArticuloText.text(descripcion);
        let cantidadText = jQuery("#cantidadKit");
        cantidadText.val(cantidad);
        jQuery("#referenceTabla").val(identificadorFila);

        let movimiento = $("#select-movimiento").val();

        //funcion cuando es una venta nueva
        if (!isExistente && idVenta == 0) {
            console.log("venta nueva");
            $.ajax({
                url: "/armarKits",
                type: "GET",
                data: {
                    articulo: claveArticulo,
                    almacen: almacen,
                },
                beforeSend: function () {
                    //mostrar el loader
                    // $("#loader").show();
                },
                success: function ({ status, data }) {
                    console.log(status, data);
                    //ocultar el loader
                    // $("#loader").hide();
                    limpiarTablaKits();
                    // console.log(status, data);
                    if (status === true) {
                        data.forEach((element) => {
                            if (element.articulo in arrayCantidades == false) {
                                //array con las cantidades de los articulos
                                arrayCantidades[element.articulo] =
                                    element.cantidad;
                            }

                            if (element.articulo in arrayDisponible == false) {
                                //array con las cantidades disponibles de los articulos
                                arrayDisponible[element.articulo] =
                                    element.disponible;
                            }

                            tablaKits.row
                                .add([
                                    element.descripcion,
                                    element.articulo,
                                    element.descripcion,
                                    element.disponible,
                                    element.cantidad,
                                    element.tipo,
                                    `<input type="text" class="observaciones" style="width: 100px;">`,
                                    `${
                                        element.tipo === "Serie" &&
                                        (movimiento == "Factura" ||
                                            movimiento == "Pedido")
                                            ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal44" id="modalSerie22">S</button>'
                                            : ""
                                    }`,
                                ])
                                .draw(false);

                            jQuery("#cantidadKit").change();
                        });
                    }
                },
            });
        }

        //funcion cuando es una venta copiada
        // if (!isExistente && idVenta != 0 && statusVenta == "INICIAL") {
        //     entroCopiado = true;
        //     // console.log("venta copiada");
        //     $.ajax({
        //         url: "/armarKits",
        //         type: "GET",
        //         data: {
        //             articulo: claveArticulo,
        //             almacen: almacen,
        //         },
        //         beforeSend: function () {
        //             //mostrar el loader
        //             // $("#loader").show();
        //         },
        //         success: function ({ status, data }) {
        //             // console.log(status, data);
        //             //ocultar el loader
        //             // $("#loader").hide();
        //             limpiarTablaKits();
        //             // console.log(status, data);
        //             if (status === true) {
        //                 data.forEach((element) => {
        //                     if (element.articulo in arrayCantidades == false) {
        //                         //array con las cantidades de los articulos
        //                         arrayCantidades[element.articulo] =
        //                             element.cantidad;
        //                     }
        //                     if (element.articulo in arrayDisponible == false) {
        //                         //array con las cantidades disponibles de los articulos
        //                         arrayDisponible[element.articulo] =
        //                             element.disponible;
        //                     }

        //                     tablaKits.row
        //                         .add([
        //                             element.descripcion,
        //                             element.articulo,
        //                             element.descripcion,
        //                             element.disponible,
        //                             element.cantidad,
        //                             element.tipo,
        //                             `<input type="text" class="observaciones" style="width: 100px;">`,
        //                             `${
        //                                 element.tipo === "Serie" &&
        //                                 (movimiento == "Factura" ||
        //                                     movimiento == "Pedido")
        //                                     ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal44" id="modalSerie22">S</button>'
        //                                     : ""
        //                             }`,
        //                         ])
        //                         .draw(false);

        //                     jQuery("#cantidadKit").change();
        //                 });
        //             }
        //         },
        //     });
        // }

        //funcion para buscar los articulos que se agregaron al kit del json
        if (isExistente) {
            limpiarTablaKits();

            let articulos = articulosKits[identificadorFila].articulos;

            articulos.forEach((element) => {
                if (element.articuloId in arrayCantidades == false) {
                    //array con las cantidades de los articulos
                    arrayCantidades[element.articuloId] = element.cantidad;
                }
                if (element.articuloId in arrayDisponible == false) {
                    //array con las cantidades disponibles de los articulos
                    arrayDisponible[element.articuloId] = element.disponible;
                }

                tablaKits.row
                    .add([
                        element.componente,
                        element.articuloId,
                        element.descripcion,
                        element.disponible,
                        element.cantidad,
                        element.tipo,
                        `<input type="text"  class="observaciones" style="width: 100px;" value="${element.observaciones}">`,
                        `${
                            element.tipo === "Serie" &&
                            (movimiento == "Factura" || movimiento == "Pedido")
                                ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal44" id="modalSerie22">S</button>'
                                : ""
                        }`,
                    ])
                    .draw(false);

                jQuery("#cantidadKit").change();
            });
        }

        let origenType = $("#originType").val();
        let origen = $("#originDato").val();
        let origenID = $("#originID").val();

        // if (
        //     origenType != "Usuario" &&
        //     origenType != "" &&
        //     !isExistente &&
        //     idVenta != 0 &&
        //     entroCopiado == false
        // ) {
        //     //  console.log("entro1");
        //     $.ajax({
        //         url: "/armarKits2",
        //         type: "GET",
        //         data: {
        //             articulo: claveArticulo,
        //             almacen: almacen,
        //             origen: origen,
        //             origenID: origenID,
        //             idVenta: idVenta,
        //         },
        //         beforeSend: function () {
        //             //mostrar el loader
        //             // $("#loader").show();
        //         },
        //         success: function ({ status, data }) {
        //             // console.log(data);
        //             //ocultar el loader
        //             // $("#loader").hide();
        //             limpiarTablaKits();
        //             // console.log(status, data);
        //             if (status === true) {
        //                 //sacar las keys del objeto
        //                 let keys = Object.keys(data);

        //                 //recorrer el objeto
        //                 keys.forEach((key) => {
        //                     //array con las cantidades de los articulos
        //                     arrayCantidades[data[key].articulo] =
        //                         data[key].cantidadDefault;

        //                     //array con las cantidades disponibles de los articulos
        //                     arrayDisponible[data[key].articulo] =
        //                         data[key].disponible;

        //                     if (data[key].articuloRef === claveArticulo) {
        //                         tablaKits.row
        //                             .add([
        //                                 data[key].descripcion,
        //                                 data[key].articulo,
        //                                 data[key].descripcion,
        //                                 data[key].disponible,
        //                                 data[key].cantidad,
        //                                 data[key].tipo,
        //                                 `<input type="text" class="observaciones" style="width: 100px;" value="${
        //                                     data[key].observaciones
        //                                 }" ${
        //                                     statusVenta !== "INICIAL"
        //                                         ? "disabled"
        //                                         : ""
        //                                 }>`,
        //                                 `${
        //                                     data[key].tipo === "Serie" &&
        //                                     (movimiento == "Factura" ||
        //                                         movimiento == "Pedido")
        //                                         ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal44" id="modalSerie22">S</button>'
        //                                         : ""
        //                                 }`,
        //                             ])
        //                             .draw(false);
        //                     }

        //                     jQuery("#cantidadKit").change();
        //                 });
        //             }
        //         },
        //     });
        // }

        //funcion para buscar cuando ya este guardado en la base de datos
        if (
            idVenta != 0 &&
            !isExistente &&
            (origenType == "Usuario" ||
                origenType == "Ventas" ||
                origenType == "") &&
            entroCopiado == false
        ) {
            //  console.log("entro333");
            limpiarTablaKits();

            $.ajax({
                url: "/buscarKits",
                type: "GET",
                data: {
                    articulo: claveArticulo,
                    referencia: referenciaId,
                    idVenta: idVenta,
                    almacen: almacen,
                },
                beforeSend: function () {
                    //mostrar el loader
                    // $("#loader").show();
                },
                success: function ({ status, data }) {
                    // console.log(status, data);
                    //ocultar el loader
                    // $("#loader").hide();
                    // console.log(status, data);
                    if (status === true) {
                        //sacar las keys del objeto
                        let keys = Object.keys(data);

                        //recorrer el objeto
                        keys.forEach((key) => {
                            //array con las cantidades de los articulos
                            arrayCantidades[data[key].articulo] =
                                data[key].cantidadDefault;

                            //array con las cantidades disponibles de los articulos
                            arrayDisponible[data[key].articulo] =
                                data[key].disponible;

                            tablaKits.row
                                .add([
                                    data[key].descripcion,
                                    data[key].articulo,
                                    data[key].descripcion,
                                    data[key].disponible,
                                    data[key].cantidad,
                                    data[key].tipo,
                                    `<input type="text" class="observaciones" style="width: 100px;" value="${
                                        data[key].observaciones
                                    }" ${
                                        statusVenta !== "INICIAL"
                                            ? "disabled"
                                            : ""
                                    }>`,
                                    `<input type="hidden" class="form-control" id="idKit" value="${
                                        data[key].articuloReferenceId
                                    }">
                                 ${
                                     data[key].tipo === "Serie" &&
                                     (movimiento == "Factura" ||
                                         movimiento == "Pedido")
                                         ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal44" id="modalSerie22">S</button>'
                                         : ""
                                 }`,
                                ])
                                .draw(false);
                        });
                    }
                },
            });
        }
    });

    //funcion para el cambio de cantidad en el kit
    jQuery("#cantidadKit").on("change", function () {
        let cantidad = parseFloat($(this).val());
        let mov = $("#select-movimiento").val().trim();

        let actualizaVista = false;

        let contador = 0;
        let status = $("#status").text().trim();
        jQuery("#tableKits tbody tr").each(function (row, tr) {
            let articulo = $(tr).find("td:eq(1)").text();
            let tipoArticulo = $(tr).find("td:eq(5)").text();

            let cantidadTotal = arrayCantidades[articulo] * cantidad;
            let disponibleArticulo = parseFloat(arrayDisponible[articulo]);
            //validar que la cantidad no sea mayor a la disponible
            if (status == "INICIAL" && mov != "Cotización") {
                if (
                    tipoArticulo != "Servicio" &&
                    cantidadTotal > disponibleArticulo
                ) {
                    //quitar el evento change
                    // $("#cantidadKit").attr("readonly", true);
                    actualizaVista = false;
                    showMessage(
                        "No hay suficiente cantidad disponible",
                        "warning"
                    );
                    $("#generarKits").text("Cerrar");
                    $("#generarKits").removeClass("btn-success");
                    $("#generarKits").addClass("btn-danger");
                    $("#generarKits").removeAttr("id");
                    contador++;
                } else {
                    contador = 0;
                    actualizaVista = true;
                }

                if (!actualizaVista) {
                    jQuery("#cantidadKit").val(disponibleArticulo);
                    if (contador == 0) {
                        jQuery("#cantidadKit").change();
                    }
                }
            }
        });

        if (actualizaVista) {
            $("#generarKits").text("Generar kit");
            $("#generarKits").removeClass("btn-danger");
            $("#generarKits").addClass("btn-success");
            $("#generarKits").attr("id", "generarKits");
            jQuery("#tableKits tbody tr").each(function (row, tr) {
                const articulo = $(tr).find("td:eq(1)").text();
                if (status == "INICIAL") {
                    let cantidadTotal = arrayCantidades[articulo] * cantidad;
                    $(tr).find("td:eq(4)").text(cantidadTotal);
                } else {
                    let cantidadTotal = arrayCantidades[articulo];
                    $(tr).find("td:eq(4)").text(cantidadTotal);
                }
            });
        }
    });

    //boton generar los kits en la tabla
    jQuery("#generarKits").on("click", function (e) {
        // console.log("generarKits");
        e.preventDefault();
        let articulo = $(".serieEmpaqueCantidad").text();
        let identificadorFila = $("#referenceTabla").val();
        let idMov = $("#idCompra").val();

        let arrayArticulos = [];
        //recorrer la tabla de kits
        jQuery("#tableKits tbody tr").each(function (row, tr) {
            let articuloId = $(tr).find("td:eq(1)").text();
            let cantidad = $(tr).find("td:eq(4)").text();
            let descripcion = $(tr).find("td:eq(2)").text();
            let disponible = $(tr).find("td:eq(3)").text();
            let componente = $(tr).find("td:eq(2)").text();
            let tipo = $(tr).find("td:eq(5)").text();
            let observaciones = $(tr).find("td:eq(6)").find("input").val();

            //Editamos la información de los componentes de los kits
            let isArticuloKit =
                articulosKits[identificadorFila]?.hasOwnProperty("articulos");

            if (
                isArticuloKit &&
                articulosKits[identificadorFila]["articulos"].length > 0
            ) {
                articulosKits[identificadorFila]["articulos"].forEach(
                    (element) => {
                        if (element.articuloId == articuloId) {
                            let kitId = element.kitId;
                            arrayArticulos.push({
                                articuloId,
                                cantidad,
                                descripcion,
                                componente,
                                disponible,
                                tipo,
                                articulo,
                                observaciones,
                                kitId,
                            });
                        }
                    }
                );
            } else {
                arrayArticulos.push({
                    articuloId,
                    cantidad,
                    descripcion,
                    componente,
                    disponible,
                    tipo,
                    articulo,
                    observaciones,
                });
            }
        });

        //asignar el array de articulos al objeto articulosKits
        articulosKits[identificadorFila] = {
            articulos: arrayArticulos,
            cantidad: $("#cantidadKit").val(),
            ventaSeriesKits: articulosSerieTrans2,
        };

        cambiarCantidadKit();
        // cerrar modal
        $("#modalKitVentas").modal("hide");
    });

    function cambiarCantidadKit() {
        let cantidad = parseFloat($("#cantidadKit").val());
        let identificadorFila = $("#referenceTabla").val();

        $("#canti-" + identificadorFila).val(cantidad);
        $("#canti-" + identificadorFila).change();

        articulosKits[identificadorFila].cantidad = cantidad;
    }

    function validateCantidadesEmp() {
        let claveArt = $(".serieEmpaqueKey").text().trim();
        let estado = false;

        $("input[id^=" + claveArt + "-peso-]").each(function (index, obj) {
            if (
                obj.value < "0" ||
                obj.value == "" ||
                obj.value == "0" ||
                obj.value == "0.00" ||
                obj.value == "0.0"
            ) {
                estado = true;
            }
        });

        return estado;
    }

    function validateVacioEmp() {
        let claveArt = $(".serieEmpaqueKey").text().trim();
        let estado = false;

        $("input[id^=" + claveArt + "-peso-]").length == 0
            ? (estado = true)
            : (estado = false);

        return estado;
    }

    function limpiarTablaKits() {
        tablaKits.clear().draw();
    }

    function limpiarTablaEmpaque() {
        tablaEmpaques.clear().draw();
        jQuery("#totalBrutoEmpaque").html(0);
        jQuery("#totalUnidadEmpaque").html(0);
        jQuery("#totalNetoEmpaque").html(0);
        jQuery("#progressBarEmpaque").css("width", "0%");
        jQuery("#progressBarEmpaque").attr("aria-valuenow", 0);
        jQuery("#porcentajeBar").text("0% Complete (success)");
        jQuery("#progressBarEmpaque").removeClass("progress-bar-success");
        jQuery("#progressBarEmpaque").addClass("progress-bar-info");
    }

    $("#articulosModal").on("show.bs.modal", function (e) {
        let validarPrecio = validatePrecioLista();
        if (validarPrecio) {
            showMessage("No se ha seleccionado el precio de lista", "error");
            return false;
        }
        //poner el readonly el select de moneda
        // jQuery("#select-moneda").attr("readonly", true);

        const isInvalid = validateProveedorArticulo();
        const isInvalid2 = validateAlmacenArticulo();
        const isInvalid3 = validateCantidad();
        const isInvalid4 = validateImporte();

        if (isInvalid) {
            showMessage(
                "No se ha seleccionado al cliente en el movimiento",
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

        jQuery("#select-precioListaSelect").change();

        if (precioDefault !== "") {
            $.ajax({
                url: "/precioLista",
                type: "GET",
                data: {
                    list: precioDefault,
                    depot: $("#almacenKey").val(),
                },
                success: function ({ estatus, dataArticulos, sucursal }) {
                    if (estatus === 200) {
                        tablaArticulos.clear().draw();
                        let keyArticulos = Object.keys(dataArticulos);

                        keyArticulos.forEach((key) => {
                            let precio = currency(dataArticulos[key].precio, {
                                separator: ",",
                                precision: 2,
                                decimal: ".",
                                symbol: "$",
                            }).format();

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
                                        precio,
                                        cantidad,
                                        dataArticulos[key].articlesInv_depot,
                                        dataArticulos[key].articles_porcentIva,
                                        dataArticulos[key].articles_unitSale,
                                        dataArticulos[key].articles_type,
                                        dataArticulos[key].articles_retention1,
                                        dataArticulos[key].articles_retention2,
                                    ])
                                    .draw();
                            }
                        });
                    }
                },
            });
        }
    });

    $("#ArtExistencia").change(() => {
        jQuery("#select-precioListaSelect").change();
        let checkArticulosExistentes = $("#ArtExistencia").is(":checked");
        console.log(checkArticulosExistentes);
        //aquí no importa si la lista de precio esta vacia, ya que si no hay lista de precio, no se mostraran articulos
            $.ajax({
                url: "/precioListaExistencia",
                type: "GET",
                data: {
                    list: precioDefault,
                    depot: $("#almacenKey").val(),
                    checkArticulosExistentes : checkArticulosExistentes
                },
                success: function ({ estatus, dataArticulos, sucursal }) {
                    if (estatus === 200) {
                        tablaArticulos.clear().draw();
                        let keyArticulos = Object.keys(dataArticulos);

                        keyArticulos.forEach((key) => {
                            let precio = currency(dataArticulos[key].precio, {
                                separator: ",",
                                precision: 2,
                                decimal: ".",
                                symbol: "$",
                            }).format();

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
                                        precio,
                                        cantidad,
                                        dataArticulos[key].articlesInv_depot,
                                        dataArticulos[key].articles_porcentIva,
                                        dataArticulos[key].articles_unitSale,
                                        dataArticulos[key].articles_type,
                                        dataArticulos[key].articles_retention1,
                                        dataArticulos[key].articles_retention2,
                                    ])
                                    .draw();
                            }
                        });
                    }
                },
            });
        
    });

    $("#select-search-hide").change(() => {
        let categoria = $("#select-search-hide").val();
        console.log(categoria);

        if (categoria != "") {
            $.ajax({
                url: "/articulosCategoria",
                type: "GET",
                data: {
                    list: precioDefault,
                    depot: $("#almacenKey").val(),
                    categoria : categoria
                },
                success: function ({ estatus, dataArticulos, sucursal }) {
                    if (estatus === 200) {
                        tablaArticulos.clear().draw();
                        let keyArticulos = Object.keys(dataArticulos);

                        keyArticulos.forEach((key) => {
                            let precio = currency(dataArticulos[key].precio, {
                                separator: ",",
                                precision: 2,
                                decimal: ".",
                                symbol: "$",
                            }).format();

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
                                        precio,
                                        cantidad,
                                        dataArticulos[key].articlesInv_depot,
                                        dataArticulos[key].articles_porcentIva,
                                        dataArticulos[key].articles_unitSale,
                                        dataArticulos[key].articles_type,
                                        dataArticulos[key].articles_retention1,
                                        dataArticulos[key].articles_retention2,
                                    ])
                                    .draw();
                            }
                        });
                    }
                },
            });
        }
    });

    $("#select-search-familia").change(() => {
    let familia = $("#select-search-familia").val();

        if (familia != "") {
            $.ajax({
                url: "/articulosFamilia",
                type: "GET",
                data: {
                    list: precioDefault,
                    depot: $("#almacenKey").val(),
                    familia: familia
                },
                success: function ({ estatus, dataArticulos, sucursal }) {
                    if (estatus === 200) {
                        tablaArticulos.clear().draw();
                        let keyArticulos = Object.keys(dataArticulos);

                        keyArticulos.forEach((key) => {
                            let precio = currency(dataArticulos[key].precio, {
                                separator: ",",
                                precision: 2,
                                decimal: ".",
                                symbol: "$",
                            }).format();

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
                                        precio,
                                        cantidad,
                                        dataArticulos[key].articlesInv_depot,
                                        dataArticulos[key].articles_porcentIva,
                                        dataArticulos[key].articles_unitSale,
                                        dataArticulos[key].articles_type,
                                        dataArticulos[key].articles_retention1,
                                        dataArticulos[key].articles_retention2,
                                    ])
                                    .draw();
                            }
                        });
                    }
                },
            });
        }
    });

    $("#select-search-grupo").change(() => {
    let grupo = $("#select-search-grupo").val();

        if (grupo != "") {
            $.ajax({
                url: "/articulosGrupo",
                type: "GET",
                data: {
                    list: precioDefault,
                    depot: $("#almacenKey").val(),
                    grupo: grupo
                },
                success: function ({ estatus, dataArticulos, sucursal }) {
                    if (estatus === 200) {
                        tablaArticulos.clear().draw();
                        let keyArticulos = Object.keys(dataArticulos);

                        keyArticulos.forEach((key) => {
                            let precio = currency(dataArticulos[key].precio, {
                                separator: ",",
                                precision: 2,
                                decimal: ".",
                                symbol: "$",
                            }).format();

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
                                        precio,
                                        cantidad,
                                        dataArticulos[key].articlesInv_depot,
                                        dataArticulos[key].articles_porcentIva,
                                        dataArticulos[key].articles_unitSale,
                                        dataArticulos[key].articles_type,
                                        dataArticulos[key].articles_retention1,
                                        dataArticulos[key].articles_retention2,
                                    ])
                                    .draw();
                            }
                        });
                    }
                },
            });
        }
    });


    const $validator = jQuery("#progressWizard").validate({
        submitHandler: function (form) {
            jsonArticulos();

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
            motivoCancelacion: {
                required: function () {
                    let movimiento = $("#select-movimiento").val();
                    if (movimiento === "Rechazo de Venta") {
                        return true;
                    }

                    return false;
                },
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
            sellerKey: {
                required: true,
                maxlength: 50,
            },
            precioListaSelect: {
                required: true,
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
            motivoCancelacion: {
                required: function () {
                    let movimiento = $("#select-movimiento").val();
                    if (movimiento === "Rechazo de Venta") {
                        return "Por favor llena este campo";
                    }
                },
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
            sellerKey: {
                required: "Por favor llena este campo",
                maxlength: jQuery.validator.format("Maximo de {0} caracteres"),
            },
            precioListaSelect: {
                required: "Por favor llena este campo",
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
        const isValid = validateMovimiento();

        let key = $(this).val() !== " " ? $(this).val() : false;

        if (!isValid) {
            if (key) {
                getProveedorByClave(key);
            } else {
                jQuery("#proveedorFechaVencimiento").val("");
                jQuery("#proveedorFechaVencimiento").val("");
                jQuery("#select-proveedorCondicionPago").val("");
                jQuery("#select-proveedorCondicionPago").change();
                $("#proveedorName").val("");
            }
        } else {
            jQuery("#proveedorKey").val("");
            showMessage(
                "No se ha seleccionado el proceso de la venta",
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
                        let fechaEmision = document.getElementById("fechaEmision").value;
                        let fechaActual = moment(fechaEmision);
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

    function agregarTipo() {
        let condicionPago = jQuery("#select-proveedorCondicionPago").val();

        $.ajax({
            url: "/comercial/ventas/api/getCondicionPago",
            type: "GET",
            data: {
                condicionPago: condicionPago,
            },
            success: function (data) {
                jQuery("#tipoCondicion").val(data.creditConditions_type);
            },
        });
    }

    async function getProveedorByClave(clave) {
        await $.ajax({
            url: "/comercial/ventas/api/getCliente",
            type: "GET",
            data: {
                cliente: clave,
            },
            success: function (data) {
                if (Object.keys(data).length > 0) {
                    if (data.customers_creditCondition !== null) {
                        jQuery("#select-proveedorCondicionPago").val(
                            data.customers_creditCondition
                        );
                        jQuery("#select-proveedorCondicionPago").change();
                        // jQuery("#select-proveedorCondicionPago").attr(
                        //     "readonly",
                        //     true
                        // );

                        $("#proveedorName").val(data.customers_businessName);
                        tipoCondicionPago();
                    } else {
                        // jQuery("#select-proveedorCondicionPago").attr(
                        //     "readonly",
                        //     false
                        // );
                        $("#proveedorName").val(data.customers_businessName);
                    }

                    if (data.customers_priceList !== null) {
                        jQuery("#select-precioListaSelect").val(
                            "articles_" + data.customers_priceList
                        );
                        jQuery("#select-precioListaSelect").change();
                        // jQuery("#select-precioListaSelect").attr(
                        //     "readonly",
                        //     false
                        // );

                        $("#proveedorName").val(data.customers_businessName);
                        tipoCondicionPago();
                    } else {
                        // jQuery("#select-precioListaSelect").attr(
                        //     "readonly",
                        //     false
                        // );
                        $("#proveedorName").val(data.customers_businessName);
                    }

                    if (data.customers_RFC !== null) {
                        $("#rfc-cliente").val(data.customers_RFC);
                    }
                    if (data.customers_identificationCFDI !== null) {
                        $("#select-clienteCFDI").val(
                            data.customers_identificationCFDI
                        );
                        $("#select-clienteCFDI").change();
                    }
                } else {
                    jQuery("#proveedorFechaVencimiento").val("");
                    jQuery("#select-proveedorCondicionPago").val("");
                    jQuery("#select-proveedorCondicionPago").change();
                    // jQuery("#select-money").val("");
                    jQuery("#select-money").change();
                    $("#proveedorName").val("");
                }
                //validamos que la clave del proveedor exista
                if (Object.keys(data).length > 0) {
                    jQuery("#proveedorKey").val(data.customers_key);
                } else {
                    jQuery("#proveedorKey").val("");
                    //insertar un mensaje de alerta
                    swal({
                        title: "Error",
                        text: "La clave del cliente no existe",
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
        let validarMov = validateMovimiento();
        let claveMonedaSat = $("#clave_sat_moneda").val().trim() !== "MXN";
        let rfc = $("#rfc-cliente").val().trim().toUpperCase();
        let isRfcComercioExterior = rfc.slice(0, 2) == "XE";

        // console.log(claveMonedaSat, rfc, isRfcComercioExterior);

        if (validarMov) {
            showMessage("Por favor selecciona un movimiento", "error");
            return false;
        }

        let validarCondicion = validateCondicionPago();
        if (validarCondicion) {
            showMessage("Por favor selecciona una condición de pago", "error");
            return false;
        }

        let validarVendedor = validateVendedor();
        if (validarVendedor) {
            showMessage("Por favor selecciona un vendedor", "error");
            return false;
        }

        let validarPrecio = validatePrecioLista();
        if (validarPrecio) {
            showMessage("No se ha seleccionado el precio de lista", "error");
            return false;
        }

        let validarConcepto = validateConceptoProveedor();
        if (validarConcepto) {
            showMessage("No se ha seleccionado el concepto", "error");
            return false;
        }
        

        e.preventDefault();
        let status = $("#status").text().trim();
        let articulos = $("#cantidadArticulos").val();
        let movimiento = $("#select-movimiento").val();

        if (movimiento === "Rechazo de Venta") {
            let motivoCancelacion = validateMotivoCancelacion();
            if (motivoCancelacion) {
                showMessage(
                    "Por favor ingresa un motivo de cancelación",
                    "error"
                );
                return false;
            }
        }

        const isInvalid = validateCantidad();
        const isInvalid2 = validateImporte();
        let $condicionPago = jQuery("#select-proveedorCondicionPago")
            .find("option:selected")
            .text()
            .trim();

        if (status === "INICIAL") {
            if (articulos > 0) {
                if (isInvalid || isInvalid2) {
                    showMessage(
                        "La cantidad o el costo no pueden ser menores a cero",
                        "error"
                    );
                } else {
                    if (
                        (movimiento === "Factura" &&
                            $condicionPago === "CONTADO") ||
                        $condicionPago === "Contado" ||
                        $condicionPago === "contado"
                    ) {
                        if (
                            movimiento === "Factura" &&
                            claveMonedaSat &&
                            isRfcComercioExterior
                        ) {
                            $("#agregarDatosComercio").text("Agregar");
                            $("#modalComercioExterior").modal({
                                backdrop: "static",
                                keyboard: true,
                                show: true,
                            });
                        } else {
                            $("#cuentaPago").change();

                            $("#ventasCalculadora").modal({
                                backdrop: "static",
                                keyboard: true,
                                show: true,
                            });
                        }
                    } else {
                        //validamos si el movimiento es dolar y si el RFC DEL CLIENTE ES EXTRANJERO
                        if (
                            movimiento === "Factura" &&
                            claveMonedaSat &&
                            isRfcComercioExterior
                        ) {
                            $("#agregarDatosComercio").text(
                                "Afectar Movimiento"
                            );

                            $("#modalComercioExterior").modal({
                                backdrop: "static",
                                keyboard: true,
                                show: true,
                            });
                        } else {
                            if (movimiento === "Factura") {
                                $("#facturaModal").modal({
                                    backdrop: "static",
                                    keyboard: true,
                                    show: true,
                                });
                            } else {
                                afectarRequest("/comercial/ventas/afectar");
                            }
                        }
                    }
                }
            } else {
                showMessage(
                    "No se puede afectar una venta sin artículos",
                    "error"
                );
            }
        } else if (movimiento === "Cotización" || movimiento === "Pedido") {
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

    $("#agregarDatosComercio").click(function (e) {
        e.preventDefault();
        //validamos que el select tipo Operación contenga un dato

        let isTipoOperacionVacio =
            $("#select-tipoOperacion").val() === "" ? true : false;

        if (isTipoOperacionVacio) {
            showMessage2(
                "Tipo Operación vacia",
                "Necesita seleccionar un tipo de operación para afectar el movimiento",
                "warning"
            );
            return false;
        }

        if (!isTipoOperacionVacio) {
            let validacionComercio = true;
            let incoterm = $("#incoTermKey").val() === "" ? true : false;
            let clavePedimiento =
                $("#select-clavePedimento").val() === "" ? true : false;
            let subdivision = $("#subdivision").is(":checked");
            let origen = $("#origen").is(":checked");

            if (incoterm) {
                validacionComercio = false;
                showMessage2(
                    "Incoterm vacia",
                    "Necesita seleccionar un data de Incoterm para afectar el movimiento",
                    "warning"
                );
                return validacionComercio;
            }

            if (clavePedimiento) {
                validacionComercio = false;
                showMessage2(
                    "Clave pedimiento vacia",
                    "Necesita seleccionar una clave pedimento para afectar el movimiento",
                    "warning"
                );

                return validacionComercio;
            }

            if (subdivision) {
                let isVacio =
                    $("#numExportadorConfiable").val() === "" ? true : false;

                if (isVacio) {
                    validacionComercio = false;
                    showMessage2(
                        "Subdivisión",
                        "Necesita llenar el dato de exportador confiable",
                        "warning"
                    );
                    return validacionComercio;
                }
            }

            if (origen) {
                let isVacio =
                    $("#numCertificadoOrigen").val() === "" ? true : false;

                if (isVacio) {
                    validacionComercio = false;
                    showMessage2(
                        "Certificado Origen",
                        "Necesita llenar el dato de certificado origen",
                        "warning"
                    );
                    return validacionComercio;
                }
            }
        }
        //Guardamos la información para el proceso de comercio exterior
        let inputComercioExterior = $("#inputJsonComercioExterior");

        let comercioExterior = {
            IncotermKey: $("#incoTermKey").val(),
            subdivision: $("#subdivision").prop("checked"),
            origen: $("#origen").prop("checked"),
            mTraslado: $("#motivoTrasladoKey").val(),
            tOperacion: $("#select-tipoOperacion").val(),
            cPedimento: $("#select-clavePedimento").val(),
            cOrigen: $("#numCertificadoOrigen").val(),
            eConfiable: $("#numExportadorConfiable").val(),
        };

        inputComercioExterior.attr("value", JSON.stringify(comercioExterior));

        let $condicionPago = jQuery("#select-proveedorCondicionPago")
            .find("option:selected")
            .text()
            .trim();

        let movimiento = $("#select-movimiento").val();
        $("#modalComercioExterior").modal("hide");
        if (
            (movimiento === "Factura" && $condicionPago === "CONTADO") ||
            $condicionPago === "Contado" ||
            $condicionPago === "contado"
        ) {
            $("#cuentaPago").change();
            $("#ventasCalculadora").modal({
                backdrop: "static",
                keyboard: true,
                show: true,
            });
        } else {
            // afectarRequest("/comercial/ventas/afectar");

            $("#facturaModal").modal({
                backdrop: "static",
                keyboard: true,
                show: true,
            });
        }

        //guardamos la informacion del comercio exterior
    });

    //change de los checkbox comercio exterior
    $("#subdivision").change((e) => {
        let isCheck = e.target.checked;
        if (isCheck) {
            $("#numExportadorConfiable").prop("readonly", false);
        } else {
            $("#numExportadorConfiable").prop("readonly", true);
            $("#numExportadorConfiable").val("");
        }
    });
    $("#origen").change((e) => {
        let isCheck = e.target.checked;
        if (isCheck) {
            $("#numCertificadoOrigen").prop("readonly", false);
        } else {
            $("#numCertificadoOrigen").prop("readonly", true);
            $("#numCertificadoOrigen").val("");
        }
    });

    jQuery("#select-movimiento").on("change", function (e) {
        const mov = $("#select-movimiento").val();
        if (mov === "Pedido" || mov === "Factura") {
            $("#contrato").show();
            if (mov === "Factura") {
                $(".tablaDesborde").css("width", "2000");
            } else {
                $(".tablaDesborde").css("width", "2100");
            }
            // $(".cantidadNeta").show();
            // $(".unidadEmpaque").show();
        } else {
            $("#contrato").hide();
            // $(".cantidadNeta").hide();
            // $(".unidadEmpaque").hide();
        }
    });

    jQuery("#select-motivoCancelacion").on("change", function (e) {
        const motivo = $("#select-motivoCancelacion").val();
        //si el motivo de la canción tiene la clave 01 se activa el campo de la factura
        if (motivo === "01") {
            $("#select-folioSustitucion").prop("disabled", false);
        } else {
            $("#select-folioSustitucion").prop("disabled", true);
            $("#select-folioSustitucion").val("");
        }
    });

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

        let radioFactura = jQuery("#generarFactura").is(":checked");
        let radioPedido = jQuery("#generarPedido").is(":checked");
        let radioVentaPerdida = jQuery("#generarVentaPerdida").is(":checked");
        let movSelecActual = $("#select-movimiento").val();
        let showModal = false;

        if (
            radioFactura === false &&
            radioPedido === false &&
            radioVentaPerdida === false
        ) {
            showMessage("Seleccione una opción", "error");
        }

        let movimiento;
        let listaArticulos = jsonArticulos();
        let claveArticuloSerie;
        let isSerieVacio = false;
        let isSerieVacio2 = false;
        let isSerieVacio3 = false;
        let isSerieRepetido = false;
        let repetidos = {};

        const keyListaArticulos = Object.keys(listaArticulos);
        // console.log(listaArticulos);

        if (movSelecActual == "Factura" || movSelecActual == "Pedido") {
            //Validamos si el movimiento es de serie o no

            keyListaArticulos.forEach((keyArticulo) => {
                if (listaArticulos[keyArticulo].tipoArticulo == "Serie") {
                    if (
                        listaArticulos[keyArticulo].venderSerie === undefined ||
                        listaArticulos[keyArticulo].venderSerie === null
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
                    "warning"
                );
                $("#loader").hide();
            } else {
                keyListaArticulos.forEach((keyArticulo) => {
                    if (listaArticulos[keyArticulo].tipoArticulo == "Serie") {
                        listaArticulos[keyArticulo]["venderSerie"].forEach(
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
                            "Por favor, ingresa diferentes series",
                            "error"
                        );
                        $("#loader").hide();
                        isSerieRepetido = true;
                        return false;
                    }
                });
            }
        }

        keyListaArticulos.forEach((keyArticulo) => {
            if (movSelecActual == "Factura" || movSelecActual == "Pedido") {
                if (listaArticulos[keyArticulo].tipoArticulo == "Kit") {
                    if (
                        listaArticulos[keyArticulo].ventaKit === undefined ||
                        listaArticulos[keyArticulo].ventaKit === null
                    ) {
                        isSerieVacio2 = true;
                        claveArticuloKit = keyArticulo;
                        return false;
                    }

                    listaArticulos[keyArticulo].ventaKit["articulos"].forEach(
                        (articulo) => {
                            if (articulo.tipo == "Serie") {
                                let clavesSeries = Object.keys(
                                    listaArticulos[keyArticulo].ventaKit[
                                        "ventaSeriesKits"
                                    ]
                                );
                                //verificar que el articulo tenga configurado sus series en el kit
                                if (
                                    clavesSeries.includes(articulo.articuloId)
                                ) {
                                    //verificar que el articulo tenga series configuradas
                                    if (
                                        listaArticulos[keyArticulo].ventaKit[
                                            "ventaSeriesKits"
                                        ][articulo.articuloId].length == 0
                                    ) {
                                        isSerieVacio3 = true;
                                        claveArticuloKit = articulo.articuloId;
                                        return false;
                                    }
                                } else {
                                    isSerieVacio3 = true;
                                    claveArticuloKit = articulo.articuloId;
                                    return false;
                                }
                            }
                        }
                    );
                }

                //    console.log(listaArticulos[keyArticulo].ventaKit['articulos']);
            }
        });

        if (isSerieVacio2) {
            showMessage2(
                "Artículo Kit",
                `Por favor, verifique que el artículo con clave ${
                    claveArticuloKit.split("-")[0]
                } tenga completo sus kits`,
                "warning"
            );
            $("#loader").hide();
        }

        if (isSerieVacio3) {
            showMessage2(
                "Artículo Kit",
                `Por favor, verifique que el artículo en el kit con clave ${
                    claveArticuloKit.split("-")[0]
                } tenga completo sus números de serie`,
                "warning"
            );
            $("#loader").hide();
        }

        if (
            !isSerieRepetido &&
            !isSerieVacio &&
            !isSerieVacio2 &&
            !isSerieVacio3
        ) {
            if (radioFactura === true) {
                if ($("#origin").val() === "") {
                    let movOriginal = $("#select-movimiento").val();
                    $("#origin").val(movOriginal);
                }

                movimiento = "Factura";
                $("#select-movimiento").val(movimiento);

                //Verificamos si el usuario tiene permisos para afectar
                let $domPermiso = movimiento.replace(/\s/g, "");

                if ($("#" + $domPermiso).val() !== "true") {
                    showMessage2(
                        "Permisos Insuficientes",
                        "No tienes permisos para afectar",
                        "warning"
                    );
                } else {
                    $("#idCompra").val("0");
                    showModal = true;
                }

                // $('#select-movimiento').trigger('change');
            }
            if (radioPedido === true) {
                if ($("#origin").val() === "") {
                    let movOriginal = $("#select-movimiento").val();
                    $("#origin").val(movOriginal);
                }
                movimiento = "Pedido";
                $("#select-movimiento").val(movimiento);
                //Verificamos si el usuario tiene permisos para afectar
                let $domPermiso = movimiento.replace(/\s/g, "");

                if ($("#" + $domPermiso).val() !== "true") {
                    showMessage2(
                        "Permisos Insuficientes",
                        "No tienes permisos para afectar",
                        "warning"
                    );
                } else {
                    $("#idCompra").val("0");
                    showModal = true;
                }
                // $('#select-movimiento').trigger('change');
            }
            if (radioVentaPerdida === true) {
                if ($("#origin").val() === "") {
                    let movOriginal = $("#select-movimiento").val();
                    $("#origin").val(movOriginal);
                }
                movimiento = "Rechazo de Venta";
                $("#select-movimiento").val(movimiento);
                //Verificamos si el usuario tiene permisos para afectar
                let $domPermiso = movimiento.replace(/\s/g, "");

                if ($("#" + $domPermiso).val() !== "true") {
                    showMessage2(
                        "Permisos Insuficientes",
                        "No tienes permisos para afectar",
                        "warning"
                    );
                } else {
                    $("#idCompra").val("0");
                    showModal = true;
                }
                // $('#select-movimiento').trigger('change');
            }

            if (showModal) {
                $("#modalCompra2").modal({
                    backdrop: "static",
                    keyboard: true,
                    show: true,
                });
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
                        $("#cantidadNeta-" + clave_posicion_articulo).val(
                            cantidadRecibir
                        );
                        $("#unid-" + clave_posicion_articulo).trigger("change");
                        $("#c_unitario-" + clave_posicion_articulo).trigger(
                            "onchange"
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
                        "onchange"
                    );
                    calcularTotales();
                }

                // console.log(filaArticulo, cantidadPendiente);
            });
            $("#progressWizard").submit();
            $("#modalCompra2").modal("hide");
        }
    });

    const eliminar = function (e) {
        e.preventDefault();

        const id = $("#idCompra").val();

        if (id === "0") {
            showMessage("No se ha seleccionado ninguna venta", "error");
        } else {
            swal({
                title: "¿Está seguro de eliminar la venta?",
                text: "Una vez eliminada no podrá recuperarla",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "/eliminarVenta",
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
                                    window.location.href = "/comercial/ventas";
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
        const timbrado = $("#timbradoKey").val();
        const empresaImpuesto = $("#empresaImpuesto").val();
        console.log(timbrado);

        if (
            (status === "FINALIZADO" && movimiento === "Factura" && (timbrado === 0 || timbrado === '0')) ||
            (timbrado === null && (empresaImpuesto === 1 || empresaImpuesto === '1') && movimiento === "Factura") ||
            (empresaImpuesto === 1 && movimiento === "Factura") ||
            (status === "FINALIZADO" && movimiento === "Rechazo de Venta")
        ) {
            swal({
                title: "¿Está seguro de cancelar la venta?",
                text: movimiento + " : " + folio + "",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "/cancelarVenta/",
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
                                        "/comercial/ventas/create/" + id;
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
            // return false;
        }
        else if (
            (status === "POR AUTORIZAR" && movimiento === "Cotización") ||
            (status === "POR AUTORIZAR" && movimiento === "Pedido")
        ) {
            const id = $("#idCompra").val();
            swal({
                title: "¿Está seguro de cancelar la venta?",
                text: movimiento + " : " + folio + "",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "/cancelarMovimiento/",
                        type: "get",
                        data: { id: id },
                        success: function ({ estatus, mensaje }) {
                            if (estatus === 200) {
                                showMessage2(
                                    "Cancelación exitosa",
                                    mensaje,
                                    "success"
                                );
                                setTimeout(function () {
                                    window.location.href =
                                        "/comercial/ventas/create/" + id;
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
        } else if (
            (status === "FINALIZADO" &&
                movimiento === "Factura" &&
                (timbrado === "1" || timbrado === 1) &&
                empresaImpuesto === "0") ||
            empresaImpuesto === 0
        ) {
            $("#cancelarFacturaModal").modal({
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
                url: "/cancelarMovimiento/",
                type: "get",
                data: { id: id },
                success: function ({ estatus, mensaje }) {
                    if (estatus === 200) {
                        showMessage2("Cancelación exitosa", mensaje, "success");
                        setTimeout(function () {
                            window.location.href = "/comercial/ventas";
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
                        url: "/cancelarMovPendiente/",
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
                                        "/comercial/ventas/create/" + id;
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
        (estatus === "FINALIZADO" && movimiento === "Cotización") ||
        (estatus === "FINALIZADO" && movimiento === "Pedido")
    ) {
        jQuery("#cancelar-boton").unbind("click");
        jQuery("#cancelar-boton").hide();
    } else {
        jQuery("#cancelar-boton").click(cancelar);
        jQuery("#cancelar-boton").show();
    }

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
        if (entro) {
            $("#contenedorImagen").hide();
            $("#loading2").show();

            $.ajax({
                url: "/getCostoPromedio3/ventas",
                method: "GET",
                data: {
                    id: articulo,
                    idCompra: compra,
                },
                success: function ({
                    estatus,
                    data,
                    articulosByAlmacen,
                    imagenesArticulo,
                    articulosKitInfo,
                    unidad,
                }) {
                    $("#loading2").hide();

                    if (estatus === 200) {
                        if (data !== null) {
                            // console.log(data, articulosByAlmacen, imagenesArticulo, articulosKitInfo);
                            let precioLista = currency(
                                data.articles_listPrice1,
                                {
                                    separator: ",",
                                    precision: 4,
                                    symbol: "$",
                                }
                            ).format();

                            let ultimoCosto =
                                data.articlesCost_lastCost != undefined
                                    ? truncarDecimales(
                                          formatoMexico(
                                              data.articlesCost_lastCost
                                          ),
                                          4
                                      )
                                    : 0;

                            let ultimoCostoPromedio =
                                data.articlesCost_averageCost != undefined
                                    ? truncarDecimales(
                                          formatoMexico(
                                              data.articlesCost_averageCost
                                          ),
                                          4
                                      )
                                    : 0;

                            $("#articuloCostoPromedio").val(data.articles_key);
                            $("#descripcionCostoPromedio").val(
                                data.articles_descript
                            );
                            $("#disponibleCostoPromedio").val(
                                data.articlesInv_inventory
                            );
                            $("#existenciaCostoPromedio").val(
                                data.articlesInv_inventory
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
                            $("#unidadCostoPromedio").val(
                                unidad[data.articles_unitBuy]
                            );
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
                        }

                        if (imagenesArticulo.length > 0) {
                            //agrergamos las imagenes al div contenedorImagen
                            $(".carousel-inner").html("");
                            imagenesArticulo.forEach((element, index) => {
                                // console.log(element);

                                let FileArray =
                                    element.articlesImg_file.split("/");
                                let nombreImagen =
                                    FileArray[FileArray.length - 1];
                                let longitud = FileArray.length;
                                let urlHost = window.location.host;
                                let urlProtocol = window.location.protocol;
                                let quitamosDiagonales =
                                    urlProtocol +
                                    "//" +
                                    urlHost +
                                    "/archivo/" +
                                    element.articlesImg_path.replace(
                                        /\\/g,
                                        "/"
                                    );

                                //mostrar la imagen usando el url de la base de datos
                                $(".carousel-inner").append(`
                                <div class="item ${index == 0 ? "active" : ""}">
                                <div class='imgContenedorPreview' style="display:flex; justify-content:center" id="">
                                                                   
                                                                    <a data-fancybox='demo' data-src='${quitamosDiagonales}'>
                                                                        <img src='${quitamosDiagonales}' class="imgPreview">
                                                                    </a>
                                                                </div>
                                </div>
                                `);

                                $("#contenedorImagen").show();
                            });
                        }

                        if (articulosKitInfo != null) {
                            if (articulosKitInfo.length > 0) {
                                // console.log(articulosKitInfo);
                                $(".tableKitsArticulos").html("");
                                articulosKitInfo.forEach((element, index) => {
                                    // console.log(element);
                                    $(".tableKitsArticulos").append(`
                                    <tr>
                                    <td>${element.articles_descript}</td>
                                    <td>${element.articlesInv_inventory}</td>
                                    </tr>
                                    `);
                                });
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
        $("#KitsArticulos").removeClass("active");
        $("#siguiente").removeClass("active");

        $("#li1").addClass("active");
        $("#li2").removeClass("active");
        $("#li3").removeClass("active");
        $("#li4").removeClass("active");
        $("#li5").removeClass("active");
        
    });

    //Formato money al modal Cobro-Factura
    $(".formatoMoney").change(function () {
        let importeId = $(this).attr("id");
        let tipoCuenta = $("#tipoCuenta").val();
        let totalCobrado = 0;
        let importe = $(this)
            .val()
            .replace(/['$', ',']/g, "");
        let importeFormato = currency(importe, {
            separator: ",",
            precision: 2,
            symbol: "$",
        }).format();

        $("#" + importeId).val(importeFormato);

        $(".formatoMoney").each(function (index, input) {
            let importe = $(this)
                .val()
                .replace(/['$', ',']/g, "");
            if (importe !== "") {
                totalCobrado += parseFloat(importe);
            }
        });

        let totalCobradoFormato = currency(totalCobrado, {
            separator: ",",
            precision: 2,
            symbol: "$",
        }).format();

        $("#totalCobrado").val(totalCobradoFormato);
        if (isSalesPaymentSave !== "1") {
            let totalFactura = parseFloat(
                $("#totalC")
                    .val()
                    .replace(/['$', ',']/g, "")
            );

            let cambio = 0;

            if (tipoCuenta === "Caja") {
                if (totalCobrado >= totalFactura) {
                    cambio = totalFactura - totalCobrado;
                }
            }
            let cambioFormato = currency(cambio, {
                separator: ",",
                precision: 2,
                symbol: "$",
            }).format();

            $("#cambioTotal").val(cambioFormato);
        }
    });

    $("#cuentaPago").change(async function () {
        let cuenta = $("#cuentaPago").val();
        let cambio = 0;
        let totalFactura = parseFloat(
            $("#totalC")
                .val()
                .replace(/['$', ',']/g, "")
        );
        let totalCobrado = parseFloat(
            $("#totalCobrado")
                .val()
                .replace(/['$', ',']/g, "")
        );
        if (cuenta !== null && cuenta !== "") {
            //Consultar el nombre de la cuenta
            await $.ajax({
                url: "/tesoreria/nombre/cuenta/",
                method: "GET",
                data: {
                    cuenta: cuenta,
                },
                success: function ({ status, data }) {
                    if (status === 200) {
                        if (data?.moneyAccounts_accountType == "Banco") {
                            let cambioFormato = currency(cambio, {
                                separator: ",",
                                precision: 2,
                                symbol: "$",
                            }).format();
                            $("#cambioTotal").val(cambioFormato);

                            let formatoFactura = currency(totalFactura, {
                                separator: ",",
                                precision: 2,
                                symbol: "$",
                            }).format();

                            $("#totalCobrado").val(formatoFactura);
                            //limpiamos los importes
                            $("#cantidad1")
                                .val(formatoFactura)
                                .attr("readonly", true);
                            $("#cantidad2")
                                .val(cambioFormato)
                                .attr("readonly", true);
                            $("#cantidad3")
                                .val(cambioFormato)
                                .attr("readonly", true);
                            $("#metodoPago7").val("").attr("readonly", true);
                            $("#metodoPago7").change();
                            $(".cobrosForm").on("change", function () {
                              let metodoPago = $(this).find("option:selected").data();

                              if (metodoPago?.target == "01") {
                                // Le ponemos el valor de efectivo al metodoPago7. Usamos el valor en lugar de target
                                $("#metodoPago7").val("").attr("readonly", false);
                                $("#metodoPago7").change();
                              } else {
                                $("#metodoPago7").val("").attr("readonly", true);
                                $("#metodoPago7").change();
                              }
                            });
                        } else {
                            $("#cantidad1").attr("readonly", false);
                            $("#cantidad2").attr("readonly", false);
                            $("#cantidad3").attr("readonly", false);
                            $("#metodoPago7").attr("readonly", false);
                            if (totalCobrado >= totalFactura) {
                                cambio = totalFactura - totalCobrado;
                            }
                            totalCobrado = 0;
                            let cambioFormato = currency(cambio, {
                                separator: ",",
                                precision: 2,
                                symbol: "$",
                            }).format();
                            $("#cambioTotal").val(cambioFormato);
                            $("#cambioTotal").show();

                            $(".formatoMoney").each(function (index, input) {
                                let importe = $(this)
                                    .val()
                                    .replace(/['$', ',']/g, "");
                                if (importe !== "") {
                                    totalCobrado += parseFloat(importe);
                                }
                            });

                            let totalCobradoFormato = currency(totalCobrado, {
                                separator: ",",
                                precision: 2,
                                symbol: "$",
                            }).format();

                            $("#totalCobrado").val(totalCobradoFormato);
                        }
                        $("#tipoCuenta").val(data.moneyAccounts_accountType);
                    }
                },
            });
        }
    });

    $("#btn-modal-venta").click(async function () {
        $("#cuentaPago").change();
        let inputSaveCobro = $("#inputJsonCobroFactura");
        let isImporteVacio = validarModalImportes();
        let isMayorCobro = validarModalCobro();
        let isCuentaVacio = validarModalCuentaDinero();
        let isInfAdicionalVacio = validarModalInfAdicional();
        let isFormaCambioVacio = validarModalFormaCambio();
        let isCuentaSaldo = true;
        let cuenta = $("#cuentaPago").val();
        let cambio = $("#cambioTotal").val().replace(/[$,-]/g, "");
        let tipoCuenta = $("#tipoCuenta").val();
        // console.log(tipoCuenta);
        let validarFormasPagoImporte = validarFormasPago();

        if (tipoCuenta === "Banco") {
            isFormaCambioVacio = false;
        }

        if (cuenta !== null && cuenta !== "" && tipoCuenta === "Caja") {
            //Consultar el saldo de la cuenta
            await $.ajax({
                url: "/tesoreria/saldo/cuenta/",
                method: "GET",
                data: {
                    cuenta: cuenta,
                },
                success: function ({ status, data }) {
                    let cantidadCambio = parseFloat(cambio);
                    if (status === 200) {
                        if (data.moneyAccountsBalance_balance !== null) {
                            let saldo = parseFloat(
                                data.moneyAccountsBalance_balance
                            );

                            if (saldo >= cantidadCambio) {
                                isCuentaSaldo = true;
                            } else {
                                isCuentaSaldo = false;
                            }
                        } else {
                            if (cantidadCambio === 0 || cantidadCambio === "") {
                                isCuentaSaldo = true;
                            } else {
                                isCuentaSaldo = false;
                            }
                        }
                    }
                },
            });
        }

        if (
            !isImporteVacio ||
            !isMayorCobro ||
            isCuentaVacio ||
            isInfAdicionalVacio ||
            isFormaCambioVacio ||
            !isCuentaSaldo ||
            validarFormasPagoImporte
        ) {
            showMessage2(
                "Error",
                !isImporteVacio
                    ? "El importe no puede estar vacio"
                    : !isMayorCobro
                    ? "El cobro no puede ser menor al total de la factura"
                    : isCuentaVacio
                    ? "La cuenta de dinero no puede estar vacia"
                    : isInfAdicionalVacio
                    ? "La informacion adicional no puede estar vacia"
                    : isFormaCambioVacio
                    ? "La forma de cambio no puede estar vacia"
                    : !isCuentaSaldo
                    ? "El saldo de la cuenta es insuficiente"
                    : validarFormasPagoImporte
                    ? "El importe tiene que tener una forma de pago"
                    : "",
                "error"
            );
        } else {
            let jsonCobroFactura = {
                importe1: $("#cantidad1")
                    .val()
                    .replace(/['$', ',']/g, ""),
                importe2: $("#cantidad2")
                    .val()
                    .replace(/['$', ',']/g, ""),
                importe3: $("#cantidad3")
                    .val()
                    .replace(/['$', ',']/g, ""),
                formaCobro1: $("#metodoPago1").val(),
                formaCobro2: $("#metodoPago2").val(),
                formaCobro3: $("#metodoPago3").val(),
                formaCambio7: $("#metodoPago7").val(),
                cuentaPago: $("#cuentaPago").val(),
                accountType: $("#tipoCuenta").val(),
                infAdicional: $("#infAdicional").val(),
                totalFactura: $("#totalC")
                    .val()
                    .replace(/['$', ',']/g, ""),
                cambio: $("#cambioTotal").val().replace(/[$,-]/g, ""),
            };
            inputSaveCobro.attr("value", JSON.stringify(jsonCobroFactura));
            $("#ventasCalculadora").modal("hide");

            $("#facturaModal").modal({
                backdrop: "static",
                keyboard: true,
                show: true,
            });
            // afectarRequest("/comercial/ventas/afectar");
        }
    });

    $("#btn-modal-cancelarFactura").click(async function () {
        let isMotivoVacio = validarMotivoCancelacion();
        let isFolioSustitucionVacio = validarFolioSustitucion();
        const id = $("#idCompra").val();

        //se valida el motivo de cancelacion y el folio de sustitucion nada más se valida si el motivo de cancelacion es diferente a 01

        if (isMotivoVacio || isFolioSustitucionVacio) {
            showMessage2(
                "Precaución",
                isMotivoVacio
                    ? "El motivo de cancelación no puede estar vacio"
                    : isFolioSustitucionVacio
                    ? "El folio de sustitución no puede estar vacio"
                    : "",
                "error"
            );
        } else {
            swal({
                title: "¿Está seguro de cancelar la factura?",
                text: "Una vez cancelada no podrá realizar cambios",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                if (willDelete) {
                    let jsonCancelacion = {
                        motivoCancelacion: $("#select-motivoCancelacion").val(),
                        folioSustitucion: $("#select-folioSustitucion").val(),
                    };
                    $("#loader").show();
                    $.ajax({
                        url: "/cancelarVenta/",
                        type: "get",
                        data: {
                            id: id,
                            inputJsonCancelacionFactura:
                                JSON.stringify(jsonCancelacion),
                        },
                        success: function ({ estatus, mensaje }) {
                            if (estatus === 200) {
                                showMessage2(
                                    "Cancelacion exitosa",
                                    mensaje,
                                    "success"
                                );
                                setTimeout(function () {
                                    window.location.href =
                                        "/comercial/ventas/create/" + id;
                                }, 1000);
                                //ceramos el modal
                                $("#cancelarFacturaModal").hide();
                                $("#loader").hide();
                            } else {
                                showMessage2(
                                    "Error al cancelar",
                                    mensaje,
                                    "error"
                                );
                                $("#cancelarFacturaModal").hide();
                                $("#loader").hide();
                            }
                        },
                    });
                }
            });
        }
    });

    function validarModalImportes() {
        let isImporteUnoLLEno = false;

        $(".formatoMoney").each(function (index, input) {
            let importe = parseFloat(
                $(this)
                    .val()
                    .replace(/['$', ',']/g, "")
            );

            if (importe !== 0) {
                isImporteUnoLLEno = true;
                return false;
            }
        });
        return isImporteUnoLLEno;
    }

    function validarModalCobro() {
        let isCobroMayor = false;
        let totalFactura = parseFloat(
            $("#totalC")
                .val()
                .replace(/['$', ',']/g, "")
        );
        let totalCobrado = parseFloat(
            $("#totalCobrado")
                .val()
                .replace(/['$', ',']/g, "")
        );

        if (totalCobrado >= totalFactura) {
            isCobroMayor = true;
        }

        return isCobroMayor;
    }

    function validarModalCuentaDinero() {
        let isCuentaDineroVacia = false;
        let cuentaDinero = $("#cuentaPago").val();

        if (cuentaDinero === "") {
            isCuentaDineroVacia = true;
        }
        return isCuentaDineroVacia;
    }

    //funcion para validar si hay datos en la columna de información adicional
    function validarModalInfAdicional() {
        let isInfAdicionalVacia = false;
        let infAdicional = $("#infAdicional").val();

        if (
            $("#metodoPago1").val() == "5" ||
            $("#metodoPago1").val() == "6" ||
            $("#metodoPago2").val() == "5" ||
            $("#metodoPago2").val() == "6" ||
            $("#metodoPago3").val() == "5" ||
            $("#metodoPago3").val() == "6"
        ) {
            if (infAdicional === "") {
                isInfAdicionalVacia = true;
            }
            return isInfAdicionalVacia;
        }
    }

    function validarModalFormaCambio() {
        let isFormaCambioVacia = false;
        let formaCambio = $("#metodoPago7").val();
        console.log(formaCambio);

        if (formaCambio === "") {
            isFormaCambioVacia = true;
        }
        return isFormaCambioVacia;
    }

    function validarFormasPago() {
        let isImporteSinFormaPago = false;

        $(".formatoMoney").each(function (index, input) {
            let importe = parseFloat(
                $(this)
                    .val()
                    .replace(/['$', ',']/g, "")
            );

            if (importe !== 0) {
                let formaPagoId = $(this).attr("id").split("cantidad")[1];
                let formaPago = $("#metodoPago" + formaPagoId).val();
                console.log(formaPago);
                if (formaPago === "" || formaPago === null) {
                    isImporteSinFormaPago = true;
                    return false;
                }
            }
        });
        return isImporteSinFormaPago;
    }

    function validarMotivoCancelacion() {
        let isMotivoCancelacionVacio = false;
        let motivoCancelacion = $("#select-motivoCancelacion").val();

        if (motivoCancelacion === null || motivoCancelacion === "") {
            isMotivoCancelacionVacio = true;
        }
        return isMotivoCancelacionVacio;
    }

    function validarFolioSustitucion() {
        let isFolioSustitucionVacio = false;
        let folioSustitucion = $("#select-folioSustitucion").val();
        let motivoCancelacion = $("#select-motivoCancelacion").val();

        //unicamente se valida si el motivo de cancelacion es por clave 01
        if (motivoCancelacion === "01") {
            if (folioSustitucion === null || folioSustitucion === "") {
                isFolioSustitucionVacio = true;
            }
        }
        return isFolioSustitucionVacio;
    }
    //accion boton siguiente
    $("#siguiente-Flujo").click(async function (e) {
        e.preventDefault();
        const dataCompraFlujo = $("#data-info").val();
        if (dataCompraFlujo !== "") {
            await $.ajax({
                url: "/modulos/flujo/api/siguiente",
                method: "GET",
                data: {
                    dataFlujo: dataCompraFlujo,
                },
                success: function ({ status, data }) {
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
            await $.ajax({
                url: "/modulos/flujo/api/anterior",
                method: "GET",
                data: {
                    dataFlujo: dataCompraFlujo,
                },
                success: function ({ status, data }) {
                    if (data !== null && data.length > 0) {
                        let bodyTable = $("#movimientosTr");
                        bodyTable.html("");
                        $("#data-info").val(JSON.stringify(data[0]));
                        generarTablaFlujo(bodyTable, data, data[0]);
                    }
                },
            });
        }
    });

    //Aqui comienza el flujo
    let primerFlujo = $("#movimientoFlujo").val();

    if (primerFlujo !== "") {
        let bodyTable = $("#movimientosTr");

        let jsonFlujo = JSON.parse(primerFlujo);
        generarTablaFlujo(bodyTable, jsonFlujo);
    }

    function generarTablaFlujo(bodyTable, jsonFlujo, origen) {
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
            $("#next").hide();
            $("#crearMov").hide();
        }
    }

    if ($("#status").text().trim() === "FINALIZADO") {
        $("#timbrado").show();
    } else {
        $("#timbrado").hide();
    }
    $("#timbrado").click((e) => {
        e.preventDefault();
        $("#facturaModal").modal({
            backdrop: "static",
            keyboard: true,
            show: true,
        });

        $("#facturaInfo-aceptar").hide();
        $("#facturaInfo-aceptar2").show();
    });

    function requestTimbrado() {
        $("#loader").show();
        $.ajax({
            url: "/afectarTimbrado",
            method: "get",
            data: {
                _token: '{{ csrf_token() }}',
                venta: $("#idCompra").val(),
                dataFacturaInfo: $("#dataFacturaInfo").val(),
            },
            success: function ({ status, data }) {
                if (status) {
                    showMessage(data, "success");

                    let ruta = window.location.href;

                    setTimeout(function () {
                        window.location.href = ruta;
                    }, 1000);

                    $("#loader").hide();
                } else {
                    showMessage(data, "error");

                    $("#loader").hide();
                }
            },
        });
        $("#facturaModal").modal("hide");
    }

    //Si el movimiento es de tipo factura o pedido mostramos las columnas  Empaque y cantidad Neta
    if (movimiento === "Factura" || movimiento === "Pedido") {
        $(".cantidadNeta").show();

        if (movimiento === "Factura") {
            $(".tablaDesborde").css("width", "2000");
        } else {
            $(".tablaDesborde").css("width", "2100");
        }
    }

    if (
        $("#status").text().trim() === "FINALIZADO" ||
        $("#status").text().trim() === "CANCELADO" ||
        $("#status").text().trim() === "POR AUTORIZAR"
    ) {
        $("#crearMov").hide();
    }

    $("#GuardarComercioExterior").click((e) => {
        //Guardamos la información para el proceso de comercio exterior
        e.preventDefault();
        let inputComercioExterior = $("#inputJsonComercioExterior");

        let comercioExterior = {
            IncotermKey: $("#incoTermKey").val(),
            subdivision: $("#subdivision").prop("checked"),
            origen: $("#origen").prop("checked"),
            mTraslado: $("#motivoTrasladoKey").val(),
            tOperacion: $("#select-tipoOperacion").val(),
            cPedimento: $("#select-clavePedimento").val(),
            cOrigen: $("#numCertificadoOrigen").val(),
            eConfiable: $("#numExportadorConfiable").val(),
        };

        inputComercioExterior.attr("value", JSON.stringify(comercioExterior));
        $("#progressWizard").submit();
    });

    $("#GuardarCobroFactura").click((e) => {
        //Guardamos la información para el proceso de comercio exterior
        e.preventDefault();
        let inputSaveCobro = $("#inputJsonCobroFactura");

        let jsonCobroFactura = {
            importe1: $("#cantidad1")
                .val()
                .replace(/['$', ',']/g, ""),
            importe2: $("#cantidad2")
                .val()
                .replace(/['$', ',']/g, ""),
            importe3: $("#cantidad3")
                .val()
                .replace(/['$', ',']/g, ""),
            formaCobro1: $("#metodoPago1").val(),
            formaCobro2: $("#metodoPago2").val(),
            formaCobro3: $("#metodoPago3").val(),
            formaCambio7: $("#metodoPago7").val(),
            cuentaPago: $("#cuentaPago").val(),
            accountType: $("#tipoCuenta").val(),
            infAdicional: $("#infAdicional").val(),
            totalFactura: $("#totalC")
                .val()
                .replace(/['$', ',']/g, ""),
            cambio: $("#cambioTotal").val().replace(/[$,-]/g, ""),
        };

        inputSaveCobro.attr("value", JSON.stringify(jsonCobroFactura));

        $("#progressWizard").submit();
    });
}); //Fin de document ready

//Metemos la data del flujo al input

function disabledCompra() {
    let status = $("#status").text().trim();
    let movimiento = $("#select-movimiento").val();
    if (status !== "INICIAL") {
        $("#articleItem").find("input[type='number']").attr("readonly", true);
        $("input[id^='canti-']").attr("readonly", true);
        $("input[id^='porDesc-']").attr("readonly", true);
        $("#articleItem").find("select").attr("disabled", true);
        $("input[id^='c_unitario-']").attr("readonly", true);
        $("input[id^='cantidadNeta-']").attr("readonly", true);
        $("#articleItem").find(".btn-info").attr("disabled", true);
        $(".eliminacion-articulo").hide();
        $("#select-movimiento").attr("readonly", true);
        $("#fechaEmision").attr("readonly", true);
        $("#select-moneda").attr("readonly", true);
        $("#proveedorFechaVencimiento").attr("readonly", true);
        $("#nameTipoCambio").attr("readonly", true);
        $("#select-moduleConcept").attr("readonly", true);
        // $("#proveedorReferencia").attr("readonly", true);
        $("#proveedorKey").attr("readonly", true);
        $("#cantidadKit").attr("readonly", true);
        $("#provedorModal").attr("disabled", true);
        $("#select-proveedorCondicionPago").attr("readonly", true);
        $("#select-moduleCancellation").attr("readonly", true);
        $("#proveedorReferencia").attr("readonly", true);
        $("#almacenKey").attr("readonly", true);
        $("#select-clienteCFDI").attr("readonly", true);
        $("#almacenModal").attr("disabled", true);
        $("#sellerModal").attr("disabled", true);
        $("#folioTicket").attr("readonly", true);
        $("#operador").attr("readonly", true);
        $("#placas").attr("readonly", true);
        $("#material").attr("readonly", true);
        $("#pesoEntrada").attr("readonly", true);
        $("#fechaEntradaDatos").attr("readonly", true);
        $("#pesoSalida").attr("readonly", true);
        $("#fechaSalida").attr("readonly", true);
        $("#select-precioListaSelect").attr("readonly", true);
        $("#select-vehiculoName").attr("readonly", true);
        $("#select-choferName").attr("readonly", true);
        $("#placas").attr("readonly", true);
        $("#lugarEntrega").attr("readonly", true);
        $("#numeroBooking").attr("readonly", true);
        $("#sello").attr("readonly", true);
        $("#fechaSalida2").attr("readonly", true);
        $("#buqueName").attr("readonly", true);
        $("#destinoFinal").attr("readonly", true);
        $("#numeroContrato").attr("readonly", true);
        $("#tipoContenedor").attr("readonly", true);
        $("#botonesWizard").hide();
        $('input[id^="keyArticulo-"]').attr("readonly", true);
        $('input[id^="keyArticulo-"]').removeAttr("onchange");

        $("#incoTermModal").attr("disabled", true);
        $("#motivoTrasladoModal").attr("disabled", true);
        $("#subdivision").attr("disabled", true);
        $("#origen").attr("disabled", true);

        $("#select-tipoOperacion").attr("readonly", true);
        $("#select-clavePedimento").attr("readonly", true);

        $("#numCertificadoOrigen").attr("readonly", true);
        $("#numExportadorConfiable").attr("readonly", true);
        $('input[id^="observacion-"]').attr("readonly", true);

        if (
            movimiento === "Entrada por Compra" ||
            movimiento === "Rechazo de Compra"
        ) {
            $(".accion-pendiente").hide();
            $(".accion-recibir").hide();
        }

        $("#modal7Agregar").hide();
        $("#cerrar-kitModal").show();

        if (movimiento === "Cotización") {
            $("#generarKits").hide();
        }

        $("#generarEmpaques").hide();
        $("#quitarSeries2").hide();
        $("#quitarSeries22").hide();

        //quitarlos eventos de los botones
        $("input[id^='canti-']").removeAttr("onchange");
        $("input[id^='canti-']").removeAttr("onfocus");

        // $("#tablaEmpaques").find("td").find("input").attr("readonly", true);
    } else {
        $(".accion-pendiente").hide();
        $(".accion-recibir").hide();
    }

    if ($(".aplicaA").is(":visible") && $(".aplicaIncre").is(":visible")) {
        $('input[id^="keyArticulo-"]').attr("readonly", true);
        $('input[id^="keyArticulo-"]').removeAttr("onchange");
    }
}

//Evento para calcular la cantidad por el factor de la unidad (usamos la clave y la unidad de compra asignada)
function changeCantidadInventario(clave, posicion) {
    let decimal = $("#decimales-" + clave + "-" + posicion).val();
    // console.log(decimal);
    if (decimal !== "") {
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

//hacemos que el calcular importe se ejecute al momento de agregar un articulo ya que por default tendra cantidad 1
//solo quiero hacer
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

    if (inputCantidad !== "") {
        let cantidadNeta = $("#cantidadNeta-" + clave + "-" + posicion);

        // if (
        //     $("#select-movimiento").val() === "Pedido" &&
        //     cantidadNeta.val() !== "" &&
        //     cantidadNeta.val() != inputCantidad
        // ) {
        //     inputCantidad =
        //         cantidadNeta.val() !== ""
        //             ? parseFloat(cantidadNeta.val().replace(/[$,]/g, ""))
        //             : "";
        // }

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

            let cantidadInventarioFormat = currency(cantidad, {
                separator: ",",
                precision: parseInt(decimal),
                symbol: "",
            }).format();

            $("#canti-" + clave + "-" + posicion).val(cantidadFormat);

            cantidadInventario.val(cantidadInventarioFormat);

            if (
                inputCantidad === cantidadNeta.val() ||
                cantidadNeta.val() === ""
            ) {
                cantidadNeta.val(cantidadInventarioFormat);
            }
        } else {
            cantidadInventario.val("");
            cantidadNeta.val("");
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
//     let inputCantidadNeta = $("#cantidadNeta-" + clave + "-" + posicion);

//     if(inputCantidadNeta.val() !== ""){
//         let inputFactor = $("#unid-" + clave + "-" + posicion).val();
//         let inputFactorArray2 = inputFactor.trim().split("-");
//         let cantidadInventario = $("#c_Inventario-" + clave + "-" + posicion);

//         if (inputCantidadNeta.val() !== "" && inputCantidadNeta.val() > 0) {
//             let cantidad =
//                 parseFloat(inputCantidadNeta.val()) * parseFloat(inputFactorArray2[1]);
//             let numerosDecimales = cantidad.toString();
//             numerosDecimales = numerosDecimales.substring(
//                 numerosDecimales.indexOf(".") + 1,
//                 numerosDecimales.length - 1
//             );
//             let longitudNumerosDecimales = numerosDecimales.length;

//             let cantidadCerosPorDecimales = 10 ** longitudNumerosDecimales;

//             if (cantidadCerosPorDecimales > 10) {
//                 cantidadInventario.val(
//                     Math.round(cantidadCerosPorDecimales * cantidad) /
//                         cantidadCerosPorDecimales
//                 );
//             } else {
//                 cantidadInventario.val(cantidad);
//             }
//         } else {
//             cantidadInventario.val("");
//         }
//     }

// }

//Operacion para hallar el importe del artuculo
function changeCostoImporte(clave, posicion) {
    let inputCostoText = $("#c_unitario-" + clave + "-" + posicion)
        .val()
        .replace(/['$', ',']/g, "");

    let inputCosto = parseFloat(
        $("#c_unitario-" + clave + "-" + posicion)
            .val()
            .replace(/['$', ',']/g, "")
    );

    let inputCantidad = parseFloat(
        $("#canti-" + clave + "-" + posicion)
            .val()
            .replace(/[$,]/g, "")
    );
    console.log(inputCostoText, inputCosto, inputCantidad);

    // console.log(inputCostoText, inputCosto, inputCantidad);
    let inputImporte = $("#importe-" + clave + "-" + posicion);

    if (
        inputCosto !== "" &&
        inputCosto > 0 &&
        inputCantidad !== "" &&
        inputCantidad > 0
    ) {
        let importe = inputCosto * inputCantidad;
        let importeFormato = truncarDecimales(formatoMexico(importe), 4);
        let inputCostoFormato = formatoMexico(inputCostoText);

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
    let importe = parseFloat(inputImporte.val().replace(/,/g, ""));
    let claveMonedaSat = $("#clave_sat_moneda").val().trim() !== "MXN";
    let rfc = $("#rfc-cliente").val().trim().toUpperCase();
    let isRfcComercioExterior = rfc.slice(0, 2) == "XE";
    let descuentoLineal = parseFloat(
        $("#descuento-" + clave + "-" + posicion)
            .val()
            .replace(/,/g, "")
    );

    let importeActual = 0;

    let iva = parseFloat(
        $("#iva-" + clave + "-" + posicion)
            .val()
            .replace(/[$,]/g, "")
    );

    let porcentajeiVA;
    let importeIva;

    if (claveMonedaSat && isRfcComercioExterior) {
        importeIva = 0;
    } else {
        if (iva > 0) {
            porcentajeiVA = iva / 100;
            if (!isNaN(descuentoLineal)) {
                importeActual = importe - descuentoLineal;
            } else {
                importeActual = importe;
            }
            importeIva = importeActual * porcentajeiVA;
        } else {
            porcentajeiVA = 1;
            importeIva = 0;
        }
    }

    if (importe !== "" && importe > 0) {
        if (importe !== NaN) {
            let importeIvaFormato = truncarDecimales(round(importeIva), 4);

            $("#importe_iva-" + clave + "-" + posicion).val(
                formatoMexico(importeIvaFormato)
            );
        } else {
            $("#importe_iva-" + clave + "-" + posicion).val("");
        }
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
        let descuentoImporteFormato = truncarDecimales(descuentoImporte, 4);
        inputDescuentoImporte.val(formatoMexico(descuentoImporteFormato));
        importeIva(clave, posicion);
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
    let ret1Formato, ret2Formato, totalRetenciones, descuento;

    descuento = parseFloat(inputDescuento.val().replace(/[$,]/g, ""));

    if (isNaN(descuento)) {
        descuento = 0;
    }

    let importeIva = parseFloat(inputImporteIva.val().replace(/[$,]/g, ""));
    let importeTotal = importe + importeIva;

    let ret1 = $("#pRet1-" + clave + "-" + posicion)
        .val()
        .replace(/[$,]/g, "");
    let ret2 = $("#pRet2-" + clave + "-" + posicion)
        .val()
        .replace(/[$,]/g, "");

    if (importe !== "" && importe > 0) {
        if (ret1 !== "" && ret1 > 0) {
            let ret1Importe = (importe - descuento) * (ret1 / 100);
            // let prueba = round(ret1Importe);
            // console.log(prueba);
            ret1Formato = truncarDecimales(round(ret1Importe), 4);
            $("#pRetISR-" + clave + "-" + posicion).val(
                formatoMexico(ret1Formato)
            );
            // ret1Formato = ret1Formato.val().replace(/[$,]/g, "");
        } else {
            $("#pRetISR-" + clave + "-" + posicion).val("0.00");
            ret1Formato = 0;
        }

        if (ret2 !== "" && ret2 > 0) {
            let ret2Importe = (importe - descuento) * (ret2 / 100);
            // let prueba2 = round(ret2Importe);
            // console.log(prueba2);
            ret2Formato = truncarDecimales(round(ret2Importe), 4);
            $("#retIva-" + clave + "-" + posicion).val(
                formatoMexico(ret2Formato)
            );
            // ret2Formato = ret2Formato.val().replace(/[$,]/g, "");
        } else {
            $("#retIva-" + clave + "-" + posicion).val("0.00");
            ret2Formato = 0;
        }
    } else {
        $("#pRetISR-" + clave + "-" + posicion).val("0.00");
        $("#retIva-" + clave + "-" + posicion).val("0.00");
        ret1Formato = 0;
        ret2Formato = 0;
    }

    // console.log(ret1Formato, ret2Formato);

    totalRetenciones = parseFloat(ret1Formato) + parseFloat(ret2Formato);

    if (descuento > 0) {
        importeTotal = importeTotal - descuento;
    }

    let resultado = importeTotal - totalRetenciones;

    if (importe !== NaN && importe > 0 && importeIva !== NaN) {
        let resultadoFormato = truncarDecimales(round(resultado), 4);

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
    let retencionesISR = 0;
    let retencionesIVA = 0;
    let porcentajeISR = 0;
    let porcentajeIVA = 0;

    $('input[id^="importe-"]').each(function (index, value) {
        let idIntputs = $(this).attr("id");
        let importes = $("#" + idIntputs)
            .val()
            .replace(/[$,]/g, "");

        if (importes !== "") {
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
        // console.log(totales, total);
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

    $('input[id^="pRetISR-"]').each(function (index, value) {
        let idIntputs = $(this).attr("id");
        let ISR = parseFloat(
            $("#" + idIntputs)
                .val()
                .replace(/[$,]/g, "")
        );
        if (ISR !== "" && ISR > 0) {
            retencionesISR += parseFloat(ISR);
        }
    });

    $('input[id^="retIva-"]').each(function (index, value) {
        let idIntputs = $(this).attr("id");
        let IVA = parseFloat(
            $("#" + idIntputs)
                .val()
                .replace(/[$,]/g, "")
        );
        if (IVA !== "" && IVA > 0) {
            retencionesIVA += parseFloat(IVA);
        }
    });

    $('input[id^="pRet2-"]').each(function (index, value) {
        let idIntputs = $(this).attr("id");
        let pIVA = parseFloat(
            $("#" + idIntputs)
                .val()
                .replace(/[$,]/g, "")
        );
        if (pIVA !== "" && pIVA > 0) {
            porcentajeIVA = pIVA;
            return false;
        }
    });

    $('input[id^="pRet1-"]').each(function (index, value) {
        let idIntputs = $(this).attr("id");
        let pISR = parseFloat(
            $("#" + idIntputs)
                .val()
                .replace(/[$,]/g, "")
        );
        if (pISR !== "" && pISR > 0) {
            porcentajeISR = pISR;
            return false;
        }
    });

    // console.log(subTotal, iva, total);
    let subTotalCompleto = truncarDecimales(round(subTotal), 4);
    let impuestosCompleto = truncarDecimales(round(iva), 4);
    let totalCompleto = truncarDecimales(round(total), 4);
    let totalDescuento = truncarDecimales(round(descuento), 4);
    let totalISR = truncarDecimales(round(retencionesISR), 4);
    let totalIVA = truncarDecimales(round(retencionesIVA), 4);
    let retencionesTotales = truncarDecimales(
        round(parseFloat(totalIVA) + parseFloat(totalISR)),
        4
    );

    $("#subTotalCompleto").val("$" + formatoMexico(subTotalCompleto));
    $("#impuestosCompleto").val("$" + formatoMexico(impuestosCompleto));
    $("#totalCompleto").val("$" + formatoMexico(totalCompleto));
    $("#totalDescuento").val("$" + formatoMexico(totalDescuento));
    $("#retencionISR").val("$" + totalISR);
    $("#retencionIVA").val("$" + totalIVA);
    $("#porcentajeISR").val(porcentajeISR);
    $("#porcentajeIVA").val(porcentajeIVA);
    $("#retencionesCompleto").val("$" + formatoMexico(retencionesTotales));
}

function validateCondicionPago() {
    let estadoConcepto =
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

function validatePrecioLista() {
    const estadoConcepto =
        $("#select-precioListaSelect").val() === "" ? true : false;

    if (estadoConcepto) {
        return true;
    } else {
        return false;
    }
}

function validateMovimiento() {
    const estadoConcepto = $("#select-movimiento").val() === "" ? true : false;

    if (estadoConcepto) {
        return true;
    } else {
        return false;
    }
}

function validateVendedor() {
    const estadoConcepto = $("#sellerKey").val() === "" ? true : false;

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

function validateChofer() {
    let estado = false;
    if ($("#select-movimiento").val() != "Cotización") {
        let chofer = $("#select-choferName").val();
        if (chofer === "" || chofer === null) {
            estado = true;
        }
    }
    return estado;
}

function validateVehiculo() {
    let estado = false;
    if ($("#select-movimiento").val() != "Cotización") {
        let vehiculo = $("#select-vehiculoName").val();
        if (vehiculo === "" || vehiculo === null) {
            estado = true;
        }
    }
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
        $(".retencion").hide();
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
            let observacion = $(
                "#observacion-" + key + "-" + identificador[2]
            ).val();
            let cantidad = $("#canti-" + key + "-" + identificador[2]).val();
            let unidad = $("#unid-" + key + "-" + identificador[2]).val();
            let unidadEmpaque = $(
                "#unidadEmpaque-" + key + "-" + identificador[2]
            ).val();
            let c_unitario = $("#c_unitario-" + key + "-" + identificador[2])
                .val()
                .replace(/,/g, "");
            let importe = $("#importe-" + key + "-" + identificador[2])
                .val()
                .replace(/,/g, "");
            let iva = $("#iva-" + key + "-" + identificador[2]).val();
            let importe_iva = $("#importe_iva-" + key + "-" + identificador[2])
                .val()
                .replace(/,/g, "");
            let importe_total = $(
                "#importe_total-" + key + "-" + identificador[2]
            )
                .val()
                .replace(/,/g, "");
            let pendiente = $(
                "#pendiente-" + key + "-" + identificador[2]
            ).val();
            let recibir = $("#recibir-" + key + "-" + identificador[2]).val();
            let c_Inventario = $(
                "#c_Inventario-" + key + "-" + identificador[2]
            ).val();
            let c_Neta = $(
                "#cantidadNeta-" + key + "-" + identificador[2]
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

            let porcentaje_retencion = $(
                "#pRet1-" + key + "-" + identificador[2]
            ).val();
            let retencion = $("#pRetISR-" + key + "-" + identificador[2]).val();

            let porcentaje_retencion2 = $(
                "#pRet2-" + key + "-" + identificador[2]
            ).val();
            let retencion2 = $("#retIva-" + key + "-" + identificador[2]).val();

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
                        article: key,
                        cantidad: cantidad,
                        unidad: unidad,
                        unidadEmpaque: unidadEmpaque,
                        c_unitario: c_unitario,
                        importe: importe,
                        iva: iva,
                        importe_iva: importe_iva,
                        porcentajeDescuento: porcentajeDescuento,
                        descuento: descuento,
                        porcentaje_retencion: porcentaje_retencion,
                        retencion: retencion,
                        porcentaje_retencion2: porcentaje_retencion2,
                        retencion2: retencion2,
                        importe_total: importe_total,
                        pendiente: pendiente,
                        recibir: recibir,
                        c_Neta: c_Neta,
                        c_Inventario: c_Inventario,
                        desp: desp,
                        observacion: observacion,
                        referenceArticle: referenceArticle,
                        tipoArticulo: tipoArticulo,
                        aplicaIncre: aplicaIncre,
                        ventaKit: articulosKits[key + "-" + identificador[2]],
                        listaEmpaques:
                            articulosEmpaque[key + "-" + identificador[2]],
                        venderSerieKits: articulosSerieTrans2,
                    },
                };
            } else {
                articulosLista = {
                    ...articulosLista,
                    [key + "-" + identificador[2]]: {
                        id: id,
                        article: key,
                        cantidad: cantidad,
                        unidad: unidad,
                        unidadEmpaque: unidadEmpaque,
                        c_unitario: c_unitario,
                        importe: importe,
                        iva: iva,
                        importe_iva: importe_iva,
                        porcentajeDescuento: porcentajeDescuento,
                        descuento: descuento,
                        porcentaje_retencion: porcentaje_retencion,
                        retencion: retencion,
                        porcentaje_retencion2: porcentaje_retencion2,
                        retencion2: retencion2,
                        importe_total: importe_total,
                        pendiente: pendiente,
                        recibir: recibir,
                        c_Neta: c_Neta,
                        c_Inventario: c_Inventario,
                        desp: desp,
                        observacion: observacion,
                        referenceArticle: referenceArticle,
                        tipoArticulo: tipoArticulo,
                        aplicaIncre: aplicaIncre,
                        ventaKit: articulosKits[key + "-" + identificador[2]],
                        listaEmpaques:
                            articulosEmpaque[key + "-" + identificador[2]],
                        venderSerie:
                            articulosSerieTrans[key + "-" + identificador[2]]
                                ?.serie,
                        venderIdsSerie:
                            articulosSerieTrans[key + "-" + identificador[2]]
                                ?.ids,
                        venderSerieKits: articulosSerieTrans2,
                    },
                };
            }
        }
    });

    // console.log(articulosLista);
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

function showMessage(mensaje, icon) {
    swal(mensaje, {
        button: "OK",
        timer: 3000,
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

async function buscadorArticulos(filaReferencia) {
    let tipoMov = $("#select-movimiento").val();
    let estatus = jQuery("#status").text().trim();
    let value = jQuery("#" + filaReferencia).val();

    //Eliminamos las filas que no tengan una key
    let claveMonedaSat = $("#clave_sat_moneda").val().trim() !== "MXN";
    let rfc = $("#rfc-cliente").val().trim().toUpperCase();
    let isRfcComercioExterior = rfc.slice(0, 2) == "XE";

    const isInvalid = validateProveedorArticulo();
    const isInvalid2 = validateAlmacenArticulo();
    const isInvalid3 = $("#select-precioListaSelect").val().trim();

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

    if (isInvalid3 === "") {
        showMessage("No se ha seleccionado el precio de lista", "error");
    }

    if (isInvalid || isInvalid2 || isInvalid3 === "") {
        $("#" + filaReferencia).val("");
        return false;
    }

    await $.ajax({
        url: "/buscador/articulos/venta",
        method: "GET",
        data: {
            clave: value,
            list: $("#select-precioListaSelect").val().trim(),
        },
        success: function ({ status, data }) {
            console.log("data", data);
            const isRetencionesVisible = $(".retencion").is(":visible");
            if (status) {
                let retencion = false;
                let retencion1 = data.articles_retention1;
                let retencion2 = data.articles_retention2;

                let {
                    articles_key: articleKey,
                    articles_type: tipo,
                    articles_descript: articleName,
                    articles_porcentIva: articleIva,
                    precio: precioArt,
                    unidad: articleUnidad,
                    tipo: articles_type,
                } = data;

                if (
                    data.articles_retention1 !== null ||
                    data.articles_retention2 !== null
                ) {
                    retencion = true;
                }

                // console.log("retencion1", retencion1, "retencion2", retencion2, calculoImpuestos);

                if (claveMonedaSat && isRfcComercioExterior) {
                    articleIva = "";
                    retencion1 = "";
                    retencion2 = "";
                } else {
                    if (calculoImpuestos === "0") {
                        articleIva = articleIva;
                        retencion1 = retencion1;
                        retencion2 = retencion2;
                    } else {
                        articleIva = "";
                        retencion1 = "";
                        retencion2 = "";
                    }
                }

                if (isRetencionesVisible) {
                    retencion = true;
                }

                let factor = parseFloat($("#nameTipoCambio").val());
                let precio = parseFloat(precioArt.replace(/['$', ',']/g, ""));
                let precioDecimal = precio / factor;

                let precioFinal = truncarDecimales(
                    formatoMexico(precioDecimal),
                    4
                );

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
                                   (tipo === "Serie" && tipoMov == "Factura") ||
                                   (tipoMov == "Pedido" &&
                                       tipo === "Serie" &&
                                       estatus == "INICIAL")
                                       ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal4" id="modalSerie2">S</button>'
                                       : ""
                               }

                               ${
                                   tipo == "Kit" && estatus == "INICIAL"
                                       ? '<button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target=".modal7" id="modalKits"><span class="glyphicon glyphicon-tags"></span></button>'
                                       : ""
                               }
                               </td>
                               <td><input name="dataArticulos[]" id="desp-${articleKey}-${result}" type="text" class="botonesArticulos" value='${articleName}' readonly title="${articleName}"></td>
                               <td>
                                       <input name="dataArticulos[]" id="canti-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value='1'>
                               </td>
                               <td><input name="dataArticulos[]" id="c_unitario-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='${precioFinal}' onchange="calcularImporte('${articleKey}', '${result}')" ${
                        precioVariable === "1" ? "readonly" : ""
                    }></td>
                               <td>
                                   <select name="dataArticulos[]" id="unid-${articleKey}-${result}" class="botonesArticulos" value='${articleUnidad}' onchange="recalcularCantidadInventario('${articleKey}', '${result}')" >
                                   </select>
                               </td>
                               <td  style="display:none" class="unidadEmpaque">
                               <select name="dataArticulos[]" id="unidadEmpaque-${articleKey}-${result}" class="botonesArticulos" value='${articleUnidad}'>
                               </select>
                               </td>
                               <td><input name="dataArticulos[]" id="c_Inventario-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                               <td><input name="dataArticulos[]" id="importe-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                               <td><input name="dataArticulos[]" id="porDesc-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="descuentoLineal('${articleKey}', '${result}')" value=''></td>
               <td><input name="dataArticulos[]" id="descuento-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                               <td><input name="dataArticulos[]" id="iva-${articleKey}-${result}" type="number" class="botonesArticulos sinBotones" value='${articleIva}' readonly></td>
                               <td><input name="dataArticulos[]" id="importe_iva-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                               <td ${
                                   retencion
                                       ? 'style="display: "'
                                       : 'style="display: none"'
                               } class="retencion2"><input id="pRet1-${articleKey}-${result}"
                                 type="text" class="botonesArticulos" readonly value='${
                                     retencion1 == "NaN" || retencion1 == null
                                         ? ""
                                         : retencion1
                                 }' name="dataArticulos[]"></td>
                         <td ${
                             retencion
                                 ? 'style="display: "'
                                 : 'style="display: none"'
                         } class="retencion2"><input id="pRetISR-${articleKey}-${result}"
                                 type="text" class="botonesArticulos" readonly value='0.00' name="dataArticulos[]"></td>
                         <td ${
                             retencion
                                 ? 'style="display: "'
                                 : 'style="display: none"'
                         } class="retencion2"><input id="pRet2-${articleKey}-${result}"
                                 type="text" class="botonesArticulos" readonly value='${
                                     retencion2 == "NaN" || retencion2 == null
                                         ? ""
                                         : retencion2
                                 }' name="dataArticulos[]"></td>
                         <td ${
                             retencion
                                 ? 'style="display: "'
                                 : 'style="display: none"'
                         } class="retencion2"><input id="retIva-${articleKey}-${result}" type="text" class="botonesArticulos" readonly value='0.00' name="dataArticulos[]">
                         </td>
                               <td><input name="dataArticulos[]" id="importe_total-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                               <td><input name="dataArticulos[]" id="observacion-${articleKey}-${result}" type="text" class="botonesArticulos"  title="${articleName}"></td>
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
                    changeCostoImporte(articleKey, result);
                    importeIva(articleKey, result);
                    importeTotal(articleKey, result);
                    siguienteCampo(articleKey, result);
                } else {
                    $("#articleItem").append(`
                                <tr id="${articleKey}-${result}">
                                <td id="btnInput"><input name="dataArticulos[]" id="keyArticulo-${articleKey}-${result}" type="text" class="keyArticulo" value='${articleKey}' onchange="buscadorArticulos('keyArticulo-${articleKey}-${result}')" />
                
                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target=".modal3">...</button>
                                ${
                                    (tipo === "Serie" &&
                                        tipoMov == "Factura") ||
                                    (tipoMov == "Pedido" &&
                                        tipo === "Serie" &&
                                        estatus == "INICIAL")
                                        ? '<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target=".modal4" id="modalSerie2">S</button>'
                                        : ""
                                }
                                ${
                                    tipo == "Kit" && estatus == "INICIAL"
                                        ? '<button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target=".modal7" id="modalKits"><span class="glyphicon glyphicon-tags"></span></button>'
                                        : ""
                                }
                                </td>
                                <td><input name="dataArticulos[]" id="desp-${articleKey}-${result}" type="text" class="botonesArticulos" value='${articleName}' readonly title="${articleName}"></td>
                                <td>
                                        <input name="dataArticulos[]" id="canti-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="changeCantidadInventario('${articleKey}', '${result}')"  value='1'>
                                </td>
                                <td><input name="dataArticulos[]" id="c_unitario-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='${precioFinal}' onchange="calcularImporte('${articleKey}', '${result}')" ${
                        precioVariable === "1" ? "readonly" : ""
                    } </td>
                                <td>
                                    <select name="dataArticulos[]" id="unid-${articleKey}-${result}" class="botonesArticulos" value='${articleUnidad}' onchange="recalcularCantidadInventario('${articleKey}', '${result}')" >
                                    </select>
                                </td>
                                <td style="display:none" class="unidadEmpaque">
                                <select name="dataArticulos[]" id="unidadEmpaque-${articleKey}-${result}" class="botonesArticulos" value='${articleUnidad}'>
                                </select>
                                </td>
                                <td><input name="dataArticulos[]" id="c_Inventario-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                                <td><input name="dataArticulos[]" id="importe-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                                <td><input name="dataArticulos[]" id="porDesc-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" onchange="descuentoLineal('${articleKey}', '${result}')" value=''></td>
               <td><input name="dataArticulos[]" id="descuento-${articleKey}-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                                <td><input name="dataArticulos[]" id="iva-${articleKey}-${result}" type="number" class="botonesArticulos sinBotones" value='${articleIva}' readonly></td>
                                <td><input name="dataArticulos[]" id="importe_iva-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                                <td ${
                                    retencion
                                        ? 'style="display: "'
                                        : 'style="display: none"'
                                } class="retencion2"><input id="pRet1-${articleKey}-${result}"
                                     type="text" class="botonesArticulos" readonly value='${
                                         retencion1 == "NaN" ||
                                         retencion1 == null
                                             ? ""
                                             : retencion1
                                     }' name="dataArticulos[]"></td>
                             <td ${
                                 retencion
                                     ? 'style="display: "'
                                     : 'style="display: none"'
                             } class="retencion2"><input id="pRetISR-${articleKey}-${result}"
                                     type="text" class="botonesArticulos" readonly value='0.00' name="dataArticulos[]"></td>
                             <td ${
                                 retencion
                                     ? 'style="display: "'
                                     : 'style="display: none"'
                             } class="retencion2"><input id="pRet2-${articleKey}-${result}"
                                     type="text" class="botonesArticulos" readonly value='${
                                         retencion2 == "NaN" ||
                                         retencion2 == null
                                             ? ""
                                             : retencion2
                                     }' name="dataArticulos[]"></td>
                             <td ${
                                 retencion
                                     ? 'style="display: "'
                                     : 'style="display: none"'
                             } class="retencion2"><input id="retIva-${articleKey}-${result}" type="text" class="botonesArticulos" readonly value='0.00' name="dataArticulos[]">
                             </td>
                                <td><input name="dataArticulos[]" id="importe_total-${articleKey}-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                                <td><input name="dataArticulos[]" id="observacion-${articleKey}-${result}" type="text" class="botonesArticulos" title="${articleName}"></td>
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
                    changeCostoImporte(articleKey, result);
                    importeIva(articleKey, result);
                    importeTotal(articleKey, result);
                    siguienteCampo(articleKey, result);
                }

                const selectOptions = $("#unid-" + articleKey + "-" + result); //Obtenemos el select para añadirle las multiunidades correspondientes

                const selectOptionsEmpaque = $(
                    "#unidadEmpaque-" + articleKey + "-" + result
                );

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

                $.ajax({
                    url: "/listaEmpaques",
                    type: "GET",
                    success: function ({ estatus, data }) {
                        data.forEach((element) => {
                            selectOptionsEmpaque.append(`
                            <option value="${
                                element.packaging_units_packaging +
                                "-" +
                                element.packaging_units_weight +
                                "-" +
                                element.packaging_units_unit
                            }">${
                                element.packaging_units_packaging +
                                "-" +
                                element.packaging_units_weight +
                                "-" +
                                element.packaging_units_unit
                            }</option>
                            `);
                        });
                    },
                });

                contadorArticulos++;
                $("#cantidadArticulos").val(contadorArticulos);

                if (retencion) {
                    $(".retencion").show();
                    $(".retencion2").show();
                }
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
                    $("#pRet1-" + articulo + "-" + posicion).val("");
                    $("#pRetISR-" + articulo + "-" + posicion).val("");
                    $("#pRet2-" + articulo + "-" + posicion).val("");
                    $("#retIva-" + articulo + "-" + posicion).val("");
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
        let estatus = $("#status").text().trim();

        if (estatus !== "INICIAL") {
            return false;
        }

        if (tipoMov === "Rechazo de Venta") {
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
                           <input name="dataArticulos[]" id="canti-${result}" type="text" class="botonesArticulos sinBotones" value='1'>
                   </td>
                   <td><input name="dataArticulos[]" id="c_unitario-${result}" type="any" class="botonesArticulos sinBotones" value=''></td>
                   <td>
                       <select name="dataArticulos[]" id="unid-${result}" class="botonesArticulos" value='' >
                       </select>
                   </td>
                   <td style="display:none" class="unidadEmpaque">
                   <select name="dataArticulos[]" id="unidadEmpaque-${result}" class="botonesArticulos" value=''>
                   </select>
                   </td>
                   <td><input name="dataArticulos[]" id="c_Inventario-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                   <td><input name="dataArticulos[]" id="importe-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                   <td><input name="dataArticulos[]" id="porDesc-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                   <td><input name="dataArticulos[]" id="descuento-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                   <td><input name="dataArticulos[]" id="iva-${result}" type="number" class="botonesArticulos sinBotones" value='' readonly></td>
                   <td><input name="dataArticulos[]" id="importe_iva-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                   <td class="retencion2"><input name="dataArticulos[]" id="pRet1-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                   <td class="retencion2"><input name="dataArticulos[]" id="pRetISR-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                   <td class="retencion2"><input name="dataArticulos[]" id="pRet2-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                   <td class="retencion2"><input name="dataArticulos[]" id="retIva-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                   <td><input name="dataArticulos[]" id="importe_total-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                   <td><input name="dataArticulos[]" id="observacion-${result}" type="text" class="botonesArticulos" value=''  title=""></td>
                   <td
                           style="display: flex; justify-content: center; align-items: center" class='eliminacion-articulo' onclick="eliminarArticulo('ninguno', '${result}')">
                           <i class="fa fa-trash-o"  aria-hidden="true"
                               style="color: red; font-size: 25px; cursor: pointer;"></i>
                   </td>
                   <td style="display: none">
                       <input name="dataArticulos[]" id="tipoArticulo-${result}" type="hidden" class="botonesArticulos sinBotones" value=''>
                   </td>
                     <td style="display: none">
                        <input id="decimales-${result}" type="text" value="" readonly>
                    </td>
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
                            <input name="dataArticulos[]" id="canti-${result}" type="text" class="botonesArticulos sinBotones" value='1'>
                    </td>
                    <td><input name="dataArticulos[]" id="c_unitario-${result}" type="any" class="botonesArticulos sinBotones" value='' ></td>
                    <td>
                        <select name="dataArticulos[]" id="unid-${result}" class="botonesArticulos" value=''>
                        </select>
                    </td>
                    <td style="display:none" class="unidadEmpaque">
                    <select name="dataArticulos[]" id="unidadEmpaque-${result}" class="botonesArticulos" value=''>
                    </select>
                    </td>
                    <td><input name="dataArticulos[]" id="c_Inventario-${result}" type="text" class="botonesArticulos sinBotones" value='' readonly></td>
                    <td><input name="dataArticulos[]" id="importe-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                    <td><input name="dataArticulos[]" id="porDesc-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                    <td><input name="dataArticulos[]" id="descuento-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                    <td><input name="dataArticulos[]" id="iva-${result}" type="number" class="botonesArticulos sinBotones" value='' readonly></td>
                    <td><input name="dataArticulos[]" id="importe_iva-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                    <td class="retencion2"><input name="dataArticulos[]" id="pRet1-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                    <td class="retencion2"><input name="dataArticulos[]" id="pRetISR-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                    <td class="retencion2"><input name="dataArticulos[]" id="pRet2-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                    <td class="retencion2"><input name="dataArticulos[]" id="retIva-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                    <td><input name="dataArticulos[]" id="importe_total-${result}" type="any" class="botonesArticulos sinBotones" value='0.00' readonly></td>
                    <td><input name="dataArticulos[]" id="observacion-${result}" type="text" class="botonesArticulos" value=''  title=""></td>
                    <td
                            style="display: flex; justify-content: center; align-items: center" class='eliminacion-articulo' onclick="eliminarArticulo('ninguno', '${result}')">
                            <i class="fa fa-trash-o" aria-hidden="true"
                                style="color: red; font-size: 25px; cursor: pointer;"></i>
                    </td>
                        <td style="display: none">
                               <input name="dataArticulos[]" id="tipoArticulo-${result}" type="hidden" class="botonesArticulos sinBotones"  value=''>
                        </td>
                         <td style="display: none">
                            <input id="decimales-${result}" type="text" value="" readonly>
                        </td>
                    </tr>
            `);
        }

        result++;
        contadorArticulos++;
        $("#cantidadArticulos").val(contadorArticulos);
    }
});

let completo = false;

function cambioUnidad(identificador) {
    let claveArt = $(".serieEmpaqueKey").text().trim();
    let unidad = $("#" + claveArt + "-unidad-" + identificador).val();
    let peso = unidad.split("-")[1];
    let pesoFloat = parseFloat(peso);
    $("#pesoUnidad-" + identificador).val(pesoFloat);

    cambioCantidad(identificador);
}

function cambioCantidad(identificador) {
    let claveArt = $(".serieEmpaqueKey").text().trim();

    let pesoIndicado = $("#" + claveArt + "-peso-" + identificador).val();

    if (pesoIndicado != 0) {
        let pesoUnidad = $("#pesoUnidad-" + identificador).val();
        let result = pesoIndicado - pesoUnidad;

        $("#pesoNeto-" + identificador).val(result);

        calcularTotalesEmpaque();
    }
}

function cambioPeso(identificador) {
    let claveArt = $(".serieEmpaqueKey").text().trim();
    let pesoFloat = parseFloat(
        jQuery("#" + claveArt + "-peso-" + identificador)
            .val()
            .replace(/[$,]/g, "")
    );
    let pesoUnidad = parseFloat(
        jQuery("#pesoUnidad-" + identificador)
            .val()
            .replace(/[$,]/g, "")
    );

    let cantidad = parseFloat(
        $(".serieEmpaqueCantidad").text().replace(/[$,]/g, "")
    );

    let pesoBrutoTotal = 0;
    $("input[id^=" + claveArt + "-peso-]").each(function (index, value) {
        let peso = $(value).val().replace(/[$,]/g, "");
        pesoBrutoTotal += parseFloat(peso);
    });

    if (pesoFloat <= cantidad) {
        if (pesoBrutoTotal > cantidad) {
            showMessage(
                "El peso bruto no puede ser mayor a la cantidad del articulo",
                "error"
            );
            $("#" + claveArt + "-peso-" + identificador).val("");
        } else {
            let result = pesoFloat - pesoUnidad;
            $("#pesoNeto-" + identificador).val(result);
            calcularTotalesEmpaque();
        }
    } else {
        showMessage(
            "El peso bruto no puede ser mayor a la cantidad del articulo",
            "error"
        );
        $("#" + claveArt + "-peso-" + identificador).val("");
    }
}

function calcularTotalesEmpaque() {
    let pesoBruto = 0;
    let unidadesPeso = 0;
    let pesoNeto = 0;
    let claveArt = $(".serieEmpaqueKey").text().trim();

    let cantidad = parseFloat($(".serieEmpaqueCantidad").text());
    $("input[id^=" + claveArt + "-peso-]").each(function (index, value) {
        let peso = $(value).val().replace(/[$,]/g, "");
        pesoBruto += parseFloat(peso);
    });

    $('input[id^="pesoUnidad-"]').each(function (index, value) {
        let peso = $(value).val().replace(/[$,]/g, "");
        unidadesPeso += parseFloat(peso);
    });

    $('input[id^="pesoNeto-"]').each(function (index, value) {
        let peso = $(value).val().replace(/[$,]/g, "");
        pesoNeto += parseFloat(peso);
    });

    // //actualizar el progress bar
    let progessBar = jQuery("#progressBarEmpaque");
    let porcentajeBar = jQuery("#porcentajeBar");

    let porcentaje = (pesoBruto * 100) / cantidad;
    porcentajeBar.text(porcentaje.toFixed(2) + "%");
    progessBar.css("width", porcentaje.toFixed(2) + "%");
    progessBar.attr("aria-valuenow", porcentaje.toFixed(2));
    let avance = $("input[id^=avance-]").each(function (index, value) {
        this.value = porcentaje.toFixed(2);
    });

    if (porcentaje >= 100) {
        progessBar.removeClass("progress-bar-info");
        progessBar.addClass("progress-bar-success");
    } else {
        progessBar.removeClass("progress-bar-success");
        progessBar.addClass("progress-bar-info");
    }

    $("#totalBrutoEmpaque").html(pesoBruto);
    $("#totalUnidadEmpaque").html(unidadesPeso);
    $("#totalNetoEmpaque").html(pesoNeto);
}

const genero = obtenerGeneroDelMovimiento();
let articulo = "el";
if (genero === "F") {
    articulo = "la";
}

function obtenerGeneroDelMovimiento() {
    const movimiento = $("#select-movimiento").val();

    //si el movimiento es diferente de aplicación el genero es F
    if (movimiento !== "Cotización" || movimiento !== "Factura") {
        return "M";
    } else {
        return "F";
    }
}

function afectarRequest(url) {
    let movimiento = jQuery("#select-movimiento").val();


    swal({
        title: "¿Está seguro que desea generar " + articulo + " " + movimiento + "?",
        text: "¡Con el nuevo estatus diferente a INICIAL no podrá realizar cambios!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
        buttons: ["Cancelar", "Aceptar"],
    }).then((willDelete) => {
        if (willDelete) {
            let movVenta = $("#select-movimiento").val().trim();
            let listaArticulos = jsonArticulos();
            let isSerieVacio = false;
            let isSerieRepetido = false;
            let isSerieVacio2 = false;
            let isSerieVacio3 = false;
            let claveArticuloSerie = "";
            let claveArticuloKit = "";
            let repetidos = {};

            //Mostramos el loader
            $("#loader").show();

            const keyListaArticulos = Object.keys(listaArticulos);
            // console.log(listaArticulos);

            if (movVenta == "Factura" || movVenta == "Pedido") {
                //Validamos si el movimiento es de serie o no
                keyListaArticulos.forEach((keyArticulo) => {
                    if (listaArticulos[keyArticulo].tipoArticulo == "Serie") {
                        if (
                            listaArticulos[keyArticulo].venderSerie ===
                                undefined ||
                            listaArticulos[keyArticulo].venderSerie === null
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
                        "warning"
                    );
                    $("#loader").hide();
                } else {
                    keyListaArticulos.forEach((keyArticulo) => {
                        if (
                            listaArticulos[keyArticulo].tipoArticulo == "Serie"
                        ) {
                            listaArticulos[keyArticulo]["venderSerie"].forEach(
                                (serie) => {
                                    repetidos[serie] =
                                        (repetidos[serie] || 0) + 1;
                                }
                            );
                        }
                    });

                    let keySeries = Object.keys(repetidos);

                    keySeries.forEach((keySerie) => {
                        if (repetidos[keySerie] !== 1) {
                            showMessage(
                                "Por favor, ingresa diferentes series",
                                "error"
                            );
                            $("#loader").hide();
                            isSerieRepetido = true;
                            return false;
                        }
                    });
                }
            }

            // console.log(listaArticulos);

            keyListaArticulos.forEach((keyArticulo) => {
                if (listaArticulos[keyArticulo].tipoArticulo == "Kit") {
                    if (
                        listaArticulos[keyArticulo].ventaKit === undefined ||
                        listaArticulos[keyArticulo].ventaKit === null
                    ) {
                        isSerieVacio2 = true;
                        claveArticuloKit = keyArticulo;
                        return false;
                    }

                    if (movVenta == "Factura" || movVenta == "Pedido") {
                        listaArticulos[keyArticulo].ventaKit[
                            "articulos"
                        ].forEach((articulo) => {
                            if (articulo.tipo == "Serie") {
                                let clavesSeries = Object.keys(
                                    listaArticulos[keyArticulo].ventaKit[
                                        "ventaSeriesKits"
                                    ]
                                );
                                //verificar que el articulo tenga configurado sus series en el kit
                                if (
                                    clavesSeries.includes(articulo.articuloId)
                                ) {
                                    //verificar que el articulo tenga series configuradas
                                    if (
                                        listaArticulos[keyArticulo].ventaKit[
                                            "ventaSeriesKits"
                                        ][articulo.articuloId].length == 0
                                    ) {
                                        isSerieVacio3 = true;
                                        claveArticuloKit = articulo.articuloId;
                                        return false;
                                    }
                                } else {
                                    isSerieVacio3 = true;
                                    claveArticuloKit = articulo.articuloId;
                                    return false;
                                }
                            }
                        });
                    }

                    //    console.log(listaArticulos[keyArticulo].ventaKit['articulos']);
                }
            });

            if (isSerieVacio2) {
                showMessage2(
                    "Artículo Kit",
                    `Por favor, verifique que el artículo con clave ${
                        claveArticuloKit.split("-")[0]
                    } tenga completo sus kits`,
                    "warning"
                );
                $("#loader").hide();
            }

            if (isSerieVacio3) {
                showMessage2(
                    "Artículo Kit",
                    `Por favor, verifique que el artículo en el kit con clave ${
                        claveArticuloKit.split("-")[0]
                    } tenga completo sus números de serie`,
                    "warning"
                );
                $("#loader").hide();
            }

            if (
                !isSerieRepetido &&
                !isSerieVacio &&
                !isSerieVacio2 &&
                !isSerieVacio3
            ) {
                $.ajax({
                    url: url,
                    type: "POST",
                    data: $("#progressWizard").serialize(),
                    success: function ({ mensaje, estatus, id }) {
                        // console.log(mensaje);
                        $("#loader").hide();
                        if (estatus === 200) {
                            showMessage2(
                                "Afectación exitosa",
                                mensaje,
                                "success"
                            );
                            let ruta = window.location.href;
                            let ruta2 = ruta.split("/");
                            if (ruta2.length > 6) {
                                ruta + "/" + id;
                            } else {
                                ruta += "/" + id;
                            }
                            setTimeout(function () {
                                window.location.href = ruta;
                            }, 1000);
                        }

                        if (estatus === 500) {
                            showMessage2("Precaución", mensaje, "warning");
                        }

                        if (estatus === 404) {
                            showMessage2("Precaución", mensaje, "warning");

                            let ruta = window.location.href;
                            let ruta2 = ruta.split("/");
                            if (ruta2.length > 6) {
                                ruta + "/" + id;
                            } else {
                                ruta += "/" + id;
                            }
                            setTimeout(function () {
                                window.location.href = ruta;
                            }, 1000);
                        }

                        if (estatus === 400) {
                            showMessage2("Error", mensaje, "error");

                            let ruta = window.location.href;
                            let ruta2 = ruta.split("/");
                            if (ruta2.length > 6) {
                                ruta + "/" + id;
                            } else {
                                ruta += "/" + id;
                            }
                            setTimeout(function () {
                                window.location.href = ruta;
                            }, 1000);
                        }
                    },
                });
            }
        }
    });
}

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
