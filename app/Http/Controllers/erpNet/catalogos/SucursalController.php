<?php

namespace App\Http\Controllers\erpNet\catalogos;

use App\Exports\CatSucursalExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_COMPANIES;
use App\Models\catalogos\CAT_MONEY_ACCOUNTS;
use App\Models\catalogosSAT\CAT_SAT_ESTADO;
use App\Models\catalogosSAT\CAT_SAT_MUNICIPIO;
use App\Models\catalogosSAT\CAT_SAT_PAIS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;


class SucursalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:Sucursal']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sucursales = CAT_BRANCH_OFFICES::join('CAT_COMPANIES', 'CAT_BRANCH_OFFICES.branchOffices_companyId', '=', 'CAT_COMPANIES.companies_key')
            ->select('CAT_BRANCH_OFFICES.*', 'CAT_COMPANIES.companies_name')
            ->where('CAT_BRANCH_OFFICES.branchOffices_status', '=', 'Alta')
            ->get();


        return view('page.catalogos.sucursal.index', compact('sucursales'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $empresas = $this->getEmpresas();
        $create_pais_array = $this->selectPais();
        $create_estado_array = $this->selectEstado();
        $create_ciudad_array = $this->selectCiudad();
        $cuentas_array = $this->selectCuentas();
        return view('page.catalogos.sucursal.create', compact('create_pais_array', 'create_estado_array', 'create_ciudad_array', 'empresas', 'cuentas_array'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $cat_sucursales_request = $request->except('_token');
        $isKeySucursal = CAT_BRANCH_OFFICES::where('branchOffices_key', $cat_sucursales_request['keySucursal'])->first();
        if ($isKeySucursal) {
            $message = "La clave: " . $cat_sucursales_request['keySucursal'] . " ya existe en la base de datos";
            $status = false;
        } else {
            $cat_sucursal = new CAT_BRANCH_OFFICES();

            $cat_sucursal->branchOffices_key = $cat_sucursales_request['keySucursal'];
            $cat_sucursal->branchOffices_companyId = $cat_sucursales_request['empresa'];
            $cat_sucursal->branchOffices_name = $cat_sucursales_request['nameSucursal'];
            $cat_sucursal->branchOffices_status = $cat_sucursales_request['statusDG'];
            $cat_sucursal->branchOffices_addres = $cat_sucursales_request['address'];
            $cat_sucursal->branchOffices_suburb = $cat_sucursales_request['coloniaBusqueda'];
            $cat_sucursal->branchOffices_cp = $cat_sucursales_request['cpBusqueda'];
            $cat_sucursal->branchOffices_city = $cat_sucursales_request['city'];
            $cat_sucursal->branchOffices_state = $cat_sucursales_request['state'];
            $cat_sucursal->branchOffices_country = $cat_sucursales_request['country'];
            $cat_sucursal->branchOffices_concentrationAccount = $cat_sucursales_request['selectCuenta'];

            try {
                $isCreate = $cat_sucursal->save();

                if ($isCreate) {
                    $message = "La clave: " . $cat_sucursales_request['keySucursal'] . " se registró correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido crear la sucursal " . $cat_sucursales_request['nameSucursal'];
                    $status = false;
                }
            } catch (\Throwable $th) {
                dd($th);
                $message = "Por favor comuníquese con el administrador de sistemas ya que no se pudo crear la sucursal.";
                return redirect()->route('catalogo.sucursal.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('catalogo.sucursal.index')->with('message', $message)->with('status', $status);
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
            $sucursal = CAT_BRANCH_OFFICES::where('branchOffices_id', '=', $id)->first();
            $empresas = $this->getEmpresas();
            $show_pais_array = $this->selectPais();
            $show_estado_array = $this->selectEstado();
            $show_ciudad_array = $this->selectCiudad();
            $show_cuentas_array = $this->selectCuentas();
            return view('page.catalogos.sucursal.show', compact('show_pais_array', 'show_estado_array', 'show_ciudad_array', 'sucursal', 'empresas', 'show_cuentas_array'));
        } catch (\Exception $e) {
            return redirect()->route('catalogo.sucursal.index')->with('message', 'No se pudo encontrar la sucursal')->with('status', false);
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
            $sucursal = CAT_BRANCH_OFFICES::where('branchOffices_id', '=', $id)->first();
            $empresas = $this->getEmpresas();
            $edit_pais_array = $this->selectPais();
            $edit_estado_array = $this->selectEstado();
            $edit_ciudad_array = $this->selectCiudad();
            $edit_cuentas_array = $this->selectCuentas();
            return view('page.catalogos.sucursal.edit', compact('edit_pais_array', 'edit_estado_array', 'edit_ciudad_array', 'sucursal', 'empresas', 'edit_cuentas_array'));
        } catch (\Exception $e) {
            return redirect()->route('catalogo.sucursal.index')->with('message', 'No se pudo encontrar la sucursal')->with('status', false);
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
            $cat_sucursal = CAT_BRANCH_OFFICES::where('branchOffices_id', '=', $id)->first();
            $cat_sucursales_request = $request->except('_token');
            $cat_sucursal->branchOffices_companyId = $cat_sucursales_request['empresa'];
            $cat_sucursal->branchOffices_name = $cat_sucursales_request['nameSucursal'];
            $cat_sucursal->branchOffices_status = $cat_sucursales_request['statusDG'];
            $cat_sucursal->branchOffices_addres = $cat_sucursales_request['address'];
            $cat_sucursal->branchOffices_suburb = $cat_sucursales_request['coloniaBusqueda'];
            $cat_sucursal->branchOffices_cp = $cat_sucursales_request['cpBusqueda'];
            $cat_sucursal->branchOffices_city = $cat_sucursales_request['city'];
            $cat_sucursal->branchOffices_state = $cat_sucursales_request['state'];
            $cat_sucursal->branchOffices_country = $cat_sucursales_request['country'];
            $cat_sucursal->branchOffices_concentrationAccount = $cat_sucursales_request['selectCuenta'];

            try {
                $isUpdate =  $cat_sucursal->update();
                if ($isUpdate) {
                    $message = "La sucursal se actualizó correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido actualizar la sucursal";
                    $status = false;
                }
            } catch (\Throwable $th) {
                $message = "Por favor, vaya con el administrador de sistemas, no se puede actualizar la sucursal: ";
                return redirect()->route('catalogo.sucursal.index')->with('message', $message)->with('status', false);
            }

            return redirect()->route('catalogo.sucursal.index')->with('message', $message)->with('status', $status);
        } catch (\Exception $e) {
            return redirect()->route('catalogo.sucursal.index')->with('message', 'No se pudo actualizar la sucursal')->with('status', false);
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
            $sucursal_delete = CAT_BRANCH_OFFICES::where('branchOffices_id', $id)->first();
            $sucursal_delete->branchOffices_status = 'Baja';

            $isRemoved = $sucursal_delete->update();
            $status = false;
            if ($isRemoved) {
                $message = "El almacen se eliminó correctamente";
                $status = true;
            } else {
                $message = "No se ha podido eliminar el almacen";
                $status = false;
            }

            return redirect()->route('catalogo.sucursal.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('catalogo.sucursal.index')->with('message', 'No se pudo mostrar el almacen')->with('status', false);
        }
    }

    public function sucursalAction(Request $request)
    {
        $keySucursal = $request->keySucursal;
        $nameSucursal = $request->nameSucursal;
        $status = $request->status;
        switch ($request->input('action')) {
            case 'Búsqueda':

                $sucursal_collection_filtro = CAT_BRANCH_OFFICES::join('CAT_COMPANIES', 'CAT_BRANCH_OFFICES.branchOffices_companyId', '=', 'CAT_COMPANIES.companies_key')
                    ->select('CAT_BRANCH_OFFICES.*', 'CAT_COMPANIES.companies_name')->wherebranchOfficesKey($keySucursal)->wherebranchOfficesName($nameSucursal)->wherebranchOfficesStatus($status)->get();


                return redirect()->route('catalogo.sucursal.index')->with('sucursal_filtro', $sucursal_collection_filtro)->with('keySucursal', $keySucursal)->with('nameSucursal', $nameSucursal)->with('status', $status);
                break;

            case 'Exportar excel':
                $sucursal = new CatSucursalExport($keySucursal, $nameSucursal, $status);
                return Excel::download($sucursal, 'sucursales.xlsx');
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
            $city_array[$value['c_Municipio'] . '-' . $value['c_Estado']] = $value['descripcion'] . ' - ' . $value['c_Municipio'];
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

    function selectCuentas()
    {
        $cajas = CAT_MONEY_ACCOUNTS::where('moneyAccounts_status', '=', 'Alta')->get();
        $cajas_array = array();
        foreach ($cajas as $caja) {
            $cajas_array[$caja->moneyAccounts_key] = $caja->moneyAccounts_key . ' - ' . $caja->moneyAccounts_bank;
        }
        return $cajas_array;
    }

    public function getEmpresas()
    {
        $empresas = [];
        $empresas_collection = CAT_COMPANIES::where('companies_status', 'Alta')->get();
        $empresas_array = $empresas_collection->toArray();

        foreach ($empresas_array as $key => $value) {
            $empresas[$value['companies_key']] = $value['companies_name'];
        }
        return $empresas;
    }
}
