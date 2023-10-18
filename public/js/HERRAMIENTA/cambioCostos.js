


jQuery(document).ready(function () {
    jQuery("#listCompras").select2({
        minimumResultsForSearch: -1,
    });

    tablaArticulos = jQuery("#shTable10").DataTable({
        language: language,
        searching: false,
        paging: false,
        info: false,
        responsive: true,
        ordering: false,
        scrollX: true,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    //verificar que solo seleccione un checkbox
    jQuery(".indicadores").click(function () {
        jQuery(".indicadores").not(this).prop("checked", false);
    });

    jQuery("#listCompras").change(function () {
        var id = jQuery(this).val();
        jQuery.ajax({
            url: "/herramientas/cambioCostos/listas",
            type: "GET",
            data: {
                id: id,
            },
            success: function (data) {
                tablaArticulos.clear().draw();

                jQuery.each(data, function (i, item) {
                    let ultimoCosto = currency(
                        item.articlesList_lastCost
                    ).format();
                    let costoPromedio = currency(
                        item.articlesList_averageCost
                    ).format();

                    tablaArticulos.row
                        .add([
                            item.articlesList_article,
                            item.articlesList_nameArticle,
                            ultimoCosto,
                            costoPromedio,
                            //el value del input contendra lo que se va a enviar al controlador
                            `<input type="text" id="costoNuevo-${item.articlesList_article}" class="inputCosto">`,
                        ])
                        .draw(false);
                });

                // jQuery("#indicadores").html(data);
            },
        });
    });

    //utilizamos el id costoNuevo-${item.articlesList_article}" para identificar el input, y cambiar el valor del input
    jQuery("#shTable10").on("change", ".inputCosto", function () {
        let id = jQuery(this).attr("id");
        let value = jQuery(this).val();
        let idArticulo = id.split("-")[1];

        //hacemos que tenga el formato de moneda
        value = currency(value).format();

        //si el usuario solo cambia un dato, los demas se mantienen
        tablaArticulos.rows().every(function (rowIdx, tableLoop, rowLoop) {
            let data = this.data();
            if (data[0] === idArticulo) {
                data[4] = `<input type="text" id="costoNuevo-${idArticulo}" class="inputCosto" value="${value}">`;
                this.data(data);
            }
            
        });
            
    });

    jQuery("#btnAplicar").click(function () {
        const isValid = validarOpciones();
        if (!isValid) {
            swal("Error", "Debe seleccionar una opción", "error");
            return;
        }

        const isValid2 = validarSelect();
        if (isValid2) {
            swal("Error", "Debe seleccionar una base", "error");
            return;
        }

        const isValid3 = validarIndicadores();
        if (!isValid3) {
            swal("Error", "Debe seleccionar un indicador", "error");
            return;
        }

        const isValid4 = validarValor();
        if (isValid4) {
            swal("Error", "Debe ingresar un valor", "error");
            return;
        }

        let opcion = jQuery(".opciones:checked").attr("id");
        let base = jQuery("#bases").val();
        let indicador = jQuery(".indicadores:checked").attr("id");
        let valor = jQuery("#valor").val();

        if (base === "Ultimo Costo") {
            tablaArticulos.rows().every(function (rowIdx, tableLoop, rowLoop) {
                let data = this.data();
                console.log(data);
                let lastCost = data[2].replace(/[$,]/g, "");
                let newCost = 0;
                if (opcion === "porcentaje") {
                    if (indicador === "positivo") {
                        newCost = lastCost * (valor / 100);
                        newCost = parseFloat(newCost) + parseFloat(lastCost);
                    } else {
                        newCost = lastCost * (valor / 100);
                        newCost = parseFloat(lastCost) - parseFloat(newCost);
                    }
                    newCost = currency(newCost).format();
                } else {
                    if (indicador === "positivo") {
                        newCost = parseFloat(lastCost) + parseFloat(valor);
                    } else {
                        newCost = parseFloat(lastCost) - parseFloat(valor);
                    }

                    newCost = currency(newCost).format();
                }
                data[4] = `<input type="text"  id="costoNuevo-${data[0]}" class="inputCosto" value="${newCost}">`;
                this.data(data);
            });
        }

        if (base === "Costo Promedio") {
            tablaArticulos.rows().every(function (rowIdx, tableLoop, rowLoop) {
                let data = this.data();
                let costoProm = data[3].replace(/[$,]/g, "");
                let newCost = 0;
                if (opcion === "porcentaje") {
                    if (indicador === "positivo") {
                        newCost = costoProm * (valor / 100);
                        newCost = parseFloat(newCost) + parseFloat(costoProm);
                    } else {
                        newCost = costoProm * (valor / 100);
                        newCost = parseFloat(costoProm) - parseFloat(newCost);
                    }
                    newCost = currency(newCost).format();
                } else {
                    if (indicador === "positivo") {
                        newCost = parseFloat(costoProm) + parseFloat(valor);
                    } else {
                        newCost = parseFloat(costoProm) - parseFloat(valor);
                    }

                    newCost = currency(newCost).format();
                }
                data[4] = `<input type="text"  id="costoNuevo-${data[0]}" class="inputCosto" value="${newCost}">`;
                this.data(data);
            });
        }

        //cerrar modal
        jQuery("#modalCambioCostos").modal("hide");
    });

    jQuery("#btn-procesar").click(function (e) {
        e.preventDefault();

        // const isValid = validarInputs();

        // if (isValid) {
        //     swal(
        //         "Error",
        //         "Debe agregar los costos nuevos de los articulos",
        //         "error"
        //     );
        // } else {
            swal({
                title: "¿Está seguro de realizar el cambio de costos?",
                text: "Se cambiarán los costos de los artículos seleccionados",
                icon: "warning",
                buttons: true,
                buttons: ["Cancelar", "Aceptar"],
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    jsonArticulos();
                    $("#loader").show();
                    jQuery.ajax({
                        url: "/herramientas/cambioCostos/procesar",
                        type: "POST",
                        data: jQuery("#progressWizard").serialize(),
                        success: function ({ estatus, mensaje }) {
                            $("#loader").hide();
                            disabledHerramienta();
                            if (estatus) {
                                swal("Éxito", mensaje, "success");
                                
                                setTimeout(function () {
                                    location.reload();
                                }, 2000);
                            } else {
                                swal("Error", mensaje, "error");
                            }
                        },
                    });
                }
            });
        // }
    });

    function validarOpciones() {
        let estado = false;
        jQuery(".opciones").each(function () {
            if (jQuery(this).is(":checked")) {
                estado = true;
            }
        });

        return estado;
    }

    function validarIndicadores() {
        let estado = false;
        jQuery(".indicadores").each(function () {
            if (jQuery(this).is(":checked")) {
                estado = true;
            }
        });

        return estado;
    }

    function validarSelect() {
        let estado = false;
        if (jQuery("#bases").val() === "" || jQuery("#bases").val() === null) {
            estado = true;
        }

        return estado;
    }

    function validarValor() {
        let estado = false;
        if (jQuery("#valor").val() === "" || jQuery("#valor").val() === null) {
            estado = true;
        }

        return estado;
    }

    function validarInputs() {
        let estado = false;
        jQuery(".inputCosto").each(function () {
            if (jQuery(this).val() === "" || jQuery(this).val() === null) {
                estado = true;
            }
        });

        return estado;
    }

    function jsonArticulos() {
        let articulosLista = {};
        const inputSaveArticulo = $("#inputDataArticles");

        tablaArticulos.rows().every(function (rowIdx, tableLoop, rowLoop) {
            let data = this.data();
             console.log(data);
            // return;
            let articulo = data[0];
            let nombre = data[1];
            let costo= '$0.00';
            let inicioPalabra = data[4].search("value");

            console.log(inicioPalabra);

            if(inicioPalabra === 56 || inicioPalabra === 57 || inicioPalabra === 58){ 
             costo = data[4].substring(
                inicioPalabra + 7,
                data[4].length - 2
            );
            }

            //sacar el valor del input
            // let costo = data[4].val();

            articulosLista[articulo] = {
                articulo: articulo,
                nombre: nombre,
                costo: costo,
            };

            console.log(data, articulosLista);
            return;
        });

        inputSaveArticulo.val(JSON.stringify(articulosLista));
        console.log(inputSaveArticulo.val());
        return articulosLista;
    }

    function disabledHerramienta() {
        jQuery("#listCompras").attr("readonly", true);
        jQuery("#btn-asistente").attr("disabled", true);

        jQuery("#shTable10 tbody tr").each(function () {
            jQuery(this)
                .find("td")
                .each(function () {
                    jQuery(this).find("input").attr("disabled", true);
                });
        });
    }
});
