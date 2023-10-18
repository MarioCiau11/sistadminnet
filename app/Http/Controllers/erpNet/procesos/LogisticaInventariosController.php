<?php

namespace App\Http\Controllers\erpNet\procesos;

use App\Exports\PROC_InventariosExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_ARTICLES_CATEGORY;
use App\Models\agrupadores\CAT_ARTICLES_FAMILY;
use App\Models\agrupadores\CAT_ARTICLES_GROUP;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_ARTICLES_UNITS;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_DEPOTS;
use App\Models\catalogos\CAT_PROVIDERS;
use App\Models\catalogos\CONF_CREDIT_CONDITIONS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS_CONSECUTIVES;
use App\Models\catalogos\CONF_MODULES_CONCEPT;
use App\Models\catalogos\CONF_MONEY;
use App\Models\catalogos\CONF_UNITS;
use App\Models\modulos\helpers\PROC_ARTICLES_COST;
use App\Models\modulos\helpers\PROC_ARTICLES_COST_HIS;
use App\Models\modulos\PROC_ARTICLES_INV;
use App\Models\modulos\PROC_ASSISTANT_UNITS;
use App\Models\modulos\PROC_DEL_SERIES_MOV;
use App\Models\modulos\PROC_DEL_SERIES_MOV2;
use App\Models\modulos\PROC_INVENTORIES;
use App\Models\modulos\PROC_INVENTORIES_DETAILS;
use App\Models\modulos\PROC_LOT_SERIES;
use App\Models\modulos\PROC_LOT_SERIES_MOV;
use App\Models\modulos\PROC_LOT_SERIES_MOV2;
use App\Models\modulos\PROC_MOVEMENT_FLOW;
use App\Models\modulos\PROC_TREASURY;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use stdClass;

use function PHPUnit\Framework\throwException;

