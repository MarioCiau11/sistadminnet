<?php

namespace App\Http\Controllers\erpNet\prodServ;

use App\Http\Controllers\Controller;
use App\Models\CatalogosSAT\CAT_SAT_CLAVEPRODSERV;
use App\Models\catalogosSAT\CAT_SAT_FRACCION_ARANCELARIA;
use App\Models\catalogosSAT\CAT_SAT_UNIDAD_MEDIDA;
use Illuminate\Http\Request;

class ProdServController extends Controller
{
    public function buscarProdServ(Request $request)
    {
        $prodServBusqueda = $request->prodServ;

        $prodServArray = [];
        $prodServBuscado = CAT_SAT_CLAVEPRODSERV::where('descripcion', 'LIKE', $prodServBusqueda . '%')
            ->orwhere('c_ClaveProdServ', 'LIKE', $prodServBusqueda . '%')->get();
        $prodServ_key_array = $prodServBuscado->toArray();

        foreach ($prodServ_key_array as $value) {
            $prodServArray[] = $value['c_ClaveProdServ'] . '-' . $value['descripcion'];
        }

        return response()->json(['prodServ' => $prodServArray]);
    }


    public function buscarfraccionArancelaria(Request $request)
    {
        $prodServBusqueda = $request->fraccionArancelaria;

        $prodServArray = [];
        $prodServBuscado = CAT_SAT_FRACCION_ARANCELARIA::where('descripcion', 'LIKE', $prodServBusqueda . '%')
            ->orwhere('c_FraccionArancelaria', 'LIKE', $prodServBusqueda . '%')->orwhere('UMT', 'LIKE', $prodServBusqueda . '%')->get();
        $prodServ_key_array = $prodServBuscado->toArray();

        foreach ($prodServ_key_array as $value) {
            $prodServArray[] = $value['c_FraccionArancelaria'] . '-' . $value['descripcion'] . '-' . $value['UMT'];
        }

        return response()->json(['fraccionArancelaria' => $prodServArray]);
    }

    public function buscarunidadAduana(Request $request)
    {
        $prodServBusqueda = $request->unidadAduana;

        $prodServArray = [];
        $prodServBuscado = CAT_SAT_UNIDAD_MEDIDA::where('descripcion', 'LIKE', $prodServBusqueda . '%')
            ->orwhere('c_UnidadMedida', 'LIKE', $prodServBusqueda . '%')->get();
        $prodServ_key_array = $prodServBuscado->toArray();

        foreach ($prodServ_key_array as $value) {
            $prodServArray[] = $value['c_UnidadMedida'] . '-' . $value['descripcion'];
        }

        return response()->json(['unidadAduana' => $prodServArray]);
    }
}
