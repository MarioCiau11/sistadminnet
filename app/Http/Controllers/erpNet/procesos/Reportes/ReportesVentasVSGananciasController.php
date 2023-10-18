<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesVentasVSGananciasExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_ARTICLES_CATEGORY;
use App\Models\agrupadores\CAT_ARTICLES_FAMILY;
use App\Models\agrupadores\CAT_ARTICLES_GROUP;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\modulos\PROC_SALES;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportesVentasVSGananciasController extends Controller
{
    public function index()
    {

        $select_sucursales = $this->selectSucursales();

        $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
            ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
            ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
            ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
            // ->where('PROC_SALES.sales_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->where('PROC_SALES.sales_movement', '=', 'Factura')
            ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
            ->orderBy('PROC_SALES.updated_at', 'DESC')
            ->paginate(25);
        return view('page.Reportes.Ventas.indexReporteVentasVSGanancia', compact('select_sucursales', 'ventas'));
    }

    public function reportesVentasGananciaAction(Request $request)
    {
        $nameSucursal = $request->nameSucursal;
        $nameFecha = $request->nameFecha;
        $nameAgrupador = $request->nameAgrupador;

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
                    ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
                    ->where('PROC_SALES.sales_movement', '=', 'Factura')
                    ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                    ->whereSalesDate($nameFecha)
                    ->whereSalesBranchOffice($nameSucursal)
                    ->orderBy('PROC_SALES.updated_at', 'DESC')
                    ->get();

                $reportes_filtro_array = $reportes_collection_filtro->toArray();
                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.reportes.ventas-ganancia')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('nameSucursal', $nameSucursal)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameAgrupador', $nameAgrupador)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal);
                break;

            case 'Exportar excel':
                $venta = new ReportesVentasVSGananciasExport($nameSucursal, $nameFecha, $nameAgrupador);
                return Excel::download($venta, 'ReporteVentasGanancia.xlsx');
                break;

            case 'Exportar PDF':
                $venta = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                    ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                    ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                    ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
                    ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
                    ->where('PROC_SALES.sales_movement', '=', 'Factura')
                    ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                    ->whereSalesDate($nameFecha)
                    ->whereSalesBranchOffice($nameSucursal)
                    ->orderBy('PROC_SALES.updated_at', 'DESC')
                    ->get();

                if ($venta->isEmpty()) {
                    return redirect()->route('vista.reportes.ventas-ganancia')->with('message', 'No se pudo generar el reporte, no hay datos para mostrar.')->with('status', false);
                } else {
                    $collectionVentas = collect($venta);
                    $sucursalesVentas = $collectionVentas->unique('sales_branchOffice')->unique()->all();

                    $sucursalesPorVentas = [];
                    foreach ($sucursalesVentas as $sucursal) {
                        if ($nameAgrupador === 'Categoría') {
                            $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                                ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                                ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                                ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                                ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
                                ->where('PROC_SALES.sales_movement', '=', 'Factura')
                                ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                                ->whereSalesBranchOffice($sucursal->sales_branchOffice)
                                ->whereSalesDate($nameFecha)
                                ->get();
                        } elseif ($nameAgrupador === 'Grupo') {
                            $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                                ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                                ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                                ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                                ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
                                ->where('PROC_SALES.sales_movement', '=', 'Factura')
                                ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                                ->whereSalesBranchOffice($sucursal->sales_branchOffice)
                                ->whereSalesDate($nameFecha)
                                ->get();
                        } elseif ($nameAgrupador === 'Familia') {
                            $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                                ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                                ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                                ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                                ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
                                ->where('PROC_SALES.sales_movement', '=', 'Factura')
                                ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                                ->whereSalesBranchOffice($sucursal->sales_branchOffice)
                                ->whereSalesDate($nameFecha)
                                ->get();
                        }


                        $arraySucursalesVentas = $sucursal->toArray();
                        $sucursalesPorVentas[] = array_merge($arraySucursalesVentas, ['ventas' => $ventas->toArray()]);
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


                    $pdf = PDF::loadView('page.Reportes.Ventas.ventasVSGanancia-reporte', [
                        'logo' => $logoBase64,
                        'sucursalesVentas' => $sucursalesPorVentas,
                        'fecha' => $nameFecha,
                        'agrupador' => $nameAgrupador,

                    ]);
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


    public function selectCategoria()
    {
        $categorias = CAT_ARTICLES_CATEGORY::where('categoryArticle_status', 'Alta')->get();
        $categorias_array = array();
        foreach ($categorias as $categorias) {
            $categorias_array[$categorias->categoryArticle_name] = $categorias->categoryArticle_name;
        }
        return $categorias_array;
    }

    public function selectGrupo()
    {
        $grupos = CAT_ARTICLES_GROUP::where('groupArticle_status', 'Alta')->get();
        $grupos_array = array();
        foreach ($grupos as $grupos) {
            $grupos_array[$grupos->groupArticle_name] = $grupos->groupArticle_name;
        }
        return $grupos_array;
    }

    public function selectFamilia()
    {
        $familias = CAT_ARTICLES_FAMILY::where('familyArticle_status', 'Alta')->get();
        $familias_array = array();
        foreach ($familias as $familias) {
            $familias_array[$familias->familyArticle_name] = $familias->familyArticle_name;
        }
        return $familias_array;
    }
}
