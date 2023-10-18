<?php

namespace App\Exports;

use App\Models\catalogos\CAT_COMPANIES;
use App\Models\catalogos\CAT_EMPRESAS;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CatEmpresasExport implements FromView, ShouldAutoSize, WithStyles
{
    public $keyEmpresa;
    public $nameEmpresa;
    public $status;

    public function __construct($keyEmpresa, $nameEmpresa, $status)
    {
        $this->keyEmpresa = $keyEmpresa;
        $this->nameEmpresa = $nameEmpresa;
        $this->status = $status;
    }

    public function view(): View
    {
       
        $empresa_collection_filtro = CAT_COMPANIES::whereCompaniesKey($this->keyEmpresa)->whereCompaniesName($this->nameEmpresa)->whereCompaniesStatus($this->status)->get();
        
        return view('exports.empresas', [
            'empresas' => $empresa_collection_filtro
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
