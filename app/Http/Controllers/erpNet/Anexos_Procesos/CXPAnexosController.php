<?php

namespace App\Http\Controllers\erpNet\Anexos_Procesos;

use App\Http\Controllers\Controller;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\modulos\PROC_ACCOUNTS_PAYABLE;
use App\Models\modulos\PROC_ACCOUNTS_PAYABLE_FILES;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CXPAnexosController extends Controller
{
    public function index($id)
    {
        $anexosFiles = PROC_ACCOUNTS_PAYABLE_FILES::where('accountsPayableFiles_keyAccountPayable', '=', $id)->get();
        $movimiento = PROC_ACCOUNTS_PAYABLE::WHERE('accountsPayable_id', '=', $id)->first();
        return view('page.modulos.Gestion_y_Finanzas.Cuentas _por_Pagar.anexos.index-anexo', compact('id', 'anexosFiles', 'movimiento'));
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

        $rutaDestino = $empresaRuta . '/CxP/' . $año . '/' . $mes . '/' . $id;
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();

        $isExistRuta = Storage::disk('empresas')->exists(str_replace(['//', '///', '////'], '/', $empresaRuta));

        if (!$isExistRuta) {
            Storage::disk('empresas')->makeDirectory(str_replace(['//', '///', '////'], '/', $empresaRuta));
        }

        //Creamos el directorio Modolos/Cuentas_Por_Pagar/{id}
        Storage::disk('empresas')->makeDirectory(str_replace(['//', '///', '////'], '/', $rutaDestino));
        Storage::disk('empresas')->put(str_replace(['//', '///', '////'], '/', $rutaDestino . '/' . $fileName), file_get_contents($file));

        try {
            $anexoCXP = PROC_ACCOUNTS_PAYABLE_FILES::where('accountsPayableFiles_file', '=', $fileName)->where('accountsPayableFiles_keyAccountPayable', '=', $id)->first();

            if ($anexoCXP === null) {
                $anexoCXP = new PROC_ACCOUNTS_PAYABLE_FILES();
            }

            $anexoCXP->accountsPayableFiles_keyAccountPayable = $id;
            $anexoCXP->accountsPayableFiles_path = str_replace(['//', '///', '////'], '/', $rutaDestino . '/' . $fileName);
            $anexoCXP->accountsPayableFiles_file = $fileName;
            $anexoCXP->save();
        } catch (\Throwable $th) {
            return response()->json(['status' => 500, 'mensaje' => 'Error al guardar los archivos']);
        }
        return response()->json(['status' => 200, 'mensaje' => 'Archivos guardados correctamente']);
    }

    public function destroy($id)
    {
        $anexoCXP = PROC_ACCOUNTS_PAYABLE_FILES::where('accountsPayableFiles_id', '=', $id)->first();
        $ruta = $anexoCXP->accountsPayableFiles_path;
        $anexoCXP->delete();
        Storage::disk('empresas')->delete($ruta);
        return redirect()->route('vista.modulo.cuentasPagar.anexos', $anexoCXP->accountsPayableFiles_keyAccountPayable)->with('message', 'Archivo eliminado correctamente')->with('status', 200);
    }
}
