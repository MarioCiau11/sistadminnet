<?php

namespace App\Http\Controllers\erpNet\ConfiguracionGeneral;

use App\Exports\ConfUnidadesEmpExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CONF_UNITS;
use App\Models\catalogos\CONF_PACKAGING_UNITS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;

class UnidadesEmpaqueController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:Unidades Empaque']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $unidadesEmp_collection = CONF_PACKAGING_UNITS::where('packaging_units_status', '=', 'Alta')->orderBy('packaging_units_id', 'desc')->get();
        $unidadesEmp_array = $unidadesEmp_collection->toArray();
        return view('page.ConfiguracionGeneral.Unidad.UnidadesEmpaque.index', compact('unidadesEmp_array'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $unidad_unidad_array = $this->selectUnidades();
        return view('page.ConfiguracionGeneral.Unidad.UnidadesEmpaque.create', compact('unidad_unidad_array'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $conf_unidadEmp_request = $request->except('_token'); //rechazamos el token que nos pasa el formulario
        // dd($conf_unidadEmp_request);
        $isKeyUnidadEmp = CONF_PACKAGING_UNITS::where('packaging_units_packaging', $conf_unidadEmp_request['nameUnidadEmpaque'])->first();

        if ($isKeyUnidadEmp) {
            $message = "La clave: " . $conf_unidadEmp_request['nameUnidadEmpaque'] . " ya existe en la base de datos";
            $status = false;
        } else {
            $conf_unidadEmp = new CONF_PACKAGING_UNITS(); //creamos un objeto de la clase Conf_Monedas

            //Agremos la nueva información a las columnas correspondientes de la tabla Conf_Monedas
            $conf_unidadEmp->packaging_units_packaging = $conf_unidadEmp_request['nameUnidadEmpaque'];
            $conf_unidadEmp->packaging_units_weight = $conf_unidadEmp_request['namePeso'];
            $conf_unidadEmp->packaging_units_unit =  $conf_unidadEmp_request['nameUnidad'];
            $conf_unidadEmp->packaging_units_status = $conf_unidadEmp_request['statusDG'];
            try {
                $isCreate =  $conf_unidadEmp->save();
                if ($isCreate) {
                    $message = "La clave: " . $conf_unidadEmp_request['nameUnidadEmpaque'] . " se registró correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido crear la configuración moneda: " . $conf_unidadEmp_request['nameUnidadEmpaque'];
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor, vaya con el administrador de sistemas, no se pudo crear la configuración moneda: " . $conf_unidadEmp_request['nameUnidadEmpaque'];
                return redirect()->route('configuracion.unidades-empaque.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('configuracion.unidades-empaque.index')->with('message', $message)->with('status', $status);
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
            $unidad_collection = CONF_PACKAGING_UNITS::where('packaging_units_id', $id)->first();
            $unidadEmp_array = $unidad_collection->toArray();
            $unidad_unidad_array = $this->selectUnidades2();
            return view('page.ConfiguracionGeneral.Unidad.UnidadesEmpaque.show', compact('unidad_unidad_array', 'unidadEmp_array'));
        } catch (\Exception $e) {
            $message = "Error al mostrar la unidad: " . $id;
            return redirect()->route('configuracion.unidades-empaque.index')->with('message', $message)->with('status', false);
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
            $unidadEmp_edit = CONF_PACKAGING_UNITS::where('packaging_units_id', $id)->first();
            $unidad_unidad_array = $this->selectUnidades();
            return view('page.ConfiguracionGeneral.Unidad.UnidadesEmpaque.edit', compact('unidad_unidad_array', 'unidadEmp_edit'));
        } catch (\Exception $e) {
            $message = "Error al editar la unidad";
            return redirect()->route('configuracion.unidades-empaque.index')->with('message', $message)->with('status', false);
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
            $editEmp_unidad_request = $request->except('_token');
            $unidad =  CONF_PACKAGING_UNITS::where('packaging_units_id', $id)->first();
            $unidad->packaging_units_weight = $editEmp_unidad_request['namePeso'];
            $unidad->packaging_units_unit = $editEmp_unidad_request['nameUnidad'];
            $unidad->packaging_units_status = $editEmp_unidad_request['statusDG'];

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
                dd($e);
                $message = "Error al actualizar la unidad: ";
                return redirect()->route('configuracion.unidades-empaque.index')->with('message', $message)->with('status', false);
            }
        } catch (\Exception $e) {
            $message = "Error al editar la unidad";
            return redirect()->route('configuracion.unidades-empaque.index')->with('message', $message)->with('status', false);
        }
        return redirect()->route('configuracion.unidades-empaque.index')->with('message', $message)->with('status', $status);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $unidadEmp_delete = CONF_PACKAGING_UNITS::where('packaging_units_id', $id)->first();
        $unidadEmp_delete->packaging_units_status = 'Baja';

        $isRemoved = $unidadEmp_delete->update();
        $status = false;
        if ($isRemoved) {
            $message = "La unidad: " . $unidadEmp_delete->packaging_units_packaging . " se ha eliminado correctamente";
            $status = true;
        } else {
            $message = "La unidad: " . $unidadEmp_delete->packaging_units_packaging . " no se ha eliminado correctamente";
            $status = false;
        }

        return redirect()->route('configuracion.unidades-empaque.index')->with('message', $message)->with('status', $status);
    }

    public function unidadEmpAction(Request $request)
    {

        $keyUnidad = $request->keyUnidad;
        $status = $request->status;
        //    dd($keyUnidad, $status);
        switch ($request->input('action')) {
            case 'Búsqueda':
                if (!$keyUnidad) {
                    $unidad_collection_filtro = CONF_PACKAGING_UNITS::whereStatus($status)->get();
                } else {
                    $unidad_collection_filtro = CONF_PACKAGING_UNITS::whereUnitPackaging($keyUnidad)->whereStatus($status)->get();
                }

                $unidad_array_filtro = $unidad_collection_filtro->toArray();
                return redirect()->route('configuracion.unidades-empaque.index')->with('unidad_array_filtro', $unidad_array_filtro)->with('keyUnidad', $keyUnidad)->with('status', $status);
                break;

            case 'Exportar excel':
                $unidadEmp =  new ConfUnidadesEmpExport($keyUnidad, $status);
                return Excel::download($unidadEmp, 'unidadesEmpaque.xlsx');
                break;

            default:
                break;
        }
    }

    public function selectUnidades()
    {
        $unidad_array = [];
        $unidad_key_sat_collection = CONF_UNITS::where('units_status', '=', 'Alta')->orderBy('units_id', 'desc')->get();
        $unidad_key_sat_array = $unidad_key_sat_collection->toArray();

        foreach ($unidad_key_sat_array as $key => $value) {
            $unidad_array[$value['units_unit']] = $value['units_unit'];
        }
        return $unidad_array;
    }

    public function selectUnidades2()
    {
        $unidad_array = [];
        $unidad_key_sat_collection = CONF_UNITS::all();
        $unidad_key_sat_array = $unidad_key_sat_collection->toArray();

        foreach ($unidad_key_sat_array as $key => $value) {
            $unidad_array[$value['units_unit']] = $value['units_unit'];
        }
        return $unidad_array;
    }
}
