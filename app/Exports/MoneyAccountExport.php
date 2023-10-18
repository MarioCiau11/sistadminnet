<?php

namespace App\Exports;

use App\Models\catalogos\CAT_FINANCIAL_INSTITUTIONS;
use App\Models\catalogos\CAT_MONEY_ACCOUNTS;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class MoneyAccountExport implements FromView, ShouldAutoSize, WithStyles
{
    public $keyCDinero;
    public $nameBanco;
    public $numeroCuenta;
    public $typeCuenta;
    public $status;

    public function __construct($keyCDinero, $nameBanco,  $numeroCuenta, $typeCuenta, $status)
    {
        $this->keyCDinero = $keyCDinero;
        $this->nameBanco = $nameBanco;
        $this->numeroCuenta = $numeroCuenta;
        $this->typeCuenta = $typeCuenta;
        $this->status = $status;

    }

    public function view(): View
    {
        $moneyAccounts =  CAT_MONEY_ACCOUNTS::join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_company')
        ->join('CAT_FINANCIAL_INSTITUTIONS', 'CAT_FINANCIAL_INSTITUTIONS.instFinancial_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_bank')
        ->join('CONF_MONEY', 'CONF_MONEY.money_key', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_money')
        ->select('CAT_MONEY_ACCOUNTS.*', 'CAT_COMPANIES.companies_name', 'CAT_FINANCIAL_INSTITUTIONS.instFinancial_name', 
        'CONF_MONEY.money_key')
        ->whereMoneyAccountsKey($this->keyCDinero)
        ->whereInstFinancialName($this->nameBanco)
        ->whereMoneyAccountsNumberAccount($this->numeroCuenta)
        ->whereMoneyAccountsStatus($this->status)
        ->whereMoneyAccountsAccountType($this->typeCuenta)
        ->get();
                   
        return view('exports.MoneyAccounts', [
            'moneyAccounts' => $moneyAccounts,
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
