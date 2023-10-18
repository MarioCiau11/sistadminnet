


jQuery(document).ready(function () {
    
     $.datepicker.setDefaults($.datepicker.regional["es"]);

    const $fecha_rango = $('.fecha-rango');
    const $fechaInicio = $('#fechaInicial');
    const $fechaFinal = $('#fechaFinal');
    const $fecha_select = jQuery('#fechaSelect');

    $fecha_select.val() == 'Rango Fechas' ? $fecha_rango.show() : $fecha_rango.hide();

    const $fecha_rango2 = $('.fecha-rango2');
    const $fechaInicio2 = $('#fechaInicial2');
    const $fechaFinal2 = $('#fechaFinal2');
    const $fecha_select2 = jQuery('#fechaSelect2');

    $fecha_select2.val() == 'Rango Fechas' ? $fecha_rango2.show() : $fecha_rango2.hide();

    const $fecha_rango4 = $('.fecha-rango4');
    const $fechaInicio4 = $('#fechaInicial4');
    const $fechaFinal4 = $('#fechaFinal4');
    const $fecha_select4 = jQuery('#fechaSelect4');

    $fecha_select4.val() == 'Rango Fechas' ? $fecha_rango4.show() : $fecha_rango4.hide();

    const $fecha_rango5 = $('.fecha-rango5');
    const $fechaInicio5 = $('#fechaInicial5');
    const $fechaFinal5 = $('#fechaFinal5');
    const $fecha_select5 = jQuery('#fechaSelect5');

    $fecha_select5.val() == 'Rango Fechas' ? $fecha_rango5.show() : $fecha_rango5.hide();

    const $fecha_rango6 = $('.fecha-rango6');
    const $fechaInicio6 = $('#fechaInicial6');
    const $fechaFinal6 = $('#fechaFinal6');
    const $fecha_select6 = jQuery('#fechaSelect6');

    $fecha_select6.val() == 'Rango Fechas' ? $fecha_rango6.show() : $fecha_rango6.hide();


    const $form = jQuery('#formValidate');

    jQuery('.select-search-hide, .select-search-hided').select2({
        minimumResultsForSearch: -1
    });

    jQuery('.datepicker').datepicker({
        dateFormat: 'yy/mm/dd',
    });
    $fecha_select.on('change', function() {
        let option = jQuery(this).val();

        if (option === 'Rango Fechas') {
            $fecha_rango.show();
        } else {
            $fechaInicio.val('');
            $fechaFinal.val('');
            $fecha_rango.hide();
        }
    });
    $fecha_select2.on('change', function() {
        let option = jQuery(this).val();

        if (option === 'Rango Fechas') {
            $fecha_rango2.show();
        } else {
            $fechaInicio2.val('');
            $fechaFinal2.val('');
            $fecha_rango2.hide();
        }
    });
    $fecha_select4.on('change', function () {
        let option = jQuery(this).val();

        if (option === 'Rango Fechas') {
            $fecha_rango4.show();
        } else {
            $fechaInicio4.val('');
            $fechaFinal4.val('');
            $fecha_rango4.hide();
        }
    });

    $fecha_select5.on('change', function () {
        let option = jQuery(this).val();

        if (option === 'Rango Fechas') {
            $fecha_rango5.show();
        } else {
            $fechaInicio5.val('');
            $fechaFinal5.val('');
            $fecha_rango5.hide();
        }
    });

    $fecha_select6.on('change', function () {
        let option = jQuery(this).val();

        if (option === 'Rango Fechas') {
            $fecha_rango6.show();
        } else {
            $fechaInicio6.val('');
            $fechaFinal6.val('');
            $fecha_rango6.hide();
        }
    });

    $form.validate({
        rules: {
            fechaInicio: {
                required: true,
                date: true
            },
            fechaFinal: {
                required: true,
                date: true
            }
        },
        messages: {
            fechaInicio: {
                required: 'Ingrese una fecha de inicio',
                date: 'Ingrese una fecha válida'
            },
            fechaFinal: {
                required: 'Ingrese una fecha de fin',
                date: 'Ingrese una fecha válida'
            },
            fechaInicio2: {
                required: 'Ingrese una fecha de inicio',
                date: 'Ingrese una fecha válida'
            },
            fechaFinal2: {
                required: 'Ingrese una fecha de fin',
                date: 'Ingrese una fecha válida'
            },
            fechaInicio4: {
                required: 'Ingrese una fecha de inicio',
                date: 'Ingrese una fecha válida'
            },
            fechaFinal4: {
                required: 'Ingrese una fecha de fin',
                date: 'Ingrese una fecha válida'
            },
            fechaInicio5: {
                required: 'Ingrese una fecha de inicio',
                date: 'Ingrese una fecha válida'
            },
            fechaFinal5: {
                required: 'Ingrese una fecha de fin',
                date: 'Ingrese una fecha válida'
            },
            fechaInicio6: {
                required: 'Ingrese una fecha de inicio',
                date: 'Ingrese una fecha válida'
            },
        },
        highlight: function(element) {
            jQuery(element).closest(".form-group").addClass("has-error");
        },
        unhighlight: function(element) {
            jQuery(element).closest(".form-group").removeClass("has-error");
        },
        success: function(element) {
            jQuery(element).closest(".form-group").removeClass("has-error");
        },
    });

    let dashboard1 = $("#dashboard1").val();
    let dashboard2 = $("#dashboard2").val();
    let dashboard3 = $("#dashboard3").val();
    let dashboard4 = $("#dashboard4").val();
    let dashboard5 = $("#dashboard5").val();
    let dashboard6 = $("#dashboard6").val();

    //graficos
        const desiredWidth = 364; // Ancho deseado en píxeles
        const desiredHeight = 364; // Alto deseado en píxeles
    

    if (dashboard1 == 1) {
        document.getElementById('myChart').width = desiredWidth;
        document.getElementById('myChart').height = desiredHeight;
    }
    if (dashboard2 == 1) {
    document.getElementById('myChart2').width = desiredWidth;
    document.getElementById('myChart2').height = desiredHeight;
    }
    if (dashboard3 == 1) {
        document.getElementById('myChart3').width = desiredWidth;
        document.getElementById('myChart3').height = desiredHeight;
    }
    if (dashboard4 == 1) {
        document.getElementById('myChart4').width = desiredWidth;
        document.getElementById('myChart4').height = desiredHeight;
    }
    if (dashboard5 == 1) {
        document.getElementById('myChart5').width = desiredWidth;
        document.getElementById('myChart5').height = desiredHeight;
    }
    if (dashboard6 == 1) {
        document.getElementById('myChart6').width = desiredWidth;
        document.getElementById('myChart6').height = desiredHeight;
    }
    
    //--------------------------------------------------------------
    if (dashboard1 == 1) {
        //graficos de top 10 productos mas vendidos
        const ctx = document.getElementById('myChart');
        let chart = null;

        $('#filterButton').click(function () {
            // Obtener los valores de los filtros
            var sucursal = $('#sucursalSelect').val();
            var fecha = $('#fechaSelect').val();
            var fechaInicio = '';
            var fechaFinal = '';

            // Verificar si el filtro seleccionado es "Rango Fechas"
            if (fecha === 'Rango Fechas') {
                fechaInicio = $('#fechaInicial').val();
                fechaFinal = $('#fechaFinal').val();
            }

            // Antes de crear un nuevo gráfico, verificar si hay un gráfico existente y destruirlo
            if (chart) {
                chart.destroy();
            }

            // Realizar la solicitud AJAX para obtener los datos del backend
            $.ajax({
                url: '/api/getArticulos',
                type: 'POST',
                dataType: 'json',
                data: {
                    sucursal: sucursal,
                    fecha: fecha,
                    fechaInicio: fechaInicio,
                    fechaFinal: fechaFinal
                },
                success: function (data) {
                    console.log(data);
                    // Extraer los datos de la respuesta
                    const labels = data.map(item => item.salesDetails_article);
                    const values = data.map(item => item.total);

                    // Crear el nuevo gráfico con los datos obtenidos
                    chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: '#',
                                data: values,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'TOP 10 PRODUCTO MÁS VENDIDO',
                                    font: {
                                        size: 16
                                    }
                                },
                                tooltip: {
                                    enabled: true,
                                    callbacks: {
                                        title: function (context) {
                                            // Mostrar el nombre completo como título del tooltip
                                            const dataIndex = context[0].dataIndex;
                                            return data[dataIndex].salesDetails_descript;
                                        },
                                        label: function (context) {
                                            const value = context.parsed.y;
                                            return 'Unidades: ' + value;
                                        }
                                    },
                                    titleAlign: 'left',
                                    bodyAlign: 'left',
                                    displayColors: false,
                                    bodyFont: {
                                        multi: 'wrap'
                                    }
                                }
                            }
                        }
                    });
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });

        //ahora hacemos para cuando el usuario entre a la pagina
        var sucursal = $('#sucursalSelect').val();
        var fecha = "Mes"
        var fechaInicio = '';
        var fechaFinal = '';

        // Verificar si el filtro seleccionado es "Rango Fechas"
        if (fecha === 'Rango Fechas') {
            fechaInicio = $('#fechaInicial').val();
            fechaFinal = $('#fechaFinal').val();
        }

        // Antes de crear un nuevo gráfico, verificar si hay un gráfico existente y destruirlo
        if (chart) {
            chart.destroy();
        }

        // Realizar la solicitud AJAX para obtener los datos del backend
        $.ajax({
            url: '/api/getArticulos',
            type: 'POST',
            dataType: 'json',
            data: {
                sucursal: sucursal,
                fecha: fecha,
                fechaInicio: fechaInicio,
                fechaFinal: fechaFinal
            },
            success: function (data) {
                console.log(data);
                // Extraer los datos de la respuesta
                const labels = data.map(item => item.salesDetails_article);
                const values = data.map(item => item.total);

                // Crear el nuevo gráfico con los datos obtenidos
                chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '#',
                            data: values,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'TOP 10 PRODUCTO MÁS VENDIDO',
                                font: {
                                    size: 16
                                }
                            },
                            tooltip: {
                                enabled: true,
                                callbacks: {
                                    title: function (context) {
                                        // Mostrar el nombre completo como título del tooltip
                                        const dataIndex = context[0].dataIndex;
                                        return data[dataIndex].salesDetails_descript;
                                    },
                                    label: function (context) {
                                        const value = context.parsed.y;
                                        return 'Cantidad Total: ' + value;
                                    }
                                },
                                titleAlign: 'left',
                                bodyAlign: 'left',
                                displayColors: false,
                                bodyFont: {
                                    multi: 'wrap'
                                }
                            }
                        }
                    }
                });
            }
        });
    }

    if (dashboard2 == 1) {
        //GRAFICO DE VENTAS POR FAMILIA
        const ctx2 = document.getElementById('myChart2');
        let chart2 = null;

        $('#filterButton2').click(function () {
            // Obtener los valores de los filtros
            var sucursal = $('#sucursalSelect2').val();
            var fecha = $('#fechaSelect2').val();
            var fechaInicio = '';
            var fechaFinal = '';

            // Verificar si el filtro seleccionado es "Rango Fechas"
            if (fecha === 'Rango Fechas') {
                fechaInicio = $('#fechaInicial2').val();
                fechaFinal = $('#fechaFinal2').val();
            }

            // Antes de crear un nuevo gráfico, verificar si hay un gráfico existente y destruirlo
            if (chart2) {
                chart2.destroy();
            }

            // Realizar la solicitud AJAX para obtener los datos del backend
            $.ajax({
                url: '/api/getVentasXFamilia',
                type: 'POST',
                dataType: 'json',
                data: {
                    sucursal: sucursal,
                    fecha: fecha,
                    fechaInicio: fechaInicio,
                    fechaFinal: fechaFinal
                },
                success: function (data) {
                    console.log(data);
                    // Extraer los datos de la respuesta
                    const labels = data.map(item => item.articles_family);
                    const values = data.map(item => item.porcentaje);
                    const totalCantidad = data.map(item => item.total_cantidad);
                    const porcentaje = data.map(item => item.porcentaje);


                    // Crear el nuevo gráfico con los datos obtenidos
                    chart2 = new Chart(ctx2, {
                        type: 'pie',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: '#',
                                data: values,
                                borderWidth: 1,
                            
                            }]
                        },
                        options: {
                            cutoutPercentage: '50',
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'VENTAS NETAS POR FAMILIA',
                                    font: {
                                        size: 16
                                    }
                                },
                                tooltip: {
                                    enabled: true,
                                    callbacks: {
                                        title: function (context) {
                                            // Mostrar el nombre completo como título del tooltip
                                            const dataIndex = context[0].dataIndex;
                                            return data[dataIndex].salesDetails_descript;
                                        },
                                        label: function (context) {
                                            const dataIndex = context.dataIndex;
                                            const porceta = parseFloat(porcentaje[dataIndex]).toFixed(2);
                                            const cantidadTotal = totalCantidad[dataIndex];
                                            //devolvemos la cantidad total de productos vendidos y el porcentaje de ventas
                                            return 'Porcentaje: ' + porceta + '%';

                                        }
                                    },
                                    titleAlign: 'left',
                                    bodyAlign: 'left',
                                    displayColors: false,
                                    bodyFont: {
                                        multi: 'wrap'
                                    }
                                }
                            }
                        }
                    });
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });

        //ahora hacemos para cuando el usuario entre a la pagina
        var sucursal = $('#sucursalSelect2').val();
        var fecha = "Mes"
        var fechaInicio = '';
        var fechaFinal = '';

        // Verificar si el filtro seleccionado es "Rango Fechas"
        if (fecha === 'Rango Fechas') {
            fechaInicio = $('#fechaInicial2').val();
            fechaFinal = $('#fechaFinal2').val();
        }

        // Antes de crear un nuevo gráfico, verificar si hay un gráfico existente y destruirlo
        if (chart2) {
            chart2.destroy();
        }

        // Realizar la solicitud AJAX para obtener los datos del backend
        $.ajax({
            url: '/api/getVentasXFamilia',
            type: 'POST',
            dataType: 'json',
            data: {
                sucursal: sucursal,
                fecha: fecha,
                fechaInicio: fechaInicio,
                fechaFinal: fechaFinal
            },
            success: function (data) {
                console.log(data);
                // Extraer los datos de la respuesta
                const labels = data.map(item => item.articles_family);
                const values = data.map(item => item.porcentaje);
                const totalCantidad = data.map(item => item.total_cantidad);
                const porcentaje = data.map(item => item.porcentaje);

                // Crear el nuevo gráfico con los datos obtenidos
                chart2 = new Chart(ctx2, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '#',
                            data: values,
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'VENTAS NETAS POR FAMILIA',
                                font: {
                                    size: 16
                                }
                            },
                            tooltip: {
                                enabled: true,
                                callbacks: {
                                    title: function (context) {
                                        // Mostrar el nombre completo como título del tooltip
                                        const dataIndex = context[0].dataIndex;
                                        return data[dataIndex].salesDetails_article;
                                    },
                                    label: function (context) {
                                        const dataIndex = context.dataIndex;
                                        const porceta = parseFloat(porcentaje[dataIndex]).toFixed(2);
                                        const cantidadTotal = totalCantidad[dataIndex];
                                        //devolvemos la cantidad total de productos vendidos y el porcentaje de ventas
                                        return 'Porcentaje: ' + porceta + '%';
                                    }
                                },
                                titleAlign: 'left',
                                bodyAlign: 'left',
                                displayColors: false,
                                bodyFont: {
                                    multi: 'wrap'
                                }
                            }
                        }
                    }
                });
            }
        });
    }

    if (dashboard3 == 1) {
        const ctx3 = document.getElementById('myChart3');
        let chart3 = null;

        $('#filterButton3').click(function () {
            // Obtener los valores de los filtros
            var sucursal = $('#sucursalSelect3').val();


            // Antes de crear un nuevo gráfico, verificar si hay un gráfico existente y destruirlo
            if (chart3) {
                chart3.destroy();
            }

            // Realizar la solicitud AJAX para obtener los datos del backend
            $.ajax({
                url: '/api/getVentasXMes',
                type: 'POST',
                dataType: 'json',
                data: {
                    sucursal: sucursal,
                },
                success: function (data) {
                    console.log(data);
                    // Extraer los datos de la respuesta
                    const labels = data.map(item => item.branchOffices_name);
                    const salesMonthPrevious = data.map(item => item.venta_anterior);
                    const salesMonthCurrent = data.map(item => item.venta_actual);
                    var currentDate = new Date();
                    var monthNames = [
                        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                    ];
                    var previousMonth = monthNames[currentDate.getMonth() - 1];
                    var currentMonth = monthNames[currentDate.getMonth()];

                    // Crear el nuevo gráfico con los datos obtenidos
                    chart3 = new Chart(ctx3, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: previousMonth,
                                    borderWidth: 1,
                                    data: salesMonthPrevious,
                                },
                                {
                                    label: currentMonth,
                                    borderWidth: 1,
                                    data: salesMonthCurrent,
                                },
                            ],
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function (value) {
                                            return '$' + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                        }
                                    }
                                },
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                },
                                title: {
                                    display: true,
                                    text: 'VENTAS MES ACTUAL VS MES ANTERIOR POR SUCURSAL',
                                    font: {
                                        size: 14,
                                    },
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            var label = context.dataset.label || '';
                                            label += ': $' + context.parsed.y.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                            return label;
                                        }
                                    }
                                }
                            },
                        },
                    });
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });

        //ahora hacemos para cuando el usuario entre a la pagina
        var sucursal = $('#sucursalSelect3').val();

        // Antes de crear un nuevo gráfico, verificar si hay un gráfico existente y destruirlo
        if (chart3) {
            chart3.destroy();
        }

        // Realizar la solicitud AJAX para obtener los datos del backend
        $.ajax({
            url: '/api/getVentasXMes',
            type: 'POST',
            dataType: 'json',
            data: {
                sucursal: sucursal,
            },
            success: function (data) {
                console.log(data);
                // Extraer los datos de la respuesta
                const labels = data.map(item => item.branchOffices_name);
                const salesMonthPrevious = data.map(item => parseFloat(item.venta_anterior));
                const salesMonthCurrent = data.map(item => parseFloat(item.venta_actual));
                //le ponemos el signo de $ a los valores
                salesMonthPrevious.map(value => '$' + value);
                salesMonthCurrent.map(value => '$' + value);
                console.log(salesMonthPrevious, salesMonthCurrent);


                // Obtener el número de mes anterior y mes actual
                // Obtener el nombre del mes anterior y mes actual en español
                var currentDate = new Date();
                var monthNames = [
                    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ];
                var previousMonth = monthNames[currentDate.getMonth() - 1];
                var currentMonth = monthNames[currentDate.getMonth()];


                // Crear el nuevo gráfico con los datos obtenidos
                chart3 = new Chart(ctx3, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: previousMonth,
                                borderWidth: 1,
                                data: salesMonthPrevious,
                            },
                            {
                                label: currentMonth,
                                borderWidth: 1,
                                data: salesMonthCurrent,
                            },
                        ],
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                    }
                                }
                            },
                        },
                        plugins: {
                            legend: {
                                display: true,
                            },
                            title: {
                                display: true,
                                text: 'VENTAS MES ACTUAL VS MES ANTERIOR POR SUCURSAL',
                                font: {
                                    size: 14,
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        var label = context.dataset.label || '';
                                        label += ': $' + context.parsed.y.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                        return label;
                                    }
                                }
                            }
                        },
                    },
                });
            }
        });
    }

    if (dashboard4 == 1) {
        const ctx4 = document.getElementById('myChart4');
        let chart4 = null;
    
        $('#filterButton4').click(function () {
            var sucursal = $('#sucursalSelect4').val();
            var fecha = $('#fechaSelect4').val();
            var fechaInicio = '';
            var fechaFinal = '';

            // Verificar si el filtro seleccionado es "Rango Fechas"
            if (fecha === 'Rango Fechas') {
                fechaInicio = $('#fechaInicial4').val();
                fechaFinal = $('#fechaFinal4').val();
            }

            // Antes de crear un nuevo gráfico, verificar si hay un gráfico existente y destruirlo
            if (chart4) {
                chart4.destroy();
            }

            // Realizar la solicitud AJAX para obtener los datos del backend
            $.ajax({
                url: '/api/getVentasFlujo',
                type: 'POST',
                dataType: 'json',
                data: {
                    sucursal: sucursal,
                    fecha: fecha,
                    fechaInicio: fechaInicio,
                    fechaFinal: fechaFinal
                },
                success: function (data) {
                    console.log(data);
                    // Extraer los datos de la respuesta
                    const labels = data.map(item => item.branchOfficeName);
                    const totalSales = data.map(item => item.totalSales);
                    const totalFlow = data.map(item => item.totalFlow);

                    // Crear el nuevo gráfico con los datos obtenidos

                    // Configuración del gráfico
                    chart4 = new Chart(ctx4, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Flujo',
                                    borderWidth: 1,
                                    data: totalFlow,
                                },
                                {
                                    label: 'Ventas',
                                    borderWidth: 1,
                                    data: totalSales,
                                },
                            ],
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    stacked: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                        }
                                    }
                                },
                                x: {
                                    stacked: true,
                                },
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                },
                                title: {
                                    display: true,
                                    text: 'FLUJO Y VENTAS',
                                    font: {
                                        size: 16,
                                    },
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            var label = context.dataset.label || '';
                                            label += ': $' + context.parsed.y.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                            return label;
                                        }
                                    }
                                }
                            },
                        },
                    });
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });
        //ahora hacemos para cuando el usuario entre a la pagina
        var sucursal = $('#sucursalSelect4').val();
        var fecha = "Mes"
        var fechaInicio = '';
        var fechaFinal = '';

        // Verificar si el filtro seleccionado es "Rango Fechas"
        if (fecha === 'Rango Fechas') {
            fechaInicio = $('#fechaInicial4').val();
            fechaFinal = $('#fechaFinal4').val();
        }

        // Antes de crear un nuevo gráfico, verificar si hay un gráfico existente y destruirlo
        if (chart4) {
            chart4.destroy();
        }

        // Realizar la solicitud AJAX para obtener los datos del backend
        $.ajax({
            url: '/api/getVentasFlujo',
            type: 'POST',
            dataType: 'json',
            data: {
                sucursal: sucursal,
                fecha: fecha,
                fechaInicio: fechaInicio,
                fechaFinal: fechaFinal
            },
            success: function (data) {
                console.log(data);
                // Extraer los datos de la respuesta
                const labels = data.map(item => item.branchOfficeName);
                const totalSales = data.map(item => item.totalSales);
                const totalFlow = data.map(item => item.totalFlow);

                // Crear el nuevo gráfico con los datos obtenidos

                // Configuración del gráfico
                chart4 = new Chart(ctx4, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Flujo',
                                borderWidth: 1,
                                data: totalFlow,
                            },
                            {
                                label: 'Ventas',
                                borderWidth: 1,
                                data: totalSales,
                            },
                        ],
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                stacked: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                    }
                                }
                            },
                            x: {
                                stacked: true,
                            },
                        },
                        plugins: {
                            legend: {
                                display: true,
                            },
                            title: {
                                display: true,
                                text: 'FLUJO Y VENTAS',
                                font: {
                                    size: 16,
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        var label = context.dataset.label || '';
                                        label += ': $' + context.parsed.y.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                        return label;
                                    }
                                }
                            }
                        },
                    },
                });
            }
        });
    }

    if (dashboard5 == 1) {
        const ctx5 = document.getElementById('myChart5');
        let chart5 = null;

        $('#filterButton5').click(function () {
            // Obtener los valores de los filtros
            var sucursal = $('#sucursalSelect5').val();
            var fecha = $('#fechaSelect5').val();
            var fechaInicio = '';
            var fechaFinal = '';

            // Verificar si el filtro seleccionado es "Rango Fechas"
            if (fecha === 'Rango Fechas') {
                fechaInicio = $('#fechaInicial5').val();
                fechaFinal = $('#fechaFinal5').val();
            }

            // Antes de crear un nuevo gráfico, verificar si hay un gráfico existente y destruirlo
            if (chart5) {
                chart5.destroy();
            }

            // Realizar la solicitud AJAX para obtener los datos del backend
            $.ajax({
                url: 'api/getVentasVSGanancia',
                type: 'POST',
                dataType: 'json',
                data: {
                    sucursal: sucursal,
                    fecha: fecha,
                    fechaInicio: fechaInicio,
                    fechaFinal: fechaFinal
                },
                success: function (data) {
                    console.log(data);
                    // Extraer los datos de la respuesta
                    const labels = data.map(item => item.branchOffices_name);
                    const totalSales = data.map(item => item.totalVentas);
                    const totalProfit = data.map(item => item.totalGanancias);
                    console.log(totalSales, totalProfit);
                    // Crear el nuevo gráfico con los datos obtenidos

                    // Configuración del gráfico
                    chart5 = new Chart(ctx5, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Ventas',
                                    borderWidth: 1,
                                    data: totalSales,
                                },
                                {
                                    label: 'Ganancia',
                                    borderWidth: 1,
                                    data: totalProfit,
                                },
                            ],
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                },
                                title: {
                                    display: true,
                                    text: 'VENTAS VS GANANCIA',
                                    font: {
                                        size: 16,
                                    },
                                },
                            },
                        },
                    });
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });

        //ahora hacemos para cuando el usuario entre a la pagina
        var sucursal = $('#sucursalSelect5').val();
        var fecha = "Mes"
        var fechaInicio = '';
        var fechaFinal = '';

        // Verificar si el filtro seleccionado es "Rango Fechas"
        if (fecha === 'Rango Fechas') {
            fechaInicio = $('#fechaInicial5').val();
            fechaFinal = $('#fechaFinal5').val();
        }

        // Antes de crear un nuevo gráfico, verificar si hay un gráfico existente y destruirlo
        if (chart5) {
            chart5.destroy();
        }

        // Realizar la solicitud AJAX para obtener los datos del backend
        $.ajax({
            url: 'api/getVentasVSGanancia',
            type: 'POST',
            dataType: 'json',
            data: {
                sucursal: sucursal,
                fecha: fecha,
                fechaInicio: fechaInicio,
                fechaFinal: fechaFinal
            },
            success: function (data) {
                console.log(data);
                // Extraer los datos de la respuesta
                const labels = data.map(item => item.branchOffices_name);
                const totalSales = data.map(item => item.totalVentas);
                const totalProfit = data.map(item => item.totalGanancias);
                console.log(totalSales, totalProfit);
                // Crear el nuevo gráfico con los datos obtenidos

                // Configuración del gráfico
                chart5 = new Chart(ctx5, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Ventas',
                                borderWidth: 1,
                                data: totalSales,
                            },
                            {
                                label: 'Ganancia',
                                borderWidth: 1,
                                data: totalProfit,
                            },
                        ],
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                            },
                            title: {
                                display: true,
                                text: 'VENTAS VS GANANCIA',
                                font: {
                                    size: 16,
                                },
                            },
                        },
                    },
                });
            }
        });
    }

    if (dashboard6 == 1) {
        const ctx6 = document.getElementById('myChart6');
        let chart6 = null;

        $('#filterButton6').click(function () {
            // Obtener los valores de los filtros
            var sucursal = $('#sucursalSelect6').val();
            var fecha = $('#fechaSelect6').val();
            var fechaInicio = '';
            var fechaFinal = '';

            // Verificar si el filtro seleccionado es "Rango Fechas"
            if (fecha === 'Rango Fechas') {
                fechaInicio = $('#fechaInicial6').val();
                fechaFinal = $('#fechaFinal6').val();
            }

            // Antes de crear un nuevo gráfico, verificar si hay un gráfico existente y destruirlo
            if (chart6) {
                chart6.destroy();
            }

            // Realizar la solicitud AJAX para obtener los datos del backend
            $.ajax({
                url: 'api/getGananciaVSGastos',
                type: 'POST',
                dataType: 'json',
                data: {
                    sucursal: sucursal,
                    fecha: fecha,
                    fechaInicio: fechaInicio,
                    fechaFinal: fechaFinal
                },
                success: function (data) {
                    console.log(data);
                    // Extraer los datos de la respuesta
                    const labels = data.map(item => item.NombreSucursal);
                    const totalProfit = data.map(item => item.TotalGanancias);
                    const totalExpenses = data.map(item => item.TotalGastos);

                    // Crear el nuevo gráfico con los datos obtenidos

                    // Configuración del gráfico
                    if (sucursal === 'Todos') {
                        // Gráfico de dona normal
                        chart6 = new Chart(ctx6, {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: 'Ganancia',
                                        borderWidth: 1,
                                        data: totalProfit,
                                    },
                                    {
                                        label: 'Gastos',
                                        borderWidth: 1,
                                        data: totalExpenses,
                                    },
                                ],
                            },
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                    },
                                    title: {
                                        display: true,
                                        text: 'GANANCIA VS GASTOS',
                                        font: {
                                            size: 16,
                                        },
                                    },
                                },
                            },
                        });
                    } else {
                        // Gráfico combinado
                        chart6 = new Chart(ctx6, {
                            type: 'doughnut',
                            data: {
                                labels: ['Ganancia', 'Gastos'],
                                datasets: [
                                    {
                                        borderWidth: 1,
                                        data: [totalProfit[0], totalExpenses[0]],
                                    }
                                ],
                            },
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                    },
                                    title: {
                                        display: true,
                                        text: 'GANANCIA VS GASTOS',
                                        font: {
                                            size: 16,
                                        },
                                    },
                                },
                            },
                        });
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });

        //ahora hacemos para cuando el usuario entre a la pagina
        var sucursal = $('#sucursalSelect6').val();
        var fecha = "Mes"
        var fechaInicio = '';
        var fechaFinal = '';

        // Verificar si el filtro seleccionado es "Rango Fechas"
        if (fecha === 'Rango Fechas') {
            fechaInicio = $('#fechaInicial6').val();
            fechaFinal = $('#fechaFinal6').val();
        }

        // Antes de crear un nuevo gráfico, verificar si hay un gráfico existente y destruirlo
        if (chart6) {
            chart6.destroy();
        }

        // Realizar la solicitud AJAX para obtener los datos del backend
        $.ajax({
            url: 'api/getGananciaVSGastos',
            type: 'POST',
            dataType: 'json',
            data: {
                sucursal: sucursal,
                fecha: fecha,
                fechaInicio: fechaInicio,
                fechaFinal: fechaFinal
            },
            success: function (data) {
                console.log(data);
                // Extraer los datos de la respuesta
                const labels = data.map(item => item.NombreSucursal);
                const totalProfit = data.map(item => item.TotalGanancias);
                const totalExpenses = data.map(item => item.TotalGastos);

                if (sucursal === 'Todos') {
                    // Gráfico de dona normal
                    chart6 = new Chart(ctx6, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Ganancia',
                                    borderWidth: 1,
                                    data: totalProfit,
                                },
                                {
                                    label: 'Gastos',
                                    borderWidth: 1,
                                    data: totalExpenses,
                                },
                            ],
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                },
                                title: {
                                    display: true,
                                    text: 'GANANCIA VS GASTOS',
                                    font: {
                                        size: 16,
                                    },
                                },
                            },
                        },
                    });
                } else {
                    // Gráfico combinado
                    chart6 = new Chart(ctx6, {
                        type: 'doughnut',
                        data: {
                            labels: ['Ganancia', 'Gastos'],
                            datasets: [
                                {
                                    borderWidth: 1,
                                    data: [totalProfit[0], totalExpenses[0]],
                                }
                            ],
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                },
                                title: {
                                    display: true,
                                    text: 'GANANCIA VS GASTOS',
                                    font: {
                                        size: 16,
                                    },
                                },
                            },
                        },
                    });
                }
            }
        });
    }
    




});
