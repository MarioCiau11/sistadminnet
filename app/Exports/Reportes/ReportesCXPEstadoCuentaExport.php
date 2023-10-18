<?php

namespace App\Exports\Reportes;

use App\Models\modulos\PROC_ACCOUNTS_PAYABLE;
use App\Models\modulos\PROC_ASSISTANT;
use App\Models\modulos\PROC_TREASURY;
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

class ReportesCXPEstadoCuentaExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
{
    public $nameProveedor;
    public $proveedor;
    public $nameCategoria;
    public $nameGrupo;
    public $nameFecha;
    public $nameMoneda;

    public function __construct($nameProveedor, $proveedor, $nameCategoria, $nameGrupo, $nameFecha, $nameMoneda)
    {
        $this->nameProveedor = $nameProveedor;
        $this->proveedor = $proveedor;
        $this->nameCategoria = $nameCategoria;
        $this->nameGrupo = $nameGrupo;
        $this->nameFecha = $nameFecha;
        $this->nameMoneda = $nameMoneda;
    }

    public function view(): View
    {

        $assistant = [];
        $movimientosCxP =  PROC_ASSISTANT::join("CAT_PROVIDERS", "CAT_PROVIDERS.providers_key", "=", "PROC_ASSISTANT.assistant_account")
            ->join("PROC_ACCOUNTS_PAYABLE", "PROC_ACCOUNTS_PAYABLE.accountsPayable_id", "=", "PROC_ASSISTANT.assistant_moduleID")
            ->whereIn("PROC_ACCOUNTS_PAYABLE.accountsPayable_status", ["POR AUTORIZAR", "FINALIZADO"])
            ->where("assistant_canceled", "=", "0")
            ->where("assistant_branch", "=", "CxP")
            ->where('PROC_ASSISTANT.assistant_companieKey', '=', session('company')->companies_key)
            ->whereAssistantAccount($this->nameProveedor)
            ->whereProviderCategory($this->nameCategoria)
            ->whereProviderGroup($this->nameGrupo)
            ->whereAssistantCreated($this->nameFecha)
            ->whereAssistantMoney($this->nameMoneda)
            ->orderBy('PROC_ASSISTANT.updated_at', 'ASC')
            ->get()
            ->toArray();

        foreach ($movimientosCxP as $mov) {
            if ($mov['assistant_movement'] === "Sol. de Cheque/Transferencia") {
                //BUSCAMOS EL Cheque
                $assistant[] = $mov;

                $cheque = PROC_TREASURY::where("treasuries_company", "=", $mov['accountsPayable_company'])->where('treasuries_branchOffice', "=", $mov['accountsPayable_branchOffice'])->where("treasuries_origin", "=", $mov['accountsPayable_movement'])->where("treasuries_originID", "=", $mov['accountsPayable_movementID'])->where("treasuries_originType", "=", "Din")->where("treasuries_status", "=", "FINALIZADO")->get()->toArray();

                if (count($cheque) > 0) {
                    //BUSCAMOS EL AUXILIAR DEL Cheque
                    $assistant2 = PROC_ASSISTANT::join("CAT_PROVIDERS", "CAT_PROVIDERS.providers_key", "=", "PROC_ASSISTANT.assistant_account")
                        ->where("assistant_branch", "=", "CxP")->where("assistant_movement", "=", $cheque[0]["treasuries_movement"])->where("assistant_moduleID", "=", $cheque[0]["treasuries_id"])->where("assistant_canceled", "=", 0)->first();
                    $assistant[] = $assistant2;
                }
            } else if ($mov['accountsPayable_movement'] === $mov['assistant_movement']) {
                $assistant[] = $mov;
            }
        }


        $proveedorAssistant = [];
        if (count($assistant) > 0) {

            foreach ($assistant as $proveedor) {
                if (!array_key_exists($proveedor['providers_name'] . '-' . $proveedor['assistant_money'], $proveedorAssistant)) {
                    $proveedorAssistant[$proveedor['providers_name'] . '-' . $proveedor['assistant_money']] = $proveedor;
                    $proveedorAssistant[$proveedor['providers_name'] . '-' . $proveedor['assistant_money']]['cuentasxp'] = [];
                }


                array_push($proveedorAssistant[$proveedor['providers_name'] . '-' . $proveedor['assistant_money']]['cuentasxp'], $proveedor);
            }
        }

        return view('exports.reporteCXPEstadoCuenta', [
            'proveedoresEstado' => $proveedorAssistant,
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
