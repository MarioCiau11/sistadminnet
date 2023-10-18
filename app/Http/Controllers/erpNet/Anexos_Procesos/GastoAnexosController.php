<?php

namespace App\Http\Controllers\erpNet\Anexos_Procesos;

use App\Http\Controllers\Controller;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\modulos\PROC_EXPENSES;
use App\Models\modulos\PROC_EXPENSES_FILES;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GastoAnexosController extends Controller
{
    public function index($id)
    {
        $anexosFiles = PROC_EXPENSES_FILES::WHERE('expensesFiles_keyExpense', '=', $id)->get();
        $movimiento = PROC_EXPENSES::WHERE('expenses_id', '=', $id)->first();
        return view('page.modulos.Gestion_y_Finanzas.Gastos.anexos.index-anexo', compact('id', 'anexosFiles', 'movimiento'));
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

        $rutaDestino = $empresaRuta . '/Gastos/' . $año . '/' . $mes . '/' . $id;
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();

        $isExistRuta = Storage::disk('empresas')->exists(str_replace(['//', '///', '////'], '/', $empresaRuta));

        if (!$isExistRuta) {
            Storage::disk('empresas')->makeDirectory(str_replace(['//', '///', '////'], '/', $empresaRuta));
        }

        //Crearemos el directorio de Modulos/Factura de Gasto/{id}
        Storage::disk('empresas')->makeDirectory(str_replace(['//', '///', '////'], '/', $rutaDestino));
        Storage::disk('empresas')->put(str_replace(['//', '///', '////'], '/', $rutaDestino . '/' . $fileName), file_get_contents($file));

        try {
            //Comprobamos si el nombre existe en la db
            $anexoGasto = PROC_EXPENSES_FILES::WHERE('expensesFiles_file', '=', $fileName)->WHERE('expensesFiles_keyExpense', '=', $id)->first();

            if ($anexoGasto === null) {
                $anexosGasto = new PROC_EXPENSES_FILES();
            }
            //Insertamos los datos del file en la db
            $anexosGasto->expensesFiles_keyExpense = $id; //Id del gasto
            $anexosGasto->expensesFiles_path = str_replace(['//', '///', '////'], '/', $rutaDestino . '/' . $fileName);
            $anexosGasto->expensesFiles_file = $fileName;
            $anexosGasto->save();
        } catch (\Throwable $th) {
            return response()->json(['status' => 500, 'mensaje' => 'Error al guardar los archivos']);
        }

        return response()->json(['status' => 200, 'mensaje' => 'Archivo guardado correctamente']);
    }

    public function destroy($id)
    {
        $anexoGasto = PROC_EXPENSES_FILES::WHERE('expensesFiles_id', '=', $id)->first();
        $ruta = $anexoGasto->expensesFiles_path;
        $anexoGasto->delete();
        Storage::disk('empresas')->delete($ruta);
        return redirect()->route('vista.modulo.gastos.anexos', $anexoGasto->expensesFiles_keyExpense)->with('message', 'Archivo eliminado correctamente')->with('status', 200);
    }
}
