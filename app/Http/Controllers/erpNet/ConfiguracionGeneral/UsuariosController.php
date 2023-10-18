<?php

namespace App\Http\Controllers\erpNet\ConfiguracionGeneral;

use App\Exports\ConfUsuariosExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_AGENTS;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_COMPANIES;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CAT_DEPOTS;
use App\Models\catalogos\CAT_MONEY_ACCOUNTS;
use App\Models\catalogos\CONF_ROLES;
use App\Models\catalogos\CONF_USERS_BRANCH_ALLOWED;
use App\Models\catalogos\CONF_USERS_COMPANIES;
use App\Models\Licenses;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;


class UsuariosController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:Roles y Usuarios'], ['except' => ['licenceApp', 'verificate']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_collection = User::where('user_status', '=', 'Alta')->orderBy('user_id', 'desc')->get();
        $user_array = $user_collection->toArray();
        $select_roles = $this->selectRoles();
        return view('page.ConfiguracionGeneral.RolesUsuarios.Usuarios.index', compact('user_array', 'select_roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $create_empresas_array = $this->selectEmpresas();
        $create_sucursales_array = $this->selectSucursales();
        $clientes_array = $this->selectClientes();
        $almacenes_array = $this->selectAlmacen();
        $agentes_array = $this->selectAgente();
        $cuentas_array = $this->selectCuentas();
        $cuentaCaja_array = $this->selectCuentaCaja();
        $select_roles = $this->selectRoles();
        $permisosDashboard = Permission::where('categoria', '=', 'Dashboard')->get();
        return view('page.ConfiguracionGeneral.RolesUsuarios.Usuarios.create', compact('create_empresas_array', 'select_roles', 'create_sucursales_array', 'clientes_array', 'almacenes_array', 'agentes_array', 'cuentas_array', 'cuentaCaja_array', 'permisosDashboard'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $conf_user_request = $request->except('_token'); // Obtenemos todos los datos excepto el token
        $isKeyUser = User::where('username', $conf_user_request['user'])->first();

        if ($isKeyUser) {
            $message = "El usuario: " . $conf_user_request['user'] . " ya existe en la base de datos";
            $status = false;
        } else {
            $conf_user = new User();

            //Agregamos la informacion del usuario a la base de datos
            $rol_id = Conf_roles::where('identifier', '=', $conf_user_request['rol'])->first();

            $conf_user->user_name = $conf_user_request['nombre'];
            $conf_user->username = $conf_user_request['user'];
            $conf_user->user_email = $conf_user_request['email'];
            $conf_user->password = bcrypt($conf_user_request['pass']);
            $conf_user->user_rol = $conf_user_request['rol'];
            $conf_user->user_status = $conf_user_request['status'];
            $conf_user->user_block_sale_prices = $request->input('bloq', 0);
            $conf_user->user_blockPurchaseCost = $request->input('bloqCosto', 0);
            $conf_user->user_viewPurchaseCost = $request->input('verCosto', 0);
            $conf_user->user_viewArticleInformationCost = $request->input('verCostoInfo', 0);

            $conf_user->user_defaultCustomer = $conf_user_request['selectCliente'];
            $conf_user->user_defaultDepot = $conf_user_request['selectAlmacen'];
            $conf_user->user_defaultAgent = $conf_user_request['selectAgente'];
            $conf_user->user_concentrationAccount = $conf_user_request['selectCuenta'];
            $conf_user->user_mainAccount = $conf_user_request['selectCaja'];

            // dd($conf_user_request);

            //validamos si los checksbox están activos
            $dashboard1 = $request->input('Top_10_Productos_más_Vendidos', 0);
            $dashboard2 = $request->input('Ventas_Netas_por_Familia', 0);
            $dashboard3 = $request->input('Ventas_Mes_Actual_VS_Mes_Anterior', 0);
            $dashboard4 = $request->input('Flujo_y_Ventas', 0);
            $dashboard5 = $request->input('Ventas_VS_Ganancia', 0);
            $dashboard6 = $request->input('Ganancia_VS_Gastos', 0);

            $conf_user->user_getTop10SalesArticles = $dashboard1;
            $conf_user->user_getSalesByFamily = $dashboard2;
            $conf_user->user_getCurrentSaleVSPreviousSale = $dashboard3;
            $conf_user->user_getSalesAndFlows = $dashboard4;
            $conf_user->user_calculateSalesSummary = $dashboard5;
            $conf_user->user_getEarningAndExpenses = $dashboard6;
            // dd($conf_user);

            try {

                // dd($conf_user);


                $isCreate = $conf_user->save();
                $user_data = $conf_user::latest('user_id')->first();
                $user_data->roles()->sync($rol_id->id);
                $last_idUser = $user_data->user_id;
                foreach ($conf_user_request['empresas'] as $key => $value) {
                    $conf_user_empresas = new CONF_USERS_COMPANIES();
                    $conf_user_empresas->usersCompanies_userID = $last_idUser;
                    $conf_user_empresas->usersCompanies_userCompany = $value;
                    $conf_user_empresas->save();
                }

                foreach ($conf_user_request['sucursales'] as $key => $value) {
                    $conf_user_sucursales = new CONF_USERS_BRANCH_ALLOWED();
                    $conf_user_sucursales->usersBranch_userID = $last_idUser;
                    $conf_user_sucursales->usersBranch_userBranchAllowed = $value;
                    $conf_user_sucursales->save();
                }

                if ($isCreate) {
                    $message = "El usuario: " . $conf_user_request['nombre'] . " se ha creado correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido crear al usuario: " . $conf_user_request['nombre'];
                    $status = false;
                }
            } catch (\Throwable $th) {
                // dd($th);
                $message = "Por favor, contáctese con el administrador de sistemas ya que no se ha podido crear al usuario: " . $conf_user_request['nombre'];
                return redirect()->route('configuracion.usuarios.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('configuracion.usuarios.index')->with('message', $message)->with('status', $status);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
            $empresasRelacionadasUsuario = [];
            $sucursalesRelacionadasUsuario = [];
            $user = User::where('user_id', $id)->first();
            $show_empresas_array = $this->selectEmpresas();
            $show_sucursales_array = $this->selectSucursales();
            $select_roles = $this->selectRoles();
            $show_clientes_array = $this->selectClientes();
            $show_almacenes_array = $this->selectAlmacen();
            $show_agentes_array = $this->selectAgente();
            $show_cuentas_array = $this->selectCuentas();
            $show_cuentaCaja_array = $this->selectCuentaCaja();
            $user_show_empresas = CONF_USERS_COMPANIES::where('usersCompanies_userID', $id)->get();
            $user_show_sucursales = CONF_USERS_BRANCH_ALLOWED::where('usersBranch_userID', $id)->get();
            $permisosDashboard = Permission::where('categoria', '=', 'Dashboard')->get();


            foreach ($user_show_empresas as $key => $value) {
                $empresasRelacionadasUsuario[] = $value->usersCompanies_userCompany;
            }

            foreach ($user_show_sucursales as $key => $value) {
                $sucursalesRelacionadasUsuario[] = $value->usersBranch_userBranchAllowed;
            }
            return view('page.ConfiguracionGeneral.RolesUsuarios.Usuarios.show', compact('user', 'show_empresas_array', 'empresasRelacionadasUsuario', 'select_roles', 'show_sucursales_array', 'sucursalesRelacionadasUsuario', 'show_clientes_array', 'show_almacenes_array', 'show_agentes_array', 'show_cuentas_array', 'show_cuentaCaja_array', 'permisosDashboard'));
        } catch (\exception $e) {
            return redirect()->route('configuracion.usuarios.index')->with('message', 'No se ha podido mostrar el usuario')->with('status', false);
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
        $edit_empresas_array = $this->selectEmpresas();
        $edit_sucursales_array = $this->selectSucursales();
        $select_roles = $this->selectRoles();
        $edit_clientes_array = $this->selectClientes();
        $edit_almacenes_array = $this->selectAlmacen();
        $edit_agentes_array = $this->selectAgente();
        $edit_cuentas_array = $this->selectCuentas();
        $edit_cuentaCaja_array = $this->selectCuentaCaja();
        try {
            $id = Crypt::decrypt($id);
            $empresasRelacionadasUsuario = [];
            $sucursalesRelacionadasUsuario = [];
            $user_edit = User::where('user_id', $id)->first();
            $user_edit_empresas = CONF_USERS_COMPANIES::where('usersCompanies_userID', $id)->get();
            $user_edit_sucursales = CONF_USERS_BRANCH_ALLOWED::where('usersBranch_userID', $id)->get();

            foreach ($user_edit_empresas as $key => $value) {
                $empresasRelacionadasUsuario[] = $value->usersCompanies_userCompany;
            }

            foreach ($user_edit_sucursales as $key => $value) {
                $sucursalesRelacionadasUsuario[] = $value->usersBranch_userBranchAllowed;
            }
            return view('page.ConfiguracionGeneral.RolesUsuarios.Usuarios.edit', compact('user_edit', 'edit_empresas_array', 'empresasRelacionadasUsuario', 'select_roles', 'edit_sucursales_array', 'sucursalesRelacionadasUsuario', 'edit_clientes_array', 'edit_almacenes_array', 'edit_agentes_array', 'edit_cuentas_array', 'edit_cuentaCaja_array'));
            // dd($user_edit);
        } catch (\Exception $e) {
            return redirect()->route('configuracion.usuarios.index')->with('message', 'No se ha podido encontrar el usuario')->with('status', false);
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
            $edit_user_request = $request->except('_token');
            $user = User::where('user_id', $id)->first();
            $rol_id = Conf_roles::where('identifier', '=', $edit_user_request['rol'])->first();
            $user->user_name = $edit_user_request['nombre'];
            $user->username = $edit_user_request['user'];
            $user->user_email = $edit_user_request['email'];

            isset($edit_user_request['changePassword']) ? $user->password = bcrypt($edit_user_request['pass']) : null;

            $user->user_rol = $edit_user_request['rol'];
            $user->user_status = $edit_user_request['status'];
            $user->user_block_sale_prices = isset($edit_user_request['bloq']) ? 1 : 0;
            $user->user_blockPurchaseCost = isset($edit_user_request['bloqCosto']) ? 1 : 0;
            $user->user_viewPurchaseCost = isset($edit_user_request['verCosto']) ? 1 : 0;
            $user->user_viewArticleInformationCost = isset($edit_user_request['verCostoInfo']) ? 1 : 0;
            $user->user_defaultCustomer = $edit_user_request['selectCliente'];
            $user->user_defaultDepot = $edit_user_request['selectAlmacen'];
            $user->user_defaultAgent = $edit_user_request['selectAgente'];
            $user->user_concentrationAccount = $edit_user_request['selectCuenta'];
            $user->user_mainAccount = $edit_user_request['selectCaja'];

            //validamos si los checksbox están activos
            $dashboard1 = isset($edit_user_request['Top_10_Productos_más_Vendidos']) ? 1 : 0;
            $dashboard2 = isset($edit_user_request['Ventas_Netas_por_Familia']) ? 1 : 0;
            $dashboard3 = isset($edit_user_request['Ventas_Mes_Actual_VS_Mes_Anterior']) ? 1 : 0;
            $dashboard4 = isset($edit_user_request['Flujo_y_Ventas']) ? 1 : 0;
            $dashboard5 = isset($edit_user_request['Ventas_VS_Ganancia']) ? 1 : 0;
            $dashboard6 = isset($edit_user_request['Ganancia_VS_Gastos']) ? 1 : 0;


            $user->user_getTop10SalesArticles = $dashboard1;
            $user->user_getSalesByFamily = $dashboard2;
            $user->user_getCurrentSaleVSPreviousSale = $dashboard3;
            $user->user_getSalesAndFlows = $dashboard4;
            $user->user_calculateSalesSummary = $dashboard5;
            $user->user_getEarningAndExpenses = $dashboard6;

            // dd($user);

            try {
                $isUpdate = $user->update();
                $last_idUser = $user->user_id;
                $user->roles()->sync($rol_id->id);


                CONF_USERS_COMPANIES::where('usersCompanies_userID', $last_idUser)->delete();
                CONF_USERS_BRANCH_ALLOWED::where('usersBranch_userID', $last_idUser)->delete();

                foreach ($edit_user_request['empresas'] as $key => $value) {
                    $create_user_empresa = new CONF_USERS_COMPANIES();
                    $create_user_empresa->usersCompanies_userID = $last_idUser;
                    $create_user_empresa->usersCompanies_userCompany = $value;
                    $create_user_empresa->save();
                }

                foreach ($edit_user_request['sucursales'] as $key => $value) {
                    $create_user_sucursal = new CONF_USERS_BRANCH_ALLOWED();
                    $create_user_sucursal->usersBranch_userID = $last_idUser;
                    $create_user_sucursal->usersBranch_userBranchAllowed = $value;
                    $create_user_sucursal->save();
                }

                if ($isUpdate) {
                    $message = "El usuario: " . $edit_user_request['nombre'] . " se ha actualizado correctamente";
                    $status = true;
                } else {
                    $message = "No se ha podido actualizar al usuario: " . $edit_user_request['nombre'];
                    $status = false;
                }
            } catch (\Throwable $th) {
                dd($th);
                $message = "Por favor, contáctese con el administrador de sistemas ya que no se ha podido actualizar al usuario: " . $edit_user_request['nombre'];
                return redirect()->route('configuracion.usuarios.index')->with('message', $message)->with('status', false);
            }

            return redirect()->route('configuracion.usuarios.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('configuracion.usuarios.index')->with('message', 'No se ha podido encontrar el usuario')->with('status', false);
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
            $id = crypt::decrypt($id);
            $user_delete = User::where('user_id', $id)->first();
            $user_delete->user_status = 'Baja';

            $isRemoved = $user_delete->update();
            $status = false;
            if ($isRemoved) {
                $message = "El usuario se ha dado de baja correctamente";
                $status = true;
            } else {
                $message = "No se ha podido dar de baja al usuario";
                $status = false;
            }
            return redirect()->route('configuracion.usuarios.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('configuracion.usuarios.index')->with('message', 'No se ha podido encontrar el usuario')->with('status', false);
        }
    }

    public function userAction(Request $request)
    {
        $nombre = $request->nombre;
        $user = $request->user;
        $rol = $request->rol;
        $status = $request->status;

        switch ($request->input('action')) {
            case 'Búsqueda':

                $user_collection_filtro = User::whereUserName($nombre)->whereUserNames($user)->whereUserRoles($rol)->whereUserStatus($status)->get();

                $user_filtro_array = $user_collection_filtro->toArray();

                return redirect()->route('configuracion.usuarios.index')->with('user_filtro_array', $user_filtro_array)->with('nombre', $nombre)->with('user', $user)->with('rol', $rol)->with('status', $status);
                break;

            case 'Exportar excel':
                $usuario = new ConfUsuariosExport($nombre, $user, $rol, $status);
                return Excel::download($usuario, 'usuarios.xlsx');
                break;
        }
    }

    public function licenceApp()
    {
        if (!session('userSession')) {
            return response()->json(['data' => 'No hay sesión']);
        }

        $licenciaVigente = Licenses::where('license_UserID', '=', session('userSession')->user_id)->where('license_Licenses', '=', session('userSession')->password)->where('license_Active', '=', 1)->first();
        return response()->json(['data' => $licenciaVigente]);
    }

    public function verificate(Request $request)
    {
        if ($request->user_id == null) {
            return response()->json(['data' => 'No hay sesión']);
        }

        $licenciaVigente = Licenses::where('license_UserID', '=', $request->user_id)->first();
        return response()->json(['data' => $licenciaVigente]);
    }

    function selectEmpresas()
    {
        $empresas = CAT_COMPANIES::where('companies_status', '=', 'Alta')->get();
        $empresas_array = array();
        foreach ($empresas as $empresa) {
            $empresas_array[$empresa->companies_key] = $empresa->companies_name;
        }

        return $empresas_array;
    }

    function selectSucursales()
    {
        $sucursales = CAT_BRANCH_OFFICES::where('branchOffices_status', '=', 'Alta')->get();
        $sucursales_array = array();
        foreach ($sucursales as $sucursal) {
            $sucursales_array[$sucursal->branchOffices_key] = $sucursal->branchOffices_name;
        }
        return $sucursales_array;
    }

    function selectClientes()
    {
        $clientes = CAT_CUSTOMERS::where('customers_status', '=', 'Alta')->get();
        $clientes_array = array();
        foreach ($clientes as $cliente) {
            $clientes_array[$cliente->customers_key] = $cliente->customers_key . ' - ' . $cliente->customers_businessName;
        }
        return $clientes_array;
    }

    function selectAlmacen()
    {
        $almacenes = CAT_DEPOTS::where('depots_status', '=', 'Alta')->get();
        $almacenes_array = array();
        foreach ($almacenes as $almacen) {
            $almacenes_array[$almacen->depots_key] = $almacen->depots_key . ' - ' . $almacen->depots_name;
        }
        return $almacenes_array;
    }

    function selectAgente()
    {
        $agentes = CAT_AGENTS::where('agents_status', '=', 'Alta')->where('agents_type', '=', 'Vendedor')->get();
        $agentes_array = array();
        foreach ($agentes as $agente) {
            $agentes_array[$agente->agents_key] = $agente->agents_key . ' - ' . $agente->agents_name;
        }
        return $agentes_array;
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

    function selectCuentaCaja()
    {
        $cajas = CAT_MONEY_ACCOUNTS::where('moneyAccounts_status', '=', 'Alta')->where('moneyAccounts_accountType', '=', 'Caja')->get();
        $cajas_array = array();
        foreach ($cajas as $caja) {
            $cajas_array[$caja->moneyAccounts_key] = $caja->moneyAccounts_key . ' - ' . $caja->moneyAccounts_bank;
        }
        return $cajas_array;
    }

    function selectRoles()
    {
        $roles = CONF_ROLES::where('status', '=', 'Alta')->get();
        $roles_array = array();
        foreach ($roles as $rol) {
            $roles_array[$rol->identifier] = $rol->identifier;
        }
        return $roles_array;
    }

    function AllRoles()
    {
        $roles = CONF_ROLES::all();
        $roles_array = array();
        foreach ($roles as $rol) {
            $roles_array[$rol->identifier] = $rol->identifier;
        }
        return $roles_array;
    }
}
