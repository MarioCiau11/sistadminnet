<?php

namespace App\Http\Controllers\erpNet\ConfiguracionGeneral;

use App\Exports\RolesExport;
use App\Http\Controllers\Controller;
use App\Models\catalogos\CONF_ROLES;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use stdClass;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:Roles y Usuarios']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::where('status', '=', 'Alta')->get();
        return view('page.ConfiguracionGeneral.RolesUsuarios.Roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categoriaObject = new stdClass();

        $permission_collection = Permission::all();
        $permission_array = $permission_collection->toArray();


        foreach ($permission_array as $key => $value) {
            if ($value['categoria'] == 'Reportes Módulos') {
                if (property_exists($categoriaObject, $value['tipoReporte'])) {
                    $categoriaObject->{$value['tipoReporte']}[] = $value;
                } else {
                    $categoriaObject->{$value['tipoReporte']} = [$value];
                }
            } else {
                if (property_exists($categoriaObject, $value['categoria'])) {
                    $categoriaObject->{$value['categoria']}[] = $value;
                } else {
                    $categoriaObject->{$value['categoria']} = [$value];
                }
            }
        }

        $categoriasPermisos = (array) $categoriaObject;
        $categorias = array_keys($categoriasPermisos);

        return view('page.ConfiguracionGeneral.RolesUsuarios.Roles.create', compact('categoriasPermisos', 'categorias'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $conf_rol_request = $request->except('_token'); //rechazamos el token que nos pasa el formulario
        // dd($conf_rol_request);
        $isKeyRol = Role::where('name', '=', $conf_rol_request['nombre'])->first();

        if ($isKeyRol) {
            $message = "El rol: " . $conf_rol_request['nombre'] . " ya existe en la base de datos";
            $status = false;
        } else {
            try {
                $role = Role::create(['name' => $conf_rol_request['nombre'], 'guard_name' => 'web', 'descript' => $conf_rol_request['nameDescripcion'], 'status' => $conf_rol_request['statusDG'], 'identifier' => $conf_rol_request['identificador']]);
                isset($conf_rol_request['permisos']) ? $role->givePermissionTo($conf_rol_request['permisos']) : null;
                $message = "El rol: " . $conf_rol_request['nombre'] . " se creó correctamente";
                $status = true;
            } catch (\Throwable $th) {
                $message = "Por favor, vaya con el administrador de sistemas, no se pudo crear el rol: " . $conf_rol_request['nombre'];
                return redirect()->route('configuracion.roles.index')->with('message', $message)->with('status', false);
            }
        }
        return redirect()->route('configuracion.roles.index')->with('message', $message)->with('status', $status);
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
            $role = Role::find($id);
            $categoriaObject = new stdClass();

            $permission_collection = Permission::all();
            $permission_array = $permission_collection->toArray();

            foreach ($permission_array as $key => $value) {
                if (property_exists($categoriaObject, $value['categoria'])) {
                    $categoriaObject->{$value['categoria']}[] = $value;
                } else {
                    $categoriaObject->{$value['categoria']} = [$value];
                }
            }

            $categoriasPermisos = (array) $categoriaObject;
            $categorias = array_keys($categoriasPermisos);
            $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
                ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
                ->all();
            //dd($money_edit);
            return view('page.ConfiguracionGeneral.RolesUsuarios.Roles.show', compact('role', 'categoriasPermisos', 'categorias', 'rolePermissions'));
        } catch (\Exception $e) {
            return redirect()->route('page.ConfiguracionGeneral.RolesUsuarios.Roles.show')->with('message', 'No se pudo encontrar la configuración rol')->with('status', false);
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
            $role = Role::find($id);
            $categoriaObject = new stdClass();
            $permission_collection = Permission::all();
            $permission_array = $permission_collection->toArray();

            foreach ($permission_array as $key => $value) {
                if ($value['categoria'] == 'Reportes Módulos') {
                    if (property_exists($categoriaObject, $value['tipoReporte'])) {
                        $categoriaObject->{$value['tipoReporte']}[] = $value;
                    } else {
                        $categoriaObject->{$value['tipoReporte']} = [$value];
                    }
                } else {
                    if (property_exists($categoriaObject, $value['categoria'])) {
                        $categoriaObject->{$value['categoria']}[] = $value;
                    } else {
                        $categoriaObject->{$value['categoria']} = [$value];
                    }
                }
            }

            $categoriasPermisos = (array) $categoriaObject;
            $categorias = array_keys($categoriasPermisos);

            // dd($categoriasPermisos, $categorias);


            $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
                ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
                ->all();
            //dd($money_edit);
            return view('page.ConfiguracionGeneral.RolesUsuarios.Roles.edit', compact('role', 'categoriasPermisos', 'categorias', 'rolePermissions'));
        } catch (\Exception $e) {
            return redirect()->route('page.ConfiguracionGeneral.RolesUsuarios.Roles.edit')->with('message', 'No se pudo encontrar la configuración rol')->with('status', false);
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
            $role = Role::find($id);
            $role->name = $request->input('nombre');
            $role->descript = $request->input('nameDescripcion');
            $role->status = $request->input('statusDG');

            try {
                $role->update();
                $role->permissions()->sync($request->input('permisos'));

                return redirect()->route('configuracion.roles.index')->with('message', 'El rol se actualizó correctamente')->with('status', true);
            } catch (\Throwable $th) {
                return redirect()->route('configuracion.roles.index')->with('message', 'Error al actualizar el rol')->with('status', false);
            }
        } catch (\Exception $e) {
            return redirect()->route('configuracion.roles.index')->with('message', 'No se pudo encontrar la configuración rol')->with('status', false);
        }

        return redirect()->route('configuracion.roles.index')->with('message', 'No se pudo encontrar la configuración rol')->with('status', false);
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

            $role_delete = Role::where('id', $id)->first();
            $role_delete->status = 'Baja';

            $isRemoved = $role_delete->update();
            $status = false;
            if ($isRemoved) {
                $message = "El rol se eliminó correctamente";
                $status = true;
            } else {
                $message = "No se ha podido eliminar el rol";
                $status = false;
            }

            return redirect()->route('configuracion.roles.index')->with('message', $message)->with('status', $status);
        } catch (\Throwable $th) {
            return redirect()->route('configuracion.roles.index')->with('message', 'No se pudo mostrar el rol')->with('status', false);
        }
    }

    public function rolesAction(Request $request)
    {
        $nameRol = $request->nombre;
        $status = $request->status;

        switch ($request->input('action')) {
            case 'Búsqueda':

                $roles_collection_filtro = Conf_roles::whereRolName($nameRol)->whereRolStatus($status)->get();


                return redirect()->route('configuracion.roles.index')->with('roles_filtro_array', $roles_collection_filtro)->with('nombre', $nameRol)->with('status', $status);
                break;

            case 'Exportar excel':
                $rol = new RolesExport($nameRol, $status);
                return Excel::download($rol, 'roles.xlsx');
                break;

            default:
                break;
        }
    }
}
