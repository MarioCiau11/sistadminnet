<?php

namespace App\Exports\Reportes;

use App\Models\modulos\PROC_EXPENSES;
use App\Models\modulos\PROC_SALES;
use App\Models\modulos\PROC_SALES_DETAILS;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportesUtilidadExport implements FromView, ShouldAutoSize, WithStyles
{
    public $nameCliente;
    public $nameFecha;

    public function __construct($nameCliente, $nameFecha)
    {
        $this->nameCliente = $nameCliente;
        $this->nameFecha = $nameFecha;
    }

    public function view(): View
    {
        $venta = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
            ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
            ->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_SALES.sales_company')
            ->whereSalesCustomer($this->nameCliente)
            ->whereSalesDate($this->nameFecha)
            ->orderBy('PROC_SALES.created_at', 'asc')
            ->where('PROC_SALES.sales_movement', '=', 'FACTURA')
            ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
            ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
            ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->get();

        $gastos = PROC_EXPENSES::join('PROC_EXPENSES_DETAILS', 'PROC_EXPENSES.expenses_id', '=', 'PROC_EXPENSES_DETAILS.expensesDetails_expenseID')
            ->join('PROC_SALES', 'PROC_SALES.sales_movementID', '=', 'PROC_EXPENSES.expenses_antecedentsName')
            ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
            ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->get()->unique();


        $detalleVentas = PROC_SALES_DETAILS::join('PROC_SALES', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')->where('PROC_SALES.sales_movement', '=', 'FACTURA')->join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
            ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
            ->whereSalesCustomer($this->nameCliente)
            ->whereSalesDate($this->nameFecha)
            ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
            ->where('PROC_SALES.sales_branchOffice', '=', session('sucursal')->branchOffices_key)
            ->orderBy('PROC_SALES.created_at', 'asc')->get();

        return view('exports.reporteUtilidad', [
            'venta' => $venta,
            'gastos' => $gastos,
            'detalleVentas' => $detalleVentas,
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
