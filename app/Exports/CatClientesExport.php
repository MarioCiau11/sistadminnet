<?php

namespace App\Exports;

use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CAT_PROVIDERS;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CatClientesExport implements FromView, ShouldAutoSize, WithStyles
{
    public $keyCustomer;
    public $nameCustomer;
    public $bussinessName;
    public $category;
    public $group;
    public $status;

    public function __construct($keyCustomer, $nameCustomer, $bussinessName, $category, $group, $status)
    {
        $this->keyCustomer = $keyCustomer;
        $this->nameCustomer = $nameCustomer;
        $this->bussinessName = $bussinessName;
        $this->category = $category;
        $this->group = $group;
        $this->status = $status;
    }

    public function view(): View
    {
       
        $cliente_collection_filtro = CAT_CUSTOMERS::whereCustomersKey($this->keyCustomer)->whereCustomersName($this->nameCustomer)->whereCustomersBusinessName($this->bussinessName)->whereCustomersCategory($this->category)->whereCustomersGroup($this->group)->whereCustomersStatus($this->status)->get();
        
        return view('exports.clientes', [
            'clientes' => $cliente_collection_filtro
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
