<?php

namespace App\Exports;

use App\Models\catalogos\CAT_COST_CENTER;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CATCostCenterExport implements FromView, ShouldAutoSize, WithStyles
{
    public $keyCentroCosto;
    public $nameCentroCosto;
    public $status;

    public function __construct($keyCentroCosto, $nameCentroCosto, $status)
    {
        $this->keyCentroCosto = $keyCentroCosto;
        $this->nameCentroCosto = $nameCentroCosto;
        $this->status = $status;
    }

    public function view(): View
    {
       
        $centroCostos_collection_filtro = CAT_COST_CENTER::whereCostCenterKey($this->keyCentroCosto)->whereCostCenterName($this->nameCentroCosto)->whereCostCenterStatus($this->status)->get();
        
        return view('exports.centroCostos', [
            'centroCostos' => $centroCostos_collection_filtro
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
