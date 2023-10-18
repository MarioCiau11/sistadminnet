<?php

namespace App\Exports\Reportes;

use App\Models\catalogos\CAT_ARTICLES;
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


class ReportesInventariosCostoDiaExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
{
    public $nameSucursal;
    public $nameAlmacen;

    public function __construct($nameSucursal, $nameAlmacen)
    {
        $this->nameSucursal = $nameSucursal;
        $this->nameAlmacen = $nameAlmacen;
    }

    public function collection (){
        $inventario = CAT_ARTICLES::join('PROC_ARTICLES_INV', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_INV.articlesInv_article', 'left')
        ->join('PROC_ARTICLES_COST', 'CAT_ARTICLES.articles_key', '=', 'PROC_ARTICLES_COST.articlesCost_article', 'left')
        ->join('CAT_DEPOTS', 'PROC_ARTICLES_INV.articlesInv_depot', '=', 'CAT_DEPOTS.depots_key', 'left')
        ->join('CAT_BRANCH_OFFICES', 'PROC_ARTICLES_INV.articlesInv_branchKey', '=', 'CAT_BRANCH_OFFICES.branchOffices_key', 'left')
        ->whereIn('articles_type', ['Normal', 'Serie'])
        ->where('articles_status', '=', 'Alta')
        ->whereArticlesInvBranch($this->nameSucursal)
        ->whereArticlesInvDepot($this->nameAlmacen)
        ->where('articlesCost_companieKey', '=', session('company')->companies_key)
        ->orderBy('articles_id', 'asc')
        ->get()->unique('articlesInv_id');

        return [
            'inventario' => $inventario
        ];
    }

    public function view(): View {
        return view('exports.reporteInventariosCostoDia', $this->collection());
    }

    public function drawings()
    {

        if (session('company')->companies_logo === null || session('company')->companies_logo === '') {
            $logoFile = null;
        } else {
            $logoFile = storage_path('app/empresas/' . session('company')->companies_logo);
            if (!file_exists($logoFile)) {
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