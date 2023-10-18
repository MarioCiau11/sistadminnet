<?php

namespace App\Exports;

use App\Models\catalogos\CONF_CREDIT_CONDITIONS;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class ConfCondicionCreditoExport implements FromView, ShouldAutoSize, WithStyles
{
    public $nameCondCredito;
    public $status;

    public function __construct($nameCondCredito, $status)
    {
        $this->nameCondCredito = $nameCondCredito;
        $this->status = $status;
    }

    public function view(): View
    {
        $condCredito_collection_filtro = CONF_CREDIT_CONDITIONS::whereConditionName($this->nameCondCredito)->whereStatus($this->status)->get();
        
        return view('exports.condCredito', [
            'condCreditos' => $condCredito_collection_filtro
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
