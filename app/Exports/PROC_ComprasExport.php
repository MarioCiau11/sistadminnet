<?php

namespace App\Exports;

use App\Models\modulos\PROC_PURCHASE;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PROC_ComprasExport implements FromView, ShouldAutoSize, WithStyles
{
    public $nameFolio;
    public $nameKey;
    public $nameMov;
    public $status;
    public $nameFecha;
    public $nameUsuario;
    public $nameSucursal;
    public $nameMoneda;

    public function __construct($nameFolio, $nameKey, $nameMov, $status, $nameFecha, $nameUsuario, $nameSucursal, $nameMoneda)
    {
        $this->nameFolio = $nameFolio;
        $this->nameKey = $nameKey;
        $this->nameMov = $nameMov;
        $this->status = $status;
        $this->nameFecha = $nameFecha;
        $this->nameUsuario = $nameUsuario;
        $this->nameSucursal = $nameSucursal;
        $this->nameMoneda = $nameMoneda;
    }

    public function view(): View
    {
        $compras_collection_filtro = PROC_PURCHASE::join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
        ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
        ->join('CONF_CREDIT_CONDITIONS', 'PROC_PURCHASE.purchase_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
        ->join('CAT_COMPANIES', 'PROC_PURCHASE.purchase_company', '=', 'CAT_COMPANIES.companies_key')
        ->join('CAT_DEPOTS', 'PROC_PURCHASE.purchase_depot', '=', 'CAT_DEPOTS.depots_key')
        ->select('PROC_PURCHASE.*', 'CAT_PROVIDERS.providers_name', 'CAT_BRANCH_OFFICES.branchOffices_name', 'CONF_CREDIT_CONDITIONS.creditConditions_name', 'CAT_COMPANIES.companies_nameShort', 'CAT_DEPOTS.depots_name')
        ->wherePurchaseMovementID($this->nameFolio)
        ->wherePurchaseProvider($this->nameKey)
        ->wherePurchaseMovement($this->nameMov)
        ->wherePurchaseStatus($this->status)
        ->wherePurchaseDate($this->nameFecha)
        ->wherePurchaseUser($this->nameUsuario)
        ->wherePurchasebranchOffice($this->nameSucursal)
        ->wherePurchaseMoney($this->nameMoneda)
        ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
        ->orderBY('PROC_PURCHASE.purchase_movement', 'asc')
        ->orderBy('PROC_PURCHASE.purchase_movementID', 'asc')
        ->get();


        return view('exports.Compras', [
            'compras' => $compras_collection_filtro,
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


