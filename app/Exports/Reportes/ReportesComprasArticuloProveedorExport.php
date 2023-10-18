<?php

namespace App\Exports\Reportes;

use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
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

class ReportesComprasArticuloProveedorExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
{
    /* Una variable pública que se está utilizando en el constructor. */
    public $nameProveedor;
    public $nameCategoria;
    public $nameGrupo;
    public $nameMov;
    public $nameArticulo;
    public $nameUnidad;
    public $nameFecha;
    public $nameAlmacen;
    public $nameSucursal;
    public $nameMoneda;
    public $status;

    // $nameProveedor, $nameMov,  $nameArticulo, $nameUnidad, $nameFecha, $nameAlmacen, $nameSucursal, $nameMoneda, $status

    /**
     * A constructor function.
     * 
     * @param nameProveedor Es el nombre del proveedor
     * @param nameCategoria Nombre de la categoria
     * @param nameGrupo Nombre del grupo
     * @param nameMov Nombre del movimiento
     * @param nameArticulo Nombre del articulo
     * @param nameUnidad Nombre de la unidad
     * @param nameFecha Fecha de la compra
     * @param nameAlmacen nombre del almacen
     * @param nameSucursal nombre de la sucursal
     * @param nameMoneda Nombre de la moneda
     * @param status Alta o baja
     */


    public function __construct($nameProveedor, $nameCategoria, $nameGrupo, $nameMov, $nameArticulo, $nameUnidad, $nameFecha, $nameAlmacen, $nameSucursal, $nameMoneda, $status)
    {
        $this->nameProveedor = $nameProveedor;
        $this->nameCategoria = $nameCategoria;
        $this->nameGrupo = $nameGrupo;
        $this->nameMov = $nameMov;
        $this->nameArticulo = $nameArticulo;
        $this->nameUnidad = $nameUnidad;
        $this->nameFecha = $nameFecha;
        $this->nameAlmacen = $nameAlmacen;
        $this->nameSucursal = $nameSucursal;
        $this->nameMoneda = $nameMoneda;
        $this->status = $status;
    }

    /**
     * Obtenemos los datos desde la base de datos para después
     * agruparlos por nombre de proveedor.
     * 
     * @return View Se devuelve la vista.
     */
    public function collection()
    {
        $reportes_collection_filtro = PROC_PURCHASE::join(
            'CAT_PROVIDERS',
            'PROC_PURCHASE.purchase_provider',
            '=',
            'CAT_PROVIDERS.providers_key'
        )
            ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CONF_CREDIT_CONDITIONS', 'PROC_PURCHASE.purchase_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
            ->join('CAT_COMPANIES', 'PROC_PURCHASE.purchase_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('CAT_DEPOTS', 'PROC_PURCHASE.purchase_depot', '=', 'CAT_DEPOTS.depots_key')
            ->join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
            ->join('CAT_ARTICLES', 'PROC_PURCHASE_DETAILS.purchaseDetails_article', '=', 'CAT_ARTICLES.articles_key')
            ->join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_unit')
            ->wherePurchaseProvider($this->nameProveedor)
            ->whereProviderCategory($this->nameCategoria)
            ->whereProviderGroup($this->nameGrupo)
            ->wherePurchaseMovement($this->nameMov)
            ->wherePurchaseArticle($this->nameArticulo)
            ->wherePurchaseUnit($this->nameUnidad)
            ->wherePurchaseDepot($this->nameAlmacen)
            ->wherePurchaseBranchOffice($this->nameSucursal)
            ->wherePurchaseDate($this->nameFecha)
            ->wherePurchaseMoney($this->nameMoneda)
            ->wherePurchaseStatus($this->status)
            ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
            ->where('PROC_PURCHASE.purchase_movement', '=', 'Entrada por Compra')
            ->where('PROC_PURCHASE.purchase_status', '=', 'FINALIZADO')
            ->orderBy('PROC_PURCHASE.updated_at', 'DESC')
            ->get();



        /* Agrupamos los datos por proveedor. */
        $collectionCompras = collect($reportes_collection_filtro);
        $proveedoresCompra = $collectionCompras->unique('purchase_provider')->unique()->all();

        $sucursal_almacen = $collectionCompras->unique('purchase_branchOffice')->unique()->first();


        $proveedorPorCompras = [];
        /* Un bucle foreach que itera sobre la colección.. */
        foreach ($proveedoresCompra as $proveedor) {

            $compras = PROC_PURCHASE::join(
                'CAT_PROVIDERS',
                'PROC_PURCHASE.purchase_provider',
                '=',
                'CAT_PROVIDERS.providers_key'
            )
                ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                ->join('CONF_CREDIT_CONDITIONS', 'PROC_PURCHASE.purchase_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
                ->join('CAT_COMPANIES', 'PROC_PURCHASE.purchase_company', '=', 'CAT_COMPANIES.companies_key')
                ->join('CAT_DEPOTS', 'PROC_PURCHASE.purchase_depot', '=', 'CAT_DEPOTS.depots_key')
                ->join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
                ->join('CAT_ARTICLES', 'PROC_PURCHASE_DETAILS.purchaseDetails_article', '=', 'CAT_ARTICLES.articles_key')
                ->join('CONF_UNITS', 'CONF_UNITS.units_unit', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_unit')
                ->wherePurchaseProvider($proveedor->purchase_provider)
                ->whereProviderCategory($this->nameCategoria)
                ->whereProviderGroup($this->nameGrupo)
                ->wherePurchaseMovement($this->nameMov)
                ->wherePurchaseArticle($this->nameArticulo)
                ->wherePurchaseUnit($this->nameUnidad)
                ->wherePurchaseDepot($this->nameAlmacen)
                ->wherePurchaseBranchOffice($this->nameSucursal)
                ->wherePurchaseDate($this->nameFecha)
                ->wherePurchaseMoney($this->nameMoneda)
                ->wherePurchaseStatus($this->status)
                ->where('PROC_PURCHASE.purchase_movement', '=', 'Entrada por Compra')
                ->where('PROC_PURCHASE.purchase_status', '=', 'FINALIZADO')
                ->orderBy('PROC_PURCHASE.updated_at', 'DESC')
                ->get();


            /* Fusión del array del proveedor con el array de las compras. */
            $arrayProveedores = $proveedor->toArray();
            $proveedorPorCompras[] = array_merge($arrayProveedores, ['compras' => $compras->toArray()]);
        }

        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->orderBy('generalParameters_id', 'desc')->get();

        return [
            'proveedorCompras' => $proveedorPorCompras,
            'moneda' => $this->nameMoneda,
            'fecha' => $this->nameFecha,
            'movimiento' => $this->nameMov,
            'unidad' => $this->nameUnidad,
            'almacen' => $this->nameAlmacen,
            'sucursal' => $this->nameSucursal,
            'status' => $this->status,

            'parametro' => $parametro,
        ];
    }

    public function view(): View
    {
        return view('exports.reporteComprasArticuloProveedor', $this->collection());
    }
    public function drawings()
    {

        if (session('company')->companies_logo === null || session('company')->companies_logo === '') {
            $logoFile = null;
        } else {
            $logoFile = storage_path('app/empresas/' . session('company')->companies_logo);
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
