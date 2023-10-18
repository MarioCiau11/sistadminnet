<?php

namespace App\Http\Controllers\erpNet\herramienta;

use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_COMPANIES;
use App\Models\catalogos\CAT_DEPOTS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\modulos\helpers\PROC_ARTICLES_COST;
use App\Models\modulos\helpers\PROC_ARTICLES_COST_HIS;
use App\Models\modulos\PROC_ARTICLES_INV;
use App\Models\modulos\PROC_ASSISTANT_UNITS;
use App\Models\modulos\PROC_INVENTORIES;
use App\Models\modulos\PROC_INVENTORIES_DETAILS;
use App\Models\modulos\PROC_PURCHASE;
use App\Models\modulos\PROC_PURCHASE_DETAILS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class herramientaController extends Controller
{
    public function index()
    {
        $empresas = $this->getEmpresas();
        $empresa = session('company');
        $sucursalSession = session('sucursal');
        $sucursales_collection = CAT_BRANCH_OFFICES::where('branchOffices_companyId', $empresa->companies_key)->where('branchOffices_status', 'Alta')->get();
        $almacenes_collection = CAT_DEPOTS::where('depots_branchlId', $sucursalSession->branchOffices_key)->where('depots_status', 'Alta')->get();

        $sucursalesDestino = CAT_BRANCH_OFFICES::where('branchOffices_status', 'Alta')->get();


        $almacenes = array();
        foreach ($almacenes_collection as $clave => $val) {
            $almacenes[$val->depots_key] = $val->depots_name;
        }
        //armar arreglo de sucursales
        $sucursales = array();
        foreach ($sucursales_collection as $key => $sucursal) {
            $sucursales[$sucursal->branchOffices_key] = $sucursal['branchOffices_name'];
        }
        // dd($sucursales, $sucursal);
        return view('page.herramienta.herramienta')->with(compact('empresa', 'empresas', 'sucursalSession', 'sucursales', 'almacenes', 'sucursalesDestino'));
    }

    public function store(Request $request)
    {

        try {


            //   dd($request->all());
            //validar que tenga proveedor de referencia
            $empresa = CAT_COMPANIES::where('companies_key', $request->empresas)->first();

            if ($empresa->companies_referenceProvider === null) {
                $status = 500;
                $message = 'Falta captura proveedor referencia en la empresa: ' . $empresa->companies_key;
                return response()->json(['mensaje' => $message, 'estatus' => $status]);
            }


            $articulos = $request['inputDataArticles'];
            $articulos = json_decode($articulos, true);
            $claveArt = array_keys($articulos);

            $moneda = CONF_MONEY::where('money_key', session('generalParameters')->generalParameters_defaultMoney)->first();

            foreach ($claveArt as $key => $articulo) {
                $articuloInd = explode('-', $articulo);


                //validamos que haya stock para el articulo
                $validar = PROC_ARTICLES_INV::where('articlesInv_article', '=', $articuloInd)->where('articlesInv_depot', '=', $request['almacenes'])->first();

                if ($validar == null) {
                    $procesar = false;
                    $message = 'No hay stock para el articulo ' . $articulos[$articulo]['descripcion'] . ' en el almacen ' . $request['almacenes'];
                    $status = 500;


                    return response()->json(['mensaje' => $message, 'estatus' => $status]);
                }

                $articuloVenta = (float) $articulos[$articulo]['cantidad'];
                $disponible = (float) $validar->articlesInv_inventory;

                if ($articuloVenta > $disponible) {
                    $message = 'No hay stock para el articulo ' . $articulos[$articulo]['descripcion'] . ' en el almacen ' . $request['almacenes'];
                    $status = 500;
                    return response()->json(['mensaje' => $message, 'estatus' => $status]);
                }
            }

            // dd($articuloInd, $validar, $request->all());

            if ($request->almacenes === $request->almacenDestino) {
                $message = 'El almacen de origen y destino no pueden ser iguales';
                $status = 500;
                return response()->json(['mensaje' => $message, 'estatus' => $status]);
            }

            //Encontramos la empresa Destino
            $empresaDestino = CAT_COMPANIES::WHERE('companies_key', '=', $request->empresaDestino)->first();

            $total = 0;
            $total_articulos = 0;
            $importeTotal = 0;
            $ivaTotal = 0;
            foreach ($claveArt as $clave) {
                if (isset($empresaDestino) && ($empresaDestino->companies_calculateTaxes === "0" || $empresaDestino->companies_calculateTaxes === 0)) {
                    $configuracionArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $clave)->first();
                    $ivaConfigurado = floatval($configuracionArticulo->articles_porcentIva) / 100;
                    $totalMenosIva = intval((floatval($articulos[$clave]["importeTotal"]) *  $ivaConfigurado) * 100) / 100;
                    $ivaTotal += intval($totalMenosIva  * 100) / 100;
                    $importeTotal  +=  floatval($articulos[$clave]["importeTotal"]);
                    $total += $importeTotal + $ivaTotal;
                } else {
                    $importeTotal += floatval($articulos[$clave]["importeTotal"]);
                    $total += floatval($articulos[$clave]["importeTotal"]);
                }

                $total_articulos++;
            }

            $inventario = new PROC_INVENTORIES();

            $folioAjuste = PROC_INVENTORIES::where('inventories_movement', '=', 'Ajuste de Inventario')->where('inventories_branchOffice', '=', $request->sucursales)->max('inventories_movementID');
            $folioAjuste = $folioAjuste == null ? 1 : $folioAjuste + 1;
            $inventario->inventories_movementID = $folioAjuste;
            //origen del movimiento
            $inventario->inventories_movement = 'Ajuste de Inventario';
            $inventario->inventories_issueDate =  date('Y-m-d');
            $inventario->inventories_money =   $moneda->money_key;
            $inventario->inventories_typeChange =  $moneda->money_change;
            $inventario->inventories_reference = NULL;
            $inventario->inventories_concept = 'AJUSTE HERRAMIENTA';
            $inventario->inventories_company = session('company')->companies_key;
            $inventario->inventories_user = Auth::user()->username;
            $inventario->inventories_branchOffice = session('sucursal')->branchOffices_key;
            $inventario->inventories_depot =  $request->almacenes;
            $inventario->inventories_depotDestiny =  NULL;
            $inventario->inventories_total =  $total;
            $inventario->inventories_lines = $total_articulos;
            $inventario->inventories_status = 'FINALIZADO';
            $inventario->inventories_originType = 'Usuario';
            $inventario->inventories_origin = Auth::user()->username;
            $inventario->inventories_originID = null;
            $inventario->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $inventario->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $isCreate =  $inventario->save();
            $lastId = $inventario::latest('inventories_id')->first()->inventories_id;

            //generar la orden de compra con los productos
            $compra = new PROC_PURCHASE();
            $folioCompra = PROC_PURCHASE::where('purchase_movement', '=', 'Orden de Compra')->where('purchase_branchOffice', '=', $request->sucursalDestino)->max('purchase_movementID');
            $folioCompra = $folioCompra == null ? 1 : $folioCompra + 1;
            $compra->purchase_movementID = $folioCompra;
            $compra->purchase_movement = 'Orden de Compra';
            $compra->purchase_issueDate = Carbon::now()->format('Y-m-d');
            $compra->purchase_concept = 'OC HERRAMIENTA';
            $compra->purchase_money =  $moneda->money_key;
            $compra->purchase_typeChange = $moneda->money_change;
            $compra->purchase_provider = $empresa->companies_referenceProvider;
            $compra->purchase_condition = 1;
            $compra->purchase_expiration = Carbon::now()->format('Y-m-d H:i:s');
            $compra->purchase_company = $request->empresaDestino;
            $compra->purchase_branchOffice = $request->sucursalDestino;
            $compra->purchase_depot = $request->almacenDestino;
            $compra->purchase_user = Auth::user()->username;
            $compra->purchase_status = 'POR AUTORIZAR';
            $compra->purchase_amount = $importeTotal;
            $compra->purchase_taxes = $ivaTotal;
            $compra->purchase_total =  $total;
            $compra->purchase_lines =  $total_articulos;
            $compra->purchase_origin = Auth::user()->username;
            $compra->purchase_originType = 'Compras';
            $compra->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $compra->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $compra->save();
            $lastIdCompra = $compra::latest('purchase_id')->first()->purchase_id;


            if ($articulos !== null) {
                foreach ($claveArt as $articulo) {
                    $total = 0;
                    $total_articulos = 0;
                    $importeTotal = 0;
                    $ivaTotal = 0;

                    if (isset($empresaDestino) && ($empresaDestino->companies_calculateTaxes === "0" || $empresaDestino->companies_calculateTaxes === 0)) {
                        $configuracionArticulo = CAT_ARTICLES::WHERE('articles_key', '=', $clave)->first();
                        $ivaConfigurado = floatval($configuracionArticulo->articles_porcentIva) / 100;
                        $totalMenosIva = intval((floatval($articulos[$clave]["importeTotal"]) *  $ivaConfigurado) * 100) / 100;
                        $ivaTotal += intval($totalMenosIva  * 100) / 100;
                        $importeTotal  +=  floatval($articulos[$clave]["importeTotal"]);
                        $total += $importeTotal + $ivaTotal;
                    } else {
                        $importeTotal += floatval($articulos[$clave]["importeTotal"]);
                        $total += floatval($articulos[$clave]["importeTotal"]);
                    }

                    $detalleInventario = new PROC_INVENTORIES_DETAILS();
                    $detalleInventario->inventoryDetails_inventoryID = $lastId;
                    $detalleInventario->inventoryDetails_article = $articulo;
                    $detalleInventario->inventoryDetails_descript = $articulos[$articulo]['descripcion'];
                    $detalleInventario->inventoryDetails_quantity = str_replace(['$', ','], '', $articulos[$articulo]['cantidad']);
                    $detalleInventario->inventoryDetails_unitCost = str_replace(['$', ','], '', $articulos[$articulo]['costo']);
                    $detalleInventario->inventoryDetails_inventoryAmount = str_replace(['$', ','], '', $articulos[$articulo]['cantidad']);
                    $detalleInventario->inventoryDetails_amount = $importeTotal;
                    $detalleInventario->inventoryDetails_total = $total;
                    $detalleInventario->inventoryDetails_depot = $request->almacenes;
                    $detalleInventario->inventoryDetails_branchOffice = session('sucursal')->branchOffices_key;
                    $detalleInventario->save();

                    $detalleCompra = new PROC_PURCHASE_DETAILS();
                    $detalleCompra->purchaseDetails_purchaseID = $lastIdCompra;
                    $detalleCompra->purchaseDetails_article = $articulo;
                    $detalleCompra->purchaseDetails_type = $articulos[$articulo]['tipo'];
                    $detalleCompra->purchaseDetails_descript = $articulos[$articulo]['descripcion'];
                    $detalleCompra->purchaseDetails_quantity = str_replace(['$', ','], '', $articulos[$articulo]['cantidad']);
                    $detalleCompra->purchaseDetails_outstandingAmount = str_replace(['$', ','], '', $articulos[$articulo]['cantidad']);
                    $detalleCompra->purchaseDetails_unitCost = str_replace(['$', ','], '', $articulos[$articulo]['costo']);
                    $detalleCompra->purchaseDetails_inventoryAmount = str_replace(['$', ','], '', $articulos[$articulo]['cantidad']);
                    $detalleCompra->purchaseDetails_amount = $importeTotal;
                    $detalleCompra->purchaseDetails_total = $total;
                    $detalleCompra->purchaseDetails_ivaPorcent = isset($configuracionArticulo->articles_porcentIva) ? $configuracionArticulo->articles_porcentIva : '0';
                    $detalleCompra->purchaseDetails_branchOffice = $request->sucursalDestino;
                    $detalleCompra->purchaseDetails_depot = $request->almacenDestino;
                    $detalleCompra->save();
                }
            }

            $folioAfectar = PROC_INVENTORIES::where('inventories_id', $lastId)->first();


            $this->auxiliarU($folioAfectar->inventories_id);
            $this->agregarAlmacen($folioAfectar->inventories_id);
            $this->costoPromedio($folioAfectar->inventories_id);




            if ($isCreate) {
                $status = 200;
                $message = 'El ajuste de inventario se registró correctamente. La orden de compra se creó con el folio: ' . $compra->purchase_movementID;
            } else {
                $status = 500;
                $message = 'Ocurrio un error al ajustar el inventario';
            }
        } catch (\Throwable $th) {
            $status = 500;
            $message = $th->getMessage() . '-' . $th->getLine();
        }

        return response()->json(['mensaje' => $message, 'estatus' => $status]);
    }



    public function auxiliarU($folio)
    {
        $folioAfectar = PROC_INVENTORIES::where('inventories_id', '=', $folio)->first();

        if ($folioAfectar->inventories_status == 'FINALIZADO' && $folioAfectar->inventories_movement == 'Ajuste de Inventario') {

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
                $auxiliarU->assistantUnit_group = $articulo->inventoryDetails_depot;
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
                $auxiliarU->assistantUnit_paymentUnit = abs((float)$articulo->inventoryDetails_inventoryAmount);
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
        if ($folioAfectar->inventories_status == 'FINALIZADO' && $folioAfectar->inventories_movement == 'Ajuste de Inventario') {
            // echo 'Entrou';
            // dd($folioAfectar);
            $articulos = PROC_INVENTORIES_DETAILS::where('inventoryDetails_inventoryID', '=', $folioAfectar->inventories_id)->get();

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
    }

    public function costoPromedio($folio)
    {
        $folioAfectar = PROC_INVENTORIES::where('inventories_id', '=', $folio)->first();

        if ($folioAfectar->inventories_status == 'FINALIZADO' && $folioAfectar->inventories_movement == 'Ajuste de Inventario') {
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
                $costoAuxArticulo = PROC_ASSISTANT_UNITS::where('assistantUnit_account', '=', $articulo)->where('assistantUnit_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('assistantUnit_companieKey', '=', $folioAfectar->inventories_company)->get()->sum('assistantUnit_charge');
                $cantidadAux[$articulo] = $costoAuxArticulo;

                $costoAuxArticulo2 = PROC_ASSISTANT_UNITS::where('assistantUnit_account', '=', $articulo)->where('assistantUnit_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('assistantUnit_companieKey', '=', $folioAfectar->inventories_company)->get()->sum('assistantUnit_payment');

                $cantidadArticulos = PROC_ARTICLES_INV::where('articlesInv_branchKey', '=', $folioAfectar->inventories_branchOffice)->where('articlesInv_article', '=', $articuloClave[$key])->get()->sum('articlesInv_inventory');
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
    }


    public function getEmpresas()
    {
        $empresas = [];
        $empresas_collection = CAT_COMPANIES::WHERE('companies_status', 'Alta')->get();

        $empresas_array = $empresas_collection->toArray();

        foreach ($empresas_array as $key => $value) {
            $empresas[$value['companies_key']] = $value['companies_name'];
        }
        return $empresas;
    }

    public function getArticulosAlmacen(Request $request)
    {

        $articulosInventario = [];
        $articulosInv = PROC_ARTICLES_INV::join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article')
            ->join('CONF_UNITS', 'CONF_UNITS.units_id', '=', 'CAT_ARTICLES.articles_unitSale')
            ->join('PROC_ARTICLES_COST', 'PROC_ARTICLES_COST.articlesCost_article', '=', 'PROC_ARTICLES_INV.articlesInv_article')->where('articlesInv_companieKey', $request->company)->where('articlesInv_branchKey', $request->branch)->where('articlesInv_depot', $request->almacen)->get()->unique('articlesInv_article');

        foreach ($articulosInv as $value) {
            $articulosInventario[] = $value;
        }

        //regresar json
        if ($articulosInventario != null) {
            $status = 200;
        } else {
            $status = 404;
        }

        return response()->json(['data' => $articulosInventario, 'estatus' => $status]);
    }

    public function getSelectSucursales(Request $request)
    {

        $sucursales = CAT_BRANCH_OFFICES::where("branchOffices_status", 'Alta')->where("branchOffices_companyId", $request->company)->get();


        return response()->json(['datos' => $sucursales, 'estatus' => 200]);
    }

    public function getSelectAlmacenes(Request $request)
    {

        $almacenes = CAT_DEPOTS::where("depots_status", 'Alta')->where("depots_branchlId", $request->sucursal)->get();


        return response()->json(['datos' => $almacenes, 'estatus' => 200]);
    }
}
