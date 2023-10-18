<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Exports\CAT_VehiculosExport;
use App\Http\Controllers\Controller;
use App\Models\agrupadores\CAT_VEHICLES_CATEGORY;
use App\Models\agrupadores\CAT_VEHICLES_GROUP;
use App\Models\catalogos\CAT_AGENTS;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_VEHICLES;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;

class VehiculosController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:Vehículos']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categoria_array = $this->SelectCategorias();
        $grupo_array = $this->SelectGrupos();
        $vehiculos = CAT_VEHICLES::join('CAT_BRANCH_OFFICES', 'CAT_VEHICLES.vehicles_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
            ->select('CAT_VEHICLES.*', 'CAT_BRANCH_OFFICES.branchOffices_name')
            ->where('CAT_VEHICLES.vehicles_status', '=', 'Alta')
            ->get();
        return view('page.catalogos.Vehiculos.index', compact('vehiculos', 'categoria_array', 'grupo_array'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $select_agente = $this->selectAgentes();
        $select_sucursales = $this->SelectSucursales();
        $select_categoria = $this->SelectCategorias();
        $select_grupo = $this->SelectGrupos();
        return view('page.catalogos.Vehiculos.create', compact('select_agente', 'select_sucursales', 'select_categoria', 'select_grupo'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $cat_vehiculos_request = $request->except('_token');
        $isKeyVehiculo = CAT_VEHICLES::where('vehicles_name', $cat_vehiculos_request['nameNombre'])->first();
        if ($isKeyVehiculo) {
            $message = "El agente: " . $cat_vehiculos_request['nameNombre'] . " ya existe";
            $status = false;
        } else {
            $cat_vehiculos = new CAT_VEHICLES();
            $cat_vehiculos->vehicles_name = $cat_vehiculos_request['nameNombre'];
            $cat_vehiculos->vehicles_plates = $cat_vehiculos_request['namePlacas'];
            $cat_vehiculos->vehicles_capacityVolume = (float)$cat_vehiculos_request['capacidadVolumen'];
            $cat_vehiculos->vehicles_capacityWeight = (float)$cat_vehiculos_request['capacidadPeso'];
            $cat_vehiculos->vehicles_defaultAgent = $cat_vehiculos_request['agenteXOmision'];
            $cat_vehiculos->vehicles_branchOffice = $cat_vehiculos_request['nameSucursal'];
            $cat_vehiculos->vehicles_status = $cat_vehiculos_request['statusDG'];
            $cat_vehiculos->vehicles_category = $cat_vehiculos_request['nameCategoria'];
            $cat_vehiculos->vehicles_group = $cat_vehiculos_request['nameGrupo'];

            try {
                $isCreate = $cat_vehiculos->save();

                if ($isCreate) {
                    $message = "El Vehículo: " . $cat_vehiculos_request['nameNombre'] . " se ha guardado correctamente";
                    $status = true;
                } else {
                    $message = "El Vehículo: " . $cat_vehiculos_request['nameNombre'] . " no se ha guardado correctamente";
                    $status = false;
                }
            } catch (\Throwable $th) {
                dd($th);
                $message = "Error al guardar el Vehículo: " . $cat_vehiculos_request['nameNombre'];
                return redirect()->route('catalogo.vehiculos.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('catalogo.vehiculos.index')->with('message', $message)->with('status', $status);
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
            $select_agente = $this->selectAgentes();
            $select_sucursales = $this->SelectSucursales();
            $select_categoria = $this->SelectCategorias();
            $select_grupo = $this->SelectGrupos();
            $vehiculo = CAT_VEHICLES::where('vehicles_key', $id)->first();
            return view('page.catalogos.Vehiculos.show', compact('vehiculo', 'select_agente', 'select_sucursales', 'select_categoria', 'select_grupo'));
        } catch (\Exception $e) {
            return redirect()->route('catalogo.vehiculos.index')->with('message', 'Error al mostrar el Vehículo')->with('status', false);
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
            $select_agente = $this->selectAgentes();
            $select_sucursales = $this->SelectSucursales();
            $select_categoria = $this->SelectCategorias();
            $select_grupo = $this->SelectGrupos();
            $vehiculo_edit = CAT_VEHICLES::where('vehicles_key', $id)->first();
            return view('page.catalogos.Vehiculos.edit', compact('vehiculo_edit', 'select_agente', 'select_sucursales', 'select_categoria', 'select_grupo'));
        } catch (\Exception $e) {
            return redirect()->route('catalogo.vehiculos.index')->with('message', 'Error al mostrar el Vehículo')->with('status', false);
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

            $vehiculo = CAT_VEHICLES::where('vehicles_key', $id)->first();
            $edit_vehiculo_request = $request->except('_token');
            $vehiculo->vehicles_name = $edit_vehiculo_request['nameNombre'];
            $vehiculo->vehicles_plates = $edit_vehiculo_request['namePlacas'];
            $vehiculo->vehicles_capacityVolume = (float)$edit_vehiculo_request['capacidadVolumen'];
            $vehiculo->vehicles_capacityWeight = (float)$edit_vehiculo_request['capacidadPeso'];
            $vehiculo->vehicles_defaultAgent = $edit_vehiculo_request['agenteXOmision'];
            $vehiculo->vehicles_branchOffice = $edit_vehiculo_request['nameSucursal'];
            $vehiculo->vehicles_status = $edit_vehiculo_request['statusDG'];
            $vehiculo->vehicles_category = $edit_vehiculo_request['nameCategoria'];
            $vehiculo->vehicles_group = $edit_vehiculo_request['nameGrupo'];

            try {
                $isUpdate = $vehiculo->save();
                if ($isUpdate) {
                    $message = "El Vehículo: " . $edit_vehiculo_request['nameNombre'] . " se ha actualizado correctamente";
                    $status = true;
                } else {
                    $message = "El Vehículo: " . $edit_vehiculo_request['nameNombre'] . " no se ha actualizado correctamente";
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Error al actualizar el Vehículo: " . $edit_vehiculo_request['nameNombre'];
                return redirect()->route('catalogo.vehiculos.index')->with('message', $message)->with('status', false);
            }
            return redirect()->route('catalogo.vehiculos.index')->with('message', $message)->with('status', $status);
        } catch (\Exception $e) {
            return redirect()->route('catalogo.vehiculos.index')->with('message', 'Error al actualizar el Vehículo')->with('status', false);
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
            $vehiculo_delete = CAT_VEHICLES::where('vehicles_key', $id)->first();
            $vehiculo_delete->vehicles_status = 'Baja';

            $isRemoved = $vehiculo_delete->update();
            $status = false;


            if ($isRemoved) {
                $message = "El Vehículo: " . $vehiculo_delete->vehicles_name . " se ha eliminado correctamente";
                $status = true;
            } else {
                $message = "El Vehículo: " . $vehiculo_delete->vehicles_name . " no se ha eliminado correctamente";
                $status = false;
            }
            return redirect()->route('catalogo.vehiculos.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            $message = "Error al eliminar el Vehículo: " . $vehiculo_delete->vehicles_name;
            return redirect()->route('catalogo.vehiculos.index')->with('message', $message)->with('status', false);
        }
    }

    public function vehiculosAction(Request $request)
    {
        $keyVehiculo = $request->keyVehiculo;
        $nameVehiculo = $request->nameVehiculo;
        $status = $request->status;

        switch ($request->input('action')) {
            case 'Búsqueda':
                $vehiculo_collection_filtro = CAT_VEHICLES::join('CAT_BRANCH_OFFICES', 'CAT_VEHICLES.vehicles_branchOffice', '=', 'CAT_BRANCH_OFFICES.branchOffices_key')
                    ->select('CAT_VEHICLES.*', 'CAT_BRANCH_OFFICES.branchOffices_name')->whereVehicleKey($keyVehiculo)->whereVehicleName($nameVehiculo)->whereVehicleStatus($status)->get();

                return redirect()->route('catalogo.vehiculos.index')->with('vehiculo_filtro_array', $vehiculo_collection_filtro)->with('keyVehiculo', $keyVehiculo)->with('nameVehiculo', $nameVehiculo)->with('status', $status);
                break;

            case 'Exportar excel':
                $vehiculo = new CAT_VehiculosExport($keyVehiculo, $nameVehiculo, $status);
                return Excel::download($vehiculo, 'vehiculos.xlsx');
                break;

            default:
                break;
        }
    }

    public function selectAgentes()
    {
        $agente_array = [];
        $agente_key_sat_collection = CAT_AGENTS::where('agents_status', '=', 'Alta')->get();
        $agente_key_sat_array = $agente_key_sat_collection->toArray();

        foreach ($agente_key_sat_array as $key => $value) {
            $agente_array[$value['agents_name']] = $value['agents_key'] . '.- ' . $value['agents_name'];
        }
        return $agente_array;
    }

    function SelectSucursales()
    {
        $sucursales = CAT_BRANCH_OFFICES::where('branchOffices_status', '=', 'Alta')->get();
        $sucursales_array = array();
        foreach ($sucursales as $sucursal) {
            $sucursales_array[$sucursal->branchOffices_key] = $sucursal->branchOffices_name;
        }
        return $sucursales_array;
    }

    public function getIDVehiculo()
    {
        $idLast = CAT_VEHICLES::all()->last();
        if ($idLast == null) {
            $getid = 0;
        } else {
            $getid = $idLast->vehicles_key;
        }
        //    $getCategoria= $idLast->categoriaProveedor_id;
        $VehiculoID = $getid + 1;

        return response()->json(['vehiculo' => $VehiculoID]);
    }

    function selectCategorias()
    {
        $categorias = CAT_VEHICLES_CATEGORY::where('categoryVehicle_status', '=', 'Alta')->orderBy('categoryVehicle_id', 'ASC')->get();
        $categorias_array = array();
        foreach ($categorias as $categoria) {
            $categorias_array[$categoria->categoryVehicle_name] = $categoria->categoryVehicle_name;
        }
        return $categorias_array;
    }

    function selectGrupos()
    {
        $grupos = CAT_VEHICLES_GROUP::where('groupVehicle_status', '=', 'Alta')->orderBy('groupVehicle_id', 'ASC')->get();
        $grupos_array = array();
        foreach ($grupos as $grupo) {
            $grupos_array[$grupo->groupVehicle_name] = $grupo->groupVehicle_name;
        }
        return $grupos_array;
    }
}
