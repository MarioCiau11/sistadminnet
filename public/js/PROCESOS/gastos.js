let conceptosDelete = [];
let result = 1000;
const movimientosArray = {
    Compras: "PROC_PURCHASE",
    CxP: "PROC_ACCOUNTS_PAYABLE",
    Din: "PROC_TREASURY",
    Gastos: "PROC_EXPENSES",
};

jQuery(document).ready(function () {
    let retencion = false;

    $("form").keypress(function (e) {
        if (e.which == 13) {
            return false;
        }
    });

    const $select = jQuery(
        "#select-movimiento, #select-moduleConcept, #select-listaPrecios, #select-proveedorCondicionPago, #select-search-hided, #select-PaymentMethod"
    ).select2({
        minimumResultsForSearch: -1,
    });

    jQuery("#progressWizard").validate({
        submitHandler: function (form) {
            const isPreciosVacios = validarConceptoImporte();
            let isEstablecimientoVacio;

            if ($("#select-movimiento").val() === "Reposición Caja") {
                isEstablecimientoVacio = validarEstablecimiento();
            } else {
                isEstablecimientoVacio = false;
            }
            if (isPreciosVacios) {
                showMessage(
                    "Debe ingresar un precio para cada concepto",
                    "error"
                );
                return false;
            }
            if (isEstablecimientoVacio) {
                showMessage("Debe seleccionar un establecimiento", "error");
                return false;
            }
            jsonConceptos();
            console.log(jsonConceptos());
            form.submit();
        },
        rules: {
            movimientos: {
                required: true,
                maxlength: 100,
            },
            nameMoneda: {
                required: true,
                maxlength: 100,
            },
            cuentaKey: {
                required: function () {
                    if (jQuery("#select-movimiento").val() === "Reposición Caja") {
                        return true;
                    }
                    return false;
                },
            },
            claveBancaria: {
                required: function () {
                    if (jQuery("#select-movimiento").val() === "Reposición Caja") {
                        return true;
                    }
                    return false;
                },
            },
            proveedorKey: {
                required: true,
            },
            proveedorName: {
                required: true,
            },
            proveedorCondicionPago: {
                required: true,
            },
            proveedorFechaVencimiento: {
                required: true,
            },
            formaPago: {
                required: true,
            },
            concepto: {
                required: true,
                maxlength: 100,
            },
        },
        messages: {
            movimientos: {
                required: "Este campo es requerido",
                maxlength: jQuery.validator.format("Maximo de {0} caracteres"),
            },
            nameMoneda: {
                required: "Este campo es requerido",
                maxlength: jQuery.validator.format("Maximo de {0} caracteres"),
            },
            cuentaKey: {
                required: "Este campo es requerido",
            },
            claveBancaria: {
                required: "Este campo es requerido",
            },
            proveedorKey: {
                required: "Este campo es requerido",
            },
            proveedorName: {
                required: "Este campo es requerido",
            },
            proveedorCondicionPago: {
                required: "Este campo es requerido",
            },
            proveedorFechaVencimiento: {
                required: "Este campo es requerido",
            },
            formaPago: {
                required: "Este campo es requerido",
            },
            concepto: {
                required: "Este campo es requerido",
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

    $("#proveedorModal").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#conceptoModal").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#ModalFlujo").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#conceptoModal").on("show.bs.modal", function (e) {
        /*
        - Validamos que se haya seleccionado un movimiento *Listo
        - Validamos que se haya seleccionado un proveedor  *Listo
        - Validamos que se haya seleccionado una forma de pago *Listo
        - Validamos que se haya seleccionado una cuenta de dinero *Listo
        - Validamos que haya un importe seleccionado en los conceptos en el detalle
        */
        const isMovimientoSelected = validarMovimientoSeleccionado();
        const isProveedorSelected = validarProveedorSeleccionado();
        const isCondicionPagoSelected = validarCondicionPagoSeleccionado();
        const isFormaPagoSelected = validarFormaPagoSeleccionado();
        const isCuentaDineroSelected = validarCuentaSeleccionado();
        const isEstablecimientoSelected = validarEstablecimiento();

        const isPrecioEmpty = validarConceptoImporte();

        if ($("#select-movimiento").val() === "Factura de Gasto") {
            if (
                isMovimientoSelected ||
                isProveedorSelected ||
                isCondicionPagoSelected ||
                isFormaPagoSelected ||
                isPrecioEmpty
            ) {
                showMessage(
                    `No se ha seleccionado ${
                        isMovimientoSelected
                            ? "un movimiento"
                            : isProveedorSelected
                            ? "un proveedor"
                            : isCondicionPagoSelected
                            ? "una condicion de pago"
                            : isFormaPagoSelected
                            ? "una forma de pago"
                            : isPrecioEmpty
                            ? "un precio"
                            : ""
                    }`,
                    "error"
                );
                return false;
            }
        } else {
            if (
                isMovimientoSelected ||
                isProveedorSelected ||
                isCondicionPagoSelected ||
                isFormaPagoSelected ||
                isCuentaDineroSelected ||
                isEstablecimientoSelected ||
                isPrecioEmpty
            ) {
                showMessage(
                    `No se ha seleccionado ${
                        isMovimientoSelected
                            ? "un movimiento"
                            : isProveedorSelected
                            ? "un proveedor"
                            : isCondicionPagoSelected
                            ? "una condicion de pago"
                            : isFormaPagoSelected
                            ? "una forma de pago"
                            : isCuentaDineroSelected
                            ? "una cuenta de Bancos/Efectivo"
                            : isEstablecimientoSelected
                            ? "un establecimiento"
                            : isPrecioEmpty
                            ? "un precio"
                            : ""
                    }`,
                    "error"
                );
                return false;
            }
        }
    });

    const leyendas = {
        'Factura de Gasto': 'Se recibe la factura del gasto. Con esta operación se generará la cuenta por pagar para programar pagos.',
        'Reposición Caja': 'Este proceso sirve para realizar la declaración de los gastos no deducibles para solicitar la reposición del fondo fijo de caja de efectivos.',
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
    const selectedMovimiento = $('#select-movimiento').val();
    mostrarLeyenda(selectedMovimiento);

    jQuery("#select-moneda").select2();

    jQuery("#select-search-hided").on("change", function (e) {
        $.ajax({
            url: "/getTipoCambio",
            type: "GET",
            data: {
                tipoCambio: jQuery("#select-search-hided").val(),
            },
            success: function (data) {
                $("#nameTipoCambio").val(parseFloat(data.money_change));
            },
        });
    });

    const tableCuenta = jQuery("#shTable1").DataTable({
        select: {
            style: "single",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    tableCuenta.on("select", function (e, dt, type, indexex) {
        const rowData = tableCuenta.row(indexex).data();

        $("#cuentaKey").val(rowData[0]);
        $("#cuentaKey").keyup();
        $("#tipoCuenta").val(rowData[2]);
        $("#tipoCambio").val(rowData[1]);
    });

    const tableProveedores = jQuery("#shTable2").DataTable({
        select: {
            style: "single",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    tableProveedores.on("select", function (e, dt, type, indexex) {
        const rowData = tableProveedores.row(indexex).data();
        $("#proveedorKey").val(rowData[0]);
        $("#proveedorName").val(rowData[1]);
        $("#proveedorKey").change();
        $("#proveedorName").change();
        // getProveedorByClave(rowData[0]);
    });

    const tableAcreedores = jQuery("#shTable4").DataTable({
        select: {
            style: "single",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    const tableConceptos = jQuery("#shTable5").DataTable({
        select: {
            style: "multiple",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    const tableAntecendentes = jQuery("#shTable6").DataTable({
        select: {
            style: "single",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    tableAntecendentes.on("select", function (e, dt, type, indexex) {
        const rowData = tableAntecendentes.row(indexex).data();
        console.log(rowData);

        $("#antecedentesName").val(rowData[0]);
    });

    const tableArticulos = jQuery("#shTable7").DataTable({
        select: {
            style: "single",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    tableArticulos.on("select", function (e, dt, type, indexex) {
        const rowData = tableArticulos.row(indexex).data();
        console.log(rowData);

        $("#activoFijoNombre").val(rowData[0]);
        $("#activoFijoSerie").val(rowData[2]);
    });

    jQuery("#activoFijo").on("change", function (e) {
        e.preventDefault();
        if (jQuery("#activoFijo").is(":checked")) {
            jQuery("#contenedorActivoFijo").show();
            jQuery("#contenedorActivoFijoSerie").show();
            jQuery("#activoFijoNombre").focus();
        } else {
            jQuery("#contenedorActivoFijo").hide();
            jQuery("#contenedorActivoFijoSerie").hide();
        }
    });

    jQuery("#antecedentes").on("change", function (e) {
        if (jQuery("#antecedentes").is(":checked")) {
            jQuery("#contenedorAntecedente").show();
            jQuery("#antecedentesName").focus();
        } else {
            jQuery("#contenedorAntecedente").hide();
        }
    });

    // if(activoFijo === true){
    //     console.log("activo fijo");
    // }
    jQuery("#agregarConceptos").on("click", function (e) {
        const rowData = tableConceptos.rows(".selected").data();

        let conceptos = [];
        rowData.each(function (index, value) {
            conceptos.push(index);
        });

        if (conceptos.length > 0) {
            $("#controlArticulo").hide();
            $("#controlConcepto2").hide();

            // let retencion = false;

            for (let i = 0; i < conceptos.length; i++) {
                let concepto = conceptos[i][1];
                const isRetencionesVisible = $(".retencion").is(":visible");
                let conceptoKey = conceptos[i][0].replace(/\s/g, "");
                let iva;
                let retencion1;
                let retencion2;
                if (conceptos[i][3] !== "" || conceptos[i][4] !== "") {
                    retencion = true;
                }

                if (calculoImpuestos === "0") {
                    iva = parseFloat(conceptos[i][2]).toFixed(0);
                    retencion1 = conceptos[i][3];
                    retencion2 = conceptos[i][4];
                } else {
                    iva = "";
                    retencion1 = "";
                    retencion2 = "";
                }

                if (isRetencionesVisible) {
                    retencion = true;
                }

                $("#conceptoItem").append(`  
                    <tr id="${conceptoKey}-${result}">
                    <td style="display: none" class='establecimiento'><input  id="keyArticulo" type="text" class="keyArticulo" readonly style='display:none' value='${conceptoKey}' name="dataConceptos[]"><input id="establecimiento-${conceptoKey}-${result}"
                            type="text" class="keyArticulo" readonly name="dataConceptos[]" value=''>
                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal"
                            data-target=".modal3" id="modal-${conceptoKey}-${result}">...</button>
                    </td>
                    <td id="btnInput"><input id="concept-${conceptoKey}-${result}" type="text" class="keyArticulo" readonly value='${concepto}' title="${concepto}" name="dataConceptos[]">
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                            data-target=".modal4">...</button>
                    </td>
                    <td><input id="ref-${conceptoKey}-${result}" type="text" class="botonesArticulos" name="dataConceptos[]">
                    </td>
                    <td><input id="cantidad-${conceptoKey}-${result}" type="number" class="botonesArticulos"  value='1' name="dataConceptos[]" min="1" onchange="actualizarTotales('${conceptoKey}', '${result}')">
                    </td>
                    <td><input id="precio-${conceptoKey}-${result}" type="text" class="botonesArticulos" onchange="calcularImporteRetencion('${conceptoKey}', '${result}')" name="dataConceptos[]">
                    </td>
                    <td><input id="importe-${conceptoKey}-${result}" type="text" class="botonesArticulos" readonly value='' name="dataConceptos[]">
                    </td>
                    <td><input id="pIva-${conceptoKey}-${result}" type="text" class="botonesArticulos" readonly value='${
                    iva === "NaN" ? "" : iva
                }' name="dataConceptos[]">
                    </td>
                    <td><input id="iva-${conceptoKey}-${result}" type="text" class="botonesArticulos" readonly value='0.00' name="dataConceptos[]">
                    </td>
                    <td ${
                        retencion
                            ? 'style="display: "'
                            : 'style="display: none"'
                    } class="retencion2"><input id="pRet1-${conceptoKey}-${result}"
                            type="text" class="botonesArticulos" readonly value='${
                                retencion1 == "NaN" ? "" : retencion1
                            }' name="dataConceptos[]"></td>
                    <td ${
                        retencion
                            ? 'style="display: "'
                            : 'style="display: none"'
                    } class="retencion2"><input id="pRetISR-${conceptoKey}-${result}"
                            type="text" class="botonesArticulos" readonly value='0.00' name="dataConceptos[]"></td>
                    <td ${
                        retencion
                            ? 'style="display: "'
                            : 'style="display: none"'
                    } class="retencion2"><input id="pRet2-${conceptoKey}-${result}"
                            type="text" class="botonesArticulos" readonly value='${
                                retencion2 == "NaN" ? "" : retencion2
                            }' name="dataConceptos[]"></td>
                    <td ${
                        retencion
                            ? 'style="display: "'
                            : 'style="display: none"'
                    } class="retencion2"><input id="retIva-${conceptoKey}-${result}" type="text" class="botonesArticulos" readonly value='0.00' name="dataConceptos[]">
                    </td>
                    <td><input id="total-${conceptoKey}-${result}" type="text" class="botonesArticulos" readonly value='0.00' name="dataConceptos[]">
                    </td>
                    <td style="display: flex; justify-content: center; align-items: center">
    
                        <i class="fa fa-trash-o  btn-delete-articulo" onclick="eliminarConcepto('${conceptoKey}', '${result}')" aria-hidden="true"
                            style="color: red; font-size: 25px; cursor: pointer;"></i>
                    </td>
                </tr>`);
                contadorConceptos++;
            } //fin for

            if (retencion) {
                $(".retencion").show();
                $(".retencion2").show();
            }

            if ($("#select-movimiento").val() === "Reposición Caja") {
                $(".establecimiento").show();
            } else {
                $(".establecimiento").hide();
            }

            $("#shTable5").DataTable().rows(".selected").deselect();

            result++;
        }
    });

    jQuery("#select-movimiento").on("change", function (e) {
        const mov = $("#select-movimiento").val();

        if (mov === "Reposición Caja") {
            $(".establecimiento").show();
            $("#contenedorCuenta").show();
        } else {
            $(".establecimiento").hide();
            $("#contenedorCuenta").hide();
        }
    });

    $("#cuentaModal").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    jQuery("#select-proveedorCondicionPago").on("change", function () {
        tipoCondicionPago();
    });

    jQuery("#select-proveedorCondicionPago").trigger("change");

    $("#proveedorKey").change(function () {
        let key = $(this).val() !== " " ? $(this).val() : false;

        if (key) {
            getProveedorByClave(key);
        } else {
            jQuery("#proveedorFechaVencimiento").val("");
            jQuery("#select-proveedorCondicionPago").val("");
            jQuery("#select-proveedorCondicionPago").change();
            jQuery("#select-proveedorCondicionPago").attr("readonly", false);
            jQuery("#select-PaymentMethod").val("");
            jQuery("#select-PaymentMethod").change();
            jQuery("#select-PaymentMethod").attr("readonly", false);
            $("#proveedorName").val("");
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
            jQuery("#proveedorFechaVencimiento").keyup();
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
                        jQuery("#proveedorFechaVencimiento").keyup();
                    }
                },
            });
        } else {
            jQuery("#proveedorFechaVencimiento").val("");
        }
    }

    function getProveedorByClave(clave) {
        $.ajax({
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

                        $("#proveedorName").val(data.providers_name);
                        tipoCondicionPago();
                    } else {
                        jQuery("#select-proveedorCondicionPago").val("");
                        jQuery("#select-proveedorCondicionPago").change();
                        // jQuery("#select-proveedorCondicionPago").attr(
                        //     "readonly",
                        //     false
                        // );
                        $("#proveedorName").val(data.providers_name);
                    }
                    if (data.providers_formPayment !== null) {
                        jQuery("#select-PaymentMethod").val(
                            data.providers_formPayment
                        );
                        jQuery("#select-PaymentMethod").change();
                        // jQuery("#select-PaymentMethod").attr("readonly", true);
                        $("#proveedorName").val(data.providers_name);
                    } else {
                        jQuery("#select-PaymentMethod").val("");
                        jQuery("#select-PaymentMethod").change();
                        // jQuery("#select-PaymentMethod").attr("readonly", false);

                        $("#proveedorName").val(data.providers_name);
                    }
                } else {
                    jQuery("#proveedorFechaVencimiento").val("");
                    jQuery("#select-proveedorCondicionPago").val("");
                    jQuery("#select-proveedorCondicionPago").change();
                    jQuery("#select-PaymentMethod").val("");
                    jQuery("#select-PaymentMethod").change();
                    // jQuery("#select-money").val("");
                    jQuery("#select-money").change();
                    $("#proveedorName").val("");
                }
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

                jQuery("#select-proveedorCondicionPago").keyup();
                jQuery("#proveedorFechaVencimiento").keyup();
                jQuery("#select-PaymentMethod").keyup();
                $("#proveedorName").keyup();
            },
        });
    }

    function showMessage(mensaje, icon) {
        swal(mensaje, {
            button: "OK",
            icon: icon,
        });
    }

    function validarProveedorSeleccionado() {
        const $proveedorNombre = $("#proveedorName").val();
        let proveedorNombreVacio = false;
        if ($proveedorNombre === "") {
            proveedorNombreVacio = true;
        }
        return proveedorNombreVacio;
    }

    function validarMovimientoSeleccionado() {
        const $movimientoNombre = $("#select-movimiento").val();
        let movimientoNombreVacio = false;

        if ($movimientoNombre === "") {
            movimientoNombreVacio = true;
        }

        return movimientoNombreVacio;
    }

    function validarFormaPagoSeleccionado() {
        const $formaPagoNombre = $("#select-PaymentMethod").val();
        let formaPagoNombreVacio = false;

        if ($formaPagoNombre === "") {
            formaPagoNombreVacio = true;
        }

        return formaPagoNombreVacio;
    }

    function validarCondicionPagoSeleccionado() {
        const $condicionPagoNombre = $("#select-proveedorCondicionPago").val();
        let condicionPagoNombreVacio = false;

        if ($condicionPagoNombre === "") {
            condicionPagoNombreVacio = true;
        }

        return condicionPagoNombreVacio;
    }

    function validarCuentaSeleccionado() {
        const $cuentaNombre = $("#cuentaKey").val();
        let cuentaNombreVacio = false;
        if ($cuentaNombre === "") {
            cuentaNombreVacio = true;
        }
        return cuentaNombreVacio;
    }

    function validarMonedas() {
        const moneda = $("#select-search-hided").val();
        const cuentaMoneda = $("#tipoCambio").val();

        if (moneda !== cuentaMoneda) {
            return true;
        }

        return false;
    }

    function validarConceptoImporte() {
        let isPrecioVacio = false;
        $('input[id^="precio-"]').each(function (index, value) {
            let precio = $(value).val();
            if (precio === "" || precio == "0" || precio == "0.00") {
                isPrecioVacio = true;
            }
        });

        return isPrecioVacio;
    }

    function validarEstablecimiento() {
        let isEstablecimientoVacio = false;
        $('input[id^="establecimiento-"]').each(function (index, value) {
            let establecimiento = $(value).val();
            if (establecimiento === "") {
                isEstablecimientoVacio = true;
            }
        });

        return isEstablecimientoVacio;
    }

    const afectar = function (e) {
        e.preventDefault();
        let movimiento = $("#select-movimiento").val();
        let validacion = false;
        let movimientoNombreVacio = validarMovimientoSeleccionado();
        let proveedorNombreVacio = validarProveedorSeleccionado();
        let condicionPagoNombreVacio = validarCondicionPagoSeleccionado();
        let formaPagoNombreVacio = validarFormaPagoSeleccionado();
        let cuentaNombreVacio = validarCuentaSeleccionado();
        let validarCuentas = validarMonedas();

        let isPrecioVacio = validarConceptoImporte();
        let isEstablecimientoVacio = validarEstablecimiento();

        if ($("#select-movimiento").val() === "Factura de Gasto") {
            if (
                proveedorNombreVacio ||
                condicionPagoNombreVacio ||
                movimientoNombreVacio ||
                formaPagoNombreVacio ||
                isPrecioVacio
            ) {
                showMessage(
                    `No se ha seleccionado ${
                        movimientoNombreVacio
                            ? "un movimiento"
                            : proveedorNombreVacio
                            ? "un proveedor"
                            : condicionPagoNombreVacio
                            ? "una condición de pago"
                            : formaPagoNombreVacio
                            ? "una forma de pago"
                            : isPrecioVacio
                            ? "un precio"
                            : ""
                    }`,
                    "error"
                );
                return false;
            } else {
                validacion = true;
            }
        } else if (
            proveedorNombreVacio ||
            condicionPagoNombreVacio ||
            movimientoNombreVacio ||
            formaPagoNombreVacio ||
            cuentaNombreVacio ||
            isEstablecimientoVacio ||
            isPrecioVacio ||
            validarCuentas
        ) {
            showMessage(
                `No se ha seleccionado ${
                    movimientoNombreVacio
                        ? "un movimiento"
                        : proveedorNombreVacio
                        ? "un proveedor"
                        : condicionPagoNombreVacio
                        ? "una condición de pago"
                        : formaPagoNombreVacio
                        ? "una forma de pago"
                        : cuentaNombreVacio
                        ? "una cuenta"
                        : isEstablecimientoVacio
                        ? "un establecimiento"
                        : isPrecioVacio
                        ? "un precio"
                        : validarCuentas
                        ? "una cuenta con la misma moneda que el movimiento"
                        : ""
                }`,
                "error"
            );
            return false;
        } else {
            validacion = true;
        }

        if (validacion) {
            if (contadorConceptos === 0) {
                showMessage("No hay nada que afectar!", "error");
                return false;
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
                        let listaConceptos = jsonConceptos();

                        $("#loader").show();
                        $.ajax({
                            url: "/gestion_finanzas/gastos/afectar",
                            type: "POST",
                            data: $("#progressWizard").serialize(),
                            success: function ({ mensaje, estatus, id }) {
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
                                } else {
                                    showMessage2("Error", mensaje, "error");
                                }
                            },
                        });
                    }
                });
            }
        }
    };

    $("#establecimientoModal").on("show.bs.modal", function (event) {
        let button = $(event.relatedTarget);
        let identificadorFila = button.parent().parent().attr("id");
        $("#auxEstablecimiento").val(identificadorFila);
    });

    jQuery("#agregarEstablecimiento").on("click", function (e) {
        let aux = $("#auxEstablecimiento").val();
        let data = aux.split("-");

        const rowData = jQuery("#shTable4").DataTable().row(".selected").data();

        if (rowData !== undefined) {
            $("#establecimiento-" + data[0] + "-" + data[1]).val(rowData[0]);
            $("#shTable4").DataTable().row(".selected").deselect();
        }
    });

    const cancelar = function (e) {
        e.preventDefault();
        let status = $("#status").text().trim();
        let movimiento = $("#select-movimiento").val();
        const folio = $("#folioGasto").val();
        const id = $("#idGasto").val();
        console.log(status, movimiento, id);

        if (
            (status === "FINALIZADO" && movimiento === "Factura de Gasto") ||
            (status === "FINALIZADO" && movimiento === "Reposición Caja")
        ) {
            swal({
                title: "¿Está seguro de cancelar el gasto?",
                text: movimiento + " : " + folio + "",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                if (willDelete) {
                    $("#loader").show();
                    $.ajax({
                        url: "/cancelarGasto/",
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
                                        "/gestion_finanzas/gastos/create";
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
        } else {
            showMessage("El gasto ya esta cancelado", "error");
        }
    };

    const eliminar = function (e) {
        e.preventDefault();
        const id = $("#idGasto").val();

        if (id === "0") {
            showMessage("No se ha seleccionado ningun gasto", "error");
        } else {
            swal({
                title: "¿Está seguro de eliminar el gasto?",
                text: "Una vez eliminado no podrá recuperarlo",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                if (willDelete) {
                    $("#loader").show();
                    $.ajax({
                        url: "/eliminarGasto",
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
                                    window.location.href =
                                        "/gestion_finanzas/gastos/create";
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

    disabledCompra();
    calcularTotal();
    $("#select-movimiento").trigger("change");

    let estatus = jQuery("#status").text().trim();

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

    if (estatus === "CANCELADO" || estatus === "INICIAL") {
        jQuery("#cancelar-boton").unbind("click");
        jQuery("#cancelar-boton").hide();
    } else {
        jQuery("#cancelar-boton").click(cancelar);
        jQuery("#cancelar-boton").show();
    }

    $("#copiar-gasto").click(function (e) {
        e.preventDefault();
        //Enviamos el formulario para copiar la compra
        const form = jQuery("#progressWizard");
        const inputCopiar =
            '<input type="text" name="copiar" value="copiar" readonly>';
        form.append(inputCopiar);

        $('input[id^="id-"]').each(function (index, value) {
            $(this).remove();
        });
        $("#idGasto").val("0");

        form.submit();
    });
    $("#activoFijo").change();
    $("#antecedentes").change();

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

    //Aquí comienza el flujo
    let primerFlujo = $("#movimientoFlujo").val();

    if (primerFlujo !== "") {
        let bodyTable = $("#movimientosTr");

        let jsonFlujo = JSON.parse(primerFlujo);
        generarTablaFlujo(bodyTable, jsonFlujo);
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
    let movimiento = $("#select-movimiento").val();
    if (movimiento !== "") {
        let movimientoSinEspacios = movimiento.replace(/\s+/g, "");

        if ($("#" + movimientoSinEspacios).length === 0) {
            $("#afectar-boton").hide();
            $("#cancelar-boton").hide();
            $("#enviarForm").hide();
        }
    }
}); //Fin del document ready

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

function actualizarTotales(key, posicion) {
    calcularImporteRetencion(key, posicion);
}

function showMessage2(titulo, mensaje, icon) {
    swal({
        title: titulo,
        text: mensaje,
        icon: icon,
    });
}

function calcularImporteRetencion(clave, posicion) {
    let precio = $("#precio-" + clave + "-" + posicion)
        .val()
        .replace(/[$,]/g, "")
        .replace(/[^0-9,.]/g, "")
        .replace(/,/g, ".");
    let cantidad = $("#cantidad-" + clave + "-" + posicion)
        .val()
        .replace(/,/g, "");

    let pIva = $("#pIva-" + clave + "-" + posicion)
        .val()
        .replace(/,/g, "");
    let pRetencion = $("#pRet1-" + clave + "-" + posicion)
        .val()
        .replace(/,/g, "");
    let pRetencion2 = $("#pRet2-" + clave + "-" + posicion)
        .val()
        .replace(/,/g, "");
    let inputIva = $("#iva-" + clave + "-" + posicion);
    let inputRet1 = $("#pRetISR-" + clave + "-" + posicion);
    let inputRet2 = $("#retIva-" + clave + "-" + posicion);
    let inputTotal = $("#total-" + clave + "-" + posicion);
    let inputImporte = $("#importe-" + clave + "-" + posicion);
    let inputPrecio = $("#precio-" + clave + "-" + posicion);
    pRetencion = pRetencion === "" ? 0 : pRetencion;
    pRetencion2 = pRetencion2 === "" ? 0 : pRetencion2;

    let ret1 = precio * (pRetencion / 100);
    let ret2 = precio * (pRetencion2 / 100);

    let importe = precio * cantidad;
    let importeIva = importe * (pIva / 100);
    let total = importe + importeIva - (ret1 + ret2); //formula de calculo de importe con retencion

    let precioFormato = truncarDecimales(round(precio), 2);

    let importeFormato = truncarDecimales(round(importe), 2);

    let importeIvaFormato = truncarDecimales(round(importeIva), 2);

    let ret1Formato = truncarDecimales(round(ret1), 2);

    let ret2Formato = truncarDecimales(round(ret2), 2);

    let totalFormato = truncarDecimales(round(total), 2);

    inputPrecio.val(formatoMexico(precioFormato));
    inputImporte.val(formatoMexico(importeFormato));
    inputIva.val(formatoMexico(importeIvaFormato));
    inputRet1.val(formatoMexico(ret1Formato));
    inputRet2.val(formatoMexico(ret2Formato));
    inputTotal.val(formatoMexico(totalFormato));

    calcularTotal();
}

function calcularTotal() {
    let subTotal = 0;
    let iva = 0;
    let total = 0;

    $('input[id^="importe-"]').each(function (index, value) {
        let idIntputs = $(this).attr("id");
        // console.log(idIntputs);
        let importes = $("#" + idIntputs)
            .val()
            .replace(/[$,]/g, "");
        if (importes !== "" && importes > 0) {
            subTotal += parseFloat(importes);
        }
    });

    $('input[id^="iva-"]').each(function (index, value) {
        let idIntputs = $(this).attr("id");
        let impuestos = $("#" + idIntputs)
            .val()
            .replace(/[$,]/g, "");
        if (impuestos !== "" && impuestos > 0) {
            iva += parseFloat(impuestos);
        }
    });

    $('input[id^="total-"]').each(function (index, value) {
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

    let totalFormato = truncarDecimales(round(total), 2);
    let subTotalFormato = truncarDecimales(round(subTotal), 2);
    let ivaFormato = truncarDecimales(round(iva), 2);

    if (total > 0) {
        $("#totalCompleto").val(formatoMexico(totalFormato));
    }

    $("#subTotalCompleto").val(formatoMexico(subTotalFormato));
    $("#impuestosCompleto").val(formatoMexico(ivaFormato));
}

function jsonConceptos() {
    let conceptos = [];
    let key = "";
    const inputSaveConcepto = $("#inputDataConceptos");
    $('input[name="dataConceptos[]"]').each(function (index, value) {
        let idIntputs = $(value).attr("id");
        //console.log(idIntputs);
        if (idIntputs === "keyArticulo") {
            key = $(value).val();
        } else {
            let identificador = idIntputs.split("-");
            let id = $("#id-" + key + "-" + identificador[2]).val();
            let establecimiento = $(
                "#establecimiento-" + key + "-" + identificador[2]
            ).val();
            let concepto = $("#concept-" + key + "-" + identificador[2]).val();
            let referencia = $("#ref-" + key + "-" + identificador[2]).val();
            let cantidad = $("#cantidad-" + key + "-" + identificador[2]).val();
            let precio = $("#precio-" + key + "-" + identificador[2]).val();
            let importe = $("#importe-" + key + "-" + identificador[2]).val();
            let porcenta_iva = $("#pIva-" + key + "-" + identificador[2]).val();
            let iva = $("#iva-" + key + "-" + identificador[2]).val();
            let porcentaje_retencion = $(
                "#pRet1-" + key + "-" + identificador[2]
            ).val();
            let retencion = $("#pRetISR-" + key + "-" + identificador[2]).val();

            let porcentaje_retencion2 = $(
                "#pRet2-" + key + "-" + identificador[2]
            ).val();
            let retencion2 = $("#retIva-" + key + "-" + identificador[2]).val();
            let total = $("#total-" + key + "-" + identificador[2]).val();

            conceptos = {
                ...conceptos,
                [key + "-" + identificador[2]]: {
                    id: id,
                    establecimiento: establecimiento,
                    concepto: concepto,
                    referencia: referencia,
                    cantidad: cantidad,
                    precio: precio,
                    importe: importe,
                    porcenta_iva: porcenta_iva,
                    iva: iva,
                    porcentaje_retencion: porcentaje_retencion,
                    retencion: retencion,
                    porcentaje_retencion2: porcentaje_retencion2,
                    retencion2: retencion2,
                    total: total,
                },
            };
        }
    });

    inputSaveConcepto.attr("name", "dataConceptosJson");
    inputSaveConcepto.val(JSON.stringify(conceptos));
    console.log(conceptos);

    return conceptos;
    // console.log(conceptos);
}

function eliminarConcepto(clave, posicion) {
    const isRetencionesVisible = $(".retencion").is(":visible");
    const isEstablecimientoVisible = $(".establecimiento").is(":visible");

    if (contadorConceptos > 0) {
        conceptosDelete = {
            ...conceptosDelete,
            [clave + "-" + posicion]: jQuery(
                "#id-" + clave + "-" + posicion
            ).val(),
        };

        $("#" + clave + "-" + posicion).remove();
        calcularTotal();
        contadorConceptos--;
        console.log(contadorConceptos);

        jQuery("#inputDataConceptosDelete").attr(
            "value",
            JSON.stringify(conceptosDelete)
        );
    }

    if (contadorConceptos === 0) {
        $("#controlArticulo").show();
        $("#controlConcepto2").show();

        if (isRetencionesVisible) {
            $(".retencion").show();
        }

        if (isEstablecimientoVisible) {
            $(".establecimiento").show();
        }

        $("#totalCompleto").val("$0.00");
    }
}

function disabledCompra() {
    let status = $("#status").text().trim();
    let movimiento = $("#select-movimiento").val();
    if (status !== "INICIAL") {
        $("#conceptoItem")
            .find("input[type='number'], input[type='text']")
            .attr("readonly", true);
        $("#conceptoItem").find("button").attr("disabled", true);
        $(".eliminacion-concepto").hide();
        $("#select-movimiento").attr("readonly", true);
        $("#select-search-hided").attr("readonly", true);
        $("#nameTipoCambio").attr("readonly", true);
        $("#select-PaymentMethod").attr("readonly", true);
        $("#observaciones").attr("readonly", true);
        $("#saldoProveedor").attr("readonly", true);
        $("#select-proveedorCondicionPago").attr("readonly", true);
        $("#proveedorKey").attr("readonly", true);
        $("#fechaEmision").attr("readonly", true);
        $("#proveedorFechaVencimiento").attr("readonly", true);
        $("#almacenKey").attr("readonly", true);
        $("#provedorModal").attr("disabled", true);
        $("#provedorModal").attr("disabled", true);
        $("#antecedentes").attr("disabled", true);
        $("#activoFijoModal").attr("disabled", true);
        $("#cuentaModal").attr("disabled", true);
        $("#activoFijo").attr("disabled", true);
        $("#activoFijoNombre").attr("readonly", true);
        $("#activoFijoSerie").attr("readonly", true);
        $("#activoFijoModal").attr("disabled", true);
        $("#antecedentes").attr("disabled", true);
        $("#antecedentesName").attr("readonly", true);
        $("#antecedentesModal").attr("disabled", true);
        $("#enviarForm").hide();
    }
}
// function agregarEstablecimiento(clave, posicion) {

//     const rowData = jQuery("#shTable4").DataTable().row(".selected").data();
//     if(rowData === undefined){
//         $("#establecimiento-" + clave + "-" + posicion).val("");

//     }else{
//         let establecimiento = rowData[1];
//         $("#establecimiento-" + clave + "-" + posicion).val(establecimiento);
//         $("#shTable4").DataTable().row(".selected").deselect();
//     }

// }
