<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesTesoreriaConcentradoExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_MONEY_ACCOUNTS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\modulos\helpers\PROC_MONEY_ACCOUNTS_BALANCE;
use App\Models\modulos\PROC_ASSISTANT;
use App\Models\modulos\PROC_TREASURY;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use stdClass;

class ReportesTesoreriaConcentradosController extends Controller
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

            ->orderBy('PROC_ASSISTANT.created_at', 'DESC')->paginate(25);

        return view('page.Reportes.Tesoreria.indexTesoreriaConcentrado', compact('cuentas', 'selectMonedas', 'parametro', 'tesoreria'));
    }

    public function reportesTesoreriaAction(Request $request)
    {
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

                return redirect()->route('vista.reportes.tesoreria-concentrados')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('nameCuenta', $nameCuenta)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameMoneda', $nameMoneda)
                    ->with('nameMov', $nameMov)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal);
                break;

            case 'Exportar excel':

                try {
                    $tesoreria = new ReportesTesoreriaConcentradoExport($nameCuenta, $nameFecha, $nameMoneda, $nameMov);
                    return Excel::download($tesoreria, 'Reporte Tesoreria Concentrado.xlsx');
                } catch (\Throwable $th) {
                    return redirect()->route('vista.reportes.tesoreria-concentrados')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                }

                break;

            case 'Exportar PDF':

                $tesoreriaNombre = PROC_MONEY_ACCOUNTS_BALANCE::join('CAT_MONEY_ACCOUNTS',  'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_moneyAccount', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_key')
                    ->join('PROC_ASSISTANT', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_moneyAccount', '=',  'PROC_ASSISTANT.assistant_account')
                    ->join('CAT_COMPANIES', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_company', '=', 'CAT_COMPANIES.companies_key')
                    ->whereMoneyAccountsBalanceMoneyAccount($nameCuenta)
                    ->whereMoneyAccountsBalanceMoney($nameMoneda)
                    ->whereCreatedAt($nameFecha)
                    ->where('moneyAccountsBalance_company', '=', session('company')->companies_key)
                    ->first();


                if ($tesoreriaNombre === null) {
                    return redirect()->route('vista.reportes.tesoreria-concentrados')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
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

                    $tesoreria = PROC_MONEY_ACCOUNTS_BALANCE::join('CAT_MONEY_ACCOUNTS',  'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_moneyAccount', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_key')
                        ->join('PROC_ASSISTANT', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_moneyAccount', '=',  'PROC_ASSISTANT.assistant_account',)
                        ->join('CAT_COMPANIES', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_company', '=', 'CAT_COMPANIES.companies_key')
                        ->whereMoneyAccountsBalanceMoneyAccount($nameCuenta)
                        ->whereMoneyAccountsBalanceMoney($nameMoneda)
                        ->whereCreatedAt($nameFecha)
                        ->where('moneyAccountsBalance_company', '=', session('company')->companies_key)
                        ->get();




                    $cuentasUnicas = [];
                    foreach ($tesoreria as $key => $tesoreriaCuenta) {
                        $jsonCuentas = new stdClass();

                        if (array_key_exists($tesoreriaCuenta->moneyAccounts_key, $cuentasUnicas)) {
                            //si existe
                            $lastCharge =  (float) $cuentasUnicas[$tesoreriaCuenta->moneyAccounts_key]->assistant_charge;
                            $newCharge = (float) $tesoreriaCuenta->assistant_charge;

                            $lastPayment =  (float) $cuentasUnicas[$tesoreriaCuenta->moneyAccounts_key]->assistant_payment;
                            $newPayment = (float) $tesoreriaCuenta->assistant_payment;

                            if ($tesoreriaCuenta->assistant_charge !== null) {
                                $cuentasUnicas[$tesoreriaCuenta->moneyAccounts_key]->assistant_charge = $lastCharge + $newCharge;
                            }

                            if ($tesoreriaCuenta->assistant_payment !== null) {
                                $cuentasUnicas[$tesoreriaCuenta->moneyAccounts_key]->assistant_payment = $lastPayment + $newPayment;
                            }
                        } else {

                            $jsonCuentas->moneyAccounts_key = $tesoreriaCuenta->moneyAccounts_key;
                            $jsonCuentas->moneyAccounts_numberAccount = $tesoreriaCuenta->moneyAccounts_numberAccount;
                            $jsonCuentas->moneyAccounts_referenceBank = $tesoreriaCuenta->moneyAccounts_referenceBank;
                            $jsonCuentas->moneyAccountsBalance_initialBalance = $tesoreriaCuenta->moneyAccountsBalance_initialBalance;
                            $jsonCuentas->assistant_charge = $tesoreriaCuenta->assistant_charge;
                            $jsonCuentas->assistant_payment = $tesoreriaCuenta->assistant_payment;
                            $jsonCuentas->moneyAccountsBalance_moneyAccount = $tesoreriaCuenta->moneyAccountsBalance_moneyAccount;
                            $jsonCuentas->moneyAccountsBalance_balance = $tesoreriaCuenta->moneyAccountsBalance_balance;


                            $cuentasUnicas[$tesoreriaCuenta->moneyAccounts_key] = $jsonCuentas;
                        }
                    }
                    // dd($cuentasUnicas);

                    $pdf = PDF::loadView('page.Reportes.Tesoreria.tesoreriaConcentrado-reporte',  ['logo' => $logoBase64, 'tesoreria' => $tesoreriaNombre, 'nameCuenta' => $nameCuenta, 'nameFecha' => $nameFecha, 'nameMoneda' => $nameMoneda, 'nameMov' => $nameMov, 'cuentas' => $cuentasUnicas]);
                    $pdf->set_paper('a4', 'landscape');

                    return $pdf->stream();
                }
                break;

            default:
                break;
        }
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
