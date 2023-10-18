<?php

namespace App\Exports\Reportes;

use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE_P;
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

class ReportesCXCEstadoCuentaExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
{
    public $nameCliente;
    public $cliente;
    public $nameCategoria;
    public $nameGrupo;
    public $namePlazo;
    public $nameMoneda;

    public function __construct($nameCliente, $cliente, $nameCategoria, $nameGrupo, $namePlazo, $nameMoneda)
    {
        $this->nameCliente = $nameCliente;
        $this->cliente = $cliente;
        $this->nameCategoria = $nameCategoria;
        $this->nameGrupo = $nameGrupo;
        $this->namePlazo = $namePlazo;
        $this->nameMoneda = $nameMoneda;
    }


    public function view(): View
    {
        $assistant = [];
        $movimientosCxC = PROC_ASSISTANT::join("CAT_CUSTOMERS", "CAT_CUSTOMERS.customers_key", "=", "PROC_ASSISTANT.assistant_account")
            ->join("PROC_ACCOUNTS_RECEIVABLE", "PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_id", "=", "PROC_ASSISTANT.assistant_moduleID")
            ->whereIn("PROC_ACCOUNTS_RECEIVABLE.accountsReceivable_status", ["POR AUTORIZAR", "FINALIZADO"])
            ->where("assistant_canceled", "=", 0)
            ->where("assistant_branch", "=", "CxC")
            ->where('PROC_ASSISTANT.assistant_companieKey', '=', session('company')->companies_key)
            ->whereAssistantAccount($this->nameCliente)
            ->whereCustomerCategory($this->nameCategoria)
            ->whereCustomerGroup($this->nameGrupo)
            ->whereAssistantCreated($this->namePlazo)
            ->whereAssistantMoney($this->nameMoneda)
            ->orderBy('PROC_ASSISTANT.updated_at', 'ASC')
            ->get()
            ->toArray();

        foreach ($movimientosCxC as $mov) {
            if ($mov['assistant_movement'] === "Solicitud Depósito") {
                //BUSCAMOS EL DEPOSITO
                $assistant[] = $mov;

                //Busacamos su origen 
                $cxc = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', 'Cobro de Facturas')->WHERE('accountsReceivable_movementID', '=', $mov['assistant_movementID'])->WHERE('accountsReceivable_company', '=', $mov['assistant_companieKey'])->WHERE('accountsReceivable_branchOffice', '=', $mov['assistant_branchKey'])->first();

                if($cxc !== null) {
                    $solicitudDeposito = PROC_TREASURY::WHERE('treasuries_origin', '=', $cxc->accountsReceivable_movement)->WHERE('treasuries_originID', '=', $cxc->accountsReceivable_movementID)->WHERE('treasuries_company', '=', $mov['assistant_companieKey'])->WHERE('treasuries_branchOffice', '=', $mov['assistant_branchKey'])->first();


                    $deposito = PROC_TREASURY::where("treasuries_company", "=", $mov['accountsReceivable_company'])->where('treasuries_branchOffice', "=", $mov['accountsReceivable_branchOffice'])->where("treasuries_origin", "=",  $solicitudDeposito->treasuries_movement)->where("treasuries_originID", "=", $solicitudDeposito->treasuries_movementID)->where("treasuries_originType", "=", "Din")->where("treasuries_status", "=", "FINALIZADO")->get()->toArray();

                    // $deposito = PROC_TREASURY::where("treasuries_company", "=", $mov['accountsReceivable_company'])->where('treasuries_branchOffice', "=", $mov['accountsReceivable_branchOffice'])->where("treasuries_origin", "=", $mov['accountsReceivable_movement'])->where("treasuries_originID", "=", $mov['accountsReceivable_movementID'])->where("treasuries_originType", "=", "Din")->where("treasuries_status", "=", "FINALIZADO")->get()->toArray();

                    if (count($deposito) > 0) {
                        //BUSCAMOS EL AUXILIAR DEL DEPOSITO
                        $assistant2 = PROC_ASSISTANT::join("CAT_CUSTOMERS", "CAT_CUSTOMERS.customers_key", "=", "PROC_ASSISTANT.assistant_account")
                            ->where("assistant_branch", "=", "CxC")->where("assistant_movement", "=", $deposito[0]["treasuries_movement"])->where("assistant_moduleID", "=", $deposito[0]["treasuries_id"])->where("assistant_canceled", "=", 0)->first();
                        $assistant[] = $assistant2;
                    }
                }
                //BUSCAMOS EL DEPOSITO

            
            } else if ($mov['accountsReceivable_movement'] === $mov['assistant_movement']) {
                $assistant[] = $mov;
            }
        }

        $proveedorAssistant = [];
        if (count($assistant) > 0) {

            foreach ($assistant as $proveedor) {
                if (!array_key_exists($proveedor['customers_businessName'] . '-' . $proveedor['assistant_money'], $proveedorAssistant)) {
                    $proveedorAssistant[$proveedor['customers_businessName'] . '-' . $proveedor['assistant_money']] = $proveedor;
                    $proveedorAssistant[$proveedor['customers_businessName'] . '-' . $proveedor['assistant_money']]['cuentasxp'] = [];
                }


                array_push($proveedorAssistant[$proveedor['customers_businessName'] . '-' . $proveedor['assistant_money']]['cuentasxp'], $proveedor);
            }
        }

        return view('exports.reporteCXCEstadoCuenta', [
            'cuentasxcobrar' =>  $proveedorAssistant,
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
