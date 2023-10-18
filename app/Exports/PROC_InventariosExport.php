<?php

namespace App\Exports;

use App\Models\modulos\PROC_INVENTORIES;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PROC_InventariosExport implements FromView, ShouldAutoSize, WithStyles
{
    public $nameFolio;
    public $nameMov;
    public $status;
    public $nameFecha;
    public $nameUsuario;
    public $nameSucursal;

    public function __construct($nameFolio, $nameMov, $status, $nameFecha, $nameUsuario, $nameSucursal)
    {
        $this->nameFolio = $nameFolio;
        $this->nameMov = $nameMov;
        $this->status = $status;
        $this->nameFecha = $nameFecha;
        $this->nameUsuario = $nameUsuario;
        $this->nameSucursal = $nameSucursal;
    }

    public function view(): View
    {
        $inventarios_collection_filtro = PROC_INVENTORIES::join('CAT_BRANCH_OFFICES', 'PROC_INVENTORIES.inventories_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
        ->join('CAT_COMPANIES', 'PROC_INVENTORIES.inventories_company', '=', 'CAT_COMPANIES.companies_key')
        ->join('CAT_DEPOTS', 'PROC_INVENTORIES.inventories_depot', '=', 'CAT_DEPOTS.depots_key')
        ->join('CAT_DEPOTS as CAT_DEPOTS2', 'PROC_INVENTORIES.inventories_depotDestiny', '=', 'CAT_DEPOTS2.depots_key', 'left')
        ->select('PROC_INVENTORIES.*', 'CAT_BRANCH_OFFICES.branchOffices_name', 'CAT_COMPANIES.companies_name', 'CAT_DEPOTS.depots_name', 'CAT_DEPOTS2.depots_name as depots_nameDestiny')
        ->where('PROC_INVENTORIES.inventories_company', '=', session('company')->companies_key)
        ->whereInventoriesMovementID($this->nameFolio)
        ->whereInventoriesMovement($this->nameMov)
        ->whereInventoriesStatus($this->status)
        ->whereInventoriesDate($this->nameFecha)
        ->whereInventoriesUser($this->nameUsuario)
        ->whereInventoriesBranchOffice($this->nameSucursal)
        ->orderBy('PROC_INVENTORIES.created_at', 'desc')
        ->get();

        return view('exports.Inventarios', [
            'inventarios' => $inventarios_collection_filtro,
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
