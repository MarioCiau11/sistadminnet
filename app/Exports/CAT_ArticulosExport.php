<?php

namespace App\Exports;
use App\Models\catalogos\CAT_ARTICLES;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Contracts\View\View;

class CAT_ArticulosExport implements FromView, ShouldAutoSize, WithStyles
{
    public $claveArticulo;
    public $articuloName;
    public $category;
    public $group;
    public $family;
    public $status;
    public $unidad;
    

    public function __construct($claveArticulo, $articuloName, $category, $group, $family,$status, $unidad)
    {
        $this->claveArticulo = $claveArticulo;
        $this->articuloName = $articuloName;
        $this->category = $category;
        $this->group = $group;
        $this->family = $family;
        $this->status = $status;
        $this->unidad = $unidad;
    }

    public function view(): View
    {
         $articulos_filtro = CAT_ARTICLES::whereArticlesKey($this->claveArticulo)
         ->whereArticlesNombre($this->articuloName)
         ->whereArticlesCategory($this->category)
         ->whereArticlesGroup($this->group)
         ->whereArticlesFamily($this->family)
         ->whereArticlesStatus($this->status)
         ->get();
         
        return view('exports.Articulos', [
            'articulos' => $articulos_filtro,
            'unidad' => $this->unidad,
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
