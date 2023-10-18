<?php

namespace App\Http\Controllers\erpNet;

use App\Http\Controllers\Controller;
use App\Http\Controllers\erpNet\Timbrado\TimbradoController;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_COMPANIES;
use App\Models\modulos\PROC_CANCELED_CFDI;
use App\Models\modulos\PROC_PURCHASE;
use App\Models\modulos\PROC_SALES;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Obtenemos las ordenes de compra pendientes hechas por la herramienta.
        $ordenesCompra = PROC_PURCHASE::where('purchase_company', '=', session("company")->companies_key)
            ->where('purchase_branchOffice', '=', session("sucursal")->branchOffices_key)
            ->where('purchase_concept', '=', "OC HERRAMIENTA")
            ->where('purchase_status', '=', 'POR AUTORIZAR')
            ->get()->count();
        // dd($ordenesCompra);
        // Crear una instancia del controlador TimbradoController
        // dd($consultaResults);


        $movimientosCancelados = PROC_CANCELED_CFDI::WHERE('canceledCfdi_company', '=', session('company')->companies_key)->WHERE('canceledCfdi_branchOffice', '=', session('sucursal')->branchOffices_key)->get();
        $select_sucursales = $this->selectSucursales();
        $usuario = Auth::user();


        return view('page.dashboard', compact('ordenesCompra', 'movimientosCancelados', 'select_sucursales', 'usuario'));
    }


    public function getTop10SalesDetails(Request $request)
    {
        $branchOffice = $request->input('sucursal');
        // dd($branchOffice);

        //si es rango de fecha se obtienen las fechas del input, sino la fecha lo obtenemos de la funcion getDatesRange
        if ($request->input('fecha') == 'Rango Fechas') {
            $startDate = $request->input('fechaInicio');
            $endDate = $request->input('fechaFinal');
        } else {
            [$startDate, $endDate] = $this->getDatesRange($request->input('fecha'));
        }
        //si el input de fefcha es rango de fechas, se obtienen las fechas del input

        $company = session('company')->companies_key;

        // dd($startDate, $endDate, $branchOffice);

        $query = "EXEC GetTop10SalesDetails @startDate = ?, @endDate = ?, @branchOffice = ?, @company = ?";
        $parameters = [$startDate, $endDate, $branchOffice, $company];

        $result = DB::select(DB::raw($query), $parameters);
        // dd($result);

        // Devuelve los resultados como respuesta JSON
        return response()->json($result);
    }

    public function getSalesByFamily(Request $request)
    {
        $branchOffice = $request->input('sucursal');
        if ($request->input('fecha') == 'Rango Fechas') {
            $startDate = $request->input('fechaInicio');
            $endDate = $request->input('fechaFinal');
        } else {
            [$startDate, $endDate] = $this->getDatesRange($request->input('fecha'));
        }

        $query = "EXEC GetSalesByFamily @startDate = ?, @endDate = ?, @branchOffice = ?";
        $parameters = [$startDate, $endDate, $branchOffice];

        $result = DB::select(DB::raw($query), $parameters);
        // dd($result);

        // Devuelve los resultados como respuesta JSON
        return response()->json($result);
        // dd($result);
    }

    public function getEarningAndExpenses(Request $request)
    {
        $company = session('company')->companies_key;
        $branchOffice = $request->input('sucursal');
        if ($request->input('fecha') == 'Rango Fechas') {
            $startDate = $request->input('fechaInicio');
            $endDate = $request->input('fechaFinal');
        } else {
            [$startDate, $endDate] = $this->getDatesRange($request->input('fecha'));
        }

        $query = "EXEC GetEarningAndExpenses @company = ?, @branchOffice = ?, @startDate = ?, @endDate = ?";
        $parameters = [$company, $branchOffice, $startDate, $endDate];

        $result = DB::select(DB::raw($query), $parameters);
        // dd($result);

        // Devuelve los resultados como respuesta JSON
        return response()->json($result);
        // dd($result);
    }

    public function getCurrentMonthVSLastMonth(Request $request)
    {
        $branchOffice = $request->input('sucursal');
        $company = session('company')->companies_key;
        $currentStartDate = date('Y-m-d', strtotime('first day of this month'));
        $currentEndDate = date('Y-m-d', strtotime('last day of this month'));
        $lastStartDate = date('Y-m-d', strtotime('first day of last month'));
        $lastEndDate = date('Y-m-d', strtotime('last day of last month'));

        $query = "EXEC GetCurrentSaleVSPreviousSale @currentStartDate = ?, @currentEndDate = ?, @previousStartDate = ?, @previousEndDate = ?, @branchOffice = ?, @company = ?";
        $parameters = [$currentStartDate, $currentEndDate, $lastStartDate, $lastEndDate, $branchOffice, $company];

        $result = DB::select(DB::raw($query), $parameters);
        // dd($result);

        // Devuelve los resultados como respuesta JSON
        return response()->json($result);
        // dd($result);
    }


    public function getSalesVSProfit(Request $request)
    {
        $company = session('company')->companies_key;
        $branchOffice = $request->input('sucursal');
        if ($request->input('fecha') == 'Rango Fechas') {
            $startDate = $request->input('fechaInicio');
            $endDate = $request->input('fechaFinal');
        } else {
            [$startDate, $endDate] = $this->getDatesRange($request->input('fecha'));
        }
        // dd($startDate, $endDate, $branchOffice);

        $query = "EXEC CalculateSalesSummary @company = ?, @branchOffice = ?, @startDate = ?, @endDate = ?";
        $parameters = [$company, $branchOffice, $startDate, $endDate];

        $result = DB::select(DB::raw($query), $parameters);
        // dd($result);

        // Devuelve los resultados como respuesta JSON
        return response()->json($result);
        // dd($result);
    }

    public function getSalesAndFlows(Request $request)
    {
        $branchOffice = $request->input('sucursal');
        // Si es rango de fecha se obtienen las fechas del input, sino la fecha se obtiene de la función getDatesRange
        if ($request->input('fecha') == 'Rango Fechas') {
            $startDate = $request->input('fechaInicio');
            $endDate = $request->input('fechaFinal');
        } else {
            [$startDate, $endDate] = $this->getDatesRange($request->input('fecha'));
        }
        // Si el input de fecha es rango de fechas, se obtienen las fechas del input

        $company = session('company')->companies_key;

        // Crear una tabla temporal local
        $tempTable = '##tempResults_' . uniqid();
        $createTempTableQuery = "CREATE TABLE $tempTable (
            branchOfficeKey VARCHAR(50),
            branchOfficeName VARCHAR(50),
            totalSales DECIMAL(18, 2),
            totalFlow DECIMAL(18, 2)
        )";

        // Ejecutar el procedimiento almacenado insertando los resultados en la tabla temporal
        $execProcedureQuery = "INSERT INTO $tempTable
            EXEC GetSalesAndFlows @branchOffice = ?, @company = ?, @startDate = ?, @endDate = ?";
        $parameters = [$branchOffice, $company, $startDate, $endDate];

        // Ejecutar las consultas
        DB::statement($createTempTableQuery);
        DB::statement($execProcedureQuery, $parameters);

        // Obtener los resultados de la tabla temporal
        $results = DB::select("SELECT * FROM $tempTable");

        // Eliminar la tabla temporal
        $dropTempTableQuery = "DROP TABLE $tempTable";
        DB::statement($dropTempTableQuery);

        // Devolver los resultados como respuesta JSON
        return response()->json($results);
    }


    private function getDatesRange($filter)
    {
        switch ($filter) {
            case 'Hoy':
                $startDate = date('Y-m-d');
                $endDate = date('Y-m-d');
                break;
            case 'Ayer':
                $startDate = date('Y-m-d', strtotime('-1 day'));
                $endDate = date('Y-m-d', strtotime('-1 day'));
                break;
            case 'Semana':
                //para semana se agarra el día de hoy y se le pone hasta el domingo de la semana
                $now = Carbon::now();
                $startDate = (new DateTime($now->format('Y-m-d')))->modify('this week');
                $endDate = (new DateTime($now->format('Y-m-d')))->modify('this week +6 days');

                $startDate = $startDate->format('Y-m-d');
                $endDate = $endDate->format('Y-m-d');
                break;
            case 'Mes':
                $now = Carbon::now();
                $startDate = (new DateTime($now->format('Y-m-d')))->modify('first day of this month');
                $endDate = (new DateTime($now->format('Y-m-d')))->modify('last day of this month');

                $startDate = $startDate->format('Y-m-d');
                $endDate = $endDate->format('Y-m-d');
                break;
            case 'Mes Anterior':
                $now = Carbon::now();
                $startDate = $now->subMonth()->startOfMonth()->format('Y-m-d');
                $endDate = $now->endOfMonth()->format('Y-m-d');
                break;
            default:
                $startDate = null;
                $endDate = null;
                break;
        }

        return [$startDate, $endDate];
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
}
