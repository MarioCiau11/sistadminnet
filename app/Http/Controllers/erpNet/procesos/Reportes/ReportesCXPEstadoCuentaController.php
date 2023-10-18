<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesCXPEstadoCuentaExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_PROVIDER_CATEGORY;
use App\Models\agrupadores\CAT_PROVIDER_GROUP;
use App\Models\catalogos\CAT_PROVIDERS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\modulos\PROC_ASSISTANT;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use App\Models\catalogos\CONF_MONEY;
use App\Models\modulos\PROC_TREASURY;
use DateTime;

class ReportesCXPEstadoCuentaController extends Controller
{
    public function index()
    {
        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }
        $proveedor = $this->selectProveedores();
        $selectMonedas = $this->getMonedas();
        $categorias = $this->selectCategoria();
        $grupos = $this->selectGrupo();
        $cuentasxpagar = [];
        $movimientosCxP =  PROC_ASSISTANT::join("CAT_PROVIDERS", "CAT_PROVIDERS.providers_key", "=", "PROC_ASSISTANT.assistant_account")
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_ASSISTANT.assistant_branchKey')
            ->join("PROC_ACCOUNTS_PAYABLE", "PROC_ACCOUNTS_PAYABLE.accountsPayable_id", "=", "PROC_ASSISTANT.assistant_moduleID")
            ->whereIn("PROC_ACCOUNTS_PAYABLE.accountsPayable_status", ["POR AUTORIZAR", "FINALIZADO"])
            ->where("assistant_branch", "=", "CxP")
            ->where("assistant_canceled", "=", 0)
            ->where('PROC_ASSISTANT.assistant_companieKey', '=', session('company')->companies_key)
            ->where('assistant_money', '=', $parametro->generalParameters_defaultMoney)
            ->whereAssistantCreated("Mes")
            ->orderBy('PROC_ACCOUNTS_PAYABLE.updated_at', 'DESC')
            ->get()->toArray();

        foreach ($movimientosCxP as $mov) {

            if ($mov['assistant_movement'] === "Sol. de Cheque/Transferencia") {
                //BUSCAMOS EL DEPOSITO
                $cuentasxpagar[] = $mov;

                $cheque = PROC_TREASURY::where("treasuries_company", "=", $mov['accountsPayable_company'])->where('treasuries_branchOffice', "=", $mov['accountsPayable_branchOffice'])->where("treasuries_origin", "=", $mov['accountsPayable_movement'])->where("treasuries_originID", "=", $mov['accountsPayable_movementID'])->where("treasuries_originType", "=", "Din")->where("treasuries_status", "=", "FINALIZADO")->get()->toArray();

                if (count($cheque) > 0) {
                    //BUSCAMOS EL AUXILIAR DEL DEPOSITO
                    $assistant = PROC_ASSISTANT::join("CAT_PROVIDERS", "CAT_PROVIDERS.providers_key", "=", "PROC_ASSISTANT.assistant_account")
                        ->where("assistant_branch", "=", "CxP")->where("assistant_movement", "=", $cheque[0]["treasuries_movement"])->where("assistant_moduleID", "=", $cheque[0]["treasuries_id"])->where("assistant_canceled", "=", 0)->first();
                    $cuentasxpagar[] = $assistant;
                }
            } else if ($mov['accountsPayable_movement'] === $mov['assistant_movement']) {
                $cuentasxpagar[] = $mov;
            }
        }

        //  dd($cuentasxpagar);

