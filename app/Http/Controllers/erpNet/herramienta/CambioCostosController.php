<?php

namespace App\Http\Controllers\erpNet\herramienta;

use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_ARTICLES_LIST;
use App\Models\agrupadores\CAT_PROVIDER_LIST;
use Illuminate\Http\Request;

class CambioCostosController extends Controller
{
    public function index()
    {

        $listaProveedor = $this->selectListaProveedor();
        return view('page.herramienta.cambioCostos', compact('listaProveedor'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $articulos = $request['inputDataArticles'];
        $articulos = json_decode($articulos, true);
        $claveArt = array_keys($articulos);


        foreach ($claveArt as $clave) {
            if ($articulos[$clave]['costo'] != '$0.00') {
                $lista = CAT_ARTICLES_LIST::where('articlesList_article', '=', $clave)->where('articlesList_listID', '=', $request->listaProveedor)->first();
                $lista->articlesList_penultimateCost = $lista->articlesList_lastCost;
                $lista->articlesList_lastCost = str_replace(['$', ','], '', $articulos[$clave]['costo']);
                $actualizado = $lista->save();
            }
        }

        if ($actualizado) {
            $message = 'Se actualizó correctamente';
            $estatus = true;
        } else {
            $message = 'No se actualizo';
            $estatus = false;
        }

        // dd($lista);
        return response()->json(['mensaje' => $message, 'estatus' => $estatus]);
    }

    public function listas(Request $request)
    {
        $listas = CAT_ARTICLES_LIST::where('articlesList_listID', '=', $request->id)->get();

        return response()->json($listas);
    }

    public function selectListaProveedor()
    {
        $condiciones_array = [];
        $condiciones_key_sat_collection = CAT_PROVIDER_LIST::where('listProvider_status', '=', 'Alta')->get();
        $condiciones_key_sat_array = $condiciones_key_sat_collection->toArray();

        foreach ($condiciones_key_sat_array as $key => $value) {
            $condiciones_array[$value['listProvider_id']] = $value['listProvider_name'];
        }
        return $condiciones_array;
    }
}
