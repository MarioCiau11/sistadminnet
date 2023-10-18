<?php

namespace App\Http\Controllers\erpNet\Anexos_Procesos;

use App\Http\Controllers\Controller;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\modulos\PROC_TREASURY;
use App\Models\modulos\PROC_TREASURY_FILES;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TesoreriaAnexosController extends Controller
{
    public function index($id)
    {
        $anexosFiles = PROC_TREASURY_FILES::WHERE('treasuriesFiles_keyTreasury', '=', $id)->get();
        $movimiento = PROC_TREASURY::WHERE('treasuries_id', '=', $id)->first();

        return view('page.modulos.Gestion_y_Finanzas.Tesoreria.anexos.index-anexo', compact('id', 'anexosFiles', 'movimiento'));
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


        $rutaDestino = $empresaRuta . '/Tesoreria/' . $año . '/' . $mes . '/' . $id;
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();

        $isExistRuta = Storage::disk('empresas')->exists(str_replace(['//', '///', '////'], '/', $empresaRuta));

        if (!$isExistRuta) {
            Storage::disk('empresas')->makeDirectory(str_replace(['//', '///', '////'], '/', $empresaRuta));
        }

        //Crearemos el directorio de Modulos/Tesoreria/{id}
        Storage::disk('empresas')->makeDirectory(str_replace(['//', '///', '////'], '/', $empresaRuta));
        Storage::disk('empresas')->put(str_replace(['//', '///', '////'], '/', $rutaDestino . '/' . $fileName), file_get_contents($file));

        try {
            //Comprobamos si el nombre existe en la db
            $anexoTesoreria = PROC_TREASURY_FILES::WHERE('treasuriesFiles_file', '=', $fileName)->WHERE('treasuriesFiles_keyTreasury', '=', $id)->first();

            if ($anexoTesoreria === null) {
                $anexosTesoreria = new PROC_TREASURY_FILES();
            }
            //Insertamos los datos del file en la db
            $anexosTesoreria->treasuriesFiles_keyTreasury = $id; //Id del gasto
            $anexosTesoreria->treasuriesFiles_path = str_replace(['//', '///', '////'], '/', $rutaDestino . '/' . $fileName);
            $anexosTesoreria->treasuriesFiles_file = $fileName;
            $anexosTesoreria->save();
        } catch (\Throwable $th) {
            return response()->json(['status' => 500, 'mensaje' => 'Error al guardar los archivos']);
        }
        return response()->json(['status' => 200, 'mensaje' => 'Archivos guardados correctamente']);
    }
    public function destroy($id)
    {
        $anexoTesoreria = PROC_TREASURY_FILES::WHERE('treasuriesFiles_id', '=', $id)->first();
        $ruta = $anexoTesoreria->treasuriesFiles_path;
        $anexoTesoreria->delete();
        Storage::disk('empresas')->delete($ruta);
        return redirect()->route('vista.modulo.tesoreria.anexos', $anexoTesoreria->treasuriesFiles_keyTreasury)->with('message', 'Archivo eliminado correctamente')->with('status', 200);
    }
}
