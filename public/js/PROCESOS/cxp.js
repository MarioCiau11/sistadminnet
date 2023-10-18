let result = 1000;
let tableCXPP;
let articlesDelete = {};
const movimientosArray = {
    Compras: "PROC_PURCHASE",
    CxP: "PROC_ACCOUNTS_PAYABLE",
    Din: "PROC_TREASURY",
    Gastos: "PROC_EXPENSES",
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

    jQuery("#selectMovSucursal").select2({
        placeholder: "Seleccione una sucursal",
    });
    // console.log(movimientoSelected);
    const $select = jQuery(
        "#select-movimiento, #select-moduleConcept, #select-listaPrecios, #select-proveedorFormaPago, #select-search-hided"
    ).select2({
        minimumResultsForSearch: -1,
    });

    jQuery("#progressWizard").validate({
        submitHandler: function (form) {
            let selectMov = $("#select-movimiento").val();

            switch (selectMov) {
                case "Pago de Facturas":
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
                maxlength: 100,
            },
            cuentaKey: {
                required: function () {
                    if (
                        jQuery("#select-movimiento").val() !== "Entrada por Compra"
                    ) {
                        if (jQuery("#select-movimiento").val() !== "Factura de Gasto") {
                            return true;
                        }
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
                    if (
                        jQuery("#select-movimiento").val() !== "Entrada por Compra"
                    ) {
                        if (jQuery("#select-movimiento").val() !== "Factura de Gasto") {
                            return true;
                        }
                    }
                    return false;
                },
            },
            proveedorFechaVencimiento: {
                required: true,
            },
            concepto: {
                required: function () {
                    if (jQuery("#select-movimiento").val() !== "Factura de Gasto") {
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
                required: function () {
                    let movimiento = $("#select-movimiento").val();
                    if (movimiento === "") {
                        return "Por favor selecciona un movimiento";
                    }
                },
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
            url: "/cxp/saldo/proveedor/",
            method: "GET",
            data: {
                proveedor: $("#proveedorKey").val(),
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
        if (movimiento === "Aplicación" || movimiento === "Pago de Facturas") {
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
        
        } else {
            $("#anticipoAplicar").hide();
            $("#referencia").attr('readonly', false);
        }
    });

    tableCuenta.on("select", function (e, dt, type, indexex) {
        const rowData = tableCuenta.row(indexex).data();
        $("#cuentaKey").val(rowData[0]);
        $("#cuentaKey").trigger("keyup");
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
        $("#anticiposKey").val(rowData[1] + " " + rowData[0]);
        $("#referencia").val(rowData[1] + " " + rowData[0]);
        $("#importe").val(rowData[3]);
        $("#cuentaKey").val(rowData[4]);
        $("#cuentaKey").trigger("keyup");
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

        //limpiar el detalle de la tabla
        getProveedorByClave(rowData[0]);
    });

    const leyendas = {
        'Pago de Facturas': 'Con este proceso se consolida la (s) facturas de compras o de gastos para programar la transferencia o emitir el egreso de efectivos.',
        'Anticipo': 'Con este proceso realiza un pago adelantado total o parcial antes de recibir el bien o servicio.',
        'Aplicación': 'Este proceso de control se relaciona el Anticipo a la factura de compras o de gasto, disminuyendo el saldo de la factura.',
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

        if (selectedMovimiento == "Aplicación" || $("#select-movimiento").val() == "Pago de Facturas") {
            $("#afectar-boton").html("Finalizar <span class='glyphicon glyphicon-play pull-right'></span>");
        } else {
            $("#afectar-boton").html("Avanzar <span class='glyphicon glyphicon-play pull-right'></span>");
        }
    });

    // Mostrar la leyenda inicial al cargar la página si hay una opción seleccionada
    const selectedMovimiento = $('#select-movimiento').val();
    mostrarLeyenda(selectedMovimiento);

    if ($("#select-movimiento").val() == "Aplicación" || $("#select-movimiento").val() == "Pago de Facturas") {
        $("#afectar-boton").html("Finalizar <span class='glyphicon glyphicon-play pull-right'></span>");
    } else {
        $("#afectar-boton").html("Avanzar <span class='glyphicon glyphicon-play pull-right'></span>");
    }

    let concepto = $("#select-moduleConcept").val();
    console.log(concepto);

    if (selectedMovimiento === 'Entrada por Compra' || selectedMovimiento === 'Factura de Gasto' || selectedMovimiento === 'Sol. de Cheque/Transferencia') {
        // Si son alguno de estos tres, no hacemos nada para no afectar el select de conceptos
       
    }

    if (selectedMovimiento == 'Pago de Facturas' || selectedMovimiento == 'Anticipo' || selectedMovimiento == 'Aplicación') {
        $.ajax({
            url: '/api/cuentas_por_pagar/getConceptosByMovimiento',
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

    $('#select-movimiento').on('change', function() {
        var selectedMovimiento = $(this).val();
        console.log(selectedMovimiento);

        if (selectedMovimiento === 'Entrada por Compra' || selectedMovimiento === 'Factura de Gasto' || selectedMovimiento === 'Sol. de Cheque/Transferencia') {
            // Si son alguno de estos tres, no hacemos nada para no afectar el select de conceptos
            return;
        }

        if (selectedMovimiento == 'Pago de Facturas' || selectedMovimiento == 'Anticipo' || selectedMovimiento == 'Aplicación') {
            $.ajax({
                url: '/api/cuentas_por_pagar/getConceptosByMovimiento',
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
    



    let proveedor = "";
    let mov = "";
    jQuery(".botonesAplica").change(function () {
        proveedor = $("#proveedorKey").val();
        mov = $(this).val();
        console.log(proveedor, mov);
    });

    $("#agregarAplicaModal").on("click", function (e) {
        $(".botonesAplica").change();
        if (mov !== "") {
            $.ajax({
                url: "/aplicaFolio",
                type: "GET",
                data: {
                    proveedor: proveedor,
                    sucursal: $("#selectMovSucursal").val(),
                    movimiento: mov,
                    moneda: $("#select-search-hided").val(),
                },
                success: function ({ estatus, dataProveedor }) {
                    if (estatus === 200) {
                        if (dataProveedor.length > 0) {
                            dataProveedor.forEach((element) => {
                                let fecha =
                                    (element.accountsPayable_expiration =
                                        moment(
                                            element.accountsPayable_expiration
                                        ).format("YYYY-MM-DD"));
                                let importe = currency(
                                    element.accountsPayable_total,
                                    {
                                        separator: ",",
                                        precision: 2,
                                        decimal: ".",
                                        symbol: "$",
                                    }
                                ).format();

                                let saldo = currency(
                                    element.accountsPayable_balance,
                                    {
                                        separator: ",",
                                        precision: 2,
                                        symbol: "",
                                    }
                                ).format();

                                tableCXPP.row
                                    .add([
                                        element.accountsPayable_id,
                                        element.accountsPayable_movement,
                                        element.accountsPayable_movementID,
                                        fecha,
                                        importe,
                                        saldo,
                                        element.accountsPayable_money,
                                        element.accountsPayable_branchOffice,
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

    //Buscamos las entradas a pagar conforme a la sucursal
    $("#selectMovSucursal").change(() => {
        $.ajax({
            url: "/aplicaFolio",
            type: "GET",
            data: {
                proveedor: proveedor,
                sucursal: $("#selectMovSucursal").val(),
                movimiento: mov,
                moneda: $("#select-search-hided").val(),
            },
            success: function ({ estatus, dataProveedor }) {
                if (estatus === 200) {
                    if (dataProveedor.length > 0) {
                        tableCXPP.clear().draw();
                        dataProveedor.forEach((element) => {
                            let fecha = (element.accountsPayable_expiration =
                                moment(
                                    element.accountsPayable_expiration
                                ).format("YYYY-MM-DD"));
                            let importe = currency(
                                element.accountsPayable_total,
                                {
                                    separator: ",",
                                    precision: 2,
                                    decimal: ".",
                                    symbol: "$",
                                }
                            ).format();

                            let saldo = currency(
                                element.accountsPayable_balance,
                                {
                                    separator: ",",
                                    precision: 2,
                                    symbol: "",
                                }
                            ).format();

                            tableCXPP.row
                                .add([
                                    element.accountsPayable_id,
                                    element.accountsPayable_movement,
                                    element.accountsPayable_movementID,
                                    fecha,
                                    importe,
                                    saldo,
                                    element.accountsPayable_money,
                                    element.accountsPayable_branchOffice,
                                ])
                                .draw();
                        });
                    } else {
                        tableCXPP.clear().draw();
                    }
                }
            },
        });
    });

    jQuery("#modalAnticipos").on("show.bs.modal", function (e) {
        const isValid = validateProveedor();
        if (isValid) {
            showMessage(
                "No se ha seleccionado el proveedor en el movimiento",
                "error"
            );
            return false;
        }
        tableAnticipo.clear().draw();
        $.ajax({
            url: "/cxp/getAnticipos",
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
                                element.accountsPayable_balance,
                                {
                                    separator: ",",
                                    precision: 2,
                                    decimal: ".",
                                    symbol: "$",
                                }
                            ).format();

                            tableAnticipo.row
                                .add([
                                    element.accountsPayable_movementID,
                                    element.accountsPayable_movement,
                                    element.accountsPayable_status,
                                    importe,
                                    element.accountsPayable_moneyAccount,
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
                // console.log(datos);

                $("#articleItem").append(`<tr id="${idMov}-${result}">
                    <td style="display: none">
                        <input type="text" name="cxp[]" id="movId-${result}" value="${idMov}"/>
                    </td>
                    <td style="display: " class="aplica">
                    <select name="cxp[]" id="aplicaSelect-${result}" class="botonesAplica" value="${movimiento}" onchange="buscarMov('${idMov}', '${result}')">
                    </select>
                    </td>
                    <td style="display: " class="aplicaC" id="btnInput"><input id="aplicaC-${result}" type="text"
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
                if (movimiento === "Entrada por Compra") {
                    select.append(`<option value="${movimiento}"  selected>${movimiento}</option>
                <option value="Factura de Gasto">Factura de Gasto</option>`);
                } else {
                    select.append(`<option value="Entrada por Compra">Entrada por Compra</option>
                <option value="${movimiento}"  selected>${movimiento}</option>`);
                }
                select.val(movimiento);

                result++;

                contadorArticulos++;
            }
        }

        $("#shTable5").DataTable().rows(".selected").deselect();

        jQuery("#cantidadArticulos").val(contadorArticulos);
    });

    $("#proveedorKey").change(function () {
        // const isValid = validateConceptoProveedor();

        let key = $(this).val() !== " " ? $(this).val() : false;

        // if (!isValid) {
        if (key) {
            getProveedorByClave(key);
        } else {
            jQuery("#select-proveedorFormaPago").val("");
            jQuery("#select-proveedorFormaPago").change();
            // jQuery("#select-proveedorFormaPago").change("readonly", false);
        }
        // $("#anticiposKey").val("");
        // $("#referencia").val("");
        // }
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

    $("#aplica-Modal").on("show.bs.modal", function (e) {
        let identificadorFila = $(e.relatedTarget).parent().parent().attr("id");
        $("#fila-id").val(identificadorFila);

        $("#select-search-hided").change();
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
        const isInvalid = validateConceptoProveedor();
        const isInvalid2 = validateFormapago();

        if (isInvalid) {
            showMessage(
                "No se ha seleccionado el concepto en el movimiento",
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
        const isInvalid = validateConceptoProveedor();

        if (isInvalid) {
            showMessage(
                "No se ha seleccionado el concepto en el movimiento",
                "error"
            );
            return false;
        }
    });

    //Metemos la data del flujo al input

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

    function getProveedorByClave(clave) {
        let movActual = $("#idCXP").val();
        $.ajax({
            url: "/logistica/compras/api/getProveedor",
            type: "GET",
            data: {
                proveedor: clave,
            },
            success: function (data) {
                if (Object.keys(data).length > 0) {
                    if (data.providers_formPayment !== null) {
                        if (movActual === "0") {
                            jQuery("#select-proveedorFormaPago").val(
                                data.providers_formPayment
                            );
                            jQuery("#select-proveedorFormaPago").change();
                        }

                        // jQuery("#select-proveedorFormaPago").attr(
                        //     "readonly",
                        //     false
                        // );
                        $("#proveedorName").val(data.providers_name);
                    } else {
                        if ($("#status").val() === "POR AUTORIZAR") {
                            jQuery("#select-proveedorFormaPago").val("");
                        }
                        jQuery("#select-proveedorFormaPago").change();

                        $("#proveedorName").val(data.providers_name);
                    }
                } else {
                    jQuery("#select-proveedorFormaPago").val("");
                    jQuery("#select-proveedorFormaPago").change();
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

                jQuery("#select-proveedorFormaPago").keyup();
                $("#proveedorName").keyup();
            },
        });
    }
    function validateConceptoProveedor() {
        if (jQuery("#select-movimiento").val() !== "Factura de Gasto") {
            if (jQuery("#select-movimiento").val() !== "Pago de Facturas") {
                if (jQuery("#select-movimiento").val() !== "Sol. de Cheque/Transferencia") {
                    const estadoConcepto =
                        $("#select-moduleConcept").val() === "" ? true : false;

                    if (estadoConcepto) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
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

    function showMessage2(titulo, mensaje, icon) {
        swal({
            title: titulo,
            text: mensaje,
            icon: icon,
        });
    }

    //Acciones cuando cambie el movimiento
    const $movimientoSelect = $("#select-movimiento");
    const $inputTotal = $("#total-input");
    const $saldoAnticipoDIV = $("#saldoAnticipoDIV");

    $movimientoSelect.change(function () {
        if (
            $(this).val() === "Anticipo" ||
            $(this).val() === "" ||
            $(this).val() === "Entrada por Compra" ||
            $(this).val() === "Factura de Gasto"
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

        if ($(this).val() === "Sol. de Cheque/Transferencia") {
            $(".contenedoresImporte").show();
        }
    });

    //Calculamos el importe. iva e importe total
    const $importeDOM = $("#importe");
    const $impuestoDOM = $("#impuesto");
    const $importeTotalDOM = $("#importeTotal");

    $importeDOM.change(function () {
        if ($movimientoSelect.val() === "Anticipo") {
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

        if ($movimientoSelect.val() === "Pago de Facturas") {
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
            $impuestoDOM
                .val()
                .replace(/[$,]/g, "")
                .replace(/[^0-9,.]/g, "")
                .replace(/,/g, ".")
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
        // let isConceptoEmpty = validateConceptoProveedor();
        let claveProveedor = $proveedorKey.val();

        // if (!isConceptoEmpty) {
        if (claveProveedor !== "") {
            await $.ajax({
                url: "/cxp/saldo/proveedor/",
                method: "GET",
                data: {
                    proveedor: claveProveedor,
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
        }
        // }
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

    const afectar = (e) => {
        e.preventDefault();
        let movimiento = jQuery("#select-movimiento").val();
        let estatus = jQuery("#status").text().trim();
        let tipo = jQuery("#tipoCuenta").val();
        const genero = obtenerGeneroDelMovimiento();
        let articulo = "un";
        if (genero === "F") {
            articulo = "una";
        }
        if (
            (movimiento === "Anticipo" && estatus === "INICIAL") ||
            movimiento === "Pago de Facturas"
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

            if (movimiento === "Anticipo") {
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
                            : isVacio
                            ? "un importe con respecto al detalle"
                            : importe
                            ? "un importe"
                            : !isMismaMoneda
                            ? "la cuenta con la misma moneda"
                            : ""
                    }`,
                    "error"
                );
            } else {
                if (tipo === "Banco") {
                    swal({
                        title: "¿Está seguro que desea generar " + articulo + " " + movimiento + "?",
                        text: "¡Con el nuevo estatus diferente a INICIAL no podrá realizar cambios!",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                        buttons: ["Cancelar", "Aceptar"],
                    }).then((willDelete) => {
                        if (willDelete) {
                            $("#loader").show();
                            let cxpJson = jsonCxpDetails();

                            $.ajax({
                                type: "POST",
                                url: "/gestion_finanzas/cuentas_por_pagar/afectar",
                                data: $("#progressWizard").serialize(),
                                success: function ({
                                    estatus,
                                    mensaje,
                                    id,
                                    cheque,
                                }) {
                                    $("#loader").hide();
                                    if (estatus === 200) {
                                        showMessage2(
                                            "Se generó automaticamente.",
                                            "Proceso: " +
                                                cheque.treasuries_movement +
                                                " " +
                                                cheque.treasuries_movementID,
                                            "info"
                                        );
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
                                        }, 1000);
                                    } else {
                                        showMessage2("Error", mensaje, "error");
                                    }
                                },
                            });
                        }
                    });
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
                            $("#loader").show();
                            jsonCxpDetails();
                            $.ajax({
                                url: "/gestion_finanzas/cuentas_por_pagar/afectar",
                                type: "POST",
                                data: $("#progressWizard").serialize(),
                                success: function ({
                                    estatus,
                                    mensaje,
                                    id,
                                    egreso,
                                }) {
                                    $("#loader").hide();
                                    if (estatus === 200) {
                                        showMessage2(
                                            "Se generó automaticamente.",
                                            "Proceso: " +
                                                egreso.treasuries_movement +
                                                egreso.treasuries_movementID,
                                            "info"
                                        );
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
        } else if (movimiento === "Anticipo" && estatus === "POR AUTORIZAR") {
            let folio = $("#folioMov").val();
            $("#exampleModalCenterTitle2").html("Módulo - CXP");
            $("#modalAfectar2").modal("show");
        }

        if (movimiento === "Factura de Gasto" || movimiento === "Entrada por Compra") {
            let folio = $("#folioMov").val();
            $("#exampleModalCenterTitle").html("Módulo - CXP");

            $("#modalAfectar").modal("show");
        }

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
            if (movimiento === "Anticipo") {
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
                        $.ajax({
                            url: "/gestion_finanzas/cuentas_por_pagar/afectar",
                            type: "POST",
                            data: $("#progressWizard").serialize(),
                            success: function ({ estatus, mensaje, id }) {
                                if (estatus === 200) {
                                    showMessage2(
                                        "La Aplicación se ha creado correctamente.",
                                        "",
                                        "success"
                                    );
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

    function validarMonedas() {
        const moneda = $("#select-search-hided").val();
        const cuentaMoneda = $("#tipoCambio").val();

        if (moneda === cuentaMoneda) {
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

    if (
        movimiento.val() === "Entrada por Compra" ||
        movimiento.val() === "Factura de Gasto" ||
        movimiento.val() === "Sol. de Cheque/Transferencia"
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

        if (
            (status === "FINALIZADO" && movimiento === "Aplicación") ||
            (status === "FINALIZADO" && movimiento === "Pago de Facturas") ||
            (status === "POR AUTORIZAR" && movimiento === "Anticipo")
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
                    $("#loader").show();
                    $.ajax({
                        url: "/cancelarCxP/",
                        type: "GET",
                        data: {
                            id,
                            movimiento,
                            folio,
                            tipo,
                        },
                        success: function ({ estatus, mensaje, id }) {
                            $("#loader").hide();
                            if (estatus === 200) {
                                showMessage2(mensaje, "", "success");
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
                                }, 1000);
                            } else if (estatus === 400) {
                                showMessage2("Precaución", mensaje, "warning");
                            } else {
                                showMessage2("Error", mensaje, "error");
                            }
                        },
                    });
                }
            });
        }

        if (status === "POR AUTORIZAR" && movimiento === "Sol. de Cheque/Transferencia") {
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
                    "No se puede cancelar una Sol. de Cheque/Transferencia pendiente directamente",
                    "warning"
                );
            });
        }

        if (movimiento === "Entrada por Compra" || movimiento === "Factura de Gasto") {
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
                        url: "/eliminarMovimiento/",
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
                                        "/gestion_finanzas/cuentas_por_pagar/create";
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
            $("#origin").val("Pago de Facturas");
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

        if (aplicacionRadio === true) {
            // // $('#select-movimiento').val('Cheque');
            // // $('#status').val('INICIAL');
            $("#origin").val("Aplicación");
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
        // console.log("afectando");
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
            getProveedorByClave(key);
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

    //Revisamos si tiene permiso para afectar, cancelar o guardar

    if (
        movimientoSelected === "Anticipo" ||
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

    $("input[id^='importe-']").trigger("change");
}); //Fin del Jquery;

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
                url: "/aplicaFolio",
                method: "GET",
                data: {
                    proveedor: proveedor,
                    sucursal: $("#selectMovSucursal").val(),
                    movimiento: mov,
                    moneda: $("#select-search-hided").val(),
                },
                success: function ({ estatus, dataProveedor }) {
                    if (estatus === 200) {
                        if (dataProveedor.length > 0) {
                            tableCXPP.clear().draw();
                            dataProveedor.forEach((element) => {
                                let fecha =
                                    (element.accountsPayable_expiration =
                                        moment(
                                            element.accountsPayable_expiration
                                        ).format("YYYY-MM-DD"));
                                let importe = currency(
                                    element.accountsPayable_total,
                                    {
                                        separator: ",",
                                        precision: 2,
                                        decimal: ".",
                                        symbol: "",
                                    }
                                ).format();

                                let saldo = currency(
                                    element.accountsPayable_balance,
                                    {
                                        separator: ",",
                                        precision: 2,
                                        symbol: "",
                                    }
                                ).format();

                                jQuery("#shTable5")
                                    .DataTable()
                                    .row.add([
                                        element.accountsPayable_id,
                                        element.accountsPayable_movement,
                                        element.accountsPayable_movementID,
                                        fecha,
                                        importe,
                                        saldo,
                                        element.accountsPayable_money,
                                        element.accountsPayable_branchOffice,
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
        $("#select-proveedorFormaPago").attr("readonly", true);
        $("#proveedorKey").attr("readonly", true);
        $("#fechaEmision").attr("readonly", true);
        $("#proveedorModal").attr("disabled", true);
        $("#cuentaModal").attr("disabled", true);
        $("#importe").attr("readonly", true);
        $("#impuesto").attr("readonly", true);
        $("#anticiposKey").attr("readonly", true);
        $("#observaciones").attr("readonly", true);
        $("#referencia").attr("readonly", true);
        $("#status").attr("disabled", true);
        $(".botonesAplicaModal").attr("disabled", true);
        $("#anticiposModal").attr("disabled", true);
        $(".selectsAplica").attr("disabled", true);
        $(".accion").hide();

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

//funcion para detectar los comandos de teclado
$(document).keydown(function (e) {
    let mov = $("#select-movimiento").val();
    let status = $("#status").text().trim();

    if (
        (mov === "Aplicación" && status === "INICIAL") ||
        (mov === "Pago de Facturas" && status === "INICIAL")
    ) {
        if (e.ctrlKey && e.keyCode === 40) {
            $("#articleItem").append(`<tr id="${result}">
            <td style="display: none">
                <input type="text" name="cxp[]" id="movId-${result}" value=""/>
            </td>
            <td  class="aplica">
                                                <select name="cxp[]" id="aplicaSelect-${result}" class="botonesAplica" onchange="buscarMov('${mov}', '${result}')">
                                                    
                                                </select>
                                            </td>
            <td style="display: " class="aplicaC" id="btnInput"><input id="aplicaC-${result}" type="text"
            class="botonesArticulos" readonly value="" name="cxp[]">
                <button type="button" class="btn btn-info btn-sm" id="agregarAplicaModal-${result}" data-toggle="modal"
                data-target=".modal4">...</button>
            </td>
            <td style="display: ; justify-content: center; align-items: center"
                class="importe"><input id="importe-${result}" type="text"
                    class="botonesArticulos" value="" name="cxp[]"></td>
            <td style="display: ; justify-content: center; align-items: center"
                class="diferencia"><input id="diferencia-${result}" type="text"
                    class="botonesArticulos" readonly value="$0.00" name="cxp[]"></td>
            <td style="display: ; justify-content: center; align-items: center"
                class="porcentaje"><input id="porcentaje-${result}" type="text"
                    class="botonesArticulos" readonly value="0" name="cxp[]"></td>
            <td style="display: " class="accion">
                <i class="fa fa-trash-o  btn-delete-articulo" aria-hidden="true"
                    style="color: red; font-size: 25px; cursor: pointer;" onclick="eliminarArticulo('ninguno', '${result}')"></i>
            </td>
            <td style="display: none; justify-content: center; align-items: center"
            ><input id="importeT-${result}" type="text"
                class="botonesArticulos" value="" name="cxp[]"></td>
            <td style="display: none; justify-content: center; align-items: center"
            ><input id="sucursal-${result}" type="text"
                class="botonesArticulos" value="" name="cxp[]"></td>
                </tr>`);

            const select = $("#aplicaSelect-" + result);
            select.append(`<option value="Entrada por Compra">Entrada por Compra</option>
                <option value="Factura de Gasto">Factura de Gasto</option>`);
            select.val("Entrada por Compra");
            select.change();
            result++;
            contadorArticulos++;
        }
    }
});
