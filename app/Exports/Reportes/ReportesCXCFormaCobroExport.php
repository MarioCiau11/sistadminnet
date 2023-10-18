<?php

namespace App\Exports\Reportes;

use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE;
use App\Models\modulos\PROC_SALES;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


/* Es una clase que implementa la interfaz FromView, lo que significa que tiene un método view() que devuelve
un objeto View */

class ReportesCXCFormaCobroExport implements FromView, ShouldAutoSize, WithStyles
{
    /* Una variable pública que se está utilizando en el constructor. */

    public $nameFecha;
    public $nameSucursal;
    public $nameMoneda;


    // $nameCliente, $nameMov,  $nameArticulo, $nameUnidad, $nameFecha, $nameAlmacen, $nameSucursal, $nameMoneda, $status

    /**
     * A constructor function.
     * 

     * @param nameFecha Fecha de la compra
     * @param nameSucursal nombre de la sucursal
     * @param nameMoneda Nombre de la moneda
  
     */


    public function __construct($nameFecha, $nameSucursal, $nameMoneda)
    {

        $this->nameFecha = $nameFecha;
        $this->nameSucursal = $nameSucursal;
        $this->nameMoneda = $nameMoneda;
    }

    /**
     * Obtenemos los datos desde la base de datos para después
     * agruparlos por nombre de proveedor.
     * 
     * @return View Se devuelve la vista.
     */
    public function view(): View
    {

        $ventas_contado = PROC_SALES::join('PROC_SALES_PAYMENT', 'PROC_SALES.sales_id', '=', 'PROC_SALES_PAYMENT.salesPayment_saleID')
            ->join('CAT_BRANCH_OFFICES', 'PROC_SALES.sales_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CONF_FORMS_OF_PAYMENT', 'PROC_SALES_PAYMENT.salesPayment_paymentMethod1', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key')
            ->where('sales_company', '=', session('company')->companies_key)
            ->where('sales_status', '=', 'FINALIZADO')->where('sales_movement', '=', 'Factura')->where('sales_typeCondition', '=', 'Contado')
            ->whereSalesDate($this->nameFecha)
            ->whereSalesBranchOffice($this->nameSucursal)
            ->whereSalesMoney($this->nameMoneda)->get();

        $cobros_credido = PROC_ACCOUNTS_RECEIVABLE::join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CONF_FORMS_OF_PAYMENT', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_formPayment', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key')
            ->where('accountsReceivable_company', '=', session('company')->companies_key)->where('accountsReceivable_status', '=', 'FINALIZADO')->where('accountsReceivable_movement', '=', 'Cobro de Facturas')
            ->whereAccountsReceivableDate($this->nameFecha)
            ->whereAccountsReceivablebranchOffice($this->nameSucursal)
            ->whereAccountsReceivableMoney($this->nameMoneda)->get();



        $movimientos = [];

        foreach ($ventas_contado as $venta) {
            $movimientos[] = $venta;
        }

        foreach ($cobros_credido as $cobro) {
            $movimientos[] = $cobro;
        }



        return view('exports.reporteCXCFormaCobro', [
            'movimientos' => $movimientos,
            'cobros_credido' => $cobros_credido,
            'ventas_contado' => $ventas_contado,
        ]);
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
