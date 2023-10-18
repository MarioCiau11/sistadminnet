<?php

namespace App\Http\Controllers\erpNet\procesos;

use App\Exports\PROC_VentasExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\erpNet\Timbrado\TimbradoController;
use App\Mail\EnviarCotizacion;
use App\Models\agrupadores\CAT_ARTICLES_CATEGORY;
use App\Models\agrupadores\CAT_ARTICLES_FAMILY;
use App\Models\agrupadores\CAT_ARTICLES_GROUP;
use App\Models\catalogos\CAT_AGENTS;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_ARTICLES_IMG;
use App\Models\catalogos\CAT_ARTICLES_UNITS;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_COMPANIES;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CAT_DEPOTS;
use App\Models\catalogos\CAT_KIT_ARTICLES;
use App\Models\catalogos\CAT_MONEY_ACCOUNTS;
use App\Models\catalogos\CAT_PROVIDERS;
use App\Models\catalogos\CAT_VEHICLES;
use App\Models\catalogos\CONF_CREDIT_CONDITIONS;
use App\Models\catalogos\CONF_FORMS_OF_PAYMENT;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS_CONSECUTIVES;
use App\Models\catalogos\CONF_MODULES_CONCEPT;
use App\Models\catalogos\CONF_MONEY;
use App\Models\catalogos\CONF_PACKAGING_UNITS;
use App\Models\catalogos\CONF_REASON_CANCELLATIONS;
use App\Models\catalogos\CONF_UNITS;
use App\Models\catalogosSAT\CAT_SAT_CLAVEPEDIMENTO;
use App\Models\catalogosSAT\CAT_SAT_INCOTERM;
use App\Models\catalogosSAT\CAT_SAT_MOTIVO_TRASLADO;
use App\Models\catalogosSAT\CAT_SAT_MOTIVOS_CANCELACION;
use App\Models\catalogosSAT\CAT_SAT_TIPOOPERACION;
use App\Models\catalogosSAT\CAT_SAT_USOCFDI;
use App\Models\historicos\HIST_STAMPED;
use App\Models\modulos\helpers\PROC_ARTICLES_COST;
use App\Models\modulos\helpers\PROC_MONEY_ACCOUNTS_BALANCE;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE_P;
use App\Models\modulos\PROC_ARTICLES_INV;
use App\Models\modulos\PROC_ASSISTANT;
use App\Models\modulos\PROC_ASSISTANT_UNITS;
use App\Models\modulos\PROC_BALANCE;
use App\Models\modulos\PROC_DEL_SERIES_MOV2;
use App\Models\modulos\PROC_KIT_ARTICLES;
use App\Models\modulos\PROC_LOT_SERIES;
use App\Models\modulos\PROC_MOVEMENT_FLOW;
use App\Models\modulos\PROC_PACKINGLIST;
use App\Models\modulos\PROC_SALES;
use App\Models\modulos\PROC_SALES_DETAILS;
use App\Models\modulos\PROC_SALES_FOREIGN_TRADE;
use App\Models\modulos\PROC_SALES_PAYMENT;
use App\Models\modulos\PROC_TREASURY;
use App\Models\timbrado\PROC_CANCELED_REASON;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use stdClass;
use Termwind\Components\Dd;

class VentasController extends Controller
{
    public $estatus = [
        0 => 'INICIAL',
        1 => 'POR AUTORIZAR',
        2 => 'FINALIZADO',
        3 => 'CANCELADO',
    ];

    public $venta;

    public function __construct(PROC_SALES $venta)
    {
        $this->venta = $venta;
    }

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

        $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
            ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
            ->where('PROC_SALES.sales_company', session('company')->companies_key)
            ->where('PROC_SALES.sales_branchOffice', session('sucursal')->branchOffices_key)
            ->where('PROC_SALES.sales_user', Auth::user()->username)
            ->where('PROC_SALES.sales_money', session('generalParameters')->generalParameters_defaultMoney)
            ->orderBy('PROC_SALES.created_at', 'DESC')
            ->get();

        // dd($ventas);

