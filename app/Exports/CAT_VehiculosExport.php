<?php

namespace App\Exports;

use App\Models\catalogos\CAT_VEHICLES;


use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Contracts\View\View;

class CAT_VehiculosExport implements FromView, ShouldAutoSize, WithStyles
{
    public $keyVehiculo;
    public $nameVehiculo;
    public $status;

    public function __construct($keyVehiculo, $nameVehiculo, $status)
    {
        $this->keyVehiculo = $keyVehiculo;
        $this->nameVehiculo = $nameVehiculo;
        $this->status = $status;
    }

    public function View(): View
    {
        $vehiculos_collection_filtro = CAT_VEHICLES::join('CAT_BRANCH_OFFICES', 'CAT_VEHICLES.vehicles_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
        ->select('CAT_VEHICLES.*', 'CAT_BRANCH_OFFICES.branchOffices_name')->whereVehicleKey($this->keyVehiculo)->whereVehicleName($this->nameVehiculo)->whereVehicleStatus($this->status)->get();

        return view('exports.vehiculos', [
            'vehiculos' => $vehiculos_collection_filtro
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