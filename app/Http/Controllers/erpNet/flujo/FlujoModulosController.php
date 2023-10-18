<?php

namespace App\Http\Controllers\erpNet\flujo;

use App\Http\Controllers\Controller;
use App\Models\modulos\PROC_ACCOUNTS_PAYABLE;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE;
use App\Models\modulos\PROC_EXPENSES;
use App\Models\modulos\PROC_MOVEMENT_FLOW;
use App\Models\modulos\PROC_PURCHASE;
use App\Models\modulos\PROC_SALES;
use App\Models\modulos\PROC_TREASURY;
use Illuminate\Http\Request;

class FlujoModulosController extends Controller
{
    public function siguienteFlujo(Request $request)
    {
        $dataFlujo = json_decode($request->dataFlujo);

        $nuevoFlujo = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
            ->WHERE('movementFlow_originID', '=', $dataFlujo->movementFlow_destinityID)
            ->WHERE('movementFlow_moduleOrigin', '=', $dataFlujo->movementFlow_moduleDestiny)
            ->get();


        return response()->json(['status' => 200, 'data' => $nuevoFlujo]);
    }

    public function anteriorFlujo(Request $request)
    {
        $dataFlujo = json_decode($request->dataFlujo);

        $anteriorFlujo = PROC_MOVEMENT_FLOW::WHERE('movementFlow_company', '=', session('company')->companies_key)
            ->WHERE('movementFlow_destinityID', '=', $dataFlujo->movementFlow_originID)
            ->WHERE('movementFlow_moduleDestiny', '=', $dataFlujo->movementFlow_moduleOrigin)
            ->get();

        return response()->json(['status' => 200, 'data' => $anteriorFlujo]);
    }

