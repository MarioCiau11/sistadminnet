<?php

namespace App\Http\Controllers\erpNet\ConfiguracionGeneral;

use App\Http\Controllers\Controller;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS_CONSECUTIVES;
use App\Models\catalogos\CONF_MONEY;
use App\Models\modulos\PROC_ACCOUNTS_PAYABLE;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE;
use App\Models\modulos\PROC_EXPENSES;
use App\Models\modulos\PROC_INVENTORIES;
use App\Models\modulos\PROC_PURCHASE;
use App\Models\modulos\PROC_SALES;
use App\Models\modulos\PROC_TREASURY;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ParametrosGeneralesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:Párametros Generales']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('page.configuracionGeneral.parametrosGenerales.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $consOrdenEntrada = PROC_PURCHASE::where('purchase_movement', '=', 'Orden de Compra')->where('purchase_branchOffice', '=', session('sucursal')->branchOffices_key)->max('purchase_movementID');
        $consEntrada = PROC_PURCHASE::where('purchase_movement', '=', 'Entrada por Compra')->where('purchase_branchOffice', '=', session('sucursal')->branchOffices_key)->max('purchase_movementID');
        $consAjuste = PROC_INVENTORIES::where('inventories_movement', '=', 'Ajuste de Inventario')->where('inventories_branchOffice', '=', session('sucursal')->branchOffices_key)->max('inventories_movementID');
        $consTransferenciaAlmacen = PROC_INVENTORIES::where('inventories_movement', '=', 'Transferencia entre Alm.')->where('inventories_branchOffice', '=', session('sucursal')->branchOffices_key)->max('inventories_movementID');
        $consCotizacion = PROC_SALES::where('sales_movement', '=', 'Cotización')->where('sales_branchOffice', '=', session('sucursal')->branchOffices_key)->max('sales_movementID');
        $consPedido = PROC_SALES::where('sales_movement', '=', 'Pedido')->where('sales_branchOffice', '=', session('sucursal')->branchOffices_key)->max('sales_movementID');
        $consFactura = PROC_SALES::where('sales_movement', '=', 'Factura')->where('sales_branchOffice', '=', session('sucursal')->branchOffices_key)->max('sales_movementID');
        $consAnticipo = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', 'Anticipo')->where('accountsPayable_branchOffice', '=', session('sucursal')->branchOffices_key)->max('accountsPayable_movementID');
        $consAplicacion = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', 'Aplicación')->where('accountsPayable_branchOffice', '=', session('sucursal')->branchOffices_key)->max('accountsPayable_movementID');
        $consPago = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movement', '=', 'Pago de Facturas')->where('accountsPayable_branchOffice', '=', session('sucursal')->branchOffices_key)->max('accountsPayable_movementID');
        $consAnticipoCXC = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', 'Anticipo Clientes')->where('accountsReceivable_branchOffice', '=', session('sucursal')->branchOffices_key)->max('accountsReceivable_movementID');
        $consAplicacionCXC = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', 'Aplicación')->where('accountsReceivable_branchOffice', '=', session('sucursal')->branchOffices_key)->max('accountsReceivable_movementID');
        $consDevolucionAnticipo = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', 'Devolución de Anticipo')->where('accountsReceivable_branchOffice', '=', session('sucursal')->branchOffices_key)->max('accountsReceivable_movementID');
        $consCobro = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', '=', 'Cobro de Facturas')->where('accountsReceivable_branchOffice', '=', session('sucursal')->branchOffices_key)->max('accountsReceivable_movementID');
        $consGasto = PROC_EXPENSES::where('expenses_movement', '=', 'Factura de Gasto')->where('expenses_branchOffice', '=', session('sucursal')->branchOffices_key)->max('expenses_movementID');
        $consCajaChica = PROC_EXPENSES::where('expenses_movement', '=', 'Reposición Caja')->where('expenses_branchOffice', '=', session('sucursal')->branchOffices_key)->max('expenses_movementID');
        $consTransferenciaT = PROC_TREASURY::where('treasuries_movement', '=', 'Traspaso Cuentas')->where('treasuries_branchOffice', '=', session('sucursal')->branchOffices_key)->max('treasuries_movementID');
        $consEgreso = PROC_TREASURY::where('treasuries_movement', '=', 'Egreso')->where('treasuries_branchOffice', '=', session('sucursal')->branchOffices_key)->max('treasuries_movementID');

        // dd($consGasto);
        // dd($consOrdenEntrada);
        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();

        $monedas = $this->getMonedas();
        // dd($parametro);
        return view('page.configuracionGeneral.parametrosGenerales.create', compact('parametro', 'monedas', 'consOrdenEntrada', 'consEntrada', 'consAjuste', 'consTransferenciaAlmacen', 'consCotizacion', 'consPedido', 'consFactura', 'consAnticipo', 'consAplicacion', 'consPago', 'consAnticipoCXC', 'consAplicacionCXC', 'consDevolucionAnticipo', 'consCobro', 'consGasto', 'consCajaChica', 'consTransferenciaT', 'consEgreso'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());

        $config_parameters_request = $request->except('_token');

        $config_parameters = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();


        if ($config_parameters == null) {
            $config_parameters = new CONF_GENERAL_PARAMETERS();
        } else {
            $config_parameters;
        }

        $config_parameters->generalParameters_company = session('company')->companies_key;

        $config_parameters->generalParameters_businessDays = $config_parameters_request['diasHabiles'];

        $valor1fecha1 = $config_parameters_request['ejercicioInicia'];

        $valores = explode("/", $valor1fecha1);

        //    if()
        //    dd($valores);


        // dd($config_parameters_request['ejercicioInicia'], $config_parameters_request['ejercicioTermina']);

        $dateIni = Carbon::parse($config_parameters_request['ejercicioInicia'])->format('Y-m-d');
        $dateFin = Carbon::parse($config_parameters_request['ejercicioTermina'])->format('Y-m-d');

        // dd($dateIni, $dateFin);


        $config_parameters->generalParameters_exerciseStarts = $dateIni;
        $config_parameters->generalParameters_exerciseEnds = $dateFin;
        $config_parameters->generalParameters_filesCustomers = $config_parameters_request['rutaDocumentosClientes'];
        $config_parameters->generalParameters_filesProviders = $config_parameters_request['rutaDocumentosProveedores'];
        $config_parameters->generalParameters_filesMovements = $config_parameters_request['rutaDocumentosMovimientos'];
        $config_parameters->generalParameters_filesArticles = $config_parameters_request['rutaFotosArticulos'];
        $config_parameters->generalParameters_defaultMoney = $config_parameters_request['monedaPredeterminada'];
        $config_parameters->generalParameters_termsConditionsReportQuote = $config_parameters_request['especifications'];
        $config_parameters->generalParameters_termsConditionsReportSalesNote = $config_parameters_request['especifications2'];
        $config_parameters->generalParameters_termsConditionsReportDeliveryFormat = $config_parameters_request['especifications3'];
        $config_parameters->generalParameters_defaultText = $config_parameters_request['especifications4'];

        $facturas = empty($config_parameters_request['facturasSin']) ? 0 : $config_parameters_request['facturasSin'];

        $config_parameters->generalParameters_billsNot = $facturas;

        // $config_parameters->generalParameters_consOrderPurchase = $config_parameters_request['consOrdenCompra'];
        // $config_parameters->generalParameters_consEntryPurchase = $config_parameters_request['consEntradaCompra'];
        // $config_parameters->generalParameters_consAdjustment = $config_parameters_request['consAjuste'];
        // $config_parameters->generalParameters_consTransfer = $config_parameters_request['consTransferencia'];
        // $config_parameters->generalParameters_consQuotation = $config_parameters_request['consCotizacion'];
        // $config_parameters->generalParameters_consDemand = $config_parameters_request['consPedido'];
        // $config_parameters->generalParameters_consBill = $config_parameters_request['consFactura'];
        // $config_parameters->generalParameters_consAdvance = $config_parameters_request['consAplicacion'];
        // $config_parameters->generalParameters_consApplication = $config_parameters_request['consAplicacion'];
        // $config_parameters->generalParameters_consPayment = $config_parameters_request['consPago'];
        // $config_parameters->generalParameters_consAdvanceCXC = $config_parameters_request['consAnticipoCXC'];
        // $config_parameters->generalParameters_consApplicationCXC = $config_parameters_request['consAplicacionCXC'];
        // $config_parameters->generalParameters_consReturnAdvance = $config_parameters_request['consDevolucionAnticipo'];
        // $config_parameters->generalParameters_consCollection = $config_parameters_request['consCobro'];
        // $config_parameters->generalParameters_consExpense = $config_parameters_request['consGasto'];
        // $config_parameters->generalParameters_consPettyCash = $config_parameters_request['consCajaChica'];
        // $config_parameters->generalParameters_consTransferT = $config_parameters_request['consTransferenciaT'];
        // $config_parameters->generalParameters_consEgress = $config_parameters_request['consEgreso'];
        // dd($config_parameters_request);
        try {

            $isSave = $config_parameters->save();

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

            // $config_consecutives_parameters = new CONF_GENERAL_PARAMETERS_CONSECUTIVES();
            $config_consecutives_parameters->generalConsecutives_generalParametersID = $config_parameters->generalParameters_id;
            $config_consecutives_parameters->generalConsecutives_company = session('company')->companies_key;
            $config_consecutives_parameters->generalConsecutives_branchOffice = session('sucursal')->branchOffices_key;
            $config_consecutives_parameters->generalConsecutives_consOrderPurchase = $config_parameters_request['consOrdenCompra'];
            $config_consecutives_parameters->generalConsecutives_consEntryPurchase = $config_parameters_request['consEntradaCompra'];
            $config_consecutives_parameters->generalConsecutives_consAdjustment = $config_parameters_request['consAjuste'];
            $config_consecutives_parameters->generalConsecutives_consTransfer = $config_parameters_request['consTransferencia'];
            $config_consecutives_parameters->generalConsecutives_consQuotation = $config_parameters_request['consCotizacion'];
            $config_consecutives_parameters->generalConsecutives_consDemand = $config_parameters_request['consPedido'];
            $config_consecutives_parameters->generalConsecutives_consBill = $config_parameters_request['consFactura'];
            $config_consecutives_parameters->generalConsecutives_consAdvance = $config_parameters_request['consAnticipo'];
            $config_consecutives_parameters->generalConsecutives_consApplication = $config_parameters_request['consAplicacion'];
            $config_consecutives_parameters->generalConsecutives_consPayment = $config_parameters_request['consPago'];
            $config_consecutives_parameters->generalConsecutives_consAdvanceCXC = $config_parameters_request['consAnticipoCXC'];
            $config_consecutives_parameters->generalConsecutives_consApplicationCXC = $config_parameters_request['consAplicacionCXC'];
            $config_consecutives_parameters->generalConsecutives_consReturnAdvance = $config_parameters_request['consDevolucionAnticipo'];
            $config_consecutives_parameters->generalConsecutives_consCollection = $config_parameters_request['consCobro'];
            $config_consecutives_parameters->generalConsecutives_consExpense = $config_parameters_request['consGasto'];
            $config_consecutives_parameters->generalConsecutives_consPettyCash = $config_parameters_request['consCajaChica'];
            $config_consecutives_parameters->generalConsecutives_consTransferT = $config_parameters_request['consTransferenciaT'];
            $config_consecutives_parameters->generalConsecutives_consEgress = $config_parameters_request['consEgreso'];
            $config_consecutives_parameters->save();
            if ($isSave) {
                $message = "Se guardó correctamente el registro";
                $status = true;
                session(['generalParameters' => $config_parameters]);
            } else {

                $message = "No se guardó correctamente el registro";
                $status = false;
            }
        } catch (\Throwable $th) {
            $message = "Por favor, vaya con el administrador de sistemas con el siguiente mensaje: " . $th->getMessage();
            return redirect()->route('configuracion.parametros-generales.create')->with('message', $message)->with('status', false);
        }

        return redirect()->route('configuracion.parametros-generales.create')->with('message', $message)->with('status', $status);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
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
}
