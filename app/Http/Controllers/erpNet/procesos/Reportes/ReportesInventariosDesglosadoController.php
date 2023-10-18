<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesInventarioDesglosadoExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_ARTICLES_CATEGORY;
use App\Models\agrupadores\CAT_ARTICLES_FAMILY;
use App\Models\agrupadores\CAT_ARTICLES_GROUP;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_DEPOTS;
use App\Models\catalogos\CONF_UNITS;
use App\Models\modulos\PROC_ASSISTANT_UNITS;
use App\Models\modulos\PROC_DEL_SERIES_MOV;
use App\Models\modulos\PROC_DEL_SERIES_MOV2;
use App\Models\modulos\PROC_INVENTORIES;
use App\Models\modulos\PROC_LOT_SERIES;
use App\Models\modulos\PROC_LOT_SERIES_MOV;
use App\Models\modulos\PROC_LOT_SERIES_MOV2;
use App\Models\modulos\PROC_PURCHASE;
use App\Models\modulos\PROC_SALES;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportesInventariosDesglosadoController extends Controller
{
    public function index()
    {

        $articulos = $this->selectArticulos();
        $almacenes = $this->selectAlmacenes();
        $categorias = $this->selectCategoria();
        $familias = $this->selectFamilia();
        $grupos = $this->selectGrupo();

        $inventarios =  PROC_ASSISTANT_UNITS::join('CAT_ARTICLES', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'CAT_ARTICLES.articles_key')
            ->join('CAT_DEPOTS', 'PROC_ASSISTANT_UNITS.assistantUnit_group', '=', 'CAT_DEPOTS.depots_key')
            ->where('PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', session('company')->companies_key)
            ->where('PROC_ASSISTANT_UNITS.assistantUnit_canceled', '=', 0)
            ->select('PROC_ASSISTANT_UNITS.*', 'CAT_ARTICLES.articles_descript', 'CAT_ARTICLES.articles_family', 'CAT_ARTICLES.articles_group', 'CAT_ARTICLES.articles_category', 'CAT_DEPOTS.depots_name')
            ->orderBy('PROC_ASSISTANT_UNITS.created_at', 'desc')
            ->paginate(25);

        return view('page.Reportes.Inventarios.indexReporteInventarioDesglosado', compact('articulos', 'almacenes', 'familias', 'grupos', 'categorias', 'inventarios'));
    }

    public function reportesInventarioDesglosadoAction(Request $request)
    {
        // dd($request->all());
        $nameDelArticulo = $request->nameDelArticulo;
        $nameAlArticulo = $request->nameAlArticulo;
        $nameArticulo = $request->nameArticulo;
        $nameFecha = $request->nameFecha;
        $nameCategoria = $request->nameCategoria;
        $nameFamilia = $request->nameFamilia;
        $nameGrupo = $request->nameGrupo;
        $nameAlmacen = $request->nameAlmacen;
        $nameMov = $request->nameMov;
        $nameSerie = $request->nameSerie;
        $nameExistencia = $request->nameExistencia;

        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;

        // dd($nameDelArticulo, $nameAlArticulo, $nameArticulo, $nameFecha, $nameCategoria, $nameFamilia, $nameGrupo, $nameAlmacen, $nameMov, $fechaInicio, $fechaFinal);
        if ($fechaInicio !== null && $fechaFinal !== null && $nameFecha === "Rango Fechas") {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }

        switch ($request->input('action')) {
            case 'Búsqueda':
                $reportes_collection_filtro =  PROC_ASSISTANT_UNITS::join('CAT_ARTICLES', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'CAT_ARTICLES.articles_key')
                    ->join('CAT_DEPOTS', 'PROC_ASSISTANT_UNITS.assistantUnit_group', '=', 'CAT_DEPOTS.depots_key')
                    ->select('PROC_ASSISTANT_UNITS.*', 'CAT_ARTICLES.articles_key', 'CAT_ARTICLES.articles_group', 'CAT_ARTICLES.articles_category', 'CAT_ARTICLES.articles_family', 'CAT_ARTICLES.articles_descript', 'CAT_DEPOTS.depots_name')
                    ->whereAssistantUnitAccount($nameDelArticulo, $nameAlArticulo, $nameArticulo)
                    ->whereAssistantUnitDate($nameFecha)
                    ->whereArticleCategory($nameCategoria)
                    ->whereArticleFamily($nameFamilia)
                    ->whereArticleGroup($nameGrupo)
                    ->whereAssistantUnitDepot($nameAlmacen)
                    ->whereAssistantUnitMovement($nameMov)
                    ->where('PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', session('company')->companies_key)
                    ->where('PROC_ASSISTANT_UNITS.assistantUnit_canceled', '=', 0)
                    ->orderBy('PROC_ASSISTANT_UNITS.created_at', 'desc')
                    ->get();
                    

                $reportes_filtro_array = $reportes_collection_filtro->toArray();
                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.reportes.inventario.desglosado')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('nameDelArticulo', $nameDelArticulo)
                    ->with('nameAlArticulo', $nameAlArticulo)
                    ->with('nameArticulo', $nameArticulo)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameCategoria', $nameCategoria)
                    ->with('nameFamilia', $nameFamilia)
                    ->with('nameGrupo', $nameGrupo)
                    ->with('nameAlmacen', $nameAlmacen)
                    ->with('nameMov', $nameMov)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal)
                    ->with('nameSerie', $nameSerie);
                break;
            case 'Exportar excel':
                $inventario = new ReportesInventarioDesglosadoExport($nameDelArticulo, $nameAlArticulo, $nameArticulo, $nameFecha, $nameCategoria, $nameFamilia, $nameGrupo, $nameAlmacen, $nameMov, $nameSerie, $nameExistencia);
                return Excel::download($inventario, 'ReporteInventarioDesglosado.xlsx');
                break;
            case 'Exportar PDF':
                $inventario =  PROC_ASSISTANT_UNITS::join('CAT_ARTICLES', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'CAT_ARTICLES.articles_key')
                    ->join('CAT_DEPOTS', 'PROC_ASSISTANT_UNITS.assistantUnit_group', '=', 'CAT_DEPOTS.depots_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_ASSISTANT_UNITS.assistantUnit_branchKey', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CAT_COMPANIES', 'PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', 'CAT_COMPANIES.companies_key')
                    ->select('PROC_ASSISTANT_UNITS.*', 'CAT_ARTICLES.articles_key', 'CAT_ARTICLES.articles_group', 'CAT_ARTICLES.articles_category', 'CAT_ARTICLES.articles_type', 'CAT_ARTICLES.articles_family', 'CAT_ARTICLES.articles_descript', 'CAT_DEPOTS.depots_name', 'CAT_BRANCH_OFFICES.branchOffices_name', 'CAT_COMPANIES.companies_name')
                    ->whereAssistantUnitAccount($nameDelArticulo, $nameAlArticulo, $nameArticulo)
                    ->whereAssistantUnitDate($nameFecha)
                    ->whereArticleCategory($nameCategoria)
                    ->whereArticleFamily($nameFamilia)
                    ->whereArticleGroup($nameGrupo)
                    ->whereAssistantUnitDepot($nameAlmacen)
                    ->whereAssistantUnitMovement($nameMov)
                    ->where('PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', session('company')->companies_key)
                    ->where('PROC_ASSISTANT_UNITS.assistantUnit_branchKey', '=',  session('sucursal')->branchOffices_key)
                    ->where('PROC_ASSISTANT_UNITS.assistantUnit_canceled', '=', 0)
                    ->get()
                    ->unique('assistantUnit_id');


                if ($inventario->isEmpty()) {
                    return redirect()->route('vista.reportes.inventario.desglosado')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                } else {
                    $informacionDesglose = [];

                    foreach ($inventario as $inventario) {
                        $modulo = strtoupper($inventario->assistantUnit_module);
                        $tipoArticulo = $inventario->articles_type;
                        $identificadorModulo = $inventario->assistantUnit_moduleID;
                        $articuloReferencia = $inventario->assistantUnit_account;
                        $empresa = $inventario->assistantUnit_companieKey;
                        $sucursal = $inventario->assistantUnit_branchKey;
                        $isConcluidoMov = false;

                        //Validamos que los movimientos esten en statusConcluido
                        switch ($inventario->assistantUnit_module) {
                            case 'Compras':
                                $movCompra = PROC_PURCHASE::WHERE('purchase_id', '=', $identificadorModulo)->WHERE('purchase_status', '=', 'FINALIZADO')->first();

                                if ($movCompra != NULL) {
                                    $isConcluidoMov = true;
                                }
                                break;

                            case 'Ventas':
                                $movVentas = PROC_SALES::WHERE('sales_id', '=', $identificadorModulo)->WHERE('sales_status', '=', 'FINALIZADO')->first();

                                if ($movVentas != NULL) {
                                    $isConcluidoMov = true;
                                }
                                break;

                            case 'Inv':
                                $movInventario = PROC_INVENTORIES::WHERE('inventories_id', '=', $identificadorModulo)->WHERE('inventories_status', '=', 'FINALIZADO')->first();

                                if ($movInventario != NULL) {
                                    $isConcluidoMov = true;
                                }
                                break;

                            default:
                                # code...
                                break;
                        }

                        if ($isConcluidoMov) {
                            if ($tipoArticulo == "Serie" && $nameSerie == "Si") {
                                switch ($inventario->assistantUnit_module) {
                                    case 'Compras':
                                        $seriesCompras = PROC_LOT_SERIES_MOV::WHERE('lotSeriesMov_module', '=', $modulo)->WHERE('lotSeriesMov_purchaseID', '=', $identificadorModulo)->WHERE('lotSeriesMov_article', '=', $articuloReferencia)->where('lotSeriesMov_companieKey', '=', $empresa)
                                            ->where('lotSeriesMov_branchKey', '=',  $sucursal)->GET();
                                        if (count($seriesCompras) >= 1) {
                                            $inventarioConSerie = array_merge($inventario->toArray(), ['series' => $seriesCompras->toArray()]);
                                            array_push($informacionDesglose, $inventarioConSerie);
                                        } else {
                                            array_push($informacionDesglose, $inventario->toArray());
                                        }
                                        break;

                                    case 'Ventas':
                                        $seriesVentas = PROC_DEL_SERIES_MOV2::WHERE('delSeriesMov2_module', '=', $inventario->assistantUnit_module)->WHERE('delSeriesMov2_saleID', '=', $identificadorModulo)->WHERE('delSeriesMov2_article', '=', $articuloReferencia)->where('delSeriesMov2_companieKey', '=', $empresa)->where('delSeriesMov2_branchKey', '=',  $sucursal)->where('delSeriesMov2_affected', '=', 1)->GET()->unique('delSeriesMov2_id');

                                        if (count($seriesVentas) >= 1) {
                                            $inventarioConSerie = array_merge($inventario->toArray(), ['series' => $seriesVentas->toArray()]);
                                            array_push($informacionDesglose, $inventarioConSerie);
                                        } else {
                                            array_push($informacionDesglose, $inventario->toArray());
                                        }
                                        break;

                                    case 'Inv':
                                        if ($inventario->assistantUnit_movement == 'Ajuste de Inventario') {
                                            $seriesInv = PROC_LOT_SERIES_MOV2::WHERE('lotSeriesMov2_module', '=', $modulo)->WHERE('lotSeriesMov2_inventoryID', '=', $identificadorModulo)->WHERE('lotSeriesMov2_article', '=', $articuloReferencia)->where('lotSeriesMov2_companieKey', '=', $empresa)
                                                ->where('lotSeriesMov2_branchKey', '=',  $sucursal)->GET();
                                        } else {
                                            $seriesInv = PROC_DEL_SERIES_MOV::WHERE('delSeriesMov_module', '=', $modulo)->WHERE('delSeriesMov_inventoryID', '=', $identificadorModulo)->WHERE('delSeriesMov_article', '=', $articuloReferencia)->where('delSeriesMov_companieKey', '=', $empresa)
                                                ->where('delSeriesMov_branchKey', '=',  $sucursal)->GET();
                                        }


                                        if (count($seriesInv) >= 1) {
                                            $inventarioConSerie = array_merge($inventario->toArray(), ['series' => $seriesInv->toArray()]);
                                            array_push($informacionDesglose, $inventarioConSerie);
                                        } else {
                                            array_push($informacionDesglose, $inventario->toArray());
                                        }

                                        break;

                                    default:
                                        # code...
                                        break;
                                }
                            }
                            if ($nameSerie == "No") {
                                array_push($informacionDesglose, $inventario->toArray());
                            }
                        }
                    }

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

                    switch ($nameFecha) {

                            // $date = $date->format('l jS \\of F Y h:i:s A');
                        case 'Hoy':


                            $nameFecha = Carbon::now()->isoFormat('LL');
                            // ->format('l jS \\of F Y');

                            break;
                        case 'Ayer':
                            $nameFecha = new Carbon('yesterday');
                            break;
                        case 'Semana':
                            $fecha_actual = Carbon::now()->isoFormat('LL');
                            $fecha_semana = new Carbon('last week');
                            $fecha_formato = $fecha_semana->isoFormat('LL');
                            $nameFecha = 'Del ' . $fecha_formato . ' al ' . $fecha_actual;
                            break;
                        case 'Mes':
                            $now = Carbon::now();
                            $start    = (new DateTime($now->format('Y-m-d')))->modify('first day of this month');
                            $end      = (new DateTime($now->format('Y-m-d')))->modify('last day of this month');

                            $fecha_inicial = $start->format('Y-m-d');
                            $fecha_fin = $end->format('Y-m-d');
                            $fechaInicio = Carbon::parse($fecha_inicial)->isoFormat('LL');
                            $fechaFinal = Carbon::parse($fecha_fin)->isoFormat('LL');
                            $nameFecha = 'Del ' . $fechaInicio . ' al ' . $fechaFinal;
                            break;
                        case 'Año Móvil':
                            $fecha_actual = Carbon::now()->isoFormat('LL');
                            $fecha_año_actual = Carbon::now()->format('Y');
                            $fecha_inicial = $fecha_año_actual . '-01-01';
                            $fecha_formato = Carbon::parse($fecha_inicial)->isoFormat('LL');
                            $nameFecha = 'Del ' . $fecha_formato . ' al ' . $fecha_actual;

                            break;
                        case 'Año Pasado':
                            $fecha_año_inicioMes_pasado = new Carbon('last year');
                            $formato_fecha_inicioMes_pasado = $fecha_año_inicioMes_pasado->format('Y');
                            $inicoAñoPasado = $formato_fecha_inicioMes_pasado . '-01-01';
                            $finAñoPasado = $formato_fecha_inicioMes_pasado . '-12-31';
                            $fecha_inicio_formato = Carbon::parse($inicoAñoPasado)->isoFormat('LL');
                            $fecha_fin_formato = Carbon::parse($finAñoPasado)->isoFormat('LL');
                            $nameFecha = 'Del ' . $fecha_inicio_formato . ' al ' . $fecha_fin_formato;
                            break;
                        default:
                            $fechaInicio = Carbon::parse($fechaInicio)->isoFormat('LL');
                            $fechaFinal = Carbon::parse($fechaFinal)->isoFormat('LL');
                            $nameFecha = $fechaInicio . ' - ' . $fechaFinal;

                            break;
                    }

                    $pdf = PDF::loadView('page.Reportes.Inventarios.inventariosDesglosado-reporte', [
                        'logo' => $logoBase64,
                        'inventarios' => $informacionDesglose,

                        'fecha' => $nameFecha,
                        'nameAlmacen' => $nameAlmacen,
                        'nameMov' => $nameMov,
                        'nameCategoria' => $nameCategoria,
                        'nameFamilia' => $nameFamilia,
                        'nameGrupo' => $nameGrupo,
                        'nameDelArticulo' => $nameDelArticulo,
                        'nameAlArticulo' => $nameAlArticulo,
                        'nameSerie' => $nameSerie,
                        'nameExistencia' => $nameExistencia,
                    ]);
                    $pdf->set_paper('a4', 'landscape');

                    return $pdf->stream();
                }

                break;
        }
    }

    function selectArticulos()
    {
        $articulos = CAT_ARTICLES::where('articles_status', '=', 'Alta')->orderBy('articles_id', 'asc')->get();
        $articulos_array = array();
        foreach ($articulos as $articulos) {
            $articulos_array[$articulos->Todos] = 'Todos';
            $articulos_array[$articulos->articles_key] = $articulos->articles_key . ' - ' . $articulos->articles_descript;
        }
        return $articulos_array;
    }

    function selectAlmacenes()
    {
        $almacen = CAT_DEPOTS::join('CAT_BRANCH_OFFICES', 'CAT_DEPOTS.depots_branchlId', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CAT_COMPANIES', 'CAT_BRANCH_OFFICES.branchOffices_companyId', '=', 'CAT_COMPANIES.companies_key')
            ->where('CAT_DEPOTS.depots_status', '=', 'Alta')
            ->where('CAT_COMPANIES.companies_key', '=', session('company')->companies_key)
            // ->where('CAT_BRANCH_OFFICES.branchOffices_key', '=', session('sucursal')->branchOffices_key)
            ->select('CAT_DEPOTS.*')->get();
        $almacenes_array = array();
        $almacenes_array['Todos'] = 'Todos';
        foreach ($almacen as $almacenes) {
            $almacenes_array[$almacenes->depots_key] = $almacenes->depots_key . ' - ' . $almacenes->depots_name;
        }
        return $almacenes_array;
    }

    function selectCategoria()
    {
        $categoria = CAT_ARTICLES_CATEGORY::where('categoryArticle_status', '=', 'Alta')->get();
        $categoria_array = array();
        $categoria_array['Todos'] = 'Todos';
        foreach ($categoria as $categorias) {
            $categoria_array[$categorias->categoryArticle_name] = $categorias->categoryArticle_id . ' - ' . $categorias->categoryArticle_name;
        }
        return $categoria_array;
    }

    function selectFamilia()
    {
        $familia = CAT_ARTICLES_FAMILY::where('familyArticle_status', '=', 'Alta')->get();
        $familia_array = array();
        $familia_array['Todos'] = 'Todos';
        foreach ($familia as $familias) {
            $familia_array[$familias->familyArticle_name] = $familias->familyArticle_id . ' - ' . $familias->familyArticle_name;
        }
        return $familia_array;
    }

    function selectGrupo()
    {
        $grupo = CAT_ARTICLES_GROUP::where('groupArticle_status', '=', 'Alta')->get();
        $grupo_array = array();
        $grupo_array['Todos'] = 'Todos';
        foreach ($grupo as $grupos) {
            $grupo_array[$grupos->groupArticle_name] = $grupos->groupArticle_id . ' - ' . $grupos->groupArticle_name;
        }
        return $grupo_array;
    }
}
