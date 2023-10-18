<?php

namespace App\Exports\Reportes;

use App\Models\modulos\PROC_EXPENSES;
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

class ReportesGastosConceptoExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
{
    public $nameMov;
    public $nameProveedor;
    public $nameCategoria;
    public $nameGrupo;
    public $nameConcepto;
    public $nameSucursal;
    public $nameFecha;
    public $nameMoneda;
    public $status;

    public function __construct($nameMov, $nameProveedor, $nameCategoria, $nameGrupo, $nameConcepto, $nameSucursal, $nameFecha, $nameMoneda, $status)
    {
        $this->nameMov = $nameMov;
        $this->nameProveedor = $nameProveedor;
        $this->nameCategoria = $nameCategoria;
        $this->nameGrupo = $nameGrupo;
        $this->nameConcepto = $nameConcepto;
        $this->nameSucursal = $nameSucursal;
        $this->nameFecha = $nameFecha;
        $this->nameMoneda = $nameMoneda;
        $this->status = $status;
    }

    public function view(): View
    {
        $reportes_collection_filtro = PROC_EXPENSES::join('CAT_PROVIDERS', 'PROC_EXPENSES.expenses_provider', '=', 'CAT_PROVIDERS.providers_key')
            ->join('PROC_EXPENSES_DETAILS', 'PROC_EXPENSES.expenses_id', '=', 'PROC_EXPENSES_DETAILS.expensesDetails_expenseID')
            ->join('CAT_BRANCH_OFFICES', 'PROC_EXPENSES.expenses_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CAT_COMPANIES', 'PROC_EXPENSES.expenses_company', '=', 'CAT_COMPANIES.companies_key')
            ->whereExpensesMovement($this->nameMov)
            ->whereExpensesProvider($this->nameProveedor)
            ->whereProviderCategory($this->nameCategoria)
            ->whereProviderGroup($this->nameGrupo)
            ->whereExpensesDetailsConcept($this->nameConcepto)
            ->whereExpensesBranchOffice($this->nameSucursal)
            ->whereExpensesDate($this->nameFecha)
            ->whereExpensesMoney($this->nameMoneda)
            ->whereExpensesStatus($this->status)
            ->where('PROC_EXPENSES.expenses_company', '=', session('company')->companies_key)
            ->where('PROC_EXPENSES.expenses_status', '=', 'FINALIZADO')
            ->orderBy('PROC_EXPENSES.expenses_movementID', 'ASC')

            ->get();

        return view('exports.reporteGastosConcepto', [
            'gastos' => $reportes_collection_filtro,
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

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
            3    => ['font' => ['bold' => true]],
        ];
    }
}
