<?php

namespace App\Exports;

use App\Models\catalogos\CONF_REASON_CANCELLATIONS;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class ConfMotivosCancelacionExport implements FromView, ShouldAutoSize, WithStyles
{
   public $nameMotivo;
    public $modulo;
    public $status;

    public function __construct($nameMotivo, $modulo, $status)
    {
        $this->nameMotivo = $nameMotivo;
        $this->modulo = $modulo;
        $this->status = $status;
    }

    public function view(): View
    {
        if(!$this->nameMotivo) {
            $motivo_collection_filtro = CONF_REASON_CANCELLATIONS::whereReasonCancellationsName($this->nameMotivo)
                ->whereStatus($this->status)
                ->get();
        } else {
            $motivo_collection_filtro = CONF_REASON_CANCELLATIONS::whereReasonCancellationsName($this->nameMotivo)
                ->get();
        }
        return view('exports.motivosCancelacion', [
            'motivo' => $motivo_collection_filtro
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
