<?php

namespace App\Exports;

use App\Models\catalogos\CONF_FORMS_OF_PAYMENT;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class ConfFormasPagoExport implements FromView, ShouldAutoSize, WithStyles
{
    public $keyForma;
    public $nameForma;
    public $status;

    public function __construct($keyForma, $nameForma, $status)
    {
        $this->keyForma = $keyForma;
        $this->nameForma = $nameForma;
        $this->status = $status;
    }

    public function view(): View
    {
         $formas_collection_filtro = CONF_FORMS_OF_PAYMENT::whereFormsPaymentKey($this->keyForma)->whereFormsPaymentName($this->nameForma)->whereFormsPaymentStatus($this->status)->get();
                
        return view('exports.formasPago', [
            'formaPagos' => $formas_collection_filtro
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
