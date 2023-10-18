<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesVentasSeriesExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\modulos\PROC_DEL_SERIES_MOV2;
use App\Models\modulos\PROC_SALES;
use App\Models\modulos\PROC_SALES_DETAILS;
use Carbon\Carbon;
use DateTime;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;

class ReportesVentasSeriesController extends Controller
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
        $clientes = $this->selectClientes();
        $series = $this->selectSeries();


        $ventas = PROC_SALES::join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
            ->join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
            ->join('CAT_BRANCH_OFFICES', 'PROC_SALES.sales_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('PROC_DEL_SERIES_MOV2', 'PROC_SALES_DETAILS.salesDetails_id', 'PROC_DEL_SERIES_MOV2.delSeriesMov2_articleID')
            ->where('sales_status', '=', 'FINALIZADO')->where('sales_movement', '=', 'Factura')
            ->where('PROC_SALES_DETAILS.salesDetails_type', '=', 'Serie')
            ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
            ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->where('PROC_SALES.sales_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->orderBy('PROC_SALES.updated_at', 'DESC')
            ->paginate(25);

        $kits = PROC_SALES::join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
        ->join('PROC_KIT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_id', '=', 'PROC_KIT_ARTICLES.procKit_articleIDReference')
        ->join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
        ->join('CAT_BRANCH_OFFICES', 'PROC_SALES.sales_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
        ->where('salesDetails_type', '=', 'Kit')
        ->where('procKit_tipo', '=', 'Serie')
        ->where('sales_status', '=', 'FINALIZADO')
            ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
            ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->where('PROC_SALES.sales_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->orderBy('PROC_SALES.updated_at', 'DESC')
            ->paginate(25);

        //  dd($ventas);

        return view('page.Reportes.Ventas.indexReporteVentasSeries', compact('select_sucursales', 'selectMonedas', 'ventas', 'parametro', 'articulos', 'clientes', 'series', 'kits'));
    }

    public function reportesArtAction(Request $request)
    {
        $nameCliente = $request->nameCliente;
        $nameArticulo = $request->nameArticulo;
        $nameFecha = $request->nameFecha;
        $nameSucursal = $request->nameSucursal;
        $nameMoneda = $request->nameMoneda;
        $series = $request->series;

        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;

        //  dd($request->all());

        //esto nos sirve para el rango de fechas, para validar que no estén vacíos
        if (
            $fechaInicio !== null && $fechaFinal !== null && $nameFecha === "Rango Fechas"
        ) {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }


        switch ($request->input('action')) {
                /* A query builder. */
            case 'Búsqueda':

                $reportes_collection_filtro = PROC_SALES::join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                    ->join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_SALES.sales_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('PROC_DEL_SERIES_MOV2', 'PROC_SALES_DETAILS.salesDetails_id', 'PROC_DEL_SERIES_MOV2.delSeriesMov2_articleID')->where('sales_status', '=', 'FINALIZADO')->where('sales_movement', '=', 'Factura')
                    ->where('PROC_SALES_DETAILS.salesDetails_type', '=', 'Serie')
                    ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
                    ->whereSalesCustomer($nameCliente)
                    ->whereSalesArticle($nameArticulo)
                    ->whereSalesDate($nameFecha)
                    ->whereSalesBranchOffice($nameSucursal)
                    ->whereSalesMoney($nameMoneda)
                    ->whereSalesSeries($series)
                    ->orderBy('PROC_SALES.updated_at', 'DESC')
                    ->get();

                $reportes_filtro_array = $reportes_collection_filtro->toArray();

                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.reportes.ventas-serie')->with('reportes_filtro_array', $reportes_filtro_array)->with('nameFecha', $nameFecha)->with('nameSucursal', $nameSucursal)->with('nameMoneda', $nameMoneda)->with('nameCliente', $nameCliente)->with('nameArticulo', $nameArticulo)->with('series', $series)->with('fechaInicio', $fechaInicio)->with('fechaFinal', $fechaFinal);

                break;

            case 'Exportar excel':
                $venta = new ReportesVentasSeriesExport($nameCliente, $nameArticulo, $nameFecha, $nameSucursal, $nameMoneda, $series);
                return Excel::download($venta, 'ReporteVentasSeries.xlsx');
                break;

                /* Función para exportar a excel. */
            case 'Exportar PDF':

                $venta = PROC_SALES::join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                ->join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                ->join('CAT_BRANCH_OFFICES', 'PROC_SALES.sales_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                ->join('PROC_DEL_SERIES_MOV2', 'PROC_SALES_DETAILS.salesDetails_id', 'PROC_DEL_SERIES_MOV2.delSeriesMov2_articleID')
                ->where('sales_status', '=', 'FINALIZADO')
                ->where('sales_movement', '=', 'Factura')
                ->where('PROC_SALES_DETAILS.salesDetails_type', '=', 'Serie')
                ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
                ->where('delSeriesMov2_cancelled', '=', 0)
                ->whereSalesCustomer($nameCliente)
                ->whereSalesArticle($nameArticulo)
                ->whereSalesDate($nameFecha)
                ->whereSalesBranchOffice($nameSucursal)
                ->whereSalesMoney($nameMoneda)
                ->whereSalesSeries($series)
                ->orderBy('PROC_SALES.updated_at', 'DESC')
                ->select('PROC_SALES.sales_id', 'PROC_SALES.sales_movement', 'PROC_SALES.sales_movementID', 'PROC_SALES.sales_customer', 'PROC_SALES.sales_reference', 'PROC_SALES.sales_issuedate', 'CAT_CUSTOMERS.customers_key', 'CAT_CUSTOMERS.customers_businessName', 'CAT_BRANCH_OFFICES.branchOffices_key','CAT_BRANCH_OFFICES.branchOffices_key', 'PROC_SALES_DETAILS.salesDetails_article', 'PROC_SALES_DETAILS.salesDetails_descript', 'PROC_SALES_DETAILS.salesDetails_unit', 'PROC_DEL_SERIES_MOV2.delSeriesMov2_lotSerie')
                ->get();

                $kit = PROC_SALES::join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                ->join('PROC_KIT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_id', '=', 'PROC_KIT_ARTICLES.procKit_articleIDReference')
                ->join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                ->join('CAT_BRANCH_OFFICES', 'PROC_SALES.sales_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                ->where('salesDetails_type', '=', 'Kit')
                ->where('procKit_tipo', '=', 'Serie')
                ->where('sales_status', '=', 'FINALIZADO')
                    ->where('sales_movement', '=', 'Factura')
                    ->whereSalesCustomer($nameCliente)
                    ->whereSalesArticle($nameArticulo)
                    ->whereSalesDate($nameFecha)
                    ->whereSalesBranchOffice($nameSucursal)
                    ->whereSalesMoney($nameMoneda)
                    ->get()->unique('procKit_id');
                    // dd($kit);




                    foreach ($kit as $componente) {
                        if ($componente->procKit_tipo == "Serie") {
                            $seriesComponentes = PROC_SALES::join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                                ->join('PROC_DEL_SERIES_MOV2', 'PROC_SALES_DETAILS.salesDetails_saleID', 'PROC_DEL_SERIES_MOV2.delSeriesMov2_saleID')
                                ->join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                                ->join('CAT_BRANCH_OFFICES', 'PROC_SALES.sales_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                                ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_DEL_SERIES_MOV2.delSeriesMov2_article')
                            ->whereSalesBranchOffice($nameSucursal)->where('delSeriesMov2_cancelled', '=', 0)->WHERE('delSeriesMov2_article', '=', $componente->procKit_articleID)->WHERE('delSeriesMov2_articleID', '=', $componente->procKit_article)->WHERE('delSeriesMov2_saleID', '=', $componente->salesDetails_saleID)->WHERE('delSeriesMov2_module', '=', 'Ventas')

                            //sales_movement, sales_movementID, salesDetails_article, salesDetails_descript, sales_issuedate, sales_reference, customers_key, customers_businessName, salesDetails_unit, delSeriesMov2_lotSerie
                            ->select('PROC_SALES.sales_id', 'PROC_SALES.sales_movement', 'PROC_SALES.sales_movementID', 'CAT_ARTICLES.articles_key', 'CAT_ARTICLES.articles_descript', 'PROC_SALES.sales_issuedate', 'PROC_SALES.sales_reference', 'CAT_CUSTOMERS.customers_key', 'CAT_CUSTOMERS.customers_businessName', 'PROC_SALES_DETAILS.salesDetails_unit', 'PROC_DEL_SERIES_MOV2.delSeriesMov2_lotSerie')
                        ->get()->unique('sales_id');
                            // dd($seriesComponentes);


                            if (count($seriesComponentes) > 0) {
                                foreach ($seriesComponentes as $seriesC) {
                                    //no queremos pasarlo array así que solo lo dejamos tal cual para que forme el formato de tabla
                                    $seriesArticulosVendidos[] = $seriesC->toArray();
                                }
                            } else {
                                //si no hay series, entonces no pasamos nada, pero como necesitamos la variable, la declaramos vacía
                                $seriesArticulosVendidos = [];
                            }
                        }
                    }
                    // dd($seriesComponentes);
                    //NO QUEREMOS QUE ESTE EN ARRAY POR LO QUE LO CONVERTIMOS A OBJETO

                    // dd($seriesArticulosVendidos);
                    //tanto $ventas como $seriesArticulosVendidos son objetos, por lo que vamos a convertirlos a array
                    $venta = $venta->toArray();
                    // $seriesArticulosVendidos = $seriesArticulosVendidos->toArray();
                    // dd($venta, $seriesArticulosVendidos);
                    
                    //si los dos arrays están vacíos, entonces no hay nada que mostrar por lo que mandamos un mensaje de error
                    if (empty($venta) && empty($seriesArticulosVendidos)) {
                        return redirect()->route('vista.reportes.ventas-serie')->with('message', 'No se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                    }
           
                    $nameFecha = $this->obtenerFecha($nameFecha, $fechaInicio, $fechaFinal);
                    $logoBase64 = $this->obtenerLogoEmpresa();
                    $pdf = PDF::loadView(
                        'page.Reportes.Ventas.ventasSeries-reporte',
                        ['ventas' => $venta, 'nameFecha' => $nameFecha, 'nameSucursal' => $nameSucursal, 'nameMoneda' => $nameMoneda, 'nameCliente' => $nameCliente, 'nameArticulo' => $nameArticulo, 'series' => $series,      'logo' => $logoBase64, 'kits' => $seriesArticulosVendidos]
                    );
                    return $pdf->stream();
                
                break;
        }
    }
    private function obtenerLogoEmpresa()
    {
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

        return $logoBase64;
    }

    //ahora hacemos una función para obtener la fecha 
    private function obtenerFecha($nameFecha, $fechaInicio, $fechaFinal)
    {
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
        return $nameFecha;
    }
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
        $articulos = CAT_ARTICLES::where('articles_status', '=', 'Alta')->where('articles_type', '=', 'Serie')->orderBy('articles_id', 'asc')->get();
        // dd($articulos);
        $articulos_array = array();
        foreach ($articulos as $articulos) {
            $articulos_array[$articulos->Todos] = 'Todos';
            $articulos_array[$articulos->articles_descript] = $articulos->articles_key . ' - ' . $articulos->articles_descript;
        }
        return $articulos_array;
    }

    /**
     * Esta función devuelve un array de todos los proveedores en la base de datos, siendo el primer elemento 'Todos' (todos).
     * seguido de los proveedores_key y el providers_name.
     * 
     */
    function selectClientes()
    {
        $clientes = CAT_CUSTOMERS::where('customers_status', '=', 'Alta')->get();
        // dd($clientes);
        $clientes_array = array();
        foreach ($clientes as $clientes) {
            $clientes_array[$clientes->Todos] = 'Todos';
            $clientes_array[$clientes->customers_key] = $clientes->customers_key . ' - ' . $clientes->customers_businessName;
        }
        return $clientes_array;
    }

    function selectSeries()
    {
        $series = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_affected', '=', 1)
        ->where('delSeriesMov2_cancelled', '=', 0)
        ->get()->unique('delSeriesMov2_lotSerie');

        //  dd($series);
        $series_array = array();
        foreach ($series as $series) {
            $series_array[$series->Todos] = 'Todos';
            $series_array[$series->delSeriesMov2_lotSerie] = $series->delSeriesMov2_lotSerie;
        }
        return $series_array;
    }
}
