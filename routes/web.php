<?php

use App\Http\Controllers\Controllers\erpNet\Timbrado\TimbresController;
use App\Http\Controllers\FilesController14;
use App\Http\Controllers\erpNet\Anexos_Procesos\GastoAnexosController;
use App\Http\Controllers\erpNet\Anexos_Procesos\CompraAnexosController;
use App\Http\Controllers\erpNet\Anexos_Procesos\CXCAnexosController;
use App\Http\Controllers\erpNet\Anexos_Procesos\CXPAnexosController;
use App\Http\Controllers\erpNet\Anexos_Procesos\InventariosAnexosController;
use App\Http\Controllers\erpNet\Anexos_Procesos\TesoreriaAnexosController;
use App\Http\Controllers\erpNet\Anexos_Procesos\VentasAnexosController;
use App\Http\Controllers\erpNet\login\LoginController;
use App\Http\Controllers\erpNet\catalogos\AgentesController;
use App\Http\Controllers\erpNet\catalogos\AgrupadorCategoriaController;
use App\Http\Controllers\erpNet\catalogos\AgrupadorFamiliaController;
use App\Http\Controllers\erpNet\catalogos\AgrupadorGrupoController;
use App\Http\Controllers\erpNet\catalogos\AlmacenesController;
use App\Http\Controllers\erpNet\catalogos\ArticulosController;
use App\Http\Controllers\erpNet\catalogos\CentroCostosController;
use App\Http\Controllers\erpNet\catalogos\ClientesController;
use App\Http\Controllers\erpNet\catalogos\ConceptosGastosController;
use App\Http\Controllers\erpNet\catalogos\CuentasDineroController;
use App\Http\Controllers\erpNet\DashboardController;
use App\Http\Controllers\erpNet\catalogos\EmpresaController;
use App\Http\Controllers\erpNet\catalogos\InstitucionFinancieraController;
use App\Http\Controllers\erpNet\catalogos\ListaProveedorController;
use App\Http\Controllers\erpNet\catalogos\ProveedorController;
use App\Http\Controllers\erpNet\catalogos\SucursalController;
use App\Http\Controllers\erpNet\catalogos\VehiculosController;
use App\Http\Controllers\erpNet\colonia\ColoniaController;
use App\Http\Controllers\erpNet\ConfiguracionGeneral\UnidadesController;
use App\Http\Controllers\erpNet\ConfiguracionGeneral\UnidadesEmpaqueController;
use App\Http\Controllers\erpNet\ConfiguracionGeneral\ConceptoModulosController;
use App\Http\Controllers\erpNet\ConfiguracionGeneral\CondicionCreditoController;
use App\Http\Controllers\erpNet\ConfiguracionGeneral\FormasPagoController;
use App\Http\Controllers\erpNet\ConfiguracionGeneral\MonedasController;
use App\Http\Controllers\erpNet\ConfiguracionGeneral\MotivosCancelacionController;
use App\Http\Controllers\erpNet\ConfiguracionGeneral\RolesController;
use App\Http\Controllers\erpNet\ConfiguracionGeneral\UsuariosController;
use App\Http\Controllers\erpNet\ConfiguracionGeneral\ParametrosGeneralesController;
use App\Http\Controllers\erpNet\cp\CpController;
use App\Http\Controllers\erpNet\flujo\FlujoModulosController;
use App\Http\Controllers\erpNet\herramienta\CambioCostosController;
use App\Http\Controllers\erpNet\herramienta\CambioPreciosVentaController;
use App\Http\Controllers\erpNet\herramienta\herramientaController;
use App\Http\Controllers\erpNet\login\LicenciasController;
use App\Http\Controllers\erpNet\procesos\CXCController;
use App\Http\Controllers\erpNet\procesos\CxpController;
use App\Http\Controllers\erpNet\procesos\GastosController;
use App\Http\Controllers\erpNet\procesos\LogisticaComprasController;
use App\Http\Controllers\erpNet\procesos\LogisticaInventariosController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReporteriaController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesComprasAcumArticuloProveedor;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesComprasArticuloProvController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesComprasController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesComprasUnidadController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesCXCAntiguedadSaldosController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesCXCEstadoCuentaController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesCXPAntiguedadSaldosController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesCXPEstadoCuentaController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesGastosAntecedentesActivoFController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesGastosConceptoController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesInventariosDesglosadoController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesInventariosGeneralController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesListaPreciosController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesTesoreriaConcentradosController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesTesoreriaDesglosadoController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesUtilidadController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesVentasAcumuladoController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesVentasArticuloClientesController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesComprasAcumArticuloProveedorController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesComprasSeriesController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesCXCCobranzaCobroController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesInventariosConcentradoController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesInventariosCostoDiaController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesVentasAcumArticuloCliente;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesVentasProductoMasVendidoController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesVentasSeriesController;
use App\Http\Controllers\erpNet\procesos\Reportes\ReportesVentasVSGananciasController;
use App\Http\Controllers\erpNet\procesos\TesoreriaController;
use App\Http\Controllers\erpNet\procesos\VentasController;
use App\Http\Controllers\erpNet\prodServ\ProdServController;
use App\Http\Controllers\erpNet\Timbrado\TimbradoController;
use App\Http\Controllers\erpNet\Timbrado\TimbresController as TimbradoTimbresController;
use App\Http\Controllers\regimenVScfdi\REGIMEN_CFDI_CONTROLLER;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes();

// Rutas de configuración general

Route::middleware(['auth'])->group(function () {
    Route::resource('/', DashboardController::class)->names("dashboard");
    Route::resource('formas-pago', FormasPagoController::class)->names('configuracion.formas-pago');
    Route::resource('monedas', MonedasController::class)->names("configuracion.monedas");
    Route::resource('condiciones-termino', CondicionCreditoController::class)->names('configuracion.condiciones-credito');
    route::resource('unidades', UnidadesController::class)->names('configuracion.unidades');
    route::resource('unidades-empaque', UnidadesEmpaqueController::class)->names('configuracion.unidades-empaque');
    Route::resource('concepto-procesos', ConceptoModulosController::class)->names('configuracion.concepto-modulos');
    Route::resource('roles', RolesController::class)->names('configuracion.roles');
    Route::resource('usuarios', UsuariosController::class)->names('configuracion.usuarios');
    Route::resource('parametros-generales', ParametrosGeneralesController::class)->names('configuracion.parametros-generales');
    Route::resource('motivos-cancelacion', MotivosCancelacionController::class)->names('configuracion.motivos-cancelacion');

    //Filtro de las configuraciones generales
    Route::post('/configuracionGeneral/formasPago/filtro', [FormasPagoController::class, "formasPagoAction"])->name('configuracion.formas-pago.filtro');
    Route::post('/configuracionGeneral/monedas/filtro', [MonedasController::class, "moneyAction"])->name('configuracion.monedas.filtro');
    Route::post('/configuracionGeneral/condicionesCredito/filtro', [CondicionCreditoController::class, 'condicionCreditoAction'])->name('configuracion.condiciones-credito.filtro');
    route::post('/configuracionGeneral/unidades/filtro', [UnidadesController::class, 'unidadAction'])->name('configuracion.unidades.filtro');
    route::post('/configuracionGeneral/unidadesEmpaque/filtro', [UnidadesEmpaqueController::class, 'unidadEmpAction'])->name('configuracion.unidades-empaque.filtro');
    Route::post('/configuracionGeneral/conceptoModulos/filtro', [ConceptoModulosController::class, "conceptosAction"])->name('configuracion.concepto-modulos.filtro');
    Route::post('/configuracionGeneral/roles/filtro', [RolesController::class, 'rolesAction'])->name('configuracion.roles.filtro');
    Route::post('/configuracionGeneral/usuarios/filtro', [UsuariosController::class, 'userAction'])->name('configuracion.usuarios.filtro');
    Route::post('/configuracionGeneral/motivosCancelacion/filtro', [MotivosCancelacionController::class, 'motivosAction'])->name('configuracion.motivos-cancelacion.filtro');
});

