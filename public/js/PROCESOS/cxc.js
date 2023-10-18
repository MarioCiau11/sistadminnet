let result = 1000;
let tableCXPP;
let articlesDelete = {};
const movimientosArray = {
    Ventas: "PROC_SALES",
    CxC: "PROC_ACCOUNTS_RECEIVABLE",
    Din: "PROC_TREASURY",
};
jQuery(document).ready(function () {
    let iva;
    if (calculoImpuestos === "0") {
        iva = 0.16;
    } else {
        iva = 0;
    }
    const movimientoSelected = $("#select-movimiento").val();
    const $totalCxp = $("#totalCompleto");
    const $btnImporte = $("#btn-importe");

    $("form").keypress(function (e) {
        if (e.which == 13) {
            return false;
        }
    });

    // console.log(movimientoSelected);facturaModal
    const $select = jQuery(
        "#select-movimiento, #select-moduleConcept, #select-listaPrecios, #select-proveedorFormaPago, #select-search-hided, #select-motivoCancelacion, #select-folioSustitucion, #select-facturaRelacion"
    ).select2({
        minimumResultsForSearch: -1,
    });

    jQuery("#select-basic-identificadorCFDI").select2();

    jQuery("#progressWizard").validate({
        submitHandler: function (form) {
            let selectMov = $("#select-movimiento").val();

            switch (selectMov) {
                case "Cobro de Facturas":
                    validacionesForm(form);
                    break;
                case "Aplicación":
                    validacionesForm(form);
                    break;

                default:
                    jsonCxpDetails();
                    form.submit();
                    break;
            }
            return false;
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
                    if (jQuery("#select-movimiento").val() !== "Factura") {
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
            proveedorFormaPago: {
                required: function () {
                    if (jQuery("#select-movimiento").val() !== "Factura") {
                        return true;
                    }
                    return false;
                },
            },
            proveedorFechaVencimiento: {
                required: true,
            },
            concepto: {
                required: function () {
                    if (jQuery("#select-movimiento").val() !== "Factura") {
                        return true;
                    }
                    return false;
                },
                maxlength: 100,
            },
            importe: {
                required: true,
            },
            anticiposKey: {
                required: function () {
                    if (jQuery("#select-movimiento").val() === "Aplicación") {
                        return true;
                    }
                    return false;
                },
            },
            referencia: {
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
            proveedorFormaPago: {
                required: "Este campo es requerido",
            },
            proveedorFechaVencimiento: {
                required: "Este campo es requerido",
            },
            concepto: {
                required: "Este campo es requerido",
                maxlength: jQuery.validator.format("Maximo de {0} caracteres"),
            },
            importe: {
                required: "Este campo es requerido",
            },
            anticiposKey: {
                required: "Este campo es requerido. seleccione un anticipo",
            },
            referencia: {
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

        $.ajax({
            url: "/cxc/saldo/cliente/",
            method: "GET",
            data: {
                cliente: $("#proveedorKey").val(),
                moneda: $("#select-search-hided").val(),
            },
            success: function ({ status, saldo }) {
                if (status) {
                    if (saldo !== null) {
                        let saldoFormato = currency(saldo.balance_balance, {
                            precision: 2,
                            separator: ",",
                        }).format();
                        $saldoProveedor.val(saldoFormato);
                    } else {
                        $saldoProveedor.val("");
                    }
                }
            },
        });
    });

    const leyendas = {
        'Anticipo Clientes': 'Con este proceso registra los anticipos del cliente y emite el comprobante de ingreso.',
        'Aplicación': 'Este proceso realiza la disminución del del saldo de las facturas y emite el comprobante de egreso.',
        'Cobro de Facturas': 'Realiza la cobranza de 1 o varias facturas y genera el complemento de pago.',
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

        if (selectedMovimiento == "Aplicación" || $("#select-movimiento").val() == "Cobro de Facturas" || $("#select-movimiento").val() == "Devolución de Anticipo") {
            $("#afectar-boton").html("Finalizar <span class='glyphicon glyphicon-play pull-right'></span>");
        } else {
            $("#afectar-boton").html("Avanzar <span class='glyphicon glyphicon-play pull-right'></span>");
        }
    });

    // Mostrar la leyenda inicial al cargar la página si hay una opción seleccionada
    const selectedMovimiento = $('#select-movimiento').val();
    mostrarLeyenda(selectedMovimiento);


    const tableCuenta = jQuery("#shTable1").DataTable({
        select: {
            style: "single",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    jQuery("#select-movimiento").on("change", function (e) {
        const movimiento = $("#select-movimiento").val();
        if (movimiento === "Aplicación" || movimiento === "Cobro de Facturas") {
            $(".aplica").show();
            $(".aplicaC").show();
            $(".importe").show();
            $(".diferencia").show();
            $(".porcentaje").show();
            $(".accion").show();
        } else {
            $(".aplica").hide();
            $(".aplicaC").hide();
            $(".importe").hide();
            $(".diferencia").hide();
            $(".porcentaje").hide();
            $(".accion").hide();
        }

        if (movimiento === "Aplicación") {
            $("#anticipoAplicar").show();
            $("#referencia").attr('readonly', true);
            $("#anticipoChange").text("Anticipo/NC a Aplicar");
        } else if (movimiento === "Devolución de Anticipo") {
            $("#anticipoAplicar").show();
            $("#referencia").attr('readonly', false);
            $("#anticipoChange").text("Anticipo/NC a Devolver");
        }
        else {
            $("#anticipoAplicar").hide();
            $("#referencia").attr('readonly', false);
        }
    });



    tableCuenta.on("select", function (e, dt, type, indexex) {
        const rowData = tableCuenta.row(indexex).data();
        $("#cuentaKey").val(rowData[0]);
        $("#cuentaKey").val(rowData[0]).trigger("keyup");
        $("#tipoCambio").val(rowData[1]);
        $("#tipoCuenta").val(rowData[2]);
    });

    const tableCXP = jQuery("#shTable4").DataTable({
        select: {
            style: "multiple",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    const tableAnticipo = jQuery("#shTable6").DataTable({
        select: {
            style: "single",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    tableAnticipo.on("select", function (e, dt, type, indexex) {
        const rowData = tableAnticipo.row(indexex).data();
        // console.log(indexex, type, dt, rowData);
        $("#keyAnticipo").val(rowData[0]);
        $("#anticiposKey").val(rowData[1] + " " + rowData[0]);
        $("#referencia").val(rowData[1] + " " + rowData[0]);
        $("#importe").val(rowData[3]);
        $("#totalCompleto").val(rowData[3]);
        $("#cuentaKey").val(rowData[4]);
        $("#cuentaModal").attr("disabled", true);

        //traemos la informacion de la cuenta
        $.ajax({
            url: "/getInfoCuenta",
            type: "GET",
            data: {
                cuentaKey: rowData[4],
            },
            success: function ({ status, data }) {
                if (status) {
                    $("#tipoCuenta").val(data.moneyAccounts_accountType);
                    $("#tipoCambio").val(data.moneyAccounts_money);
                }
            },
        });
    });

    $("#agregaAnticipo").on("click", function (e) {
        e.preventDefault();
    });


    tableCXP.on("select", function (e, dt, type, indexex) {
        const rowData = tableCXP.row(indexex).data();
        // console.log(indexex, type, dt, rowData);
    });

    tableCXPP = jQuery("#shTable5").DataTable({
        select: {
            style: "multiple",
        },
        columnDefs: [
            {
                targets: [0],
                visible: false,
            },
        ],
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    tableCXPP.on("select", function (e, dt, type, indexex) {
        const rowData = tableCXPP.row(indexex).data();
        // console.log(indexex, type, dt, rowData);
    });

    let concepto = $("#select-moduleConcept").val();
    $('#select-movimiento').on('change', function() {
        var selectedMovimiento = $(this).val();
        console.log(selectedMovimiento);
        if
            (selectedMovimiento === 'Factura' || selectedMovimiento === 'Solicitud Depósito') {
            //si son alguno de estos tres no hacemos nada para no afectar el select de conceptos

        }
        if (selectedMovimiento === 'Anticipo Clientes' || selectedMovimiento === 'Aplicación') {
            // Lógica para filtrar los conceptos según el movimiento seleccionado
            $.ajax({
                url: '/api/cuentas_por_cobrar/getConceptosByMovimiento',
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
        //el caso de Cobro de Facturas es especial porque este ya trae el Concepto 'Pago' por default por lo que si se selecciona este movimiento tenemos que hacer que el select de conceptos se quede con el concepto 'Pago'
        if (selectedMovimiento === 'Cobro de Facturas')
        {
            var selectConceptos = $('#select-moduleConcept');
            selectConceptos.empty();
            selectConceptos.append($('<option>', { 
                value: 'Pago',
                text: 'Pago'
            }));
        }
    });

    if
        (selectedMovimiento === 'Factura' || selectedMovimiento === 'Solicitud Depósito') {
        //si son alguno de estos tres no hacemos nada para no afectar el select de conceptos

    }
    if (selectedMovimiento === 'Anticipo Clientes' || selectedMovimiento === 'Aplicación') {
        // Lógica para filtrar los conceptos según el movimiento seleccionado
        $.ajax({
            url: '/api/cuentas_por_cobrar/getConceptosByMovimiento',
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
    //el caso de Cobro de Facturas es especial porque este ya trae el Concepto 'Pago' por default por lo que si se selecciona este movimiento tenemos que hacer que el select de conceptos se quede con el concepto 'Pago'
    if (selectedMovimiento === 'Cobro de Facturas')
    {
        var selectConceptos = $('#select-moduleConcept');
        selectConceptos.empty();
        selectConceptos.append($('<option>', { 
            value: 'Pago',
            text: 'Pago'
        }));
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

    tableProveedores.on("select", function (e, dt, type, indexex) {
        const rowData = tableProveedores.row(indexex).data();
        $("#proveedorKey").val(rowData[0]);
        $("#proveedorName").val(rowData[1]);
        $("#proveedorKey").change();
        $("#proveedorName").change();
        $("#anticiposKey").val("");
        $("#referencia").val("");
        getClientesByClave(rowData[0]);
        $("#proveedorKey").keyup();
    });

    let proveedor = "";
    let mov = "";
    jQuery(".botonesAplica").change(function () {
        proveedor = $("#proveedorKey").val();
        mov = $(this).val();
    });

    $("#agregarAplicaModal").on("click", function (e) {
        $(".botonesAplica").change();
        if (mov !== "") {
            $.ajax({
                url: "/aplicaFolio/cxc",
                type: "GET",
                data: {
                    proveedor: proveedor,
                    movimiento: mov,
                    moneda: $("#select-search-hided").val(),
                },
                success: function ({ estatus, dataProveedor }) {
                    if (estatus === 200) {
                        if (dataProveedor.length > 0) {
                            dataProveedor.forEach((element) => {
                                let fecha =
                                    (element.accountsReceivable_expiration =
                                        moment(
                                            element.accountsReceivable_expiration
                                        ).format("YYYY-MM-DD"));
                                let importe = currency(
                                    element.accountsReceivable_total,
                                    {
                                        separator: ",",
                                        precision: 2,
                                        decimal: ".",
                                        symbol: "$",
                                    }
                                ).format();

                                let saldo = currency(
                                    element.accountsReceivable_balance,
                                    {
                                        separator: ",",
                                        precision: 2,
                                        symbol: "",
                                    }
                                ).format();

                                tableCXPP.row
                                    .add([
                                        element.accountsReceivable_id,
                                        element.accountsReceivable_movement,
                                        element.accountsReceivable_movementID,
                                        fecha,
                                        importe,
                                        saldo,
                                        element.accountsReceivable_money,
                                        element.accountsReceivable_branchOffice,
                                    ])
                                    .draw();
                            });
                        } else {
                            tableCXPP.clear().draw();
                        }
                    }
                },
            });
        }

        tableCXPP.clear().draw();
        const isValid = validateProveedor();
        const isValid2 = validateAplica();

        if (isValid) {
            showMessage(
                "No se ha seleccionado el proveedor en el movimiento",
                "error"
            );
            return false;
        }
        if (isValid2) {
            showMessage(
                "No se ha seleccionado el aplica en el movimiento",
                "error"
            );
            return false;
        }
    });

    jQuery("#modalAnticipos").on("show.bs.modal", function (e) {
        const isValid = validateProveedor();
        if (isValid) {
            showMessage(
                "No se ha seleccionado el cliente en el movimiento",
                "error"
            );
            return false;
        }
        tableAnticipo.clear().draw();
        $.ajax({
            url: "/cxc/getAnticipos",
            type: "GET",
            data: {
                proveedor: $("#proveedorKey").val(),
                moneda: $("#select-search-hided").val(),
            },
            success: function ({ estatus, anticipos }) {
                if (estatus === 200) {
                    if (anticipos.length > 0) {
                        anticipos.forEach((element) => {
                            let importe = currency(
                                element.accountsReceivable_balance,
                                {
                                    separator: ",",
                                    precision: 2,
                                    decimal: ".",
                                    symbol: "$",
                                }
                            ).format();

                            tableAnticipo.row
                                .add([
                                    element.accountsReceivable_movementID,
                                    element.accountsReceivable_movement,
                                    element.accountsReceivable_status,
                                    importe,
                                    element.accountsReceivable_moneyAccount,
                                ])
                                .draw();
                        });
                    } else {
                        tableAnticipo.clear().draw();
                    }
                }
            },
        });
    });



    jQuery("#agregarAplica").on("click", function (e) {
        const rowData = tableCXPP.rows(".selected").data();
        // console.log(rowData);

        if (rowData.length > 0) {
            let filaId = $("#fila-id").val();

            if (filaId !== "controlArticulo") {
                $(`#${filaId}`).remove();
                contadorArticulos--;
            }

            $("#controlArticulo").hide();
            $("#controlArticulo2").hide();
            // console.log(rowData);
            let datos = [];
            let claves = Object.keys(rowData);
            for (let i = 0; i < claves.length; i++) {
                if (!isNaN(claves[i])) {
                    datos.push(rowData[claves[i]]);
                }
            }

            for (let i = 0; i < datos.length; i++) {
                // console.log(datos[i]);
                let idMov = datos[i][0];
                let movimiento = datos[i][1];
                let movimientoID = datos[i][2];
                let sucursal = datos[i][7];

                let importe = currency(datos[i][5], {
                    separator: ",",
                    precision: 2,
                    decimal: ".",
                    symbol: "$",
                }).format();

                $("#articleItem").append(`<tr id="${idMov}-${result}">
                    <td style="display: none">
                        <input type="text" name="cxp[]" id="movId-${result}" value="${idMov}"/>
                    </td>
                    <td style="display: " class="aplica">
                    <select name="cxp[]" id="aplicaSelect-${result}" class="botonesAplica" value="${movimiento}" onchange="buscarMov('${idMov}', '${result}')">
                        <option value="${movimiento}"  selected>${movimiento}</option>
                    </select>
                    </td>
                    <td style="display: " class="aplicaC"  id="btnInput"><input id="aplicaC-${result}" type="text"
                    class="botonesArticulos" readonly value="${movimientoID}" name="cxp[]">
                        <button type="button" class="btn btn-info btn-sm" id="agregarAplicaModal-${result}" data-toggle="modal"
                        data-target=".modal4">...</button>
                    </td>
                    <td style="display: ; justify-content: center; align-items: center"
                        class="importe"><input id="importe-${result}" type="text"
                            class="botonesArticulos" value="${importe}" name="cxp[]" onchange="calcularTotal('${idMov}', '${result}')"></td>
                    <td style="display: ; justify-content: center; align-items: center"
                        class="diferencia"><input id="diferencia-${result}" type="text"
                            class="botonesArticulos" readonly value="$0.00" name="cxp[]"></td>
                    <td style="display: ; justify-content: center; align-items: center"
                        class="porcentaje"><input id="porcentaje-${result}" type="text"
                            class="botonesArticulos" readonly value="0" name="cxp[]"></td>
                    <td style="display: " class="accion">
                        <i class="fa fa-trash-o  btn-delete-articulo" aria-hidden="true"
                            style="color: red; font-size: 25px; cursor: pointer;" onclick="eliminarArticulo('${idMov}', '${result}')"></i>
                    </td>
                    <td style="display: none; justify-content: center; align-items: center"
                    ><input id="importeT-${result}" type="text"
                        class="botonesArticulos" value="${importe}" name="cxp[]"></td>
                    <td style="display: none; justify-content: center; align-items: center"
                    ><input id="sucursal-${result}" type="text"
                        class="botonesArticulos" value="${sucursal}" name="cxp[]"></td>
                        </tr>`);

                const select = $("#aplicaSelect-" + result);
                select.val(movimiento);

                result++;

                contadorArticulos++;
            }
        }

        $("#shTable5").DataTable().rows(".selected").deselect();

        jQuery("#cantidadArticulos").val(contadorArticulos);
    });

    $("#proveedorKey").change(function () {
        const isValid = validateMovimiento();

        let key = $(this).val() !== " " ? $(this).val() : false;

        if (!isValid) {
            if (key) {
                getClientesByClave(key);
            } else {
                jQuery("#select-proveedorFormaPago").val("");
                jQuery("#select-proveedorFormaPago").change();
            }
            // $("#anticiposKey").val("");
            // $("#referencia").val("");
        } else {
            showMessage(
                "No se ha seleccionado el proceso de la cuenta por cobrar",
                "error"
            );
        }
    });

    $("#importe").keyup(function () {
        const isValid = validateProveedor();

        if (isValid) {
            showMessage(
                "No se ha seleccionado el proveedor en el movimiento",
                "error"
            );
            jQuery("#importe").val("");
            jQuery("#importe").change();
        }
    });

    $("#cuentaModalVentana").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#aplica-Modal").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#cancelarFacturaModal").modal({
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

    $("#aplica-Modal").on("show.bs.modal", function (e) {
        let identificadorFila = $(e.relatedTarget).parent().parent().attr("id");
        $("#fila-id").val(identificadorFila);

        //agregar evento change a select
        $("#select-search-hided").change();
    });

    $("#radioWarning").on("click", function () {
        let tipo = jQuery("#select-movimiento").val();
        $.ajax({
            url: "/getFacturasCxC",
            type: "GET",
            data: {
                tipo: tipo,
            },
            success: function ({ facturas }) {
                //agregar las facturas al select
                const select = $("#select-facturaRelacion");
                select.empty();
                select.append(
                    `<option value="">Seleccione una opción</option>`
                );
                facturas.forEach((factura) => {
                    select.append(
                        `<option value="${factura.accountsReceivable_id}">${
                            factura.accountsReceivable_movement +
                            " " +
                            factura.accountsReceivable_movementID
                        }</option>`
                    );
                });
                // console.log(facturas);
            },
        });

        if ($("#radioWarning").is(":checked")) {
            $(".facturaRelacion").show();
        }

        // console.log("radioWarning");
    });

    $("#radioPrimary").on("click", function () {
        if ($("#radioPrimary").is(":checked")) {
            $(".facturaRelacion").hide();
        }
    });

    $("#facturaInfo-aceptar").on("click", function () {
        let tipo = jQuery("#tipoCuenta").val();
        let movimiento = jQuery("#select-movimiento").val();
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

        // afectarRequest("/comercial/ventas/afectar");
        if (
            (movimiento === "Anticipo Clientes" && estatus === "INICIAL") ||
            movimiento === "Cobro de Facturas" || movimiento === "Devolución de Anticipo"
        ) {
            if (tipo === "Banco") {
                afectarRequestBanco();
            } else {
                afectarRequestCaja();
            }
        } else if (movimiento === "Aplicación") {
            afectarRequestAplicacion();
        }
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

    $("#aplica-Modal").on("hide.bs.modal", function (e) {
        //Sacamos el total de los importes seleccionados
        let importeEncabezado = $("#importe").val();
        let importeEncabezadoFormato = currency(importeEncabezado, {
            separator: ",",
            precision: 2,
            symbol: "$",
        }).format();

        let importeTotal = totalCxpCambio();
        let importeFormato = currency(importeTotal, {
            separator: ",",
            precision: 2,
            symbol: "$",
        }).format();
        $totalCxp.val(importeFormato);

        const movimiento = $("#select-movimiento").val();

        if (movimiento === "Aplicación") {
            $("#importe").val(importeEncabezadoFormato);
        } else {
            $("#importe").val(importeFormato);
        }

        // if (movimiento === "Aplicación"){
        //     $("#importe").val("");
        // } else
        // {
        //     $("#importe").val(importeFormato);
        // }
    });

    $("#cuentaModalVentana").on("show.bs.modal", function (e) {
        const isInvalid = validateMovimiento();
        const isInvalid2 = validateFormapago();

        if (isInvalid) {
            showMessage(
                "No se ha seleccionado el proceso de la cuenta por cobrar",
                "error"
            );
            return false;
        } else if (isInvalid2) {
            showMessage(
                "No se ha seleccionado la forma de pago en el movimiento",
                "error"
            );
            return false;
        }
    });

    $("#proveedorModalVentana").on("show.bs.modal", function (e) {
        const isInvalid = validateMovimiento();

        if (isInvalid) {
            showMessage(
                "No se ha seleccionado el proceso de la cuenta por cobrar",
                "error"
            );
            return false;
        }
    });

    function getClientesByClave(clave) {
        let mov = $("#select-movimiento").val();
        $.ajax({
            url: "/gestion_finanzas/cuentas_por_cobrar/api/getCliente",
            type: "GET",
            data: {
                cliente: clave,
            },
            success: function (data) {
                console.log(data);
                if (Object.keys(data).length > 0) {
                    // if(mov != 'Factura'){
                    // $("#proveedorName").val(data.customers_name);
                    // } else {
                    //     $("#proveedorName").val(data.customers_businessName);
                    // }

                    $("#proveedorName").val(data.customers_businessName);
                    if (data.customers_identificationCFDI !== null) {
                        $("#select-basic-identificadorCFDI").val(
                            data.customers_identificationCFDI
                        );
                        $("#select-basic-identificadorCFDI").change();
                    }
                } else {
                    $("#proveedorName").val("");
                }

                $("#proveedorName").keyup();
            },
        });
    }

    function validateConceptoProveedor() {
        if (jQuery("#select-movimiento").val() !== "Factura de Gasto") {
            if (jQuery("#select-movimiento").val() !== "Solicitud Depósito") {
                const estadoConcepto =
                    $("#select-moduleConcept").val() === "" ? true : false;

                if (estadoConcepto) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    function validateProveedor() {
        const estadoConcepto = $("#proveedorKey").val() === "" ? true : false;

        if (estadoConcepto) {
            return true;
        } else {
            return false;
        }
    }

    function validateTable() {
        const estadoTable = $(".importe").val() === "" ? true : false;

        if (estadoTable) {
            return true;
        } else {
            return false;
        }
    }

    function validateFormapago() {
        const estadoForma =
            $("#select-proveedorFormaPago").val() === "" ? true : false;

        if (estadoForma) {
            return true;
        } else {
            return false;
        }
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

    function validateMovimiento() {
        const estadoMovimiento =
            $("#select-movimiento").val() === "" ? true : false;

        if (estadoMovimiento) {
            return true;
        } else {
            return false;
        }
    }

    function validateCuenta() {
        if (jQuery("#select-movimiento").val() !== "Factura de Gasto") {
            const estadoCuenta = $("#cuentaKey").val() === "" ? true : false;

            if (estadoCuenta) {
                return true;
            } else {
                return false;
            }
        }
    }

    function validateAnticipo() {
        if (jQuery("#select-movimiento").val() === "Aplicación") {
            const anticipoKey = $("#anticiposKey").val() === "" ? true : false;

            if (anticipoKey) {
                return true;
            } else {
                return false;
            }
        }
    }

     function validateCaja() {
        let isValid = false;
        let cuentaKey = $("#cuentaKey").val();
        

          $.ajax({
            url: "/getInfoCuentaBancaria",
              type: "GET",
              //hacemos que sea asincrona la peticion
                async: false,
            data: {
                cuentaKey: cuentaKey,
            },
            success: function ({ status, data }) {
                let balance = parseFloat(data.moneyAccountsBalance_balance);
                let cuenta = data.moneyAccountsBalance_accountType;
                console.log(balance, cuenta);
                if (balance <= 0 && cuenta === 'Caja') {
                    isValid = true;
                }
            },
        });

        return isValid;
    }

    function validateMismoImporte() {
        let isValid = false;
        let id = $("#keyAnticipo").val();

        $.ajax({
            url: "/getAnticipo",
            type: "GET",
            //hacemos que sea asincrona la peticion
            async: false,
            data: {
                proveedor: $("#proveedorKey").val(),
                id: id,
            },
            success: function ({ status, data }) {
                console.log(data);
                let importe = parseFloat(data.accountsReceivable_total);
                let balance = parseFloat(data.accountsReceivable_balance);
                console.log(importe, balance);
                if (importe !== balance) {
                    console.log("entro");
                    isValid = true;
                }
            },
        });

        return isValid;
    }


        // $.ajax({
        //     url: "/cxc/getAnticipos",
        //     type: "GET",
        //     async: false,
        //     data: {
        //         proveedor: $("#proveedorKey").val(),
        //         moneda: $("#select-search-hided").val(),
        //     },

        //     success: function ({ estatus, anticipos }) {
        //         console.log(anticipos);
        //     },
        // });
    





    function validateAplicas() {
        if (jQuery("#select-movimiento").val() === "Aplicación") {
            const aplicas = $("#inputDataCxp").val() === "" ? true : false;

            if (aplicas) {
                return true;
            } else {
                return false;
            }
        }
    }

    function validateAplica() {
        const estadoAplica = $(".botonesAplica").val() === "" ? true : false;

        if (estadoAplica) {
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

    function showMessage2(titulo, mensaje, icon, id = null) {
        swal({
            title: titulo,
            text: mensaje,
            icon: icon,
        }).then((willDelete) => {
            if (willDelete) {
                if (id != null || id == undefined) {
                    let ruta = window.location.href;
                    let ruta2 = ruta.split("/");
                    // console.log(ruta, ruta2);
                    if (ruta2.length > 6) {
                        ruta + "/" + id;
                    } else {
                        ruta += "/" + id;
                    }
                    setTimeout(function () {
                        window.location.href = ruta;
                    }, 1);
                }
            }
        });
    }

    //Acciones cuando cambie el movimiento
    const $movimientoSelect = $("#select-movimiento");
    const $inputTotal = $("#total-input");
    const $saldoAnticipoDIV = $("#saldoAnticipoDIV");

    $movimientoSelect.change(function () {
        if (
            $(this).val() === "Anticipo Clientes" ||
            $(this).val() === "" ||
            $(this).val() === "Factura"
        ) {
            //Escondemos la tabla en caso de que haya
            $inputTotal.hide();
            $saldoAnticipoDIV.show();
            $(".contenedoresImporte").show();
            $(".espacioContenedor").hide();
        } else {
            $inputTotal.show();
            $saldoAnticipoDIV.hide();
            $(".contenedoresImporte").hide();
            $(".espacioContenedor").show();
        }

        if ($(this).val() === "Solicitud Depósito") {
            $(".contenedoresImporte").show();
        }
    });

    //Calculamos el importe. iva e importe total
    const $importeDOM = $("#importe");
    const $impuestoDOM = $("#impuesto");
    const $importeTotalDOM = $("#importeTotal");

    $importeDOM.change(function () {
        if ($movimientoSelect.val() === "Anticipo Clientes") {
             let importe = parseFloat(
                 $importeDOM
                     .val()
                     .replace(/[$,]/g, "")
                     .replace(/[^0-9,.]/g, "")
                     .replace(/,/g, ".")
             );

             if (isNaN(importe)) {
                 importe = "";
                 $importeDOM.val(importe);
             } else {
                 $importeDOM.val(importe);
            }
            
            let impuestoPorImporte = importe * iva;
            let importeTotal = importe + impuestoPorImporte;

            let importeFormato = currency(importe, {
                separator: ",",
                precision: 2,
                symbol: "$",
            }).format();

            let impuestoFormato = currency(impuestoPorImporte, {
                separator: ",",
                precision: 2,
                symbol: "$",
            }).format();

            let importeTotalFormato = currency(importeTotal, {
                separator: ",",
                precision: 2,
                symbol: "$",
            }).format();

            //Mostramos el formato de moneda
            $importeDOM.val(importeFormato);
            $impuestoDOM.val(impuestoFormato);
            $importeTotalDOM.val(importeTotalFormato);
        }

        if ($movimientoSelect.val() === "Aplicación") {
             let importe = parseFloat(
                 $importeDOM
                     .val()
                     .replace(/[$,]/g, "")
                     .replace(/[^0-9,.]/g, "")
                     .replace(/,/g, ".")
             );

             if (isNaN(importe)) {
                 importe = "";
                 $importeDOM.val(importe);
             } else {
                 $importeDOM.val(importe);
             }

            let importeTotal = importe;

            let importeFormato = currency(importe, {
                separator: ",",
                precision: 2,
                symbol: "$",
            }).format();

            let importeTotalFormato = currency(importeTotal, {
                separator: ",",
                precision: 2,
                symbol: "",
            }).format();

            //Mostramos el formato de moneda
            $importeDOM.val(importeFormato);
            $importeTotalDOM.val(importeTotalFormato);
        }

        if ($movimientoSelect.val() === "Cobro de Facturas") {
            let importe = parseFloat(
                $importeDOM
                    .val()
                    .replace(/[$,]/g, "")
                    .replace(/[^0-9,.]/g, "")
                    .replace(/,/g, ".")
            );

            if (isNaN(importe)) {
                importe = "";
                $importeDOM.val(importe);
            } else {
                $importeDOM.val(importe);
            }

           let importeFormato = currency(importe, {
               separator: ",",
               precision: 2,
               symbol: "$",
           }).format();

           //Mostramos el formato de moneda
           $importeDOM.val(importeFormato);
       }
    });

    $impuestoDOM.change(function () {
        let impuesto = parseFloat(
            $impuestoDOM.val().replace(/['$', ',']/g, "")
        );

        if (isNaN(impuesto)) {
            let importe = $importeDOM.val();
            $importeTotalDOM.val(importe);
        }
    });

    //Obtenemos el saldo del proveedor seleccionado
    const $proveedorKey = $("#proveedorKey");
    const $saldoProveedor = $("#saldoProveedor");

    $proveedorKey.change(async function () {
        let isConceptoEmpty = validateConceptoProveedor();
        let claveProveedor = $proveedorKey.val();

        if (!isConceptoEmpty) {
            if (claveProveedor !== "") {
                await $.ajax({
                    url: "/cxc/saldo/cliente/",
                    method: "GET",
                    data: {
                        cliente: claveProveedor,
                        moneda: $("#select-search-hided").val(),
                    },
                    success: function ({ status, saldo }) {
                        if (status) {
                            if (saldo !== null) {
                                let saldoFormato = currency(
                                    saldo.balance_balance,
                                    {
                                        precision: 2,
                                        separator: ",",
                                    }
                                ).format();
                                $saldoProveedor.val(saldoFormato);
                            } else {
                                $saldoProveedor.val("");
                            }
                        }
                    },
                });
            }
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

    $("#modalAfectar").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#modalAfectar2").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });
    
    const genero = obtenerGeneroDelMovimiento();
    let articulo = "un";
    if (genero === "F") {
        articulo = "una";
    }
    const afectar = (e) => {
        e.preventDefault();
        let movimiento = jQuery("#select-movimiento").val();
        let estatus = jQuery("#status").text().trim();
        let tipo = jQuery("#tipoCuenta").val();
        if (
            (movimiento === "Anticipo Clientes" && estatus === "INICIAL") ||
            movimiento === "Cobro de Facturas"
        ) {
            const movimientoSe = validateMovimiento();
            const conceptoProveedor = validateConceptoProveedor();
            const proveedor = validateProveedor();
            const formaPago = validateFormapago();
            const cuenta = validateCuenta();
            let isMismoImporte = validarImportesTotales();
            let isVacio = validarCxpVacios();
            let importe = validateImporte();
            let isMismaMoneda = validarMonedas();
            let validarCFD = validarCFDI();

            if (movimiento === "Anticipo Clientes") {
                isMismoImporte = true;
                isVacio = false;
            }

            if (
                movimientoSe ||
                conceptoProveedor ||
                proveedor ||
                formaPago ||
                cuenta ||
                !isMismoImporte ||
                isVacio ||
                importe ||
                !isMismaMoneda ||
                validarCFD
            ) {
                showMessage(
                    `No se ha seleccionado ${
                        movimientoSe
                            ? "un movimiento"
                            : conceptoProveedor
                            ? "un concepto"
                            : proveedor
                            ? "un proveedor"
                            : formaPago
                            ? "una forma de pago"
                            : cuenta
                            ? "una cuenta"
                            : !isMismoImporte
                            ? "un importe con respecto al detalle"
                            : isVacio
                            ? "un importe con respecto al detalle"
                            : importe
                            ? "un importe"
                            : !isMismaMoneda
                            ? "la cuenta con la misma moneda"
                            : validarCFD
                            ? "un CFDI"
                            : ""
                    }`,
                    "error"
                );
            } else {
                $("#facturaModal").modal({
                    backdrop: "static",
                    keyboard: true,
                    show: true,
                });
                // if (tipo === "Banco") {
                //     afectarRequestBanco();
                // } else {
                //     afectarRequestCaja();
                // }
            }
        } else if (movimiento === "Anticipo Clientes" && estatus === "POR AUTORIZAR") {
            let folio = $("#folioMov").val();
            $("#exampleModalCenterTitle2").html("Módulo - CXC");
            $("#modalAfectar2").modal("show");
        }

        if (movimiento === "Factura") {
            let folio = $("#folioMov").val();
            $("#exampleModalCenterTitle").html("Módulo - CXC");

            $("#modalAfectar").modal("show");
        }

        let mov = $("#select-movimiento").val();
        let folio = $("#folioMov").val();

        if (mov === "Cobro de Facturas") {
            $("#exampleModalLongTitle2").html("Tipo de Cobro");
            $("#labelCXCNormal").html("Cobro Normal");
            $("#labelCXCRelacionado").html("Cobro Relacionado");
            $("#labelCXCRelacion").html("Cobro Relación");
        } else if (mov === "Anticipo Clientes") {
            $("#exampleModalLongTitle2").html("Tipo de Anticipo");
            $("#labelCXCNormal").html("Anticipo Normal");
            $("#labelCXCRelacionado").html("Anticipo Relacionado");
            $("#labelCXCRelacion").html("Anticipo Relación");
        } else if (mov === "Aplicación") {
            $("#exampleModalLongTitle2").html("Tipo de Aplicación");
            $("#labelCXCNormal").html("Aplicación Normal");
            $("#labelCXCRelacionado").html("Aplicación Relacionado");
            $("#labelCXCRelacion").html("Aplicación Relación");
        } else if (mov === "Devolución de Anticipo") {
            $("#exampleModalLongTitle2").html("Tipo de Devolución");
            $("#labelCXCNormal").html("Devolución Normal");
            $("#labelCXCRelacionado").html("Devolución Relacionado");
            $("#labelCXCRelacion").html("Devolución Relación");
        }

        //suma

        if (movimiento === "Aplicación") {
            const movimientoSe = validateMovimiento();
            const conceptoProveedor = validateConceptoProveedor();
            const proveedor = validateProveedor();
            const formaPago = validateFormapago();
            const cuenta = validateCuenta();
            let table = validateTable();
            let anticipo = validateAnticipo();
            let isMismoImporte = validarImportesTotales();
            let isVacio = validarCxpVacios();
            let isMismaMoneda = validarMonedas();
            if (movimiento === "Anticipo Clientes") {
                isMismoImporte = true;
            }

            if (
                movimientoSe ||
                conceptoProveedor ||
                proveedor ||
                formaPago ||
                cuenta ||
                !isMismoImporte ||
                anticipo ||
                isVacio ||
                !isMismaMoneda
            ) {
                showMessage(
                    `No se ha seleccionado ${
                        movimientoSe
                            ? "un movimiento"
                            : conceptoProveedor
                            ? "un concepto"
                            : proveedor
                            ? "un proveedor"
                            : formaPago
                            ? "una forma de pago"
                            : cuenta
                            ? "una cuenta"
                            : !isMismoImporte
                            ? "un importe con respecto al detalle"
                            : anticipo
                            ? "el movimiento a aplicar"
                            : isVacio
                            ? "un importe con respecto al detalle"
                            : !isMismaMoneda
                            ? "la cuenta con la misma moneda"
                            : ""
                    }`,
                    "error"
                );
            } else {
                $("#facturaModal").modal({
                    backdrop: "static",
                    keyboard: true,
                    show: true,
                });
            }
        }

        //ahora hacemos para el movimiento Devolución de Anticipo
        if (movimiento === "Devolución de Anticipo") {
            const movimientoSe = validateMovimiento();
            const conceptoProveedor = validateConceptoProveedor();
            const proveedor = validateProveedor();
            const formaPago = validateFormapago();
            const cuenta = validateCuenta();
            let table = validateTable();
            let anticipo = validateAnticipo();
            let dineroCaja = validateCaja();
            let mismoImporte = validateMismoImporte();
            // let validateMismoImporte = validateMismoImporte();
            // console.log(validateMismoImporte);
            let isMismoImporte = validarImportesTotales();
            let isMismaMoneda = validarMonedas();
            // if (movimiento === "Devolución de Anticipo") {
            //     isMismoImporte = true;
            // }

            if (
                movimientoSe ||
                conceptoProveedor ||
                proveedor ||
                formaPago ||
                cuenta ||
                !isMismoImporte ||
                anticipo ||
                !isMismaMoneda
            ) {
                showMessage(
                    `No se ha seleccionado ${
                        movimientoSe
                            ? "un movimiento"
                            : conceptoProveedor
                            ? "un concepto"
                            : proveedor
                            ? "un proveedor"
                            : formaPago
                            ? "una forma de pago"
                            : cuenta
                            ? "una cuenta"
                            : !isMismoImporte
                            ? "un importe con respecto al detalle"
                            : anticipo
                            ? "el movimiento a devolver"
                            : !isMismaMoneda
                            ? "la cuenta con la misma moneda"
                            : ""
                    }`,
                    "error"
                );
            } 
            else if (dineroCaja) {
                showMessage(
                    `No se puede afectar la cuenta por cobrar porque no hay dinero en caja`,
                    "error"
                );
            } else if (mismoImporte) {
                showMessage(
                    `No se puede afectar la cuenta por cobrar porque el importe del anticipo no es igual al saldo`,
                    "error"
                );
            }
            else {
                $("#facturaModal").modal({
                    backdrop: "static",
                    keyboard: true,
                    show: true,
                });
            }
        }
    }; //Fin de afectar

    function obtenerGeneroDelMovimiento() {
        const movimiento = $("#select-movimiento").val();

        //si el movimiento es diferente de aplicación el genero es F
        if (movimiento !== "Aplicación") {
            return "M";
        } else {
            return "F";
        }
    }

    $("#afectar-boton").click((evento) => afectar(evento));

    if (movimientoSelected !== "") {
        $("#proveedorKey").trigger("change");
    }

    function afectarRequestBanco() {
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
                let cxpJson = jsonCxpDetails();

                //Mostramos el loader
                $("#loader").show();
                $.ajax({
                    type: "POST",
                    url: "/gestion_finanzas/cuentas_por_cobrar/afectar",
                    data: $("#progressWizard").serialize(),
                    success: function ({ estatus, mensaje, id, cheque }) {
                        $("#loader").hide();
                        if (estatus === 200) {
                            showMessage2(
                                "Se generó automaticamente.",
                                "Proceso: " +
                                    cheque.treasuries_movement +
                                    " " +
                                    cheque.treasuries_movementID,
                                "info",
                                id
                            );
                        } else {
                            showMessage2("Error", mensaje, "error", id);
                        }
                    },
                });
            }
        });
    }

    function afectarRequestCaja() {
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
                jsonCxpDetails();
                $("#loader").show();
                $.ajax({
                    url: "/gestion_finanzas/cuentas_por_cobrar/afectar",
                    type: "POST",
                    data: $("#progressWizard").serialize(),
                    success: function ({ estatus, mensaje, id, egreso }) {
                        $("#loader").hide();
                        if (estatus === 200) {
                            showMessage2(
                                "Se generó automaticamente.",
                                "Proceso: " +
                                    egreso.treasuries_movement +
                                    " " +
                                    egreso.treasuries_movementID,
                                "info",
                                id
                            );
                        } else {
                            showMessage2("Error", mensaje, "error", id);
                        }
                    },
                });
            }
        });
    }

    function afectarRequestAplicacion() {
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
                jsonCxpDetails();
                $("#loader").show();
                $.ajax({
                    url: "/gestion_finanzas/cuentas_por_cobrar/afectar",
                    type: "POST",
                    data: $("#progressWizard").serialize(),
                    success: function ({ estatus, mensaje, id }) {
                        $("#loader").hide();
                        if (estatus === 200) {
                            showMessage2(
                                "La Aplicación se ha creado correctamente.",
                                "",
                                "success",
                                id
                            );
                        } else {
                            showMessage2("Error", mensaje, "error", id);
                        }
                    },
                });
            }
        });
    }

    function afectarRequestDevolucion() {
        swal({
            title: "¿Está seguro que desea generar " + articulo + " " + movimiento + "?",
            text: "¡Con el nuevo estatus diferente a INICIAL no podrá realizar cambios!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
            buttons: ["Cancelar", "Aceptar"],
        }).then((willDelete) => {
            if (willDelete) {
                jsonCxpDetails();
                $("#loader").show();
                $.ajax({
                    url: "/gestion_finanzas/cuentas_por_cobrar/afectar",
                    type: "POST",
                    data: $("#progressWizard").serialize(),
                    success: function ({ estatus, mensaje, id }) {
                        $("#loader").hide();
                        if (estatus === 200) {
                            showMessage2(
                                "La Devolución de Anticipo se ha creado correctamente.",
                                "",
                                "success",
                                id
                            );
                        } else {
                            showMessage2("Error", mensaje, "error", id);
                        }
                    },
                });
            }
        });
    }

    $("#btn-modal-cancelarFactura").click(async function () {
        let isMotivoVacio = validarMotivoCancelacion();
        let isFolioSustitucionVacio = validarFolioSustitucion();
        const id = $("#idCXP").val();
        let status = $("#status").text().trim();
        let movimiento = $("#select-movimiento").val();
        let folio = $("#folioMov").val();
        const tipo = $("#tipoCuenta").val();
        const timbrado = $("#timbradoKey").val();
        const empresaImpuesto = $("#empresaImpuesto").val();
        //se valida el motivo de cancelacion y el folio de sustitucion nada más se valida si el motivo de cancelacion es diferente a 01

        if (movimiento === "Cobro de Facturas") {
            $("#exampleModalLongTitle3").html("Cancelar Cobro de Facturas");
        } else if (movimiento === "Anticipo Clientes") {
            $("#exampleModalLongTitle3").html("Cancelar Anticipo");
        } else if (movimiento === "Aplicación") {
            $("#exampleModalLongTitle3").html("Cancelar Aplicación");
        }

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
                title: "¿Estás seguro de cancelar?",
                text: "Se cancelará el movimiento: " + movimiento + " " + folio,
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
                    // $("#loader").show();
                    $.ajax({
                        url: "/cancelarCxC/",
                        type: "GET",
                        data: {
                            id,
                            movimiento,
                            folio,
                            tipo,
                            inputJsonCancelacionFactura:
                                JSON.stringify(jsonCancelacion),
                        },
                        success: function ({ estatus, mensaje, id }) {
                            if (estatus === 200) {
                                showMessage2(mensaje, "", "success", id);
                                $("#cancelarFacturaModal").hide();
                                // $("#loader").hide();
                            } else if (estatus === 400) {
                                showMessage2("Precaución", mensaje, "warning");
                            } else {
                                showMessage2("Error", mensaje, "error");
                                $("#cancelarFacturaModal").hide();
                                // $("#loader").hide();
                            }
                        },
                    });
                }
            });
        }
    });

    function validarMonedas() {
        const moneda = $("#select-search-hided").val();
        const cuentaMoneda = $("#tipoCambio").val();

        if (moneda === cuentaMoneda) {
            return true;
        }

        return false;
    }

    function validarCFDI() {
        let cfdi = $("#select-basic-identificadorCFDI").val();

        if (cfdi === "") {
            return true;
        }

        return false;
    }

    const copiar = function (e) {
        e.preventDefault();
        //Enviamos el formulario para copiar la compra
        const form = jQuery("#progressWizard");
        const inputCopiar =
            '<input type="text" name="copiar" value="copiar" readonly>';
        form.append(inputCopiar);

        $('input[id^="id-"]').each(function (index, value) {
            $(this).remove();
        });
        $("#idCXP").val("0");

        form.submit();
    };

    let movimiento = jQuery("#select-movimiento");
    console.log(movimiento);

    if (
        movimiento.val() === "Solicitud Depósito" ||
        movimiento.val() === "Factura"
    ) {
        jQuery("#copiar-compra").unbind("click");
        jQuery("#copiar-compra").hide();
    } else {
        jQuery("#copiar-compra").click(copiar);
        jQuery("#copiar-compra").show();
    }

    const cancelar = function (e) {
        e.preventDefault();

        let status = $("#status").text().trim();
        let movimiento = $("#select-movimiento").val();
        const folio = $("#folioMov").val();
        const id = $("#idCXP").val();
        const tipo = $("#tipoCuenta").val();
        const timbrado = $("#timbradoKey").val();
        const empresaImpuesto = $("#empresaImpuesto").val();

        if (movimiento === "Cobro de Facturas") {
            $("#exampleModalLongTitle3").html("Cancelar Cobro de Facturas");
        } else if (movimiento === "Anticipo Clientes") {
            $("#exampleModalLongTitle3").html("Cancelar Anticipo");
        } else if (movimiento === "Aplicación") {
            $("#exampleModalLongTitle3").html("Cancelar Aplicación");
        } else if (movimiento === "Devolución de Anticipo") {
            $("#exampleModalLongTitle3").html("Cancelar Devolución");
        }

        if (
            (status === "FINALIZADO" &&
                movimiento === "Aplicación" &&
                timbrado === "0") ||
            (timbrado === null && empresaImpuesto === 1) ||
            empresaImpuesto === "1" ||
            (status === "FINALIZADO" &&
                movimiento === "Cobro de Facturas" &&
                timbrado === "0") ||
            (timbrado === null && empresaImpuesto === 1) ||
            empresaImpuesto === "1" ||
            (status === "POR AUTORIZAR" &&
                movimiento === "Anticipo Clientes" &&
                timbrado === "0") ||
            (timbrado === null && empresaImpuesto === 1) ||
            empresaImpuesto === "1" || (status === "FINALIZADO" &&
                movimiento === "Devolución de Anticipo" &&
                timbrado === "0")
        ) {
            swal({
                title: "¿Estás seguro de cancelar?",
                text: "Se cancelará el movimiento: " + movimiento + " " + folio,
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "/cancelarCxC/",
                        type: "GET",
                        data: {
                            id,
                            movimiento,
                            folio,
                            tipo,
                        },
                        success: function ({ estatus, mensaje, id }) {
                            if (estatus === 200) {
                                showMessage2(mensaje, "", "success", id);
                            } else if (estatus === 400) {
                                showMessage2("Precaución", mensaje, "warning");
                            } else {
                                showMessage2("Error", mensaje, "error");
                            }
                        },
                    });
                }
            });
        } else if (
            (status === "FINALIZADO" && movimiento === "Aplicación") ||
            (movimiento === "Cobro de Facturas" &&
                timbrado === "1" &&
                empresaImpuesto === "0") ||
            empresaImpuesto === 0
        ) {
            $("#cancelarFacturaModal").modal({
                backdrop: "static",
                keyboard: true,
                show: true,
            });
        } else if (
            (status === "POR AUTORIZAR" &&
                movimiento === "Anticipo Clientes" &&
                timbrado === "1" &&
                empresaImpuesto === "0") ||
            empresaImpuesto === 0
        ) {
            $("#cancelarFacturaModal").modal({
                backdrop: "static",
                keyboard: true,
                show: true,
            });
        } else if (
            (status === "FINALIZADO" &&
                movimiento === "Devolución de Anticipo" &&
                timbrado === "1" &&
                empresaImpuesto === "0") ||
            empresaImpuesto === 0
        ) {
            $("#cancelarFacturaModal").modal({
                backdrop: "static",
                keyboard: true,
                show: true,
            });
        } 

        if (movimiento == "Anticipo Clientes" && estatus == "FINALIZADO") {
            swal({
                title: "¿Estás seguro de cancelar?",
                text: "Se cancelará el movimiento: " + movimiento + " " + folio,
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                showMessage2(
                    "Precaución",
                    "No se puede cancelar un anticipo que ya se encuentra aplicado o en su defecto, que ya tenga una devolución de anticipo.",
                    "warning"
                );
            });
        }

        if (status == "POR AUTORIZAR" && movimiento == "Sol. de Cheque/Transferencia") {
            swal({
                title: "¿Estás seguro de cancelar?",
                text: "Se cancelará el movimiento: " + movimiento + " " + folio,
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                showMessage2(
                    "Precaución",
                    "No se puede cancelar una Sol. de Cheque/Transferencia pendiente directamente.",
                    "warning"
                );
            });
        }

        if (status == "POR AUTORIZAR" && movimiento == "Solicitud Depósito") {
            swal({
                title: "¿Estás seguro de cancelar?",
                text: "Se cancelará el movimiento: " + movimiento + " " + folio,
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                showMessage2(
                    "Precaución",
                    "No se puede cancelar una solicitud de depósito pendiente directamente.",
                    "warning"
                );
            });
        }

        if (movimiento == "Factura") {
            swal({
                title: "¿Estás seguro de cancelar?",
                text: "Se cancelará el movimiento: " + movimiento + " " + folio,
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                showMessage2(
                    "Precaución",
                    "El origen del movimiento es de otro módulo. No se puede cancelar directamente.",
                    "warning"
                );
            });
        }

        // console.log(status, movimiento, folio, id);
    };

    const eliminar = function (e) {
        e.preventDefault();

        const id = $("#idCXP").val();

        if (id === "0") {
            showMessage("No se ha seleccionado ningun movimiento", "error");
        } else {
            swal({
                title: "¿Está seguro de eliminar el movimiento?",
                text: "Una vez eliminado no podrá recuperarlo",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "/eliminar/cxc",
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
                                        "/gestion_finanzas/cuentas_por_cobrar/create";
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

    $("#btn-modal-afectar").on("click", function (e) {
        e.preventDefault();
        let pagoRadio = $("#pagoRadio").is(":checked");

        if (pagoRadio === true) {
            // $('#select-movimiento').val('Cheque');
            // $('#status').val('INICIAL');
            $("#impuesto").val(0.0);
            $("#origin").val("Cobro de Facturas");
            $("#modalAfectar").modal("hide");

            //obtenemos el valor del origin
            let mov = $("#origin").val();

            //Verificamos si el usuario tiene permisos para afectar
            let $domPermiso = mov.replace(/\s/g, "");

            if ($("#" + $domPermiso).val() !== "true") {
                showMessage2(
                    "Permisos Insuficientes",
                    "No tienes permisos para afectar",
                    "warning"
                );
            } else {
                let saldo = $("#saldoAnticipo").val();
                $("#importe").val(saldo);
                $("#importeTotal").val(saldo);
                $("#progressWizard").submit();
            }
        }
    });

    $("#btn-modal-afectar2").on("click", function (e) {
        e.preventDefault();
        let aplicacionRadio = $("#aplicacionRadio").is(":checked");
        let devolucionRadio = $("#devolucionRadio").is(":checked");

        if (aplicacionRadio === true) {
            // // $('#select-movimiento').val('Cheque');
            // // $('#status').val('INICIAL');
            $("#origin").val("Aplicación");
            $("#modalAfectar").modal("hide");
            //obtenemos el valor del origin
            let mov = $("#origin").val();
            console.log(mov);

            //Verificamos si el usuario tiene permisos para afectar
            let $domPermiso = mov.replace(/\s/g, "");

            if ($("#" + $domPermiso).val() !== "true") {
                showMessage2(
                    "Permisos Insuficientes",
                    "No tienes permisos para afectar",
                    "warning"
                );
            } else {
                let saldo = $("#saldoAnticipo").val();
                $("#importe").val(saldo);
                $("#importeTotal").val(saldo);
                $("#progressWizard").submit();
            }
        }

        if (devolucionRadio === true) {
            let importeTotal = $("#importeTotal").val();
            let saldo = $("#saldoAnticipo").val();
            console.log(importeTotal, saldo);
            $("#origin").val("Devolución de Anticipo");
            $("#modalAfectar").modal("hide");
            //obtenemos el valor del origin
            let mov = $("#origin").val();
            console.log(mov);
            
            let $domPermiso = mov.replace(/\s/g, "");
            console.log($("#" + $domPermiso).val());

            //si importe total es diferente a saldo no se puede afectar
            if (importeTotal != saldo) {
                showMessage2(
                    "Error",
                    "El importe total debe ser igual al saldo",
                    "error"
                );
            } else if ($("#" + $domPermiso).val() !== "true") {
                showMessage2(
                    "Permisos Insuficientes",
                    "No tienes permisos para afectar",
                    "warning"
                );
            } else {
                $("#importe").val(saldo);
                $("#importeTotal").val(saldo);
                $("#progressWizard").submit();
            }
        
        }
    });

    let estatus = jQuery("#status").text().trim();
    if (estatus === "CANCELADO" || estatus === "INICIAL") {
        jQuery("#cancelar-boton").unbind("click");
        jQuery("#cancelar-boton").hide();
    } else {
        jQuery("#cancelar-boton").click(cancelar);
        jQuery("#cancelar-boton").show();
    }

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

    //Validamos que no se repita el aplica y el Aplica Consecutivo
    function validarConsecutivoRepetido() {
        let aplicaConsecutivo = {};
        let contador = 0;

        $('input[name="cxp[]"]').each(function (index, elemento) {
            let posicionFila = $(elemento).attr("id").split("-");
            if ($(elemento).attr("id") !== "movId-" + posicionFila[1]) {
                let selectAplicaID = $(elemento).attr("id").split("-")[1];
                let selectAplicaValue = $(
                    "#aplicaSelect-" + selectAplicaID
                ).val();

                aplicaConsecutivo = {
                    ...aplicaConsecutivo,
                    [selectAplicaValue +
                    "-" +
                    $("#aplicaC-" + selectAplicaID).val()]: {
                        consecutivoID: $("#aplicaC-" + selectAplicaID).val(),
                    },
                };
            } else {
                contador++;
            }
        });
        if (Object.keys(aplicaConsecutivo).length === contador) {
            return false;
        } else {
            return true;
        }
    }

    //Validamos que el aplica y el aplica consecutivo no esten vacios

    function validarCxpVacios() {
        let aplicaConseVacio = false;
        let importeVacio = false;

        if ($('input[name="cxp[]"]').length === 0) {
            aplicaConseVacio = true;
            importeVacio = true;
        } else {
            $('input[name="cxp[]"]').each(function (index, elemento) {
                if ($(elemento).attr("id") !== "movId") {
                    let selectAplicaID = $(elemento).attr("id").split("-")[1];
                    aplicaConseVacio =
                        $("#aplicaC-" + selectAplicaID).val() === ""
                            ? true
                            : false;
                    importeVacio =
                        $("#importe-" + selectAplicaID).val() === ""
                            ? true
                            : false;
                }

                if (aplicaConseVacio || importeVacio) {
                    return false;
                }
            });
        }

        if (aplicaConseVacio || importeVacio) {
            return true;
        } else {
            return false;
        }
    }

    $btnImporte.click(() => {
        let importeTotal = $totalCxp.val().replace(/[$,]/g, "");
        if (importeTotal != "") {
            $importeDOM.val(importeTotal);
            $importeDOM.change();
        }
    });

    function jsonCxpDetails() {
        let cxpListaDetails = {};
        let key = "";
        const inputSaveArticulo = $("#inputDataCxp");

        $('input[name="cxp[]"]').each(function (index, value) {
            let idIntputs = $(value).attr("id");
            let cxpMovId = idIntputs.split("-")[0];

            if (cxpMovId === "movId") {
                let posicion = idIntputs.split("-")[1];
                let aplica = $("#aplicaSelect-" + posicion).val();
                key = aplica;
            } else {
                let posicionFila = idIntputs.split("-")[1];
                let id = $("#idDetalle-" + posicionFila).val();
                cxpListaDetails = {
                    ...cxpListaDetails,
                    [key + "-" + posicionFila]: {
                        id: id,
                        movID: $("#movId-" + posicionFila).val(),
                        aplicaSelect: $("#aplicaSelect-" + posicionFila).val(),
                        aplicaConsecutivo: $("#aplicaC-" + posicionFila).val(),
                        importe: $("#importe-" + posicionFila)
                            .val()
                            .replace(/['$',',']/g, ""),
                        diferencia: $("#diferencia-" + posicionFila)
                            .val()
                            .replace(/[$,]/g, ""),
                        porcentaje: $("#porcentaje-" + posicionFila)
                            .val()
                            .replace(/,/g, ""),
                        totalFila: $("#importeT-" + posicionFila)
                            .val()
                            .replace(/,/g, ""),
                        Total: $("#totalCompleto").val().replace(/[$,]/g, ""),
                        Sucursal: $("#sucursal-" + posicionFila).val(),
                    },
                };
            }
        });

        inputSaveArticulo.attr("name", "dataArticulosJson");
        inputSaveArticulo.attr("value", JSON.stringify(cxpListaDetails));

        return cxpListaDetails;
    }

    //ahora hacemos que cuando sea devolución de anticipo totalCompleto se ponga en lo que tiene el anticipo seleccionado




    function validarImportesTotales() {
        let $importeTotalCxp = parseFloat(
            $("#totalCompleto").val().replace(/[$,]/g, "")
        );
        let $importeForm = parseFloat($("#importe").val().replace(/[$,]/g, ""));

        if ($importeTotalCxp === $importeForm) {
            return true;
        }
        return false;
    }

    function validacionesForm(form) {
        let isAplicasRepetidos = validarConsecutivoRepetido(); //Validar que no se repita el consecutivo cuando es el mismo aplica
        let isVacio = validarCxpVacios(); //Valida que no haya importes vacios antes de enviar el formulario
        let jsonCxp = jsonCxpDetails(); //Forma el json de los datos de la cxpDetails

        let isMismoImporte = true;

        if (Object.keys(jsonCxp).length > 0) {
            isMismoImporte = validarImportesTotales();
        } else {
            isMismoImporte = true;
            isVacio = false;
        }

        if (isAplicasRepetidos || isVacio || !isMismoImporte) {
            showMessage(
                `No se ${
                    isAplicasRepetidos
                        ? "permiten cuentas por pagar con el mismo folio del movimiento"
                        : isVacio
                        ? "permiten importes vacios"
                        : !isMismoImporte
                        ? "permiten importes diferentes"
                        : ""
                }`,
                "error"
            );
        }

        if (!isVacio && !isAplicasRepetidos && isMismoImporte) {
            form.submit();
        }
    }

    $("#select-moduleConcept").change(function () {
        let key =
            $("#proveedorKey").val() !== " " ? $("#proveedorKey").val() : false;

        if (key) {
            getClientesByClave(key);
        } else {
            jQuery("#select-proveedorFormaPago").val("");
            jQuery("#select-proveedorFormaPago").change();
        }
    });

    function validateImporte() {
        const importe = $("#importe").val();
        if (importe === "" || importe == "0" || importe == "0.00") {
            return true;
        } else {
            return false;
        }
    }
    $movimientoSelect.change();
    disabledPendiente();

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

    //Aqui comienza el flujo
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

    if (
        movimientoSelected === "Anticipo Clientes" ||
        movimientoSelected === "Aplicación" ||
        movimientoSelected === "Pago de Facturas"
    ) {
        if (movimientoSelected !== "") {
            let movimientoSinEspacios = movimientoSelected.replace(/\s+/g, "");

            if ($("#" + movimientoSinEspacios).length === 0) {
                $("#afectar-boton").hide();
                $("#cancelar-boton").hide();
                $("#botonForm").hide();
            }
        }
    }

    if (
        movimientoSelected === "Anticipo Clientes" &&
        ($("#status").text().trim() === "FINALIZADO" ||
            $("#status").text().trim() === "POR AUTORIZAR")
    ) {
        $("#timbrado").show();
    } else if ($("#status").text().trim() === "FINALIZADO") {
        $("#timbrado").show();
    } else {
        $("#timbrado").hide();
    }

    $("#timbrado").click((e) => {
        e.preventDefault();

        let mov = $("#select-movimiento").val();
        let folio = $("#folioMov").val();

        if (mov === "Cobro de Facturas") {
            $("#exampleModalLongTitle2").html("Tipo de Cobro");
            $("#labelCXCNormal").html("Cobro Normal");
            $("#labelCXCRelacionado").html("Cobro Relacionado");
            $("#labelCXCRelacion").html("Cobro Relación");
        } else if (mov === "Anticipo Clientes") {
            $("#exampleModalLongTitle2").html("Tipo de Anticipo");
            $("#labelCXCNormal").html("Anticipo Normal");
            $("#labelCXCRelacionado").html("Anticipo Relacionado");
            $("#labelCXCRelacion").html("Anticipo Relación");
        } else if (mov === "Aplicación") {
            $("#exampleModalLongTitle2").html("Tipo de Aplicación");
            $("#labelCXCNormal").html("Aplicación Normal");
            $("#labelCXCRelacionado").html("Aplicación Relacionado");
            $("#labelCXCRelacion").html("Aplicación Relación");
        } else if (mov === "Devolución de Anticipo") {
            $("#exampleModalLongTitle2").html("Tipo de Devolución");
            $("#labelCXCNormal").html("Devolución Normal");
            $("#labelCXCRelacionado").html("Devolución Relacionado");
            $("#labelCXCRelacion").html("Devolución Relación");
        }

        $("#facturaModal").modal({
            backdrop: "static",
            keyboard: true,
            show: true,
        });

        $("#facturaInfo-aceptar").hide();
        $("#facturaInfo-aceptar2").show();
    });

    $("input[id^='importe-']").trigger("change");
}); //Fin del Jquery;

function requestTimbrado() {
    $("#loader").show();
    $.ajax({
        url: "/afectarTimbradoCxc",
        method: "GET",
        data: {
            cxc: $("#idCXP").val(),
            dataFacturaInfo: $("#dataFacturaInfo").val(),
        },
        success: function ({ status, data }) {
            if (status) {
                showMessage(data, "success");
                let ruta = window.location.href;

                setTimeout(function () {
                    window.location.href = ruta;
                }, 1000);
            } else {
                showMessage(data, "error");
            }
            $("#loader").hide();
        },
    });
    $("#facturaModal").modal("hide");
}

function buscarMov(clave, posicion) {
    // console.log(clave, posicion);
    let selectChange = $("#aplicaSelect-" + posicion);

    //buscamos la fila con el id
    let fila = $("#" + clave + "-" + posicion);

    let id = selectChange.attr("id").split("-")[1];
    $("#aplicaC-" + id).val("");
    $("#importe-" + id).val("");

    $("#agregarAplicaModal-" + id).on("click", function (event) {
        event.preventDefault();
        jQuery("#shTable5").DataTable().clear().draw();

        let mov = selectChange.val();
        proveedor = $("#proveedorKey").val();
        if (mov !== "") {
            $.ajax({
                url: "/aplicaFolio/cxc",
                method: "GET",
                data: {
                    proveedor: proveedor,
                    movimiento: mov,
                    moneda: $("#select-search-hided").val(),
                },
                success: function ({ estatus, dataProveedor }) {
                    if (estatus === 200) {
                        if (dataProveedor.length > 0) {
                            tableCXPP.clear().draw();
                            dataProveedor.forEach((element) => {
                                let fecha =
                                    (element.accountsReceivable_expiration =
                                        moment(
                                            element.accountsReceivable_expiration
                                        ).format("YYYY-MM-DD"));
                                let importe = currency(
                                    element.accountsReceivable_total,
                                    {
                                        separator: ",",
                                        precision: 2,
                                        decimal: ".",
                                        symbol: "",
                                    }
                                ).format();

                                let saldo = currency(
                                    element.accountsReceivable_balance,
                                    {
                                        separator: ",",
                                        precision: 2,
                                        symbol: "",
                                    }
                                ).format();

                                jQuery("#shTable5")
                                    .DataTable()
                                    .row.add([
                                        element.accountsReceivable_id,
                                        element.accountsReceivable_movement,
                                        element.accountsReceivable_movementID,
                                        fecha,
                                        importe,
                                        saldo,
                                        element.accountsReceivable_money,
                                        element.accountsReceivable_branchOffice,
                                    ])
                                    .draw();
                            });
                        } else {
                            jQuery("#shTable5").DataTable().clear().draw();
                        }
                    }
                },
            });
        }
    });
}

function disabledPendiente() {
    let estatus = $("#status").text().trim();
    if (estatus === "POR AUTORIZAR") {
        $("#select-movimiento").attr("readonly", true);
        $("#select-search-hided").attr("readonly", true);
        $("#select-moduleConcept").attr("readonly", true);
        $("#select-proveedorFormaPago").attr("readonly", true);
        $("#select-basic-identificadorCFDI").attr("readonly", true);
        $("#proveedorKey").attr("readonly", true);
        $("#fechaEmision").attr("readonly", true);
        $("#proveedorModal").attr("disabled", true);
        $("#cuentaModal").attr("disabled", true);
        $("#importe").attr("readonly", true);
        $("#impuesto").attr("readonly", true);
        $("#observaciones").attr("readonly", true);
        $("#referencia").attr("readonly", true);
        $("#status").attr("disabled", true);
        $("#botonForm").hide();
        $("#status").attr("disabled", false);
    }
    if (estatus === "FINALIZADO" || estatus === "CANCELADO") {
        $("#select-movimiento").attr("readonly", true);
        $("#select-search-hided").attr("readonly", true);
        $("#select-moduleConcept").attr("readonly", true);
        $("#proveedorKey").attr("readonly", true);
        $("#fechaEmision").attr("readonly", true);
        $("#proveedorModal").attr("disabled", true);
        $("#select-basic-identificadorCFDI").attr("readonly", true);
        $("#cuentaModal").attr("disabled", true);
        $("#importe").attr("readonly", true);
        $("#impuesto").attr("readonly", true);
        $("#anticiposKey").attr("readonly", true);
        $("#observaciones").attr("readonly", true);
        $("#referencia").attr("readonly", true);
        $(".botonesArticulos").attr("readonly", true);
        $("#status").attr("disabled", true);
        $(".botonesAplicaModal").attr("disabled", true);
        $("#anticiposModal").attr("disabled", true);
        $(".selectsAplica").attr("disabled", true);
        $(".accion").hide();
        $("#select-proveedorFormaPago").attr("readonly", true);
        $("#botonForm").hide();
        $("#status").attr("disabled", false);
    }
}

//Calcula el total de la cxp
function totalCxpCambio() {
    let importe = 0;
    $("input[id^='importe-']").each(function (index, elemento) {
        importe +=
            parseFloat(
                $(elemento)
                    .val()
                    .replace(/['$', ',']/g, "")
            ) || 0;
    });

    return importe;
}

function eliminarArticulo(clave, posicion) {
    if (contadorArticulos > 0) {
        articlesDelete = {
            ...articlesDelete,
            [clave + "-" + posicion]: jQuery(
                "#id-" + clave + "-" + posicion
            ).val(),
        };

        $("#" + clave + "-" + posicion).remove();
        contadorArticulos--;
        let suma = 0;
        $('input[id^="importe-"]')
            .each(function (index, value) {
                let idIntputs = $(this).attr("id");
                let cantidad = $("#" + idIntputs).val();
                suma += parseFloat(cantidad.replace(/[$,]/g, "")) || 0;
            })
            .promise()
            .done(function () {
                $("#totalCompleto").val(
                    currency(suma, {
                        separator: ",",
                        precision: 2,
                        symbol: "$",
                    }).format()
                );
            });

        jQuery("#cantidadArticulos").val(contadorArticulos);
        jQuery("#inputDataArticlesDelete").attr(
            "value",
            JSON.stringify(articlesDelete)
        );
        jQuery("#movId").val("");
    }

    if (contadorArticulos === 0) {
        $("#controlArticulo").show();

        jQuery("#movId").val("");
    }
}

function showMessage(mensaje, icon) {
    swal(mensaje, {
        button: "OK",
        timer: 3000,
        icon: icon,
    });
}

function calcularTotal(idMov, result) {
    //Calculamos la diferencia entre el importe actual y el importe del cxp
    let importeTotalPorFila = $(`#importeT-${result}`)
        .val()
        .replace(/['$', ',']/g, "");

    let importeFila = $(`#importe-${result}`)
        .val()
        .replace(/['$', ',']/g, "");

    let importeTotalFilaFormato = currency(importeFila, {
        separator: ",",
        precision: 2,
        symbol: "$",
    }).format();

    $(`#importe-${result}`).val(importeTotalFilaFormato);

    let diferencia =
        parseFloat(importeTotalPorFila) - parseFloat(importeFila) || 0;

    if (diferencia <= 0) {
        diferencia = 0;
    }
    let diferenciaFormato = currency(diferencia, {
        separator: ",",
        precision: 2,
        symbol: "$",
    }).format();

    $(`#diferencia-${result}`).val(diferenciaFormato);

    //Calculamos el porcentaje de la diferencia entre el importe actual y el importe del cxp

    let porcentaje =
        ((parseFloat(importeFila) || 0 * 100) /
            parseFloat(importeTotalPorFila)) *
        100;

    if (diferencia <= 0) {
        porcentaje = 0;
    }

    $(`#porcentaje-${result}`).val(porcentaje.toFixed(2));

    let importeTotal = totalCxpCambio();
    let importeFormato = currency(importeTotal, {
        separator: ",",
        precision: 2,
        symbol: "$",
    }).format();
    $("#totalCompleto").val(importeFormato);

    $("#importe").val(importeFormato);
}
