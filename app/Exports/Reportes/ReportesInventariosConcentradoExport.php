<?php

namespace App\Exports\Reportes;

use App\Models\modulos\PROC_ASSISTANT_UNITS;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ReportesInventariosConcentradoExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
{
    public $nameDelArticulo;
    public $nameAlArticulo;
    public $nameArticulo;
    public $nameFecha;
    public $nameCategoria;
    public $nameFamilia;
    public $nameGrupo;
    public $nameAlmacen;

    public function __construct($nameDelArticulo, $nameAlArticulo, $nameArticulo,$nameFecha, $nameCategoria, $nameFamilia, $nameGrupo, $nameAlmacen)
    {
        $this->nameDelArticulo = $nameDelArticulo;
        $this->nameAlArticulo = $nameAlArticulo;
        $this->nameArticulo = $nameArticulo;
        $this->nameFecha = $nameFecha;
        $this->nameCategoria = $nameCategoria;
        $this->nameFamilia = $nameFamilia;
        $this->nameGrupo = $nameGrupo;
        $this->nameAlmacen = $nameAlmacen;
    }

    public function collection (){
        $inventario =  PROC_ASSISTANT_UNITS::join('CAT_ARTICLES', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'CAT_ARTICLES.articles_key')
        ->join('CAT_DEPOTS', 'PROC_ASSISTANT_UNITS.assistantUnit_group', '=', 'CAT_DEPOTS.depots_key')
        ->join('CAT_BRANCH_OFFICES', 'PROC_ASSISTANT_UNITS.assistantUnit_branchKey', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
        ->join('CAT_COMPANIES', 'PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', 'CAT_COMPANIES.companies_key')
        ->whereAssistantUnitAccount($this->nameDelArticulo, $this->nameAlArticulo, $this->nameArticulo)
        ->whereAssistantUnitDate($this->nameFecha)
        ->whereArticleCategory($this->nameCategoria)
        ->whereArticleFamily($this->nameFamilia)
        ->whereArticleGroup($this->nameGrupo)
        ->where('PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', session('company')->companies_key)
        ->orderBy('CAT_ARTICLES.articles_id', 'asc')
        ->get()
        ->unique('assistantUnit_id');

        
        $articulos =  PROC_ASSISTANT_UNITS::join('CAT_ARTICLES', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'CAT_ARTICLES.articles_key')
        ->join('CAT_DEPOTS', 'PROC_ASSISTANT_UNITS.assistantUnit_group', '=', 'CAT_DEPOTS.depots_key')
        ->join('CAT_COMPANIES', 'PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', 'CAT_COMPANIES.companies_key')
        ->join('PROC_ARTICLES_INV', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'PROC_ARTICLES_INV.articlesInv_article')
        ->join('CONF_UNITS', 'CONF_UNITS.units_id', '=', 'CAT_ARTICLES.articles_unitSale')
        ->where('assistantUnit_canceled', "0")
        ->whereAssistantUnitAccount($this->nameDelArticulo, $this->nameAlArticulo, $this->nameArticulo)
        ->whereAssistantUnitDate($this->nameFecha)
        ->whereArticleCategory($this->nameCategoria)
        ->whereArticleFamily($this->nameFamilia)
        ->whereArticleGroup($this->nameGrupo)
        ->where('PROC_ASSISTANT_UNITS.assistantUnit_companieKey', '=', session('company')->companies_key)
        ->orderBy('CAT_ARTICLES.articles_key', 'asc')
        ->get()->unique('assistantUnit_account')->sortBy('articles_key');

        $inventarios_array = [];
        foreach ($inventario as $key => $inv) {
            $inventarios_array[] = $inv;
        }

        
        return  [
            'inventario' => $inventario,
            'articulos' => $articulos,
            'nameAlmacen' => $this->nameAlmacen,
            'inventario_array' => $inventarios_array
        ];
    }

    public function view(): View {
        return view('exports.reporteInventariosConcentrado', $this->collection());
    }

    public function drawings()
    {

        if (session('company')->companies_logo === null || session('company')->companies_logo === '') {
            $logoFile = null;
        } else {
            $logoFile = storage_path('app/empresas/' . session('company')->companies_logo);
            if(!file_exists($logoFile)) {
                $logoFile = null;
            }
        }

        if ($logoFile == null) {
            $logoFile = storage_path('app/empresas/default.png');

        }
        $drawing = new Drawing();
        $drawing->setPath($logoFile);
        $drawing->setWidth(50);
        $drawing->setHeight(50);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

    public function columnWidths(): array
    {
        return [
       
        ];
    }

    public function registerEvents(): array
    {
        //poner altura a la fila A1
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(50);
            },
        ];
    }

/**
 * > La función `styles` devuelve un array de estilos para aplicar a la hoja
 * 
 * @param Worksheet La hoja que se está exportando
 * 
 * @return An array of styles.
 */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}