// Rutas de catálogos
Route::middleware(['auth'])->group(function () {
    Route::resource('empresa', EmpresaController::class)->names("catalogo.empresa");
    Route::resource('sucursal', SucursalController::class)->names("catalogo.sucursal");
    Route::resource('almacen', AlmacenesController::class)->names("catalogo.almacen");
    Route::resource('cuentas-bancos', CuentasDineroController::class)->names("catalogo.cuenta-dinero");
    Route::resource('razones-gastos', ConceptosGastosController::class)->names("catalogo.concepto-gastos");
    Route::resource('instituciones-financieras', InstitucionFinancieraController::class)->names('catalogo.instituciones-financieras');
    route::resource('operativos', AgentesController::class)->names('catalogo.agentes');
    route::resource('vehiculos', VehiculosController::class)->names('catalogo.vehiculos');
    Route::resource('clientes', ClientesController::class)->names('catalogo.clientes');
    Route::resource('proveedor', ProveedorController::class)->names("catalogo.proveedor");
    Route::resource('articulos', ArticulosController::class)->names('catalogo.articulos');
    Route::resource('centroCostos', CentroCostosController::class)->names('catalogo.centroCostos');
    // Route::resource('timbres', TimbradoTimbresController::class)->names('timbrado.timbres');

    //timbres
    Route::get('/administrar-timbres', [TimbradoTimbresController::class, 'index'])->name('timbrado.index');
    Route::get('/administrar-timbres/create', [TimbradoTimbresController::class, 'create'])->name('timbrado.create');
    Route::post('/administrar-timbres/timbradoStore', [TimbradoTimbresController::class, 'store'])->name('timbrado.store');
    //Licencias
    Route::get('/administrar-licencias', [LicenciasController::class, 'index'])->name('licencias.index');
    Route::get('/eliminarLicencia/{license}', [LicenciasController::class, 'edit'])->name('licenses.delete');

    //Filtros de catálogos
    Route::post('/catalogo/empresa/filtro', [EmpresaController::class, 'empresaAction'])->name('catalogo.empresa.filtro');
    Route::post('/catalogo/sucursal/filtro', [SucursalController::class, "sucursalAction"])->name('catalogo.sucursal.filtro');
    Route::post('/catalogo/almacen/filtro', [AlmacenesController::class, "almacenAction"])->name('catalogo.almacen.filtro');
    Route::post('/catalogo/proveedor/filtro', [ProveedorController::class, "providerAction"])->name('catalogo.proveedor.filtro');
    Route::post('/catalogo/concepto-gastos/filtro', [ConceptosGastosController::class, "conceptoAction"])->name('catalogo.concepto-gastos.filtro');
    Route::post('/catalogo/agentes/filtro', [AgentesController::class, "agentesAction"])->name('catalogo.agentes.filtro');
    Route::post('/catalogo/vehiculos/filtro', [VehiculosController::class, "vehiculosAction"])->name('catalogo.vehiculos.filtro');
    Route::post('/catalogo/clientes/filtro', [ClientesController::class, "customerAction"])->name('catalogo.clientes.filtro');
    Route::post('/catalogo/articulos/filtro', [ArticulosController::class, "articuloAction"])->name('catalogo.articulo.filtro');
    Route::post('/catalogo/instituciones-financieras/filtro', [InstitucionFinancieraController::class, "instFinancialAction"])->name('catalogo.instituciones-financieras.filtro');
    Route::post('/catalogo/cuenta-dinero/filtro', [CuentasDineroController::class, "cuentaDineroAction"])->name('catalogo.cuenta-dinero.filtro');
    Route::post('/catalogo/centroCostos/filtro', [CentroCostosController::class, "centroCostosAction"])->name('catalogo.centroCostos.filtro');

    //Api para buscar los articulos con relación cuando sea de tipo Kit el articulo
    Route::get('/catalogo/articulos/relaciones/', [ArticulosController::class, "getRelacionArticulos"])->name('catalogo.relacion.articulos');

    //Eliminaos las imagenes del articulo
    Route::get('/articulo/eliminar/img/', [ArticulosController::class, 'eliminarImagen'])->name('eliminar.articulo.imagen');

    //Buscamos su ultimo costo
    Route::get('/catalogo/articulos/ultimoCosto', [ArticulosController::class, "getLastCosto"])->name('ultimoCostoArticulo');


    //rutas categorias
    Route::post('/categoria/create', [AgrupadorCategoriaController::class, "agrupadorCategoriaAgregar"])->name('agregarCategoria');
    Route::get('/categoria/getCategoria', [AgrupadorCategoriaController::class, "getCategoria"])->name('categoriaId');
    Route::get('categoria/index', [AgrupadorCategoriaController::class, "indexCategoria"])->name('categoriaIndex');
    Route::get('categoriaCrear/index', [AgrupadorCategoriaController::class, "createCategoria"])->name('categoriaCreate');
    Route::get('categoria/{id}/edit', [AgrupadorCategoriaController::class, "editCategoria"])->name('editCategoria');
    Route::put('categoria/{id}', [AgrupadorCategoriaController::class, "updateCategoria"])->name('updateCategoria');
    route::delete('categoria/delete', [AgrupadorCategoriaController::class,  "deleteCategoria"])->name('deleteCategory');

    Route::get('lista/index', [ListaProveedorController::class, "index"])->name('listaIndex');
    Route::get('lista/create', [ListaProveedorController::class, "create"])->name('listaCreate');
    Route::post('lista/store', [ListaProveedorController::class, "store"])->name('listastore');
    Route::get('lista/show/{id?}', [ListaProveedorController::class, "show"])->name('listaShow');
    Route::get('lista/edit/{id?}', [ListaProveedorController::class, "edit"])->name('listaEdit');
    Route::delete('lista/destroy/{id?}', [ListaProveedorController::class, "destroy"])->name('listaDestroy');
    Route::put('lista/update/{id?}', [ListaProveedorController::class, "update"])->name('listaUpdate');
    Route::post('/lista/filtro', [ListaProveedorController::class, "listaAction"])->name('listaFiltro');
    Route::get('/lista/getId', [ListaProveedorController::class, "getLista"])->name('lista.getId');
    Route::get('/lista/getCosto', [ListaProveedorController::class, "getCosto"])->name('lista.getCosto');
    //rutas grupos
    Route::post('/grupo/create', [AgrupadorGrupoController::class, "agrupadorGrupoAgregar"])->name('agregarGrupo');
    Route::get('/grupo/getGrupo', [AgrupadorGrupoController::class, "getGrupo"])->name('grupoId');
    Route::get('grupo/index', [AgrupadorGrupoController::class, "indexGrupo"])->name('grupoIndex');
    Route::get('grupoCrear/index', [AgrupadorGrupoController::class, "createGrupo"])->name('grupoCreate');
    Route::get('grupo/{id}/edit', [AgrupadorGrupoController::class, "editGrupo"])->name('editGrupo');
    Route::put('grupo/{id}', [AgrupadorGrupoController::class, "updateGrupo"])->name('updateGrupo');
    route::delete('grupo/delete', [AgrupadorGrupoController::class,  "deleteGrupo"])->name('deleteGrupo');

    //rutas familia
    Route::post('/articulos/familia/save', [AgrupadorFamiliaController::class, "agrupadorFamiliaAgregar"])->name('agregarFamilia');
    Route::get('/getFamilia', [AgrupadorFamiliaController::class, "getFamilia"])->name('familiaId');
    Route::get('familiaCrear/index', [AgrupadorFamiliaController::class, "createFamilia"])->name('familiaCreate');
    Route::get('familia/index', [AgrupadorFamiliaController::class, "indexCategoria"])->name('familiaIndex');
    Route::get('familia/{id}/edit', [AgrupadorFamiliaController::class, "editFamilia"])->name('editFamilia');
    Route::put('familia/{id}', [AgrupadorFamiliaController::class, "updateFamilia"])->name('updateFamilia');
    route::delete('familia/delete', [AgrupadorFamiliaController::class,  "deleteFamilia"])->name('deleteFamilia');


    //Rutas de los modulos
    //Compras

    Route::get('/logistica/compras', [LogisticaComprasController::class, "index"])->name('vista.modulo.compras');

    Route::get('/logistica/compra/{id?}/', [LogisticaComprasController::class, "create"])->name('vista.modulo.compras.create-compra');

    Route::post('/logistica/afectar/{id?}/', [LogisticaComprasController::class, "afectar"])->name('vista.modulo.compras.afectar');

    Route::post('/logistica/compra/store/', [LogisticaComprasController::class, "store"])->name('modulo.compras.store-compra');

    Route::post('logistica/compras/filtro', [LogisticaComprasController::class, "comprasAction"])->name('logistica.compras.filtro');

    //Anexos Compra
    Route::get('/logistica/compra/{id?}/anexos', [CompraAnexosController::class, "index"])->name('vista.modulo.compras.anexos');

    Route::post('/logistica/compra/{id?}/anexos/store', [CompraAnexosController::class, "store"])->name('modulo.compras.anexos.store');

    Route::delete('/logistica/compra/anexos/delete/{id}/', [CompraAnexosController::class, "destroy"])->name('modulo.compras.anexos.delete');



    //Flujo modulos
    Route::get('/modulos/flujo/api/siguiente', [FlujoModulosController::class, 'siguienteFlujo'])->name('modulo.flujo.api.siguiente');

    Route::get('/modulos/flujo/api/anterior', [FlujoModulosController::class, 'anteriorFlujo'])->name('modulo.flujo.api.anterior');

    //Api lista precio articulo proveedor
    Route::get('/articulos/listaProveedorCompras/{reference}/{listaPrecio}', [LogisticaComprasController::class, 'articulosListaPrecioProveedor'])->name('modulo.articulosListaPrecioProveedorRef');

    Route::get('/articulos/articulosConExistencia/{reference}', [LogisticaComprasController::class, 'articulosConExistencia'])->name('modulo.articulosConExistencia');

    Route::get('/articulos/categoria/{categoria}', [LogisticaComprasController::class, 'articulosCategoria'])->name('modulo.articulosCategoria');

    Route::get('/articulos/familia/{familia}', [LogisticaComprasController::class, 'articulosFamilia'])->name('modulo.articulosFamilia');

    Route::get('/articulos/grupo/{grupo}', [LogisticaComprasController::class, 'articulosGrupo'])->name('modulo.articulosGrupo');

    //en ventas
    //Pruebas compras
    Route::get('/afectar', [LogisticaComprasController::class, 'afectarPruebas'])->name('afectarPruebas');


    //ruta ayuda
    Route::get('/cfdi/regimen/', [REGIMEN_CFDI_CONTROLLER::class, 'regimenCFDI'])->name('regimenCFDI');

    Route::get('/getCostoPromedio/{id?}/', [LogisticaComprasController::class, "getCostoPromedio"])->name('getCostoPromedio');


    Route::get('/eliminarCompra/{id?}/', [LogisticaComprasController::class, "eliminarCompra"])->name('eliminarCompra');

    Route::get('/cancelarCompra/{id?}/', [LogisticaComprasController::class, "cancelarCompra"])->name('cancelarCompra');

    Route::get('/cancelarOrden/{id?}/', [LogisticaComprasController::class, "cancelarOrdenCompleta"])->name('cancelarOrden');

    Route::get('/cancelarOrdenPendiente/{id?}/', [LogisticaComprasController::class, "cancelarOrdenPendiente"])->name('cancelarOrdenPendiente');

    Route::get('/agregarAlmacen', [LogisticaComprasController::class, "agregarAlmacen"])->name('agregarAlmacen');

    //Compras reportes
    Route::get('/logistica/compras/reportes/{idCompra}', [LogisticaComprasController::class, "getReporteCompra"])->name('vista.modulo.compras.reportes');

    //Gastos reporte
    Route::get('/logistica/gastos/reportes/{idGasto}', [GastosController::class, "getReporteGasto"])->name('vista.modulo.gastos.reportes');

    Route::get('/logistica/cuentas_por_pagar/reportes/{idCXP}', [CxpController::class, "getReporteCuentas"])->name('vista.modulo.cuentas.reportes');

    Route::get('/logistica/tesoreria/reportes/{idTesoreria}', [TesoreriaController::class, "getReporteTesoreria"])->name('vista.modulo.tesoreria.reportes');

    Route::get(('/logistica/inventarios/reportes/{idInventario}'), [LogisticaInventariosController::class, "getReporteInventario"])->name('vista.modulo.inventario.reportes');

    Route::get('/eliminarInventario/{id?}/', [LogisticaInventariosController::class, "eliminarInventario"])->name('eliminarInventario');





    Route::get('/quitarCostoPromedio/{id?}/', [LogisticaComprasController::class, "quitarCostoPromedio"])->name('quitarCostoPromedio');






    //Api´s de compras
    Route::get('/logistica/compras/api/getProveedor', [LogisticaComprasController::class, "getProveedor"])->name('api.modulo.compras.getProveedor');

    Route::get('/logistica/compras/api/getAlmacen', [LogisticaComprasController::class, "getAlmacen"])->name('api.modulo.compras.getAlmacen');

    Route::get('/logistica/compras/api/getCondicionPago', [LogisticaComprasController::class, "getCondicionPago"])->name('api.modulo.compras.getCondicionPago');

    Route::get('/logistica/compras/api/getMultiUnidad', [LogisticaComprasController::class, "getMultiUnidad"])->name('api.modulo.compras.getFactorUnidad');

    Route::get('/api/unidadFactor/decimales', [LogisticaComprasController::class, "getDecimalesUnidad"])->name('api.unidadFactor.decimales');

    Route::GET('/api/compra/getConceptosByMovimiento', [LogisticaComprasController::class, "getConceptosByMovimiento"])->name('api.compra.getConceptosByMovimiento');

    Route::get('/api/inventarios/getConceptosByMovimiento', [LogisticaInventariosController::class, "getConceptosByMovimiento"])->name('api.inventarios.getConceptosByMovimiento');




    Route::get('/logistica/compras/api/getArticulosSerie', [LogisticaComprasController::class, "getArticulosSerie"])->name('api.modulo.compras.getArticulosSerie');


    //Rutas del modulo de Gestión y Finanzas - Cuentas por Pagar

    Route::get('/agregarCxPCheque', [CxpController::class, "agregarCxPCheque"])->name('agregarCxPCheque');

    Route::get('/ayudaVer', [CxCController::class, "ayudaVer"])->name('ayudaVer');

    Route::get('/getFacturasCxC', [CxCController::class, "getFacturasCxC"])->name('getFacturasCxC');


    Route::get('/status/movimiento', [FlujoModulosController::class, "statusMovimiento"])->name('status.movimientos');

    Route::get('/cancelarCxP/{id?}/', [CxpController::class, "cancelarCxP"])->name('cancelarCxP');

    Route::get('/eliminarMovimiento/{id?}/', [CxpController::class, "eliminarMovimiento"])->name('eliminarMovimiento');

    Route::get('/gestion_finanzas/cuentas_por_pagar', [CxpController::class, "index"])->name('vista.modulo.cuentasPagar.index');

    Route::get('/gestion_finanzas/cuentas_por_pagar/create/{id?}', [CxpController::class, "create"])->name('vista.modulo.cuentasPagar.create-cxp');

    Route::post('/gestion_finanzas/cuentas_por_pagar/store/', [CxpController::class, "store"])->name('modulo.cuentasPagar.store-cxp');

    Route::post('gestion_finanzas/cuentas_por_pagar/filtro', [CxpController::class, "CXPAction"])->name('modulo.cuentasPagar.filtro');

    //Obtener el saldo del proveedor
    Route::get('/cxp/saldo/proveedor/', [CxpController::class, "getSaldoByProveedor"])->name('cxp.saldo.proveedor');

    Route::get('/cxp/getAnticipos/', [CxpController::class, "getAnticipos"])->name('cxp.getAnticipos');

    Route::get('/aplicaFolio', [CxpController::class, "aplicaFolio"])->name('aplicaFolio');

    Route::get('/getInfoCuenta', [CxpController::class, "getInfoCuenta"])->name('getInfoCuenta');

    Route::get('/getInfoCuentaBancaria', [CXCController::class, "getBalanceCuenta"])->name('getInfoCuentaBancaria');
    Route::get('/getAnticipo', [CXCController::class, "getAnticipo"])->name('getAnticipo');


    //Cuentas por pagar afectar

    Route::post('/gestion_finanzas/cuentas_por_pagar/afectar/{id?}/', [CxpController::class, "afectar"])->name('vista.modulo.cuentasPagar.afectar');

    //Anexos Gasto
    Route::get('/gestion_finanzas/cuentas_por_pagar/{id?}/anexos', [CXPAnexosController::class, "index"])->name('vista.modulo.cuentasPagar.anexos');

    Route::post('/gestion_finanzas/cuentas_por_pagar/{id?}/anexos/store', [CXPAnexosController::class, "store"])->name('modulo.cuentasPagar.anexos.store');

    Route::delete('/gestion_finanzas/cuentas_por_pagar/anexos/delete/{id}/', [CXPAnexosController::class, "destroy"])->name('modulo.cuentasPagar.anexos.delete');


    //Ayuda cxp
    Route::get('/cxp/ayuda/', [CxpController::class, "ayudaMov"])->name('cxp.getProveedor');




    //Rutas del modulo de Gestión y Finanzas - Cuentas por Cobrar

    Route::get('/gestion_finanzas/cuentas_por_cobrar', [CXCController::class, "index"])->name('vista.modulo.cuentasCobrar.index');

    Route::get('/gestion_finanzas/cuentas_por_cobrar/create/{id?}', [CXCController::class, "create"])->name('vista.modulo.cuentasCobrar.create-cxc');

    Route::post('/gestion_finanzas/cuentas_por_cobrar/store/', [CXCController::class, "store"])->name('modulo.cuentasCobrar.store-cxc');

    Route::post('gestion_finanzas/cuentas_por_cobrar/filtro', [CXCController::class, "CXCAction"])->name('modulo.cuentasxcobrar.filtro');

    Route::get('/cxc/getAnticipos/', [CXCController::class, "getAnticipos"])->name('cxc.getAnticipos');





    //API´s de cuentas por cobrar y por pagar
    Route::get('/cxc/saldo/cliente/', [CxCController::class, "getSaldoByProveedor"])->name('cxc.saldo.cliente');

    Route::post('/gestion_finanzas/cuentas_por_cobrar/afectar/{id?}/', [CxCController::class, "afectar"])->name('vista.modulo.cuentasCobrar.afectar');

    Route::get('/gestion_finanzas/cuentas_por_cobrar/api/getCliente', [CXCController::class, "getClientes"])->name('api.modulo.cxc.getClientes');

    Route::GET('/api/cuentas_por_cobrar/getConceptosByMovimiento', [CXCController::class, "getConceptosByMovimiento"])->name('api.cxc.getConceptosByMovimiento');

    //ahora de cuentas por pagar
    Route::get('/api/cuentas_por_pagar/getConceptosByMovimiento', [CxpController::class, "getConceptosByMovimiento"])->name('api.cxp.getConceptosByMovimiento');

    Route::get('/aplicaFolio/cxc', [CxCController::class, "aplicaFolio"])->name('aplicaFolioCxc');

    Route::get('/cancelarCxC/{id?}/', [CxCController::class, "cancelarCxC"])->name('cancelarCxC');

    Route::get('/eliminar/cxc', [CXCController::class, "eliminarMovimientoCxC"])->name('eliminarMovimientoCxC');

    Route::get('/gestion_finanzas/cuentas_por_cobrar/reportes/{idCXC}', [CxCController::class, "getReporteCuentas"])->name('vista.modulo.cuentasCxC.reportes');

    //Anexos CXC
    Route::get('/gestion_finanzas/cuentas_por_cobrar/{id?}/anexos', [CXCAnexosController::class, "index"])->name('vista.modulo.cxc.anexos');

    Route::post('/gestion_finanzas/cuentas_por_cobrar/{id?}/anexos/store', [CXCAnexosController::class, "store"])->name('modulo.cxc.anexos.store');

    Route::delete('/gestion_finanzas/cuentas_por_cobrar/anexos/delete/{id}/', [CXCAnexosController::class, "destroy"])->name('modulo.cxc.anexos.delete');


    //Rutas del módulo de Gestión y Finanzas - Gastos

    Route::get('/cancelarGasto/{id?}/', [GastosController::class, "cancelarGasto"])->name('cancelarGasto');

    Route::get('/eliminarGasto/{id?}/', [GastosController::class, "eliminarGasto"])->name('eliminarGasto');

    Route::get('/gestion_finanzas/gastos', [GastosController::class, "index"])->name('vista.modulo.gastos.index');

    Route::get('/gestion_finanzas/gastos/create/{id?}', [GastosController::class, "create"])->name('vista.modulo.gastos.create-gasto');

    Route::post('/gestion_finanzas/gastos/store', [GastosController::class, "store"])->name('modulo.gastos.store-gasto');

    Route::post('/gestion_finanzas/gastos/afectar/{id?}/', [GastosController::class, "afectar"])->name('modulo.gastos.afectar-gasto');

    Route::post('gestion_finanzas/gastos/filtro', [GastosController::class, "gastosAction"])->name('modulo.gastos.filtro');



    //Anexos Gasto
    Route::get('/gestion_finanzas/gastos/{id?}/anexos', [GastoAnexosController::class, "index"])->name('vista.modulo.gastos.anexos');

    Route::post('/gestion_finanzas/gastos/{id?}/anexos/store', [GastoAnexosController::class, "store"])->name('modulo.gastos.anexos.store');

    Route::delete('/gestion_finanzas/gastos/anexos/delete/{id}/', [GastoAnexosController::class, "destroy"])->name('modulo.gastos.anexos.delete');


    //Rutas del módulo de Gestión y Finanzas - Tesorería

    Route::get('/gestion_finanzas/tesoreria', [TesoreriaController::class, "index"])->name('vista.modulo.tesoreria.index');

    Route::get('/gestion_finanzas/tesoreria/create/{id?}', [TesoreriaController::class, "create"])->name('vista.modulo.tesoreria.create-tesoreria');

    Route::post('/gestion_finanzas/tesoreria/store/{id?}', [TesoreriaController::class, "store"])->name('modulo.tesoreria.store-tesoreria');

    Route::get('/cancelarTesoreria/{id?}/', [TesoreriaController::class, "cancelarTesoreria"])->name('cancelarTesoreria');

    //Traemos el saldo de la cuenta seleccionada
    Route::get('/tesoreria/saldo/cuenta/', [TesoreriaController::class, "getSaldoByCuenta"])->name('tesoreria.saldo.cuenta');

    //Traemos el nombre de la cuenta seleccionada
    Route::get('/tesoreria/nombre/cuenta/', [TesoreriaController::class, "getNombreCuenta"])->name('tesoreria.nombre.cuenta');

    //Afectar tesoreria
    Route::post('/gestion_finanzas/tesoreria/afectar/{id?}/', [TesoreriaController::class, "afectar"])->name('vista.modulo.tesoreria.afectar');

    //Filtro tesoreria
    Route::post('gestion_finanzas/tesoreria/filtro', [TesoreriaController::class, "TesoreriaAction"])->name('modulo.tesoreria.filtro');

    //ajax para traer los datos de la cuenta

    Route::get('/gestion_finanzas/tesoreria/api/getProveedor', [TesoreriaController::class, "getProveedor"])->name('api.modulo.tesoteria.getProveedor');

    //getConceptosByMovimiento de tesoreria
    Route::GET('/api/tesoreria/getConceptosByMovimiento', [TesoreriaController::class, "getConceptosByMovimiento"])->name('api.tesoreria.getConceptosByMovimiento');

    //Ayuda compras
    Route::get('/compras/ayuda', [LogisticaComprasController::class, "ayuda"])->name('compras.ayuda');
    //Ayuda Tesoreria
    Route::get('/tesoreria/ayuda/', [TesoreriaController::class, "ayudaTesoreria"])->name('tesoreria.getProveedor');

    Route::get('/gestion_finanzas/tesoreria/{id?}/anexos', [TesoreriaAnexosController::class, "index"])->name('vista.modulo.tesoreria.anexos');

    Route::post('/gestion_finanzas/tesoreria/{id?}/anexos/store', [TesoreriaAnexosController::class, "store"])->name('modulo.tesoreria.anexos.store');

    Route::delete('/gestion_finanzas/tesoreria/anexos/delete/{id}/', [TesoreriaAnexosController::class, "destroy"])->name('modulo.tesoreria.anexos.delete');

    //Rutas de los modulos Comercial - Ventas

    Route::get('/comercial/ventas', [VentasController::class, "index"])->name('vista.modulo.ventas');

    Route::get('/asignarFolio', [VentasController::class, 'asignarFolio'])->name('asignarFolio');

    Route::get('/comercial/ventas/create/{id?}/', [VentasController::class, "create"])->name('vista.modulo.ventas.create-venta');

    Route::post('/comercial/ventas/store/', [VentasController::class, "store"])->name('modulo.ventas.store-venta');

    Route::get('/getCostoPromedio3/ventas{id?}/', [VentasController::class, "getCostoPromedio"])->name('getCostoPromedio3.ventas');

    Route::get('/agregarTesoreria', [VentasController::class, "agregarTesoreria"])->name('agregarTesoreria');

    Route::post('/comercial/ventas/afectar/{id?}', [VentasController::class, "afectar"])->name('vista.modulo.ventas.afectar');

    Route::get('/comercial/ventas/api/getCliente', [VentasController::class, "getCliente"])->name('api.modulo.compras.getCliente');

    //getConceptosByMovimiento de ventas
    Route::GET('/api/ventas/getConceptosByMovimiento', [VentasController::class, "getConceptosByMovimiento"])->name('api.ventas.getConceptosByMovimiento');

    Route::get('/getPlacas', [VentasController::class, "getPlacas"])->name('getPlacas');

    Route::get('/listaEmpaques', [VentasController::class, "listaEmpaques"])->name('listaEmpaques');
    Route::get('/getListaEmpaques', [VentasController::class, "getListaEmpaques"])->name('getListaEmpaques');
    Route::get('/deleteListaEmpaques', [VentasController::class, "deleteListaEmpaques"])->name('deleteListaEmpaques');

    Route::get('/auxiliarU', [VentasController::class, "auxiliarU"])->name('auxiliarU');

    Route::get('/afectarTimbrado', [VentasController::class, "afectarTimbrado"])->name('afectarTimbrado');
    Route::get('/afectarTimbradoCxc', [CXCController::class, "timbrarCxcModule"])->name('afectarTimbradoCxc');

    Route::get('/consultarEstado', [TimbradoController::class, "consultaEstadoCancelacion"])->name('consultarEstado');


    Route::get('/enviarEmail', [VentasController::class, "enviarEmail"])->name('enviarEmail');

    Route::get('/precioLista', [VentasController::class, "precioLista"])->name('precioLista');
    Route::get('/precioListaExistencia', [VentasController::class, "precioListaConExistencia"])->name('precioListaExistencia');

    Route::get('/articulosCategoria', [VentasController::class, "articulosCategoria"])->name('articulosCategoria');

    Route::get('/articulosFamilia', [VentasController::class, "articulosFamilia"])->name('articulosFamilia');

    Route::get('/articulosGrupo', [VentasController::class, "articulosGrupo"])->name('articulosGrupo');

    Route::get('/invArtDepot', [LogisticaInventariosController::class, "articulosInventarioDepot"])->name('invArtDepot');

    Route::get('/invArtExistencia', [LogisticaInventariosController::class, "articulosInventarioExistencia"])->name('invArtExistencia');

    Route::get('/invArtCategoria', [LogisticaInventariosController::class, "articulosInventarioCategoria"])->name('invArtCategoria');

    Route::get('/invArtFamilia', [LogisticaInventariosController::class, "articulosInventarioFamilia"])->name('invArtFamilia');

    Route::get('/invArtGrupo', [LogisticaInventariosController::class, "articulosInventarioGrupo"])->name('invArtGrupo');

    Route::get('/buscador/articulos/venta', [VentasController::class, "buscardorArticulosInventarios"])->name('ventas.buscardorArticulosInventarios');
    Route::get('/armarKits', [VentasController::class, "armarKits"])->name('ventas.armarKits');
    Route::get('/armarKits2', [VentasController::class, "armarKits2"])->name('ventas.armarKits2');


    Route::get('/buscarKits', [VentasController::class, "buscarKits"])->name('ventas.buscarKits');

    Route::get('/generarPDF', [TimbradoController::class, "generarPDF"])->name('generarPDF');

    Route::get('/verCorreo', [TimbradoController::class, "verCorreo"])->name('verCorreo');

    Route::get('/comercial/ventas/api/getMultiUnidad', [VentasController::class, "getMultiUnidad"])->name('api.modulo.ventas.getFactorUnidad');

    Route::get('/comercial/ventas/api/getCondicionPago', [VentasController::class, "getCondicionPago"])->name('api.modulo.ventas.getCondicionPago');

    Route::post('logistica/ventas/filtro', [VentasController::class, "ventasAction"])->name('logistica.ventas.filtro');

    Route::get('/comercial/ventas/reportes/{idVenta}', [VentasController::class, "getReporteVenta"])->name('vista.modulo.ventas.reportes');

    Route::get('/comercial/ventas/nota-venta/{idVenta}', [VentasController::class, "getReporteNotaVenta"])->name('vista.modulo.notaVenta.reportes');
    

    Route::get('/comercial/ventas/cotizacion/{idVenta}', [VentasController::class, "getReporteCotización"])->name('vista.modulo.ventas.cotizacion');

    Route::post('/comercial/ventas/Enviarcotizacion/{idVenta}', [VentasController::class, "enviarCorreoCotizacion"])->name('vista.modulo.ventas.emailCotizacion');


    Route::get('/comercial/ventas/cotizacion-sin-impuestos/{idVenta}', [VentasController::class, "getReporteCotizaciónSimpuestos"])->name('vista.modulo.ventas.cotizacion-sin-impuestos');

    Route::get('/comercial/ventas/formato-entrega/{idVenta}', [VentasController::class, "getReporteFormatoEntrega"])->name('vista.modulo.ventas.formato-entrega');

    Route::get('/comercial/ventas/factura', [VentasController::class, "getFactura"])->name('vista.modulo.ventas.factura');

    Route::get('/eliminarVenta/{id?}/', [VentasController::class, "eliminarVenta"])->name('eliminarVenta');

    Route::get('/cancelarVenta/{id?}/', [VentasController::class, "cancelarVenta"])->name('cancelarVenta');

    Route::get('/cancelarMovimiento/{id?}/', [VentasController::class, "cancelarMovimiento"])->name('cancelarMovimiento');

    Route::get('/cancelarMovPendiente/{id?}/', [VentasController::class, "cancelarMovPendiente"])->name('cancelarMovPendiente');

    Route::get('/comercial/ventas/api/getCliente', [VentasController::class, "getCliente"])->name('nombreClienteTesoreria');

    Route::get('/logistica/ventas/api/getSeriesSeleccionados', [VentasController::class, "getSeriesGuardados"])->name('api.modulo.ventas.getSeriesSeleccionados');


    //Anexos Venta
    Route::get('/comercial/ventas/{id?}/anexos', [VentasAnexosController::class, "index"])->name('vista.modulo.ventas.anexos');


    Route::post('/comercial/ventas/{id?}/anexos/store', [VentasAnexosController::class, "store"])->name('modulo.ventas.anexos.store');

    Route::delete('/comercial/ventas/anexos/delete/{id}/', [VentasAnexosController::class, "destroy"])->name('modulo.ventas.anexos.delete');

    Route::get('/comercial/delete/articuloKit/', [ArticulosController::class, 'deleteKitArticle'])->name('delete.articulo');

    //Inventarios
    Route::get('/logistica/inventarios', [LogisticaInventariosController::class, "index"])->name('vista.modulo.inventarios');

    Route::post('logistica/inventarios/filtro', [LogisticaInventariosController::class, "inventariosAction"])->name('logistica.inventarios.filtro');


    Route::get('/logistica/inventario/{id?}/', [LogisticaInventariosController::class, "create"])->name('vista.modulo.inventarios.create-inventario');

    Route::post('/logistica/inventario/store', [LogisticaInventariosController::class, "store"])->name('modulo.inventarios.store-inventario');


    Route::post('/logistica/inventarios/afectar/{id?}/', [LogisticaInventariosController::class, "afectar"])->name('vista.modulo.inventarios.afectar');

    Route::post('/logistica/inventarios/afectar/{id?}/', [LogisticaInventariosController::class, "afectar"])->name('vista.modulo.inventarios.afectar');

    Route::get('/concluirOrigines', [LogisticaInventariosController::class, "concluirOrigines"])->name('concluirOrigines');

    Route::get('/getCostoPromedio2/inventarios{id?}/', [LogisticaInventariosController::class, "getCostoPromedio"])->name('getCostoPromedio2.inventarios');

    Route::get('/getAlmacenesDestino/{id?}/', [LogisticaInventariosController::class, "getAlmacenesDestino"])->name('getAlmacenesDestino');

    Route::get('/costoPromedio', [LogisticaInventariosController::class, "costoPromedio"])->name('CostoPromedio.inventarios');
    Route::get('/comercial/ventas/api/getCosto', [LogisticaInventariosController::class, "getCosto"])->name('api.modulo.ventas.getCosto');

    Route::get('/logistica/inventarios/api/getArticulosSerie', [LogisticaInventariosController::class, "getArticulosSerie"])->name('api.modulo.inventarios.getArticulosSerie');

    Route::get('/logistica/inventarios/api/getSeries', [LogisticaInventariosController::class, "getSeries"])->name('api.modulo.inventarios.getSeries');

    Route::get('/logistica/inventarios/api/getSeriesSeleccionados', [LogisticaInventariosController::class, "getSeriesGuardados"])->name('api.modulo.inventarios.getSeriesSeleccionados');
    //Api´s de inventarios

    Route::get('/logistica/inventarios/api/getMultiUnidad', [LogisticaInventariosController::class, "getMultiUnidad"])->name('api.modulo.inventarios.getFactorUnidad');

    Route::get('/logistica/inventarios/api/getCostoUnitario', [LogisticaInventariosController::class, "getCostoUnitario"])->name('api.modulo.inventarios.getCostoUnitario');

    Route::get('/cancelarInventario/{id?}/', [LogisticaInventariosController::class, "cancelarInventario"])->name('cancelarInventario');


    //Anexos Inventarios
    Route::get('/logistica/inventarios/{id?}/anexos', [InventariosAnexosController::class, "index"])->name('vista.modulo.inventarios.anexos');

    Route::post('/logistica/inventarios/{id?}/anexos/store', [InventariosAnexosController::class, "store"])->name('modulo.inventarios.anexos.store');

    Route::delete('/logistica/inventarios/anexos/delete/{id}/', [InventariosAnexosController::class, "destroy"])->name('modulo.inventarios.anexos.delete');

    //Rutas de Reportes compras

    Route::get('/reportes/Reportecompras/por_unidad', [ReportesComprasUnidadController::class, "index"])->name('vista.reportes.compras-unidad');

    Route::post('reportes/Reportecompras/por_unidad/filtro', [ReportesComprasUnidadController::class, "reportesAction"])->name('reportes.compras.unidad.filtro');


    route::get('/reportes/Reportecompras/por_articulo_provedor', [ReportesComprasArticuloProvController::class, "index"])->name('vista.reportes.compras-articulo-provedor');

    Route::post('reportes/Reportecompras/por_articulo_provedor/filtro', [ReportesComprasArticuloProvController::class, "reportesArtAction"])->name('reportes.compras.articulo-proveedor.filtro');

    route::get('/reportes/Reportecompras/acumulado-por-articulo-proveedor', [ReportesComprasAcumArticuloProveedor::class, "index"])->name('vista.reportes.acumulado-por-articulo-proveedor');

    Route::post('reportes/Reportecompras/acumulado-por-articulo-proveedor/filtro', [ReportesComprasAcumArticuloProveedor::class, "reportesAcumArtAction"])->name('reportes.compras.acumulado-articulo-proveedor.filtro');

    route::get('/reportes/Reportecompras/compras-con-series', [ReportesComprasSeriesController::class, "index"])->name('vista.reportes.compras-con-series');

    Route::post('reportes/Reportecompras/compras-con-series/filtro', [ReportesComprasSeriesController::class, "reportesArtAction"])->name('reportes.compras.series.filtro');

    //Rutas de Reportes Gastos

    Route::get('/reportes/ReporteGasto/por_concepto', [ReportesGastosConceptoController::class, "index"])->name('vista.reportes.gastos-concepto');

    Route::post('reportes/ReporteGasto/por_concepto/filtro', [ReportesGastosConceptoController::class, "reportesGastosAction"])->name('reportes.gastos.concepto.filtro');


    Route::get('/reportes/ReporteGasto/por_antecedente_activo_fijo', [ReportesGastosAntecedentesActivoFController::class, "index"])->name('vista.reportes.gastos-antecedente-activo-fijo');

    Route::post('reportes/ReporteGasto/por_antecedente_activo_fijo/filtro', [ReportesGastosAntecedentesActivoFController::class, "reportesGastosATAFAction"])->name('reportes.gastos.antecedente-activo-fijo.filtro');


    //Rutas de Reportes CXP

    Route::get('/reportes/Reportecxp/antiguedad_saldos', [ReportesCXPAntiguedadSaldosController::class, "index"])->name('vista.reportes.cxp-antiguedad-saldos');

    Route::post('reportes/Reportecxp/antiguedad_saldos/filtro', [ReportesCXPAntiguedadSaldosController::class, "reportesCXPAction"])->name('reportes.cxp.antiguedad-saldos.filtro');

    Route::get('/reportes/Reportecxp/estado_cuenta', [ReportesCXPEstadoCuentaController::class, "index"])->name('vista.reportes.cxp-estado-cuenta');

    Route::post('reportes/Reportecxp/estado_cuenta/filtro', [ReportesCXPEstadoCuentaController::class, "reportesCXPEstadoCuentaAction"])->name('reportes.cxp.estado-cuenta.filtro');

    //Rutas de Reportes CXC

    Route::get('/reportes/Reportecxc/antiguedad_de_saldos', [ReportesCXCAntiguedadSaldosController::class, "index"])->name('vista.reportes.cxc-antiguedad-saldos');

    Route::post('reportes/Reportecxc/antiguedad_de_saldos/filtro', [ReportesCXCAntiguedadSaldosController::class, "reportesCXCAction"])->name('reportes.cxc.antiguedad-saldos.filtro');

    Route::get('/reportes/Reportecxc/estado_de_cuenta', [ReportesCXCEstadoCuentaController::class, "index"])->name('vista.reportes.cxc-estado-cuenta');

    Route::post('reportes/Reportecxc/estado_de_cuenta/filtro', [ReportesCXCEstadoCuentaController::class, "reportesCXCEstadoCuentaAction"])->name('reportes.cxc.estado-cuenta.filtro');

    Route::get('/reportes/Reportecxc/cobranza-forma-cobro', [ReportesCXCCobranzaCobroController::class, "index"])->name('vista.reportes.cxc-cobranza-forma-cobro');

    Route::post('reportes/Reportecxc/cobranza-forma-cobro/filtro', [ReportesCXCCobranzaCobroController::class, "reportesCXCCobranzaCobroAction"])->name('reportes.cxc.cobranza-forma-cobro.filtro');


    //Rutas de Reportes Tesoreria

    Route::get('/eliminarMovTeso', [TesoreriaController::class, "eliminarMovimiento"])->name('eliminarMovimientoTes');

    Route::get('/reportes/ReporteTesoreria/Concentrados', [ReportesTesoreriaConcentradosController::class, "index"])->name('vista.reportes.tesoreria-concentrados');

    Route::post('reportes/ReporteTesoreria/Concentrados/filtro', [ReportesTesoreriaConcentradosController::class, "reportesTesoreriaAction"])->name('reportes.tesoreria.concentrados.filtro');

    Route::get('/reportes/ReporteTesoreria/Desglosado', [ReportesTesoreriaDesglosadoController::class, "index"])->name('vista.reportes.tesoreria-desglosado');

    Route::post('reportes/ReporteTesoreria/Desglosado/filtro', [ReportesTesoreriaDesglosadoController::class, "reportesTesoreriaDesglosadoAction"])->name('reportes.tesoreria.desglosado.filtro');

    //Rutas de Reportes ventas

    Route::get('/reportes/ReporteVentas/por_acumulado', [ReportesVentasAcumuladoController::class, "index"])->name('vista.reportes.ventas-acumulado');

    Route::post('reportes/ReporteVentas/por_acumulado/filtro', [ReportesVentasAcumuladoController::class, "reportesVentasAcumuladoAction"])->name('reportes.ventas.acumulado.filtro');

    Route::get('/reportes/ReporteVentas/por_articulo_cliente', [ReportesVentasArticuloClientesController::class, "index"])->name('vista.reportes.ventas-cliente-articulo');

    Route::post('reportes/ReporteVentas/por_articulo_cliente/filtro', [ReportesVentasArticuloClientesController::class, "reportesVentasClienteAction"])->name('reportes.ventas.cliente.filtro');

    Route::get('/reportes/ReporteVentas/por_acumulado-cliente', [ReportesVentasAcumArticuloCliente::class, "index"])->name('vista.reportes.ventas-acumulado-cliente');

    Route::post('reportes/ReporteVentas/por_acumulado-cliente/filtro', [ReportesVentasAcumArticuloCliente::class, "reportesVentasAcumuladoClienteAction"])->name('reportes.ventas.acumulado-cliente.filtro');

    Route::get('/reportes/ReporteVentas/ventas-por-serie', [ReportesVentasSeriesController::class, "index"])->name('vista.reportes.ventas-serie');

    Route::post('/reportes/ReporteVentas/ventas-por-serie/filtro', [ReportesVentasSeriesController::class, "reportesArtAction"])->name('reportes.ventas.ventas-serie.filtro');

    // Route::post('reportes/ReporteVentas/ventas-por-serie/filtro', [ReportesVentasAcumArticuloCliente::class, "reportesVentasAcumuladoClienteAction"])->name('reportes.ventas.acumulado-cliente.filtro');

    Route::get('/reportes/ReporteVentas/ventas-producto-mas-vendido', [ReportesVentasProductoMasVendidoController::class, "index"])->name('vista.reportes.ventas-producto-mas-vendido');

    Route::post('reportes/ReporteVentas/ventas-producto-mas-vendido/filtro', [ReportesVentasProductoMasVendidoController::class, "reportesVentasProductoMasVendidoAction"])->name('reportes.ventas.producto-mas-vendido.filtro');

    Route::get('/reportes/ReporteVentas/ventas-ganancia', [ReportesVentasVSGananciasController::class, "index"])->name('vista.reportes.ventas-ganancia');

    Route::post('reportes/ReporteVentas/ventas-ganancia/filtro', [ReportesVentasVSGananciasController::class, "reportesVentasGananciaAction"])->name('reportes.ventas.ganancia.filtro');





    //Rutas de Lista de Precios

    Route::get('/reportes/listaPrecios', [ReportesListaPreciosController::class, "index"])->name('vista.reportes.listaPrecios');

    Route::post('reportes/listaPrecios/filtro', [ReportesListaPreciosController::class, "reportesListaPreciosAction"])->name('reportes.listaPrecios.filtro');


    //Rutas de Reportes inventarios General

    Route::get('/reportes/ReporteInventario/general', [ReportesInventariosGeneralController::class, "index"])->name('vista.reportes.inventario.general');

    Route::post('reportes/ReporteInventario/general/filtro', [ReportesInventariosGeneralController::class, "reportesInventarioGeneralAction"])->name('reportes.inventario.general.filtro');

    //Rutas de Reportes inventarios Desglosado

    Route::get('/reportes/ReporteInventario/desglosado', [ReportesInventariosDesglosadoController::class, "index"])->name('vista.reportes.inventario.desglosado');

    Route::post('reportes/ReporteInventario/desglosado/filtro', [ReportesInventariosDesglosadoController::class, "reportesInventarioDesglosadoAction"])->name('reportes.inventario.desglosado.filtro');

    Route::get('/reportes/ReporteInventario/concentrado', [ReportesInventariosConcentradoController::class, "index"])->name('vista.reportes.inventario.concentrado');

    Route::post('reportes/ReporteInventario/concentrado/filtro', [ReportesInventariosConcentradoController::class, "reportesInventarioConcentradoAction"])->name('reportes.inventario.concentrado.filtro');

    Route::get('/reportes/ReporteInventario/costo-dia', [ReportesInventariosCostoDiaController::class, "index"])->name('vista.reportes.inventario.costo-dia');

    Route::post('reportes/ReporteInventario/costo-dia/filtro', [ReportesInventariosCostoDiaController::class, "reportesInventarioCostoDiaAction"])->name('reportes.inventario.costo-dia.filtro');


    //Rutas de Reportes Utilidad Ventas vs Gastos

    Route::get('/reportes/ReporteUtilidad/gerenciales', [ReportesUtilidadController::class, "index"])->name('vista.reportes.utilidad-ventas-vs-gastos');

    Route::post('reportes/ReporteUtilidad/gerenciales/filtro', [ReportesUtilidadController::class, "reportesUtilidadAction"])->name('reportes.utilidad.ventas-vs-gastos.filtro');


    //herramienta controller
    Route::get('/herramientas/index', [herramientaController::class, "index"])->name('herramienta.index');

    Route::get('/herramientas/cambioCostos/index', [CambioCostosController::class, "index"])->name('herramienta.cambioCostos.index');
    Route::get('/herramientas/cambioCostos/listas', [CambioCostosController::class, "listas"])->name('herramienta.cambioCostos.listas');
    Route::post('/herramientas/cambioCostos/procesar', [CambioCostosController::class, "store"])->name('herramienta.cambioCostos.procesar');

    Route::get('/herramientas/cambioPreciosVenta/index', [CambioPreciosVentaController::class, "index"])->name('herramienta.cambioPreciosVenta.index');
    Route::get('/herramientas/cambioPreciosVenta/listaPrecios', [CambioPreciosVentaController::class, "listaPrecios"])->name('herramienta.cambioPreciosVenta.listasPrecios');
    Route::post('/herramientas/cambioPreciosVenta/procesar', [CambioPreciosVentaController::class, "store"])->name('herramienta.cambioPreciosVenta.procesar');

    Route::post('/herramientas/store', [herramientaController::class, "store"])->name('herramienta.store');
    Route::get('/getArticulosAlmacen', [herramientaController::class, "getArticulosAlmacen"])->name('herramienta.getArticulosAlmacen');
    Route::get('/getSelectSucursales', [herramientaController::class, "getSelectSucursales"])->name('herramienta./getSelectSucursales');
    Route::get('/getSelectAlmacenes', [herramientaController::class, "getSelectAlmacenes"])->name('herramienta./getSelectAlmacenes');
    //API para buscar un articulo por medio de su key
    Route::get('/herramientas/buscar_articulo', [LogisticaComprasController::class, 'getArticulo'])->name('articulo.buscar_articulo');

    //creamos una ruta para los reportes, será un index pero no lo haremos desde el controlador sin usar controlador
    Route::get('/reportes/compras', [ReporteriaController::class, "indexCompras"])->name('vista.reportes.compras');
    //de gastos
    Route::get('/reportes/gastos', [ReporteriaController::class, "indexGastos"])->name('vista.reportes.gastos');
    //de cxp
    Route::get('/reportes/cxp', [ReporteriaController::class, "indexCxP"])->name('vista.reportes.cxp');
    //de tesoreria
    Route::get('/reportes/tesoreria', [ReporteriaController::class, "indexTesoreria"])->name('vista.reportes.tesoreria');
    //de ventas ahora
    Route::get('/reportes/ventas', [ReporteriaController::class, "indexVentas"])->name('vista.reportes.ventas');
    //de cxc
    Route::get('/reportes/cxc', [ReporteriaController::class, "indexCxC"])->name('vista.reportes.cxc');
    //de inventarios
    Route::get('/reportes/inventarios', [ReporteriaController::class, "indexInventarios"])->name('vista.reportes.inventarios');
    //de utilidad
    Route::get('/reportes/utilidad', [ReporteriaController::class, "indexGerenciales"])->name('vista.reportes.gerenciales');
    // Agregar a favoritos
    Route::post('/add-to-favorites', [ReporteriaController::class, 'addFavorite']);
    // Verificar si un reporte es un favorito
    Route::get('/check-favorite', [ReporteriaController::class, 'checkFavorite']);
    // Eliminar un favorito
    Route::post('/remove-favorite', [ReporteriaController::class, 'removeFavorite']);
});




