<?php

namespace App\Exports\Reportes;

use App\Models\modulos\PROC_PURCHASE;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportesComprasUnidadExport implements FromView, ShouldAutoSize, WithStyles
{
    /** 
     * Este metodo es el encargado de generar el reporte de compras por unidad
     * Además tenemos una variable publica que se esta utilizando en el constructor
     */
    public $nameMov;
    public $nameUnidad;
    public $nameAlmacen;
    public $nameSucursal;
    public $nameFecha;
    public $nameMoneda;
    public $status;

    /** 
     * A constructor function.
     * 
     * @param nameMov Nombre del movimiento
     * @param nameUnidad Nombre de la unidad
     * @param nameAlmacen Nombre del almacen
     * @param nameSucursal Nombre de la sucursal
     * @param nameFecha Fecha de la compra
     * @param nameMoneda Nombre de la moneda
     * @param status Alta o baja
     */
    public function __construct($nameMov, $nameUnidad, $nameAlmacen, $nameSucursal, $nameFecha, $nameMoneda, $status)
    {
        $this->nameMov = $nameMov;
        $this->nameUnidad = $nameUnidad;
        $this->nameAlmacen = $nameAlmacen;
        $this->nameSucursal = $nameSucursal;
        $this->nameFecha = $nameFecha;
        $this->nameMoneda = $nameMoneda;
        $this->status = $status;
    }

    /**
     * 
     * function view que es la encargada de generar el reporte
     * 
     */
    public function view(): View
    {
        $reportes_collection_filtro = PROC_PURCHASE::join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
        ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
        ->join('CONF_CREDIT_CONDITIONS', 'PROC_PURCHASE.purchase_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
        ->join('CAT_COMPANIES', 'PROC_PURCHASE.purchase_company', '=', 'CAT_COMPANIES.companies_key')
        ->join('CAT_DEPOTS', 'PROC_PURCHASE.purchase_depot', '=', 'CAT_DEPOTS.depots_key')
        ->join('PROC_PURCHASE_DETAILS', 'PROC_PURCHASE.purchase_id', '=', 'PROC_PURCHASE_DETAILS.purchaseDetails_purchaseID')
        ->join('PROC_ARTICLES_COST', 'PROC_PURCHASE_DETAILS.purchaseDetails_article', '=', 'PROC_ARTICLES_COST.articlesCost_article')
        
        /**
         * Aqui se hace la consulta a la base de datos para obtener los datos
         * Los filtros se hacen en el constructor de la clase
         */
        ->wherePurchaseMovement($this->nameMov)
        ->wherePurchaseUnit($this->nameUnidad)
        ->wherePurchaseDepot($this->nameAlmacen)
        ->wherePurchaseBranchOffice($this->nameSucursal)
        ->wherePurchaseDate($this->nameFecha)
        ->wherePurchaseMoney($this->nameMoneda)
        ->wherePurchaseStatus($this->status)
        ->orderBy('PROC_PURCHASE.updated_at', 'DESC')
        ->where('PROC_PURCHASE.purchase_company', '=', session('company')->companies_key)
        ->get();

        /**
         * Aqui se retorna la vista con los datos obtenidos de la consulta
         * La vista se encuentra en la carpeta exports
         * El nombre de la vista es reporteComprasUnidad
         * La variable que se esta pasando es compras
         * La variable compras contiene los datos de la consulta
         */
        return view('exports.reporteComprasUnidad', [
            'compras' => $reportes_collection_filtro,
        ]);

    }

    /**
     * 
     * function styles que es la encargada de darle estilo a la tabla
     * 
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