class LogisticaInventariosController extends Controller
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
        $select_sucursales = $this->selectSucursales();
        $select_users = $this->selectUsuarios();


        //asignar un nombre a una tabla para poder hacer un join
        $inventarios = PROC_INVENTORIES::join('CAT_BRANCH_OFFICES', 'PROC_INVENTORIES.inventories_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CAT_COMPANIES', 'PROC_INVENTORIES.inventories_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('CAT_DEPOTS', 'PROC_INVENTORIES.inventories_depot', '=', 'CAT_DEPOTS.depots_key')
            ->join('CAT_DEPOTS as CAT_DEPOTS2', 'PROC_INVENTORIES.inventories_depotDestiny', '=', 'CAT_DEPOTS2.depots_key', 'left')
            ->select('PROC_INVENTORIES.*', 'CAT_BRANCH_OFFICES.branchOffices_name', 'CAT_COMPANIES.companies_name', 'CAT_DEPOTS.depots_name', 'CAT_DEPOTS2.depots_name as depots_nameDestiny')
            ->where('PROC_INVENTORIES.inventories_company', '=', session('company')->companies_key)
            ->where('PROC_INVENTORIES.inventories_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->where('PROC_INVENTORIES.inventories_user', '=', Auth::user()->username)
            ->orderBy('PROC_INVENTORIES.updated_at', 'desc')->get();



        return view('page.modulos.logistica.inventarios.index-inventarios', compact('fecha_actual', 'select_users', 'select_sucursales', 'inventarios'));
    }

    public function create(Request $request)
    {

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();

        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }
        if ($parametro->generalParameters_defaultMoney == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de seleccionar la moneda por defecto');
        }
        try {


            //Obtenemos los permisos que tiene el usuario para el modulo de compras
            $usuario = Auth::user();
            $permisos = $usuario->getAllPermissions()->where('categoria', '=', 'Inventarios')->pluck('name')->toArray();
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
            $select_monedas = CONF_MONEY::WHERE('money_status', '=', 'Alta')->get();
            $select_conceptos = CONF_MODULES_CONCEPT::WHERE('moduleConcept_status', '=', 'Alta')->WHERE('moduleConcept_module', '=', 'Inventarios')->get();
            $fecha_actual = Carbon::now()->format('Y-m-d');
            $selectMonedas = $this->getMonedas();

            $parametro = CONF_GENERAL_PARAMETERS::join('CONF_MONEY', 'CONF_GENERAL_PARAMETERS.generalParameters_defaultMoney', '=', 'CONF_MONEY.money_key')
                ->select('CONF_GENERAL_PARAMETERS.*', 'CONF_MONEY.money_change')
                ->where('CONF_GENERAL_PARAMETERS.generalParameters_company', '=', session('company')->companies_key)
                ->first();
            if ($parametro == null) {
                return redirect()->back()->with('status', false)->with('message', 'Favor de registrar los parametros generales');
            }
            $select_condicionPago = CONF_CREDIT_CONDITIONS::WHERE('creditConditions_status', '=', 'Alta')->get();
            $unidad = $this->getConfUnidades();
            $articulos = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article', 'left')->WHERE('articles_status', '=', 'Alta')->get();


            // dd($articulos, $articulosUnidad);

            $almacenes = CAT_DEPOTS::join('CAT_BRANCH_OFFICES', 'CAT_DEPOTS.depots_branchlId', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                ->join('CAT_COMPANIES', 'CAT_BRANCH_OFFICES.branchOffices_companyId', '=', 'CAT_COMPANIES.companies_key')
                ->where('CAT_COMPANIES.companies_key', '=', session('company')->companies_key)
                ->where('CAT_BRANCH_OFFICES.branchOffices_key', '=', session('sucursal')->branchOffices_key)
                ->where('CAT_DEPOTS.depots_status', '=', 'Alta')
                ->select('CAT_DEPOTS.*')
                ->get();
            // dd($almacenes);
            $almacenesDestino = CAT_DEPOTS::join('CAT_BRANCH_OFFICES', 'CAT_DEPOTS.depots_branchlId', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                ->where('branchOffices_companyId', '=', session('company')->companies_key)->where('CAT_DEPOTS.depots_status', '=', 'Alta')->get();
            // dd($almacenesDestino);




            if (isset($request->id) && $request->id != null) {
                $inventario = PROC_INVENTORIES::find($request->id);
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


                if ($inventario->inventories_movement !== 'Tránsito') {
                    //Validamos si el usuario tiene permiso de ver los movimientos ya creados
                    if (!$usuario->can($inventario->inventories_movement . ' C')) {
                        return redirect()->route('vista.modulo.inventarios')->with('status', false)->with('message', 'No tiene permisos para visualizar este movimiento');
                    }
                }

                if ($inventario->inventories_movement === 'Tránsito') {
                    $movien = [
                        'Tránsito' => 'Tránsito'
                    ];
                    $movimientos = array_merge($movimientos, $movien);
                }






                if (!$inventario) {
                    throw new \Exception();
                }

                $articulosByInventario = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $request->id)->get();

                if (count($articulosByInventario) != 0) {

                    // $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $articulosByInventario[0]->inventoryDetails_article)->join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->join('PROC_ARTICLES_COST', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_COST.articlesCost_article')->join('CONF_UNITS', 'CAT_ARTICLES.articles_unitBuy', '=', 'CONF_UNITS.units_id')->join('CAT_DEPOTS', 'PROC_ARTICLES_COST.articlesCost_depotKey', '=', 'CAT_DEPOTS.depots_key')->where('articlesCost_branchKey', '=', $inventario->inventories_branchOffice)->where('articlesCost_depotKey', '=', $inventario->inventories_depot)->where('PROC_ARTICLES_INV.articlesInv_depot', '=', $inventario->inventories_depot)->where('PROC_ARTICLES_INV.articlesInv_branchKey', '=', $inventario->inventories_branchOffice)->first();
                    // dd($inventario);

                    $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $articulosByInventario[0]->inventoryDetails_article)->join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->join('PROC_ARTICLES_COST', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_COST.articlesCost_article')->join('CONF_UNITS', 'CAT_ARTICLES.articles_unitBuy', '=', 'CONF_UNITS.units_id')->join('CAT_DEPOTS', 'PROC_ARTICLES_COST.articlesCost_depotKey', '=', 'CAT_DEPOTS.depots_key')->where('articlesCost_branchKey', '=', session('sucursal')->branchOffices_key)->where('articlesCost_depotKey', '=', $inventario->inventories_depot)->where('PROC_ARTICLES_INV.articlesInv_branchKey', '=', session('sucursal')->branchOffices_key)->where('PROC_ARTICLES_INV.articlesInv_depot', '=', $inventario->inventories_depot)->first();
                    // dd($infoArticulo);


                    $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $articulosByInventario[0]->inventoryDetails_article)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->get();

                    if ($infoArticulo == null) {
                        $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $articulosByInventario[0]->inventoryDetails_article)->first();
                        $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $articulosByInventario[0]->inventoryDetails_article)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->get();
                    }

                    if ($inventario->inventories_movement === 'Entrada por Traspaso') {


                        $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $articulosByInventario[0]->inventoryDetails_article)->join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->join('PROC_ARTICLES_COST', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_COST.articlesCost_article')->join('CONF_UNITS', 'CAT_ARTICLES.articles_unitBuy', '=', 'CONF_UNITS.units_id')->join('CAT_DEPOTS', 'PROC_ARTICLES_COST.articlesCost_depotKey', '=', 'CAT_DEPOTS.depots_key')->where('articlesCost_branchKey', '=', $inventario->inventories_branchOffice)->where('articlesCost_depotKey', '=', $inventario->inventories_depotDestiny)->where('PROC_ARTICLES_INV.articlesInv_branchKey', '=', $inventario->inventories_branchOffice)->where('PROC_ARTICLES_INV.articlesInv_depot', '=', $inventario->inventories_depotDestiny)->first();

                        $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $request->id)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->where('PROC_ARTICLES_INV.articlesInv_branchKey', '=', $inventario->inventories_branchOffice)->get();

                        if ($infoArticulo == null) {
                            $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $articulosByInventario[0]->inventoryDetails_article)->first();
                            $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $articulosByInventario[0]->inventoryDetails_article)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->where('PROC_ARTICLES_INV.articlesInv_branchKey', '=', $inventario->inventories_branchOffice)->get();
                        }
                    }

                    //buscar el articulo en todos los almacenes 



                } else {
                    $infoArticulo = null;
                    $articulosByAlmacen = null;
                }

                $primerFlujoDeInventario = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
                    ->WHERE('movementFlow_originID', '=', $inventario->inventories_id)
                    ->WHERE('movementFlow_movementOriginID', '=', $inventario->inventories_movementID)
                    ->WHERE('movementFlow_moduleOrigin', '=', 'Inv')
                    ->get();

                // dd($primerFlujoDeInventario);

                if (count($primerFlujoDeInventario) === 0) {
                    $primerFlujoDeInventario = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
                        ->WHERE('movementFlow_destinityID', '=', $inventario->inventories_id)
                        ->WHERE('movementFlow_movementDestinityID', '=', $inventario->inventories_movementID)
                        ->WHERE('movementFlow_moduleDestiny', '=', 'Inv')
                        ->get();
                    // dd($primerFlujoDeInventario);
                }
                // dd($infoArticulo);

                return view('page.modulos.logistica.inventarios.create-inventarios', compact('fecha_actual', 'select_monedas', 'select_conceptos', 'selectMonedas', 'parametro', 'select_condicionPago', 'almacenes', 'unidad', 'inventario', 'articulosByInventario', 'almacenesDestino', 'infoArticulo', 'primerFlujoDeInventario', 'articulosByAlmacen', 'movimientos', 'articulos', 'usuario', 'select_categoria', 'select_grupo', 'select_familia'));
            } else {
                $inventario = null;
                $articulosByInventario = null;
            }



            return view('page.modulos.logistica.inventarios.create-inventarios', compact('fecha_actual', 'select_monedas', 'select_conceptos', 'selectMonedas', 'parametro', 'select_condicionPago', 'almacenes', 'unidad', 'inventario', 'articulosByInventario', 'almacenesDestino', "movimientos", 'articulos', 'usuario', 'select_categoria', 'select_grupo', 'select_familia'));
        } catch (\Exception $e) {
            return redirect()->route('vista.modulo.inventarios')->with('status', false)->with('message', 'El movimiento no se ha encontrado');
        }
    }

    public function store(Request $request)
    {

        $inventario_request = $request->except('_token');


        //    dd($inventario_request);
        $id = $request->id;
        $copiaRequest = $request->copiar;
        if ($id == 0 || $copiaRequest == 'copiar') {
            $inventario = new PROC_INVENTORIES();
        } else {
            $inventario = PROC_INVENTORIES::where('inventories_id', $id)->first();
        }

        $inventario->inventories_movement = $inventario_request['movimientos'];
        $inventario->inventories_issueDate
            = \Carbon\Carbon::now();
        $inventario->inventories_concept = $inventario_request['concepto'];
        $inventario->inventories_money = $inventario_request['nameMoneda'];
        $inventario->inventories_typeChange = $inventario_request['nameTipoCambio'];
        $inventario->inventories_reference = $inventario_request['proveedorReferencia'];
        $inventario->inventories_company = session('company')->companies_key;
        $inventario->inventories_depot = $inventario_request['almacenKey'];
        $inventario->inventories_depotType = $inventario_request['almacenTipoKey'];
        $inventario->inventories_depotDestiny = $inventario_request['almacenDestinoKey'];
        $inventario->inventories_depotDestinyType = $inventario_request['almacenTipoDestinoKey'];
        $inventario->inventories_user = Auth::user()->username;
        $inventario->inventories_status = $this->estatus[0];
        $inventario->inventories_total = $inventario_request['totalCompleto'];
        $inventario->inventories_lines = $inventario_request['cantidadArticulos'];

        if ($inventario_request['movimientos'] == 'Entrada por Traspaso') {
            //buscamos la sucursal del almacen de origen
            $sucursalOrigen = CAT_DEPOTS::where('depots_key', $inventario_request['almacenDestinoKey'])->first();
            $inventario->inventories_branchOffice = $sucursalOrigen->depots_branchlId;
        } else {
            $inventario->inventories_branchOffice = session('sucursal')->branchOffices_key;
        }

        if ($inventario_request['folio'] != null) {
            if ($copiaRequest == 'copiar') {
                $inventario->inventories_originType = null;
                $inventario->inventories_origin = null;
                $inventario->inventories_originID = null;
            } else {
                $inventario->inventories_originType = 'Inv';
                $inventario->inventories_origin = 'Tránsito';
                $inventario->inventories_originID = $inventario_request['folio'];
            }
        } else {
            if ($copiaRequest == 'copiar') {
                $inventario->inventories_originType = null;
                $inventario->inventories_origin = null;
                $inventario->inventories_originID = null;
            } else {
                if ($inventario->inventories_originID ===  Null) {
                    $inventario->inventories_originType = 'Usuario';
                    $inventario->inventories_origin = Auth::user()->username;
                    $inventario->inventories_originID = null;
                }
            }
        }

        $inventario->created_at = Carbon::now()->format('Y-m-d H:i:s');
        $inventario->updated_at = Carbon::now()->format('Y-m-d H:i:s');


        //insertar articulos en la tabla de detalle de inventario
        $articulos = $inventario_request['dataArticulosJson'];
        $articulos = json_decode($articulos, true);
        $claveArt = array_keys($articulos);


        // $articulos2 = $inventario_request['dataArticulosJson2'];
        // $articulos2 = json_decode($articulos2, true);
        // $claveArt2 = array_keys($articulos2);


        // dd($articulos, $claveArt, $articulos2, $claveArt2);

        //Eliminamos los articulos que no sean necesarios        
        $articulosDelete = json_decode($inventario_request['dataArticulosDelete'], true);


        if ($articulosDelete  != null) {
            foreach ($articulosDelete as $articulo) {
                $detalleCompra = PROC_INVENTORIES_DETAILS::where('inventoryDetails_id', $articulo)->first();
                $detalleCompra->delete();
            }
        }



        try {
            if ($id == 0) {
                $isCreate =  $inventario->save();
                $lastId = $inventario::latest('inventories_id')->first()->inventories_id;
            } else {
                $isCreate =  $inventario->update();
                $lastId = $inventario->inventories_id;
            }


            if ($articulos !== null) {
                //Creamos un arreglo donde se almacenara las seriesAsignadas
                $asignacionSeriesB['series'] = [];
                $asignacionIdsSerieB['idSeries'] = [];
                $asignacionDeleteSeries['seriesD'] = [];
                $asignacionDeleteIdSeries['idSeriesD'] = [];

                foreach ($claveArt as $keyItemArt => $articulo) {
                    if (isset($articulos[$articulo]['id'])) {
                        $detalleInventario = PROC_INVENTORIES_DETAILS::where('inventoryDetails_id', '=', $articulos[$articulo]['id'])->first();
                    } else {
                        $detalleInventario = new PROC_INVENTORIES_DETAILS();
                    }
                    $detalleInventario->inventoryDetails_inventoryID = $lastId;
                    $articuloClave = explode('-', $articulo);
                    $detalleInventario->inventoryDetails_article = $articuloClave[0];
                    $detalleInventario->inventoryDetails_type = $articulos[$articulo]['tipoArticulo'];
                    $detalleInventario->inventoryDetails_descript = $articulos[$articulo]['desp'];
                    $detalleInventario->inventoryDetails_quantity = str_replace(['$', ','], '', $articulos[$articulo]['cantidad']);
                    
                    $detalleInventario->inventoryDetails_unitCost = str_replace(['$', ','], '', $articulos[$articulo]['c_unitario']);
                    

                    $unidadDiv = explode('-', $articulos[$articulo]['unidad']);
                    $detalleInventario->inventoryDetails_unit = $unidadDiv[0];
                    $detalleInventario->inventoryDetails_factor = $unidadDiv[1];

                    $detalleInventario->inventoryDetails_inventoryAmount = str_replace(['$', ','], '', $articulos[$articulo]['c_Inventario']);
                    $detalleInventario->inventoryDetails_amount = str_replace(['$', ','], '', $articulos[$articulo]['importe_total']);
                    $detalleInventario->inventoryDetails_total = str_replace(['$', ','], '', $articulos[$articulo]['importe_total']);

                    if ($inventario_request['folio'] != null || isset($articulos[$articulo]['aplicaIncre'])) {

                        if ($copiaRequest == 'copiar') {
                            $detalleInventario->inventoryDetails_apply = null;
                            $detalleInventario->inventoryDetails_applyIncrement = null;
                        } else {
                            $detalleInventario->inventoryDetails_apply = 'Tránsito';
                            if ($inventario_request['folio'] != null) {
                                $detalleInventario->inventoryDetails_applyIncrement = $inventario_request['folio'];
                            }

                            if (isset($articulos[$articulo]['aplicaIncre'])) {
                                $detalleInventario->inventoryDetails_applyIncrement = $articulos[$articulo]['aplicaIncre'];
                            }
                        }
                    } else {
                        $detalleInventario->inventoryDetails_apply = null;
                        $detalleInventario->inventoryDetails_applyIncrement = null;
                    }

                    $detalleInventario->inventoryDetails_branchOffice = session('sucursal')->branchOffices_key;
                    $detalleInventario->inventoryDetails_depot = $inventario_request['almacenKey'];
                    if ($inventario_request['movimientos'] == 'Entrada por Traspaso') {
                        $detalleInventario->inventoryDetails_outstandingAmount = $articulos[$articulo]['cantidad'];
                    } else {
                        $detalleInventario->inventoryDetails_outstandingAmount = null;
                    }

                    if ($detalleInventario->inventoryDetails_referenceArticles === null) {
                        $detalleInventario->inventoryDetails_referenceArticles = isset($articulos[$articulo]['referenceArticle']) ? $articulos[$articulo]['referenceArticle'] : null; //Recuperamos el id del articulo de referencia
                    }

                    $detalleInventario->save();

                    if ($copiaRequest != 'copiar') {
                        if ($articulos[$articulo]['tipoArticulo'] == "Serie" && $inventario->inventories_movement == "Ajuste de Inventario") {
                            //Agregamos los articulos de serie a la tabla PROC_LOT_SERIES_MOV;
                            $articuloFila = $articuloClave[0] . '-' . $keyItemArt;
                            $series = [];
                            $idSeries = [];
                            $seriesDelete = [];
                            $idSeriesDelete = [];
                            if (isset($articulos[$articulo]['asignacionSerie'])) {
                                foreach ($articulos[$articulo]['asignacionSerie'] as $key => $value) {
                                    if (isset($articulos[$articulo]['asignacionIdsSerie'])) {
                                        if ($key <=  (count($articulos[$articulo]['asignacionIdsSerie']) - 1)) {
                                            $proc_lot_series_mov = PROC_LOT_SERIES_MOV2::where('lotSeriesMov2_companieKey', '=', $inventario->inventories_company)->where('lotSeriesMov2_branchKey', '=', $inventario->inventories_branchOffice)->where('lotSeriesMov2_inventoryID', "=", $lastId)->where('lotSeriesMov2_article', "=", $articuloClave[0])->where('lotSeriesMov2_id', "=", $articulos[$articulo]['asignacionIdsSerie'][$key])->first();
                                        } else {
                                            $proc_lot_series_mov = new PROC_LOT_SERIES_MOV2();
                                            $proc_lot_series_mov->lotSeriesMov2_articleID =  PROC_INVENTORIES_DETAILS::latest('inventoryDetails_id')->first()->inventoryDetails_id;
                                        }
                                    } else {
                                        $proc_lot_series_mov = new PROC_LOT_SERIES_MOV2();
                                        $proc_lot_series_mov->lotSeriesMov2_articleID =  PROC_INVENTORIES_DETAILS::latest('inventoryDetails_id')->first()->inventoryDetails_id;
                                    }



                                    $proc_lot_series_mov->lotSeriesMov2_companieKey =  $inventario->inventories_company;
                                    $proc_lot_series_mov->lotSeriesMov2_branchKey = $inventario->inventories_branchOffice;
                                    $proc_lot_series_mov->lotSeriesMov2_module = 'INV';
                                    $proc_lot_series_mov->lotSeriesMov2_inventoryID = $lastId;
                                    $proc_lot_series_mov->lotSeriesMov2_article = $articuloClave[0];
                                    $proc_lot_series_mov->lotSeriesMov2_lotSerie = $value;
                                    $proc_lot_series_mov->lotSeriesMov2_quantity = 1;


                                    $proc_lot_series_mov->save();

                                    //Obtenemos el ultimo id o el id actualizado de la serie
                                    $lastIdSeries = isset($proc_lot_series_mov->lotSeriesMov2_id) ? $proc_lot_series_mov->lotSeriesMov2_id : PROC_LOT_SERIES_MOV2::latest('lotSeriesMov2_id')->first();

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

                            if (isset($articulos[$articulo]['eliminarSerie'])) {
                                foreach ($articulos[$articulo]['eliminarSerie'] as $key => $value) {
                                    if (isset($articulos[$articulo]['eliminarIdsSerie'])) {
                                        if ($key <=  (count($articulos[$articulo]['eliminarIdsSerie']) - 1)) {
                                            if ($articulos[$articulo]['eliminarIdsSerie'][$key] != 'undefined') {
                                                $proc_del_series_mov = PROC_DEL_SERIES_MOV::where('delSeriesMov_companieKey', '=', $inventario->inventories_company)->where('delSeriesMov_branchKey', '=', $inventario->inventories_branchOffice)->where('delSeriesMov_inventoryID', "=", $lastId)->where('delSeriesMov_article', "=", $articuloClave[0])->where('delSeriesMov_id', "=", $articulos[$articulo]['eliminarIdsSerie'][$key])->first();
                                            } else {
                                                $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                                $proc_del_series_mov->delSeriesMov_articleID =  PROC_INVENTORIES_DETAILS::latest('inventoryDetails_id')->first()->inventoryDetails_id;
                                            }
                                        } else {
                                            $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                            $proc_del_series_mov->delSeriesMov_articleID =  PROC_INVENTORIES_DETAILS::latest('inventoryDetails_id')->first()->inventoryDetails_id;
                                        }
                                    } else {
                                        $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                        $proc_del_series_mov->delSeriesMov_articleID =  PROC_INVENTORIES_DETAILS::latest('inventoryDetails_id')->first()->inventoryDetails_id;
                                    }



                                    $proc_del_series_mov->delSeriesMov_companieKey =  $inventario->inventories_company;
                                    $proc_del_series_mov->delSeriesMov_branchKey = $inventario->inventories_branchOffice;
                                    $proc_del_series_mov->delSeriesMov_module = 'INV';
                                    $proc_del_series_mov->delSeriesMov_inventoryID = $lastId;
                                    $proc_del_series_mov->delSeriesMov_article = $articuloClave[0];
                                    $proc_del_series_mov->delSeriesMov_lotSerie = $value;
                                    $proc_del_series_mov->delSeriesMov_quantity = 1;
                                    $proc_del_series_mov->delSeriesMov_cancelled = 0;
                                    $proc_del_series_mov->delSeriesMov_affected = 0;

                                    $proc_del_series_mov->save();

                                    //Obtenemos el ultimo id o el id actualizado de la serie
                                    $lastIdSeriesDelete = isset($proc_del_series_mov->delSeriesMov_id) ? $proc_del_series_mov->delSeriesMov_id : PROC_DEL_SERIES_MOV::latest('delSeriesMov_id')->first();

                                    $seriesDelete = [
                                        ...$seriesDelete,
                                        $value,
                                    ];

                                    $idSeriesDelete = [
                                        ...$idSeriesDelete,
                                        $lastIdSeriesDelete
                                    ];
                                }

                                if (!array_key_exists($articuloFila, $asignacionDeleteSeries['seriesD'])) {
                                    $asignacionDeleteSeries['seriesD'][$articuloFila] = $seriesDelete;
                                } else {
                                    array_push($asignacionDeleteSeries['seriesD'][$articuloFila], $seriesDelete);
                                }

                                if (!array_key_exists($articuloFila, $asignacionDeleteIdSeries['idSeriesD'])) {
                                    $asignacionDeleteIdSeries['idSeriesD'][$articuloFila] = $idSeriesDelete;
                                } else {
                                    array_push($asignacionDeleteIdSeries['idSeriesD'][$articuloFila], $idSeriesDelete);
                                }
                            }
                        }

                        if (($articulos[$articulo]['tipoArticulo'] == "Serie" && $inventario->inventories_movement == "Transferencia entre Alm.") || ($articulos[$articulo]['tipoArticulo'] == "Serie" && $inventario->inventories_movement == "Salida por Traspaso") || ($articulos[$articulo]['tipoArticulo'] == "Serie" && $inventario->inventories_movement == "Entrada por Traspaso")) {
                            if (isset($articulos[$articulo]['transferirSerie'])) {
                                $articuloFila = $articuloClave[0] . '-' . $keyItemArt;
                                $series = [];
                                $idSeries = [];
                                $seriesDelete = [];
                                $idSeriesDelete = [];
                                foreach ($articulos[$articulo]['transferirSerie'] as $key => $value) {
                                    if (isset($articulos[$articulo]['transferirIdsSerie'])) {
                                        if ($key <=  (count($articulos[$articulo]['transferirIdsSerie']) - 1)) {
                                            if ($articulos[$articulo]['transferirIdsSerie'][$key] != 'undefined') {
                                                $proc_del_series_mov = PROC_DEL_SERIES_MOV::where('delSeriesMov_companieKey', '=', $inventario->inventories_company)->where('delSeriesMov_branchKey', '=', $inventario->inventories_branchOffice)->where('delSeriesMov_inventoryID', "=", $lastId)->where('delSeriesMov_article', "=", $articuloClave[0])->where('delSeriesMov_id', "=", $articulos[$articulo]['transferirIdsSerie'][$key])->first();

                                                if ($proc_del_series_mov == null) {
                                                    $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                                    $proc_del_series_mov->delSeriesMov_articleID =  PROC_INVENTORIES_DETAILS::latest('inventoryDetails_id')->first()->inventoryDetails_id;
                                                }
                                            } else {
                                                $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                                $proc_del_series_mov->delSeriesMov_articleID =  PROC_INVENTORIES_DETAILS::latest('inventoryDetails_id')->first()->inventoryDetails_id;
                                            }
                                        } else {
                                            $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                            $proc_del_series_mov->delSeriesMov_articleID =  PROC_INVENTORIES_DETAILS::latest('inventoryDetails_id')->first()->inventoryDetails_id;
                                        }
                                    } else {
                                        $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                        $proc_del_series_mov->delSeriesMov_articleID =  PROC_INVENTORIES_DETAILS::latest('inventoryDetails_id')->first()->inventoryDetails_id;
                                    }



                                    $proc_del_series_mov->delSeriesMov_companieKey =  $inventario->inventories_company;
                                    $proc_del_series_mov->delSeriesMov_branchKey = $inventario->inventories_branchOffice;
                                    $proc_del_series_mov->delSeriesMov_module = 'INV';
                                    $proc_del_series_mov->delSeriesMov_inventoryID = $lastId;
                                    $proc_del_series_mov->delSeriesMov_article = $articuloClave[0];
                                    $proc_del_series_mov->delSeriesMov_lotSerie = $value;
                                    $proc_del_series_mov->delSeriesMov_quantity = 1;
                                    $proc_del_series_mov->delSeriesMov_cancelled = 0;
                                    $proc_del_series_mov->delSeriesMov_affected = 0;

                                    $proc_del_series_mov->save();

                                    //Obtenemos el ultimo id o el id actualizado de la serie
                                    $lastIdSeriesDelete = isset($proc_del_series_mov->delSeriesMov_id) ? $proc_del_series_mov->delSeriesMov_id : PROC_DEL_SERIES_MOV::latest('delSeriesMov_id')->first();

                                    $seriesDelete = [
                                        ...$seriesDelete,
                                        $value,
                                    ];

                                    $idSeriesDelete = [
                                        ...$idSeriesDelete,
                                        $lastIdSeriesDelete
                                    ];
                                }

                                if (!array_key_exists($articuloFila, $asignacionDeleteSeries['seriesD'])) {
                                    $asignacionDeleteSeries['seriesD'][$articuloFila] = $seriesDelete;
                                } else {
                                    array_push($asignacionDeleteSeries['seriesD'][$articuloFila], $seriesDelete);
                                }

                                if (!array_key_exists($articuloFila, $asignacionDeleteIdSeries['idSeriesD'])) {
                                    $asignacionDeleteIdSeries['idSeriesD'][$articuloFila] = $idSeriesDelete;
                                } else {
                                    array_push($asignacionDeleteIdSeries['idSeriesD'][$articuloFila], $idSeriesDelete);
                                }
                            }
                        }
                    }
                }
            }

            if ($copiaRequest != 'copiar') {
                if (count($asignacionSeriesB['series']) > 0 || count($asignacionDeleteSeries['seriesD']) > 0) {
                    $contenedor = [];
                    $contenedorNuevasSeries = array_merge($asignacionSeriesB, $asignacionIdsSerieB);
                    $contenedorDeleteSeries = array_merge($asignacionDeleteSeries, $asignacionDeleteIdSeries);

                    if (count($asignacionSeriesB['series']) > 0 && count($asignacionDeleteSeries['seriesD']) > 0) {
                        $contenedor = array_merge($contenedorNuevasSeries, $contenedorDeleteSeries);
                    }

                    if (count($contenedor) > 0) {
                        $jsonDetalle = json_encode($contenedor);
                    } else if (count($contenedorNuevasSeries['series']) > 0) {
                        $jsonDetalle = json_encode($contenedorNuevasSeries);
                    } else if (count($contenedorDeleteSeries['seriesD']) > 0) {
                        $jsonDetalle = json_encode($contenedorDeleteSeries);
                    }

                    if ($lastId != 0) {
                        $inventarios = PROC_INVENTORIES::WHERE('inventories_id', '=', $lastId)->first();
                        $inventarios->inventories_jsonData = $jsonDetalle;
                        $inventarios->save();
                    }
                }
            }

            if ($isCreate) {
                $message = $id == 0 ? 'Movimiento creado correctamente' : 'Movimiento actualizado correctamente';
                $status = true;
            } else {
                $message = $id == 0 ? 'Error al crear el movimiento' : 'Error al actualizar el movimiento';
                $status = false;
            }
        } catch (\Throwable $th) {
            dd($th);
            $message = $id == 0 ? "Por favor, vaya con el administrador de sistemas, no se pudo crear el movimiento" : "Por favor, vaya con el administrador de sistemas, no se pudo actualizar el movimiento";
            return redirect()->route('vista.modulo.inventarios.create-inventario')->with('message', $message)->with('status', false);
        }

        return redirect()->route('vista.modulo.inventarios.create-inventario', $id == 0 ? $lastId : $id)->with('message', $message)->with('status', $status);
    }

    public function afectar(Request $request)
    {

        $inventario_request = $request->except('_token');
        //  dd($inventario_request);

        if ($inventario_request['movimientos'] === "Ajuste de Inventario") {

            $isRepSerie = $this->verificacionArticulosSerie($request);

            if ($isRepSerie) {
                $message = 'No se puede afectar el inventario, existen artículos con series repetidas';
                $status = 500;
                $lastId = false;
                return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
            }

            // $isAlmacen = $this->verificacionAlmacen($request);

            // if ($isAlmacen) {
            //     $message = 'No se puede afectar el inventario, el almacen no es de activos fijos';
            //     $status = 500;
            //     $lastId = false;
            //     return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
            // }
        }

        // if ($inventario_request['movimientos'] === "Transferencia entre Alm." || $inventario_request['movimientos'] === "Salida por Traspaso" || $inventario_request['movimientos'] === "Entrada por Traspaso") {

        //     $isAlmacen = $this->verificacionAlmacenDestino($request);

        //     if ($isAlmacen) {
        //         $message = 'No se puede afectar el inventario, el almacen destino no es de activos fijos';
        //         $status = 500;
        //         $lastId = false;
        //         return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
        //     }
        // }

        if ($inventario_request['movimientos'] == 'Transferencia entre Alm.') {
            //validar que el almacen de destino sea de la misma sucursal
            $almacenDestino = CAT_DEPOTS::find($inventario_request['almacenDestinoKey']);
            if ($almacenDestino->depots_branchlId !== session('sucursal')->branchOffices_key) {
                $message = 'El almacen de destino no pertenece a la sucursal';
                $status = 500;
                $lastId = false;
                return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
            }
        }

        if ($inventario_request['movimientos'] == 'Salida por Traspaso') {
            //validar que el almacen de destino sea de la misma sucursal
            $almacenDestino = CAT_DEPOTS::find($inventario_request['almacenDestinoKey']);

            if ($almacenDestino->depots_branchlId === session('sucursal')->branchOffices_key) {
                $message = 'El almacen de destino pertenece a la sucursal, se sugiere usar el movimiento de transferencia';
                $status = 400;
                $lastId = false;
                return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
            }
        }

        if ($inventario_request['movimientos'] == 'Transferencia entre Alm.' || $inventario_request['movimientos'] == 'Salida por Traspaso') {

            if ($inventario_request['almacenKey'] === $inventario_request['almacenDestinoKey']) {
                $message = 'El almacen de origen y destino no pueden ser el mismo: ' . $request['almacenKey'];
                $status = 400;
                $lastId = false;
                return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
            }


            $articulos = $inventario_request['dataArticulosJson'];
            $articulos = json_decode($articulos, true);
            $claveArt = array_keys($articulos);

            // dd($articulos, $claveArt);
            //validar que el almacen tenga existencia del articulo
            foreach ($claveArt as $key => $articulo) {
                $articuloInd = explode('-', $articulo);
                //validamos que haya stock para el articulo
                $validar = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articuloInd)->where('articlesInv_depot', '=', $request['almacenKey'])->where('articlesInv_branchKey', '=', session('sucursal')->branchOffices_key)->first();

                if ($validar == null) {
                    $message = 'No hay stock para el articulo: ' . $articuloInd[0] . "-" . $articulos[$articulo]['desp'] . ' en el almacen ' . $request['almacenKey'];
                    $status = 400;
                    $lastId = false;
                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                }
                $articuloVenta = (float) $articulos[$articulo]['c_Inventario'];
                $disponible = (float) $validar->articlesInv_inventory;

                if ($articuloVenta > $disponible) {
                    $message = 'No hay stock para el articulo: ' . $articuloInd[0] . "-" . $articulos[$articulo]['desp'] . ' en el almacen ' . $request['almacenKey'];
                    $status = 400;
                    $lastId = false;
                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                }
            }
        }

        if ($inventario_request['movimientos'] == 'Ajuste de Inventario') {
            $articulos = $inventario_request['dataArticulosJson'];
            $articulos = json_decode($articulos, true);
            $claveArt = array_keys($articulos);
            //validar que el almacen tenga existencia del articulo
            foreach ($claveArt as $key => $articulo) {
                $articuloInd = explode('-', $articulo);
                //validamos que haya stock para el articulo

                // $negativo = false;
                // dd($validar);

                $articuloVenta = (float) $articulos[$articulo]['c_Inventario'];
                if ($articuloVenta < 0) {

                    $validar = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articuloInd)->where('articlesInv_depot', '=', $request['almacenKey'])->where('articlesInv_branchKey', '=', session('sucursal')->branchOffices_key)->first();

                    if ($validar == null) {
                        $message = 'No hay stock para el articulo en el almacen: ' . $request['almacenKey'];
                        $status = 400;
                        $lastId = false;
                        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                    }

                    $disponible = (float) $validar->articlesInv_inventory;
                    $articuloVenta2 = $articuloVenta * -1;


                    if ($disponible < $articuloVenta2) {
                        $message = 'No hay stock para el articulo en el almacen: ' . $request['almacenKey'];
                        $status = 400;
                        $lastId = false;

                        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                    }
                }
            }
        }

        $id = $request->id;
        $copiaRequest = $request->copiar;

        //    dd($inventario_request);

        if ($id == 0 || $copiaRequest == 'copiar') {
            $inventario = new PROC_INVENTORIES();
        } else {
            $inventario = PROC_INVENTORIES::where('inventories_id', $id)->first();
        }

        $inventario->inventories_movement = $inventario_request['movimientos'];
        $inventario->inventories_issueDate
            = \Carbon\Carbon::now();
        $inventario->inventories_concept = $inventario_request['concepto'];
        $inventario->inventories_money = $inventario_request['nameMoneda'];
        $inventario->inventories_typeChange = $inventario_request['nameTipoCambio'];
        $inventario->inventories_reference = $inventario_request['proveedorReferencia'];
        $inventario->inventories_company = session('company')->companies_key;
        $inventario->inventories_depot = $inventario_request['almacenKey'];
        $inventario->inventories_depotType = $inventario_request['almacenTipoKey'];
        $inventario->inventories_depotDestiny = $inventario_request['almacenDestinoKey'];
        $inventario->inventories_depotDestinyType = $inventario_request['almacenTipoDestinoKey'];
        $inventario->inventories_user = Auth::user()->username;
        //si el movimiento es Transferencia entre Alm. entonces el total es 0
        if ($inventario_request['movimientos'] == 'Transferencia entre Alm.') {
            $inventario->inventories_total = 0;
        } else {
            $inventario->inventories_total = $inventario_request['totalCompleto'];
        }
        $inventario->inventories_lines = $inventario_request['cantidadArticulos'];
        

        if ($inventario_request['movimientos'] == 'Entrada por Traspaso') {
            //buscamos la sucursal del almacen de origen 
            $sucursalOrigen = CAT_DEPOTS::where('depots_key', $inventario_request['almacenDestinoKey'])->first();
            $inventario->inventories_branchOffice = $sucursalOrigen->depots_branchlId;
        } else {
            $inventario->inventories_branchOffice = session('sucursal')->branchOffices_key;
        }

        switch ($inventario_request['movimientos']) {
            case 'Ajuste de Inventario':
                $inventario->inventories_status = $this->estatus[2]; //AFECTADA CONCLUIDA
                break;
            case 'Transferencia entre Alm.':
                $inventario->inventories_status = $this->estatus[2]; //AFECTADA CONCLUIDA
                break;
                // case 'Ajuste de Inventario':
                //     $inventario->inventories_status = $this->estatus[2]; //AFECTADA CONCLUIDA
                //     break;
            case 'Salida por Traspaso':
                $inventario->inventories_status = $this->estatus[2]; //AFECTADA CONCLUIDA
                break;
            case 'Entrada por Traspaso':
                $inventario->inventories_status = $this->estatus[2]; //AFECTADA CONCLUIDA
                break;
            default:
                # code...
                break;
        }

        $inventario->created_at = Carbon::now()->format('Y-m-d H:i:s');
        $inventario->updated_at = Carbon::now()->format('Y-m-d H:i:s');




        //insertar articulos en la tabla de detalle de inventario
        $articulos = $inventario_request['dataArticulosJson'];
        $articulos = json_decode($articulos, true);
        $claveArt = array_keys($articulos);




        //Eliminamos los articulos que no sean necesarios        
        $articulosDelete = json_decode($inventario_request['dataArticulosDelete'], true);


        if ($articulosDelete  != null) {
            foreach ($articulosDelete as $articulo) {
                $detalleCompra = PROC_INVENTORIES_DETAILS::where('inventoryDetails_id', $articulo)->first();
                $detalleCompra->delete();
            }
        }


        try {
            if ($id == 0) {
                $isCreate =  $inventario->save();
                $lastId = $inventario::latest('inventories_id')->first()->inventories_id;
            } else {
                $isCreate =  $inventario->update();
                $lastId = $inventario->inventories_id;
            }

            // dd($lastId);


            $folioAfectar = PROC_INVENTORIES::where('inventories_id', $lastId)->first();
            // dd($folioAfectar);
            $tipoMovimiento = $folioAfectar->inventories_movement;
            // dd($tipoMovimiento);

            $this->actualizarFolio($tipoMovimiento, $folioAfectar);

            if ($articulos !== null) {
                $asignacionSeriesB['series'] = [];
                $asignacionIdsSerieB['idSeries'] = [];
                $asignacionDeleteSeries['seriesD'] = [];
                $asignacionDeleteIdSeries['idSeriesD'] = [];
                foreach ($claveArt as $keyItemArt => $articulo) {
                    if ($inventario_request['movimientos'] != 'Ajuste de Inventario') {
                        //c_unitario importe_total se ponen en 0
                        $articulos[$articulo]['c_unitario'] = 0;
                        $articulos[$articulo]['importe_total'] = 0;                  
                    }
                    if (isset($articulos[$articulo]['id'])) {
                        $detalleInventario = PROC_INVENTORIES_DETAILS::where('inventoryDetails_id', '=', $articulos[$articulo]['id'])->first();
                    } else {
                        $detalleInventario = new PROC_INVENTORIES_DETAILS();
                    }

                    // dd(str_replace(['$', ','], '', $articulos[$articulo]['importe_total']));
                    $detalleInventario->inventoryDetails_inventoryID = $lastId;
                    $articuloClave = explode('-', $articulo);
                    $detalleInventario->inventoryDetails_article = $articuloClave[0];
                    $detalleInventario->inventoryDetails_type = $articulos[$articulo]['tipoArticulo'];
                    $detalleInventario->inventoryDetails_descript = $articulos[$articulo]['desp'];
                    $detalleInventario->inventoryDetails_quantity = str_replace(['$', ','], '', $articulos[$articulo]['cantidad']);
                    $detalleInventario->inventoryDetails_unitCost = str_replace(['$', ','], '', $articulos[$articulo]['c_unitario']);
                    // dd($articulos[$articulo]['c_unitario']);
                    $unidadDiv = explode('-', $articulos[$articulo]['unidad']);
                    $detalleInventario->inventoryDetails_unit = $unidadDiv[0];
                    $detalleInventario->inventoryDetails_factor = $unidadDiv[1];

                    $detalleInventario->inventoryDetails_inventoryAmount = str_replace(['$', ','], '', $articulos[$articulo]['c_Inventario']);
                    $detalleInventario->inventoryDetails_amount = str_replace(['$', ','], '', $articulos[$articulo]['importe_total']);
                    $detalleInventario->inventoryDetails_total = str_replace(['$', ','], '', $articulos[$articulo]['importe_total']);

                    if ($inventario_request['folio'] != null) {

                        if ($copiaRequest == 'copiar') {
                            $detalleInventario->inventoryDetails_apply = null;
                            $detalleInventario->inventoryDetails_applyIncrement = null;
                        } else {
                            $detalleInventario->inventoryDetails_apply = 'Tránsito';
                            $detalleInventario->inventoryDetails_applyIncrement = $inventario_request['folio'];
                        }
                    } else {
                        if ($inventario_request['movimientos'] != 'Entrada por Traspaso') {
                            $detalleInventario->inventoryDetails_apply = null;
                            $detalleInventario->inventoryDetails_applyIncrement = null;
                        }
                    }

                    if ($inventario_request['movimientos'] == 'Entrada por Traspaso') {
                        //buscamos la sucursal del almacen de origen
                        $sucursalOrigen = CAT_DEPOTS::where('depots_key', $inventario_request['almacenDestinoKey'])->first();
                        $detalleInventario->inventoryDetails_branchOffice = $sucursalOrigen->depots_branchlId;
                    } else {
                        $detalleInventario->inventoryDetails_branchOffice = session('sucursal')->branchOffices_key;
                    }



                    $detalleInventario->inventoryDetails_depot = $inventario_request['almacenKey'];
                    if ($inventario_request['movimientos'] == 'Entrada por Traspaso') {
                        $detalleInventario->inventoryDetails_outstandingAmount = $articulos[$articulo]['cantidad'];
                    } else {
                        $detalleInventario->inventoryDetails_outstandingAmount = null;
                    }
                    // $detalleInventario->inventoryDetails_outstandingAmount = null;
                    // $detalleInventario->inventoryDetails_canceledAmount = null;
                    // $detalleInventario->inventoryDetails_referenceArticles = isset($articulos[$articulo]['referenceArticle']) ? $articulos[$articulo]['referenceArticle'] : null; //Recuperamos el id del articulo de referencia
                    $detalleInventario->save();

                    $lastIdDetalle = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $lastId)->where('inventoryDetails_article', '=', $articuloClave[0])->select('inventoryDetails_id')->first();


                    if ($articulos[$articulo]['tipoArticulo'] == "Serie" && $inventario_request['movimientos'] == "Ajuste de Inventario") {
                        //Agregamos los articulos de serie a la tabla PROC_LOT_SERIES_MOV;
                        if (isset($articulos[$articulo]['asignacionSerie'])) {
                            foreach ($articulos[$articulo]['asignacionSerie'] as $key => $value) {
                                if (isset($articulos[$articulo]['asignacionIdsSerie'])) {
                                    if ($key <=  (count($articulos[$articulo]['asignacionIdsSerie']) - 1)) {
                                        $proc_lot_series_mov = PROC_LOT_SERIES_MOV2::where('lotSeriesMov2_companieKey', '=', $inventario->inventories_company)->where('lotSeriesMov2_branchKey', '=', $inventario->inventories_branchOffice)->where('lotSeriesMov2_inventoryID', "=", $lastId)->where('lotSeriesMov2_article', "=", $articuloClave[0])->where('lotSeriesMov2_id', "=", $articulos[$articulo]['asignacionIdsSerie'][$key])->first();
                                    } else {
                                        $proc_lot_series_mov = new PROC_LOT_SERIES_MOV2();
                                        $proc_lot_series_mov->lotSeriesMov2_articleID =  $lastIdDetalle->inventoryDetails_id;
                                    }
                                } else {
                                    $proc_lot_series_mov = new PROC_LOT_SERIES_MOV2();
                                    $proc_lot_series_mov->lotSeriesMov2_articleID =  $lastIdDetalle->inventoryDetails_id;
                                }



                                $proc_lot_series_mov->lotSeriesMov2_companieKey =  $inventario->inventories_company;
                                $proc_lot_series_mov->lotSeriesMov2_branchKey = $inventario->inventories_branchOffice;
                                $proc_lot_series_mov->lotSeriesMov2_module = 'INV';
                                $proc_lot_series_mov->lotSeriesMov2_inventoryID = $lastId;
                                $proc_lot_series_mov->lotSeriesMov2_article = $articuloClave[0];
                                $proc_lot_series_mov->lotSeriesMov2_lotSerie = $value;
                                $proc_lot_series_mov->lotSeriesMov2_quantity = 1;


                                $proc_lot_series_mov->save();

                                $proc_lot_series = new PROC_LOT_SERIES();
                                $proc_lot_series->lotSeries_companieKey =  $inventario->inventories_company;
                                $proc_lot_series->lotSeries_branchKey = $inventario->inventories_branchOffice;
                                $proc_lot_series->lotSeries_article = $articuloClave[0];
                                $proc_lot_series->lotSeries_lotSerie = $value;
                                $proc_lot_series->lotSeries_depot = $inventario->inventories_depot;;
                                $proc_lot_series->lotSeries_existence = 1;
                                $proc_lot_series->lotSeries_delete = 0;
                                $proc_lot_series->save();
                            }
                        }

                        if (isset($articulos[$articulo]['eliminarSerie'])) {
                            foreach ($articulos[$articulo]['eliminarSerie'] as $key => $value) {
                                if (isset($articulos[$articulo]['eliminarIdsSerie'])) {
                                    if ($key <=  (count($articulos[$articulo]['eliminarIdsSerie']) - 1)) {
                                        if ($articulos[$articulo]['eliminarIdsSerie'][$key] != 'undefined') {
                                            $proc_del_series_mov = PROC_DEL_SERIES_MOV::where('delSeriesMov_companieKey', '=', $inventario->inventories_company)->where('delSeriesMov_branchKey', '=', $inventario->inventories_branchOffice)->where('delSeriesMov_inventoryID', "=", $lastId)->where('delSeriesMov_article', "=", $articuloClave[0])->where('delSeriesMov_id', "=", $articulos[$articulo]['eliminarIdsSerie'][$key])->first();
                                        } else {
                                            $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                            $proc_lot_series_mov->delSeriesMov_articleID =  $lastIdDetalle->inventoryDetails_id;
                                        }
                                    } else {
                                        $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                        // dd($lastIdDetalle->inventoryDetails_id);
                                        $proc_del_series_mov->delSeriesMov_articleID =  $lastIdDetalle->inventoryDetails_id;
                                    }
                                } else {
                                    $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                    $proc_lot_series_mov->delSeriesMov_articleID =  $lastIdDetalle->inventoryDetails_id;
                                }



                                $proc_del_series_mov->delSeriesMov_companieKey =  $inventario->inventories_company;
                                $proc_del_series_mov->delSeriesMov_branchKey = $inventario->inventories_branchOffice;
                                $proc_del_series_mov->delSeriesMov_module = 'INV';
                                $proc_del_series_mov->delSeriesMov_inventoryID = $lastId;
                                $proc_del_series_mov->delSeriesMov_article = $articuloClave[0];
                                $proc_del_series_mov->delSeriesMov_lotSerie = $value;
                                $proc_del_series_mov->delSeriesMov_quantity = 1;
                                $proc_del_series_mov->delSeriesMov_cancelled = 0;
                                $proc_del_series_mov->delSeriesMov_affected = 1;



                                $proc_del_series_mov->save();

                                $proc_lot_series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', $inventario->inventories_company)->where('lotSeries_branchKey', '=', $inventario->inventories_branchOffice)->where('lotSeries_article', "=", $articuloClave[0])->where('lotSeries_lotSerie', "=", $value)->first();

                                if ($proc_lot_series !== null) {

                                    $proc_lot_series->lotSeries_delete = 1;
                                    $proc_lot_series->update();
                                }
                            }
                        }
                    } else if ($articulos[$articulo]['tipoArticulo'] == "Serie" && $inventario_request['movimientos'] == "Transferencia entre Alm.") {

                        if (isset($articulos[$articulo]['transferirSerie'])) {
                            foreach ($articulos[$articulo]['transferirSerie'] as $key => $value) {
                                if (isset($articulos[$articulo]['transferirIdsSerie'])) {
                                    if ($key <=  (count($articulos[$articulo]['transferirIdsSerie']) - 1)) {
                                        if ($articulos[$articulo]['transferirIdsSerie'][$key] != 'undefined') {
                                            $proc_del_series_mov = PROC_DEL_SERIES_MOV::where('delSeriesMov_companieKey', '=', $inventario->inventories_company)->where('delSeriesMov_branchKey', '=', $inventario->inventories_branchOffice)->where('delSeriesMov_inventoryID', "=", $lastId)->where('delSeriesMov_article', "=", $articuloClave[0])->where('delSeriesMov_id', "=", $articulos[$articulo]['transferirIdsSerie'][$key])->first();
                                        } else {
                                            $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                            $proc_del_series_mov->delSeriesMov_articleID =  $lastIdDetalle->inventoryDetails_id;
                                        }
                                    } else {
                                        $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                        $proc_del_series_mov->delSeriesMov_articleID =  $lastIdDetalle->inventoryDetails_id;
                                    }
                                } else {
                                    $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                    $proc_del_series_mov->delSeriesMov_articleID =  $lastIdDetalle->inventoryDetails_id;
                                }



                                $proc_del_series_mov->delSeriesMov_companieKey =  $inventario->inventories_company;
                                $proc_del_series_mov->delSeriesMov_branchKey = $inventario->inventories_branchOffice;
                                $proc_del_series_mov->delSeriesMov_module = 'INV';
                                $proc_del_series_mov->delSeriesMov_inventoryID = $lastId;
                                $proc_del_series_mov->delSeriesMov_article = $articuloClave[0];
                                $proc_del_series_mov->delSeriesMov_lotSerie = $value;
                                $proc_del_series_mov->delSeriesMov_quantity = 1;
                                $proc_del_series_mov->delSeriesMov_cancelled = 0;
                                $proc_del_series_mov->delSeriesMov_affected = 1;



                                $proc_del_series_mov->save();

                                $proc_lot_series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', $inventario->inventories_company)->where('lotSeries_branchKey', '=', $inventario->inventories_branchOffice)->where('lotSeries_article', "=", $articuloClave[0])->where('lotSeries_lotSerie', "=", $value)->first();

                                if ($proc_lot_series !== null) {

                                    $proc_lot_series->lotSeries_depot = $inventario->inventories_depotDestiny;
                                    $proc_lot_series->update();
                                }
                            }
                        }
                    } else if ($articulos[$articulo]['tipoArticulo'] == "Serie" && $inventario_request['movimientos'] == "Salida por Traspaso") {

                        if (isset($articulos[$articulo]['transferirSerie'])) {
                            $articuloFila = $articuloClave[0] . '-' . $keyItemArt;
                            $series = [];
                            $idSeries = [];
                            $seriesDelete = [];
                            $idSeriesDelete = [];
                            foreach ($articulos[$articulo]['transferirSerie'] as $key => $value) {
                                if (isset($articulos[$articulo]['transferirIdsSerie'])) {
                                    if ($key <=  (count($articulos[$articulo]['transferirIdsSerie']) - 1)) {
                                        if ($articulos[$articulo]['transferirIdsSerie'][$key] != 'undefined') {
                                            $proc_del_series_mov = PROC_DEL_SERIES_MOV::where('delSeriesMov_companieKey', '=', $inventario->inventories_company)->where('delSeriesMov_branchKey', '=', $inventario->inventories_branchOffice)->where('delSeriesMov_inventoryID', "=", $lastId)->where('delSeriesMov_article', "=", $articuloClave[0])->where('delSeriesMov_id', "=", $articulos[$articulo]['transferirIdsSerie'][$key])->first();
                                        } else {
                                            $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                            $proc_del_series_mov->delSeriesMov_articleID =  $lastIdDetalle->inventoryDetails_id;
                                        }
                                    } else {
                                        $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                        $proc_del_series_mov->delSeriesMov_articleID =  $lastIdDetalle->inventoryDetails_id;
                                    }
                                } else {
                                    $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                    $proc_del_series_mov->delSeriesMov_articleID =  $lastIdDetalle->inventoryDetails_id;
                                }



                                $proc_del_series_mov->delSeriesMov_companieKey =  $inventario->inventories_company;
                                $proc_del_series_mov->delSeriesMov_branchKey = $inventario->inventories_branchOffice;
                                $proc_del_series_mov->delSeriesMov_module = 'INV';
                                $proc_del_series_mov->delSeriesMov_inventoryID = $lastId;
                                $proc_del_series_mov->delSeriesMov_article = $articuloClave[0];
                                $proc_del_series_mov->delSeriesMov_lotSerie = $value;
                                $proc_del_series_mov->delSeriesMov_quantity = 1;
                                $proc_del_series_mov->delSeriesMov_cancelled = 0;
                                $proc_del_series_mov->delSeriesMov_affected = 1;


                                $proc_del_series_mov->save();

                                //Obtenemos el ultimo id o el id actualizado de la serie
                                $lastIdSeriesDelete = isset($proc_del_series_mov->delSeriesMov_id) ? $proc_del_series_mov->delSeriesMov_id : PROC_DEL_SERIES_MOV::latest('delSeriesMov_id')->first();

                                $seriesDelete = [
                                    ...$seriesDelete,
                                    $value,
                                ];

                                $idSeriesDelete = [
                                    ...$idSeriesDelete,
                                    $lastIdSeriesDelete
                                ];


                                $sucursalOrigen = CAT_DEPOTS::where('depots_key', $inventario_request['almacenDestinoKey'])->first();

                                $proc_lot_series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', $inventario->inventories_company)->where('lotSeries_article', "=", $articuloClave[0])->where('lotSeries_lotSerie', "=", $value)->first();

                                if ($proc_lot_series !== null) {
                                    $proc_lot_series->lotSeries_branchKey =  $sucursalOrigen->depots_branchlId;
                                    $proc_lot_series->lotSeries_depot = $inventario->inventories_depotDestiny;
                                    $proc_lot_series->update();
                                }
                            }

                            if (!array_key_exists($articuloFila, $asignacionDeleteSeries['seriesD'])) {
                                $asignacionDeleteSeries['seriesD'][$articuloFila] = $seriesDelete;
                            } else {
                                array_push($asignacionDeleteSeries['seriesD'][$articuloFila], $seriesDelete);
                            }

                            if (!array_key_exists($articuloFila, $asignacionDeleteIdSeries['idSeriesD'])) {
                                $asignacionDeleteIdSeries['idSeriesD'][$articuloFila] = $idSeriesDelete;
                            } else {
                                array_push($asignacionDeleteIdSeries['idSeriesD'][$articuloFila], $idSeriesDelete);
                            }
                        }
                    } else if ($articulos[$articulo]['tipoArticulo'] == "Serie" && $inventario_request['movimientos'] == "Entrada por Traspaso") {

                        if (isset($articulos[$articulo]['transferirSerie'])) {
                            foreach ($articulos[$articulo]['transferirSerie'] as $key => $value) {
                                if (isset($articulos[$articulo]['transferirIdsSerie'])) {
                                    if ($key <=  (count($articulos[$articulo]['transferirIdsSerie']) - 1)) {
                                        $proc_del_series_mov = PROC_DEL_SERIES_MOV::where('delSeriesMov_companieKey', '=', $inventario->inventories_company)->where('delSeriesMov_branchKey', '=', $inventario->inventories_branchOffice)->where('delSeriesMov_inventoryID', "=", $lastId)->where('delSeriesMov_article', "=", $articuloClave[0])->where('delSeriesMov_id', "=", $articulos[$articulo]['transferirIdsSerie'][$key])->first();
                                    } else {
                                        $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                        $proc_del_series_mov->delSeriesMov_articleID =  $lastIdDetalle->inventoryDetails_id;
                                    }
                                } else {
                                    $proc_del_series_mov = new PROC_DEL_SERIES_MOV();
                                    $proc_del_series_mov->delSeriesMov_articleID =  $lastIdDetalle->inventoryDetails_id;
                                }



                                $proc_del_series_mov->delSeriesMov_companieKey =  $inventario->inventories_company;
                                $proc_del_series_mov->delSeriesMov_branchKey = $inventario->inventories_branchOffice;
                                $proc_del_series_mov->delSeriesMov_module = 'INV';
                                $proc_del_series_mov->delSeriesMov_inventoryID = $lastId;
                                $proc_del_series_mov->delSeriesMov_article = $articuloClave[0];
                                $proc_del_series_mov->delSeriesMov_lotSerie = $value;
                                $proc_del_series_mov->delSeriesMov_quantity = 1;
                                $proc_del_series_mov->delSeriesMov_cancelled = 0;
                                $proc_del_series_mov->delSeriesMov_affected = 1;


                                $proc_del_series_mov->save();

                                $proc_lot_series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', $inventario->inventories_company)->where('lotSeries_branchKey', '=', $inventario->inventories_branchOffice)->where('lotSeries_article', "=", $articuloClave[0])->where('lotSeries_lotSerie', "=", $value)->first();

                                if ($proc_lot_series !== null) {

                                    $proc_lot_series->lotSeries_depot = $inventario->inventories_depotDestiny;
                                    $proc_lot_series->update();
                                }
                            }
                        }
                    }
                }
            }

            if (count($asignacionSeriesB['series']) > 0 || count($asignacionDeleteSeries['seriesD']) > 0) {
                $contenedor = [];
                $contenedorNuevasSeries = array_merge($asignacionSeriesB, $asignacionIdsSerieB);
                $contenedorDeleteSeries = array_merge($asignacionDeleteSeries, $asignacionDeleteIdSeries);

                if (count($asignacionSeriesB['series']) > 0 && count($asignacionDeleteSeries['seriesD']) > 0) {
                    $contenedor = array_merge($contenedorNuevasSeries, $contenedorDeleteSeries);
                }

                if (count($contenedor) > 0) {
                    $jsonDetalle = json_encode($contenedor);
                } else if (count($contenedorNuevasSeries['series']) > 0) {
                    $jsonDetalle = json_encode($contenedorNuevasSeries);
                } else if (count($contenedorDeleteSeries['seriesD']) > 0) {
                    $jsonDetalle = json_encode($contenedorDeleteSeries);
                }

                if ($folioAfectar->inventories_id != 0) {
                    $inventarios = PROC_INVENTORIES::WHERE('inventories_id', '=', $folioAfectar->inventories_id)->first();
                    $inventarios->inventories_jsonData = $jsonDetalle;
                    $inventarios->save();
                }
            }


            //agregamos unidades a aux u
            $this->auxiliarU($folioAfectar->inventories_id);
            //agregamos a almacen
            $this->agregarAlmacen($folioAfectar->inventories_id);
            //afectamos el costo promedio
            $this->costoPromedio($folioAfectar->inventories_id);

            //agregamos transito a sucursal
            $generado = $this->generarTransito($folioAfectar->inventories_id);

            //agregar mov flujo
            $this->agregarMov($folioAfectar->inventories_id);

            //concluimos los origenes de los movimientos
            $this->concluirOrigines($folioAfectar->inventories_id);
            //buscamos transito generado
            $transitoGenerado = PROC_INVENTORIES::where('inventories_id', '=', $generado)->first();

            if ($transitoGenerado !== null) {
                //Actualizamos el transito la columna jsonData


                $message = ' ';
                $status = 200;
                return response()->json(['mensaje' => $message, 'estatus' => $status, 'transito' => $transitoGenerado, 'id' => $lastId], $status);
            }

            if ($isCreate) {
                $status = 200;
                if ($inventario_request['movimientos'] != 'Ajuste de Inventario') {
                    $status = 200;
                    $message = 'La ' . $inventario_request['movimientos'] . ' se ha creado correctamente';
                } else {
                    $status = 200;
                    $message = 'El ' . $inventario_request['movimientos'] . ' se ha creado correctamente';
                }
            } else {
                $status = 500;
                $message = 'Error al afectar el movimiento';
            }
        } catch (\Throwable $th) {
            $status = 500;
            $lastId = 0;
            $message = $th->getMessage() . '-' . $th->getLine();
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
    }


    public function auxiliarU($folio)
    {
        $folioAfectar = PROC_INVENTORIES::where('inventories_id', '=', $folio)->first();

        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Ajuste de Inventario') {

            //buscamos sus articulos
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->where('inventoryDetails_branchOffice', '=', $folioAfectar->inventories_branchOffice)->get();


            foreach ($articulos as $articulo) {
                //agregar datos a aux
                // dd($articulo);
                $auxiliarU = new PROC_ASSISTANT_UNITS();
                $auxiliarU->assistantUnit_companieKey = $folioAfectar->inventories_company;
                $auxiliarU->assistantUnit_branchKey = $folioAfectar->inventories_branchOffice;
                $auxiliarU->assistantUnit_branch = 'Inv';
                $auxiliarU->assistantUnit_movement = $folioAfectar->inventories_movement;
                $auxiliarU->assistantUnit_movementID = $folioAfectar->inventories_movementID;
                $auxiliarU->assistantUnit_module = 'Inv';
                $auxiliarU->assistantUnit_moduleID = $articulo->inventoryDetails_inventoryID;
                $auxiliarU->assistantUnit_money = $folioAfectar->inventories_money;
                $auxiliarU->assistantUnit_typeChange = $folioAfectar->inventories_typeChange;
                $auxiliarU->assistantUnit_group = $articulo->inventoryDetails_depot;
                $auxiliarU->assistantUnit_account = $articulo->inventoryDetails_article;
                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;
                $auxiliarU->assistantUnit_year = $year;
                $auxiliarU->assistantUnit_period = $period;

                if ($articulo->inventoryDetails_quantity > 0) {
                    $auxiliarU->assistantUnit_charge = $articulo->inventoryDetails_amount;
                    $auxiliarU->assistantUnit_payment = null;
                    $auxiliarU->assistantUnit_chargeUnit = abs((float)$articulo->inventoryDetails_inventoryAmount);
                    $auxiliarU->assistantUnit_paymentUnit = null;
                } else {
                    $auxiliarU->assistantUnit_charge = null;
                    $auxiliarU->assistantUnit_payment = $articulo->inventoryDetails_amount;
                    $auxiliarU->assistantUnit_chargeUnit = null;
                    $auxiliarU->assistantUnit_paymentUnit = abs((float)$articulo->inventoryDetails_inventoryAmount);
                }



                $auxiliarU->assistantUnit_apply = $articulo->inventoryDetails_apply;
                $auxiliarU->assistantUnit_applyID =  $articulo->inventoryDetails_applyIncrement;
                $auxiliarU->assistantUnit_canceled = 0;
                $auxiliarU->save();
            }
        }

        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Transferencia entre Alm.') {

            //buscamos sus articulos
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->where('inventoryDetails_branchOffice', '=', $folioAfectar->inventories_branchOffice)->get();


            foreach ($articulos as $articulo) {
                //agregar datos a aux
                $auxiliarU = new PROC_ASSISTANT_UNITS();
                $auxiliarU->assistantUnit_companieKey = $folioAfectar->inventories_company;
                $auxiliarU->assistantUnit_branchKey = $folioAfectar->inventories_branchOffice;
                $auxiliarU->assistantUnit_branch = 'Inv';
                $auxiliarU->assistantUnit_movement = $folioAfectar->inventories_movement;
                $auxiliarU->assistantUnit_movementID = $folioAfectar->inventories_movementID;
                $auxiliarU->assistantUnit_module = 'Inv';
                $auxiliarU->assistantUnit_moduleID = $articulo->inventoryDetails_inventoryID;
                $auxiliarU->assistantUnit_money = $folioAfectar->inventories_money;
                $auxiliarU->assistantUnit_typeChange = $folioAfectar->inventories_typeChange;
                $auxiliarU->assistantUnit_group =  $folioAfectar->inventories_depot;
                $auxiliarU->assistantUnit_account = $articulo->inventoryDetails_article;
                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;
                $auxiliarU->assistantUnit_year = $year;
                $auxiliarU->assistantUnit_period = $period;
                $auxiliarU->assistantUnit_charge = null;
                $auxiliarU->assistantUnit_payment = $articulo->inventoryDetails_amount;
                $auxiliarU->assistantUnit_chargeUnit = null;
                $auxiliarU->assistantUnit_paymentUnit = (float)$articulo->inventoryDetails_inventoryAmount;
                $auxiliarU->assistantUnit_apply = $articulo->inventoryDetails_apply;
                $auxiliarU->assistantUnit_applyID =  $articulo->inventoryDetails_applyIncrement;
                $auxiliarU->assistantUnit_canceled = 0;
                $auxiliarU->save();

                $auxiliarU = new PROC_ASSISTANT_UNITS();
                $auxiliarU->assistantUnit_companieKey = $folioAfectar->inventories_company;
                $auxiliarU->assistantUnit_branchKey = $folioAfectar->inventories_branchOffice;
                $auxiliarU->assistantUnit_branch = 'Inv';
                $auxiliarU->assistantUnit_movement = $folioAfectar->inventories_movement;
                $auxiliarU->assistantUnit_movementID = $folioAfectar->inventories_movementID;
                $auxiliarU->assistantUnit_module = 'Inv';
                $auxiliarU->assistantUnit_moduleID = $articulo->inventoryDetails_inventoryID;
                $auxiliarU->assistantUnit_money = $folioAfectar->inventories_money;
                $auxiliarU->assistantUnit_typeChange = $folioAfectar->inventories_typeChange;
                $auxiliarU->assistantUnit_group = $folioAfectar->inventories_depotDestiny;
                $auxiliarU->assistantUnit_account = $articulo->inventoryDetails_article;
                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;
                $auxiliarU->assistantUnit_year = $year;
                $auxiliarU->assistantUnit_period = $period;
                $auxiliarU->assistantUnit_charge = $articulo->inventoryDetails_amount;
                $auxiliarU->assistantUnit_payment = null;
                $auxiliarU->assistantUnit_chargeUnit = (float)$articulo->inventoryDetails_inventoryAmount;
                $auxiliarU->assistantUnit_paymentUnit = null;
                $auxiliarU->assistantUnit_apply = $articulo->inventoryDetails_apply;
                $auxiliarU->assistantUnit_applyID =  $articulo->inventoryDetails_applyIncrement;
                $auxiliarU->assistantUnit_canceled = 0;
                $auxiliarU->save();
            }
        }

        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Salida por Traspaso') {

            //buscamos sus articulos
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->where('inventoryDetails_branchOffice', '=', $folioAfectar->inventories_branchOffice)->get();


            foreach ($articulos as $articulo) {
                //agregar datos a aux
                $auxiliarU = new PROC_ASSISTANT_UNITS();
                $auxiliarU->assistantUnit_companieKey = $folioAfectar->inventories_company;
                $auxiliarU->assistantUnit_branchKey = $folioAfectar->inventories_branchOffice;
                $auxiliarU->assistantUnit_branch = 'Inv';
                $auxiliarU->assistantUnit_movement = $folioAfectar->inventories_movement;
                $auxiliarU->assistantUnit_movementID = $folioAfectar->inventories_movementID;
                $auxiliarU->assistantUnit_module = 'Inv';
                $auxiliarU->assistantUnit_moduleID = $articulo->inventoryDetails_inventoryID;
                $auxiliarU->assistantUnit_money = $folioAfectar->inventories_money;
                $auxiliarU->assistantUnit_typeChange = $folioAfectar->inventories_typeChange;
                $auxiliarU->assistantUnit_group =  $folioAfectar->inventories_depot;
                $auxiliarU->assistantUnit_account = $articulo->inventoryDetails_article;
                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;
                $auxiliarU->assistantUnit_year = $year;
                $auxiliarU->assistantUnit_period = $period;
                $auxiliarU->assistantUnit_charge = null;
                $auxiliarU->assistantUnit_payment = $articulo->inventoryDetails_amount;
                $auxiliarU->assistantUnit_chargeUnit = null;
                $auxiliarU->assistantUnit_paymentUnit = (float)$articulo->inventoryDetails_inventoryAmount;
                $auxiliarU->assistantUnit_apply = $articulo->inventoryDetails_apply;
                $auxiliarU->assistantUnit_applyID =  $articulo->inventoryDetails_applyIncrement;
                $auxiliarU->assistantUnit_canceled = 0;
                $auxiliarU->save();
            }
        }

        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Entrada por Traspaso') {

            //buscamos sus articulos
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->where('inventoryDetails_branchOffice', '=', $folioAfectar->inventories_branchOffice)->get();


            foreach ($articulos as $articulo) {
                //agregar datos a aux
                $auxiliarU = new PROC_ASSISTANT_UNITS();
                $auxiliarU->assistantUnit_companieKey = $folioAfectar->inventories_company;
                $auxiliarU->assistantUnit_branchKey = $folioAfectar->inventories_branchOffice;
                $auxiliarU->assistantUnit_branch = 'Inv';
                $auxiliarU->assistantUnit_movement = $folioAfectar->inventories_movement;
                $auxiliarU->assistantUnit_movementID = $folioAfectar->inventories_movementID;
                $auxiliarU->assistantUnit_module = 'Inv';
                $auxiliarU->assistantUnit_moduleID = $articulo->inventoryDetails_inventoryID;
                $auxiliarU->assistantUnit_money = $folioAfectar->inventories_money;
                $auxiliarU->assistantUnit_typeChange = $folioAfectar->inventories_typeChange;
                $auxiliarU->assistantUnit_group = $folioAfectar->inventories_depotDestiny;
                $auxiliarU->assistantUnit_account = $articulo->inventoryDetails_article;
                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;
                $auxiliarU->assistantUnit_year = $year;
                $auxiliarU->assistantUnit_period = $period;
                $auxiliarU->assistantUnit_charge = $articulo->inventoryDetails_amount;
                $auxiliarU->assistantUnit_payment = null;
                $auxiliarU->assistantUnit_chargeUnit = (float)$articulo->inventoryDetails_inventoryAmount;
                $auxiliarU->assistantUnit_paymentUnit = null;
                $auxiliarU->assistantUnit_apply = $articulo->inventoryDetails_apply;
                $auxiliarU->assistantUnit_applyID =  $articulo->inventoryDetails_applyIncrement;
                $auxiliarU->assistantUnit_canceled = 0;
                $auxiliarU->save();
            }
        }
    }

    public function agregarAlmacen($folio)
    {
        $folioAfectar = PROC_INVENTORIES::where('inventories_id', '=', $folio)->first();
        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Ajuste de Inventario') {
            // echo 'Entrou';
            // dd($folioAfectar);
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->get();

            foreach ($articulos as $articulo) {
                if ($articulo['inventoryDetails_type'] !== "Servicio") {
                    $cantidad = $articulo->inventoryDetails_inventoryAmount;
                    $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->inventoryDetails_article)->where('articlesInv_depot', '=', $folioAfectar->inventories_depot)->first();

                    $inventario == null ? $inventario = new PROC_ARTICLES_INV() : $inventario;
                    $inventario->articlesInv_depot = $folioAfectar->inventories_depot;
                    $inventario->articlesInv_branchKey = $folioAfectar->inventories_branchOffice;
                    $inventario->articlesInv_companieKey = $folioAfectar->inventories_company;
                    $inventario->articlesInv_inventory = ($inventario->articlesInv_inventory + $cantidad);
                    $inventario->articlesInv_article = $articulo->inventoryDetails_article;

                    $inventario->save();
                    $inventario = null;
                }
            }
        }

        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Transferencia entre Alm.') {
            // dd($folioAfectar);
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->get();

            // dd($folioAfectar, $articulos);
            foreach ($articulos as $articulo) {

                $cantidad = $articulo->inventoryDetails_inventoryAmount;
                $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->inventoryDetails_article)->where('articlesInv_depot', '=', $folioAfectar->inventories_depot)->first();

                $inventario == null ? $inventario = new PROC_ARTICLES_INV() : $inventario;
                $inventario->articlesInv_depot = $folioAfectar->inventories_depot;
                $inventario->articlesInv_branchKey = $folioAfectar->inventories_branchOffice;
                $inventario->articlesInv_companieKey = $folioAfectar->inventories_company;
                $inventario->articlesInv_inventory = ($inventario->articlesInv_inventory - $cantidad);
                $inventario->articlesInv_article = $articulo->inventoryDetails_article;

                $inventario->save();
                $inventario = null;

                $inventario2 = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->inventoryDetails_article)->where('articlesInv_depot', '=', $folioAfectar->inventories_depotDestiny)->first();

                $inventario2 == null ? $inventario2 = new PROC_ARTICLES_INV() : $inventario2;
                $inventario2->articlesInv_depot = $folioAfectar->inventories_depotDestiny;
                $inventario2->articlesInv_branchKey = $folioAfectar->inventories_branchOffice;
                $inventario2->articlesInv_companieKey = $folioAfectar->inventories_company;
                $inventario2->articlesInv_inventory = ($inventario2->articlesInv_inventory + $cantidad);
                $inventario2->articlesInv_article = $articulo->inventoryDetails_article;

                $inventario2->save();
                $inventario2 = null;
            }
        }

        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Salida por Traspaso') {
            // dd($folioAfectar);
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->get();

            // dd($folioAfectar, $articulos);
            foreach ($articulos as $articulo) {

                $cantidad = $articulo->inventoryDetails_inventoryAmount;
                $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->inventoryDetails_article)->where('articlesInv_depot', '=', $folioAfectar->inventories_depot)->first();

                $inventario == null ? $inventario = new PROC_ARTICLES_INV() : $inventario;
                $inventario->articlesInv_depot = $folioAfectar->inventories_depot;
                $inventario->articlesInv_branchKey = $folioAfectar->inventories_branchOffice;
                $inventario->articlesInv_companieKey = $folioAfectar->inventories_company;
                $inventario->articlesInv_inventory = ($inventario->articlesInv_inventory - $cantidad);
                $inventario->articlesInv_article = $articulo->inventoryDetails_article;

                $inventario->save();
                $inventario = null;
            }
        }

        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Entrada por Traspaso') {
            // dd($folioAfectar);
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->get();

            // dd($folioAfectar, $articulos);
            foreach ($articulos as $articulo) {

                $cantidad = $articulo->inventoryDetails_inventoryAmount;
                $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->inventoryDetails_article)->where('articlesInv_depot', '=', $folioAfectar->inventories_depotDestiny)->first();

                $inventario == null ? $inventario = new PROC_ARTICLES_INV() : $inventario;
                $inventario->articlesInv_depot = $folioAfectar->inventories_depotDestiny;
                $inventario->articlesInv_branchKey = $folioAfectar->inventories_branchOffice;
                $inventario->articlesInv_companieKey = $folioAfectar->inventories_company;
                $inventario->articlesInv_inventory = ($inventario->articlesInv_inventory + $cantidad);
                $inventario->articlesInv_article = $articulo->inventoryDetails_article;

                $inventario->save();
                $inventario = null;
            }
        }
    }

    public function costoPromedio($folio)
    {
        $folioAfectar = PROC_INVENTORIES::where('inventories_id', '=', $folio)->first();

        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Ajuste de Inventario') {
            $contador = 0;
            $articuloClave = [];
            //sacamos sus articulos
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->get();

            foreach ($articulos as $articulo) {
                $articuloClave[$contador] = $articulo->inventoryDetails_article;
                $contador++;
            }

            $cantidadAux = [];
            $cantidadAux2 = [];
            $cantidadInventario = [];



            foreach ($articuloClave as $key => $articulo) {
                $costoAuxArticulo = PROC_ASSISTANT_UNITS::where('assistantUnit_account', '=', $articulo)->where('assistantUnit_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('assistantUnit_companieKey', '=', $folioAfectar->inventories_company)->where('assistantUnit_group', '=', $folioAfectar->inventories_depot)->get()->sum('assistantUnit_charge');
                $cantidadAux[$articulo] = $costoAuxArticulo;

                $costoAuxArticulo2 = PROC_ASSISTANT_UNITS::where('assistantUnit_account', '=', $articulo)->where('assistantUnit_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('assistantUnit_companieKey', '=', $folioAfectar->inventories_company)->where('assistantUnit_group', '=', $folioAfectar->inventories_depot)->get()->sum('assistantUnit_payment');

                $cantidadArticulos = PROC_ARTICLES_INV::where('articlesInv_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesInv_article', '=', $articuloClave[$key])->where('articlesInv_depot', '=', $folioAfectar->inventories_depot)->get()->sum('articlesInv_inventory');
                $cantidadInventario[$articulo] = $cantidadArticulos;

                if ($costoAuxArticulo2 == null) {
                    $costoAuxArticulo2 = 0;
                }
                $cantidadAux2[$articulo] = $costoAuxArticulo2;
                //  dd($costoAuxArticulo);
            }



            foreach ($articulos as $articulo) {
                //agregamos costo promedio
                if ($articulo->inventoryDetails_quantity > 0) {


                    $costoPromedio = ($cantidadAux[$articulo->inventoryDetails_article] - $cantidadAux2[$articulo->inventoryDetails_article]) / $cantidadInventario[$articulo->inventoryDetails_article];



                    $articuloCostoH = new PROC_ARTICLES_COST_HIS();
                    //  dd($costoPromedio);

                    $articuloCostoH2 = PROC_ARTICLES_COST_HIS::where('articlesCostHis_article', '=', $articulo->inventoryDetails_article)->where('articlesCostHis_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesCostHis_companieKey', '=', $folioAfectar->inventories_company)->where('articlesCostHis_depotKey', '=', $folioAfectar->inventories_depot)->orderBy('created_at', 'desc')->first();



                    if ($articuloCostoH2 === null) {

                        $articuloCostoH->articlesCostHis_companieKey = $folioAfectar->inventories_company;
                        $articuloCostoH->articlesCostHis_branchKey = $folioAfectar->inventories_branchOffice;
                        $articuloCostoH->articlesCostHis_depotKey = $folioAfectar->inventories_depot;
                        $articuloCostoH->articlesCostHis_article = $articulo->inventoryDetails_article;
                        $articuloCostoH->articlesCostHis_lastCost = $articuloCostoH2 ? $articuloCostoH2->articlesCostHis_currentCost : null;
                        $articuloCostoH->articlesCostHis_currentCost =   $articuloCostoH2 ? $articuloCostoH2->articlesCostHis_currentCost : 0;
                        $articuloCostoH->articlesCostHis_averageCost =    $costoPromedio;
                        $articuloCostoH->created_at = date('Y-m-d H:i:s');
                        $articuloCostoH->save();
                    } else {

                        if ($articuloCostoH2->articlesCostHis_averageCost != $costoPromedio) {
                            $articuloCostoH->articlesCostHis_companieKey = $folioAfectar->inventories_company;
                            $articuloCostoH->articlesCostHis_branchKey = $folioAfectar->inventories_branchOffice;
                            $articuloCostoH->articlesCostHis_depotKey = $folioAfectar->inventories_depot;
                            $articuloCostoH->articlesCostHis_article = $articulo->inventoryDetails_article;
                            $articuloCostoH->articlesCostHis_lastCost = $articuloCostoH2 ? $articuloCostoH2->articlesCostHis_currentCost : null;
                            $articuloCostoH->articlesCostHis_currentCost =   $articuloCostoH2 ? $articuloCostoH2->articlesCostHis_currentCost : 0;
                            $articuloCostoH->articlesCostHis_averageCost =    $costoPromedio;
                            $articuloCostoH->created_at = date('Y-m-d H:i:s');
                            $articuloCostoH->save();
                        }
                    }
                    // dd($articuloCosto2);

                    //agregamos costo promedio

                    $articuloCosto = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articulo->inventoryDetails_article)->where('articlesCost_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesCost_companieKey', '=', $folioAfectar->inventories_company)->where('articlesCost_depotKey', '=', $folioAfectar->inventories_depot)->first();


                    $articuloReferencia = PROC_ARTICLES_COST_HIS::where('articlesCostHis_article', '=', $articulo->inventoryDetails_article)->where('articlesCostHis_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesCostHis_companieKey', '=', $folioAfectar->inventories_company)->where('articlesCostHis_depotKey', '=', $folioAfectar->inventories_depot)->orderBy('created_at', 'desc')->first();

                    if ($articuloCosto == null) {
                        $articuloCosto = new PROC_ARTICLES_COST();
                        $articuloCosto->articlesCost_companieKey = $folioAfectar->inventories_company;
                        $articuloCosto->articlesCost_branchKey = $folioAfectar->inventories_branchOffice;
                        $articuloCosto->articlesCost_depotKey = $folioAfectar->inventories_depot;
                        $articuloCosto->articlesCost_article = $articulo->inventoryDetails_article;
                        $articuloCosto->articlesCost_lastCost = $articuloReferencia ? $articuloReferencia->articlesCostHis_currentCost : null;
                        $articuloCosto->articlesCost_averageCost =  $articuloReferencia->articlesCostHis_averageCost;
                        $articuloCosto->save();
                    } else {
                        $articuloCosto->articlesCost_companieKey = $folioAfectar->inventories_company;
                        $articuloCosto->articlesCost_branchKey = $folioAfectar->inventories_branchOffice;
                        $articuloCosto->articlesCost_depotKey = $folioAfectar->inventories_depot;
                        $articuloCosto->articlesCost_article = $articulo->inventoryDetails_article;
                        $articuloCosto->articlesCost_lastCost = $articuloReferencia ? $articuloReferencia->articlesCostHis_currentCost : null;
                        $articuloCosto->articlesCost_averageCost =  $articuloReferencia->articlesCostHis_averageCost;
                        $articuloCosto->update();
                    }
                }
            }
        }

        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Entrada por Traspaso') {
            $contador = 0;
            $articuloClave = [];
            //sacamos sus articulos
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->get();

            foreach ($articulos as $articulo) {
                $articuloClave[$contador] = $articulo->inventoryDetails_article;
                $contador++;
            }

            $cantidadAux = [];
            $cantidadAux2 = [];
            $cantidadInventario = [];



            foreach ($articuloClave as $key => $articulo) {
                $costoAuxArticulo = PROC_ASSISTANT_UNITS::where('assistantUnit_account', '=', $articulo)->where('assistantUnit_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('assistantUnit_companieKey', '=', $folioAfectar->inventories_company)->where('assistantUnit_group', '=', $folioAfectar->inventories_depotDestiny)->get()->sum('assistantUnit_charge');
                $cantidadAux[$articulo] = $costoAuxArticulo;

                $costoAuxArticulo2 = PROC_ASSISTANT_UNITS::where('assistantUnit_account', '=', $articulo)->where('assistantUnit_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('assistantUnit_companieKey', '=', $folioAfectar->inventories_company)->where('assistantUnit_group', '=', $folioAfectar->inventories_depotDestiny)->get()->sum('assistantUnit_payment');

                $cantidadArticulos = PROC_ARTICLES_INV::where('articlesInv_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesInv_article', '=', $articuloClave[$key])->where('articlesInv_depot', '=', $folioAfectar->inventories_depotDestiny)->get()->sum('articlesInv_inventory');
                $cantidadInventario[$articulo] = $cantidadArticulos;

                if ($costoAuxArticulo2 == null) {
                    $costoAuxArticulo2 = 0;
                }
                $cantidadAux2[$articulo] = $costoAuxArticulo2;
                //  dd($costoAuxArticulo);
            }



            foreach ($articulos as $articulo) {
                //agregamos costo promedio
                if ($articulo->inventoryDetails_quantity > 0) {

                    $costoPromedio = ($cantidadAux[$articulo->inventoryDetails_article] - $cantidadAux2[$articulo->inventoryDetails_article]) / $cantidadInventario[$articulo->inventoryDetails_article];


                    $costoActual = ($articulo->inventoryDetails_unitCost / $articulo->inventoryDetails_inventoryAmount) * $articulo->inventoryDetails_quantity;

                    $articuloCostoH = new PROC_ARTICLES_COST_HIS();
                    //  dd($costoPromedio);

                    $articuloCostoH2 = PROC_ARTICLES_COST_HIS::where('articlesCostHis_article', '=', $articulo->inventoryDetails_article)->where('articlesCostHis_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesCostHis_companieKey', '=', $folioAfectar->inventories_company)->where('articlesCostHis_depotKey', '=', $folioAfectar->inventories_depotDestiny)->orderBy('created_at', 'desc')->first();



                    if ($articuloCostoH2 === null) {

                        $articuloCostoH->articlesCostHis_companieKey = $folioAfectar->inventories_company;
                        $articuloCostoH->articlesCostHis_branchKey = $folioAfectar->inventories_branchOffice;
                        $articuloCostoH->articlesCostHis_depotKey = $folioAfectar->inventories_depotDestiny;
                        $articuloCostoH->articlesCostHis_article = $articulo->inventoryDetails_article;
                        $articuloCostoH->articlesCostHis_lastCost = $articuloCostoH2 ? $articuloCostoH2->articlesCostHis_currentCost : null;
                        $articuloCostoH->articlesCostHis_currentCost =   $costoActual;
                        $articuloCostoH->articlesCostHis_averageCost =    $costoPromedio;
                        $articuloCostoH->created_at = date('Y-m-d H:i:s');
                        $articuloCostoH->save();
                    } else {

                        if ($articuloCostoH2->articlesCostHis_averageCost != $costoPromedio) {
                            $articuloCostoH->articlesCostHis_companieKey = $folioAfectar->inventories_company;
                            $articuloCostoH->articlesCostHis_branchKey = $folioAfectar->inventories_branchOffice;
                            $articuloCostoH->articlesCostHis_depotKey = $folioAfectar->inventories_depotDestiny;
                            $articuloCostoH->articlesCostHis_article = $articulo->inventoryDetails_article;
                            $articuloCostoH->articlesCostHis_lastCost = $articuloCostoH2 ? $articuloCostoH2->articlesCostHis_currentCost : null;
                            $articuloCostoH->articlesCostHis_currentCost =  $costoActual;
                            $articuloCostoH->articlesCostHis_averageCost =    $costoPromedio;
                            $articuloCostoH->created_at = date('Y-m-d H:i:s');
                            $articuloCostoH->save();
                        }
                    }
                    // dd($articuloCosto2);

                    //agregamos costo promedio

                    $articuloCosto = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articulo->inventoryDetails_article)->where('articlesCost_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesCost_companieKey', '=', $folioAfectar->inventories_company)->where('articlesCost_depotKey', '=', $folioAfectar->inventories_depotDestiny)->first();


                    $articuloReferencia = PROC_ARTICLES_COST_HIS::where('articlesCostHis_article', '=', $articulo->inventoryDetails_article)->where('articlesCostHis_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesCostHis_companieKey', '=', $folioAfectar->inventories_company)->where('articlesCostHis_depotKey', '=', $folioAfectar->inventories_depotDestiny)->orderBy('created_at', 'desc')->first();

                    if ($articuloCosto == null) {
                        $articuloCosto = new PROC_ARTICLES_COST();
                        $articuloCosto->articlesCost_companieKey = $folioAfectar->inventories_company;
                        $articuloCosto->articlesCost_branchKey = $folioAfectar->inventories_branchOffice;
                        $articuloCosto->articlesCost_depotKey = $folioAfectar->inventories_depotDestiny;
                        $articuloCosto->articlesCost_article = $articulo->inventoryDetails_article;
                        $articuloCosto->articlesCost_lastCost = $articuloReferencia ? $articuloReferencia->articlesCostHis_currentCost : null;
                        $articuloCosto->articlesCost_averageCost =  $articuloReferencia->articlesCostHis_averageCost;
                        $articuloCosto->save();
                    } else {
                        $articuloCosto->articlesCost_companieKey = $folioAfectar->inventories_company;
                        $articuloCosto->articlesCost_branchKey = $folioAfectar->inventories_branchOffice;
                        $articuloCosto->articlesCost_depotKey = $folioAfectar->inventories_depotDestiny;
                        $articuloCosto->articlesCost_article = $articulo->inventoryDetails_article;
                        $articuloCosto->articlesCost_lastCost = $articuloReferencia ? $articuloReferencia->articlesCostHis_currentCost : null;
                        $articuloCosto->articlesCost_averageCost =  $articuloReferencia->articlesCostHis_averageCost;
                        $articuloCosto->update();
                    }
                } //
            }
        }
    }

    public function generarTransito($folio)
    {
        $folioAfectar = PROC_INVENTORIES::where('inventories_id', '=', $folio)->first();
        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Salida por Traspaso') {
            //generar un transito en inventarios
            //buscar la sucursal destino con respecto a almacen destino
            $sucursalDestino = CAT_DEPOTS::where('depots_key', '=', $folioAfectar->inventories_depotDestiny)->first();

            $folioTransito = PROC_INVENTORIES::where('inventories_movement', '=', 'Tránsito')->where('inventories_branchOffice', '=', $sucursalDestino->depots_branchlId)->max('inventories_movementID');
            $folioTransito = $folioTransito == null ? 1 : $folioTransito + 1;
            //origen del movimiento

            $transito = new PROC_INVENTORIES();
            $transito->inventories_movement = 'Tránsito';
            $transito->inventories_movementID = $folioTransito;
            $transito->inventories_issueDate = date('Y-m-d');
            $transito->inventories_concept = $folioAfectar->inventories_concept;
            $transito->inventories_money = $folioAfectar->inventories_money;
            $transito->inventories_typeChange = $folioAfectar->inventories_typeChange;
            $transito->inventories_reference = $folioAfectar->inventories_reference;
            $transito->inventories_company = session('company')->companies_key;
            $transito->inventories_branchOffice = $sucursalDestino->depots_branchlId;
            $transito->inventories_depot = $folioAfectar->inventories_depot;
            $transito->inventories_depotType = $folioAfectar->inventories_depotType;
            $transito->inventories_depotDestiny = $folioAfectar->inventories_depotDestiny;
            $transito->inventories_depotDestinyType = $folioAfectar->inventories_depotDestinyType;
            $transito->inventories_user = Auth::user()->username;
            $transito->inventories_total = $folioAfectar->inventories_total;
            $transito->inventories_lines = $folioAfectar->inventories_lines;
            $transito->inventories_status = $this->estatus[1];
            $transito->inventories_originType = 'Inv';
            $transito->inventories_origin = $folioAfectar->inventories_movement;
            $transito->inventories_originID = $folioAfectar->inventories_movementID;
            $transito->inventories_jsonData = $folioAfectar->inventories_jsonData;


            $transito->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $transito->updated_at = Carbon::now()->format('Y-m-d H:i:s');

            $create = $transito->save();

            $lastId = PROC_INVENTORIES::latest('inventories_id')->first()->inventories_id;

            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->get();

            foreach ($articulos as $articulo) {
                $articuloTransito = new PROC_INVENTORIES_DETAILS();
                $articuloTransito->inventoryDetails_inventoryID = $lastId;
                $articuloTransito->inventoryDetails_article = $articulo->inventoryDetails_article;
                $articuloTransito->inventoryDetails_type = $articulo->inventoryDetails_type;
                $articuloTransito->inventoryDetails_descript = $articulo->inventoryDetails_descript;
                $articuloTransito->inventoryDetails_quantity = $articulo->inventoryDetails_quantity;
                $articuloTransito->inventoryDetails_unitCost = $articulo->inventoryDetails_unitCost;
                $articuloTransito->inventoryDetails_unit = $articulo->inventoryDetails_unit;
                $articuloTransito->inventoryDetails_factor = $articulo->inventoryDetails_factor;
                $articuloTransito->inventoryDetails_inventoryAmount = $articulo->inventoryDetails_inventoryAmount;
                $articuloTransito->inventoryDetails_amount = $articulo->inventoryDetails_amount;
                $articuloTransito->inventoryDetails_ivaPorcent = $articulo->inventoryDetails_ivaPorcent;
                $articuloTransito->inventoryDetails_total = $articulo->inventoryDetails_total;
                $articuloTransito->inventoryDetails_apply = $articulo->inventoryDetails_apply;
                $articuloTransito->inventoryDetails_applyIncrement = $articulo->inventoryDetails_applyIncrement;
                $articuloTransito->inventoryDetails_branchOffice = $articulo->inventoryDetails_branchOffice;
                $articuloTransito->inventoryDetails_depot = $articulo->inventoryDetails_depot;
                $articuloTransito->inventoryDetails_outstandingAmount = $articulo->inventoryDetails_quantity;
                $articuloTransito->inventoryDetails_canceledAmount = $articulo->inventoryDetails_canceledAmount;
                $articuloTransito->inventoryDetails_referenceArticles = $articulo->inventoryDetails_referenceArticles;
                $articuloTransito->save();

                $lastIdDetalle = PROC_INVENTORIES_DETAILS::latest('inventoryDetails_id')->first()->inventoryDetails_id;

                if ($articulo->inventoryDetails_type == 'Serie') {
                    $series = PROC_DEL_SERIES_MOV::where('delSeriesMov_inventoryID', '=', $folioAfectar->inventories_id)->where('delSeriesMov_article', '=', $articulo->inventoryDetails_article)->where('delSeriesMov_articleID', '=', $articulo->inventoryDetails_id)->get();
                    foreach ($series as $serie) {
                        $serieTransito = new PROC_DEL_SERIES_MOV();
                        $serieTransito->delSeriesMov_inventoryID = $lastId;
                        $serieTransito->delSeriesMov_article = $serie->delSeriesMov_article;
                        $serieTransito->delSeriesMov_articleID = $lastIdDetalle;
                        $serieTransito->delSeriesMov_lotSerie = $serie->delSeriesMov_lotSerie;
                        $serieTransito->delSeriesMov_quantity = $serie->delSeriesMov_quantity;
                        $serieTransito->delSeriesMov_branchKey = $serie->delSeriesMov_branchKey;
                        $serieTransito->delSeriesMov_companieKey = $serie->delSeriesMov_companieKey;
                        $serieTransito->delSeriesMov_module = 'INV';

                        $serieTransito->save();
                    }
                } //
            }


            // dd($transito, $folioAfectar, $articulos, $articuloTransito);
            $this->agregarMov2($folioAfectar->inventories_id, $lastId);
            return $lastId;
        }
    }

    public function agregarMov($folio)
    {
        $folioAfectar = PROC_INVENTORIES::where('inventories_id', '=', $folio)->first();
        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Entrada por Traspaso') {

            $sucursalDestino = CAT_DEPOTS::where('depots_key', '=', $folioAfectar->inventories_depotDestiny)->first();
            //buscar origen del movimiento
            $movOrigen = PROC_INVENTORIES::where('inventories_movementID', '=', $folioAfectar->inventories_originID)->where('inventories_movement', '=', $folioAfectar->inventories_origin)->where('inventories_branchOffice', '=', $sucursalDestino->depots_branchlId)->first();


            $movimiento = new PROC_MOVEMENT_FLOW();
            $movimiento->movementFlow_branch = $folioAfectar->inventories_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->inventories_company;
            $movimiento->movementFlow_moduleOrigin = 'Inv';
            $movimiento->movementFlow_originID = $movOrigen->inventories_id;
            $movimiento->movementFlow_movementOrigin = $movOrigen->inventories_movement;
            $movimiento->movementFlow_movementOriginID = $movOrigen->inventories_movementID;
            $movimiento->movementFlow_moduleDestiny = 'Inv';
            $movimiento->movementFlow_destinityID = $folioAfectar->inventories_id;
            $movimiento->movementFlow_movementDestinity = $folioAfectar->inventories_movement;
            $movimiento->movementFlow_movementDestinityID = $folioAfectar->inventories_movementID;
            $movimiento->movementFlow_cancelled = 0;
            $movimiento->save();
        }
    }

    public function agregarMov2($folio, $destino = false)
    {

        $destino ? $destino : null;

        $movDestino = PROC_INVENTORIES::where('inventories_id', '=', $destino)->first();

        $folioAfectar = PROC_INVENTORIES::where('inventories_id', '=', $folio)->first();
        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Salida por Traspaso') {

            $movimiento = new PROC_MOVEMENT_FLOW();
            $movimiento->movementFlow_branch = $folioAfectar->inventories_branchOffice;
            $movimiento->movementFlow_company = $folioAfectar->inventories_company;
            $movimiento->movementFlow_moduleOrigin = 'Inv';
            $movimiento->movementFlow_originID = $folioAfectar->inventories_id;
            $movimiento->movementFlow_movementOrigin = $folioAfectar->inventories_movement;
            $movimiento->movementFlow_movementOriginID = $folioAfectar->inventories_movementID;
            $movimiento->movementFlow_moduleDestiny = 'Inv';
            $movimiento->movementFlow_destinityID = $movDestino->inventories_id;
            $movimiento->movementFlow_movementDestinity = $movDestino->inventories_movement;
            $movimiento->movementFlow_movementDestinityID = $movDestino->inventories_movementID;
            $movimiento->movementFlow_cancelled = 0;
            $movimiento->save();
        }
    }

    public function concluirOrigines($folio)
    {
        $folioAfectar = PROC_INVENTORIES::where('inventories_id', '=', $folio)->first();


        //concluir origen de cotizacion a pendiente cuando se afecte
        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Entrada por Traspaso') {

            $movOrigin = PROC_INVENTORIES::where('inventories_movementID', '=', $folioAfectar->inventories_originID)->where('inventories_movement', '=', $folioAfectar->inventories_origin)->where('inventories_company', '=', $folioAfectar->inventories_company)->where('inventories_branchOffice', '=', $folioAfectar->inventories_branchOffice)->first();

            //concluir origen
            if ($movOrigin != null) {

                //quitar el pendiente del detalle
                $detalle = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->get();
                // dd($movOrigin, $folioAfectar, $detalle);
                //buscamos el articulo origen con el referenceArticles
                foreach ($detalle as $detalle) {
                    $articulo = PROC_INVENTORIES_DETAILS::where('inventoryDetails_id', '=', $detalle->inventoryDetails_referenceArticles)->first();
                    $articulo->inventoryDetails_outstandingAmount = $articulo->inventoryDetails_outstandingAmount - $detalle->inventoryDetails_quantity;

                    $articulo->update();
                }

                //verificamos que todos los articulos de la cotizacion esten concluidos
                $detalle2 = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $movOrigin->inventories_id)->get();
                $concluido = false;
                foreach ($detalle2 as $detalle2) {
                    if ($detalle2->inventoryDetails_outstandingAmount <= 0) {
                        $concluido = true;
                    } else {
                        $concluido = false;
                        break;
                    }
                }

                //Concluimos la cotizacion si todos los articulos no tienen pendientes
                if ($concluido == true) {
                    $movOrigin->inventories_status = $this->estatus[2];
                    $movOrigin->update();
                }
                // dd($detalle, $detalle2);
            }
        }
    }

    public function eliminarInventario(Request $request)
    {

        $inventario = PROC_INVENTORIES::where('inventories_id', '=', $request->id)->first();

        //buscamos sus articulos
        if ($inventario->inventories_movement != 'Entrada por Traspaso') {
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $request->id)->where('inventoryDetails_branchOffice', '=', $inventario->inventories_branchOffice)->get();
        } else {
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $request->id)->where('inventoryDetails_branchOffice', '!=', $inventario->inventories_branchOffice)->get();
        }

        // dd($inventario, $articulos);
        if ($articulos->count() > 0) {
            //eliminamos sus articulos
            foreach ($articulos as $articulo) {

                if ($articulo->inventoryDetails_type == 'Serie') {

                    if ($inventario->inventories_movement == 'Ajuste de Inventario') {

                        if ($articulo->inventoryDetails_quantity > 0) {
                            $series = PROC_LOT_SERIES_MOV2::where('lotSeriesMov2_inventoryID', '=', $articulo->inventoryDetails_inventoryID)->where('lotSeriesMov2_articleID', '=', $articulo->inventoryDetails_id)->get();
                            // dd($series);
                            if ($series != null) {
                                foreach ($series as $serie) {
                                    $serie->delete();
                                }
                            }
                        } else {
                            $series = PROC_DEL_SERIES_MOV::where('delSeriesMov_inventoryID', '=', $articulo->inventoryDetails_inventoryID)->where('delSeriesMov_articleID', '=', $articulo->inventoryDetails_id)->get();
                            // dd($series);
                            if ($series != null) {
                                foreach ($series as $serie) {
                                    $serie->delete();
                                }
                            }
                        }
                    }


                    if ($inventario->inventories_movement != 'Ajuste de Inventario') {

                        $series = PROC_DEL_SERIES_MOV::where('delSeriesMov_inventoryID', '=', $articulo->inventoryDetails_inventoryID)->where('delSeriesMov_articleID', '=', $articulo->inventoryDetails_id)->get();

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


        // dd($articulos);
        if ($inventario->inventories_status === $this->estatus[0] && $articulosDelete === true) {
            $isDelete = $inventario->delete();
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

    public function cancelarInventario(Request $request)
    {
        $folioAfectar = PROC_INVENTORIES::where('inventories_id', '=', $request->id)->first();
        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Ajuste de Inventario') {

            $ajusteCancelado = false;
            $transferenciaCancelado = true;
            $salidaCancelado = true;
            $reciboCancelado = true;

            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->where('inventoryDetails_branchOffice', '=', $folioAfectar->inventories_branchOffice)->get();


            foreach ($articulos as $articulo) {

                $articuloVenta = $articulo->inventoryDetails_inventoryAmount;

                if (
                    $articuloVenta > 0
                ) {

                    $validar = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->inventoryDetails_article)->where('articlesInv_depot', '=', $folioAfectar->inventories_depot)->where('articlesInv_branchKey', '=', $folioAfectar->inventories_branchOffice)->first();

                    if ($validar == null) {
                        $message = 'La cantidad indicada excede al disponible en el almacen: ' . $request['almacenKey'];
                        $status = 500;
                        $lastId = false;
                        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                    }

                    $disponible = (float) $validar->articlesInv_inventory;

                    if (
                        $disponible < $articuloVenta
                    ) {
                        $message = 'La cantidad indicada excede al disponible en el almacen: ' . $request['almacenKey'];
                        $status = 500;
                        $lastId = false;

                        return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                    }
                }


                if ($articulo->inventoryDetails_type == 'Serie') {
                    if ($articulo->inventoryDetails_quantity > 0) {
                        $series = PROC_LOT_SERIES_MOV2::where('lotSeriesMov2_inventoryID', '=', $articulo->inventoryDetails_inventoryID)->where('lotSeriesMov2_articleID', '=', $articulo->inventoryDetails_id)->get();
                        // dd($series);
                        if (
                            $series != null
                        ) {
                            foreach ($series as $serie) {

                                //buscamos la serie en las tablas de eliminar serie tanto de inventarios como de ventas

                                $seriesEliminados = PROC_DEL_SERIES_MOV::where('delSeriesMov_lotSerie', '=', $serie->lotSeriesMov2_lotSerie)->where('delSeriesMov_cancelled', '=', 0)->get()->toArray();
                                //   DD($folioAfectar, $seriesEliminados);
                                if (count($seriesEliminados) > 0) {
                                    $message = 'Alguna serie ya ha sido eliminada/trasnferida en otro movimiento';
                                    $status = 500;
                                    $lastId = false;

                                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                                }

                                $seriesEliminados2 = PROC_DEL_SERIES_MOV2::where('delSeriesMov2_lotSerie', '=', $serie->lotSeriesMov2_lotSerie)->where('delSeriesMov2_cancelled', '=', 0)->get()->toArray();
                                if (count($seriesEliminados2) > 0) {
                                    $message = 'Alguna serie tiene un movimiento de venta pendiente/concluido';
                                    $status = 500;
                                    $lastId = false;

                                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                                }

                                // $serie->delete();
                            }
                        }
                    } else {
                        $series = PROC_DEL_SERIES_MOV::where('delSeriesMov_inventoryID', '=', $articulo->inventoryDetails_inventoryID)->where('delSeriesMov_articleID', '=', $articulo->inventoryDetails_id)->get();
                        // dd($series);
                        if (
                            $series != null
                        ) {
                            foreach ($series as $serie) {
                                $serie->delSeriesMov_cancelled = 1;
                                $serie->update();
                                // $serie->delete();
                                $lot_serie = PROC_LOT_SERIES::where('lotSeries_article', '=', $serie->delSeriesMov_article)->where('lotSeries_lotSerie', '=', $serie->delSeriesMov_lotSerie)->first();
                                if ($lot_serie != null) {
                                    $lot_serie->lotSeries_delete = 0;
                                    $lot_serie->update();
                                }
                            }
                        }
                    }
                }

                if ($articulo->inventoryDetails_type == 'Serie') {
                    if ($articulo->inventoryDetails_quantity > 0) {
                        $series = PROC_LOT_SERIES_MOV2::where('lotSeriesMov2_inventoryID', '=', $articulo->inventoryDetails_inventoryID)->where('lotSeriesMov2_articleID', '=', $articulo->inventoryDetails_id)->get();
                        // dd($series);
                        if (
                            $series != null
                        ) {
                            foreach ($series as $serie) {
                                // $serie->delete();
                                $lot_serie = PROC_LOT_SERIES::where('lotSeries_article', '=', $serie->lotSeriesMov2_article)->where('lotSeries_lotSerie', '=', $serie->lotSeriesMov2_lotSerie)->first();
                                if ($lot_serie != null) {
                                    $lot_serie->lotSeries_delete = 1;
                                    $lot_serie->update();
                                }
                            }
                        }
                    } else {
                        $series = PROC_DEL_SERIES_MOV::where('delSeriesMov_inventoryID', '=', $articulo->inventoryDetails_inventoryID)->where('delSeriesMov_articleID', '=', $articulo->inventoryDetails_id)->get();
                        // dd($series);
                        if (
                            $series != null
                        ) {
                            foreach ($series as $serie) {
                                $serie->delSeriesMov_cancelled = 1;
                                $serie->update();
                                // $serie->delete();
                                $lot_serie = PROC_LOT_SERIES::where('lotSeries_article', '=', $serie->delSeriesMov_article)->where('lotSeries_lotSerie', '=', $serie->delSeriesMov_lotSerie)->first();
                                if ($lot_serie != null) {
                                    $lot_serie->lotSeries_delete = 0;
                                    $lot_serie->update();
                                }
                            }
                        }
                    }
                }


                //agregar datos a aux
                $auxiliarU = new PROC_ASSISTANT_UNITS();
                $auxiliarU->assistantUnit_companieKey = $folioAfectar->inventories_company;
                $auxiliarU->assistantUnit_branchKey = $folioAfectar->inventories_branchOffice;
                $auxiliarU->assistantUnit_branch = 'Inv';
                $auxiliarU->assistantUnit_movement = $folioAfectar->inventories_movement;
                $auxiliarU->assistantUnit_movementID = $folioAfectar->inventories_movementID;
                $auxiliarU->assistantUnit_module = 'Inv';
                $auxiliarU->assistantUnit_moduleID = $articulo->inventoryDetails_inventoryID;
                $auxiliarU->assistantUnit_money = $folioAfectar->inventories_money;
                $auxiliarU->assistantUnit_typeChange = $folioAfectar->inventories_typeChange;
                $auxiliarU->assistantUnit_group = $articulo->inventoryDetails_depot;
                $auxiliarU->assistantUnit_account = $articulo->inventoryDetails_article;
                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;
                $auxiliarU->assistantUnit_year = $year;
                $auxiliarU->assistantUnit_period = $period;

                if ($articulo->inventoryDetails_quantity > 0) {
                    $auxiliarU->assistantUnit_charge = "-" . $articulo->inventoryDetails_amount;
                    $auxiliarU->assistantUnit_payment = null;
                    $auxiliarU->assistantUnit_chargeUnit = -(float)$articulo->inventoryDetails_inventoryAmount;
                    $auxiliarU->assistantUnit_paymentUnit = null;
                } else {
                    $auxiliarU->assistantUnit_charge = null;
                    $auxiliarU->assistantUnit_payment = "-" . $articulo->inventoryDetails_amount;
                    $auxiliarU->assistantUnit_chargeUnit = null;
                    $auxiliarU->assistantUnit_paymentUnit = -(float)$articulo->inventoryDetails_inventoryAmount;
                }



                $auxiliarU->assistantUnit_apply = $articulo->inventoryDetails_apply;
                $auxiliarU->assistantUnit_applyID =  $articulo->inventoryDetails_applyIncrement;
                $auxiliarU->assistantUnit_canceled = 1;

                $validar = $auxiliarU->save();
                if ($validar) {
                    $ajusteCancelado = true;
                } else {
                    $ajusteCancelado = false;
                }


                $cantidad = $articulo->inventoryDetails_inventoryAmount;
                $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->inventoryDetails_article)->where('articlesInv_depot', '=', $folioAfectar->inventories_depot)->first();

                $inventario == null ? $inventario = new PROC_ARTICLES_INV() : $inventario;
                $inventario->articlesInv_depot = $folioAfectar->inventories_depot;
                $inventario->articlesInv_branchKey = $folioAfectar->inventories_branchOffice;
                $inventario->articlesInv_companieKey = $folioAfectar->inventories_company;
                $inventario->articlesInv_inventory = ($inventario->articlesInv_inventory - $cantidad);
                $inventario->articlesInv_article = $articulo->inventoryDetails_article;

                $validar2 = $inventario->save();
                if ($validar2) {
                    $ajusteCancelado = true;
                } else {
                    $ajusteCancelado = false;
                }
                $inventario = null;
            }


            if ($ajusteCancelado) {
                $folioAfectar->inventories_status = $this->estatus[3];

                $folioAfectar->save();
            }

            $this->quitarCostoPromedio($folioAfectar->inventories_id);
        }

        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Transferencia entre Alm.') {

            $ajusteCancelado = true;
            $transferenciaCancelado = false;
            $salidaCancelado = true;
            $reciboCancelado = true;
            //buscamos sus articulos
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->where('inventoryDetails_branchOffice', '=', $folioAfectar->inventories_branchOffice)->get();


            foreach ($articulos as $articulo) {

                $articuloVenta = $articulo->inventoryDetails_inventoryAmount;



                $validar = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->inventoryDetails_article)->where('articlesInv_depot', '=', $folioAfectar->inventories_depotDestiny)->where('articlesInv_branchKey', '=', $folioAfectar->inventories_branchOffice)->first();


                if ($validar == null) {
                    $message = 'La cantidad indicada excede al disponible en el almacen: ' . $request['almacenKey'];
                    $status = 500;
                    $lastId = false;
                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                }

                $disponible = (float) $validar->articlesInv_inventory;

                if (
                    $disponible < $articuloVenta
                ) {
                    $message = 'La cantidad indicada excede al disponible en el almacen: ' . $request['almacenKey'];
                    $status = 500;
                    $lastId = false;

                    return response()->json(['mensaje' => $message, 'estatus' => $status, 'id' => $lastId]);
                }

                if ($articulo->inventoryDetails_type == 'Serie') {
                    $series = PROC_DEL_SERIES_MOV::where('delSeriesMov_inventoryID', '=', $articulo->inventoryDetails_inventoryID)->where('delSeriesMov_articleID', '=', $articulo->inventoryDetails_id)->get();
                    // dd($series);
                    if (
                        $series != null
                    ) {
                        foreach ($series as $serie) {
                            $serie->delSeriesMov_cancelled = 1;
                            $serie->update();
                            // $serie->delete();
                            $lot_serie = PROC_LOT_SERIES::where('lotSeries_article', '=', $serie->delSeriesMov_article)->where('lotSeries_lotSerie', '=', $serie->delSeriesMov_lotSerie)->first();
                            if ($lot_serie != null) {
                                $lot_serie->lotSeries_depot = $folioAfectar->inventories_depot;
                                $lot_serie->update();
                            }
                        }
                    }
                }


                //agregar datos a aux
                $auxiliarU = new PROC_ASSISTANT_UNITS();
                $auxiliarU->assistantUnit_companieKey = $folioAfectar->inventories_company;
                $auxiliarU->assistantUnit_branchKey = $folioAfectar->inventories_branchOffice;
                $auxiliarU->assistantUnit_branch = 'Inv';
                $auxiliarU->assistantUnit_movement = $folioAfectar->inventories_movement;
                $auxiliarU->assistantUnit_movementID = $folioAfectar->inventories_movementID;
                $auxiliarU->assistantUnit_module = 'Inv';
                $auxiliarU->assistantUnit_moduleID = $articulo->inventoryDetails_inventoryID;
                $auxiliarU->assistantUnit_money = $folioAfectar->inventories_money;
                $auxiliarU->assistantUnit_typeChange = $folioAfectar->inventories_typeChange;
                $auxiliarU->assistantUnit_group =  $folioAfectar->inventories_depot;
                $auxiliarU->assistantUnit_account = $articulo->inventoryDetails_article;
                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;
                $auxiliarU->assistantUnit_year = $year;
                $auxiliarU->assistantUnit_period = $period;
                $auxiliarU->assistantUnit_charge = null;
                $auxiliarU->assistantUnit_payment = "-" . $articulo->inventoryDetails_amount;
                $auxiliarU->assistantUnit_chargeUnit = null;
                $auxiliarU->assistantUnit_paymentUnit = -(float)$articulo->inventoryDetails_inventoryAmount;
                $auxiliarU->assistantUnit_apply = $articulo->inventoryDetails_apply;
                $auxiliarU->assistantUnit_applyID =  $articulo->inventoryDetails_applyIncrement;
                $auxiliarU->assistantUnit_canceled = 1;
                $validar = $auxiliarU->save();
                if ($validar) {
                    $transferenciaCancelado = true;
                } else {
                    $transferenciaCancelado = false;
                }

                $auxiliarU = new PROC_ASSISTANT_UNITS();
                $auxiliarU->assistantUnit_companieKey = $folioAfectar->inventories_company;
                $auxiliarU->assistantUnit_branchKey = $folioAfectar->inventories_branchOffice;
                $auxiliarU->assistantUnit_branch = 'Inv';
                $auxiliarU->assistantUnit_movement = $folioAfectar->inventories_movement;
                $auxiliarU->assistantUnit_movementID = $folioAfectar->inventories_movementID;
                $auxiliarU->assistantUnit_module = 'Inv';
                $auxiliarU->assistantUnit_moduleID = $articulo->inventoryDetails_inventoryID;
                $auxiliarU->assistantUnit_money = $folioAfectar->inventories_money;
                $auxiliarU->assistantUnit_typeChange = $folioAfectar->inventories_typeChange;
                $auxiliarU->assistantUnit_group = $folioAfectar->inventories_depotDestiny;
                $auxiliarU->assistantUnit_account = $articulo->inventoryDetails_article;
                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;
                $auxiliarU->assistantUnit_year = $year;
                $auxiliarU->assistantUnit_period = $period;
                $auxiliarU->assistantUnit_charge = "-" . $articulo->inventoryDetails_amount;
                $auxiliarU->assistantUnit_payment = null;
                $auxiliarU->assistantUnit_chargeUnit = -(float)$articulo->inventoryDetails_inventoryAmount;
                $auxiliarU->assistantUnit_paymentUnit = null;
                $auxiliarU->assistantUnit_apply = $articulo->inventoryDetails_apply;
                $auxiliarU->assistantUnit_applyID =  $articulo->inventoryDetails_applyIncrement;
                $auxiliarU->assistantUnit_canceled = 1;
                $validar2 = $auxiliarU->save();

                if ($validar2) {
                    $transferenciaCancelado = true;
                } else {
                    $transferenciaCancelado = false;
                }



                $cantidad = $articulo->inventoryDetails_inventoryAmount;
                $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->inventoryDetails_article)->where('articlesInv_depot', '=', $folioAfectar->inventories_depot)->first();

                $inventario == null ? $inventario = new PROC_ARTICLES_INV() : $inventario;
                $inventario->articlesInv_depot = $folioAfectar->inventories_depot;
                $inventario->articlesInv_branchKey = $folioAfectar->inventories_branchOffice;
                $inventario->articlesInv_companieKey = $folioAfectar->inventories_company;
                $inventario->articlesInv_inventory = ($inventario->articlesInv_inventory + $cantidad);
                $inventario->articlesInv_article = $articulo->inventoryDetails_article;

                $validar3 = $inventario->save();
                if ($validar3) {
                    $transferenciaCancelado = true;
                } else {
                    $transferenciaCancelado = false;
                }
                $inventario = null;

                $inventario2 = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->inventoryDetails_article)->where('articlesInv_depot', '=', $folioAfectar->inventories_depotDestiny)->first();

                $inventario2 == null ? $inventario2 = new PROC_ARTICLES_INV() : $inventario2;
                $inventario2->articlesInv_depot = $folioAfectar->inventories_depotDestiny;
                $inventario2->articlesInv_branchKey = $folioAfectar->inventories_branchOffice;
                $inventario2->articlesInv_companieKey = $folioAfectar->inventories_company;
                $inventario2->articlesInv_inventory = ($inventario2->articlesInv_inventory - $cantidad);
                $inventario2->articlesInv_article = $articulo->inventoryDetails_article;

                $validar4 = $inventario2->save();
                if ($validar4) {
                    $transferenciaCancelado = true;
                } else {
                    $transferenciaCancelado = false;
                }
                $inventario2 = null;
            }

            if ($transferenciaCancelado) {
                $folioAfectar->inventories_status = $this->estatus[3];
                $folioAfectar->save();
            }
        }

        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Salida por Traspaso') {

            try {

                $ajusteCancelado = true;
                $transferenciaCancelado = true;
                $salidaCancelado = false;
                $reciboCancelado = true;

                $sucursalDestino = CAT_DEPOTS::where('depots_key', '=', $folioAfectar->inventories_depotDestiny)->first();

                $transito = PROC_INVENTORIES::where('inventories_branchOffice', '=', $sucursalDestino->depots_branchlId)->where('inventories_origin', '=', $folioAfectar->inventories_movement)->where('inventories_originID', '=', $folioAfectar->inventories_movementID)->where('inventories_depot', '=', $folioAfectar->inventories_depot)->where('inventories_depotDestiny', '=', $folioAfectar->inventories_depotDestiny)->first();

                $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $transito->inventories_id)->get();


                if ($transito->inventories_status === $this->estatus[2]) {
                    $salidaCancelado = false;
                    $status = 400;
                    $message = 'Parcialmente pendiente. No se puede cancelar';

                    return response()->json(['mensaje' => $message, 'estatus' => $status]);
                }


                //verificar si los articulos del transito son distintos a la salida 
                if ($transito->inventories_status === $this->estatus[1]) {

                    foreach ($articulos as $key => $articulo) {
                        if ($articulo->inventoryDetails_outstandingAmount != $articulo->inventoryDetails_quantity) {
                            $salidaCancelado = false;
                            $status = 400;
                            $message = 'Parcialmente pendiente. No se puede cancelar.';

                            return response()->json(['mensaje' => $message, 'estatus' => $status]);
                        }
                    }
                }

                $transito->inventories_status = $this->estatus[3];
                $validar = $transito->save();
                if ($validar) {
                    $salidaCancelado = true;
                } else {
                    $salidaCancelado = false;
                }


                $movimiento = PROC_MOVEMENT_FLOW::where('movementFlow_movementOrigin', '=', $folioAfectar->inventories_movement)->where('movementFlow_originID', '=', $folioAfectar->inventories_id)->where('movementFlow_movementDestinity', '=', $transito->inventories_movement)->where('movementFlow_destinityID', '=', $transito->inventories_id)->where('movementFlow_branch', '=', $folioAfectar->inventories_branchOffice)->where('movementFlow_moduleOrigin', '=', 'Inv')->first();

                $movimiento->movementFlow_cancelled = 1;
                $validar2 = $movimiento->save();
                if ($validar2) {
                    $salidaCancelado = true;
                } else {
                    $salidaCancelado = false;
                }


                //buscamos sus articulos
                $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->where('inventoryDetails_branchOffice', '=', $folioAfectar->inventories_branchOffice)->get();


                foreach ($articulos as $articulo) {


                    if ($articulo->inventoryDetails_type == 'Serie') {
                        $series = PROC_DEL_SERIES_MOV::where('delSeriesMov_inventoryID', '=', $articulo->inventoryDetails_inventoryID)->where('delSeriesMov_articleID', '=', $articulo->inventoryDetails_id)->get();

                        $sucursalOrigen = CAT_DEPOTS::where('depots_key', $folioAfectar->inventories_depot)->first();
                        // dd($series);
                        if (
                            $series != null
                        ) {
                            foreach ($series as $serie) {
                                $serie->delSeriesMov_cancelled = 1;
                                $serie->update();
                                // $serie->delete();
                                $lot_serie = PROC_LOT_SERIES::where('lotSeries_article', '=', $serie->delSeriesMov_article)->where('lotSeries_lotSerie', '=', $serie->delSeriesMov_lotSerie)->first();
                                if ($lot_serie != null) {
                                    $lot_serie->lotSeries_branchKey =  $sucursalOrigen->depots_branchlId;
                                    $lot_serie->lotSeries_depot = $folioAfectar->inventories_depot;
                                    $lot_serie->update();
                                }
                            }
                        }
                    }

                    //agregar datos a aux
                    $auxiliarU = new PROC_ASSISTANT_UNITS();
                    $auxiliarU->assistantUnit_companieKey = $folioAfectar->inventories_company;
                    $auxiliarU->assistantUnit_branchKey = $folioAfectar->inventories_branchOffice;
                    $auxiliarU->assistantUnit_branch = 'Inv';
                    $auxiliarU->assistantUnit_movement = $folioAfectar->inventories_movement;
                    $auxiliarU->assistantUnit_movementID = $folioAfectar->inventories_movementID;
                    $auxiliarU->assistantUnit_module = 'Inv';
                    $auxiliarU->assistantUnit_moduleID = $articulo->inventoryDetails_inventoryID;
                    $auxiliarU->assistantUnit_money = $folioAfectar->inventories_money;
                    $auxiliarU->assistantUnit_typeChange = $folioAfectar->inventories_typeChange;
                    $auxiliarU->assistantUnit_group =  $folioAfectar->inventories_depot;
                    $auxiliarU->assistantUnit_account = $articulo->inventoryDetails_article;
                    //ponemos fecha del ejercicio
                    $year = Carbon::now()->year;
                    //sacamos el periodo 
                    $period = Carbon::now()->month;
                    $auxiliarU->assistantUnit_year = $year;
                    $auxiliarU->assistantUnit_period = $period;
                    $auxiliarU->assistantUnit_charge = null;
                    $auxiliarU->assistantUnit_payment = "-" . $articulo->inventoryDetails_amount;
                    $auxiliarU->assistantUnit_chargeUnit = null;
                    $auxiliarU->assistantUnit_paymentUnit = -(float)$articulo->inventoryDetails_inventoryAmount;
                    $auxiliarU->assistantUnit_apply = $articulo->inventoryDetails_apply;
                    $auxiliarU->assistantUnit_applyID =  $articulo->inventoryDetails_applyIncrement;
                    $auxiliarU->assistantUnit_canceled = 1;
                    $validar3 = $auxiliarU->save();
                    if ($validar3) {
                        $salidaCancelado = true;
                    } else {
                        $salidaCancelado = false;
                    }


                    $cantidad = $articulo->inventoryDetails_inventoryAmount;
                    $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->inventoryDetails_article)->where('articlesInv_depot', '=', $folioAfectar->inventories_depot)->first();

                    $inventario == null ? $inventario = new PROC_ARTICLES_INV() : $inventario;
                    $inventario->articlesInv_depot = $folioAfectar->inventories_depot;
                    $inventario->articlesInv_branchKey = $folioAfectar->inventories_branchOffice;
                    $inventario->articlesInv_companieKey = $folioAfectar->inventories_company;
                    $inventario->articlesInv_inventory = ($inventario->articlesInv_inventory + $cantidad);
                    $inventario->articlesInv_article = $articulo->inventoryDetails_article;

                    $validar4 = $inventario->save();
                    if ($validar4) {
                        $salidaCancelado = true;
                    } else {
                        $salidaCancelado = false;
                    }
                    $inventario = null;
                }

                if ($salidaCancelado) {
                    $folioAfectar->inventories_status = $this->estatus[3];
                    $validar5 = $folioAfectar->save();
                    if ($validar5) {
                        $salidaCancelado = true;
                    } else {
                        $salidaCancelado = false;
                    }
                }
            } catch (\Exception $e) {
                dd($e);
                $salidaCancelado = false;
                $message = $e->getMessage();
            }
        }

        if ($folioAfectar->inventories_status == $this->estatus[2] && $folioAfectar->inventories_movement == 'Entrada por Traspaso') {


            $ajusteCancelado = true;
            $transferenciaCancelado = true;
            $salidaCancelado = true;
            $reciboCancelado = false;
            //buscamos sus articulos
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->where('inventoryDetails_branchOffice', '=', $folioAfectar->inventories_branchOffice)->get();


            foreach ($articulos as $articulo) {


                if ($articulo->inventoryDetails_type == 'Serie') {
                    $series = PROC_DEL_SERIES_MOV::where('delSeriesMov_inventoryID', '=', $articulo->inventoryDetails_inventoryID)->where('delSeriesMov_articleID', '=', $articulo->inventoryDetails_id)->get();

                    // dd($series);
                    if (
                        $series != null
                    ) {
                        foreach ($series as $serie) {
                            $serie->delSeriesMov_cancelled = 1;
                            $serie->update();
                            // $serie->delete();
                            $lot_serie = PROC_LOT_SERIES::where('lotSeries_article', '=', $serie->delSeriesMov_article)->where('lotSeries_lotSerie', '=', $serie->delSeriesMov_lotSerie)->first();
                            if ($lot_serie != null) {
                                $lot_serie->lotSeries_depot = $folioAfectar->inventories_depot;
                                $lot_serie->update();
                            }
                        }
                    }
                }
                //agregar datos a aux
                $auxiliarU = new PROC_ASSISTANT_UNITS();
                $auxiliarU->assistantUnit_companieKey = $folioAfectar->inventories_company;
                $auxiliarU->assistantUnit_branchKey = $folioAfectar->inventories_branchOffice;
                $auxiliarU->assistantUnit_branch = 'Inv';
                $auxiliarU->assistantUnit_movement = $folioAfectar->inventories_movement;
                $auxiliarU->assistantUnit_movementID = $folioAfectar->inventories_movementID;
                $auxiliarU->assistantUnit_module = 'Inv';
                $auxiliarU->assistantUnit_moduleID = $articulo->inventoryDetails_inventoryID;
                $auxiliarU->assistantUnit_money = $folioAfectar->inventories_money;
                $auxiliarU->assistantUnit_typeChange = $folioAfectar->inventories_typeChange;
                $auxiliarU->assistantUnit_group = $folioAfectar->inventories_depotDestiny;
                $auxiliarU->assistantUnit_account = $articulo->inventoryDetails_article;
                //ponemos fecha del ejercicio
                $year = Carbon::now()->year;
                //sacamos el periodo 
                $period = Carbon::now()->month;
                $auxiliarU->assistantUnit_year = $year;
                $auxiliarU->assistantUnit_period = $period;
                $auxiliarU->assistantUnit_charge = "-" . $articulo->inventoryDetails_amount;
                $auxiliarU->assistantUnit_payment = null;
                $auxiliarU->assistantUnit_chargeUnit = -(float)$articulo->inventoryDetails_inventoryAmount;
                $auxiliarU->assistantUnit_paymentUnit = null;
                $auxiliarU->assistantUnit_apply = $articulo->inventoryDetails_apply;
                $auxiliarU->assistantUnit_applyID =  $articulo->inventoryDetails_applyIncrement;
                $auxiliarU->assistantUnit_canceled = 1;
                $validar = $auxiliarU->save();
                if ($validar) {
                    $reciboCancelado = true;
                } else {
                    $reciboCancelado = false;
                }

                $cantidad = $articulo->inventoryDetails_inventoryAmount;
                $inventario = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articulo->inventoryDetails_article)->where('articlesInv_depot', '=', $folioAfectar->inventories_depotDestiny)->first();

                $inventario == null ? $inventario = new PROC_ARTICLES_INV() : $inventario;
                $inventario->articlesInv_depot = $folioAfectar->inventories_depotDestiny;
                $inventario->articlesInv_branchKey = $folioAfectar->inventories_branchOffice;
                $inventario->articlesInv_companieKey = $folioAfectar->inventories_company;
                $inventario->articlesInv_inventory = ($inventario->articlesInv_inventory - $cantidad);
                $inventario->articlesInv_article = $articulo->inventoryDetails_article;

                $validar2 = $inventario->save();
                if ($validar2) {
                    $reciboCancelado = true;
                } else {
                    $reciboCancelado = false;
                }
                $inventario = null;
            }

            $movOrigin = PROC_INVENTORIES::where('inventories_movementID', '=', $folioAfectar->inventories_originID)->where('inventories_movement', '=', $folioAfectar->inventories_origin)->where('inventories_company', '=', $folioAfectar->inventories_company)->where('inventories_branchOffice', '=', $folioAfectar->inventories_branchOffice)->first();

            //concluir origen
            if ($movOrigin != null) {

                //quitar el pendiente del detalle
                $detalle = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->get();
                // dd($movOrigin, $folioAfectar, $detalle);
                //buscamos el articulo origen con el referenceArticles
                foreach ($detalle as $detalle) {
                    $articulo = PROC_INVENTORIES_DETAILS::where('inventoryDetails_id', '=', $detalle->inventoryDetails_referenceArticles)->first();
                    $articulo->inventoryDetails_outstandingAmount = $articulo->inventoryDetails_outstandingAmount + $detalle->inventoryDetails_outstandingAmount;

                    $articulo->update();
                }

                //verificamos que todos los articulos de la cotizacion esten concluidos
                $detalle2 = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $movOrigin->inventories_id)->get();
                $concluido = false;
                foreach ($detalle2 as $detalle) {
                    if ($detalle->inventoryDetails_outstandingAmount != 0) {
                        $concluido = true;
                    }
                }

                //Concluimos la cotizacion si todos los articulos no tienen pendientes
                if ($concluido == true) {
                    $movOrigin->inventories_status = $this->estatus[1];
                    $movOrigin->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                    $validar3 = $movOrigin->update();
                    if ($validar3) {
                        $reciboCancelado = true;
                    } else {
                        $reciboCancelado = false;
                    }
                }
                // dd($detalle, $detalle2);

                $movimiento = PROC_MOVEMENT_FLOW::where('movementFlow_movementOrigin', '=', $movOrigin->inventories_movement)->where('movementFlow_originID', '=', $movOrigin->inventories_id)->where('movementFlow_movementDestinity', '=', $folioAfectar->inventories_movement)->where('movementFlow_destinityID', '=', $folioAfectar->inventories_id)->where('movementFlow_branch', '=', $folioAfectar->inventories_branchOffice)->where('movementFlow_moduleOrigin', '=', 'Inv')->first();

                $movimiento->movementFlow_cancelled = 1;
                $validar4 = $movimiento->update();
                if ($validar4) {
                    $reciboCancelado = true;
                } else {
                    $reciboCancelado = false;
                }
            }

            if ($reciboCancelado) {
                $folioAfectar->inventories_status = $this->estatus[3];
                $folioAfectar->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                $validar5 = $folioAfectar->save();
                if ($validar5) {
                    $reciboCancelado = true;
                } else {
                    $reciboCancelado = false;
                }
            }

            $this->quitarCostoPromedio($folioAfectar->inventories_id);
        }

        // dd($ajusteCancelado, $transferenciaCancelado, $salidaCancelado, $reciboCancelado);
        if ($ajusteCancelado == true && $transferenciaCancelado == true && $salidaCancelado == true && $reciboCancelado == true) {
            $status = 200;
            $message = 'Proceso cancelado correctamente';
        } else {
            $status = 500;
            $message = 'Error al cancelar el movimiento';
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status]);
    }

    public function quitarCostoPromedio($folio)
    {

        $folioAfectar = PROC_INVENTORIES::where('inventories_id', '=', $folio)->first();
        if ($folioAfectar->inventories_status == $this->estatus[3] && $folioAfectar->inventories_movement == 'Ajuste de Inventario') {
            $contador = 0;
            $articuloClave = [];
            //sacamos sus articulos
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->get();

            foreach ($articulos as $articulo) {
                $articuloClave[$contador] = $articulo->inventoryDetails_article;
                $contador++;
            }

            $cantidadAux = [];
            $cantidadAux2 = [];
            $cantidadInventario = [];



            foreach ($articuloClave as $key => $articulo) {
                $costoAuxArticulo = PROC_ASSISTANT_UNITS::where('assistantUnit_account', '=', $articulo)->where('assistantUnit_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('assistantUnit_companieKey', '=', $folioAfectar->inventories_company)->where('assistantUnit_group', '=', $folioAfectar->inventories_depot)->get()->sum('assistantUnit_charge');
                $cantidadAux[$articulo] = $costoAuxArticulo;

                $costoAuxArticulo2 = PROC_ASSISTANT_UNITS::where('assistantUnit_account', '=', $articulo)->where('assistantUnit_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('assistantUnit_companieKey', '=', $folioAfectar->inventories_company)->where('assistantUnit_group', '=', $folioAfectar->inventories_depot)->get()->sum('assistantUnit_payment');

                $cantidadArticulos = PROC_ARTICLES_INV::where('articlesInv_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesInv_article', '=', $articuloClave[$key])->where('articlesInv_depot', '=', $folioAfectar->inventories_depot)->get()->sum('articlesInv_inventory');
                $cantidadInventario[$articulo] = $cantidadArticulos;

                if ($costoAuxArticulo2 == null) {
                    $costoAuxArticulo2 = 0;
                }
                $cantidadAux2[$articulo] = $costoAuxArticulo2;
                //  dd($costoAuxArticulo);
            }



            foreach ($articulos as $articulo) {
                //agregamos costo promedio
                if ($cantidadInventario[$articulo->inventoryDetails_article] != 0) {
                    if ($articulo->inventoryDetails_quantity > 0) {


                        $costoPromedio = ($cantidadAux[$articulo->inventoryDetails_article] - $cantidadAux2[$articulo->inventoryDetails_article]) / $cantidadInventario[$articulo->inventoryDetails_article];



                        $articuloCostoH = new PROC_ARTICLES_COST_HIS();
                        //  dd($costoPromedio);

                        $articuloCostoH2 = PROC_ARTICLES_COST_HIS::where('articlesCostHis_article', '=', $articulo->inventoryDetails_article)->where('articlesCostHis_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesCostHis_companieKey', '=', $folioAfectar->inventories_company)->where('articlesCostHis_depotKey', '=', $folioAfectar->inventories_depot)->orderBy('created_at', 'desc')->first();



                        if ($articuloCostoH2 === null) {

                            $articuloCostoH->articlesCostHis_companieKey = $folioAfectar->inventories_company;
                            $articuloCostoH->articlesCostHis_branchKey = $folioAfectar->inventories_branchOffice;
                            $articuloCostoH->articlesCostHis_depotKey = $folioAfectar->inventories_depot;
                            $articuloCostoH->articlesCostHis_article = $articulo->inventoryDetails_article;
                            $articuloCostoH->articlesCostHis_lastCost = $articuloCostoH2 ? $articuloCostoH2->articlesCostHis_currentCost : null;
                            $articuloCostoH->articlesCostHis_currentCost =   $articuloCostoH->articlesCostHis_lastCost;
                            $articuloCostoH->articlesCostHis_averageCost =    $costoPromedio;
                            $articuloCostoH->created_at = date('Y-m-d H:i:s');
                            $articuloCostoH->save();
                        } else {

                            if ($articuloCostoH2->articlesCostHis_averageCost != $costoPromedio) {
                                $articuloCostoH->articlesCostHis_companieKey = $folioAfectar->inventories_company;
                                $articuloCostoH->articlesCostHis_branchKey = $folioAfectar->inventories_branchOffice;
                                $articuloCostoH->articlesCostHis_depotKey = $folioAfectar->inventories_depot;
                                $articuloCostoH->articlesCostHis_article = $articulo->inventoryDetails_article;
                                $articuloCostoH->articlesCostHis_lastCost = $articuloCostoH2 ? $articuloCostoH2->articlesCostHis_currentCost : null;
                                $articuloCostoH->articlesCostHis_currentCost =   $articuloCostoH->articlesCostHis_lastCost;
                                $articuloCostoH->articlesCostHis_averageCost =    $costoPromedio;
                                $articuloCostoH->created_at = date('Y-m-d H:i:s');
                                $articuloCostoH->save();
                            }
                        }
                        // dd($articuloCosto2);

                        //agregamos costo promedio

                        $articuloCosto = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articulo->inventoryDetails_article)->where('articlesCost_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesCost_companieKey', '=', $folioAfectar->inventories_company)->where('articlesCost_depotKey', '=', $folioAfectar->inventories_depot)->first();


                        $articuloReferencia = PROC_ARTICLES_COST_HIS::where('articlesCostHis_article', '=', $articulo->inventoryDetails_article)->where('articlesCostHis_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesCostHis_companieKey', '=', $folioAfectar->inventories_company)->where('articlesCostHis_depotKey', '=', $folioAfectar->inventories_depot)->orderBy('created_at', 'desc')->first();

                        if ($articuloCosto == null) {
                            $articuloCosto = new PROC_ARTICLES_COST();
                            $articuloCosto->articlesCost_companieKey = $folioAfectar->inventories_company;
                            $articuloCosto->articlesCost_branchKey = $folioAfectar->inventories_branchOffice;
                            $articuloCosto->articlesCost_depotKey = $folioAfectar->inventories_depot;
                            $articuloCosto->articlesCost_article = $articulo->inventoryDetails_article;
                            $articuloCosto->articlesCost_lastCost = $articuloReferencia ? $articuloReferencia->articlesCostHis_currentCost : null;
                            $articuloCosto->articlesCost_averageCost =  $articuloReferencia->articlesCostHis_averageCost;
                            $articuloCosto->save();
                        } else {
                            $articuloCosto->articlesCost_companieKey = $folioAfectar->inventories_company;
                            $articuloCosto->articlesCost_branchKey = $folioAfectar->inventories_branchOffice;
                            $articuloCosto->articlesCost_depotKey = $folioAfectar->inventories_depot;
                            $articuloCosto->articlesCost_article = $articulo->inventoryDetails_article;
                            $articuloCosto->articlesCost_lastCost = $articuloReferencia ? $articuloReferencia->articlesCostHis_currentCost : null;
                            $articuloCosto->articlesCost_averageCost =  $articuloReferencia->articlesCostHis_averageCost;
                            $articuloCosto->update();
                        }
                    }
                }
            }
        }

        if ($folioAfectar->inventories_status == $this->estatus[3] && $folioAfectar->inventories_movement == 'Entrada por Traspaso') {
            $contador = 0;
            $articuloClave = [];
            //sacamos sus articulos
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->get();

            foreach ($articulos as $articulo) {
                $articuloClave[$contador] = $articulo->inventoryDetails_article;
                $contador++;
            }

            $cantidadAux = [];
            $cantidadAux2 = [];
            $cantidadInventario = [];



            foreach ($articuloClave as $key => $articulo) {
                $costoAuxArticulo = PROC_ASSISTANT_UNITS::where('assistantUnit_account', '=', $articulo)->where('assistantUnit_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('assistantUnit_companieKey', '=', $folioAfectar->inventories_company)->where('assistantUnit_group', '=', $folioAfectar->inventories_depot)->get()->sum('assistantUnit_charge');
                $cantidadAux[$articulo] = $costoAuxArticulo;

                $costoAuxArticulo2 = PROC_ASSISTANT_UNITS::where('assistantUnit_account', '=', $articulo)->where('assistantUnit_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('assistantUnit_companieKey', '=', $folioAfectar->inventories_company)->where('assistantUnit_group', '=', $folioAfectar->inventories_depot)->get()->sum('assistantUnit_payment');

                $cantidadArticulos = PROC_ARTICLES_INV::where('articlesInv_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesInv_article', '=', $articuloClave[$key])->where('articlesInv_depot', '=', $folioAfectar->inventories_depot)->get()->sum('articlesInv_inventory');
                $cantidadInventario[$articulo] = $cantidadArticulos;

                if ($costoAuxArticulo2 == null) {
                    $costoAuxArticulo2 = 0;
                }
                $cantidadAux2[$articulo] = $costoAuxArticulo2;
                //  dd($costoAuxArticulo);
            }

            foreach ($articulos as $articulo) {
                //agregamos costo promedio
                if ($cantidadInventario[$articulo->inventoryDetails_article] != 0) {

                    if ($articulo->inventoryDetails_quantity > 0) {

                        $costoPromedio = ($cantidadAux[$articulo->inventoryDetails_article] - $cantidadAux2[$articulo->inventoryDetails_article]) / $cantidadInventario[$articulo->inventoryDetails_article];


                        $articuloCostoH = new PROC_ARTICLES_COST_HIS();
                        //  dd($costoPromedio);

                        $articuloCostoH2 = PROC_ARTICLES_COST_HIS::where('articlesCostHis_article', '=', $articulo->inventoryDetails_article)->where('articlesCostHis_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesCostHis_companieKey', '=', $folioAfectar->inventories_company)->where('articlesCostHis_depotKey', '=', $folioAfectar->inventories_depot)->orderBy('created_at', 'desc')->first();



                        if ($articuloCostoH2 === null) {

                            $articuloCostoH->articlesCostHis_companieKey = $folioAfectar->inventories_company;
                            $articuloCostoH->articlesCostHis_branchKey = $folioAfectar->inventories_branchOffice;
                            $articuloCostoH->articlesCostHis_depotKey = $folioAfectar->inventories_depot;
                            $articuloCostoH->articlesCostHis_article = $articulo->inventoryDetails_article;
                            $articuloCostoH->articlesCostHis_lastCost = $articuloCostoH2 ? $articuloCostoH2->articlesCostHis_currentCost : null;
                            $articuloCostoH->articlesCostHis_currentCost =   $articuloCostoH->articlesCostHis_lastCost;
                            $articuloCostoH->articlesCostHis_averageCost =    $costoPromedio;
                            $articuloCostoH->created_at = date('Y-m-d H:i:s');
                            $articuloCostoH->save();
                        } else {

                            if ($articuloCostoH2->articlesCostHis_averageCost != $costoPromedio) {
                                $articuloCostoH->articlesCostHis_companieKey = $folioAfectar->inventories_company;
                                $articuloCostoH->articlesCostHis_branchKey = $folioAfectar->inventories_branchOffice;
                                $articuloCostoH->articlesCostHis_depotKey = $folioAfectar->inventories_depot;
                                $articuloCostoH->articlesCostHis_article = $articulo->inventoryDetails_article;
                                $articuloCostoH->articlesCostHis_lastCost = $articuloCostoH2 ? $articuloCostoH2->articlesCostHis_currentCost : null;
                                $articuloCostoH->articlesCostHis_currentCost =  $articuloCostoH->articlesCostHis_lastCost;
                                $articuloCostoH->articlesCostHis_averageCost =    $costoPromedio;
                                $articuloCostoH->created_at = date('Y-m-d H:i:s');
                                $articuloCostoH->save();
                            }
                        }
                        // dd($articuloCosto2);

                        //agregamos costo promedio

                        $articuloCosto = PROC_ARTICLES_COST::where('articlesCost_article', '=', $articulo->inventoryDetails_article)->where('articlesCost_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesCost_companieKey', '=', $folioAfectar->inventories_company)->where('articlesCost_depotKey', '=', $folioAfectar->inventories_depot)->first();


                        $articuloReferencia = PROC_ARTICLES_COST_HIS::where('articlesCostHis_article', '=', $articulo->inventoryDetails_article)->where('articlesCostHis_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesCostHis_companieKey', '=', $folioAfectar->inventories_company)->where('articlesCostHis_depotKey', '=', $folioAfectar->inventories_depot)->orderBy('created_at', 'desc')->first();

                        if ($articuloCosto == null) {
                            $articuloCosto = new PROC_ARTICLES_COST();
                            $articuloCosto->articlesCost_companieKey = $folioAfectar->inventories_company;
                            $articuloCosto->articlesCost_branchKey = $folioAfectar->inventories_branchOffice;
                            $articuloCosto->articlesCost_depotKey = $folioAfectar->inventories_depot;
                            $articuloCosto->articlesCost_article = $articulo->inventoryDetails_article;
                            $articuloCosto->articlesCost_lastCost = $articuloReferencia ? $articuloReferencia->articlesCostHis_currentCost : null;
                            $articuloCosto->articlesCost_averageCost =  $articuloReferencia->articlesCostHis_averageCost;
                            $articuloCosto->save();
                        } else {
                            $articuloCosto->articlesCost_companieKey = $folioAfectar->inventories_company;
                            $articuloCosto->articlesCost_branchKey = $folioAfectar->inventories_branchOffice;
                            $articuloCosto->articlesCost_depotKey = $folioAfectar->inventories_depot;
                            $articuloCosto->articlesCost_article = $articulo->inventoryDetails_article;
                            $articuloCosto->articlesCost_lastCost = $articuloReferencia ? $articuloReferencia->articlesCostHis_currentCost : null;
                            $articuloCosto->articlesCost_averageCost =  $articuloReferencia->articlesCostHis_averageCost;
                            $articuloCosto->update();
                        }
                    }
                }
            }
        }
    }

    public function getArticulosSerie(Request $request)
    {
        $limit = abs($request->limit);
        $articuloSerie = PROC_LOT_SERIES_MOV2::where('lotSeriesMov2_articleID', '=', $request->id)->where('lotSeriesMov2_companieKey', '=', session('company')->companies_key)->where('lotSeriesMov2_branchKey', '=', session('sucursal')->branchOffices_key)->where('lotSeriesMov2_inventoryID', '=', $request->idCompra)->where('lotSeriesMov2_article', '=', $request->claveArticulo)->limit($limit)->get()->unique('lotSeriesMov2_lotSerie');


        return response()->json(['status' => 200, 'data' => $articuloSerie]);
    }

    public function getSeries(Request $request)
    {
        $series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', session('company')->companies_key)->where('lotSeries_branchKey', '=', session('sucursal')->branchOffices_key)->where('lotSeries_depot', '=', $request->almacen)->where('lotSeries_article', '=', $request->claveArticulo)->where('lotSeries_delete', '=', 0)->get();

        return response()->json(['status' => 200, 'data' => $series]);
    }

    public function getSeriesGuardados(Request $request)
    {

        $inventario = PROC_INVENTORIES::where('inventories_id', '=', $request->idCompra)->first();

        if ($inventario->inventories_movement !== 'Entrada por Traspaso') {
            $series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', session('company')->companies_key)->where('lotSeries_branchKey', '=', session('sucursal')->branchOffices_key)->where('lotSeries_depot', '=', $request->almacen)->where('lotSeries_article', '=', $request->claveArticulo)->where('lotSeries_delete', '=', 0)->get();

            $series_seleccionados = PROC_DEL_SERIES_MOV::where('delSeriesMov_companieKey', '=', session('company')->companies_key)->where('delSeriesMov_branchKey', '=', session('sucursal')->branchOffices_key)->where('delSeriesMov_inventoryID', '=', $request->idCompra)->where('delSeriesMov_article', '=', $request->claveArticulo)->get();
        } else {

            $series = PROC_LOT_SERIES::where('lotSeries_companieKey', '=', session('company')->companies_key)->where('lotSeries_branchKey', '=', session('sucursal')->branchOffices_key)->where('lotSeries_depot', '=', $request->almacen)->where('lotSeries_article', '=', $request->claveArticulo)->where('lotSeries_delete', '=', 0)->get();

            $series_seleccionados = PROC_DEL_SERIES_MOV::where('delSeriesMov_companieKey', '=', $inventario->inventories_company)->where('delSeriesMov_branchKey', '=', $inventario->inventories_branchOffice)->where('delSeriesMov_inventoryID', '=', $request->idCompra)->where('delSeriesMov_article', '=', $request->claveArticulo)->get();
        }

        return response()->json(['status' => 200, 'data' => $series, 'data2' => $series_seleccionados]);
    }


    public function getReporteInventario($id)
    {

        $inventario = PROC_INVENTORIES::join('CAT_BRANCH_OFFICES', 'PROC_INVENTORIES.inventories_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CAT_COMPANIES', 'PROC_INVENTORIES.inventories_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('CAT_DEPOTS', 'PROC_INVENTORIES.inventories_depot', '=', 'CAT_DEPOTS.depots_key')
            ->join('CAT_DEPOTS as CAT_DEPOTS2', 'PROC_INVENTORIES.inventories_depotDestiny', '=', 'CAT_DEPOTS2.depots_key', 'left')
            ->where('inventories_id', '=', $id)
            ->select('PROC_INVENTORIES.*', 'CAT_BRANCH_OFFICES.branchOffices_name', 'CAT_COMPANIES.companies_name', 'CAT_COMPANIES.companies_rfc', 'CAT_COMPANIES.companies_logo', 'CAT_DEPOTS.depots_name', 'CAT_DEPOTS2.depots_name as depots_nameDestiny')
            ->first();

        //  dd($inventario);

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

        $articulos = PROC_INVENTORIES_DETAILS::join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_INVENTORIES_DETAILS.inventoryDetails_unit')
            ->where('inventoryDetails_inventoryID', '=', $id)
            ->get();

        $pdf = PDF::loadview('reportes.inventarios-reporte', ['inventario' => $inventario, 'articulos' => $articulos, 'logo' => $logoBase64]);
        $pdf->set_paper('a4', 'landscape');

        return $pdf->stream();
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

    public function verificacionAlmacenDestino($request)
    {
        $almacenValid = false;
        $articulos = $request->dataArticulosJson;
        $articulos = json_decode($articulos, true);
        $claveArt = array_keys($articulos);

        foreach ($claveArt as $clave) {
            if ($articulos[$clave]['tipoArticulo'] === "Serie") {
                //validar el almacen sea de activo fijo
                if ($request['almacenTipoDestinoKey'] !== "Activo Fijo") {
                    $almacenValid = true;
                    break;
                }
            }
        }
        return $almacenValid;
    }

    public function inventariosAction(Request $request)
    {

        $nameFolio = $request->nameFolio;
        $nameMov = $request->nameMov;
        $status = $request->status;
        $nameFecha = $request->nameFecha;
        $nameUsuario = $request->nameUsuario;
        $nameSucursal = $request->nameSucursal;

        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;

        if ($fechaInicio !== null && $fechaFinal !== null && $nameFecha === "Rango Fechas") {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }

        switch ($request->input('action')) {
            case 'Búsqueda':
                $inventarios_collection_filtro = PROC_INVENTORIES::join('CAT_BRANCH_OFFICES', 'PROC_INVENTORIES.inventories_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CAT_COMPANIES', 'PROC_INVENTORIES.inventories_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('CAT_DEPOTS', 'PROC_INVENTORIES.inventories_depot', '=', 'CAT_DEPOTS.depots_key')
                    ->join('CAT_DEPOTS as CAT_DEPOTS2', 'PROC_INVENTORIES.inventories_depotDestiny', '=', 'CAT_DEPOTS2.depots_key', 'left')
                    ->select('PROC_INVENTORIES.*', 'CAT_BRANCH_OFFICES.branchOffices_name', 'CAT_COMPANIES.companies_name', 'CAT_DEPOTS.depots_name', 'CAT_DEPOTS2.depots_name as depots_nameDestiny')
                    ->where('PROC_INVENTORIES.inventories_company', '=', session('company')->companies_key)
                    ->whereInventoriesMovementID($nameFolio)
                    ->whereInventoriesMovement($nameMov)
                    ->whereInventoriesStatus($status)
                    ->whereInventoriesDate($nameFecha)
                    ->whereInventoriesUser($nameUsuario)
                    ->whereInventoriesbranchOffice($nameSucursal)
                    ->orderBy('PROC_INVENTORIES.created_at', 'desc')
                    ->get();

                $inventarios_filtro_array = $inventarios_collection_filtro->toArray();

                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.modulo.inventarios')->with('inventarios_filtro_array', $inventarios_filtro_array)
                    ->with('nameFolio', $nameFolio)
                    ->with('nameMov', $nameMov)
                    ->with('status', $status)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameUsuario', $nameUsuario)
                    ->with('nameSucursal', $nameSucursal)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal);
                break;

            case 'Exportar excel':
                $inventario = new PROC_InventariosExport($nameFolio, $nameMov, $status, $nameFecha, $nameUsuario, $nameSucursal);
                return Excel::download($inventario, 'Inventarios.xlsx');

                break;

            default:
                break;
        }
    }

    public function getCostoPromedio(Request $request)
    {

        $inventario = PROC_INVENTORIES::where('inventories_id', '=', $request->idInventario)->first();



        $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $request->id)->join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->join('PROC_ARTICLES_COST', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_COST.articlesCost_article')->join('CONF_UNITS', 'CAT_ARTICLES.articles_unitBuy', '=', 'CONF_UNITS.units_id')->join('CAT_DEPOTS', 'PROC_ARTICLES_COST.articlesCost_depotKey', '=', 'CAT_DEPOTS.depots_key')->where('articlesCost_branchKey', '=', session('sucursal')->branchOffices_key)->where('articlesCost_depotKey', '=', $inventario->inventories_depot)->where('PROC_ARTICLES_INV.articlesInv_branchKey', '=', session('sucursal')->branchOffices_key)->where('PROC_ARTICLES_INV.articlesInv_depot', '=', $inventario->inventories_depot)->first();

        $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $request->id)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->get();

        if ($infoArticulo == null) {
            $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $request->id)->first();
            $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $request->id)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->get();
        }
        if ($inventario->inventories_movement === 'Entrada por Traspaso') {

            $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $request->id)->join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->join('PROC_ARTICLES_COST', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_COST.articlesCost_article')->join('CONF_UNITS', 'CAT_ARTICLES.articles_unitBuy', '=', 'CONF_UNITS.units_id')->join('CAT_DEPOTS', 'PROC_ARTICLES_COST.articlesCost_depotKey', '=', 'CAT_DEPOTS.depots_key')->where('articlesCost_branchKey', '=', $inventario->inventories_branchOffice)->where('articlesCost_depotKey', '=', $inventario->inventories_depotDestiny)->where('PROC_ARTICLES_INV.articlesInv_branchKey', '=', $inventario->inventories_branchOffice)->where('PROC_ARTICLES_INV.articlesInv_depot', '=', $inventario->inventories_depotDestiny)->first();

            $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $request->id)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->get();

            if ($infoArticulo == null) {
                $infoArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $request->id)->first();
                $articulosByAlmacen = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('PROC_ARTICLES_INV.articlesInv_article', '=', $request->id)->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key')->where('PROC_ARTICLES_INV.articlesInv_companieKey', '=', session('company')->companies_key)->get();
            }
        }



        if ($infoArticulo != null && $articulosByAlmacen != null) {
            $status = 200;
        } else {
            $status = 404;
        }

        return response()->json(['data' => $infoArticulo, 'estatus' => $status, 'articulosByAlmacen' => $articulosByAlmacen]);
    }

    public function getAlmacenesDestino(Request $request)
    {


        if ($request->movimiento == "Transferencia entre Alm.") {
            $almacenes = CAT_DEPOTS::join('CAT_BRANCH_OFFICES', 'CAT_DEPOTS.depots_branchlId', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                ->where('branchOffices_companyId', '=', session('company')->companies_key)->where('CAT_BRANCH_OFFICES.branchOffices_key', '=', session('sucursal')->branchOffices_key)->where('CAT_DEPOTS.depots_status', '=', 'Alta')->get();
        }

        if ($request->movimiento == "Salida por Traspaso" || $request->movimiento == "Entrada por Traspaso" || $request->movimiento == "Tránsito") {
            $almacenes = CAT_DEPOTS::join('CAT_BRANCH_OFFICES', 'CAT_DEPOTS.depots_branchlId', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                ->where('CAT_BRANCH_OFFICES.branchOffices_key', '!=', session('sucursal')->branchOffices_key)->where('CAT_DEPOTS.depots_status', '=', 'Alta')->where('branchOffices_companyId', '=', session('company')->companies_key)->get();
        }

        if ($almacenes != null) {
            $status = 200;
        } else {
            $status = 404;
        }

        return response()->json(['data' => $almacenes, 'estatus' => $status]);
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
        $monedas = [];
        $monedas_collection = CONF_MONEY::where('money_status', '=', 'Alta')->get();
        $monedas_array = $monedas_collection->toArray();

        foreach ($monedas_array as $key => $value) {
            $monedas[trim($value['money_key'])] = $value['money_name'];
        };
        return $monedas;
    }

    public function getTipoCambio(Request $request)
    {

        $tipoCambio = CONF_MONEY::where('money_key', '=', $request->tipoCambio)->first();

        return response()->json($tipoCambio);
    }

    public function getCostoUnitario(Request $request)
    {

        $costoArticulo = CAT_ARTICLES::where('articles_key', '=', $request->articulo)->first();

        return response()->json($costoArticulo);
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
        $proveedor = CAT_PROVIDERS::where('providers_key', '=', $request->proveedor)->first();
        return response()->json($proveedor);
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

    //Obtenemos las multiunidades
    public function getCosto(Request $request)
    {

        $costo = PROC_ARTICLES_COST::where('articlesCost_article', '=', $request->articulo)->where('articlesCost_depotKey', '=', $request->almacen)->where('articlesCost_companieKey', '=', session('company')->companies_key)->where('articlesCost_branchKey', '=', session('sucursal')->branchOffices_key)->first();

        return response()->json($costo);
    }


    public function articulosInventarioDepot(Request $request)
    {
        $articulosUnidad = [];
        $articulosInvUnidad = [];
        $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*')->get()->toArray();
        $unidad = $this->getConfUnidades();

        $articulos = CAT_ARTICLES::WHERE('articles_status', '=', 'Alta')->WHEREIN('articles_type', ['Normal', 'Serie'])->select('CAT_ARTICLES.*')->get()->toArray();

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
            $articuloJson->articles_key = $articulo["articles_key"];
            $articuloJson->articles_descript = $articulo["articles_descript"];
            $articuloJson->articles_porcentIva = $articulo["articles_porcentIva"];
            $articuloJson->articles_unitSale = $unidad[$articulo["articles_unitSale"]];
            $articuloJson->articles_type =  $articulo["articles_type"];
            $articuloJson->articles_transfer = $unidad[$articulo["articles_transfer"]];

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

    public function articulosInventarioExistencia(Request $request)
    {
        $articulosUnidad = [];
        $articulosInvUnidad = [];
        $checkExistencia = $request->checkArticulosExistentes;
        // dd($checkExistencia);


        if ($checkExistencia == "true") {
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)->WHEREIN('articles_type', ['Normal', 'Serie'])->WHERE('articlesInv_inventory', '>', 0)
                ->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*')->get()->toArray();
            $unidad = $this->getConfUnidades();

            if (count($articulosInv) > 0) {
                foreach ($articulosInv as $articulo) {
                    $articulosInvUnidad[$articulo['articlesInv_article']] = $articulo;
                }
            }
        } else {
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*')->get()->toArray();
            $unidad = $this->getConfUnidades();

            $articulos = CAT_ARTICLES::WHERE('articles_status', '=', 'Alta')->WHEREIN('articles_type', ['Normal', 'Serie'])->select('CAT_ARTICLES.*')->get()->toArray();

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
            $articuloJson->articles_key = $articulo["articles_key"];
            $articuloJson->articles_descript = $articulo["articles_descript"];
            $articuloJson->articles_porcentIva = $articulo["articles_porcentIva"];
            $articuloJson->articles_unitSale = $unidad[$articulo["articles_unitSale"]];
            $articuloJson->articles_type =  $articulo["articles_type"];
            $articuloJson->articles_transfer = $unidad[$articulo["articles_transfer"]];

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

    public function articulosInventarioCategoria(Request $request)
    {
        $articulosUnidad = [];
        $articulosInvUnidad = [];
        $categoria = $request->categoria;
        // dd($checkExistencia);


        if ($categoria !== '') {
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)->WHEREIN('articles_type', ['Normal', 'Serie'])->WHERE('articles_category', '=', $categoria)
                ->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*')->get()->toArray();
            $unidad = $this->getConfUnidades();

            if (count($articulosInv) > 0) {
                foreach ($articulosInv as $articulo) {
                    $articulosInvUnidad[$articulo['articlesInv_article']] = $articulo;
                }
            }
        } else {
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*')->get()->toArray();
            $unidad = $this->getConfUnidades();

            $articulos = CAT_ARTICLES::WHERE('articles_status', '=', 'Alta')->WHEREIN('articles_type', ['Normal', 'Serie'])->select('CAT_ARTICLES.*')->get()->toArray();

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
            $articuloJson->articles_key = $articulo["articles_key"];
            $articuloJson->articles_descript = $articulo["articles_descript"];
            $articuloJson->articles_porcentIva = $articulo["articles_porcentIva"];
            $articuloJson->articles_unitSale = $unidad[$articulo["articles_unitSale"]];
            $articuloJson->articles_type =  $articulo["articles_type"];
            $articuloJson->articles_transfer = $unidad[$articulo["articles_transfer"]];

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

    public function articulosInventarioFamilia(Request $request)
    {
        $articulosUnidad = [];
        $articulosInvUnidad = [];
        $familia = $request->familia;
        // dd($checkExistencia);


        if ($familia !== '') {
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)->WHEREIN('articles_type', ['Normal', 'Serie'])->WHERE('articles_family', '=', $familia)
                ->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*')->get()->toArray();
            $unidad = $this->getConfUnidades();

            if (count($articulosInv) > 0) {
                foreach ($articulosInv as $articulo) {
                    $articulosInvUnidad[$articulo['articlesInv_article']] = $articulo;
                }
            }
        } else {
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*')->get()->toArray();
            $unidad = $this->getConfUnidades();

            $articulos = CAT_ARTICLES::WHERE('articles_status', '=', 'Alta')->WHEREIN('articles_type', ['Normal', 'Serie'])->select('CAT_ARTICLES.*')->get()->toArray();

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
            $articuloJson->articles_key = $articulo["articles_key"];
            $articuloJson->articles_descript = $articulo["articles_descript"];
            $articuloJson->articles_porcentIva = $articulo["articles_porcentIva"];
            $articuloJson->articles_unitSale = $unidad[$articulo["articles_unitSale"]];
            $articuloJson->articles_type =  $articulo["articles_type"];
            $articuloJson->articles_transfer = $unidad[$articulo["articles_transfer"]];

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

    public function articulosInventarioGrupo(Request $request)
    {
        $articulosUnidad = [];
        $articulosInvUnidad = [];
        $grupo = $request->grupo;
        // dd($checkExistencia);


        if ($grupo !== '') {
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)->WHEREIN('articles_type', ['Normal', 'Serie'])->WHERE('articles_group', '=', $grupo)
                ->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*')->get()->toArray();
            $unidad = $this->getConfUnidades();

            if (count($articulosInv) > 0) {
                foreach ($articulosInv as $articulo) {
                    $articulosInvUnidad[$articulo['articlesInv_article']] = $articulo;
                }
            }
        } else {
            $articulosInv = CAT_ARTICLES::leftjoin('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')->WHERE('articles_status', '=', 'Alta')->where("PROC_ARTICLES_INV.articlesInv_depot", "=", $request->depot)->select('CAT_ARTICLES.*', 'PROC_ARTICLES_INV.*')->get()->toArray();
            $unidad = $this->getConfUnidades();

            $articulos = CAT_ARTICLES::WHERE('articles_status', '=', 'Alta')->WHEREIN('articles_type', ['Normal', 'Serie'])->select('CAT_ARTICLES.*')->get()->toArray();

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
            $articuloJson->articles_key = $articulo["articles_key"];
            $articuloJson->articles_descript = $articulo["articles_descript"];
            $articuloJson->articles_porcentIva = $articulo["articles_porcentIva"];
            $articuloJson->articles_unitSale = $unidad[$articulo["articles_unitSale"]];
            $articuloJson->articles_type =  $articulo["articles_type"];
            $articuloJson->articles_transfer = $unidad[$articulo["articles_transfer"]];

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

    public function getConceptosByMovimiento(Request $request)
    {
        $movimientoSeleccionado = $request->input('movimiento');
        // dd($movimientoSeleccionado);
        if ($movimientoSeleccionado === null) {
            $conceptos = CONF_MODULES_CONCEPT::where('moduleConcept_status', '=', 'Alta')
                ->where('moduleConcept_module', '=', 'Inventarios')
                ->get();
        } else {
            $conceptos = CONF_MODULES_CONCEPT::join('CONF_MODULES_CONCEPT_MOVEMENT', 'CONF_MODULES_CONCEPT_MOVEMENT.moduleMovement_conceptID', '=', 'CONF_MODULES_CONCEPT.moduleConcept_id')
                ->where('moduleConcept_status', '=', 'Alta')
                ->where('moduleMovement_movementName', '=', $movimientoSeleccionado)
                ->get();
        }
        return response()->json($conceptos);
    }

    public function actualizarFolio($tipoMovimiento, $folioAfectar)
    {
        switch ($tipoMovimiento) {
            case 'Ajuste de Inventario':
                $consecutivoColumn = 'generalConsecutives_consAdjustment';
                break;
            case 'Transferencia entre Alm.':
                $consecutivoColumn = 'generalConsecutives_consTransfer';
                break;
            case 'Salida por Traspaso':
                $folioSalidaTraspaso = PROC_INVENTORIES::where('inventories_movement', '=', 'Salida por Traspaso')->where('inventories_branchOffice', '=', $folioAfectar->inventories_branchOffice)->max('inventories_movementID');
                $folioSalidaTraspaso = $folioSalidaTraspaso == null ? 1 : $folioSalidaTraspaso + 1;
                $folioAfectar->inventories_movementID = $folioSalidaTraspaso;
                //origen del movimiento
                $folioAfectar->inventories_originType = 'Usuario';
                $folioAfectar->inventories_origin = Auth::user()->username;
                $folioAfectar->inventories_originID = null;
                $folioAfectar->update();
                break;

            case 'Tránsito':
                $folioTransito = PROC_INVENTORIES::where('inventories_movement', '=', 'Tránsito')->where('inventories_branchOffice', '=', $folioAfectar->inventories_branchOffice)->max('inventories_movementID');
                $folioTransito = $folioTransito == null ? 1 : $folioTransito + 1;
                $folioAfectar->inventories_movementID = $folioTransito;
                //origen del movimiento
                $folioAfectar->update();
                break;

            case 'Entrada por Traspaso':
                $folioReciboTraspaso = PROC_INVENTORIES::where('inventories_movement', '=', 'Entrada por Traspaso')->where('inventories_branchOffice', '=', $folioAfectar->inventories_branchOffice)->max('inventories_movementID');
                $folioReciboTraspaso = $folioReciboTraspaso == null ? 1 : $folioReciboTraspaso + 1;
                $folioAfectar->inventories_movementID = $folioReciboTraspaso;
                //origen del movimiento
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
                $folioOrden = PROC_INVENTORIES::where('inventories_movement', '=', $tipoMovimiento)
                    ->where('inventories_branchOffice', '=', $folioAfectar->inventories_branchOffice)
                    ->max('inventories_movementID');
                $folioOrden = $folioOrden == null ? 1 : $folioOrden + 1;
                $folioAfectar->inventories_movementID = $folioOrden;
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
            } else {
                // Utiliza el valor incrementado del consecutivo en tu lógica
                DB::table('CONF_GENERAL_PARAMETERS_CONSECUTIVES')
                    ->where('generalConsecutives_company', session('company')->companies_key)
                    ->where('generalConsecutives_branchOffice', session('sucursal')->branchOffices_key)
                    ->update([$consecutivoColumn => $consecutivo + 1]);

                $folioAfectar->inventories_movementID = $consecutivo + 1;
                $folioAfectar->update();
            }
        }
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