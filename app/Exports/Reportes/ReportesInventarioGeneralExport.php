<?php

namespace App\Exports\Reportes;

use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\modulos\PROC_ASSISTANT_UNITS;
use App\Models\modulos\PROC_SALES_DETAILS;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportesInventarioGeneralExport implements FromView, ShouldAutoSize, WithStyles
{
    public $nameDelArticulo;
    public $nameAlArticulo;
    public $nameArticulo;
    public $nameFecha;
    public $nameCategoria;
    public $nameFamilia;
    public $nameGrupo;
    public $nameAlmacen;
    public $nameMov;

    public function __construct($nameDelArticulo, $nameAlArticulo, $nameArticulo, $nameFecha, $nameCategoria, $nameFamilia, $nameGrupo, $nameAlmacen, $nameMov)
    {
        $this->nameDelArticulo = $nameDelArticulo;
        $this->nameAlArticulo = $nameAlArticulo;
        $this->nameArticulo = $nameArticulo;
        $this->nameFecha = $nameFecha;
        $this->nameCategoria = $nameCategoria;
        $this->nameFamilia = $nameFamilia;
        $this->nameGrupo = $nameGrupo;
        $this->nameAlmacen = $nameAlmacen;
        $this->nameMov = $nameMov;
    }

    public function view(): View
    {
        $reportes_collection_filtro =  PROC_ASSISTANT_UNITS::join('CAT_ARTICLES', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'CAT_ARTICLES.articles_key')
        ->join('CAT_DEPOTS', 'PROC_ASSISTANT_UNITS.assistantUnit_group', '=', 'CAT_DEPOTS.depots_key')
        ->select('PROC_ASSISTANT_UNITS.*', 'CAT_ARTICLES.articles_descript', 'CAT_ARTICLES.articles_family', 'CAT_ARTICLES.articles_group', 'CAT_ARTICLES.articles_category', 'CAT_DEPOTS.depots_name')
        ->where('assistantUnit_canceled', "0")
        ->whereAssistantUnitAccount($this->nameDelArticulo, $this->nameAlArticulo, $this->nameArticulo)
        ->whereAssistantUnitDate($this->nameFecha)
        ->whereArticleCategory($this->nameCategoria)
        ->whereArticleFamily($this->nameFamilia)
        ->whereArticleGroup($this->nameGrupo)
        ->whereAssistantUnitDepot($this->nameAlmacen)
        ->whereAssistantUnitMovement($this->nameMov)
        ->where('PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', session('company')->companies_key)
        ->orderBy('PROC_ASSISTANT_UNITS.created_at', 'desc')
        ->get();

        $inventario =  PROC_ASSISTANT_UNITS::join('CAT_ARTICLES', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'CAT_ARTICLES.articles_key')
        ->join('CAT_DEPOTS', 'PROC_ASSISTANT_UNITS.assistantUnit_group', '=', 'CAT_DEPOTS.depots_key')
        ->join('CAT_COMPANIES', 'PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', 'CAT_COMPANIES.companies_key')
        ->join('PROC_ARTICLES_INV', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'PROC_ARTICLES_INV.articlesInv_article')
        ->select('PROC_ASSISTANT_UNITS.*', 'CAT_ARTICLES.articles_descript', 'CAT_ARTICLES.articles_family', 'CAT_ARTICLES.articles_group', 'CAT_ARTICLES.articles_category', 'CAT_ARTICLES.articles_key', 'CAT_DEPOTS.depots_name', 'CAT_COMPANIES.companies_name', 'CAT_COMPANIES.companies_logo', 'PROC_ARTICLES_INV.*')
        ->where('assistantUnit_canceled', "0")
        ->whereAssistantUnitAccount($this->nameDelArticulo, $this->nameAlArticulo, $this->nameArticulo)
        ->whereAssistantUnitDate($this->nameFecha)
        ->whereArticleCategory($this->nameCategoria)
        ->whereArticleFamily($this->nameFamilia)
        ->whereArticleGroup($this->nameGrupo)
        ->whereAssistantUnitDepot($this->nameAlmacen)
        ->whereAssistantUnitMovement($this->nameMov)
        ->where('PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', session('company')->companies_key)
        ->orderBy('PROC_ASSISTANT_UNITS.created_at', 'desc')
        ->get()
        ->unique('assistantUnit_id');

        $articulos =  PROC_ASSISTANT_UNITS::join('CAT_ARTICLES', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'CAT_ARTICLES.articles_key')
        ->join('CAT_DEPOTS', 'PROC_ASSISTANT_UNITS.assistantUnit_group', '=', 'CAT_DEPOTS.depots_key')
        ->join('CAT_COMPANIES', 'PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', 'CAT_COMPANIES.companies_key')
        ->join('PROC_ARTICLES_INV', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'PROC_ARTICLES_INV.articlesInv_article')
        ->select('PROC_ASSISTANT_UNITS.*', 'CAT_ARTICLES.articles_descript', 'CAT_ARTICLES.articles_family', 'CAT_ARTICLES.articles_group', 'CAT_ARTICLES.articles_category', 'CAT_ARTICLES.articles_key', 'CAT_DEPOTS.depots_name', 'CAT_COMPANIES.companies_name', 'CAT_COMPANIES.companies_logo', 'PROC_ARTICLES_INV.*')
        ->where('assistantUnit_canceled', "0")
        ->whereAssistantUnitAccount($this->nameDelArticulo, $this->nameAlArticulo, $this->nameArticulo)
        ->whereAssistantUnitDate($this->nameFecha)
        ->whereArticleCategory($this->nameCategoria)
        ->whereArticleFamily($this->nameFamilia)
        ->whereArticleGroup($this->nameGrupo)
        ->whereAssistantUnitDepot($this->nameAlmacen)
        ->whereAssistantUnitMovement($this->nameMov)
        ->where('PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', session('company')->companies_key)
        ->orderBy('PROC_ASSISTANT_UNITS.created_at', 'desc')
        ->get()->unique('assistantUnit_account');

        // dd($articulos);


        $ventas_array = [];
        $precios = [];
        $compras_array = [];
        $inventarios_array = [];
        $clientes = [];
         foreach($inventario as $key => $inv){
            if($inv->assistantUnit_module == "Inv"){
                $inventarios_array[] = $inv;
            }

            if($inv->assistantUnit_module == "Compras"){
                $compras_array[] = $inv;
            }

            if($inv->assistantUnit_module == "Ventas"){
                $ventas_array[] = $inv;

                $cliente = CAT_CUSTOMERS::where('customers_key', $inv->asssistantUnit_costumer)->first();
                $precios[]= PROC_SALES_DETAILS::where('salesDetails_saleID', $inv->assistantUnit_moduleID)->get();

            if (!in_array($cliente->customers_key.'-'.$cliente->customers_businessName, $clientes)) {
                $clientes[] = $cliente->customers_key.'-'.$cliente->customers_businessName;
            }



                    

            }
            
         }

        return view('exports.reporteInventariosGeneral', [
            'inventario' => $inventario,
            'precios' => $precios,
            'articulos' => $articulos,
            'inventario_array' => $inventarios_array,
            'clientes' => $clientes,
            'compras' => $compras_array,
            'ventas' => $ventas_array,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