        return view('page.Reportes.CuentasXPagar.indexCXPEstadoCuenta', compact('proveedor', 'selectMonedas', 'parametro', 'cuentasxpagar', 'categorias', 'grupos'));
    }

    public function reportesCXPEstadoCuentaAction(Request $request)
    {
        $nameProveedor = $request->nameProveedor;
        $proveedor = $request->proveedor;
        $nameCategoria = $request->nameCategoria;
        $nameGrupo = $request->nameGrupo;
        $nameFecha = $request->nameFecha;
        $nameMoneda = $request->nameMoneda;

        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;
        // dd($fechaInicio, $fechaFinal);

        if ($fechaInicio !== null && $fechaFinal !== null && $nameFecha === "Rango Fechas") {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }


        switch ($request->input('action')) {
            case 'Búsqueda':
                $reportes_collection_filtro = PROC_ASSISTANT::join("CAT_PROVIDERS", "CAT_PROVIDERS.providers_key", "=", "PROC_ASSISTANT.assistant_account")
                    ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_ASSISTANT.assistant_branchKey')
                    ->where("assistant_branch", "=", "CxP")
                    ->where('PROC_ASSISTANT.assistant_companieKey', '=', session('company')->companies_key)
                    ->where("assistant_canceled", "=", "0")
                    ->whereAssistantAccount($nameProveedor)
                    ->whereProviderCategory($nameCategoria)
                    ->whereProviderGroup($nameGrupo)
                    ->whereAssistantCreated($nameFecha)
                    ->whereAssistantMoney($nameMoneda)
                    ->orderBy('PROC_ASSISTANT.updated_at', 'DESC')
                    ->get();
                // dd($reportes_collection_filtro);

                $reportes_filtro_array = $reportes_collection_filtro->toArray();
                $nameFecha = $request->nameFecha;


                return redirect()->route('vista.reportes.cxp-estado-cuenta')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('nameProveedor', $nameProveedor)
                    ->with('proveedor', $proveedor)
                    ->with('nameCategoria', $nameCategoria)
                    ->with('nameGrupo', $nameGrupo)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameMoneda', $nameMoneda)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal);
                break;

            case 'Exportar excel':
                try {
                    $cxp = new ReportesCXPEstadoCuentaExport($nameProveedor, $proveedor, $nameCategoria, $nameGrupo, $nameFecha, $nameMoneda);
                    return Excel::download($cxp, 'Reporte de Estado de Cuenta.xlsx');
                } catch (\Throwable $th) {
                    return redirect()->route('vista.reportes.cxp-estado-cuenta')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                }


                break;
            case 'Exportar PDF':

                $assistant = [];
                $movimientosCxP =  PROC_ASSISTANT::join("CAT_PROVIDERS", "CAT_PROVIDERS.providers_key", "=", "PROC_ASSISTANT.assistant_account")
                    ->join("PROC_ACCOUNTS_PAYABLE", "PROC_ACCOUNTS_PAYABLE.accountsPayable_id", "=", "PROC_ASSISTANT.assistant_moduleID")
                    ->whereIn("PROC_ACCOUNTS_PAYABLE.accountsPayable_status", ["POR AUTORIZAR", "FINALIZADO"])
                    ->where("assistant_canceled", "=", "0")
                    ->where("assistant_branch", "=", "CxP")
                    ->where('PROC_ASSISTANT.assistant_companieKey', '=', session('company')->companies_key)
                    ->whereAssistantAccount($nameProveedor)
                    ->whereProviderCategory($nameCategoria)
                    ->whereProviderGroup($nameGrupo)
                    ->whereAssistantCreated($nameFecha)
                    ->whereAssistantMoney($nameMoneda)
                    ->orderBy('PROC_ASSISTANT.updated_at', 'ASC')
                    ->get()
                    ->toArray();

                foreach ($movimientosCxP as $mov) {
                    if ($mov['assistant_movement'] === "Sol. de Cheque/Transferencia") {
                        //BUSCAMOS EL DEPOSITO
                        $assistant[] = $mov;

                        $cheque = PROC_TREASURY::where("treasuries_company", "=", $mov['accountsPayable_company'])->where('treasuries_branchOffice', "=", $mov['accountsPayable_branchOffice'])->where("treasuries_origin", "=", $mov['accountsPayable_movement'])->where("treasuries_originID", "=", $mov['accountsPayable_movementID'])->where("treasuries_originType", "=", "Din")->where("treasuries_status", "=", "FINALIZADO")->get()->toArray();

                        if (count($cheque) > 0) {
                            //BUSCAMOS EL AUXILIAR DEL DEPOSITO
                            $assistant2 = PROC_ASSISTANT::join("CAT_PROVIDERS", "CAT_PROVIDERS.providers_key", "=", "PROC_ASSISTANT.assistant_account")
                                ->where("assistant_branch", "=", "CxP")->where("assistant_movement", "=", $cheque[0]["treasuries_movement"])->where("assistant_moduleID", "=", $cheque[0]["treasuries_id"])->where("assistant_canceled", "=", 0)->first();
                            $assistant[] = $assistant2;
                        }
                    } else if ($mov['accountsPayable_movement'] === $mov['assistant_movement']) {
                        $assistant[] = $mov;
                    }
                }



                $proveedorAssistant = [];

                if (count($assistant) > 0) {
                    foreach ($assistant as $proveedor) {
                        $key = $proveedor['providers_name'] . '-' . $proveedor['assistant_money'];

                        if (!isset($proveedorAssistant[$key])) {
                            $proveedorAssistant[$key] = [
                                'providers_key' => $proveedor['providers_key'],
                                'providers_name' => $proveedor['providers_name'],
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

                $pdf = PDF::loadView('page.Reportes.CuentasXPagar.estadoCuentaCXP-reporte', ['logo' => $logoBase64, 'cxp' => $assistant, 'nameProveedor' => $nameProveedor, 'nameFecha' => $nameFecha, 'nameMoneda' => $nameMoneda, 'proveedoresEstado' => $proveedorAssistant]);
                $pdf->set_paper('a4', 'landscape');
                return $pdf->stream();
                break;
        }
    }

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
        $categorias = CAT_PROVIDER_CATEGORY::where('categoryProvider_status', '=', 'Alta')->get();
        $categorias_array = array();
        foreach ($categorias as $categorias) {
            $categorias_array[$categorias->Todos] = 'Todos';
            $categorias_array[$categorias->categoryProvider_name] = $categorias->categoryProvider_name;
        }
        return $categorias_array;
    }

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
