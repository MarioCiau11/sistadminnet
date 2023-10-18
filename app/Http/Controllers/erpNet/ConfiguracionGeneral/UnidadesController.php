<?php

namespace App\Http\Controllers\erpNet\ConfiguracionGeneral;

use App\Http\Controllers\Controller;
use App\Models\catalogos\Conf_Unidades;
use App\Models\CatalogosSAT\CAT_SAT_CLAVEUNIDAD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ConfUnidadesExport;
use App\Models\catalogos\CONF_UNITS;

class UnidadesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:Unidades']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $unity = CONF_UNITS::join('CAT_SAT_CLAVEUNIDAD', 'CONF_UNITS.units_keySat', '=', 'CAT_SAT_CLAVEUNIDAD.c_ClaveUnidad')
            ->select('CONF_UNITS.*', 'CAT_SAT_CLAVEUNIDAD.nombre')
            ->where('CONF_UNITS.units_status', '=', 'Alta')
            ->get();
        return view('page.ConfiguracionGeneral.Unidad.Unidades.index', compact('unity'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $create_unidad_array = $this->selectUnidades();
        return view('page.ConfiguracionGeneral.Unidad.Unidades.create', compact('create_unidad_array'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $conf_unidad_request = $request->except('_token');

        // dd($conf_unidad_request);

        $isKeyUnidad = CONF_UNITS::where('units_unit', $conf_unidad_request['nameUnidad'])->first();

        if ($isKeyUnidad) {
            $message = "La unidad: " . $conf_unidad_request['nameUnidad'] . " ya existe en la base de datos";
            $status = false;
        } else {
            $conf_unidad = new CONF_UNITS(); //creamos un objeto de la clase Conf_Monedas

            $conf_unidad->units_unit = $conf_unidad_request['nameUnidad'];
            $conf_unidad->units_decimalVal = $conf_unidad_request['numDecimalValida'];
            $conf_unidad->units_keySat = $conf_unidad_request['nameclaveSAT'];
            $conf_unidad->units_status = $conf_unidad_request['statusDG'];
            try {
                $isCreated = $conf_unidad->save();
                if ($isCreated) {
                    $message = "La unidad: " . $conf_unidad_request['nameUnidad'] . " se ha creado correctamente";
                    $status = true;
                } else {
                    $message = "La unidad: " . $conf_unidad_request['nameUnidad'] . " no se ha creado correctamente";
                    $status = false;
                }
            } catch (\Exception $e) {
                $message = "Error al guardar la unidad: " . $conf_unidad_request['nameUnidad'];
                return redirect()->route('configuracion.unidades.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('configuracion.unidades.index')->with('message', $message)->with('status', $status);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $id = Crypt::decrypt($id);
            // dd($id);
            $unidad = CONF_UNITS::where('units_id', '=', $id)->first();
            $show_unidad_array = $this->selectUnidades();
            return view('page.ConfiguracionGeneral.Unidad.Unidades.show', compact('show_unidad_array', 'unidad'));
        } catch (\Exception $e) {

            return redirect()->route('configuracion.unidades.index')->with('message', 'No se pudo encontrar la almacen')->with('status', false);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $id = Crypt::decrypt($id);
            $unidad = CONF_UNITS::where('units_id', '=', $id)->first();
            $edit_unidad_array = $this->selectUnidades();
            return view('page.ConfiguracionGeneral.Unidad.Unidades.edit', compact('edit_unidad_array', 'unidad'));
        } catch (\Exception $e) {
            dd($e);
            $message = "Error al editar la unidad";
            return redirect()->route('configuracion.unidades.index')->with('message', $message)->with('status', false);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        try {
            $id = Crypt::decrypt($id);
            $edit_unidad_request = $request->except('_token');
            $unidad =  CONF_UNITS::where('units_id', $id)->first();
            $unidad->units_decimalVal = $edit_unidad_request['numDecimalValida'];
            $unidad->units_keySat = $edit_unidad_request['nameclaveSAT'];
            $unidad->units_status = $edit_unidad_request['statusDG'];

            try {
                $isUpdated = $unidad->update();
                if ($isUpdated) {
                    $message = "La unidad: se ha actualizado correctamente";
                    $status = true;
                } else {
                    $message = "La unidad:  no se ha actualizado correctamente";
                    $status = false;
                }
            } catch (\Exception $e) {
                $message = "Error al actualizar la unidad: ";
                return redirect()->route('configuracion.unidades.index')->with('message', $message)->with('status', false);
            }
        } catch (\Exception $e) {
            $message = "Error al editar la unidad";
            return redirect()->route('configuracion.unidades.index')->with('message', $message)->with('status', false);
        }
        return redirect()->route('configuracion.unidades.index')->with('message', $message)->with('status', $status);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $unidad_delete = CONF_UNITS::where('units_id', $id)->first();
        $unidad_delete->units_status = 'Baja';

        $isRemoved = $unidad_delete->update();
        $status = false;
        if ($isRemoved) {
            $message = "La unidad: " . $unidad_delete->units_unit . " se ha eliminado correctamente";
            $status = true;
        } else {
            $message = "La unidad: " . $unidad_delete->units_unit . " no se ha eliminado correctamente";
            $status = false;
        }

        return redirect()->route('configuracion.unidades.index')->with('message', $message)->with('status', $status);
    }

    public function unidadAction(Request $request)
    {

        $keyUnidad = $request->keyUnidad;
        $status = $request->status;
        //    dd($keyUnidad, $status);
        switch ($request->input('action')) {
            case 'Búsqueda':
                if (!$keyUnidad) {
                    $unidad_collection_filtro = CONF_UNITS::join('CAT_SAT_CLAVEUNIDAD', 'CONF_UNITS.units_keySat', '=', 'CAT_SAT_CLAVEUNIDAD.c_ClaveUnidad')
                        ->select('CONF_UNITS.*', 'CAT_SAT_CLAVEUNIDAD.nombre')->whereStatus($status)->get();
                } else {
                    $unidad_collection_filtro = CONF_UNITS::join('CAT_SAT_CLAVEUNIDAD', 'CONF_UNITS.units_keySat', '=', 'CAT_SAT_CLAVEUNIDAD.c_ClaveUnidad')
                        ->select('CONF_UNITS.*', 'CAT_SAT_CLAVEUNIDAD.nombre')->whereUnit($keyUnidad)->whereStatus($status)->get();
                }

                return redirect()->route('configuracion.unidades.index')->with('unidad_filtro', $unidad_collection_filtro)->with('keyUnidad', $keyUnidad)->with('status', $status);
                break;

            case 'Exportar excel':
                $unidad =  new ConfUnidadesExport($keyUnidad, $status);
                return Excel::download($unidad, 'unidades.xlsx');
                break;

            default:
                break;
        }
    }

    public function selectUnidades()
    {
        $unidad_array = [];
        $unidad_key_sat_collection = CAT_SAT_CLAVEUNIDAD::all();
        $unidad_key_sat_array = $unidad_key_sat_collection->toArray();

        foreach ($unidad_key_sat_array as $key => $value) {
            $unidad_array[$value['c_ClaveUnidad']] = $value['c_ClaveUnidad'] . ' - ' . $value['nombre'];
        }
        return $unidad_array;
    }
}
