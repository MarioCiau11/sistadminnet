<?php

namespace App\Exports;

use App\Models\catalogos\CONF_PACKAGING_UNITS;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConfUnidadesEmpExport implements FromView, ShouldAutoSize, WithStyles
{

    public $keyUnidad;
    public $status;
    

    public function __construct($keyMoneda, $status)
    {
        $this->keyMoneda = $keyMoneda;
        $this->status = $status;
    }

    Public function view(): View
    {
        if(!$this->keyUnidad){
             $unidadEmp_collection_filtro = CONF_PACKAGING_UNITS::whereStatus($this->status)->get();
        }else{
            $unidadEmp_collection_filtro = CONF_PACKAGING_UNITS::whereUnitPackaging($this->keyUnidad)->whereStatus($this->status)->get();
        }
        return view('exports.unidadesEmp', [
            'unidadesEmp' => $unidadEmp_collection_filtro
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