//Login select y Validaciones de usuarios, empresas y sucursal
Route::get('/login/user/verificacion', [LoginController::class, 'verificacionUsuario'])->name('login.user.verificacion');
Route::get('/login/user/verificacion/password', [LoginController::class, 'verificacionPassword'])->name('login.user.verificacion.password');

Route::post('/login/license', [UsuariosController::class, 'licenceApp'])->name('login.license');
Route::post('/login/license/verificate', [UsuariosController::class, 'verificate'])->name('login.license.verificate');

//rutas para selects de los formularios
Route::get('/cp/busqueda/', [CpController::class, 'buscarCp'])->name('cp.busqueda');
Route::get('/colonia/busqueda/', [ColoniaController::class, 'buscarColonia'])->name('colonia.busqueda');
Route::get('/prodServ/busqueda', [ProdServController::class, 'buscarProdServ'])->name('prodServ.busqueda');

Route::get('/fraccionArancelaria/busqueda', [ProdServController::class, 'buscarfraccionArancelaria'])->name('fraccionArancelaria.busqueda');

Route::get('/unidadAduana/busqueda', [ProdServController::class, 'buscarunidadAduana'])->name('unidadAduana.busqueda');

Route::get('/getTipoCambio', [LogisticaComprasController::class, 'getTipoCambio'])->name('getTipoCambio');
Route::get('/getcostoArticulo', [LogisticaInventariosController::class, 'getCostoArticulo'])->name('getcostoArticulo');


