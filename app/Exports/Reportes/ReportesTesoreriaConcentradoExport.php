<?php

namespace App\Exports\Reportes;

use App\Models\modulos\helpers\PROC_MONEY_ACCOUNTS_BALANCE;
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
use stdClass;

class ReportesTesoreriaConcentradoExport implements  FromView, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
{
    public $nameCuenta;
    public $nameFecha;
    public $nameMoneda;
    public $nameMov;

    public function __construct($nameCuenta, $nameFecha, $nameMoneda, $nameMov)
    {
        $this->nameCuenta = $nameCuenta;
        $this->nameFecha = $nameFecha;
        $this->nameMoneda = $nameMoneda;
        $this->nameMov = $nameMov;
    }

    public function view(): View
    {
        
            $tesoreria = PROC_MONEY_ACCOUNTS_BALANCE::join('CAT_MONEY_ACCOUNTS',  'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_moneyAccount', '=','CAT_MONEY_ACCOUNTS.moneyAccounts_key')
            ->join('PROC_ASSISTANT','PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_moneyAccount', '=',  'PROC_ASSISTANT.assistant_account',)
            ->join('CAT_COMPANIES', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_company', '=', 'CAT_COMPANIES.companies_key')
            ->whereMoneyAccountsBalanceMoneyAccount($this->nameCuenta)
            ->whereMoneyAccountsBalanceMoney($this->nameMoneda)
            ->where('moneyAccountsBalance_company', '=', session('company')->companies_key)
            ->get();

            $cuentasUnicas = [];
            foreach ($tesoreria as $key => $tesoreriaCuenta) {
                $jsonCuentas = new stdClass();
                       
               if(array_key_exists($tesoreriaCuenta->moneyAccounts_key,$cuentasUnicas)){
                //si existe
                $lastCharge =  (float) $cuentasUnicas[$tesoreriaCuenta->moneyAccounts_key]->assistant_charge;
                $newCharge = (float) $tesoreriaCuenta->assistant_charge;

                $lastPayment =  (float) $cuentasUnicas[$tesoreriaCuenta->moneyAccounts_key]->assistant_payment;
                $newPayment = (float) $tesoreriaCuenta->assistant_payment;

                if($tesoreriaCuenta->assistant_charge !== null ){
                    $cuentasUnicas[$tesoreriaCuenta->moneyAccounts_key]->assistant_charge = $lastCharge + $newCharge;
                }

                 if($tesoreriaCuenta->assistant_payment !== null ){
                    $cuentasUnicas[$tesoreriaCuenta->moneyAccounts_key]->assistant_payment = $lastPayment + $newPayment;
                }
               }else{

                $jsonCuentas->moneyAccounts_key = $tesoreriaCuenta->moneyAccounts_key;
                $jsonCuentas->moneyAccounts_numberAccount = $tesoreriaCuenta->moneyAccounts_numberAccount;
                $jsonCuentas->moneyAccounts_referenceBank = $tesoreriaCuenta->moneyAccounts_referenceBank;
                $jsonCuentas->moneyAccountsBalance_balance = $tesoreriaCuenta->moneyAccountsBalance_balance;
                $jsonCuentas->assistant_charge = $tesoreriaCuenta->assistant_charge;
                $jsonCuentas->assistant_payment = $tesoreriaCuenta->assistant_payment;
                $jsonCuentas->moneyAccountsBalance_moneyAccount = $tesoreriaCuenta->moneyAccountsBalance_moneyAccount;
                $jsonCuentas->moneyAccountsBalance_initialBalance    = $tesoreriaCuenta->moneyAccountsBalance_initialBalance;

                            
                 $cuentasUnicas[$tesoreriaCuenta->moneyAccounts_key] = $jsonCuentas;
                            
               }
            }
            
        
            return view('exports.reporteTesoreriaConcentrado', [
                'tesorerias' => $cuentasUnicas,
            ]);

            
    }

    public function drawings()
    {

        if (session('company')->companies_logo === null || session('company')->companies_logo === '') {
            $logoFile = null;
        } else {
            $logoFile = storage_path('app/empresas/' . session('company')->companies_logo);
            if(!file_exists($logoFile)) {
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
        return [
       
        ];
    }

    public function registerEvents(): array
    {
        //poner altura a la fila A1
        return [
            AfterSheet::class => function(AfterSheet $event) {
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
