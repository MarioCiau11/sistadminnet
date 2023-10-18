<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesCXCEstadoCuentaExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_CUSTOMERS_CATEGORY;
use App\Models\agrupadores\CAT_CUSTOMERS_GROUP;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE;
use App\Models\modulos\PROC_ASSISTANT;
use App\Models\modulos\PROC_TREASURY;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportesCXCEstadoCuentaController extends Controller
{
    public function index()
    {

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }
        $clientes = $this->selectClientes();
        $categorias = $this->selectCategoria();
        $grupos = $this->selectGrupo();
        $selectMonedas = $this->getMonedas();

        $cuentasxcobrar = [];
        $movimientosCxC = PROC_ASSISTANT::join("CAT_CUSTOMERS", "CAT_CUSTOMERS.customers_key", "=", "PROC_ASSISTANT.assistant_account")
            ->join("PROC_ACCOUNTS_RECEIVABLE", "PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_id", "=", "PROC_ASSISTANT.assistant_moduleID")
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_ASSISTANT.assistant_branchKey')
            ->whereIn("PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_status", ["POR AUTORIZAR", "FINALIZADO"])
            ->where("assistant_branch", "=", "CxC")
            ->where("assistant_canceled", "=", 0)
            ->where('PROC_ASSISTANT.assistant_companieKey', '=', session('company')->companies_key)
            ->where('assistant_money', '=', $parametro->generalParameters_defaultMoney)
            ->whereAssistantCreated("Mes")
            ->orderBy('PROC_ACCOUNTS_RECEIVABLE.updated_at', 'ASC')
            ->get()->toArray();

        foreach ($movimientosCxC as $mov) {
            if ($mov['assistant_movement'] === "Solicitud Depósito") {
                $cuentasxcobrar[] = $mov;
                //Busacamos su origen 
                $cxc = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', 'Cobro de Facturas')->WHERE('accountsReceivable_movementID', '=', $mov['assistant_movementID'])->WHERE('accountsReceivable_company', '=', $mov['assistant_companieKey'])->WHERE('accountsReceivable_branchOffice', '=', $mov['assistant_branchKey'])->first();
                // dd($cxc);

                //BUSCAMOS EL DEPOSITO
                //Si $cxc es null es porque no se ha generado el cobro de facturas así que no se ha generado el deposito
                if ($cxc !== null) {
                    $solicitudDeposito = PROC_TREASURY::WHERE('treasuries_origin', '=', $cxc->accountsReceivable_movement)->WHERE('treasuries_originID', '=', $cxc->accountsReceivable_movementID)->WHERE('treasuries_company', '=', $mov['assistant_companieKey'])->WHERE('treasuries_branchOffice', '=', $mov['assistant_branchKey'])->first();

                    $solicitudDeposito = PROC_TREASURY::WHERE('treasuries_origin', '=', $cxc->accountsReceivable_movement)->WHERE('treasuries_originID', '=', $cxc->accountsReceivable_movementID)->WHERE('treasuries_company', '=', $mov['assistant_companieKey'])->WHERE('treasuries_branchOffice', '=', $mov['assistant_branchKey'])->first();


                    $deposito = PROC_TREASURY::where("treasuries_company", "=", $mov['accountsReceivable_company'])->where('treasuries_branchOffice', "=", $mov['accountsReceivable_branchOffice'])->where("treasuries_origin", "=",  $solicitudDeposito->treasuries_movement)->where("treasuries_originID", "=", $solicitudDeposito->treasuries_movementID)->where("treasuries_originType", "=", "Din")->where("treasuries_status", "=", "FINALIZADO")->get()->toArray();

                    // dd($deposito);

                    if (count($deposito) > 0) {
                        //BUSCAMOS EL AUXILIAR DEL DEPOSITO
                        $assistant = PROC_ASSISTANT::join("CAT_CUSTOMERS", "CAT_CUSTOMERS.customers_key", "=", "PROC_ASSISTANT.assistant_account")
                        ->where("assistant_branch", "=", "CxC")->where("assistant_movement", "=", $deposito[0]["treasuries_movement"])->where("assistant_moduleID", "=", $deposito[0]["treasuries_id"])->where("assistant_canceled", "=", 0)->first();
                        $cuentasxcobrar[] = $assistant;
                    }
                }
            } else if ($mov['accountsReceivable_movement'] === $mov['assistant_movement']) {
                $cuentasxcobrar[] = $mov;
            }
        }



        return view('page.Reportes.CuentasXCobrar.indexCXCEstadoCuenta', compact('clientes', 'selectMonedas', 'parametro', 'cuentasxcobrar', 'categorias', 'grupos'));
    }

    public function reportesCXCEstadoCuentaAction(Request $request)
    {
        $nameCliente = $request->nameCliente;
        $cliente = $request->cliente;
        $nameCategoria = $request->nameCategoria;
        $nameGrupo = $request->nameGrupo;
        $nameFecha = $request->nameFecha;
        $nameMoneda = $request->nameMoneda;

        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;


        if ($fechaInicio !== null && $fechaFinal !== null && $nameFecha === "Rango Fechas") {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }


        switch ($request->input('action')) {
            case 'Búsqueda':
                $reportes_collection_filtro = PROC_ASSISTANT::join("CAT_CUSTOMERS", "CAT_CUSTOMERS.customers_key", "=", "PROC_ASSISTANT.assistant_account")
                    ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_ASSISTANT.assistant_branchKey')
                    ->where("assistant_branch", "=", "CxC")
                    ->where('PROC_ASSISTANT.assistant_companieKey', '=', session('company')->companies_key)
                    ->where("assistant_canceled", "=", "0")
                    ->whereAssistantAccount($nameCliente)
                    ->whereCustomerCategory($nameCategoria)
                    ->whereCustomerGroup($nameGrupo)
                    ->whereAssistantCreated($nameFecha)
                    ->whereAssistantMoney($nameMoneda)
                    ->orderBy('PROC_ASSISTANT.updated_at', 'DESC')
                    ->get();

                $reportes_filtro_array = $reportes_collection_filtro->toArray();
                $nameFecha = $request->nameFecha;


                return redirect()->route('vista.reportes.cxc-estado-cuenta')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('nameCliente', $nameCliente)
                    ->with('cliente', $cliente)
                    ->with('nameCategoria', $nameCategoria)
                    ->with('nameGrupo', $nameGrupo)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameMoneda', $nameMoneda)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal);


                break;

            case 'Exportar excel':

                try {
                    $cxc = new ReportesCXCEstadoCuentaExport($nameCliente, $cliente, $nameCategoria, $nameGrupo, $nameFecha, $nameMoneda);
                    return Excel::download($cxc, 'Reporte de Estado de Cuenta.xlsx');
                } catch (\Throwable $th) {
                    dd($th);
                    return redirect()->route('vista.reportes.cxc-estado-cuenta')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                }

                break;
            case 'Exportar PDF':

                $cxc = PROC_ASSISTANT::join("CAT_CUSTOMERS", "CAT_CUSTOMERS.customers_key", "=", "PROC_ASSISTANT.assistant_account")
                    ->join("PROC_ACCOUNTS_RECEIVABLE", "PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_id", "=", "PROC_ASSISTANT.assistant_moduleID")
                    ->whereIn("PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_status", ["POR AUTORIZAR", "FINALIZADO"])
                    ->where("assistant_canceled", "=", 0)
                    ->where("assistant_branch", "=", "CxC")
                    ->where('PROC_ASSISTANT.assistant_companieKey', '=', session('company')->companies_key)
                    ->whereAssistantAccount($nameCliente)
                    ->whereCustomerCategory($nameCategoria)
                    ->whereCustomerGroup($nameGrupo)
                    ->whereAssistantCreated($nameFecha)
                    ->whereAssistantMoney($nameMoneda)
                    ->orderBy('PROC_ASSISTANT.updated_at', 'ASC')
                    ->first();


                if ($cxc === null) {
                    return redirect()->route('vista.reportes.cxc-estado-cuenta')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                } else {
                    $assistant = [];
                    $movimientosCxC = PROC_ASSISTANT::join("CAT_CUSTOMERS", "CAT_CUSTOMERS.customers_key", "=", "PROC_ASSISTANT.assistant_account")
                        ->join("PROC_ACCOUNTS_RECEIVABLE", "PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_id", "=", "PROC_ASSISTANT.assistant_moduleID")
                        ->whereIn("PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_status", ["POR AUTORIZAR", "FINALIZADO"])
                        ->where("assistant_canceled", "=", 0)
                        ->where("assistant_branch", "=", "CxC")
                        ->where('PROC_ASSISTANT.assistant_companieKey', '=', session('company')->companies_key)
                        ->whereAssistantAccount($nameCliente)
                        ->whereCustomerCategory($nameCategoria)
                        ->whereCustomerGroup($nameGrupo)
                        ->whereAssistantCreated($nameFecha)
                        ->whereAssistantMoney($nameMoneda)
                        ->orderBy('PROC_ASSISTANT.updated_at', 'ASC')
                        ->get()
                        ->toArray();

                    foreach ($movimientosCxC as $mov) {
                        if ($mov['assistant_movement'] === "Solicitud Depósito") {
                            //BUSCAMOS EL DEPOSITO
                            $assistant[] = $mov;

                            //Busacamos su origen 
                            $cxc = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', 'Cobro de Facturas')->WHERE('accountsReceivable_movementID', '=', $mov['assistant_movementID'])->WHERE('accountsReceivable_company', '=', $mov['assistant_companieKey'])->WHERE('accountsReceivable_branchOffice', '=', $mov['assistant_branchKey'])->first();

                            //BUSCAMOS EL DEPOSITO

                            if($cxc !== null){
                                $solicitudDeposito = PROC_TREASURY::WHERE('treasuries_origin', '=', $cxc->accountsReceivable_movement)->WHERE('treasuries_originID', '=', $cxc->accountsReceivable_movementID)->WHERE('treasuries_company', '=', $mov['assistant_companieKey'])->WHERE('treasuries_branchOffice', '=', $mov['assistant_branchKey'])->first();


                                $deposito = PROC_TREASURY::where("treasuries_company", "=", $mov['accountsReceivable_company'])->where('treasuries_branchOffice', "=", $mov['accountsReceivable_branchOffice'])->where("treasuries_origin", "=",  $solicitudDeposito->treasuries_movement)->where("treasuries_originID", "=", $solicitudDeposito->treasuries_movementID)->where("treasuries_originType", "=", "Din")->where("treasuries_status", "=", "FINALIZADO")->get()->toArray();

                                // $deposito = PROC_TREASURY::where("treasuries_company", "=", $mov['accountsReceivable_company'])->where('treasuries_branchOffice', "=", $mov['accountsReceivable_branchOffice'])->where("treasuries_origin", "=", $mov['accountsReceivable_movement'])->where("treasuries_originID", "=", $mov['accountsReceivable_movementID'])->where("treasuries_originType", "=", "Din")->where("treasuries_status", "=", "FINALIZADO")->get()->toArray();

                                if (count($deposito) > 0) {
                                    //BUSCAMOS EL AUXILIAR DEL DEPOSITO
                                    $assistant2 = PROC_ASSISTANT::join("CAT_CUSTOMERS", "CAT_CUSTOMERS.customers_key", "=", "PROC_ASSISTANT.assistant_account")
                                        ->where("assistant_branch", "=", "CxC")->where("assistant_movement", "=", $deposito[0]["treasuries_movement"])->where("assistant_moduleID", "=", $deposito[0]["treasuries_id"])->where("assistant_canceled", "=", 0)->first();
                                    $assistant[] = $assistant2;
                                }
                            }
                        } else if ($mov['accountsReceivable_movement'] === $mov['assistant_movement']) {
                            $assistant[] = $mov;
                        }
                    }



                    $proveedorAssistant = [];
                    if (count($assistant) > 0) {
                        foreach ($assistant as $proveedor) {
                            $key = $proveedor['customers_businessName'] . '-' . $proveedor['assistant_money'];

                            if (!isset($proveedorAssistant[$key])) {
                                $proveedorAssistant[$key] = [
                                    'customers_key' => $proveedor['customers_key'],
                                    'customers_businessName' => $proveedor['customers_businessName'],
                                    'assistant_money' => $proveedor['assistant_money'],
                                    'cuentasxp' => []
                                ];
                            }

                            $proveedorAssistant[$key]['cuentasxp'][] = $proveedor;
                        }
                    }


                    if (session('company')->companies_logo == null || session('company')->companies_logo == '') {
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

                    $pdf = PDF::loadView('page.Reportes.CuentasXCobrar.estadoCuentaCXC-reporte', ['logo' => $logoBase64, 'cxp' => $assistant, 'nameCliente' => $nameCliente, 'nameFecha' => $nameFecha, 'nameMoneda' => $nameMoneda, 'proveedoresEstado' => $proveedorAssistant]);
                    $pdf->set_paper('a4', 'landscape');
                    return $pdf->stream();;
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

    function selectCategoria()
    {
        $categorias = CAT_CUSTOMERS_CATEGORY::where('categoryCostumer_status', '=', 'Alta')->get();
        $categorias_array = array();
        foreach ($categorias as $categorias) {
            $categorias_array[$categorias->Todos] = 'Todos';
            $categorias_array[$categorias->categoryCostumer_name] = $categorias->categoryCostumer_name;
        }
        return $categorias_array;
    }

    function selectGrupo()
    {
        $grupos = CAT_CUSTOMERS_GROUP::where('groupCustomer_status', '=', 'Alta')->get();
        $grupos_array = array();
        foreach ($grupos as $grupos) {
            $grupos_array[$grupos->Todos] = 'Todos';
            $grupos_array[$grupos->groupCustomer_name] = $grupos->groupCustomer_name;
        }
        return $grupos_array;
    }
}