    public function statusMovimiento(Request $request)
    {
        $respustaStatus = [];
        $sucursal = $request->sucursal;
        $empresa = $request->empresa;

        $folioOrigen = $request->folioO;
        $TABLAMOVIMIENTO_ORIGEN = $request->dbO;
        $movimientoOrigen = $request->movimientoO;

        $folioD = $request->folioD;
        $TABLAMOVIMIENTO_D = $request->dbD;
        $movimientoD = $request->movimientoD;


        switch ($TABLAMOVIMIENTO_ORIGEN) {
            case 'PROC_ACCOUNTS_PAYABLE':
                $statusOrigen = PROC_ACCOUNTS_PAYABLE::WHERE('accountsPayable_movement', '=', $movimientoOrigen)->where("accountsPayable_company", '=', $empresa)->where("accountsPayable_branchOffice", '=', $sucursal)->where("accountsPayable_movementID", '=', $folioOrigen)->select('accountsPayable_status')->first();
                $respustaStatus['statusOrigen'] = $statusOrigen->accountsPayable_status;
                break;

            case 'PROC_PURCHASE':
                $statusOrigen = PROC_PURCHASE::WHERE('purchase_movement', '=', $movimientoOrigen)->where("purchase_company", '=', $empresa)->where("purchase_branchOffice", '=', $sucursal)->where("purchase_movementID", '=', $folioOrigen)->select('purchase_status')->first();
                $respustaStatus['statusOrigen'] = $statusOrigen->purchase_status;
                break;

            case 'PROC_TREASURY':
                $statusOrigen = PROC_TREASURY::WHERE('treasuries_movement', '=', $movimientoOrigen)->where("treasuries_company", '=', $empresa)->where("treasuries_branchOffice", '=', $sucursal)->where("treasuries_movementID", '=', $folioOrigen)->select('treasuries_status')->first();
                $respustaStatus['statusOrigen'] = $statusOrigen->treasuries_status;
                break;

            case "PROC_ACCOUNTS_RECEIVABLE":
                $statusOrigen = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', $movimientoOrigen)->where("accountsReceivable_company", '=', $empresa)->where("accountsReceivable_branchOffice", '=', $sucursal)->where("accountsReceivable_movementID", '=', $folioOrigen)->select('accountsReceivable_status')->first();
                $respustaStatus['statusOrigen'] = $statusOrigen->accountsReceivable_status;
                break;

            case "PROC_SALES":
                $statusOrigen = PROC_SALES::WHERE('sales_movement', '=', $movimientoOrigen)->where("sales_company", '=', $empresa)->where("sales_branchOffice", '=', $sucursal)->where("sales_movementID", '=', $folioOrigen)->select('sales_status')->first();
                $respustaStatus['statusOrigen'] = $statusOrigen->sales_status;
                break;

            case "PROC_EXPENSES":
                $statusOrigen = PROC_EXPENSES::WHERE('expenses_movement', '=', $movimientoOrigen)->where("expenses_company", '=', $empresa)->where("expenses_branchOffice", '=', $sucursal)->where("expenses_movementID", '=', $folioOrigen)->select('expenses_status')->first();
                $respustaStatus['statusOrigen'] = $statusOrigen->expenses_status;
                break;
            default:
                # code...
                break;
        }

        switch ($TABLAMOVIMIENTO_D) {
            case 'PROC_ACCOUNTS_PAYABLE':
                $statusDestino = PROC_ACCOUNTS_PAYABLE::WHERE('accountsPayable_movement', '=', $movimientoD)->where("accountsPayable_company", '=', $empresa)->where("accountsPayable_branchOffice", '=', $sucursal)->where("accountsPayable_movementID", '=', $folioD)->select('accountsPayable_status')->first();
                $respustaStatus['statusDestino'] = $statusDestino->accountsPayable_status;
                break;

            case 'PROC_PURCHASE':
                $statusDestino = PROC_PURCHASE::WHERE('purchase_movement', '=', $movimientoD)->where("purchase_company", '=', $empresa)->where("purchase_branchOffice", '=', $sucursal)->where("purchase_movementID", '=', $folioD)->select('purchase_status')->first();
                $respustaStatus['statusDestino'] = $statusDestino->purchase_status;
                break;

            case 'PROC_TREASURY':
                $statusDestino = PROC_TREASURY::WHERE('treasuries_movement', '=', $movimientoD)->where("treasuries_company", '=', $empresa)->where("treasuries_branchOffice", '=', $sucursal)->where("treasuries_movementID", '=', $folioD)->select('treasuries_status')->first();
                $respustaStatus['statusDestino'] = $statusDestino->treasuries_status;
                break;

            case "PROC_ACCOUNTS_RECEIVABLE":
                $statusDestino = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_movement', '=', $movimientoD)->where("accountsReceivable_company", '=', $empresa)->where("accountsReceivable_branchOffice", '=', $sucursal)->where("accountsReceivable_movementID", '=', $folioD)->select('accountsReceivable_status')->first();
                $respustaStatus['statusDestino'] = $statusDestino->accountsReceivable_status;
                break;

            case "PROC_SALES":
                $statusDestino = PROC_SALES::WHERE('sales_movement', '=', $movimientoD)->where("sales_company", '=', $empresa)->where("sales_branchOffice", '=', $sucursal)->where("sales_movementID", '=', $folioD)->select('sales_status')->first();
                $respustaStatus['statusDestino'] = $statusDestino->sales_status;
                break;

            case "PROC_EXPENSES":
                $statusOrigen = PROC_EXPENSES::WHERE('expenses_movement', '=', $movimientoD)->where("expenses_company", '=', $empresa)->where("expenses_branchOffice", '=', $sucursal)->where("expenses_movementID", '=', $folioD)->select('expenses_status')->first();
                $respustaStatus['statusOrigen'] = $statusOrigen->expenses_status;
            default:
                # code...
                break;
        }




        if ($statusOrigen) {
            return response()->json(['status' => true, 'data' => $respustaStatus]);
        } else {
            return response()->json(['status' => false, 'data' => $respustaStatus]);
        }
    }
}
