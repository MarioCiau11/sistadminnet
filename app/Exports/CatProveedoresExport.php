<?php

namespace App\Exports;

use App\Models\catalogos\CAT_PROVIDERS;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CatProveedoresExport implements FromView, ShouldAutoSize, WithStyles
{
    public $keyProvider;
    public $nameProvider;
    public $category;
    public $group;
    public $status;

    public function __construct($keyProvider, $nameProvider, $category, $group, $status)
    {
        $this->keyProvider = $keyProvider;
        $this->nameProvider = $nameProvider;
        $this->category = $category;
        $this->group = $group;
        $this->status = $status;
    }

    public function view(): View
    {
       
        $proveedor_collection_filtro = CAT_PROVIDERS::whereProvidersKey($this->keyProvider)->whereProvidersName($this->nameProvider)->whereProvidersCategory($this->category)->whereProvidersGroup($this->group)->whereProvidersStatus($this->status)->get();
        
        return view('exports.proveedores', [
            'proveedores' => $proveedor_collection_filtro
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
