<?php

namespace App\Exports\Reportes;

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

class ReportesComprasAcumuladasExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
{
    public $nameProveedorUno;
    public $nameProveedorDos;
    public $nameCategoria;
    public $nameGrupo;
    public $nameMoneda;
    public $nameEjercicio;
    public $nameArticuloUno;
    public $nameArticuloDos;
    public $categoria;
    public $grupo;
    public $familia;

    public function __construct($nameProveedorUno, $nameProveedorDos, $nameCategoria, $nameGrupo, $nameMoneda, $nameEjercicio, $nameArticuloUno, $nameArticuloDos, $categoria, $grupo, $familia)
    {
        $this->nameProveedorUno = $nameProveedorUno;
        $this->nameProveedorDos = $nameProveedorDos;
        $this->nameCategoria = $nameCategoria;
        $this->nameGrupo = $nameGrupo;
        $this->nameMoneda = $nameMoneda;
        $this->nameEjercicio = $nameEjercicio;
        $this->nameArticuloUno = $nameArticuloUno;
        $this->nameArticuloDos = $nameArticuloDos;
        $this->categoria = $categoria;
        $this->grupo = $grupo;
        $this->familia = $familia;
    }

    public function view(): View
    {
        $compra = PROC_PURCHASE::join('CAT_PROVIDERS', 'PROC_PURCHASE.purchase_provider', '=', 'CAT_PROVIDERS.providers_key')
            ->join('CAT_BRANCH_OFFICES', 'PROC_PURCHASE.purchase_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->join('CAT_COMPANIES', 'PROC_PURCHASE.purchase_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('PROC_ASSISTANT_UNITS', 'PROC_PURCHASE.purchase_movementID', '=', 'PROC_ASSISTANT_UNITS.assistantUnit_movementID')
            ->join('CAT_ARTICLES', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'CAT_ARTICLES.articles_key')
            ->where('PROC_ASSISTANT_UNITS.assistantUnit_movement', '=', 'Entrada por Compra')
            ->where('PROC_PURCHASE.purchase_status', '=', 'FINALIZADO')
            ->where('PROC_ASSISTANT_UNITS.assistantUnit_canceled', '=', '0')
            ->wherePurchaseNameProvider($this->nameProveedorUno, $this->nameProveedorDos)
            ->whereProviderCategory($this->nameCategoria)
            ->whereProviderGroup($this->nameGrupo)
            ->wherePurchaseMoney($this->nameMoneda)
            ->whereAssistantUnitYear($this->nameEjercicio)
            ->whereAssistantUnitAccount($this->nameArticuloUno, $this->nameArticuloDos)
            ->whereArticleCategory($this->categoria)
            ->whereArticleGroup($this->grupo)
            ->whereArticleFamily($this->familia)
            ->orderBy('articles_id', 'ASC')
            ->get();

        return view('exports.reporteComprasAcumuladas', [
            'compras' => $compra,
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
        ];
    }
}
