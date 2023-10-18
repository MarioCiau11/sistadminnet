<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesGastosAntActFExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_PROVIDER_CATEGORY;
use App\Models\agrupadores\CAT_PROVIDER_GROUP;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_DEPOTS;
use App\Models\catalogos\CAT_EXPENSE_CONCEPTS;
use App\Models\catalogos\CAT_PROVIDERS;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MONEY;
use App\Models\catalogos\CONF_UNITS;
use App\Models\modulos\PROC_EXPENSES;
use App\Models\modulos\PROC_LOT_SERIES;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportesGastosAntecedentesActivoFController extends Controller
{
    public function index()
    {

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();
        if ($parametro == null) {
            return redirect('/parametros-generales/create')->with('status', false)->with('message', 'Favor de registrar los parametros generales');
        }
        $select_sucursales = $this->selectSucursales();
        $selectMonedas = $this->getMonedas();
        $unidad = $this->selectUnidades();
        $conceptos = $this->selectConceptos();
        $proveedor = $this->selectProveedores();
        $categorias = $this->selectCategoria();
        $grupos = $this->selectGrupo();
        $articulo = $this->selectArticulosSerie();
        $serie = $this->selectSerie();
        $antecedente = $this->selectAntecedente();
        $gastos = PROC_EXPENSES::join('CAT_PROVIDERS', 'PROC_EXPENSES.expenses_provider', '=', 'CAT_PROVIDERS.providers_key')
            ->join('PROC_EXPENSES_DETAILS', 'PROC_EXPENSES.expenses_id', '=', 'PROC_EXPENSES_DETAILS.expensesDetails_expenseID')
            ->join('CAT_BRANCH_OFFICES', 'PROC_EXPENSES.expenses_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CAT_COMPANIES', 'PROC_EXPENSES.expenses_company', '=', 'CAT_COMPANIES.companies_key')
            ->where(function ($query) {
                $query->where('expenses_antecedents', '=', '1')
                    ->orWhere('expenses_fixedAssets', '=', '1');
            })
            ->where('PROC_EXPENSES.expenses_company', '=', session('company')->companies_key)
            ->where('PROC_EXPENSES.expenses_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->where('PROC_EXPENSES.expenses_money', '=', session('generalParameters')->generalParameters_defaultMoney)
            ->orderBy('PROC_EXPENSES.updated_at', 'DESC')
            ->paginate(25);


        // dd($gastos);

        return view('page.Reportes.Gastos.indexReporteGastosAntecedentesActivoF', compact('select_sucursales', 'selectMonedas', 'unidad', 'proveedor', 'conceptos', 'parametro', 'gastos', 'articulo', 'serie', 'categorias', 'grupos', 'antecedente'));
    }


    public function reportesGastosATAFAction(Request $request)
    {
        $nameMov = $request->nameMov;
        $nameProveedor = $request->nameProveedor;
        $nameCategoria = $request->nameCategoria;
        $nameGrupo = $request->nameGrupo;
        $nameSucursal = $request->nameSucursal;
        $nameFecha = $request->nameFecha;
        $nameMoneda = $request->nameMoneda;
        $status = $request->status;
        $antecedentes = $request->antecedentes;
        $activoFijo = $request->activoFijo;
        $nameAntecedentes = $request->nameAntecedentes;
        $nameActivoFijo = $request->nameActivoFijo;
        $activoFijoNombreSerie = $request->activoFijoNombreSerie;

        $fechaInicio = $request->fechaInicio;
        $fechaFinal = $request->fechaFinal;

        if ($fechaInicio !== null && $fechaFinal !== null && $nameFecha === "Rango Fechas") {
            $nameFecha = $fechaInicio . '+' . $fechaFinal;
        }

        switch ($request->input('action')) {
            case 'Búsqueda':
                $reportes_collection_filtro = PROC_EXPENSES::join('CAT_PROVIDERS', 'PROC_EXPENSES.expenses_provider', '=', 'CAT_PROVIDERS.providers_key')
                    ->join('PROC_EXPENSES_DETAILS', 'PROC_EXPENSES.expenses_id', '=', 'PROC_EXPENSES_DETAILS.expensesDetails_expenseID')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_EXPENSES.expenses_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CAT_COMPANIES', 'PROC_EXPENSES.expenses_company', '=', 'CAT_COMPANIES.companies_key')
                    ->where(function ($query) {
                        $query->where('expenses_antecedents', '=', '1')
                            ->orWhere('expenses_fixedAssets', '=', '1');
                    })
                    ->whereExpensesMovement($nameMov)
                    ->whereExpensesProvider($nameProveedor)
                    ->whereProviderCategory($nameCategoria)
                    ->whereProviderGroup($nameGrupo)
                    ->whereExpensesBranchOffice($nameSucursal)
                    ->whereExpensesDate($nameFecha)
                    ->whereExpensesMoney($nameMoneda)
                    ->whereExpensesStatus($status)
                    ->whereExpensesAntecedentsName($nameAntecedentes)
                    ->whereExpensesAntecedents($antecedentes)
                    ->whereExpensesFixedAssets($activoFijo)
                    ->whereExpensesFixedAssetsName($nameActivoFijo)
                    ->whereExpensesFixedAssetsSeries($activoFijoNombreSerie)
                    ->where('PROC_EXPENSES.expenses_company', '=', session('company')->companies_key)
                    ->orderBy('PROC_EXPENSES.updated_at', 'DESC')
                    ->get();

                $reportes_filtro_array = $reportes_collection_filtro->toArray();

                $nameFecha = $request->nameFecha;

                return redirect()->route('vista.reportes.gastos-antecedente-activo-fijo')->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('nameMov', $nameMov)
                    ->with('nameProveedor', $nameProveedor)
                    ->with('nameCategoria', $nameCategoria)
                    ->with('nameGrupo', $nameGrupo)
                    ->with('nameSucursal', $nameSucursal)
                    ->with('nameFecha', $nameFecha)
                    ->with('nameMoneda', $nameMoneda)
                    ->with('status', $status)
                    ->with('antecedentes', $antecedentes)
                    ->with('activoFijo', $activoFijo)
                    ->with('nameAntecedentes', $nameAntecedentes)
                    ->with('nameActivoFijo', $nameActivoFijo)
                    ->with('activoFijoNombreSerie', $activoFijoNombreSerie)
                    ->with('fechaInicio', $fechaInicio)
                    ->with('fechaFinal', $fechaFinal);
                break;

            case 'Exportar excel':

                $gasto = new ReportesGastosAntActFExport($nameMov, $nameProveedor, $nameCategoria, $nameGrupo, $nameSucursal, $nameFecha, $nameMoneda, $status, $antecedentes, $activoFijo, $nameAntecedentes, $nameActivoFijo, $activoFijoNombreSerie);
                return Excel::download($gasto, 'Reporte de Gastos por Antecedentes o Activo Fijo.xlsx');

                break;

            case 'Exportar PDF':

                $gastoConcepto = PROC_EXPENSES::join('CAT_PROVIDERS', 'PROC_EXPENSES.expenses_provider', '=', 'CAT_PROVIDERS.providers_key')
                    ->join('PROC_EXPENSES_DETAILS', 'PROC_EXPENSES.expenses_id', '=', 'PROC_EXPENSES_DETAILS.expensesDetails_expenseID')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_EXPENSES.expenses_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CAT_COMPANIES', 'PROC_EXPENSES.expenses_company', '=', 'CAT_COMPANIES.companies_key')
                    ->where('expenses_antecedents', '=', 1)->orWhere('expenses_fixedAssets', '=', 1)
                    ->whereExpensesMovement($nameMov)
                    ->whereExpensesProvider($nameProveedor)
                    ->whereProviderCategory($nameCategoria)
                    ->whereProviderGroup($nameGrupo)
                    ->whereExpensesBranchOffice($nameSucursal)
                    ->whereExpensesDate($nameFecha)
                    ->whereExpensesMoney($nameMoneda)
                    ->whereExpensesStatus($status)
                    ->whereExpensesAntecedentsName($nameAntecedentes)
                    ->whereExpensesAntecedents($antecedentes)
                    ->whereExpensesFixedAssets($activoFijo)
                    ->whereExpensesFixedAssetsName($nameActivoFijo)
                    ->whereExpensesFixedAssetsSeries($activoFijoNombreSerie)
                    ->where('PROC_EXPENSES.expenses_company', '=', session('company')->companies_key)
                    ->orderBy('PROC_EXPENSES.updated_at', 'ASC')
                    ->first();

                if ($gastoConcepto === null) {
                    return redirect()->route('vista.reportes.gastos-antecedente-activo-fijo')->with('message', 'no se pudo generar el reporte ya que no hay datos que se puedan generar')->with('status', false);
                } else {
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

                    $gastos = PROC_EXPENSES::join('CAT_PROVIDERS', 'PROC_EXPENSES.expenses_provider', '=', 'CAT_PROVIDERS.providers_key')
                        ->join('PROC_EXPENSES_DETAILS', 'PROC_EXPENSES.expenses_id', '=', 'PROC_EXPENSES_DETAILS.expensesDetails_expenseID')
                        ->join('CAT_BRANCH_OFFICES', 'PROC_EXPENSES.expenses_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                        ->join('CAT_COMPANIES', 'PROC_EXPENSES.expenses_company', '=', 'CAT_COMPANIES.companies_key')
                        ->where('expenses_antecedents', '=', 1)->orWhere('expenses_fixedAssets', '=', 1)
                        ->whereExpensesMovement($nameMov)
                        ->whereExpensesProvider($nameProveedor)
                        ->whereProviderCategory($nameCategoria)
                        ->whereProviderGroup($nameGrupo)
                        ->whereExpensesBranchOffice($nameSucursal)
                        ->whereExpensesDate($nameFecha)
                        ->whereExpensesMoney($nameMoneda)
                        ->whereExpensesStatus($status)
                        ->whereExpensesAntecedentsName($nameAntecedentes)
                        ->whereExpensesAntecedents($antecedentes)
                        ->whereExpensesFixedAssets($activoFijo)
                        ->whereExpensesFixedAssetsName($nameActivoFijo)
                        ->whereExpensesFixedAssetsSeries($activoFijoNombreSerie)
                        ->where('PROC_EXPENSES.expenses_company', '=', session('company')->companies_key)
                        ->orderBy('PROC_EXPENSES.updated_at', 'ASC')
                        ->get();
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
                    $pdf = PDF::loadview('page.Reportes.Gastos.gastosAntecedentesActivoF-reporte', ['gastos' => $gastos, 'gastoConcepto' => $gastoConcepto, 'logo' => $logoBase64, 'status' => $status, 'namefecha' => $nameFecha]);
                    $pdf->set_paper('a4', 'landscape');
                    return $pdf->stream();
                }
        }
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

    function selectUnidades()
    {
        $unidades = CONF_UNITS::where('units_status', '=', 'Alta')->get();
        $unidades_array = array();
        foreach ($unidades as $unidades) {
            $unidades_array[$unidades->Todos] = 'Todos';
            $unidades_array[$unidades->units_id] = $unidades->units_unit;
        }
        return $unidades_array;
    }

    function selectConceptos()
    {
        $conceptos = CAT_EXPENSE_CONCEPTS::where('expenseConcepts_status', '=', 'Alta')->get();
        $conceptos_array = array();
        foreach ($conceptos as $conceptos) {
            $conceptos_array[$conceptos->Todos] = 'Todos';
            $conceptos_array[$conceptos->expenseConcepts_id] = $conceptos->expenseConcepts_concept;
        }
        return $conceptos_array;
    }
    function selectProveedores()
    {
        $proveedores = CAT_PROVIDERS::where('providers_status', '=', 'Alta')->get();
        $proveedores_array = array();
        foreach ($proveedores as $proveedores) {
            $proveedores_array[$proveedores->Todos] = 'Todos';
            $proveedores_array[$proveedores->providers_key] = $proveedores->providers_key . ' - ' . $proveedores->providers_name;
        }
        return $proveedores_array;
    }

    function selectCategoria()
    {
        $categorias = CAT_PROVIDER_CATEGORY::where('categoryProvider_status', '=', 'Alta')->get();
        $categorias_array = array();
        foreach ($categorias as $categorias) {
            $categorias_array[$categorias->Todos] = 'Todos';
            $categorias_array[$categorias->categoryProvider_name] = $categorias->categoryProvider_name;
        }
        return $categorias_array;
    }

    function selectGrupo()
    {
        $grupos = CAT_PROVIDER_GROUP::where('groupProvider_status', '=', 'Alta')->get();
        $grupos_array = array();
        foreach ($grupos as $grupos) {
            $grupos_array[$grupos->Todos] = 'Todos';
            $grupos_array[$grupos->groupProvider_name] = $grupos->groupProvider_name;
        }
        return $grupos_array;
    }

    function selectArticulosSerie()
    {
        $articulos = CAT_ARTICLES::where('articles_type', '=', 'Serie')->where('articles_status', '=', 'Alta')->get();
        $articulos_array = array();

        foreach ($articulos as $articulos) {
            $articulos_array[$articulos->Todos] = 'Todos';
            $articulos_array[$articulos->articles_key] = $articulos->articles_key . ' - ' . $articulos->articles_descript;
        }
        return $articulos_array;
    }
    function selectSerie()
    {
        $serie = PROC_LOT_SERIES::all();
        $serie_array = array();
        foreach ($serie as $serie) {
            $serie_array[$serie->Todos] = 'Todos';
            $serie_array[$serie->lotSeries_lotSerie] = $serie->lotSeries_article . ' - ' . $serie->lotSeries_lotSerie;
        }
        return $serie_array;
    }

    function selectAntecedente()
    {
        $antecedentes = PROC_EXPENSES::select('expenses_antecedentsName', 'sales_movement')
            ->join('PROC_SALES', 'PROC_SALES.sales_movementID', '=', 'PROC_EXPENSES.expenses_antecedentsName')
            ->where('sales_movement', '=', 'Factura')
            ->where('expenses_antecedentsName', '!=', null)->distinct()->get();
        $antecedentes_array = array();
        foreach ($antecedentes as $antecedentes) {
            $antecedentes_array[$antecedentes->Todos] = 'Todos';
            $antecedentes_array[$antecedentes->expenses_antecedentsName] = $antecedentes->sales_movement . ' - ' . $antecedentes->expenses_antecedentsName;
        }
        return $antecedentes_array;
    }
}
