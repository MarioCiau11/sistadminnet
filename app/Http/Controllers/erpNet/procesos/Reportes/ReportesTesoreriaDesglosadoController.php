<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesTesoreriaDesglosadoExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CAT_PROVIDERS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\modulos\helpers\PROC_MONEY_ACCOUNTS_BALANCE;
use App\Models\modulos\PROC_ASSISTANT;
use App\Models\modulos\PROC_TREASURY;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportesTesoreriaDesglosadoController extends Controller
{
    public function index()
    {

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }
        $cuentas = $this->selectCuentas();
        $selectMonedas = $this->getMonedas();
        $tesoreria = PROC_ASSISTANT::join('PROC_MONEY_ACCOUNTS_BALANCE', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_moneyAccount', '=', 'PROC_ASSISTANT.assistant_account')
            ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_ASSISTANT.assistant_companieKey')
            ->select('PROC_ASSISTANT.*', 'CAT_COMPANIES.companies_key', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_initialBalance')
            ->where('PROC_ASSISTANT.assistant_companieKey', '=', session('company')->companies_key)
            ->where('PROC_ASSISTANT.assistant_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->orderBy('PROC_ASSISTANT.created_at', 'ASC')->paginate(25);

        return view('page.Reportes.Tesoreria.indexTesoreriaDesglosado', compact('cuentas', 'selectMonedas', 'parametro', 'tesoreria'));
    }

    public function reportesTesoreriaDesglosadoAction(Request $request)
    {

        //  dd($request->all());

        $nameCuenta = $request->nameCuenta;
        $nameFecha = $request->nameFecha;
        $nameMoneda = $request->nameMoneda;
        $nameMov = $request->nameMov;

        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;
        if ($fechaInicio !== null && $fechaFinal !== null && $nameFecha === "Rango Fechas") {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }

        switch ($request->input('action')) {
            case 'Búsqueda':
                $reportes_collection_filtro = PROC_ASSISTANT::join('PROC_MONEY_ACCOUNTS_BALANCE', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_moneyAccount', '=', 'PROC_ASSISTANT.assistant_account')->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_ASSISTANT.assistant_companieKey')
                    ->select('PROC_ASSISTANT.*', 'CAT_COMPANIES.companies_key', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_initialBalance')
                    ->whereAssistantAccount($nameCuenta)
                    ->whereCreatedAt($nameFecha)
                    ->whereAssistantMoney($nameMoneda)
                    ->whereAssistantMovement($nameMov)
                    ->where('PROC_ASSISTANT.assistant_companieKey', '=', session('company')->companies_key)
                    ->orderBy('PROC_ASSISTANT.created_at', 'DESC')->get();


                $reportes_filtro_array = $reportes_collection_filtro->toArray();
                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.reportes.tesoreria-desglosado')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('nameCuenta', $nameCuenta)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameMoneda', $nameMoneda)
                    ->with('nameMov', $nameMov)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal);
                break;

            case 'Exportar excel':
                try {
                    $tesoreria = new ReportesTesoreriaDesglosadoExport($nameCuenta, $nameFecha, $nameMoneda, $nameMov);
                    return Excel::download($tesoreria, 'Reporte Tesoreria Desglosado.xlsx');
                } catch (\Throwable $th) {
                    return redirect()->route('vista.reportes.tesoreria-desglosado')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                }

                break;


            case 'Exportar PDF':

                $datosDesglosados = [];

                //Obtenemos los datos de la tabla auxiliar
                $auxiliares = $this->obtenerDatosAuxiliaresDesglosados($nameCuenta, $nameMoneda, $nameMov, $nameFecha);

                if ($auxiliares->isEmpty()) {
                    return redirect()->route('vista.reportes.tesoreria-desglosado')->with('status', false)->with('message', 'No se encontraron registros');
                } else {

                    $this->procesarDatosDesglosados($auxiliares, $datosDesglosados);

                    $datosAuxiliares = $this->obtenerDatosAuxiliares($nameCuenta, $nameMoneda, $nameMov);

                    // Obtener las keys de los datos desglosados
                    $keys = array_keys($datosDesglosados);

                    // Obtener el primer dato del mes y sus datos auxiliares
                    $primerDatoDelMes = $keys[0];
                    $datosAuxiliaresDelMes = $datosAuxiliares[$primerDatoDelMes];

                    // Buscar la posición del primer dato del mes en los datos auxiliares
                    $posicion = array_key_exists($primerDatoDelMes, $datosAuxiliares) ? array_search($primerDatoDelMes, array_keys($datosAuxiliares)) : 0;

                    // Cortar los datos auxiliares a partir de la posición del primer dato del mes
                    $datosAuxiliaresFinales = array_slice($datosAuxiliares, $posicion, count($datosAuxiliares), true);

                    $fin = array_diff_key($datosAuxiliares, $datosAuxiliaresFinales);
                    // dd($datosDesglosados, $datosAuxiliares, $keys, $datosAuxiliares2, $primerDatoDelMes, $posicion, $datosAuxiliaresFinales, $fin);
                    $historialDesglose = [];
                    foreach ($fin as $fecha => $fechasTesoreria)
                    // dd($fecha);
                    {
                        foreach ($fechasTesoreria as $auxiliar) {
                            $totalCargos = 0;
                            $totalAbonos = 0;
                            $saldosFinales = 0;
                            $isPrimerIngreso = false;
                            $montoInicial = 0;

                            foreach ($auxiliar as $key => $bancosAuxiliar) {
                                if ($bancosAuxiliar['cargos'] != null) {
                                    $totalCargos += (float) $bancosAuxiliar['cargos'];
                                    // dd($totalCargos);
                                }
                                if ($bancosAuxiliar['abonos'] != null) {
                                    $totalAbonos += (float) $bancosAuxiliar['abonos'];
                                    // dd($totalAbonos);
                                }

                                if ($key == 0) {
                                    $montoInicial = array_key_exists($bancosAuxiliar['cuenta'], $historialDesglose) ? $historialDesglose[$bancosAuxiliar['cuenta']] : $montoInicial;
                                    $saldosFinales = $montoInicial + $totalCargos - $totalAbonos;
                                } else {
                                    $saldosFinales = $montoInicial + $totalCargos - $totalAbonos;
                                }
                                // dd($saldosFinales, $montoInicial);

                                if (!array_key_exists($bancosAuxiliar['cuenta'], $historialDesglose)) {
                                    //Creamos la key en el arreglo q contendra los saldos finales
                                    $historialDesglose[$bancosAuxiliar['cuenta']] = $saldosFinales;
                                } else {
                                    //Incrementamos el saldo final de la misma key
                                    $historialDesglose[$bancosAuxiliar['cuenta']] = $saldosFinales;
                                }
                            }
                        }
                        //  dd($historialDesglose);
                    }
                    //  dd($historialDesglose);


                    $logoBase64 = $this->obtenerLogoEmpresa();


                    $pdf = PDF::loadView('page.Reportes.Tesoreria.tesoreriaDesglosado-reporte', ['logo' => $logoBase64, 'tesorerias' => $datosDesglosados, 'datosAuxiliares' => $datosAuxiliares, 'saldoFinal' => $historialDesglose]);
                    $pdf->set_paper('a4', 'landscape');

                    return $pdf->stream();
                }
                break;
            default:
                break;
        }
    }

    private function procesarDatosDesglosados($auxiliares, &$datosDesglosados)
    {
        foreach ($auxiliares as $key => $auxiliar) {
            $beneficiario = null;
            $fecha =  Carbon::parse($auxiliar->created_at)->format('d/m/Y');
            $cuenta = $auxiliar->assistant_account;
            $saldoInicio = 0;
            $tesoreriaBeneficiario = PROC_TREASURY::find($auxiliar->assistant_moduleID);
            // dd($tesoreriaBeneficiario);
            if ($tesoreriaBeneficiario->treasuries_movement == 'Transferencia Electrónica' || $tesoreriaBeneficiario->treasuries_movement == 'Egreso') {
                //Como es Transferencia Electrónica, entonces buscamos su origen, en este caso treasuries_origin
                $solicitudCheque = PROC_TREASURY::WHERE('treasuries_movement', '=', trim($tesoreriaBeneficiario->treasuries_origin))->WHERE('treasuries_movementID', '=', $tesoreriaBeneficiario->treasuries_originID)
                ->WHERE('treasuries_branchOffice', '=', $tesoreriaBeneficiario->treasuries_branchOffice)->first();
                // dd($solicitudCheque);

            }
            
            if ($tesoreriaBeneficiario->treasuries_beneficiary !== null) {
                if (($tesoreriaBeneficiario->treasuries_movement === "Transferencia Electrónica" && $solicitudCheque->treasuries_originType === 'CxP') || ($tesoreriaBeneficiario->treasuries_movement === "Egreso" && $tesoreriaBeneficiario->treasuries_originType !== 'CxC')) {
                    $beneficiario = CAT_PROVIDERS::find($tesoreriaBeneficiario->treasuries_beneficiary);
                    $beneficiario = $beneficiario->providers_name;
                } else {
                    $beneficiario = CAT_CUSTOMERS::find($tesoreriaBeneficiario->treasuries_beneficiary);
                    $beneficiario = $beneficiario->customers_businessName;
                }
            }


            if (array_key_exists($fecha, $datosDesglosados) && array_key_exists($cuenta, $datosDesglosados[$fecha])) {


                if ($auxiliar->assistant_balanceInitial === null) {
                    $saldoInicio = $auxiliar->moneyAccountsBalance_initialBalance;
                }

                $datosFinales  = [
                    "cuenta" => $auxiliar->assistant_account,
                    "movimiento" => $auxiliar->assistant_movement . " " . $auxiliar->assistant_movementID,
                    "beneficiario" => $beneficiario,
                    "referencia" => $auxiliar->assistant_reference,
                    "inicio" => $saldoInicio,
                    "cargos" => $auxiliar->assistant_charge,
                    "abonos" => $auxiliar->assistant_payment,
                    "saldos" => 0,
                    "fecha" => $fecha,
                    "saldoIngreso" => $auxiliar->moneyAccountsBalance_initialBalance,
                    'inicioSaldoBit' =>  $auxiliar->assistant_balanceInitial
                ];


                array_push($datosDesglosados[$fecha][$cuenta], $datosFinales);
            } else {
                $saldoInicio = 0;
                if ($auxiliar->assistant_balanceInitial === null) {
                    $saldoInicio = $auxiliar->moneyAccountsBalance_initialBalance;
                }


                $datosDesglosados[$fecha][$cuenta] = [];
                $datosFinales  = [
                    "cuenta" => $auxiliar->assistant_account,
                    "movimiento" => $auxiliar->assistant_movement . " " . $auxiliar->assistant_movementID,
                    "beneficiario" => $beneficiario,
                    "referencia" => $auxiliar->assistant_reference,
                    "inicio" => $saldoInicio,
                    "cargos" => $auxiliar->assistant_charge,
                    "abonos" => $auxiliar->assistant_payment,
                    "saldos" => 0,
                    "fecha" => $fecha,
                    "saldoIngreso" => $auxiliar->moneyAccountsBalance_initialBalance,
                    'inicioSaldoBit' =>  $auxiliar->assistant_balanceInitial
                ];

                array_push($datosDesglosados[$fecha][$cuenta], $datosFinales);
            }
        }
        // dd($tesoreriaOrigen);
    }



    private function obtenerDatosAuxiliares($cuenta, $moneda, $mov)
    {
        $reportesTesoreriaDesglosadoAuxiliar = new ReportesTesoreriaDesglosadoAuxiliarController();
        return $reportesTesoreriaDesglosadoAuxiliar->AuxiliarDesglosado($cuenta, $moneda, $mov);
    }

    //ponemos en una función la parte donde se obtiene el logo de la empresa
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

    private function obtenerDatosAuxiliaresDesglosados($cuenta, $moneda, $mov, $nameFecha)
    {
        return PROC_ASSISTANT::join('PROC_MONEY_ACCOUNTS_BALANCE', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_moneyAccount', '=', 'PROC_ASSISTANT.assistant_account')->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_ASSISTANT.assistant_companieKey')
            ->select('PROC_ASSISTANT.*', 'CAT_COMPANIES.companies_logo', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_initialBalance')
            ->whereAssistantAccount($cuenta)
            ->whereAssistantMoney($moneda)
            ->whereCreatedAt($nameFecha)
            ->whereAssistantMovement($mov)
            ->where('PROC_ASSISTANT.assistant_companieKey', '=', session('company')->companies_key)
            ->orderBy('PROC_ASSISTANT.created_at', 'ASC')
            ->get();
    }



    function selectCuentas()
    {
        $cuentas = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_status', '=', 'Alta')
            ->where('moneyAccountsBalance_company', '=', session('company')->companies_key)
            ->get();
        $cuentas_array = array();
        $cuentas_array['Todos'] = 'Todos';
        foreach ($cuentas as $cuenta) {
            $cuentas_array[$cuenta->moneyAccountsBalance_moneyAccount] = $cuenta->moneyAccountsBalance_moneyAccount;
        }
        return $cuentas_array;
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