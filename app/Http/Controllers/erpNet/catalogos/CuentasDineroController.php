<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Exports\MoneyAccountExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_COMPANIES;
use App\Models\catalogos\CAT_FINANCIAL_INSTITUTIONS;
use App\Models\catalogos\CAT_MONEY_ACCOUNTS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\modulos\helpers\PROC_MONEY_ACCOUNTS_BALANCE;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Node\Stmt\TryCatch;

class CuentasDineroController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:Cuentas de Dinero']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $moneyAccounts = CAT_MONEY_ACCOUNTS::join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_company')
            ->join('CAT_FINANCIAL_INSTITUTIONS', 'CAT_FINANCIAL_INSTITUTIONS.instFinancial_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_bank')
            ->join('CONF_MONEY', 'CONF_MONEY.money_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_money')
            ->select(
                'CAT_MONEY_ACCOUNTS.*',
                'CAT_COMPANIES.companies_name',
                'CAT_FINANCIAL_INSTITUTIONS.instFinancial_name',
                'CONF_MONEY.money_key'
            )
            ->where('CAT_MONEY_ACCOUNTS.moneyAccounts_status', '=', 'Alta')
            ->get();


        return view('page.catalogos.cuentaDinero.cuenta-dinero.index',  compact('moneyAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $selectInstFinancial = $this->getInstFinancial();
        $selectMonedas = $this->getMonedas();
        $selectEmpresas = $this->getEmpresas();
        return view('page.catalogos.cuentaDinero.cuenta-dinero.create', compact('selectInstFinancial', 'selectMonedas', 'selectEmpresas'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $cat_moneyAccounts_request = $request->except('_token');
        $isKeyMoneyAccounts = CAT_MONEY_ACCOUNTS::where('moneyAccounts_key', '=', $cat_moneyAccounts_request['keyClaveBanco'])->first();

        if ($isKeyMoneyAccounts) {
            $message = "La clave: " . $cat_moneyAccounts_request['keyClaveBanco'] . " ya existe en la base de datos";
            $status = false;
        } else {
            $cat_MoneyAccounts = new CAT_MONEY_ACCOUNTS();
            $cat_MoneyAccounts->moneyAccounts_key = $cat_moneyAccounts_request['keyClaveBanco'];
            $cat_MoneyAccounts->moneyAccounts_company = $cat_moneyAccounts_request['empresa'];
            $cat_MoneyAccounts->moneyAccounts_bank = $cat_moneyAccounts_request['nameBanco'];
            $cat_MoneyAccounts->moneyAccounts_numberAccount = $cat_moneyAccounts_request['numeroCuenta'];
            $cat_MoneyAccounts->moneyAccounts_keyAccount = $cat_moneyAccounts_request['cuentaCLABE'];
            $cat_MoneyAccounts->moneyAccounts_bankAgreement = $cat_moneyAccounts_request['convenioBanco'];
            $cat_MoneyAccounts->moneyAccounts_accountType = $cat_moneyAccounts_request['tipoCuenta'];
            $cat_MoneyAccounts->moneyAccounts_money = $cat_moneyAccounts_request['moneda'];
            $cat_MoneyAccounts->moneyAccounts_status = $cat_moneyAccounts_request['status'];
            $cat_MoneyAccounts->moneyAccounts_referenceBank = $cat_moneyAccounts_request['rBanco'];



            try {
                $isCreate = $cat_MoneyAccounts->save();
                $cuentaSaldo = new PROC_MONEY_ACCOUNTS_BALANCE();
                $cuentaSaldo->moneyAccountsBalance_moneyAccount = $cat_moneyAccounts_request['keyClaveBanco'];
                $cuentaSaldo->moneyAccountsBalance_accountType = $cat_moneyAccounts_request['tipoCuenta'];
                $cuentaSaldo->moneyAccountsBalance_money = $cat_moneyAccounts_request['moneda'];
                $cuentaSaldo->moneyAccountsBalance_balance = null;
                $cuentaSaldo->moneyAccountsBalance_initialBalance = null;
                $cuentaSaldo->moneyAccountsBalance_status =  $cat_moneyAccounts_request['status'];
                $cuentaSaldo->moneyAccountsBalance_company =  $cat_moneyAccounts_request['empresa'];
                $cuentaSaldo->save();

                if ($isCreate) {
                    $message = "La clave: " . $cat_moneyAccounts_request['keyClaveBanco'] . " se registró correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido crear la cuenta de dinero " . $cat_moneyAccounts_request['keyClaveBanco'];
                    $status = false;
                }
            } catch (\Throwable $th) {

                $message = "Por favor comuníquese con el administrador de sistemas ya que no se pudo crear la cuenta de dinero.";
                return redirect()->route('catalogo.cuenta-dinero.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('catalogo.cuenta-dinero.index')->with('message', $message)->with('status', $status);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        try {
            $id = Crypt::decrypt($id);
            $moneyAccounts = CAT_MONEY_ACCOUNTS::where('moneyAccounts_id', '=', $id)->first();

            $selectInstFinancial = $this->getInstFinancial();
            $selectMonedas = $this->getMonedas();
            $selectEmpresas = $this->getEmpresas();
            return view('page.catalogos.cuentaDinero.cuenta-dinero.show', compact('selectInstFinancial', 'selectMonedas', 'selectEmpresas', 'moneyAccounts'));
        } catch (\Exception $e) {
            return redirect()->route('catalogo.cuenta-dinero.index')->with('message', 'No se pudo encontrar la cuenta de dinero')->with('status', false);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $id = Crypt::decrypt($id);
            $moneyAccounts = CAT_MONEY_ACCOUNTS::where('moneyAccounts_id', '=', $id)->first();

            $selectInstFinancial = $this->getInstFinancial();
            $selectMonedas = $this->getMonedas();
            $selectEmpresas = $this->getEmpresas();
            return view('page.catalogos.cuentaDinero.cuenta-dinero.edit', compact('selectInstFinancial', 'selectMonedas', 'selectEmpresas', 'moneyAccounts'));
        } catch (\Exception $e) {
            return redirect()->route('catalogo.cuenta-dinero.index')->with('message', 'No se pudo encontrar la cuenta de dinero')->with('status', false);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        try {

            $id = Crypt::decrypt($id);
            $cat_moneyAccounts_request = $request->except('_token');
            $cat_MoneyAccounts = CAT_MONEY_ACCOUNTS::where('moneyAccounts_id', '=', $id)->first();
            $cat_MoneyAccounts->moneyAccounts_company = $cat_moneyAccounts_request['empresa'];
            $cat_MoneyAccounts->moneyAccounts_bank = $cat_moneyAccounts_request['nameBanco'];
            $cat_MoneyAccounts->moneyAccounts_numberAccount = $cat_moneyAccounts_request['numeroCuenta'];
            $cat_MoneyAccounts->moneyAccounts_keyAccount = $cat_moneyAccounts_request['cuentaCLABE'];
            $cat_MoneyAccounts->moneyAccounts_bankAgreement = $cat_moneyAccounts_request['convenioBanco'];
            $cat_MoneyAccounts->moneyAccounts_accountType = $cat_moneyAccounts_request['tipoCuenta'];
            $cat_MoneyAccounts->moneyAccounts_money = $cat_moneyAccounts_request['moneda'];
            $cat_MoneyAccounts->moneyAccounts_status = $cat_moneyAccounts_request['status'];
            $cat_MoneyAccounts->moneyAccounts_referenceBank = $cat_moneyAccounts_request['rBanco'];


            //Actualizamos account balance
            $cuentaSaldo = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', '=', $cat_MoneyAccounts->moneyAccounts_key)->first();
            $cuentaSaldo->moneyAccountsBalance_accountType = $cat_moneyAccounts_request['tipoCuenta'];
            $cuentaSaldo->moneyAccountsBalance_money = $cat_moneyAccounts_request['moneda'];
            $cuentaSaldo->moneyAccountsBalance_status =  $cat_moneyAccounts_request['status'];
            $cuentaSaldo->moneyAccountsBalance_company =  $cat_moneyAccounts_request['empresa'];


            try {
                $isUpdate =  $cat_MoneyAccounts->update();
                $isUpdate2 =  $cuentaSaldo->update();
                if ($isUpdate) {
                    $message = "La cuenta de dinero se actualizó correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido actualizar la cuenta de dinero";
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor, vaya con el administrador de sistemas, no se puede actualizar la cuenta de dinero";
                return redirect()->route('catalogo.cuenta-dinero.index')->with('message', $message)->with('status', false);
            }

            return redirect()->route('catalogo.cuenta-dinero.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('catalogo.cuenta-dinero.index')->with('message', 'No se pudo encontrar la cuenta de dinero')->with('status', false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $id = Crypt::decrypt($id);

            $cDinero_delete = CAT_MONEY_ACCOUNTS::where('moneyAccounts_id', $id)->first();
            $cDinero_delete->moneyAccounts_status = 'Baja';

            $isRemoved = $cDinero_delete->update();
            $status = false;
            if ($isRemoved) {
                $message = "La cuenta de dinero se eliminó correctamente";
                $status = true;
            } else {
                $message = "No se ha podido eliminar la cuenta de dinero";
                $status = false;
            }

            return redirect()->route('catalogo.cuenta-dinero.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('catalogo.cuenta-dinero.index')->with('message', 'No se pudo mostrar la cuenta de dinero')->with('status', false);
        }
    }

    public function cuentaDineroAction(Request $request)
    {
        $keyCDinero = $request->keyCDinero;
        $nameBanco = $request->nameBanco;
        $numeroCuenta = $request->numeroCuenta;
        $status = $request->status;
        $typeCuenta = $request->typeCuenta;

        switch ($request->input('action')) {
            case 'Búsqueda':

                $moneyAccounts =  CAT_MONEY_ACCOUNTS::join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_company')
                    ->join('CAT_FINANCIAL_INSTITUTIONS', 'CAT_FINANCIAL_INSTITUTIONS.instFinancial_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_bank')
                    ->join('CONF_MONEY', 'CONF_MONEY.money_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_money')
                    ->select(
                        'CAT_MONEY_ACCOUNTS.*',
                        'CAT_COMPANIES.companies_name',
                        'CAT_FINANCIAL_INSTITUTIONS.instFinancial_name',
                        'CONF_MONEY.money_key'
                    )->whereMoneyAccountsKey($keyCDinero)->whereInstFinancialName($nameBanco)->whereMoneyAccountsNumberAccount($numeroCuenta)->whereMoneyAccountsStatus($status)->whereMoneyAccountsAccountType($typeCuenta)->get();

                return redirect()->route('catalogo.cuenta-dinero.index')->with('moneyAccounts', $moneyAccounts)->with('status', $status)->with('typeCuenta', $typeCuenta)->with('keyCDinero', $keyCDinero)->with('nameBanco', $nameBanco)->with('numeroCuenta', $numeroCuenta);
                break;

            case 'Exportar excel':
                $moneyAccounts = new MoneyAccountExport(
                    $keyCDinero,
                    $nameBanco,
                    $numeroCuenta,
                    $typeCuenta,
                    $status
                );
                return Excel::download($moneyAccounts, 'Cuentas de dinero.xlsx');
                break;

            default:
                break;
        }
    }

    public function getInstFinancial()
    {
        $instFinancial = [];
        $instFinancial_collection = CAT_FINANCIAL_INSTITUTIONS::where('instFinancial_status', '=', 'Alta')->get();
        $instFinancial_array = $instFinancial_collection->toArray();

        foreach ($instFinancial_array as $key => $value) {
            $instFinancial[$value['instFinancial_key']] = $value['instFinancial_name'];
        }
        return $instFinancial;
    }

    public function getMonedas()
    {
        $monedas = [];
        $monedas_collection = CONF_MONEY::where('money_status', '=', 'Alta')->get();
        $monedas_array = $monedas_collection->toArray();

        foreach ($monedas_array as $key => $value) {
            $monedas[trim($value['money_key'])] = $value['money_name'];
        };
        return $monedas;
    }

    public function getEmpresas()
    {
        $empresas = [];
        $empresas_collection = CAT_COMPANIES::where('companies_status', '=', 'Alta')->get();
        $empresas_array = $empresas_collection->toArray();

        foreach ($empresas_array as $key => $value) {
            $empresas[$value['companies_key']] = $value['companies_name'];
        }
        return $empresas;
    }
}
