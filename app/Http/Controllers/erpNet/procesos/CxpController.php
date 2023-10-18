<?php

namespace App\Http\Controllers\erpNet\procesos;

use App\Exports\PROC_CXPExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
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
use App\Models\modulos\PROC_ACCOUNTS_PAYABLE_DETAILS;
use App\Models\modulos\PROC_ACCOUNTS_PAYABLE_P;
use App\Models\modulos\PROC_ASSISTANT;
use App\Models\modulos\PROC_BALANCE;
use App\Models\modulos\PROC_EXPENSES;
use App\Models\modulos\PROC_MOVEMENT_FLOW;
use App\Models\modulos\PROC_PURCHASE;
use App\Models\modulos\PROC_TREASURY;
use App\Models\modulos\PROC_TREASURY_DETAILS;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Termwind\Components\Dd;

class CxpController extends Controller
{

    public $estatus = [
        0 => 'INICIAL',
        1 => 'POR AUTORIZAR',
        2 => 'FINALIZADO',
        3 => 'CANCELADO',
    ];

    public $movimientos2 = [
        'Entrada por Compra' => 'Entrada por Compra',
        'Factura de Gasto' => 'Factura de Gasto',
        'Sol. de Cheque/Transferencia' => 'Sol. de Cheque/Transferencia',
    ];

    public function index(Request $request)
    {
        $status = $request->status;

        $fecha_actual = Carbon::now()->format('Y-m-d');
        $select_users = $this->selectUsuarios();
        $select_sucursales = $this->selectSucursales();
        $selectMonedas = $this->getMonedas();
        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }

