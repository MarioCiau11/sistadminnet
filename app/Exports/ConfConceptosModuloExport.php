<?php

namespace App\Exports;

use App\Models\catalogos\CONF_MODULES_CONCEPT;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConfConceptosModuloExport implements FromView, ShouldAutoSize, WithStyles
{
    public $nameConcepto;
    public $modulo;
    public $status;

    public function __construct($nameConcepto, $modulo, $status)
    {
        $this->nameConcepto = $nameConcepto;
        $this->modulo = $modulo;
        $this->status = $status;
    }

    public function view(): View
    {
        if(!$this->nameConcepto){
            $concepto_collection_filtro = CONF_MODULES_CONCEPT::whereConceptName($this ->nameConcepto)->whereStatus($this->status)->get();
        }else{
            $concepto_collection_filtro = CONF_MODULES_CONCEPT::whereConceptName($this ->nameConcepto)->get();
        }
        return view('exports.ConceptosModulo', [
            'concepto' => $concepto_collection_filtro
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
