<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesComprasUnidadExport;

use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_DEPOTS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\catalogos\CONF_UNITS;
use App\Models\modulos\PROC_PURCHASE;
use App\Models\modulos\PROC_PURCHASE_DETAILS;
use Carbon\Carbon;
use DateTime;
// use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Node\Stmt\Switch_;
use PDF;

class ReportesComprasUnidadController extends Controller
{
    /**
     * Función del index de la vista de reportes de compras por unidad
     * Nos sirve para mostrar la vista de reportes de compras por unidad
     */
    public function index()
    {
        /** 
         * Se obtienen los datos de las sucursales, monedas, unidades, almacenes, parámetros generales y compras
         */
        $select_sucursales = $this->selectSucursales();
        $selectMonedas = $this->getMonedas();
        $unidad = $this->selectUnidades();
        $almacen = $this->selectAlmacenes();
        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->orderBy('generalParameters_id', 'desc')->get();

        /** 
         * Se obtienen los datos de las compras
         * Se hace un join con las tablas de proveedores, sucursales, condiciones de crédito, compañías, almacenes y detalles de compras
         * Se filtra por la compañía, sucursal, moneda, movimiento, estatus y se ordena por la fecha de actualización
         * Se obtienen todos los registros
         *
         */
        $compras = PROC_PURCHASE::join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
            ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CONF_CREDIT_CONDITIONS', 'PROC_PURCHASE.purchase_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
            ->join('CAT_COMPANIES', 'PROC_PURCHASE.purchase_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('CAT_DEPOTS', 'PROC_PURCHASE.purchase_depot', '=', 'CAT_DEPOTS.depots_key')
            ->join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
            ->join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_unit')
            ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
            ->where('PROC_PURCHASE.purchase_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->where('PROC_PURCHASE.purchase_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->where('PROC_PURCHASE.purchase_movement', '=', 'Entrada por Compra')
            ->where('PROC_PURCHASE.purchase_status', '=', 'FINALIZADO')
            ->orderBy('PROC_PURCHASE.updated_at', 'DESC')
            ->get();




        /* Se retorna la vista de reportes de compras por unidad con los datos de las sucursales, monedas, unidades, almacenes, parámetros generales y compras */
        return view('page.Reportes.Compras.indexReporteComprasUnidad', compact('select_sucursales', 'selectMonedas', 'parametro', 'unidad', 'almacen', 'compras'));
    }

    /**
     * Esta función nos sirve para los botones de exportar a excel y PDF así como para el filtro de la vista de reportes de compras por unidad
     */
    public function reportesAction(Request $request)
    {
        /* Se obtienen los datos de las sucursales, monedas, unidades, almacenes, parámetros generales y compras
        * Obtener los valores del formulario y almacenarlos en variables. 
        */
        $nameMov = $request->nameMov;
        $nameUnidad = $request->nameUnidad;
        $nameAlmacen = $request->nameAlmacen;
        $nameSucursal = $request->nameSucursal;
        $nameFecha = $request->nameFecha;
        $nameMoneda = $request->nameMoneda;
        $status = $request->status;

        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;

        /**
         * Esta condicional nos sirve para concatenar las fechas de inicio y final en una sola variable
         * Si las fechas de inicio y final no son nulas y el nombre de la fecha es igual a "Rango Fechas" entonces se concatenan las fechas de inicio y final
         */
        if ($fechaInicio !== null && $fechaFinal !== null && $nameFecha === "Rango Fechas") {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }

        switch ($request->input('action')) {
            case 'Búsqueda':
                /**
                 * Se obtienen los datos de las compras
                 * Se hace un join con las tablas de proveedores, sucursales, condiciones de crédito, compañías, almacenes y detalles de compras
                 * Se filtra por la compañía, sucursal, moneda, movimiento, estatus y se ordena por la fecha de actualización
                 * Se obtienen todos los registros
                 */
                $reportes_collection_filtro = PROC_PURCHASE::join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CONF_CREDIT_CONDITIONS', 'PROC_PURCHASE.purchase_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
                    ->join('CAT_COMPANIES', 'PROC_PURCHASE.purchase_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('CAT_DEPOTS', 'PROC_PURCHASE.purchase_depot', '=', 'CAT_DEPOTS.depots_key')
                    ->join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
                    ->join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_unit')
                    ->wherePurchaseMovement($nameMov)
                    ->wherePurchaseUnit($nameUnidad)
                    ->wherePurchaseDepot($nameAlmacen)
                    ->wherePurchaseBranchOffice($nameSucursal)
                    ->wherePurchaseDate($nameFecha)
                    ->wherePurchaseMoney($nameMoneda)
                    ->wherePurchaseStatus($status)
                    ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
                    ->orderBy('PROC_PURCHASE.updated_at', 'DESC')
                    ->where('PROC_PURCHASE.purchase_status', '=', 'FINALIZADO')
                    ->get();

                /**
                 * Se convierte la colección de compras a un array
                 */
                $reportes_filtro_array = $reportes_collection_filtro->toArray();

                /**
                 * El $nameFecha se obtiene del formulario por si entro a la condicional de "Rango Fechas" y se concatenaron las fechas de inicio y final
                 */
                $nameFecha = $request->nameFecha;

                /** 
                 * Se retorna la vista de reportes de compras por unidad con los datos de las sucursales, monedas, unidades, almacenes, parámetros generales y compras
                 */
                return redirect()->route('vista.reportes.compras-unidad')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('nameMov', $nameMov)
                    ->with('nameUnidad', $nameUnidad)
                    ->with('nameAlmacen', $nameAlmacen)
                    ->with('nameSucursal', $nameSucursal)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameMoneda', $nameMoneda)
                    ->with('status', $status)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal);

                break;

                /**
                 * Ahora tenemos la opción de exportar a excel
                 * Se crea una instancia de la clase ReportesComprasUnidadExport con los parámetros de búsqueda
                 * Se retorna la descarga del archivo excel
                 */
            case 'Exportar excel':
                $compra = new ReportesComprasUnidadExport($nameMov, $nameUnidad, $nameAlmacen, $nameSucursal, $nameFecha, $nameMoneda, $status);
                return Excel::download($compra, 'Reporte de Compras por Unidad.xlsx');

                break;

            case 'Exportar PDF':
                /**
                 * Se obtienen los datos de las compras
                 */
                $compra = PROC_PURCHASE::join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CONF_CREDIT_CONDITIONS', 'PROC_PURCHASE.purchase_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
                    ->join('CAT_COMPANIES', 'PROC_PURCHASE.purchase_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('CAT_DEPOTS', 'PROC_PURCHASE.purchase_depot', '=', 'CAT_DEPOTS.depots_key')
                    ->join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
                    ->join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_unit')
                    ->wherePurchaseMovement($nameMov)
                    ->wherePurchaseUnit($nameUnidad)
                    ->wherePurchaseDepot($nameAlmacen)
                    ->wherePurchaseBranchOffice($nameSucursal)
                    ->wherePurchaseDate($nameFecha)
                    ->wherePurchaseMoney($nameMoneda)
                    ->wherePurchaseStatus($status)
                    ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
                    ->where('PROC_PURCHASE.purchase_status', '=', 'FINALIZADO')
                    ->first();

                if ($compra === null) {
                    return redirect()->route('vista.reportes.compras-unidad')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                } else {
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

                    $compras = PROC_PURCHASE::join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
                        ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                        ->join('CONF_CREDIT_CONDITIONS', 'PROC_PURCHASE.purchase_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
                        ->join('CAT_COMPANIES', 'PROC_PURCHASE.purchase_company', '=', 'CAT_COMPANIES.companies_key')
                        ->join('CAT_DEPOTS', 'PROC_PURCHASE.purchase_depot', '=', 'CAT_DEPOTS.depots_key')
                        ->join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
                        ->join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_unit')
                        ->wherePurchaseMovement($nameMov)
                        ->wherePurchaseUnit($nameUnidad)
                        ->wherePurchaseDepot($nameAlmacen)
                        ->wherePurchaseDate($nameFecha)
                        ->wherePurchaseMoney($nameMoneda)
                        ->wherePurchaseStatus($status)
                        ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
                        ->orderBy('PROC_PURCHASE.updated_at', 'DESC')
                        ->where('PROC_PURCHASE.purchase_status', '=', 'FINALIZADO')
                        ->get();



                    // dd($compras);

                    // $articulos_compra = PROC_PURCHASE_DETAILS::WHERE('purchaseDetails_purchaseID', '=', $id)->get();

                    if ($compras->isEmpty()) {
                        return back()->with('message', 'No se encontraron registros');
                    } else {

                        /**
                         * Hacemos un switch para cambiar el nombre de la fecha a un formato mas legible
                         * Teniendo en cuenta los diferentes tipos de fechas que se pueden generar
                         * Como son: Hoy, Ayer, Semana, Mes, Año. 
                         * Para esto utilizamos la libreria Carbon, la cual nos permite trabajar con fechas de una manera mas sencilla
                             
                         */
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
                                $nameFecha = $fechaInicio . ' + ' . $fechaFinal;

                                break;
                        }

                        /**
                         * Una vez que tenemos armado la consulta a la base de datos y haber transformado la fecha a un formato mas legible
                         * Procedemos a generar el PDF
                         */
                        $pdf = PDF::loadView('page.Reportes.Compras.compras-reporte', ['logo' => $logoBase64, 'compra' => $compra, 'compras' => $compras, 'status' => $status, 'nameFecha' => $nameFecha, 'nameMov' => $nameMov, 'nameUnidad' => $nameUnidad, 'nameAlmacen' => $nameAlmacen, 'nameSucursal' => $nameSucursal, 'nameMoneda' => $nameMoneda]);
                        $pdf->set_paper('a4', 'landscape');
                        return $pdf->stream();
                    }
                }


                break;
        }
    }

    /**
     * Funcion para obtener las sucursales por empresa
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
     * Funcion para obtener las monedas que se encuentran activas
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
     * Funcion para obtener las unidades que se encuentran activas
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
     * Funcion para obtener los almacenes que se encuentran activos por sucursal
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
}
