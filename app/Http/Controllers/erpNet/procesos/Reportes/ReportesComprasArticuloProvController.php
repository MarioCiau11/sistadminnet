<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesComprasArticuloProveedorExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_PROVIDER_CATEGORY;
use App\Models\agrupadores\CAT_PROVIDER_GROUP;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_DEPOTS;
use App\Models\catalogos\CAT_PROVIDERS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\catalogos\CONF_UNITS;
use App\Models\modulos\PROC_PURCHASE;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportesComprasArticuloProvController extends Controller
{
    public function index()
    {

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }
        $select_sucursales = $this->selectSucursales();
        $selectMonedas = $this->getMonedas();
        $unidad = $this->selectUnidades();
        $almacen = $this->selectAlmacenes();
        $articulos = $this->selectArticulos();
        $proveedores = $this->selectProveedores();
        $categorias = $this->selectCategoria();
        $grupos = $this->selectGrupo();
        $compras = PROC_PURCHASE::join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
            ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CONF_CREDIT_CONDITIONS', 'PROC_PURCHASE.purchase_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
            ->join('CAT_COMPANIES', 'PROC_PURCHASE.purchase_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('CAT_DEPOTS', 'PROC_PURCHASE.purchase_depot', '=', 'CAT_DEPOTS.depots_key')
            ->join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
            ->join('CAT_ARTICLES', 'PROC_PURCHASE_DETAILS.purchaseDetails_article', '=', 'CAT_ARTICLES.articles_key')
            ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
            ->where('PROC_PURCHASE.purchase_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->where('PROC_PURCHASE.purchase_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->where('PROC_PURCHASE.purchase_movement', '=', 'Entrada por Compra')
            ->where('PROC_PURCHASE.purchase_status', '=', 'FINALIZADO')
            ->orderBy('PROC_PURCHASE.updated_at', 'DESC')
            ->paginate(25);

        // dd($compras);



        return view('page.Reportes.Compras.indexReporteComprasArticuloProveedor', compact('select_sucursales', 'selectMonedas', 'parametro', 'unidad', 'almacen', 'compras', 'articulos', 'proveedores', 'categorias', 'grupos'));
    }

    //función que sirve para la búsqueda, exportar a excel y PDF de compras por artículo y proveedor

    public function reportesArtAction(Request $request)
    {
        /* Obtener los valores del formulario y almacenarlos en variables. */
        $nameProveedor = $request->nameProveedor;
        $nameCategoria = $request->nameCategoria;
        $nameGrupo = $request->nameGrupo;
        $nameMov = $request->nameMov;
        $nameArticulo = $request->nameArticulo;
        $nameUnidad = $request->nameUnidad;
        $nameFecha = $request->nameFecha;
        $nameAlmacen = $request->nameAlmacen;
        $nameSucursal = $request->nameSucursal;
        $nameMoneda = $request->nameMoneda;
        $status = $request->status;

        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;

        //esto nos sirve para el rango de fechas, para validar que no estén vacíos
        if (
            $fechaInicio !== null && $fechaFinal !== null && $nameFecha === "Rango Fechas"
        ) {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }

        // dd($nameProveedor, $nameCategoria, $nameGrupo, $nameMov, $nameArticulo, $nameUnidad, $nameFecha, $nameAlmacen, $nameSucursal, $nameMoneda, $status);

        //
        switch ($request->input('action')) {
                /* A query builder. */
            case 'Búsqueda':
                $reportes_collection_filtro = PROC_PURCHASE::join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CONF_CREDIT_CONDITIONS', 'PROC_PURCHASE.purchase_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
                    ->join('CAT_COMPANIES', 'PROC_PURCHASE.purchase_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('CAT_DEPOTS', 'PROC_PURCHASE.purchase_depot', '=', 'CAT_DEPOTS.depots_key')
                    ->join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
                    ->join('CAT_ARTICLES', 'PROC_PURCHASE_DETAILS.purchaseDetails_article', '=', 'CAT_ARTICLES.articles_key')
                    ->wherePurchaseProvider($nameProveedor)
                    ->whereProviderCategory($nameCategoria)
                    ->whereProviderGroup($nameGrupo)
                    ->wherePurchaseMovement($nameMov)
                    ->wherePurchaseArticle($nameArticulo)
                    ->wherePurchaseUnit($nameUnidad)
                    ->wherePurchaseDate($nameFecha)
                    ->wherePurchaseDepot($nameAlmacen)
                    ->wherePurchaseBranchOffice($nameSucursal)
                    ->wherePurchaseStatus($status)
                    ->wherePurchaseMoney($nameMoneda)
                    ->orderBy('PROC_PURCHASE.updated_at', 'DESC')
                    ->get();

                /* Convertimos el objeto a un array. */
                $reportes_filtro_array = $reportes_collection_filtro->toArray();

                // dd($reportes_filtro_array);
                $nameFecha = $request->nameFecha;

                /* Vamos a redirigir a la ruta de la vista con todos los datos de la vista para que no se borren al dar clic al botón */
                return redirect()->route('vista.reportes.compras-articulo-provedor')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('nameProveedor', $nameProveedor)
                    ->with('nameCategoria', $nameCategoria)
                    ->with('nameGrupo', $nameGrupo)
                    ->with('nameMov', $nameMov)
                    ->with('nameArticulo', $nameArticulo)
                    ->with('nameUnidad', $nameUnidad)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameAlmacen', $nameAlmacen)
                    ->with('nameSucursal', $nameSucursal)
                    ->with('nameMoneda', $nameMoneda)
                    ->with('status', $status)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal);
                break;

            case 'Exportar excel':

                /* Creamos una nueva instancia de la clase para pasarle los parametros y después descargar el excel. */
                $compra = new ReportesComprasArticuloProveedorExport($nameProveedor, $nameCategoria, $nameGrupo, $nameMov, $nameArticulo, $nameUnidad, $nameFecha, $nameAlmacen, $nameSucursal, $nameMoneda, $status, $fechaInicio, $fechaFinal);
                return Excel::download($compra, 'ReporteComprasArticuloProveedor.xlsx');

                break;

                /* Función para exportar a excel. */
            case 'Exportar PDF':
                /* Una consulta a la base de datos, que devuelve los datos que se utilizarán en el informe.. */
                $compra = PROC_PURCHASE::join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CONF_CREDIT_CONDITIONS', 'PROC_PURCHASE.purchase_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
                    ->join('CAT_COMPANIES', 'PROC_PURCHASE.purchase_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('CAT_DEPOTS', 'PROC_PURCHASE.purchase_depot', '=', 'CAT_DEPOTS.depots_key')
                    ->join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
                    ->join('CAT_ARTICLES', 'PROC_PURCHASE_DETAILS.purchaseDetails_article', '=', 'CAT_ARTICLES.articles_key')
                    ->join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_unit')
                    ->wherePurchaseProvider($nameProveedor)
                    ->whereProviderCategory($nameCategoria)
                    ->whereProviderGroup($nameGrupo)
                    ->wherePurchaseMovement($nameMov)
                    ->wherePurchaseArticle($nameArticulo)
                    ->wherePurchaseUnit($nameUnidad)
                    ->wherePurchaseDepot($nameAlmacen)
                    ->wherePurchaseBranchOffice($nameSucursal)
                    ->wherePurchaseDate($nameFecha)
                    ->wherePurchaseMoney($nameMoneda)
                    ->wherePurchaseStatus($status)
                    ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
                    ->where('PROC_PURCHASE.purchase_movement', '=', 'Entrada por Compra')
                    ->where('PROC_PURCHASE.purchase_status', '=', 'FINALIZADO')
                    ->orderBy('PROC_PURCHASE.updated_at', 'DESC')
                    ->get();


                /* Comprobando si la variable está vacía. Si está vacío, redirigirá a la ruta.
                'vista.reportes.compras-articulo-provedor' con un mensaje y un estado. */

                if ($compra->isEmpty()) {
                    return redirect()->route('vista.reportes.compras-articulo-provedor')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                } else {
                    /* Crearemos una colleción del array para luego utilizar el método unique() para obtener los
                    datos de purchase_provider y purchase_branchOffice keys. */
                    $collectionCompras = collect($compra);
                    $proveedoresCompra = $collectionCompras->unique('purchase_provider')->unique()->all();

                    $sucursal_almacen = $collectionCompras->unique('purchase_branchOffice')->unique()->first();

                    $sucursal = $sucursal_almacen->branchOffices_key . '-' . $sucursal_almacen->branchOffices_name;

                    $almacen = $sucursal_almacen->depots_key . '-' . $sucursal_almacen->depots_name;

                    $proveedorPorCompras = [];
                    foreach ($proveedoresCompra as $proveedor) {

                        $compras = PROC_PURCHASE::join(
                            'CAT_PROVIDERS',
                            'PROC_PURCHASE.purchase_provider',
                            '=',
                            'CAT_PROVIDERS.providers_key'
                        )
                            ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                            ->join('CONF_CREDIT_CONDITIONS', 'PROC_PURCHASE.purchase_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
                            ->join('CAT_COMPANIES', 'PROC_PURCHASE.purchase_company', '=', 'CAT_COMPANIES.companies_key')
                            ->join('CAT_DEPOTS', 'PROC_PURCHASE.purchase_depot', '=', 'CAT_DEPOTS.depots_key')
                            ->join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
                            ->join('CAT_ARTICLES', 'PROC_PURCHASE_DETAILS.purchaseDetails_article', '=', 'CAT_ARTICLES.articles_key')
                            ->join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_unit')
                            ->wherePurchaseProvider($proveedor->purchase_provider)
                            ->whereProviderCategory($nameCategoria)
                            ->whereProviderGroup($nameGrupo)
                            ->wherePurchaseMovement($nameMov)
                            ->wherePurchaseArticle($nameArticulo)
                            ->wherePurchaseUnit($nameUnidad)
                            ->wherePurchaseDepot($nameAlmacen)
                            ->wherePurchaseBranchOffice($nameSucursal)
                            ->wherePurchaseDate($nameFecha)
                            ->wherePurchaseMoney($nameMoneda)
                            ->wherePurchaseStatus($status)
                            ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
                            ->where('PROC_PURCHASE.purchase_movement', '=', 'Entrada por Compra')
                            ->where('PROC_PURCHASE.purchase_status', '=', 'FINALIZADO')
                            ->orderBy('PROC_PURCHASE.purchase_movementID', 'asc')
                            ->get();
                        /* Merging the array of the  with the array of the . */
                        $arrayProveedores = $proveedor->toArray();
                        $proveedorPorCompras[] = array_merge($arrayProveedores, ['compras' => $compras->toArray()]);
                    }

                    /*Obtenemos la imagen del storage y la convertimos a base64. */
                    if (session('company')->companies_logo === null || session('company')->companies_logo === '') {
                        $logoFile = null;
                    } else {
                        $logoFile = Storage::disk('empresas')->get(session('company')->companies_logo);
                    }

                    if ($logoFile == null) {
                        $logoFile = Storage::disk('empresas')->get('default.png');

                        if ($logoFile == null) {
                            $logoBase64 = '';
                        } else {
                            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoFile);
                        }
                    } else {
                        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoFile);
                    }


                    /* Convertir la fecha a un formato legible. */
                    switch ($nameFecha) {

                            // $date = $date->format('l jS \\of F Y h:i:s A');
                        case 'Hoy':


                            $nameFecha = Carbon::now()->isoFormat('LL');


                            break;
                        case 'Ayer':
                            $nameFecha = new Carbon('yesterday');
                            //poner el formato de la fecha
                            $nameFecha = $nameFecha->isoFormat('LL');
                            break;
                        case 'Semana':
                            $fecha_actual = Carbon::now()->isoFormat('LL');
                            $fecha_semana = new Carbon('last week');
                            $fecha_formato = $fecha_semana->isoFormat('LL');
                            $nameFecha = 'Del ' . $fecha_formato . ' al ' . $fecha_actual;
                            break;
                        case 'Mes':
                            $now = Carbon::now();
                            $start    = (new DateTime($now->format('Y-m-d')))->modify('first day of this month');
                            $end      = (new DateTime($now->format('Y-m-d')))->modify('last day of this month');

                            $fecha_inicial = $start->format('Y-m-d');
                            $fecha_fin = $end->format('Y-m-d');
                            $fechaInicio = Carbon::parse($fecha_inicial)->isoFormat('LL');
                            $fechaFinal = Carbon::parse($fecha_fin)->isoFormat('LL');
                            $nameFecha = 'Del ' . $fechaInicio . ' al ' . $fechaFinal;
                            break;
                        case 'Año Móvil':
                            $fecha_actual = Carbon::now()->isoFormat('LL');
                            $fecha_año_actual = Carbon::now()->format('Y');
                            $fecha_inicial = $fecha_año_actual . '-01-01';
                            $fecha_formato = Carbon::parse($fecha_inicial)->isoFormat('LL');
                            $nameFecha = 'Del ' . $fecha_formato . ' al ' . $fecha_actual;

                            break;
                        case 'Año Pasado':
                            $fecha_año_inicioMes_pasado = new Carbon('last year');
                            $formato_fecha_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                            $inicoAñoPasado = $formato_fecha_inicioMes_pasado . '-01-01';
                            $finAñoPasado = $formato_fecha_inicioMes_pasado . '-12-31';
                            $fecha_inicio_formato = Carbon::parse($inicoAñoPasado)->isoFormat('LL');
                            $fecha_fin_formato = Carbon::parse($finAñoPasado)->isoFormat('LL');
                            $nameFecha = 'Del ' . $fecha_inicio_formato . ' al ' . $fecha_fin_formato;
                            break;
                        default:
                            $fechaInicio = Carbon::parse($fechaInicio)->isoFormat('LL');
                            $fechaFinal = Carbon::parse($fechaFinal)->isoFormat('LL');
                            $nameFecha = $fechaInicio . ' - ' . $fechaFinal;

                            break;
                    }

                    $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->orderBy('generalParameters_id', 'desc')->get();




                    // dd($proveedorPorCompras[0]['compras'][0]);
                    // $articulos_compra = PROC_PURCHASE_DETAILS::WHERE('purchaseDetails_purchaseID', '=', $id)->get();

                    // dd($nameFecha);
                    /* Crear un archivo PDF y devolverlo al navegador. */
                    $pdf = PDF::loadView('page.Reportes.Compras.comprasArticuloProveedor-reporte', [
                        'logo' => $logoBase64,
                        'proveedorCompras' => $proveedorPorCompras,
                        'moneda' => $nameMoneda,
                        'fecha' => $nameFecha,
                        'movimiento' => $nameMov,
                        'unidad' => $nameUnidad,
                        'almacen' => $almacen,
                        'sucursal' => $sucursal,
                        'status' => $status,
                        'compra' => $compra,
                        'parametro' => $parametro,
                    ]);
                    $pdf->set_paper('a4', 'landscape');

                    return $pdf->stream();
                }
                break;
        }
    }


    /**
     * Devuelve una serie de sucursales de la empresa que está actualmente conectada
     * 
     * @return An array of branchOffices_key and branchOffices_name.
     */
    function SelectSucursales()
    {
        $sucursales = CAT_BRANCH_OFFICES::WHERE('branchOffices_status', '=', 'Alta')
            ->WHERE('branchOffices_companyId', '=', session('company')->companies_key)->get();
        $sucursales_array = array();
        $sucursales_array['Todos'] = 'Todos';
        foreach ($sucursales as $sucursal) {
            $sucursales_array[$sucursal->branchOffices_key] = $sucursal->branchOffices_name;
        }
        return $sucursales_array;
    }

    /**
     * Obtiene todas las monedas de la base de datos y las devuelve como un array
     * 
     * @return array:2 [▼
     *       "Todos" => "Todos"
     *       "MXN" => "Peso Mexicano"
     *      "USD" => "Dolar Americano"
     *     "EUR" => "Euro"
     *     ]
     */
    public function getMonedas()
    {
        $monedas = CONF_MONEY::where('money_status', '=', 'Alta')->get();
        $monedas_array = array();
        $monedas_array['Todos'] = 'Todos';
        foreach ($monedas as $moneda) {
            $monedas_array[trim($moneda->money_key)] = $moneda->money_name;
        }
        return $monedas_array;
    }

    /**
     * Devuelve una array de todas las unidades en la base de datos, siendo el primer elemento 'Todos' (todos).
     * 
     * @return an array of the units_unit column from the CONF_UNITS table.
     */
    function selectUnidades()
    {
        $unidades = CONF_UNITS::where('units_status', '=', 'Alta')->get();
        $unidades_array = array();
        foreach ($unidades as $unidades) {
            $unidades_array[$unidades->Todos] = 'Todos';
            $unidades_array[$unidades->units_unit] = $unidades->units_unit;
        }
        return $unidades_array;
    }

    /**
     * Devuelve un array de todos los depósitos (almacenes) de la empresa y la sucursal actuales.
     * 

     */
    function selectAlmacenes()
    {
        $almacen = CAT_DEPOTS::join('CAT_BRANCH_OFFICES', 'CAT_DEPOTS.depots_branchlId', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CAT_COMPANIES', 'CAT_BRANCH_OFFICES.branchOffices_companyId', '=', 'CAT_COMPANIES.companies_key')
            ->where('CAT_COMPANIES.companies_key', '=', session('company')->companies_key)
            ->where('CAT_BRANCH_OFFICES.branchOffices_key', '=', session('sucursal')->branchOffices_key)
            ->where('CAT_DEPOTS.depots_status', '=', 'Alta')
            ->select('CAT_DEPOTS.*')->get();
        $almacenes_array = array();
        $almacenes_array['Todos'] = 'Todos';
        foreach ($almacen as $almacenes) {
            $almacenes_array[$almacenes->depots_key] = $almacenes->depots_key . ' - ' . $almacenes->depots_name;
        }
        return $almacenes_array;
    }

    /**
     * Esta función devuelve un array de todos los artículos en la base de datos, siendo el primer elemento 'Todos' (todos).
     * El resto de los elementos son el articles_key y el articles_descript.
     * 
     * @return un array de todos los artículos en la base de datos.
     */
    function selectArticulos()
    {
        $articulos = CAT_ARTICLES::where('articles_status', '=', 'Alta')->orderBy('articles_id', 'asc')->get();
        $articulos_array = array();
        foreach ($articulos as $articulos) {
            $articulos_array[$articulos->Todos] = 'Todos';
            $articulos_array[$articulos->articles_key] = $articulos->articles_key . ' - ' . $articulos->articles_descript;
        }
        return $articulos_array;
    }

    /**
     * Esta función devuelve un array de todos los proveedores en la base de datos, siendo el primer elemento 'Todos' (todos).
     * seguido de los proveedores_key y el providers_name.
     * 
     */
    function selectProveedores()
    {
        $proveedores = CAT_PROVIDERS::where('providers_status', '=', 'Alta')->get();
        $proveedores_array = array();
        foreach ($proveedores as $proveedores) {
            $proveedores_array[$proveedores->Todos] = 'Todos';
            $proveedores_array[$proveedores->providers_key] = $proveedores->providers_key . ' - ' . $proveedores->providers_name;
        }
        return $proveedores_array;
    }

    /** 
     * Esta función devuelve un array de todas las categorías de proveedores en la base de datos, siendo el primer elemento 'Todos' (todos).
     * seguido de los categoryProvider_name.
     */

    function selectCategoria()
    {
        $categorias = CAT_PROVIDER_CATEGORY::where('categoryProvider_status', '=', 'Alta')->get();
        $categorias_array = array();
        foreach ($categorias as $categorias) {
            $categorias_array[$categorias->Todos] = 'Todos';
            $categorias_array[$categorias->categoryProvider_name] = $categorias->categoryProvider_name;
        }
        return $categorias_array;
    }

    /**
     * Esta función devuelve un array de todos los grupos de proveedores en la base de datos, siendo el primer elemento 'Todos' (todos).
     * seguido de los groupProvider_name.
     * 
     */

    function selectGrupo()
    {
        $grupos = CAT_PROVIDER_GROUP::where('groupProvider_status', '=', 'Alta')->get();
        $grupos_array = array();
        foreach ($grupos as $grupos) {
            $grupos_array[$grupos->Todos] = 'Todos';
            $grupos_array[$grupos->groupProvider_name] = $grupos->groupProvider_name;
        }
        return $grupos_array;
    }
}
