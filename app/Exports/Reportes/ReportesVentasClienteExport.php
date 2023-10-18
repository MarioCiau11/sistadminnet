<?php

namespace App\Exports\Reportes;

use App\Models\modulos\PROC_SALES;
use App\Models\modulos\PROC_SALES_DETAILS;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportesVentasClienteExport implements FromView, ShouldAutoSize, WithStyles
{
    public $nameCliente;
    public $nameCategoria;
    public $nameGrupo;
    public $nameMov;
    public $nameArticulo;
    public $nameUnidad;
    public $nameUsuario;
    public $status;
    public $nameFecha;
    public $nameAlmacen;
    public $nameSucursal;
    public $nameMoneda;

    public function __construct($nameCliente, $nameCategoria, $nameGrupo, $nameMov, $nameArticulo, $nameUnidad, $nameUsuario, $status, $nameFecha, $nameAlmacen, $nameSucursal, $nameMoneda)
    {
        $this->nameCliente = $nameCliente;
        $this->nameCategoria = $nameCategoria;
        $this->nameGrupo = $nameGrupo;
        $this->nameMov = $nameMov;
        $this->nameArticulo = $nameArticulo;
        $this->nameUnidad = $nameUnidad;
        $this->nameUsuario = $nameUsuario;
        $this->status = $status;
        $this->nameFecha = $nameFecha;
        $this->nameAlmacen = $nameAlmacen;
        $this->nameSucursal = $nameSucursal;
        $this->nameMoneda = $nameMoneda;
    }


    public function view(): View
    {
        $venta = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
        ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
        ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
        ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
        ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
        ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
        ->whereSalesCustomer($this->nameCliente)
        ->whereCustomerCategory($this->nameCategoria)
        ->whereCustomerGroup($this->nameGrupo)
        ->whereSalesMovement($this->nameMov)
        ->whereSalesArticle($this->nameArticulo)
        ->whereSalesUnit($this->nameUnidad)
        ->whereSalesUser($this->nameUsuario)
        ->whereSalesStatus($this->status)
        ->whereSalesDate($this->nameFecha)
        ->whereSalesDepot($this->nameAlmacen)
        ->whereSalesBranchOffice($this->nameSucursal)
        ->whereSalesMoney($this->nameMoneda)
        ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
        ->orderBy('PROC_SALES.created_at', 'desc')
        ->get();

        $collectionVentas = collect($venta);
        $clientesVenta = $collectionVentas->unique('sales_customer')->unique()->all();

        $sucursal_almacen = $collectionVentas->unique('sales_branchOffice')->unique()->first();

        $sucursal = $sucursal_almacen->branchOffices_key .'-'. $sucursal_almacen->branchOffices_name;

        $almacen = $sucursal_almacen->depots_key .'-'. $sucursal_almacen->depots_name;

        $clientesPorVentas = [];
        foreach($clientesVenta as $cliente){

            $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'CAT_CUSTOMERS.customers_key', '=', 'PROC_SALES.sales_customer')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CONF_CREDIT_CONDITIONS', 'CONF_CREDIT_CONDITIONS.creditConditions_id', '=', 'PROC_SALES.sales_condition')
            ->join('CAT_DEPOTS', 'CAT_DEPOTS.depots_key', '=', 'PROC_SALES.sales_depot')
            ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
            ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
            ->whereSalesCustomer($cliente->sales_customer)
            ->whereCustomerCategory($this->nameCategoria)
            ->whereCustomerGroup($this->nameGrupo)
            ->whereSalesMovement($this->nameMov)
            ->whereSalesArticle($this->nameArticulo)
            ->whereSalesUnit($this->nameUnidad)
            ->whereSalesUser($this->nameUsuario)
            ->whereSalesStatus($this->status)
            ->whereSalesDate($this->nameFecha)
            ->whereSalesDepot($this->nameAlmacen)
            ->whereSalesBranchOffice($this->nameSucursal)
            ->whereSalesMoney($this->nameMoneda)
            ->get();

            $arrayClientes = $cliente->toArray();
            $clientesPorVentas[] = array_merge($arrayClientes, ['ventas' => $ventas->toArray()]);
        }

        

        return view('exports.reporteVentasCliente', [
            'clientesVentas' => $clientesPorVentas
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
