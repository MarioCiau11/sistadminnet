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


/* Es una clase que implementa la interfaz FromView, lo que significa que tiene un método view() que devuelve
un objeto View */

class ReportesVentasSeriesExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
{
    /* Una variable pública que se está utilizando en el constructor. */
    public $nameCliente;
    public $nameArticulo;
    public $nameFecha;
    public $nameSucursal;
    public $nameMoneda;
    public $series;

    // $nameCliente, $nameMov,  $nameArticulo, $nameUnidad, $nameFecha, $nameAlmacen, $nameSucursal, $nameMoneda, $status

    /**
     * A constructor function.
     * 
     * @param nameCliente Es el nombre del proveedor
     * @param nameArticulo Nombre del articulo
     * @param nameFecha Fecha de la compra
     * @param nameSucursal nombre de la sucursal
     * @param nameMoneda Nombre de la moneda
     * @param series Alta o baja
     */


    public function __construct($nameCliente, $nameArticulo, $nameFecha, $nameSucursal, $nameMoneda, $series)
    {
        $this->nameCliente = $nameCliente;
        $this->nameArticulo = $nameArticulo;
        $this->nameFecha = $nameFecha;
        $this->nameSucursal = $nameSucursal;
        $this->nameMoneda = $nameMoneda;
        $this->series = $series;
    }

    /**
     * Obtenemos los datos desde la base de datos para después
     * agruparlos por nombre de proveedor.
     * 
     * @return View Se devuelve la vista.
     */
    public function view(): View
    {
        $reportes_collection_filtro =  PROC_SALES::join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
            ->join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
            ->join('CAT_BRANCH_OFFICES', 'PROC_SALES.sales_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('PROC_DEL_SERIES_MOV2', 'PROC_SALES_DETAILS.salesDetails_id', 'PROC_DEL_SERIES_MOV2.delSeriesMov2_articleID')->where('sales_status', '=', 'FINALIZADO')->where('sales_movement', '=', 'Factura')
            ->where('PROC_SALES_DETAILS.salesDetails_type', '=', 'Serie')
            ->where('PROC_SALES.sales_company', '=', session('company')->companies_key)
            ->whereSalesCustomer($this->nameCliente)
            ->whereSalesArticle($this->nameArticulo)
            ->whereSalesDate($this->nameFecha)
            ->whereSalesBranchOffice($this->nameSucursal)
            ->whereSalesMoney($this->nameMoneda)
            ->whereSalesSeries($this->series)
            ->orderBy('PROC_SALES.updated_at', 'DESC')
            ->get();

        $kit = PROC_SALES::join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
        ->join('PROC_KIT_ARTICLES', 'PROC_SALES_DETAILS.salesDetails_id', '=', 'PROC_KIT_ARTICLES.procKit_articleIDReference')
        ->join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
        ->join('CAT_BRANCH_OFFICES', 'PROC_SALES.sales_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
        ->where('salesDetails_type', '=', 'Kit')
        ->where('procKit_tipo', '=', 'Serie')
        ->where('sales_status', '=', 'FINALIZADO')
            ->whereSalesCustomer($this->nameCliente)
            ->whereSalesArticle($this->nameArticulo)
            ->whereSalesDate($this->nameFecha)
            ->whereSalesBranchOffice($this->nameSucursal)
            ->whereSalesMoney($this->nameMoneda)
            ->get();

        foreach ($kit as $componente) {
            if ($componente->procKit_tipo == "Serie") {
                $seriesComponentes = PROC_SALES::join('PROC_SALES_DETAILS', 'PROC_SALES.sales_id', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
                    ->join('PROC_DEL_SERIES_MOV2', 'PROC_SALES_DETAILS.salesDetails_saleID', 'PROC_DEL_SERIES_MOV2.delSeriesMov2_saleID')
                    ->join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
                    ->join('CAT_BRANCH_OFFICES', 'PROC_SALES.sales_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_DEL_SERIES_MOV2.delSeriesMov2_article')
                ->whereSalesBranchOffice($this->nameSucursal)->WHERE('delSeriesMov2_article', '=', $componente->procKit_articleID)->WHERE('delSeriesMov2_articleID', '=', $componente->procKit_article)->WHERE('delSeriesMov2_saleID', '=', $componente->salesDetails_saleID)->WHERE('delSeriesMov2_module', '=', 'Ventas')

                //sales_movement, sales_movementID, salesDetails_article, salesDetails_descript, sales_issuedate, sales_reference, customers_key, customers_businessName, salesDetails_unit, delSeriesMov2_lotSerie
                ->select('PROC_SALES.sales_id', 'PROC_SALES.sales_movement', 'PROC_SALES.sales_movementID', 'CAT_ARTICLES.articles_key', 'CAT_ARTICLES.articles_descript', 'PROC_SALES.sales_issuedate', 'PROC_SALES.sales_reference', 'CAT_CUSTOMERS.customers_key', 'CAT_CUSTOMERS.customers_businessName', 'PROC_SALES_DETAILS.salesDetails_unit', 'PROC_DEL_SERIES_MOV2.delSeriesMov2_lotSerie')
                ->get();
                // dd($seriesComponentes);


                if (count($seriesComponentes) > 0) {
                    foreach ($seriesComponentes as $seriesC) {
                        //no queremos pasarlo array así que solo lo dejamos tal cual para que forme el formato de tabla
                        $seriesArticulosVendidos[] = $seriesC->toArray();
                    }
                } else {
                    //si no hay series, entonces no pasamos nada, pero como necesitamos la variable, la declaramos vacía
                    $seriesArticulosVendidos = [];
                }
            }
        }

        $reportes_collection_filtro = $reportes_collection_filtro->toArray();



        return view('exports.reporteVentasSeries', [
            'ventas' => $reportes_collection_filtro,
            'kits' => $seriesArticulosVendidos,
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
