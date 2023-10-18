<?php

namespace App\Http\Controllers\regimenVScfdi;

use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogosSAT\CAT_SAT_REGIMEN_AND_CFDI;
use Illuminate\Http\Request;

class REGIMEN_CFDI_CONTROLLER extends Controller
{
    public function regimenCFDI(Request $request)
    {
    if(isset($request->regimen)){
       $regimen = $request->regimen;

       $cfdiRelacionados = CAT_SAT_REGIMEN_AND_CFDI::WHERE("regimenFiscal", $regimen)->get();

       return response()->json(['status' => 200, 'data' => $cfdiRelacionados]);
        }else{
            $cliente = $request->cliente;

            $infoCliente = CAT_CUSTOMERS::WHERE("customers_key", $cliente)->first();

            if($infoCliente->customers_taxRegime === NULL){
                return response()->json(['status' => 404]);
            }else{
                $cfdiRelacionados = CAT_SAT_REGIMEN_AND_CFDI::WHERE("regimenFiscal", $infoCliente->customers_taxRegime)->get();
                return response()->json(['status' => 200, 'data' => $cfdiRelacionados, 'cfdi' => $infoCliente->customers_identificationCFDI]);
            }
        }
    }
}
