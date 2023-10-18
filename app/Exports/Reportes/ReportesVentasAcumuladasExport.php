<?php

namespace App\Exports\Reportes;

use App\Models\modulos\PROC_SALES;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportesVentasAcumuladasExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithEvents
{
    public $nameClienteUno;
    public $nameClienteDos;
    public $nameCategoria;
    public $nameGrupo;
    public $nameMoneda;
    public $nameEjercicio;
    public $nameArticuloUno;
    public $nameArticuloDos;
    public $categoria;
    public $grupo;
    public $familia;

    public function __construct($nameClienteUno, $nameClienteDos, $nameCategoria, $nameGrupo, $nameMoneda, $nameEjercicio, $nameArticuloUno, $nameArticuloDos, $categoria, $grupo, $familia)
    {
        $this->nameClienteUno = $nameClienteUno;
        $this->nameClienteDos = $nameClienteDos;
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
        $venta = PROC_SALES::join('CAT_CUSTOMERS', 'PROC_SALES.sales_customer', '=', 'CAT_CUSTOMERS.customers_key')
            ->join('CAT_COMPANIES', 'PROC_SALES.sales_company', '=', 'CAT_COMPANIES.companies_key')
            ->join('PROC_ASSISTANT_UNITS', 'PROC_SALES.sales_movementID', '=', 'PROC_ASSISTANT_UNITS.assistantUnit_movementID')
            ->join('CAT_ARTICLES', 'PROC_ASSISTANT_UNITS.assistantUnit_account', '=', 'CAT_ARTICLES.articles_key')
            ->where('PROC_ASSISTANT_UNITS.assistantUnit_movement', '=', 'Factura')
            ->where('PROC_SALES.sales_movement', '=', 'Factura')
            ->where('PROC_SALES.sales_status', '=', 'FINALIZADO')
            ->where('PROC_ASSISTANT_UNITS.assistantUnit_canceled', '=', '0')
            ->whereSaleNameCustomer($this->nameClienteUno, $this->nameClienteDos)
            ->whereCustomerCategory($this->nameCategoria)
            ->WhereCustomerGroup($this->nameGrupo)
            ->whereSalesMoney($this->nameMoneda)
            ->whereAssistantUnitYear($this->nameEjercicio)
            ->whereAssistantUnitAccount($this->nameArticuloUno, $this->nameArticuloDos)
            ->whereArticleCategory($this->categoria)
            ->whereArticleGroup($this->grupo)
            ->whereArticleFamily($this->familia)
            ->orderBy('PROC_SALES.updated_at', 'ASC')
            ->get();



        return view('exports.reporteVentasAcumuladas', [
            'ventas' => $venta
        ]);
    }

    public function drawings()
    {
        if (session('company')->companies_logo === null || session('company')->companies_logo === '') {
            $logoFile = null;
        } else {
            $logoFile = storage_path('app/empresas/' . session('company')->companies_logo);
        }

        if ($logoFile == null) {
            $logoFile = storage_path('app/empresas/default.png');
        }

        $drawing = new Drawing();
        $drawing->setPath($logoFile);
        $drawing->setWidth(50);
        $drawing->setHeight(50);
        $drawing->setCoordinates('B1');

        return $drawing;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(50);
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
