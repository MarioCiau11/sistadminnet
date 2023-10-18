const movimientosArray = {
    Compras: "PROC_PURCHASE",
    CxP: "PROC_ACCOUNTS_PAYABLE",
    Din: "PROC_TREASURY",
    CxC: "PROC_ACCOUNTS_RECEIVABLE",
    Ventas: "PROC_SALES",
    Gastos: "PROC_EXPENSES",
};

jQuery(document).ready(function () {
    const $formaPago = $("#contenedorFormaPago");
    const $transferencia = $(".transferencia");
    const $cuentaPago = $("#cuentaPago");
    const $cuentaKey = $("#cuentaKey");

    const $select = jQuery(
        "#select-movimiento, #select-moduleConcept, #select-listaPrecios, #select-proveedorCondicionPago, #select-search-hided, #select-PaymentMethod"
    ).select2({
        minimumResultsForSearch: -1,
    });

    jQuery("#select-movimiento")
        .select2({
            minimumResultsForSearch: -1,
        })
        .attr("readonly", function () {
            let isReadonly = false;

            if (
                $("#select-movimiento").val() === "Transferencia Electrónica" ||
                $("#select-movimiento").val() === "Sol. de Cheque/Transferencia" ||
                $("#select-movimiento").val() === "Solicitud Depósito" ||
                $("#select-movimiento").val() === "Depósito"
            ) {
                isReadonly = true;
            } else {
                isReadonly = false;
            }
            return isReadonly;
        });
    
    const leyendas = {
        'Traspaso Cuentas': 'Realiza los traspasos de dinero entre cuentas de la misma empresa.',
        'Ingreso': 'Este proceso aumenta el saldo de la cuenta de banco o efectivos.',
        'Egreso': 'Este proceso disminuye el saldo de la cuenta de banco o efectivos.',
        'Depósito': 'Este proceso aumenta el saldo de la cuenta de banco formalizando un ingreso.',
        'Transferencia Electrónica': 'Este proceso disminuye el saldo de la cuenta de banco formalizando un pago.',
        'Solicitud Depósito': 'Realiza la solicitud de depósito de dinero en la cuenta de banco o efectivos.',
        'Sol. de Cheque/Transferencia': 'Realiza la solicitud de pago de dinero en la cuenta de banco o efectivos.',
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

        if (selectedMovimiento == "Traspaso Cuentas" || selectedMovimiento == "Ingreso" || selectedMovimiento == "Egreso" || selectedMovimiento == "Transferencia Electrónica" || selectedMovimiento == "Depósito") {
            $("#afectar-boton").html("Finalizar <span class='glyphicon glyphicon-play pull-right'></span>");
        } else {
            $("#afectar-boton").html("Avanzar <span class='glyphicon glyphicon-play pull-right'></span>");
        }
    });

    
    // Mostrar la leyenda inicial al cargar la página si hay una opción seleccionada
    const selectedMovimiento = $('#select-movimiento').val();
    mostrarLeyenda(selectedMovimiento);
    
    if (selectedMovimiento == "Traspaso Cuentas" || selectedMovimiento == "Ingreso" || selectedMovimiento == "Egreso" || selectedMovimiento == "Transferencia Electrónica" || selectedMovimiento == "Depósito") {
        $("#afectar-boton").html("Finalizar <span class='glyphicon glyphicon-play pull-right'></span>");
    } else {
        $("#afectar-boton").html("Avanzar <span class='glyphicon glyphicon-play pull-right'></span>");
    }
    let concepto = $("#select-moduleConcept").val();

    $('#select-movimiento').on('change', function() {
        var selectedMovimiento = $(this).val();
        console.log(selectedMovimiento);
        if
            (selectedMovimiento === 'Entrada por Compra' || selectedMovimiento === 'Factura de Gasto' || selectedMovimiento === 'Sol. de Cheque/Transferencia') {
            //si son alguno de estos tres no hacemos nada para no afectar el select de conceptos

        }
        if (selectedMovimiento === 'Traspaso Cuentas' || selectedMovimiento === 'Ingreso' || selectedMovimiento === 'Egreso' || selectedMovimiento === 'Transferencia Electrónica' || selectedMovimiento === 'Depósito' || selectedMovimiento === 'Solicitud Depósito') {
            // Lógica para filtrar los conceptos según el movimiento seleccionado
            $.ajax({
                url: '/api/tesoreria/getConceptosByMovimiento',
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
        else {
            $.ajax({
                url: '/api/tesoreria/getConceptosByMovimiento',
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
        }
    });

     if
        (selectedMovimiento === 'Entrada por Compra' || selectedMovimiento === 'Factura de Gasto' || selectedMovimiento === 'Sol. de Cheque/Transferencia') {
        //si son alguno de estos tres no hacemos nada para no afectar el select de conceptos

    }
    if (selectedMovimiento === 'Traspaso Cuentas' || selectedMovimiento === 'Ingreso' || selectedMovimiento === 'Egreso' || selectedMovimiento === 'Transferencia Electrónica' || selectedMovimiento === 'Depósito' || selectedMovimiento === 'Solicitud Depósito') {
        // Lógica para filtrar los conceptos según el movimiento seleccionado
        $.ajax({
            url: '/api/tesoreria/getConceptosByMovimiento',
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
    else {
        $.ajax({
            url: '/api/tesoreria/getConceptosByMovimiento',
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
    }



    const copiar = function (e) {
        e.preventDefault();
        //Enviamos el formulario para copiar la compra
        const form = jQuery("#progressWizard");
        const inputCopiar =
            '<input type="hidden" name="copiar" value="copiar" readonly>';
        form.append(inputCopiar);

        $('input[id^="id-"]').each(function (index, value) {
            $(this).remove();
        });
        $("#idCXP").val("0");

        form.submit();
    };

    if($("#typeOrigin").val() === "CxP" || $("#typeOrigin").val() === "CxC" || $("#typeOrigin").val() === "Factura de Gasto" || $("#typeOrigin").val() === "Ventas"){
       const val=  $("#typeOrigin").val();
        $("#copiar-movimiento").hide();
    }
    if ($("#typeOrigin").val() === "Din") {
        if ($("#select-movimiento").val() === "Transferencia Electrónica") {
            $("#copiar-movimiento").hide();
        }
    }

    $("#copiar-movimiento").click((event) => {
        copiar(event);
    });

    $("form").keypress(function (e) {
        if (e.which == 13) {
            return false;
        }
    });
    
    //marcar el checkbox dependiendo si es solicitud de cheque o de depósito
    if ($("#select-movimiento").val() === "Sol. de Cheque/Transferencia") {
        $("#chequeRadio").prop("checked", true);
    } else if ($("#select-movimiento").val() === "Solicitud Depósito") {
        $("#depositoRadio").prop("checked", true);
    }

    jQuery("#progressWizard").validate({
        submitHandler: function (form) {
            let movimiento = $("#select-movimiento").val();
            let movimientoSe = false;
            let conceptoProveedor = false;
            let proveedor = false;
            let cuenta = false;
            let cuentaOrigen = false;
            let beneficiario = false;
            let cuentaTrans = false;
            let cuentaTipo = false;
            let formaPago = false;
            let importe = false;
            let cuentaD = false;
            let cuentaTipoD = false;
            let cuentaTipoOri = false;
            let cuentaTipoMoneda = false;

            switch (movimiento) {
                case "Transferencia Electrónica":
                    movimientoSe = validateMovimiento();
                    conceptoProveedor = validateConceptoProveedor();
                    proveedor = validateProveedor();
                    cuenta = validateCuenta();
                    cuentaTipoMoneda = validarMonedas();
                    break;
                case "Depósito":
                    movimientoSe = validateMovimiento();
                    conceptoProveedor = validateConceptoProveedor();
                    proveedor = validateProveedor();
                    cuenta = validateCuenta();
                    cuentaTipoMoneda = validarMonedas();
                    break;
                case "Ingreso":
                    movimientoSe = validateMovimiento();
                    conceptoProveedor = validateConceptoProveedor();
                    // cuentaOrigen = validateCuentaOrigen();
                    importe = validateImporte();
                    formaPago = validateFormaPago();
                    cuentaTrans = validarCuentaTrans();
                    cuentaTipoMoneda = validarMonedas();
                    break;

                case "Egreso":
                    movimientoSe = validateMovimiento();
                    conceptoProveedor = validateConceptoProveedor();
                    cuentaTrans = validarCuentaTrans();
                    cuentaTipo = validateCuentaTipoCaja();
                    formaPago = validateFormaPago();
                    importe = validateImporte();
                    cuentaTipoMoneda = validarMonedas();
                    break;
                case "Traspaso Cuentas":
                    movimientoSe = validateMovimiento();
                    conceptoProveedor = validateConceptoProveedor();
                    cuentaTrans = validarCuentaTrans();
                    formaPago = validateFormaPago();
                    importe = validateImporte();
                    cuentaD = validarCuentaDes();
                    // cuentaTipoD = validateCuentaTipoDes();
                    // cuentaTipoOri = validateCuentaTipoOri();
                    cuentaTipoMoneda = validarMonedas();
                    break;

                default:
                    break;
            }

            if (
                movimientoSe ||
                conceptoProveedor ||
                proveedor ||
                cuenta ||
                cuentaOrigen ||
                beneficiario ||
                cuentaTrans ||
                cuentaTipo ||
                formaPago ||
                importe ||
                cuentaD ||
                cuentaTipoOri ||
                cuentaTipoD ||
                cuentaTipoMoneda
            ) {
                showMessage(
                    `No se ha seleccionado ${
                        movimientoSe
                            ? "un movimiento"
                            : conceptoProveedor
                            ? "un concepto"
                            : proveedor
                            ? "un proveedor"
                            : cuenta
                            ? "una cuenta"
                            : cuentaOrigen
                            ? "en la cuenta origen una cuenta de tipo banco"
                            : beneficiario
                            ? "un beneficiario"
                            : cuentaTrans
                            ? "una cuenta"
                            : cuentaTipo
                            ? "una cuenta de tipo caja"
                            : formaPago
                            ? "una forma de pago"
                            : importe
                            ? "un importe"
                            : cuentaD
                            ? "una cuenta de destino"
                            : cuentaTipoOri
                            ? "una cuenta tipo banco en el origen"
                            : cuentaTipoD
                            ? "una cuenta tipo banco en el destino"
                            : cuentaTipoMoneda
                            ? "una cuenta tipo moneda igual al movimiento"
                            : ""
                    }`,
                    "error"
                );
            } else {
                form.submit();
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
                    if (
                        jQuery("#select-movimiento").val() ===
                        "Sol. de Cheque/Transferencia"
                    ) {
                        return false;
                    }

                    if (
                        jQuery("#select-movimiento").val() ===
                        "Solicitud Depósito"
                    ) {
                        return false;
                    }

                    return true;
                },
            },
            cuentaDKey: {
                required: function () {
                    if (
                        jQuery("#select-movimiento").val() ===
                        "Sol. de Cheque/Transferencia"
                    ) {
                        return false;
                    }

                    if (
                        jQuery("#select-movimiento").val() ===
                        "Solicitud Depósito"
                    ) {
                        return false;
                    }

                    return true;
                },
            },
            claveBancaria: {
                required: function () {
                    if (
                        jQuery("#select-movimiento").val() ===
                        "Sol. de Cheque/Transferencia"
                    ) {
                        return false;
                    }

                    if (
                        jQuery("#select-movimiento").val() ===
                        "Solicitud Depósito"
                    ) {
                        return false;
                    }

                    return true;
                },
            },
            claveBancariaD: {
                required: function () {
                    if (
                        jQuery("#select-movimiento").val() ===
                        "Sol. de Cheque/Transferencia"
                    ) {
                        return false;
                    }

                    if (
                        jQuery("#select-movimiento").val() ===
                        "Solicitud Depósito"
                    ) {
                        return false;
                    }

                    return true;
                },
            },

            proveedorCondicionPago: {
                required: true,
            },
            proveedorFechaVencimiento: {
                required: true,
            },
            formaPago: {
                required: function () {
                    if (
                        jQuery("#select-movimiento").val() ===
                        "Sol. de Cheque/Transferencia"
                    ) {
                        return false;
                    }

                    if (
                        jQuery("#select-movimiento").val() ===
                        "Solicitud Depósito"
                    ) {
                        return false;
                    }

                    return true;
                },
            },
            concepto: {
                required: function () {
                    if (
                        jQuery("#select-movimiento").val() ===
                        "Sol. de Cheque/Transferencia"
                    ) {
                        return false;
                    }

                    if (
                        jQuery("#select-movimiento").val() ===
                        "Solicitud Depósito"
                    ) {
                        return false;
                    }

                    return true;
                },
                maxlength: 100,
            },
            proveedorReferencia:{
                maxlength: 100,
            }
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
            cuentaDKey: {
                required: "Este campo es requerido",
            },
            claveBancaria: {
                required: "Este campo es requerido",
            },
            claveBancariaD: {
                required: "Este campo es requerido",
            },
            beneficiario: {
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
            proveedorReferencia:{
                maxlength: jQuery.validator.format("Maximo de {0} caracteres"),
            }
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



    jQuery("#select-movimiento").on("change", function (e) {
        const mov = $("#select-movimiento").val();

        switch (mov) {
            case "Egreso":
                $("#folio").show();
                $("#moneda").show();
                $("#tipoCambio").show();
                $("#fechaEmision").show();
                $("#concepto").show();
                $("#contenedorFormaPago").show();
                $("#saldoCta").show();
                $("#importe").show();
                $("#beneficiario").hide();
                $("#beneficiarioName").hide();
                $("#saldo").show();
                $("#cuenta").show();
                $("#cuentaD").hide();
                $("#claveBancariaD").hide();
                $("#observaciones").show();
                $("#referencias").show();
                $("#checkboxCaja").hide();
                $("#tablaContenedor").hide(); //Opcional

                break;
            case "Ingreso":
            $("#folio").show();
            $("#moneda").show();
            $("#tipoCambio").show();
            $("#fechaEmision").show();
            $("#concepto").show();
            $("#contenedorFormaPago").show();
            $("#saldoCta").show();
            $("#importe").show();
            $("#beneficiario").hide();
            $("#beneficiarioName").hide();
            $("#saldo").hide();
            $("#cuenta").show();
            $("#cuentaD").hide();
            $("#claveBancariaD").hide();
            $("#cuentaPago").hide();
            $("#observaciones").show();
            $("#referencias").show();
            $transferencia.show();
            $("#checkboxCaja").show();
            $("#totalInformativo").hide();
            $("#tablaContenedor").hide();
            break;

            case "Depósito":
                $("#folio").show();
                $("#moneda").show();
                $("#tipoCambio").show();
                $("#fechaEmision").show();
                $("#concepto").show();
                $("#contenedorFormaPago").show();
                $("#saldoCta").show();
                $("#importe").show();
                $("#beneficiarioInput").show();
                $("#beneficiarioInputName").show();
                $("#saldo").hide();
                $("#cuenta").show();
                $("#cuentaD").hide();
                $("#claveBancariaD").hide();
                $("#observaciones").show();
                $("#referencias").show();
                $formaPago.hide();
                $transferencia.hide();
                $cuentaPago.show();
                $("#saldoCuenta").attr("readonly", true);
                $("#importe").attr("readonly", true);
                $("#beneficiarioInput").attr("readonly", true);
                $("#checkboxCaja").hide();
                $("#tablaContenedor").show();
                break;

            case "Transferencia Electrónica":
                $("#folio").show();
                $("#moneda").show();
                $("#tipoCambio").show();
                $("#fechaEmision").show();
                $("#concepto").show();
                $("#contenedorFormaPago").show();
                $("#saldoCta").show();
                $("#importe").show();
                $("#beneficiarioInput").show();
                $("#beneficiarioInputName").show();
                $("#saldo").hide();
                $("#cuenta").show();
                $("#cuentaD").hide();
                $("#claveBancariaD").hide();
                $("#observaciones").show();
                $("#referencias").show();
                $formaPago.hide();
                $transferencia.hide();
                $cuentaPago.show();
                $("#saldoCuenta").attr("readonly", true);
                $("#importe").attr("readonly", true);
                $("#beneficiarioInput").attr("readonly", true);
                $("#checkboxCaja").hide();
                $("#tablaContenedor").show();
                break;

            case "Traspaso Cuentas":
                $("#folio").show();
                $("#moneda").show();
                $("#tipoCambio").show();
                $("#fechaEmision").show();
                $("#concepto").show();
                $("#contenedorFormaPago").show();
                $("#saldoCta").show();
                $("#importe").show();
                $("#beneficiario").hide();
                $("#beneficiarioName").hide();
                $("#saldo").show();
                $("#cuenta").show();
                $("#cuentaD").show();
                $("#claveBancariaD").show();
                $("#observaciones").show();
                $("#referencias").show();
                $("#checkboxCaja").hide();
                $("#tablaContenedor").hide();
                break;

            case "Sol. de Cheque/Transferencia":
                $formaPago.show();
                $transferencia.hide();
                $cuentaPago.show();
                $("#checkboxCaja").hide();
                $("#tablaContenedor").hide();
                $("#beneficiarioInput").attr("readonly", true);
                break;

            case "Solicitud Depósito":
                $formaPago.show();
                $transferencia.hide();
                $cuentaPago.show();
                $("#checkboxCaja").hide();
                $("#tablaContenedor").hide();
                $("#beneficiarioInput").attr("readonly", true);
                break;

            default:
                $("#folio").show();
                $("#moneda").show();
                $("#tipoCambio").show();
                $("#fechaEmision").show();
                $("#concepto").show();
                $("#contenedorFormaPago").show();
                $("#saldoCta").show();
                $("#importe").show();
                $("#beneficiario").hide();
                $("#beneficiarioName").hide();
                $("#saldo").hide();
                $("#cuenta").show();
                $("#cuentaD").show();
                $("#claveBancariaD").show();
                $("#observaciones").show();
                $("#referencias").show();
                $transferencia.show();
                $("#checkboxCaja").hide();
                break;
        }
    });

    $("#proveedorModal").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
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

    function getProveedorByClave(clave) {
        $.ajax({
            url: "/logistica/compras/api/getProveedor",
            type: "GET",
            data: {
                proveedor: clave,
            },
            success: function (data) {
                if (Object.keys(data).length > 0) {
                    $("#beneficiarioInputName").val(data.providers_name);
                } else {
                    $("#beneficiarioInputName").val("");
                }
            },
        });
    }

    function getClienteByClave(clave) {
        $.ajax({
            url: "/comercial/ventas/api/getCliente",
            type: "GET",
            data: {
                cliente: clave,
            },
            success: function (data) {
                if (Object.keys(data).length > 0) {
                    $("#beneficiarioInputName").val(data.customers_name);
                } else {
                    $("#beneficiarioInputName").val("");
                }
            },
        });
    }

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

    const tableCuentaD = jQuery("#shTable2").DataTable({
        select: {
            style: "single",
        },
        language: language,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    const tableBeneficiario = jQuery("#shTable11").DataTable({
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
        let movimientoActual = $("#select-movimiento").val();

        switch (movimientoActual) {
            case "Transferencia Electrónica":
                $("#cuentaKey").val(rowData[0]);
                $("#tipoCuenta").val(rowData[3]);
                $("#tipoCambio2").val(rowData[2]);
                $cuentaKey.change();
                break;

            case "Depósito":
                $("#cuentaKey").val(rowData[0]);
                $("#tipoCuenta").val(rowData[3]);
                $("#tipoCambio2").val(rowData[2]);
                $cuentaKey.change();
                break;

            case "Ingreso":
                $("#cuentaTrans").val(rowData[0]);
                $("#tipoCuentaTrans").val(rowData[4]);
                $("#claveBancaria").val(rowData[0] + "-" + rowData[1]);
                $("#tipoCambio2").val(rowData[2]);
                $("#cuentaTrans").change();
                break;

            case "Egreso":
                $("#cuentaTrans").val(rowData[0]);
                $("#tipoCuentaTrans").val(rowData[4]);
                $("#claveBancaria").val(rowData[0] + "-" + rowData[1]);
                $("#tipoCambio2").val(rowData[2]);
                $("#cuentaTrans").change();
                break;

            case "Traspaso Cuentas":
                $("#cuentaTrans").val(rowData[0]);
                $("#tipoCuentaTrans").val(rowData[4]);
                $("#claveBancaria").val(rowData[1] + "-" + rowData[5]);
                $("#tipoCambio2").val(rowData[2]);
                $("#cuentaTrans").change();
                break;

            default:
                break;
        }
        $("#cuentaTrans").keyup();
        $("#claveBancaria").keyup();
    });

    tableCuentaD.on("select", function (e, dt, type, indexex) {
        const rowData = tableCuentaD.row(indexex).data();
        let movimientoActual = $("#select-movimiento").val();

        switch (movimientoActual) {
            case "Traspaso Cuentas":
                $("#cuentaDKeyInput").val(rowData[0]);
                $("#cuentaDKeyInput").keyup();
                $("#tipoCuentaDTrans").val(rowData[3]);
                $("#claveBancariaDInput").val(rowData[1] + "-" + rowData[5]);
                $("#claveBancariaDInput").keyup();
                $("#tipoCambioDestino2").val(rowData[4]);
                break;

            default:
                break;
        }
    });

    tableBeneficiario.on("select", function (e, dt, type, indexex) {
        const rowData = tableBeneficiario.row(indexex).data();
        $("#beneficiarioInput").val(rowData[0]);
        $("#beneficiarioName").val(rowData[1]);
        $("#proveedorReferencia").val();
        //ahora a proveedorReferencia como tiene letras y números vamos a separarlos a hacer que si encuentra la palabra "Anticipo Clientes" sin importar el número que tenga, lo ponga en el input de referencia

        if (
            $("#select-movimiento").val() === "Solicitud Depósito" ||
            $("#select-movimiento").val() === "Depósito" || 
            ($("#select-movimiento").val() === "Sol. de Cheque/Transferencia" && $("#typeOrigin").val() === "CxC") ||
        ($("#select-movimiento").val() === "Transferencia Electrónica" && $("#proveedorReferencia").val() === "Devolución de Anticipo")
        ) {
            getClienteByClave(rowData[0]);
        } else {
            getProveedorByClave(rowData[0]);
        }

    });
    console.log($("#select-movimiento").val(), $("#proveedorReferencia").val());

    $("#cuentaModal").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#cuentaDModal").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#informacionProveedorModal").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });
    $("#cuentaModal").on("show.bs.modal", function (e) {
        const isInvalid = validateConceptoCuenta();

        if (isInvalid) {
            showMessage(
                "No se ha seleccionado el concepto en el movimiento",
                "error"
            );
            return false;
        }
    });

    $("#select-moduleConcept").on("show.bs.select2", function (e) {
        const isInvalid = validateConceptoForma();

        if (isInvalid) {
            showMessage(
                "No se ha seleccionado el concepto en el movimiento",
                "error"
            );
            return false;
        }
    });

    jQuery("#timepicker").timepicker({
        defaultTIme: false,
    });
    jQuery("#datepickerInicia").datepicker();
    jQuery("#timepicker2").timepicker({
        defaultTIme: false,
    });
    jQuery("#datepickerInicia2").datepicker();

    function validateCuentaDestino() {
        const cuentaDestino = $("#tipoCuentaDTrans").val();
        const isChecked = $("#checkbox").is(":checked");

        if (isChecked) {
            if (cuentaDestino === "Banco") {
                return true;
            } else {
                return false;
            }
        }

        return false;
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

    function validateCuentaOrigen() {
        const cuentaOrigen = $("#tipoCuentaTrans").val();
        if (cuentaOrigen !== "Banco") {
            return true;
        }
        return false;
    }

    function validateConceptoCuenta() {
        const estadoConcepto =
            $("#select-moduleConcept").val() === "" ? true : false;

        if (estadoConcepto) {
            return true;
        } else {
            return false;
        }
    }

    function validateConceptoForma() {
        const estadoConcepto =
            $("#select-moduleConcept").val() === "" ? true : false;

        if (estadoConcepto) {
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

    $("#modalAfectar").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    $("#beneficiarioModal").modal({
        backdrop: "static",
        keyboard: true,
        show: false,
    });

    disabledPendiente();
    const genero = obtenerGeneroDelMovimiento();
    let articulo = "el";
    if (genero === "F") {
        articulo = "la";
    }

    function obtenerGeneroDelMovimiento() {
        const movimiento = $("#select-movimiento").val();

        //si el movimiento es diferente de aplicación el genero es F
        if (movimiento !== "Transferencia Electrónica") {
            return "M";
        } else {
            return "F";
        }
    }
    const afectar = (e) => {
        e.preventDefault();
        let movimiento = $("#select-movimiento").val();
        let movimientoSe = false;
        let conceptoProveedor = false;
        let proveedor = false;
        let cuenta = false;
        let cuentaOrigen = false;
        let beneficiario = false;
        let cuentaTrans = false;
        let cuentaTipo = false;
        let formaPago = false;
        let importe = false;
        let cuentaD = false;
        let cuentaTipoD = false;
        let cuentaTipoOri = false;
        let cuentaTipoMoneda = false;
        let cuentaTipoMonedaD = false;
        let cuentasIguales = false;

        switch (movimiento) {
            case "Transferencia Electrónica":
                movimientoSe = validateMovimiento();
                conceptoProveedor = validateConceptoProveedor();
                proveedor = validateProveedor();
                cuenta = validateCuenta();
                cuentaTipoMoneda = validarMonedas();
                break;

            case "Depósito":
                movimientoSe = validateMovimiento();
                conceptoProveedor = validateConceptoProveedor();
                proveedor = validateProveedor();
                cuenta = validateCuenta();
                cuentaTipoMoneda = validarMonedas();
                break;
            case "Ingreso":
                movimientoSe = validateMovimiento();
                conceptoProveedor = validateConceptoProveedor();
                // cuentaOrigen = validateCuentaOrigen();
                importe = validateImporte();
                formaPago = validateFormaPago();
                cuentaTrans = validarCuentaTrans();
                cuentaTipoMoneda = validarMonedas();
                break;

            case "Egreso":
                movimientoSe = validateMovimiento();
                conceptoProveedor = validateConceptoProveedor();
                cuentaTrans = validarCuentaTrans();
                cuentaTipo = validateCuentaTipoCaja();
                formaPago = validateFormaPago();
                importe = validateImporte();
                cuentaTipoMoneda = validarMonedas();
                break;
            case "Traspaso Cuentas":
                movimientoSe = validateMovimiento();
                conceptoProveedor = validateConceptoProveedor();
                cuentaTrans = validarCuentaTrans();
                formaPago = validateFormaPago();
                importe = validateImporte();
                cuentaD = validarCuentaDes();
                // cuentaTipoD = validateCuentaTipoDes();
                // cuentaTipoOri = validateCuentaTipoOri();
                cuentaTipoMoneda = validarMonedas();
                cuentaTipoMonedaD = validarMonedasTransferencia();
                cuentasIguales = validarCuentasIguales();
                break;

            default:
                break;
        }

        if (
            movimientoSe ||
            conceptoProveedor ||
            proveedor ||
            cuenta ||
            cuentaOrigen ||
            beneficiario ||
            cuentaTrans ||
            cuentaTipo ||
            formaPago ||
            importe ||
            cuentaD ||
            cuentaTipoOri ||
            cuentaTipoD ||
            cuentaTipoMoneda ||
            cuentaTipoMonedaD ||
            cuentasIguales
        ) {
            showMessage(
                `No se ha seleccionado ${
                    movimientoSe
                        ? "un movimiento"
                        : conceptoProveedor
                        ? "un concepto"
                        : proveedor
                        ? "un proveedor"
                        : cuenta
                        ? "una cuenta"
                        : cuentaOrigen
                        ? "en la cuenta origen una cuenta de tipo banco"
                        : beneficiario
                        ? "un beneficiario"
                        : cuentaTrans
                        ? "una cuenta"
                        : cuentaTipo
                        ? "una cuenta de tipo caja"
                        : formaPago
                        ? "una forma de pago"
                        : importe
                        ? "un importe"
                        : cuentaD
                        ? "una cuenta de destino"
                        : cuentaTipoOri
                        ? "una cuenta tipo banco en el origen"
                        : cuentaTipoD
                        ? "una cuenta tipo banco en el destino"
                        : cuentaTipoMoneda
                        ? "una cuenta con la misma moneda"
                        : cuentaTipoMonedaD
                        ? "una cuenta destino con la misma moneda"
                        : cuentasIguales
                        ? "cuentas diferentes"
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
                    $("#loader").show();
                    $.ajax({
                        url: "/gestion_finanzas/tesoreria/afectar",
                        type: "POST",
                        data: $("#progressWizard").serialize(),
                        success: function ({ status, mensaje, id }) {
                            $("#loader").hide();
                            if (status) {
                                showMessage2(
                                    "Afectación exitosa",
                                    mensaje,
                                    "success"
                                );
                                let ruta = window.location.href;
                                let ruta2 = ruta.split("/");
                                console.log(ruta, ruta2);
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
    }; //Fin de afectar

    $("#afectar-boton").on("click", function (e) {
        let estatus = jQuery("#status").text().trim();
        if (
            $("#select-movimiento").val() === "Sol. de Cheque/Transferencia" ||
            $("#select-movimiento").val() === "Solicitud Depósito"
        ) {
            $("#modalAfectar").modal("show");

            if ($("#select-movimiento").val() === "Sol. de Cheque/Transferencia") {
                $("#chequeRadio").show();
                $("#depositoRadio").hide();
                $("#accionDeposito").hide();
            } else {
                $("#depositoRadio").show();
                $("#chequeRadio").hide();
                $("#accionCheque").hide();
            }
        }

        //Mostrar unicamente el movimiento solicitud cheque

        if (
            $("#select-movimiento").val() === "Transferencia Electrónica" &&
            estatus === "INICIAL"
        ) {
            //Concluimos el cheque}
            afectar(e);
        }

        if (
            $("#select-movimiento").val() === "Depósito" &&
            estatus === "INICIAL"
        ) {
            //Concluimos el cheque}
            afectar(e);
        }

        if (
            $("#select-movimiento").val() === "Ingreso" &&
            estatus === "INICIAL"
        ) {
            //Concluimos el cheque
            afectar(e);
        }

        if (
            $("#select-movimiento").val() === "Egreso" &&
            estatus === "INICIAL"
        ) {
            //Concluimos el Egreso
            afectar(e);
        }

        if (
            $("#select-movimiento").val() === "Traspaso Cuentas" &&
            estatus === "INICIAL"
        ) {
            //Concluimos el Egreso
            afectar(e);
        }
    });

    const cancelar = function (e) {
        e.preventDefault();
        let status = $("#status").text().trim();
        let movimiento = $("#select-movimiento").val();
        const folio = $("#folio").val();
        const id = $("#id").val();
        const tipo = $("#tipoCuenta").val();

        if (
            (status === "FINALIZADO" && movimiento === "Ingreso") ||
            (status === "FINALIZADO" && movimiento === "Transferencia Electrónica") ||
            (status === "FINALIZADO" && movimiento === "Depósito") ||
            (status === "FINALIZADO" && movimiento === "Egreso") ||
            (status === "FINALIZADO" && movimiento === "Traspaso Cuentas")
        ) {
            swal({
                title: "¿Estás seguro?",
                text: "Se cancelará el movimiento: " + movimiento + " " + folio,
                icon: "warning",
                buttons: true,
                dangerMode: true,
                buttons: ["Cancelar", "Aceptar"],
            }).then((willDelete) => {
                if (willDelete) {
                    $("#loader").show();
                    $.ajax({
                        url: "/cancelarTesoreria/",
                        type: "GET",
                        data: {
                            id,
                            movimiento,
                            folio,
                            tipo,
                        },
                        success: function ({ estatus, mensaje }) {
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

        // console.log(status, movimiento, folio, id);
    };

    const eliminar = function (e) {
        e.preventDefault();

        const id = $("#id").val();

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
                    $("#loader").show();
                    $.ajax({
                        url: "/eliminarMovTeso",
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
                                        "/gestion_finanzas/tesoreria";
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

    let estatus = jQuery("#status").text().trim();
    if (estatus === "CANCELADO" || estatus === "INICIAL") {
        jQuery("#cancelar-boton").unbind("click");
        jQuery("#cancelar-boton").hide();
    } else {
        jQuery("#cancelar-boton").click(cancelar);
        jQuery("#cancelar-boton").show();
    }

    if (estatus === "INICIAL") {
        jQuery("#eliminar-boton").click(eliminar);
        jQuery("#eliminar-boton").show();
    } else {
        jQuery("#eliminar-boton").unbind("click");
        jQuery("#eliminar-boton").hide();
    }

    function validateImporte() {
        const importe = $("#importeInput").val();
        if (importe === "" || importe == "0" || importe == "0.00") {
            return true;
        } else {
            return false;
        }
    }

    function validateFormaPago() {
        const estadoFormaPago =
            $("#select-PaymentMethod").val() === "" ? true : false;

        if (estadoFormaPago) {
            return true;
        } else {
            return false;
        }
    }

    function validateCuentaTipoCaja() {
        const tipoCuenta = $("#tipoCuentaTrans").val();

        if (tipoCuenta !== "Caja") {
            return true;
        }

        return false;
    }

    function validateCuentaTipoOri() {
        const tipoCuenta = $("#tipoCuentaTrans").val();

        if (tipoCuenta === "Caja") {
            return true;
        }

        return false;
    }

    function validateCuentaTipoDes() {
        const tipoCuenta = $("#tipoCuentaDTrans").val();

        if (tipoCuenta === "Caja") {
            return true;
        }

        return false;
    }

    function validarCuentaTrans() {
        const cuentaTrans = $("#cuentaTrans").val() === "" ? true : false;
        if (cuentaTrans) {
            return true;
        }
        return false;
    }
    function validarCuentaDes() {
        const cuentaDe = $("#cuentaDKeyInput").val() === "" ? true : false;
        if (cuentaDe) {
            return true;
        }
        return false;
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

    function validateProveedor() {
        const estadoProveedor = $("#proveedorKey").val() === "" ? true : false;

        if (estadoProveedor) {
            return true;
        } else {
            return false;
        }
    }

    function validateBeneficiario() {
        const beneficiario =
            $("#beneficiarioInput").val() === "" ? true : false;

        if (beneficiario) {
            return true;
        } else {
            return false;
        }
    }

    function validarMonedas() {
        const moneda = $("#select-search-hided").val();
        const cuentaMoneda = $("#tipoCambio2").val();

        if (moneda !== cuentaMoneda) {
            return true;
        }

        return false;
    }

    function validarMonedasTransferencia() {
        const cuentaMoneda = $("#tipoCambio2").val();
        const cuentaMonedaDestino = $("#tipoCambioDestino2").val();

        if (cuentaMonedaDestino !== cuentaMoneda) {
            return true;
        }

        return false;
    }

    function validarCuentasIguales() {
        const cuentaMoneda = $("#cuentaTrans").val();
        const cuentaMonedaDestino = $("#cuentaDKeyInput").val();

        if (cuentaMonedaDestino === cuentaMoneda) {
            return true;
        }

        return false;
    }

    $("#btn-modal-afectar").on("click", function (e) {
        e.preventDefault();
        let radioCheque = $("#chequeRadio").is(":checked");
        let radioDeposito = $("#depositoRadio").is(":checked");
        // let radioChequeElectronico = $("#chequeElectronicoRadio").is(
        //     ":checked"
        // );
        // let radioEgreso = $("#egresoRadio").is(":checked");

        if (radioCheque === true) {
            // $('#select-movimiento').val('Cheque');
            // $('#status').val('INICIAL');
            $("#origin").val("Transferencia Electrónica");
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
                $("#id").val(0);
                $("#origin").val("Transferencia Electrónica");
                $("#progressWizard").submit();
            }
        }

        if (radioDeposito === true) {
            $("#origin").val("Depósito");
            $("#modalAfectar").modal("hide");
            //Verificamos si el usuario tiene permisos para afectar
            //obtenemos el valor del origin
            let mov = $("#origin").val();
            let $domPermiso = mov.replace(/\s/g, "");

            if ($("#" + $domPermiso).val() !== "true") {
                showMessage2(
                    "Permisos Insuficientes",
                    "No tienes permisos para afectar",
                    "warning"
                );
            } else {
                $("#id").val(0);
                $("#progressWizard").submit();
            }
        }
    });

    $cuentaKey.change((e) => {
        let cuenta = e.target.value;
        if (cuenta !== null && cuenta !== "") {
            //Consultar el saldo de la cuenta
            $.ajax({
                url: "/tesoreria/saldo/cuenta/",
                method: "GET",
                data: {
                    cuenta: cuenta,
                },
                success: function ({ status, data }) {
                    if (status === 200) {
                        let formato = "$0,00";
                        if (data.moneyAccountsBalance_balance !== null) {
                            formato = currency(
                                data.moneyAccountsBalance_balance,
                                {
                                    separator: ",",
                                    presicion: 2,
                                    symbol: "$",
                                }
                            ).format();
                        } else {
                            formato = currency(0, {
                                separator: ",",
                                presicion: 2,
                                symbol: "$",
                            }).format();
                        }

                        $("#saldoCuenta").val(formato);
                    }
                },
            });
        }
    });

    const nombreCuenta = (cuenta, idInput) => {
        if (cuenta !== null && cuenta !== "") {
            //Consultar el nombre de la cuenta
            $.ajax({
                url: "/tesoreria/nombre/cuenta/",
                method: "GET",
                data: {
                    cuenta: cuenta,
                },
                success: function ({ status, data }) {
                    // console.log(data);
                    if (status === 200) {
                        $("#" + idInput).val(
                            data.instFinancial_name +
                                " - " +
                                data.moneyAccounts_numberAccount
                        );
                    }
                },
            });
        }
    };

    $("#cuentaTrans").change((e) => {
        let cuenta = e.target.value;
        // console.log(cuenta);

        if (cuenta !== null && cuenta !== "") {
            //Consultamos el nombre de la cuenta
            nombreCuenta(cuenta, "claveBancaria");
            //Consultar el saldo de la cuenta
            $.ajax({
                url: "/tesoreria/saldo/cuenta/",
                method: "GET",
                data: {
                    cuenta: cuenta,
                },
                success: function ({ status, data }) {
                    if (status === 200) {
                        let formato = "$0,00";
                        if (data.moneyAccountsBalance_balance !== null) {
                            formato = currency(
                                data.moneyAccountsBalance_balance,
                                {
                                    separator: ",",
                                    presicion: 2,
                                    symbol: "$",
                                }
                            ).format();
                        } else {
                            formato = currency(0, {
                                separator: ",",
                                presicion: 2,
                                symbol: "$",
                            }).format();
                        }

                        $("#saldoCuenta").val(formato);
                    }
                },
            });
        }
    });

    $("#cuentaDKeyInput").change((e) => {
        let cuenta = e.target.value;

        if (cuenta !== "") {
            //Consultamos el nombre de la cuenta
            nombreCuenta(cuenta, "claveBancariaDInput");
            //Consultar el saldo de la cuenta
        }
    });

    // function jsonTesoreriaDetails() {
    //     let dinListaDetails = {};
    //     const inputSaveArticulo = $("#inputDataDin");

    //     dinListaDetails = {
    //         id: $("#id").val(),
    //         aplica: $("#aplicaInput").val(),
    //         aplicaC: $("#aplicaCInput").val(),
    //         importe: $("#importeInput")
    //             .val()
    //             .replace(/[',', '$']/g, ""),
    //         formaPago: $("#formaPInput").val(),
    //     };

    //     inputSaveArticulo.attr("name", "dataArticulosJson");
    //     inputSaveArticulo.attr("value", JSON.stringify(cxpListaDetails));

    //     return cxpListaDetails;
    // }

    $("#importeInput").change((e) => {
        let importeFormato = currency(e.target.value, {
            separator: ",",
            presicion: 2,
            symbol: "$",
        }).format();
        $("#importeInput").val(importeFormato);
    });

    $cuentaKey.change();
    $("#cuentaTrans").change();
    $("#cuentaDKeyInput").change();
    $("#select-movimiento").change();

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
        // console.log(jsonFlujo);
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
    let movimientoSelected = $("#select-movimiento").val();

    if (
        movimientoSelected === "Depósito" ||
        movimientoSelected === "Transferencia Electrónica" ||
        movimientoSelected === "Traspaso Cuentas" ||
        movimientoSelected === "Egreso" ||
        movimientoSelected === "Ingreso"
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
}); //Fin del jQuery

function disabledPendiente() {
    let estatus = $("#status").text().trim();
    console.log(estatus);
    if (estatus == "POR AUTORIZAR") {
        $("#select-movimiento").attr("readonly", true);
        $("#select-search-hided").attr("readonly", true);

        $("#select-moduleConcept").attr("readonly", true);
        $("#select-PaymentMethod").attr("readonly", true);
        $("#cuentaModal").attr("disabled", true);
        $("#fechaEmision").attr("readonly", true);
        $("#cuentaDModal").attr("disabled", true);
        $("#saldoProveedor").attr("readonly", true);
        $("#importeInput").attr("readonly", true);
        $("#beneficiarioInput").attr("readonly", true);
        $("#cuentaKey").attr("readonly", true);
        $("#cuentaDKey").attr("readonly", true);
        $("#observacionesInput").attr("readonly", true);
        $("#proveedorReferencia").attr("readonly", true);
        $("#status").attr("disabled", true);
        $("#botonForm").hide();
        $("#status").attr("disabled", false);
        $("#saldoCuenta").attr("readonly", true);
    }
    if (estatus == "FINALIZADO" || estatus === "CANCELADO") {
        $("#select-movimiento").attr("readonly", true);
        $("#select-search-hided").attr("readonly", true);
        $("#select-moduleConcept").attr("readonly", true);
        $("#select-PaymentMethod").attr("readonly", true);
        $("#cuentaModal").attr("disabled", true);
        $("#cuentaDModal").attr("disabled", true);
        $("#fechaEmision").attr("readonly", true);
        $("#saldoProveedor").attr("readonly", true);
        $("#importe").attr("readonly", true);
        $("#beneficiarioInput").attr("readonly", true);
        $("#cuentaKey").attr("readonly", true);
        $("#cuentaDKey").attr("readonly", true);
        $("#observacionesInput").attr("readonly", true);
        $("#proveedorReferencia").attr("readonly", true);
        $("#status").attr("disabled", true);
        $("#botonForm").hide();
        $("#status").attr("disabled", false);
        $("#saldoCuenta").attr("readonly", true);
        $(".import").attr("readonly", true);

        $("#beneficiarioModal").attr("disabled", true);
    }
}
