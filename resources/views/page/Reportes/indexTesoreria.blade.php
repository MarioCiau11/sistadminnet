@extends('layouts.layout')

@section('content')
    <div class="mainpanel">
        <div class="pageheader">
            <div class="media">
                <div class="pageicon pull-left ">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <div class="media-body">
                    <ul class="breadcrumb">
                        <li><a href=""><i class="glyphicon glyphicon-home"></i></a></li>
                        <li>Reportes</li>
                    </ul>
                    <h4>Reportes</h4>
                </div>
            </div><!-- media -->
        </div><!-- pageheader -->

        <div class="contentpanel">
            <div class="col-sm-12 col-md-12">
                <!-- Tab panes -->
                <div class="tab-content nopadding noborder">
                    <ul class="nav nav-tabs nav-line" style="margin: 0px !important">
                        <li class="active"><a href="#following2" data-toggle="tab"><i class="fas fa-list"></i> Todos</a>
                        </li>
                        <li class=""><a href="#followers2" data-toggle="tab"><i class="fas fa-star"></i> Favoritos</a>
                        </li>
                        <li class=""><a href="#followers3" data-toggle="tab"><i
                                    class="fa-solid fa-clock-rotate-left"></i> Recientes</a></li>
                        {{-- agregamos un input para buscar reportes --}}
                    </ul>
                    <div class="tab-pane active" id="following2">
                        <div class="activity-list">
                            <div class="pull-right" style="margin-top: 10px; margin-bottom: 10px;">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Buscar reporte"
                                        id="search-input">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="col-md-12 text-center">
                                    <h4 class="report-title">Reportes de Tesorería</h4>
                                </div>
                                <div class="col-md-6">
                                    @can('Auxiliares por Cuenta de Dinero Nivel - Concentrado')
                                        <div class="panel panel-default report-panel">
                                            <div class="panel-body">
                                                <div class="pageicon pull-left ">
                                                    <i class="fa-solid fa-file-lines"></i>
                                                </div>
                                                <span class="report-name"><a class="report-link"
                                                        data-recent-id="tesoreria-concentrados"
                                                        href="{{ route('vista.reportes.tesoreria-concentrados') }}">Auxiliares por Cuenta de Dinero
                                                Nivel - Concentrado</a></span>
                                                <button class="btn btn-default pull-right favorite-btn" data-toggle="tooltip"
                                                    data-placement="top" title="Marcar como favorito"
                                                    data-report-id="tesoreria-concentrados"
                                                    data-report-name="Auxiliares por Cuenta de Dinero Nivel - Concentrado"
                                                    data-report-identifier="report-9"
                                                    data-report-category="Tesoreria"><i class="far fa-star"></i></button>
                                            </div>
                                        </div>
                                    @endcan
                                </div>
                                <div class="col-md-6">
                                    @can('Auxiliares por Cuenta de Dinero Nivel - Desglosado')
                                    <div class="panel panel-default report-panel">
                                        <div class="panel-body">
                                            <div class="pageicon pull-left ">
                                                <i class="fa-solid fa-file-lines"></i>
                                            </div>
                                            <span class="report-name"><a class="report-link"
                                                    data-recent-id="tesoreria-desglosado"
                                                    href="{{ route('vista.reportes.tesoreria-desglosado') }}">Auxiliares por Cuenta de Dinero Nivel
                                            -
                                            Desglosado</a></span>
                                            <button class="btn btn-default pull-right favorite-btn" data-toggle="tooltip"
                                                data-placement="top" title="Marcar como favorito"
                                                data-report-id="tesoreria-desglosado"
                                                data-report-name="Auxiliares por Cuenta de Dinero Nivel - Desglosado"
                                                data-report-identifier="report-10"
                                                data-report-category="Tesoreria"><i class="far fa-star"></i></button>
                                        </div>
                                    </div>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="followers2">
                        <div class="activity-list">
                            <div class="col-md-12">
                                <div class="text-center">
                                    <h4 class="report-title">Mis Favoritos</h4>
                                </div>
                                @foreach ($favorites as $favorite)
                                    <div class="panel panel-default report-panel">
                                        <div class="panel-body">
                                            <div class="pageicon pull-left">
                                                <i class="fa-solid fa-file-lines"></i>
                                            </div>
                                            <span class="report-name">
                                                <a class="report-link" data-recent-id="{{ $favorite->report_key }}"
                                                    href="{{ route('vista.reportes.' . $favorite->report_key) }}">
                                                    {{ $favorite->report_name }}
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div><!-- activity-list -->
                    </div><!-- tab-pane -->

                    <div class="tab-pane" id="followers3">
                        <div class="activity-list">
                            <div class="col-md-12">
                                <div class="text-center">
                                    <h4 class="report-title">Mis Recientes</h4>
                                </div>
                            </div>
                            <div class="col-md-12" id="recientes-container">
                                <!-- Los reportes recientes se mostrarán aquí -->
                            </div>
                        </div><!-- activity-list -->
                    </div><!-- tab-pane -->

                </div><!-- tab-content -->
            </div>
        </div><!-- contentpanel -->

    </div><!-- mainpanel -->
    </div><!-- mainwrapper -->
    <script>
        $('.favorite-btn').on('click', function() {
            const button = $(this);
            const reportId = button.data('report-id');
            const reportName = button.data('report-name');
            const reportIdentifier = button.data('report-identifier');
            const reportCategory = button.data('report-category');

            // Comprobar si el botón ya está marcado como favorito
            const isFavorited = button.hasClass('favorited');

            if (isFavorited) {
                // Si ya está marcado como favorito, eliminarlo de los favoritos
                $.post('/remove-favorite', {
                    reportIdentifier: reportIdentifier
                }, function(response) {
                    if (response.success) {
                        button.removeClass('favorited');
                        //recargar la página
                        location.reload();
                    }
                });
            } else {
                // Si no está marcado como favorito, agregarlo a los favoritos
                $.post('/add-to-favorites', {
                    reportId: reportId,
                    reportName: reportName,
                    reportIdentifier: reportIdentifier,
                    reportCategory: reportCategory
                }, function(response) {
                    if (response.success) {
                        button.addClass('favorited');
                        //recargar la página
                        location.reload();
                    }
                });
            }
        });

        // Comprobar y establecer la clase 'favorited' en los botones al cargar la página
        $('.favorite-btn').each(function() {
            const button = $(this);
            const reportIdentifier = button.data('report-identifier');

            $.get('/check-favorite', {
                reportIdentifier: reportIdentifier
            }, function(response) {
                if (response.favorite) {
                    button.addClass('favorited');
                    button.find('i').removeClass('fa-regular fa-star').addClass(
                        'fa-solid fa-star');
                    //si ya está marcado como favorito que ahorita el tooltip diga "Quitar de favoritos"
                    button.attr('title', 'Quitar de favoritos');

                }
            });
        });


        
        $(document).ready(function() {
            // Función para filtrar los reportes en función de la búsqueda
            function filtrarReportes(busqueda) {
                $('.col-md-6').each(function() {
                    let tieneReportesVisibles = false;

                    $(this).find('.report-panel').each(function() {
                        let reportName = $(this).find('.report-name').text().trim().toLowerCase();

                        if (reportName.includes(busqueda)) {
                            $(this).removeClass('hidden'); // Muestra el reporte
                            tieneReportesVisibles = true;
                        } else {
                            $(this).addClass('hidden'); // Oculta el reporte
                        }
                    });

                    // Mostrar u ocultar el contenedor .col-md-6 basado en si tiene reportes visibles
                    if (tieneReportesVisibles) {
                        $(this).removeClass('hidden');
                    } else {
                        $(this).addClass('hidden');
                    }
                });
            }

            // Manejar el evento de entrada de búsqueda en tiempo real
            $('#search-input').on('input', function() {
                let busqueda = $(this).val().trim().toLowerCase();
                console.log(busqueda);
                filtrarReportes(busqueda);
                //si el input tiene texto ocultamos report-title, sino lo mostramos
                if (busqueda.length > 0) {
                    $('.report-title').addClass('hidden'); // Ocultar títulos de reporte
                } else {
                    $('.report-title').removeClass('hidden'); // Mostrar títulos de reporte
                }
            });


            // Función para guardar un reporte reciente en el Local Storage
            function guardarReporteReciente(reportId) {
                let recientes = JSON.parse(localStorage.getItem('reportesRecientes')) || [];

                // Evitar agregar el mismo reporte más de una vez
                if (!recientes.includes(reportId)) {
                    recientes.push(reportId);
                }

                // Limitar la cantidad de reportes recientes a 5
                if (recientes.length > 5) {
                    recientes.shift(); // Elimina el reporte más antiguo
                }

                localStorage.setItem('reportesRecientes', JSON.stringify(recientes));
            }

            // Función para mostrar los reportes recientes
            function mostrarReportesRecientes() {
                let recientes = JSON.parse(localStorage.getItem('reportesRecientes')) || [];
                let recientesContainer = $('#recientes-container');

                recientesContainer.empty(); // Limpia el contenido anterior

                // Recorrer el arreglo en orden inverso para mostrar el más reciente primero
                for (let i = recientes.length - 1; i >= 0; i--) {
                    let reporteReciente = $('<div class="panel panel-default report-panel">')
                        .append($('<div class="panel-body">')
                            .append($('<div class="pageicon pull-left">')
                                .html('<i class="fa-solid fa-file-lines"></i>'))
                            .append($('<span class="report-name">')
                                .append($('<a class="report-link">')
                                    .attr('href', obtenerRutaReporte(recientes[i]))
                                    .text(obtenerNombreReporte(recientes[i])))));

                    recientesContainer.append(reporteReciente);
                }
            }

            function obtenerRutaReporte(reportId) {
                switch (reportId) {
                    case 'compras-articulo-provedor':
                        return '{{ route('vista.reportes.compras-articulo-provedor') }}';
                    case 'acumulado-por-articulo-proveedor':
                        return '{{ route('vista.reportes.acumulado-por-articulo-proveedor') }}';
                    case 'compras-con-series':
                        return '{{ route('vista.reportes.compras-con-series') }}';
                    case 'ventas-cliente-articulo':
                        return '{{ route('vista.reportes.ventas-cliente-articulo') }}';
                    case 'ventas-serie':
                        return '{{ route('vista.reportes.ventas-serie') }}';
                    case 'listaPrecios':
                        return '{{ route('vista.reportes.listaPrecios') }}';
                    case 'ventas-producto-mas-vendido':
                        return '{{ route('vista.reportes.ventas-producto-mas-vendido') }}';
                    case 'ventas-ganancia':
                        return '{{ route('vista.reportes.ventas-ganancia') }}';
                    case 'tesoreria-concentrados':
                        return '{{ route('vista.reportes.tesoreria-concentrados') }}';
                    case 'tesoreria-desglosado':
                        return '{{ route('vista.reportes.tesoreria-desglosado') }}';
                    case 'inventario-desglosado':
                        return '{{ route('vista.reportes.inventario.desglosado') }}';
                    case 'inventario.concentrado':
                        return '{{ route('vista.reportes.inventario.concentrado') }}';
                    case 'inventario.costo-dia':
                        return '{{ route('vista.reportes.inventario.costo-dia') }}';
                    case 'gastos-concepto':
                        return '{{ route('vista.reportes.gastos-concepto') }}';
                    case 'gastos-antecedente-activo-fijo':
                        return '{{ route('vista.reportes.gastos-antecedente-activo-fijo') }}';
                    case 'cxp-antiguedad-saldos':
                        return '{{ route('vista.reportes.cxp-antiguedad-saldos') }}';
                    case 'cxp-estado-cuenta':
                        return '{{ route('vista.reportes.cxp-estado-cuenta') }}';
                    case 'cxc-antiguedad-saldos':
                        return '{{ route('vista.reportes.cxc-antiguedad-saldos') }}';
                    case 'cxc-cobranza-forma-cobro':
                        return '{{ route('vista.reportes.cxc-cobranza-forma-cobro') }}';
                    case 'cxc-estado-cuenta':
                        return '{{ route('vista.reportes.cxc-estado-cuenta') }}';
                    case 'utilidad-ventas-vs-gastos':
                        return '{{ route('vista.reportes.utilidad-ventas-vs-gastos') }}';
                    default:
                        return '#';
                }
            }

            function obtenerNombreReporte(reportId) {
                switch (reportId) {
                    case 'compras-articulo-provedor':
                        return 'Reporte por Proveedor';
                    case 'acumulado-por-articulo-proveedor':
                        return 'Reporte Acumulado por Artículo/Proveedor';
                    case 'compras-con-series':
                        return 'Reporte Compras con Serie';
                    case 'ventas-cliente-articulo':
                        return 'Reporte por Cliente';
                    case 'ventas-serie':
                        return 'Reporte Ventas con Serie';
                    case 'listaPrecios':
                        return 'Lista de Precios';
                    case 'ventas-producto-mas-vendido':
                        return 'Reporte Producto Más Vendido';
                    case 'ventas-ganancia':
                        return 'Reporte Ventas VS Ganancia';
                    case 'tesoreria-concentrados':
                        return 'Auxiliares por Cuenta de Dinero Nivel - Concentrado';
                    case 'tesoreria-desglosado':
                        return 'Auxiliares por Cuenta de Dinero Nivel - Desglosado';
                    case 'inventario-desglosado':
                        return 'Inventarios - Tipo Desglosado';
                    case 'inventario.concentrado':
                        return 'Inventarios - Tipo Concentrado';
                    case 'inventario.costo-dia':
                        return 'Costo del Inventario al día';
                    case 'gastos-concepto':
                        return 'Gastos por Concepto';
                    case 'gastos-antecedente-activo-fijo':
                        return 'Gastos por Activo Fijo';
                    case 'cxp-antiguedad-saldos':
                        return 'Antiguedad de Saldos CxP';
                    case 'cxp-estado-cuenta':
                        return 'Estado de Cuenta CxP';
                    case 'cxc-antiguedad-saldos':
                        return 'Antiguedad de Saldos CxC';
                    case 'cxc-cobranza-forma-cobro':
                        return 'Cobranza por Forma de Cobro';
                    case 'cxc-estado-cuenta':
                        return 'Estado de Cuenta CxC';
                    case 'utilidad-ventas-vs-gastos':
                        return 'Utilidad Ventas vs Gastos';

                    default:
                        return '';
                }
            }

            // Llama a las funciones correspondientes cuando se hace clic en un enlace de reporte
            $('.report-link').on('click', function(event) {
                let reportId = $(this).data('recent-id');
                console.log(reportId);
                guardarReporteReciente(reportId);
            });

            // Llama a la función para mostrar los reportes recientes cuando se carga la página
            mostrarReportesRecientes();
        });

        window.addEventListener("pageshow", function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>

    <script src="{{ asset('js/flot/jquery.flot.min.js') }}"></script>
    <script src="{{ asset('js/flot/jquery.flot.resize.min.js') }}"></script>
    <script src="{{ asset('js/flot/jquery.flot.spline.min.js') }}"></script>
    <script src="{{ asset('js/graficos.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/language/DatePicker/datePicker.js') }}"></script>
@stop
