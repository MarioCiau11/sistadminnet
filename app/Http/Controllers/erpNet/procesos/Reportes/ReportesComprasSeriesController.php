<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesComprasSeriesExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_PROVIDERS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\modulos\PROC_LOT_SERIES_MOV;
use App\Models\modulos\PROC_PURCHASE;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportesComprasSeriesController extends Controller
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
        $proveedores = $this->selectProveedores();
        $series = $this->selectSeries();

        $compras = PROC_PURCHASE::join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
            ->join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
            ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('PROC_LOT_SERIES_MOV', 'PROC_PURCHASE_DETAILS.purchaseDetails_id', 'PROC_LOT_SERIES_MOV.LOTSeriesMov_articleID')
            ->where('purchase_status', '=', 'FINALIZADO')->where('purchase_movement', '=', 'Entrada por Compra')
            ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
            ->where('PROC_PURCHASE.purchase_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->where('PROC_PURCHASE.purchase_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->orderBy('PROC_PURCHASE.updated_at', 'DESC')
            ->paginate(25)
            ->unique('lotSeriesMov_lotSerie');

        // dd($compras);
        //  dd($ventas);

        return view('page.Reportes.Compras.indexReporteComprasSerie', compact('select_sucursales', 'selectMonedas', 'compras', 'parametro', 'articulos', 'proveedores', 'series'));
    }

    public function reportesArtAction(Request $request)
    {
        $nameProveedor = $request->nameProveedor;
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

                $reportes_collection_filtro = PROC_PURCHASE::join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
                    ->join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('PROC_LOT_SERIES_MOV', 'PROC_PURCHASE_DETAILS.purchaseDetails_id', 'PROC_LOT_SERIES_MOV.LOTSeriesMov_articleID')
                    ->where('purchase_status', '=', 'FINALIZADO')->where('purchase_movement', '=', 'Entrada por Compra')
                    ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
                    ->wherePurchaseProvider($nameProveedor)
                    ->wherePurchaseArticle($nameArticulo)
                    ->wherePurchaseDate($nameFecha)
                    ->wherePurchaseBranchOffice($nameSucursal)
                    ->wherePurchaseMoney($nameMoneda)
                    ->wherePurchaseSeries($series)
                    ->orderBy('PROC_PURCHASE.updated_at', 'DESC')
                    ->get()
                    ->unique('lotSeriesMov_lotSerie');

                // dd($reportes_collection_filtro);
                $reportes_filtro_array = $reportes_collection_filtro->toArray();

                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.reportes.compras-con-series')->with('reportes_filtro_array', $reportes_filtro_array)->with('nameFecha', $nameFecha)->with('nameSucursal', $nameSucursal)->with('nameMoneda', $nameMoneda)->with('nameProveedor', $nameProveedor)->with('nameArticulo', $nameArticulo)->with('series', $series);

                break;

            case 'Exportar excel':

                $compra = new ReportesComprasSeriesExport($nameProveedor, $nameArticulo, $nameFecha, $nameSucursal, $nameMoneda, $series);

                return Excel::download($compra, 'ReporteComprasSeries.xlsx');



                break;

                /* Función para exportar a excel. */
            case 'Exportar PDF':

                $compra =  PROC_PURCHASE::join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
                    ->join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('PROC_LOT_SERIES_MOV', 'PROC_PURCHASE_DETAILS.purchaseDetails_id', 'PROC_LOT_SERIES_MOV.LOTSeriesMov_articleID')
                    ->where('purchase_status', '=', 'FINALIZADO')->where('purchase_movement', '=', 'Entrada por Compra')
                    ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
                    ->wherePurchaseProvider($nameProveedor)
                    ->wherePurchaseArticle($nameArticulo)
                    ->wherePurchaseDate($nameFecha)
                    ->wherePurchaseBranchOffice($nameSucursal)
                    ->wherePurchaseMoney($nameMoneda)
                    ->wherePurchaseSeries($series)
                    ->orderBy('PROC_PURCHASE.purchase_movement', 'asc')
                    ->get()
                    ->unique('lotSeriesMov_lotSerie');


                if ($compra->isEmpty()) {
                    return redirect()->route('vista.reportes.compras-con-series')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                } else {
                    //llamamos a la función para declarar el logo
                    $logoBase64 = $this->declararLogo();
                    $pdf = PDF::loadView(
                        'page.Reportes.Compras.comprasSeries-reporte',
                        ['compras' => $compra, 'nameFecha' => $nameFecha, 'nameSucursal' => $nameSucursal, 'nameMoneda' => $nameMoneda, 'nameProveedor' => $nameProveedor, 'nameArticulo' => $nameArticulo, 'series' => $series,      'logo' => $logoBase64,]
                    );


                    return $pdf->stream();
                }

                break;
        }
    }

    function declararLogo()
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
        $articulos = CAT_ARTICLES::where('articles_status', '=', 'Alta')->orderBy('articles_id', 'ASC')->get();

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
        // dd($proveedores);
        $proveedores_array = array();
        foreach ($proveedores as $proveedores) {
            $proveedores_array[$proveedores->Todos] = 'Todos';
            $proveedores_array[$proveedores->providers_key] = $proveedores->providers_key . ' - ' . $proveedores->providers_name;
        }
        return $proveedores_array;
    }

    function selectSeries()
    {
        $series = PROC_LOT_SERIES_MOV::get()->unique('lotSeriesMov_lotSerie');

        //  dd($series);
        $series_array = array();
        foreach ($series as $series) {
            $series_array[$series->Todos] = 'Todos';
            $series_array[$series->lotSeriesMov_lotSerie] = $series->lotSeriesMov_lotSerie;
        }
        return $series_array;
    }
}