        return view('page.modulos.Comercial.Ventas.index-ventas', compact('fecha_actual', 'select_users', 'select_sucursales', 'selectMonedas', 'parametro', 'ventas'));
    }

    public function create(Request $request)
    {
        try {
            // dd(gettype(session('company')->companies_calculateTaxes));
            $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();

            if ($parametro->generalParameters_defaultMoney == null) {
                return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de seleccionar la moneda por defecto');
            }

            $informacionMoneda = CONF_MONEY::where('money_key', $parametro->generalParameters_defaultMoney)->first();
            //Obtenemos los permisos que tiene el usuario para el modulo de compras
            $usuario = Auth::user();
            // dd($usuario);
            $permisos = $usuario->getAllPermissions()->where('categoria', '=', 'Ventas')->pluck('name')->toArray();
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

            // selects para comercio exterior
            $select_incoTerm = CAT_SAT_INCOTERM::all();
            $select_motivoTraslado = CAT_SAT_MOTIVO_TRASLADO::all();
            $select_tipoOperacion = CAT_SAT_TIPOOPERACION::all();
            $select_clavePedimento = CAT_SAT_CLAVEPEDIMENTO::all();
            $select_MotivoCancelacion = CAT_SAT_MOTIVOS_CANCELACION::all();


            $select_motivos = CONF_REASON_CANCELLATIONS::WHERE('reasonCancellations_status', '=', 'Alta')->WHERE('reasonCancellations_module', '=', 'Ventas')->get();


            $facturasTimbradas = PROC_SALES::where('sales_stamped', '=', '1')->join('PROC_CFDI', 'PROC_SALES.sales_id', '=', 'PROC_CFDI.cfdi_moduleID')
                ->where('PROC_CFDI.cfdi_module', '=', 'Ventas')
                ->where('sales_movement', '=', 'Factura')
                ->where('sales_company', '=', session('company')->companies_key)
                ->where('sales_branchOffice', '=', session('sucursal')->branchOffices_key)
                ->where('sales_status', '=', 'FINALIZADO')
                ->get();

            $cobroVenta = null;
            $select_conceptos = CONF_MODULES_CONCEPT::WHERE('moduleConcept_status', '=', 'Alta')->WHERE('moduleConcept_module', '=', 'Ventas')->get();
            $fecha_actual = Carbon::now()->format('Y-m-d');
            $selectMonedas = $this->getMonedas2();
            $parametro = CONF_GENERAL_PARAMETERS::join('CONF_MONEY', 'CONF_GENERAL_PARAMETERS.generalParameters_defaultMoney', '=', 'CONF_MONEY.money_key')
                ->select('CONF_GENERAL_PARAMETERS.*', 'CONF_MONEY.money_change')
                ->where('CONF_GENERAL_PARAMETERS.generalParameters_company', '=', session('company')->companies_key)
                ->first();
            $select_condicionPago = CONF_CREDIT_CONDITIONS::WHERE('creditConditions_status', '=', 'Alta')->get();
            $select_agentes = CAT_AGENTS::where('agents_status', '=', 'Alta')->where('agents_branchOffice', '=', session('sucursal')->branchOffices_key)->get();
            $select_vehiculos = CAT_VEHICLES::WHERE('vehicles_status', '=', 'Alta')->where('vehicles_branchOffice', '=', session('sucursal')->branchOffices_key)->get();

            $select_categoria = $this->selectCategoria();
            $select_grupo = $this->selectGrupo();
            $select_familia = $this->selectFamilia();

            $selectCFDI = CAT_SAT_USOCFDI::all();

            $articulos = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article', 'left')->WHERE('articles_status', '=', 'Alta')->get();


            //  dd($articulos);
            $proveedores = CAT_PROVIDERS::WHERE('providers_status', '=', 'Alta')->get();
            $select_formaPago = CONF_FORMS_OF_PAYMENT::WHERE('formsPayment_status', '=', 'Alta')->get();
            // dd($select_formaPago);

            // dd( Auth::user()->user_block_sale_prices);
            $almacenes = CAT_DEPOTS::join('CAT_BRANCH_OFFICES', 'CAT_DEPOTS.depots_branchlId', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                ->join('CAT_COMPANIES', 'CAT_BRANCH_OFFICES.branchOffices_companyId', '=', 'CAT_COMPANIES.companies_key')
                ->where('CAT_COMPANIES.companies_key', '=', session('company')->companies_key)
                ->where('CAT_BRANCH_OFFICES.branchOffices_key', '=', session('sucursal')->branchOffices_key)
                ->where('CAT_DEPOTS.depots_status', '=', 'Alta')
                ->orderBy('CAT_DEPOTS.created_at', 'ASC')
                ->get();
            // dd($almacenes);
            //función para obtener los agentes tipo vendedor
            $catAgents = new CAT_AGENTS();
            $vendedores = $catAgents->getActiveSellers();
            // dd($vendedores);

            $unidad = $this->getConfUnidades();

            $clientes = CAT_CUSTOMERS::WHERE('customers_status', '=', 'Alta')->get();
            $moneyAccounts = CAT_MONEY_ACCOUNTS::join('CAT_FINANCIAL_INSTITUTIONS', 'CAT_FINANCIAL_INSTITUTIONS.instFinancial_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_bank')
                ->where('CAT_MONEY_ACCOUNTS.moneyAccounts_status', '=', 'Alta')
                ->where('CAT_MONEY_ACCOUNTS.moneyAccounts_company', '=', session('company')->companies_key)
                ->get();



            $primerFlujodeVenta = [];

            if (isset($request->id)) {
                $incotermNombre = null;
                $trasladoNombre = null;
                $venta = PROC_SALES::find($request->id);
                if ($venta == null) {
                    return redirect()->route('vista.modulo.ventas')->with('status', false)->with('message', 'La venta no se ha encontrado');
                }
                $informacionMoneda = CONF_MONEY::where('money_key', $venta->sales_money)->first();

                //Obtenemos la información del comercio exterior
                $comercioExt = PROC_SALES_FOREIGN_TRADE::WHERE('salesForeingTrade_saleID', "=", $venta->sales_id)->first();

                if ($comercioExt !== null) {
                    $incotermNombre = CAT_SAT_INCOTERM::WHERE('c_INCOTERM', '=', $comercioExt->salesForeingTrade_incoterm)->first();
                    $trasladoNombre = CAT_SAT_MOTIVO_TRASLADO::WHERE('c_MotivoTraslado', '=', $comercioExt->salesForeingTrade_transferReason)->first();
                }

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
                if (!$usuario->can($venta->sales_movement . ' C')) {
                    return redirect()->route('vista.modulo.ventas')->with('status', false)->with('message', 'No tiene permisos para visualizar este movimiento');
                }

                $nameProveedor = CAT_CUSTOMERS::where('customers_key', '=', $venta->sales_customer)->first();
                $articulosByVenta = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $venta->sales_id)->get();

                // dd($articulosByVenta);
                //Buscamos el cobro de la venta
                $cobroVenta = PROC_SALES_PAYMENT::where('salesPayment_saleID', '=', $venta->sales_id)->first();

                $primerFlujodeVenta = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
                    ->WHERE('movementFlow_originID', '=', $venta->sales_id)
                    ->WHERE('movementFlow_movementOriginID', '=', $venta->sales_movementID)
                    ->WHERE('movementFlow_moduleOrigin', '=', 'Ventas')
                    ->get();


                if (count($primerFlujodeVenta) === 0) {
                    $primerFlujodeVenta = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
                        ->WHERE('movementFlow_destinityID', '=', $venta->sales_id)
                        ->WHERE('movementFlow_movementDestinityID', '=', $venta->sales_movementID)
                        ->WHERE('movementFlow_moduleDestiny', '=', 'Ventas')
                        ->get();
                    // dd($primerFlujoDeVenta);
                }

                if (count($articulosByVenta) != 0) {
                    $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $articulosByVenta[0]->salesDetails_article)->join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->join('PROC_ARTICLES_COST', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_COST.articlesCost_article')->join('CONF_UNITS', 'CAT_ARTICLES.articles_unitBuy', '=', 'CONF_UNITS.units_id')->join('CAT_DEPOTS', 'PROC_ARTICLES_COST.articlesCost_depotKey', '=', 'CAT_DEPOTS.depots_key')->where('articlesCost_branchKey', '=', $venta->sales_branchOffice)->where('articlesCost_depotKey', '=', $venta->sales_depot)->where('PROC_ARTICLES_INV.articlesInv_depot', '=', $venta->sales_depot)->where('PROC_ARTICLES_INV.articlesInv_branchKey', '=', $venta->sales_branchOffice)->first();


                    $imagenesArticulo = CAT_ARTICLES_IMG::where('articlesImg_article', '=', $articulosByVenta[0]->salesDetails_article)->get();



                    $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $articulosByVenta[0]->salesDetails_article)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->get();

                    if ($infoArticulo == null) {
                        $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $articulosByVenta[0]->salesDetails_article)->first();


                        $imagenesArticulo = CAT_ARTICLES_IMG::where('articlesImg_article', '=', $articulosByVenta[0]->salesDetails_article)->get();

                        $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $articulosByVenta[0]->salesDetails_article)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->get();
                    }

                    if ($infoArticulo->articles_type == 'Kit') {

                        $articulosKit = CAT_KIT_ARTICLES::where('kitArticles_articleID', '=', $infoArticulo->articles_id)->get();

                        // DD($articulosKit);
                        $articulosKitInfo = array();
                        foreach ($articulosKit as $articuloKit) {
                            if ($articuloKit->kitArticles_tipo != "Servicio") {
                                $articuloKitInfo = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('articles_key', '=', $articuloKit->kitArticles_article)->first();
                                array_push($articulosKitInfo, $articuloKitInfo);
                            } else {
                                $articuloKitInfo = CAT_ARTICLES::where('articles_key', '=', $articuloKit->kitArticles_article)->first();
                                array_push($articulosKitInfo, $articuloKitInfo);
                            }
                        }
                        //  dd($articulosKitInfo);
                    } else {
                        $articulosKitInfo = null;
                    }
                } else {
                    $infoArticulo = null;
                    $articulosByAlmacen = null;
                    $imagenesArticulo = null;
                    $articulosKitInfo = null;
                }


                $infoProveedor = CAT_CUSTOMERS::join('CONF_CREDIT_CONDITIONS', 'CAT_CUSTOMERS.customers_creditCondition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id', 'left outer')->where('CAT_CUSTOMERS.customers_key', '=', $venta->sales_customer)->first();
                // dd($infoProveedor);

                $startDate = Carbon::now()->subDays(60); // Fecha de inicio hace 60 días
                $endDate = Carbon::now(); // Fecha actual
                //le damos formato a las fechas
                $startDate = $startDate->format('Y-m-d');
                $endDate = $endDate->format('Y-m-d');
                // dd($startDate, $endDate);
                $top5Productos = PROC_SALES::join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                    ->where('PROC_SALES.sales_customer', '=', $venta->sales_customer)
                    ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                    ->whereBetween('PROC_SALES.sales_issuedate', [$startDate, $endDate])
                    ->select('PROC_SALES_DETAILS.salesDetails_article', 'salesDetails_descript', DB::raw('sum(CAST(PROC_SALES_DETAILS.salesDetails_quantity AS FLOAT)) as total'))
                    ->groupBy('PROC_SALES_DETAILS.salesDetails_article', 'salesDetails_descript')
                    ->orderBy('total', 'desc')
                    ->limit(5)
                    ->get();

                // dd($top5Productos);

                $monedasMov = CONF_MONEY::where('money_status', '=', 'Alta')->orderBy('money_key', 'desc')->get();

                $movimientosProveedor = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_customer', '=', $venta->sales_customer)->whereIn('accountsReceivable_movement', ['Factura', 'Anticipo Clientes'])->where("accountsReceivable_balance", ">", '0')->where("accountsReceivable_company", "=", session('company')->companies_key)->where("accountsReceivable_status", "=", 'POR AUTORIZAR')->get();



                // DD($movimientosProveedor);

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
                // dd($primerFlujodeVenta, $unidad);
                return view('page.modulos.Comercial.Ventas.create-ventas', compact('selectMonedas', 'select_conceptos', 'fecha_actual', 'select_condicionPago', 'proveedores', 'parametro', 'almacenes', 'vendedores', 'articulos', 'unidad', 'clientes', 'venta', 'nameProveedor', 'articulosByVenta', 'select_formaPago', 'moneyAccounts', 'cobroVenta', 'primerFlujodeVenta', 'infoProveedor', 'monedasMov', 'movimientosProveedor', 'saldoGeneral', 'select_vehiculos', 'select_agentes', 'movimientos', 'infoArticulo', 'articulosByAlmacen', 'select_incoTerm', 'select_motivoTraslado', 'select_tipoOperacion', 'select_clavePedimento', 'informacionMoneda', 'comercioExt', 'incotermNombre', 'trasladoNombre', 'selectCFDI', 'select_MotivoCancelacion', 'facturasTimbradas', 'select_motivos', 'imagenesArticulo', 'articulosKitInfo', 'usuario', 'select_categoria', 'select_grupo', 'select_familia', 'top5Productos'));
            } else {
                $venta = null;
                $nameProveedor = null;
                $articulosByVenta = null;
            }
            // dd($clientes);
            return view('page.modulos.Comercial.Ventas.create-ventas', compact('selectMonedas', 'select_conceptos', 'fecha_actual', 'select_condicionPago', 'proveedores', 'parametro', 'almacenes', 'vendedores', 'articulos', 'unidad', 'clientes', 'venta', 'nameProveedor', 'articulosByVenta', 'select_formaPago', 'moneyAccounts', 'cobroVenta', 'primerFlujodeVenta', 'select_vehiculos', 'select_agentes', 'movimientos', 'select_incoTerm', 'select_motivoTraslado', 'select_tipoOperacion', 'select_clavePedimento', 'informacionMoneda', 'selectCFDI', 'select_MotivoCancelacion', 'facturasTimbradas', 'select_motivos', 'usuario', 'select_categoria', 'select_grupo', 'select_familia'));
        } catch (\Exception $e) {
            return redirect()->route('vista.modulo.ventas')->with('status', false)->with('message', 'La venta no se ha encontrado' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        //  dd($request->all());
        $ventas_request = $request->except('_token');
        // dd($ventas_request);
        $id = $request->id;
        $copiaRequest = $request->copiar;
        if ($id == 0 || $copiaRequest == 'copiar') {
            $venta = new PROC_SALES();
            $venta->sales_user = Auth::user()->username;
        } else {
            $venta = PROC_SALES::where('sales_id', $id)->first();
            if ($venta->sales_originID === Null) {
                $venta->sales_user = Auth::user()->username;
            }
        }

        $venta->sales_movement = $ventas_request['movimientos'];
        $venta->sales_issueDate
            = \Carbon\Carbon::now();
        $venta->sales_concept = $ventas_request['concepto'];
        $venta->sales_money = $ventas_request['nameMoneda'];
        $venta->sales_typeChange = $ventas_request['nameTipoCambio'];
        $venta->sales_customer = $ventas_request['proveedorKey'];
        $venta->sales_typeCondition = $ventas_request['tipoCondicion'];
        $venta->sales_condition = $ventas_request['proveedorCondicionPago'];
        $venta->sales_expiration = $ventas_request['proveedorFechaVencimiento'];
        $venta->sales_reference = $ventas_request['proveedorReferencia'];
        $venta->sales_company = session('company')->companies_key;
        $venta->sales_branchOffice = session('sucursal')->branchOffices_key;
        $venta->sales_depot = $ventas_request['almacenKey'];
        $venta->sales_seller = $ventas_request['sellerKey'];
        $venta->sales_identificationCFDI = $ventas_request['clienteCFDI'];
        $venta->sales_status = $this->estatus[0];
        $venta->sales_amount = str_replace(['$', ','], '', $ventas_request['subTotalCompleto']);
        $venta->sales_taxes = str_replace(['$', ','], '', $ventas_request['impuestosCompleto']);
        $venta->sales_total = str_replace(['$', ','], '', $ventas_request['totalCompleto']);
        $venta->sales_retention1 = $ventas_request['porcentajeISR'];
        $venta->sales_retentionISR = str_replace(['$', ','], '', $ventas_request['retencionISR']);
        $venta->sales_retention2 = $ventas_request['porcentajeIVA'];
        $venta->sales_retentionIVA = str_replace(['$', ','], '', $ventas_request['retencionIVA']);
        $venta->sales_lines = $ventas_request['cantidadArticulos'];
        $venta->sales_reasonCancellation = $ventas_request['motivoCancelacion'];
        $venta->sales_stamped = 0;

        if ($ventas_request['folio'] != null) {
            if ($copiaRequest == 'copiar') {
                $venta->sales_originType = null;
                $venta->sales_origin = null;
                $venta->sales_originID = null;
            } else {
                $venta->sales_originType = 'Ventas';
                $venta->sales_origin = $ventas_request['origin']; //origen
                $venta->sales_originID = $ventas_request['folio'];
            }
        } else {
            if ($copiaRequest == 'copiar') {
                $venta->sales_originType = null;
                $venta->sales_origin = null;
                $venta->sales_originID = null;
            } else {
                if ($venta->sales_originID === Null) {
                    $venta->sales_originType = 'Usuario';
                    $venta->sales_origin = Auth::user()->username;
                    $venta->sales_originID = null;
                }
            }
        }

        //Informacion adicional
        $venta->sales_listPrice = $ventas_request['precioListaSelect'];

        // $venta->sales_driver = $ventas_request['choferName'];
        // $venta->sales_vehicle = $ventas_request['vehiculoName'];
        // $venta->sales_identificationCFDI = $ventas_request['clienteCFDI'];
        // $venta->sales_plates = $ventas_request['placas'];
        // $venta->sales_placeDelivery = $ventas_request['lugarEntrega'];
        // $venta->sales_bookingNumber = $ventas_request['numeroBooking'];
        // $venta->sales_stamp = $ventas_request['sello'];
        // $venta->sales_departureDate = $ventas_request['fechaSalida2'];
        // $venta->sales_shipName = $ventas_request['buqueName'];
        // $venta->sales_finalDestiny = $ventas_request['destinoFinal'];
        // $venta->sales_contractNumber = $ventas_request['numeroContrato'];
        // $venta->sales_containerType = $ventas_request['tipoContenedor'];
        // $venta->sales_ticket = $ventas_request['folioTicket'];
        // $venta->sales_material = $ventas_request['material'];
        // $fechaHora2 = $ventas_request['fechaHoraSalida'];

        // if ($fechaHora2 != NULL) {
        //     $fechaHora2 = Carbon::parse($fechaHora2)->format('Y-m-d H:i:s');
        // }
        // $venta->sales_outputWeight = $ventas_request['pesoSalida'];
        // $venta->sales_dateTime = $fechaHora2;

        $venta->created_at = Carbon::now()->format('Y-m-d H:i:s');
        $venta->updated_at = Carbon::now()->format('Y-m-d H:i:s');


        //insertar articulos en la tabla de detalle de compra
        $articulos = $ventas_request['dataArticulosJson'];
        $articulos = json_decode($articulos, true);
        $claveArt = array_keys($articulos);
        //Eliminamos los articulos que no sean necesarios        
        $articulosDelete = json_decode($ventas_request['dataArticulosDelete'], true);

        if ($articulosDelete  != null) {
            foreach ($articulosDelete as $articulo) {
                $detalleVenta = PROC_SALES_DETAILS::where('salesDetails_id', $articulo)->first();
                $detalleVenta->delete();
            }
        }

        try {
            $infComercioExterior = json_decode($request['inputJsonComercioExterior']);
            $cobroFactura = json_decode($request['inputJsonCobroFactura'], true);


            if ($id == 0) {
                $isCreate =  $venta->save();
                $lastId = $venta::latest('sales_id')->first()->sales_id;

                if ($infComercioExterior !== null) {
                    $comercioExterior = new PROC_SALES_FOREIGN_TRADE();
                    $comercioExterior->salesForeingTrade_saleID = $lastId;
                    $comercioExterior->salesForeingTrade_transferReason = $infComercioExterior->mTraslado;
                    $comercioExterior->salesForeingTrade_operationType = $infComercioExterior->tOperacion;
                    $comercioExterior->salesForeingTrade_petitionKey = $infComercioExterior->cPedimento;
                    $comercioExterior->salesForeingTrade_incoterm = $infComercioExterior->IncotermKey;
                    $comercioExterior->salesForeingTrade_subdivision = $infComercioExterior->subdivision;
                    $comercioExterior->salesForeingTrade_certificateOforigin = $infComercioExterior->origen;
                    $comercioExterior->salesForeingTrade_numberCertificateOrigin = $infComercioExterior->cOrigen;
                    $comercioExterior->salesForeingTrade_trustedExportedNumber = $infComercioExterior->eConfiable;
                    $comercioExterior->save();
                }

                if ($cobroFactura !== null) {
                    $cobro = new PROC_SALES_PAYMENT();
                    $cobro->salesPayment_saleID = $lastId;
                    $cobro->salesPayment_paymentMethod1 = $cobroFactura['formaCobro1'];
                    $cobro->salesPayment_paymentMethod2 = $cobroFactura['formaCobro2'];
                    $cobro->salesPayment_paymentMethod3 = $cobroFactura['formaCobro3'];
                    $cobro->salesPayment_amount1 = $cobroFactura['importe1'];
                    $cobro->salesPayment_amount2 = $cobroFactura['importe2'];
                    $cobro->salesPayment_amount3 = $cobroFactura['importe3'];
                    $cobro->salesPayment_fullCharge = $cobroFactura['totalFactura'];
                    $cobro->salesPayment_Change = $cobroFactura['cambio'];
                    $cobro->salesPayment_moneyAccount = $cobroFactura['cuentaPago'];
                    $cobro->salesPayment_moneyAccountType = $cobroFactura['accountType'];
                    $cobro->salesPayment_additionalInformation = $cobroFactura['infAdicional'];
                    $cobro->salesPayment_paymentMethodChange = $cobroFactura['formaCambio7'];
                    $cobro->salesPayment_branchOffice = session('sucursal')->branchOffices_key;
                    $cobro->save();
                }
            } else {
                $isCreate =  $venta->update();
                $lastId = $venta->sales_id;

                if ($infComercioExterior !== null) {
                    $comercioExterior = PROC_SALES_FOREIGN_TRADE::WHERE('salesForeingTrade_saleID', '=', $lastId)->first();

                    if ($comercioExterior === null) {
                        $comercioExterior = new PROC_SALES_FOREIGN_TRADE();
                    }
                    $comercioExterior->salesForeingTrade_saleID = $lastId;
                    $comercioExterior->salesForeingTrade_transferReason = $infComercioExterior->mTraslado;
                    $comercioExterior->salesForeingTrade_operationType = $infComercioExterior->tOperacion;
                    $comercioExterior->salesForeingTrade_petitionKey = $infComercioExterior->cPedimento;
                    $comercioExterior->salesForeingTrade_incoterm = $infComercioExterior->IncotermKey;
                    $comercioExterior->salesForeingTrade_subdivision = $infComercioExterior->subdivision;
                    $comercioExterior->salesForeingTrade_certificateOforigin = $infComercioExterior->origen;
                    $comercioExterior->salesForeingTrade_numberCertificateOrigin = $infComercioExterior->cOrigen;
                    $comercioExterior->salesForeingTrade_trustedExportedNumber = $infComercioExterior->eConfiable;
                    $comercioExterior->save();
                }

                if ($cobroFactura !== null) {
                    $cobroDFactura = PROC_SALES_PAYMENT::WHERE('salesPayment_saleID', '=', $lastId)->first();
                    if ($cobroDFactura === null) {
                        $cobroDFactura = new PROC_SALES_PAYMENT();
                    }
                    $cobroDFactura->salesPayment_saleID = $lastId;
                    $cobroDFactura->salesPayment_paymentMethod1 = $cobroFactura['formaCobro1'];
                    $cobroDFactura->salesPayment_paymentMethod2 = $cobroFactura['formaCobro2'];
                    $cobroDFactura->salesPayment_paymentMethod3 = $cobroFactura['formaCobro3'];
                    $cobroDFactura->salesPayment_amount1 = $cobroFactura['importe1'];
                    $cobroDFactura->salesPayment_amount2 = $cobroFactura['importe2'];
                    $cobroDFactura->salesPayment_amount3 = $cobroFactura['importe3'];
                    $cobroDFactura->salesPayment_fullCharge = $cobroFactura['totalFactura'];
                    $cobroDFactura->salesPayment_Change = $cobroFactura['cambio'];
                    $cobroDFactura->salesPayment_moneyAccount = $cobroFactura['cuentaPago'];
                    $cobroDFactura->salesPayment_moneyAccountType = $cobroFactura['accountType'];
                    $cobroDFactura->salesPayment_additionalInformation = $cobroFactura['infAdicional'];
                    $cobroDFactura->salesPayment_paymentMethodChange = $cobroFactura['formaCambio7'];
                    $cobroDFactura->salesPayment_branchOffice = session('sucursal')->branchOffices_key;
                    $cobroDFactura->save();
                }
            }

            if ($articulos !== null) {
                //Creamos un arreglo donde se almacenara las seriesAsignadas
                $asignacionSeriesB['series'] = [];
                $asignacionIdsSerieB['idSeries'] = [];
                $asignacionKits['kits'] = [];

                foreach ($claveArt as $keyItemArt => $articulo) {
                    if (isset($articulos[$articulo]['id'])) {
                        $detalleCompra = PROC_SALES_DETAILS::where('salesDetails_id', '=', $articulos[$articulo]['id'])->first();
                    } else {
                        $detalleCompra = new PROC_SALES_DETAILS();
                    }
                    $detalleCompra->salesDetails_saleID = $lastId;
                    $articuloClave = explode('-', $articulo);
                    $detalleCompra->salesDetails_article = $articuloClave[0];
                    $detalleCompra->salesDetails_type = $articulos[$articulo]['tipoArticulo'];
                    $detalleCompra->salesDetails_descript = $articulos[$articulo]['desp'];
                    $detalleCompra->salesDetails_observations = $articulos[$articulo]['observacion'];
                    $detalleCompra->salesDetails_quantity = str_replace(['$', ','], '', $articulos[$articulo]['cantidad']);
                    $detalleCompra->salesDetails_unitCost = str_replace(['$', ','], '', $articulos[$articulo]['c_unitario']);

                    $unidadDiv = explode('-', $articulos[$articulo]['unidad']);
                    $detalleCompra->salesDetails_unit = $unidadDiv[0];
                    $detalleCompra->salesDetails_factor = $unidadDiv[1];

                    $detalleCompra->salesDetails_inventoryAmount = str_replace(['$', ','], '', $articulos[$articulo]['c_Inventario']);
                    $detalleCompra->salesDetails_amount = str_replace(['$', ','], '', $articulos[$articulo]['importe']);
                    $detalleCompra->salesDetails_ivaPorcent = str_replace(['$', ','], '', $articulos[$articulo]['iva']);

                    $detalleCompra->salesDetails_amount = str_replace(['$', ','], '', $articulos[$articulo]['importe']);
                    $detalleCompra->salesDetails_ivaPorcent = str_replace(['$', ','], '', $articulos[$articulo]['iva']);
                    $detalleCompra->salesDetails_discountPorcent = $articulos[$articulo]['porcentajeDescuento'];
                    $detalleCompra->salesDetails_discount = $articulos[$articulo]['descuento'];
                    $detalleCompra->salesDetails_retention1 = $articulos[$articulo]['porcentaje_retencion'];
                    $detalleCompra->salesDetails_retentionISR = $articulos[$articulo]['retencion'];
                    $detalleCompra->salesDetails_retention2 = $articulos[$articulo]['porcentaje_retencion2'];
                    $detalleCompra->salesDetails_retentionIVA = $articulos[$articulo]['retencion2'];
                    $detalleCompra->salesDetails_total = str_replace(['$', ','], '', $articulos[$articulo]['importe_total']);
                    $detalleCompra->salesDetails_packingUnit = $articulos[$articulo]['unidadEmpaque'];
                    // $detalleCompra->salesDetails_netQuantity = str_replace(['$', ','], '', $articulos[$articulo]['c_Neta']);

                    if ($ventas_request['folio'] != null || isset($articulos[$articulo]['aplicaIncre'])) {

                        if ($copiaRequest == 'copiar') {
                            $detalleCompra->salesDetails_apply = null;
                            $detalleCompra->salesDetails_applyIncrement = null;
                        } else {
                            if ($detalleCompra->salesDetails_apply == null) {
                                $detalleCompra->salesDetails_apply = $ventas_request['origin']; //origen
                            }

                            if ($detalleCompra->salesDetails_applyIncrement == null) {
                                $detalleCompra->salesDetails_applyIncrement = $ventas_request['folio']; //folio
                            }


                            if ($detalleCompra['folio'] != null) {
                                $detalleCompra->salesDetails_applyIncrement = $ventas_request['folio'];
                            }

                            // if(isset($articulos[$articulo]['aplicaIncre'])){
                            //     $detalleCompra->salesDetails_applyIncrement = $articulos[$articulo]['aplicaIncre'];
                            // }

                        }
                    } else {
                        $detalleCompra->salesDetails_apply = null;
                        $detalleCompra->salesDetails_applyIncrement = null;
                    }

                    $detalleCompra->salesDetails_branchOffice = session('sucursal')->branchOffices_key;
                    $detalleCompra->salesDetails_depot = $ventas_request['almacenKey'];
                    $detalleCompra->salesDetails_outstandingAmount = null;
                    $detalleCompra->salesDetails_canceledAmount = null;

                    if ($detalleCompra->salesDetails_referenceArticles === null) {
                        $detalleCompra->salesDetails_referenceArticles = isset($articulos[$articulo]['referenceArticle']) ? $articulos[$articulo]['referenceArticle'] : null;
                    }

                    if (array_key_exists('listaEmpaques', $articulos[$articulo])) {
                        $detalleCompra->salesDetails_advanced = isset($articulos[$articulo]['listaEmpaques']['progreso']) ? $articulos[$articulo]['listaEmpaques']['progreso'] : null;
                    }

                    $detalleCompra->save();

                    if ($copiaRequest != 'copiar') {
                        if (isset($articulos[$articulo]['venderSerie']) && $articulos[$articulo]['tipoArticulo'] == "Serie") {
                            $lastIdDetalle = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $lastId)->where('salesDetails_article', '=', $articuloClave[0])->select('salesDetails_id')->first();

                            $articuloFila = $articuloClave[0] . '-' . $keyItemArt;
                            //Series normales
                            $series = [];
                            $idSeries = [];

                            foreach ($articulos[$articulo]['venderSerie'] as $key => $value) {
                                if (isset($articulos[$articulo]['venderIdsSerie'])) {
                                    if ($key <=  (count($articulos[$articulo]['venderIdsSerie']) - 1)) {
                                        if ($articulos[$articulo]['venderIdsSerie'][$key] != 'undefined') {
                                            $proc_del_series_mov = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_companieKey', '=', $venta->sales_company)->where('delSeriesMov2_branchKey', '=', $venta->sales_branchOffice)->where('delSeriesMov2_saleID', "=", $lastId)->where('delSeriesMov2_article', "=", $articuloClave[0])->where('delSeriesMov2_id', "=", $articulos[$articulo]['venderIdsSerie'][$key])->first();
                                            if ($proc_del_series_mov == null && $venta->sales_origin == 'Pedido') {
                                                //Creamos nuevas series con diferente venta
                                                $proc_del_series_mov = new PROC_DEL_SERIES_MOV2();
                                                $proc_del_series_mov->delSeriesMov2_articleID =  $lastIdDetalle['salesDetails_id'];
                                            }
                                        } else {
                                            $proc_del_series_mov = new PROC_DEL_SERIES_MOV2();
                                            $proc_del_series_mov->delSeriesMov2_articleID =  $lastIdDetalle['salesDetails_id'];
                                        }
                                    } else {
                                        $proc_del_series_mov = new PROC_DEL_SERIES_MOV2();
                                        $proc_del_series_mov->delSeriesMov2_articleID =  $lastIdDetalle['salesDetails_id'];
                                    }
                                } else {
                                    $proc_del_series_mov = new PROC_DEL_SERIES_MOV2();
                                    $proc_del_series_mov->delSeriesMov2_articleID =  $lastIdDetalle['salesDetails_id'];
                                }



                                $proc_del_series_mov->delSeriesMov2_companieKey =  $venta->sales_company;
                                $proc_del_series_mov->delSeriesMov2_branchKey = $venta->sales_branchOffice;
                                $proc_del_series_mov->delSeriesMov2_module = 'Ventas';
                                $proc_del_series_mov->delSeriesMov2_saleID = $lastId;
                                $proc_del_series_mov->delSeriesMov2_article = $articuloClave[0];
                                $proc_del_series_mov->delSeriesMov2_lotSerie = $value;
                                $proc_del_series_mov->delSeriesMov2_quantity = 1;
                                $proc_del_series_mov->delSeriesMov2_cancelled = 0;
                                $proc_del_series_mov->delSeriesMov2_affected = 0;
                                // dd($proc_del_series_mov);


                                $proc_del_series_mov->save();

                                //Obtenemos el ultimo id o el id actualizado de la serie
                                $lastIdSeries = isset($proc_del_series_mov->delSeriesMov2_id) ? $proc_del_series_mov->delSeriesMov2_id : PROC_DEL_SERIES_MOV2::latest('delSeriesMov2_id')->first();

                                $series = [
                                    ...$series,
                                    $value,
                                ];

                                $idSeries = [
                                    ...$idSeries,
                                    $lastIdSeries
                                ];
                            }

                            if (!array_key_exists($articuloFila, $asignacionSeriesB['series'])) {
                                $asignacionSeriesB['series'][$articuloFila] = $series;
                            } else {
                                array_push($asignacionSeriesB['series'][$articuloFila], $series);
                            }

                            if (!array_key_exists($articuloFila, $asignacionIdsSerieB['idSeries'])) {
                                $asignacionIdsSerieB['idSeries'][$articuloFila] = $idSeries;
                            } else {
                                array_push($asignacionIdsSerieB['idSeries'][$articuloFila], $idSeries);
                            }
                        }

                        if (isset($articulos[$articulo]['ventaKit']) && $articulos[$articulo]['ventaKit'] != null && $articulos[$articulo]['tipoArticulo'] == "Kit") {
                            $articuloFila = $articuloClave[0] . '-' . $keyItemArt;
                            //Series normales
                            $kitInformacion = [];
                            foreach ($articulos[$articulo]['ventaKit']['articulos'] as $articuloKit) {
                                if (isset($articuloKit['kitId']) && $articuloKit['kitId'] != '') {
                                    $detalleVentaKit = PROC_KIT_ARTICLES::where('procKit_id', '=', $articuloKit['kitId'])->where('procKit_saleID', '=', $lastId)->first();

                                    if ($detalleVentaKit == null) {
                                        $detalleVentaKit = new PROC_KIT_ARTICLES();

                                        $detalleVentaKit->procKit_articleIDReference = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $lastId)
                                            ->where('salesDetails_article', '=', $articuloClave[0])
                                            ->where('salesDetails_observations', '=', $detalleCompra->salesDetails_observations)
                                            ->first()->salesDetails_id;
                                    }
                                } else {
                                    $detalleVentaKit = new PROC_KIT_ARTICLES();

                                    $detalleVentaKit->procKit_articleIDReference = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $lastId)
                                        ->where('salesDetails_article', '=', $articuloClave[0])
                                        ->where('salesDetails_observations', '=', $detalleCompra->salesDetails_observations)
                                        ->first()->salesDetails_id;
                                }

                                $detalleVentaKit->procKit_article = $articuloClave[0];
                                $detalleVentaKit->procKit_articleID = $articuloKit['articuloId'];
                                $detalleVentaKit->procKit_saleID = $lastId;
                                $detalleVentaKit->procKit_articleDesp = $articuloKit['descripcion'];
                                $detalleVentaKit->procKit_cantidad = $articuloKit['cantidad'];
                                $detalleVentaKit->procKit_tipo = $articuloKit['tipo'];
                                $detalleVentaKit->procKit_observation = $articuloKit['observaciones'];
                                $detalleVentaKit->procKit_affected = 0;
                                $detalleVentaKit->save();


                                //Obtenemos la informacion del kit
                                $lastIdKit = isset($detalleVentaKit->procKit_id) ? $detalleVentaKit->procKit_id : PROC_KIT_ARTICLES::latest('procKit_id')->SELECT('procKit_id')->first()->procKit_id;


                                $kitInformacion = [
                                    ...$kitInformacion,
                                    'articuloId' => $articuloKit['articuloId'],
                                    'cantidad' => $articuloKit['cantidad'],
                                    'descripcion' => $articuloKit['descripcion'],
                                    'componente' =>  $articuloKit['descripcion'],
                                    'disponible' =>  $articuloKit['disponible'],
                                    'tipo' =>  $articuloKit['tipo'],
                                    'articulo' => $articuloClave[0],
                                    'observaciones' => $articuloKit['observaciones'],
                                    'kitId' => $lastIdKit,
                                ];

                                if (isset($articulos[$articulo]['ventaKit']['ventaSeriesKits'])) {
                                    $lastIdDetalle = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $lastId)->where('salesDetails_article', '=', $articuloClave[0])->select('salesDetails_id')->first();
                                    $seriesTrans42 = [];
                                    $seriesTransId42 = [];

                                    foreach ($articulos[$articulo]['ventaKit']['ventaSeriesKits'] as $key => $value) {
                                        if ($key == $articuloKit['articuloId']) {
                                            if (isset($value['serie']) && $value['serie'] != null) {
                                                foreach ($value['serie'] as $serie) {

                                                    if (isset($value['ids']) && $value['ids'] != '' && $value['ids'] != null && $value['ids'] != 'undefined') {
                                                        $proc_del_series_mov = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_id', '=', $value['ids'])->first();

                                                        if ($venta->sales_origin == 'Pedido') {
                                                            //Creamos nuevas series con diferente venta
                                                            $proc_del_series_mov = new PROC_DEL_SERIES_MOV2();
                                                            $proc_del_series_mov->delSeriesMov2_articleID =  $lastIdDetalle['salesDetails_id'];
                                                        }
                                                    } else {

                                                        $proc_del_series_mov = new PROC_DEL_SERIES_MOV2();
                                                    }

                                                    $proc_del_series_mov->delSeriesMov2_articleID =  $articuloClave[0];
                                                    $proc_del_series_mov->delSeriesMov2_companieKey =  $venta->sales_company;
                                                    $proc_del_series_mov->delSeriesMov2_branchKey = $venta->sales_branchOffice;
                                                    $proc_del_series_mov->delSeriesMov2_module = 'Ventas';
                                                    $proc_del_series_mov->delSeriesMov2_saleID = $lastId;
                                                    $proc_del_series_mov->delSeriesMov2_article = $articuloKit['articuloId'];
                                                    $proc_del_series_mov->delSeriesMov2_lotSerie = $serie;
                                                    $proc_del_series_mov->delSeriesMov2_quantity = 1;
                                                    $proc_del_series_mov->delSeriesMov2_cancelled = 0;
                                                    $proc_del_series_mov->delSeriesMov2_affected = 0;
                                                    $proc_del_series_mov->save();

                                                    //Obtenemos la informacion del kit
                                                    $lastIdKitSeries = isset($proc_del_series_mov->delSeriesMov2_id) ? $proc_del_series_mov->delSeriesMov2_id : PROC_DEL_SERIES_MOV2::latest('delSeriesMov2_id')->SELECT('delSeriesMov2_id')->first()->delSeriesMov2_id;


                                                    $seriesTrans42 = [
                                                        ...$seriesTrans42,
                                                        $serie,
                                                    ];

                                                    $seriesTransId42 = [
                                                        ...$seriesTransId42,
                                                        $lastIdKitSeries
                                                    ];
                                                }
                                            }
                                        }
                                    }
                                }

                                if (!array_key_exists($articuloFila, $asignacionKits['kits'])) {
                                    if (count($seriesTrans42) > 0) {
                                        $asignacionKits['kits'][$articuloFila] = [
                                            'articulos' => [$kitInformacion],
                                            'cantidad' => str_replace(['$', ','], '', $articulos[$articulo]['cantidad']),
                                            'ventaSeriesKits' => array(
                                                $articuloKit['articuloId'] =>  array(
                                                    "serie" => $seriesTrans42,
                                                    "ids" => $seriesTransId42
                                                )
                                            )
                                        ];
                                    } else {
                                        $asignacionKits['kits'][$articuloFila] = [
                                            'articulos' => [$kitInformacion],
                                            'cantidad' => str_replace(['$', ','], '', $articulos[$articulo]['cantidad']),
                                            'ventaSeriesKits' => []
                                        ];
                                    }
                                } else {
                                    $asignacionKits['kits'][$articuloFila]['articulos'] = [...$asignacionKits['kits'][$articuloFila]['articulos'], $kitInformacion];
                                    $asignacionKits['kits'][$articuloFila]['cantidad'] =  str_replace(['$', ','], '', $articulos[$articulo]['cantidad']);

                                    if (isset($asignacionKits['kits'][$articuloFila]['ventaSeriesKits'][$articuloKit['articuloId']]) && count($seriesTrans42) > 0) {
                                        $asignacionKits['kits'][$articuloFila]['ventaSeriesKits'][$articuloKit['articuloId']]['serie'] = [
                                            ...$asignacionKits['kits'][$articuloFila]['ventaSeriesKits'][$articuloKit['articuloId']]['serie'],
                                            $seriesTrans42
                                        ];

                                        $asignacionKits['kits'][$articuloFila]['ventaSeriesKits'][$articuloKit['articuloId']]['ids'] = [
                                            ...$asignacionKits['kits'][$articuloFila]['ventaSeriesKits'][$articuloKit['articuloId']]['ids'],
                                            $seriesTransId42
                                        ];
                                    } else {
                                        $keysVentasSeries = array_keys($asignacionKits['kits'][$articuloFila]['ventaSeriesKits']);
                                        $keysC = [];
                                        if (count($keysVentasSeries) == 0 && count($seriesTrans42) > 0) {
                                            $asignacionKits['kits'][$articuloFila]['ventaSeriesKits'] = array(
                                                $articuloKit['articuloId'] =>  array(
                                                    "serie" => $seriesTrans42,
                                                    "ids" => $seriesTransId42
                                                )
                                            );
                                        }

                                        foreach ($keysVentasSeries as $key => $keyComponente) {
                                            if (count($asignacionKits['kits'][$articuloFila]['ventaSeriesKits'][$keyComponente]['serie']) > 0) {
                                                $keysC[$keyComponente] = $asignacionKits['kits'][$articuloFila]['ventaSeriesKits'][$keyComponente];
                                            }
                                        }

                                        if (isset($keysC) && count($seriesTrans42) > 0) {
                                            $keysC[$articuloKit['articuloId']] = array(
                                                "serie" => $seriesTrans42,
                                                "ids" => $seriesTransId42
                                            );
                                            $asignacionKits['kits'][$articuloFila]['ventaSeriesKits'] = $keysC;
                                        }
                                    }
                                }
                            }
                        }

                        if ($ventas_request['movimientos'] == 'Pedido') {

                            if (isset($articulos[$articulo]['listaEmpaques']) && $articulos[$articulo]['listaEmpaques'] != null) {

                                foreach ($articulos[$articulo]['listaEmpaques']['empaques'] as $articulo2) {

                                    if (isset($articulo2['idEmpaque']) && $articulo2['idEmpaque'] != '') {
                                        $listaEmpaque = PROC_PACKINGLIST::where('packingList_id', '=', $articulo2['idEmpaque'])->first();
                                    } else {
                                        $listaEmpaque = new PROC_PACKINGLIST();
                                    }

                                    $listaEmpaque->packingList_saleID = $lastId;
                                    $listaEmpaque->packingList_article = $articuloClave[0];
                                    $listaEmpaque->packingList_companieKey = session('company')->companies_key;
                                    $listaEmpaque->packingList_branchKey = session('sucursal')->branchOffices_key;
                                    $listaEmpaque->packingList_module = 'Ventas';
                                    $listaEmpaque->packingList_unidPack = $articulo2['unidad'];
                                    $listaEmpaque->packingList_quantity = $articulos[$articulo]['listaEmpaques']['cantidad'];
                                    $listaEmpaque->packingList_weight = $articulo2['peso'];
                                    $listaEmpaque->packingList_weightUnid = $articulo2['pesoUnidad'];
                                    $listaEmpaque->packingList_weightNet = $articulo2['pesoNeto'];
                                    $listaEmpaque->packingList_weightNet = $articulo2['pesoNeto'];
                                    $listaEmpaque->packingList_advance = $articulo2['porcentaje'];
                                    if (isset($articulo2['idEmpaque']) && $articulo2['idEmpaque'] != '') {
                                    } else {
                                        $listaEmpaque->packingList_articleID = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $lastId)->where('salesDetails_article', '=', $articuloClave[0])->first()->salesDetails_id;
                                    }


                                    $listaEmpaque->save();
                                }
                            }
                        }
                    }
                }

                if ($copiaRequest != 'copiar') {
                    $progreso = 0;
                    if ($ventas_request['movimientos'] == 'Pedido') {
                        $pedido = PROC_SALES::where('sales_id', '=', $lastId)->first();
                        $articulosPedido = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $pedido->sales_id)->get();
                        // dd($articulosPedido);
                        if (count($articulosPedido) > 0) {
                            foreach ($articulosPedido as $articuloPedido) {
                                $progreso += $articuloPedido->salesDetails_advanced;
                            }
                            $pedido->sales_advanced = $progreso / count($articulosPedido);
                            //  dd($progreso, count($articulosPedido));
                            $pedido->save();
                        }
                    }
                }
            }

            //Formamos nuestro json de series y idseries
            //Obtenemos las keys de un arreglo

            if ($copiaRequest != 'copiar') {
                $contenedorKits = [];
                $contenedorSeries = [];
                if (count($asignacionSeriesB['series']) > 0) {
                    $contenedorSeries = array_merge($asignacionSeriesB, $asignacionIdsSerieB);
                }

                if (count($asignacionKits['kits']) > 0) {
                    $contenedorKits = $asignacionKits;
                }

                if ($lastId != 0 && (isset($contenedorSeries) || isset($contenedorKits))) {
                    $contenedorFinal = array_merge($contenedorKits, $contenedorSeries);
                    $jsonDetalle = json_encode($contenedorFinal);
                    $venta = PROC_SALES::WHERE('sales_id', '=', $lastId)->first();
                    $venta->sales_jsonData = $jsonDetalle;
                    $venta->save();
                }
            }



            if ($isCreate) {
                if ($ventas_request['movimientos'] == 'Cotizacion' || $ventas_request['movimientos'] == 'Factura') {
                    $message = 'La ' . $ventas_request['movimientos'] . ' se ha creado correctamente';
                } else {
                    $message = 'El ' . $ventas_request['movimientos'] . ' se ha creado correctamente';
                }
                $status = true;
            } else {
                $message = $id == 0 ? 'Error al crear la venta' : 'Error al actualizar la venta';
                $status = false;
            }
        } catch (\Throwable $th) {
            dd($th);
            $message = $id == 0 ? "Por favor, vaya con el administrador de sistemas, no se pudo crear la venta" : "Por favor, vaya con el administrador de sistemas, no se pudo actualizar la venta";
            return redirect()->route('vista.modulo.ventas.create-venta')->with('message', $message)->with('status', false);
        }

        return redirect()->route('vista.modulo.ventas.create-venta', $id == 0 ? $lastId : $id)->with('message', $message)->with('status', $status);
    }

    public function afectar(Request $request)
    {
        $id = $request->id;


        //validar que la factura tenga todo lo necesario para la afectación
        if ($request['movimientos'] === 'Factura') {
            if (session('company')->companies_stamped == '1') {
                if (session('company')->companies_calculateTaxes == '0') {
                    $message = $this->validacionesFactura(session('company')->companies_key, $request['proveedorKey'], $request['dataArticulosJson']);
                    if ($message) {
                        $message = $message;
                        $status = 500;
                        $lastId = false;
                        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                    }

                    if (isset($request['inputJsonComercioExterior']) && $request['inputJsonComercioExterior'] != null) {
                        $message = $this->validacionesFacturaExterior($request['dataArticulosJson']);
                        if ($message) {
                            $message = $message;
                            $status = 500;
                            $lastId = false;
                            return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                        }
                    }
                }
            }
        }

        // dd($request->all());



        if ($request['movimientos'] === 'Factura' && $request['tipoCondicion'] === 'Contado') {
            $cobros = $request['inputJsonCobroFactura'];
            $cobros = json_decode($cobros, true);

            $modena = $request['nameMoneda'];
            $moneda = CONF_MONEY::where('money_key', '=',  $modena)->first();

            //validar que la cuenta de dinero sea la misma que la moneda de la venta
            $cuentaDinero = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', '=', $cobros['cuentaPago'])->first();

            if ($cuentaDinero->moneyAccountsBalance_money != $modena) {
                $procesar = false;
                $message = 'La moneda de la cuenta de dinero no es la misma que la moneda de la venta';
                $status = 500;
                $lastId = false;
                return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
            }

            if ($cuentaDinero->moneyAccountsBalance_accountType === 'Caja') {
                //validar la forma de cambio sea misma que la moneda de la venta
                if ($cobros['formaCambio7'] !== null) {
                    $formaCambio = CONF_FORMS_OF_PAYMENT::where('formsPayment_key', '=', $cobros['formaCambio7'])->first();

                    if (trim($formaCambio->formsPayment_money) != $moneda->money_keySat) {
                        $procesar = false;
                        $message = 'La moneda de la forma de cambio no es la misma que la moneda de la venta';
                        $status = 500;
                        $lastId = false;
                        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                    }
                }
            }

            //buscamos la moneda del la forma cobro
            $formaCobro = CONF_FORMS_OF_PAYMENT::where('formsPayment_key', '=', $cobros['formaCobro1'])->first();
            $formaCobro2 = CONF_FORMS_OF_PAYMENT::where('formsPayment_key', '=', $cobros['formaCobro2'])->first();
            $formaCobro3 = CONF_FORMS_OF_PAYMENT::where('formsPayment_key', '=', $cobros['formaCobro3'])->first();
            //  dd(trim($formaCobro->formsPayment_money), $moneda->moneykeySat);

            if ($formaCobro != null) {
                if (trim($formaCobro->formsPayment_money) != $moneda->money_keySat) {
                    $procesar = false;
                    $message = 'La moneda de la forma de cobro 1 no es la misma que la moneda de la venta';
                    $status = 500;
                    $lastId = false;
                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                }
            }


            if ($formaCobro2 != null) {
                if (trim($formaCobro2->formsPayment_money) != $moneda->money_keySat) {
                    $procesar = false;
                    $message = 'La moneda de la forma de cobro 2 no es la misma que la moneda de la venta';
                    $status = 500;
                    $lastId = false;

                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                }
            }


            if ($formaCobro3 != null) {
                if (trim($formaCobro3->formsPayment_money) != $moneda->money_keySat) {
                    $procesar = false;
                    $message = 'La moneda de la forma de cobro 3 no es la misma que la moneda de la venta';
                    $status = 500;
                    $lastId = false;

                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                }
            }
        }


        if ($request['movimientos'] === 'Factura') {

            //sacar los articulos de la venta
            $articulos = $request['dataArticulosJson'];
            $articulos = json_decode($articulos, true);
            $claveArt = array_keys($articulos);

            // dd($claveArt, $articulos);
            $procesar = false;


            if (session('generalParameters')->generalParameters_billsNot == "0" || session('generalParameters')->generalParameters_billsNot == 0) {
                $procesar = true;
                foreach ($claveArt as $key => $articulo) {
                    $articuloInd = explode('-', $articulo);
                    //validamos que haya stock para el articulo

                    $articuloTipo = CAT_ARTICLES::where('articles_key', '=', $articuloInd[0])->first();

                    if ($articuloTipo->articles_type != 'Kit' && $articuloTipo->articles_type != 'Servicio') {

                        $validar = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articuloInd[0])->where('articlesInv_depot', '=', $request['almacenKey'])->first();

                        // dd($articuloInd, $validar, $articuloTipo);
                        if ($validar == null) {
                            $procesar = false;
                            //mostramos el mensaje de que no hay stock para el articulo seleccionado
                            $message = 'No hay stock para el articulo: ' . $articuloInd[0] . "-" . $articulos[$articulo]['desp'] . ' en el almacen ' . $request['almacenKey'];
                            $status = 500;
                            $lastId = false;
                            return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                        }

                        $articuloVenta = (float) $articulos[$articulo]['c_Inventario'];
                        $disponible = (float) $validar->articlesInv_inventory;

                        if ($articuloVenta <= $disponible) {
                            $procesar = true;
                        } else {
                            $message = 'No hay stock para el articulo: ' . $articuloInd[0] . "-" . $articulos[$articulo]['desp'] . ' en el almacen ' . $request['almacenKey'];

                            $status = 500;
                            $lastId = false;

                            return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                        }
                    }

                    if ($articuloTipo->articles_type == 'Kit') {
                        $articulosKits = $articulos[$articulo]['ventaKit'];
                        foreach ($articulosKits as $key => $articuloKit) {
                            foreach ($articuloKit as $key => $articuloKit2) {
                                $validar = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articuloKit2['articuloId'])->where('articlesInv_depot', '=', $request['almacenKey'])->first();
                                if ($validar == null) {
                                    $procesar = false;
                                    //mostramos el mensaje de que no hay stock para el articulo seleccionado
                                    $message = 'No hay stock para el articulo: ' . $articuloKit2['articuloId'] . ' en el almacen ' . $request['almacenKey'];
                                    $status = 500;
                                    $lastId = false;
                                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                                }

                                $articuloVenta = (float) $articuloKit2['cantidad'];
                                $disponible = (float) $validar->articlesInv_inventory;

                                if ($articuloVenta <= $disponible) {
                                    $procesar = true;
                                } else {
                                    $message = 'No hay stock para el articulo: ' . $articuloKit2['articuloId'] . ' en el almacen ' . $request['almacenKey'];

                                    $status = 500;
                                    $lastId = false;

                                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                                }

                                break;
                            }

                            //salir del foreach
                            break;
                        }
                    }
                }
            } else {
                $procesar = true;
            }
        } else {
            $procesar = true;
        }

        if ($procesar == true) {
            if ($id == 0) {
                $venta = new PROC_SALES();
            } else {
                $venta = PROC_SALES::where('sales_id', $request->id)->first();
            }

            $movimientoIn = $request->movimientos;


            $venta->sales_movement = $request->movimientos;
            $venta->sales_issueDate = $request->fechaEmision;
            $venta->sales_concept = $request->concepto;
            $venta->sales_money = $request->nameMoneda;
            $venta->sales_typeChange = $request->nameTipoCambio;
            $venta->sales_customer = $request->proveedorKey;
            $venta->sales_typeCondition = $request->tipoCondicion;
            $venta->sales_condition = $request->proveedorCondicionPago;
            $venta->sales_expiration = $request->proveedorFechaVencimiento;
            // $vencimiento2 = Carbon::parse($vencimiento)->format('Y-m-d');

            $venta->sales_reference = $request->proveedorReferencia;
            $venta->sales_company = session('company')->companies_key;
            $venta->sales_branchOffice = session('sucursal')->branchOffices_key;
            $venta->sales_depot = $request->almacenKey;
            $venta->sales_seller = $request->sellerKey;
            $venta->sales_identificationCFDI = $request->clienteCFDI;
            $venta->sales_user = Auth::user()->username;
            $venta->sales_reasonCancellation = $request['motivoCancelacion'];
            $venta->sales_stamped = 0;

            switch ($movimientoIn) {
                case 'Cotización':
                    $venta->sales_status = $this->estatus[1]; //AFECTADA POR AUTORIZAR
                    break;

                case 'Pedido':
                    $venta->sales_status = $this->estatus[1]; //AFECTADA CONCLUIDA
                    break;
                case 'Factura':
                    $venta->sales_status = $this->estatus[2]; //AFECTADA CONCLUIDA
                    break;
                case 'Rechazo de Venta':
                    $venta->sales_status = $this->estatus[2]; //AFECTADA CONCLUIDA
                    break;
                default:
                    # code...
                    break;
            }

            $venta->sales_amount = str_replace(['$', ','], '', $request['subTotalCompleto']);
            $venta->sales_taxes = str_replace(['$', ','], '', $request['impuestosCompleto']);
            $venta->sales_total = str_replace(['$', ','], '', $request['totalCompleto']);
            $venta->sales_retention1 = $request['porcentajeISR'];
            $venta->sales_retentionISR = str_replace(['$', ','], '', $request['retencionISR']);
            $venta->sales_retention2 = $request['porcentajeIVA'];
            $venta->sales_retentionIVA = str_replace(['$', ','], '', $request['retencionIVA']);
            $venta->sales_lines = $request->cantidadArticulos;
            $venta->sales_listPrice = $request->precioListaSelect;


            // $venta->sales_driver = $request['choferName'];
            // $venta->sales_vehicle = $request['vehiculoName'];
            // $venta->sales_identificationCFDI = $request['clienteCFDI'];
            // $venta->sales_plates = $request['placas'];
            // $venta->sales_placeDelivery = $request['lugarEntrega'];
            // $venta->sales_bookingNumber = $request['numeroBooking'];
            // $venta->sales_stamp = $request['sello'];
            // $venta->sales_departureDate = $request['fechaSalida2'];
            // $venta->sales_shipName = $request['buqueName'];
            // $venta->sales_finalDestiny = $request['destinoFinal'];
            // $venta->sales_contractNumber = $request['numeroContrato'];
            // $venta->sales_containerType = $request['tipoContenedor'];
            // $venta->sales_ticket = $request['folioTicket'];
            // $venta->sales_material = $request['material'];
            // $fechaHora2 = $request['fechaHoraSalida'];

            // if ($fechaHora2 != NULL) {
            //     $fechaHora2 = Carbon::parse($fechaHora2)->format('Y-m-d H:i:s');
            // }
            // $venta->sales_outputWeight = $request->pesoSalida;
            // $venta->sales_dateTime = $fechaHora2;

            $venta->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $venta->updated_at = Carbon::now()->format('Y-m-d H:i:s');

            $articulos = $request->dataArticulosJson;
            $articulos = json_decode($articulos, true);
            $claveArt = array_keys($articulos);


            //Eliminamos los articulos que no sean necesarios        
            $articulosDelete = json_decode($request['dataArticulosDelete'], true);


            if ($articulosDelete  != null) {
                foreach ($articulosDelete as $articulo) {
                    $detalleCompra = PROC_SALES_DETAILS::where('salesDetails_id', $articulo)->first();
                    $detalleCompra->delete();
                }
            }

            try {
                if ($id == 0) {
                    $isCreate =  $venta->save();
                    $lastId = $venta::latest('sales_id')->first()->sales_id;
                } else {
                    $isCreate = $venta->update();
                    $lastId = $venta->sales_id;
                }

                $folioAfectar = PROC_SALES::where('sales_id', $lastId)->first();
                $tipoMovimiento = $folioAfectar->sales_movement;


                $this->actualizarFolio($tipoMovimiento, $folioAfectar);


                //Actualizar articulos en la tabla de detalle de compra y la compra anterior
                // dd($articulos);
                if ($articulos !== null) {
                    //Creamos un arreglo donde se almacenara las seriesAsignadas
                    $asignacionSeriesB['series'] = [];
                    $asignacionIdsSerieB['idSeries'] = [];
                    $asignacionKits['kits'] = [];

                    foreach ($claveArt as $keyItemArt => $articulo) {
                        if (isset($articulos[$articulo]['id'])) {
                            $detalleCompra = PROC_SALES_DETAILS::where('salesDetails_id', '=', $articulos[$articulo]['id'])->first();
                        } else {
                            $detalleCompra = new PROC_SALES_DETAILS();
                        }
                        $detalleCompra->salesDetails_saleID = $lastId;
                        $articuloClave = explode('-', $articulo);
                        $detalleCompra->salesDetails_article = $articuloClave[0];
                        $detalleCompra->salesDetails_type = $articulos[$articulo]['tipoArticulo'];
                        $detalleCompra->salesDetails_descript = $articulos[$articulo]['desp'];
                        $detalleCompra->salesDetails_observations = $articulos[$articulo]['observacion'];
                        $detalleCompra->salesDetails_quantity = str_replace(['$', ','], '', $articulos[$articulo]['cantidad']);
                        $detalleCompra->salesDetails_unitCost = str_replace(['$', ','], '', $articulos[$articulo]['c_unitario']);

                        $unidadDiv = explode('-', $articulos[$articulo]['unidad']);
                        $detalleCompra->salesDetails_unit = $unidadDiv[0];
                        $detalleCompra->salesDetails_factor = $unidadDiv[1];

                        $detalleCompra->salesDetails_inventoryAmount = str_replace(['$', ','], '', $articulos[$articulo]['c_Inventario']);
                        $detalleCompra->salesDetails_amount = str_replace(['$', ','], '', $articulos[$articulo]['importe']);
                        $detalleCompra->salesDetails_discountPorcent = $articulos[$articulo]['porcentajeDescuento'];
                        $detalleCompra->salesDetails_discount = $articulos[$articulo]['descuento'];
                        $detalleCompra->salesDetails_ivaPorcent = str_replace(['$', ','], '', $articulos[$articulo]['iva']);
                        $detalleCompra->salesDetails_retention1 = $articulos[$articulo]['porcentaje_retencion'];
                        $detalleCompra->salesDetails_retentionISR = $articulos[$articulo]['retencion'];
                        $detalleCompra->salesDetails_retention2 = $articulos[$articulo]['porcentaje_retencion2'];
                        $detalleCompra->salesDetails_retentionIVA = $articulos[$articulo]['retencion2'];
                        $detalleCompra->salesDetails_total = str_replace(['$', ','], '', $articulos[$articulo]['importe_total']);
                        $detalleCompra->salesDetails_packingUnit = $articulos[$articulo]['unidadEmpaque'];
                        // $detalleCompra->salesDetails_netQuantity = str_replace(['$', ','], '',$articulos[$articulo]['c_Neta']);


                        if (isset($articulos[$articulo]['aplicaIncre'])) {
                            $detalleCompra->salesDetails_apply = $folioAfectar->sales_origin; //origen
                            $detalleCompra->salesDetails_applyIncrement = $folioAfectar->sales_originID;
                        } else {
                            $detalleCompra->salesDetails_apply = null;
                            $detalleCompra->salesDetails_applyIncrement = null;
                        }




                        $detalleCompra->salesDetails_branchOffice = session('sucursal')->branchOffices_key;
                        $detalleCompra->salesDetails_depot = $request['almacenKey'];

                        if ($movimientoIn == 'Cotización' || $movimientoIn == 'Pedido') {
                            $detalleCompra->salesDetails_outstandingAmount = str_replace(['$', ','], "", $articulos[$articulo]['cantidad']);
                        } else {
                            $detalleCompra->salesDetails_outstandingAmount = null;
                        }

                        $detalleCompra->salesDetails_canceledAmount = null;
                        if (array_key_exists('listaEmpaques', $articulos[$articulo])) {
                            $detalleCompra->salesDetails_advanced = isset($articulos[$articulo]['listaEmpaques']['progreso']) ? $articulos[$articulo]['listaEmpaques']['progreso'] : null;
                        }
                        $detalleCompra->save();


                        if (isset($articulos[$articulo]['venderSerie']) && $articulos[$articulo]['tipoArticulo'] == "Serie") {
                            $lastIdDetalle = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $lastId)->where('salesDetails_article', '=', $articuloClave[0])->select('salesDetails_id')->first();

                            $articuloFila = $articuloClave[0] . '-' . $keyItemArt;
                            //Series normales
                            $series = [];
                            $idSeries = [];

                            foreach ($articulos[$articulo]['venderSerie'] as $key => $value) {
                                if (isset($articulos[$articulo]['venderIdsSerie'])) {
                                    if ($key <=  (count($articulos[$articulo]['venderIdsSerie']) - 1)) {
                                        if ($articulos[$articulo]['venderIdsSerie'][$key] != 'undefined') {
                                            $proc_del_series_mov = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_companieKey', '=', $venta->sales_company)->where('delSeriesMov2_branchKey', '=', $venta->sales_branchOffice)->where('delSeriesMov2_saleID', "=", $lastId)->where('delSeriesMov2_article', "=", $articuloClave[0])->where('delSeriesMov2_id', "=", $articulos[$articulo]['venderIdsSerie'][$key])->first();

                                            if ($proc_del_series_mov == null) {
                                                //Creamos nuevas series con diferente venta
                                                $proc_del_series_mov = new PROC_DEL_SERIES_MOV2();
                                                $proc_del_series_mov->delSeriesMov2_articleID =  $lastIdDetalle['salesDetails_id'];
                                            }

                                            if ($proc_del_series_mov == null && $venta->sales_origin == 'Pedido') {
                                                //Creamos nuevas series con diferente venta
                                                $proc_del_series_mov = new PROC_DEL_SERIES_MOV2();
                                                $proc_del_series_mov->delSeriesMov2_articleID =  $lastIdDetalle['salesDetails_id'];
                                            }
                                        } else {
                                            $proc_del_series_mov = new PROC_DEL_SERIES_MOV2();
                                            $proc_del_series_mov->delSeriesMov2_articleID =  $lastIdDetalle['salesDetails_id'];
                                        }
                                    } else {
                                        $proc_del_series_mov = new PROC_DEL_SERIES_MOV2();
                                        $proc_del_series_mov->delSeriesMov2_articleID =  $lastIdDetalle['salesDetails_id'];
                                    }
                                } else {
                                    $proc_del_series_mov = new PROC_DEL_SERIES_MOV2();
                                    $proc_del_series_mov->delSeriesMov2_articleID =  $lastIdDetalle['salesDetails_id'];
                                }

                                $proc_del_series_mov->delSeriesMov2_companieKey =  $venta->sales_company;
                                $proc_del_series_mov->delSeriesMov2_branchKey = $venta->sales_branchOffice;
                                $proc_del_series_mov->delSeriesMov2_module = 'Ventas';
                                $proc_del_series_mov->delSeriesMov2_saleID = $lastId;
                                $proc_del_series_mov->delSeriesMov2_article = $articuloClave[0];
                                $proc_del_series_mov->delSeriesMov2_lotSerie = $value;
                                $proc_del_series_mov->delSeriesMov2_quantity = 1;
                                $proc_del_series_mov->delSeriesMov2_cancelled = 0;

                                if ($venta->sales_movement == "Factura" || $venta->sales_movement == "Pedido") {
                                    $proc_del_series_mov->delSeriesMov2_affected = 1;
                                    $proc_del_series_mov->save();

                                    $lastIdSeries = isset($proc_del_series_mov->delSeriesMov2_id) ? $proc_del_series_mov->delSeriesMov2_id : PROC_DEL_SERIES_MOV2::latest('delSeriesMov2_id')->first();

                                    if ($venta->sales_origin == "Pedido") {
                                        //Buscamos el origen de la factura para buscar las series que fueron seleccionadas
                                        $ventaOrigen = PROC_SALES::WHERE('sales_movement', '=', 'Pedido')->WHERE('sales_movementID', '=', $venta->sales_originID)->WHERE('sales_company', '=',  session('company')->companies_key)->WHERE('sales_branchOffice', '=', session('sucursal')->branchOffices_key)->first();

                                        $proc_del_series_mov = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_companieKey', '=', $venta->sales_company)->where('delSeriesMov2_branchKey', '=', $venta->sales_branchOffice)->where('delSeriesMov2_saleID', "=", $ventaOrigen->sales_id)->WHERE('delSeriesMov2_article', '=', $articuloClave[0])->WHERE('delSeriesMov2_lotSerie', '=', $value)->first();

                                        if ($proc_del_series_mov !== null) {
                                            $proc_del_series_mov->delSeriesMov2_affected = 1;
                                            $proc_del_series_mov->update();

                                            $lastIdSeries = isset($proc_del_series_mov->delSeriesMov2_id) ? $proc_del_series_mov->delSeriesMov2_id : PROC_DEL_SERIES_MOV2::latest('delSeriesMov2_id')->first();
                                        }
                                    }
                                } else {
                                    $proc_del_series_mov->delSeriesMov2_affected = 0;
                                    $proc_del_series_mov->save();

                                    $lastIdSeries = isset($proc_del_series_mov->delSeriesMov2_id) ? $proc_del_series_mov->delSeriesMov2_id : PROC_DEL_SERIES_MOV2::latest('delSeriesMov2_id')->first();
                                }

                                //Obtenemos el ultimo id o el id actualizado de la serie
                                $series = [
                                    ...$series,
                                    $value,
                                ];

                                $idSeries = [
                                    ...$idSeries,
                                    $lastIdSeries
                                ];


                                $proc_lot_series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', $venta->sales_company)->where('lotSeries_branchKey', '=', $venta->sales_branchOffice)->where('lotSeries_article', "=", $articuloClave[0])->where('lotSeries_lotSerie', "=", $value)->first();

                                if ($proc_lot_series !== null) {
                                    if ($venta->sales_movement == "Factura" || $venta->sales_movement == "Pedido") {
                                        //este es el de series
                                        $proc_lot_series->lotSeries_delete = 1;
                                        $proc_lot_series->update();
                                    }
                                }
                            }

                            if (!array_key_exists($articuloFila, $asignacionSeriesB['series'])) {
                                $asignacionSeriesB['series'][$articuloFila] = $series;
                            } else {
                                array_push($asignacionSeriesB['series'][$articuloFila], $series);
                            }

                            if (!array_key_exists($articuloFila, $asignacionIdsSerieB['idSeries'])) {
                                $asignacionIdsSerieB['idSeries'][$articuloFila] = $idSeries;
                            } else {
                                array_push($asignacionIdsSerieB['idSeries'][$articuloFila], $idSeries);
                            }
                        }


                        if (isset($articulos[$articulo]['ventaKit']) && $articulos[$articulo]['ventaKit'] != null && $articulos[$articulo]['tipoArticulo'] == "Kit") {
                            $articuloFila = $articuloClave[0] . '-' . $keyItemArt;
                            //Series normales
                            $kitInformacion = [];
                            foreach ($articulos[$articulo]['ventaKit']['articulos'] as $articuloKit) {

                                if (isset($articuloKit['kitId']) && $articuloKit['kitId'] != '') {
                                    $detalleVentaKit = PROC_KIT_ARTICLES::where('procKit_id', '=', $articuloKit['kitId'])->where('procKit_saleID', '=', $lastId)->first();

                                    if ($detalleVentaKit == null) {
                                        $detalleVentaKit = new PROC_KIT_ARTICLES();
                                    }
                                } else {
                                    $detalleVentaKit = new PROC_KIT_ARTICLES();
                                }

                                $detalleVentaKit->procKit_article = $articuloClave[0];
                                $detalleVentaKit->procKit_articleID = $articuloKit['articuloId'];
                                $detalleVentaKit->procKit_saleID = $lastId;
                                $detalleVentaKit->procKit_articleDesp = $articuloKit['descripcion'];
                                $detalleVentaKit->procKit_cantidad = $articuloKit['cantidad'];
                                $detalleVentaKit->procKit_tipo = $articuloKit['tipo'];
                                $detalleVentaKit->procKit_observation = $articuloKit['observaciones'];
                                if ($venta->sales_movement == "Factura") {
                                    $detalleVentaKit->procKit_affected = 1;
                                } else {

                                    $detalleVentaKit->procKit_affected = 0;
                                }

                                if (isset($articuloKit['kitId']) && $articuloKit['kitId'] != '') {
                                } else {
                                    $detalleVentaKit->procKit_articleIDReference = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $lastId)
                                        ->where('salesDetails_article', '=', $articuloClave[0])
                                        ->where('salesDetails_observations', '=', $detalleCompra->salesDetails_observations)
                                        ->first()->salesDetails_id;
                                }


                                $detalleVentaKit->save();

                                //Obtenemos la informacion del kit
                                $lastIdKit = isset($detalleVentaKit->procKit_id) ? $detalleVentaKit->procKit_id : PROC_KIT_ARTICLES::latest('procKit_id')->SELECT('procKit_id')->first()->procKit_id;

                                $kitInformacion = [
                                    ...$kitInformacion,
                                    'articuloId' => $articuloKit['articuloId'],
                                    'cantidad' => $articuloKit['cantidad'],
                                    'descripcion' => $articuloKit['descripcion'],
                                    'componente' =>  $articuloKit['descripcion'],
                                    'disponible' =>  $articuloKit['disponible'],
                                    'tipo' =>  $articuloKit['tipo'],
                                    'articulo' => $articuloClave[0],
                                    'observaciones' => $articuloKit['observaciones'],
                                    'kitId' => $lastIdKit,
                                ];

                                if (isset($articulos[$articulo]['ventaKit']['ventaSeriesKits'])) {
                                    $seriesTrans42 = [];
                                    $seriesTransId42 = [];
                                    foreach ($articulos[$articulo]['ventaKit']['ventaSeriesKits'] as $key => $value) {
                                        if ($key == $articuloKit['articuloId']) {
                                            if (isset($value['serie']) && $value['serie'] != null) {

                                                foreach ($value['serie'] as $key => $serie) {

                                                    if (isset($value['ids']) && $value['ids'] != '' && $value['ids'] != null && $value['ids'] != 'undefined') {
                                                        $proc_del_series_mov = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_id', '=', $value['ids'][$key])->first();
                                                        $proc_del_series_mov->delSeriesMov2_lotSerie = $proc_del_series_mov->delSeriesMov2_lotSerie;
                                                        if ($venta->sales_movement == "Factura") {
                                                            $proc_del_series_mov->delSeriesMov2_affected = 1;
                                                        } else {
                                                            $proc_del_series_mov->delSeriesMov2_affected = 0;
                                                        }
                                                    } else {
                                                        $proc_del_series_mov = new PROC_DEL_SERIES_MOV2();
                                                        $proc_del_series_mov->delSeriesMov2_lotSerie = $serie;
                                                    }

                                                    $proc_del_series_mov->delSeriesMov2_articleID =  $articuloClave[0];
                                                    $proc_del_series_mov->delSeriesMov2_companieKey =  $venta->sales_company;
                                                    $proc_del_series_mov->delSeriesMov2_branchKey = $venta->sales_branchOffice;
                                                    $proc_del_series_mov->delSeriesMov2_module = 'Ventas';
                                                    $proc_del_series_mov->delSeriesMov2_saleID = $lastId;
                                                    $proc_del_series_mov->delSeriesMov2_article = $articuloKit['articuloId'];

                                                    $proc_del_series_mov->delSeriesMov2_quantity = 1;
                                                    $proc_del_series_mov->delSeriesMov2_cancelled = 0;
                                                    $proc_del_series_mov->delSeriesMov2_affected = 1;
                                                    $proc_del_series_mov->save();

                                                    if ($venta->sales_movement == "Factura") {
                                                        $proc_del_series_mov->delSeriesMov2_affected = 1;
                                                        $proc_del_series_mov->save();

                                                        if ($venta->sales_origin == "Pedido") {
                                                            //Buscamos el origen de la factura para buscar las series que fueron seleccionadas
                                                            $ventaOrigen = PROC_SALES::WHERE('sales_movement', '=', 'Pedido')->WHERE('sales_movementID', '=', $venta->sales_originID)->WHERE('sales_company', '=',  session('company')->companies_key)->WHERE('sales_branchOffice', '=', session('sucursal')->branchOffices_key)->first();

                                                            $proc_del_series_mov = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_companieKey', '=', $venta->sales_company)->where('delSeriesMov2_branchKey', '=', $venta->sales_branchOffice)->where('delSeriesMov2_saleID', "=", $ventaOrigen->sales_id)->WHERE('delSeriesMov2_article', '=', $articuloClave[0])->WHERE('delSeriesMov2_lotSerie', '=', $serie)->first();

                                                            if ($proc_del_series_mov !== null) {
                                                                $proc_del_series_mov->delSeriesMov2_affected = 1;
                                                                $proc_del_series_mov->update();
                                                            }
                                                        }
                                                    }

                                                    //Obtenemos la informacion del kit
                                                    $lastIdKitSeries = isset($proc_del_series_mov->delSeriesMov2_id) ? $proc_del_series_mov->delSeriesMov2_id : PROC_DEL_SERIES_MOV2::latest('delSeriesMov2_id')->SELECT('delSeriesMov2_id')->first()->delSeriesMov2_id;



                                                    $seriesTrans42 = [
                                                        ...$seriesTrans42,
                                                        $serie,
                                                    ];

                                                    $seriesTransId42 = [
                                                        ...$seriesTransId42,
                                                        $lastIdKitSeries
                                                    ];

                                                    $proc_lot_series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', $venta->sales_company)->where('lotSeries_branchKey', '=', $venta->sales_branchOffice)->where('lotSeries_article', "=", $articuloKit['articuloId'])->where('lotSeries_lotSerie', "=", $serie)->first();

                                                    if ($proc_lot_series !== null) {
                                                        if ($venta->sales_movement == "Factura") {
                                                            $proc_lot_series->lotSeries_delete = 1;
                                                            $proc_lot_series->update();
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                if (!array_key_exists($articuloFila, $asignacionKits['kits'])) {

                                    if (count($seriesTrans42) > 0) {
                                        $asignacionKits['kits'][$articuloFila] = [
                                            'articulos' => [$kitInformacion],
                                            'cantidad' => str_replace(['$', ','], '', $articulos[$articulo]['cantidad']),
                                            'ventaSeriesKits' => array(
                                                $articuloKit['articuloId'] =>  array(
                                                    "serie" => $seriesTrans42,
                                                    "ids" => $seriesTransId42
                                                )
                                            )
                                        ];
                                    } else {
                                        $asignacionKits['kits'][$articuloFila] = [
                                            'articulos' => [$kitInformacion],
                                            'cantidad' => str_replace(['$', ','], '', $articulos[$articulo]['cantidad']),
                                            'ventaSeriesKits' => []
                                        ];
                                    }
                                } else {
                                    $asignacionKits['kits'][$articuloFila]['articulos'] = [...$asignacionKits['kits'][$articuloFila]['articulos'], $kitInformacion];
                                    $asignacionKits['kits'][$articuloFila]['cantidad'] =  str_replace(['$', ','], '', $articulos[$articulo]['cantidad']);

                                    if (isset($asignacionKits['kits'][$articuloFila]['ventaSeriesKits'][$articuloKit['articuloId']]) && count($seriesTrans42) > 0) {
                                        $asignacionKits['kits'][$articuloFila]['ventaSeriesKits'][$articuloKit['articuloId']]['serie'] = [
                                            ...$asignacionKits['kits'][$articuloFila]['ventaSeriesKits'][$articuloKit['articuloId']]['serie'],
                                            $seriesTrans42
                                        ];

                                        $asignacionKits['kits'][$articuloFila]['ventaSeriesKits'][$articuloKit['articuloId']]['ids'] = [
                                            ...$asignacionKits['kits'][$articuloFila]['ventaSeriesKits'][$articuloKit['articuloId']]['ids'],
                                            $seriesTransId42
                                        ];
                                    } else {
                                        $keysVentasSeries = array_keys($asignacionKits['kits'][$articuloFila]['ventaSeriesKits']);
                                        $keysC = [];
                                        if (count($keysVentasSeries) == 0 && count($seriesTrans42) > 0) {
                                            $asignacionKits['kits'][$articuloFila]['ventaSeriesKits'] = array(
                                                $articuloKit['articuloId'] =>  array(
                                                    "serie" => $seriesTrans42,
                                                    "ids" => $seriesTransId42
                                                )
                                            );
                                        }

                                        foreach ($keysVentasSeries as $key => $keyComponente) {
                                            if (count($asignacionKits['kits'][$articuloFila]['ventaSeriesKits'][$keyComponente]['serie']) > 0) {
                                                $keysC[$keyComponente] = $asignacionKits['kits'][$articuloFila]['ventaSeriesKits'][$keyComponente];
                                            }
                                        }

                                        if (isset($keysC) && count($seriesTrans42) > 0) {
                                            $keysC[$articuloKit['articuloId']] = array(
                                                "serie" => $seriesTrans42,
                                                "ids" => $seriesTransId42
                                            );
                                            $asignacionKits['kits'][$articuloFila]['ventaSeriesKits'] = $keysC;
                                        }
                                    }
                                }
                            }
                        }

                        if ($movimientoIn == 'Pedido') {

                            if (isset($articulos[$articulo]['listaEmpaques']) && $articulos[$articulo]['listaEmpaques'] != null) {

                                foreach ($articulos[$articulo]['listaEmpaques']['empaques'] as $articulo2) {

                                    if (isset($articulo2['idEmpaque']) && $articulo2['idEmpaque'] != '') {
                                        $listaEmpaque = PROC_PACKINGLIST::where('packingList_id', '=', $articulo2['idEmpaque'])->first();
                                    } else {
                                        $listaEmpaque = new PROC_PACKINGLIST();
                                    }

                                    $listaEmpaque->packingList_saleID = $lastId;
                                    $listaEmpaque->packingList_article = $articuloClave[0];
                                    $listaEmpaque->packingList_companieKey = session('company')->companies_key;
                                    $listaEmpaque->packingList_branchKey = session('sucursal')->branchOffices_key;
                                    $listaEmpaque->packingList_module = 'Ventas';
                                    $listaEmpaque->packingList_unidPack = $articulo2['unidad'];
                                    $listaEmpaque->packingList_quantity = $articulos[$articulo]['listaEmpaques']['cantidad'];
                                    $listaEmpaque->packingList_weight = $articulo2['peso'];
                                    $listaEmpaque->packingList_weightUnid = $articulo2['pesoUnidad'];
                                    $listaEmpaque->packingList_weightNet = $articulo2['pesoNeto'];
                                    $listaEmpaque->packingList_weightNet = $articulo2['pesoNeto'];
                                    $listaEmpaque->packingList_advance = $articulo2['porcentaje'];

                                    if (isset($articulo2['idEmpaque']) && $articulo2['idEmpaque'] != '') {
                                    } else {
                                        $listaEmpaque->packingList_articleID = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $lastId)->where('salesDetails_article', '=', $articuloClave[0])->first()->salesDetails_id;
                                    }

                                    $listaEmpaque->save();
                                }
                            }
                        }
                    }
                }

                $progreso = 0;
                if ($movimientoIn == 'Pedido') {
                    $pedido = PROC_SALES::where('sales_id', '=', $lastId)->first();
                    $articulosPedido = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $pedido->sales_id)->get();
                    foreach ($articulosPedido as $articuloPedido) {
                        $progreso += $articuloPedido->salesDetails_advanced;
                    }
                    $pedido->sales_advanced = $progreso / count($articulosPedido);

                    $pedido->save();
                }


                //Validamos que el movimiento tenga informacion en el input inputJsonComercioExterior
                if ($movimientoIn === "Factura") {
                    $infComercioExterior = json_decode($request['inputJsonComercioExterior']);
                    if ($infComercioExterior !== null) {
                        $comercioExterior = PROC_SALES_FOREIGN_TRADE::WHERE('salesForeingTrade_saleID', '=', $folioAfectar->sales_id)->first();

                        if ($comercioExterior === null) {
                            $comercioExterior = new PROC_SALES_FOREIGN_TRADE();
                        }

                        $comercioExterior->salesForeingTrade_saleID = $folioAfectar->sales_id;
                        $comercioExterior->salesForeingTrade_transferReason = $infComercioExterior->mTraslado;
                        $comercioExterior->salesForeingTrade_operationType = $infComercioExterior->tOperacion;
                        $comercioExterior->salesForeingTrade_petitionKey = $infComercioExterior->cPedimento;
                        $comercioExterior->salesForeingTrade_incoterm = $infComercioExterior->IncotermKey;
                        $comercioExterior->salesForeingTrade_subdivision = $infComercioExterior->subdivision;
                        $comercioExterior->salesForeingTrade_certificateOforigin = $infComercioExterior->origen;
                        $comercioExterior->salesForeingTrade_numberCertificateOrigin = $infComercioExterior->cOrigen;
                        $comercioExterior->salesForeingTrade_trustedExportedNumber = $infComercioExterior->eConfiable;
                        $comercioExterior->save();
                    }
                }


                $contenedorKits = [];
                $contenedorSeries = [];
                if (count($asignacionSeriesB['series']) > 0) {
                    $contenedorSeries = array_merge($asignacionSeriesB, $asignacionIdsSerieB);
                }

                if (count($asignacionKits['kits']) > 0) {
                    $contenedorKits = $asignacionKits;
                }

                if ($lastId != 0 && (isset($contenedorSeries) || isset($contenedorKits))) {
                    $contenedorFinal = array_merge($contenedorKits, $contenedorSeries);
                    $jsonDetalle = json_encode($contenedorFinal);
                    $venta = PROC_SALES::WHERE('sales_id', '=', $folioAfectar->sales_id)->first();
                    $venta->sales_jsonData = $jsonDetalle;
                    $venta->save();
                }


                // //agregar CxC
                $this->agregarCxC($folioAfectar->sales_id);
                // //agregar CxC
                $this->agregarCxCP($folioAfectar->sales_id);
                // //agregar a almacen
                $this->quitarAlmacen($folioAfectar->sales_id);
                //Agremos el cobro factura cuando el movimiento este concluido
                if ($movimientoIn == 'Factura' && $folioAfectar->sales_typeCondition == 'Contado') {
                    $this->agregarCobro($folioAfectar->sales_id, $request);
                }
                // //agregamos a auxiliar
                $this->auxiliar($folioAfectar->sales_id);
                // agregamos a aux unidades 
                $this->auxiliarU($folioAfectar->sales_id);
                // //agregamos saldo a tabla
                $this->agregarSaldo($folioAfectar->sales_id);
                // //agregamos costo promedio
                // $this->costoPromedio($folioAfectar->sales_id);
                // //agregamos movimientos
                $this->agregarMov($folioAfectar->sales_id);
                //concluir origines
                $this->concluirOrigines($folioAfectar->sales_id);
                //agregar a tesoreria
                $this->agregarTesoreria($folioAfectar->sales_id);
                //agregamos costo promedio
                $this->costoPromedio($folioAfectar->sales_id);

                // dd($movimientoIn);
                //ahora comprobamos que si es Rechazo de Venta que las series regresen a su estado original
                if ($movimientoIn === 'Rechazo de Venta') {

                    $articulos = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $folioAfectar->sales_id)->where('salesDetails_branchOffice', '=', $folioAfectar->sales_branchOffice)->get();

                    foreach ($articulos as $articulo) {
                        if ($articulo->salesDetails_type == 'Serie') {
                            $series = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_saleID', '=', $articulo->salesDetails_saleID)->where('delSeriesMov2_articleID', '=', $articulo->salesDetails_id)->get();

                            $seriesOrigen = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_saleID', '=', $folioAfectar->sales_id)->where('delSeriesMov2_article', '=', $articulo->salesDetails_article)->get();

                            foreach ($seriesOrigen  as $seriesO) {
                                $seriesO->delSeriesMov2_affected = 0;
                                $seriesO->update();
                            }

                            if (
                                $series != null
                            ) {
                                foreach ($series as $serie) {
                                    $serie->delSeriesMov2_cancelled = 1;
                                    $serie->update();

                                    $proc_lot_series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', $folioAfectar->sales_company)->where('lotSeries_branchKey', '=', $folioAfectar->sales_branchOffice)->where('lotSeries_lotSerie', "=", $serie->delSeriesMov2_lotSerie)->first();


                                    if ($proc_lot_series !== null) {
                                        $proc_lot_series->lotSeries_delete = 0;
                                        $proc_lot_series->update();
                                    }
                                    // $serie->delete();
                                }
                            }
                        }

                        //ahora lo ahcemos cuando el tipo sea Kit
                        if ($articulo['salesDetails_type'] == "Kit") {
                            $articulosKit = PROC_KIT_ARTICLES::where('procKit_article', $articulo->salesDetails_article)->where('procKit_saleID', $folioAfectar->sales_id)->get();
                            foreach ($articulosKit as $articuloKit) {

                                // $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articuloKit->procKit_articleID)->where('articlesInv_depot', '=', $folioAfectar->sales_depot)->first();

                                // if ($inventario != null) {
                                //     $inventario->articlesInv_inventory = $inventario->articlesInv_inventory + $articuloKit->procKit_cantidad;
                                //     $inventario->update();
                                // }

                                if ($articuloKit->procKit_tipo == 'Serie') {
                                    $series = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_saleID', '=', $articuloKit->procKit_saleID)->where('delSeriesMov2_article', '=', $articuloKit->procKit_articleID)->where('delSeriesMov2_articleID', '=', $articuloKit->procKit_article)->get();
                                    if (count($series) > 0) {
                                        foreach ($series as $serie) {
                                            $serie->delSeriesMov2_cancelled = 1;
                                            $serie->update();
                                            // $serie->delete();

                                            $proc_lot_series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', $folioAfectar->sales_company)->where('lotSeries_branchKey', '=', $folioAfectar->sales_branchOffice)->where('lotSeries_lotSerie', "=", $serie->delSeriesMov2_lotSerie)->first();


                                            if ($proc_lot_series !== null) {
                                                $proc_lot_series->lotSeries_delete = 0;
                                                $proc_lot_series->update();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }


                if ($isCreate) {
                    $status = 200;
                    //si es factura o cotización será la, sino sera el
                    if ($movimientoIn == 'Factura' || $movimientoIn == 'Cotización') {
                        $message = 'Se ha creado la ' . $movimientoIn . ' correctamente';
                    } else {
                        $message = 'Se ha creado el ' . $movimientoIn . ' correctamente';
                    }
                    if ($folioAfectar->sales_movement == 'Factura') {

                        if (session('company')->companies_stamped === '1' || session('company')->companies_stamped === 1) {
                            if (session('company')->companies_calculateTaxes === '0' || session('company')->companies_calculateTaxes === 0) {
                                $cfdi = new TimbradoController();
                                $cfdi->timbrarFactura($folioAfectar->sales_id, $request);

                                if ($cfdi->getStatus()) {
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


                                if (!$cfdi->getStatus()) {
                                    $status = 404;
                                    $message = 'Afectación de venta realizada correctamente, pero no se pudo timbrar la factura';
                                }

                                if (!$cfdi->getStatus2()) {
                                    $status = 400;
                                    $message = 'Conexión con el servidor de timbrado no disponible, favor de intentar más tarde';
                                }
                            }
                        }
                    }
                } else {
                    $status = 500;
                    $message = 'Error al afectar la venta';
                }
            } catch (\Throwable $th) {
                $status = 500;
                $message = $th->getMessage() . ' ' . $th->getLine();
                $lastId = 0;
            }

            return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
        }
    }

    public function agregarCxC($folio)
    {
        $folioAfectar = PROC_SALES::where('sales_id', '=', $folio)->first();
        if ($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Factura' && $folioAfectar->sales_typeCondition == 'Crédito') {
            // echo 'entro';
            //agregamos una nueva cuenta por pagar para la compra
            $cuentaCobrar = new PROC_ACCOUNTS_RECEIVABLE();
            $cuentaCobrar->accountsReceivable_movement = $folioAfectar->sales_movement;
            $cuentaCobrar->accountsReceivable_movementID = $folioAfectar->sales_movementID;
            $cuentaCobrar->accountsReceivable_issuedate = Carbon::parse($folioAfectar->sales_issuedate)->format('Y-m-d');
            $cuentaCobrar->accountsReceivable_money = $folioAfectar->sales_money;
            $cuentaCobrar->accountsReceivable_typeChange = $folioAfectar->sales_typeChange;
            $cuentaCobrar->accountsReceivable_customer = $folioAfectar->sales_customer;
            $cuentaCobrar->accountsReceivable_condition = $folioAfectar->sales_condition;
            $vencimiento = Carbon::parse($folioAfectar->sales_expiration)->format('Y-m-d');
            $vencimiento2 = Carbon::parse($vencimiento)->format('Y-m-d');
            $cuentaCobrar->accountsReceivable_expiration = $vencimiento2;

            $emision = Carbon::parse($folioAfectar->sales_issueDate)->format('Y-m-d');
            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
            $vencimiento = Carbon::parse($folioAfectar->sales_expiration)->format('Y-m-d');
            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);
            $diasMoratorio = $shippingDate->diffInDays($currentDate);
            $cuentaCobrar->accountsReceivable_moratoriumDays = '-' . $diasMoratorio;


            $cuentaCobrar->accountsReceivable_amount = $folioAfectar->sales_amount;
            $cuentaCobrar->accountsReceivable_taxes = $folioAfectar->sales_taxes;
            $cuentaCobrar->accountsReceivable_total = $folioAfectar->sales_total;
            $cuentaCobrar->accountsReceivable_retention1 = $folioAfectar->sales_retention1;
            $cuentaCobrar->accountsReceivable_retentionISR = $folioAfectar->sales_retentionISR;
            $cuentaCobrar->accountsReceivable_retention2 = $folioAfectar->sales_retention2;
            $cuentaCobrar->accountsReceivable_retentionIVA = $folioAfectar->sales_retentionIVA;
            $cuentaCobrar->accountsReceivable_concept = $folioAfectar->sales_concept;
            $cuentaCobrar->accountsReceivable_reference = $folioAfectar->sales_reference;
            $cuentaCobrar->accountsReceivable_balance = $folioAfectar->sales_total;
            $cuentaCobrar->accountsReceivable_company = $folioAfectar->sales_company;
            $cuentaCobrar->accountsReceivable_branchOffice = $folioAfectar->sales_branchOffice;
            $cuentaCobrar->accountsReceivable_user = $folioAfectar->sales_user;
            $cuentaCobrar->accountsReceivable_status = $this->estatus[1];
            $cuentaCobrar->accountsReceivable_origin = $folioAfectar->sales_movement;
            $cuentaCobrar->accountsReceivable_originID = $folioAfectar->sales_movementID;
            $cuentaCobrar->accountsReceivable_originType = 'Ventas';
            $cuentaCobrar->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $cuentaCobrar->updated_at = Carbon::now()->format('Y-m-d H:i:s');

            $create = $cuentaCobrar->save();
        }
    }

    public function agregarCxCP($folio)
    {
        $folioAfectar = PROC_SALES::where('sales_id', '=', $folio)->first();
        if ($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Factura' && $folioAfectar->sales_typeCondition == 'Crédito') {
            //agregamos una nueva cuenta por pagar para la compra

            $cuentaCobrar = new PROC_ACCOUNTS_RECEIVABLE_P();
            $cuentaCobrar->accountsReceivableP_movement = $folioAfectar->sales_movement;
            $cuentaCobrar->accountsReceivableP_movementID = $folioAfectar->sales_movementID;
            $cuentaCobrar->accountsReceivableP_issuedate = Carbon::parse($folioAfectar->sales_issuedate)->format('Y-m-d');
            $cuentaCobrar->accountsReceivableP_expiration =  Carbon::parse($folioAfectar->sales_expiration)->format('Y-m-d');

            //dias credito y moratorio
            $emision = Carbon::parse($folioAfectar->sales_issueDate)->format('Y-m-d');
            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
            $vencimiento = Carbon::parse($folioAfectar->sales_expiration)->format('Y-m-d');
            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

            $diasCredito = $currentDate->diffInDays($shippingDate);
            $diasMoratorio = $shippingDate->diffInDays($currentDate);


            $cuentaCobrar->accountsReceivableP_creditDays = $diasCredito;
            $cuentaCobrar->accountsReceivableP_moratoriumDays = '-' . $diasMoratorio;


            $cuentaCobrar->accountsReceivableP_money = $folioAfectar->sales_money;
            $cuentaCobrar->accountsReceivableP_typeChange = $folioAfectar->sales_typeChange;
            $cuentaCobrar->accountsReceivableP_customer = $folioAfectar->sales_customer;
            $cuentaCobrar->accountsReceivableP_condition = $folioAfectar->sales_condition;

            $cuentaCobrar->accountsReceivableP_amount = $folioAfectar->sales_amount;
            $cuentaCobrar->accountsReceivableP_taxes = $folioAfectar->sales_taxes;
            $cuentaCobrar->accountsReceivableP_total = $folioAfectar->sales_total;
            $cuentaCobrar->accountsReceivableP_balanceTotal = $folioAfectar->sales_total;
            $cuentaCobrar->accountsReceivableP_concept = $folioAfectar->sales_concept;
            $cuentaCobrar->accountsReceivableP_reference = $folioAfectar->sales_reference;
            $cuentaCobrar->accountsReceivableP_balance = $folioAfectar->sales_total;
            $cuentaCobrar->accountsReceivableP_company = $folioAfectar->sales_company;
            $cuentaCobrar->accountsReceivableP_branchOffice = $folioAfectar->sales_branchOffice;
            $cuentaCobrar->accountsReceivableP_user = $folioAfectar->sales_user;
            $cuentaCobrar->accountsReceivableP_status = $this->estatus[1];
            $cuentaCobrar->accountsReceivableP_origin = $folioAfectar->sales_movement;
            $cuentaCobrar->accountsReceivableP_originID = $folioAfectar->sales_movementID;
            $cuentaCobrar->accountsReceivableP_originType =  'Ventas';


            $create = $cuentaCobrar->save();

            try {
                if ($create) {
                    $message = 'Se agregó la cuenta por pagar correctamente';
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


    public function agregarSaldo($folio)
    {
        $folioAfectar = PROC_SALES::where('sales_id', '=', $folio)->first();

        if ($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Factura' && $folioAfectar->sales_typeCondition == 'Crédito') {
            // echo 'new';
            // dd($folioAfectar);
            //agregamos el saldo del proveedor en la tabla de saldos
            $saldo = PROC_BALANCE::where('balance_account', '=', $folioAfectar->sales_customer)->where('balance_branchKey', '=', $folioAfectar->sales_branchOffice)->where('balance_branch', '=', 'CxC')->where('balance_companieKey', '=', $folioAfectar->sales_company)->where('balance_money', '=', $folioAfectar->sales_money)->first();

            if ($saldo == null) {
                $saldo = new PROC_BALANCE();
                $saldo->balance_companieKey = $folioAfectar->sales_company;
                $saldo->balance_branchKey = $folioAfectar->sales_branchOffice;
                $saldo->balance_branch = 'CxC';
                $saldo->balance_money = $folioAfectar->sales_money;
                $saldo->balance_account = $folioAfectar->sales_customer;
                $saldo->balance_balance = $folioAfectar->sales_total;
                $saldo->balance_reconcile = $saldo->balance_balance;
                $saldo->save();
            } else {
                $saldo->balance_balance = $saldo->balance_balance + $folioAfectar->sales_total;
                $saldo->balance_reconcile = $saldo->balance_balance;
                $saldo->update();
            }
        }

        //no se usa porque se hace la inserccion hasta cxc
        if ($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Factura' && $folioAfectar->sales_typeCondition == 'Contado') {

            $cambios = PROC_SALES_PAYMENT::where('salesPayment_saleID', '=', $folioAfectar->sales_id)->get();

            foreach ($cambios as $camb) {

                if ($camb->salesPayment_moneyAccountType != "Banco") {
                    $saldo = PROC_BALANCE::where('balance_account', '=', $camb->salesPayment_moneyAccount)->where('balance_branchKey', '=', $camb->salesPayment_branchOffice)->where('balance_companieKey', '=', $folioAfectar->sales_company)->where('balance_money', '=', $folioAfectar->sales_money)->first();

                    $saldo2 = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', $camb->salesPayment_moneyAccount)->where('moneyAccountsBalance_status', 'Alta')->where('moneyAccountsBalance_money', $folioAfectar->sales_money)->where('moneyAccountsBalance_company', $folioAfectar->sales_company)->first();
                    if ($saldo == null) {
                        $saldo = new PROC_BALANCE();
                        $saldo->balance_companieKey = $folioAfectar->sales_company;
                        $saldo->balance_branchKey = $folioAfectar->sales_branchOffice;
                        $saldo->balance_branch = 'Din';
                        $saldo->balance_money = $folioAfectar->sales_money;
                        $saldo->balance_account = $camb->salesPayment_moneyAccount;
                        $saldo->balance_balance = $camb->salesPayment_fullCharge;
                        $saldo->balance_reconcile = $saldo->balance_balance;
                        $saldo->save();
                    } else {
                        $saldo->balance_balance = $saldo->balance_balance + $camb->salesPayment_fullCharge;
                        $saldo->balance_reconcile = $saldo->balance_balance;
                        $saldo->update();
                    }

                    if ($saldo2 !== null) {
                        $saldo2->moneyAccountsBalance_balance = $saldo2->moneyAccountsBalance_balance + $camb->salesPayment_fullCharge;
                        $saldo2->update();
                    }
                }
            }
        }
    }

    public function quitarAlmacen($folio)
    {
        $folioAfectar = PROC_SALES::where('sales_id', '=', $folio)->first();

        if (($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Factura' && $folioAfectar->sales_typeCondition == 'Crédito') || ($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Factura' && $folioAfectar->sales_typeCondition == 'Contado')) {


            $articulos = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $folioAfectar->sales_id)->get();
            foreach ($articulos as $articulo) {
                if ($articulo['salesDetails_type'] !== "Servicio" && $articulo['salesDetails_type'] !== "Kit") {

                    $cantidad = $articulo->salesDetails_inventoryAmount;
                    $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->salesDetails_article)->where('articlesInv_depot', '=', $folioAfectar->sales_depot)->first();


                    if ($inventario != null) {
                        $inventario->articlesInv_inventory = $inventario->articlesInv_inventory - $cantidad;
                        $inventario->update();
                    }


                    if ($inventario == null && session('generalParameters')->generalParameters_billsNot == 1) {
                        $inventario2 = new PROC_ARTICLES_INV();
                        $inventario2->articlesInv_depot = $folioAfectar->sales_depot;
                        $inventario2->articlesInv_branchKey = $folioAfectar->sales_branchOffice;
                        $inventario2->articlesInv_companieKey = $folioAfectar->sales_company;
                        $inventario2->articlesInv_inventory = ($inventario2->articlesInv_inventory - $cantidad);
                        $inventario2->articlesInv_article = $articulo->salesDetails_article;
                        $inventario2->save();
                    }
                }

                if ($articulo['salesDetails_type'] == "Kit") {
                    $articulosKit = PROC_KIT_ARTICLES::where('procKit_article', $articulo->salesDetails_article)->where('procKit_saleID', $folioAfectar->sales_id)->get();
                    foreach ($articulosKit as $articuloKit) {

                        $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articuloKit->procKit_articleID)->where('articlesInv_depot', '=', $folioAfectar->sales_depot)->first();

                        if ($inventario != null) {
                            $inventario->articlesInv_inventory = $inventario->articlesInv_inventory - $articuloKit->procKit_cantidad;
                            $inventario->update();
                        }

                        if ($inventario == null && session('generalParameters')->generalParameters_billsNot == 1) {
                            $inventario2 = new PROC_ARTICLES_INV();
                            $inventario2->articlesInv_depot = $folioAfectar->sales_depot;
                            $inventario2->articlesInv_branchKey = $folioAfectar->sales_branchOffice;
                            $inventario2->articlesInv_companieKey = $folioAfectar->sales_company;
                            $inventario2->articlesInv_inventory = ($inventario2->articlesInv_inventory - $$articuloKit->procKit_cantidad);
                            $inventario2->articlesInv_article = $articuloKit->procKit_articleID;
                            $inventario2->save();
                        }
                    }
                }
            }
        }
    }

    public function auxiliar($folio)
    {
        $folioAfectar = PROC_SALES::where('sales_id', '=', $folio)->first();

        if ($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Factura' && $folioAfectar->sales_typeCondition == 'Crédito') {

            // echo 'new';
            // dd($folioAfectar);
            //agregar datos a aux
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->sales_company;
            $auxiliar->assistant_branchKey = $folioAfectar->sales_branchOffice;
            $auxiliar->assistant_branch = 'CxC';
            $auxiliar->assistant_movement = $folioAfectar->sales_movement;
            $auxiliar->assistant_movementID = $folioAfectar->sales_movementID;
            $auxiliar->assistant_module = 'CxC';

            //buscamos el modulo de cxp
            $cxc = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movementID', '=', $folioAfectar->sales_movementID)->where('accountsReceivable_branchOffice', '=', $folioAfectar->sales_branchOffice)->first();

            $auxiliar->assistant_moduleID = $cxc->accountsReceivable_id;
            $auxiliar->assistant_money = $folioAfectar->sales_money;
            $auxiliar->assistant_typeChange = $folioAfectar->sales_typeChange;
            $auxiliar->assistant_account = $folioAfectar->sales_customer;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = $folioAfectar->sales_total;
            $auxiliar->assistant_payment = null;
            $auxiliar->assistant_apply = $folioAfectar->sales_movement;
            $auxiliar->assistant_applyID = $folioAfectar->sales_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->sales_reference;


            $auxiliar->save();
        }
    }

    public function auxiliarU($folio)
    {
        $folioAfectar = PROC_SALES::where('sales_id', '=', $folio)->first();

        if (($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Factura' && $folioAfectar->sales_typeCondition == 'Crédito') || ($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Factura' && $folioAfectar->sales_typeCondition == 'Contado')) {
            //buscamos sus articulos
            $articulos = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $folioAfectar->sales_id)->where('salesDetails_branchOffice', '=', $folioAfectar->sales_branchOffice)->get();

            foreach ($articulos as $articulo) {

                if ($articulo->salesDetails_type != 'Kit' && $articulo->salesDetails_type != 'Servicio') {
                    $costo = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articulo->salesDetails_article)->where('articlesCost_branchKey', '=', $articulo->salesDetails_branchOffice)->first();

                    // dd($costo);
                    if (session('generalParameters')->generalParameters_billsNot == 0) {
                        $costo = $costo->articlesCost_lastCost === null  || '.0000' ? $costo->articlesCost_averageCost : $costo->articlesCost_lastCost;
                        $importe = $articulo->salesDetails_inventoryAmount * $costo;
                    } else {
                        if ($folioAfectar->sales_money == session('generalParameters')->generalParameters_defaultMoney) {
                            $costo = ($articulo->salesDetails_unitCost / $articulo->salesDetails_inventoryAmount) * $articulo->salesDetails_quantity;
                            $importe = $articulo->salesDetails_inventoryAmount * $costo;
                        } else {
                            $costo = (($articulo->salesDetails_unitCost / $articulo->salesDetails_inventoryAmount) * $articulo->salesDetails_quantity) * $folioAfectar->sales_typeChange;
                            $importe = $articulo->salesDetails_inventoryAmount * $costo;
                        }
                    }

                    //agregar datos a aux
                    $auxiliarU = new PROC_ASSISTANT_UNITS();
                    $auxiliarU->assistantUnit_companieKey = $folioAfectar->sales_company;
                    $auxiliarU->assistantUnit_branchKey = $folioAfectar->sales_branchOffice;
                    $auxiliarU->assistantUnit_branch = 'Inv';
                    $auxiliarU->assistantUnit_movement = $folioAfectar->sales_movement;
                    $auxiliarU->assistantUnit_movementID = $folioAfectar->sales_movementID;
                    $auxiliarU->assistantUnit_module = 'Ventas';
                    $auxiliarU->assistantUnit_moduleID = $articulo->salesDetails_saleID;
                    if ($folioAfectar->sales_money != session('generalParameters')->generalParameters_defaultMoney) {
                        $moneda = CONF_MONEY::where('money_key', '=', session('generalParameters')->generalParameters_defaultMoney)->first();
                        $auxiliarU->assistantUnit_money = $moneda->money_key;
                        $auxiliarU->assistantUnit_typeChange = $moneda->money_change;
                    } else {
                        $auxiliarU->assistantUnit_money = $folioAfectar->sales_money;
                        $auxiliarU->assistantUnit_typeChange = $folioAfectar->sales_typeChange;
                    }


                    $auxiliarU->assistantUnit_group = $articulo->salesDetails_depot;
                    $auxiliarU->assistantUnit_account = $articulo->salesDetails_article;
                    //ponemos fecha del ejercicio
                    $year = Carbon::now()->year;
                    //sacamos el periodo 
                    $period = Carbon::now()->month;
                    $auxiliarU->assistantUnit_year = $year;
                    $auxiliarU->assistantUnit_period = $period;
                    $auxiliarU->assistantUnit_charge = null;


                    $abonoUnidad = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articulo->salesDetails_article)->where('articlesCost_branchKey', '=', $articulo->salesDetails_branchOffice)->where('articlesCost_companieKey', '=', $folioAfectar->sales_company)->where('articlesCost_depotKey', '=', $folioAfectar->sales_depot)->first();


                    if ($folioAfectar->sales_money == session('generalParameters')->generalParameters_defaultMoney) {
                        $auxiliarU->assistantUnit_payment =  $abonoUnidad->articlesCost_averageCost != null ? ($abonoUnidad->articlesCost_averageCost * (float)$articulo->salesDetails_inventoryAmount) : '0.00';
                    } else {
                        $auxiliarU->assistantUnit_payment = $abonoUnidad->articlesCost_averageCost != null ? (($abonoUnidad->articlesCost_averageCost * (float)$articulo->salesDetails_inventoryAmount) * $folioAfectar->sales_typeChange) : '0.00';
                    }


                    $auxiliarU->assistantUnit_chargeUnit = null;
                    $auxiliarU->assistantUnit_paymentUnit = (float)$articulo->salesDetails_inventoryAmount;
                    $auxiliarU->assistantUnit_apply = $articulo->salesDetails_apply;
                    $auxiliarU->assistantUnit_applyID =  $articulo->salesDetails_applyIncrement;
                    $auxiliarU->assistantUnit_canceled = 0;
                    $auxiliarU->asssistantUnit_costumer = $folioAfectar->sales_customer;
                    $auxiliarU->save();
                }



                if ($articulo->salesDetails_type == 'Kit') {

                    $articuloDefault = CAT_ARTICLES::where('articles_key', '=', $articulo->salesDetails_article)->first();

                    $articulosKit = CAT_KIT_ARTICLES::where('kitArticles_articleID', '=', $articuloDefault->articles_id)->get();

                    foreach ($articulosKit as $articuloKit) {
                        $articuloKitDefault = CAT_ARTICLES::where('articles_key', '=', $articuloKit->kitArticles_article)->first();

                        if ($articuloKitDefault != null && $articuloKitDefault->articles_type != "Servicio") {
                            $costoPromedioKit = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articuloKitDefault->articles_key)->where('articlesCost_branchKey', '=', $folioAfectar->sales_branchOffice)->where('articlesCost_companieKey', '=', $folioAfectar->sales_company)->where('articlesCost_depotKey', '=', $folioAfectar->sales_depot)->first();

                            $auxiliarU = new PROC_ASSISTANT_UNITS();
                            $auxiliarU->assistantUnit_companieKey = $folioAfectar->sales_company;
                            $auxiliarU->assistantUnit_branchKey = $folioAfectar->sales_branchOffice;
                            $auxiliarU->assistantUnit_branch = 'Inv';
                            $auxiliarU->assistantUnit_movement = $folioAfectar->sales_movement;
                            $auxiliarU->assistantUnit_movementID = $folioAfectar->sales_movementID;
                            $auxiliarU->assistantUnit_module = 'Ventas';
                            $auxiliarU->assistantUnit_moduleID = $articulo->salesDetails_saleID;
                            if ($folioAfectar->sales_money != session('generalParameters')->generalParameters_defaultMoney) {
                                $moneda = CONF_MONEY::where('money_key', '=', session('generalParameters')->generalParameters_defaultMoney)->first();
                                $auxiliarU->assistantUnit_money = $moneda->money_key;
                                $auxiliarU->assistantUnit_typeChange = $moneda->money_change;
                            } else {
                                $auxiliarU->assistantUnit_money = $folioAfectar->sales_money;
                                $auxiliarU->assistantUnit_typeChange = $folioAfectar->sales_typeChange;
                            }


                            $auxiliarU->assistantUnit_group = $articulo->salesDetails_depot;
                            $auxiliarU->assistantUnit_account = $articuloKitDefault->articles_key;
                            //ponemos fecha del ejercicio
                            $year = Carbon::now()->year;
                            //sacamos el periodo 
                            $period = Carbon::now()->month;
                            $auxiliarU->assistantUnit_year = $year;
                            $auxiliarU->assistantUnit_period = $period;
                            $auxiliarU->assistantUnit_charge = null;


                            if ($folioAfectar->sales_money == session('generalParameters')->generalParameters_defaultMoney) {
                                $auxiliarU->assistantUnit_payment =  $costoPromedioKit->articlesCost_averageCost != null ? ($costoPromedioKit->articlesCost_averageCost * (float)$articuloKit->kitArticles_cantidad) : '0.00';
                            } else {
                                $auxiliarU->assistantUnit_payment = $costoPromedioKit->articlesCost_averageCost != null ? (($costoPromedioKit->articlesCost_averageCost * (float)$articuloKit->kitArticles_cantidad) * $folioAfectar->sales_typeChange) : '0.00';
                            }


                            $auxiliarU->assistantUnit_chargeUnit = null;
                            $auxiliarU->assistantUnit_paymentUnit = (float)$articuloKit->kitArticles_cantidad * (float) $articulo->salesDetails_inventoryAmount;
                            $auxiliarU->assistantUnit_apply = $articulo->salesDetails_apply;
                            $auxiliarU->assistantUnit_applyID =  $articulo->salesDetails_applyIncrement;
                            $auxiliarU->assistantUnit_canceled = 0;
                            $auxiliarU->asssistantUnit_costumer = $folioAfectar->sales_customer;
                            $auxiliarU->save();
                        }
                    }
                }
            }
        }
    }

    public function costoPromedio($folio)
    {
        $folioAfectar = PROC_SALES::where('sales_id', '=', $folio)->first();


        $articulos = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $folioAfectar->sales_id)->get();

        foreach ($articulos as $articulo) {
            //agregamos costo promedio                // if ($articulo->purchaseDetails_quantity > 0) {
            if ($articulo->salesDetails_type != 'Kit' && $articulo->salesDetails_type != 'Servicio') {

                $costoPromedio = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articulo->salesDetails_article)->where('articlesCost_branchKey', '=', $folioAfectar->sales_branchOffice)->where('articlesCost_companieKey', '=', $folioAfectar->sales_company)->where('articlesCost_depotKey', '=', $folioAfectar->sales_depot)->first();


                if ($costoPromedio != null) {
                    $articulo->salesDetails_saleCost =  $costoPromedio->articlesCost_averageCost * $articulo->salesDetails_quantity;
                    $articulo->update();
                }
            }




            if ($articulo->salesDetails_type == 'Kit') {

                $articuloDefault = CAT_ARTICLES::where('articles_key', '=', $articulo->salesDetails_article)->first();

                $articulosKit = CAT_KIT_ARTICLES::where('kitArticles_articleID', '=', $articuloDefault->articles_id)->get();

                $sumaCostoKit = 0;
                foreach ($articulosKit as $articuloKit) {
                    $articuloKitDefault = CAT_ARTICLES::where('articles_key', '=', $articuloKit->kitArticles_article)->first();
                    if ($articuloKitDefault != null) {
                        $costoPromedioKit = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articuloKitDefault->articles_key)->where('articlesCost_branchKey', '=', $folioAfectar->sales_branchOffice)->where('articlesCost_companieKey', '=', $folioAfectar->sales_company)->where('articlesCost_depotKey', '=', $folioAfectar->sales_depot)->first();

                        if ($costoPromedioKit != null) {
                            $sumaCostoKit += $costoPromedioKit->articlesCost_averageCost * ($articuloKit->kitArticles_cantidad * $articulo->salesDetails_quantity);
                        }
                    }
                }

                $articulo->salesDetails_saleCost = $sumaCostoKit;
                $articulo->update();
            }
        }
    }

    public function agregarCobro($folio, $request)
    {

        $folioAfectar = PROC_SALES::where('sales_id', '=', $folio)->first();
        $lastId = $folioAfectar::latest('sales_id')->first()->sales_id;
        $cobroFactura = json_decode($request['inputJsonCobroFactura'], true);
        if ($folioAfectar->sales_status == $this->estatus[2] || $folioAfectar->sales_status == $this->estatus[0] && $folioAfectar->sales_movement == 'Factura' && $cobroFactura != null) {
            $cobro = PROC_SALES_PAYMENT::WHERE('salesPayment_saleID', '=', $lastId)->first();
            if ($cobro === null) {
                $cobro = new PROC_SALES_PAYMENT();
            }
            $cobro->salesPayment_saleID = $folioAfectar->sales_id;
            $cobro->salesPayment_paymentMethod1 = $cobroFactura['formaCobro1'];
            $cobro->salesPayment_paymentMethod2 = $cobroFactura['formaCobro2'];
            $cobro->salesPayment_paymentMethod3 = $cobroFactura['formaCobro3'];
            $cobro->salesPayment_amount1 = $cobroFactura['importe1'];
            $cobro->salesPayment_amount2 = $cobroFactura['importe2'];
            $cobro->salesPayment_amount3 = $cobroFactura['importe3'];
            $cobro->salesPayment_fullCharge = $cobroFactura['totalFactura'];
            $cobro->salesPayment_Change = $cobroFactura['cambio'];
            $cobro->salesPayment_moneyAccount = $cobroFactura['cuentaPago'];
            $cobro->salesPayment_moneyAccountType = $cobroFactura['accountType'];
            $cobro->salesPayment_additionalInformation = $cobroFactura['infAdicional'];
            $cobro->salesPayment_paymentMethodChange = $cobroFactura['formaCambio7'];
            $cobro->salesPayment_branchOffice = session('sucursal')->branchOffices_key;

            $cobro->save();
        }
    }

    public function agregarCancelacionFactura($folio, $request)
    {
        $folioAfectar = PROC_SALES::where('sales_id', '=', $folio)->first();
        $cancelacionFactura = json_decode($request['inputJsonCancelacionFactura'], true);

        if ($folioAfectar->sales_status == $this->estatus[3] && $folioAfectar->sales_movement == 'Factura' && $folioAfectar->sales_stamped == 1) {
            $cancelacion = new PROC_CANCELED_REASON();
            $cancelacion->canceledReason_module = 'Ventas';
            $cancelacion->canceledReason_moduleID = $folioAfectar->sales_id;
            $cancelacion->canceledReason_reason = $cancelacionFactura['motivoCancelacion'];
            $cancelacion->canceledReason_sustitutionUuid = $cancelacionFactura['folioSustitucion'];

            $cancelacion->save();
        }
    }

    public function agregarMov($folio)
    {
        $folioAfectar = PROC_SALES::where('sales_id', '=', $folio)->first();

        //agregar mov de cotizacion a pedido si tiene origen de cotizacion
        if ($folioAfectar->sales_status == $this->estatus[1] && $folioAfectar->sales_movement == 'Pedido') {

            $movOrigin = PROC_SALES::where('sales_movementID', '=', $folioAfectar->sales_originID)->where('sales_movement', '=', $folioAfectar->sales_origin)->where('sales_company', '=', $folioAfectar->sales_company)->where('sales_branchOffice', '=', $folioAfectar->sales_branchOffice)->first();

            if ($movOrigin != null) {
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->sales_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->sales_company;
                $movimiento->movementFlow_moduleOrigin = 'Ventas';
                $movimiento->movementFlow_originID = $movOrigin->sales_id;
                $movimiento->movementFlow_movementOrigin = $movOrigin->sales_movement;
                $movimiento->movementFlow_movementOriginID = $movOrigin->sales_movementID;
                $movimiento->movementFlow_moduleDestiny = 'Ventas';
                $movimiento->movementFlow_destinityID = $folioAfectar->sales_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->sales_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->sales_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();
            }
        }

        if ($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Factura' && $folioAfectar->sales_typeCondition == 'Crédito') {

            $movOrigin = PROC_SALES::where('sales_movementID', '=', $folioAfectar->sales_originID)->where('sales_movement', '=', $folioAfectar->sales_origin)->where('sales_company', '=', $folioAfectar->sales_company)->where('sales_branchOffice', '=', $folioAfectar->sales_branchOffice)->first();

            if ($movOrigin != null) {
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->sales_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->sales_company;
                $movimiento->movementFlow_moduleOrigin = 'Ventas';
                $movimiento->movementFlow_originID = $movOrigin->sales_id;
                $movimiento->movementFlow_movementOrigin = $movOrigin->sales_movement;
                $movimiento->movementFlow_movementOriginID = $movOrigin->sales_movementID;
                $movimiento->movementFlow_moduleDestiny = 'Ventas';
                $movimiento->movementFlow_destinityID = $folioAfectar->sales_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->sales_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->sales_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();
            }

            $movPosterior = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movementID', '=', $folioAfectar->sales_movementID)->where('accountsReceivable_movement', '=', 'Factura')->where('accountsReceivable_branchOffice', '=', $folioAfectar->sales_branchOffice)->first();

            if ($movPosterior != null) {
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->sales_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->sales_company;
                $movimiento->movementFlow_moduleOrigin = 'Ventas';
                $movimiento->movementFlow_originID = $folioAfectar->sales_id;
                $movimiento->movementFlow_movementOrigin = $folioAfectar->sales_movement;
                $movimiento->movementFlow_movementOriginID = $folioAfectar->sales_movementID;
                $movimiento->movementFlow_moduleDestiny = 'CxC';
                $movimiento->movementFlow_destinityID = $movPosterior->accountsReceivable_id;
                $movimiento->movementFlow_movementDestinity = $movPosterior->accountsReceivable_movement;
                $movimiento->movementFlow_movementDestinityID = $movPosterior->accountsReceivable_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();
            }
        }

        if ($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Factura' && $folioAfectar->sales_typeCondition == 'Contado') {

            $movOrigin = PROC_SALES::where('sales_movementID', '=', $folioAfectar->sales_originID)->where('sales_movement', '=', $folioAfectar->sales_origin)->where('sales_company', '=', $folioAfectar->sales_company)->where('sales_branchOffice', '=', $folioAfectar->sales_branchOffice)->first();

            if ($movOrigin != null) {
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->sales_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->sales_company;
                $movimiento->movementFlow_moduleOrigin = 'Ventas';
                $movimiento->movementFlow_originID = $movOrigin->sales_id;
                $movimiento->movementFlow_movementOrigin = $movOrigin->sales_movement;
                $movimiento->movementFlow_movementOriginID = $movOrigin->sales_movementID;
                $movimiento->movementFlow_moduleDestiny = 'Ventas';
                $movimiento->movementFlow_destinityID = $folioAfectar->sales_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->sales_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->sales_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();
            }

            $movPosterior = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movementID', '=', $folioAfectar->sales_movementID)->where('accountsReceivable_movement', '=', 'Factura')->where('accountsReceivable_branchOffice', '=', $folioAfectar->sales_branchOffice)->first();

            if ($movPosterior != null) {
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->sales_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->sales_company;
                $movimiento->movementFlow_moduleOrigin = 'Ventas';
                $movimiento->movementFlow_originID = $folioAfectar->sales_id;
                $movimiento->movementFlow_movementOrigin = $folioAfectar->sales_movement;
                $movimiento->movementFlow_movementOriginID = $folioAfectar->sales_movementID;
                $movimiento->movementFlow_moduleDestiny = 'CxC';
                $movimiento->movementFlow_destinityID = $movPosterior->accountsReceivable_id;
                $movimiento->movementFlow_movementDestinity = $movPosterior->accountsReceivable_movement;
                $movimiento->movementFlow_movementDestinityID = $movPosterior->accountsReceivable_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();
            }
        }

        if ($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Rechazo de Venta') {

            $movOrigin = PROC_SALES::where('sales_movementID', '=', $folioAfectar->sales_originID)->where('sales_movement', '=', $folioAfectar->sales_origin)->where('sales_company', '=', $folioAfectar->sales_company)->where('sales_branchOffice', '=', $folioAfectar->sales_branchOffice)->first();

            if ($movOrigin != null) {
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->sales_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->sales_company;
                $movimiento->movementFlow_moduleOrigin = 'Ventas';
                $movimiento->movementFlow_originID = $movOrigin->sales_id;
                $movimiento->movementFlow_movementOrigin = $movOrigin->sales_movement;
                $movimiento->movementFlow_movementOriginID = $movOrigin->sales_movementID;
                $movimiento->movementFlow_moduleDestiny = 'Ventas';
                $movimiento->movementFlow_destinityID = $folioAfectar->sales_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->sales_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->sales_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();
            }
        }
    }

    public function concluirOrigines($folio)
    {
        $folioAfectar = PROC_SALES::where('sales_id', '=', $folio)->first();


        //concluir origen de cotizacion a pendiente cuando se afecte
        if ($folioAfectar->sales_status == $this->estatus[1] && $folioAfectar->sales_movement == 'Pedido') {

            $movOrigin = PROC_SALES::where('sales_movementID', '=', $folioAfectar->sales_originID)->where('sales_movement', '=', $folioAfectar->sales_origin)->where('sales_company', '=', $folioAfectar->sales_company)->where('sales_branchOffice', '=', $folioAfectar->sales_branchOffice)->first();

            //concluir origen
            if ($movOrigin != null) {

                //quitar el pendiente del detalle
                $detalle = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $folioAfectar->sales_id)->get();
                //buscamos el articulo origen con el referenceArticles
                foreach ($detalle as $detalle) {
                    if ($detalle->salesDetails_referenceArticles != null) {
                        $articulo = PROC_SALES_DETAILS::where('salesDetails_id', '=', $detalle->salesDetails_referenceArticles)->first();
                        $articulo->salesDetails_outstandingAmount = $articulo->salesDetails_outstandingAmount - $detalle->salesDetails_quantity;
                        $articulo->update();
                    }
                }

                //verificamos que todos los articulos de la cotizacion esten concluidos
                $detalle2 = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $movOrigin->sales_id)->get();
                $concluido = false;
                foreach ($detalle2 as $detalle) {
                    if ($detalle->salesDetails_outstandingAmount <= 0) {
                        $concluido = true;
                    } else {
                        $concluido = false;
                        break;
                    }
                }

                //Concluimos la cotizacion si todos los articulos no tienen pendientes
                if ($concluido == true) {
                    $movOrigin->sales_status = $this->estatus[2];
                    $movOrigin->update();
                }
                // dd($detalle, $detalle2);
            }
        }

        if (($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Factura' && $folioAfectar->sales_typeCondition == 'Crédito') || ($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Factura' && $folioAfectar->sales_typeCondition == 'Contado')) {


            $movOrigin = PROC_SALES::where('sales_movementID', '=', $folioAfectar->sales_originID)->where('sales_movement', '=', $folioAfectar->sales_origin)->where('sales_company', '=', $folioAfectar->sales_company)->where('sales_branchOffice', '=', $folioAfectar->sales_branchOffice)->first();

            if ($movOrigin != null) {

                //quitar el pendiente del detalle
                $detalle = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $folioAfectar->sales_id)->get();
                //buscamos el articulo origen con el referenceArticles
                foreach ($detalle as $detalle) {
                    if ($detalle->salesDetails_referenceArticles != null) {
                        $articulo = PROC_SALES_DETAILS::where('salesDetails_id', '=', $detalle->salesDetails_referenceArticles)->first();
                        $articulo->salesDetails_outstandingAmount = $articulo->salesDetails_outstandingAmount - $detalle->salesDetails_quantity;
                        $articulo->update();
                    }
                }

                //verificamos que todos los articulos de la cotizacion esten concluidos
                $detalle2 = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $movOrigin->sales_id)->get();
                $concluido = false;
                foreach ($detalle2 as $detalle) {
                    if ($detalle->salesDetails_outstandingAmount <= 0) {
                        $concluido = true;
                    } else {
                        $concluido = false;
                        break;
                    }
                }

                //Concluimos la cotizacion si todos los articulos no tienen pendientes
                if ($concluido == true) {
                    $movOrigin->sales_status = $this->estatus[2];
                    $movOrigin->update();
                }
                // dd($detalle, $detalle2);
            }
        }

        if ($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Rechazo de Venta') {


            $movOrigin = PROC_SALES::where('sales_movementID', '=', $folioAfectar->sales_originID)->where('sales_movement', '=', $folioAfectar->sales_origin)->where('sales_company', '=', $folioAfectar->sales_company)->where('sales_branchOffice', '=', $folioAfectar->sales_branchOffice)->first();

            if ($movOrigin != null) {

                //quitar el pendiente del detalle
                $detalle = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $folioAfectar->sales_id)->get();
                //buscamos el articulo origen con el referenceArticles
                foreach ($detalle as $detalle) {
                    $articulo = PROC_SALES_DETAILS::where('salesDetails_id', '=', $detalle->salesDetails_referenceArticles)->first();
                    $articulo->salesDetails_outstandingAmount = $articulo->salesDetails_outstandingAmount - $detalle->salesDetails_quantity;
                    $articulo->update();
                }

                //verificamos que todos los articulos de la cotizacion esten concluidos
                $detalle2 = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $movOrigin->sales_id)->get();
                $concluido = false;
                foreach ($detalle2 as $detalle) {
                    if ($detalle->salesDetails_outstandingAmount <= 0) {
                        $concluido = true;
                    } else {
                        $concluido = false;
                        break;
                    }
                }

                //Concluimos la cotizacion si todos los articulos no tienen pendientes
                if ($concluido == true) {
                    $movOrigin->sales_status = $this->estatus[2];
                    $movOrigin->update();
                }
                // dd($detalle, $detalle2);
            }
        }
    }
    public function getReporteVenta($id)
    {

        $venta = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
            ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_SALES.sales_company')
            ->join('CONF_MONEY', 'CONF_MONEY.money_key', '=', 'PROC_SALES.sales_money')
            ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
            ->where('sales_id', '=', $id)
            // ->select('PROC_SALES.*', 'CAT_CUSTOMERS.customers_name', 'CAT_CUSTOMERS.customers_rfc', 'CAT_CUSTOMERS.customers_addres', 'CONF_CREDIT_CONDITIONS.creditConditions_name', 'CAT_COMPANIES.companies_name', 'CAT_COMPANIES.companies_rfc', 'CAT_COMPANIES.companies_logo')
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


        $articulos_venta = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->WHERE('salesDetails_saleID', '=', $id)->get();

        $articulos_kit = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->join('PROC_KIT_ARTICLES', 'PROC_KIT_ARTICLES.procKit_saleID', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
            ->WHERE('salesDetails_saleID', '=', $id)->get()->unique('procKit_id');

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();


        // dd($articulos_venta, );

        // dd($articulos_kit);

        $direccion = $venta->companies_addres;
        $suburb = $venta->companies_suburb;
        //eliminamos los guiones de la direccion
        $suburb = str_replace('-', '', $suburb);
        //eliminamos los numeros de la direccion
        $suburb = preg_replace('/[0-9]+/', '', $suburb);
        $otrosDatos = $suburb . ', CP. ' . $venta->companies_cp . ',' . $venta->companies_country . ',' . $venta->companies_state . ',' . str_replace('-', '', preg_replace('/[0-9]+/', '', $venta->companies_city));

        $articulos = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->WHERE('salesDetails_saleID', '=', $id)->get();

        if ($venta->sales_movement === 'Pedido') {
            $pdf = PDF::loadView('reportes.pedido-reporte', ['venta' => $id, 'venta' => $venta, 'articulos_venta' => $articulos_venta, 'logo' => $logoBase64, 'direccion' => $direccion, 'otrosDatos' => $otrosDatos, 'articulos' => $articulos, 'articulos_kit' => $articulos_kit, 'parametro' => $parametro]);
            $pdf->set_paper('legal', 'portrait');
        } else {
            $pdf = PDF::loadView('reportes.ventas-reporte', ['venta' => $id, 'venta' => $venta, 'articulos_venta' => $articulos_venta, 'logo' => $logoBase64]);
        }
        return $pdf->stream();
    }

    public function getReporteNotaVenta($id)
    {

        $venta = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
            ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_SALES.sales_company')
            ->join('CONF_MONEY', 'CONF_MONEY.money_key', '=', 'PROC_SALES.sales_money')
            ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
            ->where('sales_id', '=', $id)
            // ->select('PROC_SALES.*', 'CAT_CUSTOMERS.customers_name', 'CAT_CUSTOMERS.customers_rfc', 'CAT_CUSTOMERS.customers_addres', 'CONF_CREDIT_CONDITIONS.creditConditions_name', 'CAT_COMPANIES.companies_name', 'CAT_COMPANIES.companies_rfc', 'CAT_COMPANIES.companies_logo')
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


        $articulos_venta = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->WHERE('salesDetails_saleID', '=', $id)->get();

        $articulos_kit = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->join('PROC_KIT_ARTICLES', 'PROC_KIT_ARTICLES.procKit_saleID', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
            ->WHERE('salesDetails_saleID', '=', $id)->get()->unique('procKit_id');

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();


        // dd($articulos_venta, );

        // dd($articulos_kit);

        $direccion = $venta->companies_addres;
        $suburb = $venta->companies_suburb;
        //eliminamos los guiones de la direccion
        $suburb = str_replace('-', '', $suburb);
        //eliminamos los numeros de la direccion
        $suburb = preg_replace('/[0-9]+/', '', $suburb);
        $otrosDatos = $suburb . ', CP. ' . $venta->companies_cp . ',' . $venta->companies_country . ',' . $venta->companies_state . ',' . str_replace('-', '', preg_replace('/[0-9]+/', '', $venta->companies_city));

        $articulos = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->WHERE('salesDetails_saleID', '=', $id)->get();

        $pdf = PDF::loadView('reportes.pedidoSinImp-reporte', ['venta' => $id, 'venta' => $venta, 'articulos_venta' => $articulos_venta, 'logo' => $logoBase64, 'direccion' => $direccion, 'otrosDatos' => $otrosDatos, 'articulos' => $articulos, 'articulos_kit' => $articulos_kit, 'parametro' => $parametro]);
        $pdf->set_paper('legal', 'portrait');

        return $pdf->stream();
    }

    public function getReporteCotización($id)
    {

        $venta = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
            ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_SALES.sales_company')
            ->join('CONF_MONEY', 'CONF_MONEY.money_key', '=', 'PROC_SALES.sales_money')
            ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
            ->where('sales_id', '=', $id)
            // ->select('PROC_SALES.*', 'CAT_CUSTOMERS.customers_name', 'CAT_CUSTOMERS.customers_rfc', 'CAT_CUSTOMERS.customers_addres', 'CONF_CREDIT_CONDITIONS.creditConditions_name', 'CAT_COMPANIES.companies_name', 'CAT_COMPANIES.companies_rfc', 'CAT_COMPANIES.companies_logo')
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

        $direccion = $venta->companies_addres;
        $suburb = $venta->companies_suburb;
        //eliminamos los guiones de la direccion
        $suburb = str_replace('-', '', $suburb);
        //eliminamos los numeros de la direccion
        $suburb = preg_replace('/[0-9]+/', '', $suburb);
        $otrosDatos = $suburb . ', CP. ' . $venta->companies_cp . ',' . $venta->companies_country . ',' . $venta->companies_state . ',' . str_replace('-', '', preg_replace('/[0-9]+/', '', $venta->companies_city));
        //Seleccionamos solo el texto de la dirección
        $articulos_venta = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->WHERE('salesDetails_saleID', '=', $id)->get();

        $articulos_kit = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->join('PROC_KIT_ARTICLES', 'PROC_KIT_ARTICLES.procKit_saleID', '=', 'PROC_SALES_DETAILS.salesDetails_saleID', 'left outer')
            ->WHERE('salesDetails_saleID', '=', $id)->get()->unique('procKit_id');

        // dd($articulos_kit);

        $articulos = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->WHERE('salesDetails_saleID', '=', $id)->get();

        $cuentasDinero = CAT_MONEY_ACCOUNTS::join('CAT_FINANCIAL_INSTITUTIONS', 'CAT_FINANCIAL_INSTITUTIONS.instFinancial_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_bank')
            ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_company')
            ->where('moneyAccounts_company', '=', session('company')->companies_key)
            ->where('moneyAccounts_status', '=', 'Alta')
            ->where('moneyAccounts_accountType', '!=', 'Caja')
            ->where('moneyAccounts_key', '=', 'BBVA7218')
            ->get();
        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();


        // dd($cuentasDinero);


        $pdf = PDF::loadView('reportes.cotizacion-reporte', ['venta' => $id, 'venta' => $venta, 'articulos_venta' => $articulos_venta, 'logo' => $logoBase64, 'direccion' => $direccion, 'otrosDatos' => $otrosDatos, 'articulos' => $articulos, 'cuentasDinero' => $cuentasDinero, 'articulos_kit' => $articulos_kit, 'parametro' => $parametro]);
        $pdf->set_paper('legal', 'portrait');
        return $pdf->stream();

        //devolvemos un html
        // return view('reportes.cotizacion-reporte', ['venta' => $id, 'venta' => $venta, 'articulos_venta' => $articulos_venta, 'logo' => $logoBase64, 'direccion' => $direccion, 'otrosDatos' => $otrosDatos]);
    }

    public function getReporteCotizaciónSimpuestos($id)
    {

        $venta = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
            ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_SALES.sales_company')
            ->join('CONF_MONEY', 'CONF_MONEY.money_key', '=', 'PROC_SALES.sales_money')
            ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
            ->where('sales_id', '=', $id)
            // ->select('PROC_SALES.*', 'CAT_CUSTOMERS.customers_name', 'CAT_CUSTOMERS.customers_rfc', 'CAT_CUSTOMERS.customers_addres', 'CONF_CREDIT_CONDITIONS.creditConditions_name', 'CAT_COMPANIES.companies_name', 'CAT_COMPANIES.companies_rfc', 'CAT_COMPANIES.companies_logo')
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

        $direccion = $venta->companies_addres;
        $suburb = $venta->companies_suburb;
        //eliminamos los guiones de la direccion
        $suburb = str_replace('-', '', $suburb);
        //eliminamos los numeros de la direccion
        $suburb = preg_replace('/[0-9]+/', '', $suburb);
        $otrosDatos = $suburb . ', CP. ' . $venta->companies_cp . ',' . $venta->companies_country . ',' . $venta->companies_state . ',' . str_replace('-', '', preg_replace('/[0-9]+/', '', $venta->companies_city));
        //Seleccionamos solo el texto de la direccion


        $articulos_venta = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->WHERE('salesDetails_saleID', '=', $id)->get();

        $articulos = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->WHERE('salesDetails_saleID', '=', $id)->first();

        $articulos_kit = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->join('PROC_KIT_ARTICLES', 'PROC_KIT_ARTICLES.procKit_saleID', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
            ->WHERE('salesDetails_saleID', '=', $id)->get()->unique('procKit_id');

        // dd($articulos_kit);

        $cuentasDinero = CAT_MONEY_ACCOUNTS::join('CAT_FINANCIAL_INSTITUTIONS', 'CAT_FINANCIAL_INSTITUTIONS.instFinancial_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_bank')
            ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_company')
            ->where('moneyAccounts_company', '=', session('company')->companies_key)
            ->where('moneyAccounts_status', '=', 'Alta')
            ->where('moneyAccounts_accountType', '!=', 'Caja')
            ->where('moneyAccounts_key', '=', 'BBVA7218')
            ->get();
        // dd($cuentasDinero);

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();


        $pdf = PDF::loadView('reportes.cotizacionSinImp-reporte', ['venta' => $id, 'venta' => $venta, 'articulos_venta' => $articulos_venta, 'logo' => $logoBase64, 'direccion' => $direccion, 'otrosDatos' => $otrosDatos, 'articulos' => $articulos, 'cuentasDinero' => $cuentasDinero, 'articulos_kit' => $articulos_kit, 'parametro' => $parametro]);
        $pdf->set_paper('legal', 'portrait');
        return $pdf->stream();
        //devolvemos un html
        // return view('reportes.cotizacion-reporte', ['venta' => $id, 'venta' => $venta, 'articulos_venta' => $articulos_venta, 'logo' => $logoBase64, 'direccion' => $direccion, 'otrosDatos' => $otrosDatos]);
    }

    public function enviarCorreoCotizacion(Request $request, $id)
    {

        $venta = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
            ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_SALES.sales_company')
            ->join('CONF_MONEY', 'CONF_MONEY.money_key', '=', 'PROC_SALES.sales_money')
            ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
            ->where('sales_id', '=', $id)
            // ->select('PROC_SALES.*', 'CAT_CUSTOMERS.customers_name', 'CAT_CUSTOMERS.customers_rfc', 'CAT_CUSTOMERS.customers_addres', 'CONF_CREDIT_CONDITIONS.creditConditions_name', 'CAT_COMPANIES.companies_name', 'CAT_COMPANIES.companies_rfc', 'CAT_COMPANIES.companies_logo')
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

        $direccion = $venta->companies_addres;
        $suburb = $venta->companies_suburb;
        //eliminamos los guiones de la direccion
        $suburb = str_replace('-', '', $suburb);
        //eliminamos los numeros de la direccion
        $suburb = preg_replace('/[0-9]+/', '', $suburb);
        $otrosDatos = $suburb . ', CP. ' . $venta->companies_cp . ',' . $venta->companies_country . ',' . $venta->companies_state . ',' . str_replace('-', '', preg_replace('/[0-9]+/', '', $venta->companies_city));
        //Seleccionamos solo el texto de la dirección
        $articulos_venta = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->WHERE('salesDetails_saleID', '=', $id)->get();

        $articulos_kit = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->join('PROC_KIT_ARTICLES', 'PROC_KIT_ARTICLES.procKit_saleID', '=', 'PROC_SALES_DETAILS.salesDetails_saleID', 'left outer')
            ->WHERE('salesDetails_saleID', '=', $id)->get()->unique('procKit_id');

        // dd($articulos_kit);

        $articulos = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->WHERE('salesDetails_saleID', '=', $id)->get();

        $cuentasDinero = CAT_MONEY_ACCOUNTS::join('CAT_FINANCIAL_INSTITUTIONS', 'CAT_FINANCIAL_INSTITUTIONS.instFinancial_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_bank')
            ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_company')
            ->where('moneyAccounts_company', '=', session('company')->companies_key)
            ->where('moneyAccounts_status', '=', 'Alta')
            ->whereNotIn('moneyAccounts_bank', ['INTERNA', 'CAJAS'])
            ->where('moneyAccounts_accountType', '!=', 'Caja')
            ->get();
        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();

        //buscamos el nombre del cliente
        $cliente = CAT_CUSTOMERS::where('customers_key', '=', $venta->sales_customer)->first();
        //buscamos el folio del movimiento
        $infoVenta = PROC_SALES::where('sales_id', '=', $id)->first();
        // buscamos el nombre del vendedor



        // dd($cuentasDinero);


        $pdf = PDF::loadView('reportes.cotizacion-reporte', ['venta' => $id, 'venta' => $venta, 'articulos_venta' => $articulos_venta, 'logo' => $logoBase64, 'direccion' => $direccion, 'otrosDatos' => $otrosDatos, 'articulos' => $articulos, 'cuentasDinero' => $cuentasDinero, 'articulos_kit' => $articulos_kit, 'parametro' => $parametro]);
        $pdf->set_paper('legal', 'portrait');

        $pdfContent = $pdf->output();
        // Obtener las direcciones de correo seleccionadas
        $selectedEmails = $request->input('email', []);

        // Si se seleccionó la opción "Otro correo", agregar esa dirección a la lista
        if ($request->has('anotherEmail') && !empty($request->input('anotherEmail'))) {
            $selectedEmails[] = $request->input('anotherEmail');
        }
        $nombreEmpresa = session('company')->companies_nameShort;
        // dd($nombreEmpresa);
        // Verificar si hay correos seleccionados antes de enviar
        if (count($selectedEmails) > 0) {
            foreach ($selectedEmails as $email) {
                Mail::to($email)->send(new EnviarCotizacion(
                    $pdfContent,
                    $infoVenta->sales_movementID,
                    $cliente->customers_businessName,
                    $infoVenta->sales_reference,
                    $infoVenta->sales_seller,
                    $nombreEmpresa
                ));
            }
            return redirect()->back()->with('status', true)->with('message', 'El correo se envió correctamente');
        } else {
            // No se seleccionaron direcciones de correo, puedes mostrar un mensaje de error o redireccionar
            return redirect()->back()->with('status', false)->with('message', 'No se seleccionó ninguna dirección de correo');
        }

        // Mail::to('destinatario@example.com')
        // ->send(new EnviarCotizacion($pdfContent, $id, $cliente));


    }

    public function getReporteFormatoEntrega($id)
    {
        $seriesArticulosVendidos = [];
        $venta = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
            ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_SALES.sales_company')
            ->join('CONF_MONEY', 'CONF_MONEY.money_key', '=', 'PROC_SALES.sales_money')
            ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
            ->where('sales_id', '=', $id)
            // ->select('PROC_SALES.*', 'CAT_CUSTOMERS.customers_name', 'CAT_CUSTOMERS.customers_rfc', 'CAT_CUSTOMERS.customers_addres', 'CONF_CREDIT_CONDITIONS.creditConditions_name', 'CAT_COMPANIES.companies_name', 'CAT_COMPANIES.companies_rfc', 'CAT_COMPANIES.companies_logo')
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


        $articulos_venta = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->WHERE('salesDetails_saleID', '=', $id)->get();

        $articulos_kit = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->join('PROC_KIT_ARTICLES', 'PROC_KIT_ARTICLES.procKit_saleID', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
            ->WHERE('salesDetails_saleID', '=', $id)->get()->unique('procKit_id');

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();


        // dd($articulos_kit);

        //Buscamos los articulos de tipoSerie en el detalle de las ventas

        foreach ($articulos_venta as $articuloVenta) {
            if ($articuloVenta->salesDetails_type == "Serie") {
                $seriesEliminados2 = PROC_DEL_SERIES_MOV2::WHERE('delSeriesMov2_companieKey', '=', $venta->sales_company)->WHERE('delSeriesMov2_branchKey', '=', $venta->sales_branchOffice)->WHERE('delSeriesMov2_article', '=', $articuloVenta->salesDetails_article)->WHERE('delSeriesMov2_articleID', '=', $articuloVenta->salesDetails_id)->WHERE('delSeriesMov2_saleID', '=', $articuloVenta->salesDetails_saleID)->WHERE('delSeriesMov2_module', '=', 'Ventas')->get();

                if (count($seriesEliminados2) > 0) {
                    foreach ($seriesEliminados2 as $series) {
                        $seriesArticulosVendidos[$series->delSeriesMov2_article . '-' . $series->delSeriesMov2_articleID][] = $series->toArray();
                    }
                }
            }
        }



        foreach ($articulos_kit as $componente) {
            if ($componente->procKit_tipo == "Serie") {
                $seriesComponentes = PROC_DEL_SERIES_MOV2::WHERE('delSeriesMov2_companieKey', '=', $venta->sales_company)->WHERE('delSeriesMov2_branchKey', '=', $venta->sales_branchOffice)->WHERE('delSeriesMov2_article', '=', $componente->procKit_articleID)->WHERE('delSeriesMov2_articleID', '=', $componente->procKit_article)->WHERE('delSeriesMov2_saleID', '=', $componente->salesDetails_saleID)->WHERE('delSeriesMov2_module', '=', 'Ventas')->get();


                if (count($seriesComponentes) > 0) {
                    foreach ($seriesComponentes as $seriesC) {
                        $seriesArticulosVendidos[$seriesC->delSeriesMov2_article . '-' . $seriesC->delSeriesMov2_articleID][] = $seriesC->toArray();
                    }
                }
            }
        }


        $direccion = $venta->companies_addres;
        $suburb = $venta->companies_suburb;
        //eliminamos los guiones de la direccion
        $suburb = str_replace('-', '', $suburb);
        //eliminamos los numeros de la direccion
        $suburb = preg_replace('/[0-9]+/', '', $suburb);
        $otrosDatos = $suburb . ', CP. ' . $venta->companies_cp . ',' . $venta->companies_country . ',' . $venta->companies_state . ',' . str_replace('-', '', preg_replace('/[0-9]+/', '', $venta->companies_city));

        $articulos = PROC_SALES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_SALES_DETAILS.SalesDetails_unit')
            ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
            ->WHERE('salesDetails_saleID', '=', $id)->get();

        $pdf = PDF::loadView('reportes.formatoEntrega-reporte', ['venta' => $id, 'venta' => $venta, 'articulos_venta' => $articulos_venta, 'logo' => $logoBase64, 'direccion' => $direccion, 'otrosDatos' => $otrosDatos, 'articulos' => $articulos, 'articulos_kit' => $articulos_kit, 'series' => $seriesArticulosVendidos, 'parametro' => $parametro]);
        $pdf->set_paper('legal', 'portrait');
        return $pdf->stream();

        //devolvemos un html
        // return view('reportes.cotizacion-reporte', ['venta' => $id, 'venta' => $venta, 'articulos_venta' => $articulos_venta, 'logo' => $logoBase64, 'direccion' => $direccion, 'otrosDatos' => $otrosDatos]);
    }

    public function getFactura()
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

        $pdf = PDF::loadView('reportes.nuevaFactura', ['logo' => $logoBase64]);
        //le ponemos tamaño carta y vertical
        $pdf->set_paper('letter', 'portrait');
        return $pdf->stream();
    }

    public function agregarTesoreria($folio)
    {
        $folioAfectar = PROC_SALES::where('sales_id', '=', $folio)->first();
        if ($folioAfectar->sales_status == $this->estatus[2] && $folioAfectar->sales_movement == 'Factura' && $folioAfectar->sales_typeCondition == 'Contado') {

            $cambios = PROC_SALES_PAYMENT::where('salesPayment_saleID', '=', $folioAfectar->sales_id)->get();

            // dd($cambios->getMetodo1);

            $ingreso = false;
            $deposito = false;
            $tesoreriaArray = [];
            foreach ($cambios as $camb) {
                if ($camb->salesPayment_moneyAccountType == 'Caja') {
                    $valorCambio = (float)$camb->salesPayment_Change;
                    $ingreso = true;
                } else {
                    $deposito = true;
                }
                if ($camb->salesPayment_amount1 > 0) {
                    $tesoreriaArray['salesPayment_paymentMethod1'] = $camb->getMetodo1->formsPayment_sat . '#' . $camb->salesPayment_amount1 . '#' . $camb->salesPayment_moneyAccount;
                }

                if ($camb->salesPayment_amount2 > 0) {
                    $tesoreriaArray['salesPayment_paymentMethod2'] =
                        $camb->getMetodo2->formsPayment_sat . '#' . $camb->salesPayment_amount2 . '#' . $camb->salesPayment_moneyAccount;
                }

                if ($camb->salesPayment_amount3 > 0) {
                    $tesoreriaArray['salesPayment_paymentMethod3'] =
                        $camb->getMetodo3->formsPayment_sat . '#' . $camb->salesPayment_amount3 . '#' . $camb->salesPayment_moneyAccount;
                }

                if ($ingreso == true && $valorCambio > 0) {
                    $tesoreria = new PROC_TREASURY();

                    $folioEgreso = PROC_TREASURY::where('treasuries_movement', '=', 'Egreso')->where('treasuries_branchOffice', '=', $folioAfectar->sales_branchOffice)->max('treasuries_movementID');
                    $folioEgreso = $folioEgreso == null ? 1 : $folioEgreso + 1;
                    $tesoreria->treasuries_movement = 'Egreso';
                    $tesoreria->treasuries_movementID = $folioEgreso;
                    $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->sales_issuedate)->format('Y-m-d H:i:s');
                    $tesoreria->treasuries_concept = $folioAfectar->sales_concept;
                    $tesoreria->treasuries_money = $folioAfectar->sales_money;
                    $tesoreria->treasuries_typeChange = $folioAfectar->sales_typeChange;
                    $tesoreria->treasuries_moneyAccount = $camb->salesPayment_moneyAccount;
                    $tesoreria->treasuries_moneyAccountOrigin = $camb->salesPayment_moneyAccount;
                    $tesoreria->treasuries_paymentMethod = $camb->salesPayment_paymentMethodChange;
                    $tesoreria->treasuries_beneficiary = null;
                    $tesoreria->treasuries_reference = 'Cambio';
                    $tesoreria->treasuries_amount = $camb->salesPayment_Change;
                    $tesoreria->treasuries_taxes = null;
                    $tesoreria->treasuries_total = $camb->salesPayment_Change;
                    $tesoreria->treasuries_company = $folioAfectar->sales_company;
                    $tesoreria->treasuries_branchOffice = $folioAfectar->sales_branchOffice;
                    $tesoreria->treasuries_user = $folioAfectar->sales_user;
                    $tesoreria->treasuries_status = $this->estatus[2];
                    $tesoreria->treasuries_originType = 'Ventas';
                    $tesoreria->treasuries_origin = $folioAfectar->sales_movement;
                    $tesoreria->treasuries_originID = $folioAfectar->sales_movementID;
                    $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
                    $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                    $create = $tesoreria->save();
                    $lastId = $tesoreria->latest('treasuries_id')->first()->treasuries_id;
                    if ($lastId != null) {
                        $this->agregarAuxiliarPosterior($lastId);
                        $this->agregarMovPosterior($lastId, $folioAfectar->sales_id);
                    }
                }
            }

            foreach ($tesoreriaArray as $key => $value) {

                $tesoreo = explode('#', $value);
                // dd($tesoreo);
                if ($ingreso == true) {
                    if ($tesoreo[0] == '01') {
                        $tesoreria = new PROC_TREASURY();

                        $folioEgreso = PROC_TREASURY::where('treasuries_movement', '=', 'Ingreso')->where('treasuries_branchOffice', '=', $folioAfectar->sales_branchOffice)->max('treasuries_movementID');
                        $folioEgreso = $folioEgreso == null ? 1 : $folioEgreso + 1;
                        $tesoreria->treasuries_movement = 'Ingreso';
                        $tesoreria->treasuries_movementID = $folioEgreso;
                        $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->sales_issuedate)->format('Y-m-d H:i:s');
                        $tesoreria->treasuries_concept = $folioAfectar->sales_concept;
                        $tesoreria->treasuries_money = $folioAfectar->sales_money;
                        $tesoreria->treasuries_typeChange = $folioAfectar->sales_typeChange;
                        $tesoreria->treasuries_moneyAccount = $tesoreo[2];
                        $tesoreria->treasuries_moneyAccountOrigin = $tesoreo[2];
                        $tesoreria->treasuries_paymentMethod = $tesoreo[0];
                        $tesoreria->treasuries_beneficiary = $folioAfectar->sales_customer;
                        $tesoreria->treasuries_reference = $folioAfectar->sales_reference;
                        $tesoreria->treasuries_amount = $tesoreo[1];
                        $tesoreria->treasuries_taxes = null;
                        $tesoreria->treasuries_total = $tesoreo[1];
                        $tesoreria->treasuries_company = $folioAfectar->sales_company;
                        $tesoreria->treasuries_branchOffice = $folioAfectar->sales_branchOffice;
                        $tesoreria->treasuries_user = $folioAfectar->sales_user;
                        $tesoreria->treasuries_status = $this->estatus[2];
                        $tesoreria->treasuries_originType = 'Ventas';
                        $tesoreria->treasuries_origin = $folioAfectar->sales_movement;
                        $tesoreria->treasuries_originID = $folioAfectar->sales_movementID;
                        $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
                        $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                        $create = $tesoreria->save();
                        $lastId = $tesoreria->latest('treasuries_id')->first()->treasuries_id;

                        if ($lastId != null) {
                            $this->agregarAuxiliarPosterior($lastId);
                            $this->agregarMovPosterior($lastId, $folioAfectar->sales_id);
                        }
                    } else {
                        $tesoreria = new PROC_TREASURY();

                        $folioEgreso = PROC_TREASURY::where('treasuries_movement', '=', 'Solicitud Depósito')->where('treasuries_branchOffice', '=', $folioAfectar->sales_branchOffice)->max('treasuries_movementID');
                        $folioEgreso = $folioEgreso == null ? 1 : $folioEgreso + 1;
                        $tesoreria->treasuries_movement = 'Solicitud Depósito';
                        $tesoreria->treasuries_movementID = $folioEgreso;
                        $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->sales_issuedate)->format('Y-m-d H:i:s');
                        $tesoreria->treasuries_concept = $folioAfectar->sales_concept;
                        $tesoreria->treasuries_money = $folioAfectar->sales_money;
                        $tesoreria->treasuries_typeChange = $folioAfectar->sales_typeChange;
                        $tesoreria->treasuries_moneyAccount = $tesoreo[2];
                        $tesoreria->treasuries_moneyAccountOrigin = $tesoreo[2];
                        $tesoreria->treasuries_paymentMethod = $tesoreo[0];
                        // dd($tesoreria);
                        $tesoreria->treasuries_beneficiary = $folioAfectar->sales_customer;
                        $tesoreria->treasuries_reference = $folioAfectar->sales_reference;
                        $tesoreria->treasuries_amount = $tesoreo[1];
                        $tesoreria->treasuries_taxes = null;
                        $tesoreria->treasuries_total = $tesoreo[1];
                        $tesoreria->treasuries_company = $folioAfectar->sales_company;
                        $tesoreria->treasuries_branchOffice = $folioAfectar->sales_branchOffice;
                        $tesoreria->treasuries_user = $folioAfectar->sales_user;
                        $tesoreria->treasuries_status = $this->estatus[1]; //este
                        $tesoreria->treasuries_originType = 'Ventas';
                        $tesoreria->treasuries_origin = $folioAfectar->sales_movement;
                        $tesoreria->treasuries_originID = $folioAfectar->sales_movementID;
                        $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
                        $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                        $create = $tesoreria->save();
                        $lastId = $tesoreria->latest('treasuries_id')->first()->treasuries_id;
                        $mov = $tesoreria->latest('treasuries_id')->first();

                        if ($lastId != null) {
                            $this->agregarAuxiliarPosterior($lastId);
                            $this->agregarMovPosterior($lastId, $folioAfectar->sales_id);
                            // $this->generarDepositoCaja($mov);
                        }
                    }
                }


                if ($deposito == true) {
                    $tesoreria = new PROC_TREASURY();

                    $folioEgreso = PROC_TREASURY::where('treasuries_movement', '=', 'Solicitud Depósito')->where('treasuries_branchOffice', '=', $folioAfectar->sales_branchOffice)->max('treasuries_movementID');
                    $folioEgreso = $folioEgreso == null ? 1 : $folioEgreso + 1;
                    $tesoreria->treasuries_movement = 'Solicitud Depósito';
                    $tesoreria->treasuries_movementID = $folioEgreso;
                    $tesoreria->treasuries_issuedate = Carbon::parse($folioAfectar->sales_issuedate)->format('Y-m-d H:i:s');
                    $tesoreria->treasuries_concept = $folioAfectar->sales_concept;
                    $tesoreria->treasuries_money = $folioAfectar->sales_money;
                    $tesoreria->treasuries_typeChange = $folioAfectar->sales_typeChange;
                    $tesoreria->treasuries_moneyAccount = $tesoreo[2];
                    $tesoreria->treasuries_moneyAccountOrigin = $tesoreo[2];
                    $tesoreria->treasuries_paymentMethod = $tesoreo[0];
                    $tesoreria->treasuries_amount = $tesoreo[1];
                    $tesoreria->treasuries_beneficiary = $folioAfectar->sales_customer;
                    $tesoreria->treasuries_reference = $folioAfectar->sales_reference;
                    $tesoreria->treasuries_taxes = null;
                    $tesoreria->treasuries_total = $tesoreo[1];
                    $tesoreria->treasuries_company = $folioAfectar->sales_company;
                    $tesoreria->treasuries_branchOffice = $folioAfectar->sales_branchOffice;
                    $tesoreria->treasuries_user = $folioAfectar->sales_user;
                    $tesoreria->treasuries_status = $this->estatus[1];
                    $tesoreria->treasuries_originType = 'Ventas';
                    $tesoreria->treasuries_origin = $folioAfectar->sales_movement;
                    $tesoreria->treasuries_originID = $folioAfectar->sales_movementID;
                    $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
                    $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                    $create = $tesoreria->save();
                    $lastId = $tesoreria->latest('treasuries_id')->first()->treasuries_id;

                    if ($lastId != null) {
                        $this->agregarMovPosterior($lastId, $folioAfectar->sales_id);
                    }
                }
            }
        }
    }

    public function generarDepositoCaja($data)
    {
        $tesoreria = new PROC_TREASURY();

        $folioEgreso = PROC_TREASURY::where('treasuries_movement', '=', 'Depósito')->where('treasuries_branchOffice', '=', $data->treasuries_branchOffice)->max('treasuries_movementID');
        $folioEgreso = $folioEgreso == null ? 1 : $folioEgreso + 1;
        $tesoreria->treasuries_movement = 'Depósito';
        $tesoreria->treasuries_movementID = $folioEgreso;
        $tesoreria->treasuries_issuedate = Carbon::parse($data->sales_issuedate)->format('Y-m-d H:i:s');
        $tesoreria->treasuries_concept = $data->treasuries_concept;
        $tesoreria->treasuries_money = $data->treasuries_money;
        $tesoreria->treasuries_typeChange = $data->treasuries_typeChange;
        $tesoreria->treasuries_moneyAccount = $data->treasuries_moneyAccount;
        $tesoreria->treasuries_moneyAccountOrigin = $data->treasuries_moneyAccountOrigin;
        $tesoreria->treasuries_paymentMethod = $data->treasuries_paymentMethod;
        $tesoreria->treasuries_amount = $data->treasuries_amount;
        $tesoreria->treasuries_taxes = null;
        $tesoreria->treasuries_total = $data->treasuries_total;
        $tesoreria->treasuries_company = $data->treasuries_company;
        $tesoreria->treasuries_branchOffice = $data->treasuries_branchOffice;
        $tesoreria->treasuries_user = $data->treasuries_user;
        $tesoreria->treasuries_status = $this->estatus[2];
        $tesoreria->treasuries_originType = 'Din';
        $tesoreria->treasuries_origin = $data->treasuries_movement;
        $tesoreria->treasuries_originID = $data->treasuries_movementID;
        $tesoreria->created_at = Carbon::now()->format('Y-m-d H:i:s');
        $tesoreria->updated_at = Carbon::now()->format('Y-m-d H:i:s');
        $create = $tesoreria->save();
        $lastId = $tesoreria->latest('treasuries_id')->first();
        if ($lastId != null) {
            $this->agregarFlujoTesoreria($data, $lastId);
            $this->agregarAuxiliarTesoreria($lastId);
        }
    }

    public function agregarFlujoTesoreria($origen, $destino)
    {
        $movimiento = new PROC_MOVEMENT_FLOW();
        $movimiento->movementFlow_branch = $origen->treasuries_branchOffice;
        $movimiento->movementFlow_company = $origen->treasuries_company;
        $movimiento->movementFlow_moduleOrigin = 'Din';
        $movimiento->movementFlow_originID = $origen->treasuries_id;
        $movimiento->movementFlow_movementOrigin = $origen->treasuries_movement;
        $movimiento->movementFlow_movementOriginID = $origen->treasuries_movementID;
        $movimiento->movementFlow_moduleDestiny = 'Din';
        $movimiento->movementFlow_destinityID = $destino->treasuries_id;
        $movimiento->movementFlow_movementDestinity = $destino->treasuries_movement;
        $movimiento->movementFlow_movementDestinityID = $destino->treasuries_movementID;
        $movimiento->movementFlow_cancelled = 0;
        $movimiento->save();
    }

    public function agregarAuxiliarTesoreria($tesoreria)
    {
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
        $auxiliar->assistant_charge = $tesoreria->treasuries_total;
        $auxiliar->assistant_payment = null;

        $auxiliar->assistant_apply = $tesoreria->treasuries_movement;
        $auxiliar->assistant_applyID = $tesoreria->treasuries_movementID;
        $auxiliar->assistant_canceled = 0;
        $auxiliar->assistant_reference = $tesoreria->treasuries_reference;


        $auxiliar->save();
    }


    //funciones posteriores al agregar a tesoreria
    public function agregarAuxiliarPosterior($id)
    {
        $tesoreria = PROC_TREASURY::where('treasuries_id', '=', $id)->first();


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
        if ($tesoreria->treasuries_movement == 'Ingreso') {
            $auxiliar->assistant_charge = $tesoreria->treasuries_total;
            $auxiliar->assistant_payment = null;
        } else {
            $auxiliar->assistant_charge = null;
            $auxiliar->assistant_payment = $tesoreria->treasuries_total;
        }

        $auxiliar->assistant_apply = $tesoreria->treasuries_movement;
        $auxiliar->assistant_applyID = $tesoreria->treasuries_movementID;
        $auxiliar->assistant_canceled = 0;
        $auxiliar->assistant_reference = $tesoreria->treasuries_reference;


        $auxiliar->save();
    }
    //funciones posteriores al agregar a tesoreria
    public function agregarMovPosterior($destino, $origen)
    {
        $origen = PROC_SALES::where(
            'sales_id',
            '=',
            $origen
        )->first();
        $destino = PROC_TREASURY::where('treasuries_id', '=', $destino)->first();

        $movimiento = new PROC_MOVEMENT_FLOW();
        $movimiento->movementFlow_branch = $origen->sales_branchOffice;
        $movimiento->movementFlow_company = $origen->sales_company;
        $movimiento->movementFlow_moduleOrigin = 'Ventas';
        $movimiento->movementFlow_originID = $origen->sales_id;
        $movimiento->movementFlow_movementOrigin = $origen->sales_movement;
        $movimiento->movementFlow_movementOriginID = $origen->sales_movementID;
        $movimiento->movementFlow_moduleDestiny = 'Din';
        $movimiento->movementFlow_destinityID = $destino->treasuries_id;
        $movimiento->movementFlow_movementDestinity = $destino->treasuries_movement;
        $movimiento->movementFlow_movementDestinityID = $destino->treasuries_movementID;
        $movimiento->movementFlow_cancelled = 0;
        $movimiento->save();
    }

    public function eliminarVenta(Request $request)
    {

        $venta = PROC_SALES::where('sales_id', '=', $request->id)->first();

        if ($venta->sales_movement === "Factura") {
            $comercioExt = PROC_SALES_FOREIGN_TRADE::WHERE('salesForeingTrade_saleID', '=', $request->id)->first();
            if ($comercioExt !== null) {
                $comercioExt->delete();
            }
        }

        if ($venta->sales_movement === "Factura" || $venta->sales_typeCondition === "Contado") {
            $facturaContado = PROC_SALES_PAYMENT::where('salesPayment_saleID', '=', $request->id)->first();
            if ($facturaContado !== null) {
                $facturaContado->delete();
            }
        }
        // dd($venta);

        //buscamos sus articulos
        $articulos = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $request->id)->where('salesDetails_branchOffice', '=', $venta->sales_branchOffice)->get();
        $articulosKits = PROC_KIT_ARTICLES::where('procKit_saleID', '=', $venta->sales_id)->get();

        //Buscamos los articulos con sus listas de empaque
        if ($articulos->count() > 0) {

            //eliminamos sus articulos
            foreach ($articulos as $articulo) {
                if ($articulo->salesDetails_type == 'Serie') {
                    if ($venta->sales_movement == 'Factura' || $venta->sales_movement == 'Pedido') {
                        $series = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_saleID', '=', $articulo->salesDetails_saleID)->where('delSeriesMov2_articleID', '=', $articulo->salesDetails_id)->get();
                        // dd($series);
                        if ($series != null) {
                            foreach ($series as $serie) {
                                $serie->delete();
                            }
                        }
                    }
                }

                if ($articulo->salesDetails_type == "Kit") {
                    if ($venta->sales_movement == 'Factura' || $venta->sales_movement == 'Pedido') {
                        $series = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_saleID', '=', $articulo->salesDetails_saleID)->where('delSeriesMov2_articleID', '=', $articulo->salesDetails_article)->get();
                        // dd($series);
                        if ($series != null) {
                            foreach ($series as $serie) {
                                $serie->delete();
                            }
                        }
                    }
                }
                $articulosDelete = $articulo->delete();
            }
        } else {
            $articulosDelete = true;
        }



        if ($articulosKits->count() > 0) {
            //eliminamos sus articulos
            foreach ($articulosKits as $articulo2) {
                $articulosDelete = $articulo2->delete();
            }
        } else {
            $articulosDelete = true;
        }

        // dd($articulos);
        if ($venta->sales_status === $this->estatus[0] && $articulosDelete === true) {
            $isDelete = $venta->delete();
        } else {
            $isDelete = false;
        }

        if ($isDelete) {
            $status = 200;
            $message = 'Venta eliminada correctamente';
        } else {
            $status = 500;
            $message = 'Error al eliminar la venta';
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status]);
    }

    public function cancelarVenta(Request $request)
    {
        $ventaCancelar = PROC_SALES::where('sales_id', '=', $request->id)->first();

        $ventaCancelada = false;
        $rechazadaCancelada = true;
        $ventaContado = false;
        $ventaCredito = false;



        if ($ventaCancelar->sales_status == $this->estatus[2] && $ventaCancelar->sales_movement == 'Factura' && $ventaCancelar->sales_typeCondition == 'Crédito') {

            try {
                $ventaContado = true;
                $ventaCredito = false;

                $movGenerado = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $ventaCancelar->sales_id)->where('movementFlow_movementDestinity', '=', $ventaCancelar->sales_movement)->where('movementFlow_moduleOrigin', '=', 'Ventas')->first();

                //verificar si ya hay algun movimiento posterior a este
                $movimiento = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $movGenerado->movementFlow_destinityID)->first();
                //  dd( gettype($compraCancelar->sales_total), gettype($movimiento->accountsPayable_balance));
                if ($ventaCancelar->sales_total != $movimiento->accountsReceivable_balance) {
                    //  echo 'entro';
                    $status = 500;
                    $message = 'No se puede cancelar la venta, ya que hay movimientos posteriores a esta';

                    return response()->json(['mensaje' => $message, 'estatus' => $status]);
                }




                //buscamos el flujo de la venta
                $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $ventaCancelar->sales_id)->where('movementFlow_moduleOrigin', '=', 'Ventas')->where('movementFlow_movementOriginID', '=', $ventaCancelar->sales_movementID)->where('movementFlow_branch', '=', $ventaCancelar->sales_branchOffice)->first();

                //bucamos el cxc generado por la venta y lo cancelamos
                $cxc = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', '=', $flujo->movementFlow_destinityID)->where('accountsReceivable_branchOffice', '=', $ventaCancelar->sales_branchOffice)->first();

                if ($cxc != null) {
                    $cxc->accountsReceivable_status = $this->estatus[3];
                    $validar = $cxc->update();
                    if ($validar) {
                        $ventaCancelada = true;
                    } else {
                        $ventaCancelada = false;
                    }
                }

                //buscamos el cxc pendiente de la venta y lo cancelamos
                $cxcp = PROC_ACCOUNTS_RECEIVABLE_P::where('accountsReceivableP_movement', '=', $cxc->accountsReceivable_movement)->where('accountsReceivableP_movementID', '=', $cxc->accountsReceivable_movementID)->where('accountsReceivableP_branchOffice', '=', $cxc->accountsReceivable_branchOffice)->first();

                if ($cxcp != null) {
                    $validar2 = $cxcp->delete();
                    if ($validar2) {
                        $ventaCancelada = true;
                    } else {
                        $ventaCancelada = false;
                    }
                }

                //regresamos el saldo al cliente
                $saldo = PROC_BALANCE::where('balance_account', '=', $ventaCancelar->sales_customer)->where('balance_branchKey', '=', $ventaCancelar->sales_branchOffice)->where('balance_money', '=', $ventaCancelar->sales_money)->where('balance_branch', '=', 'CxC')->first();

                if ($saldo != null) {
                    $saldo->balance_balance = $saldo->balance_balance - $ventaCancelar->sales_total;
                    $saldo->balance_reconcile = $saldo->balance_balance;
                    $validar3 = $saldo->update();
                    if ($validar3) {
                        $ventaCancelada = true;
                    } else {
                        $venta = false;
                    }
                }

                $articulos = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $ventaCancelar->sales_id)->where('salesDetails_branchOffice', '=', $ventaCancelar->sales_branchOffice)->get();

                foreach ($articulos as $articulo) {
                    if ($articulo->salesDetails_type == 'Serie') {
                        $series = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_saleID', '=', $articulo->salesDetails_saleID)->where('delSeriesMov2_articleID', '=', $articulo->salesDetails_id)->get();

                        if ($ventaCancelar->sales_origin == "Pedido") {
                            //Buscamos el origen de la factura para buscar las series que fueron seleccionadas
                            $ventaOrigen = PROC_SALES::WHERE('sales_movement', '=', 'Pedido')->WHERE('sales_movementID', '=', $ventaCancelar->sales_originID)->WHERE('sales_company', '=',  session('company')->companies_key)->WHERE('sales_branchOffice', '=', session('sucursal')->branchOffices_key)->first();

                            $seriesOrigen = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_saleID', '=', $ventaOrigen->sales_id)->where('delSeriesMov2_article', '=', $articulo->salesDetails_article)->get();

                            foreach ($seriesOrigen  as $seriesO) {
                                $seriesO->delSeriesMov2_affected = 0;
                                $seriesO->update();
                            }
                        }
                        // dd($series);
                        if (
                            $series != null
                        ) {
                            foreach ($series as $serie) {
                                $serie->delSeriesMov2_cancelled = 1;
                                $serie->update();
                                // $serie->delete();

                                $proc_lot_series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', $ventaCancelar->sales_company)->where('lotSeries_branchKey', '=', $ventaCancelar->sales_branchOffice)->where('lotSeries_lotSerie', "=", $serie->delSeriesMov2_lotSerie)->first();


                                if ($proc_lot_series !== null) {
                                    $proc_lot_series->lotSeries_delete = 0;
                                    $proc_lot_series->update();
                                }
                            }
                        }
                    }

                    if ($articulo->salesDetails_type != 'Kit' && $articulo->salesDetails_type != 'Servicio') {

                        $costo = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articulo->salesDetails_article)->where('articlesCost_branchKey', '=', $articulo->salesDetails_branchOffice)->first();

                        if ($costo === null) {
                            $costo = $articulo->salesDetails_unitCost;
                        } else {
                            $costo = $costo->articlesCost_lastCost === null || ".0000" ? $costo->articlesCost_averageCost : $costo->articlesCost_lastCost;
                        }

                        $importe = $articulo->salesDetails_inventoryAmount * $costo;
                        //agregar datos a aux
                        $auxiliarU = new PROC_ASSISTANT_UNITS();
                        $auxiliarU->assistantUnit_companieKey = $ventaCancelar->sales_company;
                        $auxiliarU->assistantUnit_branchKey = $ventaCancelar->sales_branchOffice;
                        $auxiliarU->assistantUnit_branch = 'Inv';
                        $auxiliarU->assistantUnit_movement = $ventaCancelar->sales_movement;
                        $auxiliarU->assistantUnit_movementID = $ventaCancelar->sales_movementID;
                        $auxiliarU->assistantUnit_module = 'Ventas';
                        $auxiliarU->assistantUnit_moduleID = $articulo->salesDetails_saleID;
                        if ($ventaCancelar->sales_money != session('generalParameters')->generalParameters_defaultMoney) {
                            $moneda = CONF_MONEY::where('money_key', '=', session('generalParameters')->generalParameters_defaultMoney)->first();
                            $auxiliarU->assistantUnit_money = $moneda->money_key;
                            $auxiliarU->assistantUnit_typeChange = $moneda->money_change;
                        } else {
                            $auxiliarU->assistantUnit_money = $ventaCancelar->sales_money;
                            $auxiliarU->assistantUnit_typeChange = $ventaCancelar->sales_typeChange;
                        }
                        $auxiliarU->assistantUnit_group = $articulo->salesDetails_depot;
                        $auxiliarU->assistantUnit_account = $articulo->salesDetails_article;
                        //ponemos fecha del ejercicio
                        $year = Carbon::now()->year;
                        //sacamos el periodo 
                        $period = Carbon::now()->month;


                        $abonoUnidad = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articulo->salesDetails_article)->where('articlesCost_branchKey', '=', $articulo->salesDetails_branchOffice)->where('articlesCost_companieKey', '=', $ventaCancelar->sales_company)->where('articlesCost_depotKey', '=', $ventaCancelar->sales_depot)->first();

                        $auxiliarU->assistantUnit_year = $year;
                        $auxiliarU->assistantUnit_period = $period;
                        $auxiliarU->assistantUnit_charge = null;
                        if ($ventaCancelar->sales_money == session('generalParameters')->generalParameters_defaultMoney) {
                            $auxiliarU->assistantUnit_payment = $abonoUnidad->articlesCost_averageCost != null ? '-' . $abonoUnidad->articlesCost_averageCost : '0.00';
                        } else {
                            $auxiliarU->assistantUnit_payment = $abonoUnidad->articlesCost_averageCost != null ? '-' . $abonoUnidad->articlesCost_averageCost * $ventaCancelar->sales_typeChange : '0.00';
                        }
                        $auxiliarU->assistantUnit_chargeUnit = null;
                        $auxiliarU->assistantUnit_paymentUnit = '-' . (float)$articulo->salesDetails_inventoryAmount;
                        $auxiliarU->assistantUnit_apply = $articulo->salesDetails_apply;
                        $auxiliarU->assistantUnit_applyID =  $articulo->salesDetails_applyIncrement;
                        $auxiliarU->assistantUnit_canceled = 1;
                        $auxiliarU->asssistantUnit_costumer = $ventaCancelar->sales_customer;
                        $validar4 = $auxiliarU->save();

                        if ($validar4) {
                            $ventaCancelada = true;
                        } else {
                            $ventaCancelada = false;
                        }
                    }

                    // if ($articulo->salesDetails_type == 'Kit') {

                    //     $articuloDefault = CAT_ARTICLES::where('articles_key', '=', $articulo->salesDetails_article)->first();

                    //     $articulosKit = CAT_KIT_ARTICLES::where('kitArticles_articleID', '=', $articuloDefault->articles_id)->get();

                    //     $sumaCostoKit = 0;
                    //     foreach ($articulosKit as $articuloKit) {
                    //         $articuloKitDefault = CAT_ARTICLES::where('articles_key', '=', $articuloKit->kitArticles_article)->first();
                    //         if ($articuloKitDefault != null) {
                    //             $costoPromedioKit = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articuloKitDefault->articles_key)->where('articlesCost_branchKey', '=', $ventaCancelar->sales_branchOffice)->where('articlesCost_companieKey', '=', $ventaCancelar->sales_company)->where('articlesCost_depotKey', '=', $ventaCancelar->sales_depot)->first();

                    //             if ($costoPromedioKit != null) {
                    //                 $sumaCostoKit += $costoPromedioKit->articlesCost_averageCost * $articuloKit->kitArticles_cantidad;
                    //             }
                    //         }
                    //     }
                    //     if($ventaCancelar->sales_money == session('generalParameters')->generalParameters_defaultMoney){
                    //     $costo = $sumaCostoKit;
                    //     $importe = $articulo->salesDetails_inventoryAmount * $costo;
                    //     }else{
                    //     $costo = $sumaCostoKit * $ventaCancelar->sales_typeChange;
                    //     $importe = $articulo->salesDetails_inventoryAmount * $costo;
                    //     }

                    //     $auxiliarU = new PROC_ASSISTANT_UNITS();
                    //     $auxiliarU->assistantUnit_companieKey = $ventaCancelar->sales_company;
                    //     $auxiliarU->assistantUnit_branchKey = $ventaCancelar->sales_branchOffice;
                    //     $auxiliarU->assistantUnit_branch = 'Inv';
                    //     $auxiliarU->assistantUnit_movement = $ventaCancelar->sales_movement;
                    //     $auxiliarU->assistantUnit_movementID = $ventaCancelar->sales_movementID;
                    //     $auxiliarU->assistantUnit_module = 'Ventas';
                    //     $auxiliarU->assistantUnit_moduleID = $articulo->salesDetails_saleID;
                    //     if($ventaCancelar->sales_money != session('generalParameters')->generalParameters_defaultMoney){
                    //         $moneda = CONF_MONEY::where('money_key', '=',session('generalParameters')->generalParameters_defaultMoney)->first();
                    //         $auxiliarU->assistantUnit_money = $moneda->money_key;
                    //         $auxiliarU->assistantUnit_typeChange = $moneda->money_change;
                    //     }else{
                    //         $auxiliarU->assistantUnit_money = $ventaCancelar->sales_money;
                    //         $auxiliarU->assistantUnit_typeChange = $ventaCancelar->sales_typeChange;
                    //     }


                    //     $auxiliarU->assistantUnit_group = $articulo->salesDetails_depot;
                    //     $auxiliarU->assistantUnit_account = $articulo->salesDetails_article;
                    //     //ponemos fecha del ejercicio
                    //     $year = Carbon::now()->year;
                    //     //sacamos el periodo 
                    //     $period = Carbon::now()->month;
                    //     $auxiliarU->assistantUnit_year = $year;
                    //     $auxiliarU->assistantUnit_period = $period;
                    //     $auxiliarU->assistantUnit_charge = null;

                    //     $auxiliarU->assistantUnit_payment = '-'.$importe;


                    //     $auxiliarU->assistantUnit_chargeUnit = null;
                    //     $auxiliarU->assistantUnit_paymentUnit ='-'.(float)$articulo->salesDetails_inventoryAmount;
                    //     $auxiliarU->assistantUnit_apply = $articulo->salesDetails_apply;
                    //     $auxiliarU->assistantUnit_applyID =  $articulo->salesDetails_applyIncrement;
                    //     $auxiliarU->assistantUnit_canceled = 0;
                    //     $auxiliarU->asssistantUnit_costumer = $ventaCancelar->sales_customer;
                    //     $auxiliarU->save();

                    // }

                    if ($articulo->salesDetails_type == 'Kit') {

                        $articuloDefault = CAT_ARTICLES::where('articles_key', '=', $articulo->salesDetails_article)->first();

                        $articulosKit = CAT_KIT_ARTICLES::where('kitArticles_articleID', '=', $articuloDefault->articles_id)->get();

                        foreach ($articulosKit as $articuloKit) {
                            $articuloKitDefault = CAT_ARTICLES::where('articles_key', '=', $articuloKit->kitArticles_article)->first();
                            if ($articuloKitDefault != null  && $articuloKitDefault->articles_type != "Servicio") {
                                $costoPromedioKit = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articuloKitDefault->articles_key)->where('articlesCost_branchKey', '=', $ventaCancelar->sales_branchOffice)->where('articlesCost_companieKey', '=', $ventaCancelar->sales_company)->where('articlesCost_depotKey', '=', $ventaCancelar->sales_depot)->first();


                                $auxiliarU = new PROC_ASSISTANT_UNITS();
                                $auxiliarU->assistantUnit_companieKey = $ventaCancelar->sales_company;
                                $auxiliarU->assistantUnit_branchKey = $ventaCancelar->sales_branchOffice;
                                $auxiliarU->assistantUnit_branch = 'Inv';
                                $auxiliarU->assistantUnit_movement = $ventaCancelar->sales_movement;
                                $auxiliarU->assistantUnit_movementID = $ventaCancelar->sales_movementID;
                                $auxiliarU->assistantUnit_module = 'Ventas';
                                $auxiliarU->assistantUnit_moduleID = $articulo->salesDetails_saleID;
                                if ($ventaCancelar->sales_money != session('generalParameters')->generalParameters_defaultMoney) {
                                    $moneda = CONF_MONEY::where('money_key', '=', session('generalParameters')->generalParameters_defaultMoney)->first();
                                    $auxiliarU->assistantUnit_money = $moneda->money_key;
                                    $auxiliarU->assistantUnit_typeChange = $moneda->money_change;
                                } else {
                                    $auxiliarU->assistantUnit_money = $ventaCancelar->sales_money;
                                    $auxiliarU->assistantUnit_typeChange = $ventaCancelar->sales_typeChange;
                                }


                                $auxiliarU->assistantUnit_group = $articulo->salesDetails_depot;
                                $auxiliarU->assistantUnit_account = $articuloKitDefault->articles_key;
                                //ponemos fecha del ejercicio
                                $year = Carbon::now()->year;
                                //sacamos el periodo 
                                $period = Carbon::now()->month;
                                $auxiliarU->assistantUnit_year = $year;
                                $auxiliarU->assistantUnit_period = $period;
                                $auxiliarU->assistantUnit_charge = null;


                                if ($ventaCancelar->sales_money == session('generalParameters')->generalParameters_defaultMoney) {
                                    $auxiliarU->assistantUnit_payment =  $costoPromedioKit->articlesCost_averageCost != null ? '-' . ($costoPromedioKit->articlesCost_averageCost * (float)$articuloKit->kitArticles_cantidad) : '0.00';
                                } else {
                                    $auxiliarU->assistantUnit_payment = $costoPromedioKit->articlesCost_averageCost != null ? (($costoPromedioKit->articlesCost_averageCost * (float)$articuloKit->kitArticles_cantidad) * $ventaCancelar->sales_typeChange) : '0.00';
                                }


                                $auxiliarU->assistantUnit_chargeUnit = null;
                                $auxiliarU->assistantUnit_paymentUnit = '-' . ((float)$articuloKit->kitArticles_cantidad * (float)$articulo->salesDetails_inventoryAmount);
                                $auxiliarU->assistantUnit_apply = $articulo->salesDetails_apply;
                                $auxiliarU->assistantUnit_applyID =  $articulo->salesDetails_applyIncrement;
                                $auxiliarU->assistantUnit_canceled = 0;
                                $auxiliarU->asssistantUnit_costumer = $ventaCancelar->sales_customer;
                                $auxiliarU->save();
                            }
                        }
                    }

                    if ($articulo['salesDetails_type'] !== "Servicio" && $articulo['salesDetails_type'] !== "Kit") {
                        $cantidad = $articulo->salesDetails_inventoryAmount;
                        $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->salesDetails_article)->where('articlesInv_depot', '=', $ventaCancelar->sales_depot)->first();


                        if ($inventario != null) {
                            $inventario->articlesInv_inventory = $inventario->articlesInv_inventory + $cantidad;
                            $validar5 = $inventario->update();
                            if ($validar5) {
                                $ventaCancelada = true;
                            } else {
                                $ventaCancelada = false;
                            }
                        }
                    }

                    if ($articulo['salesDetails_type'] == "Kit") {
                        $articulosKit = PROC_KIT_ARTICLES::where('procKit_article', $articulo->salesDetails_article)->where('procKit_saleID', $ventaCancelar->sales_id)->get();
                        foreach ($articulosKit as $articuloKit) {

                            $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articuloKit->procKit_articleID)->where('articlesInv_depot', '=', $ventaCancelar->sales_depot)->first();

                            if ($inventario != null) {
                                $inventario->articlesInv_inventory = $inventario->articlesInv_inventory + $articuloKit->procKit_cantidad;
                                $inventario->update();
                            }

                            if ($articuloKit->procKit_tipo == 'Serie') {
                                $series = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_saleID', '=', $articuloKit->procKit_saleID)->where('delSeriesMov2_article', '=', $articuloKit->procKit_articleID)->where('delSeriesMov2_articleID', '=', $articuloKit->procKit_article)->get();
                                if (count($series) > 0) {
                                    foreach ($series as $serie) {
                                        $serie->delSeriesMov2_cancelled = 1;
                                        $serie->update();
                                        // $serie->delete();

                                        $proc_lot_series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', $ventaCancelar->sales_company)->where('lotSeries_branchKey', '=', $ventaCancelar->sales_branchOffice)->where('lotSeries_lotSerie', "=", $serie->delSeriesMov2_lotSerie)->first();


                                        if ($proc_lot_series !== null) {
                                            $proc_lot_series->lotSeries_delete = 0;
                                            $proc_lot_series->update();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $auxiliar = new PROC_ASSISTANT();

                $auxiliar->assistant_companieKey = $ventaCancelar->sales_company;
                $auxiliar->assistant_branchKey = $ventaCancelar->sales_branchOffice;
                $auxiliar->assistant_branch = 'CxC';
                $auxiliar->assistant_movement = $ventaCancelar->sales_movement;
                $auxiliar->assistant_movementID = $ventaCancelar->sales_movementID;
                $auxiliar->assistant_module = 'CxC';

                $auxiliar->assistant_moduleID = $cxc->accountsReceivable_id;
                $auxiliar->assistant_money = $ventaCancelar->sales_money;
                $auxiliar->assistant_typeChange = $ventaCancelar->sales_typeChange;
                $auxiliar->assistant_account = $ventaCancelar->sales_customer;

                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;


                $auxiliar->assistant_year = $year;
                $auxiliar->assistant_period = $period;
                $auxiliar->assistant_charge = '-' . $ventaCancelar->sales_total;
                $auxiliar->assistant_payment = null;
                $auxiliar->assistant_apply = $ventaCancelar->sales_movement;
                $auxiliar->assistant_applyID = $ventaCancelar->sales_movementID;
                $auxiliar->assistant_canceled = 1;
                $auxiliar->assistant_reference = $ventaCancelar->sales_reference;


                $validar6 = $auxiliar->save();
                if ($validar6) {
                    $ventaCancelada = true;
                } else {
                    $ventaCancelada = false;
                }

                $flujo->movementFlow_cancelled = 1;
                $validar7 = $flujo->update();
                if ($validar7) {
                    $ventaCancelada = true;
                } else {
                    $ventaCancelada = false;
                }

                $ventaCancelar->sales_status = $this->estatus[3];
                if ($ventaCancelada == true) {
                    $ventaCredito = $ventaCancelar->update();
                } else {
                    $ventaCredito = false;
                }
            } catch (\Exception $e) {
                dd($e);
                $ventaCredito = false;
                $message = $e->getMessage();
            }
            // dd($ventaCancelar, $flujo, $cxc, $cxcp, $saldo);
        }

        if ($ventaCancelar->sales_status == $this->estatus[2] && $ventaCancelar->sales_movement == 'Factura' && $ventaCancelar->sales_typeCondition == 'Contado') {

            try {
                $ventaCredito = true;
                $ventaCancelada = false;


                $cambios = PROC_SALES_PAYMENT::where('salesPayment_saleID', '=', $ventaCancelar->sales_id)->get();

                $ingreso = false;
                $deposito = false;
                $solicitud = false;
                $tesoreriaArray = [];
                $cuenta = '';
                foreach ($cambios as $camb) {
                    $cuenta = $camb->salesPayment_moneyAccount;
                    if ($camb->salesPayment_moneyAccountType == 'Caja') {
                        $valorCambio = (float)$camb->salesPayment_Change;
                        if ($camb->getFormaCambio->formsPayment_sat == '01') {
                            $ingreso = true;
                        } else {
                            $solicitud = true;
                        }
                    } else {
                        $deposito = true;
                    }
                    // dd($ingreso, $deposito, $solicitud);
                    if ($camb->salesPayment_paymentMethod1 > 0) {
                        $tesoreriaArray['salesPayment_paymentMethod1'] = $camb->salesPayment_paymentMethod1 . '-' . $camb->salesPayment_amount1 . '-' . $camb->salesPayment_moneyAccount;
                    }

                    if ($camb->salesPayment_paymentMethod2 > 0) {
                        $tesoreriaArray['salesPayment_paymentMethod2'] = $camb->salesPayment_paymentMethod2 . '-' . $camb->salesPayment_amount2 . '-' . $camb->salesPayment_moneyAccount;
                    }

                    if ($camb->salesPayment_paymentMethod3 > 0) {
                        $tesoreriaArray['salesPayment_paymentMethod3'] = $camb->salesPayment_paymentMethod3 . '-' . $camb->salesPayment_amount3 . '-' . $camb->salesPayment_moneyAccount;
                    }

                    if ($ingreso == true && $valorCambio > 0) {
                        //buscamos el egreso con respecto al cambio
                        $egreso = PROC_TREASURY::where('treasuries_movement', '=', 'Egreso')->where('treasuries_origin', '=', $ventaCancelar->sales_movement)->where('treasuries_originID', '=', $ventaCancelar->sales_movementID)->where('treasuries_branchOffice', '=', $ventaCancelar->sales_branchOffice)->first();

                        if ($egreso != null) {
                            $egreso->treasuries_status = $this->estatus[3];
                            $validar = $egreso->update();
                            if ($validar) {
                                $ventaCancelada = true;
                            } else {
                                $ventaCancelada = false;
                            }
                        }

                        //buscamos el movimiento generado
                        $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $ventaCancelar->sales_id)->where('movementFlow_moduleOrigin', '=', 'Ventas')->where('movementFlow_destinityID', '=', $egreso->treasuries_id)->where('movementFlow_moduleDestiny', '=', 'Din')->where('movementFlow_movementDestinity', '=', 'Egreso')->where('movementFlow_branch', '=', $ventaCancelar->sales_branchOffice)->first();

                        if ($flujo != null) {
                            $flujo->movementFlow_cancelled = 1;
                            $validar2 = $flujo->update();
                            if ($validar2) {
                                $ventaCancelada = true;
                            } else {
                                $ventaCancelada = false;
                            }
                        }

                        $auxiliar = new PROC_ASSISTANT();

                        $auxiliar->assistant_companieKey = $egreso->treasuries_company;
                        $auxiliar->assistant_branchKey = $egreso->treasuries_branchOffice;
                        $auxiliar->assistant_branch = 'Din';
                        $auxiliar->assistant_movement = $egreso->treasuries_movement;
                        $auxiliar->assistant_movementID = $egreso->treasuries_movementID;
                        $auxiliar->assistant_module = 'Din';

                        $auxiliar->assistant_moduleID = $egreso->treasuries_id;
                        $auxiliar->assistant_money = $egreso->treasuries_money;
                        $auxiliar->assistant_typeChange = $egreso->treasuries_typeChange;
                        $auxiliar->assistant_account = $egreso->treasuries_moneyAccount;

                        //ponemos fecha del ejercicio
                        $year = Carbon::now()->year;
                        //sacamos el periodo 
                        $period = Carbon::now()->month;


                        $auxiliar->assistant_year = $year;
                        $auxiliar->assistant_period = $period;

                        $auxiliar->assistant_charge = null;
                        $auxiliar->assistant_payment = '-' . $egreso->treasuries_total;


                        $auxiliar->assistant_apply = $egreso->treasuries_movement;
                        $auxiliar->assistant_applyID = $egreso->treasuries_movementID;
                        $auxiliar->assistant_canceled = 1;
                        $auxiliar->assistant_reference = $egreso->treasuries_reference;
                        $validar3 = $auxiliar->save();
                        if ($validar3) {
                            $ventaCancelada = true;
                        } else {
                            $ventaCancelada = false;
                        }
                    }
                }

                foreach ($tesoreriaArray as $key => $value) {

                    $tesoreo = explode('-', $value);
                    // dd($ingreso, $deposito, $solicitud);
                    if ($ingreso == true) {
                        $ingreso = PROC_TREASURY::where('treasuries_movement', '=', 'Ingreso')->where('treasuries_origin', '=', $ventaCancelar->sales_movement)->where('treasuries_originID', '=', $ventaCancelar->sales_movementID)->where('treasuries_branchOffice', '=', $ventaCancelar->sales_branchOffice)->first();

                        if ($ingreso != null) {
                            $ingreso->treasuries_status = $this->estatus[3];
                            $validar4 = $ingreso->update();
                            if ($validar4) {
                                $ventaCancelada = true;
                            } else {
                                $ventaCancelada = false;
                            }
                        }

                        $flujo2 = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $ventaCancelar->sales_id)->where('movementFlow_moduleOrigin', '=', 'Ventas')->where('movementFlow_destinityID', '=', $ingreso->treasuries_id)->where('movementFlow_moduleDestiny', '=', 'Din')->where('movementFlow_movementDestinity', '=', 'Ingreso')->where('movementFlow_branch', '=', $ventaCancelar->sales_branchOffice)->first();

                        if ($flujo2 != null) {
                            $flujo2->movementFlow_cancelled = 1;
                            $validar5 = $flujo2->update();
                            if ($validar5) {
                                $ventaCancelada = true;
                            } else {
                                $ventaCancelada = false;
                            }
                        }


                        $auxiliar = new PROC_ASSISTANT();

                        $auxiliar->assistant_companieKey = $ingreso->treasuries_company;
                        $auxiliar->assistant_branchKey = $ingreso->treasuries_branchOffice;
                        $auxiliar->assistant_branch = 'Din';
                        $auxiliar->assistant_movement = $ingreso->treasuries_movement;
                        $auxiliar->assistant_movementID = $ingreso->treasuries_movementID;
                        $auxiliar->assistant_module = 'Din';

                        $auxiliar->assistant_moduleID = $ingreso->treasuries_id;
                        $auxiliar->assistant_money = $ingreso->treasuries_money;
                        $auxiliar->assistant_typeChange = $ingreso->treasuries_typeChange;
                        $auxiliar->assistant_account = $ingreso->treasuries_moneyAccount;

                        //ponemos fecha del ejercicio
                        $year = Carbon::now()->year;
                        //sacamos el periodo 
                        $period = Carbon::now()->month;


                        $auxiliar->assistant_year = $year;
                        $auxiliar->assistant_period = $period;

                        $auxiliar->assistant_charge = '-' . $ingreso->treasuries_total;
                        $auxiliar->assistant_payment = null;


                        $auxiliar->assistant_apply = $ingreso->treasuries_movement;
                        $auxiliar->assistant_applyID = $ingreso->treasuries_movementID;
                        $auxiliar->assistant_canceled = 1;
                        $auxiliar->assistant_reference = $ingreso->treasuries_reference;
                        $validar6 = $auxiliar->save();
                        if ($validar6) {
                            $ventaCancelada = true;
                        } else {
                            $ventaCancelada = false;
                        }
                    }


                    // if ($deposito == true) {

                    //     $socDeposito = PROC_TREASURY::where('treasuries_movement', '=', 'Solicitud Depósito')->where('treasuries_origin', '=', $ventaCancelar->sales_movement)->where('treasuries_originID', '=', $ventaCancelar->sales_movementID)->where('treasuries_branchOffice', '=', $ventaCancelar->sales_branchOffice)->first();

                    //     $deposito2 = PROC_TREASURY::where('treasuries_movement', '=', 'Depósito')->where('treasuries_origin', '=', $socDeposito->treasuries_movement)->where('treasuries_originID', '=', $socDeposito->treasuries_movementID)->where('treasuries_branchOffice', '=', $socDeposito->treasuries_branchOffice)->first();
                    //     // dd($deposito2);

                    //     if ($deposito2 != null) {
                    //         $deposito2->treasuries_status = $this->estatus[3];
                    //         $validar16 = $deposito2->update();
                    //         if ($validar16) {
                    //             $ventaCancelada = true;
                    //         } else {
                    //             $ventaCancelada = false;
                    //         }
                    //     }

                    //     if ($socDeposito != null) {
                    //         $socDeposito->treasuries_status = $this->estatus[3];
                    //         $validar7 = $socDeposito->update();
                    //         if ($validar7) {
                    //             $ventaCancelada = true;
                    //         } else {
                    //             $ventaCancelada = false;
                    //         }


                    //         $flujo3 = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=',
                    //             $socDeposito->treasuries_id)->where('movementFlow_moduleOrigin', '=', 'Din')->where('movementFlow_destinityID', '=', $deposito2->treasuries_id)->where('movementFlow_moduleDestiny', '=', 'Din')->where('movementFlow_movementDestinity', '=', 'Depósito')->where('movementFlow_branch', '=', $ventaCancelar->sales_branchOffice)->first();
                       
                    //             // dd($flujo3);
                    //     // dd($socDeposito, $deposito2, $flujo3);


                    //         if ($flujo3 != null) {
                    //             $flujo3->movementFlow_cancelled = 1;
                    //             $validar17 = $flujo3->update();
                    //             if ($validar17) {
                    //                 $ventaCancelada = true;
                    //             } else {
                    //                 $ventaCancelada = false;
                    //             }
                    //         }

                    
                    //         $auxiliar = new PROC_ASSISTANT();

                    //         $auxiliar->assistant_companieKey = $deposito2->treasuries_company;
                    //         $auxiliar->assistant_branchKey = $deposito2->treasuries_branchOffice;
                    //         $auxiliar->assistant_branch = 'Din';
                    //         $auxiliar->assistant_movement = $deposito2->treasuries_movement;
                    //         $auxiliar->assistant_movementID = $deposito2->treasuries_movementID;
                    //         $auxiliar->assistant_module = 'Din';

                    //         $auxiliar->assistant_moduleID = $deposito2->treasuries_id;
                    //         $auxiliar->assistant_money = $deposito2->treasuries_money;
                    //         $auxiliar->assistant_typeChange = $deposito2->treasuries_typeChange;
                    //         $auxiliar->assistant_account = $deposito2->treasuries_moneyAccount;

                    //         //ponemos fecha del ejercicio
                    //         $year = Carbon::now()->year;
                    //         //sacamos el periodo
                    //         $period = Carbon::now()->month;


                    //         $auxiliar->assistant_year = $year;
                    //         $auxiliar->assistant_period = $period;

                    //         $auxiliar->assistant_charge = '-' . $deposito2->treasuries_total;
                    //         $auxiliar->assistant_payment = null;


                    //         $auxiliar->assistant_apply = $deposito2->treasuries_movement;
                    //         $auxiliar->assistant_applyID = $deposito2->treasuries_movementID;
                    //         $auxiliar->assistant_canceled = 1;
                    //         $auxiliar->assistant_reference = $deposito2->treasuries_reference;
                    //         $validar19 = $auxiliar->save();
                    //         if ($validar19) {
                    //             $ventaCancelada = true;
                    //         } else {
                    //             $ventaCancelada = false;
                    //         }
                    //     }

                    //     $flujo2 = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $ventaCancelar->sales_id)->where('movementFlow_moduleOrigin', '=', 'Ventas')->where('movementFlow_destinityID', '=', $socDeposito->treasuries_id)->where('movementFlow_moduleDestiny', '=', 'Din')->where('movementFlow_movementDestinity', '=', 'Solicitud Depósito')->where('movementFlow_branch', '=', $ventaCancelar->sales_branchOffice)->first();

                    //     if ($flujo2 != null) {
                    //         $flujo2->movementFlow_cancelled = 1;
                    //         $validar8 = $flujo2->update();
                    //         if ($validar8) {
                    //             $ventaCancelada = true;
                    //         } else {
                    //             $ventaCancelada = false;
                    //         }
                    //     }
                    // }
                    if ($deposito == true) {

                        $socDeposito = PROC_TREASURY::where('treasuries_movement', '=', 'Solicitud Depósito')->where('treasuries_origin', '=', $ventaCancelar->sales_movement)->where('treasuries_originID', '=', $ventaCancelar->sales_movementID)->where('treasuries_branchOffice', '=', $ventaCancelar->sales_branchOffice)->first();

                        if ($socDeposito != null) {
                            $socDeposito->treasuries_status = $this->estatus[3];
                            $validar7 = $socDeposito->update();
                            if ($validar7) {
                                $ventaCancelada = true;
                            } else {
                                $ventaCancelada = false;
                            }
                        }

                        $flujo2 = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $ventaCancelar->sales_id)->where('movementFlow_moduleOrigin', '=', 'Ventas')->where('movementFlow_destinityID', '=', $socDeposito->treasuries_id)->where('movementFlow_moduleDestiny', '=', 'Din')->where('movementFlow_movementDestinity', '=', 'Solicitud Depósito')->where('movementFlow_branch', '=', $ventaCancelar->sales_branchOffice)->first();

                        if ($flujo2 != null) {
                            $flujo2->movementFlow_cancelled = 1;
                            $validar8 = $flujo2->update();
                            if ($validar8) {
                                $ventaCancelada = true;
                            } else {
                                $ventaCancelada = false;
                            }
                        }
                    }
                    if ($solicitud == true) {

                        $socDeposito = PROC_TREASURY::where('treasuries_movement', '=', 'Solicitud Depósito')->where('treasuries_origin', '=', $ventaCancelar->sales_movement)->where('treasuries_originID', '=', $ventaCancelar->sales_movementID)->where('treasuries_branchOffice', '=', $ventaCancelar->sales_branchOffice)->first();
                        // dd($socDeposito);


                        $deposito2 = PROC_TREASURY::where('treasuries_movement', '=', 'Depósito')->where('treasuries_origin', '=', $socDeposito->treasuries_movement)->where('treasuries_originID', '=', $socDeposito->treasuries_movementID)->where('treasuries_branchOffice', '=', $socDeposito->treasuries_branchOffice)->first();
                        // dd($deposito2);

                        if ($deposito2 != null) {
                            $deposito2->treasuries_status = $this->estatus[3];
                            $validar9 = $deposito2->update();
                            if ($validar9) {
                                $ventaCancelada = true;
                            } else {
                                $ventaCancelada = false;
                            }
                        }

                        if ($socDeposito != null) {
                            $socDeposito->treasuries_status = $this->estatus[3];
                            $validar7 = $socDeposito->update();
                            if ($validar7) {
                                $ventaCancelada = true;
                            } else {
                                $ventaCancelada = false;
                            }
                        }

                        $flujo2 = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $ventaCancelar->sales_id)->where('movementFlow_moduleOrigin', '=', 'Ventas')->where('movementFlow_destinityID', '=', $socDeposito->treasuries_id)->where('movementFlow_moduleDestiny', '=', 'Din')->where('movementFlow_movementDestinity', '=', 'Solicitud Depósito')->where('movementFlow_branch', '=', $ventaCancelar->sales_branchOffice)->first();

                        if ($flujo2 != null) {
                            $flujo2->movementFlow_cancelled = 1;
                            $validar8 = $flujo2->update();
                            if ($validar8) {
                                $ventaCancelada = true;
                            } else {
                                $ventaCancelada = false;
                            }
                        }

                        $auxiliar = new PROC_ASSISTANT();

                        $auxiliar->assistant_companieKey = $deposito2->treasuries_company;
                        $auxiliar->assistant_branchKey = $deposito2->treasuries_branchOffice;
                        $auxiliar->assistant_branch = 'Din';
                        $auxiliar->assistant_movement = $deposito2->treasuries_movement;
                        $auxiliar->assistant_movementID = $deposito2->treasuries_movementID;
                        $auxiliar->assistant_module = 'Din';

                        $auxiliar->assistant_moduleID = $deposito2->treasuries_id;
                        $auxiliar->assistant_money = $deposito2->treasuries_money;
                        $auxiliar->assistant_typeChange = $deposito2->treasuries_typeChange;
                        $auxiliar->assistant_account = $deposito2->treasuries_moneyAccount;

                        //ponemos fecha del ejercicio
                        $year = Carbon::now()->year;
                        //sacamos el periodo
                        $period = Carbon::now()->month;


                        $auxiliar->assistant_year = $year;
                        $auxiliar->assistant_period = $period;

                        $auxiliar->assistant_charge = '-' . $deposito2->treasuries_total;
                        $auxiliar->assistant_payment = null;


                        $auxiliar->assistant_apply = $deposito2->treasuries_movement;
                        $auxiliar->assistant_applyID = $deposito2->treasuries_movementID;
                        $auxiliar->assistant_canceled = 1;
                        $auxiliar->assistant_reference = $deposito2->treasuries_reference;
                        $validar6 = $auxiliar->save();
                        if ($validar6) {
                            $ventaCancelada = true;
                        } else {
                            $ventaCancelada = false;
                        }
                    }
                }

                if($ingreso == true){
                    $saldo = PROC_BALANCE::where('balance_account', '=', $cuenta)->where('balance_branchKey', '=', $ventaCancelar->sales_branchOffice)->where('balance_money', '=', $ventaCancelar->sales_money)->first();

                    if ($saldo != null) {
                        $saldo->balance_balance = $saldo->balance_balance - $ventaCancelar->sales_total;
                        $saldo->balance_reconcile = $saldo->balance_balance;
                        $validar9 = $saldo->update();
                        if ($validar9) {
                            $ventaCancelada = true;
                        } else {
                            $ventaCancelada = false;
                        }
                    }
    
                    $saldo2 = PROC_MONEY_ACCOUNTS_BALANCE::where('moneyAccountsBalance_moneyAccount', $cuenta)->where('moneyAccountsBalance_status', 'Alta')->where('moneyAccountsBalance_money', $ventaCancelar->sales_money)->where('moneyAccountsBalance_company', $ventaCancelar->sales_company)->first();
    
                    if ($saldo2 != null) {
                        $saldo2->moneyAccountsBalance_balance = $saldo2->moneyAccountsBalance_balance - $ventaCancelar->sales_total;
                        $validar10 = $saldo2->update();
                        if ($validar10) {
                            $ventaCancelada = true;
                        } else {
                            $ventaCancelada = false;
                        }
                    }
                }
               

                $articulos = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $ventaCancelar->sales_id)->where('salesDetails_branchOffice', '=', $ventaCancelar->sales_branchOffice)->get();

                foreach ($articulos as $articulo) {

                    if ($articulo->salesDetails_type == 'Serie') {
                        $series = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_saleID', '=', $articulo->salesDetails_saleID)->where('delSeriesMov2_articleID', '=', $articulo->salesDetails_id)->get();

                        if ($ventaCancelar->sales_origin == "Pedido") {
                            //Buscamos el origen de la factura para buscar las series que fueron seleccionadas
                            $ventaOrigen = PROC_SALES::WHERE('sales_movement', '=', 'Pedido')->WHERE('sales_movementID', '=', $ventaCancelar->sales_originID)->WHERE('sales_company', '=',  session('company')->companies_key)->WHERE('sales_branchOffice', '=', session('sucursal')->branchOffices_key)->first();

                            $seriesOrigen = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_saleID', '=', $ventaOrigen->sales_id)->where('delSeriesMov2_article', '=', $articulo->salesDetails_article)->get();

                            foreach ($seriesOrigen  as $seriesO) {
                                $seriesO->delSeriesMov2_affected = 0;
                                $seriesO->update();
                            }
                        }

                        if (
                            $series != null
                        ) {
                            foreach ($series as $serie) {
                                $serie->delSeriesMov2_cancelled = 1;
                                $serie->update();

                                $proc_lot_series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', $ventaCancelar->sales_company)->where('lotSeries_branchKey', '=', $ventaCancelar->sales_branchOffice)->where('lotSeries_lotSerie', "=", $serie->delSeriesMov2_lotSerie)->first();


                                if ($proc_lot_series !== null) {
                                    $proc_lot_series->lotSeries_delete = 0;
                                    $proc_lot_series->update();
                                }
                                // $serie->delete();
                            }
                        }
                    }
                    if ($articulo->salesDetails_type != 'Kit' && $articulo->salesDetails_type != 'Servicio') {
                        $costo = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articulo->salesDetails_article)->where('articlesCost_branchKey', '=', $articulo->salesDetails_branchOffice)->first();

                        if ($costo === null) {
                            $costo = $articulo->salesDetails_unitCost;
                        } else {
                            $costo = $costo->articlesCost_lastCost === null || ".0000" ? $costo->articlesCost_averageCost : $costo->articlesCost_lastCost;
                        }

                        $importe = $articulo->salesDetails_inventoryAmount * $costo;
                        //agregar datos a aux
                        $auxiliarU = new PROC_ASSISTANT_UNITS();
                        $auxiliarU->assistantUnit_companieKey = $ventaCancelar->sales_company;
                        $auxiliarU->assistantUnit_branchKey = $ventaCancelar->sales_branchOffice;
                        $auxiliarU->assistantUnit_branch = 'Inv';
                        $auxiliarU->assistantUnit_movement = $ventaCancelar->sales_movement;
                        $auxiliarU->assistantUnit_movementID = $ventaCancelar->sales_movementID;
                        $auxiliarU->assistantUnit_module = 'Ventas';
                        $auxiliarU->assistantUnit_moduleID = $articulo->salesDetails_saleID;
                        $auxiliarU->assistantUnit_money = $ventaCancelar->sales_money;
                        $auxiliarU->assistantUnit_typeChange = $ventaCancelar->sales_typeChange;
                        $auxiliarU->assistantUnit_group = $articulo->salesDetails_depot;
                        $auxiliarU->assistantUnit_account = $articulo->salesDetails_article;
                        //ponemos fecha del ejercicio
                        $year = Carbon::now()->year;
                        //sacamos el periodo 
                        $period = Carbon::now()->month;
                        $auxiliarU->assistantUnit_year = $year;
                        $auxiliarU->assistantUnit_period = $period;
                        $auxiliarU->assistantUnit_charge = null;

                        $abonoUnidad = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articulo->salesDetails_article)->where('articlesCost_branchKey', '=', $articulo->salesDetails_branchOffice)->where('articlesCost_companieKey', '=', $ventaCancelar->sales_company)->where('articlesCost_depotKey', '=', $ventaCancelar->sales_depot)->first();
                        if ($ventaCancelar->sales_money == session('generalParameters')->generalParameters_defaultMoney) {
                            $auxiliarU->assistantUnit_payment = $abonoUnidad->articlesCost_averageCost != null ? '-' . $abonoUnidad->articlesCost_averageCost : '0.00';
                        } else {
                            $auxiliarU->assistantUnit_payment = $abonoUnidad->articlesCost_averageCost != null ? '-' . $abonoUnidad->articlesCost_averageCost * $ventaCancelar->sales_typeChange : '0.00';
                        }
                        $auxiliarU->assistantUnit_chargeUnit = null;
                        $auxiliarU->assistantUnit_paymentUnit = '-' . (float)$articulo->salesDetails_inventoryAmount;
                        $auxiliarU->assistantUnit_apply = $articulo->salesDetails_apply;
                        $auxiliarU->assistantUnit_applyID =  $articulo->salesDetails_applyIncrement;
                        $auxiliarU->assistantUnit_canceled = 1;
                        $auxiliarU->asssistantUnit_costumer = $ventaCancelar->sales_customer;
                        $validar11 = $auxiliarU->save();
                        if ($validar11) {
                            $ventaCancelada = true;
                        } else {
                            $ventaCancelada = false;
                        }
                    }

                    // if ($articulo->salesDetails_type == 'Kit') {

                    //     $articuloDefault = CAT_ARTICLES::where('articles_key', '=', $articulo->salesDetails_article)->first();

                    //     $articulosKit = CAT_KIT_ARTICLES::where('kitArticles_articleID', '=', $articuloDefault->articles_id)->get();

                    //     $sumaCostoKit = 0;
                    //     foreach ($articulosKit as $articuloKit) {
                    //         $articuloKitDefault = CAT_ARTICLES::where('articles_key', '=', $articuloKit->kitArticles_article)->first();
                    //         if ($articuloKitDefault != null) {
                    //             $costoPromedioKit = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articuloKitDefault->articles_key)->where('articlesCost_branchKey', '=', $ventaCancelar->sales_branchOffice)->where('articlesCost_companieKey', '=', $ventaCancelar->sales_company)->where('articlesCost_depotKey', '=', $ventaCancelar->sales_depot)->first();

                    //             if ($costoPromedioKit != null) {
                    //                 $sumaCostoKit += $costoPromedioKit->articlesCost_averageCost * $articuloKit->kitArticles_cantidad;
                    //             }
                    //         }
                    //     }
                    //     if($ventaCancelar->sales_money == session('generalParameters')->generalParameters_defaultMoney){
                    //     $costo = $sumaCostoKit;
                    //     $importe = $articulo->salesDetails_inventoryAmount * $costo;
                    //     }else{
                    //     $costo = $sumaCostoKit * $ventaCancelar->sales_typeChange;
                    //     $importe = $articulo->salesDetails_inventoryAmount * $costo;
                    //     }

                    //     $auxiliarU = new PROC_ASSISTANT_UNITS();
                    //     $auxiliarU->assistantUnit_companieKey = $ventaCancelar->sales_company;
                    //     $auxiliarU->assistantUnit_branchKey = $ventaCancelar->sales_branchOffice;
                    //     $auxiliarU->assistantUnit_branch = 'Inv';
                    //     $auxiliarU->assistantUnit_movement = $ventaCancelar->sales_movement;
                    //     $auxiliarU->assistantUnit_movementID = $ventaCancelar->sales_movementID;
                    //     $auxiliarU->assistantUnit_module = 'Ventas';
                    //     $auxiliarU->assistantUnit_moduleID = $articulo->salesDetails_saleID;
                    //     if($ventaCancelar->sales_money != session('generalParameters')->generalParameters_defaultMoney){
                    //         $moneda = CONF_MONEY::where('money_key', '=',session('generalParameters')->generalParameters_defaultMoney)->first();
                    //         $auxiliarU->assistantUnit_money = $moneda->money_key;
                    //         $auxiliarU->assistantUnit_typeChange = $moneda->money_change;
                    //     }else{
                    //         $auxiliarU->assistantUnit_money = $ventaCancelar->sales_money;
                    //         $auxiliarU->assistantUnit_typeChange = $ventaCancelar->sales_typeChange;
                    //     }


                    //     $auxiliarU->assistantUnit_group = $articulo->salesDetails_depot;
                    //     $auxiliarU->assistantUnit_account = $articulo->salesDetails_article;
                    //     //ponemos fecha del ejercicio
                    //     $year = Carbon::now()->year;
                    //     //sacamos el periodo 
                    //     $period = Carbon::now()->month;
                    //     $auxiliarU->assistantUnit_year = $year;
                    //     $auxiliarU->assistantUnit_period = $period;
                    //     $auxiliarU->assistantUnit_charge = null;

                    //     $auxiliarU->assistantUnit_payment = '-'.$importe;


                    //     $auxiliarU->assistantUnit_chargeUnit = null;
                    //     $auxiliarU->assistantUnit_paymentUnit ='-'.(float)$articulo->salesDetails_inventoryAmount;
                    //     $auxiliarU->assistantUnit_apply = $articulo->salesDetails_apply;
                    //     $auxiliarU->assistantUnit_applyID =  $articulo->salesDetails_applyIncrement;
                    //     $auxiliarU->assistantUnit_canceled = 0;
                    //     $auxiliarU->asssistantUnit_costumer = $ventaCancelar->sales_customer;
                    //     $auxiliarU->save();

                    // }

                    if ($articulo->salesDetails_type == 'Kit') {

                        $articuloDefault = CAT_ARTICLES::where('articles_key', '=', $articulo->salesDetails_article)->first();

                        $articulosKit = CAT_KIT_ARTICLES::where('kitArticles_articleID', '=', $articuloDefault->articles_id)->get();

                        foreach ($articulosKit as $articuloKit) {
                            $articuloKitDefault = CAT_ARTICLES::where('articles_key', '=', $articuloKit->kitArticles_article)->first();
                            if ($articuloKitDefault != null  && $articuloKitDefault->articles_type != "Servicio") {
                                $costoPromedioKit = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articuloKitDefault->articles_key)->where('articlesCost_branchKey', '=', $ventaCancelar->sales_branchOffice)->where('articlesCost_companieKey', '=', $ventaCancelar->sales_company)->where('articlesCost_depotKey', '=', $ventaCancelar->sales_depot)->first();


                                $auxiliarU = new PROC_ASSISTANT_UNITS();
                                $auxiliarU->assistantUnit_companieKey = $ventaCancelar->sales_company;
                                $auxiliarU->assistantUnit_branchKey = $ventaCancelar->sales_branchOffice;
                                $auxiliarU->assistantUnit_branch = 'Inv';
                                $auxiliarU->assistantUnit_movement = $ventaCancelar->sales_movement;
                                $auxiliarU->assistantUnit_movementID = $ventaCancelar->sales_movementID;
                                $auxiliarU->assistantUnit_module = 'Ventas';
                                $auxiliarU->assistantUnit_moduleID = $articulo->salesDetails_saleID;
                                if ($ventaCancelar->sales_money != session('generalParameters')->generalParameters_defaultMoney) {
                                    $moneda = CONF_MONEY::where('money_key', '=', session('generalParameters')->generalParameters_defaultMoney)->first();
                                    $auxiliarU->assistantUnit_money = $moneda->money_key;
                                    $auxiliarU->assistantUnit_typeChange = $moneda->money_change;
                                } else {
                                    $auxiliarU->assistantUnit_money = $ventaCancelar->sales_money;
                                    $auxiliarU->assistantUnit_typeChange = $ventaCancelar->sales_typeChange;
                                }


                                $auxiliarU->assistantUnit_group = $articulo->salesDetails_depot;
                                $auxiliarU->assistantUnit_account = $articuloKitDefault->articles_key;
                                //ponemos fecha del ejercicio
                                $year = Carbon::now()->year;
                                //sacamos el periodo 
                                $period = Carbon::now()->month;
                                $auxiliarU->assistantUnit_year = $year;
                                $auxiliarU->assistantUnit_period = $period;
                                $auxiliarU->assistantUnit_charge = null;


                                if ($ventaCancelar->sales_money == session('generalParameters')->generalParameters_defaultMoney) {
                                    $auxiliarU->assistantUnit_payment =  $costoPromedioKit->articlesCost_averageCost != null ? '-' . ($costoPromedioKit->articlesCost_averageCost * (float)$articuloKit->kitArticles_cantidad) : '0.00';
                                } else {
                                    $auxiliarU->assistantUnit_payment = $costoPromedioKit->articlesCost_averageCost != null ? (($costoPromedioKit->articlesCost_averageCost * (float)$articuloKit->kitArticles_cantidad) * $ventaCancelar->sales_typeChange) : '0.00';
                                }


                                $auxiliarU->assistantUnit_chargeUnit = null;
                                $auxiliarU->assistantUnit_paymentUnit = '-' . ((float)$articuloKit->kitArticles_cantidad * (float)$articulo->salesDetails_inventoryAmount);
                                $auxiliarU->assistantUnit_apply = $articulo->salesDetails_apply;
                                $auxiliarU->assistantUnit_applyID =  $articulo->salesDetails_applyIncrement;
                                $auxiliarU->assistantUnit_canceled = 0;
                                $auxiliarU->asssistantUnit_costumer = $ventaCancelar->sales_customer;
                                $auxiliarU->save();
                            }
                        }
                    }

                    if ($articulo['salesDetails_type'] !== "Servicio" && $articulo['salesDetails_type'] !== "Kit") {
                        $cantidad = $articulo->salesDetails_inventoryAmount;
                        $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->salesDetails_article)->where('articlesInv_depot', '=', $ventaCancelar->sales_depot)->first();


                        if ($inventario != null) {
                            $inventario->articlesInv_inventory = $inventario->articlesInv_inventory + $cantidad;
                            $validar12 = $inventario->update();
                            if ($validar12) {
                                $ventaCancelada = true;
                            } else {
                                $ventaCancelada = false;
                            }
                        }
                    }

                    if ($articulo['salesDetails_type'] == "Kit") {
                        $articulosKit = PROC_KIT_ARTICLES::where('procKit_article', $articulo->salesDetails_article)->where('procKit_saleID', $ventaCancelar->sales_id)->get();
                        foreach ($articulosKit as $articuloKit) {

                            $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articuloKit->procKit_articleID)->where('articlesInv_depot', '=', $ventaCancelar->sales_depot)->first();

                            if ($inventario != null) {
                                $inventario->articlesInv_inventory = $inventario->articlesInv_inventory + $articuloKit->procKit_cantidad;
                                $inventario->update();
                            }

                            if ($articuloKit->procKit_tipo == 'Serie') {
                                $series = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_saleID', '=', $articuloKit->procKit_saleID)->where('delSeriesMov2_article', '=', $articuloKit->procKit_articleID)->where('delSeriesMov2_articleID', '=', $articuloKit->procKit_article)->get();
                                if (count($series) > 0) {
                                    foreach ($series as $serie) {
                                        $serie->delSeriesMov2_cancelled = 1;
                                        $serie->update();
                                        // $serie->delete();

                                        $proc_lot_series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', $ventaCancelar->sales_company)->where('lotSeries_branchKey', '=', $ventaCancelar->sales_branchOffice)->where('lotSeries_lotSerie', "=", $serie->delSeriesMov2_lotSerie)->first();


                                        if ($proc_lot_series !== null) {
                                            $proc_lot_series->lotSeries_delete = 0;
                                            $proc_lot_series->update();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }


                $ventaCancelar->sales_status = $this->estatus[3];
                if ($ventaCancelada == true) {
                    $ventaContado = $ventaCancelar->update();
                } else {
                    $ventaContado = false;
                }
            } catch (\Exception $e) {
                dd($e);
                $ventaContado = false;
                $message = $e->getMessage();
            }

            // dd($ventaCancelar, $flujo, $cxc, $cxcp, $saldo);
        }

        if ($ventaCancelar->sales_status == $this->estatus[2] && $ventaCancelar->sales_movement == 'Rechazo de Venta') {
            $ventaContado = true;
            $ventaCredito = true;
            $ventaCancelada = false;
            $rechazadaCancelada = false;
            $ventaCancelar->sales_status = $this->estatus[3];
            $cancelarVenta = $ventaCancelar->update();

            if ($cancelarVenta) {
                $rechazadaCancelada = true;
            } else {
                $rechazadaCancelada = false;
            }

            //buscamos si tiene pedido
            if ($ventaCancelar->sales_originID !== null) {
                $ventaAnterior = PROC_SALES::where('sales_movementID', '=', $ventaCancelar->sales_originID)->where("sales_branchOffice", '=', $ventaCancelar->sales_branchOffice)->where("sales_movement", '=', $ventaCancelar->sales_origin)->first();
            } else {
                $ventaAnterior = PROC_SALES::where('sales_movementID', '=', $ventaCancelar->sales_originID)->where("sales_branchOffice", '=', $ventaCancelar->sales_branchOffice)->first();
            }

            if ($ventaAnterior !== null) {
                $ventaAnterior->sales_status = $this->estatus[1];
                $ventaAnteriorReset = $ventaAnterior->update();

                if ($ventaAnteriorReset) {
                    $ventaCancelada = true;
                } else {
                    $ventaCancelada = false;
                }

                //buscamos sus articulos
                $articulosVenta = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $ventaAnterior->sales_id)->where('salesDetails_branchOffice', '=', $ventaAnterior->sales_branchOffice)->get();

                $articulosCancelado = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $ventaCancelar->sales_id)->where('salesDetails_branchOffice', '=', $ventaCancelar->sales_branchOffice)->get();

                $arrayArticulosCancelados = [];

                foreach ($articulosCancelado as $articulo) {
                    $arrayArticulosCancelados[$articulo->salesDetails_article . '-' . $articulo->salesDetails_referenceArticles] = ['cantidad' => $articulo->salesDetails_quantity];
                }

                foreach ($articulosVenta as $articulo) {
                    if (isset($arrayArticulosCancelados[$articulo->salesDetails_article . '-' . $articulo->salesDetails_id])) {
                        $nuevoPendiente = ($articulo->salesDetails_outstandingAmount + $arrayArticulosCancelados[$articulo->salesDetails_article . '-' . $articulo->salesDetails_id]['cantidad']);
                        $articulo->salesDetails_outstandingAmount = $nuevoPendiente;
                        $articulo->update();
                    }
                }
            }

            //buscamos el mov generado
            $movimientosMov = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $ventaCancelar->sales_id)->where('movementFlow_movementDestinity', '=', $ventaCancelar->sales_movement)->where('movementFlow_movementOrigin', '=', $ventaCancelar->sales_origin)->where('movementFlow_branch', '=', $ventaCancelar->sales_branchOffice)->where('movementFlow_company', '=', $ventaCancelar->sales_company)->first();

            if ($movimientosMov !== null) {
                $movimientosMov->movementFlow_status = $this->estatus[3];
                $movimientosMov->update();
            }


            //buscamos el mov generado
            $movimientosMov = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $ventaCancelar->sales_ID)->where('movementFlow_movementDestinity', '=', $ventaCancelar->sales_movement)->where('movementFlow_movementOrigin', '=', $ventaCancelar->sales_origin)->where('movementFlow_branch', '=', $ventaCancelar->sales_branchOffice)->where('movementFlow_company', '=', $ventaCancelar->sales_company)->first();

            if ($movimientosMov !== null) {
                $movimientosMov->movementFlow_status = $this->estatus[3];
                $movimientosMov->update();
            }
        }

        //Regresamos el cantidad indicada o pendiente al origen
        if ($ventaCancelar->sales_originID !== Null) {
            $ventaAnterior = PROC_SALES::where('sales_movementID', '=', $ventaCancelar->sales_originID)->where("sales_branchOffice", '=', $ventaCancelar->sales_branchOffice)->where("sales_movement", '=', $ventaCancelar->sales_origin)->first();


            if ($ventaAnterior !== null) {
                $ventaAnterior->sales_status = $this->estatus[1];
                $ventaAnteriorReset = $ventaAnterior->update();

                if ($ventaAnteriorReset) {
                    $ventaCancelada = true;
                } else {
                    $ventaCancelada = false;
                }

                //buscamos sus articulos
                $articulosCompraAnt = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $ventaAnterior->sales_id)->where('salesDetails_branchOffice', '=', $ventaAnterior->sales_branchOffice)->get();

                $articulosCancelado = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $ventaCancelar->sales_id)->where('salesDetails_branchOffice', '=', $ventaCancelar->sales_branchOffice)->get();

                $arrayArticulosCancelados = [];

                foreach ($articulosCancelado as $articulo) {
                    $arrayArticulosCancelados[$articulo->salesDetails_article . '-' . $articulo->salesDetails_referenceArticles] = ['cantidad' => $articulo->salesDetails_quantity];
                }

                foreach ($articulosCompraAnt as $articulo) {
                    if (isset($arrayArticulosCancelados[$articulo->salesDetails_article . '-' . $articulo->salesDetails_id])) {
                        $nuevoPendiente = ($articulo->salesDetails_outstandingAmount + $arrayArticulosCancelados[$articulo->salesDetails_article . '-' . $articulo->salesDetails_id]['cantidad']);
                        $articulo->salesDetails_outstandingAmount = $nuevoPendiente;
                        $articulo->update();
                    }
                }

                $flujoAnterior = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $ventaAnterior->sales_id)->where('movementFlow_moduleOrigin', '=', 'Ventas')->where('movementFlow_destinityID', '=', $ventaCancelar->sales_id)->where('movementFlow_moduleDestiny', '=', 'Ventas')->where('movementFlow_movementDestinity', '=', $ventaCancelar->sales_movement)->where('movementFlow_branch', '=', $ventaCancelar->sales_branchOffice)->first();

                if ($flujoAnterior !== null) {
                    $flujoAnterior->movementFlow_cancelled = 1;
                    $validar12 = $flujoAnterior->update();
                    if ($validar12) {
                        $ventaCancelada = true;
                    } else {
                        $ventaCancelada = false;
                    }
                }
            }
        }

        if ($ventaCredito == true && $ventaContado == true && $rechazadaCancelada == true) {
            if ($ventaCancelar->sales_movement === "Factura" && ($ventaCancelar->sales_stamped === 0 || $ventaCancelar->sales_stamped === '0') && (session('company')->companies_calculateTaxes == '0' || session('company')->companies_calculateTaxes == 0)) {
                $this->agregarCancelacionFactura($request->id, $request);
                //Metemos la logica de la cancelación de la factura
                $cfdi = new TimbradoController();
                $error = $cfdi->cancelarFactura($request->id);

                if ($error) {
                    $status = 500;
                    $message = 'Error al cancelar el timbrado de la' . $ventaCancelar->sales_movement;
                } else {
                    $status = 200;
                    $message = 'Venta cancelada correctamente';
                }
            } else {
                $status = 200;
                $message = 'Venta cancelada correctamente';
            }
        } else {
            $status = 500;
            $message = 'Error al cancelar la venta';
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status]);
    }

    public function cancelarMovimiento(Request $request)
    {
        $movimiento = PROC_SALES::where('sales_id', '=', $request->id)->first();
        //buscamos sus entradas de compra 

        // dd($orden);
        if ($movimiento->sales_status == $this->estatus[1] && $movimiento->sales_movement == 'Cotización') {


            $facturas = PROC_SALES::where('sales_originID', '=', $movimiento->sales_movementID)->where('sales_origin', '=', $movimiento->sales_movement)->where('sales_movement', '=', 'Factura')->where('sales_status', '=', $this->estatus[2])->where('sales_branchOffice', '=', $movimiento->sales_branchOffice)->get();

            $pedidos = PROC_SALES::where('sales_originID', '=', $movimiento->sales_movementID)->where('sales_origin', '=', $movimiento->sales_movement)->where('sales_movement', '=', 'Pedido')->where('sales_status', '=', $this->estatus[1])->where('sales_branchOffice', '=', $movimiento->sales_branchOffice)->get();

            $ventasPerdidas = PROC_SALES::where('sales_originID', '=', $movimiento->sales_movementID)->where('sales_origin', '=', $movimiento->sales_movement)->where('sales_movement', '=', 'Rechazo de Venta')->where('sales_status', '=', $this->estatus[2])->where('sales_branchOffice', '=', $movimiento->sales_branchOffice)->get();

            // dd($facturas, $pedidos);

            if (($facturas->count() > 0) || ($pedidos->count() > 0) || ($ventasPerdidas->count() > 0)) {
                $status = 500;
                $message = 'No se puede cancelar la cotización, tiene movimientos relacionados';
                return response()->json(['mensaje' => $message, 'estatus' => $status]);
            } else {

                $pedidosSinAfectar = PROC_SALES::where('sales_originID', '=', $movimiento->sales_movementID)->where('sales_origin', '=', $movimiento->sales_movement)->where('sales_movement', '=', 'Pedido')->where('sales_status', '=', $this->estatus[0])->where('sales_branchOffice', '=', $movimiento->sales_branchOffice)->get();

                if ($pedidosSinAfectar->count() > 0) {
                    foreach ($pedidosSinAfectar as $pedido) {
                        $pedido->sales_status = $this->estatus[3];
                        $pedido->update();
                    }
                }


                $facturasSinAfectar = PROC_SALES::where('sales_originID', '=', $movimiento->sales_movementID)->where('sales_origin', '=', $movimiento->sales_movement)->where('sales_movement', '=', 'Factura')->where('sales_status', '=', $this->estatus[0])->where('sales_branchOffice', '=', $movimiento->sales_branchOffice)->get();

                if ($facturasSinAfectar->count() > 0) {
                    foreach ($facturasSinAfectar as $factura) {
                        $factura->sales_status = $this->estatus[3];
                        $factura->update();
                    }
                }

                $ventasPerdidasSinAfectar = PROC_SALES::where('sales_originID', '=', $movimiento->sales_movementID)->where('sales_origin', '=', $movimiento->sales_movement)->where('sales_movement', '=', 'Rechazo de Venta')->where('sales_status', '=', $this->estatus[0])->where('sales_branchOffice', '=', $movimiento->sales_branchOffice)->get();

                if ($ventasPerdidasSinAfectar->count() > 0) {
                    foreach ($ventasPerdidasSinAfectar as $ventaPerdida) {
                        $ventaPerdida->sales_status = $this->estatus[3];
                        $ventaPerdida->update();
                    }
                }


                //cancelamos sus articulos 
                $articulosOrden = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $movimiento->sales_id)->where('salesDetails_branchOffice', '=', $movimiento->sales_branchOffice)->get();

                foreach ($articulosOrden as $articulo) {
                    $articulo->salesDetails_outstandingAmount = null;
                    $articulo->update();
                }


                $movimiento->sales_status = $this->estatus[3];
                $cancelarCotizacion = $movimiento->update();
            }
        }

        if ($movimiento->sales_status == $this->estatus[1] && $movimiento->sales_movement == 'Pedido') {


            $articulos = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $movimiento->sales_id)->where('salesDetails_branchOffice', '=', $movimiento->sales_branchOffice)->get();

            foreach ($articulos as $articulo) {
                if ($articulo->salesDetails_type == 'Serie') {
                    $series = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_saleID', '=', $articulo->salesDetails_saleID)->where('delSeriesMov2_articleID', '=', $articulo->salesDetails_id)->get();

                    $seriesOrigen = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_saleID', '=', $movimiento->sales_id)->where('delSeriesMov2_article', '=', $articulo->salesDetails_article)->get();

                    foreach ($seriesOrigen  as $seriesO) {
                        $seriesO->delSeriesMov2_affected = 0;
                        $seriesO->update();
                    }

                    if (
                        $series != null
                    ) {
                        foreach ($series as $serie) {
                            $serie->delSeriesMov2_cancelled = 1;
                            $serie->update();

                            $proc_lot_series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', $movimiento->sales_company)->where('lotSeries_branchKey', '=', $movimiento->sales_branchOffice)->where('lotSeries_lotSerie', "=", $serie->delSeriesMov2_lotSerie)->first();


                            if ($proc_lot_series !== null) {
                                $proc_lot_series->lotSeries_delete = 0;
                                $proc_lot_series->update();
                            }
                            // $serie->delete();
                        }
                    }
                }

                //ahora lo ahcemos cuando el tipo sea Kit
                if ($articulo['salesDetails_type'] == "Kit") {
                    $articulosKit = PROC_KIT_ARTICLES::where('procKit_article', $articulo->salesDetails_article)->where('procKit_saleID', $movimiento->sales_id)->get();
                    foreach ($articulosKit as $articuloKit) {
                        if ($articuloKit->procKit_tipo == 'Serie') {
                            $series = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_saleID', '=', $articuloKit->procKit_saleID)->where('delSeriesMov2_article', '=', $articuloKit->procKit_articleID)->where('delSeriesMov2_articleID', '=', $articuloKit->procKit_article)->get();
                            if (count($series) > 0) {
                                foreach ($series as $serie) {
                                    $serie->delSeriesMov2_cancelled = 1;
                                    $serie->update();
                                    // $serie->delete();

                                    $proc_lot_series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', $movimiento->sales_company)->where('lotSeries_branchKey', '=', $movimiento->sales_branchOffice)->where('lotSeries_lotSerie', "=", $serie->delSeriesMov2_lotSerie)->first();


                                    if ($proc_lot_series !== null) {
                                        $proc_lot_series->lotSeries_delete = 0;
                                        $proc_lot_series->update();
                                    }
                                }
                            }
                        }
                    }
                }
            }




            $facturas = PROC_SALES::where('sales_originID', '=', $movimiento->sales_movementID)->where('sales_origin', '=', $movimiento->sales_movement)->where('sales_movement', '=', 'Factura')->where('sales_status', '=', $this->estatus[2])->where('sales_branchOffice', '=', $movimiento->sales_branchOffice)->get();

            $ventasPerdidas = PROC_SALES::where('sales_originID', '=', $movimiento->sales_movementID)->where('sales_origin', '=', $movimiento->sales_movement)->where('sales_movement', '=', 'Rechazo de Venta')->where('sales_status', '=', $this->estatus[2])->where('sales_branchOffice', '=', $movimiento->sales_branchOffice)->get();
            // dd($facturas, $pedidos);

            if ($facturas->count() > 0 || $ventasPerdidas->count() > 0) {
                $status = 500;
                $message = 'No se puede cancelar el pedido, tiene movimientos relacionados';
                return response()->json(['mensaje' => $message, 'estatus' => $status]);
            } else {

                $facturasSinAfectar = PROC_SALES::where('sales_originID', '=', $movimiento->sales_movementID)->where('sales_origin', '=', $movimiento->sales_movement)->where('sales_movement', '=', 'Factura')->where('sales_status', '=', $this->estatus[0])->where('sales_branchOffice', '=', $movimiento->sales_branchOffice)->get();

                if ($facturasSinAfectar->count() > 0) {
                    foreach ($facturasSinAfectar as $factura) {
                        $factura->sales_status = $this->estatus[3];
                        $factura->update();
                    }
                }

                $ventasPerdidasSinAfectar = PROC_SALES::where('sales_originID', '=', $movimiento->sales_movementID)->where('sales_origin', '=', $movimiento->sales_movement)->where('sales_movement', '=', 'Rechazo de Venta')->where('sales_status', '=', $this->estatus[0])->where('sales_branchOffice', '=', $movimiento->sales_branchOffice)->get();

                if ($ventasPerdidasSinAfectar->count() > 0) {
                    foreach ($ventasPerdidasSinAfectar as $ventaPerdida) {
                        $ventaPerdida->sales_status = $this->estatus[3];
                        $ventaPerdida->update();
                    }
                }


                //cancelamos sus articulos 
                $articulosOrden = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $movimiento->sales_id)->where('salesDetails_branchOffice', '=', $movimiento->sales_branchOffice)->get();

                foreach ($articulosOrden as $articulo) {
                    $articulo->salesDetails_outstandingAmount = null;
                    $articulo->update();
                }



                $movimiento->sales_status = $this->estatus[3];
                $cancelarCotizacion = $movimiento->update();


                //cancelamos el flujo de movimiento
                $flujo = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $movimiento->sales_id)->where('movementFlow_moduleDestiny', '=', 'Ventas')->where('movementFlow_movementDestinityID', '=', $movimiento->sales_movementID)->where('movementFlow_branch', '=', $movimiento->sales_branchOffice)->first();

                if ($flujo) {
                    $flujo->movementFlow_cancelled = 1;
                    $flujo->update();
                }
            }
        }

        //Regresamos el cantidad indicada o pendiente al origen
        if ($movimiento->sales_originID !== Null) {
            $ventaAnterior = PROC_SALES::where('sales_movementID', '=', $movimiento->sales_originID)->where("sales_branchOffice", '=', $movimiento->sales_branchOffice)->where("sales_movement", '=', $movimiento->sales_origin)->first();


            if ($ventaAnterior !== null) {
                $ventaAnterior->sales_status = $this->estatus[1];
                $ventaAnteriorReset = $ventaAnterior->update();

                if ($ventaAnteriorReset) {
                    $cancelarCotizacion = true;
                } else {
                    $cancelarCotizacion = false;
                }

                //buscamos sus articulos
                $articulosCompraAnt = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $ventaAnterior->sales_id)->where('salesDetails_branchOffice', '=', $ventaAnterior->sales_branchOffice)->get();

                $articulosCancelado = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $movimiento->sales_id)->where('salesDetails_branchOffice', '=', $movimiento->sales_branchOffice)->get();

                $arrayArticulosCancelados = [];

                foreach ($articulosCancelado as $articulo) {
                    $arrayArticulosCancelados[$articulo->salesDetails_article . '-' . $articulo->salesDetails_referenceArticles] = ['cantidad' => $articulo->salesDetails_quantity];

                    // if($articulo->salesDetails_type  === 'Serie'){
                    //     $seriesEliminados = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_saleID', '=', $movimiento->sales_id)->where('delSeriesMov2_affected', '=', 1)->get();

                    //     if($seriesEliminados !== null){
                    //         foreach ($seriesEliminados as $serie) {
                    //             $serie->delSeriesMov2_cancelled = 1;
                    //             $serie->update();

                    //             $proc_lot_series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', $movimiento->sales_company)->where('lotSeries_branchKey', '=', $movimiento->sales_branchOffice)->where('lotSeries_lotSerie', "=", $serie->delSeriesMov2_lotSerie)->first();


                    //             if($proc_lot_series !== null){
                    //                 $proc_lot_series->lotSeries_delete = 0;
                    //                 $proc_lot_series->update();
                    //             }
                    //         }


                    //     }

                    // }

                }

                foreach ($articulosCompraAnt as $articulo) {
                    if (isset($arrayArticulosCancelados[$articulo->salesDetails_article . '-' . $articulo->salesDetails_id])) {
                        $nuevoPendiente = ($articulo->salesDetails_outstandingAmount + $arrayArticulosCancelados[$articulo->salesDetails_article . '-' . $articulo->salesDetails_id]['cantidad']);
                        $articulo->salesDetails_outstandingAmount = $nuevoPendiente;
                        $articulo->update();
                    }
                }
            }
        }
        if ($cancelarCotizacion) {
            $status = 200;
            $message = 'Proceso cancelado correctamente';
        } else {
            $status = 500;
            $message = 'Error al cancelar el movimiento';
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status]);
    }

    public function cancelarMovPendiente(Request $request)
    {

        $orden = PROC_SALES::where('sales_id', '=', $request->id)->first();

        //buscamos sus articulos
        $articulosOrden = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $orden->sales_id)->where('salesDetails_branchOffice', '=', $orden->sales_branchOffice)->get();

        //buscamos sus entradas de compra

        $entradasCompra = PROC_SALES::where('sales_originID', '=', $orden->sales_id)->where('sales_origin', '=', $orden->sales_movement)->where('sales_status', '=', $this->estatus[2])->where('sales_branchOffice', '=', $orden->sales_branchOffice)->get();

        // dd($entradasCompra);

        $importeEntradas = 0;
        $impuestoEntradas = 0;
        foreach ($entradasCompra as $entrada) {
            $importeEntradas += $entrada->sales_amount;
            $impuestoEntradas += $entrada->sales_taxes;
        }

        // dd($importeEntradas, $impuestoEntradas);

        foreach ($articulosOrden as $articulo) {

            $articulo->salesDetails_canceledAmount === null ?   $articulo->salesDetails_canceledAmount  = $articulo->salesDetails_outstandingAmount : $articulo->salesDetails_canceledAmount  = $articulo->salesDetails_canceledAmount + $articulo->salesDetails_outstandingAmount;
            $articulo->salesDetails_outstandingAmount = null;
            $articulo->update();
        }

        $orden->sales_status = $this->estatus[2];
        $orden->sales_amount = $importeEntradas;
        $orden->sales_taxes = $impuestoEntradas;
        $orden->sales_total = 0;

        $cancelarOrden = $orden->update();

        if ($cancelarOrden) {
            $status = 200;
            $message = 'Proceso cancelado correctamente';
        } else {
            $status = 500;
            $message = 'Error al cancelar el movimiento';
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status]);
    }

    public function ventasAction(Request $request)
    {
        $nameFolio = $request->nameFolio;
        $nameKey = $request->nameKey;
        $nameMov = $request->nameMov;
        $status = $request->status;
        $nameFecha = $request->nameFecha;
        $nameUsuario = $request->nameUsuario;
        $nameSucursal = $request->nameSucursal;
        $nameMoneda = $request->nameMoneda;
        $timbrado = $request->timbrado;
        $ref = $request->referenciaMov;

        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;

        if ($fechaInicio !== null && $fechaFinal !== null && $nameFecha === "Rango Fechas") {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }

        switch ($request->input('action')) {
            case 'Búsqueda':
                $ventas_collection_filtro = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
                    ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                    ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
                    ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
                    ->orderBy('PROC_SALES.created_at', 'desc')
                    ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
                    ->whereSalesMovementID($nameFolio)
                    ->whereSalesCustomer($nameKey)
                    ->whereSalesMovement($nameMov)
                    ->whereSalesStatus($status)
                    ->whereSalesDate($nameFecha)
                    ->whereSalesUser($nameUsuario)
                    ->whereSalesBranchOffice($nameSucursal)
                    ->whereSalesMoney($nameMoneda)
                    ->whereSalesStamped($timbrado)
                    ->whereSalesReference($ref)
                    ->get();

                $ventas_filtro_array = $ventas_collection_filtro->toArray();
                // dd($ventas_filtro_array);

                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.modulo.ventas')->with('ventas_filtro_array', $ventas_filtro_array)
                    ->with('nameFolio', $nameFolio)
                    ->with('nameKey', $nameKey)
                    ->with('nameMov', $nameMov)
                    ->with('status', $status)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameUsuario', $nameUsuario)
                    ->with('nameSucursal', $nameSucursal)
                    ->with('nameMoneda', $nameMoneda)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal)
                    ->with('timbrado', $timbrado)
                    ->with('referenciaMov', $ref);
                break;
            case 'Exportar excel':
                $venta = new PROC_VentasExport($nameFolio, $nameKey, $nameMov, $status, $nameFecha, $nameUsuario, $nameSucursal, $nameMoneda, $timbrado, $ref);
                return Excel::download($venta, 'Ventas.xlsx');
                break;
        }
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
        $sucursales = CAT_BRANCH_OFFICES::WHERE('branchOffices_status', '=', 'Alta')
            ->WHERE('branchOffices_companyId', '=', session('company')->companies_key)->get();
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
        $proveedor = CAT_CUSTOMERS::where('customers_key', '=', $request->proveedor)
            ->where('customers_status', '=', 'Alta')
            ->first();
        return response()->json($proveedor);
    }

    public function getCliente(Request $request)
    {
        $cliente = CAT_CUSTOMERS::where('customers_key', '=', $request->cliente)->first();
        return response()->json($cliente);
    }

    public function getPlacas(Request $request)
    {
        $placas = CAT_VEHICLES::where('vehicles_key', '=', $request->vehiculo)->get();
        return response()->json($placas);
    }

    public function getConfUnidades()
    {
        $unidades = [];
        $unidades_collection = CONF_UNITS::all();

        $unidades_array = $unidades_collection->toArray();

        foreach ($unidades_array as $key => $value) {
            $unidades[$value['units_id']] = $value['units_unit'];
        }
        return $unidades;
    }



    public function getSeriesGuardados(Request $request)
    {

        $tipoArticulo = $request->tipo;

        $venta = PROC_SALES::where('sales_id', '=', $request->idCompra)->first();

        $series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', session('company')->companies_key)->where('lotSeries_branchKey', '=', session('sucursal')->branchOffices_key)->where('lotSeries_depot', '=', $request->almacen)->where('lotSeries_article', '=', $request->claveArticulo)->where('lotSeries_delete', '=', 0)->get();

        if ($tipoArticulo == "Kit") {
            $series_seleccionados = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_companieKey', '=', session('company')->companies_key)->where('delSeriesMov2_branchKey', '=', session('sucursal')->branchOffices_key)->where('delSeriesMov2_saleID', '=', $request->idCompra)->where('delSeriesMov2_article', '=', $request->claveArticulo)->get();
        } else {
            $series_seleccionados = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_companieKey', '=', session('company')->companies_key)->where('delSeriesMov2_branchKey', '=', session('sucursal')->branchOffices_key)->where('delSeriesMov2_saleID', '=', $request->idCompra)->where('delSeriesMov2_article', '=', $request->claveArticulo)->where('delSeriesMov2_articleID', '=', $request->articleId)->get();
        }


        if (count($series_seleccionados) == 0) {
            if ($venta->sales_origin === "Pedido") {
                //Buscamos el origen de la factura para buscar las series que fueron seleccionadas
                $ventaOrigen = PROC_SALES::WHERE('sales_movement', '=', 'Pedido')->WHERE('sales_movementID', '=', $venta->sales_originID)->WHERE('sales_company', '=',  session('company')->companies_key)->WHERE('sales_branchOffice', '=', session('sucursal')->branchOffices_key)->first();


                if ($tipoArticulo == "Kit") {
                    $series_seleccionados = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_companieKey', '=', session('company')->companies_key)->where('delSeriesMov2_branchKey', '=', session('sucursal')->branchOffices_key)->where('delSeriesMov2_saleID', '=', $ventaOrigen->sales_id)->where('delSeriesMov2_article', '=', $request->claveArticulo)->get();
                } else {
                    $series_seleccionados = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_companieKey', '=', session('company')->companies_key)->where('delSeriesMov2_branchKey', '=', session('sucursal')->branchOffices_key)->where('delSeriesMov2_saleID', '=', $ventaOrigen->sales_id)->where('delSeriesMov2_article', '=', $request->claveArticulo)->where('delSeriesMov2_articleID', '=', $request->articleId)->get();
                }
            }
        }


        return response()->json(['status' => 200, 'data' => $series, 'data2' => $series_seleccionados]);
    }

    public function precioLista(Request $request)
    {
        $articulosUnidad = [];
        $articulosInvUnidad = [];
        $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*', 'CAT_ARTICLES.' . $request->list . ' as precio')->get()->toArray();
        $unidad = $this->getConfUnidades();

        $articulos = CAT_ARTICLES::WHERE('articles_status', '=', 'Alta')->select('CAT_ARTICLES.*', 'CAT_ARTICLES.' . $request->list . ' as precio')->get()->toArray();

        if (count($articulosInv) > 0) {
            foreach ($articulosInv as $articulo) {
                $articulosInvUnidad[$articulo['articlesInv_article']] = $articulo;
            }

            foreach ($articulos as $articulo) {
                if (!array_key_exists($articulo['articles_key'], $articulosInvUnidad)) {
                    $articulosInvUnidad[$articulo['articles_key']] = $articulo;
                }
            }
        } else {
            $articulosInvUnidad = $articulos;
        }

        foreach ($articulosInvUnidad as $articulo) {
            $articuloJson = new stdClass();
            $articuloJson->precio = $articulo["precio"];
            $articuloJson->articles_key = $articulo["articles_key"];
            $articuloJson->articles_descript = $articulo["articles_descript"];
            $articuloJson->articles_porcentIva = $articulo["articles_porcentIva"];
            $articuloJson->articles_unitSale = $unidad[$articulo["articles_unitSale"]];
            $articuloJson->articles_type =  $articulo["articles_type"];
            $articuloJson->articles_retention1 =  floatVal($articulo["articles_retention1"]);
            $articuloJson->articles_retention2 =  floatVal($articulo["articles_retention2"]);

            if (isset($articulo["articlesInv_branchKey"]) && session('sucursal')->branchOffices_key == $articulo["articlesInv_branchKey"]) {
                $articuloJson->articlesInv_inventory = $articulo["articlesInv_inventory"];
                $articuloJson->articlesInv_depot = $articulo["articlesInv_depot"];
                $articuloJson->articlesInv_branchKey =  $articulo["articlesInv_branchKey"];
            } else {
                $articuloJson->articlesInv_inventory = 0;
                $articuloJson->articlesInv_depot = null;
                $articuloJson->articlesInv_branchKey = null;
            }

            if (!array_key_exists($articulo["articles_key"], $articulosUnidad)) {
                $articulosUnidad[$articulo["articles_key"]] = $articuloJson;
            } else {
                if (isset($articulo["articlesInv_branchKey"]) && session('sucursal')->branchOffices_key == $articulo["articlesInv_branchKey"]) {
                    $articulosUnidad[$articulo["articles_key"]]->articlesInv_inventory += $articulo["articlesInv_inventory"];
                }
            }
        }

        $sucursal = session('sucursal')->branchOffices_key;

        if ($articulos !== null) {
            $message = 'Folios aplica del proveedor';
            $estatus = 200;
        } else {
            $message = 'No se pudo encontrar folios aplicados';
            $estatus = 500;
        }

        return response()->json(['message' => $message, 'estatus' => $estatus, 'dataArticulos' => $articulosUnidad, 'sucursal' => $sucursal]);
    }

    public function precioListaConExistencia(Request $request)
    {
        $articulosUnidad = [];
        $articulosInvUnidad = [];
        $checkExistencia = $request->checkArticulosExistentes;
        // dd($checkExistencia);

        if ($checkExistencia == "true") {
            // dd("entro");
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)
                ->WHERE('articlesInv_inventory', '>', 0)
                ->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*', 'CAT_ARTICLES.' . $request->list . ' as precio')->get()->toArray();
            $unidad = $this->getConfUnidades();


            if (count($articulosInv) > 0) {
                foreach ($articulosInv as $articulo) {
                    $articulosInvUnidad[$articulo['articlesInv_article']] = $articulo;
                }

                // dd($articulosInvUnidad);
            }
        } else {
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*', 'CAT_ARTICLES.' . $request->list . ' as precio')->get()->toArray();
            $unidad = $this->getConfUnidades();

            $articulos = CAT_ARTICLES::WHERE('articles_status', '=', 'Alta')->select('CAT_ARTICLES.*', 'CAT_ARTICLES.' . $request->list . ' as precio')->get()->toArray();

            if (count($articulosInv) > 0) {
                foreach ($articulosInv as $articulo) {
                    $articulosInvUnidad[$articulo['articlesInv_article']] = $articulo;
                }

                foreach ($articulos as $articulo) {
                    if (!array_key_exists($articulo['articles_key'], $articulosInvUnidad)) {
                        $articulosInvUnidad[$articulo['articles_key']] = $articulo;
                    }
                }
            } else {
                $articulosInvUnidad = $articulos;
            }
        }

        foreach ($articulosInvUnidad as $articulo) {
            $articuloJson = new stdClass();
            $articuloJson->precio = $articulo["precio"];
            $articuloJson->articles_key = $articulo["articles_key"];
            $articuloJson->articles_descript = $articulo["articles_descript"];
            $articuloJson->articles_porcentIva = $articulo["articles_porcentIva"];
            $articuloJson->articles_unitSale = $unidad[$articulo["articles_unitSale"]];
            $articuloJson->articles_type =  $articulo["articles_type"];
            $articuloJson->articles_retention1 =  floatVal($articulo["articles_retention1"]);
            $articuloJson->articles_retention2 =  floatVal($articulo["articles_retention2"]);

            if (isset($articulo["articlesInv_branchKey"]) && session('sucursal')->branchOffices_key == $articulo["articlesInv_branchKey"]) {
                $articuloJson->articlesInv_inventory = $articulo["articlesInv_inventory"];
                $articuloJson->articlesInv_depot = $articulo["articlesInv_depot"];
                $articuloJson->articlesInv_branchKey =  $articulo["articlesInv_branchKey"];
            } else {
                $articuloJson->articlesInv_inventory = 0;
                $articuloJson->articlesInv_depot = null;
                $articuloJson->articlesInv_branchKey = null;
            }

            if (!array_key_exists($articulo["articles_key"], $articulosUnidad)) {
                $articulosUnidad[$articulo["articles_key"]] = $articuloJson;
            } else {
                if (isset($articulo["articlesInv_branchKey"]) && session('sucursal')->branchOffices_key == $articulo["articlesInv_branchKey"]) {
                    $articulosUnidad[$articulo["articles_key"]]->articlesInv_inventory += $articulo["articlesInv_inventory"];
                }
            }
        }

        $sucursal = session('sucursal')->branchOffices_key;

        if ($articulosInvUnidad !== null) {
            $message = 'Folios aplica del proveedor';
            $estatus = 200;
        } else {
            $message = 'No se pudo encontrar folios aplicados';
            $estatus = 500;
        }

        return response()->json(['message' => $message, 'estatus' => $estatus, 'dataArticulos' => $articulosUnidad, 'sucursal' => $sucursal]);
    }

    public function articulosCategoria(Request $request)
    {
        $articulosUnidad = [];
        $articulosInvUnidad = [];
        $categoria = $request->categoria;
        // dd($checkExistencia);

        if ($categoria !== '') {
            // dd("entro");
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)
                ->WHERE('articles_category', '=', $categoria)
                ->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*', 'CAT_ARTICLES.' . $request->list . ' as precio')->get()->toArray();
            $unidad = $this->getConfUnidades();


            if (count($articulosInv) > 0) {
                foreach ($articulosInv as $articulo) {
                    $articulosInvUnidad[$articulo['articlesInv_article']] = $articulo;
                }

                // dd($articulosInvUnidad);
            }
        } else {
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*', 'CAT_ARTICLES.' . $request->list . ' as precio')->get()->toArray();
            $unidad = $this->getConfUnidades();

            $articulos = CAT_ARTICLES::WHERE('articles_status', '=', 'Alta')->select('CAT_ARTICLES.*', 'CAT_ARTICLES.' . $request->list . ' as precio')->get()->toArray();

            if (count($articulosInv) > 0) {
                foreach ($articulosInv as $articulo) {
                    $articulosInvUnidad[$articulo['articlesInv_article']] = $articulo;
                }

                foreach ($articulos as $articulo) {
                    if (!array_key_exists($articulo['articles_key'], $articulosInvUnidad)) {
                        $articulosInvUnidad[$articulo['articles_key']] = $articulo;
                    }
                }
            } else {
                $articulosInvUnidad = $articulos;
            }
        }

        foreach ($articulosInvUnidad as $articulo) {
            $articuloJson = new stdClass();
            $articuloJson->precio = $articulo["precio"];
            $articuloJson->articles_key = $articulo["articles_key"];
            $articuloJson->articles_descript = $articulo["articles_descript"];
            $articuloJson->articles_porcentIva = $articulo["articles_porcentIva"];
            $articuloJson->articles_unitSale = $unidad[$articulo["articles_unitSale"]];
            $articuloJson->articles_type =  $articulo["articles_type"];
            $articuloJson->articles_retention1 =  floatVal($articulo["articles_retention1"]);
            $articuloJson->articles_retention2 =  floatVal($articulo["articles_retention2"]);

            if (isset($articulo["articlesInv_branchKey"]) && session('sucursal')->branchOffices_key == $articulo["articlesInv_branchKey"]) {
                $articuloJson->articlesInv_inventory = $articulo["articlesInv_inventory"];
                $articuloJson->articlesInv_depot = $articulo["articlesInv_depot"];
                $articuloJson->articlesInv_branchKey =  $articulo["articlesInv_branchKey"];
            } else {
                $articuloJson->articlesInv_inventory = 0;
                $articuloJson->articlesInv_depot = null;
                $articuloJson->articlesInv_branchKey = null;
            }

            if (!array_key_exists($articulo["articles_key"], $articulosUnidad)) {
                $articulosUnidad[$articulo["articles_key"]] = $articuloJson;
            } else {
                if (isset($articulo["articlesInv_branchKey"]) && session('sucursal')->branchOffices_key == $articulo["articlesInv_branchKey"]) {
                    $articulosUnidad[$articulo["articles_key"]]->articlesInv_inventory += $articulo["articlesInv_inventory"];
                }
            }
        }

        $sucursal = session('sucursal')->branchOffices_key;

        if ($articulosInvUnidad !== null) {
            $message = 'Folios aplica del proveedor';
            $estatus = 200;
        } else {
            $message = 'No se pudo encontrar folios aplicados';
            $estatus = 500;
        }

        return response()->json(['message' => $message, 'estatus' => $estatus, 'dataArticulos' => $articulosUnidad, 'sucursal' => $sucursal]);
    }

    public function articulosFamilia(Request $request)
    {
        $articulosUnidad = [];
        $articulosInvUnidad = [];
        $familia = $request->familia;
        // dd($checkExistencia);

        if ($familia !== '') {
            // dd("entro");
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)
                ->WHERE('articles_family', '=', $familia)
                ->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*', 'CAT_ARTICLES.' . $request->list . ' as precio')->get()->toArray();
            $unidad = $this->getConfUnidades();


            if (count($articulosInv) > 0) {
                foreach ($articulosInv as $articulo) {
                    $articulosInvUnidad[$articulo['articlesInv_article']] = $articulo;
                }

                // dd($articulosInvUnidad);
            }
        } else {
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*', 'CAT_ARTICLES.' . $request->list . ' as precio')->get()->toArray();
            $unidad = $this->getConfUnidades();

            $articulos = CAT_ARTICLES::WHERE('articles_status', '=', 'Alta')->select('CAT_ARTICLES.*', 'CAT_ARTICLES.' . $request->list . ' as precio')->get()->toArray();

            if (count($articulosInv) > 0) {
                foreach ($articulosInv as $articulo) {
                    $articulosInvUnidad[$articulo['articlesInv_article']] = $articulo;
                }

                foreach ($articulos as $articulo) {
                    if (!array_key_exists($articulo['articles_key'], $articulosInvUnidad)) {
                        $articulosInvUnidad[$articulo['articles_key']] = $articulo;
                    }
                }
            } else {
                $articulosInvUnidad = $articulos;
            }
        }

        foreach ($articulosInvUnidad as $articulo) {
            $articuloJson = new stdClass();
            $articuloJson->precio = $articulo["precio"];
            $articuloJson->articles_key = $articulo["articles_key"];
            $articuloJson->articles_descript = $articulo["articles_descript"];
            $articuloJson->articles_porcentIva = $articulo["articles_porcentIva"];
            $articuloJson->articles_unitSale = $unidad[$articulo["articles_unitSale"]];
            $articuloJson->articles_type =  $articulo["articles_type"];
            $articuloJson->articles_retention1 =  floatVal($articulo["articles_retention1"]);
            $articuloJson->articles_retention2 =  floatVal($articulo["articles_retention2"]);

            if (isset($articulo["articlesInv_branchKey"]) && session('sucursal')->branchOffices_key == $articulo["articlesInv_branchKey"]) {
                $articuloJson->articlesInv_inventory = $articulo["articlesInv_inventory"];
                $articuloJson->articlesInv_depot = $articulo["articlesInv_depot"];
                $articuloJson->articlesInv_branchKey =  $articulo["articlesInv_branchKey"];
            } else {
                $articuloJson->articlesInv_inventory = 0;
                $articuloJson->articlesInv_depot = null;
                $articuloJson->articlesInv_branchKey = null;
            }

            if (!array_key_exists($articulo["articles_key"], $articulosUnidad)) {
                $articulosUnidad[$articulo["articles_key"]] = $articuloJson;
            } else {
                if (isset($articulo["articlesInv_branchKey"]) && session('sucursal')->branchOffices_key == $articulo["articlesInv_branchKey"]) {
                    $articulosUnidad[$articulo["articles_key"]]->articlesInv_inventory += $articulo["articlesInv_inventory"];
                }
            }
        }

        $sucursal = session('sucursal')->branchOffices_key;

        if ($articulosInvUnidad !== null) {
            $message = 'Folios aplica del proveedor';
            $estatus = 200;
        } else {
            $message = 'No se pudo encontrar folios aplicados';
            $estatus = 500;
        }

        return response()->json(['message' => $message, 'estatus' => $estatus, 'dataArticulos' => $articulosUnidad, 'sucursal' => $sucursal]);
    }

    public function articulosGrupo(Request $request)
    {
        $articulosUnidad = [];
        $articulosInvUnidad = [];
        $grupo = $request->grupo;
        // dd($checkExistencia);

        if ($grupo !== '') {
            // dd("entro");
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)
                ->WHERE('articles_group', '=', $grupo)
                ->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*', 'CAT_ARTICLES.' . $request->list . ' as precio')->get()->toArray();
            $unidad = $this->getConfUnidades();


            if (count($articulosInv) > 0) {
                foreach ($articulosInv as $articulo) {
                    $articulosInvUnidad[$articulo['articlesInv_article']] = $articulo;
                }

                // dd($articulosInvUnidad);
            }
        } else {
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*', 'CAT_ARTICLES.' . $request->list . ' as precio')->get()->toArray();
            $unidad = $this->getConfUnidades();

            $articulos = CAT_ARTICLES::WHERE('articles_status', '=', 'Alta')->select('CAT_ARTICLES.*', 'CAT_ARTICLES.' . $request->list . ' as precio')->get()->toArray();

            if (count($articulosInv) > 0) {
                foreach ($articulosInv as $articulo) {
                    $articulosInvUnidad[$articulo['articlesInv_article']] = $articulo;
                }

                foreach ($articulos as $articulo) {
                    if (!array_key_exists($articulo['articles_key'], $articulosInvUnidad)) {
                        $articulosInvUnidad[$articulo['articles_key']] = $articulo;
                    }
                }
            } else {
                $articulosInvUnidad = $articulos;
            }
        }

        foreach ($articulosInvUnidad as $articulo) {
            $articuloJson = new stdClass();
            $articuloJson->precio = $articulo["precio"];
            $articuloJson->articles_key = $articulo["articles_key"];
            $articuloJson->articles_descript = $articulo["articles_descript"];
            $articuloJson->articles_porcentIva = $articulo["articles_porcentIva"];
            $articuloJson->articles_unitSale = $unidad[$articulo["articles_unitSale"]];
            $articuloJson->articles_type =  $articulo["articles_type"];
            $articuloJson->articles_retention1 =  floatVal($articulo["articles_retention1"]);
            $articuloJson->articles_retention2 =  floatVal($articulo["articles_retention2"]);

            if (isset($articulo["articlesInv_branchKey"]) && session('sucursal')->branchOffices_key == $articulo["articlesInv_branchKey"]) {
                $articuloJson->articlesInv_inventory = $articulo["articlesInv_inventory"];
                $articuloJson->articlesInv_depot = $articulo["articlesInv_depot"];
                $articuloJson->articlesInv_branchKey =  $articulo["articlesInv_branchKey"];
            } else {
                $articuloJson->articlesInv_inventory = 0;
                $articuloJson->articlesInv_depot = null;
                $articuloJson->articlesInv_branchKey = null;
            }

            if (!array_key_exists($articulo["articles_key"], $articulosUnidad)) {
                $articulosUnidad[$articulo["articles_key"]] = $articuloJson;
            } else {
                if (isset($articulo["articlesInv_branchKey"]) && session('sucursal')->branchOffices_key == $articulo["articlesInv_branchKey"]) {
                    $articulosUnidad[$articulo["articles_key"]]->articlesInv_inventory += $articulo["articlesInv_inventory"];
                }
            }
        }

        $sucursal = session('sucursal')->branchOffices_key;

        if ($articulosInvUnidad !== null) {
            $message = 'Folios aplica del proveedor';
            $estatus = 200;
        } else {
            $message = 'No se pudo encontrar folios aplicados';
            $estatus = 500;
        }

        return response()->json(['message' => $message, 'estatus' => $estatus, 'dataArticulos' => $articulosUnidad, 'sucursal' => $sucursal]);
    }

    public function getCostoPromedio(Request $request)
    {

        $compra = PROC_SALES::where('sales_id', '=', $request->idCompra)->first();
        $unidad = $this->getConfUnidades();

        $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $request->id)->join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->join('PROC_ARTICLES_COST', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_COST.articlesCost_article')->join('CONF_UNITS', 'CAT_ARTICLES.articles_unitBuy', '=', 'CONF_UNITS.units_id')->join('CAT_DEPOTS', 'PROC_ARTICLES_COST.articlesCost_depotKey', '=', 'CAT_DEPOTS.depots_key')->where('articlesCost_branchKey', '=', $compra->sales_branchOffice)->where('articlesCost_depotKey', '=', $compra->sales_depot)->where('PROC_ARTICLES_INV.articlesInv_depot', '=', $compra->sales_depot)->first();

        $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $request->id)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->get();


        $imagenesArticulo = CAT_ARTICLES_IMG::where('articlesImg_article', '=', $request->id)->get();


        if ($infoArticulo == null) {
            $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $request->id)->first();
            $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $request->id)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->get();
        }

        if ($infoArticulo->articles_type == 'Kit') {

            $articulosKit = CAT_KIT_ARTICLES::where('kitArticles_articleID', '=', $infoArticulo->articles_id)->get();
            $articulosKitInfo = array();
            foreach ($articulosKit as $articuloKit) {
                $articuloKitInfo = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('articles_key', '=', $articuloKit->kitArticles_article)->first();
                array_push($articulosKitInfo, $articuloKitInfo);
            }
            //  dd($articulosKitInfo);
        } else {
            $articulosKitInfo = null;
        }

        if ($infoArticulo != null) {
            $status = 200;
        } else {
            $status = 404;
        }

        return response()->json(['data' => $infoArticulo, 'estatus' => $status, 'articulosByAlmacen' => $articulosByAlmacen, 'imagenesArticulo' => $imagenesArticulo, 'articulosKitInfo' => $articulosKitInfo, 'unidad' => $unidad]);
    }

    public function listaEmpaques(Request $request)
    {
        $listaEmpaques = CONF_PACKAGING_UNITS::where('packaging_units_status', '=', 'Alta')->get();

        return response()->json(['status' => 200, 'data' =>  $listaEmpaques]);
    }

    public function getListaEmpaques(Request $request)
    {
        $listaEmpaques = PROC_PACKINGLIST::where('packingList_saleID', '=', $request->sale)->where('packingList_article', '=', $request->article)->where('packingList_articleID', '=', $request->fila)->get();
        return response()->json(['status' => 200, 'data' =>  $listaEmpaques]);
    }

    public function deleteListaEmpaques(Request $request)
    {
        $listaEmpaques = PROC_PACKINGLIST::where('packingList_saleID', '=', $request->sale)->where('packingList_article', '=', $request->article)->where('packingList_articleID', '=', $request->fila)->get();
        foreach ($listaEmpaques as $key => $value) {
            $delete =   $value->delete();
        }
        if ($delete) {
            $status = 200;
        } else {
            $status = 500;
        }
        return response()->json(['status' => $status]);
    }



    public function buscardorArticulosInventarios(Request $request)
    {

        $articulo = CAT_ARTICLES::WHERE('articles_key', '=', $request->clave)->WHERE('articles_status', '=', 'Alta')->select('CAT_ARTICLES.*', 'CAT_ARTICLES.' . $request->list . ' as precio')->first();

        if ($articulo == null) {
            return response()->json(['status' => false, 'data' => 'Articulo no encontrado']);
        } else {
            $articulo = collect($articulo)->toArray();
            $unidad = $this->getConfUnidades();
            $unidadDefectoArticulo = $unidad[$articulo['articles_unitSale']];
            $articuloUnidad = (object) array_merge((array) $articulo, (array) ['unidad' => $unidadDefectoArticulo]);

            return response()->json(['status' => true, 'data' =>  $articuloUnidad]);
        }
    }

    public function getMultiUnidad(Request $request)
    {
        $multiUnidad = CAT_ARTICLES_UNITS::where('articlesUnits_article', '=', $request->factorUnidad)->get();
        return response()->json($multiUnidad);
    }

    public function getCondicionPago(Request $request)
    {
        $condicionPago = CONF_CREDIT_CONDITIONS::where('creditConditions_id', '=', $request->condicionPago)->first();
        return response()->json($condicionPago);
    }


    public function validacionesFactura($empresa, $cliente, $articulosIn)
    {


        $empresa = CAT_COMPANIES::where('companies_key', $empresa)->first();

        //  dd($empresa);
        // verificar que la empresa tenga timbres disponibles
        // if ($empresa->companies_AvailableStamps === 0 || $empresa->companies_AvailableStamps === null || $empresa->companies_AvailableStamps === '0') {
        //     $message = 'La empresa no cuenta con timbres disponibles';
        //     return $message;
        // }

        if ($empresa->companies_rfc == '' || $empresa->companies_rfc === null) {
            $message = 'La razón social debe contar con RFC';
            return $message;
        }

        if ($empresa->companies_taxRegime == '' || $empresa->companies_taxRegime === null) {
            $message = 'La razón social debe contar con Regimen Fiscal';
            return $message;
        }

        //    if($empresa->companies_routeCertificate == null || $empresa->companies_routeKey == null){
        //     $message = 'La razón social debe contar con los certificados CSD y Key';
        //     return $message;
        //     }

        //validar que tenga una contraseña para el sat
        if ($empresa->companies_passwordKey == '' || $empresa->companies_passwordKey === null) {
            $message = 'No tiene registrada la contraseña para el certificado';
            return $message;
        }

        //verificar que los archivos esten en la ruta correcta
        // if(!Storage::disk('empresas')->exists($empresa->companies_routeKey) ){
        //     $message = 'El key del certificado no está en la ruta configurada ' . $empresa->companies_routeKey;
        //     return $message;
        // }

        // if(!Storage::disk('empresas')->exists($empresa->companies_routeCertificate) ){
        //     $message = 'El certificado no está en la ruta configurada ' .$empresa->companies_routeCertificate;
        //     return $message;
        // }


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

        $articulos = $articulosIn;
        $articulos = json_decode($articulos, true);
        $claveArt = array_keys($articulos);

        foreach ($claveArt as $key => $articulo) {
            $articuloInd = explode('-', $articulo);

            $articulo = CAT_ARTICLES::where('articles_key', $articuloInd[0])->first();

            // dd($articulo);

            if ($articulo->articles_productService == '' || $articulo->articles_productService == null  || $articulo->articles_productService == '0') {
                $message = 'No tiene capturado correctamente el producto servicio en el articulo ' . $articulo->articles_key;
                return $message;
            }

            if ($articulo->articles_objectTax == '' || $articulo->articles_objectTax == null) {
                $message = 'No tiene capturado correctamente el producto servicio en el articulo ' . $articulo->articles_key;
                return $message;
            }

            if ($articulo->articles_type == 'Kit') {
                //buscamos los articulos kit
                $articulosKit = CAT_KIT_ARTICLES::where('kitArticles_articleID', $articulo->articles_id)->get();


                foreach ($articulosKit as $key => $articuloKit) {
                    $articuloKit = CAT_ARTICLES::where('articles_key', $articuloKit->kitArticles_article)->first();

                    if ($articuloKit->articles_productService == '' || $articuloKit->articles_productService === null || $articuloKit->articles_productService == '0') {
                        $message = 'No tiene capturado correctamente el producto servicio en el articulo ' . $articuloKit->articles_key;
                        return $message;
                    }

                    if ($articuloKit->articles_objectTax == '' || $articuloKit->articles_objectTax === null) {
                        $message = 'No tiene capturado correctamente el producto servicio en el articulo ' . $articuloKit->articles_key;
                        return $message;
                    }
                }
            }
        }
    }

    public function validacionesFacturaGuardada($empresa, $venta)
    {
        $empresa = CAT_COMPANIES::where('companies_key', $empresa)->first();
        //  dd($empresa);

        $sale = PROC_SALES::where('sales_id', $venta)->first();

        //verificar que la empresa tenga timbres disponibles
        if ($empresa->companies_AvailableStamps === 0 || $empresa->companies_AvailableStamps === null || $empresa->companies_AvailableStamps === '0') {
            $message = 'La empresa no cuenta con timbres disponibles';
            return $message;
        }

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
        $cliente = CAT_CUSTOMERS::where('customers_key', $sale->sales_customer)->first();

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

        $articulosVenta = PROC_SALES_DETAILS::where('salesDetails_saleID', $venta)->get();
        foreach ($articulosVenta as $key => $articulo) {

            $articulo = CAT_ARTICLES::where('articles_key', $articulo->salesDetails_article)->first();

            if ($articulo->articles_productService == '' || $articulo->articles_productService === null || $articulo->articles_productService == '0') {
                $message = 'No tiene capturado correctamente el producto servicio en el articulo ' . $articulo->articles_key;
                return $message;
            }

            if ($articulo->articles_objectTax == '' || $articulo->articles_objectTax === null) {
                $message = 'No tiene capturado correctamente el producto servicio en el articulo ' . $articulo->articles_key;
                return $message;
            }
        }
    }

    public function validacionesFacturaExterior($articulosIn)
    {

        $articulos = $articulosIn;
        $articulos = json_decode($articulos, true);
        $claveArt = array_keys($articulos);

        foreach ($claveArt as $key => $articulo) {
            $articuloInd = explode('-', $articulo);

            $articulo = CAT_ARTICLES::where('articles_key', $articuloInd[0])->first();

            if ($articulo->articles_tariffFraction == '' || $articulo->articles_tariffFraction === null) {
                $message = 'No tiene capturado correctamente la fracción arancelaria en el artículo: ' . $articulo->articles_key;
                return $message;
            }

            if ($articulo->articles_customsUnit == '' || $articulo->articles_customsUnit === null) {
                $message = 'No tiene capturado correctamente la unidad aduana en el artículo: ' . $articulo->articles_key;
                return $message;
            }
        }
    }


    public function afectarTimbrado(Request $request)
    {

        try {
            $message = $this->validacionesFacturaGuardada(session('company')->companies_key, $request->venta);
            if ($message) {
                $message = $message;
                return response()->json(['data' => $message, 'status' => false]);
            }


            $cfdi = new TimbradoController();
            $cfdi->timbrarFactura($request->venta, $request);
            // dd($cfdi);
            $mensaje = $cfdi->getMensaje();
            $status = $cfdi->getStatus();
            // dd($status);

            if ($status == true) {

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


            return response()->json(['status' => $status, 'data' => $mensaje]);
        } catch (\Throwable $th) {
            dd($th);
            return response()->json(['status' => false, 'data' => $th->getMessage()]);
        }
    }

    public function actualizarFolio($tipoMovimiento, $folioAfectar)
    {
        $consecutivoColumn = null;

        switch ($tipoMovimiento) {
            case 'Cotización':
                $consecutivoColumn = 'generalConsecutives_consQuotation';
                break;
            case 'Pedido':
                $consecutivoColumn = 'generalConsecutives_consDemand';
                break;
            case 'Factura':
                $consecutivoColumn = 'generalConsecutives_consBill';
                break;
            case 'Rechazo de Venta':
                $consecutivoColumn = 'generalConsecutives_consBill'; // Cambia esto si es necesario
                break;
        }

        if ($consecutivoColumn !== null) {
            $consecutivo = DB::table('CONF_GENERAL_PARAMETERS_CONSECUTIVES')
            ->where('generalConsecutives_company', session('company')->companies_key)
                ->where('generalConsecutives_branchOffice', session('sucursal')->branchOffices_key)
                ->value($consecutivoColumn);

            // Verificar si ya tiene folio asignado
            if ($folioAfectar->sales_movementID === null) {
                $folioAfectar->sales_movementID = $consecutivo !== null ? $consecutivo + 1 : 1;
                $folioAfectar->update();

                DB::table('CONF_GENERAL_PARAMETERS_CONSECUTIVES')
                ->where('generalConsecutives_company', session('company')->companies_key)
                    ->where('generalConsecutives_branchOffice', session('sucursal')->branchOffices_key)
                    ->update([$consecutivoColumn => $folioAfectar->sales_movementID]);
            }
        }
    }
    public function asignarFolio(Request $request)
    {
        $folioAfectar = PROC_SALES::where('sales_id', $request->sales_id)->first();

        $this->actualizarFolio('Cotización', $folioAfectar); // Reemplaza 'Cotización' con el tipo de movimiento adecuado

        return response()->json(['status' => true, 'data' => 'Folio asignado correctamente', 'folio' => $folioAfectar->sales_movementID]);
    }


    public function armarKits(Request $request)
    {

        $articuloDefault = CAT_ARTICLES::where('articles_key', $request->articulo)->first();

        //buscar articulos pertenecientes a un kit
        $articulos = CAT_KIT_ARTICLES::where('kitArticles_articleID', $articuloDefault->articles_id)->get();

        $articulosKit = [];
        if ($articulos != null) {
            foreach ($articulos as $key => $articulo) {
                $articuloD = PROC_ARTICLES_INV::where('articlesInv_article', $articulo->kitArticles_article)->where('articlesInv_depot', $request->almacen)->where('articlesInv_companieKey', session('company')->companies_key)->where('articlesInv_branchKey', session('sucursal')->branchOffices_key)->first();

                $articuloJson = new stdClass();

                $articuloJson->articuloReferenceId = $articulo->kitArticles_id;
                $articuloJson->articulo = $articulo->kitArticles_article;
                $articuloJson->descripcion = $articulo->kitArticles_articleDesp;
                $articuloJson->cantidad = $articulo->kitArticles_cantidad;
                $articuloJson->tipo = $articulo->kitArticles_tipo;
                $articuloJson->costo = $articulo->procKit_costo;
                $articuloJson->disponible = $articuloD == null ? 0 : $articuloD->articlesInv_inventory;

                array_push($articulosKit, $articuloJson);
            }
        }

        return response()->json(['status' => true, 'data' => $articulosKit]);
    }

    public function armarKits2(Request $request)
    {

        //buscar el origen
        $ventaOrigen = PROC_SALES::where('sales_movement', $request->origen)->where('sales_movementID', $request->origenID)->where('sales_company', session('company')->companies_key)->where('sales_branchOffice', session('sucursal')->branchOffices_key)->first();

        $articuloDefault = CAT_ARTICLES::where('articles_key', $request->articulo)->first();

        $kitPredeterminado = CAT_KIT_ARTICLES::where('kitArticles_articleID', $articuloDefault->articles_id)->get();


        if ($ventaOrigen != null) {

            //buscar articulos pertenecientes a un kit
            $articulos = PROC_KIT_ARTICLES::where('procKit_saleID', $ventaOrigen->sales_id)->get();

            // dd($articulos);


            $articuloKit = [];
            if ($articulos != null) {
                foreach ($articulos as $key => $articulo) {
                    $articuloD = PROC_ARTICLES_INV::where('articlesInv_article', $articulo->procKit_articleID)->where('articlesInv_depot', $request->almacen)->where('articlesInv_companieKey', session('company')->companies_key)->where('articlesInv_branchKey', session('sucursal')->branchOffices_key)->first();


                    $articuloJson = new stdClass();

                    $articuloJson->articuloReferenceId = $articulo->procKit_id;
                    $articuloJson->articulo = $articulo->procKit_articleID;
                    $articuloJson->descripcion = $articulo->procKit_articleDesp;
                    $articuloJson->cantidad = $articulo->procKit_cantidad;
                    $articuloJson->tipo = $articulo->procKit_tipo;
                    $articuloJson->costo = $articulo->procKit_costo;
                    $articuloJson->observaciones = $articulo->procKit_observation;
                    $articuloJson->articuloRef = $articulo->procKit_article;

                    $articuloJson->disponible = $articuloD == null ? 0 : $articuloD->articlesInv_inventory;

                    if (!array_key_exists($articulo->procKit_articleID . '-' . $articulo->procKit_articleIDReference, $articuloKit)) {
                        $articuloKit[$articulo->procKit_articleID . '-' . $articulo->procKit_articleIDReference] = $articuloJson;
                    }


                    foreach ($kitPredeterminado as $key => $kit) {
                        $articuloJson = new stdClass();
                        $articuloJson->cantidad = $kit->kitArticles_cantidad;


                        if (array_key_exists($kit->kitArticles_article . '-' . $articulo->procKit_articleIDReference, $articuloKit)) {
                            $articuloKit[$kit->kitArticles_article . '-' . $articulo->procKit_articleIDReference]->cantidadDefault = $articuloJson->cantidad;
                        }
                    }
                }
            }
        }


        $articulosVenta =  PROC_KIT_ARTICLES::where('procKit_saleID', $request->idVenta)->get();

        if ($articulosVenta != null && count($articulosVenta) > 0) {

            //vaciar el objeto
            $articuloKit = [];

            foreach ($articulosVenta as $key => $articulo) {
                $articuloD = PROC_ARTICLES_INV::where('articlesInv_article', $articulo->procKit_articleID)->where('articlesInv_depot', $request->almacen)->where('articlesInv_companieKey', session('company')->companies_key)->where('articlesInv_branchKey', session('sucursal')->branchOffices_key)->first();


                $articuloJson = new stdClass();

                $articuloJson->articuloReferenceId = $articulo->procKit_id;
                $articuloJson->articulo = $articulo->procKit_articleID;
                $articuloJson->descripcion = $articulo->procKit_articleDesp;
                $articuloJson->cantidad = $articulo->procKit_cantidad;
                $articuloJson->tipo = $articulo->procKit_tipo;
                $articuloJson->costo = $articulo->procKit_costo;
                $articuloJson->observaciones = $articulo->procKit_observation;
                $articuloJson->articuloRef = $articulo->procKit_article;
                $articuloJson->disponible = $articuloD == null ? 0 : $articuloD->articlesInv_inventory;

                if (!array_key_exists($articulo->procKit_articleID, $articuloKit)) {
                    $articuloKit[$articulo->procKit_articleID] = $articuloJson;
                }
            }

            foreach ($kitPredeterminado as $key => $kit) {
                $articuloJson = new stdClass();
                $articuloJson->cantidad = $kit->kitArticles_cantidad;


                if (array_key_exists($kit->kitArticles_article, $articuloKit)) {
                    $articuloKit[$kit->kitArticles_article]->cantidadDefault = $articuloJson->cantidad;
                }
            }
        }

        // dd($kitVenta, $kitPredeterminado, $articuloKit);
        return response()->json(['status' => true, 'data' => $articuloKit]);
    }

    public function buscarKits(Request $request)
    {
        // $request->idVenta = 1;
        // $request->referencia = 1;
        // $request->articulo = 31;
        // $request->almacen = '001';

        $articuloDefault = CAT_ARTICLES::where('articles_key', $request->articulo)->first();


        $kitVenta = PROC_KIT_ARTICLES::where('procKit_saleID', $request->idVenta)->where('procKit_articleIDReference', $request->referencia)->where('procKit_article', $request->articulo)->get();


        $kitPredeterminado = CAT_KIT_ARTICLES::where('kitArticles_articleID', $articuloDefault->articles_id)->get();


        $articuloKit = [];
        foreach ($kitVenta as $key => $kit) {
            $articuloD = PROC_ARTICLES_INV::where('articlesInv_article', $kit->procKit_articleID)->where('articlesInv_depot', $request->almacen)->where('articlesInv_companieKey', session('company')->companies_key)->where('articlesInv_branchKey', session('sucursal')->branchOffices_key)->first();

            $articuloJson = new stdClass();
            $articuloJson->articuloReferenceId = $kit->procKit_id;
            $articuloJson->articulo = $kit->procKit_articleID;
            $articuloJson->descripcion = $kit->procKit_articleDesp;
            $articuloJson->cantidad = $kit->procKit_cantidad;
            $articuloJson->tipo = $kit->procKit_tipo;
            $articuloJson->observaciones = $kit->procKit_observation;
            $articuloJson->disponible = $articuloD == null ? 0 : $articuloD->articlesInv_inventory;

            if (!array_key_exists($kit->procKit_articleID, $articuloKit)) {
                $articuloKit[$kit->procKit_articleID] = $articuloJson;
            }
        }

        foreach ($kitPredeterminado as $key => $kit) {

            $articuloJson = new stdClass();
            $articuloJson->cantidad = $kit->kitArticles_cantidad;

            if (array_key_exists($kit->kitArticles_article, $articuloKit)) {
                $articuloKit[$kit->kitArticles_article]->cantidadDefault = $articuloJson->cantidad;
            }
        }


        if ($kitVenta == null || count($kitVenta) == 0) {

            $kitPredeterminado = CAT_KIT_ARTICLES::where('kitArticles_articleID', $articuloDefault->articles_id)->get();


            $articuloKit = [];
            foreach ($kitPredeterminado as $key => $kit) {
                $articuloD = PROC_ARTICLES_INV::where('articlesInv_article', $kit->kitArticles_article)->where('articlesInv_depot', $request->almacen)->where('articlesInv_companieKey', session('company')->companies_key)->where('articlesInv_branchKey', session('sucursal')->branchOffices_key)->first();

                $articuloJson = new stdClass();
                $articuloJson->articuloReferenceId = $kit->kitArticles_id;
                $articuloJson->articulo = $kit->kitArticles_article;
                $articuloJson->descripcion = $kit->kitArticles_articleDesp;
                $articuloJson->cantidad = $kit->kitArticles_cantidad;
                $articuloJson->tipo = $kit->kitArticles_tipo;
                $articuloJson->observaciones = '';
                $articuloJson->disponible = $articuloD == null ? 0 : $articuloD->articlesInv_inventory;

                if (!array_key_exists($kit->kitArticles_article, $articuloKit)) {
                    $articuloKit[$kit->kitArticles_article] = $articuloJson;
                }
            }

            foreach ($kitPredeterminado as $key => $kit) {

                $articuloJson = new stdClass();
                $articuloJson->cantidad = $kit->kitArticles_cantidad;

                if (array_key_exists($kit->kitArticles_article, $articuloKit)) {
                    $articuloKit[$kit->kitArticles_article]->cantidadDefault = $articuloJson->cantidad;
                }
            }
        }



        // dd($kitVenta, $kitPredeterminado, $articuloKit);
        return response()->json(['status' => true, 'data' => $articuloKit]);
    }

    public function getConceptosByMovimiento(Request $request)
    {
        $movimientoSeleccionado = $request->input('movimiento');
        // dd($movimientoSeleccionado);
        if ($movimientoSeleccionado === null) {
            $conceptos = CONF_MODULES_CONCEPT::where('moduleConcept_status', '=', 'Alta')
                ->where('moduleConcept_module', '=', 'Ventas')
                ->get();
        } else {
            $conceptos = CONF_MODULES_CONCEPT::join('CONF_MODULES_CONCEPT_MOVEMENT', 'CONF_MODULES_CONCEPT_MOVEMENT.moduleMovement_conceptID', '=', 'CONF_MODULES_CONCEPT.moduleConcept_id')
                ->where('moduleConcept_status', '=', 'Alta')
                ->where('moduleConcept_module', '=', 'Ventas')
                ->where('moduleMovement_movementName', '=', $movimientoSeleccionado)
                ->get();
            // dd($conceptos);
        }
        return response()->json($conceptos);
    }


    public function selectCategoria()
    {
        $categoria_select = [];
        $categoria_collection = CAT_ARTICLES_CATEGORY::where('categoryArticle_status', 'Alta')->get();
        $categoria_array = $categoria_collection->toArray();

        foreach ($categoria_array as $key => $value) {
            $categoria_select[$value['categoryArticle_name']] = $value['categoryArticle_name'];
        }
        return $categoria_select;
    }

    public function selectGrupo()
    {
        $grupo_select = [];
        $grupo_collection = CAT_ARTICLES_GROUP::where('groupArticle_status', 'Alta')->get();
        $grupo_array = $grupo_collection->toArray();
        foreach ($grupo_array as $key => $value) {
            $grupo_select[$value['groupArticle_name']] = $value['groupArticle_name'];
        }
        return $grupo_select;
    }

    public function selectFamilia()
    {
        $familia_select = [];
        $familia_collection = CAT_ARTICLES_FAMILY::where('familyArticle_status', 'Alta')->get();
        $familia_array = $familia_collection->toArray();
        foreach ($familia_array as $key => $value) {
            $familia_select[$value['familyArticle_name']] = $value['familyArticle_name'];
        }
        return $familia_select;
    }
}