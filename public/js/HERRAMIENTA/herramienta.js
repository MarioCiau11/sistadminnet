let tablaArticulos;


jQuery(document).ready(function () {

    jQuery(
        "#select-empresa, #select-sucursal, #select-empresaDestino, #select-almacen, #select-sucursalDestino, #select-almacenDestino"
    ).select2({
        minimumResultsForSearch: -1,
    });

    tablaArticulos = jQuery("#shTable1").DataTable({
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
            button: "OK",
        });
    }

    jQuery("#select-almacen").on("change", function (event) {

        let company = jQuery("#select-empresa").val();
        let branch = jQuery("#select-sucursal").val();
        let almacen = event.target.value;

        tablaArticulos.clear().draw();
        $("#loader").show();

        jQuery.ajax({
            url: "/getArticulosAlmacen",
            type: "GET",
            dataType: "json",
            data: {
                company,
                branch,
                almacen,
            },
            success: function ({estatus, data}) {
                $("#loader").hide();
                console.log(data);
                if(estatus === 200){
                   data.forEach((element) => {

                    let inventario = currency(
                        element.articlesInv_inventory,
                        {
                            separator: ",",
                            precision: element.units_decimalVal,
                            symbol: "",
                        }
                    ).format();

                    let costo = currency( element.articlesCost_averageCost, {
                        separator: ",",
                        precision: 2,
                        symbol: "",
                    }).format();

                    let costo2 = currency( element.articlesCost_lastCost, {
                        separator: ",",
                        precision: 2,
                        symbol: "",
                    }).format();
                    tablaArticulos.row.add([
                            element.articlesInv_article,
                            element.articles_descript,
                            inventario,
                            `<input type="text" class="form-control input1 minLargo" value="0" placeholder="Cantidad" name="cantidad[]" id="cantidad-${element.articlesInv_article}" onchange="formatoNumero('${element.articlesInv_article}')">
                            <input type="hidden" name="cantidad[]" value="${element.articles_type}" id="tipo-${element.articlesInv_article}">`,
                            `<input type="text" class=" form-control input2" value="${element.articlesCost_lastCost == 0.00 ? costo : costo2 }" id="costo-${element.articlesInv_article}" onchange="formatoNumero2('${element.articlesInv_article}')">`,
                        ]).draw();


                            
                     })
                }
                },
        });
    });

    jQuery("#btn-procesar").on("click", function (e) {
        e.preventDefault();

        const validacion = validarObligatorios();
        const cantidades = validarCantidades();
        const costos = validarCostos();

        // const cantidadesMayores = validarCantidadesMayores();

        if(validacion){
            showMessage(
                "Favor de llenar todos los campos requeridos",
                "error"
            );
        }else if(cantidades){
            showMessage(
                "No hay articulos seleccionados",
                "error"
            );
        }else if(costos){
            showMessage(
                "Favor de llenar todos los campos de costo",
                "error"
            );
        }else {
            swal({
                title: '¿Procesar Movimiento?',
                text: "Confirmar Operación",
                icon: "warning",
            }).then(willDelete => {
                if (willDelete) {
                    jsonArticulos();
                    $("#loader").show();
                    jQuery.ajax({
                        url: "/herramientas/store",
                        type: "POST",
                        data: jQuery("#progressWizard").serialize(),
                        success: function ({estatus, mensaje}) {
                            if(estatus === 200){
                                $("#loader").hide();
                                showMessage2(
                                    "Éxito",
                                    mensaje,
                                    "success"
                                );

                                disabledHerramienta();

                            }else{
                                showMessage2(
                                    "Error",
                                    mensaje,
                                    "error"
                                );
                                $("#loader").hide();
                            }
                            
                        },
                    });
                }
            });
        }

       

       
    });


    jQuery("#select-empresaDestino").on("change",async function (event) {
        jQuery("#select-sucursalDestino").attr("readonly", false);
        $('#select-sucursalDestino')
        .children()
        .remove()
        .end()
        .append('<option value="">Cargando...</option>');
        jQuery("#select-sucursalDestino").val('').trigger("change");
        await jQuery.ajax({
            url: "/getSelectSucursales",
            type: "GET",
            dataType: "json",
            data: {
                company: event.target.value,
            },
            success: function ({ estatus, datos }) {


                if (estatus === 200) {
                    $("#select-sucursalDestino").children().remove();
                    $("#select-sucursalDestino").append(`<option value="">Seleccione uno...</option>`)
                    
                    datos.forEach((element) => {
                        $("#select-sucursalDestino").append(
                            `<option value="${element.branchOffices_key}">${element.branchOffices_name}</option>`
                        );
                    });

                    $('#select-sucursalDestino').val('').trigger('change');
                }
            },
        });

        //  jQuery("#select-empresaDestino").attr("readonly", true);
    });


    jQuery("#select-sucursalDestino").on("change", function (event) {

      let isVacio = $("#select-sucursalDestino").val() == "" ? true : false;

      if(!isVacio){
        jQuery("#select-almacenDestino").attr('readonly', false);
        $('#select-almacenDestino')
        .children()
        .remove()
        .end()
        .append('<option value="">Cargando...</option>');
        jQuery("#select-almacenDestino").val('').trigger("change");
        jQuery.ajax({
            url: "/getSelectAlmacenes",
            type: "GET",
            dataType: "json",
            data: {
                sucursal: event.target.value,
            },
            success: function ({ estatus, datos }) {
                if (estatus === 200) {
                    $("#select-almacenDestino").children().remove();
                    $("#select-almacenDestino").append(`<option value="">Seleccione uno...</option>`)
                    datos.forEach((element) => {
                        $("#select-almacenDestino").append(
                            `<option value="${element.depots_key}">${element.depots_name}</option>`
                        );
                    });
                    $('#select-almacenDestino').val('').trigger('change');
                }
            },
        });
      }

        // jQuery("#select-sucursalDestino").attr("readonly", true);
    });


    function validarObligatorios() {
        let estado = false;

        if (jQuery("#select-almacen").val() === "" || jQuery("#select-almacenDestino").val() === ""){
            estado = true;
        }

        return estado;

    }

    function validarCostos() {
        let estado = false;
        jQuery(".input2").each(function (index, element) {
            
            if (jQuery(element).val() === "" || jQuery(element).val() === "0.00" || jQuery(element).val() === "0"){
                estado = true;
            }
        });

        return estado;

    }

    function validarCantidades(){
        let estado = true;

        //buscar que al menos una cantidad sea mayor a 0
        jQuery("input[name*=cantidad]").each(function(key, element){
            if (jQuery(element).val().replace(/[$,]/g, "") > 0){
                estado = false;
            }
        });

        return estado;
    }

    function validarCantidadesMayores(){
        let estado = false;

        jQuery("#shTable1 tbody tr").each(function (row, tr) {
            let cantidad = jQuery(tr).find("input:eq(0)").val();
            let id = jQuery(tr).find("input:eq(0)")[0].id;
            let existencia = jQuery(tr).find("td:eq(2)").html();

            let valor1 = parseFloat(cantidad);
            let valor2 = parseFloat(existencia);


            if(valor1 > valor2){
                estado = true;
            }
        });
        return estado;
    }

    function disabledHerramienta() {
        jQuery("#select-empresaDestino").attr("readonly", true);
        jQuery("#select-sucursalDestino").attr("readonly", true);
        jQuery("#select-almacenDestino").attr("readonly", true);
        jQuery("#select-almacen").attr("readonly", true);
        jQuery(".input1").attr("readonly", true);

        
    }
  
});

