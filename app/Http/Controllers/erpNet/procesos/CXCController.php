<?php

namespace App\Http\Controllers\erpNet\procesos;

use App\Exports\PROC_CXCExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\erpNet\Timbrado\TimbradoController;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_COMPANIES;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CAT_MONEY_ACCOUNTS;
use App\Models\catalogos\CONF_FORMS_OF_PAYMENT;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS_CONSECUTIVES;
use App\Models\catalogos\CONF_MODULES_CONCEPT;
use App\Models\catalogos\CONF_MONEY;
use App\Models\catalogosSAT\CAT_SAT_MOTIVOS_CANCELACION;
use App\Models\historicos\HIST_STAMPED;
use App\Models\modulos\helpers\PROC_MONEY_ACCOUNTS_BALANCE;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE_DETAILS;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE_P;
use App\Models\modulos\PROC_ASSISTANT;
use App\Models\modulos\PROC_BALANCE;
use App\Models\modulos\PROC_MOVEMENT_FLOW;
use App\Models\modulos\PROC_SALES;
use App\Models\modulos\PROC_TREASURY;
use App\Models\modulos\PROC_TREASURY_DETAILS;
use App\Models\timbrado\PROC_CANCELED_REASON;
use App\Models\timbrado\PROC_CFDI;
use App\Models\timbrado\PROC_CFDI_CXC_REFERENCE;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class CXCController extends Controller
{

    public $estatus = [
        0 => 'INICIAL',
        1 => 'POR AUTORIZAR',
        2 => 'FINALIZADO',
        3 => 'CANCELADO',
    ];




    public $movimientos2 = [
        'Factura' => 'Factura',
        'Solicitud Depósito' => 'Solicitud Depósito',
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

        $cuentasxcobrar = PROC_ACCOUNTS_RECEIVABLE::join('CAT_CUSTOMERS', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_customer', '=', 'CAT_CUSTOMERS.customers_key', 'left outer')
            ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key', 'left outer')
            ->join('CONF_CREDIT_CONDITIONS', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id', 'left outer')
            ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_company', '=', 'CAT_COMPANIES.companies_key', 'left outer')
            ->join('CONF_FORMS_OF_PAYMENT', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_formPayment', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key', 'left outer')
            ->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_company', '=', session('company')->companies_key)
            ->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_user', '=', Auth::user()->username)
            ->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_status', "POR AUTORIZAR")
            ->when($parametro->generalParameters_defaultMoney, function ($query, $parametro) {
                return $query->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_money', '=', $parametro);
            }, function ($query) {
                return $query;
            })
            ->orderBy('PROC_ACCOUNTS_RECEIVABLE.updated_at', 'DESC')
            ->get()
            ->unique();

        // DD($cuentasxcobrar);
        return view('page.modulos.Gestion_y_Finanzas.Cuentas_por_Cobrar.index-cxc', compact('fecha_actual', 'select_users', 'select_sucursales', 'selectMonedas', 'parametro', 'cuentasxcobrar'));
    }

    public function create(Request $request)
    {

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro->generalParameters_defaultMoney == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de seleccionar la moneda por defecto');
        }
        try {
            //Obtenemos los permisos que tiene el usuario para el modulo de compras
            $usuario = Auth::user();
            $permisos = $usuario->getAllPermissions()->where('categoria', '=', 'Cuentas por cobrar')->pluck('name')->toArray();

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
            // dd($movimientos);

            $selectMonedas = $this->getMonedas2();
            $select_conceptos = CONF_MODULES_CONCEPT::WHERE('moduleConcept_status', '=', 'Alta')->Where('moduleConcept_module', '=', 'Cuentas por Cobrar')->get();

            $fecha_actual = Carbon::now()->format('Y-m-d');
            $parametro = CONF_GENERAL_PARAMETERS::join('CONF_MONEY', 'CONF_GENERAL_PARAMETERS.generalParameters_defaultMoney', '=', 'CONF_MONEY.money_key')
                ->select('CONF_GENERAL_PARAMETERS.*', 'CONF_MONEY.money_change')
                ->where('CONF_GENERAL_PARAMETERS.generalParameters_company', '=', session('company')->companies_key)
                ->first();
            $moneyAccounts = CAT_MONEY_ACCOUNTS::join('CAT_FINANCIAL_INSTITUTIONS', 'CAT_FINANCIAL_INSTITUTIONS.instFinancial_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_bank')
                ->where('CAT_MONEY_ACCOUNTS.moneyAccounts_status', '=', 'Alta')
                ->where('CAT_MONEY_ACCOUNTS.moneyAccounts_company', '=', session('company')->companies_key)
                ->get();
            $clientes = CAT_CUSTOMERS::WHERE('customers_status', '=', 'Alta')->get();

            $aplica = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_status', '=', 'POR AUTORIZAR')->get();
            $select_formaPago = CONF_FORMS_OF_PAYMENT::WHERE('formsPayment_status', '=', 'Alta')->get();
            $select_MotivoCancelacion = CAT_SAT_MOTIVOS_CANCELACION::all();

            $cxcTimbradas = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_stamped', '=', '1')->join('PROC_CFDI', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_id', '=', 'PROC_CFDI.cfdi_moduleID')
                ->WHERE('PROC_CFDI.cfdi_module', '=', 'CxC')
                ->WHERE('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_status', '=', 'FINALIZADO')
                ->WHERE('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_company', '=', session('company')->companies_key)
                ->WHERE('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_branchOffice', '=', session('sucursal')->branchOffices_key)
                ->WHEREIN('accountsReceivable_movement', ['Anticipo Clientes', 'Aplicación', 'Cobro de Facturas'])

                ->get();

            // dd($cxcTimbradas, $facturas, $aplicacionesTimbradas, $pagosTimbradas);        


            //Verificamos si recibimos un id en la url
            if (isset($request->id) && $request->id != 0) {
                $cxc = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_id', '=', $request->id)->first();

                // dd($cxc);

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
                    $cxc->accountsReceivable_movement === 'Anticipo Clientes' || $cxc->accountsReceivable_movement === 'Aplicación' ||
                    $cxc->accountsReceivable_movement === 'Cobro de Facturas' || $cxc->accountsReceivable_movement === 'Devolución de Anticipo'
                ) {
                    //Validamos si el usuario tiene permiso de ver los movimientos ya creados
                    if (!array_key_exists($cxc->accountsReceivable_movement, $movimientos)) {
                        return redirect()->route('vista.modulo.cuentasCobrar.index')->with('status', false)->with('message', 'No tiene permisos para visualizar este movimiento');
                    }
                }
                //  dd($cxc);
                $tipoCuenta = CAT_MONEY_ACCOUNTS::WHERE('moneyAccounts_key', '=', $cxc->accountsReceivable_moneyAccount)->select('moneyAccounts_accountType', 'moneyAccounts_money')->first();

                $cxcDetails = PROC_ACCOUNTS_RECEIVABLE_DETAILS::WHERE('accountsReceivableDetails_accountReceivableID', '=', $request->id)->get();

                //Obtenemos las entradas o gastos que hacen referencia a esta cuenta por pagar
                $ventasMov = [];

                foreach ($cxcDetails as $cxcD) {
                    $cxcMovmiento = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_id', '=', $cxcD->accountsReceivableDetails_movReference)->first();
                    $ventasMov[$cxcMovmiento->accountsReceivable_id] =  $cxcMovmiento->accountsReceivable_balance;
                }

                $primerFlujodeCXC = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
                    ->WHERE('movementFlow_originID', '=', $cxc->accountsReceivable_id)
                    ->WHERE('movementFlow_movementOriginID', '=', $cxc->accountsReceivable_movementID)
                    ->WHERE('movementFlow_moduleOrigin', '=', 'CxC')
                    ->get();

                if (count($primerFlujodeCXC) === 0) {
                    $primerFlujodeCXC = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
                        ->WHERE('movementFlow_destinityID', '=', $cxc->accountsReceivable_id)
                        ->WHERE('movementFlow_movementDestinityID', '=', $cxc->accountsReceivable_movementID)
                        ->WHERE('movementFlow_moduleDestiny', '=', 'CxC')
                        ->get();
                }

                $infoProveedor = CAT_CUSTOMERS::join('CONF_CREDIT_CONDITIONS', 'CAT_CUSTOMERS.customers_creditCondition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id', 'left outer')->where('customers_key', '=', $cxc->accountsReceivable_customer)->first();

                //  dd($infoProveedor);

                $monedasMov = CONF_MONEY::where('money_status', '=', 'Alta')->orderBy('money_key', 'desc')->get();

                $movimientosProveedor = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_customer', '=', $cxc->accountsReceivable_customer)->whereIn('accountsReceivable_movement', ['Factura', 'Anticipo Clientes'])->where("accountsReceivable_balance", ">", '0')->where("accountsReceivable_company", "=", session('company')->companies_key)->where("accountsReceivable_status", "=", 'POR AUTORIZAR')->get();

                //  DD($movimientosProveedor);

                $saldoGeneral = 0;
                foreach ($movimientosProveedor as $movimiento) {
                    if ($movimiento->accountsReceivable_money == session('generalParameters')->generalParameters_defaultMoney) {
                        if ($movimiento->accountsReceivable_movement !== 'Anticipo Clientes') {
                            $saldoGeneral += $movimiento->accountsReceivable_balance;
                        } else {
                            $saldoGeneral -= $movimiento->accountsReceivable_balance;
                        }
                    } else {
                        $monedaActual = CONF_MONEY::where('money_key', '=', $movimiento->accountsReceivable_money)->first();
                        if ($movimiento->accountsReceivable_movement !== 'Anticipo Clientes') {
                            $saldoGeneral += ($movimiento->accountsReceivable_balance * $monedaActual->money_change);
                        } else {
                            $saldoGeneral -= ($movimiento->accountsReceivable_balance * $monedaActual->money_change);
                        }
                    }
                }


                switch ($cxc->accountsReceivable_movement) {
                    case 'Anticipo Clientes':

                        return view('page.modulos.Gestion_y_Finanzas.Cuentas_por_Cobrar.create-cxc', compact('selectMonedas', 'fecha_actual', 'clientes', 'parametro', 'moneyAccounts', 'clientes', 'select_formaPago', 'select_conceptos', 'aplica', 'cxc', 'tipoCuenta', 'movimientos', 'primerFlujodeCXC', 'infoProveedor', 'saldoGeneral', 'movimientosProveedor', 'monedasMov', 'select_MotivoCancelacion', 'cxcTimbradas'));
                        break;

                    case 'Cobro de Facturas':

                        return view('page.modulos.Gestion_y_Finanzas.Cuentas_por_Cobrar.create-cxc', compact('selectMonedas', 'fecha_actual', 'clientes', 'parametro', 'moneyAccounts', 'clientes', 'select_formaPago', 'select_conceptos', 'aplica', 'cxc', 'tipoCuenta', 'movimientos', 'cxcDetails', 'primerFlujodeCXC', 'infoProveedor', 'saldoGeneral', 'movimientosProveedor', 'monedasMov', 'ventasMov', 'select_MotivoCancelacion', 'cxcTimbradas'));
                        break;

                    case 'Aplicación':

                        return view('page.modulos.Gestion_y_Finanzas.Cuentas_por_Cobrar.create-cxc', compact('selectMonedas', 'fecha_actual', 'clientes', 'parametro', 'moneyAccounts', 'clientes', 'select_formaPago', 'select_conceptos', 'aplica', 'cxc', 'tipoCuenta', 'movimientos', 'cxcDetails', 'primerFlujodeCXC', 'infoProveedor', 'saldoGeneral', 'movimientosProveedor', 'monedasMov', 'ventasMov', 'select_MotivoCancelacion', 'cxcTimbradas'));
                        break;

                    case 'Devolución de Anticipo':

                        return view('page.modulos.Gestion_y_Finanzas.Cuentas_por_Cobrar.create-cxc', compact('selectMonedas', 'fecha_actual', 'clientes', 'parametro', 'moneyAccounts', 'clientes', 'select_formaPago', 'select_conceptos', 'aplica', 'cxc', 'tipoCuenta', 'movimientos', 'cxcDetails', 'primerFlujodeCXC', 'infoProveedor', 'saldoGeneral', 'movimientosProveedor', 'monedasMov', 'ventasMov', 'select_MotivoCancelacion', 'cxcTimbradas'));

                        // case 'Factura de Gasto':
                        //     $movimientos = $this->movimientos2;
                        //     return view('page.modulos.Gestion_y_Finanzas.Cuentas_por_Cobrar.create-cxc', compact('selectMonedas', 'fecha_actual', 'clientes', 'parametro', 'moneyAccounts', 'clientes', 'select_formaCobro', 'select_conceptos', 'aplica', 'cxc', 'tipoCuenta', 'movimientos', 'primerFlujodeCXP'));
                        //     break;

                    case 'Factura':
                        $movimientos = array_merge($movimientos, $this->movimientos2);
                        $select_conceptos = CONF_MODULES_CONCEPT::WHERE('moduleConcept_status', '=', 'Alta')->get();

                        return view('page.modulos.Gestion_y_Finanzas.Cuentas_por_Cobrar.create-cxc', compact('selectMonedas', 'fecha_actual', 'clientes', 'parametro', 'moneyAccounts', 'clientes', 'select_formaPago', 'select_conceptos', 'aplica', 'cxc', 'tipoCuenta', 'movimientos', 'primerFlujodeCXC', 'infoProveedor', 'saldoGeneral', 'movimientosProveedor', 'monedasMov', 'select_MotivoCancelacion', 'cxcTimbradas'));
                        break;


                    case 'Solicitud Depósito':
                        $movimientos = array_merge($movimientos, $this->movimientos2);

                        return view('page.modulos.Gestion_y_Finanzas.Cuentas_por_Cobrar.create-cxc', compact('selectMonedas', 'fecha_actual', 'clientes', 'parametro', 'moneyAccounts', 'clientes', 'select_formaPago', 'select_conceptos', 'aplica', 'cxc', 'tipoCuenta', 'movimientos', 'primerFlujodeCXC', 'infoProveedor', 'saldoGeneral', 'movimientosProveedor', 'monedasMov', 'select_MotivoCancelacion', 'cxcTimbradas'));
                        break;

                    case 'Sol. de Cheque/Transferencia':
                        $movimientos = array_merge($movimientos, $this->movimientos2);

                        return view('page.modulos.Gestion_y_Finanzas.Cuentas_por_Cobrar.create-cxc', compact('selectMonedas', 'fecha_actual', 'clientes', 'parametro', 'moneyAccounts', 'clientes', 'select_formaPago', 'select_conceptos', 'aplica', 'cxc', 'tipoCuenta', 'movimientos', 'primerFlujodeCXC', 'infoProveedor', 'saldoGeneral', 'movimientosProveedor', 'monedasMov', 'select_MotivoCancelacion', 'cxcTimbradas'));
                        break;

                    default:
                        //  dd($select_conceptos);
                        return view('page.modulos.Gestion_y_Finanzas.Cuentas_por_Cobrar.create-cxc', compact('selectMonedas', 'fecha_actual', 'clientes', 'parametro', 'moneyAccounts', 'clientes', 'select_formaPago', 'select_conceptos', 'aplica', 'cxc', 'tipoCuenta', 'movimientos', 'cxcDetails', 'primerFlujodeCXC', 'infoProveedor', 'saldoGeneral', 'movimientosProveedor', 'monedasMov', 'select_MotivoCancelacion', 'cxcTimbradas'));
                        break;
                }
            }


            return view('page.modulos.Gestion_y_Finanzas.Cuentas_por_Cobrar.create-cxc', compact('selectMonedas', 'fecha_actual', 'clientes', 'parametro', 'moneyAccounts', 'clientes', 'select_formaPago', 'select_conceptos', 'aplica', 'movimientos', 'select_MotivoCancelacion', 'cxcTimbradas'));
        } catch (\Exception $e) {

            //dd($e);
            return redirect()->route('vista.modulo.cuentasCobrar.index')->with('status', false)->with('message', 'El movimiento no se ha encontrado');
        }
    }

    public function store(Request $request)
    {

        $cxc_request = $request->except('_token');
        // dd(gettype($cxc_request['totalCompleto']));
        //  dd($cxc_request);
        $id = $request->id;
        $copiaRequest = $request->copiar;
        $tipoMovimiento = $request->movimientos;

        try {
            switch ($tipoMovimiento) {

                case 'Anticipo Clientes':
                    if ($request->origin === null) {
                        if ($id == 0 || $copiaRequest == 'copiar') {
                            $cxc = new PROC_ACCOUNTS_RECEIVABLE();
                        } else {
                            $cxc = PROC_ACCOUNTS_RECEIVABLE::find($id);
                        }
                        $cxc->accountsReceivable_movement = $tipoMovimiento;
                        // $cxc->accountsReceivable_issuedate
                        // = \Carbon\Carbon::now();
                        $cxc->accountsReceivable_issuedate =  $cxc_request['fechaEmision'];
                        $cxc->accountsReceivable_money =  $cxc_request['nameMoneda'];
                        $cxc->accountsReceivable_typeChange =  $cxc_request['nameTipoCambio'];
                        $cxc->accountsReceivable_moneyAccount =  $cxc_request['cuentaKey'];
                        $cxc->accountsReceivable_customer =  $cxc_request['proveedorKey'];
                        $cxc->accountsReceivable_formPayment =  $cxc_request['proveedorFormaPago'];
                        $cxc->accountsReceivable_observations =  $cxc_request['observaciones'];
                        $cxc->accountsReceivable_reference =  $cxc_request['referencia'];
                        $cxc->accountsReceivable_amount =  str_replace(['$', ','], '', $cxc_request['importe']); //dinero
                        $cxc->accountsReceivable_taxes =   str_replace(['$', ','], '', $cxc_request['impuesto']); //dinero
                        $cxc->accountsReceivable_total =  str_replace(['$', ','], '', $cxc_request['importeTotal']); //dinero
                        $cxc->accountsReceivable_concept =  isset($cxc_request['concepto']) ? $cxc_request['concepto'] : null;
                        $cxc->accountsReceivable_status = $this->estatus[0];
                        $cxc->accountsReceivable_company = session('company')->companies_key;
                        $cxc->accountsReceivable_branchOffice = session('sucursal')->branchOffices_key;
                        $cxc->accountsReceivable_user = Auth::user()->username;
                        $cxc->accountsReceivable_CFDI = $cxc_request['identificadorCFDI'];
                        $cxc->accountsReceivable_stamped = 0;

                        $cxc->created_at = Carbon::now()->format('Y-m-d H:i:s');
                        $cxc->updated_at = Carbon::now()->format('Y-m-d H:i:s');


                        $isCreate = $cxc->save();
                        $lastId = $id == 0 ? PROC_ACCOUNTS_RECEIVABLE::latest('accountsReceivable_id')->first()->accountsReceivable_id : $id;

                        if ($isCreate) {
                            $message = $id == 0 ? 'El Anticipo Clientes se ha creado correctamente' : 'El Anticipo Clientes se ha actualizado correctamente';
                            $status = true;
                        } else {
                            $message = $id == 0 ? 'Error al crear el anticipo ' : 'Error al actualizar el anticipo ';
                            $status = false;
                        }

                        return redirect()->route('vista.modulo.cuentasCobrar.create-cxc', $id == 0 ? $lastId : $id)->with('message', $message)->with('status', $status);
                    } else {

                        $cxc = new PROC_ACCOUNTS_RECEIVABLE();
                        $cxc->accountsReceivable_movement = $cxc_request['origin'];
                        $cxc->accountsReceivable_issuedate = $cxc_request['fechaEmision'];
                        $cxc->accountsReceivable_money =  $cxc_request['nameMoneda'];
                        $cxc->accountsReceivable_typeChange =  $cxc_request['nameTipoCambio'];
                        $cxc->accountsReceivable_moneyAccount =  $cxc_request['cuentaKey'];
                        $cxc->accountsReceivable_customer =  $cxc_request['proveedorKey'];
                        $cxc->accountsReceivable_formPayment =  $cxc_request['proveedorFormaPago'];
                        $cxc->accountsReceivable_observations =  $cxc_request['observaciones'];
                        $cxc->accountsReceivable_reference =  $tipoMovimiento . ' ' . $cxc_request['folio'];
                        $cxc->accountsReceivable_amount =  str_replace(['$', ','], '', $cxc_request['importe']); //dinero
                        $cxc->accountsReceivable_taxes =   str_replace(['$', ','], '', $cxc_request['impuesto']); //dinero
                        $cxc->accountsReceivable_total =  str_replace(['$', ','], '', $cxc_request['importeTotal']); //dinero
                        $cxc->accountsReceivable_concept =  isset($cxc_request['concepto']) ? $cxc_request['concepto'] : null;
                        $cxc->accountsReceivable_status = $this->estatus[0];
                        $cxc->accountsReceivable_company = session('company')->companies_key;
                        $cxc->accountsReceivable_branchOffice = session('sucursal')->branchOffices_key;
                        $cxc->accountsReceivable_user = Auth::user()->username;
                        $cxc->accountsReceivable_originType = 'CxC';
                        $cxc->accountsReceivable_origin = $tipoMovimiento;
                        $cxc->accountsReceivable_originID = $cxc_request['folio'];
                        $cxc->accountsReceivable_CFDI = $cxc_request['identificadorCFDI'];
                        $cxc->created_at = Carbon::now()->format('Y-m-d H:i:s');
                        $cxc->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                        $isCreate = $cxc->save();
                        $lastId = PROC_ACCOUNTS_RECEIVABLE::latest('accountsReceivable_id')->first()->accountsReceivable_id;

                        if ($isCreate) {
                            if ($cxc_request['origin'] === "Aplicación") {
                                $message = 'La Aplicación se ha creado correctamente';
                            } else {
                                $message = 'La Devolución de Anticipo se ha creado correctamente';
                            }
                            $status = true;
                        } else {
                            if ($cxc_request['origin'] === "Aplicación") {
                                $message = 'Error al crear la aplicación';
                            } else {
                                $message = 'Error al crear la devolución de anticipo';
                            }
                            $status = false;
                        }

                        return redirect()->route('vista.modulo.cuentasCobrar.create-cxc', $lastId)->with('message', $message)->with('status', $status);
                    }
                    break;


                    //-----------------------------------------------------
                case 'Cobro de Facturas':
                    if ($id == 0 || $copiaRequest == 'copiar') {
                        $cxc = new PROC_ACCOUNTS_RECEIVABLE();
                    } else {
                        $cxc = PROC_ACCOUNTS_RECEIVABLE::find($id);
                    }
                    $cxc->accountsReceivable_movement = $tipoMovimiento;
                    $cxc->accountsReceivable_issuedate =  $cxc_request['fechaEmision'];
                    $cxc->accountsReceivable_money =  $cxc_request['nameMoneda'];
                    $cxc->accountsReceivable_typeChange =  $cxc_request['nameTipoCambio'];
                    $cxc->accountsReceivable_moneyAccount =  $cxc_request['cuentaKey'];
                    $cxc->accountsReceivable_customer =  $cxc_request['proveedorKey'];
                    $cxc->accountsReceivable_formPayment =  $cxc_request['proveedorFormaPago'];
                    $cxc->accountsReceivable_observations =  $cxc_request['observaciones'];
                    $cxc->accountsReceivable_reference =  $cxc_request['referencia'];
                    $cxc->accountsReceivable_amount =  str_replace(['$', ','], '', $cxc_request['importe']); //dinero
                    $cxc->accountsReceivable_taxes =   str_replace(['$', ','], '', $cxc_request['impuesto']); //dinero
                    $cxc->accountsReceivable_total =  str_replace(['$', ','], '', $cxc_request['importeTotal']); //dinero
                    $cxc->accountsReceivable_concept =  isset($cxc_request['concepto']) ? $cxc_request['concepto'] : null;
                    $cxc->accountsReceivable_status = $this->estatus[0];
                    $cxc->accountsReceivable_company = session('company')->companies_key;
                    $cxc->accountsReceivable_branchOffice = session('sucursal')->branchOffices_key;
                    $cxc->accountsReceivable_user = Auth::user()->username;
                    $cxc->accountsReceivable_CFDI = $cxc_request['identificadorCFDI'];
                    $cxc->created_at = Carbon::now()->format('Y-m-d H:i:s');
                    $cxc->updated_at = Carbon::now()->format('Y-m-d H:i:s');

                    $detalleCxC =  $cxc_request['dataArticulosJson'];
                    $detalleCxc = json_decode($detalleCxC, true);
                    $claveDet = array_keys($detalleCxc);

                    $detallesDelete = json_decode($cxc_request['dataArticulosDelete'], true);


                    if ($detallesDelete  != null) {
                        foreach ($detallesDelete as $detalle) {
                            $detalleCxC = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_id', $detalle)->first();
                            $detalleCxC->delete();
                        }
                    }

                    $isCreate = $cxc->save();
                    $lastId = $id == 0 ? PROC_ACCOUNTS_RECEIVABLE::latest('accountsReceivable_id')->first()->accountsReceivable_id : $id;

                    if ($detalleCxC !== null) {
                        foreach ($claveDet as $detalle) {
                            if (isset($detalleCxc[$detalle]['id'])) {
                                $detalleInsert = PROC_ACCOUNTS_RECEIVABLE_DETAILS::find($detalleCxc[$detalle]['id']);
                            } else {
                                $detalleInsert = new PROC_ACCOUNTS_RECEIVABLE_DETAILS();
                            }
                            $detalleInsert->accountsReceivableDetails_apply = $detalleCxc[$detalle]['aplicaSelect'];
                            $detalleInsert->accountsReceivableDetails_accountReceivableID =  $lastId;
                            $detalleInsert->accountsReceivableDetails_applyIncrement = $detalleCxc[$detalle]['aplicaConsecutivo'];
                            $detalleInsert->accountsReceivableDetails_amount = str_replace(['$', ','], '', $detalleCxc[$detalle]['importe']);
                            $detalleInsert->accountsReceivableDetails_company = session('company')->companies_key;
                            $detalleInsert->accountsReceivableDetails_branchOffice = session('sucursal')->branchOffices_key;
                            $detalleInsert->accountsReceivableDetails_user = Auth::user()->username;
                            $detalleInsert->accountsReceivableDetails_movReference = $detalleCxc[$detalle]['movID'];

                            //Buscamos la factura a cobrar
                            $factura = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_id', '=', $detalleCxc[$detalle]['movID'])->first();

                            if ($factura != null) {
                                $porcentajeISR = floatval($factura->accountsReceivable_retention1) / 100;
                                $retencionesISR = floatval(str_replace(['$', ','], '', $detalleCxc[$detalle]['importe'])) * $porcentajeISR;

                                $porcentajeIVA = floatval($factura->accountsReceivable_retention2) / 100;
                                $retencionesIVA = floatval(str_replace(['$', ','], '', $detalleCxc[$detalle]['importe'])) * $porcentajeIVA;

                                $detalleInsert->accountsReceivableDetails_retention1 = $factura->accountsReceivable_retention1;
                                $detalleInsert->accountsReceivableDetails_retentionISR = $retencionesISR;
                                $detalleInsert->accountsReceivableDetails_retention2 = $factura->accountsReceivable_retention2;
                                $detalleInsert->accountsReceivableDetails_retentionIVA =  $retencionesIVA;

                                $cxcUpdate = PROC_ACCOUNTS_RECEIVABLE::find($lastId);
                                $cxcUpdate->accountsReceivable_retention1 = $factura->accountsReceivable_retention1;
                                $cxcUpdate->accountsReceivable_retentionISR = $retencionesISR;
                                $cxcUpdate->accountsReceivable_retention2 = $factura->accountsReceivable_retention2;
                                $cxcUpdate->accountsReceivable_retentionIVA =  $retencionesIVA;
                                $cxcUpdate->save();
                            }
                            $detalleInsert->save();
                        }
                    }

                    if ($isCreate) {
                        $message = $id == 0 ? 'El Cobro se ha creado correctamente' : 'El Cobro se ha actualizado correctamente';
                        $status = true;
                    } else {
                        $message = $id == 0 ? 'Error al crear el pago ' : 'Error al actualizar el pago ';
                        $status = false;
                    }

                    return redirect()->route('vista.modulo.cuentasCobrar.create-cxc', $id == 0 ? $lastId : $id)->with('message', $message)->with('status', $status);
                    break;
                    //-----------------------------------------------------
                case 'Aplicación':
                    if ($id == 0 || $copiaRequest == 'copiar') {
                        $cxc = new PROC_ACCOUNTS_RECEIVABLE();
                    } else {
                        $cxc = PROC_ACCOUNTS_RECEIVABLE::find($id);
                    }

                    $cxc_request['impuesto'] == null ? $cxc_request['impuesto'] = 0.00 : $cxc_request['impuesto'] = $cxc_request['impuesto'];


                    $cxc->accountsReceivable_movement = $tipoMovimiento;
                    $cxc->accountsReceivable_issuedate =  $cxc_request['fechaEmision'];
                    $cxc->accountsReceivable_money =  $cxc_request['nameMoneda'];
                    $cxc->accountsReceivable_typeChange =  $cxc_request['nameTipoCambio'];
                    $cxc->accountsReceivable_moneyAccount =  $cxc_request['cuentaKey'];
                    $cxc->accountsReceivable_customer =  $cxc_request['proveedorKey'];
                    $cxc->accountsReceivable_formPayment =  $cxc_request['proveedorFormaPago'];
                    $cxc->accountsReceivable_observations =  $cxc_request['observaciones'];
                    $cxc->accountsReceivable_reference =  $cxc_request['referencia'];
                    $cxc->accountsReceivable_amount =  str_replace(['$', ','], '', $cxc_request['importe']); //dinero
                    $cxc->accountsReceivable_taxes =   str_replace(['$', ','], '',  $cxc_request['impuesto']); //dinero
                    $cxc->accountsReceivable_total =  str_replace(['$', ','], '', $cxc_request['importeTotal']); //dinero
                    $cxc->accountsReceivable_concept =  isset($cxc_request['concepto']) ? $cxc_request['concepto'] : null;
                    $cxc->accountsReceivable_status = $this->estatus[0];
                    $cxc->accountsReceivable_company = session('company')->companies_key;
                    $cxc->accountsReceivable_branchOffice = session('sucursal')->branchOffices_key;
                    $cxc->accountsReceivable_user = Auth::user()->username;
                    $divisores = explode(" ", $cxc_request['anticiposKey']);
                    // dd($divisores);
                    $cxc->accountsReceivable_originType = 'CxC';
                    $cxc->accountsReceivable_origin = $divisores[0] . ' ' . $divisores[1];
                    $cxc->accountsReceivable_originID = $divisores[2];
                    $cxc->accountsReceivable_CFDI = $cxc_request['identificadorCFDI'];
                    $cxc->created_at = Carbon::now()->format('Y-m-d H:i:s');
                    $cxc->updated_at = Carbon::now()->format('Y-m-d H:i:s');

                    $detalleCxC =  $cxc_request['dataArticulosJson'];
                    $detalleCxc = json_decode($detalleCxC, true);
                    $claveDet = array_keys($detalleCxc);

                    $detallesDelete = json_decode($cxc_request['dataArticulosDelete'], true);


                    if ($detallesDelete  != null) {
                        foreach ($detallesDelete as $detalle) {
                            $detalleCxC = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_id', $detalle)->first();
                            $detalleCxC->delete();
                        }
                    }

                    $isCreate = $cxc->save();
                    $lastId = $id == 0 ? PROC_ACCOUNTS_RECEIVABLE::latest('accountsReceivable_id')->first()->accountsReceivable_id : $id;

                    if ($detalleCxC !== null) {
                        foreach ($claveDet as $detalle) {
                            if (isset($detalleCxc[$detalle]['id'])) {
                                $detalleInsert = PROC_ACCOUNTS_RECEIVABLE_DETAILS::find($detalleCxc[$detalle]['id']);
                            } else {
                                $detalleInsert = new PROC_ACCOUNTS_RECEIVABLE_DETAILS();
                            }
                            $detalleInsert->accountsReceivableDetails_apply = $detalleCxc[$detalle]['aplicaSelect'];
                            $detalleInsert->accountsReceivableDetails_accountReceivableID =  $lastId;
                            $detalleInsert->accountsReceivableDetails_applyIncrement = $detalleCxc[$detalle]['aplicaConsecutivo'];
                            $detalleInsert->accountsReceivableDetails_amount =  str_replace(['$', ','], '', $detalleCxc[$detalle]['importe']);
                            $detalleInsert->accountsReceivableDetails_company = session('company')->companies_key;
                            $detalleInsert->accountsReceivableDetails_branchOffice = session('sucursal')->branchOffices_key;
                            $detalleInsert->accountsReceivableDetails_user = Auth::user()->username;
                            $detalleInsert->accountsReceivableDetails_movReference = $detalleCxc[$detalle]['movID'];
                            $detalleInsert->save();
                        }
                    }


                    if ($isCreate) {
                        $message = $id == 0 ? 'La Aplicación se ha creado correctamente' : 'La Aplicación se ha actualizado correctamente';
                        $status = true;
                    } else {
                        $message = $id == 0 ? 'Error al crear la aplicación ' : 'Error al actualizar la aplicación ';
                        $status = false;
                    }

                    return redirect()->route('vista.modulo.cuentasCobrar.create-cxc', $id == 0 ? $lastId : $id)->with('message', $message)->with('status', $status);
                    break;
                    //-----------------------------------------------------
                case 'Factura':
                    //Buscamos la factura a cobrar
                    $factura = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_id', '=', $cxc_request['id'])->first();

                    $cxc = new PROC_ACCOUNTS_RECEIVABLE();
                    $cxc->accountsReceivable_movement = $cxc_request['origin'];
                    $cxc->accountsReceivable_issuedate = $cxc_request['fechaEmision'];
                    $cxc->accountsReceivable_money =  $cxc_request['nameMoneda'];
                    $cxc->accountsReceivable_typeChange =  $cxc_request['nameTipoCambio'];
                    $cxc->accountsReceivable_moneyAccount =  $cxc_request['cuentaKey'];
                    $cxc->accountsReceivable_customer =  $cxc_request['proveedorKey'];
                    $cxc->accountsReceivable_formPayment =  $cxc_request['proveedorFormaPago'];
                    $cxc->accountsReceivable_observations =  $cxc_request['observaciones'];
                    $cxc->accountsReceivable_reference =  $cxc_request['referencia'];
                    $cxc->accountsReceivable_amount =  str_replace(['$', ','], '', $cxc_request['saldo']); //dinero
                    $cxc->accountsReceivable_taxes =   str_replace(['$', ','], '', $cxc_request['impuesto']); //dinero
                    $cxc->accountsReceivable_total =  str_replace(['$', ','], '', $cxc_request['saldo']); //dinero

                    if ($factura != null) {
                        $cxc->accountsReceivable_retention1 = $factura->accountsReceivable_retention1;
                        $cxc->accountsReceivable_retentionISR = $factura->accountsReceivable_retentionISR;
                        $cxc->accountsReceivable_retention2 = $factura->accountsReceivable_retention2;
                        $cxc->accountsReceivable_retentionIVA = $factura->accountsReceivable_retentionIVA;
                    }

                    $cxc->accountsReceivable_concept =  'Pago';
                    $cxc->accountsReceivable_status = $this->estatus[0];
                    $cxc->accountsReceivable_company = session('company')->companies_key;
                    $cxc->accountsReceivable_branchOffice = session('sucursal')->branchOffices_key;
                    $cxc->accountsReceivable_user = Auth::user()->username;
                    $cxc->accountsReceivable_originType = 'CxC';
                    $cxc->accountsReceivable_origin = $tipoMovimiento;
                    $cxc->accountsReceivable_originID = $cxc_request['folio'];
                    $cxc->accountsReceivable_CFDI = $cxc_request['identificadorCFDI'];
                    $cxc->created_at = Carbon::now()->format('Y-m-d H:i:s');
                    $cxc->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                    $isCreate = $cxc->save();
                    $lastId = PROC_ACCOUNTS_RECEIVABLE::latest('accountsReceivable_id')->first()->accountsReceivable_id;



                    $cxcDetails = new PROC_ACCOUNTS_RECEIVABLE_DETAILS();
                    $cxcDetails->accountsReceivableDetails_accountReceivableID = $lastId;
                    $cxcDetails->accountsReceivableDetails_apply = $tipoMovimiento;
                    $cxcDetails->accountsReceivableDetails_applyIncrement = $cxc_request['folio'];
                    $cxcDetails->accountsReceivableDetails_amount = str_replace(['$', ','], '', $cxc_request['saldo']);
                    $cxcDetails->accountsReceivableDetails_company = session('company')->companies_key;
                    $cxcDetails->accountsReceivableDetails_branchOffice = session('sucursal')->branchOffices_key;
                    $cxcDetails->accountsReceivableDetails_user = Auth::user()->username;
                    $cxcDetails->accountsReceivableDetails_movReference = $cxc_request['id'];

                    if ($factura != null) {
                        $cxcDetails->accountsReceivableDetails_retention1 = $factura->accountsReceivable_retention1;
                        $cxcDetails->accountsReceivableDetails_retentionISR = $factura->accountsReceivable_retentionISR;
                        $cxcDetails->accountsReceivableDetails_retention2 = $factura->accountsReceivable_retention2;
                        $cxcDetails->accountsReceivableDetails_retentionIVA = $factura->accountsReceivable_retentionIVA;
                    }
                    $cxcDetails->save();


                    if ($isCreate) {
                        $message = 'El Cobro se ha creado correctamente';
                        $status = true;
                    } else {
                        $message = 'Error al crear el Cobro ';
                        $status = false;
                    }

                    return redirect()->route('vista.modulo.cuentasCobrar.create-cxc', $lastId)->with('message', $message)->with('status', $status);
                    break;


                default:
                    # code..
                    break;
            }
        } catch (\Throwable $th) {
            dd($th);
            $message = $id == 0 ? "Por favor, vaya con el administrador de sistemas, no se pudo crear la cuenta por pagar" : "Por favor, vaya con el administrador de sistemas, no se pudo actualizar la cuenta por pagar";
            return redirect()->route('vista.modulo.cuentasCobrar.create-cxc', $id)->with('message', $message)->with('status', false);
        }
    }

    public function afectar(Request $request)
    {
        $cxc_request = $request->except('_token');
        //  dd($cxc_request);
        $id = $request->id;
        $tipoMovimiento = $request['movimientos'];
        $cuenta = $cxc_request['cuentaKey'];
        $tipoCuenta = $request['tipoCuenta'];

        if ($tipoMovimiento == 'Cobro de Facturas') {
            //validar el concepto

            if ($cxc_request['concepto'] != 'Pago') {
                $message = 'Cuando el movimiento es Cobro de Facturas, debe introducir el concepto Pago';
                $status = 500;
                $lastId = $cxc_request['id'];
                return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
            }

            if ($cxc_request['concepto'] == 'Pago') {
                $concepto = CONF_MODULES_CONCEPT::where('moduleConcept_name', $cxc_request['concepto'])->where('moduleConcept_module', 'Cuentas por Cobrar')->first();

                if ($concepto->moduleConcept_prodServ != '84111506-Servicios de facturación') {
                    $message = 'Cuando el movimiento y el concepto son Pago, el servicio de facturación debe ser "84111506-Servicios de facturación"';
                    $status = 500;
                    $lastId = $cxc_request['id'];
                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                }
            }
        }


        if ($tipoMovimiento == 'Anticipo Clientes' || $tipoMovimiento == 'Cobro de Facturas' || $tipoMovimiento == 'Aplicación' || $tipoMovimiento == 'Devolución de Anticipo') {
            //verificar que la forma de pago sea de la misma que el movimiento
            $formaPago = CONF_FORMS_OF_PAYMENT::WHERE('formsPayment_key', '=', $cxc_request['proveedorFormaPago'])->first();

            $moneda = CONF_MONEY::WHERE('money_key', '=', $cxc_request['nameMoneda'])->first();
            // DD(trim($formaPago->formsPayment_money), $moneda->money_keySat);

            if (trim($formaPago->formsPayment_money) != $moneda->money_keySat) {
                $message = 'La forma de pago no es compatible con la moneda del movimiento';
                $status = 500;
                $lastId = $cxc_request['id'];

                return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
            }
        }

        if ($tipoMovimiento == 'Cobro de Facturas' || $tipoMovimiento == 'Aplicación') {
            //validar que el tipo cambio sea el mismo que el de la moneda 
            $tipoCambio = $cxc_request['nameTipoCambio'];
            $moneda = $cxc_request['nameMoneda'];
            $tipoCambioMoneda = CONF_MONEY::WHERE('money_key', '=', $moneda)->first();


            if ($tipoCambioMoneda->money_change != $tipoCambio) {
                $message = 'El tipo de cambio no es el mismo que el de la moneda';
                $status = 500;
                $lastId = $cxc_request['id'];

                return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
            }

            $detalleCxP =  $cxc_request['dataArticulosJson'];
            $detalleCxp = json_decode($detalleCxP, true);
            // dd($detalleCxp);
            $claveDet = array_keys($detalleCxp);

            if ($detalleCxP !== null) {
                foreach ($claveDet as $key => $value) {
                    //buscar los movimientos
                    $movimiento = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $detalleCxp[$value]['movID'])->first();
                    // dd($movimiento);
                    //verificar que los movimientos todos sean de la misma moneda con la que se esta pagando
                    if ($movimiento->accountsReceivable_money !== $cxc_request['nameMoneda']) {
                        $message = 'Los movimientos no son de la misma moneda';
                        $status = 500;
                        $lastId = $cxc_request['id'];

                        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                    }

                    if ($movimiento->accountsReceivable_customer !== $cxc_request['proveedorKey']) {
                        $message = 'Los movimientos no son del mismo cliente';
                        $status = 500;
                        $lastId = $cxc_request['id'];

                        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                    }
                }
            }
        }


        try {

            if ($cxc_request['movimientos'] === 'Aplicación') {
                $referencias = explode(" ", $cxc_request['anticiposKey']);
                //  dd($referencias);
                //buscamos el anticipo a aplicar 
                $anticipo = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', $referencias[0]  . ' ' . $referencias[1])->WHERE('accountsReceivable_movementID', '=', $referencias[2])->WHERE('accountsReceivable_branchOffice', '=', session('sucursal')->branchOffices_key)->first();
                // dd($anticipo);
                if ($anticipo->accountsReceivable_money != $cxc_request['nameMoneda']) {
                    $message = 'El anticipo no es de la misma moneda que la cuenta por cobrar';
                    $status = 500;
                    $lastId = $cxc_request['id'];

                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                }

                $valor1 = (float) $anticipo->accountsReceivable_balance;
                $valor2 = (float)str_replace(['$', ','], '', $cxc_request['totalCompleto']);
                if ($anticipo !== null) {
                    if ($valor1 < $valor2) {
                        $message = 'La cantidad que desea pagar es mayor al anticipo';
                        $status = 500;
                        $lastId = $cxc_request['id'];

                        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                    }
                }

                if ($anticipo->accountsReceivable_customer != $cxc_request['proveedorKey']) {
                    $message = 'El anticipo no es del mismo cliente que la cuenta por cobrar';
                    $status = 500;
                    $lastId = $cxc_request['id'];

                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                }
            }

            if ($cxc_request['movimientos'] === 'Cobro de Facturas') {
                $detalles = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', '=', $cxc_request['id'])->get();

                if (!$detalles) {
                    foreach ($detalles as $detalle) {
                        $movimiento = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $detalle->accountsReceivableDetails_movReference)->first();
                        $dataArticulosJson = json_decode($cxc_request['dataArticulosJson'], true);


                        $valor1 = (float) $movimiento->accountsReceivable_balance;
                        $valor2 = (float)str_replace(['$', ','], '', $cxc_request['totalCompleto']);
                        // DD($movimiento);
                        if ($movimiento->accountsReceivable_status == $this->estatus[2] || $valor1 < $valor2) {
                            $message = 'La cantidad que desea pagar es mayor al saldo del movimiento';
                            $status = 500;
                            $lastId = $cxc_request['id'] != 0 ? $cxc_request['id'] : '';

                            return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                        }
                    }
                } else {
                    $dataArticulosJson = (array) json_decode($cxc_request['dataArticulosJson'], true);

                    foreach ($dataArticulosJson as $key => $data) {
                        $movimiento = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $data['movID'])->first();

                        $valor1 = (float)str_replace(['$', ','], '', $data['importe']);
                        $valor2 = (float) $movimiento->accountsReceivable_balance;
                        // DD($movimiento);
                        if ($movimiento->accountsReceivable_status == $this->estatus[2] || $valor1 > $valor2) {
                            $message = 'La cantidad que desea pagar es mayor al saldo del movimiento';
                            $status = 500;
                            $lastId = $cxc_request['id'] != 0 ? $cxc_request['id'] : '';

                            return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                        }
                    }
                }
            }

            //ahora haremos para Devolución de Anticipo
            if ($cxc_request['movimientos'] === 'Devolución de Anticipo') {
                $referencias = explode(" ", $cxc_request['anticiposKey']);
                // dd($referencias);

                $anticipo = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', $referencias[0]  . ' ' . $referencias[1])->WHERE('accountsReceivable_movementID', '=', $referencias[2])->WHERE('accountsReceivable_branchOffice', '=', session('sucursal')->branchOffices_key)->first();
                // dd($anticipo);

                if ($anticipo->accountsReceivable_money != $cxc_request['nameMoneda']) {
                    $message = 'El anticipo no es de la misma moneda que la cuenta por cobrar';
                    $status = 500;
                    $lastId = $cxc_request['id'];

                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                }

                $valor1 = (float) $anticipo->accountsReceivable_balance;
                $valor2 = (float)str_replace(['$', ','], '', $cxc_request['importeTotal']);
                // dd($valor1, $valor2);
                if ($anticipo !== null) {
                    if ($valor1 < $valor2) {
                        $message = 'La cantidad que desea devolver es mayor al anticipo';
                        $status = 500;
                        $lastId = $cxc_request['id'];

                        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                    }
                }

                if ($anticipo->accountsReceivable_customer != $cxc_request['proveedorKey']) {
                    $message = 'El anticipo no es del mismo cliente que la cuenta por cobrar';
                    $status = 500;
                    $lastId = $cxc_request['id'];

                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                }
            }


            if ($id == 0) {
                $cxc = new PROC_ACCOUNTS_RECEIVABLE();
            } else {
                $cxc = PROC_ACCOUNTS_RECEIVABLE::find($id);
            }

            $cxc->accountsReceivable_movement = $tipoMovimiento;
            $cxc->accountsReceivable_issuedate =  $cxc_request['fechaEmision'];
            $cxc->accountsReceivable_money =  $cxc_request['nameMoneda'];
            $cxc->accountsReceivable_typeChange =  $cxc_request['nameTipoCambio'];
            $cxc->accountsReceivable_moneyAccount =  $cxc_request['cuentaKey'];
            $cxc->accountsReceivable_customer =  $cxc_request['proveedorKey'];
            $cxc->accountsReceivable_formPayment =  $cxc_request['proveedorFormaPago'];
            $cxc->accountsReceivable_observations =  $cxc_request['observaciones'];
            $cxc->accountsReceivable_reference =  $cxc_request['referencia'];
            $cxc->accountsReceivable_amount =  str_replace(['$', ','], '', $cxc_request['importe']); //dinero
            $cxc->accountsReceivable_taxes =   str_replace(['$', ','], '', $cxc_request['impuesto']); //dinero

            $cxc->accountsReceivable_concept =  $cxc_request['concepto'];
            $cxc->accountsReceivable_company = session('company')->companies_key;
            $cxc->accountsReceivable_branchOffice = session('sucursal')->branchOffices_key;
            $cxc->accountsReceivable_user = Auth::user()->username;
            $cxc->accountsReceivable_CFDI = $cxc_request['identificadorCFDI'];

            if ($cxc_request['movimientos'] !== 'Anticipo Clientes') {
                $cxc->accountsReceivable_balance =  str_replace(['$', ','], '', $cxc_request['totalCompleto']); //dinero
                $cxc->accountsReceivable_total =  str_replace(['$', ','], '', $cxc_request['totalCompleto']); //dinero
            } else {
                $cxc->accountsReceivable_balance =  str_replace(['$', ','], '', $cxc_request['importeTotal']); //dinero
                $cxc->accountsReceivable_total =  str_replace(['$', ','], '', $cxc_request['importeTotal']); //dinero
            }

            $cxc->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $cxc->updated_at = Carbon::now()->format('Y-m-d H:i:s');


            //agregamos el detalle de la cuenta por pagar
            $detalleCxC =  $cxc_request['dataArticulosJson'];
            $detalleCxc = json_decode($detalleCxC, true);
            $claveDet = array_keys($detalleCxc);

            switch ($tipoMovimiento) {
                case 'Anticipo Clientes':
                    $cxc->accountsReceivable_status = $this->estatus[1];
                    break;

                case 'Aplicación':
                    $cxc->accountsReceivable_status = $this->estatus[2];
                    $cxc->accountsReceivable_origin = explode(" ", $cxc_request['anticiposKey'])[0] . ' ' . explode(" ", $cxc_request['anticiposKey'])[1];
                    $cxc->accountsReceivable_originID = explode(" ", $cxc_request['anticiposKey'])[2];
                    $cxc->update();
                    break;

                case 'Cobro de Facturas':
                    $cxc->accountsReceivable_status = $this->estatus[2];
                    $cxc->accountsReceivable_total =  str_replace(',', '', $cxc_request['importe']);
                    $cxc->update();
                    break;

                case 'Devolución de Anticipo':
                    $cxc->accountsReceivable_status = $this->estatus[2];
                    $cxc->accountsReceivable_total =  str_replace(',', '', $cxc_request['importeTotal']);
                    $cxc->update();
                    break;

                default:
                    # code...
                    break;
            }


            if ($id == 0) {
                $isCreate = $cxc->save();
                $lastId = $id == 0 ? PROC_ACCOUNTS_RECEIVABLE::latest('accountsReceivable_id')->first()->accountsReceivable_id : $id;
            } else {
                $isCreate = $cxc->update();
                $lastId = $cxc->accountsReceivable_id;
            }

            //Generamos el folio del anticipo
            $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_id', '=', $lastId)->first();

            $this->actualizarFolio($tipoMovimiento, $folioAfectar);

            // switch ($tipoMovimiento) {
            //     case 'Anticipo Clientes':
            //         $folioMov = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', 'Anticipo Clientes')->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->max('accountsReceivable_movementID');
            //         $folioMov = $folioMov == null ? 1 : $folioMov + 1;
            //         $folioAfectar->accountsReceivable_movementID = $folioMov;
            //         $folioAfectar->update();
            //         break;

            //     case 'Aplicación':
            //         $folioMov = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', 'Aplicación')->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->max('accountsReceivable_movementID');
            //         $folioMov = $folioMov == null ? 1 : $folioMov + 1;
            //         $folioAfectar->accountsReceivable_movementID = $folioMov;
            //         $folioAfectar->update();

            //         $detalles = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', '=', $folioAfectar->accountsReceivable_id)->get();

            //         if (count($detalles) > 0) {
            //             foreach ($detalles as $detalle) {
            //                 //eliminamos el detalle de la cuenta por pagar original
            //                 $detalle->delete();
            //             }
            //         }

            //         break;

            //     case 'Cobro de Facturas':
            //         $folioMov = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', 'Cobro de Facturas')->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->max('accountsReceivable_movementID');
            //         $folioMov = $folioMov == null ? 1 : $folioMov + 1;
            //         $folioAfectar->accountsReceivable_movementID = $folioMov;
            //         $folioAfectar->update();

            //         //Eliminar detalles
            //         $detalles = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', '=', $folioAfectar->accountsReceivable_id)->get();

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

            if ($detalleCxC !== null) {
                foreach ($claveDet as $detalle) {
                    $detalleInsert = new PROC_ACCOUNTS_RECEIVABLE_DETAILS();
                    $detalleInsert->accountsReceivableDetails_apply = $detalleCxc[$detalle]['aplicaSelect'];
                    $detalleInsert->accountsReceivableDetails_accountReceivableID =  $lastId;
                    $detalleInsert->accountsReceivableDetails_applyIncrement = $detalleCxc[$detalle]['aplicaConsecutivo'];
                    $detalleInsert->accountsReceivableDetails_amount = str_replace(['$', ','], '', $detalleCxc[$detalle]['importe']);
                    $detalleInsert->accountsReceivableDetails_company = session('company')->companies_key;
                    $detalleInsert->accountsReceivableDetails_branchOffice = session('sucursal')->branchOffices_key;
                    $detalleInsert->accountsReceivableDetails_user = Auth::user()->username;
                    $detalleInsert->accountsReceivableDetails_movReference = $detalleCxc[$detalle]['movID'];
                    //Buscamos la factura a cobrar
                    $factura = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_id', '=', $detalleCxc[$detalle]['movID'])->first();

                    if ($factura != null) {
                        $porcentajeISR = floatval($factura->accountsReceivable_retention1) / 100;
                        $retencionesISR = floatval(str_replace(['$', ','], '', $detalleCxc[$detalle]['importe'])) * $porcentajeISR;

                        $porcentajeIVA = floatval($factura->accountsReceivable_retention2) / 100;
                        $retencionesIVA = floatval(str_replace(['$', ','], '', $detalleCxc[$detalle]['importe'])) * $porcentajeIVA;

                        $detalleInsert->accountsReceivableDetails_retention1 = $factura->accountsReceivable_retention1;
                        $detalleInsert->accountsReceivableDetails_retentionISR = $retencionesISR;
                        $detalleInsert->accountsReceivableDetails_retention2 = $factura->accountsReceivable_retention2;
                        $detalleInsert->accountsReceivableDetails_retentionIVA =  $retencionesIVA;
                        $cxcUpdate = PROC_ACCOUNTS_RECEIVABLE::find($lastId);
                        $cxcUpdate->accountsReceivable_retention1 = $factura->accountsReceivable_retention1;
                        $cxcUpdate->accountsReceivable_retentionISR = $retencionesISR;
                        $cxcUpdate->accountsReceivable_retention2 = $factura->accountsReceivable_retention2;
                        $cxcUpdate->accountsReceivable_retentionIVA =  $retencionesIVA;
                        $cxcUpdate->save();
                    }
                    $detalleInsert->save();
                }
            }

            if ($tipoMovimiento !== 'Aplicación' && $tipoMovimiento !== 'Devolución de Anticipo') {
                if ($tipoCuenta != 'Caja') {
                    //agregar CxC pendiente -- El anticipo
                    $this->agregarCxCP($folioAfectar->accountsReceivable_id);
                    // //agregar aux
                    $this->auxiliar($folioAfectar->accountsReceivable_id);

                    // //Generamos una solicitud de deposito de acuerdo al tipo de cuenta a CXC -Cheque del deposito
                    $cheque = $this->agregarCxCCheque($folioAfectar->accountsReceivable_id);
                    // //buscamos el cheque generado
                    $chequeGenerado = PROC_TREASURY::WHERE('treasuries_id', '=', $cheque)->first();
                    $this->agregarMov($folioAfectar->accountsReceivable_id);
                    // //agregar saldo
                    $this->agregarSaldo($folioAfectar->accountsReceivable_id); //se agrega el saldo a 0
                } else {

                    $ingreso = $this->agregarTesoreria($folioAfectar->accountsReceivable_id); //Agrega a tesoreria como concluido

                    $ingresoGenerado = PROC_TREASURY::WHERE('treasuries_id', '=', $ingreso)->first();

                    //observaciones: agregar cxppendiente

                    $this->agregarMovCaja($folioAfectar->accountsReceivable_id);

                    $this->actualizarSaldo($folioAfectar->accountsReceivable_id); //Actualiza el saldo de la cuenta de tipo caja

                }
            }




            //actualizamos saldos de cuentas por pagar y eliminamos en pendiente
            if ($folioAfectar->accountsReceivable_movement == 'Cobro de Facturas' && $folioAfectar->accountsReceivable_status == $this->estatus[2]) {
                // //buscamos la cuenta origen 
                $detalles = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', '=', $folioAfectar->accountsReceivable_id)->get();


                foreach ($detalles as $detalle) {
                    $cuentaOrigen = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_id', '=', $detalle->accountsReceivableDetails_movReference)->first();

                    if ($detalle->accountsReceivableDetails_amount >= $cuentaOrigen->accountsReceivable_balance) {
                        $cuentaOrigen->accountsReceivable_balance =  $cuentaOrigen->accountsReceivable_balance - $detalle->accountsReceivableDetails_amount;
                        $cuentaOrigen->update();

                        if ($cuentaOrigen->accountsReceivable_balance == 0 || $cuentaOrigen->accountsReceivable_balance == 0.00) {
                            $cuentaOrigen->accountsReceivable_status = $this->estatus[2];
                            $cuentaOrigen->update();
                        }

                        $cxcP = PROC_ACCOUNTS_RECEIVABLE_P::WHERE('accountsReceivableP_movement', '=', $cuentaOrigen->accountsReceivable_movement)->WHERE('accountsReceivableP_movementID', '=', $cuentaOrigen->accountsReceivable_movementID)->WHERE('accountsReceivableP_branchOffice', '=', $cuentaOrigen->accountsReceivable_branchOffice)->first();

                        $cxcP->accountsReceivableP_balance =   $cxcP->accountsReceivableP_balance - $detalle->accountsReceivableDetails_amount;
                        $cxcP->accountsReceivableP_balanceTotal =   $cxcP->accountsReceivableP_balanceTotal - $detalle->accountsReceivableDetails_amount;
                        $cxcP->update();

                        if ($cxcP->accountsReceivableP_balance == 0 || $cxcP->accountsReceivableP_balance == 0.00) {
                            $cxcP->delete();
                        }
                    } else {
                        $cuentaOrigen->accountsReceivable_balance =  $cuentaOrigen->accountsReceivable_balance - $detalle->accountsReceivableDetails_amount;
                        $cuentaOrigen->update();
                        $cxcP = PROC_ACCOUNTS_RECEIVABLE_P::WHERE('accountsReceivableP_movement', '=', $cuentaOrigen->accountsReceivable_movement)->WHERE('accountsReceivableP_movementID', '=', $cuentaOrigen->accountsReceivable_movementID)->WHERE('accountsReceivableP_branchOffice', '=', $cuentaOrigen->accountsReceivable_branchOffice)->first();
                        $cxcP->accountsReceivableP_balance =   $cxcP->accountsReceivableP_balance - $detalle->accountsReceivableDetails_amount;
                        $cxcP->accountsReceivableP_balanceTotal =   $cxcP->accountsReceivableP_balanceTotal - $detalle->accountsReceivableDetails_amount;
                        $cxcP->update();
                        if ($cxcP->accountsReceivableP_balance == 0) {
                            $cxcP->delete();
                        }
                    }
                }
            }


            if ($folioAfectar->accountsReceivable_movement == 'Aplicación' && $folioAfectar->accountsReceivable_status == $this->estatus[2]) {

                //buscamos la cuenta origen de la aplicacion
                if ($folioAfectar->accountsReceivable_origin !== null) {
                    $anticipo = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', $folioAfectar->accountsReceivable_origin)->where('accountsReceivable_movementID', '=', $folioAfectar->accountsReceivable_originID)->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->first();
                } else {
                    $referencias = explode(" ", $folioAfectar->accountsReceivable_reference);
                    $anticipo = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', $referencias[0]  . ' ' . $referencias[1])->where('accountsReceivable_movementID', '=', $referencias[2])->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->first();
                    // dd($referencias, $anticipo);
                }

                if ($folioAfectar->accountsReceivable_total <= $anticipo->accountsReceivable_total) {
                    $anticipo->accountsReceivable_balance = $anticipo->accountsReceivable_balance - $folioAfectar->accountsReceivable_total;
                    $anticipo->update();
                    if ($anticipo->accountsReceivable_balance == 0) {
                        $anticipo->accountsReceivable_status = $this->estatus[2];
                        $anticipo->update();

                        //buscamos el anticipo pendiente en cxp y lo eliminamos
                        $cxcP = PROC_ACCOUNTS_RECEIVABLE_P::WHERE('accountsReceivableP_movement', '=', $anticipo->accountsReceivable_movement)->WHERE('accountsReceivableP_movementID', '=', $anticipo->accountsReceivable_movementID)->WHERE('accountsReceivableP_branchOffice', '=', $anticipo->accountsReceivable_branchOffice)->first();
                        if ($cxcP !== null) {
                            $cxcP->delete();
                        }
                    }
                }


                $detalles = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', '=', $folioAfectar->accountsReceivable_id)->get();
                // dd($detalles);
                $pago = $folioAfectar->accountsReceivable_total;
                foreach ($detalles as $detalle) {
                    $cuentaOrigen = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_id', '=', $detalle->accountsReceivableDetails_movReference)->first();

                    if ($pago >= $cuentaOrigen->accountsReceivable_balance) {
                        $cuentaOrigen->accountsReceivable_balance =  $cuentaOrigen->accountsReceivable_balance - $detalle->accountsReceivableDetails_amount;
                        $cuentaOrigen->update();

                        if ($cuentaOrigen->accountsReceivable_balance == 0) {
                            $cuentaOrigen->accountsReceivable_status = $this->estatus[2];
                            $cuentaOrigen->update();
                        }

                        $cxcP = PROC_ACCOUNTS_RECEIVABLE_P::WHERE('accountsReceivableP_movement', '=', $cuentaOrigen->accountsReceivable_movement)->WHERE('accountsReceivableP_movementID', '=', $cuentaOrigen->accountsReceivable_movementID)->WHERE('accountsReceivableP_branchOffice', '=', $cuentaOrigen->accountsReceivable_branchOffice)->first();

                        $cxcP->accountsReceivableP_balance =   $cxcP->accountsReceivableP_balance - $detalle->accountsReceivableDetails_amount;
                        $cxcP->accountsReceivableP_balanceTotal =   $cxcP->accountsReceivableP_balanceTotal - $detalle->accountsReceivableDetails_amount;
                        $cxcP->update();

                        if ($cxcP->accountsReceivableP_balance == 0) {
                            $cxcP->delete();
                        }
                    } else {
                        $cuentaOrigen->accountsReceivable_balance =  $cuentaOrigen->accountsReceivable_balance - $detalle->accountsReceivableDetails_amount;
                        $cuentaOrigen->update();
                        $cxcP = PROC_ACCOUNTS_RECEIVABLE_P::WHERE('accountsReceivableP_movement', '=', $cuentaOrigen->accountsReceivable_movement)->WHERE('accountsReceivableP_movementID', '=', $cuentaOrigen->accountsReceivable_movementID)->WHERE('accountsReceivableP_branchOffice', '=', $cuentaOrigen->accountsReceivable_branchOffice)->first();
                        $cxcP->accountsReceivableP_balance =   $cxcP->accountsReceivableP_balance - $detalle->accountsReceivableDetails_amount;
                        $cxcP->accountsReceivableP_balanceTotal =   $cxcP->accountsReceivableP_balanceTotal - $detalle->accountsReceivableDetails_amount;
                        $cxcP->update();
                        if ($cxcP->accountsReceivableP_balance == 0) {
                            $cxcP->delete();
                        }
                    }
                    $pago -= $detalle->accountsReceivableDetails_amount;
                }

                $this->auxiliarAplicacion($folioAfectar->accountsReceivable_id);
                $this->agregarMovAplicacion($folioAfectar->accountsReceivable_id);
            }

            if ($folioAfectar->accountsReceivable_movement == 'Devolución de Anticipo' && $folioAfectar->accountsReceivable_status == $this->estatus[2]) {

                //buscamos la cuenta origen de la aplicacion
                if ($folioAfectar->accountsReceivable_origin !== null) {
                    $anticipo = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', $folioAfectar->accountsReceivable_origin)->where('accountsReceivable_movementID', '=', $folioAfectar->accountsReceivable_originID)->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->first();
                } else {
                    $referencias = explode(" ", $folioAfectar->accountsReceivable_reference);
                    $anticipo = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', $referencias[0]  . ' ' . $referencias[1])->where('accountsReceivable_movementID', '=', $referencias[2])->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->first();
                    // dd($referencias, $anticipo);
                }

                if ($folioAfectar->accountsReceivable_total <= $anticipo->accountsReceivable_total) {
                    $anticipo->accountsReceivable_balance = $anticipo->accountsReceivable_balance - $folioAfectar->accountsReceivable_total;
                    $anticipo->update();
                    if ($anticipo->accountsReceivable_balance == 0) {
                        $anticipo->accountsReceivable_status = $this->estatus[2];
                        $anticipo->update();

                        //buscamos el anticipo pendiente en cxp y lo eliminamos
                        $cxcP = PROC_ACCOUNTS_RECEIVABLE_P::WHERE('accountsReceivableP_movement', '=', $anticipo->accountsReceivable_movement)->WHERE('accountsReceivableP_movementID', '=', $anticipo->accountsReceivable_movementID)->WHERE('accountsReceivableP_branchOffice', '=', $anticipo->accountsReceivable_branchOffice)->first();
                        if ($cxcP !== null) {
                            $cxcP->delete();
                        }
                    }
                }

                if ($tipoCuenta != 'Caja') {

                    //agregamos auxiliar
                    // //agregar aux
                    $this->auxiliar($folioAfectar->accountsReceivable_id);
                    // //Generamos una solicitud de deposito de acuerdo al tipo de cuenta a CXC -Cheque del deposito
                    $cheque = $this->agregarCxCCheque($folioAfectar->accountsReceivable_id);
                    // //buscamos el cheque generado
                    $chequeGenerado = PROC_TREASURY::WHERE('treasuries_id', '=', $cheque)->first();
                    $this->agregarMov($folioAfectar->accountsReceivable_id);
                    // //agregar saldo
                    $this->agregarSaldo($folioAfectar->accountsReceivable_id); //se agrega el saldo a 0
                } else {
                    $ingreso = $this->agregarTesoreria($folioAfectar->accountsReceivable_id); //Agrega a tesoreria como concluido

                    $ingresoGenerado = PROC_TREASURY::WHERE('treasuries_id', '=', $ingreso)->first();

                    //observaciones: agregar cxppendiente

                    $this->agregarMovCaja($folioAfectar->accountsReceivable_id);

                    $this->actualizarSaldo($folioAfectar->accountsReceivable_id); //Actualiza el saldo de la cuenta de tipo caja
                }

                // $this->auxiliarDevolucion($folioAfectar->accountsReceivable_id);
                $this->agregarMovDevolucion($folioAfectar->accountsReceivable_id);
            }



            if ($isCreate) {
                $message = $id == 0 ? 'Movimiento afectado correctamente' : 'Movimiento afectado correctamente';
                $status = 200;

                if (session('company')->companies_stamped === '1' || session('company')->companies_stamped === 1) {
                    //Validamos si la empresa calcula impuestos o no
                    if (session('company')->companies_calculateTaxes === '0' || session('company')->companies_calculateTaxes === 0) {
                        //validacion de factura
                        $message2 = $this->validacionesFactura(session('company')->companies_key, $request['proveedorKey']);

                        if ($message2) {
                            $message = $message2;
                            $status = 500;
                            $lastId = $folioAfectar->accountsReceivable_id;
                            return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($egresoGenerado) ? $egresoGenerado : null]);
                        }

                        $timbrado = new TimbradoController();
                        $timbrado->timbrarCxc($folioAfectar->accountsReceivable_id, $request);

                        if ($timbrado->getStatus()) {
                            $company = CAT_COMPANIES::where('companies_key', session('company')->companies_key)->first();
                            $company->companies_AvailableStamps = $company->companies_AvailableStamps - 1;
                            // dd($company);
                            $company->save();

                            $historial = new HIST_STAMPED();
                            $historial->histStamped_IDCompany = session('company')->companies_key;
                            $historial->histStamped_Date = date('Y-m-d H:i:s');
                            $historial->histStamped_Stamp = intval(1);
                            // dd($historial);
                            $historial->save();
                        }

                        if (!$timbrado->getStatus()) {
                            $status = 500;
                            $message = "Afectación de " . $folioAfectar->accountsReceivable_movement . " realizada correctamente, pero no se pudo timbrar la factura";
                        }

                        if (!$timbrado->getStatus2()) {
                            $status = 400;
                            $message = "Conexión con el servidor de timbrado no disponible, favor de intentar más tarde";
                        }
                    }
                }
            } else {
                $message = $id == 0 ? 'Error al afectar el Movimiento' : 'Error al afectar el Movimiento';
                $status = 500;
            }
        } catch (\Throwable $th) {
            $status = 500;
            $message = $th->getMessage() . '-' . $th->getLine();
            $lastId = 0;
        }
        // }
        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId, 'cheque' => isset($chequeGenerado) ? $chequeGenerado : null, 'egreso' => isset($ingresoGenerado) ? $ingresoGenerado : null], 200);
    }

    public function agregarMovCaja($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $folio)->first();

        // dd($folioAfectar);
        if ($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Anticipo Clientes') {
            $movimiento = new PROC_MOVEMENT_FLOW();
            $movPosterior = PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->accountsReceivable_movementID)->where('treasuries_originType', '=', 'CxC')->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->where('treasuries_origin', '=', 'Anticipo Clientes')->first();

            $movimiento->movementFlow_branch = $folioAfectar->accountsReceivable_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->accountsReceivable_company;
            $movimiento->movementFlow_moduleOrigin = 'CxC';
            $movimiento->movementFlow_originID = $folioAfectar->accountsReceivable_id;
            $movimiento->movementFlow_movementOrigin = $folioAfectar->accountsReceivable_movement;
            $movimiento->movementFlow_movementOriginID = $folioAfectar->accountsReceivable_movementID;
            $movimiento->movementFlow_moduleDestiny = 'Din';
            $movimiento->movementFlow_destinityID = $movPosterior->treasuries_id;
            $movimiento->movementFlow_movementDestinity = $movPosterior->treasuries_movement;
            $movimiento->movementFlow_movementDestinityID = $movPosterior->treasuries_movementID;
            $movimiento->movementFlow_cancelled = 0;
            $movimiento->save();
        }


        if ($folioAfectar->accountsReceivable_status == $this->estatus[2] && $folioAfectar->accountsReceivable_movement == 'Cobro de Facturas') {

            $detalles = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', '=', $folioAfectar->accountsReceivable_id)->get();


            foreach ($detalles as $detalle) {
                //buscamos cxp original
                $cxcOriginal = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $detalle->accountsReceivableDetails_movReference)->first();
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->accountsReceivable_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->accountsReceivable_company;
                $movimiento->movementFlow_moduleOrigin = 'CxC';
                $movimiento->movementFlow_originID = $cxcOriginal->accountsReceivable_id;
                $movimiento->movementFlow_movementOrigin = $cxcOriginal->accountsReceivable_movement;
                $movimiento->movementFlow_movementOriginID = $cxcOriginal->accountsReceivable_movementID;
                $movimiento->movementFlow_moduleDestiny = 'CxC';
                $movimiento->movementFlow_destinityID = $folioAfectar->accountsReceivable_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->accountsReceivable_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->accountsReceivable_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();
            }


            $movimiento = new PROC_MOVEMENT_FLOW();
            $movPosterior = PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->accountsReceivable_movementID)->where('treasuries_originType', '=', 'CxC')->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->where('treasuries_origin', '=', 'Cobro de Facturas')->first();
            $movimiento->movementFlow_branch = $folioAfectar->accountsReceivable_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->accountsReceivable_company;
            $movimiento->movementFlow_moduleOrigin = 'CxC';
            $movimiento->movementFlow_originID = $folioAfectar->accountsReceivable_id;
            $movimiento->movementFlow_movementOrigin = $folioAfectar->accountsReceivable_movement;
            $movimiento->movementFlow_movementOriginID = $folioAfectar->accountsReceivable_movementID;
            $movimiento->movementFlow_moduleDestiny = 'Din';
            $movimiento->movementFlow_destinityID = $movPosterior->treasuries_id;
            $movimiento->movementFlow_movementDestinity = $movPosterior->treasuries_movement;
            $movimiento->movementFlow_movementDestinityID = $movPosterior->treasuries_movementID;
            $movimiento->movementFlow_cancelled = 0;
            $movimiento->save();
        }

        if ($folioAfectar->accountsReceivable_status == $this->estatus[2] && $folioAfectar->accountsReceivable_movement == 'Devolución de Anticipo') {
            $movimiento = new PROC_MOVEMENT_FLOW();
            $movPosterior = PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->accountsReceivable_movementID)->where('treasuries_originType', '=', 'CxC')->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->where('treasuries_origin', '=', 'Devolución de Anticipo')->first();

            $movimiento->movementFlow_branch = $folioAfectar->accountsReceivable_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->accountsReceivable_company;
            $movimiento->movementFlow_moduleOrigin = 'CxC';
            $movimiento->movementFlow_originID = $folioAfectar->accountsReceivable_id;
            $movimiento->movementFlow_movementOrigin = $folioAfectar->accountsReceivable_movement;
            $movimiento->movementFlow_movementOriginID = $folioAfectar->accountsReceivable_movementID;
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
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $folio)->first();


        //dd($folioAfectar);
        if ($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Anticipo Clientes') {

            $movPosterior =  PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->accountsReceivable_movementID)->where('treasuries_originType', '=', 'CxC')->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->where('treasuries_origin', '=', 'Anticipo Clientes')->first();

            $movimiento = new PROC_MOVEMENT_FLOW();
            $movimiento->movementFlow_branch = $folioAfectar->accountsReceivable_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->accountsReceivable_company;
            $movimiento->movementFlow_moduleOrigin = 'CxC';
            $movimiento->movementFlow_originID = $folioAfectar->accountsReceivable_id;
            $movimiento->movementFlow_movementOrigin = $folioAfectar->accountsReceivable_movement;
            $movimiento->movementFlow_movementOriginID = $folioAfectar->accountsReceivable_movementID;
            $movimiento->movementFlow_moduleDestiny = 'Din';
            $movimiento->movementFlow_destinityID = $movPosterior->treasuries_id;
            $movimiento->movementFlow_movementDestinity = $movPosterior->treasuries_movement;
            $movimiento->movementFlow_movementDestinityID = $movPosterior->treasuries_movementID;
            $movimiento->movementFlow_cancelled = 0;

            $movimiento->save();
        }

        if ($folioAfectar->accountsReceivable_status == $this->estatus[2] && $folioAfectar->accountsReceivable_movement == 'Cobro de Facturas') {

            $detalles = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', '=', $folioAfectar->accountsReceivable_id)->get();


            foreach ($detalles as $detalle) {
                //buscamos cxp original
                $cxcOriginal = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $detalle->accountsReceivableDetails_movReference)->first();
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->accountsReceivable_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->accountsReceivable_company;
                $movimiento->movementFlow_moduleOrigin = 'CxC';
                $movimiento->movementFlow_originID = $cxcOriginal->accountsReceivable_id;
                $movimiento->movementFlow_movementOrigin = $cxcOriginal->accountsReceivable_movement;
                $movimiento->movementFlow_movementOriginID = $cxcOriginal->accountsReceivable_movementID;
                $movimiento->movementFlow_moduleDestiny = 'CxC';
                $movimiento->movementFlow_destinityID = $folioAfectar->accountsReceivable_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->accountsReceivable_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->accountsReceivable_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();
            }

            $movPosterior =  PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->accountsReceivable_movementID)->where('treasuries_origin', '=', $folioAfectar->accountsReceivable_movement)->where('treasuries_originType', '=', 'CxC')->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->where('treasuries_movement', '=', 'Solicitud Depósito')->first();



            $movimiento = new PROC_MOVEMENT_FLOW();
            $movimiento->movementFlow_branch = $folioAfectar->accountsReceivable_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->accountsReceivable_company;
            $movimiento->movementFlow_moduleOrigin = 'CxC';
            $movimiento->movementFlow_originID = $folioAfectar->accountsReceivable_id;
            $movimiento->movementFlow_movementOrigin = $folioAfectar->accountsReceivable_movement;
            $movimiento->movementFlow_movementOriginID = $folioAfectar->accountsReceivable_movementID;
            $movimiento->movementFlow_moduleDestiny = 'Din';
            $movimiento->movementFlow_destinityID = $movPosterior->treasuries_id;
            $movimiento->movementFlow_movementDestinity = $movPosterior->treasuries_movement;
            $movimiento->movementFlow_movementDestinityID = $movPosterior->treasuries_movementID;
            $movimiento->movementFlow_cancelled = 0;
            $movimiento->save();
        }

        if ($folioAfectar->accountsReceivable_status == $this->estatus[2] && $folioAfectar->accountsReceivable_movement == 'Devolución de Anticipo') {

            $movPosterior =  PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->accountsReceivable_movementID)->where('treasuries_originType', '=', 'CxC')->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->where('treasuries_origin', '=', 'Devolución de Anticipo')->first();

            $movimiento = new PROC_MOVEMENT_FLOW();
            $movimiento->movementFlow_branch = $folioAfectar->accountsReceivable_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->accountsReceivable_company;
            $movimiento->movementFlow_moduleOrigin = 'CxC';
            $movimiento->movementFlow_originID = $folioAfectar->accountsReceivable_id;
            $movimiento->movementFlow_movementOrigin = $folioAfectar->accountsReceivable_movement;
            $movimiento->movementFlow_movementOriginID = $folioAfectar->accountsReceivable_movementID;
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
        $folioAfectar  = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $folio)->first();
        // dd($folioAfectar);
        if ($folioAfectar->accountsReceivable_movement == 'Aplicación' && $folioAfectar->accountsReceivable_status == $this->estatus[2]) {
            //Agregamos a aux el abono a la entrada pagada
            $detalle = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', '=', $folioAfectar->accountsReceivable_id)->get();

            foreach ($detalle as $det) {

                $mov = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $det->accountsReceivableDetails_movReference)->first();

                //agregamos a aux el abono al movimiento
                $auxiliar = new PROC_ASSISTANT();
                $auxiliar->assistant_companieKey = $folioAfectar->accountsReceivable_company;
                $auxiliar->assistant_branchKey = $folioAfectar->accountsReceivable_branchOffice;
                $auxiliar->assistant_branch = 'CxC';
                $auxiliar->assistant_movement = $folioAfectar->accountsReceivable_movement;
                $auxiliar->assistant_movementID = $folioAfectar->accountsReceivable_movementID;
                $auxiliar->assistant_module = 'CxC';
                $auxiliar->assistant_moduleID = $folioAfectar->accountsReceivable_id;
                $auxiliar->assistant_money = $folioAfectar->accountsReceivable_money;
                $auxiliar->assistant_typeChange = $folioAfectar->accountsReceivable_typeChange;
                $auxiliar->assistant_account = $folioAfectar->accountsReceivable_customer;

                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = null;
                $auxiliar->assistant_payment = $folioAfectar->accountsReceivable_total;
                $auxiliar->assistant_apply = $mov->accountsReceivable_movement;
                $auxiliar->assistant_applyID =  $mov->accountsReceivable_movementID;
                $auxiliar->assistant_canceled = 0;
                $auxiliar->assistant_reference = $folioAfectar->accountsReceivable_reference;
                $auxiliar->save();
            }

            //buscamos el movimiento anterior
            if ($folioAfectar->accountsReceivable_origin !== null) {
                $anticipo = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', $folioAfectar->accountsReceivable_origin)->where('accountsReceivable_movementID', '=', $folioAfectar->accountsReceivable_originID)->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->first();
            } else {
                $referencias = explode(" ", $folioAfectar->accountsReceivable_reference);
                $anticipo = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', $referencias[0]  . ' ' . $referencias[1])->where('accountsReceivable_movementID', '=', $referencias[2])->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->first();
                // dd($referencias, $anticipo);
            }
            $auxiliar = new PROC_ASSISTANT();
            $auxiliar->assistant_companieKey = $folioAfectar->accountsReceivable_company;
            $auxiliar->assistant_branchKey = $folioAfectar->accountsReceivable_branchOffice;
            $auxiliar->assistant_branch = 'CxC';
            $auxiliar->assistant_movement = $folioAfectar->accountsReceivable_movement;
            $auxiliar->assistant_movementID = $folioAfectar->accountsReceivable_movementID;
            $auxiliar->assistant_module = 'CxC';
            $auxiliar->assistant_moduleID = $folioAfectar->accountsReceivable_id;
            $auxiliar->assistant_money = $folioAfectar->accountsReceivable_money;
            $auxiliar->assistant_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $auxiliar->assistant_account = $folioAfectar->accountsReceivable_customer;

            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = $folioAfectar->accountsReceivable_total;
            $auxiliar->assistant_payment = null;
            $auxiliar->assistant_apply = $anticipo->accountsReceivable_movement;
            $auxiliar->assistant_applyID =  $anticipo->accountsReceivable_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->accountsReceivable_reference;
            $auxiliar->save();
        }
    }

    public function agregarMovAplicacion($folio)
    {
        $folioAfectar  = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $folio)->first();
        // dd($folioAfectar);
        if ($folioAfectar->accountsReceivable_movement == 'Aplicación' && $folioAfectar->accountsReceivable_status == $this->estatus[2]) {
            //Agregamos a aux el abono a la entrada pagada
            $detalle = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', '=', $folioAfectar->accountsReceivable_id)->get();

            foreach ($detalle as $det) {

                $mov = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $det->accountsReceivableDetails_movReference)->first();

                //agregamos el movimiento 
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->accountsReceivable_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->accountsReceivable_company;
                $movimiento->movementFlow_moduleOrigin = 'CxC';
                $movimiento->movementFlow_originID = $mov->accountsReceivable_id;
                $movimiento->movementFlow_movementOrigin = $mov->accountsReceivable_movement;
                $movimiento->movementFlow_movementOriginID = $mov->accountsReceivable_movementID;
                $movimiento->movementFlow_moduleDestiny = 'CxC';
                $movimiento->movementFlow_destinityID = $folioAfectar->accountsReceivable_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->accountsReceivable_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->accountsReceivable_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();
            }

            //buscamos el movimiento anterior
            if ($folioAfectar->accountsReceivable_origin !== null) {
                $anticipo = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', $folioAfectar->accountsReceivable_origin)->where('accountsReceivable_movementID', '=', $folioAfectar->accountsReceivable_originID)->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->first();
            } else {
                $referencias = explode(" ", $folioAfectar->accountsReceivable_reference);
                $anticipo = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', $referencias[0]  . ' ' . $referencias[1])->where('accountsReceivable_movementID', '=', $referencias[2])->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->first();
                // dd($referencias, $anticipo);
            }

            $movimiento = new PROC_MOVEMENT_FLOW();
            $movimiento->movementFlow_branch = $folioAfectar->accountsReceivable_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->accountsReceivable_company;
            $movimiento->movementFlow_moduleOrigin = 'CxC';
            $movimiento->movementFlow_originID = $anticipo->accountsReceivable_id;
            $movimiento->movementFlow_movementOrigin = $anticipo->accountsReceivable_movement;
            $movimiento->movementFlow_movementOriginID = $anticipo->accountsReceivable_movementID;
            $movimiento->movementFlow_moduleDestiny = 'CxC';
            $movimiento->movementFlow_destinityID = $folioAfectar->accountsReceivable_id;
            $movimiento->movementFlow_movementDestinity = $folioAfectar->accountsReceivable_movement;
            $movimiento->movementFlow_movementDestinityID = $folioAfectar->accountsReceivable_movementID;
            $movimiento->movementFlow_cancelled = 0;
            $movimiento->save();
        }
    }

    // public function auxiliarDevolucion($folio)
    // {
    //     $folioAfectar  = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $folio)->first();
    //     // dd($folioAfectar);
    //     if ($folioAfectar->accountsReceivable_movement == 'Devolución de Anticipo' && $folioAfectar->accountsReceivable_status == $this->estatus[2]) {

    //         //buscamos el movimiento anterior
    //         if ($folioAfectar->accountsReceivable_origin !== null) {
    //             $anticipo = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', $folioAfectar->accountsReceivable_origin)->where('accountsReceivable_movementID', '=', $folioAfectar->accountsReceivable_originID)->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->first();
    //         } else {
    //             $referencias = explode(" ", $folioAfectar->accountsReceivable_reference);
    //             $anticipo = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', $referencias[0]  . ' ' . $referencias[1] . ' ' . $referencias[2])->where('accountsReceivable_movementID', '=', $referencias[3])->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->first();
    //             // dd($referencias, $anticipo);
    //         }
    //         $auxiliar = new PROC_ASSISTANT();
    //         $auxiliar->assistant_companieKey = $folioAfectar->accountsReceivable_company;
    //         $auxiliar->assistant_branchKey = $folioAfectar->accountsReceivable_branchOffice;
    //         $auxiliar->assistant_branch = 'CxC';
    //         $auxiliar->assistant_movement = $folioAfectar->accountsReceivable_movement;
    //         $auxiliar->assistant_movementID = $folioAfectar->accountsReceivable_movementID;
    //         $auxiliar->assistant_module = 'CxC';
    //         $auxiliar->assistant_moduleID = $folioAfectar->accountsReceivable_id;
    //         $auxiliar->assistant_money = $folioAfectar->accountsReceivable_money;
    //         $auxiliar->assistant_typeChange = $folioAfectar->accountsReceivable_typeChange;
    //         $auxiliar->assistant_account = $folioAfectar->accountsReceivable_customer;

    //         $year = Carbon::now()->year;
    //         //sacamos el periodo 
    //         $period = Carbon::now()->month;


    //         $auxiliar->assistant_year = $year;
    //         $auxiliar->assistant_period = $period;
    //         $auxiliar->assistant_charge = $folioAfectar->accountsReceivable_total;
    //         $auxiliar->assistant_payment = null;
    //         $auxiliar->assistant_apply = $anticipo->accountsReceivable_movement;
    //         $auxiliar->assistant_applyID =  $anticipo->accountsReceivable_movementID;
    //         $auxiliar->assistant_canceled = 0;
    //         $auxiliar->assistant_reference = $folioAfectar->accountsReceivable_reference;
    //         $auxiliar->save();
    //     }
    // }

    public function agregarMovDevolucion($folio)
    {
        $folioAfectar  = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $folio)->first();
        // dd($folioAfectar);
        if ($folioAfectar->accountsReceivable_movement == 'Devolución de Anticipo' && $folioAfectar->accountsReceivable_status == $this->estatus[2]) {

            //buscamos el movimiento anterior
            if ($folioAfectar->accountsReceivable_origin !== null) {
                $anticipo = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', $folioAfectar->accountsReceivable_origin)->where('accountsReceivable_movementID', '=', $folioAfectar->accountsReceivable_originID)->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->first();
            } else {
                $referencias = explode(" ", $folioAfectar->accountsReceivable_reference);
                $anticipo = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', $referencias[0]  . ' ' . $referencias[1])->where('accountsReceivable_movementID', '=', $referencias[2])->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->first();
                // dd($referencias, $anticipo);
            }

            $movimiento = new PROC_MOVEMENT_FLOW();
            $movimiento->movementFlow_branch = $folioAfectar->accountsReceivable_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->accountsReceivable_company;
            $movimiento->movementFlow_moduleOrigin = 'CxC';
            $movimiento->movementFlow_originID = $anticipo->accountsReceivable_id;
            $movimiento->movementFlow_movementOrigin = $anticipo->accountsReceivable_movement;
            $movimiento->movementFlow_movementOriginID = $anticipo->accountsReceivable_movementID;
            $movimiento->movementFlow_moduleDestiny = 'CxC';
            $movimiento->movementFlow_destinityID = $folioAfectar->accountsReceivable_id;
            $movimiento->movementFlow_movementDestinity = $folioAfectar->accountsReceivable_movement;
            $movimiento->movementFlow_movementDestinityID = $folioAfectar->accountsReceivable_movementID;
            $movimiento->movementFlow_cancelled = 0;
            $movimiento->save();
        }
    }

    public function ayudaVer()
    {
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', 1)->first();




        $movPosterior =  PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->accountsReceivable_movementID)->where('treasuries_originType', '=', 'CxC')->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->where('treasuries_origin', '=', 'Anticipo Clientes')->first();

        dd($folioAfectar, $movPosterior);
    }

    public function auxiliar($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $folio)->first();

        $cxc = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movementID', '=', $folioAfectar->accountsReceivable_movementID)->where('accountsReceivable_id', '=', $folio)->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->first();
        // dd($folioAfectar);
        if ($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Anticipo Clientes') {
            $assistantApply = $folioAfectar->accountsReceivable_movement;
            $assistantApplyID = $folioAfectar->accountsReceivable_movementID;
        }
        if ($folioAfectar->accountsReceivable_status == $this->estatus[2] && $folioAfectar->accountsReceivable_movement == 'Cobro de Facturas') {

            $assistantApply = $cxc->accountsReceivable_origin;
            $assistantApplyID = $cxc->accountsReceivable_originID;
        }
        if ($folioAfectar->accountsReceivable_status == $this->estatus[2] && $folioAfectar->accountsReceivable_movement == 'Devolución de Anticipo') {

            $assistantApply = $folioAfectar->accountsReceivable_movement;
            $assistantApplyID = $folioAfectar->accountsReceivable_movementID;
        }

        //agregar datos a aux
        $auxiliar = new PROC_ASSISTANT();

        $auxiliar->assistant_companieKey = $folioAfectar->accountsReceivable_company;
        $auxiliar->assistant_branchKey = $folioAfectar->accountsReceivable_branchOffice;
        $auxiliar->assistant_branch = 'CxC';
        //buscamos el modulo de cxp

        $auxiliar->assistant_movement = $cxc->accountsReceivable_movement;
        $auxiliar->assistant_movementID = $cxc->accountsReceivable_movementID;
        $auxiliar->assistant_module = 'CxC';
        $auxiliar->assistant_moduleID = $cxc->accountsReceivable_id;
        $auxiliar->assistant_money = $folioAfectar->accountsReceivable_money;
        $auxiliar->assistant_typeChange = $folioAfectar->accountsReceivable_typeChange;
        $auxiliar->assistant_account = $folioAfectar->accountsReceivable_customer;

        //ponemos fecha del ejercicio
        $year = Carbon::now()->year;
        //sacamos el periodo 
        $period = Carbon::now()->month;

        $auxiliar->assistant_year = $year;
        $auxiliar->assistant_period = $period;
        $auxiliar->assistant_charge = null;
        $auxiliar->assistant_payment = $folioAfectar->accountsReceivable_total;
        $auxiliar->assistant_apply = $assistantApply;
        $auxiliar->assistant_applyID = $assistantApplyID;
        $auxiliar->assistant_canceled = 0;
        $auxiliar->assistant_reference = $folioAfectar->accountsReceivable_reference;


        $auxiliar->save();
    }

    public function agregarCxCP($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $folio)->first();

        // dd($folioAfectar);
        if ($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Anticipo Clientes') {

            //agregamos una nueva cuenta por pagar para la compra
            $cuentaPagar = new PROC_ACCOUNTS_RECEIVABLE_P();
            $cuentaPagar->accountsReceivableP_movement = $folioAfectar->accountsReceivable_movement;
            $cuentaPagar->accountsReceivableP_movementID = $folioAfectar->accountsReceivable_movementID;
            $cuentaPagar->accountsReceivableP_issuedate = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d H:i:s');
            $cuentaPagar->accountsReceivableP_expiration =  Carbon::parse($folioAfectar->accountsReceivable_expiration)->format('Y-m-d H:i:s');

            //dias credito y moratorio
            $emision = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d');
            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
            $vencimiento = Carbon::parse($folioAfectar->accountsReceivable_expiration)->format('Y-m-d');
            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

            $diasCredito = $currentDate->diffInDays($shippingDate);
            $diasMoratorio = $shippingDate->diffInDays($currentDate);
            $cuentaPagar->accountsReceivableP_creditDays = $diasCredito;
            $cuentaPagar->accountsReceivableP_moratoriumDays = '-' . $diasMoratorio;
            $cuentaPagar->accountsReceivableP_money = $folioAfectar->accountsReceivable_money;
            $cuentaPagar->accountsReceivableP_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $cuentaPagar->accountsReceivableP_moneyAccount = $folioAfectar->accountsReceivable_moneyAccount;

            $cuentaPagar->accountsReceivableP_customer = $folioAfectar->accountsReceivable_customer;
            $cuentaPagar->accountsReceivableP_condition = $folioAfectar->accountsReceivable_condition;

            $cuentaPagar->accountsReceivableP_formPayment = $folioAfectar->accountsReceivable_formPayment;
            $cuentaPagar->accountsReceivableP_amount = $folioAfectar->accountsReceivable_amount;
            $cuentaPagar->accountsReceivableP_taxes = $folioAfectar->accountsReceivable_taxes;
            $cuentaPagar->accountsReceivableP_total = $folioAfectar->accountsReceivable_total;
            $cuentaPagar->accountsReceivableP_balanceTotal = $folioAfectar->accountsReceivable_total;
            $cuentaPagar->accountsReceivableP_reference = $folioAfectar->accountsReceivable_reference;
            $cuentaPagar->accountsReceivableP_balance = $folioAfectar->accountsReceivable_total;
            $cuentaPagar->accountsReceivableP_company = $folioAfectar->accountsReceivable_company;
            $cuentaPagar->accountsReceivableP_branchOffice = $folioAfectar->accountsReceivable_branchOffice;
            $cuentaPagar->accountsReceivableP_user = $folioAfectar->accountsReceivable_user;
            $cuentaPagar->accountsReceivableP_status = $this->estatus[1];
            $cuentaPagar->accountsReceivableP_concept = $folioAfectar->accountsReceivable_concept;
            $cuentaPagar->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $cuentaPagar->updated_at = Carbon::now()->format('Y-m-d H:i:s');

            $create = $cuentaPagar->save();

            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por cobrar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por cobrar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }


            return response()->json(['message' => $message, 'estatus' => $estatus]);
        }
    }

    public function agregarCxCPCheque($folio)
    {

        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $folio)->first();

        // dd($folioAfectar);
        if (($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Solicitud Depósito') || ($folioAfectar->accountsReceivable_status == $this->estatus[2] && $folioAfectar->accountsReceivable_movement == 'Cobro de Facturas')) {

            //agregamos una nueva cuenta por pagar para la compra
            $cuentaPagar = new PROC_ACCOUNTS_RECEIVABLE_P();
            $cuentaPagar->accountsReceivableP_movement = 'Solicitud Depósito';
            $cuentaPagar->accountsReceivableP_movementID = $folioAfectar->accountsReceivable_movementID;
            $cuentaPagar->accountsReceivableP_issuedate = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d H:i:s');
            $cuentaPagar->accountsReceivableP_expiration =  Carbon::parse($folioAfectar->accountsReceivable_expiration)->format('Y-m-d H:i:s');

            //dias credito y moratorio
            $emision = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d');
            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
            $vencimiento = Carbon::parse($folioAfectar->accountsReceivable_expiration)->format('Y-m-d');
            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

            $diasCredito = $currentDate->diffInDays($shippingDate);
            $diasMoratorio = $shippingDate->diffInDays($currentDate);
            $cuentaPagar->accountsReceivableP_creditDays = $diasCredito;
            $cuentaPagar->accountsReceivableP_moratoriumDays = '-' . $diasMoratorio;
            $cuentaPagar->accountsReceivableP_money = $folioAfectar->accountsReceivable_money;
            $cuentaPagar->accountsReceivableP_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $cuentaPagar->accountsReceivableP_moneyAccount = $folioAfectar->accountsReceivable_moneyAccount;

            $cuentaPagar->accountsReceivableP_customer = $folioAfectar->accountsReceivable_customer;
            $cuentaPagar->accountsReceivableP_condition = $folioAfectar->accountsReceivable_condition;

            $cuentaPagar->accountsReceivableP_formPayment = $folioAfectar->accountsReceivable_paymentMethod;
            $cuentaPagar->accountsReceivableP_amount = $folioAfectar->accountsReceivable_amount;
            $cuentaPagar->accountsReceivableP_taxes = $folioAfectar->accountsReceivable_taxes;
            $cuentaPagar->accountsReceivableP_total = $folioAfectar->accountsReceivable_total;
            $cuentaPagar->accountsReceivableP_balanceTotal = $folioAfectar->accountsReceivable_total;
            $cuentaPagar->accountsReceivableP_reference = $folioAfectar->accountsReceivable_reference;
            $cuentaPagar->accountsReceivableP_balance = $folioAfectar->accountsReceivable_total;
            $cuentaPagar->accountsReceivableP_company = $folioAfectar->accountsReceivable_company;
            $cuentaPagar->accountsReceivableP_branchOffice = $folioAfectar->accountsReceivable_branchOffice;
            $cuentaPagar->accountsReceivableP_user = $folioAfectar->accountsReceivable_user;
            $cuentaPagar->accountsReceivableP_status = $this->estatus[1];
            $cuentaPagar->accountsReceivableP_origin = $folioAfectar->accountsReceivable_movement;
            $cuentaPagar->accountsReceivableP_originID = $folioAfectar->accountsReceivable_movementID;
            $cuentaPagar->accountsReceivableP_originType = 'Din';
            $cuentaPagar->accountsReceivableP_concept = $folioAfectar->accountsReceivable_concept;

            $create = $cuentaPagar->save();

            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por cobrar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por cobrar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }


            return response()->json(['message' => $message, 'estatus' => $estatus]);
        }

        if ($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Sol. de Cheque/Transferencia') {

            //agregamos una nueva cuenta por pagar para la compra
            $cuentaPagar = new PROC_ACCOUNTS_RECEIVABLE_P();
            $cuentaPagar->accountsReceivableP_movement = 'Sol. de Cheque/Transferencia';
            $cuentaPagar->accountsReceivableP_movementID = $folioAfectar->accountsReceivable_movementID;
            $cuentaPagar->accountsReceivableP_issuedate = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d H:i:s');
            $cuentaPagar->accountsReceivableP_expiration =  Carbon::parse($folioAfectar->accountsReceivable_expiration)->format('Y-m-d H:i:s');

            //dias credito y moratorio
            $emision = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d');
            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
            $vencimiento = Carbon::parse($folioAfectar->accountsReceivable_expiration)->format('Y-m-d');
            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

            $diasCredito = $currentDate->diffInDays($shippingDate);
            $diasMoratorio = $shippingDate->diffInDays($currentDate);
            $cuentaPagar->accountsReceivableP_creditDays = $diasCredito;
            $cuentaPagar->accountsReceivableP_moratoriumDays = '-' . $diasMoratorio;
            $cuentaPagar->accountsReceivableP_money = $folioAfectar->accountsReceivable_money;
            $cuentaPagar->accountsReceivableP_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $cuentaPagar->accountsReceivableP_moneyAccount = $folioAfectar->accountsReceivable_moneyAccount;

            $cuentaPagar->accountsReceivableP_customer = $folioAfectar->accountsReceivable_customer;
            $cuentaPagar->accountsReceivableP_condition = $folioAfectar->accountsReceivable_condition;

            $cuentaPagar->accountsReceivableP_formPayment = $folioAfectar->accountsReceivable_paymentMethod;
            $cuentaPagar->accountsReceivableP_amount = $folioAfectar->accountsReceivable_amount;
            $cuentaPagar->accountsReceivableP_taxes = $folioAfectar->accountsReceivable_taxes;
            $cuentaPagar->accountsReceivableP_total = $folioAfectar->accountsReceivable_total;
            $cuentaPagar->accountsReceivableP_balanceTotal = $folioAfectar->accountsReceivable_total;
            $cuentaPagar->accountsReceivableP_reference = $folioAfectar->accountsReceivable_reference;
            $cuentaPagar->accountsReceivableP_balance = $folioAfectar->accountsReceivable_total;
            $cuentaPagar->accountsReceivableP_company = $folioAfectar->accountsReceivable_company;
            $cuentaPagar->accountsReceivableP_branchOffice = $folioAfectar->accountsReceivable_branchOffice;
            $cuentaPagar->accountsReceivableP_user = $folioAfectar->accountsReceivable_user;
            $cuentaPagar->accountsReceivableP_status = $this->estatus[1];
            $cuentaPagar->accountsReceivableP_origin = $folioAfectar->accountsReceivable_movement;
            $cuentaPagar->accountsReceivableP_originID = $folioAfectar->accountsReceivable_movementID;
            $cuentaPagar->accountsReceivableP_originType = 'Din';
            $cuentaPagar->accountsReceivableP_concept = $folioAfectar->accountsReceivable_concept;

            $create = $cuentaPagar->save();

            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por cobrar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por cobrar';
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
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', $folio)->first();
        // dd($folioAfectar);
        if ($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Anticipo Clientes') {

            //agregamos a tesoreria
            $tesoreria = new PROC_TREASURY();
            $folioIngreso = PROC_TREASURY::where('treasuries_movement', '=', 'Ingreso')->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->max('treasuries_movementID');
            $folioIngreso = $folioIngreso == null ? 1 : $folioIngreso + 1;
            $tesoreria->treasuries_movement = 'Ingreso';
            $tesoreria->treasuries_movementID = $folioIngreso;
            $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d H:i:s');
            $tesoreria->treasuries_money = $folioAfectar->accountsReceivable_money;
            $tesoreria->treasuries_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $tesoreria->treasuries_moneyAccount = $folioAfectar->accountsReceivable_moneyAccount;
            // $tesoreria->treasuries_moneyAccountOrigin = $folioAfectar->accountsReceivable_moneyAccount;
            $tesoreria->treasuries_paymentMethod = $folioAfectar->accountsReceivable_formPayment;
            $tesoreria->treasuries_beneficiary = $folioAfectar->accountsReceivable_customer;
            $tesoreria->treasuries_reference = $folioAfectar->accountsReceivable_reference;
            $tesoreria->treasuries_amount = $folioAfectar->accountsReceivable_amount;
            $tesoreria->treasuries_taxes = $folioAfectar->accountsReceivable_taxes;
            $tesoreria->treasuries_total = $folioAfectar->accountsReceivable_total;
            $tesoreria->treasuries_company = $folioAfectar->accountsReceivable_company;
            $tesoreria->treasuries_branchOffice = $folioAfectar->accountsReceivable_branchOffice;
            $tesoreria->treasuries_user = $folioAfectar->accountsReceivable_user;
            $tesoreria->treasuries_status = $this->estatus[2];
            $tesoreria->treasuries_originType = 'CxC';
            $tesoreria->treasuries_origin = 'Anticipo Clientes';
            $tesoreria->treasuries_originID = $folioAfectar->accountsReceivable_movementID;
            $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $tesoreria->save();



            $lastId = PROC_ACCOUNTS_RECEIVABLE::latest('accountsReceivable_id')->first()->accountsReceivable_id;
            $lastId2 = PROC_TREASURY::latest('treasuries_id')->first()->treasuries_id;

            //Guardamos la información en la tabla PROC_TREASORIES_DETAILS
            $detalle = new PROC_TREASURY_DETAILS();
            $detalle->treasuriesDetails_treasuriesID = $lastId2;
            $detalle->treasuriesDetails_amount = $folioAfectar->accountsReceivable_total;
            $detalle->treasuriesDetails_apply = $folioAfectar->accountsReceivable_movement;
            $detalle->treasuriesDetails_applyIncrement = $folioAfectar->accountsReceivable_movementID;
            $detalle->treasuriesDetails_paymentMethod = $folioAfectar->accountsReceivable_formPayment;
            $detalle->treasuriesDetails_movReference = $lastId;
            $detalle->save();

            //Agregamos a auxiliar
            $this->auxiliarCaja($lastId);
            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por cobrar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por cobrar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }


            return $lastId2;
        }

        if ($folioAfectar->accountsReceivable_status == $this->estatus[2] && $folioAfectar->accountsReceivable_movement == 'Cobro de Facturas') {


            //agregamos a tesoreria
            $tesoreria = new PROC_TREASURY();
            $folioIngreso = PROC_TREASURY::where('treasuries_movement', '=', 'Ingreso')->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->max('treasuries_movementID');
            $folioIngreso = $folioIngreso == null ? 1 : $folioIngreso + 1;
            $tesoreria->treasuries_movement = 'Ingreso';
            $tesoreria->treasuries_movementID = $folioIngreso;
            $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d H:i:s');
            $tesoreria->treasuries_money = $folioAfectar->accountsReceivable_money;
            $tesoreria->treasuries_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $tesoreria->treasuries_moneyAccount = $folioAfectar->accountsReceivable_moneyAccount;
            $tesoreria->treasuries_moneyAccountOrigin = $folioAfectar->accountsReceivable_moneyAccount;
            $tesoreria->treasuries_paymentMethod = $folioAfectar->accountsReceivable_formPayment;
            $tesoreria->treasuries_beneficiary = $folioAfectar->accountsReceivable_customer;
            $tesoreria->treasuries_reference = $folioAfectar->accountsReceivable_reference;
            $tesoreria->treasuries_amount = $folioAfectar->accountsReceivable_amount;
            $tesoreria->treasuries_taxes = $folioAfectar->accountsReceivable_taxes;
            $tesoreria->treasuries_total = $folioAfectar->accountsReceivable_total;
            $tesoreria->treasuries_company = $folioAfectar->accountsReceivable_company;
            $tesoreria->treasuries_branchOffice = $folioAfectar->accountsReceivable_branchOffice;
            $tesoreria->treasuries_user = $folioAfectar->accountsReceivable_user;
            $tesoreria->treasuries_status = $this->estatus[2];
            $tesoreria->treasuries_originType = 'CxC';
            $tesoreria->treasuries_origin = 'Cobro de Facturas';
            $tesoreria->treasuries_originID = $folioAfectar->accountsReceivable_movementID;
            $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $tesoreria->save();

            $lastId = isset($tesoreria->treasuries_id) ?  $tesoreria->treasuries_id : PROC_TREASURY::latest('treasuries_id')->first()->treasuries_id;

            //  //Agregamos a auxiliar
            $this->auxiliar($folioAfectar->accountsReceivable_id);
            $this->auxiliarCaja2($lastId);
            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por cobrar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por cobrar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }


            return $lastId;
        }

        if ($folioAfectar->accountsReceivable_status == $this->estatus[2] && $folioAfectar->accountsReceivable_movement == 'Devolución de Anticipo') {

            //agregamos a tesoreria
            $tesoreria = new PROC_TREASURY();
            $folioIngreso = PROC_TREASURY::where('treasuries_movement', '=', 'Egreso')->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->max('treasuries_movementID');
            $folioIngreso = $folioIngreso == null ? 1 : $folioIngreso + 1;
            $tesoreria->treasuries_movement = 'Egreso';
            $tesoreria->treasuries_movementID = $folioIngreso;
            $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d H:i:s');
            $tesoreria->treasuries_money = $folioAfectar->accountsReceivable_money;
            $tesoreria->treasuries_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $tesoreria->treasuries_moneyAccount = $folioAfectar->accountsReceivable_moneyAccount;
            // $tesoreria->treasuries_moneyAccountOrigin = $folioAfectar->accountsReceivable_moneyAccount;
            $tesoreria->treasuries_paymentMethod = $folioAfectar->accountsReceivable_formPayment;
            $tesoreria->treasuries_beneficiary = $folioAfectar->accountsReceivable_customer;
            $tesoreria->treasuries_reference = 'Devolución de Anticipo';
            $tesoreria->treasuries_amount = $folioAfectar->accountsReceivable_amount;
            $tesoreria->treasuries_taxes = $folioAfectar->accountsReceivable_taxes;
            $tesoreria->treasuries_total = $folioAfectar->accountsReceivable_total;
            $tesoreria->treasuries_company = $folioAfectar->accountsReceivable_company;
            $tesoreria->treasuries_branchOffice = $folioAfectar->accountsReceivable_branchOffice;
            $tesoreria->treasuries_user = $folioAfectar->accountsReceivable_user;
            $tesoreria->treasuries_status = $this->estatus[2];
            $tesoreria->treasuries_originType = 'CxC';
            $tesoreria->treasuries_origin = 'Devolución de Anticipo';
            $tesoreria->treasuries_originID = $folioAfectar->accountsReceivable_movementID;
            $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $tesoreria->save();



            $lastId = PROC_ACCOUNTS_RECEIVABLE::latest('accountsReceivable_id')->first()->accountsReceivable_id;
            $lastId2 = PROC_TREASURY::latest('treasuries_id')->first()->treasuries_id;

            //Guardamos la información en la tabla PROC_TREASORIES_DETAILS
            $detalle = new PROC_TREASURY_DETAILS();
            $detalle->treasuriesDetails_treasuriesID = $lastId2;
            $detalle->treasuriesDetails_amount = $folioAfectar->accountsReceivable_total;
            $detalle->treasuriesDetails_apply = $folioAfectar->accountsReceivable_movement;
            $detalle->treasuriesDetails_applyIncrement = $folioAfectar->accountsReceivable_movementID;
            $detalle->treasuriesDetails_paymentMethod = $folioAfectar->accountsReceivable_formPayment;
            $detalle->treasuriesDetails_movReference = $lastId;
            $detalle->save();

            //Agregamos a auxiliar
            $this->auxiliarCaja($lastId);
            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por cobrar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por cobrar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }


            return $lastId2;
        }

        // dd($folioAfectar);
    }

    public function auxiliarCaja($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $folio)->first();


        if ($folioAfectar->accountsReceivable_status == $this->estatus[1] || $folioAfectar->accountsReceivable_status == $this->estatus[2]) {

            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->accountsReceivable_company;
            $auxiliar->assistant_branchKey = $folioAfectar->accountsReceivable_branchOffice;
            $auxiliar->assistant_branch = 'CxC';

            $auxiliar->assistant_movement = $folioAfectar->accountsReceivable_movement;
            $auxiliar->assistant_movementID = $folioAfectar->accountsReceivable_movementID;
            $auxiliar->assistant_module = 'CxC';
            $auxiliar->assistant_moduleID = $folioAfectar->accountsReceivable_id;
            $auxiliar->assistant_money = $folioAfectar->accountsReceivable_money;
            $auxiliar->assistant_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $auxiliar->assistant_account = $folioAfectar->accountsReceivable_customer;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            if ($folioAfectar->accountsReceivable_movement === 'Devolución de Anticipo') {
                $auxiliar->assistant_charge = null;
                $auxiliar->assistant_payment = $folioAfectar->accountsReceivable_total;
            } else {
                $auxiliar->assistant_charge = $folioAfectar->accountsReceivable_total;
                $auxiliar->assistant_payment = null;
            }
            $auxiliar->assistant_apply = $folioAfectar->accountsReceivable_movement;
            $auxiliar->assistant_applyID = $folioAfectar->accountsReceivable_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->accountsReceivable_reference;

            $auxiliar->save();


            //------------------------------
            //agregar datos a aux  Ingreso
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->accountsReceivable_company;
            $auxiliar->assistant_branchKey = $folioAfectar->accountsReceivable_branchOffice;
            $auxiliar->assistant_branch = 'Din';

            $tesoreria = PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->accountsReceivable_movementID)
                ->where('treasuries_originType', '=', 'CxC')
                ->where('treasuries_origin', '=', 'Anticipo Clientes')
                ->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)
                ->first();
            //si es Anticipo Clientes vamos a buscar el ingreso
            if ($folioAfectar->accountsReceivable_movement == 'Anticipo Clientes') {
                $tesoreria = PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->accountsReceivable_movementID)
                    ->where('treasuries_originType', '=', 'CxC')
                    ->where('treasuries_origin', '=', 'Anticipo Clientes')
                    ->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)
                    ->first();
            } elseif ($folioAfectar->accountsReceivable_movement == 'Devolución de Anticipo') {
                $tesoreria = PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->accountsReceivable_movementID)
                    ->where('treasuries_originType', '=', 'CxC')
                    ->where('treasuries_origin', '=', 'Devolución de Anticipo')
                    ->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)
                    ->first();
            }

            $auxiliar->assistant_movement = $tesoreria->treasuries_movement;
            $auxiliar->assistant_movementID = $tesoreria->treasuries_movementID;
            $auxiliar->assistant_module = 'Din';
            $auxiliar->assistant_moduleID = $tesoreria->treasuries_id;
            $auxiliar->assistant_money = $folioAfectar->accountsReceivable_money;
            $auxiliar->assistant_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $auxiliar->assistant_account = $folioAfectar->accountsReceivable_moneyAccount;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            //si es diferente de devolución de anticipo se pone el total de la cuenta por cobrar en charge, sino en payment
            if ($folioAfectar->accountsReceivable_movement === 'Devolución de Anticipo') {
                $auxiliar->assistant_charge = null;
                $auxiliar->assistant_payment = $folioAfectar->accountsReceivable_total;
            } else {
                $auxiliar->assistant_charge = $folioAfectar->accountsReceivable_total;
                $auxiliar->assistant_payment = null;
            }
            // $auxiliar->assistant_charge = $folioAfectar->accountsReceivable_total;
            // $auxiliar->assistant_payment = null;
            // $auxiliar->assistant_charge = $folioAfectar->accountsReceivable_total;
            // $auxiliar->assistant_payment = null;
            $auxiliar->assistant_apply = $tesoreria->treasuries_movement;
            $auxiliar->assistant_applyID = $tesoreria->treasuries_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->accountsReceivable_reference;

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
            $auxiliar->assistant_charge = $folioAfectar->treasuries_total;
            $auxiliar->assistant_payment = null;
            $auxiliar->assistant_apply = $folioAfectar->treasuries_movement;
            $auxiliar->assistant_applyID = $folioAfectar->treasuries_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->treasuries_reference;

            $auxiliar->save();
        }
    }

    public function agregarChequeTesoreria($folio, $originID, $idOrigen)
    {
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', $folio)->first();
        // dd($folioAfectar);
        if ($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Solicitud Depósito') {

            //agregamos a tesoreria
            $tesoreria = new PROC_TREASURY();
            $folioIngreso = PROC_TREASURY::where('treasuries_movement', '=', 'Solicitud Depósito')->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->max('treasuries_movementID');
            $folioIngreso = $folioIngreso == null ? 1 : $folioIngreso + 1;
            $tesoreria->treasuries_movement = 'Solicitud Depósito';
            $tesoreria->treasuries_movementID = $folioIngreso;
            $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d H:i:s');
            $tesoreria->treasuries_concept = $folioAfectar->accountsReceivable_concept;
            $tesoreria->treasuries_money = $folioAfectar->accountsReceivable_money;
            $tesoreria->treasuries_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $tesoreria->treasuries_moneyAccount = $folioAfectar->accountsReceivable_moneyAccount;
            $tesoreria->treasuries_moneyAccountOrigin = $folioAfectar->accountsReceivable_moneyAccount;
            $tesoreria->treasuries_paymentMethod = $folioAfectar->accountsReceivable_formPayment;
            $tesoreria->treasuries_beneficiary = $folioAfectar->accountsReceivable_customer;
            $tesoreria->treasuries_reference = $folioAfectar->accountsReceivable_reference;
            $tesoreria->treasuries_observations = $folioAfectar->accountsReceivable_observations;
            $tesoreria->treasuries_amount = $folioAfectar->accountsReceivable_amount;
            $tesoreria->treasuries_taxes = $folioAfectar->accountsReceivable_taxes;
            $tesoreria->treasuries_total = $folioAfectar->accountsReceivable_total;
            $tesoreria->treasuries_accountBalance = $folioAfectar->accountsReceivable_total;
            $tesoreria->treasuries_company = $folioAfectar->accountsReceivable_company;
            $tesoreria->treasuries_branchOffice = $folioAfectar->accountsReceivable_branchOffice;
            $tesoreria->treasuries_user = $folioAfectar->accountsReceivable_user;
            $tesoreria->treasuries_status = $this->estatus[1];
            $tesoreria->treasuries_originType = 'CxC';
            $tesoreria->treasuries_origin = 'Anticipo Clientes';
            $tesoreria->treasuries_originID = $originID;
            $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $tesoreria->save();
            $lastId = isset($tesoreria->treasuries_id) ? $tesoreria->treasuries_id : PROC_TREASURY::latest('treasuries_id')->first()->treasuries_id;
            //Guardamos la información en la tabla PROC_TREASORIES_DETAILS
            $detalle = new PROC_TREASURY_DETAILS();
            $detalle->treasuriesDetails_treasuriesID = $tesoreria->treasuries_id;
            $detalle->treasuriesDetails_amount = $folioAfectar->accountsReceivable_total;
            $detalle->treasuriesDetails_apply = $folioAfectar->accountsReceivable_movement;
            $detalle->treasuriesDetails_applyIncrement = $originID;
            $detalle->treasuriesDetails_paymentMethod = $folioAfectar->accountsReceivable_formPayment;
            $detalle->treasuriesDetails_movReference = $idOrigen;
            $detalle->save();

            $cuentaOrigen = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', $folio)->first();
            $cuentaOrigen->accountsReceivable_originID = $tesoreria->treasuries_movementID;
            $cuentaOrigen->update();

            if ($cuentaOrigen->accountsReceivable_status == $this->estatus[1] && $cuentaOrigen->accountsReceivable_movement == 'Solicitud Depósito') {
                $movimiento = new PROC_MOVEMENT_FLOW();

                $movimiento->movementFlow_branch = $folioAfectar->accountsReceivable_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->accountsReceivable_company;
                $movimiento->movementFlow_moduleOrigin = 'Din';
                $movimiento->movementFlow_originID = $tesoreria->treasuries_id;
                $movimiento->movementFlow_movementOrigin = $tesoreria->treasuries_movement;
                $movimiento->movementFlow_movementOriginID =   $tesoreria->treasuries_movementID;
                $movimiento->movementFlow_moduleDestiny = 'CxC';
                $movimiento->movementFlow_destinityID = $folioAfectar->accountsReceivable_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->accountsReceivable_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->accountsReceivable_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $create = $movimiento->save();
            }

            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por cobrar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por cobrar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }


            return $lastId;
        }

        if ($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Sol. de Cheque/Transferencia') {

            //agregamos a tesoreria
            $tesoreria = new PROC_TREASURY();
            $folioIngreso = PROC_TREASURY::where('treasuries_movement', '=', 'Sol. de Cheque/Transferencia')->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->max('treasuries_movementID');
            $folioIngreso = $folioIngreso == null ? 1 : $folioIngreso + 1;
            $tesoreria->treasuries_movement = 'Sol. de Cheque/Transferencia';
            $tesoreria->treasuries_movementID = $folioIngreso;
            $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d H:i:s');
            $tesoreria->treasuries_concept = $folioAfectar->accountsReceivable_concept;
            $tesoreria->treasuries_money = $folioAfectar->accountsReceivable_money;
            $tesoreria->treasuries_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $tesoreria->treasuries_moneyAccount = $folioAfectar->accountsReceivable_moneyAccount;
            $tesoreria->treasuries_moneyAccountOrigin = $folioAfectar->accountsReceivable_moneyAccount;
            $tesoreria->treasuries_paymentMethod = $folioAfectar->accountsReceivable_formPayment;
            $tesoreria->treasuries_beneficiary = $folioAfectar->accountsReceivable_customer;
            $tesoreria->treasuries_reference = 'Devolución de Anticipo';
            $tesoreria->treasuries_observations = $folioAfectar->accountsReceivable_observations;
            $tesoreria->treasuries_amount = $folioAfectar->accountsReceivable_amount;
            $tesoreria->treasuries_taxes = $folioAfectar->accountsReceivable_taxes;
            $tesoreria->treasuries_total = $folioAfectar->accountsReceivable_total;
            $tesoreria->treasuries_accountBalance = $folioAfectar->accountsReceivable_total;
            $tesoreria->treasuries_company = $folioAfectar->accountsReceivable_company;
            $tesoreria->treasuries_branchOffice = $folioAfectar->accountsReceivable_branchOffice;
            $tesoreria->treasuries_user = $folioAfectar->accountsReceivable_user;
            $tesoreria->treasuries_status = $this->estatus[1];
            $tesoreria->treasuries_originType = 'CxC';
            $tesoreria->treasuries_origin = 'Devolución de Anticipo';
            $tesoreria->treasuries_originID = $originID;
            $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $tesoreria->save();
            $lastId = isset($tesoreria->treasuries_id) ? $tesoreria->treasuries_id : PROC_TREASURY::latest('treasuries_id')->first()->treasuries_id;
            //Guardamos la información en la tabla PROC_TREASORIES_DETAILS
            $detalle = new PROC_TREASURY_DETAILS();
            $detalle->treasuriesDetails_treasuriesID = $tesoreria->treasuries_id;
            $detalle->treasuriesDetails_amount = $folioAfectar->accountsReceivable_total;
            $detalle->treasuriesDetails_apply = $folioAfectar->accountsReceivable_movement;
            $detalle->treasuriesDetails_applyIncrement = $originID;
            $detalle->treasuriesDetails_paymentMethod = $folioAfectar->accountsReceivable_formPayment;
            $detalle->treasuriesDetails_movReference = $idOrigen;
            $detalle->save();

            $cuentaOrigen = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', $folio)->first();
            $cuentaOrigen->accountsReceivable_originID = $tesoreria->treasuries_movementID;
            $cuentaOrigen->update();

            if ($cuentaOrigen->accountsReceivable_status == $this->estatus[1] && $cuentaOrigen->accountsReceivable_movement == 'Sol. de Cheque/Transferencia') {
                $movimiento = new PROC_MOVEMENT_FLOW();

                $movimiento->movementFlow_branch = $folioAfectar->accountsReceivable_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->accountsReceivable_company;
                $movimiento->movementFlow_moduleOrigin = 'Din';
                $movimiento->movementFlow_originID = $tesoreria->treasuries_id;
                $movimiento->movementFlow_movementOrigin = $tesoreria->treasuries_movement;
                $movimiento->movementFlow_movementOriginID =   $tesoreria->treasuries_movementID;
                $movimiento->movementFlow_moduleDestiny = 'CxC';
                $movimiento->movementFlow_destinityID = $folioAfectar->accountsReceivable_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->accountsReceivable_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->accountsReceivable_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $create = $movimiento->save();
            }

            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por cobrar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por cobrar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }


            return $lastId;
        }
    }

    public function agregarChequeTesoreria2($folio, $originID, $idOrigen)
    {
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', $folio)->first();
        // dd($folioAfectar);
        if ($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Solicitud Depósito') {

            //agregamos a tesoreria
            $tesoreria = new PROC_TREASURY();
            $folioIngreso = PROC_TREASURY::where('treasuries_movement', '=', 'Solicitud Depósito')->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->max('treasuries_movementID');
            $folioIngreso = $folioIngreso == null ? 1 : $folioIngreso + 1;
            $tesoreria->treasuries_movement = 'Solicitud Depósito';
            $tesoreria->treasuries_movementID = $folioIngreso;
            $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d H:i:s');
            $tesoreria->treasuries_concept = $folioAfectar->accountsReceivable_concept;
            $tesoreria->treasuries_money = $folioAfectar->accountsReceivable_money;
            $tesoreria->treasuries_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $tesoreria->treasuries_moneyAccount = $folioAfectar->accountsReceivable_moneyAccount;
            $tesoreria->treasuries_moneyAccountOrigin = $folioAfectar->accountsReceivable_moneyAccount;
            $tesoreria->treasuries_paymentMethod = $folioAfectar->accountsReceivable_formPayment;
            $tesoreria->treasuries_beneficiary = $folioAfectar->accountsReceivable_customer;
            $tesoreria->treasuries_reference = $folioAfectar->accountsReceivable_reference;
            $tesoreria->treasuries_amount = $folioAfectar->accountsReceivable_amount;
            $tesoreria->treasuries_taxes = $folioAfectar->accountsReceivable_taxes;
            $tesoreria->treasuries_total = $folioAfectar->accountsReceivable_total;
            $tesoreria->treasuries_accountBalance = $folioAfectar->accountsReceivable_total;
            $tesoreria->treasuries_company = $folioAfectar->accountsReceivable_company;
            $tesoreria->treasuries_branchOffice = $folioAfectar->accountsReceivable_branchOffice;
            $tesoreria->treasuries_user = $folioAfectar->accountsReceivable_user;
            $tesoreria->treasuries_status = $this->estatus[1];
            $tesoreria->treasuries_originType = 'CxC';
            $tesoreria->treasuries_origin = 'Cobro de Facturas';
            $tesoreria->treasuries_originID = $originID;
            $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $tesoreria->save();
            $lastId = isset($tesoreria->treasuries_id) ? $tesoreria->treasuries_id : PROC_TREASURY::latest('treasuries_id')->first()->treasuries_id;

            //Guardamos la información en la tabla PROC_TREASORIES_DETAILS
            $detalle = new PROC_TREASURY_DETAILS();
            $detalle->treasuriesDetails_treasuriesID = $tesoreria->treasuries_id;
            $detalle->treasuriesDetails_amount = $folioAfectar->accountsReceivable_total;
            $detalle->treasuriesDetails_apply = $folioAfectar->accountsReceivable_movement;
            $detalle->treasuriesDetails_applyIncrement = $originID;
            $detalle->treasuriesDetails_paymentMethod = $folioAfectar->accountsReceivable_formPayment;
            $detalle->treasuriesDetails_movReference = $idOrigen;
            $detalle->save();

            $cuentaOrigen = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', $folio)->first();
            $cuentaOrigen->accountsReceivable_originID = $tesoreria->treasuries_movementID;
            $cuentaOrigen->update();


            if ($cuentaOrigen->accountsReceivable_status == $this->estatus[1] && $cuentaOrigen->accountsReceivable_movement == 'Solicitud Depósito') {
                $movimiento = new PROC_MOVEMENT_FLOW();

                $movimiento->movementFlow_branch = $folioAfectar->accountsReceivable_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->accountsReceivable_company;
                $movimiento->movementFlow_moduleOrigin = 'Din';
                $movimiento->movementFlow_originID = $tesoreria->treasuries_id;
                $movimiento->movementFlow_movementOrigin = $tesoreria->treasuries_movement;
                $movimiento->movementFlow_movementOriginID =   $tesoreria->treasuries_movementID;
                $movimiento->movementFlow_moduleDestiny = 'CxC';
                $movimiento->movementFlow_destinityID = $folioAfectar->accountsReceivable_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->accountsReceivable_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->accountsReceivable_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $create = $movimiento->save();
            }

            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por cobrar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por cobrar';
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
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', $folio)->first();
        if ($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Anticipo Clientes') {
            $proveedor_saldo = PROC_BALANCE::WHERE('balance_account', $folioAfectar->accountsReceivable_customer)->WHERE('balance_companieKey', $folioAfectar->accountsReceivable_company)->WHERE('balance_branchKey', $folioAfectar->accountsReceivable_branchOffice)->where('balance_branch', '=', 'CxC')->where('balance_money', '=', $folioAfectar->accountsReceivable_money)->first();
            if (!$proveedor_saldo) {
                $saldoCuenta = new PROC_BALANCE;
                $saldoCuenta->balance_companieKey = $folioAfectar->accountsReceivable_company;
                $saldoCuenta->balance_money = $folioAfectar->accountsReceivable_money;
                $saldoCuenta->balance_branchKey = $folioAfectar->accountsReceivable_branchOffice;
                $saldoCuenta->balance_balance = 0;
                $saldoCuenta->balance_reconcile = 0;
                $saldoCuenta->balance_branch = "CxC";
                $saldoCuenta->balance_account = $folioAfectar->accountsReceivable_customer;
                $saldoCuenta->save();
            }
        }
    }
    public function actualizarSaldo($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', $folio)->first();


        if ($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Anticipo Clientes') {
            //buscamos cuenta
            $validarCuenta = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', $folioAfectar->accountsReceivable_moneyAccount)->where('moneyAccountsBalance_status', 'Alta')->where('moneyAccountsBalance_money', $folioAfectar->accountsReceivable_money)->where('moneyAccountsBalance_company', $folioAfectar->accountsReceivable_company)->first();
            $saldo = $validarCuenta->moneyAccountsBalance_balance + $folioAfectar->accountsReceivable_total;
            $validarCuenta->moneyAccountsBalance_balance = $saldo;
            $validarCuenta->update();

            $proveedor_saldo = PROC_BALANCE::WHERE('balance_account', $folioAfectar->accountsReceivable_customer)->WHERE('balance_companieKey', $folioAfectar->accountsReceivable_company)->WHERE('balance_branchKey', $folioAfectar->accountsReceivable_branchOffice)->where('balance_branch', '=', 'CxC')->where('balance_money', '=', $folioAfectar->accountsReceivable_money)->first();

            $cuenta_saldo = PROC_BALANCE::WHERE('balance_account', $folioAfectar->accountsReceivable_moneyAccount)->WHERE('balance_companieKey', $folioAfectar->accountsReceivable_company)->WHERE('balance_branchKey', $folioAfectar->accountsReceivable_branchOffice)->where('balance_branch', '=', 'Din')->where('balance_money', '=', $folioAfectar->accountsReceivable_money)->first();

            $ingreso = floatval($folioAfectar->accountsReceivable_total);

            if ($proveedor_saldo) {
                $saldoActual =  $proveedor_saldo->balance_balance === Null ? 0 : floatval($proveedor_saldo->balance_balance);
                $proveedor_saldo->balance_balance = $saldoActual - $ingreso;
                $proveedor_saldo->balance_reconcile = $proveedor_saldo->balance_balance;
                $proveedor_saldo->update();
            } else {
                $saldoCuenta = new PROC_BALANCE;
                $saldoCuenta->balance_companieKey = $folioAfectar->accountsReceivable_company;
                $saldoCuenta->balance_money = $folioAfectar->accountsReceivable_money;
                $saldoCuenta->balance_branchKey = $folioAfectar->accountsReceivable_branchOffice;
                $saldoCuenta->balance_balance = 0 - floatval($folioAfectar->accountsReceivable_total);
                $saldoCuenta->balance_reconcile = $saldoCuenta->balance_balance;
                $saldoCuenta->balance_branch = "CxC";
                $saldoCuenta->balance_account = $folioAfectar->accountsReceivable_customer;
                $saldoCuenta->save();
            }

            if ($cuenta_saldo) {
                $saldoActual  =   $cuenta_saldo->balance_balance === Null ? 0 : floatval($cuenta_saldo->balance_balance);
                $cuenta_saldo->balance_balance = $saldoActual + $ingreso;
                $cuenta_saldo->balance_reconcile = $cuenta_saldo->balance_balance;
                $cuenta_saldo->update();
            } else {
                $cuenta_saldo = new PROC_BALANCE;
                $cuenta_saldo->balance_companieKey = $folioAfectar->accountsReceivable_company;
                $cuenta_saldo->balance_money = $folioAfectar->accountsReceivable_money;
                $cuenta_saldo->balance_branchKey = $folioAfectar->accountsReceivable_branchOffice;
                $cuenta_saldo->balance_balance = 0 + floatval($folioAfectar->accountsReceivable_total);
                $cuenta_saldo->balance_reconcile = 0 + floatval($folioAfectar->accountsReceivable_total);
                $cuenta_saldo->balance_branch = "Din";
                $cuenta_saldo->balance_account = $folioAfectar->accountsReceivable_moneyAccount;
                $cuenta_saldo->save();
            }
        }


        if ($folioAfectar->accountsReceivable_status == $this->estatus[2] && $folioAfectar->accountsReceivable_movement == 'Cobro de Facturas') {

            //buscamos cuenta
            $validarCuenta = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', $folioAfectar->accountsReceivable_moneyAccount)->where('moneyAccountsBalance_status', 'Alta')->where('moneyAccountsBalance_money', $folioAfectar->accountsReceivable_money)->where('moneyAccountsBalance_company', $folioAfectar->accountsReceivable_company)->first();
            $saldo = $validarCuenta->moneyAccountsBalance_balance + $folioAfectar->accountsReceivable_total;
            $validarCuenta->moneyAccountsBalance_balance = $saldo;
            $validarCuenta->update();


            $validarSaldo = PROC_BALANCE::where('balance_account', $folioAfectar->accountsReceivable_moneyAccount)->where('balance_branchKey', $folioAfectar->accountsReceivable_branchOffice)->where('balance_branch', '=', 'Din')->where('balance_money', '=', $folioAfectar->accountsReceivable_money)->first();
            if ($validarSaldo) {
                $saldo = $validarSaldo->balance_balance + $folioAfectar->accountsReceivable_total;
                $validarSaldo->balance_balance = $saldo;
                $validarSaldo->balance_reconcile = $validarSaldo->balance_balance;
                $validarSaldo->update();
            } else {
                $cuenta_saldo = new PROC_BALANCE;
                $cuenta_saldo->balance_companieKey = $folioAfectar->accountsReceivable_company;
                $cuenta_saldo->balance_money = $folioAfectar->accountsReceivable_money;
                $cuenta_saldo->balance_branchKey = $folioAfectar->accountsReceivable_branchOffice;
                $cuenta_saldo->balance_balance = 0 + floatval($folioAfectar->accountsReceivable_total);
                $cuenta_saldo->balance_reconcile =  $cuenta_saldo->balance_balance;
                $cuenta_saldo->balance_branch = "Din";
                $cuenta_saldo->balance_account = $folioAfectar->accountsReceivable_moneyAccount;
                $cuenta_saldo->save();
            }

            $validarSaldo2 = PROC_BALANCE::where('balance_account', $folioAfectar->accountsReceivable_customer)->where('balance_branchKey', $folioAfectar->accountsReceivable_branchOffice)->where('balance_branch', '=', 'CxC')->where('balance_money', '=', $folioAfectar->accountsReceivable_money)->first();
            if ($validarSaldo2) {
                $saldo = $validarSaldo2->balance_balance - $folioAfectar->accountsReceivable_total;
                $validarSaldo2->balance_balance = $saldo;
                $validarSaldo2->balance_reconcile = $validarSaldo2->balance_balance;
                $validarSaldo2->update();
            }
        }

        if ($folioAfectar->accountsReceivable_status == $this->estatus[2] && $folioAfectar->accountsReceivable_movement == 'Devolución de Anticipo') {
            //buscamos cuenta
            $validarCuenta = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', $folioAfectar->accountsReceivable_moneyAccount)->where('moneyAccountsBalance_status', 'Alta')->where('moneyAccountsBalance_money', $folioAfectar->accountsReceivable_money)->where('moneyAccountsBalance_company', $folioAfectar->accountsReceivable_company)->first();
            $saldo = $validarCuenta->moneyAccountsBalance_balance - $folioAfectar->accountsReceivable_total;
            $validarCuenta->moneyAccountsBalance_balance = $saldo;
            $validarCuenta->update();

            $proveedor_saldo = PROC_BALANCE::WHERE('balance_account', $folioAfectar->accountsReceivable_customer)->WHERE('balance_companieKey', $folioAfectar->accountsReceivable_company)->WHERE('balance_branchKey', $folioAfectar->accountsReceivable_branchOffice)->where('balance_branch', '=', 'CxC')->where('balance_money', '=', $folioAfectar->accountsReceivable_money)->first();

            $cuenta_saldo = PROC_BALANCE::WHERE('balance_account', $folioAfectar->accountsReceivable_moneyAccount)->WHERE('balance_companieKey', $folioAfectar->accountsReceivable_company)->WHERE('balance_branchKey', $folioAfectar->accountsReceivable_branchOffice)->where('balance_branch', '=', 'Din')->where('balance_money', '=', $folioAfectar->accountsReceivable_money)->first();

            $ingreso = floatval($folioAfectar->accountsReceivable_total);

            if ($proveedor_saldo) {
                $saldoActual =  $proveedor_saldo->balance_balance === Null ? 0 : floatval($proveedor_saldo->balance_balance);
                $proveedor_saldo->balance_balance = $saldoActual + $ingreso;
                $proveedor_saldo->balance_reconcile = $proveedor_saldo->balance_balance;
                $proveedor_saldo->update();
            } else {
                $saldoCuenta = new PROC_BALANCE;
                $saldoCuenta->balance_companieKey = $folioAfectar->accountsReceivable_company;
                $saldoCuenta->balance_money = $folioAfectar->accountsReceivable_money;
                $saldoCuenta->balance_branchKey = $folioAfectar->accountsReceivable_branchOffice;
                $saldoCuenta->balance_balance = 0 + floatval($folioAfectar->accountsReceivable_total);
                $saldoCuenta->balance_reconcile = $saldoCuenta->balance_balance;
                $saldoCuenta->balance_branch = "CxC";
                $saldoCuenta->balance_account = $folioAfectar->accountsReceivable_customer;
                $saldoCuenta->save();
            }

            if ($cuenta_saldo) {
                $saldoActual  =   $cuenta_saldo->balance_balance === Null ? 0 : floatval($cuenta_saldo->balance_balance);
                $cuenta_saldo->balance_balance = $saldoActual - $ingreso;
                $cuenta_saldo->balance_reconcile = $cuenta_saldo->balance_balance;
                $cuenta_saldo->update();
            } else {
                $cuenta_saldo = new PROC_BALANCE;
                $cuenta_saldo->balance_companieKey = $folioAfectar->accountsReceivable_company;
                $cuenta_saldo->balance_money = $folioAfectar->accountsReceivable_money;
                $cuenta_saldo->balance_branchKey = $folioAfectar->accountsReceivable_branchOffice;
                $cuenta_saldo->balance_balance = 0 - floatval($folioAfectar->accountsReceivable_total);
                $cuenta_saldo->balance_reconcile = 0 - floatval($folioAfectar->accountsReceivable_total);
                $cuenta_saldo->balance_branch = "Din";
                $cuenta_saldo->balance_account = $folioAfectar->accountsReceivable_moneyAccount;
                $cuenta_saldo->save();
            }
        }
    }



    public function generarSolitudCheque($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', $folio)->first();
        // dd($folioAfectar);
        if ($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Anticipo Clientes') {

            //agregamos a tesoreria
            $tesoreria = new PROC_TREASURY();
            $folioCheque = PROC_TREASURY::where('treasuries_movement', '=', 'Solicitud Depósito')->where('treasuries_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->max('treasuries_movementID');
            $folioCheque = $folioCheque == null ? 1 : $folioCheque + 1;
            $tesoreria->treasuries_movement = 'Solicitud Depósito';
            $tesoreria->treasuries_movementID = $folioCheque;
            $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d H:i:s');
            $tesoreria->treasuries_money = $folioAfectar->accountsReceivable_money;
            $tesoreria->treasuries_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $tesoreria->treasuries_moneyAccount = $folioAfectar->accountsReceivable_moneyAccount;
            $tesoreria->treasuries_moneyAccountOrigin = $folioAfectar->accountsReceivable_moneyAccount;
            $tesoreria->treasuries_paymentMethod = $folioAfectar->accountsReceivable_paymentMethod;
            $tesoreria->treasuries_beneficiary = $folioAfectar->accountsReceivable_customer;
            $tesoreria->treasuries_reference = $folioAfectar->accountsReceivable_reference;
            $tesoreria->treasuries_amount = $folioAfectar->accountsReceivable_amount;
            $tesoreria->treasuries_taxes = $folioAfectar->accountsReceivable_taxes;
            $tesoreria->treasuries_total = $folioAfectar->accountsReceivable_total;
            $tesoreria->treasuries_company = $folioAfectar->accountsReceivable_company;
            $tesoreria->treasuries_branchOffice = $folioAfectar->accountsReceivable_branchOffice;
            $tesoreria->treasuries_user = $folioAfectar->accountsReceivable_user;
            $tesoreria->treasuries_status = $this->estatus[1];
            $tesoreria->treasuries_originType = 'CxC';
            $tesoreria->treasuries_origin = $folioAfectar->accountsReceivable_movement;
            $tesoreria->treasuries_originID = $folioAfectar->accountsReceivable_movementID;
            $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $tesoreria->save();

            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por cobrar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por cobrar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }


            return response()->json(['message' => $message, 'estatus' => $estatus]);
        }
    }

    public function agregarCxCCheque($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $folio)->first();

        //   dd($folioAfectar);
        if ($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Anticipo Clientes') {
            // dd($folioAfectar);
            $folioCheque = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', 'Solicitud Depósito')->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->max('accountsReceivable_movementID');
            $folioCheque = $folioCheque == null ? 1 : $folioCheque + 1;

            $cuentaPagar = new PROC_ACCOUNTS_RECEIVABLE();
            $cuentaPagar->accountsReceivable_movement = 'Solicitud Depósito';
            $cuentaPagar->accountsReceivable_movementID = $folioCheque;
            $cuentaPagar->accountsReceivable_issuedate = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d H:i:s');
            $cuentaPagar->accountsReceivable_money = $folioAfectar->accountsReceivable_money;
            $cuentaPagar->accountsReceivable_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $cuentaPagar->accountsReceivable_moneyAccount = $folioAfectar->accountsReceivable_moneyAccount;
            $cuentaPagar->accountsReceivable_customer = $folioAfectar->accountsReceivable_customer;
            $cuentaPagar->accountsReceivable_condition = $folioAfectar->accountsReceivable_condition;
            $cuentaPagar->accountsReceivable_formPayment = $folioAfectar->accountsReceivable_formPayment;
            $cuentaPagar->accountsReceivable_amount = $folioAfectar->accountsReceivable_amount;
            $cuentaPagar->accountsReceivable_taxes = $folioAfectar->accountsReceivable_taxes;
            $cuentaPagar->accountsReceivable_total = $folioAfectar->accountsReceivable_total;
            $cuentaPagar->accountsReceivable_concept = $folioAfectar->accountsReceivable_concept;
            $cuentaPagar->accountsReceivable_reference = $folioAfectar->accountsReceivable_reference;
            $cuentaPagar->accountsReceivable_observations = $folioAfectar->accountsReceivable_observations;
            $cuentaPagar->accountsReceivable_balance = $folioAfectar->accountsReceivable_total;
            $cuentaPagar->accountsReceivable_company = $folioAfectar->accountsReceivable_company;
            $cuentaPagar->accountsReceivable_branchOffice = $folioAfectar->accountsReceivable_branchOffice;
            $cuentaPagar->accountsReceivable_user = $folioAfectar->accountsReceivable_user;
            $cuentaPagar->accountsReceivable_status = $this->estatus[1];
            $cuentaPagar->accountsReceivable_origin =  $cuentaPagar->accountsReceivable_movement;
            $cuentaPagar->accountsReceivable_originID = $cuentaPagar->accountsReceivable_movementID;
            $cuentaPagar->accountsReceivable_originType = 'Din';
            $cuentaPagar->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $cuentaPagar->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $cuentaPagar->save();

            $lastId = isset($cuentaPagar->accountsReceivable_id) ? $cuentaPagar->accountsReceivable_id : PROC_ACCOUNTS_RECEIVABLE::latest('accountsReceivable_id')->first()->accountsReceivable_id;



            //Colocamos el cheque a la tabla CXPP
            $this->agregarCxCPCheque($lastId);
            //Colocamos la solicitud de cheque a la tabla Treasuries (Tesoreria)
            $deposito = $this->agregarChequeTesoreria($lastId, $folioAfectar->accountsReceivable_movementID, $folioAfectar->accountsReceivable_id);
            //Agremos a la tabla ASSISTANT (Auxiliar)
            $this->agregarAuxiliarCheque($lastId);
            $this->agregarMov($lastId);



            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por cobrar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por cobrar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }

            return $deposito;
        }

        if ($folioAfectar->accountsReceivable_status == $this->estatus[2] && $folioAfectar->accountsReceivable_movement == 'Cobro de Facturas') {

            $folioCheque = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', 'Solicitud Depósito')->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->max('accountsReceivable_movementID');
            $folioCheque = $folioCheque == null ? 1 : $folioCheque + 1;

            $cuentaPagar = new PROC_ACCOUNTS_RECEIVABLE();
            $cuentaPagar->accountsReceivable_movement = 'Solicitud Depósito';
            $cuentaPagar->accountsReceivable_movementID = $folioCheque;
            $cuentaPagar->accountsReceivable_issuedate = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d H:i:s');
            $cuentaPagar->accountsReceivable_money = $folioAfectar->accountsReceivable_money;
            $cuentaPagar->accountsReceivable_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $cuentaPagar->accountsReceivable_moneyAccount = $folioAfectar->accountsReceivable_moneyAccount;
            $cuentaPagar->accountsReceivable_customer = $folioAfectar->accountsReceivable_customer;
            $cuentaPagar->accountsReceivable_condition = $folioAfectar->accountsReceivable_condition;
            $vencimiento = $folioAfectar->accountsReceivable_expiration;
            if ($vencimiento != null) {
                $vencimiento2 = Carbon::parse($vencimiento)->format('Y-m-d H:i:s');
            } else {
                $vencimiento2 = null;
            }
            $cuentaPagar->accountsReceivable_expiration = $vencimiento2;
            $cuentaPagar->accountsReceivable_formPayment = $folioAfectar->accountsReceivable_formPayment;
            $cuentaPagar->accountsReceivable_amount = $folioAfectar->accountsReceivable_amount;
            $cuentaPagar->accountsReceivable_taxes = $folioAfectar->accountsReceivable_taxes;
            $cuentaPagar->accountsReceivable_total = $folioAfectar->accountsReceivable_total;
            $cuentaPagar->accountsReceivable_concept = $folioAfectar->accountsReceivable_concept;
            $cuentaPagar->accountsReceivable_reference = $folioAfectar->accountsReceivable_reference;
            $cuentaPagar->accountsReceivable_balance = $folioAfectar->accountsReceivable_total;
            $cuentaPagar->accountsReceivable_company = $folioAfectar->accountsReceivable_company;
            $cuentaPagar->accountsReceivable_branchOffice = $folioAfectar->accountsReceivable_branchOffice;
            $cuentaPagar->accountsReceivable_user = $folioAfectar->accountsReceivable_user;
            $cuentaPagar->accountsReceivable_status = $this->estatus[1];
            $cuentaPagar->accountsReceivable_origin =  $cuentaPagar->accountsReceivable_movement;
            $cuentaPagar->accountsReceivable_originID = $cuentaPagar->accountsReceivable_movementID;
            $cuentaPagar->accountsReceivable_originType = 'Din';
            $cuentaPagar->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $cuentaPagar->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $cuentaPagar->save();

            $lastId = isset($cuentaPagar->accountsReceivable_id) ? $cuentaPagar->accountsReceivable_id : PROC_ACCOUNTS_RECEIVABLE::latest('accountsReceivable_id')->first()->accountsReceivable_id;


            //Colocamos el cheque a la tabla CXPP
            $this->agregarCxCPCheque($lastId);
            //Colocamos la solicitud de cheque a la tabla Treasuries (Tesoreria)
            $deposito = $this->agregarChequeTesoreria2($lastId, $folioAfectar->accountsReceivable_movementID, $folioAfectar->accountsReceivable_id);
            //Agremos a la tabla ASSISTANT (Auxiliar)
            $this->agregarAuxiliarCheque($lastId);
            // $this->agregarMov($lastId);

            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por cobrar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por cobrar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }

            return $deposito;
        }

        if ($folioAfectar->accountsReceivable_status == $this->estatus[2] && $folioAfectar->accountsReceivable_movement == 'Devolución de Anticipo') {
            // dd($folioAfectar);
            $folioCheque = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', 'Sol. de Cheque/Transferencia')->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->max('accountsReceivable_movementID');
            $folioCheque = $folioCheque == null ? 1 : $folioCheque + 1;

            $cuentaPagar = new PROC_ACCOUNTS_RECEIVABLE();
            $cuentaPagar->accountsReceivable_movement = 'Sol. de Cheque/Transferencia';
            $cuentaPagar->accountsReceivable_movementID = $folioCheque;
            $cuentaPagar->accountsReceivable_issuedate = Carbon::parse($folioAfectar->accountsReceivable_issuedate)->format('Y-m-d H:i:s');
            $cuentaPagar->accountsReceivable_money = $folioAfectar->accountsReceivable_money;
            $cuentaPagar->accountsReceivable_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $cuentaPagar->accountsReceivable_moneyAccount = $folioAfectar->accountsReceivable_moneyAccount;
            $cuentaPagar->accountsReceivable_customer = $folioAfectar->accountsReceivable_customer;
            $cuentaPagar->accountsReceivable_condition = $folioAfectar->accountsReceivable_condition;
            $cuentaPagar->accountsReceivable_formPayment = $folioAfectar->accountsReceivable_formPayment;
            $cuentaPagar->accountsReceivable_amount = $folioAfectar->accountsReceivable_amount;
            $cuentaPagar->accountsReceivable_taxes = $folioAfectar->accountsReceivable_taxes;
            $cuentaPagar->accountsReceivable_total = $folioAfectar->accountsReceivable_total;
            $cuentaPagar->accountsReceivable_concept = $folioAfectar->accountsReceivable_concept;
            $cuentaPagar->accountsReceivable_reference = "Devolución de Anticipo";
            $cuentaPagar->accountsReceivable_observations = $folioAfectar->accountsReceivable_observations;
            $cuentaPagar->accountsReceivable_balance = $folioAfectar->accountsReceivable_total;
            $cuentaPagar->accountsReceivable_company = $folioAfectar->accountsReceivable_company;
            $cuentaPagar->accountsReceivable_branchOffice = $folioAfectar->accountsReceivable_branchOffice;
            $cuentaPagar->accountsReceivable_user = $folioAfectar->accountsReceivable_user;
            $cuentaPagar->accountsReceivable_status = $this->estatus[1];
            $cuentaPagar->accountsReceivable_origin =  $cuentaPagar->accountsReceivable_movement;
            $cuentaPagar->accountsReceivable_originID = $cuentaPagar->accountsReceivable_movementID;
            $cuentaPagar->accountsReceivable_originType = 'Din';
            $cuentaPagar->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $cuentaPagar->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $create = $cuentaPagar->save();

            $lastId = isset($cuentaPagar->accountsReceivable_id) ? $cuentaPagar->accountsReceivable_id : PROC_ACCOUNTS_RECEIVABLE::latest('accountsReceivable_id')->first()->accountsReceivable_id;



            //Colocamos el cheque a la tabla CXPP
            $this->agregarCxCPCheque($lastId);
            //Colocamos la solicitud de cheque a la tabla Treasuries (Tesoreria)
            $deposito = $this->agregarChequeTesoreria($lastId, $folioAfectar->accountsReceivable_movementID, $folioAfectar->accountsReceivable_id);
            //Agremos a la tabla ASSISTANT (Auxiliar)
            $this->agregarAuxiliarCheque($lastId);
            $this->agregarMov($lastId);



            try {
                if ($create) {
                    $message = 'Se agrego la cuenta por cobrar correctamente';
                    $estatus = true;
                } else {
                    $message = 'No se pudo agregar la cuenta por cobrar';
                    $estatus = false;
                }
            } catch (\Throwable $th) {
                dd($th);
            }

            return $deposito;
        }
    }

    public function agregarAuxiliarCheque($folio)
    {
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $folio)->first();

        // dd($folioAfectar);
        if (($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Solicitud Depósito') || ($folioAfectar->accountsReceivable_status == $this->estatus[2] && $folioAfectar->accountsReceivable_movement == 'Cobro de Facturas') || ($folioAfectar->accountsReceivable_status == $this->estatus[1] && $folioAfectar->accountsReceivable_movement == 'Sol. de Cheque/Transferencia')) {

            //agregar datos a aux
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->accountsReceivable_company;
            $auxiliar->assistant_branchKey = $folioAfectar->accountsReceivable_branchOffice;
            $auxiliar->assistant_branch = 'CxC';


            //buscamos el modulo de cxp
            $cxc = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movementID', '=', $folioAfectar->accountsReceivable_movementID)->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)->where('accountsReceivable_id', '=', $folio)->first();

            $auxiliar->assistant_movement = $cxc->accountsReceivable_movement;
            $auxiliar->assistant_movementID = $cxc->accountsReceivable_movementID;
            $auxiliar->assistant_module = 'CxC';
            $auxiliar->assistant_moduleID = $cxc->accountsReceivable_id;
            $auxiliar->assistant_money = $folioAfectar->accountsReceivable_money;
            $auxiliar->assistant_typeChange = $folioAfectar->accountsReceivable_typeChange;
            $auxiliar->assistant_account = $folioAfectar->accountsReceivable_customer;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = $folioAfectar->accountsReceivable_total;
            $auxiliar->assistant_payment = null;
            $auxiliar->assistant_apply = $cxc->accountsReceivable_movement;
            $auxiliar->assistant_applyID =  $cxc->accountsReceivable_movementID;
            $auxiliar->assistant_canceled = 0;
            if ($folioAfectar->accountsReceivable_movement == 'Solicitud Depósito' || $folioAfectar->accountsReceivable_movement == 'Cobro de Facturas') {
                $auxiliar->assistant_reference = $folioAfectar->accountsReceivable_reference;
            }
            if ($folioAfectar->accountsReceivable_movement == 'Sol. de Cheque/Transferencia') {
                $auxiliar->assistant_reference = 'Devolución de Anticipo';
            }


            $auxiliar->save();
        }
    }

    public function cancelarCxC(Request $request)
    {
        $movimientoCancelar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $request->id)->first();



        if ($movimientoCancelar->accountsReceivable_status == $this->estatus[2] && $movimientoCancelar->accountsReceivable_movement == 'Cobro de Facturas' && $request->tipo == 'Caja') {
            try {
                $pagoCancelado = false;
                $aplicacionCancelado = true;
                $anticipoCancelado = true;
                $devolucionCancelado = true;

                //regresamos el monto a las cuentas por pagar
                $detalle = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', '=', $movimientoCancelar->accountsReceivable_id)->get();

                // dd($detalle);

                foreach ($detalle as $key => $value) {
                    $cuentaPagar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $value->accountsReceivableDetails_movReference)->first();

                    $cuentaPagar->accountsReceivable_balance = $cuentaPagar->accountsReceivable_balance + $value->accountsReceivableDetails_amount;
                    $cuentaPagar->accountsReceivable_status = $this->estatus[1];
                    $validar = $cuentaPagar->update();
                    if ($validar) {
                        $pagoCancelado = true;
                    } else {
                        $pagoCancelado = false;
                    }

                    //buscamos el movimiento anterior al de cxp
                    $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $cuentaPagar->accountsReceivable_id)->where('movementFlow_movementDestinity', '=', $cuentaPagar->accountsReceivable_movement)->where('movementFlow_branch', '=', $cuentaPagar->accountsReceivable_branchOffice)->first();
                    // dd($flujo);
                    //regresamos las entradas a cxpp originario desde compras
                    if ($flujo->movementFlow_moduleOrigin == 'Ventas') {
                        $entrada = PROC_SALES::where('sales_id', '=', $flujo->movementFlow_originID)->first();

                        //verificamos si aun existe la entrada en cuenta por pagar pendiente
                        $cuentaPagarPendiente = PROC_ACCOUNTS_RECEIVABLE_P::where('accountsReceivableP_originType', '=', 'Ventas')->where('accountsReceivableP_origin', '=', $entrada->sales_movement)->where('accountsReceivableP_originID', '=', $entrada->sales_movementID)->where('accountsReceivableP_branchOffice', '=', $entrada->sales_branchOffice)->first();

                        if ($cuentaPagarPendiente === null) {

                            $cuentaPagarP = new PROC_ACCOUNTS_RECEIVABLE_P();
                            $cuentaPagarP->accountsReceivableP_movement = $entrada->sales_movement;
                            $cuentaPagarP->accountsReceivableP_movementID = $entrada->sales_movementID;
                            $cuentaPagarP->accountsReceivableP_issuedate = Carbon::parse($entrada->sales_issuedate)->format('Y-m-d');
                            $cuentaPagarP->accountsReceivableP_expiration =  Carbon::parse($entrada->sales_expiration)->format('Y-m-d');

                            //dias credito y moratorio
                            $emision = Carbon::parse($entrada->sales_issueDate)->format('Y-m-d');
                            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
                            $vencimiento = Carbon::parse($entrada->sales_expiration)->format('Y-m-d');
                            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

                            $diasCredito = $currentDate->diffInDays($shippingDate);
                            $diasMoratorio = $shippingDate->diffInDays($currentDate);


                            $cuentaPagarP->accountsReceivableP_creditDays = $diasCredito;
                            $cuentaPagarP->accountsReceivableP_moratoriumDays = '-' . $diasMoratorio;


                            $cuentaPagarP->accountsReceivableP_money = $entrada->sales_money;
                            $cuentaPagarP->accountsReceivableP_typeChange = $entrada->sales_typeChange;
                            $cuentaPagarP->accountsReceivableP_customer = $entrada->sales_customer;
                            $cuentaPagarP->accountsReceivableP_condition = $entrada->sales_condition;

                            $cuentaPagarP->accountsReceivableP_amount = $entrada->sales_amount;
                            $cuentaPagarP->accountsReceivableP_taxes = $entrada->sales_taxes;
                            $cuentaPagarP->accountsReceivableP_total = $entrada->sales_total;
                            $cuentaPagarP->accountsReceivableP_balanceTotal = $value->accountsReceivableDetails_amount;
                            $cuentaPagarP->accountsReceivableP_concept = $entrada->sales_concept;
                            $cuentaPagarP->accountsReceivableP_reference = $entrada->sales_reference;
                            $cuentaPagarP->accountsReceivableP_balance = $value->accountsReceivableDetails_amount;
                            $cuentaPagarP->accountsReceivableP_company = $entrada->sales_company;
                            $cuentaPagarP->accountsReceivableP_branchOffice = $entrada->sales_branchOffice;
                            $cuentaPagarP->accountsReceivableP_user = $entrada->sales_user;
                            $cuentaPagarP->accountsReceivableP_status = $this->estatus[1];
                            $cuentaPagarP->accountsReceivableP_origin = $entrada->sales_movement;
                            $cuentaPagarP->accountsReceivableP_originID = $entrada->sales_movementID;
                            $cuentaPagarP->accountsReceivableP_originType =  'Ventas';

                            $validar2 = $cuentaPagarP->save();
                            if ($validar2) {
                                $pagoCancelado = true;
                            } else {
                                $pagoCancelado = false;
                            }
                        } else {
                            $cuentaPagarPendiente->accountsReceivableP_balance =   $cuentaPagarPendiente->accountsReceivableP_balance + $value->accountsReceivableDetails_amount;
                            $cuentaPagarPendiente->accountsReceivableP_balanceTotal =   $cuentaPagarPendiente->accountsReceivableP_balanceTotal + $value->accountsReceivableDetails_amount;
                            $cuentaPagarPendiente->update();
                        }
                    }
                }

                //regresamos el saldo a la cuenta donde se genero el movimiento
                $cuenta = PROC_BALANCE::where('balance_account', '=', $movimientoCancelar->accountsReceivable_moneyAccount)->where('balance_branchKey', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();

                $cuenta->balance_balance = $cuenta->balance_balance - $movimientoCancelar->accountsReceivable_total;
                $cuenta->balance_reconcile = $cuenta->balance_balance;
                $validar3 = $cuenta->update();
                if ($validar3) {
                    $pagoCancelado = true;
                    $this->actualizarSaldosCuentasDinero($movimientoCancelar->accountsReceivable_id, $movimientoCancelar->accountsReceivable_money);
                } else {
                    $pagoCancelado = false;
                }

                $cuenta2 = PROC_BALANCE::where('balance_account', '=', $movimientoCancelar->accountsReceivable_customer)->where('balance_branchKey', '=', $movimientoCancelar->accountsReceivable_branchOffice)->where('balance_branch', '=', 'CxC')->where('balance_money', '=', $movimientoCancelar->accountsReceivable_money)->first();

                $cuenta2->balance_balance = $cuenta2->balance_balance + $movimientoCancelar->accountsReceivable_total;
                $cuenta2->balance_reconcile = $cuenta2->balance_balance;
                $validar4 = $cuenta2->update();
                if ($validar4) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }

                //Cancelamos egreso o solicitud de cheque en tesoreria
                $tesoreria = PROC_TREASURY::where('treasuries_origin', '=', $movimientoCancelar->accountsReceivable_movement)->where('treasuries_originID', '=', $movimientoCancelar->accountsReceivable_movementID)->where('treasuries_branchOffice', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();

                $tesoreria->treasuries_status = $this->estatus[3];
                $validar5 = $tesoreria->update();
                if ($validar5) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }


                //agregamos auxiliar de cancelacion de egreso o solicitud de cheque en tesoreria

                $auxiliar2 = new PROC_ASSISTANT();

                $auxiliar2->assistant_companieKey = $movimientoCancelar->accountsReceivable_company;
                $auxiliar2->assistant_branchKey = $movimientoCancelar->accountsReceivable_branchOffice;
                $auxiliar2->assistant_branch = 'CxC';

                $auxiliar2->assistant_movement = $movimientoCancelar->accountsReceivable_movement;
                $auxiliar2->assistant_movementID = $movimientoCancelar->accountsReceivable_movementID;
                $auxiliar2->assistant_module = 'CxC';
                $auxiliar2->assistant_moduleID = $movimientoCancelar->accountsReceivable_id;
                $auxiliar2->assistant_money = $movimientoCancelar->accountsReceivable_money;
                $auxiliar2->assistant_typeChange = $movimientoCancelar->accountsReceivable_typeChange;
                $auxiliar2->assistant_account = $movimientoCancelar->accountsReceivable_customer;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar2->assistant_year = $year;
                $auxiliar2->assistant_period = $period;
                $auxiliar2->assistant_charge = null;
                $auxiliar2->assistant_payment = '-' . $movimientoCancelar->accountsReceivable_total;
                $auxiliar2->assistant_apply = $movimientoCancelar->accountsReceivable_origin;
                $auxiliar2->assistant_applyID =  $movimientoCancelar->accountsReceivable_originID;
                $auxiliar2->assistant_canceled = 1;
                $auxiliar2->assistant_reference = $movimientoCancelar->accountsReceivable_reference;
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
                $auxiliar->assistant_charge = '-' . $tesoreria->treasuries_total;
                $auxiliar->assistant_payment = null;
                $auxiliar->assistant_apply = $tesoreria->treasuries_movement;
                $auxiliar->assistant_applyID = $tesoreria->treasuries_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $tesoreria->treasuries_reference;

                $validar7 = $auxiliar->save();
                if ($validar7) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }
                //cancelamos los movimientos
                $movimiento = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $movimientoCancelar->accountsReceivable_id)->where('movementFlow_movementDestinity', '=', $movimientoCancelar->accountsReceivable_movement)->where('movementFlow_branch', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();
                $movimiento->movementFlow_cancelled = 1;
                $validar8 = $movimiento->update();
                if ($validar8) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }

                $movimiento2 = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $movimientoCancelar->accountsReceivable_id)->where('movementFlow_movementOrigin', '=', $movimientoCancelar->accountsReceivable_movement)->where('movementFlow_branch', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();
                $movimiento2->movementFlow_cancelled = 1;
                $validar9 = $movimiento2->update();
                if ($validar9) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }

                //cancelamos el folio
                $movimientoCancelar->accountsReceivable_status = $this->estatus[3];
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

        if ($movimientoCancelar->accountsReceivable_status == $this->estatus[2] && $movimientoCancelar->accountsReceivable_movement == 'Cobro de Facturas' && $request->tipo == 'Banco') {

            try {


                $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $movimientoCancelar->accountsReceivable_id)->where('movementFlow_movementOrigin', '=', $movimientoCancelar->accountsReceivable_movement)->where('movementFlow_moduleDestiny', '=', 'Din')->where('movementFlow_movementDestinity', '=', 'Solicitud Depósito')->where('movementFlow_branch', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();

                //buscamos la solicitud de cheque en tesoreria
                $solicitudCheque = PROC_TREASURY::where('treasuries_id', '=', $flujo->movementFlow_destinityID)->first();


                if ($solicitudCheque->treasuries_status === $this->estatus[2]) {
                    $status = 400;
                    $message = 'No se puede cancelar el cobro, ya que tiene una solicitud de depósito asociada.' . ' Movimiento: ' . $movimientoCancelar->accountsReceivable_movement . ' Folio: ' . $movimientoCancelar->accountsReceivable_movementID;
                    return response()->json(['mensaje' => $message, 'estatus' => $status]);
                }

                $pagoCancelado = false;
                $aplicacionCancelado = true;
                $anticipoCancelado = true;
                $devolucionCancelado = true;
                //regresamos el monto a las cuentas por pagar
                $detalle = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', '=', $movimientoCancelar->accountsReceivable_id)->get();

                foreach ($detalle as $key => $value) {
                    $cuentaPagar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $value->accountsReceivableDetails_movReference)->first();

                    $cuentaPagar->accountsReceivable_balance = $cuentaPagar->accountsReceivable_balance + $value->accountsReceivableDetails_amount;
                    $cuentaPagar->accountsReceivable_status = $this->estatus[1];
                    $validar = $cuentaPagar->update();
                    if ($validar) {
                        $pagoCancelado = true;
                    } else {
                        $pagoCancelado = false;
                    }

                    //buscamos el movimiento anterior al de cxp
                    $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $cuentaPagar->accountsReceivable_id)->where('movementFlow_movementDestinity', '=', $cuentaPagar->accountsReceivable_movement)->where('movementFlow_branch', '=', $cuentaPagar->accountsReceivable_branchOffice)->first();

                    //regresamos las entradas a cxpp originario desde compras
                    if ($flujo->movementFlow_moduleOrigin == 'Ventas') {
                        $entrada = PROC_SALES::where('sales_id', '=', $flujo->movementFlow_originID)->first();

                        //verificamos si aun existe la entrada en cuenta por pagar pendiente
                        $cuentaPagarPendiente = PROC_ACCOUNTS_RECEIVABLE_P::where('accountsReceivableP_originType', '=', 'Ventas')->where('accountsReceivableP_origin', '=', $entrada->sales_movement)->where('accountsReceivableP_originID', '=', $entrada->sales_movementID)->where('accountsReceivableP_branchOffice', '=', $entrada->sales_branchOffice)->first();


                        if ($cuentaPagarPendiente === null) {

                            $cuentaPagarP = new PROC_ACCOUNTS_RECEIVABLE_P();
                            $cuentaPagarP->accountsReceivableP_movement = $entrada->sales_movement;
                            $cuentaPagarP->accountsReceivableP_movementID = $entrada->sales_movementID;
                            $cuentaPagarP->accountsReceivableP_issuedate = Carbon::parse($entrada->sales_issuedate)->format('Y-m-d');
                            $cuentaPagarP->accountsReceivableP_expiration =  Carbon::parse($entrada->sales_expiration)->format('Y-m-d');

                            //dias credito y moratorio
                            $emision = Carbon::parse($entrada->sales_issueDate)->format('Y-m-d');
                            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
                            $vencimiento = Carbon::parse($entrada->sales_expiration)->format('Y-m-d');
                            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

                            $diasCredito = $currentDate->diffInDays($shippingDate);
                            $diasMoratorio = $shippingDate->diffInDays($currentDate);


                            $cuentaPagarP->accountsReceivableP_creditDays = $diasCredito;
                            $cuentaPagarP->accountsReceivableP_moratoriumDays = '-' . $diasMoratorio;


                            $cuentaPagarP->accountsReceivableP_money = $entrada->sales_money;
                            $cuentaPagarP->accountsReceivableP_typeChange = $entrada->sales_typeChange;
                            $cuentaPagarP->accountsReceivableP_customer = $entrada->sales_customer;
                            $cuentaPagarP->accountsReceivableP_condition = $entrada->sales_condition;

                            $cuentaPagarP->accountsReceivableP_amount = $entrada->sales_amount;
                            $cuentaPagarP->accountsReceivableP_taxes = $entrada->sales_taxes;
                            $cuentaPagarP->accountsReceivableP_total = $entrada->sales_total;
                            $cuentaPagarP->accountsReceivableP_balanceTotal = $value->accountsReceivableDetails_amount;
                            $cuentaPagarP->accountsReceivableP_concept = $entrada->sales_concept;
                            $cuentaPagarP->accountsReceivableP_reference = $entrada->sales_reference;
                            $cuentaPagarP->accountsReceivableP_balance = $value->accountsReceivableDetails_amount;
                            $cuentaPagarP->accountsReceivableP_company = $entrada->sales_company;
                            $cuentaPagarP->accountsReceivableP_branchOffice = $entrada->sales_branchOffice;
                            $cuentaPagarP->accountsReceivableP_user = $entrada->sales_user;
                            $cuentaPagarP->accountsReceivableP_status = $this->estatus[1];
                            $cuentaPagarP->accountsReceivableP_origin = $entrada->sales_movement;
                            $cuentaPagarP->accountsReceivableP_originID = $entrada->sales_movementID;
                            $cuentaPagarP->accountsReceivableP_originType =  'Ventas';

                            $validar2 = $cuentaPagarP->save();
                            if ($validar2) {
                                $pagoCancelado = true;
                            } else {
                                $pagoCancelado = false;
                            }
                        } else {
                            $cuentaPagarPendiente->accountsReceivableP_balance =   $cuentaPagarPendiente->accountsReceivableP_balance + $value->accountsReceivableDetails_amount;
                            $cuentaPagarPendiente->accountsReceivableP_balanceTotal =   $cuentaPagarPendiente->accountsReceivableP_balanceTotal + $value->accountsReceivableDetails_amount;
                            $cuentaPagarPendiente->update();
                        }
                    }
                }

                $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $movimientoCancelar->accountsReceivable_id)->where('movementFlow_moduleOrigin', '=', 'CxC')->where('movementFlow_branch', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();
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

                $auxiliar->assistant_companieKey = $movimientoCancelar->accountsReceivable_company;
                $auxiliar->assistant_branchKey = $movimientoCancelar->accountsReceivable_branchOffice;
                $auxiliar->assistant_branch = 'CxC';
                $auxiliar->assistant_movement = $movimientoCancelar->accountsReceivable_movement;
                $auxiliar->assistant_movementID = $movimientoCancelar->accountsReceivable_movementID;
                $auxiliar->assistant_module = 'CxC';
                $auxiliar->assistant_moduleID = $movimientoCancelar->accountsReceivable_id;
                $auxiliar->assistant_money = $movimientoCancelar->accountsReceivable_money;
                $auxiliar->assistant_typeChange = $movimientoCancelar->accountsReceivable_typeChange;
                $auxiliar->assistant_account = $movimientoCancelar->accountsReceivable_customer;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = null;
                $auxiliar->assistant_payment = '-' . $movimientoCancelar->accountsReceivable_total;
                $auxiliar->assistant_apply = $movimientoCancelar->accountsReceivable_origin;
                $auxiliar->assistant_applyID =  $movimientoCancelar->accountsReceivable_originID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $movimientoCancelar->accountsReceivable_reference;


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

                $cxc = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $flujo2->movementFlow_destinityID)->first();


                $auxiliar2 = new PROC_ASSISTANT();

                $auxiliar2->assistant_companieKey = $cxc->accountsReceivable_company;
                $auxiliar2->assistant_branchKey = $cxc->accountsReceivable_branchOffice;
                $auxiliar2->assistant_branch = 'CxC';
                $auxiliar2->assistant_movement = $cxc->accountsReceivable_movement;
                $auxiliar2->assistant_movementID = $cxc->accountsReceivable_movementID;
                $auxiliar2->assistant_module = 'CxC';
                $auxiliar2->assistant_moduleID = $cxc->accountsReceivable_id;
                $auxiliar2->assistant_money = $cxc->accountsReceivable_money;
                $auxiliar2->assistant_typeChange = $cxc->accountsReceivable_typeChange;
                $auxiliar2->assistant_account = $cxc->accountsReceivable_customer;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar2->assistant_year = $year;
                $auxiliar2->assistant_period = $period;
                $auxiliar2->assistant_charge = '-' . $cxc->accountsReceivable_total;
                $auxiliar2->assistant_payment = null;
                $auxiliar2->assistant_apply = $cxc->accountsReceivable_movement;
                $auxiliar2->assistant_applyID =  $cxc->accountsReceivable_movementID;
                $auxiliar2->assistant_canceled = 1;
                $auxiliar2->assistant_reference = $cxc->accountsReceivable_reference;
                $validar7 = $auxiliar2->save();
                if ($validar7) {
                    $pagoCancelado = true;
                } else {
                    $pagoCancelado = false;
                }

                $cxc->accountsReceivable_status = $this->estatus[3];
                $validar8 = $cxc->update();
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

                $movimientoCancelar->accountsReceivable_status = $this->estatus[3];
                $cancelado = $movimientoCancelar->update();

                //buscamos la solicitud en cxpp
                $cxcp = PROC_ACCOUNTS_RECEIVABLE_P::where('accountsReceivableP_movement', '=', $cxc->accountsReceivable_movement)->where('accountsReceivableP_movementID', $cxc->accountsReceivable_movementID)->where('accountsReceivableP_branchOffice', '=', $cxc->accountsReceivable_branchOffice)->first();
                $cxcp->delete();
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

        if ($movimientoCancelar->accountsReceivable_status == $this->estatus[2] && $movimientoCancelar->accountsReceivable_movement == 'Aplicación') {

            try {
                $pagoCancelado = true;
                $aplicacionCancelado = false;
                $anticipoCancelado = true;
                $devolucionCancelado = true;

                //regresamos el monto a las cuentas por pagar
                $detalle = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', '=', $movimientoCancelar->accountsReceivable_id)->get();

                foreach ($detalle as $key => $value) {
                    $cuentaPagar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $value->accountsReceivableDetails_movReference)->first();


                    $cuentaPagar->accountsReceivable_balance = $cuentaPagar->accountsReceivable_balance + $value->accountsReceivableDetails_amount;
                    $cuentaPagar->accountsReceivable_status = $this->estatus[1];
                    // dd($detalle, $cuentaPagar);
                    $validar = $cuentaPagar->update();
                    if ($validar) {
                        $aplicacionCancelado = true;
                    } else {
                        $aplicacionCancelado = false;
                    }

                    //buscamos el movimiento anterior al de cxp
                    $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $cuentaPagar->accountsReceivable_id)->where('movementFlow_movementDestinity', '=', $cuentaPagar->accountsReceivable_movement)->where('movementFlow_branch', '=', $cuentaPagar->accountsReceivable_branchOffice)->first();

                    //regresamos las entradas a cxpp originario desde compras
                    if ($flujo->movementFlow_moduleOrigin == 'Ventas') {
                        $entrada = PROC_SALES::where('sales_id', '=', $flujo->movementFlow_originID)->first();

                        //verificamos si aun existe la entrada en cuenta por pagar pendiente
                        $cuentaPagarPendiente = PROC_ACCOUNTS_RECEIVABLE_P::where('accountsReceivableP_originType', '=', 'Ventas')->where('accountsReceivableP_origin', '=', $entrada->sales_movement)->where('accountsReceivableP_originID', '=', $entrada->sales_movementID)->where('accountsReceivableP_branchOffice', '=', $entrada->sales_branchOffice)->first();


                        if ($cuentaPagarPendiente === null) {

                            $cuentaPagarP = new PROC_ACCOUNTS_RECEIVABLE_P();
                            $cuentaPagarP->accountsReceivableP_movement = $entrada->sales_movement;
                            $cuentaPagarP->accountsReceivableP_movementID = $entrada->sales_movementID;
                            $cuentaPagarP->accountsReceivableP_issuedate = Carbon::parse($entrada->sales_issuedate)->format('Y-m-d');
                            $cuentaPagarP->accountsReceivableP_expiration =  Carbon::parse($entrada->sales_expiration)->format('Y-m-d');

                            //dias credito y moratorio
                            $emision = Carbon::parse($entrada->sales_issueDate)->format('Y-m-d');
                            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
                            $vencimiento = Carbon::parse($entrada->sales_expiration)->format('Y-m-d');
                            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

                            $diasCredito = $currentDate->diffInDays($shippingDate);
                            $diasMoratorio = $shippingDate->diffInDays($currentDate);


                            $cuentaPagarP->accountsReceivableP_creditDays = $diasCredito;
                            $cuentaPagarP->accountsReceivableP_moratoriumDays = '-' . $diasMoratorio;


                            $cuentaPagarP->accountsReceivableP_money = $entrada->sales_money;
                            $cuentaPagarP->accountsReceivableP_typeChange = $entrada->sales_typeChange;
                            $cuentaPagarP->accountsReceivableP_customer = $entrada->sales_customer;
                            $cuentaPagarP->accountsReceivableP_condition = $entrada->sales_condition;

                            $cuentaPagarP->accountsReceivableP_amount = $entrada->sales_amount;
                            $cuentaPagarP->accountsReceivableP_taxes = $entrada->sales_taxes;
                            $cuentaPagarP->accountsReceivableP_total = $entrada->sales_total;
                            $cuentaPagarP->accountsReceivableP_balanceTotal = $value->accountsReceivableDetails_amount;
                            $cuentaPagarP->accountsReceivableP_concept = $entrada->sales_concept;
                            $cuentaPagarP->accountsReceivableP_reference = $entrada->sales_reference;
                            $cuentaPagarP->accountsReceivableP_balance = $value->accountsReceivableDetails_amount;
                            $cuentaPagarP->accountsReceivableP_company = $entrada->sales_company;
                            $cuentaPagarP->accountsReceivableP_branchOffice = $entrada->sales_branchOffice;
                            $cuentaPagarP->accountsReceivableP_user = $entrada->sales_user;
                            $cuentaPagarP->accountsReceivableP_status = $this->estatus[1];
                            $cuentaPagarP->accountsReceivableP_origin = $entrada->sales_movement;
                            $cuentaPagarP->accountsReceivableP_originID = $entrada->sales_movementID;
                            $cuentaPagarP->accountsReceivableP_originType =  'Ventas';

                            $validar2 = $cuentaPagarP->save();
                            if ($validar2) {
                                $aplicacionCancelado = true;
                            } else {
                                $aplicacionCancelado = false;
                            }
                        } else {
                            $cuentaPagarPendiente->accountsReceivableP_balance =   $cuentaPagarPendiente->accountsReceivableP_balance + $value->accountsReceivableDetails_amount;
                            $cuentaPagarPendiente->accountsReceivableP_balanceTotal =   $cuentaPagarPendiente->accountsReceivableP_balanceTotal + $value->accountsReceivableDetails_amount;
                            $cuentaPagarPendiente->update();
                        }
                    }

                    //agregamos auxiliar de cancelacion de la aplicacion

                    $auxiliar = new PROC_ASSISTANT();
                    $auxiliar->assistant_companieKey = $movimientoCancelar->accountsReceivable_company;
                    $auxiliar->assistant_branchKey = $movimientoCancelar->accountsReceivable_branchOffice;
                    $auxiliar->assistant_branch = 'CxC';
                    $auxiliar->assistant_movement = $movimientoCancelar->accountsReceivable_movement;
                    $auxiliar->assistant_movementID = $movimientoCancelar->accountsReceivable_movementID;
                    $auxiliar->assistant_module = 'CxC';
                    $auxiliar->assistant_moduleID = $movimientoCancelar->accountsReceivable_id;
                    $auxiliar->assistant_money = $movimientoCancelar->accountsReceivable_money;
                    $auxiliar->assistant_typeChange = $movimientoCancelar->accountsReceivable_typeChange;
                    $auxiliar->assistant_account = $movimientoCancelar->accountsReceivable_customer;

                    $year = Carbon::now()->year;
                    //sacamos el periodo 
                    $period = Carbon::now()->month;


                    $auxiliar->assistant_year = $year;
                    $auxiliar->assistant_period = $period;
                    $auxiliar->assistant_charge = null;
                    $auxiliar->assistant_payment = '-' . $movimientoCancelar->accountsReceivable_total;
                    $auxiliar->assistant_apply = $cuentaPagar->accountsReceivable_movement;
                    $auxiliar->assistant_applyID =  $cuentaPagar->accountsReceivable_movementID;
                    $auxiliar->assistant_canceled = 1;
                    $auxiliar->assistant_reference = $movimientoCancelar->accountsReceivable_reference;
                    $validar3 = $auxiliar->save();
                    if ($validar3) {
                        $aplicacionCancelado = true;
                    } else {
                        $aplicacionCancelado = false;
                    }
                }

                if ($movimientoCancelar->accountsReceivable_origin !== null) {
                    $anticipo = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', $movimientoCancelar->accountsReceivable_origin)->where('accountsReceivable_movementID', '=', $movimientoCancelar->accountsReceivable_originID)->where('accountsReceivable_branchOffice', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();
                } else {
                    $referencias = explode(" ", $movimientoCancelar->accountsReceivable_reference);
                    $anticipo = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', $referencias[0]  . ' ' . $referencias[1])->where('accountsReceivable_movementID', '=', $referencias[2])->where('accountsReceivable_branchOffice', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();
                    // dd($referencias, $anticipo);
                }

                $auxiliar = new PROC_ASSISTANT();
                $auxiliar->assistant_companieKey = $movimientoCancelar->accountsReceivable_company;
                $auxiliar->assistant_branchKey = $movimientoCancelar->accountsReceivable_branchOffice;
                $auxiliar->assistant_branch = 'CxC';
                $auxiliar->assistant_movement = $movimientoCancelar->accountsReceivable_movement;
                $auxiliar->assistant_movementID = $movimientoCancelar->accountsReceivable_movementID;
                $auxiliar->assistant_module = 'CxC';
                $auxiliar->assistant_moduleID = $movimientoCancelar->accountsReceivable_id;
                $auxiliar->assistant_money = $movimientoCancelar->accountsReceivable_money;
                $auxiliar->assistant_typeChange = $movimientoCancelar->accountsReceivable_typeChange;
                $auxiliar->assistant_account = $movimientoCancelar->accountsReceivable_customer;

                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = '-' . $movimientoCancelar->accountsReceivable_total;
                $auxiliar->assistant_payment = null;
                $auxiliar->assistant_apply = $anticipo->accountsReceivable_movement;
                $auxiliar->assistant_applyID =  $anticipo->accountsReceivable_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $movimientoCancelar->accountsReceivable_reference;
                $validar4 = $auxiliar->save();

                if ($validar4) {
                    $aplicacionCancelado = true;
                } else {
                    $aplicacionCancelado = false;
                }
                //cancelamos los movimientos
                $movimiento = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $movimientoCancelar->accountsReceivable_id)->where('movementFlow_movementDestinity', '=', $movimientoCancelar->accountsReceivable_movement)->where('movementFlow_branch', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();
                $movimiento->movementFlow_cancelled = 1;
                $validar5 = $movimiento->update();
                if ($validar5) {
                    $aplicacionCancelado = true;
                } else {
                    $aplicacionCancelado = false;
                }

                $movimiento2 = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $anticipo->accountsReceivable_id)->where('movementFlow_movementOrigin', '=',  $anticipo->accountsReceivable_movement)->where('movementFlow_movementDestinity', '=', $movimientoCancelar->accountsReceivable_movement)->where('movementFlow_branch', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();
                $movimiento2->movementFlow_cancelled = 1;
                $validar6 = $movimiento2->update();
                if ($validar6) {
                    $aplicacionCancelado = true;
                } else {
                    $aplicacionCancelado = false;
                }


                //regresamos el saldo al anticipo
                $anticipo->accountsReceivable_balance = $anticipo->accountsReceivable_balance + $movimientoCancelar->accountsReceivable_total;
                $anticipo->accountsReceivable_status = $this->estatus[1];
                $validar7 = $anticipo->update();
                if ($validar7) {
                    $aplicacionCancelado = true;
                } else {
                    $aplicacionCancelado = false;
                }

                //cancelamos el folio
                $movimientoCancelar->accountsReceivable_status = $this->estatus[3];
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

        if ($movimientoCancelar->accountsReceivable_status == $this->estatus[1] && $movimientoCancelar->accountsReceivable_movement == 'Anticipo Clientes' && $request->tipo == 'Caja') {
            try {
                $pagoCancelado = true;
                $aplicacionCancelado = true;
                $anticipoCancelado = false;
                $devolucionCancelado = true;

                $val = (float) $movimientoCancelar->accountsReceivable_balance;
                $val2 = (float) $movimientoCancelar->accountsReceivable_total;
                if ($val != $val2) {
                    $status = 400;
                    $message = 'No se puede cancelar el anticipo, ya que tiene movimientos asociados.' . 'Movimiento: ' . $movimientoCancelar->accountsReceivable_movement . ' Folio: ' . $movimientoCancelar->accountsReceivable_movementID;
                    return response()->json(['status' => $status, 'message' => $message]);
                }


                //buscamos la cuenta para regresar el dinero
                $cuenta = PROC_BALANCE::where('balance_account', '=', $movimientoCancelar->accountsReceivable_moneyAccount)->where('balance_branchKey', '=', $movimientoCancelar->accountsReceivable_branchOffice)->where('balance_money', '=', $movimientoCancelar->accountsReceivable_money)->first();
                if ($cuenta) {
                    $cuenta->balance_balance = $cuenta->balance_balance - $movimientoCancelar->accountsReceivable_total;
                    $cuenta->balance_reconcile = $cuenta->balance_balance;
                    $validar = $cuenta->update();
                    if ($validar) {
                        $anticipoCancelado = true;
                        $this->actualizarSaldosCuentasDinero($movimientoCancelar->accountsReceivable_id, $movimientoCancelar->accountsReceivable_money);
                    } else {
                        $anticipoCancelado = false;
                    }
                }

                $cuenta2 = PROC_BALANCE::where('balance_account', '=', $movimientoCancelar->accountsReceivable_customer)->where('balance_branchKey', '=', $movimientoCancelar->accountsReceivable_branchOffice)->where('balance_branch', '=', 'CxC')->where('balance_money', '=', $movimientoCancelar->accountsReceivable_money)->first();
                if ($cuenta2) {
                    $cuenta2->balance_balance = $cuenta2->balance_balance + $movimientoCancelar->accountsReceivable_total;
                    $cuenta2->balance_reconcile = $cuenta2->balance_balance;
                    $validar2 = $cuenta2->update();
                    if ($validar2) {
                        $anticipoCancelado = true;
                    } else {
                        $anticipoCancelado = false;
                    }
                }

                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $movimientoCancelar->accountsReceivable_company;
                $auxiliar->assistant_branchKey = $movimientoCancelar->accountsReceivable_branchOffice;
                $auxiliar->assistant_branch = 'CxC';

                $auxiliar->assistant_movement = $movimientoCancelar->accountsReceivable_movement;
                $auxiliar->assistant_movementID = $movimientoCancelar->accountsReceivable_movementID;
                $auxiliar->assistant_module = 'CxC';
                $auxiliar->assistant_moduleID = $movimientoCancelar->accountsReceivable_id;
                $auxiliar->assistant_money = $movimientoCancelar->accountsReceivable_money;
                $auxiliar->assistant_typeChange = $movimientoCancelar->accountsReceivable_typeChange;
                $auxiliar->assistant_account = $movimientoCancelar->accountsReceivable_customer;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = '-' . $movimientoCancelar->accountsReceivable_total;
                $auxiliar->assistant_payment = null;
                $auxiliar->assistant_apply = $movimientoCancelar->accountsReceivable_movement;
                $auxiliar->assistant_applyID = $movimientoCancelar->accountsReceivable_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $movimientoCancelar->accountsReceivable_reference;

                $validar3 = $auxiliar->save();
                if ($validar3) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }


                $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $movimientoCancelar->accountsReceivable_id)->where('movementFlow_movementOrigin', '=', $movimientoCancelar->accountsReceivable_movement)->where('movementFlow_moduleDestiny', '=', 'Din')->where('movementFlow_movementDestinity', '=', 'Ingreso')->where('movementFlow_branch', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();
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

                $tesoreria->treasuries_status = $this->estatus[3];
                $validar = $tesoreria->update();

                $auxiliar2 = new PROC_ASSISTANT();

                $auxiliar2->assistant_companieKey = $movimientoCancelar->accountsReceivable_company;
                $auxiliar2->assistant_branchKey = $movimientoCancelar->accountsReceivable_branchOffice;
                $auxiliar2->assistant_branch = 'Din';

                $auxiliar2->assistant_movement = $tesoreria->treasuries_movement;
                $auxiliar2->assistant_movementID = $tesoreria->treasuries_movementID;
                $auxiliar2->assistant_module = 'Din';
                $auxiliar2->assistant_moduleID = $tesoreria->treasuries_id;
                $auxiliar2->assistant_money = $movimientoCancelar->accountsReceivable_money;
                $auxiliar2->assistant_typeChange = $movimientoCancelar->accountsReceivable_typeChange;
                $auxiliar2->assistant_account = $movimientoCancelar->accountsReceivable_moneyAccount;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar2->assistant_year = $year;
                $auxiliar2->assistant_period = $period;
                $auxiliar2->assistant_charge = '-' . $movimientoCancelar->accountsReceivable_total;
                $auxiliar2->assistant_payment = null;
                $auxiliar2->assistant_apply = $tesoreria->treasuries_movement;
                $auxiliar2->assistant_applyID = $tesoreria->treasuries_movementID;
                $auxiliar2->assistant_canceled = 1;
                $auxiliar2->assistant_reference = $movimientoCancelar->accountsReceivable_reference;

                $validar5 = $auxiliar2->save();
                if ($validar5) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }

                $movimientoCancelar->accountsReceivable_status = $this->estatus[3];
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
        if ($movimientoCancelar->accountsReceivable_status == $this->estatus[1] && $movimientoCancelar->accountsReceivable_movement == 'Anticipo Clientes' && $request->tipo == 'Banco') {
            try {
                $pagoCancelado = true;
                $aplicacionCancelado = true;
                $anticipoCancelado = false;
                $devolucionCancelado = true;

                $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $movimientoCancelar->accountsReceivable_id)->where('movementFlow_movementOrigin', '=', $movimientoCancelar->accountsReceivable_movement)->where('movementFlow_moduleDestiny', '=', 'Din')->where('movementFlow_movementDestinity', '=', 'Solicitud Depósito')->where('movementFlow_branch', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();

                //buscamos la solicitud de cheque en tesoreria
                $solicitudCheque = PROC_TREASURY::where('treasuries_id', '=', $flujo->movementFlow_destinityID)->first();


                if ($solicitudCheque->treasuries_status === $this->estatus[2]) {
                    $status = 400;
                    $message = 'No se puede cancelar el anticipo, ya que tiene una solicitud de depósito asociada.' . ' Movimiento: ' . $movimientoCancelar->accountsReceivable_movement . ' Folio: ' . $movimientoCancelar->accountsReceivable_movementID;
                    return response()->json(['mensaje' => $message, 'estatus' => $status]);
                }

                $val = (float) $movimientoCancelar->accountsReceivable_balance;
                $val2 = (float) $movimientoCancelar->accountsReceivable_total;
                if ($val != $val2) {
                    $status = 400;
                    $message = 'No se puede cancelar el anticipo, ya que tiene movimientos asociados.' . 'Movimiento: ' . $movimientoCancelar->accountsReceivable_movement . ' Folio: ' . $movimientoCancelar->accountsReceivable_movementID;
                    return response()->json(['status' => $status, 'message' => $message]);
                }



                $solicitudCheque->treasuries_status = $this->estatus[3];
                $validar = $solicitudCheque->update();
                if ($validar) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }

                $flujo2 = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $flujo->movementFlow_destinityID)->where('movementFlow_movementOrigin', '=', $flujo->movementFlow_movementDestinity)->where('movementFlow_moduleDestiny', '=', 'CxC')->where('movementFlow_movementDestinity', '=', $flujo->movementFlow_movementDestinity)->where('movementFlow_branch', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();

                //Cancelamos la solicitud de cheque en cxp
                $solicitudCheque2 = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $flujo2->movementFlow_destinityID)->first();
                $solicitudCheque2->accountsReceivable_status = $this->estatus[3];
                $validar2 = $solicitudCheque2->update();
                if ($validar2) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }

                //buscamos los mismo datos de la solicitud de cheque en cxp pendiente

                $cxcpCancelar = PROC_ACCOUNTS_RECEIVABLE_P::where('accountsReceivableP_movement', '=', $movimientoCancelar->accountsReceivable_movement)->where('accountsReceivableP_movementID', '=', $movimientoCancelar->accountsReceivable_movementID)->first();
                // $validar3 = $cxcpCancelar->delete();
                // if($validar3){
                //     $anticipoCancelado = true;
                // }else{
                //     $anticipoCancelado = false;
                // }


                $cxcpCancelar2 = PROC_ACCOUNTS_RECEIVABLE_P::where('accountsReceivableP_movement', '=', $solicitudCheque2->accountsReceivable_movement)->where('accountsReceivableP_movementID', '=', $solicitudCheque2->accountsReceivable_movementID)->first();
                // $validar4 = $cxcpCancelar2->delete();
                // if($validar4){
                //     $anticipoCancelado = true;
                // }else{
                //     $anticipoCancelado = false;
                // }


                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $movimientoCancelar->accountsReceivable_company;
                $auxiliar->assistant_branchKey = $movimientoCancelar->accountsReceivable_branchOffice;
                $auxiliar->assistant_branch = 'CxC';

                $auxiliar->assistant_movement = $movimientoCancelar->accountsReceivable_movement;
                $auxiliar->assistant_movementID = $movimientoCancelar->accountsReceivable_movementID;
                $auxiliar->assistant_module = 'CxC';
                $auxiliar->assistant_moduleID = $movimientoCancelar->accountsReceivable_id;
                $auxiliar->assistant_money = $movimientoCancelar->accountsReceivable_money;
                $auxiliar->assistant_typeChange = $movimientoCancelar->accountsReceivable_typeChange;
                $auxiliar->assistant_account = $movimientoCancelar->accountsReceivable_customer;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = null;
                $auxiliar->assistant_payment = '-' . $movimientoCancelar->accountsReceivable_total;
                $auxiliar->assistant_apply = $movimientoCancelar->accountsReceivable_movement;
                $auxiliar->assistant_applyID =  $movimientoCancelar->accountsReceivable_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $movimientoCancelar->accountsReceivable_reference;


                $validar5 = $auxiliar->save();
                if ($validar5) {
                    $anticipoCancelado = true;
                } else {
                    $anticipoCancelado = false;
                }

                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $solicitudCheque2->accountsReceivable_company;
                $auxiliar->assistant_branchKey = $solicitudCheque2->accountsReceivable_branchOffice;
                $auxiliar->assistant_branch = 'CxC';


                $auxiliar->assistant_movement = $solicitudCheque2->accountsReceivable_movement;
                $auxiliar->assistant_movementID = $solicitudCheque2->accountsReceivable_movementID;
                $auxiliar->assistant_module = 'CxC';
                $auxiliar->assistant_moduleID = $solicitudCheque2->accountsReceivable_id;
                $auxiliar->assistant_money = $solicitudCheque2->accountsReceivable_money;
                $auxiliar->assistant_typeChange = $solicitudCheque2->accountsReceivable_typeChange;
                $auxiliar->assistant_account = $solicitudCheque2->accountsReceivable_customer;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = '-' . $solicitudCheque2->accountsReceivable_total;
                $auxiliar->assistant_payment = null;
                $auxiliar->assistant_apply = $solicitudCheque2->accountsReceivable_movement;
                $auxiliar->assistant_applyID =  $solicitudCheque2->accountsReceivable_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $solicitudCheque2->accountsReceivable_reference;

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

                $movimientoCancelar->accountsReceivable_status = $this->estatus[3];
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


        if ($movimientoCancelar->accountsReceivable_status == $this->estatus[2] && $movimientoCancelar->accountsReceivable_movement == 'Devolución de Anticipo' && $request->tipo == 'Caja') {
            try {
                $pagoCancelado = true;
                $aplicacionCancelado = true;
                $anticipoCancelado = true;
                $devolucionCancelado = false;


                $val = (float) $movimientoCancelar->accountsReceivable_balance;
                $val2 = (float) $movimientoCancelar->accountsReceivable_total;
                if ($val != $val2) {
                    $status = 400;
                    $message = 'No se puede cancelar la devolución, ya que tiene movimientos asociados.' . 'Movimiento: ' . $movimientoCancelar->accountsReceivable_movement . ' Folio: ' . $movimientoCancelar->accountsReceivable_movementID;
                    return response()->json(['status' => $status, 'message' => $message]);
                }


                //buscamos la cuenta para regresar el dinero
                $cuenta = PROC_BALANCE::where('balance_account', '=', $movimientoCancelar->accountsReceivable_moneyAccount)->where('balance_branchKey', '=', $movimientoCancelar->accountsReceivable_branchOffice)->where('balance_money', '=', $movimientoCancelar->accountsReceivable_money)->first();
                if ($cuenta) {
                    $cuenta->balance_balance = $cuenta->balance_balance + $movimientoCancelar->accountsReceivable_total;
                    $cuenta->balance_reconcile = $cuenta->balance_balance;
                    $validar = $cuenta->update();
                    if ($validar) {
                        $devolucionCancelado = true;
                        $this->actualizarSaldosCuentasDinero($movimientoCancelar->accountsReceivable_id, $movimientoCancelar->accountsReceivable_money);
                    } else {
                        $devolucionCancelado = false;
                    }
                }

                $cuenta2 = PROC_BALANCE::where('balance_account', '=', $movimientoCancelar->accountsReceivable_customer)->where('balance_branchKey', '=', $movimientoCancelar->accountsReceivable_branchOffice)->where('balance_branch', '=', 'CxC')->where('balance_money', '=', $movimientoCancelar->accountsReceivable_money)->first();
                if ($cuenta2) {
                    $cuenta2->balance_balance = $cuenta2->balance_balance - $movimientoCancelar->accountsReceivable_total;
                    $cuenta2->balance_reconcile = $cuenta2->balance_balance;
                    $validar2 = $cuenta2->update();
                    if ($validar2) {
                        $devolucionCancelado = true;
                    } else {
                        $devolucionCancelado = false;
                    }
                }

                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $movimientoCancelar->accountsReceivable_company;
                $auxiliar->assistant_branchKey = $movimientoCancelar->accountsReceivable_branchOffice;
                $auxiliar->assistant_branch = 'CxC';

                $auxiliar->assistant_movement = $movimientoCancelar->accountsReceivable_movement;
                $auxiliar->assistant_movementID = $movimientoCancelar->accountsReceivable_movementID;
                $auxiliar->assistant_module = 'CxC';
                $auxiliar->assistant_moduleID = $movimientoCancelar->accountsReceivable_id;
                $auxiliar->assistant_money = $movimientoCancelar->accountsReceivable_money;
                $auxiliar->assistant_typeChange = $movimientoCancelar->accountsReceivable_typeChange;
                $auxiliar->assistant_account = $movimientoCancelar->accountsReceivable_customer;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = null;
                $auxiliar->assistant_payment = '-' . $movimientoCancelar->accountsReceivable_total;
                $auxiliar->assistant_apply = $movimientoCancelar->accountsReceivable_movement;
                $auxiliar->assistant_applyID = $movimientoCancelar->accountsReceivable_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $movimientoCancelar->accountsReceivable_reference;

                $validar3 = $auxiliar->save();
                if ($validar3) {
                    $devolucionCancelado = true;
                } else {
                    $devolucionCancelado = false;
                }


                $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $movimientoCancelar->accountsReceivable_id)->where('movementFlow_movementOrigin', '=', $movimientoCancelar->accountsReceivable_movement)->where('movementFlow_moduleDestiny', '=', 'Din')->where('movementFlow_movementDestinity', '=', 'Egreso')->where('movementFlow_branch', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();
                if ($flujo) {
                    $flujo->movementFlow_cancelled = 1;
                    $validar4 = $flujo->update();
                    if ($validar4) {
                        $devolucionCancelado = true;
                    } else {
                        $devolucionCancelado = false;
                    }
                }

                $tesoreria = PROC_TREASURY::where('treasuries_id', '=', $flujo->movementFlow_destinityID)->first();

                $tesoreria->treasuries_status = $this->estatus[3];
                $validar = $tesoreria->update();

                $auxiliar2 = new PROC_ASSISTANT();

                $auxiliar2->assistant_companieKey = $movimientoCancelar->accountsReceivable_company;
                $auxiliar2->assistant_branchKey = $movimientoCancelar->accountsReceivable_branchOffice;
                $auxiliar2->assistant_branch = 'Din';

                $auxiliar2->assistant_movement = $tesoreria->treasuries_movement;
                $auxiliar2->assistant_movementID = $tesoreria->treasuries_movementID;
                $auxiliar2->assistant_module = 'Din';
                $auxiliar2->assistant_moduleID = $tesoreria->treasuries_id;
                $auxiliar2->assistant_money = $movimientoCancelar->accountsReceivable_money;
                $auxiliar2->assistant_typeChange = $movimientoCancelar->accountsReceivable_typeChange;
                $auxiliar2->assistant_account = $movimientoCancelar->accountsReceivable_moneyAccount;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar2->assistant_year = $year;
                $auxiliar2->assistant_period = $period;
                $auxiliar2->assistant_charge = null;
                $auxiliar2->assistant_payment = '-' . $movimientoCancelar->accountsReceivable_total;
                $auxiliar2->assistant_apply = $tesoreria->treasuries_movement;
                $auxiliar2->assistant_applyID = $tesoreria->treasuries_movementID;
                $auxiliar2->assistant_canceled = 1;
                $auxiliar2->assistant_reference = $movimientoCancelar->accountsReceivable_reference;

                $validar5 = $auxiliar2->save();
                if ($validar5) {
                    $devolucionCancelado = true;
                } else {
                    $devolucionCancelado = false;
                }

                $movimientoCancelar->accountsReceivable_status = $this->estatus[3];
                $cancelado = $movimientoCancelar->update();
                if ($cancelado) {
                    $devolucionCancelado = true;
                } else {
                    $devolucionCancelado = false;
                }
            } catch (\Exception $e) {
                dd($e);
                $devolucionCancelado = false;
                $message = $e->getMessage();
            }
        }

        if ($movimientoCancelar->accountsReceivable_status == $this->estatus[2] && $movimientoCancelar->accountsReceivable_movement == 'Devolución de Anticipo' && $request->tipo == 'Banco') {
            try {
                $pagoCancelado = true;
                $aplicacionCancelado = true;
                $anticipoCancelado = true;
                $devolucionCancelado = false;

                $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $movimientoCancelar->accountsReceivable_id)->where('movementFlow_movementOrigin', '=', $movimientoCancelar->accountsReceivable_movement)->where('movementFlow_moduleDestiny', '=', 'Din')->where('movementFlow_movementDestinity', '=', 'Sol. de Cheque/Transferencia')->where('movementFlow_branch', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();

                //buscamos la solicitud de cheque en tesoreria
                $solicitudCheque = PROC_TREASURY::where('treasuries_id', '=', $flujo->movementFlow_destinityID)->first();


                if ($solicitudCheque->treasuries_status === $this->estatus[2]) {
                    $status = 400;
                    $message = 'No se puede cancelar la devolución, ya que tiene una solicitud de cheque asociada.' . ' Movimiento: ' . $movimientoCancelar->accountsReceivable_movement . ' Folio: ' . $movimientoCancelar->accountsReceivable_movementID;
                    return response()->json(['mensaje' => $message, 'estatus' => $status]);
                }

                $val = (float) $movimientoCancelar->accountsReceivable_balance;
                $val2 = (float) $movimientoCancelar->accountsReceivable_total;
                if ($val != $val2) {
                    $status = 400;
                    $message = 'No se puede cancelar la devolución, ya que tiene movimientos asociados.' . 'Movimiento: ' . $movimientoCancelar->accountsReceivable_movement . ' Folio: ' . $movimientoCancelar->accountsReceivable_movementID;
                    return response()->json(['status' => $status, 'message' => $message]);
                }



                $solicitudCheque->treasuries_status = $this->estatus[3];
                $validar = $solicitudCheque->update();
                if ($validar) {
                    $devolucionCancelado = true;
                } else {
                    $devolucionCancelado = false;
                }

                $flujo2 = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $flujo->movementFlow_destinityID)->where('movementFlow_movementOrigin', '=', $flujo->movementFlow_movementDestinity)->where('movementFlow_moduleDestiny', '=', 'CxC')->where('movementFlow_movementDestinity', '=', $flujo->movementFlow_movementDestinity)->where('movementFlow_branch', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();

                //Cancelamos la solicitud de cheque en cxp
                $solicitudCheque2 = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $flujo2->movementFlow_destinityID)->first();
                $solicitudCheque2->accountsReceivable_status = $this->estatus[3];
                $validar2 = $solicitudCheque2->update();
                if ($validar2) {
                    $devolucionCancelado = true;
                } else {
                    $devolucionCancelado = false;
                }

                $movimiento = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $movimientoCancelar->accountsReceivable_id)->where('movementFlow_movementDestinity', '=', $movimientoCancelar->accountsReceivable_movement)->where('movementFlow_branch', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();
                $movimiento->movementFlow_cancelled = 1;
                $validar4 = $movimiento->update();
                if ($validar4) {
                    $aplicacionCancelado = true;
                } else {
                    $aplicacionCancelado = false;
                }

                //buscamos los mismo datos de la solicitud de cheque en cxp pendiente

                $cxcpCancelar = PROC_ACCOUNTS_RECEIVABLE_P::where('accountsReceivableP_movement', '=', $movimientoCancelar->accountsReceivable_movement)->where('accountsReceivableP_movementID', '=', $movimientoCancelar->accountsReceivable_movementID)->first();
                // $validar3 = $cxcpCancelar->delete();
                // if($validar3){
                //     $devolucionCancelado = true;
                // }else{
                //     $devolucionCancelado = false;
                // }


                $cxcpCancelar2 = PROC_ACCOUNTS_RECEIVABLE_P::where('accountsReceivableP_movement', '=', $solicitudCheque2->accountsReceivable_movement)->where('accountsReceivableP_movementID', '=', $solicitudCheque2->accountsReceivable_movementID)->first();
                // $validar4 = $cxcpCancelar2->delete();
                // if($validar4){
                //     $devolucionCancelado = true;
                // }else{
                //     $devolucionCancelado = false;
                // }


                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $movimientoCancelar->accountsReceivable_company;
                $auxiliar->assistant_branchKey = $movimientoCancelar->accountsReceivable_branchOffice;
                $auxiliar->assistant_branch = 'CxC';

                $auxiliar->assistant_movement = $movimientoCancelar->accountsReceivable_movement;
                $auxiliar->assistant_movementID = $movimientoCancelar->accountsReceivable_movementID;
                $auxiliar->assistant_module = 'CxC';
                $auxiliar->assistant_moduleID = $movimientoCancelar->accountsReceivable_id;
                $auxiliar->assistant_money = $movimientoCancelar->accountsReceivable_money;
                $auxiliar->assistant_typeChange = $movimientoCancelar->accountsReceivable_typeChange;
                $auxiliar->assistant_account = $movimientoCancelar->accountsReceivable_customer;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = null;
                $auxiliar->assistant_payment = '-' . $movimientoCancelar->accountsReceivable_total;
                $auxiliar->assistant_apply = $movimientoCancelar->accountsReceivable_movement;
                $auxiliar->assistant_applyID =  $movimientoCancelar->accountsReceivable_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $movimientoCancelar->accountsReceivable_reference;


                $validar5 = $auxiliar->save();
                if ($validar5) {
                    $devolucionCancelado = true;
                } else {
                    $devolucionCancelado = false;
                }

                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $solicitudCheque2->accountsReceivable_company;
                $auxiliar->assistant_branchKey = $solicitudCheque2->accountsReceivable_branchOffice;
                $auxiliar->assistant_branch = 'CxC';


                $auxiliar->assistant_movement = $solicitudCheque2->accountsReceivable_movement;
                $auxiliar->assistant_movementID = $solicitudCheque2->accountsReceivable_movementID;
                $auxiliar->assistant_module = 'CxC';
                $auxiliar->assistant_moduleID = $solicitudCheque2->accountsReceivable_id;
                $auxiliar->assistant_money = $solicitudCheque2->accountsReceivable_money;
                $auxiliar->assistant_typeChange = $solicitudCheque2->accountsReceivable_typeChange;
                $auxiliar->assistant_account = $solicitudCheque2->accountsReceivable_customer;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = '-' . $solicitudCheque2->accountsReceivable_total;
                $auxiliar->assistant_payment = null;
                $auxiliar->assistant_apply = $solicitudCheque2->accountsReceivable_movement;
                $auxiliar->assistant_applyID =  $solicitudCheque2->accountsReceivable_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $solicitudCheque2->accountsReceivable_reference;

                $validar6 = $auxiliar->save();
                if ($validar6) {
                    $devolucionCancelado = true;
                } else {
                    $devolucionCancelado = false;
                }

                $flujo->movementFlow_cancelled = 1;
                $validar7 = $flujo->update();
                if ($validar7) {
                    $devolucionCancelado = true;
                } else {
                    $devolucionCancelado = false;
                }


                $flujo2->movementFlow_cancelled = 1;
                $validar8 = $flujo2->update();
                if ($validar8) {
                    $devolucionCancelado = true;
                } else {
                    $devolucionCancelado = false;
                }

                if ($movimientoCancelar->accountsReceivable_origin !== null) {
                    $anticipo = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', $movimientoCancelar->accountsReceivable_origin)->where('accountsReceivable_movementID', '=', $movimientoCancelar->accountsReceivable_originID)->where('accountsReceivable_branchOffice', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();
                } else {
                    $referencias = explode(" ", $movimientoCancelar->accountsReceivable_reference);
                    $anticipo = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', $referencias[0]  . ' ' . $referencias[1])->where('accountsReceivable_movementID', '=', $referencias[2])->where('accountsReceivable_branchOffice', '=', $movimientoCancelar->accountsReceivable_branchOffice)->first();
                    // dd($referencias, $anticipo);
                }

                //regresamos el saldo al anticipo
                $anticipo->accountsReceivable_balance = $anticipo->accountsReceivable_balance + $movimientoCancelar->accountsReceivable_total;
                $anticipo->accountsReceivable_status = $this->estatus[1];
                $validar7 = $anticipo->update();
                if ($validar7) {
                    $aplicacionCancelado = true;
                } else {
                    $aplicacionCancelado = false;
                }

                $movimientoCancelar->accountsReceivable_status = $this->estatus[3];
                $cancelado = $movimientoCancelar->update();
                if ($cancelado) {
                    $devolucionCancelado = true;
                } else {
                    $devolucionCancelado = false;
                }
            } catch (\Exception $e) {
                dd($e);
                $devolucionCancelado = false;
                $message = $e->getMessage();
            }
        }

        $cxcReference = PROC_CFDI::WHERE('cfdi_moduleID', '=', $movimientoCancelar->accountsReceivable_id)->first();

        if ($cxcReference !== null) {
            $cxcReference->cfdi_cancelled = 1;
            $cxcReference->update();
        }



        if ($pagoCancelado === true && $aplicacionCancelado === true && $anticipoCancelado === true && $devolucionCancelado === true) {
            if ((session('company')->companies_calculateTaxes == '0' || session('company')->companies_calculateTaxes == 0)) {
                $this->agregarCancelacion($movimientoCancelar->accountsReceivable_id, $request);
                //Metemos la logica para cancelar el cfdi
                $cfdi = new TimbradoController();
                $error = $cfdi->cancelarCxC($movimientoCancelar->accountsReceivable_id);

                if ($error) {
                    $status = 500;
                    $message = 'Error al cancelar el timbrado';
                } else {
                    $status = 200;
                    $message = 'Proceso cancelado correctamente';
                }
            } else {
                $status = 200;
                $message = 'Proceso cancelado correctamente';
            }
        } else {
            $status = 500;
            $message = 'Error al cancelar el Proceso';
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status]);
    }

    public function agregarCancelacion($folio, $request)
    {
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $folio)->first();
        $cancelacionFactura = json_decode($request['inputJsonCancelacionFactura'], true);

        if ($folioAfectar->accountsReceivable_status == $this->estatus[3] && $folioAfectar->accountsReceivable_movement == 'Anticipo Clientes' || $folioAfectar->accountsReceivable_movement == 'Aplicación' || $folioAfectar->accountsReceivable_movement == 'Cobro de Facturas' || $folioAfectar->accountsReceivable_movement == 'Devolución de Anticipo' && $folioAfectar->accountsReceivable_stamped == 1) {
            $cancelacion = new PROC_CANCELED_REASON();
            $cancelacion->canceledReason_module = 'CxC';
            $cancelacion->canceledReason_moduleID = $folioAfectar->accountsReceivable_id;
            $cancelacion->canceledReason_reason = $cancelacionFactura['motivoCancelacion'];
            $cancelacion->canceledReason_sustitutionUuid = $cancelacionFactura['folioSustitucion'];

            $cancelacion->save();
        }
    }

    public function actualizarSaldosCuentasDinero($folio, $moneda)
    {
        $folioAfectar = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', $folio)->first();

        $cuenta = PROC_BALANCE::where('balance_account', '=', $folioAfectar->accountsReceivable_moneyAccount)->where('balance_branchKey', '=', $folioAfectar->accountsReceivable_branchOffice)->where('balance_money', '=', $folioAfectar->accountsReceivable_money)->first();

        $validarCuenta = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', $folioAfectar->accountsReceivable_moneyAccount)->where('moneyAccountsBalance_status', 'Alta')->where("moneyAccountsBalance_company", '=', $folioAfectar->accountsReceivable_company)->where("moneyAccountsBalance_money", '=', $moneda)->first();
        $validarCuenta->moneyAccountsBalance_balance = $cuenta->balance_balance;
        $validarCuenta->update();
    }

    public function eliminarMovimientoCxC(Request $request)
    {
        $cxc = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $request->id)->first();

        // //buscamos sus articulos
        $articulos = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', '=', $request->id)->where('accountsReceivableDetails_branchOffice', '=', $cxc->accountsReceivable_branchOffice)->get();

        if ($articulos->count() > 0) {
            //eliminamos sus articulos
            foreach ($articulos as $articulo) {
                $articulosDelete = $articulo->delete();
            }
        } else {
            $articulosDelete = true;
        }


        // // dd($articulos);
        if ($cxc->accountsReceivable_status === $this->estatus[0] && $articulosDelete === true) {
            $isDelete = $cxc->delete();
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
        $cuentasxcobrar = PROC_ACCOUNTS_RECEIVABLE::join('CAT_CUSTOMERS', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_customer', '=', 'CAT_CUSTOMERS.customers_key', 'left outer')
            ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key', 'left outer')
            ->join('CONF_CREDIT_CONDITIONS', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id', 'left outer')
            ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_company', '=', 'CAT_COMPANIES.companies_key', 'left outer')
            ->join('CONF_FORMS_OF_PAYMENT', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_formPayment', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key', 'left outer')
            ->join('PROC_BALANCE', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_customer', '=', 'PROC_BALANCE.balance_account', 'left outer')
            ->join('CONF_MONEY', 'CONF_MONEY.money_key', '=', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_money')
            ->where('accountsReceivable_id', '=', $id)
            ->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_company', '=', session('company')->companies_key)
            ->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_branchOffice', '=', session('sucursal')->branchOffices_key)
            // ->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_status', '=', $this->estatus[1])
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
        $cuentas_cobrar = PROC_ACCOUNTS_RECEIVABLE::join('PROC_ACCOUNTS_RECEIVABLE_DETAILS', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_id', '=', 'PROC_ACCOUNTS_RECEIVABLE_DETAILS.accountsReceivableDetails_accountReceivableID', 'left outer')
            ->where('accountsReceivable_id', '=', $id)
            ->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_company', '=', session('company')->companies_key)
            ->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->get();


        //  dd($cuentas_cobrar);
        $pdf = PDF::loadView('reportes.cuentasxcobrar-reporte', ['cuenta' => $id, 'cuentasxcobrar' => $cuentasxcobrar, 'logo' => $logoBase64, 'cuentas_cobrar' => $cuentas_cobrar]);
        $pdf->set_paper('a4', 'landscape');
        return $pdf->stream();
    }

    public function CXCAction(Request $request)
    {
        $nameFolio = $request->nameFolio;
        $nameKey = $request->nameKey;
        $nameMov = $request->nameMov;
        $status = $request->status;
        $nameFecha = $request->nameFecha;
        $nameFechaVen = $request->nameFechaVen;
        $nameUsuario = $request->nameUsuario;
        $nameSucursal = $request->nameSucursal;
        $nameMoneda = $request->nameMoneda;
        $timbrado = $request->timbrado;

        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;
        $fechaInicioVen = $request->fechaInicioVen;
        $fechaFinalVen = $request->fechaFinalVen;
        // dd($nameFolio, $nameKey, $nameMov, $status, $nameFecha, $nameFechaVen, $nameUsuario, $nameSucursal, $nameMoneda, $fechaInicio, $fechaFinal, $fechaInicioVen, $fechaFinalVen);

        if ($fechaInicio !== null && $fechaFinal !== null && $nameFecha === 'Rango Fechas') {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }

        if ($fechaInicioVen !== null && $fechaFinalVen !== null && $nameFechaVen === 'Rango Fechas') {
            $nameFechaVen = $fechaInicioVen . '+' . $fechaFinalVen;
        }
        //  DD($nameFolio, $nameKey, $nameMov, $status, $nameFecha, $nameFechaVen, $nameUsuario, $nameSucursal, $nameMoneda, $fechaInicio, $fechaFinal, $fechaInicioVen, $fechaFinalVen);
        switch ($request->input('action')) {
            case 'Búsqueda':
                $CXC_collection_filtro = PROC_ACCOUNTS_RECEIVABLE::join('CAT_CUSTOMERS', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_customer', '=', 'CAT_CUSTOMERS.customers_key', 'left outer')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key', 'left outer')
                    ->join('CONF_CREDIT_CONDITIONS', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id', 'left outer')
                    ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_company', '=', 'CAT_COMPANIES.companies_key', 'left outer')
                    ->join('CONF_FORMS_OF_PAYMENT', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_formPayment', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key', 'left outer')
                    ->whereAccountsReceivableMovementID($nameFolio)
                    ->whereAccountsReceivableCustomer($nameKey)
                    ->whereAccountsReceivableMovement($nameMov)
                    ->whereAccountsReceivableStatus($status)
                    ->whereAccountsReceivableDate($nameFecha)
                    ->whereAccountsReceivableExpiration($nameFechaVen)
                    ->whereAccountsReceivableUser($nameUsuario)
                    ->whereAccountsReceivablebranchOffice($nameSucursal)
                    ->whereAccountsReceivableMoney($nameMoneda)
                    // ->whereBalanceMoney($nameMoneda)
                    // ->whereBalancebranchKey($nameSucursal)
                    ->whereAccountsReceivableStamped($timbrado)
                    ->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_company', '=', session('company')->companies_key)
                    // ->where('PROC_BALANCE.balance_companieKey', '=', session('company')->companies_key)
                    ->orderBy('PROC_ACCOUNTS_RECEIVABLE.updated_at', 'DESC')
                    ->get()->unique();

                // dd($CXC_collection_filtro);

                $CXC_filtro_array = $CXC_collection_filtro->toArray();

                $nameFecha = $request->nameFecha;
                $nameFechaVen = $request->nameFechaVen;

                return redirect()->route('vista.modulo.cuentasCobrar.index')
                    ->with('CXC_filtro_array', $CXC_filtro_array)
                    ->with('nameFolio', $nameFolio)
                    ->with('nameKey', $nameKey)
                    ->with('nameMov', $nameMov)
                    ->with('status', $status)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameFechaVen', $nameFechaVen)
                    ->with('nameUsuario', $nameUsuario)
                    ->with('nameSucursal', $nameSucursal)
                    ->with('nameMoneda', $nameMoneda)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal)
                    ->with('fechaInicioVen', $fechaInicioVen)
                    ->with('fechaFinalVen', $fechaFinalVen)
                    ->with('timbrado', $timbrado);

                break;

            case 'Exportar excel':
                $cuentasxcobrar = new PROC_CXCExport($nameFolio, $nameKey, $nameMov, $status, $nameFecha, $nameFechaVen, $nameUsuario, $nameSucursal, $nameMoneda, $timbrado);
                return Excel::download($cuentasxcobrar, 'Cuentas por Cobrar.xlsx');

                break;

            default:
                break;
        }
    }

    public function getSaldoByProveedor(Request $request)
    {
        $proveedorClave = $request->cliente;

        $saldo = PROC_BALANCE::WHERE('balance_companieKey', '=', session('company')->companies_key)->WHERE('balance_branchKey', '=', session('sucursal')->branchOffices_key)->WHERE('balance_account', '=', $proveedorClave)
            ->where('balance_branch', '=', 'CxC')
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

    public function getBalanceCuenta(Request $request)
    {
        $cuenta = $request->cuentaKey;
        $cuentaDB = PROC_MONEY_ACCOUNTS_BALANCE::WHERE('moneyAccountsBalance_moneyAccount', '=', $cuenta)
            ->WHERE('moneyAccountsBalance_status', '=', 'Alta')
            ->select('moneyAccountsBalance_moneyAccount', 'moneyAccountsBalance_accountType', 'moneyAccountsBalance_balance')->first();
        // dd($cuentaDB);

        return response()->json(['status' => true, 'data' => $cuentaDB]);
    }

    public function aplicaFolio(Request $request)
    {
        $aplicaProveedor = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_customer', '=', $request->proveedor)->where('accountsReceivable_movement', '=', $request->movimiento)->where('accountsReceivable_branchOffice', '=',  session('sucursal')->branchOffices_key)->where('accountsReceivable_status', '=',  $this->estatus[1])->where('accountsReceivable_money', '=', $request->moneda)->get();

        if ($aplicaProveedor !== null) {
            $message = 'Folios aplica del proveedor';
            $estatus = 200;
        } else {
            $message = 'No se pudo encontrar folios aplicados';
            $estatus = 500;
        }

        return response()->json(['message' => $message, 'estatus' => $estatus, 'dataProveedor' => $aplicaProveedor]);
    }

    public function getClientes(Request $request)
    {
        $cliente = CAT_CUSTOMERS::where('customers_key', '=', $request->cliente)->first();
        return response()->json($cliente);
    }

    public function timbrarCxcModule(Request $request)
    {
        try {
            $cfdi = new TimbradoController();
            $cfdi->timbrarCXC($request->cxc, $request);
            $mensaje = $cfdi->getMensaje();
            $status = $cfdi->getStatus();

            return response()->json(['status' => $status, 'data' => $mensaje]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'data' => $th->getMessage() . '-' . $th->getLine()]);
        }
    }

    public function validacionesFactura($empresa, $cliente)
    {
        $empresa = CAT_COMPANIES::where('companies_key', $empresa)->first();

        if ($empresa->companies_rfc == '' || $empresa->companies_rfc === null) {
            $message = 'La razón social debe contar con RFC';
            return $message;
        }

        if ($empresa->companies_taxRegime == '' || $empresa->companies_taxRegime === null) {
            $message = 'La razón social debe contar con Regimen Fiscal';
            return $message;
        }

        if ($empresa->companies_routeCertificate == null || $empresa->companies_routeKey == null) {
            $message = 'La razón social debe contar con los certificados CSD y Key';
            return $message;
        }

        //validar que tenga una contraseña para el sat
        if ($empresa->companies_passwordKey == '' || $empresa->companies_passwordKey === null) {
            $message = 'No tiene registrada la contraseña para el certificado';
            return $message;
        }

        //verificar que los archivos esten en la ruta correcta
        if (!Storage::disk('empresas')->exists($empresa->companies_routeKey)) {
            $message = 'El key del certificado no está en la ruta configurada ' . $empresa->companies_routeKey;
            return $message;
        }

        if (!Storage::disk('empresas')->exists($empresa->companies_routeCertificate)) {
            $message = 'El certificado no está en la ruta configurada ' . $empresa->companies_routeCertificate;
            return $message;
        }


        //validaciones para el cliente
        $cliente = CAT_CUSTOMERS::where('customers_key', $cliente)->first();

        //validar que el cliente tenga su regimen fiscal
        if ($cliente->customers_taxRegime == '' || $cliente->customers_taxRegime === null || $cliente->customers_taxRegime === '0') {
            $message = 'El cliente no tiene un regimen fiscal asignado.';
            return $message;
        }

        //validar que el cliente tenga su CFDI
        if ($cliente->customers_identificationCFDI == '' || $cliente->customers_identificationCFDI === null || $cliente->customers_identificationCFDI === '0') {
            $message = 'El cliente no tiene un uso de cfdi asignado.';
            return $message;
        }

        //validar que el cliente tenga su rfc
        if ($cliente->customers_RFC == '' || $cliente->customers_RFC === null) {
            $message = 'El cliente no tiene registrado su RFC y es necesario para facturar.';
            return $message;
        }

        //validar que el cliente tenga un correo
        if ($cliente->customers_mail1 == '' || $cliente->customers_mail1 === null) {
            if ($cliente->customers_mail2 == '' || $cliente->customers_mail2 === null) {
                $message = 'El cliente no tiene registrado un correo electrónico, no recibirá sus facturas.';
                return $message;
            }
        }
    }

    public function getAnticipos(Request $request)
    {
        $anticipos = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', 'Anticipo Clientes')->where('accountsReceivable_status', '=', $this->estatus[1])->where('accountsReceivable_customer', '=', $request->proveedor)->where('accountsReceivable_money', '=', $request->moneda)->get();
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

    public function getAnticipo(Request $request)
    {
        $anticipos = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', 'Anticipo Clientes')->where('accountsReceivable_status', '=', $this->estatus[1])->where('accountsReceivable_customer', '=', $request->proveedor)->where('accountsReceivable_movementID', '=', $request->id)->first();
        // dd($anticipos);
        if ($anticipos !== null) {
            $message = 'Anticipos encontrados';
            $estatus = 200;
        } else {
            $message = 'No se encontraron anticipos';
            $estatus = 500;
        }

        return response()->json(['status' => true, 'data' => $anticipos]);
    }

    public function getFacturasCxC(Request $request)
    {

        // $request->tipo = 'Anticipo Clientes';

        switch ($request->tipo) {
            case 'Anticipo Clientes':
                $facturas = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_stamped', '=', '1')->join('PROC_CFDI', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_id', '=', 'PROC_CFDI.cfdi_moduleID')
                    ->where('PROC_CFDI.cfdi_module', '=', 'CxC')
                    ->where('accountsReceivable_movement', '=', 'Anticipo Clientes')
                    ->where('accountsReceivable_company', '=', session('company')->companies_key)
                    ->where('accountsReceivable_branchOffice', '=', session('sucursal')->branchOffices_key)
                    ->whereIN('accountsReceivable_status', ['FINALIZADO', 'POR AUTORIZAR'])
                    ->get();
                break;

            case 'Aplicación':
                $facturas = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_stamped', '=', '1')->join('PROC_CFDI', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_id', '=', 'PROC_CFDI.cfdi_moduleID')
                    ->where('PROC_CFDI.cfdi_module', '=', 'CxC')
                    ->where('accountsReceivable_movement', '=', 'Aplicación')
                    ->where('accountsReceivable_company', '=', session('company')->companies_key)
                    ->where('accountsReceivable_branchOffice', '=', session('sucursal')->branchOffices_key)
                    ->where('accountsReceivable_status', '=', 'FINALIZADO')
                    ->get();
                break;

            case 'Cobro de Facturas':
                $facturas = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_stamped', '=', '1')->join('PROC_CFDI', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_id', '=', 'PROC_CFDI.cfdi_moduleID')
                    ->where('PROC_CFDI.cfdi_module', '=', 'CxC')
                    ->where('accountsReceivable_movement', '=', 'Cobro de Facturas')
                    ->where('accountsReceivable_company', '=', session('company')->companies_key)
                    ->where('accountsReceivable_branchOffice', '=', session('sucursal')->branchOffices_key)
                    ->where('accountsReceivable_status', '=', 'FINALIZADO')
                    ->get();   # code...
                break;
            default:
                # code...
                break;
        }


        if ($facturas != null) {
            return response()->json(['message' => 'Facturas encontradas', 'estatus' => 200, 'facturas' => $facturas]);
        }
    }

    public function getConceptosByMovimiento(Request $request)
    {
        $movimientoSeleccionado = $request->input('movimiento');
        // dd($movimientoSeleccionado);
        if ($movimientoSeleccionado === null) {
            $conceptos = CONF_MODULES_CONCEPT::where('moduleConcept_status', '=', 'Alta')
                ->where('moduleConcept_module', '=', 'Cuentas por Cobrar')
                ->get();
        } else {
            $conceptos = CONF_MODULES_CONCEPT::join('CONF_MODULES_CONCEPT_MOVEMENT', 'CONF_MODULES_CONCEPT_MOVEMENT.moduleMovement_conceptID', '=', 'CONF_MODULES_CONCEPT.moduleConcept_id')
                ->where('moduleConcept_status', '=', 'Alta')
                ->where('moduleConcept_module', '=', 'Cuentas por Cobrar')
                ->where('moduleMovement_movementName', '=', $movimientoSeleccionado)
                ->get();
        }
        return response()->json($conceptos);
    }

    public function actualizarFolio($tipoMovimiento, $folioAfectar)
    {
        switch ($tipoMovimiento) {
            case 'Anticipo Clientes':
                $consecutivoColumn = 'generalConsecutives_consAdvanceCXC';
                break;
            case 'Aplicación':
                $consecutivoColumn = 'generalConsecutives_consApplicationCXC';
                break;
            case 'Cobro de Facturas':
                $consecutivoColumn = 'generalConsecutives_consCollection';
                break;
            case 'Devolución de Anticipo':
                $consecutivoColumn = 'generalConsecutives_consReturnAdvance';
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
                $folioOrden = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', $tipoMovimiento)
                    ->where('accountsReceivable_branchOffice', '=', $folioAfectar->accountsReceivable_branchOffice)
                    ->max('accountsReceivable_movementID');
                $folioOrden = $folioOrden == null ? 1 : $folioOrden + 1;
                $folioAfectar->accountsReceivable_movementID = $folioOrden;
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

                $folioAfectar->accountsReceivable_movementID = $consecutivo + 1;
                $folioAfectar->update();
            }

            // Eliminar detalles de la cuenta por pagar
            if (in_array($tipoMovimiento, ['Aplicación', 'Cobro de Facturas'])) {
                $detalles = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', '=', $folioAfectar->accountsReceivable_id)->get();

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
