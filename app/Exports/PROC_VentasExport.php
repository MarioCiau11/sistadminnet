<?php

namespace App\Exports;

use App\Models\modulos\PROC_SALES;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class PROC_VentasExport implements FromView, ShouldAutoSize, WithStyles
{
    public $nameFolio;
    public $nameKey;
    public $nameMov;
    public $status;
    public $nameFecha;
    public $nameUsuario;
    public $nameSucursal;
    public $nameMoneda;
    public $timbrado;
    public $ref;

    public function __construct($nameFolio, $nameKey, $nameMov, $status, $nameFecha, $nameUsuario, $nameSucursal, $nameMoneda, $timbrado, $ref)
    {
        $this->nameFolio = $nameFolio;
        $this->nameKey = $nameKey;
        $this->nameMov = $nameMov;
        $this->status = $status;
        $this->nameFecha = $nameFecha;
        $this->nameUsuario = $nameUsuario;
        $this->nameSucursal = $nameSucursal;
        $this->nameMoneda = $nameMoneda;
        $this->timbrado = $timbrado;
        $this->ref = $ref;
    }

    public function view(): View
    {
        $ventas_collection_filtro = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
        ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
        ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
        ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
        ->join('PROC_SALES_DETAILS', 'PROC_SALES_DETAILS.salesDetails_saleID', '=', 'PROC_SALES.sales_id')
        ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
        ->whereSalesMovementID($this->nameFolio)
        ->whereSalesCustomer($this->nameKey)
        ->whereSalesMovement($this->nameMov)
        ->whereSalesStatus($this->status)
        ->whereSalesDate($this->nameFecha)
        ->whereSalesUser($this->nameUsuario)
        ->whereSalesBranchOffice($this->nameSucursal)
        ->whereSalesMoney($this->nameMoneda)
        ->whereSalesStamped($this->timbrado)
        ->whereSalesReference($this->ref)
        ->orderBy('PROC_SALES.sales_movementID', 'asc')
        ->get()
        ->unique('sales_id');
        // dd($ventas_collection_filtro);

        return view('exports.Ventas', [
            'ventas' => $ventas_collection_filtro
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
