<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesVentasProductoMasVendidoExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_ARTICLES_CATEGORY;
use App\Models\agrupadores\CAT_ARTICLES_FAMILY;
use App\Models\agrupadores\CAT_ARTICLES_GROUP;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_COMPANIES;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\modulos\PROC_SALES;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use stdClass;

class ReportesVentasProductoMasVendidoController extends Controller
{
    public function index()
    {

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }
        $select_sucursales = $this->selectSucursales();
        $selectMonedas = $this->getMonedas();
        $articulos = $this->selectArticulos();
        $select_categoria = $this->selectCategoria();
        $select_grupo = $this->selectGrupo();
        $select_familia = $this->selectFamilia();

        $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
            ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
            ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
            ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->where('PROC_SALES.sales_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->where('PROC_SALES.sales_movement', '=', 'Factura')
            ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
            ->orderBy('PROC_SALES.updated_at', 'DESC')
            ->paginate(25);
        return view('page.Reportes.Ventas.indexReporteVentasProductoMasVendido', compact('select_sucursales', 'selectMonedas', 'articulos', 'select_categoria', 'select_grupo', 'select_familia', 'ventas'));
    }

    public function reportesVentasProductoMasVendidoAction(Request $request)
    {
        $listaPrecio = $request->listaPrecio;
        $nameArticulo = $request->nameArticulo;
        $categoria = $request->categoria;
        $grupo = $request->grupo;
        $familia = $request->familia;
        $nameFecha = $request->nameFecha;
        $nameSucursal = $request->nameSucursal;

        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;

        //esto nos sirve para el rango de fechas, para validar que no estén vacíos
        if (
            $fechaInicio !== null && $fechaFinal !== null && $nameFecha === "Rango Fechas"
        ) {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }

        switch ($request->input('action')) {
            case 'Búsqueda':

                $reportes_collection_filtro = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                    ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                    ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                    ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
                    ->where('PROC_SALES.sales_movement', '=', 'Factura')
                    ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                    ->whereSalesListPrice($listaPrecio)
                    ->whereSalesArticle($nameArticulo)
                    ->whereArticleCategory($categoria)
                    ->whereArticleGroup($grupo)
                    ->whereArticleFamily($familia)
                    ->whereSalesDate($nameFecha)
                    ->whereSalesBranchOffice($nameSucursal)
                    ->get();

                $reportes_filtro_array = $reportes_collection_filtro->toArray();

                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.reportes.ventas-producto-mas-vendido')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('listaPrecio', $listaPrecio)
                    ->with('nameArticulo', $nameArticulo)
                    ->with('categoria', $categoria)
                    ->with('grupo', $grupo)
                    ->with('familia', $familia)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameSucursal', $nameSucursal)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal);

                break;

            case 'Exportar excel':

                $venta = new ReportesVentasProductoMasVendidoExport($listaPrecio, $nameArticulo, $categoria, $grupo, $familia, $nameFecha, $nameSucursal, $fechaInicio, $fechaFinal);
                return Excel::download($venta, 'Productos más vendidos.xlsx');

                break;

            case 'Exportar PDF':

                $venta = PROC_SALES::join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                    ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                    ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
                    ->where('PROC_SALES.sales_movement', '=', 'Factura')
                    ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                    ->whereSalesArticle($nameArticulo)
                    ->whereSalesDate($nameFecha)
                    ->whereSalesBranchOffice($nameSucursal)
                    ->get();

                if ($venta->isEmpty()) {
                    return redirect()->route('vista.reportes.ventas-producto-mas-vendido')->with('message', 'No se pudo generar el reporte, no hay datos para mostrar.')->with('status', false);
                } else {
                    $collectionVentas = collect($venta);
                    $listaPreciosVenta = $collectionVentas->unique('sales_listPrice')->unique()->all();

                    $listasPorVentas = [];
                    foreach ($listaPreciosVenta as $lista) {
                        $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                            ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                            ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                            ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
                            ->where('PROC_SALES.sales_movement', '=', 'Factura')
                            ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                            ->whereSalesListPrice($lista->sales_listPrice)
                            ->whereSalesArticle($nameArticulo)
                            ->whereArticleCategory($categoria)
                            ->whereArticleGroup($grupo)
                            ->whereArticleFamily($familia)
                            ->whereSalesDate($nameFecha)
                            ->whereSalesBranchOffice($nameSucursal)
                            //ordenamos de mayor a menor

                            ->get();

                        $arrayListaPrecios = $lista->toArray();

                        $listasPorVentas[] = array_merge($arrayListaPrecios, ['ventas' => $ventas->toArray()]);
                    }
                    $detalles = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                        ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                        ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                        ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                        ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
                        ->whereSalesListPrice($listaPrecio)
                        ->whereSalesArticle($nameArticulo)
                        ->whereArticleCategory($categoria)
                        ->whereArticleGroup($grupo)
                        ->whereArticleFamily($familia)
                        ->whereSalesDate($nameFecha)
                        ->whereSalesBranchOffice($nameSucursal)
                        ->get();

                    $detalles_array = [];
                    foreach ($detalles as $key => $detalle) {
                        $detalles_array[] = $detalle;
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

                    switch ($nameFecha) {

                            // $date = $date->format('l jS \\of F Y h:i:s A');
                        case 'Hoy':


                            $nameFecha = Carbon::now()->isoFormat('LL');
                            // ->format('l jS \\of F Y');

                            break;
                        case 'Ayer':
                            $nameFecha = new Carbon('yesterday');
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


                    $pdf = PDF::loadView('page.Reportes.Ventas.ventasProductoMasVendido-reporte', [
                        'logo' => $logoBase64,
                        'listasVentas' => $listasPorVentas,
                        'fecha' => $nameFecha,
                        'sucursal' => $nameSucursal,
                        'articulo' => $nameArticulo,
                        'listaPrecios' => $listaPrecio,
                        'venta' => $venta,
                        'detalles_array' => $detalles_array,
                    ]);
                    return $pdf->stream();
                }
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

    public function selectCategoria()
    {
        $categorias = CAT_ARTICLES_CATEGORY::where('categoryArticle_status', 'Alta')->get();
        $categorias_array = array();
        foreach ($categorias as $categorias) {
            $categorias_array[$categorias->Todos] = 'Todos';
            $categorias_array[$categorias->categoryArticle_name] = $categorias->categoryArticle_name;
        }
        return $categorias_array;
    }

    public function selectGrupo()
    {
        $grupos = CAT_ARTICLES_GROUP::where('groupArticle_status', 'Alta')->get();
        $grupos_array = array();
        foreach ($grupos as $grupos) {
            $grupos_array[$grupos->Todos] = 'Todos';
            $grupos_array[$grupos->groupArticle_name] = $grupos->groupArticle_name;
        }
        return $grupos_array;
    }

    public function selectFamilia()
    {
        $familias = CAT_ARTICLES_FAMILY::where('familyArticle_status', 'Alta')->get();
        $familias_array = array();
        foreach ($familias as $familias) {
            $familias_array[$familias->Todos] = 'Todos';
            $familias_array[$familias->familyArticle_name] = $familias->familyArticle_name;
        }
        return $familias_array;
    }
}
