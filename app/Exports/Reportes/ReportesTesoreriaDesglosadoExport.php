<?php

namespace App\Exports\Reportes;

use App\Http\Controllers\erpNet\procesos\Reportes\ReportesTesoreriaDesglosadoAuxiliarController;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CAT_PROVIDERS;
use App\Models\modulos\PROC_ASSISTANT;
use App\Models\modulos\PROC_TREASURY;
use Carbon\Carbon;
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

class ReportesTesoreriaDesglosadoExport implements FromView, ShouldAutoSize, WithStyles, WithDrawings, WithColumnWidths, WithEvents
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
      $datosDesglosados = [];

        //Obtenemos los datos de la tabla auxiliar
    $auxiliares = PROC_ASSISTANT::join('PROC_MONEY_ACCOUNTS_BALANCE', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_moneyAccount', '=', 'PROC_ASSISTANT.assistant_account')->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_ASSISTANT.assistant_companieKey')
    ->select('PROC_ASSISTANT.*', 'CAT_COMPANIES.companies_logo', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_initialBalance')
    ->whereAssistantAccount($this->nameCuenta)
    ->whereAssistantMoney($this->nameMoneda)
    ->whereCreatedAt($this->nameFecha)
    ->whereAssistantMovement($this->nameMov)
    ->where('PROC_ASSISTANT.assistant_companieKey', '=', session('company')->companies_key)
    ->orderBy('PROC_ASSISTANT.created_at', 'ASC')->get();

    
          //Obtenemos el beneficiario de acuerdo a los datos de la tabla auxiliar
        foreach ($auxiliares as $key => $auxiliar) {
            $beneficiario = null;
            $fecha =  Carbon::parse($auxiliar->created_at)->format('d/m/Y');
            $cuenta = $auxiliar->assistant_account;
            $saldoInicio = 0;
            $tesoreriaBeneficiario = PROC_TREASURY::find($auxiliar->assistant_moduleID);
            // dd($tesoreriaBeneficiario);

            if ($tesoreriaBeneficiario->treasuries_beneficiary !== null) {
                if ($tesoreriaBeneficiario->treasuries_movement === "Transferencia Electrónica" || $tesoreriaBeneficiario->treasuries_movement === "Sol. de Cheque/Transferencia" || $tesoreriaBeneficiario->treasuries_movement === "Egreso") {
                    $beneficiario = CAT_PROVIDERS::find($tesoreriaBeneficiario->treasuries_beneficiary);
                    $beneficiario = $beneficiario->providers_name;
                } else {
                    $beneficiario = CAT_CUSTOMERS::find($tesoreriaBeneficiario->treasuries_beneficiary);
                    $beneficiario = $beneficiario->customers_businessName;
                }
            }


            if (array_key_exists($fecha, $datosDesglosados) && array_key_exists($cuenta, $datosDesglosados[$fecha])) {


                if ($auxiliar->assistant_balanceInitial === null) {
                    $saldoInicio = $auxiliar->moneyAccountsBalance_initialBalance;
                }

                $datosFinales  = [
                    "cuenta" => $auxiliar->assistant_account,
                    "movimiento" => $auxiliar->assistant_movement . " " . $auxiliar->assistant_movementID,
                    "beneficiario" => $beneficiario,
                    "referencia" => $auxiliar->assistant_reference,
                    "inicio" => $saldoInicio,
                    "cargos" => $auxiliar->assistant_charge,
                    "abonos" => $auxiliar->assistant_payment,
                    "saldos" => 0,
                    "fecha" => $fecha,
                    "saldoIngreso" => $auxiliar->moneyAccountsBalance_initialBalance,
                    'inicioSaldoBit' =>  $auxiliar->assistant_balanceInitial
                ];


                array_push($datosDesglosados[$fecha][$cuenta], $datosFinales);
            } else {
                $saldoInicio = 0;
                if ($auxiliar->assistant_balanceInitial === null) {
                    $saldoInicio = $auxiliar->moneyAccountsBalance_initialBalance;
                }


                $datosDesglosados[$fecha][$cuenta] = [];
                $datosFinales  = [
                    "cuenta" => $auxiliar->assistant_account,
                    "movimiento" => $auxiliar->assistant_movement . " " . $auxiliar->assistant_movementID,
                    "beneficiario" => $beneficiario,
                    "referencia" => $auxiliar->assistant_reference,
                    "inicio" => $saldoInicio,
                    "cargos" => $auxiliar->assistant_charge,
                    "abonos" => $auxiliar->assistant_payment,
                    "saldos" => 0,
                    "fecha" => $fecha,
                    "saldoIngreso" => $auxiliar->moneyAccountsBalance_initialBalance,
                    'inicioSaldoBit' =>  $auxiliar->assistant_balanceInitial
                ];

                array_push($datosDesglosados[$fecha][$cuenta], $datosFinales);
            }
        }

        $reportesTesoreriaDesglosadoAuxiliar = new ReportesTesoreriaDesglosadoAuxiliarController();
        $datosAuxiliares = $reportesTesoreriaDesglosadoAuxiliar->AuxiliarDesglosado($this->nameCuenta, $this->nameMoneda, $this->nameMov);

        // Obtener las keys de los datos desglosados
        $keys = array_keys($datosDesglosados);

        // Obtener el primer dato del mes y sus datos auxiliares
        $primerDatoDelMes = $keys[0];
        $datosAuxiliaresDelMes = $datosAuxiliares[$primerDatoDelMes];

        // Buscar la posición del primer dato del mes en los datos auxiliares
        $posicion = array_key_exists($primerDatoDelMes, $datosAuxiliares) ? array_search($primerDatoDelMes, array_keys($datosAuxiliares)) : 0;

        // Cortar los datos auxiliares a partir de la posición del primer dato del mes
        $datosAuxiliaresFinales = array_slice($datosAuxiliares, $posicion, count($datosAuxiliares), true);

        $fin = array_diff_key($datosAuxiliares, $datosAuxiliaresFinales);

        $historialDesglose = [];
        foreach ($fin as $fecha => $fechasTesoreria)
        // dd($fecha);
        {
            foreach ($fechasTesoreria as $auxiliar) {
                $totalCargos = 0;
                $totalAbonos = 0;
                $saldosFinales = 0;
                $isPrimerIngreso = false;
                $montoInicial = 0;

                foreach ($auxiliar as $key => $bancosAuxiliar) {
                    if ($bancosAuxiliar['cargos'] != null) {
                        $totalCargos += (float) $bancosAuxiliar['cargos'];
                        // dd($totalCargos);
                    }
                    if ($bancosAuxiliar['abonos'] != null) {
                        $totalAbonos += (float) $bancosAuxiliar['abonos'];
                        // dd($totalAbonos);
                    }

                    if ($key == 0) {
                        $montoInicial = array_key_exists($bancosAuxiliar['cuenta'], $historialDesglose) ? $historialDesglose[$bancosAuxiliar['cuenta']] : $montoInicial;
                        $saldosFinales = $montoInicial + $totalCargos - $totalAbonos;
                    } else {
                        $saldosFinales = $montoInicial + $totalCargos - $totalAbonos;
                    }
                    // dd($saldosFinales, $montoInicial);

                    if (!array_key_exists($bancosAuxiliar['cuenta'], $historialDesglose)) {
                        //Creamos la key en el arreglo q contendra los saldos finales
                        $historialDesglose[$bancosAuxiliar['cuenta']] = $saldosFinales;
                    } else {
                        //Incrementamos el saldo final de la misma key
                        $historialDesglose[$bancosAuxiliar['cuenta']] = $saldosFinales;
                    }
                }
            }
            //  dd($historialDesglose);
        }
        //  dd($historialDesglose);

        return view('exports.reporteTesoreriaDesglosado', [
            'tesorerias' => $datosDesglosados, 'datosAuxiliares' => $datosAuxiliares, 'saldoFinal' => $historialDesglose
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
