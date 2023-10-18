<?php

namespace App\Exports\Reportes;

use App\Models\modulos\PROC_PURCHASE;
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

class ReportesComprasSeriesExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
{
    /* Una variable pública que se está utilizando en el constructor. */
    public $nameProveedor;
    public $nameArticulo;
    public $nameFecha;
    public $nameSucursal;
    public $nameMoneda;
    public $series;

    // $nameProveedor, $nameMov,  $nameArticulo, $nameUnidad, $nameFecha, $nameAlmacen, $nameSucursal, $nameMoneda, $status

    /**
     * A constructor function.
     * 
     * @param nameProveedor Es el nombre del proveedor
     * @param nameArticulo Nombre del articulo
     * @param nameFecha Fecha de la compra
     * @param nameSucursal nombre de la sucursal
     * @param nameMoneda Nombre de la moneda
     * @param series Alta o baja
     */


    public function __construct($nameProveedor, $nameArticulo, $nameFecha, $nameSucursal, $nameMoneda, $series)
    {
        $this->nameProveedor = $nameProveedor;
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
        $reportes_collection_filtro =  PROC_PURCHASE::join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
            ->join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
            ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('PROC_LOT_SERIES_MOV', 'PROC_PURCHASE_DETAILS.purchaseDetails_id', 'PROC_LOT_SERIES_MOV.LOTSeriesMov_articleID')
            ->where('purchase_status', '=', 'FINALIZADO')->where('purchase_movement', '=', 'Entrada por Compra')
            ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
            ->wherePurchaseProvider($this->nameProveedor)
            ->wherePurchaseArticle($this->nameArticulo)
            ->wherePurchaseDate($this->nameFecha)
            ->wherePurchaseBranchOffice($this->nameSucursal)
            ->wherePurchaseMoney($this->nameMoneda)
            ->wherePurchaseSeries($this->series)
            ->orderBy('PROC_PURCHASE.updated_at', 'DESC')
            ->get();


        return view('exports.reportesComprasSeries', [
            'compras' => $reportes_collection_filtro
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
