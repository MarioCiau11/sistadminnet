<?php

namespace App\Exports;

use App\Models\catalogos\CAT_DEPOTS;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Contracts\View\View;

class CATAlmacenesExport implements FromView, ShouldAutoSize, WithStyles
{
    public $keyAlmacen;
    public $nameAlmacen;
    public $status;

    public function __construct($keyAlmacen, $nameAlmacen, $status)
    {
        $this->keyAlmacen = $keyAlmacen;
        $this->nameAlmacen = $nameAlmacen;
        $this->status = $status;
    }

    public function View(): View
    {
        $almacen_collection_filtro = CAT_DEPOTS::join('CAT_BRANCH_OFFICES', 'CAT_DEPOTS.depots_branchlId', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
        ->select('CAT_DEPOTS.*', 'CAT_BRANCH_OFFICES.branchOffices_name')->whereDepotsKey($this->keyAlmacen)->whereDepotsName($this->nameAlmacen)->whereDepotsStatus($this->status)->get();

        return view('exports.almacenes', [
            'almacenes' => $almacen_collection_filtro
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

