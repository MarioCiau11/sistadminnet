<?php

namespace App\Http\Controllers\erpNet\Anexos_Procesos;

use App\Http\Controllers\Controller;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\modulos\PROC_PURCHASE;
use App\Models\modulos\PROC_PURCHASE_FILES;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompraAnexosController extends Controller
{
    public function index($id)
    {
        $anexosFiles = PROC_PURCHASE_FILES::WHERE('purchaseFiles_keyPurchase', '=', $id)->get();
        $movimiento = PROC_PURCHASE::WHERE('purchase_id', '=', $id)->first();

        return view('page.modulos.logistica.compras.anexos.index-anexo', compact('id', 'anexosFiles', 'movimiento'));
    }
    public function store(Request $request, $id)
    {
        $año = Carbon::now()->year;
        $mes = Carbon::now()->month;
        //Obtenemos los parametros generales de la empresa
        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();

        if ($parametro === null || $parametro->generalParameters_filesMovements === Null || $parametro->generalParameters_filesMovements === '') {
            $empresaRuta = session('company')->companies_routeFiles . 'Modulos';
        } else {
            $empresaRuta = session('company')->companies_routeFiles . $parametro->generalParameters_filesMovements;
        }


        $rutaDestino = $empresaRuta . '/Compras/' . $año . '/' . $mes . '/' . $id;
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();

        $isExistRuta = Storage::disk('empresas')->exists(str_replace(['//', '///', '////'], '/', $empresaRuta));

        if (!$isExistRuta) {
            Storage::disk('empresas')->makeDirectory(str_replace(['//', '///', '////'], '/', $empresaRuta));
        }

        //Creamos el directorio Modulos/Compras/{id}
        Storage::disk('empresas')->makeDirectory(str_replace(['//', '///', '////'], '/', $rutaDestino));
        Storage::disk('empresas')->put(str_replace(['//', '///', '////'], '/', $rutaDestino . '/' . $fileName), file_get_contents($file));

        try {
            //Comprobamos si el nombre existe en la db
            $anexoCompra = PROC_PURCHASE_FILES::WHERE('purchaseFiles_file', '=', $fileName)->WHERE('purchaseFiles_keyPurchase', '=', $id)->first();

            if ($anexoCompra === null) {
                $anexosCompra = new PROC_PURCHASE_FILES();
            }
            //Insertamos los datos del file en la db
            $anexosCompra->purchaseFiles_keyPurchase = $id; //Id de la compra
            $anexosCompra->purchaseFiles_path = str_replace(['//', '///', '////'], '/', $rutaDestino . '/' . $fileName);
            $anexosCompra->purchaseFiles_file = $fileName;
            $anexosCompra->save();
        } catch (\Throwable $th) {
            return response()->json(['status' => 500, 'mensaje' => 'Error al guardar los archivos']);
        }



        return response()->json(['status' => 200, 'mensaje' => 'Archivos subidos correctamente']);
    }

    public function destroy($id)
    {
        $anexoCompra = PROC_PURCHASE_FILES::WHERE('purchaseFiles_id', '=', $id)->first();
        $ruta = $anexoCompra->purchaseFiles_path;
        $anexoCompra->delete();
        Storage::disk('empresas')->delete($ruta);
        return redirect()->route('vista.modulo.compras.anexos', $anexoCompra->purchaseFiles_keyPurchase)->with('message', 'Archivo eliminado correctamente')->with('status', 200);
    }
}
