<?php

namespace App\Http\Controllers\erpNet\Anexos_Procesos;

use App\Http\Controllers\Controller;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\modulos\PROC_SALES;
use App\Models\modulos\PROC_SALES_FILES;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class VentasAnexosController extends Controller
{
    public function index($id)
    {
        $anexosFiles = PROC_SALES_FILES::WHERE('salesFiles_keySale', '=', $id)->get();
        $movimiento = PROC_SALES::WHERE('sales_id', '=', $id)->first();

        return view('page.modulos.Comercial.Ventas.anexos.index-anexo', compact('id', 'anexosFiles', 'movimiento'));
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



        $rutaDestino = $empresaRuta . '/Ventas/' . $año . '/' . $mes . '/' . $id;
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();

        $isExistRuta = Storage::disk('empresas')->exists(str_replace(['//', '///', '////'], '/', $empresaRuta));

        if (!$isExistRuta) {
            Storage::disk('empresas')->makeDirectory(str_replace(['//', '///', '////'], '/', $empresaRuta));
        }

        //Creamos el directorio Modulos/Compras/{id}
        Storage::disk('empresas')->makeDirectory(str_replace(['//', '///', '////'], '/', $empresaRuta));
        Storage::disk('empresas')->put(str_replace(['//', '///', '////'], '/', $rutaDestino . '/' . $fileName), file_get_contents($file));

        try {
            //Comprobamos si el nombre existe en la db
            $anexoVenta = PROC_SALES_FILES::WHERE('salesFiles_file', '=', $fileName)->WHERE('salesFiles_keySale', '=', $id)->first();

            if ($anexoVenta === null) {
                $anexosVenta = new PROC_SALES_FILES();
            }
            //Insertamos los datos del file en la db
            $anexosVenta->salesFiles_keySale = $id; //Id de la compra
            $anexosVenta->salesFiles_path = str_replace(['//', '///', '////'], '/', $rutaDestino . '/' . $fileName);
            $anexosVenta->salesFiles_file = $fileName;
            $anexosVenta->save();
        } catch (\Throwable $th) {
            return response()->json(['status' => 500, 'mensaje' => 'Error al guardar los archivos']);
        }



        return response()->json(['status' => 200, 'mensaje' => 'Archivos subidos correctamente']);
    }

    public function destroy($id)
    {
        $anexoVenta = PROC_SALES_FILES::WHERE('salesFiles_id', '=', $id)->first();
        $ruta = $anexoVenta->salesFiles_path;
        $anexoVenta->delete();
        Storage::disk('empresas')->delete($ruta);
        return redirect()->route('vista.modulo.ventas.anexos', $anexoVenta->salesFiles_keySale)->with('message', 'Archivo eliminado correctamente')->with('status', 200);
    }
}
