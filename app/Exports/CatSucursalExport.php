<?php

namespace App\Exports;

use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_SUCURSALES;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class CatSucursalExport implements FromView, ShouldAutoSize, WithStyles
{
    public $keySucursal;
    public $nameSucursal;
    public $status;

    public function __construct($keySucursal, $nameSucursal, $status)
    {
        $this->keySucursal = $keySucursal;
        $this->nameSucursal = $nameSucursal;
        $this->status = $status;
    }

    public function view(): View
    {
  
        $sucursal_collection_filtro = CAT_BRANCH_OFFICES::join('CAT_COMPANIES', 'branchOffices_companyId', '=', 'CAT_COMPANIES.companies_key')
            ->select('CAT_BRANCH_OFFICES.*', 'CAT_COMPANIES.companies_name')
            ->wherebranchOfficesKey($this->keySucursal)
            ->wherebranchOfficesName($this->nameSucursal)
            ->wherebranchOfficesStatus($this->status)->get();
        
        return view('exports.sucursales', [
            'sucursales' => $sucursal_collection_filtro
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
