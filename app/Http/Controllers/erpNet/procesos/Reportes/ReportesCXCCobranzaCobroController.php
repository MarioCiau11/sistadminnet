<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesCXCFormaCobroExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE;
use App\Models\modulos\PROC_SALES;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;

class ReportesCXCCobranzaCobroController extends Controller
{
    public function index()
    {

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }
        $select_sucursales = $this->selectSucursales();
        $selectMonedas = $this->getMonedas();
        $now = Carbon::now();
        $start    = (new DateTime($now->format('Y-m-d')))->modify('first day of this month');
        $end      = (new DateTime($now->format('Y-m-d')))->modify('last day of this month');

        $fecha_inicial = $start->format('Y-m-d');
        $fecha_fin = $end->format('Y-m-d');


        $ventas_contado = PROC_SALES::join('PROC_SALES_PAYMENT', 'PROC_SALES.sales_id', '=', 'PROC_SALES_PAYMENT.salesPayment_saleID')->join('CAT_BRANCH_OFFICES', 'PROC_SALES.sales_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->where('sales_company', '=', session('company')->companies_key)->where('sales_branchOffice', '=', session('sucursal')->branchOffices_key)->where('sales_status', '=', 'FINALIZADO')->where('sales_movement', '=', 'Factura')->where('sales_typeCondition', '=', 'Contado')->where('sales_issuedate', '>=', $fecha_inicial)->where('sales_issuedate', '<=', $fecha_fin)->where('PROC_SALES.sales_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->get();

        $cobros_credido = PROC_ACCOUNTS_RECEIVABLE::join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->where('accountsReceivable_company', '=', session('company')->companies_key)->where('accountsReceivable_branchOffice', '=', session('sucursal')->branchOffices_key)->where('accountsReceivable_status', '=', 'FINALIZADO')->where('accountsReceivable_movement', '=', 'Cobro de Facturas')->where('accountsReceivable_issuedate', '>=', $fecha_inicial)->where('accountsReceivable_issuedate', '<=', $fecha_fin)->where('accountsReceivable_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->get();


        $movimientos = [];

        foreach ($ventas_contado as $venta) {
            $movimientos[] = $venta;
        }

        foreach ($cobros_credido as $cobro) {
            $movimientos[] = $cobro;
        }

        // dd($movimientos);


        return view('page.Reportes.CuentasXCobrar.indexReporteCxCFormaCobro', compact('select_sucursales', 'selectMonedas', 'parametro', 'movimientos'));
    }

    public function reportesCXCCobranzaCobroAction(Request $request)
    {
        // dd($request->all());

        $nameFecha = $request->nameFecha;
        $nameSucursal = $request->nameSucursal;
        $nameMoneda = $request->nameMoneda;

        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;

        if (
            $fechaInicio !== null && $fechaFinal !== null && $nameFecha === "Rango Fechas"
        ) {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }

        switch ($request->input('action')) {
                /* A query builder. */
            case 'Búsqueda':


                $ventas_contado = PROC_SALES::join('PROC_SALES_PAYMENT', 'PROC_SALES.sales_id', '=', 'PROC_SALES_PAYMENT.salesPayment_saleID')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_SALES.sales_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->where('sales_company', '=', session('company')->companies_key)
                    ->where('sales_status', '=', 'FINALIZADO')->where('sales_movement', '=', 'Factura')->where('sales_typeCondition', '=', 'Contado')
                    ->whereSalesDate($nameFecha)
                    ->whereSalesBranchOffice($nameSucursal)
                    ->whereSalesMoney($nameMoneda)->get();

                $cobros_credido = PROC_ACCOUNTS_RECEIVABLE::join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->where('accountsReceivable_company', '=', session('company')->companies_key)->where('accountsReceivable_status', '=', 'FINALIZADO')->where('accountsReceivable_movement', '=', 'Cobro de Facturas')
                    ->whereAccountsReceivableDate($nameFecha)
                    ->whereAccountsReceivablebranchOffice($nameSucursal)
                    ->whereAccountsReceivableMoney($nameMoneda)->get();

                $movimientos = [];

                foreach ($ventas_contado as $venta) {
                    $movimientos[] = $venta;
                }

                foreach ($cobros_credido as $cobro) {
                    $movimientos[] = $cobro;
                }

                $reportes_filtro_array = $movimientos;

                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.reportes.cxc-cobranza-forma-cobro')->with('reportes_filtro_array', $reportes_filtro_array)->with('nameFecha', $nameFecha)->with('nameSucursal', $nameSucursal)->with('nameMoneda', $nameMoneda)->with('fechaInicio', $fechaInicio)->with(
                    'fechaFinal',
                    $fechaFinal
                );




                break;

            case 'Exportar excel':

                $reporte = new ReportesCXCFormaCobroExport($nameFecha, $nameSucursal, $nameMoneda);

                return Excel::download($reporte, 'ReporteCuentasXCobrarFormaCobro.xlsx');



                break;

                /* Función para exportar a excel. */
            case 'Exportar PDF':

                $ventas_contado = PROC_SALES::join('PROC_SALES_PAYMENT', 'PROC_SALES.sales_id', '=', 'PROC_SALES_PAYMENT.salesPayment_saleID')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_SALES.sales_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CONF_FORMS_OF_PAYMENT', 'PROC_SALES_PAYMENT.salesPayment_paymentMethod1', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key')
                    ->where('sales_company', '=', session('company')->companies_key)
                    ->where('sales_status', '=', 'FINALIZADO')->where('sales_movement', '=', 'Factura')->where('sales_typeCondition', '=', 'Contado')
                    ->whereSalesDate($nameFecha)
                    ->whereSalesBranchOffice($nameSucursal)
                    ->whereSalesMoney($nameMoneda)->get();

                $cobros_credido = PROC_ACCOUNTS_RECEIVABLE::join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CONF_FORMS_OF_PAYMENT', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_formPayment', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key')
                    ->where('accountsReceivable_company', '=', session('company')->companies_key)->where('accountsReceivable_status', '=', 'FINALIZADO')->where('accountsReceivable_movement', '=', 'Cobro de Facturas')
                    ->whereAccountsReceivableDate($nameFecha)
                    ->whereAccountsReceivablebranchOffice($nameSucursal)
                    ->whereAccountsReceivableMoney($nameMoneda)->get();


                $movimientos = [];

                foreach ($ventas_contado as $venta) {
                    $movimientos[] = $venta;
                }

                foreach ($cobros_credido as $cobro) {
                    $movimientos[] = $cobro;
                }

                if ($movimientos == null) {
                    return redirect()->route('vista.reportes.cxc-cobranza-forma-cobro')->with('message', 'No se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
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

                    $pdf = PDF::loadView('page.Reportes.CuentasXCobrar.formaCobro-reporte', ['movimientos' => $movimientos, 'logo' => $logoBase64, 'ventas_contado' => $ventas_contado, 'cobros_credido' => $cobros_credido]);
                    $pdf->setPaper('a4', 'landscape');

                    return $pdf->stream();
                }

                break;
        }
    }

    function SelectSucursales()
    {
        $sucursales = CAT_BRANCH_OFFICES::WHERE('branchOffices_status', '=', 'Alta')->WHERE('branchOffices_companyId', '=', session('company')->companies_key)->get();
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
}