Route::get('/empresasById', [LoginController::class, 'empresasById'])->name('empresasById');
Route::get('/empresaDatosById', [LoginController::class, 'empresaById'])->name('empresaById');
Route::get('/sucursalByClaveEmpresa', [LoginController::class, 'sucursalByClaveEmpresa'])->name('sucursalByClaveEmpresa');





// ID'S AUTOMATICOS DE ALGUNAS VISTAS
Route::get('/create/getIDAgente', [AgentesController::class, "getCategoriaAgente"])->name('AgenteCategoriaId');
Route::get('/create/getIDVehiculo', [VehiculosController::class, "getIDVehiculo"])->name('VehiculoCategoriaId');
Route::get('/catalogo/proveedor/getId', [ProveedorController::class, "getProvider"])->name('catalogo.proveedor.getId');
Route::get('/catalogo/articulo/getId', [ArticulosController::class, "getArticle"])->name('catalogo.articulo.getId');


Route::get('/archivo/{path1?}/{path2?}/{path3?}/{path4?}/{path5?}/{path6?}/{path7?}/{path8?}/{path9?}/{path10?}/{path11?}/{path12?}/{path13?}/{path14?}/{path15?}/{path16?}/{path17?}/{path18?}/{path19?}/{path20?}', FilesController14::class);

//api dashboard
Route::post('/api/getArticulos', [DashboardController::class, 'getTop10SalesDetails'])->name('api.getArticulos');
Route::post('/api/getVentasXFamilia', [DashboardController::class, 'getSalesByFamily'])->name('api.getVentasXFamilia');
Route::post('/api/getVentasXMes', [DashboardController::class, 'getCurrentMonthVSLastMonth'])->name('api.getVentasXMes');
Route::post('api/getVentasFlujo', [DashboardController::class, 'getSalesAndFlows'])->name('api.getVentasFlujo');
Route::post('api/getVentasVSGanancia', [DashboardController::class, 'getSalesVSProfit'])->name('api.getVentasVSGanancia');
Route::post('api/getGananciaVSGastos', [DashboardController::class, 'getEarningAndExpenses'])->name('api.getGananciaVSGastos');


