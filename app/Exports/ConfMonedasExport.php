<?php

namespace App\Exports;

use App\Models\catalogos\CONF_MONEY;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class ConfMonedasExport implements FromView, ShouldAutoSize, WithStyles
{
    public $keyMoneda;
    public $nameMoneda;
    public $status;

    public function __construct($keyMoneda, $nameMoneda, $status)
    {
        $this->keyMoneda = $keyMoneda;
        $this->nameMoneda = $nameMoneda;
        $this->status = $status;
    }

    public function view(): View
    {
        $money_collection_filtro = CONF_MONEY::whereMonedasKey($this->keyMoneda)->whereMonedasName($this->nameMoneda)->whereMonedasStatus($this->status)->get();
        return view('exports.monedas', [
            'monedas' => $money_collection_filtro
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
