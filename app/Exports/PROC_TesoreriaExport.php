<?php

namespace App\Exports;

use App\Models\modulos\PROC_TREASURY;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PROC_TesoreriaExport implements FromView, ShouldAutoSize, WithStyles
{
   public $nameFolio;
   public $nameMov;
   public $cuentasDinero;
   public $status;
   public $nameFecha;
   public $nameUsuario;
   public $nameSucursal;
   public $nameMoneda;

   public function __construct($nameFolio, $nameMov, $cuentasDinero, $status, $nameFecha, $nameUsuario, $nameSucursal, $nameMoneda)
   {
       $this->nameFolio = $nameFolio;
       $this->nameMov = $nameMov;
       $this->cuentasDinero = $cuentasDinero;
       $this->status = $status;
       $this->nameFecha = $nameFecha;
       $this->nameUsuario = $nameUsuario;
       $this->nameSucursal = $nameSucursal;
       $this->nameMoneda = $nameMoneda;
   }

   public function view(): View
   {
    $tesoreria_collection_filtro = PROC_TREASURY::join('CAT_PROVIDERS', 'PROC_TREASURY.treasuries_beneficiary', '=', 'CAT_PROVIDERS.providers_key', 'left')
                ->join('CAT_BRANCH_OFFICES', 'PROC_TREASURY.treasuries_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                ->join('CAT_COMPANIES', 'PROC_TREASURY.treasuries_company', '=', 'CAT_COMPANIES.companies_key')
                ->join('CONF_MONEY', 'PROC_TREASURY.treasuries_money', '=', 'CONF_MONEY.money_key')
                ->join('CAT_MONEY_ACCOUNTS', 'PROC_TREASURY.treasuries_moneyAccount', '=', 'CAT_MONEY_ACCOUNTS.moneyAccounts_key', 'left')
                ->join('CONF_FORMS_OF_PAYMENT', 'PROC_TREASURY.treasuries_paymentMethod', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key')
                ->where('PROC_TREASURY.treasuries_company', '=', session('company')->companies_key)
                ->whereTreasuriesMovementID($this->nameFolio)
                ->whereTreasuriesMovement($this->nameMov)
                ->whereTreasuriesMoneyAccount($this->cuentasDinero)
                ->whereTreasuriesStatus($this->status)
                ->whereTreasuriesDate($this->nameFecha)
                ->whereTreasuriesUser($this->nameUsuario)
                ->whereTreasuriesBranchOffice($this->nameSucursal)
                ->whereTreasuriesMoney($this->nameMoneda)
                ->orderBy('PROC_TREASURY.updated_at', 'desc')

                ->get();

                return view('exports.Tesoreria', [
                    'tesoreria' => $tesoreria_collection_filtro,
                ]);
   }

   public function styles(Worksheet $sheet)
   {
       return [
           1 => [ 'font' => [ 'bold' => true ] ],
       ];
   }

}
