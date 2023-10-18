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

class ReportesVentasProductoMasVendidoExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
{
    public $listaPrecio;
    public $nameArticulo;
    public $categoria;
    public $grupo;
    public $familia;
    public $nameFecha;
    public $nameSucursal;

    public function __construct($listaPrecio, $nameArticulo, $categoria, $grupo, $familia, $nameFecha, $nameSucursal)
    {
        $this->listaPrecio = $listaPrecio;
        $this->nameArticulo = $nameArticulo;
        $this->categoria = $categoria;
        $this->grupo = $grupo;
        $this->familia = $familia;
        $this->nameFecha = $nameFecha;
        $this->nameSucursal = $nameSucursal;
    }

    public function view(): View
    {
        $venta = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
            ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
            ->where('PROC_SALES.sales_movement', '=', 'Factura')
            ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
            ->whereSalesListPrice($this->listaPrecio)
            ->whereSalesArticle($this->nameArticulo)
            ->whereArticleCategory($this->categoria)
            ->whereArticleGroup($this->grupo)
            ->whereArticleFamily($this->familia)
            ->whereSalesDate($this->nameFecha)
            ->whereSalesBranchOffice($this->nameSucursal)
            ->get();

        $collectionVentas = collect($venta);
        $listaPreciosVenta = $collectionVentas->unique('sales_listPrice')->unique()->all();

        $listasPorVentas = [];
        foreach ($listaPreciosVenta as $lista) {
            $ventas = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
                ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
                ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
                ->where('PROC_SALES.sales_movement', '=', 'Factura')
                ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
                ->whereSalesListPrice($lista->sales_listPrice)
                ->whereSalesArticle($this->nameArticulo)
                ->whereArticleCategory($this->categoria)
                ->whereArticleGroup($this->grupo)
                ->whereArticleFamily($this->familia)
                ->whereSalesDate($this->nameFecha)
                ->whereSalesBranchOffice($this->nameSucursal)
                ->get();

            $arrayListaPrecios = $lista->toArray();

            $listasPorVentas[] = array_merge($arrayListaPrecios, ['ventas' => $ventas->toArray()]);
        }

        $detalles = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
            ->join('CAT_BRANCH_OFFICES', 'CAT_BRANCH_OFFICES.branchOffices_key', '=', 'PROC_SALES.sales_branchOffice')
            ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
            ->join('CAT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_article', '=', 'CAT_ARTICLES.articles_key')
            ->whereSalesListPrice($this->listaPrecio)
            ->whereSalesArticle($this->nameArticulo)
            ->whereArticleCategory($this->categoria)
            ->whereArticleGroup($this->grupo)
            ->whereArticleFamily($this->familia)
            ->whereSalesDate($this->nameFecha)
            ->whereSalesBranchOffice($this->nameSucursal)
            ->get();

        $detalles_array = [];
        foreach ($detalles as $key => $detalle) {
            $detalles_array[] = $detalle;
        }



        return view('exports.reporteVentasProductoMasVendido', [
            'listasVentas' => $listasPorVentas,
            'venta' => $venta,
            'detalles_array' => $detalles_array,
            'fecha' => $this->nameFecha,
        ]);
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

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
