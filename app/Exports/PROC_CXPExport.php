<?php

namespace App\Exports;

use App\Models\modulos\PROC_ACCOUNTS_PAYABLE;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PROC_CXPExport implements FromView, ShouldAutoSize, WithStyles
{
    public $nameFolio;
    public $nameKey;
    public $nameMov;
    public $estatus;
    public $fecha;
    public $nameUsuario;
    public $nameSucursal; 
    public $nameMoneda ;

    public function __construct($nameFolio, $nameKey, $nameMov, $estatus, $fecha, $nameUsuario, $nameSucursal, $nameMoneda)
    {
        $this->nameFolio = $nameFolio;
        $this->nameKey = $nameKey;
        $this->nameMov = $nameMov;
        $this->estatus = $estatus;
        $this->fecha = $fecha;
        $this->nameUsuario = $nameUsuario;
        $this->nameSucursal = $nameSucursal;
        $this->nameMoneda = $nameMoneda;
    }

    public function view(): View
    {
        $CXP_collection_filtro = PROC_ACCOUNTS_PAYABLE::join('CAT_PROVIDERS', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_provider', '=', 'CAT_PROVIDERS.providers_key')
        ->join('CAT_BRANCH_OFFICES', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
        ->join('CONF_CREDIT_CONDITIONS', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id', 'left outer')
        ->join('CAT_COMPANIES', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_company', '=', 'CAT_COMPANIES.companies_key')
        ->join('CONF_FORMS_OF_PAYMENT', 'PROC_ACCOUNTS_PAYABLE.accountsPayable_formPayment', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key', 'left outer')
        ->whereAccountsPayableMovementID($this->nameFolio)
        ->whereAccountsPayableProvider($this->nameKey)
        ->whereAccountsPayableMovement($this->nameMov)
        ->whereAccountsPayableStatus($this->estatus)
        ->whereAccountsPayableDate($this->fecha)
        ->whereAccountsPayableUser($this->nameUsuario)
        ->whereAccountsPayablebranchOffice($this->nameSucursal)
        ->whereAccountsPayableMoney($this->nameMoneda)
        // ->whereBalancebranchKey($this->nameSucursal)
        ->where('PROC_ACCOUNTS_PAYABLE.accountsPayable_company', '=', session('company')->companies_key)
        ->orderBy('PROC_ACCOUNTS_PAYABLE.updated_at', 'DESC')
        ->get()->unique();


        return view('exports.CXP', [
            'cuentasxpagar' => $CXP_collection_filtro,
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
