<?php

namespace App\Exports;

use App\Models\catalogos\Conf_roles;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class RolesExport implements FromView, ShouldAutoSize, WithStyles
{
    public $nameRol;
    public $status;

    public function __construct($nameRol, $status)
    {
        $this->nameRol = $nameRol;
        $this->status = $status;
    }

    public function view(): View
    {
        $rol_collection_filtro = Conf_roles::whereRolName($this->nameRol)->whereRolStatus($this->status)->get();
        
        return view('exports.roles', [
            'roles' => $rol_collection_filtro, 
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