        $cuentasxpagar = PROC_ACCOUNTS_PAYABLE::join('CAT_PROVIDERS', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_provider', '=', 'CAT_PROVIDERS.providers_key', 'left outer')
            ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key', 'left outer')
            ->join('CONF_CREDIT_CONDITIONS', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id', 'left outer')
            ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_company', '=', 'CAT_COMPANIES.companies_key', 'left outer')
            ->join('CONF_FORMS_OF_PAYMENT', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_formPayment', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key', 'left outer')
            ->where('PROC_ACCOUNTS_PAYABLE.accountsPayable_company', '=', session('company')->companies_key)
            ->where('PROC_ACCOUNTS_PAYABLE.accountsPayable_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->where('PROC_ACCOUNTS_PAYABLE.accountsPayable_user', '=', Auth::user()->username)
            ->where('PROC_ACCOUNTS_PAYABLE.accountsPayable_status', '=',  "POR AUTORIZAR")
            ->when($parametro->generalParameters_defaultMoney, function ($query, $parametro) {
                return $query->where('PROC_ACCOUNTS_PAYABLE.accountsPayable_money', '=', $parametro);
            }, function ($query) {
                return $query;
            })
            ->orderBy('PROC_ACCOUNTS_PAYABLE.updated_at', 'DESC')
            // ->where('balance_branch', '=', 'CxP')
            // ->where('PROC_ACCOUNTS_PAYABLE.accountsPayable_status', '=', $this->estatus[1])
            ->get()->unique();
        // DD($cuentasxpagar);





        return view('page.modulos.Gestion_y_Finanzas.Cuentas _por_Pagar.index-cxp', compact('fecha_actual', 'select_users', 'select_sucursales', 'selectMonedas', 'parametro', 'cuentasxpagar'));
    }

    public function create(Request $request)
    {

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        $empresaSucursales = CAT_BRANCH_OFFICES::WHERE('branchOffices_companyId', '=', session('company')->companies_key)->get();
        if ($parametro->generalParameters_defaultMoney == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de seleccionar la moneda por defecto');
        }

        try {
            //Obtenemos los permisos que tiene el usuario para el modulo de compras
            $usuario = Auth::user();
            $permisos = $usuario->getAllPermissions()->where('categoria', '=', 'Cuentas por pagar')->pluck('name')->toArray();

            $movimientos = [];

            if (count($permisos) > 0) {
                foreach ($permisos as $movimiento) {
                    $mov = substr($movimiento, 0, -2);
                    $letra = substr($movimiento, -1);
                    if ($letra === 'E') {
                        if (!array_key_exists($mov, $movimientos)) {
                            $movimientos[$mov] = $mov;
                        }
                    }
                }
            }

            $selectMonedas = $this->getMonedas2();
            $select_conceptos = CONF_MODULES_CONCEPT::WHERE('moduleConcept_status', '=', 'Alta')->WHERE('moduleConcept_module', '=', 'Cuentas por Pagar')->get();
            $fecha_actual = Carbon::now()->format('Y-m-d');
            $proveedores = CAT_PROVIDERS::WHERE('providers_status', '=', 'Alta')->get();
            $parametro = CONF_GENERAL_PARAMETERS::join('CONF_MONEY', 'CONF_GENERAL_PARAMETERS.generalParameters_defaultMoney', '=', 'CONF_MONEY.money_key')
                ->select('CONF_GENERAL_PARAMETERS.*', 'CONF_MONEY.money_change')
                ->where('CONF_GENERAL_PARAMETERS.generalParameters_company', '=', session('company')->companies_key)
                ->first();
            $moneyAccounts = CAT_MONEY_ACCOUNTS::join('CAT_FINANCIAL_INSTITUTIONS', 'CAT_FINANCIAL_INSTITUTIONS.instFinancial_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_bank')
                ->where('CAT_MONEY_ACCOUNTS.moneyAccounts_status', '=', 'Alta')
                ->where('CAT_MONEY_ACCOUNTS.moneyAccounts_company', '=', session('company')->companies_key)
                ->get();
            $proveedores = CAT_PROVIDERS::WHERE('providers_status', '=', 'Alta')->get();
            $aplica = PROC_ACCOUNTS_PAYABLE::WHERE('accountsPayable_status', '=', 'POR AUTORIZAR')->get();
            $select_formaPago = CONF_FORMS_OF_PAYMENT::WHERE('formsPayment_status', '=', 'Alta')->get();

            //Verificamos si recibimos un id en la url
            if (isset($request->id) && $request->id != 0) {
                $cxp = PROC_ACCOUNTS_PAYABLE::find($request->id);

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
                    $cxp->accountsPayable_movement === 'Anticipo' || $cxp->accountsPayable_movement === 'Aplicación' ||
                    $cxp->accountsPayable_movement === 'Pago de Facturas'
                ) {
                    //Validamos si el usuario tiene permiso de ver los movimientos ya creados
                    if (!array_key_exists($cxp->accountsPayable_movement, $movimientos)) {
                        return redirect()->route('vista.modulo.cuentasPagar.index')->with('status', false)->with('message', 'No tiene permisos para visualizar este movimiento');
                    }
                }


                //  dd($cxp);
                $tipoCuenta = CAT_MONEY_ACCOUNTS::WHERE('moneyAccounts_key', '=', $cxp->accountsPayable_moneyAccount)->select('moneyAccounts_accountType', 'moneyAccounts_money')->first();

                $cxpDetails = PROC_ACCOUNTS_PAYABLE_DETAILS::WHERE('accountsPayableDetails_accountPayableID', '=', $request->id)->get();

                //Obtenemos las entradas o gastos que hacen referencia a esta cuenta por pagar
                $comprasMov = [];

                foreach ($cxpDetails as $cxpD) {
                    $cxpMovmiento = PROC_ACCOUNTS_PAYABLE::WHERE('accountsPayable_id', '=', $cxpD->accountsPayableDetails_movReference)->first();
                    $comprasMov[$cxpMovmiento->accountsPayable_id] =  $cxpMovmiento->accountsPayable_balance;
                }

                $primerFlujodeCXP = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
                    ->WHERE('movementFlow_originID', '=', $cxp->accountsPayable_id)
                    ->WHERE('movementFlow_movementOriginID', '=', $cxp->accountsPayable_movementID)
                    ->WHERE('movementFlow_moduleOrigin', '=', 'CxP')
                    ->get();

                if (count($primerFlujodeCXP) === 0) {
                    $primerFlujodeCXP = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
                        ->WHERE('movementFlow_destinityID', '=', $cxp->accountsPayable_id)
                        ->WHERE('movementFlow_movementDestinityID', '=', $cxp->accountsPayable_movementID)
                        ->WHERE('movementFlow_moduleDestiny', '=', 'CxP')
                        ->get();
                }

                $infoProveedor = CAT_PROVIDERS::join('CONF_CREDIT_CONDITIONS', 'CAT_PROVIDERS.providers_creditCondition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id', 'left outer')->where('providers_key', '=', $cxp->accountsPayable_provider)->first();

                // dd($infoProveedor);
                $monedasMov = CONF_MONEY::where('money_status', '=', 'Alta')->orderBy('money_key', 'desc')->get();


                $movimientosProveedor = PROC_ACCOUNTS_PAYABLE::whereIn("accountsPayable_movement", ["Entrada por Compra", "Factura de Gasto", "Anticipo"])->where('accountsPayable_provider', '=', $cxp->accountsPayable_provider)->where("accountsPayable_balance", ">", '0')->where("accountsPayable_company", "=", session('company')->companies_key)->where("accountsPayable_status", "=", 'POR AUTORIZAR')->get();



                // dd($movimientosProveedor);

                $saldoGeneral = 0;
                foreach ($movimientosProveedor as $movimiento) {
                    if ($movimiento->accountsPayable_money == session('generalParameters')->generalParameters_defaultMoney) {

                        if ($movimiento->accountsPayable_movement !== 'Anticipo') {
                            $saldoGeneral += $movimiento->accountsPayable_balance;
                        } else {
                            $saldoGeneral -= $movimiento->accountsPayable_balance;
                        }
                    } else {
                        $monedaActual = CONF_MONEY::where('money_key', '=', $movimiento->accountsPayable_money)->first();
                        if ($movimiento->accountsPayable_movement !== 'Anticipo') {
                            $saldoGeneral += ($movimiento->accountsPayable_balance * $monedaActual->money_change);
                        } else {
                            $saldoGeneral -= ($movimiento->accountsPayable_balance * $monedaActual->money_change);
                        }
                    }
                }

                switch ($cxp->accountsPayable_movement) {
                    case 'Anticipo':
                        return view('page.modulos.Gestion_y_Finanzas.Cuentas _por_Pagar.create-cxp', compact('selectMonedas', 'fecha_actual', 'proveedores', 'parametro', 'moneyAccounts', 'proveedores', 'select_formaPago', 'select_conceptos', 'aplica', 'cxp', 'tipoCuenta', 'movimientos', 'primerFlujodeCXP', "infoProveedor", "monedasMov", "movimientosProveedor", "saldoGeneral", 'empresaSucursales'));
                        break;

                    case 'Aplicación':
                        return view('page.modulos.Gestion_y_Finanzas.Cuentas _por_Pagar.create-cxp', compact('selectMonedas', 'fecha_actual', 'proveedores', 'parametro', 'moneyAccounts', 'proveedores', 'select_formaPago', 'select_conceptos', 'aplica', 'cxp', 'tipoCuenta', 'movimientos', 'cxpDetails', 'primerFlujodeCXP',  "infoProveedor", "monedasMov", "movimientosProveedor", "saldoGeneral", "comprasMov", 'empresaSucursales'));
                        break;


                    case 'Factura de Gasto':
                        $select_conceptos = CONF_MODULES_CONCEPT::WHERE('moduleConcept_status', '=', 'Alta')->get();
                        $movimientos = array_merge($movimientos, $this->movimientos2);
                        return view('page.modulos.Gestion_y_Finanzas.Cuentas _por_Pagar.create-cxp', compact('selectMonedas', 'fecha_actual', 'proveedores', 'parametro', 'moneyAccounts', 'proveedores', 'select_formaPago', 'select_conceptos', 'aplica', 'cxp', 'tipoCuenta', 'movimientos', 'primerFlujodeCXP',  "infoProveedor", "monedasMov", "movimientosProveedor", "saldoGeneral", 'empresaSucursales'));
                        break;

                    case 'Entrada por Compra':
                        $select_conceptos = CONF_MODULES_CONCEPT::WHERE('moduleConcept_status', '=', 'Alta')->get();
                        $movimientos = array_merge($movimientos, $this->movimientos2);

                        return view('page.modulos.Gestion_y_Finanzas.Cuentas _por_Pagar.create-cxp', compact('selectMonedas', 'fecha_actual', 'proveedores', 'parametro', 'moneyAccounts', 'proveedores', 'select_formaPago', 'select_conceptos', 'aplica', 'cxp', 'tipoCuenta', 'movimientos', 'primerFlujodeCXP',  "infoProveedor", "monedasMov", "movimientosProveedor", "saldoGeneral", 'empresaSucursales'));
                        break;

                    case 'Sol. de Cheque/Transferencia':
                        $movimientos = array_merge($movimientos, $this->movimientos2);
                        return view('page.modulos.Gestion_y_Finanzas.Cuentas _por_Pagar.create-cxp', compact('selectMonedas', 'fecha_actual', 'proveedores', 'parametro', 'moneyAccounts', 'proveedores', 'select_formaPago', 'select_conceptos', 'aplica', 'cxp', 'tipoCuenta', 'movimientos', "primerFlujodeCXP",  "infoProveedor", "monedasMov", "movimientosProveedor", "saldoGeneral", 'empresaSucursales'));
                        break;

                    default:
                        return view('page.modulos.Gestion_y_Finanzas.Cuentas _por_Pagar.create-cxp', compact('selectMonedas', 'fecha_actual', 'proveedores', 'parametro', 'moneyAccounts', 'proveedores', 'select_formaPago', 'select_conceptos', 'aplica', 'cxp', 'tipoCuenta', 'movimientos', 'cxpDetails', 'primerFlujodeCXP', "infoProveedor", "monedasMov", "movimientosProveedor", "saldoGeneral", "comprasMov", 'empresaSucursales'));
                        break;
                }
            }


            return view('page.modulos.Gestion_y_Finanzas.Cuentas _por_Pagar.create-cxp', compact('selectMonedas', 'fecha_actual', 'proveedores', 'parametro', 'moneyAccounts', 'proveedores', 'select_formaPago', 'select_conceptos', 'aplica', 'movimientos', 'empresaSucursales'));
        } catch (\Exception $e) {
            return redirect()->route('vista.modulo.cuentasPagar.index')->with('status', false)->with('message', 'El movimiento no se ha encontrado');
        }
    }

    public function store(Request $request)
    {
        $cxp_request = $request->except('_token');
        // dd(gettype($cxp_request['totalCompleto']));
        //   dd($cxp_request);

        $id = $request->id;
        $copiaRequest = $request->copiar;
        $tipoMovimiento = $request->movimientos;

        try {
            switch ($tipoMovimiento) {

                case 'Anticipo':
                    if ($request->origin === null) {
                        if ($id == 0 || $copiaRequest == 'copiar') {
                            $cxp = new PROC_ACCOUNTS_PAYABLE();
                        } else {
                            $cxp = PROC_ACCOUNTS_PAYABLE::find($id);
                        }
                        $cxp->accountsPayable_movement = $tipoMovimiento;
                        $cxp->accountsPayable_issuedate
                            = \Carbon\Carbon::now();
                        $cxp->accountsPayable_money =  $cxp_request['nameMoneda'];
                        $cxp->accountsPayable_typeChange =  $cxp_request['nameTipoCambio'];
                        $cxp->accountsPayable_moneyAccount =  $cxp_request['cuentaKey'];
                        $cxp->accountsPayable_provider =  $cxp_request['proveedorKey'];
                        $cxp->accountsPayable_formPayment =  $cxp_request['proveedorFormaPago'];
                        $cxp->accountsPayable_observations =  $cxp_request['observaciones'];
                        $cxp->accountsPayable_reference =  $cxp_request['referencia'];
                        $cxp->accountsPayable_amount =  str_replace(['$', ','], '', $cxp_request['importe']); //dinero
                        $cxp->accountsPayable_taxes =   str_replace(['$', ','], '', $cxp_request['impuesto']); //dinero
                        $cxp->accountsPayable_total =  str_replace(['$', ','], '', $cxp_request['importeTotal']); //dinero
                        $cxp->accountsPayable_concept =  $cxp_request['concepto'];
                        $cxp->accountsPayable_status = $this->estatus[0];
                        $cxp->accountsPayable_company = session('company')->companies_key;
                        $cxp->accountsPayable_branchOffice = session('sucursal')->branchOffices_key;
                        $cxp->accountsPayable_user = Auth::user()->username;

                        $cxp->created_at = Carbon::now()->format('Y-m-d H:i:s');
                        $cxp->updated_at = Carbon::now()->format('Y-m-d H:i:s');

                        $isCreate = $cxp->save();
                        $lastId = $id == 0 ? PROC_ACCOUNTS_PAYABLE::latest('accountsPayable_id')->first()->accountsPayable_id : $id;

                        if ($isCreate) {
                            $message = $id == 0 ? 'Anticipo creado correctamente' : 'Anticipo actualizado correctamente';
                            $status = true;
                        } else {
                            $message = $id == 0 ? 'Error al crear el anticipo ' : 'Error al actualizar el anticipo ';
                            $status = false;
                        }

                        return redirect()->route('vista.modulo.cuentasPagar.create-cxp', $id == 0 ? $lastId : $id)->with('message', $message)->with('status', $status);
                    } else {

                        $cxp = new PROC_ACCOUNTS_PAYABLE();
                        $cxp->accountsPayable_movement = $cxp_request['origin'];
                        $cxp->accountsPayable_issuedate
                            = \Carbon\Carbon::now();
                        $cxp->accountsPayable_money =  $cxp_request['nameMoneda'];
                        $cxp->accountsPayable_typeChange =  $cxp_request['nameTipoCambio'];
                        $cxp->accountsPayable_moneyAccount =  $cxp_request['cuentaKey'];
                        $cxp->accountsPayable_provider =  $cxp_request['proveedorKey'];
                        $cxp->accountsPayable_formPayment =  $cxp_request['proveedorFormaPago'];
                        $cxp->accountsPayable_observations =  $cxp_request['observaciones'];
                        $cxp->accountsPayable_reference =  $tipoMovimiento . ' ' . $cxp_request['folio'];
                        $cxp->accountsPayable_amount =  str_replace(['$', ','], '', $cxp_request['importe']); //dinero
                        $cxp->accountsPayable_taxes =   str_replace(['$', ','], '', $cxp_request['impuesto']); //dinero
                        $cxp->accountsPayable_total =  str_replace(['$', ','], '', $cxp_request['importeTotal']); //dinero
                        $cxp->accountsPayable_concept =  $cxp_request['concepto'];
                        $cxp->accountsPayable_status = $this->estatus[0];
                        $cxp->accountsPayable_company = session('company')->companies_key;
                        $cxp->accountsPayable_branchOffice = session('sucursal')->branchOffices_key;
                        $cxp->accountsPayable_user = Auth::user()->username;
                        $cxp->accountsPayable_originType = 'CxP';
                        $cxp->accountsPayable_origin = $tipoMovimiento;
                        $cxp->accountsPayable_originID = $cxp_request['folio'];
                        $cxp->created_at = Carbon::now()->format('Y-m-d H:i:s');
                        $cxp->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                        $isCreate = $cxp->save();
                        $lastId = PROC_ACCOUNTS_PAYABLE::latest('accountsPayable_id')->first()->accountsPayable_id;

                        if ($isCreate) {
                            $message = 'La Apliación se ha creado correctamente';
                            $status = true;
                        } else {
                            $message = 'Error al crear la Aplicación ';
                            $status = false;
                        }

                        return redirect()->route('vista.modulo.cuentasPagar.create-cxp', $lastId)->with('message', $message)->with('status', $status);
                    }
                    break;

                    //-----------------------------------------------------
                case 'Pago de Facturas':
                    if ($id == 0 || $copiaRequest == 'copiar') {
                        $cxp = new PROC_ACCOUNTS_PAYABLE();
                    } else {
                        $cxp = PROC_ACCOUNTS_PAYABLE::find($id);
                    }
                    $cxp->accountsPayable_movement = $tipoMovimiento;
                    $cxp->accountsPayable_issuedate
                        = \Carbon\Carbon::now();
                    $cxp->accountsPayable_money =  $cxp_request['nameMoneda'];
                    $cxp->accountsPayable_typeChange =  $cxp_request['nameTipoCambio'];
                    $cxp->accountsPayable_moneyAccount =  $cxp_request['cuentaKey'];
                    $cxp->accountsPayable_provider =  $cxp_request['proveedorKey'];
                    $cxp->accountsPayable_formPayment =  $cxp_request['proveedorFormaPago'];
                    $cxp->accountsPayable_observations =  $cxp_request['observaciones'];
                    $cxp->accountsPayable_reference =  $cxp_request['referencia'];
                    $cxp->accountsPayable_amount =  str_replace(['$', ','], '', $cxp_request['importe']); //dinero
                    $cxp->accountsPayable_taxes =   str_replace(['$', ','], '', $cxp_request['impuesto']); //dinero
                    $cxp->accountsPayable_total =  str_replace(['$', ','], '', $cxp_request['importeTotal']); //dinero
                    $cxp->accountsPayable_concept =  $cxp_request['concepto'];
                    $cxp->accountsPayable_status = $this->estatus[0];
                    $cxp->accountsPayable_company = session('company')->companies_key;
                    $cxp->accountsPayable_branchOffice = session('sucursal')->branchOffices_key;
                    $cxp->accountsPayable_user = Auth::user()->username;

                    $cxp->created_at = Carbon::now()->format('Y-m-d H:i:s');
                    $cxp->updated_at = Carbon::now()->format('Y-m-d H:i:s');

                    $detalleCxP =  $cxp_request['dataArticulosJson'];
                    $detalleCxp = json_decode($detalleCxP, true);
                    $claveDet = array_keys($detalleCxp);

                    $detallesDelete = json_decode($cxp_request['dataArticulosDelete'], true);


                    if ($detallesDelete  != null) {
                        foreach ($detallesDelete as $detalle) {
                            $detalleCxP = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_id', $detalle)->first();
                            $detalleCxP->delete();
                        }
                    }

                    $isCreate = $cxp->save();
                    $lastId = $id == 0 ? PROC_ACCOUNTS_PAYABLE::latest('accountsPayable_id')->first()->accountsPayable_id : $id;

                    if ($detalleCxP !== null) {
                        foreach ($claveDet as $detalle) {
                            if (isset($detalleCxp[$detalle]['id'])) {
                                $detalleInsert = PROC_ACCOUNTS_PAYABLE_DETAILS::find($detalleCxp[$detalle]['id']);
                            } else {
                                $detalleInsert = new PROC_ACCOUNTS_PAYABLE_DETAILS();
                            }
                            $detalleInsert->accountsPayableDetails_apply = $detalleCxp[$detalle]['aplicaSelect'];
                            $detalleInsert->accountsPayableDetails_accountPayableID =  $lastId;
                            $detalleInsert->accountsPayableDetails_applyIncrement = $detalleCxp[$detalle]['aplicaConsecutivo'];
                            $detalleInsert->accountsPayableDetails_amount = str_replace(['$', ','], '', $detalleCxp[$detalle]['importe']);
                            $detalleInsert->accountsPayableDetails_company = session('company')->companies_key;
                            $detalleInsert->accountsPayableDetails_branchOffice = session('sucursal')->branchOffices_key;
                            $detalleInsert->accountsPayableDetails_user = Auth::user()->username;
                            $detalleInsert->accountsPayableDetails_movReference = $detalleCxp[$detalle]['movID'];
                            $detalleInsert->save();
                        }
                    }

                    if ($isCreate) {
                        $message = $id == 0 ? 'El Pago se ha creado correctamente' : 'Pago actualizado correctamente';
                        $status = true;
                    } else {
                        $message = $id == 0 ? 'Error al crear el pago ' : 'Error al actualizar el pago ';
                        $status = false;
                    }

                    return redirect()->route('vista.modulo.cuentasPagar.create-cxp', $id == 0 ? $lastId : $id)->with('message', $message)->with('status', $status);
                    break;
                    //-----------------------------------------------------
                case 'Aplicación':
                    if ($id == 0 || $copiaRequest == 'copiar') {
                        $cxp = new PROC_ACCOUNTS_PAYABLE();
                    } else {
                        $cxp = PROC_ACCOUNTS_PAYABLE::find($id);
                    }

                    $cxp_request['impuesto'] == null ? $cxp_request['impuesto'] = 0.00 : $cxp_request['impuesto'] = $cxp_request['impuesto'];


                    $cxp->accountsPayable_movement = $tipoMovimiento;
                    $cxp->accountsPayable_issuedate
                        = \Carbon\Carbon::now();
                    $cxp->accountsPayable_money =  $cxp_request['nameMoneda'];
                    $cxp->accountsPayable_typeChange =  $cxp_request['nameTipoCambio'];
                    $cxp->accountsPayable_moneyAccount =  $cxp_request['cuentaKey'];
                    $cxp->accountsPayable_provider =  $cxp_request['proveedorKey'];
                    $cxp->accountsPayable_formPayment =  $cxp_request['proveedorFormaPago'];
                    $cxp->accountsPayable_observations =  $cxp_request['observaciones'];
                    $cxp->accountsPayable_reference =  $cxp_request['referencia'];
                    $cxp->accountsPayable_amount =  str_replace(['$', ','], '', $cxp_request['importe']); //dinero
                    $cxp->accountsPayable_taxes =   str_replace(['$', ','], '',  $cxp_request['impuesto']); //dinero
                    $cxp->accountsPayable_total =  str_replace(['$', ','], '', $cxp_request['importeTotal']); //dinero
                    $cxp->accountsPayable_concept =  $cxp_request['concepto'];
                    $cxp->accountsPayable_status = $this->estatus[0];
                    $cxp->accountsPayable_company = session('company')->companies_key;
                    $cxp->accountsPayable_branchOffice = session('sucursal')->branchOffices_key;
                    $cxp->accountsPayable_user = Auth::user()->username;
                    $divisores = explode(" ", $cxp_request['anticiposKey']);
                    $cxp->accountsPayable_originType = 'CxP';
                    $cxp->accountsPayable_origin = $divisores[0];
                    $cxp->accountsPayable_originID = $divisores[1];

                    $detalleCxP =  $cxp_request['dataArticulosJson'];
                    $detalleCxp = json_decode($detalleCxP, true);
                    $claveDet = array_keys($detalleCxp);

                    $cxp->created_at = Carbon::now()->format('Y-m-d H:i:s');
                    $cxp->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                    $detallesDelete = json_decode($cxp_request['dataArticulosDelete'], true);


                    if ($detallesDelete  != null) {
                        foreach ($detallesDelete as $detalle) {
                            $detalleCxP = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_id', $detalle)->first();
                            $detalleCxP->delete();
                        }
                    }

                    $isCreate = $cxp->save();
                    $lastId = $id == 0 ? PROC_ACCOUNTS_PAYABLE::latest('accountsPayable_id')->first()->accountsPayable_id : $id;

                    if ($detalleCxP !== null) {
                        foreach ($claveDet as $detalle) {
                            if (isset($detalleCxp[$detalle]['id'])) {
                                $detalleInsert = PROC_ACCOUNTS_PAYABLE_DETAILS::find($detalleCxp[$detalle]['id']);
                            } else {
                                $detalleInsert = new PROC_ACCOUNTS_PAYABLE_DETAILS();
                            }
                            $detalleInsert->accountsPayableDetails_apply = $detalleCxp[$detalle]['aplicaSelect'];
                            $detalleInsert->accountsPayableDetails_accountPayableID =  $lastId;
                            $detalleInsert->accountsPayableDetails_applyIncrement = $detalleCxp[$detalle]['aplicaConsecutivo'];
                            $detalleInsert->accountsPayableDetails_amount =  str_replace(['$', ','], '', $detalleCxp[$detalle]['importe']);
                            $detalleInsert->accountsPayableDetails_company = session('company')->companies_key;
                            $detalleInsert->accountsPayableDetails_branchOffice = session('sucursal')->branchOffices_key;
                            $detalleInsert->accountsPayableDetails_user = Auth::user()->username;
                            $detalleInsert->accountsPayableDetails_movReference = $detalleCxp[$detalle]['movID'];
                            $detalleInsert->save();
                        }
                    }


                    if ($isCreate) {
                        $message = $id == 0 ? 'Aplicación creada correctamente' : 'Aplicación actualizada correctamente';
                        $status = true;
                    } else {
                        $message = $id == 0 ? 'Error al crear la aplicación ' : 'Error al actualizar la aplicación ';
                        $status = false;
                    }

                    return redirect()->route('vista.modulo.cuentasPagar.create-cxp', $id == 0 ? $lastId : $id)->with('message', $message)->with('status', $status);
                    break;
                    //-----------------------------------------------------
                case 'Entrada por Compra':
                    $cxp = new PROC_ACCOUNTS_PAYABLE();
                    $cxp->accountsPayable_movement = $cxp_request['origin'];
                    $cxp->accountsPayable_issuedate
                        = \Carbon\Carbon::now();
                    $cxp->accountsPayable_money =  $cxp_request['nameMoneda'];
                    $cxp->accountsPayable_typeChange =  $cxp_request['nameTipoCambio'];
                    $cxp->accountsPayable_moneyAccount =  $cxp_request['cuentaKey'];
                    $cxp->accountsPayable_provider =  $cxp_request['proveedorKey'];
                    $cxp->accountsPayable_formPayment =  $cxp_request['proveedorFormaPago'];
                    $cxp->accountsPayable_observations =  $cxp_request['observaciones'];
                    $cxp->accountsPayable_reference =  $cxp_request['referencia'];
                    $cxp->accountsPayable_amount =  str_replace(['$', ','], '', $cxp_request['saldo']); //dinero
                    $cxp->accountsPayable_taxes =   str_replace(['$', ','], '', $cxp_request['impuesto']); //dinero
                    $cxp->accountsPayable_total =  str_replace(['$', ','], '', $cxp_request['saldo']); //dinero
                    $cxp->accountsPayable_concept =  null;
                    $cxp->accountsPayable_status = $this->estatus[0];
                    $cxp->accountsPayable_company = session('company')->companies_key;
                    $cxp->accountsPayable_branchOffice = session('sucursal')->branchOffices_key;
                    $cxp->accountsPayable_user = Auth::user()->username;
                    $cxp->accountsPayable_originType = 'CxP';
                    $cxp->accountsPayable_origin = $tipoMovimiento;
                    $cxp->accountsPayable_originID = $cxp_request['folio'];
                    $cxp->created_at = Carbon::now()->format('Y-m-d H:i:s');
                    $cxp->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                    $isCreate = $cxp->save();
                    $lastId = PROC_ACCOUNTS_PAYABLE::latest('accountsPayable_id')->first()->accountsPayable_id;



                    $cxpDetails = new PROC_ACCOUNTS_PAYABLE_DETAILS();
                    $cxpDetails->accountsPayableDetails_accountPayableID = $lastId;
                    $cxpDetails->accountsPayableDetails_apply = $tipoMovimiento;
                    $cxpDetails->accountsPayableDetails_applyIncrement = $cxp_request['folio'];
                    $cxpDetails->accountsPayableDetails_amount = str_replace(['$', ','], '', $cxp_request['saldo']);
                    $cxpDetails->accountsPayableDetails_company = session('company')->companies_key;
                    $cxpDetails->accountsPayableDetails_branchOffice = session('sucursal')->branchOffices_key;
                    $cxpDetails->accountsPayableDetails_user = Auth::user()->username;
                    $cxpDetails->accountsPayableDetails_movReference = $cxp_request['id'];
                    $cxpDetails->save();


                    if ($isCreate) {
                        $message = 'El Pago se ha creado correctamente';
                        $status = true;
                    } else {
                        $message = 'Error al crear el Pago ';
                        $status = false;
                    }

                    return redirect()->route('vista.modulo.cuentasPagar.create-cxp', $lastId)->with('message', $message)->with('status', $status);
                    break;

                    //-----------------------------------------------------
                case 'Factura de Gasto':
                    $cxp = new PROC_ACCOUNTS_PAYABLE();
                    $cxp->accountsPayable_movement = $cxp_request['origin'];
                    $cxp->accountsPayable_issuedate
                        = \Carbon\Carbon::now();
                    $cxp->accountsPayable_money =  $cxp_request['nameMoneda'];
                    $cxp->accountsPayable_typeChange =  $cxp_request['nameTipoCambio'];
                    $cxp->accountsPayable_moneyAccount =  $cxp_request['cuentaKey'];
                    $cxp->accountsPayable_provider =  $cxp_request['proveedorKey'];
                    $cxp->accountsPayable_formPayment =  $cxp_request['proveedorFormaPago'];
                    $cxp->accountsPayable_observations =  $cxp_request['observaciones'];
                    $cxp->accountsPayable_reference =  $cxp_request['referencia'];
                    $cxp->accountsPayable_amount =  str_replace(['$', ','], '', $cxp_request['importe']); //dinero
                    $cxp->accountsPayable_taxes =   str_replace(['$', ','], '', $cxp_request['impuesto']); //dinero
                    $cxp->accountsPayable_total =  str_replace(['$', ','], '', $cxp_request['importeTotal']); //dinero
                    $cxp->accountsPayable_concept =  null;
                    $cxp->accountsPayable_status = $this->estatus[0];
                    $cxp->accountsPayable_company = session('company')->companies_key;
                    $cxp->accountsPayable_branchOffice = session('sucursal')->branchOffices_key;
                    $cxp->accountsPayable_user = Auth::user()->username;
                    $cxp->accountsPayable_originType = 'CxP';
                    $cxp->accountsPayable_origin = $tipoMovimiento;
                    $cxp->accountsPayable_originID = $cxp_request['folio'];
                    $cxp->created_at = Carbon::now()->format('Y-m-d H:i:s');
                    $cxp->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                    $isCreate = $cxp->save();
                    $lastId = PROC_ACCOUNTS_PAYABLE::latest('accountsPayable_id')->first()->accountsPayable_id;



                    $cxpDetails = new PROC_ACCOUNTS_PAYABLE_DETAILS();
                    $cxpDetails->accountsPayableDetails_accountPayableID = $lastId;
                    $cxpDetails->accountsPayableDetails_apply = $tipoMovimiento;
                    $cxpDetails->accountsPayableDetails_applyIncrement = $cxp_request['folio'];
                    $cxpDetails->accountsPayableDetails_amount = str_replace(['$', ','], '', $cxp_request['importeTotal']);
                    $cxpDetails->accountsPayableDetails_company = session('company')->companies_key;
                    $cxpDetails->accountsPayableDetails_branchOffice = session('sucursal')->branchOffices_key;
                    $cxpDetails->accountsPayableDetails_user = Auth::user()->username;
                    $cxpDetails->accountsPayableDetails_movReference = $cxp_request['id'];
                    $cxpDetails->save();


                    if ($isCreate) {
                        $message = 'Pago creado correctamente';
                        $status = true;
                    } else {
                        $message = 'Error al crear el Pago ';
                        $status = false;
                    }

                    return redirect()->route('vista.modulo.cuentasPagar.create-cxp', $lastId)->with('message', $message)->with('status', $status);
                    break;
                    //-----------------------------------------------------

                default:
                    # code..
                    break;
            }
        } catch (\Throwable $th) {
            dd($th);
            $message = $id == 0 ? "Por favor, vaya con el administrador de sistemas, no se pudo crear la cuenta por pagar" : "Por favor, vaya con el administrador de sistemas, no se pudo actualizar la cuenta por pagar";
            return redirect()->route('vista.modulo.cuentasPagar.create-cxp', $id)->with('message', $message)->with('status', false);
        }
    }

    public function afectar(Request $request)
    {
        $cxp_request = $request->except('_token');
        $id = $request->id;
        $tipoMovimiento = $request['movimientos'];
        $cuenta = $cxp_request['cuentaKey'];
        $tipoCuenta = $request['tipoCuenta'];

        if ($tipoMovimiento == 'Anticipo' || $tipoMovimiento == 'Pago de Facturas' || $tipoMovimiento == 'Aplicación') {
            //verificar que la forma de pago sea de la misma que el movimiento
            $formaPago = CONF_FORMS_OF_PAYMENT::WHERE('formsPayment_key', '=', $cxp_request['proveedorFormaPago'])->first();

            $moneda = CONF_MONEY::WHERE('money_key', '=', $cxp_request['nameMoneda'])->first();
            // DD(trim($formaPago->formsPayment_money), $moneda->money_keySat);

            if (trim($formaPago->formsPayment_money) != $moneda->money_keySat) {
                $message = 'La forma de pago no es compatible con la moneda del movimiento';
                $status = 500;
                $lastId = $cxp_request['id'];

                return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
            }
        }



        if ($tipoMovimiento == 'Pago de Facturas' || $tipoMovimiento == 'Aplicación') {
            //validar que el tipo cambio sea el mismo que el de la moneda 
            $tipoCambio = $cxp_request['nameTipoCambio'];
            $moneda = $cxp_request['nameMoneda'];
            $tipoCambioMoneda = CONF_MONEY::WHERE('money_key', '=', $moneda)->first();


            if ($tipoCambioMoneda->money_change != $tipoCambio) {
                $message = 'El tipo de cambio no es el mismo que el de la moneda';
                $status = 500;
                $lastId = $cxp_request['id'];

                return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
            }

            $detalleCxP =  $cxp_request['dataArticulosJson'];
            $detalleCxp = json_decode($detalleCxP, true);
            $claveDet = array_keys($detalleCxp);

            if ($detalleCxP !== null) {
                foreach ($claveDet as $key => $value) {
                    //buscar los movimientos
                    $movimiento = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $detalleCxp[$value]['movID'])->first();
                    //verificar que los movimientos todos sean de la misma moneda con la que se esta pagando
                    if ($movimiento->accountsPayable_money !== $cxp_request['nameMoneda']) {
                        $message = 'Los movimientos no son de la misma moneda';
                        $status = 500;
                        $lastId = $cxp_request['id'];

                        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                    }

                    if ($movimiento->accountsPayable_provider !== $cxp_request['proveedorKey']) {
                        $message = 'Los movimientos no son del mismo proveedor';
                        $status = 500;
                        $lastId = $cxp_request['id'];

                        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                    }
                }
            }
        }



        if ($tipoCuenta == 'Caja' && $tipoMovimiento !== 'Aplicación') {
            $validarCuenta = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', $cuenta)->where('moneyAccountsBalance_status', 'Alta')->WHERE('moneyAccountsBalance_company', '=', session('company')->companies_key)->first();

            // ver si la caja tiene saldo
            $saldo = $validarCuenta->moneyAccountsBalance_balance;
            $fondo = (float) $saldo;
            $importe = $tipoMovimiento === "Anticipo" ? (float) str_replace(['$', ','], '',  $cxp_request['importeTotal']) : (float) str_replace(['$', ','], '',  $cxp_request['totalCompleto']);
            if ($fondo < $importe) {
                $message = 'La cuenta de caja no tiene fondos suficientes';
                $status = 500;
                $procesar = false;
                $lastId = 0;
            } else {
                $procesar = true;
            }
        } else {
            $procesar = true;
        }

        if ($procesar) {
            try {

                if ($cxp_request['movimientos'] === 'Aplicación') {
                    $referencias = explode(" ", $cxp_request['anticiposKey']);
                    //  dd($referencias);
                    //buscamos el anticipo a aplicar 
                    $anticipo = PROC_ACCOUNTS_PAYABLE::WHERE('accountsPayable_movement', '=', $referencias[0])->WHERE('accountsPayable_movementID', '=', $referencias[1])->WHERE('accountsPayable_branchOffice', '=', session('sucursal')->branchOffices_key)->first();

                    // dd($anticipo);

                    if ($anticipo->accountsPayable_money != $cxp_request['nameMoneda']) {
                        $message = 'El anticipo no es de la misma moneda que la cuenta por pagar';
                        $status = 500;
                        $lastId = $cxp_request['id'];

                        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                    }

                    $valor1 = (float) $anticipo->accountsPayable_balance;
                    $valor2 = (float)str_replace(['$', ','], '', $cxp_request['totalCompleto']);
                    if ($anticipo !== null) {

                        if ($valor1 < $valor2 && $valor1 !== $valor2) {
                            $message = 'La cantidad que desea pagar es mayor al anticipo';
                            $status = 500;
                            $lastId = $cxp_request['id'];

                            return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                        }
                    }

                    if ($anticipo->accountsPayable_provider != $cxp_request['proveedorKey']) {
                        $message = 'El anticipo no es del mismo proveedor que la cuenta por pagar';
                        $status = 500;
                        $lastId = $cxp_request['id'];

                        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                    }
                }

                if ($cxp_request['movimientos'] === 'Pago de Facturas') {
                    $detalles = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_accountPayableID', '=', $cxp_request['id'])->get();

                    if (!$detalles) {
                        foreach ($detalles as $detalle) {
                            $movimiento = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $detalle->accountsPayableDetails_movReference)->first();
                            $dataArticulosJson = json_decode($cxp_request['dataArticulosJson'], true);


                            $valor1 = (float) $movimiento->accountsPayable_balance;
                            $valor2 = (float)str_replace(['$', ','], '', $cxp_request['totalCompleto']);
                            // DD($movimiento);
                            if ($movimiento->accountsPayable_status == $this->estatus[2] || $valor1 < $valor2) {
                                $message = 'La cantidad que desea pagar es mayor al saldo del movimiento2';
                                $status = 500;
                                $lastId = $cxp_request['id'] != 0 ? $cxp_request['id'] : '';

                                return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                            }
                        }
                    } else {
                        $dataArticulosJson = (array) json_decode($cxp_request['dataArticulosJson'], true);

                        foreach ($dataArticulosJson as $key => $data) {
                            $movimiento = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $data['movID'])->first();

                            $valor1 = (float)str_replace(['$', ','], '', $data['importe']);
                            $valor2 = (float) $movimiento->accountsPayable_balance;
                            // DD($movimiento);
                            if ($movimiento->accountsPayable_status == $this->estatus[2] || $valor1 > $valor2) {
                                $message = 'La cantidad que desea pagar es mayor al saldo del movimiento';
                                $status = 500;
                                $lastId = $cxp_request['id'] != 0 ? $cxp_request['id'] : '';

                                return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                            }
                        }
                    }
                }


                if ($id == 0) {
                    $cxp = new PROC_ACCOUNTS_PAYABLE();
                } else {
                    $cxp = PROC_ACCOUNTS_PAYABLE::find($id);
                }

                $cxp->accountsPayable_movement = $tipoMovimiento;
                $cxp->accountsPayable_issuedate
                    = \Carbon\Carbon::now();
                $cxp->accountsPayable_money =  $cxp_request['nameMoneda'];
                $cxp->accountsPayable_typeChange =  $cxp_request['nameTipoCambio'];
                $cxp->accountsPayable_moneyAccount =  $cxp_request['cuentaKey'];
                $cxp->accountsPayable_provider =  $cxp_request['proveedorKey'];
                $cxp->accountsPayable_formPayment =  $cxp_request['proveedorFormaPago'];
                $cxp->accountsPayable_observations =  $cxp_request['observaciones'];
                $cxp->accountsPayable_reference =  $cxp_request['referencia'];
                $cxp->accountsPayable_amount =  str_replace(['$', ','], '', $cxp_request['importe']); //dinero
                $cxp->accountsPayable_taxes =   str_replace(['$', ','], '', $cxp_request['impuesto']); //dinero

                $cxp->accountsPayable_concept =  $cxp_request['concepto'];
                $cxp->accountsPayable_company = session('company')->companies_key;
                $cxp->accountsPayable_branchOffice = session('sucursal')->branchOffices_key;
                $cxp->accountsPayable_user = Auth::user()->username;

                if ($cxp_request['movimientos'] !== 'Anticipo') {
                    $cxp->accountsPayable_balance =  str_replace(['$', ','], '', $cxp_request['totalCompleto']); //dinero
                    $cxp->accountsPayable_total =  str_replace(['$', ','], '', $cxp_request['totalCompleto']); //dinero
                } else {
                    $cxp->accountsPayable_balance =  str_replace(['$', ','], '', $cxp_request['importeTotal']); //dinero
                    $cxp->accountsPayable_total =  str_replace(['$', ','], '', $cxp_request['importeTotal']); //dinero
                }


                $cxp->created_at = Carbon::now()->format('Y-m-d H:i:s');
                $cxp->updated_at = Carbon::now()->format('Y-m-d H:i:s');

                //agregamos el detalle de la cuenta por pagar
                $detalleCxP =  $cxp_request['dataArticulosJson'];
                $detalleCxp = json_decode($detalleCxP, true);
                $claveDet = array_keys($detalleCxp);

                switch ($tipoMovimiento) {
                    case 'Anticipo':
                        $cxp->accountsPayable_status = $this->estatus[1];
                        break;

                    case 'Aplicación':
                        $cxp->accountsPayable_status = $this->estatus[2];
                        $cxp->update();
                        break;

                    case 'Pago de Facturas':
                        $cxp->accountsPayable_status = $this->estatus[2];
                        $cxp->accountsPayable_total =  str_replace(',', '', $cxp_request['importe']);
                        $cxp->update();
                        break;

                    default:
                        # code...
                        break;
                }


                if ($id == 0) {
                    $isCreate = $cxp->save();
                    $lastId = $id == 0 ? PROC_ACCOUNTS_PAYABLE::latest('accountsPayable_id')->first()->accountsPayable_id : $id;
                } else {
                    $isCreate = $cxp->update();
                    $lastId = $cxp->accountsPayable_id;
                }

                //Generamos el folio del anticipo
                $folioAfectar = PROC_ACCOUNTS_PAYABLE::WHERE('accountsPayable_id', '=', $lastId)->first();

                $this->actualizarFolio($tipoMovimiento, $folioAfectar);


                // switch ($tipoMovimiento) {
                //     case 'Anticipo':
                //         $folioMov = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', 'Anticipo')->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->max('accountsPayable_movementID');
                //         $folioMov = $folioMov == null ? 1 : $folioMov + 1;
                //         $folioAfectar->accountsPayable_movementID = $folioMov;
                //         $folioAfectar->update();
                //         break;

                //     case 'Aplicación':
                //         $folioMov = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', 'Aplicación')->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->max('accountsPayable_movementID');
                //         $folioMov = $folioMov == null ? 1 : $folioMov + 1;
                //         $folioAfectar->accountsPayable_movementID = $folioMov;
                //         $folioAfectar->update();

                //         $detalles = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_accountPayableID', '=', $folioAfectar->accountsPayable_id)->get();

                //         if (count($detalles) > 0) {
                //             foreach ($detalles as $detalle) {
                //                 //eliminamos el detalle de la cuenta por pagar original
                //                 $detalle->delete();
                //             }
                //         }

                //         break;

                //     case 'Pago de Facturas':
                //         $folioMov = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', 'Pago de Facturas')->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->max('accountsPayable_movementID');
                //         $folioMov = $folioMov == null ? 1 : $folioMov + 1;
                //         $folioAfectar->accountsPayable_movementID = $folioMov;
                //         $folioAfectar->update();

                //         //Eliminar detalles
                //         $detalles = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_accountPayableID', '=', $folioAfectar->accountsPayable_id)->get();
                //         // dd($detalles);

                //         if (count($detalles) > 0) {
                //             foreach ($detalles as $detalle) {
                //                 //eliminamos el detalle de la cuenta por pagar original
                //                 $detalle->delete();
                //             }
                //         }

                //         break;

                //     default:
                //         # code...
                //         break;
                // }

                if ($detalleCxP !== null) {
                    foreach ($claveDet as $detalle) {
                        $detalleInsert = new PROC_ACCOUNTS_PAYABLE_DETAILS();
                        $detalleInsert->accountsPayableDetails_apply = $detalleCxp[$detalle]['aplicaSelect'];
                        $detalleInsert->accountsPayableDetails_accountPayableID =  $lastId;
                        $detalleInsert->accountsPayableDetails_applyIncrement = $detalleCxp[$detalle]['aplicaConsecutivo'];
                        $detalleInsert->accountsPayableDetails_amount = str_replace(['$', ','], '', $detalleCxp[$detalle]['importe']);
                        $detalleInsert->accountsPayableDetails_company = session('company')->companies_key;
                        $detalleInsert->accountsPayableDetails_branchOffice = session('sucursal')->branchOffices_key;
                        $detalleInsert->accountsPayableDetails_user = Auth::user()->username;
                        $detalleInsert->accountsPayableDetails_movReference = $detalleCxp[$detalle]['movID'];
                        $detalleInsert->save();
                    }
                }

                if ($tipoMovimiento !== 'Aplicación') {
                    if ($tipoCuenta != 'Caja') {
                        //agregar CxP pendiente -- El anticipo
                        $this->agregarCxPP($folioAfectar->accountsPayable_id);
                        //agregar aux
                        $this->auxiliar($folioAfectar->accountsPayable_id);

                        //Generamos una solicitud de cheque de acuerdo al tipo de cuenta a CXP -Cheque del anticipo
                        $cheque = $this->agregarCxPCheque($folioAfectar->accountsPayable_id);
                        //buscamos el cheque generado
                        $chequeGenerado = PROC_TREASURY::WHERE('treasuries_id', '=', $cheque)->first();
                        $this->agregarMov($folioAfectar->accountsPayable_id);
                        //agregar saldo
                        $this->agregarSaldo($folioAfectar->accountsPayable_id); //se agrega el saldo a 0
                    } else {

                        $egreso = $this->agregarTesoreria($folioAfectar->accountsPayable_id); //Agrega a tesoreria como concluido

                        $egresoGenerado = PROC_TREASURY::WHERE('treasuries_id', '=', $egreso)->first();

                        //observaciones: agregar cxppendiente

                        $this->agregarMovCaja($folioAfectar->accountsPayable_id);

                        $this->actualizarSaldo($folioAfectar->accountsPayable_id); //Actualiza el saldo de la cuenta de tipo caja

                    }
                }




                //actualizamos saldos de cuentas por pagar y eliminamos en pendiente
                if ($folioAfectar->accountsPayable_movement == 'Pago de Facturas' && $folioAfectar->accountsPayable_status == $this->estatus[2]) {
                    // //buscamos la cuenta origen 

                    $detalles = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_accountPayableID', '=', $folioAfectar->accountsPayable_id)->get();

                    $pago = $folioAfectar->accountsPayable_total;
                    foreach ($detalles as $detalle) {
                        $cuentaOrigen = PROC_ACCOUNTS_PAYABLE::WHERE('accountsPayable_id', '=', $detalle->accountsPayableDetails_movReference)->first();

                        if ($pago >= $cuentaOrigen->accountsPayable_balance) {
                            $cuentaOrigen->accountsPayable_balance =  $cuentaOrigen->accountsPayable_balance - $detalle->accountsPayableDetails_amount;
                            $cuentaOrigen->update();

                            if ($cuentaOrigen->accountsPayable_balance == 0) {
                                $cuentaOrigen->accountsPayable_status = $this->estatus[2];
                                $cuentaOrigen->update();
                            }

                            $cxpP = PROC_ACCOUNTS_PAYABLE_P::WHERE('accountsPayableP_movement', '=', $cuentaOrigen->accountsPayable_movement)->WHERE('accountsPayableP_movementID', '=', $cuentaOrigen->accountsPayable_movementID)->WHERE('accountsPayableP_branchOffice', '=', $cuentaOrigen->accountsPayable_branchOffice)->first();

                            $cxpP->accountsPayableP_balance =   $cxpP->accountsPayableP_balance - $detalle->accountsPayableDetails_amount;
                            $cxpP->accountsPayableP_balanceTotal =   $cxpP->accountsPayableP_balanceTotal - $detalle->accountsPayableDetails_amount;
                            $cxpP->update();

                            if ($cxpP->accountsPayableP_balance == 0) {
                                $cxpP->delete();
                            }
                        } else {
                            $cuentaOrigen->accountsPayable_balance =  $cuentaOrigen->accountsPayable_balance - $detalle->accountsPayableDetails_amount;
                            $cuentaOrigen->update();
                            $cxpP = PROC_ACCOUNTS_PAYABLE_P::WHERE('accountsPayableP_movement', '=', $cuentaOrigen->accountsPayable_movement)->WHERE('accountsPayableP_movementID', '=', $cuentaOrigen->accountsPayable_movementID)->WHERE('accountsPayableP_branchOffice', '=', $cuentaOrigen->accountsPayable_branchOffice)->first();
                            $cxpP->accountsPayableP_balance =   $cxpP->accountsPayableP_balance - $detalle->accountsPayableDetails_amount;
                            $cxpP->accountsPayableP_balanceTotal =   $cxpP->accountsPayableP_balanceTotal - $detalle->accountsPayableDetails_amount;
                            $cxpP->update();
                            if ($cxpP->accountsPayableP_balance == 0) {
                                $cxpP->delete();
                            }
                        }
                        $pago -= $detalle->accountsPayableDetails_amount;
                    }
                }


                if ($folioAfectar->accountsPayable_movement == 'Aplicación' && $folioAfectar->accountsPayable_status == $this->estatus[2]) {

                    //buscamos la cuenta origen de la aplicacion
                    if ($folioAfectar->accountsPayable_origin !== null) {
                        $anticipo = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', $folioAfectar->accountsPayable_origin)->where('accountsPayable_movementID', '=', $folioAfectar->accountsPayable_originID)->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->first();
                    } else {
                        $referencias = explode(" ", $folioAfectar->accountsPayable_reference);
                        $anticipo = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', $referencias[0])->where('accountsPayable_movementID', '=', $referencias[1])->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->first();
                        // dd($referencias, $anticipo);
                    }

                    if ($folioAfectar->accountsPayable_total <= $anticipo->accountsPayable_total) {
                        $anticipo->accountsPayable_balance = $anticipo->accountsPayable_balance - $folioAfectar->accountsPayable_total;
                        $anticipo->update();
                        if ($anticipo->accountsPayable_balance == 0) {
                            $anticipo->accountsPayable_status = $this->estatus[2];
                            $anticipo->update();

                            //buscamos el anticipo pendiente en cxp y lo eliminamos
                            $cxpP = PROC_ACCOUNTS_PAYABLE_P::WHERE('accountsPayableP_movement', '=', $anticipo->accountsPayable_movement)->WHERE('accountsPayableP_movementID', '=', $anticipo->accountsPayable_movementID)->WHERE('accountsPayableP_branchOffice', '=', $anticipo->accountsPayable_branchOffice)->first();
                            if ($cxpP !== null) {
                                $cxpP->delete();
                            }
                        }
                    }


                    $detalles = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_accountPayableID', '=', $folioAfectar->accountsPayable_id)->get();
                    // dd($detalles);
                    $pago = $folioAfectar->accountsPayable_total;
                    foreach ($detalles as $detalle) {
                        $cuentaOrigen = PROC_ACCOUNTS_PAYABLE::WHERE('accountsPayable_id', '=', $detalle->accountsPayableDetails_movReference)->first();

                        if ($pago >= $cuentaOrigen->accountsPayable_balance) {
                            $cuentaOrigen->accountsPayable_balance =  $cuentaOrigen->accountsPayable_balance - $detalle->accountsPayableDetails_amount;
                            $cuentaOrigen->update();

                            if ($cuentaOrigen->accountsPayable_balance == 0) {
                                $cuentaOrigen->accountsPayable_status = $this->estatus[2];
                                $cuentaOrigen->update();
                            }

                            $cxpP = PROC_ACCOUNTS_PAYABLE_P::WHERE('accountsPayableP_movement', '=', $cuentaOrigen->accountsPayable_movement)->WHERE('accountsPayableP_movementID', '=', $cuentaOrigen->accountsPayable_movementID)->WHERE('accountsPayableP_branchOffice', '=', $cuentaOrigen->accountsPayable_branchOffice)->first();

                            $cxpP->accountsPayableP_balance =   $cxpP->accountsPayableP_balance - $detalle->accountsPayableDetails_amount;
                            $cxpP->accountsPayableP_balanceTotal =   $cxpP->accountsPayableP_balanceTotal - $detalle->accountsPayableDetails_amount;
                            $cxpP->update();

                            if ($cxpP->accountsPayableP_balance == 0) {
                                $cxpP->delete();
                            }
                        } else {
                            $cuentaOrigen->accountsPayable_balance =  $cuentaOrigen->accountsPayable_balance - $detalle->accountsPayableDetails_amount;
                            $cuentaOrigen->update();
                            $cxpP = PROC_ACCOUNTS_PAYABLE_P::WHERE('accountsPayableP_movement', '=', $cuentaOrigen->accountsPayable_movement)->WHERE('accountsPayableP_movementID', '=', $cuentaOrigen->accountsPayable_movementID)->WHERE('accountsPayableP_branchOffice', '=', $cuentaOrigen->accountsPayable_branchOffice)->first();
                            $cxpP->accountsPayableP_balance =   $cxpP->accountsPayableP_balance - $detalle->accountsPayableDetails_amount;
                            $cxpP->accountsPayableP_balanceTotal =   $cxpP->accountsPayableP_balanceTotal - $detalle->accountsPayableDetails_amount;
                            $cxpP->update();
                            if ($cxpP->accountsPayableP_balance == 0) {
                                $cxpP->delete();
                            }
                        }
                        $pago -= $detalle->accountsPayableDetails_amount;
                    }

                    $this->auxiliarAplicacion($folioAfectar->accountsPayable_id);
                    $this->agregarMovAplicacion($folioAfectar->accountsPayable_id);
                }



                if ($isCreate) {
                    $message = $id == 0 ? 'El Anticipo se ha creado correctamente' : 'El Anticipo se ha creado correctamente';
                    $status = 200;
                } else {
                    $message = $id == 0 ? 'Error al afectar el Anticipo' : 'Error al afectar el Anticipo';
                    $status = 500;
                }
            } catch (\Throwable $th) {
                $status = 500;
                $message = $th->getMessage() . '-' . $th->getLine();
                $lastId = 0;
            }
        }
        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null], 200);
    }

    public function agregarMovCaja($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $folio)->first();

        // dd($folioAfectar);
        if ($folioAfectar->accountsPayable_status == $this->estatus[1] && $folioAfectar->accountsPayable_movement == 'Anticipo') {
            $movimiento = new PROC_MOVEMENT_FLOW();
            $movPosterior = PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->accountsPayable_movementID)->where('treasuries_originType', '=', 'CxP')->where('treasuries_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->where('treasuries_origin', '=', 'Anticipo')->first();
            $movimiento->movementFlow_branch = $folioAfectar->accountsPayable_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->accountsPayable_company;
            $movimiento->movementFlow_moduleOrigin = 'CxP';
            $movimiento->movementFlow_originID = $folioAfectar->accountsPayable_id;
            $movimiento->movementFlow_movementOrigin = $folioAfectar->accountsPayable_movement;
            $movimiento->movementFlow_movementOriginID = $folioAfectar->accountsPayable_movementID;
            $movimiento->movementFlow_moduleDestiny = 'Din';
            $movimiento->movementFlow_destinityID = $movPosterior->treasuries_id;
            $movimiento->movementFlow_movementDestinity = $movPosterior->treasuries_movement;
            $movimiento->movementFlow_movementDestinityID = $movPosterior->treasuries_movementID;
            $movimiento->movementFlow_cancelled = 0;
            $movimiento->save();
        }


        if ($folioAfectar->accountsPayable_status == $this->estatus[2] && $folioAfectar->accountsPayable_movement == 'Pago de Facturas') {

            $detalles = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_accountPayableID', '=', $folioAfectar->accountsPayable_id)->get();


            foreach ($detalles as $detalle) {
                //buscamos cxp original
                $cxpOriginal = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $detalle->accountsPayableDetails_movReference)->first();
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->accountsPayable_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->accountsPayable_company;
                $movimiento->movementFlow_moduleOrigin = 'CxP';
                $movimiento->movementFlow_originID = $cxpOriginal->accountsPayable_id;
                $movimiento->movementFlow_movementOrigin = $cxpOriginal->accountsPayable_movement;
                $movimiento->movementFlow_movementOriginID = $cxpOriginal->accountsPayable_movementID;
                $movimiento->movementFlow_moduleDestiny = 'CxP';
                $movimiento->movementFlow_destinityID = $folioAfectar->accountsPayable_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->accountsPayable_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->accountsPayable_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();
            }


            $movimiento = new PROC_MOVEMENT_FLOW();
            $movPosterior = PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->accountsPayable_movementID)->where('treasuries_originType', '=', 'CxP')->where('treasuries_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->where('treasuries_origin', '=', 'Pago de Facturas')->first();
            $movimiento->movementFlow_branch = $folioAfectar->accountsPayable_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->accountsPayable_company;
            $movimiento->movementFlow_moduleOrigin = 'CxP';
            $movimiento->movementFlow_originID = $folioAfectar->accountsPayable_id;
            $movimiento->movementFlow_movementOrigin = $folioAfectar->accountsPayable_movement;
            $movimiento->movementFlow_movementOriginID = $folioAfectar->accountsPayable_movementID;
            $movimiento->movementFlow_moduleDestiny = 'Din';
            $movimiento->movementFlow_destinityID = $movPosterior->treasuries_id;
            $movimiento->movementFlow_movementDestinity = $movPosterior->treasuries_movement;
            $movimiento->movementFlow_movementDestinityID = $movPosterior->treasuries_movementID;
            $movimiento->movementFlow_cancelled = 0;
            $movimiento->save();
        }
    }

    public function agregarMov($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $folio)->first();


        //dd($folioAfectar);
        if ($folioAfectar->accountsPayable_status == $this->estatus[1] && $folioAfectar->accountsPayable_movement == 'Anticipo') {

            $movPosterior =  PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->accountsPayable_movementID)->where('treasuries_originType', '=', 'CxP')->where('treasuries_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->where('treasuries_origin', '=', 'Anticipo')->first();


            $movimiento = new PROC_MOVEMENT_FLOW();
            $movimiento->movementFlow_branch = $folioAfectar->accountsPayable_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->accountsPayable_company;
            $movimiento->movementFlow_moduleOrigin = 'CxP';
            $movimiento->movementFlow_originID = $folioAfectar->accountsPayable_id;
            $movimiento->movementFlow_movementOrigin = $folioAfectar->accountsPayable_movement;
            $movimiento->movementFlow_movementOriginID = $folioAfectar->accountsPayable_movementID;
            $movimiento->movementFlow_moduleDestiny = 'Din';
            $movimiento->movementFlow_destinityID = $movPosterior->treasuries_id;
            $movimiento->movementFlow_movementDestinity = $movPosterior->treasuries_movement;
            $movimiento->movementFlow_movementDestinityID = $movPosterior->treasuries_movementID;
            $movimiento->movementFlow_cancelled = 0;

            $movimiento->save();
        }

        if ($folioAfectar->accountsPayable_status == $this->estatus[2] && $folioAfectar->accountsPayable_movement == 'Pago de Facturas') {

            $detalles = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_accountPayableID', '=', $folioAfectar->accountsPayable_id)->get();


            foreach ($detalles as $detalle) {
                //buscamos cxp original
                $cxpOriginal = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $detalle->accountsPayableDetails_movReference)->first();
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->accountsPayable_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->accountsPayable_company;
                $movimiento->movementFlow_moduleOrigin = 'CxP';
                $movimiento->movementFlow_originID = $cxpOriginal->accountsPayable_id;
                $movimiento->movementFlow_movementOrigin = $cxpOriginal->accountsPayable_movement;
                $movimiento->movementFlow_movementOriginID = $cxpOriginal->accountsPayable_movementID;
                $movimiento->movementFlow_moduleDestiny = 'CxP';
                $movimiento->movementFlow_destinityID = $folioAfectar->accountsPayable_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->accountsPayable_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->accountsPayable_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();
            }

            $movPosterior =  PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->accountsPayable_movementID)->where('treasuries_origin', '=', $folioAfectar->accountsPayable_movement)->where('treasuries_originType', '=', 'CxP')->where('treasuries_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->where('treasuries_movement', '=', 'Sol. de Cheque/Transferencia')->first();



            $movimiento = new PROC_MOVEMENT_FLOW();
            $movimiento->movementFlow_branch = $folioAfectar->accountsPayable_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->accountsPayable_company;
            $movimiento->movementFlow_moduleOrigin = 'CxP';
            $movimiento->movementFlow_originID = $folioAfectar->accountsPayable_id;
            $movimiento->movementFlow_movementOrigin = $folioAfectar->accountsPayable_movement;
            $movimiento->movementFlow_movementOriginID = $folioAfectar->accountsPayable_movementID;
            $movimiento->movementFlow_moduleDestiny = 'Din';
            $movimiento->movementFlow_destinityID = $movPosterior->treasuries_id;
            $movimiento->movementFlow_movementDestinity = $movPosterior->treasuries_movement;
            $movimiento->movementFlow_movementDestinityID = $movPosterior->treasuries_movementID;
            $movimiento->movementFlow_cancelled = 0;
            $movimiento->save();
        }
    }

    public function auxiliarAplicacion($folio)
    {
        $folioAfectar  = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $folio)->first();
        // dd($folioAfectar);
        if ($folioAfectar->accountsPayable_movement == 'Aplicación' && $folioAfectar->accountsPayable_status == $this->estatus[2]) {
            //Agregamos a aux el abono a la entrada pagada
            $detalle = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_accountPayableID', '=', $folioAfectar->accountsPayable_id)->get();

            foreach ($detalle as $det) {

                $mov = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $det->accountsPayableDetails_movReference)->first();

                //agregamos a aux el abono al movimiento
                $auxiliar = new PROC_ASSISTANT();
                $auxiliar->assistant_companieKey = $folioAfectar->accountsPayable_company;
                $auxiliar->assistant_branchKey = $folioAfectar->accountsPayable_branchOffice;
                $auxiliar->assistant_branch = 'CxP';
                $auxiliar->assistant_movement = $folioAfectar->accountsPayable_movement;
                $auxiliar->assistant_movementID = $folioAfectar->accountsPayable_movementID;
                $auxiliar->assistant_module = 'CxP';
                $auxiliar->assistant_moduleID = $folioAfectar->accountsPayable_id;
                $auxiliar->assistant_money = $folioAfectar->accountsPayable_money;
                $auxiliar->assistant_typeChange = $folioAfectar->accountsPayable_typeChange;
                $auxiliar->assistant_account = $folioAfectar->accountsPayable_provider;

                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = null;
                $auxiliar->assistant_payment = $folioAfectar->accountsPayable_total;
                $auxiliar->assistant_apply = $mov->accountsPayable_movement;
                $auxiliar->assistant_applyID =  $mov->accountsPayable_movementID;
                $auxiliar->assistant_canceled = 0;
                $auxiliar->assistant_reference = $folioAfectar->accountsPayable_reference;
                $auxiliar->save();
            }

            //buscamos el movimiento anterior
            if ($folioAfectar->accountsPayable_origin !== null) {
                $anticipo = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', $folioAfectar->accountsPayable_origin)->where('accountsPayable_movementID', '=', $folioAfectar->accountsPayable_originID)->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->first();
            } else {
                $referencias = explode(" ", $folioAfectar->accountsPayable_reference);
                $anticipo = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', $referencias[0])->where('accountsPayable_movementID', '=', $referencias[1])->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->first();
                // dd($referencias, $anticipo);
            }
            $auxiliar = new PROC_ASSISTANT();
            $auxiliar->assistant_companieKey = $folioAfectar->accountsPayable_company;
            $auxiliar->assistant_branchKey = $folioAfectar->accountsPayable_branchOffice;
            $auxiliar->assistant_branch = 'CxP';
            $auxiliar->assistant_movement = $folioAfectar->accountsPayable_movement;
            $auxiliar->assistant_movementID = $folioAfectar->accountsPayable_movementID;
            $auxiliar->assistant_module = 'CxP';
            $auxiliar->assistant_moduleID = $folioAfectar->accountsPayable_id;
            $auxiliar->assistant_money = $folioAfectar->accountsPayable_money;
            $auxiliar->assistant_typeChange = $folioAfectar->accountsPayable_typeChange;
            $auxiliar->assistant_account = $folioAfectar->accountsPayable_provider;

            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = $folioAfectar->accountsPayable_total;
            $auxiliar->assistant_payment = null;
            $auxiliar->assistant_apply = $anticipo->accountsPayable_movement;
            $auxiliar->assistant_applyID =  $anticipo->accountsPayable_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->accountsPayable_reference;
            $auxiliar->save();
        }
    }

    public function agregarMovAplicacion($folio)
    {
        $folioAfectar  = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $folio)->first();
        // dd($folioAfectar);
        if ($folioAfectar->accountsPayable_movement == 'Aplicación' && $folioAfectar->accountsPayable_status == $this->estatus[2]) {
            //Agregamos a aux el abono a la entrada pagada
            $detalle = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_accountPayableID', '=', $folioAfectar->accountsPayable_id)->get();

            foreach ($detalle as $det) {

                $mov = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $det->accountsPayableDetails_movReference)->first();

                //agregamos el movimiento 
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->accountsPayable_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->accountsPayable_company;
                $movimiento->movementFlow_moduleOrigin = 'CxP';
                $movimiento->movementFlow_originID = $mov->accountsPayable_id;
                $movimiento->movementFlow_movementOrigin = $mov->accountsPayable_movement;
                $movimiento->movementFlow_movementOriginID = $mov->accountsPayable_movementID;
                $movimiento->movementFlow_moduleDestiny = 'CxP';
                $movimiento->movementFlow_destinityID = $folioAfectar->accountsPayable_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->accountsPayable_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->accountsPayable_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();
            }

            //buscamos el movimiento anterior
            if ($folioAfectar->accountsPayable_origin !== null) {
                $anticipo = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', $folioAfectar->accountsPayable_origin)->where('accountsPayable_movementID', '=', $folioAfectar->accountsPayable_originID)->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->first();
            } else {
                $referencias = explode(" ", $folioAfectar->accountsPayable_reference);
                $anticipo = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', $referencias[0])->where('accountsPayable_movementID', '=', $referencias[1])->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->first();
                // dd($referencias, $anticipo);
            }

            $movimiento = new PROC_MOVEMENT_FLOW();
            $movimiento->movementFlow_branch = $folioAfectar->accountsPayable_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->accountsPayable_company;
            $movimiento->movementFlow_moduleOrigin = 'CxP';
            $movimiento->movementFlow_originID = $anticipo->accountsPayable_id;
            $movimiento->movementFlow_movementOrigin = $anticipo->accountsPayable_movement;
            $movimiento->movementFlow_movementOriginID = $anticipo->accountsPayable_movementID;
            $movimiento->movementFlow_moduleDestiny = 'CxP';
            $movimiento->movementFlow_destinityID = $folioAfectar->accountsPayable_id;
            $movimiento->movementFlow_movementDestinity = $folioAfectar->accountsPayable_movement;
            $movimiento->movementFlow_movementDestinityID = $folioAfectar->accountsPayable_movementID;
            $movimiento->movementFlow_cancelled = 0;
            $movimiento->save();
        }
    }

    public function ayudaVer()
    {

        $folioAfectar  = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', 6)->first();

        // dd($folioAfectar);
        if ($folioAfectar->accountsPayable_movement == 'Aplicación' && $folioAfectar->accountsPayable_status == $this->estatus[2]) {


            if ($folioAfectar->accountsPayable_origin !== null) {
                $anticipo = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', $folioAfectar->accountsPayable_origin)->where('accountsPayable_movementID', '=', $folioAfectar->accountsPayable_originID)->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->first();
            } else {
                $referencias = explode(" ", $folioAfectar->accountsPayable_reference);
                $anticipo = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', $referencias[0])->where('accountsPayable_movementID', '=', $referencias[1])->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->first();
                // dd($referencias, $anticipo);
            }
            //buscamos la cuenta origen de la aplicacion

            // dd($folioAfectar, $anticipo);

            if ($folioAfectar->accountsPayable_total <= $anticipo->accountsPayable_total) {
                $anticipo->accountsPayable_balance = $anticipo->accountsPayable_balance - $folioAfectar->accountsPayable_total;
                // $anticipo->update();
                if ($anticipo->accountsPayable_balance == 0) {
                    $anticipo->accountsPayable_status = $this->estatus[2];
                    // $anticipo->update();
                }
            }


            $detalles = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_accountPayableID', '=', $folioAfectar->accountsPayable_id)->get();
            foreach ($detalles as $detalle) {
                $cuentaOrigen = PROC_ACCOUNTS_PAYABLE::WHERE('accountsPayable_id', '=', $detalle->accountsPayableDetails_movReference)->first();
                // dd($detalles, $cuentaOrigen);

                if ($folioAfectar->accountsPayable_total >= $cuentaOrigen->accountsPayable_total) {
                    $cuentaOrigen->accountsPayable_balance = null;
                    $folioAfectar->accountsPayable_balance = null;
                    // $folioAfectar->update();
                    $cuentaOrigen->accountsPayable_status = $this->estatus[2];
                    // $cuentaOrigen->update();

                    //eliminamos en cuenta por pagar pendiente
                    $cxpP = PROC_ACCOUNTS_PAYABLE_P::WHERE('accountsPayableP_movement', '=', $cuentaOrigen->accountsPayable_movement)->WHERE('accountsPayableP_movementID', '=', $cuentaOrigen->accountsPayable_movementID)->WHERE('accountsPayableP_branchOffice', '=', $cuentaOrigen->accountsPayable_branchOffice)->first();
                    // $cxpP->delete();
                } else {
                    $cuentaOrigen->accountsPayable_balance =  $cuentaOrigen->accountsPayable_balance - $folioAfectar->accountsPayable_total;
                    // $cuentaOrigen->update();

                    $cxpP = PROC_ACCOUNTS_PAYABLE_P::WHERE('accountsPayableP_movement', '=', $cuentaOrigen->accountsPayable_movement)->WHERE('accountsPayableP_movementID', '=', $cuentaOrigen->accountsPayable_movementID)->WHERE('accountsPayableP_branchOffice', '=', $cuentaOrigen->accountsPayable_branchOffice)->first();

                    $cxpP->accountsPayableP_balance =   $cxpP->accountsPayableP_balance - $folioAfectar->accountsPayable_total;
                    $cxpP->accountsPayableP_balanceTotal =   $cxpP->accountsPayableP_balanceTotal - $folioAfectar->accountsPayable_total;
                    // $cxpP->update();
                }
            }

            dd($folioAfectar, $anticipo, $detalles, $cuentaOrigen, $cxpP);
            // $this->auxiliarAplicacion($folioAfectar->accountsPayable_id);
            // $this->agregarMovAplicacion($folioAfectar->accountsPayable_id);
        }
    }

    public function auxiliar($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $folio)->first();

        // dd($folioAfectar);
        if ($folioAfectar->accountsPayable_status == $this->estatus[1] && $folioAfectar->accountsPayable_movement == 'Anticipo') {

            //agregar datos a aux
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->accountsPayable_company;
            $auxiliar->assistant_branchKey = $folioAfectar->accountsPayable_branchOffice;
            $auxiliar->assistant_branch = 'CxP';


            //buscamos el modulo de cxp
            $cxp = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movementID', '=', $folioAfectar->accountsPayable_movementID)->where('accountsPayable_id', '=', $folio)->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->first();

            $auxiliar->assistant_movement = $cxp->accountsPayable_movement;
            $auxiliar->assistant_movementID = $cxp->accountsPayable_movementID;
            $auxiliar->assistant_module = 'CxP';
            $auxiliar->assistant_moduleID = $cxp->accountsPayable_id;
            $auxiliar->assistant_money = $folioAfectar->accountsPayable_money;
            $auxiliar->assistant_typeChange = $folioAfectar->accountsPayable_typeChange;
            $auxiliar->assistant_account = $folioAfectar->accountsPayable_provider;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = null;
            $auxiliar->assistant_payment = $folioAfectar->accountsPayable_total;
            $auxiliar->assistant_apply = $folioAfectar->accountsPayable_movement;
            $auxiliar->assistant_applyID =  $folioAfectar->accountsPayable_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->accountsPayable_reference;



            $auxiliar->save();
        }

        if ($folioAfectar->accountsPayable_status == $this->estatus[2] && $folioAfectar->accountsPayable_movement == 'Pago de Facturas') {

            //agregar datos a aux
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->accountsPayable_company;
            $auxiliar->assistant_branchKey = $folioAfectar->accountsPayable_branchOffice;
            $auxiliar->assistant_branch = 'CxP';


            //buscamos el modulo de cxp
            $cxp = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movementID', '=', $folioAfectar->accountsPayable_movementID)->where('accountsPayable_id', '=', $folio)->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->first();

            $auxiliar->assistant_movement = $cxp->accountsPayable_movement;
            $auxiliar->assistant_movementID = $cxp->accountsPayable_movementID;
            $auxiliar->assistant_module = 'CxP';
            $auxiliar->assistant_moduleID = $cxp->accountsPayable_id;
            $auxiliar->assistant_money = $folioAfectar->accountsPayable_money;
            $auxiliar->assistant_typeChange = $folioAfectar->accountsPayable_typeChange;
            $auxiliar->assistant_account = $folioAfectar->accountsPayable_provider;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = null;
            $auxiliar->assistant_payment = $folioAfectar->accountsPayable_total;
            $auxiliar->assistant_apply = $cxp->accountsPayable_origin;
            $auxiliar->assistant_applyID =  $cxp->accountsPayable_originID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->accountsPayable_reference;


            $auxiliar->save();
        }
    }

    public function agregarCxPP($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $folio)->first();

        // dd($folioAfectar);
        if ($folioAfectar->accountsPayable_status == $this->estatus[1] && $folioAfectar->accountsPayable_movement == 'Anticipo') {

            //agregamos una nueva cuenta por pagar para la compra
            $cuentaPagar = new PROC_ACCOUNTS_PAYABLE_P();
            $cuentaPagar->accountsPayableP_movement = $folioAfectar->accountsPayable_movement;
            $cuentaPagar->accountsPayableP_movementID = $folioAfectar->accountsPayable_movementID;
            $cuentaPagar->accountsPayableP_issuedate = Carbon::parse($folioAfectar->accountsPayable_issuedate)->format('Y-m-d H:i:s');
            $cuentaPagar->accountsPayableP_expiration =  Carbon::parse($folioAfectar->accountsPayable_expiration)->format('Y-m-d H:i:s');

            //dias credito y moratorio
            $emision = Carbon::parse($folioAfectar->accountsPayable_issuedate)->format('Y-m-d');
            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
            $vencimiento = Carbon::parse($folioAfectar->accountsPayable_expiration)->format('Y-m-d');
            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

            $diasCredito = $currentDate->diffInDays($shippingDate);
            $diasMoratorio = $shippingDate->diffInDays($currentDate);
            $cuentaPagar->accountsPayableP_creditDays = $diasCredito;
            $cuentaPagar->accountsPayableP_moratoriumDays = '-' . $diasMoratorio;
            $cuentaPagar->accountsPayableP_money = $folioAfectar->accountsPayable_money;
            $cuentaPagar->accountsPayableP_typeChange = $folioAfectar->accountsPayable_typeChange;
            $cuentaPagar->accountsPayableP_moneyAccount = $folioAfectar->accountsPayable_moneyAccount;

            $cuentaPagar->accountsPayableP_provider = $folioAfectar->accountsPayable_provider;
            $cuentaPagar->accountsPayableP_condition = $folioAfectar->accountsPayable_condition;

            $cuentaPagar->accountsPayableP_formPayment = $folioAfectar->accountsPayable_formPayment;
            $cuentaPagar->accountsPayableP_amount = $folioAfectar->accountsPayable_amount;
            $cuentaPagar->accountsPayableP_taxes = $folioAfectar->accountsPayable_taxes;
            $cuentaPagar->accountsPayableP_total = $folioAfectar->accountsPayable_total;
            $cuentaPagar->accountsPayableP_balanceTotal = $folioAfectar->accountsPayable_total;
            $cuentaPagar->accountsPayableP_reference = $folioAfectar->accountsPayable_reference;
            $cuentaPagar->accountsPayableP_balance = $folioAfectar->accountsPayable_total;
            $cuentaPagar->accountsPayableP_company = $folioAfectar->accountsPayable_company;
            $cuentaPagar->accountsPayableP_branchOffice = $folioAfectar->accountsPayable_branchOffice;
            $cuentaPagar->accountsPayableP_user = $folioAfectar->accountsPayable_user;
            $cuentaPagar->accountsPayableP_status = $this->estatus[1];
            $cuentaPagar->accountsPayableP_concept = $folioAfectar->accountsPayable_concept;

            $create = $cuentaPagar->save();

            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por pagar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por pagar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }


            return response()->json(['message' => $message, 'estatus' => $estatus]);
        }
    }

    public function agregarCxPPCheque($folio)
    {

        $folioAfectar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $folio)->first();

        // dd($folioAfectar);
        if (($folioAfectar->accountsPayable_status == $this->estatus[1] && $folioAfectar->accountsPayable_movement == 'Sol. de Cheque/Transferencia') || ($folioAfectar->accountsPayable_status == $this->estatus[2] && $folioAfectar->accountsPayable_movement == 'Pago de Facturas')) {

            //agregamos una nueva cuenta por pagar para la compra
            $cuentaPagar = new PROC_ACCOUNTS_PAYABLE_P();
            $cuentaPagar->accountsPayableP_movement = 'Sol. de Cheque/Transferencia';
            $cuentaPagar->accountsPayableP_movementID = $folioAfectar->accountsPayable_movementID;
            $cuentaPagar->accountsPayableP_issuedate = Carbon::parse($folioAfectar->accountsPayable_issuedate)->format('Y-m-d H:i:s');
            $cuentaPagar->accountsPayableP_expiration =  Carbon::parse($folioAfectar->accountsPayable_expiration)->format('Y-m-d H:i:s');

            //dias credito y moratorio
            $emision = Carbon::parse($folioAfectar->accountsPayable_issuedate)->format('Y-m-d');
            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
            $vencimiento = Carbon::parse($folioAfectar->accountsPayable_expiration)->format('Y-m-d');
            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

            $diasCredito = $currentDate->diffInDays($shippingDate);
            $diasMoratorio = $shippingDate->diffInDays($currentDate);
            $cuentaPagar->accountsPayableP_creditDays = $diasCredito;
            $cuentaPagar->accountsPayableP_moratoriumDays = '-' . $diasMoratorio;
            $cuentaPagar->accountsPayableP_money = $folioAfectar->accountsPayable_money;
            $cuentaPagar->accountsPayableP_typeChange = $folioAfectar->accountsPayable_typeChange;
            $cuentaPagar->accountsPayableP_moneyAccount = $folioAfectar->accountsPayable_moneyAccount;

            $cuentaPagar->accountsPayableP_provider = $folioAfectar->accountsPayable_provider;
            $cuentaPagar->accountsPayableP_condition = $folioAfectar->accountsPayable_condition;

            $cuentaPagar->accountsPayableP_formPayment = $folioAfectar->accountsPayable_paymentMethod;
            $cuentaPagar->accountsPayableP_amount = $folioAfectar->accountsPayable_amount;
            $cuentaPagar->accountsPayableP_taxes = $folioAfectar->accountsPayable_taxes;
            $cuentaPagar->accountsPayableP_total = $folioAfectar->accountsPayable_total;
            $cuentaPagar->accountsPayableP_balanceTotal = $folioAfectar->accountsPayable_total;
            $cuentaPagar->accountsPayableP_reference = $folioAfectar->accountsPayable_reference;
            $cuentaPagar->accountsPayableP_balance = $folioAfectar->accountsPayable_total;
            $cuentaPagar->accountsPayableP_company = $folioAfectar->accountsPayable_company;
            $cuentaPagar->accountsPayableP_branchOffice = $folioAfectar->accountsPayable_branchOffice;
            $cuentaPagar->accountsPayableP_user = $folioAfectar->accountsPayable_user;
            $cuentaPagar->accountsPayableP_status = $this->estatus[1];
            $cuentaPagar->accountsPayableP_origin = $folioAfectar->accountsPayable_movement;
            $cuentaPagar->accountsPayableP_originID = $folioAfectar->accountsPayable_movementID;
            $cuentaPagar->accountsPayableP_originType = 'Din';
            $cuentaPagar->accountsPayableP_concept = $folioAfectar->accountsPayable_concept;

            $create = $cuentaPagar->save();

            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por pagar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por pagar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }


            return response()->json(['message' => $message, 'estatus' => $estatus]);
        }
    }

    public function agregarTesoreria($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', $folio)->first();
        // dd($folioAfectar);
        if ($folioAfectar->accountsPayable_status == $this->estatus[1] && $folioAfectar->accountsPayable_movement == 'Anticipo') {

            //agregamos a tesoreria
            $tesoreria = new PROC_TREASURY();
            $folioEgreso = PROC_TREASURY::where('treasuries_movement', '=', 'Egreso')->where('treasuries_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->max('treasuries_movementID');
            $folioEgreso = $folioEgreso == null ? 1 : $folioEgreso + 1;
            $tesoreria->treasuries_movement = 'Egreso';
            $tesoreria->treasuries_movementID = $folioEgreso;
            $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->accountsPayable_issuedate)->format('Y-m-d H:i:s');
            $tesoreria->treasuries_money = $folioAfectar->accountsPayable_money;
            $tesoreria->treasuries_typeChange = $folioAfectar->accountsPayable_typeChange;
            $tesoreria->treasuries_moneyAccount = $folioAfectar->accountsPayable_moneyAccount;
            // $tesoreria->treasuries_moneyAccountOrigin = $folioAfectar->accountsPayable_moneyAccount;
            $tesoreria->treasuries_paymentMethod = $folioAfectar->accountsPayable_formPayment;
            $tesoreria->treasuries_beneficiary = $folioAfectar->accountsPayable_provider;
            $tesoreria->treasuries_reference = $folioAfectar->accountsPayable_reference;
            $tesoreria->treasuries_amount = $folioAfectar->accountsPayable_amount;
            $tesoreria->treasuries_taxes = $folioAfectar->accountsPayable_taxes;
            $tesoreria->treasuries_total = $folioAfectar->accountsPayable_total;
            $tesoreria->treasuries_company = $folioAfectar->accountsPayable_company;
            $tesoreria->treasuries_branchOffice = $folioAfectar->accountsPayable_branchOffice;
            $tesoreria->treasuries_user = $folioAfectar->accountsPayable_user;
            $tesoreria->treasuries_status = $this->estatus[2];
            $tesoreria->treasuries_originType = 'CxP';
            $tesoreria->treasuries_origin = 'Anticipo';
            $tesoreria->treasuries_originID = $folioAfectar->accountsPayable_movementID;
            $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $tesoreria->save();



            $lastId = PROC_ACCOUNTS_PAYABLE::latest('accountsPayable_id')->first()->accountsPayable_id;
            $lastId2 = PROC_TREASURY::latest('treasuries_id')->first()->treasuries_id;

            //Guardamos la información en la tabla PROC_TREASORIES_DETAILS
            $detalle = new PROC_TREASURY_DETAILS();
            $detalle->treasuriesDetails_treasuriesID = $lastId2;
            $detalle->treasuriesDetails_amount = $folioAfectar->accountsPayable_total;
            $detalle->treasuriesDetails_apply = $folioAfectar->accountsPayable_movement;
            $detalle->treasuriesDetails_applyIncrement = $folioAfectar->accountsPayable_movementID;
            $detalle->treasuriesDetails_paymentMethod = $folioAfectar->accountsPayable_formPayment;
            $detalle->treasuriesDetails_movReference = $lastId;
            $detalle->save();

            //Agregamos a auxiliar
            $this->auxiliarCaja($lastId);
            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por pagar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por pagar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }


            return $lastId2;
        }

        if ($folioAfectar->accountsPayable_status == $this->estatus[2] && $folioAfectar->accountsPayable_movement == 'Pago de Facturas') {


            //agregamos a tesoreria
            $tesoreria = new PROC_TREASURY();
            $folioEgreso = PROC_TREASURY::where('treasuries_movement', '=', 'Egreso')->where('treasuries_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->max('treasuries_movementID');
            $folioEgreso = $folioEgreso == null ? 1 : $folioEgreso + 1;
            $tesoreria->treasuries_movement = 'Egreso';
            $tesoreria->treasuries_movementID = $folioEgreso;
            $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->accountsPayable_issuedate)->format('Y-m-d H:i:s');
            $tesoreria->treasuries_money = $folioAfectar->accountsPayable_money;
            $tesoreria->treasuries_typeChange = $folioAfectar->accountsPayable_typeChange;
            $tesoreria->treasuries_moneyAccount = $folioAfectar->accountsPayable_moneyAccount;
            $tesoreria->treasuries_moneyAccountOrigin = $folioAfectar->accountsPayable_moneyAccount;
            $tesoreria->treasuries_paymentMethod = $folioAfectar->accountsPayable_formPayment;
            $tesoreria->treasuries_beneficiary = $folioAfectar->accountsPayable_provider;
            $tesoreria->treasuries_reference = $folioAfectar->accountsPayable_reference;
            $tesoreria->treasuries_amount = $folioAfectar->accountsPayable_amount;
            $tesoreria->treasuries_taxes = $folioAfectar->accountsPayable_taxes;
            $tesoreria->treasuries_total = $folioAfectar->accountsPayable_total;
            $tesoreria->treasuries_company = $folioAfectar->accountsPayable_company;
            $tesoreria->treasuries_branchOffice = $folioAfectar->accountsPayable_branchOffice;
            $tesoreria->treasuries_user = $folioAfectar->accountsPayable_user;
            $tesoreria->treasuries_status = $this->estatus[2];
            $tesoreria->treasuries_originType = 'CxP';
            $tesoreria->treasuries_origin = 'Pago de Facturas';
            $tesoreria->treasuries_originID = $folioAfectar->accountsPayable_movementID;
            $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $tesoreria->save();

            $lastId = isset($tesoreria->treasuries_id) ?  $tesoreria->treasuries_id : PROC_TREASURY::latest('treasuries_id')->first()->treasuries_id;

            //  //Agregamos a auxiliar
            $this->auxiliar($folioAfectar->accountsPayable_id);
            $this->auxiliarCaja2($lastId);
            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por pagar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por pagar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }


            return $lastId;
        }

        // dd($folioAfectar);
    }

    public function auxiliarCaja($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $folio)->first();


        if ($folioAfectar->accountsPayable_status == $this->estatus[1] || $folioAfectar->accountsPayable_status == $this->estatus[2]) {

            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->accountsPayable_company;
            $auxiliar->assistant_branchKey = $folioAfectar->accountsPayable_branchOffice;
            $auxiliar->assistant_branch = 'CxP';

            $auxiliar->assistant_movement = $folioAfectar->accountsPayable_movement;
            $auxiliar->assistant_movementID = $folioAfectar->accountsPayable_movementID;
            $auxiliar->assistant_module = 'CxP';
            $auxiliar->assistant_moduleID = $folioAfectar->accountsPayable_id;
            $auxiliar->assistant_money = $folioAfectar->accountsPayable_money;
            $auxiliar->assistant_typeChange = $folioAfectar->accountsPayable_typeChange;
            $auxiliar->assistant_account = $folioAfectar->accountsPayable_provider;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = null;
            $auxiliar->assistant_payment = $folioAfectar->accountsPayable_total;
            $auxiliar->assistant_apply = $folioAfectar->accountsPayable_movement;
            $auxiliar->assistant_applyID = $folioAfectar->accountsPayable_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->accountsPayable_reference;

            $auxiliar->save();


            //------------------------------
            //agregar datos a aux  Egreso
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->accountsPayable_company;
            $auxiliar->assistant_branchKey = $folioAfectar->accountsPayable_branchOffice;
            $auxiliar->assistant_branch = 'Din';


            $tesoreria = PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->accountsPayable_movementID)->where('treasuries_originType', '=', 'CxP')
            ->where('treasuries_origin', '=', 'Anticipo')
            ->where('treasuries_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->first();

            $auxiliar->assistant_movement = $tesoreria->treasuries_movement;
            $auxiliar->assistant_movementID = $tesoreria->treasuries_movementID;
            $auxiliar->assistant_module = 'Din';
            $auxiliar->assistant_moduleID = $tesoreria->treasuries_id;
            $auxiliar->assistant_money = $folioAfectar->accountsPayable_money;
            $auxiliar->assistant_typeChange = $folioAfectar->accountsPayable_typeChange;
            $auxiliar->assistant_account = $folioAfectar->accountsPayable_moneyAccount;


            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = null;
            $auxiliar->assistant_payment = $folioAfectar->accountsPayable_total;
            $auxiliar->assistant_apply = $tesoreria->treasuries_movement;
            $auxiliar->assistant_applyID = $tesoreria->treasuries_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->accountsPayable_reference;

            $auxiliar->save();
        }
    }

    public function auxiliarCaja2($folio)
    {
        $folioAfectar = PROC_TREASURY::where('treasuries_id', '=', $folio)->first();


        if ($folioAfectar->treasuries_status == $this->estatus[2]) {
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
            $auxiliar->assistant_applyID = $folioAfectar->treasuries_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->accountsPayable_reference;

            $auxiliar->save();
        }
    }

    public function agregarChequeTesoreria($folio, $originID, $idOrigen)
    {
        $folioAfectar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', $folio)->first();
        // dd($folioAfectar);
        if ($folioAfectar->accountsPayable_status == $this->estatus[1] && $folioAfectar->accountsPayable_movement == 'Sol. de Cheque/Transferencia') {

            //agregamos a tesoreria
            $tesoreria = new PROC_TREASURY();
            $folioEgreso = PROC_TREASURY::where('treasuries_movement', '=', 'Sol. de Cheque/Transferencia')->where('treasuries_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->max('treasuries_movementID');
            $folioEgreso = $folioEgreso == null ? 1 : $folioEgreso + 1;
            $tesoreria->treasuries_movement = 'Sol. de Cheque/Transferencia';
            $tesoreria->treasuries_movementID = $folioEgreso;
            $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->accountsPayable_issuedate)->format('Y-m-d H:i:s');
            $tesoreria->treasuries_concept = $folioAfectar->accountsPayable_concept;
            $tesoreria->treasuries_money = $folioAfectar->accountsPayable_money;
            $tesoreria->treasuries_typeChange = $folioAfectar->accountsPayable_typeChange;
            $tesoreria->treasuries_moneyAccount = $folioAfectar->accountsPayable_moneyAccount;
            $tesoreria->treasuries_moneyAccountOrigin = $folioAfectar->accountsPayable_moneyAccount;
            $tesoreria->treasuries_paymentMethod = $folioAfectar->accountsPayable_formPayment;
            $tesoreria->treasuries_beneficiary = $folioAfectar->accountsPayable_provider;
            $tesoreria->treasuries_reference = $folioAfectar->accountsPayable_reference;
            $tesoreria->treasuries_observations = $folioAfectar->accountsPayable_observations;
            $tesoreria->treasuries_amount = $folioAfectar->accountsPayable_amount;
            $tesoreria->treasuries_taxes = $folioAfectar->accountsPayable_taxes;
            $tesoreria->treasuries_total = $folioAfectar->accountsPayable_total;
            $tesoreria->treasuries_accountBalance = $folioAfectar->accountsPayable_total;
            $tesoreria->treasuries_company = $folioAfectar->accountsPayable_company;
            $tesoreria->treasuries_branchOffice = $folioAfectar->accountsPayable_branchOffice;
            $tesoreria->treasuries_user = $folioAfectar->accountsPayable_user;
            $tesoreria->treasuries_status = $this->estatus[1];
            $tesoreria->treasuries_originType = 'CxP';
            $tesoreria->treasuries_origin = 'Anticipo';
            $tesoreria->treasuries_originID = $originID;
            $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $tesoreria->save();
            $lastId = isset($tesoreria->treasuries_id) ? $tesoreria->treasuries_id : PROC_TREASURY::latest('treasuries_id')->first()->treasuries_id;
            //Guardamos la información en la tabla PROC_TREASORIES_DETAILS
            $detalle = new PROC_TREASURY_DETAILS();
            $detalle->treasuriesDetails_treasuriesID = $tesoreria->treasuries_id;
            $detalle->treasuriesDetails_amount = $folioAfectar->accountsPayable_total;
            $detalle->treasuriesDetails_apply = 'Anticipo';
            $detalle->treasuriesDetails_applyIncrement = $originID;
            $detalle->treasuriesDetails_paymentMethod = $folioAfectar->accountsPayable_formPayment;
            $detalle->treasuriesDetails_movReference = $idOrigen;
            $detalle->save();

            $cuentaOrigen = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', $folio)->first();
            $cuentaOrigen->accountsPayable_originID = $tesoreria->treasuries_movementID;
            $cuentaOrigen->update();

            if ($cuentaOrigen->accountsPayable_status == $this->estatus[1] && $cuentaOrigen->accountsPayable_movement == 'Sol. de Cheque/Transferencia') {
                $movimiento = new PROC_MOVEMENT_FLOW();

                $movimiento->movementFlow_branch = $folioAfectar->accountsPayable_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->accountsPayable_company;
                $movimiento->movementFlow_moduleOrigin = 'Din';
                $movimiento->movementFlow_originID = $tesoreria->treasuries_id;
                $movimiento->movementFlow_movementOrigin = $tesoreria->treasuries_movement;
                $movimiento->movementFlow_movementOriginID =   $tesoreria->treasuries_movementID;
                $movimiento->movementFlow_moduleDestiny = 'CxP';
                $movimiento->movementFlow_destinityID = $folioAfectar->accountsPayable_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->accountsPayable_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->accountsPayable_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $create = $movimiento->save();
            }

            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por pagar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por pagar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }


            return $lastId;
        }
    }

    public function agregarChequeTesoreria2($folio, $originID)
    {
        $folioAfectar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', $folio)->first();
        // dd($folioAfectar);
        if ($folioAfectar->accountsPayable_status == $this->estatus[1] && $folioAfectar->accountsPayable_movement == 'Sol. de Cheque/Transferencia') {

            //agregamos a tesoreria
            $tesoreria = new PROC_TREASURY();
            $folioEgreso = PROC_TREASURY::where('treasuries_movement', '=', 'Sol. de Cheque/Transferencia')->where('treasuries_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->max('treasuries_movementID');
            $folioEgreso = $folioEgreso == null ? 1 : $folioEgreso + 1;
            $tesoreria->treasuries_movement = 'Sol. de Cheque/Transferencia';
            $tesoreria->treasuries_movementID = $folioEgreso;
            $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->accountsPayable_issuedate)->format('Y-m-d H:i:s');
            $tesoreria->treasuries_concept = $folioAfectar->accountsPayable_concept;
            $tesoreria->treasuries_money = $folioAfectar->accountsPayable_money;
            $tesoreria->treasuries_typeChange = $folioAfectar->accountsPayable_typeChange;
            $tesoreria->treasuries_moneyAccount = $folioAfectar->accountsPayable_moneyAccount;
            $tesoreria->treasuries_moneyAccountOrigin = $folioAfectar->accountsPayable_moneyAccount;
            $tesoreria->treasuries_paymentMethod = $folioAfectar->accountsPayable_formPayment;
            $tesoreria->treasuries_beneficiary = $folioAfectar->accountsPayable_provider;
            $tesoreria->treasuries_reference = $folioAfectar->accountsPayable_reference;
            $tesoreria->treasuries_amount = $folioAfectar->accountsPayable_amount;
            $tesoreria->treasuries_taxes = $folioAfectar->accountsPayable_taxes;
            $tesoreria->treasuries_total = $folioAfectar->accountsPayable_total;
            $tesoreria->treasuries_accountBalance = $folioAfectar->accountsPayable_total;
            $tesoreria->treasuries_company = $folioAfectar->accountsPayable_company;
            $tesoreria->treasuries_branchOffice = $folioAfectar->accountsPayable_branchOffice;
            $tesoreria->treasuries_user = $folioAfectar->accountsPayable_user;
            $tesoreria->treasuries_status = $this->estatus[1];
            $tesoreria->treasuries_originType = 'CxP';
            $tesoreria->treasuries_origin = 'Pago de Facturas';
            $tesoreria->treasuries_originID = $originID;
            $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $tesoreria->save();
            $lastId = isset($tesoreria->treasuries_id) ? $tesoreria->treasuries_id : PROC_TREASURY::latest('treasuries_id')->first()->treasuries_id;

            $cuentaOrigen = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', $folio)->first();
            $cuentaOrigen->accountsPayable_originID = $tesoreria->treasuries_movementID;
            $cuentaOrigen->update();


            if ($cuentaOrigen->accountsPayable_status == $this->estatus[1] && $cuentaOrigen->accountsPayable_movement == 'Sol. de Cheque/Transferencia') {
                $movimiento = new PROC_MOVEMENT_FLOW();

                $movimiento->movementFlow_branch = $folioAfectar->accountsPayable_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->accountsPayable_company;
                $movimiento->movementFlow_moduleOrigin = 'Din';
                $movimiento->movementFlow_originID = $tesoreria->treasuries_id;
                $movimiento->movementFlow_movementOrigin = $tesoreria->treasuries_movement;
                $movimiento->movementFlow_movementOriginID =   $tesoreria->treasuries_movementID;
                $movimiento->movementFlow_moduleDestiny = 'CxP';
                $movimiento->movementFlow_destinityID = $folioAfectar->accountsPayable_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->accountsPayable_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->accountsPayable_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $create = $movimiento->save();
            }

            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por pagar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por pagar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }


            return $lastId;
        }
    }

    public function agregarSaldo($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', $folio)->first();
        if ($folioAfectar->accountsPayable_status == $this->estatus[1] && $folioAfectar->accountsPayable_movement == 'Anticipo') {
            $proveedor_saldo = PROC_BALANCE::WHERE('balance_account', $folioAfectar->accountsPayable_provider)->WHERE('balance_companieKey', $folioAfectar->accountsPayable_company)->WHERE('balance_branchKey', $folioAfectar->accountsPayable_branchOffice)->where('balance_branch', '=', 'CxP')->where('balance_money', '=', $folioAfectar->accountsPayable_money)->first();
            if (!$proveedor_saldo) {
                $saldoCuenta = new PROC_BALANCE;
                $saldoCuenta->balance_companieKey = $folioAfectar->accountsPayable_company;
                $saldoCuenta->balance_money = $folioAfectar->accountsPayable_money;
                $saldoCuenta->balance_branchKey = $folioAfectar->accountsPayable_branchOffice;
                $saldoCuenta->balance_balance = 0;
                $saldoCuenta->balance_reconcile = 0;
                $saldoCuenta->balance_branch = "CxP";
                $saldoCuenta->balance_account = $folioAfectar->accountsPayable_provider;
                $saldoCuenta->save();
            }
        }
    }
    public function actualizarSaldo($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', $folio)->first();


        if ($folioAfectar->accountsPayable_status == $this->estatus[1] && $folioAfectar->accountsPayable_movement == 'Anticipo') {
            //buscamos cuenta
            $validarCuenta = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', $folioAfectar->accountsPayable_moneyAccount)->where('moneyAccountsBalance_status', 'Alta')->where('moneyAccountsBalance_money', $folioAfectar->accountsPayable_money)->where('moneyAccountsBalance_company', $folioAfectar->accountsPayable_company)->first();
            $saldo = $validarCuenta->moneyAccountsBalance_balance - $folioAfectar->accountsPayable_total;
            $validarCuenta->moneyAccountsBalance_balance = $saldo;
            $validarCuenta->update();

            $proveedor_saldo = PROC_BALANCE::WHERE('balance_account', $folioAfectar->accountsPayable_provider)->WHERE('balance_companieKey', $folioAfectar->accountsPayable_company)->WHERE('balance_branchKey', $folioAfectar->accountsPayable_branchOffice)->where('balance_branch', '=', 'CxP')->where('balance_money', '=', $folioAfectar->accountsPayable_money)->first();

            $cuenta_saldo = PROC_BALANCE::WHERE('balance_account', $folioAfectar->accountsPayable_moneyAccount)->WHERE('balance_companieKey', $folioAfectar->accountsPayable_company)->WHERE('balance_branchKey', $folioAfectar->accountsPayable_branchOffice)->where('balance_branch', '=', 'Din')->where('balance_money', '=', $folioAfectar->accountsPayable_money)->first();

            $ingreso = floatval($folioAfectar->accountsPayable_total);

            if ($proveedor_saldo) {
                $saldoActual =  $proveedor_saldo->balance_balance === Null ? 0 : floatval($proveedor_saldo->balance_balance);
                $proveedor_saldo->balance_balance = $saldoActual - $ingreso;
                $proveedor_saldo->balance_reconcile = $proveedor_saldo->balance_balance;
                $proveedor_saldo->update();
            } else {
                $saldoCuenta = new PROC_BALANCE;
                $saldoCuenta->balance_companieKey = $folioAfectar->accountsPayable_company;
                $saldoCuenta->balance_money = $folioAfectar->accountsPayable_money;
                $saldoCuenta->balance_branchKey = $folioAfectar->accountsPayable_branchOffice;
                $saldoCuenta->balance_balance = 0 - floatval($folioAfectar->accountsPayable_total);
                $saldoCuenta->balance_reconcile = $saldoCuenta->balance_balance;
                $saldoCuenta->balance_branch = "CxP";
                $saldoCuenta->balance_account = $folioAfectar->accountsPayable_provider;
                $saldoCuenta->save();
            }

            if ($cuenta_saldo) {
                $saldoActual  =   $cuenta_saldo->balance_balance === Null ? 0 : floatval($cuenta_saldo->balance_balance);
                $cuenta_saldo->balance_balance = $saldoActual - $ingreso;
                $cuenta_saldo->update();
                $cuenta_saldo->balance_reconcile = $cuenta_saldo->balance_balance;
                $cuenta_saldo->update();
            } else {
                $cuenta_saldo = new PROC_BALANCE;
                $cuenta_saldo->balance_companieKey = $folioAfectar->accountsPayable_company;
                $cuenta_saldo->balance_money = $folioAfectar->accountsPayable_money;
                $cuenta_saldo->balance_branchKey = $folioAfectar->accountsPayable_branchOffice;
                $cuenta_saldo->balance_balance = 0 - floatval($folioAfectar->accountsPayable_total);
                $cuenta_saldo->balance_reconcile = 0 - floatval($folioAfectar->accountsPayable_total);
                $cuenta_saldo->balance_branch = "Din";
                $cuenta_saldo->balance_account = $folioAfectar->accountsPayable_moneyAccount;
                $cuenta_saldo->save();
            }
        }


        if ($folioAfectar->accountsPayable_status == $this->estatus[2] && $folioAfectar->accountsPayable_movement == 'Pago de Facturas') {

            //buscamos cuenta
            $validarCuenta = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', $folioAfectar->accountsPayable_moneyAccount)->where('moneyAccountsBalance_status', 'Alta')->where('moneyAccountsBalance_money', $folioAfectar->accountsPayable_money)->where('moneyAccountsBalance_company', $folioAfectar->accountsPayable_company)->first();
            $saldo = $validarCuenta->moneyAccountsBalance_balance - $folioAfectar->accountsPayable_total;
            $validarCuenta->moneyAccountsBalance_balance = $saldo;
            $validarCuenta->update();


            $validarSaldo = PROC_BALANCE::where('balance_account', $folioAfectar->accountsPayable_moneyAccount)->where('balance_branchKey', $folioAfectar->accountsPayable_branchOffice)->where('balance_branch', '=', 'Din')->where('balance_money', '=', $folioAfectar->accountsPayable_money)->first();
            if ($validarSaldo) {
                $saldo = $validarSaldo->balance_balance - $folioAfectar->accountsPayable_total;
                $validarSaldo->balance_balance = $saldo;
                $validarSaldo->balance_reconcile = $validarSaldo->balance_balance;
                $validarSaldo->update();
            } else {
                $cuenta_saldo = new PROC_BALANCE;
                $cuenta_saldo->balance_companieKey = $folioAfectar->accountsPayable_company;
                $cuenta_saldo->balance_money = $folioAfectar->accountsPayable_money;
                $cuenta_saldo->balance_branchKey = $folioAfectar->accountsPayable_branchOffice;
                $cuenta_saldo->balance_balance = 0 - floatval($folioAfectar->accountsPayable_total);
                $cuenta_saldo->balance_reconcile = 0 - floatval($folioAfectar->accountsPayable_total);
                $cuenta_saldo->balance_branch = "Din";
                $cuenta_saldo->balance_account = $folioAfectar->accountsPayable_moneyAccount;
                $cuenta_saldo->save();
            }

            $validarSaldo2 = PROC_BALANCE::where('balance_account', $folioAfectar->accountsPayable_provider)->where('balance_branchKey', $folioAfectar->accountsPayable_branchOffice)->where('balance_branch', '=', 'CxP')->where('balance_money', '=', $folioAfectar->accountsPayable_money)->first();
            if ($validarSaldo2) {
                $saldo = $validarSaldo2->balance_balance - $folioAfectar->accountsPayable_total;
                $validarSaldo2->balance_balance = $saldo;
                $validarSaldo2->update();
            }
        }
    }

    public function generarSolitudCheque($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', $folio)->first();
        // dd($folioAfectar);
        if ($folioAfectar->accountsPayable_status == $this->estatus[1] && $folioAfectar->accountsPayable_movement == 'Anticipo') {

            //agregamos a tesoreria
            $tesoreria = new PROC_TREASURY();
            $folioCheque = PROC_TREASURY::where('treasuries_movement', '=', 'Sol. de Cheque/Transferencia')->where('treasuries_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->max('treasuries_movementID');
            $folioCheque = $folioCheque == null ? 1 : $folioCheque + 1;
            $tesoreria->treasuries_movement = 'Sol. de Cheque/Transferencia';
            $tesoreria->treasuries_movementID = $folioCheque;
            $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->accountsPayable_issuedate)->format('Y-m-d H:i:s');
            $tesoreria->treasuries_money = $folioAfectar->accountsPayable_money;
            $tesoreria->treasuries_typeChange = $folioAfectar->accountsPayable_typeChange;
            $tesoreria->treasuries_moneyAccount = $folioAfectar->accountsPayable_moneyAccount;
            $tesoreria->treasuries_moneyAccountOrigin = $folioAfectar->accountsPayable_moneyAccount;
            $tesoreria->treasuries_paymentMethod = $folioAfectar->accountsPayable_paymentMethod;
            $tesoreria->treasuries_beneficiary = $folioAfectar->accountsPayable_provider;
            $tesoreria->treasuries_reference = $folioAfectar->accountsPayable_reference;
            $tesoreria->treasuries_amount = $folioAfectar->accountsPayable_amount;
            $tesoreria->treasuries_taxes = $folioAfectar->accountsPayable_taxes;
            $tesoreria->treasuries_total = $folioAfectar->accountsPayable_total;
            $tesoreria->treasuries_company = $folioAfectar->accountsPayable_company;
            $tesoreria->treasuries_branchOffice = $folioAfectar->accountsPayable_branchOffice;
            $tesoreria->treasuries_user = $folioAfectar->accountsPayable_user;
            $tesoreria->treasuries_status = $this->estatus[1];
            $tesoreria->treasuries_originType = 'CxP';
            $tesoreria->treasuries_origin = $folioAfectar->accountsPayable_movement;
            $tesoreria->treasuries_originID = $folioAfectar->accountsPayable_movementID;
            $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $tesoreria->save();

            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por pagar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por pagar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }


            return response()->json(['message' => $message, 'estatus' => $estatus]);
        }
    }

    public function agregarCxPCheque($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $folio)->first();

        //   dd($folioAfectar);
        if ($folioAfectar->accountsPayable_status == $this->estatus[1] && $folioAfectar->accountsPayable_movement == 'Anticipo') {
            // dd($folioAfectar);
            $folioCheque = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', 'Sol. de Cheque/Transferencia')->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->max('accountsPayable_movementID');
            $folioCheque = $folioCheque == null ? 1 : $folioCheque + 1;

            $cuentaPagar = new PROC_ACCOUNTS_PAYABLE();
            $cuentaPagar->accountsPayable_movement = 'Sol. de Cheque/Transferencia';
            $cuentaPagar->accountsPayable_movementID = $folioCheque;
            $cuentaPagar->accountsPayable_issuedate = Carbon::parse($folioAfectar->accountsPayable_issuedate)->format('Y-m-d H:i:s');
            $cuentaPagar->accountsPayable_money = $folioAfectar->accountsPayable_money;
            $cuentaPagar->accountsPayable_typeChange = $folioAfectar->accountsPayable_typeChange;
            $cuentaPagar->accountsPayable_moneyAccount = $folioAfectar->accountsPayable_moneyAccount;
            $cuentaPagar->accountsPayable_provider = $folioAfectar->accountsPayable_provider;
            $cuentaPagar->accountsPayable_condition = $folioAfectar->accountsPayable_condition;
            $vencimiento = $folioAfectar->accountsPayable_expiration;
            if ($vencimiento != null) {
                $vencimiento2 = Carbon::parse($vencimiento)->format('Y-m-d H:i:s');
            } else {
                $vencimiento2 = null;
            }
            $cuentaPagar->accountsPayable_expiration = $vencimiento2;
            $cuentaPagar->accountsPayable_formPayment = $folioAfectar->accountsPayable_formPayment;
            $cuentaPagar->accountsPayable_amount = $folioAfectar->accountsPayable_amount;
            $cuentaPagar->accountsPayable_taxes = $folioAfectar->accountsPayable_taxes;
            $cuentaPagar->accountsPayable_total = $folioAfectar->accountsPayable_total;
            $cuentaPagar->accountsPayable_concept = $folioAfectar->accountsPayable_concept;
            $cuentaPagar->accountsPayable_reference = $folioAfectar->accountsPayable_reference;
            $cuentaPagar->accountsPayable_observations = $folioAfectar->accountsPayable_observations;
            $cuentaPagar->accountsPayable_balance = $folioAfectar->accountsPayable_total;
            $cuentaPagar->accountsPayable_company = $folioAfectar->accountsPayable_company;
            $cuentaPagar->accountsPayable_branchOffice = $folioAfectar->accountsPayable_branchOffice;
            $cuentaPagar->accountsPayable_user = $folioAfectar->accountsPayable_user;
            $cuentaPagar->accountsPayable_status = $this->estatus[1];
            $cuentaPagar->accountsPayable_origin =  $cuentaPagar->accountsPayable_movement;
            $cuentaPagar->accountsPayable_originID = $cuentaPagar->accountsPayable_movementID;
            $cuentaPagar->accountsPayable_originType = 'Din';
            $cuentaPagar->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $cuentaPagar->updated_at = Carbon::now()->format('Y-m-d H:i:s');

            $create = $cuentaPagar->save();

            $lastId = isset($cuentaPagar->accountsPayable_id) ? $cuentaPagar->accountsPayable_id : PROC_ACCOUNTS_PAYABLE::latest('accountsPayable_id')->first()->accountsPayable_id;



            //Colocamos el cheque a la tabla CXPP
            $this->agregarCxPPCheque($lastId);
            //Colocamos la solicitud de cheque a la tabla Treasuries (Tesoreria)
            $cheque = $this->agregarChequeTesoreria($lastId, $folioAfectar->accountsPayable_movementID, $folioAfectar->accountsPayable_id);
            //Agremos a la tabla ASSISTANT (Auxiliar)
            $this->agregarAuxiliarCheque($lastId);
            $this->agregarMov($lastId);



            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por pagar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por pagar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }

            return $cheque;
        }

        if ($folioAfectar->accountsPayable_status == $this->estatus[2] && $folioAfectar->accountsPayable_movement == 'Pago de Facturas') {

            $folioCheque = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', 'Sol. de Cheque/Transferencia')->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->max('accountsPayable_movementID');
            $folioCheque = $folioCheque == null ? 1 : $folioCheque + 1;

            $cuentaPagar = new PROC_ACCOUNTS_PAYABLE();
            $cuentaPagar->accountsPayable_movement = 'Sol. de Cheque/Transferencia';
            $cuentaPagar->accountsPayable_movementID = $folioCheque;
            $cuentaPagar->accountsPayable_issuedate = Carbon::parse($folioAfectar->accountsPayable_issuedate)->format('Y-m-d H:i:s');
            $cuentaPagar->accountsPayable_money = $folioAfectar->accountsPayable_money;
            $cuentaPagar->accountsPayable_typeChange = $folioAfectar->accountsPayable_typeChange;
            $cuentaPagar->accountsPayable_moneyAccount = $folioAfectar->accountsPayable_moneyAccount;
            $cuentaPagar->accountsPayable_provider = $folioAfectar->accountsPayable_provider;
            $cuentaPagar->accountsPayable_condition = $folioAfectar->accountsPayable_condition;
            $vencimiento = $folioAfectar->accountsPayable_expiration;
            if ($vencimiento != null) {
                $vencimiento2 = Carbon::parse($vencimiento)->format('Y-m-d H:i:s');
            } else {
                $vencimiento2 = null;
            }
            $cuentaPagar->accountsPayable_expiration = $vencimiento2;
            $cuentaPagar->accountsPayable_formPayment = $folioAfectar->accountsPayable_formPayment;
            $cuentaPagar->accountsPayable_amount = $folioAfectar->accountsPayable_amount;
            $cuentaPagar->accountsPayable_taxes = $folioAfectar->accountsPayable_taxes;
            $cuentaPagar->accountsPayable_total = $folioAfectar->accountsPayable_total;
            $cuentaPagar->accountsPayable_concept = $folioAfectar->accountsPayable_concept;
            $cuentaPagar->accountsPayable_reference = $folioAfectar->accountsPayable_reference;
            $cuentaPagar->accountsPayable_balance = $folioAfectar->accountsPayable_total;
            $cuentaPagar->accountsPayable_company = $folioAfectar->accountsPayable_company;
            $cuentaPagar->accountsPayable_branchOffice = $folioAfectar->accountsPayable_branchOffice;
            $cuentaPagar->accountsPayable_user = $folioAfectar->accountsPayable_user;
            $cuentaPagar->accountsPayable_status = $this->estatus[1];
            $cuentaPagar->accountsPayable_origin =  $cuentaPagar->accountsPayable_movement;
            $cuentaPagar->accountsPayable_originID = $cuentaPagar->accountsPayable_movementID;
            $cuentaPagar->accountsPayable_originType = 'Din';
            $cuentaPagar->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $cuentaPagar->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $cuentaPagar->save();

            $lastId = isset($cuentaPagar->accountsPayable_id) ? $cuentaPagar->accountsPayable_id : PROC_ACCOUNTS_PAYABLE::latest('accountsPayable_id')->first()->accountsPayable_id;


            //Colocamos el cheque a la tabla CXPP
            $this->agregarCxPPCheque($lastId);
            //Colocamos la solicitud de cheque a la tabla Treasuries (Tesoreria)
            $cheque =  $this->agregarChequeTesoreria2($lastId, $folioAfectar->accountsPayable_movementID);
            //Agremos a la tabla ASSISTANT (Auxiliar)
            $this->agregarAuxiliarCheque($lastId);
            // $this->agregarMov($lastId);

            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por pagar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por pagar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }

            return $cheque;
        }
    }

    public function agregarAuxiliarCheque($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $folio)->first();

        // dd($folioAfectar);
        if (($folioAfectar->accountsPayable_status == $this->estatus[1] && $folioAfectar->accountsPayable_movement == 'Sol. de Cheque/Transferencia') || ($folioAfectar->accountsPayable_status == $this->estatus[2] && $folioAfectar->accountsPayable_movement == 'Pago de Facturas')) {

            //agregar datos a aux
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->accountsPayable_company;
            $auxiliar->assistant_branchKey = $folioAfectar->accountsPayable_branchOffice;
            $auxiliar->assistant_branch = 'CxP';


            //buscamos el modulo de cxp
            $cxp = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movementID', '=', $folioAfectar->accountsPayable_movementID)->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)->where('accountsPayable_id', '=', $folio)->first();

            $auxiliar->assistant_movement = $cxp->accountsPayable_movement;
            $auxiliar->assistant_movementID = $cxp->accountsPayable_movementID;
            $auxiliar->assistant_module = 'CxP';
            $auxiliar->assistant_moduleID = $cxp->accountsPayable_id;
            $auxiliar->assistant_money = $folioAfectar->accountsPayable_money;
            $auxiliar->assistant_typeChange = $folioAfectar->accountsPayable_typeChange;
            $auxiliar->assistant_account = $folioAfectar->accountsPayable_provider;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = $folioAfectar->accountsPayable_total;
            $auxiliar->assistant_payment = null;
            $auxiliar->assistant_apply = $cxp->accountsPayable_movement;
            $auxiliar->assistant_applyID =  $cxp->accountsPayable_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->accountsPayable_reference;


            $auxiliar->save();
        }
    }

    public function cancelarCxP(Request $request)
    {
        $movimientoCancelar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $request->id)->first();


        if ($movimientoCancelar->accountsPayable_status == $this->estatus[2] && $movimientoCancelar->accountsPayable_movement == 'Pago de Facturas' && $request->tipo == 'Caja') {


            try {
                $pagoCancelado = false;
                $aplicacionCancelado = true;
                $anticipoCancelado = true;

                //regresamos el monto a las cuentas por pagar
                $detalle = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_accountPayableID', '=', $movimientoCancelar->accountsPayable_id)->get();

                // dd($detalle);

                foreach ($detalle as $key => $value) {
                    $cuentaPagar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $value->accountsPayableDetails_movReference)->first();

                    $cuentaPagar->accountsPayable_balance = $cuentaPagar->accountsPayable_balance + $value->accountsPayableDetails_amount;
                    $cuentaPagar->accountsPayable_status = $this->estatus[1];
                    $validar = $cuentaPagar->update();
                    if ($validar) {
                        $pagoCancelado = true;
                    } else {
                        $pagoCancelado = false;
                    }

                    //buscamos el movimiento anterior al de cxp
                    $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $cuentaPagar->accountsPayable_id)->where('movementFlow_movementDestinity', '=', $cuentaPagar->accountsPayable_movement)->where('movementFlow_branch', '=', $cuentaPagar->accountsPayable_branchOffice)->first();
                    // dd($flujo);
                    //regresamos las entradas a cxpp originario desde compras
                    if ($flujo->movementFlow_moduleOrigin == 'Compras') {
                        $entrada = PROC_PURCHASE::where('purchase_id', '=', $flujo->movementFlow_originID)->first();

                        //verificamos si aun existe la entrada en cuenta por pagar pendiente
                        $cuentaPagarPendiente = PROC_ACCOUNTS_PAYABLE_P::where('accountsPayableP_originType', '=', 'Compras')->where('accountsPayableP_origin', '=', $entrada->purchase_movement)->where('accountsPayableP_originID', '=', $entrada->purchase_movementID)->where('accountsPayableP_branchOffice', '=', $entrada->purchase_branchOffice)->first();

                        if ($cuentaPagarPendiente === null) {

                            $cuentaPagarP = new PROC_ACCOUNTS_PAYABLE_P();
                            $cuentaPagarP->accountsPayableP_movement = $entrada->purchase_movement;
                            $cuentaPagarP->accountsPayableP_movementID = $entrada->purchase_movementID;
                            $cuentaPagarP->accountsPayableP_issuedate = Carbon::parse($entrada->purchase_issueDate)->format('Y-m-d');
                            $cuentaPagarP->accountsPayableP_expiration =  Carbon::parse($entrada->purchase_expiration)->format('Y-m-d');

                            //dias credito y moratorio
                            $emision = Carbon::parse($entrada->purchase_issueDate)->format('Y-m-d');
                            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
                            $vencimiento = Carbon::parse($entrada->purchase_expiration)->format('Y-m-d');
                            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

                            $diasCredito = $currentDate->diffInDays($shippingDate);
                            $diasMoratorio = $shippingDate->diffInDays($currentDate);


                            $cuentaPagarP->accountsPayableP_creditDays = $diasCredito;
                            $cuentaPagarP->accountsPayableP_moratoriumDays = '-' . $diasMoratorio;


                            $cuentaPagarP->accountsPayableP_money = $entrada->purchase_money;
                            $cuentaPagarP->accountsPayableP_typeChange = $entrada->purchase_typeChange;
                            $cuentaPagarP->accountsPayableP_provider = $entrada->purchase_provider;
                            $cuentaPagarP->accountsPayableP_condition = $entrada->purchase_condition;

                            $cuentaPagarP->accountsPayableP_amount = $entrada->purchase_amount;
                            $cuentaPagarP->accountsPayableP_taxes = $entrada->purchase_taxes;
                            $cuentaPagarP->accountsPayableP_total = $entrada->purchase_total;
                            $cuentaPagarP->accountsPayableP_balanceTotal = $value->accountsPayableDetails_amount;
                            $cuentaPagarP->accountsPayableP_concept = $entrada->purchase_concept;
                            $cuentaPagarP->accountsPayableP_reference = $entrada->purchase_reference;
                            $cuentaPagarP->accountsPayableP_balance = $value->accountsPayableDetails_amount;
                            $cuentaPagarP->accountsPayableP_company = $entrada->purchase_company;
                            $cuentaPagarP->accountsPayableP_branchOffice = $entrada->purchase_branchOffice;
                            $cuentaPagarP->accountsPayableP_user = $entrada->purchase_user;
                            $cuentaPagarP->accountsPayableP_status = $this->estatus[1];
                            $cuentaPagarP->accountsPayableP_origin = $entrada->purchase_movement;
                            $cuentaPagarP->accountsPayableP_originID = $entrada->purchase_movementID;
                            $cuentaPagarP->accountsPayableP_originType =  'Compras';

                            $validar2 = $cuentaPagarP->save();
                            if ($validar2) {
                                $pagoCancelado = true;
                            } else {
                                $pagoCancelado = false;
                            }
                        } else {
                            $cuentaPagarPendiente->accountsPayableP_balance =   $cuentaPagarPendiente->accountsPayableP_balance + $value->accountsPayableDetails_amount;
                            $cuentaPagarPendiente->accountsPayableP_balanceTotal =   $cuentaPagarPendiente->accountsPayableP_balanceTotal + $value->accountsPayableDetails_amount;
                            $cuentaPagarPendiente->update();
                        }
                    } else if ($flujo->movementFlow_moduleOrigin == 'Factura de Gasto') {
                        $gasto = PROC_EXPENSES::where('expenses_id', '=', $flujo->movementFlow_originID)->first();

                        $cuentaPagarPendiente = PROC_ACCOUNTS_PAYABLE_P::where('accountsPayableP_originType', '=', 'Factura de Gasto')->where('accountsPayableP_origin', '=', $gasto->expenses_movement)->where('accountsPayableP_originID', '=', $gasto->expenses_movementID)->where('accountsPayableP_branchOffice', '=', $gasto->expenses_branchOffice)->first();

                        if ($cuentaPagarPendiente === null) {

                            $cuentaPagarP = new PROC_ACCOUNTS_PAYABLE_P();
                            $cuentaPagarP->accountsPayableP_movement = $gasto->expenses_movement;
                            $cuentaPagarP->accountsPayableP_movementID = $gasto->expenses_movementID;
                            $cuentaPagarP->accountsPayableP_issuedate = Carbon::parse($gasto->expenses_issueDate)->format('Y-m-d H:i:s');
                            $cuentaPagarP->accountsPayableP_expiration =  Carbon::parse($gasto->expenses_expiration)->format('Y-m-d H:i:s');

                            //dias credito y moratorio
                            $emision = Carbon::parse($gasto->expenses_issueDate)->format('Y-m-d');
                            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
                            $vencimiento = Carbon::parse($gasto->expenses_expiration)->format('Y-m-d');
                            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

                            $diasCredito = $currentDate->diffInDays($shippingDate);
                            $diasMoratorio = $shippingDate->diffInDays($currentDate);
                            $cuentaPagarP->accountsPayableP_creditDays = $diasCredito;
                            $cuentaPagarP->accountsPayableP_moratoriumDays = '-' . $diasMoratorio;
                            $cuentaPagarP->accountsPayableP_money = $gasto->expenses_money;
                            $cuentaPagarP->accountsPayableP_typeChange = $gasto->expenses_typeChange;
                            $cuentaPagarP->accountsPayableP_moneyAccount = $gasto->expenses_moneyAccount;

                            $cuentaPagarP->accountsPayableP_provider = $gasto->expenses_provider;
                            $cuentaPagarP->accountsPayableP_provider = $gasto->expenses_provider;
                            $cuentaPagarP->accountsPayableP_condition = $gasto->expenses_condition;

                            $cuentaPagarP->accountsPayableP_formPayment = $gasto->expenses_paymentMethod;
                            $cuentaPagarP->accountsPayableP_amount = $gasto->expenses_amount;
                            $cuentaPagarP->accountsPayableP_taxes = $gasto->expenses_taxes;
                            $cuentaPagarP->accountsPayableP_total = $gasto->expenses_total;
                            $cuentaPagarP->accountsPayableP_balanceTotal = $value->accountsPayableDetails_amount;
                            $cuentaPagarP->accountsPayableP_reference = 'Factura de Gasto';
                            $cuentaPagarP->accountsPayableP_balance = $value->accountsPayableDetails_amount;
                            $cuentaPagarP->accountsPayableP_company = $gasto->expenses_company;
                            $cuentaPagarP->accountsPayableP_branchOffice = $gasto->expenses_branchOffice;
                            $cuentaPagarP->accountsPayableP_user = $gasto->expenses_user;
                            $cuentaPagarP->accountsPayableP_status = $this->estatus[1];
                            $cuentaPagarP->accountsPayableP_origin = $gasto->expenses_movement;
                            $cuentaPagarP->accountsPayableP_originID = $gasto->expenses_movementID;
                            $cuentaPagarP->accountsPayableP_originType = 'Factura de Gasto';
                            $validar2 = $cuentaPagarP->save();
                            if ($validar2) {
                                $pagoCancelado = true;
                            } else {
                                $pagoCancelado = false;
                            }
                        } else {
                            $cuentaPagarPendiente->accountsPayableP_balance =   $cuentaPagarPendiente->accountsPayableP_balance + $value->accountsPayableDetails_amount;
                            $cuentaPagarPendiente->accountsPayableP_balanceTotal =   $cuentaPagarPendiente->accountsPayableP_balanceTotal + $value->accountsPayableDetails_amount;
                            $cuentaPagarPendiente->update();
                        }
                    }
                }

                //regresamos el saldo a la cuenta donde se genero el movimiento
                $cuenta = PROC_BALANCE::where('balance_account', '=', $movimientoCancelar->accountsPayable_moneyAccount)->where('balance_branchKey', '=', $movimientoCancelar->accountsPayable_branchOffice)->where('balance_money', '=', $movimientoCancelar->accountsPayable_money)->first();

                $cuenta->balance_balance = $cuenta->balance_balance + $movimientoCancelar->accountsPayable_total;
                $cuenta->balance_reconcile = $cuenta->balance_balance;
                $validar3 = $cuenta->update();
                if ($validar3) {
                    $pagoCancelado = true;
                    $this->actualizarSaldosCuentasDinero($movimientoCancelar->accountsPayable_id, $movimientoCancelar->accountsPayable_money);
                } else {
                    $pagoCancelado = false;
                }

                $cuenta2 = PROC_BALANCE::where('balance_account', '=', $movimientoCancelar->accountsPayable_provider)->where('balance_branchKey', '=', $movimientoCancelar->accountsPayable_branchOffice)->where('balance_branch', '=', 'CxP')->where('balance_money', '=', $movimientoCancelar->accountsPayable_money)->first();

                $cuenta2->balance_balance = $cuenta2->balance_balance + $movimientoCancelar->accountsPayable_total;
                $cuenta2->balance_reconcile = $cuenta2->balance_balance;
                $validar4 = $cuenta2->update();
                if ($validar4) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }

                //Cancelamos egreso o solicitud de cheque en tesoreria
                $tesoreria = PROC_TREASURY::where('treasuries_origin', '=', $movimientoCancelar->accountsPayable_movement)->where('treasuries_originID', '=', $movimientoCancelar->accountsPayable_movementID)->where('treasuries_branchOffice', '=', $movimientoCancelar->accountsPayable_branchOffice)->first();

                $tesoreria->treasuries_status = $this->estatus[3];
                $validar5 = $tesoreria->update();
                if ($validar5) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }


                //agregamos auxiliar de cancelacion de egreso o solicitud de cheque en tesoreria

                $auxiliar2 = new PROC_ASSISTANT();

                $auxiliar2->assistant_companieKey = $movimientoCancelar->accountsPayable_company;
                $auxiliar2->assistant_branchKey = $movimientoCancelar->accountsPayable_branchOffice;
                $auxiliar2->assistant_branch = 'CxP';

                $auxiliar2->assistant_movement = $movimientoCancelar->accountsPayable_movement;
                $auxiliar2->assistant_movementID = $movimientoCancelar->accountsPayable_movementID;
                $auxiliar2->assistant_module = 'CxP';
                $auxiliar2->assistant_moduleID = $movimientoCancelar->accountsPayable_id;
                $auxiliar2->assistant_money = $movimientoCancelar->accountsPayable_money;
                $auxiliar2->assistant_typeChange = $movimientoCancelar->accountsPayable_typeChange;
                $auxiliar2->assistant_account = $movimientoCancelar->accountsPayable_provider;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar2->assistant_year = $year;
                $auxiliar2->assistant_period = $period;
                $auxiliar2->assistant_charge = null;
                $auxiliar2->assistant_payment = '-' . $movimientoCancelar->accountsPayable_total;
                $auxiliar2->assistant_apply = $movimientoCancelar->accountsPayable_origin;
                $auxiliar2->assistant_applyID =  $movimientoCancelar->accountsPayable_originID;
                $auxiliar2->assistant_canceled = 1;
                $auxiliar2->assistant_reference = $movimientoCancelar->accountsPayable_reference;
                $validar6 = $auxiliar2->save();
                if ($validar6) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }

                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $tesoreria->treasuries_company;
                $auxiliar->assistant_branchKey = $tesoreria->treasuries_branchOffice;
                $auxiliar->assistant_branch = 'Din';


                $auxiliar->assistant_movement = $tesoreria->treasuries_movement;
                $auxiliar->assistant_movementID = $tesoreria->treasuries_movementID;
                $auxiliar->assistant_module = 'Din';
                $auxiliar->assistant_moduleID = $tesoreria->treasuries_id;
                $auxiliar->assistant_money = $tesoreria->treasuries_money;
                $auxiliar->assistant_typeChange = $tesoreria->treasuries_typeChange;
                $auxiliar->assistant_account = $tesoreria->treasuries_moneyAccount;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = null;
                $auxiliar->assistant_payment = '-' . $tesoreria->treasuries_total;
                $auxiliar->assistant_apply = $tesoreria->treasuries_movement;
                $auxiliar->assistant_applyID = $tesoreria->treasuries_movementID;
                $auxiliar->assistant_canceled = 1;

                $validar7 = $auxiliar->save();
                if ($validar7) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }
                //cancelamos los movimientos
                $movimiento = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $movimientoCancelar->accountsPayable_id)->where('movementFlow_movementDestinity', '=', $movimientoCancelar->accountsPayable_movement)->where('movementFlow_branch', '=', $movimientoCancelar->accountsPayable_branchOffice)->first();
                $movimiento->movementFlow_cancelled = 1;
                $validar8 = $movimiento->update();
                if ($validar8) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }

                $movimiento2 = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $movimientoCancelar->accountsPayable_id)->where('movementFlow_movementOrigin', '=', $movimientoCancelar->accountsPayable_movement)->where('movementFlow_branch', '=', $movimientoCancelar->accountsPayable_branchOffice)->first();
                $movimiento2->movementFlow_cancelled = 1;
                $validar9 = $movimiento2->update();
                if ($validar9) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }

                //cancelamos el folio
                $movimientoCancelar->accountsPayable_status = $this->estatus[3];
                $cancelado = $movimientoCancelar->update();

                if ($cancelado) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }
            } catch (\Exception $e) {
                dd($e);
                $pagoCancelado = false;
                $message = $e->getMessage();
            }
        }

        if ($movimientoCancelar->accountsPayable_status == $this->estatus[2] && $movimientoCancelar->accountsPayable_movement == 'Pago de Facturas' && $request->tipo == 'Banco') {


            $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $movimientoCancelar->accountsPayable_id)->where('movementFlow_movementOrigin', '=', $movimientoCancelar->accountsPayable_movement)->where('movementFlow_moduleDestiny', '=', 'Din')->where('movementFlow_movementDestinity', '=', 'Sol. de Cheque/Transferencia')->where('movementFlow_branch', '=', $movimientoCancelar->accountsPayable_branchOffice)->first();

            //buscamos la solicitud de cheque en tesoreria
            $solicitudCheque = PROC_TREASURY::where('treasuries_id', '=', $flujo->movementFlow_destinityID)->first();


            if ($solicitudCheque->treasuries_status === $this->estatus[2]) {
                $status = 400;
                $message = 'No se puede cancelar el pago, ya que tiene una solicitud de cheque asociada.' . ' Movimiento: ' . $movimientoCancelar->accountsPayable_movement . ' Folio: ' . $movimientoCancelar->accountsPayable_movementID;
                return response()->json(['mensaje' => $message, 'estatus' => $status]);
            }

            try {
                $pagoCancelado = false;
                $aplicacionCancelado = true;
                $anticipoCancelado = true;
                //regresamos el monto a las cuentas por pagar
                $detalle = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_accountPayableID', '=', $movimientoCancelar->accountsPayable_id)->get();

                foreach ($detalle as $key => $value) {
                    $cuentaPagar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $value->accountsPayableDetails_movReference)->first();

                    $cuentaPagar->accountsPayable_balance = $cuentaPagar->accountsPayable_balance + $value->accountsPayableDetails_amount;
                    $cuentaPagar->accountsPayable_status = $this->estatus[1];
                    $validar = $cuentaPagar->update();
                    if ($validar) {
                        $pagoCancelado = true;
                    } else {
                        $pagoCancelado = false;
                    }

                    //buscamos el movimiento anterior al de cxp
                    $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $cuentaPagar->accountsPayable_id)->where('movementFlow_movementDestinity', '=', $cuentaPagar->accountsPayable_movement)->where('movementFlow_branch', '=', $cuentaPagar->accountsPayable_branchOffice)->first();

                    //regresamos las entradas a cxpp originario desde compras
                    if ($flujo->movementFlow_moduleOrigin == 'Compras') {
                        $entrada = PROC_PURCHASE::where('purchase_id', '=', $flujo->movementFlow_originID)->first();

                        //verificamos si aun existe la entrada en cuenta por pagar pendiente
                        $cuentaPagarPendiente = PROC_ACCOUNTS_PAYABLE_P::where('accountsPayableP_originType', '=', 'Compras')->where('accountsPayableP_origin', '=', $entrada->purchase_movement)->where('accountsPayableP_originID', '=', $entrada->purchase_movementID)->where('accountsPayableP_branchOffice', '=', $entrada->purchase_branchOffice)->first();


                        if ($cuentaPagarPendiente === null) {

                            $cuentaPagarP = new PROC_ACCOUNTS_PAYABLE_P();
                            $cuentaPagarP->accountsPayableP_movement = $entrada->purchase_movement;
                            $cuentaPagarP->accountsPayableP_movementID = $entrada->purchase_movementID;
                            $cuentaPagarP->accountsPayableP_issuedate = Carbon::parse($entrada->purchase_issueDate)->format('Y-m-d');
                            $cuentaPagarP->accountsPayableP_expiration =  Carbon::parse($entrada->purchase_expiration)->format('Y-m-d');

                            //dias credito y moratorio
                            $emision = Carbon::parse($entrada->purchase_issueDate)->format('Y-m-d');
                            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
                            $vencimiento = Carbon::parse($entrada->purchase_expiration)->format('Y-m-d');
                            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

                            $diasCredito = $currentDate->diffInDays($shippingDate);
                            $diasMoratorio = $shippingDate->diffInDays($currentDate);


                            $cuentaPagarP->accountsPayableP_creditDays = $diasCredito;
                            $cuentaPagarP->accountsPayableP_moratoriumDays = '-' . $diasMoratorio;


                            $cuentaPagarP->accountsPayableP_money = $entrada->purchase_money;
                            $cuentaPagarP->accountsPayableP_typeChange = $entrada->purchase_typeChange;
                            $cuentaPagarP->accountsPayableP_provider = $entrada->purchase_provider;
                            $cuentaPagarP->accountsPayableP_condition = $entrada->purchase_condition;

                            $cuentaPagarP->accountsPayableP_amount = $entrada->purchase_amount;
                            $cuentaPagarP->accountsPayableP_taxes = $entrada->purchase_taxes;
                            $cuentaPagarP->accountsPayableP_total = $entrada->purchase_total;
                            $cuentaPagarP->accountsPayableP_balanceTotal = $value->accountsPayableDetails_amount;
                            $cuentaPagarP->accountsPayableP_concept = $entrada->purchase_concept;
                            $cuentaPagarP->accountsPayableP_reference = $entrada->purchase_reference;
                            $cuentaPagarP->accountsPayableP_balance = $value->accountsPayableDetails_amount;
                            $cuentaPagarP->accountsPayableP_company = $entrada->purchase_company;
                            $cuentaPagarP->accountsPayableP_branchOffice = $entrada->purchase_branchOffice;
                            $cuentaPagarP->accountsPayableP_user = $entrada->purchase_user;
                            $cuentaPagarP->accountsPayableP_status = $this->estatus[1];
                            $cuentaPagarP->accountsPayableP_origin = $entrada->purchase_movement;
                            $cuentaPagarP->accountsPayableP_originID = $entrada->purchase_movementID;
                            $cuentaPagarP->accountsPayableP_originType =  'Compras';

                            $validar2 = $cuentaPagarP->save();
                            if ($validar2) {
                                $pagoCancelado = true;
                            } else {
                                $pagoCancelado = false;
                            }
                        } else {
                            $cuentaPagarPendiente->accountsPayableP_balance =   $cuentaPagarPendiente->accountsPayableP_balance + $value->accountsPayableDetails_amount;
                            $cuentaPagarPendiente->accountsPayableP_balanceTotal =   $cuentaPagarPendiente->accountsPayableP_balanceTotal + $value->accountsPayableDetails_amount;
                            $cuentaPagarPendiente->update();
                        }
                    } else if ($flujo->movementFlow_moduleOrigin == 'Factura de Gasto') {
                        $gasto = PROC_EXPENSES::where('expenses_id', '=', $flujo->movementFlow_originID)->first();

                        $cuentaPagarPendiente = PROC_ACCOUNTS_PAYABLE_P::where('accountsPayableP_originType', '=', 'Factura de Gasto')->where('accountsPayableP_origin', '=', $gasto->expenses_movement)->where('accountsPayableP_originID', '=', $gasto->expenses_movementID)->where('accountsPayableP_branchOffice', '=', $gasto->expenses_branchOffice)->first();

                        if ($cuentaPagarPendiente === null) {
                            $cuentaPagarP = new PROC_ACCOUNTS_PAYABLE_P();
                            $cuentaPagarP->accountsPayableP_movement = $gasto->expenses_movement;
                            $cuentaPagarP->accountsPayableP_movementID = $gasto->expenses_movementID;
                            $cuentaPagarP->accountsPayableP_issuedate = Carbon::parse($gasto->expenses_issueDate)->format('Y-m-d H:i:s');
                            $cuentaPagarP->accountsPayableP_expiration =  Carbon::parse($gasto->expenses_expiration)->format('Y-m-d H:i:s');

                            //dias credito y moratorio
                            $emision = Carbon::parse($gasto->expenses_issueDate)->format('Y-m-d');
                            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
                            $vencimiento = Carbon::parse($gasto->expenses_expiration)->format('Y-m-d');
                            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

                            $diasCredito = $currentDate->diffInDays($shippingDate);
                            $diasMoratorio = $shippingDate->diffInDays($currentDate);
                            $cuentaPagarP->accountsPayableP_creditDays = $diasCredito;
                            $cuentaPagarP->accountsPayableP_moratoriumDays = '-' . $diasMoratorio;
                            $cuentaPagarP->accountsPayableP_money = $gasto->expenses_money;
                            $cuentaPagarP->accountsPayableP_typeChange = $gasto->expenses_typeChange;
                            $cuentaPagarP->accountsPayableP_moneyAccount = $gasto->expenses_moneyAccount;

                            $cuentaPagarP->accountsPayableP_provider = $gasto->expenses_provider;
                            $cuentaPagarP->accountsPayableP_provider = $gasto->expenses_provider;
                            $cuentaPagarP->accountsPayableP_condition = $gasto->expenses_condition;

                            $cuentaPagarP->accountsPayableP_formPayment = $gasto->expenses_paymentMethod;
                            $cuentaPagarP->accountsPayableP_amount = $gasto->expenses_amount;
                            $cuentaPagarP->accountsPayableP_taxes = $gasto->expenses_taxes;
                            $cuentaPagarP->accountsPayableP_total = $gasto->expenses_total;
                            $cuentaPagarP->accountsPayableP_balanceTotal = $value->accountsPayableDetails_amount;
                            $cuentaPagarP->accountsPayableP_reference = 'Factura de Gasto';
                            $cuentaPagarP->accountsPayableP_balance = $value->accountsPayableDetails_amount;
                            $cuentaPagarP->accountsPayableP_company = $gasto->expenses_company;
                            $cuentaPagarP->accountsPayableP_branchOffice = $gasto->expenses_branchOffice;
                            $cuentaPagarP->accountsPayableP_user = $gasto->expenses_user;
                            $cuentaPagarP->accountsPayableP_status = $this->estatus[1];
                            $cuentaPagarP->accountsPayableP_origin = $gasto->expenses_movement;
                            $cuentaPagarP->accountsPayableP_originID = $gasto->expenses_movementID;
                            $cuentaPagarP->accountsPayableP_originType = 'Factura de Gasto';

                            $validar2 = $cuentaPagarP->save();
                            if ($validar2) {
                                $pagoCancelado = true;
                            } else {
                                $pagoCancelado = false;
                            }
                        } else {
                            $cuentaPagarPendiente->accountsPayableP_balance =   $cuentaPagarPendiente->accountsPayableP_balance + $value->accountsPayableDetails_amount;
                            $cuentaPagarPendiente->accountsPayableP_balanceTotal =   $cuentaPagarPendiente->accountsPayableP_balanceTotal + $value->accountsPayableDetails_amount;
                            $cuentaPagarPendiente->update();
                        }
                    }
                }

                $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $movimientoCancelar->accountsPayable_id)->where('movementFlow_moduleOrigin', '=', 'CxP')->where('movementFlow_branch', '=', $movimientoCancelar->accountsPayable_branchOffice)->first();
                $flujo->movementFlow_cancelled = 1;
                $validar3 = $flujo->update();
                if ($validar3) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }

                $tesoreria = PROC_TREASURY::where('treasuries_id', '=', $flujo->movementFlow_destinityID)->first();

                if ($tesoreria != null) {
                    $tesoreria->treasuries_status = $this->estatus[3];
                    $validar4 = $tesoreria->update();
                }

                if ($validar4) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }

                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $movimientoCancelar->accountsPayable_company;
                $auxiliar->assistant_branchKey = $movimientoCancelar->accountsPayable_branchOffice;
                $auxiliar->assistant_branch = 'CxP';
                $auxiliar->assistant_movement = $movimientoCancelar->accountsPayable_movement;
                $auxiliar->assistant_movementID = $movimientoCancelar->accountsPayable_movementID;
                $auxiliar->assistant_module = 'CxP';
                $auxiliar->assistant_moduleID = $movimientoCancelar->accountsPayable_id;
                $auxiliar->assistant_money = $movimientoCancelar->accountsPayable_money;
                $auxiliar->assistant_typeChange = $movimientoCancelar->accountsPayable_typeChange;
                $auxiliar->assistant_account = $movimientoCancelar->accountsPayable_provider;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = null;
                $auxiliar->assistant_payment = '-' . $movimientoCancelar->accountsPayable_total;
                $auxiliar->assistant_apply = $movimientoCancelar->accountsPayable_origin;
                $auxiliar->assistant_applyID =  $movimientoCancelar->accountsPayable_originID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $movimientoCancelar->accountsPayable_reference;


                $validar5 = $auxiliar->save();

                if ($validar5) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }

                $flujo2 = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $flujo->movementFlow_destinityID)->where('movementFlow_moduleOrigin', '=', $flujo->movementFlow_moduleDestiny)->where('movementFlow_movementOrigin', '=', $flujo->movementFlow_movementDestinity)->where('movementFlow_branch', '=', $flujo->movementFlow_branch)->first();
                $flujo2->movementFlow_cancelled = 1;
                $validar6 = $flujo2->update();
                if ($validar6) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }

                $cxp = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $flujo2->movementFlow_destinityID)->first();


                $auxiliar2 = new PROC_ASSISTANT();

                $auxiliar2->assistant_companieKey = $cxp->accountsPayable_company;
                $auxiliar2->assistant_branchKey = $cxp->accountsPayable_branchOffice;
                $auxiliar2->assistant_branch = 'CxP';
                $auxiliar2->assistant_movement = $cxp->accountsPayable_movement;
                $auxiliar2->assistant_movementID = $cxp->accountsPayable_movementID;
                $auxiliar2->assistant_module = 'CxP';
                $auxiliar2->assistant_moduleID = $cxp->accountsPayable_id;
                $auxiliar2->assistant_money = $cxp->accountsPayable_money;
                $auxiliar2->assistant_typeChange = $cxp->accountsPayable_typeChange;
                $auxiliar2->assistant_account = $cxp->accountsPayable_provider;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar2->assistant_year = $year;
                $auxiliar2->assistant_period = $period;
                $auxiliar2->assistant_charge = '-' . $cxp->accountsPayable_total;
                $auxiliar2->assistant_payment = null;
                $auxiliar2->assistant_apply = $cxp->accountsPayable_movement;
                $auxiliar2->assistant_applyID =  $cxp->accountsPayable_movementID;
                $auxiliar2->assistant_canceled = 1;
                $auxiliar2->assistant_reference = $cxp->accountsPayable_reference;
                $validar7 = $auxiliar2->save();
                if ($validar7) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }

                $cxp->accountsPayable_status = $this->estatus[3];
                $validar8 = $cxp->update();
                if ($validar8) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }

                //flujo de entrada a pago
                $flujo3 = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $flujo->movementFlow_originID)->where('movementFlow_moduleDestiny', '=', $flujo->movementFlow_moduleOrigin)->where('movementFlow_movementDestinity', '=', $flujo->movementFlow_movementOrigin)->where('movementFlow_branch', '=', $flujo->movementFlow_branch)->first();

                $flujo3->movementFlow_cancelled = 1;
                $validar9 = $flujo3->update();
                if ($validar9) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }

                $movimientoCancelar->accountsPayable_status = $this->estatus[3];
                $cancelado = $movimientoCancelar->update();

                //buscamos la solicitud en cxpp
                $cxpp = PROC_ACCOUNTS_PAYABLE_P::where('accountsPayableP_movement', '=', $cxp->accountsPayable_movement)->where('accountsPayableP_movementID', $cxp->accountsPayable_movementID)->where('accountsPayableP_branchOffice', '=', $cxp->accountsPayable_branchOffice)->first();
                $cxpp->delete();
                if ($cancelado) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }
            } catch (\Exception $e) {
                dd($e);
                $pagoCancelado = false;
                $message = $e->getMessage();
            }
        }

        if ($movimientoCancelar->accountsPayable_status == $this->estatus[2] && $movimientoCancelar->accountsPayable_movement == 'Aplicación') {

            try {
                $pagoCancelado = true;
                $aplicacionCancelado = false;
                $anticipoCancelado = true;

                //regresamos el monto a las cuentas por pagar
                $detalle = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_accountPayableID', '=', $movimientoCancelar->accountsPayable_id)->get();

                foreach ($detalle as $key => $value) {
                    $cuentaPagar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $value->accountsPayableDetails_movReference)->first();


                    $cuentaPagar->accountsPayable_balance = $cuentaPagar->accountsPayable_balance + $value->accountsPayableDetails_amount;
                    $cuentaPagar->accountsPayable_status = $this->estatus[1];
                    // dd($detalle, $cuentaPagar);
                    $validar = $cuentaPagar->update();
                    if ($validar) {
                        $aplicacionCancelado = true;
                    } else {
                        $aplicacionCancelado = false;
                    }

                    //buscamos el movimiento anterior al de cxp
                    $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $cuentaPagar->accountsPayable_id)->where('movementFlow_movementDestinity', '=', $cuentaPagar->accountsPayable_movement)->where('movementFlow_branch', '=', $cuentaPagar->accountsPayable_branchOffice)->first();

                    //regresamos las entradas a cxpp originario desde compras
                    if ($flujo->movementFlow_moduleOrigin == 'Compras') {
                        $entrada = PROC_PURCHASE::where('purchase_id', '=', $flujo->movementFlow_originID)->first();

                        //verificamos si aun existe la entrada en cuenta por pagar pendiente
                        $cuentaPagarPendiente = PROC_ACCOUNTS_PAYABLE_P::where('accountsPayableP_originType', '=', 'Compras')->where('accountsPayableP_origin', '=', $entrada->purchase_movement)->where('accountsPayableP_originID', '=', $entrada->purchase_movementID)->where('accountsPayableP_branchOffice', '=', $entrada->purchase_branchOffice)->first();


                        if ($cuentaPagarPendiente === null) {

                            $cuentaPagarP = new PROC_ACCOUNTS_PAYABLE_P();
                            $cuentaPagarP->accountsPayableP_movement = $entrada->purchase_movement;
                            $cuentaPagarP->accountsPayableP_movementID = $entrada->purchase_movementID;
                            $cuentaPagarP->accountsPayableP_issuedate = Carbon::parse($entrada->purchase_issueDate)->format('Y-m-d');
                            $cuentaPagarP->accountsPayableP_expiration =  Carbon::parse($entrada->purchase_expiration)->format('Y-m-d');

                            //dias credito y moratorio
                            $emision = Carbon::parse($entrada->purchase_issueDate)->format('Y-m-d');
                            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
                            $vencimiento = Carbon::parse($entrada->purchase_expiration)->format('Y-m-d');
                            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

                            $diasCredito = $currentDate->diffInDays($shippingDate);
                            $diasMoratorio = $shippingDate->diffInDays($currentDate);


                            $cuentaPagarP->accountsPayableP_creditDays = $diasCredito;
                            $cuentaPagarP->accountsPayableP_moratoriumDays = '-' . $diasMoratorio;


                            $cuentaPagarP->accountsPayableP_money = $entrada->purchase_money;
                            $cuentaPagarP->accountsPayableP_typeChange = $entrada->purchase_typeChange;
                            $cuentaPagarP->accountsPayableP_provider = $entrada->purchase_provider;
                            $cuentaPagarP->accountsPayableP_condition = $entrada->purchase_condition;

                            $cuentaPagarP->accountsPayableP_amount = $entrada->purchase_amount;
                            $cuentaPagarP->accountsPayableP_taxes = $entrada->purchase_taxes;
                            $cuentaPagarP->accountsPayableP_total = $entrada->purchase_total;
                            $cuentaPagarP->accountsPayableP_balanceTotal = $value->accountsPayableDetails_amount;
                            $cuentaPagarP->accountsPayableP_concept = $entrada->purchase_concept;
                            $cuentaPagarP->accountsPayableP_reference = $entrada->purchase_reference;
                            $cuentaPagarP->accountsPayableP_balance = $value->accountsPayableDetails_amount;
                            $cuentaPagarP->accountsPayableP_company = $entrada->purchase_company;
                            $cuentaPagarP->accountsPayableP_branchOffice = $entrada->purchase_branchOffice;
                            $cuentaPagarP->accountsPayableP_user = $entrada->purchase_user;
                            $cuentaPagarP->accountsPayableP_status = $this->estatus[1];
                            $cuentaPagarP->accountsPayableP_origin = $entrada->purchase_movement;
                            $cuentaPagarP->accountsPayableP_originID = $entrada->purchase_movementID;
                            $cuentaPagarP->accountsPayableP_originType =  'Compras';

                            $validar2 = $cuentaPagarP->save();
                            if ($validar2) {
                                $aplicacionCancelado = true;
                            } else {
                                $aplicacionCancelado = false;
                            }
                        } else {
                            $cuentaPagarPendiente->accountsPayableP_balance =   $cuentaPagarPendiente->accountsPayableP_balance + $value->accountsPayableDetails_amount;
                            $cuentaPagarPendiente->accountsPayableP_balanceTotal =   $cuentaPagarPendiente->accountsPayableP_balanceTotal + $value->accountsPayableDetails_amount;
                            $cuentaPagarPendiente->update();
                        }
                    } else if ($flujo->movementFlow_moduleOrigin == 'Factura de Gasto') {
                        $gasto = PROC_EXPENSES::where('expenses_id', '=', $flujo->movementFlow_originID)->first();

                        $cuentaPagarPendiente = PROC_ACCOUNTS_PAYABLE_P::where('accountsPayableP_originType', '=', 'Factura de Gasto')->where('accountsPayableP_origin', '=', $gasto->expenses_movement)->where('accountsPayableP_originID', '=', $gasto->expenses_movementID)->where('accountsPayableP_branchOffice', '=', $gasto->expenses_branchOffice)->first();

                        if ($cuentaPagarPendiente === null) {
                            $cuentaPagarP = new PROC_ACCOUNTS_PAYABLE_P();
                            $cuentaPagarP->accountsPayableP_movement = $gasto->expenses_movement;
                            $cuentaPagarP->accountsPayableP_movementID = $gasto->expenses_movementID;
                            $cuentaPagarP->accountsPayableP_issuedate = Carbon::parse($gasto->expenses_issueDate)->format('Y-m-d H:i:s');
                            $cuentaPagarP->accountsPayableP_expiration =  Carbon::parse($gasto->expenses_expiration)->format('Y-m-d H:i:s');

                            //dias credito y moratorio
                            $emision = Carbon::parse($gasto->expenses_issueDate)->format('Y-m-d');
                            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
                            $vencimiento = Carbon::parse($gasto->expenses_expiration)->format('Y-m-d');
                            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

                            $diasCredito = $currentDate->diffInDays($shippingDate);
                            $diasMoratorio = $shippingDate->diffInDays($currentDate);
                            $cuentaPagarP->accountsPayableP_creditDays = $diasCredito;
                            $cuentaPagarP->accountsPayableP_moratoriumDays = '-' . $diasMoratorio;
                            $cuentaPagarP->accountsPayableP_money = $gasto->expenses_money;
                            $cuentaPagarP->accountsPayableP_typeChange = $gasto->expenses_typeChange;
                            $cuentaPagarP->accountsPayableP_moneyAccount = $gasto->expenses_moneyAccount;

                            $cuentaPagarP->accountsPayableP_provider = $gasto->expenses_provider;
                            $cuentaPagarP->accountsPayableP_provider = $gasto->expenses_provider;
                            $cuentaPagarP->accountsPayableP_condition = $gasto->expenses_condition;

                            $cuentaPagarP->accountsPayableP_formPayment = $gasto->expenses_paymentMethod;
                            $cuentaPagarP->accountsPayableP_amount = $gasto->expenses_amount;
                            $cuentaPagarP->accountsPayableP_taxes = $gasto->expenses_taxes;
                            $cuentaPagarP->accountsPayableP_total = $gasto->expenses_total;
                            $cuentaPagarP->accountsPayableP_balanceTotal = $value->accountsPayableDetails_amount;
                            $cuentaPagarP->accountsPayableP_reference = 'Factura de Gasto';
                            $cuentaPagarP->accountsPayableP_balance = $value->accountsPayableDetails_amount;
                            $cuentaPagarP->accountsPayableP_company = $gasto->expenses_company;
                            $cuentaPagarP->accountsPayableP_branchOffice = $gasto->expenses_branchOffice;
                            $cuentaPagarP->accountsPayableP_user = $gasto->expenses_user;
                            $cuentaPagarP->accountsPayableP_status = $this->estatus[1];
                            $cuentaPagarP->accountsPayableP_origin = $gasto->expenses_movement;
                            $cuentaPagarP->accountsPayableP_originID = $gasto->expenses_movementID;
                            $cuentaPagarP->accountsPayableP_originType = 'Factura de Gasto';

                            $validar2 = $cuentaPagarP->save();
                            if ($validar2) {
                                $aplicacionCancelado = true;
                            } else {
                                $aplicacionCancelado = false;
                            }
                        } else {
                            $cuentaPagarPendiente->accountsPayableP_balance =   $cuentaPagarPendiente->accountsPayableP_balance + $value->accountsPayableDetails_amount;
                            $cuentaPagarPendiente->accountsPayableP_balanceTotal =   $cuentaPagarPendiente->accountsPayableP_balanceTotal + $value->accountsPayableDetails_amount;
                            $cuentaPagarPendiente->update();
                        }
                    }

                    //agregamos auxiliar de cancelacion de la aplicacion

                    $auxiliar = new PROC_ASSISTANT();
                    $auxiliar->assistant_companieKey = $movimientoCancelar->accountsPayable_company;
                    $auxiliar->assistant_branchKey = $movimientoCancelar->accountsPayable_branchOffice;
                    $auxiliar->assistant_branch = 'CxP';
                    $auxiliar->assistant_movement = $movimientoCancelar->accountsPayable_movement;
                    $auxiliar->assistant_movementID = $movimientoCancelar->accountsPayable_movementID;
                    $auxiliar->assistant_module = 'CxP';
                    $auxiliar->assistant_moduleID = $movimientoCancelar->accountsPayable_id;
                    $auxiliar->assistant_money = $movimientoCancelar->accountsPayable_money;
                    $auxiliar->assistant_typeChange = $movimientoCancelar->accountsPayable_typeChange;
                    $auxiliar->assistant_account = $movimientoCancelar->accountsPayable_provider;

                    $year = Carbon::now()->year;
                    //sacamos el periodo 
                    $period = Carbon::now()->month;


                    $auxiliar->assistant_year = $year;
                    $auxiliar->assistant_period = $period;
                    $auxiliar->assistant_charge = null;
                    $auxiliar->assistant_payment = '-' . $movimientoCancelar->accountsPayable_total;
                    $auxiliar->assistant_apply = $cuentaPagar->accountsPayable_movement;
                    $auxiliar->assistant_applyID =  $cuentaPagar->accountsPayable_movementID;
                    $auxiliar->assistant_canceled = 1;
                    $auxiliar->assistant_reference = $movimientoCancelar->accountsPayable_reference;
                    $validar3 = $auxiliar->save();
                    if ($validar3) {
                        $aplicacionCancelado = true;
                    } else {
                        $aplicacionCancelado = false;
                    }
                }

                if ($movimientoCancelar->accountsPayable_origin !== null) {
                    $anticipo = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', $movimientoCancelar->accountsPayable_origin)->where('accountsPayable_movementID', '=', $movimientoCancelar->accountsPayable_originID)->where('accountsPayable_branchOffice', '=', $movimientoCancelar->accountsPayable_branchOffice)->first();
                } else {
                    $referencias = explode(" ", $movimientoCancelar->accountsPayable_reference);
                    $anticipo = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', $referencias[0])->where('accountsPayable_movementID', '=', $referencias[1])->where('accountsPayable_branchOffice', '=', $movimientoCancelar->accountsPayable_branchOffice)->first();
                    // dd($referencias, $anticipo);
                }

                $auxiliar = new PROC_ASSISTANT();
                $auxiliar->assistant_companieKey = $movimientoCancelar->accountsPayable_company;
                $auxiliar->assistant_branchKey = $movimientoCancelar->accountsPayable_branchOffice;
                $auxiliar->assistant_branch = 'CxP';
                $auxiliar->assistant_movement = $movimientoCancelar->accountsPayable_movement;
                $auxiliar->assistant_movementID = $movimientoCancelar->accountsPayable_movementID;
                $auxiliar->assistant_module = 'CxP';
                $auxiliar->assistant_moduleID = $movimientoCancelar->accountsPayable_id;
                $auxiliar->assistant_money = $movimientoCancelar->accountsPayable_money;
                $auxiliar->assistant_typeChange = $movimientoCancelar->accountsPayable_typeChange;
                $auxiliar->assistant_account = $movimientoCancelar->accountsPayable_provider;

                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = '-' . $movimientoCancelar->accountsPayable_total;
                $auxiliar->assistant_payment = null;
                $auxiliar->assistant_apply = $anticipo->accountsPayable_movement;
                $auxiliar->assistant_applyID =  $anticipo->accountsPayable_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar2->assistant_reference = $movimientoCancelar->accountsPayable_reference;
                $validar4 = $auxiliar->save();

                if ($validar4) {
                    $aplicacionCancelado = true;
                } else {
                    $aplicacionCancelado = false;
                }
                //cancelamos los movimientos
                $movimiento = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $movimientoCancelar->accountsPayable_id)->where('movementFlow_movementDestinity', '=', $movimientoCancelar->accountsPayable_movement)->where('movementFlow_branch', '=', $movimientoCancelar->accountsPayable_branchOffice)->first();
                $movimiento->movementFlow_cancelled = 1;
                $validar5 = $movimiento->update();
                if ($validar5) {
                    $aplicacionCancelado = true;
                } else {
                    $aplicacionCancelado = false;
                }

                $movimiento2 = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $anticipo->accountsPayable_id)->where('movementFlow_movementOrigin', '=',  $anticipo->accountsPayable_movement)->where('movementFlow_movementDestinity', '=', $movimientoCancelar->accountsPayable_movement)->where('movementFlow_branch', '=', $movimientoCancelar->accountsPayable_branchOffice)->first();
                $movimiento2->movementFlow_cancelled = 1;
                $validar6 = $movimiento2->update();
                if ($validar6) {
                    $aplicacionCancelado = true;
                } else {
                    $aplicacionCancelado = false;
                }


                //regresamos el saldo al anticipo
                $anticipo->accountsPayable_balance = $anticipo->accountsPayable_balance + $movimientoCancelar->accountsPayable_total;
                $anticipo->accountsPayable_status = $this->estatus[1];
                $validar7 = $anticipo->update();
                if ($validar7) {
                    $aplicacionCancelado = true;
                } else {
                    $aplicacionCancelado = false;
                }

                //cancelamos el folio
                $movimientoCancelar->accountsPayable_status = $this->estatus[3];
                $cancelado = $movimientoCancelar->update();
                if ($cancelado) {
                    $aplicacionCancelado = true;
                } else {
                    $aplicacionCancelado = false;
                }
            } catch (\Exception $e) {
                dd($e);
                $aplicacionCancelado = false;
                $message = $e->getMessage();
            }
        }

        if ($movimientoCancelar->accountsPayable_status == $this->estatus[1] && $movimientoCancelar->accountsPayable_movement == 'Anticipo' && $request->tipo == 'Caja') {

            try {
                $pagoCancelado = true;
                $aplicacionCancelado = true;
                $anticipoCancelado = false;

                $val = (float) $movimientoCancelar->accountsPayable_balance;
                $val2 = (float) $movimientoCancelar->accountsPayable_total;
                if ($val != $val2) {
                    $status = 400;
                    $message = 'No se puede cancelar el anticipo, ya que tiene movimientos asociados.' . ' Movimiento:' . $movimientoCancelar->accountsPayable_movement . ' Folio: ' . $movimientoCancelar->accountsPayable_movementID;
                    return response()->json(['mensaje' => $message, 'estatus' => $status]);
                }
                //buscamos la cuenta para regresar el dinero
                $cuenta = PROC_BALANCE::where('balance_account', '=', $movimientoCancelar->accountsPayable_moneyAccount)->where('balance_branchKey', '=', $movimientoCancelar->accountsPayable_branchOffice)->where('balance_money', '=', $movimientoCancelar->accountsPayable_money)->first();


                if ($cuenta) {
                    $cuenta->balance_balance = $cuenta->balance_balance + $movimientoCancelar->accountsPayable_total;
                    $cuenta->balance_reconcile = $cuenta->balance_balance;
                    $validar = $cuenta->update();
                    if ($validar) {
                        $anticipoCancelado = true;
                        $this->actualizarSaldosCuentasDinero($movimientoCancelar->accountsPayable_id, $movimientoCancelar->accountsPayable_money);
                    } else {
                        $anticipoCancelado = false;
                    }
                }

                $cuenta2 = PROC_BALANCE::where('balance_account', '=', $movimientoCancelar->accountsPayable_provider)->where('balance_branchKey', '=', $movimientoCancelar->accountsPayable_branchOffice)->where('balance_branch', '=', 'CxP')->where('balance_money', '=', $movimientoCancelar->accountsPayable_money)->first();
                if ($cuenta2) {
                    $cuenta2->balance_balance = $cuenta2->balance_balance + $movimientoCancelar->accountsPayable_total;
                    $cuenta2->balance_reconcile = $cuenta2->balance_balance;
                    $validar2 = $cuenta2->update();
                    if ($validar2) {
                        $anticipoCancelado = true;
                    } else {
                        $anticipoCancelado = false;
                    }
                }

                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $movimientoCancelar->accountsPayable_company;
                $auxiliar->assistant_branchKey = $movimientoCancelar->accountsPayable_branchOffice;
                $auxiliar->assistant_branch = 'CxP';

                $auxiliar->assistant_movement = $movimientoCancelar->accountsPayable_movement;
                $auxiliar->assistant_movementID = $movimientoCancelar->accountsPayable_movementID;
                $auxiliar->assistant_module = 'CxP';
                $auxiliar->assistant_moduleID = $movimientoCancelar->accountsPayable_id;
                $auxiliar->assistant_money = $movimientoCancelar->accountsPayable_money;
                $auxiliar->assistant_typeChange = $movimientoCancelar->accountsPayable_typeChange;
                $auxiliar->assistant_account = $movimientoCancelar->accountsPayable_provider;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = null;
                $auxiliar->assistant_payment = '-' . $movimientoCancelar->accountsPayable_total;
                $auxiliar->assistant_apply = $movimientoCancelar->accountsPayable_movement;
                $auxiliar->assistant_applyID = $movimientoCancelar->accountsPayable_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $movimientoCancelar->accountsPayable_reference;

                $validar3 = $auxiliar->save();
                if ($validar3) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }


                $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $movimientoCancelar->accountsPayable_id)->where('movementFlow_movementOrigin', '=', $movimientoCancelar->accountsPayable_movement)->where('movementFlow_moduleDestiny', '=', 'Din')->where('movementFlow_movementDestinity', '=', 'Egreso')->where('movementFlow_branch', '=', $movimientoCancelar->accountsPayable_branchOffice)->first();
                if ($flujo) {
                    $flujo->movementFlow_cancelled = 1;
                    $validar4 = $flujo->update();
                    if ($validar4) {
                        $anticipoCancelado = true;
                    } else {
                        $anticipoCancelado = false;
                    }
                }

                $tesoreria = PROC_TREASURY::where('treasuries_id', '=', $flujo->movementFlow_destinityID)->first();

                $auxiliar2 = new PROC_ASSISTANT();

                $auxiliar2->assistant_companieKey = $movimientoCancelar->accountsPayable_company;
                $auxiliar2->assistant_branchKey = $movimientoCancelar->accountsPayable_branchOffice;
                $auxiliar2->assistant_branch = 'Din';

                $auxiliar2->assistant_movement = $tesoreria->treasuries_movement;
                $auxiliar2->assistant_movementID = $tesoreria->treasuries_movementID;
                $auxiliar2->assistant_module = 'Din';
                $auxiliar2->assistant_moduleID = $tesoreria->treasuries_id;
                $auxiliar2->assistant_money = $movimientoCancelar->accountsPayable_money;
                $auxiliar2->assistant_typeChange = $movimientoCancelar->accountsPayable_typeChange;
                $auxiliar2->assistant_account = $movimientoCancelar->accountsPayable_moneyAccount;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar2->assistant_year = $year;
                $auxiliar2->assistant_period = $period;
                $auxiliar2->assistant_charge = null;
                $auxiliar2->assistant_payment = '-' . $movimientoCancelar->accountsPayable_total;
                $auxiliar2->assistant_apply = $tesoreria->treasuries_movement;
                $auxiliar2->assistant_applyID = $tesoreria->treasuries_movementID;
                $auxiliar2->assistant_canceled = 1;
                $auxiliar2->assistant_reference = $movimientoCancelar->accountsPayable_reference;

                $validar5 = $auxiliar2->save();
                if ($validar5) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }

                $tesoreria->treasuries_status = $this->estatus[3];
                $validar6 = $tesoreria->update();
                if ($validar6) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }

                $movimientoCancelar->accountsPayable_status = $this->estatus[3];
                $cancelado = $movimientoCancelar->update();
                if ($cancelado) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }
            } catch (\Exception $e) {
                dd($e);
                $anticipoCancelado = false;
                $message = $e->getMessage();
            }
        }

        if ($movimientoCancelar->accountsPayable_status == $this->estatus[1] && $movimientoCancelar->accountsPayable_movement == 'Anticipo' && $request->tipo == 'Banco') {

            try {
                $pagoCancelado = true;
                $aplicacionCancelado = true;
                $anticipoCancelado = false;

                $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $movimientoCancelar->accountsPayable_id)->where('movementFlow_movementOrigin', '=', $movimientoCancelar->accountsPayable_movement)->where('movementFlow_moduleDestiny', '=', 'Din')->where('movementFlow_movementDestinity', '=', 'Sol. de Cheque/Transferencia')->where('movementFlow_branch', '=', $movimientoCancelar->accountsPayable_branchOffice)->first();

                //buscamos la solicitud de cheque en tesoreria
                $solicitudCheque = PROC_TREASURY::where('treasuries_id', '=', $flujo->movementFlow_destinityID)->first();


                if ($solicitudCheque->treasuries_status === $this->estatus[2]) {
                    $status = 400;
                    $message = 'No se puede cancelar el anticipo, ya que tiene una solicitud de cheque asociada.' . ' Movimiento: ' . $movimientoCancelar->accountsPayable_movement . ' Folio: ' . $movimientoCancelar->accountsPayable_movementID;
                    return response()->json(['mensaje' => $message, 'estatus' => $status]);
                }

                $val = (float) $movimientoCancelar->accountsPayable_balance;
                $val2 = (float) $movimientoCancelar->accountsPayable_total;
                if ($val != $val2) {
                    $status = 400;
                    $message = 'No se puede cancelar el anticipo, ya que tiene movimientos asociados.' . ' Movimiento:' . $movimientoCancelar->accountsPayable_movement . ' Folio: ' . $movimientoCancelar->accountsPayable_movementID;
                    return response()->json(['mensaje' => $message, 'estatus' => $status]);
                }



                $solicitudCheque->treasuries_status = $this->estatus[3];
                $validar = $solicitudCheque->update();
                if ($validar) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }

                $flujo2 = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $flujo->movementFlow_destinityID)->where('movementFlow_movementOrigin', '=', $flujo->movementFlow_movementDestinity)->where('movementFlow_moduleDestiny', '=', 'CxP')->where('movementFlow_movementDestinity', '=', $flujo->movementFlow_movementDestinity)->where('movementFlow_branch', '=', $movimientoCancelar->accountsPayable_branchOffice)->first();

                //Cancelamos la solicitud de cheque en cxp
                $solicitudCheque2 = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $flujo2->movementFlow_destinityID)->first();
                $solicitudCheque2->accountsPayable_status = $this->estatus[3];
                $validar2 = $solicitudCheque2->update();
                if ($validar2) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }

                //buscamos los mismo datos de la solicitud de cheque en cxp pendiente

                $cxppCancelar = PROC_ACCOUNTS_PAYABLE_P::where('accountsPayableP_movement', '=', $movimientoCancelar->accountsPayable_movement)->where('accountsPayableP_movementID', '=', $movimientoCancelar->accountsPayable_movementID)->first();

                if ($cxppCancelar !== null) {
                    $validar3 = $cxppCancelar->delete();
                    if ($validar3) {
                        $anticipoCancelado = true;
                    } else {
                        $anticipoCancelado = false;
                    }
                } else {
                    // Manejar el caso en el que no se encuentra ningún registro
                    $anticipoCancelado = false;
                }



                $cxppCancelar2 = PROC_ACCOUNTS_PAYABLE_P::where('accountsPayableP_movement', '=', $solicitudCheque2->accountsPayable_movement)->where('accountsPayableP_movementID', '=', $solicitudCheque2->accountsPayable_movementID)->first();

                if ($cxppCancelar2 !== null) {
                    $validar4 = $cxppCancelar2->delete();
                    if ($validar4) {
                        $anticipoCancelado = true;
                    } else {
                        $anticipoCancelado = false;
                    }
                } else {
                    // Manejar el caso en el que no se encuentra ningún registro
                    $anticipoCancelado = false;
                }


                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $movimientoCancelar->accountsPayable_company;
                $auxiliar->assistant_branchKey = $movimientoCancelar->accountsPayable_branchOffice;
                $auxiliar->assistant_branch = 'CxP';

                $auxiliar->assistant_movement = $movimientoCancelar->accountsPayable_movement;
                $auxiliar->assistant_movementID = $movimientoCancelar->accountsPayable_movementID;
                $auxiliar->assistant_module = 'CxP';
                $auxiliar->assistant_moduleID = $movimientoCancelar->accountsPayable_id;
                $auxiliar->assistant_money = $movimientoCancelar->accountsPayable_money;
                $auxiliar->assistant_typeChange = $movimientoCancelar->accountsPayable_typeChange;
                $auxiliar->assistant_account = $movimientoCancelar->accountsPayable_provider;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = null;
                $auxiliar->assistant_payment = '-' . $movimientoCancelar->accountsPayable_total;
                $auxiliar->assistant_apply = $movimientoCancelar->accountsPayable_movement;
                $auxiliar->assistant_applyID =  $movimientoCancelar->accountsPayable_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $movimientoCancelar->accountsPayable_reference;


                $validar5 = $auxiliar->save();
                if ($validar5) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }

                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $solicitudCheque2->accountsPayable_company;
                $auxiliar->assistant_branchKey = $solicitudCheque2->accountsPayable_branchOffice;
                $auxiliar->assistant_branch = 'CxP';


                $auxiliar->assistant_movement = $solicitudCheque2->accountsPayable_movement;
                $auxiliar->assistant_movementID = $solicitudCheque2->accountsPayable_movementID;
                $auxiliar->assistant_module = 'CxP';
                $auxiliar->assistant_moduleID = $solicitudCheque2->accountsPayable_id;
                $auxiliar->assistant_money = $solicitudCheque2->accountsPayable_money;
                $auxiliar->assistant_typeChange = $solicitudCheque2->accountsPayable_typeChange;
                $auxiliar->assistant_account = $solicitudCheque2->accountsPayable_provider;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = '-' . $solicitudCheque2->accountsPayable_total;
                $auxiliar->assistant_payment = null;
                $auxiliar->assistant_apply = $solicitudCheque2->accountsPayable_movement;
                $auxiliar->assistant_applyID =  $solicitudCheque2->accountsPayable_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $solicitudCheque2->accountsPayable_reference;

                $validar6 = $auxiliar->save();
                if ($validar6) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }

                $flujo->movementFlow_cancelled = 1;
                $validar7 = $flujo->update();
                if ($validar7) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }


                $flujo2->movementFlow_cancelled = 1;
                $validar8 = $flujo2->update();
                if ($validar8) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }

                $movimientoCancelar->accountsPayable_status = $this->estatus[3];
                $cancelado = $movimientoCancelar->update();
                if ($cancelado) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }
            } catch (\Exception $e) {
                dd($e);
                $anticipoCancelado = false;
                $message = $e->getMessage();
            }
        }

        if ($pagoCancelado === true && $aplicacionCancelado === true && $anticipoCancelado === true) {
            $status = 200;
            $message = 'Proceso cancelado correctamente';
        } else {
            $status = 500;
            $message = 'Error al cancelar el movimiento';
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status]);
    }

    public function actualizarSaldosCuentasDinero($folio, $moneda)
    {
        $movimientoCancelar = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $folio)->first();

        $cuenta = PROC_BALANCE::where('balance_account', '=', $movimientoCancelar->accountsPayable_moneyAccount)->where('balance_branchKey', '=', $movimientoCancelar->accountsPayable_branchOffice)->where('balance_money', '=', $movimientoCancelar->accountsPayable_money)->first();

        $validarCuenta = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount',  $movimientoCancelar->accountsPayable_moneyAccount)->where('moneyAccountsBalance_status', 'Alta')->where("moneyAccountsBalance_company", '=', $movimientoCancelar->accountsPayable_company)->where("moneyAccountsBalance_money", '=', $moneda)->first();
        $validarCuenta->moneyAccountsBalance_balance = $cuenta->balance_balance;
        $validarCuenta->update();
    }
    public function eliminarMovimiento(Request $request)
    {

        $cxp = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $request->id)->first();
        // // dd($compra);

        // //buscamos sus articulos
        $articulos = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_accountPayableID', '=', $request->id)->where('accountsPayableDetails_branchOffice', '=', $cxp->accountsPayable_branchOffice)->get();

        if ($articulos->count() > 0) {
            //eliminamos sus articulos
            foreach ($articulos as $articulo) {
                $articulosDelete = $articulo->delete();
            }
        } else {
            $articulosDelete = true;
        }


        // // dd($articulos);
        if ($cxp->accountsPayable_status === $this->estatus[0] && $articulosDelete === true) {
            $isDelete = $cxp->delete();
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

    function selectUsuarios()
    {
        $usuarios = User::where('user_status', '=', 'Alta')->get();
        $usuarios_array = array();
        $usuarios_array['Todos'] = 'Todos';
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



    public function getReporteCuentas($id)
    {
        $cuentasxpagar = PROC_ACCOUNTS_PAYABLE::join('CAT_PROVIDERS', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_provider', '=', 'CAT_PROVIDERS.providers_key')
            ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CONF_MONEY', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_money', '=', 'CONF_MONEY.money_key')
            ->join('CONF_CREDIT_CONDITIONS', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id', 'left outer')
            ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('CONF_FORMS_OF_PAYMENT', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_formPayment', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key', 'left outer')
            ->join('PROC_BALANCE', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_provider', '=', 'PROC_BALANCE.balance_account')
            ->where('accountsPayable_id', '=', $id)
            ->where('PROC_ACCOUNTS_PAYABLE.accountsPayable_company', '=', session('company')->companies_key)
            ->where('PROC_ACCOUNTS_PAYABLE.accountsPayable_branchOffice', '=', session('sucursal')->branchOffices_key)
            // ->where('PROC_ACCOUNTS_PAYABLE.accountsPayable_status', '=', $this->estatus[1])
            ->first();

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
        $cuentas_pagar = PROC_ACCOUNTS_PAYABLE::join('PROC_ACCOUNTS_PAYABLE_DETAILS', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_id', '=', 'PROC_ACCOUNTS_PAYABLE_DETAILS.accountsPayableDetails_accountPayableID', 'left outer')
            ->where('accountsPayable_id', '=', $id)
            ->where('PROC_ACCOUNTS_PAYABLE.accountsPayable_company', '=', session('company')->companies_key)
            ->where('PROC_ACCOUNTS_PAYABLE.accountsPayable_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->get();


        $pdf = PDF::loadView('reportes.cuentasxpagar-reporte', ['cuenta' => $id, 'cuentasxpagar' => $cuentasxpagar, 'logo' => $logoBase64, 'cuentas_pagar' => $cuentas_pagar]);
        $pdf->set_paper('a4', 'landscape');
        return $pdf->stream();
    }

    public function CXPAction(Request $request)
    {
        $nameFolio = $request->nameFolio;
        $nameKey = $request->nameKey;
        $nameMov = $request->nameMov;
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

        // dd($nameFolio, $nameKey, $nameMov, $status, $nameFecha, $nameUsuario, $nameSucursal, $nameMoneda, $fechaInicio, $fechaFinal);


        switch ($request->input('action')) {
            case 'Búsqueda':
                $CXP_collection_filtro = PROC_ACCOUNTS_PAYABLE::join('CAT_PROVIDERS', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_provider', '=', 'CAT_PROVIDERS.providers_key', 'left outer')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key', 'left outer')
                    ->join('CONF_CREDIT_CONDITIONS', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id', 'left outer')
                    ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_company', '=', 'CAT_COMPANIES.companies_key', 'left outer')
                    ->join('CONF_FORMS_OF_PAYMENT', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_formPayment', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key', 'left outer')
                    ->whereAccountsPayableMovementID($nameFolio)
                    ->whereAccountsPayableProvider($nameKey)
                    ->whereAccountsPayableMovement($nameMov)
                    ->whereAccountsPayableStatus($status)
                    ->whereAccountsPayableDate($nameFecha)
                    ->whereAccountsPayableUser($nameUsuario)
                    ->whereAccountsPayablebranchOffice($nameSucursal)
                    ->whereAccountsPayableMoney($nameMoneda)
                    // ->whereBalanceMoney($nameMoneda)
                    // ->whereBalancebranchKey($nameSucursal)
                    // ->where('PROC_ACCOUNTS_PAYABLE.accountsPayable_company', '=', session('company')->companies_key)
                    // ->where('PROC_BALANCE.balance_companieKey', '=', session('company')->companies_key)
                    ->orderBy('PROC_ACCOUNTS_PAYABLE.updated_at', 'DESC')
                    ->get()->unique();

                $CXP_filtro_array = $CXP_collection_filtro->toArray();
                // dd($CXP_filtro_array);

                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.modulo.cuentasPagar.index')
                    ->with('CXP_filtro_array', $CXP_filtro_array)
                    ->with('nameFolio', $nameFolio)
                    ->with('nameKey', $nameKey)
                    ->with('nameMov', $nameMov)
                    ->with('status', $status)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameUsuario', $nameUsuario)
                    ->with('nameSucursal', $nameSucursal)
                    ->with('nameMoneda', $nameMoneda)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal);

                break;

            case 'Exportar excel':
                $cuentasxpagar = new PROC_CXPExport($nameFolio, $nameKey, $nameMov, $status, $nameFecha, $nameUsuario, $nameSucursal, $nameMoneda);
                return Excel::download($cuentasxpagar, 'Cuentas por Pagar.xlsx');

                break;

            default:
                break;
        }
    }

    public function getSaldoByProveedor(Request $request)
    {
        $proveedorClave = $request->proveedor;

        $saldo = PROC_BALANCE::WHERE('balance_companieKey', '=', session('company')->companies_key)->WHERE('balance_branchKey', '=', session('sucursal')->branchOffices_key)->WHERE('balance_account', '=', $proveedorClave)->where('balance_branch', '=', 'CxP')
            ->where('balance_money', '=', $request->moneda)
            ->select('balance_balance')->first();
        return response()->json(['status' => 200, 'saldo' => $saldo]);
    }

    public function getInfoCuenta(Request $request)
    {
        $cuenta = $request->cuentaKey;
        $cuentaDB = CAT_MONEY_ACCOUNTS::WHERE('moneyAccounts_key', '=', $cuenta)->WHERE('moneyAccounts_status', '=', 'Alta')->select('moneyAccounts_accountType', 'moneyAccounts_money')->first();

        if ($cuentaDB) {
            return response()->json(['status' => true, 'data' => $cuentaDB]);
        } else {
            return response()->json(['status' => false, 'data' => $cuentaDB]);
        }
    }

    public function aplicaFolio(Request $request)
    {
        $sucursal = $request->sucursal;
        if ($sucursal == "Todos") {
            $aplicaProveedor = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_provider', '=', $request->proveedor)->where('accountsPayable_movement', '=', $request->movimiento)->where('accountsPayable_company', '=',  session('company')->companies_key)->where('accountsPayable_status', '=',  $this->estatus[1])->where('accountsPayable_money', '=', $request->moneda)->get();
        } else {
            $aplicaProveedor = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_provider', '=', $request->proveedor)->where('accountsPayable_movement', '=', $request->movimiento)->where('accountsPayable_company', '=',  session('company')->companies_key)->where('accountsPayable_branchOffice', '=',  $sucursal)->where('accountsPayable_status', '=',  $this->estatus[1])->where('accountsPayable_money', '=', $request->moneda)->get();
        }
        // DD($request->moneda, $request->proveedor);


        if ($aplicaProveedor !== null) {
            $message = 'Folios aplica del proveedor';
            $estatus = 200;
        } else {
            $message = 'No se pudo encontrar folios aplicados';
            $estatus = 500;
        }

        return response()->json(['message' => $message, 'estatus' => $estatus, 'dataProveedor' => $aplicaProveedor]);
    }

    public function getAnticipos(Request $request)
    {
        $anticipos = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', 'Anticipo')->where('accountsPayable_status', '=', $this->estatus[1])->where('accountsPayable_provider', '=', $request->proveedor)->where('accountsPayable_money', '=', $request->moneda)->get();
        // dd($anticipos);
        if ($anticipos !== null) {
            $message = 'Anticipos encontrados';
            $estatus = 200;
        } else {
            $message = 'No se encontraron anticipos';
            $estatus = 500;
        }

        return response()->json(['message' => $message, 'estatus' => $estatus, 'anticipos' => $anticipos]);
    }

    public function getConceptosByMovimiento(Request $request)
    {
        $movimientoSeleccionado = $request->input('movimiento');
        // dd($movimientoSeleccionado);
        if ($movimientoSeleccionado === null) {
            $conceptos = CONF_MODULES_CONCEPT::where('moduleConcept_status', '=', 'Alta')
                ->where('moduleConcept_module', '=', 'Cuentas por Pagar')
                ->get();
        } else {
            $conceptos = CONF_MODULES_CONCEPT::join('CONF_MODULES_CONCEPT_MOVEMENT', 'CONF_MODULES_CONCEPT_MOVEMENT.moduleMovement_conceptID', '=', 'CONF_MODULES_CONCEPT.moduleConcept_id')
                ->where('moduleConcept_status', '=', 'Alta')
                ->where('moduleConcept_module', '=', 'Cuentas por Pagar')
                ->where('moduleMovement_movementName', '=', $movimientoSeleccionado)
                ->get();
            // dd($conceptos, $movimientoSeleccionado);
        }
        return response()->json($conceptos);
    }

    public function actualizarFolio($tipoMovimiento, $folioAfectar)
    {
        switch ($tipoMovimiento) {
            case 'Anticipo':
                $consecutivoColumn = 'generalConsecutives_consAdvance';
                break;
            case 'Aplicación':
                $consecutivoColumn = 'generalConsecutives_consApplication';
                break;
            case 'Pago de Facturas':
                $consecutivoColumn = 'generalConsecutives_consPayment';
                break;
        }

        // Resto de la lógica para manejar los casos diferentes
        // ...

        if (isset($consecutivoColumn)) {
            // Obtén el valor actual de la columna de consecutivo
            $consecutivo = DB::table('CONF_GENERAL_PARAMETERS_CONSECUTIVES')
                ->where('generalConsecutives_company', session('company')->companies_key)
                ->where('generalConsecutives_branchOffice', session('sucursal')->branchOffices_key)
                ->value($consecutivoColumn);

            if ($consecutivo === null || $consecutivo === 0 || $consecutivo === '0') {
                // Si el valor es nulo o cero, realiza la lógica anterior para obtener el consecutivo
                $folioOrden = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', $tipoMovimiento)
                    ->where('accountsPayable_branchOffice', '=', $folioAfectar->accountsPayable_branchOffice)
                    ->max('accountsPayable_movementID');
                $folioOrden = $folioOrden == null ? 1 : $folioOrden + 1;
                $folioAfectar->accountsPayable_movementID = $folioOrden;
                $folioAfectar->update();

                $config_parameters = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();

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
            } else {
                // Utiliza el valor incrementado del consecutivo en tu lógica
                DB::table('CONF_GENERAL_PARAMETERS_CONSECUTIVES')
                    ->where('generalConsecutives_company', session('company')->companies_key)
                    ->where('generalConsecutives_branchOffice', session('sucursal')->branchOffices_key)
                    ->update([$consecutivoColumn => $consecutivo + 1]);

                $folioAfectar->accountsPayable_movementID = $consecutivo + 1;
                $folioAfectar->update();
            }

            // Eliminar detalles de la cuenta por pagar
            if (in_array($tipoMovimiento, ['Aplicación', 'Pago de Facturas'])) {
                $detalles = PROC_ACCOUNTS_PAYABLE_DETAILS::where('accountsPayableDetails_accountPayableID', '=', $folioAfectar->accountsPayable_id)->get();

                if (count($detalles) > 0) {
                    foreach ($detalles as $detalle) {
                        // Eliminamos el detalle de la cuenta por pagar original
                        $detalle->delete();
                    }
                }
            }
        }
    }
}