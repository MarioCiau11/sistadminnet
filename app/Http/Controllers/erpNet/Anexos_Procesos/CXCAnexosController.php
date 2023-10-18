<?php

namespace App\Http\Controllers\erpNet\Anexos_Procesos;

use App\Http\Controllers\Controller;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE_FILES;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CXCAnexosController extends Controller
{
    public function index($id)
    {
        $anexosFiles = PROC_ACCOUNTS_RECEIVABLE_FILES::WHERE('accountsReceivableFiles_keyaccountsReceivable', '=', $id)->get();
        $movimiento = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_id', '=', $id)->first();

        return view('page.modulos.Gestion_y_Finanzas.Cuentas_por_Cobrar.anexos.index-anexo', compact('id', 'anexosFiles', 'movimiento'));
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

        $rutaDestino = $empresaRuta . '/CxC/' . $año . '/' . $mes . '/' . $id;
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
            $anexoCXC = PROC_ACCOUNTS_RECEIVABLE_FILES::WHERE('accountsReceivableFiles_file', '=', $fileName)->WHERE('accountsReceivableFiles_keyaccountsReceivable', '=', $id)->first();

            if ($anexoCXC === null) {
                $anexosCXC = new PROC_ACCOUNTS_RECEIVABLE_FILES();
            }
            //Insertamos los datos del file en la db
            $anexosCXC->accountsReceivableFiles_keyaccountsReceivable = $id; //Id de la compra
            $anexosCXC->accountsReceivableFiles_path = str_replace(['//', '///', '////'], '/', $rutaDestino . '/' . $fileName);
            $anexosCXC->accountsReceivableFiles_file = $fileName;
            $anexosCXC->save();
        } catch (\Throwable $th) {
            return response()->json(['status' => 500, 'mensaje' => 'Error al guardar los archivos']);
        }



        return response()->json(['status' => 200, 'mensaje' => 'Archivos subidos correctamente']);
    }

    public function destroy($id)
    {
        $anexoCXC = PROC_ACCOUNTS_RECEIVABLE_FILES::WHERE('accountsReceivableFiles_id', '=', $id)->first();
        $ruta = $anexoCXC->accountsReceivableFiles_path;
        $anexoCXC->delete();
        Storage::disk('empresas')->delete($ruta);
        return redirect()->route('vista.modulo.cxc.anexos', $anexoCXC->accountsReceivableFiles_keyaccountsReceivable)->with('message', 'Archivo eliminado correctamente')->with('status', 200);
    }
}
