<?php

namespace App\Exports;

use App\Models\catalogos\Conf_Unidades;
use App\Models\catalogos\CONF_UNITS;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConfUnidadesExport implements FromView, ShouldAutoSize, WithStyles
{

    public $keyUnidad;
    public $status;

    public function __construct($keyUnidad, $status)
    {
        $this->keyUnidad = $keyUnidad;
        $this->status = $status;
    }

    Public function view(): View
    {
        if(!$this->keyUnidad){
            $unidad_collection_filtro = CONF_UNITS::join('CAT_SAT_CLAVEUNIDAD', 'CONF_UNITS.units_keySat', '=', 'CAT_SAT_CLAVEUNIDAD.c_ClaveUnidad')
            ->select('CONF_UNITS.*', 'CAT_SAT_CLAVEUNIDAD.nombre')->whereStatus($this->status)->get();
        }else{
            $unidad_collection_filtro = CONF_UNITS::join('CAT_SAT_CLAVEUNIDAD', 'CONF_UNITS.units_keySat', '=', 'CAT_SAT_CLAVEUNIDAD.c_ClaveUnidad')
            ->select('CONF_UNITS.*', 'CAT_SAT_CLAVEUNIDAD.nombre')->whereUnit($this->keyUnidad)->whereStatus($this->status)->get();
        }
        return view('exports.unidades', [
            'unidades' => $unidad_collection_filtro
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
