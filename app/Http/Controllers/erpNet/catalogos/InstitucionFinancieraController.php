<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Exports\InstFinancialExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_FINANCIAL_INSTITUTIONS;
use App\Models\catalogosSAT\CAT_SAT_ESTADO;
use App\Models\catalogosSAT\CAT_SAT_MUNICIPIO;
use App\Models\catalogosSAT\CAT_SAT_PAIS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;


class InstitucionFinancieraController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:Instituciones Financieras']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $instituciones = CAT_FINANCIAL_INSTITUTIONS::where('instFinancial_status', '=', 'Alta')->orderBy('instFinancial_id', 'desc')->get();
        return view('page.catalogos.cuentaDinero.institucion-financiera.index', compact('instituciones'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $create_pais_array = $this->selectPais();
        $create_estado_array = $this->selectEstado();
        $create_ciudad_array = $this->selectCiudad();
        return view('page.catalogos.cuentaDinero.institucion-financiera.create', compact('create_pais_array', 'create_estado_array', 'create_ciudad_array'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $cat_instsFinancial_request = $request->except('_token');
        $isKeyInstFinancial = CAT_FINANCIAL_INSTITUTIONS::where('instFinancial_key', '=', $cat_instsFinancial_request['keyInstitucionFinanciera'])->first();

        if ($isKeyInstFinancial) {
            $message = "La clave: " . $cat_instsFinancial_request['keyInstitucionFinanciera'] . " ya existe en la base de datos";
            $status = false;
        } else {
            $cat_InstFinancial = new CAT_FINANCIAL_INSTITUTIONS();
            $cat_InstFinancial->instFinancial_key = $cat_instsFinancial_request['keyInstitucionFinanciera'];
            $cat_InstFinancial->instFinancial_name = $cat_instsFinancial_request['nameInstitucionFinanciera'];
            $cat_InstFinancial->instFinancial_status = $cat_instsFinancial_request['status'];
            $cat_InstFinancial->instFinancial_city = $cat_instsFinancial_request['ciudad'];
            $cat_InstFinancial->instFinancial_state = $cat_instsFinancial_request['estado'];
            $cat_InstFinancial->instFinancial_country = $cat_instsFinancial_request['pais'];

            try {
                $isCreate = $cat_InstFinancial->save();

                if ($isCreate) {
                    $message = "La clave: " . $cat_instsFinancial_request['keyInstitucionFinanciera'] . " se registró correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido crear la InstFinancial " . $cat_instsFinancial_request['nameInstitucionFinanciera'];
                    $status = false;
                }
            } catch (\Throwable $th) {
                dd($th);
                $message = "Por favor comuníquese con el administrador de sistemas ya que no se pudo crear la institución financiera.";
                return redirect()->route('catalogo.instituciones-financieras.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('catalogo.instituciones-financieras.index')->with('message', $message)->with('status', $status);
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
            $instFinancial = CAT_FINANCIAL_INSTITUTIONS::where('instFinancial_id', '=', $id)->first();

            $show_pais_array = $this->selectPais();
            $show_estado_array = $this->selectEstado();
            $show_ciudad_array = $this->selectCiudad();

            return view('page.catalogos.cuentaDinero.institucion-financiera.show', compact('instFinancial', 'show_pais_array', 'show_estado_array', 'show_ciudad_array'));
        } catch (\Exception $e) {
            return redirect()->route('catalogo.instituciones-financieras.index')->with('message', 'No se pudo encontrar la institución financiera')->with('status', false);
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
            $instFinancial = CAT_FINANCIAL_INSTITUTIONS::where('instFinancial_id', '=', $id)->first();

            $edit_pais_array = $this->selectPais();
            $edit_estado_array = $this->selectEstado();
            $edit_ciudad_array = $this->selectCiudad();

            return view('page.catalogos.cuentaDinero.institucion-financiera.edit', compact('instFinancial', 'edit_pais_array', 'edit_estado_array', 'edit_ciudad_array'));
        } catch (\Exception $e) {
            return redirect()->route('catalogo.instituciones-financieras.index')->with('message', 'No se pudo encontrar la institución financiera')->with('status', false);
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
            $cat_instsFinancial_request = $request->except('_token');
            $cat_InstFinancial = CAT_FINANCIAL_INSTITUTIONS::where('instFinancial_id', '=', $id)->first();
            $cat_InstFinancial->instFinancial_name = $cat_instsFinancial_request['nameInstitucionFinanciera'];
            $cat_InstFinancial->instFinancial_status = $cat_instsFinancial_request['status'];
            $cat_InstFinancial->instFinancial_city = $cat_instsFinancial_request['ciudad'];
            $cat_InstFinancial->instFinancial_state = $cat_instsFinancial_request['estado'];
            $cat_InstFinancial->instFinancial_country = $cat_instsFinancial_request['pais'];

            try {
                $isUpdate =  $cat_InstFinancial->update();
                if ($isUpdate) {
                    $message = "La institución financiera se actualizó correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido actualizar la institución financiera";
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor, vaya con el administrador de sistemas, no se puede actualizar la institución financiera";
                return redirect()->route('catalogo.instituciones-financieras.index')->with('message', $message)->with('status', false);
            }

            return redirect()->route('catalogo.instituciones-financieras.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('catalogo.instituciones-financieras.index')->with('message', 'No se pudo encontrar la institución financiera')->with('status', false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $id = Crypt::decrypt($id);

            $instFinancial_delete = CAT_FINANCIAL_INSTITUTIONS::where('instFinancial_id', $id)->first();
            $instFinancial_delete->instFinancial_status = 'Baja';

            $isRemoved = $instFinancial_delete->update();
            $status = false;
            if ($isRemoved) {
                $message = "La institución financiera se eliminó correctamente";
                $status = true;
            } else {
                $message = "No se ha podido eliminar la institución financiera";
                $status = false;
            }

            return redirect()->route('catalogo.instituciones-financieras.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('catalogo.instituciones-financieras.index')->with('message', 'No se pudo mostrar la institución financiera')->with('status', false);
        }
    }


    public function instFinancialAction(Request $request)
    {
        $keyInstFinancial = $request->keyInstFinancial;
        $nameInstFinancial = $request->nameInstFinancial;
        $status = $request->status;
        switch ($request->input('action')) {
            case 'Búsqueda':
                $instFinancial = CAT_FINANCIAL_INSTITUTIONS::whereInstFinancialKey($keyInstFinancial)->whereInsFinancialName($nameInstFinancial)->whereInsFinancialStatus($status)->get();


                return redirect()->route('catalogo.instituciones-financieras.index')->with('instFinancial', $instFinancial)->with('keyInstFinancial', $keyInstFinancial)->with('nameInstFinancial', $nameInstFinancial)->with('status', $status);
                break;

            case 'Exportar excel':
                $instFinancial = new InstFinancialExport($keyInstFinancial, $nameInstFinancial, $status);
                return Excel::download($instFinancial, 'Instituciónes Financieras.xlsx');
                break;

            default:
                break;
        }
    }

    public function selectPais()
    {
        $country_array = [];
        $country_key_sat_collection = CAT_SAT_PAIS::all();
        $country_key_sat_array = $country_key_sat_collection->toArray();

        foreach ($country_key_sat_array as $key => $value) {
            $country_array[$value['c_Pais']] = $value['descripcion'] . ' - ' . $value['c_Pais'];
        }
        return $country_array;
    }

    public function selectCiudad()
    {
        $city_array = [];
        $city_key_sat_collection = CAT_SAT_MUNICIPIO::all();
        $city_key_sat_array = $city_key_sat_collection->toArray();

        foreach ($city_key_sat_array as $key => $value) {
            $city_array[$value['c_Municipio'] . '-' . $value['descripcion']] = $value['descripcion'] . ' - ' . $value['c_Municipio'];
        }
        return $city_array;
    }

    public function selectEstado()
    {
        $state_array = [];
        $state_key_sat_collection = CAT_SAT_ESTADO::all();
        $state_key_sat_array = $state_key_sat_collection->toArray();

        foreach ($state_key_sat_array as $key => $value) {
            $state_array[$value['c_Estado']] = $value['nombreEstado'] . ' - ' . $value['c_Estado'];
        }
        return $state_array;
    }
}
