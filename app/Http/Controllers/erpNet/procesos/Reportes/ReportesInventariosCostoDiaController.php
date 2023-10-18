<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Exports\Reportes\ReportesInventariosCostoDiaExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_DEPOTS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportesInventariosCostoDiaController extends Controller
{
    public function index()
    {
        $select_sucursales = $this->selectSucursales();
        $almacen = $this->selectAlmacenes();
        $inventarios = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article', 'left')
            ->join('PROC_ARTICLES_COST', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_COST.articlesCost_article', 'left')
            ->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key', 'left')
            ->join('CAT_BRANCH_OFFICES', 'PROC_ARTICLES_INV.articlesInv_branchKey', '=', 'CAT_BRANCH_OFFICES.branchOffices_key', 'left')
            ->whereIn('articles_type', ['Normal', 'Serie'])
            ->where('articles_status', '=', 'Alta')
            //BUSCAMOS DONDE LA SUCURSAL SEA IGUAL A LA SUCURSAL DE LA SESION O NULL
            ->where(function ($query) {
                $query->where('articlesInv_branchKey', '=', session('sucursal')->branchOffices_key)
                    ->orWhere('articlesInv_branchKey', '=', null);
            })
            //BUSCAMOS DONDE LA COMPANIA SEA IGUAL A LA COMPANIA DE LA SESION O NULL
            ->where(function ($query) {
                $query->where('articlesInv_companieKey', '=', session('company')->companies_key)
                    ->orWhere(
                        'articlesInv_companieKey',
                        '=',
                        null
                    );
            })
            ->orderBy('articles_id', 'asc')
            ->get()->unique('articlesInv_id');

        return view('page.Reportes.Inventarios.indexReporteInventarioCostoDia', compact('select_sucursales', 'almacen', 'inventarios'));
    }

    public function reportesInventarioCostoDiaAction(Request $request)
    {
        $nameSucursal = $request->nameSucursal;
        $nameAlmacen = $request->nameAlmacen;

        switch ($request->input('action')) {

            case 'Búsqueda':
                $reportes_collection_filtro = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article', 'left')
                    ->join('PROC_ARTICLES_COST', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_COST.articlesCost_article', 'left')
                    ->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key', 'left')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_ARTICLES_INV.articlesInv_branchKey', '=', 'CAT_BRANCH_OFFICES.branchOffices_key', 'left')
                    ->whereIn('articles_type', ['Normal', 'Serie'])
                    ->where('articles_status', '=', 'Alta')
                    ->whereArticlesInvBranch($nameSucursal)
                    ->whereArticlesInvDepot($nameAlmacen)
                    ->orderBy('articles_id', 'asc')
                    ->get()->unique('articlesInv_id');

                $reportes_filtro_array = $reportes_collection_filtro->toArray();
                // dd($reportes_filtro_array);

                return redirect()->route('vista.reportes.inventario.costo-dia')
                    ->with('reportes_filtro_array', $reportes_filtro_array)
                    ->with('nameSucursal', $nameSucursal)
                    ->with('nameAlmacen', $nameAlmacen);

                break;

            case 'Exportar excel':
                $inventarios = new ReportesInventariosCostoDiaExport($nameSucursal, $nameAlmacen);
                return Excel::download($inventarios, 'ReporteInventarioCostoDia.xlsx');
                break;

            case 'Exportar PDF':
                $inventario = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article', 'left')
                    ->join('PROC_ARTICLES_COST', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_COST.articlesCost_article')
                    ->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key', 'left')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_ARTICLES_INV.articlesInv_branchKey', '=', 'CAT_BRANCH_OFFICES.branchOffices_key', 'left')
                    ->whereIn('articles_type', ['Normal', 'Serie'])
                    ->where('articles_status', '=', 'Alta')
                    ->where('articlesCost_companieKey', '=', session('company')->companies_key)
                    ->where('articlesInv_companieKey', '=', session('company')->companies_key)
                    ->whereArticlesInvBranch($nameSucursal)
                    ->whereArticlesInvDepot($nameAlmacen)
                    ->whereArticlesCostDepot($nameAlmacen)
                    ->orderBy('articles_id', 'asc')
                    ->get()
                    ->unique('articlesCost_id');

                if ($inventario->isEmpty()) {
                    return redirect()->route('vista.reportes.inventario.costo-dia')->with('message', 'No se pudo generar el reporte, no hay datos para mostrar.')->with('status', false);
                } else {

                    $logoBase64 = $this->obtenerLogoEmpresa();


                    $pdf = PDF::loadView(
                        'page.Reportes.Inventarios.inventariosCostoDia-reporte',
                        [
                            'inventario' => $inventario,
                            'logo' => $logoBase64,
                            'nameSucursal' => $nameSucursal,
                            'nameAlmacen' => $nameAlmacen,
                        ]
                    );

                    $pdf->set_paper('a4', 'landscape');

                    return $pdf->stream();
                }
        }
    }

    private function obtenerLogoEmpresa()
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

        return $logoBase64;
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

    function selectAlmacenes()
    {
        $almacen = CAT_DEPOTS::join('CAT_BRANCH_OFFICES', 'CAT_DEPOTS.depots_branchlId', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CAT_COMPANIES', 'CAT_BRANCH_OFFICES.branchOffices_companyId', '=', 'CAT_COMPANIES.companies_key')
            ->where('CAT_COMPANIES.companies_key', '=', session('company')->companies_key)
            ->where('CAT_BRANCH_OFFICES.branchOffices_key', '=', session('sucursal')->branchOffices_key)
            ->where('CAT_DEPOTS.depots_status', '=', 'Alta')
            ->select('CAT_DEPOTS.*')->get();
        $almacenes_array = array();
        $almacenes_array['Todos'] = 'Todos';
        foreach ($almacen as $almacenes) {
            $almacenes_array[$almacenes->depots_key] = $almacenes->depots_key . ' - ' . $almacenes->depots_name;
        }
        return $almacenes_array;
    }
}
