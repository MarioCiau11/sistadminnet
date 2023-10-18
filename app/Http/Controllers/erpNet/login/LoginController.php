<?php

namespace App\Http\Controllers\erpNet\login;

use App\Http\Controllers\Controller;
use App\Models\catalogos\CAT_BRANCH_OFFICES;
use App\Models\catalogos\CAT_COMPANIES;
use App\Models\catalogos\CONF_USERS_BRANCH_ALLOWED;
use App\Models\catalogos\CONF_USERS_COMPANIES;
use App\Models\User;
use Illuminate\Http\Request;


class LoginController extends Controller
{
    public function verificacionUsuario(Request $request)
    {
        $existUser = User::where('username', '=', $request->username)->where('user_status', '=', 'Alta')->first();
        if ($existUser) {
            return response()->json(['status' => 200, 'data' => $existUser]);
        }

        return response()->json(['status' => 404, 'data' => 'El usuario no existe']);
    }

    public function verificacionPassword(Request $request)
    {
        $existUser = User::where('username', '=', $request->username)->where('user_status', '=', 'Alta')->first();
        if ($existUser) {
            if (password_verify($request->password, $existUser->password)) {
                return response()->json(['status' => 200, 'data' => 'Contraseña correcta']);
            }
            return response()->json(['status' => 404, 'data' => 'Estas credenciales no coinciden con nuestros registros']);
        }
        return response()->json(['status' => 404, 'data' => 'Verificacion de contraseña fallida']);
    }

    public function empresasById(Request $request)
    {
        try {
            $id = $request->id;
            $empresas = CONF_USERS_COMPANIES::where('usersCompanies_userID', '=', $id)->get();
            $empresasItem = [];
            foreach ($empresas as $empresa) {
                $empresasE = CAT_COMPANIES::where('companies_key', '=', $empresa->usersCompanies_userCompany)->first();
                array_push($empresasItem, $empresasE);
            }

            return response()->json(['status' => 200, 'data' => $empresasItem]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 404, 'message' => $th->getMessage()]);
        }
    }

    public function empresaById(Request $request)
    {
        try {
            $clave = $request->clave;
            // dd($clave);
            $empresa = CAT_COMPANIES::where('companies_key', '=', $clave)->first();
            return response()->json(['status' => 200, 'data' => $empresa]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 404, 'message' => $th->getMessage()]);
        }
    }

    public function sucursalByClaveEmpresa(Request $request)
    {
        try {
            $existUser = User::where('username', '=', $request->username)->where('user_status', '=', 'Alta')->select('user_id')->first();
            $id = $existUser->user_id;
            $clave = $request->clave;
            $sucursales = CAT_BRANCH_OFFICES::where('branchOffices_companyId', '=', $clave)->where('branchOffices_status', '=', 'Alta')->get();

            $sucursalItem = [];
            foreach ($sucursales as $sucursal) {
                $sucursalesE = CAT_BRANCH_OFFICES::where('branchOffices_key', '=', $sucursal->branchOffices_key)->first();
                array_push($sucursalItem, $sucursalesE);
            }

            //ahora se debe de validar que la sucursal que se esta buscando este dentro de las sucursales que tiene el usuario en su tabla de sucursales permitidas
            $sucursalAllow = [];
            foreach ($sucursalItem as $sucursal) {
                $sucursalesPermitidas = CONF_USERS_BRANCH_ALLOWED::where('usersBranch_userID', '=' , $id)->where('usersBranch_userBranchAllowed', '=', $sucursal->branchOffices_key)->first();
                if($sucursalesPermitidas){
                    array_push($sucursalAllow, $sucursal);
                }
            }
            // dd($sucursalAllow);
            return response()->json(['status' => 200, 'data' => $sucursalAllow]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 404, 'message' => $th->getMessage()]);
        }
    }
}
