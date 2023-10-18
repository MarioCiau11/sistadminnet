<?php

namespace App\Http\Controllers\erpNet\herramienta;

use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_ARTICLES;
use Illuminate\Http\Request;

class CambioPreciosVentaController extends Controller
{
    public function index()
    {

        
        return view('page.herramienta.cambioPreciosVenta');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $articulos = $request['inputDataArticles'];
        $articulos = json_decode($articulos, true);
        $claveArt = array_keys($articulos);


        foreach ($claveArt as $clave) {
            if ($articulos[$clave]['costo'] != '$0.00') {
                // $lista = CAT_ARTICLES_LIST::where('articlesList_article', '=', $clave)->where('articlesList_listID', '=', $request->listaProveedor)->first();
                $articulo = CAT_ARTICLES::where('articles_key', '=', $clave)->first();
                // $lista->articlesList_lastCost = str_replace(['$', ','], '', $articulos[$clave]['costo']);
                if($articulos[$clave]['precioLista'] == 'articles_listPrice1'){

                    $articulo->articles_listPrice1 = str_replace(['$', ','], '', $articulos[$clave]['costo']);
                } elseif ($articulos[$clave]['precioLista'] == 'articles_listPrice2') {
                    $articulo->articles_listPrice2 = str_replace(['$', ','], '', $articulos[$clave]['costo']);
                    //probamos si el precio 2 es mayor al precio 1
                } elseif ($articulos[$clave]['precioLista'] == 'articles_listPrice3') {
                    $articulo->articles_listPrice3 = str_replace(['$', ','], '', $articulos[$clave]['costo']);
                } elseif ($articulos[$clave]['precioLista'] == 'articles_listPrice4') {
                    $articulo->articles_listPrice4 = str_replace(['$', ','], '', $articulos[$clave]['costo']);
                } elseif ($articulos[$clave]['precioLista'] == 'articles_listPrice5') {
                    $articulo->articles_listPrice5 = str_replace(['$', ','], '', $articulos[$clave]['costo']);
                }
                $actualizado = $articulo->save();
            }
        }

        if ($actualizado) {
            $message = 'Se actualizó correctamente';
            $estatus = true;
        } else {
            $message = 'No se pudo actualizar';
            $estatus = false;
        }

        // dd($lista);
        return response()->json(['mensaje' => $message, 'estatus' => $estatus]);
    }

    public function listaPrecios(Request $request)
    {
        $listasArticulos = CAT_ARTICLES::where('articles_status', '=', 'Alta')
        ->select('articles_key', 'articles_descript', $request->id)
            ->orderBy('articles_id' , 'asc')->get();

        return response()->json($listasArticulos);
    }
}
