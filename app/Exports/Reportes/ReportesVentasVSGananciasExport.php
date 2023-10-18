<?php

namespace App\Exports\Reportes;

use App\Models\modulos\PROC_SALES;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ReportesVentasVSGananciasExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
{
    public $nameSucursal;
    public $nameFecha;
    public $nameAgrupador;

    public function __construct($nameSucursal, $nameFecha, $nameAgrupador)
    {
        $this->nameSucursal = $nameSucursal;
        $this->nameFecha = $nameFecha;
        $this->nameAgrupador = $nameAgrupador;
    }

    public function collection()
    {
        $venta = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
            ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
            ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
            ->where('PROC_SALES.sales_movement', '=', 'Factura')
            ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
            ->whereSalesDate($this->nameFecha)
            ->whereSalesBranchOffice($this->nameSucursal)
            ->get();

        $collectionVentas = collect($venta);
        $sucursalesVentas = $collectionVentas->unique('sales_branchOffice')->unique()->all();

        $sucursalesPorVentas = [];
        foreach ($sucursalesVentas as $sucursal) {
            if ($this->nameAgrupador === 'Categoría') {
                $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                    ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                    ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                    ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
                    ->where('PROC_SALES.sales_movement', '=', 'Factura')
                    ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                    ->whereSalesBranchOffice($sucursal->sales_branchOffice)
                    ->whereSalesDate($this->nameFecha)
                    ->get();
            } elseif ($this->nameAgrupador === 'Grupo') {
                $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                    ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                    ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                    ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
                    ->where('PROC_SALES.sales_movement', '=', 'Factura')
                    ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                    ->whereSalesBranchOffice($sucursal->sales_branchOffice)
                    ->whereSalesDate($this->nameFecha)
                    ->get();
            } elseif ($this->nameAgrupador === 'Familia') {
                $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                    ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                    ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                    ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                    ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
                    ->where('PROC_SALES.sales_movement', '=', 'Factura')
                    ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                    ->whereSalesBranchOffice($sucursal->sales_branchOffice)
                    ->whereSalesDate($this->nameFecha)
                    ->get();
            }


            $arraySucursalesVentas = $sucursal->toArray();
            $sucursalesPorVentas[] = array_merge($arraySucursalesVentas, ['ventas' => $ventas->toArray()]);
        }

        $detalles = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
            ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
            ->where('PROC_SALES.sales_movement', '=', 'Factura')
            ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
            ->whereSalesBranchOffice($this->nameSucursal)
            ->whereSalesDate($this->nameFecha)
            ->get();

        $detalles_array = [];
        //hacemos un foreach para obtener los detalles de cada venta con sus respectivas sucursales
        foreach ($detalles as $detalle) {
            $detalles_array[] = $detalle;
        }

        return [
            'sucursalesVentas' => $sucursalesPorVentas,
            'agrupador' => $this->nameAgrupador,
            'detalles_array' => $detalles_array,

        ];
    }

    public function view(): View
    {
        return view('exports.reporteVentasVSGanancia', $this->collection());
    }

    public function drawings()
    {

        if (session('company')->companies_logo === null || session('company')->companies_logo === '') {
            $logoFile = null;
        } else {
            $logoFile = storage_path('app/empresas/' . session('company')->companies_logo);
            if (!file_exists($logoFile)) {
                $logoFile = null;
            }
        }

        if ($logoFile == null) {
            $logoFile = storage_path('app/empresas/default.png');
        }

        $drawing = new Drawing();
        $drawing->setPath($logoFile);
        $drawing->setWidth(50);
        $drawing->setHeight(50);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

    public function columnWidths(): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        //poner altura a la fila A1
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(50);
            },
        ];
    }

    /**
     * > La función `styles` devuelve un array de estilos para aplicar a la hoja
     * 
     * @param Worksheet La hoja que se está exportando
     * 
     * @return An array of styles.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
