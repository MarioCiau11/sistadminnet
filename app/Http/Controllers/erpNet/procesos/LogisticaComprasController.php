<?php

namespace App\Http\Controllers\erpNet\procesos;

use App\Exports\PROC_ComprasExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_ARTICLES_CATEGORY;
use App\Models\agrupadores\CAT_ARTICLES_FAMILY;
use App\Models\agrupadores\CAT_ARTICLES_GROUP;
use App\Models\agrupadores\CAT_ARTICLES_LIST;
use App\Models\agrupadores\CAT_PROVIDER_LIST;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_ARTICLES_UNITS;
use App\Models\catalogos\CAT_PROVIDERS;
use App\Models\catalogos\CONF_MODULES_CONCEPT;
use App\Models\catalogos\CONF_MONEY;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_DEPOTS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_CREDIT_CONDITIONS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS_CONSECUTIVES;
use App\Models\catalogos\CONF_REASON_CANCELLATIONS;
use App\Models\catalogos\CONF_UNITS;
use App\Models\modulos\helpers\PROC_ARTICLES_COST;
use App\Models\modulos\helpers\PROC_ARTICLES_COST_HIS;
use App\Models\modulos\PROC_ACCOUNTS_PAYABLE;
use App\Models\modulos\PROC_ACCOUNTS_PAYABLE_P;
use App\Models\modulos\PROC_ARTICLES_INV;
use App\Models\modulos\PROC_LOT_SERIES;
use App\Models\modulos\PROC_ASSISTANT;
use App\Models\modulos\PROC_ASSISTANT_UNITS;
use App\Models\modulos\PROC_BALANCE;
use App\Models\modulos\PROC_DEL_SERIES_MOV;
use App\Models\modulos\PROC_DEL_SERIES_MOV2;
use App\Models\modulos\PROC_LOT_SERIES_MOV;
use App\Models\modulos\PROC_MOVEMENT_FLOW;
use App\Models\modulos\PROC_PURCHASE;
use App\Models\modulos\PROC_PURCHASE_DETAILS;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class LogisticaComprasController extends Controller
{

    public $estatus = [
        0 => 'INICIAL',
        1 => 'POR AUTORIZAR',
        2 => 'FINALIZADO',
        3 => 'CANCELADO',
    ];



    public function index()
    {
        $select_users = $this->selectUsuarios();
        $select_sucursales = $this->selectSucursales();
        $selectMonedas = $this->getMonedas();
        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();

        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }

        $compras = PROC_PURCHASE::join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
            ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CONF_CREDIT_CONDITIONS', 'PROC_PURCHASE.purchase_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
            ->join('CAT_COMPANIES', 'PROC_PURCHASE.purchase_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('CAT_DEPOTS', 'PROC_PURCHASE.purchase_depot', '=', 'CAT_DEPOTS.depots_key')
            ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
            ->where('PROC_PURCHASE.purchase_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->when($parametro->generalParameters_defaultMoney, function ($query, $parametro) {
                return $query->where('PROC_PURCHASE.purchase_money', '=', $parametro);
            }, function ($query) {
                return $query;
            })
            ->where('PROC_PURCHASE.purchase_user', '=', Auth::user()->username)
            ->orderBy('PROC_PURCHASE.created_at', 'DESC')
            ->get();



        // $compras = PROC_PURCHASE::where('purchase_status', '=', 'INICIAL')->orderBy('purchase_id', 'desc')->get();

        return view('page.modulos.logistica.compras.index-compras', compact('select_users', 'select_sucursales', 'selectMonedas', 'parametro', 'compras'));
    }

    public function create(Request $request)
    {

        // dd(session('company'));

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro->generalParameters_defaultMoney == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de seleccionar la moneda por defecto');
        }
        try {

            //Obtenemos los permisos que tiene el usuario para el modulo de compras
            $usuario = Auth::user();
            $permisos = $usuario->getAllPermissions()->where('categoria', '=', 'Compras')->pluck('name')->toArray();

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

            $select_categoria = $this->selectCategoria();
            $select_grupo = $this->selectGrupo();
            $select_familia = $this->selectFamilia();
            $select_motivos = CONF_REASON_CANCELLATIONS::WHERE('reasonCancellations_status', '=', 'Alta')->WHERE('reasonCancellations_module', '=', 'Compras')->get();
            // dd($request->all());
            $select_conceptos = CONF_MODULES_CONCEPT::WHERE('moduleConcept_status', '=', 'Alta')->WHERE('moduleConcept_module', '=', 'Compras')->get();
            $listaPreciosProveedor = CAT_PROVIDER_LIST::WHERE('listProvider_status', '=', 'Alta')->get();
            $fecha_actual = Carbon::now()->format('Y-m-d');
            $selectMonedas = $this->getMonedas2();
            $parametro = CONF_GENERAL_PARAMETERS::join('CONF_MONEY', 'CONF_GENERAL_PARAMETERS.generalParameters_defaultMoney', '=', 'CONF_MONEY.money_key')
                ->select('CONF_GENERAL_PARAMETERS.*', 'CONF_MONEY.money_change')
                ->where('CONF_GENERAL_PARAMETERS.generalParameters_company', '=', session('company')->companies_key)
                ->first();
            $select_condicionPago = CONF_CREDIT_CONDITIONS::WHERE('creditConditions_status', '=', 'Alta')->get();
            $articulos = CAT_ARTICLES::WHEREIN('articles_type', ['Normal', 'Serie'])->WHERE('articles_status', '=', 'Alta')->get();
            $proveedores = CAT_PROVIDERS::WHERE('providers_status', '=', 'Alta')->get();

            //Obtenemos todos los almacenes relacionados a la sucursal y empresa
            $almacenes = CAT_DEPOTS::join('CAT_BRANCH_OFFICES', 'CAT_DEPOTS.depots_branchlId', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                ->join('CAT_COMPANIES', 'CAT_BRANCH_OFFICES.branchOffices_companyId', '=', 'CAT_COMPANIES.companies_key')
                ->where('CAT_COMPANIES.companies_key', '=', session('company')->companies_key)
                ->where('CAT_BRANCH_OFFICES.branchOffices_key', '=', session('sucursal')->branchOffices_key)
                ->where('CAT_DEPOTS.depots_status', '=', 'Alta')
                ->select('CAT_DEPOTS.*')
                ->get();

            // dd($almacenes, session('company')->companies_key, session('sucursal')->branchOffices_key);

            $unidad = $this->getConfUnidades();

            if (isset($request->id)) {
                $compra = PROC_PURCHASE::find($request->id);

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
                if (!$usuario->can($compra->purchase_movement . ' C')) {

                    return redirect()->route('vista.modulo.compras')->with('status', false)->with('message', 'No tiene permisos para visualizar este movimiento');
                }



                $nameProveedor = CAT_PROVIDERS::where('providers_key', '=', $compra->purchase_provider)->first();
                $articulosByCompra = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $compra->purchase_id)->get();

                if ($articulosByCompra->count() > 0) {
                    if ($nameProveedor->providers_priceList != null) {
                        $listaProveedor = CAT_ARTICLES_LIST::where('articlesList_listID', '=', $nameProveedor->providers_priceList)->where('articlesList_article', '=',  $articulosByCompra[0]->purchaseDetails_article)->first();
                    } else {
                        $listaProveedor = null;
                    }
                } else {
                    $listaProveedor = null;
                }

                $primerFlujoDeCompra = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
                    ->WHERE('movementFlow_originID', '=', $compra->purchase_id)
                    ->WHERE('movementFlow_movementOriginID', '=', $compra->purchase_movementID)
                    ->WHERE('movementFlow_moduleOrigin', '=', 'Compras')
                    ->get();


                if (count($primerFlujoDeCompra) === 0) {
                    $primerFlujoDeCompra = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
                        ->WHERE('movementFlow_destinityID', '=', $compra->purchase_id)
                        ->WHERE('movementFlow_movementDestinityID', '=', $compra->purchase_movementID)
                        ->WHERE('movementFlow_moduleDestiny', '=', 'Compras')
                        ->get();
                }


                if (count($articulosByCompra) != 0) {
                    $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $articulosByCompra[0]->purchaseDetails_article)->join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->join('PROC_ARTICLES_COST', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_COST.articlesCost_article')->join('CONF_UNITS', 'CAT_ARTICLES.articles_unitBuy', '=', 'CONF_UNITS.units_id')->join('CAT_DEPOTS', 'PROC_ARTICLES_COST.articlesCost_depotKey', '=', 'CAT_DEPOTS.depots_key')->where('articlesCost_branchKey', '=', $compra->purchase_branchOffice)->where('articlesCost_depotKey', '=', $compra->purchase_depot)->where('PROC_ARTICLES_INV.articlesInv_depot', '=', $compra->purchase_depot)->where('PROC_ARTICLES_INV.articlesInv_branchKey', '=', $compra->purchase_branchOffice)->first();

                    $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $articulosByCompra[0]->purchaseDetails_article)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->get();

                    if ($infoArticulo == null) {
                        $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $articulosByCompra[0]->purchaseDetails_article)->first();
                        $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $articulosByCompra[0]->purchaseDetails_article)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->get();
                    }
                } else {
                    $infoArticulo = null;
                    $articulosByAlmacen = null;
                }

                //   dd($infoArticulo, $articulosByAlmacen);

                $infoProveedor = CAT_PROVIDERS::join('CONF_CREDIT_CONDITIONS', 'CAT_PROVIDERS.providers_creditCondition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id', 'left outer')
                ->where('providers_key', '=', $compra->purchase_provider)->first();
                // dd($infoProveedor->listaProveedores);



                // dd($infoProveedor);

                $monedasMov = CONF_MONEY::where('money_status', '=', 'Alta')->orderBy('money_key', 'desc')->get();

                $movimientosProveedor = PROC_ACCOUNTS_PAYABLE::whereIn("accountsPayable_movement", ["Entrada por Compra", "Factura de Gasto", "Anticipo"])->where('accountsPayable_provider', '=', $compra->purchase_provider)->where("accountsPayable_balance", ">", '0')->where("accountsPayable_company", "=", session('company')->companies_key)->where("accountsPayable_status", "=", 'POR AUTORIZAR')->get();

                // dd($top5Productos);
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
                
                $ultimoPago = PROC_ACCOUNTS_PAYABLE::whereIn("accountsPayable_movement", ["Aplicación", "Anticipo", "Pago de Facturas"])->where('accountsPayable_provider', '=', $compra->purchase_provider)->where("accountsPayable_company", "=", session('company')->companies_key)->where("accountsPayable_status", "=", 'FINALIZADO')->orderBy('updated_at', 'desc')->first();
                // dd($ultimoPago);
                $startDate = Carbon::now()->subDays(60); // Fecha de inicio hace 60 días
                $endDate = Carbon::now(); // Fecha actual
                //le damos formato a las fechas
                $startDate = $startDate->format('Y-m-d');
                $endDate = $endDate->format('Y-m-d');
                // dd($startDate, $endDate);
                $top5Productos = PROC_PURCHASE::join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
                ->where('PROC_PURCHASE.purchase_provider', '=', $compra->purchase_provider)
                    ->where('PROC_PURCHASE.purchase_status', '=', 'FINALIZADO')
                    ->whereBetween('PROC_PURCHASE.purchase_issueDate', [$startDate, $endDate])
                    ->select('PROC_PURCHASE_DETAILS.purchaseDetails_article', 'purchaseDetails_descript', DB::raw('sum(PROC_PURCHASE_DETAILS.purchaseDetails_quantity) as total'))
                    ->groupBy('PROC_PURCHASE_DETAILS.purchaseDetails_article', 'purchaseDetails_descript')
                    ->orderBy('total', 'desc')
                    ->limit(5)
                    ->get();
                //  dd($compra, $articulosByCompra);

                return view('page.modulos.logistica.compras.create-compras', compact('selectMonedas', 'select_conceptos', 'fecha_actual', 'select_condicionPago', 'proveedores', 'parametro', 'almacenes', 'articulos', 'unidad', 'compra', 'nameProveedor', 'articulosByCompra', 'primerFlujoDeCompra', 'infoArticulo', 'articulosByAlmacen', 'infoProveedor', 'monedasMov', 'movimientosProveedor', 'saldoGeneral', 'movimientos', 'select_motivos', 'usuario', 'listaPreciosProveedor', 'listaProveedor', 'select_categoria', 'select_grupo', 'select_familia', 'ultimoPago', 'top5Productos'));
            } else {
                $compra = null;
                $nameProveedor = null;
                $articulosByCompra = null;
            }

            return view('page.modulos.logistica.compras.create-compras', compact('selectMonedas', 'select_conceptos', 'fecha_actual', 'select_condicionPago', 'proveedores', 'parametro', 'almacenes', 'articulos', 'unidad', 'compra', 'nameProveedor', 'articulosByCompra', 'movimientos', 'select_motivos', 'usuario', 'listaPreciosProveedor', 'select_categoria', 'select_grupo', 'select_familia'));
        } catch (\Exception $e) {
            return redirect()->route('vista.modulo.compras')->with('status', false)->with('message', 'La compra no se ha encontrado. Por favor, vaya con el administrador del sistema y reporte este error: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {


        $compras_request = $request->except('_token');
        // dd($compras_request);

        $id = $request->id;
        $copiaRequest = $request->copiar;
        if ($id == 0 || $copiaRequest == 'copiar') {
            $compra = new PROC_PURCHASE();
            $compra->purchase_user = Auth::user()->username;
        } else {
            $compra = PROC_PURCHASE::where('purchase_id', $id)->first();

            if ($compra->purchase_originID ===  Null) {
                $compra->purchase_user = Auth::user()->username;
            }
        }
        // $request->fechaEmision;

        $compra->purchase_movement = $compras_request['movimientos'];
        $compra->purchase_issueDate = \Carbon\Carbon::now();
        $compra->purchase_concept = $compras_request['concepto'];
        $compra->purchase_money = $compras_request['nameMoneda'];
        $compra->purchase_typeChange = $compras_request['nameTipoCambio'];
        $compra->purchase_provider = $compras_request['proveedorKey'];
        $compra->purchase_condition = $compras_request['proveedorCondicionPago'];
        $compra->purchase_expiration = $compras_request['proveedorFechaVencimiento'];
        $compra->purchase_reference = $compras_request['proveedorReferencia'];
        $compra->purchase_company = session('company')->companies_key;
        $compra->purchase_branchOffice = session('sucursal')->branchOffices_key;
        $compra->purchase_depot = $compras_request['almacenKey'];
        $compra->purchase_depotType = $compras_request['almacenTipoKey'];
        $compra->purchase_reasonCancellation = $compras_request['motivoCancelacion'];
        $compra->purchase_status = $this->estatus[0];
        $compra->purchase_listPriceProvider = $compras_request['listaProveedor'];

        $compra->purchase_amount = $compras_request['subTotalCompleto'];
        $compra->purchase_taxes = $compras_request['impuestosCompleto'];
        $compra->purchase_total = $compras_request['totalCompleto'];
        $compra->purchase_lines = $compras_request['cantidadArticulos'];

        //origen de la compra


        if ($compras_request['folio'] != null) {
            if ($copiaRequest == 'copiar') {
                $compra->purchase_originType = null;
                $compra->purchase_origin = null;
                $compra->purchase_originID = null;
            } else {
                $compra->purchase_originType = 'COMPRAS';
                $compra->purchase_origin = 'Orden de Compra';
                $compra->purchase_originID = $compras_request['folio'];
            }
        } else {
            if ($copiaRequest == 'copiar') {
                $compra->purchase_originType = null;
                $compra->purchase_origin = null;
                $compra->purchase_originID = null;
            } else {
                if ($compra->purchase_originID ===  Null) {
                    $compra->purchase_originType = 'Usuario';
                    $compra->purchase_origin = Auth::user()->username;
                    $compra->purchase_originID = null;
                }
            }
        }

        //Informacion adicional
        // $compra->purchase_ticket = $compras_request['folioTicket'];
        // $compra->purchase_operator = $compras_request['operador'];
        // $compra->purchase_plates = $compras_request['placas'];
        // $compra->purchase_material = $compras_request['material'];
        // $compra->purchase_inputWeight = $compras_request['pesoEntrada'];

        // $fechaHora = $compras_request['fechaHoraEntrada'];
        // $fechaHora2 = $compras_request['fechaHoraSalida'];

        // if ($fechaHora != NULL && $fechaHora2 != NULL) {
        //     $fechaHora = Carbon::parse($fechaHora)->format('Y-m-d H:i:s');
        //     $fechaHora2 = Carbon::parse($fechaHora2)->format('Y-m-d H:i:s');
        // }

        // $compra->purchase_inputDateTime = $fechaHora;
        // $compra->purchase_outputWeight = $compras_request['pesoSalida'];
        // $compra->purchase_outputDateTime = $fechaHora2;
        // $compra->created_at = Carbon::now()->format('Y-m-d H:i:s');
        // $compra->updated_at = Carbon::now()->format('Y-m-d H:i:s');



        //insertar articulos en la tabla de detalle de compra

        $articulos = $compras_request['dataArticulosJson'];
        $articulos = json_decode($articulos, true);

        $claveArt = array_keys($articulos);
        //Eliminamos los articulos que no sean necesarios        
        $articulosDelete = json_decode($compras_request['dataArticulosDelete'], true);


        if ($articulosDelete  != null) {
            foreach ($articulosDelete as $articulo) {
                $detalleCompra = PROC_PURCHASE_DETAILS::where('purchaseDetails_id', $articulo)->first();
                $detalleCompra->delete();
            }
        }

        //dd($claveArt);
        // dd($articulos[$claveArt[0]]);


        try {
            if ($id == 0) {
                $isCreate =  $compra->save();
                $lastId = $compra::latest('purchase_id')->first()->purchase_id;
            } else {
                $isCreate =  $compra->update();
                $lastId = $compra->purchase_id;
            }

            // dd($articulos);
            // dd($lastId);
            // dd($articulos);

            //  foreach ($claveArt as $articulo) {
            //     if($articulos[$articulo]['tipoArticulo'] == "Serie" && $compra->purchase_movement == "Entrada por Compra"){ 
            //          //Eliminamos los articulos que tengan la misma clave, la misma id de la compra y la misma sucursal
            //         $articuloClave = explode('-', $articulo);
            //          PROC_LOT_SERIES_MOV::where('lotSeriesMov_companieKey','=', $compra->purchase_company)->where('lotSeriesMov_branchKey', '=',$compra->purchase_branchOffice)->where('lotSeriesMov_purchaseID', "=", $lastId)->where('lotSeriesMov_article', "=", $articuloClave[0])->delete();
            //      }
            //  }

            //  //Resetamos los valores por defecto del lotSeriesMov_id

            if ($articulos !== null) {
                //Creamos un arreglo donde se almacenara las seriesAsignadas
                $asignacionSeriesB['series'] = [];
                $asignacionIdsSerieB['idSeries'] = [];

                foreach ($claveArt as $keyItemArt => $articulo) {
                    if (isset($articulos[$articulo]['id'])) {
                        $detalleCompra = PROC_PURCHASE_DETAILS::where('purchaseDetails_id', '=', $articulos[$articulo]['id'])->first();
                    } else {
                        $detalleCompra = new PROC_PURCHASE_DETAILS();
                    }
                    $detalleCompra->purchaseDetails_purchaseID = $lastId;
                    $articuloClave = explode('-', $articulo);

                    $detalleCompra->purchaseDetails_article = $articuloClave[0];
                    $detalleCompra->purchaseDetails_type = $articulos[$articulo]['tipoArticulo'];
                    $detalleCompra->purchaseDetails_descript = $articulos[$articulo]['desp'];
                    $detalleCompra->purchaseDetails_quantity = str_replace(['$', ','], '', $articulos[$articulo]['cantidad']);
                    $detalleCompra->purchaseDetails_unitCost = str_replace(['$', ','], '', $articulos[$articulo]['c_unitario']);

                    $unidadDiv = explode('-', $articulos[$articulo]['unidad']);
                    $detalleCompra->purchaseDetails_unit = $unidadDiv[0];
                    $detalleCompra->purchaseDetails_factor = $unidadDiv[1];

                    $detalleCompra->purchaseDetails_inventoryAmount = str_replace(['$', ','], '', $articulos[$articulo]['c_Inventario']);
                    $detalleCompra->purchaseDetails_amount = str_replace(['$', ','], '',  $articulos[$articulo]['importe']);
                    $detalleCompra->purchaseDetails_ivaPorcent = str_replace(['$', ','], '', $articulos[$articulo]['iva']);
                    $detalleCompra->purchaseDetails_discountPorcent = $articulos[$articulo]['porcentajeDescuento'];
                    $detalleCompra->purchaseDetails_discount = $articulos[$articulo]['descuento'];
                    $detalleCompra->purchaseDetails_total = str_replace(['$', ','], '', $articulos[$articulo]['importe_total']);


                    if ($compras_request['folio'] != null || isset($articulos[$articulo]['aplicaIncre'])) {
                        if ($copiaRequest == 'copiar') {
                            $detalleCompra->purchaseDetails_apply = null;
                            $detalleCompra->purchaseDetails_applyIncrement = null;
                        } else {
                            $detalleCompra->purchaseDetails_apply = 'Orden de Compra';

                            if ($compras_request['folio'] != null) {
                                $detalleCompra->purchaseDetails_applyIncrement = $compras_request['folio'];
                            }
                            if (isset($articulos[$articulo]['aplicaIncre'])) {
                                $detalleCompra->purchaseDetails_applyIncrement = $articulos[$articulo]['aplicaIncre'];
                            }
                        }
                    } else {
                        $detalleCompra->purchaseDetails_apply = null;
                        $detalleCompra->purchaseDetails_applyIncrement = null;
                    }

                    $detalleCompra->purchaseDetails_branchOffice = session('sucursal')->branchOffices_key;
                    $detalleCompra->purchaseDetails_depot = $compras_request['almacenKey'];
                    $detalleCompra->purchaseDetails_outstandingAmount = null;
                    $detalleCompra->purchaseDetails_canceledAmount = null;

                    if ($detalleCompra->purchaseDetails_referenceArticles === null) {
                        $detalleCompra->purchaseDetails_referenceArticles = isset($articulos[$articulo]['referenceArticle']) ? $articulos[$articulo]['referenceArticle'] : null; //Recuperamos el id del articulo de referencia
                    }

                    $detalleCompra->save();

                    if ($copiaRequest != 'copiar') {
                        if ($articulos[$articulo]['tipoArticulo'] == "Serie" && $compra->purchase_movement == "Entrada por Compra") {
                            $lastIdDetalle = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $lastId)->where('purchaseDetails_article', '=', $articuloClave[0])->select('purchaseDetails_id')->first();

                            $articuloFila = $articuloClave[0] . '-' . $keyItemArt;
                            $series = [];
                            $idSeries = [];

                            //Agregamos los articulos de serie a la tabla PROC_LOT_SERIES_MOV;
                            if (isset($articulos[$articulo]['asignacionSerie'])) {
                                foreach ($articulos[$articulo]['asignacionSerie'] as $key => $value) {
                                    if (isset($articulos[$articulo]['asignacionIdsSerie'])) {
                                        if ($key <=  (count($articulos[$articulo]['asignacionIdsSerie']) - 1)) {
                                            $proc_lot_series_mov = PROC_LOT_SERIES_MOV::where('lotSeriesMov_companieKey', '=', $compra->purchase_company)->where('lotSeriesMov_branchKey', '=', $compra->purchase_branchOffice)->where('lotSeriesMov_purchaseID', "=", $lastId)->where('lotSeriesMov_article', "=", $articuloClave[0])->where('lotSeriesMov_id', "=", $articulos[$articulo]['asignacionIdsSerie'][$key])->first();
                                        } else {
                                            $proc_lot_series_mov = new PROC_LOT_SERIES_MOV();
                                            $proc_lot_series_mov->lotSeriesMov_articleID =  $lastIdDetalle['purchaseDetails_id']; // PROC_PURCHASE_DETAILS::latest('purchaseDetails_id')->first()->purchaseDetails_id;
                                        }
                                    } else {
                                        $proc_lot_series_mov = new PROC_LOT_SERIES_MOV();
                                        $proc_lot_series_mov->lotSeriesMov_articleID =  $lastIdDetalle['purchaseDetails_id'];  // PROC_PURCHASE_DETAILS::latest('purchaseDetails_id')->first()->purchaseDetails_id;
                                    }




                                    $proc_lot_series_mov->lotSeriesMov_companieKey =  $compra->purchase_company;
                                    $proc_lot_series_mov->lotSeriesMov_branchKey = $compra->purchase_branchOffice;
                                    $proc_lot_series_mov->lotSeriesMov_module = 'COMPRAS';
                                    $proc_lot_series_mov->lotSeriesMov_purchaseID = $lastId;
                                    $proc_lot_series_mov->lotSeriesMov_article = $articuloClave[0];
                                    $proc_lot_series_mov->lotSeriesMov_lotSerie = $value;
                                    $proc_lot_series_mov->lotSeriesMov_quantity = 1;


                                    $proc_lot_series_mov->save();

                                    //Obtenemos el ultimo id o el id actualizado de la serie
                                    $lastIdSeries = isset($proc_lot_series_mov->lotSeriesMov_id) ? $proc_lot_series_mov->lotSeriesMov_id : PROC_LOT_SERIES_MOV::latest('lotSeriesMov_id')->first();

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
                        }
                    }
                }
            }

            //Formamos nuestro json de series y idseries
            //Obtenemos las keys de un arreglo

            if ($copiaRequest != 'copiar') {
                if (count($asignacionSeriesB['series']) > 0) {
                    $contenedor = array_merge($asignacionSeriesB, $asignacionIdsSerieB);
                    $jsonDetalle = json_encode($contenedor);

                    if ($lastId != 0) {
                        $compra = PROC_PURCHASE::WHERE('purchase_id', '=', $lastId)->first();
                        $compra->purchase_jsonData = $jsonDetalle;
                        $compra->save();
                    }
                }
            }


            if ($isCreate) {
                $message = $id == 0 ? 'Compra creada correctamente' : 'Compra actualizada correctamente';
                $status = true;
            } else {
                $message = $id == 0 ? 'Error al crear la compra' : 'Error al actualizar la compra';
                $status = false;
            }
        } catch (\Throwable $th) {
            dd($th);
            $message = $id == 0 ? "Por favor, vaya con el administrador de sistemas, no se pudo crear la compra" : "Por favor, vaya con el administrador de sistemas, no se pudo actualizar la compra";
            return redirect()->route('vista.modulo.compras.create-compra')->with('message', $message)->with('status', false);
        }

        return redirect()->route('vista.modulo.compras.create-compra', $id == 0 ? $lastId : $id)->with('message', $message)->with('status', $status);
    }

    public function afectar(Request $request)
    {
        $id = $request->id;
        // dd($request->all());
        // if ($request['movimientos'] === "Entrada por Compra") {

        //     $isAlmacen = $this->verificacionAlmacen($request);

        //     if ($isAlmacen) {
        //         $message = 'No se puede afectar la entrada, el almacén no es de activos fijos';
        //         $status = 500;
        //         $lastId = false;
        //         return response()->json(['mensaje' => $message, 'estatus' => $status]);
        //     }
        // }
        if ($id != 0) {
            //buscamo la compra
            $compra = PROC_PURCHASE::where('purchase_id', '=', $id)->first();
            if ($compra != null) {
                if ($compra->purchase_originID != null) {

                    //buscamos la compra orgigen 
                    $compraOrigen = PROC_PURCHASE::where('purchase_movementID', '=', $compra->purchase_originID)->where('purchase_movement', '=', $compra->purchase_origin)->where('purchase_company', '=', $compra->purchase_company)->where('purchase_branchOffice', '=', $compra->purchase_branchOffice)->first();

                    if ($compraOrigen != null) {

                        if ($compraOrigen->purchase_status == "FINALIZADO") {
                            $message = 'No se puede afectar la compra, la compra origen ya esta afectada';
                            $status = 500;
                            $lastId = false;
                            return response()->json(['mensaje' => $message, 'estatus' => $status]);
                        }
                    }
                }
            }
        }
        $isRepSerie = $this->verificacionArticulosSerie($request);

        if (!$isRepSerie) {
            if ($id == 0) {
                $compra = new PROC_PURCHASE();
            } else {
                $compra = PROC_PURCHASE::where('purchase_id', $request->id)->first();
            }

            $movimientoIn = $request->movimientos;

            $compra->purchase_movement = $request->movimientos;
            $compra->purchase_issueDate = $request->fechaEmision;
            $compra->purchase_concept = $request->concepto;
            $compra->purchase_money = $request->nameMoneda;
            $compra->purchase_typeChange = $request->nameTipoCambio;
            $compra->purchase_provider = $request->proveedorKey;
            $compra->purchase_condition = $request->proveedorCondicionPago;
            $compra->purchase_expiration = $request->proveedorFechaVencimiento;
            // $vencimiento2 = Carbon::parse($vencimiento)->format('Y-m-d');

            $compra->purchase_reference = $request->proveedorReferencia;
            $compra->purchase_company = session('company')->companies_key;
            $compra->purchase_branchOffice = session('sucursal')->branchOffices_key;
            $compra->purchase_depot = $request->almacenKey;
            $compra->purchase_reasonCancellation = $request->motivoCancelacion;
            $compra->purchase_depotType = $request['almacenTipoKey'];
            $compra->purchase_user = Auth::user()->username;
            $compra->purchase_listPriceProvider = $request->listaProveedor;
            switch ($movimientoIn) {
                case 'Orden de Compra':
                    $compra->purchase_status = $this->estatus[1]; //AFECTADA POR AUTORIZAR
                    break;

                case 'Entrada por Compra':
                    $compra->purchase_status = $this->estatus[2]; //AFECTADA CONCLUIDA
                    break;
                case 'Rechazo de Compra':
                    $compra->purchase_status = $this->estatus[2]; //AFECTADA CONCLUIDA
                    break;
                default:
                    # code...
                    break;
            }
            $compra->purchase_amount = $request->subTotalCompleto;
            $compra->purchase_taxes = $request->impuestosCompleto;
            $compra->purchase_total = $request->totalCompleto;
            $compra->purchase_lines = $request->cantidadArticulos;
            //Informacion adicional
            $compra->purchase_ticket = $request->folioTicket;
            $compra->purchase_operator = $request->operador;
            $compra->purchase_plates = $request->placas;
            $compra->purchase_material = $request->material;
            $compra->purchase_inputWeight = $request->pesoEntrada;

            $fechaHora = $request->fechaHoraEntrada;
            $fechaHora2 = $request->fechaHoraSalida;

            if ($fechaHora != NULL && $fechaHora2 != NULL) {
                $fechaHora = Carbon::parse($fechaHora)->format('Y-m-d H:i:s');
                $fechaHora2 = Carbon::parse($fechaHora2)->format('Y-m-d H:i:s');
            }

            $compra->purchase_inputDateTime = $fechaHora;
            $compra->purchase_outputWeight = $request->pesoSalida;
            $compra->purchase_outputDateTime = $fechaHora2;
            $compra->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $compra->updated_at = Carbon::now()->format('Y-m-d H:i:s');


            //insertar articulos en la tabla de detalle de compra
            $articulos = $request->dataArticulosJson;
            $compra->purchase_jsonData = $request->dataArticulosJson;
            $articulos = json_decode($articulos, true);
            $claveArt = array_keys($articulos);

            //Eliminamos los articulos que no sean necesarios        
            $articulosDelete = json_decode($request['dataArticulosDelete'], true);


            if ($articulosDelete  != null) {
                foreach ($articulosDelete as $articulo) {
                    $detalleCompra = PROC_PURCHASE_DETAILS::where('purchaseDetails_id', $articulo)->first();
                    $detalleCompra->delete();
                }
            }
            try {
                if ($id == 0) {
                    $isCreate =  $compra->save();
                    $lastId = $compra::latest('purchase_id')->first()->purchase_id;
                } else {
                    $isCreate = $compra->update();
                    $lastId = $compra->purchase_id;
                }

                $folioAfectar = PROC_PURCHASE::where('purchase_id', $lastId)->first();
                $tipoMovimiento = $folioAfectar->purchase_movement;

                // switch ($tipoMovimiento) {

                //     case 'Orden de Compra':
                //         $folioOrden = PROC_PURCHASE::where('purchase_movement', '=', 'Orden de Compra')->where('purchase_branchOffice', '=', $folioAfectar->purchase_branchOffice)->max('purchase_movementID');
                //         $folioOrden = $folioOrden == null ? 1 : $folioOrden + 1;
                //         $folioAfectar->purchase_movementID = $folioOrden;
                //         //origen de la compra
                //         $folioAfectar->purchase_originType = 'Usuario';
                //         $folioAfectar->purchase_origin = Auth::user()->username;
                //         $folioAfectar->purchase_originID = null;
                //         $folioAfectar->update();
                //         break;
                //     case 'Entrada por Compra':
                //         $folioOrden = PROC_PURCHASE::where('purchase_movement', '=', 'Entrada por Compra')->where('purchase_branchOffice', '=', $folioAfectar->purchase_branchOffice)->max('purchase_movementID');
                //         $folioOrden = $folioOrden == null ? 1 : $folioOrden + 1;

                //         //si la entrada de compra viene de una orden de compra concluida no permite afectarla
                //         $folioAfectar->purchase_movementID = $folioOrden;
                //         //origen de la compra
                //         if ($folioAfectar->purchase_originType === null) {
                //             $folioAfectar->purchase_originType = 'Usuario';
                //             $folioAfectar->purchase_origin = Auth::user()->username;
                //             $folioAfectar->purchase_originID = null;
                //         }
                //         $folioAfectar->update();

                //         break;
                //     case 'Rechazo de Compra':
                //         $folioOrden = PROC_PURCHASE::where('purchase_movement', '=', 'Rechazo de Compra')->where('purchase_branchOffice', '=', $folioAfectar->purchase_branchOffice)->max('purchase_movementID');
                //         $folioOrden = $folioOrden == null ? 1 : $folioOrden + 1;
                //         $folioAfectar->purchase_movementID = $folioOrden;
                //         $folioAfectar->update();
                //         break;
                // }

                $this->actualizarFolio($tipoMovimiento, $folioAfectar);

                //Actualizamos la cantidad de articulos en el inventario
                $this->actualizarInventario($movimientoIn, $articulos, $claveArt, $request, $lastId, $compra);

                //Actualizar articulos en la tabla de detalle de compra y la compra anterior
                $this->actualizarArticulos($folioAfectar);

                //agregar CxP
                $this->agregarCxP($folioAfectar->purchase_id);
                //agregar CxP
                $this->agregarCxPP($folioAfectar->purchase_id);
                //agregar a almacen
                $this->agregarAlmacen($folioAfectar->purchase_id);
                //agregamos movimientos
                $this->agregarMov($folioAfectar->purchase_id);
                //agregamos a auxiliar
                $this->auxiliar($folioAfectar->purchase_id);
                // agregamos a aux unidades 
                $this->auxiliarU($folioAfectar->purchase_id);
                //agregamos saldo a tabla
                $this->agregarSaldo($folioAfectar->purchase_id);
                //agregamos costo promedio
                $this->costoPromedio($folioAfectar->purchase_id);



                if ($isCreate) {
                    $status = 200;
                    $message = 'La ' . $movimientoIn . ' se ha creado correctamente';
                } else {
                    $status = 500;
                    $message = 'Error al afectar la compra';
                }
            } catch (\Throwable $th) {
                $status = 500;
                $lastId = 0;
                $message = $th->getMessage() . ' ' . $th->getLine();
            }

            return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
        } else {
            return response()->json(['mensaje' => 'Articulo con series duplicadas', 'estatus' => 500]);
        }
    }


    public function eliminarCompra(Request $request)
    {

        $compra = PROC_PURCHASE::where('purchase_id', '=', $request->id)->first();
        // dd($compra);

        //buscamos sus articulos
        $articulos = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $request->id)->where('purchaseDetails_branchOffice', '=', $compra->purchase_branchOffice)->get();

        if ($articulos->count() > 0) {
            //eliminamos sus articulos
            foreach ($articulos as $articulo) {

                if ($articulo->purchaseDetails_type == 'Serie') {
                    $series = PROC_LOT_SERIES_MOV::where('lotSeriesMov_purchaseID', '=', $articulo->purchaseDetails_purchaseID)->where('lotSeriesMov_articleID', '=', $articulo->purchaseDetails_id)->get();
                    // dd($series);
                    if ($series != null) {
                        foreach ($series as $serie) {
                            $serie->delete();
                        }
                    }
                }
                $articulosDelete = $articulo->delete();
            }
        } else {
            $articulosDelete = true;
        }


        // dd($articulos);
        if ($compra->purchase_status === $this->estatus[0] && $articulosDelete === true) {
            $isDelete = $compra->delete();
        } else {
            $isDelete = false;
        }

        if ($isDelete) {
            $status = 200;
            $message = 'Compra eliminada correctamente';
        } else {
            $status = 500;
            $message = 'Error al eliminar la compra';
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status]);
    }

    public function cancelarCompra(Request $request)
    {
        $compraCancelar = PROC_PURCHASE::where('purchase_id', '=', $request->id)->first();

        if ($compraCancelar->purchase_status == $this->estatus[2] && $compraCancelar->purchase_movement == 'Entrada por Compra') {

            //validar si ya hubo afectacion en cxp
            $movGenerado = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $compraCancelar->purchase_id)->where('movementFlow_movementDestinity', '=', $compraCancelar->purchase_movement)->where('movementFlow_moduleOrigin', '=', 'Compras')->first();

            //verificar si ya hay algun movimiento posterior a este
            $movimiento = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_id', '=', $movGenerado->movementFlow_destinityID)->first();
            //  dd( gettype($compraCancelar->purchase_total), gettype($movimiento->accountsPayable_balance));
            if ($compraCancelar->purchase_total != $movimiento->accountsPayable_balance) {
                //  echo 'entro';
                $status = 500;
                $message = 'No se puede cancelar la compra, ya que hay movimientos posteriores a esta';

                return response()->json(['mensaje' => $message, 'estatus' => $status]);
            }

            $articulosCompra = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $compraCancelar->purchase_id)->get();

            foreach ($articulosCompra as $articulo) {
                if ($articulo->purchaseDetails_type == 'Serie') {
                    $series = PROC_LOT_SERIES_MOV::where('lotSeriesMov_purchaseID', '=', $articulo->purchaseDetails_purchaseID)->where('lotSeriesMov_articleID', '=', $articulo->purchaseDetails_id)->where('lotSeriesMov_companieKey', '=', $compraCancelar->purchase_company)->where('lotSeriesMov_branchKey', '=', $compraCancelar->purchase_branchOffice)->get();


                    if (
                        $series != null
                    ) {
                        foreach ($series as $serie) {

                            //buscamos la serie en las tablas de eliminar serie tanto de inventarios como de ventas

                            $seriesEliminados = PROC_DEL_SERIES_MOV::where('delSeriesMov_lotSerie', '=', $serie->lotSeriesMov_lotSerie)->where('delSeriesMov_cancelled', '=', 0)->where('delSeriesMov_companieKey', '=', $compraCancelar->purchase_company)->where('delSeriesMov_branchKey', '=', $compraCancelar->purchase_branchOffice)->get()->toArray();
                            // dd($seriesEliminados);
                            //   DD($folioAfectar, $seriesEliminados);
                            if (count($seriesEliminados) > 0) {
                                $message = 'Alguna serie ya ha sido eliminada/transferida en otro movimiento';
                                $status = 500;

                                return response()->json(['mensaje' => $message, 'estatus' => $status]);
                            }

                            $seriesEliminados2 = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_lotSerie', '=', $serie->lotSeriesMov_lotSerie)->where('delSeriesMov2_cancelled', '=', 0)->where('delSeriesMov2_companieKey', '=', $compraCancelar->purchase_company)->where('delSeriesMov2_branchKey', '=', $compraCancelar->purchase_branchOffice)->get()->toArray();

                            if (count($seriesEliminados2) > 0) {
                                $message = 'Alguna serie tiene un movimiento de venta pendiente/concluido';
                                $status = 500;

                                return response()->json(['mensaje' => $message, 'estatus' => $status]);
                            }
                        }
                    }
                }
            }



            $entradaCancelada = false;
            $rechazadaCancelada = true;
            //cancela la compra
            $compraCancelar->purchase_status = $this->estatus[3];
            $cancelarCompra = $compraCancelar->update();

            if ($cancelarCompra) {
                $entradaCancelada = true;
            } else {
                $entradaCancelada = false;
            }
            //cancela la cxp de la compra
            $cxp = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movementID', '=', $compraCancelar->purchase_movementID)->where('accountsPayable_branchOffice', '=', $compraCancelar->purchase_branchOffice)->where('accountsPayable_movement', '=', 'Entrada por Compra')->first();

            if ($cxp !== null) {
                $cxp->accountsPayable_status = $this->estatus[3];
                $cxpCancelada = $cxp->update();

                if ($cxpCancelada) {
                    $entradaCancelada = true;
                } else {
                    $entradaCancelada = false;
                }
            }

            //eliminar la cxp pendiente de la compra
            $cxpPendiente = PROC_ACCOUNTS_PAYABLE_P::where('accountsPayableP_movementID', '=', $compraCancelar->purchase_movementID)->where('accountsPayableP_branchOffice', '=', $compraCancelar->purchase_branchOffice)->where('accountsPayableP_movement', '=', 'Entrada por Compra')->first();

            if ($cxpPendiente !== null) {
                $cxpPendienteDelete = $cxpPendiente->delete();

                if ($cxpPendienteDelete) {
                    $entradaCancelada = true;
                } else {
                    $entradaCancelada = false;
                }
            }
            //quitamos articulos de inventario
            $articulosCancelado = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $compraCancelar->purchase_id)->get();

            if ($articulosCancelado !== null) {
                foreach ($articulosCancelado as $articulo) {
                    $tipoArticulo = $articulo->purchaseDetails_type;

                    if ($tipoArticulo !== "Servicio") {
                        $cantidad = $articulo->purchaseDetails_inventoryAmount;
                        //    dd($cantidad);
                        $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->purchaseDetails_article)->where('articlesInv_depot', '=', $compraCancelar->purchase_depot)->first();
                        // dd($inventario);
                        $inventario->articlesInv_inventory = ($inventario->articlesInv_inventory - $cantidad);
                        $inventario->update();


                        $auxiliarU = new PROC_ASSISTANT_UNITS();
                        $auxiliarU->assistantUnit_companieKey = $compraCancelar->purchase_company;
                        $auxiliarU->assistantUnit_branchKey = $compraCancelar->purchase_branchOffice;
                        $auxiliarU->assistantUnit_branch = 'Inv';
                        $auxiliarU->assistantUnit_movement = $compraCancelar->purchase_movement;
                        $auxiliarU->assistantUnit_movementID = $compraCancelar->purchase_movementID;
                        $auxiliarU->assistantUnit_module = 'Compras';
                        $auxiliarU->assistantUnit_moduleID = $articulo->purchaseDetails_purchaseID;
                        $auxiliarU->assistantUnit_money = $compraCancelar->purchase_money;
                        $auxiliarU->assistantUnit_typeChange = $compraCancelar->purchase_typeChange;
                        $auxiliarU->assistantUnit_group = $articulo->purchaseDetails_depot;
                        $auxiliarU->assistantUnit_account = $articulo->purchaseDetails_article;
                        //ponemos fecha del ejercicio
                        $year = Carbon::now()->year;
                        //sacamos el periodo 
                        $period = Carbon::now()->month;
                        $auxiliarU->assistantUnit_year = $year;
                        $auxiliarU->assistantUnit_period = $period;

                        if ($compraCancelar->purchase_money == session('generalParameters')->generalParameters_defaultMoney) {
                            $auxiliarU->assistantUnit_charge = '-' . $articulo->purchaseDetails_amount;
                        } else {
                            $auxiliarU->assistantUnit_charge = '-' . ($articulo->purchaseDetails_amount * $compraCancelar->purchase_typeChange);
                        }

                        $auxiliarU->assistantUnit_payment = null;
                        $auxiliarU->assistantUnit_chargeUnit = -(float)$articulo->purchaseDetails_inventoryAmount;
                        $auxiliarU->assistantUnit_paymentUnit = null;
                        $auxiliarU->assistantUnit_apply = $articulo->purchaseDetails_apply;
                        $auxiliarU->assistantUnit_applyID =  $articulo->purchaseDetails_applyIncrement;
                        $auxiliarU->assistantUnit_canceled = 1;
                        $auxiliarU->save();
                    }
                }
            }

            //buscamos si tiene orden de compra
            if ($compraCancelar->purchase_originID !== Null) {
                $compraAnterior = PROC_PURCHASE::where('purchase_movementID', '=', $compraCancelar->purchase_originID)->where('purchase_branchOffice', '=', $compraCancelar->purchase_branchOffice)->where('purchase_movement', '=', $compraCancelar->purchase_origin)->first();
            } else {
                $compraAnterior = PROC_PURCHASE::where('purchase_movementID', '=', $compraCancelar->purchase_originID)->where('purchase_branchOffice', '=', $compraCancelar->purchase_branchOffice)->first();
            }


            if ($compraAnterior !== null) {
                $compraAnterior->purchase_status = $this->estatus[1];
                $compraAnterior->purchase_total = $compraAnterior->purchase_total + $compraCancelar->purchase_total;


                $compraAnteriorReset = $compraAnterior->update();


                if ($compraAnteriorReset) {
                    $entradaCancelada = true;
                } else {
                    $entradaCancelada = false;
                }
                //buscamos sus articulos
                $articulosCompraAnt = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $compraAnterior->purchase_id)->where('purchaseDetails_branchOffice', '=', $compraAnterior->purchase_branchOffice)->get();

                $articulosCancelado = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $compraCancelar->purchase_id)->where('purchaseDetails_branchOffice', '=', $compraCancelar->purchase_branchOffice)->get();

                //    dd($articulosCompraAnt, $articulosCancelado);

                $arrayArticulosCancelados = [];

                foreach ($articulosCancelado as $articulo) {
                    $arrayArticulosCancelados[$articulo->purchaseDetails_article . '-' . $articulo->purchaseDetails_referenceArticles] = ['cantidad' => $articulo->purchaseDetails_quantity];
                }

                foreach ($articulosCompraAnt as $articulo) {
                    if (isset($arrayArticulosCancelados[$articulo->purchaseDetails_article . '-' . $articulo->purchaseDetails_id])) {
                        $nuevoPendiente = ($articulo->purchaseDetails_outstandingAmount + $arrayArticulosCancelados[$articulo->purchaseDetails_article . '-' . $articulo->purchaseDetails_id]['cantidad']);
                        $articulo->purchaseDetails_outstandingAmount = $nuevoPendiente;
                        $articulo->update();
                    }
                }
            }

            //agregamos un cargo negativo a auxiliar
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $compraCancelar->purchase_company;
            $auxiliar->assistant_branchKey = $compraCancelar->purchase_branchOffice;
            $auxiliar->assistant_branch = 'CxP';
            $auxiliar->assistant_movement = $compraCancelar->purchase_movement;
            $auxiliar->assistant_movementID = $compraCancelar->purchase_movementID;
            $auxiliar->assistant_module = 'CxP';

            //buscamos el modulo de cxp
            $cxp = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movementID', '=', $compraCancelar->purchase_movementID)->where('accountsPayable_branchOffice', '=', $compraCancelar->purchase_branchOffice)->first();

            $auxiliar->assistant_moduleID = $cxp->accountsPayable_id;
            $auxiliar->assistant_money = $compraCancelar->purchase_money;
            $auxiliar->assistant_typeChange = $compraCancelar->purchase_typeChange;
            $auxiliar->assistant_account = $compraCancelar->purchase_provider;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;

            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = '-' . $compraCancelar->purchase_total;
            $auxiliar->assistant_payment = null;
            $auxiliar->assistant_apply = $compraCancelar->purchase_movement;
            $auxiliar->assistant_applyID = $compraCancelar->purchase_movementID;
            $auxiliar->assistant_canceled = 1;
            $auxiliar->assistant_reference = $compraCancelar->purchase_reference;

            $auxiliar->save();
            // dd($compraAnterior);

            //cancelar las series de la compra
            $series = PROC_LOT_SERIES_MOV::where(
                'lotSeriesMov_purchaseID',
                '=',
                $compraCancelar->purchase_id
            )->get();

            foreach ($series as $serie) {
                $serieLote = PROC_LOT_SERIES::where('lotSeries_lotSerie', '=', $serie->lotSeriesMov_lotSerie)
                    ->where('lotSeries_article', '=', $serie->lotSeriesMov_article)
                    ->where('lotSeries_depot', '=', $compraCancelar->purchase_depot)
                    ->first();

                if ($serieLote != null) {
                    if ($serieLote->lotSeries_existence !== null) {
                        $serieLote->delete(); // Eliminar el registro en lugar de actualizarlo
                    }
                }
            }
            // dd($series, $serieLote);

            //eliminamos saldo del proveedor

            $saldo = PROC_BALANCE::where('balance_account', '=', $compraCancelar->purchase_provider)->where('balance_branchKey', '=', $compraCancelar->purchase_branchOffice)->where('balance_companieKey', '=', $compraCancelar->purchase_company)->where('balance_money', '=', $compraCancelar->purchase_money)->where('balance_branch', '=', "CxP")->first();

            if ($saldo != null) {
                $saldo->balance_balance = $saldo->balance_balance - $compraCancelar->purchase_total;
                $saldo->balance_reconcile = $saldo->balance_balance;
                $saldo->update();
            }

            //cancelar movimientos de tabla

            $movimientosIN = PROC_MOVEMENT_FLOW::where('movementFlow_originID', '=', $compraCancelar->purchase_id)->where('movementFlow_movementOrigin', '=', $compraCancelar->purchase_movement)->where('movementFlow_branch', '=', $compraCancelar->purchase_branchOffice)->where('movementFlow_company', '=', $compraCancelar->purchase_company)->first();

            if ($movimientosIN != null) {
                $movimientosIN->movementFlow_cancelled = 1;
                $movimientosIN->update();
            }

            $movimientosOUT = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $compraCancelar->purchase_id)->where('movementFlow_movementDestinity', '=', $compraCancelar->purchase_movement)->where('movementFlow_branch', '=', $compraCancelar->purchase_branchOffice)->where('movementFlow_company', '=', $compraCancelar->purchase_company)->first();

            if ($movimientosOUT != null) {
                $movimientosOUT->movementFlow_cancelled = 1;
                $movimientosOUT->update();
            }


            $this->quitarCostoPromedio($request->id);
        }

        if ($compraCancelar->purchase_status == $this->estatus[2] && $compraCancelar->purchase_movement == 'Rechazo de Compra') {
            $rechazadaCancelada = false;
            $entradaCancelada = true;
            $compraCancelar->purchase_status = $this->estatus[3];
            $cancelarCompra = $compraCancelar->update();

            if ($cancelarCompra) {
                $rechazadaCancelada = true;
            } else {
                $rechazadaCancelada = false;
            }

            //buscamos si tiene orden de compra
            if ($compraCancelar->purchase_originID !== Null) {
                $compraAnterior = PROC_PURCHASE::where('purchase_movementID', '=', $compraCancelar->purchase_originID)->where('purchase_branchOffice', '=', $compraCancelar->purchase_branchOffice)->where('purchase_movement', '=', $compraCancelar->purchase_origin)->first();
            } else {
                $compraAnterior = PROC_PURCHASE::where('purchase_movementID', '=', $compraCancelar->purchase_originID)->where('purchase_branchOffice', '=', $compraCancelar->purchase_branchOffice)->first();
            }

            if ($compraAnterior !== null) {
                $compraAnterior->purchase_status = $this->estatus[1];
                $compraAnteriorReset = $compraAnterior->update();

                if ($compraAnteriorReset) {
                    $rechazadaCancelada = true;
                } else {
                    $rechazadaCancelada = false;
                }

                //buscamos sus articulos
                $articulosCompraAnt = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $compraAnterior->purchase_id)->where('purchaseDetails_branchOffice', '=', $compraAnterior->purchase_branchOffice)->get();

                $articulosCancelado = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $compraCancelar->purchase_id)->where('purchaseDetails_branchOffice', '=', $compraCancelar->purchase_branchOffice)->get();

                $arrayArticulosCancelados = [];

                foreach ($articulosCancelado as $articulo) {
                    $arrayArticulosCancelados[$articulo->purchaseDetails_article . '-' . $articulo->purchaseDetails_referenceArticles] = ['cantidad' => $articulo->purchaseDetails_quantity];
                }

                foreach ($articulosCompraAnt as $articulo) {
                    if (isset($arrayArticulosCancelados[$articulo->purchaseDetails_article . '-' . $articulo->purchaseDetails_id])) {
                        $nuevoPendiente = ($articulo->purchaseDetails_outstandingAmount + $arrayArticulosCancelados[$articulo->purchaseDetails_article . '-' . $articulo->purchaseDetails_id]['cantidad']);
                        $articulo->purchaseDetails_outstandingAmount = $nuevoPendiente;
                        $articulo->update();
                    }
                }
            }

            //buscamos el mov generado
            $movimientosMov = PROC_MOVEMENT_FLOW::where('movementFlow_destinityID', '=', $compraCancelar->purchase_id)->where('movementFlow_movementDestinity', '=', $compraCancelar->purchase_movement)->where('movementFlow_movementOrigin', '=', $compraCancelar->purchase_origin)->where('movementFlow_branch', '=', $compraCancelar->purchase_branchOffice)->where('movementFlow_company', '=', $compraCancelar->purchase_company)->first();

            if ($movimientosMov != null) {
                $movimientosMov->movementFlow_cancelled = 1;
                $movimientosMov->update();
            }
        }


        // dd($compraCancelar);
        if ($entradaCancelada == true && $rechazadaCancelada == true) {
            $status = 200;
            $message = 'Compra cancelada correctamente';
        } else {
            $status = 500;
            $message = 'Error al cancelar la compra';
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status]);
    }

    public function quitarCostoPromedio($folio)
    {

        $folioAfectar = PROC_PURCHASE::where('purchase_id', '=', $folio)->first();



        if ($folioAfectar->purchase_status == $this->estatus[3] && $folioAfectar->purchase_movement == 'Entrada por Compra') {
            $contador = 0;
            $articuloClave = [];
            //sacamos sus articulos
            $articulos = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $folioAfectar->purchase_id)->get();

            foreach ($articulos as $articulo) {
                $articuloClave[$contador] = $articulo->purchaseDetails_article;
                $contador++;
            }

            // dd($articuloClave);
            $cantidadAux = [];
            $cantidadAux2 = [];
            $cantidadInventario = [];


            foreach ($articuloClave as $key => $articulo) {
                $costoAuxArticulo = PROC_ASSISTANT_UNITS::where('assistantUnit_account', '=', $articulo)->where('assistantUnit_branchKey', '=', $folioAfectar->purchase_branchOffice)->where('assistantUnit_companieKey', '=', $folioAfectar->purchase_company)->where('assistantUnit_group', '=', $folioAfectar->purchase_depot)->get()->sum('assistantUnit_charge');
                $cantidadAux[$articulo] = $costoAuxArticulo;

                //  dd($costoAuxArticulo);

                $costoAuxArticulo2 = PROC_ASSISTANT_UNITS::where('assistantUnit_account', '=', $articulo)->where('assistantUnit_branchKey', '=', $folioAfectar->purchase_branchOffice)->where('assistantUnit_companieKey', '=', $folioAfectar->purchase_company)->where('assistantUnit_group', '=', $folioAfectar->purchase_depot)->get()->sum('assistantUnit_payment');

                // dd($costoAuxArticulo2);

                if ($costoAuxArticulo2 == null) {
                    $costoAuxArticulo2 = 0;
                }
                $cantidadAux2[$articulo] = $costoAuxArticulo2;

                $cantidadArticulos = PROC_ARTICLES_INV::where('articlesInv_branchKey', '=', $folioAfectar->purchase_branchOffice)->where('articlesInv_article', '=', $articuloClave[$key])->where('articlesInv_depot', '=', $folioAfectar->purchase_depot)->get()->sum('articlesInv_inventory');
                $cantidadInventario[$articulo] = $cantidadArticulos;
            }

            //  dd($cantidadAux, $cantidadAux2, $cantidadInventario, $articulos);

            foreach ($articulos as $articulo) {
                // dd($articulo);
                //agregamos costo promedio
                if ($cantidadInventario[$articulo->purchaseDetails_article] != 0) {

                    $costoPromedio = ($cantidadAux[$articulo->purchaseDetails_article] - $cantidadAux2[$articulo->purchaseDetails_article]) / $cantidadInventario[$articulo->purchaseDetails_article];

                    // dd($costoPromedio);

                    $articuloCostoH = new PROC_ARTICLES_COST_HIS();
                    //  dd($costoPromedio);

                    $articuloCostoH2 = PROC_ARTICLES_COST_HIS::where('articlesCostHis_article', '=', $articulo->purchaseDetails_article)->where('articlesCostHis_branchKey', '=', $folioAfectar->purchase_branchOffice)->where('articlesCostHis_companieKey', '=', $folioAfectar->purchase_company)->where('articlesCostHis_depotKey', '=', $folioAfectar->purchase_depot)->orderBy('created_at', 'desc')->first();


                    if ($articuloCostoH2 === null) {

                        $articuloCostoH->articlesCostHis_companieKey = $folioAfectar->purchase_company;
                        $articuloCostoH->articlesCostHis_branchKey = $folioAfectar->purchase_branchOffice;
                        $articuloCostoH->articlesCostHis_depotKey = $folioAfectar->purchase_depot;
                        $articuloCostoH->articlesCostHis_article = $articulo->purchaseDetails_article;
                        $articuloCostoH->articlesCostHis_lastCost = $articuloCostoH2 ? $articuloCostoH2->articlesCostHis_currentCost : null;
                        $articuloCostoH->articlesCostHis_currentCost =   $articuloCostoH->articlesCostHis_lastCost;
                        $articuloCostoH->articlesCostHis_averageCost = $costoPromedio;
                        $articuloCostoH->created_at = date('Y-m-d H:i:s');
                        $articuloCostoH->save();
                    } else {
                        if ($articuloCostoH2->articlesCostHis_currentCost != $costoPromedio) {
                            $articuloCostoH->articlesCostHis_companieKey = $folioAfectar->purchase_company;
                            $articuloCostoH->articlesCostHis_branchKey = $folioAfectar->purchase_branchOffice;
                            $articuloCostoH->articlesCostHis_depotKey = $folioAfectar->purchase_depot;
                            $articuloCostoH->articlesCostHis_article = $articulo->purchaseDetails_article;
                            $articuloCostoH->articlesCostHis_lastCost = $articuloCostoH2 ? $articuloCostoH2->articlesCostHis_currentCost : null;
                            $articuloCostoH->articlesCostHis_currentCost =  $articuloCostoH->articlesCostHis_lastCost;
                            $articuloCostoH->articlesCostHis_averageCost = $costoPromedio;
                            $articuloCostoH->created_at = date('Y-m-d H:i:s');
                            $articuloCostoH->save();
                        }
                    }
                    // dd($articuloCosto2);

                    //agregamos costo promedio

                    $articuloCosto = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articulo->purchaseDetails_article)->where('articlesCost_branchKey', '=', $folioAfectar->purchase_branchOffice)->where('articlesCost_companieKey', '=', $folioAfectar->purchase_company)->where('articlesCost_depotKey', '=', $folioAfectar->purchase_depot)->first();

                    $articuloReferencia = PROC_ARTICLES_COST_HIS::where('articlesCostHis_article', '=', $articulo->purchaseDetails_article)->where('articlesCostHis_branchKey', '=', $folioAfectar->purchase_branchOffice)->where('articlesCostHis_companieKey', '=', $folioAfectar->purchase_company)->where('articlesCostHis_depotKey', '=', $folioAfectar->purchase_depot)->orderBy('created_at', 'desc')->first();

                    if ($articuloCosto == null) {
                        $articuloCosto = new PROC_ARTICLES_COST();
                        $articuloCosto->articlesCost_companieKey = $folioAfectar->purchase_company;
                        $articuloCosto->articlesCost_branchKey = $folioAfectar->purchase_branchOffice;
                        $articuloCosto->articlesCost_depotKey = $folioAfectar->purchase_depot;
                        $articuloCosto->articlesCost_article = $articulo->purchaseDetails_article;
                        $articuloCosto->articlesCost_lastCost = $articuloReferencia ? $articuloReferencia->articlesCostHis_currentCost : null;
                        $articuloCosto->articlesCost_averageCost =  $articuloReferencia->articlesCostHis_averageCost;
                        $articuloCosto->save();
                    } else {
                        $articuloCosto->articlesCost_companieKey = $folioAfectar->purchase_company;
                        $articuloCosto->articlesCost_branchKey = $folioAfectar->purchase_branchOffice;
                        $articuloCosto->articlesCost_depotKey = $folioAfectar->purchase_depot;
                        $articuloCosto->articlesCost_article = $articulo->purchaseDetails_article;
                        $articuloCosto->articlesCost_lastCost = $articuloReferencia ? $articuloReferencia->articlesCostHis_currentCost : null;
                        $articuloCosto->articlesCost_averageCost =  $articuloReferencia->articlesCostHis_averageCost;
                        $articuloCosto->update();
                    }
                }
            } //fin foreach
        }
    }

    public function cancelarOrdenCompleta(Request $request)
    {
        $orden = PROC_PURCHASE::where('purchase_id', '=', $request->id)->first();
        //buscamos sus entradas de compra 

        $entradasCompra = PROC_PURCHASE::where('purchase_originID', '=', $orden->purchase_movementID)->where('purchase_origin', '=', $orden->purchase_movement)->where('purchase_movement', '=', 'Entrada por Compra')->where('purchase_status', '=', $this->estatus[2])->where('purchase_branchOffice', '=', $orden->purchase_branchOffice)->get();

        $comprasRechazadas = PROC_PURCHASE::where('purchase_originID', '=', $orden->purchase_movementID)->where('purchase_origin', '=', $orden->purchase_movement)->where('purchase_movement', '=', 'Rechazo de Compra')->where('purchase_status', '=', $this->estatus[2])->where('purchase_branchOffice', '=', $orden->purchase_branchOffice)->get();

        if ($entradasCompra->count() > 0 || $comprasRechazadas->count() > 0) {
            $status = 500;
            $message = 'No se puede cancelar la orden de compra, tiene movimientos asociados';
        } else {

            $entradasCompraSinAFectar = PROC_PURCHASE::where('purchase_originID', '=', $orden->purchase_movementID)->where('purchase_origin', '=', $orden->purchase_movement)->where('purchase_movement', '=', 'Entrada por Compra')->where('purchase_status', '=', $this->estatus[0])->where('purchase_branchOffice', '=', $orden->purchase_branchOffice)->get();

            if ($entradasCompraSinAFectar->count() > 0) {
                foreach ($entradasCompraSinAFectar as $entrada) {
                    $entrada->purchase_status = $this->estatus[3];
                    $entrada->update();
                }
            }

            $comprasRechazadasSinAfectar = PROC_PURCHASE::where('purchase_originID', '=', $orden->purchase_movementID)->where('purchase_origin', '=', $orden->purchase_movement)->where('purchase_movement', '=', 'Rechazo de Compra')->where('purchase_status', '=', $this->estatus[1])->where('purchase_branchOffice', '=', $orden->purchase_branchOffice)->get();

            if ($comprasRechazadasSinAfectar->count() > 0) {
                foreach ($comprasRechazadasSinAfectar as $compraRechazada) {
                    $compraRechazada->purchase_status = $this->estatus[3];
                    $compraRechazada->update();
                }
            }

            //cancelamos sus articulos 
            $articulosOrden = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $orden->purchase_id)->where('purchaseDetails_branchOffice', '=', $orden->purchase_branchOffice)->get();

            foreach ($articulosOrden as $articulo) {
                $articulo->purchaseDetails_outstandingAmount = null;
                $articulo->update();
            }


            $orden->purchase_status = $this->estatus[3];
            $cancelarOrden = $orden->update();

            if ($cancelarOrden) {
                $status = 200;
                $message = 'Orden de compra cancelada correctamente';
            } else {
                $status = 500;
                $message = 'Error al cancelar la orden de compra';
            }
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status]);
        // $status = 200;
        // $message = 'Orden cancelada correctamente';
        // return response()->json(['mensaje'=>$message, 'estatus'=>$status]);
    }

    public function cancelarOrdenPendiente(Request $request)
    {

        $orden = PROC_PURCHASE::where('purchase_id', '=', $request->id)->first();

        //buscamos sus articulos
        $articulosOrden = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $orden->purchase_id)->where('purchaseDetails_branchOffice', '=', $orden->purchase_branchOffice)->get();

        //buscamos sus entradas de compra

        $entradasCompra = PROC_PURCHASE::where('purchase_originID', '=', $orden->purchase_id)->where('purchase_origin', '=', $orden->purchase_movement)->where('purchase_status', '=', $this->estatus[2])->where('purchase_branchOffice', '=', $orden->purchase_branchOffice)->get();

        // dd($entradasCompra);

        $importeEntradas = 0;
        $impuestoEntradas = 0;
        foreach ($entradasCompra as $entrada) {
            $importeEntradas += $entrada->purchase_amount;
            $impuestoEntradas += $entrada->purchase_taxes;
        }



        // dd($importeEntradas, $impuestoEntradas);

        foreach ($articulosOrden as $articulo) {

            $articulo->purchaseDetails_canceledAmount === null ?   $articulo->purchaseDetails_canceledAmount  = $articulo->purchaseDetails_outstandingAmount : $articulo->purchaseDetails_canceledAmount  = $articulo->purchaseDetails_canceledAmount + $articulo->purchaseDetails_outstandingAmount;
            $articulo->purchaseDetails_outstandingAmount = null;
            $articulo->update();
        }

        $orden->purchase_status = $this->estatus[3];
        $orden->purchase_amount = $importeEntradas;
        $orden->purchase_taxes = $impuestoEntradas;
        $orden->purchase_total = 0;

        $cancelarOrden = $orden->update();

        if ($cancelarOrden) {
            $status = 200;
            $message = 'Orden de compra cancelada correctamente';
        } else {
            $status = 500;
            $message = 'Error al cancelar la orden de compra';
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status]);
    }

    public function actualizarFolio($tipoMovimiento, $folioAfectar)
    {
        switch ($tipoMovimiento) {
            case 'Orden de Compra':
                $consecutivoColumn = 'generalConsecutives_consOrderPurchase';
                break;
            case 'Entrada por Compra':
                $consecutivoColumn = 'generalConsecutives_consEntryPurchase';
                break;
            case 'Rechazo de Compra':
                // Mantener la lógica actual para Rechazo de Compra
                $folioOrden = PROC_PURCHASE::where('purchase_movement', '=', 'Rechazo de Compra')
                ->where('purchase_branchOffice', '=', $folioAfectar->purchase_branchOffice)
                    ->max('purchase_movementID');
                $folioOrden = $folioOrden == null ? 1 : $folioOrden + 1;
                $folioAfectar->purchase_movementID = $folioOrden;
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
                $folioOrden = PROC_PURCHASE::where('purchase_movement', '=', $tipoMovimiento)
                    ->where('purchase_branchOffice', '=', $folioAfectar->purchase_branchOffice)
                    ->max('purchase_movementID');
                $folioOrden = $folioOrden == null ? 1 : $folioOrden + 1;
                $folioAfectar->purchase_movementID = $folioOrden;
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

                $folioAfectar->purchase_movementID = $consecutivo + 1;
                $folioAfectar->update();
            }
        }
    }

    // public function actualizarParametros()
    // {

    // }

    public function actualizarInventario($movimientoIn, $articulos, $claveArt, $request, $lastId, $compra)   
    {
        if ($articulos !== null) {
            foreach ($claveArt as $articulo) {
                if (isset($articulos[$articulo]['id'])) {
                    $detalleCompra = PROC_PURCHASE_DETAILS::where('purchaseDetails_id', '=', $articulos[$articulo]['id'])->first();
                } else {
                    $detalleCompra = new PROC_PURCHASE_DETAILS();
                }
                $detalleCompra->purchaseDetails_purchaseID = $lastId;
                $articuloClave = explode('-', $articulo);
                $detalleCompra->purchaseDetails_article = $articuloClave[0];
                $detalleCompra->purchaseDetails_type = $articulos[$articulo]['tipoArticulo'];
                $detalleCompra->purchaseDetails_descript = $articulos[$articulo]['desp'];
                $detalleCompra->purchaseDetails_quantity = str_replace(['$', ','], '', $articulos[$articulo]['cantidad']);
                $detalleCompra->purchaseDetails_unitCost = str_replace(['$', ','], '', $articulos[$articulo]['c_unitario']);

                $unidadDiv = explode('-', $articulos[$articulo]['unidad']);
                $detalleCompra->purchaseDetails_unit = $unidadDiv[0];
                $detalleCompra->purchaseDetails_factor = $unidadDiv[1];

                $detalleCompra->purchaseDetails_inventoryAmount = str_replace(['$', ','], '', $articulos[$articulo]['c_Inventario']);
                $detalleCompra->purchaseDetails_amount = str_replace(['$', ','], '', $articulos[$articulo]['importe']);
                $detalleCompra->purchaseDetails_discountPorcent = $articulos[$articulo]['porcentajeDescuento'];
                $detalleCompra->purchaseDetails_discount = $articulos[$articulo]['descuento'];
                $detalleCompra->purchaseDetails_ivaPorcent = str_replace(['$', ','], '', $articulos[$articulo]['iva']);
                $detalleCompra->purchaseDetails_total = str_replace(['$', ','], '', $articulos[$articulo]['importe_total']);

                if ($movimientoIn == 'Orden de Compra') {
                    $detalleCompra->purchaseDetails_apply = null;
                    $detalleCompra->purchaseDetails_applyIncrement = null;
                }


                $detalleCompra->purchaseDetails_branchOffice = session('sucursal')->branchOffices_key;
                $detalleCompra->purchaseDetails_depot = $request['almacenKey'];

                if ($movimientoIn == 'Orden de Compra') {
                    $detalleCompra->purchaseDetails_outstandingAmount = str_replace(['$', ','], "", $articulos[$articulo]['cantidad']);
                } else {
                    $detalleCompra->purchaseDetails_outstandingAmount = null;
                }

                $detalleCompra->purchaseDetails_canceledAmount = null;
                $detalleCompra->save();

                if ($articulos[$articulo]['tipoArticulo'] == "Serie" && $compra->purchase_movement == "Entrada por Compra") {

                    $lastIdDetalle = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $lastId)->where('purchaseDetails_article', '=', $articuloClave[0])->select('purchaseDetails_id')->first();
                    //Agregamos los articulos de serie a la tabla PROC_LOT_SERIES_MOV;
                    if (isset($articulos[$articulo]['asignacionSerie'])) {
                        foreach ($articulos[$articulo]['asignacionSerie'] as $key => $value) {
                            if (isset($articulos[$articulo]['asignacionIdsSerie'])) {
                                if ($key <=  (count($articulos[$articulo]['asignacionIdsSerie']) - 1)) {
                                    $proc_lot_series_mov = PROC_LOT_SERIES_MOV::where('lotSeriesMov_companieKey', '=', $compra->purchase_company)->where('lotSeriesMov_branchKey', '=', $compra->purchase_branchOffice)->where('lotSeriesMov_purchaseID', "=", $lastId)->where('lotSeriesMov_article', "=", $articuloClave[0])->where('lotSeriesMov_id', "=", $articulos[$articulo]['asignacionIdsSerie'][$key])->first();
                                } else {
                                    $proc_lot_series_mov = new PROC_LOT_SERIES_MOV();
                                    $proc_lot_series_mov->lotSeriesMov_articleID =  $lastIdDetalle->purchaseDetails_id;
                                }
                            } else {
                                $proc_lot_series_mov = new PROC_LOT_SERIES_MOV();
                                $proc_lot_series_mov->lotSeriesMov_articleID =  $lastIdDetalle->purchaseDetails_id;
                            }



                            $proc_lot_series_mov->lotSeriesMov_companieKey =  $compra->purchase_company;
                            $proc_lot_series_mov->lotSeriesMov_branchKey = $compra->purchase_branchOffice;
                            $proc_lot_series_mov->lotSeriesMov_module = 'COMPRAS';
                            $proc_lot_series_mov->lotSeriesMov_purchaseID = $lastId;
                            $proc_lot_series_mov->lotSeriesMov_article = $articuloClave[0];
                            $proc_lot_series_mov->lotSeriesMov_lotSerie = $value;
                            $proc_lot_series_mov->lotSeriesMov_quantity = 1;


                            $proc_lot_series_mov->save();

                            $articulo = PROC_LOT_SERIES::WHERE('lotSeries_branchKey', '=', session('sucursal')->branchOffices_key)->WHERE('lotSeries_article', '=', $articuloClave[0])->WHERE('lotSeries_lotSerie', '=', $value)->first();

                            if ($articulo === null) {
                                $proc_lot_series = new PROC_LOT_SERIES();
                                $proc_lot_series->lotSeries_companieKey =  $compra->purchase_company;
                                $proc_lot_series->lotSeries_branchKey = $compra->purchase_branchOffice;
                                $proc_lot_series->lotSeries_article = $articuloClave[0];
                                $proc_lot_series->lotSeries_lotSerie = $value;
                                $proc_lot_series->lotSeries_depot = $compra->purchase_depot;;
                                $proc_lot_series->lotSeries_existence = 1;
                                $proc_lot_series->lotSeries_delete = 0;
                                $proc_lot_series->save();
                            }
                        }
                    }
                }
            }
        }
    }

    public function actualizarArticulos($folioAfectar)
    {
        if ($folioAfectar->purchase_originID !== null && $folioAfectar->purchase_status == $this->estatus[2]) {
            // //actualizar la compra anterior a concluido
            // $compraAnterior = PROC_PURCHASE::where('purchase_movementID', '=', $folioAfectar->purchase_originID)->first();
            // $compraAnterior->purchase_status = $this->estatus[2];
            // $compraAnterior->update();

            // $articulosCompraAnterior = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $compraAnterior->purchase_id)->get();

            // //verificamos que los articulos de la compra anterior sean iguales a los de la compra actual
            // foreach ($articulosCompraAnterior as $key => $articuloAnterior) {
            //     $articulo->purchaseDetails_outstandingAmount = null;
            //     $articulo->update();
            // }

            // $compraAnterior->purchase_status = $this->estatus[2];
            // $compraAnterior->update();
            $articulosPendientes2 = [];
            $isArticulosPendientesEntrega = false;
            $compraAnterior = PROC_PURCHASE::where('purchase_movementID', '=', $folioAfectar->purchase_originID)->where('purchase_branchOffice', '=', $folioAfectar->purchase_branchOffice)->where('purchase_movement', '=', 'Orden de Compra')->first();
            $articulosCompraAnterior = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $compraAnterior->purchase_id)->where('purchaseDetails_branchOffice', '=', $compraAnterior->purchase_branchOffice)->get();

            $articulosPendiente = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $folioAfectar->purchase_id)->where('purchaseDetails_branchOffice', '=', $folioAfectar->purchase_branchOffice)->get();


            $arrayArticulosPendientes = array();


            foreach ($articulosPendiente as $articulo) {
                $arrayArticulosPendientes[$articulo->purchaseDetails_article . '-' . $articulo->purchaseDetails_referenceArticles] = ['cantidad' => $articulo->purchaseDetails_quantity];
            }


            foreach ($articulosCompraAnterior as $articulo) {
                if (isset($arrayArticulosPendientes[$articulo->purchaseDetails_article . '-' . $articulo->purchaseDetails_id]) && $articulo->purchaseDetails_outstandingAmount  > 0) {
                    $nuevoPendiente = ($articulo->purchaseDetails_outstandingAmount - $arrayArticulosPendientes[$articulo->purchaseDetails_article . '-' . $articulo->purchaseDetails_id]['cantidad']);
                    $articulo->purchaseDetails_outstandingAmount = $nuevoPendiente;
                    $articulo->update();
                }

                $articulosPendientes2[] = $articulo->purchaseDetails_outstandingAmount;
            }

            foreach ($articulosPendientes2 as $key => $value) {
                if ($value !== null && $value > 0) {
                    $isArticulosPendientesEntrega = true;
                    break;
                } else {
                    $isArticulosPendientesEntrega = false;
                }
            }

            if (!$isArticulosPendientesEntrega) {
                $compraAnterior->purchase_status = $this->estatus[2];
                $compraAnterior->purchase_total = $compraAnterior->purchase_total - $folioAfectar->purchase_total;
                //actualizar pero sin modificar el updated_At
                $compraAnterior->update();
            } else {
                $compraAnterior->purchase_total = $compraAnterior->purchase_total - $folioAfectar->purchase_total;
                $compraAnterior->update();
            }
        }
    }

    public function agregarCxP($folio)
    {
        $folioAfectar = PROC_PURCHASE::where('purchase_id', '=', $folio)->first();

        if ($folioAfectar->purchase_status == $this->estatus[2] && $folioAfectar->purchase_movement == 'Entrada por Compra') {

            //agregamos una nueva cuenta por pagar para la compra
            $cuentaPagar = new PROC_ACCOUNTS_PAYABLE();
            $cuentaPagar->accountsPayable_movement = $folioAfectar->purchase_movement;
            $cuentaPagar->accountsPayable_movementID = $folioAfectar->purchase_movementID;
            $emision = $cuentaPagar->accountsPayable_issuedate = Carbon::parse($folioAfectar->purchase_issueDate)->format('Y-m-d');
            $cuentaPagar->accountsPayable_money = $folioAfectar->purchase_money;
            $cuentaPagar->accountsPayable_typeChange = $folioAfectar->purchase_typeChange;
            $cuentaPagar->accountsPayable_provider = $folioAfectar->purchase_provider;
            $cuentaPagar->accountsPayable_condition = $folioAfectar->purchase_condition;
            $vencimiento = Carbon::parse($folioAfectar->purchase_expiration)->format('Y-m-d');
            $vencimiento2 = Carbon::parse($vencimiento)->format('Y-m-d');

            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
            $vencimientoN = Carbon::parse($folioAfectar->purchase_expiration)->format('Y-m-d');
            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimientoN);

            $diasMoratorio = $shippingDate->diffInDays($currentDate);
            $cuentaPagar->accountsPayable_moratoriumDays = '-' . $diasMoratorio;
            $cuentaPagar->accountsPayable_expiration = $vencimiento2;
            $cuentaPagar->accountsPayable_amount = $folioAfectar->purchase_amount;
            $cuentaPagar->accountsPayable_taxes = $folioAfectar->purchase_taxes;
            $cuentaPagar->accountsPayable_total = $folioAfectar->purchase_total;
            $cuentaPagar->accountsPayable_concept = $folioAfectar->purchase_concept;
            $cuentaPagar->accountsPayable_reference = $folioAfectar->purchase_reference;
            $cuentaPagar->accountsPayable_balance = $folioAfectar->purchase_total;
            $cuentaPagar->accountsPayable_company = $folioAfectar->purchase_company;
            $cuentaPagar->accountsPayable_branchOffice = $folioAfectar->purchase_branchOffice;
            $cuentaPagar->accountsPayable_user = $folioAfectar->purchase_user;
            $cuentaPagar->accountsPayable_status = $this->estatus[1];
            $cuentaPagar->accountsPayable_origin = $folioAfectar->purchase_movement;
            $cuentaPagar->accountsPayable_originID = $folioAfectar->purchase_movementID;
            $cuentaPagar->accountsPayable_originType = 'Compras';
            $cuentaPagar->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $cuentaPagar->updated_at = Carbon::now()->format('Y-m-d H:i:s');


            $create = $cuentaPagar->save();
        }
    }

    public function agregarCxPP($folio)
    {
        $folioAfectar = PROC_PURCHASE::where('purchase_id', '=', $folio)->first();

        if ($folioAfectar->purchase_status == $this->estatus[2] && $folioAfectar->purchase_movement == 'Entrada por Compra') {

            //agregamos una nueva cuenta por pagar para la compra
            $cuentaPagar = new PROC_ACCOUNTS_PAYABLE_P();
            $cuentaPagar->accountsPayableP_movement = $folioAfectar->purchase_movement;
            $cuentaPagar->accountsPayableP_movementID = $folioAfectar->purchase_movementID;
            $cuentaPagar->accountsPayableP_issuedate = Carbon::parse($folioAfectar->purchase_issueDate)->format('Y-m-d');
            $cuentaPagar->accountsPayableP_expiration =  Carbon::parse($folioAfectar->purchase_expiration)->format('Y-m-d');

            //dias credito y moratorio
            $emision = Carbon::parse($folioAfectar->purchase_issueDate)->format('Y-m-d');
            $currentDate = Carbon::createFromFormat('Y-m-d', $emision);
            $vencimiento = Carbon::parse($folioAfectar->purchase_expiration)->format('Y-m-d');
            $shippingDate = Carbon::createFromFormat('Y-m-d', $vencimiento);

            $diasCredito = $currentDate->diffInDays($shippingDate);
            $diasMoratorio = $shippingDate->diffInDays($currentDate);


            $cuentaPagar->accountsPayableP_creditDays = $diasCredito;
            $cuentaPagar->accountsPayableP_moratoriumDays = '-' . $diasMoratorio;


            $cuentaPagar->accountsPayableP_money = $folioAfectar->purchase_money;
            $cuentaPagar->accountsPayableP_typeChange = $folioAfectar->purchase_typeChange;
            $cuentaPagar->accountsPayableP_provider = $folioAfectar->purchase_provider;
            $cuentaPagar->accountsPayableP_condition = $folioAfectar->purchase_condition;

            $cuentaPagar->accountsPayableP_amount = $folioAfectar->purchase_amount;
            $cuentaPagar->accountsPayableP_taxes = $folioAfectar->purchase_taxes;
            $cuentaPagar->accountsPayableP_total = $folioAfectar->purchase_total;
            $cuentaPagar->accountsPayableP_balanceTotal = $folioAfectar->purchase_total;
            $cuentaPagar->accountsPayableP_concept = $folioAfectar->purchase_concept;
            $cuentaPagar->accountsPayableP_reference = $folioAfectar->purchase_reference;
            $cuentaPagar->accountsPayableP_balance = $folioAfectar->purchase_total;
            $cuentaPagar->accountsPayableP_company = $folioAfectar->purchase_company;
            $cuentaPagar->accountsPayableP_branchOffice = $folioAfectar->purchase_branchOffice;
            $cuentaPagar->accountsPayableP_user = $folioAfectar->purchase_user;
            $cuentaPagar->accountsPayableP_status = $this->estatus[1];
            $cuentaPagar->accountsPayableP_origin = $folioAfectar->purchase_movement;
            $cuentaPagar->accountsPayableP_originID = $folioAfectar->purchase_movementID;
            $cuentaPagar->accountsPayableP_originType =  'Compras';


            $create = $cuentaPagar->save();

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

    public function agregarAlmacen($folio)
    {
        $folioAfectar = PROC_PURCHASE::where('purchase_id', '=', $folio)->first();

        if ($folioAfectar->purchase_status == $this->estatus[2] && $folioAfectar->purchase_movement == 'Entrada por Compra') {

            $articulos = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $folioAfectar->purchase_id)->get();

            foreach ($articulos as $articulo) {
                if ($articulo['purchaseDetails_type'] !== "Servicio") {
                    $cantidad = $articulo->purchaseDetails_inventoryAmount;
                    $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->purchaseDetails_article)->where('articlesInv_depot', '=', $folioAfectar->purchase_depot)->first();

                    $inventario == null ? $inventario = new PROC_ARTICLES_INV() : $inventario;
                    $inventario->articlesInv_depot = $folioAfectar->purchase_depot;
                    $inventario->articlesInv_branchKey = $folioAfectar->purchase_branchOffice;
                    $inventario->articlesInv_companieKey = $folioAfectar->purchase_company;
                    $inventario->articlesInv_inventory = ($inventario->articlesInv_inventory + $cantidad);
                    $inventario->articlesInv_article = $articulo->purchaseDetails_article;

                    $inventario->save();
                    $inventario = null;
                }
            }
        }
    }

    public function agregarMov($folio)
    {
        $folioAfectar = PROC_PURCHASE::where('purchase_id', '=', $folio)->first();

        if ($folioAfectar->purchase_status == $this->estatus[2] && $folioAfectar->purchase_movement == 'Entrada por Compra') {

            if ($folioAfectar->purchase_originID !== null) {

                $movAnterior = PROC_PURCHASE::where('purchase_movementID', '=', $folioAfectar->purchase_originID)->where('purchase_movement', '=', $folioAfectar->purchase_origin)->where('purchase_company', '=', $folioAfectar->purchase_company)->where('purchase_branchOffice', '=', $folioAfectar->purchase_branchOffice)->first();
                //AGREGAMOS LA ORDEN DE COMPRA PRIMERO
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->purchase_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->purchase_company;
                $movimiento->movementFlow_moduleOrigin = 'Compras';
                $movimiento->movementFlow_originID = $movAnterior->purchase_id;
                $movimiento->movementFlow_movementOrigin = $movAnterior->purchase_movement;
                $movimiento->movementFlow_movementOriginID = $movAnterior->purchase_movementID;
                $movimiento->movementFlow_moduleDestiny = 'Compras';
                $movimiento->movementFlow_destinityID = $folioAfectar->purchase_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->purchase_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->purchase_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();

                //AGREGAMOS LA ENTRADA DE COMPRA POSTERIORMENTE
                $movPosterior = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movementID', '=', $folioAfectar->purchase_movementID)->where('accountsPayable_movement', '=', 'Entrada por Compra')->where('accountsPayable_branchOffice', '=', $folioAfectar->purchase_branchOffice)->first();
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->purchase_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->purchase_company;
                $movimiento->movementFlow_moduleOrigin = 'Compras';
                $movimiento->movementFlow_originID = $folioAfectar->purchase_id;
                $movimiento->movementFlow_movementOrigin = $folioAfectar->purchase_movement;
                $movimiento->movementFlow_movementOriginID = $folioAfectar->purchase_movementID;
                $movimiento->movementFlow_moduleDestiny = 'CxP';
                $movimiento->movementFlow_destinityID = $movPosterior->accountsPayable_id;
                $movimiento->movementFlow_movementDestinity = $movPosterior->accountsPayable_movement;
                $movimiento->movementFlow_movementDestinityID = $movPosterior->accountsPayable_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();
            } else {
                $movPosterior = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movementID', '=', $folioAfectar->purchase_movementID)->where('accountsPayable_movement', '=', 'Entrada por Compra')->where('accountsPayable_branchOffice', '=', $folioAfectar->purchase_branchOffice)->first();
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->purchase_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->purchase_company;
                $movimiento->movementFlow_moduleOrigin = 'Compras';
                $movimiento->movementFlow_originID = $folioAfectar->purchase_id;
                $movimiento->movementFlow_movementOrigin = $folioAfectar->purchase_movement;
                $movimiento->movementFlow_movementOriginID = $folioAfectar->purchase_movementID;
                $movimiento->movementFlow_moduleDestiny = 'CxP';
                $movimiento->movementFlow_destinityID = $movPosterior->accountsPayable_id;
                $movimiento->movementFlow_movementDestinity = $movPosterior->accountsPayable_movement;
                $movimiento->movementFlow_movementDestinityID = $movPosterior->accountsPayable_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();
            }
        }

        if ($folioAfectar->purchase_status == $this->estatus[2] && $folioAfectar->purchase_movement == 'Rechazo de Compra') {

            if ($folioAfectar->purchase_originID !== null) {

                $movAnterior = PROC_PURCHASE::where('purchase_movementID', '=', $folioAfectar->purchase_originID)->where('purchase_movement', '=', $folioAfectar->purchase_origin)->where('purchase_company', '=', $folioAfectar->purchase_company)->where('purchase_branchOffice', '=', $folioAfectar->purchase_branchOffice)->first();
                //AGREGAMOS LA ORDEN DE COMPRA PRIMERO
                $movimiento = new PROC_MOVEMENT_FLOW();
                $movimiento->movementFlow_branch = $folioAfectar->purchase_branchOffice;
                $movimiento->movementFlow_company = $folioAfectar->purchase_company;
                $movimiento->movementFlow_moduleOrigin = 'Compras';
                $movimiento->movementFlow_originID = $movAnterior->purchase_id;
                $movimiento->movementFlow_movementOrigin = $movAnterior->purchase_movement;
                $movimiento->movementFlow_movementOriginID = $movAnterior->purchase_movementID;
                $movimiento->movementFlow_moduleDestiny = 'Compras';
                $movimiento->movementFlow_destinityID = $folioAfectar->purchase_id;
                $movimiento->movementFlow_movementDestinity = $folioAfectar->purchase_movement;
                $movimiento->movementFlow_movementDestinityID = $folioAfectar->purchase_movementID;
                $movimiento->movementFlow_cancelled = 0;
                $movimiento->save();
            }
        }
    }

    public function agregarSaldo($folio)
    {
        $folioAfectar = PROC_PURCHASE::where('purchase_id', '=', $folio)->first();


        if ($folioAfectar->purchase_status == $this->estatus[2] && $folioAfectar->purchase_movement == 'Entrada por Compra') {

            //agregamos el saldo del proveedor en la tabla de saldos
            $saldo = PROC_BALANCE::where('balance_account', '=', $folioAfectar->purchase_provider)->where('balance_branchKey', '=', $folioAfectar->purchase_branchOffice)->where('balance_companieKey', '=', $folioAfectar->purchase_company)->where('balance_money', '=', $folioAfectar->purchase_money)->where('balance_branch', '=', "CxP")->first();

            if ($saldo == null) {
                $saldo = new PROC_BALANCE();
                $saldo->balance_companieKey = $folioAfectar->purchase_company;
                $saldo->balance_branchKey = $folioAfectar->purchase_branchOffice;
                $saldo->balance_branch = 'CxP';
                $saldo->balance_money = $folioAfectar->purchase_money;
                $saldo->balance_account = $folioAfectar->purchase_provider;
                $saldo->balance_balance = $folioAfectar->purchase_total;
                $saldo->balance_reconcile = $saldo->balance_balance;

                $saldo->save();
            } else {
                $saldo->balance_balance = $saldo->balance_balance + $folioAfectar->purchase_total;
                $saldo->balance_reconcile = $saldo->balance_balance;
                $saldo->update();
            }
        }
    }

    public function ayuda()
    {

        $folioAfectar = PROC_PURCHASE::where('purchase_id', '=', 34)->first();
        //Actualizar articulos en la tabla de detalle de compra y la compra anterior
        if ($folioAfectar->purchase_originID !== null && $folioAfectar->purchase_status == $this->estatus[2]) {
            // //actualizar la compra anterior a concluido
            // $compraAnterior = PROC_PURCHASE::where('purchase_movementID', '=', $folioAfectar->purchase_originID)->first();
            // $compraAnterior->purchase_status = $this->estatus[2];
            // $compraAnterior->update();

            // $articulosCompraAnterior = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $compraAnterior->purchase_id)->get();

            // //verificamos que los articulos de la compra anterior sean iguales a los de la compra actual
            // foreach ($articulosCompraAnterior as $key => $articuloAnterior) {
            //     $articulo->purchaseDetails_outstandingAmount = null;
            //     $articulo->update();
            // }

            // $compraAnterior->purchase_status = $this->estatus[2];
            // $compraAnterior->update();
            $articulosPendientes2 = [];
            $isArticulosPendientesEntrega = false;
            $compraAnterior = PROC_PURCHASE::where('purchase_movementID', '=', $folioAfectar->purchase_originID)->where('purchase_branchOffice', '=', $folioAfectar->purchase_branchOffice)->where('purchase_movement', '=', 'Orden de Compra')->first();
            $articulosCompraAnterior = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $compraAnterior->purchase_id)->where('purchaseDetails_branchOffice', '=', $compraAnterior->purchase_branchOffice)->get();

            $articulosPendiente = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $folioAfectar->purchase_id)->where('purchaseDetails_branchOffice', '=', $folioAfectar->purchase_branchOffice)->get();




            $arrayArticulosPendientes = array();


            foreach ($articulosPendiente as $articulo) {
                $arrayArticulosPendientes[$articulo->purchaseDetails_article . '-' . $articulo->purchaseDetails_referenceArticles] = ['cantidad' => $articulo->purchaseDetails_quantity];
            }

            dd($compraAnterior,  $articulosCompraAnterior, $articulosPendiente, $arrayArticulosPendientes);
            foreach ($articulosCompraAnterior as $articulo) {
                if (isset($arrayArticulosPendientes[$articulo->purchaseDetails_article . '-' . $articulo->purchaseDetails_id]) && $articulo->purchaseDetails_outstandingAmount  > 0) {
                    $nuevoPendiente = ($articulo->purchaseDetails_outstandingAmount - $arrayArticulosPendientes[$articulo->purchaseDetails_article . '-' . $articulo->purchaseDetails_id]['cantidad']);
                    $articulo->purchaseDetails_outstandingAmount = $nuevoPendiente > 0 ? $nuevoPendiente : null;
                    $articulo->update();
                }

                $articulosPendientes2[] = $articulo->purchaseDetails_outstandingAmount;
            }

            foreach ($articulosPendientes2 as $key => $value) {
                if ($value !== null) {
                    $isArticulosPendientesEntrega = true;
                    break;
                } else {
                    $isArticulosPendientesEntrega = false;
                }
            }

            if (!$isArticulosPendientesEntrega) {
                $compraAnterior->purchase_status = $this->estatus[2];
                $compraAnterior->purchase_total = $compraAnterior->purchase_total - $folioAfectar->purchase_total;
                $compraAnterior->update();
            } else {
                $compraAnterior->purchase_total = $compraAnterior->purchase_total - $folioAfectar->purchase_total;
                $compraAnterior->update();
            }
        }
    }
    public function verificacionArticulosSerie($request)
    {
        $isRepetido = false;
        $articulos = $request->dataArticulosJson;
        $articulos = json_decode($articulos, true);
        $claveArt = array_keys($articulos);

        foreach ($claveArt as $clave) {
            if ($articulos[$clave]['tipoArticulo'] === "Serie") {
                if (isset($articulos[$clave]['asignacionSerie']) && count($articulos[$clave]['asignacionSerie']) > 0) {
                    foreach ($articulos[$clave]['asignacionSerie'] as $serie) {
                        $articulo = PROC_LOT_SERIES::WHERE('lotSeries_branchKey', '=', session('sucursal')->branchOffices_key)->WHERE('lotSeries_article', '=', explode('-', $clave)[0])->WHERE('lotSeries_lotSerie', '=', $serie)->first();

                        if ($articulo !== null) {
                            if ($articulo->lotSeries_existence !== null) {
                                $isRepetido = true;
                                break;
                            }
                        }

                        if ($articulo !== null) {
                            if ($articulo->lotSeries_existence === null) {
                                $articulo->lotSeries_existence = 1;
                                $articulo->update();
                            }
                        }
                    }
                }
            }

            if ($isRepetido) {
                break;
            }
        }
        return $isRepetido;
    }

    public function getConceptosByMovimiento(Request $request)
    {
        $movimientoSeleccionado = $request->input('movimiento');
        // dd($movimientoSeleccionado);
        if($movimientoSeleccionado === null){
            $conceptos = CONF_MODULES_CONCEPT::where('moduleConcept_status', '=', 'Alta')
                ->where('moduleConcept_module', '=', 'Compras')
                ->get();
        }
        else{
            $conceptos = CONF_MODULES_CONCEPT::join('CONF_MODULES_CONCEPT_MOVEMENT', 'CONF_MODULES_CONCEPT_MOVEMENT.moduleMovement_conceptID', '=', 'CONF_MODULES_CONCEPT.moduleConcept_id')
                ->where('moduleConcept_status', '=', 'Alta')
                ->where('moduleConcept_module', '=', 'Compras')
                ->where('moduleMovement_movementName', '=', $movimientoSeleccionado)
                ->get();
        }
        return response()->json($conceptos);
        
    }

    public function verificacionAlmacen($request)
    {
        $almacenValid = false;
        $articulos = $request->dataArticulosJson;
        $articulos = json_decode($articulos, true);
        $claveArt = array_keys($articulos);

        foreach ($claveArt as $clave) {
            if ($articulos[$clave]['tipoArticulo'] === "Serie") {
                //validar el almacen sea de activo fijo
                if ($request['almacenTipoKey'] !== "Activo Fijo") {
                    $almacenValid = true;
                    break;
                }
            }
        }
        return $almacenValid;
    }

    public function auxiliar($folio)
    {
        $folioAfectar = PROC_PURCHASE::where('purchase_id', '=', $folio)->first();

        // dd($folioAfectar);
        if ($folioAfectar->purchase_status == $this->estatus[2] && $folioAfectar->purchase_movement == 'Entrada por Compra') {

            //agregar datos a aux
            $auxiliar = new PROC_ASSISTANT();

            $auxiliar->assistant_companieKey = $folioAfectar->purchase_company;
            $auxiliar->assistant_branchKey = $folioAfectar->purchase_branchOffice;
            $auxiliar->assistant_branch = 'CxP';
            $auxiliar->assistant_movement = $folioAfectar->purchase_movement;
            $auxiliar->assistant_movementID = $folioAfectar->purchase_movementID;
            $auxiliar->assistant_module = 'CxP';

            //buscamos el modulo de cxp
            $cxp = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_movementID', '=', $folioAfectar->purchase_movementID)->where('accountsPayable_branchOffice', '=', $folioAfectar->purchase_branchOffice)->first();

            $auxiliar->assistant_moduleID = $cxp->accountsPayable_id;
            $auxiliar->assistant_money = $folioAfectar->purchase_money;
            $auxiliar->assistant_typeChange = $folioAfectar->purchase_typeChange;
            $auxiliar->assistant_account = $folioAfectar->purchase_provider;

            //ponemos fecha del ejercicio
            $year = Carbon::now()->year;
            //sacamos el periodo 
            $period = Carbon::now()->month;


            $auxiliar->assistant_year = $year;
            $auxiliar->assistant_period = $period;
            $auxiliar->assistant_charge = $folioAfectar->purchase_total;
            $auxiliar->assistant_payment = null;
            $auxiliar->assistant_apply = $folioAfectar->purchase_movement;
            $auxiliar->assistant_applyID = $folioAfectar->purchase_movementID;
            $auxiliar->assistant_canceled = 0;
            $auxiliar->assistant_reference = $folioAfectar->purchase_reference;


            $auxiliar->save();
        }
    }

    public function auxiliarU($folio)
    {
        $folioAfectar = PROC_PURCHASE::where('purchase_id', '=', $folio)->first();


        if ($folioAfectar->purchase_status == $this->estatus[2] && $folioAfectar->purchase_movement == 'Entrada por Compra') {


            //buscamos sus articulos
            $articulos = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $folioAfectar->purchase_id)->where('purchaseDetails_branchOffice', '=', $folioAfectar->purchase_branchOffice)->get();

            foreach ($articulos as $articulo) {
                //agregar datos a aux
                $auxiliarU = new PROC_ASSISTANT_UNITS();
                $auxiliarU->assistantUnit_companieKey = $folioAfectar->purchase_company;
                $auxiliarU->assistantUnit_branchKey = $folioAfectar->purchase_branchOffice;
                $auxiliarU->assistantUnit_branch = 'Inv';
                $auxiliarU->assistantUnit_movement = $folioAfectar->purchase_movement;
                $auxiliarU->assistantUnit_movementID = $folioAfectar->purchase_movementID;
                $auxiliarU->assistantUnit_module = 'Compras';
                $auxiliarU->assistantUnit_moduleID = $articulo->purchaseDetails_purchaseID;
                $auxiliarU->assistantUnit_money = $folioAfectar->purchase_money;
                $auxiliarU->assistantUnit_typeChange = $folioAfectar->purchase_typeChange;
                $auxiliarU->assistantUnit_group = $articulo->purchaseDetails_depot;
                $auxiliarU->assistantUnit_account = $articulo->purchaseDetails_article;
                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;
                $auxiliarU->assistantUnit_year = $year;
                $auxiliarU->assistantUnit_period = $period;

                if ($folioAfectar->purchase_money == session('generalParameters')->generalParameters_defaultMoney) {
                    $auxiliarU->assistantUnit_charge = $articulo->purchaseDetails_amount;
                } else {
                    $auxiliarU->assistantUnit_charge = ($articulo->purchaseDetails_amount * $folioAfectar->purchase_typeChange);
                }


                $auxiliarU->assistantUnit_payment = null;
                $auxiliarU->assistantUnit_chargeUnit = (float)$articulo->purchaseDetails_inventoryAmount;
                $auxiliarU->assistantUnit_paymentUnit = null;
                $auxiliarU->assistantUnit_apply = $articulo->purchaseDetails_apply;
                $auxiliarU->assistantUnit_applyID =  $articulo->purchaseDetails_applyIncrement;
                $auxiliarU->assistantUnit_canceled = 0;
                $auxiliarU->save();
            }
        }
    }

    public function costoPromedio($folio)
    {
        $folioAfectar = PROC_PURCHASE::where('purchase_id', '=', $folio)->first();
        //   dd($folioAfectar);

        if ($folioAfectar->purchase_status == $this->estatus[2] && $folioAfectar->purchase_movement == 'Entrada por Compra') {
            $contador = 0;
            $articuloClave = [];
            //sacamos sus articulos
            $articulos = PROC_PURCHASE_DETAILS::where('purchaseDetails_purchaseID', '=', $folioAfectar->purchase_id)->get();

            foreach ($articulos as $articulo) {
                $articuloClave[$contador] = $articulo->purchaseDetails_article;
                $contador++;
            }

            $cantidadAux = [];
            $cantidadAux2 = [];
            $cantidadInventario = [];


            foreach ($articuloClave as $key => $articulo) {
                $costoAuxArticulo = PROC_ASSISTANT_UNITS::where('assistantUnit_account', '=', $articulo)->where('assistantUnit_branchKey', '=', $folioAfectar->purchase_branchOffice)->where('assistantUnit_companieKey', '=', $folioAfectar->purchase_company)->where('assistantUnit_group', '=', $folioAfectar->purchase_depot)->get()->sum('assistantUnit_charge');
                $cantidadAux[$articulo] = $costoAuxArticulo;
                //  dd($costoAuxArticulo);

                $costoAuxArticulo2 = PROC_ASSISTANT_UNITS::where('assistantUnit_account', '=', $articulo)->where('assistantUnit_branchKey', '=', $folioAfectar->purchase_branchOffice)->where('assistantUnit_companieKey', '=', $folioAfectar->purchase_company)->where('assistantUnit_group', '=', $folioAfectar->purchase_depot)->get()->sum('assistantUnit_payment');

                if ($costoAuxArticulo2 == null) {
                    $costoAuxArticulo2 = 0;
                }
                $cantidadAux2[$articulo] = $costoAuxArticulo2;

                $cantidadArticulos = PROC_ARTICLES_INV::where('articlesInv_branchKey', '=', $folioAfectar->purchase_branchOffice)->where('articlesInv_article', '=', $articuloClave[$key])->where('articlesInv_depot', '=', $folioAfectar->purchase_depot)->get()->sum('articlesInv_inventory');
                $cantidadInventario[$articulo] = $cantidadArticulos;
            }


            foreach ($articulos as $articulo) {
                //agregamos costo promedio
                if ($cantidadInventario[$articulo->purchaseDetails_article] != 0) {
                    // if ($articulo->purchaseDetails_quantity > 0) {
                    $costoPromedio = ($cantidadAux[$articulo->purchaseDetails_article] - $cantidadAux2[$articulo->purchaseDetails_article]) / $cantidadInventario[$articulo->purchaseDetails_article];
                    // dd($costoPromedio);


                    if ($folioAfectar->purchase_money == session('generalParameters')->generalParameters_defaultMoney) {
                        $costoActual = ($articulo->purchaseDetails_unitCost / $articulo->purchaseDetails_inventoryAmount) * $articulo->purchaseDetails_quantity;
                    } else {
                        $costoActual = (($articulo->purchaseDetails_unitCost / $articulo->purchaseDetails_inventoryAmount) * $articulo->purchaseDetails_quantity) * $folioAfectar->purchase_typeChange;
                    }

                    $articuloCostoH = new PROC_ARTICLES_COST_HIS();
                    //  dd($costoPromedio);

                    $articuloCostoH2 = PROC_ARTICLES_COST_HIS::where('articlesCostHis_article', '=', $articulo->purchaseDetails_article)->where('articlesCostHis_branchKey', '=', $folioAfectar->purchase_branchOffice)->where('articlesCostHis_companieKey', '=', $folioAfectar->purchase_company)->where('articlesCostHis_depotKey', '=', $folioAfectar->purchase_depot)->orderBy('created_at', 'desc')->first();


                    if ($articuloCostoH2 === null) {

                        $articuloCostoH->articlesCostHis_companieKey = $folioAfectar->purchase_company;
                        $articuloCostoH->articlesCostHis_branchKey = $folioAfectar->purchase_branchOffice;
                        $articuloCostoH->articlesCostHis_depotKey = $folioAfectar->purchase_depot;
                        $articuloCostoH->articlesCostHis_article = $articulo->purchaseDetails_article;
                        $articuloCostoH->articlesCostHis_lastCost = $articuloCostoH2 ? $articuloCostoH2->articlesCostHis_currentCost : null;
                        $articuloCostoH->articlesCostHis_currentCost =  $costoActual;
                        $articuloCostoH->articlesCostHis_averageCost = $costoPromedio;
                        $articuloCostoH->created_at = date('Y-m-d H:i:s');
                        $articuloCostoH->save();
                    } else {
                        if ($articuloCostoH2->articlesCostHis_currentCost != $costoPromedio) {
                            $articuloCostoH->articlesCostHis_companieKey = $folioAfectar->purchase_company;
                            $articuloCostoH->articlesCostHis_branchKey = $folioAfectar->purchase_branchOffice;
                            $articuloCostoH->articlesCostHis_depotKey = $folioAfectar->purchase_depot;
                            $articuloCostoH->articlesCostHis_article = $articulo->purchaseDetails_article;
                            $articuloCostoH->articlesCostHis_lastCost = $articuloCostoH2 ? $articuloCostoH2->articlesCostHis_currentCost : null;
                            $articuloCostoH->articlesCostHis_currentCost =  $costoActual;
                            $articuloCostoH->articlesCostHis_averageCost = $costoPromedio;
                            $articuloCostoH->created_at = date('Y-m-d H:i:s');
                            $articuloCostoH->save();
                        }
                    }
                    // dd($articuloCostoH);

                    //agregamos costo promedio

                    $articuloCosto = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articulo->purchaseDetails_article)->where('articlesCost_branchKey', '=', $folioAfectar->purchase_branchOffice)->where('articlesCost_companieKey', '=', $folioAfectar->purchase_company)->where('articlesCost_depotKey', '=', $folioAfectar->purchase_depot)->first();

                    $articuloReferencia = PROC_ARTICLES_COST_HIS::where('articlesCostHis_article', '=', $articulo->purchaseDetails_article)->where('articlesCostHis_branchKey', '=', $folioAfectar->purchase_branchOffice)->where('articlesCostHis_companieKey', '=', $folioAfectar->purchase_company)->where('articlesCostHis_depotKey', '=', $folioAfectar->purchase_depot)->orderBy('created_at', 'desc')->first();

                    if ($articuloCosto == null) {
                        $articuloCosto = new PROC_ARTICLES_COST();
                        $articuloCosto->articlesCost_companieKey = $folioAfectar->purchase_company;
                        $articuloCosto->articlesCost_branchKey = $folioAfectar->purchase_branchOffice;
                        $articuloCosto->articlesCost_depotKey = $folioAfectar->purchase_depot;
                        $articuloCosto->articlesCost_article = $articulo->purchaseDetails_article;
                        $articuloCosto->articlesCost_lastCost = $articuloReferencia ? $articuloReferencia->articlesCostHis_currentCost : null;
                        $articuloCosto->articlesCost_averageCost =  $articuloReferencia->articlesCostHis_averageCost;
                        $articuloCosto->save();
                        $this->articulosListaPrecios($folioAfectar, $articulo->purchaseDetails_article, $articulo->purchaseDetails_article);
                    } else {
                        $articuloCosto->articlesCost_companieKey = $folioAfectar->purchase_company;
                        $articuloCosto->articlesCost_branchKey = $folioAfectar->purchase_branchOffice;
                        $articuloCosto->articlesCost_depotKey = $folioAfectar->purchase_depot;
                        $articuloCosto->articlesCost_article = $articulo->purchaseDetails_article;
                        $articuloCosto->articlesCost_lastCost = $articuloReferencia ? $articuloReferencia->articlesCostHis_currentCost : null;
                        $articuloCosto->articlesCost_averageCost =  $articuloReferencia->articlesCostHis_averageCost;
                        $articuloCosto->update();
                        $this->articulosListaPrecios($folioAfectar, $articulo->purchaseDetails_article, $articulo->purchaseDetails_article);
                    }
                    // }
                }
            }
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

    public function getCostoPromedio(Request $request)
    {

        $proveedor = CAT_PROVIDERS::where('providers_key', '=', $request->idProveedor)->first();

        if ($proveedor->providers_priceList != null) {
            $listaProveedor = CAT_ARTICLES_LIST::where('articlesList_listID', '=', $proveedor->providers_priceList)->where('articlesList_article', '=',  $request->id)->first();
        } else {
            $listaProveedor = null;
        }

        $compra = PROC_PURCHASE::where('purchase_id', '=', $request->idCompra)->first();


        $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $request->id)->join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->join('PROC_ARTICLES_COST', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_COST.articlesCost_article')->join('CONF_UNITS', 'CAT_ARTICLES.articles_unitBuy', '=', 'CONF_UNITS.units_id')->join('CAT_DEPOTS', 'PROC_ARTICLES_COST.articlesCost_depotKey', '=', 'CAT_DEPOTS.depots_key')->where('articlesCost_branchKey', '=', $compra->purchase_branchOffice)->where('articlesCost_depotKey', '=', $compra->purchase_depot)->where('PROC_ARTICLES_INV.articlesInv_depot', '=', $compra->purchase_depot)->first();



        $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $request->id)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->get();

        if ($infoArticulo == null) {
            $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $request->id)->first();
            $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $request->id)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->get();
        }
        if ($infoArticulo != null) {
            $status = 200;
        } else {
            $status = 404;
        }

        return response()->json(['data' => $infoArticulo, 'estatus' => $status, 'articulosByAlmacen' => $articulosByAlmacen, 'listaProveedor' => $listaProveedor]);
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


    public function getTipoCambio(Request $request)
    {

        $tipoCambio = CONF_MONEY::where('money_key', '=', $request->tipoCambio)->first();



        return response()->json($tipoCambio);
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





    //Obtenemos toda la informacion del proveedor seleccionado
    public function getProveedor(Request $request)
    {
        //validamos que el proveedor este dado de alta
        $proveedor = CAT_PROVIDERS::where('providers_key', '=', $request->proveedor)
            ->where('providers_status', '=', 'Alta')
            ->first();
        return response()->json($proveedor);
    }

    //Obtenemos toda la información del almacen seleccionado
    public function getAlmacen(Request $request)
    {
        //validamos que el almacen este dado de alta
        $almacen = CAT_DEPOTS::where('depots_key', '=', $request->almacen)
            ->where('depots_status', '=', 'Alta')
            ->where('CAT_DEPOTS.depots_branchlId', '=', session('sucursal')->branchOffices_key)
            ->first();
        return response()->json($almacen);
    }

    //Obtenemos la condicion de pago del proveedor asignado
    public function getCondicionPago(Request $request)
    {
        $condicionPago = CONF_CREDIT_CONDITIONS::where('creditConditions_id', '=', $request->condicionPago)->first();
        return response()->json($condicionPago);
    }

    //Obtenemos las multiunidades
    public function getMultiUnidad(Request $request)
    {
        $multiUnidad = CAT_ARTICLES_UNITS::where('articlesUnits_article', '=', $request->factorUnidad)->get();
        return response()->json($multiUnidad);
    }

    //Obtenemos el reporte de la compra seleccionada
    public function getReporteCompra($id)
    {

        $compra = PROC_PURCHASE::join('CAT_PROVIDERS', 'CAT_PROVIDERS.providers_key', '=', 'PROC_PURCHASE.purchase_provider')
            ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_PURCHASE.purchase_condition')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_PURCHASE.purchase_branchOffice')
            ->join('CONF_MONEY', 'CONF_MONEY.money_key', '=', 'PROC_PURCHASE.purchase_money')
            ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_PURCHASE.purchase_company')
            ->where('purchase_id', '=', $id)
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


        $articulos_compra = PROC_PURCHASE_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_unit')
            ->WHERE('purchaseDetails_purchaseID', '=', $id)->get();

        $pdf = PDF::loadView('reportes.compras-reporte', ['compra' => $id, 'logo' => $logoBase64, 'compra' => $compra, 'articulos_compra' => $articulos_compra]);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream();
    }

    public function comprasAction(Request $request)
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

        if ($fechaInicio !== null && $fechaFinal !== null && $nameFecha === "Rango Fechas") {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }

        switch ($request->input('action')) {
            case 'Búsqueda':
                $compras_collection_filtro = PROC_PURCHASE::join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CONF_CREDIT_CONDITIONS', 'PROC_PURCHASE.purchase_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
                    ->join('CAT_COMPANIES', 'PROC_PURCHASE.purchase_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('CAT_DEPOTS', 'PROC_PURCHASE.purchase_depot', '=', 'CAT_DEPOTS.depots_key')
                    ->wherePurchaseMovementID($nameFolio)
                    ->wherePurchaseProvider($nameKey)
                    ->wherePurchaseMovement($nameMov)
                    ->wherePurchaseStatus($status)
                    ->wherePurchaseDate($nameFecha)
                    ->wherePurchaseUser($nameUsuario)
                    ->wherePurchasebranchOffice($nameSucursal)
                    ->wherePurchaseMoney($nameMoneda)
                    ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
                    ->orderBY('PROC_PURCHASE.purchase_movement', 'asc')
                    ->orderBy('PROC_PURCHASE.purchase_movementID', 'asc')
                    ->get();


                $compras_filtro_array = $compras_collection_filtro->toArray();

                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.modulo.compras')->with('compras_filtro_array', $compras_filtro_array)
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
                $compra = new PROC_ComprasExport($nameFolio, $nameKey, $nameMov, $status, $nameFecha, $nameUsuario, $nameSucursal, $nameMoneda);
                return Excel::download($compra, 'compras.xlsx');
                break;

            default:
                break;
        }
    }

    public function getArticulosSerie(Request $request)
    {
        $limit = abs($request->limit);
        $articuloSerie = PROC_LOT_SERIES_MOV::where('lotSeriesMov_articleID', '=', $request->id)->where('lotSeriesMov_companieKey', '=', session('company')->companies_key)->where('lotSeriesMov_branchKey', '=', session('sucursal')->branchOffices_key)->where('lotSeriesMov_purchaseID', '=', $request->idCompra)->where('lotSeriesMov_article', '=', $request->claveArticulo)->limit($limit)->get()->unique('lotSeriesMov_lotSerie');
        return response()->json(['status' => 200, 'data' => $articuloSerie]);
    }

    function dateRange($first, $last, $step = '+1 day', $format = 'Y-m-d')
    {
        $dates = array();
        $current = strtotime($first);
        $last = strtotime($last);
        while (($current <= $last) && ($current <= strtotime('+1 day'))) {
            $dates[] = date($format, $current);
            $current = strtotime($current . $step);
        }
        return $dates;
    }


    //Obtenemos el articulo por clave
    function getArticulo(Request $request)
    {

        $unidad = $this->getConfUnidades();
        //Buscamos en la lista de precios del proveedor
        $articuloList = CAT_ARTICLES_LIST::join('CAT_ARTICLES', 'CAT_ARTICLES_LIST.articlesList_article', '=', 'CAT_ARTICLES.articles_key')->WHERE('articlesList_listID', '=', $request->listPrecio)->WHERE('CAT_ARTICLES.articles_status', '=', 'Alta')->WHERE('articlesList_article', '=', $request->clave)->WHEREIN('CAT_ARTICLES.articles_type', ['Normal', 'Serie'])->first();

        if ($articuloList  == null) {
            $articulo = CAT_ARTICLES::WHERE('articles_key', '=', $request->clave)->WHERE('articles_status', '=', 'Alta')->WHEREIN('articles_type', ['Normal', 'Serie'])->first();    

            if ($articulo == null) {
                return response()->json(['status' => false, 'data' => 'Articulo no encontrado']);
            } else {
                $articulo = collect($articulo)->toArray();

                $unidadDefectoArticulo = $unidad[$articulo['articles_unitBuy']];
                $articuloUnidad = (object) array_merge((array) $articulo, (array) ['unidad' => $unidadDefectoArticulo]);

                return response()->json(['status' => true, 'data' =>  $articuloUnidad]);
            }
        } else {
            $articulo = collect($articuloList)->toArray();
            $unidadDefectoArticulo = $unidad[$articulo['articles_unitBuy']];
            $articuloUnidad = (object) array_merge((array) $articulo, (array) ['unidad' => $unidadDefectoArticulo]);
            return response()->json(['status' => true, 'data' =>  $articuloUnidad]);
        }
    }


    function getDecimalesUnidad(Request $request)
    {
        $unidadDecimal = CONF_UNITS::where('units_unit', '=', $request->unidadFactor)->select('units_decimalVal')->first();

        if ($unidadDecimal == null) {
            return response()->json(['status' => false, 'data' => 'Unidad no encontrada']);
        }
        return response()->json(['status' => true, 'data' => $unidadDecimal]);
    }

    //Traemos los articulos conforme a la lista de precios o al check que trae todos los articulos
    function articulosListaPrecioProveedor(Request $request)
    {
        // dd($request->all());
        $todosLosArticulos = $request->reference;
        // dd($todosLosArticulos);
        $idListaPrecio = $request->listaPrecio;
        $movFiltro = [];


        $unidad = $this->getConfUnidades();

        if ($idListaPrecio != "null") {
            //Buscamos los articulos de la lista
            if ($idListaPrecio != "false" && $todosLosArticulos == "false") {
                $articulosListProveedor = CAT_ARTICLES_LIST::join('CAT_ARTICLES', 'CAT_ARTICLES_LIST.articlesList_article', '=', 'CAT_ARTICLES.articles_key')->WHERE('articlesList_listID', '=', $idListaPrecio)->WHERE('CAT_ARTICLES.articles_status', '=', 'Alta')->WHEREIN('CAT_ARTICLES.articles_type', ['Normal', 'Serie'])->get();

                if (count($articulosListProveedor) > 0) {
                    foreach ($articulosListProveedor as $mov) {
                        $movFiltro["data"][] = [
                            "clave" => $mov['articles_key'],
                            'nombre' => $mov['articles_descript'],
                            'iva' => $mov['articles_porcentIva'],
                            'unidad' => $unidad[$mov['articles_unitBuy']],
                            'tipo' => $mov['articles_type'],
                            'ultimoCosto' => $mov['articlesList_lastCost'],
                            'costoPromedio' =>  $mov['articlesList_averageCost']
                        ];
                    }
                }
            }
            //Buscamos todos los articulos en estatus alta
            if ($todosLosArticulos == "true") {
                $todosArticulos = CAT_ARTICLES::WHERE('articles_status', '=', 'Alta')->WHEREIN('CAT_ARTICLES.articles_type', ['Normal', 'Serie'])->get();

                if (count($todosArticulos) > 0) {
                    foreach ($todosArticulos  as $mov) {
                        $articuloList = CAT_ARTICLES_LIST::join('CAT_ARTICLES', 'CAT_ARTICLES_LIST.articlesList_article', '=', 'CAT_ARTICLES.articles_key')->WHERE('articlesList_listID', '=', $idListaPrecio)->WHERE('CAT_ARTICLES.articles_status', '=', 'Alta')->WHERE('articlesList_article', '=', $mov['articles_key'])->WHEREIN('CAT_ARTICLES.articles_type', ['Normal', 'Serie'])->first();

                        if ($articuloList != null) {
                            $movFiltro["data"][] = [
                                "clave" => $mov['articles_key'],
                                'nombre' => $mov['articles_descript'],
                                'iva' => $mov['articles_porcentIva'],
                                'unidad' => $unidad[$mov['articles_unitBuy']],
                                'tipo' => $mov['articles_type'],
                                'ultimoCosto' => $articuloList['articlesList_lastCost'],
                                'costoPromedio' => $articuloList['articlesList_averageCost']
                            ];
                        } else {
                            $movFiltro["data"][] = [
                                "clave" => $mov['articles_key'],
                                'nombre' => $mov['articles_descript'],
                                'iva' => $mov['articles_porcentIva'],
                                'unidad' => $unidad[$mov['articles_unitBuy']],
                                'tipo' => $mov['articles_type'],
                                'ultimoCosto' => 0,
                                'costoPromedio' => 0
                            ];
                        }
                    }
                }
            }
        }

        if (count($movFiltro) == 0) {
            $movFiltro["data"] = [];
        }

        return json_encode($movFiltro);
    }

    function articulosListaPrecios($folioAfectar, $claveArticulo)
    {
        //obtenemos el id de la lista de precio del movimiento entrada
        $listaPreciosArticulos = CAT_ARTICLES_LIST::WHERE('articlesList_listID', '=', $folioAfectar->purchase_listPriceProvider)->WHERE('articlesList_article', '=', $claveArticulo)->first();

        $articuloCosto = PROC_ARTICLES_COST::where('articlesCost_article', '=',  $claveArticulo)->where('articlesCost_branchKey', '=', $folioAfectar->purchase_branchOffice)->where('articlesCost_companieKey', '=', $folioAfectar->purchase_company)->where('articlesCost_depotKey', '=', $folioAfectar->purchase_depot)->first();

        if ($articuloCosto == null) {
            $articuloCosto = 0.00;
        }

        if ($listaPreciosArticulos != null) {
            $ultimoCosto = $listaPreciosArticulos->articlesList_lastCost;

            $listaPreciosArticulos->articlesList_lastCost = $articuloCosto->articlesCost_lastCost;
            $listaPreciosArticulos->articlesList_averageCost = $articuloCosto->articlesCost_averageCost;
            //actualizamos el penultimo costo si es que son diferentes
            if ($ultimoCosto != $articuloCosto->articlesCost_lastCost) {
                $listaPreciosArticulos->articlesList_penultimateCost = $ultimoCosto;
            }

            $listaPreciosArticulos->articlesList_lastPurchase = Carbon::now();
            $listaPreciosArticulos->save();
        } else {
            $datosArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $claveArticulo)->first();
            $nuevoArticuloListaPrecio = new CAT_ARTICLES_LIST();
            $nuevoArticuloListaPrecio->articlesList_article = $claveArticulo;
            $nuevoArticuloListaPrecio->articlesList_listID = $folioAfectar->purchase_listPriceProvider;
            $nuevoArticuloListaPrecio->articlesList_nameArticle = $datosArticulo->articles_descript;
            $nuevoArticuloListaPrecio->articlesList_lastCost = $articuloCosto->articlesCost_lastCost;
            $nuevoArticuloListaPrecio->articlesList_averageCost = $articuloCosto->articlesCost_averageCost;
            $nuevoArticuloListaPrecio->articlesList_penultimateCost =  $articuloCosto->articlesCost_lastCost;
            $nuevoArticuloListaPrecio->articlesList_lastPurchase = Carbon::now();
            $nuevoArticuloListaPrecio->save();
        }
    }

    function articulosConExistencia(Request $request)
    {
        $ArticulosExistentes = $request->reference;
        $movFiltro = [];

        $unidad = $this->getConfUnidades();

        if ($ArticulosExistentes == "true") {
            $articulosExistentes = PROC_ARTICLES_INV::join('CAT_ARTICLES', 'PROC_ARTICLES_INV.articlesInv_article', '=', 'CAT_ARTICLES.articles_key')->WHERE('articlesInv_inventory', '>', 0)->WHERE('CAT_ARTICLES.articles_status', '=', 'Alta')->WHEREIN('CAT_ARTICLES.articles_type', ['Normal', 'Serie'])
                ->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)
                ->where('PROC_ARTICLES_INV.articlesInv_branchKey', '=', session('sucursal')->branchOffices_key)
                // ->where('PROC_ARTICLES_INV.articlesInv_depot', '=', $request->almacen)
            ->get()
            ->unique('articlesInv_article');

            if (count($articulosExistentes) > 0) {
                foreach ($articulosExistentes  as $mov) {
                    $movFiltro["data"][] = [
                        "clave" => $mov['articles_key'],
                        'nombre' => $mov['articles_descript'],
                        'iva' => $mov['articles_porcentIva'],
                        'unidad' => $unidad[$mov['articles_unitBuy']],
                        'tipo' => $mov['articles_type'],
                        'ultimoCosto' => 0,
                        'costoPromedio' => 0
                    ];
                }
            }
        } 
        if($ArticulosExistentes == "false") {
            // dd($ArticulosExistentes);
            $articulosExistentes = CAT_ARTICLES::WHEREIN('articles_type', ['Normal', 'Serie'])->WHERE('articles_status', '=', 'Alta')->get();
                // dd($articulosExistentes);

            if (count($articulosExistentes) > 0) {
                foreach ($articulosExistentes  as $mov) {
                    $movFiltro["data"][] = [
                        "clave" => $mov['articles_key'],
                        'nombre' => $mov['articles_descript'],
                        'iva' => $mov['articles_porcentIva'],
                        'unidad' => $unidad[$mov['articles_unitBuy']],
                        'tipo' => $mov['articles_type'],
                        'ultimoCosto' => 0,
                        'costoPromedio' => 0
                    ];
                }
            }
        }

        if (count($movFiltro) == 0) {
            $movFiltro["data"] = [];
        }

        return json_encode($movFiltro);
    }

    function articulosCategoria (Request $request)
    {
        $ArticulosExistentes = $request->categoria;
        // dd($ArticulosExistentes);
        $movFiltro = [];

        $unidad = $this->getConfUnidades();

        if ($ArticulosExistentes !== '') {
            $articuloCategoria = CAT_ARTICLES::WHERE('articles_category', '=', $ArticulosExistentes)->WHERE('articles_status', '=', 'Alta')->WHEREIN('CAT_ARTICLES.articles_type', ['Normal', 'Serie'])
            ->get();
            // dd($articuloCategoria);
        }

        if (count($articuloCategoria) > 0) {
            foreach ($articuloCategoria  as $mov) {
                $movFiltro["data"][] = [
                    "clave" => $mov['articles_key'],
                    'nombre' => $mov['articles_descript'],
                    'iva' => $mov['articles_porcentIva'],
                    'unidad' => $unidad[$mov['articles_unitBuy']],
                    'tipo' => $mov['articles_type'],
                    'ultimoCosto' => 0,
                    'costoPromedio' => 0
                ];
                // dd($movFiltro);
            }
        }

        if (count($movFiltro) == 0) {
            $movFiltro["data"] = [];
        }

        return json_encode($movFiltro);
    }

    function articulosFamilia (Request $request)
    {
        $ArticulosExistentes = $request->familia;
        // dd($ArticulosExistentes);
        $movFiltro = [];

        $unidad = $this->getConfUnidades();

        if ($ArticulosExistentes !== '') {
            $articuloFamilia = CAT_ARTICLES::WHERE('articles_family', '=', $ArticulosExistentes)->WHERE('articles_status', '=', 'Alta')->WHEREIN('CAT_ARTICLES.articles_type', ['Normal', 'Serie'])
            ->get();
            // dd($articuloCategoria);
        }

        if (count($articuloFamilia) > 0) {
            foreach ($articuloFamilia  as $mov) {
                $movFiltro["data"][] = [
                    "clave" => $mov['articles_key'],
                    'nombre' => $mov['articles_descript'],
                    'iva' => $mov['articles_porcentIva'],
                    'unidad' => $unidad[$mov['articles_unitBuy']],
                    'tipo' => $mov['articles_type'],
                    'ultimoCosto' => 0,
                    'costoPromedio' => 0
                ];
                // dd($movFiltro);
            }
        }

        if (count($movFiltro) == 0) {
            $movFiltro["data"] = [];
        }

        return json_encode($movFiltro);
    }  

    public function articulosGrupo(Request $request)
    {
        $ArticulosExistentes = $request->grupo;
        // dd($ArticulosExistentes);
        $movFiltro = [];

        $unidad = $this->getConfUnidades();

        if ($ArticulosExistentes !== '') {
            $articuloGrupo = CAT_ARTICLES::WHERE('articles_group', '=', $ArticulosExistentes)->WHERE('articles_status', '=', 'Alta')->WHEREIN('CAT_ARTICLES.articles_type', ['Normal', 'Serie'])
            ->get();
            // dd($articuloCategoria);
        }

        if (count($articuloGrupo) > 0) {
            foreach ($articuloGrupo  as $mov) {
                $movFiltro["data"][] = [
                    "clave" => $mov['articles_key'],
                    'nombre' => $mov['articles_descript'],
                    'iva' => $mov['articles_porcentIva'],
                    'unidad' => $unidad[$mov['articles_unitBuy']],
                    'tipo' => $mov['articles_type'],
                    'ultimoCosto' => 0,
                    'costoPromedio' => 0
                ];
                // dd($movFiltro);
            }
        }

        if (count($movFiltro) == 0) {
            $movFiltro["data"] = [];
        }

        return json_encode($movFiltro);
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