function formatoNumero(clave) {
    let valor = jQuery("#cantidad-" + clave).val();

    if (valor === "") {
        valor = 0;
    }

    if(valor != 0){
        let resultadoFormato = currency(valor, { separator: ",", precision: 2, symbol: "" }).format();

        jQuery("#cantidad-" + clave).val(resultadoFormato);
    }


}

function formatoNumero2(clave) {
    let valor = jQuery("#costo-" + clave).val();

    if (valor === "") {
        valor = 0;
    }

    if(valor != 0){
        let resultadoFormato = currency(valor, { separator: ",", precision: 2}).format();

        jQuery("#costo-" + clave).val(resultadoFormato);
    }


}


// formar json a partir de los valores de la tabla
function jsonArticulos() {
    let articulosLista = {};
    const inputSaveArticulo = $("#inputDataArticles");



    jQuery("#shTable1 tbody tr").each(function (row, tr) {

        let cantidad = jQuery(tr).find("input:eq(0)").val().replace(/[$,]/g, "");
        let articulo = jQuery(tr).find("td:eq(0)").html();
        let descripcion = jQuery(tr).find("td:eq(1)").html();
        let costo = jQuery(tr).find("input:eq(2)").val().replace(/[$,]/g, "");;
        let tipo = jQuery(tr).find("input:eq(1)").val();
        let importeTotal = cantidad * costo;

        if (cantidad > 0) {   
                articulosLista[articulo] = {
                    descripcion: descripcion,
                    cantidad: cantidad,
                    costo: costo,
                    importeTotal: importeTotal,
                    tipo: tipo,
                }
            }
            
    });

    inputSaveArticulo.val(JSON.stringify(articulosLista));
    console.log(inputSaveArticulo.val());
    return articulosLista;

}