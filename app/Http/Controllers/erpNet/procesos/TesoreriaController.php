<?php

namespace App\Http\Controllers\erpNet\procesos;

use App\Exports\PROC_TesoreriaExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CAT_MONEY_ACCOUNTS;
use App\Models\catalogos\CAT_PROVIDERS;
use App\Models\catalogos\CONF_CREDIT_CONDITIONS;
use App\Models\catalogos\CONF_FORMS_OF_PAYMENT;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS_CONSECUTIVES;
use App\Models\catalogos\CONF_MODULES_CONCEPT;
use App\Models\catalogos\CONF_MONEY;
use App\Models\modulos\helpers\PROC_MONEY_ACCOUNTS_BALANCE;
use App\Models\modulos\PROC_ACCOUNTS_PAYABLE;
use App\Models\modulos\PROC_ACCOUNTS_PAYABLE_P;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE_P;
use App\Models\modulos\PROC_ASSISTANT;
use App\Models\modulos\PROC_BALANCE;
use App\Models\modulos\PROC_EXPENSES;
use App\Models\modulos\PROC_MOVEMENT_FLOW;
use App\Models\modulos\PROC_SALES;
use App\Models\modulos\PROC_TREASURY;
use App\Models\modulos\PROC_TREASURY_DETAILS;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class TesoreriaController extends Controller
{

    public $estatus = [
        0 => 'INICIAL',
        1 => 'POR AUTORIZAR',
        2 => 'FINALIZADO',
        3 => 'CANCELADO',
    ];


    public $movimientos2 = [
        'Transferencia Electrónica' => 'Transferencia Electrónica',
        'Depósito' => 'Depósito',
        'Sol. de Cheque/Transferencia' => 'Sol. de Cheque/Transferencia',
        'Solicitud Depósito' => 'Solicitud Depósito',
    ];



    public function index()
    {
        $fecha_actual = Carbon::now()->format('Y-m-d');
        $select_users = $this->selectUsuarios();
        $select_sucursales = $this->selectSucursales();
        $select_cuentas = $this->selectCuentasDinero();
        $selectMonedas = $this->getMonedas();
        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }

        $tesoreria = PROC_TREASURY::join('CAT_BRANCH_OFFICES', 'PROC_TREASURY.treasuries_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CAT_COMPANIES', 'PROC_TREASURY.treasuries_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('CONF_FORMS_OF_PAYMENT', 'PROC_TREASURY.treasuries_paymentMethod', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key', 'left outer')
            ->join('CONF_MONEY', 'PROC_TREASURY.treasuries_money', '=', 'CONF_MONEY.money_key')
            ->join('CAT_MONEY_ACCOUNTS', 'PROC_TREASURY.treasuries_moneyAccount', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_key', 'left outer')
            ->join('CAT_PROVIDERS', 'PROC_TREASURY.treasuries_beneficiary', '=', 'CAT_PROVIDERS.providers_key', 'left outer')
            ->join('CAT_CUSTOMERS', 'PROC_TREASURY.treasuries_beneficiary', '=', 'CAT_CUSTOMERS.customers_key', 'left outer')
            ->where('PROC_TREASURY.treasuries_company', '=', session('company')->companies_key)
            ->where('PROC_TREASURY.treasuries_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->where('PROC_TREASURY.treasuries_user', '=', Auth::user()->username)
            ->where('PROC_TREASURY.treasuries_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->orderBy('PROC_TREASURY.updated_at', 'DESC')
            ->get();

        return view('page.modulos.Gestion_y_Finanzas.Tesoreria.index-tesoreria', compact('fecha_actual', 'select_users', 'select_sucursales', 'selectMonedas', 'parametro', 'select_cuentas', 'tesoreria'));
    }

    public function create(Request $request)
    {

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro->generalParameters_defaultMoney == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de seleccionar la moneda por defecto');
        }

        try {
            $usuario = Auth::user();
            $permisos = $usuario->getAllPermissions()->where('categoria', '=', 'Tesorería')->pluck('name')->toArray();

            $movimientos = [];

            if (count($permisos) > 0) {
                foreach ($permisos as $movimiento) {
                    $mov = substr($movimiento, 0, -2);
                    $letra = substr($movimiento, -1);
                    if ($letra === 'E') {
                        if ($mov === "Traspaso Cuentas" || $mov === "Ingreso" || $mov === "Egreso") {
                            if (!array_key_exists($mov, $movimientos)) {
                                $movimientos[$mov] = $mov;
                            }
                        }
                    }
                }
            }

            $selectMonedas = $this->getMonedas2();

            $select_conceptos = CONF_MODULES_CONCEPT::WHERE('moduleConcept_status', '=', 'Alta')->wherein('moduleConcept_module', ['Tesorería', 'Ventas'])->get();
            $select_forma = CONF_FORMS_OF_PAYMENT::WHERE('formsPayment_status', '=', 'Alta')->get();
            $formasPagoArray = $this->formasPago();
            $fecha_actual = Carbon::now()->format('Y-m-d');
            $clientes = CAT_CUSTOMERS::WHERE('customers_status', '=', 'Alta')->get();
            $parametro = CONF_GENERAL_PARAMETERS::join('CONF_MONEY', 'CONF_GENERAL_PARAMETERS.generalParameters_defaultMoney', '=', 'CONF_MONEY.money_key')
                ->select('CONF_GENERAL_PARAMETERS.*', 'CONF_MONEY.money_change')
                ->where('CONF_GENERAL_PARAMETERS.generalParameters_company', '=', session('company')->companies_key)
                ->first();
            $moneyAccounts = CAT_MONEY_ACCOUNTS::join('CAT_FINANCIAL_INSTITUTIONS', 'CAT_FINANCIAL_INSTITUTIONS.instFinancial_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_bank')
                ->where('CAT_MONEY_ACCOUNTS.moneyAccounts_status', '=', 'Alta')
                ->where('CAT_MONEY_ACCOUNTS.moneyAccounts_company', '=', session('company')->companies_key)
                ->get();

            // dd($moneyAccounts);
            $proveedores = CAT_PROVIDERS::WHERE('providers_status', '=', 'Alta')->get();

            // dd($movimientos);

            $select_condicionPago = CONF_CREDIT_CONDITIONS::WHERE('creditConditions_status', '=', 'Alta')->get();
            if (isset($request->id) && $request->id != '') {
                $tesoreria = PROC_TREASURY::find($request->id);

                $movimientos = [];
                if (count($permisos) > 0) {
                    foreach ($permisos as $movimiento) {
                        $mov = substr($movimiento, 0, -2);
                        $letra = substr($movimiento, -1);
                        if ($letra === 'C') {
                            if (!array_key_exists($mov, $movimientos)) {
                                $movimientos[$mov] = $mov;
                            }
                        }
                    }
                }

                if (
                    $tesoreria->treasuries_movement === 'Egreso' || $tesoreria->treasuries_movement === 'Ingreso' ||
                    $tesoreria->treasuries_movement === 'Traspaso Cuentas' ||
                    $tesoreria->treasuries_movement === 'Depósito' ||
                    $tesoreria->treasuries_movement === 'Transferencia Electrónica'
                ) {
                    //Validamos si el usuario tiene permiso de ver los movimientos ya creados
                    if (!$usuario->can($tesoreria->treasuries_movement . ' C')) {
                        return redirect()->route('vista.modulo.tesoreria.index')->with('status', false)->with('message', 'No tiene permisos para visualizar este movimiento');
                    }
                }



                // dd($tesoreria);
                if ($tesoreria->treasuries_movement === "Solicitud Depósito" || $tesoreria->treasuries_movement === "Depósito" || ($tesoreria->treasuries_movement === "Sol. de Cheque/Transferencia" && $tesoreria->treasuries_originType === "CxC") || ($tesoreria->treasuries_movement === "Transferencia Electrónica" && $tesoreria->treasuries_reference === "Devolución de Anticipo")) {
                    $nameProveedor = CAT_CUSTOMERS::where('customers_key', '=', $tesoreria->treasuries_beneficiary)->first();
                } else {
                    $nameProveedor = CAT_PROVIDERS::where('providers_key', '=', $tesoreria->treasuries_beneficiary)->first();
                }

                $primerFlujodeTesoreria = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
                    ->WHERE('movementFlow_originID', '=', $tesoreria->treasuries_id)
                    ->WHERE('movementFlow_movementOriginID', '=', $tesoreria->treasuries_movementID)
                    ->WHERE('movementFlow_moduleOrigin', '=', 'Din')
                    ->get();



                // dd($tesoreria, $primerFlujodeTesoreria);

                if (count($primerFlujodeTesoreria) === 0) {
                    $primerFlujodeTesoreria = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
                        ->WHERE('movementFlow_destinityID', '=', $tesoreria->treasuries_id)
                        ->WHERE('movementFlow_movementDestinityID', '=', $tesoreria->treasuries_movementID)
                        ->WHERE('movementFlow_moduleDestiny', '=', 'Din')
                        ->get();
                }


                if ($tesoreria->treasuries_moneyAccountOrigin != null) {
                    $tipoCuenta2 = CAT_MONEY_ACCOUNTS::where('moneyAccounts_key', '=', $tesoreria->treasuries_moneyAccountOrigin)->first();
                } else {
                    $tipoCuenta2 = null;
                }

                if ($tesoreria->treasuries_moneyAccountDestiny != null) {
                    $tipoCuentaDestino2 = CAT_MONEY_ACCOUNTS::where('moneyAccounts_key', '=', $tesoreria->treasuries_moneyAccountDestiny)->first();
                } else {
                    $tipoCuentaDestino2 = null;
                }

                $monedasMov = CONF_MONEY::where('money_status', '=', 'Alta')->orderBy('money_key', 'desc')->get();

                $infoCuentas = PROC_MONEY_ACCOUNTS_BALANCE::join('CAT_COMPANIES', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_company', '=', 'CAT_COMPANIES.companies_key')->where('moneyAccountsBalance_company', '=', session('company')->companies_key)->get();




                if ($tesoreria->treasuries_movement == 'Ingreso' || $tesoreria->treasuries_movement == 'Traspaso Cuentas') {
                    $tipoCuenta = CAT_MONEY_ACCOUNTS::WHERE('moneyAccounts_key', '=', $tesoreria->treasuries_moneyAccountOrigin)->where('moneyAccounts_company', '=', session('company')->companies_key)->select('moneyAccounts_accountType')->first();

                    $tipoCuentaD = CAT_MONEY_ACCOUNTS::WHERE('moneyAccounts_key', '=', $tesoreria->treasuries_moneyAccountDestiny)->where('moneyAccounts_company', '=', session('company')->companies_key)->select('moneyAccounts_accountType')->first();
                } else {
                    $tipoCuenta = CAT_MONEY_ACCOUNTS::WHERE('moneyAccounts_key', '=', $tesoreria->treasuries_moneyAccount)->where('moneyAccounts_company', '=', session('company')->companies_key)->select('moneyAccounts_accountType')->first();
                }






                switch ($tesoreria->treasuries_movement) {
                    case 'Sol. de Cheque/Transferencia':
                        $select_conceptos = CONF_MODULES_CONCEPT::WHERE('moduleConcept_status', '=', 'Alta')->get();
                        $movimientos = array_merge($movimientos, $this->movimientos2);
                        return view('page.modulos.Gestion_y_Finanzas.Tesoreria.create-tesoreria', compact('fecha_actual', 'select_conceptos', 'select_forma', 'selectMonedas', 'clientes', 'parametro', 'moneyAccounts', 'proveedores', 'select_condicionPago', 'movimientos', 'tesoreria', 'tipoCuenta', 'nameProveedor', 'primerFlujodeTesoreria', 'monedasMov', 'infoCuentas'));
                        break;

                    case 'Solicitud Depósito':
                        $select_conceptos = CONF_MODULES_CONCEPT::WHERE('moduleConcept_status', '=', 'Alta')->get();
                        $movimientos = array_merge($movimientos, $this->movimientos2);
                        return view('page.modulos.Gestion_y_Finanzas.Tesoreria.create-tesoreria', compact('fecha_actual', 'select_conceptos', 'select_forma', 'selectMonedas', 'clientes', 'parametro', 'moneyAccounts', 'proveedores', 'select_condicionPago', 'movimientos', 'tesoreria', 'tipoCuenta', 'nameProveedor', 'primerFlujodeTesoreria', 'monedasMov', 'infoCuentas'));
                        break;

                    case 'Transferencia Electrónica':
                        // $select_conceptos = CONF_MODULES_CONCEPT::WHERE('moduleConcept_status', '=', 'Alta')->get();
                        $origenCheque = PROC_TREASURY::WHERE('treasuries_movementID', '=', $tesoreria->treasuries_originID)->WHERE('treasuries_company', '=', $tesoreria->treasuries_company)->WHERE('treasuries_branchOffice', '=', $tesoreria->treasuries_branchOffice)->WHEREIN('treasuries_originType', ['CxC', 'CxP', 'CxC'])->WHERE('treasuries_movement', '=', $tesoreria->treasuries_origin)->first();

                        $tipoCuenta2 = CAT_MONEY_ACCOUNTS::where('moneyAccounts_key', '=', $tesoreria->treasuries_moneyAccount)->first();

                        $movimientos = array_merge($movimientos, $this->movimientos2);
                        return view('page.modulos.Gestion_y_Finanzas.Tesoreria.create-tesoreria', compact('fecha_actual', 'select_conceptos', 'select_forma', 'selectMonedas', 'clientes', 'parametro', 'moneyAccounts', 'proveedores', 'select_condicionPago', 'movimientos', 'tesoreria', 'tipoCuenta', 'origenCheque', 'formasPagoArray', 'nameProveedor', 'primerFlujodeTesoreria', 'tipoCuenta2', 'monedasMov', 'infoCuentas'));
                        break;

                    case 'Depósito':

                        $movimientos = array_merge($movimientos, $this->movimientos2);
                        $origenCheque = PROC_TREASURY::WHERE('treasuries_movementID', '=', $tesoreria->treasuries_originID)->WHERE('treasuries_company', '=', $tesoreria->treasuries_company)->WHERE('treasuries_branchOffice', '=', $tesoreria->treasuries_branchOffice)->WHERE('treasuries_originType', '=', 'CxC')->WHERE('treasuries_movement', '=', $tesoreria->treasuries_origin)->first();

                        $tipoCuenta2 = CAT_MONEY_ACCOUNTS::where('moneyAccounts_key', '=', $tesoreria->treasuries_moneyAccount)->first();


                        $movimientos = array_merge($movimientos, $this->movimientos2);
                        return view('page.modulos.Gestion_y_Finanzas.Tesoreria.create-tesoreria', compact('fecha_actual', 'select_conceptos', 'select_forma', 'selectMonedas', 'clientes', 'parametro', 'moneyAccounts', 'proveedores', 'select_condicionPago', 'movimientos', 'tesoreria', 'tipoCuenta', 'origenCheque', 'formasPagoArray', 'nameProveedor', 'primerFlujodeTesoreria', 'tipoCuenta2', 'monedasMov', 'infoCuentas'));
                        break;

                    case 'Ingreso':
                        return view('page.modulos.Gestion_y_Finanzas.Tesoreria.create-tesoreria', compact('fecha_actual', 'select_conceptos', 'select_forma', 'selectMonedas', 'clientes', 'parametro', 'moneyAccounts', 'proveedores', 'select_condicionPago', 'movimientos', 'tesoreria', 'tipoCuenta', 'tipoCuentaD', 'nameProveedor', 'primerFlujodeTesoreria', 'tipoCuenta2', 'tipoCuentaDestino2', 'monedasMov', 'infoCuentas'));
                        break;

                    case 'Egreso':
                        $tipoCuenta2 = CAT_MONEY_ACCOUNTS::where('moneyAccounts_key', '=', $tesoreria->treasuries_moneyAccount)->first();
                        return view('page.modulos.Gestion_y_Finanzas.Tesoreria.create-tesoreria', compact('movimientos', 'selectMonedas', 'parametro', 'fecha_actual', 'select_conceptos', 'select_forma',  'moneyAccounts', 'proveedores', 'tesoreria', 'monedasMov', 'infoCuentas', 'tipoCuenta', 'tipoCuenta2', 'primerFlujodeTesoreria'));
                        break;

                    case 'Traspaso Cuentas':
                        return view('page.modulos.Gestion_y_Finanzas.Tesoreria.create-tesoreria', compact('fecha_actual', 'select_conceptos', 'select_forma', 'selectMonedas', 'clientes', 'parametro', 'moneyAccounts', 'proveedores', 'select_condicionPago', 'movimientos', 'tesoreria', 'tipoCuenta', 'tipoCuentaD', 'nameProveedor', 'primerFlujodeTesoreria', 'tipoCuentaDestino2', 'tipoCuenta2', 'monedasMov', 'infoCuentas'));
                        break;

                    default:
                        return view('page.modulos.Gestion_y_Finanzas.Tesoreria.create-tesoreria', compact('fecha_actual', 'select_conceptos', 'select_forma', 'selectMonedas', 'clientes', 'parametro', 'moneyAccounts', 'proveedores', 'select_condicionPago', 'movimientos', 'tesoreria', 'nameProveedor', 'primerFlujodeTesoreria', 'tipoCuenta', '$tipoCuenta2', 'tipoCuentaD', 'tipoCuentaDestino2', 'monedasMov', 'infoCuentas'));
                        break;
                }

                return view('page.modulos.Gestion_y_Finanzas.Tesoreria.create-tesoreria', compact('fecha_actual', 'select_conceptos', 'select_forma', 'selectMonedas', 'clientes', 'parametro', 'moneyAccounts', 'proveedores', 'select_condicionPago', 'movimientos', 'tesoreria', 'nameProveedor', 'primerFlujodeTesoreria'));
            }

            return view('page.modulos.Gestion_y_Finanzas.Tesoreria.create-tesoreria', compact('fecha_actual', 'select_conceptos', 'select_forma', 'selectMonedas', 'clientes', 'parametro', 'moneyAccounts', 'proveedores', 'select_condicionPago', 'movimientos'));
        } catch (\Exception $e) {
            // dd($e);
            return redirect()->route('vista.modulo.tesoreria.index')->with('status', false)->with('message', 'El movimiento no se ha encontrado');
        }
    }

    public function store(Request $request)
    {
        //   dd($request->all());
        try {
            $id = $request->id;
            $copiaRequest = $request->copiar;


            // dd($request->all());
            if ($id == 0 || $copiaRequest  == 'copiar') {
                $tesoreria = new PROC_TREASURY;
            } else {
                $tesoreria = PROC_TREASURY::where('treasuries_id', '=', $id)->first();
            }

            if ($request->origin === 'Transferencia Electrónica' || $request->movimientos === 'Transferencia Electrónica' || $request->movimientos === "Ingreso" || $request->movimientos === "Egreso" || $request->movimientos === "Traspaso Cuentas" || $request->movimientos === "Depósito" || $request->origin === "Depósito") {
                $tesoreria->treasuries_movement = $request->origin !== null ? $request->origin : $request->movimientos;
                $tesoreria->treasuries_issuedate
                    = \Carbon\Carbon::now();

                $tesoreria->treasuries_money = $request->nameMoneda;
                $tesoreria->treasuries_typeChange = $request->nameTipoCambio;

                if ($request->movimientos === 'Transferencia Electrónica') {
                    $tesoreria->treasuries_moneyAccount = $request->cuentaKey;
                    $tesoreria->treasuries_concept = $request->concepto;
                }

                if ($request->movimientos === 'Depósito') {
                    $tesoreria->treasuries_moneyAccount = $request->cuentaKey;
                    $tesoreria->treasuries_concept = $request->concepto;
                }

                if ($request->movimientos === "Egreso") {
                    $tesoreria->treasuries_moneyAccount = $request->cuentaTrans;
                    $tesoreria->treasuries_concept = $request->concepto;
                }

                if ($request->movimientos === 'Ingreso') {
                    $tesoreria->treasuries_moneyAccountOrigin = $request->cuentaTrans;
                    $tesoreria->treasuries_concept = $request->concepto;
                }

                if ($request->movimientos === 'Traspaso Cuentas') {
                    $tesoreria->treasuries_moneyAccountOrigin = $request->cuentaTrans;
                    $tesoreria->treasuries_moneyAccountDestiny = $request->cuentaDKey;
                    $tesoreria->treasuries_concept = $request->concepto;
                }


                $tesoreria->treasuries_paymentMethod = $request->formaPago;
                $tesoreria->treasuries_beneficiary = $request->beneficiario;
                $tesoreria->treasuries_reference = $request->proveedorReferencia;
                $tesoreria->treasuries_amount = str_replace(['$', ','], '', $request->importe);
                $tesoreria->treasuries_total = str_replace(['$', ','], '', $request->importe);
                $tesoreria->treasuries_accountBalance = str_replace(['$', ','], '', $request->importe);
                $tesoreria->treasuries_observations = $request->observaciones;
                $tesoreria->treasuries_company = session('company')->companies_key;
                $tesoreria->treasuries_branchOffice = session('sucursal')->branchOffices_key;
                $tesoreria->treasuries_user =  Auth::user()->username;
                $tesoreria->treasuries_status = $this->estatus[0];

                if ($request->movimientos === 'Sol. de Cheque/Transferencia') {
                    $tesoreria->treasuries_moneyAccount = $request->cuentaKey;
                    $tesoreria->treasuries_originType = 'Din';
                    $tesoreria->treasuries_origin = $request->movimientos;
                    $tesoreria->treasuries_originID = $request->folio;
                    $tesoreria->treasuries_concept = $request->concepto;
                }

                if ($request->movimientos === 'Solicitud Depósito') {
                    $tesoreria->treasuries_moneyAccount = $request->cuentaKey;
                    $tesoreria->treasuries_originType = 'Din';
                    $tesoreria->treasuries_origin = $request->movimientos;
                    $tesoreria->treasuries_originID = $request->folio;
                    $tesoreria->treasuries_concept = $request->concepto;
                }
            }

            $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');

            $isCreate = $tesoreria->save();
            $lastId = $id == 0 ?  $tesoreria::latest()->first()->treasuries_id : $tesoreria->treasuries_id;

            
            if ($request->movimientos === 'Sol. de Cheque/Transferencia') {
                $isVenta = false;
                $detalle = new PROC_TREASURY_DETAILS();

                $origenSolicitud = PROC_TREASURY::WHERE('treasuries_company', '=', $tesoreria->treasuries_company)->WHERE('treasuries_branchOffice', '=', $tesoreria->treasuries_branchOffice)->WHERE('treasuries_movement', '=', $tesoreria->treasuries_origin)->WHERE('treasuries_movementID', '=', $tesoreria->treasuries_originID)->first();
                // dd($origenSolicitud);



                if ($origenSolicitud->treasuries_origin !== "Reposición Caja" && $origenSolicitud->treasuries_origin !== "Devolución de Anticipo") {
                    $origen = PROC_ACCOUNTS_PAYABLE::WHERE('accountsPayable_company', '=', $origenSolicitud->treasuries_company)->WHERE('accountsPayable_branchOffice', '=', $origenSolicitud->treasuries_branchOffice)->WHERE('accountsPayable_movement', '=', $origenSolicitud->treasuries_origin)->WHERE('accountsPayable_movementID', '=', $origenSolicitud->treasuries_originID)->first();
                    // dd($origen);
                    $isVenta = false;
                } else if ($origenSolicitud->treasuries_origin === "Devolución de Anticipo") {
                    $origen = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_company', '=', $origenSolicitud->treasuries_company)->WHERE('accountsReceivable_branchOffice', '=', $origenSolicitud->treasuries_branchOffice)->WHERE('accountsReceivable_movement', '=', $origenSolicitud->treasuries_origin)->WHERE('accountsReceivable_movementID', '=', $origenSolicitud->treasuries_originID)->first();
                    // dd($origen);
                    $isVenta = false;
                } else {
                    $origen = PROC_EXPENSES::WHERE('expenses_company', '=', $origenSolicitud->treasuries_company)->WHERE('expenses_branchOffice', '=', $origenSolicitud->treasuries_branchOffice)->WHERE('expenses_movement', '=', $origenSolicitud->treasuries_origin)->WHERE('expenses_movementID', '=', $origenSolicitud->treasuries_originID)->first();
                    $isVenta = true;
                }


                $detalle->treasuriesDetails_treasuriesID = $tesoreria->treasuries_id;
                $detalle->treasuriesDetails_amount = str_replace(['$', ','], '', $request->importe);
                $detalle->treasuriesDetails_apply = $request->movimientos;
                $detalle->treasuriesDetails_applyIncrement = $request->folio;
                $detalle->treasuriesDetails_paymentMethod = $request->formaPago;
                if ($isVenta) {
                    $detalle->treasuriesDetails_movReference = $origen->expenses_id;
                } else {
                    if ($origenSolicitud->treasuries_origin === "Devolución de Anticipo") {
                        // dd("entroooo");
                        $detalle->treasuriesDetails_movReference = $origen->accountsReceivable_id;
                    } else {
                        $detalle->treasuriesDetails_movReference = $origen->accountsPayable_id;
                    }
                }
                $detalle->save();
            }

            if ($request->movimientos === 'Solicitud Depósito') {
                $isVenta = false;
                $detalle = new PROC_TREASURY_DETAILS();

                $origenSolicitud = PROC_TREASURY::WHERE('treasuries_company', '=', $tesoreria->treasuries_company)->WHERE('treasuries_branchOffice', '=', $tesoreria->treasuries_branchOffice)->WHERE('treasuries_movement', '=', $tesoreria->treasuries_origin)->WHERE('treasuries_movementID', '=', $tesoreria->treasuries_originID)->first();

                if ($origenSolicitud->treasuries_origin !== "Factura") {
                    $origen = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_company', '=', $origenSolicitud->treasuries_company)->WHERE('accountsReceivable_branchOffice', '=', $origenSolicitud->treasuries_branchOffice)->WHERE('accountsReceivable_movement', '=', $origenSolicitud->treasuries_origin)->WHERE('accountsReceivable_movementID', '=', $origenSolicitud->treasuries_originID)->first();
                    $isVenta = false;
                } else {
                    $origen = PROC_SALES::WHERE('sales_company', '=', $origenSolicitud->treasuries_company)->WHERE('sales_branchOffice', '=', $origenSolicitud->treasuries_branchOffice)->WHERE('sales_movement', '=', $origenSolicitud->treasuries_origin)->WHERE('sales_movementID', '=', $origenSolicitud->treasuries_originID)->first();
                    $isVenta = true;
                }

                $detalle->treasuriesDetails_treasuriesID = $tesoreria->treasuries_id;
                $detalle->treasuriesDetails_amount = str_replace(['$', ','], '', $request->importe);
                $detalle->treasuriesDetails_apply = $request->movimientos;
                $detalle->treasuriesDetails_applyIncrement = $request->folio;
                $detalle->treasuriesDetails_paymentMethod = $request->formaPago;

                if ($isVenta) {
                    $detalle->treasuriesDetails_movReference = $origen->sales_id;
                } else {
                    $detalle->treasuriesDetails_movReference = $origen->accountsReceivable_id;
                }

                $detalle->save();
            }

            if ($isCreate) {
                if($request->movimientos === 'Sol. de Cheque/Transferencia' && $request->origin === 'Transferencia Electrónica'){
                    $message = 'La ' . $request->origin . ' se ha creado correctamente';
                } else if($request->movimientos === 'Solicitud Depósito' && $request->origin === 'Depósito'){
                    $message = 'El ' . $request->origin . ' se ha creado correctamente';
                } else {
                    $message = 'El ' . $request->movimientos . ' se ha creado correctamente';
                }
                $status = true;
            } else {
                $message = 'Ocurrio un error al crear';
                $status = false;
            }
        } catch (\Exception $e) {
            dd($e);
            $message = 'Por favor, vaya con el administrador de sistemas, no se pudo crear el movimiento';
            $status = false;
            return redirect()->route('vista.modulo.tesoreria.create-tesoreria')->with('message', $message)->with('status', false);
        }

        return redirect()->route('vista.modulo.tesoreria.create-tesoreria', $lastId)->with('message', $message)->with('status', $status);
    }

    public function afectar(Request $request)
    {

        // dd($request->all());
        try {
            $id = $request->id;
            $isSaldoSuficiente = false;
            $cuentaAvalidar = $request->movimientos === 'Ingreso' ||  $request->movimientos === 'Egreso'  ||  $request->movimientos === 'Traspaso Cuentas' ? $request->cuentaTrans : $request->cuentaKey;


            if ($request->movimientos == 'Ingreso' || $request->movimientos == 'Egreso' || $request->movimientos == 'Traspaso Cuentas') {
                //verificar que la forma de pago sea de la misma que el movimiento
                $formaPago = CONF_FORMS_OF_PAYMENT::WHERE('formsPayment_key', '=', $request['formaPago'])->first();

                $moneda = CONF_MONEY::WHERE('money_key', '=', $request['nameMoneda'])->first();
                // DD(trim($formaPago->formsPayment_money), $moneda->money_keySat);

                if (trim($formaPago->formsPayment_money) != $moneda->money_keySat) {
                    $message = 'La forma de pago no es compatible con la moneda del movimiento';
                    $status = false;
                    $lastId = $request['id'];

                    return response()->json(['status' => $status, 'mensaje' => $message, 'id' =>  $lastId]);
                }
            }

            if ($request->movimientos === 'Transferencia Electrónica') {

                $cheque = PROC_TREASURY::find($request->id);


                $solicitud = PROC_TREASURY::where('treasuries_movement', '=', $cheque->treasuries_origin)->where('treasuries_movementID', '=',  $cheque->treasuries_originID)->where('treasuries_company', '=', $cheque->treasuries_company)->where('treasuries_branchOffice', '=', $cheque->treasuries_branchOffice)->first();

                //verificar que la solicitud de cheque sea de la misma moneda que la cuenta de la que se esta sacando el dinero
                $detalle = PROC_TREASURY_DETAILS::where('treasuriesDetails_treasuriesID', '=', $request->id)->first();
                // dd($solicitud, $detalle);

                if ($solicitud->treasuries_origin != 'Reposición Caja' && $solicitud->treasuries_origin != 'Devolución de Anticipo') {
                    $cxp = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $detalle->treasuriesDetails_movReference)->first();

                    if ($cxp->accountsPayable_money != $request->nameMoneda) {
                        $message = 'La solicitud de cheque no es de la misma moneda que la cuenta de la que se esta sacando el dinero';
                        $status = false;
                        $lastId = 0;
                        return response()->json(['status' => $status, 'mensaje' => $message, 'id' =>  $lastId]);
                    }
                } else if ($solicitud->treasuries_origin === 'Devolución de Anticipo') {
                    $cxp = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $detalle->treasuriesDetails_movReference)->first();

                    if ($cxp->accountsReceivable_money != $request->nameMoneda) {
                        $message = 'La solicitud de cheque no es de la misma moneda que la cuenta de la que se esta sacando el dinero';
                        $status = false;
                        $lastId = 0;
                        return response()->json(['status' => $status, 'mensaje' => $message, 'id' =>  $lastId]);
                    }
                } else {
                    $gasto = PROC_EXPENSES::where('expenses_id', '=', $detalle->treasuriesDetails_movReference)->first();

                    if ($gasto->expenses_money != $request->nameMoneda) {
                        $message = 'La solicitud de cheque no es de la misma moneda que la cuenta de la que se esta sacando el dinero';
                        $status = false;
                        $lastId = 0;
                        return response()->json(['status' => $status, 'mensaje' => $message, 'id' =>  $lastId]);
                    }
                }
            }


            if ($request->movimientos === 'Depósito') {

                $cheque = PROC_TREASURY::find($request->id);


                $solicitud = PROC_TREASURY::where('treasuries_movement', '=', $cheque->treasuries_origin)->where('treasuries_movementID', '=',  $cheque->treasuries_originID)->where('treasuries_company', '=', $cheque->treasuries_company)->where('treasuries_branchOffice', '=', $cheque->treasuries_branchOffice)->first();

                //verificar que la solicitud de cheque sea de la misma moneda que la cuenta de la que se esta sacando el dinero
                $detalle = PROC_TREASURY_DETAILS::where('treasuriesDetails_treasuriesID', '=', $request->id)->first();

                if ($solicitud->treasuries_origin != 'Factura') {
                    $cxp = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $detalle->treasuriesDetails_movReference)->first();

                    if ($cxp->accountsReceivable_money != $request->nameMoneda) {
                        $message = 'La solicitud de Depósito no es de la misma moneda que la cuenta de la que se esta metiendo el dinero';
                        $status = false;
                        $lastId = 0;
                        return response()->json(['status' => $status, 'mensaje' => $message, 'id' =>  $lastId]);
                    }
                } else {
                    $gasto = PROC_SALES::where('sales_id', '=', $detalle->treasuriesDetails_movReference)->first();

                    if ($gasto->sales_money != $request->nameMoneda) {
                        $message = 'La solicitud de Depósito no es de la misma moneda que la cuenta de la que se esta metiendo el dinero';
                        $status = false;
                        $lastId = 0;
                        return response()->json(['status' => $status, 'mensaje' => $message, 'id' =>  $lastId]);
                    }
                }
            }

            if ($request->movimientos === 'Ingreso' || $request->movimientos === 'Depósito') {
                $isSaldoSuficiente = true;
            } else {
                $validarCuenta = PROC_MONEY_ACCOUNTS_BALANCE::WHERE('moneyAccountsBalance_moneyAccount', '=',  $cuentaAvalidar)->where('moneyAccountsBalance_status', '=', 'Alta')->WHERE('moneyAccountsBalance_company', '=', session('company')->companies_key)->first();
                //Verificamos q la cuenta tenga saldo suficiente para afectar
                $saldo = $validarCuenta->moneyAccountsBalance_balance;
                $fondo = floatval($saldo);
                $importa_sinSigno = trim($request->importe, '$');
                $importe_sinSigno = str_replace(',', '', $importa_sinSigno);
                $importe = floatval($importe_sinSigno);

                if ($fondo < $importe && $request->movimientos !== 'Ingreso') {
                    $message = 'La cuenta no tiene fondos suficientes y no se puede sobregirar';
                    $status = false;
                    $isSaldoSuficiente = false;
                    $lastId = 0;
                } else {
                    $isSaldoSuficiente = true;
                }
            }


            if ($isSaldoSuficiente) {
                if ($id == 0) {
                    $tesoreria = new PROC_TREASURY;
                } else {
                    $tesoreria = PROC_TREASURY::where('treasuries_id', '=', $id)->first();
                }

                if ($request->origin === 'Transferencia Electrónica' || $request->movimientos === 'Transferencia Electrónica' || $request->movimientos === "Ingreso" || $request->movimientos === "Egreso" || $request->movimientos === "Traspaso Cuentas" || $request->movimientos === "Depósito") {
                    $tesoreria->treasuries_movement = $request->origin !== null ? $request->origin : $request->movimientos;
                    $tesoreria->treasuries_issuedate
                        = \Carbon\Carbon::now();
                    $tesoreria->treasuries_concept = $request->concepto;

                    $tesoreria->treasuries_money = $request->nameMoneda;
                    $tesoreria->treasuries_typeChange = $request->nameTipoCambio;

                    if ($request->movimientos === 'Transferencia Electrónica') {
                        $tesoreria->treasuries_moneyAccount = $request->cuentaKey;
                    }

                    if ($request->movimientos === 'Depósito') {
                        $tesoreria->treasuries_moneyAccount = $request->cuentaKey;
                    }

                    if ($request->movimientos === "Egreso") {
                        $tesoreria->treasuries_moneyAccount = $request->cuentaTrans;
                    }

                    if ($request->movimientos === 'Ingreso') {
                        $tesoreria->treasuries_moneyAccountOrigin = $request->cuentaTrans;
                    }

                    if ($request->movimientos === 'Traspaso Cuentas') {
                        $tesoreria->treasuries_moneyAccountOrigin = $request->cuentaTrans;
                        $tesoreria->treasuries_moneyAccountDestiny = $request->cuentaDKey;
                    }

                    $tesoreria->treasuries_paymentMethod = $request->formaPago;
                    $tesoreria->treasuries_beneficiary = $request->beneficiario;
                    $tesoreria->treasuries_reference = $request->proveedorReferencia;
                    $tesoreria->treasuries_amount = str_replace(['$', ','], '', $request->importe);
                    $tesoreria->treasuries_total = str_replace(['$', ','], '', $request->importe);
                    $tesoreria->treasuries_accountBalance = str_replace(['$', ','], '', $request->importe);
                    $tesoreria->treasuries_observations = $request->observaciones;
                    $tesoreria->treasuries_company = session('company')->companies_key;
                    $tesoreria->treasuries_branchOffice = session('sucursal')->branchOffices_key;
                    $tesoreria->treasuries_user =  Auth::user()->username;

                    if ($request->movimientos === 'Sol. de Cheque/Transferencia') {
                        $tesoreria->treasuries_moneyAccount = $request->cuentaKey;
                        $tesoreria->treasuries_originType = 'Din';
                        $tesoreria->treasuries_origin = $request->movimientos;
                        $tesoreria->treasuries_originID = $request->folio;
                    }

                    if ($request->movimientos === 'Solicitud Depósito') {
                        $tesoreria->treasuries_moneyAccount = $request->cuentaKey;
                        $tesoreria->treasuries_originType = 'Din';
                        $tesoreria->treasuries_origin = $request->movimientos;
                        $tesoreria->treasuries_originID = $request->folio;
                    }
                    switch ($request->movimientos) {
                        case 'Transferencia Electrónica':
                            $tesoreria->treasuries_status = $this->estatus[2];
                            break;

                        case 'Ingreso':
                            $tesoreria->treasuries_status = $this->estatus[2];
                            break;

                        case 'Egreso':
                            $tesoreria->treasuries_status = $this->estatus[2];
                            break;

                        case 'Depósito':
                            $tesoreria->treasuries_status = $this->estatus[2];
                            break;

                        case 'Traspaso Cuentas':
                            $tesoreria->treasuries_status = $this->estatus[2];
                            break;

                        default:
                            # code...
                            break;
                    }
                }

                $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
                $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');

                $isCreate = $tesoreria->save();
                $lastId = $id == 0 ?  $tesoreria::latest()->first()->treasuries_id : $tesoreria->treasuries_id;


                $folioAfectar = PROC_TREASURY::where('treasuries_id', '=', $lastId)->first();


                $this->actualizarFolio($request->movimientos, $folioAfectar);


                //Asignamos un folio al cheque
                // switch ($request->movimientos) {
                //     case 'Transferencia Electrónica':
                //         $folioMov = PROC_TREASURY::WHERE('treasuries_movement', '=', 'Transferencia Electrónica')->WHERE('treasuries_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->max('treasuries_movementID');

                //         $folioMov = $folioMov == null ? 1 : $folioMov + 1;
                //         $folioAfectar->treasuries_movementID = $folioMov;
                //         $folioAfectar->update();
                //         break;

                //     case 'Depósito':
                //         $folioMov = PROC_TREASURY::WHERE('treasuries_movement', '=', 'Depósito')->WHERE('treasuries_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->max('treasuries_movementID');

                //         $folioMov = $folioMov == null ? 1 : $folioMov + 1;
                //         $folioAfectar->treasuries_movementID = $folioMov;
                //         $folioAfectar->update();
                //         break;

                //     case 'Ingreso':
                //         $folioMov = PROC_TREASURY::WHERE('treasuries_movement', '=', 'Ingreso')->WHERE('treasuries_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->max('treasuries_movementID');

                //         $folioMov = $folioMov == null ? 1 : $folioMov + 1;
                //         $folioAfectar->treasuries_movementID = $folioMov;
                //         $folioAfectar->update();
                //         break;

                //     case 'Egreso':
                //         $folioMov = PROC_TREASURY::WHERE('treasuries_movement', '=', 'Egreso')->WHERE('treasuries_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->max('treasuries_movementID');

                //         $folioMov = $folioMov == null ? 1 : $folioMov + 1;
                //         $folioAfectar->treasuries_movementID = $folioMov;
                //         $folioAfectar->update();
                //         break;

                //     case 'Traspaso Cuentas':
                //         $folioMov = PROC_TREASURY::WHERE('treasuries_movement', '=', 'Traspaso Cuentas')->WHERE('treasuries_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->max('treasuries_movementID');

                //         $folioMov = $folioMov == null ? 1 : $folioMov + 1;
                //         $folioAfectar->treasuries_movementID = $folioMov;
                //         $folioAfectar->update();
                //         break;

                //     default:
                //         # code...
                //         break;
                // }

                switch ($request->movimientos) {
                    case 'Transferencia Electrónica':
                        //Modulo tesoreria
                        $this->solicitudChequeConcluido($folioAfectar->treasuries_id);

                        //Asignamos el anticipo al proveedor
                        $this->saldo($folioAfectar->treasuries_id);

                        //Restamos a la cuenta el saldoActual menos el total del cheque
                        $this->nuevoSaldoCuenta($folioAfectar->treasuries_id);

                        //Agremos la información a la tabla auxiliar
                        $this->auxiliar($folioAfectar->treasuries_id);
                        //Registramos el movimiento del cheque
                        $this->nuevoMovimiento($folioAfectar->treasuries_id);
                        break;

                    case 'Depósito':
                        //Modulo tesoreria
                        $this->solicitudChequeConcluido($folioAfectar->treasuries_id);
                        //Asignamos el anticipo al proveedor
                        $this->saldo($folioAfectar->treasuries_id);
                        //Restamos a la cuenta el saldoActual menos el total del cheque
                        $this->nuevoSaldoCuenta($folioAfectar->treasuries_id);
                        //Agremos la información a la tabla auxiliar
                        $this->auxiliar($folioAfectar->treasuries_id);
                        //Registramos el movimiento del cheque
                        $this->nuevoMovimiento($folioAfectar->treasuries_id);
                        break;
                    case 'Ingreso':
                        $primerIngreso = $this->nuevoSaldoCuenta($folioAfectar->treasuries_id);
                        $this->auxiliarIngreso($folioAfectar->treasuries_id, $primerIngreso);

                        //  $this->ingresoTipoCaja($folioAfectar->treasuries_id);
                        //  $this->auxiliarTipoCaja($folioAfectar->treasuries_id);
                        break;

                    case 'Egreso':
                        $this->nuevoSaldoCuenta($folioAfectar->treasuries_id);
                        $this->auxiliarEgreso($folioAfectar->treasuries_id);
                        break;

                    case 'Traspaso Cuentas':
                        $primerIngreso = $this->nuevoSaldoCuenta($folioAfectar->treasuries_id);
                        $this->auxiliarTransferencia($folioAfectar->treasuries_id, $primerIngreso);
                        break;

                    default:
                        break;
                }

                if ($isCreate) {
                    if ($request->movimientos === 'Transferencia Electrónica') {
                        $message = 'La ' . $request->movimientos . ' se ha creado correctamente';
                    } else if ($request->movimientos === 'Depósito') {
                        $message = 'El ' . $request->movimientos . ' se ha creado correctamente';
                    } else {
                        $message = 'El ' . $request->movimientos . ' se ha creado correctamente';
                    }
                    $status = true;
                } else {
                    $message = 'Ocurrio un error al crear';
                    $status = false;
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage() . '-' . $e->getLine();
            $status = false;
            $lastId = 0;
        }

        return response()->json(['status' => $status, 'mensaje' => $message, 'id' =>  $lastId]);
    }

    public function auxiliar($folio)
    {
        $folioAfectar = PROC_TREASURY::where('treasuries_id', $folio)->first();

        if ($folioAfectar->treasuries_movement === 'Transferencia Electrónica' && $folioAfectar->treasuries_status === "FINALIZADO") {
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->treasuries_company;
            $auxiliar->assistant_branchKey = $folioAfectar->treasuries_branchOffice;
            $auxiliar->assistant_branch = 'CxP';

            $auxiliar->assistant_movement = $folioAfectar->treasuries_movement;
            $auxiliar->assistant_movementID = $folioAfectar->treasuries_movementID;
            $auxiliar->assistant_module = 'Din';
            $auxiliar->assistant_moduleID = $folioAfectar->treasuries_id;
            $auxiliar->assistant_money = $folioAfectar->treasuries_money;
            $auxiliar->assistant_typeChange = $folioAfectar->treasuries_typeChange;
            $auxiliar->assistant_account = $folioAfectar->treasuries_beneficiary;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = null;
            $auxiliar->assistant_payment = $folioAfectar->treasuries_total;
            $auxiliar->assistant_apply = $folioAfectar->treasuries_origin;
            $auxiliar->assistant_applyID =  $folioAfectar->treasuries_originID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->treasuries_reference;

            $auxiliar->save();

            $auxiliar2 = new PROC_ASSISTANT();

            $auxiliar2->assistant_companieKey = $folioAfectar->treasuries_company;
            $auxiliar2->assistant_branchKey = $folioAfectar->treasuries_branchOffice;
            $auxiliar2->assistant_branch = 'Din';

            $auxiliar2->assistant_movement = $folioAfectar->treasuries_movement;
            $auxiliar2->assistant_movementID = $folioAfectar->treasuries_movementID;
            $auxiliar2->assistant_module = 'Din';
            $auxiliar2->assistant_moduleID = $folioAfectar->treasuries_id;
            $auxiliar2->assistant_money = $folioAfectar->treasuries_money;
            $auxiliar2->assistant_typeChange = $folioAfectar->treasuries_typeChange;
            $auxiliar2->assistant_account = $folioAfectar->treasuries_moneyAccount;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar2->assistant_year = $year;
            $auxiliar2->assistant_period = $period;
            $auxiliar2->assistant_charge = null;
            $auxiliar2->assistant_payment = $folioAfectar->treasuries_total;
            $auxiliar2->assistant_apply = $folioAfectar->treasuries_movement;
            $auxiliar2->assistant_applyID =  $folioAfectar->treasuries_movementID;
            $auxiliar2->assistant_canceled = 0;
            $auxiliar2->assistant_reference = $folioAfectar->treasuries_reference;

            $auxiliar2->save();
        }

        if ($folioAfectar->treasuries_movement === 'Depósito' && $folioAfectar->treasuries_status === "FINALIZADO") {


            //Encontramos la solicitud de deposito en tesoreria
            $solicitudDeposito = PROC_TREASURY::WHERE('treasuries_movement', '=', $folioAfectar->treasuries_origin)->WHERE('treasuries_movementID', '=', $folioAfectar->treasuries_originID)->WHERE('treasuries_company', '=', $folioAfectar->treasuries_company)->where("treasuries_branchOffice", '=', $folioAfectar->treasuries_branchOffice)->first();

            if ($solicitudDeposito->treasuries_origin !== "Factura") {

                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $folioAfectar->treasuries_company;
                $auxiliar->assistant_branchKey = $folioAfectar->treasuries_branchOffice;
                $auxiliar->assistant_branch = 'CxC';

                $auxiliar->assistant_movement = $folioAfectar->treasuries_movement;
                $auxiliar->assistant_movementID = $folioAfectar->treasuries_movementID;
                $auxiliar->assistant_module = 'Din';
                $auxiliar->assistant_moduleID = $folioAfectar->treasuries_id;
                $auxiliar->assistant_money = $folioAfectar->treasuries_money;
                $auxiliar->assistant_typeChange = $folioAfectar->treasuries_typeChange;
                $auxiliar->assistant_account = $folioAfectar->treasuries_beneficiary;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = null;
                $auxiliar->assistant_payment = $folioAfectar->treasuries_total;
                $auxiliar->assistant_apply = $folioAfectar->treasuries_origin;
                $auxiliar->assistant_applyID =  $folioAfectar->treasuries_originID;
                $auxiliar->assistant_canceled = 0;
                $auxiliar->assistant_reference = $folioAfectar->treasuries_reference;

                $auxiliar->save();

                $auxiliar2 = new PROC_ASSISTANT();

                $auxiliar2->assistant_companieKey = $folioAfectar->treasuries_company;
                $auxiliar2->assistant_branchKey = $folioAfectar->treasuries_branchOffice;
                $auxiliar2->assistant_branch = 'Din';

                $auxiliar2->assistant_movement = $folioAfectar->treasuries_movement;
                $auxiliar2->assistant_movementID = $folioAfectar->treasuries_movementID;
                $auxiliar2->assistant_module = 'Din';
                $auxiliar2->assistant_moduleID = $folioAfectar->treasuries_id;
                $auxiliar2->assistant_money = $folioAfectar->treasuries_money;
                $auxiliar2->assistant_typeChange = $folioAfectar->treasuries_typeChange;
                $auxiliar2->assistant_account = $folioAfectar->treasuries_moneyAccount;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar2->assistant_year = $year;
                $auxiliar2->assistant_period = $period;
                $auxiliar2->assistant_charge = $folioAfectar->treasuries_total;
                $auxiliar2->assistant_payment = null;
                $auxiliar2->assistant_apply = $folioAfectar->treasuries_movement;
                $auxiliar2->assistant_applyID =  $folioAfectar->treasuries_movementID;
                $auxiliar2->assistant_canceled = 0;
                $auxiliar2->assistant_reference = $folioAfectar->treasuries_reference;

                $auxiliar2->save();
            } else {
                $auxiliar2 = new PROC_ASSISTANT();

                $auxiliar2->assistant_companieKey = $folioAfectar->treasuries_company;
                $auxiliar2->assistant_branchKey = $folioAfectar->treasuries_branchOffice;
                $auxiliar2->assistant_branch = 'Din';

                $auxiliar2->assistant_movement = $folioAfectar->treasuries_movement;
                $auxiliar2->assistant_movementID = $folioAfectar->treasuries_movementID;
                $auxiliar2->assistant_module = 'Din';
                $auxiliar2->assistant_moduleID = $folioAfectar->treasuries_id;
                $auxiliar2->assistant_money = $folioAfectar->treasuries_money;
                $auxiliar2->assistant_typeChange = $folioAfectar->treasuries_typeChange;
                $auxiliar2->assistant_account = $folioAfectar->treasuries_moneyAccount;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar2->assistant_year = $year;
                $auxiliar2->assistant_period = $period;
                $auxiliar2->assistant_charge = $folioAfectar->treasuries_total;
                $auxiliar2->assistant_payment = null;
                $auxiliar2->assistant_apply = $folioAfectar->treasuries_movement;
                $auxiliar2->assistant_applyID =  $folioAfectar->treasuries_movementID;
                $auxiliar2->assistant_canceled = 0;
                $auxiliar2->assistant_reference = $folioAfectar->treasuries_reference;

                $auxiliar2->save();
            }
        }
    }
    public function auxiliarTipoCaja($folio)
    {
        $folioAfectar = PROC_TREASURY::where('treasuries_id', $folio)->first();

        if ($folioAfectar->treasuries_movement === 'Ingreso' && $folioAfectar->treasuries_status === "FINALIZADO") {
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->treasuries_company;
            $auxiliar->assistant_branchKey = $folioAfectar->treasuries_branchOffice;
            $auxiliar->assistant_branch = 'Din';

            $auxiliar->assistant_movement = $folioAfectar->treasuries_movement;
            $auxiliar->assistant_movementID = $folioAfectar->treasuries_movementID;
            $auxiliar->assistant_module = 'Din';
            $auxiliar->assistant_moduleID = $folioAfectar->treasuries_id;
            $auxiliar->assistant_money = $folioAfectar->treasuries_money;
            $auxiliar->assistant_typeChange = $folioAfectar->treasuries_typeChange;
            $auxiliar->assistant_account = $folioAfectar->treasuries_moneyAccountOrigin;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = null;
            $auxiliar->assistant_payment = $folioAfectar->treasuries_total;
            $auxiliar->assistant_apply = $folioAfectar->treasuries_movement;
            $auxiliar->assistant_applyID =  $folioAfectar->treasuries_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->treasuries_reference;

            $auxiliar->save();

            $auxiliar2 = new PROC_ASSISTANT();

            $auxiliar2->assistant_companieKey = $folioAfectar->treasuries_company;
            $auxiliar2->assistant_branchKey = $folioAfectar->treasuries_branchOffice;
            $auxiliar2->assistant_branch = 'Din';

            $auxiliar2->assistant_movement = $folioAfectar->treasuries_movement;
            $auxiliar2->assistant_movementID = $folioAfectar->treasuries_movementID;
            $auxiliar2->assistant_module = 'Din';
            $auxiliar2->assistant_moduleID = $folioAfectar->treasuries_id;
            $auxiliar2->assistant_money = $folioAfectar->treasuries_money;
            $auxiliar2->assistant_typeChange = $folioAfectar->treasuries_typeChange;
            $auxiliar2->assistant_account = $folioAfectar->treasuries_moneyAccountDestiny;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar2->assistant_year = $year;
            $auxiliar2->assistant_period = $period;
            $auxiliar2->assistant_charge = $folioAfectar->treasuries_total;
            $auxiliar2->assistant_payment = null;
            $auxiliar2->assistant_apply = $folioAfectar->treasuries_movement;
            $auxiliar2->assistant_applyID =  $folioAfectar->treasuries_movementID;
            $auxiliar2->assistant_canceled = 0;
            $auxiliar2->assistant_reference = $folioAfectar->treasuries_reference;

            $auxiliar2->save();
        }
    }
    public function ingresoTipoCaja($folio)
    {
        $folioAfectar = PROC_TREASURY::where('treasuries_id', $folio)->first();

        if ($folioAfectar->treasuries_movement === 'Ingreso' && $folioAfectar->treasuries_status === "FINALIZADO") {
            //Obtenemos la cuenta origen y su saldo
            $cuentaOrigen =  PROC_BALANCE::WHERE('balance_companieKey', '=', $folioAfectar->treasuries_company)->WHERE('balance_branchKey', '=', $folioAfectar->treasuries_branchOffice)->WHERE('balance_account', '=', $folioAfectar->treasuries_moneyAccountOrigin)->first();

            $cuenta_balance_o = PROC_MONEY_ACCOUNTS_BALANCE::WHERE('moneyAccountsBalance_company', '=', $folioAfectar->treasuries_company)->WHERE('moneyAccountsBalance_moneyAccount', '=', $folioAfectar->treasuries_moneyAccountOrigin)->first();

            //Obtenemos la cuenta destino y su saldo
            $cuentaDestino = PROC_BALANCE::WHERE('balance_companieKey', '=', $folioAfectar->treasuries_company)->WHERE('balance_branchKey', '=', $folioAfectar->treasuries_branchOffice)->WHERE('balance_account', '=', $folioAfectar->treasuries_moneyAccountDestiny)->first();

            $cuenta_balance_d = PROC_MONEY_ACCOUNTS_BALANCE::WHERE('moneyAccountsBalance_company', '=', $folioAfectar->treasuries_company)->WHERE('moneyAccountsBalance_moneyAccount', '=', $folioAfectar->treasuries_moneyAccountDestiny)->first();

            $cuentaD = $cuenta_balance_d->moneyAccountsBalance_balance !== null ? $cuenta_balance_d->moneyAccountsBalance_balance : 0;
            $cuentaO = $cuenta_balance_o->moneyAccountsBalance_balance !== null ? $cuenta_balance_o->moneyAccountsBalance_balance : 0;

            if (!$cuentaDestino) {
                $cuentaDestino = new PROC_BALANCE;
                $cuentaDestino->balance_companieKey = $folioAfectar->treasuries_company;
                $cuentaDestino->balance_branchKey = $folioAfectar->treasuries_branchOffice;
                $cuentaDestino->balance_branch = "Din";
                $cuentaDestino->balance_money = $folioAfectar->treasuries_money;
                $cuentaDestino->balance_account = $folioAfectar->treasuries_moneyAccountDestiny;

                $cuentaDestino->balance_balance = floatval($cuentaD) + floatval(str_replace("['$',',']", '', $folioAfectar->treasuries_total));
                $cuentaDestino->balance_reconcile = floatval($cuentaD) +  floatval(str_replace("['$',',']", '', $folioAfectar->treasuries_total));
                $cuentaDestino->save();

                $cuenta_balance_d->moneyAccountsBalance_balance = floatval($cuentaD) + floatval(str_replace("['$',',']", '', $folioAfectar->treasuries_total));
                $cuenta_balance_d->update();
            } else {
                $cuentaDestino->balance_balance = floatval($cuentaD) + floatval(str_replace("['$',',']", '', $folioAfectar->treasuries_total));
                $cuentaDestino->balance_reconcile = floatval($cuentaD) +  floatval(str_replace("['$',',']", '', $folioAfectar->treasuries_total));
                $cuentaDestino->update();

                $cuenta_balance_d->moneyAccountsBalance_balance = floatval($cuentaD) + floatval(str_replace("['$',',']", '', $folioAfectar->treasuries_total));
                $cuenta_balance_d->update();
            }

            if (!$cuentaOrigen) {
                //Si no existe la cuenta en balance la creamos
                $cuentaOrigen = new PROC_BALANCE;
                $cuentaOrigen->balance_companieKey = $folioAfectar->treasuries_company;
                $cuentaOrigen->balance_branchKey = $folioAfectar->treasuries_branchOffice;
                $cuentaOrigen->balance_branch = "Din";
                $cuentaOrigen->balance_money = $folioAfectar->treasuries_money;
                $cuentaOrigen->balance_account = $folioAfectar->treasuries_moneyAccountOrigin;
                $cuentaOrigen->balance_balance = floatval($cuentaO) - floatval(str_replace("['$',',']", '', $folioAfectar->treasuries_total));
                $cuentaOrigen->balance_reconcile = floatval($cuentaO) -  floatval(str_replace("['$',',']", '', $folioAfectar->treasuries_total));
                $cuentaOrigen->save();

                $cuenta_balance_o->moneyAccountsBalance_balance = floatval($cuentaO) - floatval(str_replace("['$',',']", '', $folioAfectar->treasuries_total));
                $cuenta_balance_o->update();
            } else {
                $cuentaOrigen->balance_balance = floatval($cuentaO) - floatval(str_replace("['$',',']", '', $folioAfectar->treasuries_total));
                $cuentaOrigen->balance_reconcile = floatval($cuentaO) -  floatval(str_replace("['$',',']", '', $folioAfectar->treasuries_total));
                $cuentaOrigen->update();

                $cuenta_balance_o->moneyAccountsBalance_balance = floatval($cuentaO) - floatval(str_replace("['$',',']", '', $folioAfectar->treasuries_total));
                $cuenta_balance_o->update();
            }
        }
    }

    public function auxiliarIngreso($folio, $primerIngreso)
    {
        $folioAfectar = PROC_TREASURY::where('treasuries_id', $folio)->first();

        if ($folioAfectar->treasuries_status === "FINALIZADO") {
            //agregar datos a aux
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->treasuries_company;
            $auxiliar->assistant_branchKey = $folioAfectar->treasuries_branchOffice;
            $auxiliar->assistant_branch = 'Din';

            $auxiliar->assistant_movement = $folioAfectar->treasuries_movement;
            $auxiliar->assistant_movementID = $folioAfectar->treasuries_movementID;
            $auxiliar->assistant_module = 'Din';
            $auxiliar->assistant_moduleID = $folioAfectar->treasuries_id;
            $auxiliar->assistant_money = $folioAfectar->treasuries_money;
            $auxiliar->assistant_typeChange = $folioAfectar->treasuries_typeChange;
            $auxiliar->assistant_account = $folioAfectar->treasuries_moneyAccountOrigin;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = $folioAfectar->treasuries_total;
            $auxiliar->assistant_payment = null;
            $auxiliar->assistant_apply = $folioAfectar->treasuries_movement;
            $auxiliar->assistant_applyID =  $folioAfectar->treasuries_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->treasuries_reference;

            if ($primerIngreso) {
                $auxiliar->assistant_balanceInitial = 1;
            }



            $auxiliar->save();
        }
    }

    public function auxiliarEgreso($folio)
    {
        $folioAfectar = PROC_TREASURY::where('treasuries_id', $folio)->first();

        if ($folioAfectar->treasuries_status === "FINALIZADO") {
            //agregar datos a aux
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->treasuries_company;
            $auxiliar->assistant_branchKey = $folioAfectar->treasuries_branchOffice;
            $auxiliar->assistant_branch = 'Din';

            $auxiliar->assistant_movement = $folioAfectar->treasuries_movement;
            $auxiliar->assistant_movementID = $folioAfectar->treasuries_movementID;
            $auxiliar->assistant_module = 'Din';
            $auxiliar->assistant_moduleID = $folioAfectar->treasuries_id;
            $auxiliar->assistant_money = $folioAfectar->treasuries_money;
            $auxiliar->assistant_typeChange = $folioAfectar->treasuries_typeChange;
            $auxiliar->assistant_account = $folioAfectar->treasuries_moneyAccount;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = null;
            $auxiliar->assistant_payment = $folioAfectar->treasuries_total;
            $auxiliar->assistant_apply = $folioAfectar->treasuries_movement;
            $auxiliar->assistant_applyID =  $folioAfectar->treasuries_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->treasuries_reference;

            $auxiliar->save();
        }
    }
    public function auxiliarTransferencia($folio, $primerIngreso)
    {
        $folioAfectar = PROC_TREASURY::where('treasuries_id', $folio)->first();

        if ($folioAfectar->treasuries_status === "FINALIZADO") {
            //agregar datos a aux
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->treasuries_company;
            $auxiliar->assistant_branchKey = $folioAfectar->treasuries_branchOffice;
            $auxiliar->assistant_branch = 'Din';

            $auxiliar->assistant_movement = $folioAfectar->treasuries_movement;
            $auxiliar->assistant_movementID = $folioAfectar->treasuries_movementID;
            $auxiliar->assistant_module = 'Din';
            $auxiliar->assistant_moduleID = $folioAfectar->treasuries_id;
            $auxiliar->assistant_money = $folioAfectar->treasuries_money;
            $auxiliar->assistant_typeChange = $folioAfectar->treasuries_typeChange;
            $auxiliar->assistant_account = $folioAfectar->treasuries_moneyAccountOrigin;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = null;
            $auxiliar->assistant_payment = $folioAfectar->treasuries_total;
            $auxiliar->assistant_apply = $folioAfectar->treasuries_movement;
            $auxiliar->assistant_applyID =  $folioAfectar->treasuries_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->treasuries_reference;

            $auxiliar->save();

            $auxiliar2 = new PROC_ASSISTANT();

            $auxiliar2->assistant_companieKey = $folioAfectar->treasuries_company;
            $auxiliar2->assistant_branchKey = $folioAfectar->treasuries_branchOffice;
            $auxiliar2->assistant_branch = 'Din';

            $auxiliar2->assistant_movement = $folioAfectar->treasuries_movement;
            $auxiliar2->assistant_movementID = $folioAfectar->treasuries_movementID;
            $auxiliar2->assistant_module = 'Din';
            $auxiliar2->assistant_moduleID = $folioAfectar->treasuries_id;
            $auxiliar2->assistant_money = $folioAfectar->treasuries_money;
            $auxiliar2->assistant_typeChange = $folioAfectar->treasuries_typeChange;
            $auxiliar2->assistant_account = $folioAfectar->treasuries_moneyAccountDestiny;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar2->assistant_year = $year;
            $auxiliar2->assistant_period = $period;
            $auxiliar2->assistant_charge = $folioAfectar->treasuries_total;
            $auxiliar2->assistant_payment = null;
            $auxiliar2->assistant_apply = $folioAfectar->treasuries_movement;
            $auxiliar2->assistant_applyID =  $folioAfectar->treasuries_movementID;
            $auxiliar2->assistant_canceled = 0;
            $auxiliar2->assistant_reference = $folioAfectar->treasuries_reference;

            if ($primerIngreso) {
                $auxiliar->assistant_balanceInitial = 1;
            }

            $auxiliar2->save();
        }
    }
    function nuevoMovimiento($folio)
    {
        $folioAfectar = PROC_TREASURY::where('treasuries_id', $folio)->first();

        if ($folioAfectar->treasuries_status === "FINALIZADO") {

            $tesoreriaSolicitudCheque = PROC_TREASURY::where('treasuries_movement', '=', $folioAfectar->treasuries_origin)->WHERE('treasuries_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->WHERE('treasuries_movementID', '=', $folioAfectar->treasuries_originID)->WHERE('treasuries_company', '=', $folioAfectar->treasuries_company)->first();

            $movFlow = new PROC_MOVEMENT_FLOW();
            $movFlow->movementFlow_branch = $folioAfectar->treasuries_branchOffice;
            $movFlow->movementFlow_company = $folioAfectar->treasuries_company;
            $movFlow->movementFlow_moduleOrigin = 'Din';
            $movFlow->movementFlow_originID =  $tesoreriaSolicitudCheque->treasuries_id;
            $movFlow->movementFlow_movementOrigin =  $tesoreriaSolicitudCheque->treasuries_movement;
            $movFlow->movementFlow_movementOriginID =  $tesoreriaSolicitudCheque->treasuries_movementID;
            $movFlow->movementFlow_moduleDestiny = 'Din';
            $movFlow->movementFlow_movementDestinity = $folioAfectar->treasuries_movement;
            $movFlow->movementFlow_destinityID = $folioAfectar->treasuries_id;
            $movFlow->movementFlow_movementDestinity = $folioAfectar->treasuries_movement;
            $movFlow->movementFlow_movementDestinityID = $folioAfectar->treasuries_movementID;
            $movFlow->movementFlow_cancelled = 0;
            $movFlow->save();
        }
    }
    function nuevoSaldoCuenta($folio)
    {
        $isPrimerIngreso = false;
        $folioAfectar = PROC_TREASURY::where('treasuries_id', $folio)->first();

        if ($folioAfectar->treasuries_movement === 'Transferencia Electrónica' && $folioAfectar->treasuries_status === "FINALIZADO") {
            $cuentaSaldo = PROC_MONEY_ACCOUNTS_BALANCE::WHERE('moneyAccountsBalance_company', '=', $folioAfectar->treasuries_company)->WHERE('moneyAccountsBalance_status', '=', 'Alta')->WHERE('moneyAccountsBalance_moneyAccount', '=', $folioAfectar->treasuries_moneyAccount)->first();
            $saldoActual =   $cuentaSaldo->moneyAccountsBalance_balance === Null ? 0 : floatval($cuentaSaldo->moneyAccountsBalance_balance);
            $totalCheque = floatval($folioAfectar->treasuries_total);
            $nuevoSaldo = $saldoActual - $totalCheque;

            $cuentaSaldo->moneyAccountsBalance_balance = $nuevoSaldo;
            $cuentaSaldo->update();

            $cuentaBalance = PROC_BALANCE::WHERE('balance_companieKey', '=', $folioAfectar->treasuries_company)->WHERE('balance_branchKey', '=', $folioAfectar->treasuries_branchOffice)->WHERE('balance_account', '=', $folioAfectar->treasuries_moneyAccount)->first();

            if ($cuentaBalance) {
                $cuentaBalance->balance_balance = $nuevoSaldo;
                $cuentaBalance->balance_reconcile = $nuevoSaldo;
                $cuentaBalance->update();
            } else {
                $cuentaBalance = new PROC_BALANCE();
                $cuentaBalance->balance_companieKey = $folioAfectar->treasuries_company;
                $cuentaBalance->balance_branchKey = $folioAfectar->treasuries_branchOffice;
                $cuentaBalance->balance_branch = 'Din';
                $cuentaBalance->balance_money = $folioAfectar->treasuries_money;
                $cuentaBalance->balance_account = $folioAfectar->treasuries_moneyAccount;
                $cuentaBalance->balance_balance = $nuevoSaldo;
                $cuentaBalance->balance_reconcile = $nuevoSaldo;
                $cuentaBalance->save();
            }
        }

        if ($folioAfectar->treasuries_movement === 'Ingreso' && $folioAfectar->treasuries_status === "FINALIZADO") {
            $cuentaSaldo = PROC_MONEY_ACCOUNTS_BALANCE::WHERE('moneyAccountsBalance_company', '=', $folioAfectar->treasuries_company)->WHERE('moneyAccountsBalance_status', '=', 'Alta')->WHERE('moneyAccountsBalance_moneyAccount', '=', $folioAfectar->treasuries_moneyAccountOrigin)->first();
            $saldoActual =   $cuentaSaldo->moneyAccountsBalance_balance === Null ? 0 : floatval($cuentaSaldo->moneyAccountsBalance_balance);
            $totalCheque = floatval($folioAfectar->treasuries_total);
            $nuevoSaldo = $saldoActual + $totalCheque;

            $cuentaSaldo->moneyAccountsBalance_balance = $nuevoSaldo;
            $cuentaSaldo->update();

            if ($cuentaSaldo->moneyAccountsBalance_initialBalance === Null) {
                $cuentaSaldo->moneyAccountsBalance_initialBalance = $nuevoSaldo;
                $cuentaSaldo->update();
                $isPrimerIngreso = true;
            }

            $saldo = PROC_BALANCE::WHERE('balance_branchKey', '=', $folioAfectar->treasuries_branchOffice)->WHERE('balance_account', '=', $folioAfectar->treasuries_moneyAccountOrigin)->WHERE('balance_companieKey', '=', $folioAfectar->treasuries_company)->first();

            if ($saldo) {
                $saldoActual =  $saldo->balance_balance === Null ? 0 : floatval($saldo->balance_balance);

                $ingreso = floatval($folioAfectar->treasuries_total);
                $saldo->balance_balance = $saldoActual + $ingreso;
                $saldo->balance_reconcile = $saldoActual + $ingreso;
                $saldo->update();
            } else {
                $saldoCuenta = new PROC_BALANCE;
                $saldoCuenta->balance_companieKey = $folioAfectar->treasuries_company;
                $saldoCuenta->balance_money = $folioAfectar->treasuries_money;
                $saldoCuenta->balance_branchKey = $folioAfectar->treasuries_branchOffice;
                $saldoCuenta->balance_balance = 0 + floatval($folioAfectar->treasuries_total);
                $saldoCuenta->balance_reconcile = 0 + floatval($folioAfectar->treasuries_total);
                $saldoCuenta->balance_branch = "Din";
                $saldoCuenta->balance_account = $folioAfectar->treasuries_moneyAccountOrigin;
                $saldoCuenta->save();
            }
        }

        if ($folioAfectar->treasuries_movement === 'Egreso' && $folioAfectar->treasuries_status === "FINALIZADO") {
            $cuentaSaldo = PROC_MONEY_ACCOUNTS_BALANCE::WHERE('moneyAccountsBalance_company', '=', $folioAfectar->treasuries_company)->WHERE('moneyAccountsBalance_status', '=', 'Alta')->WHERE('moneyAccountsBalance_moneyAccount', '=', $folioAfectar->treasuries_moneyAccount)->first();
            $saldoActual =   $cuentaSaldo->moneyAccountsBalance_balance === Null ? 0 : floatval($cuentaSaldo->moneyAccountsBalance_balance);
            $total = floatval($folioAfectar->treasuries_total);
            $nuevoSaldo = $saldoActual - $total;

            $cuentaSaldo->moneyAccountsBalance_balance = $nuevoSaldo;
            $cuentaSaldo->update();

            $cuentaBalance = PROC_BALANCE::WHERE('balance_companieKey', '=', $folioAfectar->treasuries_company)->WHERE('balance_branchKey', '=', $folioAfectar->treasuries_branchOffice)->WHERE('balance_account', '=', $folioAfectar->treasuries_moneyAccount)->first();

            if ($cuentaBalance) {
                $cuentaBalance->balance_balance = $nuevoSaldo;
                $cuentaBalance->balance_reconcile = $nuevoSaldo;
                $cuentaBalance->update();
            } else {
                $cuentaBalance = new PROC_BALANCE();
                $cuentaBalance->balance_companieKey = $folioAfectar->treasuries_company;
                $cuentaBalance->balance_branchKey = $folioAfectar->treasuries_branchOffice;
                $cuentaBalance->balance_branch = 'Din';
                $cuentaBalance->balance_money = $folioAfectar->treasuries_money;
                $cuentaBalance->balance_account = $folioAfectar->treasuries_moneyAccount;
                $cuentaBalance->balance_balance = $nuevoSaldo;
                $cuentaBalance->balance_reconcile = $nuevoSaldo;
                $cuentaBalance->save();
            }
        }

        if ($folioAfectar->treasuries_movement === 'Traspaso Cuentas' && $folioAfectar->treasuries_status === "FINALIZADO") {
            $cuentaSaldoOrigin = PROC_MONEY_ACCOUNTS_BALANCE::WHERE('moneyAccountsBalance_company', '=', $folioAfectar->treasuries_company)->WHERE('moneyAccountsBalance_status', '=', 'Alta')->WHERE('moneyAccountsBalance_moneyAccount', '=', $folioAfectar->treasuries_moneyAccountOrigin)->first();
            $saldoActual =   $cuentaSaldoOrigin->moneyAccountsBalance_balance === Null ? 0 : floatval($cuentaSaldoOrigin->moneyAccountsBalance_balance);
            $total = floatval($folioAfectar->treasuries_total);
            $nuevoSaldoOrigin = $saldoActual - $total;

            $cuentaSaldoOrigin->moneyAccountsBalance_balance = $nuevoSaldoOrigin;
            $cuentaSaldoOrigin->update();

            $cuentaBalance = PROC_BALANCE::WHERE('balance_companieKey', '=', $folioAfectar->treasuries_company)->WHERE('balance_branchKey', '=', $folioAfectar->treasuries_branchOffice)->WHERE('balance_account', '=', $folioAfectar->treasuries_moneyAccountOrigin)->first();

            if ($cuentaBalance) {
                $cuentaBalance->balance_balance = $nuevoSaldoOrigin;
                $cuentaBalance->balance_reconcile = $nuevoSaldoOrigin;
                $cuentaBalance->update();
            } else {
                $cuentaBalance = new PROC_BALANCE();
                $cuentaBalance->balance_companieKey = $folioAfectar->treasuries_company;
                $cuentaBalance->balance_branchKey = $folioAfectar->treasuries_branchOffice;
                $cuentaBalance->balance_branch = 'Din';
                $cuentaBalance->balance_money = $folioAfectar->treasuries_money;
                $cuentaBalance->balance_account = $folioAfectar->treasuries_moneyAccountOrigin;
                $cuentaBalance->balance_balance = $nuevoSaldoOrigin;
                $cuentaBalance->balance_reconcile = $nuevoSaldoOrigin;
                $cuentaBalance->save();
            }


            //Banco destino 

            $cuentaSaldoDes = PROC_MONEY_ACCOUNTS_BALANCE::WHERE('moneyAccountsBalance_company', '=', $folioAfectar->treasuries_company)->WHERE('moneyAccountsBalance_status', '=', 'Alta')->WHERE('moneyAccountsBalance_moneyAccount', '=', $folioAfectar->treasuries_moneyAccountDestiny)->first();
            $saldoActual =   $cuentaSaldoDes->moneyAccountsBalance_balance === Null ? 0 : floatval($cuentaSaldoDes->moneyAccountsBalance_balance);
            $total = floatval($folioAfectar->treasuries_total);
            $nuevoSaldoDes = $saldoActual + $total;

            if ($cuentaSaldoDes->moneyAccountsBalance_initialBalance === Null) {
                $cuentaSaldoDes->moneyAccountsBalance_initialBalance = $nuevoSaldoDes;
                $cuentaSaldoDes->update();
                $isPrimerIngreso = true;
            }

            $cuentaSaldoDes->moneyAccountsBalance_balance = $nuevoSaldoDes;
            $cuentaSaldoDes->update();

            $cuentaBalance = PROC_BALANCE::WHERE('balance_companieKey', '=', $folioAfectar->treasuries_company)->WHERE('balance_branchKey', '=', $folioAfectar->treasuries_branchOffice)->WHERE('balance_account', '=', $folioAfectar->treasuries_moneyAccountDestiny)->first();

            if ($cuentaBalance) {
                $cuentaBalance->balance_balance = $nuevoSaldoDes;
                $cuentaBalance->balance_reconcile = $nuevoSaldoDes;
                $cuentaBalance->update();
            } else {
                $cuentaBalance = new PROC_BALANCE();
                $cuentaBalance->balance_companieKey = $folioAfectar->treasuries_company;
                $cuentaBalance->balance_branchKey = $folioAfectar->treasuries_branchOffice;
                $cuentaBalance->balance_branch = 'Din';
                $cuentaBalance->balance_money = $folioAfectar->treasuries_money;
                $cuentaBalance->balance_account = $folioAfectar->treasuries_moneyAccountDestiny;
                $cuentaBalance->balance_balance = $nuevoSaldoDes;
                $cuentaBalance->balance_reconcile = $nuevoSaldoDes;
                $cuentaBalance->save();
            }
        }

        if ($folioAfectar->treasuries_movement === 'Depósito' && $folioAfectar->treasuries_status === "FINALIZADO") {
            $cuentaSaldo = PROC_MONEY_ACCOUNTS_BALANCE::WHERE('moneyAccountsBalance_company', '=', $folioAfectar->treasuries_company)->WHERE('moneyAccountsBalance_status', '=', 'Alta')->WHERE('moneyAccountsBalance_moneyAccount', '=', $folioAfectar->treasuries_moneyAccount)->first();
            $saldoActual =   $cuentaSaldo->moneyAccountsBalance_balance === Null ? 0 : floatval($cuentaSaldo->moneyAccountsBalance_balance);
            $totalCheque = floatval($folioAfectar->treasuries_total);
            $nuevoSaldo = $saldoActual + $totalCheque;

            $cuentaSaldo->moneyAccountsBalance_balance = $nuevoSaldo;
            $cuentaSaldo->update();

            $cuentaBalance = PROC_BALANCE::WHERE('balance_companieKey', '=', $folioAfectar->treasuries_company)->WHERE('balance_branchKey', '=', $folioAfectar->treasuries_branchOffice)->WHERE('balance_account', '=', $folioAfectar->treasuries_moneyAccount)->first();

            if ($cuentaBalance) {
                $cuentaBalance->balance_balance = $nuevoSaldo;
                $cuentaBalance->balance_reconcile = $nuevoSaldo;
                $cuentaBalance->update();
            } else {
                $cuentaBalance = new PROC_BALANCE();
                $cuentaBalance->balance_companieKey = $folioAfectar->treasuries_company;
                $cuentaBalance->balance_branchKey = $folioAfectar->treasuries_branchOffice;
                $cuentaBalance->balance_branch = 'Din';
                $cuentaBalance->balance_money = $folioAfectar->treasuries_money;
                $cuentaBalance->balance_account = $folioAfectar->treasuries_moneyAccount;
                $cuentaBalance->balance_balance = $nuevoSaldo;
                $cuentaBalance->balance_reconcile = $nuevoSaldo;
                $cuentaBalance->save();
            }
        }

        return $isPrimerIngreso;
    }
    function saldo($folio)
    {
        $folioAfectar = PROC_TREASURY::where('treasuries_id', $folio)->first();

        if ($folioAfectar->treasuries_movement === 'Transferencia Electrónica' && $folioAfectar->treasuries_status === "FINALIZADO") {


            $solicitud = PROC_TREASURY::where('treasuries_movement', '=', $folioAfectar->treasuries_origin)->where('treasuries_movementID', '=',  $folioAfectar->treasuries_originID)->where('treasuries_company', '=', $folioAfectar->treasuries_company)->where('treasuries_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->first();


            if ($solicitud->treasuries_origin != 'Reposición Caja' && $solicitud->treasuries_origin != 'Devolución de Anticipo') {
                $proveedorSaldo = PROC_BALANCE::WHERE('balance_branchKey', '=', $folioAfectar->treasuries_branchOffice)->WHERE('balance_account', '=', $folioAfectar->treasuries_beneficiary)->WHERE('balance_companieKey', '=', $folioAfectar->treasuries_company)->WHERE('balance_branch', '=', 'CxP')->where('balance_money', '=', $folioAfectar->treasuries_money)->first();

                if ($proveedorSaldo !== null && $proveedorSaldo->count() > 0) {
                    $saldoActual =  $proveedorSaldo->balance_balance === Null ? 0 : floatval($proveedorSaldo->balance_balance);

                    $totalCheque = floatval($folioAfectar->treasuries_total);
                    $proveedorSaldo->balance_balance = $saldoActual - $totalCheque;
                    $proveedorSaldo->balance_reconcile = $proveedorSaldo->balance_balance;
                    $proveedorSaldo->update();
                } else {
                    $proveedorSaldo = new PROC_BALANCE;
                    $proveedorSaldo->balance_companieKey = $folioAfectar->treasuries_company;
                    $proveedorSaldo->balance_money = $folioAfectar->treasuries_money;
                    $proveedorSaldo->balance_branchKey = $folioAfectar->treasuries_branchOffice;
                    $proveedorSaldo->balance_balance = 0 - floatval($folioAfectar->treasuries_total);
                    $proveedorSaldo->balance_reconcile = $proveedorSaldo->balance_balance;
                    $proveedorSaldo->balance_branch = "CxP";
                    $proveedorSaldo->balance_account = $folioAfectar->treasuries_beneficiary;
                    $proveedorSaldo->save();
                }
            } else if ($solicitud->treasuries_origin === 'Devolución de Anticipo') {
                $proveedorSaldo = PROC_BALANCE::WHERE('balance_branchKey', '=', $folioAfectar->treasuries_branchOffice)->WHERE('balance_account', '=', $folioAfectar->treasuries_beneficiary)->WHERE('balance_companieKey', '=', $folioAfectar->treasuries_company)->WHERE('balance_branch', '=', 'CxC')->where('balance_money', '=', $folioAfectar->treasuries_money)->first();

                if ($proveedorSaldo !== null && $proveedorSaldo->count() > 0) {
                    $saldoActual =  $proveedorSaldo->balance_balance === Null ? 0 : floatval($proveedorSaldo->balance_balance);

                    $totalCheque = floatval($folioAfectar->treasuries_total);
                    $proveedorSaldo->balance_balance = $saldoActual + $totalCheque;
                    $proveedorSaldo->balance_reconcile = $proveedorSaldo->balance_balance;
                    $proveedorSaldo->update();
                } else {
                    $proveedorSaldo = new PROC_BALANCE;
                    $proveedorSaldo->balance_companieKey = $folioAfectar->treasuries_company;
                    $proveedorSaldo->balance_money = $folioAfectar->treasuries_money;
                    $proveedorSaldo->balance_branchKey = $folioAfectar->treasuries_branchOffice;
                    $proveedorSaldo->balance_balance = 0 + floatval($folioAfectar->treasuries_total);
                    $proveedorSaldo->balance_reconcile = $proveedorSaldo->balance_balance;
                    $proveedorSaldo->balance_branch = "CxC";
                    $proveedorSaldo->balance_account = $folioAfectar->treasuries_beneficiary;
                    $proveedorSaldo->save();
                }
            }
        }

        if ($folioAfectar->treasuries_movement === 'Depósito' && $folioAfectar->treasuries_status === "FINALIZADO") {

            //Tenemos el deposito y verificamos q el origen venga de cxc
            $solicitudDeposito = PROC_TREASURY::WHERE('treasuries_movement', '=', $folioAfectar->treasuries_origin)->WHERE('treasuries_movementID', '=', $folioAfectar->treasuries_originID)->WHERE('treasuries_company', '=', $folioAfectar->treasuries_company)->where("treasuries_branchOffice", '=', $folioAfectar->treasuries_branchOffice)->first();


            if ($solicitudDeposito->treasuries_origin !== 'Factura') {

                $proveedorSaldo = PROC_BALANCE::WHERE('balance_branchKey', '=', $folioAfectar->treasuries_branchOffice)->WHERE('balance_account', '=', $folioAfectar->treasuries_beneficiary)->WHERE('balance_companieKey', '=', $folioAfectar->treasuries_company)->WHERE('balance_branch', '=', 'CxC')->where('balance_money', '=', $folioAfectar->treasuries_money)->first();

                if ($proveedorSaldo !== null && $proveedorSaldo->count() > 0) {
                    $saldoActual =  $proveedorSaldo->balance_balance === Null ? 0 : floatval($proveedorSaldo->balance_balance);

                    $totalCheque = floatval($folioAfectar->treasuries_total);
                    $proveedorSaldo->balance_balance = $saldoActual - $totalCheque;
                    $proveedorSaldo->balance_reconcile = $proveedorSaldo->balance_balance;
                    $proveedorSaldo->update();
                } else {
                    $proveedorSaldo = new PROC_BALANCE;
                    $proveedorSaldo->balance_companieKey = $folioAfectar->treasuries_company;
                    $proveedorSaldo->balance_money = $folioAfectar->treasuries_money;
                    $proveedorSaldo->balance_branchKey = $folioAfectar->treasuries_branchOffice;
                    $proveedorSaldo->balance_balance = 0 - floatval($folioAfectar->treasuries_total);
                    $proveedorSaldo->balance_reconcile = $proveedorSaldo->balance_balance;
                    $proveedorSaldo->balance_branch = "CxC";
                    $proveedorSaldo->balance_account = $folioAfectar->treasuries_beneficiary;
                    $proveedorSaldo->save();
                }
            }
        }
    }
    public function solicitudChequeConcluido($folio)
    {
        $folioAfectar = PROC_TREASURY::where('treasuries_id', $folio)->first();

        if ($folioAfectar->treasuries_movement === "Transferencia Electrónica" && $folioAfectar->treasuries_status === "FINALIZADO") {
            //Obtenemos el origen del folioAfectar
            $solicitudCheque = PROC_TREASURY::WHERE('treasuries_movement', '=', trim($folioAfectar->treasuries_origin))->WHERE('treasuries_movementID', '=', $folioAfectar->treasuries_originID)
                ->WHERE('treasuries_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->first();

            $solicitudCheque->treasuries_status = $this->estatus[2];
            $solicitudCheque->update();

            //Encontramos la solicitud de deposito en tesoreria
            $solicitudCheque = PROC_TREASURY::WHERE('treasuries_movement', '=', $folioAfectar->treasuries_origin)->WHERE('treasuries_movementID', '=', $folioAfectar->treasuries_originID)->WHERE('treasuries_company', '=', $folioAfectar->treasuries_company)->where("treasuries_branchOffice", '=', $folioAfectar->treasuries_branchOffice)->first();



            if ($solicitudCheque->treasuries_origin !== "Reposición Caja" && $solicitudCheque->treasuries_origin !== "Devolución de Anticipo") {

                //Encontramos la solicitud de cheque en CXPP y lo quitamos 
                PROC_ACCOUNTS_PAYABLE_P::WHERE('accountsPayableP_movement', '=', $folioAfectar->treasuries_origin)->WHERE('accountsPayableP_originID', '=', $folioAfectar->treasuries_originID)
                    ->WHERE('accountsPayableP_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->WHERE('accountsPayableP_company', '=', $folioAfectar->treasuries_company)->delete();


                //Encontramos la solicitud de cheque en CXP y cambiamos su estatus a FINALIZADO
                $solicitudChequeCxp = PROC_ACCOUNTS_PAYABLE::WHERE('accountsPayable_movement', '=', $folioAfectar->treasuries_origin)->WHERE('accountsPayable_originID', '=', $folioAfectar->treasuries_originID)
                    ->WHERE('accountsPayable_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->WHERE('accountsPayable_company', '=', $folioAfectar->treasuries_company)->first();

                $solicitudChequeCxp->accountsPayable_status = $this->estatus[2];
                $solicitudChequeCxp->update();
            }

            if ($solicitudCheque->treasuries_origin === "Devolución de Anticipo") {
                //Encontramos la solicitud de cheque en CXPP y lo quitamos 
                PROC_ACCOUNTS_RECEIVABLE_P::WHERE('accountsReceivableP_movement', '=', $folioAfectar->treasuries_origin)->WHERE('accountsReceivableP_originID', '=', $folioAfectar->treasuries_originID)
                    ->WHERE('accountsReceivableP_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->WHERE('accountsReceivableP_company', '=', $folioAfectar->treasuries_company)->delete();


                //Encontramos la solicitud de cheque en CXP y cambiamos su estatus a FINALIZADO
                $solicitudChequeCxp = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', $folioAfectar->treasuries_origin)->WHERE('accountsReceivable_originID', '=', $folioAfectar->treasuries_originID)
                    ->WHERE('accountsReceivable_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->WHERE('accountsReceivable_company', '=', $folioAfectar->treasuries_company)->first();

                $solicitudChequeCxp->accountsReceivable_status = $this->estatus[2];

                $solicitudChequeCxp->update();
            }
        }

        if ($folioAfectar->treasuries_movement === "Depósito" && $folioAfectar->treasuries_status === "FINALIZADO") {
            //Obtenemos el origen del folioAfectar
            $solicitudCheque = PROC_TREASURY::WHERE('treasuries_movement', '=', trim($folioAfectar->treasuries_origin))->WHERE('treasuries_movementID', '=', $folioAfectar->treasuries_originID)
                ->WHERE('treasuries_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->first();
                // dd($solicitudCheque);
            $solicitudCheque->treasuries_status = $this->estatus[2];
            $solicitudCheque->update();

            //Encontramos la solicitud de deposito en tesoreria
            $solicitudDeposito = PROC_TREASURY::WHERE('treasuries_movement', '=', $folioAfectar->treasuries_origin)->WHERE('treasuries_movementID', '=', $folioAfectar->treasuries_originID)->WHERE('treasuries_company', '=', $folioAfectar->treasuries_company)->where("treasuries_branchOffice", '=', $folioAfectar->treasuries_branchOffice)->first();

            if ($solicitudDeposito->treasuries_origin !== "Factura") {
                //Encontramos la solicitud de cheque en CXPP y lo quitamos 
                PROC_ACCOUNTS_RECEIVABLE_P::WHERE('accountsReceivableP_movement', '=', $folioAfectar->treasuries_origin)->WHERE('accountsReceivableP_originID', '=', $folioAfectar->treasuries_originID)
                    ->WHERE('accountsReceivableP_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->WHERE('accountsReceivableP_company', '=', $folioAfectar->treasuries_company)->delete();

                //Encontramos la solicitud de cheque en CXP y cambiamos su estatus a FINALIZADO
                $solicitudChequeCxp = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', $folioAfectar->treasuries_origin)->WHERE('accountsReceivable_originID', '=', $folioAfectar->treasuries_originID)
                    ->WHERE('accountsReceivable_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->WHERE('accountsReceivable_company', '=', $folioAfectar->treasuries_company)->first();

                $solicitudChequeCxp->accountsReceivable_status = $this->estatus[2];
                $solicitudChequeCxp->update();
            }
        }
    }

    public function cancelarTesoreria(Request $request)
    {
        $movimientoCancelar = PROC_TREASURY::where('treasuries_id', '=', $request->id)->first();


        if ($movimientoCancelar->treasuries_status == $this->estatus[2] && $movimientoCancelar->treasuries_movement == 'Ingreso') {
            try {
                $ingresoCancelado = false;
                $chequeCancelado = true;
                $depositoCancelado = true;
                $egresoCancelado = true;
                $transferenciaCancelado = true;

                //Verificamos que el ingreso no tenga un origen
                if ($movimientoCancelar->treasuries_origin !== null) {
                    $status = 500;
                    $message = 'No se puede cancelar el ingreso, ya que viene del origen CxC';
                    return response()->json(['mensaje' => $message, 'estatus' => $status]);
                }

                //quitamos el dinero a la cuenta de origen
                $cuentaOrigen = PROC_BALANCE::WHERE('balance_branchKey', '=', $movimientoCancelar->treasuries_branchOffice)->WHERE('balance_account', '=', $movimientoCancelar->treasuries_moneyAccountOrigin)->WHERE('balance_companieKey', '=', $movimientoCancelar->treasuries_company)->where('balance_money', '=', $movimientoCancelar->treasuries_money)->first();

                if ($cuentaOrigen !== null && $cuentaOrigen->count() > 0) {
                    $saldoActual =  $cuentaOrigen->balance_balance === Null ? 0 : floatval($cuentaOrigen->balance_balance);
                    $totalIngreso = floatval($movimientoCancelar->treasuries_total);
                    $cuentaOrigen->balance_balance = $saldoActual - $totalIngreso;
                    $cuentaOrigen->balance_reconcile =  $cuentaOrigen->balance_balance;
                    $validar = $cuentaOrigen->update();
                    if ($validar) {
                        $ingresoCancelado = true;
                    } else {
                        $ingresoCancelado = false;
                    }
                }

                $cuentaOrigen2 = PROC_MONEY_ACCOUNTS_BALANCE::WHERE('moneyAccountsBalance_moneyAccount', '=', $movimientoCancelar->treasuries_moneyAccountOrigin)->WHERE('moneyAccountsBalance_company', '=', $movimientoCancelar->treasuries_company)->where('moneyAccountsBalance_money', '=', $movimientoCancelar->treasuries_money)->first();
                if ($cuentaOrigen2 !== null && $cuentaOrigen2->count() > 0) {
                    $saldoActual2 =  $cuentaOrigen2->moneyAccountsBalance_balance === Null ? 0 : floatval($cuentaOrigen2->moneyAccountsBalance_balance);
                    $totalIngreso2 = floatval($movimientoCancelar->treasuries_total);
                    $cuentaOrigen2->moneyAccountsBalance_balance = $saldoActual2 - $totalIngreso2;
                    $validar2 = $cuentaOrigen2->update();
                    if ($validar2) {
                        $ingresoCancelado = true;
                    } else {
                        $ingresoCancelado = false;
                    }
                }

                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $movimientoCancelar->treasuries_company;
                $auxiliar->assistant_branchKey = $movimientoCancelar->treasuries_branchOffice;
                $auxiliar->assistant_branch = 'Din';

                $auxiliar->assistant_movement = $movimientoCancelar->treasuries_movement;
                $auxiliar->assistant_movementID = $movimientoCancelar->treasuries_movementID;
                $auxiliar->assistant_module = 'Din';
                $auxiliar->assistant_moduleID = $movimientoCancelar->treasuries_id;
                $auxiliar->assistant_money = $movimientoCancelar->treasuries_money;
                $auxiliar->assistant_typeChange = $movimientoCancelar->treasuries_typeChange;
                $auxiliar->assistant_account = $movimientoCancelar->treasuries_moneyAccountOrigin;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = "-" . $movimientoCancelar->treasuries_total;
                $auxiliar->assistant_payment = null;
                $auxiliar->assistant_apply = $movimientoCancelar->treasuries_movement;
                $auxiliar->assistant_applyID =  $movimientoCancelar->treasuries_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $movimientoCancelar->treasuries_reference;

                $validar3 =  $auxiliar->save();
                if ($validar3) {
                    $ingresoCancelado = true;
                } else {
                    $ingresoCancelado = false;
                }

                //Cancelamos el movimiento
                $movimientoCancelar->treasuries_status = $this->estatus[3];
                $validar4 =  $movimientoCancelar->update();
                if ($validar4) {
                    $ingresoCancelado = true;
                } else {
                    $ingresoCancelado = false;
                }
            } catch (\Exception $e) {
                dd($e);
                $ingresoCancelado = false;
                $message = $e->getMessage();
            }
        }

        if ($movimientoCancelar->treasuries_status == $this->estatus[2] && $movimientoCancelar->treasuries_movement == 'Transferencia Electrónica') {
// dd($movimientoCancelar);
            try {
                $ingresoCancelado = true;
                $chequeCancelado = false;
                $depositoCancelado = true;
                $egresoCancelado = true;
                $transferenciaCancelado = true;


                $flujo = PROC_MOVEMENT_FLOW::WHERE('movementFlow_movementOrigin', '=', $movimientoCancelar->treasuries_origin)->WHERE('movementFlow_destinityID', '=', $movimientoCancelar->treasuries_id)->WHERE('movementFlow_movementDestinity', '=', $movimientoCancelar->treasuries_movement)->WHERE('movementFlow_branch', '=', $movimientoCancelar->treasuries_branchOffice)->first();

                //regresamos la cuenta de origen a su estado anterior
                //primero comprobamos si vino de cxc o de cxp
                $solCheque = PROC_TREASURY::WHERE('treasuries_movement', '=', $movimientoCancelar->treasuries_origin)->WHERE('treasuries_movementID', '=', $movimientoCancelar->treasuries_originID)->WHERE('treasuries_branchOffice', '=', $movimientoCancelar->treasuries_branchOffice)->WHERE('treasuries_company', '=', $movimientoCancelar->treasuries_company)->first();
                // dd($solCheque);
                if($solCheque->treasuries_originType === 'CxC'){
                    // dd('cxc');
                    $flujo2 = PROC_MOVEMENT_FLOW::WHERE('movementFlow_originID', '=', $flujo->movementFlow_originID)->WHERE('movementFlow_movementOrigin', '=', $flujo->movementFlow_movementOrigin)->WHERE('movementFlow_movementDestinity', '=', $flujo->movementFlow_movementOrigin)->WHERE('movementFlow_moduleDestiny', '=', 'CxC')->WHERE('movementFlow_branch', '=', $movimientoCancelar->treasuries_branchOffice)->first();

                    $solicitudCheque = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_id', '=', $flujo2->movementFlow_destinityID)->first();
                    $solicitudCheque->accountsReceivable_status = $this->estatus[1];
                    $solicitudCheque->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                } else{
                    $flujo2 = PROC_MOVEMENT_FLOW::WHERE('movementFlow_originID', '=', $flujo->movementFlow_originID)->WHERE('movementFlow_movementOrigin', '=', $flujo->movementFlow_movementOrigin)->WHERE('movementFlow_movementDestinity', '=', $flujo->movementFlow_movementOrigin)->WHERE('movementFlow_moduleDestiny', '=', 'CxP')->WHERE('movementFlow_branch', '=', $movimientoCancelar->treasuries_branchOffice)->first();

                    $solicitudCheque = PROC_ACCOUNTS_PAYABLE::WHERE('accountsPayable_id', '=', $flujo2->movementFlow_destinityID)->first();
                    $solicitudCheque->accountsPayable_status = $this->estatus[1];
                    $solicitudCheque->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                }
                // dd($solicitudCheque);
                $validar = $solicitudCheque->update();
                // dd($validar);
                if ($validar) {
                    $chequeCancelado = true;
                } else {
                    $chequeCancelado = false;
                }
                // dd($chequeCancelado);
                if ($solCheque->treasuries_originType === 'CxC') {
                    $cuentaPagar = new PROC_ACCOUNTS_RECEIVABLE_P();


                    // dd($solicitudCheque);
                    $cuentaPagar->accountsReceivableP_movement = 'Sol. de Cheque/Transferencia';
                    $cuentaPagar->accountsReceivableP_movementID = $solicitudCheque->accountsReceivable_movementID;
                    $cuentaPagar->accountsReceivableP_issuedate = Carbon::parse($solicitudCheque->accountsReceivable_issueDate)->format('Y-m-d H:i:s');
                    $cuentaPagar->accountsReceivableP_expiration =  Carbon::parse($solicitudCheque->accountsReceivable_expiration)->format('Y-m-d H:i:s');

                    //dias credito y moratorio
                    $date = Carbon::now();
                    $emision = $date->format('Y-m-d');
                    $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
                    $vencimiento = Carbon::parse($solicitudCheque->accountsReceivable_expiration)->format('Y-m-d');
                    $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);


                    $diasCredito = $currentDate->diffInDays($shippingDate);
                    $diasMoratorio = $shippingDate->diffInDays($currentDate);
                    $cuentaPagar->accountsReceivableP_creditDays = $diasCredito;
                    $cuentaPagar->accountsReceivableP_moratoriumDays = '-' . $diasMoratorio;
                    $cuentaPagar->accountsReceivableP_money = $solicitudCheque->accountsReceivable_money;
                    $cuentaPagar->accountsReceivableP_typeChange = $solicitudCheque->accountsReceivable_typeChange;
                    $cuentaPagar->accountsReceivableP_moneyAccount = $solicitudCheque->accountsReceivable_moneyAccount;

                    $cuentaPagar->accountsReceivableP_customer = $solicitudCheque->accountsReceivable_customer;
                    $cuentaPagar->accountsReceivableP_condition = $solicitudCheque->accountsReceivable_condition;

                    $cuentaPagar->accountsReceivableP_formPayment = $solicitudCheque->accountsReceivable_paymentMethod;
                    $cuentaPagar->accountsReceivableP_amount = $solicitudCheque->accountsReceivable_amount;
                    $cuentaPagar->accountsReceivableP_taxes = $solicitudCheque->accountsReceivable_taxes;
                    $cuentaPagar->accountsReceivableP_total = $solicitudCheque->accountsReceivable_total;
                    $cuentaPagar->accountsReceivableP_balanceTotal = $solicitudCheque->accountsReceivable_total;
                    $cuentaPagar->accountsReceivableP_reference = $solicitudCheque->accountsReceivable_reference;
                    $cuentaPagar->accountsReceivableP_balance = $solicitudCheque->accountsReceivable_total;
                    $cuentaPagar->accountsReceivableP_company = $solicitudCheque->accountsReceivable_company;
                    $cuentaPagar->accountsReceivableP_branchOffice = $solicitudCheque->accountsReceivable_branchOffice;
                    $cuentaPagar->accountsReceivableP_user = $solicitudCheque->accountsReceivable_user;
                    $cuentaPagar->accountsReceivableP_status = $this->estatus[1];
                    $cuentaPagar->accountsReceivableP_origin = $solicitudCheque->accountsReceivable_movement;
                    $cuentaPagar->accountsReceivableP_originID = $solicitudCheque->accountsReceivable_movementID;
                    $cuentaPagar->accountsReceivableP_originType = 'Din';
                    $cuentaPagar->accountsReceivableP_concept = $solicitudCheque->accountsReceivable_concept;
                } else {
                    $cuentaPagar = new PROC_ACCOUNTS_PAYABLE_P();

                    // $cuentaPagar = new PROC_ACCOUNTS_PAYABLE_P();
                    $cuentaPagar->accountsPayableP_movement = 'Sol. de Cheque/Transferencia';
                    $cuentaPagar->accountsPayableP_movementID = $solicitudCheque->accountsPayable_movementID;
                    $cuentaPagar->accountsPayableP_issuedate = Carbon::parse($solicitudCheque->accountsPayable_issueDate)->format('Y-m-d H:i:s');
                    $cuentaPagar->accountsPayableP_expiration =  Carbon::parse($solicitudCheque->accountsPayable_expiration)->format('Y-m-d H:i:s');

                    //dias credito y moratorio
                    $date = Carbon::now();
                    $emision = $date->format('Y-m-d');
                    $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
                    $vencimiento = Carbon::parse($solicitudCheque->accountsPayable_expiration)->format('Y-m-d');
                    $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

                    $diasCredito = $currentDate->diffInDays($shippingDate);
                    $diasMoratorio = $shippingDate->diffInDays($currentDate);
                    $cuentaPagar->accountsPayableP_creditDays = $diasCredito;
                    $cuentaPagar->accountsPayableP_moratoriumDays = '-' . $diasMoratorio;
                    $cuentaPagar->accountsPayableP_money = $solicitudCheque->accountsPayable_money;
                    $cuentaPagar->accountsPayableP_typeChange = $solicitudCheque->accountsPayable_typeChange;
                    $cuentaPagar->accountsPayableP_moneyAccount = $solicitudCheque->accountsPayable_moneyAccount;

                    $cuentaPagar->accountsPayableP_provider = $solicitudCheque->accountsPayable_provider;
                    $cuentaPagar->accountsPayableP_condition = $solicitudCheque->accountsPayable_condition;

                    $cuentaPagar->accountsPayableP_formPayment = $solicitudCheque->accountsPayable_paymentMethod;
                    $cuentaPagar->accountsPayableP_amount = $solicitudCheque->accountsPayable_amount;
                    $cuentaPagar->accountsPayableP_taxes = $solicitudCheque->accountsPayable_taxes;
                    $cuentaPagar->accountsPayableP_total = $solicitudCheque->accountsPayable_total;
                    $cuentaPagar->accountsPayableP_balanceTotal = $solicitudCheque->accountsPayable_total;
                    $cuentaPagar->accountsPayableP_reference = $solicitudCheque->accountsPayable_reference;
                    $cuentaPagar->accountsPayableP_balance = $solicitudCheque->accountsPayable_total;
                    $cuentaPagar->accountsPayableP_company = $solicitudCheque->accountsPayable_company;
                    $cuentaPagar->accountsPayableP_branchOffice = $solicitudCheque->accountsPayable_branchOffice;
                    $cuentaPagar->accountsPayableP_user = $solicitudCheque->accountsPayable_user;
                    $cuentaPagar->accountsPayableP_status = $this->estatus[1];
                    $cuentaPagar->accountsPayableP_origin = $solicitudCheque->accountsPayable_movement;
                    $cuentaPagar->accountsPayableP_originID = $solicitudCheque->accountsPayable_movementID;
                    $cuentaPagar->accountsPayableP_originType = 'Din';
                    $cuentaPagar->accountsPayableP_concept = $solicitudCheque->accountsPayable_concept;
                }


                $validar2 = $cuentaPagar->save();
                // dd($validar2);
                if ($validar2) {
                    $chequeCancelado = true;
                } else {
                    $chequeCancelado = false;
                }

                if ($solCheque->treasuries_originType === 'CxC') {
                    $proveedorSaldo = PROC_BALANCE::WHERE('balance_branchKey', '=', $movimientoCancelar->treasuries_branchOffice)->WHERE('balance_account', '=', $movimientoCancelar->treasuries_beneficiary)->WHERE('balance_companieKey', '=', $movimientoCancelar->treasuries_company)->WHERE('balance_branch', '=', 'CxC')->WHERE('balance_money', '=', $movimientoCancelar->treasuries_money)->first();
                }
                else{
                //regresamos el saldo de la cuenta de origen
                $proveedorSaldo = PROC_BALANCE::WHERE('balance_branchKey', '=', $movimientoCancelar->treasuries_branchOffice)->WHERE('balance_account', '=', $movimientoCancelar->treasuries_beneficiary)->WHERE('balance_companieKey', '=', $movimientoCancelar->treasuries_company)->WHERE('balance_branch', '=', 'CxP')->WHERE('balance_money', '=', $movimientoCancelar->treasuries_money)->first();
                }


                $proveedorSaldo->balance_balance = $proveedorSaldo->balance_balance + $movimientoCancelar->treasuries_total;
                $proveedorSaldo->balance_reconcile =  $proveedorSaldo->balance_balance;
                $validar3 = $proveedorSaldo->update();
                // dd($validar3);
                if ($validar3) {
                    $chequeCancelado = true;
                } else {
                    $chequeCancelado = false;
                }

                //regresamos el saldo de la cuenta de destino
                $cuentaDestinoSaldo = PROC_BALANCE::WHERE('balance_branchKey', '=', $movimientoCancelar->treasuries_branchOffice)->WHERE('balance_account', '=', $movimientoCancelar->treasuries_moneyAccount)->WHERE('balance_companieKey', '=', $movimientoCancelar->treasuries_company)->where('balance_money', '=', $movimientoCancelar->treasuries_money)->first();
                $cuentaDestinoSaldo->balance_balance = $cuentaDestinoSaldo->balance_balance + $movimientoCancelar->treasuries_total;
                $cuentaDestinoSaldo->balance_reconcile =  $cuentaDestinoSaldo->balance_balance;
                $validar4 = $cuentaDestinoSaldo->update();

                $this->actualizarSaldoCuentas($movimientoCancelar->treasuries_moneyAccount, $cuentaDestinoSaldo->balance_companieKey, $cuentaDestinoSaldo->balance_balance, $movimientoCancelar->treasuries_money);

                if ($validar4) {
                    $chequeCancelado = true;
                } else {
                    $chequeCancelado = false;
                }

                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $movimientoCancelar->treasuries_company;
                $auxiliar->assistant_branchKey = $movimientoCancelar->treasuries_branchOffice;
                $auxiliar->assistant_branch = 'CxP';

                $auxiliar->assistant_movement = $movimientoCancelar->treasuries_movement;
                $auxiliar->assistant_movementID = $movimientoCancelar->treasuries_movementID;
                $auxiliar->assistant_module = 'Din';
                $auxiliar->assistant_moduleID = $movimientoCancelar->treasuries_id;
                $auxiliar->assistant_money = $movimientoCancelar->treasuries_money;
                $auxiliar->assistant_typeChange = $movimientoCancelar->treasuries_typeChange;
                $auxiliar->assistant_account = $movimientoCancelar->treasuries_beneficiary;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = null;
                $auxiliar->assistant_payment =  '-' . $movimientoCancelar->treasuries_total;
                $auxiliar->assistant_apply = $movimientoCancelar->treasuries_origin;
                $auxiliar->assistant_applyID =  $movimientoCancelar->treasuries_originID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $movimientoCancelar->treasuries_reference;

                $validar5 = $auxiliar->save();
                if ($validar5) {
                    $chequeCancelado = true;
                } else {
                    $chequeCancelado = false;
                }

                $auxiliar2 = new PROC_ASSISTANT();

                $auxiliar2->assistant_companieKey = $movimientoCancelar->treasuries_company;
                $auxiliar2->assistant_branchKey = $movimientoCancelar->treasuries_branchOffice;
                $auxiliar2->assistant_branch = 'Din';

                $auxiliar2->assistant_movement = $movimientoCancelar->treasuries_movement;
                $auxiliar2->assistant_movementID = $movimientoCancelar->treasuries_movementID;
                $auxiliar2->assistant_module = 'Din';
                $auxiliar2->assistant_moduleID = $movimientoCancelar->treasuries_id;
                $auxiliar2->assistant_money = $movimientoCancelar->treasuries_money;
                $auxiliar2->assistant_typeChange = $movimientoCancelar->treasuries_typeChange;
                $auxiliar2->assistant_account = $movimientoCancelar->treasuries_moneyAccount;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar2->assistant_year = $year;
                $auxiliar2->assistant_period = $period;
                $auxiliar2->assistant_charge = null;
                $auxiliar2->assistant_payment = '-' . $movimientoCancelar->treasuries_total;
                $auxiliar2->assistant_apply = $movimientoCancelar->treasuries_movement;
                $auxiliar2->assistant_applyID =  $movimientoCancelar->treasuries_movementID;
                $auxiliar2->assistant_canceled = 1;
                $auxiliar2->assistant_reference = $movimientoCancelar->treasuries_reference;

                $validar6 = $auxiliar2->save();
                if ($validar6) {
                    $chequeCancelado = true;
                } else {
                    $chequeCancelado = false;
                }

                $flujo->movementFlow_cancelled = 1;
                $validar7 = $flujo->update();
                if ($validar7) {
                    $chequeCancelado = true;
                } else {
                    $chequeCancelado = false;
                }

                $solicitudChequeTes = PROC_TREASURY::where('treasuries_id', '=', $flujo->movementFlow_originID)->first();

                $solicitudChequeTes->treasuries_status = $this->estatus[1];
                $validar8 = $solicitudChequeTes->update();
                if ($validar8) {
                    $chequeCancelado = true;
                } else {
                    $chequeCancelado = false;
                }


                $movimientoCancelar->treasuries_status = $this->estatus[3];
                $cancelado = $movimientoCancelar->update();
                if ($cancelado) {
                    $chequeCancelado = true;
                } else {
                    $chequeCancelado = false;
                }
            } catch (\Exception $e) {
                dd($e);
                $chequeCancelado = false;
                $message = $e->getMessage();
            }
        }

        if ($movimientoCancelar->treasuries_status == $this->estatus[2] && $movimientoCancelar->treasuries_movement == 'Depósito') {

            try {
                $ingresoCancelado = true;
                $chequeCancelado = true;
                $depositoCancelado = false;
                $egresoCancelado = true;
                $transferenciaCancelado = true;


                $flujo = PROC_MOVEMENT_FLOW::WHERE('movementFlow_movementOrigin', '=', $movimientoCancelar->treasuries_origin)->WHERE('movementFlow_destinityID', '=', $movimientoCancelar->treasuries_id)->WHERE('movementFlow_movementDestinity', '=', $movimientoCancelar->treasuries_movement)->WHERE('movementFlow_branch', '=', $movimientoCancelar->treasuries_branchOffice)->first();


                $flujoSolicitudDeposito = PROC_TREASURY::where('treasuries_id', '=', $flujo->movementFlow_originID)->first();

                //vemos el origen de la solicitud de deposito

                if ($flujoSolicitudDeposito->treasuries_origin !== "Factura") {

                    $flujo2 = PROC_MOVEMENT_FLOW::WHERE('movementFlow_originID', '=', $flujo->movementFlow_originID)->WHERE('movementFlow_movementOrigin', '=', $flujo->movementFlow_movementOrigin)->WHERE('movementFlow_movementDestinity', '=', $flujo->movementFlow_movementOrigin)->WHERE('movementFlow_moduleDestiny', '=', 'CxC')->WHERE('movementFlow_branch', '=', $movimientoCancelar->treasuries_branchOffice)->first();

                    //regresamos la cuenta de origen a su estado anterior
                    $solicitudDeposito = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_id', '=', $flujo2->movementFlow_destinityID)->first();
                    $solicitudDeposito->accountsReceivable_status = $this->estatus[1];
                    $validar = $solicitudDeposito->update();

                    if ($validar) {
                        $depositoCancelado = true;
                    } else {
                        $depositoCancelado = false;
                    }

                    $cuentaCxC = new PROC_ACCOUNTS_RECEIVABLE_P();
                    $cuentaCxC->accountsReceivableP_movement = 'Solicitud Depósito';
                    $cuentaCxC->accountsReceivableP_movementID = $solicitudDeposito->accountsReceivable_movementID;
                    $cuentaCxC->accountsReceivableP_issuedate = Carbon::parse($solicitudDeposito->accountsReceivable_issueDate)->format('Y-m-d H:i:s');
                    $cuentaCxC->accountsReceivableP_expiration =  Carbon::parse($solicitudDeposito->accountsReceivable_expiration)->format('Y-m-d H:i:s');

                    //dias credito y moratorio
                    $date = Carbon::now();
                    $emision = $date->format('Y-m-d');
                    $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
                    $vencimiento = Carbon::parse($solicitudDeposito->accountsReceivable_expiration)->format('Y-m-d');
                    $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

                    $diasCredito = $currentDate->diffInDays($shippingDate);
                    $diasMoratorio = $shippingDate->diffInDays($currentDate);
                    $cuentaCxC->accountsReceivableP_creditDays = $diasCredito;
                    $cuentaCxC->accountsReceivableP_moratoriumDays = '-' . $diasMoratorio;
                    $cuentaCxC->accountsReceivableP_money = $solicitudDeposito->accountsReceivable_money;
                    $cuentaCxC->accountsReceivableP_typeChange = $solicitudDeposito->accountsReceivable_typeChange;
                    $cuentaCxC->accountsReceivableP_moneyAccount = $solicitudDeposito->accountsReceivable_moneyAccount;

                    $cuentaCxC->accountsReceivableP_customer = $solicitudDeposito->accountsReceivable_customer;
                    $cuentaCxC->accountsReceivableP_condition = $solicitudDeposito->accountsReceivable_condition;

                    $cuentaCxC->accountsReceivableP_formPayment = $solicitudDeposito->accountsReceivable_paymentMethod;
                    $cuentaCxC->accountsReceivableP_amount = $solicitudDeposito->accountsReceivable_amount;
                    $cuentaCxC->accountsReceivableP_taxes = $solicitudDeposito->accountsReceivable_taxes;
                    $cuentaCxC->accountsReceivableP_total = $solicitudDeposito->accountsReceivable_total;
                    $cuentaCxC->accountsReceivableP_balanceTotal = $solicitudDeposito->accountsReceivable_total;
                    $cuentaCxC->accountsReceivableP_reference = $solicitudDeposito->accountsReceivable_reference;
                    $cuentaCxC->accountsReceivableP_balance = $solicitudDeposito->accountsReceivable_total;
                    $cuentaCxC->accountsReceivableP_company = $solicitudDeposito->accountsReceivable_company;
                    $cuentaCxC->accountsReceivableP_branchOffice = $solicitudDeposito->accountsReceivable_branchOffice;
                    $cuentaCxC->accountsReceivableP_user = $solicitudDeposito->accountsReceivable_user;
                    $cuentaCxC->accountsReceivableP_status = $this->estatus[1];
                    $cuentaCxC->accountsReceivableP_origin = $solicitudDeposito->accountsReceivable_movement;
                    $cuentaCxC->accountsReceivableP_originID = $solicitudDeposito->accountsReceivable_movementID;
                    $cuentaCxC->accountsReceivableP_originType = 'Din';
                    $cuentaCxC->accountsReceivableP_concept = $solicitudDeposito->accountsReceivable_concept;
                    $validar2 = $cuentaCxC->save();
                    if ($validar2) {
                        $depositoCancelado = true;
                    } else {
                        $depositoCancelado = false;
                    }


                    //regresamos el saldo de la cuenta de origen
                    $proveedorSaldo = PROC_BALANCE::WHERE('balance_branchKey', '=', $movimientoCancelar->treasuries_branchOffice)->WHERE('balance_account', '=', $movimientoCancelar->treasuries_beneficiary)->WHERE('balance_companieKey', '=', $movimientoCancelar->treasuries_company)->where('balance_money', '=', $movimientoCancelar->treasuries_money)->WHERE('balance_branch', '=', 'CxC')->first();

                    $proveedorSaldo->balance_balance = $proveedorSaldo->balance_balance + $movimientoCancelar->treasuries_total;
                    $proveedorSaldo->balance_reconcile =  $proveedorSaldo->balance_balance;
                    $validar3 = $proveedorSaldo->update();
                    if ($validar3) {
                        $depositoCancelado = true;
                    } else {
                        $depositoCancelado = false;
                    }

                    //regresamos el saldo de la cuenta de destino
                    $cuentaDestinoSaldo = PROC_BALANCE::WHERE('balance_branchKey', '=', $movimientoCancelar->treasuries_branchOffice)->WHERE('balance_account', '=', $movimientoCancelar->treasuries_moneyAccount)->WHERE('balance_companieKey', '=', $movimientoCancelar->treasuries_company)->where('balance_money', '=', $movimientoCancelar->treasuries_money)->first();

                    $cuentaDestinoSaldo->balance_balance = $cuentaDestinoSaldo->balance_balance - $movimientoCancelar->treasuries_total;
                    $cuentaDestinoSaldo->balance_reconcile =  $cuentaDestinoSaldo->balance_balance;
                    $validar4 = $cuentaDestinoSaldo->update();

                    $this->actualizarSaldoCuentas($movimientoCancelar->treasuries_moneyAccount, $cuentaDestinoSaldo->balance_companieKey, $cuentaDestinoSaldo->balance_balance, $cuentaDestinoSaldo->balance_money);



                    //actualizamos PROC_MONEY_ACCOUNTS
                    if ($validar4) {
                        $depositoCancelado = true;
                    } else {
                        $depositoCancelado = false;
                    }

                    $auxiliar = new PROC_ASSISTANT();

                    $auxiliar->assistant_companieKey = $movimientoCancelar->treasuries_company;
                    $auxiliar->assistant_branchKey = $movimientoCancelar->treasuries_branchOffice;
                    $auxiliar->assistant_branch = 'CxC';

                    $auxiliar->assistant_movement = $movimientoCancelar->treasuries_movement;
                    $auxiliar->assistant_movementID = $movimientoCancelar->treasuries_movementID;
                    $auxiliar->assistant_module = 'Din';
                    $auxiliar->assistant_moduleID = $movimientoCancelar->treasuries_id;
                    $auxiliar->assistant_money = $movimientoCancelar->treasuries_money;
                    $auxiliar->assistant_typeChange = $movimientoCancelar->treasuries_typeChange;
                    $auxiliar->assistant_account = $movimientoCancelar->treasuries_beneficiary;

                    //ponemos fecha del ejercicio
                    $year = Carbon::now()->year;
                    //sacamos el periodo 
                    $period = Carbon::now()->month;


                    $auxiliar->assistant_year = $year;
                    $auxiliar->assistant_period = $period;
                    $auxiliar->assistant_charge = null;
                    $auxiliar->assistant_payment = '-' . $movimientoCancelar->treasuries_total;
                    $auxiliar->assistant_apply = $movimientoCancelar->treasuries_origin;
                    $auxiliar->assistant_applyID =  $movimientoCancelar->treasuries_originID;
                    $auxiliar->assistant_canceled = 1;
                    $auxiliar->assistant_reference = $movimientoCancelar->treasuries_reference;

                    $validar5 = $auxiliar->save();
                    if ($validar5) {
                        $depositoCancelado = true;
                    } else {
                        $depositoCancelado = false;
                    }

                    $auxiliar2 = new PROC_ASSISTANT();

                    $auxiliar2->assistant_companieKey = $movimientoCancelar->treasuries_company;
                    $auxiliar2->assistant_branchKey = $movimientoCancelar->treasuries_branchOffice;
                    $auxiliar2->assistant_branch = 'Din';

                    $auxiliar2->assistant_movement = $movimientoCancelar->treasuries_movement;
                    $auxiliar2->assistant_movementID = $movimientoCancelar->treasuries_movementID;
                    $auxiliar2->assistant_module = 'Din';
                    $auxiliar2->assistant_moduleID = $movimientoCancelar->treasuries_id;
                    $auxiliar2->assistant_money = $movimientoCancelar->treasuries_money;
                    $auxiliar2->assistant_typeChange = $movimientoCancelar->treasuries_typeChange;
                    $auxiliar2->assistant_account = $movimientoCancelar->treasuries_moneyAccount;

                    //ponemos fecha del ejercicio
                    $year = Carbon::now()->year;
                    //sacamos el periodo 
                    $period = Carbon::now()->month;


                    $auxiliar2->assistant_year = $year;
                    $auxiliar2->assistant_period = $period;
                    $auxiliar2->assistant_charge = '-' . $movimientoCancelar->treasuries_total;
                    $auxiliar2->assistant_payment = null;
                    $auxiliar2->assistant_apply = $movimientoCancelar->treasuries_movement;
                    $auxiliar2->assistant_applyID =  $movimientoCancelar->treasuries_movementID;
                    $auxiliar2->assistant_canceled = 1;
                    $auxiliar2->assistant_reference = $movimientoCancelar->treasuries_reference;

                    $validar6 = $auxiliar2->save();
                    if ($validar6) {
                        $depositoCancelado = true;
                    } else {
                        $depositoCancelado = false;
                    }

                    $flujo->movementFlow_cancelled = 1;
                    $validar7 = $flujo->update();
                    if ($validar7) {
                        $depositoCancelado = true;
                    } else {
                        $depositoCancelado = false;
                    }
                } else {
                    //regresamos el saldo de la cuenta de destino
                    $cuentaDestinoSaldo = PROC_BALANCE::WHERE('balance_branchKey', '=', $movimientoCancelar->treasuries_branchOffice)->WHERE('balance_account', '=', $movimientoCancelar->treasuries_moneyAccount)->WHERE('balance_companieKey', '=', $movimientoCancelar->treasuries_company)->where('balance_money', '=', $movimientoCancelar->treasuries_money)->first();

                    $cuentaDestinoSaldo->balance_balance = $cuentaDestinoSaldo->balance_balance - $movimientoCancelar->treasuries_total;
                    $cuentaDestinoSaldo->balance_reconcile =  $cuentaDestinoSaldo->balance_balance;
                    $validar4 = $cuentaDestinoSaldo->update();

                    $this->actualizarSaldoCuentas($movimientoCancelar->treasuries_moneyAccount, $cuentaDestinoSaldo->balance_companieKey, $cuentaDestinoSaldo->balance_balance, $cuentaDestinoSaldo->balance_money);

                    
                    $auxiliar2 = new PROC_ASSISTANT();

                    $auxiliar2->assistant_companieKey = $movimientoCancelar->treasuries_company;
                    $auxiliar2->assistant_branchKey = $movimientoCancelar->treasuries_branchOffice;
                    $auxiliar2->assistant_branch = 'Din';

                    $auxiliar2->assistant_movement = $movimientoCancelar->treasuries_movement;
                    $auxiliar2->assistant_movementID = $movimientoCancelar->treasuries_movementID;
                    $auxiliar2->assistant_module = 'Din';
                    $auxiliar2->assistant_moduleID = $movimientoCancelar->treasuries_id;
                    $auxiliar2->assistant_money = $movimientoCancelar->treasuries_money;
                    $auxiliar2->assistant_typeChange = $movimientoCancelar->treasuries_typeChange;
                    $auxiliar2->assistant_account = $movimientoCancelar->treasuries_moneyAccount;

                    //ponemos fecha del ejercicio
                    $year = Carbon::now()->year;
                    //sacamos el periodo 
                    $period = Carbon::now()->month;


                    $auxiliar2->assistant_year = $year;
                    $auxiliar2->assistant_period = $period;
                    $auxiliar2->assistant_charge = '-' . $movimientoCancelar->treasuries_total;
                    $auxiliar2->assistant_payment = null;
                    $auxiliar2->assistant_apply = $movimientoCancelar->treasuries_movement;
                    $auxiliar2->assistant_applyID =  $movimientoCancelar->treasuries_movementID;
                    $auxiliar2->assistant_canceled = 1;
                    $auxiliar2->assistant_reference = $movimientoCancelar->treasuries_reference;

                    $validar6 = $auxiliar2->save();
                    if ($validar6) {
                        $depositoCancelado = true;
                    } else {
                        $depositoCancelado = false;
                    }

                    $flujo->movementFlow_cancelled = 1;
                    $validar7 = $flujo->update();
                    if ($validar7) {
                        $depositoCancelado = true;
                    } else {
                        $depositoCancelado = false;
                    }
                }


                $solicitudDepositoTes = PROC_TREASURY::where('treasuries_id', '=', $flujo->movementFlow_originID)->first();

                $solicitudDepositoTes->treasuries_status = $this->estatus[1];
                $validar8 = $solicitudDepositoTes->update();
                if ($validar8) {
                    $depositoCancelado = true;
                } else {
                    $depositoCancelado = false;
                }


                $movimientoCancelar->treasuries_status = $this->estatus[3];
                $cancelado = $movimientoCancelar->update();
                if ($cancelado) {
                    $depositoCancelado = true;
                } else {
                    $depositoCancelado = false;
                }
            } catch (\Exception $e) {
                dd($e);
                $depositoCancelado = false;
                $message = $e->getMessage();
            }
        }

        if ($movimientoCancelar->treasuries_status == $this->estatus[2] && $movimientoCancelar->treasuries_movement == 'Egreso') {
            try {
                $ingresoCancelado = true;
                $chequeCancelado = true;
                $depositoCancelado = true;
                $egresoCancelado = false;
                $transferenciaCancelado = true;

                //Verificamos que el ingreso no tenga un origen
                if ($movimientoCancelar->treasuries_origin !== null) {
                    $status = 500;
                    $message = 'No se puede cancelar el Egreso, ya que viene del origen CxP';
                    return response()->json(['mensaje' => $message, 'estatus' => $status]);
                }

                //quitamos el dinero a la cuenta de origen
                $cuentaOrigen = PROC_BALANCE::WHERE('balance_branchKey', '=', $movimientoCancelar->treasuries_branchOffice)->WHERE('balance_account', '=', $movimientoCancelar->treasuries_moneyAccount)->WHERE('balance_companieKey', '=', $movimientoCancelar->treasuries_company)->where('balance_money', '=', $movimientoCancelar->treasuries_money)->first();

                if ($cuentaOrigen !== null && $cuentaOrigen->count() > 0) {
                    $saldoActual =  $cuentaOrigen->balance_balance === Null ? 0 : floatval($cuentaOrigen->balance_balance);
                    $totalIngreso = floatval($movimientoCancelar->treasuries_total);
                    $cuentaOrigen->balance_balance = $saldoActual + $totalIngreso;
                    $cuentaOrigen->balance_reconcile =  $cuentaOrigen->balance_balance;
                    $validar = $cuentaOrigen->update();
                    if ($validar) {
                        $egresoCancelado = true;
                    } else {
                        $egresoCancelado = false;
                    }
                }

                $cuentaOrigen2 = PROC_MONEY_ACCOUNTS_BALANCE::WHERE('moneyAccountsBalance_moneyAccount', '=', $movimientoCancelar->treasuries_moneyAccount)->WHERE('moneyAccountsBalance_company', '=', $movimientoCancelar->treasuries_company)->where('moneyAccountsBalance_money', '=', $movimientoCancelar->treasuries_money)->first();


                if ($cuentaOrigen2 !== null && $cuentaOrigen2->count() > 0) {
                    $saldoActual2 =  $cuentaOrigen2->moneyAccountsBalance_balance === Null ? 0 : floatval($cuentaOrigen2->moneyAccountsBalance_balance);
                    $totalIngreso2 = floatval($movimientoCancelar->treasuries_total);
                    $cuentaOrigen2->moneyAccountsBalance_balance = $saldoActual2 + $totalIngreso2;
                    $validar2 = $cuentaOrigen2->update();
                    if ($validar2) {
                        $egresoCancelado = true;
                    } else {
                        $egresoCancelado = false;
                    }
                }

                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $movimientoCancelar->treasuries_company;
                $auxiliar->assistant_branchKey = $movimientoCancelar->treasuries_branchOffice;
                $auxiliar->assistant_branch = 'Din';

                $auxiliar->assistant_movement = $movimientoCancelar->treasuries_movement;
                $auxiliar->assistant_movementID = $movimientoCancelar->treasuries_movementID;
                $auxiliar->assistant_module = 'Din';
                $auxiliar->assistant_moduleID = $movimientoCancelar->treasuries_id;
                $auxiliar->assistant_money = $movimientoCancelar->treasuries_money;
                $auxiliar->assistant_typeChange = $movimientoCancelar->treasuries_typeChange;
                $auxiliar->assistant_account = $movimientoCancelar->treasuries_moneyAccount;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge =  null;
                $auxiliar->assistant_payment = '-' . $movimientoCancelar->treasuries_total;
                $auxiliar->assistant_apply = $movimientoCancelar->treasuries_movement;
                $auxiliar->assistant_applyID =  $movimientoCancelar->treasuries_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $movimientoCancelar->treasuries_reference;

                $validar3 =  $auxiliar->save();
                if ($validar3) {
                    $egresoCancelado = true;
                } else {
                    $egresoCancelado = false;
                }

                //Cancelamos el movimiento
                $movimientoCancelar->treasuries_status = $this->estatus[3];
                $validar4 =  $movimientoCancelar->update();
                if ($validar4) {
                    $egresoCancelado = true;
                } else {
                    $egresoCancelado = false;
                }
            } catch (\Exception $e) {
                dd($e);
                $egresoCancelado = false;
                $message = $e->getMessage() . ' ' . $e->getLine();
            }
        }

        if ($movimientoCancelar->treasuries_status == $this->estatus[2] &&  $movimientoCancelar->treasuries_movement == 'Traspaso Cuentas') {
            $ingresoCancelado = true;
            $chequeCancelado = true;
            $depositoCancelado = true;
            $egresoCancelado = true;
            $transferenciaCancelado = false;

            $cuentaSaldoOrigin = PROC_MONEY_ACCOUNTS_BALANCE::WHERE('moneyAccountsBalance_company', '=', $movimientoCancelar->treasuries_company)->WHERE('moneyAccountsBalance_status', '=', 'Alta')->WHERE('moneyAccountsBalance_moneyAccount', '=', $movimientoCancelar->treasuries_moneyAccountOrigin)->where('moneyAccountsBalance_money', '=', $movimientoCancelar->treasuries_money)->first();

            $saldoActual =   $cuentaSaldoOrigin->moneyAccountsBalance_balance === Null ? 0 : floatval($cuentaSaldoOrigin->moneyAccountsBalance_balance);
            $total = floatval($movimientoCancelar->treasuries_total);
            $nuevoSaldoOrigin = $saldoActual + $total;

            $cuentaSaldoOrigin->moneyAccountsBalance_balance = $nuevoSaldoOrigin;
            $cuentaSaldoOrigin->update();

            $cuentaBalance = PROC_BALANCE::WHERE('balance_companieKey', '=', $movimientoCancelar->treasuries_company)->WHERE('balance_branchKey', '=', $movimientoCancelar->treasuries_branchOffice)->WHERE('balance_account', '=', $movimientoCancelar->treasuries_moneyAccountOrigin)->where('balance_money', '=', $movimientoCancelar->treasuries_money)->first();

            if ($cuentaBalance) {
                $cuentaBalance->balance_balance = $nuevoSaldoOrigin;
                $cuentaBalance->balance_reconcile = $nuevoSaldoOrigin;
                $cuentaBalance->update();
            } else {
                $cuentaBalance = new PROC_BALANCE();
                $cuentaBalance->balance_companieKey = $movimientoCancelar->treasuries_company;
                $cuentaBalance->balance_branchKey = $movimientoCancelar->treasuries_branchOffice;
                $cuentaBalance->balance_branch = 'Din';
                $cuentaBalance->balance_money = $movimientoCancelar->treasuries_money;
                $cuentaBalance->balance_account = $movimientoCancelar->treasuries_moneyAccountOrigin;
                $cuentaBalance->balance_balance = $nuevoSaldoOrigin;
                $cuentaBalance->balance_reconcile = $nuevoSaldoOrigin;
                $cuentaBalance->save();
            }


            //Banco destino 

            $cuentaSaldoDes = PROC_MONEY_ACCOUNTS_BALANCE::WHERE('moneyAccountsBalance_company', '=', $movimientoCancelar->treasuries_company)->WHERE('moneyAccountsBalance_status', '=', 'Alta')->WHERE('moneyAccountsBalance_moneyAccount', '=', $movimientoCancelar->treasuries_moneyAccountDestiny)->where('moneyAccountsBalance_money', '=', $movimientoCancelar->treasuries_money)->first();
            $saldoActual =   $cuentaSaldoDes->moneyAccountsBalance_balance === Null ? 0 : floatval($cuentaSaldoDes->moneyAccountsBalance_balance);
            $total = floatval($movimientoCancelar->treasuries_total);
            $nuevoSaldoDes = $saldoActual - $total;
            $cuentaSaldoDes->moneyAccountsBalance_initialBalance = null;
            $cuentaSaldoDes->moneyAccountsBalance_balance = $nuevoSaldoDes;
            $cuentaSaldoDes->update();

            $cuentaBalance = PROC_BALANCE::WHERE('balance_companieKey', '=', $movimientoCancelar->treasuries_company)->WHERE('balance_branchKey', '=', $movimientoCancelar->treasuries_branchOffice)->WHERE('balance_account', '=', $movimientoCancelar->treasuries_moneyAccountDestiny)->where('balance_money', '=', $movimientoCancelar->treasuries_money)->first();

            if ($cuentaBalance) {
                $cuentaBalance->balance_balance = $nuevoSaldoDes;
                $cuentaBalance->balance_reconcile = $nuevoSaldoDes;
                $cuentaBalance->update();
            } else {
                $cuentaBalance = new PROC_BALANCE();
                $cuentaBalance->balance_companieKey = $movimientoCancelar->treasuries_company;
                $cuentaBalance->balance_branchKey = $movimientoCancelar->treasuries_branchOffice;
                $cuentaBalance->balance_branch = 'Din';
                $cuentaBalance->balance_money = $movimientoCancelar->treasuries_money;
                $cuentaBalance->balance_account = $movimientoCancelar->treasuries_moneyAccountDestiny;
                $cuentaBalance->balance_balance = $nuevoSaldoDes;
                $cuentaBalance->balance_reconcile = $nuevoSaldoDes;
                $cuentaBalance->save();
            }


            //agregar datos a aux
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $movimientoCancelar->treasuries_company;
            $auxiliar->assistant_branchKey = $movimientoCancelar->treasuries_branchOffice;
            $auxiliar->assistant_branch = 'Din';

            $auxiliar->assistant_movement = $movimientoCancelar->treasuries_movement;
            $auxiliar->assistant_movementID = $movimientoCancelar->treasuries_movementID;
            $auxiliar->assistant_module = 'Din';
            $auxiliar->assistant_moduleID = $movimientoCancelar->treasuries_id;
            $auxiliar->assistant_money = $movimientoCancelar->treasuries_money;
            $auxiliar->assistant_typeChange = $movimientoCancelar->treasuries_typeChange;
            $auxiliar->assistant_account = $movimientoCancelar->treasuries_moneyAccountOrigin;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = null;
            $auxiliar->assistant_payment = "-" . $movimientoCancelar->treasuries_total;
            $auxiliar->assistant_apply = $movimientoCancelar->treasuries_movement;
            $auxiliar->assistant_applyID =  $movimientoCancelar->treasuries_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $movimientoCancelar->treasuries_reference;

            $auxiliar->save();

            $auxiliar2 = new PROC_ASSISTANT();

            $auxiliar2->assistant_companieKey = $movimientoCancelar->treasuries_company;
            $auxiliar2->assistant_branchKey = $movimientoCancelar->treasuries_branchOffice;
            $auxiliar2->assistant_branch = 'Din';

            $auxiliar2->assistant_movement = $movimientoCancelar->treasuries_movement;
            $auxiliar2->assistant_movementID = $movimientoCancelar->treasuries_movementID;
            $auxiliar2->assistant_module = 'Din';
            $auxiliar2->assistant_moduleID = $movimientoCancelar->treasuries_id;
            $auxiliar2->assistant_money = $movimientoCancelar->treasuries_money;
            $auxiliar2->assistant_typeChange = $movimientoCancelar->treasuries_typeChange;
            $auxiliar2->assistant_account = $movimientoCancelar->treasuries_moneyAccountDestiny;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar2->assistant_year = $year;
            $auxiliar2->assistant_period = $period;
            $auxiliar2->assistant_charge = '-' . $movimientoCancelar->treasuries_total;
            $auxiliar2->assistant_payment = null;
            $auxiliar2->assistant_apply = $movimientoCancelar->treasuries_movement;
            $auxiliar2->assistant_applyID =  $movimientoCancelar->treasuries_movementID;
            $auxiliar2->assistant_canceled = 0;
            $auxiliar->assistant_balanceInitial = null;
            $auxiliar->assistant_reference = $movimientoCancelar->treasuries_reference;

            $auxiliar2->save();

            //Cancelamos el movimiento
            $movimientoCancelar->treasuries_status = $this->estatus[3];
            $validar4 =  $movimientoCancelar->update();
            if ($validar4) {
                $transferenciaCancelado = true;
            } else {
                $transferenciaCancelado = false;
            }
        }


        if ($ingresoCancelado === true && $chequeCancelado === true && $depositoCancelado === true && $egresoCancelado === true && $transferenciaCancelado === true) {
            $status = 200;
            $message = 'Proceso cancelado correctamente';
        } else {
            $status = 500;
            $message = 'Error al cancelar el movimiento';
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status]);
    }

    public function getReporteTesoreria($idTesoreria)
    {
        $tesoreria = PROC_TREASURY::join('CAT_PROVIDERS', 'CAT_PROVIDERS.providers_key', '=', 'PROC_TREASURY.treasuries_beneficiary', 'left')
            ->join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_TREASURY.treasuries_beneficiary', 'left outer')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_TREASURY.treasuries_branchOffice', 'left')
            ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_TREASURY.treasuries_company', 'left')
            ->join('CONF_MONEY', 'CONF_MONEY.money_key', '=', 'PROC_TREASURY.treasuries_money', 'left')
            ->join('PROC_BALANCE', 'PROC_TREASURY.treasuries_moneyAccount', '=', 'PROC_BALANCE.balance_account', 'left')
            ->where('treasuries_id', '=', $idTesoreria)
            ->where('PROC_TREASURY.treasuries_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->first();

        // dd($tesoreria);


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

        $tesoreriaDin = PROC_TREASURY::join('CAT_PROVIDERS', 'CAT_PROVIDERS.providers_key', '=', 'PROC_TREASURY.treasuries_beneficiary', 'left')
            ->join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_TREASURY.treasuries_beneficiary', 'left outer')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_TREASURY.treasuries_branchOffice', 'left')
            ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_TREASURY.treasuries_company', 'left')
            ->join('CONF_MONEY', 'CONF_MONEY.money_key', '=', 'PROC_TREASURY.treasuries_money', 'left')
            ->join('PROC_BALANCE', 'PROC_TREASURY.treasuries_moneyAccount', '=', 'PROC_BALANCE.balance_account', 'left')
            ->where('treasuries_id', '=', $idTesoreria)
            ->get()->unique();

        $pdf = PDF::loadview('reportes.tesoreria-reporte', ['tes' => $idTesoreria, 'tesoreria' => $tesoreria, 'logo' => $logoBase64, 'tesoreriaDin' => $tesoreriaDin]);
        $pdf->set_paper('a4', 'landscape');
        return $pdf->stream();
    }

    public function actualizarSaldoCuentas($cuenta, $empresa, $ultimoSaldo, $moneda)
    {
        $validarCuenta = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', $cuenta)->where('moneyAccountsBalance_status', 'Alta')->where("moneyAccountsBalance_company", '=', $empresa)->where("moneyAccountsBalance_money", "=", $moneda)->first();
        $validarCuenta->moneyAccountsBalance_balance = $ultimoSaldo;
        $validarCuenta->update();
    }


    function selectUsuarios()
    {
        $usuarios = User::where('user_status', '=', 'Alta')->get();
        $usuarios_array = array();
        foreach ($usuarios as $usuario) {
            $usuarios_array[$usuario->username] = $usuario->username;
        }
        return $usuarios_array;
    }


    function SelectSucursales()
    {
        $sucursales = CAT_BRANCH_OFFICES::where('branchOffices_status', '=', 'Alta')
            ->where('branchOffices_companyId', '=', session('company')->companies_key)
            ->get();
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

    public function getMonedas2()
    {
        $monedas = CONF_MONEY::where('money_status', '=', 'Alta')->get();
        $monedas_array = array();
        foreach ($monedas as $moneda) {
            $monedas_array[trim($moneda->money_key)] = $moneda->money_name;
        }
        return $monedas_array;
    }


    public function selectCuentasDinero()
    {
        $cuentas = CAT_MONEY_ACCOUNTS::join('CAT_FINANCIAL_INSTITUTIONS', 'CAT_FINANCIAL_INSTITUTIONS.instFinancial_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_bank')
            ->where('CAT_MONEY_ACCOUNTS.moneyAccounts_company', '=', session('company')->companies_key)
            ->where('CAT_MONEY_ACCOUNTS.moneyAccounts_status', '=', 'Alta')
            ->get();
        $cuentas_array = array();
        $cuentas_array['Todos'] = 'Todos';
        foreach ($cuentas as $cuenta) {
            $cuentas_array[$cuenta->moneyAccounts_key] = $cuenta->moneyAccounts_key . ' -' . $cuenta->instFinancial_key;
        };
        return $cuentas_array;
    }

    function getSaldoByCuenta(Request $request)
    {
        $cuentaSaldo = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', '=', $request->cuenta)->select('moneyAccountsBalance_balance')->first();
        return response()->json(['status' => 200, 'data' => $cuentaSaldo]);
    }

    function getNombreCuenta(Request $request)
    {
        $cuentaNombre = CAT_MONEY_ACCOUNTS::join('CAT_FINANCIAL_INSTITUTIONS', 'CAT_MONEY_ACCOUNTS.moneyAccounts_bank', '=', 'CAT_FINANCIAL_INSTITUTIONS.instFinancial_key')->where('moneyAccounts_key', '=', $request->cuenta)->first();
        return response()->json(['status' => 200, 'data' => $cuentaNombre]);
    }

    function formasPago()
    {
        $formasPago = [];
        $formasDePago = CONF_FORMS_OF_PAYMENT::WHERE('formsPayment_status', '=', 'Alta')->get();

        foreach ($formasDePago as $key => $value) {
            $formasPago[trim($value['formsPayment_key'])] = $value['formsPayment_name'];
        }

        return $formasPago;
    }


    public function TesoreriaAction(Request $request)
    {
        $nameFolio = $request->nameFolio;
        $nameMov = $request->nameMov;
        $cuentasDinero = $request->cuentasDinero;
        $status = $request->status;
        $nameFecha = $request->nameFecha;
        $nameUsuario = $request->nameUsuario;
        $nameSucursal = $request->nameSucursal;
        $nameMoneda = $request->nameMoneda;




        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;

        if ($fechaInicio !== null && $fechaFinal !== null && $nameFecha === 'Rango Fechas') {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }

        switch ($request->input('action')) {
            case 'Búsqueda':
                $tesoreria_collection_filtro = PROC_TREASURY::join('CAT_BRANCH_OFFICES', 'PROC_TREASURY.treasuries_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CAT_COMPANIES', 'PROC_TREASURY.treasuries_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('CONF_FORMS_OF_PAYMENT', 'PROC_TREASURY.treasuries_paymentMethod', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key', 'left outer')
                    ->join('CONF_MONEY', 'PROC_TREASURY.treasuries_money', '=', 'CONF_MONEY.money_key')
                    ->join('CAT_MONEY_ACCOUNTS', 'PROC_TREASURY.treasuries_moneyAccount', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_key', 'left outer')
                    ->join('CAT_PROVIDERS', 'PROC_TREASURY.treasuries_beneficiary', '=', 'CAT_PROVIDERS.providers_key', 'left outer')
                    ->join('CAT_CUSTOMERS', 'PROC_TREASURY.treasuries_beneficiary', '=', 'CAT_CUSTOMERS.customers_key', 'left outer')
                    ->where('PROC_TREASURY.treasuries_company', '=', session('company')->companies_key)
                    ->whereTreasuriesMovementID($nameFolio)
                    ->whereTreasuriesMovement($nameMov)
                    ->whereTreasuriesMoneyAccount($cuentasDinero)
                    ->whereTreasuriesStatus($status)
                    ->whereTreasuriesDate($nameFecha)
                    ->whereTreasuriesUser($nameUsuario)
                    ->whereTreasuriesBranchOffice($nameSucursal)
                    ->whereTreasuriesMoney($nameMoneda)
                    ->orderBy('PROC_TREASURY.updated_at', 'desc')
                    ->get();

                // dd($tesoreria_collection_filtro);

                $tesoreria_filtro_array = $tesoreria_collection_filtro->toArray();

                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.modulo.tesoreria.index')
                    ->with('tesoreria_filtro_array', $tesoreria_filtro_array)
                    ->with('nameFolio', $nameFolio)
                    ->with('nameMov', $nameMov)
                    ->with('status', $status)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameUsuario', $nameUsuario)
                    ->with('nameSucursal', $nameSucursal)
                    ->with('nameMoneda', $nameMoneda)
                    ->with('cuentasDinero', $cuentasDinero)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal);

                break;

            case 'Exportar excel':
                $tesoreria = new PROC_TesoreriaExport($nameFolio, $nameMov, $cuentasDinero, $status, $nameFecha, $nameUsuario, $nameSucursal, $nameMoneda);
                return Excel::download($tesoreria, 'tesoreria.xlsx');

                break;

            default:
                break;
        }
    }

    function ayudaTesoreria()
    {
        $folioAfectar = PROC_TREASURY::where('treasuries_id', '=', 47)->first();
    }

    public function getProveedor(Request $request)
    {
        $proveedor = CAT_PROVIDERS::where('providers_key', '=', $request->proveedor)->first();
        return response()->json($proveedor);
    }


    public function eliminarMovimiento(Request $request)
    {

        $ts = PROC_TREASURY::where('treasuries_id', '=', $request->id)->first();
        // // dd($compra);

        // //buscamos sus articulos
        $articulos = PROC_TREASURY_DETAILS::where('treasuriesDetails_treasuriesID', '=', $ts->treasuries_id)->get();

        if ($articulos->count() > 0) {
            //eliminamos sus articulos
            foreach ($articulos as $articulo) {
                $articulosDelete = $articulo->delete();
            }
        } else {
            $articulosDelete = true;
        }


        // // dd($articulos);
        if ($ts->treasuries_status === $this->estatus[0] && $articulosDelete === true) {
            $isDelete = $ts->delete();
        } else {
            $isDelete = false;
        }

        if ($isDelete) {
            $status = 200;
            $message = 'Movimiento eliminado correctamente';
        } else {
            $status = 500;
            $message = 'Error al eliminar el movimiento';
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status]);
    }

    public function getConceptosByMovimiento(Request $request)
    {
        $movimientoSeleccionado = $request->input('movimiento');
        // dd($movimientoSeleccionado);
        if ($movimientoSeleccionado === null) {
            $conceptos = CONF_MODULES_CONCEPT::where('moduleConcept_status', '=', 'Alta')
                ->where('moduleConcept_module', '=', 'Tesorería')
                ->get();
        } else {
            $conceptos = CONF_MODULES_CONCEPT::join('CONF_MODULES_CONCEPT_MOVEMENT', 'CONF_MODULES_CONCEPT_MOVEMENT.moduleMovement_conceptID', '=', 'CONF_MODULES_CONCEPT.moduleConcept_id')
                ->where('moduleConcept_status', '=', 'Alta')
                ->where('moduleConcept_module', '=', 'Tesorería')
                ->where('moduleMovement_movementName', '=', $movimientoSeleccionado)
                ->get();
        }
        return response()->json($conceptos);
    }

    public function actualizarFolio($tipoMovimiento, $folioAfectar)
    {
        switch ($tipoMovimiento) {
            case 'Traspaso Cuentas':
                $consecutivoColumn = 'generalConsecutives_consTransferT';
                break;
            case 'Egreso':
                $consecutivoColumn = 'generalConsecutives_consEgress';
                break;
            case 'Transferencia Electrónica':
                $folioMov = PROC_TREASURY::WHERE('treasuries_movement', '=', 'Transferencia Electrónica')->WHERE('treasuries_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->max('treasuries_movementID');

                $folioMov = $folioMov == null ? 1 : $folioMov + 1;
                $folioAfectar->treasuries_movementID = $folioMov;
                $folioAfectar->update();
                break;

            case 'Depósito':
                $folioMov = PROC_TREASURY::WHERE('treasuries_movement', '=', 'Depósito')->WHERE('treasuries_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->max('treasuries_movementID');

                $folioMov = $folioMov == null ? 1 : $folioMov + 1;
                $folioAfectar->treasuries_movementID = $folioMov;
                $folioAfectar->update();
                break;

            case 'Ingreso':
                $folioMov = PROC_TREASURY::WHERE('treasuries_movement', '=', 'Ingreso')->WHERE('treasuries_branchOffice', '=', $folioAfectar->treasuries_branchOffice)->max('treasuries_movementID');

                $folioMov = $folioMov == null ? 1 : $folioMov + 1;
                $folioAfectar->treasuries_movementID = $folioMov;
                $folioAfectar->update();
                break;
        }

        if (isset($consecutivoColumn)) {
            // Obtén el valor actual de la columna de consecutivo
            $consecutivo = DB::table('CONF_GENERAL_PARAMETERS_CONSECUTIVES')
                ->where('generalConsecutives_company', session('company')->companies_key)
                ->where('generalConsecutives_branchOffice', session('sucursal')->branchOffices_key)
                ->value($consecutivoColumn);

            if ($consecutivo === null || $consecutivo === 0 || $consecutivo === '0') {
                // Si el valor es nulo o cero, realiza la lógica anterior para obtener el consecutivo
                $folioOrden = PROC_TREASURY::where('treasuries_movement', '=', $tipoMovimiento)
                    ->where('treasuries_branchOffice', '=', $folioAfectar->treasuries_branchOffice)
                    ->max('treasuries_movementID');
                $folioOrden = $folioOrden == null ? 1 : $folioOrden + 1;
                $folioAfectar->treasuries_movementID = $folioOrden;
                $folioAfectar->update();

                $config_parameters = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
                //como no existe el consecutivo lo creamos
                $config_consecutives_parameters = CONF_GENERAL_PARAMETERS_CONSECUTIVES::where('generalConsecutives_company', '=', session('company')->companies_key)
                    ->where('generalConsecutives_branchOffice', '=', session('sucursal')->branchOffices_key)
                    ->first();
                // dd($config_consecutives_parameters);
                if ($config_consecutives_parameters == null) {
                    $config_consecutives_parameters = new CONF_GENERAL_PARAMETERS_CONSECUTIVES();
                    // dd($config_consecutives_parameters);
                } else {
                    $config_consecutives_parameters;
                }

                //ahora si creamos el consecutivo
                //el generalConsecutives_generalParametersID será el id de la tabla CONF_GENERAL_PARAMETERS
                $config_consecutives_parameters->generalConsecutives_generalParametersID = $config_parameters->generalParameters_id;
                $config_consecutives_parameters->generalConsecutives_company = session('company')->companies_key;
                $config_consecutives_parameters->generalConsecutives_branchOffice = session('sucursal')->branchOffices_key;
                $config_consecutives_parameters->$consecutivoColumn = $folioOrden;
                $config_consecutives_parameters->save();
                //todo esto lo ponemos en una funcion para que se pueda reutilizar
                // $this->actualizarParametros($tipoMovimiento, $folioAfectar);
            } else {
                // Utiliza el valor incrementado del consecutivo en tu lógica
                DB::table('CONF_GENERAL_PARAMETERS_CONSECUTIVES')
                    ->where('generalConsecutives_company', session('company')->companies_key)
                    ->where('generalConsecutives_branchOffice', session('sucursal')->branchOffices_key)
                    ->update([$consecutivoColumn => $consecutivo + 1]);

                $folioAfectar->treasuries_movementID = $consecutivo + 1;
                $folioAfectar->update();
            }
        }
    }
}
