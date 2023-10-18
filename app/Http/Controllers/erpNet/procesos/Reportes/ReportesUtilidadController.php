<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesUtilidadExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\modulos\PROC_EXPENSES;
use App\Models\modulos\PROC_SALES;
use App\Models\modulos\PROC_SALES_DETAILS;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use stdClass;

class ReportesUtilidadController extends Controller
{
    public function index()
    {
        $clientes = $this->selectClientes();
        $utilidades = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
            ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
            ->where('PROC_SALES.sales_movement', '=', 'FACTURA')
            ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
            ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
            ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->orderBy('PROC_SALES.created_at', 'desc')
            ->paginate(25);
        return view('page.Reportes.Gerenciales.indexReporteUtilidad', compact('clientes', 'utilidades'));
    }

    public function reportesUtilidadAction(Request $request)
    {

        $nameCliente = $request->nameCliente;
        $nameFecha = $request->nameFecha;

        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;

        // dd($nameCliente, $nameFecha, $fechaInicio, $fechaFinal);
        if ($fechaInicio !== null && $fechaFinal !== null && $nameFecha === "Rango Fechas") {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }



        switch ($request->input('action')) {
            case 'Búsqueda':
                $reportes_collection_filtro = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
                    ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                    ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
                    ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
                    ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                    ->where('PROC_SALES.sales_movement', '=', 'FACTURA')
                    ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                    ->whereSalesCustomer($nameCliente)
                    ->whereSalesDate($nameFecha)
                    ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
                    ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
                    ->orderBy('PROC_SALES.created_at', 'desc')
                    ->get()
                    ->unique('sales_id');
                // dd($nameFecha);


                $reportes_filtro_array = $reportes_collection_filtro->toArray();
                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.reportes.utilidad-ventas-vs-gastos')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('nameCliente', $nameCliente)
                    ->with('nameFecha', $nameFecha)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal);
                break;

            case 'Exportar excel':
                $utilidad = new ReportesUtilidadExport($nameCliente, $nameFecha);

                return Excel::download($utilidad, 'ReporteUtilidad.xlsx');
                break;

            case 'Exportar PDF':

                $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
                    ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                    ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
                    ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
                    ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                    ->join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.salesDetails_unit')
                    ->where('PROC_SALES.sales_movement', '=', 'FACTURA')
                    ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                    ->whereSalesCustomer($nameCliente)
                    ->whereSalesDate($nameFecha)
                    ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
                    ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
                    ->orderBy('PROC_SALES.created_at', 'asc')
                    ->get();



                if ($ventas->isEmpty()) {
                    return redirect()->route('vista.reportes.utilidad-ventas-vs-gastos')->with('message', 'No se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                } else {

                    $venta = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
                        ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                        ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
                        ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
                        ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_SALES.sales_company')
                        ->where('PROC_SALES.sales_movement', '=', 'FACTURA')
                        ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                        ->whereSalesCustomer($nameCliente)
                        ->whereSalesDate($nameFecha)
                        ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
                        ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
                        ->orderBy('PROC_SALES.created_at', 'asc')
                        ->get();
                    // dd($nameFecha);

                    //detalle de ventas
                    $detalleVentas = PROC_SALES_DETAILS::join('PROC_SALES', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                        ->join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
                        ->where('PROC_SALES.sales_movement', '=', 'FACTURA')
                        ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                        ->whereSalesCustomer($nameCliente)
                        ->whereSalesDate($nameFecha)
                        ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
                        ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
                        ->orderBy('PROC_SALES.created_at', 'desc')->get();

                    $gastos = PROC_EXPENSES::join('PROC_EXPENSES_DETAILS', 'PROC_EXPENSES.expenses_id', '=', 'PROC_EXPENSES_DETAILS.expensesDetails_expenseID')
                        ->join('PROC_SALES', 'PROC_SALES.sales_movementID', '=', 'PROC_EXPENSES.expenses_antecedentsName')
                        ->where('PROC_EXPENSES.expenses_status', '=', 'FINALIZADO')
                        ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
                        ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
                        ->get()->unique('expensesDetails_id');


                    //  dd($gastos, $venta);


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

                    // dd($venta, $detalleVentas, $gastos);
                    $pdf = PDF::loadView('page.Reportes.Gerenciales.utilidad-reporte', [
                        'ventas' => $ventas,
                        'detalleVentas' => $detalleVentas,
                        'gastos' => $gastos,
                        'nameFecha' => $nameFecha,
                        'logo' => $logoBase64,
                        'nameCliente' => $nameCliente,
                        'nameFecha' => $nameFecha,
                        'venta' => $venta,

                    ]);

                    $pdf->set_paper('a4', 'landscape');
                    return $pdf->stream('reporte-utilidad.pdf');
                }
                break;
        }
    }

    function selectClientes()
    {
        $clientes = CAT_CUSTOMERS::where('customers_status', '=', 'Alta')->get();
        $clientes_array = array();
        foreach ($clientes as $clientes) {
            $clientes_array[$clientes->Todos] = 'Todos';
            $clientes_array[$clientes->customers_key] = $clientes->customers_key . ' - ' . $clientes->customers_businessName;
        }
        return $clientes_array;
    }
}
