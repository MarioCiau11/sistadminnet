<?php

namespace App\Exports;

use App\Models\catalogos\CAT_FINANCIAL_INSTITUTIONS;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class InstFinancialExport implements FromView, ShouldAutoSize, WithStyles
{
    public $keyInstFinancial;
    public $nameInstFinancial;
    public $status;

    public function __construct($keyInstFinancial, $nameInstFinancial, $status)
    {
        $this->keyInstFinancial = $keyInstFinancial;
        $this->nameInstFinancial = $nameInstFinancial;
        $this->status = $status;
    }

    public function view(): View
    {
        $instFinancial = CAT_FINANCIAL_INSTITUTIONS::whereInstFinancialKey($this->keyInstFinancial)->whereInsFinancialName($this->nameInstFinancial)->whereInsFinancialStatus($this->status)->get();
                   
        return view('exports.instFinancial', [
            'instituciones' => $instFinancial
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
