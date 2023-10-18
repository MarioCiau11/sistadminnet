<?php

namespace App\Http\Controllers\erpNet\procesos\Reportes;

use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CAT_PROVIDERS;
use App\Models\modulos\PROC_ASSISTANT;
use App\Models\modulos\PROC_TREASURY;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportesTesoreriaDesglosadoAuxiliarController extends Controller
{
    public function AuxiliarDesglosado($cuenta, $moneda, $mov)
    {
        $datosDesglosados = [];

        $auxiliares = $this->obtenerDatosAuxiliaresDesglosados($cuenta, $moneda, $mov, 'Año Móvil');

        if ($auxiliares->isEmpty()) {
            return redirect()->back()->with('error', 'No se encontraron registros');
        }

        foreach ($auxiliares as $auxiliar) {
            // Extracción de datos comunes
            $beneficiario = null;
            $fecha =  Carbon::parse($auxiliar->created_at)->format('d/m/Y');
            $cuenta = $auxiliar->assistant_account;
            $saldoInicio = ($auxiliar->assistant_balanceInitial === null) ? $auxiliar->moneyAccountsBalance_initialBalance : 0;

            $tesoreriaBeneficiario = PROC_TREASURY::find($auxiliar->assistant_moduleID);

            if ($tesoreriaBeneficiario->treasuries_movement == 'Transferencia Electrónica' || $tesoreriaBeneficiario->treasuries_movement == 'Egreso') {
                //Como es Transferencia Electrónica, entonces buscamos su origen, en este caso treasuries_origin
                $solicitudCheque = PROC_TREASURY::WHERE('treasuries_movement', '=', trim($tesoreriaBeneficiario->treasuries_origin))->WHERE('treasuries_movementID', '=', $tesoreriaBeneficiario->treasuries_originID)
                ->WHERE('treasuries_branchOffice', '=', $tesoreriaBeneficiario->treasuries_branchOffice)->first();
                // dd($solicitudCheque);

            }

            if ($tesoreriaBeneficiario->treasuries_beneficiary !== null) {
                if (($tesoreriaBeneficiario->treasuries_movement === "Transferencia Electrónica" && $solicitudCheque->treasuries_originType === 'CxP') || ($tesoreriaBeneficiario->treasuries_movement === "Egreso" && $tesoreriaBeneficiario->treasuries_originType !== 'CxC')) {
                    $beneficiario = CAT_PROVIDERS::find($tesoreriaBeneficiario->treasuries_beneficiary);
                    $beneficiario = $beneficiario->providers_name;
                } else {
                    $beneficiario = CAT_CUSTOMERS::find($tesoreriaBeneficiario->treasuries_beneficiary);
                    $beneficiario = $beneficiario->customers_businessName;
                }
            }

            if (!array_key_exists($fecha, $datosDesglosados)) {
                $datosDesglosados[$fecha] = [];
            }

            if (!array_key_exists($cuenta, $datosDesglosados[$fecha])) {
                $datosDesglosados[$fecha][$cuenta] = [];
            }

            $datosFinales = [
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

        return $datosDesglosados;
    }

    private function obtenerDatosAuxiliaresDesglosados($cuenta, $moneda, $mov, $nameFecha)
    {
        return PROC_ASSISTANT::join('PROC_MONEY_ACCOUNTS_BALANCE', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_moneyAccount', '=', 'PROC_ASSISTANT.assistant_account')->join('CAT_COMPANIES', 'CAT_COMPANIES.companies_key', '=', 'PROC_ASSISTANT.assistant_companieKey')
        ->select('PROC_ASSISTANT.*', 'CAT_COMPANIES.companies_logo', 'PROC_MONEY_ACCOUNTS_BALANCE.moneyAccountsBalance_initialBalance')
        ->whereAssistantAccount($cuenta)
            ->whereAssistantMoney($moneda)
            ->whereCreatedAt($nameFecha)
            ->whereAssistantMovement($mov)
            ->where('PROC_ASSISTANT.assistant_companieKey', '=', session('company')->companies_key)
            ->orderBy('PROC_ASSISTANT.created_at', 'ASC')
            ->get();
    }

}