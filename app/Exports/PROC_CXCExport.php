<?php

namespace App\Exports;

use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PROC_CXCExport implements FromView, ShouldAutoSize, WithStyles
{
    public $nameFolio;
    public $nameKey;
    public $nameMov;
    public $estatus;
    public $nameFecha;
    public $nameFechaVen;
    public $nameUsuario;
    public $nameSucursal; 
    public $nameMoneda ;
    public $timbrado;

    public function __construct($nameFolio, $nameKey, $nameMov, $estatus, $nameFecha, $nameFechaVen, $nameUsuario, $nameSucursal, $nameMoneda, $timbrado)
    {
        $this->nameFolio = $nameFolio;
        $this->nameKey = $nameKey;
        $this->nameMov = $nameMov;
        $this->estatus = $estatus;
        $this->nameFecha = $nameFecha;
        $this->nameFechaVen = $nameFechaVen;
        $this->nameUsuario = $nameUsuario;
        $this->nameSucursal = $nameSucursal;
        $this->nameMoneda = $nameMoneda;
        $this->timbrado = $timbrado;
    }

    public function view(): View
    {
        $CXC_collection_filtro = PROC_ACCOUNTS_RECEIVABLE::join('CAT_CUSTOMERS', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_customer', '=', 'CAT_CUSTOMERS.customers_key', 'left outer')
        ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key', 'left outer')
        ->join('CONF_CREDIT_CONDITIONS', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id', 'left outer')
        ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_company', '=', 'CAT_COMPANIES.companies_key', 'left outer')
        ->join('CONF_FORMS_OF_PAYMENT', 'PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_formPayment', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key', 'left outer')
        ->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_company', '=', session('company')->companies_key)
        ->whereAccountsReceivableMovementID($this->nameFolio)
        ->whereAccountsReceivableCustomer($this->nameKey)
        ->whereAccountsReceivableMovement($this->nameMov)
        ->whereAccountsReceivableStatus($this->estatus)
        ->whereAccountsReceivableDate($this->nameFecha)
        ->whereAccountsReceivableExpiration($this->nameFechaVen)
        ->whereAccountsReceivableUser($this->nameUsuario)
        ->whereAccountsReceivablebranchOffice($this->nameSucursal)
        ->whereAccountsReceivableMoney($this->nameMoneda)
         ->whereAccountsReceivableStamped($this->timbrado)
        ->where('PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_company', '=', session('company')->companies_key)
        ->orderBy('PROC_ACCOUNTS_RECEIVABLE.updated_at', 'DESC')
        ->get()
        ->unique();

        return view('exports.CXC', [
            'cuentasxcobrar' => $CXC_collection_filtro
        ]);

    }

    public function styles(Worksheet $worksheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}
