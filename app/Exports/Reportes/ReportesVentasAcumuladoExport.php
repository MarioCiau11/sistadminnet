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
use stdClass;

class ReportesVentasAcumuladoExport implements FromView, ShouldAutoSize, WithStyles
{
    public $nameMov;
    public $nameArticulo;
    public $nameUnidad;
    public $nameUsuario;
    public $status;
    public $nameFecha;
    public $nameAlmacen;
    public $nameSucursal;
    public $nameMoneda;

    public function __construct($nameMov, $nameArticulo, $nameUnidad, $nameUsuario, $status, $nameFecha, $nameAlmacen, $nameSucursal, $nameMoneda)
    {
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

        $articulosUnicos = [];
        foreach ($venta as $key => $ventaArticulo) {
            $jsonArticulos = new stdClass();

            if(array_key_exists($ventaArticulo->salesDetails_descript, $articulosUnicos)){
                $lastTotal = (float) $articulosUnicos[$ventaArticulo->salesDetails_descript]->salesDetails_total;
                $newTotal = (float) $ventaArticulo->salesDetails_total;

                if($ventaArticulo->salesDetails_total !== null){
                    $articulosUnicos[$ventaArticulo->salesDetails_descript]->salesDetails_total = $lastTotal + $newTotal;
                }
            } else{
                $jsonArticulos->salesDetails_article = $ventaArticulo->salesDetails_article;
                $jsonArticulos->salesDetails_descript = $ventaArticulo->salesDetails_descript;
                $jsonArticulos->salesDetails_unitCost = $ventaArticulo->salesDetails_unitCost;
                $jsonArticulos->salesDetails_total = $ventaArticulo->salesDetails_total;

                $articulosUnicos[$ventaArticulo->salesDetails_descript] = $jsonArticulos;
            }
        }

        return view('exports.reporteVentasAcumulado', [
            'ventas' => $articulosUnicos,
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
