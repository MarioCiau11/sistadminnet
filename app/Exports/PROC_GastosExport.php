<?php

namespace App\Exports;

use App\Models\modulos\PROC_EXPENSES;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class PROC_GastosExport implements FromView, ShouldAutoSize, WithStyles
{
   public $nameFolio;
   public $nameKey;
   public $nameMov;
   public $Estatus;
   public $Fecha;
   public $nameUsuario;
   public $nameSucursal; 
   public $nameMoneda ;

   public function __construct($nameFolio, $nameKey, $nameMov, $Estatus, $Fecha, $nameUsuario, $nameSucursal, $nameMoneda)
   {
      $this->nameFolio = $nameFolio;
      $this->nameKey = $nameKey;
      $this->nameMov = $nameMov;
      $this->Estatus = $Estatus;
      $this->Fecha = $Fecha;
      $this->nameUsuario = $nameUsuario;
      $this->nameSucursal = $nameSucursal;
      $this->nameMoneda = $nameMoneda;
   }

   public function view(): View
   {
    $gastos_collection_filtro = PROC_EXPENSES::join('CAT_PROVIDERS', 'PROC_EXPENSES.expenses_provider', '=', 'CAT_PROVIDERS.providers_key')
    ->join('CAT_BRANCH_OFFICES', 'PROC_EXPENSES.expenses_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
    ->join('CONF_CREDIT_CONDITIONS', 'PROC_EXPENSES.expenses_condition', '=', 'CONF_CREDIT_CONDITIONS.creditConditions_id')
    ->join('CONF_FORMS_OF_PAYMENT', 'PROC_EXPENSES.expenses_paymentMethod', '=', 'CONF_FORMS_OF_PAYMENT.formsPayment_key')
    ->join('CAT_COMPANIES', 'PROC_EXPENSES.expenses_company', '=', 'CAT_COMPANIES.companies_key')
    ->where('PROC_EXPENSES.expenses_company', '=', session('company')->companies_key)
    ->whereExpensesMovementID($this->nameFolio)
    ->whereExpensesProvider($this->nameKey)
    ->whereExpensesMovement($this->nameMov)
    ->whereExpensesStatus($this->Estatus)
    ->whereExpensesDate($this->Fecha)
    ->whereExpensesUser($this->nameUsuario)
    ->whereExpensesbranchOffice($this->nameSucursal)
    ->whereExpensesMoney($this->nameMoneda)
    ->orderBy('PROC_EXPENSES.updated_at', 'DESC')
    ->get();

    return view('exports.Gastos', [
        'gastos' => $gastos_collection_filtro,
    ]);
}

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [ 'font' => [ 'bold' => true ] ],
        ];
    }
}
