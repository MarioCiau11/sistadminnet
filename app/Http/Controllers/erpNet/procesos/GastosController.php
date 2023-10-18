<?php

namespace App\Http\Controllers\erpNet\procesos;

use App\Exports\PROC_GastosExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CAT_EXPENSE_CONCEPTS;
// use App\Models\catalogos\CAT_FINANCIAL_INSTITUTIONS;
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
use App\Models\modulos\PROC_ASSISTANT;
use App\Models\modulos\PROC_BALANCE;
use App\Models\modulos\PROC_EXPENSES;
use App\Models\modulos\PROC_EXPENSES_DETAILS;
use App\Models\modulos\PROC_LOT_SERIES;
use App\Models\modulos\PROC_MOVEMENT_FLOW;
use App\Models\modulos\PROC_SALES;
use App\Models\modulos\PROC_TREASURY;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class GastosController extends Controller
{
    public $estatus = [
        0 => 'INICIAL',
        1 => 'POR AUTORIZAR',
        2 => 'FINALIZADO',
        3 => 'CANCELADO',
    ];

    public function index()
    {
        $fecha_actual = Carbon::now()->format('Y-m-d');
        $select_users = $this->selectUsuarios();
        $select_sucursales = $this->selectSucursales();
        $selectMonedas = $this->getMonedas();

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }
        $gastos = PROC_EXPENSES::join('CAT_PROVIDERS', 'PROC_EXPENSES.expenses_provider', '=', 'CAT_PROVIDERS.providers_key')
            ->join('CAT_BRANCH_OFFICES', 'PROC_EXPENSES.expenses_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CONF_CREDIT_CONDITIONS', 'PROC_EXPENSES.expenses_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
            ->join('CONF_FORMS_OF_PAYMENT', 'PROC_EXPENSES.expenses_paymentMethod', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key')
            ->join('CONF_MONEY', 'PROC_EXPENSES.expenses_money', '=', 'CONF_MONEY.money_key')
            ->join('CAT_COMPANIES', 'PROC_EXPENSES.expenses_company', '=', 'CAT_COMPANIES.companies_key')
            ->where('PROC_EXPENSES.expenses_company', '=', session('company')->companies_key)
            ->where('PROC_EXPENSES.expenses_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->when($parametro->generalParameters_defaultMoney, function ($query, $parametro) {
                return $query->where('PROC_EXPENSES.expenses_money', '=', $parametro);
            }, function ($query) {
                return $query;
            })
            ->where('PROC_EXPENSES.expenses_user', '=', Auth::user()->username)->orderBy('PROC_EXPENSES.updated_at', 'DESC')

            ->get();
        return view('page.modulos.Gestion_y_Finanzas.Gastos.index-gastos', compact('fecha_actual', 'select_users', 'select_sucursales', 'selectMonedas', 'parametro', 'gastos'));
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
            $permisos = $usuario->getAllPermissions()->where('categoria', '=', 'Gastos')->pluck('name')->toArray();
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
            $select_conceptos = CONF_MODULES_CONCEPT::WHERE('moduleConcept_status', '=', 'Alta')->get();
            $select_forma = CONF_FORMS_OF_PAYMENT::WHERE('formsPayment_status', '=', 'Alta')->get();
            $fecha_actual = Carbon::now()->format('Y-m-d');
            $aticulosSerie = PROC_LOT_SERIES::where('lotSeries_existence', '=', '1')
                ->where("lotSeries_delete", "=", "0")
                ->join('CAT_ARTICLES', 'PROC_LOT_SERIES.lotSeries_article', '=', 'CAT_ARTICLES.articles_key')->get();
            //  dd($aticulosSerie);
            $clientes = CAT_CUSTOMERS::WHERE('customers_status', '=', 'Alta')->get();
            $parametro = CONF_GENERAL_PARAMETERS::join('CONF_MONEY', 'CONF_GENERAL_PARAMETERS.generalParameters_defaultMoney', '=', 'CONF_MONEY.money_key')
                ->select('CONF_GENERAL_PARAMETERS.*', 'CONF_MONEY.money_change')
                ->where('CONF_GENERAL_PARAMETERS.generalParameters_company', '=', session('company')->companies_key)
                ->first();

            $moneyAccounts = CAT_MONEY_ACCOUNTS::join('CAT_FINANCIAL_INSTITUTIONS', 'CAT_FINANCIAL_INSTITUTIONS.instFinancial_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_bank')
                ->join('CONF_MONEY', 'CONF_MONEY.money_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_money')
                ->where('CAT_MONEY_ACCOUNTS.moneyAccounts_status', '=', 'Alta')
                ->where('CAT_MONEY_ACCOUNTS.moneyAccounts_company', '=', session('company')->companies_key)
                ->get();

            $proveedores = CAT_PROVIDERS::WHERE('providers_status', '=', 'Alta')->get();
            $select_condicionPago = CONF_CREDIT_CONDITIONS::WHERE('creditConditions_status', '=', 'Alta')->get();
            $conceptos = CAT_EXPENSE_CONCEPTS::WHERE('expenseConcepts_status', '=', 'Alta')->get();

            // dd($conceptos);

            $antecedentes = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', 'CAT_CUSTOMERS.customers_key')->WHERE('sales_status', '=', 'FINALIZADO')->WHERE('sales_movement', '=', 'Factura')->WHERE('sales_company', '=', session('company')->companies_key)->WHERE('sales_branchOffice', '=', session('sucursal')->branchOffices_key)->get();

            // dd($antecedentes);

            if (isset($request->id) && $request->id != 0) {
                $gasto = PROC_EXPENSES::find($request->id);

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

                //Validamos si el usuario tiene permiso de ver los movimientos ya creados
                if (!$usuario->can($gasto->expenses_movement . ' C')) {
                    return redirect()->route('vista.modulo.gastos.index')->with('status', false)->with('message', 'No tiene permisos para visualizar este movimiento');
                }

                if ($gasto->expenses_moneyAccount != null) {
                    $tipoCuenta2 = CAT_MONEY_ACCOUNTS::where('moneyAccounts_key', '=', $gasto->expenses_moneyAccount)->first();
                } else {
                    $tipoCuenta2 = null;
                }

                $nameProveedor = CAT_PROVIDERS::where('providers_key', '=', $gasto->expenses_provider)->first();
                $gastoD = PROC_EXPENSES_DETAILS::where('expensesDetails_expenseID', '=', $gasto->expenses_id)->get();
                $cuentaBanco = CAT_MONEY_ACCOUNTS::join('CAT_FINANCIAL_INSTITUTIONS', 'CAT_FINANCIAL_INSTITUTIONS.instFinancial_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_bank')->WHERE('CAT_FINANCIAL_INSTITUTIONS.instFinancial_name', '=', $gasto->expenses_moneyAccount)->first();

                $primerFlujoDeGastos = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
                    ->WHERE('movementFlow_originID', '=', $gasto->expenses_id)
                    ->WHERE('movementFlow_movementOriginID', '=', $gasto->expenses_movementID)
                    ->WHERE('movementFlow_moduleOrigin', '=', 'Gastos')
                    ->get();
                // dd($primerFlujoDeGastos);


                if (count($primerFlujoDeGastos) === 0) {
                    $primerFlujodeGasto = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
                        ->WHERE('movementFlow_destinityID', '=', $gasto->expenses_id)
                        ->WHERE('movementFlow_movementDestinityID', '=', $gasto->expenses_movementID)
                        ->WHERE('movementFlow_moduleDestiny', '=', 'Gastos')
                        ->get();
                }

                $infoProveedor = CAT_PROVIDERS::join('CONF_CREDIT_CONDITIONS', 'CAT_PROVIDERS.providers_creditCondition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id', 'left outer')->where('providers_key', '=', $gasto->expenses_provider)->first();

                $monedasMov = CONF_MONEY::where('money_status', '=', 'Alta')->orderBy('money_key', 'desc')->get();

                $movimientosProveedor = PROC_ACCOUNTS_PAYABLE::whereIn("accountsPayable_movement", ["Entrada por Compra", "Factura de Gasto", "Anticipo"])->where('accountsPayable_provider', '=', $gasto->expenses_provider)->where("accountsPayable_balance", ">", '0')->where("accountsPayable_company", "=", session('company')->companies_key)->where("accountsPayable_status", "=", 'POR AUTORIZAR')->get();

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

                return view('page.modulos.Gestion_y_Finanzas.Gastos.create-gastos', compact('selectMonedas', 'select_conceptos', 'select_forma', 'fecha_actual', 'clientes', 'parametro', 'moneyAccounts', 'proveedores', 'select_condicionPago', 'conceptos', 'gasto', 'nameProveedor', 'gastoD', 'cuentaBanco', 'primerFlujoDeGastos', 'aticulosSerie', 'tipoCuenta2', 'movimientos', 'antecedentes', 'infoProveedor', 'monedasMov', 'movimientosProveedor', 'saldoGeneral', 'movimientos'));
            } else {
                $gasto = null;
                $nameProveedor = null;
                $gastoD = null;
                $cuentaBanco = null;
                $infoProveedor = null;
            }

            return view('page.modulos.Gestion_y_Finanzas.Gastos.create-gastos', compact('selectMonedas', 'select_conceptos', 'select_forma', 'fecha_actual', 'clientes', 'parametro', 'moneyAccounts', 'proveedores', 'select_condicionPago', 'conceptos', 'gasto', 'nameProveedor', 'gastoD', 'cuentaBanco', 'aticulosSerie', 'movimientos', 'antecedentes'));
        } catch (\Throwable $th) {
            //throw $th;
            return redirect()->back()->with('status', false)->with('message', 'El movimiento no fue encontrado');
        }
    }

    public function store(Request $request)
    {
        //  dd($request->all());

        $gasto_request = $request->except('_token');

        // dd($importe);
        $id = $request->id;
        $copiaRequest = $request->copiar;
        try {
            if ($id == 0 || $copiaRequest == 'copiar') {
                $gasto = new PROC_EXPENSES();
            } else {
                $gasto = PROC_EXPENSES::WHERE('expenses_id', '=', $id)->first();
            }

            $gasto->expenses_movement = $gasto_request['movimientos'];
            $gasto->expenses_issueDate
                = \Carbon\Carbon::now();
            $gasto->expenses_money = $gasto_request['nameMoneda'];
            $gasto->expenses_typeChange = $gasto_request['nameTipoCambio'];
            $gasto->expenses_provider = $gasto_request['proveedorKey'];
            $gasto->expenses_observations = $gasto_request['observaciones'];
            $gasto->expenses_moneyAccount = $gasto_request['cuentaKey'];
            $gasto->expenses_typeAccount = $gasto_request['tipoCuenta'];
            $gasto->expenses_paymentMethod = $gasto_request['formaPago'];
            $gasto->expenses_condition = $gasto_request['proveedorCondicionPago'];
            $gasto->expenses_expiration = $gasto_request['proveedorFechaVencimiento'];

            $gasto->expenses_amount = $gasto_request['subTotalCompleto'];
            $gasto->expenses_taxes = $gasto_request['impuestosCompleto'];
            $gasto->expenses_total = $gasto_request['totalCompleto'];

            $gasto->expenses_antecedents = isset($gasto_request['antecedentes']) ? ($gasto_request['antecedentes'] == 'on' ? 1 : 0) : 0;
            $gasto->expenses_antecedentsName = $gasto_request['antecedentesName'];
            $gasto->expenses_fixedAssets = isset($gasto_request['activoFijo']) ? ($gasto_request['activoFijo'] == 'on' ? 1 : 0) : 0;
            $gasto->expenses_fixedAssetsName = $gasto_request['activoFijoNombre'];
            $gasto->expenses_fixedAssetsSerie = $gasto_request['activoFijoSerie'];
            $gasto->expenses_company =  session('company')->companies_key;
            $gasto->expenses_branchOffice = session('sucursal')->branchOffices_key;
            $gasto->expenses_user = Auth::user()->username;
            $gasto->expenses_status = $this->estatus[0];
            $gasto->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $gasto->updated_at = Carbon::now()->format('Y-m-d H:i:s');




            $isCreate = $gasto->save();

            $lastId = $id == 0 ? PROC_EXPENSES::latest('expenses_id')->first()->expenses_id : $id;


            //Convertimos el string de conceptos a un objeto facil de manejar en el controlador
            $conceptos = $gasto_request['dataConceptosJson'];
            $conceptos = json_decode($conceptos, true);
            $conceptosLlave = array_keys($conceptos);

            //Eliminamos los conceptos que no sean necesarios        
            $conceptosDelete = json_decode($gasto_request['dataConceptosDelete'], true);


            if ($conceptosDelete  != null) {
                foreach ($conceptosDelete as $concepto) {
                    $detalleGasto = PROC_EXPENSES_DETAILS::where('expensesDetails_id', $concepto)->first();
                    $detalleGasto->delete();
                }
            }



            if ($conceptos !== null) {
                foreach ($conceptosLlave as $key => $concepto) {
                    if (isset($conceptos[$concepto]['id'])) {
                        $detalleGasto = PROC_EXPENSES_DETAILS::where('expensesDetails_id', '=', $conceptos[$concepto]['id'])->first();
                    } else {
                        $detalleGasto = new PROC_EXPENSES_DETAILS();
                    }

                    $detalleGasto->expensesDetails_expenseID = $lastId;
                    $detalleGasto->expensesDetails_establishment = $conceptos[$concepto]['establecimiento'];
                    $detalleGasto->expensesDetails_concept = $conceptos[$concepto]['concepto'];
                    $detalleGasto->expensesDetails_reference = $conceptos[$concepto]['referencia'];
                    $detalleGasto->expensesDetails_quantity = $conceptos[$concepto]['cantidad'];
                    $detalleGasto->expensesDetails_price = $conceptos[$concepto]['precio'];
                    $detalleGasto->expensesDetails_amount = $conceptos[$concepto]['importe'];
                    $detalleGasto->expensesDetails_vat = $conceptos[$concepto]['porcenta_iva'];
                    $detalleGasto->expensesDetails_vatAmount = $conceptos[$concepto]['iva'];
                    $detalleGasto->expensesDetails_retention1 = $conceptos[$concepto]['porcentaje_retencion'];
                    $detalleGasto->expensesDetails_retentionISR = $conceptos[$concepto]['retencion'];
                    $detalleGasto->expensesDetails_retention2 = $conceptos[$concepto]['porcentaje_retencion2'];
                    $detalleGasto->expensesDetails_retentionIVA = $conceptos[$concepto]['retencion2'];
                    $detalleGasto->expensesDetails_total = $conceptos[$concepto]['total'];
                    $detalleGasto->expensesDetails_company = session('company')->companies_key;
                    $detalleGasto->expensesDetails_branchOffice = session('sucursal')->branchOffices_key;

                    $isCreate = $detalleGasto->save();
                }
            }

            if ($isCreate) {
                $message = $id == 0 ? 'La ' . $gasto->expenses_movement . ' se ha creado correctamente' : 'La ' . $gasto->expenses_movement . ' se ha actualizado correctamente';
                $status = true;
            } else {
                $message = $id == 0 ? 'Error al crear el Gasto' : 'Error al actualizar el Gasto';
                $status = false;
            }
        } catch (\Throwable $th) {
            $message = $id == 0 ? "Por favor, vaya con el administrador de sistemas, no se pudo crear el Gasto" : "Por favor, vaya con el administrador de sistemas, no se pudo actualizar el gasto";
            return redirect()->route('vista.modulo.gastos.create-gasto', $id)->with('message', $message)->with('status', false);
        }

        return redirect()->route('vista.modulo.gastos.create-gasto', $id == 0 ? $lastId : $id)->with('message', $message)->with('status', $status);
    }

    public function afectar(Request $request)
    {
        $gasto_request = $request->except('_token');
        $id = $request->id;

        $cuenta = $gasto_request['cuentaKey'];
        $tipoCuenta = $gasto_request['tipoCuenta'];
        $tipoMovimiento = $gasto_request['movimientos'];

        if ($tipoMovimiento == 'Reposición Caja' || $tipoMovimiento == 'Factura de Gasto') {
            //verificar que la forma de pago sea de la misma que el movimiento
            $formaPago = CONF_FORMS_OF_PAYMENT::WHERE('formsPayment_key', '=', $request['formaPago'])->first();

            $moneda = CONF_MONEY::WHERE('money_key', '=', $request['nameMoneda'])->first();
            // DD(trim($formaPago->formsPayment_money), $moneda->money_keySat);

            if (trim($formaPago->formsPayment_money) != $moneda->money_keySat) {
                $message = 'La forma de pago no es compatible con la moneda del movimiento';
                $status = 400;
                $lastId = $request['id'];

                return response()->json(['status' => $status, 'mensaje' => $message, 'id' =>  $lastId]);
            }
        }



        if ($tipoCuenta == 'Caja' && $tipoMovimiento == 'Reposición Caja') {
            $validarCuenta = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', $cuenta)->where('moneyAccountsBalance_status', 'Alta')->WHERE('moneyAccountsBalance_company', '=', session('company')->companies_key)->first();

            // ver si la caja tiene saldo
            $saldo = $validarCuenta->moneyAccountsBalance_balance;
            $fondo = floatval($saldo);
            $importa_sinSigno = trim($gasto_request['totalCompleto'], '$');
            $importe_sinSigno = str_replace(',', '', $importa_sinSigno);
            // dd($importe_sinSigno);
            $importe = floatval($importe_sinSigno);
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

        if ($procesar === true) {
            try {
                if ($id == 0) {
                    $gasto = new PROC_EXPENSES();
                } else {
                    $gasto = PROC_EXPENSES::WHERE('expenses_id', '=', $id)->first();
                }

                $gasto->expenses_movement = $gasto_request['movimientos'];
                $gasto->expenses_issueDate
                    = \Carbon\Carbon::now();
                $gasto->expenses_money = $gasto_request['nameMoneda'];
                $gasto->expenses_typeChange = $gasto_request['nameTipoCambio'];
                $gasto->expenses_provider = $gasto_request['proveedorKey'];
                $gasto->expenses_observations = $gasto_request['observaciones'];
                $gasto->expenses_moneyAccount = $gasto_request['cuentaKey'];
                $gasto->expenses_typeAccount = $gasto_request['tipoCuenta'];
                $gasto->expenses_paymentMethod = $gasto_request['formaPago'];
                $gasto->expenses_condition = $gasto_request['proveedorCondicionPago'];
                $gasto->expenses_expiration = $gasto_request['proveedorFechaVencimiento'];
                $gasto->expenses_amount = $gasto_request['subTotalCompleto'];
                $gasto->expenses_taxes = $gasto_request['impuestosCompleto'];
                $gasto->expenses_total = $gasto_request['totalCompleto'];
                $gasto->expenses_antecedents = isset($gasto_request['antecedentes']) ? ($gasto_request['antecedentes'] == 'on' ? 1 : 0) : 0;
                $gasto->expenses_antecedentsName = $gasto_request['antecedentesName'];
                $gasto->expenses_fixedAssets = isset($gasto_request['activoFijo']) ? ($gasto_request['activoFijo'] == 'on' ? 1 : 0) : 0;
                $gasto->expenses_fixedAssetsName = $gasto_request['activoFijoNombre'];
                $gasto->expenses_fixedAssetsSerie = $gasto_request['activoFijoSerie'];
                $gasto->expenses_company =  session('company')->companies_key;
                $gasto->expenses_branchOffice = session('sucursal')->branchOffices_key;
                $gasto->expenses_user = Auth::user()->username;
                $gasto->expenses_status = $this->estatus[2];
                $gasto->created_at = Carbon::now()->format('Y-m-d H:i:s');
                $gasto->updated_at = Carbon::now()->format('Y-m-d H:i:s');



                if ($id == 0) {
                    $isCreate = $gasto->save();
                    $lastId = PROC_EXPENSES::latest('expenses_id')->first()->expenses_id;
                } else {
                    $isCreate = $gasto->update();
                    $lastId = $gasto->expenses_id;
                }



                $folioAfectar = PROC_EXPENSES::where('expenses_id', $lastId)->first();

                $tipoMovimiento = $gasto_request['movimientos'];

                $this->actualizarFolio($tipoMovimiento, $folioAfectar);

                // switch ($tipoMovimiento) {
                //     case 'Reposición Caja':
                //         $folioMov = PROC_EXPENSES::where('expenses_movement', '=', 'Reposición Caja')->where('expenses_branchOffice', '=', $folioAfectar->expenses_branchOffice)->max('expenses_movementID');
                //         $folioMov = $folioMov == null ? 1 : $folioMov + 1;
                //         $folioAfectar->expenses_movementID = $folioMov;
                //         $folioAfectar->update();
                //         break;
                //     case 'Factura de Gasto':
                //         $folioMov = PROC_EXPENSES::where('expenses_movement', '=', 'Factura de Gasto')->where('expenses_branchOffice', '=', $folioAfectar->expenses_branchOffice)->max('expenses_movementID');
                //         $folioMov = $folioMov == null ? 1 : $folioMov + 1;
                //         $folioAfectar->expenses_movementID = $folioMov;
                //         $folioAfectar->update();
                //         break;
                // }
                //Convertimos el string de conceptos a un objeto facil de manejar en el controlador
                $conceptos = $gasto_request['dataConceptosJson'];
                //    dd($conceptos);
                $conceptos = json_decode($conceptos, true);
                $conceptosLlave = array_keys($conceptos);

                $conceptosDelete = json_decode($gasto_request['dataConceptosDelete'], true);


                if ($conceptosDelete  != null) {
                    foreach ($conceptosDelete as $concepto) {
                        $detalleGasto = PROC_EXPENSES_DETAILS::where('expensesDetails_id', $concepto)->first();
                        $detalleGasto->delete();
                    }
                }

                if ($conceptos !== null) {
                    foreach ($conceptosLlave as $key => $concepto) {
                        if (isset($conceptos[$concepto]['id'])) {
                            $detalleGasto = PROC_EXPENSES_DETAILS::where('expensesDetails_id', '=', $conceptos[$concepto]['id'])->first();
                        } else {
                            $detalleGasto = new PROC_EXPENSES_DETAILS();
                        }

                        $detalleGasto->expensesDetails_expenseID = $lastId;
                        $detalleGasto->expensesDetails_establishment = $conceptos[$concepto]['establecimiento'];
                        $detalleGasto->expensesDetails_concept = $conceptos[$concepto]['concepto'];
                        $detalleGasto->expensesDetails_reference = $conceptos[$concepto]['referencia'];
                        $detalleGasto->expensesDetails_quantity = $conceptos[$concepto]['cantidad'];
                        $detalleGasto->expensesDetails_price = $conceptos[$concepto]['precio'];
                        $detalleGasto->expensesDetails_amount = $conceptos[$concepto]['importe'];
                        $detalleGasto->expensesDetails_vat = $conceptos[$concepto]['porcenta_iva'];
                        $detalleGasto->expensesDetails_vatAmount =  $conceptos[$concepto]['iva'];
                        $detalleGasto->expensesDetails_retention1 = $conceptos[$concepto]['porcentaje_retencion'];
                        $detalleGasto->expensesDetails_retentionISR = $conceptos[$concepto]['retencion'];
                        $detalleGasto->expensesDetails_retention2 = $conceptos[$concepto]['porcentaje_retencion2'];
                        $detalleGasto->expensesDetails_retentionIVA = $conceptos[$concepto]['retencion2'];
                        $detalleGasto->expensesDetails_total = $conceptos[$concepto]['total'];
                        $detalleGasto->expensesDetails_company = session('company')->companies_key;
                        $detalleGasto->expensesDetails_branchOffice = session('sucursal')->branchOffices_key;
                        // dd($detalleGasto);

                        $isCreate = $detalleGasto->save();
                        //  dd($isCreate);
                    }
                }

                //agregar CxP
                $this->agregarCxP($folioAfectar->expenses_id);
                //agregar CxP pendiente
                $this->agregarCxPP($folioAfectar->expenses_id);
                //agregamos tesoreria
                $this->agregarTesoreria($folioAfectar->expenses_id);
                //agregar mov
                $this->agregarMov($folioAfectar->expenses_id);
                //agregar aux
                $this->auxiliar($folioAfectar->expenses_id);
                //agregar saldo
                $this->agregarSaldo($folioAfectar->expenses_id);

                if ($isCreate) {
                    $message = $id == 0 ? 'La ' . $tipoMovimiento . ' se ha creado correctamente' : 'La ' . $tipoMovimiento . ' se ha actualizado correctamente';
                    $status = 200;
                } else {
                    $message = $id == 0 ? 'Error al afectar el Gasto' : 'Error al afectar el Gasto';
                    $status = 500;
                }
            } catch (\Throwable $th) {
                $status = 500;
                $message = $th->getMessage();
            }
        }


        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
    }

    public function agregarCxP($folio)
    {

        $folioAfectar = PROC_EXPENSES::where('expenses_id', '=', $folio)->first();

        //  dd($folioAfectar);
        if ($folioAfectar->expenses_status == $this->estatus[2] && $folioAfectar->expenses_movement == 'Factura de Gasto') {

            $cuentaPagar = new PROC_ACCOUNTS_PAYABLE();
            $cuentaPagar->accountsPayable_movement = $folioAfectar->expenses_movement;
            $cuentaPagar->accountsPayable_movementID = $folioAfectar->expenses_movementID;
            $cuentaPagar->accountsPayable_issuedate = Carbon::parse($folioAfectar->expenses_issueDate)->format('Y-m-d H:i:s');
            $cuentaPagar->accountsPayable_money = $folioAfectar->expenses_money;
            $cuentaPagar->accountsPayable_typeChange = $folioAfectar->expenses_typeChange;
            $cuentaPagar->accountsPayable_moneyAccount = $folioAfectar->expenses_moneyAccount;
            $cuentaPagar->accountsPayable_provider = $folioAfectar->expenses_provider;
            $cuentaPagar->accountsPayable_condition = $folioAfectar->expenses_condition;
            $vencimiento = $folioAfectar->expenses_expiration;
            $vencimiento2 = Carbon::parse($vencimiento)->format('Y-m-d H:i:s');
            $cuentaPagar->accountsPayable_expiration = $vencimiento2;

            $emision = Carbon::parse($folioAfectar->expenses_issueDate)->format('Y-m-d');

            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
            $vencimientoN = Carbon::parse($folioAfectar->expenses_expiration)->format('Y-m-d');
            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimientoN);

            $diasMoratorio = $shippingDate->diffInDays($currentDate);
            $cuentaPagar->accountsPayable_moratoriumDays = '-' . $diasMoratorio;

            $cuentaPagar->accountsPayable_formPayment = $folioAfectar->expenses_paymentMethod;
            $cuentaPagar->accountsPayable_amount = $folioAfectar->expenses_amount;
            $cuentaPagar->accountsPayable_taxes = $folioAfectar->expenses_taxes;
            $cuentaPagar->accountsPayable_total = $folioAfectar->expenses_total;


            //buscamos las retenciones
            $retenciones = PROC_EXPENSES_DETAILS::where('expensesDetails_expenseID', '=', $folioAfectar->expenses_id)->get();

            $ret = 0;
            $ret2 = 0;
            foreach ($retenciones as $key => $retencion) {
                $ret += $retencion->expensesDetails_retentionISR;
                $ret2 += $retencion->expensesDetails_retentionIVA;
            }
            $cuentaPagar->accountsPayable_retention = $ret;
            $cuentaPagar->accountsPayable_retention2 = $ret2;
            $cuentaPagar->accountsPayable_reference = 'Factura de Gasto';
            $cuentaPagar->accountsPayable_balance = $folioAfectar->expenses_total;
            $cuentaPagar->accountsPayable_company = $folioAfectar->expenses_company;
            $cuentaPagar->accountsPayable_branchOffice = $folioAfectar->expenses_branchOffice;
            $cuentaPagar->accountsPayable_user = $folioAfectar->expenses_user;
            $cuentaPagar->accountsPayable_status = $this->estatus[1];
            $cuentaPagar->accountsPayable_origin = $folioAfectar->expenses_movement;
            $cuentaPagar->accountsPayable_originID = $folioAfectar->expenses_movementID;
            $cuentaPagar->accountsPayable_originType = 'Factura de Gasto';
            $cuentaPagar->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $cuentaPagar->updated_at = Carbon::now()->format('Y-m-d H:i:s');
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

    public function agregarCxPP($folio)
    {
        $folioAfectar = PROC_EXPENSES::where('expenses_id', '=', $folio)->first();

        // dd($folioAfectar);
        if ($folioAfectar->expenses_status == $this->estatus[2] && $folioAfectar->expenses_movement == 'Factura de Gasto') {

            //agregamos una nueva cuenta por pagar para la compra
            $cuentaPagar = new PROC_ACCOUNTS_PAYABLE_P();
            $cuentaPagar->accountsPayableP_movement = $folioAfectar->expenses_movement;
            $cuentaPagar->accountsPayableP_movementID = $folioAfectar->expenses_movementID;
            $cuentaPagar->accountsPayableP_issuedate = Carbon::parse($folioAfectar->expenses_issueDate)->format('Y-m-d H:i:s');
            $cuentaPagar->accountsPayableP_expiration =  Carbon::parse($folioAfectar->expenses_expiration)->format('Y-m-d H:i:s');

            //dias credito y moratorio
            $emision = Carbon::parse($folioAfectar->expenses_issueDate)->format('Y-m-d');
            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
            $vencimiento = Carbon::parse($folioAfectar->expenses_expiration)->format('Y-m-d');
            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

            $diasCredito = $currentDate->diffInDays($shippingDate);
            $diasMoratorio = $shippingDate->diffInDays($currentDate);
            $cuentaPagar->accountsPayableP_creditDays = $diasCredito;
            $cuentaPagar->accountsPayableP_moratoriumDays = '-' . $diasMoratorio;
            $cuentaPagar->accountsPayableP_money = $folioAfectar->expenses_money;
            $cuentaPagar->accountsPayableP_typeChange = $folioAfectar->expenses_typeChange;
            $cuentaPagar->accountsPayableP_moneyAccount = $folioAfectar->expenses_moneyAccount;

            $cuentaPagar->accountsPayableP_provider = $folioAfectar->expenses_provider;
            $cuentaPagar->accountsPayableP_provider = $folioAfectar->expenses_provider;
            $cuentaPagar->accountsPayableP_condition = $folioAfectar->expenses_condition;

            $cuentaPagar->accountsPayableP_formPayment = $folioAfectar->expenses_paymentMethod;
            $cuentaPagar->accountsPayableP_amount = $folioAfectar->expenses_amount;
            $cuentaPagar->accountsPayableP_taxes = $folioAfectar->expenses_taxes;
            $cuentaPagar->accountsPayableP_total = $folioAfectar->expenses_total;
            $cuentaPagar->accountsPayableP_balanceTotal = $folioAfectar->expenses_total;
            $cuentaPagar->accountsPayableP_reference = 'Factura de Gasto';
            $cuentaPagar->accountsPayableP_balance = $folioAfectar->expenses_total;
            $cuentaPagar->accountsPayableP_company = $folioAfectar->expenses_company;
            $cuentaPagar->accountsPayableP_branchOffice = $folioAfectar->expenses_branchOffice;
            $cuentaPagar->accountsPayableP_user = $folioAfectar->expenses_user;
            $cuentaPagar->accountsPayableP_status = $this->estatus[1];
            $cuentaPagar->accountsPayableP_origin = $folioAfectar->expenses_movement;
            $cuentaPagar->accountsPayableP_originID = $folioAfectar->expenses_movementID;
            $cuentaPagar->accountsPayableP_originType = 'Factura de Gasto';


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
        $folioAfectar = PROC_EXPENSES::where('expenses_id', $folio)->first();
        // dd($folioAfectar);
        if ($folioAfectar->expenses_status == $this->estatus[2] && $folioAfectar->expenses_movement == 'Reposición Caja' && $folioAfectar->expenses_typeAccount == 'Caja') {

            //agregamos a tesoreria
            $tesoreria = new PROC_TREASURY();

            $folioEgreso = PROC_TREASURY::where('treasuries_movement', '=', 'Egreso')->where('treasuries_branchOffice', '=', $folioAfectar->expenses_branchOffice)->max('treasuries_movementID');
            $folioEgreso = $folioEgreso == null ? 1 : $folioEgreso + 1;
            $tesoreria->treasuries_movement = 'Egreso';
            $tesoreria->treasuries_movementID = $folioEgreso;
            $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->expenses_issueDate)->format('Y-m-d H:i:s');
            $tesoreria->treasuries_money = $folioAfectar->expenses_money;
            $tesoreria->treasuries_typeChange = $folioAfectar->expenses_typeChange;
            $tesoreria->treasuries_moneyAccount = $folioAfectar->expenses_moneyAccount;
            $tesoreria->treasuries_moneyAccountOrigin = $folioAfectar->expenses_moneyAccount;
            $tesoreria->treasuries_paymentMethod = $folioAfectar->expenses_paymentMethod;
            $tesoreria->treasuries_beneficiary = $folioAfectar->expenses_provider;
            $tesoreria->treasuries_reference = 'Reposición Caja';
            $tesoreria->treasuries_amount = $folioAfectar->expenses_amount;
            $tesoreria->treasuries_taxes = $folioAfectar->expenses_taxes;
            $tesoreria->treasuries_total = $folioAfectar->expenses_total;
            $tesoreria->treasuries_company = $folioAfectar->expenses_company;
            $tesoreria->treasuries_branchOffice = $folioAfectar->expenses_branchOffice;
            $tesoreria->treasuries_user = $folioAfectar->expenses_user;
            $tesoreria->treasuries_status = $this->estatus[2];
            $tesoreria->treasuries_originType = 'Gastos';
            $tesoreria->treasuries_origin = $folioAfectar->expenses_movement;
            $tesoreria->treasuries_originID = $folioAfectar->expenses_movementID;
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

        // dd($folioAfectar);

        if ($folioAfectar->expenses_status == $this->estatus[2] && $folioAfectar->expenses_movement == 'Reposición Caja' && $folioAfectar->expenses_typeAccount == 'Banco') {
            $tesoreria = new PROC_TREASURY();
            $folioSolicitud = PROC_TREASURY::where('treasuries_movement', '=', 'Sol. de Cheque/Transferencia')->where('treasuries_branchOffice', '=', $folioAfectar->expenses_branchOffice)->max('treasuries_movementID');
            $folioSolicitud = $folioSolicitud == null ? 1 : $folioSolicitud + 1;
            $tesoreria->treasuries_movement = 'Sol. de Cheque/Transferencia';
            $tesoreria->treasuries_movementID = $folioSolicitud;
            $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->expenses_issueDate)->format('Y-m-d H:i:s');
            $tesoreria->treasuries_money = $folioAfectar->expenses_money;
            $tesoreria->treasuries_typeChange = $folioAfectar->expenses_typeChange;
            $tesoreria->treasuries_moneyAccount = $folioAfectar->expenses_moneyAccount;
            $tesoreria->treasuries_moneyAccountOrigin = $folioAfectar->expenses_moneyAccount;
            $tesoreria->treasuries_paymentMethod = $folioAfectar->expenses_paymentMethod;
            $tesoreria->treasuries_beneficiary = $folioAfectar->expenses_provider;
            $tesoreria->treasuries_reference = 'Reposición Caja';
            $tesoreria->treasuries_amount = $folioAfectar->expenses_amount;
            $tesoreria->treasuries_taxes = $folioAfectar->expenses_taxes;
            $tesoreria->treasuries_total = $folioAfectar->expenses_total;
            $tesoreria->treasuries_accountBalance = $folioAfectar->expenses_total;
            $tesoreria->treasuries_company = $folioAfectar->expenses_company;
            $tesoreria->treasuries_branchOffice = $folioAfectar->expenses_branchOffice;
            $tesoreria->treasuries_user = $folioAfectar->expenses_user;
            $tesoreria->treasuries_status = $this->estatus[1];
            $tesoreria->treasuries_originType = 'Gastos';
            $tesoreria->treasuries_origin = $folioAfectar->expenses_movement;
            $tesoreria->treasuries_originID = $folioAfectar->expenses_movementID;
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

    public function agregarMov($folio)
    {
        $folioAfectar = PROC_EXPENSES::where('expenses_id', '=', $folio)->first();

        // dd($folioAfectar);
        if ($folioAfectar->expenses_status == $this->estatus[2] && $folioAfectar->expenses_movement == 'Factura de Gasto') {

            $movPosterior = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movementID', '=', $folioAfectar->expenses_movementID)->where('accountsPayable_movement', '=', 'Factura de Gasto')->where('accountsPayable_branchOffice', '=', $folioAfectar->expenses_branchOffice)->first();
            $movimiento = new PROC_MOVEMENT_FLOW();
            $movimiento->movementFlow_branch = $folioAfectar->expenses_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->expenses_company;
            $movimiento->movementFlow_moduleOrigin = 'Gastos';
            $movimiento->movementFlow_originID = $folioAfectar->expenses_id;
            $movimiento->movementFlow_movementOrigin = $folioAfectar->expenses_movement;
            $movimiento->movementFlow_movementOriginID = $folioAfectar->expenses_movementID;
            $movimiento->movementFlow_moduleDestiny = 'CxP';
            $movimiento->movementFlow_destinityID = $movPosterior->accountsPayable_id;
            $movimiento->movementFlow_movementDestinity = $movPosterior->accountsPayable_movement;
            $movimiento->movementFlow_movementDestinityID = $movPosterior->accountsPayable_movementID;
            $movimiento->movementFlow_cancelled = 0;
            $movimiento->save();
        }

        if ($folioAfectar->expenses_status == $this->estatus[2] && $folioAfectar->expenses_movement == 'Reposición Caja') {
            $movimiento = new PROC_MOVEMENT_FLOW();
            $movPosterior = PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->expenses_movementID)->where('treasuries_originType', '=', 'GASTOS')->where('treasuries_branchOffice', '=', $folioAfectar->expenses_branchOffice)->first();
            $movimiento->movementFlow_branch = $folioAfectar->expenses_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->expenses_company;
            $movimiento->movementFlow_moduleOrigin = 'Gastos';
            $movimiento->movementFlow_originID = $folioAfectar->expenses_id;
            $movimiento->movementFlow_movementOrigin = $folioAfectar->expenses_movement;
            $movimiento->movementFlow_movementOriginID = $folioAfectar->expenses_movementID;
            $movimiento->movementFlow_moduleDestiny = 'Din';
            $movimiento->movementFlow_destinityID = $movPosterior->treasuries_id;
            $movimiento->movementFlow_movementDestinity = $movPosterior->treasuries_movement;
            $movimiento->movementFlow_movementDestinityID = $movPosterior->treasuries_movementID;
            $movimiento->movementFlow_cancelled = 0;
            $movimiento->save();
        }
    }


    public function auxiliar($folio)
    {
        $folioAfectar = PROC_EXPENSES::where('expenses_id', '=', $folio)->first();

        // dd($folioAfectar);
        if ($folioAfectar->expenses_status == $this->estatus[2] && $folioAfectar->expenses_movement == 'Factura de Gasto') {

            //agregar datos a aux
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->expenses_company;
            $auxiliar->assistant_branchKey = $folioAfectar->expenses_branchOffice;
            $auxiliar->assistant_branch = 'CxP';


            //buscamos el modulo de cxp
            $cxp = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movementID', '=', $folioAfectar->expenses_movementID)->where('accountsPayable_branchOffice', '=', $folioAfectar->expenses_branchOffice)->where('accountsPayable_movement', '=', $folioAfectar->expenses_movement)->first();

            $auxiliar->assistant_movement = $cxp->accountsPayable_movement;
            $auxiliar->assistant_movementID = $cxp->accountsPayable_movementID;
            $auxiliar->assistant_module = 'CxP';
            $auxiliar->assistant_moduleID = $cxp->accountsPayable_id;
            $auxiliar->assistant_money = $folioAfectar->expenses_money;
            $auxiliar->assistant_typeChange = $folioAfectar->expenses_typeChange;
            $auxiliar->assistant_account = $folioAfectar->expenses_provider;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = $folioAfectar->expenses_total;
            $auxiliar->assistant_payment = null;
            $auxiliar->assistant_apply = $cxp->accountsPayable_movement;
            $auxiliar->assistant_applyID =  $cxp->accountsPayable_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = "Factura de Gasto";


            $auxiliar->save();
        }



        if ($folioAfectar->expenses_status == $this->estatus[2] && $folioAfectar->expenses_movement == 'Reposición Caja'  && $folioAfectar->expenses_typeAccount == 'Caja') {
            //agregar datos a aux
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->expenses_company;
            $auxiliar->assistant_branchKey = $folioAfectar->expenses_branchOffice;
            $auxiliar->assistant_branch = 'Din';


            $tesoreria = PROC_TREASURY::where('treasuries_originID', '=', $folioAfectar->expenses_movementID)->where('treasuries_originType', '=', 'GASTOS')->where('treasuries_branchOffice', '=', $folioAfectar->expenses_branchOffice)->first();

            $auxiliar->assistant_movement = $tesoreria->treasuries_movement;
            $auxiliar->assistant_movementID = $tesoreria->treasuries_movementID;
            $auxiliar->assistant_module = 'Din';
            $auxiliar->assistant_moduleID = $tesoreria->treasuries_id;
            $auxiliar->assistant_money = $folioAfectar->expenses_money;
            $auxiliar->assistant_typeChange = $folioAfectar->expenses_typeChange;
            $auxiliar->assistant_account = $folioAfectar->expenses_moneyAccount;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = null;
            $auxiliar->assistant_payment = $folioAfectar->expenses_total;
            $auxiliar->assistant_apply = $tesoreria->treasuries_movement;
            $auxiliar->assistant_applyID = $tesoreria->treasuries_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = "Factura de Gasto";

            $auxiliar->save();
        }
    }

    //falta checar el saldo cuando sea caja chica
    public function agregarSaldo($folio)
    {
        $folioAfectar = PROC_EXPENSES::where('expenses_id', '=', $folio)->first();
        // dd($folioAfectar);
        if ($folioAfectar->expenses_status == $this->estatus[2] && $folioAfectar->expenses_movement == 'Factura de Gasto') {
            //agregamos el saldo del proveedor en la tabla de saldos
            $saldo = PROC_BALANCE::where('balance_account', '=', $folioAfectar->expenses_provider)->where('balance_branchKey', '=', $folioAfectar->expenses_branchOffice)->where('balance_companieKey', '=', $folioAfectar->expenses_company)->where('balance_money', '=', $folioAfectar->expenses_money)->where('balance_branch', '=', "CxP")->first();

            if ($saldo == null) {
                $saldo = new PROC_BALANCE();
                $saldo->balance_companieKey = $folioAfectar->expenses_company;
                $saldo->balance_branchKey = $folioAfectar->expenses_branchOffice;
                $saldo->balance_branch = 'CxP';
                $saldo->balance_money = $folioAfectar->expenses_money;
                $saldo->balance_account = $folioAfectar->expenses_provider;
                $saldo->balance_balance = $folioAfectar->expenses_total;
                $saldo->balance_reconcile = $saldo->balance_balance;
                $create = $saldo->save();
            } else {
                $saldo->balance_balance = $saldo->balance_balance + $folioAfectar->expenses_total;
                $saldo->balance_reconcile = $saldo->balance_balance;
                $create = $saldo->update();
            }
        }

        if ($folioAfectar->expenses_status == $this->estatus[2] && $folioAfectar->expenses_movement == 'Reposición Caja' && $folioAfectar->expenses_typeAccount == 'Caja') {

            $validarCuenta = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', $folioAfectar->expenses_moneyAccount)->where('moneyAccountsBalance_status', 'Alta')->WHERE('moneyAccountsBalance_company', '=', $folioAfectar->expenses_company)->where('moneyAccountsBalance_money', '=', $folioAfectar->expenses_money)->first();

            $saldo = $validarCuenta->moneyAccountsBalance_balance - $folioAfectar->expenses_total;
            $validarCuenta->moneyAccountsBalance_balance = $saldo;
            $validarCuenta->update();

            $saldoBalance = PROC_BALANCE::where('balance_account', '=', $folioAfectar->expenses_moneyAccount)->where('balance_branchKey', '=', $folioAfectar->expenses_branchOffice)->where('balance_companieKey', '=', $folioAfectar->expenses_company)->where('balance_money', '=', $folioAfectar->expenses_money)->first();

            if ($saldoBalance != null) {
                $saldoBalance->balance_balance = $saldoBalance->balance_balance - $folioAfectar->expenses_total;
                $saldoBalance->balance_reconcile = $saldoBalance->balance_balance;
                $create2 = $saldoBalance->update();
            }
        }
    }

    public function eliminarGasto(Request $request)
    {

        $gasto = PROC_EXPENSES::where('expenses_id', '=', $request->id)->first();

        //buscamos sus conceptos
        $conceptos = PROC_EXPENSES_DETAILS::where('expensesDetails_expenseID', '=', $gasto->expenses_id)->where('expensesDetails_branchOffice', '=', $gasto->expenses_branchOffice)->get();

        if ($conceptos->count() > 0) {
            //eliminamos sus conceptos
            foreach ($conceptos as $concepto) {
                $conceptosDelete = $concepto->delete();
            }
        } else {
            $conceptosDelete = true;
        }


        // dd($articulos);
        if ($gasto->expenses_status === $this->estatus[0] && $conceptosDelete === true) {
            $isDelete = $gasto->delete();
        } else {
            $isDelete = false;
        }

        if ($isDelete) {
            $status = 200;
            $message = 'Gasto eliminado correctamente';
        } else {
            $status = 500;
            $message = 'Error al eliminar el gasto';
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status]);
    }

    public function cancelarGasto(Request $request)
    {
        $gastoCancelar = PROC_EXPENSES::where('expenses_id', '=', $request->id)->first();



        $gastoCancelado = false;
        if ($gastoCancelar->expenses_status == $this->estatus[2] && $gastoCancelar->expenses_movement == 'Factura de Gasto') {


            //validar si ya hubo afectacion en cxp
            $movGenerado = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $gastoCancelar->expenses_id)->where('movementFlow_movementDestinity', '=', $gastoCancelar->expenses_movement)->where('movementFlow_moduleOrigin', '=', 'Gastos')->first();

            //verificar si ya hay algun movimiento posterior a este
            $movimiento = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $movGenerado->movementFlow_destinityID)->first();
            //  dd( gettype($gastoCancelar->expenses_total), gettype($movimiento->accountsPayable_balance));
            if ($gastoCancelar->expenses_total != $movimiento->accountsPayable_balance) {
                //  echo 'entro';
                $status = 500;
                $message = 'No se puede cancelar el gasto, ya que hay movimientos posteriores a esta';

                return response()->json(['mensaje' => $message, 'estatus' => $status]);
            }




            $cajaCancelada = true;
            //Cancelamos el gasto
            $gastoCancelar->expenses_status = $this->estatus[3];
            $cancelarGasto = $gastoCancelar->update();

            // $movimiento->accountsPayable_status = $this->estatus[3];
            // $movimiento->update();

            if ($cancelarGasto) {
                $gastoCancelado = true;
            } else {
                $gastoCancelado = false;
            }
            //cancela la cuenta por pagar
            $cxp = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movementID', '=', $gastoCancelar->expenses_movementID)->where('accountsPayable_branchOffice', '=', $gastoCancelar->expenses_branchOffice)->where('accountsPayable_movement', '=', 'Factura de Gasto')->first();

            if ($cxp !== null) {
                $cxp->accountsPayable_status = $this->estatus[3];
                $cxpCancelado = $cxp->update();

                if ($cxpCancelado) {
                    $gastoCancelado = true;
                } else {
                    $gastoCancelado = false;
                }
            }

            //eliminar la cxp pendinete del gasto
            $cxpPendiente = PROC_ACCOUNTS_PAYABLE_P::where('accountsPayableP_movementID', '=', $gastoCancelar->expenses_movementID)->where('accountsPayableP_branchOffice', '=', $gastoCancelar->expenses_branchOffice)->where('accountsPayableP_movement', '=', 'Factura de Gasto')->first();

            if ($cxpPendiente !== null) {
                $cxpPendienteDelete = $cxpPendiente->delete();
                if ($cxpPendienteDelete) {
                    $gastoCancelado = true;
                } else {
                    $gastoCancelado = false;
                }
            }

            //eliminamos saldo del proveedor
            $saldo = PROC_BALANCE::where('balance_account', '=', $gastoCancelar->expenses_provider)->where('balance_branchKey', '=', $gastoCancelar->expenses_branchOffice)->where('balance_companieKey', '=', $gastoCancelar->expenses_company)->where('balance_money', '=', $gastoCancelar->expenses_money)->where('balance_money', '=', $gastoCancelar->expenses_money)->first();

            if ($saldo != null) {
                $saldo->balance_balance = $saldo->balance_balance - $gastoCancelar->expenses_total;
                $saldo->balance_reconcile = $saldo->balance_balance;
                $saldoCancelado = $saldo->update();

                if ($saldoCancelado) {
                    $gastoCancelado = true;
                } else {
                    $gastoCancelado = false;
                }
            }


            //agregamos un cargo negativo a auxiliar
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $gastoCancelar->expenses_company;
            $auxiliar->assistant_branchKey = $gastoCancelar->expenses_branchOffice;
            $auxiliar->assistant_branch = 'CxP';

            //buscamos el modulo de cxp
            $cxp = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movementID', '=', $gastoCancelar->expenses_movementID)->where('accountsPayable_branchOffice', '=', $gastoCancelar->expenses_branchOffice)->first();

            $auxiliar->assistant_movement = $cxp->accountsPayable_movement;
            $auxiliar->assistant_movementID = $cxp->accountsPayable_movementID;
            $auxiliar->assistant_module = 'CxP';
            $auxiliar->assistant_moduleID = $cxp->accountsPayable_id;
            $auxiliar->assistant_money = $gastoCancelar->expenses_money;
            $auxiliar->assistant_typeChange = $gastoCancelar->expenses_typeChange;
            $auxiliar->assistant_account = $gastoCancelar->expenses_provider;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = '-' . $gastoCancelar->expenses_total;
            $auxiliar->assistant_payment = null;
            $auxiliar->assistant_apply = $cxp->accountsPayable_movement;
            $auxiliar->assistant_applyID =  $cxp->accountsPayable_movementID;
            $auxiliar->assistant_canceled = 1;
            $auxiliar->assistant_reference = "Factura de Gasto";
            $auxiliarNuevo = $auxiliar->save();

            if ($auxiliarNuevo) {
                $gastoCancelado = true;
            } else {
                $gastoCancelado = false;
            }


            //cancelamos movimientos de tabla

            $movimientosIN = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $gastoCancelar->expenses_id)->where('movementFlow_movementOrigin', '=', $gastoCancelar->expenses_movement)->where('movementFlow_branch', '=', $gastoCancelar->expenses_branchOffice)->where('movementFlow_company', '=', $gastoCancelar->expenses_company)->first();

            if ($movimientosIN != null) {
                $movimientosIN->movementFlow_cancelled = 1;
                $movCancelados = $movimientosIN->update();

                if ($movCancelados) {
                    $gastoCancelado = true;
                } else {
                    $gastoCancelado = false;
                }
            }
        }

        $cajaCancelada = false;
        if ($gastoCancelar->expenses_status == $this->estatus[2] && $gastoCancelar->expenses_movement == 'Reposición Caja') {
            $gastoCancelado = true;

            if ($gastoCancelar->expenses_typeAccount == 'Banco') {
                //validar si ya hubo afectación de movimiento bancario
                $movGenerado = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $gastoCancelar->expenses_id)->where('movementFlow_movementOrigin', '=', $gastoCancelar->expenses_movement)->where('movementFlow_moduleOrigin', '=', 'Gastos')->first();

                if ($movGenerado != null) {
                    $solicitud = PROC_TREASURY::where('treasuries_id', '=', $movGenerado->movementFlow_destinityID)->first();

                    if ($solicitud->treasuries_status === $this->estatus[2]) {
                        $status = 500;
                        $message = 'No se puede cancelar el gasto, ya que hay movimientos posteriores a esta';

                        return response()->json(['mensaje' => $message, 'estatus' => $status]);
                    }
                }
            }

            //Cancelamos el gasto
            $gastoCancelar->expenses_status = $this->estatus[3];
            $cancelarGasto = $gastoCancelar->update();

            if ($cancelarGasto) {
                $cajaCancelada = true;
            } else {
                $cajaCancelada = false;
            }


            //cancelamos entrada en tesoreria
            $egreso = PROC_TREASURY::where('treasuries_originID', '=', $gastoCancelar->expenses_movementID)->where('treasuries_origin', '=', 'Reposición Caja')->where('treasuries_originType', '=', 'Gastos')->where('treasuries_branchOffice', '=', $gastoCancelar->expenses_branchOffice)->where('treasuries_company', '=', $gastoCancelar->expenses_company)->first();
            // dd($egreso);
            if ($egreso != null) {
                $egreso->treasuries_status = $this->estatus[3];
                $egresoCancelado = $egreso->update();

                if ($egresoCancelado) {
                    $cajaCancelada = true;
                } else {
                    $cajaCancelada = false;
                }
            }



            if ($gastoCancelar->expenses_typeAccount == 'Caja') {
                //agregamos un cargo negativo a auxiliar
                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $gastoCancelar->expenses_company;
                $auxiliar->assistant_branchKey = $gastoCancelar->expenses_branchOffice;
                $auxiliar->assistant_branch = 'Din';

                //buscamos el modulo de cxp
                $tesoreria = PROC_TREASURY::where('treasuries_originID', '=', $gastoCancelar->expenses_movementID)->where('treasuries_originType', '=', 'GASTOS')->where('treasuries_branchOffice', '=', $gastoCancelar->expenses_branchOffice)->first();

                $auxiliar->assistant_movement = $tesoreria->treasuries_movement;
                $auxiliar->assistant_movementID = $tesoreria->treasuries_movementID;
                $auxiliar->assistant_module = 'Din';
                $auxiliar->assistant_moduleID = $tesoreria->treasuries_id;
                $auxiliar->assistant_money = $gastoCancelar->expenses_money;
                $auxiliar->assistant_typeChange = $gastoCancelar->expenses_typeChange;
                $auxiliar->assistant_account = $gastoCancelar->expenses_moneyAccount;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = null;
                $auxiliar->assistant_payment = '-' . $gastoCancelar->expenses_total;
                $auxiliar->assistant_apply = $tesoreria->treasuries_movement;
                $auxiliar->assistant_applyID =  $tesoreria->treasuries_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = "Factura de Gasto";
                $auxiliarNuevo = $auxiliar->save();

                if ($auxiliarNuevo) {
                    $cajaCancelada = true;
                } else {
                    $cajaCancelada = false;
                }

                //regresamos el saldo del proveedor
                $saldo = PROC_BALANCE::where('balance_account', '=', $gastoCancelar->expenses_moneyAccount)->where('balance_branchKey', '=', $gastoCancelar->expenses_branchOffice)->where('balance_companieKey', '=', $gastoCancelar->expenses_company)->where('balance_money', '=', $gastoCancelar->expenses_money)->first();

                if ($saldo != null) {
                    $saldo->balance_balance = $saldo->balance_balance + $gastoCancelar->expenses_total;
                    $saldoCancelado = $saldo->update();

                    if ($saldoCancelado) {
                        $cajaCancelada = true;
                    } else {
                        $cajaCancelada = false;
                    }
                }

                $cuentaSaldo = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', $gastoCancelar->expenses_moneyAccount)->where('moneyAccountsBalance_status', 'Alta')->WHERE('moneyAccountsBalance_company', '=', $gastoCancelar->expenses_company)->where('moneyAccountsBalance_money', '=', $gastoCancelar->expenses_money)->first();

                if ($cuentaSaldo != null) {
                    $cuentaSaldo->moneyAccountsBalance_balance = $cuentaSaldo->moneyAccountsBalance_balance + $gastoCancelar->expenses_total;
                    $cuentaSaldoCancelado = $cuentaSaldo->update();

                    if ($cuentaSaldoCancelado) {
                        $cajaCancelada = true;
                    } else {
                        $cajaCancelada = false;
                    }
                }
                // dd($cuentaSaldo);
            }


            $movimientosIN = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $gastoCancelar->expenses_id)->where('movementFlow_movementOrigin', '=', $gastoCancelar->expenses_movement)->where('movementFlow_branch', '=', $gastoCancelar->expenses_branchOffice)->where('movementFlow_company', '=', $gastoCancelar->expenses_company)->first();

            if ($movimientosIN != null) {
                $movimientosIN->movementFlow_cancelled = 1;
                $movCancelados = $movimientosIN->update();

                if ($movCancelados) {
                    $cajaCancelada = true;
                } else {
                    $cajaCancelada = false;
                }
            }
        }

        // dd($gastoCancelar);

        if ($gastoCancelado == true  || $cajaCancelada == true) {
            $status = 200;
            $message = 'Gasto cancelado correctamente';
        } else {
            $status = 500;
            $message = 'Error al cancelar el gasto';
        }

        return response()->json(['estatus' => $status, 'mensaje' => $message]);
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


    public function getClientes(Request $request)
    {
        $proveedor = CAT_CUSTOMERS::where('customers_key', '=', $request->proveedor)->first();
        return response()->json($proveedor);
    }


    //Obtenemos el reporte de la compra seleccionada
    public function getReporteGasto($id)
    {

        $gasto = PROC_EXPENSES::join('CAT_PROVIDERS', 'CAT_PROVIDERS.providers_key', '=', 'PROC_EXPENSES.expenses_provider')
            ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_EXPENSES.expenses_condition')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_EXPENSES.expenses_branchOffice')
            ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_EXPENSES.expenses_company')
            ->join('CONF_MONEY', 'CONF_MONEY.money_key', '=', 'PROC_EXPENSES.expenses_money')
            ->where('expenses_id', '=', $id)
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


        $concepto_gastos = PROC_EXPENSES_DETAILS::WHERE('expensesDetails_expenseID', '=', $id)->get();

        $pdf = PDF::loadView('reportes.gastos-reporte', ['gasto' => $id, 'logo' => $logoBase64, 'gasto' => $gasto, 'concepto_gastos' => $concepto_gastos]);
        $pdf->set_paper('a4', 'landscape');
        return $pdf->stream();
    }

    public function gastosAction(Request $request)
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

        switch ($request->input('action')) {
            case 'Búsqueda':
                $gastos_collection_filtro = PROC_EXPENSES::join('CAT_PROVIDERS', 'PROC_EXPENSES.expenses_provider', '=', 'CAT_PROVIDERS.providers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_EXPENSES.expenses_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CONF_CREDIT_CONDITIONS', 'PROC_EXPENSES.expenses_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
                    ->join('CONF_FORMS_OF_PAYMENT', 'PROC_EXPENSES.expenses_paymentMethod', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key')
                    ->join('CAT_COMPANIES', 'PROC_EXPENSES.expenses_company', '=', 'CAT_COMPANIES.companies_key')
                    ->where('PROC_EXPENSES.expenses_company', '=', session('company')->companies_key)
                    ->whereExpensesMovementID($nameFolio)
                    ->whereExpensesProvider($nameKey)
                    ->whereExpensesMovement($nameMov)
                    ->whereExpensesStatus($status)
                    ->whereExpensesDate($nameFecha)
                    ->whereExpensesUser($nameUsuario)
                    ->whereExpensesbranchOffice($nameSucursal)
                    ->whereExpensesMoney($nameMoneda)
                    ->orderBy('PROC_EXPENSES.updated_at', 'DESC')
                    ->get();

                $gastos_filtro_array = $gastos_collection_filtro->toArray();

                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.modulo.gastos.index')
                    ->with('gastos_filtro_array', $gastos_filtro_array)
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
                $gasto = new PROC_GastosExport($nameFolio, $nameKey, $nameMov, $status, $nameFecha, $nameUsuario, $nameSucursal, $nameMoneda);
                return Excel::download($gasto, 'gastos.xlsx');
                break;

            default:
                break;
        }
    }

    public function actualizarFolio($tipoMovimiento, $folioAfectar)
    {
        switch ($tipoMovimiento) {
            case 'Reposición Caja':
                $consecutivoColumn = 'generalConsecutives_consPettyCash';
                break;
            case 'Factura de Gasto':
                $consecutivoColumn = 'generalConsecutives_consExpense';
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
                $folioOrden = PROC_EXPENSES::where('expenses_movement', '=', $tipoMovimiento)
                    ->where('expenses_branchOffice', '=', $folioAfectar->expenses_branchOffice)
                    ->max('expenses_movementID');
                $folioOrden = $folioOrden == null ? 1 : $folioOrden + 1;
                $folioAfectar->expenses_movementID = $folioOrden;
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

                $folioAfectar->expenses_movementID = $consecutivo + 1;
                $folioAfectar->update();
            }
        }
    }
}
